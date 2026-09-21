@extends('layouts.master')
@section('title')RT Label Print @endsection
@section('css')
<style>
    #rtReportsTable_wrapper .dataTables_filter,
    #rtLabelPrintDetailsTable_wrapper .dataTables_filter {
        display: none !important;
    }
</style>
@endsection
@section('content')
@component('components.breadcrumb')
    @slot('li_1') Transaction @endslot
    @slot('title')RT Label Print @endslot
@endcomponent

@include('modals.modals-transaction.rt_reports_list_modal')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">RT Label Print</h5>
            </div>
            <div class="card-body">
                <!-- Top Row: Test Report (RT) field, Pending button, and actions -->
                <div class="row align-items-center g-3 mb-4">
                    <div class="col-md-8 col-sm-12">
                        <div class="row g-2 align-items-center">
                            <div class="col-auto">
                                <label class="form-label mb-0">Report No.</label>
                            </div>
                            <div class="col-auto flex-grow-1" style="max-width: 350px;">
                                <input type="hidden" id="selected_report_id" name="selected_report_id">
                                <input type="text" class="form-control" id="selected_report_no" name="selected_report_no"  readonly required>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-success text-nowrap" id="select_report_btn">Test Report (RT)</button>
                                 @if(hasAccess("rt_label_print","print"))
                                <button type="button" class="btn btn-primary text-nowrap ms-1" id="print_label_btn" disabled> Print Label
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Details Grid Table -->
                <div class="table-responsive">
                    <table class="table nowrap align-middle table-bordered pending_table remove-reset-filter cul-search" id="rtLabelPrintDetailsTable" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 50px;" class="text-center radio_column">
                                    <input type="checkbox" id="select_all_checkbox" class="form-check-input" />
                                </th>
                                <th>Result</th>
                                <th>Report No.</th>
                                <th>Rev. No.</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Party</th>
                                <th>Sr. No.</th>
                                <th>Identification</th>
                                <th>Location</th>
                                <th>Film Type</th>
                                <th>Film Size</th>
                                <th>No. of Films</th>
                                <th>Thickness(mm)</th>
                                <th>SFD</th>
                                <th>Density</th>
                                <th>IQI</th>
                                <th>Sensitivity</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script-manage')
    <script>
        var headerOpt = {
            'Authorization': 'Bearer {{ Auth::user()->auth_token }}',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        };
    </script>
    <script src="{{ URL::asset('views/js/rt_label_print.js?ver='.getJsVersion()) }}"></script>
@endsection
