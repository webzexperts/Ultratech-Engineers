<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\InterLocationTransfer;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use App\Models\Admin;
use App\Models\Transaction\InterLocationTransferDetails;
use App\Models\Item;
use App\Models\MaterialMpt;
use App\Models\ProbeUT;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetails;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use App\Models\EquipmentMPT;
use App\Models\LPTChemical;
use App\Models\EquipmentUT;
use App\Models\ItemOpening;
use App\Models\File;



class InterLocationTransferController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-inter_location_transfer');
    }

    public function index(InterLocationTransfer $grn_data, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $current_location = getCurrentLocation()->location_id;
        $ilt_data = InterLocationTransfer::select([
            'inter_location_transfer.ilt_id',
            'inter_location_transfer.dc_type_id',
            'inter_location_transfer.dc_number',
            'inter_location_transfer.dc_sequence',
            'inter_location_transfer.dc_date',
            'to_location.location_name as to_location',
            'item.item_name',
            'item_group.item_group',
            'item.item_type as main_group',
            'item.item_type as main_group_key',
            'unit.unit',
            'sr.name_for_display', 
            'inter_location_transfer_details.sr_table_unique_id',
            'inter_location_transfer_details.sr_table_pk_id',
            'inter_location_transfer_details.dc_qty',
            'inter_location_transfer_details.item_id',
            'inter_location_transfer_details.aerb_no',
            'inter_location_transfer_details.application_no',
            'inter_location_transfer_details.movement_approval',
            'inter_location_transfer_details.validity',
            'inter_location_transfer_details.remark',
            'inter_location_transfer_details.stock_effect_type',
            'prepared_by.person_name as prepared_by',
            'inter_location_transfer.created_on',
            'inter_location_transfer.created_by',
            'inter_location_transfer.last_by',
            'inter_location_transfer.last_on'
        ])
        ->leftJoin('inter_location_transfer_details','inter_location_transfer_details.inter_location_transfer_id','=','inter_location_transfer.ilt_id')
        ->leftJoin('admin as prepared_by','prepared_by.id','=','inter_location_transfer.prepared_by_id')
        ->leftJoin('location as to_location','to_location.location_id','=','inter_location_transfer.to_location_id')
        ->leftJoin('item','item.id','=','inter_location_transfer_details.item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'inter_location_transfer_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'inter_location_transfer_details.sr_table_unique_id');
        })
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->where('inter_location_transfer.year_id', $year_data->id)
        ->where('inter_location_transfer.current_location_id', $current_location);

        $dataTable = DataTables::of($ilt_data)
        ->filterColumn('inter_location_transfer.dc_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'inter_location_transfer.dc_number');
        })
        ->editColumn('dc_date', function($ilt_data){
            if ($ilt_data->dc_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $ilt_data->dc_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('inter_location_transfer.dc_date', function ($q, $k) {
            applyDate($q, $k, 'inter_location_transfer.dc_date');
        })
        
        ->editColumn('dc_qty', function($ilt_data) {
            return $ilt_data->dc_qty > 0 ? number_format((float)$ilt_data->dc_qty, 3, '.', '') : number_format((float) 0, 3, '.', '');
        })
        ->editColumn('main_group', function($ilt_data) {
            return config('app.item_type')[$ilt_data->main_group] ?? $ilt_data->main_group;
        })
        ->filterColumn('item.item_type', function($query, $keyword) {

            $itemTypes = config('app.item_type');
            $matchedKeys = [];
            foreach ($itemTypes as $key => $value) {
                if (stripos($value, $keyword) !== false) {
                    $matchedKeys[] = $key;
                }
            }
            if (!empty($matchedKeys)) {
                $query->whereIn('item.item_type', $matchedKeys);
            } else {
                $query->where('item.item_type', 'like', "%{$keyword}%");
            }
        })
        ->filterColumn('inter_location_transfer_details.dc_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('dc_qty')
                    ->orWhere('dc_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(dc_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('dc_qty', 'like', "%{$search}%");
            }
        })
        ->editColumn('unit', function($ilt_data) {
            if ($ilt_data->stock_effect_type == 'prod. area') {
                return 'SQIN';
            }
            return $ilt_data->unit;
        })
        ->filterColumn('unit.unit', function($query, $keyword) {
            $search = trim($keyword);
            $query->where(function($q) use ($search) {
                $q->where(function($sub_q) use ($search) {
                    $sub_q->where('unit.unit', 'like', "%{$search}%")
                          ->where(function($sub_q2) {
                              $sub_q2->where('inter_location_transfer_details.stock_effect_type', '!=', 'prod. area')
                                     ->orWhereNull('inter_location_transfer_details.stock_effect_type');
                          });
                });
                if (stripos('SQIN', $search) !== false || stripos('SQ.IN', $search) !== false || stripos('SQ INCH', $search) !== false) {
                    $q->orWhere('inter_location_transfer_details.stock_effect_type', 'prod. area');
                }
            });
        })
        ->editColumn('movement_approval', function($ilt_data){
            if(!empty($ilt_data->movement_approval))
            {
                $documentUrl = asset('storage/' . $ilt_data->movement_approval);
                $document = '<a href="' . $documentUrl . '" target="_blank">
                <i class="ri-eye-fill action-icon remove_filters_short_qty" ></i>
                </a>';
                return $document;
            }
            return '';
        })
         ->editColumn('validity', function($ilt_data){
            if ($ilt_data->validity != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $ilt_data->validity)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('inter_location_transfer_details.validity', function ($q, $k) {
            applyDate($q, $k, 'inter_location_transfer_details.validity');
        })
        ->addColumn('options',function($ilt_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if(hasAccess("inter_location_transfer", "print")) {
                    $dc_number = !empty($ilt_data->dc_number) ? '_'.str_replace('/', '_', $ilt_data->dc_number) : "";

                    $pdfName  = 'Inter_Location_Transfer'.$dc_number;
                    $encodedId = base64_encode($ilt_data->ilt_id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=inter_location_transfer");
               
                    $action .= '<li><a  class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }
                if (hasAccess("inter_location_transfer", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-inter_location_transfer"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("inter_location_transfer", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'inter_location_transfer');
        return $dataTable
        ->rawColumns(['options','sr_no','last_by','last_on','created_by','created_on','validity','movement_approval'])
        ->make(true);
    }

    public function getLatestInterLocationTransferNumber(Request $request)
    {
        $modal  =  InterLocationTransfer::class;
        $sequence = 'dc_sequence';           
        $prefix = 'DC/LOC';           
        $sup_num_format = getLatestSequence($modal,$sequence,$prefix);   

        return response()->json([
          'response_code' => 1,
          'latest_no'     => $sup_num_format['format'],
          'number'        => $sup_num_format['isFound'],
      ]);
    }


    public function store(Request $request)
    {
        // dd($request->all());
        $year_data = getCurrentYearData();
        $current_location_id = getCurrentLocation()->location_id;    

        DB::beginTransaction();
        try
        {
            $existNumber = InterLocationTransfer::where([['dc_sequence',  $request->dc_sequence],['dc_number',$request->dc_number],['year_id',$year_data->id]])->first();
            
            if($existNumber)
            {
                $latestNo = $this->getLatestInterLocationTransferNumber($request);
                $tmp =  $latestNo->getContent();
                $area = json_decode($tmp, true);
                $dc_number =   $area['latest_no'];
                $dc_sequence = $area['number'];
            }
            else
            {
                $dc_number = $request->dc_number;
                $dc_sequence = $request->dc_sequence;
            }

            $page_id = getMenuIdBassedOnDisplayName('inter_location_transfer');
            $assign_format_no = getAssignFormateNoForTransaction($current_location_id, $page_id->id,$request->dc_date);

            $ilt_data =  InterLocationTransfer::create([
                'dc_sequence'           => $dc_sequence ?? null,
                'current_location_id'   => $current_location_id ?? null,
                'dc_number'             => $dc_number ?? null,
                'dc_date'               => isset($request->dc_date) ? Date::createFromFormat('d/m/Y', $request->dc_date)->format('Y-m-d') : null,
                'dc_type_id'            => $request->dc_type_id ?? null,
                'to_location_id'        => $request->to_location_id ?? null,
                'mode_of_transport'     => $request->mode_of_transport ?? null,
                'transporter'           => $request->transporter ?? null,
                'vehicle_no'            => $request->vehicle_no ?? null,
                'sp_note'               => $request->sp_note ?? null,
                'prepared_by_id'   => $request->prepared_by_id ?? null,
                'assign_format_no'      => $assign_format_no,
                'year_id'               => $year_data->id,
                'company_id'            => Auth::user()->company_id,
                'created_on'            => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'            => Auth::user()->id,
            ]);


            $inter_details_data = $request->inter_details_data = json_decode($request->inter_details_data, true);
            if(!empty($inter_details_data))
            {
                foreach($inter_details_data as $ctKey => $ctVal)
                {
                    if($ctVal != null)
                    {
                        $mode = $ctVal['mode'];

                        if($mode == 'Delete') {
                            continue;
                        }

                        if($mode == 'Insert')
                        {
                            $from_qty         = (float)$ctVal['dc_qty'];
                            $next_qty         = 0;
                            $transaction_mode = 'I';
                            $iltd_id          = 0;                    
                            $pid_id      = $ctVal['pid_id'] ?? 0;

                            $checkQty = $this->qtyValidation($from_qty, $next_qty, $transaction_mode, $iltd_id, $pid_id, $request->dc_type_id);

                            if ($checkQty) {
                                return $checkQty;
                            }

                            $images = '';
                            $blobImage = '';
                            if(!empty($ctVal['movement_approval_doc']))
                            {
                                $file = new File();
                                $isFound =  $file->getFileFromTemp($ctVal['movement_approval_doc'],$prefix = 'movement_approval');
                                if($isFound !== false)
                                {
                                    $images = $isFound;
                                    $filePath = storage_path('app/public/' . $images);
                                    if (!empty($images) && $file->Is_Files_Exists($images))
                                    {
                                        $blobImage = file_get_contents($filePath);
                                    }
                                }
                            }

                            $rate_unit = ItemOpening::where('current_location_id',$current_location_id)
                            ->where('io_item_id',$ctVal['item_id'])
                            ->sum('io_stock_rate_unit');
                            $amount = $rate_unit * $ctVal['dc_qty'];

                            $stockEffectType = ($request->dc_type_id == 'SQIN from Prod. Area') ? 'prod. area' : 'main stock';

                             $previousAerb = null;
                             $previousApp = null;
                             $previousMove = null;
                             $previousMoveBlob = null;
                             $previousVal = null;

                             if (!empty($ctVal['sr_table_unique_id']) && $ctVal['sr_table_unique_id'] === 'rt_camera' && !empty($ctVal['sr_table_pk_id'])) {
                                 $camera = \App\Models\RTCamera::lockForUpdate()->find($ctVal['sr_table_pk_id']);
                                 if ($camera) {
                                     $previousAerb = $camera->rt_aerb_no;
                                     $previousApp = $camera->application_no;
                                     $previousMove = $camera->movement_approval;
                                     $previousMoveBlob = $camera->movement_approval_blob;
                                     $previousVal = $camera->validity;

                                     // Update the camera with the new values
                                     $camera->rt_aerb_no = $ctVal['aerb_no'] ?? null;
                                     $camera->application_no = $ctVal['application_no'] ?? null;
                                     $camera->movement_approval = $images != '' ? $images : null;
                                     $camera->movement_approval_blob = $blobImage ? $blobImage : null;
                                     $camera->validity = !empty($ctVal['validity']) ? Date::createFromFormat('d/m/Y', $ctVal['validity'])->format('Y-m-d') : null;
                                     $camera->save();
                                 }
                             }

                             $inter_details = InterLocationTransferDetails::create([
                                 'inter_location_transfer_id'       => $ilt_data->ilt_id,
                                 'item_id'             => !empty($ctVal['item_id']) ? $ctVal['item_id'] : null,
                                 'sr_table_unique_id'  => !empty($ctVal['sr_table_unique_id']) ? $ctVal['sr_table_unique_id'] : null,
                                 'sr_table_pk_id'   => !empty($ctVal['sr_table_pk_id']) ? $ctVal['sr_table_pk_id'] : null,
                                 'pid_id'           => !empty($ctVal['pid_id']) ? $ctVal['pid_id'] : null,
                                 'dc_qty'           => !empty($ctVal['dc_qty']) ? $ctVal['dc_qty'] : null,
                                 'rate_unit'        =>$rate_unit ??null,
                                 'amount'           => $amount ?? null,
                                 'remark'           => !empty($ctVal['remark']) ? $ctVal['remark'] : null,
                                 'aerb_no'          => $ctVal['aerb_no'] ?? null,
                                 'application_no'   => $ctVal['application_no'] ?? null,
                                 'movement_approval' =>  $images != '' ? $images : null,
                                 'movement_approval_blob' =>  $blobImage  ? $blobImage : null,
                                 'validity' => !empty($ctVal['validity']) ? Date::createFromFormat('d/m/Y', $ctVal['validity'])->format('Y-m-d') : null,
                                 'previous_aerb_no' => $previousAerb,
                                 'previous_application_no' => $previousApp,
                                 'previous_movement_approval' => $previousMove,
                                 'previous_movement_approval_blob' => $previousMoveBlob,
                                 'previous_validity' => $previousVal,
                                 'stock_effect_type' => $stockEffectType,
                                 'previous_status_transaction_id' => null,
                                 'current_status_transaction_id'  => null,
                             ]);

                            if(!empty($ctVal['sr_table_pk_id'])){
                                changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Outside', 'Active', $request->to_location_id, 'Insert', $inter_details);
                            } 


                            if ($stockEffectType == 'prod. area') {
                                stockEffectSQIN($current_location_id, $ctVal['item_id'], $ctVal['item_id'], $ctVal['dc_qty'], 0, 'Insert', 'D', 'Inter Location Transfer', $ilt_data->ilt_id);
                            } else {
                                stockEffect($current_location_id, $ctVal['item_id'], $ctVal['item_id'], $ctVal['dc_qty'], 0, $amount, 0, 'Insert', 'D', 'Inter Location Transfer', $ilt_data->ilt_id, $ctVal['sr_table_unique_id'] ?? null, $ctVal['sr_table_pk_id'] ?? null);
                            }
                        }
                    }
                }
            }

            if($ilt_data->save())
            {
                DB::commit();                
                $dc_number = !empty($dc_number) ? '_'.str_replace('/', '_', $dc_number) : "";
                $pdf_name = 'Inter_Location_Transfer'.$dc_number;
                GeneratePdf($ilt_data->ilt_id,$pdf_name,'inter_location_transfer','add');
                 $encodedId = base64_encode($ilt_data->ilt_id);

                if(hasAccess("inter_location_transfer", "print")){  
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=inter_location_transfer");
                }else{
                    $url ="";
                }

                return response()->json([
                    'response_code' => '1',
                     'url' => $url,
                    'response_message' => getResponseMessage('store_success'),
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('store_error'),
                ]);
            }
        }
        catch(\Exception $e)
        {
            report($e);
            // DB::rollBack();
            // return response()->json([
            //     'response_code' => '0',
            //     'response_message' => getResponseMessage('store_error'),
            //     'original_error' => $e->getMessage()
            // ]);
            DB::rollBack();
            $message = $e->getMessage();

            if (in_array($message, [
                    'Insufficient Stock',
                    'Invalid Location Code',
                    'Invalid Item'
                ]) || str_contains($message, 'Sr. No. Is Already')
            ) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            } else{
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('store_error'),
                    'original_error' => $e->getMessage()
                ]);
            }
        }
    }

    public function edit(Request $request)
    {
        $itemTypes = getItemType();   
        $isAnyPartInUse = false;
        $current_location_id = getCurrentLocation()->location_id;
        $ilt_data =  DB::select('CALL inter_location_transfer_master(?)', [$request->id]);
        if(!empty($ilt_data))
        {
            $ilt_data = $ilt_data[0];
            $ilt_data->dc_date = $ilt_data->dc_date != "" ? Date::createFromFormat('Y-m-d',  $ilt_data->dc_date)->format('d/m/Y') : "";

            $dc_number = !empty($ilt_data->dc_number) ? '_'.str_replace('/', '_', $ilt_data->dc_number) : "";


            $ilt_data->pdf_name = 'Inter_Location_Transfer'.$dc_number;

            
            if(isset($ilt_data->cmp_logo))
            {
                $ilt_data->cmp_logo = base64_encode($ilt_data->cmp_logo);
            }

            // $pi_no = !empty($ilt_data->pi_no) ? '_'.str_replace('/', '_', $ilt_data->pi_no) : "";       
            // $ilt_data->pdf_name = 'Purchase_Indent'.$pi_no;
            
        }
        
        $inter_details_data = DB::select('CALL inter_location_transfer_details(?)', [$request->id]);
        foreach ($inter_details_data as $row) {
            // $row->main_group = $itemTypes[$row->main_group] ?? $row->main_group;
            $amount = $row->dc_qty * $row->stock_rate_unit;
            $row->amount = number_format($amount, 3, '.', '');
            $row->pi_date = $row->pi_date != "" ? Date::createFromFormat('Y-m-d',  $row->pi_date)->format('d/m/Y') : "";
            if($ilt_data && $ilt_data->dc_type_id == "From Indent"){
        
                $row->pend_pi_qty = $row->pend_pi_qty + $row->dc_qty;
               
                $row->pend_pi_qty = number_format((float)$row->pend_pi_qty, 3, '.','');
            } else {
                $row->pend_pi_qty = '';
            }
            $row->name_for_display = $row->sr_no;
            if($row->sr_table_unique_id == "mpt_material" || $row->sr_table_unique_id == "dpt_chemical") {
                $row->io_stock_qty = $row->sr_qty + $row->dc_qty;
                $row->io_stock_qty = number_format((float)$row->io_stock_qty, 3, '.','');
            } else {
                $row->io_stock_qty = $row->io_stock_qty + $row->dc_qty;
                $row->io_stock_qty = number_format((float)$row->io_stock_qty, 3, '.','');
            }

            if (isset($row->validity) && $row->validity != "" && $row->validity != "0000-00-00") {
                $row->validity = Date::createFromFormat('Y-m-d', $row->validity)->format('d/m/Y');
            } else {
                $row->validity = "";
            }

             $row->movement_approval_doc = $row->movement_approval;
            $row->movement_approval_blob = base64_encode($row->movement_approval_blob);

            
            $PendingQty = DB::table('pending_inter_location_transfer_qty as pend')->where("pend.iltd_id",$row->inter_location_transfer_details_id)->value("pend.pending_qty");

            $total_qty = $row->dc_qty;
            if ($PendingQty === null) {
                $PendingQty = $total_qty;
            }
            $isFound = $total_qty - $PendingQty;

            if($isFound > 0){
                $row->in_use = true;
                $row->used_qty = $isFound;
                $isAnyPartInUse = true;
            } else {
                $row->in_use = false;
                $row->used_qty = 0;
            }
            // $Items = getItemsForPI($row->item_id);
        }
        unset($row);

        if($ilt_data)
        {
            $ilt_data->in_use = false;
            if($isAnyPartInUse == true){
                $ilt_data->in_use = true;
            }
            return response()->json([
                'ilt_data'          => $ilt_data,
                'inter_details_data'  => $inter_details_data,
                // 'items'            => $Items ?? [],
                'response_code'    => '1',
                'response_message' => '',
            ]);
        }
        else
        {
            return response()->json([
                'response_code'    => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    public function update(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();
        $current_location_id = getCurrentLocation()->location_id;

        $year_data = getCurrentYearData();
        $validated = $request->validate([
            'dc_sequence' => [
                'required',
                'max:155',
                Rule::unique('inter_location_transfer')
                    ->where(function ($query) use ($year_data, $current_location_id) {
                        $query->where('year_id', $year_data->id)
                            ->where('current_location_id', $current_location_id);
                    })
                    ->ignore($request->id, 'ilt_id')
            ],
        ], [
            'dc_sequence.unique' => 'Duplicate Inter Location Transfer No. Found.',
            'dc_sequence.required' => 'Enter Inter Location Transfer No.',
        ]);

        try
        {           

            $page_id = getMenuIdBassedOnDisplayName('inter_location_transfer');
            $assign_format_no = getAssignFormateNoForTransaction($current_location_id, $page_id->id,$request->dc_date);

            $ilt_data = InterLocationTransfer::where('ilt_id', $request->id)->update([
                'dc_sequence'           => $request->dc_sequence ?? null,
                'current_location_id'   => $current_location_id ?? null,
                'dc_number'             => $request->dc_number ?? null,
                'dc_date'               => isset($request->dc_date) ? Date::createFromFormat('d/m/Y', $request->dc_date)->format('Y-m-d') : null,
                'dc_type_id'            => $request->dc_type_id ?? null,
                'to_location_id'        => $request->to_location_id ?? null,
                'mode_of_transport'     => $request->mode_of_transport ?? null,
                'transporter'           => $request->transporter ?? null,
                'vehicle_no'            => $request->vehicle_no ?? null,
                'sp_note'               => $request->sp_note ?? null,
                'prepared_by_id'        => $request->prepared_by_id ?? null,
                // 'assign_format_no'      => $assign_format_no,   not update assign formate discussion ramde sir
                'last_on'             => Carbon::now('Asia/Kolkata'),
                'last_by'             => Auth::id(),
            ]);


            if(!$ilt_data)
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }

            $inter_details_data = json_decode($request->inter_details_data, true);

            if(!empty($inter_details_data))
            {
                foreach($inter_details_data as $ctVal)
                {
                    if(empty($ctVal)) continue;

                    $mode = $ctVal['mode'];
                    
                    if($mode == 'Insert')
                    {
                         $from_qty         = (float)$ctVal['dc_qty'];
                            $next_qty         = 0;
                            $transaction_mode = 'I';
                            $iltd_id          = 0;                    
                            $pid_id      = $ctVal['pid_id'] ?? 0;

                            $checkQty = $this->qtyValidation($from_qty, $next_qty, $transaction_mode, $iltd_id, $pid_id, $request->dc_type_id);

                            if ($checkQty) {
                                return $checkQty;
                            }

                         $images = '';
                         $blobImage = '';
                         if(!empty($ctVal['movement_approval_doc']))
                         {
                             $file = new File();
                             $isFound =  $file->getFileFromTemp($ctVal['movement_approval_doc'],$prefix = 'movement_approval');
                             if($isFound !== false)
                             {
                                 $images = $isFound;
                                 $filePath = storage_path('app/public/' . $images);
                                 if (!empty($images) && $file->Is_Files_Exists($images))
                                 {
                                     $blobImage = file_get_contents($filePath);
                                 }
                             }
                             else
                             {
                                 $images = $ctVal['movement_approval_doc'];
                                 $filePath = storage_path('app/public/' . $images);
                                 if(!empty($images) && $file->Is_Files_Exists($images))
                                 {
                                     $blobImage = file_get_contents($filePath);
                                 }
                             }
                         }

                         $rate_unit = ItemOpening::where('current_location_id',$current_location_id)
                         ->where('io_item_id',$ctVal['item_id'])
                         ->sum('io_stock_rate_unit');
                         $amount = $rate_unit * $ctVal['dc_qty'];

                         $stockEffectType = ($request->dc_type_id == 'SQIN from Prod. Area') ? 'prod. area' : 'main stock';

                         $previousAerb = null;
                         $previousApp = null;
                         $previousMove = null;
                         $previousMoveBlob = null;
                         $previousVal = null;

                         if (!empty($ctVal['sr_table_unique_id']) && $ctVal['sr_table_unique_id'] === 'rt_camera' && !empty($ctVal['sr_table_pk_id'])) {
                             $camera = \App\Models\RTCamera::lockForUpdate()->find($ctVal['sr_table_pk_id']);
                             if ($camera) {
                                 $previousAerb = $camera->rt_aerb_no;
                                 $previousApp = $camera->application_no;
                                 $previousMove = $camera->movement_approval;
                                 $previousMoveBlob = $camera->movement_approval_blob;
                                 $previousVal = $camera->validity;

                                 // Update the camera with the new values
                                 $camera->rt_aerb_no = $ctVal['aerb_no'] ?? null;
                                 $camera->application_no = $ctVal['application_no'] ?? null;
                                 $camera->movement_approval = $images != '' ? $images : null;
                                 $camera->movement_approval_blob = $blobImage ? $blobImage : null;
                                 $camera->validity = !empty($ctVal['validity']) ? Date::createFromFormat('d/m/Y', $ctVal['validity'])->format('Y-m-d') : null;
                                 $camera->save();
                             }
                         }

                        $inter_details_data = InterLocationTransferDetails::create([
                             'inter_location_transfer_id'       => $request->id,
                             'item_id'             => !empty($ctVal['item_id']) ? $ctVal['item_id'] : null,
                             'sr_table_unique_id'  => !empty($ctVal['sr_table_unique_id']) ? $ctVal['sr_table_unique_id'] : null,
                             'sr_table_pk_id'   => !empty($ctVal['sr_table_pk_id']) ? $ctVal['sr_table_pk_id'] : null,
                             'pid_id'           => !empty($ctVal['pid_id']) ? $ctVal['pid_id'] : null,
                             'dc_qty'           => !empty($ctVal['dc_qty']) ? $ctVal['dc_qty'] : null,
                             'rate_unit'        => $rate_unit ?? null,
                             'amount'           => $amount ?? null,
                             'remark'           => !empty($ctVal['remark']) ? $ctVal['remark'] : null,
                             'aerb_no'          => $ctVal['aerb_no'] ?? null,
                             'application_no'   => $ctVal['application_no'] ?? null,
                             'movement_approval' =>  $images != '' ? $images : null,
                             'movement_approval_blob' =>  $blobImage  ? $blobImage : null,
                             'validity' => !empty($ctVal['validity']) ? Date::createFromFormat('d/m/Y', $ctVal['validity'])->format('Y-m-d') : null,
                             'previous_aerb_no' => $previousAerb,
                             'previous_application_no' => $previousApp,
                             'previous_movement_approval' => $previousMove,
                             'previous_movement_approval_blob' => $previousMoveBlob,
                             'previous_validity' => $previousVal,
                             'stock_effect_type' => $stockEffectType,
                             'previous_status_transaction_id' => null,
                             'current_status_transaction_id'  => null,
                         ]);

                        if ($stockEffectType == 'prod. area') {
                            stockEffectSQIN($current_location_id, $ctVal['item_id'], $ctVal['item_id'], $ctVal['dc_qty'], 0, 'Insert', 'D', 'Inter Location Transfer', $request->id);
                        } else {
                            stockEffect($current_location_id, $ctVal['item_id'], $ctVal['item_id'], $ctVal['dc_qty'], 0, $amount, 0, 'Insert', 'D', 'Inter Location Transfer', $request->id, $ctVal['sr_table_unique_id'] ?? null, $ctVal['sr_table_pk_id'] ?? null);
                        }

                        if(!empty($ctVal['sr_table_pk_id'])){
                            changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Outside', 'Active', $request->to_location_id, 'Insert', $inter_details_data);
                        } 
                    }

                 
                    elseif($mode == 'Update')
                    {
                        if(!empty($ctVal['inter_location_transfer_details_id']))
                        {
                            

                            $olddata = InterLocationTransferDetails::where('inter_location_transfer_details_id', $ctVal['inter_location_transfer_details_id'])->select('amount','rate_unit','item_id','dc_qty')
                            ->first();

                            $dc_qty       = (float)$ctVal['dc_qty'];
                            $from_qty         = max(0, $dc_qty - $olddata->dc_qty);   // increase
                            $next_qty         = max(0, $olddata->dc_qty - $dc_qty);   // decrease
                            $transaction_mode = 'U';
                            $iltd_id           = $ctVal['inter_location_transfer_details_id'];
                            $pid_id           = $ctVal['pid_id'] ?? 0;

                            $checkQty = $this->qtyValidation($from_qty, $next_qty, $transaction_mode, $iltd_id, $pid_id, $request->dc_type_id);
                            if ($checkQty) {
                                return $checkQty;
                            }

                            
                            // $pendingQty = (float) DB::table('pending_purchase_indent_qty')
                            // ->where('pid_id', $ctVal['pid_id'])
                            // ->value('pending_qty');
                           

                            // $old_indent_qty = InterLocationTransferDetails::where('pid_id', $ctVal['pid_id'])->sum('indent_qty');
                            // $total_used_qty = $old_indent_qty - $ctVal['indent_qty'];
                            // // dd($pendingQty,$total_used_qty);
                            // // if($ctVal['indent_qty'] < $total_used_qty)
                            // if($pendingQty < $total_used_qty)
                            // {
                            //     DB::rollBack();
                            //     return response()->json([
                            //         'response_code' => '0',
                            //         'response_message' => 'Purchase Indent Qty. Is Used.',
                            //     ]);
                            // }

                            $imgs = InterLocationTransferDetails::where('inter_location_transfer_details_id', $ctVal['inter_location_transfer_details_id'])->value('movement_approval');
                            $file = new File();
                            $images = '';
                            $blobImage = '';

                            if(!empty($ctVal['movement_approval_doc'])){
                                if($imgs && !empty($imgs)){
                                    if($imgs != $ctVal['movement_approval_doc']){
                                            $file->delete_file($imgs);
                                    }                 
                                }      
                                
                                $isFound = $file->getFileFromTemp($ctVal['movement_approval_doc'],'movement_approval');

                                if($isFound !== false){                    
                                    $images = $isFound;
                                    $filePath = storage_path('app/public/' . $images);
                                    if (file_exists($filePath)) {
                                        $blobImage = file_get_contents($filePath);
                                    } else {
                                        $blobImage = null;
                                    }
                                }else{
                                    $images = $ctVal['movement_approval_doc'];
                                    $filePath = storage_path('app/public/' . $images);
                                    if (file_exists($filePath)) {
                                        $blobImage = file_get_contents($filePath);
                                    } else {
                                        $blobImage = null;
                                    }
                                }
                            }else{
                                if($imgs && !empty($imgs)){
                                    $file->delete_file($imgs);
                                }  
                            }

                            $stockEffectType = ($request->dc_type_id == 'SQIN from Prod. Area') ? 'prod. area' : 'main stock';
                            InterLocationTransferDetails::where('inter_location_transfer_details_id', $ctVal['inter_location_transfer_details_id'])->update([
                                'inter_location_transfer_id'       => $request->id,
                                'item_id'             => !empty($ctVal['item_id']) ? $ctVal['item_id'] : null,
                                'sr_table_unique_id'  => !empty($ctVal['sr_table_unique_id']) ? $ctVal['sr_table_unique_id'] : null,
                                'sr_table_pk_id'   => !empty($ctVal['sr_table_pk_id']) ? $ctVal['sr_table_pk_id'] : null,
                                'pid_id'           => !empty($ctVal['pid_id']) ? $ctVal['pid_id'] : null,
                                'dc_qty'           => !empty($ctVal['dc_qty']) ? $ctVal['dc_qty'] : null,
                                'rate_unit'        =>$olddata->rate_unit ?? null,
                                'amount'           => $ctVal['dc_qty'] * $olddata->rate_unit,
                                'remark'           => !empty($ctVal['remark']) ? $ctVal['remark'] : null,
                                'aerb_no'          => $ctVal['aerb_no'] ?? null,
                                'application_no'   => $ctVal['application_no'] ?? null,
                                'movement_approval' =>  $images != '' ? $images : null,
                                'movement_approval_blob' =>  $blobImage  ? $blobImage : null,
                                'validity' => !empty($ctVal['validity']) ? Date::createFromFormat('d/m/Y', $ctVal['validity'])->format('Y-m-d') : null,
                                'stock_effect_type' => $stockEffectType,
                            ]);
                            // dd($olddata->amount , $ctVal['amount']);
                            if ($stockEffectType == 'prod. area') {
                                stockEffectSQIN($current_location_id, $ctVal['item_id'], $olddata->item_id, $ctVal['dc_qty'], $olddata->dc_qty, 'Update', 'D', 'Inter Location Transfer', $ctVal['inter_location_transfer_details_id']);
                            } else {
                                stockEffect($current_location_id, $ctVal['item_id'], $olddata->item_id, $ctVal['dc_qty'], $olddata->dc_qty, $ctVal['dc_qty'] * $olddata->rate_unit, $olddata->amount, 'Update', 'D', 'Inter Location Transfer', $ctVal['inter_location_transfer_details_id'], $ctVal['sr_table_unique_id'] ?? $olddata->sr_table_unique_id ?? null, $ctVal['sr_table_pk_id'] ?? $olddata->sr_table_pk_id ?? null);
                            }

                            
                        }
                    }

                   
                    elseif($mode == 'Delete')
                    {
                       
                        if(!empty($ctVal['inter_location_transfer_details_id']))
                        {
                            $ilt_data =  DB::select('CALL inter_location_transfer_used_list(?)', [$request->id]);
            
                            if(!empty($ilt_data)){ 

                                DB::rollBack();
                                return response()->json([
                                    'response_code' => '0',
                                    'response_message' => "You Can't Delete, Inter Location Transfer Is Used In " . $ilt_data[0]->table_name . ".",
                                ]);
                            }
                            

                             $olddata = InterLocationTransferDetails::where('inter_location_transfer_details_id',$ctVal['inter_location_transfer_details_id'])
                             ->first();

                             $stockEffectType = $olddata->stock_effect_type ?? (($request->dc_type_id == 'SQIN from Prod. Area') ? 'prod. area' : 'main stock');
                             if ($stockEffectType == 'prod. area') {
                                 stockEffectSQIN($current_location_id, $ctVal['item_id'], $ctVal['item_id'], 0, $olddata->dc_qty, 'Delete', 'D', 'Inter Location Transfer', $ctVal['inter_location_transfer_details_id']);
                             } else {
                                 stockEffect($current_location_id, $ctVal['item_id'], $ctVal['item_id'], 0, $olddata->dc_qty, 0, $olddata->amount, 'Delete', 'D', 'Inter Location Transfer', $ctVal['inter_location_transfer_details_id'], $ctVal['sr_table_unique_id'] ?? $olddata->sr_table_unique_id ?? null, $ctVal['sr_table_pk_id'] ?? $olddata->sr_table_pk_id ?? null);
                             }

                             if (!empty($ctVal['sr_table_unique_id']) && $ctVal['sr_table_unique_id'] === 'rt_camera' && !empty($ctVal['sr_table_pk_id'])) {
                                 $camera = \App\Models\RTCamera::find($ctVal['sr_table_pk_id']);
                                 if ($camera) {
                                     $camera->rt_aerb_no = $olddata->previous_aerb_no;
                                     $camera->application_no = $olddata->previous_application_no;
                                     $camera->movement_approval = $olddata->previous_movement_approval;
                                     $camera->movement_approval_blob = $olddata->previous_movement_approval_blob;
                                     $camera->validity = $olddata->previous_validity;
                                     $camera->save();
                                 }
                             }

                             if(!empty($ctVal['sr_table_unique_id'])){
                                 changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'],$ctVal['sr_table_pk_id'],'Outside','Active',$current_location_id,'Delete');
                             }

                            $oldImgData = InterLocationTransferDetails::where('inter_location_transfer_details_id',$ctVal['inter_location_transfer_details_id'])->get();
                            if($oldImgData->isNotEmpty())
                            {
                                foreach($oldImgData as $key=>$val)
                                {
                                    if($val->movement_approval != '')
                                    {
                                        $file = new File();
                                        $file->delete_file($val->movement_approval);
                                    }
                                }
                            }

                            InterLocationTransferDetails::where('inter_location_transfer_details_id', $ctVal['inter_location_transfer_details_id'])->delete();
                        }
                    }
                }
            }

            DB::commit();

            $dc_number = !empty($request->dc_number) ? '_'.str_replace('/', '_', $request->dc_number) : "";
            $pdf_name = 'Inter_Location_Transfer'.$dc_number;
            GeneratePdf($request->id,$pdf_name,'inter_location_transfer','edit');
                $encodedId = base64_encode($request->id);

            if(hasAccess("inter_location_transfer", "print")){  
                $url = url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=inter_location_transfer");
            }else{
                $url ="";
            }

            return response()->json([
                'response_code' => '1',
                'url' => $url,
                'response_message' => getResponseMessage('update_success'),
            ]);
        }
        catch(\Exception $e)
        {
            report($e);
            DB::rollBack();

            // return response()->json([
            //     'response_code' => '0',
            //     'response_message' => getResponseMessage('update_error'),
            //     'original_error' => $e->getMessage()
            // ]);
            $message = $e->getMessage();

            if (in_array($message, [
                    'Insufficient Stock',
                    'Invalid Location Code',
                    'Invalid Item'
                ]) || str_contains($message, 'Sr. No. Is Already')
            ) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            } else{
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                    'original_error' => $e->getMessage()
                ]);
            }
        }
    }

    public function destroy(Request $request)
    {
        DB::beginTransaction();
         $LocationData = getCurrentLocation()->location_id;
        try
        {

            $iltd_data =  DB::select('CALL inter_location_transfer_used_list(?)', [$request->id]);


            if(!empty($iltd_data)){ 
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Inter Location transfer Is Used In " . $iltd_data[0]->table_name . ".",
                ]);
            }

            $ilt_parent = InterLocationTransfer::find($request->id);
            $transferDetails = InterLocationTransferDetails::where('inter_location_transfer_id', $request->id)->get();

            if($transferDetails->isNotEmpty()){
                foreach($transferDetails as $item){

                    $stockEffectType = $item->stock_effect_type ?? (($ilt_parent && $ilt_parent->dc_type_id == 'SQIN from Prod. Area') ? 'prod. area' : 'main stock');
                    if ($stockEffectType == 'prod. area') {
                        stockEffectSQIN($LocationData, $item->item_id, $item->item_id, 0, $item->dc_qty, 'Delete', 'D', 'Inter Location Transfer', $item->inter_location_transfer_details_id);
                    } else {
                        stockEffect($LocationData, $item->item_id, $item->item_id, 0, $item->dc_qty, 0, $item->amount, 'Delete', 'D', 'Inter Location Transfer', $item->inter_location_transfer_details_id, $item->sr_table_unique_id ?? null, $item->sr_table_pk_id ?? null);
                    }

                     if ($item->sr_table_unique_id === 'rt_camera' && !empty($item->sr_table_pk_id)) {
                         $camera = \App\Models\RTCamera::find($item->sr_table_pk_id);
                         if ($camera) {
                             $camera->rt_aerb_no = $item->previous_aerb_no;
                             $camera->application_no = $item->previous_application_no;
                             $camera->movement_approval = $item->previous_movement_approval;
                             $camera->movement_approval_blob = $item->previous_movement_approval_blob;
                             $camera->validity = $item->previous_validity;
                             $camera->save();
                         }
                     }

                    if($item->sr_table_unique_id != null || $item->sr_table_unique_id != ''){
                      changeStatusAndLocationForSrNo($item->sr_table_unique_id,$item->sr_table_pk_id,'Outside','Active',$LocationData,'Delete',$item);
                    }

                    if($item->movement_approval != '')
                    {
                        $file = new File();
                        $file->delete_file($item->movement_approval);
                    }
                }
            }

            
            InterLocationTransfer::where('ilt_id',$request->id)->delete();
            InterLocationTransferDetails::where('inter_location_transfer_id',$request->id)->delete();
             DB::commit();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        }
        catch(\Exception $e)
        {
            report($e);
            DB::rollBack(); 
            $message = $e->getMessage();
            if(isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451)
            {
                $error_msg = "This Is Used Somewhere, You Can't Delete";
            }
             else if(in_array($message, [
                    'Insufficient Stock',
                    'Invalid Location Code',
                    'Invalid Item'
                ]) || str_contains($message, 'Sr. No. Is Already'))
            {
                $error_msg = $message;
            }
            else
            {
                $error_msg = getResponseMessage('delete_error');
            }

            return response()->json([
                'response_code' => '0',
                'response_message' => $error_msg,
            ]);
        }
    }

    // public function getPendingSupplierForGRN(Request $request)
    // {
    //     $yearIds = getCompanyYearIdsToTill();
    //     $current_location = getCurrentLocation();

    //     if($request->grn_type_id == "Against PO"){


    //         $get_po_supplier = DB::table('pending_purchase_order_qty as pend')->select([
    //         'suppliers.supplier_name','suppliers.id',
    //         ])
    //         ->leftJoin('purchase_order_details', 'purchase_order_details.pod_id', '=', 'pend.pod_id')
    //         ->leftJoin('purchase_order', 'purchase_order.po_id', '=', 'purchase_order_details.pod_po_id')           
    //         ->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_order.po_supplier_id')            
    //         ->whereIn('purchase_order.year_id', $yearIds)
    //         ->where('pend.pending_qty', '>', 0)
    //         ->Where('purchase_order.ship_to_location_id', $current_location->location_id)->get();    
            
    //         $get_po_supplier = $get_po_supplier->unique(['id']);

    //         $get_po_supplier = $get_po_supplier->values()->all();

    //     }else{

    //         $get_po_supplier =  Supplier::select('id','supplier_name')->orderBy('supplier_name','asc')->get();
    //     }

    //     return response()->json([
    //         'response_code' => 1,
    //         'get_po_supplier'  => $get_po_supplier,
    //     ]);
    // }

    // public function getPendingPIListForInterLocationTransfer(Request $request)
    // {
    //     $yearIds = getCompanyYearIdsToTill();
    //     if(isset($request->id)){

    //         $edit_po_data = InterLocationTransferDetails::select(['grn_details.grnd_id','purchase_order.po_id','purchase_order.po_number','purchase_order.po_date','item.id as item_id','unit.unit','item.item_name',

    //             DB::raw("((SELECT IFNULL(SUM(pod.pod_po_qty),0) FROM purchase_order_details AS pod WHERE pod.grnd_pod_id = purchase_order_details.pod_id) - (SELECT IFNULL(SUM(gid.grnd_qty),0)
    //             FROM grn_details AS gid WHERE gid.grnd_pod_id = purchase_order_details.pod_id)) as pend_po_qty"),

    //             // edit ma pending open karave tyare niche ni db row use karvi
    //             // DB::raw("(SELECT purchase_order_details.pod_po_qty -  (SELECT IFNULL(SUM(psid.po_sc_qty),0) FROM po_short_close AS psid  WHERE psid.po_sc_pod_id = purchase_order_details.pod_id) - (SELECT IFNULL(SUM(gid.grnd_qty),0) FROM grn_details AS gid WHERE gid.grnd_pod_id = purchase_order_details.pod_id)) as pend_po_qty"),  
    //         ])
    //         ->leftJoin('purchase_order_details','purchase_order_details.grnd_pod_id','=','grn_details.grnd_pod_id')
    //         ->leftJoin('item','item.id','=','purchase_order_details.pod_item_id')  
    //         ->leftJoin('unit','unit.id','=','item.unit_id')  
    //         ->leftJoin('purchase_order','purchase_order.po_id','=','purchase_order_details.po_id')          
    //         ->where('grn_details.grnd_grn_id',$request->id)
    //         ->get();           

    //     }


    //     $po_data = PurchaseOrder::select(['purchase_order.po_number','purchase_order.po_date','item.id as item_id','item.item_name','purchase_order_details.pod_remark','purchase_order_details.pod_po_qty','unit.unit','purchase_order_details.pod_id',
     
    //         //DB::raw("(SELECT purchase_order_details.pod_po_qty - (SELECT IFNULL(SUM(gid.grnd_qty),0) FROM grn_details AS gid WHERE gid.grnd_pod_id = purchase_order_details.pod_id)) as pend_po_qty"),   

    //          DB::raw("(SELECT purchase_order_details.pod_po_qty -  (SELECT IFNULL(SUM(psid.po_sc_qty),0) FROM po_short_close AS psid  WHERE psid.po_sc_pod_id = purchase_order_details.pod_id) - (SELECT IFNULL(SUM(gid.grnd_qty),0) FROM grn_details AS gid WHERE gid.grnd_pod_id = purchase_order_details.pod_id)) as pend_po_qty"), 
    
    //      ])
    //     ->leftJoin('purchase_order_details','purchase_order_details.pod_po_id','=','purchase_order.po_id')
    //     ->leftJoin('item','item.id','=','purchase_order_details.pod_item_id')  
    //     ->leftJoin('unit','unit.id','=','item.unit_id')  
    //     ->where('purchase_order.po_supplier_id',$request->grn_supplier_id)
    //     ->whereIn('purchase_order.year_id',$yearIds)  
    //     ->having('pend_po_qty','>',0) 
    //     // ->groupBY('purchase_order.po_id','purchase_order_details.pod_id')   
    //     ->get();


    //     // $po_data = $po_data->unique(['po_id']);

        
    //     if(isset($edit_po_data)){
    //         $data = collect($po_data)->merge($edit_po_data);
    //         $grouped = $data->groupBy('po_id');    
            

    //         $merged = $grouped->map(function ($items) {
    //             return $items->reduce(function ($carry, $item) {
    //                 if (!$carry) {
    //                     return $item;
    //                 }
    //             // $carry->pend_po_qty += (float) $item->pend_po_qty;
    //                 return $carry;
    //             });
    //         });
    //         $po_data = $merged->values();   

    //     }

    //     if ($po_data != null) {
    //         foreach ($po_data as $cpKey => $cpVal) {
    //             if ($cpVal->po_date != null) {
    //                 $cpVal->po_date = Date::createFromFormat('Y-m-d', $cpVal->po_date)->format('d/m/Y');
    //             }
               
    //             $cpVal->pend_po_qty =  $cpVal->pend_po_qty > 0 ?  $cpVal->pend_po_qty : 0; 
    //         }
    //     }


    //     $po_data = $po_data->sortBy('purchase_order.po_id')->values();

    //     if ($po_data != null) {
    //         return response()->json([
    //             'response_code' => 1,
    //             'po_data'  => $po_data,            
    //         ]);
    //     }else{
    //         return response()->json([
    //             'response_code' => 1,
    //             'po_data'  => [],            
    //         ]);

    //     }

    // }

    // public function getPendingPoForGrn(Request $request){

    //     $yearIds = getCompanyYearIdsToTill();

    //     $request->pod_ids = explode(',', $request->pod_ids);


    //     if(isset($request->id)){
    //         $pod_id = InterLocationTransferDetails::select('pod_id')->where('grn_id','=',$request->id)->get();          

    //         $edit_po_data = InterLocationTransferDetails::select(['purchase_order.po_id','purchase_order.po_number','purchase_order.po_date','purchase_order_details.pod_id','purchase_order_details.pod_po_qty','purchase_order_details.pod_rate_unit', 
    //         'purchase_order_details.pod_amount','purchase_order_details.pod_item_id','purchase_order_details.remarks','unit.unit',
            
    //         DB::raw("((SELECT IFNULL(SUM(pod.pod_po_qty),0) FROM purchase_order_details AS pod WHERE pod.pod_id = purchase_order_details.pod_id) - (SELECT IFNULL(SUM(gid.grn_qty),0)
    //         FROM grn_details AS gid WHERE gid.grnd_pod_id = purchase_order_details.pod_id)) as pend_po_qty"),

    //         // edit ma pending open thai to aa row mukvu 
    //         // DB::raw("(SELECT purchase_order_details.pod_po_qty -  (SELECT IFNULL(SUM(psid.po_sc_qty),0) FROM po_short_close AS psid  WHERE psid.po_sc_pod_id = purchase_order_details.pod_id) - (SELECT IFNULL(SUM(gid.grnd_qty),0) FROM grn_details AS gid WHERE gid.grnd_pod_id = purchase_order_details.pod_id)) as pend_po_qty"),  
           
    //         ])                
    //         ->leftJoin('purchase_order_details','purchase_order_details.pod_id','=','grn_details.grnd_pod_id')
    //         ->leftJoin('purchase_order','purchase_order.po_id','=','purchase_order_details.po_id')
    //         ->leftJoin('item','item.id','=','purchase_order_details.pod_item_id')           
    //         ->leftJoin('unit','unit.id','=','item.unit_id')
    //         ->whereIn('grn_details.pod_id', $request->po_ids)
    //         ->where('grn_details.grn_id','=',$request->id)
    //         ->get();            

    //     }


    //     $po_data =  PurchaseOrder::select(['purchase_order.po_number','purchase_order.po_date','purchase_order.po_total_amount','purchase_order_details.pod_po_qty','purchase_order_details.pod_amount as grnd_amount','purchase_order_details.pod_rate_unit as grnd_rate_unit','purchase_order_details.pod_rate_unit','purchase_order_details.pod_amount','purchase_order_details.pod_item_id','purchase_order_details.pod_remark as grnd_remark','purchase_order_details.pod_id','unit.unit','item.id as grnd_item_id','item.item_name',

    //     // DB::raw("((SELECT IFNULL(SUM(pod.pod_po_qty),0) FROM purchase_order_details AS pod WHERE pod.pod_id = purchase_order_details.pod_id) - (SELECT IFNULL(SUM(gid.grnd_qty),0)
    //     // FROM grn_details AS gid WHERE gid.grnd_pod_id = purchase_order_details.pod_id)) as pend_po_qty"),  

    //     DB::raw("(SELECT purchase_order_details.pod_po_qty -  (SELECT IFNULL(SUM(psid.po_sc_qty),0) FROM po_short_close AS psid  WHERE psid.po_sc_pod_id = purchase_order_details.pod_id) - (SELECT IFNULL(SUM(gid.grnd_qty),0) FROM grn_details AS gid WHERE gid.grnd_pod_id = purchase_order_details.pod_id)) as pend_po_qty"),  
                
    //     ])
    //     ->leftJoin('purchase_order_details','purchase_order_details.pod_po_id','=','purchase_order.po_id')
    //     ->leftJoin('item','item.id','=','purchase_order_details.pod_item_id')
    //     ->leftJoin('unit','unit.id','=','item.unit_id')
    //     ->whereIn('purchase_order_details.pod_id', $request->pod_ids)
    //     ->whereIn('purchase_order.year_id',$yearIds)  
    //     ->having('pend_po_qty','>',0)      
    //     ->get();
        

    //     if(isset($edit_po_data)){
    //         $data = collect($po_data)->merge($edit_po_data);
    //         $grouped = $data->groupBy('pod_id');    

    //         $merged = $grouped->map(function ($items) {
    //             return $items->reduce(function ($carry, $item) {
    //                 if (!$carry) {
    //                     return $item;
    //                 }
    //                 // $carry->pend_po_qty += (float) $item->pend_po_qty;
    //                 return $carry;
    //             });
    //         });
    
    //         $po_data = $merged->values();   

    //     }

      
    //     if ($po_data != null) {
    //         $changedItemIds = [];
    //         foreach ($po_data as $cpKey => $cpVal) {

    //             if ($cpVal->po_date != null) {
    //                 $cpVal->po_date = Date::createFromFormat('Y-m-d', $cpVal->po_date)->format('d/m/Y');
    //             }

    //             if(isset($request->id)){  
    //                 $grn_qty =   InterLocationTransferDetails::where('pod_id','=',$cpVal->pod_id)->where('grn_id',$request->id)->sum('grn_qty');
    //                 $grn_id = InterLocationTransferDetails::where('pod_id','=',$cpVal->pod_id)->where('grn_id',$request->id)->first();
    //                 $cpVal->grn_qty = $grn_qty;
    //                 $cpVal->grnd_id = $grn_id != null ? $grn_id->grnd_id : 0;    
                   
                    
    //             }else{
    //                 $cpVal->grn_qty = 0;
    //                 $cpVal->grnd_id = 0;
    //                 $cpVal->in_use = false;
    //                 $cpVal->used_qty = 0;
    //             }

    //             $cpVal->pend_po_qty =  $cpVal->pend_po_qty > 0 ?  $cpVal->pend_po_qty : 0;

    //             $newRequest = new Request();

    //             $newRequest->pod_id = $cpVal->pod_id;
    //             $newRequest->record_id = $cpVal->grnd_id;
    //             $newRequest->total_qty = $cpVal->po_qty;

    //             $cpVal->show_pend_qty = self::getPendingQty($newRequest);
    //             $changedItemId = Item::where('id', $cpVal->item_id)
    //             ->where(function($query) {
    //                 $query->where('item.status', 'deactive');
    //             })
    //             ->pluck('id')
    //             ->first();
    //             if ($changedItemId) {
    //                 $changedItemIds[] = $changedItemId; // Now it works
    //             }
    //         }
    //     }

    //     $po_data = $po_data->sortBy('po_id')
    //     ->sortBy('pod_id')
    //     ->values();

    //     if($changedItemIds){
    //         $item = Item::select('id','item_name')->whereIN('id',$changedItemIds)->get();
    //     }else{
    //         $item = '';
    //     }

        
    //     if ($po_data != null) {
    //         return response()->json([
    //             'response_code' => '1',
    //             'po_data' => $po_data,
    //             'item' => $item,

               
    //         ]);
    //     } else {
    //         return response()->json([
    //             'response_code' => '1',
    //             'po_data' => []
    //         ]);
    //     }
    // }

    // public function getPendingQty(Request $request){
    //     $exectQty = $request->total_qty;       
    //     $exectQty = number_format((float)$exectQty, 3, '.','');   

    //     $oldRecords = InterLocationTransferDetails::select(DB::raw('SUM(grnd_qty) as sum'))
    //     ->where('grnd_pod_id','=',$request->grnd_pod_id)
    //     ->where('grnd_id','<=',$request->record_id)
    //     ->groupBy(['grnd_pod_id'])
    //     ->first();

    //     if($oldRecords != null){
    //         $diff = $exectQty - number_format((float)$oldRecords->sum, 3, '.','');
    //         return $diff;
    //     }else{
    //         return abs($exectQty);
    //     } 
    // }

    // // suggest vehicle
    // public function existsTrasnporter(Request $request)
    // {
    //     if($request->term != "")
    //     {
    //         $fdCountry = GRNSupplier::select('grn_transporter')->where('grn_transporter', 'LIKE', $request->term.'%')->groupBy('grn_transporter')->get();
    //         if($fdCountry != null)
    //         {
    //             $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
    //             foreach($fdCountry as $dsKey)
    //             {
    //                 $output .= '<li parent-id="grn_transporter" list-id="grn_transporter_list" class="list-group-item" tabindex="0">'.$dsKey->grn_transporter.'</li>';
    //             }
    //             $output .= '</ul>';

    //             return response()->json([
    //                 'tarnsporterList' => $output,
    //                 'response_code' => 1,
    //             ]);
    //         }
    //         else
    //         {
    //             return response()->json([
    //                 'response_message' => 'No Transporter Available',
    //                 'response_code' => 0,
    //             ]);
    //         }
    //     }
    //     else
    //     {
    //         return response()->json([
    //             'tarnsporterList' => '',
    //             'response_code' => 1,
    //         ]);
    //     }
    // }



    public function getPendingPIListForILT(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();
        $LocationData = getCurrentLocation()->location_id;
        $location_type = getCurrentLocation()->location_type;

        $itemTypes = getItemType();

            $pi_pending_data = DB::table('pending_purchase_indent_qty as pend')->select([
                'purchase_indent_details.pid_id','pi.pi_id','pi.pi_no','pi.pi_date','pi.to_location_id','item.item_name','item_group.item_group','item.item_type as main_group','purchase_indent_details.indent_qty','unit.unit','pend.pending_qty as pend_pi_qty',
                'purchase_indent_details.remark','admin.person_name as indent_by','location.location_name as to_location', 'cur_loc.location_name as current_location','pi.pi_sequence'
            ])
            ->leftJoin('purchase_indent_details', 'purchase_indent_details.pid_id', '=', 'pend.pid_id')
            ->leftJoin('purchase_indent as pi', 'pi.pi_id', '=', 'purchase_indent_details.pid_pi_id')
            ->leftJoin('item', 'item.id', '=', 'purchase_indent_details.item_id')
            ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
            ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
            ->leftJoin('admin', 'admin.id', '=', 'pi.indent_by_user_id')
            ->leftJoin('location', 'location.location_id', '=', 'pi.to_location_id')
            ->leftJoin('location as cur_loc', 'cur_loc.location_id', '=', 'pi.current_location_id')
            ->whereIn('pi.year_id', $yearIds)
            ->where('pend.pending_qty', '>', 0)
            ->when($location_type != 'HO', function ($q) use ($LocationData) {
                $q->where(function ($sub) use ($LocationData) {
                    $sub->where('pi.to_location_id', $LocationData);
                });
            })
            ->where('item.inter_location_transfer',"Allowed")
            ->get();


        
        if ($pi_pending_data != null) {
            foreach ($pi_pending_data as $cpKey => $cpVal) {
                if ($cpVal->pi_date != null) {
                    $cpVal->pi_date = Date::createFromFormat('Y-m-d', $cpVal->pi_date)->format('d/m/Y');
                }
                $cpVal->main_group = $itemTypes[$cpVal->main_group] ?? $item->main_group;
               
            }
        }


        $pi_pending_data = $pi_pending_data->sortBy('purchase_indent.pi_id')->values();

        

        if ($pi_pending_data != null) {
            return response()->json([
                'response_code' => 1,
                'pi_pending_data'  => $pi_pending_data,            
            ]);
        }else{
            return response()->json([
                'response_code' => 1,
                'pi_pending_data'  => [],            
            ]);

        }

    }

    public function getPendingPIPartDataForILT(Request $request)
    {
   
        $request->pid_ids = explode(',', $request->pid_ids);
        $current_location_id = getCurrentLocation()->location_id;

        $pi_part_data = DB::table('pending_purchase_indent_qty as pend')->select('purchase_indent_details.pid_id','purchase_indent_details.item_id','purchase_indent.pi_no','purchase_indent.pi_date','item.item_name','item_group.item_group','item.item_type as main_group','unit.unit','pend.pending_qty as pend_pi_qty','item_opening.io_stock_qty')
            ->leftJoin('purchase_indent_details', 'purchase_indent_details.pid_id', '=', 'pend.pid_id')
            ->leftJoin('item', 'item.id', '=', 'purchase_indent_details.item_id')
            ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
            ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
            ->leftJoin('purchase_indent', 'purchase_indent.pi_id', '=', 'purchase_indent_details.pid_pi_id')
            ->leftJoin('item_opening', function($join ) use ($current_location_id) {
                $join->on('item_opening.io_item_id', '=', 'purchase_indent_details.item_id')
                    ->where('item_opening.current_location_id', $current_location_id);
            })
            ->whereIn('purchase_indent_details.pid_id', $request->pid_ids)
             ->where('item.inter_location_transfer',"Allowed")
            ->where('pend.pending_qty', '>', 0)
            ->get();

        if($pi_part_data != null)
        {
            $itemTypes = getItemType();
            $finalData = collect();
    // dd($finalData);
    $itemTypes = getItemType();
        $finalData = collect();

        foreach ($pi_part_data as $item) {

            // ✅ પહેલા mapping
            $item->main_group = $itemTypes[$item->main_group] ?? $item->main_group;

            $item->pi_date = $item->pi_date != ""
                ? Date::createFromFormat('Y-m-d', $item->pi_date)->format('d/m/Y')
                : "";

            // 🔥 Industrial X-Ray Films / General → only 1 row
            if (in_array($item->main_group, ['Industrial X-Ray Films', 'General'])) {

                $newItem = clone $item;
                $newItem->single_qty = $item->pend_pi_qty; // optional (total બતાવવા)
                $finalData->push($newItem);

            } else {

                // 🔥 Others → pend_pi_qty જેટલી rows
                for ($i = 0; $i < $item->pend_pi_qty; $i++) {
                    $newItem = clone $item;
                    $newItem->single_qty = 1;
                    $finalData->push($newItem);
                }
            }
        }
            // $pi_part_data = $pi_part_data->map(function ($item) use ($itemTypes) {
            //     $item->pi_date = $item->pi_date != "" ? Date::createFromFormat('Y-m-d',  $item->pi_date)->format('d/m/Y') : "";
            //     $item->main_group = $itemTypes[$item->main_group] ?? $item->main_group;
            //     return $item;
            // });
            return response()->json([
                'response_code' => '1',
                'pi_data' => $finalData,
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '1',
                'pi_data' => []
            ]);
        }

    }


    public function getSrNo(Request $request)
    {
        $itemTypes = getItemType();

        $value = $request->main_group;

        $key = array_search($value, $itemTypes);

        if ($key === false) {
            return response()->json([
                'sr_no_data' => [],
                'response_code' => 0,
                'message' => 'Invalid main group'
            ]);
        }
        $sr_no_data = getSrNo($key, $request->item_id);
        if($sr_no_data){
            return response()->json([
                'sr_no_data' => $sr_no_data,
                'response_code' => 1,
            ]);
        }else{
            return response()->json([
                'sr_no_data' => [],
                'response_code' => 0,
            ]);
        }
        // dd($sr_no_data);
    }

    public function qtyValidation($from_qty, $next_qty, $transaction_mode, $iltd_id, $pid_id = null, $dc_type_id = null)
    {      


        if ($from_qty > 0 && $dc_type_id === 'From Indent' && !empty($pid_id) && $pid_id != 0) {

            $pending_indent_qty = DB::table('pending_purchase_indent_qty')
                ->where('pid_id', $pid_id)
                ->value('pending_qty') ?? 0;

            if ((float)$pending_indent_qty < (float)$from_qty) {
                DB::rollBack();
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => 'Indent Qty. Used.',
                ]);
            }
        }

        if ($next_qty > 0) {

            $pending_po_qty = DB::table('pending_inter_location_transfer_qty')
                ->where('iltd_id', $iltd_id)
                ->value('pending_qty') ?? 0;

            if ((float)$pending_po_qty < (float)$next_qty) {
                DB::rollBack();

                $message = ($transaction_mode === 'D')
                    ? "You Can't Delete, DC Qty. Is Used."
                    : "You Can't Update, DC Qty. Is Used.";

                return response()->json([
                    'response_code'    => '0',
                    'response_message' => $message,
                ]);
            }
        }

        return null;   
    }
}