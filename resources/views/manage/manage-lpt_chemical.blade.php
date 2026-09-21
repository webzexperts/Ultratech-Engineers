@extends('layouts.master')
@section('title') Chemical - LPT @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Master @endslot
@slot('title')Chemical - LPT @endslot
@endcomponent

@include('modals.lpt_chemical_modal')
@include('modals.modals-pending.pending_for_lpt_chemical_modal')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Chemical - LPT</h5>
                <div>
                    @if(hasAccess("chemical_lpt","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("chemical_lpt","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#LPTChemicalModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Inward Type</th>
                            <th>LPT Chemical</th>
                            <th>Chemical Name</th>
                            <th>Designation</th>
                            <th>Make</th>
                            <th>Batch No.</th>
                            <th>Mfg. Date</th>
                            <th>Identification No.</th>
                            <th>Status</th>
                            <th>Document Ref. No.</th>
                            <th>Validity Date</th>
                            <th>Remark</th>
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
            jQuery('.export_lpt_chemical').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'asc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Chemical - LPT List',
                title:"",
                className: 'export_lpt_chemical d-none',
                exportOptions: { columns: ':not(:eq(0))', modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-lpt_chemical",
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
                { data: 'lpt_inward_type', name: 'lpt_chemical.lpt_inward_type', },
                { data: 'item_name', name: 'item.item_name', },
                { data: 'lpt_chemical_name', name: 'lpt_chemical.lpt_chemical_name', },
                { data: 'lpt_designation', name: 'lpt_chemical.lpt_designation', },
                { data: 'lpt_make', name: 'lpt_chemical.lpt_make', },
                { data: 'lpt_batch_no', name: 'lpt_chemical.lpt_batch_no', },
                { data: 'lpt_mfg_date', name: 'lpt_chemical.lpt_mfg_date', },
                { data: 'lpt_identification_no', name: 'lpt_chemical.lpt_identification_no', },
                { data: 'lpt_status', name: 'lpt_chemical.lpt_status', },
                { data: 'lpt_document_ref_no', name: 'lpt_chemical.lpt_document_ref_no', },
                { data: 'lpt_validity_date', name: 'lpt_chemical.lpt_validity_date', },
                { data: 'lpt_remark', name: 'lpt_chemical.lpt_remark', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'lpt_chemical.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'lpt_chemical.created_on', },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-lpt_chemical",
                    type: 'GET',
                    data: "id=" + data["lpt_id"],
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