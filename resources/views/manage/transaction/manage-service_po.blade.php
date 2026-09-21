@extends('layouts.master')
@section('title') Service PO @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Transaction @endslot
@slot('title')Service PO @endslot
@endcomponent

@include('modals.modals-transaction.service_po_modal')
@include('modals.modals-details.service_po_details_modal')
@include('modals.modals-pending.pending_for_service_po_modal')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Service PO</h5>
                <div>
                    @if(hasAccess("service_po","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("service_po","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ServicePOModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>PO No.</th>
                            <th>PO Date</th>
                            <th>Supplier</th>
                            <th>Purpose</th>
                            <th>Ref. No. & Date</th>
                            <th>Bill To</th>
                            <th>For Location</th>
                            <th>Item</th>
                            <th>Item Group</th>
                            <th>Main Group</th>
                            <th>Sr. No.</th>
                            <th>PO Qty.</th>
                            <th>Unit</th>
                            <th>Rate/Unit</th>
                            <th>Amount</th>
                            <th>Del. Date</th>
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
            jQuery('.export_ser_po').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[2, 'desc'],[23, 'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Service PO List',
                title:"",
                className: 'export_ser_po d-none',
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
                url: "listing-service_po",
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
                { data: 'po_qty', name: 'service_po_details.po_qty', },
                { data: 'unit', name: 'unit.unit', },
                { data: 'rate_unit', name: 'service_po_details.rate_unit', },
                { data: 'amount', name: 'service_po_details.amount', },
                { data: 'del_date', name: 'service_po_details.del_date', },
                { data: 'remark', name: 'service_po_details.remark', },
                { data: 'prepared_by', name: 'prepared_by.person_name', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'service_po.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'service_po.created_on', },
                { data: 'ser_po_sequence', name: 'service_po.ser_po_sequence', visible: false, searchable: false, },

            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-service_po",
                    type: 'GET',
                    data: "id=" + data["ser_po_id"],
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

        jQuery('#dyntable tbody').on('click', '.email-service-po-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            var poId = data["ser_po_id"];
            
            jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
            
            jQuery.ajax({
                url: "{{ route('email_single_service_po') }}",
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