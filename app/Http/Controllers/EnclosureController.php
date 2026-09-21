<?php

namespace App\Http\Controllers;

use App\Models\Enclosure;
use App\Models\Admin;
use App\Models\File;
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

class EnclosureController extends Controller
{
    public function getEnclosures()
    {
        $enclosures = Enclosure::select('enclosure_id','enclosure_name','enclosure_no')->orderBy('enclosure_name', 'ASC')->get();
        if($enclosures)
        {
            return response()->json([
                'enclosures' => $enclosures,
                'response_code' => '1'
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'No Data Avilable',
            ]);
        }
    }

    public function manage()
    {
        return view('manage.manage-enclosure');
    }

    public function index(Enclosure $Enclosure, Request $request, DataTables $datatables)
    {
        $LocationData = getCurrentLocation();
        $enclosure_data = Enclosure::select([
            'enclosure.enclosure_id as id',
            'enclosure.enclosure_type_value_fix',
            'enclosure.enclosure_name',
            'enclosure.enclosure_no',
            'enclosure.enclosure_layout',
            'enclosure.enclosure_permission_to_use',
            'enclosure.enclosure_validity',
            'enclosure.created_on',
            'enclosure.created_by',
            'enclosure.last_by',
            'enclosure.last_on'
        ]);
        if ($LocationData->location_type != 'HO') {
            $enclosure_data->where(function($query) use ($LocationData) {
                $query->where('enclosure.current_location_id', $LocationData->location_id);
            });
        }
        $dataTable = DataTables::of($enclosure_data)
        ->editColumn('enclosure_name', function($enclosure_data){
            return $enclosure_data->enclosure_name;
        })
        ->editColumn('enclosure_no', function($enclosure_data){
            return $enclosure_data->enclosure_no;
        })
        ->editColumn('enclosure_layout', function($enclosure_data){
            if(!empty($enclosure_data->enclosure_layout))
            {
                $documentUrl = asset('storage/' . $enclosure_data->enclosure_layout);
                $document = '<a href="' . $documentUrl . '" target="_blank">
                <i class="ri-eye-fill action-icon remove_filters_short_qty" ></i>
                </a>';
                return $document;
            }
            return '';
        })
        ->editColumn('enclosure_permission_to_use', function($enclosure_data){
            if(!empty($enclosure_data->enclosure_permission_to_use))
            {
                $documentUrl1 = asset('storage/' . $enclosure_data->enclosure_permission_to_use);
                $document1 = '<a href="' . $documentUrl1 . '" target="_blank">
                <i class="ri-eye-fill action-icon remove_filters_short_qty" ></i>
                </a>';
                return $document1;
            }
            return '';
        })
        ->editColumn('enclosure_validity', function($enclosure_data){
            if ($enclosure_data->enclosure_validity != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $enclosure_data->enclosure_validity)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('enclosure.enclosure_validity', function ($q, $k) {
            applyDate($q, $k, 'enclosure.enclosure_validity');
        })
        ->addColumn('options',function($enclosure_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("enclosure", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_enclosure"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("enclosure", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'enclosure');
        return $dataTable
        ->rawColumns(['options','enclosure_name','enclosure_no','enclosure_layout','last_by','last_on','created_by','created_on','enclosure_permission_to_use','enclosure_validity'])
        ->make(true);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'enclosure_name' => 'required|max:100|unique:enclosure,enclosure_name',
            'enclosure_no'   => 'required|max:100|unique:enclosure,enclosure_no',
           // 'enclosure_layout_doc' => 'required',
        ],
        [
            'enclosure_name.required' => 'Please Enter Name',
            'enclosure_name.max'      => 'Maximum 100 Characters Allowed',
            'enclosure_name.unique'   => 'Enclosure Name Already Exists',
            'enclosure_no.required'   => 'Please Enter No.',
            'enclosure_no.max'        => 'Maximum 100 Characters Allowed',
            'enclosure_no.unique'     => 'No. Already Exists',
           // 'enclosure_layout_doc.required' => 'Please Upload Enclosure Layout',
        ]);

        // Move the temp-uploaded layout file into permanent storage and capture its binary.
        $layout_images = '';
        $layout_blobImage = '';
        if($request->enclosure_layout_doc)
        {
            $file = new File();
            $isFound = $file->getFileFromTemp($request->enclosure_layout_doc, $prefix = 'enclosure_layout');
            if($isFound !== false)
            {
                $layout_images = $isFound;
                $filePath = storage_path('app/public/' . $layout_images);
                if (!empty($layout_images) && $file->Is_Files_Exists($layout_images))
                {
                    $layout_blobImage = file_get_contents($filePath);
                }
            }
        }

        // Move the temp-uploaded layout file into permanent storage and capture its binary.
        $permission_images = '';
        $permission_blobImage = '';
        if($request->enclosure_permission_to_use_doc)
        {
            $file = new File();
            $isFound = $file->getFileFromTemp($request->enclosure_permission_to_use_doc, $prefix = 'permission_to_use');
            if($isFound !== false)
            {
                $permission_images = $isFound;
                $filePath = storage_path('app/public/' . $permission_images);
                if (!empty($permission_images) && $file->Is_Files_Exists($permission_images))
                {
                    $permission_blobImage = file_get_contents($filePath);
                }
            }
        }

        $LocationData = getCurrentLocation();

        DB::beginTransaction();
        try
        {
            $enclosure_data = Enclosure::create([
                'enclosure_name'        => $request->enclosure_name,
                'enclosure_type_value_fix'   => $request->enclosure_type_value_fix ?? null,
                'current_location_id'   => $LocationData->location_id ?? null,
                'enclosure_no'          => $request->enclosure_no,
                'enclosure_layout'      => $layout_images != "" ? $layout_images : null,
                'enclosure_layout_blob' => $layout_blobImage != "" ? $layout_blobImage : null,
                'enclosure_permission_to_use'      => $permission_images != "" ? $permission_images : null,
                'enclosure_permission_to_use_blob' => $permission_blobImage != "" ? $permission_blobImage : null,
                'enclosure_validity'               => isset($request->enclosure_validity) ? Date::createFromFormat('d/m/Y', $request->enclosure_validity)->format('Y-m-d') : null,
                'company_id'            => Auth::user()->company_id,
                'created_on'            => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'            => Auth::user()->id
            ]);

            if($enclosure_data->save())
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
        $enclosure_data = Enclosure::where('enclosure_id','=',$request->id)->first();
        if(!empty($enclosure_data) && isset($enclosure_data->enclosure_layout_blob))
        {
            $enclosure_data->enclosure_layout_blob = base64_encode($enclosure_data->enclosure_layout_blob);
        }
        if(!empty($enclosure_data) && isset($enclosure_data->enclosure_permission_to_use_blob))
        {
            $enclosure_data->enclosure_permission_to_use_blob = base64_encode($enclosure_data->enclosure_permission_to_use_blob);
        }
        if(!empty($enclosure_data->enclosure_validity)){

            $enclosure_data->enclosure_validity = $enclosure_data->enclosure_validity != "" ? Date::createFromFormat('Y-m-d',  $enclosure_data->enclosure_validity)->format('d/m/Y') : "";
        }

        if($enclosure_data)
        {
            return response()->json([
                'enclosure_data' => $enclosure_data,
                'response_code' => '1',
                'response_message' => '',
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'enclosure_name' => ['required','max:100',Rule::unique('enclosure', 'enclosure_name')->ignore($request->id, 'enclosure_id')],
            'enclosure_no'   => ['required','max:100',Rule::unique('enclosure', 'enclosure_no')->ignore($request->id, 'enclosure_id')],
            //'enclosure_layout_doc' => 'required',
        ],
        [
            'enclosure_name.required' => 'Please Enter Name',
            'enclosure_name.max'      => 'Maximum 100 Characters Allowed',
            'enclosure_name.unique'   => 'Name Already Exists',
            'enclosure_no.required'   => 'Please Enter No.',
            'enclosure_no.max'        => 'Maximum 100 Characters Allowed',
            'enclosure_no.unique'     => 'No. Already Exists',
            //'enclosure_layout_doc.required' => 'Please Upload Enclosure Layout',
        ]);

        $imgs = Enclosure::where('enclosure_id', $request->id)->value('enclosure_layout');

        $file = new File();
        $layout_images = '';
        $layout_blobImage = '';

        if($request->enclosure_layout_doc != '' && $request->enclosure_layout_doc != null){
            if($imgs && !empty($imgs)){
                if($imgs != $request->enclosure_layout_doc){
                    $file->delete_file($imgs);
                }
            }

            $isFound = $file->getFileFromTemp($request->enclosure_layout_doc, 'enclosure_layout');

            if($isFound !== false){
                $layout_images = $isFound;
                $filePath = storage_path('app/public/' . $layout_images);
                $layout_blobImage = file_exists($filePath) ? file_get_contents($filePath) : null;
            }else{
                $layout_images = $request->enclosure_layout_doc;
                $filePath = storage_path('app/public/' . $layout_images);
                $layout_blobImage = file_get_contents($filePath);
            }
        }else{
            if($imgs && !empty($imgs)){
                $file->delete_file($imgs);
            }
        }


        $per_imgs = Enclosure::where('enclosure_id', $request->id)->value('enclosure_permission_to_use');

        $file = new File();
        $per_images = '';
        $per_blobImage = '';

        if($request->enclosure_permission_to_use_doc != '' && $request->enclosure_permission_to_use_doc != null){
            if($per_imgs && !empty($per_imgs)){
                if($per_imgs != $request->enclosure_permission_to_use_doc){
                    $file->delete_file($per_imgs);
                }
            }

            $isFound = $file->getFileFromTemp($request->enclosure_permission_to_use_doc, 'permission_to_use');

            if($isFound !== false){
                $per_images = $isFound;
                $filePath = storage_path('app/public/' . $per_images);
                $per_blobImage = file_exists($filePath) ? file_get_contents($filePath) : null;
            }else{
                $per_images = $request->enclosure_permission_to_use_doc;
                $filePath = storage_path('app/public/' . $per_images);
                $per_blobImage = file_get_contents($filePath);
            }
        }else{
            if($per_imgs && !empty($per_imgs)){
                $file->delete_file($per_imgs);
            }
        }

        DB::beginTransaction();
        $LocationData = getCurrentLocation();

        try
        {
            $enclosure_data = Enclosure::where('enclosure_id','=',$request->id)->update([
                'enclosure_name'        => $request->enclosure_name,
                'enclosure_type_value_fix'   => $request->enclosure_type_value_fix ?? null,
                'current_location_id'   => $LocationData->location_id ?? null,
                'enclosure_no'          => $request->enclosure_no,
                'enclosure_layout'      => $layout_images != "" ? $layout_images : null,
                'enclosure_layout_blob' => $layout_blobImage != "" ? $layout_blobImage : null,
                'enclosure_permission_to_use'      => $per_images != "" ? $per_images : null,
                'enclosure_permission_to_use_blob' => $per_blobImage != "" ? $per_blobImage : null,
                'enclosure_validity'               => isset($request->enclosure_validity) ? Date::createFromFormat('d/m/Y', $request->enclosure_validity)->format('Y-m-d') : null,
                'last_on'               => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by'               => Auth::user()->id
            ]);

            if($enclosure_data)
            {
                DB::commit();
                return response()->json([
                    'response_code' => '1',
                    'response_message' => getResponseMessage('update_success'),
                ]);
            }
            else
            {
                DB::commit();
                return response()->json([
                    'response_code' => '1',
                    'response_message' => getResponseMessage('update_success'),
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
            $usage = DB::select('CALL enclosure_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                $message = "You Can't Delete, Enclosure Is Used In ". $usage[0]->table_name.".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }
            $existing = Enclosure::where('enclosure_id', $request->id)->first();
            if ($existing && $existing->enclosure_layout) {
                $file = new File();
                $file->delete_file($existing->enclosure_layout);
            }
             if ($existing && $existing->enclosure_permission_to_use) {
                $file = new File();
                $file->delete_file($existing->enclosure_permission_to_use);
            }
            Enclosure::destroy($request->id);
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        }
        catch(\Exception $e)
        {
            report($e);
            DB::rollBack(); 
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

    /**
     * Individual non-duplicate check for Enclosure Name and/or Enclosure No.
     * Pass whichever field(s) you want checked; returns the first duplicate found.
     */
    public function verify(Request $request)
    {
        if($request->filled('enclosure_name'))
        {
            $query = Enclosure::where('enclosure_name', $request->enclosure_name);
            if($request->filled('id'))
            {
                $query->where('enclosure_id', '!=', $request->id);
            }
            if($query->exists())
            {
                return response()->json([
                    'response_code' => 1,
                    'response_message' => 'Enclosure Name Already Exists',
                ]);
            }
        }

        if($request->filled('enclosure_no'))
        {
            $query = Enclosure::where('enclosure_no', $request->enclosure_no);
            if($request->filled('id'))
            {
                $query->where('enclosure_id', '!=', $request->id);
            }
            if($query->exists())
            {
                return response()->json([
                    'response_code' => 1,
                    'response_message' => 'Enclosure No. Already Exists',
                ]);
            }
        }

        return response()->json([
            'response_code' => 0,
        ]);
    }

    public function existsEnclosureName(Request $request)
    {
        if($request->term != "")
        {
            $fdEnclosure = Enclosure::select('enclosure_name')->where('enclosure_name', 'LIKE', '%'.$request->term.'%')->groupBy('enclosure_name')->get();
            if($fdEnclosure != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdEnclosure as $dsKey)
                {
                    $output .= '<li parent-id="enclosure_name" list-id="enclosure_name_list" class="list-group-item" tabindex="0">'.$dsKey->enclosure_name.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'enclosureNameList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Enclosure Name available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'enclosureNameList' => '',
                'response_code' => 1,
            ]);
        }
    }

    public function existsEnclosureNo(Request $request)
    {
        if($request->term != "")
        {
            $fdEnclosure = Enclosure::select('enclosure_no')->where('enclosure_no', 'LIKE', '%'.$request->term.'%')->groupBy('enclosure_no')->get();
            if($fdEnclosure != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdEnclosure as $dsKey)
                {
                    $output .= '<li parent-id="enclosure_no" list-id="enclosure_no_list" class="list-group-item" tabindex="0">'.$dsKey->enclosure_no.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'enclosureNoList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Enclosure No. available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'enclosureNoList' => '',
                'response_code' => 1,
            ]);
        }
    }
}
