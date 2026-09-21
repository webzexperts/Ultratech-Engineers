@extends('layouts.master')
@section('title') Material Outward @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Transaction @endslot
@slot('title') Material Outward @endslot
@endcomponent

@include('modals.modals-transaction.customer_dc_non_returnable_modal')
@include('modals.modals-details.customer_dc_non_returnable_details_modal')
@include('modals.modals-pending.pending_inward_for_customer_dc_non_returnable_modal')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Material Outward</h5>
                <div>
                    @if(hasAccess("customer_dc_non_returnable","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("customer_dc_non_returnable","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#CustomerDCNonReturnableModal" id="add_dc_btn">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>DC No.</th>
                            <th>DC Date</th>
                            <th>Customer</th>
                            <th>Inward No.</th>
                            <th>Inward Date</th>
                            <th>Challan No.</th>
                            <th>Challan Date</th>
                            <th>PO No.</th>
                            <th>PO Date</th>
                            <th>Type of Job</th>
                            <th>Job Description</th>
                            <th>Part No.</th>
                            <th>Drg. No.</th>
                            <th>Material</th>
                            <th>Heat No.</th>
                            <th>RT No.</th>
                            <th>Product Code</th>
                            <th>In. Qty.</th>
                            <th>DC Qty.</th>
                            <th>Remark</th>
                            <th>Prepared By</th>
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
    jQuery('#export-excel').on('click', function(){
        jQuery('.export_dc').click();
    });

    var table = $('#dyntable').DataTable({
        "processing": false,
        "serverSide": true,
        "scrollX": true,
        "order": [[2, 'desc'], [26, 'desc']],
        dom: 'Blfrtip',
        buttons: [{
            extend: 'excel',
            filename: 'Material Outward List',
            title: "",
            className: 'export_dc d-none',
            exportOptions: {
                columns: function(idx, data, node) {
                    return idx !== 0 && table.column(idx).visible();
                },
                modifier: { page: 'all' }
            },
            action: newexportaction
        }],
        ajax: {
            url: "listing-customer_dc_non_returnable",
            type: "POST",
            headers: headerOpt,
            error: function (jqXHR) {
                if (jqXHR.status == 401) {
                    console.log(jqXHR.statusText);
                } else {
                    console.log('Something went wrong!');
                }
            }
        },
        columns: [
            { data: 'options', name: 'options', orderable: false, searchable: false },
            { data: 'customer_dc_non_returnable_no', name: 'customer_dc_non_returnable.customer_dc_non_returnable_no' },
            { data: 'customer_dc_non_returnable_date', name: 'customer_dc_non_returnable.customer_dc_non_returnable_date' },
            { data: 'customer', name: 'customers.customer' },
            { data: 'mi_number', name: 'material_inward.material_inward_no' },
            { data: 'mi_date', name: 'material_inward.material_inward_date' },
            { data: 'mi_challan_number', name: 'material_inward.dc_no' },
            { data: 'mi_challan_date', name: 'material_inward.dc_date' },
            { data: 'mi_po_number', name: 'material_inward.po_no' },
            { data: 'mi_po_date', name: 'material_inward.po_date' },
            { data: 'type_of_job', name: 'type_of_job.type_of_job' },
            { data: 'job_description', name: 'job_descriptions.job_description' },
            { data: 'part_no', name: 'material_inward_details.part_no' },
            { data: 'drg_no', name: 'material_inward_details.drg_no' },
            { data: 'material', name: 'materials.material' },
            { data: 'mid_heat_no', name: 'material_inward_details.heat_no' },
            { data: 'mid_rt_no', name: 'material_inward_details.rt_no' },
            { data: 'mid_product_code', name: 'material_inward_details.product_code' },
            { data: 'in_qty', name: 'material_inward_details.quantity' },
            { data: 'dc_qty', name: 'customer_dc_non_returnable_details.dc_qty' },
            { data: 'remark', name: 'customer_dc_non_returnable_details.remark' },
            { data: 'prepared_by', name: 'prepared_by.person_name' },
            { data: 'last_by', name: 'last_by' },
            { data: 'last_on', name: 'customer_dc_non_returnable.last_on' },
            { data: 'created_by', name: 'created_by' },
            { data: 'created_on', name: 'customer_dc_non_returnable.created_on' },
            { data: 'customer_dc_non_returnable_sequence', name: 'customer_dc_non_returnable.customer_dc_non_returnable_sequence', visible: false, searchable: false, },
        ]
    });

    jQuery('#dyntable tbody').on('click', '.delete-customer_dc_non_returnable', function () {
        var data = table.row(jQuery(this).parents('tr')).data();
        var recordName = data ? (data["customer_dc_non_returnable_no"] || 'Record') : (jQuery(this).data('name') || 'Record');
        var recordId = data ? data["dc_id"] : jQuery(this).data('id');

         toastDelete("Do you want to delete this record?", function (){
            jQuery.ajax({
                url: "delete-customer_dc_non_returnable",
                type: 'POST',
                data: { id: recordId },
                headers: headerOpt,
                dataType: 'json',
                success: function (res) {
                    if (res.response_code == 1) {
                        toastSuccess("Record Deleted.");
                        table.ajax.reload(null, false);
                    } else {
                        toastr.error(res.response_message || "Failed to delete record.");
                    }
                },
                error: function (jqXHR) {
                    if (jqXHR.status == 401) {
                        console.log(jqXHR.statusText);
                    } else {
                        console.log('Something went wrong!');
                    }
                }
            });
        });
    });
</script>
@endsection
