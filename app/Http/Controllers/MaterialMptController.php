<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\GRN;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use App\Models\MaterialMpt;
use App\Models\ItemOpening;

class MaterialMptController extends Controller
{
    public function manage()
    {
        return view('manage.manage-material_mpt');
    }

    public function index(MaterialMpt $material_mpt, Request $request, DataTables $datatables)
    {
        $LocationData = getCurrentLocation();
        $material_mpt = MaterialMpt::select([
            'material_mpt.mm_id',
            'material_mpt.mm_material',
            'material_mpt.mm_material_make',
            'material_mpt.table_unique_id',
            'material_mpt.table_pk_id',
            'material_mpt.mm_batch_no',
            'material_mpt.mm_expiry_date',
            //'material_mpt.mm_identification_no',
            'material_mpt.current_location_id',
            'material_mpt.mm_status',
            'material_mpt.created_on',
            'material_mpt.created_by',
            'material_mpt.last_by',
            'material_mpt.last_on',
            DB::raw("
                COALESCE(
                    (
                        SELECT GROUP_CONCAT(CONCAT(loc.location_name) SEPARATOR ', ')
                        FROM mpt_dpt_material_batch_wise_stock stk
                        JOIN location loc ON loc.location_id = stk.current_location_id
                        WHERE stk.sr_table_unique_id = 'mpt_material'
                          AND stk.sr_table_pk_id = material_mpt.mm_id
                          AND stk.stock_qty > 0
                    ),
                    ''
                ) as current_location
            "),
            'own_location.location_name as own_location',
        ])
        ->leftJoin('location as current_location','current_location.location_id', '=','material_mpt.current_location_id')
        // ->leftJoin('mpt_dpt_material_batch_wise_stock','mpt_dpt_material_batch_wise_stock.sr_table_pk_id', '=','material_mpt.mm_id')
        ->leftJoin('location as own_location','own_location.location_id', '=','material_mpt.own_location_id');

        if ($LocationData->location_type != 'HO') {
            $material_mpt->where(function($query) use ($LocationData) {
                $query->where('material_mpt.current_location_id', $LocationData->location_id)
                ->orWhere('material_mpt.own_location_id', $LocationData->location_id)
                ->orWhereExists(function($q) use ($LocationData) {
                    $q->select(DB::raw(1))
                      ->from('mpt_dpt_material_batch_wise_stock as stk')
                      ->whereColumn('stk.sr_table_pk_id', 'material_mpt.mm_id')
                      ->where('stk.sr_table_unique_id', 'mpt_material')
                      ->where('stk.current_location_id', $LocationData->location_id)
                      ->where('stk.stock_qty', '>', 0);
                });
            });
        }
         
        

        $dataTable = DataTables::of($material_mpt)

        ->editColumn('mm_expiry_date', function($material_mpt){
            if ($material_mpt->mm_expiry_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $material_mpt->mm_expiry_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('material_mpt.mm_expiry_date', function ($q, $k) {
            applyDate($q, $k, 'material_mpt.mm_expiry_date');
        })
        ->filterColumn('current_location.location_name', function ($query, $keyword) {
            $query->whereRaw("
                EXISTS (
                    SELECT 1 FROM mpt_dpt_material_batch_wise_stock stk
                    JOIN location loc ON loc.location_id = stk.current_location_id
                    WHERE stk.sr_table_unique_id = 'mpt_material'
                      AND stk.sr_table_pk_id = material_mpt.mm_id
                      AND stk.stock_qty > 0
                      AND loc.location_name LIKE ?
                )
            ", ["%{$keyword}%"]);
        })
        
        ->filterColumn('material_mpt.mm_status', function ($query, $keyword) {
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
                $query->where('material_mpt.mm_status', '=', $searchStatus);
            }
            else {
                $dbFormatKeyword = str_replace(' ', '_', $lowerKeyword);
                $query->where('material_mpt.mm_status', 'like', "$dbFormatKeyword%");
            }
        })
        ->addColumn('options',function($material_mpt) use ($LocationData){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("material_mpt", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-material_mpt"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("material_mpt", "delete")){
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'material_mpt');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        
        $LocationData = getCurrentLocation()->location_id;
        $year_data = getCurrentYearData();
        DB::beginTransaction();
        try
        {

            $pendingQty =  DB::table('mpt_material_pending_qty')
                ->where('table_pk_id', $request->table_pk_id)
                ->value('pending_qty') ?? 0;

            if((float)$request->mm_qty <= 0)
            {
                DB::rollBack();
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Enter Quantity greater than 0.',
                ]);
            }

            if((float)$pendingQty == 0 || (float)$request->mm_qty > (float)$pendingQty)
            {
                DB::rollBack();
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Qty. Cannot Be Greater Than Pending Qty ' . $pendingQty . '.',
                ]);
            }


            $name_for_display = '';
            if(!empty($request->mm_material) && !empty($request->mm_batch_no)  && !empty($request->mm_material_make)) {
                $name_for_display = $request->mm_material.' - '.$request->mm_batch_no.' - '.$request->mm_material_make;
                
                if (!empty($request->grn_no)) {
                    $name_for_display .= ' - ' . $request->grn_no;
                }
            }
            $material_mpt =  MaterialMpt::create([

                'mm_material'           => $request->mm_material ?? null,
                'mm_material_make'      => $request->mm_material_make ?? null,
                'mm_batch_no'           => $request->mm_batch_no ?? null,
                //'mm_identification_no'  => $request->mm_identification_no ?? null,
                'mm_expiry_date'           => isset($request->mm_expiry_date) ? Date::createFromFormat('d/m/Y', $request->mm_expiry_date)->format('Y-m-d') : null,
                'mm_qty'                => $request->mm_qty ?? null,
                'mm_status'             => $request->mm_status ?? null,
                'table_unique_id' => $request->table_unique_id ?? null,
                'table_pk_id' => $request->table_pk_id ?? null,
                'item_id' => $request->item_id ?? null,
                'own_location_id' => $LocationData,
                'current_location_id'   => $LocationData,
                'name_for_display'      =>  $name_for_display,
                'company_id'            => Auth::user()->company_id,
                'created_on'            => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'            => Auth::user()->id,
            ]);

            if($material_mpt->save())
            {
                stockEffect($LocationData,$material_mpt->item_id ?? 0,$material_mpt->item_id ?? 0,$request->mm_qty ?? 0,0,0,0,'Insert','U','Material MPT',$material_mpt->mm_id,'mpt_material',$material_mpt->mm_id,true);

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
        // $material_mpt = MaterialMpt::select(['material_mpt.*','grn.grn_number','grn.grn_date','grn.grn_challan_number','grn.grn_challan_date','suppliers.supplier_name as grn_supplier_name'])
        // ->leftJoin('grn_details','grn_details.grnd_id','=','material_mpt.mm_grnd_id')
        // ->leftJoin('grn','grn.grn_id','=','grn_details.grnd_grn_id')
        // ->leftJoin('suppliers','suppliers.id','=','grn.grn_supplier_id')  
        // ->where('material_mpt.mm_id','=',$request->id)->first();
        //$check_item = Item::where('item.id',$material_mpt->mm_material_id)->value('item.status');

         $itemTypes = getItemType();
        $LocationData = getCurrentLocation()->location_id;
        $material_mpt = DB::select('CALL mpt_material_master(?)', [$request->id]);

        if(!empty($material_mpt))
        {
            $material_mpt = $material_mpt[0];
            
            if(!empty($material_mpt->mm_expiry_date))
            {
                $material_mpt->mm_expiry_date = Carbon::createFromFormat('Y-m-d', $material_mpt->mm_expiry_date)->format('d/m/Y');
            }
            if(!empty($material_mpt->grn_challan_date))
            {
                $material_mpt->grn_challan_date = Carbon::createFromFormat('Y-m-d', $material_mpt->grn_challan_date)->format('d/m/Y');
            }
            if(!empty($material_mpt->grn_date))
            {
                $material_mpt->grn_date = Carbon::createFromFormat('Y-m-d', $material_mpt->grn_date)->format('d/m/Y');
            }

            $material_mpt->login_location = $LocationData; 
            $material_mpt->main_group = $itemTypes[$material_mpt->main_group] ?? $material_mpt->main_group;
          
            $own_stock = DB::table('mpt_dpt_material_batch_wise_stock')
                ->where('sr_table_unique_id', 'mpt_material')
                ->where('sr_table_pk_id', $request->id)
                ->where('current_location_id', $material_mpt->own_location_id)
                ->value('stock_qty') ?? 0;

            $used_qty = (float)$material_mpt->mm_qty - (float)$own_stock;
            if ($used_qty > 0) {
                $material_mpt->in_use = true;
                $material_mpt->used_qty = number_format($used_qty, 3, '.', '');
            } else {
                $material_mpt->in_use = false;
                $material_mpt->used_qty = 0;
            }

            return response()->json([
                'material_mpt'     => $material_mpt,
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
            $mpt_material_data = MaterialMpt::where('mm_id', $request->id)->first();
            // if ($mpt_material_data->current_location_id != $LocationData) {
            if ($mpt_material_data->own_location_id != $LocationData) {
                DB::rollBack();
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Unauthorized Record Can`t Update.',
                ]);
            }
        try
        {
            $year_data = getCurrentYearData();

             $pendingQty = DB::table('mpt_material_pending_qty')
                 ->where('table_pk_id', $request->table_pk_id)
                 ->value('pending_qty') ?? 0;

             $availableQty = (float)$pendingQty;
             $availableQty += (float)$mpt_material_data->mm_qty;

             if ((float)$request->mm_qty <= 0) {
                 DB::rollBack();
                 return response()->json([
                     'response_code' => '0',
                     'response_message' => 'Enter Quantity greater than 0.',
                 ]);
             }

             $own_stock = DB::table('mpt_dpt_material_batch_wise_stock')
                 ->where('sr_table_unique_id', 'mpt_material')
                 ->where('sr_table_pk_id', $request->id)
                 ->where('current_location_id', $mpt_material_data->own_location_id)
                 ->value('stock_qty') ?? 0;

             $used_qty = (float)$mpt_material_data->mm_qty - (float)$own_stock;

             if ($used_qty > 0 && (float)$request->mm_qty < $used_qty) {
                 DB::rollBack();
                 return response()->json([
                     'response_code' => '0',
                     'response_message' => 'Qty. Cannot Be Less Than ' . '.',
                 ]);
             }

             if ((float)$request->mm_qty > $availableQty) {
                 DB::rollBack();
                 return response()->json([
                     'response_code' => '0',
                     'response_message' => 'Qty. Cannot Be Greater Than Pending Qty ' . $availableQty . '.',
                 ]);
             }
            $name_for_display = '';
            if(!empty($request->mm_material) && !empty($request->mm_batch_no)  && !empty($request->mm_material_make)) {
                $name_for_display = $request->mm_material.' - '.$request->mm_batch_no.' - '.$request->mm_material_make;
                if (!empty($request->grn_no)) {
                    $name_for_display .= ' - ' . $request->grn_no;
                }
            } 
            // dd($request->grn_no);
            // dd($name_for_display);
             $material_mpt = MaterialMpt::where('mm_id', $request->id)->update([

                'mm_material'           => $request->mm_material ?? null,
                'mm_material_make'      => $request->mm_material_make ?? null,
                'mm_batch_no'           => $request->mm_batch_no ?? null,
                //'mm_identification_no'  => $request->mm_identification_no ?? null,
                //'mm_expiry_date'           => $request->mm_expiry_date ?? null,
                'mm_expiry_date'           => isset($request->mm_expiry_date) ? Date::createFromFormat('d/m/Y', $request->mm_expiry_date)->format('Y-m-d') : null,
                'mm_qty'                   => $request->mm_qty ?? null,
                // 'mm_status'             => $request->mm_status ?? null,
                'table_unique_id' => $request->table_unique_id ?? null,
                'table_pk_id' => $request->table_pk_id ?? null,
                'item_id' => $request->item_id ?? null,
                'name_for_display'      =>  $name_for_display,
                'own_location_id'     => $LocationData,
                'current_location_id' => $LocationData,
                'company_id'            => Auth::user()->company_id,
                'last_on'               => Carbon::now('Asia/Kolkata'),
                'last_by'               => Auth::id(),
            ]);

            if($material_mpt)
            {
                stockEffect( $LocationData, $request->item_id ?? $mpt_material_data->item_id ?? 0, $mpt_material_data->item_id ?? 0, $request->mm_qty ?? 0, $mpt_material_data->mm_qty ?? 0, 0, 0, 'Update', 'U', 'Material MPT', $request->id, 'mpt_material', $request->id,true);

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
            $usage = DB::select('CALL material_mpt_used_list(?)', [$request->id]);
            if (!empty($usage)) {
                $message = "You Can't Delete, Material - MPT Is Used In ". $usage[0]->table_name.".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }
            $LocationData = getCurrentLocation()->location_id;
            $oldData = MaterialMpt::where('mm_id', $request->id)->first();
            if ($oldData) {
                stockEffect( $oldData->current_location_id ?? $LocationData, $oldData->item_id ?? 0, $oldData->item_id ?? 0, 0, $oldData->mm_qty ?? 0, 0, 0, 'Delete', 'U', 'Material MPT', $request->id, 'mpt_material', $request->id,true);
            }
            MaterialMpt::where('mm_id',$request->id)->delete();
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

    // suggest size of probe
    public function existsMake(Request $request)
    {
        if($request->term != "")
        {
            $fdCountry = MaterialMpt::select('mm_material_make')->where('mm_material_make', 'LIKE', $request->term.'%')->groupBy('mm_material_make')->get();
            if($fdCountry != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdCountry as $dsKey)
                {
                    $output .= '<li parent-id="mm_material_make" list-id="mm_material_make_list" class="list-group-item" tabindex="0">'.$dsKey->mm_material_make.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'makeList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Make Available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'makeList' => '',
                'response_code' => 1,
            ]);
        }
    }

    public function materialMptLNRData()
    {
        $lnr_data = MaterialMpt::select('mm_id','mm_inward_type')->orderBy('mm_id','desc')->first();
            return response()->json([
            'response_code' => 1,
            'lnr_data'       => $lnr_data,
        ]);
    }

    function getPendingGrnListForMaterialMpt(Request $request){
        $itemTypes = getItemType();
        $yearIds   = getCompanyYearIdsToTill();
        $LocationData = getCurrentLocation()->location_id;
        
        $opening_data = DB::table('mpt_material_pending_qty')->select(['mpt_material_pending_qty.table_pk_id',
        'mpt_material_pending_qty.table_unique_id','grn_supplier.grn_number','grn_supplier.grn_date','suppliers.supplier_name','grn_supplier.grn_challan_number','grn_supplier.grn_challan_date','item.item_name','item_group.item_group','item.item_type','unit.unit','mpt_material_pending_qty.pending_qty',   
        DB::raw("
            CASE 
                WHEN mpt_material_pending_qty.table_unique_id = 'GRN' 
                    THEN grn_supplier_details.grnd_qty
                WHEN mpt_material_pending_qty.table_unique_id = 'Opening' 
                    THEN item_opening.io_opening_qty
                ELSE 0
            END as qty
        ")
        ])
        ->leftJoin('item_opening', function ($join) {
            $join->on('item_opening.io_id', '=', 'mpt_material_pending_qty.table_pk_id')
                ->where('mpt_material_pending_qty.table_unique_id', '=', 'Opening');
        })
        ->leftJoin('grn_supplier_details', function ($join) {
            $join->on('grn_supplier_details.grnd_id', '=', 'mpt_material_pending_qty.table_pk_id')
                ->where('mpt_material_pending_qty.table_unique_id', '=', 'GRN');
        })
        ->leftJoin('grn_supplier', 'grn_supplier.grn_id', '=', 'grn_supplier_details.grnd_grn_id')
        ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
        ->leftJoin('item', 'item.id', '=', 'mpt_material_pending_qty.item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->where('mpt_material_pending_qty.location_id',$LocationData)
        ->where('mpt_material_pending_qty.pending_qty', '>', 0)
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

    public function getPendingGrnForMaterialMpt(Request $request){
        $itemTypes = getItemType();
        $LocationData = getCurrentLocation()->location_id;

        $request->table_pk_ids = explode(',', $request->table_pk_ids);
        $request->table_unique_ids = explode(',', $request->table_unique_ids);

        $data = DB::table('mpt_material_pending_qty')
            ->select([
                'mpt_material_pending_qty.table_pk_id',
                'mpt_material_pending_qty.table_unique_id',
                'grn_supplier.grn_number',
                'grn_supplier.grn_date',
                'suppliers.supplier_name',
                'grn_supplier.grn_challan_number',
                'grn_supplier.grn_challan_date',
                'item.item_name',
                'item_group.item_group',
                'item.item_type',
                'item.id as item_id',
                'mpt_material_pending_qty.pending_qty',

                DB::raw("
                    CASE 
                        WHEN mpt_material_pending_qty.table_unique_id = 'GRN' 
                            THEN grn_supplier_details.grnd_qty
                        WHEN mpt_material_pending_qty.table_unique_id = 'Opening' 
                            THEN item_opening.io_opening_qty
                        ELSE 0
                    END as qty
                ")
            ])

            ->leftJoin('item_opening', function ($join) {
                $join->on('item_opening.io_id', '=', 'mpt_material_pending_qty.table_pk_id')
                    ->where('mpt_material_pending_qty.table_unique_id', '=', 'Opening');
            })

            ->leftJoin('grn_supplier_details', function ($join) {
                $join->on('grn_supplier_details.grnd_id', '=', 'mpt_material_pending_qty.table_pk_id')
                    ->where('mpt_material_pending_qty.table_unique_id', '=', 'GRN');
            })

            ->leftJoin('grn_supplier', 'grn_supplier.grn_id', '=', 'grn_supplier_details.grnd_grn_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
            ->leftJoin('item', 'item.id', '=', 'mpt_material_pending_qty.item_id')
            ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')

            ->where('mpt_material_pending_qty.location_id', $LocationData)
            ->whereIn('mpt_material_pending_qty.table_pk_id', $request->table_pk_ids)
            ->whereIn('mpt_material_pending_qty.table_unique_id', $request->table_unique_ids)
            ->where('mpt_material_pending_qty.pending_qty', '>', 0)

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