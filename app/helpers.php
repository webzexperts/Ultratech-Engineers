<?php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Crypt;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Mail;
    use App\Mail\ReportsEmail;
    use App\Http\Controllers\UserAccessController;
    use App\Http\Controllers\CompanyYearController;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use App\Models\Company;
    use App\Models\SMTPConfiguration;

    use App\Models\Menus;
    use App\Models\Module;
    use App\Models\Admin;
    use App\Models\Country;
    use App\Models\State;
    use App\Models\City;
    use App\Models\Customer;
    use App\Models\TypeOfJob;
    use App\Models\Part;
    use App\Models\JobDescription;
    use App\Models\GstConfiguration;    
    use Illuminate\Support\Arr;
    use Pest\ArchPresets\Custom;
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Date;
    use App\Models\Unit;
    use App\Models\Reason;
    use App\Models\Supplier;
    use App\Models\Item;
    use App\Models\ItemGroup;
    use App\Models\MaterialInspection;
    use App\Models\NABLConfiguration;
    use App\Models\Location;
    use App\Models\UserLocation;
    use App\Models\ItemOpening;
    use App\Models\ItemOpeningProdArea;
    use App\Models\AssignFormatNo;
    use App\Models\Material;
    use App\Models\AreaOfCoverage;
    use App\Models\ProcedureReference;
    use App\Models\EvaluationAsPer;
    use App\Models\AcceptanceStandard;
    use App\Models\Enclosure;
    use App\Models\FilmBrand;
    use App\Models\FilmType;
    use App\Models\IqiDesignation;
    use App\Models\IqiSensitivity;
    use App\Models\Film;
    use App\Models\AuthorityPerson;
    use App\Models\FindingLevel;
    use App\Models\Finding;
    use App\Models\FilmResult;
    use App\Models\EquipmentMPT;
    use App\Models\DPTChemical;
    use App\Models\MptDptMaterialBatchWiseStock;
    use App\Exceptions\InsufficientStockException;


    use App\Models\PurchaseOrder;
    use App\Models\Transaction\ServicePO;
    
    

    use App\Http\Controllers\LocationController;
use App\Models\RTCamera;
use App\Models\Transaction\TestReportRt;

    // use function Psy\info;

    /**
     * Date Time Format 
    */
    define('DATE_TIME_FORMAT','d-m-Y | H:i:s');

    /**
     * Date Format 
    */
    define('DATE_FORMAT','d/m/Y');

    /**
     * Date Time Format Sql Raw
    */
    define('DATE_TIME_FORMAT_RAW','%d-%m-%Y | %H:%i:%s');

    /**
     * Date Format Sql Raw
    */
    define('DATE_FORMAT_RAW','%d/%m/%Y');

    /**
     * JS Version
    */
    function getJsVersion()
    {
        return "0.10.02";
    } 

    /**
     * CSS Version
    */
    function getCssVersion()
    {
        return "0.10.02";
    }

    // function getLatestSequence($modal,$sequence)
    // {
    //     $year_data = getCurrentYearData();
    //     $isFound = $modal::where('year_id', '=', $year_data->id)->max($sequence);
    //     if ($isFound != null)
    //     {
    //         $isFound++;
    //     }
    //     else
    //     {
    //         $isFound = 1;
    //     }

    //     $middle_num = str_pad($isFound, 3, "0", STR_PAD_LEFT);
    //     $postfix = $year_data->yearcode;
    //     $format =  $middle_num . '/' . $postfix;
    //     return [
    //         'format' => $format,
    //         'isFound' => $isFound
    //     ];
    // }

    // function duplicationSequnce($modal,$seq, $sequence, $id,$table_id)
    // {
    //     $year_data = getCurrentYearData();
    //     $check = $modal::where('year_id', '=', $year_data->id)->where($seq, $sequence)->where($table_id, '!=', $id)->first();
    //     if($check != null && $check != "")
    //     {
    //         return [
    //             'format' => 0,
    //         ];
    //     }
    //     else
    //     {
    //         $middle_num = str_pad($sequence, 3, "0", STR_PAD_LEFT);
    //         $postfix = $year_data->yearcode;
    //         $format =  $middle_num . '/' . $postfix;

    //         return [
    //             'format' => $format,
    //             'isFound' => $sequence
    //         ];
    //     }
    // }

    // function getLatestSequence($modal,$sequence)
    // {
    //     $year_data = getCurrentYearData();      
 
    //     $isFound = $modal::where('year_id', '=', $year_data->id)->max($sequence);
       
    //     if ($isFound != null) {
    //         $isFound++;
    //     } else {
    //         $isFound = 1;
    //     }
       
    //     $middle_num = str_pad($isFound, 4, "0", STR_PAD_LEFT);
       
    //     $postfix = $year_data->yearcode;
 
    //     if($modal == 'App\Models\Quotation'){
 
    //         $format =  'PNEC/QUOT/' .$middle_num . '/' . $postfix;
 
    //     }else{
 
    //         $format =  $middle_num . '/' . $postfix;
    //     }
       
           
    //     return [
    //         'format' => $format,
    //         'isFound' => $isFound
    //     ];
       
    // }
 
    // function duplicationSequence($modal,$seq, $sequence, $id,$table_id)
    // {
    //     $year_data = getCurrentYearData();
       
    //     $check = $modal::where('year_id', '=', $year_data->id)->where($seq, $sequence)->where($table_id, '!=', $id)->first();
       
    //     if($check != null && $check != "")
    //     {
    //         return [
    //             'format' => 0,          
    //         ];
    //     }
    //     else{
    //         $middle_num = str_pad($sequence, 4, "0", STR_PAD_LEFT);
    //         $postfix = $year_data->yearcode;  
           
    //         if($modal == 'App\Models\Quotation'){
 
    //             $format =  'PNEC/QUOT/' .$middle_num . '/' . $postfix;    
 
    //         }else{
 
    //             $format =  $middle_num . '/' . $postfix;    
    //         }
           
           
    //         return [
    //             'format' => $format,
    //             'isFound' => $sequence
    //         ];        
    //     }
    // }
    function getItemCurrentStock($item_id, $location_id)
    {
        return ItemOpening::where('io_item_id', $item_id)->where('current_location_id', $location_id)->sum('io_stock_qty');
    }

    function getLatestSequence($modal, $sequence, $prefix = null)
    {  
        $year_data = getCurrentYearData();
        $locationData = getCurrentLocation()->location_id;
 
        $isFound = $modal::where('year_id', $year_data->id)->where('current_location_id', $locationData)->max($sequence);
        $isFound = $isFound ? $isFound + 1 : 1;
 
        $middle_num = str_pad($isFound, 4, '0', STR_PAD_LEFT);
        $postfix = $year_data->yearcode;
 
        // $format = $prefix
        //     ? $prefix . '/' . $middle_num . '/' . $postfix
        //     : $middle_num . '/' . $postfix;

        $locationCode = getCurrentLocation()->location_code;
        // $locationCodeFormatted = !empty($locationCode) 
        //     ? str_pad($locationCode, 2, '0', STR_PAD_LEFT) 
        //     : '';
        $locationCodeFormatted = !empty($locationCode) 
            ? $locationCode
            : '';

        $format = 'Ultratech/' 
            . ($locationCodeFormatted ? $locationCodeFormatted . '/' : '') 
            . $prefix . '/' . $middle_num . '/' . $postfix;
 
        return [
            'format'  => $format,
            'isFound' => $isFound
        ];
    }

    function getMarketingLatestSequence($modal, $sequence, $prefix = null)
    {  
        $year_data = getCurrentYearData();
        $locationData = getCurrentLocation()->location_id;
 
        $isFound = $modal::where('year_id', $year_data->id)->max($sequence);
        $isFound = $isFound ? $isFound + 1 : 1;
 
        $middle_num = str_pad($isFound, 4, '0', STR_PAD_LEFT);
        $postfix = $year_data->yearcode;     

        if($prefix != ""){
            $format = $prefix .'/' . $middle_num . '/' . $postfix;
        }else{
            $format = 'Ultratech/' . $middle_num . '/' . $postfix;
        }

 
        return [
            'format'  => $format,
            'isFound' => $isFound
        ];
    }

    function duplicationSequence($modal,$seq, $sequence, $id,$table_id, $prefix = null  )
    {
        $year_data = getCurrentYearData();
        $locationData = getCurrentLocation()->location_id;
        $check = $modal::where('year_id', '=', $year_data->id)->where('current_location_id', $locationData)->where($seq, $sequence)->where($table_id, '!=', $id)->first();
       
        if($check != null && $check != "")
        {
            return [
                'format' => 0,          
            ];
        }
        else{
            $middle_num = str_pad($sequence, 4, "0", STR_PAD_LEFT);
            $postfix = $year_data->yearcode;  
           
            // if($modal == 'App\Models\Quotation'){
 
            //     $format =  'PNEC/QUOT/' .$middle_num . '/' . $postfix;    
 
            // }else{
 
            //     $format =  $middle_num . '/' . $postfix;    
            // }

            // old working code as on 30-03-2026
            /*if (!empty($prefix)) {
                $format = $prefix . $middle_num . '/' . $postfix;
            } else {
                $format = $middle_num . '/' . $postfix;
            }*/
            // old working code end

            // new code as on 30-03-2026
            $locationCode = getCurrentLocation()->location_code;
            // $locationCodeFormatted = !empty($locationCode)
            //     ? str_pad($locationCode, 2, '0', STR_PAD_LEFT)
            //     : '';
            $locationCodeFormatted = !empty($locationCode)
                ? $locationCode
                : '';

            $format = 'Ultratech/' 
                . ($locationCodeFormatted ? $locationCodeFormatted . '/' : '') 
                . ($prefix ? $prefix . '/' : '') 
                . $middle_num . '/' 
                . $postfix;

            return [
                'format'  => $format,
                'isFound' => $sequence
            ];
        }
    }


    function duplicationMarketingSequence($modal,$seq, $sequence, $id,$table_id, $prefix = null  )
    {
        $year_data = getCurrentYearData();
        $locationData = getCurrentLocation()->location_id;
        $check = $modal::where('year_id', '=', $year_data->id)->where($seq, $sequence)->where($table_id, '!=', $id)->first();
       
        if($check != null && $check != "")
        {
            return [
                'format' => 0,          
            ];
        }
        else{
            $middle_num = str_pad($sequence, 4, "0", STR_PAD_LEFT);
            $postfix = $year_data->yearcode;            
            

            if($prefix != ""){
                $format = $prefix .'/' . $middle_num . '/' . $postfix;
            }else{
                $format = 'Ultratech/' . $middle_num . '/' . $postfix;
        }

            // $format = 'Ultratech/' 
            //     . $middle_num . '/' 
            //     . $postfix;

            return [
                'format'  => $format,
                'isFound' => $sequence
            ];
        }
    }

    /**
     * Function is used to know current page is active or not, *( 'for this here we use route name' ).
     * var Page = type string -- single page name should be provided
     * var actions = type @array
     * var noactions = type @boolean -- If true no actions in $actions array append to page name
     * var postfix = type string -- this string append to page name at last
     * var divider = type string 
     * Ex: (
     *  '-' is divider and 'edit' is action ,'web' is post fix -- in below example
     *       example == " edit-{page name}-web "-- route name
     * )
    */

    /**
     * Function is used to know single page in $Pages array is active or not, *( 'for this here we use route name' ).
     * var Pages = type @array -- Multiple pages name should be provided
     * var actions = type @array 
     * var noactions = type @boolean -- If true no actions in $actions array prepend to page name at first 
     * var postfix = type string -- this string append to page name at last
     * var divider = type string 
     * Ex: (
     *  '-' is divider and 'edit' is action ,'web' is post fix -- in below example
     *      example == " edit-{page name}-web "-- route name
     * )
    */

    function isActivePages($Pages = array(),$actions = array(),$noactions = false,$postfix = "web",$divider = "-")
    {
        if(empty($Pages))
        {
            return false;
        }

        if(empty($actions))
        {
            $actions = [
                'add',
                'edit',
                'manage'
            ];
        }

        if($noactions == false)
        {
            foreach($Pages as $page)
            {
                foreach($actions as $action)
                {
                    if(Route::currentRouteName() == $action.$divider.$page.$divider.$postfix || Route::currentRouteName() == $action.$divider.$page)
                    {
                        return true;
                    }
                }
            }
            return false;
        }
        else
        {
            foreach( $Pages as $page)
            {
                if(Route::currentRouteName() == $Page)
                {
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * User Access Check
    */
    // function hasAccess($pageNm,$actionNm)
    // {
    //     $request = new Request();
    //     if(!UserAccessController::checkUserAccess($request,Auth::user()->id, $pageNm,$actionNm))
    //     {
    //         return false;
    //     }
    //     return true;
    // }

    /* For get All Company_year  ids which is less or equal to current company_year */
    function getCompanyYearIdsToTill()
    {
        return CompanyYearController::getTillYearIds();
    }

    /**
     * Retrive Company Year Data based on session('default_year_id') set by selecting year on front side
     * 
     * Note : if not found data then it will return json response included empty data set and message string
    */
    function getCurrentYearData()
    {
        return CompanyYearController::getDefaultYearData();
    }

    /**
     * This function will return startdate and enddate value for current selected company year
    */
    function getCurrentYearDates()
    {
        $defYearData = getCurrentYearData();
        if ($defYearData instanceof \Illuminate\Http\JsonResponse) {
            return ['startdate' => '', 'enddate' => ''];
        }
        return ['startdate' => $defYearData != null ? $defYearData->startdate : '','enddate' => $defYearData != null ? $defYearData->enddate : ''];
    }

    /**
     * Retrive Default company Year
    */
    function defaultCompanyYear()
    {
        $dcompanyYear = CompanyYearController::getDefaultComapnyYear();
        if($dcompanyYear != null)
        {
            return $dcompanyYear;
        }
        else
        {
            return  "";
        }
    }

    function accessModule()
    {
        return Module::orderBy('module_index', 'ASC')->get();
    }
    
    function accessMenu($id)
    {
        $result = DB::table('menus')->select('page')->where('Parent', '=', $id)->orderBy('Sequence', 'asc')->pluck('page')->toArray();
        return $result;
    }

    function manageAccessMenu($id)
    {
        return  Menus::where('parent', $id)->where('show_in_menu', 'YES')->where('show_in_access', 'YES')->orderBy('sequence' ,'ASC')->get();
    }
    function isSubMenuEnabled()
    {
        return (bool) config('app.enable_sub_menu', false);
    }

    function getSubModules($moduleId)
    {
        if (!isSubMenuEnabled()) {
            return collect([]);
        }
        return DB::table('sub_modules')->where('module_id', $moduleId)->orderBy('sequence', 'asc')->get();
    }

    function getSubModuleMenus($subModuleId)
    {
        return DB::table('menus')
            ->where('sub_module_id', $subModuleId)
            ->where('show_in_menu', 'YES')
            ->where('show_in_access', 'YES')
            ->orderBy('sequence', 'asc')
            ->get();
    }

    function getDirectModuleMenus($moduleId)
    {
        $query = DB::table('menus')
            ->where('parent', $moduleId)
            ->where('show_in_menu', 'YES')
            ->where('show_in_access', 'YES')
            ->orderBy('sequence', 'asc');

        if (isSubMenuEnabled()) {
            $query->whereNull('sub_module_id');
        }

        return $query->get();
    }

    function hasSubModuleAccess($subModuleId)
    {
        if (!isSubMenuEnabled()) {
            return false;
        }

        $pages = DB::table('menus')
            ->where('sub_module_id', $subModuleId)
            ->where('show_in_menu', 'YES')
            ->where('show_in_access', 'YES')
            ->pluck('page')
            ->toArray();
        
        return hasAccess($pages, 'manage');
    }

    function getCountries()
    {
        return Country::select('id','country_name')->orderBy('country_name','asc')->get();
    }

    function getStates()
    {
        return State::select('id','state')->orderBy('state','asc')->get();
    }

    function getCities()
    {
        return City::select('id','city')->orderBy('city','asc')->get();
    }

    function getCustomers()
    {
        return Customer::select('id','customer')->orderBy('customer','asc')->get();
    }


    function getMaterial()
    {
        return Material::select('id','material')->orderBy('material','asc')->get();
    }

    function getAreaOfCoverage()
    {
        return AreaOfCoverage::select('area_of_coverage_id','area_of_coverage')->orderBy('area_of_coverage','asc')->get();
    }

    function getProcedureReferance()
    {
        return ProcedureReference::select('procedure_reference_id','procedure_reference')->orderBy('procedure_reference','asc')->get();
    }

    function getEvaluationAsPer()
    {
        return EvaluationAsPer::select('evaluation_as_per_id','evaluation_as_per')->orderBy('evaluation_as_per','asc')->get();
    }

    function getAcceptanceStandards()
    {
        return AcceptanceStandard::select('id','acceptance_standard')->orderBy('acceptance_standard','asc')->get();
    }
    
    function getNABLConfiguration()
    {
        return NABLConfiguration::select('nabl_id','nabl_location')->orderBy('nabl_location','asc')->get();
    }


    // function getUserLocation(){
    //     // dd(Session::get('getLocationId'));
    //     $location = Session::get('getLocationId');
    //     // dd($location);
    //     $lc = Location::where('location_id', $location)->pluck('location_name')->first();
    //     $location_type = Location::select('location_type')->where('location_id',$location)->first();
    //     Session::put('getLocationType', $location_type->location_type);
    //     return $lc;

    // }

     function getUserLocation(){
        $location = Session::get('getLocationId');

        if($location == null){
            $location = 1;
            Session::put('getLocationId', $location);
            $locTypeRecord = Location::select('location_type')->where('location_id', $location)->first();
            if($locTypeRecord != null){
                Session::put('getLocationType', $locTypeRecord->location_type);
            }
        }

        $lc = Location::where('location_id', $location)->pluck('location_name')->first();
        $location_type = Location::select('location_type')->where('location_id',$location)->first();
        if($location_type != null){
            Session::put('getLocationType', $location_type->location_type);
        }
        return $lc;

    }


    // function hasAccess($pageNm,$actionNm){
    //     $request = new Request();
    //     $getLocation =  Session::get('getLocationId');

    //     if($getLocation == null){
    //     $stateManagerLocationId = UserLocation::select('location_id')->where('user_id',Auth::user()->id)->first();
    //     Session::put('getLocationId', $stateManagerLocationId->location_id);

    //     $getLocation =  Session::get('getLocationId');

    //     }

    //     if(!UserAccessController::checkUserAccess($request,Auth::user()->id,$pageNm,$actionNm, $getLocation)){
    //         return false;
    //     }
    //     return true;
    // }


    function hasAccess($pageNm,$actionNm){
        if (!Auth::check()) {
            return false;
        }
        if ($pageNm === 'switch_year' || $pageNm === 'switch-company_year' || $pageNm === 'aerb_documents_summary' || $pageNm === 'offer') {
            return true;
        }
        if (is_array($pageNm)) {
            if (in_array('switch_year', $pageNm) || in_array('switch-company_year', $pageNm) || in_array('aerb_documents_summary', $pageNm)) {
                return true;
            }
        }

        $request = new Request();
        $getLocation =  Session::get('getLocationId');

        if($getLocation == null){
            $getLocation = 1;
            Session::put('getLocationId', $getLocation);
            $locTypeRecord = Location::select('location_type')->where('location_id', $getLocation)->first();
            if($locTypeRecord != null){
                Session::put('getLocationType', $locTypeRecord->location_type);
            }
        }

        if(!UserAccessController::checkUserAccess($request,Auth::user()->id,$pageNm,$actionNm, $getLocation)){
            return false;
        }
        return true;
    }

    function getCurrentLocation(){
        return LocationController::getDefaultLocationData();
    }

    function getOtherLocations(){
        $current_lc = getCurrentLocation()->location_id;
        $getOtherlocation = Location::where('location.location_id', '!=', $current_lc)->get();
        return $getOtherlocation;

    }

    function getPurchaseIndentLocations()
    {
        $currentLocation = getCurrentLocation();
        if ($currentLocation->location_type === 'HO') {
            return Location::all();
        }
        if($currentLocation->location_type !== 'HO'){
            $other =  Location::where('location_type','HO')
            ->get();
            return $other;
        }
    }
    
    function getCurrentHOAndOwnLocation(){
        return LocationController::getCurrentHOAndOwnLocationData();
    }

    function getUsers()
    {
        $user = Admin::select('admin.id','admin.user_name','admin.person_name')
        ->where('status','=','Active')
        ->orderBy('admin.person_name', 'ASC')->get();
        return $user;
    }

    function getJobDescription()
    {
        return JobDescription::select('id','job_description')->orderBy('job_description','asc')->get();
    }
    function getMiJobDescription()
    {
        return JobDescription::with('parts:part_id,job_desc_id,part_no,drg_no')
            ->orderBy('job_description', 'asc')
            ->get();
    }
 
    function getTypeOfJob()
    {
        return TypeOfJob::select('id','type_of_job')->orderBy('type_of_job','asc')->get();
    }

    function getparts()
    {
        return Part::select('part_id','part_no','drg_no','job_desc_id')->orderBy('part_no','asc')->get();
    }

    function getEquipmentUt()
    {
        $location = getCurrentLocation()->location_id;
        return \App\Models\EquipmentUT::select('eu_id', 'name_for_display', 'eu_equipment_name', 'eu_make', 'eu_display', 'eu_serial_no', 'eu_next_cali_due_date')
            ->where('current_location_id', $location)
            ->where('eu_status', 'Active')
            ->orderBy('name_for_display', 'asc')
            ->get();
    }

    if (!function_exists('getRTCamerasByIsotope')) {
        function getRTCamerasByIsotope($isotope, $selected_id = null)
        {
            $LocationData = getCurrentLocation();
            $query = RTCamera::select([
                'rt_camera.rt_camera_id',
                'rt_camera.name_for_display',
                'rt_camera.rt_camera_name',
                'rt_camera.rt_serial_no',
                'rt_camera.rt_isotope',
                'rt_camera.rt_x_ray',
                'rt_camera.rt_focal_spot',
                'rt_camera_details.rtcd_initial_activity_ci',
                'rt_camera_details.rtcd_source_size',
                'rt_camera_details.rtcd_last_of_loading_date',
            ])
            ->leftJoin('rt_camera_details', function ($join) {
                $join->on('rt_camera_details.rtcd_rt_camera_id', '=', 'rt_camera.rt_camera_id')
                    ->whereRaw('rt_camera_details.rtcd_id = (
                        SELECT MAX(rtcd_id) 
                        FROM rt_camera_details 
                        WHERE rtcd_rt_camera_id = rt_camera.rt_camera_id
                    )');
            });

            if ($isotope == 'Ir-192') {
                $query->where('rt_camera.rt_isotope', 'Ir-192');
            } elseif ($isotope == 'Co-60') {
                $query->where('rt_camera.rt_isotope', 'Co-60');
            } elseif ($isotope == 'X-Ray') {
                $query->where('rt_camera.rt_isotope', 'X-Ray');
            }

            $location_id = $LocationData->location_id ?? 0;
            $show_all_location_camera = $LocationData->show_all_location_camera ?? 'No';

            if ($show_all_location_camera === 'Yes') {
                $currentLocationCameraExists = RTCamera::where('rt_isotope', $isotope)
                    ->where('current_location_id', $location_id)
                    ->whereIn('rt_status', ['Active', 'active'])
                    ->exists();

                if ($currentLocationCameraExists) {
                    $query->where(function($q) use ($location_id, $selected_id) {
                        $q->where(function($sub) use ($location_id) {
                            $sub->where('rt_camera.current_location_id', $location_id)
                                ->whereIn('rt_camera.rt_status', ['Active', 'active']);
                        });
                        if (!empty($selected_id)) { 
                            $q->orWhere('rt_camera.rt_camera_id', $selected_id);
                        }
                    });
                } else {
                    $query->where(function($q) use ($selected_id) {
                        $q->whereIn('rt_camera.rt_status', ['Active', 'active']);
                        if (!empty($selected_id)) { 
                            $q->orWhere('rt_camera.rt_camera_id', $selected_id);
                        }
                    });
                }
            } else {
                $query->where(function($q) use ($location_id, $selected_id) {
                    $q->where(function($sub) use ($location_id) {
                        $sub->where('rt_camera.current_location_id', $location_id)
                            ->whereIn('rt_camera.rt_status', ['Active', 'active']);
                    });
                    if (!empty($selected_id)) { 
                        $q->orWhere('rt_camera.rt_camera_id', $selected_id);
                    }
                });
            }

            return $query->orderBy('rt_camera.name_for_display', 'asc')->get();
        }
    }

    function getProbeUt()
    {
        $location = getCurrentLocation()->location_id;
        return \App\Models\ProbeUT::select('pu_id', 'name_for_display', 'pu_probe', 'pu_serial_number', 'pu_size_of_probe', 'pu_ref_angle', 'pu_frequency')
            ->where('current_location_id', $location)
            ->where('pu_status', 'Active')
            ->orderBy('name_for_display', 'asc')
            ->get();
    }

    function getMaterialMpt($selected_id = null)
    {
        $location = getCurrentLocation()->location_id;
        $query = \App\Models\MaterialMpt::select(
            'material_mpt.mm_id', 
            'material_mpt.name_for_display', 
            'material_mpt.mm_material', 
            'material_mpt.mm_material_make', 
            'material_mpt.mm_batch_no', 
            'material_mpt.mm_expiry_date', 
            'mpt_dpt_material_batch_wise_stock.stock_qty'
        )
        ->leftjoin('mpt_dpt_material_batch_wise_stock', 'mpt_dpt_material_batch_wise_stock.sr_table_pk_id', '=', 'material_mpt.mm_id')
        ->where('mpt_dpt_material_batch_wise_stock.sr_table_unique_id', '=', 'mpt_material')
        ->where('mpt_dpt_material_batch_wise_stock.current_location_id', '=', $location);

        if (!empty($selected_id)) {
            $query->where(function($q) use ($selected_id) {
                $q->where('mpt_dpt_material_batch_wise_stock.stock_qty', '>', 0)
                  ->orWhere('material_mpt.mm_id', '=', $selected_id);
            });
        } else {
            $query->where('mpt_dpt_material_batch_wise_stock.stock_qty', '>', 0);
        }

        return $query->orderBy('material_mpt.name_for_display', 'asc')->get();
    }

    function getEquipmentMpt($selected_id = null)
    {
        $location = getCurrentLocation()->location_id;
        return EquipmentMPT::select('em_id', 'name_for_display', 'em_equipment_name', 'em_make', 'em_serial_no', 'em_next_cali_due_date')
            ->where('current_location_id', $location)
            ->where(function ($query) use ($selected_id) {
                $query->where('em_status', 'Active');
                if (!empty($selected_id)) {
                    $query->orWhere('em_id', $selected_id);
                }
            })
            ->orderBy('name_for_display', 'asc')
            ->get();
    }

    function getSAC()
    {
        return GstConfiguration::select('gc_id','gc_sac')->orderBy('gc_sac','asc')->get();
    }

    function getUnit()
    {
        return Unit::select('id','unit')->orderBy('unit','asc')->get();
    }

    function getReasons()
    {
        return Reason::select('id','reason_name')->orderBy('reason_name','asc')->get();
    }

    function getSuppliers()
    {
        return Supplier::select('suppliers.id','suppliers.supplier_name','suppliers.city_id', 'cities.state_id')
        ->leftJoin('cities', 'cities.id', '=', 'suppliers.city_id')
        
        ->orderBy('suppliers.supplier_name','asc')->get();
    }

    function getLocations()
    {
        return Location::select('location_id','location_name')->orderBy('location_name','asc')->get();
    }

    function getGSTBillLocations()
    {
        return Location::select('location_id','location_name','location_state_id')->where('gst_bill_location','=','Yes')->orderBy('location_name','asc')->get();
    }

    function getNotFeasiblereason()
    {
        return Reason::select('id','reason_name')
        ->where('reason_type','Not Feasible')
        ->orderBy('reason_name','asc')->get();
    }


    function getInqSCreason()
    {
        return Reason::select('id','reason_name')
        ->where('reason_type','Inquiry - Quotation Short Close')
        ->orderBy('reason_name','asc')->get();
    }
 
    function getPOSCreason(){
        return Reason::select('id','reason_name')
        ->where('reason_type','PO Short Close')
        ->orderBy('reason_name','asc')->get();
    }
    function getPurchaseIndentSCreason(){
        return Reason::select('id','reason_name')
        ->where('reason_type','Purchase Indent Short Close')
        ->orderBy('reason_name','asc')->get();
    }
    function getWastageReason(){
        return Reason::select('id','reason_name')
        ->where('reason_type','Wastage')
        ->orderBy('reason_name','asc')->get();
    }


    function getReturnSlipReason()
    {
        return Reason::select('id','reason_name')
        ->where('reason_type','Return Slip')
        ->orderBy('reason_name','asc')->get();
    }

    function getCurrentLocationEnclosures()
    {
        $location_id = getCurrentLocation()->location_id;
        return Enclosure::select('enclosure_id', 'enclosure_name')
            ->where('current_location_id', '=', $location_id)
            ->where('enclosure_type_value_fix', '=', 'Enclosure')
            ->orderBy('enclosure_name', 'asc')
            ->get();
    }

    function getShifts()
    {
        return \DB::table('shift')->select('shift_id', 'shift_name')->orderBy('shift_name', 'asc')->get();
    }

    function getItemsForProduction($location_id = null)
    {
        if ($location_id == null) {
            $location_id = getCurrentLocation()->location_id;
        }
        return Item::select(
                'item.id',
                'item.item_name',
                'item.item_group_id',
                'item_group.item_group',
                'item.item_type',
                'unit.unit',
                \DB::raw('IFNULL(item_opening_prod_area.stock_sq_in, 0) as io_stock_qty')
            )
            ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
            ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
            ->leftJoin('item_opening_prod_area', function($join) use ($location_id) {
                $join->on('item_opening_prod_area.item_id', '=', 'item.id')
                     ->where('item_opening_prod_area.current_location_id', '=', $location_id);
            })
            ->where('item.status', 'Active')
            ->whereIn('item.item_type', ['film', 'Industrial X-Ray Films'])
            ->orderBy('item.item_name', 'asc')
            ->get();
    }

    // function getIssueSlipItems()
    // {
    //     return Item::select('item.id','item.item_name','item.item_type','unit.unit','item_opening.io_stock_qty')
    //     ->orderBy('item_name','asc')
    //     // ->where('status', '=', 'Active')
    //     ->whereNotIn('item_type', ['rt_camera', 'film'])
    //     ->leftJoin('unit','unit.id','=','item.unit_id')
    //     ->leftJoin('item_opening','item_opening.io_item_id','=','item.id')
    //     ->get();
    // }

    function getItems()
    {
        return Item::select('item.id','item.item_name','item.unit_id','unit.unit','item.min_stock_level',
        'item.item_type','item_group.item_group')
        ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        // ->leftJoin('item_opening', 'item_opening.io_item_id', '=', 'item.id')
        ->where('item.status','=',"Active")
        ->orderBy('item.item_name','asc')
        ->get();
    }

    // function changeStatusAndLocationForSrNo($sr_table_unique_id,$sr_table_pk_id,$current_status,$previous_status,$new_location_id,$mode) {
    //     // Table mapping
    //     $tableMap = [
    //         'rt_camera' => [
    //             'table' => 'rt_camera',
    //             'pk' => 'rt_camera_id',
    //             'status_column' => 'rt_status',
    //         ],
    //         'ut_equipment' => [
    //             'table' => 'equipment_ut',
    //             'pk' => 'eu_id',
    //             'status_column' => 'eu_status',
    //         ],
    //         'ut_equipment' => [
    //             'table' => 'equipment_ut',
    //             'pk' => 'eu_id',
    //             'status_column' => 'eu_status',
    //         ],
    //     ];

    //     if (!isset($tableMap[$sr_table_unique_id])) {
    //         return false;
    //     }

    //     $tableInfo = $tableMap[$sr_table_unique_id];

    //     // Check existence in transfer table
    //     $exists = DB::table('inter_location_transfer_details')
    //         ->where('sr_table_unique_id', $sr_table_unique_id)
    //         ->where('sr_table_pk_id', $sr_table_pk_id)
    //         ->exists();

    //     if (!$exists) {
    //         return false;
    //     }

    //     if ($mode === 'Insert') {
    //         $statusToUpdate = $current_status;
    //     } elseif ($mode === 'Delete') {
    //         $statusToUpdate = $previous_status;
    //     } else {
    //         return false;
    //     }

    //     // Update query
    //     DB::table($tableInfo['table'])
    //         ->where($tableInfo['pk'], $sr_table_pk_id)
    //         ->update([
    //             'current_location_id' => $new_location_id,
    //             $tableInfo['status_column'] => $statusToUpdate
    //         ]);

    //     return true;
    // }
    // 'mpt_material','mpt_equipment','rt_camera','ut_equipment','dpt_chemical','instrument'

    // OLD Fun 11-08-2026 
    // function changeStatusAndLocationForSrNo($sr_table_unique_id, $sr_table_pk_id, $current_status, $previous_status, $new_location_id, $mode)
    // {
    //     // Table mapping
    //     $tableMap = [
    //         'rt_camera' => [
    //             'table' => 'rt_camera',
    //             'pk' => 'rt_camera_id',
    //             'status_column' => 'rt_status',
    //         ],
    //         'mpt_equipment' => [
    //             'table' => 'equipment_mpt',
    //             'pk' => 'em_id',
    //             'status_column' => 'em_status',
    //         ],
    //         'mpt_material' => [
    //             'table' => 'material_mpt',
    //             'pk' => 'mm_id',
    //             'status_column' => 'mm_status',
    //         ],
    //         'ut_equipment' => [
    //             'table' => 'equipment_ut',
    //             'pk' => 'eu_id',
    //             'status_column' => 'eu_status',
    //         ],
    //         'dpt_chemical' => [
    //             'table' => 'dpt_chemical',
    //             'pk' => 'dpt_id',
    //             'status_column' => 'dpt_status',
    //         ],
    //         'instrument' => [
    //             'table' => 'instrument',
    //             'pk' => 'ins_id',
    //             'status_column' => 'ins_status',
    //         ],
    //         'probe_ut' => [
    //             'table' => 'probe_ut',
    //             'pk' => 'pu_id',
    //             'status_column' => 'pu_status',
    //         ],
    //     ];

    //     $tableInfo = $tableMap[$sr_table_unique_id];

    //     // Check existence in transfer table
    //     // $exists = DB::table('inter_location_transfer_details')
    //     //     ->where('sr_table_unique_id', $sr_table_unique_id)
    //     //     ->where('sr_table_pk_id', $sr_table_pk_id)
    //     //     ->exists();

    //     // if (!$exists) {
    //     //     return ['status' => false, 'message' => 'Record not found in transfer details'];
    //     // }

    //     // Get current status from DB
    //     $currentData = DB::table($tableInfo['table'])
    //         ->where($tableInfo['pk'], $sr_table_pk_id)
    //         ->first();

    //         // dd($currentData->{$tableInfo['status_column']},$previous_status);

    //     // Decide status
    //     if ($mode === 'Insert') {
    //         if($currentData->{$tableInfo['status_column']} != $previous_status){
    //              abort(404,'Sr. No. Is Already Used.');
    //         }
    //         $statusToUpdate = $current_status;
    //     } elseif ($mode === 'Delete') {
    //          if($currentData->{$tableInfo['status_column']} != $current_status){
    //              abort(404,'Sr. No. Is Already Used.');
    //         }
    //         $statusToUpdate = $previous_status;
    //     } else {
    //         return false;
    //     }

       

    //     // if (!$currentData) {
    //     //     return ['status' => false, 'message' => 'Record not found'];
    //     // }

    //     $existingStatus = $currentData->{$tableInfo['status_column']};

    //     // If same status → skip update
    //     if ($existingStatus == $statusToUpdate) {
    //        abort(404,'Sr. No. Is Already ' . $statusToUpdate);
    //     }

    //     // Perform update
    //     DB::table($tableInfo['table'])
    //         ->where($tableInfo['pk'], $sr_table_pk_id)
    //         ->update([
    //             'current_location_id' => $new_location_id,
    //             $tableInfo['status_column'] => $statusToUpdate
    //         ]);

      
    // }


    //      function changeStatusAndLocationForSrNo($sr_table_unique_id, $sr_table_pk_id, $current_status, $previous_status, $new_location_id, $mode, $detailModel = null)
    //     {
    //         // Table mapping
    //         $tableMap = [
    //             'rt_camera' => [
    //                 'table' => 'rt_camera',
    //                 'pk' => 'rt_camera_id',
    //                 'status_column' => 'rt_status',
    //             ],
    //             'mpt_equipment' => [
    //                 'table' => 'equipment_mpt',
    //                 'pk' => 'em_id',
    //                 'status_column' => 'em_status',
    //             ],
    //             'mpt_material' => [
    //                 'table' => 'material_mpt',
    //                 'pk' => 'mm_id',
    //                 'status_column' => 'mm_status',
    //             ],
    //             'ut_equipment' => [
    //                 'table' => 'equipment_ut',
    //                 'pk' => 'eu_id',
    //                 'status_column' => 'eu_status',
    //             ],
    //             'dpt_chemical' => [
    //                 'table' => 'dpt_chemical',
    //                 'pk' => 'dpt_id',
    //                 'status_column' => 'dpt_status',
    //             ],
    //             'instrument' => [
    //                 'table' => 'instrument',
    //                 'pk' => 'ins_id',
    //                 'status_column' => 'ins_status',
    //             ],
    //             'probe_ut' => [
    //                 'table' => 'probe_ut',
    //                 'pk' => 'pu_id',
    //                 'status_column' => 'pu_status',
    //             ],
    //         ];
    //
    //         if (!isset($tableMap[$sr_table_unique_id])) {
    //             return false;
    //         }
    //
    //         $tableInfo = $tableMap[$sr_table_unique_id];
    //
    //         // Get current status from DB
    //         $currentData = DB::table($tableInfo['table'])
    //             ->where($tableInfo['pk'], $sr_table_pk_id)
    //             ->first();
    //
    //         if (!$currentData) {
    //             return false;
    //         }
    //
    //         // Check if status_transaction_id tracking applies
    //         $shouldTrackStatusTxn = false;
    //         if (in_array($sr_table_unique_id, ['rt_camera', 'ut_equipment', 'mpt_equipment'])) {
    //             $shouldTrackStatusTxn = true;
    //         } elseif ($sr_table_unique_id === 'instrument') {
    //             $caliReq = $currentData->ins_cali_req ?? 'No';
    //             if (strtolower($caliReq) === 'yes') {
    //                 $shouldTrackStatusTxn = true;
    //             }
    //         }
    //
    //         $updateData = [];
    //
    //         // Decide status
    //         if ($mode === 'Insert') {
    //             if ($currentData->{$tableInfo['status_column']} != $previous_status) {
    //                 abort(404, 'Sr. No. Is Already Used.');
    //             }
    //             $statusToUpdate = $current_status;
    //
    //             if ($shouldTrackStatusTxn && $detailModel) {
    //                 $oldTxnId = (int)($currentData->status_transaction_id ?? 0);
    //                 $newTxnId = $oldTxnId + 1;
    //
    //                 if (is_object($detailModel)) {
    //                     $detailModel->previous_status_transaction_id = $oldTxnId;
    //                     $detailModel->current_status_transaction_id = $newTxnId;
    //                     $detailModel->save();
    //                 }
    //
    //                 $updateData['status_transaction_id'] = $newTxnId;
    //             }
    //         } elseif ($mode === 'Delete') {
    //             if ($currentData->{$tableInfo['status_column']} != $current_status) {
    //                 abort(404, "Sr. No. Is Already Used.");
    //             }
    //             $statusToUpdate = $previous_status;
    //
    //             if ($shouldTrackStatusTxn && $detailModel) {
    //                 $masterTxnId = (int)($currentData->status_transaction_id ?? 0);
    //                 $detailTxnId = is_object($detailModel) ? (int)($detailModel->current_status_transaction_id ?? 0) : 0;
    //                 if ($masterTxnId > 0 && $detailTxnId > 0 && $masterTxnId != $detailTxnId) {
    //                     abort(404, "This Is Used Somewhere, You Can't Delete");
    //                 }
    //
    //                 $prevTxnId = is_object($detailModel) ? (int)($detailModel->previous_status_transaction_id ?? 0) : 0;
    //                 $updateData['status_transaction_id'] = $prevTxnId;
    //             }
    //         } else {
    //             return false;
    //         }
    //
    //         $existingStatus = $currentData->{$tableInfo['status_column']};
    //
    //         // If same status → skip update
    //         if ($existingStatus == $statusToUpdate) {
    //             abort(404, 'Sr. No. Is Already ' . $statusToUpdate);
    //         }
    //
    //         $updateData['current_location_id'] = $new_location_id;
    //         $updateData[$tableInfo['status_column']] = $statusToUpdate;
    //
    //         // Perform update
    //         DB::table($tableInfo['table'])
    //             ->where($tableInfo['pk'], $sr_table_pk_id)
    //             ->update($updateData);
    //     }

    function changeStatusAndLocationForSrNo($sr_table_unique_id, $sr_table_pk_id, $current_status, $previous_status, $new_location_id, $mode, $detailModel = null)
    {
        // Table mapping
        $tableMap = [
            'rt_camera' => [
                'table' => 'rt_camera',
                'pk' => 'rt_camera_id',
                'status_column' => 'rt_status',
            ],
            'mpt_equipment' => [
                'table' => 'equipment_mpt',
                'pk' => 'em_id',
                'status_column' => 'em_status',
            ],
            'mpt_material' => [
                'table' => 'material_mpt',
                'pk' => 'mm_id',
                'status_column' => 'mm_status',
            ],
            'ut_equipment' => [
                'table' => 'equipment_ut',
                'pk' => 'eu_id',
                'status_column' => 'eu_status',
            ],
            'dpt_chemical' => [
                'table' => 'dpt_chemical',
                'pk' => 'dpt_id',
                'status_column' => 'dpt_status',
            ],
            'instrument' => [
                'table' => 'instrument',
                'pk' => 'ins_id',
                'status_column' => 'ins_status',
            ],
            'probe_ut' => [
                'table' => 'probe_ut',
                'pk' => 'pu_id',
                'status_column' => 'pu_status',
            ],
        ];

        if (!isset($tableMap[$sr_table_unique_id])) {
            return false;
        }

        $tableInfo = $tableMap[$sr_table_unique_id];

        // Get current status from DB
        $currentData = DB::table($tableInfo['table'])
            ->where($tableInfo['pk'], $sr_table_pk_id)
            ->first();

        if (!$currentData) {
            return false;
        }

        // For 'mpt_material' and 'dpt_chemical', status does NOT change, ONLY location changes.
        if (in_array($sr_table_unique_id, ['mpt_material', 'dpt_chemical'])) {
            DB::table($tableInfo['table'])
                ->where($tableInfo['pk'], $sr_table_pk_id)
                ->update(['current_location_id' => $new_location_id]);

            return true;
        }

        // Check if status_transaction_id tracking applies
        $shouldTrackStatusTxn = false;
        if (in_array($sr_table_unique_id, ['rt_camera', 'ut_equipment', 'mpt_equipment', 'probe_ut'])) {
            $shouldTrackStatusTxn = true;
        } elseif ($sr_table_unique_id === 'instrument') {
            $caliReq = $currentData->ins_cali_req ?? 'No';
            if (strtolower($caliReq) === 'yes') {
                $shouldTrackStatusTxn = true;
            }
        }

        $updateData = [];

        // Decide status
        if ($mode === 'Insert') {
            if ($currentData->{$tableInfo['status_column']} != $previous_status) {
                abort(404, 'Sr. No. Is Already Used.');
            }
            $statusToUpdate = $current_status;

            if ($shouldTrackStatusTxn && $detailModel) {
                $oldTxnId = (int)($currentData->status_transaction_id ?? 0);
                $newTxnId = $oldTxnId + 1;

                if (is_object($detailModel)) {
                    $detailModel->previous_status_transaction_id = $oldTxnId;
                    $detailModel->current_status_transaction_id = $newTxnId;
                    $detailModel->save();
                }

                $updateData['status_transaction_id'] = $newTxnId;
            }
        } elseif ($mode === 'Delete') {
            if ($currentData->{$tableInfo['status_column']} != $current_status) {
                abort(404, "Sr. No. Is Already Used.");
            }
            $statusToUpdate = $previous_status;

            if ($shouldTrackStatusTxn && $detailModel) {
                $masterTxnId = (int)($currentData->status_transaction_id ?? 0);
                $detailTxnId = is_object($detailModel) ? (int)($detailModel->current_status_transaction_id ?? 0) : 0;
                if ($masterTxnId > 0 && $detailTxnId > 0 && $masterTxnId != $detailTxnId) {
                    abort(404, "This Is Used Somewhere, You Can't Delete");
                }

                $prevTxnId = is_object($detailModel) ? (int)($detailModel->previous_status_transaction_id ?? 0) : 0;
                $updateData['status_transaction_id'] = $prevTxnId;
            }
        } else {
            return false;
        }

        $existingStatus = $currentData->{$tableInfo['status_column']};

        // If same status → skip update
        if ($existingStatus == $statusToUpdate) {
            abort(404, 'Sr. No. Is Already ' . $statusToUpdate);
        }

        $updateData['current_location_id'] = $new_location_id;
        $updateData[$tableInfo['status_column']] = $statusToUpdate;

        // Perform update
        DB::table($tableInfo['table'])
            ->where($tableInfo['pk'], $sr_table_pk_id)
            ->update($updateData);
    }

    function getItemsForInterLocationTransfer()
    {
        $locationCode = getCurrentLocation();
        return Item::select('item.id','item.item_name','item.unit_id','unit.unit','item.min_stock_level',
        'item.item_type','item_group.item_group', 'item_opening.io_stock_rate_unit',
        \DB::raw('IFNULL(item_opening_prod_area.stock_sq_in, 0) as stock_sq_in'),
        \DB::raw('
            CASE 
                WHEN item.item_type = "dpt_chemical" THEN (
                    SELECT IFNULL(SUM(stk.stock_qty), 0)
                    FROM mpt_dpt_material_batch_wise_stock stk
                    JOIN dpt_chemical dc ON dc.dpt_id = stk.sr_table_pk_id
                    WHERE stk.sr_table_unique_id = "dpt_chemical"
                      AND stk.current_location_id = ' . (int)($locationCode->location_id ?? 0) . '
                      AND dc.item_id = item.id
                )
                WHEN item.item_type = "mpt_material" THEN (
                    SELECT IFNULL(SUM(stk.stock_qty), 0)
                    FROM mpt_dpt_material_batch_wise_stock stk
                    JOIN material_mpt mm ON mm.mm_id = stk.sr_table_pk_id
                    WHERE stk.sr_table_unique_id = "mpt_material"
                      AND stk.current_location_id = ' . (int)($locationCode->location_id ?? 0) . '
                      AND mm.item_id = item.id
                )
                ELSE IFNULL(item_opening.io_stock_qty, 0)
            END as io_stock_qty
        ')
        )
        ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        ->leftJoin('item_opening', function($join) use ($locationCode) {
            $join->on('item_opening.io_item_id', '=', 'item.id')
                ->where('item_opening.current_location_id', '=', $locationCode->location_id);
        })
        ->leftJoin('item_opening_prod_area', function ($join) use ($locationCode) {
            $join->on('item_opening_prod_area.item_id', '=', 'item.id')
                ->where('item_opening_prod_area.current_location_id', '=', $locationCode->location_id);
        })
        ->where(function($query) {
            $query->where('item.status', '=', 'Active')
                  ->orWhereIn('item.item_type', ['dpt_chemical', 'mpt_material']);
        })
        ->where('item.inter_location_transfer','=',"Allowed")
        ->orderBy('item.item_name','asc')
        ->get();
    }

    function getSrNo($main_group, $item_id){
        $locationCode = getCurrentLocation();

        if (in_array($main_group, ['dpt_chemical', 'mpt_material'])) {
            $sr_no_data = DB::table('item_sr_no_name_for_display as sr_no')
                ->join('mpt_dpt_material_batch_wise_stock as stk', function($join) use ($locationCode) {
                    $join->on('stk.sr_table_unique_id', '=', 'sr_no.sr_table_unique_id')
                         ->on('stk.sr_table_pk_id', '=', 'sr_no.sr_table_pk_id')
                         ->where('stk.current_location_id', '=', $locationCode->location_id);
                })
                ->where("sr_no.sr_table_unique_id", $main_group)
                ->where('sr_no.item_id', $item_id)
                ->where('stk.stock_qty', '>', 0)
                ->get([
                    'sr_no.name_for_display',
                    'sr_no.sr_table_pk_id',
                    'sr_no.sr_table_unique_id',
                    'stk.stock_qty as sr_qty',
                    'stk.current_location_id'
                ]);
        } else {
            $sr_no_data = DB::table('item_sr_no_name_for_display as sr_no')
                ->where("sr_no.sr_table_unique_id", $main_group)
                ->where('sr_no.status', 'Active')
                ->where('sr_no.current_location_id', $locationCode->location_id)
                ->where('sr_no.item_id', $item_id)
                ->get([
                    'sr_no.name_for_display',
                    'sr_no.sr_table_pk_id',
                    'sr_no.sr_table_unique_id',
                    DB::raw('1 as sr_qty'),
                    'sr_no.current_location_id'
                ]);
        }

        foreach ($sr_no_data as $row) {
            $cali_req = 'Yes';
            if ($row->sr_table_unique_id === 'instrument') {
                $cali_req = DB::table('instrument')
                    ->where('ins_id', $row->sr_table_pk_id)
                    ->value('ins_cali_req') ?? 'No';
            }
            $row->calibration_required = $cali_req;
        }
        return $sr_no_data;
    }

    function getItemsForPI()
    {
        $locationCode = getCurrentLocation();
        return Item::select('item.id','item.item_name','item.unit_id','unit.unit','item.min_stock_level',
        'item.item_type','item_group.item_group','item_opening.io_stock_qty')
        ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        ->leftJoin('item_opening', function($join) use ($locationCode) {
            $join->on('item_opening.io_item_id', '=', 'item.id')
                ->where('item_opening.current_location_id', '=', $locationCode->location_id);
        })
        ->where('item.status','=',"Active")
        ->orderBy('item.item_name','asc')
        ->get();
    }

    // function getItemsForSupDC($piType = null)
    // {
    //     $locationCode = getCurrentLocation();

    //     $query = Item::select(
    //             'item.id',
    //             'item.item_name',
    //             'item.unit_id',
    //             'unit.unit',
    //             'item.min_stock_level',
    //             'item.item_type',
    //             'item_group.item_group',
    //             'item_opening.io_stock_qty'
    //         )
    //         ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
    //         ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
    //         ->leftJoin('item_opening', function ($join) use ($locationCode) {
    //             $join->on('item_opening.io_item_id', '=', 'item.id')
    //                 ->where('item_opening.current_location_id', '=', $locationCode->location_id);
    //         })
    //         ->where('item.status', 'Active');

    //     // Dynamic filter
    //     if ($piType == 'Non Returnable - Manual') {
    //         $query->whereIn('item.item_type', [
    //             'general',
    //             'film',
    //         ]);
    //     }

    //     if ($piType == 'Returnable - Manual') {
    //         $query->whereIn('item.item_type', [
    //             'rt_camera',
    //             'mpt_equipment',
    //             'ut_equipment',
    //             'instrument',
    //             'probe_ut'
    //         ]);
    //     }

    //     // Returnable - Service PO -> no filter (all items)

    //     return $query->orderBy('item.item_name', 'asc')->get();
    // }

    function getItemsForSupDC($piType = null)
    {
        $locationCode = getCurrentLocation();

        $query = Item::select(
                'item.id',
                'item.item_name',
                'item.unit_id',
                'unit.unit',
                'item.min_stock_level',
                'item.item_type',
                'item_group.item_group',
                'item_opening.io_stock_qty',
                \DB::raw('IFNULL(item_opening_prod_area.stock_sq_in, 0) as stock_sq_in')
            )
            ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
            ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
            ->leftJoin('item_opening', function ($join) use ($locationCode) {
                $join->on('item_opening.io_item_id', '=', 'item.id')
                    ->where('item_opening.current_location_id', '=', $locationCode->location_id);
            })
            ->leftJoin('item_opening_prod_area', function ($join) use ($locationCode) {
                $join->on('item_opening_prod_area.item_id', '=', 'item.id')
                    ->where('item_opening_prod_area.current_location_id', '=', $locationCode->location_id);
            })
            ->where('item.status', 'Active');

        // Dynamic filter
        if ($piType == 'Non Returnable - Manual') {
            $query->whereIn('item.item_type', [
                'general',
                'film',
            ]);
        }

        if ($piType == 'SQIN from Prod. Area') {
            $query->where('item.item_type', 'film');
        }

        if ($piType == 'Returnable - Manual') {
            $query->whereIn('item.item_type', [
                'rt_camera',
                'mpt_equipment',
                'ut_equipment',
                'instrument',
                'probe_ut'

            ]);
        }

        // Returnable - Service PO -> no filter (all items)

        return $query->orderBy('item.item_name', 'asc')->get();
    }
    function getItemsForServicePO()
    {
        $locationCode = getCurrentLocation();
        return Item::select('item.id','item.item_name','item.unit_id','unit.unit','item.min_stock_level',
        'item.item_type','item_group.item_group','item_opening.io_stock_qty')
        ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        ->leftJoin('item_opening', function($join) use ($locationCode) {
            $join->on('item_opening.io_item_id', '=', 'item.id')
                ->where('item_opening.current_location_id', '=', $locationCode->location_id);
        })
        ->where('item.status','=',"Active")
        ->whereNotIN('item.item_type',['film','general','dpt_chemical','mpt_material','probe_ut'])
        ->orderBy('item.item_name','asc')
        ->get();
    }

    function getItemsForDC()
    {
        $locationCode = getCurrentLocation();
        return Item::select('item.id','item.item_name','item.unit_id','unit.unit','item.min_stock_level',
        'item.item_type','item_group.item_group','item_opening.io_stock_qty', 'item_opening.io_stock_rate_unit')
        ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        ->leftJoin('item_opening', function($join) use ($locationCode) {
            $join->on('item_opening.io_item_id', '=', 'item.id')
                ->where('item_opening.current_location_id', '=', $locationCode->location_id);
        })
        ->where('item.status','=',"Active")
        ->where('item.inter_location_transfer','=',"Allowed")
        ->orderBy('item.item_name','asc')
        ->get();
    }


    function getItemsForIssue()
    {
        $locationCode = getCurrentLocation();
        return Item::select('item.id','item.item_name','item.unit_id','unit.unit','item.min_stock_level',
        'item.item_type','item.conv_factor','item_group.item_group','item_opening.io_stock_qty')
        ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        ->leftJoin('item_opening', function($join) use ($locationCode) {
            $join->on('item_opening.io_item_id', '=', 'item.id')
                ->where('item_opening.current_location_id', '=', $locationCode->location_id);
        })
        ->where('item.status','=',"Active")
        ->orderBy('item.item_name','asc')
        ->get();
    }

    // function getItemsForPI($item_id = null)
    // {
    //     $locationCode = getCurrentLocation();

    //     return Item::select(
    //             'item.id','item.item_name','item.unit_id','unit.unit',
    //             'item.min_stock_level','item.item_type',
    //             'item_group.item_group','item_opening.io_stock_qty'
    //         )
    //         ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
    //         ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
    //         ->leftJoin('item_opening', function($join) use ($locationCode) {
    //             $join->on('item_opening.io_item_id', '=', 'item.id')
    //                 ->where('item_opening.current_location_id', '=', $locationCode->location_id);
    //         })
    //         ->where(function($q) use ($item_id) {
    //             $q->where('item.status', 'Active');

    //             if ($item_id !== null) {
    //                 $q->orWhere('item.id', $item_id);
    //             }
    //         })
    //         ->orderBy('item.item_name', 'asc')
    //         ->get();
    // }
    function getRTCameraItems()
    {
        return Item::select('item.id','item.item_name','item.item_type')
        ->orderBy('item_name','asc')
        ->where('item_type','=',"rt_camera")
        // ->where('status','=',"Active")
        ->get();
    }

    function getLPTChemicalItems()
    {
        return Item::select('item.id','item.item_name','item.item_type')
        ->orderBy('item_name','asc')
        ->where('item_type','=',"lpt_chemical")
        // ->where('status','=',"Active")
        ->get();
    }

    function getProbeUtItems()
    {
        return Item::select('item.id','item.item_name')->orderBy('item_name','asc')
        // ->where('status','=',"Active")
        ->where('item_type','=','ut_probe')
        ->get();
    }

    function getUtEquipmentItems()
    {
        return Item::select('item.id','item.item_name')->orderBy('item_name','asc')
        // ->where('status','=',"Active")
        ->where('item_type','=','ut_equipment')
        ->get();
    }

    function getMaterialMptItems()
    {
        return Item::select('item.id','item.item_name')
        ->orderBy('item_name','asc')
        ->where('item_type','=','mpt_chemical')
        // ->where('status','=',"Active")
        ->get();
    }
    
    function getMPTEquipmentItems()
    {
        return Item::select('item.id','item.item_name')->orderBy('item_name','asc')
        // ->where('status','=',"Active")
        ->where('item_type','=','mpt_equipment')
        ->get();
    }
 

    // $detailsTable = table_name (edit table)
    // $itemColumn = edit table item id
    // $recordId  = edit table id
    // $itemType = item no type
    // $recordColumn = edit table id

    // function getDeactiveItems($detailsTable, $itemColumn, $recordId = null, $itemType = null, $recordColumn = null)
    // {
    //     return Item::select('item.id', 'item.item_name', 'item.status')
    //         ->when($itemType, fn($q) => $q->where('item.item_type', $itemType))
    //         ->orderBy('item.item_name')
    //         ->where(function ($q) use ($detailsTable, $itemColumn, $recordId, $recordColumn) {
    //             // Always include Active item
    //             $q->where('item.status', 'Active');
    //             // Include deactivated item if used in this record
    //             if ($recordId && $recordColumn) {
    //                 $q->orWhereExists(function ($sub) use ($detailsTable, $itemColumn, $recordId, $recordColumn) {
    //                     $sub->select(DB::raw(1))
    //                         ->from($detailsTable)
    //                         ->whereColumn("$detailsTable.$itemColumn", 'item.id')
    //                         ->where("$detailsTable.$recordColumn", $recordId);
    //                 });
    //             }
    //         })
    //         ->get();
    // }


    function getItemGroups()
    {
        return ItemGroup::select('id','item_group','item_type','identification_req')->orderBy('item_group','asc')->get();
    }

    function isActivePage($Page = null,$actions = array(),$noactions = false,$postfix = "web",$divider = "-")
    {
        if($Page == null)
        {
            return false;
        }

        if(empty($actions))
        {
            $actions = [
                'add',
                'edit',
                'manage'
            ];
        }

        if($noactions == false)
        {
            foreach ($actions as $action)
            {
                if(Route::currentRouteName() == $action.$divider.$Page.$divider.$postfix || Route::currentRouteName() == $action.$divider.$Page)
                {
                    return true;
                }
            }
            return false;
        }
        else
        {
            if(Route::currentRouteName() == $Page)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }
    // old applynumber function
    // function applynumber($query, $keyword, $column)
    // {
    //     $keyword = trim($keyword);
    //     if ($keyword === '') return;
    //     if (ctype_digit($keyword)) {
    //         $query->whereRaw("$column LIKE ?", ["{$keyword}%"]);
    //         return;
    //     }
    //     $query->whereRaw("$column LIKE ?", ["%{$keyword}%"]);
    // }

    // new applynumber function  dc
    function applynumber($query, $keyword, $column)
    {
        $k = strtolower(trim($keyword));
        if (!$k) return;

        // Current Financial Year (25-26 format)
        $year = (int) date('y');
        $next_year = sprintf('%02d', ($year + 1) % 100);
        $current_fin_year = $year . '-' . $next_year;

        $prefix = $k;
        $year_part = '';

        // Split prefix/year if slash exists
        if (str_contains($k, '/')) {
            $parts = explode('/', $k, 2);
            $prefix = $parts[0];
            $year_part = $parts[1];
        }

        // Prefix must be numeric
        if (!preg_match('/^[0-9]+$/', $prefix)) {
            $query->whereRaw('1=0');
            return;
        }

        // Validate year format (25-26)
        $is_strict_format = preg_match('/^[0-9]{2}-[0-9]{2}$/', $year_part);

        if ($year_part !== '') {
            if (!$is_strict_format) {
                $query->whereRaw("LOWER($column) LIKE ?", ["%$k%"]);
                return;
            }

            if ($year_part !== $current_fin_year) {
                $query->whereRaw('1=0');
                return;
            }
        }

        // Extract prefix part before slash
        $prefix_column = "SUBSTRING_INDEX($column, '/', 1)";

        $query->where(function ($x) use ($prefix, $k, $prefix_column, $column) {
            $x->whereRaw("LOWER($prefix_column) LIKE ?", ["%$prefix%"]);
            $x->orWhereRaw("LOWER($column) = ?", [$k]);

            if (is_numeric($prefix)) {
                $x->orWhereRaw("CAST($prefix_column AS UNSIGNED) = ?", [(int)$prefix]);
            }
        });
    }

    function applynumberprefix($query, $keyword, $column)
    {
        $keyword = strtolower(trim($keyword));
        if ($keyword === '') return;
 
        $year = (int) date('y');
        $next_year = sprintf('%02d', ($year + 1) % 100);
        $current_fin_year = $year . '-' . $next_year;
 
        if (ctype_digit($keyword)) {
 
            // Convert keyword to left-padded sequence (4 digit)
            $seq = str_pad($keyword, 4, '0', STR_PAD_LEFT);
 
            // Match only sequence block → /0001/ style
            $query->whereRaw("LOWER($column) LIKE ?", ["%/$seq/%"]);
 
            return;
        }
        if (preg_match('/^\/([0-9]+)$/', $keyword, $m)) {
 
            $seq = str_pad($m[1], 4, '0', STR_PAD_LEFT);
 
            $query->whereRaw("LOWER($column) LIKE ?", ["%/$seq/%"]);
 
            return;
        }
        $prefix = $keyword;
        $year_part = '';
 
        if (str_contains($keyword, '/')) {
            $parts = explode('/', $keyword, 3);
            $prefix = strtolower($parts[0] . '/' . $parts[1]);
            $year_part = $parts[2] ?? '';
        }
 
        if ($year_part && !preg_match('/^[0-9]{2}-[0-9]{2}$/', $year_part)) {
            $query->whereRaw("LOWER($column) LIKE ?", ["%$keyword%"]);
            return;
        }
 
        if ($year_part && $year_part !== $current_fin_year) {
            $query->whereRaw("1=0");
            return;
        }
        $query->where(function ($x) use ($prefix, $keyword, $column) {
            $x->whereRaw("LOWER($column) LIKE ?", ["%$prefix%"])
            ->orWhereRaw("LOWER($column) = ?", [$keyword]);
        });
    }

    // function applyDate($query, $keyword, $column)
    // {
    //     $keyword = strtolower(trim($keyword));
    //     if ($keyword === '') return;
    //     $normalized = str_replace('/', '-', $keyword);

    //     if(ctype_digit($keyword))
    //     {
    //         $day = str_pad($keyword, 2, '0', STR_PAD_LEFT);
    //         $query->whereRaw("DATE_FORMAT($column, '%d') = ?", [$day]);
    //         return;
    //     }

    //     if(preg_match('/^\/([0-9]{1,2})$/', $keyword, $m))
    //     {
    //         $month = str_pad($m[1], 2, '0', STR_PAD_LEFT);
    //         $query->whereRaw("DATE_FORMAT($column, '%m') = ?", [$month]);
    //         return;
    //     }

    //     if(preg_match('/^[0-9]{2}-[0-9]{2}$/', $normalized))
    //     {
    //         list($day, $month) = explode('-', $normalized);
    //         $query->whereRaw("DATE_FORMAT($column, '%d-%m') = ?", ["$day-$month"]);
    //         return;
    //     }

    //     if(preg_match('/^[0-9]{2}-[0-9]{2}-[0-9]{4}$/', $normalized))
    //     {
    //         $dt = Carbon::createFromFormat('d-m-Y', $normalized)->format('Y-m-d');
    //         $query->whereDate($column, $dt);
    //         return;
    //     }

    //     if(preg_match('/^[0-9]{2}-[0-9]{4}$/', $normalized))
    //     {
    //         list($month, $year) = explode('-', $normalized);
    //         $query->whereYear($column, $year)->whereMonth($column, $month);
    //         return;
    //     }

    //     $query->whereRaw("DATE_FORMAT($column, '%d-%m-%Y') LIKE ?", ["%$keyword%"]);
    // }

    function applySequenceSearch($query, $keyword, $column)
    {
        $keyword = strtolower(trim($keyword));
        if ($keyword === '') return;

        // Example format:
        // Ultratech/01/PI/0004/26-27

        // Split parts
        $parts = explode('/', $keyword);

        $prefix      = $parts[0] ?? null; // Ultratech
        $location    = $parts[1] ?? null; // 01
        $type        = $parts[2] ?? null; // PI
        $sequence    = $parts[3] ?? null; // 0004
        $year_part   = $parts[4] ?? null; // 26-27

        // 👉 ONLY NUMBER SEARCH (e.g. 4 or 0004)
        if (ctype_digit($keyword)) {
            $seq = str_pad($keyword, 4, '0', STR_PAD_LEFT);
            $query->whereRaw("LOWER($column) LIKE ?", ["%/$seq/%"]);
            return;
        }

        // 👉 /0004 type search
        if (preg_match('/^\/([0-9]+)$/', $keyword, $m)) {
            $seq = str_pad($m[1], 4, '0', STR_PAD_LEFT);
            $query->whereRaw("LOWER($column) LIKE ?", ["%/$seq/%"]);
            return;
        }

        // 👉 FULL FORMAT SEARCH
        $query->where(function ($q) use ($prefix, $location, $type, $sequence, $year_part, $column, $keyword) {

            if ($prefix) {
                $q->whereRaw("LOWER($column) LIKE ?", ["%$prefix%"]);
            }

            if ($location && ctype_digit($location)) {
                $loc = str_pad($location, 2, '0', STR_PAD_LEFT);
                $q->whereRaw("LOWER($column) LIKE ?", ["%/$loc/%"]);
            }

            if ($type) {
                $q->whereRaw("LOWER($column) LIKE ?", ["%/$type/%"]);
            }

            if ($sequence && ctype_digit($sequence)) {
                $seq = str_pad($sequence, 4, '0', STR_PAD_LEFT);
                $q->whereRaw("LOWER($column) LIKE ?", ["%/$seq/%"]);
            }

            if ($year_part && preg_match('/^[0-9]{2}-[0-9]{2}$/', $year_part)) {
                $q->whereRaw("LOWER($column) LIKE ?", ["%/$year_part"]);
            }

            // fallback (partial search)
            $q->orWhereRaw("LOWER($column) LIKE ?", ["%$keyword%"]);
        });
    }
    function applyDate($query, $keyword, $column)
    {
        $input = trim($keyword);
        if ($input === '') return;
        $normalized = str_replace('/', '-', $input);
        if (preg_match('/^\d{4}$/', $input)) {
            $year = (int) $input;
            if ($year >= 1000 && $year <= date('Y') + 5) { // dynamic upper limit
                $query->whereYear($column, $year);
                return;
            }
        }
        if (preg_match('/^\d{1,2}(\/\d{1,2})?(\/\d{4})?$/', $input)) {
            $parts = explode('/', $input);

            if (!empty($parts[0])) $query->whereDay($column, (int)$parts[0]);
            if (!empty($parts[1])) $query->whereMonth($column, (int)$parts[1]);
            if (!empty($parts[2])) $query->whereYear($column, (int)$parts[2]);

            return;
        }
        if (preg_match('/^\/\d{1,2}$/', $input)) {
            $month = (int) ltrim($input, '/');
            if ($month >= 1 && $month <= 12) {
                $query->whereMonth($column, $month);
            }
            return;
        }
        if (ctype_digit($input) && $input >= 1 && $input <= 31) {
            $query->whereDay($column, (int)$input);
            return;
        }
        $query->whereRaw("DATE_FORMAT($column, '%d/%m/%Y') LIKE ?", ["%$input%"]);
    }

    function applyCommonCreatedLastOnColumnsFilter($dataTable, $tableName)
    {
        return $dataTable
        ->editColumn('created_by', function ($row) {
            if ($row->created_by) {
                $created_by = Admin::find($row->created_by);
                return $created_by?->user_name ?? '';
            }
            return '';
        })
        ->filterColumn('created_by', function ($query, $keyword) use ($tableName) {
            $query->whereIn("{$tableName}.created_by", function ($sub) use ($keyword) {
                $sub->select('id')
                    ->from('admin')
                    ->where('user_name', 'like', "%{$keyword}%");
            });
        })
        ->editColumn('last_by', function ($row) {
            if ($row->last_by) {
                $last_by = Admin::find($row->last_by);
                return $last_by?->user_name ?? '';
            }
            return '';
        })
        ->filterColumn('last_by', function ($query, $keyword) use ($tableName) {
            $query->whereIn("{$tableName}.last_by", function ($sub) use ($keyword) {
                $sub->select('id')
                    ->from('admin')
                    ->where('user_name', 'like', "%{$keyword}%");
            });
        })
        ->editColumn('created_on', function ($row) {
            if ($row->created_on) {
                return Date::createFromFormat('Y-m-d H:i:s', $row->created_on)
                    ->format(DATE_TIME_FORMAT);
            }
            return '';
        })
        ->filterColumn("{$tableName}.created_on", function ($q, $k) use ($tableName) {
            $input = trim($k);
            if ($input === '') return;

            $clean = preg_replace('/[^0-9\-]/', '', $input);
            $parts = array_filter(explode('-', $clean));
            $is_date_format = $clean === $input && count($parts) <= 3 && count($parts) > 0;
            
            if ($is_date_format) {
                $sql = [];
                $bind = [];
                $isValidDatePart = true;
                if (!empty($parts[0])) {
                    $d = (int)$parts[0];
                    if ($d >= 1 && $d <= 31) {
                        $sql[] = "DAY({$tableName}.created_on)=?";
                        $bind[] = $d;
                    } else {
                        $isValidDatePart = false;
                    }
                }

                if (!empty($parts[1])) {
                    $m = (int)$parts[1];
                    if ($m >= 1 && $m <= 12) {
                        $sql[] = "MONTH({$tableName}.created_on)=?";
                        $bind[] = $m;
                    } else {
                        $isValidDatePart = false;
                    }
                }

                if (!empty($parts[2])) {
                    $y = (int)$parts[2];
                    if (preg_match('/^\d{4}$/', $parts[2]) && $y >= 1900 && $y <= 2100) {
                        $sql[] = "YEAR({$tableName}.created_on)=?";
                        $bind[] = $y;
                    } else {
                        $isValidDatePart = false;
                    }
                }

                if ($sql && $isValidDatePart) {
                    $q->whereRaw(implode(' AND ', $sql), $bind);
                } else {
                    $q->whereRaw("DATE_FORMAT({$tableName}.created_on, '%d-%m-%Y') LIKE ?", ["%{$input}%"]);
                }
            } 
            else {
                $q->whereRaw("DATE_FORMAT({$tableName}.created_on, '%d-%m-%Y') LIKE ?", ["%{$input}%"]);
            }
        })
        ->editColumn('last_on', function ($row) {
            if ($row->last_on) {
                return Date::createFromFormat('Y-m-d H:i:s', $row->last_on)
                    ->format(DATE_TIME_FORMAT);
            }
            return '';
        })
        ->filterColumn("{$tableName}.last_on", function ($q, $k) use ($tableName) {
            $input = trim($k);
            if ($input === '') return;

            $clean = preg_replace('/[^0-9\-]/', '', $input);
            $parts = array_filter(explode('-', $clean));
            $is_date_format = $clean === $input && count($parts) <= 3 && count($parts) > 0;
            
            if ($is_date_format) {
                $sql = []; 
                $bind = [];
                $isValidDatePart = true;
                if (!empty($parts[0])) {
                    $d = (int)$parts[0];
                    if ($d >= 1 && $d <= 31) {
                        $sql[] = "DAY({$tableName}.last_on)=?";
                        $bind[] = $d;
                    } else {
                        $isValidDatePart = false;
                    }
                }

                if (!empty($parts[1])) {
                    $m = (int)$parts[1];
                    if ($m >= 1 && $m <= 12) {
                        $sql[] = "MONTH({$tableName}.last_on)=?";
                        $bind[] = $m;
                    } else {
                        $isValidDatePart = false;
                    }
                }

                if (!empty($parts[2])) {
                    $y = (int)$parts[2];
                    if (preg_match('/^\d{4}$/', $parts[2]) && $y >= 1900 && $y <= 2100) {
                        $sql[] = "YEAR({$tableName}.last_on)=?";
                        $bind[] = $y;
                    } else {
                        $isValidDatePart = false;
                    }
                }

                if ($sql && $isValidDatePart) {
                    $q->whereRaw(implode(' AND ', $sql), $bind);
                } else {
                    $q->whereRaw("DATE_FORMAT({$tableName}.last_on, '%d-%m-%Y') LIKE ?", ["%{$input}%"]);
                }
            }
            else {
                $q->whereRaw("DATE_FORMAT({$tableName}.last_on, '%d-%m-%Y') LIKE ?", ["%{$input}%"]);
            }
        });
    }

    function getAllTestMethod()
    {
        return [
            'RT' => "RT",
            'UT' => "UT",
            'MPT' => "MPT",
            // 'LPT' =>  "LPT",
            'DPT' =>  "DPT",
            'ET' => "ET",
            'VT' => "VT",
            'PAUT' => "PAUT",
            'UTT' => "UTT",
        ];
    }

    function getItemType()
    {
        // return [
        //     // 'film' => "Film",
        //     // 'general' => "General",
        //     // 'lpt_chemical' => "LPT Chemical",
        //     // 'mpt_chemical' =>  "MPT Chemical",
        //     // 'mpt_equipment' => "MPT Equipment",
        //     // 'rt_camera' => "RT Camera",
        //     // 'ut_equipment' => "UT Equipment",
        //     // 'ut_probe' => "UT Probe",
        //     'film' => "Industrial X-Ray Films",
        //     'general' => "General",
        //     'rt_camera' => 'IRED',
        //     'mpt_equipment' => 'Equipment – MPT',
        //     'mpt_material' => 'Material – MPT',
        //     'dpt_chemical' => 'Chemical – DPT' ,
        //     'ut_equipment' => "Equipment – UT",
        //     'instrument' => 'Instrument',
            
        // ];
        return config('app.item_type');
    }

    // function getItemForIssueSlip()
    // {
    //     return [
    //         'film' => "film",
    //         'general' => "general",
    //         'lpt_chemical' => "lpt_chemical",
    //         'mpt_chemical' =>  "mpt_chemical",
    //         'mpt_equipment' => "mpt_equipment",
    //         'rt_camera' => "rt_camera",
    //         'ut_equipment' => "ut_equipment",
    //         'ut_probe' => "ut_probe",
    //     ];
    // }
    function getDecimalPlace()
    {
        return [
            '0' => "0",
            '2' => "2",
            '3' => "3",
        ];
    }

    function getReasonType()
    {
        return [
            'Not Feasible' => "Not Feasible",
            'Inquiry - Quotation Short Close' => "Inquiry - Quotation Short Close",
            // 'Material Inspection' => "Material Inspection",
            // 'Return Slip' => "Return Slip",
            // 'Order Short Close' => "Order Short Close",
            'PO Short Close' => "PO Short Close",
            'Material Indent Short Close' => "Material Indent Short Close",
            'Wastage' => "Wastage",
        ];
    }

    function getResponseMessage($mode=null)
    {
        $messages = [
            'store_success'  => 'Record Inserted.',
            'store_error'    => 'Record Not Inserted.',
            'update_success' => 'Record Updated.',
            'update_error'   => 'Record Not Updated.',
            'delete_success' => 'Record Deleted.',
            'delete_error'   => 'Record Not Deleted.',
        ];

        return $messages[$mode] ?? '';
    }


/*
// OLD CODE (COMMENTED OUT):
// function stockEffect($location,$curItem,$preItem,$curQty,$preQty,$mode,$type){
function stockEffect_old($location,$curItem,$preItem,$curQty,$preQty,$curAmount,$preAmount,$mode,$type,$section,$form_id){
    // dd($location,$curItem,$preItem,$curQty,$preQty,$curAmount,$preAmount,$mode,$type,$section,$form_id);

    $locationCode = getCurrentLocation();

    if($locationCode->location_id == $location){
        if($location != 0 && $curItem !=0 && $preItem !=0){
            if($type == 'U'){
                if($curItem == $preItem){
                    increaseStockQty($location,$curItem,$curQty,$preQty,$mode,$section,$form_id,$preItem,$curItem,$curAmount,$preAmount);               
                }else{
                    increaseStockQty($location,$preItem,$curQty,$preQty,'Delete',$section,$form_id,$preItem,$curItem,$curAmount,$preAmount);                 

                    increaseStockQty($location,$curItem,$curQty,$preQty,'Insert',$section,$form_id,$preItem,$curItem,$curAmount,$preAmount);                  
                }
            }elseif($type == 'D'){
                if($curItem == $preItem){
                    decreaseStockQty($location,$curItem,$curQty,$preQty,$mode,$section,$form_id,$preItem,$curItem,$curAmount,$preAmount);

                }else{
                    decreaseStockQty($location,$preItem,$curQty,$preQty,'Delete',$section,$form_id,$preItem,$curItem,$curAmount,$preAmount);                 

                    decreaseStockQty($location,$curItem,$curQty,$preQty,'Insert',$section,$form_id,$preItem,$curItem,$curAmount,$preAmount);
                   
                }
            }
        }else{
            if($location == 0){
                abort(404,'Invalid Location Code');
            }elseif($curItem == 0){
                abort(404,'Invalid Item');
            }elseif($preItem == 0){
                abort(404,'Invalid Item');
            }
        }
    }else{

            abort(404,'Location Code Mismatched');
    }

}
*/

// Enhanced stockEffect function with optional batch stock parameters
function stockEffect($location, $curItem, $preItem, $curQty, $preQty, $curAmount, $preAmount, $mode, $type, $section, $form_id, $sr_table_unique_id = null, $sr_table_pk_id = null, $only_batch_stock = false) {
    // Direct Entry Case (DPT Chemical / MPT Material Module)
    if ($only_batch_stock && !empty($sr_table_unique_id) && !empty($sr_table_pk_id)) {
        mptDptMaterialBatchWiseStockEffect($location, $sr_table_unique_id, $sr_table_pk_id, $curQty, $preQty, $mode, $type);
        return;
    }

    $locationCode = getCurrentLocation();

    if ($locationCode->location_id == $location) {
        if ($location != 0 && $curItem != 0 && $preItem != 0) {
            if ($type == 'U') {
                if ($curItem == $preItem) {
                    increaseStockQty($location, $curItem, $curQty, $preQty, $mode, $section, $form_id, $preItem, $curItem, $curAmount, $preAmount);
                } else {
                    increaseStockQty($location, $preItem, $curQty, $preQty, 'Delete', $section, $form_id, $preItem, $curItem, $curAmount, $preAmount);
                    increaseStockQty($location, $curItem, $curQty, $preQty, 'Insert', $section, $form_id, $preItem, $curItem, $curAmount, $preAmount);
                }
            } elseif ($type == 'D') {
                if ($curItem == $preItem) {
                    decreaseStockQty($location, $curItem, $curQty, $preQty, $mode, $section, $form_id, $preItem, $curItem, $curAmount, $preAmount);
                } else {
                    decreaseStockQty($location, $preItem, $curQty, $preQty, 'Delete', $section, $form_id, $preItem, $curItem, $curAmount, $preAmount);
                    decreaseStockQty($location, $curItem, $curQty, $preQty, 'Insert', $section, $form_id, $preItem, $curItem, $curAmount, $preAmount);
                }
            }

            // Batch-wise stock maintenance when batch ref is provided
            if (!empty($sr_table_unique_id) && !empty($sr_table_pk_id) && in_array($sr_table_unique_id, ['dpt_chemical', 'mpt_material'])) {
                mptDptMaterialBatchWiseStockEffect($location, $sr_table_unique_id, $sr_table_pk_id, $curQty, $preQty, $mode, $type);
            }

        } else {
            if ($location == 0) {
                throw new InsufficientStockException('Invalid Location Code', $curItem);
            } elseif ($curItem == 0 || $preItem == 0) {
                throw new InsufficientStockException('Invalid Item', $curItem);
            }
        }
    } else {
        throw new   InsufficientStockException('Location Code Mismatched', $curItem);
    }
}

/**
 * Sub-Helper Function for Batch-Wise Stock Maintenance (mpt_dpt_material_batch_wise_stock)
 */
function mptDptMaterialBatchWiseStockEffect($location_id, $sr_table_unique_id, $sr_table_pk_id, $curQty, $preQty, $mode, $type) {
    if ($location_id != 0 && $sr_table_pk_id != 0 && in_array($sr_table_unique_id, ['dpt_chemical', 'mpt_material'])) {
        
        $checkBatch = MptDptMaterialBatchWiseStock::where('current_location_id', $location_id)
            ->where('sr_table_unique_id', $sr_table_unique_id)
            ->where('sr_table_pk_id', $sr_table_pk_id)
            ->lockForUpdate()
            ->first();
            

        if ($type == 'U') { 
            // Increase Stock (Receiver Location in ILT / Direct Entry)
            if ($checkBatch == null) {
                $location_stock = MptDptMaterialBatchWiseStock::create([
                    'sr_table_unique_id'  => $sr_table_unique_id,
                    'sr_table_pk_id'      => $sr_table_pk_id,
                    'current_location_id' => $location_id,
                    'stock_qty'           => $curQty > 0 ? $curQty : 0,
                ]);
            } else {
                $qtySum = (float)$checkBatch->stock_qty;

                if ($mode == 'Insert') {
                    $totalQty = $qtySum + $curQty;
                } elseif ($mode == 'Update') {
                    // dd($qtySum,$curQty,$preQty);
                    $totalQty = $qtySum + ($curQty - $preQty);
                } elseif ($mode == 'Delete') {
                    $totalQty = $qtySum - $preQty;
                }

                if ($totalQty < 0) {
                    throw new InsufficientStockException('Insufficient Stock');
                    abort(404, 'Insufficient Stock');
                }

                $location_stock = MptDptMaterialBatchWiseStock::where('mpt_dpt_material_batch_wise_stock_id', $checkBatch->mpt_dpt_material_batch_wise_stock_id)
                    ->update([
                        'stock_qty' => $totalQty > 0 ? $totalQty : 0,
                    ]);
            }
        } elseif ($type == 'D') { 
            // Decrease Stock (Sender Location in ILT / Issue)
            if ($checkBatch == null) {
                throw new InsufficientStockException('Insufficient Stock');
                abort(404, 'Insufficient Stock');
            } else {
                $qtySum = (float)$checkBatch->stock_qty;

                if ($mode == 'Insert') {
                    $totalQty = $qtySum - $curQty;
                } elseif ($mode == 'Update') {
                    $totalQty = $qtySum + ($preQty - $curQty);
                } elseif ($mode == 'Delete') {
                    $totalQty = $qtySum + $preQty;
                }

                if ($totalQty < 0) {
                    throw new InsufficientStockException('Insufficient Stock');
                    abort(404, 'Insufficient Stock');
                }

                $location_stock = MptDptMaterialBatchWiseStock::where('mpt_dpt_material_batch_wise_stock_id', $checkBatch->mpt_dpt_material_batch_wise_stock_id)
                    ->update([
                        'stock_qty' => $totalQty > 0 ? $totalQty : 0,
                    ]);
            }
        }
    }
}




// below this function use to stock qty increase
function increaseStockQty($location,$item,$curQty,$preQty,$mode,$section,$form_id,$preItem,$curItem,$curAmount,$preAmount){

// dd("Location ".$location,"item ".$item,"CurQty ".$curQty,"PreQty ".$preQty,"Mode ".$mode,"Section ".$section,"Form ".$form_id,"PreItem ".$preItem,"CurItem ".$curItem,"curAmount ".$curAmount,"preAmount ".$preAmount);

    if($location != 0 && $item !=0){
        $checkItem = ItemOpening::where('io_item_id',$item)->where('current_location_id',$location)->lockForUpdate()->first();

        if($checkItem == null){
            $location_stock = ItemOpening::create([
                'current_location_id' => $location,
                'io_item_id' => $item,
                'io_stock_qty' => $curQty > 0 ? $curQty : 0,
                'io_stock_amount'=>$curAmount > 0 && $curQty > 0 ? $curAmount : 0,
                'io_stock_rate_unit' => $curQty > 0 ?  $curAmount / $curQty : 0,
            ]);


        }else{
            $qtySum = ItemOpening::where('io_item_id',$item)->where('current_location_id',$location)->lockForUpdate()->sum('io_stock_qty');
            $amountSum = ItemOpening::where('io_item_id',$item)->where('current_location_id',$location)->lockForUpdate()->sum('io_stock_amount');
            // $qtySum = number_format((float)$qtySum, 3, '.');
            $qtySum = number_format((float)$qtySum, 3, '.','');
            $amountSum = number_format((float)$amountSum, 3, '.','');

            if($mode == 'Insert'){
                    $totalSumQty = $qtySum + $curQty;
                    $totalSumAmount = $amountSum + $curAmount;
            }elseif($mode == 'Update'){
                    $totalSumQty = $qtySum + ($curQty - $preQty);
                    $totalSumAmount = $amountSum + ($curAmount - $preAmount);
            }elseif($mode == 'Delete'){
                    $totalSumQty = $qtySum - $preQty ;
                    $totalSumAmount = $amountSum - $preAmount;
            }

            if($totalSumQty < 0){
                throw new \App\Exceptions\InsufficientStockException('Insufficient Stock',$item);
                abort(404,'Insufficient Stock');
            }else{
                $totalQty = $totalSumQty > 0 ? $totalSumQty : 0 ;
                $totalAmount = $totalSumAmount > 0  && $totalSumQty > 0 ? $totalSumAmount : 0 ;


                $location_stock = ItemOpening::where('io_item_id',$item)->where('current_location_id',$location)
                ->update([
                    'current_location_id' => $location,
                    'io_item_id' => $item,
                    'io_stock_qty' => $totalQty,
                    'io_stock_amount' => $totalAmount,
                    'io_stock_rate_unit' => $totalQty > 0 ?  $totalAmount / $totalQty : 0,
                ]);

            }
        }

    }else{
        if($location == 0){
            throw new \App\Exceptions\InsufficientStockException('Invalid Location Code',$item);
            abort(404,'Invalid Location Code');
        }elseif($item == 0){
            throw new \App\Exceptions\InsufficientStockException('Invalid Item',$item);
            abort(404,'Invalid Item');
        }

    }


}
//end this  function



// below this function use to stock qty decrease
function decreaseStockQty($location,$item,$curQty,$preQty,$mode,$section,$form_id,$preItem,$curItem,$curAmount,$preAmount){
// function decreaseStockQty($location,$item,$curQty,$preQty,$mode){
    if($location != 0 && $item !=0){
        // $checkItem = LocationStock::where('item_id',$item)->where('location_id',$location)->first();
        $checkItem = ItemOpening::where('io_item_id',$item)->where('current_location_id',$location)->lockForUpdate()->first();

        if($checkItem == null){
            throw new \App\Exceptions\InsufficientStockException('Insufficient Stock',$item);
            abort(404,'Insufficient Stock');
           
        }else{

            $qtySum = ItemOpening::where('io_item_id',$item)->where('current_location_id',$location)->lockForUpdate()->sum('io_stock_qty');
            $amountSum = ItemOpening::where('io_item_id',$item)->where('current_location_id',$location)->lockForUpdate()->sum('io_stock_amount');

            $qtySum = number_format((float)$qtySum, 3, '.','');
            $amountSum = number_format((float)$amountSum, 3, '.','');

            if($mode == 'Insert'){
                $totalSumQty = $qtySum - $curQty;
                $totalSumAmount = $amountSum - $curAmount;
            }elseif($mode == 'Update'){
                $totalSumQty = $qtySum + ($preQty - $curQty);
                $totalSumAmount = $amountSum + ($preAmount - $curAmount);
            }elseif($mode == 'Delete'){
                $totalSumQty = $qtySum + $preQty ;
                $totalSumAmount = $amountSum + $preAmount;
            }

            // dd($amountSum,$curAmount,$preAmount,$totalSumAmount);
            if($totalSumQty < 0){
                throw new \App\Exceptions\InsufficientStockException('Insufficient Stock',$item);
                abort(404,'Insufficient Stock');
            }else{
                $totalQty = $totalSumQty > 0 ? $totalSumQty : 0 ;
                $totalAmount = $totalSumAmount > 0 ? $totalSumAmount : 0 ;
                
                
                $location_stock = ItemOpening::where('io_item_id',$item)->where('current_location_id',$location)
                ->update([
                    'current_location_id' => $location,
                    'io_item_id' => $item,
                    'io_stock_qty' => $totalQty,
                    'io_stock_amount' =>$totalQty > 0 &&  $totalAmount > 0 ?  $totalAmount : 0,
                    'io_stock_rate_unit' => $totalQty > 0 &&  $totalAmount > 0 ?  $totalAmount / $totalQty : 0,
                ]);
            }
        }
    }else{
        if($location == 0){
            throw new \App\Exceptions\InsufficientStockException('Invalid Location Code',$item);
            abort(404,'Invalid Location Code');
        }elseif($item == 0){
            throw new \App\Exceptions\InsufficientStockException('Invalid Item',$item);
            abort(404,'Invalid Item');
        }
    }

}
//end this  function


// Generate PDF Function
// function GeneratePdf($id, $name, $type, $formType, $job_type_fix = null, $nabl_type_fix = null, $repair = null) {
//     if (!hasAccess($type, 'print')) {
//         return;
//     }
//     $crystal_url = env('CRYSTAL_URL');

//     $remotePdfUrl = "{$crystal_url}{$type}_reports/{$type}.php?id={$id}&name={$name}";
//     if ($nabl_type_fix != '') {
//         // $nabl_type_fix = str_replace(' ', '_', $nabl_type_fix);
//         $remotePdfUrl .= "&nabl_type_fix={$nabl_type_fix}";
//     }
//     if ($job_type_fix != '') {
//         $remotePdfUrl .= "&job_type_fix={$job_type_fix}";
//     }
//     if ($type === 'observation_sheet') {
//         $parts = explode('_', $name);
//         $lastPart = strtolower(end($parts));
//         if (in_array($lastPart, ['rt', 'ut', 'mpt', 'dpt'])) {
//             $remotePdfUrl .= "&type={$lastPart}";
//         }
//     }    

//     $localDirectory = storage_path("app/public/reports/{$type}_reports_file/");
//     // dd($remotePdfUrl);
//     $localFileName = $name . '.pdf';
//     $localFilePath = $localDirectory . $localFileName;
//     $returnurl = "{$crystal_url}{$type}_reports/report_pdf_file/{$localFileName}";

//     // Create directory if it doesn't exist
//     if (!is_dir($localDirectory)) mkdir($localDirectory, 0777, true);

//     // Check if the PDF exists remotely (for 'listing' formType)
//     if ($formType == 'listing') {
//         $exitsPdfUrl = "{$crystal_url}{$type}_reports/report_pdf_file/{$name}.pdf";
//         $statusCode = get_headers($exitsPdfUrl, true)[0];
//         if (strpos($statusCode, "200") !== false) {
//             copy($returnurl, $localFilePath); // Copy if exists
//         } else {
//             checkPdf($remotePdfUrl, $localFilePath, $returnurl);
//         }
//     } else {
//         checkPdf($remotePdfUrl, $localFilePath, $returnurl); // For other formTypes
//     }
// }


if (!function_exists('sanitize_pdf_name')) {
    function sanitize_pdf_name($name) {
        // Replace slashes, spaces (including unicode spaces), and hyphens with underscores
        $name = preg_replace('/[\/\s\-]/u', '_', $name);
        // Keep only alphanumeric characters, underscores, and dots
        $name = preg_replace('/[^A-Za-z0-9_\.]/', '', $name);
        // Remove duplicate underscores
        $name = preg_replace('/__+/', '_', $name);
        return trim($name, '_');
    }
}

// Generate PDF Function
function GeneratePdf($id, $name, $type, $formType, $job_type_fix = null, $nabl_type_fix = null, $repair = null) {
    if (!hasAccess($type, 'print')) {
        return;
    }
    $name = sanitize_pdf_name($name);
    $crystal_url = env('CRYSTAL_URL');

    $encodedName = rawurlencode($name);
    $remotePdfUrl = "{$crystal_url}{$type}_reports/{$type}.php?id={$id}&name={$encodedName}";
    if ($nabl_type_fix != '') {
        $remotePdfUrl .= "&nabl_type_fix=" . rawurlencode($nabl_type_fix);
    }
    if ($job_type_fix != '') {
        $remotePdfUrl .= "&job_type_fix=" . rawurlencode($job_type_fix);
    }
    if ($repair != '') {
        $remotePdfUrl .= "&repair=" . rawurlencode($repair);
    }
    if ($type === 'observation_sheet') {
        $parts = explode('_', $name);
        $lastPart = strtolower(end($parts));
        if (in_array($lastPart, ['rt', 'ut', 'mpt', 'dpt'])) {
            $remotePdfUrl .= "&type={$lastPart}";
        }
    }    

    $localDirectory = storage_path("app/public/reports/{$type}_reports_file/");
    // dd($remotePdfUrl);
    $localFileName = $name . '.pdf';
    $localFilePath = $localDirectory . $localFileName;
    $returnurl = "{$crystal_url}{$type}_reports/report_pdf_file/" . rawurlencode($localFileName);

    // Create directory if it doesn't exist
    if (!is_dir($localDirectory)) mkdir($localDirectory, 0777, true);

    // Check if the PDF exists remotely (for 'listing' formType)
    if ($formType == 'listing') {
        $exitsPdfUrl = "{$crystal_url}{$type}_reports/report_pdf_file/" . rawurlencode($name) . ".pdf";
        $statusCode = get_headers($exitsPdfUrl, true)[0];
        if (strpos($statusCode, "200") !== false) {
            copy($returnurl, $localFilePath); // Copy if exists
        } else {
            checkPdf($remotePdfUrl, $localFilePath, $returnurl);
        }
    } else {
        checkPdf($remotePdfUrl, $localFilePath, $returnurl); // For other formTypes
    }
}

function checkPdf($remotePdfUrl, $localFilePath, $returnurl) {
    $fp = fopen($localFilePath, 'w');
    $ch = curl_init($remotePdfUrl);
    curl_setopt_array($ch, [
        CURLOPT_FILE => $fp,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_FAILONERROR => true
    ]);

    if (curl_exec($ch) === false) {
        echo "cURL Error: " . curl_error($ch);
    } else {
        sleep(2);
        if (copy($returnurl, $localFilePath)) {           
        } else {
            echo "Failed to copy the PDF file.";
        }
    }

    fclose($fp);
    curl_close($ch);
}

    // function getCameraType()
    // {
    //     return [
    //         'Camera (Gama Ray)' => "Camera (Gama Ray)",
    //         'Camera X-Ray' => "Camera X-Ray",
    //         'Gamma Ray & X-Rays' => "Gamma Ray & X-Rays",
    //         'Gamma Ray Camera Ir 192' => "Gamma Ray Camera Ir 192",
    //         'X-Ray Equipment' => "X-Ray Equipment",
    //     ];
    // }

    function getIsotopeType()
    {
        return [
            'Ir-192' => "Ir-192",
            'Co-60' => "Co-60",
            'X-Ray' => "X-Ray",
        ];
    }

    function getLocationType()
    {
        return [
            'HO' => "HO",
            'Own Unit' => "Own Unit",
            'In-House (Foundry)' => "In-House (Foundry)",
        ];
    }

    function getPageIdForAssignFormatNo()
    {
        return Menus::select('id')->where('show_in_assign_format', 'YES')->pluck('id');
    }

    function getRptNameForAssignFormateNo($id){
        return Menus::select('rpt_name','display_name')->where('show_in_assign_format', 'YES')->where('id',$id)->first();
    }

    function getMenuIdBassedOnDisplayName($page){
        return Menus::select('id')->where('page', $page)->first();
    }

    function getAssignFormateNoForTransaction($locationId, $pageId, $effectDate){
        if(empty($effectDate)){
            return null;
        }
        $effectDate = Date::createFromFormat('d/m/Y', $effectDate)->format('Y-m-d');
        $formatNo = AssignFormatNo::where('assign_location_id', $locationId)
                    ->where('page_id', $pageId)
                    ->whereDate('assign_effect_date', '<=', $effectDate)
                    ->orderBy('assign_effect_date', 'desc')
                    ->value('assign_format_no');
                    

        return $formatNo;
    }
    function digitsToWords($amount){

        $wholePart = floor($amount);
        $fractionalPart = (int) round(($amount - $wholePart) * 100); // <- cast AFTER rounding

        $formatter = new NumberFormatter("en_IN", NumberFormatter::SPELLOUT);
        $formatter->setAttribute(NumberFormatter::ROUNDING_MODE, NumberFormatter::ROUND_HALFUP);

        $wholePartInWords = $wholePart > 0 ? $formatter->format($wholePart) : "zero";

        $result =  "Rupees " . $wholePartInWords;

        if ($fractionalPart > 0) {
            // Ensure this is integer, not float!
            $fractionalPartInWords = $formatter->format($fractionalPart);
            $result .= " and " . $fractionalPartInWords . " Paisa";
        }

        $result .= " Only";

        return ucwords(str_replace('-', ' ', $result));
        // // Format the amount in wholePart using the SPELLOUT style
        // $wholePartInWords = $formatter->format($wholePart);


        // // Format the Fractional part in words
        // $fractionalPartInWords = $formatter->format($fractionalPart);


        // // Construct the final result
        // if($wholePart > 0){
        //     $result = $wholePartInWords;
        // }else{
        //     $result = "No";
        // }

        // if ($fractionalPart > 0) {
        //     $result .= " and " . $fractionalPartInWords;
        // }else{
        //     $result .= " and " . 'No';
        // }


        // return ucwords( str_replace(' and ', ' Rupees and ', str_replace('-', ' ', ucfirst($result)))." Paisa Only");
    }

   
    define("PWHT_APPLICABLE" ,[
        'yes' => 'Yes',
        'no' => 'No',
        'partially' => 'Partially'
    ]);

    define("OVERLAY_APPLICABLE" ,[
        'yes' => 'Yes',
        'no' => 'No',
        'partially' => 'Partially'
    ]);

    define("APPLICABLE_NDT",[
        'rt' => 'RT',
        'paut' => 'PAUT',
        'rt+paut' => 'RT + PAUT',
        'tofd+paut' => 'TOFD + PAUT',
        'rt+tofd+paut' => 'RT + TOFD + PAUT'
    ]);

    define("BLOCK_REQUIREMENT",[
        'given' => 'Given',
        'not_given' => 'Not Given',
        'not_applicable' => 'Not Applicable'
    ]);

    define("PROJECT_LOCATION",[
        'plant-15' => 'Plant-15',
        'plant-15a' => 'Plant-15A',
        'plant-19' => 'Plant-19',
        'dahej' => 'Dahej'
    ]);

    define("ASME_CODE_STAMP",[
        'yes' => 'Yes',
        'no' => 'No'
    ]);

    function getFilmBrands()
    {
        return FilmBrand::select('film_brand_id', 'film_brand')->orderBy('film_brand', 'asc')->get();
    }

    function getFilmTypes()
    {
        return FilmType::select('film_type_id', 'film_type')->orderBy('film_type', 'asc')->get();
    }

    function getIqiDesignations()
    {
        return IqiDesignation::select('iqi_designation_id', 'iqi_designation')->orderBy('iqi_designation', 'asc')->get();
    }

    function getIqiSensitivities()
    {
        return IqiSensitivity::select('iqi_sensitivity_id', 'iqi_sensitivity')->orderBy('iqi_sensitivity', 'asc')->get();
    }

    function getFilms()
    {
        return Film::select('film_id', 'film_size_inch', 'film_size_cm', 'sq_in', 'sq_cm')->where('status', 'Active')->orderBy('film_size_inch', 'asc')->get();
    }

    function getAuthorityPersons()
    {
        return AuthorityPerson::select('authority_person_id', 'operator', 'designation')->where('status', 'Active')->orderBy('operator', 'asc')->get();
    }

    function getAuthorityPersonsTestBy()
    {
        return AuthorityPerson::select('authority_person_id', 'operator', 'designation')->where('status', 'Active')->where('tested_by','Yes')->orderBy('operator', 'asc')->get();
    }
    function getAuthorityPersonsReviewedBy()
    {
        return AuthorityPerson::select('authority_person_id', 'operator', 'designation')->where('status', 'Active')->where('reviewed_by','Yes')->orderBy('operator', 'asc')->get();
    }
    function getAuthorityPersonsAuthorizedBy()
    {
        return AuthorityPerson::select('authority_person_id', 'operator', 'designation')->where('status', 'Active')->where('authorized_by','Yes')->orderBy('operator', 'asc')->get();
    }

    function getCheckedByAuthorityPersons()
    {
        return AuthorityPerson::select('authority_person_id', 'operator', 'designation')->where('status', 'Active')->where('checked_by', 'Yes')->orderBy('operator', 'asc')->get();
    }

    function getAuthorizedByAuthorityPersons()
    {
        return AuthorityPerson::select('authority_person_id', 'operator', 'designation')->where('status', 'Active')->where('authorized_by', 'Yes')->orderBy('operator', 'asc')->get();
    }

    function getFindings()
    {
        return Finding::select('finding_id', 'finding_name', 'abbreviation', 'required_finding_level')->orderBy('finding_name', 'asc')->get();
    }

    function getFindingLevels()
    {
        return FindingLevel::select('finding_level_id', 'finding_level_name')->orderBy('finding_level_name', 'asc')->get();
    }

    function getFilmResults()
    {
        return FilmResult::select('film_result_id', 'film_result_name')->orderBy('film_result_name', 'asc')->get();
    }

    /*function getULRConf($changedulrIds = [])
    {
        $get_ulr = \App\Models\NABLConfiguration::select('nabl_id as id', 'tc_no as ulr')->orderBy('tc_no');
        if(!empty($changedulrIds))
        {
            $get_ulr->where(function ($q) use ($changedulrIds) {
                $q->where('nabl_configurations.status','=','Active')
                ->orWhereIn('nabl_configurations.nabl_id', $changedulrIds);
            });
        }
        else
        {
            $get_ulr->where('nabl_configurations.status','=','Active');
        }
        return $get_ulr->get();
    }*/

    function getULRConf($changedulrIds = [])
    {
        $currentLocation = getCurrentLocation();

        $get_ulr = NABLConfiguration::select(
                'nabl_configurations.nabl_id as id',
                'nabl_configurations.tc_no as ulr'
            )
            ->join('location', 'location.nabl_id', '=', 'nabl_configurations.nabl_id')
            ->where('location.location_id', $currentLocation->location_id)
            ->orderBy('nabl_configurations.tc_no');

        if (!empty($changedulrIds)) {
            $get_ulr->where(function ($q) use ($changedulrIds) {
                $q->where('nabl_configurations.status', 'Active')
                ->orWhereIn('nabl_configurations.nabl_id', $changedulrIds);
            });
        } else {
            $get_ulr->where('nabl_configurations.status', 'Active');
        }

        return $get_ulr->get();
    }

    function stockEffectSQIN($location,$curItem,$preItem,$curQty,$preQty,$mode,$type,$section,$form_id){
        $locationCode = getCurrentLocation();

        if($locationCode->location_id == $location){
            if($location != 0 && $curItem !=0 && $preItem !=0){
                if($type == 'U'){
                    if($curItem == $preItem){
                        increaseStockQtySQIN($location,$curItem,$curQty,$preQty,$mode,$section,$form_id,$preItem,$curItem);               
                    }else{
                        increaseStockQtySQIN($location,$preItem,$curQty,$preQty,'Delete',$section,$form_id,$preItem,$curItem);                 
                        increaseStockQtySQIN($location,$curItem,$curQty,$preQty,'Insert',$section,$form_id,$preItem,$curItem);                  
                    }
                }elseif($type == 'D'){
                    if($curItem == $preItem){
                        decreaseStockQtySQIN($location,$curItem,$curQty,$preQty,$mode,$section,$form_id,$preItem,$curItem);
                    }else{
                        decreaseStockQtySQIN($location,$preItem,$curQty,$preQty,'Delete',$section,$form_id,$preItem,$curItem);                 
                        decreaseStockQtySQIN($location,$curItem,$curQty,$preQty,'Insert',$section,$form_id,$preItem,$curItem);
                    }
                }
            }else{
                if($location == 0){
                    abort(404,'Invalid Location Code');
                }elseif($curItem == 0){
                    abort(404,'Invalid Item');
                }elseif($preItem == 0){
                    abort(404,'Invalid Item');
                }
            }
        }else{
            abort(404,'Location Code Mismatched');
        }
    }

    function increaseStockQtySQIN($location,$item,$curQty,$preQty,$mode,$section,$form_id,$preItem,$curItem){
        if($location != 0 && $item !=0){
            $checkItem = ItemOpeningProdArea::where('item_id',$item)->where('current_location_id',$location)->lockForUpdate()->first();

            if($checkItem == null){
                $location_stock = ItemOpeningProdArea::create([
                    'current_location_id' => $location,
                    'item_id' => $item,
                    'stock_sq_in' => $curQty > 0 ? $curQty : 0,
                ]);
            }else{
                $qtySum = ItemOpeningProdArea::where('item_id',$item)->where('current_location_id',$location)->lockForUpdate()->sum('stock_sq_in');
                $qtySum = number_format((float)$qtySum, 2, '.','');

                if($mode == 'Insert'){
                    $totalSumQty = $qtySum + $curQty;
                }elseif($mode == 'Update'){
                    $totalSumQty = $qtySum + ($curQty - $preQty);
                }elseif($mode == 'Delete'){
                    $totalSumQty = $qtySum - $preQty ;
                }

                if($totalSumQty < 0){
                    throw new \App\Exceptions\InsufficientStockException('Insufficient Stock',$item);
                }else{
                    $totalQty = $totalSumQty > 0 ? $totalSumQty : 0 ;
                    $checkItem->update([
                        'stock_sq_in' => $totalQty,
                    ]);
                }
            }
        }else{
            if($location == 0){
                throw new \App\Exceptions\InsufficientStockException('Invalid Location Code',$item);
            }elseif($item == 0){
                throw new \App\Exceptions\InsufficientStockException('Invalid Item',$item);
            }
        }
    }

    function decreaseStockQtySQIN($location,$item,$curQty,$preQty,$mode,$section,$form_id,$preItem,$curItem){
        if($location != 0 && $item !=0){
            $checkItem = ItemOpeningProdArea::where('item_id',$item)->where('current_location_id',$location)->lockForUpdate()->first();

            if($checkItem == null){
                throw new \App\Exceptions\InsufficientStockException('Insufficient Stock',$item);
            }else{
                $qtySum = ItemOpeningProdArea::where('item_id',$item)->where('current_location_id',$location)->lockForUpdate()->sum('stock_sq_in');
                $qtySum = number_format((float)$qtySum, 2, '.','');

                if($mode == 'Insert'){
                    $totalSumQty = $qtySum - $curQty;
                }elseif($mode == 'Update'){
                    $totalSumQty = $qtySum - ($curQty - $preQty);
                }elseif($mode == 'Delete'){
                    $totalSumQty = $qtySum + $preQty ;
                }

                if($totalSumQty < 0){
                    throw new \App\Exceptions\InsufficientStockException('Insufficient Stock',$item);
                }else{
                    $totalQty = $totalSumQty > 0 ? $totalSumQty : 0 ;
                    $checkItem->update([
                        'stock_sq_in' => $totalQty,
                    ]);
                }
            }
        }else{
            if($location == 0){
                throw new \App\Exceptions\InsufficientStockException('Invalid Location Code',$item);
            }elseif($item == 0){
                throw new \App\Exceptions\InsufficientStockException('Invalid Item',$item);
            }
        }
    }

    // Mail Send Functionality
    function sendReportEmails($reportData, $reportType, $title)
    {
        $emailAttachments = [];
        $customer_name = [];
        $name = [];
 
        foreach ($reportData as $data) {
            if (empty($data->email_id)) continue;
 
            $email = $data->email_id;
            $customer_name[$email] = $data->customer_name;
            // $name[$email] = str_replace("/", "_", $data->name);
            $name[$email] = sanitize_pdf_name($data->name);
 
            $filePath = storage_path(
                'app/public/reports/' . $data->type . '_reports_file/' . $name[$email] . '.pdf'
            );
 
            if (!file_exists($filePath)) {
                GeneratePdf(base64_decode($data->id), $name[$email], $data->type, 'listing');
            }
 
            if (file_exists($filePath)) {
                $emailAttachments[$email][] = $filePath;
            }
        }
 
        if (empty($emailAttachments)) {
            return response()->json(['status' => false, 'message' => 'No Reports Found To Email.'], 422);
        }

        $company = Company::find(auth()->user()->company_id ?? null);

        // Dynamically load SMTP configuration from database if exists
        $smtpConfig = null;

        // Check matching checkbox flag
        if ($reportType === 'purchase_order' || $reportType === 'service_po') {
            $smtpConfig = SMTPConfiguration::where('purchase', 1)
                ->first();
        }

        // Fallback to first available SMTP configuration
        if (!$smtpConfig) {
            $smtpConfig = SMTPConfiguration::first();
        }

        if (!$smtpConfig) {
            return response()->json([
                'status' => false,
                'message' => 'SMTP Configuration not found. Please set up SMTP Configuration under Settings first.'
            ], 422);
        }

        if ($smtpConfig) {
            $encryption = $smtpConfig->enable_ssl ? 'ssl' : null;

            $localDomain = strpos($smtpConfig->email ?? '', '@') !== false ? explode('@', $smtpConfig->email)[1] : 'localhost';

            config([
                'mail.mailers.smtp.host'         => $smtpConfig->mail_host,
                'mail.mailers.smtp.port'         => $smtpConfig->out_port_no,
                'mail.mailers.smtp.username'     => $smtpConfig->email,
                'mail.mailers.smtp.password'     => $smtpConfig->password,
                'mail.mailers.smtp.encryption'   => $encryption,
                'mail.from.address'              => $smtpConfig->email,
                'mail.from.name'                 => env('APP_NAME'),
                'mail.mailers.smtp.local_domain' => $localDomain,
            ]);

            if ($smtpConfig->reply_email) {
                config([
                    'mail.reply_to' => [
                        'address' => $smtpConfig->reply_email,
                        // 'name'    => env('APP_NAME'),
                    ]
                ]);
            }
        }
        try {
            foreach ($emailAttachments as $email => $attachments) {
                $emailList = array_map('trim', explode(',', $email));
                $emailList = array_filter(array_unique($emailList));
 
                $reportsInfo = [];
                foreach ($attachments as $filePath) {
                    $fileName = pathinfo($filePath, PATHINFO_FILENAME);
                    
                    $docNo = '';
                    $docDate = '';
                    
                    foreach ($reportData as $rData) {
                        $rName = str_replace("/", "_", $rData->name);
                        if ($rName === $fileName || str_contains($fileName, $rName)) {
                            $id = base64_decode($rData->id);
                            if ($rData->type === 'purchase_order') {
                                $doc = PurchaseOrder::find($id);
                                if ($doc) {
                                    $docNo = $doc->po_number;
                                    $docDate = !empty($doc->po_date) ? date('d-m-Y', strtotime($doc->po_date)) : '';
                                }
                            } elseif ($rData->type === 'service_po') {
                                $doc = ServicePO::find($id);
                                if ($doc) {
                                    $docNo = $doc->ser_po_number;
                                    $docDate = !empty($doc->ser_po_date) ? date('d-m-Y', strtotime($doc->ser_po_date)) : '';
                                }
                            }
                            break;
                        }
                    }
                    
                    if (empty($docNo)) {
                        $docNo = str_replace('_', '/', $fileName);
                    }
                    
                    $reportsInfo[] = [
                        'filename' => pathinfo($filePath, PATHINFO_BASENAME),
                        'doc_no' => $docNo,
                        'doc_date' => $docDate
                    ];
                }

                $mail = Mail::to($emailList);
                if ($smtpConfig && !empty($smtpConfig->cc_email)) {
                    $ccList = array_map('trim', explode(',', $smtpConfig->cc_email));
                    $ccList = array_filter(array_unique($ccList));
                    if (!empty($ccList)) {
                        $mail->cc($ccList);
                    }
                }
                
                $mail->send(new ReportsEmail($attachments, $name[$email], $customer_name[$email], $title, $company, $reportsInfo));
            }
        } catch (\Exception $e) {
            Log::error("SMTP Email Send Failure: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to send email. Please check your SMTP Configuration credentials (Host, Username, Password, SSL/Port).'
            ], 500);
        }
 
        return response()->json(['status' => true, 'message' => 'Emails Sent Successfully']);
    }

    function getReportModelsList()
    {
        return [
            \App\Models\Transaction\TestReportRt::class,
            \App\Models\Transaction\TestReportUt::class,
            \App\Models\Transaction\TestReportDpt::class,
            \App\Models\Transaction\TestReportMpt::class,
        ];
    }

    function getLatestUlrSequence($ulr_id, $year, $location_id, $requested_sequence = null)
    {
        if ($requested_sequence !== null && $requested_sequence !== '') {
            return intval($requested_sequence);
        }

        $max_sequence = 0;
        foreach (getReportModelsList() as $model) {
            if (class_exists($model)) {
                $seq = $model::where('ulr_year', $year)
                    ->where('ulr_id', $ulr_id)
                    ->where('current_location_id', $location_id)
                    ->max('ulr_sequence');
                if ($seq > $max_sequence) {
                    $max_sequence = $seq;
                }
            }
        }

        return $max_sequence > 0 ? $max_sequence + 1 : 1;
    }

    /*
    function isDuplicateUlrSequence($ulr_sequence, $ulr_id, $year, $location_id, $currentModel = null, $currentId = null)
    {
        foreach (getReportModelsList() as $model) {
            if (class_exists($model)) {
                $query = $model::where('ulr_sequence', $ulr_sequence)
                    ->where('ulr_id', $ulr_id)
                    ->where('ulr_year', $year)
                    ->where('current_location_id', $location_id);

                if ($currentModel && $currentId && $model === $currentModel) {
                    $instance = new $model();
                    $query->where($instance->getKeyName(), '!=', $currentId);
                }

                if ($model === TestReportRt::class) {
                    $query->whereColumn(
                        'revision_test_report_rt_id',
                        'test_report_rt_id'
                    );
                }

                if ($query->exists()) {
                    return true;
                }
            }
        }
        return false;
    }
    */

    function isDuplicateUlrSequence($ulr_sequence, $ulr_id, $year, $location_id, $currentModel = null, $currentId = null, $isRevision = false, $baseReportId = null)
    {
        foreach (getReportModelsList() as $model) {
            if (class_exists($model)) {
                $query = $model::where('ulr_sequence', $ulr_sequence)
                    ->where('ulr_id', $ulr_id)
                    ->where('ulr_year', $year)
                    ->where('current_location_id', $location_id);

                $instance = new $model();
                $table = $instance->getTable();
                $keyName = $instance->getKeyName();
                $revisionCol = 'revision_' . $table . '_id';

                // 1. Exclude the current record itself if updating
                if ($currentModel && $currentId && $model === $currentModel) {
                    $query->where($keyName, '!=', $currentId);
                }

                // 2. If it's a revision of a report in the same model, exclude all records belonging to the same base report family
                if ($isRevision && $baseReportId && $currentModel && $model === $currentModel && \Illuminate\Support\Facades\Schema::hasColumn($table, $revisionCol)) {
                    $query->where(function ($q) use ($baseReportId, $keyName, $revisionCol) {
                        $q->whereNull($revisionCol)
                          ->orWhere(function ($q2) use ($baseReportId, $keyName, $revisionCol) {
                              $q2->where($keyName, '!=', $baseReportId)
                                 ->where($revisionCol, '!=', $baseReportId);
                          });
                    });
                } elseif (\Illuminate\Support\Facades\Schema::hasColumn($table, $revisionCol)) {
                    // For non-revisions, only check base reports (where revision_xxx_id is NULL or equal to main id)
                    $query->where(function ($q) use ($keyName, $revisionCol) {
                        $q->whereNull($revisionCol)
                          ->orWhereColumn($revisionCol, $keyName);
                    });
                }

                if ($query->exists()) {
                    return true;
                }
            }
        }
        return false;
    }

    function formatUlrNo($procedure_data, $sequence, $yearShort)
    {
        $middle_num = str_pad($sequence, 8, "0", STR_PAD_LEFT);
        return $procedure_data->tc_no
            . $yearShort
            // . $procedure_data->location_no
            . $middle_num;
            // . $procedure_data->nabl_type;
    }

    /* old function getDptChemicals()
    {
        $location = getCurrentLocation()->location_id;
        return DPTChemical::select('dpt_id', 'dpt_chemical', 'name_for_display', 'dpt_designation', 'dpt_make', 'dpt_batch_no', 'dpt_expiry_date')
            ->where('current_location_id', $location)
            ->where('dpt_status', '=', 'Active')
            ->orderBy('dpt_chemical', 'asc')
            ->get();
    }*/

    function getDptChemicals($selected_id = null)
    {
        $location = getCurrentLocation()->location_id;
        $query = DPTChemical::select(
            'dpt_chemical.dpt_id', 
            'dpt_chemical.dpt_chemical', 
            'dpt_chemical.name_for_display', 
            'dpt_chemical.dpt_designation', 
            'dpt_chemical.dpt_make', 
            'dpt_chemical.dpt_batch_no', 
            'dpt_chemical.dpt_expiry_date', 
            'mpt_dpt_material_batch_wise_stock.stock_qty'
        )
        ->leftjoin('mpt_dpt_material_batch_wise_stock', 'mpt_dpt_material_batch_wise_stock.sr_table_pk_id', '=', 'dpt_chemical.dpt_id')
        ->where('mpt_dpt_material_batch_wise_stock.sr_table_unique_id', '=', 'dpt_chemical')
        ->where('mpt_dpt_material_batch_wise_stock.current_location_id', '=', $location);

        if (!empty($selected_id)) {
            $query->where(function($q) use ($selected_id) {
                $q->where('mpt_dpt_material_batch_wise_stock.stock_qty', '>', 0)
                  ->orWhere('dpt_chemical.dpt_id', '=', $selected_id);
            });
        } else {
            $query->where('mpt_dpt_material_batch_wise_stock.stock_qty', '>', 0);
        }

        return $query->orderBy('dpt_chemical.dpt_chemical', 'asc')->get();
    }
