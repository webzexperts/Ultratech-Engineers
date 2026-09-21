<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Transaction\InterLocationTransfer;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use App\Models\Admin;
use App\Models\Transaction\InterLocationTransferDetails;
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



class InterLocationTransferSummaryController extends Controller
{
    public function manage()
    {
        return view('manage.reports.manage-inter_location_transfer_summary');
    }

    public function index(InterLocationTransfer $grn_data, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $current_location = getCurrentLocation()->location_id;
        $ilt_data = InterLocationTransfer::select([
            'inter_location_transfer.ilt_id',
            'inter_location_transfer.dc_type_id',
            'inter_location_transfer.dc_number',
            'inter_location_transfer.dc_sequence',
            'inter_location_transfer.dc_date',
            'to_location.location_name as to_location',
            'item.item_name',
            'item_group.item_group',
            'item.item_type as main_group',
            'item.item_type as main_group_key',
            'unit.unit',
            'sr.name_for_display', 
            'inter_location_transfer_details.sr_table_unique_id',
            'inter_location_transfer_details.sr_table_pk_id',
            'inter_location_transfer_details.dc_qty',
            'inter_location_transfer_details.item_id',
            'inter_location_transfer_details.remark',
            'prepared_by.person_name as prepared_by',
            'inter_location_transfer.created_on',
            'inter_location_transfer.created_by',
            'inter_location_transfer.last_by',
            'inter_location_transfer.last_on'
        ])
        ->leftJoin('inter_location_transfer_details','inter_location_transfer_details.inter_location_transfer_id','=','inter_location_transfer.ilt_id')
        ->leftJoin('admin as prepared_by','prepared_by.id','=','inter_location_transfer.prepared_by_id')
        ->leftJoin('location as to_location','to_location.location_id','=','inter_location_transfer.to_location_id')
        ->leftJoin('item','item.id','=','inter_location_transfer_details.item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'inter_location_transfer_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'inter_location_transfer_details.sr_table_unique_id');
        })
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->where('inter_location_transfer.year_id', $year_data->id)
        ->where('inter_location_transfer.current_location_id', $current_location);
        if($request->from_date != "" && $request->to_date != "") {
            $from = Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $to = Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $ilt_data->whereDate('inter_location_transfer.dc_date','>=',$from);
            $ilt_data->whereDate('inter_location_transfer.dc_date','<=',$to);
        } else if($request->from_date != "") {
            $from = Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $ilt_data->where('inter_location_transfer.dc_date','>=',$from);
        } else if($request->to_date != "") {
            $to = Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $ilt_data->where('inter_location_transfer.dc_date','<=',$to);
        }
        $dataTable = DataTables::of($ilt_data)
        ->filterColumn('inter_location_transfer.dc_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'inter_location_transfer.dc_number');
        })
        ->editColumn('dc_date', function($ilt_data){
            if ($ilt_data->dc_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $ilt_data->dc_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('inter_location_transfer.dc_date', function ($q, $k) {
            applyDate($q, $k, 'inter_location_transfer.dc_date');
        })
        
        ->editColumn('dc_qty', function($inquiry_data) {
            return $inquiry_data->dc_qty > 0 ? number_format((float)$inquiry_data->dc_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->editColumn('main_group', function($ilt_data) {
            return config('app.item_type')[$ilt_data->main_group] ?? $ilt_data->main_group;
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
        ->filterColumn('inter_location_transfer_details.dc_qty', function($query, $keyword) {
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
        ->editColumn('unit', function($ilt_data) {
            if ($ilt_data->dc_type_id == 'SQIN from Prod. Area') {
                return 'SQIN';
            }
            return $ilt_data->unit;
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
        // ->filterColumn('sr_no', function($query, $keyword) {
        //     $locationId = getCurrentLocation()->location_id;

        //     $query->whereIn('inter_location_transfer_details.item_id', function($q) use ($keyword, $locationId) {
        //         $q->select('item_id')
        //         ->from('item_sr_no_name_for_display')
        //         ->where('status', 'Active')
        //         ->where('current_location_id', $locationId)
        //         ->where('name_for_display', 'like', "%{$keyword}%")
        //         ->whereColumn('sr_table_unique_id', 'item.item_type'); // important
        //     });
        // })
        // ->addColumn('sr_no', function($ilt_data) {
        //     $srNo = getSrNo($ilt_data->main_group_key, $ilt_data->item_id)->first();

        //     return $srNo ? $srNo->name_for_display : '';
        // });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'inter_location_transfer');
        return $dataTable
        ->rawColumns(['sr_no','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

}