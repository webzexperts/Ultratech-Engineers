<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\JobCard;
use App\Models\Transaction\JobCardDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Exception;

class JobCardController extends Controller
{
    /**
     * Display Manage View
     */
    public function manage()
    {
        return view('manage.transaction.manage-job_card');
    }

    /**
     * Yajra DataTable Listing
     */
    public function index(Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();

        $jobCards = JobCard::select([
            'job_card.job_card_id as id',
            'job_card.job_card_id',
            'job_card.job_card_sequence',
            'job_card.job_card_no',
            'job_card.job_card_date',
            'job_card.shift_id',
            'job_card.incharge_authority_person_id',
            'job_card.radiographer_authority_person_id',
            'job_card.rt_camera_id',
            'shift.shift_name',
            'incharge_person.operator as incharge_name',
            'radiographer_person.operator as radiographer_name',
            DB::raw("IFNULL(NULLIF(rt_camera.name_for_display, ''), rt_camera.rt_camera_name) as rt_camera_name"),
            'job_card.created_by',
            'job_card.created_on',
            'job_card.last_by',
            'job_card.last_on',
        ])
        ->leftJoin('shift', 'shift.shift_id', '=', 'job_card.shift_id')
        ->leftJoin('authority_person as incharge_person', 'incharge_person.authority_person_id', '=', 'job_card.incharge_authority_person_id')
        ->leftJoin('authority_person as radiographer_person', 'radiographer_person.authority_person_id', '=', 'job_card.radiographer_authority_person_id')
        ->leftJoin('rt_camera', 'rt_camera.rt_camera_id', '=', 'job_card.rt_camera_id')
        ->where('job_card.year_id', $year_data->id)
        ->where('job_card.current_location_id', $location_data->location_id);

        $dataTable = DataTables::of($jobCards)
            ->filterColumn('job_card.job_card_no', function ($query, $keyword) {
                applynumberprefix($query, $keyword, 'job_card.job_card_no');
            })
            ->editColumn('job_card_date', function ($row) {
                return $row->job_card_date ? Date::createFromFormat('Y-m-d', $row->job_card_date)->format(DATE_FORMAT) : '';
            })
            ->filterColumn('job_card.job_card_date', function ($q, $k) {
                applyDate($q, $k, 'job_card.job_card_date');
            })
            ->filterColumn('rt_camera_name', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('rt_camera.name_for_display', 'like', "%{$keyword}%")
                        ->orWhere('rt_camera.rt_camera_name', 'like', "%{$keyword}%")
                        ->orWhere('rt_camera.rt_serial_no', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('actions', function ($row) {
                $actions = '<div class="dropdown d-inline-block">
                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-more-fill align-middle"></i>
                    </button>
                    <ul class="dropdown-menu">';

                if (hasAccess("job_card", "edit")) {
                    $actions .= '<li><a class="dropdown-item edit-job_card" href="javascript:void(0)" data-id="' . $row->id . '"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("job_card", "delete")) {
                    $actions .= '<li><a class="dropdown-item delete-job_card" href="javascript:void(0)" data-id="' . $row->id . '"><i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete</a></li>';
                }

                $actions .= '</ul></div>';
                return $actions;
            });

        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'job_card');

        $dataTable->filterColumn('job_card.created_by', function ($query, $keyword) {
            $query->whereIn('job_card.created_by', function ($sub) use ($keyword) {
                $sub->select('id')
                    ->from('admin')
                    ->where('user_name', 'like', "%{$keyword}%");
            });
        })
        ->filterColumn('job_card.last_by', function ($query, $keyword) {
            $query->whereIn('job_card.last_by', function ($sub) use ($keyword) {
                $sub->select('id')
                    ->from('admin')
                    ->where('user_name', 'like', "%{$keyword}%");
            });
        });

        return $dataTable
            ->rawColumns(['actions', 'last_by', 'last_on', 'created_by', 'created_on'])
            ->make(true);
    }

    

    /**
     * Store New Job Card
     */
    public function store(Request $request)
    {
        $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();

        $request->validate([
            'job_card_date'     => 'required',
            'job_card_sequence' => 'required',
            'job_card_no'       => 'required',
            'shift_id'          => 'required',
            'incharge_authority_person_id'       => 'required',
            'radiographer_authority_person_id'   => 'required',
            'rt_camera_id'      => 'required',
        ]);

        $details = is_string($request->details) ? json_decode($request->details, true) : ($request->details ?? []);
        
        $activeDetails = array_filter($details, function($row) {
            return ($row['mode'] ?? '') !== 'Delete';
        });

        if (empty($activeDetails)) {
            return response()->json([
                'response_code'    => '0',
                'response_message' => 'Please select at least one worksheet.',
            ]);
        }

        foreach ($activeDetails as $row) {
            if (empty($row['priority_id_fix'])) {
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => 'Please Select Priority.',
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $existNumber = JobCard::where([
                ['job_card_sequence', $request->job_card_sequence],
                ['job_card_no', $request->job_card_no],
                ['year_id', $year_data->id],
                ['current_location_id', $location_data->location_id]
            ])->lockForUpdate()->first();

            if ($existNumber) {
                $latestNo = $this->getLatestJobCardNumber($request);
                $area = json_decode($latestNo->getContent(), true);
                $jc_no = $area['latest_no'];
                $jc_seq = $area['number'];
            } else {
                $jc_no = $request->job_card_no;
                $jc_seq = $request->job_card_sequence;
            }

            $checkDup = checkReportDuplication(JobCard::class, 'job_card_no', $jc_no, $request, "Duplicate Job Card No. Found.");
            if ($checkDup) {
                DB::rollBack();
                return response()->json($checkDup);
            }

            $page_id = getMenuIdBassedOnDisplayName('job_card');
            $assign_format_no = getAssignFormateNoForTransaction($location_data->location_id, $page_id->id ?? 0, $request->job_card_date);

            $jcDate = $request->job_card_date ? Date::createFromFormat('d/m/Y', $request->job_card_date)->format('Y-m-d') : null;

            $jobCard = JobCard::create([
                'current_location_id' => $location_data->location_id,
                'job_card_sequence'   => $jc_seq,
                'job_card_no'         => $jc_no,
                'job_card_date'       => $jcDate,
                'shift_id'            => $request->shift_id ?: null,
                'incharge_authority_person_id'         => $request->incharge_authority_person_id ?: null,
                'radiographer_authority_person_id'     => $request->radiographer_authority_person_id ?: null,
                'rt_camera_id'        => $request->rt_camera_id ?: null,
                'company_id'          => Auth::user()->company_id,
                'assign_format_no'    => $assign_format_no,
                'year_id'             => $year_data->id,
                'created_by'          => Auth::user()->id,
                'created_on'          => Carbon::now('Asia/Kolkata'),
            ]);

            // Sort details priority-wise: 1st -> 2nd -> 3rd -> 4th
            $priorityMap = ['1st' => 1, '2nd' => 2, '3rd' => 3, '4th' => 4];
            usort($details, function ($a, $b) use ($priorityMap) {
                $pA = $priorityMap[$a['priority_id_fix'] ?? ''] ?? 999;
                $pB = $priorityMap[$b['priority_id_fix'] ?? ''] ?? 999;
                return $pA <=> $pB;
            });

            foreach ($details as $row) {
                if (($row['mode'] ?? '') === 'Delete') {
                    continue;
                }
                $obsDetailId = $row['observation_sheet_details_id'] ?? null;

                $checkQty = $this->qtyValidation($obsDetailId);
                if ($checkQty) {
                    return $checkQty;
                }

                JobCardDetails::create([
                    'job_card_id'                  => $jobCard->job_card_id,
                    'observation_sheet_details_id' => $obsDetailId,
                    'priority_id_fix'              => $row['priority_id_fix'],
                ]);
            }

            DB::commit();

            return response()->json([
                'response_code'    => '1',
                'response_message' => getResponseMessage('store_success'),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'response_code'    => '0',
                'response_message' => getResponseMessage('store_error'),
                'original_error'   => $e->getMessage()
            ]);
        }
    }

    /**
     * Edit Job Card
     */
    public function edit(Request $request)
    {
        $id = $request->input('id');
        $master = DB::select('CALL job_card_master(?)', [$id]);

        if (empty($master)) {
            return response()->json([
                'response_code'    => '0',
                'response_message' => 'Record Not Found',
            ]);
        }

        $jobCard = $master[0];
        $jobCard->job_card_id = $jobCard->job_card_id ?? ($jobCard->id ?? $id);

        $jobCard->job_card_date = (!empty($jobCard->job_card_date) && $jobCard->job_card_date != '0000-00-00')
            ? Date::createFromFormat('Y-m-d', $jobCard->job_card_date)->format('d/m/Y')
            : '';

        if (isset($jobCard->company_logo)) {
            $jobCard->company_logo = base64_encode($jobCard->company_logo);
        }

        $details = DB::select('CALL job_card_details(?)', [$id]);
        foreach ($details as $row) {
            $row->mode = 'Update';
            $row->worksheet_date = (!empty($row->worksheet_date) && $row->worksheet_date != '0000-00-00')
                ? Date::createFromFormat('Y-m-d', $row->worksheet_date)->format('d/m/Y')
                : '';
        }

        return response()->json([
            'response_code' => 1,
            'job_card'      => $jobCard,
            'job_card_details'       => $details
        ]);
    }

    /**
     * Update Existing Job Card
     */
    public function update(Request $request)
    {
        $id = $request->input('id');
        $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();

        $request->validate([
            'id'                => 'required',
            'job_card_date'     => 'required',
            'job_card_sequence' => 'required',
            'job_card_no'       => 'required',
            'shift_id'          => 'required',
            'incharge_authority_person_id'       => 'required',
            'radiographer_authority_person_id'   => 'required',
            'rt_camera_id'      => 'required',
        ]);

        $details = is_string($request->details) ? json_decode($request->details, true) : ($request->details ?? []);

        $activeDetails = array_filter($details, function($row) {
            return ($row['mode'] ?? '') !== 'Delete';
        });

        if (empty($activeDetails)) {
            return response()->json([
                'response_code'    => '0',
                'response_message' => 'Please select at least one worksheet.',
            ]);
        }

        foreach ($activeDetails as $row) {
            if (empty($row['priority_id_fix'])) {
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => 'Please Select Priority.',
                ]);
            }
        }

        $checkDup = checkReportDuplication(JobCard::class, 'job_card_no', $request->job_card_no, $request, "Duplicate Job Card No. Found.", $id, 'job_card_id');
        if ($checkDup) {
            return response()->json($checkDup);
        }

        DB::beginTransaction();
        try {
            $existingJc = JobCard::where('job_card_id', $id)->lockForUpdate()->first();
            if (!$existingJc) {
                DB::rollBack();
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => 'Job Card record not found or already deleted.',
                ]);
            }

            $jcDate = $request->job_card_date ? Date::createFromFormat('d/m/Y', $request->job_card_date)->format('Y-m-d') : null;

            JobCard::where('job_card_id', $id)->update([
                'job_card_sequence' => $request->job_card_sequence,
                'job_card_no'       => $request->job_card_no,
                'job_card_date'     => $jcDate,
                'shift_id'          => $request->shift_id ?: null,
                'incharge_authority_person_id'       => $request->incharge_authority_person_id ?: null,
                'radiographer_authority_person_id'   => $request->radiographer_authority_person_id ?: null,
                'rt_camera_id'      => $request->rt_camera_id ?: null,
                'last_by'           => Auth::user()->id,
                'last_on'           => Carbon::now('Asia/Kolkata'),
            ]);

            // Sort details priority-wise: 1st -> 2nd -> 3rd -> 4th
            $priorityMap = ['1st' => 1, '2nd' => 2, '3rd' => 3, '4th' => 4];
            usort($details, function ($a, $b) use ($priorityMap) {
                $pA = $priorityMap[$a['priority_id_fix'] ?? ''] ?? 999;
                $pB = $priorityMap[$b['priority_id_fix'] ?? ''] ?? 999;
                return $pA <=> $pB;
            });

            foreach ($details as $row) {
                $mode = $row['mode'] ?? 'Update';
                $detailId = $row['job_card_detail_id'] ?? null;
                $obsDetailId = $row['observation_sheet_details_id'] ?? null;

                if ($mode === 'Insert' || (empty($detailId) && $mode !== 'Delete')) {
                    $checkQty = $this->qtyValidation($obsDetailId);
                    if ($checkQty) {
                        return $checkQty;
                    }

                    JobCardDetails::create([
                        'job_card_id'                  => $id,
                        'observation_sheet_details_id' => $obsDetailId,
                        'priority_id_fix'              => $row['priority_id_fix'],
                    ]);
                } elseif ($mode === 'Update') {
                    if (!empty($detailId)) {
                        JobCardDetails::where('job_card_detail_id', $detailId)->update([
                            'observation_sheet_details_id' => $obsDetailId,
                            'priority_id_fix'              => $row['priority_id_fix'],
                        ]);
                    }
                } elseif ($mode === 'Delete') {
                    if (!empty($detailId)) {
                        JobCardDetails::where('job_card_detail_id', $detailId)->delete();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'response_code'    => '1',
                'response_message' => getResponseMessage('update_success'),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'response_code'    => '0',
                'response_message' => getResponseMessage('update_error'),
                'original_error'   => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete Job Card
     */
    public function destroy(Request $request)
    {
        $id = $request->input('id');

        DB::beginTransaction();
        try {
            JobCardDetails::where('job_card_id', $id)->delete();
            JobCard::where('job_card_id', $id)->delete();

            DB::commit();

            return response()->json([
                'response_code'    => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'response_code'    => '0',
                'response_message' => getResponseMessage('delete_error'),
                'original_error'   => $e->getMessage()
            ]);
        }
    }

    /**
     * Get Latest Job Card Sequence Number
     */
    public function getLatestJobCardNumber(Request $request)
    {
        $modal = JobCard::class;
        $sequence = 'job_card_sequence';
        $prefix = 'JC';
        $sup_num_format = getLatestSequence($modal, $sequence, $prefix);

        return response()->json([
            'response_code' => 1,
            'latest_no'     => $sup_num_format['format'],
            'number'        => $sup_num_format['isFound'],
        ]);
    }

    /**
     * Check Sequence Number Duplication
     */
    public function checkJobCardSequenceDuplication(Request $request)
    {
        $modal = JobCard::class;
        $seq = 'job_card_sequence';
        $sequence = $request->input('sequence') ?? $request->input('job_card_sequence');
        $id = $request->input('id');
        $table_id = 'job_card_id';
        $prefix = 'JC';

        if (empty($sequence) || (int)$sequence <= 0) {
            return response()->json([
                'response_code'    => 0,
                'response_message' => 'Please Enter Valid Sr. No.'
            ]);
        }

        if (!empty($id)) {
            $year_data = getCurrentYearData();
            $locationData = getCurrentLocation()->location_id;
            $check = $modal::where('year_id', '=', $year_data->id)
                ->where('current_location_id', $locationData)
                ->where($seq, $sequence)
                ->where($table_id, '!=', $id)
                ->first();

            if ($check != null && $check != "") {
                return response()->json([
                    'response_code'    => 0,
                    'response_message' => 'Duplicate Job Card No. Found.'
                ]);
            }

            $middle_num = str_pad($sequence, 4, "0", STR_PAD_LEFT);
            $postfix = $year_data->yearcode;
            $format = $middle_num . '/' . $postfix;

            return response()->json([
                'response_code' => 1,
                'latest_no'     => $format,
                'number'        => $sequence
            ]);
        }

        $result = duplicationSequence($modal, $seq, $sequence, $id, $table_id, $prefix);

        if ($result['format'] == 0) {
            return response()->json([
                'response_code'    => 0,
                'response_message' => 'Duplicate Job Card No. Found.'
            ]);
        }

        return response()->json([
            'response_code' => 1,
            'latest_no'     => $result['format'],
            'number'        => $sequence
        ]);
    }

    /**
     * Get Pending Worksheets for Job Card Grid
     */

    public function getPendingWorksheets(Request $request)
    {
        $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();
        $job_card_id = $request->input('job_card_id');

        $query = DB::table('pending_worksheet_for_job_card as pw')
            ->join('observation_sheet_details as osd', 'osd.observation_sheet_details_id', '=', 'pw.observation_sheet_details_id')
            ->join('observation_sheet as os', 'os.observation_sheet_id', '=', 'osd.observation_sheet_id')
            ->leftJoin('customers', 'customers.id', '=', 'os.customer_id')
            ->leftJoin('material_inward_details as mid', 'mid.material_inward_details_id', '=', 'osd.material_inward_details_id')
            ->leftJoin('materials', 'materials.id', '=', 'mid.material_id')
            ->where('os.year_id', $year_data->id)
            ->where('os.current_location_id', $location_data->location_id)
            ->select([
                'osd.observation_sheet_details_id',
                'os.observation_sheet_no as worksheet_no',
                'os.observation_sheet_date as worksheet_date',
                'customers.customer as customer_name',
                'mid.job_desc',
                'mid.die_no',
                'mid.drg_no',
                'mid.heat_no',
                'materials.material',
                'osd.observation_qty as quantity',
                DB::raw("'' as priority_id_fix"),
                DB::raw("0 as is_selected"),
                DB::raw("0 as job_card_detail_id")
            ]);

        $pendingWorksheets = $query->get()->toArray();

        // If edit mode, include currently selected worksheets
        if (!empty($job_card_id)) {
            $existing = DB::table('job_card_details as jcd')
                ->join('observation_sheet_details as osd', 'osd.observation_sheet_details_id', '=', 'jcd.observation_sheet_details_id')
                ->join('observation_sheet as os', 'os.observation_sheet_id', '=', 'osd.observation_sheet_id')
                ->leftJoin('customers', 'customers.id', '=', 'os.customer_id')
                ->leftJoin('material_inward_details as mid', 'mid.material_inward_details_id', '=', 'osd.material_inward_details_id')
                ->leftJoin('materials', 'materials.id', '=', 'mid.material_id')
                ->where('jcd.job_card_id', $job_card_id)
                ->select([
                    'jcd.job_card_detail_id',
                    'osd.observation_sheet_details_id',
                    'os.observation_sheet_no as worksheet_no',
                    'os.observation_sheet_date as worksheet_date',
                    'customers.customer as customer_name',
                    'mid.job_desc',
                    'mid.die_no',
                    'mid.drg_no',
                    'mid.heat_no',
                    'materials.material',
                    'osd.observation_qty as quantity',
                    'jcd.priority_id_fix',
                    DB::raw("1 as is_selected")
                ])
                ->orderByRaw("CASE jcd.priority_id_fix WHEN '1st' THEN 1 WHEN '2nd' THEN 2 WHEN '3rd' THEN 3 WHEN '4th' THEN 4 ELSE 5 END ASC, jcd.job_card_detail_id ASC")
                ->get()
                ->toArray();

            $pendingWorksheets = array_merge($existing, $pendingWorksheets);
        }

        foreach ($pendingWorksheets as $row) {
            $row->worksheet_date = (!empty($row->worksheet_date) && $row->worksheet_date != '0000-00-00')
                ? Date::createFromFormat('Y-m-d', $row->worksheet_date)->format('d/m/Y')
                : '';
        }

        return response()->json([
            'response_code' => 1,
            'worksheets' => $pendingWorksheets
        ]);
    }

    /**
     * Worksheet Validation using Pending View (SupplierDCController pattern)
     */
    public function qtyValidation($obsDetailId)
    {
        if (!empty($obsDetailId)) {
            $isPending = DB::table('pending_worksheet_for_job_card')
                ->where('observation_sheet_details_id', $obsDetailId)
                ->exists();

            if (!$isPending) {
                DB::rollBack();
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => 'Worksheet is Used',
                ]);
            }
        }

        return null;
    }
}
