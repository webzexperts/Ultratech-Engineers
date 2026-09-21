@extends('layouts.master')
@section('title') Pending Material Indent for PO @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Reports @endslot
@slot('title')Pending Material Indent for PO @endslot
@endcomponent


<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Pending Material Indent for PO</h5>
                <div>
                    @if(hasAccess("pending_purchase_indent_for_po","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>From Location</th>
                            <th>To Location</th>
                            <th>Indent No.</th>
                            <th>Indent Date</th>
                            <th>Item</th>
                            <th>Item Group</th>
                            <th>Main Group</th>
                            <th>Indent Qty. </th>
                            <th>Unit</th>
                            <th>Remark</th>
                            <th>Indent By</th>   
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
            "order": [[3, 'asc'],[11, 'asc']],
            dom: 'Blrtip',
            buttons: [{
                extend:'excel',
                filename: 'Pending Material Indent for PO List',
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
                url: "listing-pend_purchase_indent_for_po",
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
                
                { data: 'current_location', name: 'location.location_name', },
                { data: 'to_location', name: 'location.location_name', },
                { data: 'pi_no', name: 'pi.pi_no', },
                { data: 'pi_date', name: 'pi.pi_date', },
                { data: 'item_name', name: 'item.item_name', },
                { data: 'item_group', name: 'item_group.item_group', },
                { data: 'main_group', name: 'item.item_type', },
                { data: 'indent_qty', name: 'purchase_indent_details.indent_qty', },
                { data: 'unit', name: 'unit.unit', },
                { data: 'remark', name: 'purchase_indent_details.remark', },
                { data: 'indent_by', name: 'admin.person_name', },
                { data: 'pi_sequence', name: 'pi.pi_sequence', visible: false, },
            ],
        });

    </script>
@endsection