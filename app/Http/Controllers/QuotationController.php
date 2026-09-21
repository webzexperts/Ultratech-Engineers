<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CustomerContacts;
use App\Models\Quotuiry;
use App\Models\Quotation;
use App\Models\QuotationDetails;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\File;
use App\Models\Inquiry;
use App\Models\InquiryDetails;
use App\Models\InquiryShortClose;
use App\Models\Transaction\OrderAcceptanceDetails;
use League\CommonMark\Extension\SmartPunct\Quote;

class QuotationController extends Controller
{
     public function manage()
    {
        return view('manage.manage-quotation');
    }

    public function index(QuotationDetails $quotation_data, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $quotation_data = QuotationDetails::select([
            'quotation.quot_id',
            'quotation.quot_number',
            'quotation.quot_sequence',
            'quotation.quot_date',
            'customers.customer_code',
            'customers.customer',
            'quotation.quot_ref_no_date',
            'inquiry.inq_number',
            'inquiry.inq_date',
            'quotation_details.quotd_test_method_id',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            'quotation_details.quotd_part_no',
            //\DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'quotation_details.quotd_qty',
            'quot_qty_unit.unit as quot_qty_unit_name',
            'quotation_details.quotd_rate_unit',
            'quot_rate_unit.unit as quot_rate_unit_name',
            'customer_contacts.contact_person as kind_attn',
            'quotation_details.quotd_remark',
            'quotation.created_on',
            'quotation.created_by',
            'quotation.last_by',
            'quotation.last_on',
            'prepared_by.person_name as quot_prepared_by_name',
            'authorised_by.person_name as quot_authorised_by_name',
        ])
        ->leftJoin('inquiry_details','inquiry_details.inqd_id','=','quotation_details.quotd_inqd_id')
        ->leftJoin('inquiry','inquiry.inq_id','=','inquiry_details.inq_id')
        ->leftJoin('quotation','quotation.quot_id','=','quotation_details.quotd_quot_id')
        ->leftJoin('customers','customers.id','=','quotation.quot_customer_id')
        ->leftJoin('type_of_job','type_of_job.id','=','quotation_details.quotd_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','quotation_details.quotd_job_desc_id')
        //->leftJoin('part','part.part_id','=','quotation_details.quotd_part_id')

        ->leftJoin('unit as quot_qty_unit','quot_qty_unit.id','=','quotation_details.quotd_unit_id')
        ->leftJoin('unit as quot_rate_unit','quot_rate_unit.id','=','quotation_details.quotd_rate_unit_id')
        ->leftJoin('customer_contacts','customer_contacts.id','=','quotation.quot_kind_attn_id')

        ->leftJoin('admin as prepared_by','prepared_by.id','=','quotation.quot_prepared_by_id')
        ->leftJoin('admin as authorised_by','authorised_by.id','=','quotation.quot_authorised_by_id')
        ->where('quotation.year_id', $year_data->id);

        $dataTable = DataTables::of($quotation_data)
        // ->filterColumn('part.part', function($query, $keyword) {
        //     $keyword = trim($keyword);
        //     $query->whereRaw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) LIKE ?", ["%{$keyword}%"]);
        // })
        ->editColumn('quot_date', function($quotation_data){
            if ($quotation_data->quot_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $quotation_data->quot_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('quotation.quot_date', function ($q, $k) {
            applyDate($q, $k, 'quotation.quot_date');
        })
        ->editColumn('inq_date', function($quotation_data){
            if ($quotation_data->inq_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $quotation_data->inq_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('inquiry.inq_date', function ($q, $k) {
            applyDate($q, $k, 'inquiry.inq_date');
        })
        ->filterColumn('inquiry.inq_number', function ($q, $k) {
            applynumberprefix($q, $k, 'inquiry.inq_number');
        })

         ->editColumn('quotd_qty', function($inquiry_data) {
            // return $inquiry_data->quotd_qty > 0 ? number_format((float)$inquiry_data->quotd_qty, 3, '.','') : number_format((float) 0, 3, '.','');
            return $inquiry_data->quotd_qty > 0 ? number_format((float)$inquiry_data->quotd_qty, 2, '.','') : number_format((float) 0, 2, '.','');
        })
        ->filterColumn('quotation_details.quotd_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('quotd_qty')
                    ->orWhere('quotd_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(quotd_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('quotd_qty', 'like', "%{$search}%");
            }
        })
        ->editColumn('quotd_rate_unit', function($inquiry_data) {
            return $inquiry_data->quotd_rate_unit === null
                ? ''
                : ($inquiry_data->quotd_rate_unit > 0
                    ? number_format((float)$inquiry_data->quotd_rate_unit, 2, '.', '')
                    : number_format((float)$inquiry_data->quotd_rate_unit, 2, '.', '')
                );
        })
        ->filterColumn('quotation_details.quotd_rate_unit', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('quotd_rate_unit')
                    ->orWhere('quotd_rate_unit', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(quotd_rate_unit AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('quotd_rate_unit', 'like', "%{$search}%");
            }
        })
        ->filterColumn('quotation.quot_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'quotation.quot_number');
        })
        
        ->addColumn('options',function($quotation_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("quotation", "print")) {
                    $quot_no = !empty($quotation_data->quot_number) ? '_' . str_replace('/', '_', $quotation_data->quot_number) : "";
                    $cust_name = !empty($quotation_data->customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $quotation_data->customer) : "";
                    $pdfName   = 'Quotation' . $quot_no . $cust_name;
                    $encodedId = base64_encode($quotation_data->quot_id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=quotation");
                    $action .= '<li><a class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }

                if (hasAccess("quotation", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-quotation"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("quotation", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'quotation');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $year_data = getCurrentYearData();
        $existNumber = Quotation::where([['quot_sequence',  $request->quot_sequence],['quot_number',$request->quot_number],['year_id',$year_data->id]])->first();
        
        if($existNumber)
        {
            $latestNo = $this->getLatestQuotationNumber($request);
            $tmp =  $latestNo->getContent();
            $area = json_decode($tmp, true);
            $quot_number =   $area['latest_no'];
            $quot_sequence = $area['number'];
        }
        else
        {
            $quot_number = $request->quot_number;
            $quot_sequence = $request->quot_sequence;
        }

        $uploadDoc = '';
        $blobImage = '';
        if(isset($request->revision_doc_doc) && $request->revision_doc_doc != ""){
            $file = new File();
            $isFound =  $file->getFileFromTemp($request->revision_doc_doc,$prefix = "revision");

            if($isFound != false){
                $uploadDoc = $isFound;
                $filePath = storage_path('app/public/' . $uploadDoc);
                if (!empty($uploadDoc) && $file->Is_Files_Exists($uploadDoc)) {
                    $blobImage = file_get_contents($filePath);
                }
            }
        }

        DB::beginTransaction();
        try
        {
            $page_id = getMenuIdBassedOnDisplayName('quotation');
            $LocationData = getCurrentLocation();
            $assign_format_no = getAssignFormateNoForTransaction($LocationData->location_id, $page_id->id, $request->quot_date);

            $quotation_data =  Quotation::create([
                'quot_number'           => $quot_number ?? null,
                'quot_sequence'         => $quot_sequence ?? null,
                'quot_date'             => isset($request->quot_date) ? Date::createFromFormat('d/m/Y', $request->quot_date)->format('Y-m-d') : null,
                'quot_customer_id'      => $request->quot_customer_id ?? null,
                'quot_sac_id'           => $request->quot_sac_id ?? null,
                'quot_ref_no_date'      => $request->quot_ref_no_date ?? null,
                'quot_kind_attn_id'     => $request->quot_kind_attn_id ?? null,
                'quot_offer_validity'   => $request->quot_offer_validity ?? null,
                'quot_prepared_by_id'   => $request->quot_prepared_by_id ?? null,
                'quot_authorised_by_id' => $request->quot_authorised_by_id ?? null,
                'quot_special_note'     => $request->quot_special_note ?? null,
                'quot_copy_from_id'     => $request->quot_copy_from_id ?? null,
                'quot_terms_and_conditions'     => $request->quot_terms_and_conditions ?? null,
                'quot_revision_doc'     => $uploadDoc,
                'quot_image'            => $blobImage,
                'assign_format_no'      => $assign_format_no,
                'year_id'               => $year_data->id,
                'company_id'            => Auth::user()->company_id,
                'created_on'            => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'            => Auth::user()->id,
            ]);


            $quotation_details_data = $request->quotation_details_data = json_decode($request->quotation_details_data, true);
            if(!empty($quotation_details_data))
            {
                foreach($quotation_details_data as $ctKey => $ctVal)
                {
                    if($ctVal != null)
                    {
                        $quotation_details_data = QuotationDetails::create([
                            'quotd_quot_id'            => $quotation_data->quot_id,

                            'quotd_inqd_id'        => !empty($ctVal['quotd_inqd_id']) ? $ctVal['quotd_inqd_id'] : null,

                            'quotd_test_method_id'        => !empty($ctVal['quotd_test_method_id']) ? $ctVal['quotd_test_method_id'] : null,

                            'quotd_type_of_job_id'     => !empty($ctVal['quotd_type_of_job_id']) ? $ctVal['quotd_type_of_job_id'] : null,

                            'quotd_job_desc_id' => !empty($ctVal['quotd_job_desc_id']) ? $ctVal['quotd_job_desc_id'] : null,

                            'quotd_part_id'            => !empty($ctVal['quotd_part_id']) ? $ctVal['quotd_part_id'] : null,
                            'quotd_part_no'            => !empty($ctVal['quotd_part_no']) ? $ctVal['quotd_part_no'] : null,

                            'quotd_process_at_id'         => !empty($ctVal['quotd_process_at_id']) ? $ctVal['quotd_process_at_id'] : null,

                            'quotd_description'        => !empty($ctVal['quotd_description']) ? $ctVal['quotd_description'] : null,

                            'quotd_qty'           => !empty($ctVal['quotd_qty']) ? $ctVal['quotd_qty'] : null,

                            'quotd_unit_id'            => !empty($ctVal['quotd_unit_id']) ? $ctVal['quotd_unit_id'] : null,

                            'quotd_rate_unit'           => !empty($ctVal['quotd_rate_unit']) ? $ctVal['quotd_rate_unit'] : null,

                            'quotd_rate_unit_id'            => !empty($ctVal['quotd_rate_unit_id']) ? $ctVal['quotd_rate_unit_id'] : null,


                            'quotd_minimum_charge'           => !empty($ctVal['quotd_minimum_charge']) ? $ctVal['quotd_minimum_charge'] : null,

                            'quotd_minimum_charge_id'            => !empty($ctVal['quotd_minimum_charge_unit_id']) ? $ctVal['quotd_minimum_charge_unit_id'] : null,

                            'quotd_conveyance_charge' => !empty($ctVal['quotd_conveyance_charge']) ? $ctVal['quotd_conveyance_charge'] : null,
                            
                            'quotd_remark' => !empty($ctVal['quotd_remark']) ? $ctVal['quotd_remark'] : null,

                            'status' => 'Y',
                        ]);
                    }
                }
            }

            if($quotation_data->save())
            {
                DB::commit();

                $quot_no = !empty($quotation_data->quot_number) ? '_' . str_replace('/', '_', $quotation_data->quot_number) : "";
                $cust = DB::table('customers')->where('id', $quotation_data->quot_customer_id)->value('customer');
                $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
                $pdf_name = 'Quotation' . $quot_no . $cust_name;

                GeneratePdf($quotation_data->quot_id, $pdf_name, 'quotation', 'add');
                $encodedId = base64_encode($quotation_data->quot_id);
                $url = hasAccess("quotation", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=quotation") : "";

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

    public function edit(Request $request)
    {
        $isAnyPartInUse = false;
        $quotation_data =  DB::select('CALL quotation_master(?)', [$request->id]);
        if(!empty($quotation_data))
        {
            $quotation_data = $quotation_data[0];
            $quotation_data->quot_date = $quotation_data->quot_date != "" ? Date::createFromFormat('Y-m-d',  $quotation_data->quot_date)->format('d/m/Y') : "";
            // if(isset($quotation_data->company_logo))
            // {
            //     $quotation_data->company_logo = base64_encode($quotation_data->company_logo);
            // }
            if(isset($quotation_data->cmp_logo))
            {
                $quotation_data->cmp_logo = base64_encode($quotation_data->cmp_logo);
            }
            if(isset($quotation_data->quot_image))
            {
                $quotation_data->quot_image = base64_encode($quotation_data->quot_image);
            }
            
            $quot_no = !empty($quotation_data->quot_number) ? '_' . str_replace('/', '_', $quotation_data->quot_number) : "";
            $cust = DB::table('customers')->where('id', $quotation_data->quot_customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $quotation_data->pdf_name = 'Quotation' . $quot_no . $cust_name;
        }
        
        $quotation_details_data = DB::select('CALL quotation_details(?)', [$request->id]);
       

        if($quotation_details_data){
            foreach($quotation_details_data as $dKey => $dVal)
            {
                $dVal->quotd_inq_date = $dVal->inq_date != "" ? Date::createFromFormat('Y-m-d',  $dVal->inq_date)->format('d/m/Y') : "";
                $dVal->quotd_inq_number = $dVal->inq_number ;

                $total_oad_qty = OrderAcceptanceDetails::where('oad_quotd_id', '=', $dVal->quotd_id)->sum('oad_qty');
                $total_used_qty = $total_oad_qty;

                    if($total_used_qty != null && $total_used_qty > 0){
                        $dVal->in_use = true;
                        $dVal->used_qty = $total_used_qty;
                        $isAnyPartInUse = true;
                    } else {
                        $dVal->in_use = false;
                        $dVal->used_qty = 0;
                    }
            }
        }

        if($isAnyPartInUse == true){
            $quotation_data->in_use = true;
        } else {
            $quotation_data->in_use = false;
        }

        if($quotation_data)
        {
            return response()->json([
                'quotation_data'         => $quotation_data,
                'quotation_details_data' => $quotation_details_data,
                'response_code'          => '1',
                'response_message'        => '',
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

    public function update(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $year_data = getCurrentYearData();
            $validated = $request->validate(['quot_sequence' => ['required', 'max:155', Rule::unique('quotation')->where(function ($query) use ($request, $year_data) { $query->where('year_id', '=', $year_data->id);})->ignore($request->id, 'quot_id')],
                'quot_number' => ['required','max:155',Rule::unique('quotation')->where(function ($query) use ($request, $year_data) { $query->where('year_id', '=', $year_data->id); })->ignore($request->id, 'quot_id')],
            ], [
                'quot_sequence.unique' => 'Quotation No. Already Exists',
                'quot_sequence.required' => 'Please Enter Quotation No.',
            ]);


            $imgs = Quotation::where('quot_id', $request->id)->value('quot_revision_doc');

            $file = new File();
            $uploadDoc = '';
            $blobImage = '';

            if($request->revision_doc_doc != '' && $request->revision_doc_doc != null){
                if($imgs && !empty($imgs)){
                    if($imgs != $request->revision_doc_doc){
                            $file->delete_file($imgs);
                    }                 
                }      
                
                $isFound = $file->getFileFromTemp($request->revision_doc_doc,'revision');

                if($isFound !== false){                    
                    $uploadDoc = $isFound;
                    $filePath = storage_path('app/public/' . $uploadDoc);
                    $blobImage = file_exists($filePath) ? file_get_contents($filePath) : null;
                }else{
                    $uploadDoc = $request->revision_doc_doc;
                    $filePath = storage_path('app/public/' . $uploadDoc);
                    $blobImage = $blobImage = file_get_contents($filePath);;
                }
            }else{
                if($imgs && !empty($imgs)){
                    $file->delete_file($imgs);
                }  
            }   

            $page_id = getMenuIdBassedOnDisplayName('quotation');
            $LocationData = getCurrentLocation();
            $assign_format_no = getAssignFormateNoForTransaction($LocationData->location_id, $page_id->id, $request->quot_date);

            $quotation_data = Quotation::where('quot_id', $request->id)->update([
                'quot_sequence'       => $request->quot_sequence ?? null,
                'quot_number'         => $request->quot_number ?? null,
                'quot_date'           => isset($request->quot_date) ? Date::createFromFormat('d/m/Y', $request->quot_date)->format('Y-m-d') : null,
                'quot_customer_id'    => $request->quot_customer_id ?? null,
                'quot_sac_id'           => $request->quot_sac_id ?? null,
                'quot_kind_attn_id'   => $request->quot_kind_attn_id ?? null,
                'quot_offer_validity'   => $request->quot_offer_validity ?? null,
                'quot_ref_no_date'    => $request->quot_ref_no_date ?? null,
                'quot_special_note'   => $request->quot_special_note ?? null,
                'quot_prepared_by_id' => $request->quot_prepared_by_id ?? null,
                'quot_authorised_by_id' => $request->quot_authorised_by_id ?? null,
                'quot_copy_from_id'     => $request->quot_copy_from_id ?? null,
                'quot_terms_and_conditions'     => $request->quot_terms_and_conditions ?? null,
                'quot_revision_doc'     => $uploadDoc,
                'quot_image'            => $blobImage,
                // 'assign_format_no'      => $assign_format_no, // not update assign formate discussion ramde sir
                'last_on'             => Carbon::now('Asia/Kolkata'),
                'last_by'             => Auth::id(),
            ]);

            if(!$quotation_data)
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }

            $update_details =  QuotationDetails::where('quotd_quot_id',$request->id)->update(['status' => 'D',]);
            $quotation_details_data = json_decode($request->quotation_details_data, true);
            foreach($quotation_details_data as $ctVal)
            {
                if($ctVal['quotd_id'] == 0)
                {
                    QuotationDetails::create([
                        'quotd_quot_id' => $request->id,

                        'quotd_inqd_id' => !empty($ctVal['quotd_inqd_id']) ? $ctVal['quotd_inqd_id'] : null,

                        'quotd_test_method_id' => !empty($ctVal['quotd_test_method_id']) ? $ctVal['quotd_test_method_id'] : null,

                        'quotd_type_of_job_id' => !empty($ctVal['quotd_type_of_job_id']) ? $ctVal['quotd_type_of_job_id'] : null,

                        'quotd_job_desc_id' => !empty($ctVal['quotd_job_desc_id']) ? $ctVal['quotd_job_desc_id'] : null,

                        'quotd_part_id' => !empty($ctVal['quotd_part_id']) ? $ctVal['quotd_part_id'] : null,
                        'quotd_part_no' => !empty($ctVal['quotd_part_no']) ? $ctVal['quotd_part_no'] : null,

                        'quotd_process_at_id' => !empty($ctVal['quotd_process_at_id']) ? $ctVal['quotd_process_at_id'] : null,

                        'quotd_description' => !empty($ctVal['quotd_description']) ? $ctVal['quotd_description'] : null,

                        'quotd_qty' => !empty($ctVal['quotd_qty']) ? $ctVal['quotd_qty'] : null,
                        'quotd_unit_id' => !empty($ctVal['quotd_unit_id']) ? $ctVal['quotd_unit_id'] : null,

                        'quotd_rate_unit' => !empty($ctVal['quotd_rate_unit']) ? $ctVal['quotd_rate_unit'] : null,
                        'quotd_rate_unit_id' => !empty($ctVal['quotd_rate_unit_id']) ? $ctVal['quotd_rate_unit_id'] : null,

                        'quotd_minimum_charge' => !empty($ctVal['quotd_minimum_charge']) ? $ctVal['quotd_minimum_charge'] : null,
                        'quotd_minimum_charge_id' => !empty($ctVal['quotd_minimum_charge_unit_id']) ? $ctVal['quotd_minimum_charge_unit_id'] : null,
                       
                        'quotd_conveyance_charge' => !empty($ctVal['quotd_conveyance_charge']) ? $ctVal['quotd_conveyance_charge'] : null,

                        'quotd_remark' => !empty($ctVal['quotd_remark']) ? $ctVal['quotd_remark'] : null,
                        'status' => 'Y',
                    ]);
                }
                else
                {
                    QuotationDetails::where('quotd_id', $ctVal['quotd_id'])->update([
                         //'quotd_inqd_id' => !empty($ctVal['quotd_inqd_id']) ? $ctVal['quotd_inqd_id'] : null,

                         'quotd_test_method_id' => !empty($ctVal['quotd_test_method_id']) ? $ctVal['quotd_test_method_id'] : null,

                        'quotd_type_of_job_id' => !empty($ctVal['quotd_type_of_job_id']) ? $ctVal['quotd_type_of_job_id'] : null,

                        'quotd_job_desc_id' => !empty($ctVal['quotd_job_desc_id']) ? $ctVal['quotd_job_desc_id'] : null,

                        'quotd_part_id' => !empty($ctVal['quotd_part_id']) ? $ctVal['quotd_part_id'] : null,
                        'quotd_part_no' => !empty($ctVal['quotd_part_no']) ? $ctVal['quotd_part_no'] : null,

                        'quotd_process_at_id' => !empty($ctVal['quotd_process_at_id']) ? $ctVal['quotd_process_at_id'] : null,

                        'quotd_description' => !empty($ctVal['quotd_description']) ? $ctVal['quotd_description'] : null,

                        'quotd_qty' => !empty($ctVal['quotd_qty']) ? $ctVal['quotd_qty'] : null,
                        'quotd_unit_id' => !empty($ctVal['quotd_unit_id']) ? $ctVal['quotd_unit_id'] : null,

                        'quotd_rate_unit' => !empty($ctVal['quotd_rate_unit']) ? $ctVal['quotd_rate_unit'] : null,
                        'quotd_rate_unit_id' => !empty($ctVal['quotd_rate_unit_id']) ? $ctVal['quotd_rate_unit_id'] : null,

                        'quotd_minimum_charge' => !empty($ctVal['quotd_minimum_charge']) ? $ctVal['quotd_minimum_charge'] : null,
                        'quotd_minimum_charge_id' => !empty($ctVal['quotd_minimum_charge_unit_id']) ? $ctVal['quotd_minimum_charge_unit_id'] : null,
                       
                        'quotd_conveyance_charge' => !empty($ctVal['quotd_conveyance_charge']) ? $ctVal['quotd_conveyance_charge'] : null,

                        'quotd_remark' => !empty($ctVal['quotd_remark']) ? $ctVal['quotd_remark'] : null,
                        'status' => 'Y',
                    ]);
                }
            }

            QuotationDetails::where('quotd_quot_id',$request->id)->where('status','D')->delete();

            if($quotation_data)
            {
                DB::commit();

                $quot = Quotation::where('quot_id', $request->id)->first();
                $quot_no = !empty($quot->quot_number) ? '_' . str_replace('/', '_', $quot->quot_number) : "";
                $cust = DB::table('customers')->where('id', $quot->quot_customer_id)->value('customer');
                $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
                $pdf_name = 'Quotation' . $quot_no . $cust_name;

                GeneratePdf($quot->quot_id, $pdf_name, 'quotation', 'update');
                $encodedId = base64_encode($quot->quot_id);
                $url = hasAccess("quotation", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=quotation") : "";

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

     public function destroy(Request $request)
    {
        try
        {
            $oa_details = OrderAcceptanceDetails::
            leftJoin('quotation_details','quotation_details.quotd_id','=','order_acceptance_details.oad_quotd_id')
            ->where('quotation_details.quotd_quot_id',$request->id)->get();
            if($oa_details->isNotEmpty()){ 
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Quotation Is Used In Order Acceptance",
                ]);
            }
            $delete_image = Quotation::where('quot_id', $request->id)->value('quot_revision_doc');

            if($delete_image && !empty($delete_image)){
                $file = new File();
                $file->delete_file($delete_image);
            }
            Quotation::where('quot_id',$request->id)->delete();
            QuotationDetails::where('quotd_quot_id',$request->id)->delete();
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

    public function getLatestQuotationNumber(Request $request)
    {
        $modal  =  Quotation::class;
        $sequence = 'quot_sequence';           
        $sup_num_format = getMarketingLatestSequence($modal,$sequence);   

        // $quotationLatestDetail = Quotation::select('quotation_country_of_origin_of_goods_id','quotation_currency_id')->orderBy('quotation_id','Desc')->first();
       
        return response()->json([
          'response_code' => 1,
          'latest_no'     => $sup_num_format['format'],
          'number'        => $sup_num_format['isFound'],
      ]);
    }

    public function CustomerContactPersonData(Request $request)
    {
        $contactPersons = CustomerContacts::where('customer_id', $request->customer_id)->select('id', 'contact_person','contact_email','contact_mobile_no')->orderBy('contact_person', 'asc')->get();
        return response()->json([
            'kind_attention' => $contactPersons,
            'response_code' => '1'
        ]);
    }

    /*public function getPendingQuotCustomers(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();

        $get_quot_customer = Inquiry::select(
                'customers.id',
                'customers.customer'
            )
            ->join('inquiry_details', 'inquiry_details.inq_id', '=', 'inquiry.inq_id')
            ->join('customers', 'customers.id', '=', 'inquiry.inq_customer_id')

            // estimation join OPTIONAL (only for feasibility Yes)
            ->leftJoin(
                'estimation_costing',
                'estimation_costing.ec_inqd_id',
                '=',
                'inquiry_details.inqd_id'
            )

            // exclude already quoted
            ->leftJoin(
                'quotation_details',
                'quotation_details.quotd_inqd_id',
                '=',
                'inquiry_details.inqd_id'
            )

            ->whereIn('inquiry.year_id', $yearIds)

            // Direct OR Estimation logic
            ->where(function ($q) {
                $q->where('inquiry_details.inqd_feasibility_required', 'No')
                ->orWhere(function ($q2) {
                    $q2->where('inquiry_details.inqd_feasibility_required', 'Yes')
                        ->whereNotNull('estimation_costing.ec_inqd_id');
                });
            })

            // quotation not yet created
            ->whereNull('quotation_details.quotd_inqd_id')

            ->orderBy('customers.customer', 'asc')
            ->distinct()
            ->get();

        return response()->json([
            'response_code' => 1,
            'quot_customer' => $get_quot_customer,
        ]);
    }*/

    public function getPendingQuotCustomers(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();

        $get_quot_customer = Inquiry::select(
                'customers.id',
                'customers.customer',
                DB::raw("
                    (
                        IFNULL(inquiry_details.inqd_quantity, 0) 
                            -
                            (
                                SELECT IFNULL(SUM(isc.inq_sc_qty), 0)
                                FROM inquiry_short_close as isc
                                WHERE isc.inq_sc_inqd_id = inquiry_details.inqd_id
                            )
                        ) as pend_inq_qty
                    ")
            )
            ->join('inquiry_details', 'inquiry_details.inq_id', '=', 'inquiry.inq_id')
            ->join('customers', 'customers.id', '=', 'inquiry.inq_customer_id')

            // estimation join OPTIONAL (only for feasibility Yes)
            ->leftJoin(
                'feasibility_review',
                'feasibility_review.fr_inqd_id',
                '=',
                'inquiry_details.inqd_id'
            )

            // quotation join
            ->leftJoin(
                'quotation_details',
                'quotation_details.quotd_inqd_id',
                '=',
                'inquiry_details.inqd_id'
            )
            ->whereIn('inquiry.year_id', $yearIds)

            // Direct OR Feasibility logic
            ->where(function ($q) {
                $q->where('inquiry_details.inqd_feasibility_required', 'No')
                ->orWhere(function ($q2) {
                    $q2->where('inquiry_details.inqd_feasibility_required', 'Yes')
                        ->where('feasibility_review.fr_result_id', '=', 'Feasible');
                });
            })

            // 🔹 EDIT CONDITION HANDLING
            ->where(function ($q) use ($request) {
                if (!empty($request->id)) {
                    $q->whereNull('quotation_details.quotd_inqd_id')
                    ->orWhere('quotation_details.quotd_quot_id', $request->id);
                } else {
                    $q->whereNull('quotation_details.quotd_inqd_id');
                }
            })

            ->orderBy('customers.customer', 'asc')
            ->distinct()
            ->having('pend_inq_qty','>',0)
            ->get();

            $get_quot_customer = $get_quot_customer->unique(['id']);

            $get_quot_customer = $get_quot_customer->values()->all();

        return response()->json([
            'response_code' => 1,
            'quot_customer' => $get_quot_customer,
        ]);
    }

    /*public function getPendingInqEstimationList(Request $request)
    {
        $yearIds    = getCompanyYearIdsToTill();
        $customerId = $request->customer_id;
        $quotationId = $request->id; // 👈 edit time quotation id

        $data = Inquiry::select(
                'inquiry.inq_id',
                'inquiry.inq_number as quotd_inq_number',
                'inquiry.inq_date as quotd_inq_date',
                'customers.customer_code',
                'customers.customer',
                'inquiry.inq_ref_no_date',
                'inquiry_details.inqd_id',
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
                'inquiry_details.inqd_remark',

                // 👇 extra fields for edit time
                'quotation_details.quotd_id',
                'quotation_details.quotd_quot_id'
            )
            ->join('inquiry_details', 'inquiry_details.inq_id', '=', 'inquiry.inq_id')
            ->join('customers', 'customers.id', '=', 'inquiry.inq_customer_id')
            ->leftJoin('type_of_job', 'type_of_job.id', '=', 'inquiry_details.inqd_type_of_job_id')
            ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'inquiry_details.inqd_job_description_id')
            ->leftJoin('part', 'part.part_id', '=', 'inquiry_details.inqd_part_id')
            ->leftJoin('unit', 'unit.id', '=', 'inquiry_details.inqd_unit_id')

            ->leftJoin('estimation_costing', 'estimation_costing.ec_inqd_id', '=', 'inquiry_details.inqd_id')

            ->leftJoin('quotation_details', 'quotation_details.quotd_inqd_id', '=', 'inquiry_details.inqd_id')

            ->where('inquiry.inq_customer_id', $customerId)
            ->whereIn('inquiry.year_id', $yearIds)

            // feasibility condition
            ->where(function ($q) {
                $q->where('inquiry_details.inqd_feasibility_required', 'No')
                ->orWhere(function ($q2) {
                    $q2->where('inquiry_details.inqd_feasibility_required', 'Yes')
                        ->whereNotNull('estimation_costing.ec_inqd_id');
                });
            })

            // 👇 IMPORTANT PART (add edit-time logic)
            ->where(function ($q) use ($quotationId) {
                if ($quotationId) {
                    // edit time: allow current quotation items
                    $q->whereNull('quotation_details.quotd_inqd_id')
                    ->orWhere('quotation_details.quotd_quot_id', $quotationId);
                } else {
                    // add time: only pending
                    $q->whereNull('quotation_details.quotd_inqd_id');
                }
            })

            ->orderBy('inquiry.inq_date', 'desc')
            ->get();

        foreach ($data as $row) {
            if ($row->quotd_inq_date) {
                $row->quotd_inq_date = Date::createFromFormat('Y-m-d', $row->quotd_inq_date)->format('d/m/Y');
            }

            // 👇 frontend ke liye flag
            $row->in_use = ($row->quotd_quot_id && $row->quotd_quot_id != $quotationId);
        }

        return response()->json([
            'response_code' => 1,
            'inq_data' => $data
        ]);
    }*/

    public function getPendingInqEstimationList(Request $request)
    {
        $yearIds     = getCompanyYearIdsToTill();
        $customerId  = $request->customer_id;
        $quotationId = $request->id;
        $used_sc_inqd_ids = InquiryShortClose::whereNotNull('inq_sc_inqd_id')->pluck('inq_sc_inqd_id');

        /*
        |----------------------------------------------------------------------
        | 1️⃣ EDIT DATA (quotation_details first priority)
        |----------------------------------------------------------------------
        */
        $editData = collect();

        if (!empty($quotationId)) {
            $editData = Inquiry::select(
                    'inquiry.inq_id',
                    'inquiry.inq_number as quotd_inq_number',
                    'inquiry.inq_date as quotd_inq_date',
                    'customers.customer_code',
                    'customers.customer',
                    'inquiry.inq_ref_no_date',

                    'inquiry_details.inqd_id as quotd_inqd_id',
                    'inquiry_details.inqd_test_method',
                    'inquiry_details.inqd_type_of_job_id',
                    'type_of_job.type_of_job',
                    'quotation_details.quotd_job_desc_id',
                    'job_descriptions.job_description',
                    'quotation_details.quotd_part_id',
                    'quotation_details.quotd_part_no as inqd_part_no',
                    // \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),

                   
                    'quotation_details.quotd_process_at_id as inqd_process_at',
                    'quotation_details.quotd_qty as inqd_quantity',
                    'quotation_details.quotd_unit_id as inqd_unit_id',
                    'unit.unit',
                    'quotation_details.quotd_remark as inqd_remark',
                    'quotation_details.quotd_id'
                )
                ->join('inquiry_details', 'inquiry_details.inq_id', '=', 'inquiry.inq_id')
                ->join('quotation_details', 'quotation_details.quotd_inqd_id', '=', 'inquiry_details.inqd_id')
                ->join('customers', 'customers.id', '=', 'inquiry.inq_customer_id')
                ->leftJoin('type_of_job', 'type_of_job.id', '=', 'inquiry_details.inqd_type_of_job_id')
                ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'quotation_details.quotd_job_desc_id')
                //->leftJoin('part', 'part.part_id', '=', 'quotation_details.quotd_part_id')
                ->leftJoin('unit', 'unit.id', '=', 'quotation_details.quotd_unit_id')
                ->where('quotation_details.quotd_quot_id', $quotationId)
                ->where('inquiry.inq_customer_id', $customerId)
                ->whereNotIn('inquiry_details.inqd_id',$used_sc_inqd_ids)
                ->whereIn('inquiry.year_id', $yearIds)
                ->get();
        }

        /*
        |----------------------------------------------------------------------
        | 2️⃣ PENDING DATA (unmapped inquiry_details)
        |----------------------------------------------------------------------
        */
        $used_inqd_ids = QuotationDetails::whereNotNull('quotd_inqd_id')->pluck('quotd_inqd_id');
        $pendingData = Inquiry::select(
                'inquiry.inq_id',
                'inquiry.inq_number as quotd_inq_number',
                'inquiry.inq_date as quotd_inq_date',
                'customers.customer_code',
                'customers.customer',
                'inquiry.inq_ref_no_date',

                'inquiry_details.inqd_id as quotd_inqd_id',
                'inquiry_details.inqd_test_method',
                'inquiry_details.inqd_type_of_job_id',
                'type_of_job.type_of_job',
                'inquiry_details.inqd_job_description_id',
                'job_descriptions.job_description',
                'inquiry_details.inqd_part_id',
                'inquiry_details.inqd_part_no',
                //\DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),

                // 🔥 FROM inquiry_details
                'inquiry_details.inqd_process_at',
                'inquiry_details.inqd_quantity',
                'inquiry_details.inqd_unit_id',
                'unit.unit',
                'inquiry_details.inqd_remark',

                DB::raw('NULL as quotd_id')
            )
            ->join('inquiry_details', 'inquiry_details.inq_id', '=', 'inquiry.inq_id')
            ->join('customers', 'customers.id', '=', 'inquiry.inq_customer_id')
            ->leftJoin('type_of_job', 'type_of_job.id', '=', 'inquiry_details.inqd_type_of_job_id')
            ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'inquiry_details.inqd_job_description_id')
            //->leftJoin('part', 'part.part_id', '=', 'inquiry_details.inqd_part_id')
            ->leftJoin('unit', 'unit.id', '=', 'inquiry_details.inqd_unit_id')
            ->leftJoin('feasibility_review', 'feasibility_review.fr_inqd_id', '=', 'inquiry_details.inqd_id')
            ->where('inquiry.inq_customer_id', $customerId)
            ->whereNotIn('inquiry_details.inqd_id',$used_inqd_ids)
            ->whereNotIn('inquiry_details.inqd_id',$used_sc_inqd_ids)
            ->whereIn('inquiry.year_id', $yearIds)

            // 🔥 feasibility condition
            ->where(function ($q) {
                $q->where('inquiry_details.inqd_feasibility_required', 'No')
                ->orWhere(function ($q2) {
                    $q2->where('inquiry_details.inqd_feasibility_required', 'Yes')
                        ->where('feasibility_review.fr_result_id', '=', 'Feasible');
                });
            })
            ->get();

        /*
        |----------------------------------------------------------------------
        | 3️⃣ MERGE BOTH
        |----------------------------------------------------------------------
        */
        $data = $editData
            ->concat($pendingData)
            ->values();

        /*
        |----------------------------------------------------------------------
        | 4️⃣ DATE FORMAT
        |----------------------------------------------------------------------
        */
        foreach ($data as $row) {
            if (!empty($row->quotd_inq_date)) {
                $row->quotd_inq_date = \Carbon\Carbon::parse($row->quotd_inq_date)
                    ->format('d/m/Y');
            }
        }

        return response()->json([
            'response_code' => 1,
            'inq_data'      => $data
        ]);
    }

    /*public function getPendingInqEstimationData(Request $request)
    {
        $inqdIds = explode(',', $request->inqd_ids);

        $data = Inquiry::select(
                'inquiry.inq_number as quotd_inq_number',
                'inquiry.inq_date as quotd_inq_date',
                'customers.customer',
                'customers.customer_code',
                'inquiry_details.inqd_id',
                'inquiry.inq_ref_no_date',
                'inquiry_details.inqd_test_method as quotd_test_method_id',
                'inquiry_details.inqd_type_of_job_id as quotd_type_of_job_id',
                'type_of_job.type_of_job',
                'inquiry_details.inqd_job_description_id as quotd_job_desc_id',
                'job_descriptions.job_description',
                'inquiry_details.inqd_part_id as quotd_part_id',
                \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
                'inquiry_details.inqd_process_at as quotd_process_at_id',
                'inquiry_details.inqd_quantity as quotd_qty',
                'inquiry_details.inqd_unit_id as quotd_unit_id',
                'unit.unit',
                //'inquiry.inq_sp_note',
                //'inquiry_details.inqd_description',
                'inquiry_details.inqd_remark as quotd_remark' 
            )
            ->join('inquiry_details', 'inquiry_details.inq_id', '=', 'inquiry.inq_id')
            ->join('customers', 'customers.id', '=', 'inquiry.inq_customer_id')
            ->leftJoin('type_of_job', 'type_of_job.id', '=', 'inquiry_details.inqd_type_of_job_id')
             ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'inquiry_details.inqd_job_description_id')
            ->leftJoin('part', 'part.part_id', '=', 'inquiry_details.inqd_part_id')
            ->leftJoin('unit', 'unit.id', '=', 'inquiry_details.inqd_unit_id')
            ->whereIn('inquiry_details.inqd_id', $inqdIds)
            ->get();

            if ($data != null) {
                foreach ($data as $cpKey => $cpVal) {
                    if ($cpVal->quotd_inq_date != null) {
                        $cpVal->quotd_inq_date = Date::createFromFormat('Y-m-d', $cpVal->quotd_inq_date)->format('d/m/Y');
                    }
                   

                }
            }

        return response()->json([
            'response_code' => 1,
            'inq_data' => $data
        ]);
    }*/

    public function getPendingInqEstimationData(Request $request)
    {
        $inqdIds     = explode(',', $request->inqd_ids);
        $quotationId = $request->id;

        /*
        |--------------------------------------------------------------------------
        | 1. EDIT DATA (already in quotation)
        |--------------------------------------------------------------------------
        */
        $edit_data = collect();

        if (!empty($quotationId)) {
            $edit_data = Inquiry::select([
                    'inquiry.inq_number as quotd_inq_number',
                    'inquiry.inq_date as quotd_inq_date',
                    'customers.customer',
                    'customers.customer_code',
                    'inquiry_details.inqd_id as quotd_inqd_id',
                    'inquiry.inq_ref_no_date',
                    'inquiry_details.inqd_test_method as quotd_test_method_id',
                    'inquiry_details.inqd_type_of_job_id as quotd_type_of_job_id',
                    'type_of_job.type_of_job',
                    'inquiry_details.inqd_job_description_id as quotd_job_desc_id',
                    'job_descriptions.job_description',
                    // 'inquiry_details.inqd_part_id as quotd_part_id',
                    'quotation_details.quotd_part_no',
                    //\DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
                    'inquiry_details.inqd_process_at as quotd_process_at_id',
                    'quotation_details.quotd_description',

                    'quotation_details.quotd_qty',
                    'quotation_details.quotd_unit_id',
                    'quotation_details.quotd_rate_unit',
                    'quotation_details.quotd_rate_unit_id',
                    'quotation_details.quotd_minimum_charge',
                    'quotation_details.quotd_minimum_charge_id as quotd_minimum_charge_unit_id',
                    'quotation_details.quotd_conveyance_charge',
                    'quotation_details.quotd_remark',
                    'quotation_details.quotd_id',
                ])
                ->join('inquiry_details', 'inquiry_details.inq_id', '=', 'inquiry.inq_id')
                ->join('quotation_details', 'quotation_details.quotd_inqd_id', '=', 'inquiry_details.inqd_id')
                ->join('customers', 'customers.id', '=', 'inquiry.inq_customer_id')
                ->leftJoin('type_of_job', 'type_of_job.id', '=', 'inquiry_details.inqd_type_of_job_id')
                ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'inquiry_details.inqd_job_description_id')
                //->leftJoin('part', 'part.part_id', '=', 'inquiry_details.inqd_part_id')
                ->where('quotation_details.quotd_quot_id', $quotationId)
                ->whereIn('inquiry_details.inqd_id', $inqdIds)
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | 2. PENDING DATA (not yet added)
        |--------------------------------------------------------------------------
        */
        $pending_data = Inquiry::select([
                'inquiry.inq_number as quotd_inq_number',
                'inquiry.inq_date as quotd_inq_date',
                'customers.customer',
                'customers.customer_code',
                'inquiry_details.inqd_id as quotd_inqd_id',
                'inquiry.inq_ref_no_date',
                'inquiry_details.inqd_test_method as quotd_test_method_id',
                'inquiry_details.inqd_type_of_job_id as quotd_type_of_job_id',
                'type_of_job.type_of_job',
                'inquiry_details.inqd_job_description_id as quotd_job_desc_id',
                'job_descriptions.job_description',
                'inquiry_details.inqd_part_id as quotd_part_id',
                'inquiry_details.inqd_part_no as quotd_part_no',
                //\DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
                'inquiry_details.inqd_process_at as quotd_process_at_id',
                'inquiry_details.inqd_description as quotd_description',

                'inquiry_details.inqd_quantity as quotd_qty',
                'inquiry_details.inqd_unit_id as quotd_unit_id',

                DB::raw('NULL as quotd_rate_unit'),
                DB::raw('NULL as quotd_rate_unit_id'),
                DB::raw('NULL as quotd_minimum_charge'),
                DB::raw('NULL as quotd_minimum_charge_id'),
                DB::raw('NULL as quotd_conveyance_charge'),
                DB::raw('inquiry_details.inqd_remark as quotd_remark'),
                DB::raw('0 as quotd_id'),
            ])
            ->join('inquiry_details', 'inquiry_details.inq_id', '=', 'inquiry.inq_id')
            ->join('customers', 'customers.id', '=', 'inquiry.inq_customer_id')
            ->leftJoin('type_of_job', 'type_of_job.id', '=', 'inquiry_details.inqd_type_of_job_id')
            ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'inquiry_details.inqd_job_description_id')
            //->leftJoin('part', 'part.part_id', '=', 'inquiry_details.inqd_part_id')
            ->leftJoin('quotation_details', function ($join) use ($quotationId) {
                $join->on('quotation_details.quotd_inqd_id', '=', 'inquiry_details.inqd_id')
                    ->where('quotation_details.quotd_quot_id', '=', $quotationId);
            })
            ->whereIn('inquiry_details.inqd_id', $inqdIds)
            ->whereNull('quotation_details.quotd_id')
            ->orderBy('inquiry_details.inqd_id','desc')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 3. MERGE + GROUP (same as reference)
        |--------------------------------------------------------------------------
        */
        $data = collect($pending_data)->merge($edit_data);

        $grouped = $data->groupBy('quotd_inqd_id');

        $final_data = $grouped->map(function ($items) {
            return $items->reduce(function ($carry, $item) {
                if (!$carry) {
                    return $item;
                }

                $carry->quotd_qty += (float) $item->quotd_qty;
                return $carry;
            });
        })->values();

        /*
        |--------------------------------------------------------------------------
        | 4. FORMAT DATES
        |--------------------------------------------------------------------------
        */
        foreach ($final_data as $row) {
            if (!empty($row->quotd_inq_date)) {
                $row->quotd_inq_date = \Carbon\Carbon::createFromFormat('Y-m-d', $row->quotd_inq_date)
                    ->format('d/m/Y');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 5. RESPONSE
        |--------------------------------------------------------------------------
        */
        return response()->json([
            'response_code' => 1,
            'inq_data'      => $final_data
        ]);
    }

    public function getAllQuotationNo(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();

        $quot_terms = Quotation::select(
                'quotation.quot_id',
                'quotation.quot_number'
            )
            ->whereIn('quotation.year_id', $yearIds)
            ->where('quotation.quot_id', '!=', $request->id)
            ->orderBy('quotation.quot_number', 'asc')
            ->get();

        return response()->json([
            'response_code' => 1,
            'quot_terms' => $quot_terms,
        ]);
    }

    public function getQuotTermsConditions(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();

        $terms_conditions = Quotation::select(
                'quotation.quot_id',
                'quotation.quot_terms_and_conditions'
            )
            ->where('quotation.quot_id',$request->quot_id)
            ->whereIn('quotation.year_id', $yearIds)
            ->orderBy('quotation.quot_terms_and_conditions', 'asc')
            ->first();

        return response()->json([
            'response_code' => 1,
            'terms_conditions' => $terms_conditions,
        ]);
    }

    public function getQuotationPartNoList(Request $request)
    {
        $term = $request->term ?? '';
        $job_desc_id = $request->job_desc_id ?? '';

        if (!empty($term)) {
            $query = QuotationDetails::select('quotd_part_no')
                ->whereNotNull('quotd_part_no')
                ->where('quotd_part_no', '!=', '')
                ->where('status', 'Y')
                ->where('quotd_part_no', 'LIKE', '%' . $term . '%');

            if (!empty($job_desc_id)) {
                $query->where('quotd_job_desc_id', $job_desc_id);
            }

            $partNos = $query->groupBy('quotd_part_no')->orderBy('quotd_part_no', 'asc')->get();

            if ($partNos->isEmpty() && !empty($job_desc_id)) {
                $partNos = QuotationDetails::select('quotd_part_no')
                    ->whereNotNull('quotd_part_no')
                    ->where('quotd_part_no', '!=', '')
                    ->where('status', 'Y')
                    ->where('quotd_part_no', 'LIKE', '%' . $term . '%')
                    ->groupBy('quotd_part_no')
                    ->orderBy('quotd_part_no', 'asc')
                    ->get();
            }

            if ($partNos->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($partNos as $row) {
                    $output .= '<li parent-id="quotd_part_no" list-id="quotd_part_no_list" class="list-group-item" tabindex="0">' . e($row->quotd_part_no) . '</li>';
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