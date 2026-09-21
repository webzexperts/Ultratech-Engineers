<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Transaction\TechniqueSheetRt;
use App\Models\Transaction\TechniqueSheetRtDetails;
use App\Models\File;
use App\Models\Transaction\TestReportRt;
use App\Models\Transaction\TestReportRtDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class TechniqueSheetRtController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-technique_sheet_rt');
    }

    public function index(Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();

        $ts_data = TechniqueSheetRt::select([
            'technique_sheet_rt.technique_sheet_rt_id as id',
            'technique_sheet_rt.technique_sheet_rt_no',
            'technique_sheet_rt.technique_sheet_rt_sequence',
            'technique_sheet_rt.technique_sheet_rt_date',
            'technique_sheet_rt.entry_type_fix',
            'customers.customer',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            'technique_sheet_rt.part_no',
            'technique_sheet_rt.drg_no',
            'area_of_coverage.area_of_coverage',
            'technique_sheet_rt.created_on',
            'technique_sheet_rt.created_by',
            'technique_sheet_rt.last_by',
            'technique_sheet_rt.last_on'
        ])
        ->leftJoin('customers', 'customers.id', '=', 'technique_sheet_rt.customer_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'technique_sheet_rt.type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'technique_sheet_rt.job_desc_id')
        ->leftJoin('area_of_coverage', 'area_of_coverage.area_of_coverage_id', '=', 'technique_sheet_rt.area_of_coverage_id')
        ->where('technique_sheet_rt.year_id', $year_data->id)
        ->where('technique_sheet_rt.current_location_id', $location_data->location_id);

        $dataTable = DataTables::of($ts_data)
        ->filterColumn('technique_sheet_rt.technique_sheet_rt_no', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'technique_sheet_rt.technique_sheet_rt_no');
        })
        ->editColumn('technique_sheet_rt_date', function($ts) {
            return $ts->technique_sheet_rt_date != null
                ? Date::createFromFormat('Y-m-d', $ts->technique_sheet_rt_date)->format(DATE_FORMAT)
                : '';
        })
        ->filterColumn('technique_sheet_rt.technique_sheet_rt_date', function ($q, $k) {
            applyDate($q, $k, 'technique_sheet_rt.technique_sheet_rt_date');
        })
        ->filterColumn('part_no', function($query, $keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('technique_sheet_rt.part_no', 'like', "%{$keyword}%");
            });
        })
        ->filterColumn('drg_no', function($query, $keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('technique_sheet_rt.drg_no', 'like', "%{$keyword}%");
            });
        })
        ->addColumn('options', function($ts) {
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("technique_sheet_rt", "print")) {
                    $ts_number = !empty($ts->technique_sheet_rt_no) ? '_' . str_replace('/', '_', $ts->technique_sheet_rt_no) : "";
                    $cust_name = !empty($ts->customer) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $ts->customer) : "";
                    $pdfName   = 'Technique_Sheet_Rt' . $ts_number . $cust_name;
                    $encodedId = base64_encode($ts->id);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=technique_sheet_rt");
                    $action .= '<li><a class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }

                if (hasAccess("technique_sheet_rt", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-technique_sheet_rt"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("technique_sheet_rt", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });

        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'technique_sheet_rt');
        return $dataTable
        ->rawColumns(['options', 'last_by', 'last_on', 'created_by', 'created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $year_data = getCurrentYearData();
        $current_location_id = getCurrentLocation()->location_id;

        $request->validate([
            'technique_sheet_rt_sequence' => 'required',
            'technique_sheet_rt_date' => 'required',
            'customer_id' => 'required',
            'type_of_job_id' => 'required',
            'job_desc_id' => 'required',
            // 'part_id' => 'required',
            'area_of_coverage_id' => 'required',
        ]);

        // $existCombination = TechniqueSheetRt::where([
        //     ['customer_id', $request->customer_id],
        //     ['type_of_job_id', $request->type_of_job_id],
        //     ['job_desc_id', $request->job_desc_id],
        //     ['part_id', $request->part_id],
        //     ['area_of_coverage_id', $request->area_of_coverage_id],
        //     ['year_id', $year_data->id],
        // ])->first();

        // if ($existCombination) {
        //     return response()->json([
        //         'response_code' => '0',
        //         'response_message' => 'Technique Sheet already exists for this Customer, Type of Job, Job Description, Part, and Area of Coverage combination.',
        //     ]);
        // }

        DB::beginTransaction();
        try {
            $existNumber = TechniqueSheetRt::where([
                ['technique_sheet_rt_sequence', $request->technique_sheet_rt_sequence],
                ['technique_sheet_rt_no', $request->technique_sheet_rt_no],
                ['year_id', $year_data->id],
                ['current_location_id', $current_location_id]
            ])->first();

            if ($existNumber) {
                $latestNo = $this->getLatestTechniqueSheetNumber($request);
                $area = json_decode($latestNo->getContent(), true);
                $ts_no = $area['latest_no'];
                $ts_seq = $area['number'];
            } else {
                $ts_no = $request->technique_sheet_rt_no;
                $ts_seq = $request->technique_sheet_rt_sequence;
            }

            // Image Sketch Processing
            $shootingSketchPath = null;
            $blobImage = null;
            if ($request->shooting_sketch_image_doc) {
                $file = new File();
                if (str_contains($request->shooting_sketch_image_doc, 'temp_media/')) {
                    $isFound = $file->getFileFromTemp($request->shooting_sketch_image_doc, 'technique_sheet_rt');
                } else {
                    $isFound = $file->getFileCopyFormUpload($request->shooting_sketch_image_doc, 'technique_sheet_rt');
                }
                if ($isFound !== false) {
                    $shootingSketchPath = $isFound;
                    $filePath = storage_path('app/public/' . $shootingSketchPath);
                    if (file_exists($filePath)) {
                        $blobImage = file_get_contents($filePath);
                    }
                } else {
                    $shootingSketchPath = $request->shooting_sketch_image_doc;
                    $filePath = storage_path('app/public/' . $shootingSketchPath);
                    if (file_exists($filePath)) {
                        $blobImage = file_get_contents($filePath);
                    }
                }
            }

            $page_id = getMenuIdBassedOnDisplayName('technique_sheet_rt');
            $assign_format_no = getAssignFormateNoForTransaction($current_location_id, $page_id->id,$request->technique_sheet_rt_date);

            $ts_header = TechniqueSheetRt::create([
                'current_location_id' => $current_location_id,
                'technique_sheet_rt_sequence' => $ts_seq,
                'technique_sheet_rt_no' => $ts_no,
                'technique_sheet_rt_date' => isset($request->technique_sheet_rt_date) ? Date::createFromFormat('d/m/Y', $request->technique_sheet_rt_date)->format('Y-m-d') : null,
                'entry_type_fix' => $request->entry_type_fix ?? 'Manual',
                'test_report_rt_id' => !empty($request->test_report_rt_id) ? $request->test_report_rt_id : null,
                'customer_id' => $request->customer_id,
                'type_of_job_id' => $request->type_of_job_id,
                'job_desc_id' => $request->job_desc_id,
                'part_id' => null,
                'part_no' => $request->part_no,
                'drg_no' => $request->drg_no,
                'area_of_coverage_id' => $request->area_of_coverage_id,
                'source_used' => $request->source_used,
                'source_size' => $request->source_size,
                'xray_focal_size' => $request->xray_focal_size,
                'lead_screen_thick' => $request->lead_screen_thick,
                'lead_screen_thick_back' => $request->lead_screen_thick_back,
                'iqi' => $request->iqi,
                'film_processing' => $request->film_processing,
                'test_technique' => $request->test_technique,
                'test_arrangement' => $request->test_arrangement,
                'test_class' => $request->test_class,
                'film_brand' => $request->film_brand,
                'film_type' => $request->film_type,
                'procedure_ref_id' => $request->procedure_ref_id,
                'evaluation_as_per_id' => $request->evaluation_as_per_id,
                'acceptance_standard_id' => $request->acceptance_standard_id,
                'customer_procedure_ref' => $request->customer_procedure_ref,
                'shooting_sketch_image' => $shootingSketchPath,
                'shooting_sketch_image_blob' => $blobImage,
                'film_size_fix' => $request->film_size_fix ?? 'inch',
                'sfd_unit_fix' => $request->sfd_unit_fix ?? 'mm',
                'no_of_films' => $request->no_of_films,
                'film_size' => $request->film_size,
                'total_area' => $request->total_area,
                'prepared_by_user_id' => $request->prepared_by_user_id ?? Auth::id(),
                'checked_by_authority_person_id' => $request->checked_by_authority_person_id,
                'authorized_by_authority_person_id' => $request->authorized_by_authority_person_id,
                'sp_note' => $request->sp_note,
                'assign_format_no' => $assign_format_no,
                'company_id' => Auth::user()->company_id,
                'year_id' => $year_data->id,
                'created_by' => Auth::id(),
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
            ]);

            $details = json_decode($request->ts_details_data, true);
            if (!empty($details)) {
                foreach ($details as $row) {
                    if (empty($row) || $row['mode'] === 'Delete') continue;

                    TechniqueSheetRtDetails::create([
                        'technique_sheet_rt_id' => $ts_header->technique_sheet_rt_id,
                        'sr_no' => $row['sr_no'],
                        'identification' => $row['identification'],
                        'location' => $row['location'],
                        'source_id_fix' => $row['source_id_fix'],
                        'film_brand_id' => $row['film_brand_id'],
                        'film_type_id' => $row['film_type_id'],
                        'thickness' => $row['thickness'],
                        'sfd' => $row['sfd'],
                        'iqi_designation' => !empty($row['iqi_designation']) ? $row['iqi_designation'] : null,
                        'iqi_sensitivity' => !empty($row['iqi_sensitivity']) ? $row['iqi_sensitivity'] : null,
                        'iqi_designation_id' => null,
                        'iqi_sensitivity_id' => null,
                        'film_id' => $row['film_id'],
                        'no_of_film_fix' => $row['no_of_film_fix'],
                        'film_qty' => $row['film_qty'],
                        'sq_in' => $row['sq_in'],
                        'sq_cm' => $row['sq_cm'],
                        'total_sq_in' => $row['total_sq_in'],
                        'total_sq_cm' => $row['total_sq_cm'],
                        'test_technique' => $row['test_technique'] ?? null,
                        'film_position' => $row['film_position'] ?? null,
                    ]);
                }
            }

            DB::commit();

            // Print PDF
            $ts_number = !empty($ts_header->technique_sheet_rt_no) ? '_' . str_replace('/', '_', $ts_header->technique_sheet_rt_no) : "";
            $cust = DB::table('customers')->where('id', $ts_header->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Technique_Sheet_Rt' . $ts_number . $cust_name;

            GeneratePdf($ts_header->technique_sheet_rt_id, $pdf_name, 'technique_sheet_rt', 'add');
            $encodedId = base64_encode($ts_header->technique_sheet_rt_id);
            $url = hasAccess("technique_sheet_rt", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=technique_sheet_rt") : "";


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
                'original_error' => $e->getMessage()
            ]);
        }
    }

    public function edit(Request $request)
    {
        $ts_data = DB::select('CALL technique_sheet_rt_master(?)', [$request->id]);
        if (!empty($ts_data)) {
            $ts_data = $ts_data[0];
            $ts_data->technique_sheet_rt_date = $ts_data->technique_sheet_rt_date != "" 
                ? Date::createFromFormat('Y-m-d', $ts_data->technique_sheet_rt_date)->format('d/m/Y') 
                : "";

            if (isset($ts_data->shooting_sketch_image_blob)) {
                $ts_data->shooting_sketch_image_blob = base64_encode($ts_data->shooting_sketch_image_blob);
            }
            if(isset($ts_data->cmp_logo))
            {
                $ts_data->cmp_logo = base64_encode($ts_data->cmp_logo);
            }
            // Prevent Malformed UTF-8 characters exception by base64 encoding binary blobs
            foreach ($ts_data as $key => $value) {
                if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                    $ts_data->$key = base64_encode($value);
                }
            }

            $ts_number = !empty($ts_data->technique_sheet_rt_no) ? '_' . str_replace('/', '_', $ts_data->technique_sheet_rt_no) : "";
            $cust = DB::table('customers')->where('id', $ts_data->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $ts_data->pdf_name = 'Technique_Sheet_Rt' . $ts_number . $cust_name;

            $details = DB::select('CALL technique_sheet_rt_details(?)', [$request->id]);

            foreach ($details as $row) {
                $row->mode = 'Update';
            }

            return response()->json([
                'ts_data' => $ts_data,
                'ts_details_data' => $details,
                'response_code' => '1',
            ]);
        }

        return response()->json([
            'response_code' => '0',
            'response_message' => 'Record Does Not Exist',
        ]);
    }

    public function update(Request $request)
    {
        $year_data = getCurrentYearData();
        $current_location_id = getCurrentLocation()->location_id;

        $request->validate([
            'technique_sheet_rt_sequence' => [
                'required',
                'max:155',
                Rule::unique('technique_sheet_rt')
                    ->where(function ($query) use ($year_data, $current_location_id) {
                        $query->where('year_id', $year_data->id)
                            ->where('current_location_id', $current_location_id);
                    })
                    ->ignore($request->id, 'technique_sheet_rt_id')
            ],
            'technique_sheet_rt_date' => 'required',
            'customer_id' => 'required',
            'type_of_job_id' => 'required',
            'job_desc_id' => 'required',
            // 'part_id' => 'required',
            'area_of_coverage_id' => 'required',
        ], [
            'technique_sheet_rt_sequence.unique' => 'Duplicate Sr. No. Found.',
            'technique_sheet_rt_sequence.required' => 'Enter Sr. No.',
            'technique_sheet_rt_date.required' => 'Enter Date.',
            'customer_id.required' => 'Select Customer.',
            'type_of_job_id.required' => 'Select Type of Job.',
            'job_desc_id.required' => 'Select Job Description.',
            'area_of_coverage_id.required' => 'Select Area of Coverage.',
        ]);

        // $existCombination = TechniqueSheetRt::where([
        //     ['customer_id', $request->customer_id],
        //     ['type_of_job_id', $request->type_of_job_id],
        //     ['job_desc_id', $request->job_desc_id],
        //     ['part_id', $request->part_id],
        //     ['area_of_coverage_id', $request->area_of_coverage_id],
        //     ['year_id', $year_data->id],
        // ])
        // ->where('technique_sheet_rt_id', '!=', $request->id)
        // ->first();

        // if ($existCombination) {
        //     return response()->json([
        //         'response_code' => '0',
        //         'response_message' => 'Technique Sheet already exists for this Customer, Type of Job, Job Description, Part, and Area of Coverage combination.',
        //     ]);
        // }

        DB::beginTransaction();
        try {
            $ts = TechniqueSheetRt::where('technique_sheet_rt_id', $request->id)->first();
            if (!$ts) {
                return response()->json(['response_code' => '0', 'response_message' => 'Record Does Not Exist']);
            }

            // Image Sketch Processing
            $existing_image = $ts->shooting_sketch_image;
            $existing_blob = $ts->shooting_sketch_image_blob;

            $file = new File();
            $shootingSketchPath = $existing_image;
            $blobImage = $existing_blob;

            if ($request->filled('shooting_sketch_image_doc') && $request->shooting_sketch_image_doc != '') {
                if ($existing_image && $existing_image != $request->shooting_sketch_image_doc) {
                    $file->delete_file($existing_image);
                }

                $isFound = $file->getFileFromTemp($request->shooting_sketch_image_doc, 'technique_sheet_rt');
                if ($isFound !== false) {
                    $shootingSketchPath = $isFound;
                    $filePath = storage_path('app/public/' . $shootingSketchPath);
                    if (file_exists($filePath)) {
                        $blobImage = file_get_contents($filePath);
                    }
                } else {
                    $shootingSketchPath = $request->shooting_sketch_image_doc;
                    $filePath = storage_path('app/public/' . $shootingSketchPath);
                    if (file_exists($filePath)) {
                        $blobImage = file_get_contents($filePath);
                    }
                }
            } elseif ($request->shooting_sketch_image_doc == '' || $request->shooting_sketch_image_doc == null) {
                // User removed the sketch
                if ($existing_image) {
                    $file->delete_file($existing_image);
                }
                $shootingSketchPath = null;
                $blobImage = null;
            }

            $page_id = getMenuIdBassedOnDisplayName('technique_sheet_rt');
            $assign_format_no = getAssignFormateNoForTransaction($current_location_id, $page_id->id,$request->technique_sheet_rt_date);

            $ts->update([
                'technique_sheet_rt_sequence' => $request->technique_sheet_rt_sequence,
                'technique_sheet_rt_no' => $request->technique_sheet_rt_no,
                'technique_sheet_rt_date' => isset($request->technique_sheet_rt_date) ? Date::createFromFormat('d/m/Y', $request->technique_sheet_rt_date)->format('Y-m-d') : null,
                'entry_type_fix' => $request->entry_type_fix ?? 'Manual',
                'test_report_rt_id' => !empty($request->test_report_rt_id) ? $request->test_report_rt_id : null,
                'customer_id' => $request->customer_id,
                'type_of_job_id' => $request->type_of_job_id,
                'job_desc_id' => $request->job_desc_id,
                'part_id' => null,
                'part_no' => $request->part_no,
                'drg_no' => $request->drg_no,
                'area_of_coverage_id' => $request->area_of_coverage_id,
                'source_used' => $request->source_used,
                'source_size' => $request->source_size,
                'xray_focal_size' => $request->xray_focal_size,
                'lead_screen_thick' => $request->lead_screen_thick,
                'lead_screen_thick_back' => $request->lead_screen_thick_back,
                'iqi' => $request->iqi,
                'film_processing' => $request->film_processing,
                'test_technique' => $request->test_technique,
                'test_arrangement' => $request->test_arrangement,
                'test_class' => $request->test_class,
                'film_brand' => $request->film_brand,
                'film_type' => $request->film_type,
                'procedure_ref_id' => $request->procedure_ref_id,
                'evaluation_as_per_id' => $request->evaluation_as_per_id,
                'acceptance_standard_id' => $request->acceptance_standard_id,
                'customer_procedure_ref' => $request->customer_procedure_ref,
                'shooting_sketch_image' => $shootingSketchPath,
                'shooting_sketch_image_blob' => $blobImage,
                'film_size_fix' => $request->film_size_fix ?? 'inch',
                'sfd_unit_fix' => $request->sfd_unit_fix ?? 'mm',
                'no_of_films' => $request->no_of_films,
                'film_size' => $request->film_size,
                'total_area' => $request->total_area,
                'prepared_by_user_id' => $request->prepared_by_user_id ?? Auth::id(),
                'checked_by_authority_person_id' => $request->checked_by_authority_person_id,
                'authorized_by_authority_person_id' => $request->authorized_by_authority_person_id,
                'sp_note' => $request->sp_note,
                // 'assign_format_no'      => $assign_format_no,   not update assign formate discussion ramde sir
                'last_by' => Auth::id(),
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
            ]);

            $details = json_decode($request->ts_details_data, true);
            if (!empty($details)) {
                foreach ($details as $row) {
                    if (empty($row)) continue;

                    $mode = $row['mode'];

                    if ($mode === 'Insert') {
                        TechniqueSheetRtDetails::create([
                            'technique_sheet_rt_id' => $ts->technique_sheet_rt_id,
                            'sr_no' => $row['sr_no'],
                            'identification' => $row['identification'],
                            'location' => $row['location'],
                            'source_id_fix' => $row['source_id_fix'],
                            'film_brand_id' => $row['film_brand_id'],
                            'film_type_id' => $row['film_type_id'],
                            'thickness' => $row['thickness'],
                            'sfd' => $row['sfd'],
                            'iqi_designation' => !empty($row['iqi_designation']) ? $row['iqi_designation'] : null,
                            'iqi_sensitivity' => !empty($row['iqi_sensitivity']) ? $row['iqi_sensitivity'] : null,
                            'iqi_designation_id' => null,
                            'iqi_sensitivity_id' => null,
                            'film_id' => $row['film_id'],
                            'no_of_film_fix' => $row['no_of_film_fix'],
                            'film_qty' => $row['film_qty'],
                            'sq_in' => $row['sq_in'],
                            'sq_cm' => $row['sq_cm'],
                            'total_sq_in' => $row['total_sq_in'],
                            'total_sq_cm' => $row['total_sq_cm'],
                            'test_technique' => $row['test_technique'] ?? null,
                            'film_position' => $row['film_position'] ?? null,
                        ]);
                    } elseif ($mode === 'Update') {
                        if (!empty($row['technique_sheet_rt_details_id'])) {
                            TechniqueSheetRtDetails::where('technique_sheet_rt_details_id', $row['technique_sheet_rt_details_id'])->update([
                                'sr_no' => $row['sr_no'],
                                'identification' => $row['identification'],
                                'location' => $row['location'],
                                'source_id_fix' => $row['source_id_fix'],
                                'film_brand_id' => $row['film_brand_id'],
                                'film_type_id' => $row['film_type_id'],
                                'thickness' => $row['thickness'],
                                'sfd' => $row['sfd'],
                                'iqi_designation' => !empty($row['iqi_designation']) ? $row['iqi_designation'] : null,
                                'iqi_sensitivity' => !empty($row['iqi_sensitivity']) ? $row['iqi_sensitivity'] : null,
                                'iqi_designation_id' => null,
                                'iqi_sensitivity_id' => null,
                                'film_id' => $row['film_id'],
                                'no_of_film_fix' => $row['no_of_film_fix'],
                                'film_qty' => $row['film_qty'],
                                'sq_in' => $row['sq_in'],
                                'sq_cm' => $row['sq_cm'],
                                'total_sq_in' => $row['total_sq_in'],
                                'total_sq_cm' => $row['total_sq_cm'],
                                'test_technique' => $row['test_technique'] ?? null,
                                'film_position' => $row['film_position'] ?? null,
                            ]);
                        }
                    } elseif ($mode === 'Delete') {
                        if (!empty($row['technique_sheet_rt_details_id'])) {
                            TechniqueSheetRtDetails::where('technique_sheet_rt_details_id', $row['technique_sheet_rt_details_id'])->delete();
                        }
                    }
                }
            }

            DB::commit();

            // Print PDF
            $ts_number = !empty($ts->technique_sheet_rt_no) ? '_' . str_replace('/', '_', $ts->technique_sheet_rt_no) : "";
            $cust = DB::table('customers')->where('id', $ts->customer_id)->value('customer');
            $cust_name = !empty($cust) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $cust) : "";
            $pdf_name = 'Technique_Sheet_Rt' . $ts_number . $cust_name;

            GeneratePdf($ts->technique_sheet_rt_id, $pdf_name, 'technique_sheet_rt', 'update');
            $encodedId = base64_encode($ts->technique_sheet_rt_id);
            $url = hasAccess("technique_sheet_rt", "print") ? url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=technique_sheet_rt") : "";
            

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
                'original_error' => $e->getMessage()
            ]);
        }
    }

    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            $ts = TechniqueSheetRt::where('technique_sheet_rt_id', $request->id)->first();
            if ($ts) {
                if ($ts->shooting_sketch_image) {
                    $file = new File();
                    $file->delete_file($ts->shooting_sketch_image);
                }
                TechniqueSheetRtDetails::where('technique_sheet_rt_id', $request->id)->delete();
                $ts->delete();
            }

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

    public function getLatestTechniqueSheetNumber(Request $request)
    {
        $modal = TechniqueSheetRt::class;
        $sequence = 'technique_sheet_rt_sequence';
        $prefix = 'RSS';

        $num_format = getLatestSequence($modal, $sequence, $prefix);

        return response()->json([
            'response_code' => 1,
            'latest_no' => $num_format['format'],
            'number' => $num_format['isFound'],
        ]);
    }

    public function existsIqi(Request $request)
    {
        if ($request->term != "") {
            $location_id = getCurrentLocation()->location_id;
            $fdIqi = TechniqueSheetRt::select('iqi')
                ->where('iqi', 'LIKE', $request->term . '%')
                ->where('current_location_id', $location_id)
                ->groupBy('iqi')
                ->get();
            if ($fdIqi->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($fdIqi as $dsKey) {
                    if (trim($dsKey->iqi) === '') continue;
                    $output .= '<li parent-id="iqi" list-id="iqi_list" class="list-group-item" tabindex="0">' . e($dsKey->iqi) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'iqiList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'iqiList' => '',
            'response_code' => 1,
        ]);
    }

    public function existsFilmProcessing(Request $request)
    {
        if ($request->term != "") {
            $location_id = getCurrentLocation()->location_id;
            $fdFilmProc = TechniqueSheetRt::select('film_processing')
                ->where('film_processing', 'LIKE', $request->term . '%')
                ->where('current_location_id', $location_id)
                ->groupBy('film_processing')
                ->get();
            if ($fdFilmProc->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($fdFilmProc as $dsKey) {
                    if (trim($dsKey->film_processing) === '') continue;
                    $output .= '<li parent-id="film_processing" list-id="film_processing_list" class="list-group-item" tabindex="0">' . e($dsKey->film_processing) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'filmProcessingList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'filmProcessingList' => '',
            'response_code' => 1,
        ]);
    }

    public function existsTestTechnique(Request $request)
    {
        if ($request->term != "") {
            $fdTestTech = TechniqueSheetRtDetails::select('test_technique')
                ->where('test_technique', 'LIKE', $request->term . '%')
                ->groupBy('test_technique')
                ->get();

            if ($fdTestTech->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($fdTestTech as $dsKey) {
                    if (trim($dsKey->test_technique) === '') continue;
                    $output .= '<li parent-id="detail_test_technique" list-id="detail_test_technique_list" class="list-group-item" tabindex="0">' . e($dsKey->test_technique) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'testTechniqueList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'testTechniqueList' => '',
            'response_code' => 1,
        ]);
    }

    public function existsPart(Request $request)
    {
        if ($request->term != "") {
            $location_id = getCurrentLocation()->location_id;
            $data = DB::table('technique_sheet_rt')
                ->select('part_no')
                ->where('part_no', 'LIKE', $request->term . '%')
                ->where('current_location_id', $location_id)
                ->groupBy('part_no')
                ->get();
            if ($data->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($data as $row) {
                    if (trim($row->part_no) === '') continue;
                    $output .= '<li parent-id="part_no" list-id="part_no_list" class="list-group-item" tabindex="0">' . e($row->part_no) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'partNoList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'partNoList' => '',
            'response_code' => 1,
        ]);
    }

    public function existsDrgNo(Request $request)
    {
        if ($request->term != "") {
            $location_id = getCurrentLocation()->location_id;
            $data = DB::table('technique_sheet_rt')
                ->select('drg_no')
                ->where('drg_no', 'LIKE', $request->term . '%')
                ->where('current_location_id', $location_id)
                ->groupBy('drg_no')
                ->get();
            if ($data->isNotEmpty()) {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach ($data as $row) {
                    if (trim($row->drg_no) === '') continue;
                    $output .= '<li parent-id="drg_no" list-id="drg_no_list" class="list-group-item" tabindex="0">' . e($row->drg_no) . '</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'drgNoList' => $output,
                    'response_code' => 1,
                ]);
            }
        }
        return response()->json([
            'drgNoList' => '',
            'response_code' => 1,
        ]);
    }
    public function getCopyList(Request $request)
    {
        $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();
        
        $query = TechniqueSheetRt::select([
            'technique_sheet_rt.technique_sheet_rt_id as id',
            'technique_sheet_rt.technique_sheet_rt_no',
            'technique_sheet_rt.technique_sheet_rt_date',
            'customers.customer',
            'technique_sheet_rt.entry_type_fix',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            DB::raw('technique_sheet_rt.part_no as part_no'),
            DB::raw('technique_sheet_rt.drg_no as drg_no'),
            'materials.material',
            'test_report_rt.heat_no',
            'test_report_rt.rt_no',
            'test_report_rt.product_code'
        ])
        ->leftJoin('customers', 'customers.id', '=', 'technique_sheet_rt.customer_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'technique_sheet_rt.type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'technique_sheet_rt.job_desc_id')
        ->leftJoin('test_report_rt', 'test_report_rt.test_report_rt_id', '=', 'technique_sheet_rt.test_report_rt_id')
        ->leftJoin('materials', 'materials.id', '=', 'test_report_rt.material_id')
        ->where('technique_sheet_rt.current_location_id', $location_data->location_id)
        ->whereIn('technique_sheet_rt.year_id', getCompanyYearIdsToTill());

        if ($request->filled('type_of_job_id')) {
            $query->where('technique_sheet_rt.type_of_job_id', $request->type_of_job_id);
        }

        // if ($request->filled('customer_id')) {
        //     $query->where('technique_sheet_rt.customer_id', $request->customer_id);
        // }

        $list = $query->orderBy('technique_sheet_rt.technique_sheet_rt_id', 'desc')->get();

        foreach ($list as $row) {
            $row->technique_sheet_rt_date = $row->technique_sheet_rt_date != null
                ? Date::createFromFormat('Y-m-d', $row->technique_sheet_rt_date)->format(DATE_FORMAT)
                : '';
        }

        return response()->json([
            'response_code' => 1,
            'list' => $list
        ]);
    }
    public function techniqueSheetRtLNRData()
    {
        // $year_data = getCurrentYearData();
        $location_data = getCurrentLocation();
        $lnr_data = TechniqueSheetRt::select([
            'lead_screen_thick',
            'lead_screen_thick_back'
        ])
        ->where('current_location_id', $location_data->location_id)
        ->orderBy('technique_sheet_rt_id', 'desc')
        ->first();

        return response()->json([
            'response_code' => 1,
            'lnr_data'      => $lnr_data,
        ]);
    }

    public function getPendingCustomers(Request $request)
    {  
        $getCustomer = Customer::select('id', 'customer')->orderBy('customer')->get();
            
        if($getCustomer != null)
        {
            return response()->json([
                'response_code' => 1,
                'getCustomer'  => $getCustomer,                
            ]);
        }else{
            return response()->json([
                'response_code' => 0,
                'getCustomer'  => "",    
            ]);
        }
    }

    public function pendingRtReportPendingList(Request $request)
    {
        try {
            $yearIds = getCompanyYearIdsToTill();
            $location_data = getCurrentLocation();

            $pendingListQuery = TestReportRt::select([
                'test_report_rt.test_report_rt_id',
                'test_report_rt.test_report_date',
                'test_report_rt.test_report_no',
                'test_report_rt.revision_number',
                'type_of_job.type_of_job',
                'job_descriptions.job_description',
                // 'part.part_no',
                DB::raw("test_report_rt.part_no"),

                'materials.material',   
                'test_report_rt.sp_note'
               
            ])

            // ->leftJoin('test_report_rt','test_report_rt.test_report_rt_id','test_report_rt_details.test_report_rt_id')

            ->leftJoin('technique_sheet_rt', 'technique_sheet_rt.test_report_rt_id', '=', 'test_report_rt.test_report_rt_id')

            ->leftJoin('customers','customers.id','test_report_rt.customer_id') 
            ->leftJoin('type_of_job','type_of_job.id','test_report_rt.type_of_job_id') 
            ->leftJoin('job_descriptions','job_descriptions.id','test_report_rt.job_desc_id') 
            // ->leftJoin('part','part.part_id','test_report_rt.part_id') 
            ->leftJoin('materials','materials.id','test_report_rt.material_id') 

            ->where('test_report_rt.current_location_id', $location_data->location_id)
            ->whereIn('test_report_rt.year_id', $yearIds);

            if ($request->filled('type_of_job_id')) {
                $pendingListQuery->where('test_report_rt.type_of_job_id', $request->type_of_job_id);
            }

            $pendingList = $pendingListQuery->get();
            foreach ($pendingList as $item) {

                $item->test_report_date = $item->test_report_date ? Date::createFromFormat('Y-m-d', $item->test_report_date)->format('d/m/Y') : '';
            }

            return response()->json([
                'response_code' => '1',
                'PendingList' => $pendingList,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'response_code' => '0',
                'response_message' => $e->getMessage(),
            ]);
        }
    }

    public function getpendingRtReportData(Request $request)
    {
        try {

            $testReportId = $request->test_report_rt_id;
            // Header Data
            $testReport = TestReportRt::select(

                    'test_report_rt.test_report_rt_id',
                    'test_report_rt.customer_id',
                    'test_report_rt.type_of_job_id',
                    'test_report_rt.job_desc_id',
                    'test_report_rt.part_id',
                    'test_report_rt.area_of_coverage_id',
                    'test_report_rt.source_used',
                    'test_report_rt.source_size',
                    'test_report_rt.xray_focal_size',
                    'test_report_rt.lead_screen_thick',
                    'test_report_rt.iqi',
                    'test_report_rt.film_processing',
                    'test_report_rt.test_technique',
                    'test_report_rt.test_arrangement',
                    'test_report_rt.test_class',
                    'test_report_rt.film_brand',
                    'test_report_rt.film_type',
                    'test_report_rt.procedure_ref_id',
                    'test_report_rt.evaluation_as_per_id',
                    'test_report_rt.acceptance_standard_id',
                    'test_report_rt.customer_procedure_ref',
                    'test_report_rt.film_size_unit_fix',
                    'test_report_rt.sfd_unit_fix',
                    'test_report_rt.film_size',
                    'test_report_rt.total_area',
                    'test_report_rt.no_of_films',

                )
                ->where('test_report_rt_id', $testReportId)
                ->first();

            // Detail Data
            $testReportDetails = TestReportRtDetails::select(
                    'test_report_rt_details.test_report_rt_details_id',
                    'test_report_rt_details.sr_no',
                    'test_report_rt_details.test_report_rt_id',
                    'test_report_rt_details.identification',
                    'test_report_rt_details.location',
                    'test_report_rt_details.source_id_fix',
                    'film_brand.film_brand',
                    'test_report_rt_details.film_brand_id',
                    'film_type.film_type',
                    'test_report_rt_details.film_id',
                    'film.film_size_inch as film_size',
                    'test_report_rt_details.film_type_id',
                    'test_report_rt_details.thickness',
                    'test_report_rt_details.sfd',
                    DB::raw("IFNULL(NULLIF(test_report_rt_details.iqi_designation, ''), iqi_designation.iqi_designation) as iqi_designation"),
                    'test_report_rt_details.iqi_designation_id',
                    DB::raw("IFNULL(NULLIF(test_report_rt_details.iqi_sensitivity, ''), iqi_sensitivity.iqi_sensitivity) as iqi_sensitivity"),
                    'test_report_rt_details.iqi_sensitivity_id',
                    'test_report_rt_details.no_of_film_fix',
                    'test_report_rt_details.exposure_time',
                    'test_report_rt_details.finding_id',
                    'test_report_rt_details.finding_level_id',
                    'test_report_rt_details.result_id',
                    'test_report_rt_details.film_qty',
                    'test_report_rt_details.sq_in',
                    'test_report_rt_details.sq_cm',
                    'test_report_rt_details.total_sq_in',
                    'test_report_rt_details.total_sq_cm',

                
                    
                )
                ->leftJoin('film','film.film_id','test_report_rt_details.film_id')
                ->leftJoin('film_brand','film_brand.film_brand_id','test_report_rt_details.film_brand_id')
                ->leftJoin('film_type','film_type.film_type_id','test_report_rt_details.film_type_id')
                ->leftJoin('iqi_designation','iqi_designation.iqi_designation_id','test_report_rt_details.iqi_designation_id')
                ->leftJoin('iqi_sensitivity','iqi_sensitivity.iqi_sensitivity_id','test_report_rt_details.iqi_sensitivity_id')
                ->where('test_report_rt_details.test_report_rt_id', $testReportId)
                ->get();

                foreach ($testReportDetails as $row) {
                    $row['mode'] = 'Insert';                        
                }

            return response()->json([
                'response_code'      => 1,
                'test_report_data'   => $testReport,
                'test_report_details'=> $testReportDetails,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'response_code'    => 0,
                'response_message' => $e->getMessage(),
            ]);
        }
    }
}
