<?php

namespace App\Http\Controllers;

use App\Models\AcceptanceStandard;
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

class AcceptanceStandardController extends Controller
{
    public function manage()
    {
        return view('manage.manage-acceptance_standard');
    }

    public function index(AcceptanceStandard $AcceptanceStandard, Request $request, DataTables $datatables)
    {
        $acceptance_standard_data = AcceptanceStandard::select([
            'acceptance_standards.id',
            'acceptance_standards.acceptance_standard',
            'acceptance_standards.status',
            'acceptance_standards.last_by',
            'acceptance_standards.last_on',
            'acceptance_standards.created_by',
            'acceptance_standards.created_on'
        ]);
    
        $dataTable =DataTables::of($acceptance_standard_data)
        // ->editColumn('status', function($bank_data){
        //     return $bank_data->status == "Active" ? "Active":"Deactive";
        // })
        // ->filterColumn('acceptance_standards.status', function ($query, $keyword) {
        //     $globalSearch = request()->input('search.value');
        //     $searchValue = $globalSearch != '' ? $globalSearch : $keyword;
        //     $lowerKeyword = strtolower(trim($searchValue));
        //     if ($lowerKeyword === 'active') {
        //         $searchStatus = 'active';
        //     }
        //     elseif ($lowerKeyword === 'deactive') {
        //         $searchStatus = 'deactive';
        //     }
        //     if (!empty($searchStatus)) {
        //         $query->where('acceptance_standards.status', '=', $searchStatus);
        //     } 
        //     else {
        //         $dbFormatKeyword = str_replace(' ', '_', $lowerKeyword);
        //         $query->where('acceptance_standards.status', 'like', "$dbFormatKeyword%");
        //     }
        // })
        // ->filterColumn('acceptance_standards.status', function($query, $keyword) {
        //     $keyword = strtolower(trim($keyword));
        //     if (Str::startsWith($keyword, 'a')) {
        //         $query->where('acceptance_standards.status', 'Active');
        //     } elseif (Str::startsWith($keyword, 'd')) {
        //         $query->where('acceptance_standards.status','Deactive');
        //     }
        // })
        ->addColumn('options',function($acceptance_standard_data){ 
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("acceptance_standard", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_acceptance_standard"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("acceptance_standard", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }

            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'acceptance_standards');
        return $dataTable
        ->rawColumns(['options','acceptance_standard','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'acceptance_standard'=>'required|max:255|unique:acceptance_standards',
        ],
        [
            'acceptance_standard.unique' => 'Acceptance Standard Has Already Been Taken',
            'acceptance_standard.required' => 'Enter Acceptance Standard',
            'acceptance_standard.max' => 'Maximum 255 Characters Allowed',
        ]);

        DB::beginTransaction();

        try
        {
            $acceptance_standard_data = AcceptanceStandard::create([
                'acceptance_standard' => $request->acceptance_standard,
                'status' => $request->status ?? 'Active',
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($acceptance_standard_data->save())
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
        $acceptance_standard_data = AcceptanceStandard::where('id','=',$request->id)->first();
        if($acceptance_standard_data)
        {
            return response()->json([
                'acceptance_standard_data' => $acceptance_standard_data,
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
            'acceptance_standard'=>['required','max:255',Rule::unique('acceptance_standards')->ignore($request->id, 'id')]
        ],
        [
            'acceptance_standard.required' => 'Enter Acceptance Standard',
            'acceptance_standard.max' => 'Maximum 255 Characters Allowed'
        ]);

        DB::beginTransaction();

        try
        {
            $acceptance_standard_data = AcceptanceStandard::where('id','=',$request->id)->update([
                'acceptance_standard' => $request->acceptance_standard,
                'status' => $request->status ?? 'Active',
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($acceptance_standard_data)
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
            $usage = DB::select('CALL acceptance_standard_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                $message = "You Can't Delete, Acceptance Standard Is Used In ". $usage[0]->table_name.".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }
            AcceptanceStandard::destroy($request->id);
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

    public function existsAcceptanceStandard(Request $request)
    {
        if($request->term != "")
        {
            $fdAcceptanceStandard = AcceptanceStandard::select('acceptance_standard')->where('acceptance_standard', 'LIKE', $request->term.'%')->groupBy('acceptance_standard')->get();
            if($fdAcceptanceStandard != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdAcceptanceStandard as $dsKey)
                {
                    $output .= '<li parent-id="acceptance_standard" list-id="acceptance_standard_list" class="list-group-item" tabindex="0">'.$dsKey->acceptance_standard.'</li>';
                } 
                $output .= '</ul>';
                return response()->json([
                    'acceptance_standardList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Acceptance Standard Available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'acceptance_standardList' => '',
                'response_code' => 1,
            ]);
        }
    }

    public function verifyAcceptanceStandard(Request $request)
    {
        if(!empty($request->acceptance_standard))
        {
            $name = $request->acceptance_standard;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = AcceptanceStandard::where('acceptance_standard',$name)->where('id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = AcceptanceStandard::where('acceptance_standard',$name)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'acceptance_standard_data_name' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Record already exists.',
                    // 'response_message' => 'The Acceptance Standard Name Has Already Been Taken',
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

    public function getAcceptanceStandards()
    {
        $standards = AcceptanceStandard::orderBy('acceptance_standard', 'ASC')->get();
        if($standards)
        {
            return response()->json([
                'acceptance_standards' => $standards,
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
}