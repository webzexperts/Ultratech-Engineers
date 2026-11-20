@extends('layouts.master')
@section('title') Job Card @endsection
@section('css')
<style>
    #JobCardWorksheetTable .select2-container,
    #JobCardModal .worksheet-priority-select + .select2-container {
        width: 100% !important;
        min-width: 100px !important;
    }
</style>
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Transaction @endslot
@slot('title') Job Card @endslot
@endcomponent

@include('modals.modals-transaction.job_card_modal')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Job Card</h5>
                <div>
                    @if(hasAccess("job_card", "export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("job_card", "add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#JobCardModal" id="add_job_card_btn">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Jobcard No.</th>
                            <th>Jobcard Date</th>
                            <th>Shift</th>
                            <th>Incharge</th>
                            <th>Radiographer</th>
                            <th>Camera</th>
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
    var headerOpt = {
        'Authorization': 'Bearer {{ Auth::user()->auth_token }}',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    };

    jQuery('#export-excel').on('click', function () {
        jQuery('.export_job_card').click();
    });

    var table = $('#dyntable').DataTable({
        "processing": false,
        "serverSide": true,
        "scrollX": true,
        "order": [[2, 'desc'], [10, 'desc']],
        dom: 'Blfrtip',
        buttons: [{
            extend: 'excel',
            filename: 'Job Card List',
            title: "",
            className: 'export_job_card d-none',
            exportOptions: {
                columns: function (idx, data, node) {
                    return idx !== 0 && table.column(idx).visible();
                },
                modifier: { page: 'all' }
            },
            action: newexportaction
        }],
        ajax: {
            url: "listing-job_card",
            type: "POST",
            headers: headerOpt,
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery('#dyntable_processing').hide();
                if (jqXHR.status == 401) {
                    console.log(jqXHR.statusText);
                }
            }
        },
        "columns": [
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
            { data: 'job_card_no', name: 'job_card.job_card_no' },
            { data: 'job_card_date', name: 'job_card.job_card_date' },
            { data: 'shift_name', name: 'shift.shift_name' },
            { data: 'incharge_name', name: 'incharge_person.operator' },
            { data: 'radiographer_name', name: 'radiographer_person.operator' },
            { data: 'rt_camera_name', name: 'rt_camera_name' },
            { data: 'last_by', name: 'last_by' },
            { data: 'last_on', name: 'job_card.last_on' },
            { data: 'created_by', name: 'created_by' },
            { data: 'created_on', name: 'job_card.created_on' },
            { data: 'job_card_sequence', name: 'job_card.job_card_sequence', visible: false, searchable: false }
        ]
    });

    // Delete Job Card
    jQuery('#dyntable tbody').on('click', '.delete-job_card', function () {
        var id = jQuery(this).data('id');
        toastDelete("Do you want to delete this record?", function () {
            jQuery.ajax({
                url: "delete-job_card",
                type: 'POST',
                data: { id: id, _token: '{{ csrf_token() }}' },
                headers: headerOpt,
                dataType: 'json',
                success: function (data) {
                    if (data.response_code == 1) {
                        Swal.fire({
                            text: data.response_message,
                            icon: 'success',
                            customClass: {
                                confirmButton: 'btn btn-primary w-xs mt-2',
                            },
                            buttonsStyling: false
                        });
                        table.draw(false);
                    } else {
                        toastr.error(data.response_message);
                    }
                },
                error: function (jqXHR) {
                    toastr.error('Something went wrong!');
                }
            });
        });
    });
</script>
<script src="{{ URL::asset('views/js/job_card.js?ver='.getJsVersion().time()) }}"></script>
@endsection
