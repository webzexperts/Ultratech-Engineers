@extends('layouts.master')
@section('title') Estimation & Costing @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Marketing @endslot
@slot('title')Estimation & Costing @endslot
@endcomponent

@include('modals.modals-pending.pending_for_estimation')
@include('modals.estimation_costing_modal')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Estimation & Costing</h5>
                <div>
                    @if(hasAccess("estimation_costing","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("estimation_costing","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#EstimationCostingModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Estimation No.</th>
                            <th>Estimation Date</th>
                            <th>Inq. No.</th>
                            <th>Inq. Date</th>
                            {{-- <th>Customer Code</th> --}}
                            <th>Customer</th>
                            <th>Ref. No.</th>
                            <th>Type of Test</th>
                            <th>Type of Job</th>
                            <th>Job Description</th>
                            <th>Part No.</th>
                            <th>Estimation</th>
                            <th>Costing</th>
                            <th>Prepared By</th>
                            <th>Reviewed By</th>
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
            jQuery('.export_inquiry').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[2, 'desc'],[1 ,'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Estimation & Costing List',
                title:"",
                className: 'export_inquiry d-none',
                exportOptions: { columns: ':not(:eq(0))', modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-estimation_costing",
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
                { data: 'ec_number', name: 'estimation_costing.ec_number', },
                { data: 'ec_date', name: 'estimation_costing.ec_date', },
                { data: 'inq_number', name: 'inquiry.inq_number', },
                { data: 'inq_date', name: 'inquiry.inq_date', },
                // { data: 'customer_code', name: 'customers.customer_code', },
                { data: 'customer', name: 'customers.customer', },
                { data: 'inq_ref_no_date', name: 'inquiry.inq_ref_no_date', },
                { data: 'inqd_test_method', name: 'inquiry_details.inqd_test_method', },
                { data: 'type_of_job', name: 'type_of_job.type_of_job', },
                { data: 'job_description', name: 'job_descriptions.job_description', },
                { data: 'part', name: 'part.part', },
                { data: 'ec_estimation', name: 'estimation_costing.ec_estimation', },
                { data: 'ec_costing', name: 'estimation_costing.ec_costing', },
                // { data: 'ec_prepared_by_name', name: 'ec_prepared_by_name', },
                // { data: 'ec_reviewed_by_name', name: 'ec_reviewed_by_name', },
                { data: 'ec_prepared_by_name', name: 'prepared_by.person_name' },
                { data: 'ec_reviewed_by_name', name: 'reviewed_by.person_name' },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'estimation_costing.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'estimation_costing.created_on', },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-estimation_costing",
                    type: 'GET',
                    data: "id=" + data["ec_id"],
                    headers: headerOpt,
                    dataType: 'json',
                    processData: false,
                    success: function (data) {
                        if (data.response_code == 1) {
                            Swal.fire({
                                // title: 'Deleted!',
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