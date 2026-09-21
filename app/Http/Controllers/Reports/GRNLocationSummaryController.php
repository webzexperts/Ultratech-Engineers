<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Transaction\GRNLocation;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use App\Models\Admin;
use App\Models\Transaction\GRNSupplierDetails;
use App\Models\Item;
use App\Models\MaterialMpt;
use App\Models\ProbeUT;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetails;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use App\Models\EquipmentMPT;
use App\Models\LPTChemical;
use App\Models\EquipmentUT;



class GRNLocationSummaryController extends Controller
{
    //
    public function manage()
    {
        return view('manage.reports.manage-grn_location_summary');
    }

        public function index(GRNLocation $grn_loc_data, Request $request, DataTables $datatables)
    {

        $year_data = getCurrentYearData();
        $current_location = getCurrentLocation()->location_id;
        $grn_loc_data = GRNLocation::select([
            'grn_location.grn_loc_id',
            'inter_location_transfer.dc_type_id',
            'grn_location.grn_loc_number',
            'grn_location.grn_loc_sequence',
            'grn_location.grn_loc_number',
            'grn_location.grn_loc_date',
            'to_location.location_name',
            'inter_location_transfer.dc_number',
            'inter_location_transfer.dc_date',
            'item.item_name',
            'item_group.item_group',
            'item.item_type',
            'sr.name_for_display',
            'grn_location_details.qty',
            'grn_location_details.remark',
            'unit.unit',           
            'prepared_by.person_name as prepared_by',
            'grn_location.created_on',
            'grn_location.created_by',
            'grn_location.last_by',
            'grn_location.last_on'
        ])
        ->leftJoin('grn_location_details','grn_location_details.grn_locd_grn_id','=','grn_location.grn_loc_id')
        ->leftJoin('admin as prepared_by','prepared_by.id','=','grn_location.prepared_by_user_id')
        ->leftJoin('item','item.id','=','grn_location_details.item_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')        
        ->leftJoin('inter_location_transfer_details', 'inter_location_transfer_details.inter_location_transfer_details_id', '=', 'grn_location_details.inter_location_transfer_details_id')
        ->leftJoin('inter_location_transfer', 'inter_location_transfer.ilt_id', '=', 'inter_location_transfer_details.inter_location_transfer_id')
        ->leftJoin('location as to_location', 'to_location.location_id', '=', 'inter_location_transfer.to_location_id')
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'grn_location_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'grn_location_details.sr_table_unique_id');
        })
        ->where('grn_location.year_id', $year_data->id)
        ->where('grn_location.current_location_id',$current_location);
        if($request->from_date != "" && $request->to_date != "") {
            $from = Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $to = Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $grn_loc_data->whereDate('grn_location.grn_loc_date','>=',$from);
            $grn_loc_data->whereDate('grn_location.grn_loc_date','<=',$to);
        } else if($request->from_date != "") {
            $from = Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $grn_loc_data->where('grn_location.grn_loc_date','>=',$from);
        } else if($request->to_date != "") {
            $to = Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $grn_loc_data->where('grn_location.grn_loc_date','<=',$to);
        }


        $dataTable = DataTables::of($grn_loc_data)
        ->filterColumn('grn_location.grn_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'grn_location.grn_number');
        })
        
         ->editColumn('item_type', function($grn_loc_data) {
            return config('app.item_type')[$grn_loc_data->item_type] ?? $grn_loc_data->item_type;
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
        ->editColumn('grn_loc_date', function($grn_loc_data){
            if ($grn_loc_data->grn_loc_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $grn_loc_data->grn_loc_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('grn_location.grn_loc_date', function ($q, $k) {
            applyDate($q, $k, 'grn_location.grn_loc_date');
        })
       
        ->editColumn('dc_date', function($grn_loc_data){
            if ($grn_loc_data->dc_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $grn_loc_data->dc_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('inter_location_transfer.dc_date', function ($q, $k) {
            applyDate($q, $k, 'inter_location_transfer.dc_date');
        })

        ->editColumn('qty', function($inquiry_data) {
            return $inquiry_data->qty > 0 ? number_format((float)$inquiry_data->qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('grn_location_details.qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('qty')
                    ->orWhere('qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('qty', 'like', "%{$search}%");
            }
        })
        ->editColumn('unit', function($grn_loc_data) {
            if ($grn_loc_data->dc_type_id == 'SQIN from Prod. Area') {
                return 'SQIN';
            }
            return $grn_loc_data->unit;
        })
        ->filterColumn('unit.unit', function($query, $keyword) {
            $search = trim($keyword);
            $query->where(function($q) use ($search) {
                $q->where(function($sub_q) use ($search) {
                    $sub_q->where('unit.unit', 'like', "%{$search}%")
                          ->where(function($sub_q2) {
                              $sub_q2->where('inter_location_transfer.dc_type_id', '!=', 'SQIN from Prod. Area')
                                     ->orWhereNull('inter_location_transfer.dc_type_id');
                          });
                });
                if (stripos('SQIN', $search) !== false || stripos('SQ.IN', $search) !== false || stripos('SQ INCH', $search) !== false) {
                    $q->orWhere('inter_location_transfer.dc_type_id', 'SQIN from Prod. Area');
                }
            });
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'grn_location');
        return $dataTable
        // ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

}

?>