<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\Offer;
use App\Models\Transaction\OfferDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Date;

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
                'procedure_reference.procedure_reference',
                'evaluation_as_per.evaluation_as_per',
                'acceptance_standards.acceptance_standard',
                'area_of_coverage.area_of_coverage',
                DB::raw("GROUP_CONCAT(DISTINCT offer_details.type_of_testing_id_fix SEPARATOR ', ') as process_type"),
            ])
            ->leftJoin('offer_details', 'offer_details.offer_id', '=', 'offer.offer_id')
            ->leftJoin('customers', 'customers.id', '=', 'offer.customer_id')
            ->leftJoin('procedure_reference', 'procedure_reference.procedure_reference_id', '=', 'offer_details.procedure_ref_id')
            ->leftJoin('evaluation_as_per', 'evaluation_as_per.evaluation_as_per_id', '=', 'offer_details.evaluation_as_per_id')
            ->leftJoin('acceptance_standards', 'acceptance_standards.id', '=', 'offer_details.acceptance_standard_id')
            ->leftJoin('area_of_coverage', 'area_of_coverage.area_of_coverage_id', '=', 'offer_details.area_of_coverage_id')
            ->where('offer.year_id', $year_data->id)
            ->where('offer.current_location_id', $current_location_id)
            ->where(function($q) {
                $q->whereNull('offer.authorized_id')
                  ->orWhere('offer.authorized_id', 0);
            })
            ->groupBy([
                'offer.offer_id',
                'offer.offer_sequence',
                'offer.offer_no',
                'offer.offer_date',
                'offer.job_type_fix',
                'offer.nabl_type_fix',
                'offer.test_at_fix',
                'customers.customer',
                'offer.dc_no',
                'offer.dc_date',
                'procedure_reference.procedure_reference',
                'evaluation_as_per.evaluation_as_per',
                'acceptance_standards.acceptance_standard',
                'area_of_coverage.area_of_coverage',
            ]);

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
            ->rawColumns(['checkbox'])
            ->make(true);
    }

    public function getOfferDetails(Request $request)
    {
        $offer_id = $request->input('offer_id');

        if (!$offer_id) {
            return response()->json(['status' => 'error', 'data' => []]);
        }

        $details = OfferDetails::select([
                'offer_details.offer_details_id',
                'offer_details.offer_id',
                'offer_details.type_of_testing_id_fix as process_type',
                'job_descriptions.job_description',
                'offer_details.product_code as die_no',
                'offer_details.heat_no',
                'offer_details.rt_no as drg_no',
                'materials.material',
                'offer_details.quantity as total_qty',
                'offer_details.quantity as req_qty',
                'offer_details.remark',
            ])
            ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'offer_details.job_desc_id')
            ->leftJoin('materials', 'materials.id', '=', 'offer_details.material_id')
            ->where('offer_details.offer_id', $offer_id)
            ->get();

        return response()->json(['status' => 'success', 'data' => $details]);
    }

    public function authorizeOffer(Request $request)
    {
        $offer_ids = $request->input('offer_ids');

        if (empty($offer_ids)) {
            return response()->json(['status' => 'error', 'message' => 'Please select at least one offer to authorize.']);
        }

        if (!is_array($offer_ids)) {
            $offer_ids = explode(',', $offer_ids);
        }

        Offer::whereIn('offer_id', $offer_ids)->update([
            'authorized_id' => 1
        ]);

        return response()->json(['status' => 'success', 'message' => 'Offer(s) Authorized Successfully!']);
    }

    public function unauthorizeOffer(Request $request)
    {
        $offer_ids = $request->input('offer_ids');

        if (empty($offer_ids)) {
            return response()->json(['status' => 'error', 'message' => 'Please select at least one offer.']);
        }

        if (!is_array($offer_ids)) {
            $offer_ids = explode(',', $offer_ids);
        }

        Offer::whereIn('offer_id', $offer_ids)->update([
            'authorized_id' => null
        ]);

        return response()->json(['status' => 'success', 'message' => 'Offer(s) Unauthorized Successfully!']);
    }
}
