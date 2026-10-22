<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Film;
use App\Models\FilmBrand;
use App\Models\State;
use App\Models\Unit;
use App\Models\Transaction\Invoice;
use App\Models\Transaction\InvoiceDetails;
use App\Models\Transaction\FilmDc;
use App\Models\Transaction\FilmDcDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    /**
     * Display Manage Invoice View
     */
    public function manage()
    {
        return view('manage.transaction.manage-invoice');
    }

    /**
     * Yajra DataTable Server-Side Listing
     */
    public function index(Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $current_location = getCurrentLocation()->location_id;

        $invoice_query = DB::table('invoices')
            ->select([
                'invoices.invoice_id',
                'invoices.invoice_sequence',
                'invoices.invoice_no',
                'invoices.invoice_date',
                'invoices.ref_no',
                'invoices.ref_date',
                'invoices.job_type_fix',
                'invoices.source_type',
                'invoices.tax_type',
                'customers.customer_code',
                'customers.customer as customer_name',
                'invoices.customer_client',
                'invoices.pos_customer',
                'states.state as pos_state_name',
                'states.state_code as pos_state_code',
                'invoices.basic_amount',
                'invoices.discount_amount',
                'invoices.value_of_goods',
                'invoices.sgst_amount',
                'invoices.cgst_amount',
                'invoices.igst_amount',
                'invoices.other_charges',
                'invoices.sub_total',
                'invoices.round_off',
                'invoices.net_amount',
                'invoices.due_date',
                'created_user.person_name as created_by_name',
                'last_user.person_name as last_by_name',
                'invoices.created_on',
                'invoices.created_by',
                'invoices.last_by',
                'invoices.last_on',
            ])
            ->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
            ->leftJoin('states', 'states.id', '=', 'invoices.pos_state_id')
            ->leftJoin('admin as created_user', 'created_user.id', '=', 'invoices.created_by')
            ->leftJoin('admin as last_user', 'last_user.id', '=', 'invoices.last_by')
            ->where('invoices.year_id', $year_data->id)
            ->where('invoices.current_location_id', $current_location);

        $dataTable = DataTables::of($invoice_query)
            ->filterColumn('invoices.invoice_no', function ($query, $keyword) {
                applynumberprefix($query, $keyword, 'invoices.invoice_no');
            })
            ->editColumn('invoice_date', function ($row) {
                return $row->invoice_date ? Date::createFromFormat('Y-m-d', $row->invoice_date)->format(DATE_FORMAT) : '';
            })
            ->filterColumn('invoices.invoice_date', function ($q, $k) {
                applyDate($q, $k, 'invoices.invoice_date');
            })
            ->editColumn('due_date', function ($row) {
                return $row->due_date ? Date::createFromFormat('Y-m-d', $row->due_date)->format(DATE_FORMAT) : '';
            })
            ->filterColumn('invoices.due_date', function ($q, $k) {
                applyDate($q, $k, 'invoices.due_date');
            })
            ->editColumn('basic_amount', function ($row) {
                return number_format(floatval($row->basic_amount ?? 0), 2, '.', '');
            })
            ->editColumn('value_of_goods', function ($row) {
                return number_format(floatval($row->value_of_goods ?? 0), 2, '.', '');
            })
            ->editColumn('net_amount', function ($row) {
                return number_format(floatval($row->net_amount ?? 0), 2, '.', '');
            })
            ->addColumn('options', function ($row) {
                $action = '<div class="dropdown d-inline-block">
                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-more-fill align-middle"></i>
                    </button>
                    <ul class="dropdown-menu">';

                if (hasAccess('invoice', 'print')) {
                    $inv_no_fmt = !empty($row->invoice_no) ? '_' . str_replace('/', '_', $row->invoice_no) : "";
                    $cust_name_fmt = !empty($row->customer_name) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $row->customer_name) : "";
                    $pdfName = 'Invoice' . $inv_no_fmt . $cust_name_fmt;
                    $encodedId = base64_encode($row->invoice_id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=invoice");
                    $action .= '<li><a class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }
                if (hasAccess('invoice', 'edit')) {
                    $action .= '<li><a href="javascript:void(0);" class="dropdown-item edit-invoice" data-id="' . $row->invoice_id . '"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }
                if (hasAccess('invoice', 'delete')) {
                    $action .= '<li><a href="javascript:void(0);" class="dropdown-item remove-item-btn delete-invoice" data-id="' . $row->invoice_id . '" data-name="' . $row->invoice_no . '"><i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete</a></li>';
                }
                $action .= '</ul></div>';
                return $action;
            });

        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'invoices');
        return $dataTable->rawColumns(['options', 'created_by', 'created_on', 'last_by', 'last_on'])->make(true);
    }

    /**
     * Get Latest Invoice Sequence & Number Format
     */
    public function getLatestInvoiceSequence(Request $request)
    {
        $modal = Invoice::class;
        $sequence = 'invoice_sequence';
        $prefix = 'UE';

        $num_format = getLatestSequence($modal, $sequence, $prefix);

        return response()->json([
            'response_code' => 1,
            'latest_no'     => $num_format['format'],
            'number'        => $num_format['isFound'],
        ]);
    }

    /**
     * Check Invoice Sequence Number Duplication
     */
    public function checkInvoiceSequenceDuplication(Request $request)
    {
        $modal = Invoice::class;
        $seq = 'invoice_sequence';
        $sequence = $request->input('invoice_sequence');
        $id = $request->input('id');
        $table_id = 'invoice_id';
        $prefix = 'UE';

        $result = duplicationSequence($modal, $seq, $sequence, $id, $table_id, $prefix);

        if ($result['format'] == 0) {
            return response()->json([
                'response_code'    => 0,
                'response_message' => 'Duplicate Bill No. Found.'
            ]);
        }

        return response()->json([
            'response_code' => 1,
            'latest_no'     => $result['format'],
            'number'        => $sequence
        ]);
    }

    /**
     * Get Customer Place of Supply and Billing Details
     */
    public function getCustomerDetails(Request $request)
    {
        $customerId = $request->input('customer_id');
        if (empty($customerId)) {
            return response()->json(['response_code' => 0, 'data' => null]);
        }

        $customer = DB::table('customers')
            ->select([
                'customers.id',
                'customers.customer',
                'customers.address',
                'customers.city_id',
                'customers.gstin',
                'customers.pan',
                'customers.credit_days',
                'cities.state_id',
                'states.state',
                'states.state_code'
            ])
            ->leftJoin('cities', 'cities.id', '=', 'customers.city_id')
            ->leftJoin('states', 'states.id', '=', 'cities.state_id')
            ->where('customers.id', $customerId)
            ->first();

        if ($customer) {
            return response()->json([
                'response_code' => 1,
                'data' => [
                    'pos_customer'   => $customer->customer ?? '',
                    'pos_address'    => $customer->address ?? '',
                    'pos_state_id'   => $customer->state_id ?? null,
                    'pos_state_code' => $customer->state_code ?? '',
                    'pos_gstin_no'   => $customer->gstin ?? '',
                    'pos_pan_no'     => $customer->pan ?? '',
                    'due_days'       => $customer->credit_days ?? 0,
                ]
            ]);
        }

        return response()->json(['response_code' => 0, 'data' => null]);
    }

    /**
     * Get Pending Film DCs for Selected Customer & Filters
     */
    public function getPendingFilmDc(Request $request)
    {
        $customerId = $request->input('customer_id');
        $jobType = $request->input('job_type_fix');
        $filmSizeUnit = $request->input('film_size_unit_fix');
        $editInvoiceId = $request->input('edit_invoice_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $location = getCurrentLocation()->location_id;
        $year_data = getCurrentYearData();

        if (empty($customerId)) {
            return response()->json([
                'response_code' => 1,
                'film_dcs'      => []
            ]);
        }

        $query = DB::table('film_dc as fd')
            ->select([
                'fd.film_dc_id',
                'fd.film_dc_sequence',
                'fd.film_dc_no',
                'fd.film_dc_date',
                'fd.job_type_fix',
                'fd.film_size_unit_fix',
                'fd.customer_client',
                'fd.total_qty',
                'fd.total_square_inch',
                'fd.film_size_for_print',
                'fd.sp_note',
                'c.customer as customer_name'
            ])
            ->join('customers as c', 'c.id', '=', 'fd.customer_id')
            ->where('fd.current_location_id', $location)
            ->where('fd.customer_id', $customerId);

        if (!empty($jobType)) {
            $query->where('fd.job_type_fix', $jobType);
        }

        if (!empty($filmSizeUnit)) {
            $query->where('fd.film_size_unit_fix', $filmSizeUnit);
        }

        if (!empty($startDate)) {
            $query->where('fd.film_dc_date', '>=', Date::createFromFormat('d/m/Y', $startDate)->format('Y-m-d'));
        }
        if (!empty($endDate)) {
            $query->where('fd.film_dc_date', '<=', Date::createFromFormat('d/m/Y', $endDate)->format('Y-m-d'));
        }

        // Exclude already billed Film DCs unless part of the current edit invoice
        if (!empty($editInvoiceId)) {
            $currentInvoiceDcIds = DB::table('invoice_details')
                ->where('invoice_id', $editInvoiceId)
                ->whereNotNull('film_dc_id')
                ->pluck('film_dc_id')
                ->toArray();

            $billedDcIds = DB::table('invoice_details')
                ->where('invoice_id', '!=', $editInvoiceId)
                ->whereNotNull('film_dc_id')
                ->pluck('film_dc_id')
                ->toArray();

            if (!empty($billedDcIds)) {
                $query->whereNotIn('fd.film_dc_id', $billedDcIds);
            }
        } else {
            $billedDcIds = DB::table('invoice_details')
                ->whereNotNull('film_dc_id')
                ->pluck('film_dc_id')
                ->toArray();

            if (!empty($billedDcIds)) {
                $query->whereNotIn('fd.film_dc_id', $billedDcIds);
            }
        }

        $filmDcs = $query->orderBy('fd.film_dc_date', 'desc')
            ->orderBy('fd.film_dc_id', 'desc')
            ->get();

        foreach ($filmDcs as $row) {
            $row->film_dc_date = (!empty($row->film_dc_date) && $row->film_dc_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $row->film_dc_date)->format('d/m/Y')
                : "";
            $row->party_name = !empty($row->customer_client) ? $row->customer_client : ($row->customer_name ?? '');
        }

        return response()->json([
            'response_code' => 1,
            'film_dcs'      => $filmDcs
        ]);
    }

    /**
     * Get Breakdown of Items / Films from Selected Film DCs
     */
    public function getFilmDcItemsForInvoice(Request $request)
    {
        $dcIds = $request->input('film_dc_ids', []);
        if (is_string($dcIds)) {
            $dcIds = json_decode($dcIds, true) ?: [];
        }

        if (empty($dcIds)) {
            return response()->json([
                'response_code' => 1,
                'items'         => []
            ]);
        }

        $details = DB::table('film_dc_details as fdd')
            ->select([
                'fdd.film_dc_detail_id',
                'fdd.film_dc_id',
                'fd.film_dc_no',
                'fd.film_dc_date',
                'fdd.test_report_no',
                'fdd.die_no',
                'fdd.film_brand_id',
                'fb.film_brand as film_brand_name',
                'fdd.film_id',
                'f.film_size_inch',
                'f.film_size_cm',
                'fdd.dc_quantity as film_qty',
                'fdd.remark'
            ])
            ->join('film_dc as fd', 'fd.film_dc_id', '=', 'fdd.film_dc_id')
            ->leftJoin('film_brand as fb', 'fb.film_brand_id', '=', 'fdd.film_brand_id')
            ->leftJoin('film as f', 'f.film_id', '=', 'fdd.film_id')
            ->whereIn('fdd.film_dc_id', $dcIds)
            ->get();

        $items = [];
        foreach ($details as $row) {
            $formatted_dc_date = (!empty($row->film_dc_date) && $row->film_dc_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $row->film_dc_date)->format('d/m/Y')
                : "";

            $filmSize = $row->film_size_inch ?: ($row->film_size_cm ?: '');

            $items[] = [
                'mode'              => 'New',
                'invoice_detail_id' => 0,
                'film_dc_id'        => $row->film_dc_id,
                'film_dc_detail_id' => $row->film_dc_detail_id,
                'dc_no'             => $row->film_dc_no ?? '',
                'dc_date'           => $formatted_dc_date,
                'test_report_no'    => $row->test_report_no ?? '',
                'description'       => $row->remark ?? '',
                'film_brand_id'     => $row->film_brand_id ?? null,
                'film_brand_name'   => $row->film_brand_name ?? '',
                'film_id'           => $row->film_id ?? null,
                'film_size'         => $filmSize,
                'film_qty'          => floatval($row->film_qty ?? 0),
                'qty'               => floatval($row->film_qty ?? 1),
                'unit_id'           => null,
                'unit_name'         => 'Nos',
                'rate'              => 0.00,
                'amount'            => 0.00,
                'remark'            => $row->remark ?? '',
            ];
        }

        return response()->json([
            'response_code' => 1,
            'items'         => $items
        ]);
    }

    /**
     * Store New Invoice
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoice_sequence' => 'required',
            'invoice_date'     => 'required',
            'customer_id'      => 'required',
            'job_type_fix'     => 'required',
            'source_type'      => 'required',
        ]);

        $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();

        DB::beginTransaction();
        try {
            $existNumber = DB::table('invoices')->where([
                ['invoice_sequence', $request->invoice_sequence],
                ['year_id', $year_data->id],
                ['current_location_id', $location_data->location_id]
            ])->first();

            if ($existNumber) {
                $latestNo = $this->getLatestInvoiceSequence($request);
                $inv_seq = $latestNo->original['number'];
                $inv_no = $latestNo->original['latest_no'];
            } else {
                $inv_seq = $request->invoice_sequence;
                $inv_no = $request->invoice_no;
            }

            $ref_date = null;
            if (!empty($request->ref_date)) {
                $ref_date = Date::createFromFormat('d/m/Y', $request->ref_date)->format('Y-m-d');
            }

            $due_date = null;
            if (!empty($request->due_date)) {
                $due_date = Date::createFromFormat('d/m/Y', $request->due_date)->format('Y-m-d');
            }

            $sgst_percent = floatval($request->sgst_percentage ?? $request->sgst_percent ?? 0);
            $cgst_percent = floatval($request->cgst_percentage ?? $request->cgst_percent ?? 0);
            $igst_percent = floatval($request->igst_percentage ?? $request->igst_percent ?? 0);
            $round_off = floatval($request->round_off_val ?? $request->round_off ?? 0);

            $invoice_id = DB::table('invoices')->insertGetId([
                'current_location_id' => $location_data->location_id,
                'invoice_sequence'    => $inv_seq,
                'invoice_no'          => $inv_no,
                'invoice_date'        => Date::createFromFormat('d/m/Y', $request->invoice_date)->format('Y-m-d'),
                'ref_title'           => $request->ref_title ?? 'Ref. No.',
                'ref_no'              => $request->ref_no,
                'ref_date'            => $ref_date,
                'job_type_fix'        => $request->job_type_fix ?? 'Casting',
                'film_size_unit_fix'  => $request->film_size_unit_fix ?? 'inch',
                'source_type'         => $request->source_type ?? 'From Film DC',
                'customer_id'         => $request->customer_id,
                'customer_client'     => $request->customer_client,
                'tax_type'            => $request->tax_type ?? 'SGST + CGST',
                'gst_type_fix_id'     => $request->gst_type_fix_id ?? 1,
                'sac_id'              => $request->sac_id ?: null,
                'pos_customer'        => $request->pos_customer,
                'pos_address'         => $request->pos_address,
                'pos_state_id'        => $request->pos_state_id ?: null,
                'pos_gstin_no'        => $request->pos_gstin_no,
                'pos_pan_no'          => $request->pos_pan_no,
                'basic_amount'        => floatval($request->basic_amount ?? 0),
                'discount_amount'     => floatval($request->discount_amount ?? 0),
                'value_of_goods'      => floatval($request->value_of_goods ?? 0),
                'sgst_percent'        => $sgst_percent,
                'sgst_amount'         => floatval($request->sgst_amount ?? 0),
                'cgst_percent'        => $cgst_percent,
                'cgst_amount'         => floatval($request->cgst_amount ?? 0),
                'igst_percent'        => $igst_percent,
                'igst_amount'         => floatval($request->igst_amount ?? 0),
                'other_charges'       => floatval($request->other_charges ?? 0),
                'sub_total'           => floatval($request->sub_total ?? 0),
                'round_off'           => $round_off,
                'net_amount'          => floatval($request->net_amount ?? 0),
                'due_days'            => intval($request->due_days ?? 0),
                'due_date'            => $due_date,
                'terms_and_conditions'=> $request->terms_and_conditions ?? null,
                'sp_note'             => $request->sp_note ?? null,
                'prepared_by_user_id' => $request->prepared_by_user_id ?: Auth::user()->id,
                'company_id'          => $location_data->company_id,
                'year_id'             => $year_data->id,
                'created_by'          => Auth::user()->id,
                'created_on'          => now(),
            ]);

            $details = is_string($request->details) ? json_decode($request->details, true) : ($request->details ?? []);
            foreach ($details as $row) {
                if (empty($row) || ($row['mode'] ?? '') === 'Delete') {
                    continue;
                }

                $dc_date = null;
                if (!empty($row['dc_date'])) {
                    $dc_date = Date::createFromFormat('d/m/Y', $row['dc_date'])->format('Y-m-d');
                }

                DB::table('invoice_details')->insert([
                    'invoice_id'        => $invoice_id,
                    'film_dc_id'        => $row['film_dc_id'] ?? null,
                    'film_dc_detail_id' => $row['film_dc_detail_id'] ?? null,
                    'dc_no'             => $row['dc_no'] ?? null,
                    'dc_date'           => $dc_date,
                    'test_report_no'    => $row['test_report_no'] ?? null,
                    'description'       => $row['description'] ?? null,
                    'film_brand_id'     => $row['film_brand_id'] ?? null,
                    'film_id'           => $row['film_id'] ?? null,
                    'film_size'         => $row['film_size'] ?? null,
                    'film_qty'          => floatval($row['film_qty'] ?? 0),
                    'qty'               => floatval($row['qty'] ?? 1),
                    'unit_id'           => $row['unit_id'] ?? null,
                    'unit_name'         => $row['unit_name'] ?? null,
                    'rate'              => floatval($row['rate'] ?? 0),
                    'amount'            => floatval($row['amount'] ?? 0),
                    'remark'            => $row['remark'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'response_code'    => '1',
                'response_message' => 'Record Inserted.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'response_code'    => '0',
                'response_message' => 'Error while inserting record.',
                'original_error'   => $e->getMessage()
            ]);
        }
    }

    /**
     * Edit / Fetch Invoice Data
     */
    public function edit(Request $request)
    {
        $id = $request->input('id');

        $invoice = DB::table('invoices')
            ->select([
                'invoices.*',
                'customers.customer as customer_name',
                'states.state as pos_state_name',
                'states.state_code as pos_state_code'
            ])
            ->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
            ->leftJoin('states', 'states.id', '=', 'invoices.pos_state_id')
            ->where('invoices.invoice_id', $id)
            ->first();

        if (empty($invoice)) {
            return response()->json([
                'response_code'    => '0',
                'response_message' => 'Record Does Not Exist'
            ]);
        }

        $invoice->invoice_date = (!empty($invoice->invoice_date) && $invoice->invoice_date != "0000-00-00")
            ? Date::createFromFormat('Y-m-d', $invoice->invoice_date)->format('d/m/Y')
            : "";

        $invoice->ref_date = (!empty($invoice->ref_date) && $invoice->ref_date != "0000-00-00")
            ? Date::createFromFormat('Y-m-d', $invoice->ref_date)->format('d/m/Y')
            : "";

        $invoice->due_date = (!empty($invoice->due_date) && $invoice->due_date != "0000-00-00")
            ? Date::createFromFormat('Y-m-d', $invoice->due_date)->format('d/m/Y')
            : "";

        $details = DB::table('invoice_details as ind')
            ->select([
                'ind.*',
                'fb.film_brand as film_brand_name',
                'u.unit as unit_display_name'
            ])
            ->leftJoin('film_brand as fb', 'fb.film_brand_id', '=', 'ind.film_brand_id')
            ->leftJoin('unit as u', 'u.id', '=', 'ind.unit_id')
            ->where('ind.invoice_id', $id)
            ->get();

        $details_data = [];
        foreach ($details as $d) {
            $formatted_dc_date = (!empty($d->dc_date) && $d->dc_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $d->dc_date)->format('d/m/Y')
                : "";

            $details_data[] = [
                'invoice_detail_id' => $d->invoice_detail_id,
                'invoice_id'        => $d->invoice_id,
                'film_dc_id'        => $d->film_dc_id,
                'film_dc_detail_id' => $d->film_dc_detail_id,
                'dc_no'             => $d->dc_no ?? '',
                'dc_date'           => $formatted_dc_date,
                'test_report_no'    => $d->test_report_no ?? '',
                'description'       => $d->description ?? '',
                'film_brand_id'     => $d->film_brand_id,
                'film_brand_name'   => $d->film_brand_name ?? '',
                'film_id'           => $d->film_id,
                'film_size'         => $d->film_size ?? '',
                'film_qty'          => floatval($d->film_qty ?? 0),
                'qty'               => floatval($d->qty ?? 1),
                'unit_id'           => $d->unit_id,
                'unit_name'         => $d->unit_name ?: ($d->unit_display_name ?? 'Nos'),
                'rate'              => floatval($d->rate ?? 0),
                'amount'            => floatval($d->amount ?? 0),
                'remark'            => $d->remark ?? '',
                'mode'              => 'Update'
            ];
        }

        return response()->json([
            'response_code' => 1,
            'invoice'       => $invoice,
            'details'       => $details_data
        ]);
    }

    /**
     * Update Existing Invoice
     */
    public function update(Request $request)
    {
        $request->validate([
            'id'               => 'required',
            'invoice_sequence' => 'required',
            'invoice_date'     => 'required',
            'customer_id'      => 'required',
            'job_type_fix'     => 'required',
            'source_type'      => 'required',
        ]);

        $invoice_id = $request->input('id');
        $location_data = getCurrentLocation();

        DB::beginTransaction();
        try {
            $ref_date = null;
            if (!empty($request->ref_date)) {
                $ref_date = Date::createFromFormat('d/m/Y', $request->ref_date)->format('Y-m-d');
            }

            $due_date = null;
            if (!empty($request->due_date)) {
                $due_date = Date::createFromFormat('d/m/Y', $request->due_date)->format('Y-m-d');
            }

            $sgst_percent = floatval($request->sgst_percentage ?? $request->sgst_percent ?? 0);
            $cgst_percent = floatval($request->cgst_percentage ?? $request->cgst_percent ?? 0);
            $igst_percent = floatval($request->igst_percentage ?? $request->igst_percent ?? 0);
            $round_off = floatval($request->round_off_val ?? $request->round_off ?? 0);

            DB::table('invoices')->where('invoice_id', $invoice_id)->update([
                'invoice_sequence'    => $request->invoice_sequence,
                'invoice_no'          => $request->invoice_no,
                'invoice_date'        => Date::createFromFormat('d/m/Y', $request->invoice_date)->format('Y-m-d'),
                'ref_title'           => $request->ref_title ?? 'Ref. No.',
                'ref_no'              => $request->ref_no,
                'ref_date'            => $ref_date,
                'job_type_fix'        => $request->job_type_fix ?? 'Casting',
                'film_size_unit_fix'  => $request->film_size_unit_fix ?? 'inch',
                'source_type'         => $request->source_type ?? 'From Film DC',
                'customer_id'         => $request->customer_id,
                'customer_client'     => $request->customer_client,
                'tax_type'            => $request->tax_type ?? 'SGST + CGST',
                'gst_type_fix_id'     => $request->gst_type_fix_id ?? 1,
                'sac_id'              => $request->sac_id ?: null,
                'pos_customer'        => $request->pos_customer,
                'pos_address'         => $request->pos_address,
                'pos_state_id'        => $request->pos_state_id ?: null,
                'pos_gstin_no'        => $request->pos_gstin_no,
                'pos_pan_no'          => $request->pos_pan_no,
                'basic_amount'        => floatval($request->basic_amount ?? 0),
                'discount_amount'     => floatval($request->discount_amount ?? 0),
                'value_of_goods'      => floatval($request->value_of_goods ?? 0),
                'sgst_percent'        => $sgst_percent,
                'sgst_amount'         => floatval($request->sgst_amount ?? 0),
                'cgst_percent'        => $cgst_percent,
                'cgst_amount'         => floatval($request->cgst_amount ?? 0),
                'igst_percent'        => $igst_percent,
                'igst_amount'         => floatval($request->igst_amount ?? 0),
                'other_charges'       => floatval($request->other_charges ?? 0),
                'sub_total'           => floatval($request->sub_total ?? 0),
                'round_off'           => $round_off,
                'net_amount'          => floatval($request->net_amount ?? 0),
                'due_days'            => intval($request->due_days ?? 0),
                'due_date'            => $due_date,
                'terms_and_conditions'=> $request->terms_and_conditions ?? null,
                'sp_note'             => $request->sp_note ?? null,
                'prepared_by_user_id' => $request->prepared_by_user_id ?: Auth::user()->id,
                'last_by'             => Auth::user()->id,
                'last_on'             => now(),
            ]);

            $details = is_string($request->details) ? json_decode($request->details, true) : ($request->details ?? []);
            $processedDetailIds = [];

            foreach ($details as $row) {
                if (empty($row)) continue;

                $detailId = $row['invoice_detail_id'] ?? 0;
                $mode = $row['mode'] ?? '';

                if ($mode === 'Delete' && !empty($detailId)) {
                    DB::table('invoice_details')->where('invoice_detail_id', $detailId)->delete();
                    continue;
                }

                if ($mode === 'Delete') {
                    continue;
                }

                $dc_date = null;
                if (!empty($row['dc_date'])) {
                    $dc_date = Date::createFromFormat('d/m/Y', $row['dc_date'])->format('Y-m-d');
                }

                $detailData = [
                    'invoice_id'        => $invoice_id,
                    'film_dc_id'        => $row['film_dc_id'] ?? null,
                    'film_dc_detail_id' => $row['film_dc_detail_id'] ?? null,
                    'dc_no'             => $row['dc_no'] ?? null,
                    'dc_date'           => $dc_date,
                    'test_report_no'    => $row['test_report_no'] ?? null,
                    'description'       => $row['description'] ?? null,
                    'film_brand_id'     => $row['film_brand_id'] ?? null,
                    'film_id'           => $row['film_id'] ?? null,
                    'film_size'         => $row['film_size'] ?? null,
                    'film_qty'          => floatval($row['film_qty'] ?? 0),
                    'qty'               => floatval($row['qty'] ?? 1),
                    'unit_id'           => $row['unit_id'] ?? null,
                    'unit_name'         => $row['unit_name'] ?? null,
                    'rate'              => floatval($row['rate'] ?? 0),
                    'amount'            => floatval($row['amount'] ?? 0),
                    'remark'            => $row['remark'] ?? null,
                ];

                if (!empty($detailId)) {
                    DB::table('invoice_details')->where('invoice_detail_id', $detailId)->update($detailData);
                    $processedDetailIds[] = $detailId;
                } else {
                    $newId = DB::table('invoice_details')->insertGetId($detailData);
                    $processedDetailIds[] = $newId;
                }
            }

            DB::commit();

            return response()->json([
                'response_code'    => '1',
                'response_message' => 'Record Updated.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'response_code'    => '0',
                'response_message' => 'Error while updating record.',
                'original_error'   => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete Invoice
     */
    public function destroy(Request $request)
    {
        $id = $request->input('id');

        DB::beginTransaction();
        try {
            DB::table('invoice_details')->where('invoice_id', $id)->delete();
            DB::table('invoices')->where('invoice_id', $id)->delete();

            DB::commit();

            return response()->json([
                'response_code'    => '1',
                'response_message' => 'Record Deleted.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'response_code'    => '0',
                'response_message' => 'Error while deleting record.',
                'original_error'   => $e->getMessage()
            ]);
        }
    }
}
