

@extends('layouts.master')
@section('title') NABL Configuration @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Master @endslot
@slot('title') NABL Configuration @endslot
@endcomponent

@include('modals.nabl_configuration_modal')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">NABL Configuration</h5>
                <div>
                    @if(hasAccess("nabl_configuration","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("nabl_configuration","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#NABLConfigurationModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>NABL Location</th>
                            <th>TC No.</th>
                            <!-- <th>Location No.</th> -->
                            <!-- <th>NABL Type</th> -->
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
            jQuery('.export_nabl_configuration').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'asc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'NABL Configuration List',
                title:"",
                className: 'export_nabl_configuration d-none',
                exportOptions: { columns: ':not(:eq(0))', modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-nabl_configuration",
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
                { data: 'nabl_location', name: 'nabl_configurations.nabl_location', },
                { data: 'tc_no', name: 'nabl_configurations.tc_no', },
                //{ data: 'location_no', name: 'nabl_configurations.location_no', },
                //{ data: 'nabl_type', name: 'nabl_configurations.nabl_type', },
                { data: 'status', name: 'nabl_configurations.status', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'nabl_configurations.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'nabl_configurations.created_on', },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-nabl_configuration",
                    type: 'GET',
                    data: "id=" + data["nabl_id"],
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