<?php

namespace App\Http\Controllers;

use App\Models\FindingLevel;
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

class FindingLevelController extends Controller
{
    public function getFindingLevels()
    {
        $finding_levels = FindingLevel::orderBy('finding_level_name', 'ASC')->get();
        if($finding_levels)
        {
            return response()->json([
                'finding_levels' => $finding_levels,
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
        return view('manage.manage-finding_level');
    }

    public function index(FindingLevel $FindingLevel, Request $request, DataTables $datatables)
    {
        $finding_level_data = FindingLevel::select([
            'finding_level.finding_level_id as id',
            'finding_level.finding_level_name',
            'finding_level.created_on',
            'finding_level.created_by',
            'finding_level.last_by',
            'finding_level.last_on'
        ]);
        $dataTable = DataTables::of($finding_level_data)
        ->editColumn('finding_level_name', function($finding_level_data){
            return $finding_level_data->finding_level_name;
        })
        ->addColumn('options',function($finding_level_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("finding_level", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_finding_level"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("finding_level", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'finding_level');
        return $dataTable
        ->rawColumns(['options','finding_level_name','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'finding_level_name'=>'required|max:100|unique:finding_level,finding_level_name',
        ],
        [
            'finding_level_name.required' => 'Enter Finding Level',
            'finding_level_name.max' => 'Maximum 100 Characters Allowed',
            'finding_level_name.unique' => 'Finding Level Already Exists',
        ]);

        DB::beginTransaction();
        try
        {
            $finding_level_data = FindingLevel::create([
                'finding_level_name' => $request->finding_level_name,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($finding_level_data->save())
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
        $finding_level_data = FindingLevel::where('finding_level_id','=',$request->id)->first();
        if($finding_level_data)
        {
            return response()->json([
                'finding_level_data' => $finding_level_data,
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
            'finding_level_name'=>['required','max:100',Rule::unique('finding_level', 'finding_level_name')->ignore($request->id, 'finding_level_id')]
        ],
        [
            'finding_level_name.required' => 'Enter Finding Level',
            'finding_level_name.max' => 'Maximum 100 Characters Allowed',
            'finding_level_name.unique' => 'Finding Level Already Exists',
        ]);

        DB::beginTransaction();

        try
        {
            $finding_level_data = FindingLevel::where('finding_level_id','=',$request->id)->update([
                'finding_level_name' => $request->finding_level_name,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($finding_level_data)
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
            $usage = DB::select('CALL finding_level_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                DB::rollBack();
                $message = "You Can't Delete, Finding Level Is Used In " . $usage[0]->table_name . ".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            FindingLevel::destroy($request->id);
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

    public function verify(Request $request)
    {
        $query = FindingLevel::where('finding_level_name', $request->finding_level_name);

        if($request->filled('id'))
        {
            $query->where('finding_level_id', '!=', $request->id);
        }

        if($query->exists())
        {
            return response()->json([
                'response_code' => 1,
                'response_message' => 'Finding Level Already Exists',
            ]);
        }

        return response()->json([
            'response_code' => 0,
        ]);
    }

    public function existsFindingLevel(Request $request)
    {
        if($request->term != "")
        {
            $fdFindingLevel = FindingLevel::select('finding_level_name')->where('finding_level_name', 'LIKE', '%'.$request->term.'%')->groupBy('finding_level_name')->get();
            if($fdFindingLevel != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdFindingLevel as $dsKey)
                {
                    $output .= '<li parent-id="finding_level_name" list-id="finding_level_list" class="list-group-item" tabindex="0">'.$dsKey->finding_level_name.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'findingLevelList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Finding Level available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'findingLevelList' => '',
                'response_code' => 1,
            ]);
        }
    }
}
