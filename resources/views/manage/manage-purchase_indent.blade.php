@extends('layouts.master')
@section('title') Material Indent @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Store @endslot
@slot('title')Material Indent @endslot
@endcomponent

@include('modals.purchase_indent_modal')
@include('modals.modals-details.purchase_indent_details')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Material Indent</h5>
                <div>
                    @if(hasAccess("purchase_indent","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("purchase_indent","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#PurchaseIndentModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Indent No.</th>
                            <th>Date</th>
                            <th>To Location</th>
                            <th>Item</th>
                            <th>Item Group</th>
                            <th>Main Group</th>
                            <th>Indent Qty.</th>
                            <th>Unit</th>
                            <th>Remark</th>
                            <th>Sp. Note</th>
                            <th>Indent By</th>
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
            jQuery('.export_pi').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[2, 'desc'],[16, 'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Material Indent List',
                title:"",
                className: 'export_pi d-none',
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
                url: "listing-purchase_indent",
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
                { data: 'pi_no', name: 'purchase_indent.pi_no', },
                { data: 'pi_date', name: 'purchase_indent.pi_date', },
                { data: 'to_location', name: 'to_location.location_name', },
                { data: 'item_name', name: 'item.item_name', },
                { data: 'item_group', name: 'item_group.item_group', },
                { data: 'main_group', name: 'item.item_type', },
                { data: 'indent_qty', name: 'purchase_indent_details.indent_qty', },
                { data: 'unit', name: 'unit.unit', },
                { data: 'remark', name: 'purchase_indent_details.remark', },
                { data: 'special_note', name: 'purchase_indent.special_note', },
                { data: 'indent_by', name: 'indent_by.person_name', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'purchase_indent.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'purchase_indent.created_on', },
                { data: 'pi_sequence', name: 'purchase_indent.pi_sequence', visible: false, },

            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-purchase_indent",
                    type: 'GET',
                    data: "id=" + data["pi_id"],
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