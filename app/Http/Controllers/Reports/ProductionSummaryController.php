<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction\ProductionEntry;

class ProductionSummaryController extends Controller
{
    public function manage()
    {
        return view('manage.reports.manage-production_summary');
    }

    public function index(Request $request, DataTables $datatables)
    {
        $LocationData = getCurrentLocation();
        $year_data = getCurrentYearData();
       
        $pe_details_sub = DB::table('production_entry_details')
            ->select('production_entry_id')
            ->selectRaw('GROUP_CONCAT(DISTINCT source_id_fix ORDER BY source_id_fix SEPARATOR ", ") as source_id_fix')
            ->selectRaw('SUM(production_sq_in) as prod_sq_in')
            ->selectRaw('SUM(westage_in) as repair_reshoot_sqin')
            ->selectRaw('SUM(retake_sq_in) as retake_sq_in')
            ->groupBy('production_entry_id');

        $pe_data = ProductionEntry::select([
            'production_entry.production_entry_id',
            'production_entry.production_entry_no',
            'production_entry.production_entry_date',
            'enclosure.enclosure_name',
            'sub.source_id_fix',
            'sub.prod_sq_in',
            'sub.repair_reshoot_sqin',
            'sub.retake_sq_in',
            'production_entry.total_sq_in',
            'production_entry.remark',
            'production_entry.production_entry_sequence',
        ])
        ->leftJoin('enclosure', 'enclosure.enclosure_id', '=', 'production_entry.enclosure_id')
        ->leftJoinSub($pe_details_sub, 'sub', 'sub.production_entry_id', '=', 'production_entry.production_entry_id')
        ->where('production_entry.year_id', $year_data->id)
        ->where('production_entry.current_location_id', $LocationData->location_id);

        if($request->from_date != "" && $request->to_date != "") {
            $from = Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $to = Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $pe_data->whereDate('production_entry.production_entry_date', '>=', $from);
            $pe_data->whereDate('production_entry.production_entry_date', '<=', $to);
        } else if($request->from_date != "") {
            $from = Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $pe_data->where('production_entry.production_entry_date', '>=', $from);
        } else if($request->to_date != "") {
            $to = Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $pe_data->where('production_entry.production_entry_date', '<=', $to);
        }

        $dataTable = DataTables::of($pe_data)
        ->filterColumn('production_entry.production_entry_no', function($query, $keyword) {
            applySequenceSearch($query, $keyword, 'production_entry.production_entry_no');
        })
        ->editColumn('production_entry_date', function($row){
            if ($row->production_entry_date != null) {
                return Date::createFromFormat('Y-m-d', $row->production_entry_date)->format(DATE_FORMAT);
            }
            return '';
        })
        ->filterColumn('production_entry.production_entry_date', function ($q, $k) {
            applyDate($q, $k, 'production_entry.production_entry_date');
        })
        ->editColumn('prod_sq_in', function($row) {
            return $row->prod_sq_in > 0 ? number_format((float)$row->prod_sq_in, 2, '.', '') : number_format(0, 2, '.', '');
        })
        ->editColumn('repair_reshoot_sqin', function($row) {
            return $row->repair_reshoot_sqin > 0 ? number_format((float)$row->repair_reshoot_sqin, 2, '.', '') : number_format(0, 2, '.', '');
        })
        ->editColumn('retake_sq_in', function($row) {
            return $row->retake_sq_in > 0 ? number_format((float)$row->retake_sq_in, 2, '.', '') : number_format(0, 2, '.', '');
        })
        ->editColumn('total_sq_in', function($row) {
            return $row->total_sq_in > 0 ? number_format((float)$row->total_sq_in, 2, '.', '') : number_format(0, 2, '.', '');
        })
        ->withQuery('totals', function($filteredQuery) {
            $query = $filteredQuery instanceof \Illuminate\Database\Eloquent\Builder 
                ? clone $filteredQuery->getQuery() 
                : clone $filteredQuery;

            $query->offset = null;
            $query->limit = null;
            $query->orders = null;

            $totals = DB::query()->fromSub($query, 'p_totals')
                ->selectRaw('
                    COALESCE(SUM(prod_sq_in), 0) as total_prod_sq_in,
                    COALESCE(SUM(retake_sq_in), 0) as total_retake_sq_in,
                    COALESCE(SUM(repair_reshoot_sqin), 0) as total_repair_reshoot_sqin,
                    COALESCE(SUM(total_sq_in), 0) as grand_total_sq_in
                ')->first();

            return [
                'total_prod_sq_in' => number_format((float)($totals->total_prod_sq_in ?? 0), 2, '.', ''),
                'total_retake_sq_in' => number_format((float)($totals->total_retake_sq_in ?? 0), 2, '.', ''),
                'total_repair_reshoot_sqin' => number_format((float)($totals->total_repair_reshoot_sqin ?? 0), 2, '.', ''),
                'grand_total_sq_in' => number_format((float)($totals->grand_total_sq_in ?? 0), 2, '.', ''),
            ];
        });
        
        return $dataTable->make(true);
    }
}
