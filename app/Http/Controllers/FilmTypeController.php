<?php

namespace App\Http\Controllers;

use App\Models\FilmType;
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

class FilmTypeController extends Controller
{
    public function getFilmTypes()
    {
        $film_types = FilmType::orderBy('film_type', 'ASC')->get();
        if($film_types)
        {
            return response()->json([
                'film_types' => $film_types,
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
        return view('manage.manage-film_type');
    }

    public function index(FilmType $FilmType, Request $request, DataTables $datatables)
    {
        $film_type_data = FilmType::select([
            'film_type.film_type_id as id',
            'film_type.film_type',
            'film_type.created_on',
            'film_type.created_by',
            'film_type.last_by',
            'film_type.last_on'
        ]);
        $dataTable = DataTables::of($film_type_data)
        ->editColumn('film_type', function($film_type_data){
            return $film_type_data->film_type;
        })
        ->addColumn('options',function($film_type_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("film_type", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_film_type"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("film_type", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'film_type');
        return $dataTable
        ->rawColumns(['options','film_type','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'film_type'=>'required|max:100|unique:film_type',
        ],
        [
            'film_type.required' => 'Enter Film Type',
            'film_type.max' => 'Maximum 100 Characters Allowed',
        ]);

        DB::beginTransaction();
        try
        {
            $film_type_data = FilmType::create([
                'film_type' => $request->film_type,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($film_type_data->save())
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
        $film_type_data = FilmType::where('film_type_id','=',$request->id)->first();
        if($film_type_data)
        {
            return response()->json([
                'film_type_data' => $film_type_data,
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
            'film_type'=>['required','max:100',Rule::unique('film_type')->ignore($request->id, 'film_type_id')]
        ],
        [
            'film_type.required' => 'Enter Film Type',
            'film_type.max' => 'Maximum 100 Characters Allowed'
        ]);

        DB::beginTransaction();

        try
        {
            $film_type_data = FilmType::where('film_type_id','=',$request->id)->update([
                'film_type' => $request->film_type,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($film_type_data)
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
            $usage = DB::select('CALL film_type_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                DB::rollBack();
                $message = "You Can't Delete, Film Type Is Used In " . $usage[0]->table_name . ".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            FilmType::destroy($request->id);
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
        $query = FilmType::where('film_type', $request->film_type);

        if($request->filled('id'))
        {
            $query->where('film_type_id', '!=', $request->id);
        }

        if($query->exists())
        {
            return response()->json([
                'response_code' => 1,
                'response_message' => 'Film Type Already Exists',
            ]);
        }

        return response()->json([
            'response_code' => 0,
        ]);
    }

    public function existsFilmType(Request $request)
    {
        if($request->term != "")
        {
            $fdFilmType = FilmType::select('film_type')->where('film_type', 'LIKE', '%'.$request->term.'%')->groupBy('film_type')->get();
            if($fdFilmType != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdFilmType as $dsKey)
                {
                    $output .= '<li parent-id="film_type" list-id="film_type_list" class="list-group-item" tabindex="0">'.$dsKey->film_type.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'filmTypeList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Film Type available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'filmTypeList' => '',
                'response_code' => 1,
            ]);
        }
    }
}
