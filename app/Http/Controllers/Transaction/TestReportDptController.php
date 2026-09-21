<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Location;
use App\Models\NABLConfiguration;
use App\Models\Transaction\TestReportDpt;
use App\Models\Transaction\TestReportDptChemicalDetails;
use App\Models\Transaction\TestReportDptDetails;
use App\Models\File;
use App\Models\Transaction\MaterialInwardDetails;
use App\Models\Transaction\ObservationSheetDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DateTime;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class TestReportDptController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-test_report_dpt');
    }

    public function index(Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();

        $reports = TestReportDpt::select([
            'test_report_dpt.test_report_dpt_id as id',
            'test_report_dpt.test_report_no',
            'test_report_dpt.test_report_sequence',
            'test_report_dpt.test_report_date',
            'test_report_dpt.nabl_type_fix',
            'test_report_dpt.ulr_no',
            'test_report_dpt.test_carried_out_at',
            'test_report_dpt.job_type_fix',
            'customers.customer',
            'material_inward.dc_no',
            'material_inward.dc_date',
            'material_inward.po_no',
            'material_inward.po_date',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            'test_report_dpt.part_no',
            'test_report_dpt.drg_no',
            'materials.material',
            'test_report_dpt.product_code',
            'test_report_dpt.total_qty',
            'test_report_dpt.created_on',
            'test_report_dpt.created_by',
            'test_report_dpt.last_by',
            'test_report_dpt.last_on'
        ])
        ->leftJoin('customers', 'customers.id', '=', 'test_report_dpt.customer_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'test_report_dpt.type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'test_report_dpt.job_desc_id')
        ->leftJoin('materials', 'materials.id', '=', 'test_report_dpt.material_id')
        ->leftJoin('material_inward_details', 'material_inward_details.material_inward_details_id', '=', 'test_report_dpt.material_inward_details_id')
        ->leftJoin('material_inward', 'material_inward.material_inward_id', '=', 'material_inward_details.material_inward_id')
        ->where('test_report_dpt.year_id', $year_data->id)
        ->where('test_report_dpt.current_location_id', $location_data->location_id);

        $dataTable = DataTables::of($reports)
        ->filterColumn('test_report_dpt.test_report_no', function($query, $keyword) {
            applySequenceSearch($query, $keyword, 'test_report_dpt.test_report_no');
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
        ->filterColumn('test_report_dpt.test_report_date', function ($q, $k) {
            applyDate($q, $k, 'test_report_dpt.test_report_date');
        })
        ->filterColumn('material_inward.dc_date', function ($q, $k) {
            applyDate($q, $k, 'material_inward.dc_date');
        })
        ->filterColumn('material_inward.po_date', function ($q, $k) {
            applyDate($q, $k, 'material_inward.po_date');
        })
        ->filterColumn('part_no', function($query, $keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('test_report_dpt.part_no', 'like', "%{$keyword}%");
            });
        })
        ->filterColumn('drg_no', function($query, $keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('test_report_dpt.drg_no', 'like', "%{$keyword}%");
            });
        })
        ->addColumn('options', function($report) {
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("test_report_dpt", "print")) {
                    $report_number = !empty($report->test_report_no) ? '_' . str_replace('/', '_', $report->test_report_no) : "";
                    $cust_name = !empty($report->customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $report->customer) : "";
                    $pdfName   = 'Test_Report_DPT' . $report_number . $cust_name;
                    $encodedId = base64_encode($report->id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=test_report_dpt");
                    $action .= '<li><a class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }

                if (hasAccess("test_report_dpt", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-test_report_dpt"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("test_report_dpt", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });

        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'test_report_dpt');
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
            'material_id' => 'required',
            'area_of_coverage_id' => 'required',
        ]);

        $pendingQty = DB::table('pending_material_inward_dpt_qty')
            ->where('material_inward_details_id', $request->material_inward_details_id)
            ->value('pending_qty') ?? 0;

        if ($request->total_qty > $pendingQty) {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'DPT Report Qty. Is Used.',
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
        $chemicals = json_decode($request->chemical_details, true);
        /*
        if (!empty($chemicals)) {
            foreach ($chemicals as $row) {
                if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;
                $chem_status = DB::table('dpt_chemical')->where('dpt_id', $row['dpt_id'])->value('dpt_status');
                if ($chem_status !== 'Active') {
                    return response()->json([
                        'response_code' => '0',
                        'response_message' => 'Chemical is not active.'
                    ]);
                }
            }
        }
        */

        DB::beginTransaction();
        try {
            $fromTypeId = $request->from_type_id_fix;
            $qtyCheck = $this->qtyValidation($request->total_qty ?? 0, 0, 'I', $request->customer_id, $request->material_inward_details_id, $request->observation_sheet_details_id ?? null, $fromTypeId, 0);
            if ($qtyCheck) {
                return $qtyCheck;
            }

            $existNumber = TestReportDpt::where([
                ['test_report_sequence', $request->test_report_sequence],
                ['test_report_no', $request->test_report_no],
                ['year_id', $year_data->id],
                ['current_location_id', $location_data->location_id]
            ])->first();

            if ($existNumber) {
                $latestNo = $this->getLatestTestReportDptNumber($request);
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
                    $isFound = $file->getFileFromTemp($request->defectogram_image_doc, 'test_report_dpt');
                } else {
                    $isFound = $file->getFileCopyFormUpload($request->defectogram_image_doc, 'test_report_dpt');
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
            $ulr_no = '';
            $ulr_sequence = 0;
            $ulr_year = 0;

            if ($request->nabl_type_fix === 'NABL') {
                $ulr_id = $request->ulr_id;
                
                $dateVal = Carbon::createFromFormat('d/m/Y', $request->test_report_date)->format('Y-m-d');
                $DateObj = new DateTime($dateVal);
                $ulr_year = $DateObj->format("Y");

                if (isDuplicateUlrSequence($request->ulr_sequence, $ulr_id, $ulr_year, $location_data->location_id)) {
                    return response()->json([
                        'response_code' => 0,
                        'response_message' => 'Duplicate ULR No. Found.'
                    ]);
                }
                $ulr_no = $request->ulr_no;
                $ulr_sequence = $request->ulr_sequence;
            }
            $page_id = getMenuIdBassedOnDisplayName('test_report_dpt');
            $assign_format_no = $page_id ? getAssignFormateNoForTransaction($location_data->location_id, $page_id->id, $request->test_report_date) : '';

            $report = TestReportDpt::create([
                'test_report_sequence' => $report_seq,
                'test_report_no' => $report_no,
                'test_report_date' => isset($request->test_report_date) ? Date::createFromFormat('d/m/Y', $request->test_report_date)->format('Y-m-d') : null,
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
                'date_of_testing' => !empty($request->date_of_testing) ? Date::createFromFormat('d/m/Y', $request->date_of_testing)->format('Y-m-d') : null,
                'date_of_testing_value' => $request->date_of_testing_value,
                'test_carried_out_at' => $request->test_carried_out_at,
                'amendment_no' => $request->amendment_no,
                'amendment_date' => !empty($request->amendment_date) ? Date::createFromFormat('d/m/Y', $request->amendment_date)->format('Y-m-d') : null,
                'amendment_reason' => $request->amendment_reason,
                'stage_of_test' => $request->stage_of_test,
                'area_of_coverage_id' => $request->area_of_coverage_id,
                
                'surface_condition' => $request->surface_condition,
                'surface_temp' => $request->surface_temp,
                'thickness' => $request->thickness,
                'test_technique' => $request->test_technique,
                'pre_cleaning' => $request->pre_cleaning,
                'penetrant_application' => $request->penetrant_application,
                'dwell_time' => $request->dwell_time,
                'penetrant_remover' => $request->penetrant_remover,
                'developer' => $request->developer,
                'developer_application' => $request->developer_application,
                'developing_time' => $request->developing_time,
                'lighting' => $request->lighting,
                'light_intensity' => $request->light_intensity,
                'background_light' => $request->background_light,
                'uva_light_intensity' => $request->uva_light_intensity,
                
                'procedure_ref_id' => $request->procedure_ref_id,
                'acceptance_standard_id' => $request->acceptance_standard_id,
                'defectogram_image' => $defectogramPath,
                'defectogram_image_blob' => $blobImage,
                'total_qty' => $request->total_qty ?? 0,
                'ulr_id' => $ulr_id,
                'ulr_no' => $ulr_no,
                'ulr_sequence' => $ulr_sequence,
                'ulr_year' => $ulr_year,
                'tested_by_authority_person_id' => $request->tested_by_authority_person_id,
                'reviewed_by_authority_person_id' => $request->reviewed_by_authority_person_id,
                'authorized_by_authority_person_id' => $request->authorized_by_authority_person_id,
                'sp_note' => $request->sp_note,
                'note' => $request->note,
                'assign_format_no'      => $assign_format_no,
                'company_id' => Auth::user()->company_id,
                'year_id' => $year_data->id,
                'current_location_id' => $location_data->location_id,
                'created_by' => Auth::id(),
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
            ]);

            // Grid 1: Chemical Details
            $chemicals = json_decode($request->chemical_details, true);
            if (!empty($chemicals)) {
                foreach ($chemicals as $row) {
                    if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;
                    TestReportDptChemicalDetails::create([
                        'test_report_dpt_id' => $report->test_report_dpt_id,
                        'dpt_id' => $row['dpt_id'],
                        //'chemical_designation' => $row['chemical_designation'],
                        'make' => $row['make'],
                        'batch_no' => $row['batch_no'],
                        'expiry_date' => !empty($row['expiry_date']) ? Date::createFromFormat('d/m/Y', $row['expiry_date'])->format('Y-m-d') : null,
                    ]);
                }
            }

            // Grid 2: Test Details
            $details = json_decode($request->report_details_data, true);
            if (!empty($details)) {
                foreach ($details as $row) {
                    if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;
                    TestReportDptDetails::create([
                        'test_report_dpt_id' => $report->test_report_dpt_id,
                        'dpt_test_no' => $row['dpt_test_no'],
                        'heat_no' => $row['heat_no'],
                        'quantity' => $row['quantity'],
                        'discontinuity_evaluation' => $row['discontinuity_evaluation'],
                        'result_id' => !empty($row['detail_result_id']) ? $row['detail_result_id'] : null,
                    ]);
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
            $pdf_name = 'Test_Report_DPT' . $report_number . $cust_name;

            if (function_exists('GeneratePdf')) {
                GeneratePdf($report->test_report_dpt_id, $pdf_name, 'test_report_dpt', 'add');
            }
            $encodedId = base64_encode($report->test_report_dpt_id);
            $url = hasAccess("test_report_dpt", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=test_report_dpt") : "";

            return response()->json([
                'response_code' => '1',
                'url' => $url,
                'response_message' => getResponseMessage('store_success'),
                'id' => $report->test_report_dpt_id
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
        $report_data = DB::select('CALL test_report_dpt_master(?)', [$request->id]);
        if (!empty($report_data)) {
            $report_data = $report_data[0];

            $report_number = !empty($report_data->test_report_no) ? '_' . str_replace('/', '_', $report_data->test_report_no) : "";
            $cust = Customer::where('id', $report_data->customer_id)->value('customer');
            //$cust = DB::table('customers')->where('id', $report_data->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $report_data->pdf_name = 'Test_Report_DPT' . $report_number . $cust_name;

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

            $chemicals = DB::select('CALL test_report_dpt_chemical_details(?)', [$request->id]);
            foreach ($chemicals as $row) {
                $row->expiry_date = ($row->expiry_date != "" && $row->expiry_date != "0000-00-00")
                    ? Date::createFromFormat('Y-m-d', $row->expiry_date)->format('d/m/Y')
                    : "";
            }

            $details = DB::select('CALL test_report_dpt_details(?)', [$request->id]);

            // Get current pending qty for this inward detail & from_type_id_fix (so edit mode can display it)
            $fromTypeId = $report_data->from_type_id_fix;
            $pendQuery = DB::table('pending_material_inward_dpt_qty')
                ->where('material_inward_details_id', $report_data->material_inward_details_id)
                ->where('from_type_id_fix', $fromTypeId);
            if (!empty($report_data->observation_sheet_details_id) && $fromTypeId == 2) {
                $pendQuery->where('observation_sheet_details_id', $report_data->observation_sheet_details_id);
            }
            $pend_qty = $pendQuery->value('pending_qty') ?? 0;
            // Add back the already-used qty
            $pend_qty = $pend_qty + ($report_data->total_qty ?? 0);

            return response()->json([
                'response_code' => 1,
                'report_data' => $report_data,
                'pend_qty' => $pend_qty,
                'chemical_details_data' => $chemicals,
                'report_details_data' => $details,
            ]);
        }

        return response()->json([
            'response_code' => 0,
            'response_message' => 'Record Not Found',
        ]);
    }

    public function update(Request $request)
    {
        $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();
        $request->validate([
            'id' => 'required',
            'test_report_sequence' => 'required',
            'test_report_date' => 'required',
            'customer_id' => 'required',
            'type_of_job_id' => 'required',
            'job_desc_id' => 'required',
            'material_id' => 'required',
            'area_of_coverage_id' => 'required',
        ]);

        $report = TestReportDpt::where('test_report_dpt_id', $request->id)->first();
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
        $chemicals = json_decode($request->chemical_details, true);
        /*
        if (!empty($chemicals)) {
            foreach ($chemicals as $row) {
                if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;
                if (($row['mode'] ?? '') === 'Insert') {
                    $chem_status = DB::table('dpt_chemical')->where('dpt_id', $row['dpt_id'])->value('dpt_status');
                    if ($chem_status !== 'Active') {
                        return response()->json([
                            'response_code' => '0',
                            'response_message' => 'Chemical is not active.'
                        ]);
                    }
                }
            }
        }
        */

        DB::beginTransaction();
        
        try {

            $oldQty = (float)($report->total_qty ?? 0);
            $diff = (float)($request->total_qty ?? 0) - $oldQty;
            $from_qty = $diff > 0 ? $diff : 0;
            $next_qty = $diff < 0 ? abs($diff) : 0;

            $fromTypeId = $request->from_type_id_fix;
            $qtyCheck = $this->qtyValidation($from_qty, $next_qty, 'U', $request->customer_id, $report->material_inward_details_id, $report->observation_sheet_details_id ?? null, $fromTypeId, $request->id);
            if ($qtyCheck) {
                return $qtyCheck;
            }
            $file = new File();
            $defectogramPath = $report->defectogram_image;
            $blobImage = $report->defectogram_image_blob;

            if ($request->filled('defectogram_image_doc') && $request->defectogram_image_doc != '') {
                if ($defectogramPath && $defectogramPath != $request->defectogram_image_doc) {
                    $file->delete_file($defectogramPath);
                }
                if (str_contains($request->defectogram_image_doc, 'temp_media/')) {
                    $isFound = $file->getFileFromTemp($request->defectogram_image_doc, 'test_report_dpt');
                } else {
                    $isFound = $file->getFileCopyFormUpload($request->defectogram_image_doc, 'test_report_dpt');
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

            $ulr_id = null;
            $ulr_no = '';
            $ulr_sequence = 0;
            $ulr_year = 0;

            if ($request->nabl_type_fix === 'NABL') {
                $ulr_id = $request->ulr_id;
                
                $dateVal = Carbon::createFromFormat('d/m/Y', $request->test_report_date)->format('Y-m-d');
                $DateObj = new \DateTime($dateVal);
                $ulr_year = $DateObj->format("Y");

                if (isDuplicateUlrSequence($request->ulr_sequence, $ulr_id, $ulr_year, $location_data->location_id, TestReportDpt::class, $request->id)) {
                    return response()->json([
                        'response_code' => 0,
                        'response_message' => 'Duplicate ULR No. Found.'
                    ]);
                }
                $ulr_no = $request->ulr_no;
                $ulr_sequence = $request->ulr_sequence;
            }

            $page_id = getMenuIdBassedOnDisplayName('test_report_dpt');
            $assign_format_no = $page_id ? getAssignFormateNoForTransaction($location_data->location_id, $page_id->id, $request->test_report_date) : '';

            $report->update([
                'test_report_sequence' => $request->test_report_sequence,
                'test_report_no' => $request->test_report_no,
                'test_report_date' => isset($request->test_report_date) ? Date::createFromFormat('d/m/Y', $request->test_report_date)->format('Y-m-d') : null,
                'customer_id' => $request->customer_id,
                'material_inward_details_id' => $report->material_inward_details_id,
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
                'date_of_testing' => !empty($request->date_of_testing) ? Date::createFromFormat('d/m/Y', $request->date_of_testing)->format('Y-m-d') : null,
                'date_of_testing_value' => $request->date_of_testing_value,
                'test_carried_out_at' => $request->test_carried_out_at,
                'amendment_no' => $request->amendment_no,
                'amendment_date' => !empty($request->amendment_date) ? Date::createFromFormat('d/m/Y', $request->amendment_date)->format('Y-m-d') : null,
                'amendment_reason' => $request->amendment_reason,
                'stage_of_test' => $request->stage_of_test,
                'area_of_coverage_id' => $request->area_of_coverage_id,
                
                'surface_condition' => $request->surface_condition,
                'surface_temp' => $request->surface_temp,
                'thickness' => $request->thickness,
                'test_technique' => $request->test_technique,
                'pre_cleaning' => $request->pre_cleaning,
                'penetrant_application' => $request->penetrant_application,
                'dwell_time' => $request->dwell_time,
                'penetrant_remover' => $request->penetrant_remover,
                'developer' => $request->developer,
                'developer_application' => $request->developer_application,
                'developing_time' => $request->developing_time,
                'lighting' => $request->lighting,
                'light_intensity' => $request->light_intensity,
                'background_light' => $request->background_light,
                'uva_light_intensity' => $request->uva_light_intensity,
                
                'procedure_ref_id' => $request->procedure_ref_id,
                'acceptance_standard_id' => $request->acceptance_standard_id,
                'defectogram_image' => $defectogramPath,
                'defectogram_image_blob' => $blobImage,
                'total_qty' => $request->total_qty ?? 0,
                // 'assign_format_no' => $assign_format_no,
                'ulr_id' => $ulr_id,
                'ulr_no' => $ulr_no,
                'ulr_sequence' => $ulr_sequence,
                'ulr_year' => $ulr_year,
                'tested_by_authority_person_id' => $request->tested_by_authority_person_id,
                'reviewed_by_authority_person_id' => $request->reviewed_by_authority_person_id,
                'authorized_by_authority_person_id' => $request->authorized_by_authority_person_id,
                'sp_note' => $request->sp_note,
                'note' => $request->note,
                'last_by' => Auth::id(),
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
            ]);

            // Grid 1: Chemical Details Update
            $chemicals = json_decode($request->chemical_details, true);
            if (!empty($chemicals)) {
                foreach ($chemicals as $row) {
                    if (empty($row)) continue;
                    $mode = $row['mode'] ?? '';

                    if ($mode === 'Insert') {
                        TestReportDptChemicalDetails::create([
                            'test_report_dpt_id' => $report->test_report_dpt_id,
                            'dpt_id' => $row['dpt_id'],
                            //'chemical_designation' => $row['chemical_designation'],
                            'make' => $row['make'],
                            'batch_no' => $row['batch_no'],
                            'expiry_date' => !empty($row['expiry_date']) ? Date::createFromFormat('d/m/Y', $row['expiry_date'])->format('Y-m-d') : null,
                        ]);
                    } elseif ($mode === 'Update') {
                        if (!empty($row['test_report_dpt_chemical_details_id'])) {
                            TestReportDptChemicalDetails::where('test_report_dpt_chemical_details_id', $row['test_report_dpt_chemical_details_id'])->update([
                                'dpt_id' => $row['dpt_id'],
                                //'chemical_designation' => $row['chemical_designation'],
                                'make' => $row['make'],
                                'batch_no' => $row['batch_no'],
                                'expiry_date' => !empty($row['expiry_date']) ? Date::createFromFormat('d/m/Y', $row['expiry_date'])->format('Y-m-d') : null,
                            ]);
                        }
                    } elseif ($mode === 'Delete') {
                        if (!empty($row['test_report_dpt_chemical_details_id'])) {
                            TestReportDptChemicalDetails::where('test_report_dpt_chemical_details_id', $row['test_report_dpt_chemical_details_id'])->delete();
                        }
                    }
                }
            }

            // Grid 2: Test Details Update
            $details = json_decode($request->report_details_data, true);
            if (!empty($details)) {
                foreach ($details as $row) {
                    if (empty($row)) continue;
                    $mode = $row['mode'] ?? '';

                    if ($mode === 'Insert') {
                        TestReportDptDetails::create([
                            'test_report_dpt_id' => $report->test_report_dpt_id,
                            'dpt_test_no' => $row['dpt_test_no'],
                            'heat_no' => $row['heat_no'],
                            'quantity' => $row['quantity'],
                            'discontinuity_evaluation' => $row['discontinuity_evaluation'],
                            'result_id' => !empty($row['detail_result_id']) ? $row['detail_result_id'] : null,
                        ]);
                    } elseif ($mode === 'Update') {
                        if (!empty($row['test_report_dpt_details_id'])) {
                            TestReportDptDetails::where('test_report_dpt_details_id', $row['test_report_dpt_details_id'])->update([
                                'dpt_test_no' => $row['dpt_test_no'],
                                'heat_no' => $row['heat_no'],
                                'quantity' => $row['quantity'],
                                'discontinuity_evaluation' => $row['discontinuity_evaluation'],
                                'result_id' => !empty($row['detail_result_id']) ? $row['detail_result_id'] : null,
                            ]);
                        }
                    } elseif ($mode === 'Delete') {
                        if (!empty($row['test_report_dpt_details_id'])) {
                            TestReportDptDetails::where('test_report_dpt_details_id', $row['test_report_dpt_details_id'])->delete();
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
            $pdf_name = 'Test_Report_DPT' . $report_number . $cust_name;

            if (function_exists('GeneratePdf')) {
                GeneratePdf($request->id, $pdf_name, 'test_report_dpt', 'update');
            }
            $encodedId = base64_encode($request->id);
            $url = hasAccess("test_report_dpt", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=test_report_dpt") : "";

            return response()->json([
                'response_code' => '1',
                'url' => $url,
                'response_message' => getResponseMessage('update_success'),
                'id' => $request->id
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
            $report = TestReportDpt::where('test_report_dpt_id', $request->id)->first();
            if ($report) {

                // Qty Validation
                $oldQty = (float)($report->total_qty ?? 0);
                $fromTypeId = $report->from_type_id_fix;
                $qtyCheck = $this->qtyValidation(0, $oldQty, 'D', $report->customer_id, $report->material_inward_details_id, $report->observation_sheet_details_id ?? null, $fromTypeId, $request->id);

                if ($qtyCheck) {
                    return $qtyCheck;
                }
                if ($report->defectogram_image) {
                    $file = new File();
                    $file->delete_file($report->defectogram_image);
                }
                TestReportDptChemicalDetails::where('test_report_dpt_id', $request->id)->delete();
                TestReportDptDetails::where('test_report_dpt_id', $request->id)->delete();
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
            $query = DB::table('pending_material_inward_dpt_qty')
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

    public function qtyValidation($from_qty, $next_qty, $transaction_mode, $customer_id, $material_inward_details_id, $observation_sheet_details_id = null, $from_type_id_fix = 1, $test_report_dpt_id = 0)
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

            $query = DB::table('pending_material_inward_dpt_qty')
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

            $pending_dpt_qty = (float)($query->value('pending_qty') ?? 0);

            if ($pending_dpt_qty < (float)$from_qty) {
                DB::rollBack();
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => 'DPT Report Qty. Is Used.',
                ]);
            }
        }
        return null; // No error
    }

    public function getLatestTestReportDptNumber(Request $request)
    {
        $modal = TestReportDpt::class;
        $sequence = 'test_report_sequence';
        $prefix = 'DPT';

        $num_format = getLatestSequence($modal, $sequence, $prefix);

        return response()->json([
            'response_code' => 1,
            'latest_no' => $num_format['format'],
            'number' => $num_format['isFound'],
        ]);
    }

    public function getLastReportDptDetails()
    {
        $location = getCurrentLocation()->location_id;
        $lastReport = TestReportDpt::where('current_location_id', $location)
            ->orderBy('test_report_dpt_id', 'desc')
            ->first();

        $chemicals = [];
        if ($lastReport) {
            $chemicals = DB::table('test_report_dpt_chemical_details as trcd')
                ->join('dpt_chemical as dc', 'dc.dpt_id', '=', 'trcd.dpt_id')
                ->where('trcd.test_report_dpt_id', $lastReport->test_report_dpt_id)
                ->where('dc.current_location_id', $location)
                ->where('dc.dpt_status', 'Active')
                ->select([
                    'trcd.dpt_id',
                    'dc.dpt_chemical',
                    //'trcd.chemical_designation',
                    'trcd.make',
                    'trcd.batch_no',
                    DB::raw("DATE_FORMAT(trcd.expiry_date, '%d/%m/%Y') as expiry_date")
                ])
                ->get();
        }

        return response()->json([
            'response_code' => 1,
            'chemical_details' => $chemicals
        ]);
    }

    public function getPendingCustomerData(Request $request)
    {
        try {
            $location = getCurrentLocation()->location_id;
            $year = getCompanyYearIdsToTill();

            $reportId = $request->input('report_id');
            $currentDetailsId = null;
            if ($reportId) {
                $report = DB::table('test_report_dpt')->where('test_report_dpt_id', $reportId)->first();
                if ($report) {
                    $currentDetailsId = $report->material_inward_details_id;
                }
            }

            $query = DB::table('pending_material_inward_dpt_qty as pend')
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
                    'mid.product_code',
                    'ac.area_of_coverage',
                    'pr.procedure_reference',
                    'as_std.acceptance_standard'
                ])
                ->leftJoin('material_inward_details as mid', 'mid.material_inward_details_id', '=', 'pend.material_inward_details_id')
                ->leftJoin('material_inward as mi', 'mi.material_inward_id', '=', 'mid.material_inward_id')
                ->leftJoin('type_of_job as toj', 'toj.id', '=', 'mid.type_of_job_id')
                ->leftJoin('job_descriptions as jd', 'jd.id', '=', 'mid.job_desc_id')
                ->leftJoin('part as p', 'p.part_id', '=', 'mid.part_id')
                ->leftJoin('materials as m', 'm.id', '=', 'mid.material_id')
                ->leftJoin('area_of_coverage as ac', 'ac.area_of_coverage_id', '=', 'mid.area_of_coverage_id')
                ->leftJoin('procedure_reference as pr', 'pr.procedure_reference_id', '=', 'mid.procedure_ref_id')
                ->leftJoin('acceptance_standards as as_std', 'as_std.id', '=', 'mid.acceptance_standard_id')
                ->leftJoin('observation_sheet_details as osd', 'osd.observation_sheet_details_id', '=', 'pend.observation_sheet_details_id')
                ->leftJoin('observation_sheet as os', 'os.observation_sheet_id', '=', 'osd.observation_sheet_id')
                ->where('pend.customer_id', $request->customer_id)
                ->where('pend.current_location_id', $location)
                ->whereIn('mi.year_id', $year);

            $pending_data = $query->where(function($q) use ($currentDetailsId) {
                    $q->where('pend.pending_qty', '>', 0);
                    if ($currentDetailsId) {
                        $q->orWhere('pend.material_inward_details_id', $currentDetailsId);
                    }
                })
                ->get();

            return response()->json([
                'response_code' => 1,
                'pending_data' => $pending_data
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'response_code'    => 0,
                'response_message' => $e->getMessage(),
                'pending_data'     => []
            ]);
        }
    }

    public function getPendingCustomersForDpt(Request $request)
    {
        try {
            $location = getCurrentLocation()->location_id;
            $year = getCompanyYearIdsToTill();

            $reportId = $request->input('report_id');
            $currentCustomerId = null;
            if ($reportId) {
                $currentCustomerId = DB::table('test_report_dpt')->where('test_report_dpt_id', $reportId)->value('customer_id');
            }

            $currentCustomer = null;
            if ($currentCustomerId) {
                $currentCustomer = DB::table('customers')
                    ->where('id', $currentCustomerId)
                    ->select('id', 'customer')
                    ->first();
            }

            $customers = DB::table('pending_material_inward_dpt_qty as pend')
                ->join('customers', 'customers.id', '=', 'pend.customer_id')
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
                    if (is_array($c) ? ($c['id'] == $currentCustomer->id) : ($c->id == $currentCustomer->id)) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $customers[] = is_array($currentCustomer) ? $currentCustomer : $currentCustomer->toArray();
                }
            }

            return response()->json([
                'response_code' => 1,
                'customers' => $customers
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'response_code'    => 0,
                'response_message' => $e->getMessage(),
                'customers'        => []
            ]);
        }
    }

    public function getLatestULRNo(Request $request)
    {
        $procedure_data = NABLConfiguration::where('nabl_id', '=', $request->ulr_id)->first();
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
        if (!($location_data instanceof Location)) {
            $location_data = Location::orderBy('location_id', 'desc')->first();
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
            TestReportDpt::class,
            $id
        );

        return response()->json([
            'response_code' => $isDuplicate ? 0 : 1,
            'response_message' => $isDuplicate ? 'Duplicate ULR No. Found.' : 'Available.'
        ]);
    }

    public function getCopyReportsList(Request $request)
    {
        $year = getCurrentYearData()->id;
        $location = getCurrentLocation()->location_id;

        $query = TestReportDpt::select([
            'test_report_dpt.test_report_dpt_id as id',
            'test_report_dpt.test_report_no',
            'test_report_dpt.test_report_date',
            'customers.customer',
            'test_report_dpt.nabl_type_fix',
            'test_report_dpt.job_type_fix',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            'test_report_dpt.part_no',
            'test_report_dpt.drg_no',
            'materials.material',
            'test_report_dpt.product_code'
        ])
        ->leftJoin('customers', 'customers.id', '=', 'test_report_dpt.customer_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'test_report_dpt.type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'test_report_dpt.job_desc_id')
        ->leftJoin('materials', 'materials.id', '=', 'test_report_dpt.material_id')
        ->where('test_report_dpt.year_id', $year)
        ->where('test_report_dpt.current_location_id', $location);

        if ($request->filled('type_of_job_id')) {
            $query->where('test_report_dpt.type_of_job_id', $request->type_of_job_id);
        }

        $reports = $query->orderBy('test_report_dpt.test_report_dpt_id', 'desc')->get();

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
        $report_data = DB::select('CALL test_report_dpt_master(?)', [$id]);
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

            if (isset($report_data->cmp_logo)) {
                $report_data->cmp_logo = base64_encode($report_data->cmp_logo);
            }
            // Prevent Malformed UTF-8 characters exception by base64 encoding binary blobs
            foreach ($report_data as $key => $value) {
                if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                    $report_data->$key = base64_encode($value);
                }
            }

            $chemicals = DB::select('CALL test_report_dpt_chemical_details(?)', [$id]);
            foreach ($chemicals as $row) {
                $row->expiry_date = ($row->expiry_date != "" && $row->expiry_date != "0000-00-00")
                    ? Date::createFromFormat('Y-m-d', $row->expiry_date)->format('d/m/Y')
                    : "";
            }

            $details = DB::select('CALL test_report_dpt_details(?)', [$id]);

            return response()->json([
                'response_code' => 1,
                'report_data' => $report_data,
                'chemical_details_data' => $chemicals,
                'report_details_data' => $details,
            ]);
        }

        return response()->json([
            'response_code' => 0,
            'response_message' => 'Record Not Found',
        ]);
    }

    public function existsCustomerClient(Request $request)
    {
        return $this->existsSuggestion('test_report_dpt', 'customer_client', $request->term, 'customerClientList', 'customer_client');
    }

    public function existsPartNo(Request $request)
    {
        return $this->existsSuggestion('test_report_dpt', 'part_no', $request->term, 'partNoList', 'part_no');
    }

    public function existsDrgNo(Request $request)
    {
        return $this->existsSuggestion('test_report_dpt', 'drg_no', $request->term, 'drgNoList', 'drg_no');
    }

    public function existsSurfaceCondition(Request $request)
    {
        return $this->existsSuggestion('test_report_dpt', 'surface_condition', $request->term, 'surfaceConditionList', 'surface_condition');
    }

    public function existsTestTechnique(Request $request)
    {
        return $this->existsSuggestion('test_report_dpt', 'test_technique', $request->term, 'testTechniqueList', 'test_technique');
    }

    public function existsTestCarriedOutAt(Request $request)
    {
        return $this->existsSuggestion('test_report_dpt', 'test_carried_out_at', $request->term, 'testCarriedOutAtList', 'test_carried_out_at');
    }

    public function existsSurfaceTemp(Request $request)
    {
        return $this->existsSuggestion('test_report_dpt', 'surface_temp', $request->term, 'surfaceTempList', 'surface_temp');
    }

    public function existsThickness(Request $request)
    {
        return $this->existsSuggestion('test_report_dpt', 'thickness', $request->term, 'thicknessList', 'thickness');
    }

    public function existsPreCleaning(Request $request)
    {
        return $this->existsSuggestion('test_report_dpt', 'pre_cleaning', $request->term, 'preCleaningList', 'pre_cleaning');
    }

    public function existsPenetrantApplication(Request $request)
    {
        return $this->existsSuggestion('test_report_dpt', 'penetrant_application', $request->term, 'penetrantApplicationList', 'penetrant_application');
    }

    public function existsDwellTime(Request $request)
    {
        return $this->existsSuggestion('test_report_dpt', 'dwell_time', $request->term, 'dwellTimeList', 'dwell_time');
    }

    public function existsPenetrantRemover(Request $request)
    {
        return $this->existsSuggestion('test_report_dpt', 'penetrant_remover', $request->term, 'penetrantRemoverList', 'penetrant_remover');
    }

    public function existsDeveloper(Request $request)
    {
        return $this->existsSuggestion('test_report_dpt', 'developer', $request->term, 'developerList', 'developer');
    }

    public function existsDeveloperApplication(Request $request)
    {
        return $this->existsSuggestion('test_report_dpt', 'developer_application', $request->term, 'developerApplicationList', 'developer_application');
    }

    public function existsLighting(Request $request)
    {
        return $this->existsSuggestion('test_report_dpt', 'lighting', $request->term, 'lightingList', 'lighting');
    }

    public function existsBackgroundLight(Request $request)
    {
        return $this->existsSuggestion('test_report_dpt', 'background_light', $request->term, 'backgroundLightList', 'background_light');
    }

    public function existsProductCode(Request $request)
    {
        return $this->existsSuggestion('test_report_dpt', 'product_code', $request->term, 'productCodeList', 'product_code');
    }

    private function existsSuggestion($table, $column, $term, $listId, $parentId)
    {
        $location = getCurrentLocation()->location_id;
        if ($term != "") {
            $data = DB::table($table)
                ->select($column)
                ->where('current_location_id', $location)
                ->where($column, 'LIKE', $term . '%')
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->groupBy($column)
                ->orderBy($column, 'asc')
                ->get();
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

    public function testReportDptLNRData()
    {
        $location_data = getCurrentLocation();
        $year_data = getCurrentYearData();

        $lnr_data = TestReportDpt::select([
            'test_report_dpt_id',
            'note'
        ])
        ->where('current_location_id', $location_data->location_id)
        ->orderBy('test_report_dpt_id', 'desc')
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
