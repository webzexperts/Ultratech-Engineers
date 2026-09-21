<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DataTables;
use Date;
use App\Models\Admin;
use App\Models\File;
use App\Models\MaterialInspection;
use App\Models\MaterialInward;
use App\Models\MaterialInwardDetails;
use App\Models\Transaction\NonRetMatChallanDetails;

class MaterialInspectionController extends Controller
{
  
    public function manage()
    {
        return view('manage.transaction.manage-material_inspection');
    }

    public function index(MaterialInspection $material_inspection, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $material_inspection_data = MaterialInspection::select([
            'material_inspection.mins_id',
            'material_inspection.mins_sequence',
            'material_inspection.mins_number',
            'material_inspection.mins_date',
            'material_inspection.mins_mid_id',
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
            // \DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),
            'material_inward_details.mid_qty',
            'material_inward_details.mid_qty_unit_id',
            'unit.unit',
            'material_inspection.mins_insp_qty',
            'material_inspection.mins_result',
            'material_inspection.mins_rej_reason',
            'inspected_by.person_name as inspected_by_name',
            'material_inspection.created_on',
            'material_inspection.created_by',
            'material_inspection.last_by',
            'material_inspection.last_on',
        ])
        ->leftJoin('material_inward_details','material_inward_details.mid_id','=','material_inspection.mins_mid_id')
        ->leftJoin('material_inward','material_inward.mi_id','=','material_inward_details.mid_mi_id')
        ->leftJoin('customers','customers.id','=','material_inward.mi_customer_id')
        ->leftJoin('type_of_job','type_of_job.id','=','material_inward_details.mid_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','material_inward_details.mid_job_desc_id')
        ->leftJoin('part','part.part_id','=','material_inward_details.mid_part_id')
        ->leftJoin('unit','unit.id','=','material_inward_details.mid_qty_unit_id')
        ->leftJoin('admin as inspected_by','inspected_by.id','=','material_inspection.mins_inspected_by_id')
        ->where('material_inspection.year_id', $year_data->id);
        $dataTable = DataTables::of($material_inspection_data)
        ->filterColumn('part.part', function($query, $keyword) {
            $keyword = trim($keyword);
            $query->whereRaw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) LIKE ?", ["%{$keyword}%"]);
        })
        ->editColumn('mins_date', function($material_inspection_data){
            if ($material_inspection_data->mins_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $material_inspection_data->mins_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->editColumn('mi_date', function($material_inspection_data){
            if ($material_inspection_data->mi_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $material_inspection_data->mi_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->editColumn('mi_challan_date', function($material_inspection_data){
            if ($material_inspection_data->mi_challan_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $material_inspection_data->mi_challan_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('material_inspection.mins_date', function ($q, $k) {
            applyDate($q, $k, 'material_inspection.mins_date');
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
        ->filterColumn('material_inspection.mins_number', function ($q, $k) {
            applynumber($q, $k, 'material_inspection.mins_number');
        })
        ->addColumn('options',function($material_inspection_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("material_inspection", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-material_inspection"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("material_inspection", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'material_inspection');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {

        
        $year_data = getCurrentYearData();


        $existNumber = MaterialInspection::where([['mins_sequence',  $request->mins_sequence],['mins_number',$request->mins_number],
            ['year_id',$year_data->id]])->first();
        
            if($existNumber){
                $latestNo = $this->getLatestMaterialInspectionNumber($request);              
                $tmp =  $latestNo->getContent();
                $area = json_decode($tmp, true);
                $mins_number =   $area['latest_no'];
                $mins_sequence = $area['number'];
            }else{
                $mins_number = $request->mins_number;
                $mins_sequence = $request->mins_sequence;
            }
        DB::beginTransaction();
        try
        {

            $material_inspection = MaterialInspection::create([
                'mins_sequence' => $mins_sequence !="" ? $mins_sequence : null,
                'mins_number'   => $mins_number !="" ? $mins_number : null,   
                'mins_date' => isset($request->mins_date) ? Date::createFromFormat('d/m/Y', $request->mins_date)->format('Y-m-d') : null,
                'mins_mid_id'                 => $request->mins_mid_id !="" ? $request->mins_mid_id : null,
                'mins_description'         => $request->mins_description !="" ? $request->mins_description : null,
                'mins_insp_qty'                   => $request->mins_insp_qty !="" ? $request->mins_insp_qty : null,
                'mins_result'                     => $request->mins_result !="" ? $request->mins_result : null,
                'mins_rej_reason'        => $request->mins_rej_reason !="" ? $request->mins_rej_reason : null,
                'mins_inspected_by_id'        => $request->mins_inspected_by_id  !="" ? $request->mins_inspected_by_id : null,
                'mins_special_note'           => $request->mins_special_note  !="" ? $request->mins_special_note : null,
                'year_id'         => $year_data->id,
                'company_id'      => Auth::user()->company_id,
                'created_on'      => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'      => Auth::user()->id,

            ]);

            if($material_inspection->save())
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
       
         $material_inspection_data =  DB::select('CALL material_inspection_master(?)',  [$request->id]);
         if (!empty($material_inspection_data)) {
                    $material_inspection_data = $material_inspection_data[0];
                    $material_inspection_data->mins_date = $material_inspection_data->mins_date != "" ? Date::createFromFormat('Y-m-d',  $material_inspection_data->mins_date)->format('d/m/Y') : "";

                    $material_inspection_data->mi_date = $material_inspection_data->mi_date != "" ? Date::createFromFormat('Y-m-d',  $material_inspection_data->mi_date)->format('d/m/Y') : "";

                    $material_inspection_data->mi_challan_date = $material_inspection_data->mi_challan_date != "" ? Date::createFromFormat('Y-m-d',  $material_inspection_data->mi_challan_date)->format('d/m/Y') : "";
                   
                    if(isset($material_inspection_data->cmp_logo)){
                        $material_inspection_data->cmp_logo = base64_encode($material_inspection_data->cmp_logo);
                    }

                    $total_grn_qty = NonRetMatChallanDetails::where('nrmdc_mins_id', '=', $material_inspection_data->mins_id)->sum('nrmcd_qty');
                    $total_used_qty = $total_grn_qty;

                    if($total_used_qty != null && $total_used_qty > 0){
                        $material_inspection_data->in_use = true;
                        $material_inspection_data->used_qty = $total_used_qty;
                    } else {
                        $material_inspection_data->in_use = false;
                        $material_inspection_data->used_qty = 0;
                    }
                }
        if($material_inspection_data){
            return response()->json([
                'material_inspection_data'         => $material_inspection_data,
                'response_code'    => '1',
                'response_message' => '',
            ]);
        }else{
            return response()->json([
                'response_code'    => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    

     public function update(Request $request)
    {
        DB::beginTransaction();
        // dd($request->all());
        try{

            /* -----------------------------------------------------------
            UPDATE MATERIAL INSPECTION MAIN RECORD
            ----------------------------------------------------------- */

            $year_data = getCurrentYearData();
                  
            $validated = $request->validate([       
                'mins_sequence' => ['required', 'max:155', Rule::unique('material_inspection')->where(function ($query) use ($request, $year_data) { $query->where('year_id', '=', $year_data->id);})->ignore($request->id, 'mins_id')
                ],
                'mins_number' => ['required','max:155',Rule::unique('material_inspection')->where(function ($query) use ($request, $year_data) { $query->where('year_id', '=', $year_data->id); })->ignore($request->id, 'mins_id')
                ],
                ], [
                'mins_number.unique' => 'Material Inspection No. Already Exists',
                'mins_number.required' => 'Please Enter Material Inspection  No.',
                
            ]);


            $material_inspection_data = MaterialInspection::where('mins_id', $request->id)->update([

                'mins_sequence' =>$request->mins_sequence !="" ? $request->mins_sequence : null,
                'mins_number'   => $request->mins_number !="" ? $request->mins_number : null,   
                'mins_date' => isset($request->mins_date) ? Date::createFromFormat('d/m/Y', $request->mins_date)->format('Y-m-d') : null,
                'mins_mid_id'                 => $request->mins_mid_id !="" ? $request->mins_mid_id : null,
                'mins_description'         => $request->mins_description !="" ? $request->mins_description : null,
                'mins_insp_qty'                   => $request->mins_insp_qty !="" ? $request->mins_insp_qty : null,
                'mins_result'                     => $request->mins_result !="" ? $request->mins_result : null,
                'mins_rej_reason'        => $request->mins_rej_reason !="" ? $request->mins_rej_reason : null,
                'mins_inspected_by_id'        => $request->mins_inspected_by_id  !="" ? $request->mins_inspected_by_id : null,
                'mins_special_note'           => $request->mins_special_note  !="" ? $request->mins_special_note : null,
                'last_on'       => Carbon::now('Asia/Kolkata'),
                'last_by'       => Auth::id(),
            ]);

            if(!$material_inspection_data){
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
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
        DB::beginTransaction();
        try{

            $mid_used_ids = MaterialInspection::select('mins_id')->where('mins_id', $request->id)->pluck('mins_id');
            if (NonRetMatChallanDetails::whereIn('nrmdc_mins_id', $mid_used_ids)->exists()) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Material Inspection Is Used In Delivery Challan",
                ]);
            }

            MaterialInspection::where('mins_id',$request->id)->delete();

            DB::commit();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        }catch(\Exception $e){
            report($e);
            DB::rollBack();
            if(isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451){
                $error_msg = "This is used somewhere, you can't delete";
            }else{
                $error_msg = getResponseMessage('delete_error');
            }
            return response()->json([
                'response_code' => '0',
                'response_message' => $error_msg,
            ]);
        }
    }

    public function getLatestMaterialInspectionNumber(Request $request)
    {
        $modal  =  MaterialInspection::class;
        $sequence = 'mins_sequence';           
        $sup_num_format = getLatestSequence($modal,$sequence);   
       
        return response()->json([
          'response_code' => 1,
          'latest_no'     => $sup_num_format['format'],
          'number'        => $sup_num_format['isFound'],
      ]);
    }

    public function getMaterialInwardListForMaterialInspection(Request $request){

        $yearIds = getCompanyYearIdsToTill();



        $material_inward_data = MaterialInward::select('material_inward.mi_id','material_inward.mi_number','material_inward.mi_date','material_inward.mi_customer_id','customers.customer_code','customers.customer','material_inward.mi_challan_number','material_inward.mi_challan_date','material_inward_details.mid_id','material_inward_details.mid_test_method_id','material_inward_details.mid_type_of_job_id','type_of_job.type_of_job','material_inward_details.mid_job_desc_id','job_descriptions.job_description','material_inward_details.mid_part_id',\DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),'material_inward_details.mid_qty','material_inward_details.mid_qty_unit_id','unit.unit','material_inward.mi_special_note', 
        // DB::raw(" material_inward_details.mid_qty - COALESCE((SELECT SUM(mins_insp_qty) FROM material_inspection WHERE mins_mid_id = material_inward_details.mid_id ),0) AS mid_pend_qty

        //    DB::raw("
        //         material_inward_details.mid_qty
        //         - COALESCE(
        //             (SELECT SUM(mins_insp_qty)
        //             FROM material_inspection
        //             WHERE mins_mid_id = material_inward_details.mid_id
        //             ), 0
        //         )
        //         - COALESCE(
        //             (SELECT SUM(nrmcd_qty)
        //             FROM non_ret_mat_challan_details
        //             WHERE nrmcd_mid_id = material_inward_details.mid_id
        //             ), 0
        //         ) AS mid_pend_qty
        //     ")
           DB::raw("
                material_inward_details.mid_qty
                - COALESCE(
                    (SELECT SUM(mins_insp_qty)
                    FROM material_inspection
                    WHERE mins_mid_id = material_inward_details.mid_id
                    ), 0
                ) AS mid_pend_qty
            ")
        ) 


        ->leftJoin('material_inward_details','material_inward_details.mid_mi_id','=','material_inward.mi_id')
        ->leftJoin('customers','customers.id','=','material_inward.mi_customer_id')
        ->leftJoin('type_of_job','type_of_job.id','=','material_inward_details.mid_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','material_inward_details.mid_job_desc_id')
        ->leftJoin('part','part.part_id','=','material_inward_details.mid_part_id')
        ->leftJoin('unit','unit.id','=','material_inward_details.mid_qty_unit_id')
        ->whereIn('material_inward.year_id',$yearIds)
        ->having('mid_pend_qty', '>', 0)
        ->get();

        if ($material_inward_data != null) {
           
            foreach ($material_inward_data as $cpKey => $cpVal) {

                if ($cpVal->mi_date != null) {
                    $cpVal->mi_date = Date::createFromFormat('Y-m-d', $cpVal->mi_date)->format('d/m/Y');
                }
                if ($cpVal->mi_challan_date != null) {
                    $cpVal->mi_challan_date = Date::createFromFormat('Y-m-d', $cpVal->mi_challan_date)->format('d/m/Y');
                }
                if ($cpVal->mi_inward_date != null) {
                    $cpVal->mi_inward_date = Date::createFromFormat('Y-m-d', $cpVal->mi_inward_date)->format('d/m/Y');
                }
                
            }

        }

        if ($material_inward_data != null) {
            return response()->json([
                'response_code' => '1',
                'material_inward_data' => $material_inward_data,


            ]);
        } else {
            return response()->json([
                'response_code' => '1',
                'material_inward_data' => []
            ]);
        }
    }


    public function getMaterialInwardPartDataForMaterialInspection(Request $request){

        $yearIds = getCompanyYearIdsToTill();


        $material_inward_data = MaterialInward::select('material_inward.mi_id','material_inward.mi_number','material_inward.mi_date','material_inward.mi_customer_id','customers.customer_code','customers.customer','material_inward.mi_challan_number','material_inward.mi_challan_date','material_inward_details.mid_id','material_inward_details.mid_test_method_id','material_inward_details.mid_type_of_job_id','type_of_job.type_of_job','material_inward_details.mid_job_desc_id','job_descriptions.job_description','material_inward_details.mid_part_id',\DB::raw("IF(part.drg_no IS NOT NULL AND part.drg_no != '', CONCAT(part.part_no, ' - ', part.drg_no), part.part_no) as part"),'material_inward_details.mid_qty','material_inward_details.mid_qty_unit_id','unit.unit',
        
        //DB::raw(" material_inward_details.mid_qty - COALESCE((SELECT SUM(mins_insp_qty) FROM material_inspection WHERE mins_mid_id = material_inward_details.mid_id ),0) AS mid_pend_qty

        
        DB::raw("
                material_inward_details.mid_qty
                - COALESCE(
                    (SELECT SUM(mins_insp_qty)
                    FROM material_inspection
                    WHERE mins_mid_id = material_inward_details.mid_id
                    ), 0
                )
                - COALESCE(
                    (SELECT SUM(nrmcd_qty)
                    FROM non_ret_mat_challan_details
                    WHERE nrmcd_mid_id = material_inward_details.mid_id
                    ), 0
                ) AS mid_pend_qty
            ")
        )   

        ->leftJoin('material_inward_details','material_inward_details.mid_mi_id','=','material_inward.mi_id')
        ->leftJoin('customers','customers.id','=','material_inward.mi_customer_id')
        ->leftJoin('type_of_job','type_of_job.id','=','material_inward_details.mid_type_of_job_id')
        ->leftJoin('job_descriptions','job_descriptions.id','=','material_inward_details.mid_job_desc_id')
        ->leftJoin('part','part.part_id','=','material_inward_details.mid_part_id')
        ->leftJoin('unit','unit.id','=','material_inward_details.mid_qty_unit_id')
        ->whereIn('material_inward.year_id',$yearIds)
        ->where('material_inward_details.mid_id','=',$request->mid_ids)
        ->having('mid_pend_qty', '>', 0)
        ->get();

        if ($material_inward_data != null) {

            foreach ($material_inward_data as $cpKey => $cpVal) {

                if ($cpVal->mi_date != null) {
                    $cpVal->mi_date = Date::createFromFormat('Y-m-d', $cpVal->mi_date)->format('d/m/Y');
                }
                if ($cpVal->mi_challan_date != null) {
                    $cpVal->mi_challan_date = Date::createFromFormat('Y-m-d', $cpVal->mi_challan_date)->format('d/m/Y');
                }
                if ($cpVal->mi_inward_date != null) {
                    $cpVal->mi_inward_date = Date::createFromFormat('Y-m-d', $cpVal->mi_inward_date)->format('d/m/Y');
                }
            
            }

        }
        if ($material_inward_data != null) {
            return response()->json([
                'response_code' => '1',
                'material_inward_data' => $material_inward_data,


            ]);
        } else {
            return response()->json([
                'response_code' => '1',
                'material_inward_data' => []
            ]);
        }
    }

    public function materialInspectionLNRData()
    {
        $lnr_data = MaterialInspection::select('mins_id','mins_inspected_by_id')->orderBy('mins_id','Desc')->first();
       
        return response()->json([
          'response_code' => 1,
          'lnr_data'       => $lnr_data,
      ]);
    }



}
