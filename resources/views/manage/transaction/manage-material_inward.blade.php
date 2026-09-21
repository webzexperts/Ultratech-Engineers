@extends('layouts.master')
@section('title') Material Inward @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Transaction @endslot
@slot('title')Material Inward @endslot
@endcomponent

{{-- Modals are created in Stage 2 (parent) and Stage 3 (child details). --}}
@include('modals.modals-transaction.material_inward_modal')
@include('modals.modals-details.material_inward_details_modal')
@include('modals.modals-details.copy_material_inward_details_modal')
@include('modals.modals-pending.pending_repair_reports_modal')
@include('modals.customer_modal')
@include('modals.modals-details.contact_modal')
@include('modals.city_modal')
@include('modals.state_modal')
@include('modals.country_modal')
@include('modals.type_of_job_modal')
@include('modals.job_description_modal')
@include('modals.material_modal')
@include('modals.area_of_coverage_modal')
@include('modals.procedure_reference_modal')
@include('modals.evaluation_as_per_modal')
@include('modals.acceptance_standard_modal')
@include('modals.part_modal')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Material Inward</h5>
                <div>
                    @if(hasAccess("material_inward","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("material_inward","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#MaterialInwardModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Inward No.</th>
                            <th>Date</th>
                            <th>NABL</th>
                            <th>Test At</th>
                            <th>Type</th>
                            <th>Customer</th>
                            <th>DC No.</th>
                            <th>DC Date</th>
                            <th>PO No.</th>
                            <th>PO Date</th>
                            <th>Type of Test</th>
                            <th>Type of Job</th>
                            <th>Job Desc.</th>
                            <th>Part No.</th>
                            <th>Drg. No.</th>
                            <th>Material</th>
                            <th>Heat No.</th>
                            <th>RT No.</th>
                            <th>Product Code</th>
                            <th>Qty.</th>
                            <!-- <th>Prepared By</th> -->
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
            jQuery('.export_material_inward').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'desc'],[25, 'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Material Inward List',
                title:"",
                className: 'export_material_inward d-none',
                exportOptions: {
                    columns: function(idx, data, node) {
                        return idx !== 0 && table.column(idx).visible();
                    },
                    modifier: { page: 'all' }
                },
                action: newexportaction
            }],
            ajax: {
                url: "listing-material_inward",
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
                { data: 'material_inward_no', name: 'material_inward.material_inward_no', },
                { data: 'material_inward_date', name: 'material_inward.material_inward_date', },
                { data: 'nabl_type_fix', name: 'material_inward.nabl_type_fix', },
                { data: 'test_at_fix', name: 'material_inward.test_at_fix', },
                { data: 'job_type_fix', name: 'material_inward.job_type_fix', },
                { data: 'customer', name: 'customers.customer', },
                { data: 'dc_no', name: 'material_inward.dc_no', },
                { data: 'dc_date', name: 'material_inward.dc_date', },
                { data: 'po_no', name: 'material_inward.po_no', },
                { data: 'po_date', name: 'material_inward.po_date', },
                { data: 'type_of_testing_id_fix', name: 'material_inward_details.type_of_testing_id_fix', },
                { data: 'type_of_job', name: 'type_of_job.type_of_job', },
                { data: 'job_description', name: 'job_descriptions.job_description', },
                { data: 'part_no', name: 'material_inward_details.part_no', },
                { data: 'drg_no', name: 'material_inward_details.drg_no', },
                { data: 'material', name: 'materials.material', },
                { data: 'heat_no', name: 'material_inward_details.heat_no', },
                { data: 'rt_no', name: 'material_inward_details.rt_no', },
                { data: 'product_code', name: 'material_inward_details.product_code', },
                { data: 'quantity', name: 'material_inward_details.quantity', },
                // { data: 'prepared_by', name: 'prepared_by.person_name', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'material_inward.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'material_inward.created_on', },
                { data: 'material_inward_sequence', name: 'material_inward.material_inward_sequence', visible: false, searchable: false, },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-material_inward",
                    type: 'GET',
                    data: "id=" + data["material_inward_id"],
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


