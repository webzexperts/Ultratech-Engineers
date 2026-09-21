<?php

namespace App\Http\Controllers;

use App\Models\Finding;
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

class FindingController extends Controller
{
    public function getFindings()
    {
        $findings = Finding::orderBy('finding_name', 'ASC')->get();
        if($findings)
        {
            return response()->json([
                'findings' => $findings,
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
        return view('manage.manage-finding');
    }

    public function index(Finding $Finding, Request $request, DataTables $datatables)
    {
        $finding_data = Finding::select([
            'finding.finding_id as id',
            'finding.finding_name',
            'finding.abbreviation',
            'finding.required_finding_level',
            'finding.created_on',
            'finding.created_by',
            'finding.last_by',
            'finding.last_on'
        ]);
        $dataTable = DataTables::of($finding_data)
        ->editColumn('finding_name', function($finding_data){
            return $finding_data->finding_name;
        })
        ->editColumn('abbreviation', function($finding_data){
            return $finding_data->abbreviation;
        })
        // ->editColumn('required_finding_level', function($finding_data){
        //     if($finding_data->required_finding_level == 'Yes')
        //     {
        //         return '<span class="badge bg-success">Yes</span>';
        //     }
        //     return '<span class="badge bg-secondary">No</span>';
        // })
        ->addColumn('options',function($finding_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("finding", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_finding"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("finding", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'finding');
        return $dataTable
        ->rawColumns(['options','finding_name','abbreviation','required_finding_level','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'finding_name' => 'required|max:100|unique:finding,finding_name',
            'abbreviation' => 'nullable|max:100',
        ],
        [
            'finding_name.required' => 'Please Enter Finding',
            'finding_name.max'      => 'Maximum 100 Characters Allowed',
            'finding_name.unique'   => 'Finding Already Exists',
            'abbreviation.max'      => 'Maximum 100 Characters Allowed',
        ]);

        DB::beginTransaction();
        try
        {
            $finding_data = Finding::create([
                'finding_name'           => $request->finding_name,
                // 'abbreviation'           => $request->abbreviation,
                // 'required_finding_level' => $request->required_finding_level == 'Yes' ? 'Yes' : 'No',
                'abbreviation'           => null,
                'required_finding_level' => 'No',
                'company_id'             => Auth::user()->company_id,
                'created_on'             => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'             => Auth::user()->id
            ]);

            if($finding_data->save())
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
        $finding_data = Finding::where('finding_id','=',$request->id)->first();
        if($finding_data)
        {
            return response()->json([
                'finding_data' => $finding_data,
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
            'finding_name' => ['required','max:100',Rule::unique('finding', 'finding_name')->ignore($request->id, 'finding_id')],
            'abbreviation' => 'nullable|max:100',
        ],
        [
            'finding_name.required' => 'Please Enter Finding',
            'finding_name.max'      => 'Maximum 100 Characters Allowed',
            'finding_name.unique'   => 'Finding Already Exists',
            'abbreviation.max'      => 'Maximum 100 Characters Allowed',
        ]);

        DB::beginTransaction();

        try
        {
            Finding::where('finding_id','=',$request->id)->update([
                'finding_name'           => $request->finding_name,
                // 'abbreviation'           => $request->abbreviation,
                // 'required_finding_level' => $request->required_finding_level == 'Yes' ? 'Yes' : 'No',
                'abbreviation'           => null,
                'required_finding_level' => 'No',
                'last_on'                => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by'                => Auth::user()->id
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
            $usage = DB::select('CALL finding_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                DB::rollBack();
                $message = "You Can't Delete, Finding Is Used In " . $usage[0]->table_name . ".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            Finding::destroy($request->id);
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
        $query = Finding::where('finding_name', $request->finding_name);

        if($request->filled('id'))
        {
            $query->where('finding_id', '!=', $request->id);
        }

        if($query->exists())
        {
            return response()->json([
                'response_code' => 1,
                'response_message' => 'Finding Already Exists',
            ]);
        }

        return response()->json([
            'response_code' => 0,
        ]);
    }

    public function existsFinding(Request $request)
    {
        if($request->term != "")
        {
            $fdFinding = Finding::select('finding_name')->where('finding_name', 'LIKE', '%'.$request->term.'%')->groupBy('finding_name')->get();
            if($fdFinding != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdFinding as $dsKey)
                {
                    $output .= '<li parent-id="finding_name" list-id="finding_list" class="list-group-item" tabindex="0">'.$dsKey->finding_name.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'findingList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Finding available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'findingList' => '',
                'response_code' => 1,
            ]);
        }
    }
}
