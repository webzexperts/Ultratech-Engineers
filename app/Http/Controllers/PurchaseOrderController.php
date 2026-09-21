<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetails;
use App\Models\GRNDetails;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\File;
use App\Models\POShortClose;
use App\Models\Supplier;
use App\Models\PurchaseIndentDetails;
use App\Models\SupplierDetails;

class PurchaseOrderController extends Controller
{
    public function manage()
    {
        return view('manage.manage-purchase_order');
    }

    public function index(PurchaseOrder $po_data, Request $request, DataTables $datatables)
    {
        $current_location_id = getCurrentLocation()->location_id;
        $year_data = getCurrentYearData();
        $po_data = PurchaseOrder::select([
            'purchase_order.po_type_id',
            'purchase_order.po_sequence',
            'purchase_order.po_id',
            'purchase_order.po_number',
            'purchase_order.po_date',
            'purchase_order.po_supplier_id',
            'suppliers.supplier_name',
            'purchase_order.ref_no_date',
            'purchase_order.bill_to_location_id',
            'bill_to_location.location_name as bill_to_location_name',
            'purchase_order.ship_to_location_id',
            'ship_to_location.location_name as ship_to_location_name',
            'purchase_order_details.pod_item_id',
            'item.item_name',
            'item_group.item_group',
            'item.item_type as main_group',
            'purchase_order_details.pod_po_qty',
            'unit.unit',
            'purchase_order_details.pod_rate_unit',
            'purchase_order_details.pod_amount',
            'purchase_order_details.pod_del_date',
            'purchase_order_details.pod_remark',
            'purchase_order.po_sp_note',
            'admin.person_name',
            'purchase_order.created_on',
            'purchase_order.created_by',
            'purchase_order.last_by',
            'purchase_order.last_on',
        ])

        ->leftJoin('purchase_order_details','purchase_order_details.pod_po_id','=','purchase_order.po_id')
        ->leftJoin('suppliers','suppliers.id','=','purchase_order.po_supplier_id')
        ->leftJoin('location as bill_to_location','bill_to_location.location_id','=','purchase_order.bill_to_location_id')
        ->leftJoin('location as ship_to_location','ship_to_location.location_id','=','purchase_order.ship_to_location_id')
        ->leftJoin('item','item.id','=','purchase_order_details.pod_item_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('admin','admin.id','=','purchase_order.prepared_by_user_id')
        ->where('purchase_order.year_id', $year_data->id)
        ->where("purchase_order.current_location_id", $current_location_id);


        $dataTable = DataTables::of($po_data)
        ->editColumn('po_date', function($po_data){
            if ($po_data->po_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $po_data->po_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('purchase_order.po_date', function ($q, $k) {
            applyDate($q, $k, 'purchase_order.po_date');
        })
        ->editColumn('pod_del_date', function($po_data){
            if ($po_data->pod_del_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $po_data->pod_del_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })

        ->editColumn('main_group', function($po_data) {
            return config('app.item_type')[$po_data->main_group] ?? $po_data->main_group;
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

        

        ->filterColumn('purchase_order_details.pod_del_date', function ($q, $k) {
            applyDate($q, $k, 'purchase_order_details.pod_del_date');
        })

         ->editColumn('pod_po_qty', function($po_data) {
            return $po_data->pod_po_qty > 0 ? number_format((float)$po_data->pod_po_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('purchase_order_details.pod_po_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('pod_po_qty')
                    ->orWhere('pod_po_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(pod_po_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('pod_po_qty', 'like', "%{$search}%");
            }
        })
        ->editColumn('pod_rate_unit', function($po_data) {
            return $po_data->pod_rate_unit > 0 ? number_format((float)$po_data->pod_rate_unit, 2, '.','') : number_format((float) 0, 2, '.','');
        })
        ->filterColumn('purchase_order_details.pod_rate_unit', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,2})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('pod_rate_unit')
                    ->orWhere('pod_rate_unit', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(pod_rate_unit AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('pod_rate_unit', 'like', "%{$search}%");
            }
        })
        
        ->editColumn('pod_amount', function($po_data) {
            return $po_data->pod_amount > 0 ? number_format((float)$po_data->pod_amount, 2, '.','') : number_format((float) 0, 2, '.','');
        })
        ->filterColumn('purchase_order_details.pod_amount', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,2})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('pod_amount')
                    ->orWhere('pod_amount', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(pod_amount AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('pod_amount', 'like', "%{$search}%");
            }
        })
        ->filterColumn('purchase_order.po_number', function($query, $keyword) {
            applySequenceSearch($query, $keyword, 'purchase_order.po_number');
        })
        
        ->addColumn('options',function($po_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if(hasAccess("purchase_order", "print")) {
                    $po_number = !empty($po_data->po_number) ? '_'.str_replace('/', '_', $po_data->po_number) : "";

                    $supplier_for_wolh = !empty($po_data->supplier_name) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $po_data->supplier_name) : "";

                    $pdfName  = 'Purchase_Order'.$po_number.$supplier_for_wolh;
                    $encodedId = base64_encode($po_data->po_id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=purchase_order");
               
                    $action .= '<li><a  class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }
                $action .= '<li><a class="dropdown-item email-po-btn" href="javascript:void(0);"><i class="ri-mail-send-fill align-bottom me-2 text-muted" id="email_a"></i> Email</a></li>';

                if (hasAccess("purchase_order", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-purchase_order"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }


                if (hasAccess("purchase_order", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'purchase_order');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on','options'])
        ->make(true);
    }

    // public function store(Request $request)
    // {
    //     dd($request->all());
    //     $year_data = getCurrentYearData();

    //     $existNumber = PurchaseOrder::where([['po_sequence',  $request->po_sequence],['po_number',$request->po_number],['year_id',$year_data->id]])->first();
        
    //     if($existNumber)
    //     {
    //         $latestNo = $this->getLatestPurchaseOrderNumber($request);
    //         $tmp =  $latestNo->getContent();
    //         $area = json_decode($tmp, true);
    //         $po_number =   $area['latest_no'];
    //         $po_sequence = $area['number'];
    //     }
    //     else
    //     {
    //         $po_number = $request->po_number;
    //         $po_sequence = $request->po_sequence;
    //     }

    //     DB::beginTransaction();
    //     try
    //     {
    //         $po_data =  PurchaseOrder::create([
    //             'po_type_id'           =>$request->po_type_id ?? null,
    //             'po_number'           => $po_number ?? null,
    //             'po_sequence'         => $po_sequence ?? null,
    //             'po_date'             => isset($request->po_date) ? Date::createFromFormat('d/m/Y', $request->po_date)->format('Y-m-d') : null,
    //             'po_supplier_id'      => $request->po_supplier_id ?? null,
    //             'po_kind_attn_id'           => $request->po_kind_attn_id ?? null,
    //             'po_copy_from_id'      => $request->po_copy_from_id ?? null,
    //             'po_terms_and_conditions'     => $request->po_terms_and_conditions ?? null,
    //             'po_sp_note'   => $request->po_sp_note ?? null,
    //             'po_total_amount'   => $request->po_total_amount ?? null,
    //             'po_upload_file'     =>  $images !="" ? $images : null,
    //             'po_upload_file_blob_image'    => $blobImage !="" ? $blobImage : null,
    //             'year_id'               => $year_data->id,
    //             'company_id'            => Auth::user()->company_id,
    //             'created_on'            => Carbon::now('Asia/Kolkata')->toDateTimeString(),
    //             'created_by'            => Auth::user()->id,
    //         ]);


    //         $po_details_data = $request->po_details_data = json_decode($request->po_details_data, true);
    //         if(!empty($po_details_data))
    //         {
    //             foreach($po_details_data as $ctKey => $ctVal)
    //             {
    //                 if($ctVal != null)
    //                 {
    //                     $po_details_data = PurchaseOrderDetails::create([
    //                         'pod_po_id'            => $po_data->po_id,

    //                         'pod_item_id'        => !empty($ctVal['pod_item_id']) ? $ctVal['pod_item_id'] : null,

    //                         'pod_description'     => !empty($ctVal['pod_description']) ? $ctVal['pod_description'] : null,

    //                         'pod_po_qty' => !empty($ctVal['pod_po_qty']) ? $ctVal['pod_po_qty'] : null,

    //                         'pod_rate_unit'            => !empty($ctVal['pod_rate_unit']) ? $ctVal['pod_rate_unit'] : null,

    //                         'pod_amount'         => !empty($ctVal['pod_amount']) ? $ctVal['pod_amount'] : null,

    //                         'pod_del_date'           => !empty($ctVal['pod_del_date']) ? Date::createFromFormat('d/m/Y', $ctVal['pod_del_date'])->format('Y-m-d') : null,

    //                         'pod_remark'            => !empty($ctVal['pod_remark']) ? $ctVal['pod_remark'] : null,

    //                         'status' => 'Y',
    //                     ]);
    //                 }
    //             }
    //         }

    //         if($po_data->save())
    //         {
    //             DB::commit();

    //             $get_supplier = PurchaseOrder::select('suppliers.supplier_name')
    //             ->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_order.po_supplier_id')
    //             ->where('purchase_order.po_supplier_id', $po_data->po_supplier_id)
    //             ->first();

    //             $po_number = !empty($po_data->po_number) ? '_'.str_replace('/', '_', $po_data->po_number) : "";

    //             $customer_for_wolh = !empty($get_supplier) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $get_supplier->supplier_name) : "";

    //             $pdf_name = 'Purchase_Order'.$po_number.$customer_for_wolh;

    //             GeneratePdf($po_data->po_id,$pdf_name,'purchase_order','add');
    //             return response()->json([
    //                 'response_code' => '1',
    //                 'response_message' => getResponseMessage('store_success'),
    //             ]);
    //         }
    //         else
    //         {
    //             return response()->json([
    //                 'response_code' => '0',
    //                 'response_message' => getResponseMessage('store_error'),
    //             ]);
    //         }
    //     }
    //     catch(\Exception $e)
    //     {
    //         DB::rollBack();
    //         return response()->json([
    //             'response_code' => '0',
    //             'response_message' => getResponseMessage('store_error'),
    //             'original_error' => $e->getMessage()
    //         ]);
    //     }
    // }
    public function store(Request $request)
    {
        // dd($request->all());
        $year_data = getCurrentYearData();
        $LocationData = getCurrentLocation();

        

        DB::beginTransaction();

        try {
            $existNumber = PurchaseOrder::where([
                ['po_sequence', $request->po_sequence],
                ['po_number', $request->po_number],
                ['year_id', $year_data->id]
            ])->first();

            if ($existNumber) {
                $latestNo = $this->getLatestPurchaseOrderNumber($request);
                $tmp = $latestNo->getContent();
                $area = json_decode($tmp, true);

                $po_number = $area['latest_no'];
                $po_sequence = $area['number'];
            } else {
                $po_number = $request->po_number;
                $po_sequence = $request->po_sequence;
            }

            $page_id = getMenuIdBassedOnDisplayName('purchase_order');
            $assign_format_no = getAssignFormateNoForTransaction($LocationData->location_id, $page_id->id,$request->po_date);
            $amount_rupee = $request->net_amount;
            $amount_in_word = digitsToWords($amount_rupee);


            $po_data = PurchaseOrder::create([
                'po_type_id' => $request->po_type_id ?? null,
                'po_number' => $po_number,
                'po_sequence' => $po_sequence,
                'po_date' => $request->po_date ? Date::createFromFormat('d/m/Y', $request->po_date)->format('Y-m-d') : null,
                'po_supplier_id' => $request->po_supplier_id,
                'po_kind_attn_id' => $request->po_kind_attn_id,
                'bill_to_location_id' => $request->bill_to_location_id,
                'ship_to_location_id' => $request->ship_to_location_id,
                'po_terms_and_conditions' => $request->po_terms_and_conditions,
                'po_sp_note' => $request->po_sp_note,
                'ref_no_date' => $request->ref_no_date,
                'payment_terms' => $request->payment_terms,
                'basic_amount' => $request->basic_amount,
                'gst_type_fix_id' => $request->gst_type_fix_id,
                'sgst_percentage' => $request->sgst_percentage,
                'sgst_amount' => $request->sgst_amount,
                'cgst_percentage' => $request->cgst_percentage,
                'cgst_amount' => $request->cgst_amount,
                'igst_percentage' => $request->igst_percentage,
                'igst_amount' => $request->igst_amount,
                'round_off_val' => $request->round_off_val,
                'net_amount' => $request->net_amount,
                'amount_in_word' => $amount_in_word ?? null,
                'prepared_by_user_id' => $request->prepared_by_user_id,
                'assign_format_no' => $assign_format_no,
                'current_location_id' => $LocationData->location_id ?? null,
                'year_id' => $year_data->id,
                'company_id' => Auth::user()->company_id,
                'created_by' => Auth::user()->id,
                'created_on' => Carbon::now('Asia/Kolkata'),
            ]);
            $details = json_decode($request->po_details_data, true);

            if (!empty($details)) {
                foreach ($details as $row) {

                    if (!empty($row['pod_item_id'])) {

                        // if($row['pod_pid_id'] != 0  && $request->po_type_id == 'From Indent'){

                            // $pendingQty =  DB::table('pending_purchase_indent_qty')
                            //     ->where('pid_id', $row['pod_pid_id'])
                            //     ->value('pending_qty');

                            // if((float)$row['pod_po_qty'] > (float)$pendingQty)
                            // {
                            //     DB::rollBack();
                            //     return response()->json([
                            //         'response_code' => '0',
                            //         'response_message' => 'Purchase Indent Qty. Used.',
                            //     ]);
                            // }
                        
                        // }

                       
                        $from_qty         = (float)$row['pod_po_qty'];
                        $next_qty         = 0;
                        $transaction_mode = 'I';
                        $pod_id           = 0;                    
                        $pid_id           = $row['pod_pid_id'] ?? 0;

                        $checkQty = $this->qtyValidation($from_qty, $next_qty, $transaction_mode, $pod_id, $pid_id, $request->po_type_id);

                        if ($checkQty) {
                            return $checkQty;
                        }
                       
                        PurchaseOrderDetails::create([
                            'pod_po_id' => $po_data->po_id,
                            'pod_pid_id' => (isset($row['pod_pid_id']) && $row['pod_pid_id'] != 0) ? $row['pod_pid_id'] : null,
                            'pod_item_id' => $row['pod_item_id'],
                            'pod_po_qty' => $row['pod_po_qty'],
                            'pod_rate_unit' => $row['pod_rate_unit'],
                            'pod_amount' => $row['pod_amount'],
                            'pod_del_date' => !empty($row['pod_del_date']) ? Date::createFromFormat('d/m/Y', $row['pod_del_date'])->format('Y-m-d') : null,
                            'pod_remark' => $row['pod_remark'] ?? null,
                            'status' => 'Y',
                        ]);
                    }
                }
            }

            DB::commit();
             $get_supplier = PurchaseOrder::select('suppliers.supplier_name')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_order.po_supplier_id')
                ->where('purchase_order.po_supplier_id', $po_data->po_supplier_id)
                ->first();

                $po_number = !empty($po_data->po_number) ? '_'.str_replace('/', '_', $po_data->po_number) : "";

                $customer_for_wolh = !empty($get_supplier) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $get_supplier->supplier_name) : "";

                $pdf_name = 'Purchase_Order'.$po_number.$customer_for_wolh;

                GeneratePdf($po_data->po_id,$pdf_name,'purchase_order','add');
                $encodedId = base64_encode($po_data->po_id);
                if(hasAccess("purchase_order", "print")){
                    
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=purchase_order");
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
        $current_location_id = getCurrentLocation()->location_id;
        $itemTypes = getItemType();   

        $po_data =  DB::select('CALL purchase_order_master(?)', [$request->id]);
        if(!empty($po_data))
        {
            $po_data = $po_data[0];
            $po_data->po_date = $po_data->po_date != "" ? Date::createFromFormat('Y-m-d',  $po_data->po_date)->format('d/m/Y') : "";

            $po_number = !empty($po_data->po_number) ? '_'.str_replace('/', '_', $po_data->po_number) : "";

            $customer_for_wolh = !empty($po_data) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $po_data->supplier_name) : "";

            $po_data->pdf_name = 'Purchase_Order'.$po_number.$customer_for_wolh;

            if(isset($po_data->cmp_logo))
            {
                $po_data->cmp_logo = base64_encode($po_data->cmp_logo);
            }
            
        }
        
        $po_details_data = DB::select('CALL purchase_order_details(?)', [$request->id]);
       

        if($po_details_data){
            foreach($po_details_data as $dKey => $dVal)
            {
                $dVal->pod_del_date = $dVal->pod_del_date != "" ? Date::createFromFormat('Y-m-d',  $dVal->pod_del_date)->format('d/m/Y') : "";
                $dVal->pi_date = $dVal->pi_date != "" ? Date::createFromFormat('Y-m-d',  $dVal->pi_date)->format('d/m/Y') : "";

                // $dVal->main_group = $itemTypes[$dVal->main_group] ?? $dVal->main_group;
                // $dVal->io_stock_qty = getItemCurrentStock($dVal->pod_item_id, $current_location_id);
                if($po_data && $po_data->po_type_id == "From Indent"){
            
                   $dVal->pend_pi_qty = $dVal->pend_pi_qty + $dVal->pod_po_qty;
                   $dVal->pend_pi_qty = number_format((float)$dVal->pend_pi_qty, 3, '.','');
                } else {
                    $dVal->pend_pi_qty = '';
                }

                $PendingQty = DB::table('pending_purchase_order_qty as pend')->where("pend.pod_id",$dVal->pod_id)->value("pend.pending_qty");
                                
                $total_qty = $dVal->pod_po_qty;
                
                $isFound = $total_qty - $PendingQty ;
                // dd($isFound,$total_qty,$PendingQty,"df",$dVal->pod_id);
                if($isFound != null ){
                    $dVal->in_use = true;
                    $dVal->used_qty = $isFound;
                    $isAnyPartInUse = true;
                } else {
                    $dVal->in_use = false;
                    $dVal->used_qty = 0;
                }
            }
            
        }
        
        if($po_data)
        {

            $po_data->in_use = false;
            if($isAnyPartInUse == true){
                $po_data->in_use = true;
            }

            return response()->json([
                'po_data'         => $po_data,
                'po_details_data' => $po_details_data,
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
        // dd($request->all());
        $year_data = getCurrentYearData();
        $LocationData = getCurrentLocation();

        DB::beginTransaction();
        $validated = $request->validate([
                'po_sequence' => [
                    'required',
                    'max:155',
                    Rule::unique('purchase_order')
                        ->where(function ($query) use ($year_data, $LocationData) {
                            $query->where('year_id', $year_data->id)
                                ->where('current_location_id', $LocationData->location_id);
                        })
                        ->ignore($request->id, 'po_id')
                ],
            ], [
                'po_sequence.unique' => 'Duplicate Purchase Order No. Found.',
                'po_sequence.required' => 'Enter Purchase Order No.',
            ]);
        try
        {
            $page_id = getMenuIdBassedOnDisplayName('purchase_order');
            $assign_format_no = getAssignFormateNoForTransaction($LocationData->location_id, $page_id->id,$request->po_date);

            $year_data = getCurrentYearData();
            $amount_rupee = $request->net_amount;
            $amount_in_word = digitsToWords($amount_rupee);
    
            $po_data = PurchaseOrder::where('po_id', $request->id)->update([
                'po_type_id' => $request->po_type_id,
                'po_number' => $request->po_number,
                'po_sequence' => $request->po_sequence,
                'po_date' => $request->po_date ? Date::createFromFormat('d/m/Y', $request->po_date)->format('Y-m-d') : null,
                'po_supplier_id' => $request->po_supplier_id,
                'po_kind_attn_id' => $request->po_kind_attn_id,
                'bill_to_location_id' => $request->bill_to_location_id,
                'ship_to_location_id' => $request->ship_to_location_id,
                'po_terms_and_conditions' => $request->po_terms_and_conditions,
                'po_sp_note' => $request->po_sp_note,
                'ref_no_date' => $request->ref_no_date,
                'payment_terms' => $request->payment_terms,
                'basic_amount' => $request->basic_amount,
                'gst_type_fix_id' => $request->gst_type_fix_id,
                'sgst_percentage' => $request->sgst_percentage,
                'sgst_amount' => $request->sgst_amount,
                'cgst_percentage' => $request->cgst_percentage,
                'cgst_amount' => $request->cgst_amount,
                'igst_percentage' => $request->igst_percentage,
                'igst_amount' => $request->igst_amount,
                'round_off_val' => $request->round_off_val,
                'net_amount' => $request->net_amount,
                'amount_in_word' => $amount_in_word ?? null,
                'prepared_by_user_id' => $request->prepared_by_user_id,
                // 'assign_format_no' => $assign_format_no, not update assign formate discussion ramde sir
                'current_location_id' => $LocationData->location_id ?? null,
                'year_id' => $year_data->id,
                'company_id' => Auth::user()->company_id,
                'last_on'             => Carbon::now('Asia/Kolkata'),
                'last_by'             => Auth::id(),
            ]);

            if(!$po_data)
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }

            $update_details =  PurchaseOrderDetails::where('pod_po_id',$request->id)->update(['status' => 'D',]);
            $details = json_decode($request->po_details_data, true);
            foreach($details as $row)
            {
                if($row['pod_id'] == 0)
                {
                    // if($row['pod_pid_id'] != 0  && $request->po_type_id == 'From Indent'){

                    //     $pendingQty = (float) DB::table('pending_purchase_indent_qty')
                    //         ->where('pid_id', $row['pod_pid_id'])
                    //         ->value('pending_qty');

                    //     if($row['pod_po_qty'] > $pendingQty)
                    //     {
                    //         DB::rollBack();
                    //         return response()->json([
                    //             'response_code' => '0',
                    //             'response_message' => 'Purchase Indent Qty. Is Used.',
                    //         ]);
                    //     }
                    
                    // }

                    $from_qty         = (float)$row['pod_po_qty'];
                    $next_qty         = 0;
                    $transaction_mode = 'I';
                    $pod_id           = 0;
                    $pid_id           = $row['pod_pid_id'] ?? 0;

                    $checkQty = $this->qtyValidation($from_qty, $next_qty, $transaction_mode, $pod_id, $pid_id, $request->po_type_id);
                    if ($checkQty) {
                        return $checkQty;
                    }


                    PurchaseOrderDetails::create([
                        'pod_po_id' => $request->id,
                        'pod_pid_id' => (!empty($row['pod_pid_id']) && $row['pod_pid_id'] != 0) ? $row['pod_pid_id'] : null,
                        'pod_item_id' => $row['pod_item_id'],
                        'pod_po_qty' => $row['pod_po_qty'],
                        'pod_rate_unit' => $row['pod_rate_unit'],
                        'pod_amount' => $row['pod_amount'],
                        'pod_del_date' => !empty($row['pod_del_date']) ? Date::createFromFormat('d/m/Y', $row['pod_del_date'])->format('Y-m-d') : null,
                        'pod_remark' => $row['pod_remark'] ?? null,
                        'status' => 'Y',
                    ]);
                }
                else
                {
                    // $old_po_qty = PurchaseOrderDetails::where('pod_id', $row['pod_id'])->sum('pod_po_qty');
                    // if($row['pod_pid_id'] != 0  && $request->po_type_id == 'From Indent'){

                    //     $pendingQty = (float) DB::table('pending_purchase_indent_qty')
                    //         ->where('pid_id', $row['pod_pid_id'])
                    //         ->value('pending_qty');

                    //     $total_used_qty =  $row['pod_po_qty'] - $old_po_qty;

                        
                        
                    //     if($total_used_qty > 0){
                    //         if($row['pod_po_qty'] > $pendingQty)
                    //         {
                    //             DB::rollBack();
                    //             return response()->json([
                    //                 'response_code' => '0',
                    //                 'response_message' => 'Purchase Indent Qty. Is Used.',
                    //             ]);
                    //         }

                    //     }                        
                    
                    // }
                    // $pendingpoQty = (float) DB::table('pending_purchase_order_qty')
                    //     ->where('pod_id', $row['pod_id'])
                    //     ->value('pending_qty');

                        
                    //     $total_used_qty = $old_po_qty - $row['pod_po_qty'];
                    //     // dd($pendingpoQty,$total_used_qty);
                    //     // if($ctVal['indent_qty'] < $total_used_qty)
                    //     if($pendingpoQty < $total_used_qty)
                    //     {
                    //         DB::rollBack();
                    //         return response()->json([
                    //             'response_code' => '0',
                    //             'response_message' => 'PO Qty. Is Used.',
                    //         ]);
                    //     }

                    $old_po_qty = (float) PurchaseOrderDetails::where('pod_id', $row['pod_id'])->value('pod_po_qty');

                    $new_po_qty       = (float)$row['pod_po_qty'];
                    $from_qty         = max(0, $new_po_qty - $old_po_qty);   // increase
                    $next_qty         = max(0, $old_po_qty - $new_po_qty);   // decrease
                    $transaction_mode = 'U';
                    $pod_id           = $row['pod_id'];
                    $pid_id           = $row['pod_pid_id'] ?? 0;

                    $checkQty = $this->qtyValidation($from_qty, $next_qty, $transaction_mode, $pod_id, $pid_id, $request->po_type_id);
                    if ($checkQty) {
                        return $checkQty;
                    }

                       
                    PurchaseOrderDetails::where('pod_id', $row['pod_id'])->update([

                        'pod_pid_id' => (!empty($row['pod_pid_id']) && $row['pod_pid_id'] != 0) ? $row['pod_pid_id'] : null,
                        'pod_item_id' => $row['pod_item_id'],
                        'pod_po_qty' => $row['pod_po_qty'],
                        'pod_rate_unit' => $row['pod_rate_unit'],
                        'pod_amount' => $row['pod_amount'],
                        'pod_del_date' => !empty($row['pod_del_date']) ? Date::createFromFormat('d/m/Y', $row['pod_del_date'])->format('Y-m-d') : null,
                        'pod_remark' => $row['pod_remark'] ?? null,
                        'status' => 'Y',
                    ]);
                }
            }

            $delete_record = PurchaseOrderDetails::where('pod_po_id',$request->id)->where('status','D')->get();

            if($delete_record->isNotEmpty()){
                $isued_po_data =  DB::select('CALL puchase_order_used_list(?)', [$request->id]);
                
                if(!empty($isued_po_data)){ 

                    return response()->json([
                        'response_code' => '0',
                        'response_message' => "You Can't Delete, Purchase Order Is Used In " . $isued_po_data[0]->table_name . ".",
                    ]);
                }
            }
            

            PurchaseOrderDetails::where('pod_po_id',$request->id)->where('status','D')->delete();

            if($po_data)
            {
                DB::commit();

                $get_suppliers = PurchaseOrder::select('suppliers.supplier_name')
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_order.po_supplier_id')
                    ->where('purchase_order.po_supplier_id', $request->po_supplier_id)
                    ->first();
                    $po_number = !empty($request->po_number) ? '_'.str_replace('/', '_', $request->po_number) : "";
                    
                    $customer_for_wolh = !empty($get_suppliers) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $get_suppliers->supplier_name) : "";
                    
                    $pdf_name = 'Purchase_Order'.$po_number.$customer_for_wolh;
                GeneratePdf($request->id,$pdf_name,'purchase_order','edit');
                $encodedId = base64_encode($request->id);
                
                if(hasAccess("purchase_order", "print")){
                    
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=purchase_order");
                }else{
                    
                    $url ="";
               }
               

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
        DB::beginTransaction();
        try
        {
            // dd($request->id);
            $po_data =  DB::select('CALL puchase_order_used_list(?)', [$request->id]);
            if(!empty($po_data)){ 
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Purchase Order Is Used In " . $po_data[0]->table_name . ".",
                ]);
            }

            PurchaseOrder::where('po_id',$request->id)->delete();
            PurchaseOrderDetails::where('pod_po_id',$request->id)->delete();
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


    public function getAllPONo(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();

        $po_terms = PurchaseOrder::select(
                'purchase_order.po_id',
                'purchase_order.po_number',
            )
            ->whereIn('purchase_order.year_id', $yearIds)
            ->orderBy('purchase_order.po_number', 'asc')
            ->where('purchase_order.po_id', '!=', $request->id)
            ->get();

        return response()->json([
            'response_code' => 1,
            'po_terms' => $po_terms,
        ]);
    }

    public function getPOTermsConditions(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();

        $terms_conditions = PurchaseOrder::select(
                'purchase_order.po_id',
                'purchase_order.po_terms_and_conditions'
            )
            ->where('purchase_order.po_id',$request->po_id)
            ->whereIn('purchase_order.year_id', $yearIds)
            ->orderBy('purchase_order.po_terms_and_conditions', 'asc')
            ->first();

        return response()->json([
            'response_code' => 1,
            'terms_conditions' => $terms_conditions,
        ]);
    }

    public function getLatestPurchaseOrderNumber(Request $request)
    {
        $modal  =  PurchaseOrder::class;
        $sequence = 'po_sequence';           
        $prefix = 'PO';           
      
        $sup_num_format = getLatestSequence($modal,$sequence,$prefix);   
        return response()->json([
          'response_code' => 1,
          'latest_no'     => $sup_num_format['format'],
          'number'        => $sup_num_format['isFound'],
      ]);
    }

    public function getPurchaseIndentListForPurchaseOrder(Request $request)
    {
        $itemTypes = getItemType();
        $yearIds = getCompanyYearIdsToTill();
        $LocationData = getCurrentLocation()->location_id;
        $location_type = getCurrentLocation()->location_type;

        $pi_data = DB::table('pending_purchase_indent_qty as pend')->select([
                'purchase_indent_details.pid_id as pod_pid_id','pi.pi_id','pi.pi_no','pi.pi_date','pi.to_location_id','item.item_name','item_group.item_group','item.item_type as main_group','purchase_indent_details.indent_qty','unit.unit', 'pend.pending_qty as pend_pi_qty',
                'purchase_indent_details.remark','admin.person_name as indent_by','location.location_name'
            ])
            ->leftJoin('purchase_indent_details', 'purchase_indent_details.pid_id', '=', 'pend.pid_id')
            ->leftJoin('purchase_indent as pi', 'pi.pi_id', '=', 'purchase_indent_details.pid_pi_id')
            ->leftJoin('item', 'item.id', '=', 'purchase_indent_details.item_id')
            ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
            ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
            ->leftJoin('admin', 'admin.id', '=', 'pi.indent_by_user_id')
            ->leftJoin('location', 'location.location_id', '=', 'pi.to_location_id')
            ->whereIn('pi.year_id', $yearIds)
            ->where('pend.pending_qty', '>', 0)
            ->when($location_type != 'HO', function ($q) use ($LocationData) {
                $q->where(function ($sub) use ($LocationData) {
                    $sub->where('pi.to_location_id', $LocationData);
                });
            });
            $pi_data = $pi_data->get();

       

        if($pi_data != null)
        {
            $pi_data = $pi_data->map(function ($item) {
                $item->pi_date = $item->pi_date != "" ? Date::createFromFormat('Y-m-d',  $item->pi_date)->format('d/m/Y') : "";
                return $item;
            });
            $pi_data->transform(function ($item) use ($itemTypes) {
                $item->main_group = $itemTypes[$item->main_group] ?? $item->main_group;
                return $item;
            });
            return response()->json([
                'response_code' => '1',
                'pi_data' => $pi_data,
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '1',
                'pi_data' => []
            ]);
        }
    }

    public function getPurchaseIndentPartDataForPurchaseOrder(Request $request)
    {

        $request->pod_pid_id = explode(',', $request->pod_pid_ids);
         $current_location_id = getCurrentLocation()->location_id;

        $pi_part_data = DB::table('pending_purchase_indent_qty as pend')->select('purchase_indent_details.pid_id as pod_pid_id','purchase_indent_details.item_id as pod_item_id','purchase_indent.pi_no','purchase_indent.pi_date','item.item_name','item_group.item_group','item.item_type as main_group','purchase_indent_details.indent_qty','unit.unit', DB::raw('pend.pending_qty as pend_pi_qty'),'item_opening.io_stock_qty')
            ->leftJoin('purchase_indent_details', 'purchase_indent_details.pid_id', '=', 'pend.pid_id')
            ->leftJoin('item', 'item.id', '=', 'purchase_indent_details.item_id')
            ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
            ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
            ->leftJoin('purchase_indent', 'purchase_indent.pi_id', '=', 'purchase_indent_details.pid_pi_id')
            ->leftJoin('item_opening', function($join ) use ($current_location_id) {
                $join->on('item_opening.io_item_id', '=', 'purchase_indent_details.item_id')
                    ->where('item_opening.current_location_id', $current_location_id);
            })
            ->whereIn('purchase_indent_details.pid_id', $request->pod_pid_id)
            ->where('pend.pending_qty', '>', 0)
            ->get();

        if($pi_part_data != null)
        {
            $itemTypes = getItemType();
            $pi_part_data = $pi_part_data->map(function ($item) use ($itemTypes) {
                $item->pi_date = $item->pi_date != "" ? Date::createFromFormat('Y-m-d',  $item->pi_date)->format('d/m/Y') : "";
                $item->main_group = $itemTypes[$item->main_group] ?? $item->main_group;
                $item->pod_po_qty = $item->pend_pi_qty;
                return $item;
            });
            return response()->json([
                'response_code' => '1',
                'po_details_data' => $pi_part_data,
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '1',
                'po_details_data' => []
            ]);
        }

    }
       

    public function getSupplierKindAttn(Request $request)
    {
        // dd($request->all());
        $supplierId = $request->supplier_id;

        $LnrData = PurchaseOrder::select('po_terms_and_conditions')->where('po_supplier_id', $supplierId)->orderby('po_id', 'desc')->first();

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

    public function qtyValidation($from_qty, $next_qty, $transaction_mode, $pod_id, $pid_id = null, $po_type_id = null)
    {      

        // ===================== 1. PURCHASE ORDER VALIDATION FOR  PURCHASE INDENT =====================
        if ($from_qty > 0 && $po_type_id === 'From Indent' && !empty($pid_id) && $pid_id != 0) {

            $pending_indent_qty = DB::table('pending_purchase_indent_qty')
                ->where('pid_id', $pid_id)
                ->value('pending_qty') ?? 0;

            if ((float)$pending_indent_qty < (float)$from_qty) {
                DB::rollBack();
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => 'Purchase Indent Qty. Used.',
                ]);
            }
        }

        // ===================== 2. PURCHASE ORDER VALIDATION FOR GRN  =====================
        if ($next_qty > 0) {

            // $pending_po_qty = DB::table('pending_purchase_order_qty')
            //     ->where('pod_id', $pod_id)
            //     ->value('pending_qty') ?? 0;
            // // dd($pending_po_qty,$next_qty);
            // if ((float)$pending_po_qty < (float)$next_qty) {
            //     DB::rollBack();

            //     $message = ($transaction_mode === 'D')
            //         ? "You Can't Delete, PO Qty. Is Used."
            //         : "You Can't Update, PO Qty. Is Used.";

            //     return response()->json([
            //         'response_code'    => '0',
            //         'response_message' => $message,
            //     ]);
            // }
        }

        return null;   // No error
    }
}