<?php
namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\SupplierDC;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use App\Models\Admin;
use App\Models\ItemOpening;
use App\Models\Transaction\DeliveryChallanCustomer;
use App\Models\Transaction\DeliveryChallanCustomerDetails;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class DeliveryChallanCustomerController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-delivery_challan_customer');
    }

    public function index(DeliveryChallanCustomer $grn_data, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $current_location = getCurrentLocation()->location_id;
        $dc_data = DeliveryChallanCustomer::select([

            'delivery_challan_customer.dc_id',
            'delivery_challan_customer.dc_number',
            'delivery_challan_customer.dc_sequence',
            'delivery_challan_customer.dc_date',
            'customers.customer',
            'item.item_name',
            'item_group.item_group',
            'item.item_type as main_group',
            'item.item_type as main_group_key',
            'unit.unit',
            'sr.name_for_display', 
            'delivery_challan_customer_details.sr_table_unique_id',
            'delivery_challan_customer_details.sr_table_pk_id',
            'delivery_challan_customer_details.dc_qty',
            'delivery_challan_customer_details.item_id',
            'delivery_challan_customer_details.remark',
            'prepared_by.person_name as prepared_by',
            'delivery_challan_customer.created_on',
            'delivery_challan_customer.created_by',
            'delivery_challan_customer.last_by',
            'delivery_challan_customer.last_on'
        ])
        ->leftJoin('delivery_challan_customer_details','delivery_challan_customer_details.dc_id','=','delivery_challan_customer.dc_id')
        ->leftJoin('admin as prepared_by','prepared_by.id','=','delivery_challan_customer.prepared_by_user_id')
        ->leftJoin('item','item.id','=','delivery_challan_customer_details.item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->leftJoin('customers','customers.id','=','delivery_challan_customer.customer_id')

        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'delivery_challan_customer_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'delivery_challan_customer_details.sr_table_unique_id');
        })
        ->where('delivery_challan_customer.year_id', $year_data->id)
        ->where('delivery_challan_customer.current_location_id', $current_location);

        $dataTable = DataTables::of($dc_data)
        ->filterColumn('delivery_challan_customer.dc_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'delivery_challan_customer.dc_number');
        })
        ->editColumn('dc_date', function($dc_data){
            if ($dc_data->dc_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $dc_data->dc_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('delivery_challan_customer.dc_date', function ($q, $k) {
            applyDate($q, $k, 'delivery_challan_customer.dc_date');
        })
        
        ->editColumn('dc_qty', function($inquiry_data) {
            return $inquiry_data->dc_qty > 0 ? number_format((float)$inquiry_data->dc_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('delivery_challan_customer_details.dc_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('dc_qty')
                    ->orWhere('dc_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(dc_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('dc_qty', 'like', "%{$search}%");
            }
        })
        ->editColumn('main_group', function($dc_data) {
            return config('app.item_type')[$dc_data->main_group] ?? $dc_data->main_group;
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
       
        ->addColumn('options',function($dc_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if(hasAccess("delivery_challan_customer", "print")) {
                    $dc_number = !empty($dc_data->dc_number) ? '_'.str_replace('/', '_', $dc_data->dc_number) : "";

                    $customer_name = !empty($dc_data->customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $dc_data->customer) : "";


                    $pdfName  = 'Delivery_Challan'.$dc_number.$customer_name;
                    $encodedId = base64_encode($dc_data->dc_id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=delivery_challan_customer");
               
                    $action .= '<li><a  class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }
                if (hasAccess("delivery_challan_customer", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-delivery_challan_customer"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("delivery_challan_customer", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'delivery_challan_customer');
        return $dataTable
        ->rawColumns(['options','sr_no','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $year_data = getCurrentYearData();
        $current_location_id = getCurrentLocation()->location_id;    

        DB::beginTransaction();
        try
        {
            $existNumber = DeliveryChallanCustomer::where([['dc_sequence',  $request->dc_sequence],['dc_number',$request->dc_number],['year_id',$year_data->id]])->first();
            
            if($existNumber)
            {
                $latestNo = $this->getLatestCustomerDCNumber($request);
                $tmp      =  $latestNo->getContent();
                $area     = json_decode($tmp, true);
                $dc_number   =   $area['latest_no'];
                $dc_sequence = $area['number'];
            }
            else
            {
                $dc_number   = $request->dc_number;
                $dc_sequence = $request->dc_sequence;
            }

            $page_id = getMenuIdBassedOnDisplayName('delivery_challan_customer');
            $assign_format_no = getAssignFormateNoForTransaction($current_location_id, $page_id->id,$request->dc_date);

            $dc_data =  DeliveryChallanCustomer::create([

                'dc_sequence'           => $dc_sequence ?? null,
                'dc_number'             => $dc_number ?? null,
                'customer_id'           => $request->customer_id ?? null,
                'current_location_id'   => $current_location_id ?? null,
                'dc_date'               => isset($request->dc_date) ? Date::createFromFormat('d/m/Y', $request->dc_date)->format('Y-m-d') : null,
                'mode_of_transport'     => $request->mode_of_transport ?? null,
                'transporter'           => $request->transporter ?? null,
                'vehicle_no'            => $request->vehicle_no ?? null,
                'special_note'          => $request->special_note ?? null,
                'prepared_by_user_id'   => $request->prepared_by_user_id ?? null,
                'assign_format_no'      => $assign_format_no,
                'year_id'               => $year_data->id,
                'company_id'            => Auth::user()->company_id,
                'created_on'            => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'            => Auth::user()->id,
            ]);


            $dc_details_data = $request->dc_details_data = json_decode($request->dc_details_data, true);
            if(!empty($dc_details_data))
            {
                foreach($dc_details_data as $ctKey => $ctVal)
                {
                    if($ctVal != null)
                    {
                        $mode = $ctVal['mode'];

                        if($mode == 'Delete') {
                            continue;
                        }

                        if($mode == 'Insert')
                        {

                             $rate_unit = ItemOpening::where('current_location_id',$current_location_id)
                            ->where('io_item_id',$ctVal['item_id'])
                            ->sum('io_stock_rate_unit');
                            $amount = $rate_unit * $ctVal['dc_qty'];

                            $dc_details_data = DeliveryChallanCustomerDetails::create([

                                'dc_id'               => $dc_data->dc_id,
                                'item_id'             => $ctVal['item_id'] ?? null,
                                'sr_table_unique_id'  => !empty($ctVal['sr_table_unique_id']) ? $ctVal['sr_table_unique_id'] : null,
                                'sr_table_pk_id'      => !empty($ctVal['sr_table_pk_id']) ? $ctVal['sr_table_pk_id'] : null,
                                'dc_qty'              => $ctVal['dc_qty'] ?? null,
                                'rate_unit'           => $rate_unit ?? null,
                                'amount'              => $amount ?? null,
                                'remark'              => $ctVal['remark'] ?? null,
                                'previous_status_transaction_id' => null,
                                'current_status_transaction_id'  => null,
                            ]);

                            if(!empty($ctVal['sr_table_pk_id'])){
                                changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Outside', 'Active', $current_location_id, 'Insert', $dc_details_data);
                            } 

                            stockEffect($current_location_id, $ctVal['item_id'],$ctVal['item_id'],$ctVal['dc_qty'],0,$amount,0,'Insert','D','Delivery Challan Customer', $dc_details_data->dc_detail_id, $ctVal['sr_table_unique_id'] ?? null, $ctVal['sr_table_pk_id'] ?? null);
                        }
                    }
                }
            }

            if($dc_data->save())
            {
                DB::commit();  
                 $get_customer = DeliveryChallanCustomer::select('customers.customer')
                ->leftJoin('customers', 'customers.id', '=', 
                'delivery_challan_customer.customer_id')
                ->where('delivery_challan_customer.customer_id', $dc_data->customer_id)
                ->first();

                $dc_number = !empty($dc_number) ? '_'.str_replace('/', '_', $dc_number) : "";

                $customer_name = !empty($get_customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $get_customer->customer) : "";

                $pdf_name = 'Delivery_Challan'.$dc_number.$customer_name;
                GeneratePdf($dc_data->dc_id,$pdf_name,'delivery_challan_customer','add');
                 $encodedId = base64_encode($dc_data->dc_id);

                if(hasAccess("delivery_challan_customer", "print")){  
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=delivery_challan_customer");
                }else{
                    $url ="";
                }

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
        }
    }

    public function edit(Request $request)
    {
        $isAnyPartInUse = false;
        $dc_data =  DB::select('CALL delivery_challan_customer_master(?)', [$request->id]);
        if(!empty($dc_data))
        {
            $dc_data = $dc_data[0];
            $dc_data->dc_date = $dc_data->dc_date != "" ? Date::createFromFormat('Y-m-d',  $dc_data->dc_date)->format('d/m/Y') : "";

            $dc_number = !empty($dc_data->dc_number) ? '_'.str_replace('/', '_', $dc_data->dc_number) : "";

            $customer_name = !empty($dc_data) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $dc_data->customer) : "";

            $dc_data->pdf_name = 'Delivery_Challan'.$dc_number.$customer_name;
            if(isset($dc_data->cmp_logo))
            {
                $dc_data->cmp_logo = base64_encode($dc_data->cmp_logo);
            }
        }
        
        $dc_details_data = DB::select('CALL delivery_challan_customer_details(?)', [$request->id]);
        foreach ($dc_details_data as $row) {
            $row->mode = 'Update';
            $amount = $row->dc_qty * $row->stock_rate_unit;
            $row->amount = number_format($amount, 3, '.', '');
            if($row->sr_table_unique_id == "mpt_material" || $row->sr_table_unique_id == "dpt_chemical") {
                $row->io_stock_qty = $row->sr_qty + $row->dc_qty;
                $row->io_stock_qty = number_format((float)$row->io_stock_qty, 3, '.','');
            } else {
                $row->io_stock_qty = $row->io_stock_qty + $row->dc_qty;
                $row->io_stock_qty = number_format((float)$row->io_stock_qty, 3, '.','');
            }

            $row->name_for_display = $row->sr_no;
            $PendingQty = DB::table('pending_delivery_challan_dc_qty as pend')->where("pend.dc_detail_id",$row->dc_detail_id)->value("pend.pending_qty");

                $total_qty = $row->dc_qty;                          
                $isFound = $total_qty - $PendingQty;

                if($isFound > 0){
                    $row->in_use = true;
                    $row->used_qty = $isFound;
                    $isAnyPartInUse = true;
                } else {
                    $row->in_use = false;
                    $row->used_qty = 0;
                }
        }
        unset($row);

        if($dc_data)
        {
            $dc_data->in_use = false;
            if($isAnyPartInUse == true){
                $dc_data->in_use = true;
            }
            return response()->json([
                'dc_data'          => $dc_data,
                'dc_details_data'  => $dc_details_data,
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

    public function update(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();
        $current_location_id = getCurrentLocation()->location_id;

        $year_data = getCurrentYearData();
        $validated = $request->validate([
            'dc_sequence' => [
                'required',
                'max:155',
                Rule::unique('delivery_challan_customer')
                    ->where(function ($query) use ($year_data, $current_location_id) {
                        $query->where('year_id', $year_data->id)
                            ->where('current_location_id', $current_location_id);
                    })
                    ->ignore($request->id, 'dc_id')
            ],
        ], [
            'dc_sequence.unique' => 'Duplicate Delivery Challan No. Found.',
            'dc_sequence.required' => 'Enter Delivery Challan No.',
        ]);

        try
        {           

            $page_id = getMenuIdBassedOnDisplayName('delivery_challan_customer');

            $dc_data = DeliveryChallanCustomer::where('dc_id', $request->id)->update([
                'dc_sequence'           => $request->dc_sequence ?? null,
                'dc_number'             => $request->dc_number ?? null,
                'current_location_id'   => $current_location_id ?? null,
                'dc_date'               => isset($request->dc_date) ? Date::createFromFormat('d/m/Y', $request->dc_date)->format('Y-m-d') : null,
                'mode_of_transport'     => $request->mode_of_transport ?? null,
                'transporter'           => $request->transporter ?? null,
                'vehicle_no'            => $request->vehicle_no ?? null,
                'special_note'          => $request->special_note ?? null,
                'prepared_by_user_id'   => $request->prepared_by_user_id ?? null,
                'last_on'               => Carbon::now('Asia/Kolkata'),
                'last_by'               => Auth::id(),
            ]);


            if(!$dc_data)
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }

            $dc_details_data = json_decode($request->dc_details_data, true);

            if(!empty($dc_details_data))
            {
                foreach($dc_details_data as $ctVal)
                {
                    if(empty($ctVal)) continue;

                    $mode = $ctVal['mode'];
                    
                    if($mode == 'Insert')
                    {
                        $rate_unit = ItemOpening::where('current_location_id',$current_location_id)
                        ->where('io_item_id',$ctVal['item_id'])
                        ->sum('io_stock_rate_unit');
                        $amount = $rate_unit * $ctVal['dc_qty'];

                        $dc_details_data = DeliveryChallanCustomerDetails::create([

                            'dc_id'            => $request->id,
                            'item_id'          => $ctVal['item_id'] ?? null,
                            'sr_table_unique_id'  => !empty($ctVal['sr_table_unique_id']) ? $ctVal['sr_table_unique_id'] : null,
                            'sr_table_pk_id'      => !empty($ctVal['sr_table_pk_id']) ? $ctVal['sr_table_pk_id'] : null,
                            'dc_qty'           => $ctVal['dc_qty'] ?? null,
                            'rate_unit'        => $rate_unit ?? null,
                            'amount'           => $amount ?? null,
                            'remark'           => $ctVal['remark'] ?? null,
                            'previous_status_transaction_id' => null,
                            'current_status_transaction_id'  => null,
                        ]);

                        stockEffect($current_location_id, $ctVal['item_id'],$ctVal['item_id'],$ctVal['dc_qty'],0,$amount,0,'Insert','D','Delivery Challan Customer',$dc_details_data->dc_detail_id, $ctVal['sr_table_unique_id'] ?? null, $ctVal['sr_table_pk_id'] ?? null);

                        if(!empty($ctVal['sr_table_pk_id'])){
                            changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Outside', 'Active', $current_location_id, 'Insert', $dc_details_data);
                        } 
                    }

                 
                    elseif($mode == 'Update')
                    {
                        if(!empty($ctVal['dc_detail_id']))
                        {
                            $olddata = DeliveryChallanCustomerDetails::where('dc_detail_id', $ctVal['dc_detail_id'])->select('amount','rate_unit','item_id','dc_qty')
                            ->first();

                            $pendingQty = (float) DB::table('pending_delivery_challan_dc_qty')
                            ->where('dc_detail_id', $ctVal['dc_detail_id'])
                            ->value('pending_qty');
                           
                            $total_used_qty = $olddata->dc_qty - $ctVal['dc_qty'];
                            
                            if($pendingQty < $total_used_qty)
                            {
                                DB::rollBack();
                                return response()->json([
                                    'response_code' => '0',
                                    'response_message' => 'DC Qty. Is Used.',
                                ]);
                            }

                            DeliveryChallanCustomerDetails::where('dc_detail_id', $ctVal['dc_detail_id'])->update([
                                'dc_id'           => $request->id,
                                'item_id'         => $ctVal['item_id'] ?? null,
                                'sr_table_unique_id'  => !empty($ctVal['sr_table_unique_id']) ? $ctVal['sr_table_unique_id'] : null,
                                'sr_table_pk_id'      => !empty($ctVal['sr_table_pk_id']) ? $ctVal['sr_table_pk_id'] : null,
                                'dc_qty'           => $ctVal['dc_qty'] ?? null,
                                'rate_unit'        => $olddata->rate_unit ?? null,
                                'amount'           => $ctVal['dc_qty'] * $olddata->rate_unit,
                                'remark'           => $ctVal['remark'] ?? null,
                            ]);
                            stockEffect($current_location_id,$ctVal['item_id'], $olddata->item_id,$ctVal['dc_qty'],$olddata->dc_qty,$ctVal['dc_qty'] * $olddata->rate_unit,$olddata->amount,'Update','D','Delivery Challan Customer',$ctVal['dc_detail_id'], $ctVal['sr_table_unique_id'] ?? $olddata->sr_table_unique_id ?? null, $ctVal['sr_table_pk_id'] ?? $olddata->sr_table_pk_id ?? null);
                            
                        }
                    }

                   
                    elseif($mode == 'Delete')
                    {
                        if(!empty($ctVal['dc_detail_id']))
                        {

                            $olddata = DeliveryChallanCustomerDetails::where('dc_detail_id',$ctVal['dc_detail_id'])->select('dc_detail_id','amount','rate_unit','item_id','dc_qty')
                            ->first();

                            stockEffect($current_location_id,$ctVal['item_id'],$ctVal['item_id'],0,
                            $olddata->dc_qty,0,$olddata->amount,'Delete','D','Delivery Challan Customer',$olddata->dc_detail_id, $ctVal['sr_table_unique_id'] ?? $olddata->sr_table_unique_id ?? null, $ctVal['sr_table_pk_id'] ?? $olddata->sr_table_pk_id ?? null);

                            if($ctVal['sr_table_unique_id'] != null || $ctVal['sr_table_unique_id'] != ''){
                                $detailRecord = DeliveryChallanCustomerDetails::find($ctVal['dc_detail_id']);
                                changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'],$ctVal['sr_table_pk_id'],'Outside','Active',$current_location_id,'Delete', $detailRecord);
                            }

                            DeliveryChallanCustomerDetails::where('dc_detail_id', $ctVal['dc_detail_id'])->delete();
                        }
                    }
                }
            }

            DB::commit();

            $get_customer = DeliveryChallanCustomer::select('customers.customer')
            ->leftJoin('customers', 'customers.id', '=', 
            'delivery_challan_customer.customer_id')
            ->where('delivery_challan_customer.customer_id', $request->customer_id)
            ->first();

            $dc_number = !empty($request->dc_number) ? '_'.str_replace('/', '_', $request->dc_number) : "";

            $customer_name = !empty($get_customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $get_customer->customer) : "";

            $pdf_name = 'Delivery_Challan'.$dc_number.$customer_name;

            GeneratePdf($request->id,$pdf_name,'delivery_challan_customer','edit');
                $encodedId = base64_encode($request->id);

            if(hasAccess("delivery_challan_customer", "print")){  
                $url = url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=delivery_challan_customer");
            }else{
                $url ="";
            }

            return response()->json([
                'response_code' => '1',
                'url' => $url,
                'response_message' => getResponseMessage('update_success'),
            ]);
        }
        catch(\Exception $e)
        {
            report($e);
            // DB::rollBack();

            // return response()->json([
            //     'response_code' => '0',
            //     'response_message' => getResponseMessage('update_error'),
            //     'original_error' => $e->getMessage()
            // ]);

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
        }
    }

    public function destroy(Request $request)
    {
        DB::beginTransaction();
        $LocationData = getCurrentLocation()->location_id;
        try
        {

            $sp_data =  DB::select('CALL delivery_challan_used_list(?)', [$request->id]);
            if(!empty($sp_data)){ 
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Delivery Challan Is Used In " . $sp_data[0]->table_name . ".",
                ]);
            }
            $DcDetails = DeliveryChallanCustomerDetails::where('dc_id', $request->id)->get();

            if($DcDetails->isNotEmpty()){
                foreach($DcDetails as $item){
                    stockEffect($LocationData,$item->item_id,$item->item_id,0,$item->dc_qty,0,$item->amount,'Delete','D','Delivery Challan Customer',$item->dc_detail_id, $item->sr_table_unique_id ?? null, $item->sr_table_pk_id ?? null);

                    if($item->sr_table_unique_id != null || $item->sr_table_unique_id != ''){
                      changeStatusAndLocationForSrNo($item->sr_table_unique_id,$item->sr_table_pk_id,'Outside','Active',$LocationData,'Delete', $item);
                    }
                }
            }
            
            DeliveryChallanCustomer::where('dc_id',$request->id)->delete();
            DeliveryChallanCustomerDetails::where('dc_id',$request->id)->delete();
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

            // dd($e->getMessage());
            if(isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451)
            {
                $error_msg = "This Is Used Somewhere, You Can't Delete";
            }
            else if(in_array($message, [
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

    public function getLatestCustomerDCNumber(Request $request)
    {
        $modal  =  DeliveryChallanCustomer::class;
        $sequence = 'dc_sequence';           
        $prefix = 'DC/CUST';           
        $sup_num_format = getLatestSequence($modal,$sequence,$prefix);   

        return response()->json([
          'response_code' => 1,
          'latest_no'     => $sup_num_format['format'],
          'number'        => $sup_num_format['isFound'],
      ]);
    }
}





?>