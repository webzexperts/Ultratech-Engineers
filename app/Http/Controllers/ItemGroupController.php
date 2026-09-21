<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ItemGroup;
use App\Models\Admin;
use App\Models\Item;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DataTables;
use Date;

class ItemGroupController extends Controller
{
    //
    public function manage()
    {
        return view('manage.manage-item_group');
    }

    public function getItemGroups()
    {
        $item_group = ItemGroup::select('id','item_group','item_type','identification_req')->orderBy('item_group','asc')->get();
        if($item_group)
        {
            return response()->json([
                'item_group' => $item_group,
                'response_code' => '1'
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'No Data Avilable',
            ]);
        }
    }

    public function index(ItemGroup $ItemGroup, Request $request, DataTables $datatables)
    {
        $itemgroup_data = ItemGroup::select([
            'item_group.id',
            'item_group.item_group',
            'item_group.item_type',
            'item_group.identification_req',
            'item_group.notify_before',
            'item_group.created_on',
            'item_group.created_by',
            'item_group.last_by',
            'item_group.last_on'
        ]);
        $dataTable = DataTables::of($itemgroup_data)
        ->editColumn('item_group', function($itemgroup_data){ 
            return ucfirst($itemgroup_data->item_group);
        })

        ->editColumn('item_type', function($itemgroup_data) {
            return config('app.item_type')[$itemgroup_data->item_type] ?? $itemgroup_data->item_type;
        })
        ->filterColumn('item_group.item_type', function($query, $keyword) {

            $itemTypes = config('app.item_type');
            $matchedKeys = [];
            foreach ($itemTypes as $key => $value) {
                if (stripos($value, $keyword) !== false) {
                    $matchedKeys[] = $key;
                }
            }
            if (!empty($matchedKeys)) {
                $query->whereIn('item_group.item_type', $matchedKeys);
            } else {
                $query->where('item_group.item_type', 'like', "%{$keyword}%");
            }
        })

      
        ->editColumn('identification_req', function($itemgroup_data){ 
            return ucfirst($itemgroup_data->identification_req);
        })
        ->addColumn('options',function($itemgroup_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("item_group", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_item_group"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("item_group", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'item_group');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_group' =>'required|max:255|unique:item_group',
            'ig_item_type'       => 'required',
        ],
        [
            'item_group.required'     => 'Enter Item Group',
            'item_group.unique'       => 'Duplicate Item Group Found.',
            'ig_item_type.required'      => 'Select Item Type',
            
        ]);

        DB::beginTransaction();
        try
        {
            $item_group_data = ItemGroup::create([
                'item_group'             => $request->item_group,
                'item_type'              => $request->ig_item_type,
                'identification_req'     => $request->ig_identification_req ?? 'no',                
                'notify_before'          => $request->notify_before ?? 0,
                'company_id'             => Auth::user()->company_id,
                'created_on'             => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'             => Auth::user()->id
            ]);

            if($item_group_data->save())
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

    public function edit(Request $request){
        $item_group_data = ItemGroup::where('id','=',$request->id)->first();

        $isAnyPartInUse = false;
        $InUseInItem = Item::where("item.item_group_id",$request->id)->first();
        if($InUseInItem != "")
        {
            $item_group_data->in_use = true;
        }
        else
        {
            $item_group_data->in_use = false;
        }

        if($item_group_data)
        {
            return response()->json([
                'item_group_data' => $item_group_data,
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
        $validated = $request->validate([
            'item_group' =>['required','max:255',Rule::unique('item_group')->ignore($request->id, 'id')],
            'ig_item_type'       => 'required',
        ],
        [
            'item_group.required'     => 'Enter Item Group',
            'item_group.unique'       => 'Duplicate Item Group Found.',
            'ig_item_type.required'      => 'Select Item Type',
            
        ]);

        DB::beginTransaction();

        try
        {
            $item_group_data = ItemGroup::where('id','=',$request->id)->update([
                'item_group'             => $request->item_group,
                'item_type'              => $request->ig_item_type,
                'identification_req'     => $request->ig_identification_req ?? 'no',
                'notify_before'          => $request->notify_before ?? 0,             
                'company_id'             => Auth::user()->company_id,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($item_group_data)
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
        DB::beginTransaction();
        try
        {
            $usage = DB::select('CALL item_group_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                DB::rollBack();
                $message = "You Can't Delete, Item Group Is Used In " . $usage[0]->table_name . ".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            ItemGroup::destroy($request->id);
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


    public function existsItemGroup(Request $request)
    {
        if($request->term != "")
        {
            $fdItemGroup = ItemGroup::select('item_group')->where('item_group', 'LIKE', '%'.$request->term.'%')->groupBy('item_group')->get();
            if($fdItemGroup != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdItemGroup as $dsKey)
                {
                    $output .= '<li parent-id="item_group" list-id="item_group_list" class="list-group-item" tabindex="0">'.$dsKey->item_group.'</li>';
                } 
                $output .= '</ul>';

                return response()->json([
                    'ItemGroupList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Item Group available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'ItemGroupList' => '',
                'response_code' => 1,
            ]);
        }
    }
}