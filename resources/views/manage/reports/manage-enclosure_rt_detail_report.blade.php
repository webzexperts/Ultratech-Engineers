@extends('layouts.master')
@section('title') Enclosure / RP Detail (Report) @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Reports @endslot
@slot('title') Enclosure / RP Detail (Report) @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Enclosure / RP Detail (Report)</h5>
                <div>
                    @if(hasAccess("enclosure_rp_detail","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dyntable" class="table nowrap align-middle table-bordered cul-search" style="width:100%">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>En. Name</th>
                                <th>En. No.</th>
                                <th>Layout</th>
                                <th>Perm. To Use</th>
                                <th>Validity</th>
                                <th>RSO Name</th>
                                <th>RSO Validity</th>
                                <th>RSO PMS No.</th>
                                <th>RSO Cert.</th>
                                <th>Radiographer</th>
                                <th>Radiographer PMS No.</th>
                                <th>Radiographer Certificate</th>
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
        jQuery('#export-excel').on('click',function(){
            jQuery('.enclosure_rp_detail').click();
        });

        setTimeout(() => {
            loadDataTable();
        }, 1000);

        function loadDataTable(){
            if (jQuery.fn.DataTable.isDataTable('#dyntable')) {
                jQuery('#dyntable').DataTable().destroy();
            }

             table = $('#dyntable').DataTable({
                "processing": false,
                "serverSide": true,
                "scrollX": true,
                "order": [0, 'asc'],
                dom: 'Blfrtip',
                "oLanguage": {
                    "sSearch": "Search :"
                },
                buttons: [{
                    extend:'excel',
                    filename: 'Enclosure / RP Detail (Report) List',
                    title:"",
                    className: 'enclosure_rp_detail d-none',
                    exportOptions: { modifier: { page: 'all' } },
                    action: newexportaction
                }],
                ajax: {
                    url: "listing-enclosure_rp_detail",
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
                    { data: 'location_name', name: 'location_name', },
                    { data: 'enclosure_name', name: 'enclosure_name', },
                    { data: 'enclosure_no', name: 'enclosure_no', },
                    { data: 'enclosure_layout', name: 'enclosure_layout', class: 'remove_filters_short_qty', orderable: false, searchable: false },
                    { data: 'enclosure_permission_to_use', name: 'enclosure_permission_to_use', class: 'remove_filters_short_qty', orderable: false, searchable: false },
                    { data: 'enclosure_validity', name: 'enclosure_validity', },
                    { data: 'rso_name', name: 'rso_name', },
                    { data: 'rso_validity', name: 'rso_validity', },
                    { data: 'rso_pms_no', name: 'rso_pms_no', },
                    { data: 'rso_certificate', name: 'rso_certificate', class: 'remove_filters_short_qty', orderable: false, searchable: false },
                    { data: 'rad_name', name: 'rad_name', },
                    { data: 'rad_pms_no', name: 'rad_pms_no', },
                    { data: 'rad_certificate', name: 'rad_certificate', class: 'remove_filters_short_qty', orderable: false, searchable: false },
                ],
            });
        }
    </script> 
@endsection
