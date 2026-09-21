<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\TestReportUt;
use App\Models\Transaction\TestReportUtEquipmentDetails;
use App\Models\Transaction\TestReportUtProbeDetails;
use App\Models\Transaction\TestReportUtDetails;
use App\Models\Transaction\MaterialInwardDetails;
use App\Models\Transaction\ObservationSheetDetails;
use App\Models\Customer;
use App\Models\EquipmentUT;
use App\Models\ProbeUT;
use App\Models\NABLConfiguration;
use App\Models\Location;
use App\Models\File;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class TestReportUtController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-test_report_ut');
    }

    public function index(Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();

        $reports = TestReportUt::select([
            'test_report_ut.test_report_ut_id as id','test_report_ut.test_report_no','test_report_ut.test_report_sequence','test_report_ut.test_report_date','test_report_ut.nabl_type_fix','test_report_ut.ulr_no','test_report_ut.test_carried_out_at','test_report_ut.job_type_fix','test_report_ut.from_type_id_fix','customers.customer','material_inward.dc_no','material_inward.dc_date','material_inward.po_no','material_inward.po_date','type_of_job.type_of_job','job_descriptions.job_description','test_report_ut.part_no','test_report_ut.drg_no','materials.material','test_report_ut.heat_no','test_report_ut.product_code','test_report_ut.total_qty','test_report_ut.created_on','test_report_ut.created_by','test_report_ut.last_by','test_report_ut.last_on'
        ])
        ->leftJoin('customers', 'customers.id', '=', 'test_report_ut.customer_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'test_report_ut.type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'test_report_ut.job_desc_id')
        ->leftJoin('materials', 'materials.id', '=', 'test_report_ut.material_id')
        ->leftJoin('material_inward_details', 'material_inward_details.material_inward_details_id', '=', 'test_report_ut.material_inward_details_id')
        ->leftJoin('material_inward', 'material_inward.material_inward_id', '=', 'material_inward_details.material_inward_id')
        ->where('test_report_ut.year_id', $year_data->id)
        ->where('test_report_ut.current_location_id', $location_data->location_id);

        $dataTable = DataTables::of($reports)
        ->filterColumn('test_report_ut.test_report_no', function($query, $keyword) {
            applySequenceSearch($query, $keyword, 'test_report_ut.test_report_no');
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
        ->filterColumn('test_report_ut.test_report_date', function ($q, $k) {
            applyDate($q, $k, 'test_report_ut.test_report_date');
        })
        ->filterColumn('material_inward.dc_date', function ($q, $k) {
            applyDate($q, $k, 'material_inward.dc_date');
        })
        ->filterColumn('material_inward.po_date', function ($q, $k) {
            applyDate($q, $k, 'material_inward.po_date');
        })
        ->filterColumn('part_no', function($query, $keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('test_report_ut.part_no', 'like', "%{$keyword}%");
            });
        })
        ->filterColumn('drg_no', function($query, $keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('test_report_ut.drg_no', 'like', "%{$keyword}%");
            });
        })
        ->addColumn('options', function($report) {
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("test_report_ut", "print")) {
                    $report_number = !empty($report->test_report_no) ? '_' . str_replace('/', '_', $report->test_report_no) : "";
                    $cust_name = !empty($report->customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $report->customer) : "";
                    $pdfName   = 'Test_Report_UT' . $report_number . $cust_name;
                    $encodedId = base64_encode($report->id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=test_report_ut");
                    $action .= '<li><a class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }

                if (hasAccess("test_report_ut", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-test_report_ut"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("test_report_ut", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });

        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'test_report_ut');
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

        $equipment = json_decode($request->equipment_details, true);
        if (!empty($equipment)) {
            foreach ($equipment as $row) {
                if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;
                $eq_status = EquipmentUT::where('eu_id', $row['detail_eu_id'])->value('eu_status');
                if ($eq_status !== 'Active') {
                    return response()->json([
                        'response_code' => '0',
                        'response_message' => 'Equipment is not active.'
                    ]);
                }
            }
        }

        $probes = json_decode($request->probe_details, true);
        if (!empty($probes)) {
            foreach ($probes as $row) {
                if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;
                $pu_status = ProbeUT::where('pu_id', $row['detail_pu_id'])->value('pu_status');
                if ($pu_status !== 'Active') {
                    return response()->json([
                        'response_code' => '0',
                        'response_message' => 'Probe is not active.'
                    ]);
                }
            }
        }

        DB::beginTransaction();
        try {
            $fromTypeId = $request->from_type_id_fix;
            $qtyCheck = $this->qtyValidation($request->total_qty ?? 0, 0, 'I', $request->customer_id, $request->material_inward_details_id, $request->observation_sheet_details_id ?? null, $fromTypeId, 0);
            if ($qtyCheck) {
                return $qtyCheck;
            }

            $existNumber = TestReportUt::where([
                ['test_report_sequence', $request->test_report_sequence],
                ['test_report_no', $request->test_report_no],
                ['year_id', $year_data->id],
                ['current_location_id', $location_data->location_id]
            ])->first();

            if ($existNumber) {
                $latestNo = $this->getLatestTestReportUtNumber($request);
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
                    $isFound = $file->getFileFromTemp($request->defectogram_image_doc, 'test_report_ut');
                } else {
                    $isFound = $file->getFileCopyFormUpload($request->defectogram_image_doc, 'test_report_ut');
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

                if (isDuplicateUlrSequence($request->ulr_sequence, $ulr_id, $ulr_year, $location_data->location_id)) {
                    return response()->json([
                        'response_code' => 0,
                        'response_message' => 'Duplicate ULR No. Found.'
                    ]);
                }
                $ulr_no = $request->ulr_no;
                $ulr_sequence = $request->ulr_sequence;
            }
            $page_id = getMenuIdBassedOnDisplayName('test_report_ut');
            $assign_format_no = $page_id ? getAssignFormateNoForTransaction($location_data->location_id, $page_id->id, $request->test_report_date) : '';

            $report = TestReportUt::create([
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
                'couplant' => $request->couplant,
                'test_technique' => $request->test_technique,
                'ref_block_used' => $request->ref_block_used,
                'scanning_area' => $request->scanning_area,
                'scan_plan_no' => $request->scan_plan_no,
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

            // Grid 1: Equipment Details
            $equipment = json_decode($request->equipment_details, true);
            if (!empty($equipment)) {
                foreach ($equipment as $row) {
                    if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;
                    TestReportUtEquipmentDetails::create([
                        'test_report_ut_id' => $report->test_report_ut_id,
                        'eu_id' => $row['detail_eu_id'],
                        'make' => $row['make'],
                        'display' => $row['display'],
                        'eu_sr_no' => $row['eu_sr_no'],
                        'cal_due_date' => !empty($row['cal_due_date']) ? Date::createFromFormat('d/m/Y', $row['cal_due_date'])->format('Y-m-d') : null,
                    ]);
                }
            }

            // Grid 2: Probe Details
            $probes = json_decode($request->probe_details, true);
            if (!empty($probes)) {
                foreach ($probes as $row) {
                    if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;
                    TestReportUtProbeDetails::create([
                        'test_report_ut_id' => $report->test_report_ut_id,
                        'pu_id' => $row['detail_pu_id'],
                        'pu_sr_no' => $row['pu_sr_no'],
                        'size_of_probe' => $row['size_of_probe'],
                        'ref_angle' => $row['ref_angle'],
                        'frequency' => $row['frequency'],
                        'cal_range' => $row['cal_range'],
                        'ref_gain' => $row['ref_gain'],
                        'scanning_db' => $row['scanning_db'],
                        'transfer_corr_gain' => $row['transfer_corr_gain'],
                    ]);
                }
            }

            // Grid 3: UT Report Details
            $details = json_decode($request->report_details_data, true);
            if (!empty($details)) {
                foreach ($details as $row) {
                    if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;
                    TestReportUtDetails::create([
                        'test_report_ut_id' => $report->test_report_ut_id,
                        'ut_test_no' => $row['ut_test_no'],
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
            $cust = Customer::where('id', $report->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Test_Report_UT' . $report_number . $cust_name;

            if (function_exists('GeneratePdf')) {
                GeneratePdf($report->test_report_ut_id, $pdf_name, 'test_report_ut', 'add');
            }
            $encodedId = base64_encode($report->test_report_ut_id);
            $url = hasAccess("test_report_ut", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=test_report_ut") : "";

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
        $report_data = DB::select('CALL test_report_ut_master(?)', [$request->id]);
        if (!empty($report_data)) {
            $report_data = $report_data[0];

            $report_number = !empty($report_data->test_report_no) ? '_' . str_replace('/', '_', $report_data->test_report_no) : "";
            $cust = Customer::where('id', $report_data->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $report_data->pdf_name = 'Test_Report_UT' . $report_number . $cust_name;

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

            $equipment = DB::select('CALL test_report_ut_equipment_details(?)', [$request->id]);
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

            $probes = DB::select('CALL test_report_ut_probe_details(?)', [$request->id]);
            foreach ($probes as $row) {
                foreach ($row as $key => $value) {
                    if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                        $row->$key = base64_encode($value);
                    }
                }
            }

            $details = DB::select('CALL test_report_ut_details(?)', [$request->id]);
            foreach ($details as $row) {
                foreach ($row as $key => $value) {
                    if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                        $row->$key = base64_encode($value);
                    }
                }
            }

            // Get current pending qty for this inward detail & from_type_id_fix (so edit mode can display it)
            $fromTypeId = $report_data->from_type_id_fix;
            $pendQuery = DB::table('pending_material_inward_ut_qty')
                ->where('material_inward_details_id', $report_data->material_inward_details_id)
                ->where('from_type_id_fix', $fromTypeId);
            if (!empty($report_data->observation_sheet_details_id) && $fromTypeId == 2) {
                $pendQuery->where('observation_sheet_details_id', $report_data->observation_sheet_details_id);
            }
            $pend_qty = $pendQuery->value('pending_qty') ?? 0;
            // Add back the already-used qty from this report so the displayed value makes sense
            $pend_qty = $pend_qty + ($report_data->total_qty ?? 0);

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

            return response()->json([
                'response_code' => 1,
                'report_data' => $report_data,
                'pend_qty' => $pend_qty,
                'equipment_details_data' => $equipment,
                'probe_details_data' => $probes,
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

        $report = TestReportUt::where('test_report_ut_id', $request->id)->first();
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

        $equipment = json_decode($request->equipment_details, true);
        if (!empty($equipment)) {
            foreach ($equipment as $row) {
                if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;
                $eq_status = EquipmentUT::where('eu_id', $row['detail_eu_id'])->value('eu_status');
                if ($eq_status !== 'Active') {
                    return response()->json([
                        'response_code' => '0',
                        'response_message' => 'Selected Equipment is not active.'
                    ]);
                }
            }
        }

        $probes = json_decode($request->probe_details, true);
        if (!empty($probes)) {
            foreach ($probes as $row) {
                if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;
                $pu_status = ProbeUT::where('pu_id', $row['detail_pu_id'])->value('pu_status');
                if ($pu_status !== 'Active') {
                    return response()->json([
                        'response_code' => '0',
                        'response_message' => 'Selected Probe is not active.'
                    ]);
                }
            }
        }

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
                    $isFound = $file->getFileFromTemp($request->defectogram_image_doc, 'test_report_ut');
                } else {
                    $isFound = $file->getFileCopyFormUpload($request->defectogram_image_doc, 'test_report_ut');
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
                // User removed the sketch
                if ($defectogramPath) {
                    $file->delete_file($defectogramPath);
                }
                $defectogramPath = null;
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

                if (isDuplicateUlrSequence($request->ulr_sequence, $ulr_id, $ulr_year, $location_data->location_id, TestReportUt::class, $request->id)) {
                    return response()->json([
                        'response_code' => 0,
                        'response_message' => 'Duplicate ULR No. Found.'
                    ]);
                }
                $ulr_no = $request->ulr_no;
                $ulr_sequence = $request->ulr_sequence;
            }

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
                'couplant' => $request->couplant,
                'test_technique' => $request->test_technique,
                'ref_block_used' => $request->ref_block_used,
                'scanning_area' => $request->scanning_area,
                'scan_plan_no' => $request->scan_plan_no,
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
                'last_by' => Auth::id(),
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
            ]);

            // Grid 1: Equipment Details Update
            $equipment = json_decode($request->equipment_details, true);
            if (!empty($equipment)) {
                foreach ($equipment as $row) {
                    if (empty($row)) continue;
                    $mode = $row['mode'] ?? '';

                    if ($mode === 'Insert') {
                        TestReportUtEquipmentDetails::create([
                            'test_report_ut_id' => $report->test_report_ut_id,
                            'eu_id' => $row['detail_eu_id'],
                            'make' => $row['make'],
                            'display' => $row['display'],
                            'eu_sr_no' => $row['eu_sr_no'],
                            'cal_due_date' => !empty($row['cal_due_date']) ? Date::createFromFormat('d/m/Y', $row['cal_due_date'])->format('Y-m-d') : null,
                        ]);
                    } elseif ($mode === 'Update') {
                        if (!empty($row['test_report_ut_equipment_details_id'])) {
                            TestReportUtEquipmentDetails::where('test_report_ut_equipment_details_id', $row['test_report_ut_equipment_details_id'])->update([
                                'eu_id' => $row['detail_eu_id'],
                                'make' => $row['make'],
                                'display' => $row['display'],
                                'eu_sr_no' => $row['eu_sr_no'],
                                'cal_due_date' => !empty($row['cal_due_date']) ? Date::createFromFormat('d/m/Y', $row['cal_due_date'])->format('Y-m-d') : null,
                            ]);
                        }
                    } elseif ($mode === 'Delete') {
                        if (!empty($row['test_report_ut_equipment_details_id'])) {
                            TestReportUtEquipmentDetails::where('test_report_ut_equipment_details_id', $row['test_report_ut_equipment_details_id'])->delete();
                        }
                    }
                }
            }

            // Grid 2: Probe Details Update
            $probes = json_decode($request->probe_details, true);
            if (!empty($probes)) {
                foreach ($probes as $row) {
                    if (empty($row)) continue;
                    $mode = $row['mode'] ?? '';

                    if ($mode === 'Insert') {
                        TestReportUtProbeDetails::create([
                            'test_report_ut_id' => $report->test_report_ut_id,
                            'pu_id' => $row['detail_pu_id'],
                            'pu_sr_no' => $row['pu_sr_no'],
                            'size_of_probe' => $row['size_of_probe'],
                            'ref_angle' => $row['ref_angle'],
                            'frequency' => $row['frequency'],
                            'cal_range' => $row['cal_range'],
                            'ref_gain' => $row['ref_gain'],
                            'scanning_db' => $row['scanning_db'],
                            'transfer_corr_gain' => $row['transfer_corr_gain'],
                        ]);
                    } elseif ($mode === 'Update') {
                        if (!empty($row['test_report_ut_probe_details_id'])) {
                            TestReportUtProbeDetails::where('test_report_ut_probe_details_id', $row['test_report_ut_probe_details_id'])->update([
                                'pu_id' => $row['detail_pu_id'],
                                'pu_sr_no' => $row['pu_sr_no'],
                                'size_of_probe' => $row['size_of_probe'],
                                'ref_angle' => $row['ref_angle'],
                                'frequency' => $row['frequency'],
                                'cal_range' => $row['cal_range'],
                                'ref_gain' => $row['ref_gain'],
                                'scanning_db' => $row['scanning_db'],
                                'transfer_corr_gain' => $row['transfer_corr_gain'],
                            ]);
                        }
                    } elseif ($mode === 'Delete') {
                        if (!empty($row['test_report_ut_probe_details_id'])) {
                            TestReportUtProbeDetails::where('test_report_ut_probe_details_id', $row['test_report_ut_probe_details_id'])->delete();
                        }
                    }
                }
            }

            // Grid 3: UT Report Details Update
            $details = json_decode($request->report_details_data, true);
            if (!empty($details)) {
                foreach ($details as $row) {
                    if (empty($row)) continue;
                    $mode = $row['mode'] ?? '';

                    if ($mode === 'Insert') {
                        TestReportUtDetails::create([
                            'test_report_ut_id' => $report->test_report_ut_id,
                            'ut_test_no' => $row['ut_test_no'],
                            'heat_no' => $row['heat_no'],
                            'quantity' => $row['quantity'],
                            'discontinuity_evaluation' => $row['discontinuity_evaluation'],
                            'result_id' => !empty($row['detail_result_id']) ? $row['detail_result_id'] : null,
                        ]);
                    } elseif ($mode === 'Update') {
                        if (!empty($row['test_report_ut_details_id'])) {
                            TestReportUtDetails::where('test_report_ut_details_id', $row['test_report_ut_details_id'])->update([
                                'ut_test_no' => $row['ut_test_no'],
                                'heat_no' => $row['heat_no'],
                                'quantity' => $row['quantity'],
                                'discontinuity_evaluation' => $row['discontinuity_evaluation'],
                                'result_id' => !empty($row['detail_result_id']) ? $row['detail_result_id'] : null,
                            ]);
                        }
                    } elseif ($mode === 'Delete') {
                        if (!empty($row['test_report_ut_details_id'])) {
                            TestReportUtDetails::where('test_report_ut_details_id', $row['test_report_ut_details_id'])->delete();
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
            $report_number = !empty($report->test_report_no) ? '_' . str_replace('/', '_', $report->test_report_no) : "";
            $cust = Customer::where('id', $report->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Test_Report_UT' . $report_number . $cust_name;

            if (function_exists('GeneratePdf')) {
                GeneratePdf($report->test_report_ut_id, $pdf_name, 'test_report_ut', 'update');
            }
            $encodedId = base64_encode($report->test_report_ut_id);
            $url = hasAccess("test_report_ut", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=test_report_ut") : "";

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
            $report = TestReportUt::where('test_report_ut_id', $request->id)->first();
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
                TestReportUtEquipmentDetails::where('test_report_ut_id', $request->id)->delete();
                TestReportUtProbeDetails::where('test_report_ut_id', $request->id)->delete();
                TestReportUtDetails::where('test_report_ut_id', $request->id)->delete();
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

    public function getLatestTestReportUtNumber(Request $request)
    {
        $modal = TestReportUt::class;
        $sequence = 'test_report_sequence';
        $prefix = 'UT';

        $num_format = getLatestSequence($modal, $sequence, $prefix);

        return response()->json([
            'response_code' => 1,
            'latest_no' => $num_format['format'],
            'number' => $num_format['isFound'],
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
                $report = DB::table('test_report_ut')->where('test_report_ut_id', $reportId)->first();
                if ($report) {
                    $currentDetailsId = $report->material_inward_details_id;
                }
            }

            $query = DB::table('pending_material_inward_ut_qty as pend')
                ->select([
                    'pend.from_type_id_fix','pend.material_inward_details_id','pend.observation_sheet_details_id','pend.pending_qty','pend.current_location_id','mid.type_of_job_id','mid.job_desc_id','mid.material_id','mid.area_of_coverage_id','mid.procedure_ref_id','mid.evaluation_as_per_id','mid.acceptance_standard_id','mid.thickness','mid.quantity as inward_qty','pend.customer_id','mi.year_id','mi.dc_no','mi.dc_date','mi.po_no','mi.po_date','mi.nabl_type_fix','mi.job_type_fix','mi.material_inward_no','mi.material_inward_date','mi.test_at_fix','toj.type_of_job','jd.job_description','mid.part_no','mid.drg_no','m.material','mid.heat_no','mid.product_code','ac.area_of_coverage','pr.procedure_reference','as_std.acceptance_standard'
                ])
                ->leftJoin('material_inward_details as mid', 'mid.material_inward_details_id', '=', 'pend.material_inward_details_id')
                ->leftJoin('material_inward as mi', 'mi.material_inward_id', '=', 'mid.material_inward_id')
                ->leftJoin('type_of_job as toj', 'toj.id', '=', 'mid.type_of_job_id')
                ->leftJoin('job_descriptions as jd', 'jd.id', '=', 'mid.job_desc_id')
                ->leftJoin('materials as m', 'm.id', '=', 'mid.material_id')
                ->leftJoin('area_of_coverage as ac', 'ac.area_of_coverage_id', '=', 'mid.area_of_coverage_id')
                ->leftJoin('procedure_reference as pr', 'pr.procedure_reference_id', '=', 'mid.procedure_ref_id')
                ->leftJoin('acceptance_standards as as_std', 'as_std.id', '=', 'mid.acceptance_standard_id')
                ->leftJoin('observation_sheet_details as osd', 'osd.observation_sheet_details_id', '=', 'pend.observation_sheet_details_id')
                ->leftJoin('observation_sheet as os', 'os.observation_sheet_id', '=', 'osd.observation_sheet_id')
                ->where('pend.current_location_id', $location)
                ->where('pend.customer_id', $request->customer_id)
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

    public function getPendingCustomersForUt(Request $request)
    {
        try {
            $location = getCurrentLocation()->location_id;
            $year = getCompanyYearIdsToTill();

            $reportId = $request->input('report_id');
            $currentCustomerId = null;
            if ($reportId) {
                $currentCustomerId = TestReportUt::where('test_report_ut_id', $reportId)->value('customer_id');
            }

            $currentCustomer = null;
            if ($currentCustomerId) {
                $currentCustomer = Customer::where('id', $currentCustomerId)
                    ->select('id', 'customer')
                    ->first();
            }

            $customers = DB::table('pending_material_inward_ut_qty as pend')
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
        $Date = new DateTime($date);
        $year = $Date->format("Y");

        $location_data = getCurrentLocation();
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
        $DateObj = new DateTime($date);
        $ulr_year = $DateObj->format("Y");
        
        $location_data = getCurrentLocation();
        $location_id = $location_data ? $location_data->location_id : null;

        $isDuplicate = isDuplicateUlrSequence(
            $ulr_sequence,
            $ulr_id,
            $ulr_year,
            $location_id,
            TestReportUt::class,
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

        $query = TestReportUt::select([
            'test_report_ut.test_report_ut_id as id',
            'test_report_ut.test_report_no',
            'test_report_ut.test_report_date',
            'customers.customer',
            'test_report_ut.nabl_type_fix',
            'test_report_ut.job_type_fix',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            'test_report_ut.part_no',
            'test_report_ut.drg_no',
            'materials.material',
            'test_report_ut.heat_no',
            'test_report_ut.product_code'
        ])
        ->leftJoin('customers', 'customers.id', '=', 'test_report_ut.customer_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'test_report_ut.type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'test_report_ut.job_desc_id')
        ->leftJoin('materials', 'materials.id', '=', 'test_report_ut.material_id')
        ->where('test_report_ut.year_id', $year)
        ->where('test_report_ut.current_location_id', $location);

        if ($request->filled('type_of_job_id')) {
            $query->where('test_report_ut.type_of_job_id', $request->type_of_job_id);
        }

        $reports = $query->orderBy('test_report_ut.test_report_ut_id', 'desc')->get();

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
        $report_data = DB::select('CALL test_report_ut_master(?)', [$id]);
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

            $equipment = DB::select('CALL test_report_ut_equipment_details(?)', [$id]);
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

            $probes = DB::select('CALL test_report_ut_probe_details(?)', [$id]);
            foreach ($probes as $row) {
                foreach ($row as $key => $value) {
                    if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                        $row->$key = base64_encode($value);
                    }
                }
            }

            $details = DB::select('CALL test_report_ut_details(?)', [$id]);
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
                'probe_details_data' => $probes,
                'report_details_data' => $details,
            ]);
        }

        return response()->json([
            'response_code' => 0,
            'response_message' => 'Record Not Found',
        ]);
    }

    // Suggestions autocomplete endpoints
    public function existsCustomerClient(Request $request)
    {
        return $this->existsSuggestion('test_report_ut', 'customer_client', $request->term, 'customerClientList', 'customer_client');
    }

    public function existsPartNo(Request $request)
    {
        return $this->existsSuggestion('test_report_ut', 'part_no', $request->term, 'partNoList', 'part_no');
    }

    public function existsDrgNo(Request $request)
    {
        return $this->existsSuggestion('test_report_ut', 'drg_no', $request->term, 'drgNoList', 'drg_no');
    }

    public function existsSurfaceCondition(Request $request)
    {
        return $this->existsSuggestion('test_report_ut', 'surface_condition', $request->term, 'surfaceConditionList', 'surface_condition');
    }

    public function existsCouplant(Request $request)
    {
        return $this->existsSuggestion('test_report_ut', 'couplant', $request->term, 'couplantList', 'couplant');
    }

    public function existsTestTechnique(Request $request)
    {
        return $this->existsSuggestion('test_report_ut', 'test_technique', $request->term, 'testTechniqueList', 'test_technique');
    }

    public function existsRefBlockUsed(Request $request)
    {
        return $this->existsSuggestion('test_report_ut', 'ref_block_used', $request->term, 'refBlockUsedList', 'ref_block_used');
    }

    public function existsScanningArea(Request $request)
    {
        return $this->existsSuggestion('test_report_ut', 'scanning_area', $request->term, 'scanningAreaList', 'scanning_area');
    }

    public function existsScanPlanNo(Request $request)
    {
        return $this->existsSuggestion('test_report_ut', 'scan_plan_no', $request->term, 'scanPlanNoList', 'scan_plan_no');
    }

    public function existsTestCarriedOutAt(Request $request)
    {
        return $this->existsSuggestion('test_report_ut', 'test_carried_out_at', $request->term, 'testCarriedOutAtList', 'test_carried_out_at');
    }

    public function existsSurfaceTemp(Request $request)
    {
        return $this->existsSuggestion('test_report_ut', 'surface_temp', $request->term, 'surfaceTempList', 'surface_temp');
    }

    public function existsThickness(Request $request)
    {
        return $this->existsSuggestion('test_report_ut', 'thickness', $request->term, 'thicknessList', 'thickness');
    }

    public function existsProductCode(Request $request)
    {
        return $this->existsSuggestion('test_report_ut', 'product_code', $request->term, 'productCodeList', 'product_code');
    }

    public function getLastReportEquipmentAndProbes(Request $request)
    {
        $location = getCurrentLocation()->location_id;
        
        $lastReport = TestReportUt::where('current_location_id', $location)
            ->orderBy('test_report_ut_id', 'desc')
            ->first();

        $equipment = [];
        $probes = [];

        if ($lastReport) {
            $equipment = DB::table('test_report_ut_equipment_details as eq_det')
                ->join('equipment_ut as eq', 'eq.eu_id', '=', 'eq_det.eu_id')
                ->where('eq_det.test_report_ut_id', $lastReport->test_report_ut_id)
                ->where('eq.eu_status', 'Active')
                ->select([
                    'eq_det.eu_id',
                    'eq.eu_equipment_name',
                    'eq_det.make',
                    'eq_det.display',
                    'eq_det.eu_sr_no',
                    'eq_det.cal_due_date'
                ])
                ->get()
                ->map(function($row) {
                    $row->cal_due_date = ($row->cal_due_date != "" && $row->cal_due_date != "0000-00-00")
                        ? Date::createFromFormat('Y-m-d', $row->cal_due_date)->format('d/m/Y')
                        : "";
                    return $row;
                })
                ->toArray();

            $probes = DB::table('test_report_ut_probe_details as pb_det')
                ->join('probe_ut as pb', 'pb.pu_id', '=', 'pb_det.pu_id')
                ->where('pb_det.test_report_ut_id', $lastReport->test_report_ut_id)
                ->where('pb.pu_status', 'Active')
                ->select([
                    'pb_det.pu_id',
                    'pb.pu_probe',
                    'pb_det.pu_sr_no',
                    'pb_det.size_of_probe',
                    'pb_det.ref_angle',
                    'pb_det.frequency',
                    'pb_det.cal_range',
                    'pb_det.ref_gain',
                    'pb_det.scanning_db',
                    'pb_det.transfer_corr_gain'
                ])
                ->get()
                ->toArray();
        }

        return response()->json([
            'response_code' => 1,
            'equipment_details_data' => $equipment,
            'probe_details_data' => $probes
        ]);
    }

    public function checkNegativePendingQty($customer_id = 0, $location_id = 0, $from_type_id_fix = 1, $material_inward_details_id = 0, $observation_sheet_details_id = null, $transaction_mode = 'U')
    {
        $location_id = $location_id ?: getCurrentLocation()->location_id;
        $msg = ($transaction_mode === 'I')
            ? "You Can't Insert, Something Went Wrong."
            : "You Can't Update, Something Went Wrong.";

        if (!empty($material_inward_details_id)) {
            $query = DB::table('pending_material_inward_ut_qty')
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

    public function qtyValidation($from_qty, $next_qty, $transaction_mode, $customer_id, $material_inward_details_id, $observation_sheet_details_id = null, $from_type_id_fix = 1, $test_report_ut_id = 0)
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

            $query = DB::table('pending_material_inward_ut_qty')
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

            $pending_ut_qty = (float)($query->value('pending_qty') ?? 0);

            if ($pending_ut_qty < (float)$from_qty) {
                DB::rollBack();
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => 'UT Report Qty. Is Used.',
                ]);
            }
        }

        return null; // No error
    }

    private function existsSuggestion($table, $column, $term, $listId, $parentId)
    {
        if ($term != "") {
            $location_data = getCurrentLocation();
            $locationId = $location_data ? $location_data->location_id : null;

            $query = DB::table($table)
                ->select($column)
                ->where($column, 'LIKE', $term . '%')
                ->whereNotNull($column)
                ->where($column, '!=', '');

            if ($locationId) {
                $query->where('current_location_id', $locationId);
            }

            $data = $query->groupBy($column)
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

    public function testReportUtLNRData()
    {
        $location_data = getCurrentLocation();
        $year_data = getCurrentYearData();

        $lnr_data = TestReportUt::select([
            'test_report_ut_id',
            'note'
        ])
        ->where('current_location_id', $location_data->location_id)
        ->orderBy('test_report_ut_id', 'desc')
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
