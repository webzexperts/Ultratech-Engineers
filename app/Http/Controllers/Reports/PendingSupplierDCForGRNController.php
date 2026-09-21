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


class PendingSupplierDCForGRNController extends Controller
{

    public function manage()
    {
        return view('manage.reports.manage-pending_supplier_dc_for_grn');
    }

    public function index(PurchaseOrderDetails $data, Request $request, DataTables $datatables)
    {
        $yearIds = getCompanyYearIdsToTill();
        $current_location = getCurrentLocation();
        $itemTypes = getItemType();

        $dc_data = DB::table('pending_supplier_dc_qty as pend')->select(['supplier_dc_details.sup_dcd_id','supplier_dc.sup_dc_id','supplier_dc.sup_dc_number','supplier_dc.sup_dc_date','supplier_dc.sup_dc_sequence','supplier_dc.ref_no_date','item.item_name','item_group.item_group','item.item_type as main_group','pend.pending_qty','unit.unit','sr.name_for_display','supplier_dc_details.remark','admin.person_name as prepared_by','supplier_dc.sup_dc_type_id','supplier_dc.supplier_id','suppliers.supplier_name'])
        ->leftJoin('supplier_dc_details', 'supplier_dc_details.sup_dcd_id', '=', 'pend.sup_dcd_id')
        ->leftJoin('supplier_dc', 'supplier_dc.sup_dc_id', '=', 'supplier_dc_details.sup_dcd_dc_id') 
        ->leftJoin('item','item.id','=','supplier_dc_details.item_id')  
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        ->leftJoin('admin', 'admin.id', '=', 'supplier_dc.prepared_by_user_id')
        ->leftJoin('suppliers','suppliers.id','=','supplier_dc.supplier_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')  
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'supplier_dc_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'supplier_dc_details.sr_table_unique_id');
        })
        // ->where('supplier_dc.supplier_id',$request->grn_supplier_id)
        ->whereIn('supplier_dc.year_id',$yearIds)
        ->where('pend.pending_qty', '>', 0)
        ->when($current_location->location_type != 'HO', function ($q) use ($current_location) {
            $q->where(function ($sub) use ($current_location) {
                $sub->where('supplier_dc.current_location_id', $current_location->location_id);
                    // ->orWhere('supplier_dc.to_location_id', $current_location->location_id);
            });
        });

        $dataTable = DataTables::of($dc_data)
        ->editColumn('sup_dc_date', function($dc_data){
            if ($dc_data->sup_dc_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $dc_data->sup_dc_date)->format(DATE_FORMAT); return $formatedDate;
                } else {
                    return '';
                }
            })
        ->filterColumn('supplier_dc.sup_dc_date', function ($q, $k) {
            applyDate($q, $k, 'supplier_dc.sup_dc_date');
        })
        ->filterColumn('supplier_dc.dc_number', function($query, $keyword) {
            applySequenceSearch($query, $keyword, 'supplier_dc.dc_number');
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
