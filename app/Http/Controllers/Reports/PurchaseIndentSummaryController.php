<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\PurchaseIndent;
use App\Models\PurchaseIndentDetails;

class PurchaseIndentSummaryController extends Controller
{
    public function manage()
    {
        return view('manage.reports.manage-purchase_indent_summary');
    }

    public function index(PurchaseIndent $pi_data, Request $request, DataTables $datatables)
    {
        $LocationData = getCurrentLocation();
       
        $pi_data = PurchaseIndent::select([
            'purchase_indent.pi_id',
            'purchase_indent.pi_no',
            'purchase_indent.pi_date',
            'to_location.location_name as to_location',
            'indent_by.person_name as indent_by',
            'item.item_name',
            'item_group.item_group',
            'item.item_type as main_group',
            'purchase_indent_details.indent_qty',
            'unit.unit',
            'purchase_indent_details.remark',
            'purchase_indent.special_note',
            'purchase_indent.created_on',
            'purchase_indent.created_by',
            'purchase_indent.last_by',
            'purchase_indent.last_on',
            'purchase_indent.pi_sequence',
        ])

        ->leftJoin('purchase_indent_details','purchase_indent_details.pid_pi_id','=','purchase_indent.pi_id')
        ->leftJoin('admin as indent_by','indent_by.id','=','purchase_indent.indent_by_user_id')
        ->leftJoin('location as to_location','to_location.location_id','=','purchase_indent.to_location_id')
        ->leftJoin('item','item.id','=','purchase_indent_details.item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->where('purchase_indent.current_location_id', $LocationData->location_id);
        if($request->from_date != "" && $request->to_date != "") {
            $from =  Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $to =  Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $pi_data->whereDate('purchase_indent.pi_date','>=',$from);
            $pi_data->whereDate('purchase_indent.pi_date','<=',$to);
        } else if($request->from_date != "") {
            $from =  Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $pi_data->where('purchase_indent.pi_date','>=',$from);
        } else if($request->to_date != "") {
            $to =  Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $pi_data->where('purchase_indent.pi_date','<=',$to);
        }


        $dataTable = DataTables::of($pi_data)
        ->filterColumn('purchase_indent.pi_no', function($query, $keyword) {
            applySequenceSearch($query, $keyword, 'purchase_indent.pi_no');
        })
        ->editColumn('pi_date', function($pi_data){
            if ($pi_data->pi_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $pi_data->pi_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('purchase_indent.pi_date', function ($q, $k) {
            applyDate($q, $k, 'purchase_indent.pi_date');
        })
        
        ->editColumn('indent_qty', function($pi_data) {
            return $pi_data->indent_qty > 0 ? number_format((float)$pi_data->indent_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('purchase_indent_details.indent_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('indent_qty')
                    ->orWhere('indent_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(indent_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('indent_qty', 'like', "%{$search}%");
            }
        })
        ->editColumn('main_group', function($pi_data) {
            return config('app.item_type')[$pi_data->main_group] ?? $pi_data->main_group;
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
        });
        
        // $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'purchase_indent');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on','options'])
        ->make(true);
    }
}
