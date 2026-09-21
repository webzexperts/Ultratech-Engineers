<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\GRN;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use App\Models\Instrument;
use App\Models\ItemOpening;
use App\Models\File;

class InstrumentController extends Controller
{
    public function manage()
    {
        return view('manage.manage-instrument');
    }

    public function index(Instrument $Instrument, Request $request, DataTables $datatables)
    {
        $LocationData = getCurrentLocation();
        $instrument = Instrument::select([
            'instrument.ins_id',
            'instrument.ins_instrument_no',
            'instrument.ins_instrument_name',
            'instrument.ins_make',
            'instrument.ins_mfg_sr_no',
            'instrument.ins_last_cali_date',
            'instrument.ins_next_cali_due_date',
            'instrument.ins_status',
            'instrument.ins_doc_ref_no',
            'instrument.ins_cali_req',
            'instrument.ins_cali_freq',
            'instrument.table_unique_id',
            'instrument.table_pk_id',
            'instrument.created_on',
            'instrument.created_by',
            'instrument.last_by',
            'instrument.last_on',
            'current_location.location_name as current_location',
            'own_location.location_name as own_location',
        ])
        ->leftJoin('location as current_location','current_location.location_id', '=','instrument.current_location_id')
        ->leftJoin('location as own_location','own_location.location_id', '=','instrument.own_location_id');
        if ($LocationData->location_type != 'HO') {
            $instrument->where(function($query) use ($LocationData) {
                $query->where('instrument.current_location_id', $LocationData->location_id)
                ->orWhere('instrument.own_location_id', $LocationData->location_id);
            });
        }
         
        

        $dataTable = DataTables::of($instrument)
       

        ->editColumn('ins_last_cali_date', function($instrument){
            if ($instrument->ins_last_cali_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $instrument->ins_last_cali_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('instrument.ins_last_cali_date', function ($q, $k) {
            applyDate($q, $k, 'instrument.ins_last_cali_date');
        })
        
        ->editColumn('ins_next_cali_due_date', function($instrument){
            if ($instrument->ins_next_cali_due_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $instrument->ins_next_cali_due_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('instrument.ins_next_cali_due_date', function ($q, $k) {
            applyDate($q, $k, 'instrument.ins_next_cali_due_date');
        })
        ->filterColumn('instrument.ins_status', function ($query, $keyword) {
            $globalSearch = request()->input('search.value');
            $searchValue = $globalSearch != '' ? $globalSearch : $keyword;
            $lowerKeyword = strtolower(trim($searchValue));
            if ($lowerKeyword === 'active') {
                $searchStatus = 'active';
            }
            elseif ($lowerKeyword === 'deactive') {
                $searchStatus = 'deactive';
            }
            elseif ($lowerKeyword === 'outside') {
                $searchStatus = 'outside';
            }
            elseif ($lowerKeyword === 'service po') {
                $searchStatus = 'service po';
            }
            if (!empty($searchStatus)) {
                $query->where('instrument.ins_status', '=', $searchStatus);
            } 
            else {
                $dbFormatKeyword = str_replace(' ', '_', $lowerKeyword);
                $query->where('instrument.ins_status', 'like', "$dbFormatKeyword%");
            }
        })
        
        ->addColumn('options',function($instrument) use ($LocationData){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("instrument", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-instrument"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("instrument", "delete")){
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'instrument');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        
    // dd($request->all()) ;
        $LocationData = getCurrentLocation()->location_id;
        $year_data = getCurrentYearData();
        DB::beginTransaction();
        try
        {
            // $old_qty = Instrument::where('table_pk_id',$request->table_pk_id)->count();

            $pendingQty = DB::table('instrument_pending_qty')
                ->where('table_pk_id', $request->table_pk_id)
                ->value('pending_qty');

            // if((float)$old_qty > (float)$pendingQty)
            if((float)$pendingQty == 0)
            {
                DB::rollBack();
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $request->table_unique_id .' Qty. Used.',
                ]);
            }

            $name_for_display = '';
            if(!empty($request->ins_instrument_no) && !empty($request->ins_instrument_name)) {
                $name_for_display = $request->ins_instrument_no.' - '.$request->ins_instrument_name;
            }

            // File handling
            $cali_certificate_path = null;
            $cali_certificate_blob = null;
            if ($request->filled('calibration_certificate_doc')) {
                $file_model = new File();
                $isFound = $file_model->getFileFromTemp($request->calibration_certificate_doc, 'calibration_certificate');
                if ($isFound) {
                    $cali_certificate_path = $isFound;
                } else {
                    $cali_certificate_path = $request->calibration_certificate_doc;
                }

                if ($cali_certificate_path != "") {
                    $cali_certificate_blob = file_get_contents(storage_path('app/public/' . $cali_certificate_path));
                }
            }

            $instrument =  Instrument::create([
                'ins_instrument_no'           => $request->ins_instrument_no ?? null,
                'ins_instrument_name'         => $request->ins_instrument_name ?? null,
                'ins_make'                    => $request->ins_make ?? null,
                'ins_mfg_sr_no'               => $request->ins_mfg_sr_no ?? null,
                'ins_last_cali_date'          => isset($request->ins_last_cali_date) ? Date::createFromFormat('d/m/Y', $request->ins_last_cali_date)->format('Y-m-d') : null,
                'ins_next_cali_due_date'      => isset($request->ins_next_cali_due_date) ? Date::createFromFormat('d/m/Y', $request->ins_next_cali_due_date)->format('Y-m-d') : null,
                'ins_status'                  => $request->ins_status ?? null,
                'ins_doc_ref_no'              => $request->ins_doc_ref_no ?? null,
                'ins_cali_req'                => $request->ins_cali_req ?? null,
                'ins_cali_freq'               => $request->ins_cali_freq ?? null,
                'ins_cali_certificate'        => $cali_certificate_path,
                'ins_cali_certificate_blob'   => $cali_certificate_blob,
                'table_unique_id'             => $request->table_unique_id ?? null,
                'table_pk_id'                 => $request->table_pk_id ?? null,
                'item_id'                     => $request->item_id ?? null,
                'name_for_display'            => $name_for_display,
                'status_transaction_id' => 0,
                'own_location_id'       => $LocationData,
                'current_location_id'   => $LocationData,
                'company_id'            => Auth::user()->company_id,
                'created_on'            => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'            => Auth::user()->id,
            ]);

            if($instrument->save())
            {
                DB::commit();
                return response()->json([
                    'response_code' => '1',
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
            DB::rollBack();
            return response()->json([
                'response_code' => '0',
                'response_message' => getResponseMessage('store_error'),
                'original_error' => $e->getMessage()
            ]);
        }
    }


    public function edit(Request $request)
    {
        $itemTypes = getItemType();
        $LocationData = getCurrentLocation()->location_id;
        $instrument = DB::select('CALL instrument_master(?)', [$request->id]);

        if(!empty($instrument))
        {
            $instrument = $instrument[0];
            
            if(isset($instrument->ins_last_cali_date)){
                $instrument->ins_last_cali_date = $instrument->ins_last_cali_date != "" ? Date::createFromFormat('Y-m-d',  $instrument->ins_last_cali_date)->format('d/m/Y') : "";
            }
            if(!empty($instrument->ins_next_cali_due_date)){
                $instrument->ins_next_cali_due_date = $instrument->ins_next_cali_due_date != "" ? Date::createFromFormat('Y-m-d',  $instrument->ins_next_cali_due_date)->format('d/m/Y') : "";
            }
            if(isset($instrument->ins_cali_certificate_blob))
            {
                $instrument->ins_cali_certificate_blob = base64_encode($instrument->ins_cali_certificate_blob);
            }

             if(!empty($instrument->grn_date)){
                $instrument->grn_date = $instrument->grn_date != "" ? Date::createFromFormat('Y-m-d',  $instrument->grn_date)->format('d/m/Y') : "";
            }
            if(!empty($instrument->grn_challan_date)){
                $instrument->grn_challan_date = $instrument->grn_challan_date != "" ? Date::createFromFormat('Y-m-d',  $instrument->grn_challan_date)->format('d/m/Y') : "";
            }
            

            $instrument->login_location = $LocationData; 
            $instrument->main_group = $itemTypes[$instrument->main_group] ?? $instrument->main_group;


            $is_used = DB::table('grn_supplier_details')
                ->where('sr_table_unique_id', 'instrument')
                ->where('sr_table_pk_id', $request->id)
                ->where('for_calibration', 'Yes')
                ->where('status', 'Y')
                ->exists();

            return response()->json([
                'instrument'     => $instrument,
                'is_used'        => $is_used,
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
        $LocationData = getCurrentLocation()->location_id;
        DB::beginTransaction();
            $instrument_data = Instrument::where('ins_id', $request->id)->first();
            if ($instrument_data->current_location_id != $LocationData) {
                DB::rollBack();
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Unauthorized Record Can`t Update.'
                ]);
            }
        try
        {
            $year_data = getCurrentYearData();
            $name_for_display = '';
            if(!empty($request->ins_instrument_no) && !empty($request->ins_instrument_name)) {
                $name_for_display = $request->ins_instrument_no.' - '.$request->ins_instrument_name;
            }

            // File handling
            $cali_certificate_path = null;
            $cali_certificate_blob = null;
            if ($request->filled('calibration_certificate_doc')) {
                $file_model = new File();
                $isFound = $file_model->getFileFromTemp($request->calibration_certificate_doc, 'calibration_certificate');
                if ($isFound) {
                    $cali_certificate_path = $isFound;
                } else {
                    $cali_certificate_path = $request->calibration_certificate_doc;
                }

                if ($cali_certificate_path != "") {
                    $cali_certificate_blob = file_get_contents(storage_path('app/public/' . $cali_certificate_path));
                }
            }

             $instrument = Instrument::where('ins_id', $request->id)->update([
                'ins_instrument_no'           => $request->ins_instrument_no ?? null,
                'ins_instrument_name'         => $request->ins_instrument_name ?? null,
                'ins_make'                    => $request->ins_make ?? null,
                'ins_mfg_sr_no'               => $request->ins_mfg_sr_no ?? null,
                'ins_last_cali_date'          => isset($request->ins_last_cali_date) ? Date::createFromFormat('d/m/Y', $request->ins_last_cali_date)->format('Y-m-d') : null,
                'ins_next_cali_due_date'      => isset($request->ins_next_cali_due_date) ? Date::createFromFormat('d/m/Y', $request->ins_next_cali_due_date)->format('Y-m-d') : null,
                // 'ins_status'                  => $request->ins_status ?? null,
                'ins_doc_ref_no'              => $request->ins_doc_ref_no ?? null,
                'ins_cali_req'                => $request->ins_cali_req ?? null,
                'ins_cali_freq'               => $request->ins_cali_freq ?? null,
                'ins_cali_certificate'        => $cali_certificate_path,
                'ins_cali_certificate_blob'   => $cali_certificate_blob,
                'table_unique_id'             => $request->table_unique_id ?? null,
                'table_pk_id'                 => $request->table_pk_id ?? null,
                'item_id'                     => $request->item_id ?? null,
                'name_for_display'            => $name_for_display,
                'own_location_id'       => $LocationData,
                'current_location_id'   => $LocationData,
                'company_id'            => Auth::user()->company_id,
                'last_on'               => Carbon::now('Asia/Kolkata'),
                'last_by'               => Auth::id(),
            ]);

            if($instrument)
            {
                DB::commit();
                return response()->json([
                    'response_code' => '1',
                    'response_message' => getResponseMessage('update_success'),
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }

        }catch(\Exception $e)
            {
                report($e);
                DB::rollBack();
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                    'original_error' => $e->getMessage()
                ]);
            }
    }

    public function destroy(Request $request)
    {
        try
        {
            // dd($request->all());
            $usage = DB::select('CALL instrument_used_list(?)', [$request->id]);
            if (!empty($usage)) {
                $message = "You Can't Delete, Instrument Is Used In ". $usage[0]->table_name.".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }
            Instrument::where('ins_id',$request->id)->delete();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        }
        catch(\Exception $e)
        {
            report($e);
            if(isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451)
            {
                $error_msg = "This Is Used Somewhere, You Can't Delete";
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




    function getPendingGrnListForInstrument(Request $request)
    {
        $itemTypes = getItemType();
        $yearIds   = getCompanyYearIdsToTill();
        $LocationData = getCurrentLocation()->location_id;
        
        $opening_data = DB::table('instrument_pending_qty')->select(['instrument_pending_qty.table_pk_id',
        'instrument_pending_qty.table_unique_id','grn_supplier.grn_number','grn_supplier.grn_date','suppliers.supplier_name','grn_supplier.grn_challan_number','grn_supplier.grn_challan_date','item.item_name','item_group.item_group','item.item_type','unit.unit','instrument_pending_qty.pending_qty',   
        DB::raw("
            CASE 
                WHEN instrument_pending_qty.table_unique_id = 'GRN' 
                    THEN grn_supplier_details.grnd_qty
                WHEN instrument_pending_qty.table_unique_id = 'Opening' 
                    THEN item_opening.io_opening_qty
                ELSE 0
            END as qty
        ")
        ])
        ->leftJoin('item_opening', function ($join) {
            $join->on('item_opening.io_id', '=', 'instrument_pending_qty.table_pk_id')
                ->where('instrument_pending_qty.table_unique_id', '=', 'Opening');
        })
        ->leftJoin('grn_supplier_details', function ($join) {
            $join->on('grn_supplier_details.grnd_id', '=', 'instrument_pending_qty.table_pk_id')
                ->where('instrument_pending_qty.table_unique_id', '=', 'GRN');
        })
        ->leftJoin('grn_supplier', 'grn_supplier.grn_id', '=', 'grn_supplier_details.grnd_grn_id')
        ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
        ->leftJoin('item', 'item.id', '=', 'instrument_pending_qty.item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->where('instrument_pending_qty.location_id',$LocationData)
        ->where('instrument_pending_qty.pending_qty', '>', 0)
        ->get();


        if($opening_data != null)
        {
            $opening_data->transform(function ($item) use ($itemTypes) {
                $item->item_type = $itemTypes[$item->item_type] ?? $item->item_type;
                $item->grn_date = $item->grn_date != "" ? Date::createFromFormat('Y-m-d', $item->grn_date)->format('d/m/Y') : "";
                $item->grn_challan_date = $item->grn_challan_date != "" ? Date::createFromFormat('Y-m-d', $item->grn_challan_date)->format('d/m/Y') : "";
                return $item;
            });
            return response()->json([
                'response_code' => '1',
                'opening_data' => $opening_data,
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '1',
                'opening_data' => []
            ]);
        }
    }

    public function getPendingGrnForInstrument(Request $request){
        $itemTypes = getItemType();
        $LocationData = getCurrentLocation()->location_id;

        $request->table_pk_ids = explode(',', $request->table_pk_ids);
        $request->table_unique_ids = explode(',', $request->table_unique_ids);

        $data = DB::table('instrument_pending_qty')
        ->select([
            'instrument_pending_qty.table_pk_id',
            'instrument_pending_qty.table_unique_id',
            'grn_supplier.grn_number',
            'grn_supplier.grn_date',
            'suppliers.supplier_name',
            'grn_supplier.grn_challan_number',
            'grn_supplier.grn_challan_date',
            'item.item_name',
            'item_group.item_group',
            'item.item_type',
            'item.id as item_id',
            'instrument_pending_qty.pending_qty',

            DB::raw("
                CASE 
                    WHEN instrument_pending_qty.table_unique_id = 'GRN' 
                        THEN grn_supplier_details.grnd_qty
                    WHEN instrument_pending_qty.table_unique_id = 'Opening' 
                        THEN item_opening.io_opening_qty
                    ELSE 0
                END as qty
            ")
        ])

        ->leftJoin('item_opening', function ($join) {
            $join->on('item_opening.io_id', '=', 'instrument_pending_qty.table_pk_id')
                ->where('instrument_pending_qty.table_unique_id', '=', 'Opening');
        })

        ->leftJoin('grn_supplier_details', function ($join) {
            $join->on('grn_supplier_details.grnd_id', '=', 'instrument_pending_qty.table_pk_id')
                ->where('instrument_pending_qty.table_unique_id', '=', 'GRN');
        })

        ->leftJoin('grn_supplier', 'grn_supplier.grn_id', '=', 'grn_supplier_details.grnd_grn_id')
        ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
        ->leftJoin('item', 'item.id', '=', 'instrument_pending_qty.item_id')
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')

        ->where('instrument_pending_qty.location_id', $LocationData)
        ->whereIn('instrument_pending_qty.table_pk_id', $request->table_pk_ids)
        ->whereIn('instrument_pending_qty.table_unique_id', $request->table_unique_ids)
        ->where('instrument_pending_qty.pending_qty', '>', 0)

        ->first(); // single select (as per your UI)

        if ($data != null) {

            $data->item_type = $itemTypes[$data->item_type] ?? $data->item_type;

            $data->grn_date = $data->grn_date != "" 
                ? Date::createFromFormat('Y-m-d', $data->grn_date)->format('d/m/Y') 
                : "";

            $data->grn_challan_date = $data->grn_challan_date != "" 
                ? Date::createFromFormat('Y-m-d', $data->grn_challan_date)->format('d/m/Y') 
                : "";

            return response()->json([
                'response_code' => '1',
                'opening_data' => $data,
            ]);
        }

        return response()->json([
            'response_code' => '1',
            'opening_data' => []
        ]);
    }
    
}