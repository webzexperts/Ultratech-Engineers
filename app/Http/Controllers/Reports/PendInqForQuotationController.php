<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FeasibilityReview;
use App\Models\Inquiry;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class PendInqForQuotationController extends Controller
{
    public function manage()
    {
        return view('manage.reports.manage-pend_inq_for_quotation');
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
                'inquiry_details.inqd_part_no',
                //DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
                'unit.unit',
                'inquiry_details.inqd_process_at',
                'inquiry_details.inqd_quantity',
                'inquiry_details.inqd_remark',
                DB::raw("
                    (
                        IFNULL(inquiry_details.inqd_quantity, 0) 
                            -
                            (
                                SELECT IFNULL(SUM(isc.inq_sc_qty), 0)
                                FROM inquiry_short_close as isc
                                WHERE isc.inq_sc_inqd_id = inquiry_details.inqd_id
                            )
                        ) as pend_inq_qty
                    ")
            )
            ->leftJoin('inquiry_details', 'inquiry_details.inq_id', '=', 'inquiry.inq_id')
            ->leftJoin('customers', 'customers.id', '=', 'inquiry.inq_customer_id')
            ->leftJoin('type_of_job', 'type_of_job.id', '=', 'inquiry_details.inqd_type_of_job_id')
            ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'inquiry_details.inqd_job_description_id')
            //->leftJoin('part', 'part.part_id', '=', 'inquiry_details.inqd_part_id')
            ->leftJoin('unit','unit.id','=','inquiry_details.inqd_unit_id')
            ->leftJoin(
                'feasibility_review',
                'feasibility_review.fr_inqd_id',
                '=',
                'inquiry_details.inqd_id'
            )

            // quotation join
            ->leftJoin(
                'quotation_details',
                'quotation_details.quotd_inqd_id',
                '=',
                'inquiry_details.inqd_id'
            )
            ->whereIn('inquiry.year_id', $yearIds)

            // Direct OR Feasibility logic
            ->where(function ($q) {
                $q->where('inquiry_details.inqd_feasibility_required', 'No')
                ->orWhere(function ($q2) {
                    $q2->where('inquiry_details.inqd_feasibility_required', 'Yes')
                        ->where('feasibility_review.fr_result_id', '=', 'Feasible');
                });
            })

            // 🔹 EDIT CONDITION HANDLING
            ->where(function ($q) use ($request) {
                if (!empty($request->id)) {
                    $q->whereNull('quotation_details.quotd_inqd_id')
                    ->orWhere('quotation_details.quotd_quot_id', $request->id);
                } else {
                    $q->whereNull('quotation_details.quotd_inqd_id');
                }
            })

            // ->orderBy('customers.customer', 'asc')
            ->distinct()
            ->having('pend_inq_qty','>',0);


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
            applynumberprefix($query, $keyword, 'inquiry.inq_number');
        })
        ->filterColumn('part.part', function($query, $keyword) {
            $keyword = trim($keyword);
            $query->whereRaw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) LIKE ?", ["%{$keyword}%"]);
        });
        return $dataTable
        ->make(true);
    }
}
