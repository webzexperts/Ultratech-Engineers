<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\CustomerContacts;
use App\Models\Inquiry;
use App\Models\InquiryDetails;
use App\Models\FeasibilityReview;
use App\Models\QuotationDetails;
use App\Models\EstimationCosting;
use App\Models\InquiryShortClose;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DataTables;
use Date;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\File;

class InquiryController extends Controller
{
    public function manage()
    {
        return view('manage.manage-inquiry');
    }

    public function CustomerContactPersonData(Request $request)
    {
        $contactPersons = CustomerContacts::where('customer_id', $request->customer_id)->select('id', 'contact_person','contact_email','contact_mobile_no')->orderBy('contact_person', 'asc')->get();
        return response()->json([
            'kind_attention' => $contactPersons,
            'response_code' => '1'
        ]);
    }

    public function index(Inquiry $Inquiry, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $inquiry_data = InquiryDetails::select([
            'inquiry.inq_id',
            'inquiry.inq_sequence',
            'inquiry.inq_number',
            'inquiry.inq_date',
            'inquiry.inq_kind_attn_id',
            'inquiry.inq_ref_no_date',
            'inquiry.inq_sp_note',
            'inquiry.created_on',
            'inquiry.created_by',
            'inquiry.last_by',
            'inquiry.last_on',
            'customers.customer',
            'customers.customer_code',
            'customer_contacts.contact_person',
            'customer_contacts.contact_mobile_no',
            'customer_contacts.contact_email',
            'admin.person_name as user_name',
            'inquiry_details.inqd_id',
            'inquiry_details.inqd_test_method',
            'inquiry_details.inqd_job_description_id',
            'inquiry_details.inqd_part_no',
            'inquiry_details.inqd_process_at',
            'inquiry_details.inqd_description',
            'inquiry_details.inqd_quantity',
            'inquiry_details.inqd_feasibility_required',
            'inquiry_details.inqd_estimation_required',
            'inquiry_details.inqd_remark',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
           // \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'unit.unit'
        ])
        ->leftJoin('inquiry','inquiry.inq_id','=','inquiry_details.inq_id')
        ->leftJoin('customers','customers.id','=','inquiry.inq_customer_id')
        ->leftJoin('customer_contacts','customer_contacts.id','=','inquiry.inq_kind_attn_id')
        ->leftJoin('admin','admin.id','=','inquiry.inq_prepared_by_id')
        ->leftJoin('type_of_job','type_of_job.id','=','inquiry_details.inqd_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','inquiry_details.inqd_job_description_id')
        //->leftJoin('part','part.part_id','=','inquiry_details.inqd_part_id')
        ->leftJoin('unit','unit.id','=','inquiry_details.inqd_unit_id')
        ->where('inquiry.year_id', $year_data->id);
        $dataTable = DataTables::of($inquiry_data)
        ->filterColumn('inquiry.inq_number', function($query, $keyword) {
            applynumber($query, $keyword, 'inquiry.inq_number');
        })
        // ->filterColumn('part', function($query, $keyword) {
        //     $keyword = trim($keyword);
        //     $query->whereRaw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) LIKE ?", ["%{$keyword}%"]);
        // })
        ->editColumn('inq_date', function($inquiry_data){
            if ($inquiry_data->inq_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $inquiry_data->inq_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
         ->filterColumn('inquiry.inq_number', function ($q, $k) {
            applynumberprefix($q, $k, 'inquiry.inq_number');
        })
        ->filterColumn('inquiry.inq_date', function ($q, $k) {
            applyDate($q, $k, 'inquiry.inq_date');
        })
        ->editColumn('inqd_process_at', function($inquiry_data) {
            if ($inquiry_data->inqd_process_at != '') {
                if ($inquiry_data->inqd_process_at == 'In-House') {
                    $status = 'In-House';
                } else if ($inquiry_data->inqd_process_at == 'Outside') {
                    $status = 'Outside';
                } else {
                    $status = '';
                }
                return $status;
            } else {
                return '';
            }
        })
        ->editColumn('contact_person', function ($inquiry_data) {
            $output = '';
            if (!empty($inquiry_data->contact_person)) {
                $output .= $inquiry_data->contact_person;
            }

            if (!empty($inquiry_data->contact_email)) {
                $output .=' '.$inquiry_data->contact_email;
            }

            if (!empty($inquiry_data->contact_mobile_no)) {
                $output .=', '.$inquiry_data->contact_mobile_no;
            }

            return trim($output);
        })
        ->filterColumn('customer_contacts.contact_person', function($query, $keyword) {
            $keyword = trim($keyword);
            $query->whereRaw("
                CONCAT(
                    IFNULL(customer_contacts.contact_person, ''),
                    IF(customer_contacts.contact_email IS NOT NULL AND customer_contacts.contact_email != '', CONCAT(' ', customer_contacts.contact_email), ''),
                    IF(customer_contacts.contact_mobile_no IS NOT NULL AND customer_contacts.contact_mobile_no != '', CONCAT(', ', customer_contacts.contact_mobile_no), '')
                ) LIKE ?
            ", ["%{$keyword}%"]);
        })
        ->editColumn('inqd_quantity', function($inquiry_data) {
            // return $inquiry_data->inqd_quantity > 0 ? number_format((float)$inquiry_data->inqd_quantity, 3, '.','') : number_format((float) 0, 3, '.','');
            return $inquiry_data->inqd_quantity > 0 ? number_format((float)$inquiry_data->inqd_quantity, 2, '.','') : number_format((float) 0, 2, '.','');
        })
        ->filterColumn('inquiry_details.inqd_quantity', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('inqd_quantity')
                    ->orWhere('inqd_quantity', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(inqd_quantity AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('inqd_quantity', 'like', "%{$search}%");
            }
        })
        ->addColumn('options',function($inquiry_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("inquiry", "print")) {
                    $inq_no = !empty($inquiry_data->inq_number) ? '_' . str_replace('/', '_', $inquiry_data->inq_number) : "";
                    $cust_name = !empty($inquiry_data->customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $inquiry_data->customer) : "";
                    $pdfName   = 'Inquiry' . $inq_no . $cust_name;
                    $encodedId = base64_encode($inquiry_data->inq_id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=inquiry");
                    $action .= '<li><a class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }

                if (hasAccess("inquiry", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_inquiry"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("inquiry", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'inquiry');
        return $dataTable
        ->rawColumns(['options','contact_person','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $year_data = getCurrentYearData();
        $existNumber = Inquiry::where([['inq_sequence',  $request->inq_sequence],['inq_number',$request->inq_number],['year_id',$year_data->id]])->first();
        
        if($existNumber)
        {
            $latestNo = $this->getLatestInquiryNumber($request);
            $tmp =  $latestNo->getContent();
            $area = json_decode($tmp, true);
            $inq_number =   $area['latest_no'];
            $inq_sequence = $area['number'];
        }
        else
        {
            $inq_number = $request->inq_number;
            $inq_sequence = $request->inq_sequence;
        }

        DB::beginTransaction();
        try
        {
            $page_id = getMenuIdBassedOnDisplayName('inquiry');
            $LocationData = getCurrentLocation();
            $assign_format_no = getAssignFormateNoForTransaction($LocationData->location_id, $page_id->id, $request->inq_date);
            // dd($assign_format_no);
            $inquiry_data =  Inquiry::create([
                'inq_number' => $inq_number ?? null,
                'inq_sequence'   => $inq_sequence ?? null,
                'inq_date' => isset($request->inq_date) ? Date::createFromFormat('d/m/Y', $request->inq_date)->format('Y-m-d') : null,
                'inq_customer_id' => $request->inq_customer_id ?? null,
                'inq_kind_attn_id' => $request->inq_kind_attn_id ?? null,
                'inq_ref_no_date' => $request->inq_ref_no_date ?? null,
                'inq_sp_note' => $request->inq_sp_note ?? null,
                'inq_prepared_by_id' => $request->inq_prepared_by_id ?? null,
                'assign_format_no' => $assign_format_no,
                'year_id' => $year_data->id,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id,
            ]);

            $inquiry_details_data = $request->inquiry_details_data = json_decode($request->inquiry_details_data, true);
            if(!empty($inquiry_details_data))
            {
                foreach($inquiry_details_data as $ctKey => $ctVal)
                {
                    if($ctVal != null)
                    {
                        $images = '';
                        $blobImage = '';
                        if(!empty($ctVal['inqd_file_upload_doc']))
                        {
                            $file = new File();
                            $isFound =  $file->getFileFromTemp($ctVal['inqd_file_upload_doc'],$prefix = 'inquiry');
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

                        $inquiry_details_data = InquiryDetails::create([
                            'inq_id' => $inquiry_data->id,
                            'inqd_test_method' => !empty($ctVal['inqd_test_method']) ? $ctVal['inqd_test_method'] : null,
                            'inqd_type_of_job_id' => !empty($ctVal['inqd_type_of_job_id']) ? $ctVal['inqd_type_of_job_id'] : null,
                            'inqd_job_description_id' => !empty($ctVal['inqd_job_description_id']) ? $ctVal['inqd_job_description_id'] : null,
                            'inqd_part_no' => !empty($ctVal['inqd_part_no']) ? $ctVal['inqd_part_no'] : null,
                            'inqd_process_at' => !empty($ctVal['inqd_process_at']) ? $ctVal['inqd_process_at'] : null,
                            'inqd_description' => !empty($ctVal['inqd_description']) ? $ctVal['inqd_description'] : null,
                            'inqd_quantity' => !empty($ctVal['inqd_quantity']) ? $ctVal['inqd_quantity'] : null,
                            'inqd_unit_id' => !empty($ctVal['inqd_unit_id']) ? $ctVal['inqd_unit_id'] : null,
                            'inqd_feasibility_required' => !empty($ctVal['inqd_feasibility_required']) ? $ctVal['inqd_feasibility_required'] : null,
                            'inqd_estimation_required' => !empty($ctVal['inqd_estimation_required']) ? $ctVal['inqd_estimation_required'] : null,
                            'inqd_remark' => !empty($ctVal['inqd_remark']) ? $ctVal['inqd_remark'] : null,
                            'inqd_file_upload' =>  $images != '' ? $images : null,
                            'inqd_file_upload_blob_image' =>  $blobImage  ? $blobImage : null,
                            // 'inqd_file_upload' =>  $images,
                            // 'inqd_file_upload_blob_image' =>  $blobImage,
                            'status' => 'Y',
                        ]);
                        unset($blobImage);
                    }
                }
            }

            if($inquiry_data->save())
            {
                DB::commit();

                $inq_id = $inquiry_data->inq_id ?? $inquiry_data->id;
                $inq_no = !empty($inquiry_data->inq_number) ? '_' . str_replace('/', '_', $inquiry_data->inq_number) : "";
                $cust = DB::table('customers')->where('id', $inquiry_data->inq_customer_id)->value('customer');
                $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
                $pdf_name = 'Inquiry' . $inq_no . $cust_name;

                GeneratePdf($inq_id, $pdf_name, 'inquiry', 'add');
                $encodedId = base64_encode($inq_id);
                $url = hasAccess("inquiry", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=inquiry") : "";

                return response()->json([
                    'response_code' => '1',
                    'url' => $url,
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

    public function getLatestInquiryNumber(Request $request)
    {
        $modal  =  Inquiry::class;
        $sequence = 'inq_sequence';
        $sup_num_format = getMarketingLatestSequence($modal,$sequence);

        return response()->json([
          'response_code' => 1,
          'latest_no'     => $sup_num_format['format'],
          'number'        => $sup_num_format['isFound'],
      ]);
    }

    public function edit(Request $request)
    {
        $isAnyPartInUse = false;
        $inquiry_data =  DB::select('CALL inquiry_master(?)', [$request->id]);
        if(!empty($inquiry_data))
        {
            $inquiry_data = $inquiry_data[0];
            $inquiry_data->inq_date = $inquiry_data->inq_date != "" ? Date::createFromFormat('Y-m-d',  $inquiry_data->inq_date)->format('d/m/Y') : "";
            if(isset($inquiry_data->company_logo))
            {
                $inquiry_data->company_logo = base64_encode($inquiry_data->company_logo);
            }

            $inq_no = !empty($inquiry_data->inq_number) ? '_' . str_replace('/', '_', $inquiry_data->inq_number) : "";
            $cust = DB::table('customers')->where('id', $inquiry_data->inq_customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $inquiry_data->pdf_name = 'Inquiry' . $inq_no . $cust_name;
        }

        $inquiry_details_data = DB::select('CALL inquiry_details(?)', [$request->id]);
        foreach($inquiry_details_data as $dKey => $dVal)
        {
            $dVal->inqd_file_upload_doc = $dVal->inqd_file_upload;
            $dVal->inqd_file_upload_blob_image = base64_encode($dVal->inqd_file_upload_blob_image);

            $fea_is_used = FeasibilityReview::where('fr_inqd_id', $dVal->inqd_id)->exists();
            $est_is_used = EstimationCosting::where('ec_inqd_id', $dVal->inqd_id)->exists();
            $quo_is_used = QuotationDetails::where('quotd_inqd_id', $dVal->inqd_id)->exists();
            $isc_is_used = InquiryShortClose::where('inq_sc_inqd_id', $dVal->inqd_id)->exists();

            if($fea_is_used || $est_is_used || $quo_is_used || $isc_is_used)
            {
                $dVal->in_use = true;
                $isAnyPartInUse = true;
            }
            else
            {
                $dVal->in_use = false;
            }
        }

        if($inquiry_data)
        {
            return response()->json([
                'inquiry_data'     => $inquiry_data,
                'inquiry_details_data'     => $inquiry_details_data,
                'response_code'    => '1',
                'response_message' => '',
            ]);
        }
        else
        {
            return response()->json([
                'response_code'    => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // public function update(Request $request)
    // {
    //     DB::beginTransaction();
    //     try
    //     {
    //         $year_data = getCurrentYearData();
    //         $validated = $request->validate(['inq_sequence' => ['required', 'max:155', Rule::unique('inquiry')->where(function ($query) use ($request, $year_data) { $query->where('year_id', '=', $year_data->id);})->ignore($request->id, 'inq_id')],
    //             'inq_number' => ['required','max:155',Rule::unique('inquiry')->where(function ($query) use ($request, $year_data) { $query->where('year_id', '=', $year_data->id); })->ignore($request->id, 'inq_id')],
    //         ], [
    //             'inq_sequence.unique' => 'Inquiry No. Already Exists',
    //             'inq_sequence.required' => 'Please Enter Inquiry No.',
    //         ]);

    //         $inquiry_data = Inquiry::where('inq_id', $request->id)->update([
    //             'inq_sequence' => $request->inq_sequence ?? null,
    //             'inq_number'   => $request->inq_number ?? null,
    //             'inq_date' => isset($request->inq_date) ? Date::createFromFormat('d/m/Y', $request->inq_date)->format('Y-m-d') : null,
    //             'inq_customer_id'   => $request->inq_customer_id ?? null,
    //             'inq_kind_attn_id'   => $request->inq_kind_attn_id ?? null,
    //             'inq_ref_no_date'   => $request->inq_ref_no_date ?? null,
    //             'inq_sp_note'   => $request->inq_sp_note ?? null,
    //             'inq_prepared_by_id'   => $request->inq_prepared_by_id ?? null,
    //             'last_on'       => Carbon::now('Asia/Kolkata'),
    //             'last_by'       => Auth::id(),
    //         ]);

    //         if(!$inquiry_data)
    //         {
    //             return response()->json([
    //                 'response_code' => '0',
    //                 'response_message' => 'Record Not Updated',
    //             ]);
    //         }

    //         $images = '';
    //         $blobImage = '';
    //         $update_details =  InquiryDetails::where('inq_id',$request->id)->update(['status' => 'D',]);

    //         $inquiry_details_data = json_decode($request->inquiry_details_data, true);
    //         foreach($inquiry_details_data as $ctVal)
    //         {
    //             if($ctVal['inqd_id'] == 0)
    //             {
    //                 if(!empty($ctVal['inqd_file_upload_doc']))
    //                 {
    //                     $file = new File();
    //                     $isFound =  $file->getFileFromTemp($ctVal['inqd_file_upload_doc'],$prefix = "inquiry");
    //                     if($isFound !== false)
    //                     {
    //                         $images = $isFound;
    //                         $filePath = storage_path('app/public/' . $images);
    //                         if(!empty($images) && $file->Is_Files_Exists($images))
    //                         {
    //                             $blobImage = file_get_contents($filePath);
    //                         }
    //                     }
    //                 }

    //                 InquiryDetails::create([
    //                     'inq_id' => $request->id,
    //                     'inqd_test_method' => !empty($ctVal['inqd_test_method']) ? $ctVal['inqd_test_method'] : null,
    //                     'inqd_type_of_job_id' => !empty($ctVal['inqd_type_of_job_id']) ? $ctVal['inqd_type_of_job_id'] : null,
    //                     'inqd_job_description_id' => !empty($ctVal['inqd_job_description_id']) ? $ctVal['inqd_job_description_id'] : null,
    //                     'inqd_part_id' => !empty($ctVal['inqd_part_id']) ? $ctVal['inqd_part_id'] : null,
    //                     'inqd_process_at' => !empty($ctVal['inqd_process_at']) ? $ctVal['inqd_process_at'] : null,
    //                     'inqd_description' => !empty($ctVal['inqd_description']) ? $ctVal['inqd_description'] : null,
    //                     'inqd_quantity' => !empty($ctVal['inqd_quantity']) ? $ctVal['inqd_quantity'] : null,
    //                     'inqd_unit_id' => !empty($ctVal['inqd_unit_id']) ? $ctVal['inqd_unit_id'] : null,
    //                     'inqd_feasibility_required' => !empty($ctVal['inqd_feasibility_required']) ? $ctVal['inqd_feasibility_required'] : null,
    //                     'inqd_estimation_required' => !empty($ctVal['inqd_estimation_required']) ? $ctVal['inqd_estimation_required'] : null,
    //                     'inqd_remark' => !empty($ctVal['inqd_remark']) ? $ctVal['inqd_remark'] : null,
    //                     'inqd_file_upload' =>  $images,
    //                     'inqd_file_upload_blob_image' =>  $blobImage,
    //                     'status' => 'Y',
    //                 ]);
    //             }
    //             else
    //             {
    //                 // $imgs = InquiryDetails::select('inqd_file_upload','inqd_file_upload_blob_image')->where('inqd_id','=', $ctVal['inqd_id'])->first();
    //                 // if($imgs)
    //                 // {
    //                 //     if(!empty($ctVal['inqd_file_upload_doc']) && $ctVal['inqd_file_upload_doc'] != $imgs->inqd_file_upload)
    //                 //     {
    //                 //         $file = new File();
    //                 //         $file->delete_file($imgs->inqd_file_upload);
    //                 //         $images = null;
    //                 //         $blobImage =null;
    //                 //     }
    //                 // }
                    
    //                 // if(!empty($ctVal['inqd_file_upload_doc']))
    //                 // {
    //                 //     $file = new File();
    //                 //     $isFound =  $file->getFileFromTemp($ctVal['inqd_file_upload_doc'],$prefix = "inquiry");
    //                 //     if($isFound !== false)
    //                 //     {
    //                 //         $images = $isFound;
    //                 //         $filePath = storage_path('app/public/' . $images);
    //                 //         if(!empty($images) && $file->Is_Files_Exists($images))
    //                 //         {
    //                 //             $blobImage = file_get_contents($filePath);
    //                 //         }
    //                 //     }
    //                 //     else
    //                 //     {
    //                 //         $images = $imgs->inqd_file_upload;
    //                 //         $blobImage = $imgs->inqd_file_upload_blob_image;
    //                 //     }
    //                 // }

    //                 $imgs = InquiryDetails::where('inqd_id', $ctVal['inqd_id'])->first();
    //                 $images = $imgs ? $imgs->inqd_file_upload : null;
    //                 $blobImage = $imgs ? $imgs->inqd_file_upload_blob_image : null;
    //                 if(empty($ctVal['inqd_file_upload_doc']))
    //                 {
    //                     if($imgs && !empty($imgs->inqd_file_upload))
    //                     {
    //                         $imageUsedCount = InquiryDetails::where('inqd_file_upload', $imgs->inqd_file_upload)->where('inqd_id', '!=', $ctVal['inqd_id'])->count();
    //                         if($imageUsedCount == 0)
    //                         {
    //                             $file = new File();
    //                             $file->delete_file($imgs->inqd_file_upload);
    //                         }
    //                     }

    //                     $images = null;
    //                     $blobImage = null;
    //                 }

    //                 if(!empty($ctVal['inqd_file_upload_doc']))
    //                 {
    //                     $file = new File();
    //                     $isFound = $file->getFileFromTemp($ctVal['inqd_file_upload_doc'],'inquiry');
    //                     if($isFound !== false)
    //                     {
    //                         if($imgs && !empty($imgs->inqd_file_upload))
    //                         {
    //                             $imageUsedCount = InquiryDetails::where('inqd_file_upload', $imgs->inqd_file_upload)->where('inqd_id', '!=', $ctVal['inqd_id'])->count();
    //                             if($imageUsedCount == 0)
    //                             {
    //                                 $file->delete_file($imgs->inqd_file_upload);
    //                             }
    //                         }

    //                         $images = $isFound;
    //                         $filePath = storage_path('app/public/' . $images);
    //                         $blobImage = file_exists($filePath) ? file_get_contents($filePath) : null;
    //                     }
    //                 }

    //                 InquiryDetails::where('inqd_id', $ctVal['inqd_id'])->update([
    //                     'inqd_file_upload' => $images,
    //                     'inqd_file_upload_blob_image' => $blobImage,
    //                     // 'status' => 'Y',
    //                 ]);
                    
    //                 InquiryDetails::where('inqd_id', $ctVal['inqd_id'])->update([
    //                     'inqd_test_method' => !empty($ctVal['inqd_test_method']) ? $ctVal['inqd_test_method'] : null,
    //                     'inqd_type_of_job_id' => !empty($ctVal['inqd_type_of_job_id']) ? $ctVal['inqd_type_of_job_id'] : null,
    //                     'inqd_job_description_id' => !empty($ctVal['inqd_job_description_id']) ? $ctVal['inqd_job_description_id'] : null,
    //                     'inqd_part_id' => !empty($ctVal['inqd_part_id']) ? $ctVal['inqd_part_id'] : null,
    //                     'inqd_process_at' => !empty($ctVal['inqd_process_at']) ? $ctVal['inqd_process_at'] : null,
    //                     'inqd_description' => !empty($ctVal['inqd_description']) ? $ctVal['inqd_description'] : null,
    //                     'inqd_quantity' => !empty($ctVal['inqd_quantity']) ? $ctVal['inqd_quantity'] : null,
    //                     'inqd_unit_id' => !empty($ctVal['inqd_unit_id']) ? $ctVal['inqd_unit_id'] : null,
    //                     'inqd_feasibility_required' => !empty($ctVal['inqd_feasibility_required']) ? $ctVal['inqd_feasibility_required'] : null,
    //                     'inqd_estimation_required' => !empty($ctVal['inqd_estimation_required']) ? $ctVal['inqd_estimation_required'] : null,
    //                     'inqd_remark' => !empty($ctVal['inqd_remark']) ? $ctVal['inqd_remark'] : null,
    //                     // 'inqd_file_upload' =>  $images,
    //                     // 'inqd_file_upload_blob_image' =>  $blobImage,
    //                     'status' => 'Y',
    //                 ]);
    //             }
    //         }
    //         unset($blobImage);
    //         $olddata = InquiryDetails::where('inq_id',$request->id)->where('status','D')->get();
    //         if($olddata->isNotEmpty()){

    //             foreach($olddata as $key=>$val){
    //                 if($val->inqd_file_upload != ''){
    //                     $file = new File();
    //                 $file->delete_file($val->inqd_file_upload);
    //                 }
    //             }


    //         }
    //         $update_details = InquiryDetails::where('inq_id',$request->id)->where('status','D')->delete();

    //         if($inquiry_data)
    //         {
    //             DB::commit();
    //             return response()->json([
    //                 'response_code' => '1',
    //                 'response_message' => 'Record Updated Successfully.'
    //             ]);
    //         }
    //         else
    //         {
    //             return response()->json([
    //                 'response_code' => '0',
    //                 'response_message' => 'Record Not Updated'
    //             ]);
    //         }
    //     }
    //     catch(\Exception $e)
    //     {
    //         DB::rollBack();
    //         return response()->json([
    //             'response_code' => '0',
    //             'response_message' => 'Error Occurred. Record Not Updated.',
    //             'original_error' => $e->getMessage()
    //         ]);
    //     }
    // }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $year_data = getCurrentYearData();
            $validated = $request->validate(['inq_sequence' => ['required', 'max:155', Rule::unique('inquiry')->where(function ($query) use ($request, $year_data) { $query->where('year_id', '=', $year_data->id);})->ignore($request->id, 'inq_id')],
                'inq_number' => ['required','max:155',Rule::unique('inquiry')->where(function ($query) use ($request, $year_data) { $query->where('year_id', '=', $year_data->id); })->ignore($request->id, 'inq_id')],
            ], [
                'inq_sequence.unique' => 'Inquiry No. Already Exists',
                'inq_sequence.required' => 'Please Enter Inquiry No.',
            ]);

            $page_id = getMenuIdBassedOnDisplayName('inquiry');
            $LocationData = getCurrentLocation();
            $assign_format_no = getAssignFormateNoForTransaction($LocationData->location_id, $page_id->id, $request->inq_date);

            $inquiry_data = Inquiry::where('inq_id', $request->id)->update([
                'inq_sequence' => $request->inq_sequence ?? null,
                'inq_number'   => $request->inq_number ?? null,
                'inq_date' => isset($request->inq_date) ? Date::createFromFormat('d/m/Y', $request->inq_date)->format('Y-m-d') : null,
                'inq_customer_id'   => $request->inq_customer_id ?? null,
                'inq_kind_attn_id'   => $request->inq_kind_attn_id ?? null,
                'inq_ref_no_date'   => $request->inq_ref_no_date ?? null,
                'inq_sp_note'   => $request->inq_sp_note ?? null,
                'inq_prepared_by_id'   => $request->inq_prepared_by_id ?? null,
                // 'assign_format_no' => $assign_format_no, // not update assign formate discussion ramde sir
                'last_on'       => Carbon::now('Asia/Kolkata'),
                'last_by'       => Auth::id(),
            ]);

            if(!$inquiry_data)
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }

            $update_details =  InquiryDetails::where('inq_id',$request->id)->update(['status' => 'D',]);
            $inquiry_details_data = json_decode($request->inquiry_details_data, true);
            // dd($inquiry_details_data);
            foreach($inquiry_details_data as $ctVal)
            {
                $images = '';
                $blobImage = '';

                if($ctVal['inqd_id'] == 0 || $ctVal['inqd_id'] == '')
                {
                    if(!empty($ctVal['inqd_file_upload_doc']))
                        {
                        $file = new File();
                        $isFound =  $file->getFileFromTemp($ctVal['inqd_file_upload_doc'],$prefix = "inquiry");
                        if($isFound !== false)
                        {
                            $images = $isFound;
                            $filePath = storage_path('app/public/' . $images);
                            if(!empty($images) && $file->Is_Files_Exists($images))
                            {
                                $blobImage = file_get_contents($filePath);
                            }
                        }
                    }
                    InquiryDetails::create([
                        'inq_id' => $request->id,
                        'inqd_test_method' => !empty($ctVal['inqd_test_method']) ? $ctVal['inqd_test_method'] : null,
                        'inqd_type_of_job_id' => !empty($ctVal['inqd_type_of_job_id']) ? $ctVal['inqd_type_of_job_id'] : null,
                        'inqd_job_description_id' => !empty($ctVal['inqd_job_description_id']) ? $ctVal['inqd_job_description_id'] : null,
                        'inqd_part_no' => !empty($ctVal['inqd_part_no']) ? $ctVal['inqd_part_no'] : null,
                        'inqd_process_at' => !empty($ctVal['inqd_process_at']) ? $ctVal['inqd_process_at'] : null,
                        'inqd_description' => !empty($ctVal['inqd_description']) ? $ctVal['inqd_description'] : null,
                        'inqd_quantity' => !empty($ctVal['inqd_quantity']) ? $ctVal['inqd_quantity'] : null,
                        'inqd_unit_id' => !empty($ctVal['inqd_unit_id']) ? $ctVal['inqd_unit_id'] : null,
                        'inqd_feasibility_required' => !empty($ctVal['inqd_feasibility_required']) ? $ctVal['inqd_feasibility_required'] : null,
                        'inqd_estimation_required' => !empty($ctVal['inqd_estimation_required']) ? $ctVal['inqd_estimation_required'] : null,
                        'inqd_remark' => !empty($ctVal['inqd_remark']) ? $ctVal['inqd_remark'] : null,
                        'inqd_file_upload' =>  $images != '' ? $images : null,
                        'inqd_file_upload_blob_image' =>  $blobImage  ? $blobImage : null,
                        'status' => 'Y',
                    ]);
                }
                else
                {
                    // $oldimage = InquiryDetails::where('inqd_id', $ctVal['inqd_id'])->value('inqd_file_upload');
                    // // $images = '';
                    // // $blobImage = '';

                    // if(!empty($ctVal['inqd_file_upload_doc'])){

                    //     if($oldimage != $ctVal['inqd_file_upload_doc']){
                          

                    //         $file = new File();
                    //         if(!empty($oldimage)){

                    //             $file->delete_file($oldimage);
                    //         }

                            
                    //         $isFound =  $file->getFileFromTemp($ctVal['inqd_file_upload_doc'],$prefix = 'inquiry');
                            
                    //         if($isFound !== false)
                    //         {
                    //             $images = $isFound;
                    //             $filePath = storage_path('app/public/' . $images);
                    //             if (!empty($images) && $file->Is_Files_Exists($images))
                    //             {
                    //                 $blobImage = file_get_contents($filePath);
                    //             }
                    //         }

                    //     }else{

                    //         $images = $ctVal['inqd_file_upload_doc'];
                    //         $filePath = storage_path('app/public/' . $images);
                    //         $blobImage = file_get_contents($filePath);
                    //     }

                    // }else{
                    //     if(!empty($oldimage)){
                    //         $file = new File();
                    //         $file->delete_file($oldimage);

                    //     }

                    //     $images = '';
                    //     $blobImage = '';
                    // }

                    $imgs = InquiryDetails::where('inqd_id', $ctVal['inqd_id'])->value('inqd_file_upload');
                    $file = new File();
                    $images = '';
                    $blobImage = '';

                    if($ctVal['inqd_file_upload_doc'] != '' && $ctVal['inqd_file_upload_doc'] != null){
                        if($imgs && !empty($imgs)){
                            if($imgs != $ctVal['inqd_file_upload_doc']){
                                    $file->delete_file($imgs);
                            }                 
                        }      
                        
                        $isFound = $file->getFileFromTemp($ctVal['inqd_file_upload_doc'],'inquiry');

                        if($isFound !== false){                    
                            $images = $isFound;
                            $filePath = storage_path('app/public/' . $images);
                            $blobImage = file_exists($filePath) ? file_get_contents($filePath) : null;
                        }else{
                            $images = $ctVal['inqd_file_upload_doc'];
                            $filePath = storage_path('app/public/' . $images);
                            $blobImage = $blobImage = file_get_contents($filePath);;
                        }
                    }else{
                        if($imgs && !empty($imgs)){
                            $file->delete_file($imgs);
                        }  
                    }

                    // $images = $imgs ? $imgs->inqd_file_upload : null;
                    // $blobImage = $imgs ? $imgs->inqd_file_upload_blob_image : null;
                    // if(!empty($ctVal['inqd_file_upload_doc']))
                    // {
                    //     if($imgs && !empty($imgs->inqd_file_upload))
                    //     {
                    //         $imageUsedCount = InquiryDetails::where('inqd_file_upload', $imgs->inqd_file_upload)->where('inqd_id', '!=', $ctVal['inqd_id'])->count();
                    //         if($imageUsedCount == 0)
                    //         {
                    //             $file = new File();
                    //             $file->delete_file($imgs->inqd_file_upload);
                    //         }
                    //     }

                    //     $images = null;
                    //     $blobImage = null;
                    // }

                    // if(!empty($ctVal['inqd_file_upload_doc']))
                    // {
                    //     $file = new File();
                    //     $isFound = $file->getFileFromTemp($ctVal['inqd_file_upload_doc'],'inquiry');
                    //     if($isFound !== false)
                    //     {
                    //         if($imgs && !empty($imgs->inqd_file_upload))
                    //         {
                    //             $imageUsedCount = InquiryDetails::where('inqd_file_upload', $imgs->inqd_file_upload)->where('inqd_id', '!=', $ctVal['inqd_id'])->count();
                    //             if($imageUsedCount == 0)
                    //             {
                    //                 $file->delete_file($imgs->inqd_file_upload);
                    //             }
                    //         }

                    //         $images = $isFound;
                    //         $filePath = storage_path('app/public/' . $images);
                    //         $blobImage = file_exists($filePath) ? file_get_contents($filePath) : null;
                    //     }
                    // }

                    // InquiryDetails::where('inqd_id', $ctVal['inqd_id'])->update([
                    //     'inqd_file_upload' => $images,
                    //     'inqd_file_upload_blob_image' => $blobImage,
                    //     // 'status' => 'Y',
                    // ]);
                    
                    InquiryDetails::where('inqd_id', $ctVal['inqd_id'])->update([
                        'inqd_test_method' => !empty($ctVal['inqd_test_method']) ? $ctVal['inqd_test_method'] : null,
                        'inqd_type_of_job_id' => !empty($ctVal['inqd_type_of_job_id']) ? $ctVal['inqd_type_of_job_id'] : null,
                        'inqd_job_description_id' => !empty($ctVal['inqd_job_description_id']) ? $ctVal['inqd_job_description_id'] : null,
                        'inqd_part_no' => !empty($ctVal['inqd_part_no']) ? $ctVal['inqd_part_no'] : null,
                        'inqd_process_at' => !empty($ctVal['inqd_process_at']) ? $ctVal['inqd_process_at'] : null,
                        'inqd_description' => !empty($ctVal['inqd_description']) ? $ctVal['inqd_description'] : null,
                        'inqd_quantity' => !empty($ctVal['inqd_quantity']) ? $ctVal['inqd_quantity'] : null,
                        'inqd_unit_id' => !empty($ctVal['inqd_unit_id']) ? $ctVal['inqd_unit_id'] : null,
                        'inqd_feasibility_required' => !empty($ctVal['inqd_feasibility_required']) ? $ctVal['inqd_feasibility_required'] : null,
                        'inqd_estimation_required' => !empty($ctVal['inqd_estimation_required']) ? $ctVal['inqd_estimation_required'] : null,
                        'inqd_remark' => !empty($ctVal['inqd_remark']) ? $ctVal['inqd_remark'] : null,
                        'inqd_file_upload' =>  $images != '' ? $images : null,
                        'inqd_file_upload_blob_image' =>  $blobImage  ? $blobImage : null,
                        'status' => 'Y',
                    ]);
                }
            }
            unset($blobImage);

            $olddata = InquiryDetails::where('inq_id',$request->id)->where('status','D')->get();
            if($olddata->isNotEmpty())
            {
                foreach($olddata as $key=>$val)
                {
                    if($val->inqd_file_upload != '')
                    {
                        $file = new File();
                        $file->delete_file($val->inqd_file_upload);
                    }
                }
            }
            $update_details = InquiryDetails::where('inq_id',$request->id)->where('status','D')->delete();

            if($inquiry_data)
            {
                DB::commit();

                $inq_id = $request->id;
                $inq = Inquiry::where('inq_id', $inq_id)->first();
                $inq_no = !empty($inq->inq_number) ? '_' . str_replace('/', '_', $inq->inq_number) : "";
                $cust = DB::table('customers')->where('id', $inq->inq_customer_id)->value('customer');
                $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
                $pdf_name = 'Inquiry' . $inq_no . $cust_name;

                GeneratePdf($inq_id, $pdf_name, 'inquiry', 'update');
                $encodedId = base64_encode($inq_id);
                $url = hasAccess("inquiry", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=inquiry") : "";

                return response()->json([
                    'response_code' => '1',
                    'url' => $url,
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

    /*public function destroy(Request $request)
    {
        try
        {
            $data = InquiryDetails::where('inq_id', $request->id)->get();
            if($data->isNotEmpty())
            {
                foreach($data as $key=>$val)
                {
                    if($val->inqd_file_upload != '')
                    {
                        $file = new File();
                        $file->delete_file($val->inqd_file_upload);
                    }
                }
            }

            $quotd_inqd_id = QuotationDetails::where('quotd_inqd_id',$request->id)->get();
            if($quotd_inqd_id->isNotEmpty())
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Inquiry Is Used In Quotation.",
                ]);
            }

            $ec_inqd_id = EstimationCosting::where('ec_inqd_id',$request->id)->get();
            if($ec_inqd_id->isNotEmpty())
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Inquiry Is Used In Estimation & Costing.",
                ]);
            }

            $fr_inqd_id = FeasibilityReview::where('fr_inqd_id',$request->id)->get();
            if($fr_inqd_id->isNotEmpty())
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Inquiry Is Used In Feasibility Review.",
                ]);
            }

            Inquiry::where('inq_id',$request->id)->delete();
            InquiryDetails::where('inq_id',$request->id)->delete();
            return response()->json([
                'response_code' => '1',
                'response_message' => 'Record Deleted Successfully.',
            ]);
        }
        catch(\Exception $e)
        {
            report($e);
            if(isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451)
            {
                $error_msg = "This Is Used Somewhere, You Can't Delete";
            }
            else
            {
                $error_msg = "Record Not Deleted";
            }

            return response()->json([
                'response_code' => '0',
                'response_message' => $error_msg,
            ]);
        }
    }*/

    public function destroy(Request $request)
    {
        try
        {
            $data = InquiryDetails::where('inq_id', $request->id)->get();
            if($data->isNotEmpty())
            {
                foreach($data as $key=>$val)
                {
                    if($val->inqd_file_upload != '')
                    {
                        $file = new File();
                        $file->delete_file($val->inqd_file_upload);
                    }
                }
            }
           
            $inquiry_details_id = InquiryDetails::select('inqd_id')->where('inq_id', $request->id)->pluck('inqd_id');
            if (QuotationDetails::whereIn('quotd_inqd_id', $inquiry_details_id)->exists()) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Inquiry Is Used In Quotation.",
                ]);
            }
 
            if (EstimationCosting::whereIn('ec_inqd_id', $inquiry_details_id)->exists()) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Inquiry Is Used In Estimation & Costing.",
                ]);
            }
 
            if (FeasibilityReview::whereIn('fr_inqd_id', $inquiry_details_id)->exists()) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Inquiry Is Used In Feasibility Review.",
                ]);
            }

            if (InquiryShortClose::whereIn('inq_sc_inqd_id', $inquiry_details_id)->exists()) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Inquiry Is Used In Inquiry Short Close.",
                ]);
            }
 
            // $quotd_inqd_id = QuotationDetails::whereIn('quotd_inqd_id',$inquiry_details_id)->exists();
            // if($quotd_inqd_id->isNotEmpty())
            // {
            //     return response()->json([
            //         'response_code' => '0',
            //         'response_message' => "You Can't Delete, Inquiry Is Used In Quotation.",
            //     ]);
            // }
 
            // $ec_inqd_id = EstimationCosting::whereIn('ec_inqd_id',$inquiry_details_id)->exists();
            // if($ec_inqd_id->isNotEmpty())
            // {
            //     return response()->json([
            //         'response_code' => '0',
            //         'response_message' => "You Can't Delete, Inquiry Is Used In Estimation & Costing.",
            //     ]);
            // }
           
            // $fr_inqd_id = FeasibilityReview::whereIn('fr_inqd_id',$inquiry_details_id)->exists();
            // if($fr_inqd_id->isNotEmpty())
            // {
            //     return response()->json([
            //         'response_code' => '0',
            //         'response_message' => "You Can't Delete, Inquiry Is Used In Feasibility Review.",
            //     ]);
            // }
 
            Inquiry::where('inq_id',$request->id)->delete();
            InquiryDetails::where('inq_id',$request->id)->delete();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        }
        catch(\Exception $e)
        {
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

    public function getInquiryPartNoList(Request $request)
    {
        $term = $request->term ?? '';
        $job_desc_id = $request->job_desc_id ?? '';

        if (!empty($term)) {
            $query = InquiryDetails::select('inqd_part_no')
                ->whereNotNull('inqd_part_no')
                ->where('inqd_part_no', '!=', '')
                ->where('status', 'Y')
                ->where('inqd_part_no', 'LIKE', '%' . $term . '%');

            if (!empty($job_desc_id)) {
                $query->where('inqd_job_description_id', $job_desc_id);
            }

            $partNos = $query->groupBy('inqd_part_no')->orderBy('inqd_part_no', 'asc')->get();

            if ($partNos->isEmpty() && !empty($job_desc_id)) {
                $partNos = InquiryDetails::select('inqd_part_no')
                    ->whereNotNull('inqd_part_no')
                    ->where('inqd_part_no', '!=', '')
                    ->where('status', 'Y')
                    ->where('inqd_part_no', 'LIKE', '%' . $term . '%')
                    ->groupBy('inqd_part_no')
                    ->orderBy('inqd_part_no', 'asc')
                    ->get();
            }

            if ($partNos->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($partNos as $row) {
                    $output .= '<li parent-id="inqd_part_no" list-id="inqd_part_no_list" class="list-group-item" tabindex="0">' . e($row->inqd_part_no) . '</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'partNoList'    => $output,
                    'response_code' => 1,
                ]);
            }
        }

        return response()->json([
            'partNoList'    => '',
            'response_code' => 1,
        ]);
    }

}