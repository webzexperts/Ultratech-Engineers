@extends('layouts.master')
@section('title') Quotation @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Master @endslot
@slot('title')Quotation @endslot
@endcomponent

@include('modals.quotation_modal')
@include('modals.modals-pending.pending_inquiry_for_quotation')
@include('modals.modals-details.quotation_details_modal')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Quotation</h5>
                <div>
                    @if(hasAccess("quotation","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("quotation","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#QuotationModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Quot. No.</th>
                            <!-- <th>Rev. No.</th> -->
                            <th>Date</th>
                            {{-- <th>Customer Code</th> --}}
                            <th>Customer</th>
                            <th>Ref. No.</th>
                            <th>Inq. No.</th>
                            <th>Inq. Date</th>
                            <th>Type of Test</th>
                            <th>Type Of Job</th>
                            <th>Job Desc.</th>
                            <th>Part No.</th>
                            <th>Qty.</th>
                            <th>Unit</th>
                            <th>Rate/Unit</th>
                            <th>Rate Per</th>
                            <th>Kind Attn.</th>
                            <th>Remark</th>
                            <th>Prepared By</th>
                            <th>Authorized By</th>
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
            jQuery('.export_quotation').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[2,'desc'], [23,'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Quotation List',
                title:"",
                className: 'export_quotation d-none',
                exportOptions: { columns: ':not(:eq(0))', modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-quotation",
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
                { data: 'quot_number', name: 'quotation.quot_number', },
                { data: 'quot_date', name: 'quotation.quot_date', },
                // { data: 'customer_code', name: 'customers.customer_code', },
                { data: 'customer', name: 'customers.customer', },
                { data: 'quot_ref_no_date', name: 'quotation.quot_ref_no_date', },
                 { data: 'inq_number', name: 'inquiry.inq_number', },
                { data: 'inq_date', name: 'inquiry.inq_date', },
                { data: 'quotd_test_method_id', name: 'quotation_details.quotd_test_method_id', },
                { data: 'type_of_job', name: 'type_of_job.type_of_job', },
                { data: 'job_description', name: 'job_descriptions.job_description', },
                // { data: 'part', name: 'part.part', },
                { data: 'quotd_part_no', name: 'quotation_details.quotd_part_no', },

                { data: 'quotd_qty' , name: 'quotation_details.quotd_qty' },
                { data: 'quot_qty_unit_name', name: 'quot_qty_unit.unit', },
                // { data: 'quotd_rate_unit' , name: 'quotation_details.quot_rate_unit' },
                { data: 'quotd_rate_unit' , name: 'quotation_details.quotd_rate_unit' },
                { data: 'quot_rate_unit_name', name: 'quot_rate_unit.unit', },
                { data: 'kind_attn', name: 'customer_contacts.contact_person', },
                { data: 'quotd_remark', name: 'quotation_details.quotd_remark', },
                // { data: 'quot_prepared_by_name', name: 'quot_prepared_by_name', },
                { data: 'quot_prepared_by_name', name: 'prepared_by.person_name' },
                // { data: 'quot_authorised_by_name', name: 'quot_authorised_by_name', },
                { data: 'quot_authorised_by_name', name: 'authorised_by.person_name' },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'quotation.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'quotation.created_on', },
                { data: 'quot_sequence', name: 'quotation.quot_sequence', visible: false, searchable: false, },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-quotation",
                    type: 'GET',
                    data: "id=" + data["quot_id"],
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