@extends('layouts.master')
@section('title') AERB Documents Summary @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Reports @endslot
@slot('title') AERB Documents Summary @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">AERB Documents Summary</h5>
                <div>
                    @if(hasAccess("aerb_documents_summary","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dyntable" class="table nowrap align-middle table-bordered cul-search" style="width:100%">
                        <thead>
                            <tr>
                                <th>AERB Document</th>
                                <th>Upload</th>
                                <th>Remark</th>
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
            jQuery('.export_aerb_documents_summary').click();
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
                "order": [[0, 'asc']],
                dom: 'Blfrtip',
                "oLanguage": {
                    "sSearch": "Search :"
                },
                buttons: [{
                    extend:'excel',
                    filename: 'AERB Documents Summary List',
                    title:"",
                    className: 'export_aerb_documents_summary d-none',
                    exportOptions: { modifier: { page: 'all' } },
                    action: newexportaction
                }],
                ajax: {
                    url: "listing-aerb_documents_summary",
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
                    { data: 'aerb_documents_name', name: 'aerb_documents.aerb_documents_name', },
                    { data: 'aerb_documents_upload', name: 'aerb_documents.aerb_documents_upload', orderable: false, searchable: false, },
                    { data: 'remark', name: 'aerb_documents.remark', },
                ],
            });
        }
    </script> 
@endsection
