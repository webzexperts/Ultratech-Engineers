<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\ProductionEntry;
use App\Models\Transaction\ProductionEntryDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class ProductionEntryController extends Controller
{
    private $sourceTypes = ['Ir-192', 'Co-60', 'X-Ray'];

    public function manage()
    {
        return view('manage.transaction.manage-production_entry');
    }

    public function index(ProductionEntry $pe_data, Request $request, DataTables $datatables)
    {
        $current_location_id = getCurrentLocation()->location_id;
        $year_data = getCurrentYearData();

        $pe_details_sub = DB::table('production_entry_details')
            ->select('production_entry_id')
            ->selectRaw('GROUP_CONCAT(DISTINCT source_id_fix ORDER BY source_id_fix SEPARATOR ", ") as source_id_fix')
            ->selectRaw('SUM(production_sq_in) as production_sq_in')
            ->selectRaw('SUM(westage_in) as repair_reshoot')
            ->selectRaw('SUM(retake_sq_in) as retake_sq_in')
            ->groupBy('production_entry_id');

        $pe_data = ProductionEntry::select([
            'production_entry.production_entry_sequence',
            'production_entry.production_entry_id',
            'production_entry.production_entry_no',
            'production_entry.production_entry_date',
            'enclosure.enclosure_name',
            'sub.source_id_fix',
            'sub.production_sq_in',
            'sub.repair_reshoot',
            'sub.retake_sq_in',
            'production_entry.total_sq_in',
            'production_entry.remark',
            'production_entry.created_on',
            'production_entry.created_by',
            'production_entry.last_by',
            'production_entry.last_on',
        ])
        ->leftJoin('enclosure', 'enclosure.enclosure_id', '=', 'production_entry.enclosure_id')
        ->leftJoinSub($pe_details_sub, 'sub', 'sub.production_entry_id', '=', 'production_entry.production_entry_id')
        ->where('production_entry.year_id', $year_data->id)
        ->where('production_entry.current_location_id', $current_location_id);

        $dataTable = DataTables::of($pe_data)
        ->editColumn('production_entry_date', function ($pe_data) {
            return $pe_data->production_entry_date != null
                ? Date::createFromFormat('Y-m-d', $pe_data->production_entry_date)->format(DATE_FORMAT)
                : '';
        })
        ->filterColumn('production_entry.production_entry_date', function ($q, $k) {
            applyDate($q, $k, 'production_entry.production_entry_date');
        })
        ->editColumn('production_sq_in', function ($pe_data) {
            return $pe_data->production_sq_in > 0
                ? number_format((float)$pe_data->production_sq_in, 2, '.', '')
                : number_format(0, 2, '.', '');
        })
        ->editColumn('repair_reshoot', function ($pe_data) {
            return $pe_data->repair_reshoot > 0
                ? number_format((float)$pe_data->repair_reshoot, 2, '.', '')
                : number_format(0, 2, '.', '');
        })
        ->editColumn('retake_sq_in', function ($pe_data) {
            return $pe_data->retake_sq_in > 0
                ? number_format((float)$pe_data->retake_sq_in, 2, '.', '')
                : number_format(0, 2, '.', '');
        })
        ->editColumn('total_sq_in', function ($pe_data) {
            return $pe_data->total_sq_in > 0
                ? number_format((float)$pe_data->total_sq_in, 2, '.', '')
                : number_format(0, 2, '.', '');
        })
        ->filterColumn('production_entry.production_entry_no', function ($query, $keyword) {
            applySequenceSearch($query, $keyword, 'production_entry.production_entry_no');
        })
        ->addColumn('options', function ($pe_data) {
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("production_entry", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-production_entry"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("production_entry", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });

        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'production_entry');
        return $dataTable
        ->rawColumns(['options', 'last_by', 'last_on', 'created_by', 'created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $year_data    = getCurrentYearData();
        $LocationData = getCurrentLocation();

        $this->validatePayload($request);

        DB::beginTransaction();
        try {
            // Resolve / lock the auto number
            $existNumber = ProductionEntry::where([
                ['production_entry_sequence', $request->production_entry_sequence],
                ['production_entry_no', $request->production_entry_no],
                ['year_id', $year_data->id],
                ['current_location_id', $LocationData->location_id],
            ])->first();

            if ($existNumber) {
                $latestNo = $this->getLatestProductionEntryNumber($request);
                $area = json_decode($latestNo->getContent(), true);
                $production_entry_no       = $area['latest_no'];
                $production_entry_sequence = $area['number'];
            } else {
                $production_entry_no       = $request->production_entry_no;
                $production_entry_sequence = $request->production_entry_sequence;
            }

            $details = json_decode($request->production_entry_details_data, true);
            $total_sq_in_sum = 0;
            if (!empty($details)) {
                foreach ($details as $row) {
                    if (($row['mode'] ?? '') == 'Delete') continue;
                    $total_sq_in_sum += (float)($row['total_sq_in'] ?? 0);
                }
            }

            $pe = ProductionEntry::create([
                'production_entry_no'       => $production_entry_no,
                'production_entry_sequence' => $production_entry_sequence,
                'production_entry_date'     => $request->production_entry_date ? Date::createFromFormat('d/m/Y', $request->production_entry_date)->format('Y-m-d') : null,
                'enclosure_id'             => $request->enclosure_id,
                'total_sq_in'              => $total_sq_in_sum,
                'remark'                   => $request->remark,
                'current_location_id'      => $LocationData->location_id ?? null,
                'year_id'                  => $year_data->id,
                'company_id'               => Auth::user()->company_id,
                'created_by'               => Auth::user()->id,
                'created_on'               => Carbon::now('Asia/Kolkata'),
            ]);

            if (!empty($details)) {
                foreach ($details as $row) {
                    if (($row['mode'] ?? '') == 'Delete') continue;
                    $ped = ProductionEntryDetails::create([
                        'production_entry_id' => $pe->production_entry_id,
                        'shift_id'            => $row['shift_id'] ?? null,
                        'source_id_fix'       => $row['source_id_fix'] ?? null,
                        'item_id'             => $row['item_id'] ?? null,
                        'production_sq_in'    => !empty($row['production_sq_in']) ? $row['production_sq_in'] : 0,
                        'retake_sq_in'        => !empty($row['retake_sq_in']) ? $row['retake_sq_in'] : 0,
                        'westage_in'          => !empty($row['westage_in']) ? $row['westage_in'] : 0,
                        'total_sq_in'         => !empty($row['total_sq_in']) ? $row['total_sq_in'] : 0,
                    ]);

                    stockEffectSQIN($LocationData->location_id, $row['item_id'], $row['item_id'], $row['total_sq_in'], 0, 'Insert', 'D', 'Production Entry', $ped->production_entry_details_id);
                }
            }

            DB::commit();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('store_success'),
            ]);
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();
            $message = $e->getMessage();
            if (str_contains($message, 'Insufficient Stock') || str_contains($message, 'Invalid Item') || str_contains($message, 'Invalid Location')) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }
            return response()->json([
                'response_code' => '0',
                'response_message' => getResponseMessage('store_error'),
                'original_error' => $e->getMessage(),
            ]);
        }
    }

    public function edit(Request $request)
    {
        $pe_data = DB::select('CALL production_entry_master(?)', [$request->id]);
        $pe_data = !empty($pe_data) ? $pe_data[0] : null;
         if(isset($pe_data->cmp_logo))
            {
                $pe_data->cmp_logo = base64_encode($pe_data->cmp_logo);
            }
        if ($pe_data) {
            $can_update = true;
            $allow_back_days = (int)(Auth::user()->allow_production_back_days_entry ?? 0);
            if ($allow_back_days > 0) {
                $entry_date = Carbon::parse($pe_data->production_entry_date, 'Asia/Kolkata')->startOfDay();
                $today = Carbon::now('Asia/Kolkata')->startOfDay();
                if ($entry_date->diffInDays($today) > $allow_back_days) {
                    $can_update = false;
                }
            }
            $pe_data->production_entry_date = $pe_data->production_entry_date != "" ? Date::createFromFormat('Y-m-d', $pe_data->production_entry_date)->format('d/m/Y') : "";

            $pe_details_data = DB::select('CALL production_entry_details(?)', [$request->id]);

            if ($pe_details_data) {
                foreach ($pe_details_data as $row) {
                    $row->mode = 'Update';
                }
            }

            

            return response()->json([
                'pe_data'          => $pe_data,
                'pe_details_data'  => $pe_details_data,
                'can_update'       => $can_update,
                'response_code'    => '1',
                'response_message' => '',
            ]);
        }

        return response()->json([
            'response_code'    => '0',
            'response_message' => 'Record Does Not Exists',
        ]);
    }

    public function update(Request $request)
    {
        $this->validatePayload($request, true);
        $year_data = getCurrentYearData();
        $LocationData = getCurrentLocation();

        DB::beginTransaction();
        $validated = $request->validate([
                'production_entry_no' => [
                    'required',
                    'max:155',
                    Rule::unique('production_entry')
                        ->where(function ($query) use ($year_data, $LocationData) {
                            $query->where('year_id', $year_data->id)
                                  ->where('current_location_id', $LocationData->location_id);
                        })
                        ->ignore($request->id, 'production_entry_id')
                ],
            ], [
                'production_entry_sequence.unique' => 'Duplicate Sr. No. Found.',
                'production_entry_no.required'      => 'Enter Sr. No.',
            ]);
        try {
            $pe = ProductionEntry::where('production_entry_id', $request->id)->first();
            if (!$pe) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Record not found.',
                ]);
            }

            $allow_back_days = (int)(Auth::user()->allow_production_back_days_entry ?? 0);
            if ($allow_back_days > 0) {
                $entry_date = \Carbon\Carbon::parse($pe->production_entry_date, 'Asia/Kolkata')->startOfDay();
                $today = \Carbon\Carbon::now('Asia/Kolkata')->startOfDay();
                if ($entry_date->diffInDays($today) > $allow_back_days) {
                    return response()->json([
                        'response_code' => '0',
                        'response_message' => 'Back days entry editing is not allowed for this record.',
                    ]);
                }
            }


            $details = json_decode($request->production_entry_details_data, true);
            $total_sq_in_sum = 0;
            if (!empty($details)) {
                foreach ($details as $row) {
                    if (($row['mode'] ?? '') == 'Delete') continue;
                    $total_sq_in_sum += (float)($row['total_sq_in'] ?? 0);
                }
            }

            $pe->update([
                'production_entry_no'   => $request->production_entry_no,
                'production_entry_sequence' => $request->production_entry_sequence,
                'production_entry_date' => $request->production_entry_date ? Date::createFromFormat('d/m/Y', $request->production_entry_date)->format('Y-m-d') : null,
                'enclosure_id'         => $request->enclosure_id,
                'total_sq_in'          => $total_sq_in_sum,
                'remark'               => $request->remark,
                'last_by'              => Auth::user()->id,
                'last_on'              => Carbon::now('Asia/Kolkata'),
            ]);

            if (!empty($details)) {
                foreach ($details as $row) {
                    $mode = $row['mode'] ?? '';
                    $row_id = $row['production_entry_details_id'] ?? 0;

                    if ($mode == 'Delete') {
                        if ($row_id > 0) {
                            $oldItem = ProductionEntryDetails::where('production_entry_details_id', $row_id)->first();
                            if ($oldItem) {
                                stockEffectSQIN($LocationData->location_id, $oldItem->item_id, $oldItem->item_id, 0, $oldItem->total_sq_in, 'Delete', 'D', 'Production Entry', $row_id);
                            }
                            ProductionEntryDetails::where('production_entry_details_id', $row_id)->delete();
                        }
                    } elseif ($mode == 'Insert' || $row_id == 0) {
                        $ped = ProductionEntryDetails::create([
                            'production_entry_id' => $pe->production_entry_id,
                            'shift_id'            => $row['shift_id'] ?? null,
                            'source_id_fix'       => $row['source_id_fix'] ?? null,
                            'item_id'             => $row['item_id'] ?? null,
                            'production_sq_in'    => !empty($row['production_sq_in']) ? $row['production_sq_in'] : 0,
                            'retake_sq_in'        => !empty($row['retake_sq_in']) ? $row['retake_sq_in'] : 0,
                            'westage_in'          => !empty($row['westage_in']) ? $row['westage_in'] : 0,
                            'total_sq_in'         => !empty($row['total_sq_in']) ? $row['total_sq_in'] : 0,
                        ]);

                        stockEffectSQIN($LocationData->location_id, $row['item_id'], $row['item_id'], $row['total_sq_in'], 0, 'Insert', 'D', 'Production Entry', $ped->production_entry_details_id);
                    } elseif ($mode == 'Update' && $row_id > 0) {
                        $oldItem = ProductionEntryDetails::where('production_entry_details_id', $row_id)->first();
                        ProductionEntryDetails::where('production_entry_details_id', $row_id)->update([
                            'shift_id'            => $row['shift_id'] ?? null,
                            'source_id_fix'       => $row['source_id_fix'] ?? null,
                            'item_id'             => $row['item_id'] ?? null,
                            'production_sq_in'    => !empty($row['production_sq_in']) ? $row['production_sq_in'] : 0,
                            'retake_sq_in'        => !empty($row['retake_sq_in']) ? $row['retake_sq_in'] : 0,
                            'westage_in'          => !empty($row['westage_in']) ? $row['westage_in'] : 0,
                            'total_sq_in'         => !empty($row['total_sq_in']) ? $row['total_sq_in'] : 0,
                        ]);

                        if ($oldItem) {
                            stockEffectSQIN($LocationData->location_id, $row['item_id'], $oldItem->item_id, $row['total_sq_in'], $oldItem->total_sq_in, 'Update', 'D', 'Production Entry', $row_id);
                        }
                    }
                }
            }

            DB::commit();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('update_success'),
            ]);
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();
            $message = $e->getMessage();
            if (str_contains($message, 'Insufficient Stock') || str_contains($message, 'Invalid Item') || str_contains($message, 'Invalid Location')) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }
            return response()->json([
                'response_code' => '0',
                'response_message' => getResponseMessage('update_error'),
                'original_error' => $e->getMessage(),
            ]);
        }
    }

    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            $pe = ProductionEntry::where('production_entry_id', $request->id)->first();
            if ($pe) {
                $details = ProductionEntryDetails::where('production_entry_id', $request->id)->get();
                foreach ($details as $row) {
                    stockEffectSQIN($pe->current_location_id, $row->item_id, $row->item_id, 0, $row->total_sq_in, 'Delete', 'D', 'Production Entry', $row->production_entry_details_id);
                }
                ProductionEntryDetails::where('production_entry_id', $request->id)->delete();
                ProductionEntry::where('production_entry_id', $request->id)->delete();
            }
            DB::commit();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();
            $message = $e->getMessage();
            if (str_contains($message, 'Insufficient Stock') || str_contains($message, 'Invalid Item') || str_contains($message, 'Invalid Location')) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }
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

    /**
     * Auto-number generator (same Sr. No. concept as Material Inward).
     * Display format /<Loc_Code>/PROD/XXXX/XX-XX is produced by getLatestSequence().
     */
    public function getLatestProductionEntryNumber(Request $request)
    {
        $modal    = ProductionEntry::class;
        $sequence = 'production_entry_sequence';
        $prefix   = 'PROD';

        $num_format = getLatestSequence($modal, $sequence, $prefix);

        return response()->json([
            'response_code' => 1,
            'latest_no'     => $num_format['format'],
            'number'        => $num_format['isFound'],
        ]);
    }

    private function validatePayload(Request $request, $isUpdate = false)
    {
        $request->validate([
            'production_entry_date' => 'required',
            'enclosure_id'         => 'required',
        ], [
            'production_entry_date.required' => 'Enter Date',
            'enclosure_id.required'          => 'Select Enclosure Name',
        ]);
    }
}
