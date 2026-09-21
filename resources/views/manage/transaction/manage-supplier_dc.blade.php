@extends('layouts.master')
@section('title')Supplier DC  @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Store @endslot
@slot('title')Supplier DC @endslot
@endcomponent

@include('modals.modals-transaction.supplier_dc_modal')
@include('modals.modals-details.supplier_dc_details_modal') 
@include('modals.modals-pending.pending_service_po_for_supplier_dc_modal')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Supplier DC </h5>
                <div>
                    @if(hasAccess("supplier_dc","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("supplier_dc","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#SupplierDCModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%" data-exclude-search="14">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Type</th>
                            <th>DC No.</th>
                            <th>DC Date</th>
                            <th>Supplier</th>
                            <th>Ref. No. & Date</th>
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
            jQuery('.export_supplier_dc').click();
        });
        var table = jQuery('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "scrollCollapse": true,
            // "paging": false,
            "fixedHeader": true,
            "order": [[3, 'desc'],[22, 'desc']],
            "dom": 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Supplier DC List',
                title:"",
                className: 'export_supplier_dc d-none',
                exportOptions: {
                    columns: function(idx, data, node) {
                        return (idx !== 0 && idx !== 14) && table.column(idx).visible();
                    },
                    modifier: {
                        page: 'all'
                    }
                },
                action: newexportaction
            }],
            ajax: {
                url: "listing-supplier_dc",
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
                { data: 'options', orderable: false, searchable: false },

                { data: 'sup_dc_type_id', name: 'supplier_dc.sup_dc_type_id' },
                { data: 'sup_dc_number', name: 'supplier_dc.sup_dc_number' },
                { data: 'sup_dc_date', name: 'supplier_dc.sup_dc_date' },
                { data: 'supplier_name', name: 'suppliers.supplier_name' },
                { data: 'ref_no_date', name: 'supplier_dc.ref_no_date' },

                { data: 'item_name', name: 'item.item_name' },
                { data: 'item_group', name: 'item_group.item_group' },
                { data: 'item_type', name: 'item.item_type' },
                { data: 'name_for_display', name: 'sr.name_for_display' },

                { data: 'return_qty', name: 'supplier_dc_details.return_qty' },
                { data: 'unit', name: 'unit' },
                { data: 'aerb_no', name: 'supplier_dc_details.aerb_no', },
                { data: 'application_no', name: 'supplier_dc_details.application_no', },
                { data: 'movement_approval', name: 'supplier_dc_details.movement_approval', },
                { data: 'validity', name: 'supplier_dc_details.validity', },
                { data: 'remark', name: 'supplier_dc_details.remark' },

                { data: 'user_name', name: 'admin.person_name' },
                { data: 'last_by', name: 'supplier_dc.last_by' },
                { data: 'last_on', name: 'supplier_dc.last_on' },
                { data: 'created_by', name: 'supplier_dc.created_by' },
                { data: 'created_on', name: 'supplier_dc.created_on' },

                { data: 'sup_dc_sequence', name: 'supplier_dc.sup_dc_sequence', visible: false }
            ]
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-supplier_dc",
                    type: 'GET',
                    data: "id=" + data["sup_dc_id"],
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