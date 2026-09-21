<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Transaction\CustomerDCNonReturnable;
use App\Models\Transaction\CustomerDCNonReturnableDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class CustomerDCNonReturnableController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-customer_dc_non_returnable');
    }

    public function index(Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $current_location = getCurrentLocation()->location_id;

        $dc_data = CustomerDCNonReturnableDetails::select([
            'customer_dc_non_returnable.customer_dc_non_returnable_id as dc_id',
            'customer_dc_non_returnable.customer_dc_non_returnable_sequence',
            'customer_dc_non_returnable.customer_dc_non_returnable_no',
            'customer_dc_non_returnable.customer_dc_non_returnable_date',
            'customers.customer_code',
            'customers.customer',
            'material_inward.material_inward_no as mi_number',
            'material_inward.material_inward_date as mi_date',
            'material_inward.dc_no as mi_challan_number',
            'material_inward.dc_date as mi_challan_date',
            'material_inward.po_no as mi_po_number',
            'material_inward.po_date as mi_po_date',
            'material_inward_details.type_of_testing_id_fix as type_of_test',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            'material_inward_details.part_no',
            'material_inward_details.drg_no',
            'materials.material',
            'materials.material as material_name',
            'material_inward_details.heat_no as mid_heat_no',
            'material_inward_details.rt_no as mid_rt_no',
            'material_inward_details.product_code as mid_product_code',
            'material_inward_details.quantity as in_qty',
            'customer_dc_non_returnable_details.dc_qty',
            'customer_dc_non_returnable_details.remark',
            'prepared_by.person_name as prepared_by',
            'customer_dc_non_returnable.created_on',
            'customer_dc_non_returnable.created_by',
            'customer_dc_non_returnable.last_by',
            'customer_dc_non_returnable.last_on',
        ])
        ->leftJoin('customer_dc_non_returnable', 'customer_dc_non_returnable.customer_dc_non_returnable_id', '=', 'customer_dc_non_returnable_details.customer_dc_non_returnable_id')
        ->leftJoin('material_inward_details', 'material_inward_details.material_inward_details_id', '=', 'customer_dc_non_returnable_details.material_inward_details_id')
        ->leftJoin('material_inward', 'material_inward.material_inward_id', '=', 'material_inward_details.material_inward_id')
        ->leftJoin('customers', 'customers.id', '=', 'customer_dc_non_returnable.customer_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'material_inward_details.type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'material_inward_details.job_desc_id')
        ->leftJoin('materials', 'materials.id', '=', 'material_inward_details.material_id')
        ->leftJoin('admin as prepared_by', 'prepared_by.id', '=', 'customer_dc_non_returnable.prepared_by_user_id')
        ->where('customer_dc_non_returnable.year_id', $year_data->id)
        ->where('customer_dc_non_returnable.current_location_id', $current_location);

        $dataTable = DataTables::of($dc_data)
            ->filterColumn('customer_dc_non_returnable.customer_dc_non_returnable_no', function ($query, $keyword) {
                applynumberprefix($query, $keyword, 'customer_dc_non_returnable.customer_dc_non_returnable_no');
            })
            ->editColumn('customer_dc_non_returnable_date', function ($row) {
                return $row->customer_dc_non_returnable_date ? Date::createFromFormat('Y-m-d', $row->customer_dc_non_returnable_date)->format(DATE_FORMAT) : '';
            })
            ->filterColumn('customer_dc_non_returnable.customer_dc_non_returnable_date', function ($q, $k) {
                applyDate($q, $k, 'customer_dc_non_returnable.customer_dc_non_returnable_date');
            })
            ->editColumn('mi_date', function ($row) {
                return $row->mi_date ? Date::createFromFormat('Y-m-d', $row->mi_date)->format(DATE_FORMAT) : '';
            })
            ->filterColumn('material_inward.material_inward_date', function ($q, $k) {
                applyDate($q, $k, 'material_inward.material_inward_date');
            })
            ->editColumn('mi_challan_date', function ($row) {
                return $row->mi_challan_date ? Date::createFromFormat('Y-m-d', $row->mi_challan_date)->format(DATE_FORMAT) : '';
            })
            ->editColumn('mi_po_date', function ($row) {
                return $row->mi_po_date ? Date::createFromFormat('Y-m-d', $row->mi_po_date)->format(DATE_FORMAT) : '';
            })
            ->addColumn('options', function ($row) {
                $action = '<div class="dropdown d-inline-block">
                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-more-fill align-middle"></i>
                    </button>
                    <ul class="dropdown-menu">';

                if (hasAccess('customer_dc_non_returnable', 'print')) {
                    $dc_number_fmt = !empty($row->customer_dc_non_returnable_no) ? '_' . str_replace('/', '_', $row->customer_dc_non_returnable_no) : "";
                    $customer_name_fmt = !empty($row->customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $row->customer) : "";
                    $pdfName = 'Customer_DC_Non_Returnable' . $dc_number_fmt . $customer_name_fmt;
                    $encodedId = base64_encode($row->dc_id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=customer_dc_non_returnable");
                    $action .= '<li><a class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }
                if (hasAccess('customer_dc_non_returnable', 'edit')) {
                    $action .= '<li><a href="javascript:void(0);" class="dropdown-item edit-customer_dc_non_returnable" data-id="' . $row->dc_id . '"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }
                if (hasAccess('customer_dc_non_returnable', 'delete')) {
                    $action .= '<li><a href="javascript:void(0);" class="dropdown-item remove-item-btn delete-customer_dc_non_returnable" data-id="' . $row->dc_id . '" data-name="' . $row->customer_dc_non_returnable_no . '"><i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete</a></li>';
                }
                $action .= '</ul></div>';
                return $action;
            });

        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'customer_dc_non_returnable');
        return $dataTable->rawColumns(['options', 'created_by', 'created_on', 'last_by', 'last_on'])->make(true);
    }

    public function getLatestDCSequence(Request $request)
    {
        $modal = CustomerDCNonReturnable::class;
        $sequence = 'customer_dc_non_returnable_sequence';
        $prefix = 'NRDC';
        $sup_num_format = getLatestSequence($modal, $sequence, $prefix);

        return response()->json([
            'response_code' => 1,
            'latest_no'     => $sup_num_format['format'],
            'number'        => $sup_num_format['isFound'],
        ]);
    }

    public function getPendingCustomers()
    {
        $yearIds = getCompanyYearIdsToTill();
        $current_location = getCurrentLocation()->location_id;

        $customers = Customer::select('customers.id', 'customers.customer', 'customers.customer_code')
            ->join('material_inward as mi', 'mi.customer_id', '=', 'customers.id')
            ->join('material_inward_details as mid', 'mid.material_inward_id', '=', 'mi.material_inward_id')
            ->join('pending_at_lab_customer_dc_non_returnable_qty as pend', 'pend.material_inward_details_id', '=', 'mid.material_inward_details_id')
            ->where('pend.current_location_id', $current_location)
            ->whereIn('mi.year_id', $yearIds)
            ->where('pend.pending_qty', '>', 0)
            ->groupBy('customers.id', 'customers.customer', 'customers.customer_code')
            ->distinct()
            ->orderBy('customers.customer', 'asc')
            ->get();

        return response()->json([
            'response_code' => 1,
            'customers' => $customers
        ]);
    }

    public function getPendingInwardList(Request $request)
    {
        $customer_id = $request->customer_id;
        $edit_dc_id = $request->dc_id ?? 0;
        $yearIds = getCompanyYearIdsToTill();
        $current_location = getCurrentLocation()->location_id;

        $query = DB::table('pending_at_lab_customer_dc_non_returnable_qty as pend')
            ->join('material_inward_details as mid', 'mid.material_inward_details_id', '=', 'pend.material_inward_details_id')
            ->join('material_inward as mi', 'mi.material_inward_id', '=', 'mid.material_inward_id')
            ->leftJoin('type_of_job', 'type_of_job.id', '=', 'mid.type_of_job_id')
            ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'mid.job_desc_id')
            ->leftJoin('materials', 'materials.id', '=', 'mid.material_id')
            ->select([
                'pend.material_inward_details_id',
                'mi.material_inward_no as mi_number',
                'mi.material_inward_date as mi_date',
                'mi.dc_no as mi_challan_number',
                'mi.dc_date as mi_challan_date',
                'mi.po_no as mi_po_number',
                'mi.po_date as mi_po_date',
                'mid.type_of_testing_id_fix as type_of_test',
                'type_of_job.type_of_job',
                'job_descriptions.job_description',
                'mid.part_no',
                'mid.drg_no',
                'materials.material',
                'materials.material as material_name',
                'mid.heat_no as mid_heat_no',
                'mid.rt_no as mid_rt_no',
                'mid.product_code as mid_product_code',
                'mid.quantity as in_qty',
                $edit_dc_id 
                    ? DB::raw("(pend.pending_qty + IFNULL((SELECT dcd.dc_qty FROM customer_dc_non_returnable_details dcd WHERE dcd.customer_dc_non_returnable_id = {$edit_dc_id} AND dcd.material_inward_details_id = pend.material_inward_details_id), 0)) as pend_qty")
                    : 'pend.pending_qty as pend_qty'
            ])
            ->where('mi.customer_id', $customer_id)
            ->where('pend.current_location_id', $current_location)
            ->whereIn('mi.year_id', $yearIds);

        if ($edit_dc_id) {
            $query->whereRaw("(pend.pending_qty + IFNULL((SELECT dcd.dc_qty FROM customer_dc_non_returnable_details dcd WHERE dcd.customer_dc_non_returnable_id = {$edit_dc_id} AND dcd.material_inward_details_id = pend.material_inward_details_id), 0)) > 0");
        } else {
            $query->where('pend.pending_qty', '>', 0);
        }

        $pending_list = $query->get();

        foreach ($pending_list as $item) {
            $item->mi_date = $item->mi_date ? Date::createFromFormat('Y-m-d', $item->mi_date)->format(DATE_FORMAT) : '';
            $item->mi_challan_date = $item->mi_challan_date ? Date::createFromFormat('Y-m-d', $item->mi_challan_date)->format(DATE_FORMAT) : '';
            $item->mi_po_date = $item->mi_po_date ? Date::createFromFormat('Y-m-d', $item->mi_po_date)->format(DATE_FORMAT) : '';

            $chk = $this->checkTestReportStatus($item->material_inward_details_id);
            $item->is_report_done = $chk['status'] ? 1 : 0;
            $item->report_pending_msg = $chk['status'] ? '' : $chk['message'];
        }

        return response()->json([
            'response_code' => 1,
            'data' => $pending_list
        ]);
    }

    private function checkTestReportStatus($material_inward_details_id)
    {
        $mid = DB::table('material_inward_details as mid')
            ->join('material_inward as mi', 'mi.material_inward_id', '=', 'mid.material_inward_id')
            ->leftJoin('materials', 'materials.id', '=', 'mid.material_id')
            ->select('mid.*', 'mi.material_inward_no as mi_number', 'materials.material as material_name')
            ->where('mid.material_inward_details_id', $material_inward_details_id)
            ->first();

        if (!$mid) {
            return ['status' => true];
        }

        $test_type = strtoupper(trim($mid->type_of_testing_id_fix ?? ''));
        $hasReport = false;

        if (strpos($test_type, 'RT') !== false) {
            $hasReport = DB::table('test_report_rt')->where('material_inward_details_id', $material_inward_details_id)->exists();
        } elseif (strpos($test_type, 'UT') !== false) {
            $hasReport = DB::table('test_report_ut')->where('material_inward_details_id', $material_inward_details_id)->exists();
        } elseif (strpos($test_type, 'MPT') !== false) {
            $hasReport = DB::table('test_report_mpt')->where('material_inward_details_id', $material_inward_details_id)->exists();
        } elseif (strpos($test_type, 'DPT') !== false) {
            $hasReport = DB::table('test_report_dpt')->where('material_inward_details_id', $material_inward_details_id)->exists();
        } else {
            $hasReport = DB::table('test_report_rt')->where('material_inward_details_id', $material_inward_details_id)->exists()
                || DB::table('test_report_ut')->where('material_inward_details_id', $material_inward_details_id)->exists()
                || DB::table('test_report_mpt')->where('material_inward_details_id', $material_inward_details_id)->exists()
                || DB::table('test_report_dpt')->where('material_inward_details_id', $material_inward_details_id)->exists();
        }

        if (!$hasReport) {
            return [
                'status' => false,
                'message' => "Test Report Has Not Been Processed For Material Inward."
            ];
        }

        return ['status' => true];
    }

    public function store(Request $request)
    {
        $year_data = getCurrentYearData();
        $current_location_id = getCurrentLocation()->location_id;

        DB::beginTransaction();
        try {
            $existNumber = CustomerDCNonReturnable::where([
                ['customer_dc_non_returnable_sequence', $request->customer_dc_non_returnable_sequence],
                ['year_id', $year_data->id],
                ['current_location_id', $current_location_id]
            ])->first();

            if ($existNumber) {
                $latestNo = $this->getLatestDCSequence($request);
                $tmp = $latestNo->getContent();
                $area = json_decode($tmp, true);
                $dc_number = $area['latest_no'];
                $dc_sequence = $area['number'];
            } else {
                $dc_number = $request->customer_dc_non_returnable_no;
                $dc_sequence = $request->customer_dc_non_returnable_sequence;
            }

            $page_id = getMenuIdBassedOnDisplayName('customer_dc_non_returnable');
            $assign_format_no = isset($page_id->id) ? getAssignFormateNoForTransaction($current_location_id, $page_id->id, $request->customer_dc_non_returnable_date) : null;

            $dc = CustomerDCNonReturnable::create([
                'current_location_id' => $current_location_id,
                'customer_dc_non_returnable_sequence' => $dc_sequence,
                'customer_dc_non_returnable_no' => $dc_number,
                'customer_dc_non_returnable_date' => isset($request->customer_dc_non_returnable_date) ? Date::createFromFormat('d/m/Y', $request->customer_dc_non_returnable_date)->format('Y-m-d') : null,
                'customer_id' => $request->customer_id,
                'total_qty' => $request->total_qty ?? 0,
                'mode_of_transport' => $request->mode_of_transport,
                'transporter' => $request->transporter,
                'vehicle_no' => $request->vehicle_no ?? $request->vehical_no,
                'sp_note' => $request->sp_note,
                'assign_format_no' => $assign_format_no,
                'prepared_by_user_id' => $request->prepared_by_user_id ?? Auth::user()->id,
                'company_id' => Auth::user()->company_id,
                'year_id' => $year_data->id,
                'created_by' => Auth::user()->id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
            ]);

            $details = json_decode($request->dc_details_data, true);
            if (!empty($details)) {
                foreach ($details as $row) {
                    if (empty($row) || ($row['mode'] ?? '') === 'Delete') continue;
                    if (isset($row['material_inward_details_id']) && floatval($row['dc_qty']) > 0) {
                        $from_qty = floatval($row['dc_qty'] ?? 0);
                        $next_qty = 0;
                        $transaction_mode = 'I';
                        $detail_id = 0;
                        $mid_id = $row['material_inward_details_id'] ?? null;

                        $checkQty = $this->qtyValidation($from_qty, $next_qty, $transaction_mode, $detail_id, $mid_id);
                        if ($checkQty) {
                            return $checkQty;
                        }

                        CustomerDCNonReturnableDetails::create([
                            'customer_dc_non_returnable_id' => $dc->customer_dc_non_returnable_id,
                            'material_inward_details_id' => $row['material_inward_details_id'],
                            'dc_qty' => intval($row['dc_qty']),
                            'remark' => $row['remark'] ?? null,
                        ]);
                    }
                }
            }
         DB::commit();

            $dc_number_fmt = !empty($dc_number) ? '_' . str_replace('/', '_', $dc_number) : "";
            $get_customer = Customer::find($request->customer_id);
            $customer_name_fmt = !empty($get_customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $get_customer->customer) : "";
            $pdf_name = 'Customer_DC_Non_Returnable' . $dc_number_fmt . $customer_name_fmt;

            if (function_exists('GeneratePdf')) {
                GeneratePdf($dc->customer_dc_non_returnable_id, $pdf_name, 'customer_dc_non_returnable', 'add');
            }

            $encodedId = base64_encode($dc->customer_dc_non_returnable_id);
            $url = hasAccess('customer_dc_non_returnable', 'print') ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=customer_dc_non_returnable") : '';

           
            return response()->json([
                'response_code' => 1,
                'url' => $url,
                'response_message' => getResponseMessage('store_success'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'response_code' => 0,
                'response_message' => getResponseMessage('store_error'),
                'original_error' => $e->getMessage()
            ]);
        }
    }

    public function edit(Request $request)
    {
        $id = $request->id;
        $master = DB::select("CALL customer_dc_non_returnable_master(?)", [$id]);

        if (empty($master)) {
            return response()->json(['response_code' => 0, 'response_message' => 'Record not found.']);
        }

        $dc = $master[0];
        $dc->customer_dc_non_returnable_date = ($dc->customer_dc_non_returnable_date != "" && $dc->customer_dc_non_returnable_date != "0000-00-00") ? Date::createFromFormat('Y-m-d', $dc->customer_dc_non_returnable_date)->format(DATE_FORMAT) : '';

        if (isset($dc->company_logo) && !empty($dc->company_logo)) {
            $dc->company_logo = base64_encode($dc->company_logo);
        }

        $dc_number_fmt = !empty($dc->customer_dc_non_returnable_no) ? '_' . str_replace('/', '_', $dc->customer_dc_non_returnable_no) : "";
        $customer_name_fmt = !empty($dc->customer_name ?? $dc->customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $dc->customer_name ?? $dc->customer) : "";
        $pdf_name = 'Customer_DC_Non_Returnable' . $dc_number_fmt . $customer_name_fmt;
        $dc->pdf_name = $pdf_name;

        $details = DB::select("CALL customer_dc_non_returnable_details(?)", [$id]);

        foreach ($details as $row) {
            $row->mi_date = ($row->mi_date != "" && $row->mi_date != "0000-00-00") ? Date::createFromFormat('Y-m-d', $row->mi_date)->format(DATE_FORMAT) : '';
            $row->mi_challan_date = ($row->mi_challan_date != "" && $row->mi_challan_date != "0000-00-00") ? Date::createFromFormat('Y-m-d', $row->mi_challan_date)->format(DATE_FORMAT) : '';
            $row->mi_po_date = ($row->mi_po_date != "" && $row->mi_po_date != "0000-00-00") ? Date::createFromFormat('Y-m-d', $row->mi_po_date)->format(DATE_FORMAT) : '';

            $chk = $this->checkTestReportStatus($row->material_inward_details_id);
            $row->is_report_done = $chk['status'] ? 1 : 0;
            $row->report_pending_msg = $chk['status'] ? '' : $chk['message'];

            foreach ($row as $key => $value) {
                if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                    $row->$key = base64_encode($value);
                }
            }
        }

        $encodedId = base64_encode($id);
        $url = hasAccess('customer_dc_non_returnable', 'print') ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=customer_dc_non_returnable") : '';

        return response()->json([
            'response_code' => 1,
            'url' => $url,
            'dc_data' => $dc,
            'dc_details_data' => $details
        ]);
    }

    public function update(Request $request)
    {
        $id = $request->id;
        $dc = CustomerDCNonReturnable::find($id);

        if (!$dc) {
            return response()->json(['response_code' => 0, 'response_message' => 'Record not found.']);
        }

        DB::beginTransaction();
        try {
            $year_data = getCurrentYearData();
            $current_location_id = getCurrentLocation()->location_id;

            $existSequence = CustomerDCNonReturnable::where('customer_dc_non_returnable_sequence', $request->customer_dc_non_returnable_sequence)
                ->where('year_id', $year_data->id)
                ->where('current_location_id', $current_location_id)
                ->where('customer_dc_non_returnable_id', '!=', $id)
                ->first();

            if ($existSequence) {
                return response()->json([
                    'response_code' => 0,
                    'response_message' => 'Duplicate DC No. Found.'
                ]);
            }

            $dc_date = Date::createFromFormat(DATE_FORMAT, $request->customer_dc_non_returnable_date)->format('Y-m-d');

            $dc->update([
                'customer_dc_non_returnable_sequence' => intval($request->customer_dc_non_returnable_sequence),
                'customer_dc_non_returnable_no' => $request->customer_dc_non_returnable_no,
                'customer_dc_non_returnable_date' => $dc_date,
                'customer_id' => $request->customer_id,
                'total_qty' => $request->total_qty ?? 0,
                'mode_of_transport' => $request->mode_of_transport,
                'transporter' => $request->transporter,
                'vehicle_no' => $request->vehicle_no ?? $request->vehical_no,
                'sp_note' => $request->sp_note,
                // 'prepared_by_user_id' => $request->prepared_by_user_id ?? Auth::user()->id,
                'last_by' => Auth::user()->id,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
            ]);

            $details = json_decode($request->dc_details_data, true);
            if (!empty($details)) {
                foreach ($details as $row) {
                    if (empty($row)) continue;

                    $mode = $row['mode'] ?? '';
                    $detail_id = $row['customer_dc_non_returnable_details_id'] ?? null;

                    if ($mode === 'Delete') {
                        if (!empty($detail_id)) {
                            CustomerDCNonReturnableDetails::where('customer_dc_non_returnable_details_id', $detail_id)->delete();
                        }
                        continue;
                    }

                    $from_qty = floatval($row['dc_qty'] ?? 0);
                    $next_qty = 0;
                    $transaction_mode = ($mode === 'Insert' || empty($detail_id)) ? 'I' : 'U';
                    $mid_id = $row['material_inward_details_id'] ?? null;

                    $checkQty = $this->qtyValidation($from_qty, $next_qty, $transaction_mode, $detail_id, $mid_id);
                    if ($checkQty) {
                        return $checkQty;
                    }

                    $detailData = [
                        'customer_dc_non_returnable_id' => $id,
                        'material_inward_details_id' => $row['material_inward_details_id'] ?? null,
                        'dc_qty' => intval($row['dc_qty'] ?? 0),
                        'remark' => $row['remark'] ?? null,
                    ];

                    if ($mode === 'Insert' || (empty($mode) && empty($detail_id))) {
                        if (isset($row['material_inward_details_id']) && floatval($row['dc_qty']) > 0) {
                            CustomerDCNonReturnableDetails::create($detailData);
                        }
                    } elseif ($mode === 'Update' || (empty($mode) && !empty($detail_id))) {
                        if (!empty($detail_id)) {
                            CustomerDCNonReturnableDetails::where('customer_dc_non_returnable_details_id', $detail_id)->update([
                                'material_inward_details_id' => $row['material_inward_details_id'] ?? null,
                                'dc_qty' => intval($row['dc_qty'] ?? 0),
                                'remark' => $row['remark'] ?? null,
                            ]);
                        }
                    } elseif ($mode === 'Delete') {
                        if (!empty($detail_id)) {
                            CustomerDCNonReturnableDetails::where('customer_dc_non_returnable_details_id', $detail_id)->delete();
                        }
                    }
                }
            }
          DB::commit();

            $dc_number_fmt = !empty($request->customer_dc_non_returnable_no) ? '_' . str_replace('/', '_', $request->customer_dc_non_returnable_no) : "";
            $get_customer = Customer::find($request->customer_id);
            $customer_name_fmt = !empty($get_customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $get_customer->customer) : "";
            $pdf_name = 'Customer_DC_Non_Returnable' . $dc_number_fmt . $customer_name_fmt;

            if (function_exists('GeneratePdf')) {
                GeneratePdf($id, $pdf_name, 'customer_dc_non_returnable', 'edit');
            }

            $encodedId = base64_encode($id);
            $url = hasAccess('customer_dc_non_returnable', 'print') ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=customer_dc_non_returnable") : '';

            
            return response()->json([
                'response_code' => 1,
                'url' => $url,
                'response_message' => getResponseMessage('update_success'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'response_code' => 0,
                'response_message' => getResponseMessage('update_error'),
                'original_error' => $e->getMessage()
            ]);
        }
    }

    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            CustomerDCNonReturnableDetails::where('customer_dc_non_returnable_id', $request->id)->delete();
            CustomerDCNonReturnable::where('customer_dc_non_returnable_id', $request->id)->delete();

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

    public function getLNRData(Request $request)
    {
        $LocationData = getCurrentLocation();
        $customer_id = $request->customer_id;

        $query = CustomerDCNonReturnable::where('current_location_id', $LocationData->location_id);

        if (!empty($customer_id)) {
            $query->where('customer_id', $customer_id);
        }

        $lnrData = $query->orderBy('customer_dc_non_returnable_id', 'desc')->first();

        return response()->json([
            'response_code' => 1,
            'lnr_data' => $lnrData
        ]);
    }

    public function getVehicleNoList(Request $request)
    {
        return $this->existsSuggestion('customer_dc_non_returnable', 'vehicle_no', $request->term, 'vehicleNoList', 'vehicle_no');
    }

    public function getModeOfTransportList(Request $request)
    {
        return $this->existsSuggestion('customer_dc_non_returnable', 'mode_of_transport', $request->term, 'modeOfTransportList', 'mode_of_transport');
    }

    public function getSpNoteList(Request $request)
    {
        return $this->existsSuggestion('customer_dc_non_returnable', 'sp_note', $request->term, 'spNoteList', 'sp_note');
    }

    public function getTransporterList(Request $request)
    {
        return $this->existsSuggestion('customer_dc_non_returnable', 'transporter', $request->term, 'transporterList', 'transporter');
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

            if ($location_id) {
                $query->where('current_location_id', $location_id);
            }

            $data = $query->select($column)
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

    public function qtyValidation($from_qty, $next_qty = 0, $transaction_mode = 'I', $customer_dc_non_returnable_details_id = 0, $material_inward_details_id = null)
    {
        $current_location_id = getCurrentLocation()->location_id;

        if ($from_qty > 0 && !empty($material_inward_details_id)) {

            $old_dc_qty = 0;
            if ($transaction_mode === 'U' && !empty($customer_dc_non_returnable_details_id)) {
                $old_dc_qty = DB::table('customer_dc_non_returnable_details')
                    ->where('customer_dc_non_returnable_details_id', $customer_dc_non_returnable_details_id)
                    ->value('dc_qty') ?? 0;
            }

            $pendingQty = (DB::table('pending_at_lab_customer_dc_non_returnable_qty')
                ->where('material_inward_details_id', $material_inward_details_id)
                ->where('current_location_id', $current_location_id)
                ->value('pending_qty') ?? 0) + $old_dc_qty;

            if ((float)$pendingQty < (float)$from_qty) {
                DB::rollBack();
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => 'DC Qty. cannot be more than Pending Qty.',
                ]);
            }
        }

        return null;
    }
}

