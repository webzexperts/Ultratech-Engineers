<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\QuotationDetails;
use App\Models\Transaction\OrderAcceptance;
use App\Models\Transaction\OrderAcceptanceDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use App\Models\File;
use App\Models\Transaction\PlanningManagementDetails;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


class OrderAcceptanceController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-order_acceptance');
    }

    public function index(OrderAcceptanceDetails $oa_data ,Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $oa_data = OrderAcceptanceDetails::select([
            'order_acceptance.oa_id',
            'order_acceptance.oa_number',
            'order_acceptance.oa_sequence',
            'order_acceptance.oa_date',
            'order_acceptance.oa_type_id',
            'customers.customer_code',
            'customers.customer',
            'order_acceptance.oa_po_number',
            'order_acceptance.oa_po_date',
            'order_acceptance_details.oad_test_method_id',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'order_acceptance_details.oad_process_at_id',
            'order_acceptance_details.oad_qty',
            'oa_qty_unit.unit as oa_qty_unit_name',
            'order_acceptance_details.oad_rate_unit',
            'oa_rate_unit.unit as oa_rate_unit_name',
            'quotation.quot_number',
            'order_acceptance_details.oad_remark',
            'order_acceptance.oa_special_note',
            'order_acceptance.created_on',
            'order_acceptance.created_by',
            'order_acceptance.last_by',
            'order_acceptance.last_on'
        ])
         ->leftJoin('quotation_details','quotation_details.quotd_id','=','order_acceptance_details.oad_quotd_id')
        ->leftJoin('quotation','quotation.quot_id','=','quotation_details.quotd_quot_id')
        ->leftJoin('order_acceptance','order_acceptance.oa_id','=','order_acceptance_details.oad_oa_id')
        ->leftJoin('customers','customers.id','=','order_acceptance.oa_customer_id')
        ->leftJoin('type_of_job','type_of_job.id','=','order_acceptance_details.oad_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','order_acceptance_details.oad_job_desc_id')
        ->leftJoin('part','part.part_id','=','order_acceptance_details.oad_part_id')
        ->leftJoin('unit as oa_qty_unit','oa_qty_unit.id','=','order_acceptance_details.oad_unit_id')
        ->leftJoin('unit as oa_rate_unit','oa_rate_unit.id','=','order_acceptance_details.oad_rate_unit_id')
        ->leftJoin('customer_contacts','customer_contacts.id','=','order_acceptance.oa_kind_attn_id')

        ->where('order_acceptance.year_id', $year_data->id);

        $dataTable = DataTables::of($oa_data)
        ->filterColumn('part.part', function($query, $keyword) {
            $keyword = trim($keyword);
            $query->whereRaw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) LIKE ?", ["%{$keyword}%"]);
        })
        ->editColumn('oa_date', function($oa_data){
            if ($oa_data->oa_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $oa_data->oa_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('order_acceptance.oa_date', function ($q, $k) {
            applyDate($q, $k, 'order_acceptance.oa_date');
        })
        ->editColumn('oa_po_date', function($quotation_data){
            if ($quotation_data->oa_po_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $quotation_data->oa_po_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('order_acceptance.oa_po_date', function ($q, $k) {
            applyDate($q, $k, 'order_acceptance.oa_po_date');
        })

         ->editColumn('oad_qty', function($inquiry_data) {
            return $inquiry_data->oad_qty > 0 ? number_format((float)$inquiry_data->oad_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('order_acceptance_details.oad_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('oad_qty')
                    ->orWhere('oad_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(oad_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('oad_qty', 'like', "%{$search}%");
            }
        })
        ->filterColumn('order_acceptance.oa_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'order_acceptance.oa_number');
        })
        ->filterColumn('quotation.quot_number', function($query, $keyword) {
            applynumber($query, $keyword, 'quotation.quot_number');
        })
        ->filterColumn('order_acceptance.oa_type_id', function($query, $keyword){
            $keyword = strtolower($keyword);

            if(strpos('manual', $keyword) !== false){
                $query->where('order_acceptance.oa_type_id', "Manual");
            }
            elseif(strpos('from quotation', $keyword) !== false){
                $query->where('order_acceptance.oa_type_id', "From Quotation");
            }
        })
        
        ->addColumn('options',function($oa_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';
                if(hasAccess("order_acceptance", "print")) {
                    $oa_number = !empty($oa_data->oa_number) ? '_'.str_replace('/', '_', $oa_data->oa_number) : "";

                    $supplier_for_wolh = !empty($oa_data->supplier_name) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $oa_data->supplier_name) : "";

                    $pdfName  = 'Order_Acceptance'.$oa_number.$supplier_for_wolh;
                    $encodedId = base64_encode($oa_data->po_id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=order_acceptance");
               
                    $action .= '<li><a  class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }

                if (hasAccess("order_acceptance", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-order_acceptance"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("order_acceptance", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'order_acceptance');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $year_data = getCurrentYearData();
        $existNumber = OrderAcceptance::where([['oa_sequence',  $request->oa_sequence],['oa_number',$request->oa_number],['year_id',$year_data->id]])->first();
        
        if($existNumber)
        {
            $latestNo = $this->getLatestOANumber($request);
            $tmp =  $latestNo->getContent();
            $area = json_decode($tmp, true);
            $oa_number =   $area['latest_no'];
            $oa_sequence = $area['number'];
        }
        else
        {
            $oa_number = $request->oa_number;
            $oa_sequence = $request->oa_sequence;
        }

        $uploadDoc = '';
        $blobImage = '';
        if($request->oa_file_upload_doc){
            $file = new File();
            $isFound =  $file->getFileFromTemp($request->oa_file_upload_doc,$prefix = "mi");

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
            $oa_data =  OrderAcceptance::create([
                'oa_number'             => $oa_number ?? null,
                'oa_sequence'           => $oa_sequence ?? null,
                'oa_date'               => isset($request->oa_date) ? Date::createFromFormat('d/m/Y', $request->oa_date)->format('Y-m-d') : null,
                'oa_customer_id'        => $request->oa_customer_id ?? null,
                'oa_type_id'            => $request->oa_type_id ?? null,
                'oa_po_number'          => $request->oa_po_number ?? null,
                'oa_po_date'            => isset($request->oa_po_date) ? Date::createFromFormat('d/m/Y', $request->oa_po_date)->format('Y-m-d') : null,
                'oa_kind_attn_id'       => $request->oa_kind_attn_id ?? null,
                'oa_special_note'       => $request->oa_special_note ?? null,
                'oa_copy_from_id'       => $request->oa_copy_from_id ?? null,
                'oa_terms_and_conditions'  => $request->oa_terms_and_conditions ?? null,
                'oa_file_upload'        => $uploadDoc,
                'oa_image'              => $blobImage,
                'year_id'               => $year_data->id,
                'company_id'            => Auth::user()->company_id,
                'created_on'            => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'            => Auth::user()->id,
            ]);


            $oa_details_data = $request->oa_details_data = json_decode($request->oa_details_data, true);
            if(!empty($oa_details_data))
            {
                foreach($oa_details_data as $ctKey => $ctVal)
                {
                    if($ctVal != null)
                    {
                        $oa_details_data = OrderAcceptanceDetails::create([

                            'oad_oa_id'            => $oa_data->oa_id,

                            'oad_quotd_id'        => !empty($ctVal['oad_quotd_id']) ? $ctVal['oad_quotd_id'] : null,

                            'oad_test_method_id'        => !empty($ctVal['oad_test_method_id']) ? $ctVal['oad_test_method_id'] : null,

                            'oad_type_of_job_id'     => !empty($ctVal['oad_type_of_job_id']) ? $ctVal['oad_type_of_job_id'] : null,

                            'oad_job_desc_id' => !empty($ctVal['oad_job_desc_id']) ? $ctVal['oad_job_desc_id'] : null,

                            'oad_part_id'            => !empty($ctVal['oad_part_id']) ? $ctVal['oad_part_id'] : null,

                            'oad_process_at_id'         => !empty($ctVal['oad_process_at_id']) ? $ctVal['oad_process_at_id'] : null,

                            'oad_description'        => !empty($ctVal['oad_description']) ? $ctVal['oad_description'] : null,

                            'oad_qty'           => !empty($ctVal['oad_qty']) ? $ctVal['oad_qty'] : null,

                            'oad_unit_id'            => !empty($ctVal['oad_unit_id']) ? $ctVal['oad_unit_id'] : null,

                            'oad_rate_unit'           => !empty($ctVal['oad_rate_unit']) ? $ctVal['oad_rate_unit'] : null,

                            'oad_rate_unit_id'            => !empty($ctVal['oad_rate_unit_id']) ? $ctVal['oad_rate_unit_id'] : null,


                            'oad_minimum_charge'           => !empty($ctVal['oad_minimum_charge']) ? $ctVal['oad_minimum_charge'] : null,

                            'oad_minimum_charge_unit_id'            => !empty($ctVal['oad_minimum_charge_unit_id']) ? $ctVal['oad_minimum_charge_unit_id'] : null,

                            'oad_conveyance_charge' => !empty($ctVal['oad_conveyance_charge']) ? $ctVal['oad_conveyance_charge'] : null,
                            
                            'oad_remark' => !empty($ctVal['oad_remark']) ? $ctVal['oad_remark'] : null,

                            'oad_status' => 'Y',
                        ]);
                    }
                }
            }

            if($oa_data->save())
            {
                DB::commit();

                $oa_customer = OrderAcceptance::select('customers.customer')
                ->leftJoin('customers', 'customers.id', '=', 'order_acceptance.oa_customer_id')
                ->where('order_acceptance.oa_customer_id', $oa_data->oa_customer_id)
                ->first();

                $oa_number = !empty($oa_data->oa_number) ? '_'.str_replace('/', '_', $oa_data->oa_number) : "";

                $customer_for_wolh = !empty($oa_customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $oa_customer->customer) : "";

                $pdf_name = 'Order_Acceptance'.$oa_number.$customer_for_wolh;

                GeneratePdf($oa_data->oa_id,$pdf_name,'order_acceptance','add');
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
        $oa_data =  DB::select('CALL order_acceptance_master(?)', [$request->id]);
        if(!empty($oa_data))
        {
            $oa_data = $oa_data[0];
            $oa_data->oa_date = $oa_data->oa_date != "" ? Date::createFromFormat('Y-m-d',  $oa_data->oa_date)->format('d/m/Y') : "";

            $oa_data->oa_po_date = $oa_data->oa_po_date != "" ? Date::createFromFormat('Y-m-d',  $oa_data->oa_po_date)->format('d/m/Y') : "";
            if(isset($oa_data->company_logo))
            {
                $oa_data->company_logo = base64_encode($oa_data->company_logo);
            }
            if(isset($oa_data->oa_image))
            {
                $oa_data->oa_image = base64_encode($oa_data->oa_image);
            }
            
        }
        
        $oa_details_data = DB::select('CALL order_acceptance_details(?)', [$request->id]);
       

        if($oa_details_data){
            foreach($oa_details_data as $dKey => $dVal)
            {
                $dVal->oad_quot_date = $dVal->oad_quot_date != "" ? Date::createFromFormat('Y-m-d',  $dVal->oad_quot_date)->format('d/m/Y') : "";
                $dVal->oad_quot_number = $dVal->oad_quot_number ;

                $dVal->pend_oa_qty = $dVal->pend_oa_qty + $dVal->oad_qty;


                $oad_used_qty = PlanningManagementDetails::where('pmd_oad_id', '=', $dVal->oad_id)->sum('pmd_plan_qty');
                $total_used_qty = $oad_used_qty;

                    if($total_used_qty != null && $total_used_qty > 0){
                        $dVal->in_use = true;
                        $dVal->used_qty = $total_used_qty;
                    } else {
                        $dVal->in_use = false;
                        $dVal->used_qty = 0;
                    }
            }
        }

        if($oa_data)
        {
            return response()->json([
                'oa_data'         => $oa_data,
                'oa_details_data' => $oa_details_data,
                'response_code'   => '1',
                'response_message'=> '',
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
            $validated = $request->validate(['oa_sequence' => ['required', 'max:155', Rule::unique('order_acceptance')->where(function ($query) use ($request, $year_data) { $query->where('year_id', '=', $year_data->id);})->ignore($request->id, 'oa_id')],
                'oa_number' => ['required','max:155',Rule::unique('order_acceptance')->where(function ($query) use ($request, $year_data) { $query->where('year_id', '=', $year_data->id); })->ignore($request->id, 'oa_id')],
            ], [
                'oa_sequence.unique' => 'Order Acceptance No. Already Exists',
                'oa_sequence.required' => 'Please Enter Order Acceptance No.',
            ]);


            // $imgs = OrderAcceptance::where('oa_id', $request->id)->value('oa_file_upload');

            // $file = new File();
            // $uploadDoc = '';
            // $blobImage = '';

            // if($request->oa_file_upload != '' && $request->oa_file_upload != null){
            //     if($imgs && !empty($imgs)){
            //         if($imgs != $request->oa_file_upload){
            //                 $file->delete_file($imgs);
            //         }                 
            //     }      
                
            //     $isFound = $file->getFileFromTemp($request->oa_file_upload_doc,'revision');

            //     if($isFound !== false){                    
            //         $uploadDoc = $isFound;
            //         $filePath = storage_path('app/public/' . $uploadDoc);
            //         $blobImage = file_exists($filePath) ? file_get_contents($filePath) : null;
            //     }else{
            //         $uploadDoc = $request->revision_doc_doc;
            //         $filePath = storage_path('app/public/' . $uploadDoc);
            //         $blobImage = $blobImage = file_get_contents($filePath);;
            //     }
            // }else{
            //     if($imgs && !empty($imgs)){
            //         $file->delete_file($imgs);
            //     }  
            // }
            
            $imgs = OrderAcceptance::where('oa_id', $request->id)->value('oa_file_upload');

            $file = new File();
            $images = '';
            $blobImage = '';

            if($request->oa_file_upload_doc != '' && $request->oa_file_upload_doc != null){
                if($imgs && !empty($imgs)){
                    if($imgs != $request->oa_file_upload_doc){
                            $file->delete_file($imgs);
                    }                 
                }      
                
                $isFound = $file->getFileFromTemp($request->oa_file_upload_doc,'revision');

                if($isFound !== false){                    
                    $images = $isFound;
                    $filePath = storage_path('app/public/' . $images);
                    $blobImage = file_exists($filePath) ? file_get_contents($filePath) : null;
                }else{
                    $images = $request->oa_file_upload_doc;
                    $filePath = storage_path('app/public/' . $images);
                    $blobImage = $blobImage = file_get_contents($filePath);;
                }
            }else{
                if($imgs && !empty($imgs)){
                    $file->delete_file($imgs);
                }  
            }

            $oa_data = OrderAcceptance::where('oa_id', $request->id)->update([

                'oa_sequence'         => $request->oa_sequence ?? null,
                'oa_number'           => $request->oa_number ?? null,
                'oa_date'             => isset($request->oa_date) ? Date::createFromFormat('d/m/Y', $request->oa_date)->format('Y-m-d') : null,
                'oa_type_id'          => $request->oa_type_id ?? null,
                'oa_customer_id'      => $request->oa_customer_id ?? null,
                'oa_kind_attn_id'     => $request->oa_kind_attn_id ?? null,
                'oa_po_number'        => $request->oa_po_number ?? null,
                'oa_po_date'          => isset($request->oa_po_date) ? Date::createFromFormat('d/m/Y', $request->oa_po_date)->format('Y-m-d') : null,
                'oa_special_note'     => $request->oa_special_note ?? null,
                'oa_copy_from_id'     => $request->oa_copy_from_id ?? null,
                'oa_terms_and_conditions'     => $request->oa_terms_and_conditions ?? null,
                'oa_file_upload'              => $images !="" ? $images : null,
                'oa_image'              => $blobImage !="" ? $blobImage : null,
                // 'oa_file_upload'      => $uploadDoc,
                // 'oa_image'            => $blobImage,
                'last_on'             => Carbon::now('Asia/Kolkata'),
                'last_by'             => Auth::id(),
            ]);

            if(!$oa_data)
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }

            $update_details =  OrderAcceptanceDetails::where('oad_oa_id',$request->id)->update(['oad_status' => 'D',]);
            $oa_details_data = json_decode($request->oa_details_data, true);
            foreach($oa_details_data as $ctVal)
            {
                if($ctVal['oad_id'] == 0)
                {
                    OrderAcceptanceDetails::create([

                        'oad_oa_id' => $request->id,

                        'oad_quotd_id' => !empty($ctVal['oad_quotd_id']) ? $ctVal['oad_quotd_id'] : null,

                        'oad_test_method_id' => !empty($ctVal['oad_test_method_id']) ? $ctVal['oad_test_method_id'] : null,

                        'oad_type_of_job_id' => !empty($ctVal['oad_type_of_job_id']) ? $ctVal['oad_type_of_job_id'] : null,

                        'oad_job_desc_id' => !empty($ctVal['oad_job_desc_id']) ? $ctVal['oad_job_desc_id'] : null,

                        'oad_part_id' => !empty($ctVal['oad_part_id']) ? $ctVal['oad_part_id'] : null,

                        'oad_process_at_id' => !empty($ctVal['oad_process_at_id']) ? $ctVal['oad_process_at_id'] : null,

                        'oad_description' => !empty($ctVal['oad_description']) ? $ctVal['oad_description'] : null,

                        'oad_qty' => !empty($ctVal['oad_qty']) ? $ctVal['oad_qty'] : null,

                        'oad_unit_id' => !empty($ctVal['oad_unit_id']) ? $ctVal['oad_unit_id'] : null,

                        'oad_rate_unit' => !empty($ctVal['oad_rate_unit']) ? $ctVal['oad_rate_unit'] : null,
                        'oad_rate_unit_id' => !empty($ctVal['oad_rate_unit_id']) ? $ctVal['oad_rate_unit_id'] : null,

                        'oad_minimum_charge' => !empty($ctVal['oad_minimum_charge']) ? $ctVal['oad_minimum_charge'] : null,

                        'oad_minimum_charge_id' => !empty($ctVal['oad_minimum_charge_unit_id']) ? $ctVal['oad_minimum_charge_unit_id'] : null,
                       
                        'oad_conveyance_charge' => !empty($ctVal['oad_conveyance_charge']) ? $ctVal['oad_conveyance_charge'] : null,

                        'oad_remark' => !empty($ctVal['oad_remark']) ? $ctVal['oad_remark'] : null,
                        'oad_status' => 'Y',
                    ]);
                }
                else
                {
                    OrderAcceptanceDetails::where('oad_id', $ctVal['oad_id'])->update([

                        'oad_oa_id' => $request->id,

                        'oad_test_method_id' => !empty($ctVal['oad_test_method_id']) ? $ctVal['oad_test_method_id'] : null,

                        'oad_type_of_job_id' => !empty($ctVal['oad_type_of_job_id']) ? $ctVal['oad_type_of_job_id'] : null,

                        'oad_job_desc_id' => !empty($ctVal['oad_job_desc_id']) ? $ctVal['oad_job_desc_id'] : null,

                        'oad_part_id' => !empty($ctVal['oad_part_id']) ? $ctVal['oad_part_id'] : null,

                        'oad_process_at_id' => !empty($ctVal['oad_process_at_id']) ? $ctVal['oad_process_at_id'] : null,

                        'oad_description' => !empty($ctVal['oad_description']) ? $ctVal['oad_description'] : null,

                        'oad_qty' => !empty($ctVal['oad_qty']) ? $ctVal['oad_qty'] : null,

                        'oad_unit_id' => !empty($ctVal['oad_unit_id']) ? $ctVal['oad_unit_id'] : null,

                        'oad_rate_unit' => !empty($ctVal['oad_rate_unit']) ? $ctVal['oad_rate_unit'] : null,
                        'oad_rate_unit_id' => !empty($ctVal['oad_rate_unit_id']) ? $ctVal['oad_rate_unit_id'] : null,

                        'oad_minimum_charge' => !empty($ctVal['oad_minimum_charge']) ? $ctVal['oad_minimum_charge'] : null,

                        'oad_minimum_charge_unit_id' => !empty($ctVal['oad_minimum_charge_unit_id']) ? $ctVal['oad_minimum_charge_unit_id'] : null,
                       
                        'oad_conveyance_charge' => !empty($ctVal['oad_conveyance_charge']) ? $ctVal['oad_conveyance_charge'] : null,

                        'oad_remark' => !empty($ctVal['oad_remark']) ? $ctVal['oad_remark'] : null,
                        'oad_status' => 'Y',
                    ]);
                }
            }

            OrderAcceptanceDetails::where('oad_oa_id',$request->id)->where('oad_status','D')->delete();

            if($oa_data)
            {
                DB::commit();

                $oa_customer = OrderAcceptance::select('customers.customer')
                ->leftJoin('customers', 'customers.id', '=', 'order_acceptance.oa_customer_id')
                ->where('order_acceptance.oa_customer_id', $request->oa_customer_id)
                ->first();

                $oa_number = !empty($request->oa_number) ? '_'.str_replace('/', '_', $request->oa_number) : "";

                $customer_for_wolh = !empty($oa_customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $oa_customer->customer) : "";

                $pdf_name = 'Order_Acceptance'.$oa_number.$customer_for_wolh;

                GeneratePdf($request->id,$pdf_name,'order_acceptance','edit');
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
        try
        {
            $oad_ids = OrderAcceptanceDetails::select('oad_id')->where('oad_oa_id', $request->id)->pluck('oad_id');
            if (PlanningManagementDetails::whereIn('pmd_oad_id', $oad_ids)->exists()) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Order Acceptance Is Used In Planning Management",
                ]);
            }
            $delete_image = OrderAcceptance::where('oa_id', $request->id)->value('oa_file_upload');

            if($delete_image && !empty($delete_image)){
                $file = new File();
                $file->delete_file($delete_image);
            }
            OrderAcceptance::where('oa_id',$request->id)->delete();
            OrderAcceptanceDetails::where('oad_oa_id',$request->id)->delete();
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

    public function getPendingCustomerForOA(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();

        if(isset($request->id)){
            $get_oa_customer =  Customer::select('id','customer')->orderBy('customer','asc')->get();

            return response()->json([
                'response_code' => 1,
                'get_oa_customer'  => $get_oa_customer,
            ]);
        }

        if($request->oa_type_id == "From Quotation"){

            // $get_oa_customer = QuotationDetails::select('customers.customer','customers.id',
            //     DB::raw("(SELECT quotation_details.quotd_qty - (SELECT IFNULL(SUM(gid.oad_qty),0) FROM order_acceptance_details AS gid WHERE gid.oad_quotd_id = quotation_details.quotd_id)) as pend_oa_qty"),  
            // )
            // ->leftJoin('quotation','quotation.quot_id','quotation_details.quotd_quot_id')
            // ->leftJoin('customers','customers.id', 'quotation.quot_customer_id')
            // ->whereIn('quotation.year_id',$yearIds)               
            // ->having('pend_oa_qty','>',0)

            // ->get();


            // ek var quotation nu OA thai jai pachi hve pending nai aave
            $get_oa_customer = QuotationDetails::select('customers.customer','customers.id')
            ->leftJoin('quotation','quotation.quot_id','quotation_details.quotd_quot_id')
            ->leftJoin('customers','customers.id','quotation.quot_customer_id')
            ->whereIn('quotation.year_id',$yearIds)
            // Exclude quotations with any OA issued
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('order_acceptance_details')
                    ->whereColumn('order_acceptance_details.oad_quotd_id','quotation_details.quotd_id');
            })
            ->distinct()
            ->get();
            
            $get_oa_customer = $get_oa_customer->unique(['id']);

            $get_oa_customer = $get_oa_customer->values()->all();

        }else{

            $get_oa_customer =  Customer::select('id','customer')->orderBy('customer','asc')->get();
        }

        return response()->json([
            'response_code' => 1,
            'get_oa_customer'  => $get_oa_customer,
        ]);
    }

    public function getPendingQuotListForOA(Request $request)
    {
        $yearIds     = getCompanyYearIdsToTill();
        $customerId  = $request->oa_customer_id;
        $quotationId = $request->id;

        $editData = collect();

       if (!empty($quotationId)) {
            $editData = OrderAcceptanceDetails::select(
                    'order_acceptance_details.oad_quotd_id',
                    'quotation.quot_id',
                    'quotation.quot_number as oad_quot_number',
                    'quotation.quot_date as oad_quot_date',
                    'customers.customer_code',
                    'customers.customer',
                    'quotation.quot_ref_no_date as oad_ref_no_date',

                    'order_acceptance_details.oad_test_method_id',
                    'order_acceptance_details.oad_type_of_job_id',
                    'type_of_job.type_of_job',

                    'order_acceptance_details.oad_job_desc_id',
                    'job_descriptions.job_description',

                    'order_acceptance_details.oad_part_id',
                    \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),

                    'order_acceptance_details.oad_process_at_id',
                    'order_acceptance_details.oad_description',

                    'order_acceptance_details.oad_qty',
                    'order_acceptance_details.oad_unit_id',
                    'order_acceptance_details.oad_minimum_charge_unit_id',

                    'order_acceptance_details.oad_remark',
                    'order_acceptance_details.oad_id'
                )
                ->leftJoin('quotation_details', 'quotation_details.quotd_id', '=', 'order_acceptance_details.oad_quotd_id')
                ->leftJoin('quotation', 'quotation.quot_id', '=', 'quotation_details.quotd_quot_id')
                ->leftJoin('customers', 'customers.id', '=', 'quotation.quot_customer_id')
                ->leftJoin('type_of_job', 'type_of_job.id', '=', 'quotation_details.quotd_type_of_job_id')
                ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'quotation_details.quotd_job_desc_id')
                ->leftJoin('part', 'part.part_id', '=', 'quotation_details.quotd_part_id')
                ->leftJoin('unit', 'unit.id', '=', 'order_acceptance_details.oad_unit_id')

                ->where('order_acceptance_details.oad_oa_id', $quotationId)
                ->where('quotation.quot_customer_id', $customerId)
                ->whereIn('quotation.year_id', $yearIds)
                ->get();
        }


       
        $pendingData = Quotation::select(
                'quotation.quot_id',
                'quotation_details.quotd_id as oad_quotd_id',
                'quotation.quot_number as oad_quot_number',
                'quotation.quot_date as oad_quot_date',
                'customers.customer_code',
                'customers.customer',
                'quotation.quot_ref_no_date as oad_ref_no_date',
                'quotation_details.quotd_test_method_id as oad_test_method_id',
                'quotation_details.quotd_type_of_job_id as oad_type_of_job_id',
                'type_of_job.type_of_job',
                'quotation_details.quotd_job_desc_id as oad_job_desc_id',
                'job_descriptions.job_description',
                'quotation_details.quotd_part_id as oad_part_id',
                \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
                'quotation_details.quotd_process_at_id as oad_process_at_id',
                'quotation_details.quotd_qty as oad_qty',
                'quotation_details.quotd_unit_id as oad_unit_id',
                'unit.unit',
                'quotation_details.quotd_remark as oad_remark',


                 DB::raw("(SELECT quotation_details.quotd_qty - (SELECT IFNULL(SUM(gid.oad_qty),0) FROM order_acceptance_details AS gid WHERE gid.oad_quotd_id = quotation_details.quotd_id)) as pend_oa_qty"),  

                DB::raw('NULL as oad_id')
            )
            ->join('quotation_details', 'quotation_details.quotd_quot_id', '=', 'quotation.quot_id')
            ->join('customers', 'customers.id', '=', 'quotation.quot_customer_id')
            ->leftJoin('type_of_job', 'type_of_job.id', '=', 'quotation_details.quotd_type_of_job_id')
            ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'quotation_details.quotd_job_desc_id')
            ->leftJoin('part', 'part.part_id', '=', 'quotation_details.quotd_part_id')
            ->leftJoin('unit', 'unit.id', '=', 'quotation_details.quotd_unit_id')
            ->where('quotation.quot_customer_id', $customerId)
            ->having('pend_oa_qty','>',0)
            ->whereIn('quotation.year_id', $yearIds)
            ->get();

      
        // $data = $editData
        //     ->concat($pendingData)
        //     ->values();

        if(isset($editData)){
            $data = collect($pendingData)->merge($editData);
            $grouped = $data->groupBy('oad_quotd_id');    
            

            $merged = $grouped->map(function ($items) {
                return $items->reduce(function ($carry, $item) {
                    if (!$carry) {
                        return $item;
                    }
                    return $carry;
                });
            });
            $pendingData = $merged->values();   

        }

        $pendingData = $pendingData->sortBy('oad_quotd_id')->values();


       
        foreach ($pendingData as $row) {
            if (!empty($row->oad_quot_date)) {
                $row->oad_quot_date = \Carbon\Carbon::parse($row->oad_quot_date)
                    ->format('d/m/Y');
            }
        }

        return response()->json([
            'response_code' => 1,
            'quot_data'      => $pendingData
        ]);
    }

    /*public function getPendingQuotationForOA(Request $request)
    {
        $quotdIds     = explode(',', $request->quotd_ids);
        $quotationId = $request->id;
        
        $edit_data = collect();

        if (!empty($quotationId)) {

            $edit_data = OrderAcceptanceDetails::select([
                'order_acceptance_details.oad_quotd_id',

                'quotation.quot_number as oad_quot_number',
                'quotation.quot_date as oad_quot_date',
                'quotation.quot_ref_no_date as oad_ref_no_date',

                'customers.customer',
                'customers.customer_code',

                'order_acceptance_details.oad_test_method_id as oad_test_method_id',
                'order_acceptance_details.oad_type_of_job_id as oad_type_of_job_id',
                'type_of_job.type_of_job',

                'order_acceptance_details.oad_job_desc_id as oad_job_desc_id',
                'job_descriptions.job_description',

                'order_acceptance_details.oad_part_id as oad_part_id',
                \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),

                'order_acceptance_details.oad_process_at_id as oad_process_at_id',

                'order_acceptance_details.oad_qty as oad_qty',
                'order_acceptance_details.oad_unit_id',

                'order_acceptance_details.oad_rate_unit',
                'order_acceptance_details.oad_rate_unit_id',

                'order_acceptance_details.oad_minimum_charge',
                'order_acceptance_details.oad_minimum_charge_unit_id',

                'order_acceptance_details.oad_conveyance_charge',
                'order_acceptance_details.oad_remark',

                'order_acceptance_details.oad_id'
            ])
            ->leftJoin('quotation_details', 'quotation_details.quotd_id', '=', 'order_acceptance_details.oad_quotd_id')
            ->leftJoin('quotation', 'quotation.quot_id', '=', 'quotation_details.quotd_quot_id')
            ->leftJoin('customers', 'customers.id', '=', 'quotation.quot_customer_id')
            ->leftJoin('type_of_job', 'type_of_job.id', '=', 'order_acceptance_details.oad_type_of_job_id')
            ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'order_acceptance_details.oad_job_desc_id')
            ->leftJoin('part', 'part.part_id', '=', 'order_acceptance_details.oad_part_id')
            ->where('order_acceptance_details.oad_oa_id', $quotationId)
            ->whereIn('order_acceptance_details.oad_quotd_id', $quotdIds)
            ->get();
        }

        
        $pending_data = Quotation::select([
                'quotation_details.quotd_id as oad_quotd_id',
                'quotation.quot_number as oad_quot_number',
                'quotation.quot_date as oad_quot_date',
                'customers.customer',
                'customers.customer_code',
                'quotation.quot_ref_no_date as oad_ref_no_date',
                'quotation_details.quotd_test_method_id as oad_test_method_id',
                'quotation_details.quotd_type_of_job_id as oad_type_of_job_id',
                'type_of_job.type_of_job',
                'quotation_details.quotd_job_desc_id as oad_job_desc_id',
                'job_descriptions.job_description',
                'quotation_details.quotd_part_id as oad_part_id',
                \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
                'quotation_details.quotd_process_at_id as oad_process_at_id',
                'quotation_details.quotd_description as oad_description',

                'quotation_details.quotd_qty as oad_qty',
                'quotation_details.quotd_unit_id as oad_unit_id',
                'quotation_details.quotd_remark as oad_remark',

                'quotation_details.quotd_rate_unit as oad_rate_unit',
                'quotation_details.quotd_rate_unit_id as oad_rate_unit_id',
                'quotation_details.quotd_minimum_charge as oad_minimum_charge',
                'quotation_details.quotd_minimum_charge_id as oad_minimum_charge_unit_id',
                'quotation_details.quotd_conveyance_charge as oad_conveyance_charge',
                DB::raw('0 as oad_id'),
            ])
            ->join('quotation_details', 'quotation_details.quotd_quot_id', '=', 'quotation.quot_id')
            ->join('customers', 'customers.id', '=', 'quotation.quot_customer_id')
            ->leftJoin('type_of_job', 'type_of_job.id', '=', 'quotation_details.quotd_type_of_job_id')
            ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'quotation_details.quotd_job_desc_id')
            ->leftJoin('part', 'part.part_id', '=', 'quotation_details.quotd_part_id')
            ->whereIn('quotation_details.quotd_id', $quotdIds)
            ->get();

       
            // $data = collect($pending_data)->merge($edit_data);

            // $grouped = $data->groupBy('oad_quotd_id');

            // $final_data = $grouped->map(function ($items) {
            //     return $items->reduce(function ($carry, $item) {
            //         if (!$carry) {
            //             return $item;
            //         }
            //         $carry->oad_qty += (float) ($item->oad_qty ?? $item->quotd_qty ?? 0);

            //         return $carry;
            //     });
            // })->values();
        // dd($edit_data,$pending_data);    
        if(isset($edit_data)){
                $data = collect($pending_data)->merge($edit_data);
                $grouped = $data->groupBy('oad_quotd_id');    
                

                $merged = $grouped->map(function ($items) {
                    return $items->reduce(function ($carry, $item) {
                        if (!$carry) {
                            return $item;
                        }
                        // $carry->pend_so_qty += (float) $item->pend_so_qty;
                        $carry->oad_qty += (float) $item->oad_qty;
                        return $carry;
                    });
                });

                $pending_data = $merged->values();   

        }

       
        foreach ($pending_data as $row) {
            if (!empty($row->oad_quot_date)) {
                $row->oad_quot_date = \Carbon\Carbon::createFromFormat('Y-m-d', $row->oad_quot_date)
                    ->format('d/m/Y');
            }
        }
        
        return response()->json([
            'response_code' => 1,
            'quot_data'      => $pending_data
        ]);
    }*/

    public function getPendingQuotationForOA(Request $request)
    {
        $quotdIds = array_filter(explode(',', $request->quotd_ids));
        $quotationId = $request->id;
        $quotationId = $quotationId ?: 0;

        $edit_data = collect();

        // ========================
        // EDIT DATA (Existing OA)
        // ========================
        if (!empty($quotationId) && !empty($quotdIds)) {

            $edit_data = OrderAcceptanceDetails::select([
                'order_acceptance_details.oad_quotd_id',

                'quotation.quot_number as oad_quot_number',
                'quotation.quot_date as oad_quot_date',
                'quotation.quot_ref_no_date as oad_ref_no_date',

                'customers.customer',
                'customers.customer_code',

                'order_acceptance_details.oad_test_method_id',
                'order_acceptance_details.oad_type_of_job_id',
                'type_of_job.type_of_job',

                'order_acceptance_details.oad_job_desc_id',
                'job_descriptions.job_description',

                'order_acceptance_details.oad_part_id',
                \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),

                'order_acceptance_details.oad_process_at_id',
                'order_acceptance_details.oad_qty',
                'order_acceptance_details.oad_unit_id',

                'order_acceptance_details.oad_rate_unit',
                'order_acceptance_details.oad_rate_unit_id',

                'order_acceptance_details.oad_minimum_charge',
                'order_acceptance_details.oad_minimum_charge_unit_id',

                'order_acceptance_details.oad_conveyance_charge',
                'order_acceptance_details.oad_remark',

                'order_acceptance_details.oad_id',

                // DB::raw("(SELECT quotation_details.quotd_qty - (SELECT IFNULL(SUM(gid.oad_qty),0) FROM order_acceptance_details AS gid WHERE gid.oad_quotd_id = quotation_details.quotd_id)) as pend_oa_qty"), 
                
                DB::raw("(
                    SELECT quotation_details.quotd_qty -
                    (
                        SELECT IFNULL(SUM(gid.oad_qty),0)
                        FROM order_acceptance_details AS gid
                        WHERE gid.oad_quotd_id = quotation_details.quotd_id
                        AND gid.oad_oa_id != {$quotationId}
                    )
                ) as pend_oa_qty"),


            ])
            ->leftJoin('quotation_details', 'quotation_details.quotd_id', '=', 'order_acceptance_details.oad_quotd_id')
            ->leftJoin('quotation', 'quotation.quot_id', '=', 'quotation_details.quotd_quot_id')
            ->leftJoin('customers', 'customers.id', '=', 'quotation.quot_customer_id')
            ->leftJoin('type_of_job', 'type_of_job.id', '=', 'order_acceptance_details.oad_type_of_job_id')
            ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'order_acceptance_details.oad_job_desc_id')
            ->leftJoin('part', 'part.part_id', '=', 'order_acceptance_details.oad_part_id')
            ->where('order_acceptance_details.oad_oa_id', $quotationId)
            ->whereIn('order_acceptance_details.oad_quotd_id', $quotdIds)
            ->get();
        }

        // ========================
        // PENDING DATA (Quotation)
        // ========================
        $pending_data = Quotation::select([
            'quotation_details.quotd_id as oad_quotd_id',
            'quotation.quot_number as oad_quot_number',
            'quotation.quot_date as oad_quot_date',
            'quotation.quot_ref_no_date as oad_ref_no_date',

            'customers.customer',
            'customers.customer_code',

            'quotation_details.quotd_test_method_id as oad_test_method_id',
            'quotation_details.quotd_type_of_job_id as oad_type_of_job_id',
            'type_of_job.type_of_job',

            'quotation_details.quotd_job_desc_id as oad_job_desc_id',
            'job_descriptions.job_description',

            'quotation_details.quotd_part_id as oad_part_id',
            \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),

            'quotation_details.quotd_process_at_id as oad_process_at_id',
            'quotation_details.quotd_description as oad_description',

            'quotation_details.quotd_qty as oad_qty',
            'quotation_details.quotd_unit_id as oad_unit_id',

            'quotation_details.quotd_rate_unit as oad_rate_unit',
            'quotation_details.quotd_rate_unit_id as oad_rate_unit_id',

            'quotation_details.quotd_minimum_charge as oad_minimum_charge',
            'quotation_details.quotd_minimum_charge_id as oad_minimum_charge_unit_id',

            'quotation_details.quotd_conveyance_charge as oad_conveyance_charge',
            'quotation_details.quotd_remark as oad_remark',

            //  DB::raw("(SELECT quotation_details.quotd_qty - (SELECT IFNULL(SUM(gid.oad_qty),0) FROM order_acceptance_details AS gid WHERE gid.oad_quotd_id = quotation_details.quotd_id)) as pend_oa_qty"),     

            DB::raw("(
                SELECT quotation_details.quotd_qty -
                (
                    SELECT IFNULL(SUM(gid.oad_qty),0)
                    FROM order_acceptance_details AS gid
                    WHERE gid.oad_quotd_id = quotation_details.quotd_id
                    AND gid.oad_oa_id != {$quotationId}
                )
            ) as pend_oa_qty"),




            DB::raw('0 as oad_id'),
        ])
        ->leftJoin('quotation_details', 'quotation_details.quotd_quot_id', '=', 'quotation.quot_id')
        ->leftJoin('customers', 'customers.id', '=', 'quotation.quot_customer_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'quotation_details.quotd_type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'quotation_details.quotd_job_desc_id')
        ->leftJoin('part', 'part.part_id', '=', 'quotation_details.quotd_part_id')
        ->whereIn('quotation_details.quotd_id', $quotdIds)
        ->get();

        // ========================
        // MERGE & GROUP DATA
        // ========================
        // ========================
        if (!empty($quotationId)) {

            // EDIT MODE ONLY
            $data = $pending_data->merge($edit_data);

            $pending_data = $data->groupBy('oad_quotd_id')->map(function ($items) {
                return $items->reduce(function ($carry, $item) {
                    if (!$carry) {
                        return $item;
                    }

                    $carry->oad_qty = (float) ($item->oad_qty ?? 0);
                    return $carry;
                });
            })->values();

        }


        // ========================
        // DATE FORMAT
        // ========================
        foreach ($pending_data as $row) {
            if (!empty($row->oad_quot_date)) {
                $row->oad_quot_date = \Carbon\Carbon::parse($row->oad_quot_date)->format('d/m/Y');
            }
        }

        return response()->json([
            'response_code' => 1,
            'quot_data' => $pending_data
        ]);
    }


    public function getLatestOANumber(Request $request)
    {
        $modal  =  OrderAcceptance::class;
        $sequence = 'oa_sequence'; 
        $prefix = 'OA';           
        $sup_num_format = getLatestSequence($modal,$sequence,$prefix);   

        return response()->json([
          'response_code' => 1,
          'latest_no'     => $sup_num_format['format'],
          'number'        => $sup_num_format['isFound'],
      ]);
    }

    public function getAllOANo(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();

        $oa_terms = OrderAcceptance::select(
                'order_acceptance.oa_id',
                'order_acceptance.oa_number',
            )
            ->whereIn('order_acceptance.year_id', $yearIds)
            ->orderBy('order_acceptance.oa_number', 'asc')
            ->whereNotIn('order_acceptance.oa_id', function($query) use ($request){
                $query->select('oa_id')
                    ->from('order_acceptance')
                    ->where('oa_id', $request->id);
            })
            ->get();

        return response()->json([
            'response_code' => 1,
            'oa_terms' => $oa_terms,
        ]);
    }

    public function getOATermsConditions(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();

        $terms_conditions = OrderAcceptance::select(
                'order_acceptance.oa_id',
                'order_acceptance.oa_terms_and_conditions'
            )
            ->where('order_acceptance.oa_id',$request->oa_id)
            ->whereIn('order_acceptance.year_id', $yearIds)
            ->orderBy('order_acceptance.oa_terms_and_conditions', 'asc')
            ->first();

        return response()->json([
            'response_code' => 1,
            'terms_conditions' => $terms_conditions,
        ]);
    }

}
