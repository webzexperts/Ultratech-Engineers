@extends('layouts.master')
@section('title') Item @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Master @endslot
@slot('title')Item @endslot
@endcomponent

@include('modals.item_modal')
@include('modals.unit_modal')
@include('modals.item_group_modal')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Item</h5>
                <div>
                    @if(hasAccess("item","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("item","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ItemModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Item</th>
                            <th>Item Group</th>
                            <th>Main Group</th>
                            <th>Ide. Req.</th>
                            <th>Unit</th>
                            <th>Sec. Unit</th>
                            <th>Conv. Factor</th>
                            <th>Inter Tran.</th>
                            <th>MSL</th>
                            <th>Doc. Ref. No.</th>
                            <th>Validity</th>
                            <th>Status</th>
                            <th>Modified By</th>
                            <th>Modified On</th>
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
            jQuery('.export_item').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'asc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Item List',
                title:"",
                className: 'export_item d-none',
                exportOptions: { columns: ':not(:eq(0))', modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-item",
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
                { data: 'item_name', name: 'item_name', },
                { data: 'item_group', name: 'item_group.item_group', },
                { data: 'item_type', name: 'item.item_type', },
                { data: 'identification_req', name: 'identification_req', },
                { data: 'unit', name: 'unit.unit', },
                { data: 'sec_unit', name: 'sec_unit', },
                { data: 'conv_factor', name: 'item.conv_factor', defaultContent: '' },
                { data: 'inter_location_transfer', name: 'item.inter_location_transfer', },
                { data: 'min_stock_level', name: 'min_stock_level', },
                { data: 'document_ref_no', name: 'document_ref_no', },
                { data: 'validity_date', name: 'item.validity_date', },
                { data: 'status', name: 'item.status', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'item.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'item.created_on', },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-item",
                    type: 'GET',
                    data: "id=" + data["id"],
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