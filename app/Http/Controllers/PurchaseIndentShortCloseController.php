<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseIndent;
use App\Models\PurchaseIndentDetails;
use App\Models\PurchaseIndentShortClose;
use App\Models\PurchaseIndentShortClosePendingQty;
use App\Models\PurchaseOrderDetails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class PurchaseIndentShortCloseController extends Controller
{
    public function manage()
    {
        return view('manage.manage-purchase_indent_short_close');
    }

    public function index(PurchaseIndentShortClose $purchase_indent_sc_data, Request $request, DataTables $datatables)
    {
        $current_location_id = getCurrentLocation();
        $year_data = getCurrentYearData();
        $purchase_indent_sc_data = DB::table('pending_purchase_indent_qty as pend')->select([
            'purchase_indent_short_close.pisc_id',
            'purchase_indent_short_close.pisc_date',
            'purchase_indent.pi_no',
            'purchase_indent.pi_date',
            'item.item_name',
            'item.item_type',
            'purchase_indent_short_close.pisc_sc_qty',
            'purchase_indent_details.indent_qty',
            'pend.pending_qty',
            'reason.reason_name',
            'unit.unit',
            'from_location.location_name as from_location',
            'location.location_name as to_location',
            'purchase_indent_short_close.created_on',
            'purchase_indent_short_close.created_by',
            'purchase_indent_short_close.last_by',
            'purchase_indent_short_close.last_on',
        ])
        ->leftJoin('purchase_indent_short_close', 'purchase_indent_short_close.pisc_pid_id', '=', 'pend.pid_id')
        ->leftJoin('purchase_indent_details','purchase_indent_details.pid_id','=','pend.pid_id')
        ->leftJoin('purchase_indent','purchase_indent.pi_id','=','purchase_indent_details.pid_pi_id')
        ->leftJoin('item', 'item.id', '=', 'purchase_indent_details.item_id')
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        ->leftJoin('location', 'location.location_id', '=', 'purchase_indent.to_location_id')
        ->leftJoin('location as from_location', 'from_location.location_id', '=', 'purchase_indent.current_location_id')
        ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
        ->leftJoin('reason','reason.id','=','purchase_indent_short_close.pisc_sc_reason_id')
        ->where('purchase_indent_short_close.year_id', $year_data->id);
        if ($current_location_id->location_type != 'HO') {
            $purchase_indent_sc_data->where(function($q) use ($current_location_id) {
                $q->where('purchase_indent.current_location_id', $current_location_id->location_id)
                ->orWhere('purchase_indent.to_location_id', $current_location_id->location_id);
            });
        }

        $dataTable = DataTables::of($purchase_indent_sc_data)
        ->filterColumn('purchase_indent.pi_no', function($query, $keyword) {
            applySequenceSearch($query, $keyword, 'purchase_indent.pi_no');
        })
        ->editColumn('pi_date', function($purchase_indent_sc_data){
            if ($purchase_indent_sc_data->pi_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $purchase_indent_sc_data->pi_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('purchase_indent.pi_date', function ($q, $k) {
            applyDate($q, $k, 'purchase_indent.pi_date');
        })

        ->editColumn('pisc_date', function($purchase_indent_sc_data){
            if ($purchase_indent_sc_data->pisc_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $purchase_indent_sc_data->pisc_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('purchase_indent_short_close.pisc_date', function ($q, $k) {
            applyDate($q, $k, 'purchase_indent_short_close.pisc_date');
        })

        ->editColumn('pisc_sc_qty', function($purchase_indent_sc_data) {
            return $purchase_indent_sc_data->pisc_sc_qty > 0 ? number_format((float)$purchase_indent_sc_data->pisc_sc_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
         ->filterColumn('purchase_indent_short_close.pisc_sc_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('pisc_sc_qty')
                    ->orWhere('pisc_sc_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(pisc_sc_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('pisc_sc_qty', 'like', "%{$search}%");
            }
        })
        ->editColumn('indent_qty', function($purchase_indent_sc_data) {
            return $purchase_indent_sc_data->indent_qty > 0 ? number_format((float)$purchase_indent_sc_data->indent_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
         ->filterColumn('purchase_indent_details.indent_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('indent_qty')
                    ->orWhere('indent_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(indent_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('indent_qty', 'like', "%{$search}%");
            }
        })
        ->editColumn('pending_qty', function($purchase_indent_sc_data) {
            return $purchase_indent_sc_data->pending_qty > 0 ? number_format((float)$purchase_indent_sc_data->pending_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->editColumn('item_type', function ($purchase_indent_sc_data) {
            $types = getItemType();
            return $types[$purchase_indent_sc_data->item_type] 
                ?? $purchase_indent_sc_data->item_type;
        })

       
        
        ->addColumn('options',function($purchase_indent_sc_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("purchase_indent_short_close", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'purchase_indent_short_close');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'pisc_date' => 'required',
        ],
        [
            'pisc_date.required' => 'Enter Short Close Date',
        ]);

        $year_data = getCurrentYearData();
        DB::beginTransaction();
        try
        {
            $request->purchase_indent_sc_data = json_decode($request->purchase_indent_sc_data,true);
            if(isset($request->purchase_indent_sc_data) && !empty($request->purchase_indent_sc_data))
            {
                foreach($request->purchase_indent_sc_data as $ctKey => $ctVal)
                {
                        if(isset($ctVal['pid_id']) && $ctVal['pid_id'] != "")
                        {
                            $pendingQty =  DB::table('pending_purchase_indent_qty')
                            ->where('pid_id', $ctVal['pid_id'])
                            ->value('pending_qty');

                            if((float)$ctVal['pisc_sc_qty'] > (float)$pendingQty)
                            {
                                DB::rollBack();
                                return response()->json([
                                    'response_code' => '0',
                                    'response_message' => 'Purchase Indent Qty. Used.',
                                ]);
                            }
                        }

                        if(isset($ctVal['pid_id'])){

                            $piQtySum = round(PurchaseIndentDetails::where('pid_id',$ctVal['pid_id'])->sum('indent_qty'),3);

                            $usePurchaseOrderQtySum = round(PurchaseIndentShortClose::where('pisc_pid_id',$ctVal['pid_id'])->sum('pisc_sc_qty'),3);

                            $podQtySum = round(PurchaseOrderDetails::where('pod_pid_id',$ctVal['pid_id'])->sum('pod_po_qty'),3);


                            $CurrentQty = (float)$ctVal['pisc_sc_qty'];


                          $poQtySum = $usePurchaseOrderQtySum + $podQtySum + $CurrentQty;

                            if($piQtySum < $poQtySum){
                                DB::rollBack();
                                return response()->json([
                                    'response_code' => '0',
                                    'response_message' => 'Purchase Indent Qty. Is Used',
                                ]);
                            }

                        }
                            

                    if($ctVal != null)
                    {
                        $purchase_indent_sc_data =  PurchaseIndentShortClose::create([

                            'pisc_pid_id'=> (isset($ctVal['pid_id']) &&  $ctVal['pid_id'] != "") ? $ctVal['pid_id'] : null,

                            'pisc_date'=> Date::createFromFormat('d/m/Y', $request->pisc_date)->format('Y-m-d'),

                            'pisc_sc_qty'=>(isset($ctVal['pisc_sc_qty']) && $ctVal['pisc_sc_qty'] > 0) ? $ctVal['pisc_sc_qty'] : null,

                            'pisc_sc_reason_id' => (isset($ctVal['pisc_sc_reason_id']) && $ctVal['pisc_sc_reason_id'] != "") ? $ctVal['pisc_sc_reason_id'] : null,

                            'year_id' => $year_data->id,

                            'company_id' => Auth::user()->company_id,

                            'created_by' => Auth::user()->id,

                            'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                        ]);
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
                DB::rollBack();
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
    

    public function destroy(Request $request)
    {
        try
        {
            PurchaseIndentShortClose::where('pisc_id',$request->id)->delete();
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


    public function getPendingPurchaseIndentSC(Request $request)
    {

        $current_location = getCurrentLocation();
        $current_location_id = $current_location->location_id;
        $location_type = $current_location->location_type;
        $yearIds  = getCompanyYearIdsToTill();
        $data = DB::table('pending_purchase_indent_qty as pend')->select([
                'purchase_indent_details.pid_id','pi.pi_id','pi.pi_no','pi.pi_date','pi.to_location_id','item.item_name','item_group.item_group','item.item_type as main_group','purchase_indent_details.indent_qty','unit.unit','pend.pending_qty',
                'purchase_indent_details.remark','admin.person_name as indent_by','location.location_name', 'from_location.location_name as from_location'
            ])
            ->leftJoin('purchase_indent_details', 'purchase_indent_details.pid_id', '=', 'pend.pid_id')
            ->leftJoin('purchase_indent as pi', 'pi.pi_id', '=', 'purchase_indent_details.pid_pi_id')
            ->leftJoin('item', 'item.id', '=', 'purchase_indent_details.item_id')
            ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
            ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
            ->leftJoin('admin', 'admin.id', '=', 'pi.indent_by_user_id')
            ->leftJoin('location', 'location.location_id', '=', 'pi.to_location_id')
            ->leftJoin('location as from_location', 'from_location.location_id', '=', 'pi.current_location_id')
            ->whereIn('pi.year_id', $yearIds)
            ->where('pending_qty', '>', 0)
            ->when($location_type != 'HO', function ($q) use ($current_location_id) {
                $q->where(function ($sub) use ($current_location_id) {
                    $sub->where('pi.current_location_id', $current_location_id)
                        ->orWhere('pi.to_location_id', $current_location_id);
                });
            });
            $data = $data->get();


        $data = $data->map(function ($row) {

            if ($row->pi_date) {
                $row->pi_date = \Carbon\Carbon::parse($row->pi_date)->format('d/m/Y');
            }

            $row->main_group = config('app.item_type.' . ($row->main_group ?? '')) ?? '';

            return $row;
        });

        return response()->json([
            'response_code' => $data->count() > 0 ? '1' : '0',
            'purchase_indent_sc_data' => $data
        ]);
    }
}