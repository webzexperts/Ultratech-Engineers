@extends('layouts.master')
@section('title') Location @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Master @endslot
@slot('title')Location @endslot
@endcomponent

@include('modals.location_modal')
@include('modals.city_modal')
@include('modals.state_modal')
@include('modals.country_modal')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Location</h5>
                <div>
                    @if(hasAccess("location","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("location","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#LocationModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Code</th>
                            <th>GST Billing</th>
                            <th>Company</th>
                            <th>GSTIN</th>
                            <th>NABL</th>
                            <th>Location</th>
                            <th>ILAC</th>
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
            jQuery('.export_location').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'asc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Location List',
                title:"",
                className: 'export_location d-none',
                exportOptions: { columns: ':not(:eq(0))', modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-location",
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
                { data: 'location_name', name: 'location.location_name', },
                { data: 'location_type', name: 'location.location_type', },
                { data: 'location_code', name: 'location.location_code', },
                { data: 'gst_bill_location', name: 'location.gst_bill_location', },
                { data: 'location_company_name', name: 'location.location_company_name', },
                { data: 'location_gstin', name: 'location.location_gstin', },
                { data: 'location_nabl_applicable', name: 'location.location_nabl_applicable', },
                { data: 'nabl_location', name: 'nabl_configurations.nabl_location', },
                { data: 'location_ilac_applicable', name: 'location.location_ilac_applicable', },
                { data: 'location_status', name: 'location.location_status', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'location.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'location.created_on', },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-location",
                    type: 'GET',
                    data: "id=" + data["location_id"],
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