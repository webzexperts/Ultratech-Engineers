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


class PendingItemReturnFromCustomerController extends Controller
{

    public function manage()
    {
        return view('manage.reports.manage-pending_item_return_from_customer');
    }

    public function index(PurchaseOrderDetails $data, Request $request, DataTables $datatables)
    {
        $yearIds = getCompanyYearIdsToTill();
        $current_location = getCurrentLocation();

        $itemTypes = getItemType();

        $dc_data = DB::table('pending_delivery_challan_dc_qty as pend')
        ->select([
            'delivery_challan_customer_details.dc_detail_id',
            'delivery_challan_customer.dc_number',
            'delivery_challan_customer.dc_sequence',
            'delivery_challan_customer.dc_date',
            'item.item_name','item_group.item_group',
            'item.item_type as main_group',
            'pend.pending_qty','unit.unit',
            'delivery_challan_customer_details.remark',
            'admin.person_name as prepared_by',
            'customers.customer',
            'sr.name_for_display','pend.dc_detail_id'])
        ->leftJoin('delivery_challan_customer_details','delivery_challan_customer_details.dc_detail_id','=','pend.dc_detail_id')
        ->leftJoin('delivery_challan_customer', 'delivery_challan_customer.dc_id', '=', 'delivery_challan_customer_details.dc_id')    
        ->leftJoin('item','item.id','=','delivery_challan_customer_details.item_id')  
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        ->leftJoin('admin', 'admin.id', '=', 'delivery_challan_customer.prepared_by_user_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')  
        ->leftJoin('customers','customers.id','=','delivery_challan_customer.customer_id')
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'delivery_challan_customer_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'delivery_challan_customer_details.sr_table_unique_id');
        })
        // ->where('delivery_challan_customer.customer_id',$request->customer_id)
        // ->Where('delivery_challan_customer.current_location_id', $current_location->location_id)
        ->whereIn('delivery_challan_customer.year_id',$yearIds)  
        ->where('pend.pending_qty', '>', 0)
        ->when($current_location->location_type != 'HO', function ($q) use ($current_location) {
            $q->where(function ($sub) use ($current_location) {
                $sub->where('delivery_challan_customer.current_location_id', $current_location->location_id);
                    // ->orWhere('delivery_challan_customer.to_location_id', $current_location->location_id);
            });
        });

        $dataTable = DataTables::of($dc_data)
        ->editColumn('dc_date', function($dc_data){
            if ($dc_data->dc_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $dc_data->dc_date)->format(DATE_FORMAT); return $formatedDate;
                } else {
                    return '';
                }
            })
        ->filterColumn('delivery_challan_customer.dc_date', function ($q, $k) {
            applyDate($q, $k, 'delivery_challan_customer.dc_date');
        })
        ->filterColumn('delivery_challan_customer.dc_number', function($query, $keyword) {
            applySequenceSearch($query, $keyword, 'delivery_challan_customer.dc_number');
        })

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
        return $dataTable
        ->rawColumns(['last_by','last_on','created_by','created_on','options'])
        ->make(true);
    }
}
