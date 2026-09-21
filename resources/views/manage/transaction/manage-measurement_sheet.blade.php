@extends('layouts.master')
@section('title') Measurement Sheet @endsection
@section('css')
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Transaction @endslot
@slot('title') Measurement Sheet @endslot
@endcomponent

@include('modals.modals-transaction.measurement_sheet_modal')
@include('modals.modals-pending.pending_rt_for_measurement_modal')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Measurement Sheet</h5>
                <div>
                    @if(hasAccess("measurement_sheet", "export"))
                    <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("measurement_sheet", "add"))
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#MeasurementSheetModal">Add</button>
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
                            <th>Total Ir-192</th>
                            <th>Total Co-60</th>
                            <th>Total X-Ray</th>
                            <th>Total Ir-192 Repair</th>
                            <th>Total Co-60 Repair</th>
                            <th>Total X-Ray Repair</th>
                            <th>Modified By</th>
                            <th>Modified On</th>
                            <th>Created By</th>
                            <th>Created On</th>
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
    var headerOpt = {
        'Authorization': 'Bearer {{ Auth::user()->auth_token }}', 'X-CSRF-TOKEN': '{{ csrf_token() }}'
    };
    jQuery('#export-excel').on('click', function() {
        jQuery('.export_measurement_sheet').click();
    });
    var table = $('#dyntable').DataTable({
        "processing": false,
        "serverSide": true,
        "scrollX": true,
        "order": [[2, 'desc'], [14, 'desc']],
        dom: 'Blfrtip',
        buttons: [{
            extend: 'excel',
            filename: 'Measurement Sheet List',
            title: "",
            className: 'export_measurement_sheet d-none',
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
            url: "listing-measurement_sheet",
            type: "POST",
            headers: headerOpt,
            error: function(jqXHR, textStatus, errorThrown) {
                jQuery('#dyntable_processing').hide();
                if (jqXHR.status == 401) {
                    console.log(jqXHR.statusText);
                }
            }
        },
        "columns": [
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
                { data: 'measurement_sheet_no', name: 'measurement_sheet.measurement_sheet_no'}, 
                { data: 'measurement_sheet_date', name: 'measurement_sheet.measurement_sheet_date'}, 
                { data: 'customer_name', name: 'customers.customer'}, 
                { data: 'total_ir_192_sqin', name: 'measurement_sheet.total_ir_192_sqin'}, 
                { data: 'total_co_60_sqin', name: 'measurement_sheet.total_co_60_sqin'}, 
                { data: 'total_x_ray_sqin', name: 'measurement_sheet.total_x_ray_sqin'}, 
                { data: 'total_ir_192_repair_sqin', name: 'measurement_sheet.total_ir_192_repair_sqin'}, 
                { data: 'total_co_60_repair_sqin', name: 'measurement_sheet.total_co_60_repair_sqin'}, 
                { data: 'total_x_ray_repair_sqin', name: 'measurement_sheet.total_x_ray_repair_sqin'}, 
                { data: 'last_by', name: 'measurement_sheet.last_by',}, 
                { data: 'last_on', name: 'measurement_sheet.last_on',}, 
                { data: 'created_by', name: 'measurement_sheet.created_by',}, 
                { data: 'created_on', name: 'measurement_sheet.created_on', },
                { data: 'measurement_sheet_sequence', name: 'measurement_sheet.measurement_sheet_sequence', visible: false, searchable: false },
        ], 
        "initComplete": function(settings, json) {
            jQuery('#dyntable_processing').hide();
        }
    });

    // Delete Button Action on Datatable with Standard toastDelete Confirmation
    jQuery(document).on('click', '.delete-sheet', function() {
        let id = jQuery(this).data('id');
        let rowData = table.row(jQuery(this).closest('tr')).data();
        let sheetNo = rowData ? rowData.measurement_sheet_no : 'this Measurement Sheet';

        toastDelete(`Do you want to delete this record?`, function() {
            skipLoader = false;
            if (typeof showLoader === 'function') showLoader();
            jQuery.ajax({
                url: 'delete-measurement_sheet',
                type: 'GET',
                data: {
                    id: id
                },
                headers: headerOpt,
                success: function(res) {
                    if (res.response_code == 1) {
                        Swal.fire({
                            text: res.response_message || "Record Deleted.",
                            icon: 'success',
                            customClass: {
                                confirmButton: 'btn btn-primary w-xs mt-2'
                            },
                            buttonsStyling: false
                        });
                        table.ajax.reload();
                    } else {
                        toastr.error(res.response_message || "Error deleting record");
                    }
                },
                error: function() {
                    toastr.error("Error deleting record");
                },
                complete: function() {
                    if (typeof hideLoader === 'function') hideLoader();
                }
            });
        });
    });

</script>
<script src="{{ URL::asset('views/js/measurement_sheet.js?ver='.getJsVersion()) }}"></script>
@endsection
