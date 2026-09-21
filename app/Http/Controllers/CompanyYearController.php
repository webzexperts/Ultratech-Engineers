<?php

namespace App\Http\Controllers;

use App\Models\CompanyYear;
use App\Models\Admin;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DataTables;
use Date;

class CompanyYearController extends Controller
{

    /**
     * Return all company_year data without filter
     */
    public function companyYearData()
    {
        $company_years = CompanyYear::orderBy('year','ASC')->get();

        if($company_years){
            return response()->json([
                'company_years' => $company_years,
                'response_code' => '1'
            ]);
        }else{
            return response()->json([
                'response_code' => '0',
                'response_message' => 'No Data Available',
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
        return view('manage.manage-company_year');
    }

    /**
     * Return default company year
     */
    public static function getDefaultComapnyYear(){
        $year_data = CompanyYear::where('company_id','=',Auth::user()->company_id)->where('id','=',session('default_year_id'))->first();
        $curr_year = "";
        if($year_data != null){
            $curr_year = date('Y',strtotime($year_data->startdate)).'-'.date('y',strtotime($year_data->enddate));
            // $curr_year = date('Y',strtotime($year_data->startdate)).'-'.date('Y',strtotime($year_data->enddate));
            
        }
        return $curr_year;
    }

    /**
     * Return default company year data
     */
    public static function getDefaultYearData($forBlade = false){
        $year_data = CompanyYear::where('id','=',session('default_year_id'))->first();
        if($year_data == null){
           
            if($forBlade == false){
                return response()->json([
                    'response_code' => '0',
                    'data' => $year_data,
                    'response_message' => 'Company Year Data Not Available'
                ]);
            }else{
                 return null;
            }
            
        }
        return $year_data;
    }

     /* For get All Company_year ids which is less or equal to current company_year */

    public static function getTillYearIds(){

        $year_data = CompanyYear::where('id','=',session('default_year_id'))->first();
        $curr_year_id = 0;
        $year_ids = [];
        if($year_data != null){
            $curr_year_id = $year_data->id;
            $allYear = CompanyYear::where('year', '<=',$year_data->year)->get(['id']);
            if($allYear != null){
                foreach($allYear as $akey => $aVal){
                    $year_ids[$akey] = $aVal['id'];
                }
            }
        }

        return $year_ids;
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(CompanyYear $CompanyYear,Request $request,DataTables $dataTables)
    {
        $company_year_data = CompanyYear::select(['year','default_year','startdate','enddate','yearcode','id','sequence','created_on','created_by','last_by','last_on']);
        $dataTable = DataTables::of($company_year_data)
        // return DataTables::of($company_year_data)
        // ->editColumn('created_by', function($company_year_data){ 
        //     if($company_year_data->created_by != null){
        //         $created_by = Admin::where('id','=',$company_year_data->created_by)->first('user_name'); 
        //         return isset($created_by->user_name) ? $created_by->user_name : '';
        //     }else{
        //         return '';
        //     }
        // })
        // ->editColumn('last_by', function($company_year_data){ 
        //     if($company_year_data->last_by != null){
        //         $last_by = Admin::where('id','=',$company_year_data->last_by)->first('user_name'); 
        //         return isset($last_by->user_name) ? $last_by->user_name : '';
        //     }else{
        //         return '';
        //     }
        
        // })
        // ->editColumn('created_on', function($company_year_data){ 
        //     if ($company_year_data->created_on != null) { 
        //         $formatedDate1 = Date::createFromFormat('Y-m-d H:i:s', $company_year_data->created_on)->format(DATE_TIME_FORMAT); return $formatedDate1; 
        //     }else{
        //         return '';
        //     }
        // })
        // ->editColumn('last_on', function($company_year_data){ 
        //     if ($company_year_data->last_on != null) {
        //         $formatedDate2 = Date::createFromFormat('Y-m-d H:i:s', $company_year_data->last_on)->format(DATE_TIME_FORMAT); return $formatedDate2; 
        //     }else{
        //         return '';
        //     }
        // })
        ->editColumn('startdate', function($company_year_data){ 
            if ($company_year_data->startdate != null) {
                $formatedDate3 = Date::createFromFormat('Y-m-d', $company_year_data->startdate)->format(DATE_FORMAT); return $formatedDate3; 
            }else{
                return '';
            }
        })
        ->filterColumn('company_years.startdate', function ($q, $k) {
            applyDate($q, $k, 'company_years.startdate');
        })
        ->editColumn('enddate', function($company_year_data){ 
            if ($company_year_data->enddate != null) {
                $formatedDate4 = Date::createFromFormat('Y-m-d', $company_year_data->enddate)->format(DATE_FORMAT); return $formatedDate4; 
            }else{
                return '';
            }
        })
        ->filterColumn('company_years.enddate', function ($q, $k) {
            applyDate($q, $k, 'company_years.enddate');
        })

        ->editColumn('year', function($company_year_data){ 
            if ($company_year_data->year != null) {
                $formatedDate5 = date('Y',strtotime($company_year_data->startdate)).'-'.date('y',strtotime($company_year_data->enddate)); return $formatedDate5; 
            }else{
                return '';
            }
        })
        ->filterColumn('company_years.year', function ($query, $keyword) {
            $keyword = trim($keyword);
            if (strpos($keyword, '-') !== false) {
                [$start, $end] = explode('-', $keyword);
                if (strlen($end) == 2 && strlen($start) == 4) {
                    $prefix = substr($start, 0, 2);
                    $endFull = $prefix . $end;
                } else {
                    $endFull = $end;
                }

                $searchValue = $start . '-' . $endFull;
                $query->where('company_years.year', 'LIKE', "%{$searchValue}%");
            }
            else if (strlen($keyword) == 4) {
                $query->where('company_years.year', 'LIKE', "{$keyword}-%");
            } else {
                $query->where('company_years.year', 'LIKE', "%{$keyword}%");
            }
        })

        ->addColumn('options',function($company_year_data){ 

            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';
            $maxCompanyYear = CompanyYear::max('year');
            $minCompanyYear = CompanyYear::min('year');
            $maxSequence = CompanyYear::max('sequence');
            
            if(session('default_year_id') != $company_year_data->id){
                if($maxCompanyYear == $company_year_data->year || $minCompanyYear == $company_year_data->year){
                        if(hasAccess("company_year","delete")){
                             $action .= '<li> <a class="dropdown-item remove-item-btn">
                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                        </a>
                                    </li>';
                        }
                }
            }
            $action .= "</ul></div>";
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'company_years');
        return $dataTable
        ->rawColumns(['created_by','created_on','last_by','last_on','options'])
        ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('add.add-company_year');
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
            'year'=>'required|max:500|unique:company_years',
        ],
        [
            'year.required' => 'Please Enter Year',
            'year.max' => 'Maximum 500 Characters Allowed'
        ]);

        // $defaultYear = isset($request->default_year) && $request->default_year != "" ? $request->default_year : "N";


        if($request->type == "forward"){
            $maxSeq = CompanyYear::max('sequence');
        
            if($maxSeq != null){
                $maxSeq = intVal($maxSeq);
                $sequence = $maxSeq+1;
            }else{
                $sequence = 500;
            }
        }else{
            $minSeq = CompanyYear::min('sequence');
        
            if($minSeq != null){
                $minSeq = intVal($minSeq);
                $sequence = $minSeq-1;
            }else{
                $sequence = 500;
            }
        }

        $total = CompanyYear::count('id');

        if($total == 0){
            $defaultYear = 'Y';
        }else{
            $defaultYear = 'N';
        }

        $company_year_data=  CompanyYear::create([
            'year' => $request->year,
            'type' => $request->type,
            'sequence' => $sequence,
            'default_year' => $defaultYear,
            'startdate' => Date::createFromFormat('d/m/Y', $request->startdate)->format('Y-m-d'),
            'enddate' => Date::createFromFormat('d/m/Y', $request->enddate)->format('Y-m-d'),
            'yearcode' => $request->yearcode,
            'company_id' => Auth::user()->company_id,
            'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
            'created_by' => Auth::user()->id
        ]);

        if($company_year_data->save()){
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
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CompanyYear  $CompanyYear
     * @return \Illuminate\Http\Response
     */
    public function edit(CompanyYear $CompanyYear,Request $request,$id)
    {
        $company_year_data = CompanyYear::where('id','=',$id)->first();
        if($company_year_data){
            return response()->json([
                'company_year' => $company_year_data,
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

    public function switchYear(CompanyYear $CompanyYear){
        $company_years = CompanyYear::orderBy('year','ASC')->get();
        return view('switch-company_year')->with('company_years',$company_years);
    }

    public function change(Request $request){

        session(['default_year_id' => $request->company_year]);
        // CompanyYear::where('default_year','=','Y')->update(['default_year' => 'N']);

        // $company_year_data=  CompanyYear::where('id',$request->company_year)->update([
        //     'default_year' => 'Y',
        //     'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
        //     'last_by' => Auth::user()->id
        // ]);

        // $currentYearData = CompanyYear::where('id',$request->company_year)->first(['yearcode']);

        // $formatData = ['company_year_id' => $request->company_year];
        // if($currentYearData != null){
        //     $formatData['postfix'] = $currentYearData->yearcode;
        // }

        // CompanyNumberFormat::where('id','=',1)->update($formatData);

        // if($company_year_data){
            return response()->json([
                'response_code' => '1',
                'default_year' => self::getDefaultComapnyYear(),
                'response_message' => 'Company Year Has been changed.',
            ]);
        // }else{
        //     return response()->json([
        //         'response_code' => '0',
        //         'response_message' => 'Company Year has Not changed',
        //     ]);
        // }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CompanyYear  $CompanyYear
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CompanyYear $CompanyYear)
    {
        
        $validated = $request->validate([
            'year'=>'required|max:500|unique:company_years',
            'startdate' => 'unique:company_years',
            'enddate' => 'unique:company_years',
        ],
        [
            'year.required' => 'Please Enter Year',
            'year.max' => 'Maximum 500 Characters Allowed'
        ]);

        // $defaultYear = isset($request->default_year) && $request->default_year != "" ? $request->default_year : "N";

        $company_year_data=  CompanyYear::where('id','=',$request->id)->update([
            'year' => $request->year,
            'default_year' => 'N',
            'type' => $request->type,
            'startdate' => $request->startdate,
            'enddate' => $request->enddate,
            'yearcode' => $request->yearcode,
            'company_id' => Auth::user()->company_id,
            'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
            'last_by' => Auth::user()->id
        ]);

        if($company_year_data){
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

    public function makeYear(Request $request){
        $exists_data = CompanyYear::all();

        $respData = array();

        $previousYear = date('Y',strtotime('-1 year'));
        $currentYear = date('Y');
        $nextYear = date('Y',strtotime('+1 year'));
        $found = 0;

        if($exists_data != null){

            $maxcompanyYear = CompanyYear::max('year');
            $mincompanyYear = CompanyYear::min('year');

            if($request->type == "reverse"){
                
                foreach ($exists_data as $key => $value) {
                    
                    if($mincompanyYear != null){

                        $explY = explode('-', $mincompanyYear);

                        $found++;
                        $forStart = $explY[0];
                        $forEnd = $explY[0] - 1;
                        
                        $forStart = $forStart.'-03-31';
                        $forEnd = $forEnd.'-04-01';

                        $respData['year_code'] = date('y', strtotime($forEnd)).'-'.date('y', strtotime($forStart));
                        $respData['year'] = date('Y', strtotime($forEnd)).'-'.date('Y', strtotime($forStart));
                        $respData['startdate'] = date('d/m/Y', strtotime($forEnd));
                        $respData['enddate'] = date('d/m/Y', strtotime($forStart));

                    }else{

                        $explY = explode('-',$value['year']);
                    
                        if($value['year'] == $previousYear.'-'.$currentYear){

                            $found++;
                            $forStart = $explY[0];
                            $forEnd = $explY[0] - 1;

                            $forStart = $forStart.'-03-31';
                            $forEnd = $forEnd.'-04-01';
                            
                            $respData['year_code'] = date('y', strtotime($forEnd)).'-'.date('y', strtotime($forStart));
                            $respData['year'] = date('Y', strtotime($forEnd)).'-'.date('Y', strtotime($forStart));
                            $respData['startdate'] = date('d/m/Y', strtotime($forEnd));
                            $respData['enddate'] = date('d/m/Y', strtotime($forStart));
                        }
                    }
                }

            }else{
                foreach ($exists_data as $key => $value) {
                    
                    if($maxcompanyYear != null){

                        $explY = explode('-', $maxcompanyYear);
                       
                        $found++;
                        $forStart = $explY[1];
                        $forEnd = $explY[1] + 1;

                        $forStart = $forStart.'-04-01';
                        $forEnd = $forEnd.'-03-31';
                        
                        $respData['year_code'] = date('y', strtotime($forStart)).'-'.date('y', strtotime($forEnd));
                        $respData['year'] = date('Y', strtotime($forStart)).'-'.date('Y', strtotime($forEnd));
                        $respData['startdate'] = date('d/m/Y', strtotime($forStart));
                        $respData['enddate'] = date('d/m/Y', strtotime($forEnd));

                    }else{
                        
                        $explY = explode('-',$value['year']);
                        if($value['year'] == $currentYear.'-'.$nextYear){
    
                            $found++;
                            $forStart = $explY[1];
                            $forEnd = $explY[1] + 1;

                            $forStart = $forStart.'-04-01';
                            $forEnd = $forEnd.'-03-31';
                            
                            $respData['year_code'] = date('y', strtotime($forStart)).'-'.date('y', strtotime($forEnd));
                            $respData['year'] = date('Y', strtotime($forStart)).'-'.date('Y', strtotime($forEnd));
                            $respData['startdate'] = date('d/m/Y', strtotime($forStart));
                            $respData['enddate'] = date('d/m/Y', strtotime($forEnd));
                        }
                    }
                   
                }
            }

            if($found == 0){
                if($request->type == "reverse"){

                    $respData['year_code'] = date('y', strtotime($previousYear.'-04-01')).'-'.date('y', strtotime($currentYear.'-03-31'));
                    $respData['year'] = $previousYear.'-'.$currentYear;
                    $respData['startdate'] = '01/04/'.$previousYear;
                    $respData['enddate'] = '31/03/'.$currentYear;
                }else{
    
                    $respData['year_code'] = date('y', strtotime($currentYear.'-04-01')).'-'.date('y', strtotime($nextYear.'-03-31'));
                    $respData['year'] = $currentYear.'-'.$nextYear;
                    $respData['startdate'] = '01/04/'.$currentYear;
                    $respData['enddate'] = '31/03/'.$nextYear;
                } 
            }

        }else{

            if($request->type == "reverse"){

                $respData['year_code'] = date('y', strtotime($previousYear.'-04-01')).'-'.date('y', strtotime($currentYear.'-03-31'));
                $respData['year'] = $previousYear.'-'.$currentYear;
                $respData['startdate'] = '01/04/'.$previousYear;
                $respData['enddate'] = '31/03/'.$currentYear;
            }else{

                $respData['year_code'] = date('y', strtotime($currentYear.'-04-01')).'-'.date('y', strtotime($nextYear.'-03-31'));
                $respData['year'] = $currentYear.'-'.$nextYear;
                $respData['startdate'] = '01/04/'.$currentYear;
                $respData['enddate'] = '31/03/'.$nextYear;
            } 
        }

        return response()->json([
            'response_code' => '1',
            'response_data' => $respData,
        ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CompanyYear  $CompanyYear
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,CompanyYear $CompanyYear)
    {
        
        // $defYear = CompanyYear::select(['id'])->orderBy('sequence','desc')->first();
        // if($defYear != null){
        //     $defYear = $defYear->id;
        // }else{
        //     $defYear = 0;
        // }
        
        // if($request->id == $defYear){
        //     return response()->json([
        //         'response_code' => '0',
        //         'response_message' => "Default Year can not be Deleted."
        //     ]);
        // }
  
        if($request->id == session('default_year_id')){
            return response()->json([
                'response_code' => '0',
                'response_message' => "Selected Year Can Not Be Deleted."
            ]);
        }
        

        try{
            CompanyYear::destroy($request->id);
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        }catch(\Exception $e){
            report($e);
            if(isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451){
                $error_msg = "This Is Used Somewhere, You Can't Delete";
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