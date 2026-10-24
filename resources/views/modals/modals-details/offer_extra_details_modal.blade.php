<div class="modal fade" id="OfferExtraDetailsModal" aria-labelledby="OfferExtraDetailsModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-lg" style="max-width: 950px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="OfferExtraDetailsModalLabel">Offer Extra Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="OfferExtraDetailsForm" class="needs-validation" novalidate>
                    @csrf
                    <div class="container-fluid px-2">

                        {{-- 1. Test Witness --}}
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-md-7">
                                <label class="form-label mb-0" for="test_witness_tpi_fix">Test Witness by the customer or its representatives or Third-Party Inspection (TPI) Agency?</label>
                            </div>
                            <div class="col-md-5">
                                <select class="js-example-basic-single" name="test_witness_tpi_fix" id="test_witness_tpi_fix" style="width: 100%;">
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>

                        {{-- 2. TPI Person --}}
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-md-7">
                                <label class="form-label mb-0" for="tpi_person">TPI Person</label>
                            </div>
                            <div class="col-md-5">
                                <input type="text" class="form-control skip-tab" name="tpi_person" id="tpi_person" readonly tabindex="-1" style="pointer-events: none;">
                            </div>
                        </div>

                        {{-- 3. Test Item/s Condition --}}
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-md-7">
                                <label class="form-label mb-0" for="test_items_condition">Test Item/s Condition</label>
                            </div>
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="test_items_condition" id="test_items_condition">
                            </div>
                        </div>

                        {{-- 4. Information provided by you --}}
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-md-7">
                                <label class="form-label mb-0" for="public_domain_agree_fix">Information provided by you are intends to place in the public domain by testing laboratory, are you agree ?</label>
                            </div>
                            <div class="col-md-5">
                                <select class="js-example-basic-single" name="public_domain_agree_fix" id="public_domain_agree_fix" style="width: 100%;">
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>

                        {{-- 5. Conformity statement --}}
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-md-7">
                                <label class="form-label mb-0" for="conformity_statement_fix">Do wish to incorporate the conformity statement in the Test Report ?</label>
                            </div>
                            <div class="col-md-5">
                                <select class="js-example-basic-single" name="conformity_statement_fix" id="conformity_statement_fix" style="width: 100%;">
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>

                        {{-- 6. Requirements including test methods --}}
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-md-7">
                                <label class="form-label mb-0" for="test_methods_defined_fix">The requirements, including the test methods to be used are adequately defined, documented and understood by laboratory.</label>
                            </div>
                            <div class="col-md-5">
                                <select class="js-example-basic-single" name="test_methods_defined_fix" id="test_methods_defined_fix" style="width: 100%;">
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>

                        {{-- 7. Laboratory capability --}}
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-md-7">
                                <label class="form-label mb-0" for="laboratory_capability_fix">The laboratory has the capability and resources to meet the requirements.</label>
                            </div>
                            <div class="col-md-5">
                                <select class="js-example-basic-single" name="laboratory_capability_fix" id="laboratory_capability_fix" style="width: 100%;">
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>

                        {{-- 8. Appropriate test method --}}
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-md-7">
                                <label class="form-label mb-0" for="appropriate_test_method_fix">The appropriate test method is selected and is capable of meeting the customer's requirements.</label>
                            </div>
                            <div class="col-md-5">
                                <select class="js-example-basic-single" name="appropriate_test_method_fix" id="appropriate_test_method_fix" style="width: 100%;">
                                    <option value="">Select</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>

                        {{-- 9. Pertinent discussions --}}
                        <div class="row g-2 mb-2 align-items-start">
                            <div class="col-md-7 pt-1">
                                <label class="form-label mb-0" for="pertinent_discussions">Pertinent discussions with customer relating to the customer's requirements of the work during the period of execution of the contract.</label>
                            </div>
                            <div class="col-md-5">
                                <textarea class="form-control" name="pertinent_discussions" id="pertinent_discussions" rows="3"></textarea>
                            </div>
                        </div>

                        {{-- 10. Intimate Customer deviation --}}
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-md-7">
                                <label class="form-label mb-0" for="deviation_intimate">Intimate the Customer, if any deviation from the contract.</label>
                            </div>
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="deviation_intimate" id="deviation_intimate">
                            </div>
                        </div>

                        {{-- 11. Contract review process repeated --}}
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col-md-7">
                                <label class="form-label mb-0" for="contract_review_repeated_fix">Contract review process repeated as amendment is received after work has command and the amendment has been communicated.</label>
                            </div>
                            <div class="col-md-5">
                                <select class="js-example-basic-single" name="contract_review_repeated_fix" id="contract_review_repeated_fix" style="width: 100%;">
                                    <option value="">Select</option>
                                    <option value="Applicable">Applicable</option>
                                    <option value="Not Applicable">Not Applicable</option>
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="add_extra_details_btn">Add</button>
                        <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('script-modal')
<script>
    function toggleExtraTpiPerson() {
        let val = jQuery('#OfferExtraDetailsModal #test_witness_tpi_fix').val();
        let $tpi = jQuery('#OfferExtraDetailsModal #tpi_person');
        if (val === 'Yes') {
            $tpi.prop('readonly', false).css('pointer-events', 'auto').removeClass('skip-tab').removeAttr('tabindex');
        } else {
            $tpi.val('').prop('readonly', true).css('pointer-events', 'none').addClass('skip-tab').attr('tabindex', '-1');
        }
    }

    jQuery(document).ready(function() {
        jQuery('#OfferExtraDetailsModal').on('shown.bs.modal', function () {
            jQuery(this).find('.js-example-basic-single').each(function() {
                if (!jQuery(this).hasClass("select2-hidden-accessible")) {
                    jQuery(this).select2({
                        dropdownParent: jQuery('#OfferExtraDetailsModal'),
                        width: '100%'
                    });
                }
            });
            toggleExtraTpiPerson();
        });

        jQuery('#OfferExtraDetailsModal').on('change', '#test_witness_tpi_fix', function () {
            toggleExtraTpiPerson();
        });

        jQuery('#OfferExtraDetailsModal').on('hidden.bs.modal', function () {
            if (jQuery('#OfferModal').hasClass('show')) {
                jQuery('body').addClass('modal-open');
            }
        });
    });
</script>
@endpush