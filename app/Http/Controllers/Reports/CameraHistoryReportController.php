<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Yajra\DataTables\DataTables;
use App\Models\RTCamera;

class CameraHistoryReportController extends Controller
{
    public function manage()
    {
        $LocationData = getCurrentLocation();

        $cameras_query = RTCamera::select('rt_camera_id', 'name_for_display')
            ->where('rt_status', '!=', 'D');

        if ($LocationData->location_type != 'HO') {
            $cameras_query->where(function($query) use ($LocationData) {
                $query->where('rt_camera.current_location_id', $LocationData->location_id)
                      ->orWhere('rt_camera.own_location_id', $LocationData->location_id);
            });
        }

        $cameras = $cameras_query->orderBy('name_for_display', 'ASC')->get();

        return view('manage.reports.manage-camera_history_report', compact('cameras'));
    }

    public function index(Request $request)
    {
        $cameraId = $request->camera_id;
        
        if (empty($cameraId)) {
            $data = [];
        } else {
            $data = DB::select('CALL rt_camera_history(?)', [$cameraId]);
        }

        return DataTables::of($data)
            ->rawColumns([])
            ->make(true);
    }
}
