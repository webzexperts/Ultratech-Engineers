<?php

namespace App\Http\Controllers\PDF;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function checkReportExists(Request $request){
        $id = $request->id;
        // $name = $request->name;
        $name = sanitize_pdf_name($request->name);
        $type = $request->type;       
        
        if (!hasAccess($type, 'print')) {
            abort(401);
        }
        
        $filePath = storage_path('app/public/reports/'.$type.'_reports_file/' . $name . '.pdf'); 
        $isFileExists = file_exists($filePath);
        
        if($isFileExists){  

            $urldd = asset('storage/reports/'.$type.'_reports_file/'. $name.'.pdf') . '?v=' . time();

            return redirect($urldd);
        }else{
            if ($type === 'observation_sheet') {
                $realId = base64_decode($id);
                $testTypes = \DB::table('observation_sheet_details')
                    ->where('observation_sheet_id', $realId)
                    ->whereNotNull('type_of_testing_id_fix')
                    ->pluck('type_of_testing_id_fix')
                    ->unique()
                    ->toArray();

                if (empty($testTypes)) {
                    $testTypes = ['RT'];
                }

                $localDirectory = storage_path('app/public/reports/observation_sheet_reports_file/');
                if (!is_dir($localDirectory)) {
                    mkdir($localDirectory, 0777, true);
                }

                $individualFiles = [];
                foreach ($testTypes as $testType) {
                    $testTypeLower = strtolower($testType);
                    $individualName = $name . '_' . $testTypeLower;
                    $individualPath = $localDirectory . $individualName . '.pdf';

                    if (!file_exists($individualPath)) {
                        GeneratePdf($realId, $individualName, 'observation_sheet', 'listing', $request->job_type_fix, $request->nabl_type_fix, $testTypeLower);
                        sleep(2);
                    }
                    if (file_exists($individualPath)) {
                        $individualFiles[] = $individualPath;
                    }
                }

                if (!empty($individualFiles)) {
                    $pdf = new \setasign\Fpdi\Fpdi();
                    $hasPages = false;
                    foreach ($individualFiles as $file) {
                        if (file_exists($file)) {
                            try {
                                $pageCount = $pdf->setSourceFile($file);
                                for ($i = 1; $i <= $pageCount; $i++) {
                                    $templateId = $pdf->importPage($i);
                                    $size = $pdf->getTemplateSize($templateId);
                                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                                    $pdf->useTemplate($templateId);
                                    $hasPages = true;
                                }
                            } catch (\Exception $e) {
                                \Log::error("Failed to merge PDF page from {$file}: " . $e->getMessage());
                            }
                        }
                    }
                    if ($hasPages) {
                        $pdf->Output('F', $filePath);
                    } else {
                        copy($individualFiles[0], $filePath);
                    }
                }

                $urldd = asset('storage/reports/observation_sheet_reports_file/'. $name.'.pdf') . '?v=' . time();
                return redirect($urldd);
            } else {
                // GeneratePdf(base64_decode($id),$name,$type);
                GeneratePdf(base64_decode($id), $name, $type, 'listing', $request->job_type_fix, $request->nabl_type_fix, $request->repair);
                sleep(2);
                $urldd = asset('storage/reports/'.$type.'_reports_file/'. $name.'.pdf');
                return redirect($urldd);
            }
        }
      
    }

    public function mergeReports(Request $request)
    {

        $pdf = new Fpdi();

        $report_data = json_decode($request['report_data']);
        //    dd($request);

        foreach ($report_data as $data) {

            // $name = str_replace("/","_",$data->name);
            $name = sanitize_pdf_name($data->name);
            $filePath = storage_path('app/public/reports/'.$data->type.'_reports_file/' . $name .'.pdf'); 
        //    $filePath = public_path('reports/'.$data->type.'_reports_file/' . $name .'.pdf'); 
            $type = $data->type;
          
            if (!hasAccess($type, 'print')) {
                abort(401);
            }
          
            // Check if the file exists
            if (file_exists($filePath)) {
                // dd($filePath);
                $pageCount = $pdf->setSourceFile($filePath); // Load the PDF file
                // dd("yes",$pageCount);
             
                // Add all pages of the current PDF
                for ($i = 1; $i <= $pageCount; $i++) {
                    $templateId = $pdf->importPage($i); // Import page
                    $size = $pdf->getTemplateSize($templateId); // Get page size
                    // Add a new page and use the imported template
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);
                }
            } else {

                if($type == "invoice"){
                    GeneratePdf(base64_decode($data->id),$name,$data->type,'','add','ORIGINAL',strtolower($data->invoice_job_type));

                     if (file_exists($filePath)) {
                        $pageCount = $pdf->setSourceFile($filePath);

                        for ($i = 1; $i <= $pageCount; $i++) {
                            $templateId = $pdf->importPage($i);
                            $size = $pdf->getTemplateSize($templateId);
                            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                            $pdf->useTemplate($templateId);
                        }
                    }
                }else{

                // Handle the missing file (e.g., log, skip, or throw an exception)
                GeneratePdf(base64_decode($data->id),$name,$data->type,'yes','listing');
                if (file_exists($filePath)) {
                    $pageCount = $pdf->setSourceFile($filePath); // Load the newly created PDF
        
                    for ($i = 1; $i <= $pageCount; $i++) {
                        $templateId = $pdf->importPage($i); // Import page
                        $size = $pdf->getTemplateSize($templateId); // Get page size
                        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]); // Add a new page
                        $pdf->useTemplate($templateId); // Use the imported template
                    }
                } 
             }

            }
        }

        $filename = $type.'_report_' . uniqid() . '.pdf';  
        $outputPath = storage_path('app/public/reports/merged_pdf_file/'.$filename);
        //$outputPath = asset('storage/reports/merged_pdf_file/'.$filename);
        // dd($outputPath);
        // $outputPath = public_path('reports/merged_pdf_file/'.$filename);
        $pdf->Output('F', $outputPath);
       // $fileUrl = storage_path('app/public/reports/merged_pdf_file/'.$filename);
        $fileUrl = asset('storage/reports/merged_pdf_file/'.$filename);
        // $fileUrl = asset('reports/merged_pdf_file/'.$filename);
        return response()->json(['url' => $fileUrl]);

    }

    public function downloadSingleMptReports(Request $request)
    {
        $report_data = json_decode($request['report_data']);
        $downloadLinks = [];

        foreach ($report_data as $data) {
            // $name = str_replace("/", "_", $data->name);
            $name = sanitize_pdf_name($data->name);
            $filePath = storage_path('app/public/reports/' . $data->type . '_reports_file/' . $name . '.pdf');
           // $filePath = public_path('reports/' . $data->type . '_reports_file/' . $name . '.pdf');
 
            if (!hasAccess($data->type, 'print')) {
                continue;
            }

            // Check if file exists, otherwise generate it
            if (!file_exists($filePath)) {
                GeneratePdf(base64_decode($data->id), $name, $data->type, 'no','listing');
                
            }

            if (file_exists($filePath)) {
                $downloadLinks[] = [
                    'name' => $name . '.pdf',
                    'url'  => asset('storage/reports/' . $data->type . '_reports_file/' . $name . '.pdf')
                    //'url'  => asset('reports/' . $data->type . '_reports_file/' . $name . '.pdf')
                ];
            }
        }

        return response()->json(['files' => $downloadLinks]);
    }

    public function downloadPdf(Request $request)
    {

        $id = base64_decode($request->id);
        // $pdf_name = basename($request->name); 
        $pdf_name = sanitize_pdf_name(basename($request->name)); 
        $type = $request->type;
 
        if (!hasAccess($type, 'print')) {
            abort(401);
        }
 
        $filePath = storage_path('app/public/reports/' . $type . '_reports_file/' . $pdf_name . '.pdf');

        if (!file_exists($filePath)) {
            GeneratePdf($id, $pdf_name,$type,'add');
        }

        return response()->file($filePath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$pdf_name.'.pdf"',
        ]);
        
       
    }

}
