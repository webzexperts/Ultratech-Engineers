<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\Offer;
use App\Models\Transaction\OfferDetails;
use App\Models\Transaction\MaterialInward;
use App\Models\Transaction\MaterialInwardDetails;
use App\Models\AuthorityPerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Carbon;

class OfferAuthorizedController extends Controller
{
    public function manage()
    {
        $current_location_id = getCurrentLocation()->location_id;

        $authorized_persons = AuthorityPerson::where('authorized_by', 'Yes')
            ->where('current_location_id', $current_location_id)
            ->where(function($q) {
                $q->whereNull('status')->orWhere('status', 1);
            })
            ->orderBy('operator', 'asc')
            ->get(['authority_person_id', 'operator']);

        return view('manage.transaction.manage-offer_authorized', compact('authorized_persons'));
    }

    public function index(Request $request, DataTables $datatables)
    {
        $current_location_id = getCurrentLocation()->location_id;
        $year_data = getCurrentYearData();
        $authorized_status = $request->input('authorized_status', 0);

        $offer_query = Offer::select([
                'offer.offer_id',
                'offer.offer_sequence',
                'offer.offer_no',
                'offer.offer_date',
                'offer.job_type_fix',
                'offer.nabl_type_fix',
                'offer.test_at_fix',
                'customers.customer as customer_name',
                'offer.dc_no',
                'offer.dc_date',
            ])
            ->leftJoin('customers', 'customers.id', '=', 'offer.customer_id')
            ->where('offer.year_id', $year_data->id)
            ->where('offer.current_location_id', $current_location_id);

        if ($authorized_status == 1) {
            $offer_query->where('offer.authorized_id', 1);
        } else {
            $offer_query->where(function($q) {
                $q->whereNull('offer.authorized_id')
                  ->orWhere('offer.authorized_id', 0);
            });
        }

        return DataTables::of($offer_query)
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="offer_checkbox form-check-input" name="offer_ids[]" value="' . $row->offer_id . '">';
            })
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
            ->filterColumn('offer.job_type_fix', function ($query, $keyword) {
                $globalSearch = request()->input('search.value');
                $searchValue = $globalSearch != '' ? $globalSearch : $keyword;
                $lowerKeyword = strtolower(trim($searchValue));
                if ($lowerKeyword === 'welding') {
                    $query->where('offer.job_type_fix', '=', 'Welding');
                } elseif ($lowerKeyword === 'non-welding' || $lowerKeyword === 'non welding') {
                    $query->where('offer.job_type_fix', '=', 'Non-Welding');
                } else {
                    $query->where('offer.job_type_fix', 'like', "{$lowerKeyword}%");
                }
            })
            ->rawColumns(['checkbox'])
            ->make(true);
    }

    public function getOfferDetails(Request $request)
    {
        $offer_ids = $request->input('offer_ids');
        if (!$offer_ids && $request->has('offer_id')) {
            $offer_ids = $request->input('offer_id');
        }

        if (!$offer_ids) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        if (!is_array($offer_ids)) {
            $offer_ids = explode(',', $offer_ids);
        }

        $details = OfferDetails::select([
                'offer_details.offer_details_id',
                'offer_details.offer_id',
                'offer_details.process_type as process_type',
                'offer_details.type_of_testing_id_fix',
                'offer_details.job_desc',
                //DB::raw("COALESCE(job_descriptions.job_description, offer_details.job_desc, '') as job_description"),
                'offer_details.product_code as die_no',
                'offer_details.heat_no',
                'offer_details.rt_no as drg_no',
                'materials.material as material_name',
                'evaluation_as_per.evaluation_as_per',
                'acceptance_standards.acceptance_standard',
                'procedure_reference.procedure_reference',
                'area_of_coverage.area_of_coverage',
                'offer_details.quantity as total_qty',
                'offer_details.quantity as req_qty',
                'offer_details.remark',
            ])
            //->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'offer_details.job_desc_id')
            ->leftJoin('materials', 'materials.id', '=', 'offer_details.material_id')
            ->leftJoin('procedure_reference', 'procedure_reference.procedure_reference_id', '=', 'offer_details.procedure_ref_id')
            ->leftJoin('evaluation_as_per', 'evaluation_as_per.evaluation_as_per_id', '=', 'offer_details.evaluation_as_per_id')
            ->leftJoin('acceptance_standards', 'acceptance_standards.id', '=', 'offer_details.acceptance_standard_id')
            ->leftJoin('area_of_coverage', 'area_of_coverage.area_of_coverage_id', '=', 'offer_details.area_of_coverage_id')
            ->whereIn('offer_details.offer_id', $offer_ids)
            ->get();

        return response()->json(['status' => 'success', 'data' => $details]);
    }

    public function authorizeOffer(Request $request)
    {
        $offer_ids = $request->input('offer_ids');
        $authorized_by_id = $request->input('authorized_by_id');

        if (empty($offer_ids)) {
            return response()->json(['status' => 'error', 'message' => 'Please Select At Least One Offer To Authorize.']);
        }

        if (empty($authorized_by_id)) {
            return response()->json(['status' => 'error', 'message' => 'Select Authorized By.']);
        }

        if (!is_array($offer_ids)) {
            $offer_ids = explode(',', $offer_ids);
        }

        $current_location_id = getCurrentLocation()->location_id;
        $page_id = getMenuIdBassedOnDisplayName('material_inward');

        DB::beginTransaction();
        try {
            $currentTime = Carbon::now('Asia/Kolkata');

            foreach ($offer_ids as $offer_id) {
                $offer = Offer::find($offer_id);
                if (!$offer) continue;

                // 1. Update Offer Record
                $offer->update([
                    'authorized_id' => 1,
                    'authorized_by_authority_person_id' => $authorized_by_id,
                    'authorized_created_on' => $currentTime,
                    'authorized_last_on' => $currentTime,
                    'authorized_by_user_id' => Auth::id(),
                ]);

                // 2. Generate Material Inward Sequence Number
                $num_format = getLatestSequence(MaterialInward::class, 'material_inward_sequence', 'INW');
                $material_inward_no = $num_format['format'];
                $material_inward_sequence = $num_format['isFound'];

                $effectDateFormatted = null;
                if (!empty($offer->offer_date)) {
                    try {
                        $effectDateFormatted = Date::createFromFormat('Y-m-d', $offer->offer_date)->format('d/m/Y');
                    } catch (\Exception $e) {
                        $effectDateFormatted = $offer->offer_date;
                    }
                }

                $assign_format_no = getAssignFormateNoForTransaction(
                    $current_location_id,
                    $page_id->id ?? null,
                    $effectDateFormatted
                );

                // 3. Create Material Inward Header
                $materialInward = MaterialInward::create([
                    'material_inward_no' => $material_inward_no,
                    'material_inward_sequence' => $material_inward_sequence,
                    'material_inward_date' => $offer->offer_date,
                    'current_location_id' => $current_location_id,
                    'company_id' => $offer->company_id,
                    'year_id' => $offer->year_id,
                    'offer_id' => $offer->offer_id,
                    'inward_type_value_fix' => 'From Offer',
                    'customer_id' => $offer->customer_id,
                    'dc_no' => $offer->dc_no,
                    'dc_date' => $offer->dc_date,
                    'po_no' => $offer->po_no,
                    'po_date' => $offer->po_date,
                    'nabl_type_fix' => $offer->nabl_type_fix,
                    'test_at_fix' => $offer->test_at_fix,
                    'job_type_fix' => $offer->job_type_fix,
                    'sample_drawn_by' => $offer->sample_drawn_by,
                    'is_any_tpi_witness' => $offer->is_any_tpi_witness,
                    'tpi_name' => $offer->tpi_name,
                    'condition_of_sample' => $offer->condition_of_sample,
                    'is_equipment_available' => $offer->is_equipment_available,
                    'competent_personnel_available' => $offer->competent_personnel_available,
                    'is_test_sub_contracted' => $offer->is_test_sub_contracted,
                    'test_feasible' => $offer->test_feasible,
                    'all_test_parameters_are_in_accredited_scope' => $offer->all_test_parameters_are_in_accredited_scope,
                    'required_statement_of_conformity' => $offer->required_statement_of_conformity,
                    'additional_requirement_from_customer' => $offer->additional_requirement_from_customer,
                    'special_note' => $offer->special_note,
                    'assign_format_no' => $assign_format_no,
                    'prepared_by_user_id' => Auth::id(),
                    'created_by' => Auth::id(),
                    'created_on' => $currentTime,
                ]);

                // 4. Create Material Inward Detail Items
                $offerDetails = OfferDetails::where('offer_id', $offer->offer_id)->get();
                foreach ($offerDetails as $detail) {
                    if ($offer->nabl_type_fix == 'NABL' && $detail->process_type != 'Repair') {
                        $is_observation_sheet = 'Yes';
                    } else if ($detail->process_type == 'Repair') {
                        $is_observation_sheet = 'N/A';
                    } else if ($detail->type_of_testing_id_fix != 'RT' && $offer->nabl_type_fix != 'NABL') {
                        $is_observation_sheet = 'N/A';
                    } else {
                        $is_observation_sheet = 'No';
                    }

                    MaterialInwardDetails::create([
                        'material_inward_id' => $materialInward->material_inward_id,
                        'offer_details_id' => $detail->offer_details_id,
                        'type_of_testing_id_fix' => $detail->type_of_testing_id_fix,
                        'process_type' => $detail->process_type ?? 'Fresh',
                        'is_observation_sheet' => $is_observation_sheet,
                        'test_report_rt_id' => $detail->test_report_rt_id,
                        'job_desc' => $detail->job_desc,
                        'part_no' => $detail->part_no,
                        'drg_no' => $detail->drg_no,
                        'material_id' => $detail->material_id,
                        'heat_no' => $detail->heat_no,
                        'rt_no' => $detail->rt_no,
                        'product_code' => $detail->product_code,
                        'thickness' => $detail->thickness,
                        'area_of_coverage_id' => $detail->area_of_coverage_id,
                        'procedure_ref_id' => $detail->procedure_ref_id,
                        'evaluation_as_per_id' => $detail->evaluation_as_per_id,
                        'acceptance_standard_id' => $detail->acceptance_standard_id,
                        'quantity' => $detail->quantity ?? 0,
                        'approx_value' => $detail->approx_value,
                        'approx_weight' => $detail->approx_weight,
                        'remark' => $detail->remark,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Offer Authorized Successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed to authorize offer: ' . $e->getMessage()]);
        }
    }

    public function unauthorizeOffer(Request $request)
    {
        $offer_ids = $request->input('offer_ids');

        if (empty($offer_ids)) {
            return response()->json(['status' => 'error', 'message' => 'Please Select At Least One Offer To Unauthorize.']);
        }

        if (!is_array($offer_ids)) {
            $offer_ids = explode(',', $offer_ids);
        }

        DB::beginTransaction();
        try {
            $currentTime = Carbon::now('Asia/Kolkata');

            foreach ($offer_ids as $offer_id) {
                $offer = Offer::find($offer_id);
                if (!$offer) continue;

                // Find associated Material Inward record
                $materialInward = MaterialInward::where('offer_id', $offer_id)->first();
                if ($materialInward) {
                    // Check if Material Inward is used in downstream transactions
                    $usage = DB::select('CALL material_inward_used_list(?)', [$materialInward->material_inward_id]);
                    if (!empty($usage)) {
                        DB::rollBack();
                        $tableName = $usage[0]->table_name ?? 'another transaction';
                        return response()->json([
                            'status' => 'error',
                            'message' => "You Can't Unauthorize. Offer Is Used In {$tableName}."
                        ]);
                    }

                    // Material Inward is not used anywhere, delete child details and header
                    MaterialInwardDetails::where('material_inward_id', $materialInward->material_inward_id)->delete();
                    $materialInward->delete();
                }

                // Reset Offer status
                $offer->update([
                    'authorized_id' => 0,
                    'authorized_last_by_user_id' => Auth::id(),
                    'authorized_last_on' => $currentTime,
                ]);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Offer Unauthorized Successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed to unauthorize offer: ' . $e->getMessage()]);
        }
    }
}
