<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\NABLConfiguration;
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

class NABLConfigurationController extends Controller
{
    public function manage()
    {
        return view('manage.manage-nabl_configuration');
    }

    public function index(NABLConfiguration $nabl_data, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $nabl_data = NABLConfiguration::select([
            'nabl_configurations.nabl_id',
            'nabl_configurations.nabl_location',
            'nabl_configurations.tc_no',
            // 'nabl_configurations.location_no',
            // 'nabl_configurations.nabl_type',
            'nabl_configurations.status',
            'nabl_configurations.created_on',
            'nabl_configurations.created_by',
            'nabl_configurations.last_by',
            'nabl_configurations.last_on'
        ])
        ->where('nabl_configurations.company_id', Auth::user()->company_id);
        $dataTable =DataTables::of($nabl_data)
        // ->editColumn('nabl_type', function($nabl_data){
        //     if ($nabl_data->nabl_type != null) {
        //         $nabl_type = '';
        //         if($nabl_data->nabl_type == "F") {
        //             $nabl_type = "F - Full";
        //         } else if($nabl_data->nabl_type == "P") {
        //             $nabl_type = "P - Partial";
        //         }
        //         return $nabl_type;
        //     } else {
        //         return '';
        //     }
        // })
        ->filterColumn('nabl_configurations.status', function ($query, $keyword) {
            $globalSearch = request()->input('search.value');
            $searchValue = $globalSearch != '' ? $globalSearch : $keyword;
            $lowerKeyword = strtolower(trim($searchValue));
            if ($lowerKeyword === 'active') {
                $searchStatus = 'active';
            }
            elseif ($lowerKeyword === 'deactive') {
                $searchStatus = 'deactive';
            }
            if (!empty($searchStatus)) {
                $query->where('nabl_configurations.status', '=', $searchStatus);
            } 
            else {
                $dbFormatKeyword = str_replace(' ', '_', $lowerKeyword);
                $query->where('nabl_configurations.status', 'like', "$dbFormatKeyword%");
            }
        })
        ->addColumn('options',function($nabl_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("nabl_configuration", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_nabl_configuration"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("nabl_configuration", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'nabl_configurations');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $year_data = getCurrentYearData();
        DB::beginTransaction();
        try
        {
            $nabl_data = NABLConfiguration::create([
                'nabl_location' => $request->nabl_location,
                'tc_no'         => $request->tc_no,
                'location_no'   => 0,
                'nabl_type'     => $request->nabl_type,
                'status'        => $request->status,
                'company_id'    => Auth::user()->company_id,
                'created_on'    => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'    => Auth::user()->id
            ]);

            if($nabl_data->save())
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
        // dd($request->all());
        $nabl_data = NABLConfiguration::where('nabl_id','=',$request->id)->first();
        // dd($nabl_data);
        if($nabl_data)
        {
            return response()->json([
                'nabl_data' => $nabl_data,
                'response_code' => '1',
                'response_message' => '',
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists.',
            ]);
        }
    }

    public function update(Request $request)
    {
        $year_data = getCurrentYearData();
        // dd($request->all());
        DB::beginTransaction();
        try
        {
            $nabl_data = NABLConfiguration::where('nabl_id','=',$request->id)->update([
                'nabl_location' => $request->nabl_location,
                'tc_no'         => $request->tc_no,
                // 'location_no'   => $request->location_no,
                // 'nabl_type'     => $request->nabl_type,
                'status'        => $request->status,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($nabl_data)
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
            NABLConfiguration::destroy($request->id);
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
                $error_msg = "This Is Used Somewhere, You Can't Delete.";
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

}