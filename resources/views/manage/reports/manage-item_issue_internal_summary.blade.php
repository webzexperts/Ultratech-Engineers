@extends('layouts.master')
@section('title') Item Issue (Internal) Summary @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Reports @endslot
@slot('title')Item Issue (Internal) Summary @endslot
@endcomponent

            
<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Item Issue (Internal) Summary</h5>
                <div>
                    @if(hasAccess("item_issue_internal_summary","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <form id="iltSearchForm" name="iltSearchForm" class="stdform">

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
                            <th>Issue No.</th>
                            <th>Issue Date</th>
                            <th>Item</th>
                            <th>Item Group</th>
                            <th>Main Group</th>
                            <th>Sr. No.</th>
                            <th>Issue Qty.</th>
                            <th>Unit</th>
                            <th>Issue Type</th>
                            <th>Reason</th>
                            <th>Remark</th>
                            <th>Issue To</th>
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
            jQuery('.export_item_issue_internal_summary').click();
        });

        setTimeout(() => {
            DataYearWise();
            loadDataTable();
        }, 1000);

        function loadDataTable(){
            if (jQuery.fn.DataTable.isDataTable('#dyntable')) {
                jQuery('#dyntable').DataTable().destroy();
            }

            var data = new FormData(document.getElementById('iltSearchForm'));
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
                    filename: 'Item Issue (Internal) Summary List',
                    title:"",
                    className: 'export_item_issue_internal_summary d-none',
                    exportOptions: { modifier: { page: 'all' } },
                    action: newexportaction
                }],
                ajax: {
                    url: "listing-item_issue_internal_summary",
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
                    { data: 'issue_number', name: 'item_issue.issue_number', },
                    { data: 'issue_date', name: 'item_issue.issue_date', },
                    { data: 'item_name', name: 'item.item_name', },
                    { data: 'item_group', name: 'item_group.item_group', },
                    { data: 'main_group', name: 'item.item_type', },
                    { data: 'name_for_display', name: 'sr.name_for_display' },
                    { data: 'issue_qty', name: 'item_issue_details.issue_qty', },
                    { data: 'unit', name: 'unit.unit', },
                    { data: 'issue_type', name: 'item_issue_details.issue_type', },
                    { data: 'reason_name', name: 'reason.reason_name', },
                    { data: 'remark', name: 'item_issue_details.remark', },
                    { data: 'issue_to', name: 'item_issue.issue_to', },
                    { data: 'prepared_by' , name: 'prepared_by.person_name' },
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
            var searchForm = jQuery("#iltSearchForm");
            searchForm.find('#to_date').val('');
            searchForm.find('#from_date').val('');
            DataYearWise();
        });
        
    </script> 
@endsection