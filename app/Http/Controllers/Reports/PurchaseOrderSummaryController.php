<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetails;
use App\Models\GRNDetails;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\File;
use App\Models\POShortClose;
use App\Models\Supplier;
use App\Models\PurchaseIndentDetails;
use App\Models\SupplierDetails;

class PurchaseOrderSummaryController extends Controller
{
    public function manage()
    {
        return view('manage.reports.manage-purchase_order_summary');
    }

        public function index(PurchaseOrder $po_data, Request $request, DataTables $datatables)
    {
        $current_location_id = getCurrentLocation()->location_id;
        $po_data = PurchaseOrder::select([
            'purchase_order.po_type_id',
            'purchase_order.po_sequence',
            'purchase_order.po_id',
            'purchase_order.po_number',
            'purchase_order.po_date',
            'purchase_order.po_supplier_id',
            'suppliers.supplier_name',
            'suppliers.email_id as supplier_email',
            'purchase_order.ref_no_date',
            'purchase_order.bill_to_location_id',
            'bill_to_location.location_name as bill_to_location_name',
            'purchase_order.ship_to_location_id',
            'ship_to_location.location_name as ship_to_location_name',
            'purchase_order_details.pod_item_id',
            'item.item_name',
            'item_group.item_group',
            'item.item_type as main_group',
            'purchase_order_details.pod_po_qty',
            'unit.unit',
            'purchase_order_details.pod_rate_unit',
            'purchase_order_details.pod_amount',
            'purchase_order_details.pod_del_date',
            'purchase_order_details.pod_remark',
            'admin.person_name',
            'purchase_order.po_sp_note',
            'purchase_order.created_on',
            'purchase_order.created_by',
            'purchase_order.last_by',
            'purchase_order.last_on',
        ])

        ->leftJoin('purchase_order_details','purchase_order_details.pod_po_id','=','purchase_order.po_id')
        ->leftJoin('suppliers','suppliers.id','=','purchase_order.po_supplier_id')
        ->leftJoin('location as bill_to_location','bill_to_location.location_id','=','purchase_order.bill_to_location_id')
        ->leftJoin('location as ship_to_location','ship_to_location.location_id','=','purchase_order.ship_to_location_id')
        ->leftJoin('item','item.id','=','purchase_order_details.pod_item_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('admin','admin.id','=','purchase_order.prepared_by_user_id')
        ->where("purchase_order.current_location_id", $current_location_id);
         if($request->from_date != "" && $request->to_date != "") {
            $from =  Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $to =  Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $po_data->whereDate('purchase_order.po_date','>=',$from);
            $po_data->whereDate('purchase_order.po_date','<=',$to);
        } else if($request->from_date != "") {
            $from =  Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $po_data->where('purchase_order.po_date','>=',$from);
        } else if($request->to_date != "") {
            $to =  Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $po_data->where('purchase_order.po_date','<=',$to);
        }

        $dataTable = DataTables::of($po_data)
        ->editColumn('po_date', function($po_data){
            if ($po_data->po_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $po_data->po_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('purchase_order.po_date', function ($q, $k) {
            applyDate($q, $k, 'purchase_order.po_date');
        })
        ->editColumn('pod_del_date', function($po_data){
            if ($po_data->pod_del_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $po_data->pod_del_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })

        ->editColumn('main_group', function($po_data) {
            return config('app.item_type')[$po_data->main_group] ?? $po_data->main_group;
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

        ->filterColumn('purchase_order_details.pod_del_date', function ($q, $k) {
            applyDate($q, $k, 'purchase_order_details.pod_del_date');
        })

         ->editColumn('pod_po_qty', function($po_data) {
            return $po_data->pod_po_qty > 0 ? number_format((float)$po_data->pod_po_qty, 3, '.','') : number_format((float) 0, 3, '.','');
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
        ->editColumn('pod_rate_unit', function($po_data) {
            return $po_data->pod_rate_unit > 0 ? number_format((float)$po_data->pod_rate_unit, 2, '.','') : number_format((float) 0, 2, '.','');
        })
        ->filterColumn('purchase_order_details.pod_rate_unit', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,2})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('pod_rate_unit')
                    ->orWhere('pod_rate_unit', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(pod_rate_unit AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('pod_rate_unit', 'like', "%{$search}%");
            }
        })
        
        ->editColumn('pod_amount', function($po_data) {
            return $po_data->pod_amount > 0 ? number_format((float)$po_data->pod_amount, 2, '.','') : number_format((float) 0, 2, '.','');
        })
        ->filterColumn('purchase_order_details.pod_amount', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,2})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('pod_amount')
                    ->orWhere('pod_amount', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(pod_amount AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('pod_amount', 'like', "%{$search}%");
            }
        })
        ->filterColumn('purchase_order.po_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'purchase_order.po_number');
        });
        // $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'purchase_order');
        return $dataTable
        ->rawColumns(['last_by','last_on','created_by','created_on'])
        ->make(true);
    }
}
