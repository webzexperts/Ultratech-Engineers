@extends('layouts.master')
@section('title') Camera Movement Detail Report @endsection
@section('content')
@component('components.breadcrumb')
    @slot('li_1') Reports @endslot
    @slot('title') Camera Movement Detail Report @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Camera Movement Detail Report</h5>
                <div>
                    @if(hasAccess("camera_movement_detail","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dyntable" class="table nowrap align-middle table-bordered cul-search" style="width:100%">
                        <thead>
                            <tr>
                                <th>AERB No.</th>
                                <th>Camera Sr. No.</th>
                                <th>Camera Name</th>
                                <th>Isotope</th>
                                <th>Location</th>
                                <th>Application No.</th>
                                <th class="action_col">Approval Letter</th>
                                <th>Validity</th>
                                <th>Present Ci</th>
                                <th class="action_col">Decay Chart</th>
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

    jQuery('#export-excel').on('click', function() {
        jQuery('.export_camera_movement_detail').click();
    });

    setTimeout(() => {
        loadDataTable();
    }, 1000);

    function loadDataTable() {
        if (jQuery.fn.DataTable.isDataTable('#dyntable')) {
            jQuery('#dyntable').DataTable().destroy();
        }

        var columns = [
            { data: 'aerb_no', name: 'aerb_no' },
            { data: 'camera_sr_no', name: 'camera_sr_no' },
            { data: 'camera_name', name: 'camera_name' },
            { data: 'isotope', name: 'isotope' },
            { data: 'location', name: 'location' },
            { data: 'application_no', name: 'application_no' },
            { data: 'approval_letter', name: 'approval_letter', orderable: false, searchable: false, },
            { data: 'validity', name: 'validity' },
            { data: 'present_ci', name: 'initial_curie', orderable: true, searchable: true },
            { data: 'decay_chart', name: 'decay_chart', orderable: false, searchable: false, }
        ];

        table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": false,
            "scrollX": true,
            "order": [[1, 'asc']],
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                filename: 'Camera Movement Detail List',
                title: "",
                className: 'export_camera_movement_detail d-none',
                exportOptions: { 
                    modifier: { page: 'all' },
                    columns: [0, 1, 2, 3, 4, 5, 7, 8]
                },
                action: newexportaction
            }],
            ajax: {
                url: "{{ route('listing-camera_movement_detail') }}",
                type: "POST",
                headers: headerOpt,
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#dyntable_processing').hide();
                    if (jqXHR.status == 401) {
                        console.log(jqXHR.statusText);
                    } else {
                        console.log('Something went wrong!');
                    }
                    console.log(JSON.parse(jqXHR.responseText));
                }
            },
            columns: columns,
        });
    }
</script>
@endsection
