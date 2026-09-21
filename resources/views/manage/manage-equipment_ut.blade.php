@extends('layouts.master')
@section('title') Equipment - UT @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Store @endslot
@slot('title')Equipment - UT @endslot
@endcomponent

@include('modals.equipment_ut_modal')
@include('modals.modals-details.equipment_ut_details_modal')
@include('modals.modals-pending.pending_for_equipment_ut')


<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Equipment - UT</h5>
                <div>
                    @if(hasAccess("equipment_ut","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("equipment_ut","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#EquipmentUTModal">Add</button>
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
            jQuery('.export_equipment_ut').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'desc'],[2, 'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Equipment - UT List',
                title:"",
                className: 'export_equipment_ut d-none',
                exportOptions: { columns: ':not(:eq(0))', modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-equipment_ut",
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
                { data: 'table_unique_id', name: 'equipment_ut.table_unique_id', },
                { data: 'eu_equipment_name', name: 'equipment_ut.eu_equipment_name', },
                { data: 'eu_make', name: 'equipment_ut.eu_make', },
                { data: 'eu_serial_no', name: 'equipment_ut.eu_serial_no', },
                { data: 'eu_last_cali_date', name: 'equipment_ut.eu_last_cali_date', },
                { data: 'eu_next_cali_due_date', name: 'equipment_ut.eu_next_cali_due_date', },
                { data: 'current_location', name: 'current_location.location_name', },
                { data: 'eu_status', name: 'equipment_ut.eu_status', },
                { data: 'own_location', name: 'own_location.location_name', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'equipment_ut.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'equipment_ut.created_on', },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-equipment_ut",
                    type: 'GET',
                    data: "id=" + data["eu_id"],
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