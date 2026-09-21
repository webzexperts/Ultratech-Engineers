<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use App\Models\Transaction\ItemIssue;
use App\Models\Transaction\ItemIssueDetails;
use App\Models\ItemOpening;
use App\Models\Admin;

class ItemIssueInternalSummaryController extends Controller
{
    //
    public function manage()
    {
        return view('manage.reports.manage-item_issue_internal_summary');
    }

    public function index(ItemIssue $issue_data, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $current_location = getCurrentLocation()->location_id;
        $issue_data = ItemIssue::select([
            'item_issue.issue_id',
            'item_issue.issue_number',
            'item_issue.issue_sequence',
            'item_issue.issue_date',
            'item.item_name',
            'item_group.item_group',
            'item.item_type as main_group',
            'item.item_type as main_group_key',
            'unit.unit',
            'sr.name_for_display',
            'item_issue_details.sr_table_unique_id',
            'item_issue_details.sr_table_pk_id',
            'item_issue_details.issue_qty',
            'item_issue_details.remark',
            'item_issue_details.issue_type',
            'item_issue.issue_to',
            'prepared_by.person_name as prepared_by',
            'reason.reason_name',
            'item_issue.created_on',
            'item_issue.created_by',
            'item_issue.last_by',
            'item_issue.last_on'
        ])
        ->leftJoin('item_issue_details','item_issue_details.issue_id','=','item_issue.issue_id')
        ->leftJoin('admin as prepared_by','prepared_by.id','=','item_issue.prepared_by_user_id')
        ->leftJoin('item','item.id','=','item_issue_details.item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('reason','reason.id','=','item_issue_details.wastage_reason_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
         ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'item_issue_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'item_issue_details.sr_table_unique_id');
        })
        ->where('item_issue.year_id', $year_data->id)
        ->where('item_issue.current_location_id', $current_location);
        if($request->from_date != "" && $request->to_date != "") {
            $from = Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $to = Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $issue_data->whereDate('item_issue.issue_date','>=',$from);
            $issue_data->whereDate('item_issue.issue_date','<=',$to);
        } else if($request->from_date != "") {
            $from = Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $issue_data->where('item_issue.issue_date','>=',$from);
        } else if($request->to_date != "") {
            $to = Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $issue_data->where('item_issue.issue_date','<=',$to);
        }
        $dataTable = DataTables::of($issue_data)
        ->filterColumn('item_issue.issue_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'item_issue.issue_number');
        })
        ->editColumn('issue_date', function($issue_data){
            if ($issue_data->issue_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $issue_data->issue_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('item_issue.issue_date', function ($q, $k) {
            applyDate($q, $k, 'item_issue.issue_date');
        })
        
        ->editColumn('issue_qty', function($issue_data) {
            return $issue_data->issue_qty > 0 ? number_format((float)$issue_data->issue_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->editColumn('main_group', function($issue_data) {
            return config('app.item_type')[$issue_data->main_group] ?? $issue_data->main_group;
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
        ->filterColumn('item_issue_details.issue_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('issue_qty')
                    ->orWhere('issue_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(issue_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('issue_qty', 'like', "%{$search}%");
            }
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'item_issue');
        return $dataTable
        ->rawColumns(['sr_no'])
        ->make(true);
    }
}