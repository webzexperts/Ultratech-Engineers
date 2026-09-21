@extends('layouts.master')
@section('title') Authority Person @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Master @endslot
@slot('title')Authority Person @endslot
@endcomponent

@include('modals.authority_person_modal')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Authority Person</h5>
                <div>
                    @if(hasAccess("authority_person","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("authority_person","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#AuthorityPersonModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%" data-exclude-search="3,9">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Operator</th>
                            <th>Designation</th>
                            <th>Signature</th>
                            <th>Operator Type</th>
                            <th>RSO / Radiographer</th>
                            <th>Location</th>
                            <th>Validity</th>
                            <th>PMS No.</th>
                            <th>Certificate</th>
                            <th>Status</th>
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
            jQuery('.export_authority_person').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'asc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Authority Person List',
                title:"",
                className: 'export_authority_person d-none',
                exportOptions: {
                    columns: function(idx, data, node) {
                        return (idx !== 0 && idx !== 3 && idx !== 9) && table.column(idx).visible();
                    },
                    modifier: { page: 'all' }
                },
                action: newexportaction
            }],
            ajax: {
                url: "listing-authority_person",
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
                { data: 'operator', name: 'operator', },
                { data: 'designation', name: 'designation', },
                { data: 'signature', name: 'signature',  class:'remove_filters_short_qty'},
                { data: 'operator_type', name: 'operator_type', },
                { data: 'authority_person_type_value_fix', name: 'authority_person.authority_person_type_value_fix', },
                { data: 'location_name', name: 'location.location_name', },
                { data: 'validity', name: 'validity', },
                { data: 'pms_no', name: 'pms_no', },
                { data: 'certificate', name: 'certificate',class:'remove_filters_short_qty' },
                { data: 'status', name: 'authority_person.status', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'authority_person.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'authority_person.created_on', },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-authority_person",
                    type: 'GET',
                    data: "id=" + data["id"],
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
