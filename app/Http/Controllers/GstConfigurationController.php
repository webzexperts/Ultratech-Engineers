<?php

namespace App\Http\Controllers;

use App\Models\GstConfiguration;
use App\Models\GstConfigurationDetails;
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

class GstConfigurationController extends Controller
{
    public function manage()
    {
        return view('manage.manage-gst_configuration');
    }

    public function gstData()
    {
        $gst_conf = GstConfiguration::all();
        if($gst_conf)
        {
            return response()->json([
                'gst_conf' => $gst_conf,
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

    public function index(GstConfiguration $gst_data, Request $request, DataTables $datatables)
    {
        $gst_data = GstConfiguration::select([
            'gst_configuration.gc_id',
            'gst_configuration.gc_sac',
            'gst_configuration.gc_description',
            'gst_configuration.gc_remarks',
            'gst_configuration_details.gcd_details_id',
            'gst_configuration_details.gc_id',
            'gst_configuration_details.tcd_taxtype_name',
            'gst_configuration_details.gcd_tax_per',
            'gst_configuration_details.gcd_effective_date',
            'gst_configuration_details.gcd_remarks',
            'gst_configuration.created_on',
            'gst_configuration.created_by',
            'gst_configuration.last_by',
            'gst_configuration.last_on'
        ])
        ->leftJoin('gst_configuration_details','gst_configuration_details.gc_id','=','gst_configuration.gc_id');
        $dataTable = DataTables::of($gst_data)
        ->editColumn('gcd_tax_per', function($gst_data){ 
            return $gst_data->gcd_tax_per;
        })
        ->filterColumn('gst_configuration_details.gcd_tax_per', function($query, $keyword) {
            $query->whereRaw("CAST(gst_configuration_details.gcd_tax_per as CHAR) LIKE ?", ["%{$keyword}%"]);
        })
        ->editColumn('gcd_effective_date', function($gst_data){
            if ($gst_data->gcd_effective_date != null) {
                $formatedDate2 = Date::createFromFormat('Y-m-d', $gst_data->gcd_effective_date)->format(DATE_FORMAT); return $formatedDate2;
            }else{
                return '';
            }
        })
        ->filterColumn('gst_configuration_details.gcd_effective_date', function ($q, $k) {
            applyDate($q, $k, 'gst_configuration_details.gcd_effective_date');
        })
        ->addColumn('options',function($gst_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("gst_configuration", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_gst_configuration"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("gst_configuration", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'gst_configuration');
        return $dataTable
        ->rawColumns(['options','gc_sac','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        // $validated = $request->validate([
        //     'sac'=>'required|max:255|unique:gst_configuration',
        // ],
        // [
        //     'sac.required' => 'Please Enter SAC',
        //     'sac.unique' => 'The SAC Has Already Been Taken',
        //     'sac.max' => 'Maximum 255 characters allowed',
        // ]);

        DB::beginTransaction();
        try
        {
            $gst_data =  GstConfiguration::create([
                'gc_sac'         => $request->sac,
                'gc_description' => $request->description,
                'gc_remarks'     => $request->gc_remark,
                'company_id'     => Auth::user()->company_id,
                'created_on'     => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'     => Auth::user()->id
            ]);

            if($gst_data->save())
            {
                if(isset($request->gst_data) && !empty($request->gst_data))
                {
                    $convertJson = json_decode($request->gst_data, true);
                    foreach($convertJson as $ctKey => $ctVal)
                    {
                        if($ctVal['tax_type'] == "1")
                        {
                            $gst_name = "SGST";
                        }
                        else if($ctVal['tax_type'] == "2")
                        {
                            $gst_name = "CGST";
                        }
                        else
                        {
                            $gst_name = "IGST";
                        }

                        if($ctVal['tax_type'] == "")
                        {
                            return response()->json([
                                'response_code' => '1',
                                'response_message' => 'Please Select Tax Type.',
                            ]);
                            return false;
                        }

                        if($ctVal != null)
                        {
                            $gst_details =  GstConfigurationDetails::create([
                                'gc_id' => $gst_data->gc_id,
                                'tcd_taxtype_fix_id' => isset($ctVal['tax_type']) ? $ctVal['tax_type'] : "",
                                'tcd_taxtype_name' => $gst_name,
                                'gcd_tax_per' => isset($ctVal['tax']) ? $ctVal['tax'] : "",
                                'gcd_effective_date' => isset($ctVal['effective_date']) ?  Date::createFromFormat('d/m/Y',$ctVal['effective_date'])->format('Y-m-d') : "",
                                'gcd_remarks' => isset($ctVal['remark']) ? $ctVal['remark'] : "",
                            ]);
                        }
                    }
                }

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
        $gst_data = GstConfiguration::select(['gst_configuration.*'])->where('gst_configuration.gc_id','=',$request->id)->first();
        $gst_details = GstConfigurationDetails::select(['gst_configuration_details.*','gst_configuration_details.tcd_taxtype_fix_id as tax_type','gst_configuration_details.gcd_tax_per as tax','gst_configuration_details.gcd_effective_date as effective_date','gst_configuration_details.gcd_remarks as remark','gst_configuration_details.tcd_taxtype_name as gst_name'])->where('gc_id','=',$request->id)->get();

        if($gst_details != null)
        {
            foreach($gst_details as $cpKey => $cpVal)
            {
                if($cpVal->effective_date != null)
                {
                    $cpVal->effective_date = Date::createFromFormat('Y-m-d', $cpVal->effective_date)->format('d/m/Y');
                }
            }
        }

        if($gst_data)
        {
            return response()->json([
                'gst_data'         => $gst_data,
                'gst_details'      => $gst_details,
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

    // public function update(Request $request)
    // {
    //     DB::beginTransaction();

    //     try
    //     {
    //         $gst_data =  GstConfiguration::where('gc_id','=',$request->id)->update([
    //             'gc_sac'         => $request->sac,
    //             'gc_description' => $request->description,
    //             'gc_remarks'     => $request->gc_remark,
    //             'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
    //             'last_by' => Auth::user()->id
    //         ]);

    //         if($gst_data)
    //         {
    //             $gc_id = GstConfigurationDetails::where('gc_id', $request->id)->pluck('gc_id')->first();
    //             if(isset($request->gst_data) && !empty($request->gst_data))
    //             {
    //                 $convertJson = json_decode($request->gst_data, true);
    //                 foreach($convertJson as $ctKey => $ctVal)
    //                 {
    //                     if($ctVal != null)
    //                     {
    //                         if($ctVal['gcd_details_id'] != null)
    //                         {
    //                             $gst_details =  GstConfigurationDetails::where('gcd_details_id',$ctVal['gcd_details_id'])->where('gc_id','=',$request->id)->update([
    //                                 'gcd_details_id' => $ctVal['gcd_details_id'],
    //                                 'gc_id' => $gc_id,
    //                                 'tcd_taxtype_fix_id' => isset($ctVal['tax_type']) ? $ctVal['tax_type'] : "",
    //                                 'tcd_taxtype_name' => isset($ctVal['gst_name']) ? $ctVal['gst_name'] : "",
    //                                 'gcd_tax_per' => isset($ctVal['tax']) ? $ctVal['tax'] : "",
    //                                 'gcd_effective_date' => isset($ctVal['effective_date']) ?  Date::createFromFormat('d/m/Y',$ctVal['effective_date'])->format('Y-m-d') : "",
    //                                 'gcd_remarks' => isset($ctVal['remark']) ? $ctVal['remark'] : "",
    //                             ]);
    //                         }
    //                         else
    //                         {
    //                             if($ctVal['tax_type'] == "1")
    //                             {
    //                                 $gst_name = "SGST";
    //                             }
    //                             else if($ctVal['tax_type'] == "2")
    //                             {
    //                                 $gst_name = "CGST";
    //                             }
    //                             else
    //                             {
    //                                 $gst_name = "IGST";
    //                             }

    //                             $gst_details =  GstConfigurationDetails::create([
    //                                 'gc_id' => $gc_id,
    //                                 'tcd_taxtype_fix_id' => isset($ctVal['tax_type']) ? $ctVal['tax_type'] : "",
    //                                 'tcd_taxtype_name' => $gst_name,
    //                                 'gcd_tax_per' => isset($ctVal['tax']) ? $ctVal['tax'] : "",
    //                                 'gcd_effective_date' => isset($ctVal['effective_date']) ?  Date::createFromFormat('d/m/Y',$ctVal['effective_date'])->format('Y-m-d') : "",
    //                                 'gcd_remarks' => isset($ctVal['remark']) ? $ctVal['remark'] : "",
    //                             ]);
    //                         }
    //                     }
    //                 }
    //             }

    //             DB::commit();
    //             return response()->json([
    //                 'response_code' => '1',
    //                 'response_message' => 'Record Updated Successfully.',
    //             ]);
    //         }
    //         else
    //         {
    //             return response()->json([
    //                 'response_code' => '0',
    //                 'response_message' => 'Record Not Updated',
    //             ]);
    //         }
    //     }
    //     catch(\Exception $e)
    //     {
    //         DB::rollBack();
    //         return response()->json([
    //             'response_code' => '0',
    //             'response_message' => 'Error Occured Record Not Updated',
    //             'original_error' => $e->getMessage()
    //         ]);
    //     }
    // }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $gst_data = GstConfiguration::where('gc_id', $request->id)->update([
                'gc_sac'         => $request->sac,
                'gc_description' => $request->description,
                'gc_remarks'     => $request->gc_remark,
                'last_on'        => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by'        => Auth::user()->id
            ]);

            if($gst_data)
            {
                $gc_id = $request->id;
                $incoming_ids = [];
                if(isset($request->gst_data) && !empty($request->gst_data))
                {
                    $convertJson = json_decode($request->gst_data, true);

                    foreach($convertJson as $ctVal)
                    {
                        if($ctVal != null)
                        {
                            if(isset($ctVal['gcd_details_id']) && $ctVal['gcd_details_id'] != null)
                            {
                                $incoming_ids[] = $ctVal['gcd_details_id'];
                                GstConfigurationDetails::where('gcd_details_id', $ctVal['gcd_details_id'])
                                    ->where('gc_id', $gc_id)
                                    ->update([
                                        'tcd_taxtype_fix_id' => $ctVal['tax_type'] ?? "",
                                        'tcd_taxtype_name'   => $ctVal['gst_name'] ?? "",
                                        'gcd_tax_per'        => $ctVal['tax'] ?? "",
                                        'gcd_effective_date' => isset($ctVal['effective_date']) ? Date::createFromFormat('d/m/Y', $ctVal['effective_date'])->format('Y-m-d') : null,
                                        'gcd_remarks'        => $ctVal['remark'] ?? ""
                                    ]);
                            }
                            else
                            {
                                $gst_name = "IGST";
                                if($ctVal['tax_type'] == "1") $gst_name = "SGST";
                                else if($ctVal['tax_type'] == "2") $gst_name = "CGST";

                                $newDetail = GstConfigurationDetails::create([
                                    'gc_id'             => $gc_id,
                                    'tcd_taxtype_fix_id'=> $ctVal['tax_type'] ?? "",
                                    'tcd_taxtype_name'  => $gst_name,
                                    'gcd_tax_per'       => $ctVal['tax'] ?? "",
                                    'gcd_effective_date'=> isset($ctVal['effective_date']) ? Date::createFromFormat('d/m/Y', $ctVal['effective_date'])->format('Y-m-d') : null,
                                    'gcd_remarks'       => $ctVal['remark'] ?? ""
                                ]);

                                $incoming_ids[] = $newDetail->gcd_details_id;
                            }
                        }
                    }
                }

                if(!empty($incoming_ids))
                {
                    GstConfigurationDetails::where('gc_id', $gc_id)->whereNotIn('gcd_details_id', $incoming_ids)->delete();
                }

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
            $usage = DB::select('CALL gst_configuration_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                $message = "You Can't Delete, GST Configuration Is Used In ". $usage[0]->table_name.".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            GstConfiguration::destroy($request->id);
            GstConfigurationDetails::where('gc_id','=',$request->id)->delete();
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
}