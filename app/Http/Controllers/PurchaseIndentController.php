<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PurchaseIndent;
use App\Models\PurchaseIndentDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;
use App\Models\PurchaseIndentShortClose;


class PurchaseIndentController extends Controller
{
    public function manage()
    {
        return view('manage.manage-purchase_indent');
    }

    public function index(PurchaseIndent $pi_data, Request $request, DataTables $datatables)
    {
        $LocationData = getCurrentLocation();
        $year_data = getCurrentYearData();
        $pi_data = PurchaseIndent::select([
            'purchase_indent.pi_id',
            'purchase_indent.pi_no',
            'purchase_indent.pi_date',
            'to_location.location_name as to_location',
            // 'indent_by.user_name as indent_by',
            'indent_by.person_name as indent_by',
            'item.item_name',
            'item_group.item_group',
            'item.item_type as main_group',
            'purchase_indent_details.indent_qty',
            'unit.unit',
            'purchase_indent_details.remark',
            'purchase_indent.special_note',
            'purchase_indent.created_on',
            'purchase_indent.created_by',
            'purchase_indent.last_by',
            'purchase_indent.last_on',
            'purchase_indent.pi_sequence',
        ])

        ->leftJoin('purchase_indent_details','purchase_indent_details.pid_pi_id','=','purchase_indent.pi_id')
        ->leftJoin('admin as indent_by','indent_by.id','=','purchase_indent.indent_by_user_id')
        ->leftJoin('location as to_location','to_location.location_id','=','purchase_indent.to_location_id')
        ->leftJoin('item','item.id','=','purchase_indent_details.item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->where('purchase_indent.current_location_id', $LocationData->location_id)
        ->where('purchase_indent.year_id', $year_data->id);


        $dataTable = DataTables::of($pi_data)
        ->filterColumn('purchase_indent.pi_no', function($query, $keyword) {
            applySequenceSearch($query, $keyword, 'purchase_indent.pi_no');
        })
        ->editColumn('pi_date', function($pi_data){
            if ($pi_data->pi_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $pi_data->pi_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('purchase_indent.pi_date', function ($q, $k) {
            applyDate($q, $k, 'purchase_indent.pi_date');
        })
        
        ->editColumn('indent_qty', function($pi_data) {
            return $pi_data->indent_qty > 0 ? number_format((float)$pi_data->indent_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('purchase_indent_details.indent_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('indent_qty')
                    ->orWhere('indent_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(indent_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('indent_qty', 'like', "%{$search}%");
            }
        })
        ->editColumn('main_group', function($pi_data) {
            return config('app.item_type')[$pi_data->main_group] ?? $pi_data->main_group;
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
        
        ->addColumn('options',function($pi_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if(hasAccess("purchase_indent", "print")) {
                    $pi_no = !empty($pi_data->pi_no) ? '_'.str_replace('/', '_', $pi_data->pi_no) : "";

                    $pdfName  = 'Material_Indent'.$pi_no;
                    $encodedId = base64_encode($pi_data->pi_id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=purchase_indent");
               
                    $action .= '<li><a  class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }

                if (hasAccess("purchase_indent", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-purchase_indent"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("purchase_indent", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'purchase_indent');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on','options'])
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
            $existNumber = PurchaseIndent::where([['pi_sequence',  $request->pi_sequence],['pi_no',$request->pi_no],['year_id',$year_data->id]])->first();
            
            if($existNumber)
            {
                $latestNo = $this->getLatestPurchaseIndentNumber($request);
                $tmp =  $latestNo->getContent();
                $area = json_decode($tmp, true);
                $pi_no =   $area['latest_no'];
                $pi_sequence = $area['number'];
            }
            else
            {
                $pi_no = $request->pi_no;
                $pi_sequence = $request->pi_sequence;
            }

            $page_id = getMenuIdBassedOnDisplayName('purchase_indent');
            $assign_format_no = getAssignFormateNoForTransaction($current_location_id, $page_id->id,$request->pi_date);


            $pi_data =  PurchaseIndent::create([
                'pi_no'                 => $pi_no ?? null,
                'pi_sequence'           => $pi_sequence ?? null,
                'pi_date'               => isset($request->pi_date) ? Date::createFromFormat('d/m/Y', $request->pi_date)->format('Y-m-d') : null,
                'current_location_id'   => $current_location_id ?? null,
                'to_location_id'        => $request->to_location_id ?? null,
                'indent_by_user_id'     => $request->indent_by_user_id ?? null,
                'special_note'          => $request->special_note ?? null,
                'assign_format_no'      => $assign_format_no,
                'year_id'               => $year_data->id,
                'company_id'            => Auth::user()->company_id,
                'created_on'            => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'            => Auth::user()->id,
            ]);


            $pi_details_data = $request->pi_details_data = json_decode($request->pi_details_data, true);
            if(!empty($pi_details_data))
            {
                foreach($pi_details_data as $ctKey => $ctVal)
                {
                    if($ctVal != null)
                    {
                        $mode = $ctVal['mode'];

                        if($mode == 'Delete') {
                            continue;
                        }

                        if($mode == 'Insert')
                        {
                            $pi_details_data = PurchaseIndentDetails::create([

                                'pid_pi_id'            => $pi_data->pi_id,

                                'item_id'        => !empty($ctVal['item_id']) ? $ctVal['item_id'] : null,

                                'indent_qty' => !empty($ctVal['indent_qty']) ? $ctVal['indent_qty'] : null,

                                'remark'            => !empty($ctVal['remark']) ? $ctVal['remark'] : null,

                            ]);
                        }
                    }
                }
            }

            if($pi_data->save())
            {
                DB::commit();                
                $pi_no = !empty($pi_no) ? '_'.str_replace('/', '_', $pi_no) : "";
                $pdf_name = 'Material_Indent'.$pi_no;
                GeneratePdf($pi_data->pi_id,$pdf_name,'purchase_indent','add');
                 $encodedId = base64_encode($pi_data->pi_id);

                if(hasAccess("purchase_order", "print")){  
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=purchase_indent");
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
            return response()->json([
                'response_code' => '0',
                'response_message' => getResponseMessage('store_error'),
                'original_error' => $e->getMessage()
            ]);
        }
    }

        
    public function edit(Request $request)
    {
        $itemTypes = getItemType();   
        $isAnyPartInUse = false;
        $current_location_id = getCurrentLocation()->location_id;
        $pi_data =  DB::select('CALL purchase_indent_master(?)', [$request->id]);
        if(!empty($pi_data))
        {
            $pi_data = $pi_data[0];
            $pi_data->pi_date = $pi_data->pi_date != "" ? Date::createFromFormat('Y-m-d',  $pi_data->pi_date)->format('d/m/Y') : "";
            if(isset($pi_data->cmp_logo))
            {
                $pi_data->cmp_logo = base64_encode($pi_data->cmp_logo);
            }

            $pi_no = !empty($pi_data->pi_no) ? '_'.str_replace('/', '_', $pi_data->pi_no) : "";       
            $pi_data->pdf_name = 'Purchase_Indent'.$pi_no;
            
        }
        
        $pi_details_data = DB::select('CALL purchase_indent_details(?)', [$request->id]);
        foreach ($pi_details_data as $row) {
            // $row->main_group = $itemTypes[$row->main_group] ?? $row->main_group;
            // $row->io_stock_qty = getItemCurrentStock($row->item_id, $current_location_id);
            
            $PendingQty = DB::table('pending_purchase_indent_qty as pend')->where("pend.pid_id",$row->pid_id)->value("pend.pending_qty");

            $total_qty = $row->indent_qty;

            $isFound = $total_qty - ($PendingQty ?? 0);

            if($isFound != null){
                $row->in_use = true;
                $row->used_qty = $isFound;
                $isAnyPartInUse = true;
            } else {
                $row->in_use = false;
                $row->used_qty = 0;
            }
            // $Items = getItemsForPI($row->item_id);
        }
        unset($row);
        if($pi_data->pi_date != ""){
            $pi_data->in_use = false;
            if($isAnyPartInUse == true){
                $pi_data->in_use = true;
            }
        }
        if($pi_data)
        {
            return response()->json([
                'pi_data'          => $pi_data,
                'pi_details_data'  => $pi_details_data,
                // 'items'            => $Items ?? [],
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
    //     // dd($request->all());
    //     DB::beginTransaction();
    //     $current_location_id = getCurrentLocation()->location_id;

    //     try
    //     {
    //         $year_data = getCurrentYearData();
    //         $validated = $request->validate(['pi_sequence' => ['required', 'max:155', Rule::unique('purchase_indent')->where(function ($query) use ($request, $year_data) { $query->where('year_id', '=', $year_data->id);})->ignore($request->id, 'pi_id')],
    //             'pi_sequence' => ['required','max:155',Rule::unique('purchase_indent')->where(function ($query) use ($request, $year_data) { $query->where('year_id', '=', $year_data->id); })->ignore($request->id, 'pi_id')],
    //         ], [
    //             'pi_sequence.unique' =>   'Purchase Indent No. Already Exists',
    //             'pi_sequence.required' => 'Please Enter Purchase Indent No.',
    //         ]);

                      
    //         $pi_data = PurchaseIndent::where('pi_id', $request->id)->update([
    //             'pi_sequence'       => $request->pi_sequence ?? null,
    //             'pi_no'             => $request->pi_no ?? null,
    //             'pi_date'           => (!empty($request->pi_date)) ? Date::createFromFormat('d/m/Y', $request->pi_date)->format('Y-m-d') : null,
    //             'current_location_id'   => $current_location_id ?? null,
    //             'to_location_id'        => $request->to_location_id ?? null,
    //             'indent_by_user_id'     => $request->indent_by_user_id ?? null,
    //             'special_note'          => $request->special_note ?? null,
    //             'last_on'             => Carbon::now('Asia/Kolkata'),
    //             'last_by'             => Auth::id(),
    //         ]);

    //         if(!$pi_data)
    //         {
    //             return response()->json([
    //                 'response_code' => '0',
    //                 'response_message' => getResponseMessage('update_error'),
    //             ]);
    //         }

    //         $existingIds = PurchaseIndentDetails::where('pid_pi_id', $request->id)
    //         ->pluck('pid_id')
    //         ->toArray();

    //         $pi_details_data = json_decode($request->pi_details_data, true);

    //         $newIds = collect($pi_details_data)
    //         ->pluck('pid_id')
    //         ->filter(function ($id) {
    //             return $id != 0;
    //         })
    //         ->toArray();

    //         $deleteIds = array_diff($existingIds, $newIds);

    //         if (!empty($deleteIds)) {
    //             PurchaseIndentDetails::whereIn('pid_id', $deleteIds)->delete();
    //         }


    //         foreach($pi_details_data as $ctVal)
    //         {
    //             if($ctVal['pid_id'] == 0)
    //             {
    //                 PurchaseIndentDetails::create([

    //                     'pid_pi_id'         => $request->id,

    //                     'item_id'        => !empty($ctVal['item_id']) ? $ctVal['item_id'] : null,

    //                     'indent_qty' => !empty($ctVal['indent_qty']) ? $ctVal['indent_qty'] : null,

    //                     'remark'            => !empty($ctVal['remark']) ? $ctVal['remark'] : null,

    //                 ]);
    //             }
    //             else
    //             {
    //                 PurchaseIndentDetails::where('pid_id', $ctVal['pid_id'])->update([

    //                     'item_id'        => !empty($ctVal['item_id']) ? $ctVal['item_id'] : null,

    //                      'indent_qty' => !empty($ctVal['indent_qty']) ? $ctVal['indent_qty'] : null,

    //                     'remark'            => !empty($ctVal['remark']) ? $ctVal['remark'] : null,
    //                 ]);
    //             }
    //         }


    //         if($pi_data)
    //         {
    //             DB::commit();
    //             return response()->json([
    //                 'response_code' => '1',
    //                 'response_message' => getResponseMessage('update_success'),
    //             ]);
    //         }
    //         else
    //         {
    //             return response()->json([
    //                 'response_code' => '0',
    //                 'response_message' => getResponseMessage('update_error'),
    //             ]);
    //         }
    //     }
    //     catch(\Exception $e)
    //     {
    //         DB::rollBack();
    //         return response()->json([
    //             'response_code' => '0',
    //             'response_message' => getResponseMessage('update_error'),
    //             'original_error' => $e->getMessage()
    //         ]);
    //     }
    // }

    public function update(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();
        $current_location_id = getCurrentLocation()->location_id;

        $year_data = getCurrentYearData();
        $validated = $request->validate([
            'pi_sequence' => [
                'required',
                'max:155',
                Rule::unique('purchase_indent')
                    ->where(function ($query) use ($year_data, $current_location_id) {
                        $query->where('year_id', $year_data->id)
                            ->where('current_location_id', $current_location_id);
                    })
                    ->ignore($request->id, 'pi_id')
            ],
        ], [
            'pi_sequence.unique' => 'Duplicate Indent No. Found.',
            'pi_sequence.required' => 'Enter Indent No.',
        ]);

        try
        {           

            $page_id = getMenuIdBassedOnDisplayName('purchase_indent');
            $assign_format_no = getAssignFormateNoForTransaction($current_location_id, $page_id->id,$request->pi_date);

                      
            $pi_data = PurchaseIndent::where('pi_id', $request->id)->update([
                'pi_sequence'       => $request->pi_sequence ?? null,
                'pi_no'             => $request->pi_no ?? null,
                'pi_date'           => (!empty($request->pi_date)) ? Date::createFromFormat('d/m/Y', $request->pi_date)->format('Y-m-d') : null,
                'current_location_id'   => $current_location_id ?? null,
                'to_location_id'        => $request->to_location_id ?? null,
                'indent_by_user_id'     => $request->indent_by_user_id ?? null,
                'special_note'          => $request->special_note ?? null,
                // 'assign_format_no'      => $assign_format_no,   not update assign formate discussion ramde sir
                'last_on'             => Carbon::now('Asia/Kolkata'),
                'last_by'             => Auth::id(),
            ]);


            if(!$pi_data)
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }

           
            $pi_details_data = json_decode($request->pi_details_data, true);

            if(!empty($pi_details_data))
            {
                foreach($pi_details_data as $ctVal)
                {
                    if(empty($ctVal)) continue;

                    $mode = $ctVal['mode'];

                    
                    if($mode == 'Insert')
                    {
                        PurchaseIndentDetails::create([
                            'pid_pi_id'  => $request->id,
                            'item_id'    => $ctVal['item_id'] ?? null,
                            'indent_qty' => $ctVal['indent_qty'] ?? null,
                            'remark'     => $ctVal['remark'] ?? null,
                        ]);
                    }

                 
                    elseif($mode == 'Update')
                    {
                        if(!empty($ctVal['pid_id']))
                        {

                            $pendingQty = (float) DB::table('pending_purchase_indent_qty')
                            ->where('pid_id', $ctVal['pid_id'])
                            ->value('pending_qty');
                           

                            $old_indent_qty = PurchaseIndentDetails::where('pid_id', $ctVal['pid_id'])->sum('indent_qty');
                            $total_used_qty = $old_indent_qty - $ctVal['indent_qty'];
                            // dd($pendingQty,$total_used_qty);
                            // if($ctVal['indent_qty'] < $total_used_qty)
                            if($pendingQty < $total_used_qty)
                            {
                                DB::rollBack();
                                return response()->json([
                                    'response_code' => '0',
                                    'response_message' => 'Material Indent Qty. Is Used.',
                                ]);
                            }

                            // dd("sdfs");


                            PurchaseIndentDetails::where('pid_id', $ctVal['pid_id'])->update([
                                'item_id'    => $ctVal['item_id'] ?? null,
                                'indent_qty' => $ctVal['indent_qty'] ?? null,
                                'remark'     => $ctVal['remark'] ?? null,
                            ]);
                        }
                    }

                   
                    elseif($mode == 'Delete')
                    {
                        if(!empty($ctVal['pid_id']))
                        {
                            $pi_data =  DB::select('CALL puchase_indent_used_list(?)', [$request->id]);
            
                            if(!empty($pi_data)){ 

                                DB::rollBack();
                                return response()->json([
                                    'response_code' => '0',
                                    'response_message' => "You Can't Delete, Material Indent Is Used In " . $pi_data[0]->table_name . ".",
                                ]);
                            }
                            PurchaseIndentDetails::where('pid_id', $ctVal['pid_id'])->delete();
                        }
                    }
                }
            }

            DB::commit();

            $pi_no = !empty($request->pi_no) ? '_'.str_replace('/', '_', $request->pi_no) : "";       
            $pdf_name = 'Material_Indent'.$pi_no;
            GeneratePdf($request->id,$pdf_name,'purchase_indent','edit');
            $encodedId = base64_encode($request->id);
            
            if(hasAccess("purchase_indent", "print")){  
                $url = url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=purchase_indent");
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
            $pi_data =  DB::select('CALL puchase_indent_used_list(?)', [$request->id]);
            
            if(!empty($pi_data)){ 
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Material Indent Is Used In " . $pi_data[0]->table_name . ".",
                ]);
            }

            PurchaseIndent::where('pi_id',$request->id)->delete();
            PurchaseIndentDetails::where('pid_pi_id',$request->id)->delete();
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

    public function getLatestPurchaseIndentNumber(Request $request)
    {
        $modal  =  PurchaseIndent::class;
        $sequence = 'pi_sequence';           
        $prefix = 'PI';           
      
        $sup_num_format = getLatestSequence($modal,$sequence,$prefix);   
        return response()->json([
          'response_code' => 1,
          'latest_no'     => $sup_num_format['format'],
          'number'        => $sup_num_format['isFound'],
      ]);
    }
    
}