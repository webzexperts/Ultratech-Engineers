@extends('layouts.master')
@section('title') Camera - RT @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Master @endslot
@slot('title')Camera - RT @endslot
@endcomponent

@include('modals.rt_camera_modal')
@include('modals.modals-details.rt_camera_details')
@include('modals.modals-pending.pending_for_rt_camera_modal')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Camera - RT</h5>
                <div>
                    @if(hasAccess("rt_camera","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("rt_camera","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#RTCameraModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%" data-exclude-search="15">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Type</th>
                            <th>Camera Name</th>
                            <th>Sr. No.</th>
                            <th>Isotope</th>
                            <th>Loading Date</th>
                            <th>Activity</th>
                            <th>Source Size</th>
                            <th>X-Ray KV</th>
                            <th>Focal Spot</th>
                            <!-- <th>Document Ref No.</th> -->
                            <!-- <th>Validity</th> -->
                            <th>Current Location</th>
                            <th>Status</th>
                            <th>Own Location</th>
                            <th>AERB No.</th>
                            <th>Application No.</th>
                            <th>Movement Approval</th>
                            <th>Validity</th>
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
            jQuery('.export_rt_camera').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[ 1, 'desc' ]],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Camera - RT List',
                title:"",
                className: 'export_rt_camera d-none',
                 exportOptions: {
                    columns: function(idx, data, node) {
                        return (idx !== 0 && idx !== 15) && table.column(idx).visible();
                    },
                    modifier: {
                        page: 'all'
                    }
                },
                action: newexportaction
            }],
            ajax: {
                url: "listing-rt_camera",
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
                { data: 'table_unique_id', name: 'rt_camera.table_unique_id', },
                { data: 'rt_camera_name', name: 'rt_camera.rt_camera_name', },
                { data: 'rt_serial_no', name: 'rt_camera.rt_serial_no', },
                { data: 'rt_isotope', name: 'rt_camera.rt_isotope', },
                { data: 'rtcd_last_of_loading_date', name: 'rt_camera_details.rtcd_last_of_loading_date', },
                { data: 'rtcd_initial_activity_ci', name: 'rt_camera_details.rtcd_initial_activity_ci', },
                { data: 'rtcd_source_size', name: 'rt_camera_details.rtcd_source_size', },
                { data: 'rt_x_ray', name: 'rt_camera.rt_x_ray', },
                { data: 'rt_focal_spot', name: 'rt_camera.rt_focal_spot', },
                // { data: 'rt_document_ref_no', name: 'rt_camera.rt_document_ref_no', },
                // { data: 'rt_validity_date', name: 'rt_camera.rt_validity_date', },
                { data: 'current_location', name: 'current_location.location_name', },
                { data: 'rt_status', name: 'rt_camera.rt_status', },
                { data: 'own_location', name: 'own_location.location_name', },
                { data: 'rt_aerb_no', name: 'rt_camera.rt_aerb_no', },
                { data: 'application_no', name: 'rt_camera.application_no', },
                { data: 'movement_approval', name: 'rt_camera.movement_approval', },
                { data: 'validity', name: 'rt_camera.validity', },
               
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'rt_camera.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'rt_camera.created_on', },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-rt_camera",
                    type: 'GET',
                    data: "id=" + data["rt_camera_id"],
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