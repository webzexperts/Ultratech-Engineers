<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Transaction\OrderAcceptance;
use App\Models\Transaction\OrderAcceptanceDetails;
use App\Models\Transaction\PlanningManagement;
use App\Models\Transaction\PlanningManagementDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use App\Models\File;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


class PlanningManagementController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-planning_management');
    }

    public function index(PlanningManagementDetails $oa_data ,Request $request, DataTables $datatables)
    {
        // dd('30');
        $year_data = getCurrentYearData();
        $pm_data = PlanningManagementDetails::select([
            'planning_management.pm_id',
            'planning_management.pm_number',
            'planning_management.pm_date',
            'planning_management.pm_process_at',
            'customers.customer_code',
            'customers.customer',
            'order_acceptance.oa_date',
            'order_acceptance.oa_number',
            'order_acceptance.oa_id',
            'order_acceptance.oa_po_number',
            'order_acceptance.oa_po_date',
            'order_acceptance.oa_special_note',
            'order_acceptance.oa_type_id',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'oa_rate_unit.unit as oa_rate_unit_name',
            'oa_qty_unit.unit as oa_qty_unit_name',
            'order_acceptance_details.oad_test_method_id',
            'order_acceptance_details.oad_process_at_id',
            'order_acceptance_details.oad_qty',
            'order_acceptance_details.oad_rate_unit',
            'order_acceptance_details.oad_remark',
            'planning_management_details.pmd_plan_qty',
            'planning_management.pm_completion_avrg_period',
            'planning_management.created_on',
            'planning_management.created_by',
            'planning_management.last_by',
            'planning_management.last_on'
        ])
        ->leftJoin('planning_management','planning_management.pm_id','=','planning_management_details.pmd_pm_id')
        ->leftJoin('order_acceptance_details','order_acceptance_details.oad_id','=','planning_management_details.pmd_oad_id')
        ->leftJoin('order_acceptance','order_acceptance.oa_id','=','order_acceptance_details.oad_oa_id')
        ->leftJoin('customers','customers.id','=','order_acceptance.oa_customer_id')
        ->leftJoin('type_of_job','type_of_job.id','=','order_acceptance_details.oad_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','order_acceptance_details.oad_job_desc_id')
        ->leftJoin('part','part.part_id','=','order_acceptance_details.oad_part_id')
        ->leftJoin('unit as oa_qty_unit','oa_qty_unit.id','=','order_acceptance_details.oad_unit_id')
        ->leftJoin('unit as oa_rate_unit','oa_rate_unit.id','=','order_acceptance_details.oad_rate_unit_id')

        ->where('planning_management.year_id', $year_data->id);
        // dd($pm_data->get());

        $dataTable = DataTables::of($pm_data)
        ->filterColumn('part.part', function($query, $keyword) {
            $keyword = trim($keyword);
            $query->whereRaw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) LIKE ?", ["%{$keyword}%"]);
        })
        ->editColumn('oa_date', function($pm_data){
            if ($pm_data->oa_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $pm_data->oa_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('order_acceptance.oa_date', function ($q, $k) {
            applyDate($q, $k, 'order_acceptance.oa_date');
        })
        ->editColumn('pm_date', function($pm_data){
            if ($pm_data->pm_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $pm_data->pm_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('planning_management.pm_date', function ($q, $k) {
            applyDate($q, $k, 'planning_management.pm_date');
        })

         ->editColumn('pmd_plan_qty', function($pm_data) {
            return $pm_data->pmd_plan_qty > 0 ? number_format((float)$pm_data->pmd_plan_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('planning_management_details.pmd_plan_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('pmd_plan_qty')
                    ->orWhere('pmd_plan_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(pmd_plan_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('pmd_plan_qty', 'like', "%{$search}%");
            }
        })
        ->filterColumn('planning_management.pm_number', function($query, $keyword) {
            applynumber($query, $keyword, 'planning_management.pm_number');
        })
        ->filterColumn('order_acceptance.oa_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'order_acceptance.oa_number');
        })
        
        ->addColumn('options',function($quotation_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("planning_management", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-planning_management"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("planning_management", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'planning_management');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $year_data = getCurrentYearData();
        $existNumber = PlanningManagement::where([['pm_sequence',  $request->pm_sequence],['pm_number',$request->pm_number],['year_id',$year_data->id]])->first();
        
        if($existNumber)
        {
            $latestNo = $this->getLatestPMNumber($request);
            $tmp =  $latestNo->getContent();
            $area = json_decode($tmp, true);
            $pm_number =   $area['latest_no'];
            $pm_sequence = $area['number'];
        }
        else
        {
            $pm_number = $request->pm_number;
            $pm_sequence = $request->pm_sequence;
        }

        DB::beginTransaction();
        try
        {
            $planning_management_data =  PlanningManagement::create([
                'pm_number'             => $pm_number ?? null,
                'pm_sequence'           => $pm_sequence ?? null,
                'pm_date'               => isset($request->pm_date) ? Date::createFromFormat('d/m/Y', $request->pm_date)->format('Y-m-d') : null,
                'pm_process_at'              => $request->pm_process_at ?? null,
                'pm_completion_avrg_period'  => $request->pm_completion_avrg_period ?? null,
                'year_id'               => $year_data->id,
                'company_id'            => Auth::user()->company_id,
                'created_on'            => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'            => Auth::user()->id,
            ]);


            $pm_details_data = $request->pm_details_data = json_decode($request->pm_details_data, true);
            if(!empty($pm_details_data))
            {
                // dd($planning_management_data->pm_id);
                foreach($pm_details_data as $ctKey => $ctVal)
                {
                    if($ctVal != null)
                    {
                        $pm_details_data = PlanningManagementDetails::create([
                            'pmd_pm_id'    => $planning_management_data->pm_id,
                            'pmd_oad_id'   => $ctVal['pmd_oad_id'],
                            'pmd_plan_qty' => $ctVal['pmd_plan_qty'],
                            'pmd_status'   => 'Y',
                        ]);
                    }
                }
            }

            if($planning_management_data->save())
            {
                DB::commit();
                return response()->json([
                    'response_code' => '1',
                    'response_message' => getResponseMessage('store_success'),
                ]);
            }
            else
            {
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

    public function edit(Request $request)
    {
        $pm_data =  DB::select('CALL planning_management_master(?)', [$request->id]);
        if(!empty($pm_data))
        {
            $pm_data = $pm_data[0];
            $pm_data->pm_date = $pm_data->pm_date != "" ? Date::createFromFormat('Y-m-d',  $pm_data->pm_date)->format('d/m/Y') : "";
            
            if(isset($pm_data->company_logo))
            {
                $pm_data->company_logo = base64_encode($pm_data->company_logo);
            }
            
        }
        
        $pm_details_data = DB::select('CALL planning_management_details(?)', [$request->id]);

        if($pm_details_data){
            foreach($pm_details_data as $dKey => $dVal)
            {
                $dVal->oa_date = $dVal->oa_date != "" ? Date::createFromFormat('Y-m-d',  $dVal->oa_date)->format('d/m/Y') : "";

                $dVal->oa_po_date = $dVal->oa_po_date != "" ? Date::createFromFormat('Y-m-d',  $dVal->oa_po_date)->format('d/m/Y') : "";

                $dVal->pend_oa_qty = $dVal->pend_oa_qty + $dVal->pmd_plan_qty;
            }
        }

        if($pm_data)
        {
            return response()->json([
                'pm_data'         => $pm_data,
                'pm_details_data' => $pm_details_data,
                'response_code'   => '1',
                'response_message'=> '',
            ]);
        }
        else
        {
            return response()->json([
                'response_code'    => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    public function update(Request $request)
    {
        $year_data = getCurrentYearData();

        $pm = PlanningManagement::find($request->id);

        if (!$pm) {
            return response()->json([
                'response_code' => '0',
                'response_message' => getResponseMessage('update_error'),
            ]);
        }

        DB::beginTransaction();
        try
        {
            // Update main DC
            $pm->update([
                'pm_sequence' => $request->pm_sequence ?? '',
                'pm_number'   => $request->pm_number ?? '',
                'pm_date'     => isset($request->pm_date) ? Date::createFromFormat('d/m/Y', $request->pm_date)->format('Y-m-d') : '',
                'pm_process_at'   => $request->pm_process_at ?? '',
                'pm_completion_avrg_period'   => $request->pm_completion_avrg_period ?? '',
                'year_id'            => $year_data->id,
                'company_id'         => Auth::user()->company_id,
                'modified_on'        => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'modified_by'        => Auth::user()->id,
            ]);

            $pm_details_data = json_decode($request->pm_details_data ?? '[]', true);

            if (!empty($pm_details_data)) {

                $incomingIds = [];

                foreach ($pm_details_data as $ctVal) {

                    if ($ctVal == null) continue;

                    if ($ctVal['pmd_id'] != '' &&  $ctVal['pmd_id'] != "0") {
                        $detail = PlanningManagementDetails::find($ctVal['pmd_id']);

                        if ($detail) {
                            $detail->update([
                                'pmd_plan_qty'  => $ctVal['pmd_plan_qty'] ?? '',
                                'pmd_status'  => 'Y',
                            ]);

                            $incomingIds[] = $detail->pmd_id;
                        }

                    } 
                    // Else → CREATE
                    else {

                        $detail = PlanningManagementDetails::create([
                            'pmd_pm_id'      => $pm->pm_id,
                            'pmd_oad_id'     => $ctVal['pmd_oad_id'] ?? NULL,
                            'pmd_plan_qty'  => $ctVal['pmd_plan_qty'] ?? '',
                            'pmd_status'  => 'Y',

                        ]);

                        $incomingIds[] = $detail->pmd_id;
                    }
                }

                // 👉 Delete records not present in request
                PlanningManagementDetails::where('pmd_pm_id', $pm->pm)
                    ->whereNotIn('pmd_id', $incomingIds)
                    ->delete();

            } else {
                PlanningManagementDetails::where('pmd_pm_id', $pm->pm_id)->delete();
            }


            DB::commit();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('update_success'),
            ]);

        } catch (\Exception $e) {
            report($e);
            DB::rollBack();
            return response()->json([
                'response_code' => '0',
                'response_message' => getResponseMessage('update_error'),
                'original_error' => $e->getMessage()
            ]);
        }
    }

    public function getPendingOAListForPM(Request $request)
    {
        $yearIds     = getCompanyYearIdsToTill();
        $planningManagementId = $request->id;


        if (!empty($planningManagementId)) {
           $editData = PlanningManagementDetails::select(

                    'order_acceptance.oa_number',
                    'customers.customer_code',
                    'customers.customer',
                    'order_acceptance.oa_id',
                    'order_acceptance.oa_date',
                    'order_acceptance.oa_po_number',
                    'order_acceptance.oa_po_date',
                    'order_acceptance.oa_special_note',
                    'order_acceptance.oa_type_id',
                    'order_acceptance_details.oad_test_method_id',
                    'order_acceptance_details.oad_type_of_job_id',
                    'type_of_job.type_of_job',
                    'order_acceptance_details.oad_job_desc_id',
                    'job_descriptions.job_description',
                    'order_acceptance_details.oad_part_id',
                    \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
                    'order_acceptance_details.oad_qty',
                    'order_acceptance_details.oad_id as pmd_oad_id',
                    'order_acceptance_details.oad_unit_id',
                    'order_acceptance_details.oad_remark',
                    'order_acceptance_details.oad_process_at_id',
                    'unit.unit',
                    'planning_management_details.pmd_id',
                    'planning_management_details.pmd_oad_id',
                    'planning_management_details.pmd_plan_qty',
                )
                ->leftJoin('order_acceptance_details', 'order_acceptance_details.oad_id', '=', 'planning_management_details.pmd_oad_id')
                ->leftJoin('order_acceptance', 'order_acceptance.oa_id', '=', 'order_acceptance_details.oad_oa_id')

                ->join('customers', 'customers.id', '=', 'order_acceptance.oa_customer_id')
                ->leftJoin('type_of_job', 'type_of_job.id', '=', 'order_acceptance_details.oad_type_of_job_id')
                ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'order_acceptance_details.oad_job_desc_id')
                ->leftJoin('part', 'part.part_id', '=', 'order_acceptance_details.oad_part_id')
                ->leftJoin('unit', 'unit.id', '=', 'order_acceptance_details.oad_unit_id')
                ->where('planning_management_details.pmd_pm_id', $planningManagementId)
                ->whereIn('order_acceptance.year_id', $yearIds)
                ->get();
        }

        // dd($request->all());
       
        $pendingData =  OrderAcceptance::select(
                'order_acceptance.oa_number',
                'customers.customer_code',
                'customers.customer',
                'order_acceptance.oa_date',
                'order_acceptance.oa_po_number',
                'order_acceptance.oa_po_date',
                'order_acceptance.oa_special_note',
                'order_acceptance.oa_type_id',
                'order_acceptance_details.oad_test_method_id',
                'order_acceptance_details.oad_type_of_job_id',
                'type_of_job.type_of_job',
                'order_acceptance_details.oad_job_desc_id',
                'job_descriptions.job_description',
                'order_acceptance_details.oad_part_id',
                \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
                'order_acceptance_details.oad_qty',
                'order_acceptance_details.oad_id as pmd_oad_id',
                'order_acceptance_details.oad_unit_id',
                'order_acceptance_details.oad_remark',
                'order_acceptance_details.oad_process_at_id',
                'unit.unit',
                DB::raw("(
                    SELECT order_acceptance_details.oad_qty -
                    (
                        SELECT IFNULL(SUM(pmd.pmd_plan_qty),0)
                        FROM planning_management_details AS pmd
                        WHERE pmd.pmd_oad_id = order_acceptance_details.oad_id
                    )
                ) as pend_oa_qty")
            )
            ->join('order_acceptance_details', 'order_acceptance_details.oad_oa_id', '=', 'order_acceptance.oa_id')
            ->join('customers', 'customers.id', '=', 'order_acceptance.oa_customer_id')
            ->leftJoin('type_of_job', 'type_of_job.id', '=', 'order_acceptance_details.oad_type_of_job_id')
            ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'order_acceptance_details.oad_job_desc_id')
            ->leftJoin('part', 'part.part_id', '=', 'order_acceptance_details.oad_part_id')
            ->leftJoin('unit', 'unit.id', '=', 'order_acceptance_details.oad_unit_id')
            ->having('pend_oa_qty', '>', 0)
            ->whereIn('order_acceptance.year_id', $yearIds)
            ->get();

            // dd($pendingData);

      
        // $data = $editData
        //     ->concat($pendingData)
        //     ->values();

        /*if(isset($editData)){
            $data = collect($pendingData)->merge($editData);
            $grouped = $data->groupBy('pmd_oad_id');    
            

            $merged = $grouped->map(function ($items) {
                return $items->reduce(function ($carry, $item) {
                    if (!$carry) {
                        return $item;
                    }
                    $carry->pend_oa_qty += (float) $item->pmd_plan_qty;
                    return $carry;
                });
            });
            $pendingData = $merged->values();   

        }*/ // commented on 10-03-2026 pend_oa_qty not getting ok

        if (!empty($planningManagementId) && isset($editData)) {

            // Sum planned qty per OA detail
            $plannedQty = $editData->groupBy('pmd_oad_id')->map(function ($items) {
                return $items->sum('pmd_plan_qty');
            });

            foreach ($pendingData as $row) {

                if (isset($plannedQty[$row->pmd_oad_id])) {
                    $row->pend_oa_qty = $row->pend_oa_qty + $plannedQty[$row->pmd_oad_id];
                }
            }

            // Add edit rows if not present in pending
            $pendingIds = $pendingData->pluck('pmd_oad_id')->toArray();

            foreach ($editData as $row) {
                if (!in_array($row->pmd_oad_id, $pendingIds)) {

                    $row->pend_oa_qty = $row->pmd_plan_qty;
                    $pendingData->push($row);
                }
            }
        }

        $pendingData = $pendingData->sortBy('pmd_oad_id')->values();


       
        foreach ($pendingData as $row) {
            if (!empty($row->oa_po_date)) {
                $row->oa_po_date = \Carbon\Carbon::parse($row->oa_po_date)
                    ->format('d/m/Y');
            }
            if (!empty($row->oa_date)) {
                $row->oa_date = \Carbon\Carbon::parse($row->oa_date)
                    ->format('d/m/Y');
            }
        }

        return response()->json([
            'response_code' => 1,
            'oa_data'      => $pendingData
        ]);
    }

    /*public function getPendingOAForPM(Request $request)
    {
        // dd($request->all());
        $oadIds = array_filter(explode(',', $request->oad_ids));
        $planningManagementId = $request->id ?? 0;

        $edit_data = collect();
        if (!empty($planningManagementId) && !empty($oadIds)) {

            $edit_data = PlanningManagementDetails::select([
                     'order_acceptance.oa_number',
                    'customers.customer_code',
                    'customers.customer',
                    'order_acceptance.oa_id',
                    'order_acceptance.oa_date',
                    'order_acceptance.oa_po_number',
                    'order_acceptance.oa_po_date',
                    'order_acceptance.oa_special_note',
                    'order_acceptance.oa_type_id',
                    'order_acceptance_details.oad_test_method_id',
                    'order_acceptance_details.oad_type_of_job_id',
                    'type_of_job.type_of_job',
                    'order_acceptance_details.oad_job_desc_id',
                    'job_descriptions.job_description',
                    'order_acceptance_details.oad_part_id',
                    \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
                    'order_acceptance_details.oad_qty',
                    'order_acceptance_details.oad_id as pmd_oad_id',
                    'order_acceptance_details.oad_unit_id',
                    'order_acceptance_details.oad_remark',
                    'order_acceptance_details.oad_process_at_id',
                    'unit.unit',
                    'order_acceptance_details.oad_id as pmd_oad_id',
                    'planning_management_details.pmd_id',
                    'planning_management_details.pmd_plan_qty',

                        DB::raw("(
                        SELECT order_acceptance_details.oad_qty -
                        (
                            SELECT IFNULL(SUM(pmd.pmd_plan_qty),0)
                            FROM planning_management_details AS pmd
                            WHERE pmd.pmd_oad_id = order_acceptance_details.oad_id
                        )
                    ) as pend_oa_qty")
                ])
                ->leftJoin('order_acceptance_details', 'order_acceptance_details.oad_id', '=', 'planning_management_details.pmd_oad_id')
                ->leftJoin('order_acceptance', 'order_acceptance.oa_id', '=', 'order_acceptance_details.oad_oa_id')
                ->leftJoin('customers', 'customers.id', '=', 'order_acceptance.oa_customer_id')
                ->leftJoin('type_of_job', 'type_of_job.id', '=', 'order_acceptance_details.oad_type_of_job_id')
                ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'order_acceptance_details.oad_job_desc_id')
                ->leftJoin('unit', 'unit.id', '=', 'order_acceptance_details.oad_unit_id')
                ->leftJoin('part', 'part.part_id', '=', 'order_acceptance_details.oad_part_id')
                ->where('order_acceptance_details.oad_oa_id', $planningManagementId)
                ->whereIn('planning_management_details.pmd_oad_id', $oadIds)
                ->get();
        }

        // ========================
        // PENDING DATA (OrderAcceptance)
        // ========================
        $pending_data = OrderAcceptance::select([
                'order_acceptance.oa_number',
                'customers.customer_code',
                'customers.customer',
                'order_acceptance.oa_id',
                'order_acceptance.oa_date',
                'order_acceptance.oa_po_number',
                'order_acceptance.oa_po_date',
                'order_acceptance.oa_special_note',
                'order_acceptance.oa_type_id',
                'order_acceptance_details.oad_test_method_id',
                'order_acceptance_details.oad_type_of_job_id',
                'type_of_job.type_of_job',
                'order_acceptance_details.oad_job_desc_id',
                'job_descriptions.job_description',
                'order_acceptance_details.oad_part_id',
                \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
                'order_acceptance_details.oad_qty',
                'order_acceptance_details.oad_id as pmd_oad_id',
                'order_acceptance_details.oad_unit_id',
                'order_acceptance_details.oad_remark',
                'order_acceptance_details.oad_process_at_id',
                'unit.unit',
                     

                DB::raw("(
                    SELECT order_acceptance_details.oad_qty -
                    (
                        SELECT IFNULL(SUM(pmd.pmd_plan_qty),0)
                        FROM planning_management_details AS pmd
                        WHERE pmd.pmd_oad_id = order_acceptance_details.oad_id
                    )
                ) as pend_oa_qty")




            // DB::raw('0 as oad_id'),
        ])
        ->leftJoin('order_acceptance_details', 'order_acceptance_details.oad_oa_id', '=', 'order_acceptance.oa_id')
        ->leftJoin('customers', 'customers.id', '=', 'order_acceptance.oa_customer_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'order_acceptance_details.oad_type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'order_acceptance_details.oad_job_desc_id')
        ->leftJoin('part', 'part.part_id', '=', 'order_acceptance_details.oad_part_id')
        ->leftJoin('unit', 'unit.id', '=', 'order_acceptance_details.oad_unit_id')
        ->whereIn('order_acceptance_details.oad_id', $oadIds)
        ->get();

        // dd($pending_data);

        if ($planningManagementId) {

            // EDIT MODE ONLY
            $data = $pending_data->merge($edit_data);

            $pending_data = $data->groupBy('pmd_oad_id')->map(function ($items) {
                return $items->reduce(function ($carry, $item) {
                    if (!$carry) {
                        return $item;
                    }

                    $carry->pend_oa_qty += $item->pmd_plan_qty ?? 0;
                    return $carry;
                });
            })->values();

        }


        // ========================
        // DATE FORMAT
        // ========================
        foreach ($pending_data as $row) {
            if (!empty($row->oa_date)) {
                $row->oa_date = \Carbon\Carbon::parse($row->oa_date)->format('d/m/Y');
            }
            if (!empty($row->oa_po_date)) {
                $row->oa_po_date = \Carbon\Carbon::parse($row->oa_po_date)->format('d/m/Y');
            }
        }

        return response()->json([
            'response_code' => 1,
            'oa_data' => $pending_data
        ]);
    }*/ // commented on 10-03-2026 pend_oa_qty not getting ok

    public function getPendingOAForPM(Request $request)
    {
        $oadIds = array_filter(explode(',', $request->oad_ids));
        $planningManagementId = $request->id ?? 0;

        $edit_data = collect();

        // ========================
        // EDIT DATA
        // ========================
        if (!empty($planningManagementId) && !empty($oadIds)) {

            $edit_data = PlanningManagementDetails::select([
                'order_acceptance.oa_number',
                'customers.customer_code',
                'customers.customer',
                'order_acceptance.oa_id',
                'order_acceptance.oa_date',
                'order_acceptance.oa_po_number',
                'order_acceptance.oa_po_date',
                'order_acceptance.oa_special_note',
                'order_acceptance.oa_type_id',
                'order_acceptance_details.oad_test_method_id',
                'order_acceptance_details.oad_type_of_job_id',
                'type_of_job.type_of_job',
                'order_acceptance_details.oad_job_desc_id',
                'job_descriptions.job_description',
                'order_acceptance_details.oad_part_id',
                \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
                'order_acceptance_details.oad_qty',
                'order_acceptance_details.oad_id as pmd_oad_id',
                'order_acceptance_details.oad_unit_id',
                'order_acceptance_details.oad_remark',
                'order_acceptance_details.oad_process_at_id',
                'unit.unit',
                'planning_management_details.pmd_id',
                'planning_management_details.pmd_plan_qty',

                DB::raw("(
                    SELECT order_acceptance_details.oad_qty -
                    (
                        SELECT IFNULL(SUM(pmd.pmd_plan_qty),0)
                        FROM planning_management_details AS pmd
                        WHERE pmd.pmd_oad_id = order_acceptance_details.oad_id
                    )
                ) as pend_oa_qty")

            ])
            ->leftJoin('order_acceptance_details','order_acceptance_details.oad_id','=','planning_management_details.pmd_oad_id')
            ->leftJoin('order_acceptance','order_acceptance.oa_id','=','order_acceptance_details.oad_oa_id')
            ->leftJoin('customers','customers.id','=','order_acceptance.oa_customer_id')
            ->leftJoin('type_of_job','type_of_job.id','=','order_acceptance_details.oad_type_of_job_id')
            ->leftJoin('job_descriptions','job_descriptions.id','=','order_acceptance_details.oad_job_desc_id')
            ->leftJoin('unit','unit.id','=','order_acceptance_details.oad_unit_id')
            ->leftJoin('part','part.part_id','=','order_acceptance_details.oad_part_id')
            ->where('planning_management_details.pmd_pm_id',$planningManagementId)
            ->whereIn('planning_management_details.pmd_oad_id',$oadIds)
            ->get();
        }


        // ========================
        // PENDING DATA
        // ========================
        $pending_data = OrderAcceptance::select([
            'order_acceptance.oa_number',
            'customers.customer_code',
            'customers.customer',
            'order_acceptance.oa_id',
            'order_acceptance.oa_date',
            'order_acceptance.oa_po_number',
            'order_acceptance.oa_po_date',
            'order_acceptance.oa_special_note',
            'order_acceptance.oa_type_id',
            'order_acceptance_details.oad_test_method_id',
            'order_acceptance_details.oad_type_of_job_id',
            'type_of_job.type_of_job',
            'order_acceptance_details.oad_job_desc_id',
            'job_descriptions.job_description',
            'order_acceptance_details.oad_part_id',
            \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'order_acceptance_details.oad_qty',
            'order_acceptance_details.oad_id as pmd_oad_id',
            'order_acceptance_details.oad_unit_id',
            'order_acceptance_details.oad_remark',
            'order_acceptance_details.oad_process_at_id',
            'unit.unit',

            DB::raw('0 as pmd_plan_qty'),

            DB::raw("(
                SELECT order_acceptance_details.oad_qty -
                (
                    SELECT IFNULL(SUM(pmd.pmd_plan_qty),0)
                    FROM planning_management_details AS pmd
                    WHERE pmd.pmd_oad_id = order_acceptance_details.oad_id
                )
            ) as pend_oa_qty")

        ])
        ->leftJoin('order_acceptance_details','order_acceptance_details.oad_oa_id','=','order_acceptance.oa_id')
        ->leftJoin('customers','customers.id','=','order_acceptance.oa_customer_id')
        ->leftJoin('type_of_job','type_of_job.id','=','order_acceptance_details.oad_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','order_acceptance_details.oad_job_desc_id')
        ->leftJoin('part','part.part_id','=','order_acceptance_details.oad_part_id')
        ->leftJoin('unit','unit.id','=','order_acceptance_details.oad_unit_id')
        ->whereIn('order_acceptance_details.oad_id',$oadIds)
        ->get();


        // ========================
        // EDIT MODE MERGE
        // ========================
        if ($planningManagementId) {

            $data = $pending_data->merge($edit_data);

            $pending_data = $data->groupBy('pmd_oad_id')->map(function ($items) {

                return $items->reduce(function ($carry, $item) {

                    if (!$carry) {
                        return $item;
                    }

                    $carry->pend_oa_qty = ($carry->pend_oa_qty ?? 0) + ($item->pmd_plan_qty ?? 0);

                    return $carry;

                });

            })->values();
        }


        // ========================
        // DATE FORMAT
        // ========================
        foreach ($pending_data as $row) {

            if (!empty($row->oa_date)) {
                $row->oa_date = \Carbon\Carbon::parse($row->oa_date)->format('d/m/Y');
            }

            if (!empty($row->oa_po_date)) {
                $row->oa_po_date = \Carbon\Carbon::parse($row->oa_po_date)->format('d/m/Y');
            }
        }


        return response()->json([
            'response_code' => 1,
            'oa_data' => $pending_data
        ]);
    }

    public function destroy(Request $request)
    {
        try
        {
            PlanningManagement::where('pm_id',$request->id)->delete();
            PlanningManagementDetails::where('pmd_pm_id',$request->id)->delete();
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


    public function getLatestPMNumber(Request $request)
    {
        $modal  =  PlanningManagement::class;
        $sequence = 'pm_sequence';           
        $sup_num_format = getLatestSequence($modal,$sequence);   

        return response()->json([
          'response_code' => 1,
          'latest_no'     => $sup_num_format['format'],
          'number'        => $sup_num_format['isFound'],
      ]);
    }

}
