@extends('layouts.master')
@section('title') PO Mapping Process @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Transaction @endslot
@slot('title')PO Mapping Process @endslot
@endcomponent

@include('modals.modals-transaction.po_mapping_process_modal')
@include('modals.modals-pending.pending_material_insp_for_po_mapping_process')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center pb-2">
                    <h5 class="card-title mb-0 flex-grow-1">PO Mapping Process</h5>
                    <div>
                        @if(hasAccess("po_mapping_process","export"))
                            <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                        @endif

                        @if(hasAccess("po_mapping_process","add"))
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#POMappingProcessModal">Add</button>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th class="action_col">Actions</th>
                                <th>PO Mapping No.</th>
                                <th>PO Mapping Date</th>
                                <th>Customer Code</th>
                                <th>Customer</th>
                                <th>OA Type</th>
                                <th>OA No.</th>
                                <th>OA Date</th>
                                <th>PO No.</th>
                                <th>PO Date</th>
                                <th>Job Description</th>
                                <th>Part No.</th>
                                <th>OA Qty.</th>
                                <th>Unit</th>
                                <th>Planning Qty.</th>
                                <th>Type of Job</th>
                                <th>Type of Test</th>
                                <th>Mapping Qty.</th>
                                <th>Map Unit</th>
                                <th>Remark</th>
                                <th>Mapped By</th>
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
            jQuery('.export_po_mp').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'desc'],[2, 'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'PO Mapping Process List',
                title:"",
                className: 'export_po_mp d-none',
                exportOptions: { columns: ':not(:eq(0))', modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-po_mapping_process",
                type: "POST",
                headers: headerOpt,
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#dyntable_processing').hide();
                    if (jqXHR.status == 401) {
                        console.log(jqXHR.statusText);
                    } else {
                        console.log('Something went wrong!');
                    }
                    console.log(JSON.parse(jqXHR.responseText));
                }
            },
           columns:[
                { data:'options', orderable:false, searchable:false },
                { data:'po_mp_number', name:'pmp.po_mp_number' },
                { data:'po_mp_date', name:'pmp.po_mp_date' },
                { data:'customer_code', name:'customers.customer_code' },
                { data:'customer', name:'customers.customer' },
                { data:'oa_type_id', name:'oa.oa_type_id' },
                { data:'oa_number', name:'oa.oa_number' },
                { data:'oa_date', name:'oa.oa_date' },
                { data:'oa_po_number', name:'oa.oa_po_number' },
                { data:'oa_po_date', name:'oa.oa_po_date' },
                { data:'job_description', name:'job_descriptions.job_description' },
                { data:'part', name:'part.part' },
                { data:'oad_qty', name:'oad.oad_qty' },
                { data:'oad_unit', name:'oad_unit' },
                { data:'pmd_plan_qty', name:'pmd.pmd_plan_qty' },
                { data:'type_of_job', name:'type_of_job.type_of_job' },
                { data:'mid_test_method_id', name:'mid.mid_test_method_id' },
                { data:'total_oa_mapping_qty', name:'pmpd.po_mpd_oa_mapping_qty' },
                { data:'inward_unit', name:'inward_unit' },
                { data:'po_mp_special_note', name:'pmp.po_mp_special_note' },
                { data:'mapped_by_name', name:'mapped_by.person_name' },
                { data:'last_by', name:'updater.user_name' },
                { data:'last_on', name:'pmp.last_on' },
                { data:'created_by', name:'creator.user_name' },
                { data:'created_on', name:'pmp.created_on' }
            ],
        });

          jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-po_mapping_process",
                    type: 'GET',
                    data: "id=" + data["po_mp_id"],
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

        // Add edit and view handlers if needed
        jQuery('#dyntable tbody').on('click', '.edit-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Handle edit
        });

        jQuery('#dyntable tbody').on('click', '.view-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Handle view
        });
    </script>
@endsection