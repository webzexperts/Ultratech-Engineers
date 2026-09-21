@extends('layouts.master')
@section('title') Inter Location Transfer @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Store @endslot
@slot('title')Inter Location Transfer @endslot
@endcomponent

@include('modals.modals-transaction.inter_location_transfer_modal')
@include('modals.modals-details.inter_location_transfer_detail_modal')
@include('modals.modals-pending.pending_indent_for_inter_location_transfer')
@include('modals.modals-pending.pending_po_for_grn_modal')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Inter Location Transfer</h5>
                <div>
                    @if(hasAccess("inter_location_transfer","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("inter_location_transfer","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#InterLocationTransferModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%" data-exclude-search="13">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Type</th>
                            <th>DC No.</th>
                            <th>DC Date</th>
                            <th>To Location</th>
                            <th>Item</th>
                            <th>Item Group</th>
                            <th>Main Group</th>
                            <th>Sr. No.</th>
                            <th>DC Qty.</th>
                            <th>Unit</th>
                            <th>AERB No.</th>
                            <th>Application No.</th>
                            <th>Movement Approval</th>
                            <th>Validity</th>
                            <th>Remark</th>
                            <th>Prepared By</th>
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
        var headerOpt = {'Authorization':'Bearer {{ Auth::user()->auth_token }}','X-CSRF-TOKEN':'{{ csrf_token() }}'};
        jQuery('#export-excel').on('click',function(){
            jQuery('.export_inter_location_transfer').click();
        });
        var table = jQuery('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "scrollCollapse": true,
            // "paging": false,
            "fixedHeader": true,
            "order": [[3, 'desc'],[21, 'desc']],
            "dom": 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Inter Location Transfer List',
                title:"",
                className: 'export_inter_location_transfer d-none',
                exportOptions: {
                        columns: function(idx, data, node) {
                            return (idx !== 0 && idx !== 13) && table.column(idx).visible();
                        },
                        modifier: {
                            page: 'all'
                        }
                },
                action: newexportaction
            }],
            ajax: {
                url: "listing-inter_location_transfer",
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
                { data: 'dc_type_id', name: 'inter_location_transfer.dc_type_id', },
                { data: 'dc_number', name: 'inter_location_transfer.dc_number', },
                { data: 'dc_date', name: 'inter_location_transfer.dc_date', },
                { data: 'to_location', name: 'to_location.location_name', },
                { data: 'item_name', name: 'item.item_name', },
                { data: 'item_group', name: 'item_group.item_group', },
                { data: 'main_group', name: 'item.item_type', },
                { data: 'name_for_display', name: 'sr.name_for_display' },
                { data: 'dc_qty', name: 'inter_location_transfer_details.dc_qty', },
                { data: 'unit', name: 'unit.unit', },
                { data: 'aerb_no', name: 'inter_location_transfer_details.aerb_no', },
                { data: 'application_no', name: 'inter_location_transfer_details.application_no', },
                { data: 'movement_approval', name: 'inter_location_transfer_details.movement_approval', },
                { data: 'validity', name: 'inter_location_transfer_details.validity', },
                { data: 'remark', name: 'inter_location_transfer_details.remark', },
                { data: 'prepared_by' , name: 'prepared_by.person_name' },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'inter_location_transfer.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'inter_location_transfer.created_on', },
                { data: 'dc_sequence', name: 'inter_location_transfer.dc_sequence', visible: false, searchable: false, },

            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-inter_location_transfer",
                    type: 'GET',
                    data: "id=" + data["ilt_id"],
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