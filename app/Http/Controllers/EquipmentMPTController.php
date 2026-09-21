<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EquipmentMPT;
use App\Models\EquipmentMPTDetails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DataTables;
use Date;
use App\Models\Admin;
use App\Models\File;
use App\Models\Year;
use App\Models\GRN;
use App\Models\Item;
use App\Models\ItemOpening;

class EquipmentMPTController extends Controller
{
   public function manage()
    {
        return view('manage.manage-equipment_mpt');
    }

    public function index(EquipmentMPT $equipment_mpt_data, Request $request, DataTables $datatables)
    {
        $LocationData = getCurrentLocation();
        // dd($LocationData);
        $equipment_mpt_data = EquipmentMPT::select([
            'equipment_mpt.em_id',
            'equipment_mpt.table_unique_id',
            'equipment_mpt.table_pk_id',
            'item.item_name',
            'equipment_mpt.em_equipment_name',
            'equipment_mpt.em_make',
            'equipment_mpt.em_serial_no',
            'equipment_mpt.em_next_cali_due_date',
            'equipment_mpt.em_last_cali_date',
            // 'equipment_mpt.em_type_of_current',
            // 'equipment_mpt.em_grnd_id',
            // 'equipment_mpt.em_doc_ref_no',
            // 'equipment_mpt.em_remark',
            // 'equipment_mpt_details.emd_calibration_due_date',
            'equipment_mpt.em_status',
            'equipment_mpt.created_on',
            'equipment_mpt.created_by',
            'equipment_mpt.last_by',
            'equipment_mpt.last_on',
            'current_location.location_name as current_location',
            'own_location.location_name as own_location',
        ])
        // ->leftJoin('equipment_mpt_details', 'equipment_mpt_details.emd_em_id', '=', 'equipment_mpt.em_id')
        ->leftJoin('item_opening','item_opening.io_id','=','equipment_mpt.table_pk_id')
        ->leftJoin('location as current_location','current_location.location_id', '=','equipment_mpt.current_location_id')
        ->leftJoin('location as own_location','own_location.location_id', '=','equipment_mpt.own_location_id')
        ->leftJoin('item', 'item.id', '=', 'item_opening.io_item_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id');
        if ($LocationData->location_type != 'HO') {
            $equipment_mpt_data->where(function($query) use ($LocationData) {
                $query->where('equipment_mpt.current_location_id', $LocationData->location_id)
                ->orWhere('equipment_mpt.own_location_id', $LocationData->location_id);
            });
        }
        $dataTable = DataTables::of($equipment_mpt_data)
        ->editColumn('em_last_cali_date', function($equipment_mpt_data){
            if ($equipment_mpt_data->em_last_cali_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $equipment_mpt_data->em_last_cali_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->editColumn('em_next_cali_due_date', function($equipment_mpt_data){
            if ($equipment_mpt_data->em_next_cali_due_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $equipment_mpt_data->em_next_cali_due_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('equipment_mpt.em_next_cali_due_date', function ($q, $k) {
            applyDate($q, $k, 'equipment_mpt.em_next_cali_due_date');
        })
        ->filterColumn('equipment_mpt.em_last_cali_date', function ($q, $k) {
            applyDate($q, $k, 'equipment_mpt.em_last_cali_date');
        })
        ->filterColumn('equipment_mpt.em_status', function ($query, $keyword) {
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
                $query->where('equipment_mpt.em_status', '=', $searchStatus);
            } 
            else {
                $dbFormatKeyword = str_replace(' ', '_', $lowerKeyword);
                $query->where('equipment_mpt.em_status', 'like', "$dbFormatKeyword%");
            }
        })
        ->addColumn('options',function($equipment_mpt_data) use ($LocationData){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("equipment_mpt", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-equipment_mpt"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("equipment_mpt", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'equipment_mpt');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function equipmentLNRData()
    {
        $lnr_data = EquipmentMPT::select('em_id','em_inward_type')->orderBy('em_id','Desc')->first();
       
        return response()->json([
          'response_code' => 1,
          'lnr_data'       => $lnr_data,
      ]);
    }

    public function store(Request $request)
    {       
        // dd($request->all()); 
        $LocationData = getCurrentLocation()->location_id;
        DB::beginTransaction();
        try
        {
            // $old_qty = EquipmentMPT::where('table_pk_id',$request->table_pk_id)->count();

            $pendingQty =  DB::table('mpt_equipment_pending_qty')
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
            if(!empty($request->em_equipment_name) && !empty($request->em_serial_no)  && !empty($request->em_make)) {
                $name_for_display = $request->em_equipment_name.' - '.$request->em_serial_no.' - '.$request->em_make;
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

            $equipment_mpt_data = EquipmentMPT::create([
                'em_equipment_name'          => $request->em_equipment_name !="" ? $request->em_equipment_name : null,
                'em_make'                    => $request->em_make !="" ? $request->em_make : null,
                'em_serial_no'               => $request->em_serial_no !="" ? $request->em_serial_no : null,
                'em_cali_freq'               => $request->ins_cali_freq !="" ? $request->ins_cali_freq : null,
                'em_last_cali_date'          => isset($request->em_last_cali_date) ? Date::createFromFormat('d/m/Y', $request->em_last_cali_date)->format('Y-m-d') : null,
                'em_next_cali_due_date'      => isset($request->em_next_cali_due_date) ? Date::createFromFormat('d/m/Y', $request->em_next_cali_due_date)->format('Y-m-d') : null,
                'em_cali_certificate'        => $cali_certificate_path,
                'em_cali_certificate_blob'   => $cali_certificate_blob,
                'em_status'                 => $request->em_status  !="" ? $request->em_status : null,
                'own_location_id'           => $LocationData,
                'current_location_id'       => $LocationData,
                'table_unique_id'           => $request->table_unique_id ?? null,
                'table_pk_id'               => $request->table_pk_id ?? null,
                'item_id'                   => $request->item_id ?? null,
                'name_for_display' =>  $name_for_display,
                'status_transaction_id' => 0,
                'company_id'                => Auth::user()->company_id,
                'created_by'                => Auth::user()->id,
                'created_on'                => Carbon::now('Asia/Kolkata')->toDateTimeString(),
            ]);

            // $equipment_mpt_details_data = $request->equipment_mpt_details_data = json_decode($request->equipment_mpt_details_data, true);
            // if(!empty($equipment_mpt_details_data))
            // {
            //     foreach($equipment_mpt_details_data as $ctKey => $ctVal)
            //     {
            //         if($ctVal != null)
            //         {
            //             $equipment_mpt_details_data = EquipmentMPTDetails::create([
            //                 'emd_em_id'            => $equipment_mpt_data->em_id,

            //                 'emd_calibration_due_date'        => !empty($ctVal['emd_calibration_due_date']) ?  Date::createFromFormat('d/m/Y', $ctVal['emd_calibration_due_date'])->format('Y-m-d') : null,

            //                 'status' => 'Y',
            //             ]);
            //         }
            //     }
            // }

            if($equipment_mpt_data->save())
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
        $equipment_mpt_data =  DB::select('CALL equipment_mpt_master(?)',  [$request->id]);
        if (!empty($equipment_mpt_data)) {
            $equipment_mpt_data = $equipment_mpt_data[0];
            
            if(isset($equipment_mpt_data->em_last_cali_date)){
                $equipment_mpt_data->em_last_cali_date = $equipment_mpt_data->em_last_cali_date != "" ? Date::createFromFormat('Y-m-d',  $equipment_mpt_data->em_last_cali_date)->format('d/m/Y') : "";
            }
            if(!empty($equipment_mpt_data->em_next_cali_due_date)){
                $equipment_mpt_data->em_next_cali_due_date = $equipment_mpt_data->em_next_cali_due_date != "" ? Date::createFromFormat('Y-m-d',  $equipment_mpt_data->em_next_cali_due_date)->format('d/m/Y') : "";
            }
            if(isset($equipment_mpt_data->em_cali_certificate_blob))
            {
                $equipment_mpt_data->em_cali_certificate_blob = base64_encode($equipment_mpt_data->em_cali_certificate_blob);
            }
            $equipment_mpt_data->login_location = $LocationData; 
            $equipment_mpt_data->main_group = $itemTypes[$equipment_mpt_data->main_group] ?? $equipment_mpt_data->main_group;
            if(!empty($equipment_mpt_data->grn_date)){
                $equipment_mpt_data->grn_date = $equipment_mpt_data->grn_date != "" ? Date::createFromFormat('Y-m-d',  $equipment_mpt_data->grn_date)->format('d/m/Y') : "";
            }
            if(!empty($equipment_mpt_data->grn_challan_date)){
                $equipment_mpt_data->grn_challan_date = $equipment_mpt_data->grn_challan_date != "" ? Date::createFromFormat('Y-m-d',  $equipment_mpt_data->grn_challan_date)->format('d/m/Y') : "";
            }
            
            // $check_item = Item::where('item.id',$equipment_mpt_data->em_mpt_equipment_id)->value('item.status');


        }
        // $equipment_mpt_details_data = DB::select('CALL equipment_mpt_details(?)', [$request->id]);
        // if($equipment_mpt_details_data){
        //     foreach($equipment_mpt_details_data as $dKey => $dVal)
        //     {
        //         $dVal->emd_calibration_due_date = $dVal->emd_calibration_due_date != "" ? Date::createFromFormat('Y-m-d',  $dVal->emd_calibration_due_date)->format('d/m/Y') : "";
        //     }
        // }

        // if($check_item == "Deactive"){
        //     $items = getDeactiveItems('equipment_mpt', 'em_mpt_equipment_id', $request->id, 'mpt_equipment','em_id');
        // }else{
        //     $items = '';
        // }

        if($equipment_mpt_data){
            $is_used = DB::table('grn_supplier_details')
                ->where('sr_table_unique_id', 'mpt_equipment')
                ->where('sr_table_pk_id', $request->id)
                ->where('for_calibration', 'Yes')
                ->where('status', 'Y')
                ->exists();

            return response()->json([
                'equipment_mpt_data'         => $equipment_mpt_data,
                'is_used'                    => $is_used,
                // 'equipment_mpt_details_data' => $equipment_mpt_details_data,
                // 'items'                 => $items,
                'response_code'    => '1',
                'response_message' => '',
            ]);
        }else{
            return response()->json([
                'response_code'    => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    

    public function update(Request $request)
    {
        DB::beginTransaction();
        // dd($request->all());
        $LocationData = getCurrentLocation()->location_id;
        $equipmentMPT = EquipmentMPT::where('em_id', $request->id)->first();
        if ($equipmentMPT->current_location_id != $LocationData) {
            DB::rollBack();
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Unauthorized Record Can`t Update.',
            ]);
        }
        try{
            $name_for_display = '';
            if(!empty($request->em_equipment_name) && !empty($request->em_serial_no)  && !empty($request->em_make)) {
                $name_for_display = $request->em_equipment_name.' - '.$request->em_serial_no.' - '.$request->em_make;
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

            $equipment_mpt_data = EquipmentMPT::where('em_id', $request->id)->update([
                'em_equipment_name'          => $request->em_equipment_name !="" ? $request->em_equipment_name : null,
                'em_make'                    => $request->em_make !="" ? $request->em_make : null,
                'em_serial_no'               => $request->em_serial_no !="" ? $request->em_serial_no : null,
                'em_cali_freq'               => $request->ins_cali_freq !="" ? $request->ins_cali_freq : null,
                'em_last_cali_date'          => isset($request->em_last_cali_date) ? Date::createFromFormat('d/m/Y', $request->em_last_cali_date)->format('Y-m-d') : null,
                'em_next_cali_due_date'      => isset($request->em_next_cali_due_date) ? Date::createFromFormat('d/m/Y', $request->em_next_cali_due_date)->format('Y-m-d') : null,
                'em_cali_certificate'        => $cali_certificate_path,
                'em_cali_certificate_blob'   => $cali_certificate_blob,
                // 'em_status'                 => $request->em_status  !="" ? $request->em_status : null,
                'own_location_id'           => $LocationData,
                'current_location_id'       => $LocationData,
                'table_unique_id'           => $request->table_unique_id ?? null,
                'table_pk_id'               => $request->table_pk_id ?? null,
                'item_id'                   => $request->item_id ?? null,
                'name_for_display' =>  $name_for_display,
                'company_id'                => Auth::user()->company_id,
                'last_on'       => Carbon::now('Asia/Kolkata'),
                'last_by'       => Auth::id(),
            ]);
            
            if(!$equipment_mpt_data){
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }
            // $update_details =  EquipmentMPTDetails::where('emd_em_id',$request->id)->update(['status' => 'D',]);
            // $equipment_mpt_details_data = json_decode($request->equipment_mpt_details_data, true);

            // foreach($equipment_mpt_details_data as $ctVal)
            // {
            //     if($ctVal['emd_id'] == 0)
            //     {
            //         EquipmentMPTDetails::create([
            //             'emd_em_id' => $request->id,

            //             'emd_calibration_due_date'        => !empty($ctVal['emd_calibration_due_date']) ? Date::createFromFormat('d/m/Y', $ctVal['emd_calibration_due_date'])->format('Y-m-d') : null,

            //             'status' => 'Y',
            //         ]);
            //     }
            //     else
            //     {
            //         EquipmentMPTDetails::where('emd_id', $ctVal['emd_id'])->update([

            //             'emd_calibration_due_date'        => !empty($ctVal['emd_calibration_due_date']) ? Date::createFromFormat('d/m/Y', $ctVal['emd_calibration_due_date'])->format('Y-m-d') : null,

            //             'status' => 'Y',
            //         ]);
            //     }
            // }

            // EquipmentMPTDetails::where('emd_em_id',$request->id)->where('status','D')->delete();

            DB::commit();

            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('update_success'),
            ]);
            

        } catch (\Exception $e) {

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
            // $grn_id = GRNDetails::where('grnd_pod_id',$request->id)->get();
            // if($grn_id->isNotEmpty())
            // {
            //     return response()->json([
            //         'response_code' => '0',
            //         'response_message' => "You Can't Delete, Purchase Order Is Used In GRN.",
            //     ]);
            // }
            $usage = DB::select('CALL mpt_equipment_used_list(?)', [$request->id]);
            if (!empty($usage)) {
                $message = "You Can't Delete, Equipment - MPT Is Used In ". $usage[0]->table_name.".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

           
            EquipmentMPT::where('em_id',$request->id)->delete();
            // EquipmentMPTDetails::where('emd_em_id',$request->id)->delete();
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

    function getPendingGrnListForEquipmentMpt(Request $request)
    {
        $itemTypes = getItemType();
        $yearIds   = getCompanyYearIdsToTill();
        $LocationData = getCurrentLocation()->location_id;
        $opening_data = DB::table('mpt_equipment_pending_qty')->select(['mpt_equipment_pending_qty.table_pk_id','mpt_equipment_pending_qty.table_unique_id','grn_supplier.grn_number','grn_supplier.grn_date','suppliers.supplier_name','grn_supplier.grn_challan_number','grn_supplier.grn_challan_date','item.item_name','item_group.item_group','item.item_type','unit.unit','mpt_equipment_pending_qty.pending_qty',   
        DB::raw("
            CASE 
                WHEN mpt_equipment_pending_qty.table_unique_id = 'GRN' 
                    THEN grn_supplier_details.grnd_qty
                WHEN mpt_equipment_pending_qty.table_unique_id = 'Opening' 
                    THEN item_opening.io_opening_qty
                ELSE 0
            END as qty
        ")
        ])
        ->leftJoin('item_opening', function ($join) {
            $join->on('item_opening.io_id', '=', 'mpt_equipment_pending_qty.table_pk_id')
                ->where('mpt_equipment_pending_qty.table_unique_id', '=', 'Opening');
        })
        ->leftJoin('grn_supplier_details', function ($join) {
            $join->on('grn_supplier_details.grnd_id', '=', 'mpt_equipment_pending_qty.table_pk_id')
                ->where('mpt_equipment_pending_qty.table_unique_id', '=', 'GRN');
        })
        ->leftJoin('grn_supplier', 'grn_supplier.grn_id', '=', 'grn_supplier_details.grnd_grn_id')
        ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
        ->leftJoin('item', 'item.id', '=', 'mpt_equipment_pending_qty.item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->where('mpt_equipment_pending_qty.location_id',$LocationData)
        ->where('mpt_equipment_pending_qty.pending_qty', '>', 0)
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


    public function getPendingGrnForEquipmentMpt(Request $request)
    {
        $itemTypes = getItemType();
        $LocationData = getCurrentLocation()->location_id;

        $request->table_pk_ids = explode(',', $request->table_pk_ids);
        $request->table_unique_ids = explode(',', $request->table_unique_ids);

        $data = DB::table('mpt_equipment_pending_qty')
            ->select([
                'mpt_equipment_pending_qty.table_pk_id',
                'mpt_equipment_pending_qty.table_unique_id',
                'grn_supplier.grn_number',
                'grn_supplier.grn_date',
                'suppliers.supplier_name',
                'grn_supplier.grn_challan_number',
                'grn_supplier.grn_challan_date',
                'item.item_name',
                'item_group.item_group',
                'item.item_type',
                'item.id as item_id',
                'mpt_equipment_pending_qty.pending_qty',

                DB::raw("
                    CASE 
                        WHEN mpt_equipment_pending_qty.table_unique_id = 'GRN' 
                            THEN grn_supplier_details.grnd_qty
                        WHEN mpt_equipment_pending_qty.table_unique_id = 'Opening' 
                            THEN item_opening.io_opening_qty
                        ELSE 0
                    END as qty
                ")
            ])

            ->leftJoin('item_opening', function ($join) {
                $join->on('item_opening.io_id', '=', 'mpt_equipment_pending_qty.table_pk_id')
                    ->where('mpt_equipment_pending_qty.table_unique_id', '=', 'Opening');
            })

            ->leftJoin('grn_supplier_details', function ($join) {
                $join->on('grn_supplier_details.grnd_id', '=', 'mpt_equipment_pending_qty.table_pk_id')
                    ->where('mpt_equipment_pending_qty.table_unique_id', '=', 'GRN');
            })

            ->leftJoin('grn_supplier', 'grn_supplier.grn_id', '=', 'grn_supplier_details.grnd_grn_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
            ->leftJoin('item', 'item.id', '=', 'mpt_equipment_pending_qty.item_id')
            ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')

            ->where('mpt_equipment_pending_qty.location_id', $LocationData)
            ->whereIn('mpt_equipment_pending_qty.table_pk_id', $request->table_pk_ids)
            ->whereIn('mpt_equipment_pending_qty.table_unique_id', $request->table_unique_ids)
            ->where('mpt_equipment_pending_qty.pending_qty', '>', 0)

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
 