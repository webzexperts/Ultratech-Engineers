<?php

namespace App\Http\Controllers;

use App\Models\Reason;
use App\Models\ItemReturnSlipDetails;
use App\Models\FeasibilityReview;
use App\Models\InquiryShortClose;
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

class ReasonController extends Controller
{
    public function manage()
    {
        return view('manage.manage-reason');
    }

    public function index(Reason $reason_data, Request $request, DataTables $datatables)
    {
        $reason_data = Reason::select([
            'reason.id',
            'reason.reason_type',
            'reason.reason_name',
            'reason.last_by',
            'reason.last_on',
            'reason.created_by',
            'reason.created_on'
        ]);
        $dataTable = DataTables::of($reason_data)
        ->editColumn('reason_name', function($reason_data){
            return $reason_data->reason_name;
        })
        ->addColumn('options',function($reason_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("reason", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_reason"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("reason", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'reason');
        return $dataTable
        ->rawColumns(['options','reason_type','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $reason_data = Reason::create([
                'reason_type' => $request->reason_type,
                'reason_name' => $request->reason_name,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($reason_data->save())
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
        $reason_data = Reason::where('id','=',$request->id)->first();

        $reason_data_in_use =  DB::select('CALL reason_master_used_list(?)', [$request->id]);
        $reason_data->in_use = !empty($reason_data_in_use) && isset($reason_data_in_use[0]);

        if($reason_data)
        {
            return response()->json([
                'reason_data' => $reason_data,
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
            'reason_name'=>['required','max:255',Rule::unique('reason')->ignore($request->id, 'id')]
        ],
        [
            'reason_name.required' => 'Please Enter Reason Name',
            'reason_name.max' => 'Maximum 255 Characters Allowed'
        ]);
        
        DB::beginTransaction();

        try
        {
            $reason_data = Reason::where('id','=',$request->id)->update([
                'reason_type' => $request->reason_type,
                'reason_name' => $request->reason_name,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($reason_data)
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
            // $inq_sc_regret_reason_id = InquiryShortClose::where('inq_sc_regret_reason_id','=',$request->id)->get(); 
            // if($inq_sc_regret_reason_id->isNotEmpty())
            // {
            //     return response()->json([
            //         'response_code' => '0',
            //         'response_message' => "You Can't Delete, Reason Is Used In Inquiry Short Close.",
            //     ]);
            // }

            // $fr_reason_id = FeasibilityReview::where('fr_reason_id','=',$request->id)->get(); 
            // if($fr_reason_id->isNotEmpty())
            // {
            //     return response()->json([
            //         'response_code' => '0',
            //         'response_message' => "You Can't Delete, Reason Is Used In Feasibility Review.",
            //     ]);
            // }

            // $irsd_reason_id = ItemReturnSlipDetails::where('irsd_reason_id','=',$request->id)->get(); 
            // if($irsd_reason_id->isNotEmpty())
            // {
            //     return response()->json([
            //         'response_code' => '0',
            //         'response_message' => "You Can't Delete, Reason Is Used In Item Return Slip.",
            //     ]);
            // }

            $reason_data =  DB::select('CALL reason_master_used_list(?)', [$request->id]);

            if(!empty($reason_data)){ 
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Reason Is Used In " . $reason_data[0]->table_name . ".",
                ]);
            }

            Reason::destroy($request->id);
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

    public function existsReason(Request $request)
    {
        if($request->term != "")
        {
            $fdReason = Reason::select('reason_name')->where('reason_name', 'LIKE', $request->term.'%')->groupBy('reason_name')->get();
            if($fdReason != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdReason as $dsKey)
                {
                    $output .= '<li parent-id="reason_name" list-id="reason_name_list" class="list-group-item" tabindex="0">'.$dsKey->reason_name.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'reasonList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Reason Name Available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'reasonList' => '',
                'response_code' => 1,
            ]);
        }
    }
}