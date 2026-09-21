@extends('layouts.master')
@section('title') Material Inspection @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Transaction @endslot
@slot('title')Material Inspection @endslot
@endcomponent

@include('modals.modals-transaction.material_inspection_modal')
@include('modals.modals-pending.pending_for_material_inspection_modal')


<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Material Inspection</h5>
                <div>
                    @if(hasAccess("material_inspection","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("material_inspection","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#MaterialInspectionModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Insp. No.</th>
                            <th>Insp. Date</th>
                            <th>Customer Code</th>
                            <th>Customer</th>
                            <th>Inward No.</th>
                            <th>Inward Date</th>
                            <th>Challan No.</th>
                            <th>Challan Date</th>
                            <th>Type of Test</th>
                            <th>Type Of Job</th>
                            <th>Job Description</th>
                            <th>Part No.</th>
                            <th>Challan Qty.</th>
                            <th>Insp. Qty.</th>
                            <th>Unit</th>
                            <th>Result</th>
                            <th>Rej. Reason</th>
                            <th>Inspected By</th>
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
            jQuery('.export_mins').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'desc'],[2, 'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Material Inspection List',
                title:"",
                className: 'export_mins d-none',
                exportOptions: { columns: ':not(:eq(0))', modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-material_inspection",
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
                { data: 'mins_number', name: 'material_inspection.mins_number', },
                { data: 'mins_date', name: 'material_inspection.mins_date', },
                { data: 'customer_code', name: 'customers.customer_code', },
                { data: 'customer', name: 'customers.customer', },
                { data: 'mi_number', name: 'material_inward.mi_number', },
                { data: 'mi_date', name: 'material_inward.mi_date', },
                { data: 'mi_challan_number', name: 'material_inward.mi_challan_number', },
                { data: 'mi_challan_date', name: 'material_inward.mi_challan_date', },
                { data: 'mid_test_method_id', name: 'material_inward_details.mid_test_method_id', },
                { data: 'type_of_job', name: 'type_of_job.type_of_job', },
                { data: 'job_description', name: 'job_descriptions.job_description', },
                { data: 'part', name: 'part.part', },
                { data: 'mid_qty', name: 'material_inward_details.mid_qty', },
                { data: 'mins_insp_qty', name: 'material_inspection.mins_insp_qty', },
                { data: 'unit', name: 'unit.unit', },
                { data: 'mins_result', name: 'material_inspection.mins_result', },
                { data: 'mins_rej_reason', name: 'material_inspection.mins_rej_reason', },
                { data: 'inspected_by_name', name: 'inspected_by.person_name', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'material_inspection.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'material_inspection.created_on', },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-material_inspection",
                    type: 'GET',
                    data: "id=" + data["mins_id"],
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