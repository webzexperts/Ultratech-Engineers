<?php

namespace App\Http\Controllers;

use App\Models\TypeOfJob;
use App\Models\Admin;
use App\Models\InquiryDetails;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DataTables;
use Date;
use Maatwebsite\Excel\Facades\Excel;

class TypeOfJobController extends Controller
{
    public function getTypeOfJobData()
    {
        $type_of_job = TypeOfJob::orderBy('type_of_job', 'ASC')->get();
        if($type_of_job)
        {
            return response()->json([
                'type_of_job' => $type_of_job,
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
        return view('manage.manage-type_of_job');
    }

    public function index(TypeOfJob $TypeOfJob, Request $request, DataTables $datatables)
    {
        $type_of_job_data = TypeOfJob::select([
            'type_of_job.id',
            'type_of_job.type_of_job',
            'type_of_job.created_on',
            'type_of_job.created_by',
            'type_of_job.last_by',
            'type_of_job.last_on'
        ]);
        $dataTable = DataTables::of($type_of_job_data)
        ->editColumn('type_of_job', function($type_of_job_data){
            return $type_of_job_data->type_of_job;
        })
        ->addColumn('options',function($type_of_job_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("type_of_job", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_type_of_job"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("type_of_job", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'type_of_job');
        return $dataTable
        ->rawColumns(['options','type_of_job','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type_of_job'=>'required|max:255|unique:type_of_job',
        ],
        [
            'type_of_job.required' => 'Please Enter Type of Job',
            'type_of_job.max' => 'Maximum 255 Characters Allowed',
        ]);

        DB::beginTransaction();
        try
        {
            $type_of_job_data = TypeOfJob::create([
                'type_of_job' => $request->type_of_job,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($type_of_job_data->save())
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
        $type_of_job_data = TypeOfJob::where('id','=',$request->id)->first();
        if($type_of_job_data)
        {
            return response()->json([
                'type_of_job_data' => $type_of_job_data,
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
            'type_of_job'=>['required','max:255',Rule::unique('type_of_job')->ignore($request->id, 'id')]
        ],
        [
            'type_of_job.required' => 'Please Enter Type of Job',
            'type_of_job.max' => 'Maximum 255 Characters Allowed'
        ]);

        DB::beginTransaction();

        try
        {
            $type_of_job_data = TypeOfJob::where('id','=',$request->id)->update([
                'type_of_job' => $request->type_of_job,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($type_of_job_data)
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

            $usage = DB::select('CALL type_of_job_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                $message = "You Can't Delete, Type Of Job Is Used In ". $usage[0]->table_name.".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }
            // $inqd_type_of_job_id = InquiryDetails::where('inqd_type_of_job_id',$request->id)->get();
            // if($inqd_type_of_job_id->isNotEmpty())
            // {
            //     return response()->json([
            //         'response_code' => '0',
            //         'response_message' => "You Can't Delete, Type of Job Is Used In Inquiry.",
            //     ]);
            // }

            TypeOfJob::destroy($request->id);
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

    public function existsTypeofJob(Request $request)
    {
        if($request->term != "")
        {
            $fdTypeofJob = TypeOfJob::select('type_of_job')->where('type_of_job', 'LIKE', '%'.$request->term.'%')->groupBy('type_of_job')->get();
            if($fdTypeofJob != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdTypeofJob as $dsKey)
                {
                    $output .= '<li parent-id="type_of_job" list-id="type_of_job_list" class="list-group-item" tabindex="0">'.$dsKey->type_of_job.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'type_of_jobList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Type of Job available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'type_of_jobList' => '',
                'response_code' => 1,
            ]);
        }
    }
}