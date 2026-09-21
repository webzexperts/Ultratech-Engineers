<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\MeasurementSheet;
use App\Models\Transaction\MeasurementSheetDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class MeasurementSheetController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-measurement_sheet');
    }

    public function index(Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();

        $sheets = MeasurementSheet::select([
            'measurement_sheet.measurement_sheet_id as id',
            'measurement_sheet.measurement_sheet_no',
            'measurement_sheet.measurement_sheet_sequence',
            'measurement_sheet.measurement_sheet_date',
            'customers.customer as customer_name',
            'measurement_sheet.total_ir_192_sqin',
            'measurement_sheet.total_co_60_sqin',
            'measurement_sheet.total_x_ray_sqin',
            'measurement_sheet.total_ir_192_repair_sqin',
            'measurement_sheet.total_co_60_repair_sqin',
            'measurement_sheet.total_x_ray_repair_sqin',
            'measurement_sheet.created_on',
            'measurement_sheet.created_by',
            'measurement_sheet.last_by',
            'measurement_sheet.last_on'
        ])
        ->leftJoin('customers', 'customers.id', '=', 'measurement_sheet.customer_id')
        ->where('measurement_sheet.year_id', $year_data->id)
        ->where('measurement_sheet.current_location_id', $location_data->location_id);

        $dataTable = $datatables->eloquent($sheets)
            ->filterColumn('measurement_sheet.measurement_sheet_no', function($query, $keyword) {
                applySequenceSearch($query, $keyword, 'measurement_sheet.measurement_sheet_no');
            })
            ->editColumn('measurement_sheet_date', function($sheet) {
                return $sheet->measurement_sheet_date != null
                    ? Date::createFromFormat('Y-m-d', $sheet->measurement_sheet_date)->format(DATE_FORMAT)
                    : '';
            })
            ->filterColumn('measurement_sheet.measurement_sheet_date', function ($q, $k) {
                applyDate($q, $k, 'measurement_sheet.measurement_sheet_date');
            })
            ->addColumn('actions', function ($sheet) {
                $actions = '<div class="dropdown d-inline-block">
                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-more-fill align-middle"></i>
                    </button>
                    <ul class="dropdown-menu">';

                if (hasAccess("measurement_sheet", "print")) {
                    $sheet_number = !empty($sheet->measurement_sheet_no) ? '_' . str_replace('/', '_', $sheet->measurement_sheet_no) : "";
                    $cust_name = !empty($sheet->customer_name) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $sheet->customer_name) : "";
                    $pdfName   = 'Measurement_Sheet' . $sheet_number . $cust_name;
                    $encodedId = base64_encode($sheet->id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=measurement_sheet");
                    $actions .= '<li><a class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }

                if (hasAccess("measurement_sheet", "edit")) {
                    $actions .= '<li><a class="dropdown-item edit-sheet" href="javascript:void(0)" data-id="' . $sheet->id . '"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("measurement_sheet", "delete")) {
                    $actions .= '<li><a class="dropdown-item delete-sheet" href="javascript:void(0)" data-id="' . $sheet->id . '"><i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete</a></li>';
                }

                $actions .= '</ul></div>';
                return $actions;
            });

        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'measurement_sheet');

        return $dataTable
            ->rawColumns(['actions', 'last_by', 'last_on', 'created_by', 'created_on'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();

        $request->validate([
            'measurement_sheet_date' => 'required',
            'customer_id' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $existNumber = MeasurementSheet::where([
                ['measurement_sheet_sequence', $request->measurement_sheet_sequence],
                ['measurement_sheet_no', $request->measurement_sheet_no],
                ['year_id', $year_data->id],
                ['current_location_id', $location_data->location_id]
            ])->first();

            if ($existNumber) {
                $latestNo = $this->getLatestMeasurementSheetNumber($request);
                $area = json_decode($latestNo->getContent(), true);
                $sheet_no = $area['latest_no'];
                $sheet_seq = $area['number'];
            } else {
                $sheet_no = $request->measurement_sheet_no;
                $sheet_seq = $request->measurement_sheet_sequence;
            }

            $page_id = getMenuIdBassedOnDisplayName('measurement_sheet');
            $assign_format_no = $page_id ? getAssignFormateNoForTransaction($location_data->location_id, $page_id->id, $request->measurement_sheet_date) : null;

            $sheet = MeasurementSheet::create([
                'current_location_id' => $location_data->location_id,
                'measurement_sheet_sequence' => $sheet_seq,
                'measurement_sheet_no' => $sheet_no,
                'measurement_sheet_date' => Date::createFromFormat('d/m/Y', $request->measurement_sheet_date)->format('Y-m-d'),
                'customer_id' => $request->customer_id,
                'total_ir_192_sqin' => floatval($request->total_ir_192_sqin ?? 0),
                'total_co_60_sqin' => floatval($request->total_co_60_sqin ?? 0),
                'total_x_ray_sqin' => floatval($request->total_x_ray_sqin ?? 0),
                'total_ir_192_repair_sqin' => floatval($request->total_ir_192_repair_sqin ?? 0),
                'total_co_60_repair_sqin' => floatval($request->total_co_60_repair_sqin ?? 0),
                'total_x_ray_repair_sqin' => floatval($request->total_x_ray_repair_sqin ?? 0),
                'sp_note' => $request->sp_note,
                'assign_format_no' => $assign_format_no,
                'prepared_by_user_id' => $request->prepared_by_user_id ?? Auth::user()->id,
                'company_id' => $location_data->company_id,
                'year_id' => $year_data->id,
                'created_by' => Auth::user()->id,
                'created_on' => now(),
            ]);

            $details = is_string($request->details) ? json_decode($request->details, true) : ($request->details ?? []);
            foreach ($details as $row) {
                if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;

                $rt_id = $row['test_report_rt_id'] ?? 0;
                $row_sqin = floatval($row['ir_192_sqin'] ?? 0) + floatval($row['co_60_sqin'] ?? 0) + floatval($row['x_ray_sqin'] ?? 0) + floatval($row['ir_192_repair_sqin'] ?? 0) + floatval($row['co_60_repair_sqin'] ?? 0) + floatval($row['x_ray_repair_sqin'] ?? 0);

                $rt_report = DB::table('test_report_rt')
                    ->where('test_report_rt_id', $rt_id)
                    ->first();

                if ($rt_report) {
                    $rt_total_sqin = (float)(
                        ($rt_report->mms_ir_192_sqin ?? 0) +
                        ($rt_report->mms_co_60_sqin ?? 0) +
                        ($rt_report->mms_x_ray_sqin ?? 0) +
                        ($rt_report->mms_ir_192_repair_sqin ?? 0) +
                        ($rt_report->mms_co_60_repair_sqin ?? 0) +
                        ($rt_report->mms_x_ray_repair_sqin ?? 0)
                    );

                    if ((float)$row_sqin != (float)$rt_total_sqin) {
                        DB::rollBack();
                        return response()->json([
                            'response_code'    => '0',
                            'response_message' => "You Can't Insert, Measurement Sheet (Sq.In) Is Not Matched With Test Report RT (Sq.In).",
                        ]);
                    }
                }

                $qtyCheck = $this->qtyValidation($rt_id, $request->customer_id, $location_data->location_id, $row_sqin, 'I');
                if ($qtyCheck) {
                    return $qtyCheck;
                }

                MeasurementSheetDetails::create([
                    'measurement_sheet_id' => $sheet->measurement_sheet_id,
                    'test_report_rt_id' => $row['test_report_rt_id'],
                    'material_inward_details_id' => $row['material_inward_details_id'] ?? null,
                    'ir_192_sqin' => floatval($row['ir_192_sqin'] ?? 0),
                    'co_60_sqin' => floatval($row['co_60_sqin'] ?? 0),
                    'x_ray_sqin' => floatval($row['x_ray_sqin'] ?? 0),
                    'ir_192_repair_sqin' => floatval($row['ir_192_repair_sqin'] ?? 0),
                    'co_60_repair_sqin' => floatval($row['co_60_repair_sqin'] ?? 0),
                    'x_ray_repair_sqin' => floatval($row['x_ray_repair_sqin'] ?? 0),
                    'remark' => $row['remark'] ?? '',
                ]);
            }

            DB::commit();

            // Print PDF
            $sheet_number = !empty($sheet->measurement_sheet_no) ? '_' . str_replace('/', '_', $sheet->measurement_sheet_no) : "";
            $cust = DB::table('customers')->where('id', $sheet->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Measurement_Sheet' . $sheet_number . $cust_name;

            if (function_exists('GeneratePdf')) {
                GeneratePdf($sheet->measurement_sheet_id, $pdf_name, 'measurement_sheet', 'add');
            }
            $encodedId = base64_encode($sheet->measurement_sheet_id);
            $url = hasAccess("measurement_sheet", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=measurement_sheet") : "";

            return response()->json([
                'response_code' => '1',
                'url' => $url,
                'response_message' => getResponseMessage('store_success'),
            ]);
        } catch (\Exception $e) {
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
        $sheet_data = DB::select('CALL measurement_sheet_master(?)', [$request->id]);
        if (!empty($sheet_data)) {
            $sheet_data = $sheet_data[0];

            $sheet_data->measurement_sheet_date = ($sheet_data->measurement_sheet_date != "" && $sheet_data->measurement_sheet_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $sheet_data->measurement_sheet_date)->format('d/m/Y')
                : "";
             if(isset($sheet_data->cmp_logo))
            {
                $sheet_data->cmp_logo = base64_encode($sheet_data->cmp_logo);
            }

            $sheet_number = !empty($sheet_data->measurement_sheet_no) ? '_' . str_replace('/', '_', $sheet_data->measurement_sheet_no) : "";
            $cust = DB::table('customers')->where('id', $sheet_data->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $sheet_data->pdf_name = 'Measurement_Sheet' . $sheet_number . $cust_name;

            $details = DB::select('CALL measurement_sheet_details(?)', [$request->id]);
            foreach ($details as $row) {
                $row->mode = 'Update';
                $row->test_report_date = ($row->test_report_date != "" && $row->test_report_date != "0000-00-00")
                    ? Date::createFromFormat('Y-m-d', $row->test_report_date)->format('d/m/Y')
                    : "";
            }

            return response()->json([
                'sheet_data' => $sheet_data,
                'details_data' => $details,
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
        $request->validate([
            'measurement_sheet_date' => 'required',
            'customer_id' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $sheet = MeasurementSheet::find($request->id);
            if (!$sheet) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Record Not Found',
                ]);
            }

            $sheet->update([
                'measurement_sheet_sequence' => $request->measurement_sheet_sequence,
                'measurement_sheet_no' => $request->measurement_sheet_no,
                'measurement_sheet_date' => Date::createFromFormat('d/m/Y', $request->measurement_sheet_date)->format('Y-m-d'),
                'customer_id' => $request->customer_id,
                'total_ir_192_sqin' => floatval($request->total_ir_192_sqin ?? 0),
                'total_co_60_sqin' => floatval($request->total_co_60_sqin ?? 0),
                'total_x_ray_sqin' => floatval($request->total_x_ray_sqin ?? 0),
                'total_ir_192_repair_sqin' => floatval($request->total_ir_192_repair_sqin ?? 0),
                'total_co_60_repair_sqin' => floatval($request->total_co_60_repair_sqin ?? 0),
                'total_x_ray_repair_sqin' => floatval($request->total_x_ray_repair_sqin ?? 0),
                'sp_note' => $request->sp_note,
                'prepared_by_user_id' => $request->prepared_by_user_id ?? Auth::user()->id,
                'last_by' => Auth::user()->id,
                'last_on' => now(),
            ]);

            $location_data = getCurrentLocation();
            $details = is_string($request->details) ? json_decode($request->details, true) : ($request->details ?? []);
            foreach ($details as $row) {
                $detailId = $row['measurement_sheet_details_id'] ?? null;
                $mode = $row['mode'] ?? '';

                if ($mode === 'Delete') {
                    if ($detailId) {
                        MeasurementSheetDetails::destroy($detailId);
                    }
                } elseif ($mode === 'Update' || $mode === 'New') {
                    $rt_id = $row['test_report_rt_id'] ?? 0;
                    $new_row_sqin = floatval($row['ir_192_sqin'] ?? 0) + floatval($row['co_60_sqin'] ?? 0) + floatval($row['x_ray_sqin'] ?? 0) + floatval($row['ir_192_repair_sqin'] ?? 0) + floatval($row['co_60_repair_sqin'] ?? 0) + floatval($row['x_ray_repair_sqin'] ?? 0);
                    
                    $rt_report = DB::table('test_report_rt')
                        ->where('test_report_rt_id', $rt_id)
                        ->first();

                    if ($rt_report) {
                        $rt_total_sqin = (float)(
                            ($rt_report->mms_ir_192_sqin ?? 0) +
                            ($rt_report->mms_co_60_sqin ?? 0) +
                            ($rt_report->mms_x_ray_sqin ?? 0) +
                            ($rt_report->mms_ir_192_repair_sqin ?? 0) +
                            ($rt_report->mms_co_60_repair_sqin ?? 0) +
                            ($rt_report->mms_x_ray_repair_sqin ?? 0)
                        );

                        if ((float)$new_row_sqin != (float)$rt_total_sqin) {
                            DB::rollBack();
                            return response()->json([
                                'response_code'    => '0',
                                'response_message' => "You Can't Update, Measurement Sheet (Sq.In) Is Not Matched With Test Report RT (Sq.In).",
                            ]);
                        }
                    }

                    $old_row_sqin = 0;
                    if ($detailId) {
                        $oldDetail = MeasurementSheetDetails::find($detailId);
                        if ($oldDetail) {
                            $old_row_sqin = floatval($oldDetail->ir_192_sqin ?? 0) + floatval($oldDetail->co_60_sqin ?? 0) + floatval($oldDetail->x_ray_sqin ?? 0) + floatval($oldDetail->ir_192_repair_sqin ?? 0) + floatval($oldDetail->co_60_repair_sqin ?? 0) + floatval($oldDetail->x_ray_repair_sqin ?? 0);
                        }
                    }

                    $diff_sqin = $new_row_sqin - $old_row_sqin;
                    if ($diff_sqin > 0) {
                        $qtyCheck = $this->qtyValidation($rt_id, $request->customer_id, $location_data->location_id, $diff_sqin, 'U');
                        if ($qtyCheck) {
                            return $qtyCheck;
                        }
                    }

                    $data = [
                        'measurement_sheet_id' => $sheet->measurement_sheet_id,
                        'test_report_rt_id' => $row['test_report_rt_id'],
                        'material_inward_details_id' => $row['material_inward_details_id'] ?? null,
                        'ir_192_sqin' => floatval($row['ir_192_sqin'] ?? 0),
                        'co_60_sqin' => floatval($row['co_60_sqin'] ?? 0),
                        'x_ray_sqin' => floatval($row['x_ray_sqin'] ?? 0),
                        'ir_192_repair_sqin' => floatval($row['ir_192_repair_sqin'] ?? 0),
                        'co_60_repair_sqin' => floatval($row['co_60_repair_sqin'] ?? 0),
                        'x_ray_repair_sqin' => floatval($row['x_ray_repair_sqin'] ?? 0),
                        'remark' => $row['remark'] ?? '',
                    ];

                    if ($detailId) {
                        MeasurementSheetDetails::where('measurement_sheet_details_id', $detailId)->update($data);
                    } else {
                        MeasurementSheetDetails::create($data);
                    }
                }
            }

            DB::commit();

            // Print PDF
            $sheet_number = !empty($sheet->measurement_sheet_no) ? '_' . str_replace('/', '_', $sheet->measurement_sheet_no) : "";
            $cust = DB::table('customers')->where('id', $sheet->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Measurement_Sheet' . $sheet_number . $cust_name;

            if (function_exists('GeneratePdf')) {
                GeneratePdf($sheet->measurement_sheet_id, $pdf_name, 'measurement_sheet', 'update');
            }
            $encodedId = base64_encode($sheet->measurement_sheet_id);
            $url = hasAccess("measurement_sheet", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=measurement_sheet") : "";

            return response()->json([
                'response_code' => '1',
                'url' => $url,
                'response_message' => getResponseMessage('update_success'),
            ]);
        } catch (\Exception $e) {
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
            $sheet = MeasurementSheet::find($request->id);
            if ($sheet) {
                MeasurementSheetDetails::where('measurement_sheet_id', $sheet->measurement_sheet_id)->delete();
                $sheet->delete();
                DB::commit();
                return response()->json([
                    'response_code' => '1',
                    'response_message' => getResponseMessage('delete_success'),
                ]);
            }
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Not Found',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'response_code' => '0',
                'response_message' => getResponseMessage('delete_error'),
                'original_error' => $e->getMessage()
            ]);
        }
    }

    // public function getPendingRtReports(Request $request)
    // {
    //     $customer_id = $request->customer_id;
    //     $location_data = getCurrentLocation();
    //     $year_data = getCurrentYearData();

    //     $start_date = $request->input('start_date');
    //     $end_date = $request->input('end_date');

    //     $whereClause = "
    //         WHERE pm.customer_id = ?
    //           AND pm.current_location_id = ?
    //           AND tr.year_id <= ?
    //     ";
    //     $bindings = [$customer_id, $location_data->location_id, $year_data->id];

    //     if (!empty($start_date)) {
    //         $formatted_start = Date::createFromFormat('d/m/Y', $start_date)->format('Y-m-d');
    //         $whereClause .= " AND tr.test_report_date >= ?";
    //         $bindings[] = $formatted_start;
    //     }

    //     if (!empty($end_date)) {
    //         $formatted_end = Date::createFromFormat('d/m/Y', $end_date)->format('Y-m-d');
    //         $whereClause .= " AND tr.test_report_date <= ?";
    //         $bindings[] = $formatted_end;
    //     }

    //     $query = "
    //         SELECT 
    //             tr.test_report_rt_id,
    //             tr.test_report_no,
    //             tr.test_report_date,
    //             tr.rt_no,
    //             tr.heat_no,
    //             m.material,
    //             toj.type_of_job,
    //             jd.job_description,
    //             tr.part_no,
    //             tr.drg_no,
    //             tr.product_code,
    //             tr.nabl_type_fix,
    //             tr.job_type_fix,
    //             IFNULL(mi.dc_no, '') AS customer_dc_no,
    //             c.customer AS customer_name,
    //             IFNULL(tr.mms_ir_192_sqin, 0.00) AS mms_ir_192_sqin,
    //             IFNULL(tr.mms_co_60_sqin, 0.00) AS mms_co_60_sqin,
    //             IFNULL(tr.mms_x_ray_sqin, 0.00) AS mms_x_ray_sqin,
    //             IFNULL(tr.mms_ir_192_repair_sqin, 0.00) AS mms_ir_192_repair_sqin,
    //             IFNULL(tr.mms_co_60_repair_sqin, 0.00) AS mms_co_60_repair_sqin,
    //             IFNULL(tr.mms_x_ray_repair_sqin, 0.00) AS mms_x_ray_repair_sqin,
    //             tr.material_inward_details_id
    //         FROM pending_rt_reports_for_measurement AS pm
    //         JOIN test_report_rt AS tr ON tr.test_report_rt_id = pm.report_id
    //         LEFT JOIN material_inward_details AS mid ON mid.material_inward_details_id = tr.material_inward_details_id
    //         LEFT JOIN material_inward AS mi ON mi.material_inward_id = mid.material_inward_id
    //         LEFT JOIN customers AS c ON c.id = tr.customer_id
    //         LEFT JOIN materials AS m ON m.id = tr.material_id
    //         LEFT JOIN type_of_job AS toj ON toj.id = tr.type_of_job_id
    //         LEFT JOIN job_descriptions AS jd ON jd.id = tr.job_desc_id
    //         {$whereClause}
    //     ";
        
    //     $pending = DB::select($query, $bindings);

    //     foreach ($pending as $row) {
    //         $row->test_report_date = ($row->test_report_date != "" && $row->test_report_date != "0000-00-00")
    //             ? Date::createFromFormat('Y-m-d', $row->test_report_date)->format('d/m/Y')
    //             : "";
    //     }

    //     return response()->json([
    //         'response_code' => 1,
    //         'reports' => $pending
    //     ]);
    // }

    public function getPendingRtReports(Request $request)
    {
        $location = getCurrentLocation()->location_id;
        $year = getCompanyYearIdsToTill();

        $query = DB::table('pending_rt_reports_for_measurement as pm')
            ->select([
                'tr.test_report_rt_id','tr.test_report_no','tr.revision_number','tr.test_report_date','tr.rt_no','tr.heat_no','m.material','toj.type_of_job','jd.job_description','tr.part_no','tr.drg_no','tr.product_code','tr.nabl_type_fix','tr.job_type_fix','mi.dc_no as customer_dc_no','c.customer as customer_name','tr.mms_ir_192_sqin','tr.mms_co_60_sqin','tr.mms_x_ray_sqin','tr.mms_ir_192_repair_sqin','tr.mms_co_60_repair_sqin','tr.mms_x_ray_repair_sqin','tr.material_inward_details_id'
            ])
            ->join('test_report_rt as tr', 'tr.test_report_rt_id', '=', 'pm.report_id')
            ->leftJoin('material_inward_details as mid', 'mid.material_inward_details_id', '=', 'tr.material_inward_details_id')
            ->leftJoin('material_inward as mi', 'mi.material_inward_id', '=', 'mid.material_inward_id')
            ->leftJoin('customers as c', 'c.id', '=', 'tr.customer_id')
            ->leftJoin('materials as m', 'm.id', '=', 'tr.material_id')
            ->leftJoin('type_of_job as toj', 'toj.id', '=', 'tr.type_of_job_id')
            ->leftJoin('job_descriptions as jd', 'jd.id', '=', 'tr.job_desc_id')
            ->where('pm.customer_id', $request->customer_id)
            ->where('pm.current_location_id', $location)
            ->whereIn('tr.year_id', $year)
            ->where('pm.total_sqin', '>', 0);

        if ($request->filled('start_date')) {
            $formatted_start = Date::createFromFormat('d/m/Y', $request->start_date)->format('Y-m-d');
            $query->where('tr.test_report_date', '>=', $formatted_start);
        }

        if ($request->filled('end_date')) {
            $formatted_end = Date::createFromFormat('d/m/Y', $request->end_date)->format('Y-m-d');
            $query->where('tr.test_report_date', '<=', $formatted_end);
        }

        $reports = $query->get();

        foreach ($reports as $row) {
            $row->test_report_date = (!empty($row->test_report_date) && $row->test_report_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $row->test_report_date)->format('d/m/Y')
                : "";
        }

        return response()->json([
            'response_code' => 1,
            'reports' => $reports
        ]);
    }

    public function getPendingCustomers(Request $request)
    {
        $location = getCurrentLocation()->location_id;
        $year = getCompanyYearIdsToTill();

        $sheetId = $request->input('sheet_id');
        $currentCustomerId = null;
        if ($sheetId) {
            $currentCustomerId = DB::table('measurement_sheet')->where('measurement_sheet_id', $sheetId)->value('customer_id');
        }

        $currentCustomer = null;
        if ($currentCustomerId) {
            $currentCustomer = DB::table('customers')
                ->where('id', $currentCustomerId)
                ->select('id', 'customer')
                ->first();
        }

        $customers = DB::table('customers')
            ->join('pending_rt_reports_for_measurement as pm', 'pm.customer_id', '=', 'customers.id')
            ->join('test_report_rt as tr', 'tr.test_report_rt_id', '=', 'pm.report_id')
            ->where('pm.current_location_id', $location)
            ->whereIn('tr.year_id', $year)
            ->where('pm.total_sqin', '>', 0)
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

    public function getLatestMeasurementSheetNumber(Request $request)
    {
        $modal = MeasurementSheet::class;
        $sequence = 'measurement_sheet_sequence';
        $prefix = 'MS';

        $num_format = getLatestSequence($modal, $sequence, $prefix);

        return response()->json([
            'response_code' => 1,
            'latest_no' => $num_format['format'],
            'number' => $num_format['isFound'],
            'date' => date(DATE_FORMAT)
        ]);
    }

    public function qtyValidation($test_report_rt_id, $customer_id, $location_id, $required_sqin, $transaction_mode = 'I')
    {
        if ($required_sqin > 0 && !empty($test_report_rt_id)) {
            DB::table('test_report_rt')
                ->where('test_report_rt_id', $test_report_rt_id)
                ->lockForUpdate()
                ->first();

            $pending_sqin = (float) (DB::table('pending_rt_reports_for_measurement')
                ->where('report_id', $test_report_rt_id)
                ->where('customer_id', $customer_id)
                ->where('current_location_id', $location_id)
                ->value('total_sqin') ?? 0);

            if ($pending_sqin < $required_sqin) {
                DB::rollBack();
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => "Test Report RT (Sq.In) is already used.",
                ]);
            }
        }

        return null;
    }
}
