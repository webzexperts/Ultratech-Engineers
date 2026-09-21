@extends('layouts.master')
@section('title') Production Entry @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Transaction @endslot
@slot('title')Production Entry @endslot
@endcomponent

@include('modals.modals-transaction.production_entry_modal')
@include('modals.modals-details.production_entry_details_modal')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Production Entry</h5>
                <div>
                    @if(hasAccess("production_entry","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("production_entry","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ProductionEntryModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Sr. No.</th>
                            <th>Date</th>
                            <th>Enclosure</th>
                            <th>Source</th>
                            <th>Production</th>
                            <th>Retake</th>
                            <th>Wastage</th>
                            <th>Total SQIN</th>
                            <th>Remark</th>
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
            jQuery('.export_production_entry').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'desc'],[14, 'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Production Entry List',
                title:"",
                className: 'export_production_entry d-none',
                exportOptions: {
                    columns: function(idx, data, node) {
                        return idx !== 0 && table.column(idx).visible();
                    },
                    modifier: { page: 'all' }
                },
                action: newexportaction
            }],
            ajax: {
                url: "listing-production_entry",
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
                { data: 'production_entry_no', name: 'production_entry.production_entry_no', },
                { data: 'production_entry_date', name: 'production_entry.production_entry_date', },
                { data: 'enclosure_name', name: 'enclosure.enclosure_name', },
                { data: 'source_id_fix', name: 'sub.source_id_fix', },
                { data: 'production_sq_in', name: 'sub.production_sq_in', },
                { data: 'retake_sq_in', name: 'sub.retake_sq_in',  },
                { data: 'repair_reshoot', name: 'sub.repair_reshoot',  },
                { data: 'total_sq_in', name: 'production_entry.total_sq_in',},
                { data: 'remark', name: 'production_entry.remark', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'production_entry.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'production_entry.created_on', },
                { data: 'production_entry_sequence', name: 'production_entry.production_entry_sequence', visible: false, searchable: false, },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-production_entry",
                    type: 'GET',
                    data: "id=" + data["production_entry_id"],
                    headers: headerOpt,
                    dataType: 'json',
                    processData: false,
                    success: function (data) {
                        if (data.response_code == 1) {
                            Swal.fire({
                                text: data.response_message,
                                icon: 'success',
                                customClass: { confirmButton: 'btn btn-primary w-xs mt-2', },
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
