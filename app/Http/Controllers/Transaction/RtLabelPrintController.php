<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class RtLabelPrintController extends Controller
{
    /**
     * Display the RT Label Print form.
     */
    public function manage()
    {
        return view('manage.transaction.manage-rt_label_print');
    }

    /**
     * Retrieve the list of RT reports filtered location-wise.
     */
    public function RtReportsList(Request $request)
    {
        $year = getCurrentYearData()->id;
        $location = getCurrentLocation()->location_id;

        $reports = DB::table('test_report_rt')
            ->select([
                'test_report_rt.test_report_rt_id AS id',
                'test_report_rt.test_report_no',
                'test_report_rt.revision_number',
                'test_report_rt.test_report_date',
                'customers.customer',
                'test_report_rt.rt_no',
                'test_report_rt.heat_no',
                'materials.material',
                'job_descriptions.job_description'
            ])
            ->leftJoin('customers', 'customers.id', '=', 'test_report_rt.customer_id')
            ->leftJoin('materials', 'materials.id', '=', 'test_report_rt.material_id')
            ->leftJoin('job_descriptions', 'job_descriptions.id', '=', 'test_report_rt.job_desc_id')
            ->where('test_report_rt.current_location_id', $location)
            ->where('test_report_rt.year_id', $year)
            ->orderBy('test_report_rt.test_report_rt_id', 'desc')
            ->get();

        foreach ($reports as $r) {
            $r->test_report_date = ($r->test_report_date != "" && $r->test_report_date != "0000-00-00")
                ? Carbon::parse($r->test_report_date)->format('d/m/Y')
                : "";
        }

        return response()->json([
            'response_code' => 1,
            'reports' => $reports
        ]);
    }

    /**
     * Get detail lines for a selected RT Report to load in the grid.
     */
    public function getRtReportDetailsForPrint(Request $request)
    {
        $id = $request->id;

        $master = DB::table('test_report_rt')
            ->select([
                'test_report_rt.test_report_rt_id AS id',
                'test_report_rt.test_report_no',
                'test_report_rt.revision_number',
                'test_report_rt.test_report_date',
                'customers.customer',
                'test_report_rt.customer_client AS party'
            ])
            ->leftJoin('customers', 'customers.id', '=', 'test_report_rt.customer_id')
            ->where('test_report_rt.test_report_rt_id', $id)
            ->first();

        if (!$master) {
            return response()->json([
                'response_code' => 0,
                'response_message' => 'Report not found'
            ]);
        }

        if ($master->test_report_date != "" && $master->test_report_date != "0000-00-00") {
            $master->test_report_date = Carbon::parse($master->test_report_date)->format('d/m/Y');
        }

        $details = DB::table('test_report_rt_details')
            ->select([
                'test_report_rt_details.test_report_rt_details_id AS id',
                'test_report_rt_details.sr_no',
                'test_report_rt_details.identification',
                'test_report_rt_details.location',
                'film_type.film_type',
                'film.film_size_inch AS film_size',
                'test_report_rt_details.film_qty',
                'test_report_rt_details.no_of_film_fix',
                'test_report_rt_details.thickness',
                'test_report_rt_details.sfd',
                'test_report_rt_details.optical_density AS density',
                DB::raw("IFNULL(NULLIF(test_report_rt_details.iqi_designation, ''), iqi_designation.iqi_designation) AS iqi"),
                DB::raw("IFNULL(NULLIF(test_report_rt_details.iqi_sensitivity, ''), iqi_sensitivity.iqi_sensitivity) AS sensitivity"),
                'film_result.film_result_name AS result'
            ])
            ->leftJoin('film_type', 'film_type.film_type_id', '=', 'test_report_rt_details.film_type_id')
            ->leftJoin('film', 'film.film_id', '=', 'test_report_rt_details.film_id')
            ->leftJoin('iqi_designation', 'iqi_designation.iqi_designation_id', '=', 'test_report_rt_details.iqi_designation_id')
            ->leftJoin('iqi_sensitivity', 'iqi_sensitivity.iqi_sensitivity_id', '=', 'test_report_rt_details.iqi_sensitivity_id')
            ->leftJoin('film_result', 'film_result.film_result_id', '=', 'test_report_rt_details.result_id')
            ->where('test_report_rt_details.test_report_rt_id', $id)
            // ->when($master->revision_number !== null && $master->revision_number !== '' && $master->revision_number !== 0 , function ($query) {
            //     $query->where('test_report_rt_details.record_type_id', 2);
            // })
            ->orderBy('test_report_rt_details.sr_no', 'asc')
            ->get();

        return response()->json([
            'response_code' => 1,
            'report_data' => $master,
            'report_details_data' => $details
        ]);
    }

    /**
     * Generate and return PDF print URL for selected RT Label details.
     */
    public function printRtLabel(Request $request)
    {
        $location = getCurrentLocation()->location_name;
        $pdf_name = 'RT_Label_Print_'.str_replace(' ','_',$location);
        $detailsIds = $request->details_ids;

        // Unlink old file if exists so fresh PDF is generated every time
        $localFilePath = storage_path('app/public/reports/rt_label_print_reports_file/'.$pdf_name.'.pdf');
        if (file_exists($localFilePath)) {
            @unlink($localFilePath);
        }

        GeneratePdf($detailsIds,$pdf_name, 'rt_label_print', 'add');

        $outputPath = asset('storage/reports/rt_label_print_reports_file/'.$pdf_name.'.pdf') . '?v=' . time();

        if (!file_exists($localFilePath)) {
            return response()->json([
                'response_code' => 0,
                'response_message' => 'Failed to generate PDF file.'
            ]);
        }

        return response()->json([
            'response_code' => 1,
            'url' => $outputPath
        ]);
    }
}
