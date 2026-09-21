<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\EstimationCosting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DataTables;
use Date;

class EstimationCostingSummaryController extends Controller
{
    public function manage()
    {
        return view('manage.reports.manage-estimation_costing_summary');
    }

    public function index(EstimationCosting $estimation_costing_summary_data, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $estimation_costing_summary_data = EstimationCosting::select([
            'estimation_costing.ec_id',
            'estimation_costing.ec_number',
            'estimation_costing.ec_sequence',
            'estimation_costing.ec_date',
            'estimation_costing.ec_inqd_id',
            'inquiry.inq_id',
            'inquiry.inq_number',
            'inquiry.inq_date',
            'customers.customer_code',
            'customers.customer',
            'inquiry.inq_ref_no_date',
            'inquiry_details.inqd_test_method',
            'inquiry_details.inqd_type_of_job_id',
            'type_of_job.type_of_job',
            'inquiry_details.inqd_job_description_id',
            'job_descriptions.job_description',
            'inquiry_details.inqd_part_id',
            \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'inquiry_details.inqd_process_at',
            'inquiry_details.inqd_quantity',
            'unit.unit',
            'estimation_costing.ec_estimation',
            'estimation_costing.ec_costing',
            'prepared_by.person_name as ec_prepared_by_name',
            'reviewed_by.person_name as ec_reviewed_by_name'
        ])
        ->leftJoin('inquiry_details','inquiry_details.inqd_id','=','estimation_costing.ec_inqd_id')
        ->leftJoin('inquiry','inquiry.inq_id','=','inquiry_details.inq_id')
        ->leftJoin('customers','customers.id','=','inquiry.inq_customer_id')
        ->leftJoin('type_of_job','type_of_job.id','=','inquiry_details.inqd_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','inquiry_details.inqd_job_description_id')
        ->leftJoin('part','part.part_id','=','inquiry_details.inqd_part_id')
        ->leftJoin('unit','unit.id','=','inquiry_details.inqd_unit_id')
        ->leftJoin('admin as prepared_by','prepared_by.id','=','estimation_costing.ec_prepared_by_id')
        ->leftJoin('admin as reviewed_by','reviewed_by.id','=','estimation_costing.ec_reviewed_by_id')
        ->where('estimation_costing.year_id', $year_data->id);

        if($request->from_date != "" && $request->to_date != "") {
            $from =  Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $to =  Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $estimation_costing_summary_data->whereDate('estimation_costing.ec_date','>=',$from);
            $estimation_costing_summary_data->whereDate('estimation_costing.ec_date','<=',$to);
        } else if($request->from_date != "") {
            $from =  Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $estimation_costing_summary_data->where('estimation_costing.ec_date','>=',$from);
        } else if($request->to_date != "") {
            $to =  Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $estimation_costing_summary_data->where('estimation_costing.ec_date','<=',$to);
        }

        $dataTable = DataTables::of($estimation_costing_summary_data)
        ->filterColumn('part.part', function($query, $keyword) {
            $keyword = trim($keyword);
            $query->whereRaw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) LIKE ?", ["%{$keyword}%"]);
        })
        ->editColumn('ec_date', function($estimation_costing_summary_data) {
            if ($estimation_costing_summary_data->ec_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $estimation_costing_summary_data->ec_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('estimation_costing.ec_date', function ($q, $k) {
            applyDate($q, $k, 'estimation_costing.ec_date');
        })
        ->editColumn('inq_date', function($estimation_data){
            if ($estimation_data->inq_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $estimation_data->inq_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('inquiry.inq_date', function ($q, $k) {
            applyDate($q, $k, 'inquiry.inq_date');
        })
        ->editColumn('inqd_quantity', function($estimation_costing_summary_data) {
            // return $estimation_costing_summary_data->inqd_quantity > 0 ? number_format((float)$estimation_costing_summary_data->inqd_quantity, 3, '.','') : number_format((float) 0, 3, '.','');
            return $estimation_costing_summary_data->inqd_quantity > 0 ? number_format((float)$estimation_costing_summary_data->inqd_quantity, 2, '.','') : number_format((float) 0, 2, '.','');
        })
        ->filterColumn('inquiry_details.inqd_quantity', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('inqd_quantity')
                    ->orWhere('inqd_quantity', 0);
                });
            } else if (is_numeric($search)) {
                $query->whereRaw("CAST(inqd_quantity AS CHAR) LIKE ?", ["{$search}%"]);
            } else {
                $query->where('inqd_quantity', 'like', "%{$search}%");
            }
        });
        return $dataTable
        ->make(true);
    }
}