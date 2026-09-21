<?php

namespace App\Http\Controllers;

use App\Models\AreaOfCoverage;
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

class AreaOfCoverageController extends Controller
{
    public function getAreaOfCoverages()
    {
        $area_of_coverages = AreaOfCoverage::orderBy('area_of_coverage', 'ASC')->get();
        if($area_of_coverages)
        {
            return response()->json([
                'area_of_coverages' => $area_of_coverages,
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
        return view('manage.manage-area_of_coverage');
    }

    public function index(AreaOfCoverage $AreaOfCoverage, Request $request, DataTables $datatables)
    {
        $area_of_coverage_data = AreaOfCoverage::select([
            'area_of_coverage.area_of_coverage_id as id',
            'area_of_coverage.area_of_coverage',
            'area_of_coverage.created_on',
            'area_of_coverage.created_by',
            'area_of_coverage.last_by',
            'area_of_coverage.last_on'
        ]);
        $dataTable = DataTables::of($area_of_coverage_data)
        ->editColumn('area_of_coverage', function($area_of_coverage_data){
            return $area_of_coverage_data->area_of_coverage;
        })
        ->addColumn('options',function($area_of_coverage_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("area_of_coverage", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_area_of_coverage"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("area_of_coverage", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'area_of_coverage');
        return $dataTable
        ->rawColumns(['options','area_of_coverage','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'area_of_coverage'=>'required|max:100|unique:area_of_coverage',
        ],
        [
            'area_of_coverage.required' => 'Enter Area of Coverage',
            'area_of_coverage.max' => 'Maximum 100 Characters Allowed',
        ]);

        DB::beginTransaction();
        try
        {
            $area_of_coverage_data = AreaOfCoverage::create([
                'area_of_coverage' => $request->area_of_coverage,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($area_of_coverage_data->save())
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
        $area_of_coverage_data = AreaOfCoverage::where('area_of_coverage_id','=',$request->id)->first();
        if($area_of_coverage_data)
        {
            return response()->json([
                'area_of_coverage_data' => $area_of_coverage_data,
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
            'area_of_coverage'=>['required','max:100',Rule::unique('area_of_coverage')->ignore($request->id, 'area_of_coverage_id')]
        ],
        [
            'area_of_coverage.required' => 'Enter Area of Coverage',
            'area_of_coverage.max' => 'Maximum 100 Characters Allowed'
        ]);

        DB::beginTransaction();

        try
        {
            $area_of_coverage_data = AreaOfCoverage::where('area_of_coverage_id','=',$request->id)->update([
                'area_of_coverage' => $request->area_of_coverage,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($area_of_coverage_data)
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
            $usage = DB::select('CALL area_of_coverage_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                $message = "You Can't Delete, Area of Coverage Is Used In ". $usage[0]->table_name.".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }
            AreaOfCoverage::destroy($request->id);
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
    //     $query = AreaOfCoverage::where('area_of_coverage', $request->area_of_coverage);

    //     if($request->filled('id'))
    //     {
    //         $query->where('area_of_coverage_id', '!=', $request->id);
    //     }

    //     if($query->exists())
    //     {
    //         return response()->json([
    //             'response_code' => 1,
    //             'response_message' => 'Area of Coverage Already Exists',
    //         ]);
    //     }

    //     return response()->json([
    //         'response_code' => 0,
    //     ]);
    // }

    public function existsAreaOfCoverage(Request $request)
    {
        if($request->term != "")
        {
            $fdAreaOfCoverage = AreaOfCoverage::select('area_of_coverage')->where('area_of_coverage', 'LIKE', '%'.$request->term.'%')->groupBy('area_of_coverage')->get();
            if($fdAreaOfCoverage != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdAreaOfCoverage as $dsKey)
                {
                    $output .= '<li parent-id="area_of_coverage" list-id="area_of_coverage_list" class="list-group-item" tabindex="0">'.$dsKey->area_of_coverage.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'areaOfCoverageList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Area of Coverage available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'areaOfCoverageList' => '',
                'response_code' => 1,
            ]);
        }
    }
}
