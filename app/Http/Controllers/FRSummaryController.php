<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use App\Models\Admin;
use App\Models\FeasibilityReview;
use Yajra\DataTables\DataTables;


class FRSummaryController extends Controller
{
    public function manage()
    {
        return view('manage.reports.manage-fr_summary');
    }

    public function index(FeasibilityReview $feasibility_review, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $feasibility_data = FeasibilityReview::select([
            'feasibility_review.fr_id',
            'feasibility_review.fr_number',
            'feasibility_review.fr_date',
            'feasibility_review.fr_inqd_id',
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
            'inquiry_details.inqd_process_at',
            'inquiry_details.inqd_quantity',
            'inquiry_details.inqd_part_no',
           // \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'unit.unit',
            'feasibility_review.fr_result_id',
            'feasibility_review.fr_suggest_method_id',
            'feasibility_review.fr_reason_id',
            'reason.reason_name',
            'feasibility_review.created_on',
            'feasibility_review.created_by',
            'feasibility_review.last_by',
            'feasibility_review.last_on',
            'prepared_by.person_name as fr_prepared_by_name',
            'reviewed_by.person_name as fr_reviewed_by_name',
        ])
        ->leftJoin('inquiry_details','inquiry_details.inqd_id','=','feasibility_review.fr_inqd_id')
        ->leftJoin('reason','reason.id','=','feasibility_review.fr_reason_id')
        ->leftJoin('inquiry','inquiry.inq_id','=','inquiry_details.inq_id')
        ->leftJoin('customers','customers.id','=','inquiry.inq_customer_id')
        ->leftJoin('type_of_job','type_of_job.id','=','inquiry_details.inqd_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','inquiry_details.inqd_job_description_id')
        //->leftJoin('part','part.part_id','=','inquiry_details.inqd_part_id')
        ->leftJoin('unit','unit.id','=','inquiry_details.inqd_unit_id')
        ->leftJoin('admin as prepared_by','prepared_by.id','=','feasibility_review.fr_prepared_by_id')
        ->leftJoin('admin as reviewed_by','reviewed_by.id','=','feasibility_review.fr_reviewed_by_id')
        ->where('feasibility_review.year_id', $year_data->id);
        if($request->from_date != "" && $request->to_date != ""){

            $from =  Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');

            $to =  Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');

            $feasibility_data->whereDate('feasibility_review.fr_date','>=',$from);
            $feasibility_data->whereDate('feasibility_review.fr_date','<=',$to);

        }else if($request->from_date != ""){

            $from =  Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');

            $feasibility_data->where('feasibility_review.fr_date','>=',$from);

        }else if($request->to_date != ""){

            $to =  Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');

            $feasibility_data->where('feasibility_review.fr_date','<=',$to);

        };
        $dataTable = DataTables::of($feasibility_data)
        ->filterColumn('part.part', function($query, $keyword) {
            $keyword = trim($keyword);
            $query->whereRaw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) LIKE ?", ["%{$keyword}%"]);
        })
        ->editColumn('fr_date', function($feasibility_data){
            if ($feasibility_data->fr_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $feasibility_data->fr_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->editColumn('inq_date', function($feasibility_data){
            if ($feasibility_data->inq_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $feasibility_data->inq_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('inquiry.inq_date', function ($q, $k) {
            applyDate($q, $k, 'inquiry.inq_date');
        })
        ->filterColumn('feasibility_review.fr_date', function ($q, $k) {
            applyDate($q, $k, 'feasibility_review.fr_date');
        })
        ->filterColumn('feasibility_review.fr_number', function ($q, $k) {
            applynumberprefix($q, $k, 'feasibility_review.fr_number');
        })
        ->filterColumn('inquiry.inq_number', function ($q, $k) {
            applynumberprefix($q, $k, 'inquiry.inq_number');
        })
        ->editColumn('inqd_quantity', function($feasibility_data) {
            // return $feasibility_data->inqd_quantity > 0 ? number_format((float)$feasibility_data->inqd_quantity, 3, '.','') : number_format((float) 0, 3, '.','');
            return $feasibility_data->inqd_quantity > 0 ? number_format((float)$feasibility_data->inqd_quantity, 2, '.','') : number_format((float) 0, 2, '.','');
        })
        ->filterColumn('inquiry_details.inqd_quantity', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('inqd_quantity')
                    ->orWhere('inqd_quantity', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(inqd_quantity AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('inqd_quantity', 'like', "%{$search}%");
            }
        })
        ->filterColumn('feasibility_review.fr_result_id', function ($query, $keyword) {
            $lowerKeyword = strtolower(trim($keyword));
            if (str_starts_with('feasible', $lowerKeyword)) {
                $query->where('fr_result_id', '=', "Feasible");
            }
            elseif (str_starts_with('not feasible', $lowerKeyword)) {
                $query->where('fr_result_id', '=', "Not Feasible");
            }
            elseif (str_starts_with('alternet method', $lowerKeyword)) {
                $query->where('fr_result_id', '=', "Alternet Method");
            }
            else {
                $query->where('fr_result_id', 'like', "%$keyword%");
            }
        });
        return $dataTable
        ->make(true);
    }
}
