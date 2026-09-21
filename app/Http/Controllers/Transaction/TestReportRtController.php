<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\TestReportRt;
use App\Models\Transaction\TestReportRtDetails;
use App\Models\Transaction\TechniqueSheetRt;
use App\Models\RTCamera;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class TestReportRtController extends Controller
{
    public function manage()
    {
        $customers = getCustomers();
        return view('manage.transaction.manage-test_report_rt', compact('customers'));
    }

    public function index(Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();

        $reports = TestReportRt::select([
            'test_report_rt.test_report_rt_id as id',
            'test_report_rt.test_report_no',
            'test_report_rt.revision_number',
            'test_report_rt.test_report_sequence',
            'test_report_rt.test_report_date',
            'test_report_rt.nabl_type_fix',
            'test_report_rt.ulr_no',
            'test_report_rt.test_carried_out_at',
            'test_report_rt.job_type_fix',
            'test_report_rt.process_type',
            'customers.customer',
            'material_inward.dc_no',
            'material_inward.dc_date',
            'material_inward.po_no',
            'material_inward.po_date',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            'test_report_rt.part_no',
            'test_report_rt.drg_no',
            'materials.material',
            'test_report_rt.heat_no',
            'test_report_rt.rt_no',
            'test_report_rt.product_code',
            'test_report_rt.rt_report_qty',
            'test_report_rt.created_on',
            'test_report_rt.created_by',
            'test_report_rt.last_by',
            'test_report_rt.last_on'
        ])
        ->leftJoin('customers', 'customers.id', '=', 'test_report_rt.customer_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'test_report_rt.type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'test_report_rt.job_desc_id')
        ->leftJoin('part', 'part.part_id', '=', 'test_report_rt.part_id')
        ->leftJoin('materials', 'materials.id', '=', 'test_report_rt.material_id')
        ->leftJoin('material_inward_details', 'material_inward_details.material_inward_details_id', '=', 'test_report_rt.material_inward_details_id')
        ->leftJoin('material_inward', 'material_inward.material_inward_id', '=', 'material_inward_details.material_inward_id')
        ->where('test_report_rt.year_id', $year_data->id)
        ->where('test_report_rt.current_location_id', $location_data->location_id);

        if ($request->filled('customer_id')) {
            $reports->where('test_report_rt.customer_id', $request->customer_id);
        }

        $dataTable = DataTables::of($reports)
        ->filterColumn('test_report_rt.test_report_no', function($query, $keyword) {
            applySequenceSearch($query, $keyword, 'test_report_rt.test_report_no');
        })
        ->editColumn('test_report_date', function($report) {
            return $report->test_report_date != null
                ? Date::createFromFormat('Y-m-d', $report->test_report_date)->format(DATE_FORMAT)
                : '';
        })
        ->editColumn('dc_date', function($report) {
            return $report->dc_date != null
                ? Date::createFromFormat('Y-m-d', $report->dc_date)->format(DATE_FORMAT)
                : '';
        })
        ->editColumn('po_date', function($report) {
            return $report->po_date != null
                ? Date::createFromFormat('Y-m-d', $report->po_date)->format(DATE_FORMAT)
                : '';
        })
        ->filterColumn('test_report_rt.test_report_date', function ($q, $k) {
            applyDate($q, $k, 'test_report_rt.test_report_date');
        })
        ->filterColumn('material_inward.dc_date', function ($q, $k) {
            applyDate($q, $k, 'material_inward.dc_date');
        })
        ->filterColumn('material_inward.po_date', function ($q, $k) {
            applyDate($q, $k, 'material_inward.po_date');
        })
        ->filterColumn('part_no', function($query, $keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('test_report_rt.part_no', 'like', "%{$keyword}%");
            });
        })
        ->filterColumn('drg_no', function($query, $keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('test_report_rt.drg_no', 'like', "%{$keyword}%");
            });
        })
        ->addColumn('options', function($report) {
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("test_report_rt", "print")) {
                    $report_number = !empty($report->test_report_no) ? '_' . str_replace('/', '_', $report->test_report_no) : "";
                    $cust_name = !empty($report->customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $report->customer) : "";
                    $pdfName   = 'Test_Report_RT' . $report_number . $cust_name;
                    $encodedId = base64_encode($report->id);
                    $nabl_type = str_replace(' ', '_', $report->nabl_type_fix);
                    // $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=test_report_rt&job_type_fix={$report->job_type_fix}&nabl_type_fix={$nabl_type}&repair=no");
                    $url = url("/print-test_report_rt?id={$encodedId}&name={$pdfName}&job_type_fix={$report->job_type_fix}&nabl_type_fix={$nabl_type}&repair=no");
                    $action .= '<li><a class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';

                    if (strtolower($report->process_type) === 'repair') {
                        $rev_suffix = !empty($report->revision_number) ? '_' . trim($report->revision_number) : '_R';
                        $pdfNameRev = 'Test_Report_RT' . $report_number . $rev_suffix . $cust_name;
                        // $urlRev = url("/check-file_exists?id={$encodedId}&name={$pdfNameRev}&type=test_report_rt&job_type_fix={$report->job_type_fix}&nabl_type_fix={$nabl_type}&repair=yes");
                        $urlRev = url("/print-test_report_rt?id={$encodedId}&name={$pdfNameRev}&job_type_fix={$report->job_type_fix}&nabl_type_fix={$nabl_type}&repair=yes");
                        $action .= '<li><a class="dropdown-item" target="_blank" href="' . $urlRev . '"><i class="bx bxs-file-pdf align-bottom me-2 text-warning" id="print_rev_a"></i> Rev. Print</a></li>';
                    }
                }

                if (hasAccess("test_report_rt", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-test_report_rt"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("test_report_rt", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });

        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'test_report_rt');
        return $dataTable
        ->rawColumns(['options', 'last_by', 'last_on', 'created_by', 'created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();

        $request->validate([
            'test_report_sequence' => 'required',
            'test_report_date' => 'required',
            'customer_id' => 'required',
            'type_of_job_id' => 'required',
            'job_desc_id' => 'required',
            // 'part_no' => 'required',
            'material_id' => 'required',
            'area_of_coverage_id' => 'required',
        ]);

        $receiptDate = !empty($request->date_of_receipt) ? Carbon::createFromFormat('d/m/Y', $request->date_of_receipt)->format('Y-m-d') : null;
        $testDate = !empty($request->date_of_testing) ? Carbon::createFromFormat('d/m/Y', $request->date_of_testing)->format('Y-m-d') : null;
        $reportDate = !empty($request->test_report_date) ? Carbon::createFromFormat('d/m/Y', $request->test_report_date)->format('Y-m-d') : null;

        if ($testDate && $receiptDate && $testDate < $receiptDate) {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Date Of Receipt Must Be Less Than Date Of Testing.',
            ]);
        }
        if ($reportDate && $receiptDate && $reportDate < $receiptDate) {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Date Of Receipt Must Be Less Than Report Date.',
            ]);
        }
        if ($reportDate && $testDate && $reportDate < $testDate) {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Date Of Testing Must Be Less Than Report Date.',
            ]);
        }

        DB::beginTransaction();
        try {
            $qtyCheck = $this->qtyValidation(
                $request->rt_report_qty ?? 0,
                0,
                'I',
                $request->customer_id,
                $request->material_inward_details_id,
                $request->observation_sheet_details_id,
                $request->revision_from_report_rt_id,
                $request->from_type_id_fix,
                0
            );
            if ($qtyCheck) {
                return $qtyCheck;
            }

            $isRevision = $request->filled('revision_number');
            if ($isRevision) {
                $report_no = $request->test_report_no;
                $report_seq = $request->test_report_sequence;
                $baseReportId = $request->revision_test_report_rt_id;
               
            } else {
                $existNumber = TestReportRt::where([
                    ['test_report_sequence', $request->test_report_sequence],
                    ['test_report_no', $request->test_report_no],
                    ['year_id', $year_data->id],
                    ['current_location_id', $location_data->location_id]
                ])->first();

                if ($existNumber) {
                    $latestNo = $this->getLatestTestReportRtNumber($request);
                    $area = json_decode($latestNo->getContent(), true);
                    $report_no = $area['latest_no'];
                    $report_seq = $area['number'];
                } else {
                    $report_no = $request->test_report_no;
                    $report_seq = $request->test_report_sequence;
                }
            }

            $shootingSketchPath = null;
            $blobImage = null;
            if ($request->shooting_sketch_image_doc) {
                $file = new File();
                $isFound = $file->getFileFromTemp($request->shooting_sketch_image_doc, 'test_report_rt');
                if ($isFound !== false) {
                    $shootingSketchPath = $isFound;
                    $filePath = storage_path('app/public/' . $shootingSketchPath);
                    if (file_exists($filePath)) {
                        $blobImage = file_get_contents($filePath);
                    }
                }
            }

            $ulr_id = null;
            $ulr_no = null;
            $ulr_sequence = null;
            $ulr_year = null;

            if ($request->nabl_type_fix === 'NABL') {
                $ulr_id = $request->ulr_id;
                
                $dateVal = Carbon::createFromFormat('d/m/Y', $request->test_report_date)->format('Y-m-d');
                $DateObj = new \DateTime($dateVal);
                $ulr_year = $DateObj->format("Y");

                $baseReportId = $request->revision_test_report_rt_id ?? null;
                if (isDuplicateUlrSequence($request->ulr_sequence, $ulr_id, $ulr_year, $location_data->location_id, TestReportRt::class, null, $isRevision, $baseReportId)) {
                    return response()->json([
                        'response_code' => 0,
                        'response_message' => 'Duplicate ULR No. Found.'
                    ]);
                }
                $ulr_no = $request->ulr_no;
                $ulr_sequence = $request->ulr_sequence;
            }
            $page_id = getMenuIdBassedOnDisplayName('test_report_rt');
            $assign_format_no = getAssignFormateNoForTransaction($location_data->location_id, $page_id->id,$request->test_report_date);

            $details = json_decode($request->report_details_data, true);
            $isRevision = $request->filled('revision_number') || !empty($request->revision_test_report_rt_id);

            $mms_ir_192_sqin = 0.0;
            $mms_co_60_sqin = 0.0;
            $mms_x_ray_sqin = 0.0;
            $mms_ir_192_repair_sqin = 0.0;
            $mms_co_60_repair_sqin = 0.0;
            $mms_x_ray_repair_sqin = 0.0;

            if (!empty($details)) {
                $filmResults = DB::table('film_result')->pluck('film_result_type_fix', 'film_result_id')->all();
                foreach ($details as &$row) {
                    if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;

                    $resultId = $row['detail_film_result_id'] ?? ($row['result_id'] ?? null);
                    $filmResultType = '';
                    if ($resultId && isset($filmResults[$resultId])) {
                        $filmResultType = strtolower(trim($filmResults[$resultId]));
                    } else {
                        $filmResultType = strtolower(trim($row['film_result_name'] ?? ($row['film_result'] ?? '')));
                    }

                    // $isPassResult = in_array($filmResultType, ['accepted', 'not accepted']);
                    $isPassResult = in_array($filmResultType, ['accepted']);
                    $isRepairResult = in_array($filmResultType, ['repair', 'reshoot', 'retake']);

                    // if ($isRevision) {
                    //     if ($isPassResult) {
                    //         $row['record_type_id'] = 3;
                    //     } else {
                    //         $row['record_type_id'] = 2;
                    //     }
                    // } else {
                    //     if ($isRepairResult) {
                    //         $row['record_type_id'] = 2;
                    //     } else {
                    //         $row['record_type_id'] = 1;
                    //     }
                    // }

                    // $includeInMs = strtolower(trim($row['include_in_measurement_sheet'] ?? 'Yes'));
                    // if ($includeInMs !== 'yes' && $includeInMs !== '1' && $includeInMs !== 'true') {
                    //     continue;
                    // }

                    $source = $row['source_id_fix'] ?? '';
                    $recType = intval($row['record_type_id'] ?? 1);
                    $totalSqIn = floatval($row['total_sq_in'] ?? 0);

                    if ($recType == 1) {
                        
                        // Fresh: પહેલી વખતે તમામ જોઈન્ટ્સનું Fresh બિલિંગ
                        if ($source === 'Ir-192') {
                            $mms_ir_192_sqin += $totalSqIn;
                        } elseif ($source === 'Co-60') {
                            $mms_co_60_sqin += $totalSqIn;
                        } elseif ($source === 'X-Ray') {
                            $mms_x_ray_sqin += $totalSqIn;
                        }
                       
                    } elseif ($recType == 2) {
                        if (strtolower(trim($filmResultType)) === 'repair') {
                                // Repair: રિવિઝનમાં માત્ર જે રિપેર રહેલા છે તેનું જ Repair બિલિંગ
                            if ($source === 'Ir-192') {
                                $mms_ir_192_repair_sqin += $totalSqIn;
                            } elseif ($source === 'Co-60') {
                                $mms_co_60_repair_sqin += $totalSqIn;
                            } elseif ($source === 'X-Ray') {
                                $mms_x_ray_repair_sqin += $totalSqIn;
                            }
                           
                        }
                        
                    }
                    // recType == 3 (Ok): પહેલાથી બિલિંગ લીધેલ હોવાથી કંઈ નહિ (0.00)
                }
                unset($row);
            }

            // Calculate total_no_of_films_rev & total_area_in_sq_rev for Repair joints (record_type_id == 2)
            $revTotals = $this->calculateRevisionTotals($details, $request->film_size_unit_fix ?? 'inch');
            $allSummaries = $this->calculateAllHeaderSummaries($details, $request->film_size_unit_fix ?? 'inch');
            $total_no_of_films_rev = $revTotals['total_no_of_films_rev'];
            $total_area_in_sq_rev  = $revTotals['total_area_in_sq_rev'];

            $report = TestReportRt::create([
                'current_location_id' => $location_data->location_id,
                'test_report_sequence' => $report_seq,
                'test_report_no' => $report_no,
                'test_report_date' => isset($request->test_report_date) ? Date::createFromFormat('d/m/Y', $request->test_report_date)->format('Y-m-d') : null,
                'customer_id' => $request->customer_id,
                'material_inward_details_id' => $request->material_inward_details_id,
                'nabl_type_fix' => $request->nabl_type_fix ?? 'Non NABL',
                'job_type_fix' => $request->job_type_fix ?? 'Non-Welding',
                'process_type' => $request->process_type ?? '',
                'from_type_id_fix' => $request->from_type_id_fix,
                'customer_client' => $request->customer_client,
                'type_of_job_id' => $request->type_of_job_id,
                'job_desc_id' => $request->job_desc_id,
                'part_id' => null,
                'part_no' => $request->part_no,
                'drg_no' => $request->drg_no,
                'material_id' => $request->material_id,
                'heat_no' => $request->heat_no,
                'rt_no' => $request->rt_no,
                'product_code' => $request->product_code,
                'date_of_receipt' => !empty($request->date_of_receipt) ? Date::createFromFormat('d/m/Y', $request->date_of_receipt)->format('Y-m-d') : null,
                'date_of_testing' => !empty($request->date_of_testing) ? Date::createFromFormat('d/m/Y', $request->date_of_testing)->format('Y-m-d') : null,
                'date_of_testing_value' => $request->date_of_testing_value,
                'test_carried_out_at' => $request->test_carried_out_at,
                'amendment_no' => $request->amendment_no,
                'amendment_date' => !empty($request->amendment_date) ? Date::createFromFormat('d/m/Y', $request->amendment_date)->format('Y-m-d') : null,
                'amendment_reason' => $request->amendment_reason,
                'stage_of_test' => $request->stage_of_test,
                'area_of_coverage_id' => $request->area_of_coverage_id,
                'technique_sheet_rt_id' => $request->technique_sheet_rt_id ?? $request->rss_no,
                'rss_reference' => $request->rss_reference,
                'welding_process' => $request->welding_process,
                'joint_type' => $request->joint_type,
                'welder_name' => $request->welder_name,
                'welder_id' => $request->welder_id,
                'position' => $request->position,
                'purpose_of_testing' => $request->purpose_of_testing,
                'ir_camera_id' => $request->camera_ir_192_id ?? null,
                'co_camera_id' => $request->camera_co_60_id ?? null,
                'xray_camera_id' => $request->camera_x_ray_id ?? null,
                'source_used' => $request->source_used,
                'source_strength' => $request->source_strength,
                'source_size' => $request->source_size,
                'xray_kv_ma' => $request->xray_kv_ma,
                'xray_focal_size' => $request->xray_focal_size,
                'lead_screen_thick' => $request->lead_screen_thick,
                'lead_screen_thick_back' => $request->lead_screen_thick_back,
                'iqi' => $request->iqi,
                'film_processing' => $request->film_processing,
                'test_technique' => $request->test_technique,
                'test_arrangement' => $request->test_arrangement,
                'test_class' => $request->test_class,
                'film_brand' => $request->film_brand,
                'film_type' => $request->film_type,
                'procedure_ref_id' => $request->procedure_ref_id,
                'evaluation_as_per_id' => $request->evaluation_as_per_id,
                'acceptance_standard_id' => $request->acceptance_standard_id,
                'customer_procedure_ref' => $request->customer_procedure_ref,
                'rt_report_qty' => $request->rt_report_qty ?? 0,
                'film_size_unit_fix' => $request->film_size_unit_fix ?? 'inch',
                'sfd_unit_fix' => $request->sfd_unit_fix ?? 'mm',
                'print_ug_in_report' => (!empty($request->print_ug_in_report) && in_array(strtolower(trim($request->print_ug_in_report)), ['yes', '1', 'true'])) ? 'Yes' : 'No',
                'no_of_films' => !empty($allSummaries['no_of_films']) ? $allSummaries['no_of_films'] : $request->no_of_films,
                // 'film_size' => !empty($allSummaries['film_size']) ? $allSummaries['film_size'] : $request->film_size,
                'total_area' => !empty($allSummaries['total_area']) ? $allSummaries['total_area'] : $request->total_area,
                'total_no_of_films_rev' => $total_no_of_films_rev,
                'total_area_in_sq_rev'  => $total_area_in_sq_rev,
                'abbreviation' => '',
                'ulr_id' => $ulr_id,
                'ulr_no' => $ulr_no,
                'ulr_sequence' => $ulr_sequence,
                'ulr_year' => $ulr_year,
                'tested_by_authority_person_id' => $request->tested_by_authority_person_id,
                'reviewed_by_authority_person_id' => $request->reviewed_by_authority_person_id,
                'authorized_by_authority_person_id' => $request->authorized_by_authority_person_id,
                'sp_note' => $request->sp_note,
                'note' => $request->note,
                'mms_ir_192_sqin' => $mms_ir_192_sqin,
                'mms_co_60_sqin' => $mms_co_60_sqin,
                'mms_x_ray_sqin' => $mms_x_ray_sqin,
                'mms_ir_192_repair_sqin' => $mms_ir_192_repair_sqin,
                'mms_co_60_repair_sqin' => $mms_co_60_repair_sqin,
                'mms_x_ray_repair_sqin' => $mms_x_ray_repair_sqin,
                'result' => $request->result,
                'observation_sheet_details_id' => $request->observation_sheet_details_id ?? null,
                'revision_number' => $request->revision_number ?? null,
                'revision_sequence' => $request->filled('revision_number') ? intval(str_replace('R', '', $request->revision_number)) : null,
                'revision_test_report_rt_id' => $request->revision_test_report_rt_id ?? null,
                'revision_from_report_rt_id' => $request->revision_from_report_rt_id ?? 0,
                'assign_format_no'      => $assign_format_no,
                'company_id' => Auth::user()->company_id,
                'year_id' => $year_data->id,
                'current_location_id' => $location_data->location_id,
                'created_by' => Auth::id(),
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
            ]);

            if (!empty($details)) {
                foreach ($details as $row) {
                    if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;

                    $resId = $row['detail_film_result_id'] ?? null;
                    $film_result_type_fix = ($resId && isset($filmResults[$resId])) ? $filmResults[$resId] : null;

                    if ($request->process_type == 'Fresh') {
                        $includeInMs = "Yes";
                    } else {
                        $includeInMs = ($film_result_type_fix == 'Repair') ? "Yes" : "No";
                    }

                    $createdDetail = TestReportRtDetails::create([
                        'test_report_rt_id' => $report->test_report_rt_id,
                        'sr_no' => $row['sr_no'],
                        'identification' => $row['identification'],
                        'location' => $row['location'],
                        'source_id_fix' => $row['source_id_fix'],
                        'film_brand_id' => $row['detail_film_brand_id'],
                        'film_type_id' => $row['detail_film_type_id'],
                        'thickness' => $row['thickness'],
                        'sfd' => $row['sfd'],
                        'iqi_designation' => !empty($row['iqi_designation']) ? $row['iqi_designation'] : null,
                        'iqi_sensitivity' => !empty($row['iqi_sensitivity']) ? $row['iqi_sensitivity'] : null,
                        'iqi_designation_id' => null,
                        'iqi_sensitivity_id' => null,
                        'optical_density' => $row['optical_density'] ?? null,
                        'film_id' => $row['detail_film_id'],
                        'no_of_film_fix' => $row['no_of_film_fix'],
                        'exposure_time' => $row['exposure_time'] ?? null,
                        'finding' => !empty($row['finding']) ? $row['finding'] : (!empty($row['finding_name']) ? $row['finding_name'] : null),
                        // 'finding_id' => !empty($row['detail_finding_id']) ? $row['detail_finding_id'] : null,
                        'finding_level_id' => !empty($row['detail_finding_level_id']) ? $row['detail_finding_level_id'] : null,
                        'result_id' => !empty($row['detail_film_result_id']) ? $row['detail_film_result_id'] : null,
                        'film_result_type_fix' => $film_result_type_fix,
                        'ug' => $row['ug'] ?? '',
                        'include_in_measurement_sheet' => $includeInMs,
                        'film_qty' => $row['film_qty'],
                        'sq_in' => $row['sq_in'],
                        'sq_cm' => $row['sq_cm'],
                        'total_sq_in' => $row['total_sq_in'],
                        'total_sq_cm' => $row['total_sq_cm'],
                        'record_type_id' => $row['record_type_id'],
                        'main_rt_detail_id' => !empty($row['main_rt_detail_id']) && $row['main_rt_detail_id'] > 0 ? $row['main_rt_detail_id'] : null,
                    ]);

                    if (empty($createdDetail->main_rt_detail_id)) {
                        $createdDetail->update([
                            'main_rt_detail_id' => $createdDetail->test_report_rt_details_id
                        ]);
                    }
                }
            }


            $checkPending = $this->checkNegativePendingQty($report->customer_id, $location_data->location_id, $report->from_type_id_fix, $report->material_inward_details_id, $request->revision_from_report_rt_id ?? 0, $request->observation_sheet_details_id ?? 0, 'I');
            if ($checkPending) {
                return $checkPending;
            }

            if($request->revision_test_report_rt_id == null)
            {
                    TestReportRt::where('test_report_rt_id',$report->test_report_rt_id)->update([
                        'revision_test_report_rt_id' => $report->test_report_rt_id,
                        'revision_from_report_rt_id' => $request->revision_from_report_rt_id ?? 0,
                        'revision_sequence' => 0,
                    ]);
            }

            DB::commit();

            // Generate Print PDF
            $report_number = !empty($report->test_report_no) ? '_' . str_replace('/', '_', $report->test_report_no) : "";
            $cust = DB::table('customers')->where('id', $report->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Test_Report_RT' . $report_number . $cust_name;

            $nabl_type = str_replace(' ', '_', $report->nabl_type_fix);
            GeneratePdf($report->test_report_rt_id, $pdf_name, 'test_report_rt', 'add', $report->job_type_fix, $nabl_type,"no");
            $encodedId = base64_encode($report->test_report_rt_id);
            $url = hasAccess("test_report_rt", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=test_report_rt&job_type_fix={$report->job_type_fix}&nabl_type_fix={$nabl_type}") : "";

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
                'original_error' => $e->getMessage()
            ]);
        }
    }

    public function edit(Request $request)
    {
        $report_data = DB::select('CALL test_report_rt_master(?)', [$request->id]);
        if (!empty($report_data)) {
            $report_data = $report_data[0];

            $report_number = !empty($report_data->test_report_no) ? '_' . str_replace('/', '_', $report_data->test_report_no) : "";
            $cust = DB::table('customers')->where('id', $report_data->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $report_data->pdf_name = 'Test_Report_RT' . $report_number . $cust_name;

            $report_data->test_report_date = ($report_data->test_report_date != "" && $report_data->test_report_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $report_data->test_report_date)->format('d/m/Y') 
                : "";
            $report_data->dc_date = ($report_data->dc_date != "" && $report_data->dc_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $report_data->dc_date)->format('d/m/Y') 
                : "";
            $report_data->po_date = ($report_data->po_date != "" && $report_data->po_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $report_data->po_date)->format('d/m/Y') 
                : "";
            $report_data->date_of_receipt = ($report_data->date_of_receipt != "" && $report_data->date_of_receipt != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $report_data->date_of_receipt)->format('d/m/Y') 
                : "";
            $report_data->date_of_testing = ($report_data->date_of_testing != "" && $report_data->date_of_testing != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $report_data->date_of_testing)->format('d/m/Y') 
                : "";
            $report_data->amendment_date = ($report_data->amendment_date != "" && $report_data->amendment_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $report_data->amendment_date)->format('d/m/Y') 
                : "";

            if (!empty($report_data->technique_sheet_rt_id)) {
                $report_data->technique_sheet_rt_no = DB::table('technique_sheet_rt')
                    ->where('technique_sheet_rt_id', $report_data->technique_sheet_rt_id)
                    ->value('technique_sheet_rt_no') ?? '';
            }

            // Fetch Observation Sheet details for worksheet_no if available
            $obsData = DB::table('observation_sheet_details as osd')
                ->join('observation_sheet as os', 'os.observation_sheet_id', '=', 'osd.observation_sheet_id')
                ->where(function($q) use ($request, $report_data) {
                    if (!empty($report_data->observation_sheet_details_id)) {
                        $q->where('osd.observation_sheet_details_id', $report_data->observation_sheet_details_id);
                    } 
                    else {
                        $q->where('osd.test_report_rt_id', $request->id);
                        if (!empty($report_data->material_inward_details_id)) {
                            $q->orWhere('osd.material_inward_details_id', $report_data->material_inward_details_id);
                        }
                    }
                })
                ->orderBy('osd.observation_sheet_details_id', 'desc')
                ->select('os.observation_sheet_no', 'os.observation_sheet_date', 'osd.observation_sheet_details_id')
                ->first();

            if ($obsData) {
                $obsDate = (!empty($obsData->observation_sheet_date) && $obsData->observation_sheet_date != "0000-00-00")
                    ? Date::createFromFormat('Y-m-d', $obsData->observation_sheet_date)->format('d/m/Y')
                    : "";
                $report_data->worksheet_no = $obsData->observation_sheet_no . ($obsDate ? ' - ' . $obsDate : '');
                $report_data->observation_sheet_no = $obsData->observation_sheet_no;
                $report_data->observation_sheet_date = $obsDate;
                if (empty($report_data->observation_sheet_details_id)) {
                    $report_data->observation_sheet_details_id = $obsData->observation_sheet_details_id;
                }
            }
            if(isset($report_data->cmp_logo))
            {
                $report_data->cmp_logo = base64_encode($report_data->cmp_logo);
            }
            if (isset($report_data->tested_by_signature)) {
                $report_data->tested_by_signature = base64_encode($report_data->tested_by_signature);
            }
            if (isset($report_data->reviewed_by_signature)) {
                $report_data->reviewed_by_signature = base64_encode($report_data->reviewed_by_signature);
            }
            if (isset($report_data->authorized_by_signature)) {
                $report_data->authorized_by_signature = base64_encode($report_data->authorized_by_signature);
            }
            // Prevent Malformed UTF-8 characters exception by base64 encoding binary blobs
            foreach ($report_data as $key => $value) {
                if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                    $report_data->$key = base64_encode($value);
                }
            }
            // $in_use_ts = TechniqueSheetRt::where('test_report_rt_id', $request->id)->exists();
            // $in_use_ms = DB::table('measurement_sheet_details')->where('test_report_rt_id', $request->id)->exists();
            // $in_use_os = DB::table('observation_sheet_details')->where('test_report_rt_id', $request->id)->exists();
            $usage = DB::select('CALL test_report_rt_used_list(?)', [$request->id]);
            $in_use_ms = false;
            $in_use_os = false;

            if (!empty($usage)) {
                foreach ($usage as $u) {
                    $tbl = strtolower(trim($u->table_name ?? ''));
                    if (strpos($tbl, 'measurement') !== false) {
                        $in_use_ms = true;
                    }
                    if (strpos($tbl, 'observation') !== false) {
                        $in_use_os = true;
                    }
                }
            }

            $baseReportId = !empty($report_data->revision_test_report_rt_id) ? $report_data->revision_test_report_rt_id : $request->id;
            $has_revision = DB::table('test_report_rt')
                ->where(function($q) use ($baseReportId) {
                    $q->where('revision_test_report_rt_id', $baseReportId)
                      ->orWhere('test_report_rt_id', $baseReportId);
                })
                ->whereNotNull('revision_number')
                ->where('revision_number', '!=', '')
                ->exists();

            $hasNextRevision = DB::table('test_report_rt')
                ->where(function($q) use ($baseReportId, $request) {
                    $q->where('revision_test_report_rt_id', $baseReportId)
                      ->orWhere('revision_from_report_rt_id', $request->id);
                })
                ->where('test_report_rt_id', '!=', $request->id)
                ->where('revision_sequence','>', $report_data->revision_sequence)
                ->exists();

            
            $is_revision_report = !empty($report_data->revision_number);
            if ($is_revision_report) {
                $pend_qty = DB::table('pending_material_inward_rt_qty')
                    ->where('material_inward_details_id', $report_data->material_inward_details_id)
                    ->where('revision_from_report_rt_id', $report_data->test_report_rt_id)
                    ->where('customer_id', $report_data->customer_id)
                    ->value('pending_qty') ?? 0;
                $report_data->pend_qty = (float)$pend_qty + (float)($report_data->rt_report_qty ?? 0);
            } else {
                $pend_qty = DB::table('pending_material_inward_rt_qty')
                    ->where('material_inward_details_id', $report_data->material_inward_details_id)
                    ->where('customer_id', $report_data->customer_id)
                    ->where(function($q) {
                        $q->whereNull('revision_from_report_rt_id')->orWhere('revision_from_report_rt_id', 0);
                    })
                    ->value('pending_qty') ?? 0;
                $report_data->pend_qty = (float)$pend_qty + (float)($report_data->rt_report_qty ?? 0);
            }


            // $report_data->in_use = ($in_use_ts || $in_use_ms) ? 1 : 0;
            $report_data->in_use = (!empty($usage)) ? 1 : 0;
            $report_data->is_used_in_ms = $in_use_ms ? 1 : 0;
            $report_data->is_used_in_os = $in_use_os ? 1 : 0;
            $report_data->has_revision = $has_revision ? 1 : 0;
            $report_data->has_next_revision = $hasNextRevision ? 1 : 0;
            $details = DB::select('CALL test_report_rt_details(?)', [$request->id]);
            foreach ($details as $row) {
                $row->mode = 'Update';
                $row->is_revision_row = ($is_revision_report && $row->record_type_id == 3) ? 0 : 1;
            }

            $cameraIds = array_values(array_filter([
                $report_data->ir_camera_id ?? 0,
                $report_data->co_camera_id ?? 0,
                $report_data->xray_camera_id ?? 0
            ]));
            $report_data->extra_cameras = [];
            if (!empty($cameraIds)) {
                $report_data->extra_cameras = RTCamera::select([
                    'rt_camera.rt_camera_id',
                    'rt_camera.name_for_display',
                    'rt_camera.rt_camera_name',
                    'rt_camera.rt_serial_no',
                    'rt_camera.rt_isotope',
                    'rt_camera.rt_x_ray',
                    'rt_camera.rt_focal_spot',
                    'rt_camera_details.rtcd_initial_activity_ci',
                    'rt_camera_details.rtcd_source_size',
                    'rt_camera_details.rtcd_last_of_loading_date',
                ])
                ->leftJoin('rt_camera_details', function ($join) {
                    $join->on('rt_camera_details.rtcd_rt_camera_id', '=', 'rt_camera.rt_camera_id')
                        ->whereRaw('rt_camera_details.rtcd_id = (
                            SELECT MAX(rtcd_id) 
                            FROM rt_camera_details 
                            WHERE rtcd_rt_camera_id = rt_camera.rt_camera_id
                        )');
                })
                ->whereIn('rt_camera.rt_camera_id', $cameraIds)
                ->get();
            }

            return response()->json([
                'report_data' => $report_data,
                'report_details_data' => $details,
                'response_code' => '1',
            ]);
        }

        return response()->json([
            'response_code' => '0',
            'response_message' => 'Record Does Not Exist',
        ]);
    }

    public function update(Request $request)
    {
        $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();

        $report = TestReportRt::where('test_report_rt_id', $request->id)->first();
        if (!$report) {
            return response()->json(['response_code' => '0', 'response_message' => 'Record Does Not Exist']);
        }


         $isRevision = $request->filled('revision_number') || !empty($report->revision_number);   
        // $isRevision = !empty($report->revision_test_report_rt_id)
        //    && $report->revision_test_report_rt_id != $report->test_report_rt_id;

        $rules = [
            'test_report_date' => 'required',
            'customer_id' => 'required',
            'type_of_job_id' => 'required',
            'job_desc_id' => 'required',
            // 'part_no' => 'required',
            'material_id' => 'required',
            'area_of_coverage_id' => 'required',
        ];

        if ($isRevision) {
            $rules['test_report_sequence'] = 'required|max:155';
        } else {
                // $rules['test_report_sequence'] = [
                //     'required',
                //     'max:155',
                //     Rule::unique('test_report_rt')
                //         ->where(function ($query) use ($year_data, $location_data) {
                //             $query->where('year_id', $year_data->id)
                //                   ->where('current_location_id', $location_data->location_id);
                //         })
                //         ->ignore($request->id, 'test_report_rt_id')
                // ];

                $duplicate = TestReportRt::where(
                        'test_report_sequence',
                        $request->test_report_sequence
                    )
                    ->where('year_id', $year_data->id)
                    ->where(
                        'current_location_id',
                        $location_data->location_id
                    )
                    ->where(
                        'test_report_rt_id',
                        '!=',
                        $request->id
                    )
                    ->whereColumn(
                        'revision_test_report_rt_id',
                        'test_report_rt_id'
                    )
                    ->exists();
                    if ($duplicate) {
                        return response()->json([
                            'response_code' => '0',
                            'response_message' => 'Report No. Already Exists'
                        ]);
                    }

        }
        $request->validate($rules);

        DB::beginTransaction();
        try {

            $oldQty = (float)(TestReportRt::where('test_report_rt_id', $request->id)->value('rt_report_qty') ?? 0);
            $diff = (float)($request->rt_report_qty ?? 0) - (float)$oldQty;
            $from_qty = $diff > 0 ? $diff : 0;
            $next_qty = $diff < 0 ? abs($diff) : 0;
            $is_used_in_os = DB::table('observation_sheet_details')
            ->where('from_type_id_fix', 2)
            ->where('test_report_rt_id', $request->id)
            ->exists(); 

            if ($is_used_in_os && (float)$request->rt_report_qty != (float)$oldQty) {
                DB::rollBack();
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => "You Can't Update, Test Report RT Qty. Is Used In Observation Sheet.",
                ]);
            }

            $qtyCheck = $this->qtyValidation(
                $from_qty,
                $next_qty,
                'U',
                $request->customer_id,
                $request->material_inward_details_id,
                $request->observation_sheet_details_id,
                $report->revision_from_report_rt_id,
                $request->from_type_id_fix,
                $request->id
            );
            if ($qtyCheck) {
                return $qtyCheck;
            }

            $receiptDate = !empty($request->date_of_receipt) ? Carbon::createFromFormat('d/m/Y', $request->date_of_receipt)->format('Y-m-d') : null;
            $testDate = !empty($request->date_of_testing) ? Carbon::createFromFormat('d/m/Y', $request->date_of_testing)->format('Y-m-d') : null;
            $reportDate = !empty($request->test_report_date) ? Carbon::createFromFormat('d/m/Y', $request->test_report_date)->format('Y-m-d') : null;

            if ($testDate && $receiptDate && $testDate < $receiptDate) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Date Of Receipt Must Be Less Than Date Of Testing.',
                ]);
            }
            if ($reportDate && $receiptDate && $reportDate < $receiptDate) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Date Of Receipt Must Be Less Than Report Date.',
                ]);
            }
            if ($reportDate && $testDate && $reportDate < $testDate) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Date Of Testing Must Be Less Than Report Date.',
                ]);
            }

            $existing_image = $report->shooting_sketch_image;
            $existing_blob = $report->shooting_sketch_image_blob;

            $file = new File();
            $shootingSketchPath = $existing_image;
            $blobImage = $existing_blob;

            if ($request->filled('shooting_sketch_image_doc') && $request->shooting_sketch_image_doc != '') {
                if ($existing_image && $existing_image != $request->shooting_sketch_image_doc) {
                    $file->delete_file($existing_image);
                }

                $isFound = $file->getFileFromTemp($request->shooting_sketch_image_doc, 'test_report_rt');
                if ($isFound !== false) {
                    $shootingSketchPath = $isFound;
                    $filePath = storage_path('app/public/' . $shootingSketchPath);
                    if (file_exists($filePath)) {
                        $blobImage = file_get_contents($filePath);
                    }
                } else {
                    $shootingSketchPath = $request->shooting_sketch_image_doc;
                    $filePath = storage_path('app/public/' . $shootingSketchPath);
                    if (file_exists($filePath)) {
                        $blobImage = file_get_contents($filePath);
                    }
                }
            } elseif ($request->shooting_sketch_image_doc == '' || $request->shooting_sketch_image_doc == null) {
                if ($existing_image) {
                    $file->delete_file($existing_image);
                }
                $shootingSketchPath = null;
                $blobImage = null;
            }

            $ulr_id = null;
            $ulr_no = null;
            $ulr_sequence = null;
            $ulr_year = null;

            if ($request->nabl_type_fix === 'NABL') {
                $ulr_id = $request->ulr_id;
                
                $dateVal = Carbon::createFromFormat('d/m/Y', $request->test_report_date)->format('Y-m-d');
                $DateObj = new \DateTime($dateVal);
                $ulr_year = $DateObj->format("Y");

                $baseReportId = $request->revision_test_report_rt_id ?? ($report->revision_test_report_rt_id ?? $request->id);
                if (isDuplicateUlrSequence($request->ulr_sequence, $ulr_id, $ulr_year, $location_data->location_id, TestReportRt::class, $request->id, $isRevision, $baseReportId)) {
                    return response()->json([
                        'response_code' => 0,
                        'response_message' => 'Duplicate ULR No. Found.'
                    ]);
                }
                $ulr_no = $request->ulr_no;
                $ulr_sequence = $request->ulr_sequence;
            }
            $page_id = getMenuIdBassedOnDisplayName('test_report_rt');
            $assign_format_no = getAssignFormateNoForTransaction($location_data->location_id, $page_id->id,$request->test_report_date);

            $details = json_decode($request->report_details_data, true);
            $isRevision = $request->filled('revision_number') || (!empty($report->revision_test_report_rt_id) && $report->revision_test_report_rt_id != $report->test_report_rt_id);

            $mms_ir_192_sqin = 0.0;
            $mms_co_60_sqin = 0.0;
            $mms_x_ray_sqin = 0.0;
            $mms_ir_192_repair_sqin = 0.0;
            $mms_co_60_repair_sqin = 0.0;
            $mms_x_ray_repair_sqin = 0.0;

            if (!empty($details)) {
                $filmResults = DB::table('film_result')->pluck('film_result_type_fix', 'film_result_id')->all();
                foreach ($details as &$row) {
                    if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;

                    $resultId = $row['detail_film_result_id'] ?? ($row['result_id'] ?? null);
                    $filmResultType = '';
                    if ($resultId && isset($filmResults[$resultId])) {
                        $filmResultType = strtolower(trim($filmResults[$resultId]));
                    } else {
                        $filmResultType = strtolower(trim($row['film_result_name'] ?? ($row['film_result'] ?? '')));
                    }

                    // $isPassResult = in_array($filmResultType, ['accepted', 'not accepted']);
                    $isPassResult = in_array($filmResultType, ['accepted']);
                    $isRepairResult = in_array($filmResultType, ['repair', 'reshoot', 'retake']);

                  

                    // $includeInMs = strtolower(trim($row['include_in_measurement_sheet'] ?? 'Yes'));
                    // if ($includeInMs !== 'yes' && $includeInMs !== '1' && $includeInMs !== 'true') {
                    //     continue;
                    // }

                    $source = $row['source_id_fix'] ?? '';
                    $recType = intval($row['record_type_id'] ?? 1);
                    $totalSqIn = floatval($row['total_sq_in'] ?? 0);

                    if ($recType == 1) {
                        // Fresh: પહેલી વખતે તમામ જોઈન્ટ્સનું Fresh બિલિંગ
                        if ($source === 'Ir-192') {
                            $mms_ir_192_sqin += $totalSqIn;
                        } elseif ($source === 'Co-60') {
                            $mms_co_60_sqin += $totalSqIn;
                        } elseif ($source === 'X-Ray') {
                            $mms_x_ray_sqin += $totalSqIn;
                        }
                    } elseif ($recType == 2) {
                        // Repair: રિવિઝનમાં માત્ર જે રિપેર રહેલા છે તેનું જ Repair બિલિંગ
                        if (strtolower(trim($filmResultType)) === 'repair') {
                            if ($source === 'Ir-192') {
                                $mms_ir_192_repair_sqin += $totalSqIn;
                            } elseif ($source === 'Co-60') {
                                $mms_co_60_repair_sqin += $totalSqIn;
                            } elseif ($source === 'X-Ray') {
                                $mms_x_ray_repair_sqin += $totalSqIn;
                            }
                        }
                    }
                    // recType == 3 (Ok): પહેલાથી બિલિંગ લીધેલ હોવાથી કંઈ નહિ (0.00)
                }
                unset($row);
            }

            $is_used_in_ms = DB::table('measurement_sheet_details')
                ->where('test_report_rt_id', $request->id)
                ->exists();

            if ($is_used_in_ms) {
                $new_total_sqin = $mms_ir_192_sqin + $mms_co_60_sqin + $mms_x_ray_sqin + $mms_ir_192_repair_sqin + $mms_co_60_repair_sqin + $mms_x_ray_repair_sqin;

                $old_report_sqin = (float) DB::table('test_report_rt')
                    ->where('test_report_rt_id', $request->id)
                    ->selectRaw('IFNULL(mms_ir_192_sqin,0) + IFNULL(mms_co_60_sqin,0) + IFNULL(mms_x_ray_sqin,0) + IFNULL(mms_ir_192_repair_sqin,0) + IFNULL(mms_co_60_repair_sqin,0) + IFNULL(mms_x_ray_repair_sqin,0) as total_sqin')
                    ->value('total_sqin');

                if ((float)$new_total_sqin != (float)$old_report_sqin) {
                    DB::rollBack();
                    return response()->json([
                        'response_code'    => '0',
                        'response_message' => "You Can't Update, Test Report RT (Sq.In) Is Used In Measurement Sheet.",
                    ]);
                }
            }

            // Calculate total_no_of_films_rev & total_area_in_sq_rev for Repair joints (record_type_id == 2)
            $revTotals = $this->calculateRevisionTotals($details, $request->film_size_unit_fix ?? 'inch');
            $allSummaries = $this->calculateAllHeaderSummaries($details, $request->film_size_unit_fix ?? 'inch');
            $total_no_of_films_rev = $revTotals['total_no_of_films_rev'];
            $total_area_in_sq_rev  = $revTotals['total_area_in_sq_rev'];

            $report->update([
                'test_report_sequence' => $request->test_report_sequence,
                'test_report_no' => $request->test_report_no,
                'test_report_date' => isset($request->test_report_date) ? Date::createFromFormat('d/m/Y', $request->test_report_date)->format('Y-m-d') : null,
                'customer_id' => $request->customer_id,
                'material_inward_details_id' => $request->material_inward_details_id,
                'nabl_type_fix' => $request->nabl_type_fix ?? 'Non NABL',
                'job_type_fix' => $request->job_type_fix ?? 'Non-Welding',
                'process_type' => $request->process_type ?? '',
                'from_type_id_fix' => $request->from_type_id_fix ?? ($report->from_type_id_fix ?? 1),
                'customer_client' => $request->customer_client,
                'type_of_job_id' => $request->type_of_job_id,
                'job_desc_id' => $request->job_desc_id,
                'part_id' => null,
                'part_no' => $request->part_no,
                'drg_no' => $request->drg_no,
                'material_id' => $request->material_id,
                'heat_no' => $request->heat_no,
                'rt_no' => $request->rt_no,
                'product_code' => $request->product_code,
                'date_of_receipt' => !empty($request->date_of_receipt) ? Date::createFromFormat('d/m/Y', $request->date_of_receipt)->format('Y-m-d') : null,
                'date_of_testing' => !empty($request->date_of_testing) ? Date::createFromFormat('d/m/Y', $request->date_of_testing)->format('Y-m-d') : null,
                'date_of_testing_value' => $request->date_of_testing_value,
                'test_carried_out_at' => $request->test_carried_out_at,
                'amendment_no' => $request->amendment_no,
                'amendment_date' => !empty($request->amendment_date) ? Date::createFromFormat('d/m/Y', $request->amendment_date)->format('Y-m-d') : null,
                'amendment_reason' => $request->amendment_reason,
                'stage_of_test' => $request->stage_of_test,
                'area_of_coverage_id' => $request->area_of_coverage_id,
                'technique_sheet_rt_id' => $request->technique_sheet_rt_id ?? $request->rss_no,
                'rss_reference' => $request->rss_reference,
                'welding_process' => $request->welding_process,
                'joint_type' => $request->joint_type,
                'welder_name' => $request->welder_name,
                'welder_id' => $request->welder_id,
                'position' => $request->position,
                'purpose_of_testing' => $request->purpose_of_testing,
                'ir_camera_id' => $request->camera_ir_192_id ?? null,
                'co_camera_id' => $request->camera_co_60_id ?? null,
                'xray_camera_id' => $request->camera_x_ray_id ?? null,
                'source_used' => $request->source_used,
                'source_strength' => $request->source_strength,
                'source_size' => $request->source_size,
                'xray_kv_ma' => $request->xray_kv_ma,
                'xray_focal_size' => $request->xray_focal_size,
                'lead_screen_thick' => $request->lead_screen_thick,
                'lead_screen_thick_back' => $request->lead_screen_thick_back,
                'iqi' => $request->iqi,
                'film_processing' => $request->film_processing,
                'test_technique' => $request->test_technique,
                'test_arrangement' => $request->test_arrangement,
                'test_class' => $request->test_class,
                'film_brand' => $request->film_brand,
                'film_type' => $request->film_type,
                'procedure_ref_id' => $request->procedure_ref_id,
                'evaluation_as_per_id' => $request->evaluation_as_per_id,
                'acceptance_standard_id' => $request->acceptance_standard_id,
                'customer_procedure_ref' => $request->customer_procedure_ref,
                'rt_report_qty' => $request->rt_report_qty ?? 0,
                'film_size_unit_fix' => $request->film_size_unit_fix ?? 'inch',
                'sfd_unit_fix' => $request->sfd_unit_fix ?? 'mm',
                'print_ug_in_report' => (!empty($request->print_ug_in_report) && in_array(strtolower(trim($request->print_ug_in_report)), ['yes', '1', 'true'])) ? 'Yes' : 'No',
                'no_of_films' => !empty($allSummaries['no_of_films']) ? $allSummaries['no_of_films'] : $request->no_of_films,
                // 'film_size' => !empty($allSummaries['film_size']) ? $allSummaries['film_size'] : $request->film_size,
                'total_area' => !empty($allSummaries['total_area']) ? $allSummaries['total_area'] : $request->total_area,
                'total_no_of_films_rev' => $total_no_of_films_rev,
                'total_area_in_sq_rev'  => $total_area_in_sq_rev,
                'abbreviation' => '',
                'ulr_id' => $ulr_id,
                'ulr_no' => $ulr_no,
                'ulr_sequence' => $ulr_sequence,
                'ulr_year' => $ulr_year,
                'tested_by_authority_person_id' => $request->tested_by_authority_person_id,
                'reviewed_by_authority_person_id' => $request->reviewed_by_authority_person_id,
                'authorized_by_authority_person_id' => $request->authorized_by_authority_person_id,
                'sp_note' => $request->sp_note,
                'note' => $request->note,
                'mms_ir_192_sqin' => $mms_ir_192_sqin,
                'mms_co_60_sqin' => $mms_co_60_sqin,
                'mms_x_ray_sqin' => $mms_x_ray_sqin,
                'mms_ir_192_repair_sqin' => $mms_ir_192_repair_sqin,
                'mms_co_60_repair_sqin' => $mms_co_60_repair_sqin,
                'mms_x_ray_repair_sqin' => $mms_x_ray_repair_sqin,
                'result' => $request->result,
                'observation_sheet_details_id' => $request->observation_sheet_details_id ?? null,
                'revision_from_report_rt_id' => $report->revision_from_report_rt_id ?? 0,
                // 'assign_format_no'      => $assign_format_no,   not update assign formate discussion ramde sir
                'last_by' => Auth::id(),
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
            ]);

            if (!empty($details)) {
                
                foreach ($details as $row) {
                    if (empty($row)) continue;

                    $mode = $row['mode'] ?? '';

                    $resId = $row['detail_film_result_id'] ?? null;
                    $film_result_type_fix = ($resId && isset($filmResults[$resId])) ? $filmResults[$resId] : null;

                    if ($request->process_type == 'Fresh') {
                        $includeInMs = "Yes";
                    } else {
                        $includeInMs = ($film_result_type_fix == 'Repair') ? "Yes" : "No";
                    }

                    if ($mode === 'Insert') {
                        
                        $createdDetail = TestReportRtDetails::create([
                            'test_report_rt_id' => $report->test_report_rt_id,
                            'sr_no' => $row['sr_no'],
                            'identification' => $row['identification'],
                            'location' => $row['location'],
                            'source_id_fix' => $row['source_id_fix'],
                            'film_brand_id' => $row['detail_film_brand_id'],
                            'film_type_id' => $row['detail_film_type_id'],
                            'thickness' => $row['thickness'],
                            'sfd' => $row['sfd'],
                            'iqi_designation' => !empty($row['iqi_designation']) ? $row['iqi_designation'] : null,
                            'iqi_sensitivity' => !empty($row['iqi_sensitivity']) ? $row['iqi_sensitivity'] : null,
                            'iqi_designation_id' => null,
                            'iqi_sensitivity_id' => null,
                            'optical_density' => $row['optical_density'] ?? null,
                            'film_id' => $row['detail_film_id'],
                            'no_of_film_fix' => $row['no_of_film_fix'],
                            'exposure_time' => $row['exposure_time'] ?? null,
                            'finding' => !empty($row['finding']) ? $row['finding'] : (!empty($row['finding_name']) ? $row['finding_name'] : null),
                            // 'finding_id' => !empty($row['detail_finding_id']) ? $row['detail_finding_id'] : null,
                            'finding_level_id' => !empty($row['detail_finding_level_id']) ? $row['detail_finding_level_id'] : null,
                            'result_id' => !empty($row['detail_film_result_id']) ? $row['detail_film_result_id'] : null,
                            'film_result_type_fix' => $film_result_type_fix,
                            'ug' => $row['ug'] ?? '',
                            'include_in_measurement_sheet' => $includeInMs,
                            'film_qty' => $row['film_qty'],
                            'sq_in' => $row['sq_in'],
                            'sq_cm' => $row['sq_cm'],
                            'total_sq_in' => $row['total_sq_in'],
                            'total_sq_cm' => $row['total_sq_cm'],
                            'record_type_id' => !empty($row['record_type_id']) ? $row['record_type_id'] : 1,
                            'main_rt_detail_id' => !empty($row['main_rt_detail_id']) && $row['main_rt_detail_id'] > 0 ? $row['main_rt_detail_id'] : null,
                        ]);

                        if (empty($createdDetail->main_rt_detail_id)) {
                            $createdDetail->update([
                                'main_rt_detail_id' => $createdDetail->test_report_rt_details_id
                            ]);
                        }
                    } elseif ($mode === 'Update') {
                        if (!empty($row['test_report_rt_details_id'])) {
                            $findingVal = !empty($row['finding']) ? $row['finding'] : (!empty($row['finding_name']) ? $row['finding_name'] : null);
                            $mainId = !empty($row['main_rt_detail_id']) ? $row['main_rt_detail_id'] : $row['test_report_rt_details_id'];

                            TestReportRtDetails::where('test_report_rt_details_id', $row['test_report_rt_details_id'])->update([
                                'sr_no' => $row['sr_no'],
                                'identification' => $row['identification'],
                                'location' => $row['location'],
                                'source_id_fix' => $row['source_id_fix'],
                                'film_brand_id' => $row['detail_film_brand_id'],
                                'film_type_id' => $row['detail_film_type_id'],
                                'thickness' => $row['thickness'],
                                'sfd' => $row['sfd'],
                                'iqi_designation' => !empty($row['iqi_designation']) ? $row['iqi_designation'] : null,
                                'iqi_sensitivity' => !empty($row['iqi_sensitivity']) ? $row['iqi_sensitivity'] : null,
                                'iqi_designation_id' => null,
                                'iqi_sensitivity_id' => null,
                                'optical_density' => $row['optical_density'] ?? null,
                                'film_id' => $row['detail_film_id'],
                                'no_of_film_fix' => $row['no_of_film_fix'],
                                'exposure_time' => $row['exposure_time'] ?? null,
                                'finding' => $findingVal,
                                // 'finding_id' => !empty($row['detail_finding_id']) ? $row['detail_finding_id'] : null,
                                'finding_level_id' => !empty($row['detail_finding_level_id']) ? $row['detail_finding_level_id'] : null,
                                'result_id' => !empty($row['detail_film_result_id']) ? $row['detail_film_result_id'] : null,
                                'film_result_type_fix' => $film_result_type_fix,
                                'ug' => $row['ug'] ?? '',
                                'include_in_measurement_sheet' => $includeInMs,
                                'film_qty' => $row['film_qty'],
                                'sq_in' => $row['sq_in'],
                                'sq_cm' => $row['sq_cm'],
                                'total_sq_in' => $row['total_sq_in'],
                                'total_sq_cm' => $row['total_sq_cm'],
                                'record_type_id' => !empty($row['record_type_id']) ? $row['record_type_id'] : 1,
                                'main_rt_detail_id' => $mainId,
                            ]);

                            if (!empty($mainId)) {
                                TestReportRtDetails::where('main_rt_detail_id', $mainId)->update([
                                    'finding' => $findingVal
                                ]);
                            }
                        }
                    } elseif ($mode === 'Delete') {
                        if (!$isRevision && !empty($row['test_report_rt_details_id'])) {
                            TestReportRtDetails::where('test_report_rt_details_id', $row['test_report_rt_details_id'])->delete();
                        }
                    }
                }
            }


            $checkPending = $this->checkNegativePendingQty($report->customer_id ?? $request->customer_id, $location_data->location_id, $report->from_type_id_fix, $report->material_inward_details_id ?? $request->material_inward_details_id, $report->revision_from_report_rt_id ?? 0, $request->observation_sheet_details_id ?? 0, 'U');
            if ($checkPending) {
                return $checkPending;
            }

            DB::commit();

            // Regenerate Print PDF
            $report_number = !empty($report->test_report_no) ? '_' . str_replace('/', '_', $report->test_report_no) : "";
            $cust = DB::table('customers')->where('id', $report->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Test_Report_RT' . $report_number . $cust_name;

            $nabl_type = str_replace(' ', '_', $report->nabl_type_fix);
            GeneratePdf($report->test_report_rt_id, $pdf_name, 'test_report_rt', 'update', $report->job_type_fix, $nabl_type,"no");
            $encodedId = base64_encode($report->test_report_rt_id);
            $url = hasAccess("test_report_rt", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=test_report_rt&job_type_fix={$report->job_type_fix}&nabl_type_fix={$nabl_type}") : "";

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
                'original_error' => $e->getMessage()
            ]);
        }
    }

    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            // $ts_rt_id = TechniqueSheetRt::select('test_report_rt_id')->where('technique_sheet_rt_id', $request->id)->pluck('test_report_rt_id');
            // if ($ts_rt_id) {
                // dd($request->id);
            // Old delete checks commented out for backup as requested
            /*
            $usage = DB::select('CALL test_report_rt_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                $message = "You Can't Delete, Test Report RT Is Used In " . $usage[0]->table_name . ".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }
            $is_used = TechniqueSheetRt::where('test_report_rt_id', $request->id)->exists();
            if ($is_used) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Test Report(RT) Is Used In Technique Sheet(RT)",
                ]);
            }
            $is_used_in_ms = DB::table('measurement_sheet_details')->where('test_report_rt_id', $request->id)->exists();
            if ($is_used_in_ms) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Test Report(RT) Is Used In Measurement Sheet",
                ]);
            }
            */

            $report = TestReportRt::where('test_report_rt_id', $request->id)->first();
            if (!$report) {
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => 'Record Does Not Exist',
                ]);
            }

            // ૧. Revision Check: Only allow deleting the latest revision record first
            $baseReportId = !empty($report->revision_test_report_rt_id) ? $report->revision_test_report_rt_id : $report->test_report_rt_id;
            $latestRevision = TestReportRt::where(function($q) use ($baseReportId) {
                    $q->where('test_report_rt_id', $baseReportId)
                      ->orWhere('revision_test_report_rt_id', $baseReportId);
                })
                ->orderBy(DB::raw('COALESCE(revision_sequence, 0)'), 'desc')
                ->orderBy('test_report_rt_id', 'desc')
                ->first();

            if ($latestRevision && $latestRevision->test_report_rt_id != $report->test_report_rt_id) {
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => "You can Delete only Last Revision Report",
                ]);
            }

            // ૨. Downstream Used List Stored Procedure Check
            $usage = DB::select('CALL test_report_rt_used_list(?)', [$request->id]);
            if (!empty($usage)) {
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => "You Can't Delete, Test Report (RT) Is Used In " . $usage[0]->table_name . ".",
                ]);
            }

            // ૩. Qty Validation (View based)
            $oldQty = (float)($report->rt_report_qty ?? 0);
            $qtyCheck = $this->qtyValidation(
                0,
                $oldQty,
                'D',
                $report->customer_id,
                $report->material_inward_details_id,
                $report->observation_sheet_details_id,
                $report->revision_from_report_rt_id,
                $report->from_type_id_fix,
                $request->id
            );
            if ($qtyCheck) {
                return $qtyCheck;
            }

            if ($report->shooting_sketch_image) {
                $file = new File();
                $file->delete_file($report->shooting_sketch_image);
            }
            TestReportRtDetails::where('test_report_rt_id', $request->id)->delete();
            $report->delete();

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

    public function getLatestTestReportRtNumber(Request $request)
    {
        $modal = TestReportRt::class;
        $sequence = 'test_report_sequence';
        $prefix = 'RT';

        $num_format = getLatestSequence($modal, $sequence, $prefix);

        return response()->json([
            'response_code' => 1,
            'latest_no' => $num_format['format'],
            'number' => $num_format['isFound'],
        ]);
    }

    public function getPendingCustomerData(Request $request)
    {
        $location = getCurrentLocation()->location_id;
        $year = getCompanyYearIdsToTill();

        $reportId = $request->input('report_id');
        $currentDetailsId = null;
        $oldQty = 0;
        if ($reportId) {
            $report = DB::table('test_report_rt')->where('test_report_rt_id', $reportId)->first();
            if ($report) {
                $currentDetailsId = $report->material_inward_details_id;
                $oldQty = $report->rt_report_qty;
            }
        }

        $query = DB::table('pending_material_inward_rt_qty as pend')
            ->join('material_inward_details as mid', 'mid.material_inward_details_id', '=', 'pend.material_inward_details_id')
            ->join('material_inward as mi', 'mi.material_inward_id', '=', 'mid.material_inward_id')
            ->select([
                'pend.from_type_id_fix',
                'pend.material_inward_details_id',
                'pend.observation_sheet_details_id',
                'pend.pending_qty',
                'pend.current_location_id',
                'pend.revision_from_report_rt_id',
                'mid.type_of_job_id',
                'mid.job_desc_id',
                'mid.part_id',
                'mid.material_id',
                'mid.area_of_coverage_id',
                'mid.procedure_ref_id',
                'mid.evaluation_as_per_id',
                'mid.acceptance_standard_id',
                DB::raw("IF(IFNULL(pend.revision_from_report_rt_id, 0) > 0, 'Repair', 'Fresh') as process_type"),
                'mid.quantity as inward_qty',
                'pend.pending_qty as reported_qty',
                'mi.customer_id',
                'mi.year_id',
                'mi.dc_no',
                'mi.dc_date',
                'mi.po_no',
                'mi.po_date',
                'mi.nabl_type_fix',
                'mi.job_type_fix',
                'mi.material_inward_no',
                'mi.material_inward_date',
                'mi.test_at_fix',
                'toj.type_of_job',
                'jd.job_description',
                'mid.part_no',
                'mid.drg_no',
                'm.material',
                'mid.heat_no',
                'mid.rt_no',
                'mid.product_code',
                'ac.area_of_coverage',
                'pr.procedure_reference',
                'ev.evaluation_as_per',
                'as_std.acceptance_standard',
                'os.observation_sheet_no',
                'os.observation_sheet_date',
                'prev_tr.test_report_no as report_no',
                'prev_tr.revision_number as revision_no',
                DB::raw("IFNULL(prev_tr.rt_no, mid.rt_no) as repair_rt_no"),
            ])
            ->leftJoin('test_report_rt as prev_tr', 'prev_tr.test_report_rt_id', '=', 'pend.revision_from_report_rt_id')
            ->leftJoin('type_of_job as toj', 'toj.id', '=', 'mid.type_of_job_id')
            ->leftJoin('job_descriptions as jd', 'jd.id', '=', 'mid.job_desc_id')
            ->leftJoin('materials as m', 'm.id', '=', 'mid.material_id')
            ->leftJoin('area_of_coverage as ac', 'ac.area_of_coverage_id', '=', 'mid.area_of_coverage_id')
            ->leftJoin('procedure_reference as pr', 'pr.procedure_reference_id', '=', 'mid.procedure_ref_id')
            ->leftJoin('evaluation_as_per as ev', 'ev.evaluation_as_per_id', '=', 'mid.evaluation_as_per_id')
            ->leftJoin('acceptance_standards as as_std', 'as_std.id', '=', 'mid.acceptance_standard_id')
            ->leftJoin('observation_sheet_details as osd', 'osd.observation_sheet_details_id', '=', 'pend.observation_sheet_details_id')
            ->leftJoin('observation_sheet as os', 'os.observation_sheet_id', '=', 'osd.observation_sheet_id')
            ->where('pend.customer_id', $request->customer_id)
            ->where('pend.current_location_id', $location)
            ->whereIn('mi.year_id', $year);

        if ($request->process_type === 'Repair') {
            $query->where('pend.revision_from_report_rt_id', '>',0);
        } 
        else {
            $query->where(function($q) {
                $q->whereNull('pend.revision_from_report_rt_id')
                  ->orWhere('pend.revision_from_report_rt_id', 0);
            });
        }

        $pending_data = $query->where(function($query) use ($currentDetailsId) {
                $query->where('pend.pending_qty', '>', 0);
                if ($currentDetailsId) {
                    $query->orWhere('pend.material_inward_details_id', $currentDetailsId);
                }
            })
            ->get();

        foreach ($pending_data as $row) {
            if (!empty($row->observation_sheet_date) && $row->observation_sheet_date != '0000-00-00') {
                $row->observation_sheet_date = Carbon::parse($row->observation_sheet_date)->format('d/m/Y');
            } else {
                $row->observation_sheet_date = '';
            }
        }

        return response()->json([
            'response_code' => 1,
            'pending_data' => $pending_data
        ]);
    }

    public function getPendingCustomersForRt(Request $request)
    {
        $location = getCurrentLocation()->location_id;
        $year = getCompanyYearIdsToTill();

        $reportId = $request->input('report_id');
        $currentCustomerId = null;
        if ($reportId) {
            $currentCustomerId = DB::table('test_report_rt')->where('test_report_rt_id', $reportId)->value('customer_id');
        }

        $currentCustomer = null;
        if ($currentCustomerId) {
            $currentCustomer = DB::table('customers')
                ->where('id', $currentCustomerId)
                ->select('id', 'customer')
                ->first();
        }

        $customers = DB::table('customers')
            ->join('pending_material_inward_rt_qty as pend', 'pend.customer_id', '=', 'customers.id')
            ->join('material_inward_details as mid', 'mid.material_inward_details_id', '=', 'pend.material_inward_details_id')
            ->join('material_inward as mi', 'mi.material_inward_id', '=', 'mid.material_inward_id')
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
            'response_code' => 1,
            'customers' => $customers
        ]);
    }

    public function testReportRtLNRData()
    {
        $location_data = getCurrentLocation();
        $year_data = getCurrentYearData();

        $lnr_data = TestReportRt::select([
            'test_report_rt_id',
            'test_carried_out_at',
            'lead_screen_thick',
            'lead_screen_thick_back',
            'note'
        ])
        ->where('current_location_id', $location_data->location_id)
        ->orderBy('test_report_rt_id', 'desc')
        ->first();

        $note = 'The above Results are related to the items tested only. Any Manual corrections in this report invalidates the report. This report shall not be reproduced except in full, without written approval of Ultratech ENGINEERS PRIVATE LIMITED.
        Disclaimer : *Marked informations as given by customer that can affects the validity of the test results.';
        $detail_lnr = null;
        if ($lnr_data) {
            $note=  $lnr_data ? $lnr_data->note : '';
            $detail_lnr = TestReportRtDetails::select([
                'optical_density'
            ])
            ->where('test_report_rt_id', $lnr_data->test_report_rt_id)
            ->orderBy('test_report_rt_details_id', 'desc')
            ->first();
        }

        return response()->json([
            'response_code' => 1,
            'lnr_data'      => [
                'test_carried_out_at' => $lnr_data ? $lnr_data->test_carried_out_at : '',
                'lead_screen_thick' => $lnr_data ? $lnr_data->lead_screen_thick : '',
                'lead_screen_thick_back'  => $lnr_data ? $lnr_data->lead_screen_thick_back : '',
                'optical_density'     => $detail_lnr ? $detail_lnr->optical_density : '',
                'note'                => $note
            ]
        ]);
    }

    public function getLatestULRNo(Request $request)
    {
        $procedure_data = \App\Models\NABLConfiguration::where('nabl_id', '=', $request->ulr_id)->first();
        if (!$procedure_data) {
            return response()->json([
                'response_code' => 0,
                'response_message' => 'ULR Configuration Not Found.'
            ]);
        }

        $dateStr = $request->date ?? $request->test_report_date;
        $date = Carbon::createFromFormat('d/m/Y', $dateStr)->format('Y-m-d');
        $Date = new \DateTime($date);
        $year = $Date->format("Y");

        $location_data = getCurrentLocation();
        if (!($location_data instanceof \App\Models\Location)) {
            $location_data = \App\Models\Location::orderBy('location_id', 'desc')->first();
        }
        $location_id = $location_data ? $location_data->location_id : null;

        $customSeq = $request->has('ulr_sequence') && $request->ulr_sequence !== null && $request->ulr_sequence !== '' ? $request->ulr_sequence : null;
        if ($customSeq !== null) {
            $isDuplicate = isDuplicateUlrSequence(
                $customSeq,
                $request->ulr_id,
                $year,
                $location_id,
                TestReportRt::class,
                $request->id ?? ''
            );
            if ($isDuplicate) {
                return response()->json([
                    'response_code' => 0,
                    'response_message' => 'Duplicate ULR No. Found.'
                ]);
            }
        }

        $isFound = getLatestUlrSequence(
            $request->ulr_id,
            $year,
            $location_id,
            $customSeq
        );

        $yearShort = $Date->format("y");
        $number = formatUlrNo($procedure_data, $isFound, $yearShort);

        return response()->json([
            'response_code' => 1,
            'ulr_sequence'  => $isFound,
            'number'        => $number,            
        ]);
    }

    public function checkUlrNo(Request $request)
    {
        $id = $request->id;
        $ulr_sequence = $request->ulr_sequence;
        $ulr_id = $request->ulr_id;
        $date = Carbon::createFromFormat('d/m/Y', $request->date)->format('Y-m-d');
        $DateObj = new \DateTime($date);
        $ulr_year = $DateObj->format("Y");
        
        $location_data = getCurrentLocation();
        $location_id = $location_data ? $location_data->location_id : null;

        $isDuplicate = isDuplicateUlrSequence(
            $ulr_sequence,
            $ulr_id,
            $ulr_year,
            $location_id,
            TestReportRt::class,
            $id
        );

        return response()->json([
            'response_code' => $isDuplicate ? 0 : 1,
            'response_message' => $isDuplicate ? 'Duplicate ULR No. Found.' : 'Available.'
        ]);
    }

    public function getTechniqueSheetsForCustomer(Request $request)
    {  
        $year = getCurrentYearData()->id;
        $location_data = getCurrentLocation();
        
        $query = DB::table('technique_sheet_rt as ts')
            ->leftJoin('customers as c', 'c.id', '=', 'ts.customer_id')
            ->leftJoin('type_of_job as tj', 'tj.id', '=', 'ts.type_of_job_id')
            ->leftJoin('job_descriptions as jd', 'jd.id', '=', 'ts.job_desc_id')
            ->leftJoin('area_of_coverage as ac', 'ac.area_of_coverage_id', '=', 'ts.area_of_coverage_id')
            ->where('ts.current_location_id', $location_data->location_id)
            ->where('ts.year_id', $year);


        $sheets = $query->select([
            'ts.technique_sheet_rt_id',
            'ts.technique_sheet_rt_no',
            DB::raw("'' as rss_reference"),
            'ts.technique_sheet_rt_date',
            'c.customer',
            'tj.type_of_job',
            'jd.job_description',
            'ts.part_no',
            'ts.drg_no',
            'ac.area_of_coverage'
        ])
        ->orderBy('ts.technique_sheet_rt_id', 'desc')
        ->get();

        foreach ($sheets as $s) {
            $s->technique_sheet_rt_date = (!empty($s->technique_sheet_rt_date) && $s->technique_sheet_rt_date != '0000-00-00')
                ? Date::createFromFormat('Y-m-d', $s->technique_sheet_rt_date)->format('d/m/Y')
                : '';
        }

        return response()->json([
            'response_code' => 1,
            'sheets' => $sheets
        ]);
    }

    public function getTechniqueSheetDetails(Request $request)
    {
        $sheet = TechniqueSheetRt::where('technique_sheet_rt_id', $request->id)
            ->first();

        $details = DB::table('technique_sheet_rt_details as ts_det')
            ->leftJoin('film_brand as fb', 'fb.film_brand_id', '=', 'ts_det.film_brand_id')
            ->leftJoin('film_type as ft', 'ft.film_type_id', '=', 'ts_det.film_type_id')
            ->leftJoin('film as f', 'f.film_id', '=', 'ts_det.film_id')
            ->leftJoin('iqi_designation as iqi', 'iqi.iqi_designation_id', '=', 'ts_det.iqi_designation_id')
            ->leftJoin('iqi_sensitivity as iqis', 'iqis.iqi_sensitivity_id', '=', 'ts_det.iqi_sensitivity_id')
            ->where('ts_det.technique_sheet_rt_id', $request->id)
            ->select([
                'ts_det.*',
                'fb.film_brand',
                'ft.film_type',
                'f.film_size_inch',
                'f.film_size_cm',
                'f.film_size_inch as film_size',
                DB::raw("IFNULL(NULLIF(ts_det.iqi_designation, ''), iqi.iqi_designation) as iqi"),
                DB::raw("IFNULL(NULLIF(ts_det.iqi_sensitivity, ''), iqis.iqi_sensitivity) as sensitivity")
            ])
            ->get();

        return response()->json([
            'response_code' => 1,
            'sheet' => $sheet,
            'details' => $details
        ]);
    }

    public function existsCustomerClient(Request $request)
    {
        if ($request->term != "") {
            $location_id = getCurrentLocation()->location_id;
            $data = DB::table('test_report_rt')
                ->select('customer_client')
                ->where('current_location_id', $location_id)
                ->where('customer_client', 'LIKE', $request->term . '%')
                ->groupBy('customer_client')
                ->get();
            if ($data->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($data as $row) {
                    if (trim($row->customer_client) === '') continue;
                    $output .= '<li parent-id="customer_client" list-id="customer_client_list" class="list-group-item" tabindex="0">' . e($row->customer_client) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'customerClientList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'customerClientList' => '',
            'response_code' => 1,
        ]);
    }

    public function existsWeldingProcess(Request $request)
    {
        if ($request->term != "") {
            $location_id = getCurrentLocation()->location_id;
            $data = DB::table('test_report_rt')
                ->select('welding_process')
                ->where('current_location_id', $location_id)
                ->where('welding_process', 'LIKE', $request->term . '%')
                ->groupBy('welding_process')
                ->get();
            if ($data->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($data as $row) {
                    if (trim($row->welding_process) === '') continue;
                    $output .= '<li parent-id="welding_process" list-id="welding_process_list" class="list-group-item" tabindex="0">' . e($row->welding_process) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'weldingProcessList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'weldingProcessList' => '',
            'response_code' => 1,
        ]);
    }

    public function existsJointType(Request $request)
    {
        if ($request->term != "") {
            $location_id = getCurrentLocation()->location_id;
            $data = DB::table('test_report_rt')
                ->select('joint_type')
                ->where('current_location_id', $location_id)
                ->where('joint_type', 'LIKE', $request->term . '%')
                ->groupBy('joint_type')
                ->get();
            if ($data->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($data as $row) {
                    if (trim($row->joint_type) === '') continue;
                    $output .= '<li parent-id="joint_type" list-id="joint_type_list" class="list-group-item" tabindex="0">' . e($row->joint_type) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'jointTypeList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'jointTypeList' => '',
            'response_code' => 1,
        ]);
    }

    public function existsWelderName(Request $request)
    {
        if ($request->term != "") {
            $location_id = getCurrentLocation()->location_id;
            $data = DB::table('test_report_rt')
                ->select('welder_name')
                ->where('current_location_id', $location_id)
                ->where('welder_name', 'LIKE', $request->term . '%')
                ->groupBy('welder_name')
                ->get();
            if ($data->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($data as $row) {
                    if (trim($row->welder_name) === '') continue;
                    $output .= '<li parent-id="welder_name" list-id="welder_name_list" class="list-group-item" tabindex="0">' . e($row->welder_name) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'welderNameList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'welderNameList' => '',
            'response_code' => 1,
        ]);
    }

    public function existsWelderId(Request $request)
    {
        if ($request->term != "") {
            $location_id = getCurrentLocation()->location_id;
            $data = DB::table('test_report_rt')
                ->select('welder_id')
                ->where('current_location_id', $location_id)
                ->where('welder_id', 'LIKE', $request->term . '%')
                ->groupBy('welder_id')
                ->get();
            if ($data->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($data as $row) {
                    if (trim($row->welder_id) === '') continue;
                    $output .= '<li parent-id="welder_id" list-id="welder_id_list" class="list-group-item" tabindex="0">' . e($row->welder_id) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'welderIdList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'welderIdList' => '',
            'response_code' => 1,
        ]);
    }

    public function existsPosition(Request $request)
    {
        if ($request->term != "") {
            $location_id = getCurrentLocation()->location_id;
            $data = DB::table('test_report_rt')
                ->select('position')
                ->where('current_location_id', $location_id)
                ->where('position', 'LIKE', $request->term . '%')
                ->groupBy('position')
                ->get();
            if ($data->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($data as $row) {
                    if (trim($row->position) === '') continue;
                    $output .= '<li parent-id="position" list-id="position_list" class="list-group-item" tabindex="0">' . e($row->position) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'positionList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'positionList' => '',
            'response_code' => 1,
        ]);
    }

    public function existsPurposeOfTesting(Request $request)
    {
        if ($request->term != "") {
            $location_id = getCurrentLocation()->location_id;
            $data = DB::table('test_report_rt')
                ->select('purpose_of_testing')
                ->where('current_location_id', $location_id)
                ->where('purpose_of_testing', 'LIKE', $request->term . '%')
                ->groupBy('purpose_of_testing')
                ->get();
            if ($data->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($data as $row) {
                    if (trim($row->purpose_of_testing) === '') continue;
                    $output .= '<li parent-id="purpose_of_testing" list-id="purpose_of_testing_list" class="list-group-item" tabindex="0">' . e($row->purpose_of_testing) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'purposeOfTestingList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'purposeOfTestingList' => '',
            'response_code' => 1,
        ]);
    }

    public function existsIqi(Request $request)
    {
        if ($request->term != "") {
            $location_id = getCurrentLocation()->location_id;
            $data = DB::table('test_report_rt')
                ->select('iqi')
                ->where('current_location_id', $location_id)
                ->where('iqi', 'LIKE', $request->term . '%')
                ->groupBy('iqi')
                ->get();
            if ($data->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($data as $row) {
                    if (trim($row->iqi) === '') continue;
                    $output .= '<li parent-id="iqi" list-id="iqi_list" class="list-group-item" tabindex="0">' . e($row->iqi) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'iqiList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'iqiList' => '',
            'response_code' => 1,
        ]);
    }

    public function existsLeadScreenThick(Request $request)
    {
        if ($request->term != "") {
            $location_id = getCurrentLocation()->location_id;
            $front_data = DB::table('test_report_rt')
                ->select('lead_screen_thick AS val')
                ->where('current_location_id', $location_id)
                ->where('lead_screen_thick', 'LIKE', $request->term . '%')
                ->groupBy('lead_screen_thick');

            $data = DB::table('test_report_rt')
                ->select('lead_screen_thick_back AS val')
                ->where('current_location_id', $location_id)
                ->where('lead_screen_thick_back', 'LIKE', $request->term . '%')
                ->groupBy('lead_screen_thick_back')
                ->union($front_data)
                ->get();

            if ($data->isNotEmpty()) {
                $parentId = $request->parentId ?? 'lead_screen_thick';
                $listId = $request->listId ?? 'lead_screen_thick_list';
                
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($data as $row) {
                    if (trim($row->val) === '') continue;
                    $output .= '<li parent-id="' . e($parentId) . '" list-id="' . e($listId) . '" class="list-group-item" tabindex="0">' . e($row->val) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'leadScreenThickList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'leadScreenThickList' => '',
            'response_code' => 1,
        ]);
    }

    public function existsTestCarriedOutAt(Request $request)
    {
        if ($request->term != "") {
            $location_id = getCurrentLocation()->location_id;
            $data = DB::table('test_report_rt')
                ->select('test_carried_out_at')
                ->where('current_location_id', $location_id)
                ->where('test_carried_out_at', 'LIKE', $request->term . '%')
                ->groupBy('test_carried_out_at')
                ->get();
            if ($data->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($data as $row) {
                    if (trim($row->test_carried_out_at) === '') continue;
                    $output .= '<li parent-id="test_carried_out_at" list-id="test_carried_out_at_list" class="list-group-item" tabindex="0">' . e($row->test_carried_out_at) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'testCarriedOutAtList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'testCarriedOutAtList' => '',
            'response_code' => 1,
        ]);
    }

    public function existsFilmProcessing(Request $request)
    {
        if ($request->term != "") {
            $location_id = getCurrentLocation()->location_id;
            $data = DB::table('test_report_rt')
                ->select('film_processing')
                ->where('current_location_id', $location_id)
                ->where('film_processing', 'LIKE', $request->term . '%')
                ->groupBy('film_processing')
                ->get();
            if ($data->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($data as $row) {
                    if (trim($row->film_processing) === '') continue;
                    $output .= '<li parent-id="film_processing" list-id="film_processing_list" class="list-group-item" tabindex="0">' . e($row->film_processing) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'filmProcessingList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'filmProcessingList' => '',
            'response_code' => 1,
        ]);
    }

    public function existsTestTechnique(Request $request)
    {
        if ($request->term != "") {
            $location_id = getCurrentLocation()->location_id;
            $data = DB::table('test_report_rt')
                ->select('test_technique')
                ->where('current_location_id', $location_id)
                ->where('test_technique', 'LIKE', $request->term . '%')
                ->groupBy('test_technique')
                ->get();
            if ($data->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($data as $row) {
                    if (trim($row->test_technique) === '') continue;
                    $output .= '<li parent-id="test_technique" list-id="test_technique_list" class="list-group-item" tabindex="0">' . e($row->test_technique) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'testTechniqueList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'testTechniqueList' => '',
            'response_code' => 1,
        ]);
    }

    public function existsPartNo(Request $request)
    {
        if ($request->term != "") {
            $location_id = getCurrentLocation()->location_id;
            $data = DB::table('test_report_rt')
                ->select('part_no')
                ->where('current_location_id', $location_id)
                ->where('part_no', 'LIKE', $request->term . '%')
                ->groupBy('part_no')
                ->get();
            if ($data->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($data as $row) {
                    if (trim($row->part_no) === '') continue;
                    $output .= '<li parent-id="part_no" list-id="part_no_list" class="list-group-item" tabindex="0">' . e($row->part_no) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'partNoList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'partNoList' => '',
            'response_code' => 1,
        ]);
    }

    public function existsFinding(Request $request)
    {
        if ($request->term != "") {
            $data = DB::table('finding')
                ->select('finding_name', 'abbreviation')
                ->where('finding_name', 'LIKE', $request->term . '%')
                ->orderBy('finding_name', 'asc')
                ->get();
            if ($data->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($data as $row) {
                    if (trim($row->finding_name) === '') continue;
                    $output .= '<li parent-id="finding" list-id="finding_list" class="list-group-item" data-abbreviation="' . e($row->abbreviation) . '" tabindex="0">' . e($row->finding_name) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'findingList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'findingList' => '',
            'response_code' => 1,
        ]);
    }

    public function existsDrgNo(Request $request)
    {
        if ($request->term != "") {
            $location_id = getCurrentLocation()->location_id;
            $data = DB::table('test_report_rt')
                ->select('drg_no')
                ->where('current_location_id', $location_id)
                ->where('drg_no', 'LIKE', $request->term . '%')
                ->groupBy('drg_no')
                ->get();
            if ($data->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($data as $row) {
                    if (trim($row->drg_no) === '') continue;
                    $output .= '<li parent-id="drg_no" list-id="drg_no_list" class="list-group-item" tabindex="0">' . e($row->drg_no) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'drgNoList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'drgNoList' => '',
            'response_code' => 1,
        ]);
    }

    public function existsProductCode(Request $request)
    {
        if ($request->term != "") {
            $location_id = getCurrentLocation()->location_id;
            $data = DB::table('test_report_rt')
                ->select('product_code')
                ->where('current_location_id', $location_id)
                ->where('product_code', 'LIKE', $request->term . '%')
                ->whereNotNull('product_code')
                ->where('product_code', '!=', '')
                ->groupBy('product_code')
                ->get();
            if ($data->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($data as $row) {
                    if (trim($row->product_code) === '') continue;
                    $output .= '<li parent-id="product_code" list-id="product_code_list" class="list-group-item" tabindex="0">' . e($row->product_code) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'productCodeList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'productCodeList' => '',
            'response_code' => 1,
        ]);
    }

    public function getCopyReportsList(Request $request)
    {
        $year = getCurrentYearData()->id;
        $location = getCurrentLocation()->location_id;

        $query = TestReportRt::select([
            'test_report_rt.test_report_rt_id as id',
            'test_report_rt.test_report_no',
            'test_report_rt.revision_number',
            'test_report_rt.test_report_date',
            'customers.customer',
            'test_report_rt.nabl_type_fix',
            'test_report_rt.job_type_fix',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            'test_report_rt.part_no',
            'test_report_rt.drg_no',
            'materials.material',
            'test_report_rt.heat_no',
            'test_report_rt.rt_no',
            'test_report_rt.product_code'
        ])
        ->leftJoin('customers', 'customers.id', '=', 'test_report_rt.customer_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'test_report_rt.type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'test_report_rt.job_desc_id')
        ->leftJoin('materials', 'materials.id', '=', 'test_report_rt.material_id')
        ->where('test_report_rt.year_id', $year)
        ->where('test_report_rt.current_location_id', $location);

        if ($request->filled('type_of_job_id')) {
            $query->where('test_report_rt.type_of_job_id', $request->type_of_job_id);
        }

        $reports = $query->orderBy('test_report_rt.test_report_rt_id', 'desc')->get();

        foreach ($reports as $r) {
            $r->test_report_date = ($r->test_report_date != "" && $r->test_report_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $r->test_report_date)->format('d/m/Y')
                : "";
        }

        return response()->json([
            'response_code' => 1,
            'reports' => $reports
        ]);
    }

    public function getReportDetailsForCopy(Request $request)
    {
        $id = $request->id;
        $report_data = DB::select('CALL test_report_rt_master(?)', [$id]);
        if (!empty($report_data)) {
            $report_data = $report_data[0];

            $report_data->test_report_date = ($report_data->test_report_date != "" && $report_data->test_report_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $report_data->test_report_date)->format('d/m/Y') 
                : "";
            $report_data->dc_date = ($report_data->dc_date != "" && $report_data->dc_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $report_data->dc_date)->format('d/m/Y') 
                : "";
            $report_data->po_date = ($report_data->po_date != "" && $report_data->po_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $report_data->po_date)->format('d/m/Y') 
                : "";
            $report_data->date_of_receipt = ($report_data->date_of_receipt != "" && $report_data->date_of_receipt != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $report_data->date_of_receipt)->format('d/m/Y') 
                : "";
            $report_data->date_of_testing = ($report_data->date_of_testing != "" && $report_data->date_of_testing != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $report_data->date_of_testing)->format('d/m/Y') 
                : "";
            $report_data->amendment_date = ($report_data->amendment_date != "" && $report_data->amendment_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $report_data->amendment_date)->format('d/m/Y') 
                : "";
            // Prevent Malformed UTF-8 characters exception by base64 encoding binary blobs
            foreach ($report_data as $key => $value) {
                if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                    $report_data->$key = base64_encode($value);
                }
            }

            if (isset($report_data->cmp_logo)) {
                $report_data->cmp_logo = base64_encode($report_data->cmp_logo);
            }
            if (isset($report_data->tested_by_signature)) {
                $report_data->tested_by_signature = base64_encode($report_data->tested_by_signature);
            }
            if (isset($report_data->reviewed_by_signature)) {
                $report_data->reviewed_by_signature = base64_encode($report_data->reviewed_by_signature);
            }
            if (isset($report_data->authorized_by_signature)) {
                $report_data->authorized_by_signature = base64_encode($report_data->authorized_by_signature);
            }

            $details = DB::select('CALL test_report_rt_details(?)', [$id]);

            return response()->json([
                'response_code' => 1,
                'report_data' => $report_data,
                'report_details_data' => $details,
            ]);
        }

        return response()->json([
            'response_code' => 0,
            'response_message' => 'Record Not Found',
        ]);
    }

    public function getPendingReportRevisionData(Request $request)
    {
        $id = $request->id;
        $report_data = DB::select('CALL test_report_rt_master(?)', [$id]);
        if (!empty($report_data)) {
            $report_data = $report_data[0];

            $pmir = DB::table('pending_material_inward_rt_qty as pmir')
                ->leftJoin('material_inward_details as mid', 'mid.material_inward_details_id', '=', 'pmir.material_inward_details_id')
                ->leftJoin('material_inward as mi', 'mi.material_inward_id', '=', 'mid.material_inward_id')
                ->where('pmir.revision_from_report_rt_id', $id)
                ->select([
                    'pmir.pending_qty',
                    'pmir.observation_sheet_details_id',
                    'pmir.material_inward_details_id',
                    'mid.part_no',
                    'mid.drg_no',
                    'mid.material_id',
                    'mid.heat_no',
                    'mid.rt_no',
                    'mid.product_code',
                    'mid.type_of_job_id',
                    'mid.job_desc_id',
                   'mid.area_of_coverage_id',
                    'mi.dc_no',
                    'mi.dc_date',
                    'mi.po_no',
                    'mi.po_date',
                    'mi.material_inward_date as date_of_receipt'
                ])
                ->orderBy('pmir.observation_sheet_details_id', 'desc')
                ->first();

            if ($pmir) {
                $report_data->pend_qty = (float)$pmir->pending_qty + (float)$report_data->rt_report_qty;
                $report_data->observation_sheet_details_id = $pmir->observation_sheet_details_id ?? 0;
                $report_data->material_inward_details_id = $pmir->material_inward_details_id ?? 0;
                $report_data->part_no = $pmir->part_no ?? '';
                $report_data->drg_no = $pmir->drg_no ?? '';
                $report_data->material_id = $pmir->material_id ?? '';
                $report_data->heat_no = $pmir->heat_no ?? '';
                $report_data->rt_no = $pmir->rt_no ?? '';
                $report_data->product_code = $pmir->product_code ?? '';
                $report_data->type_of_job_id = $pmir->type_of_job_id ?? '';
                $report_data->job_desc_id = $pmir->job_desc_id ?? '';
                $report_data->area_of_coverage_id = $pmir->area_of_coverage_id ?? '';
               
                $report_data->dc_no = $pmir->dc_no ?? '';
                $report_data->dc_date = ($pmir->dc_date && $pmir->dc_date != '0000-00-00') ? Date::createFromFormat('Y-m-d', $pmir->dc_date)->format('d/m/Y') : '';
                $report_data->po_no = $pmir->po_no ?? '';
                $report_data->po_date = ($pmir->po_date && $pmir->po_date != '0000-00-00') ? Date::createFromFormat('Y-m-d', $pmir->po_date)->format('d/m/Y') : '';
                if (!empty($pmir->date_of_receipt) && $pmir->date_of_receipt != '0000-00-00') {
                    $report_data->date_of_receipt = Date::createFromFormat('Y-m-d', $pmir->date_of_receipt)->format('d/m/Y');
                }else{
                    $report_data->date_of_receipt = '';
                }
            } else {
                $report_data->pend_qty = 0 + (float)$report_data->rt_report_qty;
                $report_data->observation_sheet_details_id = null;
                $report_data->material_inward_details_id = null;
                $report_data->dc_no = '';
                $report_data->dc_date = '';
                $report_data->po_no = '';
                $report_data->po_date = '';
                $report_data->po_date = '';
                $report_data->part_no =  '';
                $report_data->drg_no =  '';
                $report_data->material_id = '';
                $report_data->heat_no =  '';
                $report_data->rt_no =  '';
                $report_data->product_code = '';
                $report_data->type_of_job_id = '';
                $report_data->job_desc_id = '';
                $report_data->area_of_coverage_id = '';
                $report_data->date_of_receipt = ($report_data->date_of_receipt != "" && $report_data->date_of_receipt != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $report_data->date_of_receipt)->format('d/m/Y') 
                : "";
            }

            $report_data->test_report_date = ($report_data->test_report_date != "" && $report_data->test_report_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $report_data->test_report_date)->format('d/m/Y') 
                : "";
           
            $report_data->date_of_testing = ($report_data->date_of_testing != "" && $report_data->date_of_testing != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $report_data->date_of_testing)->format('d/m/Y') 
                : "";
            $report_data->amendment_date = ($report_data->amendment_date != "" && $report_data->amendment_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $report_data->amendment_date)->format('d/m/Y') 
                : "";

            $baseReportId = !empty($report_data->revision_test_report_rt_id) ? $report_data->revision_test_report_rt_id : $id;
            $report_data->revision_test_report_rt_id = $baseReportId;

            // Calculate next revision number
            $revCount = DB::table('test_report_rt')
                ->where('revision_test_report_rt_id', $baseReportId)
                ->whereNotNull('revision_number')
                ->where('revision_number', '!=', '')
                ->count();

            $nextRevNo = "R" . ($revCount + 1);

            // Fetch Observation Sheet details for the specific observation_sheet_details_id
            if (!empty($report_data->observation_sheet_details_id)) {
                $obsData = DB::table('observation_sheet_details as osd')
                    ->join('observation_sheet as os', 'os.observation_sheet_id', '=', 'osd.observation_sheet_id')
                    ->where('osd.observation_sheet_details_id', $report_data->observation_sheet_details_id)
                    ->select('os.observation_sheet_no', 'os.observation_sheet_date')
                    ->first();

                if ($obsData) {
                    $obsDate = (!empty($obsData->observation_sheet_date) && $obsData->observation_sheet_date != "0000-00-00")
                        ? Date::createFromFormat('Y-m-d', $obsData->observation_sheet_date)->format('d/m/Y')
                        : "";
                    $report_data->worksheet_no = $obsData->observation_sheet_no . ($obsDate ? ' - ' . $obsDate : '');
                    $report_data->observation_sheet_no = $obsData->observation_sheet_no;
                    $report_data->observation_sheet_date = $obsDate;
                }
            } else {
                $report_data->worksheet_no = '';
                $report_data->observation_sheet_no = '';
                $report_data->observation_sheet_date = '';
            }

            // Master level Exclusions: Clear result
            $report_data->result = "";
            // Force process type to Repair for revision
            $report_data->process_type = "Repair";

            foreach ($report_data as $key => $value) {
                if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                    $report_data->$key = base64_encode($value);
                }
            }

            if (isset($report_data->cmp_logo)) {
                $report_data->cmp_logo = base64_encode($report_data->cmp_logo);
            }
            if (isset($report_data->tested_by_signature)) {
                $report_data->tested_by_signature = base64_encode($report_data->tested_by_signature);
            }
            if (isset($report_data->reviewed_by_signature)) {
                $report_data->reviewed_by_signature = base64_encode($report_data->reviewed_by_signature);
            }
            if (isset($report_data->authorized_by_signature)) {
                $report_data->authorized_by_signature = base64_encode($report_data->authorized_by_signature);
            }

            $details = DB::select('CALL test_report_rt_details(?)', [$id]);
            if (!empty($details)) {
                foreach ($details as $d) {
                    $d->orig_film_result = $d->film_result_type_fix;

                    $filmResultName = strtolower(trim($d->film_result_type_fix ?? $d->film_result ?? ''));
                    $recTypeId = (!empty($d->record_type_id) && $d->record_type_id > 0) ? intval($d->record_type_id) : 1;

                    // ૧. જો record_type_id == 3 હોય (Ok/Accepted) -> Hide
                    if ($recTypeId == 3) {
                        $isRepair = false;
                        $d->record_type_id = 3;
                        $d->is_revision_row = 0;
                    }
                    // ૨. જો record_type_id == 2 હોય અથવા Result Repair/Retake/Reshoot હોય -> Show & Blank
                    elseif (
                        $recTypeId == 2 ||
                        strpos($filmResultName, 'repair') !== false ||
                        strpos($filmResultName, 'retake') !== false ||
                        strpos($filmResultName, 'reshoot') !== false
                    ) {
                        if (strpos($filmResultName, 'accept') !== false) {
                            $isRepair = false;
                            $d->record_type_id = 3;
                            $d->is_revision_row = 0;
                        }else{
                        $isRepair = true;
                        $d->record_type_id = 2;
                        $d->is_revision_row = 1;
                        }
                    }
                    // ૩. જો record_type_id == 1 હોય:
                    else {
                        // 1 માં જો Accepted કે Not Accepted હોય તો જ Finding & Result રહેશે (No Reset)
                        if (strpos($filmResultName, 'accept') !== false || strpos($filmResultName, 'ok') !== false) {
                            $isRepair = false;
                            $d->record_type_id = 3;
                            $d->is_revision_row = 0;
                        } else {
                            // બાકીના તમામ કેસમાં Reset થશે અને record_type_id = 2 ગણાશે
                            $isRepair = true;
                            $d->record_type_id = 3;
                            $d->is_revision_row = 0;
                        }
                    }

                    $d->is_repair_type = $isRepair ? 1 : 0;

                    // જો Repair હોય તો જ Finding & Result reset થશે
                    if ($isRepair) {
                        $d->finding_id = null;
                        $d->finding = "";
                        $d->finding_level_id = null;
                        $d->finding_level = "";
                        $d->result_id = null;
                        $d->result = "";
                        $d->film_result = "";
                    }

                    // include_in_measurement_sheet હંમેશા 'Yes' જ રહેશે
                    $d->include_in_measurement_sheet = 'Yes';

                    // $d->main_rt_detail_id = (!empty($d->main_rt_detail_id) && $d->main_rt_detail_id != 0)
                    //     ? $d->main_rt_detail_id
                    //     : $d->test_report_rt_details_id;

                    if ($d->is_revision_row == 1) {
                        $d->main_rt_detail_id = 0;
                    } else {
                        $d->main_rt_detail_id = (!empty($d->main_rt_detail_id) && $d->main_rt_detail_id != 0)
                            ? $d->main_rt_detail_id
                            : $d->test_report_rt_details_id;
                    }
                }
            }

            $cameraIds = array_values(array_filter([
                $report_data->ir_camera_id ?? 0,
                $report_data->co_camera_id ?? 0,
                $report_data->xray_camera_id ?? 0
            ]));
            $report_data->extra_cameras = [];
            if (!empty($cameraIds)) {
                $report_data->extra_cameras = RTCamera::select([
                    'rt_camera.rt_camera_id',
                    'rt_camera.name_for_display',
                    'rt_camera.rt_camera_name',
                    'rt_camera.rt_serial_no',
                    'rt_camera.rt_isotope',
                    'rt_camera.rt_x_ray',
                    'rt_camera.rt_focal_spot',
                    'rt_camera_details.rtcd_initial_activity_ci',
                    'rt_camera_details.rtcd_source_size',
                    'rt_camera_details.rtcd_last_of_loading_date',
                ])
                ->leftJoin('rt_camera_details', function ($join) {
                    $join->on('rt_camera_details.rtcd_rt_camera_id', '=', 'rt_camera.rt_camera_id')
                        ->whereRaw('rt_camera_details.rtcd_id = (
                            SELECT MAX(rtcd_id) 
                            FROM rt_camera_details 
                            WHERE rtcd_rt_camera_id = rt_camera.rt_camera_id
                        )');
                })
                ->whereIn('rt_camera.rt_camera_id', $cameraIds)
                ->get();
            }

            return response()->json([
                'response_code' => 1,
                'report_data' => $report_data,
                'report_details_data' => $details,
                'next_revision_number' => $nextRevNo,
            ]);
        }

        return response()->json([
            'response_code' => 0,
            'response_message' => 'Record Not Found',
        ]);
    }

    public function getCameraDetail(Request $request)
    {
        $cameraId = $request->camera_id;
        $dateOfTesting = $request->date_of_testing;

        if (empty($cameraId)) {
            return response()->json([
                'response_code' => 0,
                'response_message' => 'Camera ID required',
            ]);
        }

        $camera = \App\Models\RTCamera::find($cameraId);
        if (!$camera) {
            return response()->json([
                'response_code' => 0,
                'response_message' => 'Camera not found',
            ]);
        }

        $cameraDetail = \App\Models\RTCameraDetails::where('rtcd_rt_camera_id', $cameraId)
            ->orderBy('rtcd_id', 'desc')
            ->first();

        $isotope = $camera->rt_isotope;
        $initialCurie = $cameraDetail ? (float)$cameraDetail->rtcd_initial_activity_ci : 0;
        $loadingDate = $cameraDetail ? $cameraDetail->rtcd_last_of_loading_date : null;
        $sourceSize = $cameraDetail ? $cameraDetail->rtcd_source_size : '';

        $sourceStrength = '';
        if (strcasecmp($isotope, 'X-Rays') == 0 || strcasecmp($isotope, 'X-Ray') == 0) {
            $sourceStrength = '';
        } elseif (empty($dateOfTesting)) {
            $sourceStrength = '0.00 Ci';
        } elseif (!empty($initialCurie) && !empty($loadingDate)) {
            try {
                $loading = Carbon::parse($loadingDate);
                $testing = strpos($dateOfTesting, '/') !== false 
                    ? Carbon::createFromFormat('d/m/Y', $dateOfTesting) 
                    : Carbon::parse($dateOfTesting);

                if ($loading->greaterThan($testing)) {
                    $sourceStrength = number_format($initialCurie, 2, '.', '') . ' Ci';
                } else {
                    $dayDifference = $loading->diffInDays($testing);
                    if ($dayDifference == 0) {
                        $dayDifference = 1;
                    }

                    $halfLife = 0;
                    if (strcasecmp($isotope, 'Ir-192') == 0) {
                        $halfLife = 74.5;
                    } elseif (strcasecmp($isotope, 'Co-60') == 0) {
                        $halfLife = 1925;
                    }
                    // } elseif (strcasecmp($isotope, 'Se-75') == 0) {
                    //     $halfLife = 120;
                    // }

                    if ($halfLife > 0) {
                        $decay = pow(2.718, ($dayDifference * 0.693 / $halfLife));
                        $present = $initialCurie / $decay;
                        $sourceStrength = number_format($present, 2, '.', '') . ' Ci';
                    } else {
                        $sourceStrength = number_format($initialCurie, 2, '.', '') . ' Ci';
                    }
                }
            } catch (\Exception $e) {
                $sourceStrength = number_format($initialCurie, 2, '.', '') . ' Ci';
            }
        } elseif (!empty($initialCurie)) {
            $sourceStrength = number_format($initialCurie, 2, '.', '') . ' Ci';
        }

        return response()->json([
            'response_code' => 1,
            'camera_id' => $cameraId,
            'radiation_source' => $isotope,
            'source_strength' => $sourceStrength,
            'source_size' => $sourceSize,
            'xray_kv_ma' => $camera->rt_x_ray ?? '',
            'xray_focal_size' => $camera->rt_focal_spot ?? '',
            'loading_date' => $loadingDate ?? '',
            'initial_curie' => $initialCurie ?? 0,
        ]);
    }

    public function checkNegativePendingQty($customer_id = 0, $location_id = 0, $from_type_id_fix = 0, $material_inward_details_id = 0, $revision_from_report_rt_id = 0, $observation_sheet_details_id = 0, $transaction_mode = 'U')
    {
        $location_id = $location_id ?: getCurrentLocation()->location_id;
        // dd($transaction_mode);
        $msg = ($transaction_mode === 'I')
            ? "You Can't Insert, Something Went Wrong."
            : "You Can't Update, Something Went Wrong.";

        // Check pending_material_inward_rt_qty
        if (!empty($material_inward_details_id)) {
            $query = DB::table('pending_material_inward_rt_qty')
                ->where('customer_id', $customer_id)
                ->where('material_inward_details_id', $material_inward_details_id)
                ->where('current_location_id', $location_id)
                ->where('from_type_id_fix', $from_type_id_fix)
                ->where('observation_sheet_details_id', $observation_sheet_details_id)
                ->where('revision_from_report_rt_id', $revision_from_report_rt_id);
           
            $pendingQty = $query->value('pending_qty');

            if ($pendingQty !== null && (float)$pendingQty < 0) {
                DB::rollBack();
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => $msg,
                ]);
            }
        }

        return null;
    }

    public function qtyValidation($from_qty, $next_qty, $transaction_mode, $customer_id, $material_inward_details_id, $observation_sheet_details_id, $revision_from_report_rt_id = 0, $from_type_id_fix = 0, $test_report_rt_id = 0)
    {
        $location_id = getCurrentLocation()->location_id;

        // ===================== 1. Test Report VALIDATION FOR OBSERVATION / INWARD =====================
        if ($from_qty > 0) {
            // 🔒 Pessimistic Lock
            if (!empty($observation_sheet_details_id)) {
                DB::table('observation_sheet_details')
                    ->where('observation_sheet_details_id', $observation_sheet_details_id)
                    ->lockForUpdate()
                    ->first();
            } elseif (!empty($material_inward_details_id)) {
                DB::table('material_inward_details')
                    ->where('material_inward_details_id', $material_inward_details_id)
                    ->lockForUpdate()
                    ->first();
            }

            $query = DB::table('pending_material_inward_rt_qty')
                ->where('current_location_id', $location_id)
                ->where('customer_id', $customer_id)
                ->where('from_type_id_fix', $from_type_id_fix)
                ->where('observation_sheet_details_id', $observation_sheet_details_id)
                ->where('material_inward_details_id', $material_inward_details_id)
                ->where('revision_from_report_rt_id', $revision_from_report_rt_id);
           

            $pending_rt_qty = (float)($query->value('pending_qty') ?? 0);

            if ($pending_rt_qty < (float)$from_qty) {
                DB::rollBack();
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => 'RT Report Qty. Is Used.',
                ]);
            }
        }

        // ===================== 2. Test Report VALIDATION FOR DOWNSTREAM =====================
        if ($next_qty > 0 && !empty($test_report_rt_id)) {

            // Repair Observation Sheet Pending View Check
            $pending_repair_qty = DB::table('pending_observation_sheet_qty')
                ->where('from_type_id_fix', $from_type_id_fix)
                // ->where('test_report_rt_id', $test_report_rt_id)
                ->where('material_inward_details_id', $material_inward_details_id)
                ->where('current_location_id', $location_id)
                ->value('pending_qty');

            if ($pending_repair_qty !== null && (float)$pending_repair_qty < (float)$next_qty) {
                DB::rollBack();

                $message = ($transaction_mode === 'D')
                    ? "You Can't Delete, Test Report RT Is Used In Observation Sheet."
                    : "You Can't Update, Test Report RT Qty. Is Used In Observation Sheet.";

                return response()->json([
                    'response_code'    => '0',
                    'response_message' => $message,
                ]);
            }

            // Measurement Sheet Pending View Check
            $pending_ms_sqin = DB::table('pending_rt_reports_for_measurement')
                ->where('report_id', $test_report_rt_id)
                ->where('current_location_id', $location_id)
                ->where('customer_id', $customer_id)
                ->value('total_sqin');

            $rt_total_sqin = (float) DB::table('test_report_rt')
                ->where('test_report_rt_id', $test_report_rt_id)
                ->selectRaw('IFNULL(mms_ir_192_sqin,0) + IFNULL(mms_co_60_sqin,0) + IFNULL(mms_x_ray_sqin,0) + IFNULL(mms_ir_192_repair_sqin,0) + IFNULL(mms_co_60_repair_sqin,0) + IFNULL(mms_x_ray_repair_sqin,0) as total_sqin')
                ->value('total_sqin');

            if ($pending_ms_sqin !== null && $rt_total_sqin > 0 && (float)$pending_ms_sqin < (float)$rt_total_sqin) {
                DB::rollBack();

                $message = ($transaction_mode === 'D')
                    ? "You Can't Delete, Test Report RT Is Used In Measurement Sheet."
                    : "You Can't Update, Test Report RT Is Used In Measurement Sheet.";

                return response()->json([
                    'response_code'    => '0',
                    'response_message' => $message,
                ]);
            }
        }

        return null; // No error
    }

    public function getObservationSubDetails(Request $request)
    {
        $id = $request->id;

        $filmSizeUnitFix = DB::table('observation_sheet_details')
            ->where('observation_sheet_details_id', $id)
            ->value('film_size_unit_fix');

        $subDetails = DB::table('observation_sheet_details_details_input as osd')
            ->leftJoin('film_brand as fb', 'fb.film_brand_id', '=', 'osd.film_brand_id')
            ->leftJoin('film_type as ft', 'ft.film_type_id', '=', 'osd.film_type_id')
            ->leftJoin('iqi_designation as iqd', 'iqd.iqi_designation_id', '=', 'osd.iqi_designation_id')
            ->leftJoin('iqi_sensitivity as iqs', 'iqs.iqi_sensitivity_id', '=', 'osd.iqi_sensitivity_id')
            ->leftJoin('film as f', 'f.film_id', '=', 'osd.film_id')
            ->where('osd.observation_sheet_details_id', $id)
            ->select([
                'osd.sr_no',
                'osd.identification',
                'osd.location',
                'osd.source_id_fix',
                'osd.film_brand_id',
                'fb.film_brand as film_brand_name',
                'osd.film_type_id',
                'ft.film_type as film_type_name',
                'osd.thickness',
                'osd.sfd',
                'osd.iqi_designation_id',
                DB::raw("COALESCE(NULLIF(osd.iqi_designation, ''), iqd.iqi_designation) as iqi_designation_name"),
                'osd.iqi_sensitivity_id',
                DB::raw("COALESCE(NULLIF(osd.iqi_sensitivity, ''), iqs.iqi_sensitivity) as iqi_sensitivity_name"),

                'osd.film_id as detail_film_id',
                'f.film_size_inch as film_size_inch',
                'f.film_size_cm as film_size_cm',
                'f.sq_in as sq_in',
                'f.sq_cm as sq_cm',
                'osd.no_of_film_fix',
                'osd.film_qty'
            ])
            ->orderBy('osd.sr_no', 'asc')
            ->get();

        foreach ($subDetails as $row) {
            foreach ($row as $key => $value) {
                if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                    $row->$key = base64_encode($value);
                }
            }
        }

        return response()->json([
            'response_code' => 1,
            'film_size_unit_fix' => $filmSizeUnitFix,
            'sub_details' => $subDetails
        ]);
    }

    /**
     * Calculate total_no_of_films_rev & total_area_in_sq_rev for Repair joints (record_type_id == 2)
     *
     * @param array $details
     * @param string $filmSizeUnit
     * @return array
     */
    private function calculateAllHeaderSummaries($details, $filmSizeUnit = 'inch')
    {
        $sourceOrder = ['Ir-192', 'Co-60', 'X-Ray'];
        $getSourceRank = function($s) use ($sourceOrder) {
            $idx = array_search($s, $sourceOrder);
            return $idx !== false ? $idx : 999;
        };

        $sourceQtyCounts = [];
        $totalQty = 0;
        $sizeCounts = [];
        $totalFilmSizeQty = 0;
        $sourceAreaSums = [];
        $totalAreaSum = 0;

        if (!empty($details)) {
            foreach ($details as $row) {
                if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;

                $source = trim($row['source_id_fix'] ?? '');
                $qty = intval($row['film_qty'] ?? 0);
                $type = trim($row['film_type_name'] ?? '');
                $size = ($filmSizeUnit === 'cm') ? trim($row['film_size_cm'] ?? '') : trim($row['film_size_inch'] ?? '');
                $area = floatval(($filmSizeUnit === 'cm') ? ($row['total_sq_cm'] ?? 0) : ($row['total_sq_in'] ?? 0));

                if ($source) {
                    $sourceQtyCounts[$source] = ($sourceQtyCounts[$source] ?? 0) + $qty;
                    $totalQty += $qty;

                    $sourceAreaSums[$source] = ($sourceAreaSums[$source] ?? 0) + $area;
                    $totalAreaSum += $area;
                }

                if ($source && $type && $size) {
                    $key = $source . " - " . $type . " - " . $size;
                    $sizeCounts[$key] = ($sizeCounts[$key] ?? 0) + $qty;
                    $totalFilmSizeQty += $qty;
                }
            }
        }

        uksort($sourceQtyCounts, function($a, $b) use ($getSourceRank) {
            return $getSourceRank($a) - $getSourceRank($b);
        });
        $noOfFilmsParts = [];
        foreach ($sourceQtyCounts as $src => $q) {
            $noOfFilmsParts[] = $src . " - " . $q;
        }
        $no_of_films = !empty($noOfFilmsParts) ? implode(', ', $noOfFilmsParts) . " = " . $totalQty : '';

        uksort($sizeCounts, function($a, $b) use ($getSourceRank) {
            $srcA = explode(' - ', $a)[0] ?? '';
            $srcB = explode(' - ', $b)[0] ?? '';
            return $getSourceRank($srcA) - $getSourceRank($srcB);
        });
        $filmSizeParts = [];
        foreach ($sizeCounts as $k => $q) {
            $filmSizeParts[] = $k . " - " . $q;
        }
        $film_size = !empty($filmSizeParts) ? implode(', ', $filmSizeParts) . " = " . $totalFilmSizeQty : '';

        uksort($sourceAreaSums, function($a, $b) use ($getSourceRank) {
            return $getSourceRank($a) - $getSourceRank($b);
        });
        $areaParts = [];
        foreach ($sourceAreaSums as $src => $a) {
            if ($a > 0) {
                $areaParts[] = $src . " - " . number_format($a, 2, '.', '');
            }
        }
        $total_area = !empty($areaParts) ? implode(', ', $areaParts) . " = " . number_format($totalAreaSum, 2, '.', '') : '';

        return [
            'no_of_films' => $no_of_films,
            'film_size'   => $film_size,
            'total_area'  => $total_area,
        ];
    }

    private function calculateRevisionTotals($details, $filmSizeUnit = 'inch')
    {
        $ir_qty_rev = 0;
        $co_qty_rev = 0;
        $xray_qty_rev = 0;

        $ir_area_rev = 0.0;
        $co_area_rev = 0.0;
        $xray_area_rev = 0.0;

        if (!empty($details)) {
            foreach ($details as $row) {
                if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;

                $recType = $row['record_type_id'] ?? 1;
                $resName = strtolower(trim($row['film_result_type_fix'] ?? $row['film_result_name'] ?? $row['film_result'] ?? ''));
                if ($recType == 2 && ($resName === 'repair' || strpos($resName, 'repair') !== false)) {
                    $src = trim($row['source_id_fix'] ?? '');
                    $qty = intval($row['film_qty'] ?? 1);
                    $area = floatval(($filmSizeUnit === 'cm') ? ($row['total_sq_cm'] ?? 0) : ($row['total_sq_in'] ?? 0));

                    if ($src === 'Ir-192') {
                        $ir_qty_rev += $qty;
                        $ir_area_rev += $area;
                    } elseif ($src === 'Co-60') {
                        $co_qty_rev += $qty;
                        $co_area_rev += $area;
                    } elseif ($src === 'X-Ray') {
                        $xray_qty_rev += $qty;
                        $xray_area_rev += $area;
                    }
                }
            }
        }

        $total_no_of_films_rev = null;
        $total_films_parts = [];
        $total_qty_rev = $ir_qty_rev + $co_qty_rev + $xray_qty_rev;
        if ($ir_qty_rev > 0)   $total_films_parts[] = "Ir-192 - " . $ir_qty_rev;
        if ($co_qty_rev > 0)   $total_films_parts[] = "Co-60 - " . $co_qty_rev;
        if ($xray_qty_rev > 0) $total_films_parts[] = "X-Ray - " . $xray_qty_rev;

        if (!empty($total_films_parts)) {
            $total_no_of_films_rev = implode(', ', $total_films_parts) . " = " . $total_qty_rev;
        }

        $total_area_in_sq_rev = null;
        $total_area_parts = [];
        $total_area_sum_rev = $ir_area_rev + $co_area_rev + $xray_area_rev;
        if ($ir_area_rev > 0)   $total_area_parts[] = "Ir-192 - " . number_format($ir_area_rev, 2, '.', '');
        if ($co_area_rev > 0)   $total_area_parts[] = "Co-60 - " . number_format($co_area_rev, 2, '.', '');
        if ($xray_area_rev > 0) $total_area_parts[] = "X-Ray - " . number_format($xray_area_rev, 2, '.', '');

        if (!empty($total_area_parts)) {
            $total_area_in_sq_rev = implode(', ', $total_area_parts) . " = " . number_format($total_area_sum_rev, 2, '.', '');
        }

        return [
            'total_no_of_films_rev' => $total_no_of_films_rev,
            'total_area_in_sq_rev'  => $total_area_in_sq_rev,
        ];
    }

    /**
     * RT Label Print ની માફક જ દર વખતે ફ્રેશ Test Report RT PDF જનરેટ કરવું
     */
    public function printReport(Request $request)
    {
        if (!hasAccess("test_report_rt", "print")) {
            abort(401);
        }

        $id = base64_decode($request->id);
        $name = sanitize_pdf_name($request->name);
        $type = 'test_report_rt';

        GeneratePdf($id, $name, $type, 'add', $request->job_type_fix, $request->nabl_type_fix, $request->repair);

        $outputPath = asset('storage/reports/' . $type . '_reports_file/' . $name . '.pdf') . '?v=' . time();
        return redirect($outputPath);
    }

    /**
     * Dynamic dropdown options for RT Cameras based on Location Master settings
     */
    public function getRTCamerasDropdown(Request $request)
    {
        $ir_cameras = getRTCamerasByIsotope('Ir-192', $request->ir_camera_id ?? null);
        $co_cameras = getRTCamerasByIsotope('Co-60', $request->co_camera_id ?? null);
        $xray_cameras = getRTCamerasByIsotope('X-Ray', $request->xray_camera_id ?? null);

        return response()->json([
            'response_code' => 1,
            'ir_cameras' => $ir_cameras,
            'co_cameras' => $co_cameras,
            'xray_cameras' => $xray_cameras,
        ]);
    }
}