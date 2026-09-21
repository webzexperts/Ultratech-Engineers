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


class ItemSrNoWiseStockSummaryController extends Controller
{
    
    public function manage()
    {
        return view('manage.reports.manage-item_sr_no_wise_stock_summary');
    }

    public function index(ItemOpening $stock, Request $request, DataTables $datatables)
    {
        $yearIds = getCompanyYearIdsToTill();
        $current_location = getCurrentLocation();

        $LocationData = getCurrentLocation();
        $location_type = getCurrentLocation()->location_type;

        if($location_type == "HO") {
            $stock = DB::select("CALL item_sr_no_wise_stock_summary(?)", [0]);
        } else {        
            $stock = DB::select("CALL item_sr_no_wise_stock_summary(?)", [$LocationData->location_id]);
        }
        // dd($stock);
        $dataTable = DataTables::of($stock)
        
        // ->editColumn('main_group', function($stock) {
        //     return config('app.item_type')[$stock->main_group] ?? $stock->main_group;
        // })
        ->editColumn('item_type', function($stock) {
            return config('app.item_type')[$stock->item_type] ?? $stock->item_type;
        })
        ->filter(function ($query) use ($request) {

            if ($request->has('search') && $request->search['value'] != '') {

                $keyword = strtolower($request->search['value']);

                $query->collection = $query->collection->filter(function ($row) use ($keyword) {

                    $itemType = strtolower(config('app.item_type')[$row->item_type] ?? '');

                    return str_contains($itemType, $keyword);
                });
            }
        });
       
        return $dataTable
        ->rawColumns(['total_stock'])
        ->make(true);
    }

}

?>