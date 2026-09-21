<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\GRNLocation;
use App\Models\Transaction\GRNLocationDetails;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use App\Models\Admin;
use App\Models\Transaction\GRNSupplierDetails;
use App\Models\Item;
use App\Models\MaterialMpt;
use App\Models\ItemOpening;
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



class GRNLocationController extends Controller
{
    //
    public function manage()
    {
        return view('manage.transaction.manage-grn_location');
    }

    public function index(GRNLocation $grn_loc_data, Request $request, DataTables $datatables)
    {

        $year_data = getCurrentYearData();
        $current_location = getCurrentLocation()->location_id;
        $grn_loc_data = GRNLocation::select([
            'grn_location.grn_loc_id',
            'inter_location_transfer.dc_type_id',
            'grn_location_details.stock_effect_type',
            'grn_location.grn_loc_number',
            'grn_location.grn_loc_sequence',
            'grn_location.grn_loc_number',
            'grn_location.grn_loc_date',
            'to_location.location_name',
            'inter_location_transfer.dc_number',
            'inter_location_transfer.dc_date',
            'item.item_name',
            'item_group.item_group',
            'item.item_type',
            'sr.name_for_display',
            'grn_location_details.qty',
            'grn_location_details.remark',
            'unit.unit',           
            'prepared_by.person_name as prepared_by',
            'grn_location.created_on',
            'grn_location.created_by',
            'grn_location.last_by',
            'grn_location.last_on'
        ])
        ->leftJoin('grn_location_details','grn_location_details.grn_locd_grn_id','=','grn_location.grn_loc_id')
        ->leftJoin('admin as prepared_by','prepared_by.id','=','grn_location.prepared_by_user_id')
        ->leftJoin('item','item.id','=','grn_location_details.item_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')        
        ->leftJoin('inter_location_transfer_details', 'inter_location_transfer_details.inter_location_transfer_details_id', '=', 'grn_location_details.inter_location_transfer_details_id')
        ->leftJoin('inter_location_transfer', 'inter_location_transfer.ilt_id', '=', 'inter_location_transfer_details.inter_location_transfer_id')
        ->leftJoin('location as to_location', 'to_location.location_id', '=', 'inter_location_transfer.to_location_id')
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'grn_location_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'grn_location_details.sr_table_unique_id');
        })
        ->where('grn_location.year_id', $year_data->id)
        ->where('grn_location.current_location_id',$current_location);


        $dataTable = DataTables::of($grn_loc_data)
        ->filterColumn('grn_location.grn_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'grn_location.grn_number');
        })
        
         ->editColumn('item_type', function($grn_loc_data) {
            return config('app.item_type')[$grn_loc_data->item_type] ?? $grn_loc_data->item_type;
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
        ->editColumn('grn_loc_date', function($grn_loc_data){
            if ($grn_loc_data->grn_loc_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $grn_loc_data->grn_loc_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('grn_location.grn_loc_date', function ($q, $k) {
            applyDate($q, $k, 'grn_location.grn_loc_date');
        })
       
        ->editColumn('dc_date', function($grn_loc_data){
            if ($grn_loc_data->dc_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $grn_loc_data->dc_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('inter_location_transfer.dc_date', function ($q, $k) {
            applyDate($q, $k, 'inter_location_transfer.dc_date');
        })

        ->editColumn('qty', function($grn_loc_data) {
            return $grn_loc_data->qty > 0 ? number_format((float)$grn_loc_data->qty, 3, '.', '') : number_format((float) 0, 3, '.', '');
        })
        ->filterColumn('grn_location_details.qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('qty')
                    ->orWhere('qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('qty', 'like', "%{$search}%");
            }
        })
        ->editColumn('unit', function($grn_loc_data) {
            if ($grn_loc_data->stock_effect_type == 'prod. area') {
                return 'SQIN';
            }
            return $grn_loc_data->unit;
        })
        ->filterColumn('unit.unit', function($query, $keyword) {
            $search = trim($keyword);
            $query->where(function($q) use ($search) {
                $q->where(function($sub_q) use ($search) {
                    $sub_q->where('unit.unit', 'like', "%{$search}%")
                          ->where(function($sub_q2) {
                              $sub_q2->where('grn_location_details.stock_effect_type', '!=', 'prod. area')
                                     ->orWhereNull('grn_location_details.stock_effect_type');
                          });
                });
                if (stripos('SQIN', $search) !== false || stripos('SQ.IN', $search) !== false || stripos('SQ INCH', $search) !== false) {
                    $q->orWhere('grn_location_details.stock_effect_type', 'prod. area');
                }
            });
        })
        ->addColumn('options',function($grn_loc_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';              

                if(hasAccess("grn_location", "print")) {
                    $grn_loc_number = !empty($grn_loc_data->grn_loc_number) ? '_'.str_replace('/', '_', $grn_loc_data->grn_loc_number) : "";

                 
                    $pdfName  = 'GRN_Location'.$grn_loc_number;
                    $encodedId = base64_encode($grn_loc_data->grn_loc_id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=grn_location");
               
                    $action .= '<li><a  class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }

                if (hasAccess("grn_location", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-grn_location"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("grn_location", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'grn_location');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }


    public function store(Request $request)
    {
        // dd($request->all());
        $year_data = getCurrentYearData();
        $LocationData = getCurrentLocation();

        DB::beginTransaction();

        try {

            $existNumber = GRNLocation::where([
                ['grn_loc_sequence', $request->grn_loc_sequence],
                ['grn_loc_number', $request->grn_loc_number],
                ['year_id', $year_data->id]
            ])->first();

            if ($existNumber) {
                $latestNo = $this->getLatestGRNLocationNumber($request);
                $tmp = $latestNo->getContent();
                $area = json_decode($tmp, true);

                $grn_loc_number   = $area['latest_no'];
                $grn_loc_sequence = $area['number'];
            } else {
                $grn_loc_number   = $request->grn_loc_number;
                $grn_loc_sequence = $request->grn_loc_sequence;
            }

            $page_id = getMenuIdBassedOnDisplayName('grn_location');
            $assign_format_no = getAssignFormateNoForTransaction(
                $LocationData->location_id,
                $page_id->id,
                $request->grn_loc_date
            );

            $grn_data = GRNLocation::create([
                'grn_loc_number'        => $grn_loc_number,
                'grn_loc_sequence'      => $grn_loc_sequence,
                'grn_loc_date'          => $request->grn_loc_date ? Date::createFromFormat('d/m/Y', $request->grn_loc_date)->format('Y-m-d') : null,
                'mode_of_transport'    => $request->mode_of_transport,   
                'transporter'          => $request->transporter,
                'vehicle_no'           => $request->vehicle_no,
                'sp_note'              => $request->sp_note,
                'prepared_by_user_id'  => $request->prepared_by_user_id,
                'assign_format_no'     => $assign_format_no ?? null,
                'current_location_id'  => $LocationData->location_id ?? null,
                'year_id'              => $year_data->id,
                'company_id'           => Auth::user()->company_id,
                'created_by'           => Auth::user()->id,
                'created_on'           => Carbon::now('Asia/Kolkata'),
            ]);

            $details = json_decode($request->grn_location_details_data, true);

            if (!empty($details)) {
                foreach ($details as $row) {

                    if (!empty($row['item_id'])) {

                        $mode = $row['mode'];

                        if($mode == 'Delete') {
                            continue;
                        }

                        if($mode == 'Insert')
                        {

                            $pendingQty =  DB::table('pending_inter_location_transfer_qty')
                                ->where('iltd_id', $row['inter_location_transfer_details_id'])
                                ->value('pending_qty');
                            // dd((float)$row['qty'],(float)$pendingQty);
                            if((float)$row['qty'] > (float)$pendingQty)
                            {
                                DB::rollBack();
                                return response()->json([
                                    'response_code' => '0',
                                    'response_message' => 'GRN Qty. Used.',
                                ]);
                            }
                        


                            $rate_unit = ItemOpening::where('current_location_id',$LocationData->location_id)
                            ->where('io_item_id',$row['item_id'])
                            ->sum('io_stock_rate_unit');
                            $amount = $rate_unit * $row['qty'];

                            $stockEffectType = $row['stock_effect_type'] ?? 'main stock';
                            $grn_details = GRNLocationDetails::create([
                                'grn_locd_grn_id'                      => $grn_data->grn_loc_id,
                                'inter_location_transfer_details_id'   => !empty($row['inter_location_transfer_details_id']) ? $row['inter_location_transfer_details_id'] : null,
                                'item_id'                              => $row['item_id'],
                                'sr_table_unique_id'                   => !empty($row['sr_table_unique_id']) ? $row['sr_table_unique_id'] : null,
                                'sr_table_pk_id'                       => !empty($row['sr_table_pk_id']) ? $row['sr_table_pk_id'] : null,
                                'qty'                                  => $row['qty'],
                                'rate_unit'                            => $rate_unit ?? null,
                                'amount'                               => $amount ?? null,
                                'remark'                               => $row['remark'] ?? null,
                                'stock_effect_type'                    => $stockEffectType,
                                'previous_status_transaction_id'       => null,
                                'current_status_transaction_id'        => null,
                            ]);
                        
                            if(!empty($row['sr_table_pk_id'])){
                                changeStatusAndLocationForSrNo($row['sr_table_unique_id'], $row['sr_table_pk_id'], 'Active', 'Outside', $LocationData->location_id, 'Insert', $grn_details);
                            } 

                            if ($stockEffectType == 'prod. area') {
                                stockEffectSQIN($LocationData->location_id, $row['item_id'], $row['item_id'], $row['qty'], 0, 'Insert', 'U', 'GRN Location', $grn_details->grn_locd_id);
                            } else {
                                stockEffect($LocationData->location_id, $row['item_id'], $row['item_id'], $row['qty'], 0, $amount, 0, 'Insert', 'U', 'GRN Location', $grn_details->grn_locd_id, $row['sr_table_unique_id'] ?? null, $row['sr_table_pk_id'] ?? null);
                            }
                        }
                    }
                }
            }

            DB::commit();          

            $grn_loc_number = !empty($grn_loc_number) ? '_'.str_replace('/', '_', $grn_loc_number) : "";
            $pdf_name = 'GRN_Location'.$grn_loc_number;
            GeneratePdf($grn_data->grn_loc_id,$pdf_name,'grn_location','add');
                $encodedId = base64_encode($grn_data->grn_loc_id);

            if(hasAccess("grn_location", "print")){  
                $url = url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=grn_location");
            }else{
                $url ="";
            }

            return response()->json([
                'response_code' => '1',
                'url' => $url,
                'response_message' => getResponseMessage('store_success'),
            ]);

        } catch (\Exception $e) {

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
                    'response_message' => getResponseMessage('store_error'),
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

        $grn_loc_data =  DB::select('CALL grn_location_master(?)', [$request->id]);
        if(!empty($grn_loc_data))
        {
            $grn_loc_data = $grn_loc_data[0];
            $grn_loc_data->grn_loc_date = $grn_loc_data->grn_loc_date != "" ? Date::createFromFormat('Y-m-d',  $grn_loc_data->grn_loc_date)->format('d/m/Y') : "";

            $grn_loc_number = !empty($grn_loc_data->grn_loc_number) ? '_'.str_replace('/', '_', $grn_loc_data->grn_loc_number) : "";

            $grn_loc_data->pdf_name = 'GRN_Location'.$grn_loc_number;
            if(isset($grn_loc_data->cmp_logo))
            {
                $grn_loc_data->cmp_logo = base64_encode($grn_loc_data->cmp_logo);
            }
            
        }
        
        $grn_loc_details_data = DB::select('CALL grn_location_details(?)', [$request->id]);
       
        if($grn_loc_details_data){
            foreach($grn_loc_details_data as $dKey => $dVal)
            {
                $dVal->dc_date = $dVal->dc_date != "" ? Date::createFromFormat('Y-m-d',  $dVal->dc_date)->format('d/m/Y') : "";
            }
            // unset($dVal->sr_no);
        }

        if($grn_loc_data)
        {
            return response()->json([
                'grn_loc_data'          => $grn_loc_data,
                'grn_loc_details_data'  => $grn_loc_details_data,
                'response_code'         => '1',
                'response_message'      => '',
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
            'grn_loc_sequence' => [
                'required',
                'max:155',
                Rule::unique('grn_location')
                    ->where(function ($query) use ($year_data, $current_location_id) {
                        $query->where('year_id', $year_data->id)
                            ->where('current_location_id', $current_location_id);
                    })
                    ->ignore($request->id, 'grn_loc_id')
            ],
        ], [
            'grn_loc_sequence.unique'   => 'Duplicate GRN No. Found.',
            'grn_loc_sequence.required' => 'Enter GRN No.',
        ]);

        try
        {           
            $grn_location_data = GRNLocation::where('grn_loc_id', $request->id)->update([
                'grn_loc_number'        => $request->grn_loc_number,
                'grn_loc_sequence'      => $request->grn_loc_sequence,
                'grn_loc_date'          => $request->grn_loc_date ? Date::createFromFormat('d/m/Y', $request->grn_loc_date)->format('Y-m-d') : null,
                'mode_of_transport'    => $request->mode_of_transport,   
                'transporter'          => $request->transporter,
                'vehicle_no'           => $request->vehicle_no,
                'sp_note'              => $request->sp_note,
                'prepared_by_user_id'  => $request->prepared_by_user_id,
                // 'assign_format_no'     => $assign_format_no ?? null,
                'current_location_id'  => $current_location_id ?? null,
                'year_id'              => $year_data->id,
                'company_id'           => Auth::user()->company_id,
                'last_on'              => Carbon::now('Asia/Kolkata'),
                'last_by'              => Auth::id(),
            ]);


            if(!$grn_location_data)
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }

            $grn_location_details_data = json_decode($request->grn_location_details_data, true);

            if(!empty($grn_location_details_data))
            {
                foreach($grn_location_details_data as $ctVal)
                {
                    if(empty($ctVal)) continue;

                    $mode = $ctVal['mode'];
                    
                    if($mode == 'Insert')
                    {

                        $pendingQty =  DB::table('pending_inter_location_transfer_qty')
                                ->where('iltd_id', $ctVal['inter_location_transfer_details_id'])
                                ->value('pending_qty');
                            // dd((float)$ctVal['qty'],(float)$pendingQty);
                            if((float)$ctVal['qty'] > (float)$pendingQty)
                            {
                                DB::rollBack();
                                return response()->json([
                                    'response_code' => '0',
                                    'response_message' => 'GRN Qty. Used.',
                                ]);
                            }
                        
                        $rate_unit = ItemOpening::where('current_location_id',$current_location_id)
                        ->where('io_item_id',$ctVal['item_id'])
                        ->sum('io_stock_rate_unit');
                        $amount = $rate_unit * $ctVal['qty'];

                        $stockEffectType = $ctVal['stock_effect_type'] ?? 'main stock';
                        $grn_details = GRNLocationDetails::create([
                            'grn_locd_grn_id'                       => $request->id,
                            'inter_location_transfer_details_id'    => !empty($ctVal['inter_location_transfer_details_id']) ? $ctVal['inter_location_transfer_details_id'] : null,
                            'item_id'                               => $ctVal['item_id'],
                            'sr_table_unique_id'                    => !empty($ctVal['sr_table_unique_id']) ? $ctVal['sr_table_unique_id'] : null,
                            'sr_table_pk_id'                        => !empty($ctVal['sr_table_pk_id']) ? $ctVal['sr_table_pk_id'] : null,
                            'qty'                                   => $ctVal['qty'],
                            'rate_unit'                             => $rate_unit ?? null,
                            'amount'                                => $amount ?? null,
                            'remark'                                => $ctVal['remark'] ?? null,
                            'stock_effect_type'                     => $stockEffectType,
                            'previous_status_transaction_id'        => null,
                            'current_status_transaction_id'         => null,
                        ]);
                    
                        if(!empty($ctVal['sr_table_pk_id'])){
                            changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Active', 'Outside', $current_location_id, 'Insert', $grn_details);
                        } 

                        if ($stockEffectType == 'prod. area') {
                            stockEffectSQIN($current_location_id, $ctVal['item_id'], $ctVal['item_id'], $ctVal['qty'], 0, 'Insert', 'U', 'GRN Location', $grn_details->grn_locd_id);
                        } else {
                            stockEffect($current_location_id, $ctVal['item_id'], $ctVal['item_id'], $ctVal['qty'], 0, $amount, 0, 'Insert', 'U', 'GRN Location', $grn_details->grn_locd_id, $ctVal['sr_table_unique_id'] ?? null, $ctVal['sr_table_pk_id'] ?? null);
                        }
                    }

                 
                    elseif($mode == 'Update')
                    {
                        if(!empty($ctVal['grn_locd_id']))
                        {
                            
        
                            $pendingQty = (float) DB::table('pending_inter_location_transfer_qty')
                            ->where('iltd_id', $ctVal['inter_location_transfer_details_id'])
                            ->value('pending_qty');
                           
                            $old_qty = GRNLocationDetails::where('grn_locd_id', $ctVal['grn_locd_id'])->sum('qty');
                            $total_used_qty = $old_qty - $ctVal['qty'];
                            
                            if($pendingQty < $total_used_qty)
                            {
                                DB::rollBack();
                                return response()->json([
                                    'response_code' => '0',
                                    'response_message' => 'DC Qty. Is Used.',
                                ]);
                            }

                            $olddata = GRNLocationDetails::where('grn_locd_id', $ctVal['grn_locd_id'])->select('amount','rate_unit','item_id','qty','stock_effect_type')
                            ->first();

                            $stockEffectType = $ctVal['stock_effect_type'] ?? 'main stock';
                            $dc_details_data = GRNLocationDetails::where('grn_locd_id', $ctVal['grn_locd_id'])->update([
                                'grn_locd_grn_id'                       => $request->id,
                                'inter_location_transfer_details_id'    => !empty($ctVal['inter_location_transfer_details_id']) ? $ctVal['inter_location_transfer_details_id'] : null,
                                'item_id'                               => $ctVal['item_id'],
                                'sr_table_unique_id'                    => !empty($ctVal['sr_table_unique_id']) ? $ctVal['sr_table_unique_id'] : null,
                                'sr_table_pk_id'                        => !empty($ctVal['sr_table_pk_id']) ? $ctVal['sr_table_pk_id'] : null,
                                'qty'                                   => $ctVal['qty'],
                                'rate_unit'                             => $olddata->rate_unit ?? null,
                                'amount'                                => $ctVal['qty'] * $olddata->rate_unit,
                                'remark'                                => $ctVal['remark'] ?? null,
                                'stock_effect_type'                     => $stockEffectType,
                            ]);

                            if ($stockEffectType == 'prod. area') {
                                stockEffectSQIN($current_location_id, $ctVal['item_id'], $olddata->item_id, $ctVal['qty'], $olddata->qty, 'Update', 'U', 'GRN Location', $ctVal['grn_locd_id']);
                            } else {
                                stockEffect($current_location_id, $ctVal['item_id'], $olddata->item_id, $ctVal['qty'], $olddata->qty, $ctVal['qty'] * $olddata->rate_unit, $olddata->amount, 'Update', 'U', 'GRN Location', $ctVal['grn_locd_id'], $ctVal['sr_table_unique_id'] ?? $olddata->sr_table_unique_id ?? null, $ctVal['sr_table_pk_id'] ?? $olddata->sr_table_pk_id ?? null);
                            }
                            
                        }
                    }

                   
                    elseif($mode == 'Delete')
                    {
                        if(!empty($ctVal['grn_locd_id']))
                        {

                            $olddata = GRNLocationDetails::where('grn_locd_id',$ctVal['grn_locd_id'])->select('amount','rate_unit','item_id','qty','stock_effect_type')
                            ->first();

                            $stockEffectType = $olddata->stock_effect_type ?? 'main stock';
                            if ($stockEffectType == 'prod. area') {
                                stockEffectSQIN($current_location_id, $ctVal['item_id'], $ctVal['item_id'], 0, $olddata->qty, 'Delete', 'U', 'GRN Location', $ctVal['grn_locd_id']);
                            } else {
                                stockEffect($current_location_id, $ctVal['item_id'], $ctVal['item_id'], 0, $olddata->qty, 0, $olddata->amount, 'Delete', 'U', 'GRN Location', $ctVal['grn_locd_id'], $ctVal['sr_table_unique_id'] ?? $olddata->sr_table_unique_id ?? null, $ctVal['sr_table_pk_id'] ?? $olddata->sr_table_pk_id ?? null);
                            }

                            if($ctVal['sr_table_unique_id'] != null || $ctVal['sr_table_unique_id'] != ''){
                                $detailRecord = GRNLocationDetails::find($ctVal['grn_locd_id']);
                                changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'],$ctVal['sr_table_pk_id'],'Active','Outside',$current_location_id,'Delete', $detailRecord);
                            }
                            GRNLocationDetails::where('grn_locd_id', $ctVal['grn_locd_id'])->delete();
                        }
                    }
                }
            }

            DB::commit(); 
            $grn_loc_number = !empty($request->grn_loc_number) ? '_'.str_replace('/', '_', $request->grn_loc_number) : "";
            $pdf_name = 'GRN_Location'.$grn_loc_number;
            GeneratePdf($request->id,$pdf_name,'grn_location','edit');
                $encodedId = base64_encode($request->id);

            if(hasAccess("grn_location", "print")){  
                $url = url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=grn_location");
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
            // DB::rollBack();

            // return response()->json([
            //     'response_code' => '0',
            //     'response_message' => getResponseMessage('update_error'),
            //     'original_error' => $e->getMessage()
            // ]);

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
        $LocationData = getCurrentLocation()->location_id;
        try
        {
            $grnDetails = GRNLocationDetails::where('grn_locd_grn_id', $request->id)->get();

            if($grnDetails->isNotEmpty()){
                foreach($grnDetails as $item){
                    $stockEffectType = $item->stock_effect_type ?? 'main stock';
                    if ($stockEffectType == 'prod. area') {
                        stockEffectSQIN($LocationData, $item->item_id, $item->item_id, 0, $item->qty, 'Delete', 'U', 'GRN Location', $item->grn_locd_id);
                    } else {
                        stockEffect($LocationData, $item->item_id, $item->item_id, 0, $item->qty, 0, $item->amount, 'Delete', 'U', 'GRN Location', $item->grn_locd_id, $item->sr_table_unique_id ?? null, $item->sr_table_pk_id ?? null);
                    }

                    if($item->sr_table_unique_id != null || $item->sr_table_unique_id != ''){
                      changeStatusAndLocationForSrNo($item->sr_table_unique_id,$item->sr_table_pk_id,'Active','Outside',$LocationData,'Delete', $item);
                    }
                }
            }
            
            GRNLocation::where('grn_loc_id',$request->id)->delete();
            GRNLocationDetails::where('grn_locd_grn_id',$request->id)->delete();
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
            // dd($e->getMessage());
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
    

    public function getPendingLocationTransferDataForGRNLocation(Request $request)
    {
        $itemTypes = getItemType();
        $yearIds = getCompanyYearIdsToTill();
        $LocationData = getCurrentLocation()->location_id;

        $inter_transfer_data = DB::table('pending_inter_location_transfer_qty as pend')->select([
        'inter_location_transfer_details.inter_location_transfer_details_id','inter_location_transfer.dc_number','inter_location_transfer.dc_date','inter_location_transfer.dc_type_id',
        'item.item_name','item_group.item_group','item.item_type as main_group','inter_location_transfer_details.dc_qty','unit.unit', 'pend.pending_qty as pend_dc_qty', 'sr.name_for_display', 
        'inter_location_transfer_details.remark','admin.person_name as prepared_by','location.location_name'])
        ->leftJoin('inter_location_transfer_details', 'inter_location_transfer_details.inter_location_transfer_details_id', '=', 'pend.iltd_id')
        ->leftJoin('inter_location_transfer', 'inter_location_transfer.ilt_id', '=', 'inter_location_transfer_details.inter_location_transfer_id')
        ->leftJoin('item', 'item.id', '=', 'inter_location_transfer_details.item_id')
        ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        ->leftJoin('admin', 'admin.id', '=', 'inter_location_transfer.prepared_by_id')
        ->leftJoin('location', 'location.location_id', '=', 'inter_location_transfer.to_location_id')
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'inter_location_transfer_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'inter_location_transfer_details.sr_table_unique_id');
        })
        ->whereIn('inter_location_transfer.year_id', $yearIds)
        ->where('pend.pending_qty', '>', 0)
        ->where('inter_location_transfer.to_location_id', $LocationData)->get();

        if($inter_transfer_data != null)
        {
            $inter_transfer_data = $inter_transfer_data->map(function ($item) {
                $item->dc_date = $item->dc_date != "" ? Date::createFromFormat('Y-m-d',$item->dc_date)->format('d/m/Y') : "";
                if (isset($item->dc_type_id) && $item->dc_type_id == 'SQIN from Prod. Area') {
                    $item->unit = 'SQIN';
                }
                return $item;
            });
            $inter_transfer_data->transform(function ($item) use ($itemTypes) {
                $item->main_group = $itemTypes[$item->main_group] ?? $item->main_group;
                return $item;
            });
            return response()->json([
                'response_code' => '1',
                'inter_transfer_data' => $inter_transfer_data,
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '1',
                'inter_transfer_data' => []
            ]);
        }
    }




    public function getInterLocationTransferPartDataForGRNLocation(Request $request)
    {
        $request->iltd_id = explode(',', $request->inter_location_transfer_details_ids);
        $current_location_id = getCurrentLocation()->location_id;

        $inter_transfer_part_data = DB::table('pending_inter_location_transfer_qty as pend')
        ->select('inter_location_transfer_details.inter_location_transfer_details_id','inter_location_transfer_details.item_id','inter_location_transfer.dc_number','inter_location_transfer.dc_date','item.item_name','item_group.item_group','item.item_type as main_group','pend.pending_qty as qty','unit.unit','item_opening.io_stock_qty','inter_location_transfer_details.sr_table_pk_id','inter_location_transfer_details.sr_table_unique_id','sr.name_for_display as sr_no','inter_location_transfer_details.stock_effect_type')
        ->leftJoin('inter_location_transfer_details', 'inter_location_transfer_details.inter_location_transfer_details_id', '=', 'pend.iltd_id')
        ->leftJoin('inter_location_transfer', 'inter_location_transfer.ilt_id', '=', 'inter_location_transfer_details.inter_location_transfer_id')
        ->leftJoin('item', 'item.id', '=', 'inter_location_transfer_details.item_id')
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
        ->leftJoin('item_opening', function($join ) use ($current_location_id) {
            $join->on('item_opening.io_item_id', '=', 'inter_location_transfer_details.item_id')
                ->where('item_opening.current_location_id', $current_location_id);
        })
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'inter_location_transfer_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'inter_location_transfer_details.sr_table_unique_id');
        })
        ->whereIn('inter_location_transfer_details.inter_location_transfer_details_id', $request->iltd_id)
        ->where('pend.pending_qty', '>', 0)
        ->get();


        // dd($inter_transfer_part_data);
        if($inter_transfer_part_data != null)
        {
            $itemTypes = getItemType();
            $inter_transfer_part_data = $inter_transfer_part_data->map(function ($item) use ($itemTypes) {
                $item->dc_date = $item->dc_date != "" ? Date::createFromFormat('Y-m-d',  $item->dc_date)->format('d/m/Y') : "";
                $item->main_group = $itemTypes[$item->main_group] ?? $item->main_group;
                if ($item->stock_effect_type == 'prod. area') {
                    $item->unit = 'SQIN';
                }
                return $item;
            });
            return response()->json([
                'response_code' => '1',
                'inter_transfer_part_data' => $inter_transfer_part_data,
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '1',
                'inter_transfer_part_data' => []
            ]);
        }

    }
    

    public function getLatestGRNLocationNumber(Request $request)
    {
        $modal  =  GRNLocation::class;
        $sequence = 'grn_loc_sequence';           
        $prefix = 'GRN/LOC';           
      
        $sup_num_format = getLatestSequence($modal,$sequence,$prefix);   
        return response()->json([
          'response_code' => 1,
          'latest_no'     => $sup_num_format['format'],
          'number'        => $sup_num_format['isFound'],
      ]);
    }


}

?>