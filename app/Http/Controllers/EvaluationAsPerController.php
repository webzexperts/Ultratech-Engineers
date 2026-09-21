<?php

namespace App\Http\Controllers;

use App\Models\EvaluationAsPer;
use App\Models\Admin;
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

class EvaluationAsPerController extends Controller
{
    public function getEvaluationAsPers()
    {
        $evaluation_as_pers = EvaluationAsPer::orderBy('evaluation_as_per', 'ASC')->get();
        if($evaluation_as_pers)
        {
            return response()->json([
                'evaluation_as_pers' => $evaluation_as_pers,
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
        return view('manage.manage-evaluation_as_per');
    }

    public function index(EvaluationAsPer $EvaluationAsPer, Request $request, DataTables $datatables)
    {
        $evaluation_as_per_data = EvaluationAsPer::select([
            'evaluation_as_per.evaluation_as_per_id as id',
            'evaluation_as_per.evaluation_as_per',
            'evaluation_as_per.created_on',
            'evaluation_as_per.created_by',
            'evaluation_as_per.last_by',
            'evaluation_as_per.last_on'
        ]);
        $dataTable = DataTables::of($evaluation_as_per_data)
        ->editColumn('evaluation_as_per', function($evaluation_as_per_data){
            return $evaluation_as_per_data->evaluation_as_per;
        })
        ->addColumn('options',function($evaluation_as_per_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("evaluation_as_per", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_evaluation_as_per"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("evaluation_as_per", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'evaluation_as_per');
        return $dataTable
        ->rawColumns(['options','evaluation_as_per','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'evaluation_as_per'=>'required|max:100|unique:evaluation_as_per',
        ],
        [
            'evaluation_as_per.required' => 'Enter Evaluation as per',
            'evaluation_as_per.max' => 'Maximum 100 Characters Allowed',
        ]);

        DB::beginTransaction();
        try
        {
            $evaluation_as_per_data = EvaluationAsPer::create([
                'evaluation_as_per' => $request->evaluation_as_per,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($evaluation_as_per_data->save())
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
        $evaluation_as_per_data = EvaluationAsPer::where('evaluation_as_per_id','=',$request->id)->first();
        if($evaluation_as_per_data)
        {
            return response()->json([
                'evaluation_as_per_data' => $evaluation_as_per_data,
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
            'evaluation_as_per'=>['required','max:100',Rule::unique('evaluation_as_per')->ignore($request->id, 'evaluation_as_per_id')]
        ],
        [
            'evaluation_as_per.required' => 'Enter Evaluation as per',
            'evaluation_as_per.max' => 'Maximum 100 Characters Allowed'
        ]);

        DB::beginTransaction();

        try
        {
            $evaluation_as_per_data = EvaluationAsPer::where('evaluation_as_per_id','=',$request->id)->update([
                'evaluation_as_per' => $request->evaluation_as_per,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($evaluation_as_per_data)
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
            $usage = DB::select('CALL evaluation_as_per_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                $message = "You Can't Delete, Evaluation as per Is Used In ". $usage[0]->table_name.".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }
            EvaluationAsPer::destroy($request->id);
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

    public function verify(Request $request)
    {
        $query = EvaluationAsPer::where('evaluation_as_per', $request->evaluation_as_per);

        if($request->filled('id'))
        {
            $query->where('evaluation_as_per_id', '!=', $request->id);
        }

        if($query->exists())
        {
            return response()->json([
                'response_code' => 1,
                'response_message' => 'Evaluation as per Already Exists',
            ]);
        }

        return response()->json([
            'response_code' => 0,
        ]);
    }

    public function existsEvaluationAsPer(Request $request)
    {
        if($request->term != "")
        {
            $fdEvaluationAsPer = EvaluationAsPer::select('evaluation_as_per')->where('evaluation_as_per', 'LIKE', '%'.$request->term.'%')->groupBy('evaluation_as_per')->get();
            if($fdEvaluationAsPer != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdEvaluationAsPer as $dsKey)
                {
                    $output .= '<li parent-id="evaluation_as_per" list-id="evaluation_as_per_list" class="list-group-item" tabindex="0">'.$dsKey->evaluation_as_per.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'evaluationAsPerList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Evaluation as per available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'evaluationAsPerList' => '',
                'response_code' => 1,
            ]);
        }
    }
}
