@extends('layouts.master')
@section('title') Pending Supplier DC for GRN @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Reports @endslot
@slot('title')Pending Supplier DC for GRN @endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Pending Supplier DC for GRN</h5>
                <div>
                    @if(hasAccess("pending_supplier_dc_for_grn","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>DC No.</th>
                            <th>DC Date</th>
                            <th>Supplier</th>
                            <th>Ref. No. & Date</th>
                            <th>Item</th>
                            <th>Item Group</th>
                            <th>Main Group</th>
                            <th>Sr. No.</th>
                            <th>Pend. DC Qty.</th>
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
            jQuery('.export_pending_supplier_dc_for_grn').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[2, 'asc'],[13, 'asc']],
            dom: 'Blrtip',
            buttons: [{
                extend:'excel',
                filename: 'Pending Supplier DC for GRN List',
                title:"",
                className: 'export_pending_supplier_dc_for_grn d-none',
                exportOptions: {  modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-pending_supplier_dc_for_grn",
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
                { data: 'sup_dc_type_id', name: 'supplier_dc.sup_dc_type_id' },
                { data: 'sup_dc_number', name: 'supplier_dc.sup_dc_number' },
                { data: 'sup_dc_date', name: 'supplier_dc.sup_dc_date' },
                { data: 'supplier_name', name: 'suppliers.supplier_name' },
                { data: 'ref_no_date', name: 'supplier_dc.ref_no_date' },
                { data: 'item_name', name: 'item.item_name' },
                { data: 'item_group', name: 'item_group.item_group' },
                { data: 'main_group', name: 'item.item_type' },
                { data: 'name_for_display', name: 'sr.name_for_display' },
                { data: 'pending_qty', name: 'pend.pending_qty' },
                { data: 'unit', name: 'unit.unit' },
                { data: 'remark', name: 'supplier_dc_details.remark' },
                { data: 'prepared_by', name: 'admin.person_name' },
                { data: 'sup_dc_sequence', name: 'supplier_dc.sup_dc_sequence', visible: false, searchable: false }
            ],
        });

    </script>
@endsection