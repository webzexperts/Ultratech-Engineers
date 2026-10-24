<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\MaterialInward;
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
                'offer.challan_no',
                'offer.challan_date',
                'offer.special_note',
                'offer_details.sr_no',
                'offer_details.process_type_fix',
                'offer_details.type_of_testing_id_fix',
                'offer_details.job_desc as job_description',
                'offer_details.customer_id_no',
                'offer_details.die_no',
                'offer_details.drg_no',
                'materials.material',
                'offer_details.heat_no',
                'offer_details.lab_id',
                'offer_details.thickness',
                'offer_details.quantity',
                'offer.created_on',
                'offer.created_by',
                'offer.last_by',
                'offer.last_on',
            ])
            ->leftJoin('offer_details', 'offer_details.offer_id', '=', 'offer.offer_id')
            ->leftJoin('customers', 'customers.id', '=', 'offer.customer_id')
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
            ->editColumn('challan_date', function ($row) {
                if ($row->challan_date != null) {
                    return Date::createFromFormat('Y-m-d', $row->challan_date)->format(DATE_FORMAT);
                }
                return '';
            })
            ->filterColumn('offer.challan_date', function ($q, $k) {
                applyDate($q, $k, 'offer.challan_date');
            })
            ->filterColumn('offer.job_type_fix', function ($query, $keyword) {
                $globalSearch = request()->input('search.value');
                $searchValue = $globalSearch != '' ? $globalSearch : $keyword;
                $lowerKeyword = strtolower(trim($searchValue));
                if ($lowerKeyword === 'casting') {
                    $query->where('offer.job_type_fix', '=', 'Casting');
                } elseif ($lowerKeyword === 'fabrication') {
                    $query->where('offer.job_type_fix', '=', 'Fabrication');
                } else {
                    $query->where('offer.job_type_fix', 'like', "{$lowerKeyword}%");
                }
            })
            ->editColumn('quantity', function ($row) {
                return $row->quantity > 0 ? number_format((float)$row->quantity, 0, '.', '') : '';
            })
            ->editColumn('special_note', function ($row) {
                return $row->special_note ?? '';
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

                if (hasAccess("offer", "print")) {
                    $offer_number = !empty($row->offer_no) ? '_' . str_replace('/', '_', $row->offer_no) : "";
                    $cust_name = !empty($row->customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $row->customer) : "";
                    $pdfName   = 'Offer' . $offer_number . $cust_name;
                    $encodedId = base64_encode($row->offer_id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=offer");
                    $action .= '<li><a class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }

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
     * Auto-number generator for Offer sequence: OF/0001/26-27 (Customer-wise)
     */
    public function getLatestOfferNumber(Request $request)
    {
        $customerId = $request->customer_id;

        if (empty($customerId)) {
            return response()->json([
                'response_code' => 0,
                'response_message' => 'Please select customer first.',
                'latest_no' => '',
                'number' => '',
            ]);
        }

        $seqData = getCustomerLatestSequence(Offer::class, 'offer_sequence', $customerId);

        return response()->json([
            'response_code' => 1,
            'latest_no'     => $seqData['format'],
            'number'        => $seqData['isFound'],
        ]);
    }

    public function edit(Request $request)
    {
        $offer_data = DB::select('CALL offer_master(?)', [$request->id]);

        $usage = DB::select('CALL offer_used_list(?)', [$request->id]);
        $isUsed = (!empty($usage) || (!empty($offer_data) && ($offer_data[0]->authorized_id_fix == 1 || $offer_data[0]->authorized_id_fix == '1')));

        if (!empty($offer_data)) {
            $offer_data = $offer_data[0];
            $offer_data->offer_date = (!empty($offer_data->offer_date) && $offer_data->offer_date != '0000-00-00') ? Carbon::parse($offer_data->offer_date)->format('d/m/Y') : "";
            $offer_data->challan_date = (!empty($offer_data->challan_date) && $offer_data->challan_date != '0000-00-00') ? Carbon::parse($offer_data->challan_date)->format('d/m/Y') : "";
            $offer_data->del_date = (!empty($offer_data->del_date) && $offer_data->del_date != '0000-00-00') ? Carbon::parse($offer_data->del_date)->format('d/m/Y') : "";

            if (isset($offer_data->company_logo)) {
                $offer_data->company_logo = base64_encode($offer_data->company_logo);
            }


            $offer_number = !empty($offer_data->offer_no) ? '_' . str_replace('/', '_', $offer_data->offer_no) : "";
            $cust_name = !empty($offer_data->customer_name) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $offer_data->customer_name) : "";
            $offer_data->pdf_name = 'Offer' . $offer_number . $cust_name;
            $offer_data->in_use = $isUsed;
        } else {
            $offer_data = null;
        }

        $offer_details_data = DB::select('CALL offer_details(?)', [$request->id]);

        foreach ($offer_details_data as $dVal) {
            $dVal->thickness = $dVal->thickness ?? '';
            $dVal->mode = 'Update';
            $dVal->in_use = $isUsed;
        }

        return response()->json([
            'response_code'      => 1,
            'offer_data'         => $offer_data,
            'offer_details_data' => $offer_details_data,
        ]);
    }

    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            $usage = DB::select('CALL offer_used_list(?)', [$request->id]);
            if (!empty($usage)) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Offer Is Used In " . $usage[0]->table_name . ".",
                ]);
            }

            $is_auth = Offer::where('offer_id', $request->id)->first();
            if ($is_auth && $is_auth->authorized_id_fix == 1) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Offer Is Authorized",
                ]);
            }

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

        $validated = $request->validate([
            'customer_id'            => 'required',
            'offer_date'             => 'required',
          
            'material_type_id'       => 'required',
            'test_method'            => 'required',
            'procedure_ref_id'       => 'required',
            'evaluation_as_per_id'   => 'required',
            'acceptance_standard_id' => 'required',
            'radiation_energy_fix'   => 'required',
            'del_date'               => 'required',
            'offer_sequence' => [
                'required',
                'max:155',
                Rule::unique('offer')
                    ->where(function ($query) use ($year_data, $LocationData, $request) {
                        $query->where('year_id', $year_data->id)
                            ->where('current_location_id', $LocationData->location_id)
                            ->where('customer_id', $request->customer_id);
                    })
            ],
        ], [
            'customer_id.required'            => 'Select Customer.',
            'offer_date.required'             => 'Enter Offer Date.',
            'material_type_id.required'       => 'Select Material Type.',
            'test_method.required'            => 'Select Test Method.',
            'procedure_ref_id.required'       => 'Select Procedure Std. Ref.',
            'evaluation_as_per_id.required'   => 'Select Evaluation Followed.',
            'acceptance_standard_id.required' => 'Select Acceptance Std.',
            'radiation_energy_fix.required'   => 'Select Radiation Energy.',
            'del_date.required'               => 'Enter Del. Date.',
            'offer_sequence.unique'           => 'Duplicate Offer No. Found.',
            'offer_sequence.required'         => 'Enter Offer No.',
        ]);

        DB::beginTransaction();
        try {
            $isFound = Offer::where('year_id', $year_data->id)
                ->where('current_location_id', $LocationData->location_id)
                ->where('customer_id', $request->customer_id)
                ->where('offer_sequence', $request->offer_sequence)
                ->exists();

            if ($isFound) {
                return response()->json([
                    'response_code' => 0,
                    'response_message' => 'Duplicate Offer No. Found.'
                ]);
            }

            if (!empty($request->del_date) && !empty($request->offer_date)) {
                $delDate = Date::createFromFormat('d/m/Y', $request->del_date)->format('Y-m-d');
                $offerDate = Date::createFromFormat('d/m/Y', $request->offer_date)->format('Y-m-d');
                if ($delDate < $offerDate) {
                    return response()->json([
                        'response_code' => 0,
                        'response_message' => 'Del. Date Must Be Greater Than Offer Date.'
                    ]);
                }
            }

            if (!empty($request->challan_no) && empty($request->challan_date)) {
                return response()->json([
                    'response_code' => 0,
                    'response_message' => 'Enter Challan Date.'
                ]);
            }
            if (empty($request->challan_no) && !empty($request->challan_date)) {
                return response()->json([
                    'response_code' => 0,
                    'response_message' => 'Enter Challan No.'
                ]);
            }

            $offer_no       = $request->offer_no;
            $offer_sequence = $request->offer_sequence;

            $page_id = getMenuIdBassedOnDisplayName('offer');
            $assign_format_no = isset($page_id->id) ? getAssignFormateNoForTransaction($LocationData->location_id, $page_id->id, $request->offer_date) : null;

            $offer_id = Offer::insertGetId([
                'offer_no'               => $offer_no,
                'offer_sequence'         => $offer_sequence,
                'assign_format_no'       => $assign_format_no,
                'offer_date'             => $request->offer_date ? Date::createFromFormat('d/m/Y', $request->offer_date)->format('Y-m-d') : null,
                'nabl_type_fix'          => $request->nabl_type_fix,
                'test_at_fix'            => $request->test_at_fix,
                'job_type_fix'           => $request->job_type_fix,
                'customer_id'            => $request->customer_id,
                'challan_no'             => $request->challan_no,
                'challan_date'           => $request->challan_date ? Date::createFromFormat('d/m/Y', $request->challan_date)->format('Y-m-d') : null,
                'material_type_id'       => $request->material_type_id,
                'total_qty'              => $request->total_qty,
                'test_method_id'         => $request->test_method ?? $request->test_method_id,
                'procedure_reference_id' => $request->procedure_ref_id ?? $request->procedure_reference_id,
                'evaluation_as_per_id'   => $request->evaluation_as_per_id,
                'acceptance_standard_id' => $request->acceptance_standard_id,
                'radiation_energy_fix'   => $request->radiation_energy_fix,
                'coverage_area'          => $request->coverage_area,
                'procedure_submitted_fix'=> !empty($request->procedure_submitted_fix) ? $request->procedure_submitted_fix : null,
                'del_date'               => $request->del_date ? Date::createFromFormat('d/m/Y', $request->del_date)->format('Y-m-d') : null,
                'special_note'           => !empty($request->special_note) ? $request->special_note : (!empty($request->sp_note) ? $request->sp_note : null),
                'test_witness_tpi_fix'   => !empty($request->test_witness_tpi_fix) ? $request->test_witness_tpi_fix : null,
                'tpi_person'             => ($request->test_witness_tpi_fix == 'Yes' && !empty($request->tpi_person)) ? $request->tpi_person : null,
                'test_items_condition'   => !empty($request->test_items_condition) ? $request->test_items_condition : (!empty($request->test_item_condition) ? $request->test_item_condition : null),
                'public_domain_agree_fix'    => !empty($request->public_domain_agree_fix) ? $request->public_domain_agree_fix : null,
                'conformity_statement_fix'   => !empty($request->conformity_statement_fix) ? $request->conformity_statement_fix : null,
                'test_methods_defined_fix'   => !empty($request->test_methods_defined_fix) ? $request->test_methods_defined_fix : null,
                'laboratory_capability_fix'  => !empty($request->laboratory_capability_fix) ? $request->laboratory_capability_fix : null,
                'appropriate_test_method_fix'=> !empty($request->appropriate_test_method_fix) ? $request->appropriate_test_method_fix : null,
                'pertinent_discussions'  => !empty($request->pertinent_discussions) ? $request->pertinent_discussions : null,
                'deviation_intimate'     => !empty($request->deviation_intimate) ? $request->deviation_intimate : null,
                'contract_review_repeated_fix'=> !empty($request->contract_review_repeated_fix) ? $request->contract_review_repeated_fix : null,
                'current_location_id'    => $LocationData->location_id ?? null,
                'year_id'                => $year_data->id,
                'company_id'             => Auth::user()->company_id,
                'created_by'             => Auth::user()->id,
                'created_on'             => Carbon::now('Asia/Kolkata'),
            ]);

            $details = json_decode($request->offer_details_data, true);
            if (!empty($details)) {
                foreach ($details as $row) {
                    if (($row['mode'] ?? '') == 'Delete') continue;

                    OfferDetails::insert([
                        'offer_id'            => $offer_id,
                        'process_type_fix'    => $row['process_type_fix'] ?? 'Fresh',
                        'type_of_testing_id_fix' => $row['type_of_testing_id_fix'] ?? 'RT',
                        'sr_no'               => $row['sr_no'] ?? null,
                        'lab_id'              => $row['lab_id'] ?? '',
                        'customer_id_no'      => $row['customer_id_no'] ?? null,
                        'job_desc'            => $row['job_desc'] ?? '',
                        'die_no'              => $row['die_no'] ?? null,
                        'heat_no'             => $row['heat_no'] ?? null,
                        'drg_no'              => $row['drg_no'] ?? null,
                        'material_id'         => $row['material_id'] ?? null,
                        'thickness'           => $row['thickness']  ?? null,
                        'quantity'            => $row['quantity'] ?? 1,
                        'remark'              => $row['remark'] ?? null,
                    ]);
                }
            }

            DB::commit();

            $offer_number = !empty($offer_no) ? '_' . str_replace('/', '_', $offer_no) : "";
            $cust = DB::table('customers')->where('id', $request->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Offer' . $offer_number . $cust_name;

            if (function_exists('GeneratePdf')) {
                GeneratePdf($offer_id, $pdf_name, 'offer', 'add');
            }
            $encodedId = base64_encode($offer_id);
            $url = hasAccess("offer", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=offer") : "";

            return response()->json([
                'response_code'    => 1,
                'response_message' => 'Record Inserted.',
                'data'             => ['id' => $offer_id, 'file' => $url]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            report($e);
            return response()->json([
                'response_code'    => 0,
                'response_message' => 'Record Not Inserted.',
            ]);
        }
    }

        public function update(Request $request)
    {
        $year_data    = getCurrentYearData();
        $LocationData = getCurrentLocation();

        $is_auth = Offer::where('offer_id', $request->id)->value('authorized_id_fix');
        if ($is_auth == 1) {
            return response()->json([
                'response_code'    => 0,
                'response_message' => "Offer is Authorized. You can't Update It.",
            ]);
        }

        $validated = $request->validate([
            'customer_id'            => 'required',
            'offer_date'             => 'required',
            'material_type_id'       => 'required',
            'test_method'            => 'required',
            'procedure_ref_id'       => 'required',
            'evaluation_as_per_id'   => 'required',
            'acceptance_standard_id' => 'required',
            'radiation_energy_fix'   => 'required',
            'del_date'               => 'required',
            'offer_sequence' => [
                'required',
                'max:155',
                Rule::unique('offer')
                    ->where(function ($query) use ($year_data, $LocationData, $request) {
                        $query->where('year_id', $year_data->id)
                            ->where('current_location_id', $LocationData->location_id)
                            ->where('customer_id', $request->customer_id);
                    })
                    ->ignore($request->id, 'offer_id')
            ],
        ], [
            'customer_id.required'            => 'Select Customer.',
            'offer_date.required'             => 'Enter Offer Date.',
            'material_type_id.required'       => 'Select Material Type.',
            'test_method.required'            => 'Select Test Method.',
            'procedure_ref_id.required'       => 'Select Procedure Std. Ref.',
            'evaluation_as_per_id.required'   => 'Select Evaluation Followed.',
            'acceptance_standard_id.required' => 'Select Acceptance Std.',
            'radiation_energy_fix.required'   => 'Select Radiation Energy.',
            'del_date.required'               => 'Enter Del. Date.',
            'offer_sequence.unique'           => 'Duplicate Offer No. Found.',
            'offer_sequence.required'         => 'Enter Offer No.',
        ]);

        DB::beginTransaction();
        try {
            $isFound = DB::table('offer')
                ->where('year_id', $year_data->id)
                ->where('current_location_id', $LocationData->location_id)
                ->where('customer_id', $request->customer_id)
                ->where('offer_sequence', $request->offer_sequence)
                ->where('offer_id', '!=', $request->id)
                ->exists();

            if ($isFound) {
                return response()->json([
                    'response_code' => 0,
                    'response_message' => 'Duplicate Offer No. Found.'
                ]);
            }

            if (!empty($request->del_date) && !empty($request->offer_date)) {
                $delDate = Date::createFromFormat('d/m/Y', $request->del_date)->format('Y-m-d');
                $offerDate = Date::createFromFormat('d/m/Y', $request->offer_date)->format('Y-m-d');
                if ($delDate < $offerDate) {
                    return response()->json([
                        'response_code' => 0,
                        'response_message' => 'Del. Date Must Be Greater Than Offer Date.'
                    ]);
                }
            }

            if (!empty($request->challan_no) && empty($request->challan_date)) {
                return response()->json([
                    'response_code' => 0,
                    'response_message' => 'Enter Challan Date.'
                ]);
            }
            if (empty($request->challan_no) && !empty($request->challan_date)) {
                return response()->json([
                    'response_code' => 0,
                    'response_message' => 'Enter Challan No.'
                ]);
            }

            $page_id = getMenuIdBassedOnDisplayName('offer');
            $assign_format_no = isset($page_id->id) ? getAssignFormateNoForTransaction($LocationData->location_id, $page_id->id, $request->offer_date) : null;

            DB::table('offer')->where('offer_id', $request->id)->update([
                'offer_no'               => $request->offer_no,
                'offer_sequence'         => $request->offer_sequence,
                'assign_format_no'       => $assign_format_no,
                'offer_date'             => $request->offer_date ? Date::createFromFormat('d/m/Y', $request->offer_date)->format('Y-m-d') : null,
                'nabl_type_fix'          => $request->nabl_type_fix,
                'test_at_fix'            => $request->test_at_fix,
                'job_type_fix'           => $request->job_type_fix,
                'customer_id'            => $request->customer_id,
                'challan_no'             => $request->challan_no,
                'challan_date'           => $request->challan_date ? Date::createFromFormat('d/m/Y', $request->challan_date)->format('Y-m-d') : null,
                'material_type_id'       => $request->material_type_id,
                'total_qty'              => $request->total_qty,
                'test_method_id'         => $request->test_method ?? $request->test_method_id,
                'procedure_reference_id' => $request->procedure_ref_id ?? $request->procedure_reference_id,
                'evaluation_as_per_id'   => $request->evaluation_as_per_id,
                'acceptance_standard_id' => $request->acceptance_standard_id,
                'radiation_energy_fix'   => $request->radiation_energy_fix,
                'coverage_area'          => $request->coverage_area,
                'procedure_submitted_fix'=> !empty($request->procedure_submitted_fix) ? $request->procedure_submitted_fix : null,
                'del_date'               => $request->del_date ? Date::createFromFormat('d/m/Y', $request->del_date)->format('Y-m-d') : null,
                'special_note'           => !empty($request->special_note) ? $request->special_note : (!empty($request->sp_note) ? $request->sp_note : null),
                'test_witness_tpi_fix'   => !empty($request->test_witness_tpi_fix) ? $request->test_witness_tpi_fix : null,
                'tpi_person'             => ($request->test_witness_tpi_fix == 'Yes' && !empty($request->tpi_person)) ? $request->tpi_person : null,
                'test_items_condition'   => !empty($request->test_items_condition) ? $request->test_items_condition : (!empty($request->test_item_condition) ? $request->test_item_condition : null),
                'public_domain_agree_fix'    => !empty($request->public_domain_agree_fix) ? $request->public_domain_agree_fix : null,
                'conformity_statement_fix'   => !empty($request->conformity_statement_fix) ? $request->conformity_statement_fix : null,
                'test_methods_defined_fix'   => !empty($request->test_methods_defined_fix) ? $request->test_methods_defined_fix : null,
                'laboratory_capability_fix'  => !empty($request->laboratory_capability_fix) ? $request->laboratory_capability_fix : null,
                'appropriate_test_method_fix'=> !empty($request->appropriate_test_method_fix) ? $request->appropriate_test_method_fix : null,
                'pertinent_discussions'  => !empty($request->pertinent_discussions) ? $request->pertinent_discussions : null,
                'deviation_intimate'     => !empty($request->deviation_intimate) ? $request->deviation_intimate : null,
                'contract_review_repeated_fix'=> !empty($request->contract_review_repeated_fix) ? $request->contract_review_repeated_fix : null,
                'last_by'                => Auth::user()->id,
                'last_on'                => Carbon::now('Asia/Kolkata'),
            ]);

            $details = json_decode($request->offer_details_data, true);
            if (!empty($details)) {
                foreach ($details as $row) {
                    if (empty($row)) continue;
                    $mode = $row['mode'] ?? '';

                    if ($mode == 'Insert') {
                        OfferDetails::insert([
                            'offer_id'            => $request->id,
                            'process_type_fix'    => $row['process_type_fix'] ?? 'Fresh',
                            'type_of_testing_id_fix' => $row['type_of_testing_id_fix'] ?? 'RT',
                            'sr_no'               => $row['sr_no'] ?? null,
                            'lab_id'              => $row['lab_id'] ?? '',
                            'customer_id_no'      => $row['customer_id_no'] ?? null,
                            'job_desc'            => $row['job_desc'] ?? '',
                            'die_no'              => $row['die_no'] ?? null,
                            'heat_no'             => $row['heat_no'] ?? null,
                            'drg_no'              => $row['drg_no'] ?? null,
                            'material_id'         => $row['material_id'] ?? null,
                            'thickness'           => $row['thickness']?? null,
                            'quantity'            => $row['quantity'] ?? 1,
                            'remark'              => $row['remark'] ?? null,
                        ]);
                    } elseif ($mode == 'Update' && !empty($row['offer_details_id'])) {
                       OfferDetails::where('offer_details_id', $row['offer_details_id'])->update([
                            'process_type_fix'    => $row['process_type_fix'] ?? 'Fresh',
                            'type_of_testing_id_fix' => $row['type_of_testing_id_fix'] ?? 'RT',
                            'sr_no'               => $row['sr_no'] ?? null,
                            'lab_id'              => $row['lab_id'] ?? '',
                            'customer_id_no'      => $row['customer_id_no'] ?? null,
                            'job_desc'            => $row['job_desc'] ?? '',
                            'die_no'              => $row['die_no'] ?? null,
                            'heat_no'             => $row['heat_no'] ?? null,
                            'drg_no'              => $row['drg_no'] ?? null,
                            'material_id'         => $row['material_id'] ?? null,
                            'thickness'           => $row['thickness'] ?? null,
                            'quantity'            => $row['quantity'] ?? 1,
                            'remark'              => $row['remark'] ?? null,
                        ]);
                    } elseif ($mode == 'Delete' && !empty($row['offer_details_id'])) {
                       OfferDetails::where('offer_details_id', $row['offer_details_id'])->delete();
                    }
                }
            }

            DB::commit();

            $offer_number = !empty($request->offer_no) ? '_' . str_replace('/', '_', $request->offer_no) : "";
            $cust = DB::table('customers')->where('id', $request->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Offer' . $offer_number . $cust_name;

            if (function_exists('GeneratePdf')) {
                GeneratePdf($request->id, $pdf_name, 'offer', 'edit');
            }
            $encodedId = base64_encode($request->id);
            $url = hasAccess("offer", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=offer") : "";

            return response()->json([
                'response_code'    => 1,
                'response_message' => 'Record Updated.',
                'data'             => ['id' => $request->id, 'file' => $url]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            report($e);
            return response()->json([
                'response_code'    => 0,
                'response_message' => 'Record Not Updated.',
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
        $job_type_fix  = $request->job_type_fix;
        $test_at_fix   = $request->test_at_fix;

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

        // 1/2/3/4 wise: customer_id (4), nabl_type_fix (1), job_type_fix (2), test_at_fix (3)
        $query = Offer::where('year_id', $year_data->id)
            ->where('current_location_id', $LocationData->location_id)
            ->where('customer_id', $customer_id);

        if (!empty($nabl_type_fix)) {
            $query->where('nabl_type_fix', $nabl_type_fix);
        }
        if (!empty($job_type_fix)) {
            $query->where('job_type_fix', $job_type_fix);
        }
        if (!empty($test_at_fix)) {
            $query->where('test_at_fix', $test_at_fix);
        }

        $latestOffer = $query->orderBy('offer_id', 'desc')->first();

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

    public function getOldOfferDetails(Request $request)
    {
        if ($request->ajax()) {
            $customer = $request->post('customer');
            $jobTypeCasting = $request->post('job_type_casting');
            $typeOfTest = $request->post('type_of_test');
            $nablType = $request->post('nabl_type');

            if (empty($customer) || empty($jobTypeCasting) || empty($typeOfTest)) {
                return response()->json(['status' => 'success', 'data' => []]);
            }

            $current_location_id = getCurrentLocation()->location_id;

            // Find all offers for this customer and job type within current location
            $offerQuery = Offer::where('customer_id', $customer)
                ->where('job_type_fix', $jobTypeCasting)
                ->where('current_location_id', $current_location_id);

            if (!empty($nablType)) {
                $offerQuery->where('nabl_type_fix', $nablType);
            }

            $offerIds = $offerQuery->pluck('offer_id');

            if ($offerIds->count() > 0) {
                // Find all details matching the type of test
                $query = OfferDetails::whereIn('offer_details.offer_id', $offerIds)
                    ->join('offer', 'offer.offer_id', '=', 'offer_details.offer_id')
                    ->select('offer_details.*', 'offer.offer_no as inward_no', 'offer.offer_date as inward_date')
                    ->where('offer_details.type_of_testing_id_fix', $typeOfTest)
                    ->orderBy('offer_details.offer_details_id', 'DESC');

                $recentDetails = $query->get();

                return response()->json(['status' => 'success', 'data' => $recentDetails]);
            }
            return response()->json(['status' => 'success', 'data' => []]);
        }
    }

}
