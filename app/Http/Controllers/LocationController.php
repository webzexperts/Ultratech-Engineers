<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
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
use App\Models\File;
use Maatwebsite\Excel\Facades\Excel;

class LocationController extends Controller
{
    public function manage()
    {
        return view('manage.manage-location');
    }

    public function index(Location $location_data, Request $request, DataTables $datatables)
    {
        $location_data = Location::select([
            'location.location_id',
            'location.location_name',
            'location.location_type',
            'location.location_code',
            'location.location_address',
            'location.gst_bill_location',
            'location.location_country_id',
            'countries.country_name',
            'location.location_state_id',
            'states.state',
            'location.location_city_id',
            'cities.city',
            'location.location_nabl_applicable',
            'location.nabl_id',
            'location.location_ilac_applicable',
            'location.location_gstin',
            'location.location_company_name',
            'location.location_status',
            'location.show_all_location_camera',
            'nabl_configurations.nabl_location',
            'location.created_on',
            'location.created_by',
            'location.last_by',
            'location.last_on'
        ])
        ->leftJoin('countries', 'countries.id', '=', 'location.location_country_id')
        ->leftJoin('states', 'states.id', '=', 'location.location_state_id')
        ->leftJoin('cities', 'cities.id', '=', 'location.location_city_id')
        ->leftJoin('nabl_configurations', 'nabl_configurations.nabl_id', '=', 'location.nabl_id');
        $dataTable =DataTables::of($location_data)
        ->filterColumn('location.location_status', function ($query, $keyword) {
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
                $query->where('location.location_status', '=', $searchStatus);
            } 
            else {
                $dbFormatKeyword = str_replace(' ', '_', $lowerKeyword);
                $query->where('location.location_status', 'like', "$dbFormatKeyword%");
            }
        })
        ->addColumn('options',function($location_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("location", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_location"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }
                if($location_data->location_id != getCurrentLocation()->location_id)
                {
                    if (hasAccess("location", "delete") && $location_data->location_id != 1) {
                        $action .= '<li> <a class="dropdown-item remove-item-btn">
                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                        </a>
                                    </li>';
                    }
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'location');
        return $dataTable
        ->rawColumns(['options','location_name','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function getLocationState(Request $request)
    {
        $relData = State::select(['id','state'])
                    ->where('states.country_id','=',$request->country_id)
                    ->orderBy('state', 'ASC')
                    ->get();

        if($relData != null)
        {
            return response()->json([
                'states' =>  $relData,
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

    public function getLocationCity(Request $request)
    {
        $relData = City::select(['id','city'])
                    ->where('cities.state_id','=',$request->state_id)        
                    ->orderBy('city', 'ASC')
                    ->get();

        if($relData != null)
        {
            return response()->json([
                'cities' => $relData,
                'response_code' => '1',
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'relation_data' =>  '',
                'response_message' => 'No Relation Data Available!'
            ]);
        }
    }

    public function locationLNRData()
    {
        $lnr_data = Location::select('location_id','location_company_name')->orderBy('location_id','desc')->first();
            return response()->json([
            'response_code' => 1,
            'lnr_data'       => $lnr_data,
        ]);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $year_data = getCurrentYearData();

        $nabl_images = '';
        $nabl_blobImage = '';
        if($request->location_nabl_symbol_doc)
        {
            $file = new File();
            $isFound =  $file->getFileFromTemp($request->location_nabl_symbol_doc,$prefix = 'location_nabl_symbol');
            if($isFound !== false)
            {
                $nabl_images = $isFound;
                $filePath = storage_path('app/public/' . $nabl_images);
                if (!empty($nabl_images) && $file->Is_Files_Exists($nabl_images))
                {
                    $nabl_blobImage = file_get_contents($filePath);
                }
            }
        }
        $ilac_images = '';
        $ilac_blobImage = '';
        if($request->location_ilac_symbol_doc)
        {
            $file = new File();
            $isFound =  $file->getFileFromTemp($request->location_ilac_symbol_doc,$prefix = 'location_ilac_symbol');
            if($isFound !== false)
            {
                $ilac_images = $isFound;
                $filePath = storage_path('app/public/' . $ilac_images);
                if (!empty($ilac_images) && $file->Is_Files_Exists($ilac_images))
                {
                    $ilac_blobImage = file_get_contents($filePath);
                }
            }
        }

        DB::beginTransaction();
        try
        {
            $location_data = Location::create([
                'location_name' => $request->location_name,
                'location_type' => $request->location_type,
                'location_code' => $request->location_code,
                'location_address' => $request->location_address,
                'gst_bill_location' => $request->gst_bill_location,
                'location_company_name' => $request->location_company_name,
                'location_country_id' => $request->country_id,
                // 'location_country_id' => $request->location_country_id,
                'location_state_id' => $request->state_id,
                // 'location_state_id' => $request->location_state_id,
                'location_city_id' => $request->location_city_id,
                'location_pin_code' => $request->location_pin_code,
                'location_phone_no' => $request->location_phone_no,
                'location_email_id' => $request->location_email_id,
                'location_pan' => $request->location_pan,
                'location_nabl_applicable' => $request->location_nabl_applicable,
                'nabl_id' => $request->nabl_id,
                'location_nabl_symbol' => $nabl_images !="" ? $nabl_images : null,
                'location_nabl_symbol_blob' =>$nabl_blobImage !="" ? $nabl_blobImage : null,
                'location_nabl_test' => !empty($request->location_nabl_test) ? implode(',', $request->location_nabl_test) : null,
                'location_ilac_applicable' => $request->location_ilac_applicable,
                'location_ilac_symbol' =>  $ilac_images !="" ? $ilac_images : null,
                'location_ilac_symbol_blob' => $ilac_blobImage !="" ? $ilac_blobImage : null,
                'location_gstin' => $request->location_gstin,
                'location_header_address' =>$request->input('location_header_address'),
                'location_status' => $request->location_status,
                'show_all_location_camera' => $request->show_all_location_camera == 'Yes' ? 'Yes' : 'No',
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($location_data->save())
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
        $isAnyPartInUse = false;
        // $location_data = Location::where('location_id','=',$request->id)->first();
        $location_data = Location::select([
            'location.*',
            'countries.country_name',
            'states.state'
        ])
        ->leftJoin('cities','cities.id','=','location.location_city_id')
        ->leftJoin('states','states.id','=','location.location_state_id')
        ->leftJoin('countries','countries.id','=','location.location_country_id')
        ->where('location.location_id','=',$request->id)->first();
            if (!empty($location_data)) {
                if(isset($location_data->location_nabl_symbol_blob)){
                    $location_data->location_nabl_symbol_blob = base64_encode($location_data->location_nabl_symbol_blob);
                }
                if(isset($location_data->location_ilac_symbol_blob)){
                    $location_data->location_ilac_symbol_blob = base64_encode($location_data->location_ilac_symbol_blob);
                }

            }
            $isued_location_data =  DB::select('CALL location_master_used_list(?)', [$request->id]);
            if(!empty($isued_location_data) && $isued_location_data[0]->category == "Transaction"){

                $location_data->code_in_use =true;
            }else{
                $location_data->code_in_use =false;
            }
            
            $transactionData = array_filter($isued_location_data, function ($item) {
                return $item->category === "Transaction";
            });
            // dd($transactionData);
            $isLocationType = false;

            foreach($transactionData as $dKey => $dVal )
            {
                if($dVal->location_type == "bill_to_location_id" || $dVal->location_type == "bill_to_id"){
                    $isLocationType = true;
                
                }
            }
            $location_data->bill_in_use = $isLocationType;
            // $usage = DB::table('location_used')
            //             ->where('location_id', $request->id)
            //             ->first();
            
            // if (!empty($usage)) {
            //     $location_data->in_use =true;
            // } else{
            //     $location_data->in_use =false;
            // }
               

        if($location_data)
        {
            return response()->json([
                'location_data' => $location_data,
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
        $year_data = getCurrentYearData();

            $imgs = Location::where('location_id', $request->id)->value('location_nabl_symbol');

            $file = new File();
            $nabl_images = '';
            $nabl_blobImage = '';

            if($request->location_nabl_symbol_doc != '' && $request->location_nabl_symbol_doc != null){
                if($imgs && !empty($imgs)){
                    if($imgs != $request->location_nabl_symbol_doc){
                            $file->delete_file($imgs);
                    }                 
                }      
                
                $isFound = $file->getFileFromTemp($request->location_nabl_symbol_doc,'location_nabl_symbol');

                if($isFound !== false){                    
                    $nabl_images = $isFound;
                    $filePath = storage_path('app/public/' . $nabl_images);
                    $nabl_blobImage = file_exists($filePath) ? file_get_contents($filePath) : null;
                }else{
                    $nabl_images = $request->location_nabl_symbol_doc;
                    $filePath = storage_path('app/public/' . $nabl_images);
                    $nabl_blobImage = $nabl_blobImage = file_get_contents($filePath);
                }
            }else{
                if($imgs && !empty($imgs)){
                    $file->delete_file($imgs);
                }  
            }  
            
            $imgs_ilac = Location::where('location_id', $request->id)->value('location_ilac_symbol');

            $file = new File();
            $ilac_images = '';
            $ilac_blobImage = '';

            if($request->location_ilac_symbol_doc != '' && $request->location_ilac_symbol_doc != null){
                if($imgs_ilac && !empty($imgs_ilac)){
                    if($imgs_ilac != $request->location_ilac_symbol_doc){
                            $file->delete_file($imgs_ilac);
                    }                 
                }      
                
                $isFound = $file->getFileFromTemp($request->location_ilac_symbol_doc,'location_ilac_symbol');

                if($isFound !== false){                    
                    $ilac_images = $isFound;
                    $filePath = storage_path('app/public/' . $ilac_images);
                    $ilac_blobImage = file_exists($filePath) ? file_get_contents($filePath) : null;
                }else{
                    $ilac_images = $request->location_ilac_symbol_doc;
                    $filePath = storage_path('app/public/' . $ilac_images);
                    $ilac_blobImage = $ilac_blobImage = file_get_contents($filePath);
                }
            }else{
                if($imgs_ilac && !empty($imgs_ilac)){
                    $file->delete_file($imgs_ilac);
                }  
            }        

        DB::beginTransaction();
        try
        {
            $location_data = Location::where('location_id','=',$request->id)->update([
                'location_name' => $request->location_name,
                'location_type' => $request->location_type,
                'location_code' => $request->location_code,
                'location_address' => $request->location_address,
                'gst_bill_location' => $request->gst_bill_location,
                'location_company_name' => $request->location_company_name,
                'location_country_id' => $request->country_id,
                // 'location_country_id' => $request->location_country_id,
                'location_state_id' => $request->state_id,
                // 'location_state_id' => $request->location_state_id,
                'location_city_id' => $request->location_city_id,
                'location_pin_code' => $request->location_pin_code,
                'location_phone_no' => $request->location_phone_no,
                'location_email_id' => $request->location_email_id,
                'location_pan' => $request->location_pan,
                'location_nabl_applicable' => $request->location_nabl_applicable,
                'nabl_id' => $request->nabl_id,
                'location_nabl_symbol' => $nabl_images !="" ? $nabl_images : null,
                'location_nabl_symbol_blob' =>$nabl_blobImage !="" ? $nabl_blobImage : null,
                'location_nabl_test' => !empty($request->location_nabl_test) ? implode(',', $request->location_nabl_test) : null,
                'location_ilac_applicable' => $request->location_ilac_applicable,
                'location_ilac_symbol' =>  $ilac_images !="" ? $ilac_images : null,
                'location_ilac_symbol_blob' => $ilac_blobImage !="" ? $ilac_blobImage : null,
                'location_gstin' => $request->location_gstin,
                'location_header_address' => $request->location_header_address,
                'location_status' => $request->location_status,
                'show_all_location_camera' => $request->show_all_location_camera == 'Yes' ? 'Yes' : 'No',
                'company_id' => Auth::user()->company_id,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($location_data)
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
        DB::beginTransaction();
        try
        {
           
            // $usage = DB::table('location_usage')
            //             ->where('location_id', $request->id)
            //             ->first();
            
            $usage = DB::select('CALL location_master_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                $message = "You Can't Delete, Location Is Used In ". $usage[0]->table_name.".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            if (UserLocation::where('location_id', $request->id)->exists()) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Location Is Used In User Locations.",
                ]);
            }


            Location::destroy($request->id);

            DB::commit();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);

        } catch (\Exception $e) {
            report($e);
            DB::rollBack();

            $error_msg = getResponseMessage('delete_error');

            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451) {
                $error_msg = "This Is Used Somewhere, You Can't Delete";
            }

            return response()->json([
                'response_code' => '0',
                'response_message' => $error_msg,
            ]);
        }
    }
    public function existsLocationName(Request $request)
    {
        if($request->term != "")
        {
            $fdLocation = Location::select('location_name')->where('location_name', 'LIKE', $request->term.'%')->groupBy('location_name')->get();
            if($fdLocation != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdLocation as $dsKey)
                {
                    $output .= '<li parent-id="location_name" list-id="location_name_list" class="list-group-item" tabindex="0">'.$dsKey->location_name.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'location_nameList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Location Name Available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'location_nameList' => '',
                'response_code' => 1,
            ]);
        }
    }

    public function existsLocationCode(Request $request)
    {
        if($request->term != "")
        {
            $fdLocation = Location::select('location_code')->where('location_code', 'LIKE', $request->term.'%')->groupBy('location_code')->get();
            if($fdLocation != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdLocation as $dsKey)
                {
                    $output .= '<li parent-id="location_code" list-id="location_code_list" class="list-group-item" tabindex="0">'.$dsKey->location_code.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'location_codeList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Location Code Available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'location_codeList' => '',
                'response_code' => 1,
            ]);
        }
    }

    public static function getDefaultLocationData($forBlade = false){
        
        $locationData = Location::where('location_id','=',session('getLocationId'))->first();
        
        if($locationData == null){
           
            if($forBlade == false){
                return response()->json([
                    'response_code' => '0',
                    'data' => $locationData,
                    'response_message' => 'Company Year Data Not Available'
                ]);
            }else{
                 return null;
            }
            
        }
        return $locationData;
    }

    public function getLocationRelationValues(Request $request)
    {
        $relData = City::select([
            'states.country_id',
            'countries.id as country_id',
            'countries.country_name',
            'states.id as state_id',
            'states.state',
            'states.state_code'
        ])
        ->leftJoin('states','states.id','=','cities.state_id')
        ->leftJoin('countries','countries.id','=','states.country_id')
        ->where('cities.id','=',$request->location_city_id)
        ->first();

        if($relData != null)
        {
            return response()->json([
                'response_code' => '1',
                'relation_data' =>  $relData
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'relation_data' =>  '',
                'response_message' => 'No Relation Data Available!'
            ]);
        }
    }
}