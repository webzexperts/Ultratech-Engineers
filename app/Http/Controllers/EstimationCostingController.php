<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EstimationCosting;
use App\Models\Inquiry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DataTables;
use Date;
use App\Models\Admin;
use App\Models\File;
use App\Models\QuotationDetails;

class EstimationCostingController extends Controller
{
    public function manage()
    {
        return view('manage.manage-estimation_costing');
    }

    public function index(EstimationCosting $feasibility_review, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $estimation_data = EstimationCosting::select([
            'estimation_costing.ec_id',
            'estimation_costing.ec_number',
            'estimation_costing.ec_date',
            'estimation_costing.ec_inqd_id',
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
            \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'estimation_costing.ec_estimation',
            'estimation_costing.ec_costing',
            'estimation_costing.created_on',
            'estimation_costing.created_by',
            'estimation_costing.last_by',
            'estimation_costing.last_on',
            'prepared_by.person_name as ec_prepared_by_name', 
            'reviewed_by.person_name as ec_reviewed_by_name',
        ])
        ->leftJoin('inquiry_details','inquiry_details.inqd_id','=','estimation_costing.ec_inqd_id')
        ->leftJoin('inquiry','inquiry.inq_id','=','inquiry_details.inq_id')
        ->leftJoin('customers','customers.id','=','inquiry.inq_customer_id')
        ->leftJoin('type_of_job','type_of_job.id','=','inquiry_details.inqd_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','inquiry_details.inqd_job_description_id')
        ->leftJoin('part','part.part_id','=','inquiry_details.inqd_part_id')
        ->leftJoin('admin as prepared_by','prepared_by.id','=','estimation_costing.ec_prepared_by_id')
        ->leftJoin('admin as reviewed_by','reviewed_by.id','=','estimation_costing.ec_reviewed_by_id')
        ->where('estimation_costing.year_id', $year_data->id);
        $dataTable = DataTables::of($estimation_data)
        ->filterColumn('part.part', function($query, $keyword) {
            $keyword = trim($keyword);
            $query->whereRaw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) LIKE ?", ["%{$keyword}%"]);
        })
        ->editColumn('ec_date', function($estimation_data){
            if ($estimation_data->ec_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $estimation_data->ec_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->editColumn('inq_date', function($estimation_data){
            if ($estimation_data->inq_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $estimation_data->inq_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('inquiry.inq_date', function ($q, $k) {
            applyDate($q, $k, 'inquiry.inq_date');
        })
        ->filterColumn('estimation_costing.ec_date', function ($q, $k) {
            applyDate($q, $k, 'estimation_costing.ec_date');
        })
        ->filterColumn('estimation_costing.ec_number', function ($q, $k) {
            applynumber($q, $k, 'estimation_costing.ec_number');
        })
        ->filterColumn('inquiry.inq_number', function ($q, $k) {
            applynumber($q, $k, 'inquiry.inq_number');
        })
        
        ->addColumn('options',function($estimation_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("estimation_costing", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-estimation_costing"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("estimation_costing", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'estimation_costing');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {        
        $year_data = getCurrentYearData();

        $validated = $request->validate([
            'ec_number' => [
                'required',
                'max:155',
                'unique:estimation_costing,ec_number',
            ],
        ], [
            'ec_number.unique' => 'Estimation & Costing No. Already Exists',
            'ec_number.required' => 'Please Enter Estimation & Costing No.',
        ]);   


        $images = '';
        $blobImage = '';
        if($request->ec_file_upload_doc)
        {
            $file = new File();
            $isFound =  $file->getFileFromTemp($request->ec_file_upload_doc,$prefix = 'estimation_costing');
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

        $existNumber = EstimationCosting::where([['ec_sequence',  $request->ec_sequence],['ec_number',$request->ec_number],
            ['year_id',$year_data->id]])->first();
        
            if($existNumber){
                $latestNo = $this->getLatestEstimationCostingNumber($request);              
                $tmp =  $latestNo->getContent();
                $area = json_decode($tmp, true);
                $ec_number =   $area['latest_no'];
                $ec_sequence = $area['number'];
            }else{
                $ec_number = $request->ec_number;
                $ec_sequence = $request->ec_sequence;
            }
        DB::beginTransaction();
        try
        {
            $estimation_data = EstimationCosting::create([
                'ec_sequence'               => $ec_sequence !="" ? $ec_sequence : null,
                'ec_number'                 => $ec_number !="" ? $ec_number : null,   
                'ec_date'                   => isset($request->ec_date) ? Date::createFromFormat('d/m/Y', $request->ec_date)->format('Y-m-d') : null,
                'ec_inqd_id'                => $request->inqd_id !="" ? $request->inqd_id : null,
                'ec_fr_id'                => $request->ec_fr_id !="" ? $request->ec_fr_id : null,
                'ec_estimation'             => $request->ec_estimation !="" ? $request->ec_estimation : null,             
                'ec_costing'                => $request->ec_costing !="" ? $request->ec_costing : null,             
                'ec_upload_file'            => $images !="" ? $images : null,
                'ec_upload_file_blob_image' => $blobImage !="" ? $blobImage : null,
                'ec_prepared_by_id'         => $request->ec_prepared_by_id  !="" ? $request->ec_prepared_by_id : null,
                'ec_reviewed_by_id'         => $request->ec_reviewed_by_id  !="" ? $request->ec_reviewed_by_id : null,
                'year_id'                   => $year_data->id,
                'company_id'                => Auth::user()->company_id,
                'created_on'                => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'                => Auth::user()->id,

            ]);
            unset($blobImage);

            if($estimation_data->save())
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
       
         $estimation_data =  DB::select('CALL estimation_costing_master(?)',  [$request->id]);
         if (!empty($estimation_data)) {
                    $estimation_data = $estimation_data[0];
                    $estimation_data->inq_date = $estimation_data->inq_date != "" ? Date::createFromFormat('Y-m-d',  $estimation_data->inq_date)->format('d/m/Y') : "";

                    // $estimation_data->fr_date = $estimation_data->fr_date != "" ? Date::createFromFormat('Y-m-d',  $estimation_data->fr_date)->format('d/m/Y') : "";

                    $estimation_data->ec_date = $estimation_data->ec_date != "" ? Date::createFromFormat('Y-m-d',  $estimation_data->ec_date)->format('d/m/Y') : "";
                   
                    if(isset($estimation_data->cmp_logo)){
                        $estimation_data->cmp_logo = base64_encode($estimation_data->cmp_logo);
                    }
                    // if(isset($feasibility_data->inqd_file_upload_blob_image)){
                    //     $feasibility_data->inqd_file_upload_blob_image = base64_encode($feasibility_data->inqd_file_upload_blob_image);
                    // }
                    // if(isset($feasibility_data->fr_upload_file_blob_image)){
                    //     $feasibility_data->fr_upload_file_blob_image = base64_encode($feasibility_data->fr_upload_file_blob_image);
                    // }

            }
        if($estimation_data){
            return response()->json([
                'estimation_data'         => $estimation_data,
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
        try{

            $year_data = getCurrentYearData();
                  
            $validated = $request->validate([            
                'ec_number' => ['required','max:155',Rule::unique('estimation_costing')->where(function ($query) use ($request, $year_data) { $query->where('year_id', '=', $year_data->id); })->ignore($request->id, 'ec_id')
                ],
                ], [
                'ec_number.unique' => 'Estimation & Costing No. Already Exists',
                'ec_number.required' => 'Please Enter Estimation & Costing No.',
                
            ]);           


            $imgs = EstimationCosting::where('ec_id', $request->id)->value('ec_upload_file');

            $file = new File();
            $images = '';
            $blobImage = '';

            if($request->ec_file_upload_doc != '' && $request->ec_file_upload_doc != null){
                if($imgs && !empty($imgs)){
                    if($imgs != $request->ec_file_upload_doc){
                            $file->delete_file($imgs);
                    }                 
                }      
                
                $isFound = $file->getFileFromTemp($request->ec_file_upload_doc,'estimation_costing');

                if($isFound !== false){                    
                    $images = $isFound;
                    $filePath = storage_path('app/public/' . $images);
                    // dd($filePath);
                    $blobImage = file_exists($filePath) ? file_get_contents($filePath) : null;
                }else{
                    $images = $request->ec_file_upload_doc;
                    $filePath = storage_path('app/public/' . $images);
                    $blobImage = $blobImage = file_get_contents($filePath);
                }
            }else{
                if($imgs && !empty($imgs)){
                    $file->delete_file($imgs);
                }  
            }  
            
            
            $estimation_data = EstimationCosting::where('ec_id', $request->id)->update([
                'ec_sequence'               => $request->ec_sequence ?? null,
                'ec_number'                 => $request->ec_number ??  null,   
                'ec_date'                   => isset($request->ec_date) ? Date::createFromFormat('d/m/Y', $request->ec_date)->format('Y-m-d') : null,
                'ec_inqd_id'                => $request->inqd_id !="" ? $request->inqd_id : null,
                'ec_fr_id'                => $request->ec_fr_id !="" ? $request->ec_fr_id : null,
                'ec_estimation'             => $request->ec_estimation !="" ? $request->ec_estimation : null,             
                'ec_costing'                => $request->ec_costing !="" ? $request->ec_costing : null,             
                'ec_upload_file'            => $images !="" ? $images : null,
                'ec_upload_file_blob_image' => $blobImage !="" ? $blobImage : null,
                'ec_prepared_by_id'         => $request->ec_prepared_by_id  !="" ? $request->ec_prepared_by_id : null,
                'ec_reviewed_by_id'         => $request->ec_reviewed_by_id  !="" ? $request->ec_reviewed_by_id : null,
                'last_on'       => Carbon::now('Asia/Kolkata'),
                'last_by'       => Auth::id(),
            ]);

            if(!$estimation_data){
                DB::rollBack();
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
        try{
            
            $estimation = EstimationCosting::select('ec_upload_file', 'ec_inqd_id')
            ->where('ec_id', $request->id)
            ->first();

            if (!$estimation) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Record not found',
                ]);
            }

            if (QuotationDetails::where('quotd_inqd_id', $estimation->ec_inqd_id)->exists()) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Estimation & Costing Is Used In Quotation.",
                ]);
            }

            if (!empty($estimation->ec_upload_file)) {
                $file = new File();
                $file->delete_file($estimation->ec_upload_file);
            }

            
            EstimationCosting::where('ec_id',$request->id)->delete();


            // $ec_inqd_id = EstimationCosting::where('ec_id', $request->id)->value('ec_inqd_id');
            // if (QuotationDetails::where('quotd_inqd_id', $ec_inqd_id)->exists()) {
            //     return response()->json([
            //         'response_code' => '0',
            //         'response_message' => "You Can't Delete, Estimation & Costing Is Used In Quotation.",
            //     ]);
            // }

            // $delete_image = EstimationCosting::where('ec_id', $request->id)->value('ec_upload_file');

            // if($delete_image && !empty($delete_image)){
            //     $file = new File();
            //     $file->delete_file($delete_image);
            // }

            

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
    

    public function getLatestEstimationCostingNumber(Request $request)
    {
        $modal  =  EstimationCosting::class;
        $sequence = 'ec_sequence';           
        $sup_num_format = getMarketingLatestSequence($modal,$sequence);   
       
        return response()->json([
          'response_code' => 1,
          'latest_no'     => $sup_num_format['format'],
          'number'        => $sup_num_format['isFound'],
      ]);
    }


    public function getPendingInquiryListForEstimationCosting(Request $request){

        $yearIds = getCompanyYearIdsToTill();

        $inq_data = Inquiry::select(
        'inquiry.inq_id',
        'inquiry_details.inqd_id',
        'inquiry.inq_date',
        'inquiry.inq_number',
        'inquiry.inq_customer_id',
        'customers.customer_code',
        'customers.customer',
        'inquiry.inq_ref_no_date',
        'inquiry_details.inqd_test_method',
        'inquiry_details.inqd_type_of_job_id',
        'type_of_job.type_of_job',
        'inquiry_details.inqd_job_description_id',
        'job_descriptions.job_description',
        'inquiry_details.inqd_part_id',
        \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
        'inquiry_details.inqd_process_at',
        'inquiry_details.inqd_quantity',
        'inquiry_details.inqd_unit_id',
        'unit.unit',
        'inquiry.inq_sp_note',
        'inquiry_details.inqd_description',
        'inquiry_details.inqd_remark'
        )
        ->join('inquiry_details', 'inquiry_details.inq_id', '=', 'inquiry.inq_id')
        ->leftJoin('feasibility_review', 'feasibility_review.fr_inqd_id', '=', 'inquiry.inq_customer_id')
        ->leftJoin('customers', 'customers.id', '=', 'inquiry.inq_customer_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'inquiry_details.inqd_type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'inquiry_details.inqd_job_description_id')
        ->leftJoin('part', 'part.part_id', '=', 'inquiry_details.inqd_part_id')
        ->leftJoin('unit', 'unit.id', '=', 'inquiry_details.inqd_unit_id')

        // feasibility required
        ->where('inquiry_details.inqd_feasibility_required', 'Yes')

        // estimation required in feasibility review
        ->whereExists(function ($q) {
            $q->select(DB::raw(1))
            ->from('feasibility_review')
            ->join('inquiry_details as id2', 'id2.inqd_id', '=', 'feasibility_review.fr_inqd_id')
            ->whereColumn('feasibility_review.fr_inqd_id', 'inquiry_details.inqd_id')
            ->where('id2.inqd_estimation_required', 'Yes')
            ->where('feasibility_review.fr_result_id', '=', 'Feasible');
        })

        // estimation not yet done
        ->whereNotExists(function ($q) {
            $q->select(DB::raw(1))
            ->from('estimation_costing')
            ->whereColumn('estimation_costing.ec_inqd_id', 'inquiry_details.inqd_id');
        })

        ->whereIn('inquiry.year_id', $yearIds)
        ->get();
        // dd($inq_data);


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


    public function getInquiryPartDataForEstimationCosting(Request $request){

        $yearIds = getCompanyYearIdsToTill();

        // $inqd_ids = explode(',', $request->inqd_ids);
        // $used_inqd_ids = FeasibilityReview::select('fr_inqd_id')
        // ->pluck('fr_inqd_id')
        // ->toArray();

        $inq_data = Inquiry::select('inquiry.inq_id','inquiry_details.inqd_id','inquiry.inq_date','inquiry.inq_number','inquiry.inq_customer_id','customers.customer_code','customers.customer','inquiry.inq_ref_no_date','inquiry_details.inqd_test_method','inquiry_details.inqd_type_of_job_id','type_of_job.type_of_job','inquiry_details.inqd_job_description_id','job_descriptions.job_description','inquiry_details.inqd_part_id',\DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),'inquiry_details.inqd_process_at','inquiry_details.inqd_quantity','inquiry_details.inqd_unit_id','unit.unit','inquiry.inq_sp_note','inquiry_details.inqd_description','inquiry_details.inqd_remark','inquiry_details.inqd_feasibility_required','inquiry_details.inqd_file_upload','feasibility_review.fr_result_id','feasibility_review.fr_upload_file as fr_file_upload','feasibility_review.fr_id')
        ->leftJoin('inquiry_details','inquiry_details.inq_id','=','inquiry.inq_id')
        ->leftJoin('feasibility_review','feasibility_review.fr_inqd_id','=','inquiry_details.inqd_id')
        ->leftJoin('customers','customers.id','=','inquiry.inq_customer_id')
        ->leftJoin('type_of_job','type_of_job.id','=','inquiry_details.inqd_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','inquiry_details.inqd_job_description_id')
        ->leftJoin('part','part.part_id','=','inquiry_details.inqd_part_id')
        ->leftJoin('unit','unit.id','=','inquiry_details.inqd_unit_id')
        ->where('inquiry_details.inqd_feasibility_required','=',"Yes")

        // estimation required in feasibility review
        ->whereExists(function ($q) {
            $q->select(DB::raw(1))
            ->from('feasibility_review')
            ->join('inquiry_details as id2', 'id2.inqd_id', '=', 'feasibility_review.fr_inqd_id')
            ->whereColumn('feasibility_review.fr_inqd_id', 'inquiry_details.inqd_id')
            ->where('id2.inqd_estimation_required', 'Yes')
            ->where('feasibility_review.fr_result_id', '=', 'Feasible');
        })

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