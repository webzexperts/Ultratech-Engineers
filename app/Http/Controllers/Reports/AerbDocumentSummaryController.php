<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\AerbDocument;

class AerbDocumentSummaryController extends Controller
{
    public function manage()
    {
        return view('manage.reports.manage-aerb_documents_summary');
    }

    public function index(Request $request, DataTables $datatables)
    {
        $aerb_data = AerbDocument::select([
            'aerb_documents.aerb_documents_id as id',
            'aerb_documents.aerb_documents_name',
            'aerb_documents.aerb_documents_upload',
            'aerb_documents.remark',
        ]);

        return DataTables::of($aerb_data)
        ->editColumn('aerb_documents_upload', function($row){
            if(!empty($row->aerb_documents_upload))
            {
                $documentUrl = asset('storage/' . $row->aerb_documents_upload);
                return '<a href="' . $documentUrl . '" target="_blank">
                <i class="ri-eye-fill action-icon remove_filters_short_qty" style="font-size: 16px;"></i>
                </a>';
            }
            return '';
        })
        ->rawColumns(['aerb_documents_upload'])
        ->make(true);
    }
}
