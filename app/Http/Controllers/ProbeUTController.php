<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\GRN;
use App\Models\Item;
use App\Models\ProbeUT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;

class ProbeUTController extends Controller
{
    public function manage()
    {
        return view('manage.manage-probe_ut');
    }

    public function index(ProbeUT $probe_ut, Request $request, DataTables $datatables)
    {
        $LocationData = getCurrentLocation();

        $probe_ut = ProbeUT::select([
            'probe_ut.pu_id',
            'probe_ut.table_unique_id',
            'probe_ut.table_pk_id',
            'probe_ut.item_id',
            'item.item_name',
            'probe_ut.pu_probe',
            'probe_ut.pu_serial_number',
            'probe_ut.pu_size_of_probe',
            'probe_ut.pu_ref_angle',
            'probe_ut.pu_frequency',
            'probe_ut.pu_status',
            'probe_ut.own_location_id',
            'probe_ut.current_location_id',
            'probe_ut.created_on',
            'probe_ut.created_by',
            'probe_ut.last_by',
            'probe_ut.last_on',
            'current_location.location_name as current_location',
            'own_location.location_name as own_location',
        ])
        ->leftJoin('location as current_location','current_location.location_id', '=','probe_ut.current_location_id')
        ->leftJoin('location as own_location','own_location.location_id', '=','probe_ut.own_location_id')
        ->leftJoin('item','item.id','=','probe_ut.item_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id');

        if ($LocationData->location_type != 'HO') {
            $probe_ut->where(function($query) use ($LocationData) {
                $query->where('probe_ut.current_location_id', $LocationData->location_id)
                ->orWhere('probe_ut.own_location_id', $LocationData->location_id);
            });
        }

        $dataTable = DataTables::of($probe_ut)
        ->filterColumn('probe_ut.pu_status', function ($query, $keyword) {
            $globalSearch = request()->input('search.value');
            $searchValue = $globalSearch != '' ? $globalSearch : $keyword;
            $lowerKeyword = strtolower(trim($searchValue));
            if ($lowerKeyword === 'active') {
                $searchStatus = 'active';
            }
            elseif ($lowerKeyword === 'deactive') {
                $searchStatus = 'deactive';
            }
            elseif ($lowerKeyword === 'outside') {
                $searchStatus = 'outside';
            }
            elseif ($lowerKeyword === 'service po') {
                $searchStatus = 'service po';
            }
            if (!empty($searchStatus)) {
                $query->where('probe_ut.pu_status', '=', $searchStatus);
            } 
            else {
                $dbFormatKeyword = str_replace(' ', '_', $lowerKeyword);
                $query->where('probe_ut.pu_status', 'like', "$dbFormatKeyword%");
            }
        })
        ->addColumn('options',function($probe_ut){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("probe_ut", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-probe_ut"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("probe_ut", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'probe_ut');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $LocationData = getCurrentLocation()->location_id;
        DB::beginTransaction();

        $name_for_display = '';
        if(!empty($request->pu_probe) && !empty($request->pu_serial_number)) {
            $name_for_display = $request->pu_probe.' - '.$request->pu_serial_number;
        }

        try
        {
            $pendingQty = DB::table('probe_ut_pending_qty')
                ->where('table_pk_id', $request->table_pk_id)
                ->where('table_unique_id', $request->table_unique_id)
                ->value('pending_qty');

            if((float)$pendingQty == 0)
            {
                DB::rollBack();
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $request->table_unique_id .' Qty. Used.',
                ]);
            }

            $probe_ut = ProbeUT::create([
                'pu_probe'             => $request->pu_probe ?? null,
                'pu_serial_number'      => $request->pu_serial_number ?? null,
                'pu_size_of_probe'      => $request->pu_size_of_probe ?? null,
                'pu_ref_angle'          => $request->pu_ref_angle ?? null,
                'pu_frequency'          => $request->pu_frequency ?? null,
                'pu_status'             => $request->pu_status ?? null,
                'own_location_id'       => $LocationData,
                'current_location_id'   => $LocationData,
                'table_unique_id'       => $request->table_unique_id ?? null,
                'table_pk_id'           => $request->table_pk_id ?? null,
                'item_id'               => $request->item_id ?? null,
                'name_for_display'      => $name_for_display,
                'status_transaction_id' => 0,
                'company_id'            => Auth::user()->company_id,
                'created_on'            => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'            => Auth::user()->id,
            ]);

            if($probe_ut->save())
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
        $itemTypes = getItemType();
        $LocationData = getCurrentLocation()->location_id;
        $probe_ut = DB::select('CALL probe_ut_master(?)', [$request->id]);

        if(!empty($probe_ut))
        {
            $probe_ut = $probe_ut[0];
            $probe_ut->login_location = $LocationData;
            $probe_ut->main_group = $itemTypes[$probe_ut->main_group] ?? $probe_ut->main_group;
            $probe_ut->grn_date = $probe_ut->grn_date != "" ? Date::createFromFormat('Y-m-d', $probe_ut->grn_date)->format('d/m/Y') : "";
            $probe_ut->grn_challan_date = $probe_ut->grn_challan_date != "" ? Date::createFromFormat('Y-m-d', $probe_ut->grn_challan_date)->format('d/m/Y') : "";

            return response()->json([
                'probe_ut'         => $probe_ut,
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
        DB::beginTransaction();
        $LocationData = getCurrentLocation()->location_id;

        $probeUT = ProbeUT::where('pu_id', $request->id)->first();
        if (!$probeUT) {
            DB::rollBack();
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record not found.'
            ]);
        }
        if ($probeUT->current_location_id != $LocationData) {
            DB::rollBack();
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Unauthorized Record Can`t Update.'
            ]);
        }

        try
        {
            $name_for_display = '';
            if(!empty($request->pu_probe) && !empty($request->pu_serial_number)) {
                $name_for_display = $request->pu_probe.' - '.$request->pu_serial_number;
            }

            $probe_ut = ProbeUT::where('pu_id', $request->id)->update([
                'pu_probe'             => $request->pu_probe ?? null,
                'pu_serial_number'      => $request->pu_serial_number ?? null,
                'pu_size_of_probe'      => $request->pu_size_of_probe ?? null,
                'pu_ref_angle'          => $request->pu_ref_angle ?? null,
                'pu_frequency'          => $request->pu_frequency ?? null,
                'table_unique_id'       => $request->table_unique_id ?? null,
                'table_pk_id'           => $request->table_pk_id ?? null,
                'item_id'               => $request->item_id ?? null,
                'name_for_display'      => $name_for_display,
                'own_location_id'       => $LocationData,
                'current_location_id'   => $LocationData,
                'company_id'            => Auth::user()->company_id,
                'last_on'               => Carbon::now('Asia/Kolkata'),
                'last_by'               => Auth::id(),
            ]);

            if($probe_ut)
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
            $usage = DB::select('CALL probe_ut_used_list(?)', [$request->id]);
            if (!empty($usage)) {
                $message = "You Can't Delete, Probe - UT Is Used In ". $usage[0]->table_name.".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            ProbeUT::where('pu_id',$request->id)->delete();
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

    public function existsSizeOfProbe(Request $request)
    {
        if($request->term != "")
        {
            $fdCountry = ProbeUT::select('pu_size_of_probe')->where('pu_size_of_probe', 'LIKE', $request->term.'%')->groupBy('pu_size_of_probe')->get();
            if($fdCountry != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdCountry as $dsKey)
                {
                    $output .= '<li parent-id="pu_size_of_probe" list-id="pu_size_of_probe_list" class="list-group-item" tabindex="0">'.$dsKey->pu_size_of_probe.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'sipList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Size Of Probe Available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'sipList' => '',
                'response_code' => 1,
            ]);
        }
    }

    public function probeUTLNRData()
    {
        return response()->json([
            'response_code' => 1,
            'lnr_data' => null,
        ]);
    }

    public function getPendingGrnListForProbeUt(Request $request)
    {
        $itemTypes = getItemType();
        $LocationData = getCurrentLocation()->location_id;

        $opening_data = DB::table('probe_ut_pending_qty')->select([
            'probe_ut_pending_qty.table_pk_id',
            'probe_ut_pending_qty.table_unique_id',
            'grn_supplier.grn_number',
            'grn_supplier.grn_date',
            'suppliers.supplier_name',
            'grn_supplier.grn_challan_number',
            'grn_supplier.grn_challan_date',
            'item.item_name',
            'item_group.item_group',
            'item.item_type',
            'unit.unit',
            'probe_ut_pending_qty.pending_qty',   
            DB::raw("
                CASE 
                    WHEN probe_ut_pending_qty.table_unique_id = 'GRN' 
                        THEN grn_supplier_details.grnd_qty
                    WHEN probe_ut_pending_qty.table_unique_id = 'Opening' 
                        THEN item_opening.io_opening_qty
                    ELSE 0
                END as qty
            ")
        ])
        ->leftJoin('item_opening', function ($join) {
            $join->on('item_opening.io_id', '=', 'probe_ut_pending_qty.table_pk_id')
                ->where('probe_ut_pending_qty.table_unique_id', '=', 'Opening');
        })
        ->leftJoin('grn_supplier_details', function ($join) {
            $join->on('grn_supplier_details.grnd_id', '=', 'probe_ut_pending_qty.table_pk_id')
                ->where('probe_ut_pending_qty.table_unique_id', '=', 'GRN');
        })
        ->leftJoin('grn_supplier', 'grn_supplier.grn_id', '=', 'grn_supplier_details.grnd_grn_id')
        ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
        ->leftJoin('item', 'item.id', '=', 'probe_ut_pending_qty.item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->where('probe_ut_pending_qty.location_id',$LocationData)
        ->where('probe_ut_pending_qty.pending_qty', '>', 0)
        ->get();

        if($opening_data != null)
        {
            $opening_data->transform(function ($item) use ($itemTypes) {
                $item->item_type = $itemTypes[$item->item_type] ?? $item->item_type;
                $item->grn_date = $item->grn_date != "" ? Date::createFromFormat('Y-m-d', $item->grn_date)->format('d/m/Y') : "";
                $item->grn_challan_date = $item->grn_challan_date != "" ? Date::createFromFormat('Y-m-d', $item->grn_challan_date)->format('d/m/Y') : "";
                return $item;
            });
            return response()->json([
                'response_code' => '1',
                'grn_data' => $opening_data,
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '1',
                'grn_data' => []
            ]);
        }
    }

    public function getPendingGrnForProbeUt(Request $request)
    {
        $itemTypes = getItemType();
        $LocationData = getCurrentLocation()->location_id;

        $request->grnd_ids = explode(',', $request->grnd_ids);
        $request->table_unique_ids = explode(',', $request->table_unique_ids);

        $data = DB::table('probe_ut_pending_qty')
            ->select([
                'probe_ut_pending_qty.table_pk_id',
                'probe_ut_pending_qty.table_unique_id',
                'grn_supplier.grn_number',
                'grn_supplier.grn_date',
                'suppliers.supplier_name as grn_supplier_name',
                'grn_supplier.grn_challan_number',
                'grn_supplier.grn_challan_date',
                'item.item_name',
                'item_group.item_group',
                'item.item_type',
                'item.id as item_id',
                'probe_ut_pending_qty.pending_qty',
                DB::raw("
                    CASE 
                        WHEN probe_ut_pending_qty.table_unique_id = 'GRN' 
                            THEN grn_supplier_details.grnd_qty
                        WHEN probe_ut_pending_qty.table_unique_id = 'Opening' 
                            THEN item_opening.io_opening_qty
                        ELSE 0
                    END as qty
                ")
            ])
            ->leftJoin('item_opening', function ($join) {
                $join->on('item_opening.io_id', '=', 'probe_ut_pending_qty.table_pk_id')
                    ->where('probe_ut_pending_qty.table_unique_id', '=', 'Opening');
            })
            ->leftJoin('grn_supplier_details', function ($join) {
                $join->on('grn_supplier_details.grnd_id', '=', 'probe_ut_pending_qty.table_pk_id')
                    ->where('probe_ut_pending_qty.table_unique_id', '=', 'GRN');
            })
            ->leftJoin('grn_supplier', 'grn_supplier.grn_id', '=', 'grn_supplier_details.grnd_grn_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
            ->leftJoin('item', 'item.id', '=', 'probe_ut_pending_qty.item_id')
            ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
            ->where('probe_ut_pending_qty.location_id', $LocationData)
            ->whereIn('probe_ut_pending_qty.table_pk_id', $request->grnd_ids)
            ->whereIn('probe_ut_pending_qty.table_unique_id', $request->table_unique_ids)
            ->where('probe_ut_pending_qty.pending_qty', '>', 0)
            ->first();

        if ($data != null) {
            $data->item_type = $itemTypes[$data->item_type] ?? $data->item_type;
            $data->grn_date = $data->grn_date != "" ? Date::createFromFormat('Y-m-d', $data->grn_date)->format('d/m/Y') : "";
            $data->grn_challan_date = $data->grn_challan_date != "" ? Date::createFromFormat('Y-m-d', $data->grn_challan_date)->format('d/m/Y') : "";

            return response()->json([
                'response_code' => '1',
                'grn_data' => $data,
            ]);
        }

        return response()->json([
            'response_code' => '1',
            'grn_data' => []
        ]);
    }
}
