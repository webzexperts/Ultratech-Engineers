<?php

namespace App\Http\Controllers;

use App\Models\State;
use App\Models\Country;
use App\Models\City;
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

class StateController extends Controller
{
    public function getStateData()
    {
        $states = State::orderBy('state', 'ASC')->get();
        if($states)
        {
            return response()->json([
                'states' => $states,
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
        return view('manage.manage-state');
    }

    public function index(State $State, Request $request, DataTables $datatables)
    {
        $state_data = State::select([
            'states.id',
            'states.state',
            'states.state_code',
            'countries.country_name',
            'states.created_on',
            'states.created_by',
            'states.last_by',
            'states.last_on'
        ])
        ->leftJoin('countries','countries.id','=','states.country_id');
        $dataTable =DataTables::of($state_data)
        ->editColumn('state', function($state_data){
            return Str::limit($state_data->state, 50);
        })
        ->editColumn('country', function($state_data){
            return Str::limit($state_data->country, 50);
        })
        ->addColumn('options',function($state_data){
            $action ='';
            if ($state_data->id != 1) {
            $action .= '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                
                    if (hasAccess("state", "edit")) {
                        $action .= '<li><a class="dropdown-item edit-item-btn edit_state"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                    }
               
                    if (hasAccess("state", "delete")) {
                        $action .= '<li> <a class="dropdown-item remove-item-btn">
                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                        </a>
                                    </li>';
                    }
                    $action .= '</ul></div>';
            }
                    return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'states');
        return $dataTable
        ->rawColumns(['options','state','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['state' => ['required', 'max:155', Rule::unique('states')->where(function ($query) use ($request) {
            return $query->where('state','=',$request->state)->where('country_id', '=', $request->country_id)->where('state_code',$request->state_code);
        })],
        'country_id'=>'required',
        ],
        [
            'state.required' => 'Please Enter State',
            'state.max' => 'Maximum 255 Characters Allowed',
            'country_id.required' => 'Please Select Country',
            'state_code' => 'Maximum 15 Characters Allowed',
        ]);

        DB::beginTransaction();
        try
        {
            $state_data = State::create([
                'state' => $request->state,
                'state_code' => $request->state_code,
                'country_id' => $request->country_id,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($state_data->save())
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
        $state_data = State::where('id','=',$request->id)->first();
        if($state_data)
        {
            return response()->json([
                'state_data' => $state_data,
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
            'state' => ['required', 'max:155', Rule::unique('states')->where(function ($query) use ($request) {
                return $query->where('state',$request->state)->where('country_id', '=', $request->country_id)->where('state_code', '=', $request->state_code);
            })->ignore($request->id, 'id')],
            'state_code'=>[$request->state_code ? Rule::unique('states')->ignore($request->id, 'id') : ''],
            'country_id'=>'required',
        ],
        [
            'state.required' => 'Please Enter State',
            'state.max' => 'Maximum 255 Characters Allowed',
            'country_id.required' => 'Please Select Country'
        ]);

        DB::beginTransaction();

        try
        {
            $state_data = State::where('id','=',$request->id)->update([
                'state' => $request->state,
                'state_code' => $request->state_code,
                'country_id' => $request->country_id,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($state_data)
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
            $usage = DB::select('CALL state_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                DB::rollBack();
                $message = "You Can't Delete, State Is Used In " . $usage[0]->table_name . ".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            State::destroy($request->id);
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

    public function existsState(Request $request)
    {
        if($request->term != "")
        {
            $fdState = State::select('state')->where('state', 'LIKE', '%'.$request->term.'%')->groupBy('state')->get();
            if($fdState != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdState as $dsKey)
                {
                    $output .= '<li parent-id="state" list-id="state_list" class="list-group-item" tabindex="0">'.$dsKey->state.'</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'stateList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No State available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'stateList' => '',
                'response_code' => 1,
            ]);
        }
    }
}