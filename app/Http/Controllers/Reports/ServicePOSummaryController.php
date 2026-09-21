<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\SupplierDetails;
use App\Models\Transaction\ServicePO;
use App\Models\Transaction\ServicePODetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Calculation\Web\Service;

class ServicePOSummaryController extends Controller
{
    public function manage()
    {
        return view('manage.reports.manage-service_po_summary');
    }

    public function index(ServicePO $ser_po_data, Request $request, DataTables $datatables)
    {
        // $current_location_id = getCurrentLocation()->location_id;
        $current_location = getCurrentLocation();
        $year_data = getCurrentYearData();
        $ser_po_data = ServicePO::select([

            'service_po.ser_po_sequence',
            'service_po.ser_po_id',
            'service_po.ser_po_number',
            'service_po.ser_po_date',
            'suppliers.supplier_name',
            'suppliers.email_id as supplier_email',
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
            'unit.unit',
            'service_po_details.rate_unit',
            'service_po_details.amount',
            'service_po_details.del_date',
            'service_po_details.remark',
            'prepared_by.person_name as prepared_by',
            'service_po.created_on',
            'service_po.created_by',
            'service_po.last_by',
            'service_po.last_on',
        ])

        ->leftJoin('service_po_details','service_po_details.ser_pod_po_id','=','service_po.ser_po_id')
        ->leftJoin('suppliers','suppliers.id','=','service_po.supplier_id')
        ->leftJoin('location as bill_to','bill_to.location_id','=','service_po.bill_to_id')
        ->leftJoin('location as for_location','for_location.location_id','=','service_po.for_location_id')
        ->leftJoin('admin as prepared_by','prepared_by.id','=','service_po.prepared_by_user_id')
        ->leftJoin('item','item.id','=','service_po_details.item_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'service_po_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'service_po_details.sr_table_unique_id');
        })
        ->where('service_po.year_id', $year_data->id)
        // ->where("service_po.current_location_id", $current_location_id);
        ->when($current_location->location_type != 'HO', function ($q) use ($current_location) {
                $q->where(function ($sub) use ($current_location) {
                    $sub->where('service_po.current_location_id', $current_location->location_id)
                        ->orWhere('service_po.for_location_id', $current_location->location_id);
                });
            });    

         if($request->from_date != "" && $request->to_date != "") {
            $from = Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $to = Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $ser_po_data->whereDate('service_po.ser_po_date','>=',$from);
            $ser_po_data->whereDate('service_po.ser_po_date','<=',$to);
        } else if($request->from_date != "") {
            $from = Date::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
            $ser_po_data->where('service_po.ser_po_date','>=',$from);
        } else if($request->to_date != "") {
            $to = Date::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');
            $ser_po_data->where('service_po.ser_po_date','<=',$to);
        }


        $dataTable = DataTables::of($ser_po_data)
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
        ->editColumn('del_date', function($po_data){
            if ($po_data->del_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $po_data->del_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
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

        

        ->filterColumn('service_po_details.del_date', function ($q, $k) {
            applyDate($q, $k, 'service_po_details.del_date');
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
        ->editColumn('rate_unit', function($po_data) {
            return $po_data->rate_unit > 0 ? number_format((float)$po_data->rate_unit, 2, '.','') : number_format((float) 0, 2, '.','');
        })
        ->filterColumn('service_po_details.rate_unit', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,2})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('rate_unit')
                    ->orWhere('rate_unit', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(rate_unit AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('rate_unit', 'like', "%{$search}%");
            }
        })
        
        ->editColumn('amount', function($po_data) {
            return $po_data->amount > 0 ? number_format((float)$po_data->amount, 2, '.','') : number_format((float) 0, 2, '.','');
        })
        ->filterColumn('service_po_details.amount', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,2})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('amount')
                    ->orWhere('amount', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(amount AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('amount', 'like', "%{$search}%");
            }
        })
        ->filterColumn('service_po.ser_po_number', function($query, $keyword) {
            applySequenceSearch($query, $keyword, 'service_po.ser_po_number');
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'service_po');
        return $dataTable
        // ->rawColumns(['options','last_by','last_on','created_by','created_on','options'])
        ->make(true);
    }
}