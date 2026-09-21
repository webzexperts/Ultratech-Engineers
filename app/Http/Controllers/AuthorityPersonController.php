<?php

namespace App\Http\Controllers;

use App\Models\AuthorityPerson;
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

class AuthorityPersonController extends Controller
{
    /**
     * Build the comma-separated operator_type label string from the
     * individual Yes/No flags, preserving the defined order.
     */
    private function buildOperatorType($tested, $reviewed, $authorized, $checked, $approved)
    {
        $labels = [];
        if ($tested == 'Yes')     { $labels[] = 'Tested By'; }
        if ($reviewed == 'Yes')   { $labels[] = 'Reviewed By'; }
        if ($authorized == 'Yes') { $labels[] = 'Authorized By'; }
        if ($checked == 'Yes')    { $labels[] = 'Checked By'; }
        if ($approved == 'Yes')   { $labels[] = 'Approved By'; }
        return implode(', ', $labels);
    }

    public function getAuthorityPersons()
    {
        $authority_persons = AuthorityPerson::orderBy('operator', 'ASC')->get();
        if($authority_persons)
        {
            return response()->json([
                'authority_persons' => $authority_persons,
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
        return view('manage.manage-authority_person');
    }

    public function index(AuthorityPerson $AuthorityPerson, Request $request, DataTables $datatables)
    {
        $authority_person_data = AuthorityPerson::select([
            'authority_person.authority_person_id as id',
            'authority_person.operator',
            'authority_person.designation',
            'authority_person.signature',
            'authority_person.operator_type',
            'authority_person.authority_person_type_value_fix',
            'location.location_name',
            'authority_person.validity',
            'authority_person.pms_no',
            'authority_person.certificate',
            'authority_person.status',
            'authority_person.created_on',
            'authority_person.created_by',
            'authority_person.last_by',
            'authority_person.last_on'
        ])
        ->leftJoin('location','location.location_id','=','authority_person.current_location_id');
        $dataTable = DataTables::of($authority_person_data)
        ->editColumn('operator', function($authority_person_data){
            return $authority_person_data->operator;
        })
        ->editColumn('designation', function($authority_person_data){
            return $authority_person_data->designation;
        })
        ->editColumn('signature', function($authority_person_data){
            if(!empty($authority_person_data->signature))
            {
                $documentUrl = asset('storage/' . $authority_person_data->signature);
                $document = '<a href="' . $documentUrl . '" target="_blank">
                <i class="ri-eye-fill action-icon remove_filters_short_qty" ></i>
                </a>';
                return $document;
            }
            return '';
        })
        ->editColumn('certificate', function($authority_person_data){
            if(!empty($authority_person_data->certificate))
            {
                $documentUrl1 = asset('storage/' . $authority_person_data->certificate);
                $document1 = '<a href="' . $documentUrl1 . '" target="_blank">
                <i class="ri-eye-fill action-icon remove_filters_short_qty" ></i>
                </a>';
                return $document1;
            }
            return '';
        })
         ->editColumn('validity', function($authority_person_data){
            if ($authority_person_data->validity != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $authority_person_data->validity)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('authority_person.validity', function ($q, $k) {
            applyDate($q, $k, 'authority_person.validity');
        })
        ->editColumn('operator_type', function($authority_person_data){
            return $authority_person_data->operator_type ?? '-';
        })
        ->editColumn('status', function($authority_person_data){
            return $authority_person_data->status == 'Active' ? 'Active':'Deactive';
        })
        ->filterColumn('authority_person.status', function ($query, $keyword) {
            $globalSearch = request()->input('search.value');
            $searchValue = $globalSearch != '' ? $globalSearch : $keyword;
            $lowerKeyword = strtolower(trim($searchValue));
            if ($lowerKeyword === 'active') {
                $searchStatus = 'active';
            }
            elseif ($lowerKeyword === 'deactive') {
                $searchStatus = 'deactive';
            }
            if (!empty($searchStatus)) {
                $query->where('authority_person.status', '=', $searchStatus);
            }
            else {
                $dbFormatKeyword = str_replace(' ', '_', $lowerKeyword);
                $query->where('authority_person.status', 'like', "$dbFormatKeyword%");
            }
        })
        ->addColumn('options',function($authority_person_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';
 
                if (hasAccess("authority_person", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_authority_person"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }
 
                if (hasAccess("authority_person", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'authority_person');
        return $dataTable
        ->rawColumns(['options','operator','designation','signature','operator_type','validity','certificate','status','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'operator'    => 'required|max:100|unique:authority_person,operator',
            'designation' => 'nullable|max:100',
            'signature_doc' => 'nullable',
            'status'      => 'nullable|in:Active,Deactive',
        ],
        [
            'operator.required'    => 'Enter Operator',
            'operator.max'         => 'Maximum 100 Characters Allowed',
            'operator.unique'      => 'Operator Already Exists',
            'designation.max'      => 'Maximum 100 Characters Allowed',
        ]);

        DB::beginTransaction();
        try
        {
            $tested_by     = $request->tested_by == 'Yes' ? 'Yes' : 'No';
            $reviewed_by   = $request->reviewed_by == 'Yes' ? 'Yes' : 'No';
            $authorized_by = $request->authorized_by == 'Yes' ? 'Yes' : 'No';
            $checked_by    = $request->checked_by == 'Yes' ? 'Yes' : 'No';
            $approved_by   = $request->approved_by == 'Yes' ? 'Yes' : 'No';

            if ($tested_by != 'Yes' && $reviewed_by != 'Yes' && $authorized_by != 'Yes' && $checked_by != 'Yes' && $approved_by != 'Yes') {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Select At Least One Operator Type.',
                ]);
            }

            $operator_type = $this->buildOperatorType($tested_by, $reviewed_by, $authorized_by, $checked_by, $approved_by);

            $signaturePath = null;
            $blobImage = null;
            if ($request->signature_doc) {
                $file = new File();
                $isFound = $file->getFileFromTemp($request->signature_doc, 'authority_person');
                if ($isFound !== false) {
                    $signaturePath = $isFound;
                    $filePath = storage_path('app/public/' . $signaturePath);
                    if (file_exists($filePath)) {
                        $blobImage = file_get_contents($filePath);
                    }
                }
            }

            $certificatePath = null;
            $blobImageCertificate = null;
            if ($request->certificate_doc) {
                $file = new File();
                $isFound = $file->getFileFromTemp($request->certificate_doc, 'authority_person');
                if ($isFound !== false) {
                    $certificatePath = $isFound;
                    $filePath = storage_path('app/public/' . $certificatePath);
                    if (file_exists($filePath)) {
                        $blobImageCertificate = file_get_contents($filePath);
                    }
                }
            }

            $authority_person_data = AuthorityPerson::create([
                'operator'       => $request->operator,
                'designation'    => $request->designation,
                'signature'      => $signaturePath,
                'signature_blob' => $blobImage,
                'status'         => $request->status ?? 'Active',
                'tested_by'      => $tested_by,
                'reviewed_by'    => $reviewed_by,
                'authorized_by'  => $authorized_by,
                'checked_by'     => $checked_by,
                'approved_by'    => $approved_by,
                'operator_type'  => $operator_type,
                'certificate'      => $certificatePath,
                'certificate_blob' => $blobImageCertificate,
                'validity'         => isset($request->validity) ? Date::createFromFormat('d/m/Y', $request->validity)->format('Y-m-d') : null,
                'pms_no' => $request->pms_no ?? null,
                'authority_person_type_value_fix' => $request->authority_person_type_value_fix ?? null,
                'current_location_id' => $request->current_location_id ?? null,
                'company_id'     => Auth::user()->company_id,
                'created_on'     => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'     => Auth::user()->id
            ]);

            if($authority_person_data->save())
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
        $authority_person_data = AuthorityPerson::where('authority_person_id','=',$request->id)->first();
        if($authority_person_data)
        {
            if (isset($authority_person_data->signature_blob)) {
                $authority_person_data->signature_blob = base64_encode($authority_person_data->signature_blob);
            }
            if (isset($authority_person_data->certificate_blob)) {
                $authority_person_data->certificate_blob = base64_encode($authority_person_data->certificate_blob);
            }
            if(!empty($authority_person_data->validity)){
                $authority_person_data->validity = $authority_person_data->validity != "" ? Date::createFromFormat('Y-m-d',  $authority_person_data->validity)->format('d/m/Y') : "";
            }
            return response()->json([
                'authority_person_data' => $authority_person_data,
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
            'operator'    => ['required','max:100',Rule::unique('authority_person', 'operator')->ignore($request->id, 'authority_person_id')],
            'designation' => 'nullable|max:100',
            'status'      => 'nullable|in:Active,Deactive',
        ],
        [
            'operator.required'    => 'Enter Operator',
            'operator.max'         => 'Maximum 100 Characters Allowed',
            'operator.unique'      => 'Operator Already Exists',
            'designation.max'      => 'Maximum 100 Characters Allowed',
        ]);

        DB::beginTransaction();

        try
        {
            $tested_by     = $request->tested_by == 'Yes' ? 'Yes' : 'No';
            $reviewed_by   = $request->reviewed_by == 'Yes' ? 'Yes' : 'No';
            $authorized_by = $request->authorized_by == 'Yes' ? 'Yes' : 'No';
            $checked_by    = $request->checked_by == 'Yes' ? 'Yes' : 'No';
            $approved_by   = $request->approved_by == 'Yes' ? 'Yes' : 'No';

            if ($tested_by != 'Yes' && $reviewed_by != 'Yes' && $authorized_by != 'Yes' && $checked_by != 'Yes' && $approved_by != 'Yes') {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Please Select At Least One Operator Type.',
                ]);
            }

            $operator_type = $this->buildOperatorType($tested_by, $reviewed_by, $authorized_by, $checked_by, $approved_by);

            $imgs = AuthorityPerson::where('authority_person_id', $request->id)->value('signature');
            $file = new File();
            $signaturePath = '';
            $blobImage = null;

            if($request->signature_doc != '' && $request->signature_doc != null){
                if($imgs && !empty($imgs)){
                    if($imgs != $request->signature_doc){
                        $file->delete_file($imgs);
                    }                 
                }      
                
                $isFound = $file->getFileFromTemp($request->signature_doc,'authority_person');

                if($isFound !== false){                    
                    $signaturePath = $isFound;
                    $filePath = storage_path('app/public/' . $signaturePath);
                    if (file_exists($filePath)) {
                        $blobImage = file_get_contents($filePath);
                    }
                }else{
                    $signaturePath = $request->signature_doc;
                    $filePath = storage_path('app/public/' . $signaturePath);
                    if (file_exists($filePath)) {
                        $blobImage = file_get_contents($filePath);
                    }
                }
            }else{
                if($imgs && !empty($imgs)){
                    $file->delete_file($imgs);
                }  
            }        


            $cer_imgs = AuthorityPerson::where('authority_person_id', $request->id)->value('certificate');
            $file = new File();
            $certificatePath = '';
            $certificateblobImage = null;

            if($request->certificate_doc != '' && $request->certificate_doc != null){
                if($cer_imgs && !empty($cer_imgs)){
                    if($cer_imgs != $request->certificate_doc){
                        $file->delete_file($cer_imgs);
                    }                 
                }      
                
                $isFound = $file->getFileFromTemp($request->certificate_doc,'certificate');

                if($isFound !== false){                    
                    $certificatePath = $isFound;
                    $filePath = storage_path('app/public/' . $certificatePath);
                    if (file_exists($filePath)) {
                        $certificateblobImage = file_get_contents($filePath);
                    }
                }else{
                    $certificatePath = $request->certificate_doc;
                    $filePath = storage_path('app/public/' . $certificatePath);
                    if (file_exists($filePath)) {
                        $certificateblobImage = file_get_contents($filePath);
                    }
                }
            }else{
                if($cer_imgs && !empty($cer_imgs)){
                    $file->delete_file($cer_imgs);
                }  
            }  

            $updateData = [
                'operator'       => $request->operator,
                'designation'    => $request->designation,
                'status'         => $request->status ?? 'Active',
                'tested_by'      => $tested_by,
                'reviewed_by'    => $reviewed_by,
                'authorized_by'  => $authorized_by,
                'checked_by'     => $checked_by,
                'approved_by'    => $approved_by,
                'operator_type'  => $operator_type,
                'signature'      => $signaturePath != "" ? $signaturePath : null,
                'signature_blob' => $blobImage,
                'certificate'      => $certificatePath != "" ? $certificatePath : null,
                'certificate_blob' => $certificateblobImage,
                'validity'         => isset($request->validity) ? Date::createFromFormat('d/m/Y', $request->validity)->format('Y-m-d') : null,
                'pms_no' => $request->pms_no ?? null,
                'authority_person_type_value_fix' => $request->authority_person_type_value_fix ?? null,
                'current_location_id' => $request->current_location_id ?? null,
                'last_on'        => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by'        => Auth::user()->id
            ];

            $authority_person_data = AuthorityPerson::where('authority_person_id','=',$request->id)->update($updateData);

            if($authority_person_data)
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
        DB::beginTransaction();
        try
        {
            $usage = DB::select('CALL authority_person_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                DB::rollBack();
                $message = "You Can't Delete, Operator Is Used In " . $usage[0]->table_name . ".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            $existing = AuthorityPerson::where('authority_person_id', $request->id)->first();
            if ($existing && $existing->signature) {
                $file = new File();
                $file->delete_file($existing->signature);
            }
            if ($existing && $existing->certificate) {
                $file = new File();
                $file->delete_file($existing->certificate);
            }
            AuthorityPerson::destroy($request->id);
            DB::commit();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        }
        catch(\Exception $e)
        {
            DB::rollBack();
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

    public function verify(Request $request)
    {
        $query = AuthorityPerson::where('operator', $request->operator);

        if($request->filled('id'))
        {
            $query->where('authority_person_id', '!=', $request->id);
        }

        if($query->exists())
        {
            return response()->json([
                'response_code' => 1,
                'response_message' => 'Operator Already Exists',
            ]);
        }

        return response()->json([
            'response_code' => 0,
        ]);
    }

    public function existsAuthorityPerson(Request $request)
    {
        if($request->term != "")
        {
            $fdAuthorityPerson = AuthorityPerson::select('operator')->where('operator', 'LIKE', '%'.$request->term.'%')->groupBy('operator')->get();
            if($fdAuthorityPerson != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdAuthorityPerson as $dsKey)
                {
                    $output .= '<li parent-id="operator" list-id="operator_list" class="list-group-item" tabindex="0">'.$dsKey->operator.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'authorityPersonList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Operator available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'authorityPersonList' => '',
                'response_code' => 1,
            ]);
        }
    }
}
