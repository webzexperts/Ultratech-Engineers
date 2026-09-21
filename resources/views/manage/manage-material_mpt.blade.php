@extends('layouts.master')
@section('title') Material - MPT @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Store @endslot
@slot('title')Material - MPT @endslot
@endcomponent

@include('modals.material_mpt_modal')
@include('modals.modals-pending.pending_grn_for_material_mpt')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Material - MPT</h5>
                <div>
                    @if(hasAccess("material_mpt","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("material_mpt","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#MaterialMptModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Type</th>
                            <th>Material</th>
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
            jQuery('.export_material_mpt').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'desc'],[2, 'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Material-MPT List',
                title:"",
                className: 'export_material_mpt d-none',
                exportOptions: { columns: ':not(:eq(0))', modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-material_mpt",
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
                { data: 'table_unique_id', name: 'material_mpt.table_unique_id', },
                { data: 'mm_material', name: 'material_mpt.mm_material', },
                { data: 'mm_material_make', name: 'material_mpt.mm_material_make', },
                { data: 'mm_batch_no', name: 'material_mpt.mm_batch_no', },
                // { data: 'mm_identification_no', name: 'material_mpt.mm_identification_no', },
                { data: 'mm_expiry_date', name: 'material_mpt.mm_expiry_date', },
                { data: 'current_location', name: 'current_location.location_name', },
                { data: 'mm_status', name: 'material_mpt.mm_status', },
                { data: 'own_location', name: 'own_location.location_name', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'material_mpt.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'material_mpt.created_on', },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-material_mpt",
                    type: 'GET',
                    data: "id=" + data["mm_id"],
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