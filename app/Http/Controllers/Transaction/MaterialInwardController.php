<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\MaterialInward;
use App\Models\Transaction\MaterialInwardDetails;
use App\Models\Transaction\Offer;
use App\Models\Transaction\OfferDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class MaterialInwardController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-material_inward');
    }

    public function index(MaterialInward $mi_data, Request $request, DataTables $datatables)
    {
        $current_location_id = getCurrentLocation()->location_id;
        $year_data = getCurrentYearData();

        $mi_data = MaterialInward::select([
            'material_inward.material_inward_sequence',
            'material_inward.material_inward_id',
            'material_inward.material_inward_no',
            'material_inward.material_inward_date',
            'material_inward.nabl_type_fix',
            'material_inward.test_at_fix',
            'material_inward.job_type_fix',
            'customers.customer',
            'material_inward.challan_no',
            'material_inward.challan_date',
            'material_inward.offer_no',
            'material_inward.offer_date',
            'material_inward_details.process_type_fix',
            'material_inward_details.type_of_testing_id_fix',
            'material_inward_details.job_desc as job_description',
            'material_inward_details.sr_no',
            'material_inward_details.customer_id_no',
            'material_inward_details.die_no',
            'material_inward_details.drg_no',
            'materials.material',
            'material_inward_details.heat_no',
            'material_inward_details.lab_id',
            'material_inward_details.thickness',
            'material_inward_details.quantity',
            'material_inward.created_on',
            'material_inward.created_by',
            'material_inward.last_by',
            'material_inward.last_on',
        ])
        ->leftJoin('material_inward_details', 'material_inward_details.material_inward_id', '=', 'material_inward.material_inward_id')
        ->leftJoin('customers', 'customers.id', '=', 'material_inward.customer_id')
        ->leftJoin('materials', 'materials.id', '=', 'material_inward_details.material_id')
        ->where('material_inward.year_id', $year_data->id)
        ->where('material_inward.current_location_id', $current_location_id);

        $dataTable = DataTables::of($mi_data)
        ->editColumn('material_inward_date', function ($mi_data) {
            if ($mi_data->material_inward_date != null) {
                return Date::createFromFormat('Y-m-d', $mi_data->material_inward_date)->format(DATE_FORMAT);
            }
            return '';
        })
        ->filterColumn('material_inward.material_inward_date', function ($q, $k) {
            applyDate($q, $k, 'material_inward.material_inward_date');
        })
        ->editColumn('challan_date', function ($mi_data) {
            if ($mi_data->challan_date != null) {
                return Date::createFromFormat('Y-m-d', $mi_data->challan_date)->format(DATE_FORMAT);
            }
            return '';
        })
        ->filterColumn('material_inward.challan_date', function ($q, $k) {
            applyDate($q, $k, 'material_inward.challan_date');
        })
        ->editColumn('offer_date', function ($mi_data) {
            if ($mi_data->offer_date != null) {
                return Date::createFromFormat('Y-m-d', $mi_data->offer_date)->format(DATE_FORMAT);
            }
            return '';
        })
        ->filterColumn('material_inward.offer_date', function ($q, $k) {
            applyDate($q, $k, 'material_inward.offer_date');
        })
        ->filterColumn('material_inward.job_type_fix', function ($query, $keyword) {
            $globalSearch = request()->input('search.value');
            $searchValue = $globalSearch != '' ? $globalSearch : $keyword;
            $lowerKeyword = strtolower(trim($searchValue));
            if ($lowerKeyword === 'casting') {
                $query->where('material_inward.job_type_fix', '=', 'Casting');
            } elseif ($lowerKeyword === 'fabrication') {
                $query->where('material_inward.job_type_fix', '=', 'Fabrication');
            } else {
                $query->where('material_inward.job_type_fix', 'like', "{$lowerKeyword}%");
            }
        })
        ->editColumn('quantity', function ($mi_data) {
            return $mi_data->quantity > 0 ? number_format((float)$mi_data->quantity, 0, '.', '') : '';
        })
        ->filterColumn('material_inward.material_inward_no', function ($query, $keyword) {
            applySequenceSearch($query, $keyword, 'material_inward.material_inward_no');
        })
        ->addColumn('options', function ($mi_data) {
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("material_inward", "print")) {
                    $mi_number = !empty($mi_data->material_inward_no) ? '_' . str_replace('/', '_', $mi_data->material_inward_no) : "";
                    $cust_name = !empty($mi_data->customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $mi_data->customer) : "";
                    $pdfName   = 'Material_Inward' . $mi_number . $cust_name;
                    $encodedId = base64_encode($mi_data->material_inward_id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=material_inward");
                    $action .= '<li><a class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }

                if (hasAccess("material_inward", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-material_inward"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("material_inward", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });

        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'material_inward');
        return $dataTable
        ->rawColumns(['options', 'last_by', 'last_on', 'created_by', 'created_on'])
        ->make(true);
    }

    public function getLatestMaterialInwardNumber(Request $request)
    {
        $modal    = MaterialInward::class;
        $sequence = 'material_inward_sequence';
        $prefix   = '';

        $num_format = getLatestSequence($modal, $sequence, $prefix);

        return response()->json([
            'response_code' => 1,
            'latest_no'     => $num_format['format'],
            'number'        => $num_format['isFound'],
        ]);
    }

    public function edit(Request $request)
    {
        $mi_data = DB::select('CALL material_inward_master(?)', [$request->id]);

        if (!empty($mi_data)) {
            $mi_data = $mi_data[0];
            $mi_data->material_inward_date = !empty($mi_data->material_inward_date) ? Date::createFromFormat('Y-m-d', $mi_data->material_inward_date)->format('d/m/Y') : "";
            $mi_data->challan_date = !empty($mi_data->challan_date) ? Date::createFromFormat('Y-m-d', $mi_data->challan_date)->format('d/m/Y') : (!empty($mi_data->dc_date) ? Date::createFromFormat('Y-m-d', $mi_data->dc_date)->format('d/m/Y') : "");
            $mi_data->dc_date = $mi_data->challan_date;
            $mi_data->offer_date = !empty($mi_data->offer_date) ? Date::createFromFormat('Y-m-d', $mi_data->offer_date)->format('d/m/Y') : (!empty($mi_data->po_date) ? Date::createFromFormat('Y-m-d', $mi_data->po_date)->format('d/m/Y') : "");
            $mi_data->po_date = $mi_data->offer_date;
            $mi_data->del_date = !empty($mi_data->del_date) ? Date::createFromFormat('Y-m-d', $mi_data->del_date)->format('d/m/Y') : "";

            if (isset($mi_data->company_logo)) {
                $mi_data->company_logo = base64_encode($mi_data->company_logo);
            }

            $mi_number = !empty($mi_data->material_inward_no) ? '_' . str_replace('/', '_', $mi_data->material_inward_no) : "";
            $cust_name = !empty($mi_data->customer_name) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $mi_data->customer_name) : "";
            $mi_data->pdf_name = 'Material_Inward' . $mi_number . $cust_name;

            // Safety check: if stored procedure in DB is not yet recompiled with new fields, fill from Eloquent
            if (!isset($mi_data->offer_id) || !isset($mi_data->material_type_id)) {
                $rawMi = MaterialInward::find($request->id);
                if ($rawMi) {
                    foreach ($rawMi->getAttributes() as $attr => $val) {
                        if (!isset($mi_data->$attr) || $mi_data->$attr === null) {
                            $mi_data->$attr = $val;
                        }
                    }
                    if (empty($mi_data->challan_date) && !empty($rawMi->challan_date)) {
                        $mi_data->challan_date = Date::createFromFormat('Y-m-d', $rawMi->challan_date)->format('d/m/Y');
                    }
                    if (empty($mi_data->offer_date) && !empty($rawMi->offer_date)) {
                        $mi_data->offer_date = Date::createFromFormat('Y-m-d', $rawMi->offer_date)->format('d/m/Y');
                    }
                    if (empty($mi_data->del_date) && !empty($rawMi->del_date)) {
                        $mi_data->del_date = Date::createFromFormat('Y-m-d', $rawMi->del_date)->format('d/m/Y');
                    }
                }
            }
            $mi_data->procedure_ref_id = $mi_data->procedure_reference_id ?? ($mi_data->procedure_ref_id ?? '');
        }

        $mi_details_data = DB::select('CALL material_inward_details(?)', [$request->id]);

        $usage = [];
        try {
            $usage = DB::select('CALL material_inward_used_list(?)', [$request->id]);
        } catch (\Exception $e) {
            $usage = [];
        }

        $detail_used_map = [];
        $detail_dc_used_map = [];
        $detail_direct_obs_used_map = [];
        foreach ($usage as $uRow) {
            $detId = $uRow->material_inward_details_id ?? null;
            if ($detId) {
                if (!isset($detail_used_map[$detId])) {
                    $detail_used_map[$detId] = 0;
                }
                $detail_used_map[$detId] += (float)($uRow->used_qty ?? 0);

                $tableName = $uRow->table_name ?? '';
                if ($tableName === 'Material Outward') {
                    if (!isset($detail_dc_used_map[$detId])) {
                        $detail_dc_used_map[$detId] = 0;
                    }
                    $detail_dc_used_map[$detId] += (float)($uRow->used_qty ?? 0);
                }

                if ($tableName === 'Observation Sheet') {
                    if (!isset($detail_direct_obs_used_map[$detId])) {
                        $detail_direct_obs_used_map[$detId] = 0;
                    }
                    $detail_direct_obs_used_map[$detId] += (float)($uRow->used_qty ?? 0);
                }
            }
        }

        $detail_ids = [];
        if ($mi_details_data) {
            foreach ($mi_details_data as $dVal) {
                if (!empty($dVal->material_inward_details_id)) {
                    $detail_ids[] = $dVal->material_inward_details_id;
                }
                $dVal->mode = 'Update';
                $dVal->thickness = $dVal->thickness ?? '';
                $dVal->job_desc = $dVal->job_desc ?? '';
                $dVal->process_type_fix = $dVal->process_type_fix ?? 'Fresh';
                $dVal->process_type_name = $dVal->type_of_testing_id_fix ?? '';
                $dVal->offer_details_id = $dVal->offer_details_id ?? 0;

                $detId = $dVal->material_inward_details_id;
                $totUsed = $detail_used_map[$detId] ?? 0;
                $dcUsed  = $detail_dc_used_map[$detId] ?? 0;
                $directObsUsed = $detail_direct_obs_used_map[$detId] ?? 0;

                $dVal->used_qty    = (float)$totUsed;
                $dVal->used_qty_dc = (float)$dcUsed;
                $dVal->is_direct_obs_used = ($directObsUsed > 0);
                $dVal->in_use      = ($totUsed > 0);
            }
        }

        $min_report_date = null;
        if (!empty($detail_ids)) {
            $min_report_date = DB::query()
                ->fromSub(function ($query) use ($detail_ids) {
                    $query->from('test_report_rt')
                        ->select('test_report_date')
                        ->whereIn('material_inward_details_id', $detail_ids)
                        ->whereNotNull('test_report_date')
                        ->where('test_report_date', '!=', '0000-00-00')
                        ->unionAll(
                            DB::table('test_report_ut')
                                ->select('test_report_date')
                                ->whereIn('material_inward_details_id', $detail_ids)
                                ->whereNotNull('test_report_date')
                                ->where('test_report_date', '!=', '0000-00-00')
                        )
                        ->unionAll(
                            DB::table('test_report_dpt')
                                ->select('test_report_date')
                                ->whereIn('material_inward_details_id', $detail_ids)
                                ->whereNotNull('test_report_date')
                                ->where('test_report_date', '!=', '0000-00-00')
                        )
                        ->unionAll(
                            DB::table('test_report_mpt')
                                ->select('test_report_date')
                                ->whereIn('material_inward_details_id', $detail_ids)
                                ->whereNotNull('test_report_date')
                                ->where('test_report_date', '!=', '0000-00-00')
                        );
                }, 'reports')
                ->min('test_report_date');

            if ($min_report_date) {
                $min_report_date = Date::createFromFormat('Y-m-d', $min_report_date)
                    ->format('d/m/Y');
            }
        }

        if ($mi_data) {
            return response()->json([
                'mi_data'              => $mi_data,
                'mi_details_data'      => $mi_details_data,
                'min_report_date'      => $min_report_date,
                'response_code'        => '1',
                'response_message'     => '',
            ]);
        }

        return response()->json([
            'response_code'    => '0',
            'response_message' => 'Record Does Not Exists',
        ]);
    }

    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            $usage = [];
            try {
                $usage = DB::select('CALL material_inward_used_list(?)', [$request->id]);
            } catch (\Exception $e) {
                $usage = [];
            }

            if (!empty($usage)) {
                $message = "You Can't Delete, Material Inward Is Used In " . $usage[0]->table_name . ".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            $materialInward = MaterialInward::find($request->id);
            if ($materialInward && !empty($materialInward->offer_id)) {
                Offer::where('offer_id', $materialInward->offer_id)->update([
                    'authorized_id_fix' => 0,
                    'authorized_last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                    'authorized_last_by' => Auth::user()->id, 
                ]);
            }

            MaterialInwardDetails::where('material_inward_id', $request->id)->delete();
            MaterialInward::where('material_inward_id', $request->id)->delete();

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

    public function store(Request $request)
    {
        $year_data    = getCurrentYearData();
        $LocationData = getCurrentLocation();

        DB::beginTransaction();
        try {
            // Resolve / lock the auto number
            $existNumber = MaterialInward::where([
                ['material_inward_sequence', $request->material_inward_sequence],
                ['material_inward_no', $request->material_inward_no],
                ['year_id', $year_data->id],
                ['current_location_id', $LocationData->location_id],
            ])->first();

            if ($existNumber) {
                $latestNo = $this->getLatestMaterialInwardNumber($request);
                $area = json_decode($latestNo->getContent(), true);
                $material_inward_no       = $area['latest_no'];
                $material_inward_sequence = $area['number'];
            } else {
                $material_inward_no       = $request->material_inward_no;
                $material_inward_sequence = $request->material_inward_sequence;
            }

            $checkInwardDup = checkReportDuplication(MaterialInward::class, 'material_inward_no', $material_inward_no, $request, "Duplicate Inward No. Found.");
            if ($checkInwardDup) {
                return response()->json($checkInwardDup);
            }

            $page_id = getMenuIdBassedOnDisplayName('material_inward');
            $assign_format_no = getAssignFormateNoForTransaction($LocationData->location_id, $page_id->id, $request->material_inward_date);

            $mi = MaterialInward::create([
                'material_inward_no'       => $material_inward_no,
                'material_inward_sequence' => $material_inward_sequence,
                'material_inward_date'     => $request->material_inward_date ? Date::createFromFormat('d/m/Y', $request->material_inward_date)->format('Y-m-d') : null,
                'form_type_value_fix'      => 'Manual',
                'nabl_type_fix'            => $request->nabl_type_fix,
                'test_at_fix'              => $request->test_at_fix,
                'job_type_fix'             => $request->job_type_fix,
                'customer_id'              => $request->customer_id,
                'challan_no'               => $request->challan_no,
                'challan_date'             => $request->challan_date ? Date::createFromFormat('d/m/Y', $request->challan_date)->format('Y-m-d') : null,
                'offer_no'                 => $request->offer_no,
                'offer_date'               => !empty($request->offer_date) ? Date::createFromFormat('d/m/Y', $request->offer_date)->format('Y-m-d') : null,
                'material_type_id'         => $request->material_type_id,
                'total_qty'                => $request->total_qty ?? 0,
                'test_method_id'           => $request->test_method_id ?? $request->test_method_id,
                'procedure_reference_id'   => $request->procedure_ref_id ?? $request->procedure_reference_id,
                'evaluation_as_per_id'     => $request->evaluation_as_per_id,
                'acceptance_standard_id'   => $request->acceptance_standard_id,
                'radiation_energy_fix'     => $request->radiation_energy_fix ?? $request->radiation_energy,
                'coverage_area'            => $request->coverage_area,
                'procedure_submitted_fix'  => $request->procedure_submitted_fix ?? $request->procedure_submitted_fix,
                'del_date'                 => $request->del_date ? Date::createFromFormat('d/m/Y', $request->del_date)->format('Y-m-d') : null,
                'special_note'             => $request->special_note,
                'offer_id'                 => null,
                'assign_format_no'         => $assign_format_no,
                'current_location_id'      => $LocationData->location_id ?? null,
                'year_id'                  => $year_data->id,
                'company_id'               => Auth::user()->company_id,
                'created_by'               => Auth::user()->id,
                'created_on'               => Carbon::now('Asia/Kolkata')->toDateTimeString(),
            ]);

            $details = json_decode($request->material_inward_details_data, true);
            if (!empty($details)) {
                foreach ($details as $row) {
                    if (($row['mode'] ?? '') == 'Delete') continue;

                    if($request->nabl_type_fix == 'NABL' && $row['process_type_fix'] != 'Repair'){
                        $is_observation_sheet = 'Yes';
                    }else if($row['process_type_fix'] == 'Repair'){
                        $is_observation_sheet = 'N/A';
                    }else if($row['type_of_testing_id_fix'] != 'RT' && $request->nabl_type_fix != 'NABL'){
                        $is_observation_sheet = 'N/A';
                    }else{
                        $is_observation_sheet = 'No';
                    }

                    MaterialInwardDetails::create([
                        'material_inward_id'   => $mi->material_inward_id,
                        'process_type_fix'         => $row['process_type_fix'] ?? $row['process_type_fix'] ?? 'Fresh',
                        'type_of_testing_id_fix'  => $row['type_of_testing_id_fix'] ?? 'RT',
                        'sr_no'                => $row['sr_no'] ?? null,
                        'lab_id'               => $row['lab_id'] ?? '',
                        'customer_id_no'       => $row['customer_id_no'] ?? null,
                        'job_desc'             => $row['job_desc'] ?? '',
                        'die_no'               => $row['die_no'] ?? null,
                        'heat_no'              => $row['heat_no'] ?? null,
                        'drg_no'               => $row['drg_no'] ?? null,
                        'material_id'          => $row['material_id'] ?? null,
                        'thickness'         => $row['thickness'] ?? $row['thickness'] ?? null,
                        'quantity'             => $row['quantity'] ?? 1,
                        'remark'               => $row['remark'] ?? null,
                        'offer_details_id'     => null,
                        'is_observation_sheet'   => $is_observation_sheet,
                    ]);
                }
            }

            DB::commit();

            // Print PDF
            $mi_number = !empty($mi->material_inward_no) ? '_' . str_replace('/', '_', $mi->material_inward_no) : "";
            $cust = DB::table('customers')->where('id', $mi->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Material_Inward' . $mi_number . $cust_name;

            if (function_exists('GeneratePdf')) {
                GeneratePdf($mi->material_inward_id, $pdf_name, 'material_inward', 'add');
            }
            $encodedId = base64_encode($mi->material_inward_id);
            $url = hasAccess("material_inward", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=material_inward") : "";

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

        $validated = $request->validate([
            'material_inward_sequence' => [
                'required',
                'max:155',
                Rule::unique('material_inward')
                    ->where(function ($query) use ($year_data, $LocationData) {
                        $query->where('year_id', $year_data->id)
                            ->where('current_location_id', $LocationData->location_id);
                    })
                    ->ignore($request->id, 'material_inward_id')
            ],
        ], [
            'material_inward_sequence.unique' => 'Duplicate Inward No. Found.',
            'material_inward_sequence.required' => 'Enter Inward No.',
        ]);

        $checkInwardDup = checkReportDuplication(MaterialInward::class, 'material_inward_no', $request->material_inward_no, $request, "Duplicate Inward No. Found.", $request->id, 'material_inward_id');
        if ($checkInwardDup) {
            return response()->json($checkInwardDup);
        }

        DB::beginTransaction();
        try {
            $mi = MaterialInward::where('material_inward_id', $request->id)->first();
            if (!$mi) {
                return response()->json(['response_code' => '0', 'response_message' => 'Record Does Not Exists']);
            }

            $isFromOffer = ($mi->form_type_value_fix === 'From Offer' || (!empty($mi->offer_id) && $mi->offer_id > 0));

            if ($request->material_inward_date) {
                $newDate = Date::createFromFormat('d/m/Y', $request->material_inward_date)->format('Y-m-d');
                $detail_ids = DB::table('material_inward_details')
                    ->where('material_inward_id', $request->id)
                    ->pluck('material_inward_details_id');
                
                if ($detail_ids->isNotEmpty()) {
                    $min_report_date = DB::query()
                        ->fromSub(function ($query) use ($detail_ids) {
                            $query->from('test_report_rt')
                                ->select('test_report_date')
                                ->whereIn('material_inward_details_id', $detail_ids)
                                ->whereNotNull('test_report_date')
                                ->where('test_report_date', '!=', '0000-00-00')
                                ->unionAll(
                                    DB::table('test_report_ut')
                                        ->select('test_report_date')
                                        ->whereIn('material_inward_details_id', $detail_ids)
                                        ->whereNotNull('test_report_date')
                                        ->where('test_report_date', '!=', '0000-00-00')
                                )
                                ->unionAll(
                                    DB::table('test_report_dpt')
                                        ->select('test_report_date')
                                        ->whereIn('material_inward_details_id', $detail_ids)
                                        ->whereNotNull('test_report_date')
                                        ->where('test_report_date', '!=', '0000-00-00')
                                )
                                ->unionAll(
                                    DB::table('test_report_mpt')
                                        ->select('test_report_date')
                                        ->whereIn('material_inward_details_id', $detail_ids)
                                        ->whereNotNull('test_report_date')
                                        ->where('test_report_date', '!=', '0000-00-00')
                                );
                        }, 'reports')
                        ->min('test_report_date');

                    if ($min_report_date && $newDate > $min_report_date) {
                        return response()->json([
                            'response_code' => '0',
                            'response_message' => "Material Inward Date cannot be greater than the Report Date."
                        ]);
                    }
                }
            }

            $miUpdateData = [
                'material_inward_no'       => $request->material_inward_no,
                'material_inward_sequence' => $request->material_inward_sequence,
                'material_inward_date'     => $request->material_inward_date ? Date::createFromFormat('d/m/Y', $request->material_inward_date)->format('Y-m-d') : null,
                'challan_no'               => $request->challan_no,
                'challan_date'             => $request->challan_date ? Date::createFromFormat('d/m/Y', $request->challan_date)->format('Y-m-d') : null,
                'material_type_id'         => $request->material_type_id,
                'total_qty'                => $request->total_qty ?? 0,
                'test_method_id'           => $request->test_method_id ?? $request->test_method_id,
                'procedure_reference_id'   => $request->procedure_ref_id ?? $request->procedure_reference_id,
                'evaluation_as_per_id'     => $request->evaluation_as_per_id,
                'acceptance_standard_id'   => $request->acceptance_standard_id,
                'radiation_energy_fix'     => $request->radiation_energy_fix ?? $request->radiation_energy,
                'coverage_area'            => $request->coverage_area,
                'procedure_submitted_fix'      => $request->procedure_submitted_fix ?? $request->procedure_submitted_fix,
                'del_date'                 => $request->del_date ? Date::createFromFormat('d/m/Y', $request->del_date)->format('Y-m-d') : null,
                'special_note'             => $request->special_note,
                'last_by'                  => Auth::user()->id,
                'last_on'                  => Carbon::now('Asia/Kolkata')->toDateTimeString(),
            ];

            if (!$isFromOffer) {
                $miUpdateData['customer_id']   = $request->customer_id;
                $miUpdateData['nabl_type_fix'] = $request->nabl_type_fix;
                $miUpdateData['job_type_fix']  = $request->job_type_fix;
                $miUpdateData['test_at_fix']   = $request->test_at_fix;
                $miUpdateData['offer_no']      = $request->offer_no;
                $miUpdateData['offer_date']    = !empty($request->offer_date) ? Date::createFromFormat('d/m/Y', $request->offer_date)->format('Y-m-d') : null;
            }

            $mi->update($miUpdateData);

            // 3) From Offer: Reverse synchronization to Offer
            if ($isFromOffer && !empty($mi->offer_id)) {
                Offer::where('offer_id', $mi->offer_id)->update([
                    'challan_no'              => $request->challan_no,
                    'challan_date'            => $request->challan_date ? Date::createFromFormat('d/m/Y', $request->challan_date)->format('Y-m-d') : null,
                    'material_type_id'        => $request->material_type_id,
                    'total_qty'               => $request->total_qty ?? 0,
                    'test_method_id'          => $request->test_method_id ?? $request->test_method_id,
                    'procedure_reference_id'  => $request->procedure_ref_id ?? $request->procedure_reference_id,
                    'evaluation_as_per_id'    => $request->evaluation_as_per_id,
                    'acceptance_standard_id'  => $request->acceptance_standard_id,
                    'radiation_energy_fix'    => $request->radiation_energy_fix ?? $request->radiation_energy,
                    'coverage_area'           => $request->coverage_area,
                    'procedure_submitted_fix' => $request->procedure_submitted_fix ?? $request->procedure_submitted_fix,
                    'del_date'                => $request->del_date ? Date::createFromFormat('d/m/Y', $request->del_date)->format('Y-m-d') : null,
                    'special_note'            => $request->special_note ?? $request->special_note,
                    'last_by'                 => Auth::id(),
                    'last_on'                 => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                ]);
            }

            // 4) Details Processing & Reverse effect on offer_details
            $details = json_decode($request->material_inward_details_data, true);

            if (!empty($details)) {
                foreach ($details as $row) {
                    if (empty($row)) continue;

                    $mode = $row['mode'] ?? '';

                    if ($mode == 'Insert') {
                        // For From Offer: adding is disabled/not allowed
                        if ($isFromOffer) {
                            continue;
                        }

                        if($request->nabl_type_fix == 'NABL' && $row['process_type_fix'] != 'Repair'){
                            $is_observation_sheet = 'Yes';
                        }else if($row['process_type_fix'] == 'Repair'){
                            $is_observation_sheet = 'N/A';
                        }else if($row['type_of_testing_id_fix'] != 'RT' && $request->nabl_type_fix != 'NABL'){
                            $is_observation_sheet = 'N/A';
                        }else{
                            $is_observation_sheet = 'No';
                        }

                        MaterialInwardDetails::create([
                            'material_inward_id'   => $mi->material_inward_id,
                            'process_type_fix'         => $row['process_type_fix'] ?? $row['process_type_fix'] ?? 'Fresh',
                            'type_of_testing_id_fix'  => $row['type_of_testing_id_fix'] ?? 'RT',
                            'sr_no'                => $row['sr_no'] ?? null,
                            'lab_id'               => $row['lab_id'] ?? '',
                            'customer_id_no'       => $row['customer_id_no'] ?? null,
                            'job_desc'             => $row['job_desc'] ?? '',
                            'die_no'               => $row['die_no'] ?? null,
                            'heat_no'              => $row['heat_no'] ?? null,
                            'drg_no'               => $row['drg_no'] ?? null,
                            'material_id'          => $row['material_id'] ?? null,
                            'thickness'         => $row['thickness'] ?? $row['thickness'] ?? null,
                            'quantity'             => $row['quantity'] ?? 1,
                            'remark'               => $row['remark'] ?? null,
                            'offer_details_id'     => null,
                            'is_observation_sheet'   => $is_observation_sheet,
                        ]);
                    }
                    elseif ($mode == 'Update') {
                        if (!empty($row['material_inward_details_id'])) {
                            $det = MaterialInwardDetails::where('material_inward_details_id', $row['material_inward_details_id'])->first();

                            $detailId = $row['material_inward_details_id'];
                            $existsInDownstream = DB::table('observation_sheet_details')->where('material_inward_details_id', $detailId)->exists()
                                || DB::table('test_report_rt')->where('material_inward_details_id', $detailId)->exists()
                                || DB::table('test_report_ut')->where('material_inward_details_id', $detailId)->exists()
                                || DB::table('test_report_mpt')->where('material_inward_details_id', $detailId)->exists()
                                || DB::table('test_report_dpt')->where('material_inward_details_id', $detailId)->exists();


                            if ($existsInDownstream) {
                                $is_observation_sheet = DB::table('material_inward_details')
                                    ->where('material_inward_details_id', $detailId)
                                    ->value('is_observation_sheet');
                            } else {
                                if($request->nabl_type_fix == 'NABL' && $row['process_type_fix'] != 'Repair'){
                                    $is_observation_sheet = 'Yes';
                                }else if($row['process_type_fix'] == 'Repair'){
                                    $is_observation_sheet = 'N/A';
                                }else if($row['type_of_testing_id_fix'] != 'RT' && $request->nabl_type_fix != 'NABL'){
                                    $is_observation_sheet = 'N/A';
                                }else{
                                    $is_observation_sheet = 'No';
                                }
                            }


                            if ($det) {
                                $det->update([
                                    'process_type_fix'        => $row['process_type_fix'] ?? $row['process_type_fix'] ?? 'Fresh',
                                    'type_of_testing_id_fix' => $row['type_of_testing_id_fix'] ?? 'RT',
                                    'sr_no'               => $row['sr_no'] ?? null,
                                    'lab_id'              => $row['lab_id'] ?? '',
                                    'customer_id_no'      => $row['customer_id_no'] ?? null,
                                    'job_desc'            => $row['job_desc'] ?? '',
                                    'die_no'              => $row['die_no'] ?? null,
                                    'heat_no'             => $row['heat_no'] ?? null,
                                    'drg_no'              => $row['drg_no'] ?? null,
                                    'material_id'         => $row['material_id'] ?? null,
                                    'thickness'        => $row['thickness'] ?? $row['thickness'] ?? null,
                                    'quantity'            => $row['quantity'] ?? 1,
                                    'remark'              => $row['remark'] ?? null,
                                    'is_observation_sheet'   => $is_observation_sheet,
                                ]);

                                // Reverse effect on offer_details
                                $offerDetId = $row['offer_details_id'] ?? $det->offer_details_id;
                                if ($isFromOffer && !empty($offerDetId)) {
                                    $var = OfferDetails::where('offer_details_id', $offerDetId)->update([
                                        'process_type_fix'    => $row['process_type_fix'] ?? $row['process_type_fix'] ?? 'Fresh',
                                        'type_of_testing_id_fix' => $row['type_of_testing_id_fix'] ?? 'RT',
                                        'sr_no'               => $row['sr_no'] ?? null,
                                        'lab_id'              => $row['lab_id'] ?? '',
                                        'customer_id_no'      => $row['customer_id_no'] ?? null,
                                        'job_desc'            => $row['job_desc'] ?? '',
                                        'die_no'              => $row['die_no'] ?? null,
                                        'heat_no'             => $row['heat_no'] ?? null,
                                        'drg_no'              => $row['drg_no'] ?? null,
                                        'material_id'         => $row['material_id'] ?? null,
                                        'thickness'        => $row['thickness'] ?? $row['thickness'] ?? null,
                                        'quantity'            => $row['quantity'] ?? 1,
                                        'remark'              => $row['remark'] ?? null,
                                    ]);
                                }
                            }
                        }
                    }
                    elseif ($mode == 'Delete') {
                        if (!empty($row['material_inward_details_id'])) {
                            $det = MaterialInwardDetails::where('material_inward_details_id', $row['material_inward_details_id'])->first();
                            $offerDetId = $row['offer_details_id'] ?? ($det ? $det->offer_details_id : null);

                            try {
                                MaterialInwardDetails::where('material_inward_details_id', $row['material_inward_details_id'])->delete();
                            } catch (\Exception $e) {
                                report($e);
                                if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451) {
                                    DB::rollBack();
                                    return response()->json([
                                        'response_code' => '0',
                                        'response_message' => "You Can't Delete, Material Inward Detail is Used Somewhere.",
                                    ]);
                                } else {
                                    throw $e;
                                }
                            }

                            // Reverse effect: delete from offer_details
                            if ($isFromOffer && !empty($offerDetId)) {
                                DB::table('offer_details')->where('offer_details_id', $offerDetId)->delete();
                            }
                        }
                    }
                }
            }

            DB::commit();

            $mi_number = !empty($mi->material_inward_no) ? '_' . str_replace('/', '_', $mi->material_inward_no) : "";
            $cust = DB::table('customers')->where('id', $mi->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Material_Inward' . $mi_number . $cust_name;

            if (function_exists('GeneratePdf')) {
                GeneratePdf($mi->material_inward_id, $pdf_name, 'material_inward', 'update');
            }
            $encodedId = base64_encode($mi->material_inward_id);
            $url = hasAccess("material_inward", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=material_inward") : "";

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

    public function materialInwardLNRData(Request $request)
    {
        $year_data     = getCurrentYearData();
        $LocationData  = getCurrentLocation();
        $customer_id   = $request->customer_id;
        $nabl_type_fix = $request->nabl_type_fix;

        $latestLocationInward = MaterialInward::where('year_id', $year_data->id)
            ->where('current_location_id', $LocationData->location_id)
            ->orderBy('material_inward_id', 'desc')
            ->first();

        $location_lnr_radios = null;
        if ($latestLocationInward) {
            $location_lnr_radios = [
                'nabl_type_fix' => $latestLocationInward->nabl_type_fix,
                'test_at_fix'   => $latestLocationInward->test_at_fix,
                'job_type_fix'  => $latestLocationInward->job_type_fix,
            ];
        }

        if (empty($customer_id)) {
            return response()->json([
                'response_code'       => 1,
                'lnr_data'            => null,
                'lnr_detail_testing_type' => null,
                'location_lnr_radios' => $location_lnr_radios,
            ]);
        }

        $query = MaterialInward::where('year_id', $year_data->id)
            ->where('current_location_id', $LocationData->location_id)
            ->where('customer_id', $customer_id);

        if (!empty($nabl_type_fix)) {
            $query->where('nabl_type_fix', $nabl_type_fix);
        }

        $latestInward = $query->orderBy('material_inward_id', 'desc')->first();

        if (!$latestInward) {
            $latestInward = MaterialInward::where('year_id', $year_data->id)
                ->where('current_location_id', $LocationData->location_id)
                ->where('customer_id', $customer_id)
                ->orderBy('material_inward_id', 'desc')
                ->first();
        }

        $lnr_detail_testing_type = null;
        if ($latestInward) {
            $lastDetail = MaterialInwardDetails::where('material_inward_id', $latestInward->material_inward_id)
                ->whereNotNull('type_of_testing_id_fix')
                ->orderBy('material_inward_details_id', 'desc')
                ->first();
            if ($lastDetail) {
                $lnr_detail_testing_type = $lastDetail->type_of_testing_id_fix;
            }
        }

        return response()->json([
            'response_code'       => 1,
            'lnr_data'            => $latestInward,
            'lnr_detail_testing_type' => $lnr_detail_testing_type,
            'location_lnr_radios' => $location_lnr_radios,
        ]);
    }

    public function qtyValidation($new_qty, $material_inward_details_id, $transaction_mode = 'U')
    {
        if ($material_inward_details_id > 0) {
            $used_qty_dc = DB::table('customer_dc_non_returnable_details')
                ->where('material_inward_details_id', $material_inward_details_id)
                ->sum('dc_qty') ?? 0;

            if ((float)$used_qty_dc > 0 && ((float)$new_qty < (float)$used_qty_dc || $transaction_mode === 'D')) {
                DB::rollBack();

                $message = ($transaction_mode === 'D')
                    ? "You Can't Delete, Material Inward Detail is Used in Material Outward."
                    : "You Can't Update, Inward Qty. cannot be less than DC Qty. ({$used_qty_dc}).";

                return response()->json([
                    'response_code'    => '0',
                    'response_message' => $message,
                ]);
            }
            if($new_qty > 0)
            {
                $inward_detail = DB::table('material_inward_details')
                    ->where('material_inward_details_id', $material_inward_details_id)
                    ->first();
                $testing_type = $inward_detail->type_of_testing_id_fix ?? '';
                $used_qty = 0;
                if ($testing_type === 'RT') {
                    $pending_qty = DB::table('pending_material_inward_rt_qty')
                        ->where('material_inward_details_id', $material_inward_details_id)
                        ->whereNull('observation_sheet_details_id')
                        ->value('pending_qty');
                    $used_qty = (float)($pending_qty ?? 0);
                } elseif ($testing_type === 'UT') {
                    $pending_qty = DB::table('pending_material_inward_ut_qty')
                        ->where('material_inward_details_id', $material_inward_details_id)
                        ->value('pending_qty');
                    $used_qty = (float)($pending_qty ?? 0);
                } elseif ($testing_type === 'DPT') {
                    $pending_qty = DB::table('pending_material_inward_dpt_qty')
                        ->where('material_inward_details_id', $material_inward_details_id)
                        ->value('pending_qty');
                    $used_qty = (float)($pending_qty ?? 0);
                } elseif ($testing_type === 'MPT') {
                    $pending_qty = DB::table('pending_material_inward_mpt_qty')
                        ->where('material_inward_details_id', $material_inward_details_id)
                        ->value('pending_qty');
                    $used_qty = (float)($pending_qty ?? 0);
                }
                
                $obsPendingQty = DB::table('pending_observation_sheet_qty')
                    ->where('material_inward_details_id', $material_inward_details_id)
                    ->where('from_type_id_fix', 1)
                    ->value('pending_qty');
                $total_qty = (float)($used_qty) + (float)($obsPendingQty ?? 0);

                if($transaction_mode == 'D'){
                    if($total_qty < $new_qty){
                        DB::rollBack();
                        return response()->json([
                            'response_code' => '0',
                            'response_message' => "You Can't Delete, Material Inward Is Used.",
                        ]);
                    }
                }else{
                    if($total_qty < $new_qty){
                        DB::rollBack();
                        return response()->json([
                            'response_code' => '0',
                            'response_message' => "You Can't Update, Material Inward Is Used.",
                        ]);
                    }
                }
            }
        }
        return null;
    }

    public function checkNegativePendingQty($material_inward_details_id, $testing_type, $transaction_mode = 'U')
    {
        if (empty($material_inward_details_id)) {
            return null;
        }

        $pendingQty = null;
        if ($testing_type === 'RT') {
            $pendingQty = DB::table('pending_material_inward_rt_qty')
                ->where('material_inward_details_id', $material_inward_details_id)
                ->value('pending_qty');
        } elseif ($testing_type === 'UT') {
            $pendingQty = DB::table('pending_material_inward_ut_qty')
                ->where('material_inward_details_id', $material_inward_details_id)
                ->value('pending_qty');
        } elseif ($testing_type === 'DPT') {
            $pendingQty = DB::table('pending_material_inward_dpt_qty')
                ->where('material_inward_details_id', $material_inward_details_id)
                ->value('pending_qty');
        } elseif ($testing_type === 'MPT') {
            $pendingQty = DB::table('pending_material_inward_mpt_qty')
                ->where('material_inward_details_id', $material_inward_details_id)
                ->value('pending_qty');
        }

        $msg = ($transaction_mode === 'I')
            ? "You Can't Insert, Something Went Wrong."
            : "You Can't Update, Something Went Wrong.";

        if ($pendingQty !== null && (float)$pendingQty < 0) {
            DB::rollBack();
            return response()->json([
                'response_code'    => '0',
                'response_message' => $msg,
            ]);
        }

        $obsPendingQty = DB::table('pending_observation_sheet_qty')
            ->where('material_inward_details_id', $material_inward_details_id)
            ->value('pending_qty');

        if ($obsPendingQty !== null && (float)$obsPendingQty < 0) {
            DB::rollBack();
            return response()->json([
                'response_code'    => '0',
                'response_message' => $msg,
            ]);
        }

        return null;
    }

    public function getInwardPartNoList(Request $request)
    {
        $term = $request->term ?? '';
        $job_desc = $request->job_desc ?? '';
        $current_location_id = getCurrentLocation()->location_id;

        if (!empty($term)) {
            $query = MaterialInwardDetails::select('material_inward_details.die_no as part_no')
                ->join('material_inward', 'material_inward.material_inward_id', '=', 'material_inward_details.material_inward_id')
                ->where('material_inward.current_location_id', $current_location_id)
                ->whereNotNull('material_inward_details.die_no')
                ->where('material_inward_details.die_no', '!=', '')
                ->where('material_inward_details.die_no', 'LIKE', '%' . $term . '%');

            if (!empty($job_desc)) {
                $query->where('material_inward_details.job_desc', $job_desc);
            }

            $partNos = $query->groupBy('material_inward_details.die_no')->orderBy('material_inward_details.die_no', 'asc')->get();

            if ($partNos->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($partNos as $row) {
                    $output .= '<li parent-id="die_no" list-id="inward_die_no_list" class="list-group-item" tabindex="0">' . e($row->part_no) . '</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'partNoList'    => $output,
                    'response_code' => 1,
                ]);
            }
        }

        return response()->json([
            'partNoList'    => '',
            'response_code' => 1,
        ]);
    }

    public function getInwardDrgNoList(Request $request)
    {
        $term = $request->term ?? '';
        $current_location_id = getCurrentLocation()->location_id;

        if (!empty($term)) {
            $drgNos = MaterialInwardDetails::select('material_inward_details.drg_no')
                ->join('material_inward', 'material_inward.material_inward_id', '=', 'material_inward_details.material_inward_id')
                ->where('material_inward.current_location_id', $current_location_id)
                ->whereNotNull('material_inward_details.drg_no')
                ->where('material_inward_details.drg_no', '!=', '')
                ->where('material_inward_details.drg_no', 'LIKE', '%' . $term . '%')
                ->groupBy('material_inward_details.drg_no')
                ->orderBy('material_inward_details.drg_no', 'asc')
                ->get();

            if ($drgNos->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($drgNos as $row) {
                    $output .= '<li parent-id="drg_no" list-id="inward_drg_no_list" class="list-group-item" tabindex="0">' . e($row->drg_no) . '</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'drgNoList'     => $output,
                    'response_code' => 1,
                ]);
            }
        }

        return response()->json([
            'drgNoList'     => '',
            'response_code' => 1,
        ]);
    }

    public function getInwardProductCodeList(Request $request)
    {
        return response()->json([
            'productCodeList' => '',
            'response_code'   => 1,
        ]);
    }

    public function getOldInwardDetails(Request $request)
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

            $inwardQuery = MaterialInward::where('customer_id', $customer)
                ->where('job_type_fix', $jobTypeCasting)
                ->where('current_location_id', $current_location_id);

            if (!empty($nablType)) {
                $inwardQuery->where('nabl_type_fix', $nablType);
            }

            $inwardIds = $inwardQuery->pluck('material_inward_id');

            if ($inwardIds->count() > 0) {
                $query = MaterialInwardDetails::whereIn('material_inward_details.material_inward_id', $inwardIds)
                    ->join('material_inward', 'material_inward.material_inward_id', '=', 'material_inward_details.material_inward_id')
                    ->select('material_inward_details.*', 'material_inward.material_inward_no as inward_no', 'material_inward.material_inward_date as inward_date')
                    ->where('material_inward_details.type_of_testing_id_fix', $typeOfTest)
                    ->orderBy('material_inward_details.material_inward_details_id', 'DESC');

                $recentDetails = $query->get();

                return response()->json(['status' => 'success', 'data' => $recentDetails]);
            }
            return response()->json(['status' => 'success', 'data' => []]);
        }
    }
    
    public function getRtReportsListForInward(Request $request)
    {
        $customerId = $request->customer_id;
        $nablType = $request->nabl_type;
        $location = getCurrentLocation()->location_id;
        $year = getCompanyYearIdsToTill();

        $reports = DB::table('pending_repair_rt_report_for_inward')
            ->where('customer_id', $customerId)
            ->where('nabl_type_fix', $nablType)
            ->where('current_location_id', $location)
            ->whereIn('year_id', $year)
            ->orderBy('test_report_rt_id', 'DESC')
            ->get();
            
        foreach ($reports as $report) {
            $report->test_report_date = (!empty($report->test_report_date) && $report->test_report_date != '0000-00-00')
                ? Date::createFromFormat('Y-m-d', $report->test_report_date)->format(DATE_FORMAT)
                : '';
        }
        
        return response()->json([
            'response_code' => 1,
            'reports' => $reports
        ]);
    }
}
