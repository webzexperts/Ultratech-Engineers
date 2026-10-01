<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Film;
use App\Models\FilmBrand;
use App\Models\Transaction\FilmDc;
use App\Models\Transaction\FilmDcDetails;
use App\Models\Transaction\TestReportRt;
use App\Models\Transaction\TestReportRtDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class FilmDCController extends Controller
{
    /**
     * Manage View
     */
    public function manage()
    {
        return view('manage.transaction.manage-film_dc');
    }

    /**
     * Yajra DataTable Server-Side Listing
     */
    public function index(Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $current_location = getCurrentLocation()->location_id;

        $dc_data = DB::table('film_dc')
            ->select([
                'film_dc.film_dc_id',
                'film_dc.film_dc_sequence',
                'film_dc.film_dc_no',
                'film_dc.film_dc_date',
                'film_dc.from_type_id',
                'film_dc.job_type_fix',
                'film_dc.film_size_unit_fix',
                'customers.customer as customer_name',
                'film_dc.customer_client',
                'film_dc.total_qty',
                'film_dc.total_square_inch',
                DB::raw("(
                    SELECT GROUP_CONCAT(
                        DISTINCT IF(film_dc.film_size_unit_fix = 'cm', IFNULL(NULLIF(f.film_size_cm, ''), f.film_size_inch), IFNULL(NULLIF(f.film_size_inch, ''), f.film_size_cm))
                        ORDER BY f.film_id SEPARATOR ', '
                    )
                    FROM film_dc_details fdd
                    LEFT JOIN film f ON f.film_id = fdd.film_id
                    WHERE fdd.film_dc_id = film_dc.film_dc_id
                ) as film_size_for_print"),
                'created_user.person_name as created_by_name',
                'last_user.person_name as last_by_name',
                'film_dc.created_on',
                'film_dc.created_by',
                'film_dc.last_by',
                'film_dc.last_on',
            ])
            ->leftJoin('customers', 'customers.id', '=', 'film_dc.customer_id')
            ->leftJoin('admin as created_user', 'created_user.id', '=', 'film_dc.created_by')
            ->leftJoin('admin as last_user', 'last_user.id', '=', 'film_dc.last_by')
            ->where('film_dc.year_id', $year_data->id)
            ->where('film_dc.current_location_id', $current_location);

        $dataTable = DataTables::of($dc_data)
            ->filterColumn('film_dc.film_dc_no', function ($query, $keyword) {
                applynumberprefix($query, $keyword, 'film_dc.film_dc_no');
            })
            ->editColumn('film_dc_date', function ($row) {
                return $row->film_dc_date ? Date::createFromFormat('Y-m-d', $row->film_dc_date)->format(DATE_FORMAT) : '';
            })
            ->filterColumn('film_dc.film_dc_date', function ($q, $k) {
                applyDate($q, $k, 'film_dc.film_dc_date');
            })
            ->editColumn('from_type_id', function ($row) {
                return $row->from_type_id;
            })
            ->filterColumn('film_size_for_print', function ($query, $keyword) {
                $query->whereExists(function ($subQuery) use ($keyword) {
                    $subQuery->select(DB::raw(1))
                        ->from('film_dc_details as fdd')
                        ->leftJoin('film as f', 'f.film_id', '=', 'fdd.film_id')
                        ->whereColumn('fdd.film_dc_id', 'film_dc.film_dc_id')
                        ->where(function ($q) use ($keyword) {
                            $q->where('f.film_size_inch', 'like', "%{$keyword}%")
                              ->orWhere('f.film_size_cm', 'like', "%{$keyword}%");
                        });
                });
            })
            ->filterColumn('film_dc.job_type_fix', function ($query, $keyword) {
                $globalSearch = request()->input('search.value');
                $searchValue = $globalSearch != '' ? $globalSearch : $keyword;
                $lowerKeyword = strtolower(trim($searchValue));
                if ($lowerKeyword === 'welding') {
                    $query->where('film_dc.job_type_fix', '=', 'Welding');
                } elseif ($lowerKeyword === 'non-welding' || $lowerKeyword === 'non welding') {
                    $query->where('film_dc.job_type_fix', '=', 'Non-Welding');
                } else {
                    $query->where('film_dc.job_type_fix', 'like', "{$lowerKeyword}%");
                }
            })
            ->addColumn('options', function ($row) {
                $action = '<div class="dropdown d-inline-block">
                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-more-fill align-middle"></i>
                    </button>
                    <ul class="dropdown-menu">';

                if (hasAccess('film_dc', 'print')) {
                    $dc_no_fmt = !empty($row->film_dc_no) ? '_' . str_replace('/', '_', $row->film_dc_no) : "";
                    $cust_name_fmt = !empty($row->customer_name) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $row->customer_name) : "";
                    $pdfName = 'Film_DC' . $dc_no_fmt . $cust_name_fmt;
                    $encodedId = base64_encode($row->film_dc_id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=film_dc");
                    $action .= '<li><a class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }
                if (hasAccess('film_dc', 'edit')) {
                    $action .= '<li><a href="javascript:void(0);" class="dropdown-item edit-film_dc" data-id="' . $row->film_dc_id . '"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }
                if (hasAccess('film_dc', 'delete')) {
                    $action .= '<li><a href="javascript:void(0);" class="dropdown-item remove-item-btn delete-film_dc" data-id="' . $row->film_dc_id . '" data-name="' . $row->film_dc_no . '"><i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete</a></li>';
                }
                $action .= '</ul></div>';
                return $action;
            });

        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'film_dc');
        return $dataTable->rawColumns(['options', 'created_by', 'created_on', 'last_by', 'last_on'])->make(true);
    }

   

    /**
     * Store Film DC
     */
    public function store(Request $request)
    {
        $request->validate([
            'film_dc_sequence' => 'required',
            'film_dc_date'     => 'required',
            'customer_id'      => 'required',
            'job_type_fix'     => 'required',
            'from_type_id'     => 'required',
        ]);

        $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();

        DB::beginTransaction();
        try {
            $existNumber = DB::table('film_dc')->where([
                ['film_dc_sequence', $request->film_dc_sequence],
                ['year_id', $year_data->id],
                ['current_location_id', $location_data->location_id]
            ])->first();

            if ($existNumber) {
                $latestNo = $this->getLatestDCSequence($request);
                $dc_seq = $latestNo->original['number'];
                $dc_no = $latestNo->original['latest_no'];
            } else {
                $dc_seq = $request->film_dc_sequence;
                $dc_no = $request->film_dc_no;
            }

            $page_id = getMenuIdBassedOnDisplayName('film_dc');
            $assign_format_no = $page_id ? getAssignFormateNoForTransaction($location_data->location_id, $page_id->id, $request->film_dc_date) : null;

            $film_dc_id = DB::table('film_dc')->insertGetId([
                'current_location_id' => $location_data->location_id,
                'film_dc_sequence'    => $dc_seq,
                'film_dc_no'          => $dc_no,
                'film_dc_date'        => Date::createFromFormat('d/m/Y', $request->film_dc_date)->format('Y-m-d'),
                'from_type_id'        => $request->from_type_id ?? 'Test Report RT',
                'job_type_fix'        => $request->job_type_fix ?? 'Non-Welding',
                'film_size_unit_fix'  => $request->film_size_unit_fix ?? 'inch',
                'customer_id'         => $request->customer_id,
                'customer_client'     => $request->customer_client,
                'total_qty'           => floatval($request->total_qty ?? 0),
                'total_square_inch'   => floatval($request->total_square_inch ?? 0),
                'film_size_for_print' => $request->film_size_for_print,
                'sp_note'             => $request->sp_note,
                'assign_format_no'    => $assign_format_no,
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

                $formatted_report_date = null;
                if (!empty($row['test_report_date'])) {
                    $formatted_report_date = Date::createFromFormat('d/m/Y', $row['test_report_date'])->format('Y-m-d');
                }

                $filmId = $row['film_id'] ?? null;
                $dcQty = floatval($row['dc_quantity'] ?? 0);
                $sqIn = isset($row['sq_in']) ? floatval($row['sq_in']) : 0;
                $sqCm = isset($row['sq_cm']) ? floatval($row['sq_cm']) : 0;

                if (($sqIn <= 0 || $sqCm <= 0) && !empty($filmId)) {
                    $filmObj = DB::table('film')->where('film_id', $filmId)->first();
                    if ($filmObj) {
                        if ($sqIn <= 0) {
                            $sqIn = floatval($filmObj->sq_in ?? 0);
                            if ($sqIn <= 0) {
                                $sqIn = $this->calculateFilmSqIn($filmObj->film_size_inch ?? '', 0);
                            }
                        }
                        if ($sqCm <= 0) {
                            $sqCm = floatval($filmObj->sq_cm ?? 0);
                            if ($sqCm <= 0) {
                                $sqCm = $this->calculateFilmSqIn($filmObj->film_size_cm ?? '', 0);
                            }
                        }
                    }
                }

                $totalSqIn = isset($row['total_sq_in']) && floatval($row['total_sq_in']) > 0
                    ? floatval($row['total_sq_in'])
                    : ($sqIn * $dcQty);

                $totalSqCm = isset($row['total_sq_cm']) && floatval($row['total_sq_cm']) > 0
                    ? floatval($row['total_sq_cm'])
                    : ($sqCm * $dcQty);

                DB::table('film_dc_details')->insert([
                    'film_dc_id'        => $film_dc_id,
                    'test_report_rt_id' => $row['test_report_rt_id'] ?? null,
                    'test_report_no'    => $row['test_report_no'] ?? null,
                    'test_report_date'  => $formatted_report_date,
                    'die_no'            => $row['die_no'] ?? null,
                    'film_brand_id'     => $row['film_brand_id'] ?? null,
                    'film_type_id'      => $row['film_type_id'] ?? null,
                    'film_id'           => $row['film_id'] ?? null,
                    'dc_quantity'       => $dcQty,
                    'sq_in'             => $sqIn,
                    'sq_cm'             => $sqCm,
                    'total_sq_in'       => $totalSqIn,
                    'total_sq_cm'       => $totalSqCm,
                    'remark'            => $row['remark'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'response_code'    => '1',
                'response_message' => getResponseMessage('store_success'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'response_code'    => '0',
                'response_message' => getResponseMessage('store_error'),
                'original_error'   => $e->getMessage()
            ]);
        }
    }

    /**
     * Edit / Fetch Film DC Data
     */
    public function edit(Request $request)
    {
        $id = $request->input('id');

        $filmDc = DB::select('CALL film_dc_master(?)', [$id]);

        if (empty($filmDc)) {
            return response()->json([
                'response_code'    => '0',
                'response_message' => 'Record Does Not Exist'
            ]);
        }

        $filmDc = $filmDc[0];

        $filmDc->film_dc_date = (!empty($filmDc->film_dc_date) && $filmDc->film_dc_date != "0000-00-00")
            ? Date::createFromFormat('Y-m-d', $filmDc->film_dc_date)->format('d/m/Y')
            : "";

        if(isset($filmDc->cmp_logo))
        {
            $filmDc->cmp_logo = base64_encode($filmDc->cmp_logo);
        }

        $details = DB::select('CALL film_dc_details(?)', [$id]);

        $details_data = [];
        foreach ($details as $d) {
            $formatted_rep_date = (!empty($d->test_report_date) && $d->test_report_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $d->test_report_date)->format('d/m/Y')
                : "";

            $unit = $filmDc->film_size_unit_fix ?? 'inch';
            $filmSize = ($unit === 'cm') ? ($d->film_size_cm ?? $d->film_size_inch ?? '') : ($d->film_size_inch ?? $d->film_size_cm ?? '');

            $sqIn = floatval($d->sq_in ?? 0);
            if ($sqIn <= 0 && !empty($d->film_size_inch)) {
                $sqIn = $this->calculateFilmSqIn($d->film_size_inch, 0);
            }

            $sqCm = floatval($d->sq_cm ?? 0);
            if ($sqCm <= 0 && !empty($d->film_size_cm)) {
                $sqCm = $this->calculateFilmSqIn($d->film_size_cm, 0);
            }

            $dcQty = floatval($d->dc_quantity ?? 0);

            $totalSqIn = floatval($d->total_sq_in ?? 0);
            if ($totalSqIn <= 0 && $sqIn > 0) {
                $totalSqIn = $sqIn * $dcQty;
            }

            $totalSqCm = floatval($d->total_sq_cm ?? 0);
            if ($totalSqCm <= 0 && $sqCm > 0) {
                $totalSqCm = $sqCm * $dcQty;
            }

            $details_data[] = [
                'film_dc_detail_id' => $d->film_dc_detail_id,
                'film_dc_id'        => $d->film_dc_id,
                'test_report_rt_id' => $d->test_report_rt_id,
                'test_report_no'    => $d->test_report_no,
                'test_report_date'  => $formatted_rep_date,
                'die_no'            => $d->die_no ?? '',
                'part_no'           => $d->die_no ?? '',
                'film_brand_id'     => $d->film_brand_id,
                'film_brand'        => $d->film_brand ?? '',
                'film_type_id'      => $d->film_type_id ?? null,
                'film_id'           => $d->film_id,
                'film_size_inch'    => $filmSize,
                'sq_in'             => $sqIn,
                'sq_cm'             => $sqCm,
                'dc_quantity'       => $dcQty,
                'total_sq_in'       => $totalSqIn,
                'total_sq_cm'       => $totalSqCm,
                'remark'            => $d->remark,
                'mode'              => 'Update'
            ];
        }

        return response()->json([
            'response_code' => '1',
            'header_data'   => $filmDc,
            'details_data'  => $details_data
        ]);
    }

    /**
     * Update Film DC
     */
    public function update(Request $request)
    {
        $request->validate([
            'id'               => 'required',
            'film_dc_sequence' => 'required',
            'film_dc_date'     => 'required',
            'customer_id'      => 'required',
            'job_type_fix'     => 'required',
            'from_type_id'     => 'required',
        ]);

        DB::beginTransaction();
        try {
            $filmDc = DB::table('film_dc')->where('film_dc_id', $request->id)->first();
            if (!$filmDc) {
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => 'Record Not Found',
                ]);
            }

            DB::table('film_dc')->where('film_dc_id', $request->id)->update([
                'film_dc_sequence'    => $request->film_dc_sequence,
                'film_dc_no'          => $request->film_dc_no,
                'film_dc_date'        => Date::createFromFormat('d/m/Y', $request->film_dc_date)->format('Y-m-d'),
                'from_type_id'        => $request->from_type_id ?? 'Test Report RT',
                'job_type_fix'        => $request->job_type_fix ?? 'Non-Welding',
                'film_size_unit_fix'  => $request->film_size_unit_fix ?? 'inch',
                'customer_id'         => $request->customer_id,
                'customer_client'     => $request->customer_client,
                'total_qty'           => floatval($request->total_qty ?? 0),
                'total_square_inch'   => floatval($request->total_square_inch ?? 0),
                'film_size_for_print' => $request->film_size_for_print,
                'sp_note'             => $request->sp_note,
                'last_by'             => Auth::user()->id,
                'last_on'             => now(),
            ]);

            $details = is_string($request->details) ? json_decode($request->details, true) : ($request->details ?? []);
            foreach ($details as $row) {
                $detailId = $row['film_dc_detail_id'] ?? null;
                $mode = $row['mode'] ?? '';

                if ($mode === 'Delete') {
                    if ($detailId) {
                        DB::table('film_dc_details')->where('film_dc_detail_id', $detailId)->delete();
                    }
                } elseif ($mode === 'Update' || $mode === 'New') {
                    $formatted_report_date = null;
                    if (!empty($row['test_report_date'])) {
                        $formatted_report_date = Date::createFromFormat('d/m/Y', $row['test_report_date'])->format('Y-m-d');
                    }

                    $filmId = $row['film_id'] ?? null;
                    $dcQty = floatval($row['dc_quantity'] ?? 0);
                    $sqIn = isset($row['sq_in']) ? floatval($row['sq_in']) : 0;
                    $sqCm = isset($row['sq_cm']) ? floatval($row['sq_cm']) : 0;

                    if (($sqIn <= 0 || $sqCm <= 0) && !empty($filmId)) {
                        $filmObj = DB::table('film')->where('film_id', $filmId)->first();
                        if ($filmObj) {
                            if ($sqIn <= 0) {
                                $sqIn = floatval($filmObj->sq_in ?? 0);
                                if ($sqIn <= 0) {
                                    $sqIn = $this->calculateFilmSqIn($filmObj->film_size_inch ?? '', 0);
                                }
                            }
                            if ($sqCm <= 0) {
                                $sqCm = floatval($filmObj->sq_cm ?? 0);
                                if ($sqCm <= 0) {
                                    $sqCm = $this->calculateFilmSqIn($filmObj->film_size_cm ?? '', 0);
                                }
                            }
                        }
                    }

                    $totalSqIn = isset($row['total_sq_in']) && floatval($row['total_sq_in']) > 0
                        ? floatval($row['total_sq_in'])
                        : ($sqIn * $dcQty);

                    $totalSqCm = isset($row['total_sq_cm']) && floatval($row['total_sq_cm']) > 0
                        ? floatval($row['total_sq_cm'])
                        : ($sqCm * $dcQty);

                    $data = [
                        'film_dc_id'        => $request->id,
                        'test_report_rt_id' => $row['test_report_rt_id'] ?? null,
                        'test_report_no'    => $row['test_report_no'] ?? null,
                        'test_report_date'  => $formatted_report_date,
                        'die_no'            => $row['die_no'] ?? null,
                        'film_brand_id'     => $row['film_brand_id'] ?? null,
                        'film_type_id'      => $row['film_type_id'] ?? null,
                        'film_id'           => $row['film_id'] ?? null,
                        'dc_quantity'       => $dcQty,
                        'sq_in'             => $sqIn,
                        'sq_cm'             => $sqCm,
                        'total_sq_in'       => $totalSqIn,
                        'total_sq_cm'       => $totalSqCm,
                        'remark'            => $row['remark'] ?? null,
                    ];

                    if ($detailId) {
                        DB::table('film_dc_details')->where('film_dc_detail_id', $detailId)->update($data);
                    } else {
                        DB::table('film_dc_details')->insert($data);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'response_code'    => '1',
                'response_message' => getResponseMessage('update_success'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'response_code'    => '0',
                'response_message' => getResponseMessage('update_error'),
                'original_error'   => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete Film DC Record
     */
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $filmDc = DB::table('film_dc')->where('film_dc_id', $id)->first();

        if (!$filmDc) {
            return response()->json([
                'response_code'    => '0',
                'response_message' => 'Record Not Found',
            ]);
        }

        DB::beginTransaction();
        try {
            DB::table('film_dc_details')->where('film_dc_id', $id)->delete();
            DB::table('film_dc')->where('film_dc_id', $id)->delete();

            DB::commit();

            return response()->json([
                'response_code'    => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'response_code'    => '0',
                'response_message' => getResponseMessage('delete_error'),
                'original_error'   => $e->getMessage()
            ]);
        }
    }


     /**
     * Generate Latest DC Sequence Number
     */
    public function getLatestDCSequence(Request $request)
    {
        $modal = FilmDc::class;
        $sequence = 'film_dc_sequence';
        $prefix = 'FDC';
        $sup_num_format = getLatestSequence($modal, $sequence, $prefix);

        return response()->json([
            'response_code' => 1,
            'latest_no'     => $sup_num_format['format'],
            'number'        => $sup_num_format['isFound'],
        ]);
    }

    /**
     * Get Pending Customers having RT Reports
     */
    public function getPendingCustomers(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();
        $location = getCurrentLocation()->location_id;
        $jobType = $request->input('job_type_fix');
        $filmSizeUnit = $request->input('film_size_unit_fix');
        $fromType = $request->input('from_type_id', 'Test Report RT');

        if ($fromType === 'Manual') {
            $customers = DB::table('customers')
                ->select('customers.id', 'customers.customer')
                ->orderBy('customers.customer', 'asc')
                ->get()
                ->toArray();
        } else {
            // $query = DB::table('customers')
            //     ->join('pending_rt_reports_for_film_dc as pfd', 'pfd.customer_id', '=', 'customers.id')
            //     ->join('test_report_rt as tr', 'tr.test_report_rt_id', '=', 'pfd.report_id')
            //     ->where('pfd.current_location_id', $location)
            //     ->whereIn('tr.year_id', $yearIds)
            //     ->where('pfd.total_films', '>', 0);

            // if (!empty($jobType)) {
            //     $query->where('tr.job_type_fix', $jobType);
            // }

            // if (!empty($filmSizeUnit)) {
            //     $query->where('tr.film_size_unit_fix', $filmSizeUnit);
            // }

            $query = DB::table('pending_rt_reports_for_film_dc as pfd')
            ->join('customers', 'customers.id', '=', 'pfd.customer_id')
            ->join('test_report_rt as tr', 'tr.test_report_rt_id', '=', 'pfd.report_id')
            ->where('pfd.current_location_id', $location)
            ->where('pfd.total_films', '>', 0)    
            ->whereIn('tr.year_id', $yearIds);

            if (!empty($jobType)) {
                $query->where('tr.job_type_fix', $jobType);
            }

if (!empty($filmSizeUnit)) {
    $query->where('tr.film_size_unit_fix', $filmSizeUnit);
}

            $customers = $query->select('customers.id', 'customers.customer')
                ->distinct()
                ->orderBy('customers.customer', 'asc')
                ->get()
                ->toArray();

            // If editing, make sure current customer is present
            $editDcId = $request->input('film_dc_id');
            if (!empty($editDcId)) {
                $currentCustId = DB::table('film_dc')->where('film_dc_id', $editDcId)->value('customer_id');
                if ($currentCustId) {
                    $alreadyExists = false;
                    foreach ($customers as $c) {
                        if ($c->id == $currentCustId) {
                            $alreadyExists = true;
                            break;
                        }
                    }
                    if (!$alreadyExists) {
                        $curr = DB::table('customers')->where('id', $currentCustId)->select('id', 'customer')->first();
                        if ($curr) {
                            $customers[] = $curr;
                        }
                    }
                }
            }
        }

        return response()->json([
            'response_code' => 1,
            'customers'     => $customers
        ]);
    }

    /**
     * Get Pending RT Reports for selected Customer and Job Type
     */
    public function getPendingRtReports(Request $request)
    {
        $location = getCurrentLocation()->location_id;
        $yearIds = getCompanyYearIdsToTill();
        $customerId = $request->input('customer_id');
        $jobType = $request->input('job_type_fix');
        $filmSizeUnit = $request->input('film_size_unit_fix');
        $editDcId = $request->input('film_dc_id');

        $query = DB::table('pending_rt_reports_for_film_dc as pfd')
            ->join('test_report_rt as tr', 'tr.test_report_rt_id', '=', 'pfd.report_id')
            ->leftJoin('material_inward_details as mid', 'mid.material_inward_details_id', '=', 'tr.material_inward_details_id')
            ->leftJoin('material_inward as mi', 'mi.material_inward_id', '=', 'mid.material_inward_id')
            ->select([
                'tr.test_report_rt_id',
                'tr.test_report_no',
                'tr.revision_number',
                'tr.test_report_date',
                'tr.customer_client',
                'tr.rt_no',
                'tr.heat_no',
                'tr.job_desc',
                'tr.part_no',
                'tr.drg_no',
                'tr.product_code',
                'tr.nabl_type_fix',
                'tr.job_type_fix',
                'pfd.total_films as pending_films',
                'tr.no_of_films',
                'tr.total_area',
                DB::raw("IFNULL(mi.dc_no, '') as customer_dc_no")
            ])
            ->where('pfd.current_location_id', $location)
            ->whereIn('tr.year_id', $yearIds)
            ->where('pfd.customer_id', $customerId);

        if (!empty($jobType)) {
            $query->where('tr.job_type_fix', $jobType);
        }

        if (!empty($filmSizeUnit)) {
            $query->where('tr.film_size_unit_fix', $filmSizeUnit);
        }

        if (!empty($editDcId)) {
            $currentDcReportIds = DB::table('film_dc_details')
                ->where('film_dc_id', $editDcId)
                ->whereNotNull('test_report_rt_id')
                ->pluck('test_report_rt_id')
                ->toArray();

            if (!empty($currentDcReportIds)) {
                $query->where(function($q) use ($currentDcReportIds) {
                    $q->where('pfd.total_films', '>', 0)
                      ->orWhereIn('tr.test_report_rt_id', $currentDcReportIds);
                });
            } else {
                $query->where('pfd.total_films', '>', 0);
            }
        } else {
            $query->where('pfd.total_films', '>', 0);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        if (!empty($startDate)) {
            $query->where('tr.test_report_date', '>=', Date::createFromFormat('d/m/Y', $startDate)->format('Y-m-d'));
        }
        if (!empty($endDate)) {
            $query->where('tr.test_report_date', '<=', Date::createFromFormat('d/m/Y', $endDate)->format('Y-m-d'));
        }

        $reports = $query->orderBy('tr.test_report_date', 'desc')
            ->orderBy('tr.test_report_rt_id', 'desc')
            ->get();

        foreach ($reports as $row) {
            $row->test_report_date = (!empty($row->test_report_date) && $row->test_report_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $row->test_report_date)->format('d/m/Y')
                : "";
        }

        return response()->json([
            'response_code' => 1,
            'reports'       => $reports
        ]);
    }

    /**
     * Get Details for Selected RT Reports (Breakdown of films used)
     */
    public function getRtDetailsForFilmDc(Request $request)
    {
        $reportIds = $request->input('report_ids', []);
        if (is_string($reportIds)) {
            $reportIds = json_decode($reportIds, true) ?: [];
        }

        if (empty($reportIds)) {
            return response()->json([
                'response_code' => 1,
                'details'       => []
            ]);
        }

        $details = DB::table('test_report_rt_details as trd')
            ->select([
                'trd.test_report_rt_id',
                'tr.test_report_no',
                'tr.test_report_date',
                'tr.customer_client',
                'tr.part_no',
                'tr.product_code',
                'tr.film_size_unit_fix',
                'trd.film_brand_id',
                'trd.film_type_id',
                'fb.film_brand',
                'trd.film_id',
                'f.film_size_inch',
                'f.film_size_cm',
                'f.sq_in',
                'f.sq_cm',
                DB::raw('SUM(IFNULL(trd.film_qty, 0)) as dc_quantity')
            ])
            ->join('test_report_rt as tr', 'tr.test_report_rt_id', '=', 'trd.test_report_rt_id')
            ->leftJoin('film as f', 'f.film_id', '=', 'trd.film_id')
            ->leftJoin('film_brand as fb', 'fb.film_brand_id', '=', 'trd.film_brand_id')
            ->whereIn('trd.test_report_rt_id', $reportIds)
            ->where(function ($q) {
                $q->where('trd.include_in_measurement_sheet', 'Yes')
                  ->orWhere('trd.include_in_measurement_sheet', '1');
            })
            ->groupBy([
                'trd.test_report_rt_id',
                'tr.test_report_no',
                'tr.test_report_date',
                'tr.customer_client',
                'tr.part_no',
                'tr.product_code',
                'tr.film_size_unit_fix',
                'trd.film_brand_id',
                'trd.film_type_id',
                'trd.film_id',
                'fb.film_brand',
                'f.film_size_inch',
                'f.film_size_cm',
                'f.sq_in',
                'f.sq_cm'
            ])
            ->get();

        foreach ($details as $row) {
            $row->mode = 'New';
            $row->remark = '';
            $row->die_no = !empty($row->part_no) ? $row->part_no : (!empty($row->product_code) ? $row->product_code : '');
            $row->test_report_date = (!empty($row->test_report_date) && $row->test_report_date != "0000-00-00")
                ? Date::createFromFormat('Y-m-d', $row->test_report_date)->format('d/m/Y')
                : "";

            $unit = $row->film_size_unit_fix ?? 'inch';
            $filmSize = ($unit === 'cm') ? ($row->film_size_cm ?? $row->film_size_inch ?? '') : ($row->film_size_inch ?? $row->film_size_cm ?? '');

            $sqIn = floatval($row->sq_in ?? 0);
            if ($sqIn <= 0 && !empty($row->film_size_inch)) {
                $sqIn = $this->calculateFilmSqIn($row->film_size_inch, 0);
            }

            $sqCm = floatval($row->sq_cm ?? 0);
            if ($sqCm <= 0 && !empty($row->film_size_cm)) {
                $sqCm = $this->calculateFilmSqIn($row->film_size_cm, 0);
            }

            $dcQty = floatval($row->dc_quantity ?? 0);

            $row->film_size_inch = $filmSize;
            $row->sq_in = $sqIn;
            $row->sq_cm = $sqCm;
            $row->dc_quantity = $dcQty;
            $row->total_sq_in = $sqIn * $dcQty;
            $row->total_sq_cm = $sqCm * $dcQty;
        }

        return response()->json([
            'response_code' => 1,
            'details'       => $details
        ]);
    }

    /**
     * Calculate Square Inches from film size string (e.g. "6 X 4" -> 24.0, "12 X 12" -> 144.0)
     */
    private function calculateFilmSqIn($filmSizeInch, $dbSqIn = 0)
    {
        if (!empty($dbSqIn) && floatval($dbSqIn) > 0) {
            return floatval($dbSqIn);
        }
        if (empty($filmSizeInch)) {
            return 0;
        }
        if (preg_match('/^(\d+(?:\.\d+)?)\s*[xX*]\s*(\d+(?:\.\d+)?)/i', trim($filmSizeInch), $m)) {
            return floatval($m[1]) * floatval($m[2]);
        }
        return 0;
    }
}