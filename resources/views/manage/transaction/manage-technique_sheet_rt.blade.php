@extends('layouts.master')
@section('title')Radiographic Shooting Sketch @endsection
@section('css')
@endsection
@section('content')
@component('components.breadcrumb')
    @slot('li_1') Transaction @endslot
    @slot('title') Radiographic Shooting Sketch @endslot
@endcomponent

@include('modals.modals-transaction.technique_sheet_rt_modal')
@include('modals.modals-pending.pending_test_report_rt_for_tc')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Radiographic Shooting Sketch</h5>
                <div>
                    @if(hasAccess("technique_sheet_rt", "export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("technique_sheet_rt", "add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#TechniqueSheetRtModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Sr. No.</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Type of Job</th>
                            <th>Job Desc.</th>
                            <th>Part No.</th>
                            <th>Drg. No.</th>
                            <th>Area of Coverage</th>
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
            jQuery('.export_technique_sheet_rt').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'desc'], [13, 'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                filename: 'Radiographic Shooting Sketch List',
                title: "",
                className: 'export_technique_sheet_rt d-none',
                exportOptions: {
                    columns: function(idx, data, node) {
                        return idx !== 0 && table.column(idx).visible();
                    },
                    modifier: { page: 'all' }
                },
                action: newexportaction
            }],
            ajax: {
                url: "listing-technique_sheet_rt",
                type: "POST",
                headers: headerOpt,
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
                { data: 'technique_sheet_rt_no', name: 'technique_sheet_rt.technique_sheet_rt_no' },
                { data: 'technique_sheet_rt_date', name: 'technique_sheet_rt.technique_sheet_rt_date' },
                { data: 'customer', name: 'customers.customer' },
                { data: 'type_of_job', name: 'type_of_job.type_of_job' },
                { data: 'job_description', name: 'job_descriptions.job_description' },
                { data: 'part_no', name: 'technique_sheet_rt.part_no' },
                { data: 'drg_no', name: 'technique_sheet_rt.drg_no' },
                { data: 'area_of_coverage', name: 'area_of_coverage.area_of_coverage' },
                { data: 'last_by', name: 'last_by' },
                { data: 'last_on', name: 'technique_sheet_rt.last_on' },
                { data: 'created_by', name: 'created_by' },
                { data: 'created_on', name: 'technique_sheet_rt.created_on' },
                { data: 'technique_sheet_rt_sequence', name: 'technique_sheet_rt.technique_sheet_rt_sequence', visible: false, searchable: false },
            ]
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-technique_sheet_rt",
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
                            table.ajax.reload(null, false);
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
