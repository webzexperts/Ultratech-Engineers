<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\PurchaseOrder;
use App\Models\POShortClose;
use App\Models\PurchaseOrderDetails;


class POShortCloseController extends Controller
{
    public function manage()
    {
        return view('manage.manage-po_short_close');
    }

    public function index(POShortClose $po_sc_data, Request $request, DataTables $datatables)
    {
          $current_location_id = getCurrentLocation();
        $year_data = getCurrentYearData();
        $po_sc_data = DB::table('pending_purchase_order_qty as pend')->select([
                'purchase_order.po_type_id','purchase_order_details.pod_id','purchase_order.po_id','purchase_order.po_number','purchase_order.po_date','purchase_order.po_supplier_id','suppliers.supplier_name','purchase_order.ref_no_date','bill_to.location_name as bill_to','ship_to.location_name as ship_to','item.item_name','item_group.item_group','item.item_type as main_group','purchase_order_details.pod_po_qty','unit.unit', 'pend.pending_qty',
                'purchase_order_details.pod_remark','admin.person_name as prepared_by','purchase_order_details.pod_del_date','reason.reason_name','purchase_order.po_sequence','po_short_close.po_sc_id','po_short_close.po_sc_date','po_short_close.po_sc_qty', 'po_short_close.created_on',
            'po_short_close.created_by',
            'po_short_close.last_by',
            'po_short_close.last_on',
            ])
            ->leftJoin('po_short_close', 'po_short_close.po_sc_pod_id', '=', 'pend.pod_id')
            ->leftJoin('purchase_order_details', 'purchase_order_details.pod_id', '=', 'po_short_close.po_sc_pod_id')
            ->leftJoin('purchase_order', 'purchase_order.po_id', '=', 'purchase_order_details.pod_po_id')
            ->leftJoin('item', 'item.id', '=', 'purchase_order_details.pod_item_id')
            ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
            ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
            ->leftJoin('admin', 'admin.id', '=', 'purchase_order.prepared_by_user_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_order.po_supplier_id')
            ->leftJoin('reason', 'reason.id', '=', 'po_short_close.po_sc_regret_reason_id')
            ->leftJoin('location as bill_to', 'bill_to.location_id', '=', 'purchase_order.bill_to_location_id')
            ->leftJoin('location as ship_to', 'ship_to.location_id', '=', 'purchase_order.ship_to_location_id')
            ->where('po_short_close.year_id', $year_data->id);
            if ($current_location_id->location_type != 'HO') {
            $po_sc_data->where(function($q) use ($current_location_id) {
                $q->where('purchase_order.current_location_id', $current_location_id->location_id);
               
            });
        };

        $dataTable = DataTables::of($po_sc_data)
        
        ->editColumn('po_date', function($po_sc_data){
            if ($po_sc_data->po_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $po_sc_data->po_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('purchase_order.po_date', function ($q, $k) {
            applyDate($q, $k, 'purchase_order.po_date');
        })
        ->editColumn('pod_del_date', function($po_sc_data){
            if ($po_sc_data->pod_del_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $po_sc_data->pod_del_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('purchase_order_details.pod_del_date', function ($q, $k) {
            applyDate($q, $k, 'purchase_order_details.pod_del_date');
        })

        ->editColumn('po_sc_date', function($po_sc_data){
            if ($po_sc_data->po_sc_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $po_sc_data->po_sc_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('po_short_close.po_sc_date', function ($q, $k) {
            applyDate($q, $k, 'po_short_close.po_sc_date');
        })

        ->editColumn('po_sc_qty', function($po_sc_data) {
            return $po_sc_data->po_sc_qty > 0 ? number_format((float)$po_sc_data->po_sc_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
         ->filterColumn('po_short_close.po_sc_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('po_sc_qty')
                    ->orWhere('po_sc_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(po_sc_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('po_sc_qty', 'like', "%{$search}%");
            }
        })
        ->editColumn('pod_po_qty', function($po_sc_data) {
            return $po_sc_data->pod_po_qty > 0 ? number_format((float)$po_sc_data->pod_po_qty, 3, '.','') : number_format((float) 0, 3, '.','');
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
        ->editColumn('pending_qty', function($po_sc_data) {
            return $po_sc_data->pending_qty > 0 ? number_format((float)$po_sc_data->pending_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->editColumn('main_group', function($po_sc_data) {
            return config('app.item_type')[$po_sc_data->main_group] ?? $po_sc_data->main_group;
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


       
        
        ->addColumn('options',function($po_sc_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("po_short_close", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'po_short_close');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_sc_date' => 'required',
        ],
        [
            'po_sc_date.required' => 'Please Enter PO Short Close Date',
        ]);

        $year_data = getCurrentYearData();
        DB::beginTransaction();
        try
        {
            $request->po_sc_data = json_decode($request->po_sc_data,true);
            if(isset($request->po_sc_data) && !empty($request->po_sc_data))
            {
                foreach($request->po_sc_data as $ctKey => $ctVal)
                {

                    if($ctVal != null)
                    {
                        $pendingQty =  DB::table('pending_purchase_order_qty')
                        ->where('pod_id', $ctVal['pod_id'])
                        ->value('pending_qty');

                        if((float)$ctVal['po_sc_qty'] > (float)$pendingQty)
                        {
                            DB::rollBack();
                            return response()->json([
                                'response_code' => '0',
                                'response_message' => 'Purchase Order Qty. Used.',
                            ]);
                        }


                        $po_sc_data =  POShortClose::create([

                            'po_sc_pod_id'=> (isset($ctVal['pod_id']) &&  $ctVal['pod_id'] != "") ? $ctVal['pod_id'] : null,

                            'po_sc_date'=> Date::createFromFormat('d/m/Y', $request->po_sc_date)->format('Y-m-d'),

                            'po_sc_qty'=>(isset($ctVal['po_sc_qty']) && $ctVal['po_sc_qty'] > 0) ? $ctVal['po_sc_qty'] : null,

                            'po_sc_regret_reason_id' => (isset($ctVal['po_sc_regret_reason_id']) && $ctVal['po_sc_regret_reason_id'] != "") ? $ctVal['po_sc_regret_reason_id'] : null,

                            'year_id' => $year_data->id,

                            'company_id' => Auth::user()->company_id,

                            'created_by' => Auth::user()->id,

                            'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                        ]);
                    }
                }
                DB::commit();
                return response()->json([
                    'response_code' => '1',
                    'response_message' => getResponseMessage('store_success'),
                ]);
            }
            else
            {
                DB::rollBack();
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

    public function destroy(Request $request)
    {
        try
        {
            POShortClose::where('po_sc_id',$request->id)->delete();
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

    public function getPendingPoSC(Request $request)
    {
        $current_location = getCurrentLocation();
        $current_location_id = $current_location->location_id;
        $location_type = $current_location->location_type;
        $yearIds  = getCompanyYearIdsToTill();
        $data = DB::table('pending_purchase_order_qty as pend')->select([
                'purchase_order.po_type_id','purchase_order_details.pod_id','purchase_order.po_id','purchase_order.po_number','purchase_order.po_date','purchase_order.po_supplier_id','suppliers.supplier_name','purchase_order.ref_no_date','bill_to.location_name as bill_to','ship_to.location_name as ship_to','item.item_name','item_group.item_group','item.item_type as main_group','purchase_order_details.pod_po_qty','unit.unit', 'pend.pending_qty',
                'purchase_order_details.pod_remark','admin.person_name as prepared_by','purchase_order_details.pod_del_date'
            ])
            ->leftJoin('purchase_order_details', 'purchase_order_details.pod_id', '=', 'pend.pod_id')
            ->leftJoin('purchase_order', 'purchase_order.po_id', '=', 'purchase_order_details.pod_po_id')
            ->leftJoin('item', 'item.id', '=', 'purchase_order_details.pod_item_id')
            ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
            ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
            ->leftJoin('admin', 'admin.id', '=', 'purchase_order.prepared_by_user_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_order.po_supplier_id')
            ->leftJoin('location as bill_to', 'bill_to.location_id', '=', 'purchase_order.bill_to_location_id')
            ->leftJoin('location as ship_to', 'ship_to.location_id', '=', 'purchase_order.ship_to_location_id')
            ->whereIn('purchase_order.year_id', $yearIds)
            ->where('pending_qty', '>', 0)
            ->when($location_type != 'HO', function ($q) use ($current_location_id) {
                $q->where(function ($sub) use ($current_location_id) {
                    $sub->where('purchase_order.current_location_id', $current_location_id);
                });
            });
            $data = $data->get();


        $data = $data->map(function ($row) {

            if ($row->po_date) {
                $row->po_date = \Carbon\Carbon::parse($row->po_date)->format('d/m/Y');
            }

            if ($row->pod_del_date) {
                $row->pod_del_date = \Carbon\Carbon::parse($row->pod_del_date)->format('d/m/Y');
            }

            $row->main_group = config('app.item_type.' . ($row->main_group ?? '')) ?? '';

            return $row;
        });

        return response()->json([
            'response_code' => $data->count() > 0 ? '1' : '0',
            'po_sc_data' => $data
        ]);
    }
}