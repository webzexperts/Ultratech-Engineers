<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobDescription;
use App\Models\InquiryDetails;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DataTables;
use Date;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Admin;
use App\Models\Part;
use Illuminate\Http\Response;

class JobDescriptionController extends Controller
{
    public function manage()
    {
        return view('manage.manage-job_description');
    }

    public function getJobDescriptionData()
    {
        $jobdescriptions = JobDescription::with('parts')->orderBy('job_description', 'ASC')->get();
        if($jobdescriptions)
        {
            return response()->json([
                'jobdescriptions' => $jobdescriptions,
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

    public function index(JobDescription $JobDesc, Request $request, DataTables $datatables)
    {
        $jobdescription_data = JobDescription::select([
            'job_descriptions.id',
            'job_descriptions.job_description',
            'job_descriptions.last_by',
            'job_descriptions.last_on',
            'job_descriptions.created_by',
            'job_descriptions.created_on'
        ]);
        $dataTable =DataTables::of($jobdescription_data)
        ->addColumn('options',function($jobdescription_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

               
                if (hasAccess("job_description", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_job_description"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }


               
                if (hasAccess("job_description", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
                
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'job_descriptions');
        return $dataTable
        ->rawColumns(['options','job_description','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_description'=>'required|max:255|unique:job_descriptions',
        ],
        [
            'job_description.unique' => 'Job Description Has Already Been Taken',
            'job_description.required' => 'Please Enter Job Description',
            'job_description.max' => 'Maximum 255 Characters Allowed',
        ]);

        DB::beginTransaction();
        try
        {
            $jobdescription_data = JobDescription::create([
                'job_description' => $request->job_description,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($jobdescription_data->save())
            {
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
                    'response_message' => getResponseMessage('store_error'),
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
        $jobdescription_data = JobDescription::select('id','job_description')->where('id','=',$request->id)->first();

        if($jobdescription_data)
        {
            return response()->json([
                'jobdescription' => $jobdescription_data,
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
            'job_description'=>['required','max:255',Rule::unique('job_descriptions')->ignore($request->id, 'id')]
        ],
        [
            'job_description.required' => 'Please Enter Job Description',
            'job_description.max' => 'Maximum 255 Characters Allowed'
        ]);

        DB::beginTransaction();

        try
        {
            $jobdescription_data = JobDescription::where('id','=',$request->id)->update([
                'job_description' => $request->job_description,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($jobdescription_data)
            {
                DB::commit();
                return response()->json([
                    'response_code' => '1',
                    'response_message' => getResponseMessage('update_success'),
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }
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

        try
        {
            $usage = DB::select('CALL job_description_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                $message = "You Can't Delete, Job Description Is Used In ". $usage[0]->table_name.".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }
            // $part_data = Part::where('job_desc_id','=',$request->id)->get(); 
            // if($part_data->isNotEmpty())
            // {
            //     return response()->json([
            //         'response_code' => '0',
            //         'response_message' => "You Can't Delete, Job Description Is Used In Part.",
            //     ]);
            // }

            // $inqd_job_description_id = InquiryDetails::where('inqd_job_description_id',$request->id)->get();
            // if($inqd_job_description_id->isNotEmpty())
            // {
            //     return response()->json([
            //         'response_code' => '0',
            //         'response_message' => "You Can't Delete, Job Description Is Used In Inquiry.",
            //     ]);
            // }

            JobDescription::destroy($request->id);
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

    public function existsJobDescription(Request $request)
    {
        if($request->term != "")
        {
            $fdJob = JobDescription::select('job_description')->where('job_description', 'LIKE', $request->term.'%')->groupBy('job_description')->get();
            if($fdJob != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdJob as $dsKey)
                {
                    $output .= '<li parent-id="job_description" list-id="job_description_list" class="list-group-item" tabindex="0">'.$dsKey->job_description.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'jobDescriptionList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Job Description Available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'jobDescriptionList' => '',
                'response_code' => 1,
            ]);
        }
    }

    public function verifyJobDescription(Request $request)
    {
        if(!empty($request->job_description))
        {
            $name = $request->job_description;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = JobDescription::where('job_description',$name)->where('id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = JobDescription::where('job_description',$name)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'job_description' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Record already exists.',
                    // 'response_message' => 'The Country Name Has Already Been Taken',
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
