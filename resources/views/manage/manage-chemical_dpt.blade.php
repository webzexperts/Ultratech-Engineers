@extends('layouts.master')
@section('title') Chemical - DPT @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Store @endslot
@slot('title')Chemical - DPT @endslot
@endcomponent

@include('modals.chemical_dpt_modal')
@include('modals.modals-pending.pending_for_chemical_dpt')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1"> Chemical - DPT</h5>
                <div>
                    @if(hasAccess("chemical_dpt","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("chemical_dpt","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ChemicalDptModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Type</th>
                            <th>Chemical</th>
                            <!-- <th>Designation</th> -->
                            <th>Make</th>
                            <th>Batch No.</th>
                            <!-- <th>Iden. No.</th> -->
                            <th>Expiry Date</th>
                            <th>Current Location</th>
                            <th>Status</th>
                            <th>Own Location</th>
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
            jQuery('.export_chemical_dpt').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'desc'],[2, 'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Chemical-DPT List',
                title:"",
                className: 'export_chemical_dpt d-none',
                exportOptions: { columns: ':not(:eq(0))', modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-chemical_dpt",
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
                { data: 'table_unique_id', name: 'dpt_chemical.table_unique_id', },
                { data: 'dpt_chemical', name: 'dpt_chemical.dpt_chemical', },
                //{ data: 'dpt_designation', name: 'dpt_chemical.dpt_designation', },
                { data: 'dpt_make', name: 'dpt_chemical.dpt_make', },
                { data: 'dpt_batch_no', name: 'dpt_chemical.dpt_batch_no', },
                // { data: 'dpt_identification_no', name: 'dpt_chemical.dpt_identification_no', },
                { data: 'dpt_expiry_date', name: 'dpt_chemical.dpt_expiry_date', },
                { data: 'current_location', name: 'current_location.location_name', },
                { data: 'dpt_status', name: 'dpt_chemical.dpt_status', },
                { data: 'own_location', name: 'own_location.location_name', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'dpt_chemical.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'dpt_chemical.created_on', },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-chemical_dpt",
                    type: 'GET',
                    data: "id=" + data["dpt_id"],
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