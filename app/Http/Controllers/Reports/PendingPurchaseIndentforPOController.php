<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\PurchaseIndent;
use App\Models\PurchaseIndentDetails;


class PendingPurchaseIndentforPOController extends Controller
{
    public function manage()
    {
        return view('manage.reports.manage-pend_purchase_indent_for_po');
    }

    public function index(PurchaseIndentDetails $pi_data, Request $request, DataTables $datatables)
    {
        $yearIds = getCompanyYearIdsToTill();
        $current_location = getCurrentLocation();
        $current_location_id = $current_location->location_id;
        $location_type = $current_location->location_type;
        $yearIds  = getCompanyYearIdsToTill();
        $data = DB::table('pending_purchase_indent_qty as pend')->select([
                'purchase_indent_details.pid_id','pi.pi_id','pi.pi_no','pi.pi_date','pi.to_location_id','item.item_name','item_group.item_group','item.item_type as main_group','purchase_indent_details.indent_qty','unit.unit','pend.pending_qty',
                'purchase_indent_details.remark','admin.person_name as indent_by','location.location_name as to_location', 'cur_loc.location_name as current_location','pi.pi_sequence'
            ])
            ->leftJoin('purchase_indent_details', 'purchase_indent_details.pid_id', '=', 'pend.pid_id')
            ->leftJoin('purchase_indent as pi', 'pi.pi_id', '=', 'purchase_indent_details.pid_pi_id')
            ->leftJoin('item', 'item.id', '=', 'purchase_indent_details.item_id')
            ->leftJoin('unit', 'unit.id', '=', 'item.unit_id')
            ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
            ->leftJoin('admin', 'admin.id', '=', 'pi.indent_by_user_id')
            ->leftJoin('location', 'location.location_id', '=', 'pi.to_location_id')
            ->leftJoin('location as cur_loc', 'cur_loc.location_id', '=', 'pi.current_location_id')
            ->whereIn('pi.year_id', $yearIds)
            ->where('pending_qty', '>', 0)
            ->when($location_type != 'HO', function ($q) use ($current_location_id) {
                $q->where(function ($sub) use ($current_location_id) {
                    $sub->where('pi.current_location_id', $current_location_id)
                        ->orWhere('pi.to_location_id', $current_location_id);
                });
            });

        $dataTable = DataTables::of($data)
        ->filterColumn('pi.pi_no', function($query, $keyword) {
            applySequenceSearch($query, $keyword, 'pi.pi_no');
        })
        ->editColumn('pi_date', function($data){
            if ($data->pi_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $data->pi_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('pi.pi_date', function ($q, $k) {
            applyDate($q, $k, 'pi.pi_date');
        })
        ->editColumn('indent_qty', function($data) {
            return $data->indent_qty > 0 ? number_format((float)$data->indent_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->editColumn('main_group', function($data) {
            return config('app.item_type')[$data->main_group] ?? $data->main_group;
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
        ->filterColumn('purchase_indent_details.indent_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('indent_qty')
                    ->orWhere('indent_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(indent_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('indent_qty', 'like', "%{$search}%");
            }
        })

        ->editColumn('indent_qty', function($data) {
            return $data->indent_qty > 0 ? number_format((float)$data->indent_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
      
        ->filterColumn('pi.pi_no', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'pi.pi_no');
        });
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on','options'])
        ->make(true);
    }
}
