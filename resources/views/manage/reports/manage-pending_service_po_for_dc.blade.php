@extends('layouts.master')
@section('title') Pending Service PO for DC @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Reports @endslot
@slot('title')Pending Service PO for DC @endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Pending Service PO for DC</h5>
                <div>
                    @if(hasAccess("pending_service_po_for_dc","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
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
                            <th>Pend. PO Qty.</th>
                            <th>Unit</th>
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
            jQuery('.export_pending_service_po_for_dc').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'asc'],[15, 'asc']],
            dom: 'Blrtip',
            buttons: [{
                extend:'excel',
                filename: 'Pending Service PO for DC List',
                title:"",
                className: 'export_pending_service_po_for_dc d-none',
                exportOptions: {  modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-pending_service_po_for_dc",
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
                { data: 'ser_po_number', name: 'service_po.ser_po_number' },
                { data: 'ser_po_date', name: 'service_po.ser_po_date' },
                { data: 'supplier_name', name: 'suppliers.supplier_name' },
                { data: 'purpose', name: 'service_po.purpose' },
                { data: 'ref_no_date', name: 'service_po.ref_no_date' },
                { data: 'bill_to', name: 'bill_to.location_name' },
                { data: 'for_location', name: 'for_location.location_name' },
                { data: 'item_name', name: 'item.item_name' },
                { data: 'item_group', name: 'item_group.item_group' },
                { data: 'main_group', name: 'item.item_type' },
                { data: 'name_for_display', name: 'sr.name_for_display' },
                { data: 'pending_qty', name: 'pend.pending_qty' },
                { data: 'unit', name: 'unit.unit' },
                { data: 'remark', name: 'service_po_details.remark' },
                { data: 'prepared_by', name: 'admin.person_name' },
                { data: 'ser_po_sequence', name: 'service_po.ser_po_sequence', visible: false, searchable: false },
            ],
        });

    </script>
@endsection