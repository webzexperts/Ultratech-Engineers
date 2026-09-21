@extends('layouts.master')
@section('title') Estimation & Costing Summary @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Reports @endslot
@slot('title')Estimation & Costing Summary @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Estimation & Costing Summary</h5>
                <div>
                    @if(hasAccess("estimation_costing_summary","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <form id="poSearchForm" name="poSearchForm" class="stdform">
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

                <table id="dyntable" class="table nowrap align-middle table-bordered cul-search" style="width:100%">
                    <thead>
                        <tr>
                            <th>Est. No.</th>
                            <th>Est. Date</th>
                            <th>Inq. No.</th>
                            <th>Inq. Date</th>
                            <th>Customer</th>
                            <th>Ref. No. & Date</th>
                            <th>Type of Test</th>
                            <th>Type of Job</th>
                            <th>Job Desc.</th>
                            <th>Part No.</th>
                            <th>Process At</th>
                            <th>Qty.</th>
                            <th>Unit</th>
                            <th>Estimation</th>
                            <th>Costing</th>
                            <th>Prepared By</th>
                            <th>Reviewed By</th>
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
        var headerOpt = {'Authorization':'Bearer {{ Auth::user()->auth_token }}','X-CSRF-TOKEN':'{{ csrf_token() }}'};
        jQuery('#export-excel').on('click',function(){
            jQuery('.export_estimation_costing_summary').click();
        });

        setTimeout(() => {
            DataYearWise();
            loadDataTable();
        }, 1000);

        function loadDataTable() {
            if (jQuery.fn.DataTable.isDataTable('#dyntable')) {
                jQuery('#dyntable').DataTable().destroy();
            }

            var data = new FormData(document.getElementById('poSearchForm'));
            var formValue = Object.fromEntries(data.entries());
            
            var table = $('#dyntable').DataTable({
                "processing": false,
                "serverSide": true,
                "scrollX": true,
                "order": [[1,'asc'], [17,'asc']],
                // "order": [[1,'desc'], [17,'desc']],
                dom: 'Blfrtip',
                "oLanguage": {
                    "sSearch": "Search :"
                },
                buttons: [{
                    extend:'excel',
                    filename: 'Estimation & Costing Summary List',
                    title:"",
                    className: 'export_estimation_costing_summary d-none',
                    exportOptions: {
                        columns: ':not(:eq(17))',
                        modifier: { page: 'all' }
                    },
                    // exportOptions: {  modifier: { page: 'all' } },
                    action: newexportaction
                }],
                ajax: {
                    url: "listing-estimation_costing_summary",
                    type: "POST",
                    headers: headerOpt,
                    data : {
                        'from_date': formValue.from_date,
                        'to_date': formValue.to_date,
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
                    { data: 'ec_number', name: 'estimation_costing.ec_number', },
                    { data: 'ec_date', name: 'estimation_costing.ec_date', },
                    { data: 'inq_number', name: 'inquiry.inq_number', },
                    { data: 'inq_date', name: 'inquiry.inq_date', },
                    { data: 'customer', name: 'customers.customer', },
                    { data: 'inq_ref_no_date', name: 'inquiry.inq_ref_no_date', },
                    { data: 'inqd_test_method', name: 'inquiry_details.inqd_test_method', },
                    { data: 'type_of_job', name: 'type_of_job.type_of_job', },
                    { data: 'job_description', name: 'job_descriptions.job_description', },
                    { data: 'part', name: 'part.part', },
                    { data: 'inqd_process_at', name: 'inquiry_details.inqd_process_at', },
                    { data: 'inqd_quantity', name: 'inquiry_details.inqd_quantity', },
                    { data: 'unit', name: 'unit.unit', },
                    { data: 'ec_estimation', name: 'estimation_costing.ec_estimation', },
                    { data: 'ec_costing', name: 'estimation_costing.ec_costing', },
                    { data: 'ec_prepared_by_name', name: 'prepared_by.person_name' },
                    { data: 'ec_reviewed_by_name', name: 'reviewed_by.person_name' },
                    { data: 'ec_sequence', name: 'estimation_costing.ec_sequence', visible: false, searchable: false, },
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
            $from.removeClass('is-invalid');
            $to.removeClass('is-invalid');
            if (fromDate && toDate) {
                if (toDate < fromDate) {
                    $to.addClass('is-invalid');
                    return false;
                }

                if(fromDate > toDate) {
                    $from.addClass('is-invalid');
                    return false;
                }
                loadDataTable();
            }
        });

        jQuery('#reset-order-data').on('click',function(){
            var searchForm = jQuery("#psfdSearchForm");
            searchForm.find('#to_date').val('');
            searchForm.find('#from_date').val('');
            DataYearWise();
        });
    </script>
@endsection