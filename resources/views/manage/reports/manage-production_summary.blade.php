@extends('layouts.master')
@section('title') Production Summary @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Reports @endslot
@slot('title') Production Summary @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Production Summary</h5>
                <div>
                    @if(hasAccess("production_summary","export"))
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <form id="productionSearchForm" name="productionSearchForm" class="stdform">
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
                    </div>
                </form>

                <table id="dyntable" class="table nowrap align-middle table-bordered cul-search" style="width:100%">
                    <thead>
                        <tr>
                            <!-- <th>Sr. No.</th> -->
                            <th>Date</th>
                            <th>Enclosure</th>
                            <th>Source</th>
                            <th>Production</th>
                            <th>Retake</th>
                            <th>Wastage</th>
                            <th>Total SQIN</th>
                            <th>Remark</th>
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
            jQuery('.export_production_summary').click();
        });

        setTimeout(() => {
            DataYearWise();
            loadDataTable();
        }, 1000);

        function loadDataTable() {
            if (jQuery.fn.DataTable.isDataTable('#dyntable')) {
                jQuery('#dyntable').DataTable().destroy();
            }

            var data = new FormData(document.getElementById('productionSearchForm'));
            var formValue = Object.fromEntries(data.entries());
            
            var table = $('#dyntable').DataTable({
                "processing": false,
                "serverSide": true,
                "scrollX": true,
                "order": [[0,'desc']],
                dom: 'Blfrtip',
                "oLanguage": {
                    "sSearch": "Search :"
                },
                buttons: [{
                    extend:'excel',
                    filename: 'Production Summary List',
                    title:"",
                    className: 'export_production_summary d-none',
                    exportOptions: {
                            columns: function(idx, data, node) {
                                return table.column(idx).visible();
                            },
                            modifier: {
                                page: 'all'
                            }
                    },
                    action: newexportaction
                }],
                ajax: {
                    url: "listing-production_summary",
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
                    // { data: 'production_entry_no', name: 'production_entry.production_entry_no', },
                    { data: 'production_entry_date', name: 'production_entry.production_entry_date', },
                    { data: 'enclosure_name', name: 'enclosure.enclosure_name', },
                    { data: 'source_id_fix', name: 'sub.source_id_fix', },
                    { data: 'prod_sq_in', name: 'sub.prod_sq_in', },
                    { data: 'retake_sq_in', name: 'sub.retake_sq_in',},
                    { data: 'repair_reshoot_sqin', name: 'sub.repair_reshoot_sqin', },
                    { data: 'total_sq_in', name: 'production_entry.total_sq_in',},
                    { data: 'remark', name: 'production_entry.remark', },
                    { data: 'production_entry_sequence', name: 'production_entry.production_entry_sequence', visible: false, },
                ],
                drawCallback: function (settings) {
                    var api = this.api();
                    var rows = api.rows({ page: 'current' }).data();

                    var total_prod = 0;
                    var total_retake = 0;
                    var total_wastage = 0;
                    var total_sqin = 0;

                    rows.each(function (row) {
                        total_prod += parseFloat(row.prod_sq_in) || 0;
                        total_retake += parseFloat(row.retake_sq_in) || 0;
                        total_wastage += parseFloat(row.repair_reshoot_sqin) || 0;
                        total_sqin += parseFloat(row.total_sq_in) || 0;
                    });

                    $('#dyntable tbody tr.summary-total-row').remove();

                    if (rows.length > 0) {
                        var totalRow = '<tr class="summary-total-row">' +
                            '<td></td>' +
                            '<td></td>' +
                            '<td class="text-end fw-bold">Total:</td>' +
                            '<td class="fw-bold">' + total_prod.toFixed(2) + '</td>' +
                            '<td class="fw-bold">' + total_retake.toFixed(2) + '</td>' +
                            '<td class="fw-bold">' + total_wastage.toFixed(2) + '</td>' +
                            '<td class="fw-bold">' + total_sqin.toFixed(2) + '</td>' +
                            '<td></td>' +
                        '</tr>';
                        $('#dyntable tbody').append(totalRow);
                    }
                }
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
            var searchForm = jQuery("#productionSearchForm");
            searchForm.find('#to_date').val('');
            searchForm.find('#from_date').val('');
            DataYearWise();
        });
    </script>
@endsection
