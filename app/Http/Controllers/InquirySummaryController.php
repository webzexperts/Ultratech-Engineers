<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Inquiry;
use App\Models\InquiryDetails;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Date;

class InquirySummaryController extends Controller
{
    public function manage()
    {
        return view('manage.reports.manage-inquiry_summary');
    }

    public function index(Inquiry $Inquiry, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $inquiry_data = InquiryDetails::select([
            'inquiry.inq_id',
            'inquiry.inq_sequence',
            'inquiry.inq_number',
            'inquiry.inq_date',
            'customers.customer',
            'inquiry.inq_ref_no_date',
            'inquiry_details.inqd_id',
            'inquiry_details.inqd_test_method',
            'type_of_job.type_of_job',
            'inquiry_details.inqd_job_description_id',
            'job_descriptions.job_description',
            'inquiry_details.inqd_part_no',
            // DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'inquiry_details.inqd_process_at',
            'inquiry_details.inqd_quantity',
            'unit.unit',
            'inquiry_details.inqd_feasibility_required',
           // 'inquiry_details.inqd_estimation_required',
            'admin.person_name as user_name',
            'inquiry_details.inqd_remark'
        ])
        ->leftJoin('inquiry','inquiry.inq_id','=','inquiry_details.inq_id')
        ->leftJoin('customers','customers.id','=','inquiry.inq_customer_id')
        ->leftJoin('admin','admin.id','=','inquiry.inq_prepared_by_id')
        ->leftJoin('type_of_job','type_of_job.id','=','inquiry_details.inqd_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','inquiry_details.inqd_job_description_id')
       // ->leftJoin('part','part.part_id','=','inquiry_details.inqd_part_id')
        ->leftJoin('unit','unit.id','=','inquiry_details.inqd_unit_id')
        ->where('inquiry.year_id', $year_data->id);

        if($request->from_date != "" && $request->to_date != "") {
            $from =  Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $to =  Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $inquiry_data->whereDate('inquiry.inq_date','>=',$from);
            $inquiry_data->whereDate('inquiry.inq_date','<=',$to);
        } else if($request->from_date != "") {
            $from =  Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $inquiry_data->where('inquiry.inq_date','>=',$from);
        } else if($request->to_date != "") {
            $to =  Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $inquiry_data->where('inquiry.inq_date','<=',$to);
        }

        $dataTable = DataTables::of($inquiry_data)
        ->filterColumn('part.part', function($query, $keyword) {
            $keyword = trim($keyword);
            $query->whereRaw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) LIKE ?", ["%{$keyword}%"]);
        })
        ->editColumn('inq_date', function($inquiry_data) {
            if ($inquiry_data->inq_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $inquiry_data->inq_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('inquiry.inq_date', function ($q, $k) {
            applyDate($q, $k, 'inquiry.inq_date');
        })
        ->editColumn('inqd_quantity', function($inquiry_data) {
            // return $inquiry_data->inqd_quantity > 0 ? number_format((float)$inquiry_data->inqd_quantity, 3, '.','') : number_format((float) 0, 3, '.','');
            return $inquiry_data->inqd_quantity > 0 ? number_format((float)$inquiry_data->inqd_quantity, 2, '.','') : number_format((float) 0, 2, '.','');
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