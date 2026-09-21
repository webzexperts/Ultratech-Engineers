<?php

namespace App\Http\Controllers;

use App\Models\Material;
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

class MaterialController extends Controller
{
    public function manage()
    {
        return view('manage.manage-material');
    }

    public function materialData()
    {
        $materials = Material::all();
        if($materials)
        {
            return response()->json([
                'materials' => $materials,
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

    public function index(Material $Material, Request $request, DataTables $datatables)
    {
        $material_data = Material::select([
            'materials.id',
            'materials.material',
            'materials.created_on',
            'materials.created_by',
            'materials.last_by',
            'materials.last_on'
        ]);
        $dataTable = DataTables::of($material_data)
        ->editColumn('material', function($material_data){ 
            return ucfirst($material_data->material);
        })
        ->addColumn('options',function($material_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("material", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_material"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("material", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'materials');
        return $dataTable
        ->rawColumns(['options','material','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material'=>'required|max:255|unique:materials',
        ],
        [
            'material.required' => 'Please Enter Material',
            'material.max' => 'Maximum 255 Characters Allowed',
        ]);

        DB::beginTransaction();
        try
        {
            $material_data = Material::create([
                'material' => $request->material,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($material_data->save())
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
        $material_data = Material::where('id','=',$request->id)->first();
        if($material_data)
        {
            return response()->json([
                'material_data' => $material_data,
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
            'material'=>['required','max:255',Rule::unique('materials')->ignore($request->id, 'id')]
        ],
        [
            'material.required' => 'Please Enter Material',
            'material.max' => 'Maximum 255 Characters Allowed'
        ]);

        DB::beginTransaction();

        try
        {
            $material_data = Material::where('id','=',$request->id)->update([
                'material' => $request->material,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($material_data)
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
            $usage = DB::select('CALL meterial_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                $message = "You Can't Delete, Material Is Used In ". $usage[0]->table_name.".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }
            Material::destroy($request->id);
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

    public function existsMaterial(Request $request)
    {
        if($request->term != "")
        {
            $fdMaterial = Material::select('material')->where('material', 'LIKE', '%'.$request->term.'%')->groupBy('material')->get();
            if($fdMaterial != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdMaterial as $dsKey)
                {
                    $output .= '<li parent-id="material" list-id="material_list" class="list-group-item" tabindex="0">'.$dsKey->material.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'materialList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Material available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'materialList' => '',
                'response_code' => 1,
            ]);
        }
    }
}