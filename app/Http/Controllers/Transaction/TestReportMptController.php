<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\TestReportMpt;
use App\Models\Transaction\TestReportMptEquipmentDetails;
use App\Models\Transaction\TestReportMptMaterialDetails;
use App\Models\Transaction\TestReportMptDetails;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use App\Models\Transaction\MaterialInwardDetails;
use App\Models\Transaction\ObservationSheetDetails;
use DateTime;
use App\Models\Customer;

class TestReportMptController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-test_report_mpt');
    }

    public function index(Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();

        $reports = TestReportMpt::select([
            'test_report_mpt.test_report_mpt_id as id',
            'test_report_mpt.test_report_no',
            'test_report_mpt.test_report_sequence',
            'test_report_mpt.test_report_date',
            'test_report_mpt.nabl_type_fix',
            'test_report_mpt.ulr_no',
            'test_report_mpt.test_carried_out_at',
            'test_report_mpt.job_type_fix',
            'test_report_mpt.from_type_id_fix',
            'customers.customer',
            'material_inward.dc_no',
            'material_inward.dc_date',
            'material_inward.po_no',
            'material_inward.po_date',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            'test_report_mpt.part_no',
            'test_report_mpt.drg_no',
            'materials.material',
            'test_report_mpt.heat_no',
            'test_report_mpt.product_code',
            'test_report_mpt.total_qty',
            'test_report_mpt.created_on',
            'test_report_mpt.created_by',
            'test_report_mpt.last_by',
            'test_report_mpt.last_on'
        ])
        ->leftJoin('customers', 'customers.id', '=', 'test_report_mpt.customer_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'test_report_mpt.type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'test_report_mpt.job_desc_id')
        ->leftJoin('materials', 'materials.id', '=', 'test_report_mpt.material_id')
        ->leftJoin('material_inward_details', 'material_inward_details.material_inward_details_id', '=', 'test_report_mpt.material_inward_details_id')
        ->leftJoin('material_inward', 'material_inward.material_inward_id', '=', 'material_inward_details.material_inward_id')
        ->where('test_report_mpt.year_id', $year_data->id)
        ->where('test_report_mpt.current_location_id', $location_data->location_id);

        $dataTable = DataTables::of($reports)
        ->filterColumn('test_report_mpt.test_report_no', function($query, $keyword) {
            applySequenceSearch($query, $keyword, 'test_report_mpt.test_report_no');
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
        ->filterColumn('test_report_mpt.test_report_date', function ($q, $k) {
            applyDate($q, $k, 'test_report_mpt.test_report_date');
        })
        ->filterColumn('material_inward.dc_date', function ($q, $k) {
            applyDate($q, $k, 'material_inward.dc_date');
        })
        ->filterColumn('material_inward.po_date', function ($q, $k) {
            applyDate($q, $k, 'material_inward.po_date');
        })
        ->addColumn('options', function ($report) {
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("test_report_mpt", "print")) {
                    $report_number = !empty($report->test_report_no) ? '_' . str_replace('/', '_', $report->test_report_no) : "";
                    $cust_name = !empty($report->customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $report->customer) : "";
                    $pdfName   = 'Test_Report_MPT' . $report_number . $cust_name;
                    $encodedId = base64_encode($report->id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=test_report_mpt");
                    $action .= '<li><a class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }

                if (hasAccess("test_report_mpt", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_mpt_report" data-id="' . $report->id . '"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("test_report_mpt", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn delete_mpt_report" data-id="' . $report->id . '" data-name="' . htmlspecialchars($report->test_report_no) . '">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });

        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'test_report_mpt');
        return $dataTable
        ->rawColumns(['options', 'last_by', 'last_on', 'created_by', 'created_on'])
        ->make(true);
    }

    public function getLatestTestReportMptNumber(Request $request)
    {
        $modal = TestReportMpt::class;
        $sequence = 'test_report_sequence';
        $prefix = 'MPT';

        $num_format = getLatestSequence($modal, $sequence, $prefix);

        return response()->json([
            'response_code' => 1,
            'latest_no' => $num_format['format'],
            'number' => $num_format['isFound'],
        ]);
    }

    public function store(Request $request)
    {
        $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();
        $user_id = Auth::user()->id;

        $request->validate([
            'test_report_no' => 'required',
            'test_report_date' => 'required',
            'customer_id' => 'required',
            'material_inward_details_id' => 'required',
            'type_of_job_id' => 'required',
            'job_desc_id' => 'required',
            'material_id' => 'required',
            'area_of_coverage_id' => 'required',
            'procedure_ref_id' => 'required',
            'acceptance_standard_id' => 'required',
            'date_of_testing' => 'required',
        ], [
            'test_report_no.required' => 'Enter Report No.',
            'test_report_date.required' => 'Enter Report Date.',
            'customer_id.required' => 'Enter Customer.',
            'material_inward_details_id.required' => 'Select Pending Inward Data.',
            'type_of_job_id.required' => 'Enter Type of Job.',
            'job_desc_id.required' => 'Enter Job Description.',
            'material_id.required' => 'Enter Material.',
            'area_of_coverage_id.required' => 'Select Area of Coverage.',
            'procedure_ref_id.required' => 'Select Procedure Refrence.',
            'acceptance_standard_id.required' => 'Select Acceptance Standard.',
            'date_of_testing.required' => 'Enter Date of Testing.',
        ]);
        $equipment = is_string($request->equipment_details_data) ? json_decode($request->equipment_details_data, true) : ($request->equipment_details ?? []);
        if (!empty($equipment)) {
            foreach ($equipment as $row) {
                if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;
                $eq_status = DB::table('equipment_mpt')->where('em_id', $row['em_id'])->value('em_status');
                if ($eq_status !== 'Active') {
                    return response()->json([
                        'response_code' => '0',
                        'response_message' => 'Selected Equipment is not active.'
                    ]);
                }
            }
        }

        /*
        $materials = is_string($request->material_details_data) ? json_decode($request->material_details_data, true) : ($request->material_details ?? []);
        if (!empty($materials)) {
            foreach ($materials as $row) {
                if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;
                $mat_status = DB::table('material_mpt')->where('mm_id', $row['mm_id'])->value('mm_status');
                if ($mat_status !== 'Active') {
                    return response()->json([
                        'response_code' => '0',
                        'response_message' => 'Selected Material is not active.'
                    ]);
                }
            }
        }
        */

        DB::beginTransaction();
        try {
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

            if (!empty($request->date_of_testing) && !empty($materials)) {
                $testDateObj = Carbon::createFromFormat('d/m/Y', $request->date_of_testing);
                foreach ($materials as $row) {
                    if (empty($row) || ($row['mode'] ?? '') === 'Delete' || empty($row['expiry_date'])) continue;
                    try {
                        $expDateObj = Carbon::createFromFormat('d/m/Y', $row['expiry_date']);
                        if ($testDateObj > $expDateObj) {
                            return response()->json([
                                'response_code' => '0',
                                'response_message' => 'Date of Testing cannot be greater than Expiry Date.'
                            ]);
                        }
                    } catch (\Exception $e) {}
                }
            }

            $fromTypeId = $request->from_type_id_fix;
            $qtyCheck = $this->qtyValidation($request->total_qty ?? 0, 0, 'I', $request->customer_id, $request->material_inward_details_id, $request->observation_sheet_details_id ?? null, $fromTypeId, 0);
            if ($qtyCheck) {
                return $qtyCheck;
            }

            // $pendingQty = DB::table('pending_material_inward_mpt_qty')
            //     ->where('material_inward_details_id', $request->material_inward_details_id)
            //     ->value('pending_qty') ?? 0;

            // if ($request->total_qty > $pendingQty && $pendingQty > 0) {
            //     return response()->json([
            //         'response_code' => '0',
            //         'response_message' => 'MPT Report Qty. cannot be more than Pending Qty.',
            //     ]);
            // }

            if (!empty($request->amendment_no) && empty($request->amendment_date)) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Enter Amendment Date.',
                ]);
            }

            if (!empty($request->amendment_date) && empty($request->amendment_no)) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Enter Amendment No.',
                ]);
            }

            $existNumber = TestReportMpt::where([
                ['test_report_sequence', $request->test_report_sequence],
                ['test_report_no', $request->test_report_no],
                ['year_id', $year_data->id],
                ['current_location_id', $location_data->location_id]
            ])->first();

            if ($existNumber) {
                $latestNo = $this->getLatestTestReportMptNumber($request);
                $area = json_decode($latestNo->getContent(), true);
                $report_no = $area['latest_no'];
                $report_seq = $area['number'];
            } else {
                $report_no = $request->test_report_no;
                $report_seq = $request->test_report_sequence;
            }

            $defectogramPath = null;
            $blobImage = null;
            if ($request->defectogram_image_doc) {
                $file = new File();
                if (str_contains($request->defectogram_image_doc, 'temp_media/')) {
                    $isFound = $file->getFileFromTemp($request->defectogram_image_doc, 'test_report_mpt');
                } else {
                    $isFound = $file->getFileCopyFormUpload($request->defectogram_image_doc, 'test_report_mpt');
                }
                if ($isFound !== false) {
                    $defectogramPath = $isFound;
                    $filePath = storage_path('app/public/' . $defectogramPath);
                    if (file_exists($filePath)) {
                        $blobImage = file_get_contents($filePath);
                    }
                } else {
                    $defectogramPath = $request->defectogram_image_doc;
                    $filePath = storage_path('app/public/' . $defectogramPath);
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
                $DateObj = new DateTime($dateVal);
                $ulr_year = $DateObj->format("Y");

                if (isDuplicateUlrSequence($request->ulr_sequence, $ulr_id, $ulr_year, $location_data->location_id, TestReportMpt::class)) {
                    return response()->json([
                        'response_code' => 0,
                        'response_message' => 'Duplicate ULR No. Found.'
                    ]);
                }
                $ulr_no = $request->ulr_no;
                $ulr_sequence = $request->ulr_sequence;
            }

            $page_id = getMenuIdBassedOnDisplayName('test_report_mpt');
            $assign_format_no = $page_id ? getAssignFormateNoForTransaction($location_data->location_id, $page_id->id, $request->test_report_date) : '';

            $report = TestReportMpt::create([
                'test_report_sequence' => $report_seq,
                'test_report_no' => $report_no,
                'test_report_date' => $reportDate,
                'customer_id' => $request->customer_id,
                'material_inward_details_id' => $request->material_inward_details_id,
                'observation_sheet_details_id' => $request->observation_sheet_details_id ?? null,
                'nabl_type_fix' => $request->nabl_type_fix ?? 'Non NABL',
                'job_type_fix' => $request->job_type_fix ?? 'Non-Welding',
                'from_type_id_fix' => $fromTypeId,
                'customer_client' => $request->customer_client,
                'type_of_job_id' => $request->type_of_job_id,
                'job_desc_id' => $request->job_desc_id,
                'part_no' => $request->part_no,
                'drg_no' => $request->drg_no,
                'material_id' => $request->material_id,
                'heat_no' => $request->heat_no,
                'product_code' => $request->product_code,
                'date_of_testing' => $testDate,
                'date_of_testing_value' => $request->date_of_testing_value,
                'test_carried_out_at' => $request->test_carried_out_at,
                'amendment_no' => $request->amendment_no,
                'amendment_date' => !empty($request->amendment_date) ? Carbon::createFromFormat('d/m/Y', $request->amendment_date)->format('Y-m-d') : null,
                'amendment_reason' => $request->amendment_reason,
                'stage_of_test' => $request->stage_of_test,
                'area_of_coverage_id' => $request->area_of_coverage_id,
                'surface_condition' => $request->surface_condition,
                'surface_temp' => $request->surface_temp,
                'thickness' => $request->thickness,
                'test_technique' => $request->test_technique,
                'type_of_magnetization' => $request->type_of_magnetization,
                'type_of_current' => $request->type_of_current,
                'prod_pole_spacing' => $request->prod_pole_spacing,
                'performance_verification' => $request->performance_verification,
                'lighting' => $request->lighting,
                'light_intensity' => $request->light_intensity,
                'background_light' => $request->background_light,
                'uva_light_intensity' => $request->uva_light_intensity,
                'yoke_wt_lift_check' => $request->yoke_wt_lift_check,
                'procedure_ref_id' => $request->procedure_ref_id,
                'acceptance_standard_id' => $request->acceptance_standard_id,
                'defectogram_image' => $defectogramPath,
                'defectogram_image_blob' => $blobImage,
                'ulr_id' => $ulr_id,
                'ulr_no' => $ulr_no,
                'ulr_sequence' => $ulr_sequence,
                'ulr_year' => $ulr_year,
                'tested_by_authority_person_id' => $request->tested_by_authority_person_id,
                'reviewed_by_authority_person_id' => $request->reviewed_by_authority_person_id,
                'authorized_by_authority_person_id' => $request->authorized_by_authority_person_id,
                'sp_note' => $request->sp_note,
                'note' => $request->note,
                'total_qty' => $request->total_qty ?? 0,
                'assign_format_no' => $assign_format_no,
                'company_id' => $location_data->company_id,
                'year_id' => $year_data->id,
                'current_location_id' => $location_data->location_id,
                'created_by' => $user_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
            ]);

            // Save Equipment Details
            $equipmentDetails = is_string($request->equipment_details_data) ? json_decode($request->equipment_details_data, true) : ($request->equipment_details ?? []);
            if (!empty($equipmentDetails)) {
                foreach ($equipmentDetails as $eq) {
                    if (empty($eq) || ($eq['mode'] ?? '') === 'Delete') continue;
                    if (!empty($eq['em_id'])) {
                        TestReportMptEquipmentDetails::create([
                            'test_report_mpt_id' => $report->test_report_mpt_id,
                            'em_id' => $eq['em_id'],
                            'em_sr_no' => $eq['em_sr_no'] ?? null,
                            'make' => $eq['make'] ?? null,
                            'cal_due_date' => !empty($eq['cal_due_date']) ? Carbon::createFromFormat('d/m/Y', $eq['cal_due_date'])->format('Y-m-d') : null,
                        ]);
                    }
                }
            }

            // Save Material Details
            $materialDetails = is_string($request->material_details_data) ? json_decode($request->material_details_data, true) : ($request->material_details ?? []);
            if (!empty($materialDetails)) {
                foreach ($materialDetails as $mat) {
                    if (empty($mat) || ($mat['mode'] ?? '') === 'Delete') continue;
                    if (!empty($mat['mm_id'])) {
                        TestReportMptMaterialDetails::create([
                            'test_report_mpt_id' => $report->test_report_mpt_id,
                            'mm_id' => $mat['mm_id'],
                            'batch_no' => $mat['batch_no'] ?? null,
                            'make' => $mat['make'] ?? null,
                            'expiry_date' => !empty($mat['expiry_date']) ? Carbon::createFromFormat('d/m/Y', $mat['expiry_date'])->format('Y-m-d') : null,
                        ]);
                    }
                }
            }

            // Save Test Details
            $reportDetails = is_string($request->report_details_data) ? json_decode($request->report_details_data, true) : ($request->details ?? []);
            if (!empty($reportDetails)) {
                foreach ($reportDetails as $det) {
                    if (empty($det) || ($det['mode'] ?? '') === 'Delete') continue;
                    if (!empty($det['mpt_test_no'])) {
                        TestReportMptDetails::create([
                            'test_report_mpt_id' => $report->test_report_mpt_id,
                            'mpt_test_no' => $det['mpt_test_no'],
                            'heat_no' => $det['heat_no'] ?? null,
                            'quantity' => $det['quantity'] ?? 1,
                            'discontinuity_evaluation' => $det['discontinuity_evaluation'] ?? null,
                            'result_id' => $det['result_id'] ?? null,
                        ]);
                    }
                }
            }

            $checkPending = $this->checkNegativePendingQty(
                $report->customer_id,
                $location_data->location_id,
                $report->from_type_id_fix,
                $report->material_inward_details_id,
                $request->observation_sheet_details_id ?? null,
                'I'
            );
            if ($checkPending) {
                return $checkPending;
            }

            DB::commit();

            // Generate Print PDF
            $report_number = !empty($report->test_report_no) ? '_' . str_replace('/', '_', $report->test_report_no) : "";
            $cust = DB::table('customers')->where('id', $report->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Test_Report_MPT' . $report_number . $cust_name;

            if (function_exists('GeneratePdf')) {
                GeneratePdf($report->test_report_mpt_id, $pdf_name, 'test_report_mpt', 'add');
            }
            $encodedId = base64_encode($report->test_report_mpt_id);
            $url = hasAccess("test_report_mpt", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=test_report_mpt") : "";

            return response()->json([
                'response_code' => '1',
                'url' => $url,
                'response_message' => getResponseMessage('store_success'),
                'id' => $report->test_report_mpt_id
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
        $id = $request->id;
        $report_data = DB::select('CALL test_report_mpt_master(?)', [$id]);
        if (empty($report_data)) {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Not Found.'
            ]);
        }

        $report_data = $report_data[0];

        $report_number = !empty($report_data->test_report_no) ? '_' . str_replace('/', '_', $report_data->test_report_no) : "";
        $cust = Customer::where('id', $report_data->customer_id)->value('customer');
        // $cust = DB::table('customers')->where('id', $report_data->customer_id)->value('customer');
        $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
        $report_data->pdf_name = 'Test_Report_MPT' . $report_number . $cust_name;

        $report_data->test_report_date = ($report_data->test_report_date != "" && $report_data->test_report_date != "0000-00-00")
            ? Date::createFromFormat('Y-m-d', $report_data->test_report_date)->format('d/m/Y') 
            : "";
        $report_data->dc_date = ($report_data->dc_date != "" && $report_data->dc_date != "0000-00-00")
            ? Date::createFromFormat('Y-m-d', $report_data->dc_date)->format('d/m/Y') 
            : "";
        $report_data->po_date = ($report_data->po_date != "" && $report_data->po_date != "0000-00-00")
            ? Date::createFromFormat('Y-m-d', $report_data->po_date)->format('d/m/Y') 
            : "";
        $report_data->date_of_testing = ($report_data->date_of_testing != "" && $report_data->date_of_testing != "0000-00-00")
            ? Date::createFromFormat('Y-m-d', $report_data->date_of_testing)->format('d/m/Y') 
            : "";
        $report_data->amendment_date = ($report_data->amendment_date != "" && $report_data->amendment_date != "0000-00-00")
            ? Date::createFromFormat('Y-m-d', $report_data->amendment_date)->format('d/m/Y') 
            : "";
        $report_data->date_of_receipt = ($report_data->date_of_receipt != "" && $report_data->date_of_receipt != "0000-00-00")
            ? Date::createFromFormat('Y-m-d', $report_data->date_of_receipt)->format('d/m/Y') 
            : "";

        // if (!empty($report_data->observation_sheet_details_id)) {
        //     $obsData = DB::table('observation_sheet_details as osd')
        //         ->join('observation_sheet as os', 'os.observation_sheet_id', '=', 'osd.observation_sheet_id')
        //         ->where('osd.observation_sheet_details_id', $report_data->observation_sheet_details_id)
        //         ->select('os.observation_sheet_no', 'os.observation_sheet_date')
        //         ->first();
        //     if ($obsData) {
        //         $report_data->observation_sheet_no = $obsData->observation_sheet_no;
        //         $report_data->observation_sheet_date = ($obsData->observation_sheet_date != '' && $obsData->observation_sheet_date != '0000-00-00') ? Date::createFromFormat('Y-m-d', $obsData->observation_sheet_date)->format('d/m/Y') : '';
        //     }
        // }

        if (isset($report_data->cmp_logo)) {
            $report_data->cmp_logo = base64_encode($report_data->cmp_logo);
        }
        if (isset($report_data->defectogram_image_blob)) {
            $report_data->defectogram_image_blob = base64_encode($report_data->defectogram_image_blob);
        }

        // Prevent Malformed UTF-8 characters exception by base64 encoding binary blobs
        foreach ($report_data as $key => $value) {
            if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                $report_data->$key = base64_encode($value);
            }
        }

        $equipment = DB::select('CALL test_report_mpt_equipment_details(?)', [$id]);
        foreach ($equipment as $row) {
            $row->cal_due_date = ($row->cal_due_date != "" && $row->cal_due_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $row->cal_due_date)->format('d/m/Y')
                : "";
            foreach ($row as $key => $value) {
                if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                    $row->$key = base64_encode($value);
                }
            }
        }

        $materials = DB::select('CALL test_report_mpt_material_details(?)', [$id]);
        foreach ($materials as $row) {
            $row->expiry_date = ($row->expiry_date != "" && $row->expiry_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $row->expiry_date)->format('d/m/Y')
                : "";
            foreach ($row as $key => $value) {
                if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                    $row->$key = base64_encode($value);
                }
            }
        }

        $details = DB::select('CALL test_report_mpt_details(?)', [$id]);
        foreach ($details as $row) {
            foreach ($row as $key => $value) {
                if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                    $row->$key = base64_encode($value);
                }
            }
        }

        // Get current pending qty for this inward detail & from_type_id_fix (so edit mode can display it)
        $fromTypeId = $report_data->from_type_id_fix;
        $pendQuery = DB::table('pending_material_inward_mpt_qty')
            ->where('material_inward_details_id', $report_data->material_inward_details_id)
            ->where('from_type_id_fix', $fromTypeId);
        if (!empty($report_data->observation_sheet_details_id) && $fromTypeId == 2) {
            $pendQuery->where('observation_sheet_details_id', $report_data->observation_sheet_details_id);
        }
        $pend_qty = $pendQuery->value('pending_qty') ?? 0;
        // Add back the already-used qty from this report so the displayed value makes sense
        $pend_qty = $pend_qty + ($report_data->total_qty ?? 0);
        $report_data->pend_qty = $pend_qty;

        return response()->json([
            'response_code' => '1',
            'master' => $report_data,
            'equipment' => $equipment,
            'materials' => $materials,
            'details' => $details
        ]);
    }

    public function update(Request $request)
    {
        $user_id = Auth::user()->id;
        $id = $request->test_report_mpt_id;
        $location_data = getCurrentLocation();
        $request->validate([
            'test_report_mpt_id' => 'required',
            'test_report_no' => 'required',
            'test_report_date' => 'required',
            'customer_id' => 'required',
            'type_of_job_id' => 'required',
            'job_desc_id' => 'required',
            'material_id' => 'required',
            'area_of_coverage_id' => 'required',
            'procedure_ref_id' => 'required',
            'acceptance_standard_id' => 'required',
            'date_of_testing' => 'required',
        ], [
            'test_report_no.required' => 'Enter Report No.',
            'test_report_date.required' => 'Enter Report Date.',
            'customer_id.required' => 'Enter Customer.',
            'type_of_job_id.required' => 'Enter Type of Job.',
            'job_desc_id.required' => 'Enter Job Description.',
            'material_id.required' => 'Enter Material.',
            'area_of_coverage_id.required' => 'Select Area of Coverage.',
            'procedure_ref_id.required' => 'Select Procedure Refrence.',
            'acceptance_standard_id.required' => 'Select Acceptance Standard.',
            'date_of_testing.required' => 'Enter Date of Testing.',
        ]);

        $report = TestReportMpt::where('test_report_mpt_id', $request->id)->first();
        if (!$report) {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Report Not Found',
            ]);
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

        // $pendingQty = DB::table('pending_material_inward_mpt_qty')
        //     ->where('material_inward_details_id', $request->material_inward_details_id)
        //     ->value('pending_qty') ?? 0;

        // $allowedQty = $pendingQty + (int)($report->total_qty ?? 0);
        
        // if ((int)$request->total_qty > $allowedQty) {
        //     return response()->json([
        //         'response_code' => '0',
        //         'response_message' => 'MPT Report Qty. cannot be more than Pending Qty.',
        //     ]);
        // }

        if (!empty($request->amendment_no) && empty($request->amendment_date)) {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Enter Amendment Date.',
            ]);
        }

        if (!empty($request->amendment_date) && empty($request->amendment_no)) {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Enter Amendment No.',
            ]);
        }

        $existingEqIds = DB::table('test_report_mpt_equipment_details')
            ->where('test_report_mpt_id', $id)
            ->pluck('em_id')
            ->toArray();

        $equipment = is_string($request->equipment_details_data) ? json_decode($request->equipment_details_data, true) : ($request->equipment_details ?? []);
        if (!empty($equipment)) {
            foreach ($equipment as $row) {
                if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;
                if (!in_array($row['em_id'], $existingEqIds)) {
                    $eq_status = DB::table('equipment_mpt')->where('em_id', $row['em_id'])->value('em_status');
                    if ($eq_status !== 'Active') {
                        return response()->json([
                            'response_code' => '0',
                            'response_message' => 'Selected Equipment is not active.'
                        ]);
                    }
                }
            }
        }

        $existingMatIds = DB::table('test_report_mpt_material_details')
            ->where('test_report_mpt_id', $id)
            ->pluck('mm_id')
            ->toArray();

        /*
        $materials = is_string($request->material_details_data) ? json_decode($request->material_details_data, true) : ($request->material_details ?? []);
        if (!empty($materials)) {
            foreach ($materials as $row) {
                if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;
                if (!in_array($row['mm_id'], $existingMatIds)) {
                    $mat_status = DB::table('material_mpt')->where('mm_id', $row['mm_id'])->value('mm_status');
                    if ($mat_status !== 'Active') {
                        return response()->json([
                            'response_code' => '0',
                            'response_message' => 'Selected Material is not active.'
                        ]);
                    }
                }
            }
        }
        */

        DB::beginTransaction();
        try {
            $report = TestReportMpt::findOrFail($id);

            $oldQty = (float)($report->total_qty ?? 0);
            $diff = (float)($request->total_qty ?? 0) - $oldQty;
            $from_qty = $diff > 0 ? $diff : 0;
            $next_qty = $diff < 0 ? abs($diff) : 0;

            $fromTypeId = $request->from_type_id_fix;
            $qtyCheck = $this->qtyValidation($from_qty, $next_qty, 'U', $request->customer_id, $report->material_inward_details_id, $report->observation_sheet_details_id ?? null, $fromTypeId, $request->id);
            if ($qtyCheck) {
                return $qtyCheck;
            }

            // $testDate = !empty($request->date_of_testing) ? Carbon::createFromFormat('d/m/Y', $request->date_of_testing)->format('Y-m-d') : null;
            // $reportDate = !empty($request->test_report_date) ? Carbon::createFromFormat('d/m/Y', $request->test_report_date)->format('Y-m-d') : null;

            // if ($reportDate && $testDate && $reportDate < $testDate) {
            //     return response()->json([
            //         'response_code' => '0',
            //         'response_message' => 'Date Of Testing Must Be Less Than Report Date.',
            //     ]);
            // }

            if (!empty($request->date_of_testing) && !empty($materials)) {
                $testDateObj = Carbon::createFromFormat('d/m/Y', $request->date_of_testing);
                foreach ($materials as $row) {
                    if (empty($row) || ($row['mode'] ?? '') === 'Delete' || empty($row['expiry_date'])) continue;
                    try {
                        $expDateObj = Carbon::createFromFormat('d/m/Y', $row['expiry_date']);
                        if ($testDateObj > $expDateObj) {
                            return response()->json([
                                'response_code' => '0',
                                'response_message' => 'Date of Testing cannot be greater than Expiry Date.'
                            ]);
                        }
                    } catch (\Exception $e) {}
                }
            }

            // $pendingQty = DB::table('pending_material_inward_mpt_qty')
            //     ->where('material_inward_details_id', $request->material_inward_details_id)
            //     ->value('pending_qty') ?? 0;

            // $availableQty = $pendingQty + ($report->total_qty ?? 0);

            // if ($request->total_qty > $availableQty && $availableQty > 0) {
            //     return response()->json([
            //         'response_code' => '0',
            //         'response_message' => 'MPT Report Qty. cannot be more than Pending Qty.',
            //     ]);
            // }

            // if (!empty($request->amendment_no) && empty($request->amendment_date)) {
            //     return response()->json([
            //         'response_code' => '0',
            //         'response_message' => 'Enter Amendment Date.',
            //     ]);
            // }

            // if (!empty($request->amendment_date) && empty($request->amendment_no)) {
            //     return response()->json([
            //         'response_code' => '0',
            //         'response_message' => 'Enter Amendment No.',
            //     ]);
            // }

            $file = new File();
            $defectogramPath = $report->defectogram_image;
            $blobImage = $report->defectogram_image_blob;

            if ($request->filled('defectogram_image_doc') && $request->defectogram_image_doc != '') {
                if ($defectogramPath && $defectogramPath != $request->defectogram_image_doc) {
                    $file->delete_file($defectogramPath);
                }
                if (str_contains($request->defectogram_image_doc, 'temp_media/')) {
                    $isFound = $file->getFileFromTemp($request->defectogram_image_doc, 'test_report_mpt');
                } else {
                    $isFound = $file->getFileCopyFormUpload($request->defectogram_image_doc, 'test_report_mpt');
                }
                if ($isFound !== false) {
                    $defectogramPath = $isFound;
                    $filePath = storage_path('app/public/' . $defectogramPath);
                    if (file_exists($filePath)) {
                        $blobImage = file_get_contents($filePath);
                    }
                } else {
                    $defectogramPath = $request->defectogram_image_doc;
                    $filePath = storage_path('app/public/' . $defectogramPath);
                    if (file_exists($filePath)) {
                        $blobImage = file_get_contents($filePath);
                    }
                }
            } elseif ($request->defectogram_image_doc == '' || $request->defectogram_image_doc === null) {
                if ($defectogramPath) {
                    $file->delete_file($defectogramPath);
                }
                $defectogramPath = null;
                $blobImage = null;
            }

            $ulr_id = $report->ulr_id;
            $ulr_no = $report->ulr_no;
            $ulr_sequence = $report->ulr_sequence;
            $ulr_year = $report->ulr_year;

            if ($request->nabl_type_fix === 'NABL') {
                $ulr_id = $request->ulr_id;
                $dateVal = Carbon::createFromFormat('d/m/Y', $request->test_report_date)->format('Y-m-d');
                $DateObj = new \DateTime($dateVal);
                $ulr_year = $DateObj->format("Y");

                if (isDuplicateUlrSequence($request->ulr_sequence, $ulr_id, $ulr_year, $location_data->location_id, TestReportMpt::class, $id)) {
                    return response()->json([
                        'response_code' => 0,
                        'response_message' => 'Duplicate ULR No. Found.'
                    ]);
                }
                $ulr_no = $request->ulr_no;
                $ulr_sequence = $request->ulr_sequence;
            } else {
                $ulr_id = null;
                $ulr_no = null;
                $ulr_sequence = null;
                $ulr_year = null;
            }

            $report->update([
                'test_report_sequence' => $request->test_report_sequence,
                'test_report_no' => $request->test_report_no,
                'test_report_date' => $reportDate,
                'customer_id' => $request->customer_id,
                'material_inward_details_id' => $request->material_inward_details_id,
                'observation_sheet_details_id' => $request->observation_sheet_details_id ?? null,
                'nabl_type_fix' => $request->nabl_type_fix ?? 'Non NABL',
                'job_type_fix' => $request->job_type_fix ?? 'Non-Welding',
                'from_type_id_fix' => $fromTypeId,
                'customer_client' => $request->customer_client,
                'type_of_job_id' => $request->type_of_job_id,
                'job_desc_id' => $request->job_desc_id,
                'part_no' => $request->part_no,
                'drg_no' => $request->drg_no,
                'material_id' => $request->material_id,
                'heat_no' => $request->heat_no,
                'product_code' => $request->product_code,
                'date_of_testing' => $testDate,
                'date_of_testing_value' => $request->date_of_testing_value,
                'test_carried_out_at' => $request->test_carried_out_at,
                'amendment_no' => $request->amendment_no,
                'amendment_date' => !empty($request->amendment_date) ? Carbon::createFromFormat('d/m/Y', $request->amendment_date)->format('Y-m-d') : null,
                'amendment_reason' => $request->amendment_reason,
                'stage_of_test' => $request->stage_of_test,
                'area_of_coverage_id' => $request->area_of_coverage_id,
                'surface_condition' => $request->surface_condition,
                'surface_temp' => $request->surface_temp,
                'thickness' => $request->thickness,
                'test_technique' => $request->test_technique,
                'type_of_magnetization' => $request->type_of_magnetization,
                'type_of_current' => $request->type_of_current,
                'prod_pole_spacing' => $request->prod_pole_spacing,
                'performance_verification' => $request->performance_verification,
                'lighting' => $request->lighting,
                'light_intensity' => $request->light_intensity,
                'background_light' => $request->background_light,
                'uva_light_intensity' => $request->uva_light_intensity,
                'yoke_wt_lift_check' => $request->yoke_wt_lift_check,
                'procedure_ref_id' => $request->procedure_ref_id,
                'acceptance_standard_id' => $request->acceptance_standard_id,
                'defectogram_image' => $defectogramPath,
                'defectogram_image_blob' => $blobImage,
                'ulr_id' => $ulr_id,
                'ulr_no' => $ulr_no,
                'ulr_sequence' => $ulr_sequence,
                'ulr_year' => $ulr_year,
                'tested_by_authority_person_id' => $request->tested_by_authority_person_id,
                'reviewed_by_authority_person_id' => $request->reviewed_by_authority_person_id,
                'authorized_by_authority_person_id' => $request->authorized_by_authority_person_id,
                'sp_note' => $request->sp_note,
                'note' => $request->note,
                'total_qty' => $request->total_qty ?? 0,
                'last_by' => $user_id,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
            ]);

            // Sync Equipment Details based on Mode
            $equipmentDetails = is_string($request->equipment_details_data) ? json_decode($request->equipment_details_data, true) : ($request->equipment_details ?? []);
            if (!empty($equipmentDetails)) {
                foreach ($equipmentDetails as $row) {
                    if (empty($row)) continue;
                    $mode = $row['mode'] ?? '';
                    $eqData = [
                        'test_report_mpt_id' => $id,
                        'em_id' => $row['em_id'],
                        'em_sr_no' => $row['em_sr_no'] ?? null,
                        'make' => $row['make'] ?? null,
                        'cal_due_date' => !empty($row['cal_due_date']) ? Carbon::createFromFormat('d/m/Y', $row['cal_due_date'])->format('Y-m-d') : null,
                    ];
                    if ($mode === 'Insert') {
                        TestReportMptEquipmentDetails::create($eqData);
                    } elseif ($mode === 'Update') {
                        if (!empty($row['test_report_mpt_equipment_details_id'])) {
                            TestReportMptEquipmentDetails::where('test_report_mpt_equipment_details_id', $row['test_report_mpt_equipment_details_id'])->update($eqData);
                        }
                    } elseif ($mode === 'Delete') {
                        if (!empty($row['test_report_mpt_equipment_details_id'])) {
                            TestReportMptEquipmentDetails::where('test_report_mpt_equipment_details_id', $row['test_report_mpt_equipment_details_id'])->delete();
                        }
                    }
                }
            }

            // Sync Material Details based on Mode
            $materialDetails = is_string($request->material_details_data) ? json_decode($request->material_details_data, true) : ($request->material_details ?? []);
            if (!empty($materialDetails)) {
                foreach ($materialDetails as $row) {
                    if (empty($row)) continue;
                    $mode = $row['mode'] ?? '';
                    $matData = [
                        'test_report_mpt_id' => $id,
                        'mm_id' => $row['mm_id'],
                        'batch_no' => $row['batch_no'] ?? null,
                        'make' => $row['make'] ?? null,
                        'expiry_date' => !empty($row['expiry_date']) ? Carbon::createFromFormat('d/m/Y', $row['expiry_date'])->format('Y-m-d') : null,
                    ];
                    if ($mode === 'Insert') {
                        TestReportMptMaterialDetails::create($matData);
                    } elseif ($mode === 'Update') {
                        if (!empty($row['test_report_mpt_material_details_id'])) {
                            TestReportMptMaterialDetails::where('test_report_mpt_material_details_id', $row['test_report_mpt_material_details_id'])->update($matData);
                        }
                    } elseif ($mode === 'Delete') {
                        if (!empty($row['test_report_mpt_material_details_id'])) {
                            TestReportMptMaterialDetails::where('test_report_mpt_material_details_id', $row['test_report_mpt_material_details_id'])->delete();
                        }
                    }
                }
            }

            // Sync Report Details based on Mode
            $reportDetails = is_string($request->report_details_data) ? json_decode($request->report_details_data, true) : ($request->details ?? []);
            if (!empty($reportDetails)) {
                foreach ($reportDetails as $row) {
                    if (empty($row)) continue;
                    $mode = $row['mode'] ?? '';
                    $detData = [
                        'test_report_mpt_id' => $id,
                        'mpt_test_no' => $row['mpt_test_no'],
                        'heat_no' => $row['heat_no'] ?? null,
                        'quantity' => $row['quantity'] ?? 1,
                        'discontinuity_evaluation' => $row['discontinuity_evaluation'] ?? null,
                        'result_id' => !empty($row['result_id']) ? $row['result_id'] : (!empty($row['detail_result_id']) ? $row['detail_result_id'] : null),
                    ];
                    if ($mode === 'Insert') {
                        TestReportMptDetails::create($detData);
                    } elseif ($mode === 'Update') {
                        if (!empty($row['test_report_mpt_details_id'])) {
                            TestReportMptDetails::where('test_report_mpt_details_id', $row['test_report_mpt_details_id'])->update($detData);
                        }
                    } elseif ($mode === 'Delete') {
                        if (!empty($row['test_report_mpt_details_id'])) {
                            TestReportMptDetails::where('test_report_mpt_details_id', $row['test_report_mpt_details_id'])->delete();
                        }
                    }
                }
            }

            $checkPending = $this->checkNegativePendingQty(
                $report->customer_id ?? $request->customer_id,
                $location_data->location_id,
                $fromTypeId,
                $report->material_inward_details_id ?? $request->material_inward_details_id,
                $request->observation_sheet_details_id ?? null,
                'U'
            );
            if ($checkPending) {
                return $checkPending;
            }

            DB::commit();

            // Regenerate Print PDF
            $report_number = !empty($request->test_report_no) ? '_' . str_replace('/', '_', $request->test_report_no) : "";
            $cust = Customer::where('id', $report->customer_id)->value('customer');
            // $cust = DB::table('customers')->where('id', $request->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Test_Report_MPT' . $report_number . $cust_name;

            if (function_exists('GeneratePdf')) {
                GeneratePdf($id, $pdf_name, 'test_report_mpt', 'update');
            }
            $encodedId = base64_encode($id);
            $url = hasAccess("test_report_mpt", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=test_report_mpt") : "";

            return response()->json([
                'response_code' => '1',
                'url' => $url,
                'response_message' => getResponseMessage('update_success'),
                'id' => $id
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
        $id = $request->id;

        DB::beginTransaction();
        try {
            $report = TestReportMpt::where('test_report_mpt_id', $request->id)->first();
            if ($report) {
                // Qty Validation
                $oldQty = (float)($report->total_qty ?? 0);
                $fromTypeId = $report->from_type_id_fix;
                $qtyCheck = $this->qtyValidation(0, $oldQty, 'D', $report->customer_id, $report->material_inward_details_id, $report->observation_sheet_details_id ?? null, $fromTypeId, $request->id);

                if ($qtyCheck) {
                    return $qtyCheck;
                }

            TestReportMptEquipmentDetails::where('test_report_mpt_id', $id)->delete();
            TestReportMptMaterialDetails::where('test_report_mpt_id', $id)->delete();
            TestReportMptDetails::where('test_report_mpt_id', $id)->delete();
            // TestReportMpt::where('test_report_mpt_id', $id)->delete();
            $report->delete();
            }

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

    public function checkNegativePendingQty($customer_id = 0, $location_id = 0, $from_type_id_fix = 1, $material_inward_details_id = 0, $observation_sheet_details_id = null, $transaction_mode = 'U')
    {
        $location_id = $location_id ?: getCurrentLocation()->location_id;
        $msg = ($transaction_mode === 'I')
            ? "You Can't Insert, Something Went Wrong."
            : "You Can't Update, Something Went Wrong.";

        if (!empty($material_inward_details_id)) {
            $query = DB::table('pending_material_inward_mpt_qty')
                ->where('customer_id', $customer_id)
                ->where('material_inward_details_id', $material_inward_details_id)
                ->where('from_type_id_fix', $from_type_id_fix)
                ->where('current_location_id', $location_id);

            if (!empty($observation_sheet_details_id) && $from_type_id_fix == 2) {
                $query->where('observation_sheet_details_id', $observation_sheet_details_id);
            } else {
                $query->where(function($q) {
                    $q->whereNull('observation_sheet_details_id')
                      ->orWhere('observation_sheet_details_id', 0);
                });
            }

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

    public function qtyValidation($from_qty, $next_qty, $transaction_mode, $customer_id, $material_inward_details_id, $observation_sheet_details_id = null, $from_type_id_fix = 1, $test_report_mpt_id = 0)
    {
        $location_id = getCurrentLocation()->location_id;

        if ($from_qty > 0) {
            if (!empty($observation_sheet_details_id) && $from_type_id_fix == 2) {
                ObservationSheetDetails::where('observation_sheet_details_id', $observation_sheet_details_id)
                    ->lockForUpdate()
                    ->first();
            } elseif (!empty($material_inward_details_id)) {
                MaterialInwardDetails::where('material_inward_details_id', $material_inward_details_id)
                    ->lockForUpdate()
                    ->first();
            }

            $query = DB::table('pending_material_inward_mpt_qty')
                ->where('material_inward_details_id', $material_inward_details_id)
                ->where('customer_id', $customer_id)
                ->where('from_type_id_fix', $from_type_id_fix)
                ->where('current_location_id', $location_id);

            if (!empty($observation_sheet_details_id) && $from_type_id_fix == 2) {
                $query->where('observation_sheet_details_id', $observation_sheet_details_id);
            } else {
                $query->where(function($q) {
                    $q->whereNull('observation_sheet_details_id')
                      ->orWhere('observation_sheet_details_id', 0);
                });
            }

            $pending_mpt_qty = (float)($query->value('pending_qty') ?? 0);

            if ($pending_mpt_qty < (float)$from_qty) {
                DB::rollBack();
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => 'MPT Report Qty. Is Used.',
                ]);
            }
        }
        return null; // No error
    }

    public function getPendingCustomersForMpt(Request $request)
    {
        $location = getCurrentLocation()->location_id;
        $year = getCompanyYearIdsToTill();

        $reportId = $request->input('report_id');
        $currentCustomerId = null;
        if ($reportId) {
            $currentCustomerId = DB::table('test_report_mpt')->where('test_report_mpt_id', $reportId)->value('customer_id');
        }

        $currentCustomer = null;
        if ($currentCustomerId) {
            $currentCustomer = DB::table('customers')
                ->where('id', $currentCustomerId)
                ->select('id', 'customer')
                ->first();
        }

        $customers = DB::table('pending_material_inward_mpt_qty as pend')
            ->join('customers as c', 'c.id', '=', 'pend.customer_id')
            ->join('material_inward_details as mid', 'mid.material_inward_details_id', '=', 'pend.material_inward_details_id')
            ->join('material_inward as mi', 'mi.material_inward_id', '=', 'mid.material_inward_id')
            ->where('pend.current_location_id', $location)
            ->whereIn('mi.year_id', $year)
            ->where('pend.pending_qty', '>', 0)
            ->select('c.id', 'c.customer')
            ->distinct()
            ->orderBy('c.customer', 'asc')
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

    public function getPendingCustomerData(Request $request)
    {
        $location = getCurrentLocation()->location_id;
        $year = getCompanyYearIdsToTill();

        $reportId = $request->input('report_id');
        $currentDetailsId = null;
        $oldQty = 0;
        if ($reportId) {
            $report = DB::table('test_report_mpt')->where('test_report_mpt_id', $reportId)->first();
            if ($report) {
                $currentDetailsId = $report->material_inward_details_id;
                $oldQty = $report->total_qty;
            }
        }

        $pending_data = DB::table('pending_material_inward_mpt_qty as pend')
            ->join('material_inward_details as mid', 'mid.material_inward_details_id', '=', 'pend.material_inward_details_id')
            ->join('material_inward as mi', 'mi.material_inward_id', '=', 'mid.material_inward_id')
            ->select([
                'pend.from_type_id_fix',
                'pend.material_inward_details_id',
                'pend.observation_sheet_details_id',
                'pend.pending_qty',
                'pend.current_location_id',
                'mid.type_of_job_id',
                'mid.job_desc_id',
                'mid.part_id',
                'mid.material_id',
                'mid.area_of_coverage_id',
                'mid.procedure_ref_id',
                'mid.evaluation_as_per_id',
                'mid.acceptance_standard_id',
                'mid.thickness',
                'mid.quantity as inward_qty',
                'pend.customer_id',
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
                'mid.product_code'
            ])
            ->leftJoin('type_of_job as toj', 'toj.id', '=', 'mid.type_of_job_id')
            ->leftJoin('job_descriptions as jd', 'jd.id', '=', 'mid.job_desc_id')
            ->leftJoin('materials as m', 'm.id', '=', 'mid.material_id')
            ->where('pend.customer_id', $request->customer_id)
            ->where('pend.current_location_id', $location)
            ->whereIn('mi.year_id', $year)
            ->where(function ($q) use ($currentDetailsId) {
                $q->where('pend.pending_qty', '>', 0);
                if ($currentDetailsId) {
                    $q->orWhere('pend.material_inward_details_id', $currentDetailsId);
                }
            })
            ->orderBy('mi.material_inward_id', 'desc')
            ->get();

        foreach ($pending_data as $row) {
            $row->dc_date = ($row->dc_date != "" && $row->dc_date != "0000-00-00") ? Date::createFromFormat('Y-m-d', $row->dc_date)->format('d/m/Y') : "";
            $row->po_date = ($row->po_date != "" && $row->po_date != "0000-00-00") ? Date::createFromFormat('Y-m-d', $row->po_date)->format('d/m/Y') : "";
            $row->material_inward_date = ($row->material_inward_date != "" && $row->material_inward_date != "0000-00-00") ? Date::createFromFormat('Y-m-d', $row->material_inward_date)->format('d/m/Y') : "";
        }

        return response()->json([
            'response_code' => 1,
            'pending_data' => $pending_data
        ]);
    }

    public function getCopyReportsList(Request $request)
    {
        $year = getCurrentYearData()->id;
        $location = getCurrentLocation()->location_id;

        $query = TestReportMpt::select([
            'test_report_mpt.test_report_mpt_id as id',
            'test_report_mpt.test_report_no',
            'test_report_mpt.test_report_date',
            'customers.customer',
            'test_report_mpt.nabl_type_fix',
            'test_report_mpt.job_type_fix',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            'test_report_mpt.part_no',
            'test_report_mpt.drg_no',
            'materials.material',
            'test_report_mpt.heat_no',
            'test_report_mpt.product_code'
        ])
        ->leftJoin('customers', 'customers.id', '=', 'test_report_mpt.customer_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'test_report_mpt.type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'test_report_mpt.job_desc_id')
        ->leftJoin('materials', 'materials.id', '=', 'test_report_mpt.material_id')
        ->where('test_report_mpt.year_id', $year)
        ->where('test_report_mpt.current_location_id', $location);

        if ($request->filled('type_of_job_id')) {
            $query->where('test_report_mpt.type_of_job_id', $request->type_of_job_id);
        }

        $reports = $query->orderBy('test_report_mpt.test_report_mpt_id', 'desc')->get();

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
        $report_data = DB::select('CALL test_report_mpt_master(?)', [$id]);
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
            if (isset($report_data->date_of_receipt)) {
                $report_data->date_of_receipt = ($report_data->date_of_receipt != "" && $report_data->date_of_receipt != "0000-00-00")
                    ? Date::createFromFormat('Y-m-d', $report_data->date_of_receipt)->format('d/m/Y') 
                    : "";
            }
            $report_data->date_of_testing = ($report_data->date_of_testing != "" && $report_data->date_of_testing != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $report_data->date_of_testing)->format('d/m/Y') 
                : "";
            $report_data->amendment_date = ($report_data->amendment_date != "" && $report_data->amendment_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $report_data->amendment_date)->format('d/m/Y') 
                : "";

            if (isset($report_data->cmp_logo)) {
                $report_data->cmp_logo = base64_encode($report_data->cmp_logo);
            }

            foreach ($report_data as $key => $value) {
                if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                    $report_data->$key = base64_encode($value);
                }
            }

            $equipment = DB::select('CALL test_report_mpt_equipment_details(?)', [$id]);
            foreach ($equipment as $row) {
                $row->cal_due_date = ($row->cal_due_date != "" && $row->cal_due_date != "0000-00-00")
                    ? Date::createFromFormat('Y-m-d', $row->cal_due_date)->format('d/m/Y')
                    : "";
                foreach ($row as $key => $value) {
                    if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                        $row->$key = base64_encode($value);
                    }
                }
            }

            $materials = DB::select('CALL test_report_mpt_material_details(?)', [$id]);
            foreach ($materials as $row) {
                $row->expiry_date = ($row->expiry_date != "" && $row->expiry_date != "0000-00-00")
                    ? Date::createFromFormat('Y-m-d', $row->expiry_date)->format('d/m/Y')
                    : "";
                foreach ($row as $key => $value) {
                    if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                        $row->$key = base64_encode($value);
                    }
                }
            }

            $details = DB::select('CALL test_report_mpt_details(?)', [$id]);
            foreach ($details as $row) {
                foreach ($row as $key => $value) {
                    if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                        $row->$key = base64_encode($value);
                    }
                }
            }

            return response()->json([
                'response_code' => 1,
                'report_data' => $report_data,
                'equipment_details_data' => $equipment,
                'material_details_data' => $materials,
                'report_details_data' => $details
            ]);
        }

        return response()->json([
            'response_code' => 0,
            'response_message' => 'Report Details Not Found.'
        ]);
    }

    // Suggestions autocomplete endpoints
    public function existsCustomerClient(Request $request) { return $this->existsSuggestion('test_report_mpt', 'customer_client', $request->term, 'customerClientList', 'customer_client'); }
    public function existsPartNo(Request $request) { return $this->existsSuggestion('test_report_mpt', 'part_no', $request->term, 'partNoList', 'part_no'); }
    public function existsDrgNo(Request $request) { return $this->existsSuggestion('test_report_mpt', 'drg_no', $request->term, 'drgNoList', 'drg_no'); }
    public function existsTestCarriedOutAt(Request $request) { return $this->existsSuggestion('test_report_mpt', 'test_carried_out_at', $request->term, 'testCarriedOutAtList', 'test_carried_out_at'); }
    public function existsStageOfTest(Request $request) { return $this->existsSuggestion('test_report_mpt', 'stage_of_test', $request->term, 'stageOfTestList', 'stage_of_test'); }
    public function existsSurfaceCondition(Request $request) { return $this->existsSuggestion('test_report_mpt', 'surface_condition', $request->term, 'surfaceConditionList', 'surface_condition'); }
    public function existsSurfaceTemp(Request $request) { return $this->existsSuggestion('test_report_mpt', 'surface_temp', $request->term, 'surfaceTempList', 'surface_temp'); }
    public function existsThickness(Request $request) { return $this->existsSuggestion('test_report_mpt', 'thickness', $request->term, 'thicknessList', 'thickness'); }
    public function existsTestTechnique(Request $request) { return $this->existsSuggestion('test_report_mpt', 'test_technique', $request->term, 'testTechniqueList', 'test_technique'); }
    public function existsTypeOfMagnetization(Request $request) { return $this->existsSuggestion('test_report_mpt', 'type_of_magnetization', $request->term, 'typeOfMagnetizationList', 'type_of_magnetization'); }
    public function existsTypeOfCurrent(Request $request) { return $this->existsSuggestion('test_report_mpt', 'type_of_current', $request->term, 'typeOfCurrentList', 'type_of_current'); }
    public function existsProdPoleSpacing(Request $request) { return $this->existsSuggestion('test_report_mpt', 'prod_pole_spacing', $request->term, 'prodPoleSpacingList', 'prod_pole_spacing'); }
    public function existsPerformanceVerification(Request $request) { return $this->existsSuggestion('test_report_mpt', 'performance_verification', $request->term, 'performanceVerificationList', 'performance_verification'); }
    public function existsLighting(Request $request) { return $this->existsSuggestion('test_report_mpt', 'lighting', $request->term, 'lightingList', 'lighting'); }
    public function existsLightIntensity(Request $request) { return $this->existsSuggestion('test_report_mpt', 'light_intensity', $request->term, 'lightIntensityList', 'light_intensity'); }
    public function existsBackgroundLight(Request $request) { return $this->existsSuggestion('test_report_mpt', 'background_light', $request->term, 'backgroundLightList', 'background_light'); }
    public function existsUvaLightIntensity(Request $request) { return $this->existsSuggestion('test_report_mpt', 'uva_light_intensity', $request->term, 'uvaLightIntensityList', 'uva_light_intensity'); }
    public function existsYokeWtLiftCheck(Request $request) { return $this->existsSuggestion('test_report_mpt', 'yoke_wt_lift_check', $request->term, 'yokeWtLiftCheckList', 'yoke_wt_lift_check'); }
    public function existsDiscontinuityEvaluation(Request $request) { return $this->existsSuggestion('test_report_mpt_details', 'discontinuity_evaluation', $request->term, 'discontinuityEvaluationList', 'det_discontinuity_evaluation'); }

    public function existsProductCode(Request $request)
    {
        return $this->existsSuggestion('test_report_mpt', 'product_code', $request->term, 'productCodeList', 'product_code');
    }

    private function existsSuggestion($table, $column, $term, $listId, $parentId)
    {
        if ($term != "") {
            $location_data = getCurrentLocation();
            $location_id = $location_data ? $location_data->location_id : null;

            $query = DB::table($table)
                ->where($column, 'LIKE', $term . '%')
                ->whereNotNull($column)
                ->where($column, '!=', '');

            if ($table === 'test_report_mpt') {
                if ($location_id) {
                    $query->where('current_location_id', $location_id);
                }
                $query->select($column)
                    ->groupBy($column)
                    ->orderBy($column, 'asc');
            } elseif ($table === 'test_report_mpt_details') {
                if ($location_id) {
                    $query->join('test_report_mpt', 'test_report_mpt.test_report_mpt_id', '=', 'test_report_mpt_details.test_report_mpt_id')
                        ->where('test_report_mpt.current_location_id', $location_id);
                }
                $query->select('test_report_mpt_details.' . $column)
                    ->groupBy('test_report_mpt_details.' . $column)
                    ->orderBy('test_report_mpt_details.' . $column, 'asc');
            }

            $data = $query->get();
            if ($data->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($data as $row) {
                    if (trim($row->$column) === '') continue;
                    $output .= '<li parent-id="' . $parentId . '" list-id="' . $parentId . '_list" class="list-group-item" tabindex="0">' . e($row->$column) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    $listId => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            $listId => '',
            'response_code' => 1,
        ]);
    }

    public function getLastReportMptDetails()
    {
        $location_data = getCurrentLocation();
        $location_id = $location_data ? $location_data->location_id : null;

        $lastReport = TestReportMpt::where('current_location_id', $location_id)
            ->orderBy('test_report_mpt_id', 'desc')
            ->first();

        $equipment = [];
        $materials = [];

        if ($lastReport) {
            $lastEquipment = DB::table('test_report_mpt_equipment_details')
                ->join('equipment_mpt', 'equipment_mpt.em_id', '=', 'test_report_mpt_equipment_details.em_id')
                ->where('test_report_mpt_equipment_details.test_report_mpt_id', $lastReport->test_report_mpt_id)
                ->where('equipment_mpt.em_status', 'Active')
                ->select('test_report_mpt_equipment_details.*', 'equipment_mpt.em_equipment_name', 'equipment_mpt.em_status')
                ->get();

            foreach ($lastEquipment as $row) {
                $equipment[] = [
                    'test_report_mpt_equipment_details_id' => 0,
                    'detail_em_id' => $row->em_id,
                    'em_id' => $row->em_id,
                    'em_equipment_name' => $row->em_equipment_name,
                    'equipment_name' => $row->em_equipment_name,
                    'make' => $row->make,
                    'display' => $row->em_equipment_name,
                    'em_sr_no' => $row->em_sr_no,
                    'cal_due_date' => ($row->cal_due_date != "" && $row->cal_due_date != "0000-00-00")
                        ? Date::createFromFormat('Y-m-d', $row->cal_due_date)->format('d/m/Y')
                        : "",
                ];
            }

            $lastMaterials = DB::table('test_report_mpt_material_details')
                ->join('material_mpt', 'material_mpt.mm_id', '=', 'test_report_mpt_material_details.mm_id')
                ->where('test_report_mpt_material_details.test_report_mpt_id', $lastReport->test_report_mpt_id)
                ->where('material_mpt.mm_status', 'Active')
                ->select('test_report_mpt_material_details.*', 'material_mpt.mm_material', 'material_mpt.mm_status')
                ->get();

            foreach ($lastMaterials as $row) {
                $materials[] = [
                    'test_report_mpt_material_details_id' => 0,
                    'detail_mm_id' => $row->mm_id,
                    'mm_id' => $row->mm_id,
                    'mm_material' => $row->mm_material,
                    'material_name' => $row->mm_material,
                    'make' => $row->make,
                    'batch_no' => $row->batch_no,
                    'expiry_date' => ($row->expiry_date != "" && $row->expiry_date != "0000-00-00")
                        ? Date::createFromFormat('Y-m-d', $row->expiry_date)->format('d/m/Y')
                        : "",
                ];
            }
        }

        return response()->json([
            'response_code' => 1,
            'equipment_details_data' => $equipment,
            'material_details_data' => $materials,
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

        $isFound = getLatestUlrSequence(
            $request->ulr_id,
            $year,
            $location_id,
            $request->has('ulr_sequence') && $request->ulr_sequence !== null && $request->ulr_sequence !== '' ? $request->ulr_sequence : null
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
            TestReportMpt::class,
            $id
        );

        return response()->json([
            'response_code' => $isDuplicate ? 0 : 1,
            'response_message' => $isDuplicate ? 'Duplicate ULR No. Found.' : 'Available.'
        ]);
    }

    public function testReportMptLNRData()
    {
        $location_data = getCurrentLocation();
        $year_data = getCurrentYearData();

        $lnr_data = TestReportMpt::select([
            'test_report_mpt_id',
            'note'
        ])
        ->where('current_location_id', $location_data->location_id)
        ->orderBy('test_report_mpt_id', 'desc')
        ->first();

        $note = 'The above Results are related to the items tested only. Any Manual corrections in this report invalidates the report. This report shall not be reproduced
except in full, without written approval of Ultratech ENGINEERS PRIVATE LIMITED.
Disclaimer : *Marked informations as given by customer that can affects the validity of the test results.';

        if ($lnr_data) {
            $note = $lnr_data->note ?? '';
        }

        return response()->json([
            'response_code' => 1,
            'lnr_data'      => [
                'note' => $note
            ]
        ]);
    }
}



