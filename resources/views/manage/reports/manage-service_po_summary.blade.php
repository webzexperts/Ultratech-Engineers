@extends('layouts.master')
@section('title') Service PO Summary @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Reports @endslot
@slot('title')Service PO Summary @endslot
@endcomponent

            
<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Service PO Summary</h5>
                <div>
                    @if(hasAccess("service_po_summary","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <form id="grnSearchForm" name="grnSearchForm" class="stdform">

                    <div class="row g-3 mb-3">
                        <div class="col-md-1">
                            <label for="from_date" class="form-label fw-semibold">From Date</label>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group position-relative">
                                <input type="text" name="from_date" id="from_date"
                                    class="form-control report-date-picker from-april"
                                    placeholder="Select From Date">
                                     <div class="invalid-tooltip">
                                        From Date must be less than To Date
                                    </div>
                            </div>
                        </div>

                        <div class="col-md-1">
                            <label for="to_date" class="form-label fw-semibold">To Date</label>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group position-relative">
                                <input type="text" name="to_date" id="to_date"
                                    class="form-control report-date-picker"
                                    placeholder="Select To Date">
                                    <div class="invalid-tooltip">
                                        To Date must be greater than From Date
                                    </div>
                            </div>
                        </div>

                        <!-- <div class="col-md-4">
                                <label class="control-label" for=""></label>
                                <div class="input-group position-relative mt-2">
                                    <button id="reset-order-data" type="submit" class="btn btn-primary">Reset</button>
                                </div>
                        </div> -->
                    </div>
                </form>
                <div class="table-responsive">
                <table id="dyntable" class="table nowrap align-middle table-bordered fixed-first-column" style="width:100%">
                    <thead>
                        <tr>
                            <th>PO No.</th>
                            <th>PO Date</th>
                            <th>Supplier</th>
                            <th>Purpose</th>
                            <th>Ref. No. & Date</th>
                            <th>Bill To</th>
                            <th>For Location</th>
                            <th>Item</th>
                            <th>Item Group</th>
                            <th>Main Group</th>
                            <th>Sr. No.</th>
                            <th>PO Qty.</th>
                            <th>Unit</th>
                            <th>Rate/Unit</th>
                            <th>Amount</th>
                            <th>Del. Date</th>
                            <th>Remark</th>
                            <th>Prepared By</th>
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
        var table;
        var headerOpt = {'Authorization':'Bearer {{ Auth::user()->auth_token }}','X-CSRF-TOKEN':'{{ csrf_token() }}'};
        jQuery('#export-excel').on('click',function(){
            jQuery('.export_service_po_summary').click();
        });

        setTimeout(() => {
            DataYearWise();
            loadDataTable();
        }, 1000);

        function loadDataTable(){
            if (jQuery.fn.DataTable.isDataTable('#dyntable')) {
                jQuery('#dyntable').DataTable().destroy();
            }

            var data = new FormData(document.getElementById('grnSearchForm'));
            var formValue = Object.fromEntries(data.entries());

             table = $('#dyntable').DataTable({
                "processing": false,
                "serverSide": true,
                "scrollX": true,
                "order": [[0, 'asc']],
                // dom: 'Blrtip',
                dom: 'Blfrtip',
                buttons: [{
                    extend:'excel',
                    filename: 'Service PO Summary List',
                    title:"",
                    className: 'export_service_po_summary d-none',
                    exportOptions: { modifier: { page: 'all' } },
                    action: newexportaction
                }],
                ajax: {
                    url: "listing-service_po_summary",
                    type: "POST",
                    headers: headerOpt,
                    data : {
                            'from_date':formValue.from_date,
                            'to_date':formValue.to_date,                             
                            },
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
                    { data: 'ser_po_number', name: 'service_po.ser_po_number', },
                    { data: 'ser_po_date', name: 'service_po.ser_po_date', },
                    { data: 'supplier_name', name: 'suppliers.supplier_name', },
                    { data: 'purpose', name: 'service_po.purpose', },
                    { data: 'ref_no_date', name: 'service_po.ref_no_date', },
                    { data: 'bill_to', name: 'bill_to.location_name', },
                    { data: 'for_location', name: 'for_location.location_name', },
                    { data: 'item_name', name: 'item.item_name', },
                    { data: 'item_group', name: 'item_group.item_group', },
                    { data: 'main_group', name: 'item.item_type', },
                    { data: 'name_for_display', name: 'sr.name_for_display' },
                    { data: 'po_qty', name: 'service_po_details.po_qty', },
                    { data: 'unit', name: 'unit.unit', },
                    { data: 'rate_unit', name: 'service_po_details.rate_unit', },
                    { data: 'amount', name: 'service_po_details.amount', },
                    { data: 'del_date', name: 'service_po_details.del_date', },
                    { data: 'remark', name: 'service_po_details.remark', },
                    { data: 'prepared_by', name: 'prepared_by.person_name', },
                ],
            });
        }

        function parseDate(dateStr) {
            if (!dateStr) return null;

            var parts = dateStr.split('/');
            return new Date(parts[2], parts[1] - 1, parts[0]);
        }

        jQuery(document).on('change', '#from_date, #to_date', function () {

            var $from = jQuery('#from_date');
            var $to   = jQuery('#to_date');

            var fromDate = parseDate($from.val());
            var toDate   = parseDate($to.val());

            // Reset states
            $from.removeClass('is-invalid');
            $to.removeClass('is-invalid');

            if (fromDate && toDate) {

                if (toDate < fromDate) {
                    $to.addClass('is-invalid');
                    return false;
                }

                if(fromDate > toDate){
                    $from.addClass('is-invalid');
                    return false;
                }
                loadDataTable();
            }
        });

        jQuery('#reset-order-data').on('click',function(){
            var searchForm = jQuery("#grnSearchForm");
            searchForm.find('#to_date').val('');
            searchForm.find('#from_date').val('');
            DataYearWise();
        });
        
    </script> 
@endsection