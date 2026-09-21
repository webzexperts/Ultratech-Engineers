<?php

namespace App\Http\Controllers;

use App\Models\Company;
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

class CompanyController extends Controller
{

    /**
     * Return all company data without filter
     */
    public function companyData()
    {
        $companies = company::all();
        if($companies){
            return response()->json([
                'companies' => $companies,
                'response_code' => '1'
            ]);
        }else{
            return response()->json([
                'response_code' => '0',
                'response_message' => 'No Data Avilable',
            ]);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function manage()
    {
        return view('manage.manage-company');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Company $Company,Request $request,DataTables $dataTables)
    {
        $company_data = Company::select(['company_name','id','company_code','city','state','country','phone_no','email','gstin','created_on','created_by','last_by','last_on']);
    
        return DataTables::of($company_data)
        ->editColumn('created_by', function($company_data){ 
            if($company_data->created_by != null){
                $created_by = Admin::where('id','=',$company_data->created_by)->first('user_name'); 
                return isset($created_by->user_name) ? $created_by->user_name : '';
            }else{
                return '';
            }
        })
        ->editColumn('last_by', function($company_data){ 
            if($company_data->last_by != null){
                $last_by = Admin::where('id','=',$company_data->last_by)->first('user_name'); 
                return isset($last_by->user_name) ? $last_by->user_name : '';
            }else{
                return '';
            }
        
        })
        ->editColumn('company_name', function($company_data){ 
            return Str::limit($company_data->company_name, 50);
        })
        ->editColumn('city', function($company_data){ 
            return Str::limit($company_data->city, 50);
        })
        ->editColumn('state', function($company_data){ 
            return Str::limit($company_data->state, 50);
        })
        ->editColumn('country', function($company_data){ 
            return Str::limit($company_data->country, 50);
        })
        ->editColumn('created_on', function($company_data){ 
            if ($company_data->created_on != null) { 
                $formatedDate1 = Date::createFromFormat('Y-m-d H:i:s', $company_data->created_on)->format(DATE_TIME_FORMAT); return $formatedDate1; 
            }else{
                return '';
            }
        })
        ->editColumn('last_on', function($company_data){ 
            if ($company_data->last_on != null) {
                $formatedDate2 = Date::createFromFormat('Y-m-d H:i:s', $company_data->last_on)->format(DATE_TIME_FORMAT); return $formatedDate2; 
            }else{
                return '';
            }
        })
        ->addColumn('options',function($company_data){ 
            $action = "<div>";
            if(hasAccess("company","edit")){
            $action .="<a id='edit_a' href='".route('edit-company',['id' => base64_encode($company_data->id)]) ."' data-placement='top' data-original-title='Edit' title='Edit'><i class='iconfa-pencil action-icon'></i></a>";
            }
            if(hasAccess("company","delete")){
            $action .= "<i id='del_a' data-placement='top' data-original-title='Delete' title='Delete' class='iconfa-trash action-icon'></i>";
            }
            $action .= "</div>";
            return $action;
        })
        ->rawColumns(['created_by','created_on','last_by','last_on','company_name'.'city','state','country'])
        ->make(true);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('add.add-company');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name'=>'required|max:255|unique:companies',
            'company_code'=>'required|max:255'
        ],
        [
            'company_name.required' => 'Please enter company name',
            'company_name.max' => 'Maximum 255 characters allowed',
            'company_code.required' => 'Please enter company code',
            'company_code.max' => 'Maximum 255 characters allowed'
        ]);

        $companyLogo = "";
        $otherLogo1 = "";
        $otherLogo2 = "";
        $otherLogo3 = "";

        if(isset($request->company_logo_doc) && $request->company_logo_doc != ""){
            $file = new File();
            $isFound =  $file->getFileFromTemp($request->company_logo_doc,$prefix = "company");

            if($isFound != false){
                $companyLogo = $isFound;
            }
        }

        if(isset($request->other_logo_1_doc) && $request->other_logo_1_doc != ""){
            $file = new File();
            $isFound =  $file->getFileFromTemp($request->other_logo_1_doc,$prefix = "other_logo_1");

            if($isFound != false){
                $otherLogo1 = $isFound;
            }
        }

        if(isset($request->other_logo_2_doc) && $request->other_logo_2_doc != ""){
            $file = new File();
            $isFound =  $file->getFileFromTemp($request->other_logo_2_doc,$prefix = "other_logo_2");

            if($isFound != false){
                $otherLogo2 = $isFound;
            }
        }

        if(isset($request->other_logo_3_doc) && $request->other_logo_3_doc != ""){
            $file = new File();
            $isFound =  $file->getFileFromTemp($request->other_logo_3_doc,$prefix = "other_logo_3");

            if($isFound != false){
                $otherLogo3 = $isFound;
            }
        }
        
        $company_data=  Company::create([
            'company_name' => $request->company_name,
            'company_code' => $request->company_code,
            'address' => $request->address,
            'city' => $request->city,
            'pin_code' => $request->pin_code,
            'state' => $request->state,
            'state_code' => $request->state_code,
            'country' => $request->country,
            'phone_no' => $request->phone_no,
            'email' => $request->email,
            'web_address' => $request->web_address,
            'gstin' => $request->gstin,
            'pan' => $request->pan,
            'reverse_charge' => $request->reverse_charge,
            'bank_name' => $request->bank_name,
            'branch_name' => $request->branch_name,
            'account_no' => $request->account_no,
            'account_type' => $request->account_type,
            'ifsc_code' => $request->ifsc_code,
            'company_logo' => $companyLogo,
            'other_logo_1' => $otherLogo1,
            'other_logo_2' => $otherLogo2,
            'other_logo_3' => $otherLogo3,
            'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
            'created_by' => Auth::user()->id
        ]);

        if($company_data->save()){
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('store_success'),
            ]);
        }else{
            return response()->json([
                'response_code' => '0',
                'response_message' => getResponseMessage('store_error'),
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company,$id)
    {
        return view('edit.edit-company')->with('id',$id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $company,Request $request,$id)
    {
        $company_data = Company::where('id','=',$id)->first();

        $company_data->file_path = asset('storage').'/';
        if($company_data){
            return response()->json([
                'company' => $company_data,
                'response_code' => '1',
                'response_message' => '',
            ]);
        }else{
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'company_name'=> ['required','max:255',Rule::unique('companies')->ignore($request->id, 'id')],
            'company_code'=>'required|max:255'
        ],
        [
            'company_name.required' => 'Please enter company name',
            'company_name.max' => 'Maximum 255 characters allowed',
            'company_code.required' => 'Please enter company code',
            'company_code.max' => 'Maximum 255 characters allowed'
        ]);

        $companyLogo = "";
        $otherLogo1 = "";
        $otherLogo2 = "";
        $otherLogo3 = "";

        $logos = Company::where('id','=',$request->id)->first(['company_logo','other_logo_1','other_logo_2','other_logo_3']);

        
        if($logos){
            if($logos->company_logo != "" && $request->company_logo_doc != $logos->company_logo ){
                $file = new File();
                $file->delete_file($logos->company_logo);
            }
            if($logos->other_logo_1 != "" && $request->other_logo_1_doc != $logos->other_logo_1 ){
                $file = new File();
                $file->delete_file($logos->other_logo_1);
            }
            if($logos->other_logo_2 != "" && $request->other_logo_2_doc != $logos->other_logo_2){
                $file = new File();
                $file->delete_file($logos->other_logo_2);
            }
            if($logos->other_logo_3 != "" && $request->other_logo_3_doc != $logos->other_logo_3 ){
                $file = new File();
                $file->delete_file($logos->other_logo_3);
            }
        }

        if(isset($request->company_logo_doc) && $request->company_logo_doc != ""){
            $file = new File();
            $isFound =  $file->getFileFromTemp($request->company_logo_doc,$prefix = "company");

            if($isFound != false){
                $companyLogo = $isFound;
            }else{
                if($file->Is_Files_Exists($request->company_logo_doc)){
                    $companyLogo = $request->company_logo_doc;
                }
            }
        }

        if(isset($request->other_logo_1_doc) && $request->other_logo_1_doc != ""){
            $file = new File();
            $isFound =  $file->getFileFromTemp($request->other_logo_1_doc,$prefix = "other_logo_1");

            if($isFound != false){
                $otherLogo1 = $isFound;
            }else{
                if($file->Is_Files_Exists($request->other_logo_1_doc)){
                    $otherLogo1 = $request->other_logo_1_doc;
                }
            }
        }

        if(isset($request->other_logo_2_doc) && $request->other_logo_2_doc != ""){
            $file = new File();
            $isFound =  $file->getFileFromTemp($request->other_logo_2_doc,$prefix = "other_logo_2");

            if($isFound != false){
                $otherLogo2 = $isFound;
            }else{
                if($file->Is_Files_Exists($request->other_logo_2_doc)){
                    $otherLogo2 = $request->other_logo_2_doc;
                }
            }
        }

        if(isset($request->other_logo_3_doc) && $request->other_logo_3_doc != ""){
            $file = new File();
            $isFound =  $file->getFileFromTemp($request->other_logo_3_doc,$prefix = "other_logo_3");

            if($isFound != false){
                $otherLogo3 = $isFound;
            }else{
                if($file->Is_Files_Exists($request->other_logo_3_doc)){
                    $otherLogo3 = $request->other_logo_3_doc;
                }
            }
        }
        
        if($companyLogo == "" ){
            $file = new File();
            $file->delete_file($logos->company_logo);
        }
        if($otherLogo1 == "" ){
            $file = new File();
            $file->delete_file($logos->other_logo_1);
        }
        if($otherLogo2 == ""  ){
            $file = new File();
            $file->delete_file($logos->other_logo_2);
        }
        if($otherLogo3 == "" ){
            $file = new File();
            $file->delete_file($logos->other_logo_3);
        }
        
        $company_data=  Company::where('id','=',$request->id)->update([
            'company_name' => $request->company_name,
            'company_code' => $request->company_code,
            'address' => $request->address,
            'city' => $request->city,
            'pin_code' => $request->pin_code,
            'state' => $request->state,
            'state_code' => $request->state_code,
            'country' => $request->country,
            'phone_no' => $request->phone_no,
            'email' => $request->email,
            'web_address' => $request->web_address,
            'gstin' => $request->gstin,
            'pan' => $request->pan,
            'reverse_charge' => $request->reverse_charge,
            'bank_name' => $request->bank_name,
            'branch_name' => $request->branch_name,
            'account_no' => $request->account_no,
            'account_type' => $request->account_type,
            'ifsc_code' => $request->ifsc_code,
            'company_logo' => $companyLogo,
            'other_logo_1' => $otherLogo1,
            'other_logo_2' => $otherLogo2,
            'other_logo_3' => $otherLogo3,
            'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
            'last_by' => Auth::user()->id
        ]);

        if($company_data){
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('update_success'),
            ]);
        }else{
            return response()->json([
                'response_code' => '0',
                'response_message' => getResponseMessage('update_error'),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,File $file)
    {
        $logos = Company::where('id','=',$request->id)->first(['company_logo','other_logo_1','other_logo_2','other_logo_3']);

        if($logos){
            if($logos->company_logo != ""){
                $file->delete_file($logos->company_logo);
            }
            if($logos->other_logo_1 != ""){
                $file->delete_file($logos->other_logo_1);
            }
            if($logos->other_logo_2 != ""){
                $file->delete_file($logos->other_logo_2);
            }
            if($logos->other_logo_3 != ""){
                $file->delete_file($logos->other_logo_3);
            }
        }

        try{
            Company::destroy($request->id);
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        }catch(\Exception $e){
            report($e);
            if(isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451){
                $error_msg = "This is used somewhere, you can't delete";
            }else{
                $error_msg = getResponseMessage('delete_error');
            }
            return response()->json([
                'response_code' => '0',
                'response_message' => $error_msg,
            ]);
        }
    }
}
