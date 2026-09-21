<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\ObservationSheet;
use App\Models\Transaction\ObservationSheetDetails;
use App\Models\Transaction\ObservationSheetDetailsInput;
use App\Models\Transaction\ObservationSheetDetailsPrint;
use App\Models\Transaction\TechniqueSheetRt;
use App\Models\Transaction\TestReportRt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class ObservationSheetController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-observation_sheet');
    }

    public function index(Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();

        $sheets = ObservationSheet::select([
            'observation_sheet.observation_sheet_id as id',
            'observation_sheet.observation_sheet_no',
            'observation_sheet.observation_sheet_sequence',
            'observation_sheet.observation_sheet_date',
            'customers.customer as customer_name',
            'osd.observation_sheet_details_id',
            'osd.material_inward_details_id',
            'osd.process_type',
            'mid.type_of_testing_id_fix',
            'osd.observation_qty',
            'mid.part_no',
            'mid.drg_no',
            'mid.heat_no',
            'mid.product_code',
            // DB::raw("IF(osd.from_type_id_fix = 2, tr.test_report_no, tsr.technique_sheet_rt_no) as rt_no"),
            'mi.material_inward_no',
            'mi.material_inward_date',
            'mi.nabl_type_fix',
            'mi.test_at_fix',
            'mi.job_type_fix',
            'mi.dc_no',
            'mi.dc_date',
            'mi.po_no',
            'mi.po_date',
            'toj.type_of_job',
            'jd.job_description',
            'm.material',
            'observation_sheet.created_on',
            'observation_sheet.created_by',
            'observation_sheet.last_by',
            'observation_sheet.last_on'
        ])
        ->leftJoin('customers', 'customers.id', '=', 'observation_sheet.customer_id')
        ->leftJoin('observation_sheet_details as osd', 'osd.observation_sheet_id', '=', 'observation_sheet.observation_sheet_id')
        ->leftJoin('material_inward_details as mid', 'mid.material_inward_details_id', '=', 'osd.material_inward_details_id')
        ->leftJoin('material_inward as mi', 'mi.material_inward_id', '=', 'mid.material_inward_id')
        ->leftJoin('test_report_rt as tr', 'tr.test_report_rt_id', '=', 'osd.test_report_rt_id')
        ->leftJoin('technique_sheet_rt as tsr', 'tsr.technique_sheet_rt_id', '=', 'osd.technique_sheet_rt_id')
        ->leftJoin('type_of_job as toj', 'toj.id', '=', 'mid.type_of_job_id')
        ->leftJoin('job_descriptions as jd', 'jd.id', '=', 'mid.job_desc_id')
        ->leftJoin('materials as m', 'm.id', '=', 'mid.material_id')
        ->where('observation_sheet.year_id', $year_data->id)
        ->where('observation_sheet.current_location_id', $location_data->location_id);

        $dataTable = $datatables->eloquent($sheets)
            ->filterColumn('observation_sheet.observation_sheet_no', function($query, $keyword) {
                applySequenceSearch($query, $keyword, 'observation_sheet.observation_sheet_no');
            })
            ->filterColumn('rt_no', function($query, $keyword) {
                applySequenceSearch($query, $keyword, DB::raw("IF(osd.from_type_id_fix = 2, tr.test_report_no, COALESCE(NULLIF(tsr.technique_sheet_rt_no, ''), NULLIF(mid.rt_no, ''), ''))"));
            })
            ->filterColumn('mi.material_inward_no', function($query, $keyword) {
                applySequenceSearch($query, $keyword, 'mi.material_inward_no');
            })
            ->editColumn('observation_sheet_date', function($sheet) {
                return $sheet->observation_sheet_date != null
                    ? Date::createFromFormat('Y-m-d', $sheet->observation_sheet_date)->format(DATE_FORMAT)
                    : '';
            })
            ->editColumn('material_inward_date', function($sheet) {
                return (!empty($sheet->material_inward_date) && $sheet->material_inward_date != '0000-00-00')
                    ? Date::createFromFormat('Y-m-d', $sheet->material_inward_date)->format(DATE_FORMAT)
                    : '';
            })
            ->editColumn('dc_date', function($sheet) {
                return (!empty($sheet->dc_date) && $sheet->dc_date != '0000-00-00')
                    ? Date::createFromFormat('Y-m-d', $sheet->dc_date)->format(DATE_FORMAT)
                    : '';
            })
            ->editColumn('po_date', function($sheet) {
                return (!empty($sheet->po_date) && $sheet->po_date != '0000-00-00')
                    ? Date::createFromFormat('Y-m-d', $sheet->po_date)->format(DATE_FORMAT)
                    : '';
            })
            ->filterColumn('observation_sheet.observation_sheet_date', function ($q, $k) {
                applyDate($q, $k, 'observation_sheet.observation_sheet_date');
            })
            ->filterColumn('mi.material_inward_date', function ($q, $k) {
                applyDate($q, $k, 'mi.material_inward_date');
            })
            ->filterColumn('mi.dc_date', function ($q, $k) {
                applyDate($q, $k, 'mi.dc_date');
            })
            ->filterColumn('mi.po_date', function ($q, $k) {
                applyDate($q, $k, 'mi.po_date');
            })
            ->addColumn('actions', function ($sheet) {
                $actions = '<div class="dropdown d-inline-block">
                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-more-fill align-middle"></i>
                    </button>
                    <ul class="dropdown-menu">';

                if (hasAccess("observation_sheet", "print")) {
                    $sheet_number = !empty($sheet->observation_sheet_no) ? '_' . str_replace('/', '_', $sheet->observation_sheet_no) : "";
                    $cust_name = !empty($sheet->customer_name) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $sheet->customer_name) : "";
                    $pdfName   = 'Observation_Sheet' . $sheet_number . $cust_name;
                    $encodedId = base64_encode($sheet->id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=observation_sheet");
                    $actions .= '<li><a class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }

                if (hasAccess("observation_sheet", "edit")) {
                    $actions .= '<li><a class="dropdown-item edit-sheet" href="javascript:void(0)" data-id="' . $sheet->id . '"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("observation_sheet", "delete")) {
                    $actions .= '<li><a class="dropdown-item delete-sheet" href="javascript:void(0)" data-id="' . $sheet->id . '"><i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete</a></li>';
                }

                $actions .= '</ul></div>';
                return $actions;
            });

        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'observation_sheet');

        return $dataTable
            ->rawColumns(['actions', 'last_by', 'last_on', 'created_by', 'created_on'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();

        $request->validate([
            'observation_sheet_date' => 'required',
            'customer_id' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $existNumber = ObservationSheet::where([
                ['observation_sheet_sequence', $request->observation_sheet_sequence],
                ['observation_sheet_no', $request->observation_sheet_no],
                ['year_id', $year_data->id],
                ['current_location_id', $location_data->location_id]
            ])->first();

            if ($existNumber) {
                $latestNo = $this->getLatestObservationSheetNumber($request);
                $area = json_decode($latestNo->getContent(), true);
                $sheet_no = $area['latest_no'];
                $sheet_seq = $area['number'];
            } else {
                $sheet_no = $request->observation_sheet_no;
                $sheet_seq = $request->observation_sheet_sequence;
            }

            $page_id = getMenuIdBassedOnDisplayName('observation_sheet');
            $assign_format_no = getAssignFormateNoForTransaction($location_data->location_id, $page_id->id ?? 0, $request->observation_sheet_date);

            $obsDate = $request->observation_sheet_date ? Date::createFromFormat('d/m/Y', $request->observation_sheet_date)->format('Y-m-d') : null;

            $obs = ObservationSheet::create([
                'observation_sheet_sequence' => $sheet_seq,
                'observation_sheet_no'       => $sheet_no,
                'observation_sheet_date'     => $obsDate,
                'nabl_type_fix'              => $request->nabl_type_fix ?? 'Non NABL',
                'customer_id'                => $request->customer_id,
                'prepared_by_user_id'        =>$request->prepared_by_user_id,
                'sp_note'                    => $request->special_note ?? $request->sp_note ?? '',
                'assign_format_no'           => $assign_format_no,
                'current_location_id'        => $location_data->location_id,
                'company_id'                 => Auth::user()->company_id,
                'year_id'                    => $year_data->id,
                'created_by'                 => Auth::user()->id,
                'created_on'                 => Carbon::now('Asia/Kolkata'),
            ]);

            $detailsData = json_decode($request->input('observation_sheet_details_data'), true);
            if (empty($detailsData) && is_array($request->observation_sheet_details_data)) {
                $detailsData = $request->observation_sheet_details_data;
            }

            if (!empty($detailsData)) {
                // Pending Qty Validation
                foreach ($detailsData as $row) {
                    if (isset($row['mode']) && $row['mode'] === 'Delete') continue;
                    if($row['from_type_id_fix'] == 1){
                         $is_used_in_mi = DB::table('material_inward_details')
                        ->where('material_inward_details_id', $row['material_inward_details_id'])
                        ->first(); 

                        if ($is_used_in_mi && $row['quantity'] != (float)$is_used_in_mi->quantity) {
                            DB::rollBack();
                            return response()->json([
                                'response_code'    => '0',
                                'response_message' => "You Can't Insert, Observation Sheet Qty. Is Not Matched With Material Inward Qty.",
                            ]);
                        }
                    }else{
                        $is_used_in_report = DB::table('test_report_rt')
                        ->where('test_report_rt_id', $row['test_report_rt_id'])
                        ->where('material_inward_details_id', $row['material_inward_details_id'])
                        ->first(); 

                        if ($is_used_in_report && $row['quantity'] != (float)$is_used_in_report->rt_report_qty) {
                            DB::rollBack();
                            return response()->json([
                                'response_code'    => '0',
                                'response_message' => "You Can't Insert, Observation Sheet Qty. Is Not Matched With Test Report RT Qty.",
                            ]);
                        }
                    }

                    $test_report_rt_id = (!empty($row['test_report_rt_id']) && $row['test_report_rt_id'] != 0 && ($row['from_type_id_fix'] ?? 1) != 1) ? $row['test_report_rt_id'] : null;
                    $from_type_id_fix  = $row['from_type_id_fix'] ?? 1;
                    $enteredQty        = (float)($row['quantity'] ?? 0);

                    $checkQty = $this->qtyValidation(
                        $enteredQty,
                        0,
                        'I',
                        $request->customer_id,
                        $row['material_inward_details_id'] ?? 0,
                        $from_type_id_fix,
                        $test_report_rt_id,
                        0
                    );

                    if ($checkQty) {
                        return $checkQty;
                    }
                }

                foreach ($detailsData as $row) {
                    if (isset($row['mode']) && $row['mode'] === 'Delete') continue;

                    $test_report_rt_id = (!empty($row['test_report_rt_id']) && $row['test_report_rt_id'] != 0 && ($row['from_type_id_fix'] ?? 1) != 1) ? $row['test_report_rt_id'] : null;
                    $from_type_id_fix = $row['from_type_id_fix'];

                    $isRt = strtoupper(trim($row['type_of_testing_id_fix'] ?? '')) === 'RT';
                    $filmSizeUnit = $isRt ? (!empty($row['film_size_unit_fix']) ? $row['film_size_unit_fix'] : (!empty($row['film_size_fix']) ? $row['film_size_fix'] : 'inch')) : null;

                    $detailModel = ObservationSheetDetails::create([
                        'observation_sheet_id'       => $obs->observation_sheet_id,
                        'material_inward_details_id' => $row['material_inward_details_id'] ?? null,
                        'technique_sheet_rt_id'      => $row['technique_sheet_rt_id'] ?? null,
                        'test_report_rt_id'          => $test_report_rt_id,
                        'from_type_id_fix'           => $from_type_id_fix,
                        'observation_qty'            => $row['quantity'],
                        'process_type'               => $row['process_type'],
                        'type_of_testing_id_fix'     => $row['type_of_testing_id_fix'],
                        'film_size_unit_fix'         => $filmSizeUnit,
                    ]);


                    if (!empty($row['sub_details']) && is_array($row['sub_details'])) {
                        foreach ($row['sub_details'] as $sub) {
                            $formattedSrNo = isset($sub['sr_no']) && is_numeric($sub['sr_no']) ? ((float)$sub['sr_no'] == (int)$sub['sr_no'] ? (int)$sub['sr_no'] : (float)$sub['sr_no']) : ($sub['sr_no'] ?? null);
                            ObservationSheetDetailsInput::create([
                                'observation_sheet_details_id' => $detailModel->observation_sheet_details_id,
                                'sr_no'              => $formattedSrNo,
                                'identification'     => $sub['identification'] ?? null,
                                'location'           => $sub['location'] ?? null,
                                'source_id_fix'      => $sub['source_id_fix'] ?? null,
                                'film_brand_id'      => $sub['film_brand_id'] ?? null,
                                'film_type_id'       => $sub['film_type_id'] ?? null,
                                'thickness'          => $sub['thickness'] ?? null,
                                'sfd'                => $sub['sfd'] ?? null,
                                //'iqi_designation_id' => $sub['iqi_designation_id'] ?? null,
                                //'iqi_sensitivity_id' => $sub['iqi_sensitivity_id'] ?? null,
                                'iqi_designation_id' => null,
                                'iqi_sensitivity_id' => null,
                                'iqi_designation'    => $sub['iqi_designation'] ?? null,
                                'iqi_sensitivity'    => $sub['iqi_sensitivity'] ?? null,
                                'film_id'            => $sub['film_id'] ?? null,
                                'no_of_film_fix'     => $sub['no_of_film_fix'] ?? 'Single',
                                'film_qty'           => $sub['film_qty'] ?? 1,
                            ]);

                            ObservationSheetDetailsPrint::create([
                                'observation_sheet_details_id' => $detailModel->observation_sheet_details_id,
                                'sr_no'              => $formattedSrNo,
                                'identification'     => $sub['identification'] ?? null,
                                'location'           => $sub['location'] ?? null,
                                'source_id_fix'      => $sub['source_id_fix'] ?? null,
                                'film_brand_id'      => $sub['film_brand_id'] ?? null,
                                'film_type_id'       => $sub['film_type_id'] ?? null,
                                'thickness'          => $sub['thickness'] ?? null,
                                'sfd'                => $sub['sfd'] ?? null,
                                //'iqi_designation_id' => $sub['iqi_designation_id'] ?? null,
                                //'iqi_sensitivity_id' => $sub['iqi_sensitivity_id'] ?? null,
                                'iqi_designation_id' => null,
                                'iqi_sensitivity_id' => null,
                                'iqi_designation'    => $sub['iqi_designation'] ?? null,
                                'iqi_sensitivity'    => $sub['iqi_sensitivity'] ?? null,
                                'film_id'            => $sub['film_id'] ?? null,
                                'no_of_film_fix'     => $sub['no_of_film_fix'] ?? 'Single',
                                'film_qty'           => $sub['film_qty'] ?? 1,
                            ]);
                        }
                    }

                    $checkPending = $this->checkNegativePendingQty(
                        $request->customer_id,
                        $location_data->location_id,
                        $from_type_id_fix,
                        $row['material_inward_details_id'] ?? 0,
                        $test_report_rt_id,
                        $row['type_of_testing_id_fix'] ?? 'RT',
                        'I'
                    );
                    if ($checkPending) {
                        return $checkPending;
                    }
                }
            }

            DB::commit();

            $sheet_number = !empty($obs->observation_sheet_no) ? '_' . str_replace('/', '_', $obs->observation_sheet_no) : "";
            $cust = DB::table('customers')->where('id', $obs->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Observation_Sheet' . $sheet_number . $cust_name;

            $this->generateAndMergePdf($obs->observation_sheet_id, $pdf_name);

            $encodedId = base64_encode($obs->observation_sheet_id);
            $url = hasAccess("observation_sheet", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=observation_sheet") : "";

            return response()->json([
                'response_code'    => '1',
                'url'              => $url,
                'response_message' => getResponseMessage('store_success'),
                'sheet_id'         => $obs->observation_sheet_id
            ]);
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();
            return response()->json([
                'response_code'    => '0',
                'response_message' => getResponseMessage('store_error'),
                'original_error'   => $e->getMessage(),
            ]);
        }
    }

    public function getOldRtReportDetailsForRepair(Request $request)
    {
        $id = $request->test_report_rt_id;
        if (!$id) {
            return response()->json(['response_code' => 0, 'response_message' => 'Report ID missing']);
        }

        $header = DB::table('test_report_rt as tr')
            ->leftJoin('materials as m', 'm.id', '=', 'tr.material_id')
            ->where('tr.test_report_rt_id', $id)
            ->select('tr.heat_no', 'tr.product_code', 'tr.part_no', 'tr.drg_no', 'm.material', 'tr.film_size_unit_fix')
            ->first();

        $details = DB::select('CALL test_report_rt_details(?)', [$id]);

        $filmResults = DB::table('film_result')->get()->keyBy('film_result_id');

        $filtered = array_values(array_filter($details, function($d) use ($filmResults) {
            // First check if result_id exists in the row
            $resultId = $d->result_id ?? $d->film_result_id ?? $d->detail_film_result_id ?? null;
            
            if ($resultId && isset($filmResults[$resultId])) {
                $type = strtolower(trim($filmResults[$resultId]->film_result_type_fix ?? ''));
                if (in_array($type, ['repair', 'retake', 'reshoot'])) {
                    return true;
                }
            }

            // Fallback to name check if ID is not found or type didn't match
            $r = $d->film_result ?? $d->film_result_name ?? $d->result_name ?? $d->result ?? $d->result_type ?? '';
            $resultName = strtolower(trim($r));
            return in_array($resultName, ['repair', 'retake', 'reshoot']);
        }));

        return response()->json([
            'response_code' => 1,
            'header' => $header,
            'details' => $filtered
        ]);
    }

    public function getOldRtReportsList(Request $request)
    {
        $customerId = $request->customer_id;
        $nablType = $request->nabl_type;
        $location = getCurrentLocation()->location_id;
        $year = getCompanyYearIdsToTill();

        $reports = DB::table('test_report_rt as tr')
            ->leftJoin('customers as c', 'c.id', '=', 'tr.customer_id')
            ->leftJoin('type_of_job as toj', 'toj.id', '=', 'tr.type_of_job_id')
            ->leftJoin('job_descriptions as jd', 'jd.id', '=', 'tr.job_desc_id')
            ->leftJoin('materials as m', 'm.id', '=', 'tr.material_id')
            ->leftJoin('observation_sheet_details as osd', 'osd.test_report_rt_id', '=', 'tr.test_report_rt_id')
            ->where('tr.customer_id', $customerId)
            ->where('tr.nabl_type_fix', $nablType)
            ->where('tr.current_location_id', $location)
            ->whereIn('tr.year_id', $year)
            ->where('tr.result', 'Repair (Send Back)')
            ->whereNull('osd.test_report_rt_id')
            ->select([
                'tr.test_report_rt_id',
                'tr.revision_number',
                'tr.test_report_no',
                'tr.test_report_date',
                'tr.nabl_type_fix',
                'tr.job_type_fix',
                'tr.part_no',
                'tr.drg_no',
                'tr.heat_no',
                'tr.product_code',
                'tr.rt_no',
                'tr.ulr_no',
                'tr.customer_client',
                DB::raw("'' as project_name"),
                'c.customer',
                'm.material',
                'toj.type_of_job',
                'jd.job_description'
            ])

            ->orderBy('tr.test_report_rt_id', 'DESC')
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

    // public function getOldRtReportsList(Request $request)
    // {
    //     $customerId = $request->customer_id;

    //     $reports = DB::table('old_rt_reports_list_for_observation_sheet as tr')
    //         ->leftJoin('type_of_job as toj', 'toj.id', '=', 'tr.job_type_fix')
    //         ->leftJoin('job_descriptions as jd', 'jd.id', '=', 'tr.job_desc_id')
    //             ->where('tr.customer_id', $customerId)
    //             ->select([
    //                 'tr.test_report_rt_id',
    //                 'tr.test_report_no',
    //                 'tr.revision_number',
    //                 'tr.test_report_date',
    //                 'tr.nabl_type_fix',
    //                 'tr.job_type_fix',
    //                 DB::raw("'' as part_no"),
    //                 DB::raw("'' as drg_no"),
    //                 DB::raw("'' as heat_no"),
    //                 DB::raw("'' as product_code"),
    //                 DB::raw("'' as rt_no"),
    //                 'tr.ulr_no',
    //                 'tr.customer_client',
    //                 DB::raw("'' as project_name"),
    //                 DB::raw("'' as material"),
    //                 DB::raw("toj.type_of_job"),
    //                 DB::raw("jd.job_description")
    //             ])
    //             ->orderBy('tr.test_report_rt_id', 'DESC')
    //             ->get();
    //             foreach ($reports as $report) {
    //                 $report->test_report_date = (!empty($report->test_report_date) && $report->test_report_date != '0000-00-00')
    //                     ? Date::createFromFormat('Y-m-d', $report->test_report_date)->format(DATE_FORMAT)
    //                     : '';
    //             }


    //         return response()->json([
    //             'response_code' => 1,
    //             'reports' => $reports
    //         ]);
    // }


    public function edit(Request $request)
    {
        $obs_data = DB::select('CALL observation_sheet_master(?)', [$request->id]);
        if (!empty($obs_data)) {
            $obs_data = $obs_data[0];
            $obs_data->observation_sheet_date = ($obs_data->observation_sheet_date != "" && $obs_data->observation_sheet_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $obs_data->observation_sheet_date)->format('d/m/Y')
                : "";

            $sheet_number = !empty($obs_data->observation_sheet_no) ? '_' . str_replace('/', '_', $obs_data->observation_sheet_no) : "";
            $cust = DB::table('customers')->where('id', $obs_data->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $obs_data->pdf_name = 'Observation_Sheet' . $sheet_number . $cust_name;

            foreach ($obs_data as $key => $value) {
                if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                    $obs_data->$key = base64_encode($value);
                }
            }

            $usage = DB::select('CALL observation_sheet_used_list(?)', [$request->id]);
            $usedDetailIds = !empty($usage) ? array_column($usage, 'observation_sheet_details_id') : [];
            if($usage != null && $usage != "")
            {
                $obs_data->in_use = true;
            }
            else
            {
                $obs_data->in_use = false;
            }

            $details = DB::select('CALL observation_sheet_details(?)', [$request->id]);
            foreach ($details as $d) {
                foreach ($d as $prop => $val) {
                    if (is_string($val) && !mb_check_encoding($val, 'UTF-8')) {
                        $d->$prop = base64_encode($val);
                    }
                }
            }

            foreach ($details as $d) {
                $subData = DB::select('CALL observation_sheet_details_input(?)', [$d->observation_sheet_details_id]);
                if (!empty($subData)) {
                    foreach ($subData as $s) {
                        if (isset($s->sr_no) && is_numeric($s->sr_no)) {
                            $s->sr_no = (float)$s->sr_no == (int)$s->sr_no ? (int)$s->sr_no : (float)$s->sr_no;
                        }
                        foreach ($s as $sKey => $sVal) {
                            if (is_string($sVal) && !mb_check_encoding($sVal, 'UTF-8')) {
                                $s->$sKey = base64_encode($sVal);
                            }
                        }
                    }
                }
                $d->sub_details = $subData ?? [];
                $d->mode = 'Update';
                if (empty($d->rt_no) && !empty($d->technique_sheet_rt_no)) {
                    $d->rt_no = $d->technique_sheet_rt_no;
                }
                if (!empty($d->test_report_rt_id) && $d->test_report_rt_id > 0) {
                    $tr = TestReportRt::where('test_report_rt_id', $d->test_report_rt_id)
                        ->select('test_report_no', 'revision_number')
                        ->first();
                    if ($tr) {
                        if (empty($d->rt_no)) $d->rt_no = $tr->test_report_no;
                        if (empty($d->revision_number)) $d->revision_number = $tr->revision_number;
                    }
                } else {
                    $d->test_report_rt_id = null;
                }
                if (isset($d->observation_qty) && !isset($d->quantity)) {
                    $d->quantity = $d->observation_qty;
                }
                if (!empty($d->inward_date) && $d->inward_date != "0000-00-00") {
                    $d->inward_date = Date::createFromFormat('Y-m-d', $d->inward_date)->format('d/m/Y');
                }
                if (!empty($d->dc_date) && $d->dc_date != "0000-00-00") {
                    $d->dc_date = Date::createFromFormat('Y-m-d', $d->dc_date)->format('d/m/Y');
                }
                if (!empty($d->po_date) && $d->po_date != "0000-00-00") {
                    $d->po_date = Date::createFromFormat('Y-m-d', $d->po_date)->format('d/m/Y');
                }
                $d->in_use = in_array($d->observation_sheet_details_id, $usedDetailIds);
            }

            return response()->json([
                'response_code'    => '1',
                'obs_data'         => $obs_data,
                'obs_details_data' => $details,
                'response_message' => ''
            ]);
        }

        return response()->json([
            'response_code'    => '0',
            'response_message' => 'Record Not Found.'
        ]);
    }

    public function update(Request $request)
    {
        $year_data =getCurrentYearData();
        $location_data = getCurrentLocation();
        DB::beginTransaction();
        try {
            $sheetId = $request->id;
            $sheet = ObservationSheet::where('observation_sheet_id', $sheetId)->first();
            
            if (!$sheet) {
                return response()->json(['response_code' => '0', 'response_message' => 'Record Does Not Exist']);
            }

            $obsDate = !empty($request->observation_sheet_date) 
                ? Date::createFromFormat('d/m/Y', $request->observation_sheet_date)->format('Y-m-d')
                : $sheet->observation_sheet_date;

            $sheet->update([
                'observation_sheet_sequence' => $request->observation_sheet_sequence,
                'observation_sheet_no'       => $request->observation_sheet_no,
                'observation_sheet_date'     => $obsDate,
                'customer_id'                => $request->customer_id,
                'prepared_by_user_id'        => !empty($request->prepared_by_user_id) ? $request->prepared_by_user_id : Auth::user()->id,
                'sp_note'                    => $request->special_note ?? $request->sp_note ?? '',
                'last_by'                    => Auth::user()->id,
                'last_on'                    => Carbon::now('Asia/Kolkata'),
            ]);

            $detailsData = json_decode($request->input('observation_sheet_details_data'), true);
            if (empty($detailsData) && is_array($request->observation_sheet_details_data)) {
                $detailsData = $request->observation_sheet_details_data;
            }

            if (!empty($detailsData)) {
                // Pending Qty Validation on Update
                foreach ($detailsData as $row) {
                    $mode              = $row['mode'] ?? 'Update';
                    $detailId          = $row['observation_sheet_details_id'] ?? null;
                    $test_report_rt_id = (!empty($row['test_report_rt_id']) && $row['test_report_rt_id'] != 0 && ($row['from_type_id_fix'] ?? 1) != 1) ? $row['test_report_rt_id'] : null;
                    $from_type_id_fix  = $row['from_type_id_fix'] ?? (!empty($test_report_rt_id) ? 2 : 1);
                    $enteredQty        = (float)($row['quantity'] ?? 1);

                    if ($mode === 'Delete') {
                        if ($detailId) {
                            $old_qty = (float)(ObservationSheetDetails::where('observation_sheet_details_id', $detailId)->value('observation_qty') ?? 0);
                            $checkQty = $this->qtyValidation(
                                0,
                                $old_qty,
                                'D',
                                $request->customer_id,
                                $row['material_inward_details_id'] ?? 0,
                                $from_type_id_fix,
                                $test_report_rt_id,
                                $detailId
                            );
                            if ($checkQty) {
                                return $checkQty;
                            }
                        }
                        continue;
                    }

                    $old_qty = $detailId ? (float)(ObservationSheetDetails::where('observation_sheet_details_id', $detailId)->value('observation_qty') ?? 0) : 0;
                    $diff = (float)$enteredQty - (float)$old_qty;
                    $from_qty = $diff > 0 ? $diff : 0;
                    $next_qty = $diff < 0 ? abs($diff) : 0;

                    $checkQty = $this->qtyValidation(
                        $from_qty,
                        $next_qty,
                        'U',
                        $request->customer_id,
                        $row['material_inward_details_id'] ?? 0,
                        $from_type_id_fix,
                        $test_report_rt_id,
                        $detailId
                    );

                    if ($checkQty) {
                        return $checkQty;
                    }
                }

                foreach ($detailsData as $row) {
                    $mode = $row['mode'] ?? 'Update';
                    $detailId = $row['observation_sheet_details_id'] ?? null;
                    
                    $test_report_rt_id = (!empty($row['test_report_rt_id']) && $row['test_report_rt_id'] != 0 && ($row['from_type_id_fix'] ?? 1) != 1) ? $row['test_report_rt_id'] : null;
                    $from_type_id_fix = $row['from_type_id_fix'] ?? (!empty($test_report_rt_id) ? 2 : 1);

                    if ($mode === 'Delete') {
                        if ($detailId) {
                            $dObj = ObservationSheetDetails::find($detailId);
                            ObservationSheetDetailsPrint::where('observation_sheet_details_id', $detailId)->delete();
                            ObservationSheetDetailsInput::where('observation_sheet_details_id', $detailId)->delete();
                            ObservationSheetDetails::destroy($detailId);
                        }
                    } elseif ($mode === 'Insert') {
                        $isRt = strtoupper(trim($row['type_of_testing_id_fix'] ?? '')) === 'RT';
                        $filmSizeUnit = $isRt ? (!empty($row['film_size_unit_fix']) ? $row['film_size_unit_fix'] : (!empty($row['film_size_fix']) ? $row['film_size_fix'] : 'inch')) : null;

                        $detailModel = ObservationSheetDetails::create([
                            'observation_sheet_id'       => $sheet->observation_sheet_id,
                            'material_inward_details_id' => $row['material_inward_details_id'] ?? null,
                            'technique_sheet_rt_id'      => $row['technique_sheet_rt_id'] ?? null,
                            'test_report_rt_id'          => $test_report_rt_id,
                            'from_type_id_fix'           => $from_type_id_fix,
                            'observation_qty'            => $row['quantity'] ?? 1,
                            'process_type'               => $row['process_type'] ?? 'Fresh',
                            'type_of_testing_id_fix'     => $row['type_of_testing_id_fix'],
                            'film_size_unit_fix'         => $filmSizeUnit,
                        ]);


                        if (!empty($row['sub_details']) && is_array($row['sub_details'])) {
                            foreach ($row['sub_details'] as $sub) {
                                ObservationSheetDetailsInput::create([
                                    'observation_sheet_details_id' => $detailModel->observation_sheet_details_id,
                                    'sr_no'              => $sub['sr_no'] ?? null,
                                    'identification'     => $sub['identification'] ?? null,
                                    'location'           => $sub['location'] ?? null,
                                    'source_id_fix'      => $sub['source_id_fix'] ?? null,
                                    'film_brand_id'      => $sub['film_brand_id'] ?? null,
                                    'film_type_id'       => $sub['film_type_id'] ?? null,
                                    'thickness'          => $sub['thickness'] ?? null,
                                    'sfd'                => $sub['sfd'] ?? null,
                                    //'iqi_designation_id' => $sub['iqi_designation_id'] ?? null,
                                    //'iqi_sensitivity_id' => $sub['iqi_sensitivity_id'] ?? null,
                                    'iqi_designation_id' => null,
                                    'iqi_sensitivity_id' => null,
                                    'iqi_designation'    => $sub['iqi_designation'] ?? null,
                                    'iqi_sensitivity'    => $sub['iqi_sensitivity'] ?? null,
                                    'film_id'            => $sub['film_id'] ?? null,
                                    'no_of_film_fix'     => $sub['no_of_film_fix'] ?? 'Single',
                                    'film_qty'           => $sub['film_qty'] ?? 1,
                                ]);

                                ObservationSheetDetailsPrint::create([
                                    'observation_sheet_details_id' => $detailModel->observation_sheet_details_id,
                                    'sr_no'              => $sub['sr_no'] ?? null,
                                    'identification'     => $sub['identification'] ?? null,
                                    'location'           => $sub['location'] ?? null,
                                    'source_id_fix'      => $sub['source_id_fix'] ?? null,
                                    'film_brand_id'      => $sub['film_brand_id'] ?? null,
                                    'film_type_id'       => $sub['film_type_id'] ?? null,
                                    'thickness'          => $sub['thickness'] ?? null,
                                    'sfd'                => $sub['sfd'] ?? null,
                                    //'iqi_designation_id' => $sub['iqi_designation_id'] ?? null,
                                    //'iqi_sensitivity_id' => $sub['iqi_sensitivity_id'] ?? null,
                                    'iqi_designation_id' => null,
                                    'iqi_sensitivity_id' => null,
                                    'iqi_designation'    => $sub['iqi_designation'] ?? null,
                                    'iqi_sensitivity'    => $sub['iqi_sensitivity'] ?? null,
                                    'film_id'            => $sub['film_id'] ?? null,
                                    'no_of_film_fix'     => $sub['no_of_film_fix'] ?? 'Single',
                                    'film_qty'           => $sub['film_qty'] ?? 1,
                                ]);
                            }
                        }

                        $checkPending = $this->checkNegativePendingQty(
                            $request->customer_id,
                            $location_data->location_id,
                            $from_type_id_fix,
                            $row['material_inward_details_id'] ?? 0,
                            $test_report_rt_id,
                            $row['type_of_testing_id_fix'] ?? 'RT',
                            'U'
                        );
                        if ($checkPending) {
                            return $checkPending;
                        }
                    } elseif ($mode === 'Update') {
                        if ($detailId) {
                            $isRt = strtoupper(trim($row['type_of_testing_id_fix'] ?? '')) === 'RT';
                            $filmSizeUnit = $isRt ? (!empty($row['film_size_unit_fix']) ? $row['film_size_unit_fix'] : (!empty($row['film_size_fix']) ? $row['film_size_fix'] : 'inch')) : null;

                            ObservationSheetDetails::where('observation_sheet_details_id', $detailId)->update([
                                'technique_sheet_rt_id' => $row['technique_sheet_rt_id'] ?? null,
                                'test_report_rt_id'     => $test_report_rt_id,
                                'from_type_id_fix'      => $from_type_id_fix,
                                'observation_qty'       => $row['quantity'] ?? 1,
                                'process_type'          => $row['process_type'] ?? 'Fresh',
                                'type_of_testing_id_fix' => $row['type_of_testing_id_fix'],
                                'film_size_unit_fix'     => $filmSizeUnit,
                            ]);

                            // Print gets deleted and created fresh as requested
                            ObservationSheetDetailsPrint::where('observation_sheet_details_id', $detailId)->delete();

                            if (!empty($row['sub_details']) && is_array($row['sub_details'])) {
                                $activeSubInputIds = [];
                                $printSrNo = 1;

                                foreach ($row['sub_details'] as $sub) {
                                    $subMode = $sub['mode'] ?? 'Update';
                                    $subInputId = $sub['observation_sheet_details_details_input_id'] ?? null;

                                    if ($subMode === 'Delete') {
                                        if ($subInputId) {
                                             $detailUsage = DB::table('test_report_rt')
                                                 ->where('observation_sheet_details_id', $detailId)
                                                 ->exists();
                                             if ($detailUsage) {
                                                 DB::rollBack();
                                                 return response()->json([
                                                     'response_code' => '0',
                                                     'response_message' => "You Can't Delete, Observation Detail Is Used In Test Report (RT)."
                                                 ]);
                                             }
                                            ObservationSheetDetailsInput::where('observation_sheet_details_details_input_id', $subInputId)->delete();
                                        }
                                    } else {
                                        $formattedSrNo = isset($sub['sr_no']) && is_numeric($sub['sr_no']) ? ((float)$sub['sr_no'] == (int)$sub['sr_no'] ? (int)$sub['sr_no'] : (float)$sub['sr_no']) : ($sub['sr_no'] ?? $printSrNo);
                                        if (!empty($subInputId)) {
                                            $activeSubInputIds[] = $subInputId;
                                            ObservationSheetDetailsInput::where('observation_sheet_details_details_input_id', $subInputId)->update([
                                                'observation_sheet_details_id' => $detailId,
                                                'sr_no'              => $formattedSrNo,
                                                'identification'     => $sub['identification'] ?? null,
                                                'location'           => $sub['location'] ?? null,
                                                'source_id_fix'      => $sub['source_id_fix'] ?? null,
                                                'film_brand_id'      => $sub['film_brand_id'] ?? null,
                                                'film_type_id'       => $sub['film_type_id'] ?? null,
                                                'thickness'          => $sub['thickness'] ?? null,
                                                'sfd'                => $sub['sfd'] ?? null,
                                                //'iqi_designation_id' => $sub['iqi_designation_id'] ?? null,
                                                //'iqi_sensitivity_id' => $sub['iqi_sensitivity_id'] ?? null,
                                                'iqi_designation_id' => null,
                                                'iqi_sensitivity_id' => null,
                                                'iqi_designation'    => $sub['iqi_designation'] ?? null,
                                                'iqi_sensitivity'    => $sub['iqi_sensitivity'] ?? null,
                                                'film_id'            => $sub['film_id'] ?? null,
                                                'no_of_film_fix'     => $sub['no_of_film_fix'] ?? 'Single',
                                                'film_qty'           => $sub['film_qty'] ?? 1,
                                            ]);
                                        } else {
                                            $newInput = ObservationSheetDetailsInput::create([
                                                'observation_sheet_details_id' => $detailId,
                                                'sr_no'              => $formattedSrNo,
                                                'identification'     => $sub['identification'] ?? null,
                                                'location'           => $sub['location'] ?? null,
                                                'source_id_fix'      => $sub['source_id_fix'] ?? null,
                                                'film_brand_id'      => $sub['film_brand_id'] ?? null,
                                                'film_type_id'       => $sub['film_type_id'] ?? null,
                                                'thickness'          => $sub['thickness'] ?? null,
                                                'sfd'                => $sub['sfd'] ?? null,
                                                //'iqi_designation_id' => $sub['iqi_designation_id'] ?? null,
                                                //'iqi_sensitivity_id' => $sub['iqi_sensitivity_id'] ?? null,
                                                'iqi_designation_id' => null,
                                                'iqi_sensitivity_id' => null,
                                                'iqi_designation'    => $sub['iqi_designation'] ?? null,
                                                'iqi_sensitivity'    => $sub['iqi_sensitivity'] ?? null,
                                                'film_id'            => $sub['film_id'] ?? null,
                                                'no_of_film_fix'     => $sub['no_of_film_fix'] ?? 'Single',
                                                'film_qty'           => $sub['film_qty'] ?? 1,
                                            ]);
                                            $activeSubInputIds[] = $newInput->observation_sheet_details_details_input_id;
                                        }

                                        // Print gets created fresh for active sub details
                                        ObservationSheetDetailsPrint::create([
                                            'observation_sheet_details_id' => $detailId,
                                            'sr_no'              => $formattedSrNo,
                                            'identification'     => $sub['identification'] ?? null,
                                            'location'           => $sub['location'] ?? null,
                                            'source_id_fix'      => $sub['source_id_fix'] ?? null,
                                            'film_brand_id'      => $sub['film_brand_id'] ?? null,
                                            'film_type_id'       => $sub['film_type_id'] ?? null,
                                            'thickness'          => $sub['thickness'] ?? null,
                                            'sfd'                => $sub['sfd'] ?? null,
                                            //'iqi_designation_id' => $sub['iqi_designation_id'] ?? null,
                                            //'iqi_sensitivity_id' => $sub['iqi_sensitivity_id'] ?? null,
                                            'iqi_designation_id' => null,
                                            'iqi_sensitivity_id' => null,
                                            'iqi_designation'    => $sub['iqi_designation'] ?? null,
                                            'iqi_sensitivity'    => $sub['iqi_sensitivity'] ?? null,
                                            'film_id'            => $sub['film_id'] ?? null,
                                            'no_of_film_fix'     => $sub['no_of_film_fix'] ?? 'Single',
                                            'film_qty'           => $sub['film_qty'] ?? 1,
                                        ]);

                                        $printSrNo++;
                                    }
                                }

                                ObservationSheetDetailsInput::where('observation_sheet_details_id', $detailId)
                                    ->whereNotIn('observation_sheet_details_details_input_id', $activeSubInputIds)
                                    ->delete();
                            }

                            $checkPending = $this->checkNegativePendingQty(
                                $request->customer_id,
                                $location_data->location_id,
                                $from_type_id_fix,
                                $row['material_inward_details_id'] ?? 0,
                                $test_report_rt_id,
                                $row['type_of_testing_id_fix'] ?? 'RT',
                                'U'
                            );
                            if ($checkPending) {
                                return $checkPending;
                            }
                        }
                    }
                }
            }

            // Delete old generated PDF files for this observation sheet to ensure they are regenerated fresh
            $sheet_number = !empty($sheet->observation_sheet_no) ? '_' . str_replace('/', '_', $sheet->observation_sheet_no) : "";
            $cust = DB::table('customers')->where('id', $sheet->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Observation_Sheet' . $sheet_number . $cust_name;

            $localDirectory = storage_path("app/public/reports/observation_sheet_reports_file/");
            $pattern = $localDirectory . $pdf_name . '*';
            foreach (glob($pattern) as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }

            DB::commit();

            $sheet_number = !empty($sheet->observation_sheet_no) ? '_' . str_replace('/', '_', $sheet->observation_sheet_no) : "";
            $cust = DB::table('customers')->where('id', $sheet->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Observation_Sheet' . $sheet_number . $cust_name;

            $this->generateAndMergePdf($sheetId, $pdf_name);

            $encodedId = base64_encode($sheetId);
            $url = hasAccess("observation_sheet", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=observation_sheet") : "";

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

    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            $usage = DB::select('CALL observation_sheet_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                $message = "You Can't Delete, Observation Sheet Is Used In " . $usage[0]->table_name . ".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            $detailIds = ObservationSheetDetails::where('observation_sheet_id', $request->id)->pluck('observation_sheet_details_id');
            
            ObservationSheetDetailsPrint::whereIn('observation_sheet_details_id', $detailIds)->delete();
            ObservationSheetDetailsInput::whereIn('observation_sheet_details_id', $detailIds)->delete();
            ObservationSheetDetails::where('observation_sheet_id', $request->id)->delete();
            ObservationSheet::where('observation_sheet_id', $request->id)->delete();

            DB::commit();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();
            return response()->json([
                'response_code' => '0',
                'response_message' => getResponseMessage('delete_error'),
            ]);
        }
    }

    public function getLatestObservationSheetNumber(Request $request)
    {
        $modal = ObservationSheet::class;
        $sequence = 'observation_sheet_sequence';
        $prefix = 'OS';

        $num_format = getLatestSequence($modal, $sequence, $prefix);

        return response()->json([
            'response_code' => 1,
            'latest_no' => $num_format['format'],
            'number' => $num_format['isFound'],

        ]);
    }
    

    public function getPendingCustomers(Request $request)
    {
        $location = getCurrentLocation()->location_id;
        $year = getCompanyYearIdsToTill();

        $sheetId = $request->input('sheet_id') ?? $request->input('id');
        $currentCustomerId = null;
        if ($sheetId) {
            $currentCustomerId = DB::table('observation_sheet')->where('observation_sheet_id', $sheetId)->value('customer_id');
        }

        $currentCustomer = null;
        if ($currentCustomerId) {
            $currentCustomer = DB::table('customers')
                ->where('id', $currentCustomerId)
                ->select('id', 'customer')
                ->first();
        }

        $customers = DB::table('customers')
            ->join('material_inward as mi', 'mi.customer_id', '=', 'customers.id')
            ->join('material_inward_details as mid', 'mid.material_inward_id', '=', 'mi.material_inward_id')
            ->join('pending_observation_sheet_qty as pend', 'pend.material_inward_details_id', '=', 'mid.material_inward_details_id')
            ->where('pend.current_location_id', $location)
            ->whereIn('mi.year_id', $year)
            ->where('pend.pending_qty', '>', 0)
            ->select('customers.id', 'customers.customer')
            ->distinct()
            ->orderBy('customers.customer', 'asc')
            ->get()
            ->toArray();

        if ($currentCustomer) {
            $exists = false;
            foreach ($customers as $c) {
                if ($c->id == $currentCustomer->id) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $customers[] = $currentCustomer;
            }
        }

        return response()->json([
            'customers' => $customers
        ]);
    }

    public function getPendingInwardData(Request $request)
    {
        $location = getCurrentLocation()->location_id;
        $year = getCompanyYearIdsToTill();

        $pending_data = DB::table('pending_observation_sheet_qty as pend')
            ->join('material_inward_details as mid', 'mid.material_inward_details_id', '=', 'pend.material_inward_details_id')
            ->join('material_inward as mi', 'mi.material_inward_id', '=', 'mid.material_inward_id')
            ->leftJoin('test_report_rt as tr', 'tr.test_report_rt_id', '=', 'pend.test_report_rt_id')
            ->leftJoin('type_of_job as toj', 'toj.id', '=', 'mid.type_of_job_id')
            ->leftJoin('job_descriptions as jd', 'jd.id', '=', 'mid.job_desc_id')
            ->leftJoin('materials as m', 'm.id', '=', 'mid.material_id')
            ->leftJoin('area_of_coverage as ac', 'ac.area_of_coverage_id', '=', 'mid.area_of_coverage_id')
            ->leftJoin('procedure_reference as pr', 'pr.procedure_reference_id', '=', 'mid.procedure_ref_id')
            ->leftJoin('evaluation_as_per as ev', 'ev.evaluation_as_per_id', '=', 'mid.evaluation_as_per_id')
            ->leftJoin('acceptance_standards as as_std', 'as_std.id', '=', 'mid.acceptance_standard_id')
            ->where('mi.customer_id', $request->customer_id)
            ->where('pend.current_location_id', $location)
            ->whereIn('mi.year_id', $year)
            ->where('pend.pending_qty', '>', 0)
            ->select([
                'pend.material_inward_details_id',
                'pend.test_report_rt_id',
                'pend.from_type_id_fix',
                'pend.pending_qty',
                'mid.type_of_testing_id_fix',
                DB::raw("IF(pend.test_report_rt_id > 0, 'Repair', mid.process_type) as process_type"),
                'mid.type_of_job_id',
                'mid.job_desc_id',
                'mid.part_id',
                'mid.material_id',
                'mid.heat_no',
                DB::raw("IF(pend.test_report_rt_id > 0, tr.test_report_no, '') as rt_no"),
                DB::raw("IF(pend.test_report_rt_id > 0, tr.revision_number, '') as revision_number"),
                'mid.product_code',
                'mid.thickness',
                'mid.area_of_coverage_id',
                'mid.procedure_ref_id',
                'mid.evaluation_as_per_id',
                'mid.acceptance_standard_id',
                'mid.quantity as inward_qty',
                'mid.remark',
                'mi.customer_id',
                'mi.dc_no',
                'mi.dc_date',
                'mi.po_no',
                'mi.po_date',
                'mi.nabl_type_fix',
                'mi.test_at_fix',
                'mi.job_type_fix',
                'mi.material_inward_no',
                'mi.material_inward_date',
                'toj.type_of_job',
                'jd.job_description',
                'mid.part_no',
                'mid.drg_no',
                'm.material'
            ])
            ->get();

        foreach ($pending_data as $row) {
            $row->material_inward_date = ($row->material_inward_date != "" && $row->material_inward_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $row->material_inward_date)->format('d/m/Y')
                : "";
            $row->dc_date = ($row->dc_date != "" && $row->dc_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $row->dc_date)->format('d/m/Y')
                : "";
            $row->po_date = ($row->po_date != "" && $row->po_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $row->po_date)->format('d/m/Y')
                : "";
        }

        return response()->json([
            'response_code' => 1,
            'pending_data' => $pending_data
        ]);
    }

    public function getPendingTechniqueSheetList(Request $request)
    {
        $current_location_id = getCurrentLocation()->location_id;
        $yearIds = getCompanyYearIdsToTill();

        $query = TechniqueSheetRt::select([
            'technique_sheet_rt.technique_sheet_rt_id',
            'technique_sheet_rt.technique_sheet_rt_no',
            'technique_sheet_rt.technique_sheet_rt_date',
            DB::raw("'Regular' as nabl_type_fix"),
            DB::raw("'' as rev_no"),
            DB::raw("'' as ulr_no"),
            DB::raw("'' as party"),
            DB::raw("'' as project_name"),
            DB::raw("'' as material"),
            'c.customer',
            'toj.type_of_job',
            'technique_sheet_rt.film_type',
            'jd.job_description',
            'technique_sheet_rt.part_no',
            'technique_sheet_rt.drg_no',
            'aoc.area_of_coverage'
        ])
        ->leftJoin('customers as c', 'c.id', '=', 'technique_sheet_rt.customer_id')
        ->leftJoin('type_of_job as toj', 'toj.id', '=', 'technique_sheet_rt.type_of_job_id')
        ->leftJoin('job_descriptions as jd', 'jd.id', '=', 'technique_sheet_rt.job_desc_id')
        ->leftJoin('area_of_coverage as aoc', 'aoc.area_of_coverage_id', '=', 'technique_sheet_rt.area_of_coverage_id')
        ->where('technique_sheet_rt.current_location_id', $current_location_id)
        ->whereIn('technique_sheet_rt.year_id', $yearIds);

        $sheets = $query->get();

        foreach ($sheets as $s) {
            $s->technique_sheet_rt_date = ($s->technique_sheet_rt_date != "" && $s->technique_sheet_rt_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $s->technique_sheet_rt_date)->format('d/m/Y')
                : "";
        }

        return response()->json([
            'response_code' => 1,
            'sheets' => $sheets
        ]);
    }

    // public function qtyValidation($from_qty, $transaction_mode = 'I', $customer_id = 0, $material_inward_details_id = 0, $from_type_id_fix = 1, $test_report_rt_id = 0, $observation_sheet_details_id = 0)
    // {
    //     $location_id = getCurrentLocation()->location_id;
    //     $test_report_rt_id = ($from_type_id_fix == 1) ? 0 : ($test_report_rt_id ?? 0);
    //
    //     if ($transaction_mode === 'D') {
    //         if (!empty($observation_sheet_details_id)) {
    //             $usage = DB::table('test_report_rt')
    //                 ->where('observation_sheet_details_id', $observation_sheet_details_id)
    //                 ->exists();
    //
    //             if ($usage) {
    //                 DB::rollBack();
    //                 return response()->json([
    //                     'response_code'    => '0',
    //                     'response_message' => "You Can't Delete, Observation Detail Is Used In Test Report (RT).",
    //                 ]);
    //             }
    //         }
    //         return null;
    //     }
    //
    //     $oldQty = 0;
    //     if ($transaction_mode === 'U' && !empty($observation_sheet_details_id)) {
    //         $oldQty = (float)(DB::table('observation_sheet_details')
    //             ->where('observation_sheet_details_id', $observation_sheet_details_id)
    //             ->value('observation_qty') ?? 0);
    //     }
    //
    //     $pendingQty = (float)(DB::table('pending_observation_sheet_qty')
    //         ->where('customer_id', $customer_id)
    //         ->where('from_type_id_fix', $from_type_id_fix)
    //         ->where('material_inward_details_id', $material_inward_details_id)
    //         ->where('test_report_rt_id', $test_report_rt_id)
    //         ->where('current_location_id', $location_id)
    //         ->value('pending_qty') ?? 0);
    //
    //     $maxAllowed = $pendingQty + $oldQty;
    //
    //     if ((float)$from_qty > (float)$maxAllowed) {
    //         DB::rollBack();
    //         return response()->json([
    //             'response_code'    => '0',
    //             'response_message' => 'Observation Qty. cannot be more than Pending Qty.',
    //         ]);
    //     }
    //
    //     return null;
    // }

    public function qtyValidation($from_qty, $next_qty, $transaction_mode, $customer_id, $material_inward_details_id, $from_type_id_fix, $test_report_rt_id, $observation_sheet_details_id)
    {
        $location_id = getCurrentLocation()->location_id;
        $test_report_rt_id = ($from_type_id_fix == 1) ? null : ($test_report_rt_id ?? 0);

        // ===================== 1. Observation Sheet VALIDATION FOR MATERIAL INWARD =====================
        if ($from_qty > 0) {
            // 🔒 Pessimistic Lock
            if ($from_type_id_fix == 1 && !empty($material_inward_details_id)) {
                DB::table('material_inward_details')
                    ->where('material_inward_details_id', $material_inward_details_id)
                    ->lockForUpdate()
                    ->first();
            } elseif ($from_type_id_fix == 2 && !empty($test_report_rt_id)) {
                DB::table('test_report_rt')
                    ->where('test_report_rt_id', $test_report_rt_id)
                    ->lockForUpdate()
                    ->first();
            }

            $pending_obs_qty = DB::table('pending_observation_sheet_qty')
                ->where('customer_id', $customer_id)
                ->where('from_type_id_fix', $from_type_id_fix)
                ->where('material_inward_details_id', $material_inward_details_id)
                ->where('test_report_rt_id', $test_report_rt_id)
                ->where('current_location_id', $location_id)
                ->value('pending_qty') ?? 0;

            if ((float)$pending_obs_qty < (float)$from_qty) {
                DB::rollBack();
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => 'Observation Qty. Is Used.',
                ]);
            }
        }

        // ===================== 2. Observation Sheet VALIDATION FOR TEST REPORT =====================
        if ($next_qty > 0 && !empty($observation_sheet_details_id)) {
            $obsDetail = DB::table('observation_sheet_details')
                ->where('observation_sheet_details_id', $observation_sheet_details_id)
                ->first();

            $testing_type = $obsDetail->type_of_testing_id_fix ?? 'RT';

            if ($testing_type === 'RT') {
                // RT View માં observation_sheet_details_id કૉલમ છે
                $pending_report_qty = DB::table('pending_material_inward_rt_qty')
                    ->where('observation_sheet_details_id', $observation_sheet_details_id)
                    ->where('current_location_id', $location_id)
                    ->value('pending_qty') ?? 0;
            } else {
                // UT / DPT / MPT Views માં material_inward_details_id થી ચેક થશે
                $view_name = 'pending_material_inward_ut_qty';
                if ($testing_type === 'DPT') {
                    $view_name = 'pending_material_inward_dpt_qty';
                } elseif ($testing_type === 'MPT') {
                    $view_name = 'pending_material_inward_mpt_qty';
                }

                $mid_id = $obsDetail->material_inward_details_id ?? $material_inward_details_id;

                $pending_report_qty = DB::table($view_name)
                    ->where('material_inward_details_id', $mid_id)
                    ->where('current_location_id', $location_id)
                    ->value('pending_qty') ?? 0;
            }

            if ((float)$pending_report_qty < (float)$next_qty) {
                DB::rollBack();

                $message = ($transaction_mode === 'D')
                    ? "You Can't Delete, Observation Sheet Is Used In Test Report ({$testing_type})."
                    : "You Can't Update, Observation Qty. Is Used In Test Report ({$testing_type}).";

                return response()->json([
                    'response_code'    => '0',
                    'response_message' => $message,
                ]);
            }
        }

        return null; // No error
    }


    public function checkNegativePendingQty($customer_id, $location_id, $from_type_id_fix, $material_inward_details_id, $test_report_rt_id, $testing_type = 'RT', $transaction_mode = 'U')
    {
        $test_report_rt_id = ($from_type_id_fix == 1) ? null : ($test_report_rt_id ?? null);

        // 1. Check pending_observation_sheet_qty
        $pendingQuery = DB::table('pending_observation_sheet_qty')
            ->where('customer_id', $customer_id)
            ->where('from_type_id_fix', $from_type_id_fix)
            ->where('material_inward_details_id', $material_inward_details_id)
            ->where('test_report_rt_id', $test_report_rt_id)
            ->where('current_location_id', $location_id);

        $pendingQty = $pendingQuery->value('pending_qty');

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

        // 2. Check material inward testing view if from inward
        if ($from_type_id_fix == 1 && !empty($material_inward_details_id)) {
            $inwardPendingQty = null;
            if ($testing_type === 'RT') {
                $inwardPendingQty = DB::table('pending_material_inward_rt_qty')
                    ->where('material_inward_details_id', $material_inward_details_id)
                    ->value('pending_qty');
            } elseif ($testing_type === 'UT') {
                $inwardPendingQty = DB::table('pending_material_inward_ut_qty')
                    ->where('material_inward_details_id', $material_inward_details_id)
                    ->value('pending_qty');
            } elseif ($testing_type === 'DPT') {
                $inwardPendingQty = DB::table('pending_material_inward_dpt_qty')
                    ->where('material_inward_details_id', $material_inward_details_id)
                    ->value('pending_qty');
            } elseif ($testing_type === 'MPT') {
                $inwardPendingQty = DB::table('pending_material_inward_mpt_qty')
                    ->where('material_inward_details_id', $material_inward_details_id)
                    ->value('pending_qty');
            }

            if ($inwardPendingQty !== null && (float)$inwardPendingQty < 0) {
                DB::rollBack();
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => $msg,
                ]);
            }
        }

        return null;
    }

    private function generateAndMergePdf($id, $pdf_name)
    {
        $testTypes = DB::table('observation_sheet_details')
            ->where('observation_sheet_id', $id)
            ->whereNotNull('type_of_testing_id_fix')
            ->pluck('type_of_testing_id_fix')
            ->unique()
            ->toArray();

        if (empty($testTypes)) {
            $testTypes = ['RT'];
        }

        $localDirectory = storage_path('app/public/reports/observation_sheet_reports_file/');
        if (!is_dir($localDirectory)) {
            mkdir($localDirectory, 0777, true);
        }

        // Delete any existing files with this prefix to avoid leftover versions
        $pattern = $localDirectory . $pdf_name . '*';
        foreach (glob($pattern) as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        $individualFiles = [];
        foreach ($testTypes as $testType) {
            $testTypeLower = strtolower($testType);
            $individualName = $pdf_name . '_' . $testTypeLower;
            $individualPath = $localDirectory . $individualName . '.pdf';

            GeneratePdf($id, $individualName, 'observation_sheet', 'listing', null, null, $testTypeLower);
            sleep(2);
            
            if (file_exists($individualPath)) {
                $individualFiles[] = $individualPath;
            }
        }

        if (!empty($individualFiles)) {
            $pdf = new \setasign\Fpdi\Fpdi();
            $hasPages = false;
            foreach ($individualFiles as $file) {
                if (file_exists($file)) {
                    try {
                        $pageCount = $pdf->setSourceFile($file);
                        for ($i = 1; $i <= $pageCount; $i++) {
                            $templateId = $pdf->importPage($i);
                            $size = $pdf->getTemplateSize($templateId);
                            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                            $pdf->useTemplate($templateId);
                            $hasPages = true;
                        }
                    } catch (\Exception $e) {
                        \Log::error("Failed to merge PDF page from {$file}: " . $e->getMessage());
                    }
                }
            }
            if ($hasPages) {
                $pdf->Output('F', $localDirectory . $pdf_name . '.pdf');
            } else {
                copy($individualFiles[0], $localDirectory . $pdf_name . '.pdf');
            }
        }
    }
}
