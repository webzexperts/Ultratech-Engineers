@extends('layouts.master')
@section('title') Item Stock Summary @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Reports @endslot
@slot('title')Item Stock Summary @endslot
@endcomponent

            
<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Item Stock Summary</h5>
                <div>
                    @if(hasAccess("item_stock_summary","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table id="dyntable" class="table nowrap align-middle table-bordered cul-search" style="width:100%">
                    <thead>
                        <tr>
                            <?php $LocationData = getCurrentLocation(); 
                            if($LocationData->location_type == 'HO'){ ?>
                                <th>Location</th>
                            <?php } ?>
                            <th>Item</th>
                            <th>Item Group</th>
                            <th>Main Group</th>
                            <th>Unit</th>
                            <th>Current Stock</th>
                            <th>Pend. Inter Location Receipt</th>
                            <th>Returnable (Supplier) </th>
                            <th>Returnable (Customer)</th>
                            <th>Total Stock</th>
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
        var table;
        var headerOpt = {'Authorization':'Bearer {{ Auth::user()->auth_token }}','X-CSRF-TOKEN':'{{ csrf_token() }}'};
        jQuery('#export-excel').on('click',function(){
            jQuery('.export_item_stock_summary').click();
        });

        setTimeout(() => {
            loadDataTable();
        }, 1000);

        function loadDataTable(){
            if (jQuery.fn.DataTable.isDataTable('#dyntable')) {
                jQuery('#dyntable').DataTable().destroy();
            }

            var columns = [];

            @if($LocationData->location_type == 'HO')
                columns.push({
                    data: 'location_name',
                    name: 'location.location_name'
                });
            @endif

            columns.push(
                { data: 'item_name', name: 'item.item_name', },
                { data: 'item_group', name: 'item_group.item_group', },
                { data: 'item_type', name: 'item.item_type', },
                { data: 'unit' , name: 'unit.unit' },
                { data: 'current_stock' , name: 'current_stock' },
                { data: 'pend_int_loc_qty' , name: 'pend_int_loc_qty' },
                { data: 'pend_sup_dc_qty' , name: 'pend_sup_dc_qty' },
                { data: 'pen_return_qty' , name: 'pen_return_qty' },
                { data: 'total_stock' , name: 'total_stock' },
            );

             table = $('#dyntable').DataTable({
                "processing": false,
                "serverSide": false,
                "scrollX": true,
                "order": [[0, 'asc']],
                dom: 'Blfrtip',
                buttons: [{
                    extend:'excel',
                    filename: 'Item Stock Summary List',
                    title:"",
                    className: 'export_item_stock_summary d-none',
                    exportOptions: { modifier: { page: 'all' } },
                    action: newexportaction
                }],
                ajax: {
                    url: "listing-item_stock_summary",
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
                columns: columns,
            });
        }
    </script> 
@endsection