@extends('layouts.master')
@section('title') Item Issue Slip @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Master @endslot
@slot('title')Item Issue Slip @endslot
@endcomponent

@include('modals.issue_slip_modal')
@include('modals.modals-details.issue_slip_details')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Item Issue Slip</h5>
                <div>
                    @if(hasAccess("issue_slip","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("issue_slip","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#IssueSlipModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Issue Slip No.</th>
                            <th>Date</th>
                            <th>Item</th>
                            <th>Sr. No.</th>
                            <th>Receiver</th>
                            <th>Issue Type</th>
                            <th>Sp. Note</th>
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
            jQuery('.export_issue_slip').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[ 2, 'desc' ],[ 12, 'desc' ]],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Item Issue Slip List',
                title:"",
                className: 'export_issue_slip d-none',
                exportOptions: { columns: function(idx, data, node) { return idx !== 0 && table.column(idx).visible(); },
                modifier: { page: 'all' }},
                action: newexportaction
            }],
            ajax: {
                url: "listing-issue_slip",
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
                { data: 'iis_number', name: 'item_issue_slip.iis_number', },
                { data: 'iis_date', name: 'item_issue_slip.iis_date', },
                { data: 'item_name', name: 'item.item_name', },
                { data: 'iisd_sr_no_or_batch_no', name: 'item_issue_slip_details.iisd_sr_no_or_batch_no', },
                { data: 'user_name', name: 'admin.person_name', },
                { data: 'iisd_issue_type', name: 'item_issue_slip_details.iisd_issue_type', },
                { data: 'iis_special_note', name: 'item_issue_slip.iis_special_note', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'item_issue_slip.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'item_issue_slip.created_on', },
                { data: 'iis_sequence', name: 'item_issue_slip.iis_sequence', visible:false},
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-issue_slip",
                    type: 'GET',
                    data: "id=" + data["iis_id"],
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