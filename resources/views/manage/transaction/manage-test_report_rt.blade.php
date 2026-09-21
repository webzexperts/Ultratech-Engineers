@extends('layouts.master')
@section('title')Test Report (RT) @endsection
@section('css')
@endsection
@section('content')
@component('components.breadcrumb')
    @slot('li_1') Transaction @endslot
    @slot('title') Test Report (RT) @endslot
@endcomponent

@include('modals.modals-transaction.test_report_rt_modal')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between pb-2">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="card-title mb-0">Test Report (RT)</h5>
                </div>
                <div>
                    @if(hasAccess("test_report_rt", "export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("test_report_rt", "add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#TestReportRtModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="row mt-2 ml-2">
                <div class="col-md-3">
                    <select class="zindexnotapply js-example-basic-single" name="filter_customer_id" id="filter_customer_id">
                        <option value="">All Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->customer }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Date</th>
                            <th>Report No.</th>
                            <th>Rev. No.</th>
                            <th>Customer</th>
                            <th>RT No.</th>
                            <th>Product Code</th>
                            <th>DC No.</th>
                            <th>DC Date</th>
                            <th>NABL</th>
                            <th>ULR No.</th>
                            <th>Test At</th>
                            <th>Type</th>
                            <th>PO No.</th>
                            <th>PO Date</th>
                            <th>Type of Job</th>
                            <th>Job Desc.</th>
                            <th>Part No.</th>
                            <th>Drg. No.</th>
                            <th>Material</th>
                            <th>Heat No.</th>
                            <th>Qty.</th>
                            <th>Modified By</th>
                            <th>Modified On</th>
                            <th>Created By</th>
                            <th>Created On</th>
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
        jQuery('#export-excel').on('click', function() {
            jQuery('.export_test_report_rt').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'desc'],[2, 'desc'],[3,'desc'], [26, 'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                filename: 'Test Report (RT) List',
                title: "",
                className: 'export_test_report_rt d-none',
                exportOptions: {
                    columns: function(idx, data, node) {
                        return idx !== 0 && table.column(idx).visible();
                    },
                    modifier: { page: 'all' }
                },
                action: newexportaction
            }],
            ajax: {
                url: "listing-test_report_rt",
                type: "POST",
                headers: headerOpt,
                data: function (d) {
                    d.customer_id = jQuery('#filter_customer_id').val();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#dyntable_processing').hide();
                    if (jqXHR.status == 401) {
                        console.log(jqXHR.statusText);
                    } else {
                        console.log('Something went wrong!');
                    }
                    console.log(JSON.parse(jqXHR.responseText));
                }
            },
            columns: [
                { data: 'options', name: 'options', orderable: false, searchable: false },
                { data: 'test_report_date', name: 'test_report_rt.test_report_date' },
                { data: 'test_report_no', name: 'test_report_rt.test_report_no' },
                { data: 'revision_number', name: 'test_report_rt.revision_number' },
                { data: 'customer', name: 'customers.customer' },
                { data: 'rt_no', name: 'test_report_rt.rt_no' },
                { data: 'product_code', name: 'test_report_rt.product_code' },
                { data: 'dc_no', name: 'material_inward.dc_no' },
                { data: 'dc_date', name: 'material_inward.dc_date' },
                { data: 'nabl_type_fix', name: 'test_report_rt.nabl_type_fix' },
                { data: 'ulr_no', name: 'test_report_rt.ulr_no' },
                { data: 'test_carried_out_at', name: 'test_report_rt.test_carried_out_at' },
                { data: 'job_type_fix', name: 'test_report_rt.job_type_fix' },
                { data: 'po_no', name: 'material_inward.po_no' },
                { data: 'po_date', name: 'material_inward.po_date' },
                { data: 'type_of_job', name: 'type_of_job.type_of_job' },
                { data: 'job_description', name: 'job_descriptions.job_description' },
                { data: 'part_no', name: 'test_report_rt.part_no' },
                { data: 'drg_no', name: 'test_report_rt.drg_no' },
                { data: 'material', name: 'materials.material' },
                { data: 'heat_no', name: 'test_report_rt.heat_no' },
                { data: 'rt_report_qty', name: 'test_report_rt.rt_report_qty' },
                { data: 'last_by', name: 'last_by' },
                { data: 'last_on', name: 'test_report_rt.last_on' },
                { data: 'created_by', name: 'created_by' },
                { data: 'created_on', name: 'test_report_rt.created_on' },
                { data: 'test_report_sequence', name: 'test_report_rt.test_report_sequence', visible: false, searchable: false },
            ]
        });

        jQuery('#filter_customer_id').on('change', function () {
            table.draw();
        });

        jQuery(document).on('click', '#reset-filters-dyntable', function () {
            jQuery('#filter_customer_id').val('').trigger('change.select2');
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-test_report_rt",
                    type: 'GET',
                    data: "id=" + data["id"],
                    headers: headerOpt,
                    dataType: 'json',
                    processData: false,
                    success: function (data) {
                        if (data.response_code == 1) {
                            Swal.fire({
                                text: data.response_message,
                                icon: 'success',
                                customClass: { confirmButton: 'btn btn-primary w-xs mt-2' },
                                buttonsStyling: false
                            });
                            table.row(jQuery(this)).draw(false);
                        } else {
                            toastr.error(data.response_message);
                        }
                    },
                    error: function (jqXHR) {
                        if (jqXHR.status == 401) {
                            console.log(jqXHR.statusText);
                        } else {
                            console.log('Something went wrong!');
                        }
                        console.log(JSON.parse(jqXHR.responseText));
                    }
                });
            });
        });
    </script>
@endsection
