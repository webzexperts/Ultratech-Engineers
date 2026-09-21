<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\SupplierDetails;
use App\Models\Transaction\ServicePO;
use App\Models\Transaction\ServicePODetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Calculation\Web\Service;

class ServicePOController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-service_po');
    }

    public function index(ServicePO $ser_po_data, Request $request, DataTables $datatables)
    {
        $current_location_id = getCurrentLocation()->location_id;
        $year_data = getCurrentYearData();
        $ser_po_data = ServicePO::select([

            'service_po.ser_po_sequence',
            'service_po.ser_po_id',
            'service_po.ser_po_number',
            'service_po.ser_po_date',
            'suppliers.supplier_name',
            'service_po.purpose',
            'service_po.ref_no_date',
            'bill_to.location_name as bill_to',
            'for_location.location_name as for_location',
            'item.item_name',
            'item_group.item_group',
            'item.item_type as main_group',
            'item.item_type as main_group_key',
            'sr.name_for_display',
            'service_po_details.po_qty',
            'unit.unit',
            'service_po_details.rate_unit',
            'service_po_details.amount',
            'service_po_details.del_date',
            'service_po_details.remark',
            'prepared_by.person_name as prepared_by',
            'service_po.created_on',
            'service_po.created_by',
            'service_po.last_by',
            'service_po.last_on',
        ])

        ->leftJoin('service_po_details','service_po_details.ser_pod_po_id','=','service_po.ser_po_id')
        ->leftJoin('suppliers','suppliers.id','=','service_po.supplier_id')
        ->leftJoin('location as bill_to','bill_to.location_id','=','service_po.bill_to_id')
        ->leftJoin('location as for_location','for_location.location_id','=','service_po.for_location_id')
        ->leftJoin('admin as prepared_by','prepared_by.id','=','service_po.prepared_by_user_id')
        ->leftJoin('item','item.id','=','service_po_details.item_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'service_po_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'service_po_details.sr_table_unique_id');
        })
        ->where('service_po.year_id', $year_data->id)
        ->where("service_po.current_location_id", $current_location_id);


        $dataTable = DataTables::of($ser_po_data)
        ->editColumn('ser_po_date', function($ser_po_data){
            if ($ser_po_data->ser_po_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $ser_po_data->ser_po_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('service_po.ser_po_date', function ($q, $k) {
            applyDate($q, $k, 'service_po.ser_po_date');
        })
        ->editColumn('del_date', function($po_data){
            if ($po_data->del_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $po_data->del_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })

        ->editColumn('main_group', function($ser_po_data) {
            return config('app.item_type')[$ser_po_data->main_group] ?? $ser_po_data->main_group;
        })
        ->filterColumn('item.item_type', function($query, $keyword) {

            $itemTypes = config('app.item_type');
            $matchedKeys = [];
            foreach ($itemTypes as $key => $value) {
                if (stripos($value, $keyword) !== false) {
                    $matchedKeys[] = $key;
                }
            }
            if (!empty($matchedKeys)) {
                $query->whereIn('item.item_type', $matchedKeys);
            } else {
                $query->where('item.item_type', 'like', "%{$keyword}%");
            }
        })

        

        ->filterColumn('service_po_details.del_date', function ($q, $k) {
            applyDate($q, $k, 'service_po_details.del_date');
        })

         ->editColumn('po_qty', function($po_data) {
            return $po_data->po_qty > 0 ? number_format((float)$po_data->po_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('service_po_details.po_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('po_qty')
                    ->orWhere('po_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(po_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('po_qty', 'like', "%{$search}%");
            }
        })
        ->editColumn('rate_unit', function($po_data) {
            return $po_data->rate_unit > 0 ? number_format((float)$po_data->rate_unit, 2, '.','') : number_format((float) 0, 2, '.','');
        })
        ->filterColumn('service_po_details.rate_unit', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,2})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('rate_unit')
                    ->orWhere('rate_unit', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(rate_unit AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('rate_unit', 'like', "%{$search}%");
            }
        })
        
        ->editColumn('amount', function($po_data) {
            return $po_data->amount > 0 ? number_format((float)$po_data->amount, 2, '.','') : number_format((float) 0, 2, '.','');
        })
        ->filterColumn('service_po_details.amount', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,2})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('amount')
                    ->orWhere('amount', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(amount AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('amount', 'like', "%{$search}%");
            }
        })
        ->filterColumn('service_po.ser_po_number', function($query, $keyword) {
            applySequenceSearch($query, $keyword, 'service_po.ser_po_number');
        })
        
        ->addColumn('options',function($po_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if(hasAccess("service_po", "print")) {
                    $ser_po_number = !empty($po_data->ser_po_number) ? '_'.str_replace('/', '_', $po_data->ser_po_number) : "";

                    $supplier_name = !empty($po_data->supplier_name) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $po_data->supplier_name) : "";

                    $pdfName  = 'Service_PO'.$ser_po_number.$supplier_name;
                    $encodedId = base64_encode($po_data->ser_po_id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=service_po");
               
                    $action .= '<li><a  class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }
                $action .= '<li><a class="dropdown-item email-service-po-btn" href="javascript:void(0);"><i class="ri-mail-send-fill align-bottom me-2 text-muted" id="email_a"></i> Email</a></li>';

                if (hasAccess("service_po", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-service_po"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }


                if (hasAccess("service_po", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'service_po');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on','options'])
        ->make(true);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $year_data = getCurrentYearData();
        $LocationData = getCurrentLocation();

        DB::beginTransaction();


        try {
            $existNumber = ServicePO::where([
                ['ser_po_sequence', $request->ser_po_sequence],
                ['ser_po_number', $request->ser_po_number],
                ['year_id', $year_data->id]
            ])->first();

            if ($existNumber) {
                $latestNo = $this->getLatestServicePONumber($request);
                $tmp = $latestNo->getContent();
                $area = json_decode($tmp, true);

                $ser_po_number   = $area['latest_no'];
                $ser_po_sequence = $area['number'];
            } else {
                $ser_po_number   = $request->ser_po_number;
                $ser_po_sequence = $request->ser_po_sequence;
            }


            $page_id = getMenuIdBassedOnDisplayName('service_po');
            $assign_format_no = getAssignFormateNoForTransaction($LocationData->location_id, $page_id->id,$request->ser_po_date);
            $amount_rupee = $request->net_amount;
            $amount_in_word = digitsToWords($amount_rupee);


            $ser_po_data = ServicePO::create([
                'ser_po_number'    => $ser_po_number,
                'ser_po_sequence'  => $ser_po_sequence,
                'ser_po_date'      => $request->ser_po_date ? Date::createFromFormat('d/m/Y', $request->ser_po_date)->format('Y-m-d') : null,
                'supplier_id'          => $request->supplier_id,
                'kind_attn_id'         => $request->kind_attn_id,
                'purpose'              => $request->purpose,
                'ref_no_date'          => $request->ref_no_date,
                'bill_to_id'           => $request->bill_to_id,
                'for_location_id'      => $request->for_location_id,
                'terms_and_conditions' => $request->terms_and_conditions,
                'sp_note'              => $request->sp_note,
                'payment_terms'        => $request->payment_terms,
                'basic_amount'         => $request->basic_amount,
                'gst_type_fix_id'      => $request->gst_type_fix_id,
                'sgst_percentage'      => $request->sgst_percentage,
                'sgst_amount'          => $request->sgst_amount,
                'cgst_percentage'      => $request->cgst_percentage,
                'cgst_amount'          => $request->cgst_amount,
                'igst_percentage'      => $request->igst_percentage,
                'igst_amount'          => $request->igst_amount,
                'round_off_val'        => $request->round_off_val,
                'net_amount'           => $request->net_amount,
                'amount_in_word'       => $amount_in_word ?? null,
                'prepared_by_user_id'  => $request->prepared_by_user_id,
                'assign_format_no'     => $assign_format_no,
                'current_location_id'  => $LocationData->location_id ?? null,
                'year_id'              => $year_data->id,
                'company_id'           => Auth::user()->company_id,
                'created_by'           => Auth::user()->id,
                'created_on'           => Carbon::now('Asia/Kolkata'),
            ]);
            $details = json_decode($request->service_po_details_data, true);
            // dd($details);

            if (!empty($details)) {
                foreach ($details as $row) {

                    if (!empty($row['item_id'])) {

                        $mode = $row['mode'];
                        if($mode == 'Delete') {
                            continue;
                        }

                        if($mode == 'Insert')
                        {
                          $spDetails =   ServicePODetails::create([
                                'ser_pod_po_id'  => $ser_po_data->ser_po_id,
                                'item_id'        => $row['item_id'],
                                'sr_table_unique_id'  => $row['sr_table_unique_id'] ?? NULL,
                                'sr_table_pk_id'      => $row['sr_table_pk_id'] ?? NULL,
                                'po_qty'         => $row['po_qty'] ?? NULL,
                                'rate_unit'      => $row['rate_unit'] ?? NULL,
                                'amount'         => $row['amount'] ?? NULL,
                                'del_date'       => !empty($row['del_date']) ? Date::createFromFormat('d/m/Y', $row['del_date'])->format('Y-m-d') : NULL,
                                'remark'  => $row['remark'] ?? NULL,
                                'for_calibration' => $row['for_calibration'] ?? 'No',
                                'previous_status_transaction_id' => null,
                                'current_status_transaction_id'  => null,
                            ]);

                            if(!empty($row['sr_table_pk_id'])){
                                changeStatusAndLocationForSrNo($row['sr_table_unique_id'], $row['sr_table_pk_id'], 'Service PO', 'Active', $LocationData->location_id, 'Insert', $spDetails);
                            }
                        }
                    }
                }
            }

            DB::commit();   
                 $get_supplier = ServicePO::select('suppliers.supplier_name')
                ->leftJoin('suppliers', 'suppliers.id', '=', 
                'service_po.supplier_id')
                ->where('service_po.supplier_id', $ser_po_data->supplier_id)
                ->first();

                $ser_po_number = !empty($ser_po_data->ser_po_number) ? '_'.str_replace('/', '_', $ser_po_data->ser_po_number) : "";

                $supplier_name = !empty($get_supplier) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $get_supplier->supplier_name) : "";

                $pdf_name = 'Service_PO'.$ser_po_number.$supplier_name;

                GeneratePdf($ser_po_data->ser_po_id,$pdf_name,'service_po','add');
                $encodedId = base64_encode($ser_po_data->ser_po_id);
                if(hasAccess("service_po", "print")){
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=service_po");
                }else{
                    $url ="";
                } 

            return response()->json([
                'response_code' => '1',
                'url' => $url,
                'response_message' => getResponseMessage('store_success'),
            ]);

        } catch (\Exception $e) {

            report($e);
            DB::rollBack();
            $message = $e->getMessage();

            if (in_array($message, [
                    'Insufficient Stock',
                    'Invalid Location Code',
                    'Invalid Item'
                ]) || str_contains($message, 'Sr. No. Is Already') || str_contains($message, "You Can't Delete")
            ) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            } else{
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('store_error'),
                    'original_error' => $e->getMessage()
                ]);
            }

            // return response()->json([
            //     'response_code' => '0',
            //     'response_message' => getResponseMessage('store_error'),
            //     'original_error' => $e->getMessage()
            // ]);
        }
    }

    public function edit(Request $request)
    {
         $isAnyPartInUse = false;
        // $itemTypes = getItemType();   
        $ser_po_data =  DB::select('CALL service_po_master(?)', [$request->id]);
        if(!empty($ser_po_data))
        {
            $ser_po_data = $ser_po_data[0];
            $ser_po_data->ser_po_date = $ser_po_data->ser_po_date != "" ? Date::createFromFormat('Y-m-d',  $ser_po_data->ser_po_date)->format('d/m/Y') : "";

            $po_number = !empty($ser_po_data->ser_po_number) ? '_'.str_replace('/', '_', $ser_po_data->ser_po_number) : "";

            $supplier_name = !empty($ser_po_data) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $ser_po_data->supplier_name) : "";

            $ser_po_data->pdf_name = 'Service_PO'.$po_number.$supplier_name;

            if(isset($ser_po_data->cmp_logo))
            {
                $ser_po_data->cmp_logo = base64_encode($ser_po_data->cmp_logo);
            }
            
        }
        
        $ser_po_details_data = DB::select('CALL service_po_details(?)', [$request->id]);
       

        if($ser_po_details_data){
            foreach($ser_po_details_data as $dKey => $dVal)
            {
                $dVal->del_date = $dVal->del_date != "" ? Date::createFromFormat('Y-m-d',  $dVal->del_date)->format('d/m/Y') : "";
                $dVal->for_calibration = DB::table('service_po_details')->where('ser_pod_id', $dVal->ser_pod_id)->value('for_calibration');
                
                $cali_req = 'Yes';
                if ($dVal->sr_table_unique_id === 'instrument') {
                    $cali_req = DB::table('instrument')
                        ->where('ins_id', $dVal->sr_table_pk_id)
                        ->value('ins_cali_req') ?? 'No';
                }
                $dVal->calibration_required = $cali_req;

                // $dVal->main_group = $itemTypes[$dVal->main_group] ?? $dVal->main_group;

                $dVal->mode = 'Update';

                $dVal->name_for_display = $dVal->sr_no;
                $PendingQty = DB::table('pending_service_po_qty as pend')->where("pend.ser_pod_id",$dVal->ser_pod_id)->value("pend.pending_qty");

                $total_qty = $dVal->po_qty;                          
                $isFound = $total_qty - $PendingQty;

                if($isFound > 0){
                    $dVal->in_use = true;
                    $dVal->used_qty = $isFound;
                    $isAnyPartInUse = true;
                } else {
                    $dVal->in_use = false;
                    $dVal->used_qty = 0;
                }
               
            }
            
        }
        
        if($ser_po_data)
        {
            $ser_po_data->in_use = false;
            if($isAnyPartInUse == true){
                $ser_po_data->in_use = true;
            }
            return response()->json([
                'ser_po_data'             => $ser_po_data,
                'service_po_details_data' => $ser_po_details_data,
                'response_code'           => '1',
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
        // dd($request->all());
        $year_data = getCurrentYearData();
        $LocationData = getCurrentLocation();

        DB::beginTransaction();
        $validated = $request->validate([
                'ser_po_sequence' => [
                    'required',
                    'max:155',
                    Rule::unique('service_po')
                        ->where(function ($query) use ($year_data, $LocationData) {
                            $query->where('year_id', $year_data->id)
                                ->where('current_location_id', $LocationData->location_id);
                        })
                        ->ignore($request->id, 'ser_po_id')
                ],
            ], [
                'ser_po_sequence.unique'   => 'Duplicate Service PO No. Found.',
                'ser_po_sequence.required' => 'Enter Service PO No.',
            ]);
        try
        {
            $page_id = getMenuIdBassedOnDisplayName('service_po');
            $assign_format_no = getAssignFormateNoForTransaction($LocationData->location_id, $page_id->id,$request->ser_po_date);

            $year_data = getCurrentYearData();
            $amount_rupee = $request->net_amount;
            $amount_in_word = digitsToWords($amount_rupee);
    
            $ser_po_data = ServicePO::where('ser_po_id', $request->id)->update([
                'ser_po_number'   => $request->ser_po_number,
                'ser_po_sequence' => $request->ser_po_sequence,
                'ser_po_date'          => $request->ser_po_date ? Date::createFromFormat('d/m/Y', $request->ser_po_date)->format('Y-m-d') : null,
                'supplier_id'          => $request->supplier_id,
                'kind_attn_id'         => $request->kind_attn_id,
                'purpose'              => $request->purpose,
                'ref_no_date'          => $request->ref_no_date,
                'bill_to_id'           => $request->bill_to_id,
                'for_location_id'      => $request->for_location_id,
                'terms_and_conditions' => $request->terms_and_conditions,
                'payment_terms'        => $request->payment_terms,
                'sp_note'              => $request->sp_note,
                'basic_amount'         => $request->basic_amount,
                'gst_type_fix_id'      => $request->gst_type_fix_id,
                'sgst_percentage'      => $request->sgst_percentage,
                'sgst_amount'          => $request->sgst_amount,
                'cgst_percentage'      => $request->cgst_percentage,
                'cgst_amount'          => $request->cgst_amount,
                'igst_percentage'      => $request->igst_percentage,
                'igst_amount'          => $request->igst_amount,
                'round_off_val'        => $request->round_off_val,
                'net_amount'           => $request->net_amount,
                'amount_in_word'       => $amount_in_word ?? null,
                'prepared_by_user_id'  => $request->prepared_by_user_id,
                // 'assign_format_no'     => $assign_format_no,
                'current_location_id'  => $LocationData->location_id ?? null,
                'year_id'              => $year_data->id,
                'company_id'           => Auth::user()->company_id,
                'last_on'              => Carbon::now('Asia/Kolkata'),
                'last_by'              => Auth::id(),
            ]);

            if(!$ser_po_data)
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }

            $service_po_details_data = json_decode($request->service_po_details_data, true);

            if(!empty($service_po_details_data))
            {
                foreach($service_po_details_data as $ctVal)
                {
                    if(empty($ctVal)) continue;

                    $mode = $ctVal['mode'];
                    
                    if($mode == 'Insert')
                    {
                       $spDetails = ServicePODetails::create([
                            'ser_pod_po_id'   => $request->id,
                            'item_id'         => $ctVal['item_id'],
                            'sr_table_unique_id'  => $ctVal['sr_table_unique_id'] ?? NULL,
                            'sr_table_pk_id'      => $ctVal['sr_table_pk_id'] ?? NULL,
                            'po_qty'         => $ctVal['po_qty'] ?? NULL,
                            'rate_unit'      => $ctVal['rate_unit'] ?? NULL,
                            'amount'         => $ctVal['amount'] ?? NULL,
                            'del_date'       => !empty($ctVal['del_date']) ? Date::createFromFormat('d/m/Y', $ctVal['del_date'])->format('Y-m-d') : NULL,
                            'remark'  => $ctVal['remark'] ?? NULL,
                            'for_calibration' => $ctVal['for_calibration'] ?? 'No',
                            'previous_status_transaction_id' => null,
                            'current_status_transaction_id'  => null,
                        ]);

                        if(!empty($ctVal['sr_table_pk_id'])){
                            changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Service PO', 'Active', $LocationData->location_id, 'Insert', $spDetails);
                        }
                    }

                 
                    elseif($mode == 'Update')
                    {
                        if(!empty($ctVal['ser_pod_id']))
                        {
                            $pendingQty = (float) DB::table('pending_service_po_qty')
                            ->where('ser_pod_id', $ctVal['ser_pod_id'])
                            ->value('pending_qty');
                           
                            $old_qty = ServicePODetails::where('ser_pod_id', $ctVal['ser_pod_id'])->sum('po_qty');
                            $total_used_qty = $old_qty - $ctVal['po_qty'];
                            
                            if($pendingQty < $total_used_qty)
                            {
                                DB::rollBack();
                                return response()->json([
                                    'response_code' => '0',
                                    'response_message' => 'PO Qty. Is Used.',
                                ]);
                            }

                            ServicePODetails::where('ser_pod_id', $ctVal['ser_pod_id'])->update([

                                'item_id'         => $ctVal['item_id'],
                                'sr_table_unique_id'  => $ctVal['sr_table_unique_id'] ?? NULL,
                                'sr_table_pk_id'      => $ctVal['sr_table_pk_id'] ?? NULL,
                                'po_qty'         => $ctVal['po_qty'] ?? NULL,
                                'rate_unit'      => $ctVal['rate_unit'] ?? NULL,
                                'amount'         => $ctVal['amount'] ?? NULL,
                                'del_date'       => !empty($ctVal['del_date']) ? Date::createFromFormat('d/m/Y', $ctVal['del_date'])->format('Y-m-d') : NULL,
                                'remark'  => $ctVal['remark'] ?? NULL,
                                'for_calibration' => $ctVal['for_calibration'] ?? 'No',
                            ]);
                        }
                    }
                   
                    elseif($mode == 'Delete')
                    {
                        if(!empty($ctVal['ser_pod_id']))
                        {
                             $SPDetails = ServicePODetails::where('ser_pod_id', $ctVal['ser_pod_id'])->get();

                             if($SPDetails->isNotEmpty()){
                                foreach($SPDetails as $item){

                                    $ser_po_data =  DB::select('CALL service_po_used_list(?)', [$request->id]);
                
                                    if(!empty($ser_po_data)){ 

                                        return response()->json([
                                            'response_code' => '0',
                                            'response_message' => "You Can't Delete, Service PO Is Used In " . $ser_po_data[0]->table_name . ".",
                                        ]);
                                    }

                                    if($item->sr_table_unique_id != null || $item->sr_table_unique_id != ''){
                                    changeStatusAndLocationForSrNo($item->sr_table_unique_id,$item->sr_table_pk_id,'Service PO','Active',$LocationData->location_id,'Delete', $item);
                                    }
                                }
                            }
                            
                            ServicePODetails::where('ser_pod_id', $ctVal['ser_pod_id'])->delete();
                        }
                    }
                }

                DB::commit();

                $get_suppliers = ServicePO::select('suppliers.supplier_name')
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'service_po.supplier_id')
                    ->where('service_po.supplier_id', $request->supplier_id)
                    ->first();
                    $po_number = !empty($request->ser_po_number) ? '_'.str_replace('/', '_', $request->ser_po_number) : "";
                    
                    $supplier_name = !empty($get_suppliers) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $get_suppliers->supplier_name) : "";
                    
                    $pdf_name = 'Service_PO'.$po_number.$supplier_name;
                    GeneratePdf($request->id,$pdf_name,'service_po','edit');
                    $encodedId = base64_encode($request->id);
                
                    if(hasAccess("service_po", "print")){
                        $url = url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=service_po");
                    }else{
                        
                        $url ="";
                    }
                return response()->json([
                    'response_code' => '1',
                    'url' => $url,
                    'response_message' => getResponseMessage('update_success'),
                ]);
            }else
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

            $message = $e->getMessage();

            if (in_array($message, [
                    'Insufficient Stock',
                    'Invalid Location Code',
                    'Invalid Item'
                ]) || str_contains($message, 'Sr. No. Is Already') || str_contains($message, "You Can't Delete")
            ) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            } else{
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                    'original_error' => $e->getMessage()
                ]);
            }
            // return response()->json([
            //     'response_code' => '0',
            //     'response_message' => getResponseMessage('update_error'),
            //     'original_error' => $e->getMessage()
            // ]);
        }
    }

    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try
        {
             $sp_data =  DB::select('CALL service_po_used_list(?)', [$request->id]);
            
            if(!empty($sp_data)){ 
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Service PO Is Used In " . $sp_data[0]->table_name . ".",
                ]);
            }
             $LocationData = getCurrentLocation()->location_id;
             $SPDetails = ServicePODetails::where('ser_pod_po_id', $request->id)->get();

             if($SPDetails->isNotEmpty()){
                foreach($SPDetails as $item){

                    if($item->sr_table_unique_id != null || $item->sr_table_unique_id != ''){
                      changeStatusAndLocationForSrNo($item->sr_table_unique_id,$item->sr_table_pk_id,'Service PO','Active',$LocationData,'Delete', $item);
                    }
                }
            }

            ServicePO::where('ser_po_id',$request->id)->delete();
            ServicePODetails::where('ser_pod_po_id',$request->id)->delete();
              DB::commit();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        }
        catch(\Exception $e)
        {
            report($e);
            DB::rollBack(); 
            $message = $e->getMessage();

            if(isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451)
            {
                $error_msg = "This Is Used Somewhere, You Can't Delete";
            }else if(in_array($message, [
                    'Insufficient Stock',
                    'Invalid Location Code',
                    'Invalid Item'
                ]) || str_contains($message, 'Sr. No. Is Already') || str_contains($message, "You Can't Delete"))
            {
                $error_msg = $message;
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

    public function getLatestServicePONumber(Request $request)
    {
        $modal  =  ServicePO::class;
        $sequence = 'ser_po_sequence';           
        $prefix = 'S-PO';           
      
        $sup_num_format = getLatestSequence($modal,$sequence,$prefix);   
        return response()->json([
          'response_code' => 1,
          'latest_no'     => $sup_num_format['format'],
          'number'        => $sup_num_format['isFound'],
      ]);
    }

    public function getServicePOSupplierKindAttn(Request $request)
    {
        $supplierId = $request->supplier_id;

        $LnrData = ServicePO::select('terms_and_conditions')->where('supplier_id', $supplierId)->orderby('ser_po_id', 'desc')->first();

        $Kind_attn = SupplierDetails::select(
            'supd_details_id',
            'contact_person',
            'phone_no',
            'email_id'
        )
        ->where('sup_id', $supplierId)
        ->get();

        return response()->json([
            'response_code' => '1',
            'LnrData' => $LnrData,
            'Kind_attn' => $Kind_attn,
        ]);
    }
}