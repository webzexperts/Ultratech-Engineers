<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class POSummaryController extends Controller
{
    public function manage()
    {
        return view('manage.reports.manage-po_summary');
    }

    public function index(PurchaseOrder $po_data, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $po_data = PurchaseOrder::select([
            'purchase_order.po_number',
            'purchase_order.po_date',
            'suppliers.supplier_name',
            'item.item_name',
            'purchase_order_details.pod_description',
            'purchase_order_details.pod_po_qty',
            'unit.unit',
            'purchase_order_details.pod_rate_unit',
            'purchase_order_details.pod_amount',
            'purchase_order_details.pod_del_date',
            'purchase_order_details.pod_remark'
            
        ])

        ->leftJoin('purchase_order_details','purchase_order_details.pod_po_id','=','purchase_order.po_id')
        ->leftJoin('suppliers','suppliers.id','=','purchase_order.po_supplier_id')
        ->leftJoin('item','item.id','=','purchase_order_details.pod_item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->where('purchase_order.year_id', $year_data->id);

        if($request->from_date != "" && $request->to_date != "")
        {
            $from =  Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $to =  Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $po_data->whereDate('purchase_order.po_date','>=',$from);
            $po_data->whereDate('purchase_order.po_date','<=',$to);
        }
        else if($request->from_date != "")
        {
            $from =  Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $po_data->where('purchase_order.po_date','>=',$from);
        }
        else if($request->to_date != "")
        {
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
        return $dataTable
        ->make(true);
    }
}
