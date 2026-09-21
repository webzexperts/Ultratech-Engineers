@extends('layouts.master')
@section('title') Company Year @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('title') Company Year @endslot
@endcomponent

@include('modals.company_year_modal')
@include('modals.auth_modal')
<style>
    @media (min-width: 768px) {
        .col-md-6 { width: 100%; }
    }
</style>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Company Year</h5>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#CompanyYearModal">Add</button>
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>                           
                            <th class="action_col">Actions</th>
                            <th>Year</th>
                            <th>Start Date</th>
                            <th>End Date</th>
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

        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "order": [[1, 'asc']],
            ajax: {
                url: "listing-company_year",
                type: "POST",
                headers: headerOpt,
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#dyntable_processing').hide();
                    if (jqXHR.status == 401) {
                        // toastError(jqXHR.statusText);
                        console.log(jqXHR.statusText);
                    } else {
                        // toastError('Somthing went wrong!');
                        console.log('Somthing went wrong!');
                    }
                    console.log(JSON.parse(jqXHR.responseText));
                }
            },
            columns: [
                {
                    data: 'options',
                    name: 'options',
                    orderable: false,
                    searchable: false,
                },
                { data: 'year', name: 'company_years.year', },    
                { data: 'startdate', name: 'company_years.startdate', },    
                { data: 'enddate', name: 'company_years.enddate', },    
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'company_years.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'company_years.created_on', }
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-company_year",
                    type: 'GET',
                    data: "id=" + data["id"],
                    headers: headerOpt,
                    dataType: 'json',
                    processData: false,
                    success: function (data) {
                        if (data.response_code == 1) {
                            Swal.fire({
                                // title: 'Deleted!',
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