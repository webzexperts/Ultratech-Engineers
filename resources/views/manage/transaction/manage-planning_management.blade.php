@extends('layouts.master')
@section('title') Planning Management @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Transaction @endslot
@slot('title') Planning Management @endslot
@endcomponent

@include('modals.modals-transaction.planning_management_modal')
@include('modals.modals-pending.pending_oa_for_planning_management')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Planning Management</h5>
                <div>
                    @if(hasAccess("planning_management","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("planning_management","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#PlanningManagementModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Planning No.</th>
                            <th>Date</th>
                            <th>OA No.</th>
                            <th>OA Date</th>
                            <th>Process At</th>
                            <th>Customer Code</th>
                            <th>Customer</th>
                            <th>Type Of Job</th>
                            <th>Job Description</th>
                            <th>Part No.</th>
                            <th>OA Type</th>
                            <th>Type of Test</th>
                            <th>Qty.</th>
                            <th>Unit</th>
                            <th>Remark</th>
                            <th>Sp. Note</th>
                            <th>Completion Avrg. Period</th>
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
            jQuery('.export_planning_management').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'asc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Planning Management List',
                title:"",
                className: 'export_planning_management d-none',
                exportOptions: { columns: ':not(:eq(0))', modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-planning_management",
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
                { data: 'pm_number', name: 'planning_management.pm_number', },
                { data: 'pm_date', name: 'planning_management.pm_date', },
                { data: 'oa_number', name: 'order_acceptance.oa_number', },
                { data: 'oa_date', name: 'order_acceptance.oa_date', },
                { data: 'pm_process_at', name: 'planning_management.pm_process_at', },
                { data: 'customer_code', name: 'customers.customer_code', },
                { data: 'customer', name: 'customers.customer', },
                { data: 'type_of_job', name: 'type_of_job.type_of_job', },
                { data: 'job_description', name: 'job_descriptions.job_description', },
                { data: 'part', name: 'part.part', },
                { data: 'oa_type_id', name: 'order_acceptance.oa_type_id', },
                { data: 'oad_test_method_id', name: 'order_acceptance_details.oad_test_method_id', },
                { data: 'pmd_plan_qty', name: 'planning_management_details.pmd_plan_qty', },
                { data: 'oa_qty_unit_name', name: 'oa_qty_unit.unit', },
                { data: 'oad_remark', name: 'order_acceptance_details.oad_remark', },
                { data: 'oa_special_note', name: 'order_acceptance.oa_special_note', },
                { data: 'pm_completion_avrg_period' , name: 'planning_management.pm_completion_avrg_period' },   
               
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'order_acceptance.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'order_acceptance.created_on', },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-planning_management",
                    type: 'GET',
                    data: "id=" + data["pm_id"],
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