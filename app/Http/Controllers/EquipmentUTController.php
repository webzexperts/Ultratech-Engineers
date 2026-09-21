<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EquipmentUT;
use App\Models\EquipmentUTDetails;
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

class EquipmentUTController extends Controller
{
    public function manage()
    {
        return view('manage.manage-equipment_ut');
    }

    public function index(EquipmentUT $equipment_ut_data, Request $request, DataTables $datatables)
    {
        $LocationData = getCurrentLocation();
        $equipment_ut_data = EquipmentUT::select([
            'equipment_ut.eu_id',
            'equipment_ut.table_unique_id',
            'equipment_ut.table_pk_id',
            'item.item_name',
            'equipment_ut.eu_equipment_name',
            'equipment_ut.eu_make',
            'equipment_ut.eu_display',
            'equipment_ut.eu_serial_no',
            'equipment_ut.eu_next_cali_due_date',
            'equipment_ut.eu_last_cali_date',
            'equipment_ut.eu_doc_ref_no',
            'equipment_ut.eu_status',
            'equipment_ut.created_on',
            'equipment_ut.created_by',
            'equipment_ut.last_by',
            'equipment_ut.last_on',
            'current_location.location_name as current_location',
            'own_location.location_name as own_location',
        ])
        // ->leftJoin('equipment_ut_details', 'equipment_ut_details.eud_eu_id', '=', 'equipment_ut.eu_id')
        ->leftJoin('item_opening','item_opening.io_id','=','equipment_ut.table_pk_id')
        ->leftJoin('location as current_location','current_location.location_id', '=','equipment_ut.current_location_id')
        ->leftJoin('location as own_location','own_location.location_id', '=','equipment_ut.own_location_id')
        ->leftJoin('item', 'item.id', '=', 'item_opening.io_item_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id');
        if ($LocationData->location_type != 'HO') {
            $equipment_ut_data->where(function($query) use ($LocationData) {
                $query->where('equipment_ut.current_location_id', $LocationData->location_id)
                ->orWhere('equipment_ut.own_location_id', $LocationData->location_id);
            });
        }
        $dataTable = DataTables::of($equipment_ut_data)
        ->editColumn('eu_last_cali_date', function($equipment_ut_data){
            if ($equipment_ut_data->eu_last_cali_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $equipment_ut_data->eu_last_cali_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->editColumn('eu_next_cali_due_date', function($equipment_ut_data){
            if ($equipment_ut_data->eu_next_cali_due_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $equipment_ut_data->eu_next_cali_due_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('equipment_ut.eu_next_cali_due_date', function ($q, $k) {
            applyDate($q, $k, 'equipment_ut.eu_next_cali_due_date');
        })
        ->filterColumn('equipment_ut.eu_last_cali_date', function ($q, $k) {
            applyDate($q, $k, 'equipment_ut.eu_last_cali_date');
        })        ->filterColumn('equipment_ut_details.eud_calibration_due_date', function ($q, $k) {
            applyDate($q, $k, 'equipment_ut_details.eud_calibration_due_date');
        })
        ->filterColumn('equipment_ut.eu_status', function ($query, $keyword) {
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
                $query->where('equipment_ut.eu_status', '=', $searchStatus);
            } 
            else {
                $dbFormatKeyword = str_replace(' ', '_', $lowerKeyword);
                $query->where('equipment_ut.eu_status', 'like', "$dbFormatKeyword%");
            }
        })
        
        ->addColumn('options',function($equipment_ut_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("equipment_ut", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-equipment_ut"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("equipment_ut", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'equipment_ut');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function equipmentLNRData()
    {
        $lnr_data = EquipmentUT::select('eu_id','eu_inward_type')->orderBy('eu_id','Desc')->first();
       
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
            // $old_qty = EquipmentUT::where('table_pk_id',$request->table_pk_id)->count();

            $pendingQty = DB::table('ut_equipment_pending_qty')
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
            if(!empty($request->eu_equipment_name) && !empty($request->eu_serial_no)  && !empty($request->eu_make)) {
                $name_for_display = $request->eu_equipment_name.' - '.$request->eu_serial_no.' - '.$request->eu_make;
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

            $equipment_ut_data = EquipmentUT::create([
                'eu_equipment_name'       => $request->eu_equipment_name !="" ? $request->eu_equipment_name : null,
                'eu_make'                 => $request->eu_make !="" ? $request->eu_make : null,
                'eu_display'              => $request->eu_display !="" ? $request->eu_display : null,
                'eu_serial_no'            => $request->eu_serial_no !="" ? $request->eu_serial_no : null,
                'eu_cali_freq'            => $request->ins_cali_freq !="" ? $request->ins_cali_freq : null,
                'eu_last_cali_date'       => isset($request->eu_last_cali_date) ? Date::createFromFormat('d/m/Y', $request->eu_last_cali_date)->format('Y-m-d') : null,
                'eu_next_cali_due_date'   => isset($request->eu_next_cali_due_date) ? Date::createFromFormat('d/m/Y', $request->eu_next_cali_due_date)->format('Y-m-d') : null,
                'eu_doc_ref_no'           => $request->eu_doc_ref_no  !="" ? $request->eu_doc_ref_no : null,
                'eu_cali_certificate'     => $cali_certificate_path,
                'eu_cali_certificate_blob'=> $cali_certificate_blob,
                'eu_status'               => $request->eu_status  !="" ? $request->eu_status : null,
                'own_location_id'         => $LocationData,
                'current_location_id'     => $LocationData,
                'table_unique_id'         => $request->table_unique_id ?? null,
                'table_pk_id'             => $request->table_pk_id ?? null,
                'item_id'                 => $request->item_id ?? null,
                'name_for_display'        =>  $name_for_display,
                'status_transaction_id' => 0,
                'company_id'              => Auth::user()->company_id,
                'created_on'              => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'              => Auth::user()->id,
            ]);

            // $equipment_ut_details_data = $request->equipment_ut_details_data = json_decode($request->equipment_ut_details_data, true);
            // if(!empty($equipment_ut_details_data))
            // {
            //     foreach($equipment_ut_details_data as $ctKey => $ctVal)
            //     {
            //         if($ctVal != null)
            //         {
            //             $equipment_ut_details_data = EquipmentUTDetails::create([
            //                 'eud_eu_id'            => $equipment_ut_data->eu_id,

            //                 'eud_calibration_due_date'        => !empty($ctVal['eud_calibration_due_date']) ?  Date::createFromFormat('d/m/Y', $ctVal['eud_calibration_due_date'])->format('Y-m-d') : null,

            //                 'status' => 'Y',
            //             ]);
            //         }
            //     }
            // }

            if($equipment_ut_data->save())
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
        $equipment_ut_data =  DB::select('CALL equipment_ut_master(?)',  [$request->id]);
        if (!empty($equipment_ut_data)) {
            $equipment_ut_data = $equipment_ut_data[0];
            if(isset($equipment_ut_data->eu_last_cali_date)){
                $equipment_ut_data->eu_last_cali_date = $equipment_ut_data->eu_last_cali_date != "" ? Date::createFromFormat('Y-m-d',  $equipment_ut_data->eu_last_cali_date)->format('d/m/Y') : "";
            }
            if(!empty($equipment_ut_data->eu_next_cali_due_date)){
                $equipment_ut_data->eu_next_cali_due_date = $equipment_ut_data->eu_next_cali_due_date != "" ? Date::createFromFormat('Y-m-d',  $equipment_ut_data->eu_next_cali_due_date)->format('d/m/Y') : "";
            }
            $equipment_ut_data->login_location = $LocationData; 
            $equipment_ut_data->main_group = $itemTypes[$equipment_ut_data->main_group] ?? $equipment_ut_data->main_group;

            if(!empty($equipment_ut_data->grn_date)){
                $equipment_ut_data->grn_date = $equipment_ut_data->grn_date != "" ? Date::createFromFormat('Y-m-d',  $equipment_ut_data->grn_date)->format('d/m/Y') : "";
            }
            if(!empty($equipment_ut_data->grn_challan_date)){
                $equipment_ut_data->grn_challan_date = $equipment_ut_data->grn_challan_date != "" ? Date::createFromFormat('Y-m-d',  $equipment_ut_data->grn_challan_date)->format('d/m/Y') : "";
            }

            if(isset($equipment_ut_data->eu_cali_certificate_blob))
            {
                $equipment_ut_data->eu_cali_certificate_blob = base64_encode($equipment_ut_data->eu_cali_certificate_blob);
            }


            // $check_item = Item::where('item.id',$equipment_ut_data->eu_ut_equipment_id)->value('item.status');

        }
        // $equipment_ut_details_data = DB::select('CALL equipment_ut_details(?)', [$request->id]);
        // if($equipment_ut_details_data){
        //     foreach($equipment_ut_details_data as $dKey => $dVal)
        //     {
        //         $dVal->eud_calibration_due_date = $dVal->eud_calibration_due_date != "" ? Date::createFromFormat('Y-m-d',  $dVal->eud_calibration_due_date)->format('d/m/Y') : "";
        //     }
        // }
        // if($check_item == 'Deactive'){
        //     $items = getDeactiveItems('equipment_ut', 'eu_ut_equipment_id', $request->id, 'ut_equipment','eu_id');
        // }else{
        //     $items= '';
        // }
        if($equipment_ut_data){
            $is_used = DB::table('grn_supplier_details')
                ->where('sr_table_unique_id', 'ut_equipment')
                ->where('sr_table_pk_id', $request->id)
                ->where('for_calibration', 'Yes')
                ->where('status', 'Y')
                ->exists();

            return response()->json([
                'equipment_ut_data'         => $equipment_ut_data,
                'is_used'                    => $is_used,
                // 'equipment_ut_details_data' => $equipment_ut_details_data,
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
        $LocationData = getCurrentLocation()->location_id;
        $equipmentMPT = EquipmentUT::where('eu_id', $request->id)->first();
        if ($equipmentMPT->current_location_id != $LocationData) {
            DB::rollBack();
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Unauthorized Record Can`t Update.',
            ]);
        }
        DB::beginTransaction();
        // dd($request->all());
        try{
            $name_for_display = '';
            if(!empty($request->eu_equipment_name) && !empty($request->eu_serial_no)  && !empty($request->eu_make)) {
                $name_for_display = $request->eu_equipment_name.' - '.$request->eu_serial_no.' - '.$request->eu_make;
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

            $equipment_ut_data = EquipmentUT::where('eu_id', $request->id)->update([
                'eu_equipment_name'       => $request->eu_equipment_name !="" ? $request->eu_equipment_name : null,
                'eu_make'                 => $request->eu_make !="" ? $request->eu_make : null,
                'eu_display'              => $request->eu_display !="" ? $request->eu_display : null,
                'eu_serial_no'            => $request->eu_serial_no !="" ? $request->eu_serial_no : null,
                'eu_cali_freq'            => $request->ins_cali_freq !="" ? $request->ins_cali_freq : null,
                'eu_last_cali_date'       => isset($request->eu_last_cali_date) ? Date::createFromFormat('d/m/Y', $request->eu_last_cali_date)->format('Y-m-d') : null,
                'eu_next_cali_due_date'   => isset($request->eu_next_cali_due_date) ? Date::createFromFormat('d/m/Y', $request->eu_next_cali_due_date)->format('Y-m-d') : null,
                'eu_doc_ref_no'           => $request->eu_doc_ref_no  !="" ? $request->eu_doc_ref_no : null,
                'eu_cali_certificate'     => $cali_certificate_path,
                'eu_cali_certificate_blob'=> $cali_certificate_blob,
                // 'eu_status'               => $request->eu_status  !="" ? $request->eu_status : null,
                'own_location_id'         => $LocationData,
                'current_location_id'     => $LocationData,
                'table_unique_id'         => $request->table_unique_id ?? null,
                'table_pk_id'             => $request->table_pk_id ?? null,
                'item_id'                 => $request->item_id ?? null,
                'name_for_display'        =>  $name_for_display,
                'last_on'       => Carbon::now('Asia/Kolkata'),
                'last_by'       => Auth::id(),
            ]);
            
            if(!$equipment_ut_data){
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }
            // $update_details =  EquipmentUTDetails::where('eud_eu_id',$request->id)->update(['status' => 'D',]);
            // $equipment_ut_details_data = json_decode($request->equipment_ut_details_data, true);

            // foreach($equipment_ut_details_data as $ctVal)
            // {
            //     if($ctVal['eud_id'] == 0)
            //     {
            //         EquipmentUTDetails::create([
            //             'eud_eu_id' => $request->id,

            //             'eud_calibration_due_date'        => !empty($ctVal['eud_calibration_due_date']) ? Date::createFromFormat('d/m/Y', $ctVal['eud_calibration_due_date'])->format('Y-m-d') : null,

            //             'status' => 'Y',
            //         ]);
            //     }
            //     else
            //     {
            //         EquipmentUTDetails::where('eud_id', $ctVal['eud_id'])->update([

            //             'eud_calibration_due_date'        => !empty($ctVal['eud_calibration_due_date']) ? Date::createFromFormat('d/m/Y', $ctVal['eud_calibration_due_date'])->format('Y-m-d') : null,

            //             'status' => 'Y',
            //         ]);
            //     }
            // }

            // EquipmentUTDetails::where('eud_eu_id',$request->id)->where('status','D')->delete();

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
            $usage = DB::select('CALL ut_equipment_used_list(?)', [$request->id]);
            if (!empty($usage)) {
                $message = "You Can't Delete, Equipment - UT Is Used In ". $usage[0]->table_name.".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

           
            $equipment = EquipmentUT::where('eu_id',$request->id)->first();
            if ($equipment && !empty($equipment->eu_cali_certificate)) {
                $file_model = new File();
                $file_model->delete_file($equipment->eu_cali_certificate);
            }
            EquipmentUT::where('eu_id',$request->id)->delete();
            // EquipmentUTDetails::where('eud_eu_id',$request->id)->delete();
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

    public function existsMake(Request $request)
    {
        if($request->term != "")
        {
            $fdEquipmentUT = EquipmentUT::select('eu_make')->where('eu_make', 'LIKE', $request->term.'%')->groupBy('eu_make')->get();
                // dd($fdEquipmentUT);
            if($fdEquipmentUT != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdEquipmentUT as $dsKey)
                {
                    $output .= '<li parent-id="eu_make" list-id="eu_make_list" class="list-group-item" tabindex="0">'.$dsKey->eu_make.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'MakeList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Make Available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'MakeList' => '',
                'response_code' => 1,
            ]);
        }
    }
    public function existsDisplay(Request $request)
    {
        if($request->term != "")
        {
            $fdEquipmentUT = EquipmentUT::select('eu_display')->where('eu_display', 'LIKE', $request->term.'%')->groupBy('eu_display')->get();
                // dd($fdEquipmentUT);
            if($fdEquipmentUT != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdEquipmentUT as $dsKey)
                {
                    $output .= '<li parent-id="eu_display" list-id="eu_display_list" class="list-group-item" tabindex="0">'.$dsKey->eu_display.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'DisplayList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Display Available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'DisplayList' => '',
                'response_code' => 1,
            ]);
        }
    }
    public function existsCalibrationStandard(Request $request)
    {
        if($request->term != "")
        {
            $fdEquipmentUT = EquipmentUT::select('eu_calibration_standard')->where('eu_calibration_standard', 'LIKE', $request->term.'%')->groupBy('eu_calibration_standard')->get();
                // dd($fdEquipmentUT);
            if($fdEquipmentUT != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdEquipmentUT as $dsKey)
                {
                    $output .= '<li parent-id="eu_calibration_standard" list-id="eu_calibration_standard_list" class="list-group-item" tabindex="0">'.$dsKey->eu_calibration_standard.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'CalibrationStandardList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Calibration Standard Available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'CalibrationStandardList' => '',
                'response_code' => 1,
            ]);
        }
    }
    public function existsCalibrationTechnique(Request $request)
    {
        if($request->term != "")
        {
            $fdEquipmentUT = EquipmentUT::select('eu_calibration_technique')->where('eu_calibration_technique', 'LIKE', $request->term.'%')->groupBy('eu_calibration_technique')->get();
                // dd($fdEquipmentUT);
            if($fdEquipmentUT != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdEquipmentUT as $dsKey)
                {
                    $output .= '<li parent-id="eu_calibration_technique" list-id="eu_calibration_technique_list" class="list-group-item" tabindex="0">'.$dsKey->eu_calibration_technique.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'CalibrationTechniqueList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Calibration Technique Available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'CalibrationTechniqueList' => '',
                'response_code' => 1,
            ]);
        }
    }

    function getPendingGrnListForEquipmentUt(Request $request)
    {
        $itemTypes = getItemType();
        $yearIds   = getCompanyYearIdsToTill();
        $LocationData = getCurrentLocation()->location_id;
        
        $opening_data = DB::table('ut_equipment_pending_qty')->select(['ut_equipment_pending_qty.table_pk_id',
        'ut_equipment_pending_qty.table_unique_id','grn_supplier.grn_number','grn_supplier.grn_date','suppliers.supplier_name','grn_supplier.grn_challan_number','grn_supplier.grn_challan_date','item.item_name','item_group.item_group','item.item_type','unit.unit','ut_equipment_pending_qty.pending_qty',   
        DB::raw("
            CASE 
                WHEN ut_equipment_pending_qty.table_unique_id = 'GRN' 
                    THEN grn_supplier_details.grnd_qty
                WHEN ut_equipment_pending_qty.table_unique_id = 'Opening' 
                    THEN item_opening.io_opening_qty
                ELSE 0
            END as qty
        ")
        ])
        ->leftJoin('item_opening', function ($join) {
            $join->on('item_opening.io_id', '=', 'ut_equipment_pending_qty.table_pk_id')
                ->where('ut_equipment_pending_qty.table_unique_id', '=', 'Opening');
        })
        ->leftJoin('grn_supplier_details', function ($join) {
            $join->on('grn_supplier_details.grnd_id', '=', 'ut_equipment_pending_qty.table_pk_id')
                ->where('ut_equipment_pending_qty.table_unique_id', '=', 'GRN');
        })
        ->leftJoin('grn_supplier', 'grn_supplier.grn_id', '=', 'grn_supplier_details.grnd_grn_id')
        ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
        ->leftJoin('item', 'item.id', '=', 'ut_equipment_pending_qty.item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->where('ut_equipment_pending_qty.location_id',$LocationData)
        ->where('ut_equipment_pending_qty.pending_qty', '>', 0)
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

    public function getPendingGrnForEquipmentUt(Request $request){
        $itemTypes = getItemType();
        $LocationData = getCurrentLocation()->location_id;

        $request->table_pk_ids = explode(',', $request->table_pk_ids);
        $request->table_unique_ids = explode(',', $request->table_unique_ids);

        $data = DB::table('ut_equipment_pending_qty')
        ->select([
            'ut_equipment_pending_qty.table_pk_id',
            'ut_equipment_pending_qty.table_unique_id',
            'grn_supplier.grn_number',
            'grn_supplier.grn_date',
            'suppliers.supplier_name',
            'grn_supplier.grn_challan_number',
            'grn_supplier.grn_challan_date',
            'item.item_name',
            'item_group.item_group',
            'item.item_type',
            'item.id as item_id',
            'ut_equipment_pending_qty.pending_qty',

            DB::raw("
                CASE 
                    WHEN ut_equipment_pending_qty.table_unique_id = 'GRN' 
                        THEN grn_supplier_details.grnd_qty
                    WHEN ut_equipment_pending_qty.table_unique_id = 'Opening' 
                        THEN item_opening.io_opening_qty
                    ELSE 0
                END as qty
            ")
        ])

        ->leftJoin('item_opening', function ($join) {
            $join->on('item_opening.io_id', '=', 'ut_equipment_pending_qty.table_pk_id')
                ->where('ut_equipment_pending_qty.table_unique_id', '=', 'Opening');
        })

        ->leftJoin('grn_supplier_details', function ($join) {
            $join->on('grn_supplier_details.grnd_id', '=', 'ut_equipment_pending_qty.table_pk_id')
                ->where('ut_equipment_pending_qty.table_unique_id', '=', 'GRN');
        })

        ->leftJoin('grn_supplier', 'grn_supplier.grn_id', '=', 'grn_supplier_details.grnd_grn_id')
        ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
        ->leftJoin('item', 'item.id', '=', 'ut_equipment_pending_qty.item_id')
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')

        ->where('ut_equipment_pending_qty.location_id', $LocationData)
        ->whereIn('ut_equipment_pending_qty.table_pk_id', $request->table_pk_ids)
        ->whereIn('ut_equipment_pending_qty.table_unique_id', $request->table_unique_ids)
        ->where('ut_equipment_pending_qty.pending_qty', '>', 0)

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
 