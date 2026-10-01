@extends('layouts.master')
@section('title') Film DC @endsection
@section('css')
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Transaction @endslot
@slot('title') Film DC @endslot
@endcomponent

@include('modals.modals-transaction.film_dc_modal')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Film DC</h5>
                <div>
                    @if(hasAccess("film_dc", "export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("film_dc", "add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#FilmDCModal" id="add_dc_btn">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>DC No.</th>
                            <th>DC Date</th>
                            <th>DC From Type</th>
                            <th>Customer Name</th>
                            <th>Customer Client</th>
                            <th>Job Type</th>
                            <!-- <th>Total SQIN</th> -->
                            <th>Film Size</th>
                            <th>DC Qty.</th>
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
    var headerOpt = {
        'Authorization': 'Bearer {{ Auth::user()->auth_token }}',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    };
    jQuery('#export-excel').on('click', function () {
        jQuery('.export_film_dc').click();
    });

    var table = $('#dyntable').DataTable({
        "processing": false,
        "serverSide": true,
        "scrollX": true,
        "order": [[2, 'desc'], [13, 'desc']],
        dom: 'Blfrtip',
        buttons: [{
            extend: 'excel',
            filename: 'Film DC List',
            title: "",
            className: 'export_film_dc d-none',
            exportOptions: {
                columns: function (idx, data, node) {
                    return idx !== 0 && table.column(idx).visible();
                },
                modifier: { page: 'all' }
            },
            action: newexportaction
        }],
        ajax: {
            url: "listing-film_dc",
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
            { data: 'options', name: 'options', orderable: false, searchable: false },
            { data: 'film_dc_no', name: 'film_dc.film_dc_no' },
            { data: 'film_dc_date', name: 'film_dc.film_dc_date' },
            { data: 'from_type_id', name: 'film_dc.from_type_id' },
            { data: 'customer_name', name: 'customers.customer' },
            { data: 'customer_client', name: 'film_dc.customer_client' },
            { data: 'job_type_fix', name: 'film_dc.job_type_fix' },
            { data: 'film_size_for_print', name: 'film_size_for_print' },
            { data: 'total_qty', name: 'film_dc.total_qty' },
           // { data: 'total_square_inch', name: 'film_dc.total_square_inch' },
            { data: 'last_by_name', name: 'last_user.person_name' },
            { data: 'last_on', name: 'film_dc.last_on' },
            { data: 'created_by_name', name: 'created_user.person_name' },
            { data: 'created_on', name: 'film_dc.created_on' },
            { data: 'film_dc_sequence', name: 'film_dc.film_dc_sequence', visible: false, searchable: false }
        ]
    });

    jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
        var data = table.row(jQuery(this).parents('tr')).data();
        toastDelete("Do you want to delete this record?", function () {
            jQuery.ajax({
                url: "delete-film_dc",
                type: 'GET',
                data: "id=" + data["film_dc_id"],
                headers: headerOpt,
                dataType: 'json',
                processData: false,
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
                        table.row(jQuery(this)).draw(false);
                    } else {
                        toastr.error(data.response_message);
                    }
                },
                error: function (jqXHR) {
                    if (jqXHR.status == 401) {
                        toastr.error(jqXHR.statusText);
                    } else {
                        toastr.error('Something went wrong!');
                    }
                }
            });
        });
    });
</script>
@endsection
