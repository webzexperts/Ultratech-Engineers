@extends('layouts.master')
@section('title') Non Returnable Material Challan @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Transaction @endslot
@slot('title') Non Returnable Material Challan @endslot
@endcomponent

@include('modals.modals-transaction.non_ret_mat_challan_modal')
@include('modals.modals-details.non_ret_mat_challan_details_modal')
@include('modals.modals-pending.pending_inward_for_dc_modal')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Non Returnable Material Challan</h5>
                <div>
                    @if(hasAccess("non_returnable_material_challan","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("non_returnable_material_challan","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#NRMCModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>DC No.</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Customer Code</th>
                            <th>Customer</th>
                            <th>Inward No.</th>
                            <th>Inward Date</th>
                            <th>Challan No.</th>
                            <th>Challan Date</th>
                            <th>Type Of Job</th>
                            <th>Job Description</th>
                            <th>Part No.</th>
                            <th>Method</th>
                            <th>DC Qty.</th>
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
            jQuery('.export_nrmc').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'desc'],[2, 'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Non. Ret. Mat. Challan List',
                title:"",
                className: 'export_nrmc d-none',
                exportOptions: { columns: ':not(:eq(0))', modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-non_returnable_material_challan",
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
                { data: 'nrmc_number', name: 'non_ret_mat_challan.nrmc_number', },
                { data: 'nrmc_date', name: 'non_ret_mat_challan.nrmc_date', },
                { data: 'nrmcd_type', name: 'non_ret_mat_challan_details.nrmcd_type', },
                { data: 'customer_code', name: 'customers.customer_code', },
                { data: 'customer', name: 'customers.customer', },
                { data: 'mi_number', name: 'material_inward.mi_number', },
                { data: 'mi_date', name: 'material_inward.mi_date', },
                { data: 'mi_challan_number', name: 'material_inward.mi_challan_number', },
                { data: 'mi_challan_date', name: 'material_inward.mi_challan_date', },
                { data: 'type_of_job', name: 'type_of_job.type_of_job', },
                { data: 'job_description', name: 'job_descriptions.job_description', },
                { data: 'part', name: 'part.part', },
                { data: 'mid_test_method_id', name: 'material_inward_details.mid_test_method_id', },
                { data: 'nrmcd_qty', name: 'non_ret_mat_challan_details.nrmcd_qty', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'non_ret_mat_challan.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'non_ret_mat_challan.created_on', },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-non_returnable_material_challan",
                    type: 'GET',
                    data: "id=" + data["nrmc_id"],
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