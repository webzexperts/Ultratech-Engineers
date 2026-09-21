<?php

namespace App\Http\Controllers;

use App\Models\State;
use App\Models\City;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\CustomerContacts;
use App\Models\Supplier;
use App\Models\Location;
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

class CityController extends Controller
{
    public function getCityData()
    {
        $cities = City::orderBy('city', 'ASC')->get();
        if($cities)
        {
            return response()->json([
                'cities' => $cities,
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
        return view('manage.manage-city');
    }

    public function index(City $City, Request $request, DataTables $datatables)
    {
        $city_data = City::select([
            'cities.id',
            'cities.city',
            'countries.country_name',
            'states.state_code',
            'states.state',
            'cities.created_on',
            'cities.created_by',
            'cities.last_by',
            'cities.last_on'
        ])
        ->leftJoin('states','states.id','=','cities.state_id')
        ->leftJoin('countries','countries.id','=','states.country_id');
        $dataTable = DataTables::of($city_data)
        ->editColumn('city', function($city_data){ 
            return Str::limit($city_data->city, 50);
        })
        ->editColumn('country', function($city_data){ 
            return Str::limit($city_data->country, 50);
        })
        ->editColumn('state', function($city_data){ 
            return Str::limit($city_data->state, 50);
        })
        ->addColumn('options',function($city_data){
            $action = "";
            if ($city_data->id != 1) {
             $action .= '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';
               
                    if (hasAccess("city", "edit")) {
                        $action .= '<li><a class="dropdown-item edit-item-btn edit_city"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                    }
                    if (hasAccess("city", "delete")) {
                        $action .= '<li> <a class="dropdown-item remove-item-btn">
                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                        </a>
                                    </li>';
                    }
                    
                    $action .= '</ul></div>';
            }
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'cities');
        return $dataTable
        ->rawColumns(['options','city','country','state','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'city' => ['required', 'max:155', Rule::unique('cities')->where(function ($query) use ($request) {
                return $query->where('state_id', '=', $request->state_id);
            })],
            'state_id'=>'required',
        ],
        [
            'city.required' => 'Please Enter City',
            'city.unique' => 'The City Name Has Already Been Taken',
            'city.max' => 'Maximum 255 Characters Allowed',
            'state_id.required' => 'Please Select State'
        ]);

        DB::beginTransaction();
        try
        {
            $city_data = City::create([
                'city' => $request->city,
                'state_id' => $request->state_id,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($city_data->save())
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
        $city_data = City::select([
            'cities.*',
            'countries.country_name',
            'states.state_code'
        ])
        ->leftJoin('states','states.id','=','cities.state_id')
        ->leftJoin('countries','countries.id','=','states.country_id')
        ->where('cities.id','=',$request->id)->first();

        if($city_data)
        {
            return response()->json([
                'city_data' => $city_data,
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
            'city' => ['required', 'max:155', Rule::unique('cities')->where(function ($query) use ($request) {
                return $query->where('state_id', '=', $request->state_id);
            })->ignore($request->id, 'id')],
            'state_id'=>'required',
        ],
        [
            'city.required' => 'Please Enter City',
            'city.max' => 'Maximum 255 Characters Allowed',
            'state_id.required' => 'Please Select State'
        ]);

        DB::beginTransaction();

        try
        {
            $city_data = City::where('id','=',$request->id)->update([
                'city' => $request->city,
                'state_id' => $request->state_id,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($city_data)
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
            $usage = DB::select('CALL city_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                DB::rollBack();
                $message = "You Can't Delete, City Is Used In " . $usage[0]->table_name . ".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            City::destroy($request->id);
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

    public function getRelationValues(Request $request)
    {
        $relData = State::select([
            'states.state_code',
            'countries.country_name'
        ])
        ->leftJoin('countries','countries.id','=','states.country_id')
        ->where('states.id','=',$request->state_id)
        ->first();

        if($relData != null)
        {
            return response()->json([
                'response_code' => '1',
                'relation_data' =>  $relData
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'relation_data' =>  '',
                'response_message' => 'No Relation Data Available!'
            ]);
        }
    }

    public function existsCity(Request $request)
    {
        if($request->term != "")
        {
            $fdCity = City::select('city')->where('city', 'LIKE', '%'.$request->term.'%')->groupBy('city')->get();
            if($fdCity != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdCity as $dsKey)
                {
                    $output .= '<li parent-id="city" list-id="city_list" class="list-group-item" tabindex="0">'.$dsKey->city.'</li>';
                } 
                $output .= '</ul>';

                return response()->json([
                    'cityList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No City available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'cityList' => '',
                'response_code' => 1,
            ]);
        }
    }
}