<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FeasibilityReview;
use App\Models\Inquiry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DataTables;
use Date;
use App\Models\Admin;
use App\Models\File;
use App\Models\EstimationCosting;
use App\Models\QuotationDetails;


class FeasibilityReviewController extends Controller
{
    //
    public function manage()
    {
        return view('manage.manage-feasibility_review');
    }

    public function index(FeasibilityReview $feasibility_review, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $feasibility_data = FeasibilityReview::select([
            'feasibility_review.fr_id',
            'feasibility_review.fr_number',
            'feasibility_review.fr_sequence',
            'feasibility_review.fr_date',
            'feasibility_review.fr_inqd_id',
            'inquiry.inq_id',
            'inquiry.inq_number',
            'inquiry.inq_date',
            'customers.customer_code',
            'customers.customer',
            'inquiry.inq_ref_no_date',
            'inquiry_details.inqd_test_method',
            'inquiry_details.inqd_type_of_job_id',
            'type_of_job.type_of_job',
            'inquiry_details.inqd_job_description_id',
            'job_descriptions.job_description',
            'inquiry_details.inqd_part_id',
            'inquiry_details.inqd_part_no',
            //\DB::raw("IF(inquiry_details.inqd_part_no IS NOT NULL AND inquiry_details.inqd_part_no != '', inquiry_details.inqd_part_no, IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no)) as part"),
            'feasibility_review.fr_result_id',
            'feasibility_review.fr_suggest_method_id',
            'feasibility_review.fr_reason_id',
            'reason.reason_name',
            'feasibility_review.created_on',
            'feasibility_review.created_by',
            'feasibility_review.last_by',
            'feasibility_review.last_on',
            'prepared_by.person_name as fr_prepared_by_name',
            'reviewed_by.person_name as fr_reviewed_by_name',
        ])
        ->leftJoin('inquiry_details','inquiry_details.inqd_id','=','feasibility_review.fr_inqd_id')
        ->leftJoin('reason','reason.id','=','feasibility_review.fr_reason_id')
        ->leftJoin('inquiry','inquiry.inq_id','=','inquiry_details.inq_id')
        ->leftJoin('customers','customers.id','=','inquiry.inq_customer_id')
        ->leftJoin('type_of_job','type_of_job.id','=','inquiry_details.inqd_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','inquiry_details.inqd_job_description_id')
       // ->leftJoin('part','part.part_id','=','inquiry_details.inqd_part_id')
        ->leftJoin('admin as prepared_by','prepared_by.id','=','feasibility_review.fr_prepared_by_id')
        ->leftJoin('admin as reviewed_by','reviewed_by.id','=','feasibility_review.fr_reviewed_by_id')
        ->where('feasibility_review.year_id', $year_data->id);
        $dataTable = DataTables::of($feasibility_data)
        // ->filterColumn('part.part', function($query, $keyword) {
        //     $keyword = trim($keyword);
        //     $query->whereRaw("IF(inquiry_details.inqd_part_no IS NOT NULL AND inquiry_details.inqd_part_no != '', inquiry_details.inqd_part_no, IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no)) LIKE ?", ["%{$keyword}%"]);
        // })
        ->editColumn('fr_date', function($feasibility_data){
            if ($feasibility_data->fr_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $feasibility_data->fr_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->editColumn('inq_date', function($feasibility_data){
            if ($feasibility_data->inq_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $feasibility_data->inq_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('inquiry.inq_date', function ($q, $k) {
            applyDate($q, $k, 'inquiry.inq_date');
        })
        ->filterColumn('feasibility_review.fr_date', function ($q, $k) {
            applyDate($q, $k, 'feasibility_review.fr_date');
        })
        ->filterColumn('feasibility_review.fr_number', function ($q, $k) {
            applynumberprefix($q, $k, 'feasibility_review.fr_number');
        })
        ->filterColumn('inquiry.inq_number', function ($q, $k) {
            applynumberprefix($q, $k, 'inquiry.inq_number');
        })

        ->filterColumn('feasibility_review.fr_result_id', function ($query, $keyword) {
            $lowerKeyword = strtolower(trim($keyword));
            if (str_starts_with('feasible', $lowerKeyword)) {
                $query->where('fr_result_id', '=', "Feasible");
            }
            elseif (str_starts_with('not feasible', $lowerKeyword)) {
                $query->where('fr_result_id', '=', "Not Feasible");
            }
            elseif (str_starts_with('alternet method', $lowerKeyword)) {
                $query->where('fr_result_id', '=', "Alternet Method");
            }
            else {
                $query->where('fr_result_id', 'like', "%$keyword%");
            }
        })
        ->addColumn('options',function($feasibility_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("feasibility_review", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-feasibility_review"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("feasibility_review", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'feasibility_review');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {

        
        // dd($request->all());
        $year_data = getCurrentYearData();

        $images = '';
        $blobImage = '';
        if($request->fr_file_upload_doc)
        {
            $file = new File();
            $isFound =  $file->getFileFromTemp($request->fr_file_upload_doc,$prefix = 'feasibility_review');
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

        $existNumber = FeasibilityReview::where([['fr_sequence',  $request->fr_sequence],['fr_number',$request->fr_number],
            ['year_id',$year_data->id]])->first();
        
            if($existNumber){
                $latestNo = $this->getLatestFeasibilityNumber($request);              
                $tmp =  $latestNo->getContent();
                $area = json_decode($tmp, true);
                $fr_number =   $area['latest_no'];
                $fr_sequence = $area['number'];
            }else{
                $fr_number = $request->fr_number;
                $fr_sequence = $request->fr_sequence;
            }
        DB::beginTransaction();
        try
        {

            $feasibility_data = FeasibilityReview::create([
                'fr_sequence' => $fr_sequence !="" ? $fr_sequence : null,
                'fr_number'   => $fr_number !="" ? $fr_number : null,   
                'fr_date' => isset($request->fr_date) ? Date::createFromFormat('d/m/Y', $request->fr_date)->format('Y-m-d') : null,
                'fr_inqd_id'                 => $request->inqd_id !="" ? $request->inqd_id : null,
                'fr_result_id'         => $request->fr_result_id !="" ? $request->fr_result_id : null,
                'fr_feasibility_review'                   => $request->fr_feasibility_review !="" ? $request->fr_feasibility_review : null,
                'fr_suggest_method_id'                     => $request->inqd_test_method !="" ? $request->inqd_test_method : null,
                'fr_reason_id'        => $request->fr_reason_id !="" ? $request->fr_reason_id : null,
                'fr_estimation'       => $request->fr_estimation !="" ? $request->fr_estimation : null,
                'fr_costing'          => $request->fr_costing !="" ? $request->fr_costing : null,
                'fr_upload_file'              => $images !="" ? $images : null,
                'fr_upload_file_blob_image'=> $blobImage !="" ? $blobImage : null,
                'fr_prepared_by_id'        => $request->fr_prepared_by_id  !="" ? $request->fr_prepared_by_id : null,
                'fr_reviewed_by_id'           => $request->fr_reviewed_by_id  !="" ? $request->fr_reviewed_by_id : null,
                'year_id'         => $year_data->id,
                'company_id'      => Auth::user()->company_id,
                'created_on'      => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'      => Auth::user()->id,

            ]);
            unset($blobImage);

            if($feasibility_data->save())
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
       
         $feasibility_data =  DB::select('CALL feasibility_review_master(?)',  [$request->id]);
         if (!empty($feasibility_data)) {
                    $feasibility_data = $feasibility_data[0];
                    $feasibility_data->inq_date = $feasibility_data->inq_date != "" ? Date::createFromFormat('Y-m-d',  $feasibility_data->inq_date)->format('d/m/Y') : "";

                    $feasibility_data->fr_date = $feasibility_data->fr_date != "" ? Date::createFromFormat('Y-m-d',  $feasibility_data->fr_date)->format('d/m/Y') : "";
                   
                    if(isset($feasibility_data->cmp_logo)){
                        $feasibility_data->cmp_logo = base64_encode($feasibility_data->cmp_logo);
                    }
                    // if(isset($feasibility_data->inqd_file_upload_blob_image)){
                    //     $feasibility_data->inqd_file_upload_blob_image = base64_encode($feasibility_data->inqd_file_upload_blob_image);
                    // }
                    // if(isset($feasibility_data->fr_upload_file_blob_image)){
                    //     $feasibility_data->fr_upload_file_blob_image = base64_encode($feasibility_data->fr_upload_file_blob_image);
                    // }

                }
                $Feasibility_in_use = EstimationCosting::select('estimation_costing.ec_fr_id')->where('estimation_costing.ec_fr_id',$request->id)
                ->first();
                if($Feasibility_in_use != null && $Feasibility_in_use != "")
                {
                    $feasibility_data->in_use = true;
                }
                else
                {
                    $feasibility_data->in_use = false;
                }
        if($feasibility_data){
            return response()->json([
                'feasibility_data'         => $feasibility_data,
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
        DB::beginTransaction();
        // dd($request->all());
        try{

            /* -----------------------------------------------------------
            UPDATE FEASIBILITY REVIEW MAIN RECORD
            ----------------------------------------------------------- */

            $year_data = getCurrentYearData();
                  
            $validated = $request->validate([       
                'fr_sequence' => ['required', 'max:155', Rule::unique('feasibility_review')->where(function ($query) use ($request, $year_data) { $query->where('year_id', '=', $year_data->id);})->ignore($request->id, 'fr_id')
                ],
                'fr_number' => ['required','max:155',Rule::unique('feasibility_review')->where(function ($query) use ($request, $year_data) { $query->where('year_id', '=', $year_data->id); })->ignore($request->id, 'fr_id')
                ],
                ], [
                'fr_number.unique' => 'Feasibility Review No. Already Exists',
                'fr_number.required' => 'Please Enter Feasibility Review  No.',
                
            ]);


            $imgs = FeasibilityReview::where('fr_id', $request->id)->value('fr_upload_file');

            $file = new File();
            $images = '';
            $blobImage = '';

            if($request->fr_file_upload_doc != '' && $request->fr_file_upload_doc != null){
                if($imgs && !empty($imgs)){
                    if($imgs != $request->fr_file_upload_doc){
                            $file->delete_file($imgs);
                    }                 
                }      
                
                $isFound = $file->getFileFromTemp($request->fr_file_upload_doc,'feasibility_review');

                if($isFound !== false){                    
                    $images = $isFound;
                    $filePath = storage_path('app/public/' . $images);
                    $blobImage = file_exists($filePath) ? file_get_contents($filePath) : null;
                }else{
                    $images = $request->fr_file_upload_doc;
                    $filePath = storage_path('app/public/' . $images);
                    $blobImage = $blobImage = file_get_contents($filePath);
                }
            }else{
                if($imgs && !empty($imgs)){
                    $file->delete_file($imgs);
                }  
            }              
            
            $feasibility_data = FeasibilityReview::where('fr_id', $request->id)->update([

                'fr_sequence' => $request->fr_sequence ?? null,
                'fr_number'   => $request->fr_number ?? null,   
                'fr_date' => isset($request->fr_date) ? Date::createFromFormat('d/m/Y', $request->fr_date)->format('Y-m-d') : null,
                'fr_inqd_id'                 => $request->inqd_id !="" ? $request->inqd_id : null,
                'fr_result_id'         => $request->fr_result_id !="" ? $request->fr_result_id : null,
                'fr_feasibility_review'                   => $request->fr_feasibility_review !="" ? $request->fr_feasibility_review : null,
                'fr_suggest_method_id'                     => $request->inqd_test_method !="" ? $request->inqd_test_method : null,
                'fr_reason_id'        => $request->fr_reason_id !="" ? $request->fr_reason_id : null,
                'fr_estimation'       => $request->fr_estimation !="" ? $request->fr_estimation : null,
                'fr_costing'          => $request->fr_costing !="" ? $request->fr_costing : null,
                'fr_upload_file'              => $images !="" ? $images : null,
                'fr_upload_file_blob_image'=> $blobImage !="" ? $blobImage : null,
                'fr_prepared_by_id'        => $request->fr_prepared_by_id  !="" ? $request->fr_prepared_by_id : null,
                'fr_reviewed_by_id'           => $request->fr_reviewed_by_id  !="" ? $request->fr_reviewed_by_id : null,
                'last_on'       => Carbon::now('Asia/Kolkata'),
                'last_by'       => Auth::id(),
            ]);

            if(!$feasibility_data){
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }

            DB::commit();

            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('update_success'),
            ]);
            

        } catch (\Exception $e) {

            DB::rollBack();
            report($e);

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
        try{


            $feasibility = FeasibilityReview::select('fr_upload_file', 'fr_inqd_id')
            ->where('fr_id', $request->id)
            ->first();

            if (!$feasibility) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('delete_error'),
                ]);
            }

            if (QuotationDetails::where('quotd_inqd_id', $feasibility->fr_inqd_id)->exists()) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Feasibility Review Is Used In Quotation.",
                ]);
            }

            $estimation_data = EstimationCosting::where('ec_fr_id','=',$request->id)->get(); 
            if($estimation_data->isNotEmpty())
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Feasibility Review Is Used In Estimation Costing.",
                ]);
            }


            if (!empty($feasibility->fr_upload_file)) {
                $file = new File();
                $file->delete_file($feasibility->fr_upload_file);
            }                    

            // $delete_image = FeasibilityReview::where('fr_id', $request->id)->value('fr_upload_file');

            // if($delete_image && !empty($delete_image)){
            //     $file = new File();
            //     $file->delete_file($delete_image);
            // }
                
            FeasibilityReview::where('fr_id',$request->id)->delete();

            DB::commit();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        }catch(\Exception $e){
            report($e);
            DB::rollBack();
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

    public function getLatestFeasibilityNumber(Request $request)
    {
        $modal  =  FeasibilityReview::class;
        $sequence = 'fr_sequence';           
        $sup_num_format = getMarketingLatestSequence($modal,$sequence);   
       
        return response()->json([
          'response_code' => 1,
          'latest_no'     => $sup_num_format['format'],
          'number'        => $sup_num_format['isFound'],
      ]);
    }

    public function getInquiryListForFeasibility(Request $request){

        $yearIds = getCompanyYearIdsToTill();

        $used_inqd_ids = FeasibilityReview::select('fr_inqd_id')
        ->pluck('fr_inqd_id')
        ->toArray();

        $inq_data = Inquiry::select('inquiry.inq_id','inquiry_details.inqd_id','inquiry.inq_date','inquiry.inq_number','inquiry.inq_customer_id','customers.customer_code','customers.customer','inquiry.inq_ref_no_date','inquiry_details.inqd_test_method','inquiry_details.inqd_type_of_job_id','type_of_job.type_of_job','inquiry_details.inqd_job_description_id','job_descriptions.job_description','inquiry_details.inqd_part_id','inquiry_details.inqd_part_no',
        //\DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
        'inquiry_details.inqd_process_at','inquiry_details.inqd_quantity','inquiry_details.inqd_unit_id','unit.unit','inquiry.inq_sp_note')
        ->leftJoin('inquiry_details','inquiry_details.inq_id','=','inquiry.inq_id')
        ->leftJoin('customers','customers.id','=','inquiry.inq_customer_id')
        ->leftJoin('type_of_job','type_of_job.id','=','inquiry_details.inqd_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','inquiry_details.inqd_job_description_id')
       // ->leftJoin('part','part.part_id','=','inquiry_details.inqd_part_id')
        ->leftJoin('unit','unit.id','=','inquiry_details.inqd_unit_id')
        ->where('inquiry_details.inqd_feasibility_required','=',"Yes")
        ->whereNotIn('inquiry_details.inqd_id',$used_inqd_ids)
        ->whereIn('inquiry.year_id',$yearIds)
        ->get();

        if ($inq_data != null) {
           
            foreach ($inq_data as $cpKey => $cpVal) {

                if ($cpVal->inq_date != null) {
                    $cpVal->inq_date = Date::createFromFormat('Y-m-d', $cpVal->inq_date)->format('d/m/Y');
                }
            }

        }

        if ($inq_data != null) {
            return response()->json([
                'response_code' => '1',
                'inq_data' => $inq_data,


            ]);
        } else {
            return response()->json([
                'response_code' => '1',
                'inq_data' => []
            ]);
        }
    }


    public function getInquiryPartDataForFeasibility(Request $request){

        $yearIds = getCompanyYearIdsToTill();

        // $inqd_ids = explode(',', $request->inqd_ids);
        // $used_inqd_ids = FeasibilityReview::select('fr_inqd_id')
        // ->pluck('fr_inqd_id')
        // ->toArray();

        $inq_data = Inquiry::select('inquiry.inq_id','inquiry_details.inqd_id','inquiry.inq_date','inquiry.inq_number','inquiry.inq_customer_id','customers.customer_code','customers.customer','inquiry.inq_ref_no_date','inquiry_details.inqd_test_method','inquiry_details.inqd_type_of_job_id','type_of_job.type_of_job','inquiry_details.inqd_job_description_id','job_descriptions.job_description','inquiry_details.inqd_part_id','inquiry_details.inqd_part_no',
        
        //\DB::raw("IF(inquiry_details.inqd_part_no IS NOT NULL AND inquiry_details.inqd_part_no != '', inquiry_details.inqd_part_no, IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no)) as part"),

        'inquiry_details.inqd_process_at','inquiry_details.inqd_quantity','inquiry_details.inqd_unit_id','unit.unit','inquiry.inq_sp_note','inquiry_details.inqd_description','inquiry_details.inqd_remark','inquiry_details.inqd_feasibility_required','inquiry_details.inqd_file_upload')
        ->leftJoin('inquiry_details','inquiry_details.inq_id','=','inquiry.inq_id')
        ->leftJoin('customers','customers.id','=','inquiry.inq_customer_id')
        ->leftJoin('type_of_job','type_of_job.id','=','inquiry_details.inqd_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','inquiry_details.inqd_job_description_id')
        //->leftJoin('part','part.part_id','=','inquiry_details.inqd_part_id')
        ->leftJoin('unit','unit.id','=','inquiry_details.inqd_unit_id')
        ->where('inquiry_details.inqd_feasibility_required','=',"Yes")
        ->whereIn('inquiry.year_id',$yearIds)
        ->where('inquiry_details.inqd_id','=',$request->inqd_ids)
        // ->whereNotIn('inquiry_details.inqd_id',$used_inqd_ids)
        ->get();

        if ($inq_data != null) {

            foreach ($inq_data as $cpKey => $cpVal) {

                if ($cpVal->inq_date != null) {
                    $cpVal->inq_date = Date::createFromFormat('Y-m-d', $cpVal->inq_date)->format('d/m/Y');
                }
            
            }

        }
        if ($inq_data != null) {
            return response()->json([
                'response_code' => '1',
                'inq_data' => $inq_data,


            ]);
        } else {
            return response()->json([
                'response_code' => '1',
                'inq_data' => []
            ]);
        }
    }



}