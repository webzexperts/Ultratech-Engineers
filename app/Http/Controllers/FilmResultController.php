<?php

namespace App\Http\Controllers;

use App\Models\FilmResult;
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

class FilmResultController extends Controller
{
    /**
     * Allowed values for the result radio group (matches the enum column).
     */
    private $resultTypes = ['Accepted', 'Not Accepted', 'Repair', 'Reshoot', 'Retake'];

    public function getFilmResults()
    {
        $film_results = FilmResult::orderBy('film_result_name', 'ASC')->get();
        if($film_results)
        {
            return response()->json([
                'film_results' => $film_results,
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
        return view('manage.manage-film_result');
    }

    public function index(FilmResult $FilmResult, Request $request, DataTables $datatables)
    {
        $film_result_data = FilmResult::select([
            'film_result.film_result_id as id',
            'film_result.film_result_name',
            'film_result.film_result_type_fix',
            'film_result.created_on',
            'film_result.created_by',
            'film_result.last_by',
            'film_result.last_on'
        ]);
        $dataTable = DataTables::of($film_result_data)
        ->editColumn('film_result_name', function($film_result_data){
            return $film_result_data->film_result_name;
        })
        ->editColumn('film_result_type_fix', function($film_result_data){
            return $film_result_data->film_result_type_fix;
        })
        ->filterColumn('film_result.film_result_type_fix', function ($query, $keyword) {
            $globalSearch = request()->input('search.value');
            $searchValue = $globalSearch != '' ? $globalSearch : $keyword;
            $lowerKeyword = strtolower(trim($searchValue));
            $query->where('film_result.film_result_type_fix', 'like', "$lowerKeyword%");
        })

        ->addColumn('options',function($film_result_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("film_result", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_film_result"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("film_result", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'film_result');
        return $dataTable
        ->rawColumns(['options','film_result_name','film_result_type_fix','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'film_result_name'     => 'required|max:100|unique:film_result,film_result_name',
            'film_result_type_fix' => 'required|in:'.implode(',', $this->resultTypes),
        ],
        [
            'film_result_name.required'     => 'Please Enter Film Result',
            'film_result_name.max'          => 'Maximum 100 Characters Allowed',
            'film_result_name.unique'       => 'Film Result Already Exists',
            'film_result_type_fix.required' => 'Please Select Result',
            'film_result_type_fix.in'       => 'Please Select A Valid Result',
        ]);

        DB::beginTransaction();
        try
        {
            $film_result_data = FilmResult::create([
                'film_result_name'     => $request->film_result_name,
                'film_result_type_fix' => $request->film_result_type_fix,
                'company_id'           => Auth::user()->company_id,
                'created_on'           => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'           => Auth::user()->id
            ]);

            if($film_result_data->save())
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
        $film_result_data = FilmResult::where('film_result_id','=',$request->id)->first();
        $usage = DB::select('CALL film_result_used_list(?)', [$request->id]);
        if($usage != null && $usage != "")
        {
            $film_result_data->in_use = true;
        }
        else
        {
            $film_result_data->in_use = false;
        }
        if($film_result_data)
        {
            return response()->json([
                'film_result_data' => $film_result_data,
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
            'film_result_name'     => ['required','max:100',Rule::unique('film_result', 'film_result_name')->ignore($request->id, 'film_result_id')],
            'film_result_type_fix' => 'required|in:'.implode(',', $this->resultTypes),
        ],
        [
            'film_result_name.required'     => 'Please Enter Film Result',
            'film_result_name.max'          => 'Maximum 100 Characters Allowed',
            'film_result_name.unique'       => 'Film Result Already Exists',
            'film_result_type_fix.required' => 'Please Select Result',
            'film_result_type_fix.in'       => 'Please Select A Valid Result',
        ]);

        DB::beginTransaction();

        try
        {
            $film_result_data = FilmResult::where('film_result_id','=',$request->id)->update([
                'film_result_name'     => $request->film_result_name,
                'film_result_type_fix' => $request->film_result_type_fix,
                'last_on'              => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by'              => Auth::user()->id
            ]);

            if($film_result_data)
            {
                DB::commit();
                return response()->json([
                    'response_code' => '1',
                    'response_message' => getResponseMessage('update_success'),
                ]);
            }
            else
            {
                DB::commit();
                return response()->json([
                    'response_code' => '1',
                    'response_message' => getResponseMessage('update_success'),
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
            $usage = DB::select('CALL film_result_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                DB::rollBack();
                $message = "You Can't Delete, Film Result Is Used In " . $usage[0]->table_name . ".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            FilmResult::destroy($request->id);
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
        $query = FilmResult::where('film_result_name', $request->film_result_name);

        if($request->filled('id'))
        {
            $query->where('film_result_id', '!=', $request->id);
        }

        if($query->exists())
        {
            return response()->json([
                'response_code' => 1,
                'response_message' => 'Film Result Already Exists',
            ]);
        }

        return response()->json([
            'response_code' => 0,
        ]);
    }

    public function existsFilmResult(Request $request)
    {
        if($request->term != "")
        {
            $fdFilmResult = FilmResult::select('film_result_name')->where('film_result_name', 'LIKE', '%'.$request->term.'%')->groupBy('film_result_name')->get();
            if($fdFilmResult != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdFilmResult as $dsKey)
                {
                    $output .= '<li parent-id="film_result_name" list-id="film_result_name_list" class="list-group-item" tabindex="0">'.$dsKey->film_result_name.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'filmResultList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Film Result available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'filmResultList' => '',
                'response_code' => 1,
            ]);
        }
    }
}
