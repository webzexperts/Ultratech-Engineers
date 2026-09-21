<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FeasibilityReview;
use App\Models\Inquiry;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class PendInqForEastCostController extends Controller
{
    public function manage()
    {
        return view('manage.reports.manage-pend_inq_for_ec');
    }

    public function index(Inquiry $inq_data, Request $request, DataTables $datatables)
    {
        $yearIds = getCompanyYearIdsToTill();

         $inq_data = Inquiry::select(
            'inquiry.inq_date',
            'inquiry.inq_number',
            'inquiry.inq_sequence',
            'customers.customer',
            'inquiry.inq_ref_no_date',
            'inquiry_details.inqd_test_method',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            // \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'inquiry_details.inqd_process_at',
            'inquiry_details.inqd_quantity',
            'inquiry_details.inqd_remark'
        )
        ->join('inquiry_details', 'inquiry_details.inq_id', '=', 'inquiry.inq_id')
        ->leftJoin('feasibility_review', 'feasibility_review.fr_inqd_id', '=', 'inquiry.inq_customer_id')
        ->leftJoin('customers', 'customers.id', '=', 'inquiry.inq_customer_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'inquiry_details.inqd_type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'inquiry_details.inqd_job_description_id')
        ->leftJoin('part', 'part.part_id', '=', 'inquiry_details.inqd_part_id')
        ->leftJoin('unit', 'unit.id', '=', 'inquiry_details.inqd_unit_id')

        // feasibility required
        ->where('inquiry_details.inqd_feasibility_required', 'Yes')

        // estimation required in feasibility review
        ->whereExists(function ($q) {
            $q->select(DB::raw(1))
            ->from('feasibility_review')
            ->join('inquiry_details as id2', 'id2.inqd_id', '=', 'feasibility_review.fr_inqd_id')
            ->whereColumn('feasibility_review.fr_inqd_id', 'inquiry_details.inqd_id')
            ->where('id2.inqd_estimation_required', 'Yes')
            ->where('feasibility_review.fr_result_id', '=', 'Feasible');
        })

        // estimation not yet done
        ->whereNotExists(function ($q) {
            $q->select(DB::raw(1))
            ->from('estimation_costing')
            ->whereColumn('estimation_costing.ec_inqd_id', 'inquiry_details.inqd_id');
        })

        ->whereIn('inquiry.year_id', $yearIds);


        $dataTable = DataTables::of($inq_data)
        ->editColumn('inq_date', function($inq_data){
            if ($inq_data->inq_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $inq_data->inq_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('inquiry.inq_date', function ($q, $k) {
            applyDate($q, $k, 'inquiry.inq_date');
        })
        ->editColumn('inqd_quantity', function($inq_data) {
            // return $inq_data->inqd_quantity > 0 ? number_format((float)$inq_data->inqd_quantity, 3, '.','') : number_format((float) 0, 3, '.','');
            return $inq_data->inqd_quantity > 0 ? number_format((float)$inq_data->inqd_quantity, 2, '.','') : number_format((float) 0, 2, '.','');
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
        ->filterColumn('inquiry.inq_number', function($query, $keyword) {
            applynumber($query, $keyword, 'inquiry.inq_number');
        })
        ->filterColumn('part.part', function($query, $keyword) {
            $keyword = trim($keyword);
            $query->whereRaw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) LIKE ?", ["%{$keyword}%"]);
        });
        return $dataTable
        ->make(true);
    }
}
