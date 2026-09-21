@extends('layouts.master')
@section('title') Item Return (Customer) @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Transaction @endslot
@slot('title')Item Return (Customer) @endslot
@endcomponent

@include('modals.modals-transaction.item_return_customer_modal')
@include('modals.modals-details.item_return_customer_details_modal')
@include('modals.modals-pending.pending_dc_for_item_return')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Item Return (Customer)</h5>
                <div>
                    @if(hasAccess("item_return_customer","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("item_return_customer","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ItemReturnCustomerModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>GRN No.</th>
                            <th>GRN Date</th>
                            <th>Customer</th>
                            <th>DC No.</th>
                            <th>DC Date</th>
                            <th>Item</th>
                            <th>Item Group</th>
                            <th>Main Group</th>
                            <th>Sr. No.</th>
                            <th>GRN Qty.</th>
                            <th>Unit</th>
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
            "order": [[2, 'desc'],[18, 'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Item Return (Customer) List',
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
                url: "listing-item_return_customer",
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
                { data: 'return_number', name: 'item_return_customer.return_number', },
                { data: 'return_date', name: 'item_return_customer.return_date', },
                { data: 'customer', name: 'customers.customer', },
                { data: 'dc_number', name: 'delivery_challan_customer.dc_number', },
                { data: 'dc_date', name: 'delivery_challan_customer.dc_date', },
                { data: 'item_name', name: 'item.item_name', },
                { data: 'item_group', name: 'item_group.item_group', },
                { data: 'main_group', name: 'item.item_type', },
                { data: 'name_for_display', name: 'sr.name_for_display' },
                { data: 'grn_qty', name: 'item_return_customer_details.grn_qty', },
                { data: 'unit', name: 'unit.unit', },
                { data: 'remark', name: 'item_return_customer_details.remark', },
                { data: 'prepared_by', name: 'prepared_by.person_name', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'item_return_customer.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'item_return_customer.created_on', },
                { data: 'return_sequence', name: 'item_return_customer.return_sequence', visible: false, searchable: false, },

            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-item_return_customer",
                    type: 'GET',
                    data: "id=" + data["return_id"],
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