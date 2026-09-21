<?php

namespace App\Http\Controllers;

use App\Models\IqiDesignation;
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

class IqiDesignationController extends Controller
{
    public function getIqiDesignations()
    {
        $iqi_designations = IqiDesignation::orderBy('iqi_designation', 'ASC')->get();
        if($iqi_designations)
        {
            return response()->json([
                'iqi_designations' => $iqi_designations,
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
        return view('manage.manage-iqi_designation');
    }

    public function index(IqiDesignation $IqiDesignation, Request $request, DataTables $datatables)
    {
        $iqi_designation_data = IqiDesignation::select([
            'iqi_designation.iqi_designation_id as id',
            'iqi_designation.iqi_designation',
            'iqi_designation.created_on',
            'iqi_designation.created_by',
            'iqi_designation.last_by',
            'iqi_designation.last_on'
        ]);
        $dataTable = DataTables::of($iqi_designation_data)
        ->editColumn('iqi_designation', function($iqi_designation_data){
            return $iqi_designation_data->iqi_designation;
        })
        ->addColumn('options',function($iqi_designation_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("iqi_designation", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_iqi_designation"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("iqi_designation", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'iqi_designation');
        return $dataTable
        ->rawColumns(['options','iqi_designation','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'iqi_designation'=>'required|max:100|unique:iqi_designation',
        ],
        [
            'iqi_designation.required' => 'Enter IQI Designation',
            'iqi_designation.max' => 'Maximum 100 Characters Allowed',
        ]);

        DB::beginTransaction();
        try
        {
            $iqi_designation_data = IqiDesignation::create([
                'iqi_designation' => $request->iqi_designation,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($iqi_designation_data->save())
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
        $iqi_designation_data = IqiDesignation::where('iqi_designation_id','=',$request->id)->first();
        if($iqi_designation_data)
        {
            return response()->json([
                'iqi_designation_data' => $iqi_designation_data,
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
            'iqi_designation'=>['required','max:100',Rule::unique('iqi_designation')->ignore($request->id, 'iqi_designation_id')]
        ],
        [
            'iqi_designation.required' => 'Enter IQI Designation',
            'iqi_designation.max' => 'Maximum 100 Characters Allowed'
        ]);

        DB::beginTransaction();

        try
        {
            IqiDesignation::where('iqi_designation_id','=',$request->id)->update([
                'iqi_designation' => $request->iqi_designation,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

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
        DB::beginTransaction();
        try
        {
            $usage = DB::select('CALL iqi_designation_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                DB::rollBack();
                $message = "You Can't Delete, IQI Designation Is Used In " . $usage[0]->table_name . ".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            IqiDesignation::destroy($request->id);
            DB::commit();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        }
        catch(\Exception $e)
        {
            report($e);
            DB::rollBack();
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
    //     $query = IqiDesignation::where('iqi_designation', $request->iqi_designation);

    //     if($request->filled('id'))
    //     {
    //         $query->where('iqi_designation_id', '!=', $request->id);
    //     }

    //     if($query->exists())
    //     {
    //         return response()->json([
    //             'response_code' => 1,
    //             'response_message' => 'IQI Designation Already Exists',
    //         ]);
    //     }

    //     return response()->json([
    //         'response_code' => 0,
    //     ]);
    // }

    public function existsIqiDesignation(Request $request)
    {
        if($request->term != "")
        {
            $fdIqiDesignation = IqiDesignation::select('iqi_designation')->where('iqi_designation', 'LIKE', '%'.$request->term.'%')->groupBy('iqi_designation')->get();
            if($fdIqiDesignation != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdIqiDesignation as $dsKey)
                {
                    $output .= '<li parent-id="iqi_designation" list-id="iqi_designation_list" class="list-group-item" tabindex="0">'.$dsKey->iqi_designation.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'iqiDesignationList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No IQI Designation available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'iqiDesignationList' => '',
                'response_code' => 1,
            ]);
        }
    }
}
