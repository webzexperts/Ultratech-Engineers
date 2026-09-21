<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\State;
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
use App\Imports\ImportCountry;

class CountryController extends Controller
{
    public function manage()
    {
        return view('manage.manage-country');
    }

    public function getCountryData()
    {
        $countries = Country::orderBy('country_name', 'ASC')->get();
        if($countries)
        {
            return response()->json([
                'countries' => $countries,
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

    public function index(Country $Country, Request $request, DataTables $datatables)
    {
        $country_data = Country::select([
            'countries.id',
            'countries.country_name',
            'countries.last_by',
            'countries.last_on',
            'countries.created_by',
            'countries.created_on'
        ]);
        $dataTable =DataTables::of($country_data)
        ->addColumn('options',function($country_data){
                $action = "";
            if ($country_data->id != 1) {
                $action .= '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

              
                    if (hasAccess("country", "edit")) {
                        $action .= '<li><a class="dropdown-item edit-item-btn edit_country"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                    }
                
                    if (hasAccess("country", "delete")) {
                        $action .= '<li> <a class="dropdown-item remove-item-btn">
                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                        </a>
                                    </li>';
                    }
                    $action .= '</ul></div>';
            }
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'countries');
        return $dataTable
        ->rawColumns(['options','country_name','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'country_name'=>'required|max:255|unique:countries',
        ],
        [
            'country_name.unique' => 'Country Has Already Been Taken',
            'country_name.required' => 'Please Enter Country',
            'country_name.max' => 'Maximum 255 Characters Allowed',
        ]);

        DB::beginTransaction();
        try
        {
            $country_data = Country::create([
                'country_name' => $request->country_name,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($country_data->save())
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
        $country_data = Country::select('id','country_name')->where('id','=',$request->id)->first();

        if($country_data)
        {
            return response()->json([
                'country' => $country_data,
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
            'country_name'=>['required','max:255',Rule::unique('countries')->ignore($request->id, 'id')]
        ],
        [
            'country_name.required' => 'Please Enter Country',
            'country_name.max' => 'Maximum 255 Characters Allowed'
        ]);

        DB::beginTransaction();

        try
        {
            $country_data = Country::where('id','=',$request->id)->update([
                'country_name' => $request->country_name,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($country_data)
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
        if($request->id == "1")
        {
            return response()->json([
                'response_code' => "0",
                'response_message' => "This Is Used For Reference, You Can't Delete",
            ]);
        }

        DB::beginTransaction();
        try
        {
            $usage = DB::select('CALL country_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                DB::rollBack();
                $message = "You Can't Delete, Country Is Used In " . $usage[0]->table_name . ".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            Country::destroy($request->id);
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

    public function existsCountry(Request $request)
    {
        if($request->term != "")
        {
            $fdCountry = Country::select('country_name')->where('country_name', 'LIKE', $request->term.'%')->groupBy('country_name')->get();
            if($fdCountry != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdCountry as $dsKey)
                {
                    $output .= '<li parent-id="country_name" list-id="country_name_list" class="list-group-item" tabindex="0">'.$dsKey->country_name.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'countryList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Country Available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'countryList' => '',
                'response_code' => 1,
            ]);
        }
    }

    public function verifyCountry(Request $request)
    {
        if(!empty($request->country_name))
        {
            $name = $request->country_name;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = Country::where('country_name',$name)->where('id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = Country::where('country_name',$name)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'country_name' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Record already exists.',
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

    public function importview()
    {
        return view('import.import_country');
    }
 
    public function importCountry()
    {
        Excel::import(new ImportCountry,request()->file('file'));
        return redirect()->back();
    }
}