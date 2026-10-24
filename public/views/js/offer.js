/* =====================================================================
 *  Offer (parent + child) — mirrors material_inward.js structure.
 *  Detail rows are held in offer_details_data[] with a per-row
 *  `mode` (Insert / Update / Delete) and submitted as JSON on save.
 * ===================================================================== */

var offer_details_data = [];
var formId = jQuery('#commonOfferForm').find('input[name="id"]').val();

/* ---------------------------------------------------------------------
 *  Edit (open + fill)
 * ------------------------------------------------------------------- */
jQuery('#dyntable tbody').on('click', '.edit-offer', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#OfferModal').find('#id').val(data["offer_id"]);
    if (data && data["offer_id"]) {
        fetchAndFillOffer(data["offer_id"]);
    }
});

function fetchAndFillOffer(id) {
    if (!id) return;
    jQuery('#OfferModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-offer",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.offer_data != null) {
                var d = data.offer_data;
                jQuery('#OfferModal').find('#id').val(d.offer_id != "" ? d.offer_id : "");

                let url = checkFileRoute + "?id=" + d.offer_id + "&name=" + d.pdf_name + "&type=offer";
                jQuery('#preview_btn').attr('href', url).show();

                jQuery('#OfferModal').find('#offer_sequence').val(d.offer_sequence ?? "").focus();
                jQuery('#OfferModal').find('#offer_no').val(d.offer_no ?? "");
                jQuery('#OfferModal').find('#offer_date').val(d.offer_date ?? "");
                jQuery('#OfferModal').find('#old_offer_date').val(d.offer_date ?? "");

                jQuery('#OfferModal').find('input[name="nabl_type_fix"][value="' + d.nabl_type_fix + '"]').prop('checked', true);
                jQuery('#OfferModal').find('input[name="test_at_fix"][value="' + d.test_at_fix + '"]').prop('checked', true);
                jQuery('#OfferModal').find('input[name="job_type_fix"][value="' + d.job_type_fix + '"]').prop('checked', true);

                filterTestsByNabl();

                jQuery('#OfferModal').find('#customer_id').val(d.customer_id ?? "").trigger('change.select2');
                jQuery('#OfferModal').find('#challan_no').val(d.challan_no ?? "");
                jQuery('#OfferModal').find('#challan_date').val(d.challan_date ?? "");
                validateChallanNoDate();
                jQuery('#OfferModal').find('#material_type_id').val(d.material_type_id ?? "").trigger('change.select2');
                jQuery('#OfferModal').find('#total_qty').val(d.total_qty ?? "");
                jQuery('#OfferModal').find('#test_method').val(d.test_method_id ?? (d.test_method ?? "")).trigger('change.select2');
                jQuery('#OfferModal').find('#procedure_ref_id').val(d.procedure_reference_id ?? (d.procedure_ref_id ?? "")).trigger('change.select2');
                jQuery('#OfferModal').find('#evaluation_as_per_id').val(d.evaluation_as_per_id ?? "").trigger('change.select2');
                jQuery('#OfferModal').find('#acceptance_standard_id').val(d.acceptance_standard_id ?? "").trigger('change.select2');
                jQuery('#OfferModal').find('#radiation_energy_fix').val(d.radiation_energy_fix ?? "").trigger('change.select2');
                jQuery('#OfferModal').find('#coverage_area').val(d.coverage_area ?? "");
                jQuery('#OfferModal').find('#procedure_submitted_fix').val(d.procedure_submitted_fix ?? "").trigger('change.select2');
                jQuery('#OfferModal').find('#del_date').val(d.del_date ?? "");
                jQuery('#OfferModal').find('#special_note').val(d.special_note ?? (d.sp_note ?? ""));

                // Fill Extra Details Main Hidden fields & Modal fields
                jQuery('#main_test_witness_tpi_fix').val(d.test_witness_tpi_fix ?? "");
                jQuery('#main_tpi_person').val(d.tpi_person ?? "");
                jQuery('#main_test_items_condition').val(d.test_items_condition ?? (d.test_item_condition ?? ""));
                jQuery('#main_public_domain_agree_fix').val(d.public_domain_agree_fix ?? "");
                jQuery('#main_conformity_statement_fix').val(d.conformity_statement_fix ?? "");
                jQuery('#main_test_methods_defined_fix').val(d.test_methods_defined_fix ?? "");
                jQuery('#main_laboratory_capability_fix').val(d.laboratory_capability_fix ?? "");
                jQuery('#main_appropriate_test_method_fix').val(d.appropriate_test_method_fix ?? "");
                jQuery('#main_pertinent_discussions').val(d.pertinent_discussions ?? "");
                jQuery('#main_deviation_intimate').val(d.deviation_intimate ?? "");
                jQuery('#main_contract_review_repeated_fix').val(d.contract_review_repeated_fix ?? "");
                syncMainHiddenToExtraDetailsModal();

                offer_details_data = [];
                if (data.offer_details_data && data.offer_details_data.length > 0) {
                    data.offer_details_data.forEach(function (item) {
                        if (item.offer_detail_id && !item.offer_details_id) {
                            item.offer_details_id = item.offer_detail_id;
                        }
                    });
                    offer_details_data.push(...data.offer_details_data);
                }
                fillOfferDetailTable();

                // Customer is always readonly in Edit mode
                setSelect2Readonly(jQuery('#OfferModal').find('#customer_id'), true);
                jQuery('#OfferModal').find('#customer_id').addClass('skip-tab');

                // Reset datepicker maxDate to default (endDate) first
                jQuery('#OfferModal').find('#offer_date').datepicker('option', 'maxDate', endDate);
                var parentInUse = (data.offer_data && (data.offer_data.in_use == true || data.offer_data.in_use == "true" || data.offer_data.authorized_id_fix == 1 || data.offer_data.authorized_id_fix == "1"))
                    || offer_details_data.some(item => item.in_use == true || item.in_use == "true" || (item.used_qty && parseFloat(item.used_qty) > 0));
                lockParentRadios(true);
                if (parentInUse) {
                    jQuery('#OfferModal').find('#offer_sequence').prop('readonly', true).addClass('skip-tab');
                } else {
                    jQuery('#OfferModal').find('#offer_sequence').prop('readonly', false).removeClass('skip-tab');
                }
                updateParentRadiosLockState();
                // Offer date is always editable now
                jQuery('#OfferModal').find('#offer_date').prop('readonly', false).css('pointer-events', 'auto').removeClass('skip-tab');

                if (data.min_report_date) {
                    jQuery('#OfferModal').find('#offer_date')
                        .data('min-report-date', data.min_report_date)
                        .datepicker('option', 'maxDate', data.min_report_date);
                } else {
                    jQuery('#OfferModal').find('#offer_date').data('min-report-date', '');
                }

                const form = document.getElementById("commonOfferForm");
                if (form) form.classList.remove('was-validated');

                var isAuthorized = (d.authorized_id_fix == 1 || d.authorized_id_fix == "1");
                window.isOfferAuthorized = isAuthorized;
                if (isAuthorized) {
                    jQuery('#OfferModal').find('#submitbtn').prop('disabled', true).addClass('disabled').attr('title', 'Offer is Authorized');
                } else {
                    jQuery('#OfferModal').find('#submitbtn').prop('disabled', false).removeClass('disabled').removeAttr('title');
                }

                jQuery('#OfferModal').find('#add_new').show();
                jQuery('#OfferModal').find('#preview_btn').show();
            } else {
                console.log(data.response_message);
            }
        },
        error: function (jqXHR) {
            if (jqXHR.status == 401) { console.log(jqXHR.statusText); }
            else { console.log('Something went wrong!'); }
        },
        complete: function () {
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        }
    });
}

/* ---------------------------------------------------------------------
 *  Parent modal lifecycle
 * ------------------------------------------------------------------- */
jQuery('#OfferModal').on('show.bs.modal', function (e) {
    if (e.target !== this) return;
    var formId = jQuery('#commonOfferForm').find('input[name="id"]').val();
    if (formId == "" || formId == undefined) {
        resetParentForm(true);
    } else {
        jQuery('#OfferModal').find('#add_new').show();
        jQuery('#OfferModal').find('#preview_btn').show();
    }
});

jQuery('#OfferModal').on('shown.bs.modal', function () {
    var formId = jQuery('#commonOfferForm').find('input[name="id"]').val();
    if (formId && formId !== "") {
        setTimeout(() => {
            var isSeqReadonly = jQuery('#OfferModal').find('#offer_sequence').prop('readonly');
            if (isSeqReadonly) {
                jQuery('#OfferModal').find('#challan_no').focus().select();
            } else {
                jQuery('#OfferModal').find('#offer_sequence').focus().select();
            }
        }, 100);
    } else {
        setTimeout(() => {
            focusInitialOfferField();
        }, 150);
    }
});

jQuery('#OfferModal').on('hide.bs.modal', function (e) {
    if (e.target !== this) return;
    resetParentForm(false);
});

/* ---------------------------------------------------------------------
 *  Reset / Add New
 * ------------------------------------------------------------------- */
jQuery('#resetbtn').on('click', function () {
    var formId = jQuery('#OfferModal').find('#id').val();
    if (!formId) {
        resetParentForm(true);
        setTimeout(function () {
            focusInitialOfferField();
        }, 100);
    } else {
        fetchAndFillOffer(formId);
    }
});

jQuery('#OfferModal').on('click', '#add_new', function () {
    resetParentForm(true);
});

function resetParentForm(fetchLatest = false) {
    var form = document.getElementById("commonOfferForm");
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
    }

    var extraForm = document.getElementById("OfferExtraDetailsForm");
    if (extraForm) {
        extraForm.reset();
        jQuery('#OfferExtraDetailsModal select.js-example-basic-single').val('').trigger('change.select2');
        jQuery('#OfferExtraDetailsModal #tpi_person').prop('readonly', true).addClass('skip-tab').css('pointer-events', 'none');
    }
    jQuery('#commonOfferForm input[id^="main_"]').val('');

    jQuery('#OfferModal').find('#id').val('');
    jQuery('#OfferModal').find('#old_offer_date').val('');
    setSelect2Readonly(jQuery('#OfferModal').find('#customer_id'), false);
    jQuery('#OfferModal').find('#customer_id').removeClass('skip-tab');
    jQuery('#OfferModal').find('#offer_date').val(currentDate).prop('readonly', false).css('pointer-events', 'auto').removeClass('skip-tab').datepicker('option', 'maxDate', endDate).data('min-report-date', '');
    jQuery('#OfferModal').find('#offer_sequence').val('').prop('readonly', false).removeClass('skip-tab');
    jQuery('#OfferModal').find('#offer_no').val('');
    jQuery('#OfferModal').find('#total_qty').val('');
    jQuery('#OfferModal').find('#challan_no').val('');
    jQuery('#OfferModal').find('#challan_date').val('');
    validateChallanNoDate();
    jQuery('#OfferModal').find('#coverage_area').val('');
    jQuery('#OfferModal').find('#del_date').val(currentDate);
    jQuery('#OfferModal').find('#special_note').val('');
    window.lastCustomerTestingType = null;
    offer_details_data = [];
    jQuery('#OfferDetailTable tbody').empty().append('<tr><td colspan="14" id="noDetails" class="text-center">No Offer Details Added</td></tr>');

    // Reset select2 dropdowns
    jQuery('#OfferModal').find('#customer_id').val('').trigger('change');
    ['material_type_id', 'test_method', 'procedure_ref_id', 'evaluation_as_per_id', 'acceptance_standard_id', 'radiation_energy_fix'].forEach(function (f) {
        jQuery('#OfferModal').find('#' + f).val('').trigger('change.select2');
    });
    jQuery('#OfferModal').find('#procedure_submitted_fix').val('').trigger('change.select2');

    // Check defaults first
    jQuery('#OfferModal').find('input[name="test_at_fix"][value="Inhouse"]').prop('checked', true);
    jQuery('#OfferModal').find('input[name="job_type_fix"][value="Casting"]').prop('checked', true);
    jQuery('#OfferModal').find('input[name="nabl_type_fix"][value="Non NABL"]').prop('checked', true);

    lockParentRadios(false);

    ['nabl_type_fix', 'job_type_fix', 'test_at_fix'].forEach(function (name) {
        let $group = jQuery('#OfferModal').find('input[name="' + name + '"]');
        $group.attr('tabindex', '-1');
        let $chk = $group.filter(':checked');
        if (!$chk.length) $chk = $group.first();
        $chk.attr('tabindex', '0');
    });
    filterTestsByNabl();

    jQuery('#OfferModal').find('#add_new').hide();
    jQuery('#OfferModal').find('#preview_btn').hide();
    window.isOfferAuthorized = false;
    jQuery('#OfferModal').find('#submitbtn').prop('disabled', false).removeClass('disabled').removeAttr('title');
    jQuery('#OfferModal .PIselected button[data-bs-target="#OfferDetailsModal"]').show();
    jQuery('#OfferModal #resetbtn').prop('disabled', false);

    var currentCustId = jQuery('#OfferModal').find('#customer_id').val();
    if (fetchLatest) {
        if (currentCustId) {
            getLatestOfferNo(currentCustId);
            getLNRData(currentCustId);
        } else {
            getLNRData();
        }
    }
}

// Validate Offer Date against min report date
function validateOfferDateAgainstReports() {
    var offerDateInput = jQuery('#OfferModal').find('#offer_date');
    var offerDateVal = offerDateInput.val().trim();
    var minReportDateVal = offerDateInput.data('min-report-date');

    if (offerDateVal && minReportDateVal) {
        var offerDateObj = new Date(offerDateVal.split("/").reverse().join("-"));
        var minReportDateObj = new Date(minReportDateVal.split("/").reverse().join("-"));

        if (offerDateObj > minReportDateObj) {
            toastr.error("Offer Date cannot be greater than the Report Date.");
            return false;
        }
    }
    return true;
}

// Validate Del. Date against Offer Date (del_date cannot be less than offer_date)
function validateDelDateAgainstOfferDate() {
    var offerDateVal = (jQuery('#OfferModal #offer_date').val() || '').trim();
    var delDateVal = (jQuery('#OfferModal #del_date').val() || '').trim();
    if (offerDateVal && delDateVal) {
        var offerParts = offerDateVal.split('/');
        var delParts = delDateVal.split('/');
        if (offerParts.length === 3 && delParts.length === 3) {
            var offerDateObj = new Date(parseInt(offerParts[2], 10), parseInt(offerParts[1], 10) - 1, parseInt(offerParts[0], 10));
            var delDateObj = new Date(parseInt(delParts[2], 10), parseInt(delParts[1], 10) - 1, parseInt(delParts[0], 10));
            if (delDateObj < offerDateObj) {
                toastr.error("Del. Date Must Be Greater Than Or Equal To Offer Date.");
                jQuery('#OfferModal #del_date').focus();
                return false;
            }
        }
    }
    return true;
}

// When Offer Date is changed by user, auto set Del. Date equal to Offer Date
jQuery('#OfferModal').on('change', '#offer_date', function () {
    validateOfferDateAgainstReports();
    var offDate = jQuery(this).val();
    if (offDate && isValidDate(offDate)) {
        jQuery('#OfferModal #del_date').val(offDate);
    }
});

/* ---------------------------------------------------------------------
 *  Challan No & Date mutual required validation
 * ------------------------------------------------------------------- */
function validateChallanNoDate() {
    var cNo = (jQuery('#OfferModal #challan_no').val() || '').trim();
    var cDate = (jQuery('#OfferModal #challan_date').val() || '').trim();

    if (cNo || cDate) {
        jQuery('#OfferModal #challan_no').prop('required', true);
        jQuery('#OfferModal #challan_date').prop('required', true);
        jQuery('#challan_label sup.astric').show();
    } else {
        jQuery('#OfferModal #challan_no').prop('required', false).removeClass('is-invalid');
        jQuery('#OfferModal #challan_date').prop('required', false).removeClass('is-invalid');
        jQuery('#challan_label sup.astric').hide();
    }
}

jQuery(document).on('input change blur', '#OfferModal #challan_no, #OfferModal #challan_date', function () {
    validateChallanNoDate();
});

/* ---------------------------------------------------------------------
 *  Conditional fields
 * ------------------------------------------------------------------- */
jQuery('#OfferModal').on('change', '#is_any_tpi_witness , input[name="nabl_type_fix"]', function () {
    toggleTpiName();
});

function toggleTpiName() {
    var val = jQuery('#OfferModal').find('#is_any_tpi_witness').val();
    var nablType = jQuery('#OfferModal').find('input[name="nabl_type_fix"]:checked').val();
    var tpiElem = jQuery('#OfferModal').find('#tpi_name');
    var astricElem = jQuery('#OfferModal').find('#tpi_name_astric');
    if (val == 'Yes') {
        tpiElem.prop('readonly', false).css('pointer-events', 'auto').removeClass('skip-tab').attr('tabindex', '0');
        if (nablType == 'NABL') {
            tpiElem.prop('required', true);
            astricElem.show();
        } else {
            tpiElem.prop('required', false);
            astricElem.hide();
        }
    } else {
        tpiElem.val('').prop('readonly', true).css('pointer-events', 'none').addClass('skip-tab').attr('tabindex', '-1').prop('required', false).removeClass('is-invalid');
        astricElem.hide();
    }
}

// Parent radios drive tests and LNR (1/2/3/4 wise)
jQuery('#OfferModal').on('change', 'input[name="nabl_type_fix"], input[name="job_type_fix"], input[name="test_at_fix"]', function () {
    let name = jQuery(this).attr('name');
    jQuery('#OfferModal').find('input[name="' + name + '"]').attr('tabindex', '-1');
    jQuery(this).attr('tabindex', '0');
    if (name === 'nabl_type_fix') {
        filterTestsByNabl();
    }
    var formId = jQuery('#OfferModal').find('#id').val();
    var customerId = jQuery('#OfferModal').find('#customer_id').val();
    if (!formId && customerId) {
        getLNRData(customerId);
    }
});

// Tab navigation across top parent radios: nabl_type_fix -> job_type_fix -> test_at_fix -> customer_id
jQuery(document).on('keydown', '#OfferModal input[name="nabl_type_fix"]', function (e) {
    if ((e.key === 'Tab' || e.keyCode === 9) && !e.shiftKey) {
        e.preventDefault();
        let $target = jQuery('#OfferModal input[name="job_type_fix"]:checked');
        if (!$target.length) $target = jQuery('#OfferModal input[name="job_type_fix"]').first();
        $target.attr('tabindex', '0');
        if ($target.length) $target[0].focus();
    }
});

jQuery(document).on('keydown', '#OfferModal input[name="job_type_fix"]', function (e) {
    if (e.key === 'Tab' || e.keyCode === 9) {
        if (e.shiftKey) {
            e.preventDefault();
            let $prev = jQuery('#OfferModal input[name="nabl_type_fix"]:checked');
            if (!$prev.length) $prev = jQuery('#OfferModal input[name="nabl_type_fix"]').first();
            $prev.attr('tabindex', '0');
            if ($prev.length) $prev[0].focus();
        } else {
            e.preventDefault();
            let $next = jQuery('#OfferModal input[name="test_at_fix"]:checked');
            if (!$next.length) $next = jQuery('#OfferModal input[name="test_at_fix"]').first();
            $next.attr('tabindex', '0');
            if ($next.length) $next[0].focus();
        }
    }
});

jQuery(document).on('keydown', '#OfferModal input[name="test_at_fix"]', function (e) {
    if (e.key === 'Tab' || e.keyCode === 9) {
        if (e.shiftKey) {
            e.preventDefault();
            let $prev = jQuery('#OfferModal input[name="job_type_fix"]:checked');
            if (!$prev.length) $prev = jQuery('#OfferModal input[name="job_type_fix"]').first();
            $prev.attr('tabindex', '0');
            if ($prev.length) $prev[0].focus();
        } else {
            e.preventDefault();
            let $next = jQuery('#OfferModal #customer_id');
            if ($next.hasClass('select2-hidden-accessible')) {
                $next.next('.select2-container').find('.select2-selection').focus();
            } else {
                $next.focus();
            }
        }
    }
});

jQuery(document).on('keydown', '#OfferModal .select2-selection', function (e) {
    if ((e.key === 'Tab' || e.keyCode === 9) && e.shiftKey) {
        let $select = jQuery(this).closest('.select2-container').prev('select#customer_id');
        if ($select.length) {
            e.preventDefault();
            let $target = jQuery('#OfferModal input[name="test_at_fix"]:checked');
            if (!$target.length) $target = jQuery('#OfferModal input[name="test_at_fix"]').first();
            $target.attr('tabindex', '0');
            if ($target.length) $target[0].focus();
        }
    }
});

/* ---------------------------------------------------------------------
 *  Process Type & Observation Sheet Rules
 * ------------------------------------------------------------------- */
function handleProcessTypeRules(isUserChange = false) {
    let $qtyInput = jQuery('#OfferDetailsForm #quantity');
    $qtyInput.val('1').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
}

function handleTestingTypeProcessTypeRules() {
    let $radios = jQuery('#OfferDetailsForm input[name="process_type_fix"]');
    let $container = jQuery('#process_type_container');

    // Fresh / Repair radios must NOT be readonly
    setRadioReadonly("#OfferDetailsForm input[name='process_type_fix']", false);
    $radios.prop('disabled', false);
    $container.css('pointer-events', 'auto').css('opacity', '1');
    $container.find('.form-check').css('pointer-events', 'auto').css('opacity', '1');

    // Process is RT and readonly using select2 readonly (not disabled)
    let $proc = jQuery('#OfferDetailsForm #type_of_testing_id_fix');
    if (!$proc.val()) {
        $proc.val('RT').trigger('change.select2');
    }
    setSelect2Readonly('#OfferDetailsModal #type_of_testing_id_fix', true);
}

jQuery('#OfferDetailsForm').on('change', '#type_of_testing_id_fix', function () {
    handleTestingTypeProcessTypeRules();
});

jQuery('#OfferDetailsForm').on('change', 'input[name="process_type_fix"]', function () {
    handleProcessTypeRules(true);
});

function filterTestsByNabl() {
    var nablType = jQuery('#OfferModal input[name="nabl_type_fix"]:checked').val();
    const allTestingOptions = [
        { value: "RT", text: "RT" },
        { value: "UT", text: "UT" },
        { value: "MPT", text: "MPT" },
        { value: "DPT", text: "DPT" }
    ];

    let filtered = [...allTestingOptions];
    if (nablType === "NABL") {
        let allowed = (window.locationNablTests || "").split(',').map(s => s.trim().toUpperCase());
        filtered = allTestingOptions.filter(opt => allowed.includes(opt.value));
    }

    let $select = jQuery('#OfferDetailsForm #type_of_testing_id_fix');
    let currentVal = $select.attr('data-target-val') || $select.val();
    $select.removeAttr('data-target-val');
    $select.empty().append('<option value="">Select Type of Test</option>');

    if (currentVal && !filtered.some(opt => opt.value === currentVal)) {
        let isEdit = jQuery('#OfferDetailsModal #form_type').val() === 'edit';
        if (isEdit) {
            let foundInAll = allTestingOptions.find(opt => opt.value === currentVal);
            if (foundInAll) {
                filtered = [...filtered, foundInAll];
            } else {
                filtered = [...filtered, { value: currentVal, text: currentVal }];
            }
        }
    }

    filtered.forEach(opt => {
        $select.append(new Option(opt.text, opt.value));
    });

    if (filtered.some(opt => opt.value === currentVal)) {
        $select.val(currentVal).trigger('change.select2');
    } else {
        $select.val('').trigger('change.select2');
    }
}

function getNextDetailSrNo() {
    let maxSr = 0;
    if (Array.isArray(offer_details_data)) {
        offer_details_data.forEach(function (row) {
            if (row && row.mode !== 'Delete') {
                let sr = parseInt(row.sr_no, 10);
                if (!isNaN(sr) && sr > maxSr) {
                    maxSr = sr;
                }
            }
        });
    }
    return maxSr + 1;
}

jQuery('#OfferDetailsModal').on('show.bs.modal', function () {
    validateCopyButton();
    filterTestsByNabl();
    var formType = jQuery('#OfferDetailsModal').find("#form_type").val();
    if (formType !== 'edit') {
        jQuery('#OfferDetailsModal #sr_no').val(getNextDetailSrNo());
        jQuery('#OfferDetailsModal #repeat_item_row').show();
        resetPendingRepairFields();
        jQuery('#OfferDetailsModal').find("#quantity").removeAttr("min");

        jQuery('#OfferDetailsForm #type_of_testing_id_fix').val('RT').trigger('change.select2');
        jQuery('#OfferDetailsForm #type_of_testing_id_fix').addClass('skip-tab');
        setSelect2Readonly('#OfferDetailsModal #type_of_testing_id_fix', true);
        jQuery('#OfferDetailsModal #type_of_testing_id_fix').next('.select2-container').css('pointer-events', 'none');

        setRadioReadonly("#OfferDetailsForm input[name='process_type_fix']", false);
        jQuery('#OfferDetailsForm input[name="process_type_fix"]').prop('disabled', false);
        jQuery('#process_type_container').css('pointer-events', 'auto').css('opacity', '1');
    } else {
        jQuery('#OfferDetailsForm #type_of_testing_id_fix').addClass('skip-tab');
        setSelect2Readonly('#OfferDetailsModal #type_of_testing_id_fix', true);
        jQuery('#OfferDetailsModal #type_of_testing_id_fix').next('.select2-container').css('pointer-events', 'none');
    }
});

jQuery('#OfferDetailsModal').on('shown.bs.modal', function () {
    validateCopyButton();
    jQuery('#OfferDetailsForm #type_of_testing_id_fix').addClass('skip-tab');
    setSelect2Readonly('#OfferDetailsModal #type_of_testing_id_fix', true);
    jQuery('#OfferDetailsModal #type_of_testing_id_fix').next('.select2-container').css('pointer-events', 'none');
    var formType = jQuery('#OfferDetailsModal').find("#form_type").val();
    if (formType === 'edit') {
        jQuery('#copy_from_sr_no').prop('readonly', true).prop('disabled', true).addClass('skip-tab').attr('tabindex', '-1').val('');
    } else {
        jQuery('#copy_from_sr_no').prop('readonly', false).prop('disabled', false).removeClass('skip-tab').removeAttr('tabindex').val('');
        let currentSr = jQuery('#OfferDetailsModal #sr_no').val();
        if (!currentSr || currentSr === '') {
            jQuery('#OfferDetailsModal #sr_no').val(getNextDetailSrNo());
        }
    }
    setTimeout(() => {
        var parentId = jQuery('#OfferModal').find("#id").val();
        var isParentStored = (parentId && parentId !== "" && parentId != 0 && parentId !== "0");
        var formType = jQuery('#OfferDetailsModal').find("#form_type").val();
        let processType = jQuery('#OfferDetailsModal input[name="process_type_fix"]:checked').val() || 'Fresh';
        let isRepair = processType === 'Repair';

        if (formType === 'edit') {
            var formIndx = jQuery('#OfferDetailsModal').find("#form_index").val();
            if (formIndx !== "") {
                var d = offer_details_data[formIndx];
                let isRowInUse = d && (d.in_use == true || d.in_use == "true" || (d.used_qty && parseFloat(d.used_qty) > 0));
                let isRepairRow = d && ((d.process_type_fix || d.process_type) === 'Repair');

                if (isRepairRow) {
                    setRepairFieldsReadonly(true);
                }

                setSelect2Readonly('#OfferDetailsModal #type_of_testing_id_fix', true);
                setRadioReadonly("#OfferDetailsForm input[name='process_type_fix']", false);

                let focusTarget = isRepairRow ? '#thickness' : (isRowInUse ? '#part_no' : null);
                if (focusTarget) {
                    setTimeout(() => {
                        let $elem = jQuery('#OfferDetailsModal').find(focusTarget);
                        if ($elem.hasClass('select2-hidden-accessible')) {
                            $elem.next('.select2-container').find('.select2-selection').focus();
                        } else {
                            $elem.focus();
                        }
                    }, 50);
                    return;
                }

                if (isRepairRow) {
                    return;
                }
            }
        }

        if (isRepair) {
            setRepairFieldsReadonly(true);
            setTimeout(() => {
                jQuery('#OfferDetailsModal').find('#thickness').focus();
            }, 50);
            return;
        }

        setRepairFieldsReadonly(false);
        if (formType !== 'edit' && window.lastCustomerTestingType) {
            jQuery('#OfferDetailsModal').find('#type_of_testing_id_fix').val(window.lastCustomerTestingType).trigger('change.select2');
        }
        handleTestingTypeProcessTypeRules();
    }, 150);
});

/* ---------------------------------------------------------------------
 *  Detail modal: Job Description -> Part dependency
 * ------------------------------------------------------------------- */
jQuery('#OfferDetailsForm #inward_job_desc_id').on('change', function () {
    let parts = jQuery(this).find(':selected').data('parts');
    let $partSelect = jQuery('#OfferDetailsForm #part_id');
    let previousSelectedPart = $partSelect.val();

    $partSelect.empty().append('<option value="">Select Part No. / Die No.</option>');
    if (parts && parts.length > 0) {
        jQuery.each(parts, function (i, part) {
            let displayText = part.drg_no ? part.part_no + ' - ' + part.drg_no : part.part_no;
            $partSelect.append(
                `<option value="${part.part_id}" data-part_no="${part.part_no}" data-drg_no="${part.drg_no ?? ''}">
                    ${displayText}
                </option>`
            );
        });
    }

    if (previousSelectedPart && $partSelect.find('option[value="' + previousSelectedPart + '"]').length > 0) {
        $partSelect.val(previousSelectedPart).trigger('change');
    } else {
        $partSelect.val('').trigger('change');
    }
});

function suggestInwardPartNo(e, $this) {
    var jobId = jQuery('#OfferDetailsForm #inward_job_desc_id').val() || '';
    commonSuggestionAjax({
        inputElement: $this,
        listSelector: '#inward_part_no_list',
        url: 'get-material_inward_part_no_list',
        responseListKey: 'partNoList',
        extraData: { job_desc_id: jobId }
    });
}

function suggestInwardDrgNo(e, $this) {
    commonSuggestionAjax({
        inputElement: $this,
        listSelector: '#inward_drg_no_list',
        url: 'get-material_inward_drg_no_list',
        responseListKey: 'drgNoList'
    });
}

function suggestInwardProductCode(e, $this) {
    commonSuggestionAjax({
        inputElement: $this,
        listSelector: '#inward_product_code_list',
        url: 'get-material_inward_product_code_list',
        responseListKey: 'productCodeList'
    });
}

function resequenceOfferDetailSrNos() {
    let activeSrNo = 1;
    if (Array.isArray(offer_details_data)) {
        offer_details_data.forEach(function (item) {
            if (item && item.mode !== "Delete") {
                let currentSr = parseInt(item.sr_no, 10);
                if (currentSr !== activeSrNo) {
                    item.sr_no = activeSrNo;
                    if (item.mode !== "Insert") {
                        item.mode = "Update";
                    }
                }
                activeSrNo++;
            }
        });
    }
}

/* ---------------------------------------------------------------------
 *  Detail row remove / edit
 * ------------------------------------------------------------------- */
function removeOfferDetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        let item = offer_details_data[formIndx];
        if ((item.offer_details_id && item.offer_details_id != 0) || (item.offer_detail_id && item.offer_detail_id != 0)) {
            item.mode = "Delete";
        } else {
            offer_details_data.splice(formIndx, 1);
        }
        resequenceOfferDetailSrNos();
        jQuery('#OfferDetailTable tbody').empty();
        fillOfferDetailTable();
    });
}

function editOfferDetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();
    fillOfferDetailsForm(formIndx, rawIndx);
}

function fillOfferDetailsForm(formIndx, rawIndx) {
    let thisForm = jQuery('#OfferDetailsModal');
    var d = offer_details_data[formIndx];

    thisForm.find("#repeat_item").prop('checked', false);

    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    thisForm.find("#offer_details_id").val(d.offer_details_id ?? d.offer_detail_id ?? 0);

    let pType = d.process_type_fix || 'Fresh';
    thisForm.find('input[name="process_type_fix"][value="' + pType + '"]').prop('checked', true);

    thisForm.find("#sr_no").val(d.sr_no ?? "");
    thisForm.find("#type_of_testing_id_fix").val(d.type_of_testing_id_fix || 'RT').trigger('change.select2');
    thisForm.find("#type_of_testing_id_fix").addClass('skip-tab');
    setSelect2Readonly('#OfferDetailsModal #type_of_testing_id_fix', true);
    thisForm.find("#type_of_testing_id_fix").next('.select2-container').css('pointer-events', 'none');
    thisForm.find("#lab_id").val(d.lab_id ?? "");
    thisForm.find("#customer_id_no").val(d.customer_id_no ?? "");
    thisForm.find("#job_desc").val(d.job_desc ?? d.job_description ?? "");
    thisForm.find("#die_no").val(d.die_no ?? "");
    thisForm.find("#heat_no").val(d.heat_no ?? "");
    thisForm.find("#drg_no").val(d.drg_no ?? "");
    thisForm.find("#material_id").val(zeroToEmpty(d.material_id)).trigger('change.select2');
    thisForm.find("#thickness").val(d.thickness ?? d.thickness_mm ?? "");
    thisForm.find("#quantity").val(d.quantity).prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
    thisForm.find("#remark").val(d.remark ?? "");

    thisForm.modal('show');
}

/* ---------------------------------------------------------------------
 *  Grid rebuild
 * ------------------------------------------------------------------- */
function fillOfferDetailTable() {
    resequenceOfferDetailSrNos();
    let tbody = jQuery('#OfferDetailTable tbody');
    tbody.empty();
    let totalQty = 0;
    let visibleRowCount = 0;

    if (offer_details_data && offer_details_data.length > 0) {
        for (let key in offer_details_data) {
            let item = offer_details_data[key];
            if (item.mode == "Delete") continue;
            visibleRowCount++;
            let formIndx = offer_details_data.indexOf(item);

            let process_type = item.process_type_fix || 'Fresh';
            let process_name = item.process_type_name || item.type_of_testing_id_fix || '';
            let lab_id = item.lab_id || '';
            let sr_no = item.sr_no || '';
            let customer_id_no = item.customer_id_no || '';
            let job_desc = item.job_desc || item.job_description || '';
            let die_no = item.die_no || '';
            let heat_no = item.heat_no || '';
            let drg_no = item.drg_no || '';
            let mat = item.material_name || '';
            let thick = item.thickness || item.thickness_mm || '';
            let qty = item.quantity;
            let rem = item.remark || '';

            totalQty += parseInt(qty) || 0;

            let in_use = (item.in_use == true || item.in_use == "true" || (item.used_qty && parseFloat(item.used_qty) > 0)) ? true : false;
            let actionBtn = '';

            actionBtn = in_use ? DetailsActionDropdown('editOfferDetails') : DetailsActionDropdown('editOfferDetails', 'removeOfferDetails');


            let tblHtml = `<tr>
                <td>${actionBtn}<input type="hidden" name="form_indx" value="${formIndx}"/></td>
                <td>${sr_no}</td>
                <td>${process_type}</td>
                <td>${process_name}</td>
                <td>${lab_id}</td>
                <td>${customer_id_no}</td>
                <td>${job_desc}</td>
                <td>${die_no}</td>
                <td>${heat_no}</td>
                <td>${drg_no}</td>
                <td>${mat}</td>
                <td>${thick}</td>
                <td>${qty}</td>
                <td>${rem}</td>
            </tr>`;
            tbody.append(tblHtml);
        }
    }

    if (visibleRowCount == 0) {
        tbody.append('<tr><td colspan="14" id="noDetails" class="text-center">No Offer Details Added</td></tr>');
    }

    jQuery('#OfferModal #total_qty').val(totalQty > 0 ? totalQty : '');
    updateParentRadiosLockState();
}

/* ---------------------------------------------------------------------
 *  Detail modal lifecycle
 * ------------------------------------------------------------------- */
jQuery('#OfferDetailsForm').on('reset', function () {
    jQuery('#copy_from_sr_no').val('');
    setTimeout(function () {
        jQuery('#OfferDetailsForm input[name="process_type_fix"][value="Fresh"]').prop('checked', true);
        handleTestingTypeProcessTypeRules();
        handleProcessTypeRules();
    }, 50);
});

jQuery('#OfferDetailsModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#OfferDetailsModal');
    thisModal.find('#copy_from_sr_no').val('').prop('readonly', false).prop('disabled', false).removeClass('skip-tab').removeAttr('tabindex');
    thisModal.find('#sr_no').val('');
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    thisModal.find("#offer_details_id").val(0);
    thisModal.find("#quantity").removeAttr("min");
    thisModal.find("#repeat_item").prop('checked', false);
    thisModal.find("#repeat_item_row").show();
    setSelect2Readonly(thisModal.find('#type_of_testing_id_fix'), false);
    thisModal.find('#type_of_testing_id_fix').removeClass('skip-tab');
    setRadioReadonly("#OfferDetailsForm input[name='process_type_fix']", false);
    setSelect2Readonly(thisModal.find('#type_of_job_id'), false);
    thisModal.find('#type_of_job_id').removeClass('skip-tab');
    setSelect2Readonly(thisModal.find('#inward_job_desc_id'), false);
    thisModal.find('#inward_job_desc_id').removeClass('skip-tab');
    thisModal.find('#job_desc').prop('readonly', false).removeClass('skip-tab').val('');
    setSelect2Readonly(thisModal.find('#area_of_coverage_id'), false);
    thisModal.find('#area_of_coverage_id').removeClass('skip-tab');
    resetPendingRepairFields();
    jQuery('#OfferDetailsForm').trigger("reset");
    jQuery('#OfferDetailsForm input[name="process_type_fix"][value="Fresh"]').prop('checked', true);

    handleTestingTypeProcessTypeRules();
    handleProcessTypeRules();
    jQuery('#inward_part_no_list').empty();
    jQuery('#inward_drg_no_list').empty();
    jQuery('#inward_product_code_list').empty();
    this.dataset.customHideFocus = 'true';
    const input = jQuery("#OfferModal").find("input[name='sample_drawn_by']");
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 200);
    }
});

// Copy data from specified Sr. No. on Blur / Change
jQuery(document).on('blur change', '#copy_from_sr_no', function () {
    let copySrNo = jQuery(this).val().trim();
    if (!copySrNo) return;

    let targetRow = offer_details_data.find(r => r.mode !== 'Delete' && String(r.sr_no).trim() === copySrNo);
    if (targetRow) {
        let currentSrNo = jQuery('#OfferDetailsModal #sr_no').val();
        let form = jQuery('#OfferDetailsForm');

        form.find('#lab_id').val(targetRow.lab_id || '');
        form.find('#customer_id_no').val(targetRow.customer_id_no || '');
        form.find('#job_desc').val(targetRow.job_desc || targetRow.job_description || '');
        form.find('#die_no').val(targetRow.die_no || '');
        form.find('#heat_no').val(targetRow.heat_no || '');
        form.find('#drg_no').val(targetRow.drg_no || '');
        if (targetRow.material_id) {
            form.find('#material_id').val(targetRow.material_id).trigger('change.select2');
        }
        form.find('#thickness').val(targetRow.thickness || targetRow.thickness_mm || '');
        form.find('#remark').val(targetRow.remark || '');

        // Maintain latest Sr. No. (Do not overwrite with copied Sr. No.)
        jQuery('#OfferDetailsModal #sr_no').val(currentSrNo);

        // Clear Copy From Sr. No. input after copy
        jQuery(this).val('');
    } else {
        // Clear Copy From Sr. No. input if not found
        jQuery(this).val('');
    }
});

// Tab key navigation from Copy From Sr. No. directly to Sr. No.
jQuery(document).on('keydown', '#copy_from_sr_no', function (e) {
    if (e.key === 'Tab' && !e.shiftKey) {
        e.preventDefault();
        jQuery(this).trigger('blur');
        setTimeout(() => {
            jQuery('#OfferDetailsModal #sr_no').focus().select();
        }, 50);
    }
});

/* ---------------------------------------------------------------------
 *  Detail form submit (add / update a row in the in-memory grid)
 * ------------------------------------------------------------------- */
jQuery('#OfferDetailsForm').on('submit', function (e) {
    e.preventDefault();
    let form = this;
    let thisModal = jQuery('#OfferDetailsModal');

    if (!form.checkValidity()) {
        e.stopPropagation();
        jQuery(form).addClass('was-validated');
        return;
    }

    var qty = parseInt(jQuery('#OfferDetailsForm #quantity').val()) || 0;
    if (qty <= 0) { toastr.error('Enter Quantity greater than 0.'); return; }

    let formValue = {};
    jQuery.each(jQuery('#OfferDetailsForm').serializeArray(), function (i, f) { formValue[f.name] = f.value; });

    let enteredSrNo = (formValue.sr_no || '').toString().trim();
    if (enteredSrNo !== '') {
        let editIndex = formValue.form_type === 'edit' ? parseInt(formValue.form_index, 10) : -1;
        let isDuplicate = offer_details_data.some(function (row, idx) {
            if (!row || row.mode === 'Delete') return false;
            if (editIndex >= 0 && idx === editIndex) return false;
            return (row.sr_no || '').toString().trim() === enteredSrNo;
        });

        if (isDuplicate) {
            toastr.error('Duplicate Sr. No. Found.');
            jQuery('#OfferDetailsForm #sr_no').focus();
            return;
        }
    }

    formValue.process_type_fix = jQuery('#OfferDetailsModal input[name="process_type_fix"]:checked').val() || 'Fresh';
    formValue.process_type = formValue.process_type_fix;
    formValue.process_type_name = jQuery('#OfferDetailsModal #type_of_testing_id_fix option:selected').val() ? jQuery('#OfferDetailsModal #type_of_testing_id_fix option:selected').text() : (formValue.type_of_testing_id_fix || '');
    formValue.material_name = jQuery('#OfferDetailsModal #material_id option:selected').val() ? jQuery('#OfferDetailsModal #material_id option:selected').text() : '';

    if (formValue.form_type == "edit") {
        formValue.mode = (formValue.offer_details_id && formValue.offer_details_id != 0) ? "Update" : "Insert";
        offer_details_data[formValue.form_index] = {
            ...offer_details_data[formValue.form_index],
            ...formValue
        };
        fillOfferDetailTable();
        toastSuccess("Record Updated.");
        thisModal.modal('hide');
    } else {
        formValue.mode = "Insert";
        formValue.offer_details_id = 0;
        offer_details_data.push(formValue);
        fillOfferDetailTable();
        toastSuccess("Record Inserted.");

        // ADD Mode: LNR behavior - keep all details, only increment sr_no (+1), modal stays open
        jQuery('#OfferDetailsModal #sr_no').val(getNextDetailSrNo());
        jQuery('#copy_from_sr_no').val('');
        jQuery('#OfferDetailsForm #quantity').val('1').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');

        // Process remains RT and readonly
        jQuery('#OfferDetailsForm #type_of_testing_id_fix').val('RT').trigger('change.select2');
        jQuery('#OfferDetailsForm #type_of_testing_id_fix').addClass('skip-tab');
        setSelect2Readonly('#OfferDetailsModal #type_of_testing_id_fix', true);
        jQuery('#OfferDetailsModal #type_of_testing_id_fix').next('.select2-container').css('pointer-events', 'none');

        setTimeout(function () {
            $(form).removeClass('was-validated');
            $(form).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            jQuery('#OfferDetailsForm').find('.error').removeClass('error');
            jQuery('#OfferDetailsForm #sr_no').focus().select();
        }, 150);
    }
});

/* ---------------------------------------------------------------------
 *  Extra Details Sync Helpers (Main Form Hidden Fields <-> Modal)
 * ------------------------------------------------------------------- */
function syncExtraDetailsToMainHidden() {
    jQuery('#main_test_witness_tpi_fix').val(jQuery('#OfferExtraDetailsModal #test_witness_tpi_fix').val() || '');
    jQuery('#main_tpi_person').val(jQuery('#OfferExtraDetailsModal #tpi_person').val() || '');
    jQuery('#main_test_items_condition').val(jQuery('#OfferExtraDetailsModal #test_items_condition').val() || '');
    jQuery('#main_public_domain_agree_fix').val(jQuery('#OfferExtraDetailsModal #public_domain_agree_fix').val() || '');
    jQuery('#main_conformity_statement_fix').val(jQuery('#OfferExtraDetailsModal #conformity_statement_fix').val() || '');
    jQuery('#main_test_methods_defined_fix').val(jQuery('#OfferExtraDetailsModal #test_methods_defined_fix').val() || '');
    jQuery('#main_laboratory_capability_fix').val(jQuery('#OfferExtraDetailsModal #laboratory_capability_fix').val() || '');
    jQuery('#main_appropriate_test_method_fix').val(jQuery('#OfferExtraDetailsModal #appropriate_test_method_fix').val() || '');
    jQuery('#main_pertinent_discussions').val(jQuery('#OfferExtraDetailsModal #pertinent_discussions').val() || '');
    jQuery('#main_deviation_intimate').val(jQuery('#OfferExtraDetailsModal #deviation_intimate').val() || '');
    jQuery('#main_contract_review_repeated_fix').val(jQuery('#OfferExtraDetailsModal #contract_review_repeated_fix').val() || '');
}

function syncMainHiddenToExtraDetailsModal() {
    let tpiFix = jQuery('#main_test_witness_tpi_fix').val() || '';
    jQuery('#OfferExtraDetailsModal #test_witness_tpi_fix').val(tpiFix).trigger('change');
    jQuery('#OfferExtraDetailsModal #tpi_person').val(jQuery('#main_tpi_person').val() || '');
    if (tpiFix === 'Yes') {
        jQuery('#OfferExtraDetailsModal #tpi_person').prop('readonly', false).removeClass('skip-tab').css('pointer-events', 'auto');
    } else {
        jQuery('#OfferExtraDetailsModal #tpi_person').prop('readonly', true).addClass('skip-tab').css('pointer-events', 'none');
    }
    jQuery('#OfferExtraDetailsModal #test_items_condition, #OfferExtraDetailsModal #test_item_condition').val(jQuery('#main_test_items_condition').val() || '');
    jQuery('#OfferExtraDetailsModal #public_domain_agree_fix').val(jQuery('#main_public_domain_agree_fix').val() || '').trigger('change');
    jQuery('#OfferExtraDetailsModal #conformity_statement_fix').val(jQuery('#main_conformity_statement_fix').val() || '').trigger('change');
    jQuery('#OfferExtraDetailsModal #test_methods_defined_fix').val(jQuery('#main_test_methods_defined_fix').val() || '').trigger('change');
    jQuery('#OfferExtraDetailsModal #laboratory_capability_fix').val(jQuery('#main_laboratory_capability_fix').val() || '').trigger('change');
    jQuery('#OfferExtraDetailsModal #appropriate_test_method_fix').val(jQuery('#main_appropriate_test_method_fix').val() || '').trigger('change');
    jQuery('#OfferExtraDetailsModal #pertinent_discussions').val(jQuery('#main_pertinent_discussions').val() || '');
    jQuery('#OfferExtraDetailsModal #deviation_intimate').val(jQuery('#main_deviation_intimate').val() || '');
    jQuery('#OfferExtraDetailsModal #contract_review_repeated_fix').val(jQuery('#main_contract_review_repeated_fix').val() || '').trigger('change');
}

jQuery(document).on('click', '#OfferExtraDetailsModal #add_extra_details_btn', function () {
    syncExtraDetailsToMainHidden();
    toastSuccess("Extra Details Inserted.");
    jQuery('#OfferExtraDetailsModal').modal('hide');
    setTimeout(() => {
        jQuery('#OfferModal #special_note').focus();
    }, 200);
});

jQuery(document).on('click', '#extra_btn', function () {
    syncMainHiddenToExtraDetailsModal();
});

jQuery('#OfferExtraDetailsModal').on('hide.bs.modal', function () {
    syncExtraDetailsToMainHidden();
});

jQuery('#OfferExtraDetailsModal').on('show.bs.modal', function () {
    syncMainHiddenToExtraDetailsModal();
});

/* ---------------------------------------------------------------------
 *  Main form submit (assemble JSON + POST)
 * ------------------------------------------------------------------- */
jQuery('#commonOfferForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#OfferModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;

    var dateValue = document.getElementById("offer_date").value.trim();
    if (!isValidDate(dateValue)) {
        toastr.error("Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#OfferModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#OfferModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    validateChallanNoDate();

    var challanDate = (jQuery('#OfferModal #challan_date').val() || '').trim();
    if (challanDate && !isValidDate(challanDate)) {
        toastr.error("Enter A Valid Challan Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#OfferModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity() || jQuery(form).find('.is-invalid').length > 0) {
        e.stopPropagation();
        jQuery(form).addClass('was-validated');
        let $firstInvalid = jQuery(form).find(':invalid, .is-invalid').first();
        if ($firstInvalid.length) {
            $firstInvalid.focus();
        }
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#OfferModal').find('#submitbtn').prop('disabled', false);
        return;
    }


    if (!validateOfferDateAgainstReports()) {
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#OfferModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!validateDelDateAgainstOfferDate()) {
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#OfferModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    // syncExtraDetailsToMainHidden();



    var formIdnew = jQuery('#OfferModal').find('#commonOfferForm').find('#id').val();
    var formUrl = formIdnew != undefined && formIdnew != "" ? "update-offer" : "store-offer";
    var data = new FormData(form);

    // Ensure all 11 Extra Detail fields are explicitly included in FormData
    const extraFields = [
        'test_witness_tpi_fix', 'tpi_person', 'test_items_condition',
        'public_domain_agree_fix', 'conformity_statement_fix', 'test_methods_defined_fix',
        'laboratory_capability_fix', 'appropriate_test_method_fix', 'pertinent_discussions',
        'deviation_intimate', 'contract_review_repeated_fix'
    ];
    extraFields.forEach(function (f) {
        data.set(f, jQuery('#OfferModal').find('#main_' + f).val() || '');
    });

    data.append('offer_details_data', JSON.stringify(offer_details_data ?? []));
    data.append('_token', jQuery('meta[name="csrf-token"]').attr('content'));

    let validData = offer_details_data.filter(item => item.mode !== "Delete");
    if (validData.length == 0) {
        toastr.error('Add At Least One Offer Detail.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#OfferModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    jQuery.ajax({
        type: 'POST',
        url: formUrl,
        data: data,
        contentType: false,
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                if (formIdnew != undefined && formIdnew != "") {
                    let redirectFn = function () {
                        if (typeof pageRelod !== 'undefined' && pageRelod == 'Yes') {
                            setTimeout(function () {
                                let seqInput = $('#offer_date');
                                if (seqInput.length && !seqInput.prop('readonly')) {
                                    seqInput.focus().select();
                                }
                            }, 50);
                        } else {
                            window.location.reload();
                        }
                    };
                    if (typeof pageRelod !== 'undefined' && pageRelod == 'Yes') {
                        const form = document.getElementById("commonOfferForm");
                        if (form) {
                            form.classList.remove('was-validated');
                        }
                    }
                    if (data.url != "") { toastSuccessPreview(data.response_message, data.url, redirectFn); }
                    else { toastSuccess(data.response_message, redirectFn); }
                } else {
                    function nextFn() {
                        resetParentForm(true);
                        setTimeout(function () {
                            focusInitialOfferField();
                        }, 100);
                    }
                    if (data.url != "") { toastSuccessPreview(data.response_message, data.url, nextFn); }
                    else { toastSuccess(data.response_message, nextFn); }
                }
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                jQuery('#OfferModal').find('#submitbtn').prop('disabled', false);
            } else {
                toastr.error(data.response_message);
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                jQuery('#OfferModal').find('#submitbtn').prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                let errors = xhr.responseJSON.errors;
                let errorMsg = '';
                jQuery.each(errors, function (key, value) { errorMsg += value + '\n'; });
                toastr.error(errorMsg);
            } else {
                toastr.error('Something went wrong. Please try again.');
            }
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#OfferModal').find('#submitbtn').prop('disabled', false);
        }
    });
});

/* ---------------------------------------------------------------------
 *  Auto-number + sequence duplicate check
 * ------------------------------------------------------------------- */
function getLatestOfferNo(customerId = '') {
    if (!customerId) {
        customerId = jQuery('#OfferModal').find('#customer_id').val();
    }
    if (!customerId) {
        jQuery('#offer_no').val('');
        jQuery('#offer_sequence').val('');
        return;
    }
    jQuery.ajax({
        url: "get-latest_offer_number",
        type: 'GET',
        headers: headerOpt,
        data: { customer_id: customerId },
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#offer_date').val(currentDate);
                jQuery('#offer_no').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#offer_sequence').val(data.number);
            } else {
                console.log(data.response_message);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('Failed To Get Latest Offer No.!');
        }
    });
}

// check sequence number duplication
jQuery('#commonOfferForm').find('#offer_sequence').on('change', function () {
    checkSequence();
});

function checkSequence() {
    let thisForm = jQuery('#commonOfferForm');
    if (thisForm.find('#offer_sequence').prop('readonly')) return;
    let val = thisForm.find('#offer_sequence').val();
    let customerId = thisForm.find('#customer_id').val();

    if (!customerId) {
        toastr.error('Please Select Customer First.');
        thisForm.find('#offer_sequence').val('');
        return;
    }

    if (val > 0 == false) {
        toastr.error('Enter Valid Offer No.');
        jQuery('#offer_sequence').parent().parent().parent('div.control-group').addClass('error');
        jQuery('#offer_sequence').focus();
        jQuery('#offer_sequence').val('');
    } else {
        jQuery('#OfferModal').find('#submitbtn').prop('disabled', true);
        jQuery('#offer_sequence').addClass('file-loader');
        jQuery('#offer_sequence').parent().parent().parent('div.control-group').removeClass('error');

        var urL = "check-offer_number_duplication?for=add&offer_sequence=" + val + "&customer_id=" + customerId;
        var formId = jQuery('#commonOfferForm').find('input[name="id"]').val();
        if (formId !== undefined && formId != "") {
            urL = "check-offer_number_duplication?for=edit&offer_sequence=" + val + "&id=" + formId + "&customer_id=" + customerId;
        }

        jQuery.ajax({
            url: urL,
            type: 'GET',
            headers: headerOpt,
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery('#offer_sequence').removeClass('file-loader');
                if (data.response_code == 0) {
                    toastr.error(data.response_message);
                    jQuery('#commonOfferForm #offer_sequence').val('');
                    const input = document.getElementById('offer_sequence'); input?.focus();
                    jQuery('#OfferModal').find('#submitbtn').prop('disabled', false);
                } else {
                    jQuery('#commonOfferForm #offer_no').val(data.latest_no);
                    jQuery('#commonOfferForm #offer_sequence').val(val);
                    jQuery('#OfferModal').find('#submitbtn').prop('disabled', false);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery('#OfferModal').find('#submitbtn').prop('disabled', false);
                jQuery('#offer_sequence').removeClass('file-loader');
                try {
                    var errMessage = JSON.parse(jqXHR.responseText);
                    if (errMessage.errors) {
                        if (typeof validator !== 'undefined') {
                            validator.showErrors(errMessage.errors);
                        } else {
                            let errorMsg = '';
                            jQuery.each(errMessage.errors, function (key, value) { errorMsg += value + '\n'; });
                            toastr.error(errorMsg);
                        }
                    } else if (jqXHR.status == 401) {
                        toastr.error(jqXHR.statusText);
                    } else {
                        toastr.error('Something went wrong!');
                        console.log(JSON.parse(jqXHR.responseText));
                    }
                } catch (e) {
                    toastr.error('Something went wrong!');
                }
            }
        });
    }
}

// Customer change handler for Customer-wise Offer Number & LNR
jQuery('#OfferModal').on('change', '#customer_id', function () {
    var customerId = jQuery(this).val();
    var formId = jQuery('#OfferModal').find('#id').val();
    if (!formId) {
        if (customerId) {
            getLatestOfferNo(customerId);
            getLNRData(customerId);
        } else {
            jQuery('#OfferModal').find('#offer_no').val('');
            jQuery('#OfferModal').find('#offer_sequence').val('');
        }
    }
});

// Get Last Note Reset (LNR) Data (1/2/3/4 wise)
function getLNRData(customerId = '') {
    var isInitial = !customerId;
    if (!customerId) {
        customerId = jQuery('#OfferModal').find('#customer_id').val();
    }
    var nablTypeFix = jQuery('#OfferModal').find('input[name="nabl_type_fix"]:checked').val();
    var jobTypeFix = jQuery('#OfferModal').find('input[name="job_type_fix"]:checked').val();
    var testAtFix = jQuery('#OfferModal').find('input[name="test_at_fix"]:checked').val();

    jQuery.ajax({
        url: "get-offer_lnr_data",
        type: 'GET',
        headers: headerOpt,
        data: {
            customer_id: customerId,
            nabl_type_fix: nablTypeFix,
            job_type_fix: jobTypeFix,
            test_at_fix: testAtFix
        },
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                if (isInitial && data.location_lnr_radios != null) {
                    var locLnr = data.location_lnr_radios;
                    if (locLnr.nabl_type_fix) {
                        let targetNabl = locLnr.nabl_type_fix;
                        if (typeof window.locationNablDefault !== 'undefined' && window.locationNablDefault === 'Non NABL') {
                            targetNabl = 'Non NABL';
                        }
                        jQuery('#OfferModal').find('input[name="nabl_type_fix"][value="' + targetNabl + '"]').prop('checked', true);
                    }
                    if (locLnr.test_at_fix) {
                        jQuery('#OfferModal').find('input[name="test_at_fix"][value="' + locLnr.test_at_fix + '"]').prop('checked', true);
                    }
                    if (locLnr.job_type_fix) {
                        jQuery('#OfferModal').find('input[name="job_type_fix"][value="' + locLnr.job_type_fix + '"]').prop('checked', true);
                    }
                    filterTestsByNabl();
                    focusInitialOfferField();
                }
                if (data.lnr_data != null) {
                    var lnr = data.lnr_data;
                    // Auto-fill OfferExtraDetails Main Hidden fields & Modal
                    jQuery('#main_test_witness_tpi_fix').val(lnr.test_witness_tpi_fix ?? "");
                    jQuery('#main_tpi_person').val(lnr.tpi_person ?? "");
                    jQuery('#main_test_items_condition').val(lnr.test_items_condition ?? "");
                    jQuery('#main_public_domain_agree_fix').val(lnr.public_domain_agree_fix ?? "");
                    jQuery('#main_conformity_statement_fix').val(lnr.conformity_statement_fix ?? "");
                    jQuery('#main_test_methods_defined_fix').val(lnr.test_methods_defined_fix ?? "");
                    jQuery('#main_laboratory_capability_fix').val(lnr.laboratory_capability_fix ?? "");
                    jQuery('#main_appropriate_test_method_fix').val(lnr.appropriate_test_method_fix ?? "");
                    jQuery('#main_pertinent_discussions').val(lnr.pertinent_discussions ?? "");
                    jQuery('#main_deviation_intimate').val(lnr.deviation_intimate ?? "");
                    jQuery('#main_contract_review_repeated_fix').val(lnr.contract_review_repeated_fix ?? "");
                    syncMainHiddenToExtraDetailsModal();
                } else {
                    jQuery('#commonOfferForm input[id^="main_"]').val('');
                    syncMainHiddenToExtraDetailsModal();
                }
                if (data.lnr_detail_testing_type) {
                    window.lastCustomerTestingType = data.lnr_detail_testing_type;
                } else {
                    window.lastCustomerTestingType = null;
                }
            }
        },
        error: function (jqXHR) {
            console.log('Error fetching LNR data');
        }
    });
}

function lockParentRadios(lock) {
    if (typeof window.locationNablDefault !== 'undefined' && window.locationNablDefault === 'Non NABL') {
        setRadioReadonly("input[name='nabl_type_fix']", true);
    } else {
        setRadioReadonly("input[name='nabl_type_fix']", lock);
    }
    setRadioReadonly("input[name='test_at_fix']", lock);
    setRadioReadonly("input[name='job_type_fix']", lock);
}

function updateParentRadiosLockState() {
    var parentId = jQuery('#OfferModal').find('#id').val();
    let isAdd = (parentId == "" || parentId == undefined || parentId == 0);

    if (!isAdd) {
        setSelect2Readonly(jQuery('#OfferModal #customer_id'), true);
        jQuery('#OfferModal #customer_id').addClass('skip-tab');
        lockParentRadios(true);
        return;
    }

    let hasRepairDetail = offer_details_data.some(function (item) {
        return item.mode !== "Delete" && ((item.process_type_fix || item.process_type) === 'Repair');
    });
    let parentInUse = offer_details_data.some(function (item) {
        return item.mode !== "Delete" && (item.in_use == true || item.in_use == "true" || (item.used_qty && parseFloat(item.used_qty) > 0));
    });

    if (hasRepairDetail || parentInUse) {
        setSelect2Readonly(jQuery('#OfferModal #customer_id'), true);
        jQuery('#OfferModal #customer_id').addClass('skip-tab');
        lockParentRadios(true);
    } else {
        setSelect2Readonly(jQuery('#OfferModal #customer_id'), false);
        jQuery('#OfferModal #customer_id').removeClass('skip-tab');
        lockParentRadios(false);
    }
}

function focusInitialOfferField() {
    let modal = jQuery('#OfferModal');
    ['nabl_type_fix', 'job_type_fix', 'test_at_fix'].forEach(function (name) {
        let $group = modal.find('input[name="' + name + '"]');
        $group.attr('tabindex', '-1');
        let $chk = $group.filter(':checked');
        if (!$chk.length) $chk = $group.first();
        $chk.attr('tabindex', '0');
    });
    let $target = modal.find('input[name="nabl_type_fix"]:checked');
    if (!$target.length) $target = modal.find('input[name="nabl_type_fix"]').first();
    setTimeout(() => {
        if ($target.length) $target[0].focus();
    }, 30);
}

jQuery('#CustomerModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('customer_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});
jQuery('#TypeOfJobModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('type_of_job_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});
jQuery('#JobDescriptionModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('job_desc') || document.getElementById('inward_job_desc_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});
jQuery('#MaterialModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('material_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});
jQuery('#AreaOfCoverageModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('area_of_coverage_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});
jQuery('#ProcedureReferenceModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('procedure_ref_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});
jQuery('#EvaluationAsPerModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('evaluation_as_per_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});
jQuery('#AcceptanceStandardModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('acceptance_standard_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});

jQuery('#PartModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('part_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});

function validatePoNoDate() {
    var poNo = jQuery('#po_no').val() ? jQuery('#po_no').val().trim() : '';
    var poDate = jQuery('#po_date').val() ? jQuery('#po_date').val().trim() : '';

    if (poNo !== '') {
        jQuery('#po_date').prop('required', true);
    } else {
        jQuery('#po_date').prop('required', false).removeClass('is-invalid');
    }

    if (poDate !== '') {
        jQuery('#po_no').prop('required', true);
    } else {
        jQuery('#po_no').prop('required', false).removeClass('is-invalid');
    }
}

jQuery(document).on('change', '#po_no, #po_date', function () {
    validatePoNoDate();
});

// Also trigger on reset/loading
jQuery('#OfferModal').on('shown.bs.modal', function () {
    validatePoNoDate();
    validateCopyButton();
});

// Add Copy logic From Here 
function renderCopyOfferTable(data) {
    var $table = jQuery('#CopyOfferDetailsTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().clear().destroy();
    }

    var tbody = $table.find('tbody');
    tbody.empty();

    jQuery.each(data, function (index, item) {
        var tr = '<tr>';
        tr += '<td><input class="form-check-input" type="radio" name="copy_offer_detail_radio" value="' + index + '"></td>';
        tr += '<td>' + (item.inward_no || '') + '</td>';
        var dateStr = item.inward_date || '';
        if (dateStr && dateStr.indexOf('-') !== -1) {
            var parts = dateStr.split('-');
            if (parts.length === 3) {
                dateStr = parts[2] + '/' + parts[1] + '/' + parts[0];
            }
        }
        tr += '<td>' + dateStr + '</td>';
        tr += '<td>' + (item.part_no || item.part_name || '') + '</td>';
        tr += '<td>' + (item.drg_no || '') + '</td>';
        tr += '<td>' + (item.heat_no || '') + '</td>';
        tr += '<td>' + (item.rt_no || '') + '</td>';
        tr += '<td>' + (item.product_code || '') + '</td>';
        tr += '<td>' + (item.thickness || '') + '</td>';
        tr += '</tr>';
        tbody.append(tr);
    });

    var $newTable = $table.DataTable({
        "bDestroy": true,
        "ordering": true,
        "order": [],
        "oLanguage": {
            "sEmptyTable": "No records found!",
            "sSearch": "Search :"
        },
        dom: 'lrtip',
        "sScrollX": true,
        "sScrollX": "100%",
        "sScrollXInner": "110%",
        "bScrollCollapse": true,
    });
    if (typeof fixDataTableColumnsUntilAdjusted === 'function') {
        fixDataTableColumnsUntilAdjusted($newTable);
    }
}

function validateCopyButton() {
    var formType = jQuery('#OfferDetailsModal').find("#form_type").val();
    var detailId = jQuery('#OfferDetailsModal').find("#offer_details_id").val();
    var isSavedDetailEdit = formType === 'edit' || (detailId && detailId !== '0');
    var processType = jQuery('#OfferDetailsModal input[name="process_type_fix"]:checked').val() || 'Fresh';

    // In EDIT mode or REPAIR case: always keep Past Inward button disabled
    if (isSavedDetailEdit || processType === 'Repair') {
        jQuery('#copyMaterialBtn').prop('disabled', true);
        window.copyOfferDetailsData = [];
        return;
    }

    // In ADD mode: check if all required filter fields are selected
    var customer = jQuery('#customer_id').val();
    var jobTypeCasting = jQuery('input[name="job_type_fix"]:checked').val();
    var nablType = jQuery('input[name="nabl_type_fix"]:checked').val();
    var typeOfTest = jQuery('#OfferDetailsModal #type_of_testing_id_fix').val() || jQuery('#type_of_testing_id_fix').val();

    if (!customer || !jobTypeCasting || !typeOfTest) {
        jQuery('#copyMaterialBtn').prop('disabled', true);
        window.copyOfferDetailsData = [];
        return;
    }

    // Query backend to verify if past offer records actually exist
    jQuery.ajax({
        url: 'get-old-offer-details',
        type: 'POST',
        dataType: 'json',
        data: {
            customer: customer,
            job_type_casting: jobTypeCasting,
            nabl_type: nablType,
            type_of_test: typeOfTest,
            _token: jQuery('input[name="_token"]').val()
        },
        success: function (response) {
            if (response && (response.status === 'success' || response.response_code == 1) && response.data && response.data.length > 0) {
                jQuery('#copyMaterialBtn').prop('disabled', false);
                window.copyOfferDetailsData = response.data;
            } else {
                jQuery('#copyMaterialBtn').prop('disabled', true);
                window.copyOfferDetailsData = [];
            }
        },
        error: function () {
            jQuery('#copyMaterialBtn').prop('disabled', true);
            window.copyOfferDetailsData = [];
        }
    });
}

jQuery(document).on('change change.select2 input', '#customer_id, input[name="job_type_fix"], #type_of_job_id, #inward_job_desc_id, #job_desc, #type_of_testing_id_fix', function () {
    validateCopyButton();
});

jQuery('#OfferDetailsForm').on('reset', function () {
    setTimeout(function () {
        validateCopyButton();
    }, 100);
});

jQuery(document).on('click', '#copyMaterialBtn', function (e) {
    e.preventDefault();

    if (window.copyOfferDetailsData && window.copyOfferDetailsData.length > 0) {
        renderCopyOfferTable(window.copyOfferDetailsData);
        jQuery('#CopyOfferDetailsModal').modal('show');
        return;
    }

    var copyBtn = jQuery(this);
    var originalText = copyBtn.text();
    copyBtn.prop('disabled', true);

    jQuery.ajax({
        url: 'get-old-offer-details',
        type: 'POST',
        dataType: 'json',
        data: {
            customer: jQuery('#customer_id').val(),
            job_type_casting: jQuery('input[name="job_type_fix"]:checked').val(),
            nabl_type: jQuery('input[name="nabl_type_fix"]:checked').val(),
            type_of_test: jQuery('#type_of_testing_id_fix').val(),
            _token: jQuery('input[name="_token"]').val()
        },
        success: function (response) {
            if (response && (response.status === 'success' || response.response_code == 1) && response.data && response.data.length > 0) {
                window.copyOfferDetailsData = response.data;
                renderCopyOfferTable(response.data);
                jQuery('#CopyOfferDetailsModal').modal('show');
            } else {
                window.copyOfferDetailsData = [];
                copyBtn.prop('disabled', true);
                toastr.info('No past inward records found for this customer, job type and test type.');
            }
        },
        error: function () {
            window.copyOfferDetailsData = [];
            copyBtn.prop('disabled', true);
        },
        complete: function () {
            copyBtn.text(originalText);
        }
    });
});

jQuery('#CopyOfferDetailsModal').on('shown.bs.modal', function () {
    let $table = jQuery('#CopyOfferDetailsTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().columns.adjust().draw();
        if (typeof initColumnSearch === 'function') {
            initColumnSearch('#CopyOfferDetailsTable', [0], 'common_search');
        }
    }
});

jQuery(document).on('click', '#submitCopyOfferDetailsBtn', function () {
    var selectedRadio = jQuery('input[name="copy_offer_detail_radio"]:checked');
    if (selectedRadio.length === 0) {
        toastr.warning('Please Select At Least One Record.');
        return;
    }

    var index = selectedRadio.val();
    var data = window.copyOfferDetailsData[index];

    if (data) {
        if (data.type_of_job_id) { jQuery('#type_of_job_id').val(data.type_of_job_id).trigger('change'); }
        if (data.inward_job_desc_id) { jQuery('#inward_job_desc_id').val(data.inward_job_desc_id).trigger('change'); }
        if (data.job_desc_id) { jQuery('#inward_job_desc_id').val(data.job_desc_id).trigger('change'); }
        if (data.job_desc || data.job_description) { jQuery('#job_desc').val(data.job_desc || data.job_description || ''); }
        if (data.material_id) { jQuery('#material_id').val(data.material_id).trigger('change'); }
        if (data.part_no) { jQuery('#part_no').val(data.part_no); } else { jQuery('#part_no').val(''); }
        if (data.drg_no) { jQuery('#drg_no').val(data.drg_no); } else { jQuery('#drg_no').val(''); }
        if (data.heat_no) { jQuery('#heat_no').val(data.heat_no); } else { jQuery('#heat_no').val(''); }
        if (data.rt_no) { jQuery('#rt_no').val(data.rt_no); } else { jQuery('#rt_no').val(''); }
        if (data.product_code) { jQuery('#product_code').val(data.product_code); } else { jQuery('#product_code').val(''); }
        if (data.thickness) { jQuery('#thickness').val(data.thickness); }
        if (data.area_of_coverage_id) { jQuery('#area_of_coverage_id').val(data.area_of_coverage_id).trigger('change'); }
        if (data.procedure_ref_id) { jQuery('#procedure_ref_id').val(data.procedure_ref_id).trigger('change'); }
        if (data.evaluation_as_per_id) { jQuery('#evaluation_as_per_id').val(data.evaluation_as_per_id).trigger('change'); }
        if (data.acceptance_standard_id) { jQuery('#acceptance_standard_id').val(data.acceptance_standard_id).trigger('change'); }
        if (data.approx_value) { jQuery('#approx_value').val(data.approx_value); }
        if (data.approx_weight) { jQuery('#approx_weight').val(data.approx_weight); }
        if (data.quantity) { jQuery('#quantity').val(data.quantity); }
        if (data.remark) { jQuery('#remark').val(data.remark); }

        jQuery('#CopyOfferDetailsModal').modal('hide');

        window.didCopyOffer = true;
    }
});

jQuery('#CopyOfferDetailsModal').on('hidden.bs.modal', function () {
    if (window.didCopyOffer) {
        window.didCopyOffer = false;
        var jobDesc = jQuery('#job_desc');
        if (jobDesc.length) {
            jobDesc.focus();
        } else {
            var typeOfJob = jQuery('#type_of_job_id');
            if (typeOfJob.hasClass("select2-hidden-accessible")) {
                typeOfJob.next('.select2-container').find('.select2-selection').focus();
            } else {
                typeOfJob.focus();
            }
        }
    }
});

/* ---------------------------------------------------------------------
 *  Pending Repair Reports Logic
 * ------------------------------------------------------------------- */
jQuery(document).ready(function () {
    jQuery('#OfferDetailsForm').on('change', 'input[name="process_type_fix"]', function () {
        let processType = jQuery(this).val();
        if (processType === 'Repair') {
            jQuery('#pendingBtn').prop('disabled', false);
        } else {
            jQuery('#pendingBtn').prop('disabled', true);
            resetPendingRepairFields();
        }
    });

    jQuery('#pendingBtn').on('click', function () {
        let customerId = jQuery('#commonOfferForm #customer_id').val();
        let nablType = jQuery('#commonOfferForm input[name="nabl_type_fix"]:checked').val();

        if (!customerId) {
            toastr.error("Select Customer.");
            return;
        }

        jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');

        jQuery.ajax({
            url: "get-rt-reports-list-for-inward",
            type: "POST",
            data: {
                customer_id: customerId,
                nabl_type: nablType,
                _token: jQuery('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function (res) {
                if (res.response_code == 1) {
                    let tbody = jQuery('#pendingRepairReportsTable tbody');
                    if (jQuery.fn.DataTable.isDataTable('#pendingRepairReportsTable')) {
                        jQuery('#pendingRepairReportsTable').DataTable().clear().destroy();
                    }
                    tbody.empty();

                    let selectedTestReportRtId = jQuery('#OfferDetailsForm #test_report_rt_id').val();
                    pendingRepairReportsData = res.reports || [];
                    if (res.reports && res.reports.length > 0) {
                        res.reports.forEach(function (r) {
                            let reportJson = JSON.stringify(r).replace(/'/g, "&apos;").replace(/"/g, "&quot;");
                            let isChecked = (selectedTestReportRtId && String(r.test_report_rt_id) === String(selectedTestReportRtId)) ? 'checked' : '';
                            let tr = `<tr>
                                <td><input type="radio" name="pending_repair_radio" class="form-check-input pending-repair-radio" data-report='${reportJson}' ${isChecked}></td>
                                <td>${r.test_report_no || ''}</td>
                                <td>${r.revision_number || ''}</td>
                                <td>${r.test_report_date || ''}</td>
                                <td>${r.type_of_job || ''}</td>
                                <td>${r.part_no || ''}</td>
                                <td>${r.drg_no || ''}</td>
                                <td>${r.heat_no || ''}</td>
                                <td>${r.product_code || ''}</td>
                                <td>${r.material || ''}</td>
                            </tr>`;
                            tbody.append(tr);
                        });
                    }

                    var $table = jQuery('#pendingRepairReportsTable');
                    var $new = $table.DataTable({
                        paging: true,
                        searching: true,
                        ordering: true,
                        info: true,
                        lengthChange: true,
                        "oLanguage": {
                            "sSearch": "Search :"
                        },
                        dom: 'lrtip',
                        "sScrollX": true,
                        "sScrollX": "100%",
                        "sScrollXInner": "110%",
                        "bScrollCollapse": true,
                    });
                    if (typeof fixDataTableColumnsUntilAdjusted === 'function') {
                        fixDataTableColumnsUntilAdjusted($new);
                    }

                    jQuery('#PendingRepairReportsModal').modal('show');
                }
            },
            error: function () {
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            },
            complete: function () {
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            }
        });
    });

    jQuery('#applyPendingRepairBtn').on('click', function () {
        let selectedRadio = jQuery('.pending-repair-radio:checked');
        if (selectedRadio.length === 0) {
            toastr.error("Please Select At Least One Report");
            return;
        }

        let reportData = selectedRadio.data('report');
        if (typeof reportData === 'string') {
            reportData = JSON.parse(reportData);
        }

        let revNo = (reportData.revision_number !== undefined && reportData.revision_number !== null) ? String(reportData.revision_number).trim() : '';
        let isRevision = revNo !== '';

        // Auto-fill core values
        jQuery('#OfferDetailsForm #test_report_rt_id').val(reportData.test_report_rt_id);
        jQuery('#OfferDetailsForm #revision_number').val(revNo);
        jQuery('#OfferDetailsForm input[name="process_type_fix"][value="Repair"]').prop('checked', true);
        jQuery('#OfferDetailsForm #type_of_testing_id_fix').val('RT').trigger('change.select2');

        // Auto-fill detail fields from pending repair report data
        if (reportData.type_of_job_id) {
            jQuery('#OfferDetailsForm #type_of_job_id').val(reportData.type_of_job_id).trigger('change.select2');
        }
        if (reportData.job_desc_id) {
            jQuery('#OfferDetailsForm #inward_job_desc_id').val(reportData.job_desc_id).trigger('change.select2');
        }
        if (reportData.job_description || reportData.job_desc) {
            jQuery('#OfferDetailsForm #job_desc').val(reportData.job_description || reportData.job_desc || '');
        }
        if (reportData.material_id) {
            jQuery('#OfferDetailsForm #material_id').val(reportData.material_id).trigger('change.select2');
        }
        if (reportData.area_of_coverage_id) {
            jQuery('#OfferDetailsForm #area_of_coverage_id').val(reportData.area_of_coverage_id).trigger('change.select2');
        }
        if (reportData.procedure_ref_id) {
            jQuery('#OfferDetailsForm #procedure_ref_id').val(reportData.procedure_ref_id).trigger('change.select2');
        }
        if (reportData.evaluation_as_per_id) {
            jQuery('#OfferDetailsForm #evaluation_as_per_id').val(reportData.evaluation_as_per_id).trigger('change.select2');
        }
        if (reportData.acceptance_standard_id) {
            jQuery('#OfferDetailsForm #acceptance_standard_id').val(reportData.acceptance_standard_id).trigger('change.select2');
        }

        jQuery('#OfferDetailsForm #part_no').val(reportData.part_no || '');
        jQuery('#OfferDetailsForm #drg_no').val(reportData.drg_no || '');
        jQuery('#OfferDetailsForm #heat_no').val(reportData.heat_no || '');
        jQuery('#OfferDetailsForm #rt_no').val(reportData.rt_no || '');
        jQuery('#OfferDetailsForm #product_code').val(reportData.product_code || '');

        setRepairFieldsReadonly(true);

        validateCopyButton();

        jQuery('#PendingRepairReportsModal').modal('hide');
        setTimeout(function () {
            let $thickness = jQuery('#OfferDetailsForm #thickness');
            $thickness.focus();
        }, 250);
    });
});

function setRepairFieldsReadonly(isReadonly, isRevision = false) {
    let form = jQuery('#OfferDetailsForm');

    if (typeof setSelect2Readonly === 'function') {
        setSelect2Readonly(form.find('#type_of_testing_id_fix'), isReadonly);
    }
    setRadioReadonly("#OfferDetailsForm input[name='process_type_fix']", isReadonly);

    // Lock detail fields for all Repair cases
    let lockDetailFields = isReadonly;

    if (typeof setSelect2Readonly === 'function') {
        setSelect2Readonly(form.find('#type_of_job_id'), lockDetailFields);
        setSelect2Readonly(form.find('#inward_job_desc_id'), lockDetailFields);
        setSelect2Readonly(form.find('#material_id'), lockDetailFields);
        setSelect2Readonly(form.find('#area_of_coverage_id'), lockDetailFields);
        setSelect2Readonly(form.find('#procedure_ref_id'), lockDetailFields);
        setSelect2Readonly(form.find('#evaluation_as_per_id'), lockDetailFields);
        setSelect2Readonly(form.find('#acceptance_standard_id'), lockDetailFields);
    }

    form.find('#job_desc').prop('readonly', lockDetailFields);
    form.find('#part_no').prop('readonly', lockDetailFields);
    form.find('#drg_no').prop('readonly', lockDetailFields);
    form.find('#heat_no').prop('readonly', lockDetailFields);
    form.find('#rt_no').prop('readonly', lockDetailFields);
    form.find('#product_code').prop('readonly', lockDetailFields);

    let allSel = '#type_of_testing_id_fix, #type_of_job_id, #inward_job_desc_id, #job_desc, #material_id, #area_of_coverage_id, #procedure_ref_id, #evaluation_as_per_id, #acceptance_standard_id, #part_no, #drg_no, #heat_no, #rt_no, #product_code';
    form.find(allSel).removeClass('skip-tab');

    if (isReadonly) {
        form.find('#type_of_testing_id_fix').addClass('skip-tab');
        if (lockDetailFields) {
            let detailSel = '#type_of_job_id, #inward_job_desc_id, #job_desc, #material_id, #area_of_coverage_id, #procedure_ref_id, #evaluation_as_per_id, #acceptance_standard_id, #part_no, #drg_no, #heat_no, #rt_no, #product_code';
            form.find(detailSel).addClass('skip-tab');
        }
    }
}

function resetPendingRepairFields() {
    let form = jQuery('#OfferDetailsForm');
    form.find('#test_report_rt_id').val(null);
    form.find('#revision_number').val('');

    setRadioReadonly("#OfferDetailsForm input[name='process_type_fix']", false);
    form.find('input[name="process_type_fix"]').prop('disabled', false);
    jQuery('#process_type_container').css('pointer-events', 'auto').css('opacity', '1');
    form.find('input[name="process_type_fix"][value="Fresh"]').prop('checked', true);

    setRepairFieldsReadonly(false, false);

    form.find('#type_of_testing_id_fix').val('RT').trigger('change.select2');
    form.find('#type_of_testing_id_fix').addClass('skip-tab');
    setSelect2Readonly('#OfferDetailsModal #type_of_testing_id_fix', true);
    form.find('#type_of_testing_id_fix').next('.select2-container').css('pointer-events', 'none');

    form.find('#quantity').val('1').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
    jQuery('#pendingBtn').prop('disabled', true);

    if (typeof handleTestingTypeProcessTypeRules === 'function') handleTestingTypeProcessTypeRules();
    if (typeof handleProcessTypeRules === 'function') handleProcessTypeRules();
    if (typeof validateCopyButton === 'function') validateCopyButton();
}

jQuery('#PendingRepairReportsModal').on('shown.bs.modal', function () {
    let $table = jQuery('#pendingRepairReportsTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().columns.adjust().draw();
        if (typeof initColumnSearch === 'function') {
            initColumnSearch('#pendingRepairReportsTable', [0], 'common_search');
        }
    }
});

jQuery('#PendingRepairReportsModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    let targetId = 'thickness';
    const input = document.getElementById(targetId);
    if (input) {
        setTimeout(() => {
            if (jQuery(input).hasClass('select2-hidden-accessible')) {
                jQuery(input).next('.select2-container').find('.select2-selection').focus();
            } else {
                input.focus();
            }
        }, 300);
    }
});
