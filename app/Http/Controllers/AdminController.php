<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\File;
use App\Models\Inquiry;
use App\Models\Location;
use App\Models\UserLocation;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DataTables;
use Date;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function manage()
    {
        return view('manage.manage-user');
    }

    public function index(Admin $user,Request $request,DataTables $dataTables)
    {
        $users_data = Admin::select(['user_name','id','person_name','designation','status','allow_production_back_days_entry','email','phone_no','created_on','created_by','last_by','last_on']);

        $dataTable = DataTables::of($users_data)
      
        ->editColumn('user_name', function($users_data){ 
            if($users_data->user_name != null){
                return $users_data->user_name;
                // return ucfirst($users_data->user_name);
            }else{
                return '';
            }
        })
         ->editColumn('person_name', function($users_data){ 
            return $users_data->person_name;
            // return ucfirst($users_data->person_name);
        })
        ->editColumn('designation', function($users_data){ 
            return $users_data->designation;
            // return ucfirst($users_data->designation);
        })
        ->editColumn('status', function($users_data){
            return $users_data->status == 'Active' ? 'Active':'Deactive';
        })
        ->editColumn('allow_production_back_days_entry', function($users_data){ 
            return ($users_data->allow_production_back_days_entry) ?? 0;
            // return ucfirst($users_data->allow_production_back_days_entry);
        })
        // ->filterColumn('admin.status', function ($query, $keyword) {
        //     $lowerKeyword = strtolower(trim($keyword));
        //     if (str_starts_with('active', $lowerKeyword)) {
        //         $query->where('status', '=', 1);
        //     }
        //     elseif (str_starts_with('deactive', $lowerKeyword)) {
        //         $query->where('status', '=', 0);
        //     }
        //     else {
        //         $query->where('status', 'like', "%$keyword%");
        //     }
        // })
        ->filterColumn('admin.status', function ($query, $keyword) {
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
                $query->where('admin.status', '=', $searchStatus);
            }
            else {
                $dbFormatKeyword = str_replace(' ', '_', $lowerKeyword);
                $query->where('admin.status', 'like', "$dbFormatKeyword%");
            }
        })
        // ->filterColumn('admin.status', function($query, $keyword) {
        //     $keyword = strtolower(trim($keyword));
        //     if (Str::startsWith($keyword, 'a')) {
        //         $query->where('status', 1);
        //     } elseif (Str::startsWith($keyword, 'd')) {
        //         $query->where('status', 0);
        //     } else {
        //         $query->where(function($q) use ($keyword) {
        //             $q->where('status', 1)->whereRaw('LOWER("active") LIKE ?', ["%{$keyword}%"])
        //             ->orWhere('status', 0)->whereRaw('LOWER("deactive") LIKE ?', ["%{$keyword}%"]);
        //         });
        //     }
        // })
       
        ->addColumn('options',function($users_data){ 

            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("user", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_user"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if ($users_data->id != 1) {
                    if (hasAccess("user", "delete")) {
                        $action .= '<li> <a class="dropdown-item remove-item-btn">
                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                        </a>
                                    </li>';
                    }
                }

            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'admin');
        return $dataTable
        ->rawColumns(['created_by','created_on','last_by','last_on','user_name','person_name','designation','options','allow_production_back_days_entry'])
        ->make(true);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try{

            /*$setting = Setting::select('total_active_users')->first();
            
            if($setting != null && ($setting->total_active_users != "0" || $setting->total_active_users != "")){
             
                $activeLimit = $setting->total_active_users;
            
                $totalActiveUsers = Admin::select('id')->where('status','=','1')->count();
                
                if($totalActiveUsers == $activeLimit && $request->status == "1"){
                     return response()->json([
                        'response_code' => '0',
                        'response_message' => 'No. of Active Users already exceed '.$activeLimit.'.',
                    ]);
                }
            }*/
            
            $validated = $request->validate([
                'user_name'=>'required|max:500|unique:admin',
                'designation'=>'max:255',
                'person_name' => 'required|max:500',
                'phone_no' => 'max:155',
                'email'=>'email|nullable',
                'password' => 'required|max:12',
            ],
            [
                'user_name.required' => 'Please Enter User Name',
                'designation.max' => 'Maximum 255 characters allowed',
                'person_name.max' => 'Maximum 500 characters allowed',
                'person_name.max' => 'Please Enter Person Name',
                'password.max' => 'Maximum 12 characters allowed',
                'email.email' => 'Please Enter Valid Email',
                'password.required' => 'Please Enter Password'
            ]);
    
            // $status = isset($request->status) ? $request->status : '0';
    
            /* $signatureImage = "";
    
            if(isset($request->signature_image_doc) && $request->signature_image_doc != ""){
                $file = new File();
                $isFound =  $file->getFileFromTemp($request->signature_image_doc,$prefix = "sign");
    
                if($isFound != false){
                    $signatureImage = $isFound;
                }
            } */
    
            $user_data=  Admin::create([
                'user_name'   => $request->user_name,
                'person_name' => $request->person_name,
                'email'       => $request->email,
                'phone_no'   => $request->phone_no,
                'designation' => $request->designation,
                //'signature_image' => $signatureImage,
                'password'    => Hash::make($request->password),
                'status'      => $request->status,
                'allow_production_back_days_entry'  => $request->allow_production_back_days_entry ?? 0,
                'company_id'  => 1,
                'created_on'  => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'  => Auth::user()->id
            ]);

            if($user_data->save()){
                $location_ids = (isset($request->location_ids) && !empty($request->location_ids)) ? $request->location_ids : [1];
                foreach($location_ids as $ctKey => $ctVal){
                    if($ctVal != null){
                        $user_detail_data=  UserLocation::create([
                            'user_id' => $user_data->id,
                            'location_id' => $ctVal,
                            'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                            'created_by' => Auth::user()->id
                        ]);
                    }
                }

                DB::commit();
                return response()->json([
                    'response_code' => '1',
                    'response_message' => getResponseMessage('store_success'),
                ]);
            }else{
                DB::rollBack();
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('store_error'),
                ]);
            }
        }catch(\Exception $e)
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
        $admin_data = Admin::where('id','=',$request->id)->first();
        $user_details_data = UserLocation::where('user_id','=',$request->id)->get();
       // $admin_data->file_path = asset('storage').'/';
       
        if($admin_data){
            return response()->json([
                'user' => $admin_data,
                'user_details_data' => $user_details_data,
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

    public function update(Request $request, Admin $admin)
    {
        DB::beginTransaction();
        try{

            /*$setting = Setting::select('total_active_users')->first();
    
            if($setting != null && ($setting->total_active_users != "0" || $setting->total_active_users != "")){
                
                $activeLimit = $setting->total_active_users;
                
                $totalActiveUsers = Admin::select('id')->where('status','=','1')->where('id','!=',$request->id)->count();
                if($totalActiveUsers == $activeLimit && $request->status == "1"){
                     return response()->json([
                        'response_code' => '0',
                        'response_message' => 'No. of Active Users already exceed '.$activeLimit.'.',
                    ]);
                }
            }*/
            
            $validated = $request->validate([
                'user_name'=> ['required','max:500',Rule::unique('admin')->ignore($request->id, 'id')],
                'designation'=>'max:255',
                'person_name' => 'required|max:500',
                'phone_no' => 'max:155',
                'email'=>'email|nullable',
                'password' => 'max:12',
            ],
            [
                'user_name.required' => 'Please enter user name',
                'designation.max' => 'Maximum 255 characters allowed',
                'person_name.max' => 'Maximum 500 characters allowed',
                'person_name.max' => 'Please Enter Person Name',
                'password.max' => 'Maximum 12 characters allowed',
                'email.email' => 'Please enter valid email'
            ]);
    
            // $status = isset($request->status) ? $request->status : '0';
    
           // $signatureImage = "";
    
            //$imgs = Admin::where('id','=',$request->id)->first('signature_image');
    
            
           /* if($imgs){
                if($imgs->signature_image != "" && $request->signature_image_doc != $imgs->signature_image ){
                    $file = new File();
                    $file->delete_file($imgs->signature_image);
                }
            }
    
            if(isset($request->signature_image_doc) && $request->signature_image_doc != ""){
                $file = new File();
                $isFound =  $file->getFileFromTemp($request->signature_image_doc,$prefix = "sign");
    
                if($isFound != false){
                    $signatureImage = $isFound;
                }else{
                    if($file->Is_Files_Exists($request->signature_image_doc)){
                        $signatureImage = $request->signature_image_doc;
                    }
                }
            }*/
    
            $update_data = [
                'user_name'   => $request->user_name,
                'person_name' => $request->person_name,
                'email'       => $request->email,
                'phone_no'   => $request->phone_no,
                'designation' => $request->designation,
                //'signature_image' => $signatureImage,
                'status'      => $request->status,
                'allow_production_back_days_entry'  => $request->allow_production_back_days_entry ?? 0,
                'last_on'     => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by'     => Auth::user()->id
            ];
    
            if(isset($request->password) && $request->password != ""){
                $update_data['password'] = Hash::make($request->password);
            }
    
            $user = Admin::where('id','=',$request->id)->update($update_data);
            
            if($user)
            {
                $oldUserUnit = UserLocation::where('user_id','=',$request->id)->get();
                $oldUserUnitData = [];
                if($oldUserUnit != null) {
                    $oldUserUnitData = $oldUserUnit->toArray();
                }

                $UserUnits = $request->only('location_ids');
                if (empty($UserUnits['location_ids'])) {
                    $UserUnits['location_ids'] = [1];
                }
                    if(isset($oldUserUnitData) && !empty($oldUserUnitData)){
                        foreach($oldUserUnitData as $oldCtKey => $oldCtVal){
                            if(isset($UserUnits['location_ids'][$oldCtKey]) && $UserUnits['location_ids'][$oldCtKey] != null){
                                $contact_data_updated =  UserLocation::where('user_id','=',$request->id)->where('id','=',$oldCtVal['id'])->update([
                                    'location_id' => $UserUnits['location_ids'][$oldCtKey],
                                    'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                                    'last_by' => Auth::user()->id
                                ]);
                                unset($oldUserUnitData[$oldCtKey]); //remove element from array after use it's key
                                unset($UserUnits['location_ids'][$oldCtKey]); //remove element from array after use it's key
                            }
                        }

                        if(isset($oldUserUnitData) && !empty($oldUserUnitData)) {
                            foreach($oldUserUnitData as $oldCtKey => $oldCtVal) {
                                UserLocation::where('id','=',$oldCtVal['id'])->delete();
                            }
                        }
                        }
                        
                        
                    if(isset($UserUnits['location_ids']) && !empty($UserUnits['location_ids'])){
                        foreach($UserUnits['location_ids'] as $ctKey => $ctVal){
                            if($ctVal != null){
                                $rt_camera_detail_data=  UserLocation::create([
                                    'user_id' => $request->id,
                                    'location_id' => $ctVal,
                                    'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                                    'created_by' => Auth::user()->id
                                ]);
                            }
                        }
                    }

                    if(isset($oldUserUnitData) && !empty($oldUserUnitData)) {
                        foreach($oldUserUnitData as $oldCtKey => $oldCtVal) {
                            UserLocation::where('id','=',$oldCtVal['id'])->delete();
                        }
                    }

                DB::commit();
                return response()->json([
                    'response_code' => '1',
                    'response_message' => getResponseMessage('update_success'),
                ]);
            }else{
                DB::rollBack();
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }
        }catch(\Exception $e)
        {
            report($e);
            dd($e->getLine());
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

        if($request->id == "1"){
            return response()->json([
                'response_code' => '0',
                'response_message' => 'You can\'t delete admin record',
            ]);
        }else if($request->id == Auth::user()->id){
            return response()->json([
                'response_code' => '0',
                'response_message' => 'You can\'t delete current logged in user',
            ]);
        }
        
        // $imgs = Admin::where('id','=',$request->id)->first('signature_image');

        // if($imgs){
        //     if($imgs->signature_image != ""){
        //         $file = new File();
        //         $file->delete_file($imgs->signature_image);
        //     }
        // }
        
        try{

            $inqiry_prepared_by_id = Inquiry::where('inq_prepared_by_id',$request->id)->get();
            if($inqiry_prepared_by_id->isNotEmpty())
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Customer Is Used In Inquiry.",
                ]);
            }
            
            Admin::destroy($request->id);
            UserLocation::where('user_id',$request->id)->delete();
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

    public function check(Request $request){
        $validated = $request->validate([
            'password' => 'required'  
        ],
        [
            'password.required' => 'Please Enter Password'
        ]);

        $admin_data = Admin::where('id','=',Auth::user()->id)->first('password');
        if($admin_data != null){
            if(Hash::check($request->password, $admin_data->password)){
                return response()->json([
                    'response_code' => '1',
                    'response_message' => 'Authenticated Successfully!',
                ]);
            }else{
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'password does not match',
                ]);
            }
        }else{
            return response()->json([
                'response_code' => '0',
                'response_message' => 'password does not match',
            ]);
        }
    }

    public function showUserAccess(Admin $admin)
    {
        $users = Admin::select('user_name','id')->where('status',1)->where('id','!=',1)->get();
        $user2 = Admin::select('user_name','id')->where('status',1)->where('id','!=',0)->get();
        return view('edit.edit-user_access')->with(['users' => $users,'user2' =>$user2]);
    }

    public function existsUsername(Request $request){
        if($request->term != ""){
            $fdUsername = Admin::select('user_name')->where('user_name', 'LIKE', '%'.$request->term.'%')->groupBy('user_name')->get();
            if($fdUsername != null){
                
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdUsername as $usKey){

                    $output .= '<li parent-id="user_name" list-id="user_name_list" class="list-group-item" tabindex="0">'.$usKey->user_name.'</li>';
                } 
                $output .= '</ul>';

                return response()->json([
                    'usernameList' => $output,
                    'response_code' => 1,
                ]);
            }else{
                return response()->json([
                    'response_message' => 'No Username available',
                    'response_code' => 0,
                ]);
            }
        }else{
            return response()->json([
                'usernameList' => '',
                'response_code' => 1,
            ]);
        }
    }

    public function existsDesignation(Request $request){
        // dd($request->all());
        if($request->term != ""){
            $fdUsername = Admin::select('designation')->where('designation', 'LIKE', '%'.$request->term.'%')->groupBy('designation')->get();
            if($fdUsername != null){
                
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdUsername as $usKey){

                    $output .= '<li parent-id="designation" list-id="designation_list" class="list-group-item" tabindex="0">'.$usKey->designation.'</li>';
                } 
                $output .= '</ul>';

                return response()->json([
                    'designationList' => $output,
                    'response_code' => 1,
                ]);
            }else{
                return response()->json([
                    'response_message' => 'No Designation available',
                    'response_code' => 0,
                ]);
            }
        }else{
            return response()->json([
                'designationList' => '',
                'response_code' => 1,
            ]);
        }
    }

    public function getLocationData(Request $request)
    {
        try
        {
            $location_data = Location::select([
                'location.location_id',
                'location.location_name',
                'location.location_type',
                'location.location_code',
                'location.location_state_id',
                'states.state',
                'location.location_city_id',
                'cities.city',
                'location.nabl_id',
                'location.location_nabl_applicable',
                'nabl_configurations.nabl_location',
                'location.location_status',
            ])
            ->leftJoin('states', 'states.id', '=', 'location.location_state_id')
            ->leftJoin('cities', 'cities.id', '=', 'location.location_city_id')
            ->leftJoin('nabl_configurations', 'nabl_configurations.nabl_id', '=', 'location.nabl_id')
            ->get();

            if($location_data->count() > 0)
            {
                return response()->json([
                    'response_code' => 1,
                    'response_message' => 'Location Data Found',
                    'location_data' => $location_data
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => 0,
                    'response_message' => 'No Location Data Found',
                    'location_data' => []
                ]);
            }
        }
        catch(\Exception $e)
        {
            report($e);
            return response()->json([
                'response_code' => 0,
                'response_message' => $e->getMessage(),
                'location_data' => []
            ]);
        }
    }
}