<?php

namespace App\Http\Controllers;

use App\Models\Shift;
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

class ShiftController extends Controller
{
    public function getShifts()
    {
        $shifts = Shift::orderBy('shift_name', 'ASC')->get();
        if($shifts)
        {
            return response()->json([
                'shifts' => $shifts,
                'response_code' => '1'
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'No Data Available',
            ]);
        }
    }

    public function manage()
    {
        return view('manage.manage-shift');
    }

    public function index(Shift $Shift, Request $request, DataTables $datatables)
    {
        $shift_data = Shift::select([
            'shift.shift_id',
            'shift.shift_name',
            'shift.created_on',
            'shift.created_by',
            'shift.last_by',
            'shift.last_on'
        ]);
        $dataTable = DataTables::of($shift_data)
        ->editColumn('shift_name', function($shift_data){
            return $shift_data->shift_name;
        })
        ->addColumn('options',function($shift_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("shift", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_shift"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("shift", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'shift');
        return $dataTable
        ->rawColumns(['options','shift_name','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shift_name'=>'required|max:100|unique:shift',
        ],
        [
            'shift_name.required' => 'Please Enter Shift Name',
            'shift_name.max' => 'Maximum 100 Characters Allowed',
        ]);

        DB::beginTransaction();
        try
        {
            $shift_data = Shift::create([
                'shift_name' => $request->shift_name,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($shift_data->save())
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
        $shift_data = Shift::where('shift_id','=',$request->id)->first();
        if($shift_data)
        {
            return response()->json([
                'shift_data' => $shift_data,
                'response_code' => '1',
                'response_message' => '',
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exist',
            ]);
        }
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'shift_name'=>['required','max:100',Rule::unique('shift')->ignore($request->id, 'shift_id')]
        ],
        [
            'shift_name.required' => 'Please Enter Shift Name',
            'shift_name.max' => 'Maximum 100 Characters Allowed'
        ]);

        DB::beginTransaction();

        try
        {
            $shift_data = Shift::where('shift_id','=',$request->id)->update([
                'shift_name' => $request->shift_name,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($shift_data)
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
            $usage = DB::select('CALL shift_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                DB::rollBack();
                $message = "You Can't Delete, Shift Is Used In " . $usage[0]->table_name . ".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            Shift::destroy($request->id);
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

    public function existsShift(Request $request)
    {
        if($request->term != "")
        {
            $fdShift = Shift::select('shift_name')->where('shift_name', 'LIKE', '%'.$request->term.'%')->groupBy('shift_name')->get();
            if($fdShift != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdShift as $dsKey)
                {
                    $output .= '<li parent-id="shift_name" list-id="shift_list" class="list-group-item" tabindex="0">'.$dsKey->shift_name.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'shiftList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Shift available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'shiftList' => '',
                'response_code' => 1,
            ]);
        }
    }
}
