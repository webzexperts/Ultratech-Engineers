<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetails;


class PendingServicePOforDCController extends Controller
{

    public function manage()
    {
        return view('manage.reports.manage-pending_service_po_for_dc');
    }

    public function index(PurchaseOrderDetails $data, Request $request, DataTables $datatables)
    {
        $yearIds = getCompanyYearIdsToTill();
        $current_location = getCurrentLocation();
      
        $dc_data = DB::table('pending_service_po_qty as pend')
        ->select(['service_po.ser_po_id','service_po.ser_po_number','service_po.ser_po_date','service_po.purpose','service_po.ref_no_date','bill_to.location_name as bill_to','for_location.location_name as for_location','item.item_name','item_group.item_group','item.item_type as main_group','pend.pending_qty','unit.unit','service_po_details.remark','admin.person_name as prepared_by','sr.name_for_display','pend.ser_pod_id','suppliers.supplier_name','service_po.ser_po_sequence'])
        ->leftJoin('service_po_details','service_po_details.ser_pod_id','=','pend.ser_pod_id')
        ->leftJoin('service_po', 'service_po.ser_po_id', '=', 'service_po_details.ser_pod_po_id')
        ->leftJoin('item','item.id','=','service_po_details.item_id')
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        ->leftJoin('admin', 'admin.id', '=', 'service_po.prepared_by_user_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')  
        ->leftJoin('suppliers','suppliers.id','=','service_po.supplier_id')  
        ->leftJoin('location as bill_to', 'bill_to.location_id', '=', 'service_po.bill_to_id')
        ->leftJoin('location as for_location', 'for_location.location_id', '=', 'service_po.for_location_id')
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'service_po_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'service_po_details.sr_table_unique_id');
        })
        // ->where('service_po.supplier_id',$request->supplier_id)
        ->Where('service_po.for_location_id', $current_location->location_id)
        ->whereIn('service_po.year_id',$yearIds)  
        ->where('pend.pending_qty', '>', 0)
        ->when($current_location->location_type != 'HO', function ($q) use ($current_location) {
            $q->where(function ($sub) use ($current_location) {
                $sub->where('service_po.current_location_id', $current_location->location_id)
                    ->orWhere('service_po.for_location_id', $current_location->location_id);
            });
        }); 

        $dataTable = DataTables::of($dc_data)
        ->editColumn('service_po.ser_po_date', function($dc_data){
            if ($dc_data->ser_po_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $dc_data->ser_po_date)->format(DATE_FORMAT); return $formatedDate;
                } else {
                    return '';
                }
            })
        ->filterColumn('service_po.ser_po_number', function($query, $keyword) {
            applySequenceSearch($query, $keyword, 'service_po.ser_po_number');
        })
        ->filterColumn('service_po.ser_po_date', function ($q, $k) {
            applyDate($q, $k, 'service_po.ser_po_date');
        })
        // ->editColumn('pod_del_date', function($dc_data){
        //     if ($dc_data->pod_del_date != null) {
        //         $formatedDate = Date::createFromFormat('Y-m-d', $dc_data->pod_del_date)->format(DATE_FORMAT); return $formatedDate;
        //     } else {
        //         return '';
        //     }
        // })
        // ->filterColumn('purchase_order_details.pod_del_date', function ($q, $k) {
        //     applyDate($q, $k, 'purchase_order_details.pod_del_date');
        // })
        // ->editColumn('pod_po_qty', function($dc_data) {
        //     return $dc_data->pod_po_qty > 0 ? number_format((float)$dc_data->pod_po_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        // })
        ->editColumn('main_group', function($dc_data) {
            return config('app.item_type')[$dc_data->main_group] ?? $dc_data->main_group;
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
        });
        // ->filterColumn('purchase_indent_details.pod_po_qty', function($query, $keyword) {
        //     $search = trim($keyword);
        //     if (preg_match('/^0(\.0{0,3})?$/', $search)) {
        //         $query->where(function($q) {
        //             $q->whereNull('pod_po_qty')
        //             ->orWhere('pod_po_qty', 0);
        //         });
        //     }
        //     elseif (is_numeric($search)) {
        //         $query->whereRaw("CAST(pod_po_qty AS CHAR) LIKE ?", ["{$search}%"]);
        //     }
        //     else {
        //         $query->where('pod_po_qty', 'like', "%{$search}%");
        //     }
        // });
        return $dataTable
        ->rawColumns(['last_by','last_on','created_by','created_on','options'])
        ->make(true);
    }
}
