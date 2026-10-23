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
        return view('manage.transaction.manage-offer_authorized');
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
                'offer.challan_no',
                'offer.challan_date',
                'evaluation_as_per.evaluation_as_per',
                'acceptance_standards.acceptance_standard',
                'test_method.test_method',
                'offer.coverage_area as area_of_coverage',
                'offer.radiation_energy_fix',
                'offer.del_date',
                'offer.procedure_submitted_fix',
            ])
            ->leftJoin('customers', 'customers.id', '=', 'offer.customer_id')
            ->leftJoin('evaluation_as_per', 'evaluation_as_per.evaluation_as_per_id', '=', 'offer.evaluation_as_per_id')
            ->leftJoin('acceptance_standards', 'acceptance_standards.id', '=', 'offer.acceptance_standard_id')
            ->leftJoin('test_method', 'test_method.test_method_id', '=', 'offer.test_method_id')
            ->where('offer.year_id', $year_data->id)
            ->where('offer.current_location_id', $current_location_id);

        if ($authorized_status == 1) {
            $offer_query->where('offer.authorized_id_fix', 1);
        } else {
            $offer_query->where(function($q) {
                $q->whereNull('offer.authorized_id_fix')
                  ->orWhere('offer.authorized_id_fix', 0);
            });
        }

        $base_query = clone $offer_query;

        $dataTable = DataTables::of($offer_query)
            ->filterColumn('offer.offer_no', function ($query, $keyword) {
                applySequenceSearch($query, $keyword, 'offer.offer_no');
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
                if ($lowerKeyword === 'welding') {
                    $query->where('offer.job_type_fix', '=', 'Welding');
                } elseif ($lowerKeyword === 'non-welding' || $lowerKeyword === 'non welding') {
                    $query->where('offer.job_type_fix', '=', 'Non-Welding');
                } else {
                    $query->where('offer.job_type_fix', 'like', "{$lowerKeyword}%");
                }
            })
            ->filterColumn('offer.nabl_type_fix', function ($query, $keyword) {
                $query->where('offer.nabl_type_fix', 'like', "%" . trim($keyword) . "%");
            })
            ->filterColumn('customers.customer', function ($query, $keyword) {
                $query->where('customers.customer', 'like', "%" . trim($keyword) . "%");
            })
            ->filterColumn('offer.challan_no', function ($query, $keyword) {
                $query->where('offer.challan_no', 'like', "%" . trim($keyword) . "%");
            })
            ->filterColumn('test_method.test_method', function ($query, $keyword) {
                $query->where('test_method.test_method', 'like', "%" . trim($keyword) . "%");
            })
            ->filterColumn('evaluation_as_per.evaluation_as_per', function ($query, $keyword) {
                $query->where('evaluation_as_per.evaluation_as_per', 'like', "%" . trim($keyword) . "%");
            })
            ->filterColumn('acceptance_standards.acceptance_standard', function ($query, $keyword) {
                $query->where('acceptance_standards.acceptance_standard', 'like', "%" . trim($keyword) . "%");
            })
            ->filterColumn('offer.coverage_area', function ($query, $keyword) {
                $query->where('offer.coverage_area', 'like', "%" . trim($keyword) . "%");
            })
            ->filterColumn('offer.radiation_energy_fix', function ($query, $keyword) {
                $query->where('offer.radiation_energy_fix', 'like', "%" . trim($keyword) . "%");
            })
            ->editColumn('del_date', function ($row) {
                if ($row->del_date != null) {
                    return Date::createFromFormat('Y-m-d', $row->del_date)->format(DATE_FORMAT);
                }
                return '';
            })
            ->filterColumn('offer.del_date', function ($q, $k) {
                applyDate($q, $k, 'offer.del_date');
            })
            ->filterColumn('offer.procedure_submitted_fix', function ($query, $keyword) {
                $query->where('offer.procedure_submitted_fix', 'like', "%" . trim($keyword) . "%");
            })
            ->make(true);

        $selected_offer_id = $request->input('selected_offer_id');
        if (!empty($selected_offer_id)) {
            $json = $dataTable->getData();
            $exists = false;
            if (!empty($json->data)) {
                foreach ($json->data as $row) {
                    if (isset($row->offer_id) && $row->offer_id == $selected_offer_id) {
                        $exists = true;
                        break;
                    }
                }
            }
            if (!$exists) {
                $selectedRow = $base_query->where('offer.offer_id', $selected_offer_id)->first();
                if ($selectedRow) {
                    if ($selectedRow->offer_date != null) {
                        try {
                            $selectedRow->offer_date = Date::createFromFormat('Y-m-d', $selectedRow->offer_date)->format(DATE_FORMAT);
                        } catch (\Exception $e) {}
                    }
                    if ($selectedRow->challan_date != null) {
                        try {
                            $selectedRow->challan_date = Date::createFromFormat('Y-m-d', $selectedRow->challan_date)->format(DATE_FORMAT);
                        } catch (\Exception $e) {}
                    }
                    if ($selectedRow->del_date != null) {
                        try {
                            $selectedRow->del_date = Date::createFromFormat('Y-m-d', $selectedRow->del_date)->format(DATE_FORMAT);
                        } catch (\Exception $e) {}
                    }
                    array_unshift($json->data, $selectedRow);
                    $json->recordsFiltered = count($json->data);
                    $dataTable->setData($json);
                }
            }
        }

        return $dataTable;
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
                'offer_details.process_type_fix as process_type',
                'offer_details.type_of_testing_id_fix',
                'offer_details.lab_id',
                'offer_details.customer_id_no',
                'offer_details.job_desc',
                'offer_details.die_no',
                'offer_details.heat_no',
                'offer_details.drg_no',
                'materials.material as material_name',
                'offer_details.thickness',
                'offer_details.quantity',
                'offer_details.remark',
            ])
            ->leftJoin('materials', 'materials.id', '=', 'offer_details.material_id')
            ->whereIn('offer_details.offer_id', $offer_ids)
            ->get();

        return response()->json(['status' => 'success', 'data' => $details]);
    }

    public function authorizeOffer(Request $request)
    {
        $offer_ids = $request->input('offer_ids');

        if (empty($offer_ids)) {
            return response()->json(['status' => 'error', 'message' => 'Please Select At Least One Offer To Authorise.']);
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
                // Row-level lock to prevent race conditions across multiple systems
                $offer = Offer::where('offer_id', $offer_id)->lockForUpdate()->first();
                if (!$offer) continue;

                // Check 1: Check if already authorized
                if ($offer->authorized_id_fix == 1) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => "Offer is already authorised."
                    ]);
                }

                // Check 2: Check if Material Inward already created for this offer
                $alreadyInward = MaterialInward::where('offer_id', $offer->offer_id)->first();
                if ($alreadyInward) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => "Material Inward is already created for Offer."
                    ]);
                }

                // 1. Update Offer Record
                $offer->update([
                    'authorized_id_fix' => 1,
                    'authorized_created_by' => Auth::id(),
                    'authorized_created_on' => $currentTime,
                ]);

                // 2. Lock Material Inward table for this year/location to prevent sequence number collision between 2 systems
                MaterialInward::where('year_id', $offer->year_id)
                    ->where('current_location_id', $current_location_id)
                    ->orderBy('material_inward_sequence', 'desc')
                    ->lockForUpdate()
                    ->first();

                // Generate Material Inward Sequence Number
                $num_format = getLatestSequence(MaterialInward::class, 'material_inward_sequence', '');
                
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
                    'material_inward_no'       => $material_inward_no,
                    'material_inward_sequence' => $material_inward_sequence,
                    'material_inward_date'     => $offer->offer_date,
                    'current_location_id'      => $current_location_id,
                    'company_id'               => $offer->company_id,
                    'year_id'                  => $offer->year_id,
                    'offer_id'                 => $offer->offer_id,
                    'form_type_value_fix'      => 'From Offer',
                    'nabl_type_fix'            => $offer->nabl_type_fix,
                    'test_at_fix'              => $offer->test_at_fix,
                    'job_type_fix'             => $offer->job_type_fix,
                    'customer_id'              => $offer->customer_id,
                    'challan_no'               => $offer->challan_no,
                    'challan_date'             => $offer->challan_date,
                    'offer_no'                 => $offer->offer_no,
                    'offer_date'               => $offer->offer_date,
                    'material_type_id'         => $offer->material_type_id ?? null,
                    'total_qty'                => $offer->total_qty ?? 0,
                    'test_method_id'           => $offer->test_method_id,
                    'procedure_reference_id'   => $offer->procedure_reference_id,
                    'evaluation_as_per_id'     => $offer->evaluation_as_per_id,
                    'acceptance_standard_id'   => $offer->acceptance_standard_id,
                    'radiation_energy_fix'     => $offer->radiation_energy_fix ?? 'Gamma Rays',
                    'coverage_area'            => $offer->coverage_area ?? '',
                    'procedure_submitted_fix'      => $offer->procedure_submitted_fix ?? null,
                    'del_date'                 => $offer->del_date ?? $offer->offer_date,
                    'special_note'             => $offer->special_note ?? null,
                    'assign_format_no'         => $assign_format_no,
                    'created_by'               => Auth::id(),
                    'created_on'               => $currentTime,
                ]);

                // 4. Create Material Inward Detail Items
                $offerDetails = OfferDetails::where('offer_id', $offer->offer_id)->get();
                foreach ($offerDetails as $detail) {
                    MaterialInwardDetails::create([
                        'material_inward_id'     => $materialInward->material_inward_id,
                        'process_type_fix'       => $detail->process_type_fix ?? 'Fresh',
                        'type_of_testing_id_fix' => $detail->type_of_testing_id_fix ?? 'RT',
                        'sr_no'                  => $detail->sr_no ?? null,
                        'lab_id'                 => $detail->lab_id ?? '',
                        'customer_id_no'         => $detail->customer_id_no ?? null,
                        'job_desc'               => $detail->job_desc ?? '',
                        'die_no'                 => $detail->die_no ?? null,
                        'heat_no'                => $detail->heat_no ?? null,
                        'drg_no'                 => $detail->drg_no ?? null,
                        'material_id'            => $detail->material_id,
                        'thickness'              => $detail->thickness ?? null,
                        'quantity'               => $detail->quantity ?? 0,
                        'remark'                 => $detail->remark ?? null,
                        'offer_details_id'       => $detail->offer_details_id,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Offer Authorised.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed to authorise offer: ' . $e->getMessage()]);
        }
    }

    public function unauthorizeOffer(Request $request)
    {
        $offer_ids = $request->input('offer_ids');

        if (empty($offer_ids)) {
            return response()->json(['status' => 'error', 'message' => 'Please Select At Least One Offer To Unauthosize.']);
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
                            'message' => "You Can't Unauthorise. Offer Is Used In {$tableName}."
                        ]);
                    }

                    // Material Inward is not used anywhere, delete child details and header
                    MaterialInwardDetails::where('material_inward_id', $materialInward->material_inward_id)->delete();
                    $materialInward->delete();
                }

                // Reset Offer status
                $offer->update([
                    'authorized_id_fix' => 0,
                    'authorized_last_by' => Auth::id(),
                    'authorized_last_on' => $currentTime,
                ]);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Offer Unauthorised.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Failed to unauthorise offer: ' . $e->getMessage()]);
        }
    }
}
