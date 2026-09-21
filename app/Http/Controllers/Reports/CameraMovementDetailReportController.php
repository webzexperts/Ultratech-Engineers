<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use App\Models\RTCamera;
use App\Models\RTCameraDetails;
use App\Models\Location;
use App\Models\Transaction\InterLocationTransfer;
use App\Models\Transaction\InterLocationTransferDetails;
use App\Models\Transaction\SupplierDC;
use App\Models\Transaction\SupplierDCDetails;

class CameraMovementDetailReportController extends Controller
{
    /**
     * Display the camera movement detail report view.
     */
    public function manage()
    {
        return view('manage.reports.manage-camera_movement_detail_report');
    }

    /**
     * Fetch camera list with decay calculations for Yajra DataTables.
     */
    public function index(Request $request)
    {
        /*
        $cameraQuery = DB::table('rt_camera')
            ->select([
                'rt_camera.rt_aerb_no as aerb_no',
                'rt_camera.name_for_display as camera_sr_no',
                'rt_camera.rt_camera_name as camera_name',
                'rt_camera.rt_isotope as isotope',
                'location.location_name as location',
                'rt_camera.application_no',
                'rt_camera.movement_approval as approval_letter',
                'rt_camera.validity',
                'rt_camera_details.rtcd_initial_activity_ci as initial_curie',
                'rt_camera_details.rtcd_last_of_loading_date as loading_date',
                'rt_camera_details.rtcd_decay_chart as decay_chart'
            ])
            ->leftJoin('rt_camera_details', function ($join) {
                $join->on('rt_camera_details.rtcd_rt_camera_id', '=', 'rt_camera.rt_camera_id')
                     ->whereRaw('rt_camera_details.rtcd_last_of_loading_date = (SELECT MAX(sub_cd.rtcd_last_of_loading_date) FROM rt_camera_details sub_cd WHERE sub_cd.rtcd_rt_camera_id = rt_camera.rt_camera_id)');
            })
            ->leftJoin('location', 'location.location_id', '=', 'rt_camera.own_location_id');

        $iltQuery = DB::table('inter_location_transfer_details')
            ->select([
                'inter_location_transfer_details.aerb_no',
                'rt_camera.name_for_display as camera_sr_no',
                'rt_camera.rt_camera_name as camera_name',
                'rt_camera.rt_isotope as isotope',
                'location.location_name as location',
                'inter_location_transfer_details.application_no',
                'inter_location_transfer_details.movement_approval as approval_letter',
                'inter_location_transfer_details.validity',
                'rt_camera_details.rtcd_initial_activity_ci as initial_curie',
                'rt_camera_details.rtcd_last_of_loading_date as loading_date',
                'rt_camera_details.rtcd_decay_chart as decay_chart'
            ])
            ->join('inter_location_transfer', 'inter_location_transfer.ilt_id', '=', 'inter_location_transfer_details.inter_location_transfer_id')
            ->join('rt_camera', 'rt_camera.rt_camera_id', '=', 'inter_location_transfer_details.sr_table_pk_id')
            ->leftJoin('rt_camera_details', function ($join) {
                $join->on('rt_camera_details.rtcd_rt_camera_id', '=', 'rt_camera.rt_camera_id')
                     ->whereRaw('rt_camera_details.rtcd_last_of_loading_date = (SELECT MAX(sub_cd.rtcd_last_of_loading_date) FROM rt_camera_details sub_cd WHERE sub_cd.rtcd_rt_camera_id = rt_camera.rt_camera_id)');
            })
            ->leftJoin('location', 'location.location_id', '=', 'inter_location_transfer.to_location_id')
            ->where('inter_location_transfer_details.sr_table_unique_id', '=', 'rt_camera');

        $sdcQuery = DB::table('supplier_dc_details')
            ->select([
                'supplier_dc_details.aerb_no',
                'rt_camera.name_for_display as camera_sr_no',
                'rt_camera.rt_camera_name as camera_name',
                'rt_camera.rt_isotope as isotope',
                'location.location_name as location',
                'supplier_dc_details.application_no',
                'supplier_dc_details.movement_approval as approval_letter',
                'supplier_dc_details.validity',
                'rt_camera_details.rtcd_initial_activity_ci as initial_curie',
                'rt_camera_details.rtcd_last_of_loading_date as loading_date',
                'rt_camera_details.rtcd_decay_chart as decay_chart'
            ])
            ->join('supplier_dc', 'supplier_dc.sup_dc_id', '=', 'supplier_dc_details.sup_dcd_dc_id')
            ->join('rt_camera', 'rt_camera.rt_camera_id', '=', 'supplier_dc_details.sr_table_pk_id')
            ->leftJoin('rt_camera_details', function ($join) {
                $join->on('rt_camera_details.rtcd_rt_camera_id', '=', 'rt_camera.rt_camera_id')
                     ->whereRaw('rt_camera_details.rtcd_last_of_loading_date = (SELECT MAX(sub_cd.rtcd_last_of_loading_date) FROM rt_camera_details sub_cd WHERE sub_cd.rtcd_rt_camera_id = rt_camera.rt_camera_id)');
            })
            ->leftJoin('location', 'location.location_id', '=', 'supplier_dc.current_location_id')
            ->where('supplier_dc_details.sr_table_unique_id', '=', 'rt_camera');

        $cameras = $cameraQuery->unionAll($iltQuery)->unionAll($sdcQuery);
        */

        $cameraQuery = DB::table('rt_camera')
            ->select([
                'rt_camera.rt_camera_id as rt_camera_id',
                'rt_camera.created_on as movement_date',
                'rt_camera.rt_aerb_no as aerb_no',
                'rt_camera.name_for_display as camera_sr_no',
                'rt_camera.rt_camera_name as camera_name',
                'rt_camera.rt_isotope as isotope',
                'location.location_name as location',
                'rt_camera.application_no',
                'rt_camera.movement_approval as approval_letter',
                'rt_camera.validity',
                'rt_camera_details.rtcd_initial_activity_ci as initial_curie',
                'rt_camera_details.rtcd_last_of_loading_date as loading_date',
                'rt_camera_details.rtcd_decay_chart as decay_chart'
            ])
            ->leftJoin('rt_camera_details', function ($join) {
                $join->on('rt_camera_details.rtcd_rt_camera_id', '=', 'rt_camera.rt_camera_id')
                     ->whereRaw('rt_camera_details.rtcd_last_of_loading_date = (SELECT MAX(sub_cd.rtcd_last_of_loading_date) FROM rt_camera_details sub_cd WHERE sub_cd.rtcd_rt_camera_id = rt_camera.rt_camera_id)');
            })
            ->leftJoin('location', 'location.location_id', '=', 'rt_camera.current_location_id');

        // $iltQuery = DB::table('inter_location_transfer_details')
        //     ->select([
        //         'rt_camera.rt_camera_id as rt_camera_id',
        //         'inter_location_transfer.created_on as movement_date',
        //         'inter_location_transfer_details.aerb_no',
        //         'rt_camera.name_for_display as camera_sr_no',
        //         'rt_camera.rt_camera_name as camera_name',
        //         'rt_camera.rt_isotope as isotope',
        //         'location.location_name as location',
        //         'inter_location_transfer_details.application_no',
        //         'inter_location_transfer_details.movement_approval as approval_letter',
        //         'inter_location_transfer_details.validity',
        //         'rt_camera_details.rtcd_initial_activity_ci as initial_curie',
        //         'rt_camera_details.rtcd_last_of_loading_date as loading_date',
        //         'rt_camera_details.rtcd_decay_chart as decay_chart'
        //     ])
        //     ->join('inter_location_transfer', 'inter_location_transfer.ilt_id', '=', 'inter_location_transfer_details.inter_location_transfer_id')
        //     ->join('rt_camera', 'rt_camera.rt_camera_id', '=', 'inter_location_transfer_details.sr_table_pk_id')
        //     ->leftJoin('rt_camera_details', function ($join) {
        //         $join->on('rt_camera_details.rtcd_rt_camera_id', '=', 'rt_camera.rt_camera_id')
        //              ->whereRaw('rt_camera_details.rtcd_last_of_loading_date = (SELECT MAX(sub_cd.rtcd_last_of_loading_date) FROM rt_camera_details sub_cd WHERE sub_cd.rtcd_rt_camera_id = rt_camera.rt_camera_id)');
        //     })
        //     ->leftJoin('location', 'location.location_id', '=', 'inter_location_transfer.to_location_id')
        //     ->where('inter_location_transfer_details.sr_table_unique_id', '=', 'rt_camera');

        // $sdcQuery = DB::table('supplier_dc_details')
        //     ->select([
        //         'rt_camera.rt_camera_id as rt_camera_id',
        //         'supplier_dc.created_on as movement_date',
        //         'supplier_dc_details.aerb_no',
        //         'rt_camera.name_for_display as camera_sr_no',
        //         'rt_camera.rt_camera_name as camera_name',
        //         'rt_camera.rt_isotope as isotope',
        //         'location.location_name as location',
        //         'supplier_dc_details.application_no',
        //         'supplier_dc_details.movement_approval as approval_letter',
        //         'supplier_dc_details.validity',
        //         'rt_camera_details.rtcd_initial_activity_ci as initial_curie',
        //         'rt_camera_details.rtcd_last_of_loading_date as loading_date',
        //         'rt_camera_details.rtcd_decay_chart as decay_chart'
        //     ])
        //     ->join('supplier_dc', 'supplier_dc.sup_dc_id', '=', 'supplier_dc_details.sup_dcd_dc_id')
        //     ->join('rt_camera', 'rt_camera.rt_camera_id', '=', 'supplier_dc_details.sr_table_pk_id')
        //     ->leftJoin('rt_camera_details', function ($join) {
        //         $join->on('rt_camera_details.rtcd_rt_camera_id', '=', 'rt_camera.rt_camera_id')
        //              ->whereRaw('rt_camera_details.rtcd_last_of_loading_date = (SELECT MAX(sub_cd.rtcd_last_of_loading_date) FROM rt_camera_details sub_cd WHERE sub_cd.rtcd_rt_camera_id = rt_camera.rt_camera_id)');
        //     })
        //     ->leftJoin('location', 'location.location_id', '=', 'supplier_dc.current_location_id')
        //     ->where('supplier_dc_details.sr_table_unique_id', '=', 'rt_camera');

        // $unionQuery = $cameraQuery->unionAll($iltQuery)->unionAll($sdcQuery);

        // $cameras = DB::table(DB::raw("({$unionQuery->toSql()}) as sub"))
        //     ->mergeBindings($unionQuery)
        //     ->select('sub.*')
        //     ->join(DB::raw("(SELECT rt_camera_id, MAX(movement_date) as max_date FROM ({$unionQuery->toSql()}) as sub_max GROUP BY rt_camera_id) as max_tbl"), function($join) {
        //         $join->on('max_tbl.rt_camera_id', '=', 'sub.rt_camera_id')
        //              ->on('max_tbl.max_date', '=', 'sub.movement_date');
        //     })
        //     ->mergeBindings($unionQuery);

        return DataTables::of($cameraQuery)
            ->editColumn('validity', function ($row) {
                if (!empty($row->validity)) {
                    return Carbon::parse($row->validity)->format('d/m/Y');
                }
                return '';
            })
            ->addColumn('approval_letter_name', function ($row) {
                if (!empty($row->approval_letter)) {
                    return basename($row->approval_letter);
                }
                return '';
            })
            ->addColumn('present_ci', function ($row) {
                $isotope = trim($row->isotope);
                if (empty($isotope) || strcasecmp($isotope, 'X-Rays') == 0) {
                    return '';
                }

                if (empty($row->initial_curie) || empty($row->loading_date)) {
                    return '0.00 Ci';
                }

                try {
                    $loading = Carbon::parse($row->loading_date);
                    $today = Carbon::today();

                    if ($loading->greaterThan($today)) {
                        return '0.00 Ci';
                    }

                    $dayDifference = $loading->diffInDays($today);
                    if ($dayDifference == 0) {
                        $dayDifference = 1;
                    }

                    $initial = (float)$row->initial_curie;

                    $half_life = 0;
                    if (strcasecmp($isotope, 'Ir-192') == 0) {
                        $half_life = 74.5;
                    } elseif (strcasecmp($isotope, 'Co-60') == 0) {
                        $half_life = 1925;
                    } elseif (strcasecmp($isotope, 'Se-75') == 0) {
                        $half_life = 120;
                    }

                    if ($half_life > 0) {
                        $decay = pow(2.718, ($dayDifference * 0.693 / $half_life));
                        $present = $initial / $decay;
                        return number_format($present, 2, '.', '') . ' Ci';
                    }

                    return number_format($initial, 2, '.', '') . ' Ci';
                } catch (\Exception $e) {
                    return '0.00 Ci';
                }
            })
            ->editColumn('decay_chart', function ($row) {
                if (!empty($row->decay_chart)) {
                    $url = asset('storage/' . $row->decay_chart);
                    return '<a href="' . $url . '" target="_blank">
                        <i class="ri-eye-fill action-icon remove_filters_short_qty" style="font-size: 16px;"></i>
                    </a>';
                }
                return '<span class="remove_filters_short_qty d-none"></span>';
            })
            ->editColumn('approval_letter', function ($row) {
                if (!empty($row->approval_letter)) {
                    $url = asset('storage/' . $row->approval_letter);
                    return '<a href="' . $url . '" target="_blank">
                        <i class="ri-eye-fill action-icon remove_filters_short_qty" style="font-size: 16px;"></i>
                    </a>';
                }
                return '<span class="remove_filters_short_qty d-none"></span>';
            })
            ->rawColumns(['decay_chart', 'approval_letter'])
            ->make(true);
    }
}
