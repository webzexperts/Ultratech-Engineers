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


class PendingInterLocationTransferForGRNController extends Controller
{

    public function manage()
    {
        return view('manage.reports.manage-pending_inter_location_transfer_for_grn');
    }

    public function index(PurchaseOrderDetails $data, Request $request, DataTables $datatables)
    {
        $yearIds = getCompanyYearIdsToTill();
        $current_location = getCurrentLocation();
        $LocationData = getCurrentLocation()->location_id;

        $inter_transfer_data = DB::table('pending_inter_location_transfer_qty as pend')->select([
        'inter_location_transfer_details.inter_location_transfer_details_id','inter_location_transfer.dc_number','inter_location_transfer.dc_sequence','inter_location_transfer.dc_date','inter_location_transfer.dc_type_id',
        'item.item_name','item_group.item_group','item.item_type as main_group','inter_location_transfer_details.dc_qty','unit.unit', 'pend.pending_qty as pend_dc_qty', 'sr.name_for_display', 
        'inter_location_transfer_details.remark','admin.person_name as prepared_by','location.location_name','from_location.location_name as from_location_name'])
        ->leftJoin('inter_location_transfer_details', 'inter_location_transfer_details.inter_location_transfer_details_id', '=', 'pend.iltd_id')
        ->leftJoin('inter_location_transfer', 'inter_location_transfer.ilt_id', '=', 'inter_location_transfer_details.inter_location_transfer_id')
        ->leftJoin('item', 'item.id', '=', 'inter_location_transfer_details.item_id')
        ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        ->leftJoin('admin', 'admin.id', '=', 'inter_location_transfer.prepared_by_id')
        ->leftJoin('location', 'location.location_id', '=', 'inter_location_transfer.to_location_id')
        ->leftJoin('location as from_location', 'from_location.location_id', '=', 'inter_location_transfer.current_location_id')
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'inter_location_transfer_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'inter_location_transfer_details.sr_table_unique_id');
        })
        ->whereIn('inter_location_transfer.year_id', $yearIds)
        ->where('pend.pending_qty', '>', 0)
        // ->where('inter_location_transfer.to_location_id', $LocationData)
        ->when($current_location->location_type != 'HO', function ($q) use ($current_location) {
            $q->where(function ($sub) use ($current_location) {
                $sub->where('inter_location_transfer.current_location_id', $current_location->location_id)
                    ->orWhere('inter_location_transfer.to_location_id', $current_location->location_id);
            });
        }); 

        $dataTable = DataTables::of($inter_transfer_data)
        ->editColumn('dc_date', function($inter_transfer_data){
            if ($inter_transfer_data->dc_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $inter_transfer_data->dc_date)->format(DATE_FORMAT); return $formatedDate;
                } else {
                    return '';
                }
            })
        ->filterColumn('inter_location_transfer.dc_date', function ($q, $k) {
            applyDate($q, $k, 'inter_location_transfer.dc_date');
        })
        ->filterColumn('inter_location_transfer.dc_number', function($query, $keyword) {
            applySequenceSearch($query, $keyword, 'inter_location_transfer.dc_number');
        })

        ->editColumn('main_group', function($inter_transfer_data) {
            return config('app.item_type')[$inter_transfer_data->main_group] ?? $inter_transfer_data->main_group;
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
        ->editColumn('unit', function($inter_transfer_data) {
            if ($inter_transfer_data->dc_type_id == 'SQIN from Prod. Area') {
                return 'SQIN';
            }
            return $inter_transfer_data->unit;
        })
        ->filterColumn('unit.unit', function($query, $keyword) {
            $search = trim($keyword);
            $query->where(function($q) use ($search) {
                $q->where('unit.unit', 'like', "%{$search}%");
                if (stripos('SQIN', $search) !== false || stripos('SQ.IN', $search) !== false || stripos('SQ INCH', $search) !== false) {
                    $q->orWhere('inter_location_transfer.dc_type_id', 'SQIN from Prod. Area');
                }
            });
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
