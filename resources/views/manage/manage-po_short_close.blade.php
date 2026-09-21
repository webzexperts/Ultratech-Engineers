@extends('layouts.master')
@section('title') PO Short Close @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Marketing @endslot
@slot('title')PO Short Close @endslot
@endcomponent

@include('modals.po_short_close_modal')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">PO Short Close</h5>
                <div>
                    @if(hasAccess("po_short_close","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("po_short_close","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#POShortCloseModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Short Close Date</th>
                            <th>Type</th>
                            <th>PO No.</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Ref. No. & Date</th>
                            <th>Bill To</th>
                            <th>Ship To</th>
                            <th>Item</th>
                            <th>Item Group</th>
                            <th>Main Group</th>
                            <th>PO Qty. </th>
                            <th>Unit</th>
                            <th>Pend. PO Qty.</th>
                            <th>Short Close Qty</th>
                            <th>Reason</th>
                            <th>Del. Date</th>
                            <th>Remark</th>
                            <th>Prepared By</th>
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
                filename: 'PO Short Close List',
                title:"",
                className: 'export_sc d-none',
                exportOptions: { columns: ':not(:eq(0))', modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-po_short_close",
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
                { data: 'po_sc_date', name: 'po_short_close.po_sc_date', },
                { data: 'po_type_id', name: 'purchase_order.po_type_id', },
                { data: 'po_number', name: 'purchase_order.po_number', },
                { data: 'po_date', name: 'purchase_order.po_date', },
                { data: 'supplier_name', name: 'suppliers.supplier_name', },
                { data: 'ref_no_date', name: 'purchase_order.ref_no_date', },
                { data: 'bill_to', name: 'bill_to.location_name', },
                { data: 'ship_to', name: 'ship_to.location_name', },
                { data: 'item_name', name: 'item.item_name', },
                { data: 'item_group', name: 'item_group.item_group', },
                { data: 'main_group', name: 'item.item_type', },
                { data: 'pod_po_qty', name: 'purchase_order_details.pod_po_qty', },
                { data: 'unit', name: 'unit.unit', },
                { data: 'pending_qty' , name: 'pend.pending_qty' },
                { data: 'po_sc_qty' , name: 'po_short_close.po_sc_qty' },
                { data: 'reason_name', name: 'reason.reason_name', },
                { data: 'pod_del_date' , name: 'purchase_order_details.pod_del_date' },
                { data: 'pod_remark', name: 'purchase_order_details.pod_remark', },
                { data: 'prepared_by', name: 'admin.person_name', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'po_short_close.created_on', },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-po_short_close",
                    type: 'GET',
                    data: "id=" + data["po_sc_id"],
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