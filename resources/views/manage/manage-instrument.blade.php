@extends('layouts.master')
@section('title') Instrument @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Store @endslot
@slot('title')Instrument @endslot
@endcomponent

@include('modals.instrument_modal')
@include('modals.modals-pending.pending_for_instrument')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1"> Instrument</h5>
                <div>
                    @if(hasAccess("instrument","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("instrument","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#InstrumentModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Type</th>
                            <th>Instrument No.</th>
                            <th>Instrument Name</th>
                            <th>Make</th>
                            <th>Sr. No.</th>
                            <th>AERB Ref. No.</th>
                            <th>Calibration Req. </th>
                            <th>Frequency (Days)</th>
                            <th>Last Cal. Date</th>
                            <th>Next Cal. Due</th>
                            <th>Current Location</th>
                            <th>Status</th>
                            <th>Own Location</th>
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
            jQuery('.export_instrument').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'desc'],[2, 'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Instrument List',
                title:"",
                className: 'export_instrument d-none',
                exportOptions: { columns: ':not(:eq(0))', modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-instrument",
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
                { data: 'table_unique_id', name: 'instrument.table_unique_id', },
                { data: 'ins_instrument_no', name: 'instrument.ins_instrument_no', },
                { data: 'ins_instrument_name', name: 'instrument.ins_instrument_name', },
                { data: 'ins_make', name: 'instrument.ins_make', },
                { data: 'ins_mfg_sr_no', name: 'instrument.ins_mfg_sr_no', },
                { data: 'ins_doc_ref_no', name: 'instrument.ins_doc_ref_no', },
                { data: 'ins_cali_req', name: 'instrument.ins_cali_req', },
                { data: 'ins_cali_freq', name: 'instrument.ins_cali_freq', },
                { data: 'ins_last_cali_date', name: 'instrument.ins_last_cali_date', },
                { data: 'ins_next_cali_due_date', name: 'instrument.ins_next_cali_due_date', },
                { data: 'current_location', name: 'current_location.location_name', },
                { data: 'ins_status', name: 'instrument.ins_status', },
                { data: 'own_location', name: 'own_location.location_name', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'instrument.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'instrument.created_on', },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            // Are you Sure, You want to Delete ?
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-instrument",
                    type: 'GET',
                    data: "id=" + data["ins_id"],
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