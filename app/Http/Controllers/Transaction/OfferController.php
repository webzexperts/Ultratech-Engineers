<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\Offer;
use App\Models\Transaction\OfferDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class OfferController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-offer');
    }

    public function index(Request $request, DataTables $datatables)
    {
        $current_location_id = getCurrentLocation()->location_id;
        $year_data = getCurrentYearData();

        $offer_query = Offer::select([
                'offer.offer_sequence',
                'offer.offer_id',
                'offer.offer_no',
                'offer.offer_date',
                'offer.nabl_type_fix',
                'offer.test_at_fix',
                'offer.job_type_fix',
                'customers.customer as customer',
                'offer.dc_no',
                'offer.dc_date',
                'offer.po_no',
                'offer.po_date',
                'offer_details.type_of_testing_id_fix',
                'type_of_job.type_of_job',
                'job_descriptions.job_description',
                'offer_details.part_no',
                'offer_details.drg_no',
                'materials.material',
                'offer_details.heat_no',
                'offer_details.rt_no',
                'offer_details.product_code',
                'offer_details.quantity',
                'prepared_by.person_name as prepared_by',
                'offer.created_on',
                'offer.created_by',
                'offer.last_by',
                'offer.last_on',
            ])
            ->leftJoin('offer_details', 'offer_details.offer_id', '=', 'offer.offer_id')
            ->leftJoin('customers', 'customers.id', '=', 'offer.customer_id')
            ->leftJoin('admin as prepared_by', 'prepared_by.id', '=', 'offer.prepared_by_user_id')
            ->leftJoin('type_of_job', 'type_of_job.id', '=', 'offer_details.type_of_job_id')
            ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'offer_details.job_desc_id')
            ->leftJoin('materials', 'materials.id', '=', 'offer_details.material_id')
            ->where('offer.year_id', $year_data->id)
            ->where('offer.current_location_id', $current_location_id);

        $dataTable = DataTables::of($offer_query)
            // ->editColumn('part', function ($row) {
            //     return $row->part ?? '';
            // })
            // ->filterColumn('part', function($query, $keyword) {
            //     $keyword = trim($keyword);
            //     $query->whereRaw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) LIKE ?", ["%{$keyword}%"]);
            // })
            // ->orderColumn('part', function ($query, $order) {
            //     $query->orderByRaw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) {$order}");
            // })
            ->editColumn('offer_date', function ($row) {
                if ($row->offer_date != null) {
                    return Date::createFromFormat('Y-m-d', $row->offer_date)->format(DATE_FORMAT);
                }
                return '';
            })
            ->filterColumn('offer.offer_date', function ($q, $k) {
                applyDate($q, $k, 'offer.offer_date');
            })
            ->editColumn('dc_date', function ($row) {
                if ($row->dc_date != null) {
                    return Date::createFromFormat('Y-m-d', $row->dc_date)->format(DATE_FORMAT);
                }
                return '';
            })
            ->filterColumn('offer.dc_date', function ($q, $k) {
                applyDate($q, $k, 'offer.dc_date');
            })
            ->editColumn('po_date', function ($row) {
                if ($row->po_date != null) {
                    return Date::createFromFormat('Y-m-d', $row->po_date)->format(DATE_FORMAT);
                }
                return '';
            })
            ->filterColumn('offer.po_date', function ($q, $k) {
                applyDate($q, $k, 'offer.po_date');
            })
            ->filterColumn('offer.job_type_fix', function ($query, $keyword) {
                $globalSearch = request()->input('search.value');
                $searchValue = $globalSearch != '' ? $globalSearch : $keyword;
                $lowerKeyword = strtolower(trim($searchValue));
                if ($lowerKeyword === 'welding') {
                    $searchJobTypeFix = 'Welding';
                } elseif ($lowerKeyword === 'non-welding') {
                    $searchJobTypeFix = 'Non-Welding';
                }
                if (!empty($searchJobTypeFix)) {
                    $query->where('offer.job_type_fix', '=', $searchJobTypeFix);
                } else {
                    $dbFormatKeyword = str_replace(' ', '_', $lowerKeyword);
                    $query->where('offer.job_type_fix', 'like', "$dbFormatKeyword%");
                }
            })
            ->editColumn('quantity', function ($row) {
                return $row->quantity > 0 ? number_format((float)$row->quantity, 0, '.', '') : '';
            })
            ->filterColumn('offer.offer_no', function ($query, $keyword) {
                applySequenceSearch($query, $keyword, 'offer.offer_no');
            })
            ->addColumn('options', function ($row) {
                $action = '<div class="dropdown d-inline-block">
                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-more-fill align-middle"></i>
                    </button>
                    <ul class="dropdown-menu">';

                // if (hasAccess("offer", "print")) {
                //     $offer_number = !empty($row->offer_no) ? '_' . str_replace('/', '_', $row->offer_no) : "";
                //     $cust_name = !empty($row->customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $row->customer) : "";
                //     $pdfName   = 'Offer' . $offer_number . $cust_name;
                //     $encodedId = base64_encode($row->offer_id);
                //     $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=offer");
                //     $action .= '<li><a class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                // }

                if (hasAccess("offer", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-offer"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("offer", "delete")) {
                    $action .= '<li><a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                </a></li>';
                }
                $action .= '</ul></div>';
                return $action;
            });

        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'offer');
        return $dataTable
            ->rawColumns(['options', 'last_by', 'last_on', 'created_by', 'created_on'])
            ->make(true);
    }

    /**
     * Auto-number generator for Offer sequence: OFFER/0001/26-27
     */
    public function getLatestOfferNumber(Request $request)
    {
        $year_data    = getCurrentYearData();
        $locationData = getCurrentLocation()->location_id;

        $isFound = DB::table('offer')
            ->where('year_id', $year_data->id)
            ->where('current_location_id', $locationData)
            ->max('offer_sequence');

        $isFound = $isFound ? $isFound + 1 : 1;

        $middle_num = str_pad($isFound, 4, '0', STR_PAD_LEFT);
        $postfix    = $year_data->yearcode;

        $format = 'OFFER/' . $middle_num . '/' . $postfix;

        return response()->json([
            'response_code' => 1,
            'latest_no'     => $format,
            'number'        => $isFound,
        ]);
    }

    public function edit(Request $request)
    {
        try {
            $offer_data = DB::select('CALL offer_master(?)', [$request->id]);
        } catch (\Exception $e) {
            report($e);
            $offer_data = DB::table('offer')->where('offer_id', $request->id)->get()->toArray();
        }

        if (!empty($offer_data)) {
            $offer_data = $offer_data[0];
            $offer_data->offer_date = (!empty($offer_data->offer_date) && $offer_data->offer_date != '0000-00-00') ? Carbon::parse($offer_data->offer_date)->format('d/m/Y') : "";
            $offer_data->dc_date = (!empty($offer_data->dc_date) && $offer_data->dc_date != '0000-00-00') ? Carbon::parse($offer_data->dc_date)->format('d/m/Y') : "";
            $offer_data->po_date = (!empty($offer_data->po_date) && $offer_data->po_date != '0000-00-00') ? Carbon::parse($offer_data->po_date)->format('d/m/Y') : "";
            if (isset($offer_data->company_logo)) {
                $offer_data->company_logo = base64_encode($offer_data->company_logo);
            }

            $offer_number = !empty($offer_data->offer_no) ? '_' . str_replace('/', '_', $offer_data->offer_no) : "";
            $cust_name = !empty($offer_data->customer_name) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $offer_data->customer_name) : "";
            $offer_data->pdf_name = 'Offer' . $offer_number . $cust_name;
        }

        try {
            $offer_details_data = DB::select('CALL offer_details(?)', [$request->id]);
        } catch (\Exception $e) {
            report($e);
            $offer_details_data = [];
        }

        $usage = [];
        try {
            $usage = DB::select('CALL offer_used_list(?)', [$request->id]);
        } catch (\Exception $e) {
            $usage = [];
        }

        $detail_used_map = [];
        foreach ($usage as $uRow) {
            $detId = $uRow->offer_details_id ?? ($uRow->material_inward_details_id ?? null);
            if ($detId) {
                if (!isset($detail_used_map[$detId])) {
                    $detail_used_map[$detId] = 0;
                }
                $detail_used_map[$detId] += (float)($uRow->used_qty ?? 0);
            }
        }

        $detail_ids = [];
        if ($offer_details_data) {
            foreach ($offer_details_data as $dVal) {
                if (!empty($dVal->offer_details_id)) {
                    $detail_ids[] = $dVal->offer_details_id;
                }
                $dVal->mode = 'Update';

                // Fetch part_no & drg_no directly from DB table offer_details if needed
                $dbDetail = DB::table('offer_details')->where('offer_details_id', $dVal->offer_details_id)->first();
                if ($dbDetail) {
                    if (empty($dVal->part_no) && !empty($dbDetail->part_no)) $dVal->part_no = $dbDetail->part_no;
                    if (empty($dVal->drg_no) && !empty($dbDetail->drg_no)) $dVal->drg_no = $dbDetail->drg_no;
                    if (!isset($dVal->process_type) && isset($dbDetail->process_type)) $dVal->process_type = $dbDetail->process_type;
                    if (!isset($dVal->is_observation_sheet) && isset($dbDetail->is_observation_sheet)) $dVal->is_observation_sheet = $dbDetail->is_observation_sheet;
                    if (!isset($dVal->test_report_rt_id) && isset($dbDetail->test_report_rt_id)) $dVal->test_report_rt_id = $dbDetail->test_report_rt_id;
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

                // Ensure alias compatibility so both offer.js and any other caller have both keys
                $dVal->type_of_job = $dVal->type_of_job_name ?? ($dVal->type_of_job ?? '');
                $dVal->type_of_job_name = $dVal->type_of_job_name ?? $dVal->type_of_job;

                $dVal->job_description = $dVal->job_description_name ?? ($dVal->job_description ?? '');
                $dVal->job_description_name = $dVal->job_description_name ?? $dVal->job_description;

                $dVal->material = $dVal->material_name ?? ($dVal->material ?? '');
                $dVal->material_name = $dVal->material_name ?? $dVal->material;

                $dVal->area_of_coverage = $dVal->area_of_coverage_name ?? ($dVal->area_of_coverage ?? '');
                $dVal->area_of_coverage_name = $dVal->area_of_coverage_name ?? $dVal->area_of_coverage;

                $dVal->procedure_reference = $dVal->procedure_ref_name ?? ($dVal->procedure_reference ?? '');
                $dVal->procedure_ref_name = $dVal->procedure_ref_name ?? $dVal->procedure_reference;

                $dVal->evaluation_as_per = $dVal->evaluation_as_per_name ?? ($dVal->evaluation_as_per ?? '');
                $dVal->evaluation_as_per_name = $dVal->evaluation_as_per_name ?? $dVal->evaluation_as_per;

                $dVal->acceptance_standard = $dVal->acceptance_standard_name ?? ($dVal->acceptance_standard ?? '');
                $dVal->acceptance_standard_name = $dVal->acceptance_standard_name ?? $dVal->acceptance_standard;

                $detId = $dVal->offer_details_id;
                $totUsed = $detail_used_map[$detId] ?? 0;
                $dVal->used_qty = (float)$totUsed;
                $dVal->in_use = ($totUsed > 0);
            }
        }

        if (!empty($offer_data)) {
            return response()->json([
                'offer_data'         => $offer_data,
                'offer_details_data' => $offer_details_data,
                'min_report_date'    => null,
                'response_code'      => '1',
                'response_message'   => '',
            ]);
        }

        return response()->json([
            'response_code'    => '0',
            'response_message' => 'Record Does Not Exist',
        ]);
    }

    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            DB::table('offer_details')->where('offer_id', $request->id)->delete();
            DB::table('offer')->where('offer_id', $request->id)->delete();

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

    public function store(Request $request)
    {
        $year_data    = getCurrentYearData();
        $LocationData = getCurrentLocation();

        DB::beginTransaction();
        try {
            $existNumber = DB::table('offer')->where([
                ['offer_sequence', $request->offer_sequence],
                ['offer_no', $request->offer_no],
                ['year_id', $year_data->id],
                ['current_location_id', $LocationData->location_id],
            ])->first();

            if ($existNumber) {
                $latestNo = $this->getLatestOfferNumber($request);
                $area = json_decode($latestNo->getContent(), true);
                $offer_no       = $area['latest_no'];
                $offer_sequence = $area['number'];
            } else {
                $offer_no       = $request->offer_no;
                $offer_sequence = $request->offer_sequence;
            }

            $offer_id = DB::table('offer')->insertGetId([
                'offer_no'                                  => $offer_no,
                'offer_sequence'                            => $offer_sequence,
                'offer_date'                                => $request->offer_date ? Date::createFromFormat('d/m/Y', $request->offer_date)->format('Y-m-d') : null,
                'nabl_type_fix'                             => $request->nabl_type_fix,
                'test_at_fix'                               => $request->test_at_fix,
                'job_type_fix'                              => $request->job_type_fix,
                'customer_id'                               => $request->customer_id,
                'dc_no'                                     => $request->dc_no,
                'dc_date'                                   => $request->dc_date ? Date::createFromFormat('d/m/Y', $request->dc_date)->format('Y-m-d') : null,
                'po_no'                                     => $request->po_no,
                'po_date'                                   => !empty($request->po_date) ? Date::createFromFormat('d/m/Y', $request->po_date)->format('Y-m-d') : null,
                'sample_drawn_by'                           => $request->sample_drawn_by,
                'is_any_tpi_witness'                        => $request->is_any_tpi_witness,
                'tpi_name'                                  => $request->is_any_tpi_witness == 'Yes' ? $request->tpi_name : null,
                'condition_of_sample'                       => $request->condition_of_sample,
                'is_equipment_available'                    => $request->is_equipment_available,
                'competent_personnel_available'             => $request->competent_personnel_available,
                'is_test_sub_contracted'                    => $request->is_test_sub_contracted,
                'test_feasible'                             => $request->test_feasible,
                'all_test_parameters_are_in_accredited_scope' => $request->all_test_parameters_are_in_accredited_scope,
                'required_statement_of_conformity'          => $request->required_statement_of_conformity,
                'additional_requirement_from_customer'      => $request->additional_requirement_from_customer,
                'special_note'                              => $request->special_note,
                'prepared_by_user_id'                       => Auth::user()->id,
                'current_location_id'                       => $LocationData->location_id ?? null,
                'year_id'                                   => $year_data->id,
                'company_id'                                => Auth::user()->company_id,
                'created_by'                                => Auth::user()->id,
                'created_on'                                => Carbon::now('Asia/Kolkata'),
            ]);

            $details = json_decode($request->offer_details_data, true);
            if (!empty($details)) {
                foreach ($details as $row) {
                    if (($row['mode'] ?? '') == 'Delete') continue;
                    if (empty($row['type_of_testing_id_fix'])) continue;
                    DB::table('offer_details')->insert([
                        'offer_id'               => $offer_id,
                        'process_type'           => $row['process_type'] ?? 'Fresh',
                        'test_report_rt_id'      => (!empty($row['test_report_rt_id']) && $row['test_report_rt_id'] != 0) ? $row['test_report_rt_id'] : null,
                        'type_of_testing_id_fix' => $row['type_of_testing_id_fix'] ?? null,
                        'type_of_job_id'         => $row['type_of_job_id'] ?? null,
                        'job_desc_id'            => $row['job_desc_id'] ?? null,
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
                }
            }

            DB::commit();

            /*
            $offer_number = !empty($offer_no) ? '_' . str_replace('/', '_', $offer_no) : "";
            $cust = DB::table('customers')->where('id', $request->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Offer' . $offer_number . $cust_name;

            if (function_exists('GeneratePdf')) {
                GeneratePdf($offer_id, $pdf_name, 'offer', 'add');
            }
            $encodedId = base64_encode($offer_id);
            $url = hasAccess("offer", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=offer") : "";
            */
            $url = "";

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

        DB::beginTransaction();
        try {
            DB::table('offer')->where('offer_id', $request->id)->update([
                'offer_no'                                  => $request->offer_no,
                'offer_sequence'                            => $request->offer_sequence,
                'offer_date'                                => $request->offer_date ? Date::createFromFormat('d/m/Y', $request->offer_date)->format('Y-m-d') : null,
                'nabl_type_fix'                             => $request->nabl_type_fix,
                'test_at_fix'                               => $request->test_at_fix,
                'job_type_fix'                              => $request->job_type_fix,
                'customer_id'                               => $request->customer_id,
                'dc_no'                                     => $request->dc_no,
                'dc_date'                                   => $request->dc_date ? Date::createFromFormat('d/m/Y', $request->dc_date)->format('Y-m-d') : null,
                'po_no'                                     => $request->po_no,
                'po_date'                                   => !empty($request->po_date) ? Date::createFromFormat('d/m/Y', $request->po_date)->format('Y-m-d') : null,
                'sample_drawn_by'                           => $request->sample_drawn_by,
                'is_any_tpi_witness'                        => $request->is_any_tpi_witness,
                'tpi_name'                                  => $request->is_any_tpi_witness == 'Yes' ? $request->tpi_name : null,
                'condition_of_sample'                       => $request->condition_of_sample,
                'is_equipment_available'                    => $request->is_equipment_available,
                'competent_personnel_available'             => $request->competent_personnel_available,
                'is_test_sub_contracted'                    => $request->is_test_sub_contracted,
                'test_feasible'                             => $request->test_feasible,
                'all_test_parameters_are_in_accredited_scope' => $request->all_test_parameters_are_in_accredited_scope,
                'required_statement_of_conformity'          => $request->required_statement_of_conformity,
                'additional_requirement_from_customer'      => $request->additional_requirement_from_customer,
                'special_note'                              => $request->special_note,
                'last_by'                                   => Auth::user()->id,
                'last_on'                                   => Carbon::now('Asia/Kolkata'),
            ]);

            $details = json_decode($request->offer_details_data, true);

            if (!empty($details)) {
                foreach ($details as $row) {
                    if (empty($row)) continue;
                    $mode = $row['mode'] ?? '';

                    if ($mode == 'Insert') {
                        DB::table('offer_details')->insert([
                            'offer_id'               => $request->id,
                            'process_type'           => $row['process_type'] ?? 'Fresh',
                            'test_report_rt_id'      => (!empty($row['test_report_rt_id']) && $row['test_report_rt_id'] != 0) ? $row['test_report_rt_id'] : null,
                            'type_of_testing_id_fix' => $row['type_of_testing_id_fix'] ?? null,
                            'type_of_job_id'         => $row['type_of_job_id'] ?? null,
                            'job_desc_id'            => $row['job_desc_id'] ?? null,
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
                    } elseif ($mode == 'Update') {
                        if (!empty($row['offer_details_id'])) {
                            DB::table('offer_details')->where('offer_details_id', $row['offer_details_id'])->update([
                                'offer_id'               => $request->id,
                                'process_type'           => $row['process_type'] ?? 'Fresh',
                                'test_report_rt_id'      => (!empty($row['test_report_rt_id']) && $row['test_report_rt_id'] != 0) ? $row['test_report_rt_id'] : null,
                                'type_of_testing_id_fix' => $row['type_of_testing_id_fix'] ?? null,
                                'type_of_job_id'         => $row['type_of_job_id'] ?? null,
                                'job_desc_id'            => $row['job_desc_id'] ?? null,
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
                        }
                    } elseif ($mode == 'Delete') {
                        if (!empty($row['offer_details_id'])) {
                            DB::table('offer_details')->where('offer_details_id', $row['offer_details_id'])->delete();
                        }
                    }
                }
            }

            DB::commit();

            /*
            $offer_number = !empty($request->offer_no) ? '_' . str_replace('/', '_', $request->offer_no) : "";
            $cust = DB::table('customers')->where('id', $request->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Offer' . $offer_number . $cust_name;

            if (function_exists('GeneratePdf')) {
                GeneratePdf($request->id, $pdf_name, 'offer', 'update');
            }
            $encodedId = base64_encode($request->id);
            $url = hasAccess("offer", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=offer") : "";
            */
            $url = "";

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

    public function offerLNRData(Request $request)
    {
        $year_data     = getCurrentYearData();
        $LocationData  = getCurrentLocation();
        $customer_id   = $request->customer_id;
        $nabl_type_fix = $request->nabl_type_fix;

        // Get location-wise last offer record for parent radios (regardless of customer)
        $latestLocationOffer = Offer::where('year_id', $year_data->id)
            ->where('current_location_id', $LocationData->location_id)
            ->orderBy('offer_id', 'desc')
            ->first();

        $location_lnr_radios = null;
        if ($latestLocationOffer) {
            $location_lnr_radios = [
                'nabl_type_fix' => $latestLocationOffer->nabl_type_fix,
                'test_at_fix'   => $latestLocationOffer->test_at_fix,
                'job_type_fix'  => $latestLocationOffer->job_type_fix,
            ];
        }

        // Strictly do NOT return any customer LNR data if no customer is selected
        if (empty($customer_id)) {
            return response()->json([
                'response_code'           => 1,
                'lnr_data'                => null,
                'lnr_detail_testing_type' => null,
                'location_lnr_radios'     => $location_lnr_radios,
            ]);
        }

        $query = Offer::where('year_id', $year_data->id)
            ->where('current_location_id', $LocationData->location_id)
            ->where('customer_id', $customer_id);

        if (!empty($nabl_type_fix)) {
            $query->where('nabl_type_fix', $nabl_type_fix);
        }

        $latestOffer = $query->orderBy('offer_id', 'desc')->first();

        // Fallback: If no record for specific customer + nabl_type_fix, try matching just customer
        if (!$latestOffer) {
            $latestOffer = Offer::where('year_id', $year_data->id)
                ->where('current_location_id', $LocationData->location_id)
                ->where('customer_id', $customer_id)
                ->orderBy('offer_id', 'desc')
                ->first();
        }

        $lnr_detail_testing_type = null;
        if ($latestOffer) {
            $lastDetail = OfferDetails::where('offer_id', $latestOffer->offer_id)
                ->whereNotNull('type_of_testing_id_fix')
                ->orderBy('offer_details_id', 'desc')
                ->first();
            if ($lastDetail) {
                $lnr_detail_testing_type = $lastDetail->type_of_testing_id_fix;
            }
        }

        return response()->json([
            'response_code'           => 1,
            'lnr_data'                => $latestOffer,
            'lnr_detail_testing_type' => $lnr_detail_testing_type,
            'location_lnr_radios'     => $location_lnr_radios,
        ]);
    }
}
