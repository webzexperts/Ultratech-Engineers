<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use App\Models\Admin;
use App\Models\ItemOpening;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;


class ItemStockSummaryController extends Controller
{
    
    public function manage()
    {
        return view('manage.reports.manage-item_stock_summary');
    }

    public function index(ItemOpening $stock, Request $request, DataTables $datatables)
    {
        $yearIds = getCompanyYearIdsToTill();
        $current_location = getCurrentLocation();

        $LocationData = getCurrentLocation();
        $location_type = getCurrentLocation()->location_type;
    //     $stock = ItemOpening::select([

    //     'item.item_name','item_group.item_group','item.item_type','location.location_name','unit.unit','item_opening.io_stock_qty',
    //    'pend_inter.pending_qty as pend_inter',
    //    'supplier_return.pending_qty as supplier_return',
    //    'customer_return.pending_qty as customer_return',

    //      DB::raw('
    //         COALESCE(pend_inter.pending_qty, 0) +
    //         COALESCE(supplier_return.pending_qty, 0) +
    //         COALESCE(customer_return.pending_qty, 0)
    //         as total_stock
    //     ')
        
        
    //     ])
    //     ->leftJoin('purchase_indent_details as pid', 'pid.item_id', '=', 'item_opening.io_item_id')
    //    ->leftJoin('pending_purchase_indent_qty as pend_inter', 
    //     'pend_inter.pid_id', '=', 'pid.pid_id')

    //      ->leftJoin('supplier_dc_details as sdd', 'sdd.item_id', '=', 'item_opening.io_item_id')
    //      ->leftJoin('pending_supplier_dc_qty as supplier_return', 
    //     'supplier_return.sup_dcd_id', '=', 'sdd.sup_dcd_id')

    //     ->leftJoin('delivery_challan_customer_details as dccd', 'dccd.item_id', '=', 'item_opening.io_item_id')
    //      ->leftJoin('pending_delivery_challan_dc_qty as customer_return', 
    //     'customer_return.dc_detail_id', '=', 'dccd.dc_detail_id')

    //     ->leftJoin('item', 'item.id', '=', 'item_opening.io_item_id')
    //     ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
    //     ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
    //     ->leftJoin('location','location.location_id','item_opening.current_location_id');

        if($location_type == "HO") {
            $stock = DB::select("CALL item_stock_summary(?)", [0]);
        } else {        
            $stock = DB::select("CALL item_stock_summary(?)", [$LocationData->location_id]);
        }
        // dd($stock);
        $dataTable = DataTables::of($stock)
        
        // ->editColumn('main_group', function($stock) {
        //     return config('app.item_type')[$stock->main_group] ?? $stock->main_group;
        // })
        ->editColumn('item_type', function($stock) {
            return config('app.item_type')[$stock->item_type] ?? $stock->item_type;
        });
       
        return $dataTable
        ->make(true);
    }

}

?>