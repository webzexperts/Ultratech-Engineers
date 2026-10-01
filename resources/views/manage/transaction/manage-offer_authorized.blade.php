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

    #offer_auth_table .offer_checkbox,
    #checkall_offers {
        cursor: pointer;
    }

    #authorized_by_id {
        width: 100%;
    }

    #prepared_by_user_id {
        width: 100%;
        pointer-events: none;
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
                                    style="width: 30px;"
                                >
                                    <input
                                        type="checkbox"
                                        id="checkall_offers"
                                        class="form-check-input"
                                    />
                                </th>

                                <th>Offer No.</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>NABL Type</th>
                                <th>Customer</th>
                                <th>Ch. No.</th>
                                <th>Ch. Date</th>
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
                                                    <th>Job Description</th>
                                                    <th>Heat No.</th>
                                                    <th>Drg. No.</th>
                                                    <th>Material</th>
                                                    <th>Evaluation</th>
                                                    <th>Acc. Std.</th>
                                                    <th>Procedure Ref.</th>
                                                    <th>Area of Coverage</th>
                                                    <th>Total Qty.</th>
                                                    <th>Req. Qty.</th>
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
                    AUTHORISED BY
                ========================================================== --}}
                <div class="row mt-2 align-items-center" id="authorized_by_container">

                    <div class="col-md-5 col-lg-4 d-flex align-items-center position-relative">
                        <div class="col-4">
                            <label for="authorized_by_id" class="form-label me-2 mb-0  text-nowrap"> Authorised By <sup class="astric">*</sup> </label>
                        </div>
                        <div class="col-8">
                            <select class="js-example-basic-single zindexnotapply" id="authorized_by_id" name="authorized_by_id" required>
                                <option value=""> Select Authorised By</option>
                                @foreach($authorized_persons as $person)
                                    <option value="{{ $person->authority_person_id }}">
                                        {{ $person->operator }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-tooltip" id="authorized_by_id_error">
                                Select Authorised By.
                            </div>
                        </div>
                    </div>

                    {{-- Authorised By User --}}
                    <div class="col-md-5 col-lg-4 d-flex align-items-center">
                        <div class="col-4">
                            <label for="prepared_by_user_id" class="form-label me-2 mb-0 text-nowrap"> Authorised By User</label>
                        </div>
                        <div class="col-8">
                            <select class="js-example-basic-single skip-tab zindexnotapply" name="prepared_by_user_id" id="prepared_by_user_id" disabled style="pointer-events: none;">
                                <option value="">Select Authorised By User </option>

                                @forelse(getUsers() as $users)

                                    <option
                                        value="{{ $users->id }}"
                                        {{ auth()->id() == $users->id ? 'selected' : '' }}
                                    >
                                        {{ $users->person_name }}
                                    </option>
                                @empty
                                @endforelse

                            </select>
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
                        Authorize
                    </button>

                    <button
                        type="button"
                        class="btn btn-info me-1"
                        id="btn-unauthorize"
                    >
                        Accept Unauthorize
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

    $(document).ready(function() {
        $('#prepared_by_user_id').prop('disabled', true);

        var table = $('#offer_auth_table').DataTable({
            serverSide: true,
           
            ajax: {
                url: "{{ route('listing-offer_authorized') }}",
                type: "POST",
                headers: headerOpt,
                data: function(d) {
                    d.authorized_status = currentAuthorizedStatus;
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
                        return '<input type="checkbox" class="form-check-input offer_checkbox" value="' + data + '" />';
                    }
                },
                { data: 'offer_no', name: 'offer.offer_no' },
                { data: 'offer_date', name: 'offer.offer_date' },
                { data: 'job_type_fix', name: 'offer.job_type_fix' },
                { data: 'nabl_type_fix', name: 'offer.nabl_type_fix' },
                { data: 'customer_name', name: 'customers.customer' },
                { data: 'dc_no', name: 'offer.dc_no' },
                { data: 'dc_date', name: 'offer.dc_date' }
            ],
            order: [[1, 'asc']],
             buttons: [],
            
            dom: 'Blfrtip',
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
                { data: 'job_desc', defaultContent: '' },
                { data: 'heat_no', defaultContent: '' },
                { data: 'drg_no', defaultContent: '' },
                { data: 'material_name', defaultContent: '' },
                { data: 'evaluation_as_per', defaultContent: '' },
                { data: 'acceptance_standard', defaultContent: '' },
                { data: 'procedure_reference', defaultContent: '' },
                { data: 'area_of_coverage', defaultContent: '' },
                { data: 'total_qty', defaultContent: 0 },
                { data: 'req_qty', defaultContent: 0 },
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

        function updateCheckedDetails() {
            var selectedOffers = [];
            $('.offer_checkbox:checked').each(function() {
                selectedOffers.push($(this).val());
            });

            if (currentAuthorizedStatus === 1) {
                if (selectedOffers.length > 0) {
                    $('#btn-delete').prop('disabled', false);
                } else {
                    $('#btn-delete').prop('disabled', true);
                }
            }

            if (selectedOffers.length > 0) {
                loadOfferDetails(selectedOffers);
            } else {
                clearDetailsTable();
            }
        }

        // Check All Checkbox Handler
        $('#checkall_offers').on('change', function() {
            var isChecked = $(this).is(':checked');
            $('.offer_checkbox').prop('checked', isChecked);
            updateCheckedDetails();
        });

        // Checkbox Selection Handler for Offer Details loading
        $(document).on('change', '.offer_checkbox', function() {
            var totalCheckboxes = $('.offer_checkbox').length;
            var totalChecked = $('.offer_checkbox:checked').length;
            $('#checkall_offers').prop('checked', totalCheckboxes > 0 && totalCheckboxes === totalChecked);
            updateCheckedDetails();
        });

        // Master table draw event
        table.on('draw', function() {
            $('#checkall_offers').prop('checked', false);
            updateCheckedDetails();
        });

        // Switch Mode Functions
        function switchToPendingMode() {
            currentAuthorizedStatus = 0;
            $('#btn-authorize').removeClass('d-none').prop('disabled', false);
            $('#authorized_by_container').removeClass('d-none');
            $('#btn-delete').addClass('d-none').prop('disabled', true);
            $('#checkall_offers').prop('checked', false);
            table.ajax.reload();
            clearDetailsTable();
        }

        function switchToAuthorizedMode() {
            currentAuthorizedStatus = 1;
            $('#btn-authorize').removeClass('d-none').prop('disabled', true);
            $('#authorized_by_container').removeClass('d-none');
            $('#btn-delete').removeClass('d-none').prop('disabled', true);
            $('#checkall_offers').prop('checked', false);
            table.ajax.reload();
            clearDetailsTable();
        }
        jQuery(document).on('change', '#authorized_by_id', function() {
            $('#authorized_by_id').removeClass('is-invalid');
            $('#authorized_by_id_error').html('');
        });
        // Authorize Button Action
        $('#btn-authorize').on('click', function() {
            var selectedOffers = [];
            $('.offer_checkbox:checked').each(function() {
                selectedOffers.push($(this).val());
            });

            if (selectedOffers.length === 0) {
                toastr.error('Please Select At Least One Offer To Authorize.');
                return;
            }

            var authorizedById = $('#authorized_by_id').val();
            if (!authorizedById) {
                $('#authorized_by_id').addClass('is-invalid');
                $('#authorized_by_id_error').html('Select Authorised By.');
                return;
            } else{
                $('#authorized_by_id').removeClass('is-invalid');
                $('#authorized_by_id_error').html('');
            }

            $.ajax({
                url: "{{ route('authorize-offer') }}",
                type: "POST",
                data: { offer_ids: selectedOffers, authorized_by_id: authorizedById },
                headers: headerOpt,
                success: function(response) {
                    if (response.status === 'success' || response.response_code === '1') {
                        function nextFn() {
                            window.location.reload();
                        }
                        toastSuccess(response.message || 'Offer(s) Authorized Successfully!',nextFn);
                    } else {
                        toastr.error(response.message || 'Error authorizing offer.');
                    }
                },
                error: function() {
                    toastr.error('Something went wrong while authorizing offer.');
                }
            });
        });

        // Accept Unauthorize Button Action (Switches mode to show authorized records & Delete button disabled initially)
        $('#btn-unauthorize').on('click', function() {
            switchToAuthorizedMode();
        });

        // Delete / Unauthorize Button Action
        $('#btn-delete').on('click', function() {
            var selectedOffers = [];
            $('.offer_checkbox:checked').each(function() {
                selectedOffers.push($(this).val());
            });

            if (selectedOffers.length === 0) {
                toastr.error('Please Select At Least One Offer To Unauthorize.');
                return;
            }

            var authorizedById = $('#authorized_by_id').val();
            if (!authorizedById) {
                $('#authorized_by_id').addClass('is-invalid');
                $('#authorized_by_id_error').html('Select Authorised By.');
                return;
            } else{
                $('#authorized_by_id').removeClass('is-invalid');
                $('#authorized_by_id_error').html('');
            }

            $.ajax({
                url: "{{ route('unauthorize-offer') }}",
                type: "POST",
                data: { offer_ids: selectedOffers, authorized_by_id: authorizedById },
                headers: headerOpt,
                success: function(response) {
                    if (response.status === 'success' || response.response_code === '1') {
                        function nextFn() {
                            window.location.reload();
                        }
                        toastSuccess(response.message || 'Offer(s) Unauthorized Successfully!',nextFn);                       
                    } else {
                        toastr.error(response.message || 'Error unauthorizing offer.');
                    }
                },
                error: function(jqXHR) {
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
            if (currentAuthorizedStatus === 1) {
                switchToPendingMode();
            } else {
                $('.offer_checkbox').prop('checked', false);
                $('#checkall_offers').prop('checked', false);
                $('#authorized_by_id').val('').trigger('change');
                clearDetailsTable();
            }
        });
    });
</script>
@endsection