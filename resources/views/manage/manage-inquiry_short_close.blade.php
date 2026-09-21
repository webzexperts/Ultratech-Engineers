@extends('layouts.master')
@section('title') Inquiry Short Close @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Marketing @endslot
@slot('title')Inquiry Short Close @endslot
@endcomponent

@include('modals.inquiry_short_close_modal')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Inquiry Short Close</h5>
                <div>
                    @if(hasAccess("inquiry_short_close","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("inquiry_short_close","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#InquiryShortCloseModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>S/C Date</th>
                            <th>Regrate Reason</th>
                            <th>S/C Sp. Note</th>
                            <th>Inq. No.</th>
                            <th>Inq. Date</th>
                            {{-- <th>Customer Code</th> --}}
                            <th>Customer</th>
                            <th>Ref. No.</th>
                            <th>Type of Test</th>
                            <th>Process At</th>
                            <th>Type of Job</th>
                            <th>Job Description</th>
                            <th>Part No.</th>
                            <th>Qty.</th>
                            <th>Remark</th>
                            <th>Sp. Note</th>
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
            jQuery('.export_sc').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'asc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Inquiry Short Close List',
                title:"",
                className: 'export_sc d-none',
                exportOptions: { columns: ':not(:eq(0))', modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-inquiry_short_close",
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
                { data: 'inq_sc_date', name: 'inquiry_short_close.inq_sc_date', },
                { data: 'sc_reason', name: 'reason.reason_name', },
                { data: 'inq_sc_special_note', name: 'inquiry_short_close.inq_sc_special_note', },
                { data: 'inq_number', name: 'inquiry.inq_number', },
                { data: 'inq_date', name: 'inquiry.inq_date', },
                // { data: 'customer_code', name: 'customers.customer_code', },
                { data: 'customer', name: 'customers.customer', },
                { data: 'inq_ref_no_date', name: 'inquiry.inq_ref_no_date', },
                { data: 'inqd_test_method', name: 'inquiry_details.inqd_test_method', },
                { data: 'inqd_process_at', name: 'inquiry_details.inqd_process_at', },
                { data: 'type_of_job', name: 'type_of_job.type_of_job', },
                { data: 'job_description', name: 'job_descriptions.job_description', },
                // { data: 'part', name: 'part.part', },
                { data: 'inqd_part_no', name: 'inquiry_details.inqd_part_no', },
                { data: 'inq_sc_qty' , name: 'inquiry_short_close.inq_sc_qty' },
                { data: 'inqd_remark' , name: 'inquiry_details.inqd_remark' },
                { data: 'inq_sc_special_note' , name: 'inquiry_short_close.inq_sc_special_note' },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'inquiry_short_close.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'inquiry_short_close.created_on', },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-inquiry_short_close",
                    type: 'GET',
                    data: "id=" + data["inq_sc_id"],
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
                             table.draw(false);
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