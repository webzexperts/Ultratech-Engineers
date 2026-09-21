<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Date;
use Carbon\Carbon;
use App\Models\Part;
use App\Models\InquiryDetails;
use App\Models\QuotationDetails;
use App\Models\Transaction\OrderAcceptanceDetails;
use App\Models\Transaction\MaterialInwardDetails;
use App\Models\Transaction\TechniqueSheetRt;
use App\Models\Transaction\TestReportRt;


class PartController extends Controller
{
    public function getPartData()
    {
        $part = Part::orderBy('part_no', 'ASC')->get();
        if($part)
        {
            return response()->json([
                'part' => $part,
                'response_code' => '1'
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'No Data Avilable',
            ]);
        }  
    }

    public function manage()
    {
        return view('manage.manage-part');
    }

    public function index(Part $part_data, Request $request, DataTables $datatables)
    {
        $part_data = Part::select([
            'part.part_id',
            'part.part_no',
            'part.drg_no',
            'part.job_desc_id',
            'job_descriptions.job_description',
            'part.created_on',
            'part.created_by',
            'part.last_by',
            'part.last_on'
        ])
       ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'part.job_desc_id');
        $dataTable = DataTables::of($part_data)
        ->editColumn('part_no', function($part_data){ 
            return ucfirst($part_data->part_no);
        })
        ->editColumn('drg_no', function($part_data){ 
            return ucfirst($part_data->drg_no);
        })
        ->addColumn('options',function($part_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("part", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_part"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("part", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'part');
        return $dataTable
        ->rawColumns(['options','part_no','drg_no','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_desc_id' => 'required',
        ],
        [
            'job_desc_id.required' => 'Please Select Job Description',
        ]);

        DB::beginTransaction();
        try
        {
            if(isset($request->parts_detail_data) && !empty($request->parts_detail_data))
            {
                $convertJson = json_decode($request->parts_detail_data, true);
                foreach($convertJson as $ptKey => $ptVal)
                {
                    if($ptVal != null)
                    {
                        Part::create([
                            'job_desc_id' => $request->job_desc_id,
                            'part_no' => isset($ptVal['part_no']) ? $ptVal['part_no'] : "",
                            'drg_no' => isset($ptVal['drg_no']) ? $ptVal['drg_no'] : "",
                            'company_id' => Auth::user()->company_id,
                            'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                            'created_by' => Auth::user()->id
                        ]);
                    }
                }
                
                DB::commit();
                return response()->json([
                    'response_code' => '1',
                    'response_message' => getResponseMessage('store_success'),
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Please Add At Least One Part Details.',
                ]);
            }
        }
        catch(\Exception $e)
        {
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
        if ($request->has('job_desc_id')) {
            $part_data = Part::where('job_desc_id', '=', $request->job_desc_id)->first();
        } else {
            $part_data = Part::where('part_id', '=', $request->id)->first();
        }
        if($part_data)
        {
            $parts_data = Part::where('job_desc_id', $part_data->job_desc_id)->get();
            foreach($parts_data as $key => $val) {
                $in_use = false;
                if (InquiryDetails::where('inqd_part_id', $val->part_id)->exists()) {
                    $in_use = true;
                }
                elseif (QuotationDetails::where('quotd_part_id', $val->part_id)->exists()) {
                    $in_use = true;
                }
                elseif (OrderAcceptanceDetails::where('oad_part_id', $val->part_id)->exists()) {
                    $in_use = true;
                }
                elseif (MaterialInwardDetails::where('part_id', $val->part_id)->exists()) {
                    $in_use = true;
                }
                elseif (TechniqueSheetRt::where('part_id', $val->part_id)->exists()) {
                    $in_use = true;
                }
                elseif (TestReportRt::where('part_id', $val->part_id)->exists()) {
                    $in_use = true;
                }
                $val->in_use = $in_use;
            }

            return response()->json([
                'part_data' => $part_data,
                'parts_data' => $parts_data,
                'response_code' => '1',
                'response_message' => '',
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'job_desc_id' => 'required'
        ],
        [
            'job_desc_id.required' => 'Please Select Job Description'
        ]);

        DB::beginTransaction();

        try
        {
            $partsDetails = $request->only('parts_detail_data');
            $partsDetailData = json_decode($partsDetails['parts_detail_data'], true) ?? [];

            // Get all existing part IDs from DB for this job description
            $existingPartIds = Part::where('job_desc_id', '=', $request->job_desc_id)
                ->pluck('part_id')
                ->toArray();

            $incomingPartIds = [];

            foreach ($partsDetailData as $ptVal) {
                if ($ptVal === null) {
                    continue;
                }

                $partId = isset($ptVal['part_id']) ? (int)$ptVal['part_id'] : 0;

                if ($partId > 0 && in_array($partId, $existingPartIds)) {
                    // Update existing part
                    Part::where('part_id', $partId)->update([
                        'part_no' => isset($ptVal['part_no']) ? $ptVal['part_no'] : "",
                        'drg_no' => isset($ptVal['drg_no']) ? $ptVal['drg_no'] : "",
                        'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                        'last_by' => Auth::user()->id
                    ]);
                    $incomingPartIds[] = $partId;
                } else {
                    // Create new part
                    $newPart = Part::create([
                        'job_desc_id' => $request->job_desc_id,
                        'part_no' => isset($ptVal['part_no']) ? $ptVal['part_no'] : "",
                        'drg_no' => isset($ptVal['drg_no']) ? $ptVal['drg_no'] : "",
                        'company_id' => Auth::user()->company_id,
                        'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                        'created_by' => Auth::user()->id
                    ]);
                    $incomingPartIds[] = $newPart->part_id;
                }
            }

            // Delete parts that are in the database but were not sent in the incoming list
            $partsToDelete = array_diff($existingPartIds, $incomingPartIds);
            if (!empty($partsToDelete)) {
                Part::whereIn('part_id', $partsToDelete)->delete();
            }

            DB::commit();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('update_success'),
            ]);
        }
        catch(\Exception $e)
        {
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
        // dd($request->all());
        try
        {
            $usage = DB::select('CALL part_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                $message = "You Can't Delete, Part Is Used In ". $usage[0]->table_name.".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }
            // $inqd_part_id = InquiryDetails::where('inqd_part_id',$request->id)->get();
            // if($inqd_part_id->isNotEmpty())
            // {
            //     return response()->json([
            //         'response_code' => '0',
            //         'response_message' => "You Can't Delete, Part Is Used In Inquiry.",
            //     ]);
            // }

            Part::destroy($request->id);
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        }
        catch(\Exception $e)
        {
            report($e);
            if(isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451)
            {
                $error_msg = "This Is Used Somewhere, You Can't Delete";
            }
            else
            {
                $error_msg = getResponseMessage('delete_error');
            }

            return response()->json([
                'response_code' => '0',
                'response_message' => $error_msg,
            ]);
        }
    }

    public function existsPart(Request $request)
    {
        if($request->term != "")
        {
            $fdJob = Part::select('part_no')->where('part_no', 'LIKE', $request->term.'%')->groupBy('part_no')->get();
            if($fdJob != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdJob as $dsKey)
                {
                    $output .= '<li parent-id="part_no" list-id="part_list" class="list-group-item" tabindex="0">'.$dsKey->part_no.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'partList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Part Available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'partList' => '',
                'response_code' => 1,
            ]);
        }
    }

    public function verifyPart(Request $request)
    {
        if(!empty($request->part_no))
        {
            $part_no = $request->part_no;
            $drg_no = $request->drg_no ?? '';
            $job_desc_id = $request->job_desc_id;
            
            $query = Part::where('part_no', $part_no)
                         ->where('job_desc_id', $job_desc_id)
                         ->where(function($q) use ($drg_no) {
                             if ($drg_no === '') {
                                 $q->whereNull('drg_no')->orWhere('drg_no', '');
                             } else {
                                 $q->where('drg_no', $drg_no);
                             }
                         });
                         
            if(isset($request->part_id) && $request->part_id != "")
            {
                $query->where('part_id', '!=', $request->part_id);
            }
            
            $users_count = $query->first();

            if($users_count)
            {
                return response()->json([
                    'part' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Part No. & Drg. No. Found.',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

}
