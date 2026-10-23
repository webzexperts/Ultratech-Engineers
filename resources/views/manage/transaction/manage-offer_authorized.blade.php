@extends('layouts.master')

@section('title')
    Offer Authorized
@endsection

@section('css')
<style>
    .card-body {
        padding: 1rem 1.25rem;
    }

    .table-details-header {
        background-color: #f8fafc;
        font-weight: 600;
        font-size: 13px;
        padding: 6px 12px;
        border: 1px solid #e2e8f0;
        border-bottom: none;
        color: #1e293b;
    }

    #offer_details_table_wrapper .dataTables_filter {
        float: right;
        text-align: right;
        margin-bottom: 0.5rem;
    }

    #offer_details_table_wrapper .dataTables_length {
        float: left;
        margin-bottom: 0.5rem;
    }

    #offer_details_table_wrapper .dataTables_info {
        float: left;
        margin-top: 0.5rem;
    }

    #offer_details_table_wrapper .dataTables_paginate {
        float: right;
        margin-top: 0.5rem;
    }

    #offer_auth_table td,
    #offer_auth_table th,
    #offer_details_table td,
    #offer_details_table th {
        vertical-align: middle;
    }

    #offer_auth_table .offer_radio {
        cursor: pointer;
    }
</style>
@endsection

@section('content')

@component('components.breadcrumb')
    @slot('li_1') Transaction @endslot
    @slot('title') Offer Authorized @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">

        <div class="card">

            {{-- Page Header --}}
            <div class="card-header align-items-center d-flex pb-2 pt-2">
                <h5 class="card-title mb-0 flex-grow-1" id="page_title_heading">
                    Offer Authorised
                </h5>
            </div>

            <div class="card-body">

                {{-- =========================================================
                    MASTER OFFER GRID
                ========================================================== --}}
                <div class="table-responsive">
                    <table
                        id="offer_auth_table"
                        class="table nowrap align-middle table-bordered"
                        style="width:100%"
                        data-exclude-search="0"
                    >
                        <thead>
                            <tr>
                                <th
                                    class="text-center align-middle"
                                    style="width: 40px;"
                                >
                                </th>

                                <th>Offer No.</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>NABL Type</th>
                                <th>Customer</th>
                                <th>Ch. No.</th>
                                <th>Ch. Date</th>
                                <th>Evaluation</th>
                                <th>Acc. Std.</th>
                                <th>Test Method</th>
                                <th>Covered Are.</th>
                                <th>Radiation Energy</th>
                                <th>Del. Date</th>
                                <th>Procedure Submitted</th>
                            </tr>
                        </thead>

                        <tbody></tbody>
                    </table>
                </div>

                {{-- =========================================================
                    OFFER DETAILS
                ========================================================== --}}
                <div class="row PIselected mt-2">

                    <div class="col-xl-12">

                        <div class="card">

                            <div class="card-header align-items-center d-flex pt-0">

                               

                                <h4 class="card-title mb-0 flex-grow-1">
                                    <b>Offer Details</b>
                                </h4>

                            </div>

                            <div class="card-body">

                                <div class="live-preview">

                                    <div class="table-responsive">

                                        <table
                                            id="offer_details_table"
                                            class="table nowrap align-middle table-bordered"
                                            style="width:100%"
                                        >
                                            <thead>
                                                <tr>
                                                    <th>Process Type</th>
                                                    <th>Process</th>
                                                    <th>Lab ID</th>
                                                    <th>Customer ID No.</th>
                                                    <th>Job Description</th>
                                                    <th>Die No.</th>
                                                    <th>Heat No.</th>
                                                    <th>Drg. No.</th>
                                                    <th>Material</th>
                                                    <th>Thickness (mm)</th>
                                                    <th>Quantity</th>
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

                </div>



                {{-- =========================================================
                    ACTION BUTTONS
                ========================================================== --}}
                <div class="col-lg-12 text-end mt-3 mb-2">

                    <button
                        type="button"
                        class="btn btn-success me-1"
                        id="btn-authorize"
                    >
                        Authorise
                    </button>

                    <button
                        type="button"
                        class="btn btn-info me-1"
                        id="btn-unauthorize"
                    >
                        Accept Unauthorise
                    </button>

                    <button
                        type="button"
                        class="btn btn-danger me-1 d-none"
                        id="btn-delete"
                        disabled
                    >
                        Delete
                    </button>

                    <button
                        type="button"
                        class="btn btn-warning"
                        id="btn-cancel"
                    >
                        Reset
                    </button>

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

    var currentAuthorizedStatus = 0; // 0 = Pending, 1 = Authorized
    var selectedOfferId = null;
    var isAuthorizing = false;

    $(document).ready(function() {
        var table = $('#offer_auth_table').DataTable({
            serverSide: true,
            paging: false,
            info: false,
            lengthChange: false,
            ajax: {
                url: "{{ route('listing-offer_authorized') }}",
                type: "POST",
                headers: headerOpt,
                data: function(d) {
                    d.authorized_status = currentAuthorizedStatus;
                    if (selectedOfferId) {
                        d.selected_offer_id = selectedOfferId;
                    }
                },
                error: function(jqXHR) {
                    if (jqXHR.status == 401) {
                        console.log(jqXHR.statusText);
                    } else {
                        console.log('Something went wrong!');
                    }
                }
            },
            columns: [
                {
                    data: 'offer_id',
                    name: 'offer_id',
                    orderable: false,
                    searchable: false,
                    className: 'text-center align-middle',
                    render: function(data, type, row) {
                        var isChecked = (selectedOfferId && String(selectedOfferId) === String(data)) ? 'checked' : '';
                        return '<input type="radio" name="selected_offer" class="form-check-input offer_radio" value="' + data + '" ' + isChecked + ' />';
                    }
                },
                { data: 'offer_no', name: 'offer.offer_no' },
                { data: 'offer_date', name: 'offer.offer_date' },
                { data: 'job_type_fix', name: 'offer.job_type_fix' },
                { data: 'nabl_type_fix', name: 'offer.nabl_type_fix' },
                { data: 'customer_name', name: 'customers.customer' },
                { data: 'challan_no', name: 'offer.challan_no' },
                { data: 'challan_date', name: 'offer.challan_date' },
                { data: 'evaluation_as_per', name: 'evaluation_as_per.evaluation_as_per', defaultContent: '' },
                { data: 'acceptance_standard', name: 'acceptance_standards.acceptance_standard', defaultContent: '' },
                { data: 'test_method', name: 'test_method.test_method', defaultContent: '' },
                { data: 'area_of_coverage', name: 'offer.coverage_area', defaultContent: '' },
                { data: 'radiation_energy_fix', name: 'offer.radiation_energy_fix', defaultContent: '' },
                { data: 'del_date', name: 'offer.del_date', defaultContent: '' },
                { data: 'procedure_submitted_fix', name: 'offer.procedure_submitted_fix', defaultContent: '' }
            ],
            order: [[1, 'asc']],
            buttons: [],
            dom: 'Bfrt',
        });
        jQuery('.dataTables_filter').hide();

        var detailsTable = $('#offer_details_table').DataTable({
            processing: false,
            serverSide: false,
            paging: false,
            searching: false,
            info: false,
            data: [],
            columns: [
                { data: 'process_type', defaultContent: '' },
                { data: 'type_of_testing_id_fix', defaultContent: '' },
                { data: 'lab_id', defaultContent: '' },
                { data: 'customer_id_no', defaultContent: '' },
                { data: 'job_desc', defaultContent: '' },
                { data: 'die_no', defaultContent: '' },
                { data: 'heat_no', defaultContent: '' },
                { data: 'drg_no', defaultContent: '' },
                { data: 'material_name', defaultContent: '' },
                { data: 'thickness', defaultContent: '' },
                { data: 'quantity', defaultContent: 0 },
                { data: 'remark', defaultContent: '' }
            ]
        });

        function loadOfferDetails(offerIds) {
            if (!offerIds || (Array.isArray(offerIds) && offerIds.length === 0)) {
                clearDetailsTable();
                return;
            }

            $.ajax({
                url: "{{ route('get-offer_authorized_details') }}",
                type: "GET",
                data: { offer_ids: offerIds },
                headers: headerOpt,
                success: function(response) {
                    if (response.status === 'success' && response.data && response.data.length > 0) {
                        detailsTable.clear().rows.add(response.data).draw();
                    } else {
                        detailsTable.clear().draw();
                    }
                },
                error: function() {
                    detailsTable.clear().draw();
                    toastr.error('Failed to load offer details.');
                }
            });
        }

        function clearDetailsTable() {
            detailsTable.clear().draw();
        }

        function updateSelectedDetails() {
            var currentChecked = $('input[name="selected_offer"]:checked').val();
            if (currentChecked) {
                selectedOfferId = currentChecked;
            }

            if (currentAuthorizedStatus === 1) {
                if (selectedOfferId) {
                    $('#btn-delete').prop('disabled', false);
                } else {
                    $('#btn-delete').prop('disabled', true);
                }
            }

            if (selectedOfferId) {
                loadOfferDetails([selectedOfferId]);
            } else {
                clearDetailsTable();
            }
        }

        // Radio Selection Handler for Offer Details loading
        $(document).on('change', '.offer_radio', function() {
            selectedOfferId = $(this).val();
            updateSelectedDetails();
        });

        // Master table draw event
        table.on('draw', function() {
            if (selectedOfferId) {
                $('input[name="selected_offer"][value="' + selectedOfferId + '"]').prop('checked', true);
            }
            updateSelectedDetails();
        });

        // Switch Mode Functions
        function switchToPendingMode() {
            currentAuthorizedStatus = 0;
            selectedOfferId = null;
            $('#btn-authorize').removeClass('d-none').prop('disabled', false);
            $('#btn-delete').addClass('d-none').prop('disabled', true);
            $('input[name="selected_offer"]').prop('checked', false);
            table.ajax.reload();
            clearDetailsTable();
        }

        function switchToAuthorizedMode() {
            currentAuthorizedStatus = 1;
            selectedOfferId = null;
            $('#btn-authorize').removeClass('d-none').prop('disabled', true);
            $('#btn-delete').removeClass('d-none').prop('disabled', true);
            $('input[name="selected_offer"]').prop('checked', false);
            table.ajax.reload();
            clearDetailsTable();
        }

        // Authorize Button Action
        $('#btn-authorize').on('click', function(e) {
            e.preventDefault();
            if (isAuthorizing) return false;

            var offerId = selectedOfferId || $('input[name="selected_offer"]:checked').val();

            if (!offerId) {
                toastr.error('Please Select An Offer To Authorise.');
                return;
            }

            isAuthorizing = true;
            var $btn = $(this);
            $btn.prop('disabled', true).css('pointer-events', 'none');
            $('#btn-unauthorize, #btn-cancel').prop('disabled', true);

            $.ajax({
                url: "{{ route('authorize-offer') }}",
                type: "POST",
                data: { offer_ids: [offerId] },
                headers: headerOpt,
                success: function(response) {
                    isAuthorizing = false;
                    $btn.prop('disabled', false).css('pointer-events', 'auto').html('Authorise');
                    $('#btn-unauthorize, #btn-cancel').prop('disabled', false);
                    if (response.status === 'success' || response.response_code === '1') {
                        selectedOfferId = null;
                        function nextFn() {
                            window.location.reload();
                        }
                        toastSuccess(response.message || 'Offer Authorised.', nextFn);
                    } else {
                        toastr.error(response.message || 'Error authorising offer.');
                    }
                },
                error: function(jqXHR) {
                    isAuthorizing = false;
                    $btn.prop('disabled', false).css('pointer-events', 'auto').html('Authorise');
                    $('#btn-unauthorize, #btn-cancel').prop('disabled', false);
                    var errorMsg = 'Something went wrong while authorising offer.';
                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                        errorMsg = jqXHR.responseJSON.message;
                    }
                    toastr.error(errorMsg);
                }
            });
        });

        // Accept Unauthorize Button Action (Switches mode to show authorized records & Delete button disabled initially)
        $('#btn-unauthorize').on('click', function() {
            switchToAuthorizedMode();
        });

        // Delete / Unauthorize Button Action
        $('#btn-delete').on('click', function() {
            var offerId = selectedOfferId || $('input[name="selected_offer"]:checked').val();

            if (!offerId) {
                toastr.error('Please Select An Offer To Unauthorize.');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true);

            $.ajax({
                url: "{{ route('unauthorize-offer') }}",
                type: "POST",
                data: { offer_ids: [offerId] },
                headers: headerOpt,
                success: function(response) {
                    $btn.prop('disabled', false).html('Delete');
                    if (response.status === 'success' || response.response_code === '1') {
                        selectedOfferId = null;
                        function nextFn() {
                            window.location.reload();
                        }
                        toastSuccess(response.message || 'Offer Unauthorized.', nextFn);                       
                    } else {
                        toastr.error(response.message || 'Error unauthorizing offer.');
                    }
                },
                error: function(jqXHR) {
                    $btn.prop('disabled', false).html('Delete');
                    var errorMsg = 'Something went wrong while unauthorizing offer.';
                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                        errorMsg = jqXHR.responseJSON.message;
                    }
                    toastr.error(errorMsg);
                }
            });
        });

        // Reset / Cancel Buttons Action
        $('#btn-cancel').on('click', function() {
            selectedOfferId = null;
            if (currentAuthorizedStatus === 1) {
                switchToPendingMode();
            } else {
                $('input[name="selected_offer"]').prop('checked', false);
                clearDetailsTable();
                table.ajax.reload();
            }
        });
    });
</script>
@endsection