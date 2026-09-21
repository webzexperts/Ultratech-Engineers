<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\ServicePODetails;
use App\Models\Transaction\ServicePOShortClose;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ServicePOShortCloseController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-service_po_short_close');
    }

    public function index(ServicePOShortClose $ser_po_data, Request $request, DataTables $datatables)
    {
        $current_location_id = getCurrentLocation()->location_id;
        $year_data = getCurrentYearData();
        $ser_po_data = ServicePOShortClose::select([

            'service_po.ser_po_sequence',
            'service_po.ser_po_id',
            'service_po.ser_po_number',
            'service_po.ser_po_date',
            'suppliers.supplier_name',
            'service_po.purpose',
            'service_po.ref_no_date',
            'bill_to.location_name as bill_to',
            'for_location.location_name as for_location',
            'item.item_name',
            'item_group.item_group',
            'item.item_type as main_group',
            'item.item_type as main_group_key',
            'sr.name_for_display',
            'service_po_details.po_qty',
            'pend.pending_qty',
            'unit.unit',
            'reason.reason_name',
            'service_po_short_close.ser_po_sc_id',
            'service_po_short_close.ser_po_sc_date',
            'service_po_short_close.ser_po_sc_qty',
            'service_po_short_close.created_on',
            'service_po_short_close.created_by',
            
        ])

        ->leftJoin('service_po_details','service_po_details.ser_pod_id','=','service_po_short_close.ser_po_sc_pod_id')
        ->leftJoin('service_po','service_po.ser_po_id','=','service_po_details.ser_pod_po_id')
        ->leftJoin('suppliers','suppliers.id','=','service_po.supplier_id')
        ->leftJoin('location as bill_to','bill_to.location_id','=','service_po.bill_to_id')
        ->leftJoin('location as for_location','for_location.location_id','=','service_po.for_location_id')
        ->leftJoin('reason','reason.id','=','service_po_short_close.ser_po_sc_reason_id')
        ->leftJoin('item','item.id','=','service_po_details.item_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'service_po_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'service_po_details.sr_table_unique_id');
        })
        ->leftJoin('pending_service_po_qty as pend', function ($join) {
            $join->on('pend.ser_pod_id', '=', 'service_po_details.ser_pod_id');
        })
        ->where('service_po_short_close.year_id', $year_data->id);


        $dataTable = DataTables::of($ser_po_data)

         ->editColumn('ser_po_sc_date', function($ser_po_data){
            if ($ser_po_data->ser_po_sc_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $ser_po_data->ser_po_sc_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('service_po_short_close.ser_po_sc_date', function ($q, $k) {
            applyDate($q, $k, 'service_po_short_close.ser_po_sc_date');
        })
        ->editColumn('ser_po_date', function($ser_po_data){
            if ($ser_po_data->ser_po_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $ser_po_data->ser_po_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('service_po.ser_po_date', function ($q, $k) {
            applyDate($q, $k, 'service_po.ser_po_date');
        })
        ->editColumn('main_group', function($ser_po_data) {
            return config('app.item_type')[$ser_po_data->main_group] ?? $ser_po_data->main_group;
        })
        ->filterColumn('item.item_type', function($query, $keyword) {

            $itemTypes = config('app.item_type');
            $matchedKeys = [];
            foreach ($itemTypes as $key => $value) {
                if (stripos($value, $keyword) !== false) {
                    $matchedKeys[] = $key;
                }
            }
            if (!empty($matchedKeys)) {
                $query->whereIn('item.item_type', $matchedKeys);
            } else {
                $query->where('item.item_type', 'like', "%{$keyword}%");
            }
        })
        ->editColumn('po_qty', function($po_data) {
            return $po_data->po_qty > 0 ? number_format((float)$po_data->po_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('service_po_details.po_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('po_qty')
                    ->orWhere('po_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(po_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('po_qty', 'like', "%{$search}%");
            }
        })

        ->editColumn('ser_po_sc_qty', function($po_data) {
            return $po_data->ser_po_sc_qty > 0 ? number_format((float)$po_data->ser_po_sc_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('service_po_short_close.ser_po_sc_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('ser_po_sc_qty')
                    ->orWhere('ser_po_sc_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(ser_po_sc_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('ser_po_sc_qty', 'like', "%{$search}%");
            }
        })
       
        ->filterColumn('service_po.ser_po_number', function($query, $keyword) {
            applySequenceSearch($query, $keyword, 'service_po.ser_po_number');
        })
        
        ->addColumn('options',function($po_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';


                if (hasAccess("service_po_short_close", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'service_po');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on','options'])
        ->make(true);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'ser_po_sc_date' => 'required',
        ],
        [
            'ser_po_sc_date.required' => '12',
        ]);

        $year_data = getCurrentYearData();
        $LocationData = getCurrentLocation();
        DB::beginTransaction();
        try
        {
            $request->service_po_sc_data = json_decode($request->service_po_sc_data,true);

            if(isset($request->service_po_sc_data) && !empty($request->service_po_sc_data))
            {
                foreach($request->service_po_sc_data as $ctKey => $ctVal)
                {
                        if(isset($ctVal['ser_pod_id']) && $ctVal['ser_pod_id'] != "")
                        {
                            $pendingQty =  DB::table('pending_service_po_qty')
                            ->where('ser_pod_id', $ctVal['ser_pod_id'])
                            ->value('pending_qty');

                            if((float)$ctVal['ser_po_sc_qty'] > (float)$pendingQty)
                            {
                                DB::rollBack();
                                return response()->json([
                                    'response_code' => '0',
                                    'response_message' => 'Service PO Qty. Used.',
                                ]);
                            }
                        }

                        if(isset($ctVal['ser_pod_id'])){

                            $spdQtySum = round(ServicePODetails::where('ser_pod_id',$ctVal['ser_pod_id'])->sum('po_qty'),3);

                            $usePurchaseOrderQtySum = round(ServicePOShortClose::where('ser_po_sc_pod_id',$ctVal['ser_pod_id'])->sum('ser_po_sc_qty'),3);

                            //podQtySum = round(PurchaseOrderDetails::where('pod_pid_id',$ctVal['pid_id'])->sum('pod_po_qty'),3);

                            $CurrentQty = (float)$ctVal['ser_po_sc_qty'];

                            $poQtySum = $usePurchaseOrderQtySum + $CurrentQty;
                            //   $poQtySum = $usePurchaseOrderQtySum + $podQtySum + $CurrentQty;

                            if($spdQtySum < $poQtySum){
                                DB::rollBack();
                                return response()->json([
                                    'response_code' => '0',
                                    'response_message' => 'Service PO Qty. Is Used',
                                ]);
                            }

                        }
                            

                    if($ctVal != null)
                    {
                        $service_po_sc_data =  ServicePOShortClose::create([

                            'ser_po_sc_pod_id'=> (isset($ctVal['ser_pod_id']) &&  $ctVal['ser_pod_id'] != "") ? $ctVal['ser_pod_id'] : null,

                            'ser_po_sc_date'=> Date::createFromFormat('d/m/Y', $request->ser_po_sc_date)->format('Y-m-d'),

                            'ser_po_sc_qty'=>(isset($ctVal['ser_po_sc_qty']) && $ctVal['ser_po_sc_qty'] > 0) ? $ctVal['ser_po_sc_qty'] : null,

                            'ser_po_sc_reason_id' => (isset($ctVal['ser_po_sc_reason_id']) && $ctVal['ser_po_sc_reason_id'] != "") ? $ctVal['ser_po_sc_reason_id'] : null,

                            'year_id' => $year_data->id,

                            'company_id' => Auth::user()->company_id,

                            'created_by' => Auth::user()->id,

                            'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                            'previous_status_transaction_id' => null,
                            'current_status_transaction_id'  => null,
                        ]);

                        if(!empty($ctVal['sr_table_pk_id'])){
                                changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Service PO', 'Active', $LocationData->location_id, 'Delete', $service_po_sc_data);
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
            // DB::rollBack();
            // return response()->json([
            //     'response_code' => '0',
            //     'response_message' => getResponseMessage('store_error'),
            //     'original_error' => $e->getMessage()
            // ]);
            DB::rollBack();
            $message = $e->getMessage();

            if (in_array($message, [
                    'Insufficient Stock',
                    'Invalid Location Code',
                    'Invalid Item'
                ]) || str_contains($message, 'Sr. No. Is Already') || str_contains($message, "You Can't Delete")
            ) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            } else{
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('store_error'),
                    'original_error' => $e->getMessage()
                ]);
            }
        }
    }
    

    public function destroy(Request $request)
    {
        try
        {
             $LocationData = getCurrentLocation()->location_id;
             $SCDetails = ServicePOShortClose::where('ser_po_sc_id', $request->id)->first();

            $spDetails = ServicePODetails::where('ser_pod_id',$SCDetails->ser_po_sc_pod_id)->first();

            if($spDetails->sr_table_unique_id != null || $spDetails->sr_table_unique_id != ''){
                changeStatusAndLocationForSrNo($spDetails->sr_table_unique_id,$spDetails->sr_table_pk_id,'Active','Service PO',$LocationData,'Delete', $spDetails);
            }
            ServicePOShortClose::where('ser_po_sc_id',$request->id)->delete();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        }
        catch(\Exception $e)
        {
            report($e);
            DB::rollBack(); 
            $message = $e->getMessage();
            if(isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451)
            {
                $error_msg = "This Is Used Somewhere, You Can't Delete";
            }
             else if(in_array($message, [
                    'Insufficient Stock',
                    'Invalid Location Code',
                    'Invalid Item'
                ]) || str_contains($message, 'Sr. No. Is Already') || str_contains($message, "You Can't Delete"))
            {
                $error_msg = $message;
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


    public function getPendingServicePOSC(Request $request)
    {

        $current_location    = getCurrentLocation();
        $current_location_id = $current_location->location_id;
        $location_type       = $current_location->location_type;
        $yearIds             = getCompanyYearIdsToTill();
        $data = DB::table('pending_service_po_qty as pend')->select([

                'service_po_details.ser_pod_id',
                'sp.ser_po_id','sp.ser_po_number',
                'sp.ser_po_date',
                'sp.purpose','sp.ref_no_date',
                'suppliers.supplier_name',
                'item.item_name',
                'item_group.item_group',
                'item.item_type as main_group',
                'service_po_details.po_qty',
                'service_po_details.sr_table_unique_id',
                'service_po_details.sr_table_pk_id',
                'unit.unit',
                'service_po_details.del_date',
                'pend.pending_qty',
                'service_po_details.remark',
                'admin.person_name as prepared_by',
                'bill_to.location_name as bill_to',
                'for_location.location_name as for_location',
                'sr.name_for_display',

            ])
            ->leftJoin('service_po_details', 'service_po_details.ser_pod_id', '=', 'pend.ser_pod_id')
            ->leftJoin('service_po as sp', 'sp.ser_po_id', '=', 'service_po_details.ser_pod_po_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'sp.supplier_id')
            ->leftJoin('item', 'item.id', '=', 'service_po_details.item_id')
            ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
            ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
            ->leftJoin('admin', 'admin.id', '=', 'sp.prepared_by_user_id')
            ->leftJoin('location as bill_to', 'bill_to.location_id', '=', 'sp.bill_to_id')
            ->leftJoin('location as for_location', 'for_location.location_id', '=', 'sp.for_location_id')
            ->whereIn('sp.year_id', $yearIds)
            ->where('pending_qty', '>', 0)

            ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
                $join->on('sr.sr_table_pk_id', '=', 'service_po_details.sr_table_pk_id')
                    ->on('sr.sr_table_unique_id', '=', 'service_po_details.sr_table_unique_id');
            });
            // ->when($location_type != 'HO', function ($q) use ($current_location_id) {
            //     $q->where(function ($sub) use ($current_location_id) {
            //         $sub->where('sp.current_location_id', $current_location_id)
            //             ->orWhere('sp.to_location_id', $current_location_id);
            //     });
            // });
            $data = $data->get();


        $data = $data->map(function ($row) {

            if ($row->ser_po_date) {
                $row->ser_po_date = \Carbon\Carbon::parse($row->ser_po_date)->format('d/m/Y');
            }
            if ($row->del_date) {
                $row->del_date = \Carbon\Carbon::parse($row->del_date)->format('d/m/Y');
            }

            $row->main_group = config('app.item_type.' . ($row->main_group ?? '')) ?? '';

            return $row;
        });

        return response()->json([
            'response_code' => $data->count() > 0 ? '1' : '0',
            'service_po_sc_data' => $data
        ]);
    }
   
}