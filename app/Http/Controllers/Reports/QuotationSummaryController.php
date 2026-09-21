<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\QuotationDetails;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class QuotationSummaryController extends Controller
{
    public function manage()
    {
        return view('manage.reports.manage-quotation_summary');
    }

     public function index(QuotationDetails $quotation_data, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $quotation_data = QuotationDetails::select([
            'quotation.quot_id',
            'quotation.quot_number',
            'quotation.quot_sequence',
            'quotation.quot_date',
            'customers.customer',
            'gst_configuration.gc_sac',
            'quotation.quot_ref_no_date',
            'inquiry.inq_number',
            'inquiry.inq_date',
            'quotation_details.quotd_test_method_id',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            'inquiry_details.inqd_part_no',
            //DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'quotation_details.quotd_process_at_id',
            'quotation_details.quotd_qty',
            'quot_qty_unit.unit as quot_qty_unit_name',
            'quotation_details.quotd_rate_unit',
            //'quotation_details.quotd_minimum_charge',
            //'quotation_details.quotd_minimum_charge_id',
           // 'quot_rate_unit.unit as quot_rate_unit_name',
            //'quotation_details.quotd_remark',
            'quotation.quot_offer_validity',
            // 'quotation_details.quotd_conveyance_charge',
            'prepared_by.person_name as quot_prepared_by_name',
            'authorised_by.person_name as quot_authorised_by_name',
        ])
        ->leftJoin('inquiry_details','inquiry_details.inqd_id','=','quotation_details.quotd_inqd_id')
        ->leftJoin('inquiry','inquiry.inq_id','=','inquiry_details.inq_id')
        ->leftJoin('quotation','quotation.quot_id','=','quotation_details.quotd_quot_id')
        ->leftJoin('customers','customers.id','=','quotation.quot_customer_id')
        ->leftJoin('gst_configuration','gst_configuration.gc_id','=','quotation.quot_sac_id')
        ->leftJoin('type_of_job','type_of_job.id','=','quotation_details.quotd_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','quotation_details.quotd_job_desc_id')
        //->leftJoin('part','part.part_id','=','quotation_details.quotd_part_id')

        ->leftJoin('unit as quot_qty_unit','quot_qty_unit.id','=','quotation_details.quotd_unit_id')
        ->leftJoin('unit as quot_rate_unit','quot_rate_unit.id','=','quotation_details.quotd_rate_unit_id')

        ->leftJoin('admin as prepared_by','prepared_by.id','=','quotation.quot_prepared_by_id')
        ->leftJoin('admin as authorised_by','authorised_by.id','=','quotation.quot_authorised_by_id')
        ->where('quotation.year_id', $year_data->id);

         if($request->from_date != "" && $request->to_date != "") {
            $from =  Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $to =  Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $quotation_data->whereDate('quotation.quot_date','>=',$from);
            $quotation_data->whereDate('quotation.quot_date','<=',$to);
        } else if($request->from_date != "") {
            $from =  Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $quotation_data->where('quotation.quot_date','>=',$from);
        } else if($request->to_date != "") {
            $to =  Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $quotation_data->where('quotation.quot_date','<=',$to);
        }

        $dataTable = DataTables::of($quotation_data)
        ->filterColumn('part.part', function($query, $keyword) {
            $keyword = trim($keyword);
            $query->whereRaw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) LIKE ?", ["%{$keyword}%"]);
        })
        ->editColumn('quot_date', function($quotation_data){
            if ($quotation_data->quot_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $quotation_data->quot_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('quotation.quot_date', function ($q, $k) {
            applyDate($q, $k, 'quotation.quot_date');
        })
        ->editColumn('inq_date', function($quotation_data){
            if ($quotation_data->inq_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $quotation_data->inq_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('inquiry.inq_date', function ($q, $k) {
            applyDate($q, $k, 'inquiry.inq_date');
        })

         ->editColumn('quotd_qty', function($inquiry_data) {
            // return $inquiry_data->quotd_qty > 0 ? number_format((float)$inquiry_data->quotd_qty, 3, '.','') : number_format((float) 0, 3, '.','');
            return $inquiry_data->quotd_qty > 0 ? number_format((float)$inquiry_data->quotd_qty, 2, '.','') : number_format((float) 0, 2, '.','');
        })
        ->filterColumn('quotation_details.quotd_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('quotd_qty')
                    ->orWhere('quotd_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(quotd_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('quotd_qty', 'like', "%{$search}%");
            }
        })
       ->editColumn('quotd_rate_unit', function($inquiry_data) {
            return $inquiry_data->quotd_rate_unit === null
                ? ''
                : ($inquiry_data->quotd_rate_unit > 0
                    ? number_format((float)$inquiry_data->quotd_rate_unit, 2, '.', '')
                    : number_format((float)$inquiry_data->quotd_rate_unit, 2, '.', '')
                );
        })
        ->filterColumn('quotation_details.quotd_rate_unit', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('quotd_rate_unit')
                    ->orWhere('quotd_rate_unit', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(quotd_rate_unit AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('quotd_rate_unit', 'like', "%{$search}%");
            }
        })
      ->editColumn('quotd_minimum_charge', function($inquiry_data) {
            return $inquiry_data->quotd_minimum_charge === null
                ? ''
                : ($inquiry_data->quotd_minimum_charge > 0
                    ? number_format((float)$inquiry_data->quotd_minimum_charge, 2, '.', '')
                    : number_format((float)$inquiry_data->quotd_minimum_charge, 2, '.', '')
                );
        })
        ->filterColumn('quotation_details.quotd_minimum_charge', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('quotd_minimum_charge')
                    ->orWhere('quotd_minimum_charge', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(quotd_minimum_charge AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('quotd_minimum_charge', 'like', "%{$search}%");
            }
        })
      ->editColumn('quotd_conveyance_charge', function($inquiry_data) {
            return $inquiry_data->quotd_conveyance_charge === null
                ? ''
                : ($inquiry_data->quotd_conveyance_charge > 0
                    ? number_format((float)$inquiry_data->quotd_conveyance_charge, 2, '.', '')
                    : number_format((float)$inquiry_data->quotd_conveyance_charge, 2, '.', '')
                );
        })
        ->filterColumn('quotation_details.quotd_conveyance_charge', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('quotd_conveyance_charge')
                    ->orWhere('quotd_conveyance_charge', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(quotd_conveyance_charge AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('quotd_conveyance_charge', 'like', "%{$search}%");
            }
        })
        ->filterColumn('quotation.quot_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'quotation.quot_number');
        })
        ->filterColumn('inquiry.inq_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'inquiry.inq_number');
        });
        return $dataTable
        ->make(true);
    }
}
