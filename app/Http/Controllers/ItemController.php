<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Item;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DataTables;
use Date;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PurchaseOrderDetails;
use App\Models\ItemOpening;
use App\Models\ItemOpeningProdArea;
use App\Models\Transaction\InterLocationTransferDetails;
use App\Models\Transaction\ItemIssueDetails;


class ItemController extends Controller
{
    public function manage()
    {
        return view('manage.manage-item');
    }

    public function index(Item $Item, Request $request, DataTables $datatables)
    {
        $item_data = Item::select([
            'item.id',
            'item.item_name',
            'item.item_group_id',
            'item_group.item_group',
            'item.item_type',
            'item.identification_req',
            'item.inter_location_transfer',
            'unit.unit',
            'item.conv_factor',
            'item.min_stock_level',
            'item.document_ref_no',
            'item.validity_date',
            'item.status',
            'item.created_on',
            'item.created_by',
            'item.last_by',
            'item.last_on'
        ])
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->leftJoin('unit','unit.id','=','item.unit_id');
        $dataTable = DataTables::of($item_data)
        ->editColumn('item_name', function($item_data){ 
            return $item_data->item_name;
        })
        ->addColumn('sec_unit', function($item_data) {
            return in_array($item_data->item_type, ['film', 'Industrial X-Ray Films']) ? 'SQIN' : '';
        })
        ->filterColumn('sec_unit', function ($query, $keyword) {
            $globalSearch = request()->input('search.value');
            $searchValue = $globalSearch != '' ? $globalSearch : $keyword;
            $lowerKeyword = strtolower(trim($searchValue));
            if ($lowerKeyword !== '' && stripos('sqin', $lowerKeyword) !== false) {
                $query->whereIn('item.item_type', ['film', 'Industrial X-Ray Films']);
            } elseif ($lowerKeyword !== '') {
                $query->whereRaw('1 = 0');
            }
        })
        ->editColumn('conv_factor', function($item_data) {
            return in_array($item_data->item_type, ['film', 'Industrial X-Ray Films']) ? ($item_data->conv_factor ?? '') : '';
        })
        ->editColumn('min_stock_level', function($item_data) {
            return $item_data->min_stock_level > 0 ? number_format((float)$item_data->min_stock_level, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->editColumn('item_type', function($item_data) {
            return config('app.item_type')[$item_data->item_type] ?? $item_data->item_type;
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

        ->editColumn('status', function($item_data){
            return $item_data->status == "Active" ? "Active":"Deactive";
        })
        ->filterColumn('item.inter_location_transfer', function ($query, $keyword) {
            $globalSearch = request()->input('search.value');
            $searchValue = $globalSearch != '' ? $globalSearch : $keyword;
            $lowerKeyword = strtolower(trim($searchValue));
            if ($lowerKeyword === 'allowed') {
                $searchStatus = 'allowed';
            }
            elseif ($lowerKeyword === 'not allowed') {
                $searchStatus = 'not allowed';
            }
            if (!empty($searchStatus)) {
                $query->where('item.inter_location_transfer', '=', $searchStatus);
            }
            else {
                $dbFormatKeyword = str_replace(' ', '_', $lowerKeyword);
                $query->where('item.inter_location_transfer', 'like', "$dbFormatKeyword%");
            }
        })
         ->filterColumn('item.status', function ($query, $keyword) {
            $globalSearch = request()->input('search.value');
            $searchValue = $globalSearch != '' ? $globalSearch : $keyword;
            $lowerKeyword = strtolower(trim($searchValue));
            if ($lowerKeyword === 'active') {
                $searchStatus = 'active';
            }
            elseif ($lowerKeyword === 'deactive') {
                $searchStatus = 'deactive';
            }
            if (!empty($searchStatus)) {
                $query->where('item.status', '=', $searchStatus);
            }
            else {
                $dbFormatKeyword = str_replace(' ', '_', $lowerKeyword);
                $query->where('item.status', 'like', "$dbFormatKeyword%");
            }
        })
        ->editColumn('validity_date', function($item_data){
            if ($item_data->validity_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $item_data->validity_date)->format(DATE_FORMAT); return $formatedDate;
            }else{
                return '';
            }
        })
        ->filterColumn('item.validity_date', function ($q, $k) {
            applyDate($q, $k, 'item.validity_date');
        })
        ->addColumn('options',function($item_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("item", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_item"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("item", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'item');
        return $dataTable
        ->rawColumns(['options','item_name','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();
        try
        {
            // $existNumber = Item::where('item_code', $request->item_code)->first();
            // if($existNumber)
            // {
            //     $item_code = Item::max('item_code');
            //     if($item_code != null)
            //     {
            //         $item_code++;
            //     }
            //     else
            //     {
            //         $item_code = 1;
            //     }
            // }
            // else
            // {
            //     $item_code = $request->item_code;
            // }

            $item_data = Item::create([
                'item_name' => $request->item_name,
                'item_group_id' => $request->item_group_id ?? null,
                'item_type' => $request->item_type,
                'identification_req' => $request->identification_req ?? null,
                'inter_location_transfer' => $request->inter_location_transfer ?? null,
                'unit_id' => $request->unit_id ?? null,
                'conv_factor' => in_array($request->item_type, ['film', 'Industrial X-Ray Films']) ? ($request->conv_factor ?? null) : null,
                'min_stock_level' => $request->min_stock_level ?? null,
                'document_ref_no' => $request->document_ref_no ?? null,
                'validity_date' => isset($request->validity_date) ? Date::createFromFormat('d/m/Y', $request->validity_date)->format('Y-m-d') : null,
                'status' => $request->status,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($item_data->save())
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
        $item_data = Item::select(['item.*','unit.unit'])
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->where('item.id','=',$request->id)->first();

        $interLocationTransfer = InterLocationTransferDetails::where('item_id', $request->id)->count();
        $item_issue_used = ItemIssueDetails::where('item_id', $request->id)->count();
        // dd($request->id,$interLocationTransfer);
        if($item_data)
        {
            if(!empty($item_data->validity_date))
            {
                $item_data->validity_date = Carbon::createFromFormat('Y-m-d', $item_data->validity_date)->format('d/m/Y');
                }
                if($interLocationTransfer > 0) {
                    $item_data->used_inter_location_transfer_item = true;
                } else {
                    $item_data->used_inter_location_transfer_item = false;                        
                }
                if($item_issue_used > 0) {
                    $item_data->used_item_issue = true;
                } else {
                    $item_data->used_item_issue = false;                        
                }
                
                // Check if used in transactions or opening stocks
                $usage = DB::select('CALL item_master_used_list(?)', [$request->id]);
                $isUsed = false;
                if (!empty($usage)) {
                    // foreach ($usage as $row) {
                        // if ($row->category === 'Transaction' || 
                        //     $row->table_name === 'Item Opening Stock (Prod. Area)' || 
                        //     $row->table_name === 'Item Opening Stock') {
                            $isUsed = true;
                        //     break;
                        // }
                    // }
                }
                $item_data->used_in_any = $isUsed;
                
            return response()->json([
                'item_data' => $item_data,
                // 'inter_location_transfer' => $interLocationTransfer,
                'response_code' => '1',
                'response_message' => '',
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        $in_use = DB::table('item_used')->where('item_id', $request->id)->first();
        $item_type = Item::where('id','=',$request->id)->value('item_type');
        if(($item_type != $request->item_type) && $in_use)
        {
            DB::rollBack();
            return response()->json([
                'response_code' => '0',
                'response_message' => "You Can't Update, Item Is Used In ". $in_use->table_name.".",
            ]);
        }

        try
        {
            $existing_item = Item::where('id', $request->id)->first();
            $item_issue_used = ItemIssueDetails::where('item_id', $request->id)->count();
            
            $conv_factor = in_array($request->item_type, ['film', 'Industrial X-Ray Films']) ? ($request->conv_factor ?? null) : null;
            if ($item_issue_used > 0 && $existing_item) {
                $conv_factor = $existing_item->conv_factor;
            }

            $item_data = Item::where('id','=',$request->id)->update([
                // 'item_code' => $request->item_code,
                'item_name' => $request->item_name,
                'item_group_id' => $request->item_group_id ?? null,
                'item_type' => $request->item_type,
                'identification_req' => $request->identification_req ?? null,
                'unit_id' => $request->unit_id ?? null,
                'conv_factor' => $conv_factor,
                'inter_location_transfer' => $request->inter_location_transfer ?? null,
                'min_stock_level' => $request->min_stock_level ?? null,
                'document_ref_no' => $request->document_ref_no ?? null,
                'validity_date' => isset($request->validity_date) ? Date::createFromFormat('d/m/Y', $request->validity_date)->format('Y-m-d') : null,
                'status' => $request->status,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($item_data)
            {
                DB::commit();
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


            


            // $usage = DB::table('item_usage')
            //             ->where('item_id', $request->id)
            //             ->first();

            $usage = DB::select('CALL item_master_used_list(?)', [$request->id]);
            //             ->where('item_id', $request->id)
            //             ->first();
            // dd($usage);
            if (!empty($usage)) {
                if($usage[0]->table_name == "Item Opening Stock (Prod. Area)"){

                        $hasStock = ItemOpeningProdArea::where('item_id', $request->id)
                        ->where(function($q){
                            $q->where('stock_sq_in', '!=', 0)
                            ->orWhere('opening_sq_in', '!=', 0);
                        })
                        ->exists();
                        
                        // dd($hasStock);

                        if ($hasStock) {
                            return response()->json([
                                'response_code' => '0',
                                'response_message' => "You Can't Delete, Item Is Used In Item Opening Stock (Prod. Area).",
                            ]);
                        }else{
                            ItemOpeningProdArea::where('item_id', $request->id)->delete();
                        }

                }else if($usage[0]->table_name == "Item Opening Stock"){
                        $hasStock = ItemOpening::where('io_item_id', $request->id)
                        ->where(function($q){
                            $q->where('io_stock_qty', '!=', 0)
                            ->orWhere('io_stock_amount', '!=', 0)
                            ->orWhere('io_opening_qty', '!=', 0);
                        })
                        ->exists();

                        // dd($hasStock);

                        if ($hasStock) {
                            return response()->json([
                                'response_code' => '0',
                                'response_message' => "You Can't Delete, Item Is Used In Item Opening.",
                            ]);
                        }else{
                            ItemOpening::where('io_item_id', $request->id)->delete();
                        }
                }else{

                    $message = "You Can't Delete, Item Is Used In ". $usage[0]->table_name.".";
                    return response()->json([
                        'response_code' => '0',
                        'response_message' => $message,
                    ]);
                }

      
                
            }
            // ItemOpening::where('io_item_id', $request->id)->delete();
            Item::destroy($request->id);
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        }
        catch(\Exception $e)
        {
            report($e);
            dd($e->getmessage());
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

    public function existsItem(Request $request)
    {
        if($request->term != "")
        {
            $fdItem = Item::select('item_name')->where('item_name', 'LIKE', '%'.$request->term.'%')->groupBy('item_name')->get();
            if($fdItem != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdItem as $dsKey)
                {
                    $output .= '<li parent-id="item_name" list-id="item_name_list" class="list-group-item" tabindex="0">'.$dsKey->item_name.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'itemList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Item available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'itemList' => '',
                'response_code' => 1,
            ]);
        }
    }

    public function getItemCode()
    {
        $new_code = Item::max('item_code');
        if ($new_code != null)
        {
            $new_code++;
        }
        else
        {
            $new_code = 1;
        }

        return response()->json([
            'item_code' => $new_code,
            'response_code' => 1,
        ]);
    }
}