<?php

namespace App\Http\Controllers;

use App\Models\FilmBrand;
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

class FilmBrandController extends Controller
{
    public function getFilmBrands()
    {
        $film_brands = FilmBrand::orderBy('film_brand', 'ASC')->get();
        if($film_brands)
        {
            return response()->json([
                'film_brands' => $film_brands,
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
        return view('manage.manage-film_brand');
    }

    public function index(FilmBrand $FilmBrand, Request $request, DataTables $datatables)
    {
        $film_brand_data = FilmBrand::select([
            'film_brand.film_brand_id as id',
            'film_brand.film_brand',
            'film_brand.created_on',
            'film_brand.created_by',
            'film_brand.last_by',
            'film_brand.last_on'
        ]);
        $dataTable = DataTables::of($film_brand_data)
        ->editColumn('film_brand', function($film_brand_data){
            return $film_brand_data->film_brand;
        })
        ->addColumn('options',function($film_brand_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("film_brand", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_film_brand"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("film_brand", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'film_brand');
        return $dataTable
        ->rawColumns(['options','film_brand','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'film_brand'=>'required|max:100|unique:film_brand',
        ],
        [
            'film_brand.required' => 'Enter Film Brand',
            'film_brand.max' => 'Maximum 100 Characters Allowed',
        ]);

        DB::beginTransaction();
        try
        {
            $film_brand_data = FilmBrand::create([
                'film_brand' => $request->film_brand,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($film_brand_data->save())
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
        $film_brand_data = FilmBrand::where('film_brand_id','=',$request->id)->first();
        if($film_brand_data)
        {
            return response()->json([
                'film_brand_data' => $film_brand_data,
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
            'film_brand'=>['required','max:100',Rule::unique('film_brand')->ignore($request->id, 'film_brand_id')]
        ],
        [
            'film_brand.required' => 'Enter Film Brand',
            'film_brand.max' => 'Maximum 100 Characters Allowed'
        ]);

        DB::beginTransaction();

        try
        {
            $film_brand_data = FilmBrand::where('film_brand_id','=',$request->id)->update([
                'film_brand' => $request->film_brand,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($film_brand_data)
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
            $usage = DB::select('CALL film_brand_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                DB::rollBack();
                $message = "You Can't Delete, Film Brand Is Used In " . $usage[0]->table_name . ".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            FilmBrand::destroy($request->id);
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

    // public function verify(Request $request)
    // {
    //     $query = FilmBrand::where('film_brand', $request->film_brand);

    //     if($request->filled('id'))
    //     {
    //         $query->where('film_brand_id', '!=', $request->id);
    //     }

    //     if($query->exists())
    //     {
    //         return response()->json([
    //             'response_code' => 1,
    //             'response_message' => 'Film Brand Already Exists',
    //         ]);
    //     }

    //     return response()->json([
    //         'response_code' => 0,
    //     ]);
    // }

    public function existsFilmBrand(Request $request)
    {
        if($request->term != "")
        {
            $fdFilmBrand = FilmBrand::select('film_brand')->where('film_brand', 'LIKE', '%'.$request->term.'%')->groupBy('film_brand')->get();
            if($fdFilmBrand != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdFilmBrand as $dsKey)
                {
                    $output .= '<li parent-id="film_brand" list-id="film_brand_list" class="list-group-item" tabindex="0">'.$dsKey->film_brand.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'filmBrandList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Film Brand available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'filmBrandList' => '',
                'response_code' => 1,
            ]);
        }
    }
}
