@extends('layouts.master')
@section('title') Film @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Master @endslot
@slot('title')Film @endslot
@endcomponent

@include('modals.film_modal')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Film</h5>
                <div>
                    @if(hasAccess("film","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("film","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#FilmModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Film Size (inch)</th>
                            <th>L (inch)</th>
                            <th>W (inch)</th>
                            <th>SqIn</th>
                            <th>Film Size (cm)</th>
                            <th>L (cm)</th>
                            <th>W (cm)</th>
                            <th>SqCm</th>
                            <th>Status</th>
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
        var headerOpt = {'Authorization':'Bearer {{ Auth::user()->auth_token }}','X-CSRF-TOKEN':'{{ csrf_token() }}'};
        jQuery('#export-excel').on('click',function(){
            jQuery('.export_film').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'asc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Film List',
                title:"",
                className: 'export_film d-none',
                exportOptions: { columns: ':not(:eq(0))', modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-film",
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
                { data: 'options', name: 'options', orderable: false, searchable: false, },
                { data: 'film_size_inch', name: 'film_size_inch', },
                { data: 'length_inch', name: 'length_inch', },
                { data: 'width_inch', name: 'width_inch', },
                { data: 'sq_in', name: 'sq_in', },
                { data: 'film_size_cm', name: 'film_size_cm', },
                { data: 'length_cm', name: 'length_cm', },
                { data: 'width_cm', name: 'width_cm', },
                { data: 'sq_cm', name: 'sq_cm', },
                { data: 'status', name: 'film.status', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'film.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'film.created_on', },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-film",
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
                                customClass: {
                                    confirmButton: 'btn btn-primary w-xs mt-2',
                                },
                                buttonsStyling: false
                            })
                            table.row(jQuery(this)).draw(false);
                        } else {
                            console.log(data.response_message);
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
