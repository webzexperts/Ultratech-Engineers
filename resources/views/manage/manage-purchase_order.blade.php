@extends('layouts.master')
@section('title') Purchase Order @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Store @endslot
@slot('title')Purchase Order @endslot
@endcomponent

@include('modals.purchase_order_modal')
@include('modals.supplier_modal')

@include('modals.modals-details.supplier_contact_modal')
@include('modals.city_modal')
@include('modals.state_modal')
@include('modals.country_modal')

@include('modals.modals-details.purchase_order_details')
@include('modals.modals-pending.pending_for_purchase_order')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Purchase Order</h5>
                <div>
                    @if(hasAccess("purchase_order","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("purchase_order","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#PurchaseOrderModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            {{-- <th>Type</th> --}}
                            <th>PO No.</th>
                            <th>PO Date</th>
                            <th>Supplier</th>
                            <th>Ref. No. & Date</th>
                            <th>Bill To</th>
                            <th>Ship To</th>
                            <th>Item</th>
                            <th>Item Group</th>
                            <th>Main Group</th>
                            <th>PO Qty.</th>
                            <th>Unit</th>
                            <th>Rate/Unit</th>
                            <th>Amount</th>
                            <th>Del. Date</th>
                            <th>Remark</th>
                            <th>Sp. Note</th>
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
            jQuery('.export_po').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[2, 'desc'],[22, 'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Purchase Order List',
                title:"",
                className: 'export_po d-none',
                exportOptions: {
                        columns: function(idx, data, node) {
                            return idx !== 0 && table.column(idx).visible();
                        },
                        modifier: {
                            page: 'all'
                        }
                },
                action: newexportaction
            }],
            ajax: {
                url: "listing-purchase_order",
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
                // { data: 'po_type_id', name: 'purchase_order.po_type_id', },
                { data: 'po_number', name: 'purchase_order.po_number', },
                { data: 'po_date', name: 'purchase_order.po_date', },
                { data: 'supplier_name', name: 'suppliers.supplier_name', },
                { data: 'ref_no_date', name: 'purchase_order.ref_no_date', },
                { data: 'bill_to_location_name', name: 'bill_to_location.location_name', },
                { data: 'ship_to_location_name', name: 'ship_to_location.location_name', },
                { data: 'item_name', name: 'item.item_name', },
                { data: 'item_group', name: 'item_group.item_group', },
                { data: 'main_group', name: 'item.item_type', },
                { data: 'pod_po_qty', name: 'purchase_order_details.pod_po_qty', },
                { data: 'unit', name: 'unit.unit', },
                { data: 'pod_rate_unit', name: 'purchase_order_details.pod_rate_unit', },
                { data: 'pod_amount', name: 'purchase_order_details.pod_amount', },
                { data: 'pod_del_date', name: 'purchase_order_details.pod_del_date', },
                { data: 'pod_remark', name: 'purchase_order_details.pod_remark', },
                { data: 'po_sp_note', name: 'purchase_order.po_sp_note', },
                { data: 'person_name', name: 'admin.person_name', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'purchase_order.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'purchase_order.created_on', },
                { data: 'po_sequence', name: 'purchase_order.po_sequence', visible: false, searchable: false, },

            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-purchase_order",
                    type: 'GET',
                    data: "id=" + data["po_id"],
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

        jQuery('#dyntable tbody').on('click', '.email-po-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            var poId = data["po_id"];
            
            jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
            
            jQuery.ajax({
                url: "{{ route('email_single_purchase_order') }}",
                type: 'POST',
                data: { id: poId },
                headers: headerOpt,
                success: function(response) {
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    if (response.status) {
                        Swal.fire({
                            text: response.message,
                            icon: 'success',
                            showCloseButton: true,
                            customClass: {
                                confirmButton: 'btn btn-primary w-xs mt-2',
                            },
                            buttonsStyling: false
                        });
                    } else {
                        Swal.fire({
                            text: response.message || 'Failed to send email.',
                            icon: 'error',
                            showCloseButton: true,
                            customClass: {
                                confirmButton: 'btn btn-primary w-xs mt-2',
                            },
                            buttonsStyling: false
                        });
                    }
                },
                error: function(jqXHR) {
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    var message = 'Something went wrong!';
                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                        message = jqXHR.responseJSON.message;
                    }
                    Swal.fire({
                        text: message,
                        icon: 'error',
                        showCloseButton: true,
                        customClass: {
                            confirmButton: 'btn btn-primary w-xs mt-2',
                        },
                        buttonsStyling: false
                    });
                }
            });
        });
    </script>
@endsection