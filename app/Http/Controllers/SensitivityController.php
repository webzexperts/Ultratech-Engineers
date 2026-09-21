<?php

namespace App\Http\Controllers;

use App\Models\Sensitivity;
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

class SensitivityController extends Controller
{
    public function manage()
    {
        return view('manage.manage-sensitivity');
    }

    public function index(Sensitivity $Sensitivity, Request $request, DataTables $datatables)
    {
        $sensitivity_data = Sensitivity::select([
            'sensitivities.id',
            'sensitivities.sensitivity',
            'sensitivities.created_on',
            'sensitivities.created_by',
            'sensitivities.last_by',
            'sensitivities.last_on'
        ]);
        $dataTable = DataTables::of($sensitivity_data)
        ->editColumn('sensitivity', function($sensitivity_data){ 
            return $sensitivity_data->sensitivity;
        })
        ->addColumn('options',function($material_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("sensitivity", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_sensitivity"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("sensitivity", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'sensitivities');
        return $dataTable
        ->rawColumns(['options','sensitivity','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sensitivity'=>'required|max:255|unique:sensitivities',
        ],
        [
            'sensitivity.required' => 'Please Enter Sensitivity',
            'sensitivity.max' => 'Maximum 255 Characters Allowed',
        ]);

        DB::beginTransaction();
        try
        {
            $sensitivity_data = Sensitivity::create([
                'sensitivity' => $request->sensitivity,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($sensitivity_data->save())
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
        $sensitivity_data = Sensitivity::where('id','=',$request->id)->first();
        if($sensitivity_data)
        {
            return response()->json([
                'sensitivity_data' => $sensitivity_data,
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
            'sensitivity'=>['required','max:255',Rule::unique('sensitivities')->ignore($request->id, 'id')]
        ],
        [
            'sensitivity.required' => 'Please Enter Sensitivity',
            'sensitivity.max' => 'Maximum 255 Characters Allowed'
        ]);

        DB::beginTransaction();

        try
        {
            $sensitivity_data = Sensitivity::where('id','=',$request->id)->update([
                'sensitivity' => $request->sensitivity,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($sensitivity_data)
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
            Sensitivity::destroy($request->id);
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

    public function existsSensitivity(Request $request)
    {
        if($request->term != "")
        {
            $fdSensitivity = Sensitivity::select('sensitivity')->where('sensitivity', 'LIKE', '%'.$request->term.'%')->groupBy('sensitivity')->get();
            if($fdSensitivity != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdSensitivity as $dsKey)
                {
                    $output .= '<li parent-id="sensitivity" list-id="sensitivity_list" class="list-group-item" tabindex="0">'.$dsKey->sensitivity.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'sensitivityList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Sensitivity available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'sensitivityList' => '',
                'response_code' => 1,
            ]);
        }
    }
}