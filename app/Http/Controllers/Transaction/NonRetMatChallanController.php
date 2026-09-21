<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MaterialInward;
use App\Models\MaterialInspection;
use Illuminate\Http\Request;
use App\Models\Transaction\NonRetMatChallan;
use App\Models\Transaction\NonRetMatChallanDetails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Yajra\DataTables\Facades\DataTables;



class NonRetMatChallanController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-non_returnable_material_challan');
    }


    public function index(NonRetMatChallanDetails $nrmc_data, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $nrmc_data = NonRetMatChallanDetails::select([

            'non_ret_mat_challan.nrmc_id',
            'non_ret_mat_challan.nrmc_sequence',
            'non_ret_mat_challan.nrmc_number',
            'non_ret_mat_challan.nrmc_date',
            'non_ret_mat_challan_details.nrmcd_mid_id',
            'material_inward.mi_number',
            'material_inward.mi_date',
            'customers.customer_code',
            'customers.customer',
            'material_inward.mi_challan_number',
            'material_inward.mi_challan_date',
            'material_inward_details.mid_test_method_id',
            'material_inward_details.mid_type_of_job_id',
            'type_of_job.type_of_job',
            'material_inward_details.mid_job_desc_id',
            'job_descriptions.job_description',
            'material_inward_details.mid_job_desc_id',
            \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'non_ret_mat_challan_details.nrmcd_qty',
            'non_ret_mat_challan_details.nrmcd_type',
            'non_ret_mat_challan.created_on',
            'non_ret_mat_challan.created_by',
            'non_ret_mat_challan.last_by',
            'non_ret_mat_challan.last_on',
        ])
        ->leftJoin('material_inward_details','material_inward_details.mid_id','=','non_ret_mat_challan_details.nrmcd_mid_id')
        ->leftJoin('material_inward','material_inward.mi_id','=','material_inward_details.mid_mi_id')
        ->leftJoin('non_ret_mat_challan','non_ret_mat_challan.nrmc_id','=','non_ret_mat_challan_details.nrmcd_nrmc_id')
        ->leftJoin('customers','customers.id','=','material_inward.mi_customer_id')
        ->leftJoin('type_of_job','type_of_job.id','=','material_inward_details.mid_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','material_inward_details.mid_job_desc_id')
        ->leftJoin('part','part.part_id','=','material_inward_details.mid_part_id')
        ->where('non_ret_mat_challan.year_id', $year_data->id);
        $dataTable = DataTables::of($nrmc_data)
        ->filterColumn('part.part', function($query, $keyword) {
            $keyword = trim($keyword);
            $query->whereRaw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) LIKE ?", ["%{$keyword}%"]);
        })
        ->editColumn('nrmc_date', function($nrmc_data){
            if ($nrmc_data->nrmc_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $nrmc_data->nrmc_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->editColumn('mi_date', function($nrmc_data){
            if ($nrmc_data->mi_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $nrmc_data->mi_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->editColumn('mi_challan_date', function($nrmc_data){
            if ($nrmc_data->mi_challan_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $nrmc_data->mi_challan_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('non_ret_mat_challan.nrmc_date', function ($q, $k) {
            applyDate($q, $k, 'non_ret_mat_challan.nrmc_date');
        })
        ->filterColumn('material_inward.mi_date', function ($q, $k) {
            applyDate($q, $k, 'material_inward.mi_date');
        })
        ->filterColumn('material_inward.mi_challan_date', function ($q, $k) {
            applyDate($q, $k, 'material_inward.mi_challan_date');
        })
        ->filterColumn('material_inward.mi_number', function ($q, $k) {
            applynumber($q, $k, 'material_inward.mi_number');
        })
        ->filterColumn('non_ret_mat_challan.nrmc_number', function ($q, $k) {
            applynumber($q, $k, 'non_ret_mat_challan.nrmc_number');
        })
        ->addColumn('options',function($nrmc_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("non_returnable_material_challan", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-non_returnable_material_challan"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("non_returnable_material_challan", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'non_returnable_material_challan');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $year_data = getCurrentYearData();

        $existNumber = NonRetMatChallan::where([['nrmc_sequence',  $request->nrmc_sequence],['nrmc_number',$request->nrmc_number],
            ['year_id',$year_data->id]])->first();
        
            if($existNumber){
                $latestNo = $this->getLatestNRMCDCNumber($request);              
                $tmp  =  $latestNo->getContent();
                $area = json_decode($tmp, true);
                $nrmc_number   = $area['latest_no'];
                $nrmc_sequence = $area['number'];
            }else{
                $nrmc_number   = $request->nrmc_number;
                $nrmc_sequence = $request->nrmc_sequence;
            }
        DB::beginTransaction();
        try
        {

            $challan_data = NonRetMatChallan::create([
                'nrmc_sequence' => $nrmc_sequence != "" ? $nrmc_sequence : null,
                'nrmc_number'   => $nrmc_number != "" ? $nrmc_number : null,   
                'nrmc_date'     => isset($request->nrmc_date) ? Date::createFromFormat('d/m/Y', $request->nrmc_date)->format('Y-m-d') : null,
                'nrmc_customer_id'       => $request->nrmc_customer_id ?? null,
                'nrmc_transporter'       => $request->nrmc_transporter ?? null,
                'nrmc_vehicle_number'    => $request->nrmc_vehicle_number ?? null,
                'nrmc_lr_no_date'        => $request->nrmc_lr_no_date ?? null,
                'nrmc_special_note'      => $request->nrmc_special_note ?? null,
                'year_id'         => $year_data->id,
                'company_id'      => Auth::user()->company_id,
                'created_on'      => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'      => Auth::user()->id,

            ]);

            $dc_details_data = $request->dc_details_data = json_decode($request->dc_details_data, true);
            $midIds  = NULL;
            $minsIds = NULL;
            if(!empty($dc_details_data))
            {
                foreach($dc_details_data as $ctKey => $ctVal)
                {
                    if($ctVal != null)
                        // if($ctVal['material_type'] == "Not Inspected"){
                            $midIds = !empty($ctVal['mid_id']) ? $ctVal['mid_id'] : null;
                        // }else{
                            $minsIds  = !empty($ctVal['mins_id']) ? $ctVal['mins_id'] : null;
                        // }
                        $dc_details_data = NonRetMatChallanDetails::create([
                            'nrmcd_nrmc_id'  => $challan_data->nrmc_id,
                            'nrmcd_mid_id'   => $midIds,
                            'nrmdc_mins_id'   => $minsIds,
                            'nrmcd_qty'      => !empty($ctVal['nrmcd_qty']) ? $ctVal['nrmcd_qty'] : null,
                            'nrmcd_type'      => !empty($ctVal['material_type']) ? $ctVal['material_type'] : null,
                            'nrmcd_status'     => 'Y',
                        ]);
                    }
            }

            if($challan_data->save())
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
        $nrmc_data =  DB::select('CALL non_returnable_challan_master(?)', [$request->id]);
        if(!empty($nrmc_data))
        {
            $nrmc_data = $nrmc_data[0];
            $nrmc_data->nrmc_date = $nrmc_data->nrmc_date != "" ? Date::createFromFormat('Y-m-d',  $nrmc_data->nrmc_date)->format('d/m/Y') : "";

            if(isset($nrmc_data->company_logo))
            {
                $nrmc_data->company_logo = base64_encode($nrmc_data->company_logo);
            }
        }
        
        $dc_details_data = DB::select('CALL non_returnable_challan_details(?)', [$request->id]);

        if($dc_details_data){
            // dd($dc_details_data);
            foreach($dc_details_data as $dKey => $dVal)
            {
                $dVal->mi_challan_date = $dVal->mi_challan_date != "" ? Date::createFromFormat('Y-m-d',  $dVal->mi_challan_date)->format('d/m/Y') : "";
                $dVal->pend_dc_qty += $dVal->nrmcd_qty;
                // if (!empty($dVal->nrmcd_qty)) {
                //     if($dVal->material_type == 'Accepted'){
                //         $dVal->pend_dc_qty = $dVal->mid_qty - $dVal->nrmcd_qty;
                //     }
                // }
            }
        }

        if($nrmc_data)
        {
            return response()->json([
                'nrmc_data'       => $nrmc_data,
                'dc_details_data' => $dc_details_data,
                'response_code'   => '1',
                'response_message'=> '',
            ]);
        }
        else
        {
            return response()->json([
                'response_code'    => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    public function update(Request $request)
    {
        $year_data = getCurrentYearData();

        $challan = NonRetMatChallan::find($request->id);

        if (!$challan) {
            return response()->json([
                'response_code' => '0',
                'response_message' => getResponseMessage('update_error'),
            ]);
        }

        DB::beginTransaction();
        try
        {
            // Update main DC
            $challan->update([
                'nrmc_sequence' => $request->nrmc_sequence ?? $challan->nrmc_sequence,
                'nrmc_number'   => $request->nrmc_number ?? $challan->nrmc_number,
                'nrmc_date'     => isset($request->nrmc_date) ? Date::createFromFormat('d/m/Y', $request->nrmc_date)->format('Y-m-d') : $challan->nrmc_date,
                'nrmc_customer_id'   => $request->nrmc_customer_id ?? $challan->nrmc_customer_id,
                'nrmc_transporter'   => $request->nrmc_transporter ?? $challan->nrmc_transporter,
                'nrmc_vehicle_number'=> $request->nrmc_vehicle_number ?? $challan->nrmc_vehicle_number,
                'nrmc_lr_no_date'    => $request->nrmc_lr_no_date ?? $challan->nrmc_lr_no_date,
                'nrmc_special_note'  => $request->nrmc_special_note ?? $challan->nrmc_special_note,
                'year_id'            => $year_data->id,
                'company_id'         => Auth::user()->company_id,
                'modified_on'        => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'modified_by'        => Auth::user()->id,
            ]);

            // Update DC details
            // $dc_details_data = json_decode($request->dc_details_data ?? '[]', true);

            // if (!empty($dc_details_data)) {

            //     $incoming_mid_ids = array_column($dc_details_data, 'mid_id');

            //     // Delete old details that are no longer in the request
            //     NonRetMatChallanDetails::where('nrmcd_nrmc_id', $request->id)
            //         ->whereNotIn('nrmcd_mid_id', $incoming_mid_ids)
            //         ->delete();

            //     foreach ($dc_details_data as $ctVal) {
            //         if ($ctVal != null && !empty($ctVal['mid_id'])) {

            //             // Update if exists, else create
            //             NonRetMatChallanDetails::updateOrCreate(
            //                 [
            //                     'nrmcd_nrmc_id' => $request->id,
            //                     'nrmcd_mid_id'  => $ctVal['mid_id']
            //                 ],
            //                 [
            //                     'nrmcd_qty'   => $ctVal['nrmcd_qty'] ?? 0,
            //                     'nrmcd_type'  => $ctVal['material_type'] ?? null,
            //                     'nrmcd_status'=> 'Y'
            //                 ]
            //             );
            //         }
            //     }
            // } else {
            //     // If no details sent, delete all existing details
            //     NonRetMatChallanDetails::where('nrmcd_nrmc_id', $request->id)->delete();
            // }

            $dc_details_data = json_decode($request->dc_details_data ?? '[]', true);

            if (!empty($dc_details_data)) {

                $incomingIds = [];

                foreach ($dc_details_data as $ctVal) {

                    if ($ctVal == null) continue;

                    $midId  = $ctVal['mid_id'];

                    $minsId = $ctVal['mins_id'] != 0 && $ctVal['mins_id'] != '' ?  $ctVal['mins_id'] : NULL;

                    if ($ctVal['nrmcd_id'] != '' &&  $ctVal['nrmcd_id'] != "0") {
                        $detail = NonRetMatChallanDetails::find($ctVal['nrmcd_id']);

                        if ($detail) {
                            $detail->update([
                                'nrmcd_mid_id'  => $midId,
                                'nrmdc_mins_id' => $minsId,
                                'nrmcd_qty'     => $ctVal['nrmcd_qty'] ?? 0,
                                'nrmcd_type'    => $ctVal['material_type'] ?? null,
                                'nrmcd_status'  => 'Y',
                            ]);

                            $incomingIds[] = $detail->nrmcd_id;
                        }

                    } 
                    // 👉 Else → CREATE
                    else {

                        $detail = NonRetMatChallanDetails::create([
                            'nrmcd_nrmc_id'  => $challan->nrmc_id,
                            'nrmcd_mid_id'   => $midId,
                            'nrmdc_mins_id'  => $minsId,
                            'nrmcd_qty'      => $ctVal['nrmcd_qty'] ?? 0,
                            'nrmcd_type'     => $ctVal['material_type'] ?? null,
                            'nrmcd_status'   => 'Y',
                        ]);

                        $incomingIds[] = $detail->nrmcd_id;
                    }
                }

                // 👉 Delete records not present in request
                NonRetMatChallanDetails::where('nrmcd_nrmc_id', $challan->nrmc_id)
                    ->whereNotIn('nrmcd_id', $incomingIds)
                    ->delete();

            } else {
                NonRetMatChallanDetails::where('nrmcd_nrmc_id', $challan->nrmc_id)->delete();
            }


            DB::commit();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('update_success'),
            ]);

        } catch (\Exception $e) {
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
            NonRetMatChallan::where('nrmc_id',$request->id)->delete();
            NonRetMatChallanDetails::where('nrmcd_nrmc_id',$request->id)->delete();
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


    public function getLatestNRMCDCNumber(Request $request)
    {
        $modal  =  NonRetMatChallan::class;
        $sequence = 'nrmc_sequence'; 
        $sup_num_format = getLatestSequence($modal,$sequence);   

        return response()->json([
          'response_code' => 1,
          'latest_no'     => $sup_num_format['format'],
          'number'        => $sup_num_format['isFound'],
      ]);
    }

    public function getPendingCustomerForDC(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();

        $existingCustomerId = NonRetMatChallan::where('nrmc_id', $request->id)->value('nrmc_customer_id');

        // if(isset($request->id)){
        //     $get_dc_customer =  Customer::select('id','customer')->orderBy('customer','asc')->get();

        //     return response()->json([
        //         'response_code' => 1,
        //         'get_dc_customer'  => $get_dc_customer,
        //     ]);
        // }

        //    $get_dc_customer = MaterialInward::select(

        //         'customers.customer',
        //         'customers.id',
        //             DB::raw("(
        //                 material_inward_details.mid_qty 
        //                 - (SELECT IFNULL(SUM(mcd.nrmcd_qty),0) 
        //                 FROM non_ret_mat_challan_details AS mcd 
        //                 WHERE mcd.nrmcd_mid_id = material_inward_details.mid_id)
        //                 - (SELECT IFNULL(SUM(mis.mins_insp_qty),0) 
        //                 FROM material_inspection AS mis 
        //                 WHERE mis.mins_mid_id = material_inward_details.mid_id
        //                 AND mis.mins_result != 'Short'   -- exclude Short qty
        //                 )
        //             ) as pend_dc_qty")
        //         )
        //         ->leftJoin('material_inward_details', 'material_inward_details.mid_mi_id', '=', 'material_inward.mi_id')
        //         ->leftJoin('customers', 'customers.id', '=', 'material_inward.mi_customer_id')
        //         ->whereIn('material_inward.year_id', $yearIds)
        //         ->having('pend_dc_qty', '>', 0)
        //         ->get();

                
        //         $get_dc_customer = $get_dc_customer->unique(['id']);

        //         $get_dc_customer = $get_dc_customer->values()->all();

                $baseQuery = MaterialInward::select(
                    'customers.customer',
                    'customers.id',
                     DB::raw("(
                        material_inward_details.mid_qty

                        - (
                            SELECT IFNULL(SUM(mis.mins_insp_qty),0)
                            FROM material_inspection AS mis
                            WHERE mis.mins_mid_id = material_inward_details.mid_id AND mis.mins_id = non_ret_mat_challan_details.nrmdc_mins_id
                            AND mis.mins_result != 'Short'
                        )

                        - (
                            SELECT IFNULL(SUM(mcd.nrmcd_qty),0)
                            FROM non_ret_mat_challan_details AS mcd
                            WHERE mcd.nrmcd_mid_id = material_inward_details.mid_id 
                        )

                    ) as pend_dc_qty")

                    // DB::raw("(
                    //     material_inward_details.mid_qty 
                    //     - (SELECT IFNULL(SUM(mcd.nrmcd_qty),0) 
                    //     FROM non_ret_mat_challan_details AS mcd 
                    //     WHERE mcd.nrmcd_mid_id = material_inward_details.mid_id)
                    //     - (SELECT IFNULL(SUM(mis.mins_insp_qty),0) 
                    //     FROM material_inspection AS mis 
                    //     WHERE mis.mins_mid_id = material_inward_details.mid_id
                    //     AND mis.mins_result != 'Short'
                    //     )
                    // ) as pend_dc_qty")
                )
                ->leftJoin('material_inward_details', 'material_inward_details.mid_mi_id', '=', 'material_inward.mi_id')
                  ->leftJoin('non_ret_mat_challan_details', 'non_ret_mat_challan_details.nrmcd_mid_id', '=', 'material_inward_details.mid_id')
                ->leftJoin('customers', 'customers.id', '=', 'material_inward.mi_customer_id')
                ->whereIn('material_inward.year_id', $yearIds)
                ->having('pend_dc_qty', '>', 0);

                

            // If edit mode, add existing customer separately
            if (!empty($existingCustomerId)) {

                $existingCustomerQuery = Customer::select(
                        'customer',
                        'id',
                        DB::raw('0 as pend_dc_qty')
                    )
                    ->where('id', $existingCustomerId);

                $baseQuery = $baseQuery->union($existingCustomerQuery);
            }

            $get_dc_customer = DB::table(DB::raw("({$baseQuery->toSql()}) as sub"))
                ->mergeBindings($baseQuery->getQuery())
                ->get()
                ->unique('id')
                ->values()
                ->all();


            return response()->json([
                'response_code' => 1,
                'get_dc_customer'  => $get_dc_customer,
            ]);
    }
        
    /*public function getPendingInwardListForDC(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();

        // Fetch Material Inward Data
        $material_inward_data = MaterialInward::select(
            'material_inward.mi_number',
            'material_inward.mi_date',
            'material_inward.mi_challan_number',
            'material_inward.mi_challan_date',
            'material_inward_details.mid_id',
            'material_inward_details.mid_test_method_id',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'material_inward_details.mid_qty',
            'unit.unit',
            DB::raw("
                material_inward_details.mid_qty
                - COALESCE(
                    (SELECT SUM(mins_insp_qty) 
                    FROM material_inspection 
                    WHERE mins_mid_id = material_inward_details.mid_id
                    AND mins_result != 'Short'
                    ), 0
                )
                - COALESCE(
                    (SELECT SUM(nrmcd_qty)
                    FROM non_ret_mat_challan_details
                    WHERE nrmcd_mid_id = material_inward_details.mid_id
                    ), 0
                ) AS pend_dc_qty
            "),
            DB::raw("'Not Inspected' AS material_type"),
               DB::raw("
                  'Material Inward' 
                   AS material_from
            "),
        )
        ->leftJoin('material_inward_details', 'material_inward_details.mid_mi_id', '=', 'material_inward.mi_id')
        ->leftJoin('customers', 'customers.id', '=', 'material_inward.mi_customer_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'material_inward_details.mid_type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'material_inward_details.mid_job_desc_id')
        ->leftJoin('part', 'part.part_id', '=', 'material_inward_details.mid_part_id')
        ->leftJoin('unit', 'unit.id', '=', 'material_inward_details.mid_qty_unit_id')
        ->where('material_inward.mi_customer_id', $request->dc_customer_id)
        ->whereIn('material_inward.year_id', $yearIds)
        ->having('pend_dc_qty', '>', 0)
        ->get(); // This returns a collection

        // Fetch Material Inspection Data
        $material_inspection_data = MaterialInspection::select(
            'material_inspection.mins_id',
            'material_inward.mi_number',
            'material_inward.mi_date',
            'material_inward.mi_challan_number',
            'material_inward.mi_challan_date',
            'material_inward_details.mid_id',
            'material_inward_details.mid_test_method_id',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'material_inward_details.mid_qty',
            'unit.unit',
            DB::raw("
                material_inspection.mins_insp_qty - COALESCE(
                    (SELECT SUM(nrmcd_qty)
                    FROM non_ret_mat_challan_details
                    WHERE nrmcd_mid_id = material_inward_details.mid_id
                    ), 0
                ) AS pend_dc_qty
            "),
            'material_inspection.mins_result AS material_type',
               DB::raw("
                   'Material Inspection' 
                    AS material_from
            "),
            
        )
        ->leftJoin('material_inward_details', 'material_inward_details.mid_id', '=', 'material_inspection.mins_mid_id')
        ->leftJoin('material_inward','material_inward.mi_id','=','material_inward_details.mid_mi_id')
        ->leftJoin('customers', 'customers.id', '=', 'material_inward.mi_customer_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'material_inward_details.mid_type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'material_inward_details.mid_job_desc_id')
        ->leftJoin('part', 'part.part_id', '=', 'material_inward_details.mid_part_id')
        ->leftJoin('unit', 'unit.id', '=', 'material_inward_details.mid_qty_unit_id')
        ->where('material_inward.mi_customer_id', $request->dc_customer_id)
        ->whereIn('material_inward.year_id', $yearIds)
        ->having('pend_dc_qty', '>', 0)
        ->get(); // This returns a collection

        // Merge the two collections like you described
        $merged_data = $material_inward_data->concat($material_inspection_data);

        // Format dates for the merged collection
        $merged_data->each(function ($data) {
            if ($data->mi_date != null) {
                $data->mi_date = \Carbon\Carbon::createFromFormat('Y-m-d', $data->mi_date)->format('d/m/Y');
            }
            if ($data->mi_challan_date != null) {
                $data->mi_challan_date = \Carbon\Carbon::createFromFormat('Y-m-d', $data->mi_challan_date)->format('d/m/Y');
            }
        });

        // Return the merged data as a collection in JSON format
        return response()->json([
            'response_code' => '1',
            'inward_data' => $merged_data
        ]);
    }*/

    public function getPendingInwardListForDC(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();

        $editMidIds = [];
        $editMinsIds = [];
       
        if (!empty($request->id)) {

            $editMidIds = DB::table('non_ret_mat_challan_details')
                ->where('nrmcd_nrmc_id', $request->id)
                ->whereNotNull('nrmcd_mid_id')
                ->pluck('nrmcd_mid_id')
                ->toArray();

            $editMinsIds = DB::table('non_ret_mat_challan_details')
                ->where('nrmcd_nrmc_id', $request->id)
                ->whereNotNull('nrmdc_mins_id')
                ->pluck('nrmdc_mins_id')
                ->toArray();
        }
        $material_inward_data = MaterialInward::select(
            'material_inward.mi_number',
            'material_inward.mi_date',
            'material_inward.mi_challan_number',
            'material_inward.mi_challan_date',
            'material_inward_details.mid_id',
            'material_inward_details.mid_test_method_id',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'material_inward_details.mid_qty',
            'unit.unit',

            DB::raw("
                material_inward_details.mid_qty
                - COALESCE(
                    (SELECT SUM(mins_insp_qty)
                    FROM material_inspection
                    WHERE mins_mid_id = material_inward_details.mid_id
                    AND mins_result != 'Short'
                    ), 0
                )
                - COALESCE(
                    (SELECT SUM(nrmcd_qty)
                    FROM non_ret_mat_challan_details
                    WHERE nrmcd_mid_id = material_inward_details.mid_id AND non_ret_mat_challan_details.nrmdc_mins_id IS NUll
                    ), 0
                ) AS pend_dc_qty
            "),

            DB::raw("'Not Inspected/Received' AS material_type"),
            DB::raw("'Material Inward' AS material_from")
        )
        ->leftJoin('material_inward_details', 'material_inward_details.mid_mi_id', '=', 'material_inward.mi_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'material_inward_details.mid_type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'material_inward_details.mid_job_desc_id')
        ->leftJoin('part', 'part.part_id', '=', 'material_inward_details.mid_part_id')
        ->leftJoin('unit', 'unit.id', '=', 'material_inward_details.mid_qty_unit_id')
        ->where('material_inward.mi_customer_id', $request->dc_customer_id)
        ->whereIn('material_inward.year_id', $yearIds)
        ->where(function ($query) use ($request, $editMidIds) {

            if (empty($request->id)) {
                $query->having('pend_dc_qty', '>', 0);
            } else {
                $query->having('pend_dc_qty', '>', 0);
                    // ->orWhereIn('material_inward_details.mid_id', $editMidIds);
            }
        })
        ->get();

        $material_inspection_data = MaterialInspection::select(
            'material_inspection.mins_id',
            'material_inward.mi_number',
            'material_inward.mi_date',
            'material_inward.mi_challan_number',
            'material_inward.mi_challan_date',
            'material_inward_details.mid_id',
            'material_inward_details.mid_test_method_id',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'material_inspection.mins_insp_qty as mid_qty',
            'unit.unit',

            DB::raw("
                material_inspection.mins_insp_qty
                - COALESCE(
                    (SELECT SUM(nrmcd_qty)
                    FROM non_ret_mat_challan_details
                    WHERE nrmdc_mins_id = material_inspection.mins_id
                    ), 0
                ) AS pend_dc_qty
            "),

            'material_inspection.mins_result AS material_type',
            DB::raw("'Material Inspection' AS material_from")
        )
        ->leftJoin('material_inward_details', 'material_inward_details.mid_id', '=', 'material_inspection.mins_mid_id')
        ->leftJoin('material_inward','material_inward.mi_id','=','material_inward_details.mid_mi_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'material_inward_details.mid_type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'material_inward_details.mid_job_desc_id')
        ->leftJoin('part', 'part.part_id', '=', 'material_inward_details.mid_part_id')
        ->leftJoin('unit', 'unit.id', '=', 'material_inward_details.mid_qty_unit_id')
        ->where('material_inward.mi_customer_id', $request->dc_customer_id)
        ->whereIn('material_inward.year_id', $yearIds)
        ->where(function ($query) use ($request, $editMinsIds) {

            if (empty($request->id)) {
                $query->having('pend_dc_qty', '>', 0)->where('material_inspection.mins_result','!=','Short');
            } else {
                $query->having('pend_dc_qty', '>', 0)
                    ->orWhereIn('material_inspection.mins_id', $editMinsIds)->where('material_inspection.mins_result','!=','Short');
            }
        })
        ->get();
     
        $merged_data = $material_inward_data
                            ->concat($material_inspection_data)
                            ->values();
        $merged_data->each(function ($data) use($request) {
            if ($data->mi_date) {
                $data->mi_date = \Carbon\Carbon::createFromFormat('Y-m-d', $data->mi_date)->format('d/m/Y');
            }
            if ($data->mi_challan_date) {
                $data->mi_challan_date = \Carbon\Carbon::createFromFormat('Y-m-d', $data->mi_challan_date)->format('d/m/Y');
            }

            // EDIT MODE: show saved DC qty instead of pending
            if (!empty($request->id)) {

                if ($data->material_from == 'Material Inward') {
                    $savedQty = DB::table('non_ret_mat_challan_details')
                        ->where('nrmcd_nrmc_id', $request->id)
                        ->where('nrmcd_mid_id', $data->mid_id)
                        ->value('nrmcd_qty');

                } else {
                    $savedQty = DB::table('non_ret_mat_challan_details')
                        ->where('nrmcd_nrmc_id', $request->id)
                        ->where('nrmdc_mins_id', $data->mins_id)
                        ->value('nrmcd_qty');
                }
                if ($savedQty !== null) {
                    $data->pend_dc_qty += $savedQty;
                }
            }
        });

        $merged_data = $merged_data->filter(function($data) {
            return isset($data->pend_dc_qty) && $data->pend_dc_qty > 0;
        })->values();

        return response()->json([
            'response_code' => '1',
            'inward_data' => $merged_data
        ]);
    }

    public function getPendingInwardForDC(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();

        $midIds = $request->mid_ids ?? [];
        if (is_string($midIds)) {
            $midIds = explode(',', $midIds); 
        }
        $minsIds = $request->mins_ids ?? [];
        if (is_string($minsIds)) {
            $minsIds = explode(',', $minsIds); 
        }

        // $material_inspection_data = [];
        // $material_inward_data = [];
        $material_inspection_data = collect();
        $material_inward_data = collect();

        if(!empty($midIds)){


        $material_inward_data = MaterialInward::select(
            'material_inward.mi_id',
            'material_inward.mi_number',
            'material_inward.mi_date',
            'material_inward.mi_challan_number',
            'material_inward.mi_challan_date',
            'material_inward_details.mid_id',
            'material_inward_details.mid_test_method_id',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'material_inward_details.mid_qty',
            'unit.unit',
            'material_inward_details.mid_remark',
            'non_ret_mat_challan_details.nrmcd_id',
            'non_ret_mat_challan_details.nrmcd_qty',

            // Pending quantity = inward qty - sum(inspection qty excluding Short) - sum(non-ret challan qty)
            // DB::raw("
            //     material_inward_details.mid_qty
            //     - COALESCE(
            //         (SELECT SUM(mins_insp_qty) 
            //         FROM material_inspection 
            //         WHERE mins_mid_id = material_inward_details.mid_id
            //         AND mins_result != 'Short'
            //         ), 0
            //     )
            //     - COALESCE(
            //         (SELECT SUM(nrmcd_qty)
            //         FROM non_ret_mat_challan_details
            //         WHERE nrmcd_mid_id = material_inward_details.mid_id
            //         ), 0
            //     ) AS pend_dc_qty
            // "),
             DB::raw("
                CASE 
                    WHEN non_ret_mat_challan_details.nrmcd_id IS NOT NULL 
                    THEN non_ret_mat_challan_details.nrmcd_id
                    ELSE
                       0
                END AS nrmcd_id
            "),

            DB::raw("
                CASE 
                    WHEN non_ret_mat_challan_details.nrmcd_id IS NOT NULL 
                    THEN non_ret_mat_challan_details.nrmcd_qty
                    ELSE
                        material_inward_details.mid_qty
                        - COALESCE(
                            (
                                SELECT SUM(mins_insp_qty)
                                FROM material_inspection
                                WHERE mins_mid_id = material_inward_details.mid_id
                                AND mins_result != 'Short'
                            ), 0
                        )
                        - COALESCE(
                            (
                                SELECT SUM(nrmcd_qty)
                                FROM non_ret_mat_challan_details
                                WHERE nrmcd_mid_id = material_inward_details.mid_id
                            ), 0
                        )
                END AS pend_dc_qty
            "),
            // Material From
            DB::raw("'Not Inspected/Received' AS material_type"),
               DB::raw("
                  'Material Inward' 
                   AS material_from
            "),
        )
        ->leftJoin('material_inward_details','material_inward_details.mid_mi_id','=','material_inward.mi_id')
        // ->leftJoin('non_ret_mat_challan_details','non_ret_mat_challan_details.nrmcd_mid_id','=','material_inward_details.mid_id')
        ->leftJoin('non_ret_mat_challan_details', function($join) {
            $join->on(
                'non_ret_mat_challan_details.nrmcd_mid_id',
                '=',
                'material_inward_details.mid_id'
            )
            ->whereNull('non_ret_mat_challan_details.nrmdc_mins_id');
        })
        ->leftJoin('customers','customers.id','=','material_inward.mi_customer_id')
        ->leftJoin('type_of_job','type_of_job.id','=','material_inward_details.mid_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','material_inward_details.mid_job_desc_id')
        ->leftJoin('part','part.part_id','=','material_inward_details.mid_part_id')
        ->leftJoin('unit','unit.id','=','material_inward_details.mid_qty_unit_id')
        ->whereIn('material_inward.year_id',$yearIds)
        ->when(!empty($midIds), function($query) use ($midIds) {
            return $query->whereIn('material_inward_details.mid_id', $midIds);
        })
        ->having('pend_dc_qty', '>', 0)
        // ->groupBy(
        //     'material_inward.mi_id',
        //     'material_inward.mi_number',
        //     'material_inward.mi_date',
        //     'material_inward.mi_challan_number',
        //     'material_inward.mi_challan_date',
        //     'material_inward_details.mid_id',
        //     'material_inward_details.mid_test_method_id',
        //     'type_of_job.type_of_job',
        //     'job_descriptions.job_description',
        //     \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
        //     'material_inward_details.mid_qty',
        //     'unit.unit',
        //     'material_inward_details.mid_remark',
        //     'non_ret_mat_challan_details.nrmcd_id',
        //     'non_ret_mat_challan_details.nrmcd_qty'
        // )
        ->get();
        }

        // form inspection 
        if(!empty($minsIds)){
        $material_inspection_data = MaterialInspection::select(
            'material_inspection.mins_id',
            'material_inward.mi_number',
            'material_inward.mi_date',
            'material_inward.mi_challan_number',
            'material_inward.mi_challan_date',
            'material_inward_details.mid_id',
            'material_inward_details.mid_test_method_id',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'material_inward_details.mid_qty',
            'unit.unit',
            'material_inward_details.mid_remark',
            'non_ret_mat_challan_details.nrmcd_id',
            'non_ret_mat_challan_details.nrmcd_qty',
            // DB::raw("
            //     material_inspection.mins_insp_qty - COALESCE(
            //         (SELECT SUM(nrmcd_qty)
            //         FROM non_ret_mat_challan_details
            //         WHERE nrmcd_mid_id = material_inward_details.mid_id
            //         ), 0
            //     ) AS pend_dc_qty
            // "),
         DB::raw("
                CASE 
                    WHEN non_ret_mat_challan_details.nrmcd_id IS NOT NULL 
                    THEN non_ret_mat_challan_details.nrmcd_id
                    ELSE
                       0
                END AS nrmcd_id
            "),

              DB::raw("
                CASE 
                    WHEN non_ret_mat_challan_details.nrmcd_id IS NOT NULL 
                    THEN non_ret_mat_challan_details.nrmcd_qty
                    ELSE
                        material_inspection.mins_insp_qty 
                        - COALESCE(
                            (
                                SELECT SUM(nrmcd_qty)
                                FROM non_ret_mat_challan_details
                                WHERE nrmcd_mid_id = material_inward_details.mid_id
                            ), 0
                        )
                END AS pend_dc_qty
            "),

            'material_inspection.mins_result AS material_type',
               DB::raw("
                   'Material Inspection' 
                    AS material_from
            "),
            
        )
        ->leftJoin('material_inward_details', 'material_inward_details.mid_id', '=', 'material_inspection.mins_mid_id')
        // ->leftJoin('non_ret_mat_challan_details', 'non_ret_mat_challan_details.nrmdc_mins_id', '=', 'material_inspection.mins_id')
        ->leftJoin('non_ret_mat_challan_details', function($join) {
            $join->on(
                'non_ret_mat_challan_details.nrmdc_mins_id',
                '=',
                'material_inspection.mins_id'
            )
            ->whereNotNull('non_ret_mat_challan_details.nrmdc_mins_id');
        })
        ->leftJoin('material_inward','material_inward.mi_id','=','material_inward_details.mid_mi_id')
        ->leftJoin('customers', 'customers.id', '=', 'material_inward.mi_customer_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'material_inward_details.mid_type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'material_inward_details.mid_job_desc_id')
        ->leftJoin('part', 'part.part_id', '=', 'material_inward_details.mid_part_id')
        ->leftJoin('unit', 'unit.id', '=', 'material_inward_details.mid_qty_unit_id')
        ->whereIn('material_inward.year_id', $yearIds)
        ->when(!empty($minsIds), function($query) use ($minsIds) {
            return $query->whereIn('material_inspection.mins_id', $minsIds);
        })
        ->where('material_inspection.mins_result','!=','Short')
        ->having('pend_dc_qty', '>', 0)
        // ->groupBy(
        //     'material_inward.mi_id',
        //     'material_inspection.mins_id',
        //     'material_inward.mi_number',
        //     'material_inward.mi_date',
        //     'material_inward.mi_challan_number',
        //     'material_inward.mi_challan_date',
        //     'material_inward_details.mid_id',
        //     'material_inward_details.mid_test_method_id',
        //     'type_of_job.type_of_job',
        //     'job_descriptions.job_description',
        //     \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
        //     'material_inward_details.mid_qty',
        //     'unit.unit',
        //     'material_inward_details.mid_remark',
        //     'non_ret_mat_challan_details.nrmcd_id',
        //     'non_ret_mat_challan_details.nrmcd_qty',
        //     'material_inspection.mins_insp_qty' ,
        //     'material_inspection.mins_result' 
        // )
        ->get(); 
        }

        // dd($material_inward_data,$material_inspection_data);

        $merged_data = $material_inward_data->concat($material_inspection_data);

        // Format dates for the merged collection
        $merged_data->each(function ($data) {
            if ($data->mi_date != null) {
                $data->mi_date = \Carbon\Carbon::createFromFormat('Y-m-d', $data->mi_date)->format('d/m/Y');
            }
            if ($data->mi_challan_date != null) {
                $data->mi_challan_date = \Carbon\Carbon::createFromFormat('Y-m-d', $data->mi_challan_date)->format('d/m/Y');
            }
        });

        return response()->json([
            'response_code' => '1',
            'inward_data' => $merged_data ?: []
        ]);
    }

    /*public function getPendingInwardForDC(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();

        // Edit mode: get existing selected items for this DC
        $editMidIds  = [];
        $editMinsIds = [];

        // if (!empty($request->id)) {
        //     $editMidIds = DB::table('non_ret_mat_challan_details')
        //         ->where('nrmcd_nrmc_id', $request->id)
        //         ->whereNotNull('nrmcd_mid_id')
        //         ->pluck('nrmcd_mid_id')
        //         ->toArray();

        //     $editMinsIds = DB::table('non_ret_mat_challan_details')
        //         ->where('nrmcd_nrmc_id', $request->id)
        //         ->whereNotNull('nrmdc_mins_id')
        //         ->pluck('nrmdc_mins_id')
        //         ->toArray();
        // }

        // Material Inward
        $material_inward_data = MaterialInward::select(
            'material_inward.mi_id',
            'material_inward.mi_number',
            'material_inward.mi_date',
            'material_inward.mi_challan_number',
            'material_inward.mi_challan_date',
            'material_inward_details.mid_id',
            'material_inward_details.mid_test_method_id',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'material_inward_details.mid_qty',
            'unit.unit',
            'material_inward_details.mid_remark',
            DB::raw("
                material_inward_details.mid_qty
                - COALESCE(
                    (SELECT SUM(mins_insp_qty) 
                    FROM material_inspection 
                    WHERE mins_mid_id = material_inward_details.mid_id
                    AND mins_result != 'Short'), 0
                )
                - COALESCE(
                    (SELECT SUM(nrmcd_qty)
                    FROM non_ret_mat_challan_details
                    WHERE nrmcd_mid_id = material_inward_details.mid_id), 0
                ) AS pend_dc_qty
            "),
            DB::raw("'Not Inspected' AS material_type"),
            DB::raw("'Material Inward' AS material_from")
        )
        ->leftJoin('material_inward_details','material_inward_details.mid_mi_id','=','material_inward.mi_id')
        ->leftJoin('type_of_job','type_of_job.id','=','material_inward_details.mid_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','material_inward_details.mid_job_desc_id')
        ->leftJoin('part','part.part_id','=','material_inward_details.mid_part_id')
        ->leftJoin('unit','unit.id','=','material_inward_details.mid_qty_unit_id')
        ->whereIn('material_inward.year_id',$yearIds)
        ->having('pend_dc_qty', '>', 0)
        // ->where(function($query) use ($request, $editMidIds) {
        //     if (empty($request->id)) {
        //         $query->having('pend_dc_qty', '>', 0);
        //     } else {
        //         // Include either pending > 0 OR already selected in this DC
        //         $query->having('pend_dc_qty', '>', 0)
        //             ->orWhereIn('material_inward_details.mid_id', $editMidIds);
        //     }
        // })
        ->get();

        // Material Inspection
        $material_inspection_data = MaterialInspection::select(
            'material_inspection.mins_id',
            'material_inward.mi_number',
            'material_inward.mi_date',
            'material_inward.mi_challan_number',
            'material_inward.mi_challan_date',
            'material_inward_details.mid_id',
            'material_inward_details.mid_test_method_id',
            'type_of_job.type_of_job',
            'job_descriptions.job_description',
            \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'material_inward_details.mid_qty',
            'unit.unit',
            DB::raw("
                material_inspection.mins_insp_qty
                - COALESCE(
                    (SELECT SUM(nrmcd_qty)
                    FROM non_ret_mat_challan_details
                    WHERE nrmdc_mins_id = material_inspection.mins_id), 0
                ) AS pend_dc_qty
            "),
            'material_inspection.mins_result AS material_type',
            DB::raw("'Material Inspection' AS material_from")
        )
        ->leftJoin('material_inward_details', 'material_inward_details.mid_id', '=', 'material_inspection.mins_mid_id')
        ->leftJoin('material_inward','material_inward.mi_id','=','material_inward_details.mid_mi_id')
        ->leftJoin('type_of_job', 'type_of_job.id', '=', 'material_inward_details.mid_type_of_job_id')
        ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'material_inward_details.mid_job_desc_id')
        ->leftJoin('part', 'part.part_id', '=', 'material_inward_details.mid_part_id')
        ->leftJoin('unit', 'unit.id', '=', 'material_inward_details.mid_qty_unit_id')
        ->whereIn('material_inward.year_id', $yearIds)
        ->having('pend_dc_qty', '>', 0)
        // ->where(function($query) use ($request, $editMinsIds) {
        //     if (empty($request->id)) {
        //         $query->having('pend_dc_qty', '>', 0);
        //     } else {
        //         // Include either pending > 0 OR already selected in this DC
        //         $query->having('pend_dc_qty', '>', 0)
        //             ->orWhereIn('material_inspection.mins_id', $editMinsIds);
        //     }
        // })
        ->get();

        // Merge
        $merged_data = $material_inward_data->concat($material_inspection_data)->values();

        // Format dates
        $merged_data->each(function ($data) use($request) {
            if ($data->mi_date) {
                $data->mi_date = \Carbon\Carbon::createFromFormat('Y-m-d', $data->mi_date)->format('d/m/Y');
            }
            if ($data->mi_challan_date) {
                $data->mi_challan_date = \Carbon\Carbon::createFromFormat('Y-m-d', $data->mi_challan_date)->format('d/m/Y');
            }

            // Edit mode: show saved DC qty if exists
            if (!empty($request->id)) {
                if ($data->material_from == 'Material Inward') {
                    $savedQty = DB::table('non_ret_mat_challan_details')
                        ->where('nrmcd_nrmc_id', $request->id)
                        ->where('nrmcd_mid_id', $data->mid_id)
                        ->value('nrmcd_qty');
                } else {
                    $savedQty = DB::table('non_ret_mat_challan_details')
                        ->where('nrmcd_nrmc_id', $request->id)
                        ->where('nrmdc_mins_id', $data->mins_id)
                        ->value('nrmcd_qty');
                }
                if ($savedQty !== null) {
                    $data->pend_dc_qty = $savedQty;
                }
            }
        });

        return response()->json([
            'response_code' => '1',
            'inward_data' => $merged_data
        ]);
    }*/

}