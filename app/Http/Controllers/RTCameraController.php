<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\RTCamera;
use App\Models\RTCameraDetails;
use App\Models\GRN;
use App\Models\ItemOpening;
use App\Models\Transaction\InterLocationTransferDetails;
use App\Models\Transaction\SupplierDCDetails;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\File;

class RTCameraController extends Controller
{
    public function manage()
    {
        return view('manage.manage-rt_camera');
    }

    public function index(RTCamera $rt_camera_data, Request $request, DataTables $datatables)
    {
        $LocationData = getCurrentLocation();
        // dd($LocationData);
        $rt_camera_data = RTCamera::select([
            'rt_camera.rt_camera_id',
            'rt_camera.table_unique_id',
            'rt_camera.table_pk_id',
            'item.item_name',
            'rt_camera.rt_camera_name',
            'rt_camera.rt_serial_no',
            'rt_camera.rt_isotope',
            'rt_camera.rt_x_ray',
            'rt_camera.rt_focal_spot',
            'rt_camera.rt_document_ref_no',
            'rt_camera.rt_validity_date',
            'rt_camera.rt_aerb_no',
            'rt_camera.application_no',
            'rt_camera.movement_approval',
            'rt_camera.validity',
            // 'rt_camera.rt_remark',
            'rt_camera.rt_status',
            'rt_camera.current_location_id',
            'rt_camera.created_on',
            'rt_camera.created_by',
            'rt_camera.last_by',
            'rt_camera.last_on',
            'rt_camera_details.rtcd_id',
            'rt_camera_details.rtcd_rt_camera_id',
            'rt_camera_details.rtcd_last_of_loading_date',
            'rt_camera_details.rtcd_initial_activity_ci',
            'rt_camera_details.rtcd_source_size',
            'rt_camera_details.rtcd_pencil_no',
            'rt_camera_details.rtcd_iga_no',
            'current_location.location_name as current_location',
            'own_location.location_name as own_location',
        ])
        //->leftJoin('rt_camera_details','rt_camera_details.rtcd_rt_camera_id','=','rt_camera.rt_camera_id')
        ->leftJoin('rt_camera_details', function ($join) {
            $join->on('rt_camera_details.rtcd_rt_camera_id', '=', 'rt_camera.rt_camera_id')
                ->whereRaw('rt_camera_details.rtcd_id = (
                    SELECT MAX(rtcd_id) 
                    FROM rt_camera_details 
                    WHERE rtcd_rt_camera_id = rt_camera.rt_camera_id
                )');
        })

        ->leftJoin('item_opening','item_opening.io_id','=','rt_camera.table_pk_id')
        ->leftJoin('location as current_location','current_location.location_id', '=','rt_camera.current_location_id')
        ->leftJoin('location as own_location','own_location.location_id', '=','rt_camera.own_location_id')
        ->leftJoin('item','item.id','=','item_opening.io_item_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id');
        
        if ($LocationData->location_type != 'HO') {
            $rt_camera_data->where(function($query) use ($LocationData) {
                $query->where('rt_camera.current_location_id', $LocationData->location_id)
                ->orWhere('rt_camera.own_location_id', $LocationData->location_id);
            });
        }
       
        $dataTable = DataTables::of($rt_camera_data)
        ->editColumn('rt_validity_date', function($rt_camera_data) {
            if ($rt_camera_data->rt_validity_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $rt_camera_data->rt_validity_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('rt_camera.rt_validity_date', function ($q, $k) {
            applyDate($q, $k, 'rt_camera.rt_validity_date');
        })

        ->editColumn('rtcd_last_of_loading_date', function($rt_camera_data) {
            if ($rt_camera_data->rtcd_last_of_loading_date != null) {
                $formatedDate1 = Date::createFromFormat('Y-m-d', $rt_camera_data->rtcd_last_of_loading_date)->format(DATE_FORMAT); return $formatedDate1;
            } else {
                return '';
            }
        })
        ->filterColumn('rt_camera_details.rtcd_last_of_loading_date', function ($q, $k) {
            applyDate($q, $k, 'rt_camera_details.rtcd_last_of_loading_date');
        })
        ->filterColumn('rt_camera.rt_status', function ($query, $keyword) {
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
                $query->where('rt_camera.rt_status', '=', $searchStatus);
            } 
            else {
                $dbFormatKeyword = str_replace(' ', '_', $lowerKeyword);
                $query->where('rt_camera.rt_status', 'like', "$dbFormatKeyword%");
            }
        })
        ->editColumn('movement_approval', function($rt_camera_data){
            if(!empty($rt_camera_data->movement_approval))
            {
                $documentUrl = asset('storage/' . $rt_camera_data->movement_approval);
                $document = '<a href="' . $documentUrl . '" target="_blank">
                <i class="ri-eye-fill action-icon remove_filters_short_qty" ></i>
                </a>';
                return $document;
            }
            return '';
        })
         ->editColumn('validity', function($rt_camera_data){
            if ($rt_camera_data->validity != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $rt_camera_data->validity)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('rt_camera.validity', function ($q, $k) {
            applyDate($q, $k, 'rt_camera.validity');
        })
        ->addColumn('options',function($rt_camera_data) use ($LocationData) {
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("rt_camera", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_rt_camera"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                // if (hasAccess("rt_camera", "delete") && $rt_camera_data->current_location_id == $LocationData->location_id) {
                if (hasAccess("rt_camera", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'rt_camera');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on','movement_approval','validity'])
        ->make(true);
    }


    public function store(Request $request)
    {
        // dd($request->all());
        $LocationData = getCurrentLocation()->location_id;

        $movement_images = '';
        $movement_blobImage = '';
        if($request->movement_approval_doc)
        {
            $file = new File();
            $isFound = $file->getFileFromTemp($request->movement_approval_doc, $prefix = 'movement_approval');
            if($isFound !== false)
            {
                $movement_images = $isFound;
                $filePath = storage_path('app/public/' . $movement_images);
                if (!empty($movement_images) && $file->Is_Files_Exists($movement_images))
                {
                    $movement_blobImage = file_get_contents($filePath);
                }
            }
        }
        DB::beginTransaction();
        $name_for_display = '';
        if(!empty($request->rt_camera_name) && !empty($request->rt_serial_no)  && !empty($request->rt_isotope)) {
            $name_for_display = $request->rt_camera_name.' - '.$request->rt_serial_no.' - '.$request->rt_isotope;
        }
        try
        {
            // $old_qty = RTCamera::where('table_pk_id',$request->table_pk_id)->count();

            $pendingQty =  DB::table('rt_camera_pending_qty')
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

            $rt_camera_data =  RTCamera::create([
                'rt_camera_name' => $request->rt_camera_name ?? null,
                'rt_serial_no' => $request->rt_serial_no ?? null,
                'rt_isotope' => $request->rt_isotope ?? null,
                'rt_x_ray' => $request->rt_x_ray ?? null,
                'rt_focal_spot' => $request->rt_focal_spot ?? null,
                'rt_document_ref_no' => $request->rt_document_ref_no ?? null,
                'rt_validity_date' => isset($request->rt_validity_date) ? Date::createFromFormat('d/m/Y', $request->rt_validity_date)->format('Y-m-d') : null,
                'rt_aerb_no' => $request->rt_aerb_no ?? null,
                'application_no' => $request->application_no ?? null,
                'movement_approval'      => $movement_images != "" ? $movement_images : null,
                'movement_approval_blob' => $movement_blobImage != "" ? $movement_blobImage : null,
                'validity'               => isset($request->validity) ? Date::createFromFormat('d/m/Y', $request->validity)->format('Y-m-d') : null,

                'table_unique_id' => $request->table_unique_id ?? null,
                'table_pk_id' => $request->table_pk_id ?? null,
                'item_id' => $request->item_id ?? null,
                'rt_status' => $request->rt_status ?? null,
                'status_transaction_id' => 0,
                'own_location_id' => $LocationData,
                'current_location_id' => $LocationData,
                'name_for_display' =>  $name_for_display,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id,
            ]);

            $rt_camera_details_data = $request->rt_camera_details_data = json_decode($request->rt_camera_details_data, true);
            if(!empty($rt_camera_details_data))
            {
                foreach($rt_camera_details_data as $ctKey => $ctVal)
                {
                    if($ctVal != null)
                    {

                        $images = '';
                        $blobImage = '';
                        if(!empty($ctVal['rtcd_decay_chart_doc']))
                        {
                            $file = new File();
                            $isFound =  $file->getFileFromTemp($ctVal['rtcd_decay_chart_doc'],$prefix = 'rt_camera');
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
                        


                        $rt_camera_details_data = RTCameraDetails::create([
                            'rtcd_rt_camera_id' => $rt_camera_data->rt_camera_id,
                            'rtcd_last_of_loading_date' => isset($ctVal['rtcd_last_of_loading_date']) ? Date::createFromFormat('d/m/Y',$ctVal['rtcd_last_of_loading_date'])->format('Y-m-d') : "",
                            'rtcd_initial_activity_ci' => !empty($ctVal['rtcd_initial_activity_ci']) ? $ctVal['rtcd_initial_activity_ci'] : null,
                            'rtcd_source_size' => !empty($ctVal['rtcd_source_size']) ? $ctVal['rtcd_source_size'] : null,
                            'rtcd_pencil_no' => !empty($ctVal['rtcd_pencil_no']) ? $ctVal['rtcd_pencil_no'] : null,
                           // 'rtcd_iga_no' => !empty($ctVal['rtcd_iga_no']) ? $ctVal['rtcd_iga_no'] : null,
                            'rtcd_decay_chart' =>  $images != '' ? $images : null,
                            'rtcd_decay_chart_blob' =>  $blobImage  ? $blobImage : null,
                            'status' => 'Y',
                        ]);
                    }
                }
            }

            if($rt_camera_data->save())
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
        $rt_camera_data =  DB::select('CALL rt_camera_master(?)', [$request->id]);
        if(!empty($rt_camera_data))
        {
            $rt_camera_data = $rt_camera_data[0];
            //$rt_camera_data->rt_validity_date = $rt_camera_data->rt_validity_date != "" ? Date::createFromFormat('Y-m-d',  $rt_camera_data->rt_validity_date)->format('d/m/Y') : "";

            $rt_camera_data->login_location = $LocationData; 
            $rt_camera_data->main_group = $itemTypes[$rt_camera_data->main_group] ?? $rt_camera_data->main_group;
            $rt_camera_data->grn_date = $rt_camera_data->grn_date != "" ? Date::createFromFormat('Y-m-d',  $rt_camera_data->grn_date)->format('d/m/Y') : "";
            $rt_camera_data->grn_challan_date = $rt_camera_data->grn_challan_date != "" ? Date::createFromFormat('Y-m-d',  $rt_camera_data->grn_challan_date)->format('d/m/Y') : "";

            if(!empty($rt_camera_data) && isset($rt_camera_data->movement_approval_blob))
            {
                $rt_camera_data->movement_approval_blob = base64_encode($rt_camera_data->movement_approval_blob);
            }
            if(!empty($rt_camera_data->validity)){

                $rt_camera_data->validity = $rt_camera_data->validity != "" ? Date::createFromFormat('Y-m-d',  $rt_camera_data->validity)->format('d/m/Y') : "";
            }

            $isTransferUsed = InterLocationTransferDetails::where('sr_table_unique_id', 'rt_camera')
                ->where('sr_table_pk_id', $request->id)
                ->exists();

            $isSupplierDCUsed = SupplierDCDetails::where('sr_table_unique_id', 'rt_camera')
                ->where('sr_table_pk_id', $request->id)
                ->exists();

            $rt_camera_data->is_locked = ($isTransferUsed || $isSupplierDCUsed);
        }

        $rt_camera_details_data = DB::select('CALL rt_camera_details(?)', [$request->id]);
        foreach($rt_camera_details_data as $dKey => $dVal)
        {
            $dVal->rtcd_last_of_loading_date = $dVal->rtcd_last_of_loading_date != "" ? Date::createFromFormat('Y-m-d',  $dVal->rtcd_last_of_loading_date)->format('d/m/Y') : "";

            $dVal->rtcd_decay_chart_doc = $dVal->rtcd_decay_chart;
            $dVal->rtcd_decay_chart_blob = base64_encode($dVal->rtcd_decay_chart_blob);
            
        }

        // $check_item = Item::where('item.id',$rt_camera_data->status)->value('item.status');

        // // used for getting deactivate items
        // if($check_item == 'Deactive')
        // {
        //     // table name, table item id, request id, item type, table main id
        //     $items = getDeactiveItems('rt_camera', 'rt_camera_type_id', $request->id, 'rt_camera','rt_camera_id');
        // }
        // else
        // {
        //     $items = '';
        // }

        if($rt_camera_data)
        {
            $is_used = DB::table('grn_supplier_details')
                ->where('sr_table_unique_id', 'rt_camera')
                ->where('sr_table_pk_id', $request->id)
                ->where('for_calibration', 'Yes')
                ->where('status', 'Y')
                ->exists();

            return response()->json([
                'rt_camera_data'     => $rt_camera_data,
                'rt_camera_details_data'     => $rt_camera_details_data,
                'is_used'            => $is_used,
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
        $rtCamera = RTCamera::where('rt_camera_id', $request->id)->first();

        $isTransferUsed = InterLocationTransferDetails::where('sr_table_unique_id', 'rt_camera')
            ->where('sr_table_pk_id', $request->id)
            ->exists();

        $isSupplierDCUsed = SupplierDCDetails::where('sr_table_unique_id', 'rt_camera')
            ->where('sr_table_pk_id', $request->id)
            ->exists();

        $isLocked = ($isTransferUsed || $isSupplierDCUsed);

        $file = new File();
        $move_imgs = '';
        $movement_blobImage = '';

        if (!$isLocked) {
            $movement_imgs = $rtCamera ? $rtCamera->movement_approval : null;
            if($request->movement_approval_doc != '' && $request->movement_approval_doc != null){
                if($movement_imgs && !empty($movement_imgs)){
                    if($movement_imgs != $request->movement_approval_doc){
                        $file->delete_file($movement_imgs);
                    }
                }

                $isFound = $file->getFileFromTemp($request->movement_approval_doc, 'movement_approval');

                if($isFound !== false){
                    $move_imgs = $isFound;
                    $filePath = storage_path('app/public/' . $move_imgs);
                    $movement_blobImage = file_exists($filePath) ? file_get_contents($filePath) : null;
                }else{
                    $move_imgs = $request->movement_approval_doc;
                    $filePath = storage_path('app/public/' . $move_imgs);
                    $movement_blobImage = file_get_contents($filePath);
                }
            }else{
                if($movement_imgs && !empty($movement_imgs)){
                    $file->delete_file($movement_imgs);
                }
            }
        }

        DB::beginTransaction();
        $LocationData = getCurrentLocation()->location_id;

        if (!$rtCamera || $rtCamera->current_location_id != $LocationData) {
            DB::rollBack();
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Unauthorized Record Can`t Update.'
            ]);
        }
        
        try
        {
            $name_for_display = '';
            if(!empty($request->rt_camera_name) && !empty($request->rt_serial_no)  && !empty($request->rt_isotope)) {
                $name_for_display = $request->rt_camera_name.' - '.$request->rt_serial_no.' - '.$request->rt_isotope;
            }
            // dd($name_for_display);
            $rt_camera_data = RTCamera::where('rt_camera_id', $request->id)->update([
                'rt_camera_name' => $request->rt_camera_name ?? null,
                'rt_serial_no' => $request->rt_serial_no ?? null,
                'rt_isotope' => $request->rt_isotope ?? null,
                'rt_x_ray' => $request->rt_x_ray ?? null,
                'rt_focal_spot' => $request->rt_focal_spot ?? null,
                'rt_document_ref_no' => $request->rt_document_ref_no ?? null,
                'rt_aerb_no' => $isLocked ? $rtCamera->rt_aerb_no : ($request->rt_aerb_no ?? null),
                'application_no' => $isLocked ? $rtCamera->application_no : ($request->application_no ?? null),
                'movement_approval'      => $isLocked ? $rtCamera->movement_approval : ($move_imgs != "" ? $move_imgs : null),
                'movement_approval_blob' => $isLocked ? $rtCamera->movement_approval_blob : ($movement_blobImage != "" ? $movement_blobImage : null),
                'validity'               => $isLocked ? $rtCamera->validity : (isset($request->validity) ? Date::createFromFormat('d/m/Y', $request->validity)->format('Y-m-d') : null),
                'rt_validity_date' => isset($request->rt_validity_date) ? Date::createFromFormat('d/m/Y', $request->rt_validity_date)->format('Y-m-d') : null,
                'table_unique_id' => $request->table_unique_id ?? null,
                'table_pk_id' => $request->table_pk_id ?? null,
                'item_id' => $request->item_id ?? null,
                'name_for_display' =>  $name_for_display,
                // 'rt_status' => $request->rt_status ?? null,
                'own_location_id' => $LocationData,
                'current_location_id' => $LocationData,
                'company_id' => Auth::user()->company_id,
                'last_on'       => Carbon::now('Asia/Kolkata'),
                'last_by'       => Auth::id(),
            ]);

            if(!$rt_camera_data)
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }
           
            $update_details =  RTCameraDetails::where('rtcd_rt_camera_id',$request->id)->update(['status' => 'D']);
            $rt_camera_details_data = json_decode($request->rt_camera_details_data, true);
            foreach($rt_camera_details_data as $ctVal)
            {
                $images = '';
                $blobImage = '';
                if($ctVal['rtcd_id'] == 0)
                {

                    if(!empty($ctVal['rtcd_decay_chart_doc']))
                        {
                        $file = new File();
                        $isFound =  $file->getFileFromTemp($ctVal['rtcd_decay_chart_doc'],$prefix = "rt_camera");
                        if($isFound !== false)
                        {
                            $images = $isFound;
                            $filePath = storage_path('app/public/' . $images);
                            if(!empty($images) && $file->Is_Files_Exists($images))
                            {
                                $blobImage = file_get_contents($filePath);
                            }
                        }
                    }
                    RTCameraDetails::create([
                        'rtcd_rt_camera_id' => $request->id,
                        'rtcd_last_of_loading_date' => isset($ctVal['rtcd_last_of_loading_date']) ? Date::createFromFormat('d/m/Y',$ctVal['rtcd_last_of_loading_date'])->format('Y-m-d') : "",
                        'rtcd_initial_activity_ci' => !empty($ctVal['rtcd_initial_activity_ci']) ? $ctVal['rtcd_initial_activity_ci'] : null,
                        'rtcd_source_size' => !empty($ctVal['rtcd_source_size']) ? $ctVal['rtcd_source_size'] : null,
                        'rtcd_pencil_no' => !empty($ctVal['rtcd_pencil_no']) ? $ctVal['rtcd_pencil_no'] : null,
                        //'rtcd_iga_no' => !empty($ctVal['rtcd_iga_no']) ? $ctVal['rtcd_iga_no'] : null,
                        'rtcd_decay_chart' =>  $images != '' ? $images : null,
                        'rtcd_decay_chart_blob' =>  $blobImage  ? $blobImage : null,
                        'status' => 'Y',
                    ]);
                }
                else
                {
                    $imgs = RTCameraDetails::where('rtcd_id', $ctVal['rtcd_id'])->value('rtcd_decay_chart');
                    $file = new File();
                    $images = '';
                    $blobImage = '';

                    if($ctVal['rtcd_decay_chart_doc'] != '' && $ctVal['rtcd_decay_chart_doc'] != null){
                        if($imgs && !empty($imgs)){
                            if($imgs != $ctVal['rtcd_decay_chart_doc']){
                                    $file->delete_file($imgs);
                            }                 
                        }      
                        
                        $isFound = $file->getFileFromTemp($ctVal['rtcd_decay_chart_doc'],'rt_camera');

                        if($isFound !== false){                    
                            $images = $isFound;
                            $filePath = storage_path('app/public/' . $images);
                            if (file_exists($filePath)) {
                                $blobImage = file_get_contents($filePath);
                            } else {
                                $blobImage = null;
                            }
                        }else{
                            $images = $ctVal['rtcd_decay_chart_doc'];
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
                    RTCameraDetails::where('rtcd_id', $ctVal['rtcd_id'])->update([
                        'rtcd_last_of_loading_date' => isset($ctVal['rtcd_last_of_loading_date']) ? Date::createFromFormat('d/m/Y',$ctVal['rtcd_last_of_loading_date'])->format('Y-m-d') : "",
                        'rtcd_initial_activity_ci' => !empty($ctVal['rtcd_initial_activity_ci']) ? $ctVal['rtcd_initial_activity_ci'] : null,
                        'rtcd_source_size' => !empty($ctVal['rtcd_source_size']) ? $ctVal['rtcd_source_size'] : null,
                        'rtcd_pencil_no' => !empty($ctVal['rtcd_pencil_no']) ? $ctVal['rtcd_pencil_no'] : null,
                        //'rtcd_iga_no' => !empty($ctVal['rtcd_iga_no']) ? $ctVal['rtcd_iga_no'] : null,
                         'rtcd_decay_chart' =>  $images != '' ? $images : null,
                        'rtcd_decay_chart_blob' =>  $blobImage  ? $blobImage : null,
                        'status' => 'Y',
                    ]);
                }
            }
            unset($blobImage);
            $olddata = RTCameraDetails::where('rtcd_rt_camera_id',$request->id)->where('status','D')->get();
            if($olddata->isNotEmpty())
            {
                foreach($olddata as $key=>$val)
                {
                    if($val->rtcd_decay_chart != '')
                    {
                        $file = new File();
                        $file->delete_file($val->rtcd_decay_chart);
                    }
                }
            }
            RTCameraDetails::where('rtcd_rt_camera_id',$request->id)->where('status','D')->delete();

            if($rt_camera_data)
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
        }
        catch(\Exception $e)
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
            $usage = DB::select('CALL rt_camera_used_list(?)', [$request->id]);
            if (!empty($usage)) {
                $message = "You Can't Delete, Camera - RT Is Used In ". $usage[0]->table_name.".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }
            $data = RTCameraDetails::where('rtcd_rt_camera_id', $request->id)->get();
            if($data->isNotEmpty())
            {
                foreach($data as $key=>$val)
                {
                    if($val->rtcd_decay_chart != '')
                    {
                        $file = new File();
                        $file->delete_file($val->rtcd_decay_chart);
                    }
                }
            }
            RTCamera::where('rt_camera_id',$request->id)->delete();
            RTCameraDetails::where('rtcd_rt_camera_id',$request->id)->delete();
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

    public function getGRNListForRTCamera(Request $request)
    {
        $itemTypes = getItemType();
        $yearIds = getCompanyYearIdsToTill();
        $LocationData = getCurrentLocation()->location_id;

        // $grn_data = GRN::select(
        //     'grn.grn_id',
        //     'grn_details.grnd_id',
        //     'grn.grn_number',
        //     'grn.grn_date',
        //     'suppliers.supplier_name',
        //     'grn.grn_challan_number',
        //     'grn.grn_challan_date',
        //     'grn_details.grnd_item_id as rt_camera_type_id',
        //     'item.item_name',
        //     'grn_details.grnd_qty',
        //     DB::raw("(grn_details.grnd_qty - (SELECT COUNT(rtc.rt_grnd_id) FROM rt_camera AS rtc WHERE rtc.rt_grnd_id = grn_details.grnd_id)) AS pend_grn_qty")
        // )
        // ->leftJoin('grn_details','grn_details.grnd_grn_id','=','grn.grn_id')
        // ->leftJoin('item','item.id','=','grn_details.grnd_item_id')
        // ->leftJoin('suppliers','suppliers.id','=','grn.grn_supplier_id')
        // ->where('item.item_type','=',"rt_camera")
        // ->whereIn('grn.year_id',$yearIds)
        // ->having('pend_grn_qty','>',0)
        // ->get();

        // $opening_data = ItemOpening::select('item_opening.io_id as table_pk_id','item_opening.io_item_id','item_opening.io_stock_qty','item_opening.io_stock_rate_unit','item_opening.current_location_id','item.item_name','item_group.item_group','item_group.identification_req','item.item_type',  DB::raw("'Opening' as table_unique_id"),'pending.pending_qty','item.unit_id','unit.unit')
        // ->leftJoin('item','item.id','=','item_opening.io_item_id')
        // ->leftJoin('unit','unit.id','=','item.unit_id')
        // ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        // ->Join('rt_camera_pending_qty as pending', function ($join) {
        //     $join->on('pending.table_pk_id', '=', 'item_opening.io_id')
        //          ->where('pending.table_unique_id', 'Opening');
        // })
        // ->where('item.item_type', 'rt_camera')
        // ->where('item_group.identification_req',"yes")
        // ->where('item_opening.current_location_id',$LocationData)
        // ->where('item_opening.io_stock_qty', '>', 0) 
        // ->where('pending.pending_qty', '>', 0)
        // ->get();
       

        // if($grn_data != null)
        // {
        //     foreach($grn_data as $cpKey => $cpVal)
        //     {
        //         if($cpVal->grn_date != null)
        //         {
        //             $cpVal->grn_date = Date::createFromFormat('Y-m-d', $cpVal->grn_date)->format('d/m/Y');
        //         }

        //         if($cpVal->grn_challan_date != null)
        //         {
        //             $cpVal->grn_challan_date = Date::createFromFormat('Y-m-d', $cpVal->grn_challan_date)->format('d/m/Y');
        //         }
        //     }
        // }

        $opening_data = DB::table('rt_camera_pending_qty')->select(['rt_camera_pending_qty.table_pk_id','rt_camera_pending_qty.table_unique_id','grn_supplier.grn_number','grn_supplier.grn_date','suppliers.supplier_name','grn_supplier.grn_challan_number','grn_supplier.grn_challan_date','item.item_name','item_group.item_group','item.item_type','unit.unit','rt_camera_pending_qty.pending_qty',   
        DB::raw("
            CASE 
                WHEN rt_camera_pending_qty.table_unique_id = 'GRN' 
                    THEN grn_supplier_details.grnd_qty
                WHEN rt_camera_pending_qty.table_unique_id = 'Opening' 
                    THEN item_opening.io_opening_qty
                ELSE 0
            END as qty
        ")
        ])
        ->leftJoin('item_opening', function ($join) {
            $join->on('item_opening.io_id', '=', 'rt_camera_pending_qty.table_pk_id')
                ->where('rt_camera_pending_qty.table_unique_id', '=', 'Opening');
        })
        ->leftJoin('grn_supplier_details', function ($join) {
            $join->on('grn_supplier_details.grnd_id', '=', 'rt_camera_pending_qty.table_pk_id')
                ->where('rt_camera_pending_qty.table_unique_id', '=', 'GRN');
        })
        ->leftJoin('grn_supplier', 'grn_supplier.grn_id', '=', 'grn_supplier_details.grnd_grn_id')
        ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
        ->leftJoin('item', 'item.id', '=', 'rt_camera_pending_qty.item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->where('rt_camera_pending_qty.location_id',$LocationData)
        ->where('rt_camera_pending_qty.pending_qty', '>', 0)
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

    // public function getGRNPartDataForRTCamera(Request $request)
    // {
    //      $itemTypes = getItemType();
    //     $LocationData = getCurrentLocation()->location_id;
    //     $yearIds = getCompanyYearIdsToTill();
    //     // $request->grnd_ids = explode(',', $request->grnd_ids);
    //     // $grn_data = GRN::select(
    //     //     'grn.grn_id',
    //     //     'grn_details.grnd_id',
    //     //     'grn.grn_number',
    //     //     'grn.grn_date',
    //     //     'suppliers.supplier_name',
    //     //     'grn.grn_challan_number',
    //     //     'grn.grn_challan_date',
    //     //     'item.id as rt_camera_type_id',
    //     // )
    //     // ->leftJoin('grn_details','grn_details.grnd_grn_id','=','grn.grn_id')
    //     // ->leftJoin('item','item.id','=','grn_details.grnd_item_id')
    //     // ->leftJoin('suppliers','suppliers.id','=','grn.grn_supplier_id')
    //     // ->where('item.item_type','=',"rt_camera")
    //     // ->whereIn('grn_details.grnd_id', $request->grnd_ids)
    //     // ->whereIn('grn.year_id',$yearIds)
    //     // ->first();

    //     // if($grn_data != null)
    //     // {
    //     //     if($grn_data->grn_date != null)
    //     //     {
    //     //         $grn_data->grn_date = Date::createFromFormat('Y-m-d', $grn_data->grn_date)->format('d/m/Y');
    //     //     }

    //     //     if($grn_data->grn_challan_date != null)
    //     //     {
    //     //         $grn_data->grn_challan_date = Date::createFromFormat('Y-m-d', $grn_data->grn_challan_date)->format('d/m/Y');
    //     //     }
    //     // }

    //     $request->table_pk_ids = explode(',', $request->table_pk_ids);
    //     $request->table_unique_ids = explode(',', $request->table_unique_ids);



    //     $opening_data = ItemOpening::select('item_opening.io_id as table_pk_id','item_opening.io_item_id','item_opening.io_stock_qty','item_opening.io_stock_rate_unit','item_opening.current_location_id','item.item_name','item_group.item_group','item_group.identification_req','item.item_type', DB::raw("'Opening' as table_unique_id"),'item.id as item_id','pending.pending_qty')
    //     ->leftJoin('item','item.id','=','item_opening.io_item_id')
    //     ->leftJoin('item_group','item_group.id','=','item.item_group_id')
    //     ->leftJoin('rt_camera_pending_qty as pending', function ($join) {
    //         $join->on('pending.table_pk_id', '=', 'item_opening.io_id')
    //              ->where('pending.table_unique_id', 'Opening');
    //     })
    //     ->where('item.item_type', 'rt_camera')
    //     ->where('item_group.identification_req',"yes")
    //     ->where('item_opening.current_location_id',$LocationData)
    //     ->whereIn('item_opening.io_id', $request->table_pk_ids)
    //     ->where('item_opening.io_stock_qty', '>', 0) 
    //     ->first();

    //     if($opening_data != null)
    //     {
    //         $opening_data->item_type = $itemTypes[$opening_data->item_type] ?? $opening_data->item_type;
    //         return response()->json([
    //             'response_code' => '1',
    //             'opening_data' => $opening_data,
    //         ]);
    //     }
    //     else
    //     {
    //         return response()->json([
    //             'response_code' => '1',
    //             'opening_data' => []
    //         ]);
    //     }
    // }


    public function getGRNPartDataForRTCamera(Request $request)
{
    $itemTypes = getItemType();
    $LocationData = getCurrentLocation()->location_id;

    $request->table_pk_ids = explode(',', $request->table_pk_ids);
    $request->table_unique_ids = explode(',', $request->table_unique_ids);

    $data = DB::table('rt_camera_pending_qty')
        ->select([
            'rt_camera_pending_qty.table_pk_id',
            'rt_camera_pending_qty.table_unique_id',
            'grn_supplier.grn_number',
            'grn_supplier.grn_date',
            'suppliers.supplier_name',
            'grn_supplier.grn_challan_number',
            'grn_supplier.grn_challan_date',
            'item.item_name',
            'item_group.item_group',
            'item.item_type',
            'item.id as item_id',
            'rt_camera_pending_qty.pending_qty',

            DB::raw("
                CASE 
                    WHEN rt_camera_pending_qty.table_unique_id = 'GRN' 
                        THEN grn_supplier_details.grnd_qty
                    WHEN rt_camera_pending_qty.table_unique_id = 'Opening' 
                        THEN item_opening.io_opening_qty
                    ELSE 0
                END as qty
            ")
        ])

        ->leftJoin('item_opening', function ($join) {
            $join->on('item_opening.io_id', '=', 'rt_camera_pending_qty.table_pk_id')
                ->where('rt_camera_pending_qty.table_unique_id', '=', 'Opening');
        })

        ->leftJoin('grn_supplier_details', function ($join) {
            $join->on('grn_supplier_details.grnd_id', '=', 'rt_camera_pending_qty.table_pk_id')
                ->where('rt_camera_pending_qty.table_unique_id', '=', 'GRN');
        })

        ->leftJoin('grn_supplier', 'grn_supplier.grn_id', '=', 'grn_supplier_details.grnd_grn_id')
        ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
        ->leftJoin('item', 'item.id', '=', 'rt_camera_pending_qty.item_id')
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')

        ->where('rt_camera_pending_qty.location_id', $LocationData)
        ->whereIn('rt_camera_pending_qty.table_pk_id', $request->table_pk_ids)
        ->whereIn('rt_camera_pending_qty.table_unique_id', $request->table_unique_ids)
        ->where('rt_camera_pending_qty.pending_qty', '>', 0)

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