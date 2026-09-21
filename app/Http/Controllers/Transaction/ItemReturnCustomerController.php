<?php
namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\SupplierDC;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use App\Models\Admin;
use App\Models\ItemOpening;
use App\Models\Transaction\ItemReturnCustomer;
use App\Models\Transaction\ItemReturnCustomerDetails;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class ItemReturnCustomerController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-item_return_customer');
    }

    public function index(ItemReturnCustomer $grn_data, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $current_location = getCurrentLocation()->location_id;
        $dc_data = ItemReturnCustomer::select([

            'item_return_customer.return_id',
            'item_return_customer.return_number',
            'item_return_customer.return_date',
            'item_return_customer.return_sequence',
            'delivery_challan_customer.dc_number',
            'delivery_challan_customer.dc_date',
            'customers.customer',
            'item.item_name',
            'item_group.item_group',
            'item.item_type as main_group',
            'item.item_type as main_group_key',
            'unit.unit',
            'sr.name_for_display', 
            'item_return_customer_details.sr_table_unique_id',
            'item_return_customer_details.sr_table_pk_id',
            'item_return_customer_details.grn_qty',
            'item_return_customer_details.item_id',
            'item_return_customer_details.remark',
            'prepared_by.person_name as prepared_by',
            'item_return_customer.created_on',
            'item_return_customer.created_by',
            'item_return_customer.last_by',
            'item_return_customer.last_on'
        ])
        ->leftJoin('item_return_customer_details','item_return_customer_details.return_id','=','item_return_customer.return_id')
        ->leftJoin('delivery_challan_customer_details','delivery_challan_customer_details.dc_detail_id','=','item_return_customer_details.dc_detail_id')
        ->leftJoin('delivery_challan_customer','delivery_challan_customer.dc_id','=','delivery_challan_customer_details.dc_id')
        ->leftJoin('admin as prepared_by','prepared_by.id','=','item_return_customer.prepared_by_user_id')
        ->leftJoin('item','item.id','=','item_return_customer_details.item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->leftJoin('customers','customers.id','=','item_return_customer.customer_id')

        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'item_return_customer_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'item_return_customer_details.sr_table_unique_id');
        })
        ->where('item_return_customer.year_id', $year_data->id)
        ->where('item_return_customer.current_location_id', $current_location);

        $dataTable = DataTables::of($dc_data)
        ->filterColumn('delivery_challan_customer.dc_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'delivery_challan_customer.dc_number');
        })
        ->filterColumn('item_return_customer.return_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'item_return_customer.return_number');
        })

        ->editColumn('return_date', function($dc_data){
            if ($dc_data->return_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $dc_data->return_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('item_return_customer.return_date', function ($q, $k) {
            applyDate($q, $k, 'item_return_customer.return_date');
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


        
        ->editColumn('grn_qty', function($inquiry_data) {
            return $inquiry_data->grn_qty > 0 ? number_format((float)$inquiry_data->grn_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('item_return_customer_details.grn_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('grn_qty')
                    ->orWhere('grn_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(grn_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('grn_qty', 'like', "%{$search}%");
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

                if(hasAccess("item_return_customer", "print")) {
                    $return_number = !empty($dc_data->return_number) ? '_'.str_replace('/', '_', $dc_data->return_number) : "";

                    $customer_name = !empty($dc_data->customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $dc_data->customer) : "";


                    $pdfName  = 'Item_Return'.$return_number.$customer_name;
                    $encodedId = base64_encode($dc_data->return_id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=item_return_customer");
               
                    $action .= '<li><a  class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }
                if (hasAccess("item_return_customer", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-item_return_customer"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("item_return_customer", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'item_return_customer');
        return $dataTable
        ->rawColumns(['options','sr_no','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $year_data = getCurrentYearData();
        $LocationData = getCurrentLocation();

        DB::beginTransaction();

        try {

            $existNumber = ItemReturnCustomer::where([
                ['return_sequence', $request->return_sequence],
                ['return_number', $request->return_number],
                ['year_id', $year_data->id]
            ])->first();

            if ($existNumber) {
                $latestNo = $this->getLatestItemReturnCustomerNumber($request);
                $tmp = $latestNo->getContent();
                $area = json_decode($tmp, true);

                $return_number   = $area['latest_no'];
                $return_sequence = $area['number'];
            } else {
                $return_number   = $request->return_number;
                $return_sequence = $request->return_sequence;
            }

            $page_id = getMenuIdBassedOnDisplayName('item_return_customer');
            $assign_format_no = getAssignFormateNoForTransaction(
                $LocationData->location_id,
                $page_id->id,
                $request->return_date
            );

            $return_data = ItemReturnCustomer::create([
                'return_number'        => $return_number,
                'return_sequence'      => $return_sequence,
                'return_date'          => $request->return_date ? Date::createFromFormat('d/m/Y', $request->return_date)->format('Y-m-d') : null,
                'customer_id'          => $request->customer_id,
                'mode_of_transport'    => $request->mode_of_transport,   
                'transporter'          => $request->transporter,
                'vehicle_no'           => $request->vehicle_no,
                'spcial_note'          => $request->spcial_note,
                'prepared_by_user_id'  => $request->prepared_by_user_id,
                'assign_format_no'     => $assign_format_no ?? null,
                'current_location_id'  => $LocationData->location_id ?? null,
                'year_id'              => $year_data->id,
                'company_id'           => Auth::user()->company_id,
                'created_by'           => Auth::user()->id,
                'created_on'           => Carbon::now('Asia/Kolkata'),
            ]);

            $details = json_decode($request->dc_details_data, true);
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
                            $pendingQty = (float) DB::table('pending_delivery_challan_dc_qty')
                            ->where('dc_detail_id',$row['dc_detail_id'])
                            ->value('pending_qty');

                            $current_qty = (float)$row['grn_qty'];

                            if($current_qty > $pendingQty){
                                DB::rollBack();
                                return response()->json([
                                    'response_code' => '0',
                                    'response_message' => 'DC Qty. Is Used.',
                                ]);

                            }
                            $rate_unit = ItemOpening::where('current_location_id',$LocationData->location_id)
                            ->where('io_item_id',$row['item_id'])
                            ->sum('io_stock_rate_unit');
                            $amount = $rate_unit * $row['grn_qty'];

                             

                            $item_return_details = ItemReturnCustomerDetails::create([

                                'return_id'      => $return_data->return_id,
                                'dc_detail_id'   => !empty($row['dc_detail_id']) ? $row['dc_detail_id'] : null,
                                'item_id'        => $row['item_id'],
                                'sr_table_unique_id' => !empty($row['sr_table_unique_id']) ? $row['sr_table_unique_id'] : null,
                                'sr_table_pk_id'     => !empty($row['sr_table_pk_id']) ? $row['sr_table_pk_id'] : null,
                                'grn_qty'          => $row['grn_qty'],
                                'rate_unit'        => $rate_unit ?? null,
                                'amount'           => $amount ?? null,
                                'remark'           => $row['remark'] ?? null,
                                'previous_status_transaction_id' => null,
                                'current_status_transaction_id'  => null,
                            ]);
                        
                            if(!empty($row['sr_table_pk_id'])){
                                changeStatusAndLocationForSrNo($row['sr_table_unique_id'], $row['sr_table_pk_id'], 'Active', 'Outside', $LocationData->location_id, 'Insert', $item_return_details);
                            } 

                            stockEffect($LocationData->location_id, $row['item_id'],$row['item_id'],$row['grn_qty'],0,$amount,0,'Insert','U','Item Return Customer', $item_return_details->return_detail_id, $row['sr_table_unique_id'] ?? null, $row['sr_table_pk_id'] ?? null);
                        }
                    }
                }
            }

            DB::commit();

            $get_customer = ItemReturnCustomer::select('customers.customer')
            ->leftJoin('customers', 'customers.id', '=', 
            'item_return_customer.customer_id')
            ->where('item_return_customer.customer_id', $return_data->customer_id)
            ->first();

            $return_number = !empty($return_data->return_number) ? '_' . str_replace('/', '_', $return_data->return_number) : "";

            $customer = !empty($get_customer)
                ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $get_customer->customer)
                : "";

            $pdf_name = 'Item_Return'.$return_number.$customer;

            GeneratePdf($return_data->return_id,$pdf_name,'item_return_customer','add');

            $encodedId = base64_encode($return_data->return_id);

            if (hasAccess("item_return_customer", "print")) {
                $url = url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=item_return_customer");
            } else {
               $url = "";
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
            ){
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }else{
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
        $itemTypes = getItemType();
        $current_location_id = getCurrentLocation()->location_id;

        $dc_data =  DB::select('CALL item_return_customer_master(?)', [$request->id]);
        if(!empty($dc_data))
        {
            $dc_data = $dc_data[0];
            $dc_data->return_date = $dc_data->return_date != "" ? Date::createFromFormat('Y-m-d',  $dc_data->return_date)->format('d/m/Y') : "";

            $return_number = !empty($dc_data->return_number) ? '_'.str_replace('/', '_', $dc_data->return_number) : "";

            $customer = !empty($dc_data) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $dc_data->customer) : "";

            $dc_data->pdf_name = 'Item_Return'.$return_number.$customer;
            if(isset($dc_data->cmp_logo))
            {
                $dc_data->cmp_logo = base64_encode($dc_data->cmp_logo);
            }
            
        }
        
        $dc_details_data = DB::select('CALL item_return_customer_details(?)', [$request->id]);
       
        if($dc_details_data){
            foreach($dc_details_data as $dKey => $dVal)
            {
                $dVal->dc_date = $dVal->dc_date != "" ? Date::createFromFormat('Y-m-d',  $dVal->dc_date)->format('d/m/Y') : "";

                $dVal->pending_qty = $dVal->pending_qty +  $dVal->grn_qty;

                $dVal->mode = 'Update';

                $dVal->name_for_display = $dVal->sr_no;
            }
            unset($dVal->sr_no);
        }

        if($dc_data)
        {
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
            'return_sequence' => [
                'required',
                'max:155',
                Rule::unique('item_return_customer')
                    ->where(function ($query) use ($year_data, $current_location_id) {
                        $query->where('year_id', $year_data->id)
                            ->where('current_location_id', $current_location_id);
                    })
                    ->ignore($request->id, 'return_id')
            ],
        ], [
            'return_sequence.unique'   => 'Duplicate GRN No. Found.',
            'return_sequence.required' => 'Enter GRN No.',
        ]);

        try
        {           
            $return_data = ItemReturnCustomer::where('return_id', $request->id)->update([
                'return_number'        => $request->return_number,
                'return_sequence'      => $request->return_sequence,
                'return_date'          => $request->return_date ? Date::createFromFormat('d/m/Y', $request->return_date)->format('Y-m-d') : null,
                'customer_id'          => $request->customer_id,
                'mode_of_transport'    => $request->mode_of_transport,   
                'transporter'          => $request->transporter,
                'vehicle_no'           => $request->vehicle_no,
                'special_note'         => $request->special_note,
                'prepared_by_user_id'  => $request->prepared_by_user_id,
                'current_location_id'  => $current_location_id ?? null,
                'year_id'              => $year_data->id,
                'company_id'           => Auth::user()->company_id,
                'last_on'              => Carbon::now('Asia/Kolkata'),
                'last_by'              => Auth::id(),
            ]);


            if(!$return_data)
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
                        $pendingQty = (float) DB::table('pending_delivery_challan_dc_qty')
                        ->where('dc_detail_id',$ctVal['dc_detail_id'])
                        ->value('pending_qty');

                        $current_qty = (float)$ctVal['grn_qty'];

                        if($current_qty > $pendingQty){
                            DB::rollBack();
                            return response()->json([
                                'response_code' => '0',
                                'response_message' => 'DC Qty. Is Used.',
                            ]);

                        } 

                        $rate_unit = ItemOpening::where('current_location_id',$current_location_id)
                        ->where('io_item_id',$ctVal['item_id'])
                        ->sum('io_stock_rate_unit');
                        $amount = $rate_unit * $ctVal['grn_qty'];

                       $item_return_details = ItemReturnCustomerDetails::create([

                            'return_id'          => $request->id,
                            'dc_detail_id'       => !empty($ctVal['dc_detail_id']) ? $ctVal['dc_detail_id'] : null,
                            'item_id'            => $ctVal['item_id'],
                            'sr_table_unique_id' => !empty($ctVal['sr_table_unique_id']) ? $ctVal['sr_table_unique_id'] : null,
                            'sr_table_pk_id'     => !empty($ctVal['sr_table_pk_id']) ? $ctVal['sr_table_pk_id'] : null,
                            'grn_qty'           => $ctVal['grn_qty'],
                            'rate_unit'         => $rate_unit ?? null,
                            'amount'            => $amount ?? null,
                            'remark'            => $ctVal['remark'] ?? null,
                            'previous_status_transaction_id' => null,
                            'current_status_transaction_id'  => null,
                        ]);

                           if(!empty($ctVal['sr_table_pk_id'])){
                                changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Active', 'Outside', $current_location_id, 'Insert', $item_return_details);
                            } 

                            stockEffect($current_location_id, $ctVal['item_id'],$ctVal['item_id'],$ctVal['grn_qty'],0,$amount,0,'Insert','U','Item Return Customer', $item_return_details->return_detail_id, $ctVal['sr_table_unique_id'] ?? null, $ctVal['sr_table_pk_id'] ?? null);
                    }

                 
                    elseif($mode == 'Update')
                    {
                        if(!empty($ctVal['return_detail_id']))
                        {
                            
                            $pendingQty = (float) DB::table('pending_delivery_challan_dc_qty')
                            ->where('dc_detail_id', $ctVal['dc_detail_id'])
                            ->value('pending_qty');
                           
                            $old_qty = ItemReturnCustomerDetails::where('dc_detail_id', $ctVal['dc_detail_id'])->sum('grn_qty');
                            $total_used_qty = $old_qty - $ctVal['grn_qty'];
                            
                            if($pendingQty < $total_used_qty)
                            {
                                DB::rollBack();
                                return response()->json([
                                    'response_code' => '0',
                                    'response_message' => 'DC Qty. Is Used.',
                                ]);
                            }

                            $olddata = ItemReturnCustomerDetails::where('return_detail_id', $ctVal['return_detail_id'])->select('amount','rate_unit','item_id','grn_qty')
                            ->first();

                            $dc_details_data = ItemReturnCustomerDetails::where('return_detail_id', $ctVal['return_detail_id'])->update([
                                 'return_id'        => $request->id,
                                 'dc_detail_id'     => !empty($ctVal['dc_detail_id']) ? $ctVal['dc_detail_id'] : null,
                                'item_id'           => $ctVal['item_id'],
                                'sr_table_unique_id' => !empty($ctVal['sr_table_unique_id']) ? $ctVal['sr_table_unique_id'] : null,
                                'sr_table_pk_id'     => !empty($ctVal['sr_table_pk_id']) ? $ctVal['sr_table_pk_id'] : null,
                                'grn_qty'          => $ctVal['grn_qty'],
                                'rate_unit'        =>$olddata->rate_unit ?? null,
                                'amount'           => $ctVal['grn_qty'] * $olddata->rate_unit,
                                'remark'           => !empty($ctVal['remark']) ? $ctVal['remark'] : null,
                            ]);

                            stockEffect($current_location_id,$ctVal['item_id'], $olddata->item_id,$ctVal['grn_qty'],$olddata->grn_qty,$ctVal['grn_qty'] * $olddata->rate_unit,$olddata->amount,'Update','U','Item Return Customer',$ctVal['return_detail_id'], $ctVal['sr_table_unique_id'] ?? $olddata->sr_table_unique_id ?? null, $ctVal['sr_table_pk_id'] ?? $olddata->sr_table_pk_id ?? null);
                            
                        }
                    }

                   
                    elseif($mode == 'Delete')
                    {
                        if(!empty($ctVal['return_detail_id']))
                        {

                            $olddata = ItemReturnCustomerDetails::where('return_detail_id',$ctVal['return_detail_id'])->select('return_detail_id','amount','rate_unit','item_id','grn_qty')
                            ->first();

                             stockEffect($current_location_id,$ctVal['item_id'],$ctVal['item_id'],0,$olddata->grn_qty,0,$olddata->amount,'Delete','U','Item Retutn Customer',$olddata->return_detail_id, $ctVal['sr_table_unique_id'] ?? $olddata->sr_table_unique_id ?? null, $ctVal['sr_table_pk_id'] ?? $olddata->sr_table_pk_id ?? null);

                            if($ctVal['sr_table_unique_id'] != null || $ctVal['sr_table_unique_id'] != ''){
                                $detailRecord = ItemReturnCustomerDetails::find($ctVal['return_detail_id']);
                                changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'],$ctVal['sr_table_pk_id'],'Active','Outside',$current_location_id,'Delete', $detailRecord);
                            }
                            ItemReturnCustomerDetails::where('return_detail_id', $ctVal['return_detail_id'])->delete();
                        }
                    }
                }
            }

            DB::commit();

             $get_customer = ItemReturnCustomer::select('customers.customer')
            ->leftJoin('customers', 'customers.id', '=', 
            'item_return_customer.customer_id')
            ->where('item_return_customer.customer_id', $request->customer_id)
            ->first();

            $customer_name = !empty($get_customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $get_customer->customer) : "";

            $return_number = !empty($request->return_number) ? '_'.str_replace('/', '_', $request->return_number) : "";


            $pdf_name = 'Item_Return'.$return_number.$customer_name;
            GeneratePdf($request->id,$pdf_name,'item_return_customer','edit');
                $encodedId = base64_encode($request->id);

            if(hasAccess("item_return_customer", "print")){  
                $url = url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=item_return_customer");
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
            $DcDetails = ItemReturnCustomerDetails::where('return_id', $request->id)->get();

            if($DcDetails->isNotEmpty()){
                foreach($DcDetails as $item){
                    stockEffect($LocationData,$item->item_id,$item->item_id,0,$item->grn_qty,0,$item->amount,'Delete','U','Item Return Customer',$item->return_detail_id, $item->sr_table_unique_id ?? null, $item->sr_table_pk_id ?? null);

                    if($item->sr_table_unique_id != null || $item->sr_table_unique_id != ''){
                      changeStatusAndLocationForSrNo($item->sr_table_unique_id,$item->sr_table_pk_id,'Active','Outside',$LocationData,'Delete', $item);
                    }
                }
            }
            
            ItemReturnCustomer::where('return_id',$request->id)->delete();
            ItemReturnCustomerDetails::where('return_id',$request->id)->delete();
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

            // dd($e->getMessage());
            if(isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451)
            {
                $error_msg = "This Is Used Somewhere, You Can't Delete";
            }
            else if($e->getMessage() == 'Insufficient Stock' || $e->getMessage() == 'Invalid Location Code' || $e->getMessage() == 'Invalid Item'){

                $error_msg = $e->getMessage();
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

    public function getLatestItemReturnCustomerNumber(Request $request)
    {
        $modal  =  ItemReturnCustomer::class;
        $sequence = 'return_sequence';           
        $prefix = 'GRN/CUST';           
        $sup_num_format = getLatestSequence($modal,$sequence,$prefix);   

        return response()->json([
          'response_code' => 1,
          'latest_no'     => $sup_num_format['format'],
          'number'        => $sup_num_format['isFound'],
      ]);
    }

    public function getPendingCustomerForItemReturn(Request $request)
    {
        $yearIds          = getCompanyYearIdsToTill();
        $current_location = getCurrentLocation();

        $dc_customer = DB::table('pending_delivery_challan_dc_qty  as pend')->select([
        'customers.customer','customers.id',
        ])
        ->leftJoin('delivery_challan_customer_details', 'delivery_challan_customer_details.dc_detail_id', '=', 'pend.dc_detail_id')
        ->leftJoin('delivery_challan_customer', 'delivery_challan_customer.dc_id', '=', 'delivery_challan_customer_details.dc_id')           
        ->leftJoin('customers', 'customers.id', '=', 'delivery_challan_customer.customer_id')            
        ->whereIn('delivery_challan_customer.year_id', $yearIds)
        ->where('delivery_challan_customer.current_location_id', $current_location->location_id)
        ->where('pend.pending_qty', '>', 0)
        ->distinct()
        ->orderBy('customers.customer', 'asc')
        ->get();
           
        return response()->json([
            'response_code' => 1,
            'dc_customer'  => $dc_customer,
        ]);
    }


    public function getPendingDCCustomerListForItemReturn(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();
        $current_location = getCurrentLocation();

        $itemTypes = getItemType();

        $dc_data = DB::table('pending_delivery_challan_dc_qty as pend')
        ->select([
            'delivery_challan_customer_details.dc_detail_id',
            'delivery_challan_customer.dc_number',
            'delivery_challan_customer.dc_date',
            'item.item_name','item_group.item_group',
            'item.item_type as main_group',
            'pend.pending_qty','unit.unit',
            'delivery_challan_customer_details.remark',
            'admin.person_name as prepared_by',
            'sr.name_for_display','pend.dc_detail_id'])
        ->leftJoin('delivery_challan_customer_details','delivery_challan_customer_details.dc_detail_id','=','pend.dc_detail_id')
        ->leftJoin('delivery_challan_customer', 'delivery_challan_customer.dc_id', '=', 'delivery_challan_customer_details.dc_id')    
        ->leftJoin('item','item.id','=','delivery_challan_customer_details.item_id')  
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        ->leftJoin('admin', 'admin.id', '=', 'delivery_challan_customer.prepared_by_user_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')  
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'delivery_challan_customer_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'delivery_challan_customer_details.sr_table_unique_id');
        })
        ->where('delivery_challan_customer.customer_id',$request->customer_id)
        ->Where('delivery_challan_customer.current_location_id', $current_location->location_id)
        ->whereIn('delivery_challan_customer.year_id',$yearIds)  
        ->where('pend.pending_qty', '>', 0)
        ->get();

        
        if ($dc_data != null) {
            foreach ($dc_data as $cpKey => $cpVal) {
                if ($cpVal->dc_date != null) {
                    $cpVal->dc_date = Date::createFromFormat('Y-m-d', $cpVal->dc_date)->format('d/m/Y');
                }
                $cpVal->main_group = $itemTypes[$cpVal->main_group] ?? $cpVal->main_group;
               
            }
        }


        $dc_data = $dc_data->sortBy('delivery_challan_customer_details.dc_detail_id')->values();

        

        if ($dc_data != null) {
            return response()->json([
                'response_code' => 1,
                'dc_data'  => $dc_data,            
            ]);
        }else{
            return response()->json([
                'response_code' => 1,
                'dc_data'  => [],            
            ]);

        }
    }

    public function getPendingDCCustomerDataForItemReturn(Request $request){

        $yearIds = getCompanyYearIdsToTill();
        $itemTypes = getItemType();
        $request->dc_detail_ids = explode(',', $request->dc_detail_ids);
        $current_location = getCurrentLocation();
        
        $dc_data = DB::table('pending_delivery_challan_dc_qty as pend')
        ->select(['delivery_challan_customer_details.dc_detail_id','delivery_challan_customer.dc_number',
        'delivery_challan_customer.dc_date',
        'item.item_name','item_group.item_group',
        'item.item_type as main_group','pend.pending_qty',
        'unit.unit','sr.name_for_display',
        'delivery_challan_customer_details.sr_table_unique_id','delivery_challan_customer_details.sr_table_pk_id',
        'pend.dc_detail_id','delivery_challan_customer_details.item_id','item_opening.io_stock_qty'])
        ->leftJoin('delivery_challan_customer_details','delivery_challan_customer_details.dc_detail_id','=','pend.dc_detail_id')
        ->leftJoin('delivery_challan_customer', 'delivery_challan_customer.dc_id', '=', 'delivery_challan_customer_details.dc_id')    
        ->leftJoin('item','item.id','=','delivery_challan_customer_details.item_id')  
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        ->leftJoin('admin', 'admin.id', '=', 'delivery_challan_customer.prepared_by_user_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')  
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'delivery_challan_customer_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'delivery_challan_customer_details.sr_table_unique_id');
        })
        ->leftJoin('item_opening', function($join ) use ($current_location) {
            $join->on('item_opening.io_item_id', '=', 'delivery_challan_customer_details.item_id')
                ->where('item_opening.current_location_id', $current_location->location_id);
        })
        ->where('delivery_challan_customer.current_location_id', $current_location->location_id)
        ->where('pend.pending_qty', '>', 0)
        ->whereIn('delivery_challan_customer.year_id',$yearIds)  
        ->whereIn('pend.dc_detail_id', $request->dc_detail_ids)
        ->get();

      
        if ($dc_data != null) {
            foreach ($dc_data as $cpKey => $cpVal) {

                if ($cpVal->dc_date != null) {
                    $cpVal->dc_date = Date::createFromFormat('Y-m-d', $cpVal->dc_date)->format('d/m/Y');
                }               

                $cpVal->main_group = $itemTypes[$cpVal->main_group] ?? $cpVal->main_group;
            }
        }

        $dc_data = $dc_data->sortBy('dc_detail_id')
        ->sortBy('dc_detail_id')
        ->values();

        if ($dc_data != null) {
            return response()->json([
                'response_code' => '1',
                'dc_data' => $dc_data,
               
            ]);
        } else {
            return response()->json([
                'response_code' => '1',
                'dc_data' => []
            ]);
        }
    }
}


?>