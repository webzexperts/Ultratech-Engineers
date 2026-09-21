@extends('layouts.master')
@section('title') Camera History @endsection
@section('css')
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Reports @endslot
@slot('title') Camera History @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header  mb-2 d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Camera History</h5>
                <div>
                    @if(hasAccess("camera_history_report","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <form id="cameraHistoryForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <div class="row mt-3">
                        <div class="col-lg-2">
                            <label for="camera_id" class="form-label">Camera Name <sup class="astric">*</sup></label>
                        </div>
                        <div class="col-lg-3 d-flex position-relative">
                            <select class="zindexnotapply js-example-basic-single" name="camera_id" id="camera_id" required>
                                <option value="">Select Camera</option>
                                @foreach($cameras as $camera)
                                    <option value="{{ $camera->rt_camera_id }}">{{ $camera->name_for_display }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>

                <div class="table-responsive mt-4 details-action">
                    <table id="dyntable" class="table nowrap align-middle table-bordered cul-search" style="width:100%">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Used In</th>
                                <th>Ref. No.</th>
                                <th>Current Location</th>
                                <th>Customer</th>
                                <th>Supplier</th>
                                <th>Application No.</th>
                                <th>Approval Letter</th>
                                <th>Validity</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script-manage')
<script>
    var table;
    var headerOpt = {'Authorization':'Bearer {{ Auth::user()->auth_token }}','X-CSRF-TOKEN':'{{ csrf_token() }}'};
    
    jQuery('#export-excel').on('click', function(){
        jQuery('.export_camera_history').click();
    });

    $(document).ready(function() {
        // Initialize select2
        if (jQuery().select2) {
            $('.js-example-basic-single').select2();
        }

        // On change camera
        $('#camera_id').on('change', function() {
            var camera_id = $(this).val();
            loadDataTable(camera_id);
        });

        // Load empty table on load
        loadDataTable('');
    });

    function loadDataTable(camera_id) {
        if (jQuery.fn.DataTable.isDataTable('#dyntable')) {
            jQuery('#dyntable').DataTable().destroy();
        }

        table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[0, 'asc']],
            dom: 'Blfrtip',
            "oLanguage": {
                "sSearch": "Search :"
            },
            buttons: [{
                extend: 'excel',
                filename: 'Camera History List',
                title: "",
                className: 'export_camera_history d-none',
                exportOptions: { modifier: { page: 'all' } },
                action: newexportaction
            }],
            ajax: {
                url: "listing-camera_history_report",
                type: "POST",
                headers: headerOpt,
                data: {
                    camera_id: camera_id
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#dyntable_processing').hide();
                    if (jqXHR.status == 401) {
                        console.log(jqXHR.statusText);
                    } else {
                        console.log('Something went wrong!');
                    }
                }
            },
            columns: [
                { 
                    data: 'transaction_date', 
                    name: 'transaction_date',
                    render: function(d) {
                        if (!d) return '';
                        var parts = d.split(' ')[0].split('-');
                        if (parts.length === 3) {
                            return parts[2] + '/' + parts[1] + '/' + parts[0];
                        }
                        return d;
                    }
                },
                { data: 'module_name', name: 'module_name' },
                { data: 'doc_no', name: 'doc_no', render: function(d){ return d ? d : ''; } },
                { data: 'from_location', name: 'from_location', render: function(d){ return d ? d : '-'; } },
                { data: 'customer_name', name: 'customer_name', render: function(d){ return d ? d : ''; } },
                { data: 'supplier_name', name: 'supplier_name', render: function(d){ return d ? d : ''; } },
                { data: 'application_no', name: 'application_no', render: function(d){ return d ? d : ''; } },
                { 
                    data: 'approval_letter', 
                    name: 'approval_letter', 
                    render: function(d){ 
                        if (d) {
                            var url = "{{ asset('storage') }}/" + d;
                            return '<a href="' + url + '" target="_blank"><i class="ri-eye-fill action-icon remove_filters_short_qty" style="font-size: 16px;"></i></a>';
                        }
                        return '';
                    } 
                },
                { 
                    data: 'validity', 
                    name: 'validity',
                    render: function(d) {
                        if (!d) return '';
                        var parts = d.split(' ')[0].split('-');
                        if (parts.length === 3) {
                            return parts[2] + '/' + parts[1] + '/' + parts[0];
                        }
                        return d;
                    }
                }
            ],
        });
    }
</script>
@endsection
