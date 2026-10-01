@extends('layouts.master')
@section('title') Invoice @endsection
@section('css')
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Transaction @endslot
@slot('title') Invoice @endslot
@endcomponent

@include('modals.modals-transaction.invoice_modal')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Invoice</h5>
                <div>
                    @if(hasAccess("invoice", "export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif

                    @if(hasAccess("invoice", "add"))
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#InvoiceModal" id="add_invoice_btn">Add</button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Bill No.</th>
                            <th>Bill Date</th>
                            <th>Customer Code</th>
                            <th>Customer Name</th>
                            <th>Customer Client</th>
                            <th>Job Type</th>
                            <th>POS State</th>
                            <th>Value Of Goods</th>
                            <th>Tax Type</th>
                            <th>Net Amount</th>
                            <th>Due Date</th>
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
    var checkFileRoute = "{{ route('check-file_exists') }}";
    var headerOpt = {
        'Authorization': 'Bearer {{ Auth::user()->auth_token }}',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    };
    jQuery('#export-excel').on('click', function () {
        jQuery('.export_invoice').click();
    });

    var table = $('#dyntable').DataTable({
        "processing": false,
        "serverSide": true,
        "scrollX": true,
        "order": [[2, 'desc'], [14, 'desc']],
        dom: 'Blfrtip',
        buttons: [{
            extend: 'excel',
            filename: 'Invoice List',
            title: "",
            className: 'export_invoice d-none',
            exportOptions: {
                columns: function (idx, data, node) {
                    return idx !== 0 && table.column(idx).visible();
                },
                modifier: { page: 'all' }
            },
            action: newexportaction
        }],
        ajax: {
            url: "{{ route('listing-invoice') }}",
            type: "POST",
            headers: headerOpt,
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery('#dyntable_processing').hide();
                if (jqXHR.status == 401) {
                    console.log(jqXHR.statusText);
                }
            }
        },
        "columns": [
            { data: 'options', name: 'options', orderable: false, searchable: false },
            { data: 'invoice_no', name: 'invoices.invoice_no' },
            { data: 'invoice_date', name: 'invoices.invoice_date' },
            { data: 'customer_code', name: 'customers.customer_code' },
            { data: 'customer_name', name: 'customers.customer' },
            { data: 'customer_client', name: 'invoices.customer_client' },
            { data: 'job_type_fix', name: 'invoices.job_type_fix' },
            { data: 'pos_state_name', name: 'states.state_name' },
            { data: 'value_of_goods', name: 'invoices.value_of_goods' },
            { data: 'tax_type', name: 'invoices.tax_type' },
            { data: 'net_amount', name: 'invoices.net_amount' },
            { data: 'due_date', name: 'invoices.due_date' },
            { data: 'last_by_name', name: 'last_user.person_name' },
            { data: 'last_on', name: 'invoices.last_on' },
            { data: 'created_by_name', name: 'created_user.person_name' },
            { data: 'created_on', name: 'invoices.created_on' },
            { data: 'invoice_id', name: 'invoices.invoice_id', visible: false, searchable: false }
        ]
    });
</script>
@endsection
