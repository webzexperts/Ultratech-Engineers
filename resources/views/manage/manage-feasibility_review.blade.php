@extends('layouts.master')
@section('title') Feasibility Review @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Marketing @endslot
@slot('title')Feasibility Review @endslot
@endcomponent

@include('modals.feasibility_review_modal')
@include('modals.modals-pending.pending_for_feasibility_review_modal')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Feasibility Review</h5>
                <div>
                    @if(hasAccess("feasibility_review","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("feasibility_review","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#FeasibilityReviewModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Review No.</th>
                            <th>Review Date</th>
                            <th>Inq. No.</th>
                            <th>Inq. Date</th>
                            {{-- <th>Customer Code</th> --}}
                            <th>Customer</th>
                            <th>Ref No.</th>
                            <th>Type of Test</th>
                            <th>Type of Job</th>
                            <th>Job Description</th>
                            <th>Part No.</th>
                            <th>Result</th>
                            {{-- <th>Sugg. Method</th> --}}
                            <th>Reason</th>
                            <th>Prepared By</th>
                            <th>Reviewed By</th>
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
            jQuery('.export_feasibility').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[2, 'desc'],[19, 'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Feasibility Review List',
                title:"",
                className: 'export_feasibility d-none',
                exportOptions: { columns: ':not(:eq(0))', modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-feasibility_review",
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
                { data: 'fr_number', name: 'feasibility_review.fr_number', },
                { data: 'fr_date', name: 'feasibility_review.fr_date', },
                { data: 'inq_number', name: 'inquiry.inq_number', },
                { data: 'inq_date', name: 'inquiry.inq_date', },
                // { data: 'customer_code', name: 'customers.customer_code', },
                { data: 'customer', name: 'customers.customer', },
                { data: 'inq_ref_no_date', name: 'inquiry.inq_ref_no_date', },
                { data: 'inqd_test_method', name: 'inquiry_details.inqd_test_method', },
                { data: 'type_of_job', name: 'type_of_job.type_of_job', },
                { data: 'job_description', name: 'job_descriptions.job_description', },
                // { data: 'part', name: 'part.part', },
                { data: 'inqd_part_no', name: 'inquiry_details.inqd_part_no', },
                { data: 'fr_result_id', name: 'feasibility_review.fr_result_id', },
                // { data: 'fr_suggest_method_id', name: 'feasibility_review.fr_suggest_method_id', },
                { data: 'reason_name', name: 'reason.reason_name', },
                // { data: 'fr_prepared_by_name', name: 'fr_prepared_by_name', },
                // { data: 'fr_reviewed_by_name', name: 'fr_reviewed_by_name', },
                { data: 'fr_prepared_by_name', name: 'prepared_by.person_name' },
                { data: 'fr_reviewed_by_name', name: 'reviewed_by.person_name' },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'feasibility_review.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'feasibility_review.created_on', },
                { data: 'fr_sequence', name: 'feasibility_review.fr_sequence', visible:false},
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-feasibility_review",
                    type: 'GET',
                    data: "id=" + data["fr_id"],
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