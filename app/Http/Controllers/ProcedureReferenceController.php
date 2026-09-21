<?php

namespace App\Http\Controllers;

use App\Models\ProcedureReference;
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

class ProcedureReferenceController extends Controller
{
    public function getProcedureReferences()
    {
        $procedure_references = ProcedureReference::orderBy('procedure_reference', 'ASC')->get();
        if($procedure_references)
        {
            return response()->json([
                'procedure_references' => $procedure_references,
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
        return view('manage.manage-procedure_reference');
    }

    public function index(ProcedureReference $ProcedureReference, Request $request, DataTables $datatables)
    {
        $procedure_reference_data = ProcedureReference::select([
            'procedure_reference.procedure_reference_id as id',
            'procedure_reference.procedure_reference',
            'procedure_reference.created_on',
            'procedure_reference.created_by',
            'procedure_reference.last_by',
            'procedure_reference.last_on'
        ]);
        $dataTable = DataTables::of($procedure_reference_data)
        ->editColumn('procedure_reference', function($procedure_reference_data){
            return $procedure_reference_data->procedure_reference;
        })
        ->addColumn('options',function($procedure_reference_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("procedure_reference", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_procedure_reference"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("procedure_reference", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'procedure_reference');
        return $dataTable
        ->rawColumns(['options','procedure_reference','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'procedure_reference'=>'required|max:100|unique:procedure_reference',
        ],
        [
            'procedure_reference.required' => 'Enter Procedure Reference',
            'procedure_reference.max' => 'Maximum 100 Characters Allowed',
        ]);

        DB::beginTransaction();
        try
        {
            $procedure_reference_data = ProcedureReference::create([
                'procedure_reference' => $request->procedure_reference,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($procedure_reference_data->save())
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
        $procedure_reference_data = ProcedureReference::where('procedure_reference_id','=',$request->id)->first();
        if($procedure_reference_data)
        {
            return response()->json([
                'procedure_reference_data' => $procedure_reference_data,
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
            'procedure_reference'=>['required','max:100',Rule::unique('procedure_reference')->ignore($request->id, 'procedure_reference_id')]
        ],
        [
            'procedure_reference.required' => 'Enter Procedure Reference',
            'procedure_reference.max' => 'Maximum 100 Characters Allowed'
        ]);

        DB::beginTransaction();

        try
        {
            $procedure_reference_data = ProcedureReference::where('procedure_reference_id','=',$request->id)->update([
                'procedure_reference' => $request->procedure_reference,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($procedure_reference_data)
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
            $usage = DB::select('CALL procedure_reference_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                $message = "You Can't Delete, Procedure Reference Is Used In ". $usage[0]->table_name.".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }
            ProcedureReference::destroy($request->id);
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        }
        catch(\Exception $e)
        {
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

    // public function verify(Request $request)
    // {
    //     $query = ProcedureReference::where('procedure_reference', $request->procedure_reference);

    //     if($request->filled('id'))
    //     {
    //         $query->where('procedure_reference_id', '!=', $request->id);
    //     }

    //     if($query->exists())
    //     {
    //         return response()->json([
    //             'response_code' => 1,
    //             'response_message' => 'Procedure Reference Already Exists',
    //         ]);
    //     }

    //     return response()->json([
    //         'response_code' => 0,
    //     ]);
    // }

    public function existsProcedureReference(Request $request)
    {
        if($request->term != "")
        {
            $fdProcedureReference = ProcedureReference::select('procedure_reference')->where('procedure_reference', 'LIKE', '%'.$request->term.'%')->groupBy('procedure_reference')->get();
            if($fdProcedureReference != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdProcedureReference as $dsKey)
                {
                    $output .= '<li parent-id="procedure_reference" list-id="procedure_reference_list" class="list-group-item" tabindex="0">'.$dsKey->procedure_reference.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'procedureReferenceList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Procedure Reference available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'procedureReferenceList' => '',
                'response_code' => 1,
            ]);
        }
    }
}
