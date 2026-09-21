<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use App\Models\Admin;
use App\Models\GRNDetails;
use Yajra\DataTables\DataTables;


class GRNSummaryController extends Controller
{
    public function manage()
    {
        return view('manage.reports.manage-grn_summary');
    }

    public function index(GRNDetails $grn_data, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $grn_data = GRNDetails::select([
            'grn.grn_type_id',
            'grn.grn_number',
            'grn.grn_date',
            'suppliers.supplier_name',
            'grn.grn_challan_number',
            'grn.grn_challan_date',
            'item.item_name',
            'grn_details.grnd_description',
            'purchase_order.po_number',
            'purchase_order.po_date',
            'purchase_order_details.pod_po_qty',
            'grn_details.grnd_qty',
            'unit.unit',
            'grn_details.grnd_rate_unit',
            'grn_details.grnd_amount',
            'grn_details.grnd_remark'
           
        ])
      
        ->leftJoin('grn','grn.grn_id','=','grn_details.grnd_grn_id')
        ->leftJoin('purchase_order_details','purchase_order_details.pod_id','=','grn_details.grnd_pod_id')
        ->leftJoin('purchase_order','purchase_order.po_id','=','purchase_order_details.pod_po_id')

        ->leftJoin('suppliers','suppliers.id','=','grn.grn_supplier_id')
        ->leftJoin('item','item.id','=','grn_details.grnd_item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->where('grn.year_id', $year_data->id);
        
        if($request->from_date != "" && $request->to_date != ""){

            $from =  Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');

            $to =  Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');

            $grn_data->whereDate('grn.grn_date','>=',$from);
            $grn_data->whereDate('grn.grn_date','<=',$to);

        }else if($request->from_date != ""){

            $from =  Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');

            $grn_data->where('grn.grn_date','>=',$from);

        }else if($request->to_date != ""){

            $to =  Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');

            $grn_data->where('grn.grn_date','<=',$to);

        };

        $dataTable = DataTables::of($grn_data)
        ->filterColumn('grn.grn_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'grn.grn_number');
        })
        ->editColumn('grn_date', function($grn_data){
            if ($grn_data->grn_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $grn_data->grn_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
         ->filterColumn('grn.grn_date', function ($q, $k) {
            applyDate($q, $k, 'grn.grn_date');
        })

        ->filterColumn('purchase_order.po_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'purchase_order.po_number');
        })
     
        ->editColumn('po_date', function($grn_data){
            if ($grn_data->po_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $grn_data->po_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('purchase_order.po_date', function ($q, $k) {
            applyDate($q, $k, 'purchase_order.po_date');
        })
       
        ->editColumn('grn_challan_date', function($grn_data){
            if ($grn_data->grn_challan_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $grn_data->grn_challan_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('grn.grn_challan_date', function ($q, $k) {
            applyDate($q, $k, 'grn.grn_challan_date');
        })

         ->editColumn('pod_po_qty', function($inquiry_data) {
            return $inquiry_data->pod_po_qty > 0 ? number_format((float)$inquiry_data->pod_po_qty, 3, '.','') : '';
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

        ->editColumn('grnd_qty', function($inquiry_data) {
            return $inquiry_data->grnd_qty > 0 ? number_format((float)$inquiry_data->grnd_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('grn_details.grnd_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('grnd_qty')
                    ->orWhere('grnd_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(grnd_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('grnd_qty', 'like', "%{$search}%");
            }
        })
        ->editColumn('grnd_rate_unit', function($inquiry_data) {
            return $inquiry_data->grnd_rate_unit > 0 ? number_format((float)$inquiry_data->grnd_rate_unit, 2, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('grn_details.grnd_rate_unit', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('grnd_rate_unit')
                    ->orWhere('grnd_rate_unit', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(grnd_rate_unit AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('grnd_rate_unit', 'like', "%{$search}%");
            }
        })
        ->editColumn('grnd_amount', function($inquiry_data) {
            return $inquiry_data->grnd_amount > 0 ? number_format((float)$inquiry_data->grnd_amount, 2, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('grn_details.grnd_amount', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('grnd_amount')
                    ->orWhere('grnd_amount', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(grnd_amount AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('grnd_amount', 'like', "%{$search}%");
            }
        });
        return $dataTable
        ->make(true);
    }
}
