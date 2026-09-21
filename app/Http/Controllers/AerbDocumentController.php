<?php

namespace App\Http\Controllers;

use App\Models\AerbDocument;
use App\Models\Admin;
use App\Models\File;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DataTables;
use Date;
use Maatwebsite\Excel\Facades\Excel;

class AerbDocumentController extends Controller
{
    public function getAerbDocuments()
    {
        $aerb_documents = AerbDocument::select('aerb_documents_id','aerb_documents_name')->orderBy('aerb_documents_name', 'ASC')->get();
        if($aerb_documents)
        {
            return response()->json([
                'aerb_documents' => $aerb_documents,
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
        return view('manage.manage-aerb_documents');
    }

    public function index(AerbDocument $AerbDocument, Request $request, DataTables $datatables)
    {
        $aerb_documents_data = AerbDocument::select([
            'aerb_documents.aerb_documents_id as id',
            'aerb_documents.aerb_documents_name',
            'aerb_documents.aerb_documents_upload',
            'aerb_documents.remark',
            'aerb_documents.created_on',
            'aerb_documents.created_by',
            'aerb_documents.last_by',
            'aerb_documents.last_on'
        ]);
        $dataTable = DataTables::of($aerb_documents_data)
        ->editColumn('aerb_documents_name', function($aerb_documents_data){
            return $aerb_documents_data->aerb_documents_name;
        })
        ->editColumn('aerb_documents_upload', function($aerb_documents_data){
            if(!empty($aerb_documents_data->aerb_documents_upload))
            {
                $documentUrl = asset('storage/' . $aerb_documents_data->aerb_documents_upload);
                $document = '<a href="' . $documentUrl . '" target="_blank">
                <i class="ri-eye-fill action-icon remove_filters_short_qty" ></i>
                </a>';
                return $document;
            }
            return '';
        })
        ->editColumn('remark', function($aerb_documents_data){
            return $aerb_documents_data->remark;
        })
        ->addColumn('options',function($aerb_documents_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("aerb_documents", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_aerb_documents"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("aerb_documents", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'aerb_documents');
        return $dataTable
        ->rawColumns(['options','aerb_documents_name','aerb_documents_upload','remark','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'aerb_documents_name' => 'required|max:100|:aerb_documents,aerb_documents_name',
        ],
        [
            'aerb_documents_name.required' => 'Please Enter AERB Document Name',
            'aerb_documents_name.max'      => 'Maximum 100 Characters Allowed',
            // 'aerb_documents_name.unique'   => 'AERB Document Name Already Exists',
        ]);

        // Move the temp-uploaded file into permanent storage and capture its binary.
        $upload_images = '';
        $upload_blobImage = '';
        if($request->aerb_documents_upload_doc)
        {
            $file = new File();
            $isFound = $file->getFileFromTemp($request->aerb_documents_upload_doc, $prefix = 'aerb_documents_upload');
            if($isFound !== false)
            {
                $upload_images = $isFound;
                $filePath = storage_path('app/public/' . $upload_images);
                if (!empty($upload_images) && $file->Is_Files_Exists($upload_images))
                {
                    $upload_blobImage = file_get_contents($filePath);
                }
            }
        }

        DB::beginTransaction();
        try
        {
            $aerb_documents_data = AerbDocument::create([
                'aerb_documents_name'        => $request->aerb_documents_name,
                'aerb_documents_upload'      => $upload_images != "" ? $upload_images : null,
                'aerb_documents_upload_blob' => $upload_blobImage != "" ? $upload_blobImage : null,
                'remark'                     => $request->remark,
                'company_id'                 => Auth::user()->company_id,
                'created_on'                 => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'                 => Auth::user()->id
            ]);

            if($aerb_documents_data->save())
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
        $aerb_documents_data = AerbDocument::where('aerb_documents_id','=',$request->id)->first();
        if(!empty($aerb_documents_data) && isset($aerb_documents_data->aerb_documents_upload_blob))
        {
            $aerb_documents_data->aerb_documents_upload_blob = base64_encode($aerb_documents_data->aerb_documents_upload_blob);
        }

        if($aerb_documents_data)
        {
            return response()->json([
                'aerb_documents_data' => $aerb_documents_data,
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
            'aerb_documents_name' => ['required','max:100'],
        ],
        [
            'aerb_documents_name.required' => 'Please Enter AERB Document Name',
            'aerb_documents_name.max'      => 'Maximum 100 Characters Allowed',
            // 'aerb_documents_name.unique'   => 'AERB Document Name Already Exists',
        ]);

        $imgs = AerbDocument::where('aerb_documents_id', $request->id)->value('aerb_documents_upload');

        $file = new File();
        $upload_images = '';
        $upload_blobImage = '';

        if($request->aerb_documents_upload_doc != '' && $request->aerb_documents_upload_doc != null){
            if($imgs && !empty($imgs)){
                if($imgs != $request->aerb_documents_upload_doc){
                    $file->delete_file($imgs);
                }
            }

            $isFound = $file->getFileFromTemp($request->aerb_documents_upload_doc, 'aerb_documents_upload');

            if($isFound !== false){
                $upload_images = $isFound;
                $filePath = storage_path('app/public/' . $upload_images);
                $upload_blobImage = file_exists($filePath) ? file_get_contents($filePath) : null;
            }else{
                $upload_images = $request->aerb_documents_upload_doc;
                $filePath = storage_path('app/public/' . $upload_images);
                $upload_blobImage = file_get_contents($filePath);
            }
        }else{
            if($imgs && !empty($imgs)){
                $file->delete_file($imgs);
            }
        }

        DB::beginTransaction();

        try
        {
            $aerb_documents_data = AerbDocument::where('aerb_documents_id','=',$request->id)->update([
                'aerb_documents_name'        => $request->aerb_documents_name,
                'aerb_documents_upload'      => $upload_images != "" ? $upload_images : null,
                'aerb_documents_upload_blob' => $upload_blobImage != "" ? $upload_blobImage : null,
                'remark'                     => $request->remark,
                'last_on'                    => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by'                    => Auth::user()->id
            ]);

            if($aerb_documents_data)
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
            AerbDocument::destroy($request->id);
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

    public function verify(Request $request)
    {
        $query = AerbDocument::where('aerb_documents_name', $request->aerb_documents_name);

        if($request->filled('id'))
        {
            $query->where('aerb_documents_id', '!=', $request->id);
        }

        if($query->exists())
        {
            return response()->json([
                'response_code' => 1,
                'response_message' => 'AERB Document Name Already Exists',
            ]);
        }

        return response()->json([
            'response_code' => 0,
        ]);
    }

    public function existsAerbDocuments(Request $request)
    {
        if($request->term != "")
        {
            $fdAerbDocuments = AerbDocument::select('aerb_documents_name')->where('aerb_documents_name', 'LIKE', '%'.$request->term.'%')->groupBy('aerb_documents_name')->get();
            if($fdAerbDocuments != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdAerbDocuments as $dsKey)
                {
                    $output .= '<li parent-id="aerb_documents_name" list-id="aerb_documents_name_list" class="list-group-item" tabindex="0">'.$dsKey->aerb_documents_name.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'aerbDocumentsNameList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No AERB Document Name available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'aerbDocumentsNameList' => '',
                'response_code' => 1,
            ]);
        }
    }
}
