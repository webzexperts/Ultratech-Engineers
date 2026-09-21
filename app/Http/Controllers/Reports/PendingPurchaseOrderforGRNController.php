<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetails;


class PendingPurchaseOrderforGRNController extends Controller
{

    public function manage()
    {
        return view('manage.reports.manage-pend_purchase_order_for_grn');
    }

    public function index(PurchaseOrderDetails $data, Request $request, DataTables $datatables)
    {
        $yearIds = getCompanyYearIdsToTill();
        $current_location = getCurrentLocation();
      
        $data = DB::table('pending_purchase_order_qty as pend')->select([
                'purchase_order.po_type_id','pend.pod_id','purchase_order.po_id','purchase_order.po_number','purchase_order.po_sequence','purchase_order.po_date','purchase_order.po_supplier_id','suppliers.supplier_name','purchase_order.ref_no_date','bill_to.location_name as bill_to','ship_to.location_name as ship_to','item.item_name','item_group.item_group','item.item_type as main_group','purchase_order_details.pod_po_qty','unit.unit','pend.pending_qty',
                'purchase_order_details.pod_remark','admin.person_name as prepared_by','purchase_order_details.pod_del_date','purchase_order.po_sequence',
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
            ->where('pend.pending_qty', '>', 0)
            ->when($current_location->location_type != 'HO', function ($q) use ($current_location) {
                $q->where(function ($sub) use ($current_location) {
                    $sub->where('purchase_order.current_location_id', $current_location->location_id)
                        ->orWhere('purchase_order.bill_to_location_id', $current_location->location_id)
                        ->orWhere('purchase_order.ship_to_location_id', $current_location->location_id);
                });
            });
            
           
        
       

        $dataTable = DataTables::of($data)
        ->filterColumn('po.po_number', function($query, $keyword) {
            applySequenceSearch($query, $keyword, 'po.po_number');
        })
        ->editColumn('po_date', function($data){
            if ($data->po_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $data->po_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('po.po_date', function ($q, $k) {
            applyDate($q, $k, 'po.po_date');
        })
        ->editColumn('pod_del_date', function($data){
            if ($data->pod_del_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $data->pod_del_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('purchase_order_details.pod_del_date', function ($q, $k) {
            applyDate($q, $k, 'purchase_order_details.pod_del_date');
        })
        ->editColumn('pod_po_qty', function($data) {
            return $data->pod_po_qty > 0 ? number_format((float)$data->pod_po_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->editColumn('main_group', function($data) {
            return config('app.item_type')[$data->main_group] ?? $data->main_group;
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
        ->filterColumn('purchase_indent_details.pod_po_qty', function($query, $keyword) {
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
        });
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on','options'])
        ->make(true);
    }
}
