@extends('layouts.master')
@section('title') Service PO Short Close @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Transaction @endslot
@slot('title')Service PO Short Close @endslot
@endcomponent

@include('modals.modals-transaction.service_po_short_close_modal')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Service PO Short Close</h5>
                <div>
                    @if(hasAccess("service_po_short_close","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("service_po_short_close","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ServicePOShortCloseModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Short Close Date</th>
                            <th>PO No.</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Purpose</th>
                            <th>Ref. No. & Date</th>
                            <th>Bill To</th>
                            <th>For Location</th>
                            <th>Item</th>
                            <th>Item Group</th>
                            <th>Main Group</th>
                            <th>Sr. No.</th>
                            <th>Short Close Qty.</th>
                            <th>PO Qty.</th>
                            <th>Pending Qty.</th>
                            <th>Unit</th>
                            <th>Reason</th>
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
            jQuery('.export_sc').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Service PO Short Close List',
                title:"",
                className: 'export_sc d-none',
                exportOptions: { columns: ':not(:eq(0))', modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-service_po_short_close",
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
                { data: 'ser_po_sc_date', name: 'service_po_short_close.ser_po_sc_date', },
                { data: 'ser_po_number', name: 'service_po.ser_po_number', },
                { data: 'ser_po_date', name: 'service_po.ser_po_date', },
                { data: 'supplier_name', name: 'suppliers.supplier_name', },
                { data: 'purpose', name: 'service_po.purpose', },
                { data: 'ref_no_date', name: 'service_po.ref_no_date', },
                { data: 'bill_to', name: 'bill_to.location_name', },
                { data: 'for_location', name: 'for_location.location_name', },
                { data: 'item_name', name: 'item.item_name', },
                { data: 'item_group', name: 'item_group.item_group', },
                { data: 'main_group', name: 'item.item_type', },
                { data: 'name_for_display', name: 'sr.name_for_display' },
                { data: 'ser_po_sc_qty' , name: 'service_po_short_close.ser_po_sc_qty' },
                { data: 'po_qty' , name: 'service_po_details.po_qty' },
                { data: 'pending_qty' , name: 'pend.pending_qty' },
                { data: 'unit', name: 'unit.unit', },
                { data: 'reason_name', name: 'reason.reason_name', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'service_po_short_close.created_on', },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-service_po_short_close",
                    type: 'GET',
                    data: "id=" + data["ser_po_sc_id"],
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
    </script> -->
@endsection 