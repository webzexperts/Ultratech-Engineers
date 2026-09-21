<?php $__env->startSection('title'); ?> Offer Authorized <?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
<style>
    .table-details-header {
        background-color: #f8fafc;
        font-weight: 600;
        font-size: 13px;
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-bottom: none;
        color: #1e293b;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('li_1'); ?> Transaction <?php $__env->endSlot(); ?>
    <?php $__env->slot('title'); ?> Offer Authorized <?php $__env->endSlot(); ?>
<?php echo $__env->renderComponent(); ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Offer Authorized</h5>
                <div>
                    <button type="button" class="btn btn-primary" id="btn-authorize">Authorize</button>
                    <button type="button" class="btn btn-primary" id="btn-unauthorize">Unauthorize</button>
                    <button type="button" class="btn btn-primary" id="btn-cancel">Reset</button>
                </div>
            </div>
            <div class="card-body">
                <!-- Master Offers Grid -->
                <div class="table-responsive">
                    <table id="offer_auth_table" class="table nowrap align-middle table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-center align-middle" style="width: 30px;">
                                    <input type="checkbox" id="checkall_offers" class="form-check-input" />
                                </th>
                                <th>Offer No.</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Ch. Type</th>
                                <th>Customer</th>
                                <th>Process</th>
                                <th>Ch. No.</th>
                                <th>Ch. Date</th>
                                <th>Evaluation</th>
                                <th>Acc. Std.</th>
                                <th>Procedure Ref.</th>
                                <th>Covered Area</th>
                                <th>Radiation Energy</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- Offer Details Grid Section -->
                <div class="mt-4">
                    <div class="table-details-header">Offer Items / Details</div>
                    <div class="table-responsive" style="max-height: 250px;">
                        <table id="offer_details_table" class="table nowrap align-middle table-bordered" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>Process Type</th>
                                    <th>Sr No. / Id No.</th>
                                    <th>Job Description</th>
                                    <th>Die No.</th>
                                    <th>Heat No.</th>
                                    <th>Drg. No.</th>
                                    <th>Material</th>
                                    <th class="text-end">Total Qty</th>
                                    <th class="text-end">Req. Qty</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                            <tbody id="offer_details_body">
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-3">No record found!</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script-manage'); ?>
<script>
    var headerOpt = {
        'Authorization': 'Bearer <?php echo e(Auth::user()->auth_token); ?>',
        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
    };

    $(document).ready(function() {
        var table = $('#offer_auth_table').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'desc']],
            dom: 'lfrtip',
            ajax: {
                url: "<?php echo e(route('listing-offer_authorized')); ?>",
                type: "POST",
                headers: headerOpt,
                error: function (jqXHR, textStatus, errorThrown) {
                    if (jqXHR.status == 401) {
                        console.log(jqXHR.statusText);
                    } else {
                        toastr.error('Something went wrong!');
                    }
                }
            },
            columns: [
                {
                    data: 'checkbox',
                    name: 'checkbox',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    defaultContent: ''
                },
                { data: 'offer_no', name: 'offer.offer_no', defaultContent: '' },
                { data: 'offer_date', name: 'offer.offer_date', defaultContent: '' },
                { data: 'job_type_fix', name: 'offer.job_type_fix', defaultContent: '' },
                { data: 'nabl_type_fix', name: 'offer.nabl_type_fix', defaultContent: '' },
                { data: 'customer_name', name: 'customers.customer', defaultContent: '' },
                { data: 'process_type', name: 'offer_details.type_of_testing_id_fix', defaultContent: '' },
                { data: 'dc_no', name: 'offer.dc_no', defaultContent: '' },
                { data: 'dc_date', name: 'offer.dc_date', defaultContent: '' },
                { data: 'evaluation_as_per', name: 'evaluation_as_per.evaluation_as_per', defaultContent: '' },
                { data: 'acceptance_standard', name: 'acceptance_standards.acceptance_standard', defaultContent: '' },
                { data: 'procedure_reference', name: 'procedure_reference.procedure_reference', defaultContent: '' },
                { data: 'area_of_coverage', name: 'area_of_coverage.area_of_coverage', defaultContent: '' },
                { data: 'test_at_fix', name: 'offer.test_at_fix', defaultContent: '', orderable: false, searchable: false }
            ]
        });

        // Check All Checkbox Handler
        $('#checkall_offers').on('change', function() {
            var isChecked = $(this).is(':checked');
            $('.offer_checkbox').prop('checked', isChecked);
            if (isChecked) {
                var firstChecked = $('.offer_checkbox:checked').first().val();
                if (firstChecked) {
                    loadOfferDetails(firstChecked);
                }
            } else {
                clearDetailsTable();
            }
        });

        // Checkbox Selection Handler for Offer Details loading
        $(document).on('change', '.offer_checkbox', function() {
            var selectedOfferId = $(this).val();
            if ($(this).is(':checked')) {
                loadOfferDetails(selectedOfferId);
            } else {
                var remainingChecked = $('.offer_checkbox:checked').last().val();
                if (remainingChecked) {
                    loadOfferDetails(remainingChecked);
                } else {
                    clearDetailsTable();
                }
            }
        });

        // Row Click Handler
        $('#offer_auth_table tbody').on('click', 'tr', function(e) {
            if ($(e.target).is('input[type="checkbox"]')) return;
            var checkbox = $(this).find('.offer_checkbox');
            if (checkbox.length) {
                checkbox.prop('checked', !checkbox.is(':checked')).trigger('change');
            }
        });

        function loadOfferDetails(offerId) {
            $.ajax({
                url: "<?php echo e(route('get-offer_authorized_details')); ?>",
                type: "GET",
                data: { offer_id: offerId },
                headers: headerOpt,
                success: function(response) {
                    var tbody = $('#offer_details_body');
                    tbody.empty();
                    if (response.status === 'success' && response.data.length > 0) {
                        $.each(response.data, function(index, item) {
                            var rowHtml = '<tr>' +
                                '<td>' + (item.process_type || 'Fresh') + '</td>' +
                                '<td>' + (index + 1) + '</td>' +
                                '<td>' + (item.job_description || '') + '</td>' +
                                '<td>' + (item.die_no || 'N.A.') + '</td>' +
                                '<td>' + (item.heat_no || '') + '</td>' +
                                '<td>' + (item.drg_no || 'N.A.') + '</td>' +
                                '<td>' + (item.material || '') + '</td>' +
                                '<td class="text-end">' + (item.total_qty || 0) + '</td>' +
                                '<td class="text-end">' + (item.req_qty || 0) + '</td>' +
                                '<td>' + (item.remark || '') + '</td>' +
                            '</tr>';
                            tbody.append(rowHtml);
                        });
                    } else {
                        tbody.html('<tr><td colspan="10" class="text-center text-muted py-3">No details found for selected offer.</td></tr>');
                    }
                },
                error: function() {
                    $('#offer_details_body').html('<tr><td colspan="10" class="text-center text-danger py-3">Failed to load details.</td></tr>');
                    toastr.error('Failed to load offer details.');
                }
            });
        }

        function clearDetailsTable() {
            $('#offer_details_body').html('<tr><td colspan="10" class="text-center text-muted py-3">Select an offer from the grid above to view item details.</td></tr>');
        }

        // Authorize Button Action
        $('#btn-authorize').on('click', function() {
            var selectedOffers = [];
            $('.offer_checkbox:checked').each(function() {
                selectedOffers.push($(this).val());
            });

            if (selectedOffers.length === 0) {
                toastr.error('Please select at least one offer to authorize.');
                return;
            }

            $.ajax({
                url: "<?php echo e(route('authorize-offer')); ?>",
                type: "POST",
                data: { offer_ids: selectedOffers },
                headers: headerOpt,
                success: function(response) {
                    if (response.status === 'success') {
                        toastr.success(response.message || 'Offer(s) Authorized Successfully!');
                        table.ajax.reload();
                        clearDetailsTable();
                        $('#checkall_offers').prop('checked', false);
                    } else {
                        toastr.error(response.message || 'Error authorizing offer.');
                    }
                },
                error: function() {
                    toastr.error('Something went wrong while authorizing offer.');
                }
            });
        });

        // Unauthorize Button Action
        $('#btn-unauthorize').on('click', function() {
            var selectedOffers = [];
            $('.offer_checkbox:checked').each(function() {
                selectedOffers.push($(this).val());
            });

            if (selectedOffers.length === 0) {
                toastr.error('Please select at least one offer to unauthorize.');
                return;
            }

            $.ajax({
                url: "<?php echo e(route('unauthorize-offer')); ?>",
                type: "POST",
                data: { offer_ids: selectedOffers },
                headers: headerOpt,
                success: function(response) {
                    if (response.status === 'success') {
                        toastr.success(response.message || 'Offer(s) Unauthorized Successfully!');
                        table.ajax.reload();
                        clearDetailsTable();
                        $('#checkall_offers').prop('checked', false);
                    } else {
                        toastr.error(response.message || 'Error unauthorizing offer.');
                    }
                },
                error: function() {
                    toastr.error('Something went wrong while unauthorizing offer.');
                }
            });
        });

        // Reset / Cancel Buttons Action
        $('#btn-cancel').on('click', function() {
            $('.offer_checkbox').prop('checked', false);
            $('#checkall_offers').prop('checked', false);
            clearDetailsTable();
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\xampp\htdocs\ultratech-cbs\resources\views/manage/transaction/manage-offer_authorized.blade.php ENDPATH**/ ?>