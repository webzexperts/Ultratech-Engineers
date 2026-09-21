<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\LPTChemical;
use App\Models\GRN;
use App\Models\Supplier;
use App\Models\GRNDetails;
use App\Models\Item;
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

class LPTChemicalController extends Controller
{
    public function manage()
    {
        return view('manage.manage-lpt_chemical');
    }

    public function index(LPTChemical $lpt_chemical_data, Request $request, DataTables $datatables)
    {
        $lpt_chemical_data = LPTChemical::select([
            'lpt_chemical.lpt_id',
            'lpt_chemical.lpt_inward_type',
            'lpt_chemical.lpt_chemical_id',
            'item.item_name',
            'lpt_chemical.lpt_chemical_name',
            'lpt_chemical.lpt_designation',
            'lpt_chemical.lpt_make',
            'lpt_chemical.lpt_batch_no',
            'lpt_chemical.lpt_mfg_date',
            'lpt_chemical.lpt_identification_no',
            'lpt_chemical.lpt_status',
            'lpt_chemical.lpt_document_ref_no',
            'lpt_chemical.lpt_validity_date',
            'lpt_chemical.lpt_remark',
            'lpt_chemical.created_on',
            'lpt_chemical.created_by',
            'lpt_chemical.last_by',
            'lpt_chemical.last_on'
        ])
        ->leftJoin('item','item.id','=','lpt_chemical.lpt_chemical_id');
        $dataTable = DataTables::of($lpt_chemical_data)
        ->editColumn('lpt_mfg_date', function($lpt_chemical_data) {
            if ($lpt_chemical_data->lpt_mfg_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $lpt_chemical_data->lpt_mfg_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('lpt_chemical.lpt_mfg_date', function ($q, $k) {
            applyDate($q, $k, 'lpt_chemical.lpt_mfg_date');
        })
        ->editColumn('lpt_validity_date', function($lpt_chemical_data) {
            if ($lpt_chemical_data->lpt_validity_date != null) {
                $formatedDate1 = Date::createFromFormat('Y-m-d', $lpt_chemical_data->lpt_validity_date)->format(DATE_FORMAT); return $formatedDate1;
            } else {
                return '';
            }
        })
        ->filterColumn('lpt_chemical.lpt_validity_date', function ($q, $k) {
            applyDate($q, $k, 'lpt_chemical.lpt_validity_date');
        })
        ->filterColumn('lpt_chemical.lpt_status', function ($query, $keyword) {
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
                $query->where('lpt_chemical.lpt_status', '=', $searchStatus);
            } 
            else {
                $dbFormatKeyword = str_replace(' ', '_', $lowerKeyword);
                $query->where('lpt_chemical.lpt_status', 'like', "$dbFormatKeyword%");
            }
        })
        ->addColumn('options',function($lpt_chemical_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("lpt_chemical", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_lpt_chemical"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("lpt_chemical", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'lpt_chemical');
        return $dataTable
        ->rawColumns(['options','lpt_inward_type','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function chemicalLNRData()
    {
        $lnr_data = LPTChemical::select('lpt_id','lpt_inward_type')->orderBy('lpt_id','desc')->first();
            return response()->json([
            'response_code' => 1,
            'lnr_data'       => $lnr_data,
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $lpt_chemical_data = LPTChemical::create([
                'lpt_inward_type' => $request->lpt_inward_type ?? null,
                'lpt_chemical_id' => $request->lpt_chemical_id ?? null,
                'lpt_chemical_name' => $request->lpt_chemical_name ?? null,
                'lpt_designation' => $request->lpt_designation ?? null,
                'lpt_make' => $request->lpt_make ?? null,
                'lpt_batch_no' => $request->lpt_batch_no ?? null,
                'lpt_identification_no' => $request->lpt_identification_no ?? null,
                'lpt_mfg_date' => isset($request->lpt_mfg_date) ? Date::createFromFormat('d/m/Y', $request->lpt_mfg_date)->format('Y-m-d') : null,
                'lpt_status' => $request->lpt_status ?? null,
                'lpt_grnd_id' => $request->lpt_grnd_id ?? null,
                'lpt_document_ref_no' => $request->lpt_document_ref_no ?? null,
                'lpt_validity_date' => isset($request->lpt_validity_date) ? Date::createFromFormat('d/m/Y', $request->lpt_validity_date)->format('Y-m-d') : null,
                'lpt_remark' => $request->lpt_remark ?? null,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id
            ]);

            if($lpt_chemical_data->save())
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
        // $lpt_chemical_data = LPTChemical::where('lpt_id','=',$request->id)->first();
        $lpt_chemical_data = LPTChemical::select(
            'lpt_chemical.*',
            'grn.grn_number',
            'grn.grn_date',
            'grn.grn_challan_number',
            'grn.grn_challan_date',
            'suppliers.supplier_name'
        )
        ->leftJoin('grn_details', 'lpt_chemical.lpt_grnd_id', '=', 'grn_details.grnd_id')
        ->leftJoin('grn', 'grn_details.grnd_grn_id', '=', 'grn.grn_id')
        ->leftJoin('suppliers', 'grn.grn_supplier_id', '=', 'suppliers.id')
        ->where('lpt_chemical.lpt_id', $request->id)
        ->first();

        // $check_item = Item::where('item.id',$lpt_chemical_data->status)->value('item.status');

        if($lpt_chemical_data)
        {
            if(!empty($lpt_chemical_data))
            {
                $lpt_chemical_data->lpt_mfg_date = $lpt_chemical_data->lpt_mfg_date != "" ? Date::createFromFormat('Y-m-d',  $lpt_chemical_data->lpt_mfg_date)->format('d/m/Y') : "";
            }

            if(!empty($lpt_chemical_data))
            {
                $lpt_chemical_data->lpt_validity_date = $lpt_chemical_data->lpt_validity_date != "" ? Date::createFromFormat('Y-m-d',  $lpt_chemical_data->lpt_validity_date)->format('d/m/Y') : "";
            }

            if(!empty($lpt_chemical_data))
            {
                $lpt_chemical_data->grn_date = $lpt_chemical_data->grn_date != "" ? Date::createFromFormat('Y-m-d',  $lpt_chemical_data->grn_date)->format('d/m/Y') : "";
            }

            if(!empty($lpt_chemical_data))
            {
                $lpt_chemical_data->grn_challan_date = $lpt_chemical_data->grn_challan_date != "" ? Date::createFromFormat('Y-m-d',  $lpt_chemical_data->grn_challan_date)->format('d/m/Y') : "";
            }

            // // used for getting deactivate items
            // if($check_item == 'Deactive')
            // {
            //     // table name, table item id, request id, item type, table main id
            //     $items = getDeactiveItems('lpt_chemical', 'lpt_chemical_id', $request->id, 'lpt_chemical','lpt_id');
            // }
            // else
            // {
            //     $items = '';
            // }

            return response()->json([
                'lpt_chemical_data' => $lpt_chemical_data,
                // 'items' => $items,
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
        DB::beginTransaction();

        try
        {
            $lpt_chemical_data = LPTChemical::where('lpt_id','=',$request->id)->update([
                'lpt_inward_type' => $request->lpt_inward_type ?? null,
                'lpt_chemical_id' => $request->lpt_chemical_id ?? null,
                'lpt_chemical_name' => $request->lpt_chemical_name ?? null,
                'lpt_designation' => $request->lpt_designation ?? null,
                'lpt_make' => $request->lpt_make ?? null,
                'lpt_batch_no' => $request->lpt_batch_no ?? null,
                'lpt_identification_no' => $request->lpt_identification_no ?? null,
                'lpt_mfg_date' => isset($request->lpt_mfg_date) ? Date::createFromFormat('d/m/Y', $request->lpt_mfg_date)->format('Y-m-d') : null,
                'lpt_status' => $request->lpt_status ?? null,
                'lpt_grnd_id' => $request->lpt_grnd_id ?? null,
                'lpt_document_ref_no' => $request->lpt_document_ref_no ?? null,
                'lpt_validity_date' => isset($request->lpt_validity_date) ? Date::createFromFormat('d/m/Y', $request->lpt_validity_date)->format('Y-m-d') : null,
                'lpt_remark' => $request->lpt_remark ?? null,
                'last_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by' => Auth::user()->id
            ]);

            if($lpt_chemical_data)
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
            LPTChemical::where('lpt_id',$request->id)->delete();
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

    public function existsLPTChemicalDesignation(Request $request)
    {
        if($request->term != "")
        {
            $fdDesignation = LPTChemical::select('lpt_designation')->where('lpt_designation', 'LIKE', $request->term.'%')->groupBy('lpt_designation')->get();
            if($fdDesignation != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdDesignation as $dsKey)
                {
                    $output .= '<li parent-id="lpt_designation" list-id="lpt_designation_list" class="list-group-item" tabindex="0">'.$dsKey->lpt_designation.'</li>';
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
                    'response_message' => 'No Designation Available',
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

    public function existsLPTChemicalMake(Request $request)
    {
        if($request->term != "")
        {
            $fdMake = LPTChemical::select('lpt_make')->where('lpt_make', 'LIKE', $request->term.'%')->groupBy('lpt_make')->get();
            if($fdMake != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdMake as $dsKey)
                {
                    $output .= '<li parent-id="lpt_make" list-id="lpt_make_list" class="list-group-item" tabindex="0">'.$dsKey->lpt_make.'</li>';
                }
                $output .= '</ul>';
                return response()->json([
                    'makeList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Make Available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'makeList' => '',
                'response_code' => 1,
            ]);
        }
    }

    public function getGRNListForLPTChemical(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();
        // $used_grnd_ids = LPTChemical::select('lpt_grnd_id')->pluck('lpt_grnd_id')->toArray();

        $grn_data = GRN::select(
            'grn.grn_id',
            'grn_details.grnd_id',
            'grn.grn_number',
            'grn.grn_date',
            'suppliers.supplier_name',
            'grn.grn_challan_number',
            'grn.grn_challan_date',
            'grn_details.grnd_item_id as lpt_chemical_id',
            'item.item_name',
            'grn_details.grnd_qty',
            DB::raw("(grn_details.grnd_qty - (SELECT COUNT(lptc.lpt_grnd_id) FROM lpt_chemical AS lptc WHERE lptc.lpt_grnd_id = grn_details.grnd_id)) AS pend_grn_qty")
        )
        ->leftJoin('grn_details','grn_details.grnd_grn_id','=','grn.grn_id')
        ->leftJoin('item','item.id','=','grn_details.grnd_item_id')
        ->leftJoin('suppliers','suppliers.id','=','grn.grn_supplier_id')
        ->where('item.item_type','=',"lpt_chemical")
        // ->whereNotIn('grn_details.grnd_grn_id',$used_grnd_ids)
        ->whereIn('grn.year_id',$yearIds)
        ->having('pend_grn_qty','>',0)
        ->get();

        if($grn_data != null)
        {
            foreach($grn_data as $cpKey => $cpVal)
            {
                if($cpVal->grn_date != null)
                {
                    $cpVal->grn_date = Date::createFromFormat('Y-m-d', $cpVal->grn_date)->format('d/m/Y');
                }

                if($cpVal->grn_challan_date != null)
                {
                    $cpVal->grn_challan_date = Date::createFromFormat('Y-m-d', $cpVal->grn_challan_date)->format('d/m/Y');
                }
            }
        }

        if($grn_data != null)
        {
            return response()->json([
                'response_code' => '1',
                'grn_data' => $grn_data,
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '1',
                'grn_data' => []
            ]);
        }
    }

    public function getGRNPartDataForLPTChemical(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();
        $request->grnd_ids = explode(',', $request->grnd_ids);
        $grn_data = GRN::select(
            'grn.grn_id',
            'grn_details.grnd_id',
            'grn.grn_number',
            'grn.grn_date',
            'suppliers.supplier_name',
            'grn.grn_challan_number',
            'grn.grn_challan_date',
            'item.id as lpt_chemical_id',
        )
        ->leftJoin('grn_details','grn_details.grnd_grn_id','=','grn.grn_id')
        ->leftJoin('item','item.id','=','grn_details.grnd_item_id')
        ->leftJoin('suppliers','suppliers.id','=','grn.grn_supplier_id')
        ->where('item.item_type','=',"lpt_chemical")
        ->whereIn('grn_details.grnd_id', $request->grnd_ids)
        ->whereIn('grn.year_id',$yearIds)
        ->first();

        if($grn_data != null)
        {
            if($grn_data->grn_date != null)
            {
                $grn_data->grn_date = Date::createFromFormat('Y-m-d', $grn_data->grn_date)->format('d/m/Y');
            }

            if($grn_data->grn_challan_date != null)
            {
                $grn_data->grn_challan_date = Date::createFromFormat('Y-m-d', $grn_data->grn_challan_date)->format('d/m/Y');
            }
        }

        if($grn_data != null)
        {
            return response()->json([
                'response_code' => '1',
                'grn_data' => $grn_data,
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '1',
                'grn_data' => []
            ]);
        }
    }
}