@extends('layouts.master')
@section('title') Observation Sheet @endsection
@section('css')
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Transaction @endslot
@slot('title') Observation Sheet @endslot
@endcomponent

@include('modals.modals-transaction.observation_sheet_modal')
@include('modals.modals-details.observation_sheet_details_modal')
@include('modals.modals-pending.pending_technique_sheet_rt_modal')
@include('modals.modals-pending.pending_old_rt_report_modal')
@include('modals.modals-details.observation_sheet_sub_detail_modal')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Observation Sheet</h5>
                <div>
                    @if(hasAccess("observation_sheet", "export"))
                    <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("observation_sheet", "add"))
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ObservationSheetModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Sheet No.</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <!-- <th>RT No.</th> -->
                            <th>Nature</th>
                            <th>Type of Test</th>
                            <th>In. No.</th>
                            <th>In. Date</th>
                            <th>NABL</th>
                            <th>Test At</th>
                            <th>Type</th>
                            <th>DC No.</th>
                            <th>DC Date</th>
                            <th>PO No.</th>
                            <th>PO Date</th>
                            <th>Type of Job</th>
                            <th>Job Desc.</th>
                            <th>Part No.</th>
                            <th>Drg. No.</th>
                            <th>Material</th>
                            <th>Heat No.</th>
                            <th>Product Code</th>
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
    var checkFileRoute = "{{ route('check-file_exists') }}";
    var loginUserId = {{ auth()->id() }};
    var headerOpt = {
        'Authorization': 'Bearer {{ Auth::user()->auth_token }}', 'X-CSRF-TOKEN': '{{ csrf_token() }}'
    };
    jQuery('#export-excel').on('click', function() {
        jQuery('.export_observation_sheet').click();
    });
    var table = $('#dyntable').DataTable({
        "processing": false,
        "serverSide": true,
        "scrollX": true,
        "order": [[2, 'desc'], [27, 'desc']],
        dom: 'Blfrtip',
        buttons: [{
            extend: 'excel',
            filename: 'Observation Sheet List',
            title: "",
            className: 'export_observation_sheet d-none',
            exportOptions: {
                columns: function(idx, data, node) {
                    return idx !== 0 && table.column(idx).visible();
                },
                modifier: {
                    page: 'all'
                }
            },
            action: newexportaction
        }],
        ajax: {
            url: "listing-observation_sheet",
            type: "POST",
            headers: headerOpt,
            error: function(jqXHR, textStatus, errorThrown) {
                jQuery('#dyntable_processing').hide();
            }
        },
        "columns": [
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
            { data: 'observation_sheet_no', name: 'observation_sheet.observation_sheet_no'}, 
            { data: 'observation_sheet_date', name: 'observation_sheet.observation_sheet_date'}, 
            { data: 'customer_name', name: 'customers.customer'}, 
            // { data: 'rt_no', name: 'tr.test_report_no'},
            { data: 'process_type', name: 'osd.process_type'},
            { data: 'type_of_testing_id_fix', name: 'mid.type_of_testing_id_fix'},
            { data: 'material_inward_no', name: 'mi.material_inward_no'},
            { data: 'material_inward_date', name: 'mi.material_inward_date'},
            { data: 'nabl_type_fix', name: 'mi.nabl_type_fix'},
            { data: 'test_at_fix', name: 'mi.test_at_fix'},
            { data: 'job_type_fix', name: 'mi.job_type_fix'},
            { data: 'dc_no', name: 'mi.dc_no'},
            { data: 'dc_date', name: 'mi.dc_date'},
            { data: 'po_no', name: 'mi.po_no'},
            { data: 'po_date', name: 'mi.po_date'},
            { data: 'type_of_job', name: 'toj.type_of_job'},
            { data: 'job_description', name: 'jd.job_description'},
            { data: 'part_no', name: 'mid.part_no'},
            { data: 'drg_no', name: 'mid.drg_no'},
            { data: 'material', name: 'm.material'},
            { data: 'heat_no', name: 'mid.heat_no'},
            { data: 'product_code', name: 'mid.product_code'},
            { data: 'observation_qty', name: 'osd.observation_qty'},
            { data: 'last_by', name: 'observation_sheet.last_by'}, 
            { data: 'last_on', name: 'observation_sheet.last_on'}, 
            { data: 'created_by', name: 'observation_sheet.created_by'}, 
            { data: 'created_on', name: 'observation_sheet.created_on'},
            { data: 'observation_sheet_sequence', name: 'observation_sheet.observation_sheet_sequence', visible: false, searchable: false, },
        ], 
        "initComplete": function(settings, json) {
            jQuery('#dyntable_processing').hide();
        }
    });

    jQuery(document).on('click', '.delete-sheet', function() {
        let id = jQuery(this).data('id');
        toastDelete(`Do you want to delete this record?`, function() {
            skipLoader = false;
            if (typeof showLoader === 'function') showLoader();
            jQuery.ajax({
                url: 'delete-observation_sheet',
                type: 'GET',
                data: { id: id },
                headers: headerOpt,
                success: function(res) {
                    if (res.response_code == 1) {
                        Swal.fire({
                            text: res.response_message || "Record Deleted.",
                            icon: 'success',
                            customClass: { confirmButton: 'btn btn-primary w-xs mt-2' },
                            buttonsStyling: false
                        });
                        table.ajax.reload();
                    } else {
                        toastr.error(res.response_message || "Error deleting record");
                    }
                },
                complete: function() {
                    if (typeof hideLoader === 'function') hideLoader();
                }
            });
        });
    });
</script>
<script src="{{ URL::asset('views/js/observation_sheet.js?ver='.getJsVersion()) }}"></script>
@endsection