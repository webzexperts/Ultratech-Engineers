<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Transaction\SupplierDC;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use App\Models\Admin;
use App\Models\Transaction\SupplierDCDetails;
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



class SupplierDCSummaryController extends Controller
{
    public function manage()
    {
        return view('manage.reports.manage-supplier_dc_summary');
    }

    public function index(SupplierDC $sup_dc_data, Request $request, DataTables $datatables)
    {

        $year_data = getCurrentYearData();
        $current_location = getCurrentLocation()->location_id;
        $sup_dc_data = SupplierDC::select([
            'supplier_dc.sup_dc_id',
            'supplier_dc.sup_dc_type_id',
            'supplier_dc.sup_dc_sequence',
            'supplier_dc.sup_dc_number',
            'supplier_dc.sup_dc_date',
            'supplier_dc.supplier_id',
            'suppliers.supplier_name',
            'supplier_dc.ref_no_date',
            'item.item_name',
            'item_group.item_group',
            'item.item_type',
            'sr.name_for_display',
            \DB::raw("IF(supplier_dc.sup_dc_type_id = 'SQIN from Prod. Area', 'SQIN', unit.unit) as unit"),
            'supplier_dc_details.sr_table_unique_id',
            'supplier_dc_details.sr_table_pk_id',
            'supplier_dc_details.return_qty',
            'supplier_dc_details.remark',
            'admin.person_name as user_name',
            'supplier_dc.created_on',
            'supplier_dc.created_by',
            'supplier_dc.last_by',
            'supplier_dc.last_on'
        ])
        ->leftJoin('supplier_dc_details','supplier_dc_details.sup_dcd_dc_id','=','supplier_dc.sup_dc_id')
        ->leftJoin('admin','admin.id','=','supplier_dc.prepared_by_user_id')
        ->leftJoin('suppliers','suppliers.id','=','supplier_dc.supplier_id')
        ->leftJoin('item','item.id','=','supplier_dc_details.item_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'supplier_dc_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'supplier_dc_details.sr_table_unique_id');
        })
        ->where('supplier_dc.year_id', $year_data->id)
        ->where('supplier_dc.current_location_id',$current_location);
        if($request->from_date != "" && $request->to_date != "") {
            $from = Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $to = Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $sup_dc_data->whereDate('supplier_dc.sup_dc_date','>=',$from);
            $sup_dc_data->whereDate('supplier_dc.sup_dc_date','<=',$to);
        } else if($request->from_date != "") {
            $from = Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $sup_dc_data->where('supplier_dc.sup_dc_date','>=',$from);
        } else if($request->to_date != "") {
            $to = Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $sup_dc_data->where('supplier_dc.sup_dc_date','<=',$to);
        }

        $dataTable = DataTables::of($sup_dc_data)
        ->filterColumn('supplier_dc.grn_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'supplier_dc.grn_number');
        })
        ->editColumn('item_type', function($sup_dc_data) {
            return config('app.item_type')[$sup_dc_data->item_type] ?? $sup_dc_data->item_type;
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
        ->editColumn('sup_dc_date', function($sup_dc_data){
            if ($sup_dc_data->sup_dc_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $sup_dc_data->sup_dc_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('supplier_dc.sup_dc_date', function ($q, $k) {
            applyDate($q, $k, 'supplier_dc.sup_dc_date');
        })
        ->filterColumn('unit', function($query, $keyword) {
            $keyword = trim($keyword);
            $query->where(function($q) use ($keyword) {
                $q->where(function($sub_q) use ($keyword) {
                    $sub_q->where('unit.unit', 'like', "%{$keyword}%")
                          ->where(function($sub_q2) {
                              $sub_q2->where('supplier_dc.sup_dc_type_id', '!=', 'SQIN from Prod. Area')
                                     ->orWhereNull('supplier_dc.sup_dc_type_id');
                          });
                });
                if (stripos('SQIN', $keyword) !== false || stripos('SQ IN', $keyword) !== false) {
                    $q->orWhere('supplier_dc.sup_dc_type_id', '=', 'SQIN from Prod. Area');
                }
            });
        })
        ->filterColumn('unit.unit', function($query, $keyword) {
            $keyword = trim($keyword);
            $query->where(function($q) use ($keyword) {
                $q->where(function($sub_q) use ($keyword) {
                    $sub_q->where('unit.unit', 'like', "%{$keyword}%")
                          ->where(function($sub_q2) {
                              $sub_q2->where('supplier_dc.sup_dc_type_id', '!=', 'SQIN from Prod. Area')
                                     ->orWhereNull('supplier_dc.sup_dc_type_id');
                          });
                });
                if (stripos('SQIN', $keyword) !== false || stripos('SQ IN', $keyword) !== false) {
                    $q->orWhere('supplier_dc.sup_dc_type_id', '=', 'SQIN from Prod. Area');
                }
            });
        })

        ->editColumn('return_qty', function($inquiry_data) {
            return $inquiry_data->return_qty > 0 ? number_format((float)$inquiry_data->return_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('supplier_dc_details.return_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('return_qty')
                    ->orWhere('return_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(return_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('return_qty', 'like', "%{$search}%");
            }
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'supplier_dc');
        return $dataTable
        // ->rawColumns(['last_by','last_on','created_by','created_on'])
        ->make(true);
    }
}