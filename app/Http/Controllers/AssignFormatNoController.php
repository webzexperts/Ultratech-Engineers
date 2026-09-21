<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Menus;
use App\Models\AssignFormatNo;
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

class AssignFormatNoController extends Controller
{
    public function manage()
    {
        return view('manage.manage-assign_format_no');
    }

    public function store(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();
        try
        {   
            $rpt_name = getRptNameForAssignFormateNo($request->page_id)->rpt_name;
            $assign_format_name = getRptNameForAssignFormateNo($request->page_id)->display_name;

            $AssignData = AssignFormatNo::create([
                'assign_location_id' => $request->location_id,
                'assign_format_name' => $assign_format_name,
                'assign_format_no'   => $request->assign_format_no,
                'page_id'            => $request->page_id,
                'rpt_name'           => $rpt_name,
                'assign_effect_date' => isset($request->assign_effect_date,) ? Date::createFromFormat('d/m/Y', $request->assign_effect_date,)->format('Y-m-d') : null,
                'company_id'    => Auth::user()->company_id,
                'created_on'    => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'    => Auth::user()->id
            ]);

            if($AssignData->save())
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
        // dd($request->all());
        $AssignData = AssignFormatNo::where('assign_id','=',$request->id)->first();
        if($AssignData)
        {
            if(isset($AssignData->assign_effect_date)){
                $AssignData->assign_effect_date = $AssignData->assign_effect_date != "" ? Date::createFromFormat('Y-m-d',  $AssignData->assign_effect_date)->format('d/m/Y') : "";
            }
            return response()->json([
                'assign_data' => $AssignData,
                'response_code' => '1',
                'response_message' => '',
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists.',
            ]);
        }
    }

    public function update(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();
        try
        {
            $rpt_name = getRptNameForAssignFormateNo($request->page_id)->rpt_name;
            $assign_format_name = getRptNameForAssignFormateNo($request->page_id)->display_name;
            $AssignData = AssignFormatNo::where('assign_id','=',$request->id)->update([
                'assign_location_id' => $request->location_id,
                'assign_format_name' => $assign_format_name,
                'assign_format_no'   => $request->assign_format_no,
                'page_id'            => $request->page_id,
                'rpt_name'           => $rpt_name,
                'assign_effect_date' => isset($request->assign_effect_date,) ? Date::createFromFormat('d/m/Y', $request->assign_effect_date,)->format('Y-m-d') : null,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($AssignData)
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
            NABLConfiguration::destroy($request->id);
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
                $error_msg = "This Is Used Somewhere, You Can't Delete.";
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

    public function getAssignDataBasedOnLocation(Request $request)
    {
        // dd($request->all());
        $assign_data = Menus::select('assign_format_no.*', 'menus.display_name', 'menus.id as menu_id')
                                ->leftJoin('assign_format_no', function ($join) use ($request) {
                                    $join->on('assign_format_no.page_id', '=', 'menus.id')
                                        ->where('assign_format_no.assign_location_id', '=', $request->location_id);
                                })
                                ->whereIn('menus.id', getPageIdForAssignFormatNo())
                                ->orderBy('menus.id')
                                ->orderByDesc('assign_format_no.assign_effect_date')
                                ->orderByDesc('assign_format_no.assign_id')
                                ->get()
                                ->unique('menu_id')
                                ->values();

        if($assign_data->count() > 0){
            foreach($assign_data as $dKey => $dVal)
            {
                $dVal->assign_effect_date = $dVal->assign_effect_date != "" ? Date::createFromFormat('Y-m-d',  $dVal->assign_effect_date)->format('d/m/Y') : "";
            }
        }
        // dd($assign_data);

        return response()->json([
            'response_code' => '1',
            'response_message' => '',
            'assign_data' => $assign_data
        ]);
    }

    public function getAssignDataBasedOnPageId(Request $request)
    {
        // dd($request->all());
        if($request->assign_id != '') {
            $assign_data = AssignFormatNo::where('assign_location_id', '=', $request->location_id)
                            ->where('page_id', '=', $request->page_id)
                            ->where('assign_id', '!=', $request->assign_id)
                            ->get();    
        } else {
            $assign_data = AssignFormatNo::where('assign_location_id', '=', $request->location_id)
                                ->where('page_id', '=', $request->page_id)
                                ->get();
        }

        $last_effective_data = AssignFormatNo::where('assign_location_id', '=', $request->location_id)
                            ->where('page_id', '=', $request->page_id)
                            ->orderByDesc('assign_effect_date')
                            ->orderByDesc('assign_id')
                            ->first();

        $DisplayName = '';
        $DisplayName = Menus::select('display_name')->where('id', $request->page_id)->first();
        if($assign_data->count() > 0){
            foreach($assign_data as $dKey => $dVal)
            {
                $dVal->assign_effect_date = $dVal->assign_effect_date != "" ? Date::createFromFormat('Y-m-d',  $dVal->assign_effect_date)->format('d/m/Y') : "";
            }
        }
                
        return response()->json([
            'response_code' => '1',
            'response_message' => '',
            'assign_data' => $assign_data,
            'display_name' => $DisplayName,
            'last_effective_data' => $last_effective_data
        ]);
    }
}