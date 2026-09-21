<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Item;
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

class UnitController extends Controller
{
    public function getUnits()
    {
        $units = Unit::orderBy('unit', 'ASC')->get();
        if($units)
        {
            return response()->json([
                'units' => $units,
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
        return view('manage.manage-unit');
    }

    public function index(Unit $Unit, Request $request, DataTables $datatables)
    {
        $unit_data = Unit::select([
            'unit.id',
            'unit.unit',
            'unit.created_on',
            'unit.created_by',
            'unit.last_by',
            'unit.last_on'
        ]);
        $dataTable = DataTables::of($unit_data)
        ->editColumn('unit', function($unit_data){
            return $unit_data->unit;
        })
        ->addColumn('options',function($unit_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                // if (!in_array($unit_data->id, [1, 2])) {
                    if (hasAccess("unit", "edit")) {
                        $action .= '<li><a class="dropdown-item edit-item-btn edit_unit"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                    }
                // }

                // if (!in_array($unit_data->id, [1, 2])) {
                    if (hasAccess("unit", "delete")) {
                        $action .= '<li> <a class="dropdown-item remove-item-btn">
                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                        </a>
                                    </li>';
                    }
                // }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'unit');
        return $dataTable
        ->rawColumns(['options','unit','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit'=>'required|max:255|unique:unit',
        ],
        [
            'unit.required' => 'Please Enter Unit',
            'unit.max' => 'Maximum 255 Characters Allowed',
        ]);

        DB::beginTransaction();
        try
        {
            $unit_data = Unit::create([
                'unit' => $request->unit,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($unit_data->save())
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
        $unit_data = Unit::where('id','=',$request->id)->first();
        if($unit_data)
        {
            return response()->json([
                'unit_data' => $unit_data,
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
            'unit'=>['required','max:255',Rule::unique('unit')->ignore($request->id, 'id')]
        ],
        [
            'unit.required' => 'Please Enter Unit',
            'unit.max' => 'Maximum 255 Characters Allowed'
        ]);

        DB::beginTransaction();

        try
        {
            $unit_data = Unit::where('id','=',$request->id)->update([
                'unit' => $request->unit,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($unit_data)
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
            // if($request->id == "1" || $request->id == "2")
            // {
            //     return response()->json([
            //         'response_code' => "0",
            //         'response_message' => "This Is Used For Reference, You Can't Delete",
            //     ]);
            // }

            $usage = DB::select('CALL unit_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                DB::rollBack();
                $message = "You Can't Delete, Unit Is Used In " . $usage[0]->table_name . ".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            Unit::destroy($request->id);
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

    public function existsUnit(Request $request)
    {
        if($request->term != "")
        {
            $fdUnit = Unit::select('unit')->where('unit', 'LIKE', '%'.$request->term.'%')->groupBy('unit')->get();
            if($fdUnit != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdUnit as $dsKey)
                {
                    $output .= '<li parent-id="unit" list-id="unit_list" class="list-group-item" tabindex="0">'.$dsKey->unit.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'unitList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Unit available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'unitList' => '',
                'response_code' => 1,
            ]);
        }
    }
}