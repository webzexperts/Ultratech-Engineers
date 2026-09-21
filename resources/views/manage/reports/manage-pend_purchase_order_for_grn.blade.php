@extends('layouts.master')
@section('title') Pending Purchase Order For GRN @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Reports @endslot
@slot('title')Pending Purchase Order For GRN @endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Pending Purchase Order for GRN</h5>
                <div>
                    @if(hasAccess("pending_purchase_order_for_grn","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
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
                            <th>PO Qty.</th>
                            <th>Unit</th>
                            <th>Pend. PO Qty.</th>
                            <th>Del. Date</th>
                            <th>Remark</th>
                            <th>Prepared By</th>
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
            "order": [[2, 'asc'],[16, 'asc']],
            dom: 'Blrtip',
            buttons: [{
                extend:'excel',
                filename: 'Pending Purchase Order For GRN List',
                title:"",
                className: 'export_po d-none',
                exportOptions: {
                        columns: function(idx, data, node) {
                            return table.column(idx).visible();
                        },
                        modifier: {
                            page: 'all'
                        }
                },
                action: newexportaction
            }],
            ajax: {
                url: "listing-pending_purchase_order_for_grn",
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
                { data: 'po_type_id', name: 'purchase_order.po_type_id' },
                { data: 'po_number', name: 'purchase_order.po_number' },
                { data: 'po_date', name: 'purchase_order.po_date' },
                { data: 'supplier_name', name: 'suppliers.supplier_name' },
                { data: 'ref_no_date', name: 'purchase_order.ref_no_date' },
                { data: 'bill_to', name: 'bill_to.location_name' },
                { data: 'ship_to', name: 'ship_to.location_name' },
                { data: 'item_name', name: 'item.item_name' },
                { data: 'item_group', name: 'item_group.item_group' },
                { data: 'main_group', name: 'item.item_type' },
                { data: 'pod_po_qty', name: 'purchase_order_details.pod_po_qty' },
                { data: 'unit', name: 'unit.unit' },
                { data: 'pending_qty', name: 'pend.pending_qty' },
                { data: 'pod_del_date', name: 'purchase_order_details.pod_del_date' },
                { data: 'pod_remark', name: 'purchase_order_details.pod_remark' },
                { data: 'prepared_by', name: 'admin.person_name' },
                { data: 'po_sequence', name: 'purchase_order.po_sequence', visible: false, searchable: false },
            ],
        });

    </script>
@endsection