<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\InquiryDetails;
use App\Models\InquiryShortClose;
use App\Models\QuotationDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class InquiryShortCloseController extends Controller
{
    public function manage()
    {
        return view('manage.manage-inquiry_short_close');
    }

    public function index(Request $request)
    {
        $year_data = getCurrentYearData();
        $inq_sc_data = InquiryShortClose::select([

            'inquiry_short_close.inq_sc_id',
            'inquiry_short_close.inq_sc_date',
            'reason.reason_name as sc_reason',
            'customers.customer_code',
            'customers.customer',
            'inquiry.inq_ref_no_date',
            'inquiry.inq_number',
            'inquiry.inq_date',
            'inquiry_details.inqd_test_method',
            'inquiry_details.inqd_process_at',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            'inquiry_details.inqd_part_no',
            //\DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'inquiry_short_close.inq_sc_qty',
            'inquiry_details.inqd_remark',
            'inquiry_short_close.inq_sc_special_note',
            'inquiry_short_close.created_on',
            'inquiry_short_close.created_by',
            'inquiry_short_close.last_by',
            'inquiry_short_close.last_on',
        ])
        ->leftJoin('inquiry_details','inquiry_details.inqd_id','=','inquiry_short_close.inq_sc_inqd_id')
        ->leftJoin('inquiry','inquiry.inq_id','=','inquiry_details.inq_id')
        ->leftJoin('customers','customers.id','=','inquiry.inq_customer_id')
        ->leftJoin('type_of_job','type_of_job.id','=','inquiry_details.inqd_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','inquiry_details.inqd_job_description_id')
        //->leftJoin('part','part.part_id','=','inquiry_details.inqd_part_id')
        ->leftJoin('reason','reason.id','=','inquiry_short_close.inq_sc_regret_reason_id')
        ->where('inquiry_short_close.year_id', $year_data->id);

        $dataTable = DataTables::of($inq_sc_data)
        // ->filterColumn('part.part', function($query, $keyword) {
        //     $keyword = trim($keyword);
        //     $query->whereRaw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) LIKE ?", ["%{$keyword}%"]);
        // })
        
        ->editColumn('inq_date', function($inq_sc_data){
            if ($inq_sc_data->inq_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $inq_sc_data->inq_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('inquiry.inq_date', function ($q, $k) {
            applyDate($q, $k, 'inquiry.inq_date');
        })

        ->editColumn('inq_sc_date', function($inq_sc_data){
            if ($inq_sc_data->inq_sc_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $inq_sc_data->inq_sc_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('inquiry_short_close.inq_sc_date', function ($q, $k) {
            applyDate($q, $k, 'inquiry_short_close.inq_sc_date');
        })
         ->filterColumn('inquiry.inq_number', function ($q, $k) {
            applynumberprefix($q, $k, 'inquiry.inq_number');
        })

        ->editColumn('inq_sc_qty', function($inquiry_data) {
            // return $inquiry_data->inq_sc_qty > 0 ? number_format((float)$inquiry_data->inq_sc_qty, 3, '.','') : number_format((float) 0, 3, '.','');
            return $inquiry_data->inq_sc_qty > 0 ? number_format((float)$inquiry_data->inq_sc_qty, 2, '.','') : number_format((float) 0, 2, '.','');
        })
        ->filterColumn('inquiry_short_close.inq_sc_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('inq_sc_qty')
                    ->orWhere('inq_sc_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(inq_sc_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('inq_sc_qty', 'like', "%{$search}%");
            }
        })
        
        ->addColumn('options',function($inq_sc_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("inquiry_short_close", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'inquiry_short_close');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }
    public function getPendingInqSC(Request $request)
    {
        $yearIds      = getCompanyYearIdsToTill();
        $used_quot_inqd_ids = QuotationDetails::whereNotNull('quotd_inqd_id')->pluck('quotd_inqd_id');
        $used_sc_inqd_ids = InquiryShortClose::whereNotNull('inq_sc_inqd_id')->pluck('inq_sc_inqd_id');
        $inq_sc_data = Inquiry::select([
                'inquiry.inq_id',
                'inquiry.inq_number',
                'inquiry.inq_date',
                // 'customers.customer_code',
                'customers.customer',
                'inquiry.inq_ref_no_date',
                'inquiry_details.inqd_id',
                'inquiry_details.inqd_test_method',
                'inquiry_details.inqd_type_of_job_id',
                'type_of_job.type_of_job',
                'inquiry_details.inqd_job_description_id',
                'job_descriptions.job_description',
                'inquiry_details.inqd_part_id',
                'inquiry_details.inqd_part_no',
                // DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
                'inquiry_details.inqd_process_at',
                'inquiry_details.inqd_quantity',
                'inquiry_details.inqd_unit_id',
                'unit.unit',
                'inquiry_details.inqd_remark',
                'inquiry.inq_sp_note',


        ])
            ->join('inquiry_details', 'inquiry_details.inq_id', '=', 'inquiry.inq_id')
            ->join('customers', 'customers.id', '=', 'inquiry.inq_customer_id')
            ->leftJoin('type_of_job', 'type_of_job.id', '=', 'inquiry_details.inqd_type_of_job_id')
            ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'inquiry_details.inqd_job_description_id')
            //>leftJoin('part', 'part.part_id', '=', 'inquiry_details.inqd_part_id')
            ->leftJoin('unit', 'unit.id', '=', 'inquiry_details.inqd_unit_id')
            ->leftJoin('estimation_costing', 'estimation_costing.ec_inqd_id', '=', 'inquiry_details.inqd_id')
            ->whereNotIn('inquiry_details.inqd_id',$used_quot_inqd_ids)
            ->whereNotIn('inquiry_details.inqd_id',$used_sc_inqd_ids)
            ->whereIn('inquiry.year_id', $yearIds)

            // 🔥 feasibility condition
            ->where(function ($q) {
                $q->where('inquiry_details.inqd_feasibility_required', 'No')
                ->orWhere(function ($q2) {
                    $q2->where('inquiry_details.inqd_feasibility_required', 'Yes')
                        ->whereNotNull('estimation_costing.ec_inqd_id');
                });
            })
            ->get();

        if($inq_sc_data != null)
        {
            $inq_sc_data->transform(function ($item) {
                if($item->inq_date != null)
                {
                    $item->inq_date = Date::createFromFormat('Y-m-d', $item->inq_date)->format('d/m/Y');
                }
                return $item;
            });
        }

        if($inq_sc_data != null)
        {
            return response()->json([
                'response_code' => '1',
                'inq_sc_data' => $inq_sc_data
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'inq_sc_data' => []
            ]);
        }

    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'inq_sc_date' => 'required',
        ],
        [
            'inq_sc_date.required' => 'Please Enter Inquiry Short Close Date',
        ]);

        $year_data = getCurrentYearData();
        DB::beginTransaction();
        try
        {
            $request->inq_sc_data = json_decode($request->inq_sc_data,true);
            if(isset($request->inq_sc_data) && !empty($request->inq_sc_data))
            {
                foreach($request->inq_sc_data as $ctKey => $ctVal)
                {

                    if($ctVal != null)
                    {
                        $inq_sc_data =  InquiryShortClose::create([

                            'inq_sc_inqd_id'=> (isset($ctVal['inqd_id']) &&  $ctVal['inqd_id'] != "") ? $ctVal['inqd_id'] : null,

                            'inq_sc_date'=> Date::createFromFormat('d/m/Y', $request->inq_sc_date)->format('Y-m-d'),

                            'inq_sc_qty'=>(isset($ctVal['inq_sc_qty']) && $ctVal['inq_sc_qty'] > 0) ? $ctVal['inq_sc_qty'] : null,

                            'inq_sc_regret_reason_id' => $request->inq_sc_regret_reason_id,

                            'inq_sc_special_note' => $request->inq_sc_special_note,

                            'year_id' => $year_data->id,

                            'company_id' => Auth::user()->company_id,

                            'created_by' => Auth::user()->id,

                            'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                        ]);
                    }
                }
                DB::commit();
                return response()->json([
                    'response_code' => '1',
                    'response_message' => getResponseMessage('store_success'),
                ]);
            }
            else
            {
                DB::rollBack();
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('store_error'),
                ]);
            }
        }
        catch(\Exception $e)
        {
            report($e);
            DB::rollBack();
            return response()->json([
                'response_code' => '0',
                'response_message' => getResponseMessage('store_error'),
                'original_error' => $e->getMessage()
            ]);
        }
    }

    public function destroy(Request $request)
    {
        try
        {
            InquiryShortClose::where('inq_sc_id',$request->id)->delete();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        }
        catch(\Exception $e)
        {
            report($e);
            if(isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451)
            {
                $error_msg = "This Is Used Somewhere, You Can't Delete";
            }
            else
            {
                $error_msg = getResponseMessage('delete_error');
            }

            return response()->json([
                'response_code' => '0',
                'response_message' => $error_msg,
            ]);
        }
    }
}