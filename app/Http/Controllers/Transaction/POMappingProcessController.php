<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaterialInspection;
use App\Models\MaterialInward;
use App\Models\MaterialInwardDetails;
use App\Models\Transaction\POMappingProcess;
use App\Models\Transaction\POMappingProcessDetails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use App\Models\File;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class POMappingProcessController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-po_mapping_process');
    }

    public function index(Request $request)
    {
        $year_data = getCurrentYearData();

        $query = DB::table('po_mapping_process as pmp')

            ->select(
                'pmp.po_mp_id',
                'pmp.po_mp_number',
                'pmp.po_mp_date',
                'pmp.po_mp_special_note',

                'customers.customer_code',
                'customers.customer',

                'oa.oa_type_id',
                'oa.oa_number',
                'oa.oa_date',
                'oa.oa_po_number',
                'oa.oa_po_date',

                'job_descriptions.job_description',
                \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),

                'oad.oad_qty',
                'oad_unit.unit as oad_unit',

                'pmd.pmd_plan_qty',

                'type_of_job.type_of_job',

                'mid.mid_test_method_id',

                'pmpd.po_mpd_oa_mapping_qty as total_oa_mapping_qty',

                'inward_unit.unit as inward_unit',

                'mapped_by.person_name as mapped_by_name',
                'creator.user_name as created_by',
                'updater.user_name as last_by',

                'pmp.created_on',
                'pmp.last_on'
            )

            ->leftJoin('po_mapping_process_details as pmpd','pmpd.po_mpd_po_mp_id','=','pmp.po_mp_id')

            ->leftJoin('planning_management_details as pmd','pmd.pmd_id','=','pmpd.po_mpd_pmd_id')

            ->leftJoin('order_acceptance_details as oad','oad.oad_id','=','pmd.pmd_oad_id')

            ->leftJoin('order_acceptance as oa','oa.oa_id','=','oad.oad_oa_id')

            ->leftJoin('planning_management as pm','pm.pm_id','=','pmd.pmd_pm_id')

            ->leftJoin('material_inspection as mi','mi.mins_id','=','pmpd.po_mpd_mins_id')

            ->leftJoin('material_inward_details as mid','mid.mid_id','=','mi.mins_mid_id')

            ->leftJoin('material_inward as miw','miw.mi_id','=','mid.mid_mi_id')

            ->leftJoin('customers','customers.id','=','miw.mi_customer_id')

            ->leftJoin('type_of_job','type_of_job.id','=','mid.mid_type_of_job_id')

            ->leftJoin('job_descriptions','job_descriptions.id','=','mid.mid_job_desc_id')

            ->leftJoin('part','part.part_id','=','mid.mid_part_id')

            ->leftJoin('unit as inward_unit','inward_unit.id','=','mid.mid_qty_unit_id')

            ->leftJoin('unit as oad_unit','oad_unit.id','=','oad.oad_unit_id')

            ->leftJoin('admin as mapped_by','mapped_by.id','=','pmp.po_mp_mapped_by_id')

            ->leftJoin('admin as creator','creator.id','=','pmp.created_by')

            ->leftJoin('admin as updater','updater.id','=','pmp.last_by')

            ->where('pmp.year_id',$year_data->id)
            ->where('pmp.company_id',Auth::user()->company_id);
        return DataTables::of($query)
            ->filterColumn('part.part', function($query, $keyword) {
                $keyword = trim($keyword);
                $query->whereRaw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) LIKE ?", ["%{$keyword}%"]);
            })
            ->editColumn('po_mp_date', function ($row) {
                return $row->po_mp_date 
                    ? \Carbon\Carbon::parse($row->po_mp_date)->format(DATE_FORMAT) 
                    : '';
            })

            ->editColumn('oa_date', function ($row) {
                return $row->oa_date 
                    ? \Carbon\Carbon::parse($row->oa_date)->format(DATE_FORMAT) 
                    : '';
            })

            ->editColumn('oa_po_date', function ($row) {
                return $row->oa_po_date 
                    ? \Carbon\Carbon::parse($row->oa_po_date)->format(DATE_FORMAT) 
                    : '';
            })

            ->editColumn('created_on', function ($row) {
                return $row->created_on 
                    ? \Carbon\Carbon::parse($row->created_on)->format(DATE_TIME_FORMAT) 
                    : '';
            })

            ->editColumn('last_on', function ($row) {
                return $row->last_on 
                    ? \Carbon\Carbon::parse($row->last_on)->format(DATE_TIME_FORMAT) 
                    : '';
            })
            ->addColumn('options',function($quotation_data){
                $action = '<div class="dropdown d-inline-block">
                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-more-fill align-middle"></i>
                    </button>
                    <ul class="dropdown-menu">';

                    if (hasAccess("po_mapping_process", "edit")) {
                        $action .= '<li><a class="dropdown-item edit-item-btn edit-po_mapping_process"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                    }

                    if (hasAccess("po_mapping_process", "delete")) {
                        $action .= '<li> <a class="dropdown-item remove-item-btn">
                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                        </a>
                                    </li>';
                    }
                $action .= '</ul></div>';
                return $action;
            })

            ->rawColumns(['options','last_by','last_on','created_by','created_on'])

            ->make(true);
    }

    public function pomappingprocessLNRData()
    {
        $lnr_data = POMappingProcess::select('pomp_id','pomp_mapped_by_user_id')->orderBy('pomp_id','desc')->first();
        return response()->json([
            'response_code' => 1,
            'lnr_data'       => $lnr_data,
        ]);
    }

    public function getLatestPOMappingProcessNumber(Request $request)
    {
        $modal  =  POMappingProcess::class;
        $sequence = 'po_mp_sequence';           
        $sup_num_format = getLatestSequence($modal,$sequence);   
       
        return response()->json([
          'response_code' => 1,
          'latest_no'     => $sup_num_format['format'],
          'number'        => $sup_num_format['isFound'],
      ]);
    }

    public function getMaterialInspListForPOMP(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();

        $material_inspection_data = MaterialInspection::select(
            'material_inspection.mins_id',
            'material_inspection.mins_number',
            'material_inspection.mins_date',
            'material_inspection.mins_insp_qty',
            'material_inspection.mins_special_note',

            'material_inward.mi_customer_id',
            'customers.customer_code',
            'customers.customer',
            'material_inward.mi_number',
            'material_inward.mi_date',
            'material_inward.mi_customer_id',
            'material_inward.mi_challan_number',
            'material_inward.mi_challan_date',

            'material_inward_details.mid_type_of_job_id',
            'type_of_job.type_of_job',

            'material_inward_details.mid_job_desc_id',
            'job_descriptions.job_description',

            'material_inward_details.mid_test_method_id',
            'material_inward_details.mid_qty',

            'material_inward_details.mid_part_id',
            \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),

            'material_inspection.mins_insp_qty',
            'oad.oad_id',
            'pm.pm_process_at',
            'material_inspection.mins_result',
            'pmd.pmd_id',
            'pm.pm_id',
            'unit.unit',
            DB::raw("
                material_inspection.mins_insp_qty
                - COALESCE(
                    (SELECT SUM(po_mpd_inward_mapping_qty)
                    FROM po_mapping_process_details
                    WHERE po_mpd_mins_id = material_inspection.mins_id
                    ), 0
                ) AS mid_pend_qty
            ")
        )

        ->leftJoin('material_inward_details','material_inward_details.mid_id','=','material_inspection.mins_mid_id')
        ->leftJoin('material_inward','material_inward.mi_id','=','material_inward_details.mid_mi_id')
        ->leftJoin('customers','customers.id','=','material_inward.mi_customer_id')
        ->leftJoin('type_of_job','type_of_job.id','=','material_inward_details.mid_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','material_inward_details.mid_job_desc_id')
        ->leftJoin('part','part.part_id','=','material_inward_details.mid_part_id')
        ->leftJoin('unit','unit.id','=','material_inward_details.mid_qty_unit_id')
        // Order Acceptance
        ->join('order_acceptance as oa','oa.oa_customer_id','=','material_inward.mi_customer_id')
        // // Order Acceptance Details
        ->join('order_acceptance_details as oad', function($join){
            $join->on('oad.oad_oa_id','=','oa.oa_id')
                ->on('oad.oad_type_of_job_id','=','material_inward_details.mid_type_of_job_id')
                ->on('oad.oad_job_desc_id','=','material_inward_details.mid_job_desc_id')
                ->on('oad.oad_test_method_id','=','material_inward_details.mid_test_method_id');
        })
        // // Planning Management
        ->join('planning_management_details as pmd','pmd.pmd_oad_id','=','oad.oad_id')
        ->join('planning_management as pm','pm.pm_id','=','pmd.pmd_pm_id')
        ->where('pm.pm_process_at',"In-House")
        ->where('material_inspection.mins_result',"Accepted")
        ->whereIn('material_inspection.year_id',$yearIds)
        ->having('mid_pend_qty', '>', 0)
        ->get();
        // dd($material_inspection_data);
        if($material_inspection_data != null)
        {
            foreach($material_inspection_data as $cpKey => $cpVal)
            {
                if($cpVal->mins_date != null)
                {
                    $cpVal->mins_date = Date::createFromFormat('Y-m-d', $cpVal->mins_date)->format('d/m/Y');
                }

                if($cpVal->mi_date != null)
                {
                    $cpVal->mi_date = Date::createFromFormat('Y-m-d', $cpVal->mi_date)->format('d/m/Y');
                }

                if($cpVal->mi_challan_date != null)
                {
                    $cpVal->mi_challan_date = Date::createFromFormat('Y-m-d', $cpVal->mi_challan_date)->format('d/m/Y');
                }
            }
        }

        return response()->json([
            'response_code' => '1',
            'material_inspection_data' => $material_inspection_data
        ]);
    }
    public function getMaterialInspForPOMP(Request $request){

        // dd($request->all());
        $mins_ids = ($request->mins_ids) ?? [];
        $pm_ids   = ($request->pm_ids) ?? [];
        $inspection_data = [];

        if(!empty($mins_ids)) {
            $inspection_data = MaterialInspection::select(
                'material_inspection.mins_id',
                'material_inspection.mins_number',
                'material_inspection.mins_date',
                'material_inspection.mins_insp_qty',
                'material_inspection.mins_special_note',
                'material_inward.mi_number',
                'material_inward.mi_date',
                'material_inward.mi_customer_id',
                'material_inward.mi_challan_number',
                'material_inward.mi_challan_date',
                'customers.customer',
                'material_inward_details.mid_type_of_job_id',
                'type_of_job.type_of_job',
                'material_inward_details.mid_job_desc_id',
                'job_descriptions.job_description',
                'material_inward_details.mid_test_method_id',
                'material_inward_details.mid_part_id',
                \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
                'material_inward_details.mid_qty_unit_id',
                'unit.unit',
                 DB::raw("
                    material_inspection.mins_insp_qty
                    - COALESCE(
                        (SELECT SUM(po_mpd_inward_mapping_qty)
                        FROM po_mapping_process_details
                        WHERE po_mpd_mins_id = material_inspection.mins_id
                        ), 0
                    ) AS mid_pend_qty
                ")
            )
    
            ->leftJoin('material_inward_details','material_inward_details.mid_id','=','material_inspection.mins_mid_id')
            ->leftJoin('material_inward','material_inward.mi_id','=','material_inward_details.mid_mi_id')
            ->leftJoin('customers','customers.id','=','material_inward.mi_customer_id')
            ->leftJoin('type_of_job','type_of_job.id','=','material_inward_details.mid_type_of_job_id')
            ->leftJoin('job_descriptions','job_descriptions.id','=','material_inward_details.mid_job_desc_id')
            ->leftJoin('part','part.part_id','=','material_inward_details.mid_part_id')
            ->leftJoin('unit','unit.id','=','material_inward_details.mid_qty_unit_id')
            ->where('material_inspection.mins_id',$request->mins_ids)
            ->having('mid_pend_qty', '>', 0)
            ->first();
            // dd($inspection_data);
        }
        // dd($inspection_data);

       $planning_management_data = DB::table('material_inspection as mi')

            ->select(
                'oa.oa_number',
                'oa.oa_date',
                'oa.oa_type_id',
                'oa.oa_po_number',
                'oa.oa_po_date',
                'job_descriptions.job_description',
                \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
                'unit.unit as unit',
                'oad.oad_qty',
                'pm.pm_id',
                'oad.oad_remark',
                'pmd.pmd_plan_qty',
                'pmd.pmd_id',
                DB::raw("
                    pmd.pmd_plan_qty
                    - COALESCE(
                        (SELECT SUM(po_mpd_oa_mapping_qty)
                        FROM po_mapping_process_details
                        WHERE po_mpd_pmd_id = pmd.pmd_id
                        ), 0
                    ) AS pmd_pend_qty
                "),
                'material_inward_details.mid_qty',
                'inward_unit.unit as inward_unit'
            )

            /* Inspection → Inward */
            ->join('material_inward_details','material_inward_details.mid_id','=','mi.mins_mid_id')
            ->join('material_inward','material_inward.mi_id','=','material_inward_details.mid_mi_id')
            /* OA */
            ->join('order_acceptance as oa','oa.oa_customer_id','=','material_inward.mi_customer_id')
            ->join('order_acceptance_details as oad', function($join){
                $join->on('oad.oad_oa_id','=','oa.oa_id')
                    ->on('oad.oad_type_of_job_id','=','material_inward_details.mid_type_of_job_id')
                    ->on('oad.oad_job_desc_id','=','material_inward_details.mid_job_desc_id')
                    ->on('oad.oad_test_method_id','=','material_inward_details.mid_test_method_id');
            })
            /* Planning */
            ->join('planning_management_details as pmd','pmd.pmd_oad_id','=','oad.oad_id')
            ->join('planning_management as pm','pm.pm_id','=','pmd.pmd_pm_id')
            /* Master Tables */
            ->leftJoin('job_descriptions','job_descriptions.id','=','material_inward_details.mid_job_desc_id')
            ->leftJoin('part','part.part_id','=','material_inward_details.mid_part_id')
            ->leftJoin('unit','unit.id','=','oad.oad_unit_id')
            ->leftJoin('unit as inward_unit','inward_unit.id','=','material_inward_details.mid_qty_unit_id')
            /* Filters */
            ->where('mi.mins_id',$request->mins_ids)
            // ->where('pm.pm_id',$request->pm_ids)
            ->where('mi.mins_result',"Accepted")
            ->where('pm.pm_process_at',"In-House")
            ->having('pmd_pend_qty', '>', 0)
            ->get();
            // dd($planning_management_data);
            // dd($request->all());
            if(!empty($inspection_data)){
                $inspection_data->mins_date = Date::createFromFormat('Y-m-d', $inspection_data->mins_date)->format('d/m/Y');
                $inspection_data->mi_challan_date = Date::createFromFormat('Y-m-d', $inspection_data->mi_challan_date)->format('d/m/Y');
                $inspection_data->mi_date = Date::createFromFormat('Y-m-d', $inspection_data->mi_date)->format('d/m/Y');
            }
            foreach ($planning_management_data as $row) {
                if (!empty($row->oa_date)) {
                    $row->oa_date = \Carbon\Carbon::parse($row->oa_date)->format('d/m/Y');
                }
                if (!empty($row->oa_po_date)) {
                    $row->oa_po_date = \Carbon\Carbon::parse($row->oa_po_date)->format('d/m/Y');
                }
            }

        return response()->json([
            'response_code' => '1',
            'inspection_data' => $inspection_data,
            'planning_management_data' => $planning_management_data
        ]);
    }
    public function store(Request $request) {
        // dd($request->all());
        $year_data = getCurrentYearData();
        $existNumber = POMappingProcess::where([['po_mp_sequence',  $request->po_mp_sequence],['po_mp_number',$request->po_mp_number],['year_id',$year_data->id]])->first();
        
        if($existNumber)
        {
            $latestNo = $this->getLatestPOMappingProcessNumber($request);
            $tmp =  $latestNo->getContent();
            $area = json_decode($tmp, true);
            $po_mp_number =   $area['latest_no'];
            $po_mp_sequence = $area['number'];
        }
        else
        {
            $po_mp_number   = $request->po_mp_number;
            $po_mp_sequence = $request->po_mp_sequence;
        }

        DB::beginTransaction();
        try
        {
            $po_mapping_data = POMappingProcess::create([
                'po_mp_number'           => $po_mp_number ?? null,
                'po_mp_sequence'         => $po_mp_sequence ?? null,
                'po_mp_date'             => isset($request->po_mp_date) ? Date::createFromFormat('d/m/Y', $request->po_mp_date)->format('Y-m-d') : null,
                // 'po_mp_customer_id'      => $request->po_mp_customer_id ?? null,
                // 'po_mp_type_of_job_id'   => $request->po_mp_type_of_job_id ?? null,
                'po_mp_description'      => $request->po_mp_description ?? null,
                'po_mp_mapped_by_id'     => $request->po_mp_mapped_by_user_id ?? null,
                'po_mp_total_mapping_no' => $request->po_mp_total_mapping_no ?? null,
                'po_mp_special_note'     => $request->po_mp_sp_note ?? null,
                'year_id'                => $year_data->id,
                'company_id'             => Auth::user()->company_id,
                'created_on'             => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'             => Auth::user()->id,
            ]);

            $planning_management_data = json_decode($request->planning_management_data, true);
            if(!empty($request->pmd_id))
            {
                foreach($request->pmd_id as $key => $pmd_id)
                {
                    POMappingProcessDetails::create([
                        'po_mpd_po_mp_id'         => $po_mapping_data->po_mp_id,
                        'po_mpd_mins_id'          => $request->mins_id ?? null,
                        'po_mpd_pmd_id'           => $pmd_id ?? null,
                        'po_mpd_inward_mapping_qty' => $request->minw_mapping_qty[$key] ?? null,
                        'po_mpd_oa_mapping_qty'   => $request->oa_mapping_qty[$key] ?? null,
                        'po_mpd_remark'           => null,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'response_code' => 1,
                'response_message' => getResponseMessage('store_success'),
                // 'data' => $po_mapping_data
            ]);
        }
        catch (\Exception $e)
        {
            report($e);
            DB::rollback();
            return response()->json([
                'response_code' => 0,
                'response_message' => getResponseMessage('store_error'),
                'original_error' => $e->getMessage(),
            ]);
        }
    }

    public function destroy(Request $request)
    {
        $po_mapping_process = POMappingProcess::find($request->id);
        if ($po_mapping_process) {
            // Delete details first
            POMappingProcessDetails::where('po_mpd_po_mp_id', $request->id)->delete();
            $po_mapping_process->delete();
            return response()->json([
                'response_code' => 1,
                'response_message' => getResponseMessage('delete_success'),
            ]);
        } else {
            return response()->json([
                'response_code' => 0,
                'response_message' => getResponseMessage('delete_error'),
            ]);
        }
    }

    // public function edit(Request $request)
    // {
    //     $id = $request->id;

    //     // 1. Master / Header
    //     $pomp = POMappingProcess::where('po_mp_id', $id)
    //         ->select([
    //             'po_mp_id',
    //             'po_mp_number',
    //             'po_mp_sequence',
    //             'po_mp_date',
    //             'po_mp_description',
    //             'po_mp_mapped_by_id',
    //             'po_mp_total_mapping_no',
    //             'po_mp_special_note',
    //         ])
    //         ->first();

    //     if (!$pomp) {
    //         return response()->json([
    //             'response_code' => 0,
    //             'message'       => 'Record not found'
    //         ]);
    //     }

    //     // Format date for frontend
    //     $pomp->po_mp_date = $pomp->po_mp_date
    //         ? \Carbon\Carbon::parse($pomp->po_mp_date)->format('d/m/Y')
    //         : null;

    //     // 2. Get first mins_id (we only need one inspection record per PO Mapping)
    //     $firstDetail = POMappingProcessDetails::where('po_mpd_po_mp_id', $id)
    //         ->select('po_mpd_mins_id', 'po_mpd_pmd_id')
    //         ->first();

    //     $mins_id = $firstDetail ? $firstDetail->po_mpd_mins_id : null;

    //     // 3. Fetch inspection + inward + unit info
    //     $inspection = null;
    //     if ($mins_id) {
    //         $inspection = DB::table('material_inspection as mi')
    //             ->leftJoin('material_inward as miw', 'miw.mi_id', '=', 'mi.mins_mid_id')
    //             ->leftJoin('material_inward_details as mid', 'mid.mid_id', '=', 'mi.mins_mid_id')
    //             ->leftJoin('unit as u_accept', 'u_accept.id', '=', 'mid.mid_qty_unit_id')
    //             ->select([
    //                 'mi.mins_id',
    //                 'mi.mins_number',
    //                 DB::raw("DATE_FORMAT(mi.mins_date, '%d/%m/%Y') as mins_date"),
    //                 'miw.mi_challan_number as challan_no',
    //                 DB::raw("DATE_FORMAT(miw.mi_challan_date, '%d/%m/%Y') as challan_date"),
    //                 'miw.mi_customer_id as po_mp_customer_id',
    //                 'mi.mins_insp_qty as po_mp_accepted_qty',
    //                 'mid.mid_qty_unit_id as po_mp_accepted_unit_id',
    //                 // 'u_accept.unit as po_mp_accepted_unit_name',  // only if you need name in form
    //             ])
    //             ->where('mi.mins_id', $mins_id)
    //             ->first();
    //     }

    //     // Merge inspection fields into $pomp (explicit assignment - no toArray())
    //     if ($inspection) {
    //         $pomp->mins_id              = $inspection->mins_id;
    //         $pomp->mins_number          = $inspection->mins_number;
    //         $pomp->mins_date            = $inspection->mins_date;
    //         $pomp->challan_no           = $inspection->challan_no;
    //         $pomp->challan_date         = $inspection->challan_date;
    //         $pomp->po_mp_customer_id    = $inspection->po_mp_customer_id;
    //         $pomp->po_mp_accepted_qty   = $inspection->po_mp_accepted_qty;
    //         $pomp->po_mp_accepted_unit_id = $inspection->po_mp_accepted_unit_id ?? null;
    //     }

    //     // 4. Details rows
    //     $details = DB::table('po_mapping_process_details as pmpd')
    //         ->join('planning_management_details as pmd', 'pmd.pmd_id', '=', 'pmpd.po_mpd_pmd_id')
    //         ->join('order_acceptance_details as oad', 'oad.oad_id', '=', 'pmd.pmd_oad_id')
    //         ->join('order_acceptance as oa', 'oa.oa_id', '=', 'oad.oad_oa_id')
    //         ->leftJoin('job_descriptions as jd', 'jd.id', '=', 'oad.oad_job_desc_id')
    //         ->leftJoin('part as pt', 'pt.part_id', '=', 'oad.oad_part_id')
    //         ->leftJoin('unit as u', 'u.id', '=', 'oad.oad_unit_id')
    //         // inward unit join - corrected order
    //         ->leftJoin('material_inspection as mi_link', 'mi_link.mins_id', '=', 'pmpd.po_mpd_mins_id')
    //         ->leftJoin('material_inward_details as mid', 'mid.mid_id', '=', 'mi_link.mins_mid_id')
    //         ->leftJoin('unit as iu', 'iu.id', '=', 'mid.mid_qty_unit_id')
    //         ->where('pmpd.po_mpd_po_mp_id', $id)
    //         ->select([
    //             'pmpd.po_mpd_pmd_id as pmd_id',
    //             'pmpd.po_mpd_mins_id as mins_id',
    //             'pmpd.po_mpd_inward_mapping_qty as minw_mapping_qty',
    //             'pmpd.po_mpd_oa_mapping_qty as oa_mapping_qty',

    //             'oa.oa_type_id',
    //             'oa.oa_number',
    //             DB::raw("DATE_FORMAT(oa.oa_date, '%d/%m/%Y') as oa_date"),
    //             'oa.oa_po_number',
    //             DB::raw("DATE_FORMAT(oa.oa_po_date, '%d/%m/%Y') as oa_po_date"),

    //             'jd.job_description',
    //             'pt.part',

    //             'u.unit as unit',
    //             'oad.oad_qty',

    //             'pmd.pmd_plan_qty',

    //             DB::raw("GREATEST(0, pmd.pmd_plan_qty - COALESCE(
    //                 (SELECT SUM(po_mpd_oa_mapping_qty) 
    //                 FROM po_mapping_process_details sub 
    //                 WHERE sub.po_mpd_pmd_id = pmd.pmd_id), 0)
    //             ) as pmd_pend_qty"),

    //             DB::raw("GREATEST(0, oad.oad_qty - COALESCE(
    //                 (SELECT SUM(po_mpd_oa_mapping_qty) 
    //                 FROM po_mapping_process_details sub 
    //                 WHERE sub.po_mpd_pmd_id = pmd.pmd_id), 0)
    //             ) as pend_oa_qty"),

    //             'iu.unit as inward_unit',
    //             'oad.oad_remark'
    //         ])
    //         ->orderBy('pmpd.po_mpd_id')
    //         ->get();

    //     return response()->json([
    //         'response_code'     => 1,
    //         'pomp_data'         => $pomp,
    //         'pomp_details_data' => $details,
    //     ]);
    // }
    public function edit(Request $request)
    {
        $id = $request->id;

        // 1. Master / Header
        $pomp = POMappingProcess::where('po_mp_id', $id)
            ->select([
                'po_mp_id',
                'po_mp_number',
                'po_mp_sequence',
                'po_mp_date',
                'po_mp_description',
                'po_mp_mapped_by_id',
                'po_mp_total_mapping_no',
                'po_mp_special_note',
            ])
            ->first();

        if (!$pomp) {
            return response()->json([
                'response_code' => 0,
                'response_message'       => 'Record not found'
            ]);
        }


        if (!empty($pomp->po_mp_date)) {
            $pomp->po_mp_date = \Carbon\Carbon::parse($pomp->po_mp_date)->format('d/m/Y');
        }

        // 2. Get the linked mins_id (we assume one inspection per mapping process)
        $firstDetail = POMappingProcessDetails::where('po_mpd_po_mp_id', $id)
            ->select('po_mpd_mins_id')
            ->first();

        $mins_id = $firstDetail ? $firstDetail->po_mpd_mins_id : null;

        // 3. Fetch inspection data using ALMOST THE SAME QUERY as getMaterialInspForPOMP
        $inspection = null;
        if ($mins_id) {
            $inspection = DB::table('material_inspection as mi')
                ->leftJoin('material_inward_details as mid', 'mid.mid_id', '=', 'mi.mins_mid_id')
                ->leftJoin('material_inward as miw', 'miw.mi_id', '=', 'mid.mid_mi_id')
                ->leftJoin('customers', 'customers.id', '=', 'miw.mi_customer_id')
                ->leftJoin('type_of_job', 'type_of_job.id', '=', 'mid.mid_type_of_job_id')
                ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'mid.mid_job_desc_id')
                ->leftJoin('part', 'part.part_id', '=', 'mid.mid_part_id')
                ->leftJoin('unit', 'unit.id', '=', 'mid.mid_qty_unit_id')
                ->select([
                    'mi.mins_id',
                    'mi.mins_number',
                    DB::raw("DATE_FORMAT(mi.mins_date, '%d/%m/%Y') as mins_date"),
                    'mi.mins_insp_qty as po_mp_accepted_qty',
                    'mi.mins_special_note',
                    'miw.mi_number',
                    DB::raw("DATE_FORMAT(miw.mi_date, '%d/%m/%Y') as mi_date"),
                    'miw.mi_challan_number as challan_no',
                    DB::raw("DATE_FORMAT(miw.mi_challan_date, '%d/%m/%Y') as challan_date"),
                    'customers.customer',
                    'miw.mi_customer_id as po_mp_customer_id',
                    'mid.mid_type_of_job_id as po_mp_type_of_job_id',
                    'type_of_job.type_of_job',
                    'mid.mid_job_desc_id as po_mp_job_description_id',
                    'job_descriptions.job_description',
                    'mid.mid_test_method_id as po_mp_test_method_id',
                    'mid.mid_part_id as po_mp_part_id',
                    \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
                    'mid.mid_qty_unit_id as po_mp_accepted_unit_id',
                    'unit.unit as po_mp_accepted_unit_name',

                    // Same pending qty logic as in getMaterialInspForPOMP
                    DB::raw("
                        mi.mins_insp_qty
                        - COALESCE(
                            (SELECT SUM(po_mpd_inward_mapping_qty)
                            FROM po_mapping_process_details
                            WHERE po_mpd_mins_id = mi.mins_id
                            ), 0 
                        ) + COALESCE(
                            (SELECT SUM(po_mpd_inward_mapping_qty)
                            FROM po_mapping_process_details
                            WHERE po_mpd_mins_id = mi.mins_id
                            ), 0 
                        ) AS po_mp_pending_qty
                    ")
                ])
                ->where('mi.mins_id', $mins_id)
                ->havingRaw('po_mp_pending_qty > 0')
                ->first();
        }

        // Merge inspection fields into $pomp
        if ($inspection) {
            $pomp->mins_id                  = $inspection->mins_id;
            $pomp->mins_number              = $inspection->mins_number;
            $pomp->mins_date                = $inspection->mins_date;
            $pomp->challan_no               = $inspection->challan_no;
            $pomp->challan_date             = $inspection->challan_date;
            $pomp->po_mp_customer_id        = $inspection->po_mp_customer_id;
            $pomp->po_mp_accepted_qty       = $inspection->po_mp_accepted_qty;
            $pomp->po_mp_pending_qty        = $inspection->po_mp_pending_qty ?? 0;
            $pomp->po_mp_accepted_unit_id   = $inspection->po_mp_accepted_unit_id;
            $pomp->po_mp_type_of_job_id     = $inspection->po_mp_type_of_job_id;
            $pomp->po_mp_job_description_id = $inspection->po_mp_job_description_id;
            $pomp->po_mp_part_id            = $inspection->po_mp_part_id;
            $pomp->po_mp_test_method_id     = $inspection->po_mp_test_method_id;
            // you can add more fields if needed (customer name, job desc name, etc.)
        }

        // 4. Details (same as before – this part is not changed)
        $details = DB::table('po_mapping_process_details as pmpd')
            ->join('planning_management_details as pmd', 'pmd.pmd_id', '=', 'pmpd.po_mpd_pmd_id')
            ->join('order_acceptance_details as oad', 'oad.oad_id', '=', 'pmd.pmd_oad_id')
            ->join('order_acceptance as oa', 'oa.oa_id', '=', 'oad.oad_oa_id')
            ->leftJoin('job_descriptions as jd', 'jd.id', '=', 'oad.oad_job_desc_id')
            ->leftJoin('part as pt', 'pt.part_id', '=', 'oad.oad_part_id')
            ->leftJoin('unit as u', 'u.id', '=', 'oad.oad_unit_id')
            ->leftJoin('material_inspection as mi_link', 'mi_link.mins_id', '=', 'pmpd.po_mpd_mins_id')
            ->leftJoin('material_inward_details as mid', 'mid.mid_id', '=', 'mi_link.mins_mid_id')
            ->leftJoin('unit as iu', 'iu.id', '=', 'mid.mid_qty_unit_id')
            ->where('pmpd.po_mpd_po_mp_id', $id)
            ->select([
                'pmpd.po_mpd_pmd_id as pmd_id',
                'pmpd.po_mpd_mins_id as mins_id',
                'pmpd.po_mpd_inward_mapping_qty as minw_mapping_qty',
                'pmpd.po_mpd_oa_mapping_qty as oa_mapping_qty',

                'oa.oa_type_id',
                'oa.oa_number',
                DB::raw("DATE_FORMAT(oa.oa_date, '%d/%m/%Y') as oa_date"),
                'oa.oa_po_number',
                DB::raw("DATE_FORMAT(oa.oa_po_date, '%d/%m/%Y') as oa_po_date"),

                'jd.job_description',
                'pt.part',

                'u.unit as unit',
                'oad.oad_qty',

                'pmd.pmd_plan_qty',

                DB::raw("GREATEST(0, pmd.pmd_plan_qty - COALESCE(
                    (SELECT SUM(po_mpd_oa_mapping_qty) 
                    FROM po_mapping_process_details sub 
                    WHERE sub.po_mpd_pmd_id = pmd.pmd_id), 0) 
                    + COALESCE(
                    (SELECT SUM(po_mpd_oa_mapping_qty) 
                    FROM po_mapping_process_details sub 
                    WHERE sub.po_mpd_pmd_id = pmd.pmd_id), 0)
                ) as pmd_pend_qty"),

                DB::raw("GREATEST(0, oad.oad_qty - COALESCE(
                    (SELECT SUM(po_mpd_oa_mapping_qty) 
                    FROM po_mapping_process_details sub 
                    WHERE sub.po_mpd_pmd_id = pmd.pmd_id), 0)
                ) +  COALESCE(
                    (SELECT SUM(po_mpd_oa_mapping_qty) 
                    FROM po_mapping_process_details sub 
                    WHERE sub.po_mpd_pmd_id = pmd.pmd_id), 0)
                     as pend_oa_qty"),

                'mid.mid_qty_unit_id as inward_unit_id',
                'oad.oad_remark'
            ])
            ->orderBy('pmpd.po_mpd_id')
            ->get();

        return response()->json([
            'response_code'     => 1,
            'pomp_data'         => $pomp,
            'pomp_details_data' => $details,
        ]);
    }
    /**
     * Update an existing PO mapping process.  This is almost the same as the
     * store() method but performs an update instead of an insert and wipes the
     * old detail rows before inserting the new set.  The front‑end will hit this
     * URL when the hidden `id` field is populated.
     */
    public function update(Request $request)
    {
        $id = $request->id;

        if (!$id) {
            return response()->json([
                'response_code' => 0,
                'response_message'       => getResponseMessage('update_error'),
            ]);
        }

        $year_data = getCurrentYearData();

        // Check for duplicate number/sequence (exclude current record)
        $existNumber = POMappingProcess::where([
                ['po_mp_sequence', $request->po_mp_sequence],
                ['po_mp_number', $request->po_mp_number],
                ['year_id', $year_data->id]
            ])
            ->where('po_mp_id', '!=', $id)
            ->first();

        $po_mp_number   = $request->po_mp_number;
        $po_mp_sequence = $request->po_mp_sequence;

        if ($existNumber) {
            $latestNo = $this->getLatestPOMappingProcessNumber($request);
            $tmp = $latestNo->getContent();
            $area = json_decode($tmp, true);
            $po_mp_number   = $area['latest_no']   ?? $po_mp_number;
            $po_mp_sequence = $area['number']      ?? $po_mp_sequence;
        }

        // dd($request->all());    
        DB::beginTransaction();

        try {
            $po_mapping = POMappingProcess::findOrFail($id);

            $po_mapping->update([
                'po_mp_number'           => $po_mp_number,
                'po_mp_sequence'         => $po_mp_sequence,
                'po_mp_date'             => isset($request->po_mp_date)
                                            ? Date::createFromFormat('d/m/Y', $request->po_mp_date)->format('Y-m-d')
                                            : null,
                'po_mp_description'      => $request->po_mp_description ?? null,
                'po_mp_mapped_by_id'     => $request->po_mp_mapped_by_user_id ?? null,
                'po_mp_total_mapping_no' => $request->po_mp_total_mapping_no ?? null,
                'po_mp_special_note'     => $request->po_mp_sp_note ?? null,
                'last_on'                => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by'                => Auth::user()->id,
            ]);

            // Delete old details
            POMappingProcessDetails::where('po_mpd_po_mp_id', $id)->delete();

            // Insert new details (same logic as store)
            if (!empty($request->pmd_id)) {
                foreach ($request->pmd_id as $key => $pmd_id) {
                    POMappingProcessDetails::create([
                        'po_mpd_po_mp_id'         => $id,
                        'po_mpd_mins_id'          => $request->mins_id ?? null,
                        'po_mpd_pmd_id'           => $pmd_id ?? null,
                        'po_mpd_inward_mapping_qty' => $request->minw_mapping_qty[$key] ?? null,
                        'po_mpd_oa_mapping_qty'   => $request->oa_mapping_qty[$key] ?? null,
                        'po_mpd_remark'           => null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'response_code' => 1,
                'response_message'       => getResponseMessage('update_success'),
                'data'          => $po_mapping
            ]);
        }
        catch (\Exception $e) {
            report($e);
            DB::rollback();
            return response()->json([
                'response_code' => 0,
                'response_message'       => getResponseMessage('update_error'),
                'original_error' => $e->getMessage(),
            ]);
        }
    }
}