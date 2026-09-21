<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Transaction\GRNSupplier;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use App\Models\Admin;
use App\Models\Transaction\GRNSupplierDetails;
use App\Models\Item;
use App\Models\MaterialMpt;
use App\Models\ProbeUT;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetails;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use App\Models\EquipmentMPT;
use App\Models\LPTChemical;
use App\Models\EquipmentUT;



class GRNSupplierSummaryController extends Controller
{
    public function manage()
    {
        return view('manage.reports.manage-grn_supplier_summary');
    }

    public function index(GRNSupplier $grn_sup_data, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $current_location = getCurrentLocation()->location_id;
        $grn_sup_data = GRNSupplier::select([
            'grn_supplier.grn_id',
            'grn_supplier.grn_type_id',
            'grn_supplier.grn_sequence',
            'grn_supplier.grn_number',
            'grn_supplier.grn_date',
            'grn_supplier.grn_supplier_id',
            'suppliers.supplier_name',
            'grn_supplier.grn_challan_number',
            'grn_supplier.grn_challan_date',
            'grn_supplier_details.table_unique_id',
            'grn_supplier_details.table_pk_id',
            \DB::raw("
            CASE 
            WHEN grn_supplier_details.table_unique_id = 'Against PO' THEN po.po_number
            WHEN grn_supplier_details.table_unique_id = 'Against DC' THEN dc.sup_dc_number
                    ELSE ''
                END as ref_number
            "),\DB::raw(" CASE 
                    WHEN grn_supplier_details.table_unique_id = 'Against PO' THEN po.po_date
                    WHEN grn_supplier_details.table_unique_id = 'Against DC' THEN dc.sup_dc_date
                    ELSE NULL
                    END as ref_date
            "),
            'sr.name_for_display',
            'item.item_name',
            'item_group.item_group',
            'item.item_type',
            'unit.unit',
            'grn_supplier_details.sr_table_unique_id',
            'grn_supplier_details.sr_table_pk_id',
            'grn_supplier_details.grnd_qty',
            'grn_supplier_details.grnd_rate_unit',
            'grn_supplier_details.grnd_amount',
            'grn_supplier_details.grnd_remark',
            'admin.person_name as user_name',
            'grn_supplier.created_on',
            'grn_supplier.created_by',
            'grn_supplier.last_by',
            'grn_supplier.last_on'
        ])
        ->leftJoin('grn_supplier_details','grn_supplier_details.grnd_grn_id','=','grn_supplier.grn_id')
        ->leftJoin('admin','admin.id','=','grn_supplier.prepared_by_user_id')
        ->leftJoin('suppliers','suppliers.id','=','grn_supplier.grn_supplier_id')
        ->leftJoin('item','item.id','=','grn_supplier_details.grnd_item_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('purchase_order_details as pod', function ($join) {
            $join->on('pod.pod_id', '=', 'grn_supplier_details.table_pk_id')
                ->where('grn_supplier_details.table_unique_id', '=', 'Against PO');
        })
        ->leftJoin('purchase_order as po', 'po.po_id', '=', 'pod.pod_po_id')
        ->leftJoin('supplier_dc_details as dcd', function ($join) {
            $join->on('dcd.sup_dcd_id', '=', 'grn_supplier_details.table_pk_id')
                ->where('grn_supplier_details.table_unique_id', '=', 'Against DC');
        })
        ->leftJoin('supplier_dc as dc', 'dc.sup_dc_id', '=', 'dcd.sup_dcd_dc_id')
        ->where('grn_supplier.year_id', $year_data->id)
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'grn_supplier_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'grn_supplier_details.sr_table_unique_id');
        })
        ->where('grn_supplier.current_location_id',$current_location);
        if($request->from_date != "" && $request->to_date != "") {
            $from = Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $to = Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $grn_sup_data->whereDate('grn_supplier.grn_date','>=',$from);
            $grn_sup_data->whereDate('grn_supplier.grn_date','<=',$to);
        } else if($request->from_date != "") {
            $from = Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $grn_sup_data->where('grn_supplier.grn_date','>=',$from);
        } else if($request->to_date != "") {
            $to = Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $grn_sup_data->where('grn_supplier.grn_date','<=',$to);
        }

        $dataTable = DataTables::of($grn_sup_data)
        ->filterColumn('grn_supplier.grn_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'grn_supplier.grn_number');
        })
        ->filterColumn('ref_number', function($query, $keyword) {

            applynumberprefix($query, $keyword, 'po.po_number');

            $query->orWhere(function($q) use ($keyword) {
                applynumberprefix($q, $keyword, 'dc.sup_dc_number');
            });

        })
         ->editColumn('item_type', function($grn_sup_data) {
            return config('app.item_type')[$grn_sup_data->item_type] ?? $grn_sup_data->item_type;
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
        ->editColumn('grn_date', function($grn_sup_data){
            if ($grn_sup_data->grn_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $grn_sup_data->grn_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('grn_supplier.grn_date', function ($q, $k) {
            applyDate($q, $k, 'grn_supplier.grn_date');
        })
        ->editColumn('ref_date', function($grn_sup_data){
            if ($grn_sup_data->ref_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $grn_sup_data->ref_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('ref_date', function($q, $k) {

            $q->where(function($query) use ($k) {

                // ✅ PO Date
                $query->where(function($q1) use ($k) {
                    $q1->where('grn_supplier_details.table_unique_id', 'Against PO');
                    applyDate($q1, $k, 'po.po_date');
                });

                // ✅ DC Date
                $query->orWhere(function($q2) use ($k) {
                    $q2->where('grn_supplier_details.table_unique_id', 'Against DC');
                    applyDate($q2, $k, 'dc.sup_dc_date');
                });

            });

        })
        ->editColumn('grn_challan_date', function($grn_sup_data){
            if ($grn_sup_data->grn_challan_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $grn_sup_data->grn_challan_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('grn_supplier.grn_challan_date', function ($q, $k) {
            applyDate($q, $k, 'grn_supplier.grn_challan_date');
        })

        ->editColumn('grnd_qty', function($inquiry_data) {
            return $inquiry_data->grnd_qty > 0 ? number_format((float)$inquiry_data->grnd_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('grn_supplier_details.grnd_qty', function($query, $keyword) {
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
        ->filterColumn('grn_supplier_details.grnd_rate_unit', function($query, $keyword) {
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
        ->filterColumn('grn_supplier_details.grnd_amount', function($query, $keyword) {
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
        
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'grn_supplier');
        return $dataTable
        ->make(true);
    }

   }