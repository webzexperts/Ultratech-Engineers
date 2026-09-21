@extends('layouts.master')
@section('title') Equipment - MPT @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Store @endslot
@slot('title')Equipment - MPT @endslot
@endcomponent

@include('modals.equipment_mpt_modal')
@include('modals.modals-details.equipment_mpt_details_modal')
@include('modals.modals-pending.pending_for_equipment_mpt')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Equipment - MPT</h5>
                <div>
                    @if(hasAccess("equipment_mpt","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("equipment_mpt","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#EquipmentMPTModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Type</th>
                            <th>Equipment</th>
                            <th>Make</th>
                            <th>Sr. No.</th>
                            <th>Last Cal. Date</th>
                            <th>Next Cal. Date</th>
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
            jQuery('.export_equipment_mpt').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'desc'],[2, 'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Equipment - MPT List',
                title:"",
                className: 'export_equipment_mpt d-none',
                exportOptions: { columns: ':not(:eq(0))', modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-equipment_mpt",
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
                { data: 'table_unique_id', name: 'equipment_mpt.table_unique_id', },
                { data: 'em_equipment_name', name: 'equipment_mpt.em_equipment_name', },
                { data: 'em_make', name: 'equipment_mpt.em_make', },
                { data: 'em_serial_no', name: 'equipment_mpt.em_serial_no', },
                { data: 'em_last_cali_date', name: 'equipment_mpt.em_last_cali_date', },
                { data: 'em_next_cali_due_date', name: 'equipment_mpt.em_next_cali_due_date', },
                { data: 'current_location', name: 'current_location.location_name', },
                { data: 'em_status', name: 'equipment_mpt.em_status', },
                { data: 'own_location', name: 'own_location.location_name', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'equipment_mpt.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'equipment_mpt.created_on', },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-equipment_mpt",
                    type: 'GET',
                    data: "id=" + data["em_id"],
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