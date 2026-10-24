<?php

namespace App\Http\Controllers;

use App\Models\TestMethod;
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

class TestMethodController extends Controller
{
    public function getTestMethods()
    {
        $test_methods = TestMethod::orderBy('test_method', 'ASC')->get();
        if($test_methods)
        {
            return response()->json([
                'test_methods' => $test_methods,
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
        return view('manage.manage-test_method');
    }

    public function index(TestMethod $TestMethod, Request $request, DataTables $datatables)
    {
        $test_method_data = TestMethod::select([
            'test_method.test_method_id as id',
            'test_method.test_method',
            'test_method.created_on',
            'test_method.created_by',
            'test_method.last_by',
            'test_method.last_on'
        ]);
        $dataTable = DataTables::of($test_method_data)
        ->editColumn('test_method', function($test_method_data){
            return $test_method_data->test_method;
        })
        ->addColumn('options',function($test_method_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("test_method", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_test_method"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("test_method", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'test_method');
        return $dataTable
        ->rawColumns(['options','test_method','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'test_method'=>'required|max:150|unique:test_method',
        ],
        [
            'test_method.required' => 'Enter Test Method',
            'test_method.max' => 'Maximum 150 Characters Allowed',
            'test_method.unique' => 'Duplicate Test Method Found.',
        ]);

        DB::beginTransaction();
        try
        {
            $test_method_data = TestMethod::create([
                'test_method' => $request->test_method,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($test_method_data->save())
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
        $test_method_data = TestMethod::where('test_method_id','=',$request->id)->first();
        if($test_method_data)
        {
            return response()->json([
                'test_method_data' => $test_method_data,
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
            'test_method'=>['required','max:150',Rule::unique('test_method')->ignore($request->id, 'test_method_id')]
        ],
        [
            'test_method.required' => 'Enter Test Method',
            'test_method.max' => 'Maximum 150 Characters Allowed',
            'test_method.unique' => 'Duplicate Test Method Found.'
        ]);

        DB::beginTransaction();

        try
        {
            $test_method_data = TestMethod::where('test_method_id','=',$request->id)->update([
                'test_method' => $request->test_method,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($test_method_data)
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
            $usage = DB::select('CALL test_method_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                DB::rollBack();
                $message = "You Can't Delete, Test Method Is Used In " . $usage[0]->table_name . ".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            TestMethod::destroy($request->id);
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
        $query = TestMethod::where('test_method', $request->test_method);

        if($request->filled('id'))
        {
            $query->where('test_method_id', '!=', $request->id);
        }

        if($query->exists())
        {
            return response()->json([
                'response_code' => 1,
                'response_message' => 'Duplicate Test Method Found.',
            ]);
        }

        return response()->json([
            'response_code' => 0,
        ]);
    }

    public function existsTestMethod(Request $request)
    {
        if($request->term != "")
        {
            $fdTestMethod = TestMethod::select('test_method')->where('test_method', 'LIKE', '%'.$request->term.'%')->groupBy('test_method')->get();
            if($fdTestMethod != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdTestMethod as $dsKey)
                {
                    $output .= '<li parent-id="test_method" list-id="test_method_list" class="list-group-item" tabindex="0">'.$dsKey->test_method.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'testMethodList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Test Method available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'testMethodList' => '',
                'response_code' => 1,
            ]);
        }
    }
}
