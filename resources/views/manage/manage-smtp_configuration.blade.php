@extends('layouts.master')
@section('title') SMTP Configuration @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Master @endslot
@slot('title') SMTP Configuration @endslot
@endcomponent

@include('modals.smtp_configuration_modal')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">SMTP Configuration</h5>
                <div>
                    @if(hasAccess("smtp_configuration","add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#SMTPConfigurationModal">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Email</th>
                            <th>Cc Email Id</th>
                            <th>Mail Host</th>
                            <th>Out Port No.</th>
                            <th>Reply Email</th>
                            <th>Purchase</th>
                            <th>Enable SSL</th>
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
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'asc']],
            dom: 'Blfrtip',
            buttons: [],
            ajax: {
                url: "listing-smtp_configuration",
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
            columns: [
                { data: 'options', name: 'options', orderable: false, searchable: false, },
                { data: 'email', name: 'smtp_configurations.email', },
                { data: 'cc_email', name: 'smtp_configurations.cc_email', },
                { data: 'mail_host', name: 'smtp_configurations.mail_host', },
                { data: 'out_port_no', name: 'smtp_configurations.out_port_no', },
                { data: 'reply_email', name: 'smtp_configurations.reply_email', },
                { data: 'purchase', name: 'smtp_configurations.purchase', },
                { data: 'enable_ssl', name: 'smtp_configurations.enable_ssl', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'smtp_configurations.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'smtp_configurations.created_on', },
            ],
        });
    </script>
@endsection
