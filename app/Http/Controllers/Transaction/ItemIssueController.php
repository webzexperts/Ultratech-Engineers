<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use App\Models\Transaction\ItemIssue;
use App\Models\Transaction\ItemIssueDetails;
use App\Models\ItemOpening;
use App\Models\Admin;
use App\Models\Item;

class ItemIssueController extends Controller
{
    //
    public function manage()
    {
        return view('manage.transaction.manage-item_issue');
    }

    public function index(ItemIssue $issue_data, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $current_location = getCurrentLocation()->location_id;
        $issue_data = ItemIssue::select([
            'item_issue.issue_id',
            'item_issue.issue_number',
            'item_issue.issue_sequence',
            'item_issue.issue_date',
            'item.item_name',
            'item_group.item_group',
            'item.item_type as main_group',
            'item.item_type as main_group_key',
            'unit.unit',
            'sr.name_for_display',
            'item_issue_details.sr_table_unique_id',
            'item_issue_details.sr_table_pk_id',
            'item_issue_details.issue_qty',
            'item_issue_details.remark',
            'item_issue_details.issue_type',
            'item_issue.issue_to',
            'prepared_by.person_name as prepared_by',
            'reason.reason_name',
            'item_issue.created_on',
            'item_issue.created_by',
            'item_issue.last_by',
            'item_issue.last_on'
        ])
        ->leftJoin('item_issue_details','item_issue_details.issue_id','=','item_issue.issue_id')
        ->leftJoin('admin as prepared_by','prepared_by.id','=','item_issue.prepared_by_user_id')
        ->leftJoin('item','item.id','=','item_issue_details.item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('reason','reason.id','=','item_issue_details.wastage_reason_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
         ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'item_issue_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'item_issue_details.sr_table_unique_id');
        })
        ->where('item_issue.year_id', $year_data->id)
        ->where('item_issue.current_location_id', $current_location);

        $dataTable = DataTables::of($issue_data)
        ->filterColumn('item_issue.issue_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'item_issue.issue_number');
        })
        ->editColumn('issue_date', function($issue_data){
            if ($issue_data->issue_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $issue_data->issue_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('item_issue.issue_date', function ($q, $k) {
            applyDate($q, $k, 'item_issue.issue_date');
        })
        
        ->editColumn('issue_qty', function($issue_data) {
            return $issue_data->issue_qty > 0 ? number_format((float)$issue_data->issue_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        // ->editColumn('issue_qty', function($issue_data) {
        //     $decimals = in_array($issue_data->main_group, ['film','mpt_material', 'dpt_chemical']) ? 0 : 3;
        //     return $issue_data->issue_qty > 0 ? number_format((float)$issue_data->issue_qty, $decimals, '.', '') : number_format((float) 0, $decimals, '.', '');
        // })
        ->editColumn('main_group', function($issue_data) {
            return config('app.item_type')[$issue_data->main_group] ?? $issue_data->main_group;
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
        ->filterColumn('item_issue_details.issue_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('issue_qty')
                    ->orWhere('issue_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(issue_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('issue_qty', 'like', "%{$search}%");
            }
        })       
        ->addColumn('options',function($issue_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if(hasAccess("item_issue", "print")) {
                    $issue_number = !empty($issue_data->issue_number) ? '_'.str_replace('/', '_', $issue_data->issue_number) : "";

                 
                    $pdfName  = 'Item_Issue'.$issue_number;
                    $encodedId = base64_encode($issue_data->issue_id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=item_issue");
               
                    $action .= '<li><a  class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }

                if (hasAccess("item_issue", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-item_issue"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("item_issue", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                        </a>
                    </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'item_issue');
        return $dataTable
        ->rawColumns(['options','sr_no','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

     public function store(Request $request)
    {

        $year_data = getCurrentYearData();
        $current_location_id = getCurrentLocation()->location_id;    

        DB::beginTransaction();
        try
        {
            $existNumber = ItemIssue::where([['issue_sequence',  $request->issue_sequence],['issue_number',$request->issue_number],['year_id',$year_data->id]])->first();
            
            if($existNumber)
            {
                $latestNo = $this->getLatestItemIssueNumber($request);
                $tmp =  $latestNo->getContent();
                $area = json_decode($tmp, true);
                $issue_number =   $area['latest_no'];
                $issue_sequence = $area['number'];
            }
            else
            {
                $issue_number = $request->issue_number;
                $issue_sequence = $request->issue_sequence;
            }

            $page_id = getMenuIdBassedOnDisplayName('item_issue');
            $assign_format_no = getAssignFormateNoForTransaction($current_location_id, $page_id->id,$request->issue_date);

            $issue_data =  ItemIssue::create([
                'issue_sequence'        => $issue_sequence ?? null,
                'issue_number'          => $issue_number ?? null,
                'issue_date'            => isset($request->issue_date) ? Date::createFromFormat('d/m/Y', $request->issue_date)->format('Y-m-d') : null,   
                'current_location_id'   => $current_location_id ?? null,                
                'issue_to'              => $request->issue_to ?? null,
                'special_note'          => $request->special_note ?? null,
                'prepared_by_user_id'   => $request->prepared_by_user_id ?? null,
                'assign_format_no'      => $assign_format_no,
                'year_id'               => $year_data->id,
                'company_id'            => Auth::user()->company_id,
                'created_on'            => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'            => Auth::user()->id,
            ]);


            $issue_details_data =  json_decode($request->issue_details_data, true);
            if(!empty($issue_details_data))
            {
                foreach($issue_details_data as $ctKey => $ctVal)
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

                            $amount = $rate_unit * $ctVal['issue_qty'];

                            $issue_details_data = ItemIssueDetails::create([
                                'issue_id'            => $issue_data->issue_id,
                                'item_id'             => !empty($ctVal['item_id']) ? $ctVal['item_id'] : null,
                                'sr_table_unique_id'  => !empty($ctVal['sr_table_unique_id']) ? $ctVal['sr_table_unique_id'] : null,
                                'sr_table_pk_id'      => !empty($ctVal['sr_table_pk_id']) ? $ctVal['sr_table_pk_id'] : null,
                                'issue_qty'           => !empty($ctVal['issue_qty']) ? $ctVal['issue_qty'] : null,
                                'conv_factor'         => !empty($ctVal['conv_factor']) ? $ctVal['conv_factor'] : null,
                                'rate_unit'           => $rate_unit ??null,
                                'amount'              => $amount ?? null,
                                'issue_type'          => !empty($ctVal['issue_type']) ? $ctVal['issue_type'] : 'Consumption',
                                'wastage_reason_id'   => !empty($ctVal['wastage_reason_id']) ? $ctVal['wastage_reason_id'] : null,
                                'remark'              => !empty($ctVal['remark']) ? $ctVal['remark'] : null,
                                'previous_status_transaction_id' => null,
                                'current_status_transaction_id'  => null,
                            ]);
                          

                            if(!empty($ctVal['sr_table_pk_id'])){

                                if($ctVal['sr_table_unique_id'] == 'mpt_material' || $ctVal['sr_table_unique_id'] == 'dpt_chemical'){
                                    if($ctVal['issue_type'] == 'Consumption'){
                                      changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Consumed', 'Active', $current_location_id, 'Insert', $issue_details_data);

                                    }else{
                                      changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Deactive', 'Active', $current_location_id, 'Insert', $issue_details_data);

                                    }

                                }else{
                                    changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Deactive', 'Active', $current_location_id, 'Insert', $issue_details_data);
                                }
                            }

                            stockEffect($current_location_id, $ctVal['item_id'],$ctVal['item_id'],$ctVal['issue_qty'],0,$amount,0,'Insert','D','Item Issue', $issue_details_data->issue_detail_id, $ctVal['sr_table_unique_id'] ?? null, $ctVal['sr_table_pk_id'] ?? null);

                            if ($ctVal['item_type'] == 'film' && $ctVal['issue_type'] == 'Production Area') {
                                if (empty($ctVal['conv_factor']) || $ctVal['conv_factor'] <= 0) {
                                    throw new \Exception("Add Conversion Factor in Item Master");
                                }
                                $issue_sq_in = $ctVal['issue_qty'] * $ctVal['conv_factor'];
                                stockEffectSQIN($current_location_id, $ctVal['item_id'], $ctVal['item_id'], $issue_sq_in, 0, 'Insert', 'U', 'Item Issue', $issue_details_data->issue_detail_id);
                            }
                        }
                    }
                }
            }

            if($issue_details_data->save())
            {
                DB::commit();                
                $issue_number = !empty($issue_number) ? '_'.str_replace('/', '_', $issue_number) : "";
                $pdf_name = 'Item_Issue'.$issue_number;
                GeneratePdf($issue_data->issue_id,$pdf_name,'item_issue','add');
                 $encodedId = base64_encode($issue_data->issue_id);

                if(hasAccess("item_issue", "print")){  
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=item_issue");
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
            // return response()->json([
            //     'response_code' => '0',
            //     'response_message' => getResponseMessage('store_error'),
            //     'original_error' => $e->getMessage()
            // ]);
            DB::rollBack();
            $message = $e->getMessage();

            if (in_array($message, [
                    'Insufficient Stock',
                    'Invalid Location Code',
                    'Invalid Item',
                    'Add Conversion Factor in Item Master'
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
        $current_location_id = getCurrentLocation()->location_id;
        $issue_data =  DB::select('CALL item_issue_master(?)', [$request->id]);
        if(!empty($issue_data))
        {
            $issue_data = $issue_data[0];
            $issue_data->issue_date = $issue_data->issue_date != "" ? Date::createFromFormat('Y-m-d',  $issue_data->issue_date)->format('d/m/Y') : "";

            $issue_number = !empty($issue_data->issue_number) ? '_'.str_replace('/', '_', $issue_data->issue_number) : "";


            $issue_data->pdf_name = 'Item_Issue'.$issue_number;

            
            if(isset($issue_data->cmp_logo))
            {
                $issue_data->cmp_logo = base64_encode($issue_data->cmp_logo);
            }           
            
        }

        
        $issue_details_data = DB::select('CALL 	item_issue_details(?)', [$request->id]);
        foreach ($issue_details_data as $row) { 
            if($row->sr_table_unique_id == "mpt_material" || $row->sr_table_unique_id == "dpt_chemical") {    
                $row->io_stock_qty = $row->sr_qty + $row->issue_qty;
                $row->io_stock_qty = number_format((float)$row->io_stock_qty, 3, '.','');       
            }else{
                $row->io_stock_qty = $row->io_stock_qty + $row->issue_qty;
                $row->io_stock_qty = number_format((float)$row->io_stock_qty, 3, '.','');          

            }
        }
        unset($row);

        if($issue_data)
        {
            return response()->json([
                'issue_data'          => $issue_data,
                'issue_details_data'  => $issue_details_data,
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
            'issue_sequence' => [
                'required',
                'max:155',
                Rule::unique('item_issue')
                    ->where(function ($query) use ($year_data, $current_location_id) {
                        $query->where('year_id', $year_data->id)
                            ->where('current_location_id', $current_location_id);
                    })
                    ->ignore($request->id, 'issue_id')
            ],
        ], [
            'issue_sequence.unique' => 'Duplicate Issue No. Found.',
            'issue_sequence.required' => 'Enter Issue No.',
        ]);

        try
        {                    
            $page_id = getMenuIdBassedOnDisplayName('item_issue');
            $assign_format_no = getAssignFormateNoForTransaction($current_location_id, $page_id->id,$request->issue_date);

            $issue_data =  ItemIssue::where('issue_id', $request->id)->update([
                'issue_sequence'        => $request->issue_sequence ?? null,
                'issue_number'          => $request->issue_number ?? null,
                'issue_date'            => isset($request->issue_date) ? Date::createFromFormat('d/m/Y', $request->issue_date)->format('Y-m-d') : null,   
                'current_location_id'   => $current_location_id ?? null,                
                'issue_to'              => $request->issue_to ?? null,
                'special_note'          => $request->special_note ?? null,
                'prepared_by_user_id'   => $request->prepared_by_user_id ?? null,
                // 'assign_format_no'      => $assign_format_no,               
                'last_on'             => Carbon::now('Asia/Kolkata'),
                'last_by'             => Auth::id(),
            ]);


            if(!$issue_data)
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }

            $issue_details_data = json_decode($request->issue_details_data, true);

            if(!empty($issue_details_data))
            {
                foreach($issue_details_data as $ctVal)
                {
                    if(empty($ctVal)) continue;

                    $mode = $ctVal['mode'];
                    
                    if($mode == 'Insert')
                    {
                        $rate_unit = ItemOpening::where('current_location_id',$current_location_id)
                        ->where('io_item_id',$ctVal['item_id'])
                        ->sum('io_stock_rate_unit');

                        $amount = $rate_unit * $ctVal['issue_qty'];

                        $issue_details_data = ItemIssueDetails::create([
                            'issue_id'            => $request->id,
                            'item_id'             => !empty($ctVal['item_id']) ? $ctVal['item_id'] : null,
                            'sr_table_unique_id'  => !empty($ctVal['sr_table_unique_id']) ? $ctVal['sr_table_unique_id'] : null,
                            'sr_table_pk_id'      => !empty($ctVal['sr_table_pk_id']) ? $ctVal['sr_table_pk_id'] : null,
                            'issue_qty'           => !empty($ctVal['issue_qty']) ? $ctVal['issue_qty'] : null,
                            'conv_factor'         => !empty($ctVal['conv_factor']) ? $ctVal['conv_factor'] : null,
                            'rate_unit'           => $rate_unit ??null,
                            'amount'              => $amount ?? null,
                            'issue_type'          => !empty($ctVal['issue_type']) ? $ctVal['issue_type'] : 'Consumption',
                            'wastage_reason_id'   => !empty($ctVal['wastage_reason_id']) ? $ctVal['wastage_reason_id'] : null,
                            'remark'              => !empty($ctVal['remark']) ? $ctVal['remark'] : null,
                            'previous_status_transaction_id' => null,
                            'current_status_transaction_id'  => null,
                        ]);
                        

                        if(!empty($ctVal['sr_table_pk_id'])){

                            if($ctVal['sr_table_unique_id'] == 'mpt_material' || $ctVal['sr_table_unique_id'] == 'dpt_chemical'){
                                if($ctVal['issue_type'] == 'Consumption'){
                                    changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Consumed', 'Active', $current_location_id, 'Insert', $issue_details_data);

                                }else{
                                    changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Deactive', 'Active', $current_location_id, 'Insert', $issue_details_data);

                                }

                            }else{
                                changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Deactive', 'Active', $current_location_id, 'Insert', $issue_details_data);
                            }
                        }

                        stockEffect($current_location_id, $ctVal['item_id'],$ctVal['item_id'],$ctVal['issue_qty'],0,$amount,0,'Insert','D','Item Issue', $issue_details_data->issue_detail_id, $ctVal['sr_table_unique_id'] ?? null, $ctVal['sr_table_pk_id'] ?? null);

                        if ($ctVal['item_type'] == 'film' && $ctVal['issue_type'] == 'Production Area') {
                            if (empty($ctVal['conv_factor']) || $ctVal['conv_factor'] <= 0) {
                                throw new \Exception("Add Conversion Factor in Item Master");
                            }
                            $issue_sq_in = $ctVal['issue_qty'] * $ctVal['conv_factor'];
                            stockEffectSQIN($current_location_id, $ctVal['item_id'], $ctVal['item_id'], $issue_sq_in, 0, 'Insert', 'U', 'Item Issue', $issue_details_data->issue_detail_id);
                        }
                    }

                 
                    elseif($mode == 'Update')
                    {
                        if(!empty($ctVal['issue_detail_id']))
                        {
                            $olddata = ItemIssueDetails::join('item', 'item.id', '=', 'item_issue_details.item_id')
                            ->where('issue_detail_id', $ctVal['issue_detail_id'])
                            ->select('amount','rate_unit','item_issue_details.item_id','issue_qty','issue_type','item_issue_details.conv_factor','item.item_type')
                            ->first();                   
                            

                            ItemIssueDetails::where('issue_detail_id', $ctVal['issue_detail_id'])->update([
                                'issue_id'            => $request->id,
                                'item_id'             => !empty($ctVal['item_id']) ? $ctVal['item_id'] : null,
                                'sr_table_unique_id'  => !empty($ctVal['sr_table_unique_id']) ? $ctVal['sr_table_unique_id'] : null,
                                'sr_table_pk_id'      => !empty($ctVal['sr_table_pk_id']) ? $ctVal['sr_table_pk_id'] : null,
                                'issue_qty'           => !empty($ctVal['issue_qty']) ? $ctVal['issue_qty'] : null,
                                'conv_factor'         => $olddata->conv_factor ?? null,
                                'rate_unit'           => $olddata->rate_unit ?? null,
                                'amount'              => $ctVal['issue_qty'] * $olddata->rate_unit,
                                'issue_type'          => !empty($ctVal['issue_type']) ? $ctVal['issue_type'] : 'Consumption',
                                'wastage_reason_id'   => !empty($ctVal['wastage_reason_id']) ? $ctVal['wastage_reason_id'] : null,
                                'remark'              => !empty($ctVal['remark']) ? $ctVal['remark'] : null,
                            ]);

                            stockEffect($current_location_id,$ctVal['item_id'], $olddata->item_id,$ctVal['issue_qty'],$olddata->issue_qty,$ctVal['issue_qty'] * $olddata->rate_unit,$olddata->amount,'Update','D','Item Issue',$ctVal['issue_detail_id'], $ctVal['sr_table_unique_id'] ?? $olddata->sr_table_unique_id ?? null, $ctVal['sr_table_pk_id'] ?? $olddata->sr_table_pk_id ?? null);

                            $curQtySqIn = 0;
                            $preQtySqIn = 0;

                            if ($ctVal['item_type'] == 'film' && $ctVal['issue_type'] == 'Production Area') {
                                if (empty($ctVal['conv_factor']) || $ctVal['conv_factor'] <= 0) {
                                    throw new \Exception("Add Conversion Factor in Item Master");
                                }
                                $curQtySqIn = $ctVal['issue_qty'] * $ctVal['conv_factor'];
                            }

                            if ($olddata && ($olddata->item_type == 'film' && $olddata->issue_type == 'Production Area')) {
                                if (empty($olddata->conv_factor) || $olddata->conv_factor <= 0) {
                                    throw new \Exception("Add Conversion Factor in Item Master");
                                }
                                $preQtySqIn = $olddata->issue_qty * $olddata->conv_factor;
                            }

                            if ($curQtySqIn > 0 || $preQtySqIn > 0) {
                                stockEffectSQIN($current_location_id, $ctVal['item_id'], $olddata->item_id, $curQtySqIn, $preQtySqIn, 'Update', 'U', 'Item Issue', $ctVal['issue_detail_id']);
                            }
                            
                            if($olddata->issue_type != $ctVal['issue_qty']){
                                $detailRecord = ItemIssueDetails::find($ctVal['issue_detail_id']);
                                if(!empty($ctVal['sr_table_pk_id'])){

                                    if($ctVal['sr_table_unique_id'] == 'mpt_material' || $ctVal['sr_table_unique_id'] == 'dpt_chemical'){
                                        if($ctVal['issue_type'] == 'Consumption'){
                                            changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Consumed', 'Active', $current_location_id, 'Delete', $detailRecord);

                                        }else{
                                            changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Deactive', 'Active', $current_location_id, 'Delete', $detailRecord);

                                        }

                                    }else{
                                        changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Deactive', 'Active', $current_location_id, 'Delete', $detailRecord);
                                    }
                                }

                                if(!empty($ctVal['sr_table_pk_id'])){

                                    if($ctVal['sr_table_unique_id'] == 'mpt_material' || $ctVal['sr_table_unique_id'] == 'dpt_chemical'){
                                        if($ctVal['issue_type'] == 'Consumption'){
                                            changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Consumed', 'Active', $current_location_id, 'Insert', $detailRecord);

                                        }else{
                                            changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Deactive', 'Active', $current_location_id, 'Insert', $detailRecord);

                                        }

                                    }else{
                                        changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Deactive', 'Active', $current_location_id, 'Insert', $detailRecord);
                                    }
                                }

                            }
                            
                            


                        }
                    }

                   
                    elseif($mode == 'Delete')
                    {
                        if(!empty($ctVal['issue_detail_id']))
                        {                           

                            $olddata = ItemIssueDetails::join('item', 'item.id', '=', 'item_issue_details.item_id')
                            ->where('issue_detail_id',$ctVal['issue_detail_id'])
                            ->select('item_issue_details.*', 'item.item_type')
                            ->first();

                            stockEffect($current_location_id,$olddata->item_id,$olddata->item_id,0,$olddata->issue_qty,0,$olddata->amount,'Delete','D','Item Issue',$ctVal['issue_detail_id'], $olddata->sr_table_unique_id ?? null, $olddata->sr_table_pk_id ?? null);

                            if ($olddata && ($olddata->item_type == 'film' && $olddata->issue_type == 'Production Area')) {
                                if (empty($olddata->conv_factor) || $olddata->conv_factor <= 0) {
                                    throw new \Exception("Add Conversion Factor in Item Master");
                                }
                                $preQtySqIn = $olddata->issue_qty * $olddata->conv_factor;
                                stockEffectSQIN($current_location_id, $olddata->item_id, $olddata->item_id, 0, $preQtySqIn, 'Delete', 'U', 'Item Issue', $ctVal['issue_detail_id']);
                            }

                            if(!empty($olddata->sr_table_pk_id)){

                             if($olddata->sr_table_unique_id == 'mpt_material' || $olddata->sr_table_unique_id == 'dpt_chemical'){
                                    if($olddata->issue_type == 'Consumption'){
                                        changeStatusAndLocationForSrNo($olddata->sr_table_unique_id, $olddata->sr_table_pk_id, 'Consumed', 'Active', $current_location_id, 'Delete', $olddata);

                                    }else{
                                        changeStatusAndLocationForSrNo($olddata->sr_table_unique_id, $olddata->sr_table_pk_id, 'Deactive', 'Active', $current_location_id, 'Delete', $olddata);

                                    }

                                }else{
                                    changeStatusAndLocationForSrNo($olddata->sr_table_unique_id, $olddata->sr_table_pk_id, 'Deactive', 'Active', $current_location_id, 'Delete', $olddata);
                                }
                            }                          

                            ItemIssueDetails::where('issue_detail_id', $ctVal['issue_detail_id'])->delete();
                        }
                    }
                }
            }

            DB::commit();            

            $issue_number = !empty($request->issue_number) ? '_'.str_replace('/', '_', $request->issue_number) : "";
            $pdf_name = 'Item_Issue'.$issue_number;
            GeneratePdf($request->id,$pdf_name,'item_issue','edit');
                $encodedId = base64_encode($request->id);

            if(hasAccess("item_issue", "print")){  
                $url = url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=item_issue");
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
                    'Invalid Item',
                    'Add Conversion Factor in Item Master'
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
            // $transferDetails = InterLocationTransferDetails::where('inter_location_transfer_id', $request->id)->get();

            // if($transferDetails->isNotEmpty()){
            //     foreach($transferDetails as $item){

            //         stockEffect($LocationData,$item->item_id,$item->item_id,0,$item->dc_qty,0,$item->amount,'Delete','D','Inter Location Transfer',$item->inter_location_transfer_details_id);

            //         if($item->sr_table_unique_id != null || $item->sr_table_unique_id != ''){
            //           changeStatusAndLocationForSrNo($item->sr_table_unique_id,$item->sr_table_pk_id,'Outside','Active',$LocationData,'Delete');
            //         }
            //     }
            // }

            $issue_details_data = ItemIssueDetails::join('item', 'item.id', '=', 'item_issue_details.item_id')
            ->where('issue_id',$request->id)
            ->select('item_issue_details.*', 'item.item_type')
            ->get();

            if($issue_details_data->isNotEmpty()){

                foreach($issue_details_data as $ctVal){
                    stockEffect($LocationData,$ctVal->item_id,$ctVal->item_id,0,$ctVal->issue_qty,0,$ctVal->amount,'Delete','D','Item Issue',$ctVal->issue_detail_id, $ctVal->sr_table_unique_id ?? null, $ctVal->sr_table_pk_id ?? null);
                    
                    if ($ctVal && ($ctVal->item_type == 'film' && $ctVal->issue_type == 'Production Area')) {
                        if (empty($ctVal->conv_factor) || $ctVal->conv_factor <= 0) {
                            throw new \Exception("Add Conversion Factor in Item Master");
                        }
                        $preQtySqIn = $ctVal->issue_qty * $ctVal->conv_factor;
                        stockEffectSQIN($LocationData, $ctVal->item_id, $ctVal->item_id, 0, $preQtySqIn, 'Delete', 'U', 'Item Issue', $ctVal->issue_detail_id);
                    }

                    if(!empty($ctVal->sr_table_pk_id)){

                        if($ctVal->sr_table_unique_id == 'mpt_material' || $ctVal->sr_table_unique_id == 'dpt_chemical'){
                            if($ctVal->issue_type == 'Consumption'){
                                changeStatusAndLocationForSrNo($ctVal->sr_table_unique_id, $ctVal->sr_table_pk_id, 'Consumed', 'Active', $LocationData, 'Delete', $ctVal);

                            }else{
                                changeStatusAndLocationForSrNo($ctVal->sr_table_unique_id, $ctVal->sr_table_pk_id, 'Deactive', 'Active', $LocationData, 'Delete', $ctVal);

                            }

                        }else{
                            changeStatusAndLocationForSrNo($ctVal->sr_table_unique_id, $ctVal->sr_table_pk_id, 'Deactive', 'Active', $LocationData, 'Delete', $ctVal);
                        }
                    }   

                }

               

            }       

            
            ItemIssue::where('issue_id',$request->id)->delete();
            ItemIssueDetails::where('issue_id',$request->id)->delete();
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
                    'Invalid Item',
                    'Add Conversion Factor in Item Master'
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


     public function existsIssueTo(Request $request){
        // dd($request->all());
        if($request->term != ""){
            $fdUsername = ItemIssue::select('issue_to')->where('issue_to', 'LIKE', '%'.$request->term.'%')->groupBy('issue_to')->get();
            if($fdUsername != null){
                
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdUsername as $usKey){

                    $output .= '<li parent-id="issue_to" list-id="issue_to_list" class="list-group-item" tabindex="0">'.$usKey->issue_to.'</li>';
                } 
                $output .= '</ul>';

                return response()->json([
                    'issueToList' => $output,
                    'response_code' => 1,
                ]);
            }else{
                return response()->json([
                    'response_message' => 'No Designation available',
                    'response_code' => 0,
                ]);
            }
        }else{
            return response()->json([
                'issueToList' => '',
                'response_code' => 1,
            ]);
        }
    }


     public function getLatestItemIssueNumber(Request $request)
    {
        $modal  =  ItemIssue::class;
        $sequence = 'issue_sequence';           
        $prefix = 'ISSUE';           
      
        $sup_num_format = getLatestSequence($modal,$sequence,$prefix);   
        return response()->json([
          'response_code' => 1,
          'latest_no'     => $sup_num_format['format'],
          'number'        => $sup_num_format['isFound'],
      ]);
    }
}