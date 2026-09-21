<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\MaterialInward;
use App\Models\Transaction\MaterialInwardDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class MaterialInwardController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-material_inward');
    }

    public function index(MaterialInward $mi_data, Request $request, DataTables $datatables)
    {
        $current_location_id = getCurrentLocation()->location_id;
        $year_data = getCurrentYearData();

        $mi_data = MaterialInward::select([
            'material_inward.material_inward_sequence',
            'material_inward.material_inward_id',
            'material_inward.material_inward_no',
            'material_inward.material_inward_date',
            'material_inward.nabl_type_fix',
            'material_inward.test_at_fix',
            'material_inward.job_type_fix',
            'customers.customer as customer',
            'material_inward.dc_no',
            'material_inward.dc_date',
            'material_inward.po_no',
            'material_inward.po_date',
            'material_inward_details.type_of_testing_id_fix',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            'material_inward_details.part_no',
            'material_inward_details.drg_no',
            'materials.material',
            'material_inward_details.heat_no',
            'material_inward_details.rt_no',
            'material_inward_details.product_code',
            'material_inward_details.quantity',
            'prepared_by.person_name as prepared_by',
            'material_inward.created_on',
            'material_inward.created_by',
            'material_inward.last_by',
            'material_inward.last_on',
        ])
        ->leftJoin('material_inward_details', 'material_inward_details.material_inward_id', '=', 'material_inward.material_inward_id')
        ->leftJoin('customers', 'customers.id', '=', 'material_inward.customer_id')
        ->leftJoin('admin as prepared_by', 'prepared_by.id', '=', 'material_inward.prepared_by_user_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'material_inward_details.type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'material_inward_details.job_desc_id')
        ->leftJoin('materials', 'materials.id', '=', 'material_inward_details.material_id')
        ->where('material_inward.year_id', $year_data->id)
        ->where('material_inward.current_location_id', $current_location_id);

        $dataTable = DataTables::of($mi_data)
        ->editColumn('part', function ($mi_data) {
            return $mi_data->part ?? '';
        })
        ->filterColumn('part', function($query, $keyword) {
            $keyword = trim($keyword);
            $query->whereRaw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) LIKE ?", ["%{$keyword}%"]);
        })
        ->editColumn('material_inward_date', function ($mi_data) {
            if ($mi_data->material_inward_date != null) {
                return Date::createFromFormat('Y-m-d', $mi_data->material_inward_date)->format(DATE_FORMAT);
            }
            return '';
        })
        ->filterColumn('material_inward.material_inward_date', function ($q, $k) {
            applyDate($q, $k, 'material_inward.material_inward_date');
        })
        ->editColumn('dc_date', function ($mi_data) {
            if ($mi_data->dc_date != null) {
                return Date::createFromFormat('Y-m-d', $mi_data->dc_date)->format(DATE_FORMAT);
            }
            return '';
        })
        ->filterColumn('material_inward.dc_date', function ($q, $k) {
            applyDate($q, $k, 'material_inward.dc_date');
        })
        ->editColumn('po_date', function ($mi_data) {
            if ($mi_data->po_date != null) {
                return Date::createFromFormat('Y-m-d', $mi_data->po_date)->format(DATE_FORMAT);
            }
            return '';
        })
        ->filterColumn('material_inward.po_date', function ($q, $k) {
            applyDate($q, $k, 'material_inward.po_date');
        })
        ->filterColumn('material_inward.job_type_fix', function ($query, $keyword) {
            $globalSearch = request()->input('search.value');
            $searchValue = $globalSearch != '' ? $globalSearch : $keyword;
            $lowerKeyword = strtolower(trim($searchValue));
            if ($lowerKeyword === 'Welding') {
                $searchJobTypeFix = 'Welding';
            }
            elseif ($lowerKeyword === 'Non-Welding') {
                $searchJobTypeFix = 'Non-Welding';
            }
            if (!empty($searchJobTypeFix)) {
                $query->where('material_inward.job_type_fix', '=', $searchJobTypeFix);
            }
            else {
                $dbFormatKeyword = str_replace(' ', '_', $lowerKeyword);
                $query->where('material_inward.job_type_fix', 'like', "$dbFormatKeyword%");
            }
        })
        ->editColumn('quantity', function ($mi_data) {
            return $mi_data->quantity > 0 ? number_format((float)$mi_data->quantity, 0, '.', '') : '';
        })
        ->filterColumn('material_inward.material_inward_no', function ($query, $keyword) {
            applySequenceSearch($query, $keyword, 'material_inward.material_inward_no');
        })
        ->addColumn('options', function ($mi_data) {
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("material_inward", "print")) {
                    $mi_number = !empty($mi_data->material_inward_no) ? '_' . str_replace('/', '_', $mi_data->material_inward_no) : "";
                    $cust_name = !empty($mi_data->customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $mi_data->customer) : "";
                    $pdfName   = 'Material_Inward' . $mi_number . $cust_name;
                    $encodedId = base64_encode($mi_data->material_inward_id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=material_inward");
                    $action .= '<li><a class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }

                if (hasAccess("material_inward", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-material_inward"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("material_inward", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });

        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'material_inward');
        return $dataTable
        ->rawColumns(['options', 'last_by', 'last_on', 'created_by', 'created_on'])
        ->make(true);
    }

    /**
     * Auto-number generator (mirrors getLatestServicePONumber).
     * The exact display format (/<Loc_Code>/INW/XXXX/XX-XX) is produced by
     * the project helper getLatestSequence() based on the prefix + company/location/year.
     */
    public function getLatestMaterialInwardNumber(Request $request)
    {
        $modal    = MaterialInward::class;
        $sequence = 'material_inward_sequence';
        $prefix   = 'INW';

        $num_format = getLatestSequence($modal, $sequence, $prefix);

        return response()->json([
            'response_code' => 1,
            'latest_no'     => $num_format['format'],
            'number'        => $num_format['isFound'],
        ]);
    }

    /**
     * Edit â€” reads parent + child rows through the stored procedures
     * material_inward_master / material_inward_details
     * (see database/stored_procedures/material_inward_procedures.sql).
     */
    public function edit(Request $request)
    {
        $mi_data = DB::select('CALL material_inward_master(?)', [$request->id]);

        if (!empty($mi_data)) {
            $mi_data = $mi_data[0];
            $mi_data->material_inward_date = $mi_data->material_inward_date != "" ? Date::createFromFormat('Y-m-d', $mi_data->material_inward_date)->format('d/m/Y') : "";
            $mi_data->dc_date = $mi_data->dc_date != "" ? Date::createFromFormat('Y-m-d', $mi_data->dc_date)->format('d/m/Y') : "";
            $mi_data->po_date = !empty($mi_data->po_date) ? Date::createFromFormat('Y-m-d', $mi_data->po_date)->format('d/m/Y') : "";
            if(isset($mi_data->company_logo))
            {
                $mi_data->company_logo = base64_encode($mi_data->company_logo);
            }

            $mi_number = !empty($mi_data->material_inward_no) ? '_' . str_replace('/', '_', $mi_data->material_inward_no) : "";
            $cust_name = !empty($mi_data->customer_name) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $mi_data->customer_name) : "";
            $mi_data->pdf_name = 'Material_Inward' . $mi_number . $cust_name;
        }


        $mi_details_data = DB::select('CALL material_inward_details(?)', [$request->id]);

        $usage = DB::select('CALL material_inward_used_list(?)', [$request->id]);
        $detail_used_map = [];
        $detail_dc_used_map = [];
        $detail_direct_obs_used_map = [];
        foreach ($usage as $uRow) {
            $detId = $uRow->material_inward_details_id ?? null;
            if ($detId) {
                if (!isset($detail_used_map[$detId])) {
                    $detail_used_map[$detId] = 0;
                }
                $detail_used_map[$detId] += (float)($uRow->used_qty ?? 0);

                $tableName = $uRow->table_name ?? '';
                if ($tableName === 'Material Outward') {
                    if (!isset($detail_dc_used_map[$detId])) {
                        $detail_dc_used_map[$detId] = 0;
                    }
                    $detail_dc_used_map[$detId] += (float)($uRow->used_qty ?? 0);
                }

                if ($tableName === 'Observation Sheet') {
                    if (!isset($detail_direct_obs_used_map[$detId])) {
                        $detail_direct_obs_used_map[$detId] = 0;
                    }
                    $detail_direct_obs_used_map[$detId] += (float)($uRow->used_qty ?? 0);
                }
            }
        }
        
        $allUsedDetailIds = [];
        foreach ($usage as $uRow) {
            if (!empty($uRow->material_inward_details_ids)) {
                $ids = array_map('trim', explode(',', $uRow->material_inward_details_ids));
                $allUsedDetailIds = array_merge($allUsedDetailIds, $ids);
            }
        }
        $allUsedDetailIds = array_unique(array_filter($allUsedDetailIds));
        

        $detail_ids = [];
        if ($mi_details_data) {
            foreach ($mi_details_data as $dVal) {
                if (!empty($dVal->material_inward_details_id)) {
                    $detail_ids[] = $dVal->material_inward_details_id;
                }
                $dVal->mode = 'Update';

                // Fetch part_no & drg_no directly from DB table material_inward_details for edit mode auto-fill
                $dbDetail = DB::table('material_inward_details')->where('material_inward_details_id', $dVal->material_inward_details_id)->first();
                if ($dbDetail) {
                    if (!empty($dbDetail->part_no)) $dVal->part_no = $dbDetail->part_no;
                    if (!empty($dbDetail->drg_no)) $dVal->drg_no = $dbDetail->drg_no;
                    if (isset($dbDetail->process_type)) $dVal->process_type = $dbDetail->process_type;
                    if (isset($dbDetail->is_observation_sheet)) $dVal->is_observation_sheet = $dbDetail->is_observation_sheet;
                    if (isset($dbDetail->test_report_rt_id)) $dVal->test_report_rt_id = $dbDetail->test_report_rt_id;
                }

                $rtReportId = $dVal->test_report_rt_id ?? ($dbDetail->test_report_rt_id ?? null);
                if (!empty($rtReportId)) {
                    $rtReport = DB::table('test_report_rt')
                        ->select('revision_number')
                        ->where('test_report_rt_id', $rtReportId)
                        ->first();
                    if ($rtReport && !empty($rtReport->revision_number)) {
                        $dVal->revision_number = $rtReport->revision_number;
                    }
                }

                /*
                $used_qty_rt = DB::table('test_report_rt')
                    ->where('material_inward_details_id', $dVal->material_inward_details_id)
                    ->sum('rt_report_qty') ?? 0;
                $used_qty_ut = DB::table('test_report_ut')
                    ->where('material_inward_details_id', $dVal->material_inward_details_id)
                    ->sum('total_qty') ?? 0;
                $used_qty_dpt = DB::table('test_report_dpt')
                    ->where('material_inward_details_id', $dVal->material_inward_details_id)
                    ->sum('total_qty') ?? 0;
                $used_qty_mpt = DB::table('test_report_mpt')
                    ->where('material_inward_details_id', $dVal->material_inward_details_id)
                    ->sum('total_qty') ?? 0;
                $used_qty = $used_qty_rt + $used_qty_ut + $used_qty_dpt + $used_qty_mpt;
                $dVal->used_qty = (float)$used_qty;

                $used_qty_dc = DB::table('customer_dc_non_returnable_details')
                    ->where('material_inward_details_id', $dVal->material_inward_details_id)
                    ->sum('dc_qty') ?? 0;
                $dVal->used_qty_dc = (float)$used_qty_dc;

                $dVal->in_use = (in_array((string)$dVal->material_inward_details_id, $allUsedDetailIds) || (float)$used_qty > 0 || (float)$used_qty_dc > 0);
                */

                $detId = $dVal->material_inward_details_id;
                $totUsed = $detail_used_map[$detId] ?? 0;
                $dcUsed  = $detail_dc_used_map[$detId] ?? 0;
                $directObsUsed = $detail_direct_obs_used_map[$detId] ?? 0;

                $dVal->used_qty    = (float)$totUsed;
                $dVal->used_qty_dc = (float)$dcUsed;
                $dVal->is_direct_obs_used = ($directObsUsed > 0);
                $dVal->in_use      = ($totUsed > 0);
            }
        }

        $min_report_date = null;
        if (!empty($detail_ids)) {
            $min_dates = [];
            $min_report_date = DB::query()
                ->fromSub(function ($query) use ($detail_ids) {
                    $query->from('test_report_rt')
                        ->select('test_report_date')
                        ->whereIn('material_inward_details_id', $detail_ids)
                        ->whereNotNull('test_report_date')
                        ->where('test_report_date', '!=', '0000-00-00')
                        ->unionAll(
                            DB::table('test_report_ut')
                                ->select('test_report_date')
                                ->whereIn('material_inward_details_id', $detail_ids)
                                ->whereNotNull('test_report_date')
                                ->where('test_report_date', '!=', '0000-00-00')
                                ->where('test_report_date', '!=', '0000-00-00')
                        )
                        ->unionAll(
                            DB::table('test_report_dpt')
                                ->select('test_report_date')
                                ->whereIn('material_inward_details_id', $detail_ids)
                                ->whereNotNull('test_report_date')
                                ->where('test_report_date', '!=', '0000-00-00')
                        )
                        ->unionAll(
                            DB::table('test_report_mpt')
                                ->select('test_report_date')
                                ->whereIn('material_inward_details_id', $detail_ids)
                                ->whereNotNull('test_report_date')
                                ->where('test_report_date', '!=', '0000-00-00')
                        );
                }, 'reports')
                ->min('test_report_date');

            if ($min_report_date) {
                $min_report_date = Date::createFromFormat('Y-m-d', $min_report_date)
                    ->format('d/m/Y');
            }
        }

        if ($mi_data) {
            return response()->json([
                'mi_data'              => $mi_data,
                'mi_details_data'      => $mi_details_data,
                'min_report_date'      => $min_report_date,
                'response_code'        => '1',
                'response_message'     => '',
            ]);
        }

        return response()->json([
            'response_code'    => '0',
            'response_message' => 'Record Does Not Exists',
        ]);
    }

    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            $usage = DB::select('CALL material_inward_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                $message = "You Can't Delete, Material Inward Is Used In " . $usage[0]->table_name . ".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            MaterialInwardDetails::where('material_inward_id', $request->id)->delete();
            MaterialInward::where('material_inward_id', $request->id)->delete();

            DB::commit();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451) {
                $error_msg = "This Is Used Somewhere, You Can't Delete";
            } else {
                $error_msg = getResponseMessage('delete_error');
            }

            return response()->json([
                'response_code' => '0',
                'response_message' => $error_msg,
            ]);
        }
    }

    /* ------------------------------------------------------------------ *
     |  store / update / getPartsByJobDesc
     * ------------------------------------------------------------------ */

    public function store(Request $request)
    {
        $year_data    = getCurrentYearData();
        $LocationData = getCurrentLocation();

        // $validated = $request->validate([
        //     'tpi_name' => 'required_if:is_any_tpi_witness,Yes',
        // ], [
        //     'tpi_name.required_if' => 'Enter TPI Name.',
        // ]);

        DB::beginTransaction();
        try {
            // Resolve / lock the auto number (re-fetch if the typed one was taken)
            $existNumber = MaterialInward::where([
                ['material_inward_sequence', $request->material_inward_sequence],
                ['material_inward_no', $request->material_inward_no],
                ['year_id', $year_data->id],
                ['current_location_id', $LocationData->location_id],
            ])->first();

            if ($existNumber) {
                $latestNo = $this->getLatestMaterialInwardNumber($request);
                $area = json_decode($latestNo->getContent(), true);
                $material_inward_no       = $area['latest_no'];
                $material_inward_sequence = $area['number'];
            } else {
                $material_inward_no       = $request->material_inward_no;
                $material_inward_sequence = $request->material_inward_sequence;
            }

            $page_id = getMenuIdBassedOnDisplayName('material_inward');
            $assign_format_no = getAssignFormateNoForTransaction($LocationData->location_id, $page_id->id, $request->material_inward_date);

            $mi = MaterialInward::create([
                'material_inward_no'       => $material_inward_no,
                'material_inward_sequence' => $material_inward_sequence,
                'material_inward_date'     => $request->material_inward_date ? Date::createFromFormat('d/m/Y', $request->material_inward_date)->format('Y-m-d') : null,
                'nabl_type_fix'            => $request->nabl_type_fix,
                'test_at_fix'              => $request->test_at_fix,
                'job_type_fix'             => $request->job_type_fix,
                'customer_id'              => $request->customer_id,
                'dc_no'                    => $request->dc_no,
                'dc_date'                  => $request->dc_date ? Date::createFromFormat('d/m/Y', $request->dc_date)->format('Y-m-d') : null,
                'po_no'                    => $request->po_no,
                'po_date'                  => !empty($request->po_date) ? Date::createFromFormat('d/m/Y', $request->po_date)->format('Y-m-d') : null,
                'sample_drawn_by'          => $request->sample_drawn_by,
                'is_any_tpi_witness'       => $request->is_any_tpi_witness,
                'tpi_name'                 => $request->is_any_tpi_witness == 'Yes' ? $request->tpi_name : null,
                'condition_of_sample'      => $request->condition_of_sample,
                'is_equipment_available'   => $request->is_equipment_available,
                'competent_personnel_available' => $request->competent_personnel_available,
                'is_test_sub_contracted'   => $request->is_test_sub_contracted,
                'test_feasible'            => $request->test_feasible,
                'all_test_parameters_are_in_accredited_scope' => $request->all_test_parameters_are_in_accredited_scope,
                'required_statement_of_conformity' => $request->required_statement_of_conformity,
                'additional_requirement_from_customer' => $request->additional_requirement_from_customer,
                'special_note'             => $request->special_note,
                // prepared_by auto-set to the logged-in user (per spec)
                'prepared_by_user_id'      => Auth::user()->id,
                'assign_format_no'         => $assign_format_no,
                'current_location_id'      => $LocationData->location_id ?? null,
                'year_id'                  => $year_data->id,
                'company_id'               => Auth::user()->company_id,
                'created_by'               => Auth::user()->id,
                'created_on'               => Carbon::now('Asia/Kolkata'),
            ]);

            $details = json_decode($request->material_inward_details_data, true);
            if (!empty($details)) {
                foreach ($details as $row) {
                    // dd($row);
                    if (($row['mode'] ?? '') == 'Delete') continue;
                    if (empty($row['type_of_testing_id_fix'])) continue;

                    if($request->nabl_type_fix == 'NABL' && $row['process_type'] != 'Repair'){
                        $is_observation_sheet = 'Yes';
                    }else if($row['process_type'] == 'Repair'){
                        $is_observation_sheet = 'N/A';
                    }else if($row['type_of_testing_id_fix'] != 'RT' && $request->nabl_type_fix != 'NABL'){
                        $is_observation_sheet = 'N/A';
                    }else{
                        $is_observation_sheet = 'No';
                    }
                    //$is_observation_sheet = $request->nabl_type_fix === 'NABL'  || $row['process_type'] === 'Repair' ? 'Yes' : 'No';

                    $createdDetail = MaterialInwardDetails::create([
                        'material_inward_id'     => $mi->material_inward_id,
                        'type_of_testing_id_fix' => $row['type_of_testing_id_fix'] ?? null,
                        'process_type'           => $row['process_type'] ?? 'Fresh',
                        //'is_observation_sheet'   => $row['is_observation_sheet'] ?? 'No',
                        'is_observation_sheet'   => $is_observation_sheet,
                        'test_report_rt_id'      =>
                         $row['test_report_rt_id'] != '' && $row['test_report_rt_id'] != 0 ? $row['test_report_rt_id'] : null,
                        'type_of_job_id'         => $row['type_of_job_id'] ?? null,
                        'job_desc_id'            => $row['job_desc_id'] ?? null,
                        'part_id'                => !empty($row['part_id']) ? $row['part_id'] : null,
                        'part_no'                => $row['part_no'] ?? null,
                        'drg_no'                 => $row['drg_no'] ?? null,
                        'material_id'            => $row['material_id'] ?? null,
                        'heat_no'                => $row['heat_no'] ?? null,
                        'rt_no'                  => $row['rt_no'] ?? null,
                        'product_code'           => $row['product_code'] ?? null,
                        'thickness'              => $row['thickness'] ?? null,
                        'area_of_coverage_id'    => $row['area_of_coverage_id'] ?? null,
                        'procedure_ref_id'       => !empty($row['procedure_ref_id']) ? $row['procedure_ref_id'] : null,
                        'evaluation_as_per_id'   => !empty($row['evaluation_as_per_id']) ? $row['evaluation_as_per_id'] : null,
                        'acceptance_standard_id' => !empty($row['acceptance_standard_id']) ? $row['acceptance_standard_id'] : null,
                        'quantity'               => $row['quantity'] ?? 0,
                        'approx_value'           => $row['approx_value'] !== '' ? ($row['approx_value'] ?? null) : null,
                        'approx_weight'          => $row['approx_weight'] !== '' ? ($row['approx_weight'] ?? null) : null,
                        'remark'                 => $row['remark'] ?? null,
                    ]);

                    $checkPending = $this->checkNegativePendingQty($createdDetail->material_inward_details_id, $row['type_of_testing_id_fix'] ?? '', 'I');
                    if ($checkPending) {
                        return $checkPending;
                    }
                }
            }

            DB::commit();

            // Print PDF
            $mi_number = !empty($mi->material_inward_no) ? '_' . str_replace('/', '_', $mi->material_inward_no) : "";
            $cust = DB::table('customers')->where('id', $mi->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Material_Inward' . $mi_number . $cust_name;

            GeneratePdf($mi->material_inward_id, $pdf_name, 'material_inward', 'add');
            $encodedId = base64_encode($mi->material_inward_id);
            $url = hasAccess("material_inward", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=material_inward") : "";

            return response()->json([
                'response_code' => '1',
                'url' => $url,
                'response_message' => getResponseMessage('store_success'),
            ]);
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();
            return response()->json([
                'response_code' => '0',
                'response_message' => getResponseMessage('store_error'),
                'original_error' => $e->getMessage(),
            ]);
        }
    }

    public function update(Request $request)
    {
        $year_data    = getCurrentYearData();
        $LocationData = getCurrentLocation();

        $validated = $request->validate([
            'material_inward_sequence' => [
                'required',
                'max:155',
                Rule::unique('material_inward')
                    ->where(function ($query) use ($year_data, $LocationData) {
                        $query->where('year_id', $year_data->id)
                            ->where('current_location_id', $LocationData->location_id);
                    })
                    ->ignore($request->id, 'material_inward_id')
            ],
            // 'tpi_name' => 'required_if:is_any_tpi_witness,Yes',
        ], [
            'material_inward_sequence.unique' => 'Duplicate Inward No. Found.',
            'material_inward_sequence.required' => 'Enter Inward No.',
            // 'tpi_name.required_if' => 'Enter TPI Name.',
        ]);

        DB::beginTransaction();
        try {
            $mi = MaterialInward::where('material_inward_id', $request->id)->first();
            if (!$mi) {
                return response()->json(['response_code' => '0', 'response_message' => 'Record Does Not Exists']);
            }

            if ($request->material_inward_date) {
                $newDate = Date::createFromFormat('d/m/Y', $request->material_inward_date)->format('Y-m-d');
                $detail_ids = DB::table('material_inward_details')
                    ->where('material_inward_id', $request->id)
                    ->pluck('material_inward_details_id');
                
                if ($detail_ids->isNotEmpty()) {
                    $min_report_date = DB::query()
                        ->fromSub(function ($query) use ($detail_ids) {
                            $query->from('test_report_rt')
                                ->select('test_report_date')
                                ->whereIn('material_inward_details_id', $detail_ids)
                                ->whereNotNull('test_report_date')
                                ->where('test_report_date', '!=', '0000-00-00')
                                ->unionAll(
                                    DB::table('test_report_ut')
                                        ->select('test_report_date')
                                        ->whereIn('material_inward_details_id', $detail_ids)
                                        ->whereNotNull('test_report_date')
                                        ->where('test_report_date', '!=', '0000-00-00')
                                )
                                ->unionAll(
                                    DB::table('test_report_dpt')
                                        ->select('test_report_date')
                                        ->whereIn('material_inward_details_id', $detail_ids)
                                        ->whereNotNull('test_report_date')
                                        ->where('test_report_date', '!=', '0000-00-00')
                                )
                                ->unionAll(
                                    DB::table('test_report_mpt')
                                        ->select('test_report_date')
                                        ->whereIn('material_inward_details_id', $detail_ids)
                                        ->whereNotNull('test_report_date')
                                        ->where('test_report_date', '!=', '0000-00-00')
                                );
                        }, 'reports')
                        ->min('test_report_date');

                    if ($min_report_date && $newDate > $min_report_date) {
                        return response()->json([
                            'response_code' => '0',
                            'response_message' => "Material Inward Date cannot be greater than the Report Date."
                        ]);
                    }
                }
            }

            $page_id = getMenuIdBassedOnDisplayName('material_inward');
            $assign_format_no = getAssignFormateNoForTransaction($LocationData->location_id, $page_id->id, $request->material_inward_date);

            $mi->update([
                'material_inward_no'     => $request->material_inward_no,
                'material_inward_sequence'     => $request->material_inward_sequence,
                'material_inward_date'     => $request->material_inward_date ? Date::createFromFormat('d/m/Y', $request->material_inward_date)->format('Y-m-d') : null,
                'nabl_type_fix'            => $request->nabl_type_fix,
                'test_at_fix'              => $request->test_at_fix,
                'job_type_fix'             => $request->job_type_fix,
                'customer_id'              => $request->customer_id,
                'dc_no'                    => $request->dc_no,
                'dc_date'                  => $request->dc_date ? Date::createFromFormat('d/m/Y', $request->dc_date)->format('Y-m-d') : null,
                'po_no'                    => $request->po_no,
                'po_date'                  => !empty($request->po_date) ? Date::createFromFormat('d/m/Y', $request->po_date)->format('Y-m-d') : null,
                'sample_drawn_by'          => $request->sample_drawn_by,
                'is_any_tpi_witness'       => $request->is_any_tpi_witness,
                'tpi_name'                 => $request->is_any_tpi_witness == 'Yes' ? $request->tpi_name : null,
                'condition_of_sample'      => $request->condition_of_sample,
                'is_equipment_available'   => $request->is_equipment_available,
                'competent_personnel_available' => $request->competent_personnel_available,
                'is_test_sub_contracted'   => $request->is_test_sub_contracted,
                'test_feasible'            => $request->test_feasible,
                'all_test_parameters_are_in_accredited_scope' => $request->all_test_parameters_are_in_accredited_scope,
                'required_statement_of_conformity' => $request->required_statement_of_conformity,
                'additional_requirement_from_customer' => $request->additional_requirement_from_customer,
                'special_note'             => $request->special_note,
                // 'assign_format_no'         => $assign_format_no, // not update assign formate discussion ramde sir
                'last_by'                  => Auth::user()->id,
                'last_on'                  => Carbon::now('Asia/Kolkata'),
            ]);

            $details = json_decode($request->material_inward_details_data, true);

            if (!empty($details)) {
                foreach ($details as $row) {
                    if (empty($row)) continue;

                    $mode = $row['mode'] ?? '';

                    if($request->nabl_type_fix == 'NABL' && $row['process_type'] != 'Repair'){
                        $is_observation_sheet = 'Yes';
                    }else if($row['process_type'] == 'Repair'){
                        $is_observation_sheet = 'N/A';
                    }else if($row['type_of_testing_id_fix'] != 'RT' && $request->nabl_type_fix != 'NABL'){
                        $is_observation_sheet = 'N/A';
                    }else{
                        $is_observation_sheet = 'No';
                    }

                    //$is_observation_sheet = $request->nabl_type_fix === 'NABL'  || $row['process_type'] === 'Repair' ? 'Yes' : 'No';

                    if ($mode == 'Insert') {
                        $createdDetail = MaterialInwardDetails::create([
                            'material_inward_id'     => $mi->material_inward_id,
                            'type_of_testing_id_fix' => $row['type_of_testing_id_fix'] ?? null,
                            'process_type'           => $row['process_type'] ?? 'Fresh',
                            // 'is_observation_sheet'   => $row['is_observation_sheet'] ?? 'No',
                            'is_observation_sheet'   =>  $is_observation_sheet,
                            'test_report_rt_id' =>  $row['test_report_rt_id'] != '' && $row['test_report_rt_id'] != 0 ? $row['test_report_rt_id'] : null,
                            'type_of_job_id'         => $row['type_of_job_id'] ?? null,
                            'job_desc_id'            => $row['job_desc_id'] ?? null,
                            'part_id'                => !empty($row['part_id']) ? $row['part_id'] : null,
                            'part_no'                => $row['part_no'] ?? null,
                            'drg_no'                 => $row['drg_no'] ?? null,
                            'material_id'            => $row['material_id'] ?? null,
                            'heat_no'                => $row['heat_no'] ?? null,
                            'rt_no'                  => $row['rt_no'] ?? null,
                            'product_code'           => $row['product_code'] ?? null,
                            'thickness'              => $row['thickness'] ?? null,
                            'area_of_coverage_id'    => $row['area_of_coverage_id'] ?? null,
                            'procedure_ref_id'       => !empty($row['procedure_ref_id']) ? $row['procedure_ref_id'] : null,
                            'evaluation_as_per_id'   => !empty($row['evaluation_as_per_id']) ? $row['evaluation_as_per_id'] : null,
                            'acceptance_standard_id' => !empty($row['acceptance_standard_id']) ? $row['acceptance_standard_id'] : null,
                            'quantity'               => $row['quantity'] ?? 0,
                            'approx_value'           => $row['approx_value'] !== '' ? ($row['approx_value'] ?? null) : null,
                            'approx_weight'          => $row['approx_weight'] !== '' ? ($row['approx_weight'] ?? null) : null,
                            'remark'                 => $row['remark'] ?? null,
                        ]);

                        $checkPending = $this->checkNegativePendingQty($createdDetail->material_inward_details_id, $row['type_of_testing_id_fix'] ?? '', 'U');
                        if ($checkPending) {
                            return $checkPending;
                        }
                    }
                    elseif ($mode == 'Update') {

                        if (!empty($row['material_inward_details_id'])) {
                            $detailId = $row['material_inward_details_id'];

                            $existsInDownstream = DB::table('observation_sheet_details')->where('material_inward_details_id', $detailId)->exists()
                                || DB::table('test_report_rt')->where('material_inward_details_id', $detailId)->exists()
                                || DB::table('test_report_ut')->where('material_inward_details_id', $detailId)->exists()
                                || DB::table('test_report_mpt')->where('material_inward_details_id', $detailId)->exists()
                                || DB::table('test_report_dpt')->where('material_inward_details_id', $detailId)->exists();

                            if ($existsInDownstream) {
                                $is_observation_sheet = DB::table('material_inward_details')
                                    ->where('material_inward_details_id', $detailId)
                                    ->value('is_observation_sheet');
                            } else {
                                if($request->nabl_type_fix == 'NABL' && $row['process_type'] != 'Repair'){
                                    $is_observation_sheet = 'Yes';
                                }else if($row['process_type'] == 'Repair'){
                                    $is_observation_sheet = 'N/A';
                                }else if($row['type_of_testing_id_fix'] != 'RT' && $request->nabl_type_fix != 'NABL'){
                                    $is_observation_sheet = 'N/A';
                                }else{
                                    $is_observation_sheet = 'No';
                                }
                            }
                        } else {
                            if($request->nabl_type_fix == 'NABL' && $row['process_type'] != 'Repair'){
                                $is_observation_sheet = 'Yes';
                            }else if($row['process_type'] == 'Repair'){
                                $is_observation_sheet = 'N/A';
                            }else if($row['type_of_testing_id_fix'] != 'RT' && $request->nabl_type_fix != 'NABL'){
                                $is_observation_sheet = 'N/A';
                            }else{
                                $is_observation_sheet = 'No';
                            }
                        }

                        if (!empty($row['material_inward_details_id'])) {
                            $is_used_in_os = DB::table('observation_sheet_details')
                            ->where('from_type_id_fix', 1)
                            ->where('material_inward_details_id', $row['material_inward_details_id'])
                            ->first(); 

                            if ($is_used_in_os && (float)$row['quantity'] != (float)$is_used_in_os->observation_qty) {
                                    DB::rollBack();
                                    return response()->json([
                                        'response_code'    => '0',
                                        'response_message' => "You Can't Update, Material Inward Qty. Is Used In Observation Sheet.",
                                    ]);
                                }
                               $old_inward_qty = MaterialInwardDetails::where('material_inward_details_id', $row['material_inward_details_id'])->value('quantity');
                               $next_qty = $old_inward_qty - $row['quantity'];
                            //    dd($next_qty,$old_inward_qty);
                            $checkQty = $this->qtyValidation($next_qty, $row['material_inward_details_id'], 'U');
                            if ($checkQty) {
                                return $checkQty;
                            }


                            MaterialInwardDetails::where('material_inward_details_id', $row['material_inward_details_id'])->update([
                                'material_inward_id'     => $mi->material_inward_id,
                                'type_of_testing_id_fix' => $row['type_of_testing_id_fix'] ?? null,
                                'process_type'           => $row['process_type'] ?? 'Fresh',
                                // 'is_observation_sheet'   => $row['is_observation_sheet'] ?? 'No',
                                'is_observation_sheet'   =>  $is_observation_sheet,
                                'test_report_rt_id'      =>  $row['test_report_rt_id'] != '' && $row['test_report_rt_id'] != 0 ? $row['test_report_rt_id'] : null,
                                'type_of_job_id'         => $row['type_of_job_id'] ?? null,
                                'job_desc_id'            => $row['job_desc_id'] ?? null,
                                'part_id'                => !empty($row['part_id']) ? $row['part_id'] : null,
                                'part_no'                => $row['part_no'] ?? null,
                                'drg_no'                 => $row['drg_no'] ?? null,
                                'material_id'            => $row['material_id'] ?? null,
                                'heat_no'                => $row['heat_no'] ?? null,
                                'rt_no'                  => $row['rt_no'] ?? null,
                                'product_code'           => $row['product_code'] ?? null,
                                'thickness'              => $row['thickness'] ?? null,
                                'area_of_coverage_id'    => $row['area_of_coverage_id'] ?? null,
                                'procedure_ref_id'       => !empty($row['procedure_ref_id']) ? $row['procedure_ref_id'] : null,
                                'evaluation_as_per_id'   => !empty($row['evaluation_as_per_id']) ? $row['evaluation_as_per_id'] : null,
                                'acceptance_standard_id' => !empty($row['acceptance_standard_id']) ? $row['acceptance_standard_id'] : null,
                                'quantity'               => $row['quantity'] ?? 0,
                                'approx_value'           => $row['approx_value'] !== '' ? ($row['approx_value'] ?? null) : null,
                                'approx_weight'          => $row['approx_weight'] !== '' ? ($row['approx_weight'] ?? null) : null,
                                'remark'                 => $row['remark'] ?? null,
                            ]);

                            $checkPending = $this->checkNegativePendingQty($row['material_inward_details_id'], $row['type_of_testing_id_fix'] ?? '', 'U');
                            if ($checkPending) {
                                return $checkPending;
                            }
                        }
                    }
                    elseif ($mode == 'Delete') {
                        if (!empty($row['material_inward_details_id'])) {
                             $old_inward_qty = MaterialInwardDetails::where('material_inward_details_id', $row['material_inward_details_id'])->value('quantity');
                            $checkQty = $this->qtyValidation($old_inward_qty, $row['material_inward_details_id'], 'D');
                            if ($checkQty) {
                                return $checkQty;
                            }

                            try {
                                MaterialInwardDetails::where('material_inward_details_id', $row['material_inward_details_id'])->delete();
                            } catch (\Exception $e) {
                                report($e);
                                if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451) {
                                    DB::rollBack();
                                    return response()->json([
                                        'response_code' => '0',
                                        'response_message' => "You Can't Delete, Material Inward Detail is Used Somewhere.",
                                    ]);
                                  } else {
                                      throw $e;
                                  }
                            }
                        }
                    }
                }
            }

            DB::commit();
            // $url = "";
            $mi_number = !empty($mi->material_inward_no) ? '_' . str_replace('/', '_', $mi->material_inward_no) : "";
            $cust = DB::table('customers')->where('id', $mi->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Material_Inward' . $mi_number . $cust_name;

            GeneratePdf($mi->material_inward_id, $pdf_name, 'material_inward', 'update');
            $encodedId = base64_encode($mi->material_inward_id);
            $url = hasAccess("material_inward", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=material_inward") : "";

            return response()->json([
                'response_code' => '1',
                'url' => $url,
                'response_message' => getResponseMessage('update_success'),
            ]);
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();
            return response()->json([
                'response_code' => '0',
                'response_message' => getResponseMessage('update_error'),
                'original_error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Parts for the chosen Job Description (child modal dependency).
     * (No drawing-number column exists; data-drg_no stays empty until a source is added.)
     */
    public function getPartsByJobDesc(Request $request)
    {
        $parts = DB::table('part')
            ->select('part_id', 'part_no', 'drg_no')
            ->where('job_desc_id', $request->job_desc_id)
            ->orderBy('part_no', 'asc')
            ->get();

        return response()->json([
            'response_code' => 1,
            'parts' => $parts,
        ]);
    }

    public function materialInwardLNRData(Request $request)
    {
        $year_data     = getCurrentYearData();
        $LocationData  = getCurrentLocation();
        $customer_id   = $request->customer_id;
        $nabl_type_fix = $request->nabl_type_fix;

        // Get location-wise last inward record for parent radios (regardless of customer)
        $latestLocationInward = MaterialInward::where('year_id', $year_data->id)
            ->where('current_location_id', $LocationData->location_id)
            ->orderBy('material_inward_id', 'desc')
            ->first();

        $location_lnr_radios = null;
        if ($latestLocationInward) {
            $location_lnr_radios = [
                'nabl_type_fix' => $latestLocationInward->nabl_type_fix,
                'test_at_fix'   => $latestLocationInward->test_at_fix,
                'job_type_fix'  => $latestLocationInward->job_type_fix,
            ];
        }

        // Strictly do NOT return any customer LNR data if no customer is selected
        if (empty($customer_id)) {
            return response()->json([
                'response_code'       => 1,
                'lnr_data'            => null,
                'lnr_detail_testing_type' => null,
                'location_lnr_radios' => $location_lnr_radios,
            ]);
        }

        $query = MaterialInward::where('year_id', $year_data->id)
            ->where('current_location_id', $LocationData->location_id)
            ->where('customer_id', $customer_id);

        if (!empty($nabl_type_fix)) {
            $query->where('nabl_type_fix', $nabl_type_fix);
        }

        $latestInward = $query->orderBy('material_inward_id', 'desc')->first();

        // Fallback: If no record for specific customer + nabl_type_fix, try matching just customer
        if (!$latestInward) {
            $latestInward = MaterialInward::where('year_id', $year_data->id)
                ->where('current_location_id', $LocationData->location_id)
                ->where('customer_id', $customer_id)
                ->orderBy('material_inward_id', 'desc')
                ->first();
        }

        $lnr_detail_testing_type = null;
        if ($latestInward) {
            $lastDetail = MaterialInwardDetails::where('material_inward_id', $latestInward->material_inward_id)
                ->whereNotNull('type_of_testing_id_fix')
                ->orderBy('material_inward_details_id', 'desc')
                ->first();
            if ($lastDetail) {
                $lnr_detail_testing_type = $lastDetail->type_of_testing_id_fix;
            }
        }

        return response()->json([
            'response_code'       => 1,
            'lnr_data'            => $latestInward,
            'lnr_detail_testing_type' => $lnr_detail_testing_type,
            'location_lnr_radios' => $location_lnr_radios,
        ]);
    }

    public function qtyValidation($new_qty, $material_inward_details_id, $transaction_mode = 'U')
    {
        // dd($material_inward_details_id);
        if ($material_inward_details_id > 0) {
            $used_qty_dc = DB::table('customer_dc_non_returnable_details')
                ->where('material_inward_details_id', $material_inward_details_id)
                ->sum('dc_qty') ?? 0;

            if ((float)$used_qty_dc > 0 && ((float)$new_qty < (float)$used_qty_dc || $transaction_mode === 'D')) {
                DB::rollBack();

                $message = ($transaction_mode === 'D')
                    ? "You Can't Delete, Material Inward Detail is Used in Material Outward."
                    : "You Can't Update, Inward Qty. cannot be less than DC Qty. ({$used_qty_dc}).";

                return response()->json([
                    'response_code'    => '0',
                    'response_message' => $message,
                ]);
            }
            // dd($new_qty);
            if($new_qty > 0)
            {
                 $inward_detail = DB::table('material_inward_details')
                ->where('material_inward_details_id', $material_inward_details_id)
                ->first();
                $testing_type = $inward_detail->type_of_testing_id_fix ?? '';
                if ($testing_type === 'RT') {
                    $pending_qty = DB::table('pending_material_inward_rt_qty')
                        ->where('material_inward_details_id', $material_inward_details_id)
                        ->whereNull('observation_sheet_details_id')
                        ->value('pending_qty');
                    $used_qty =  (float)($pending_qty ?? 0);
                } elseif ($testing_type === 'UT') {
                    $pending_qty = DB::table('pending_material_inward_ut_qty')
                        ->where('material_inward_details_id', $material_inward_details_id)
                        //   ->whereNull('observation_sheet_details_id')
                        ->value('pending_qty');
                    $used_qty =(float)($pending_qty ?? 0);
                } elseif ($testing_type === 'DPT') {
                    $pending_qty = DB::table('pending_material_inward_dpt_qty')
                        ->where('material_inward_details_id', $material_inward_details_id)
                        //   ->whereNull('observation_sheet_details_id')
                        ->value('pending_qty');
                    $used_qty = (float)($pending_qty ?? 0);
                } elseif ($testing_type === 'MPT') {
                    $pending_qty = DB::table('pending_material_inward_mpt_qty')
                        ->where('material_inward_details_id', $material_inward_details_id)
                        // ->whereNull('observation_sheet_details_id')
                        ->value('pending_qty');
                    $used_qty = (float)($pending_qty ?? 0);
                }
                
                $obsPendingQty = DB::table('pending_observation_sheet_qty')
                    ->where('material_inward_details_id', $material_inward_details_id)
                    ->where('from_type_id_fix', 1)
                    ->value('pending_qty');
                $total_qty= (float)($used_qty) + (float)($obsPendingQty ?? 0);

                if($transaction_mode == 'D'){
                    // dd($total_qty , $new_qty);
                    if($total_qty < $new_qty){
                        DB::rollBack();
                        return response()->json([
                            'response_code' => '0',
                            'response_message' => "You Can't Delete, Material Inward Is Used.",
                        ]);
                    }

                }else{
                    // dd($total_qty,$new_qty);
                    if($total_qty < $new_qty){
                        DB::rollBack();
                        return response()->json([
                            'response_code' => '0',
                            'response_message' => "You Can't Update, Material Inward Is Used.",
                        ]);
                    }
                }
                        
            }
           

            // Separate Customer DC (Non-Returnable) Validation
            
        }
        return null; // No error
    }

//     public function qtyValidation($new_qty, $material_inward_details_id, $transaction_mode = 'U')
// {
//     if ($material_inward_details_id > 0) {
//         $inward_detail = DB::table('material_inward_details')
//             ->where('material_inward_details_id', $material_inward_details_id)
//             ->first();

//         if (!$inward_detail) {
//             return null;
//         }

//         $inward_qty   = (float)($inward_detail->quantity ?? 0);
//         $testing_type = $inward_detail->type_of_testing_id_fix ?? '';

//         // à«§. Customer DC (Non-Returnable) àª®àª¾àª‚ àªµàªªàª°àª¾àª¶ àªšà«‡àª• àª•àª°à«‹
//         $used_qty_dc = (float)DB::table('customer_dc_non_returnable_details')
//             ->where('material_inward_details_id', $material_inward_details_id)
//             ->sum('dc_qty');

//         if ($used_qty_dc > 0) {
//             if ($transaction_mode === 'D') {
//                 DB::rollBack();
//                 return response()->json([
//                     'response_code'    => '0',
//                     'response_message' => "You Can't Delete, Material Inward Is Used In Customer DC (Non-Returnable).",
//                 ]);
//             }
//             if ($transaction_mode === 'U' && (float)$new_qty < $used_qty_dc) {
//                 DB::rollBack();
//                 return response()->json([
//                     'response_code'    => '0',
//                     'response_message' => "You Can't Update, Inward Qty. cannot be less than Customer DC Qty. ({$used_qty_dc}).",
//                 ]);
//             }
//         }

//         // à«¨. Observation Sheet àª®àª¾àª‚ àªµàªªàª°àª¾àª¶ àªšà«‡àª• àª•àª°à«‹
//         $obs_pending = DB::table('pending_observation_sheet_qty')
//             ->where('material_inward_details_id', $material_inward_details_id)
//             ->where(function($q) {
//                 $q->whereNull('test_report_rt_id')->orWhere('test_report_rt_id', 0);
//             })
//             ->value('pending_qty');

//         $used_obs_qty = ($obs_pending !== null) ? max(0, $inward_qty - (float)$obs_pending) : 0;

//         if ($used_obs_qty > 0) {
//             if ($transaction_mode === 'D') {
//                 DB::rollBack();
//                 return response()->json([
//                     'response_code'    => '0',
//                     'response_message' => "You Can't Delete, Material Inward Is Used In Observation Sheet.",
//                 ]);
//             }
//             if ($transaction_mode === 'U' && (float)$new_qty < $used_obs_qty) {
//                 DB::rollBack();
//                 return response()->json([
//                     'response_code'    => '0',
//                     'response_message' => "You Can't Update, Inward Qty. cannot be less than Observation Sheet Qty. ({$used_obs_qty}).",
//                 ]);
//             }
//         }

//         // à«©. Direct Test Reports (RT / UT / DPT / MPT) àª®àª¾àª‚ àªµàªªàª°àª¾àª¶ àªšà«‡àª• àª•àª°à«‹
//         $report_pending = null;
//         $report_name = "Test Report ({$testing_type})";

//         if ($testing_type === 'RT') {
//             $report_pending = DB::table('pending_material_inward_rt_qty')
//                 ->where('material_inward_details_id', $material_inward_details_id)
//                 ->value('pending_qty');
//         } elseif ($testing_type === 'UT') {
//             $report_pending = DB::table('pending_material_inward_ut_qty')
//                 ->where('material_inward_details_id', $material_inward_details_id)
//                 ->value('pending_qty');
//         } elseif ($testing_type === 'DPT') {
//             $report_pending = DB::table('pending_material_inward_dpt_qty')
//                 ->where('material_inward_details_id', $material_inward_details_id)
//                 ->value('pending_qty');
//         } elseif ($testing_type === 'MPT') {
//             $report_pending = DB::table('pending_material_inward_mpt_qty')
//                 ->where('material_inward_details_id', $material_inward_details_id)
//                 ->value('pending_qty');
//         }

//         $used_report_qty = ($report_pending !== null) ? max(0, $inward_qty - (float)$report_pending) : 0;

//         if ($used_report_qty > 0) {
//             if ($transaction_mode === 'D') {
//                 DB::rollBack();
//                 return response()->json([
//                     'response_code'    => '0',
//                     'response_message' => "You Can't Delete, Material Inward Is Used In {$report_name}.",
//                 ]);
//             }
//             if ($transaction_mode === 'U' && (float)$new_qty < $used_report_qty) {
//                 DB::rollBack();
//                 return response()->json([
//                     'response_code'    => '0',
//                     'response_message' => "You Can't Update, Inward Qty. cannot be less than {$report_name}.",
//                 ]);
//             }
//         }

//         // à«ª. àªœà«‹ àªàª• àª•àª°àª¤àª¾àª‚ àªµàª§à« àª®à«‹àª¡à«àª¯à«àª²àª®àª¾àª‚ àªªàª¾àª°à«àªŸàª¿àª¶àª¨ àª¥àª¯à«‡àª² àª¹à«‹àª¯ àª…àª¨à«‡ àª•à«àª² àªµàªªàª°àª¾àª¶ àª¨àªµà«€ àª•à«àªµàª¾àª¨à«àªŸàª¿àªŸà«€ àª•àª°àª¤àª¾àª‚ àªµàª§à« àª¹à«‹àª¯
//         $total_used = $used_qty_dc + $used_obs_qty + $used_report_qty;
//         if ($transaction_mode === 'U' && (float)$new_qty < $total_used) {
//             DB::rollBack();
//             return response()->json([
//                 'response_code'    => '0',
//                 'response_message' => "You Can't Update, Inward Qty. cannot be less than Total Used Qty. ({$total_used}).",
//             ]);
//         }
//     }

//     return null; // No error
// }


    public function checkNegativePendingQty($material_inward_details_id, $testing_type, $transaction_mode = 'U')
    {
        if (empty($material_inward_details_id)) {
            return null;
        }

        $pendingQty = null;
        if ($testing_type === 'RT') {
            $pendingQty = DB::table('pending_material_inward_rt_qty')
                ->where('material_inward_details_id', $material_inward_details_id)
                ->value('pending_qty');
        } elseif ($testing_type === 'UT') {
            $pendingQty = DB::table('pending_material_inward_ut_qty')
                ->where('material_inward_details_id', $material_inward_details_id)
                ->value('pending_qty');
        } elseif ($testing_type === 'DPT') {
            $pendingQty = DB::table('pending_material_inward_dpt_qty')
                ->where('material_inward_details_id', $material_inward_details_id)
                ->value('pending_qty');
        } elseif ($testing_type === 'MPT') {
            $pendingQty = DB::table('pending_material_inward_mpt_qty')
                ->where('material_inward_details_id', $material_inward_details_id)
                ->value('pending_qty');
        }

        $msg = ($transaction_mode === 'I')
            ? "You Can't Insert, Something Went Wrong."
            : "You Can't Update, Something Went Wrong.";

        if ($pendingQty !== null && (float)$pendingQty < 0) {
            DB::rollBack();
            return response()->json([
                'response_code'    => '0',
                'response_message' => $msg,
            ]);
        }

        $obsPendingQty = DB::table('pending_observation_sheet_qty')
            ->where('material_inward_details_id', $material_inward_details_id)
            ->value('pending_qty');

        if ($obsPendingQty !== null && (float)$obsPendingQty < 0) {
            DB::rollBack();
            return response()->json([
                'response_code'    => '0',
                'response_message' => $msg,
            ]);
        }

        return null;
    }

    public function getInwardPartNoList(Request $request)
    {
        $term = $request->term ?? '';
        $job_desc_id = $request->job_desc_id ?? '';
        $current_location_id = getCurrentLocation()->location_id;

        if (!empty($term)) {
            $query = MaterialInwardDetails::select('material_inward_details.part_no')
                ->join('material_inward', 'material_inward.material_inward_id', '=', 'material_inward_details.material_inward_id')
                ->where('material_inward.current_location_id', $current_location_id)
                ->whereNotNull('material_inward_details.part_no')
                ->where('material_inward_details.part_no', '!=', '')
                ->where('material_inward_details.part_no', 'LIKE', '%' . $term . '%');

            if (!empty($job_desc_id)) {
                $query->where('material_inward_details.job_desc_id', $job_desc_id);
            }

            $partNos = $query->groupBy('material_inward_details.part_no')->orderBy('material_inward_details.part_no', 'asc')->get();

            if ($partNos->isEmpty() && !empty($job_desc_id)) {
                $partNos = MaterialInwardDetails::select('material_inward_details.part_no')
                    ->join('material_inward', 'material_inward.material_inward_id', '=', 'material_inward_details.material_inward_id')
                    ->where('material_inward.current_location_id', $current_location_id)
                    ->whereNotNull('material_inward_details.part_no')
                    ->where('material_inward_details.part_no', '!=', '')
                    ->where('material_inward_details.part_no', 'LIKE', '%' . $term . '%')
                    ->groupBy('material_inward_details.part_no')
                    ->orderBy('material_inward_details.part_no', 'asc')
                    ->get();
            }

            if ($partNos->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($partNos as $row) {
                    $output .= '<li parent-id="part_no" list-id="inward_part_no_list" class="list-group-item" tabindex="0">' . e($row->part_no) . '</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'partNoList'    => $output,
                    'response_code' => 1,
                ]);
            }
        }

        return response()->json([
            'partNoList'    => '',
            'response_code' => 1,
        ]);
    }

    public function getInwardDrgNoList(Request $request)
    {
        $term = $request->term ?? '';
        $current_location_id = getCurrentLocation()->location_id;

        if (!empty($term)) {
            $drgNos = MaterialInwardDetails::select('material_inward_details.drg_no')
                ->join('material_inward', 'material_inward.material_inward_id', '=', 'material_inward_details.material_inward_id')
                ->where('material_inward.current_location_id', $current_location_id)
                ->whereNotNull('material_inward_details.drg_no')
                ->where('material_inward_details.drg_no', '!=', '')
                ->where('material_inward_details.drg_no', 'LIKE', '%' . $term . '%')
                ->groupBy('material_inward_details.drg_no')
                ->orderBy('material_inward_details.drg_no', 'asc')
                ->get();

            if ($drgNos->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($drgNos as $row) {
                    $output .= '<li parent-id="drg_no" list-id="inward_drg_no_list" class="list-group-item" tabindex="0">' . e($row->drg_no) . '</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'drgNoList'     => $output,
                    'response_code' => 1,
                ]);
            }
        }

        return response()->json([
            'drgNoList'     => '',
            'response_code' => 1,
        ]);
    }

    public function getInwardProductCodeList(Request $request)
    {
        $term = $request->term ?? '';
        $current_location_id = getCurrentLocation()->location_id;

        if (!empty($term)) {
            $productCodes = MaterialInwardDetails::select('material_inward_details.product_code')
                ->join('material_inward', 'material_inward.material_inward_id', '=', 'material_inward_details.material_inward_id')
                ->where('material_inward.current_location_id', $current_location_id)
                ->whereNotNull('material_inward_details.product_code')
                ->where('material_inward_details.product_code', '!=', '')
                ->where('material_inward_details.product_code', 'LIKE', '%' . $term . '%')
                ->groupBy('material_inward_details.product_code')
                ->orderBy('material_inward_details.product_code', 'asc')
                ->get();

            if ($productCodes->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($productCodes as $row) {
                    $output .= '<li parent-id="product_code" list-id="inward_product_code_list" class="list-group-item" tabindex="0">' . e($row->product_code) . '</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'productCodeList' => $output,
                    'response_code'   => 1,
                ]);
            }
        }

        return response()->json([
            'productCodeList' => '',
            'response_code'   => 1,
        ]);
    }

    public function getOldInwardDetails(Request $request)
    {
        if ($request->ajax()) {
            $customer = $request->post('customer');
            $jobTypeCasting = $request->post('job_type_casting');
            $typeOfJob = $request->post('type_of_job');
            $jobDescription = $request->post('job_description');

            if (empty($customer) || empty($jobTypeCasting) || empty($typeOfJob) || empty($jobDescription)) {
                return response()->json(['status' => 'success', 'data' => []]);
            }

            $current_location_id = getCurrentLocation()->location_id;

            // Find all inwards for this customer and job type within current location
            $inwardIds = MaterialInward::where('customer_id', $customer)
                ->where('job_type_fix', $jobTypeCasting)
                ->where('current_location_id', $current_location_id)
                ->pluck('material_inward_id');

            if ($inwardIds->count() > 0) {
                // Find all details matching the type of job and job description
                $recentDetails = MaterialInwardDetails::whereIn('material_inward_details.material_inward_id', $inwardIds)
                    ->where('type_of_job_id', $typeOfJob)
                                                            ->where('job_desc_id', $jobDescription)
                    ->join('material_inward', 'material_inward.material_inward_id', '=', 'material_inward_details.material_inward_id')
                    ->select('material_inward_details.*', 'material_inward.material_inward_no as inward_no', 'material_inward.material_inward_date as inward_date')
                    ->orderBy('material_inward_details.material_inward_details_id', 'DESC')
                    ->get();

                return response()->json(['status' => 'success', 'data' => $recentDetails]);
            }
            return response()->json(['status' => 'success', 'data' => []]);
        }
    }
    
    public function getRtReportsListForInward(Request $request)
    {
        $customerId = $request->customer_id;
        $nablType = $request->nabl_type;
        $location = getCurrentLocation()->location_id;
        $year = getCompanyYearIdsToTill();

        $reports = DB::table('pending_repair_rt_report_for_inward')
            ->where('customer_id', $customerId)
            ->where('nabl_type_fix', $nablType)
            ->where('current_location_id', $location)
            ->whereIn('year_id', $year)
            ->orderBy('test_report_rt_id', 'DESC')
            ->get();
            
        foreach ($reports as $report) {
            $report->test_report_date = (!empty($report->test_report_date) && $report->test_report_date != '0000-00-00')
                ? Date::createFromFormat('Y-m-d', $report->test_report_date)->format(DATE_FORMAT)
                : '';
        }
        
        return response()->json([
            'response_code' => 1,
            'reports' => $reports
        ]);
    }
}