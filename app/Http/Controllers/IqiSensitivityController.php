<?php

namespace App\Http\Controllers;

use App\Models\IqiSensitivity;
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

class IqiSensitivityController extends Controller
{
    public function getIqiSensitivities()
    {
        $iqi_sensitivities = IqiSensitivity::orderBy('iqi_sensitivity', 'ASC')->get();
        if($iqi_sensitivities)
        {
            return response()->json([
                'iqi_sensitivities' => $iqi_sensitivities,
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
        return view('manage.manage-iqi_sensitivity');
    }

    public function index(IqiSensitivity $IqiSensitivity, Request $request, DataTables $datatables)
    {
        $iqi_sensitivity_data = IqiSensitivity::select([
            'iqi_sensitivity.iqi_sensitivity_id as id',
            'iqi_sensitivity.iqi_sensitivity',
            'iqi_sensitivity.created_on',
            'iqi_sensitivity.created_by',
            'iqi_sensitivity.last_by',
            'iqi_sensitivity.last_on'
        ]);
        $dataTable = DataTables::of($iqi_sensitivity_data)
        ->editColumn('iqi_sensitivity', function($iqi_sensitivity_data){
            return $iqi_sensitivity_data->iqi_sensitivity;
        })
        ->addColumn('options',function($iqi_sensitivity_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("iqi_sensitivity", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_iqi_sensitivity"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("iqi_sensitivity", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'iqi_sensitivity');
        return $dataTable
        ->rawColumns(['options','iqi_sensitivity','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'iqi_sensitivity'=>'required|max:100|unique:iqi_sensitivity',
        ],
        [
            'iqi_sensitivity.required' => 'Enter IQI Sensitivity',
            'iqi_sensitivity.max' => 'Maximum 100 Characters Allowed',
        ]);

        DB::beginTransaction();
        try
        {
            $iqi_sensitivity_data = IqiSensitivity::create([
                'iqi_sensitivity' => $request->iqi_sensitivity,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($iqi_sensitivity_data->save())
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
        $iqi_sensitivity_data = IqiSensitivity::where('iqi_sensitivity_id','=',$request->id)->first();
        if($iqi_sensitivity_data)
        {
            return response()->json([
                'iqi_sensitivity_data' => $iqi_sensitivity_data,
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
            'iqi_sensitivity'=>['required','max:100',Rule::unique('iqi_sensitivity')->ignore($request->id, 'iqi_sensitivity_id')]
        ],
        [
            'iqi_sensitivity.required' => 'Enter IQI Sensitivity',
            'iqi_sensitivity.max' => 'Maximum 100 Characters Allowed'
        ]);

        DB::beginTransaction();

        try
        {
            $iqi_sensitivity_data = IqiSensitivity::where('iqi_sensitivity_id','=',$request->id)->update([
                'iqi_sensitivity' => $request->iqi_sensitivity,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($iqi_sensitivity_data)
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
        DB::beginTransaction();
        try
        {
            $usage = DB::select('CALL iqi_sensitivity_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                DB::rollBack();
                $message = "You Can't Delete, IQI Sensitivity Is Used In " . $usage[0]->table_name . ".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            IqiSensitivity::destroy($request->id);
            DB::commit();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        }
        catch(\Exception $e)
        {
            DB::rollBack();
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

    // public function verify(Request $request)
    // {
    //     $query = IqiSensitivity::where('iqi_sensitivity', $request->iqi_sensitivity);

    //     if($request->filled('id'))
    //     {
    //         $query->where('iqi_sensitivity_id', '!=', $request->id);
    //     }

    //     if($query->exists())
    //     {
    //         return response()->json([
    //             'response_code' => 1,
    //             'response_message' => 'IQI Sensitivity Already Exists',
    //         ]);
    //     }

    //     return response()->json([
    //         'response_code' => 0,
    //     ]);
    // }

    public function existsIqiSensitivity(Request $request)
    {
        if($request->term != "")
        {
            $fdIqiSensitivity = IqiSensitivity::select('iqi_sensitivity')->where('iqi_sensitivity', 'LIKE', '%'.$request->term.'%')->groupBy('iqi_sensitivity')->get();
            if($fdIqiSensitivity != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdIqiSensitivity as $dsKey)
                {
                    $output .= '<li parent-id="iqi_sensitivity" list-id="iqi_sensitivity_list" class="list-group-item" tabindex="0">'.$dsKey->iqi_sensitivity.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'iqiSensitivityList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No IQI Sensitivity available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'iqiSensitivityList' => '',
                'response_code' => 1,
            ]);
        }
    }
}
