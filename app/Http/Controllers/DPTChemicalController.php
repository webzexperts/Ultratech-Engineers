<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GRN;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use App\Models\DPTChemical;
use App\Models\ItemOpening;

class DPTChemicalController extends Controller
{
    public function manage()
    {
        return view('manage.manage-chemical_dpt');
    }

    public function index(DPTChemical $dpt_chemical, Request $request, DataTables $datatables)
    {
        $LocationData = getCurrentLocation();
        $dpt_chemical = DPTChemical::select([
            'dpt_chemical.dpt_id',
            'dpt_chemical.dpt_chemical',
            //'dpt_chemical.dpt_designation',
            'dpt_chemical.dpt_make',
            'dpt_chemical.dpt_batch_no',
            // 'dpt_chemical.dpt_identification_no',
            'dpt_chemical.dpt_expiry_date',
            'dpt_chemical.dpt_status',
            'dpt_chemical.table_unique_id',
            'dpt_chemical.table_pk_id',
            'dpt_chemical.created_on',
            'dpt_chemical.created_by',
            'dpt_chemical.last_by',
            'dpt_chemical.last_on',
            DB::raw("
                COALESCE(
                    (
                        SELECT GROUP_CONCAT(CONCAT(loc.location_name) SEPARATOR ', ')
                        FROM mpt_dpt_material_batch_wise_stock stk
                        JOIN location loc ON loc.location_id = stk.current_location_id
                        WHERE stk.sr_table_unique_id = 'dpt_chemical'
                          AND stk.sr_table_pk_id = dpt_chemical.dpt_id
                          AND stk.stock_qty > 0
                    ),
                   ''
                ) as current_location
            "),
            'own_location.location_name as own_location',
        ])
        ->leftJoin('location as current_location','current_location.location_id', '=','dpt_chemical.current_location_id')
        ->leftJoin('location as own_location','own_location.location_id', '=','dpt_chemical.own_location_id');
        if ($LocationData->location_type != 'HO') {
            $dpt_chemical->where(function($query) use ($LocationData) {
                $query->where('dpt_chemical.current_location_id', $LocationData->location_id)
                ->orWhere('dpt_chemical.own_location_id', $LocationData->location_id)
                ->orWhereExists(function($q) use ($LocationData) {
                    $q->select(DB::raw(1))
                      ->from('mpt_dpt_material_batch_wise_stock as stk')
                      ->whereColumn('stk.sr_table_pk_id', 'dpt_chemical.dpt_id')
                      ->where('stk.sr_table_unique_id', 'dpt_chemical')
                      ->where('stk.current_location_id', $LocationData->location_id)
                      ->where('stk.stock_qty', '>', 0);
                });
            });
        }
         
        

        $dataTable = DataTables::of($dpt_chemical)
       

        ->editColumn('dpt_expiry_date', function($dpt_chemical){
            if ($dpt_chemical->dpt_expiry_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $dpt_chemical->dpt_expiry_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('dpt_chemical.dpt_expiry_date', function ($q, $k) {
            applyDate($q, $k, 'dpt_chemical.dpt_expiry_date');
        })
        ->filterColumn('current_location.location_name', function ($query, $keyword) {
            $query->whereRaw("
                EXISTS (
                    SELECT 1 FROM mpt_dpt_material_batch_wise_stock stk
                    JOIN location loc ON loc.location_id = stk.current_location_id
                    WHERE stk.sr_table_unique_id = 'dpt_chemical'
                      AND stk.sr_table_pk_id = dpt_chemical.dpt_id
                      AND stk.stock_qty > 0
                      AND loc.location_name LIKE ?
                )
            ", ["%{$keyword}%"]);
        })
        ->filterColumn('dpt_chemical.dpt_status', function ($query, $keyword) {
            $globalSearch = request()->input('search.value');
            $searchValue = $globalSearch != '' ? $globalSearch : $keyword;
            $lowerKeyword = strtolower(trim($searchValue));
            if ($lowerKeyword === 'active') {
                $searchStatus = 'active';
            }
            elseif ($lowerKeyword === 'deactive') {
                $searchStatus = 'deactive';
            }
            elseif ($lowerKeyword === 'consumed') {
                $searchStatus = 'consumed';
            }
            if (!empty($searchStatus)) {
                $query->where('dpt_chemical.dpt_status', '=', $searchStatus);
            } 
            else {
                $dbFormatKeyword = str_replace(' ', '_', $lowerKeyword);
                $query->where('dpt_chemical.dpt_status', 'like', "$dbFormatKeyword%");
            }
        })
        
        ->addColumn('options',function($dpt_chemical) use ($LocationData){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("chemical_dpt", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-chemical_dpt"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("chemical_dpt", "delete")){
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'dpt_chemical');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        
    // dd($request->all()) ;
        $LocationData = getCurrentLocation()->location_id;
        $year_data = getCurrentYearData();
        DB::beginTransaction();
        try
        {
            $pendingQty = DB::table('dpt_chemical_pending_qty')
                ->where('table_pk_id', $request->table_pk_id)
                ->value('pending_qty') ?? 0;

            if((float)$request->dpt_qty <= 0)
            {
                DB::rollBack();
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Enter Qty. greater than 0.',
                ]);
            }

            if((float)$pendingQty == 0 || (float)$request->dpt_qty > (float)$pendingQty)
            {
                DB::rollBack();
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Qty. Cannot Be Greater Than Pending Qty. ' . $pendingQty . '.',
                ]);
            }

            $name_for_display = '';
            if(!empty($request->dpt_chemical) && !empty($request->dpt_batch_no) && !empty($request->dpt_expiry_date)) {
                $name_for_display = $request->dpt_chemical.' - '.$request->dpt_batch_no.' - '.$request->dpt_expiry_date;

                if (!empty($request->grn_no)) {
                    $name_for_display .= ' - ' . $request->grn_no;
                }
            }

            $dpt_chemical =  DPTChemical::create([

                'dpt_chemical'           => $request->dpt_chemical ?? null,
                //'dpt_designation'        => $request->dpt_designation ?? null,
                'dpt_make'               => $request->dpt_make ?? null,
                'dpt_batch_no'           => $request->dpt_batch_no ?? null,
                // 'dpt_identification_no'  => $request->dpt_identification_no ?? null,
                'dpt_expiry_date'        => isset($request->dpt_expiry_date) ? Date::createFromFormat('d/m/Y', $request->dpt_expiry_date)->format('Y-m-d') : null,
                'dpt_qty'                => $request->dpt_qty ?? null,
                'dpt_status'             => $request->dpt_status ?? null,
                'table_unique_id'        => $request->table_unique_id ?? null,
                'table_pk_id'            => $request->table_pk_id ?? null,
                'item_id'                => $request->item_id ?? null,
                'name_for_display'       =>  $name_for_display,
                'own_location_id'        => $LocationData,
                'current_location_id'   => $LocationData,
                'company_id'            => Auth::user()->company_id,
                'created_on'            => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'            => Auth::user()->id,
            ]);

            if($dpt_chemical->save())
            {
                stockEffect($LocationData, $dpt_chemical->item_id ?? 0, $dpt_chemical->item_id ?? 0, $request->dpt_qty ?? 0, 0, 0, 0, 'Insert', 'U', 'DPT Chemical', $dpt_chemical->dpt_id, 'dpt_chemical', $dpt_chemical->dpt_id, true);

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
            // return response()->json([
            //     'response_code' => '0',
            //     'response_message' => getResponseMessage('store_error'),
            //     'original_error' => $e->getMessage()
            // ]);
            $message = $e->getMessage();

            if (in_array($message, [
                    'Insufficient Stock',
                    'Invalid Location Code',
                    'Invalid Item'
                ]) || str_contains($message, 'Sr. No. Is Already')
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


         $itemTypes = getItemType();
        $LocationData = getCurrentLocation()->location_id;
        $chemical_dpt = DB::select('CALL dpt_chemical_master(?)', [$request->id]);

        if(!empty($chemical_dpt))
        {
            $chemical_dpt = $chemical_dpt[0];
            
            if(!empty($chemical_dpt->dpt_expiry_date))
            {
                $chemical_dpt->dpt_expiry_date = Carbon::createFromFormat('Y-m-d', $chemical_dpt->dpt_expiry_date)->format('d/m/Y');
            }
            

            $chemical_dpt->login_location = $LocationData; 
            $chemical_dpt->main_group = $itemTypes[$chemical_dpt->main_group] ?? $chemical_dpt->main_group;

            if(!empty($chemical_dpt->grn_date)){
                $chemical_dpt->grn_date = $chemical_dpt->grn_date != "" ? Date::createFromFormat('Y-m-d',  $chemical_dpt->grn_date)->format('d/m/Y') : "";
            }
            if(!empty($chemical_dpt->grn_challan_date)){
                $chemical_dpt->grn_challan_date = $chemical_dpt->grn_challan_date != "" ? Date::createFromFormat('Y-m-d',  $chemical_dpt->grn_challan_date)->format('d/m/Y') : "";
            }

            $own_stock = DB::table('mpt_dpt_material_batch_wise_stock')
                ->where('sr_table_unique_id', 'dpt_chemical')
                ->where('sr_table_pk_id', $request->id)
                ->where('current_location_id', $chemical_dpt->own_location_id)
                ->value('stock_qty') ?? 0;

            $used_qty = (float)$chemical_dpt->dpt_qty - (float)$own_stock;
            if ($used_qty > 0) {
                $chemical_dpt->in_use = true;
                $chemical_dpt->used_qty = number_format($used_qty, 3, '.', '');
            } else {
                $chemical_dpt->in_use = false;
                $chemical_dpt->used_qty = 0;
            }

            return response()->json([
                'chemical_dpt'     => $chemical_dpt,
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
        $LocationData = getCurrentLocation()->location_id;
        DB::beginTransaction();
            $mpt_material_data = DPTChemical::where('dpt_id', $request->id)->first();
            // if ($mpt_material_data->current_location_id != $LocationData) {
            if ($mpt_material_data->own_location_id != $LocationData) {
                DB::rollBack();
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Unauthorized Record Can`t Update.'
                ]);
            }
        try
        {
             $year_data = getCurrentYearData();
             
             $pendingQty = DB::table('dpt_chemical_pending_qty')
                 ->where('table_pk_id', $request->table_pk_id)
                 ->value('pending_qty') ?? 0;

             $availableQty = (float)$pendingQty;
             if ($mpt_material_data->table_pk_id == $request->table_pk_id) {
                 $availableQty += (float)$mpt_material_data->dpt_qty;
             }

             if ((float)$request->dpt_qty <= 0) {
                 DB::rollBack();
                 return response()->json([
                     'response_code' => '0',
                     'response_message' => 'Enter Qty. greater than 0.',
                 ]);
             }

             $own_stock = DB::table('mpt_dpt_material_batch_wise_stock')
                 ->where('sr_table_unique_id', 'dpt_chemical')
                 ->where('sr_table_pk_id', $request->id)
                 ->where('current_location_id', $mpt_material_data->own_location_id)
                 ->value('stock_qty') ?? 0;

             $used_qty = (float)$mpt_material_data->dpt_qty - (float)$own_stock;

             if ($used_qty > 0 && (float)$request->dpt_qty < $used_qty) {
                 DB::rollBack();
                 return response()->json([
                     'response_code' => '0',
                     'response_message' => 'Qty. Cannot Be Less Than '. '.',
                 ]);
             }

             if ((float)$request->dpt_qty > $availableQty) {
                 DB::rollBack();
                 return response()->json([
                     'response_code' => '0',
                     'response_message' => 'Qty. Cannot Be Greater Than Pending Qty. ' . $availableQty . '.',
                 ]);
             }
            $name_for_display = '';
            if(!empty($request->dpt_chemical) && !empty($request->dpt_batch_no) && !empty($request->dpt_expiry_date)) {
                $name_for_display = $request->dpt_chemical.' - '.$request->dpt_batch_no.' - '.$request->dpt_expiry_date;

                if (!empty($request->grn_no)) {
                    $name_for_display .= ' - ' . $request->grn_no;
                }
            }
             $dpt_chemical = DPTChemical::where('dpt_id', $request->id)->update([

                'dpt_chemical'           => $request->dpt_chemical ?? null,
                //'dpt_designation'        => $request->dpt_designation ?? null,
                'dpt_make'               => $request->dpt_make ?? null,
                'dpt_batch_no'           => $request->dpt_batch_no ?? null,
                // 'dpt_identification_no'  => $request->dpt_identification_no ?? null,
                'dpt_expiry_date'        => isset($request->dpt_expiry_date) ? Date::createFromFormat('d/m/Y', $request->dpt_expiry_date)->format('Y-m-d') : null,
                'dpt_qty'                => $request->dpt_qty ?? null,
                // 'dpt_status'             => $request->dpt_status ?? null,
                'table_unique_id' => $request->table_unique_id ?? null,
                'table_pk_id'     => $request->table_pk_id ?? null,
                'item_id'         => $request->item_id ?? null,
                'name_for_display'      =>  $name_for_display,
                'own_location_id'       => $LocationData,
                'current_location_id'   => $LocationData,
                'company_id'            => Auth::user()->company_id,
                'last_on'               => Carbon::now('Asia/Kolkata'),
                'last_by'               => Auth::id(),
            ]);

            if($dpt_chemical)
            {
                stockEffect($LocationData, $request->item_id ?? $mpt_material_data->item_id ?? 0, $mpt_material_data->item_id ?? 0, $request->dpt_qty ?? 0, $mpt_material_data->dpt_qty ?? 0, 0, 0, 'Update', 'U', 'DPT Chemical', $request->id, 'dpt_chemical', $request->id, true);

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

        }catch(\Exception $e)
            {
                report($e);
                DB::rollBack();
                // return response()->json([
                //     'response_code' => '0',
                //     'response_message' => getResponseMessage('update_error'),
                //     'original_error' => $e->getMessage()
                // ]);
                $message = $e->getMessage();

                if (in_array($message, [
                        'Insufficient Stock',
                        'Invalid Location Code',
                        'Invalid Item'
                    ]) || str_contains($message, 'Sr. No. Is Already')
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
        try
        {
            // dd($request->all());
            $usage = DB::select('CALL dpt_chemical_used_list(?)', [$request->id]);
            if (!empty($usage)) {
                $message = "You Can't Delete, Chemical - DPT Is Used In ". $usage[0]->table_name.".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }
            $LocationData = getCurrentLocation()->location_id;
            $oldData = DPTChemical::where('dpt_id', $request->id)->first();
            if ($oldData) {
                stockEffect($oldData->current_location_id ?? $LocationData, $oldData->item_id ?? 0, $oldData->item_id ?? 0, 0, $oldData->dpt_qty ?? 0, 0, 0, 'Delete', 'U', 'DPT Chemical', $request->id, 'dpt_chemical', $request->id, true);
            }
            DPTChemical::where('dpt_id',$request->id)->delete();
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




    function getPendingGrnListForChemicalDPT(Request $request)
    {
        $itemTypes = getItemType();
        $yearIds   = getCompanyYearIdsToTill();
        $LocationData = getCurrentLocation()->location_id;
        
        $opening_data = DB::table('dpt_chemical_pending_qty')->select(['dpt_chemical_pending_qty.table_pk_id',
        'dpt_chemical_pending_qty.table_unique_id','grn_supplier.grn_number','grn_supplier.grn_date','suppliers.supplier_name','grn_supplier.grn_challan_number','grn_supplier.grn_challan_date','item.item_name','item_group.item_group','item.item_type','unit.unit','dpt_chemical_pending_qty.pending_qty',   
        DB::raw("
            CASE 
                WHEN dpt_chemical_pending_qty.table_unique_id = 'GRN' 
                    THEN grn_supplier_details.grnd_qty
                WHEN dpt_chemical_pending_qty.table_unique_id = 'Opening' 
                    THEN item_opening.io_opening_qty
                ELSE 0
            END as qty
        ")
        ])
        ->leftJoin('item_opening', function ($join) {
            $join->on('item_opening.io_id', '=', 'dpt_chemical_pending_qty.table_pk_id')
                ->where('dpt_chemical_pending_qty.table_unique_id', '=', 'Opening');
        })
        ->leftJoin('grn_supplier_details', function ($join) {
            $join->on('grn_supplier_details.grnd_id', '=', 'dpt_chemical_pending_qty.table_pk_id')
                ->where('dpt_chemical_pending_qty.table_unique_id', '=', 'GRN');
        })
        ->leftJoin('grn_supplier', 'grn_supplier.grn_id', '=', 'grn_supplier_details.grnd_grn_id')
        ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
        ->leftJoin('item', 'item.id', '=', 'dpt_chemical_pending_qty.item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->where('dpt_chemical_pending_qty.location_id',$LocationData)
        ->where('dpt_chemical_pending_qty.pending_qty', '>', 0)
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
                'opening_data' => $opening_data,
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '1',
                'opening_data' => []
            ]);
        }
    }

    public function getPendingGrnForChemicalDPT(Request $request){
        $itemTypes = getItemType();
        $LocationData = getCurrentLocation()->location_id;

        $request->table_pk_ids = explode(',', $request->table_pk_ids);
        $request->table_unique_ids = explode(',', $request->table_unique_ids);

        $data = DB::table('dpt_chemical_pending_qty')
            ->select([
                'dpt_chemical_pending_qty.table_pk_id',
                'dpt_chemical_pending_qty.table_unique_id',
                'grn_supplier.grn_number',
                'grn_supplier.grn_date',
                'suppliers.supplier_name',
                'grn_supplier.grn_challan_number',
                'grn_supplier.grn_challan_date',
                'item.item_name',
                'item_group.item_group',
                'item.item_type',
                'item.id as item_id',
                'dpt_chemical_pending_qty.pending_qty',

                DB::raw("
                    CASE 
                        WHEN dpt_chemical_pending_qty.table_unique_id = 'GRN' 
                            THEN grn_supplier_details.grnd_qty
                        WHEN dpt_chemical_pending_qty.table_unique_id = 'Opening' 
                            THEN item_opening.io_opening_qty
                        ELSE 0
                    END as qty
                ")
            ])

            ->leftJoin('item_opening', function ($join) {
                $join->on('item_opening.io_id', '=', 'dpt_chemical_pending_qty.table_pk_id')
                    ->where('dpt_chemical_pending_qty.table_unique_id', '=', 'Opening');
            })

            ->leftJoin('grn_supplier_details', function ($join) {
                $join->on('grn_supplier_details.grnd_id', '=', 'dpt_chemical_pending_qty.table_pk_id')
                    ->where('dpt_chemical_pending_qty.table_unique_id', '=', 'GRN');
            })

            ->leftJoin('grn_supplier', 'grn_supplier.grn_id', '=', 'grn_supplier_details.grnd_grn_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
            ->leftJoin('item', 'item.id', '=', 'dpt_chemical_pending_qty.item_id')
            ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')

            ->where('dpt_chemical_pending_qty.location_id', $LocationData)
            ->whereIn('dpt_chemical_pending_qty.table_pk_id', $request->table_pk_ids)
            ->whereIn('dpt_chemical_pending_qty.table_unique_id', $request->table_unique_ids)
            ->where('dpt_chemical_pending_qty.pending_qty', '>', 0)

            ->first(); // single select (as per your UI)

        if ($data != null) {

            $data->item_type = $itemTypes[$data->item_type] ?? $data->item_type;

            $data->grn_date = $data->grn_date != "" 
                ? Date::createFromFormat('Y-m-d', $data->grn_date)->format('d/m/Y') 
                : "";

            $data->grn_challan_date = $data->grn_challan_date != "" 
                ? Date::createFromFormat('Y-m-d', $data->grn_challan_date)->format('d/m/Y') 
                : "";

            return response()->json([
                'response_code' => '1',
                'opening_data' => $data,
            ]);
        }

        return response()->json([
            'response_code' => '1',
            'opening_data' => []
        ]);
    }

}