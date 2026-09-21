<?php
namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Transaction\SupplierDC;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use App\Models\Admin;
use App\Models\ItemOpening;
use App\Models\Transaction\DeliveryChallanCustomer;
use App\Models\Transaction\DeliveryChallanCustomerDetails;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class DeliveryChallanCustomerReportController extends Controller
{
    public function manage()
    {
        return view('manage.reports.manage-delivery_challan_customer_report');
    }

    public function index(DeliveryChallanCustomer $grn_data, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $current_location = getCurrentLocation()->location_id;
        $dc_data = DeliveryChallanCustomer::select([

            'delivery_challan_customer.dc_id',
            'delivery_challan_customer.dc_number',
            'delivery_challan_customer.dc_sequence',
            'delivery_challan_customer.dc_date',
            'customers.customer',
            'item.item_name',
            'item_group.item_group',
            'item.item_type as main_group',
            'item.item_type as main_group_key',
            'unit.unit',
            'sr.name_for_display', 
            'delivery_challan_customer_details.sr_table_unique_id',
            'delivery_challan_customer_details.sr_table_pk_id',
            'delivery_challan_customer_details.dc_qty',
            'delivery_challan_customer_details.item_id',
            'delivery_challan_customer_details.remark',
            'prepared_by.person_name as prepared_by',
            'delivery_challan_customer.created_on',
            'delivery_challan_customer.created_by',
            'delivery_challan_customer.last_by',
            'delivery_challan_customer.last_on'
        ])
        ->leftJoin('delivery_challan_customer_details','delivery_challan_customer_details.dc_id','=','delivery_challan_customer.dc_id')
        ->leftJoin('admin as prepared_by','prepared_by.id','=','delivery_challan_customer.prepared_by_user_id')
        ->leftJoin('item','item.id','=','delivery_challan_customer_details.item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->leftJoin('customers','customers.id','=','delivery_challan_customer.customer_id')

        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'delivery_challan_customer_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'delivery_challan_customer_details.sr_table_unique_id');
        })
        ->where('delivery_challan_customer.year_id', $year_data->id)
        ->where('delivery_challan_customer.current_location_id', $current_location);

        if($request->from_date != "" && $request->to_date != "") {
            $from = Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $to = Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $dc_data->whereDate('delivery_challan_customer.dc_date','>=',$from);
            $dc_data->whereDate('delivery_challan_customer.dc_date','<=',$to);
        } else if($request->from_date != "") {
            $from = Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $dc_data->where('delivery_challan_customer.dc_date','>=',$from);
        } else if($request->to_date != "") {
            $to = Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $dc_data->where('delivery_challan_customer.dc_date','<=',$to);
        }

        $dataTable = DataTables::of($dc_data)
        ->filterColumn('delivery_challan_customer.dc_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'delivery_challan_customer.dc_number');
        })
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
        
        ->editColumn('dc_qty', function($inquiry_data) {
            return $inquiry_data->dc_qty > 0 ? number_format((float)$inquiry_data->dc_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('delivery_challan_customer_details.dc_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('dc_qty')
                    ->orWhere('dc_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(dc_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('dc_qty', 'like', "%{$search}%");
            }
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
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'delivery_challan_customer');
        return $dataTable
        // ->rawColumns(['options','sr_no','last_by','last_on','created_by','created_on'])
        ->make(true);
    }
}





?>