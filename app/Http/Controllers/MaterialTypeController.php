<?php

namespace App\Http\Controllers;

use App\Models\MaterialType;
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

class MaterialTypeController extends Controller
{
    public function getMaterialTypes()
    {
        $material_types = MaterialType::orderBy('material_type', 'ASC')->get();
        if($material_types)
        {
            return response()->json([
                'material_types' => $material_types,
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
        return view('manage.manage-material_type');
    }

    public function index(MaterialType $MaterialType, Request $request, DataTables $datatables)
    {
        $material_type_data = MaterialType::select([
            'material_type.material_type_id as id',
            'material_type.material_type',
            'material_type.created_on',
            'material_type.created_by',
            'material_type.last_by',
            'material_type.last_on'
        ]);
        $dataTable = DataTables::of($material_type_data)
        ->editColumn('material_type', function($material_type_data){
            return $material_type_data->material_type;
        })
        ->addColumn('options',function($material_type_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("material_type", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_material_type"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("material_type", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'material_type');
        return $dataTable
        ->rawColumns(['options','material_type','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_type'=>'required|max:100|unique:material_type',
        ],
        [
            'material_type.required' => 'Enter Material Type',
            'material_type.max' => 'Maximum 100 Characters Allowed',
        ]);

        DB::beginTransaction();
        try
        {
            $material_type_data = MaterialType::create([
                'material_type' => $request->material_type,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($material_type_data->save())
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
        $material_type_data = MaterialType::where('material_type_id','=',$request->id)->first();
        if($material_type_data)
        {
            return response()->json([
                'material_type_data' => $material_type_data,
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
            'material_type'=>['required','max:100',Rule::unique('material_type')->ignore($request->id, 'material_type_id')]
        ],
        [
            'material_type.required' => 'Enter Material Type',
            'material_type.max' => 'Maximum 100 Characters Allowed'
        ]);

        DB::beginTransaction();

        try
        {
            $material_type_data = MaterialType::where('material_type_id','=',$request->id)->update([
                'material_type' => $request->material_type,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($material_type_data)
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
            try {
                $usage = DB::select('CALL material_type_used_list(?)', [$request->id]);

                if (!empty($usage)) {
                    DB::rollBack();
                    $message = "You Can't Delete, Material Type Is Used In " . $usage[0]->table_name . ".";
                    return response()->json([
                        'response_code' => '0',
                        'response_message' => $message,
                    ]);
                }
            } catch (\Exception $spEx) {
                // If stored procedure is not created yet, continue with standard deletion
            }

            MaterialType::destroy($request->id);
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
        $query = MaterialType::where('material_type', $request->material_type);

        if($request->filled('id'))
        {
            $query->where('material_type_id', '!=', $request->id);
        }

        if($query->exists())
        {
            return response()->json([
                'response_code' => 1,
                'response_message' => 'Duplicate Material Type Found.',
            ]);
        }

        return response()->json([
            'response_code' => 0,
        ]);
    }

    public function existsMaterialType(Request $request)
    {
        if($request->term != "")
        {
            $fdMaterialType = MaterialType::select('material_type')->where('material_type', 'LIKE', '%'.$request->term.'%')->groupBy('material_type')->get();
            if($fdMaterialType != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdMaterialType as $dsKey)
                {
                    $output .= '<li parent-id="material_type" list-id="material_type_list" class="list-group-item" tabindex="0">'.$dsKey->material_type.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'materialTypeList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Material Type available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'materialTypeList' => '',
                'response_code' => 1,
            ]);
        }
    }
}
