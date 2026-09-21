@extends('layouts.master')
@section('title') Material Indent Summary @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Reports @endslot
@slot('title')Material Indent Summary @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Material Indent Summary</h5>
                <div>
                    @if(hasAccess("purchase_indent_summary","export"))
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
                            <th>Indent No.</th>
                            <th>Date</th>
                            <th>To Location</th>
                            <th>Item</th>
                            <th>Item Group</th>
                            <th>Main Group</th>
                            <th>Indent Qty.</th>
                            <th>Unit</th>
                            <th>Remark</th>
                            <th>Sp. Note</th>
                            <th> Indent By </th>
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
            jQuery('.export_purchase_indent_summary').click();
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
                "order": [[0,'asc']],
                dom: 'Blfrtip',
                "oLanguage": {
                    "sSearch": "Search :"
                },
                buttons: [{
                    extend:'excel',
                    filename: 'Material Indent Summary List',
                    title:"",
                    className: 'export_purchase_indent_summary d-none',
                    exportOptions: {
                            columns: function(idx, data, node) {
                                return  table.column(idx).visible();
                            },
                            modifier: {
                                page: 'all'
                            }
                    },
                    // exportOptions: {  modifier: { page: 'all' } },
                    action: newexportaction
                }],
                ajax: {
                    url: "listing-purchase_indent_summary",
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
                    { data: 'pi_no', name: 'purchase_indent.pi_no', },
                    { data: 'pi_date', name: 'purchase_indent.pi_date', },
                    { data: 'to_location', name: 'to_location.location_name', },
                    { data: 'item_name', name: 'item.item_name', },
                    { data: 'item_group', name: 'item_group.item_group', },
                    { data: 'main_group', name: 'item.item_type', },
                    { data: 'indent_qty', name: 'purchase_indent_details.indent_qty', },
                    { data: 'unit', name: 'unit.unit', },
                    { data: 'remark', name: 'purchase_indent_details.remark', },
                    { data: 'special_note', name: 'purchase_indent.special_note', },
                    { data: 'indent_by', name: 'indent_by.person_name', },
                    { data: 'pi_sequence', name: 'purchase_indent.pi_sequence', visible: false, },
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