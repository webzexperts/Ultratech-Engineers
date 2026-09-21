<?php

namespace App\Http\Controllers;

use App\Models\Operator;
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
use App\Models\File;

class OperatorController extends Controller
{
    public function manage()
    {
        return view('manage.manage-operator');
    }

    public function operatorData(Request $request)
    {
        $assistantOperator =''; $testedbyOperator = ''; $radiographerOperator = ''; $signatureOperator = '';

        if($request->category == "assistant")
        {
            $assistantOperator = Operator::select('id', 'operator', 'category', 'designation', 'status', 'upload_sign')->where('category',$request->category)->where('status',1)->get();
        }
        else if($request->category == "tested_by")
        {
            $testedbyOperator = Operator::select('id', 'operator', 'category', 'designation', 'status', 'upload_sign')->where('category',$request->category)->where('status',1)->get();
        }
        else if($request->category == "radiographer")
        {
            $radiographerOperator = Operator::select('id', 'operator', 'category', 'designation', 'status', 'upload_sign')->where('category',$request->category)->where('status',1)->get();            
        }
        else if($request->category == "signature")
        {
            $signatureOperator = Operator::select('id', 'operator', 'category', 'designation', 'status', 'upload_sign')->where('category',$request->category)->where('status',1)->get();          
        }

        if($assistantOperator != "" || $testedbyOperator != "" || $radiographerOperator != "" || $signatureOperator != "" )
        {
            return response()->json([
                'assistantOperator' => $assistantOperator,
                'testedbyOperator' => $testedbyOperator,
                'radiographerOperator' => $radiographerOperator,
                'signatureOperator' => $signatureOperator,
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

    public function index(Operator $Operator, Request $request, DataTables $datatables)
    {
        $operator_data = Operator::select([
            'operatores.id',
            'operatores.operator',
            'operatores.category',
            'operatores.designation',
            'operatores.status',
            'operatores.last_by',
            'operatores.last_on',
            'operatores.created_by',
            'operatores.created_on'
        ]);

        $dataTable =DataTables::of($operator_data)
        ->editColumn('category', function($operator_data){ 
            if($operator_data->category != null){
                return ucwords(str_replace(","," , ",(str_replace("_"," ",$operator_data->category))));
            }else{
                return '';
            }
        })
        ->filterColumn('operatores.category', function($query, $keyword) {
            $sql = "REPLACE(REPLACE(category, '_', ' '), ',', ' , ') LIKE ?";
            $query->whereRaw($sql, ["%{$keyword}%"]);
        })
        ->editColumn('operator', function($operator_data){ 
            return Str::limit($operator_data->operator, 50);
        })
        ->editColumn('designation', function($operator_data){ 
            return Str::limit($operator_data->designation, 50);
        })
        ->editColumn('status', function($operator_data){
            return $operator_data->status == 1 ? 'Active':'Deactive';
        })
        ->filterColumn('operatores.status', function ($query, $keyword) {
            $lowerKeyword = strtolower(trim($keyword));
            if (str_starts_with('active', $lowerKeyword)) {
                $query->where('status', '=', 1);
            }
            elseif (str_starts_with('deactive', $lowerKeyword)) {
                $query->where('status', '=', 0);
            }
            else {
                $query->where('status', 'like', "%$keyword%");
            }
        })
        // ->filterColumn('operatores.status', function($query, $keyword) {
        //     $keyword = strtolower(trim($keyword));
        //     if (Str::startsWith($keyword, 'a')) {
        //         $query->where('status', 1);
        //     } elseif (Str::startsWith($keyword, 'd')) {
        //         $query->where('status', 0);
        //     } else {
        //         $query->where(function($q) use ($keyword) {
        //             $q->where('status', 1)->whereRaw('LOWER("active") LIKE ?', ["%{$keyword}%"])
        //             ->orWhere('status', 0)->whereRaw('LOWER("deactive") LIKE ?', ["%{$keyword}%"]);
        //         });
        //     }
        // })
        ->addColumn('options',function($operator_data){ 
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("operator", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_operator"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("operator", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }

            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'operatores');
        return $dataTable
        ->rawColumns(['options','operator','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'operator'=>'required|max:255',
            'category'=>'required',
            'status'=>'required'
        ],
        [
            'operator.required' => 'Please Enter Operator',
            'status.required' => 'Please Select Status',
            'operator.max' => 'Maximum 255 characters Allowed',
            'category.required' => 'Please Select Category'
        ]);

        DB::beginTransaction();
        try
        {
            $insertData = [
                'operator' => $request->operator,
                'status' => $request->status,
                'category' => $request->category,
                'designation' => $request->designation,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ];

            if($request->hasFile('upload_sign_file'))
            {
                $file = $request->file('upload_sign_file');
                $filename = 'sign_' . time() . "." . $file->getClientOriginalExtension();
                $file->storeAs('public/uploads', $filename);
                $insertData['upload_sign'] = 'storage/uploads/' . $filename;
                $insertData['image'] = file_get_contents($file->getRealPath());
            }

            $operator_data = Operator::create($insertData);
            DB::commit();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('store_success'),
            ]);
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
        $operator_data = $operator_data = Operator::select('id', 'operator', 'category', 'designation', 'status', 'upload_sign')->where('id','=',$request->id)->first();
        if($operator_data)
        {
            if(!empty($operator_data->upload_sign))
            {
                if(strpos($operator_data->upload_sign, 'storage/') === 0)
                {
                    $operator_data->image_url = asset('storage/' . substr($operator_data->upload_sign, 8));
                }
                else
                {
                    $operator_data->image_url = asset('storage/' . $operator_data->upload_sign);
                }
            }
            else
            {
                $operator_data->image_url = null;
            }

            return response()->json([
                'operator_data' => $operator_data,
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
            'operator'=>['required','max:255'],
            'category'=>'required',
            'status'=>'required'
        ],
        [
            'operator.required' => 'Please Enter Operator',
            'category.required' => 'Please Select Category',
            'status.required' => 'Please Select Status',
            'operator.max' => 'Maximum 255 characters Allowed'
        ]);

        DB::beginTransaction();
        try
        {
            $operator = Operator::find($request->id);
            if(!$operator)
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }

            $updateData = [
                'operator' => $request->operator,
                'status' => $request->status,
                'category' => $request->category,
                'designation' => $request->designation,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ];

            if($request->hasFile('upload_sign_file'))
            {
                if($operator->upload_sign && file_exists(public_path($operator->upload_sign)))
                {
                    unlink(public_path($operator->upload_sign));
                }

                $file = $request->file('upload_sign_file');
                $filename = 'sign_' . time() . "." . $file->getClientOriginalExtension();
                $file->storeAs('public/uploads', $filename);
                $updateData['upload_sign'] = 'storage/uploads/' . $filename;
                $updateData['image'] = file_get_contents($file->getRealPath());
            }
            else if($request->delete_image == "1")
            {
                if($operator->upload_sign && file_exists(public_path($operator->upload_sign)))
                {
                    unlink(public_path($operator->upload_sign));
                }
                $updateData['upload_sign'] = null;
                $updateData['image'] = null;
            }

            $operator->update($updateData);
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
            Operator::destroy($request->id);
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

    public function existsOperator(Request $request)
    {
        if($request->term != "")
        {
            $fdOperator = Operator::select('operator')->where('operator', 'LIKE', '%'.$request->term.'%')->groupBy('operator')->get();
            if($fdOperator != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdOperator as $dsKey)
                {
                    $output .= '<li parent-id="operator" list-id="operator_list" class="list-group-item" tabindex="0">'.$dsKey->operator.'</li>';
                } 
                $output .= '</ul>';

                return response()->json([
                    'operatorList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Operator available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'operatorList' => '',
                'response_code' => 1,
            ]);
        }
    }

    public function existsDesignation(Request $request)
    {
        if($request->term != "")
        {
            $fdDesignation = Operator::select('designation')->where('designation', 'LIKE', '%'.$request->term.'%')->groupBy('designation')->get();
            if($fdDesignation != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdDesignation as $dfKey)
                {
                    $output .= '<li parent-id="designation" list-id="designation_list" class="list-group-item" tabindex="0">'.$dfKey->designation.'</li>';
                } 
                $output .= '</ul>';

                return response()->json([
                    'designationList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Designation available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'designationList' => '',
                'response_code' => 1,
            ]);
        }
    }
}