<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\SupplierDC;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use App\Models\Admin;
use App\Models\Transaction\SupplierDCDetails;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use App\Models\ItemOpening;
use App\Models\File;
use App\Models\RTCamera;

class SupplierDCController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-supplier_dc');
    }

    public function index(SupplierDC $sup_dc_data, Request $request, DataTables $datatables)
    {

        $year_data = getCurrentYearData();
        $current_location = getCurrentLocation()->location_id;
        $sup_dc_data = SupplierDC::select([
            'supplier_dc.sup_dc_id',
            'supplier_dc.sup_dc_type_id',
            'supplier_dc.sup_dc_sequence',
            'supplier_dc.sup_dc_number',
            'supplier_dc.sup_dc_date',
            'supplier_dc.supplier_id',
            'suppliers.supplier_name',
            'supplier_dc.ref_no_date',
            'item.item_name',
            'item_group.item_group',
            'item.item_type',
            'sr.name_for_display',
            \DB::raw("IF(supplier_dc.sup_dc_type_id = 'SQIN from Prod. Area', 'SQIN', unit.unit) as unit"),
            'supplier_dc_details.sr_table_unique_id',
            'supplier_dc_details.sr_table_pk_id',
            'supplier_dc_details.return_qty',
            'supplier_dc_details.aerb_no',
            'supplier_dc_details.application_no',
            'supplier_dc_details.movement_approval',
            'supplier_dc_details.validity',
            'supplier_dc_details.remark',
            'admin.person_name as user_name',
            'supplier_dc.created_on',
            'supplier_dc.created_by',
            'supplier_dc.last_by',
            'supplier_dc.last_on'
        ])
        ->leftJoin('supplier_dc_details','supplier_dc_details.sup_dcd_dc_id','=','supplier_dc.sup_dc_id')
        ->leftJoin('admin','admin.id','=','supplier_dc.prepared_by_user_id')
        ->leftJoin('suppliers','suppliers.id','=','supplier_dc.supplier_id')
        ->leftJoin('item','item.id','=','supplier_dc_details.item_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'supplier_dc_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'supplier_dc_details.sr_table_unique_id');
        })
        ->where('supplier_dc.year_id', $year_data->id)
        ->where('supplier_dc.current_location_id',$current_location);


        $dataTable = DataTables::of($sup_dc_data)
        ->filterColumn('supplier_dc.grn_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'supplier_dc.grn_number');
        })
        ->editColumn('item_type', function($sup_dc_data) {
            return config('app.item_type')[$sup_dc_data->item_type] ?? $sup_dc_data->item_type;
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
        ->editColumn('sup_dc_date', function($sup_dc_data){
            if ($sup_dc_data->sup_dc_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $sup_dc_data->sup_dc_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('supplier_dc.sup_dc_date', function ($q, $k) {
            applyDate($q, $k, 'supplier_dc.sup_dc_date');
        })
        ->filterColumn('unit', function($query, $keyword) {
            $keyword = trim($keyword);
            $query->where(function($q) use ($keyword) {
                $q->where(function($sub_q) use ($keyword) {
                    $sub_q->where('unit.unit', 'like', "%{$keyword}%")
                          ->where(function($sub_q2) {
                              $sub_q2->where('supplier_dc.sup_dc_type_id', '!=', 'SQIN from Prod. Area')
                                     ->orWhereNull('supplier_dc.sup_dc_type_id');
                          });
                });
                if (stripos('SQIN', $keyword) !== false || stripos('SQ IN', $keyword) !== false) {
                    $q->orWhere('supplier_dc.sup_dc_type_id', '=', 'SQIN from Prod. Area');
                }
            });
        })
        // ->filterColumn('unit.unit', function($query, $keyword) {
        //     $keyword = trim($keyword);
        //     $query->where(function($q) use ($keyword) {
        //         $q->where(function($sub_q) use ($keyword) {
        //             $sub_q->where('unit.unit', 'like', "%{$keyword}%")
        //                   ->where(function($sub_q2) {
        //                       $sub_q2->where('supplier_dc.sup_dc_type_id', '!=', 'SQIN from Prod. Area')
        //                              ->orWhereNull('supplier_dc.sup_dc_type_id');
        //                   });
        //         });
        //         if (stripos('SQIN', $keyword) !== false || stripos('SQ IN', $keyword) !== false) {
        //             $q->orWhere('supplier_dc.sup_dc_type_id', '=', 'SQIN from Prod. Area');
        //         }
        //     });
        // })

        ->editColumn('return_qty', function($inquiry_data) {
            return $inquiry_data->return_qty > 0 ? number_format((float)$inquiry_data->return_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('supplier_dc_details.return_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('return_qty')
                    ->orWhere('return_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(return_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('return_qty', 'like', "%{$search}%");
            }
        })

        ->editColumn('movement_approval', function($inquiry_data){
            if(!empty($inquiry_data->movement_approval))
            {
                $documentUrl = asset('storage/' . $inquiry_data->movement_approval);
                $document = '<a href="' . $documentUrl . '" target="_blank">
                <i class="ri-eye-fill action-icon remove_filters_short_qty" ></i>
                </a>';
                return $document;
            }
            return '';
        })
         ->editColumn('validity', function($inquiry_data){
            if ($inquiry_data->validity != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $inquiry_data->validity)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('supplier_dc_details.validity', function ($q, $k) {
            applyDate($q, $k, 'supplier_dc_details.validity');
        })
        
        ->addColumn('options',function($sup_dc_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if(hasAccess("supplier_dc", "print")) {
                    $sup_dc_number = !empty($sup_dc_data->sup_dc_number) ? '_'.str_replace('/', '_', $sup_dc_data->sup_dc_number) : "";

                    $supplier_for_wolh = !empty($sup_dc_data->supplier_name) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $sup_dc_data->supplier_name) : "";

                    $pdfName  = 'Supplier_DC'.$sup_dc_number.$supplier_for_wolh;
                    $encodedId = base64_encode($sup_dc_data->sup_dc_number);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=supplier_dc");
                    $action .= '<li><a  class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }

                if (hasAccess("supplier_dc", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-supplier_dc"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("supplier_dc", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'supplier_dc');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on','validity','movement_approval'])
        ->make(true);
    }

    public function getLatestSupplierDCNumber(Request $request)
    {
        $modal  = SupplierDC::class;
        $sequence = 'sup_dc_sequence';           
        $prefix = 'DC/SUPP';           
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
        $LocationData = getCurrentLocation();

        DB::beginTransaction();

        try {

            $existNumber = SupplierDC::where([
                ['sup_dc_sequence', $request->sup_dc_sequence],
                ['sup_dc_number', $request->sup_dc_number],
                ['year_id', $year_data->id]
            ])->first();

            if ($existNumber) {
                $latestNo = $this->getLatestSupplierDCNumber($request);
                $tmp = $latestNo->getContent();
                $area = json_decode($tmp, true);

                $sup_dc_number = $area['latest_no'];
                $sup_dc_sequence = $area['number'];
            } else {
                $sup_dc_number = $request->sup_dc_number;
                $sup_dc_sequence = $request->sup_dc_sequence;
            }

            $page_id = getMenuIdBassedOnDisplayName('supplier_dc');
            $assign_format_no = getAssignFormateNoForTransaction(
                $LocationData->location_id,
                $page_id->id,
                $request->sup_dc_date
            );

            $sup_dc_data = SupplierDC::create([
                'sup_dc_number'        => $sup_dc_number,
                'sup_dc_sequence'      => $sup_dc_sequence,
                'sup_dc_date'          => $request->sup_dc_date ? Date::createFromFormat('d/m/Y', $request->sup_dc_date)->format('Y-m-d') : null,
                'supplier_id'   => $request->supplier_id,
                'sup_dc_type_id'       => $request->sup_dc_type_id,
                'ref_no_date'           => $request->ref_no_date,
                'mode_of_transport' => $request->mode_of_transport,   
                'transporter'   => $request->transporter,
                'vehicle_no'=> $request->vehicle_no,
                'sp_note'  => $request->sp_note,
                'prepared_by_user_id' => $request->prepared_by_user_id,
                'assign_format_no'  => $assign_format_no ?? null,
                'current_location_id' => $LocationData->location_id ?? null,
                'year_id'           => $year_data->id,
                'company_id'        => Auth::user()->company_id,
                'created_by'        => Auth::user()->id,
                'created_on'        => Carbon::now('Asia/Kolkata'),
            ]);

            $details = json_decode($request->supdc_details_data, true);

            if (!empty($details)) {
                foreach ($details as $row) {

                    if (!empty($row['item_id'])) {

                        $from_qty         = (float)$row['return_qty'];
                        $next_qty         = 0;
                        $transaction_mode = 'I';
                        $sup_dcd_id           = 0;                    
                        $ser_pod_id           = $row['ser_pod_id'] ?? 0;

                        $checkQty = $this->qtyValidation($from_qty, $next_qty, $transaction_mode, $sup_dcd_id, $ser_pod_id, $request->sup_dc_type_id);

                        if ($checkQty) {
                            return $checkQty;
                        }

                        $mode = $row['mode'];

                        if($mode == 'Delete') {
                            continue;
                        }

                        if($mode == 'Insert')
                        {
                            $images = '';
                            $blobImage = '';
                            if(!empty($row['movement_approval_doc']))
                            {
                                $file = new File();
                                $isFound =  $file->getFileFromTemp($row['movement_approval_doc'],$prefix = 'movement_approval');
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
                                    $images = $row['movement_approval_doc'];
                                    $filePath = storage_path('app/public/' . $images);
                                    if (!empty($images) && $file->Is_Files_Exists($images))
                                    {
                                        $blobImage = file_get_contents($filePath);
                                    }
                                }
                            }

                            $rate_unit = ItemOpening::where('current_location_id',$LocationData->location_id)
                            ->where('io_item_id',$row['item_id'])
                            ->sum('io_stock_rate_unit');
                            $amount = $rate_unit * $row['return_qty'];

                             $previousAerb = null;
                             $previousApp = null;
                             $previousMove = null;
                             $previousMoveBlob = null;
                             $previousVal = null;

                             if (!empty($row['sr_table_unique_id']) && $row['sr_table_unique_id'] === 'rt_camera' && !empty($row['sr_table_pk_id'])) {
                                 $camera = \App\Models\RTCamera::find($row['sr_table_pk_id']);
                                 if ($camera) {
                                     $previousAerb = $camera->rt_aerb_no;
                                     $previousApp = $camera->application_no;
                                     $previousMove = $camera->movement_approval;
                                     $previousMoveBlob = $camera->movement_approval_blob;
                                     $previousVal = $camera->validity;

                                     // Update the camera with the new values
                                     $camera->rt_aerb_no = $row['aerb_no'] ?? null;
                                     $camera->application_no = $row['application_no'] ?? null;
                                     $camera->movement_approval = $images != '' ? $images : null;
                                     $camera->movement_approval_blob = $blobImage ? $blobImage : null;
                                     $camera->validity = !empty($row['validity']) ? Date::createFromFormat('d/m/Y', $row['validity'])->format('Y-m-d') : null;
                                     $camera->save();
                                 }
                             }

                             $sup_dc_details_data = SupplierDCDetails::create([
                                 'sup_dcd_dc_id'     => $sup_dc_data->sup_dc_id,
                                 'ser_pod_id'     => !empty($row['ser_pod_id']) ? $row['ser_pod_id'] : null,
                                 'item_id'    => $row['item_id'],
                                 'sr_table_unique_id' => !empty($row['sr_table_unique_id']) ? $row['sr_table_unique_id'] : null,
                                 'sr_table_pk_id'     => !empty($row['sr_table_pk_id']) ? $row['sr_table_pk_id'] : null,
                                 'return_qty'        => $row['return_qty'],
                                 'rate_unit'        => $rate_unit ?? null,
                                 'amount'           => $amount ?? null,
                                 'aerb_no'          => $row['aerb_no'] ?? null,
                                 'application_no'   => $row['application_no'] ?? null,
                                 'movement_approval' =>  $images != '' ? $images : null,
                                 'movement_approval_blob' =>  $blobImage  ? $blobImage : null,
                                 'validity' => !empty($row['validity']) ? Date::createFromFormat('d/m/Y', $row['validity'])->format('Y-m-d') : null,
                                 'previous_aerb_no' => $previousAerb,
                                 'previous_application_no' => $previousApp,
                                 'previous_movement_approval' => $previousMove,
                                 'previous_movement_approval_blob' => $previousMoveBlob,
                                 'previous_validity' => $previousVal,
                                 'remark'     => $row['remark'] ?? null,
                                 'stock_effect_type' => ($request->sup_dc_type_id == 'SQIN from Prod. Area') ? 'prod. area' : 'main stock',
                                 'for_calibration' => $row['for_calibration'] ?? 'No',
                                 'previous_status_transaction_id' => null,
                                 'current_status_transaction_id'  => null,
                             ]);
                        
                            if ($sup_dc_details_data->stock_effect_type == 'prod. area') {
                                stockEffectSQIN($LocationData->location_id, $row['item_id'], $row['item_id'], $row['return_qty'], 0, 'Insert', 'D', 'Supplier DC', $sup_dc_details_data->sup_dcd_id);
                            } else {
                                stockEffect($LocationData->location_id, $row['item_id'],$row['item_id'],$row['return_qty'],0,$amount,0,'Insert','D','Supplier DC', $sup_dc_details_data->sup_dcd_id, $row['sr_table_unique_id'] ?? null, $row['sr_table_pk_id'] ?? null);
                            }
                            if($request->sup_dc_type_id == "Returnable - Service PO"){
                                if(!empty($row['sr_table_pk_id'])){
                                    changeStatusAndLocationForSrNo($row['sr_table_unique_id'], $row['sr_table_pk_id'], 'Outside', 'Service PO', $LocationData->location_id, 'Insert', $sup_dc_details_data);
                                }

                            }else{

                                if(!empty($row['sr_table_pk_id'])){
                                    changeStatusAndLocationForSrNo($row['sr_table_unique_id'], $row['sr_table_pk_id'], 'Outside', 'Active', $LocationData->location_id, 'Insert', $sup_dc_details_data);
                                } 
                            }
                        }
                    }
                }
            }
            
            if($sup_dc_details_data->save())
            {
                DB::commit();                
                $get_supplier = SupplierDC::select('suppliers.supplier_name')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'supplier_dc.supplier_id')
                ->where('supplier_dc.supplier_id', $sup_dc_data->supplier_id)
                ->first();

                $sup_dc_number = !empty($sup_dc_data->sup_dc_number) ? '_'.str_replace('/', '_', $sup_dc_data->sup_dc_number) : "";

                $customer_for_wolh = !empty($get_supplier) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $get_supplier->supplier_name) : "";

                $pdf_name = 'Supplier_DC'.$sup_dc_number.$customer_for_wolh;

                GeneratePdf($sup_dc_data->sup_dc_id,$pdf_name,'supplier_dc','add');
                $encodedId = base64_encode($sup_dc_data->sup_dc_id);
                if(hasAccess("supplier_dc", "print")){
                    
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=supplier_dc");
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

        } catch (\Exception $e) {

            report($e);
            DB::rollBack();
            $message = $e->getMessage();
            if (in_array($message, [
                    'Insufficient Stock',
                    'Invalid Location Code',
                    'Invalid Item'
                ]) || str_contains($message, 'Sr. No. Is Already') || str_contains($message, "You Can't Delete")
            ){
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }else{
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Error Occured Record Not Inserted',
                    'original_error' => $e->getMessage()
                ]);
            }
        }
    }

    public function edit(Request $request)
    {
        $isAnyPartInUse = false;
        $itemTypes = getItemType();
        $current_location_id = getCurrentLocation()->location_id;

        $supplier_dc_data =  DB::select('CALL supplier_dc_master(?)', [$request->id]);
        if(!empty($supplier_dc_data))
        {
            $supplier_dc_data = $supplier_dc_data[0];
            $supplier_dc_data->sup_dc_date = $supplier_dc_data->sup_dc_date != "" ? Date::createFromFormat('Y-m-d',  $supplier_dc_data->sup_dc_date)->format('d/m/Y') : "";

            $supplier_dc_number = !empty($supplier_dc_data->sup_dc_number) ? '_'.str_replace('/', '_', $supplier_dc_data->sup_dc_number) : "";

            $customer_for_wolh = !empty($supplier_dc_data) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $supplier_dc_data->supplier_name) : "";

            $supplier_dc_data->pdf_name = 'Supplier_DC'.$supplier_dc_number.$customer_for_wolh;
            if(isset($supplier_dc_data->cmp_logo))
            {
                $supplier_dc_data->cmp_logo = base64_encode($supplier_dc_data->cmp_logo);
            }
            
        }
        
        $supplier_dc_details_data = DB::select('CALL supplier_dc_details(?)', [$request->id]);
       
        if($supplier_dc_details_data){
            foreach($supplier_dc_details_data as $dKey => $dVal)
            {
                $dVal->ser_po_date = $dVal->ser_po_date != "" ? Date::createFromFormat('Y-m-d',  $dVal->ser_po_date)->format('d/m/Y') : "";
                // $dVal->main_group = $itemTypes[$dVal->main_group] ?? $dVal->main_group;
                if($supplier_dc_data->sup_dc_type_id == "Returnable - Service PO") {
                    $dVal->pending_qty = $dVal->pending_qty + $dVal->return_qty;
                }
                $dVal->name_for_display = $dVal->sr_no;
                $dVal->io_stock_qty = $dVal->io_stock_qty + $dVal->return_qty;
                $dVal->io_stock_qty = number_format((float)$dVal->io_stock_qty, 3, '.', '');

                if (isset($dVal->validity) && $dVal->validity != "" && $dVal->validity != "0000-00-00") {
                    $dVal->validity = Date::createFromFormat('Y-m-d', $dVal->validity)->format('d/m/Y');
                } else {
                    $dVal->validity = "";
                }

                $dVal->movement_approval_doc = $dVal->movement_approval;
                $dVal->movement_approval_blob = base64_encode($dVal->movement_approval_blob);


              $PendingQty = DB::table('pending_supplier_dc_qty as pend')->where("pend.sup_dcd_id",$dVal->sup_dcd_id)->value("pend.pending_qty");

                $total_qty = $dVal->return_qty;     
                if ($PendingQty === null) {
                    $PendingQty = $total_qty;
                }                     
                $isFound = $total_qty - $PendingQty;

                if($isFound > 0){
                    $dVal->in_use = true;
                    $dVal->used_qty = $isFound;
                    $isAnyPartInUse = true;
                } else {
                    $dVal->in_use = false;
                    $dVal->used_qty = 0;
                }

            }
            unset($dVal->sr_no);
        }
        if($isAnyPartInUse == true){
            $supplier_dc_data->in_use = true;
        } else {
            $supplier_dc_data->in_use = false;
        }
        if($supplier_dc_data)
        {
            return response()->json([
                'supplier_dc_data'         => $supplier_dc_data,
                'supplier_dc_details_data' => $supplier_dc_details_data,
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
            'sup_dc_sequence' => [
                'required',
                'max:155',
                Rule::unique('supplier_dc')
                    ->where(function ($query) use ($year_data, $current_location_id) {
                        $query->where('year_id', $year_data->id)
                            ->where('current_location_id', $current_location_id);
                    })
                    ->ignore($request->id, 'sup_dc_id')
            ],
        ], [
            'sup_dc_sequence.unique' => 'Duplicate DC No. Found.',
            'sup_dc_sequence.required' => 'Enter DC No.',
        ]);

        try
        {           

           

            $sup_dc_data = SupplierDC::where('sup_dc_id', $request->id)->update([
               'sup_dc_number'        => $request->sup_dc_number,
                'sup_dc_sequence'      => $request->sup_dc_sequence,
                'sup_dc_date'          => $request->sup_dc_date ? Date::createFromFormat('d/m/Y', $request->sup_dc_date)->format('Y-m-d') : null,
                'supplier_id'   => $request->supplier_id,
                'sup_dc_type_id'       => $request->sup_dc_type_id,
                'ref_no_date'           => $request->ref_no_date,
                'mode_of_transport' => $request->mode_of_transport,   
                'transporter'   => $request->transporter,
                'vehicle_no'=> $request->vehicle_no,
                'sp_note'  => $request->sp_note,
                'prepared_by_user_id' => $request->prepared_by_user_id,
                // 'assign_format_no'  => $assign_format_no ?? null,
                'current_location_id' => $current_location_id ?? null,
                'year_id'           => $year_data->id,
                'company_id'        => Auth::user()->company_id,
                'last_on'               => Carbon::now('Asia/Kolkata'),
                'last_by'               => Auth::id(),
            ]);


            if(!$sup_dc_data)
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }

            $supdc_details_data = json_decode($request->supdc_details_data, true);

            if(!empty($supdc_details_data))
            {
                foreach($supdc_details_data as $ctVal)
                {
                    if(empty($ctVal)) continue;

                    $mode = $ctVal['mode'];
                        
                    $images = '';
                    $blobImage = '';
                    
                    if($mode == 'Insert')
                    {
                        $rate_unit = ItemOpening::where('current_location_id',$current_location_id)
                        ->where('io_item_id',$ctVal['item_id'])
                        ->sum('io_stock_rate_unit');
                        $amount = $rate_unit * $ctVal['return_qty'];

                        $from_qty         = (float)$ctVal['return_qty'];
                        $next_qty         = 0;
                        $transaction_mode = 'I';
                        $sup_dcd_id           = 0;
                        $ser_pod_id           = $ctVal['ser_pod_id'] ?? 0;

                        $checkQty = $this->qtyValidation($from_qty, $next_qty, $transaction_mode, $sup_dcd_id, $ser_pod_id, $request->sup_dc_type_id);
                        if ($checkQty) {
                            return $checkQty;
                        }


                        if(!empty($ctVal['movement_approval_doc']))
                        {
                            $file = new File();
                            $isFound =  $file->getFileFromTemp($ctVal['movement_approval_doc'],$prefix = "movement_approval");
                            if($isFound !== false)
                            {
                                $images = $isFound;
                                $filePath = storage_path('app/public/' . $images);
                                if(!empty($images) && $file->Is_Files_Exists($images))
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

                        $stock_effect_type = $ctVal['stock_effect_type'] ?? (($request->sup_dc_type_id == 'SQIN from Prod. Area') ? 'prod. area' : 'main stock');
                        $ctVal['stock_effect_type'] = $stock_effect_type;

                         $previousAerb = null;
                         $previousApp = null;
                         $previousMove = null;
                         $previousMoveBlob = null;
                         $previousVal = null;

                         if (!empty($ctVal['sr_table_unique_id']) && $ctVal['sr_table_unique_id'] === 'rt_camera' && !empty($ctVal['sr_table_pk_id'])) {
                              $camera = RTCamera::find($ctVal['sr_table_pk_id']);
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

                         SupplierDCDetails::create([
                             'sup_dcd_dc_id'       => $request->id,
                             'ser_pod_id'     => !empty($ctVal['ser_pod_id']) ? $ctVal['ser_pod_id'] : null,
                             'item_id'    => $ctVal['item_id'],
                             'sr_table_unique_id' => !empty($ctVal['sr_table_unique_id']) ? $ctVal['sr_table_unique_id'] : null,
                             'sr_table_pk_id'     => !empty($ctVal['sr_table_pk_id']) ? $ctVal['sr_table_pk_id'] : null,
                             'return_qty'        => $ctVal['return_qty'],
                             'rate_unit'         => $rate_unit ?? null,
                             'amount'            => $amount ?? null,
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
                             'remark'            => $ctVal['remark'] ?? null,
                             'stock_effect_type' => $ctVal['stock_effect_type'],
                             'for_calibration' => $ctVal['for_calibration'] ?? 'No',
                             'previous_status_transaction_id' => null,
                             'current_status_transaction_id'  => null,
                         ]);

                        if ($ctVal['stock_effect_type'] == 'prod. area') {
                            stockEffectSQIN($current_location_id, $ctVal['item_id'], $ctVal['item_id'], $ctVal['return_qty'], 0, 'Insert', 'D', 'Supplier DC', $request->id);
                        } else {
                            stockEffect($current_location_id, $ctVal['item_id'],$ctVal['item_id'],$ctVal['return_qty'],0,$amount,0,'Insert','D','Supplier DC',$request->id, $ctVal['sr_table_unique_id'] ?? null, $ctVal['sr_table_pk_id'] ?? null);
                        }

                        if(!empty($ctVal['sr_table_pk_id'])){
                            if($request->sup_dc_type_id =="Returnable - Service PO"){
                                changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Outside', 'Service PO', $current_location_id, 'Insert', $sup_dc_details_data);
                            }else{
                                
                                changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Outside', 'Active', $current_location_id, 'Insert', $sup_dc_details_data);
                            }
                        } 
                    }

                 
                    elseif($mode == 'Update')
                    {
                        if(!empty($ctVal['sup_dcd_id']))
                        {
                            $olddata = SupplierDCDetails::where('sup_dcd_id', $ctVal['sup_dcd_id'])->select('amount','rate_unit','item_id','return_qty')
                            ->first();

                            $return_qty       = (float)$ctVal['return_qty'];
                            $from_qty         = max(0, $return_qty - $olddata->return_qty);   // increase
                            $next_qty         = max(0, $olddata->return_qty - $return_qty);   // decrease
                            $transaction_mode = 'U';
                            $sup_dcd_id           = $ctVal['sup_dcd_id'];
                            $ser_pod_id           = $ctVal['ser_pod_id'] ?? 0;

                            $checkQty = $this->qtyValidation($from_qty, $next_qty, $transaction_mode, $sup_dcd_id, $ser_pod_id, $request->sup_dc_type_id);
                            if ($checkQty) {
                                return $checkQty;
                            }

                            $imgs = SupplierDCDetails::where('sup_dcd_id', $ctVal['sup_dcd_id'])->value('movement_approval');
                            $file = new File();
                            $images = '';
                            $blobImage = '';

                            if($ctVal['movement_approval_doc'] != '' && $ctVal['movement_approval_doc'] != null){
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
                                            

                            $stock_effect_type = $ctVal['stock_effect_type'] ?? $olddata->stock_effect_type ?? (($request->sup_dc_type_id == 'SQIN from Prod. Area') ? 'prod. area' : 'main stock');
                            $ctVal['stock_effect_type'] = $stock_effect_type;

                            SupplierDCDetails::where('sup_dcd_id', $ctVal['sup_dcd_id'])->update([
                                'sup_dcd_dc_id'       => $request->id,
                                 'ser_pod_id'     => !empty($ctVal['ser_pod_id']) ? $ctVal['ser_pod_id'] : null,
                                'item_id'    => $ctVal['item_id'],
                                'sr_table_unique_id' => !empty($ctVal['sr_table_unique_id']) ? $ctVal['sr_table_unique_id'] : null,
                                'sr_table_pk_id'     => !empty($ctVal['sr_table_pk_id']) ? $ctVal['sr_table_pk_id'] : null,
                                'return_qty'        => $ctVal['return_qty'],
                                'rate_unit'        =>$olddata->rate_unit ?? null,
                                'amount'           => $ctVal['return_qty'] * $olddata->rate_unit,
                                'aerb_no'          => $ctVal['aerb_no'] ?? null,
                                'application_no'   => $ctVal['application_no'] ?? null,
                                'movement_approval' =>  $images != '' ? $images : null,
                                'movement_approval_blob' =>  $blobImage  ? $blobImage : null,
                                'validity' => !empty($ctVal['validity']) ? Date::createFromFormat('d/m/Y', $ctVal['validity'])->format('Y-m-d') : null,
                                'remark'           => !empty($ctVal['remark']) ? $ctVal['remark'] : null,
                                'stock_effect_type' => $ctVal['stock_effect_type'],
                            ]);
                            // dd($olddata->amount , $ctVal['amount']);
                            if ($ctVal['stock_effect_type'] == 'prod. area') {
                                stockEffectSQIN($current_location_id, $ctVal['item_id'], $olddata->item_id, $ctVal['return_qty'], $olddata->return_qty, 'Update', 'D', 'Supplier DC', $ctVal['sup_dcd_id']);
                            } else {
                                stockEffect($current_location_id,$ctVal['item_id'], $olddata->item_id,$ctVal['return_qty'],$olddata->return_qty,$ctVal['return_qty'] * $olddata->rate_unit,$olddata->amount,'Update','D','Supplier DC',$ctVal['sup_dcd_id'], $ctVal['sr_table_unique_id'] ?? $olddata->sr_table_unique_id ?? null, $ctVal['sr_table_pk_id'] ?? $olddata->sr_table_pk_id ?? null);
                            }

                            
                        }
                    }

                   
                    elseif($mode == 'Delete')
                    {
                        if(!empty($ctVal['sup_dcd_id']))
                        {
                            $sup_dc_data =  DB::select('CALL supplier_dc_used_list(?)', [$request->id]);
            
                            if(!empty($sup_dc_data)){ 

                                DB::rollBack();
                                return response()->json([
                                    'response_code' => '0',
                                    'response_message' => "You Can't Delete, Supplier DC Is Used In " . $sup_dc_data[0]->table_name . ".",
                                ]);
                            }

                            $olddata = SupplierDCDetails::where('sup_dcd_id',$ctVal['sup_dcd_id'])->select('amount','rate_unit','item_id','return_qty','stock_effect_type')
                            ->first();

                            if ($olddata->stock_effect_type == 'prod. area') {
                                stockEffectSQIN($current_location_id, $ctVal['item_id'], $ctVal['item_id'], 0, $olddata->return_qty, 'Delete', 'D', 'Supplier DC', $ctVal['sup_dcd_id']);
                            } else {
                                stockEffect($current_location_id,$ctVal['item_id'],$ctVal['item_id'],0,$olddata->return_qty,0,$olddata->amount,'Delete','D','Supplier DC',$ctVal['sup_dcd_id'], $ctVal['sr_table_unique_id'] ?? $olddata->sr_table_unique_id ?? null, $ctVal['sr_table_pk_id'] ?? $olddata->sr_table_pk_id ?? null);
                            }



                             $detailRecord = SupplierDCDetails::find($ctVal['sup_dcd_id']);

                             if($ctVal['sr_table_unique_id'] != null || $ctVal['sr_table_unique_id'] != ''){

                                 if($request->sup_dc_type_id == "Returnable - Service PO") {
                                     changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'],$ctVal['sr_table_pk_id'],'Outside','Service PO',$current_location_id,'Delete', $detailRecord);
                                 }else{
                                     changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'],$ctVal['sr_table_pk_id'],'Outside','Active',$current_location_id,'Delete', $detailRecord);
                                 }

                                  if ($ctVal['sr_table_unique_id'] === 'rt_camera' && $detailRecord && !empty($detailRecord->sr_table_pk_id)) {
                                      $camera = \App\Models\RTCamera::find($detailRecord->sr_table_pk_id);
                                      if ($camera) {
                                          $camera->rt_aerb_no = $detailRecord->previous_aerb_no;
                                          $camera->application_no = $detailRecord->previous_application_no;
                                          $camera->movement_approval = $detailRecord->previous_movement_approval;
                                          $camera->movement_approval_blob = $detailRecord->previous_movement_approval_blob;
                                          $camera->validity = $detailRecord->previous_validity;
                                          $camera->save();
                                      }
                                  }
                             }

                             if($detailRecord && $detailRecord->movement_approval != '')
                             {
                                 $file = new File();
                                 $file->delete_file($detailRecord->movement_approval);
                             }
                             
                             if ($detailRecord) {
                                 $detailRecord->delete();
                             }
                        }
                    }
                }
            }

            DB::commit();

            $get_suppliers = SupplierDC::select('suppliers.supplier_name')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'supplier_dc.supplier_id')
                ->where('supplier_dc.supplier_id', $request->supplier_id)
                ->first();
                $sup_dc_number = !empty($request->sup_dc_number) ? '_'.str_replace('/', '_', $request->sup_dc_number) : "";
                
                $customer_for_wolh = !empty($get_suppliers) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $get_suppliers->supplier_name) : "";
                
                $pdf_name = 'Supplier_DC'.$sup_dc_number.$customer_for_wolh;
            GeneratePdf($request->id,$pdf_name,'supplier_dc','edit');
            $encodedId = base64_encode($request->id);
            
            if(hasAccess("supplier_dc", "print")){
                
                $url = url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=supplier_dc");
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

            $message = $e->getMessage();

            if (in_array($message, [
                    'Insufficient Stock',
                    'Invalid Location Code',
                    'Invalid Item'
                ]) || str_contains($message, 'Sr. No. Is Already') || str_contains($message, "You Can't Delete")
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
        try
        {

            $sup_dc_data =  DB::select('CALL supplier_dc_used_list(?)', [$request->id]);


            if(!empty($sup_dc_data)){ 
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Supplier DC Is Used In " . $sup_dc_data[0]->table_name . ".",
                ]);
            }

            $LocationData = getCurrentLocation()->location_id;
            
            $SupplierDC = SupplierDC::where('sup_dc_id', $request->id)->first();
            $SupplierDCDetails = SupplierDCDetails::where('sup_dcd_dc_id', $request->id)->get();
            if($SupplierDCDetails->isNotEmpty()){
                foreach($SupplierDCDetails as $item){

                    if ($item->stock_effect_type == 'prod. area') {
                        stockEffectSQIN($LocationData, $item->item_id, $item->item_id, 0, $item->return_qty, 'Delete', 'D', 'Supplier DC', $item->sup_dcd_id);
                    } else {
                        stockEffect($LocationData,$item->item_id,$item->item_id,0,$item->return_qty,0,$item->amount,'Delete','D','Supplier DC',$item->sup_dcd_id, $item->sr_table_unique_id ?? null, $item->sr_table_pk_id ?? null);
                    }

                    if($item->sr_table_unique_id != null || $item->sr_table_unique_id != ''){

                        if($SupplierDC->sup_dc_type_id == "Returnable - Service PO") {
                            // dd($item->sr_table_unique_id,$item->sr_table_pk_id, $LocationData,$item);
                            changeStatusAndLocationForSrNo($item->sr_table_unique_id,$item->sr_table_pk_id,'Outside','Service PO',$LocationData,'Delete', $item);
                        }else{
                            changeStatusAndLocationForSrNo($item->sr_table_unique_id,$item->sr_table_pk_id,'Outside','Active',$LocationData,'Delete', $item);
                        }

                        if ($item->sr_table_unique_id === 'rt_camera' && !empty($item->sr_table_pk_id)) {
                             $camera = RTCamera::find($item->sr_table_pk_id);
                             if ($camera) {
                                 $camera->rt_aerb_no = $item->previous_aerb_no;
                                 $camera->application_no = $item->previous_application_no;
                                 $camera->movement_approval = $item->previous_movement_approval;
                                 $camera->movement_approval_blob = $item->previous_movement_approval_blob;
                                 $camera->validity = $item->previous_validity;
                                 $camera->save();
                             }
                         }
                    }
                }
            }


            
            SupplierDC::where('sup_dc_id',$request->id)->delete();
            SupplierDCDetails::where('sup_dcd_dc_id',$request->id)->delete();

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
                ]) || str_contains($message, 'Sr. No. Is Already') || str_contains($message, "You Can't Delete"))
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


    public function getPendingSupplierForSupplierDC(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();
        $current_location = getCurrentLocation();

        if($request->sup_dc_type_id == "Returnable - Service PO"){
            $get_ser_po_supplier = DB::table('pending_service_po_qty as pend')->select([
            'suppliers.supplier_name','suppliers.id',
            ])
            ->leftJoin('service_po_details', 'service_po_details.ser_pod_id', '=', 'pend.ser_pod_id')
            ->leftJoin('service_po', 'service_po.ser_po_id', '=', 'service_po_details.ser_pod_po_id')           
            ->leftJoin('suppliers', 'suppliers.id', '=', 'service_po.supplier_id')            
            ->whereIn('service_po.year_id', $yearIds)
            ->where('service_po.for_location_id', $current_location->location_id)
            ->where('pend.pending_qty', '>', 0)
            ->distinct()
            ->orderBy('suppliers.supplier_name', 'asc')
            ->get();
           
        }else{
            $get_ser_po_supplier =  Supplier::select('id','supplier_name')->orderBy('supplier_name','asc')->get();
        }

        return response()->json([
            'response_code' => 1,
            'get_ser_po_supplier'  => $get_ser_po_supplier,
        ]);
    }

    public function getPendingservicePOListForSupplierDC(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();
        $current_location = getCurrentLocation();

        $itemTypes = getItemType();

        $dc_data = DB::table('pending_service_po_qty as pend')
        ->select(['service_po.ser_po_id','service_po.ser_po_number','service_po.ser_po_date','service_po.purpose','service_po.ref_no_date','bill_to.location_name as bill_to','for_location.location_name as for_location','item.item_name','item_group.item_group','item.item_type as main_group','pend.pending_qty','unit.unit','service_po_details.remark','admin.person_name as prepared_by','sr.name_for_display','pend.ser_pod_id','service_po_details.for_calibration'])
        ->leftJoin('service_po_details','service_po_details.ser_pod_id','=','pend.ser_pod_id')
        ->leftJoin('service_po', 'service_po.ser_po_id', '=', 'service_po_details.ser_pod_po_id')    
        ->leftJoin('item','item.id','=','service_po_details.item_id')  
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        ->leftJoin('admin', 'admin.id', '=', 'service_po.prepared_by_user_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')  
        ->leftJoin('location as bill_to', 'bill_to.location_id', '=', 'service_po.bill_to_id')
        ->leftJoin('location as for_location', 'for_location.location_id', '=', 'service_po.for_location_id')
          ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'service_po_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'service_po_details.sr_table_unique_id');
        })
        ->where('service_po.supplier_id',$request->supplier_id)
        ->Where('service_po.for_location_id', $current_location->location_id)
        ->whereIn('service_po.year_id',$yearIds)  
        ->where('pend.pending_qty', '>', 0)
        ->get();

        
        if ($dc_data != null) {
            foreach ($dc_data as $cpKey => $cpVal) {
                if ($cpVal->ser_po_date != null) {
                    $cpVal->ser_po_date = Date::createFromFormat('Y-m-d', $cpVal->ser_po_date)->format('d/m/Y');
                }
                $cpVal->main_group = $itemTypes[$cpVal->main_group] ?? $item->main_group;
               
            }
        }


        $dc_data = $dc_data->sortBy('service_po.ser_po_id')->values();

        

        if ($dc_data != null) {
            return response()->json([
                'response_code' => 1,
                'dc_data'  => $dc_data,            
            ]);
        }else{
            return response()->json([
                'response_code' => 1,
                'dc_data'  => [],            
            ]);

        }

    }

    public function getPendingServicePOForSupplierDC(Request $request){

        $yearIds = getCompanyYearIdsToTill();
        $itemTypes = getItemType();
        $request->ser_pod_ids = explode(',', $request->ser_pod_ids);
        $current_location = getCurrentLocation();
        
        $dc_data = DB::table('pending_service_po_qty as pend')
        ->select(['service_po.ser_po_id','service_po.ser_po_number','service_po.ser_po_date','item.item_name','item_group.item_group','item.item_type as main_group','pend.pending_qty','unit.unit','sr.name_for_display','service_po_details.sr_table_unique_id','service_po_details.sr_table_pk_id','pend.ser_pod_id','service_po_details.item_id','item_opening.io_stock_qty','service_po_details.for_calibration'])
        ->leftJoin('service_po_details','service_po_details.ser_pod_id','=','pend.ser_pod_id')
        ->leftJoin('service_po', 'service_po.ser_po_id', '=', 'service_po_details.ser_pod_po_id')    
        ->leftJoin('item','item.id','=','service_po_details.item_id')  
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        ->leftJoin('admin', 'admin.id', '=', 'service_po.prepared_by_user_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')  
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'service_po_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'service_po_details.sr_table_unique_id');
        })
        ->leftJoin('item_opening', function($join ) use ($current_location) {
            $join->on('item_opening.io_item_id', '=', 'service_po_details.item_id')
                ->where('item_opening.current_location_id', $current_location->location_id);
        })
        ->where('service_po.for_location_id', $current_location->location_id)
        ->where('pend.pending_qty', '>', 0)
        ->whereIn('service_po.year_id',$yearIds)  
        ->whereIn('pend.ser_pod_id', $request->ser_pod_ids)
        ->get();

      
        if ($dc_data != null) {
            foreach ($dc_data as $cpKey => $cpVal) {

                if ($cpVal->ser_po_date != null) {
                    $cpVal->ser_po_date = Date::createFromFormat('Y-m-d', $cpVal->ser_po_date)->format('d/m/Y');
                }               

                $cpVal->main_group = $itemTypes[$cpVal->main_group] ?? $item->main_group;
            }
        }

        $dc_data = $dc_data->sortBy('ser_po_id')
        ->sortBy('ser_pod_id')
        ->values();

       
        
        if ($dc_data != null) {
            return response()->json([
                'response_code' => '1',
                'dc_data' => $dc_data,
               
            ]);
        } else {
            return response()->json([
                'response_code' => '1',
                'dc_data' => []
            ]);
        }
    }

    public function qtyValidation($from_qty, $next_qty, $transaction_mode, $sup_dcd_id, $ser_pod_id = null, $sup_dc_type_id = null)
    {      

        // ===================== 1. Suppllier DC VALIDATION FOR  SERVICE PO =====================
        if ($from_qty > 0 && $sup_dc_type_id === 'Returnable - Service PO' && !empty($ser_pod_id) && $ser_pod_id != 0) {

            $pending_service_po_qty = DB::table('pending_service_po_qty')
                ->where('ser_pod_id', $ser_pod_id)
                ->value('pending_qty') ?? 0;

            if ((float)$pending_service_po_qty < (float)$from_qty) {
                DB::rollBack();
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => 'PO. Used.',
                ]);
            }
        }

        // ===================== 2. Supplier DC VALIDATION FOR GRN Supplier=====================
        if ($next_qty > 0) {

            $pending_return_qty = DB::table('pending_supplier_dc_qty')
                ->where('sup_dcd_id', $sup_dcd_id)
                ->value('pending_qty') ?? 0;

            if ((float)$pending_return_qty < (float)$next_qty) {
                DB::rollBack();

                $message = ($transaction_mode === 'D')
                    ? "You Can't Delete, Return Qty. Is Used."
                    : "You Can't Update, Return Qty. Is Used.";

                return response()->json([
                    'response_code'    => '0',
                    'response_message' => $message,
                ]);
            }
        }

        return null;   // No error
    }

   
}