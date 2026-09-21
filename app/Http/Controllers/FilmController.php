<?php

namespace App\Http\Controllers;

use App\Models\Film;
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

class FilmController extends Controller
{
    public function getFilms()
    {
        $films = Film::where('status', 'Active')->orderBy('film_size_inch', 'ASC')->get();
        if($films)
        {
            return response()->json([
                'films' => $films,
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
        return view('manage.manage-film');
    }

    public function index(Film $Film, Request $request, DataTables $datatables)
    {
        $film_data = Film::select([
            'film.film_id as id',
            'film.film_size_inch',
            'film.length_inch',
            'film.width_inch',
            'film.sq_in',
            'film.film_size_cm',
            'film.length_cm',
            'film.width_cm',
            'film.sq_cm',
            'film.status',
            'film.created_on',
            'film.created_by',
            'film.last_by',
            'film.last_on'
        ]);
        $dataTable = DataTables::of($film_data)
        ->editColumn('film_size_inch', function($film_data){
            return $film_data->film_size_inch;
        })
        ->editColumn('status', function($film_data){
            return $film_data->status == 'Active' ? 'Active':'Deactive';
        })
        ->filterColumn('film.status', function ($query, $keyword) {
            $globalSearch = request()->input('search.value');
            $searchValue = $globalSearch != '' ? $globalSearch : $keyword;
            $lowerKeyword = strtolower(trim($searchValue));
            if ($lowerKeyword === 'active') {
                $searchStatus = 'active';
            }
            elseif ($lowerKeyword === 'deactive') {
                $searchStatus = 'deactive';
            }
            if (!empty($searchStatus)) {
                $query->where('film.status', '=', $searchStatus);
            }
            else {
                $dbFormatKeyword = str_replace(' ', '_', $lowerKeyword);
                $query->where('film.status', 'like', "$dbFormatKeyword%");
            }
        })
        ->addColumn('options',function($film_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("film", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-film"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("film", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'film');
        return $dataTable
        ->rawColumns(['options','film_size_inch','status','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'film_size_inch' => 'required|max:100|unique:film',
            'length_inch'    => 'required|numeric',
            'width_inch'     => 'required|numeric',
            'film_size_cm'   => 'required|max:100|unique:film,film_size_cm',
            'status'         => 'required|in:Active,Deactive',
        ],
        [
            'film_size_inch.required' => 'Enter Film Size (inch)',
            'film_size_inch.max'      => 'Maximum 100 Characters Allowed',
            'film_size_inch.unique'   => 'Film Size (inch) Already Exists',
            'length_inch.required'    => 'Enter Length (inch)',
            'length_inch.numeric'     => 'Length (inch) Must Be Numeric',
            'width_inch.required'     => 'Enter Width (inch)',
            'width_inch.numeric'      => 'Width (inch) Must Be Numeric',
            'film_size_cm.required'   => 'Enter Film Size (cm)',
            'film_size_cm.max'        => 'Maximum 100 Characters Allowed',
            'film_size_cm.unique'     => 'Film Size (cm) Already Exists',
            'status.required'         => 'Select Status',
        ]);

        DB::beginTransaction();
        try
        {
            // Auto-calculated fields (server is the source of truth)
            $length_inch = (float) $request->length_inch;
            $width_inch  = (float) $request->width_inch;
            $sq_in       = round($length_inch * $width_inch, 2);
            $length_cm   = round($length_inch * 2.54, 2);
            $width_cm    = round($width_inch * 2.54, 2);
            $sq_cm       = round($length_cm * $width_cm, 2);

            $film_size_inch = $request->filled('film_size_inch') ? $request->film_size_inch : ($length_inch . ' X ' . $width_inch);
            $film_size_cm   = $request->filled('film_size_cm') ? $request->film_size_cm : ($length_cm . ' X ' . $width_cm);

            $film_data = Film::create([
                'film_size_inch' => $film_size_inch,
                'length_inch'    => round($length_inch, 2),
                'width_inch'     => round($width_inch, 2),
                'sq_in'          => $sq_in,
                'film_size_cm'   => $film_size_cm,
                'length_cm'      => $length_cm,
                'width_cm'       => $width_cm,
                'sq_cm'          => $sq_cm,
                'status'         => $request->status,
                'company_id'     => Auth::user()->company_id,
                'created_on'     => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'     => Auth::user()->id
            ]);

            if($film_data->save())
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
        $film_data = Film::where('film_id','=',$request->id)->first();
        if($film_data)
        {
            return response()->json([
                'film_data' => $film_data,
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
            'film_size_inch' => ['required','max:100',Rule::unique('film')->ignore($request->id, 'film_id')],
            'length_inch'    => 'required|numeric',
            'width_inch'     => 'required|numeric',
            'film_size_cm'   => ['required','max:100',Rule::unique('film', 'film_size_cm')->ignore($request->id, 'film_id')],
            'status'         => 'required|in:Active,Deactive',
        ],
        [
            'film_size_inch.required' => 'Enter Film Size (inch)',
            'film_size_inch.max'      => 'Maximum 100 Characters Allowed',
            'film_size_inch.unique'   => 'Film Size (inch) Already Exists',
            'length_inch.required'    => 'Enter Length (inch)',
            'length_inch.numeric'     => 'Length (inch) Must Be Numeric',
            'width_inch.required'     => 'Enter Width (inch)',
            'width_inch.numeric'      => 'Width (inch) Must Be Numeric',
            'film_size_cm.required'   => 'Enter Film Size (cm)',
            'film_size_cm.max'        => 'Maximum 100 Characters Allowed',
            'film_size_cm.unique'     => 'Film Size (cm) Already Exists',
            'status.required'         => 'Select Status',
        ]);

        DB::beginTransaction();

        try
        {
            // Auto-calculated fields (server is the source of truth)
            $length_inch = (float) $request->length_inch;
            $width_inch  = (float) $request->width_inch;
            $sq_in       = round($length_inch * $width_inch, 2);
            $length_cm   = round($length_inch * 2.54, 2);
            $width_cm    = round($width_inch * 2.54, 2);
            $sq_cm       = round($length_cm * $width_cm, 2);

            $film_size_inch = $request->filled('film_size_inch') ? $request->film_size_inch : ($length_inch . ' X ' . $width_inch);
            $film_size_cm   = $request->filled('film_size_cm') ? $request->film_size_cm : ($length_cm . ' X ' . $width_cm);

            $film_data = Film::where('film_id','=',$request->id)->update([
                'film_size_inch' => $film_size_inch,
                'length_inch'    => round($length_inch, 2),
                'width_inch'     => round($width_inch, 2),
                'sq_in'          => $sq_in,
                'film_size_cm'   => $film_size_cm,
                'length_cm'      => $length_cm,
                'width_cm'       => $width_cm,
                'sq_cm'          => $sq_cm,
                'status'         => $request->status,
                'last_on'        => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by'        => Auth::user()->id
            ]);

            if($film_data)
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
            $usage = DB::select('CALL film_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                DB::rollBack();
                $message = "You Can't Delete, Film Is Used In " . $usage[0]->table_name . ".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            Film::destroy($request->id);
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
        $query = Film::where('film_size_inch', $request->film_size_inch);

        if($request->filled('id'))
        {
            $query->where('film_id', '!=', $request->id);
        }

        if($query->exists())
        {
            return response()->json([
                'response_code' => 1,
                'response_message' => 'Film Size (inch) Already Exists',
            ]);
        }

        return response()->json([
            'response_code' => 0,
        ]);
    }

    public function existsFilm(Request $request)
    {
        if($request->term != "")
        {
            $fdFilm = Film::select('film_size_inch')->where('film_size_inch', 'LIKE', '%'.$request->term.'%')->groupBy('film_size_inch')->get();
            if($fdFilm != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdFilm as $dsKey)
                {
                    $output .= '<li parent-id="film_size_inch" list-id="film_list" class="list-group-item" tabindex="0">'.$dsKey->film_size_inch.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'filmList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Film available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'filmList' => '',
                'response_code' => 1,
            ]);
        }
    }
}
