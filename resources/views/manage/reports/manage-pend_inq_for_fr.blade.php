@extends('layouts.master')
@section('title') Pending Inquiry For Feasibility Review @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Reports @endslot
@slot('title')Pending Inquiry For Feasibility Review @endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Pending Inquiry For Feasibility Review</h5>
                <div>
                    @if(hasAccess("pend_inq_for_fr","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Inq. No.</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Ref. No. & Date</th>
                            <th>Type of Test</th>
                            <th>Type of Job</th>
                            <th>Job Desc.</th>
                            <th>Part No.</th>
                            <th>Process At</th>
                            <th>Qty.</th>
                            <th>Unit</th>
                            <th>Remark</th>
                            <th style="display:none;"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script-manage')
    <script>
        var headerOpt = {'Authorization':'Bearer {{ Auth::user()->auth_token }}','X-CSRF-TOKEN':'{{ csrf_token() }}'};
        jQuery('#export-excel').on('click',function(){
            jQuery('.export_po').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'asc'],[12, 'asc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Pending Inquiry For Feasibility Review List',
                title:"",
                className: 'export_po d-none',
                exportOptions: {
                        columns: ':not(:eq(11))',
                        modifier: { page: 'all' }
                    },
                action: newexportaction
            }],
            ajax: {
                url: "listing-pend_inq_for_fr",
                type: "POST",
                headers: headerOpt,
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#dyntable_processing').hide();
                    if (jqXHR.status == 401) {
                        console.log(jqXHR.statusText);
                    } else {
                        console.log('Somthing went wrong!');
                    }
                    console.log(JSON.parse(jqXHR.responseText));
                }
            },
            columns: [
                
                { data: 'inq_number', name: 'inquiry.inq_number', },
                { data: 'inq_date', name: 'inquiry.inq_date', },
                { data: 'customer', name: 'customers.customer', },
                { data: 'inq_ref_no_date', name: 'inquiry.inq_ref_no_date', },
                { data: 'inqd_test_method', name: 'inquiry_details.inqd_test_method', },
                { data: 'type_of_job', name: 'type_of_job.type_of_job', },
                { data: 'job_description', name: 'job_descriptions.job_description', },
                //{ data: 'part', name: 'part.part', },
                { data: 'inqd_part_no', name: 'inquiry_details.inqd_part_no', },
                { data: 'inqd_process_at', name: 'inquiry_details.inqd_process_at', },
                { data: 'inqd_quantity', name: 'inquiry_details.inqd_quantity', },
                { data: 'unit', name: 'unit.unit', },
                { data: 'inqd_remark', name: 'inquiry_details.inqd_remark', },
                { data: 'inq_sequence', name: 'inquiry.inq_sequence', visible: false, searchable: false },
            ],
        });

    </script>
@endsection