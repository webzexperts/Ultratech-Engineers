/* =====================================================================
 *  Material Inward (parent + child) — mirrors offer.js structure.
 *  Detail rows are held in material_inward_details_data[] with a per-row
 *  `mode` (Insert / Update / Delete) and submitted as JSON on save.
 * ===================================================================== */

var material_inward_details_data = [];
var formId = jQuery('#commonMaterialInwardForm').find('input[name="id"]').val();

/* ---------------------------------------------------------------------
 *  Edit (open + fill)
 * ------------------------------------------------------------------- */
jQuery('#dyntable tbody').on('click', '.edit-material_inward', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#MaterialInwardModal').find('#id').val(data["material_inward_id"]);
    if (data && data["material_inward_id"]) {
        fetchAndFillMaterialInward(data["material_inward_id"]);
    }
});

function fetchAndFillMaterialInward(id) {
    if (!id) return;
    jQuery('#MaterialInwardModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-material_inward",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.mi_data != null) {
                var d = data.mi_data;
                jQuery('#MaterialInwardModal').find('#id').val(d.material_inward_id != "" ? d.material_inward_id : "");

                let url = checkFileRoute + "?id=" + d.material_inward_id + "&name=" + d.pdf_name + "&type=material_inward";
                jQuery('#preview_btn').attr('href', url).show();

                jQuery('#MaterialInwardModal').find('#material_inward_sequence').val(d.material_inward_sequence ?? "").focus();
                jQuery('#MaterialInwardModal').find('#material_inward_no').val(d.material_inward_no ?? "");
                jQuery('#MaterialInwardModal').find('#material_inward_date').val(d.material_inward_date ?? "");
                jQuery('#MaterialInwardModal').find('#old_inward_date').val(d.material_inward_date ?? "");

                jQuery('#MaterialInwardModal').find('input[name="nabl_type_fix"][value="' + d.nabl_type_fix + '"]').prop('checked', true);
                jQuery('#MaterialInwardModal').find('input[name="test_at_fix"][value="' + d.test_at_fix + '"]').prop('checked', true);
                jQuery('#MaterialInwardModal').find('input[name="job_type_fix"][value="' + d.job_type_fix + '"]').prop('checked', true);
                jQuery('#MaterialInwardModal').find('#inward_type_value_fix').val(d.inward_type_value_fix ?? d.form_type_value_fix ?? "Manual");
                jQuery('#MaterialInwardModal').find('#form_type_value_fix').val(d.form_type_value_fix ?? d.inward_type_value_fix ?? "Manual");
                jQuery('#MaterialInwardModal').find('#offer_id').val(d.offer_id ?? "");

                filterTestsByNabl();

                jQuery('#MaterialInwardModal').find('#customer_id').val(d.customer_id ?? "").trigger('change.select2');
                jQuery('#MaterialInwardModal').find('#challan_no').val(d.challan_no ?? d.dc_no ?? "");
                jQuery('#MaterialInwardModal').find('#challan_date').val(d.challan_date ?? d.dc_date ?? "");
                jQuery('#MaterialInwardModal').find('#offer_no').val(d.offer_no ?? d.po_no ?? "");
                jQuery('#MaterialInwardModal').find('#offer_date').val(d.offer_date ?? d.po_date ?? "");
                validateChallanNoDate();
                validateOfferNoDate();
                jQuery('#MaterialInwardModal').find('#material_type_id').val(d.material_type_id ?? "").trigger('change.select2');

                jQuery('#MaterialInwardModal').find('#total_qty').val(d.total_qty ?? "");
                jQuery('#MaterialInwardModal').find('#test_method_id').val(d.test_method_id ?? (d.test_method_id ?? "")).trigger('change.select2');
                jQuery('#MaterialInwardModal').find('#procedure_ref_id').val(d.procedure_reference_id ?? (d.procedure_ref_id ?? "")).trigger('change.select2');
                jQuery('#MaterialInwardModal').find('#evaluation_as_per_id').val(d.evaluation_as_per_id ?? "").trigger('change.select2');
                jQuery('#MaterialInwardModal').find('#acceptance_standard_id').val(d.acceptance_standard_id ?? "").trigger('change.select2');
                jQuery('#MaterialInwardModal').find('#radiation_energy_fix').val(d.radiation_energy_fix ?? "").trigger('change.select2');
                jQuery('#MaterialInwardModal').find('#coverage_area').val(d.coverage_area ?? "");
                jQuery('#MaterialInwardModal').find('#procedure_submitted_fix').val(d.procedure_submitted_fix ?? "Yes").trigger('change.select2');
                jQuery('#MaterialInwardModal').find('#del_date').val(d.del_date ?? "");
                jQuery('#MaterialInwardModal').find('#special_note').val(d.special_note ?? (d.sp_note ?? ""));

                material_inward_details_data = [];
                if (data.mi_details_data && data.mi_details_data.length > 0) {
                    data.mi_details_data.forEach(function (item) {
                        if (item.material_inward_detail_id && !item.material_inward_details_id) {
                            item.material_inward_details_id = item.material_inward_detail_id;
                        }
                    });
                    material_inward_details_data.push(...data.mi_details_data);
                }
                fillMaterialInwardDetailTable();

                var isFromOffer = (d.form_type_value_fix === 'From Offer' || d.inward_type_value_fix === 'Offer' || (d.offer_id && parseInt(d.offer_id) > 0));

                // 1) From Offer: Material Inward Detail Add button disabled
                if (isFromOffer) {
                    jQuery('#MaterialInwardModal').find('[data-bs-target="#MaterialInwardDetailsModal"]').prop('disabled', true).addClass('disabled');
                } else {
                    jQuery('#MaterialInwardModal').find('[data-bs-target="#MaterialInwardDetailsModal"]').prop('disabled', false).removeClass('disabled');
                }

                // 2) Customer Readonly, NABL & Non NABL readonly, Casting & Fabrication readonly, Test at readonly (on edit)
                setSelect2Readonly(jQuery('#MaterialInwardModal').find('#customer_id'), true);
                jQuery('#MaterialInwardModal').find('#customer_id').addClass('skip-tab');
                lockParentRadios(true);

                // 5) Offer No. & Date readonly
                if (isFromOffer) {
                    jQuery('#MaterialInwardModal').find('#offer_no').prop('readonly', true).addClass('skip-tab');
                    jQuery('#MaterialInwardModal').find('#offer_date').prop('readonly', true).addClass('skip-tab').css('pointer-events', 'none');
                    if (jQuery('#MaterialInwardModal').find('#offer_date').hasClass('hasDatepicker')) {
                        jQuery('#MaterialInwardModal').find('#offer_date').datepicker('option', 'disabled', true);
                    }
                } else {
                    jQuery('#MaterialInwardModal').find('#offer_no').prop('readonly', false).removeClass('skip-tab');
                    jQuery('#MaterialInwardModal').find('#offer_date').prop('readonly', false).removeClass('skip-tab').css('pointer-events', 'auto');
                    if (jQuery('#MaterialInwardModal').find('#offer_date').hasClass('hasDatepicker')) {
                        jQuery('#MaterialInwardModal').find('#offer_date').datepicker('option', 'disabled', false);
                    }
                }

                // Reset datepicker maxDate to default (endDate) first
                jQuery('#MaterialInwardModal').find('#material_inward_date').datepicker('option', 'maxDate', endDate);
                var parentInUse = material_inward_details_data.some(item => item.in_use == true || item.in_use == "true" || (item.used_qty && parseFloat(item.used_qty) > 0));
                if (parentInUse) {
                    lockParentRadios(true);
                    jQuery('#MaterialInwardModal').find('#material_inward_sequence').prop('readonly', true).addClass('skip-tab');
                } else {
                    if (!isFromOffer) {
                        lockParentRadios(false);
                    }
                    jQuery('#MaterialInwardModal').find('#material_inward_sequence').prop('readonly', false).removeClass('skip-tab');
                }
                updateParentRadiosLockState();

                jQuery('#MaterialInwardModal').find('#material_inward_date').prop('readonly', false).css('pointer-events', 'auto').removeClass('skip-tab');
                if (data.min_report_date) {
                    jQuery('#MaterialInwardModal').find('#material_inward_date')
                        .data('min-report-date', data.min_report_date)
                        .datepicker('option', 'maxDate', data.min_report_date);
                } else {
                    jQuery('#MaterialInwardModal').find('#material_inward_date').data('min-report-date', '');
                }

                const form = document.getElementById("commonMaterialInwardForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#MaterialInwardModal').find('#submitbtn').prop('disabled', false);
                jQuery('#MaterialInwardModal').find('#add_new').show();
                jQuery('#MaterialInwardModal').find('#preview_btn').show();
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
jQuery('#MaterialInwardModal').on('show.bs.modal', function (e) {
    if (e.target !== this) return;
    var formId = jQuery('#commonMaterialInwardForm').find('input[name="id"]').val();
    if (formId == "" || formId == undefined) {
        resetParentForm(true);
    } else {
        jQuery('#MaterialInwardModal').find('#add_new').show();
        jQuery('#MaterialInwardModal').find('#preview_btn').show();
    }
});

jQuery('#MaterialInwardModal').on('shown.bs.modal', function () {
    var formId = jQuery('#commonMaterialInwardForm').find('input[name="id"]').val();
    if (formId && formId !== "") {
        setTimeout(() => {
            var parentInUse = material_inward_details_data.some(item => item.in_use == true || item.in_use == "true" || (item.used_qty && parseFloat(item.used_qty) > 0));
            if (parentInUse) {
                jQuery('#MaterialInwardModal').find('#challan_no').focus().select();
            } else {
                jQuery('#MaterialInwardModal').find('#material_inward_sequence').focus().select();
            }
        }, 100);
    } else {
        setTimeout(() => {
            focusInitialMaterialInwardField();
        }, 150);
    }
});

jQuery('#MaterialInwardModal').on('hide.bs.modal', function (e) {
    if (e.target !== this) return;
    resetParentForm(false);
});

/* ---------------------------------------------------------------------
 *  Reset / Add New
 * ------------------------------------------------------------------- */
jQuery('#resetbtn').on('click', function () {
    var formId = jQuery('#MaterialInwardModal').find('#id').val();
    if (!formId) {
        resetParentForm(true);
        setTimeout(function () {
            focusInitialMaterialInwardField();
        }, 100);
    } else {
        fetchAndFillMaterialInward(formId);
    }
});

jQuery('#MaterialInwardModal').on('click', '#add_new', function () {
    resetParentForm(true);
});

function resetParentForm(fetchLatest = false) {
    var form = document.getElementById("commonMaterialInwardForm");
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
    }
    jQuery('#MaterialInwardModal').find('#inward_type_value_fix').val('Manual');
    jQuery('#MaterialInwardModal').find('#form_type_value_fix').val('Manual');
    jQuery('#MaterialInwardModal').find('#offer_id').val('');
    jQuery('#MaterialInwardModal').find('#id').val('');
    jQuery('#MaterialInwardModal').find('#old_inward_date').val('');
    setSelect2Readonly(jQuery('#MaterialInwardModal').find('#customer_id'), false);
    jQuery('#MaterialInwardModal').find('#customer_id').removeClass('skip-tab');
    jQuery('#MaterialInwardModal').find('#material_inward_date').prop('readonly', false).css('pointer-events', 'auto').removeClass('skip-tab').datepicker('option', 'maxDate', endDate).data('min-report-date', '');
    jQuery('#MaterialInwardModal').find('#material_inward_sequence').prop('readonly', false).removeClass('skip-tab');
    jQuery('#MaterialInwardModal').find('#material_inward_date').val(currentDate);
    jQuery('#MaterialInwardModal').find('#challan_date').val('');
    jQuery('#MaterialInwardModal').find('#challan_no').val('');
    jQuery('#MaterialInwardModal').find('#offer_no').val('').prop('readonly', false).removeClass('skip-tab');
    jQuery('#MaterialInwardModal').find('#offer_date').val('').prop('readonly', false).removeClass('skip-tab').css('pointer-events', 'auto');
    if (jQuery('#MaterialInwardModal').find('#offer_date').hasClass('hasDatepicker')) {
        jQuery('#MaterialInwardModal').find('#offer_date').datepicker('option', 'disabled', false);
    }
    jQuery('#MaterialInwardModal').find('[data-bs-target="#MaterialInwardDetailsModal"]').prop('disabled', false).removeClass('disabled');
    jQuery('#MaterialInwardModal').find('#del_date').val(currentDate);
    jQuery('#MaterialInwardModal').find('#special_note').val('');
    validateChallanNoDate();
    validateOfferNoDate();

    window.lastCustomerTestingType = null;
    material_inward_details_data = [];
    jQuery('#MaterialInwardDetailTable tbody').empty().append('<tr><td colspan="14" id="noDetails" class="text-center">No Material Inward Details Added</td></tr>');

    // Reset select2 dropdowns
    jQuery('#MaterialInwardModal').find('#customer_id').val('').trigger('change');
    ['material_type_id', 'test_method', 'procedure_ref_id', 'evaluation_as_per_id', 'acceptance_standard_id', 'radiation_energy_fix','test_method_id'].forEach(function (f) {
        jQuery('#MaterialInwardModal').find('#' + f).val('').trigger('change.select2');
    });
    jQuery('#MaterialInwardModal').find('#procedure_submitted_fix').val('Yes').trigger('change.select2');

    // Check defaults first
    jQuery('#MaterialInwardModal').find('input[name="test_at_fix"][value="Inhouse"]').prop('checked', true);
    jQuery('#MaterialInwardModal').find('input[name="job_type_fix"][value="Casting"]').prop('checked', true);
    jQuery('#MaterialInwardModal').find('input[name="nabl_type_fix"][value="Non NABL"]').prop('checked', true);

    lockParentRadios(false);

    ['nabl_type_fix', 'job_type_fix', 'test_at_fix'].forEach(function (name) {
        let $group = jQuery('#MaterialInwardModal').find('input[name="' + name + '"]');
        $group.attr('tabindex', '-1');
        let $chk = $group.filter(':checked');
        if (!$chk.length) $chk = $group.first();
        $chk.attr('tabindex', '0');
    });
    filterTestsByNabl();

    jQuery('#MaterialInwardModal').find('#add_new').hide();
    jQuery('#MaterialInwardModal').find('#preview_btn').hide();

    if (fetchLatest) {
        getLatestMaterialInwardNo();
        getLNRData();
    }
}

function validateChallanNoDate() {
    var cNo = (jQuery('#MaterialInwardModal #challan_no').val() || '').trim();
    var cDate = (jQuery('#MaterialInwardModal #challan_date').val() || '').trim();

    if (cNo || cDate) {
        jQuery('#MaterialInwardModal #challan_no').prop('required', true);
        jQuery('#MaterialInwardModal #challan_date').prop('required', true);
        jQuery('#challan_label sup.astric').show();
    } else {
        jQuery('#MaterialInwardModal #challan_no').prop('required', false).removeClass('is-invalid');
        jQuery('#MaterialInwardModal #challan_date').prop('required', false).removeClass('is-invalid');
        jQuery('#challan_label sup.astric').hide();
    }
}

function validateOfferNoDate() {
    var oNo = (jQuery('#MaterialInwardModal #offer_no').val() || '').trim();
    var oDate = (jQuery('#MaterialInwardModal #offer_date').val() || '').trim();

    if (oNo || oDate) {
        jQuery('#MaterialInwardModal #offer_no').prop('required', true);
        jQuery('#MaterialInwardModal #offer_date').prop('required', true);
        jQuery('#offer_label sup.astric').show();
    } else {
        jQuery('#MaterialInwardModal #offer_no').prop('required', false).removeClass('is-invalid');
        jQuery('#MaterialInwardModal #offer_date').prop('required', false).removeClass('is-invalid');
        jQuery('#offer_label sup.astric').hide();
    }
}

jQuery(document).on('input change blur', '#MaterialInwardModal #challan_no, #MaterialInwardModal #challan_date', function () {
    validateChallanNoDate();
});

jQuery(document).on('input change blur', '#MaterialInwardModal #offer_no, #MaterialInwardModal #offer_date', function () {
    validateOfferNoDate();
});

// Validate Material Inward Date against min report date
function validateInwardDateAgainstReports() {
    var inwardDateInput = jQuery('#MaterialInwardModal').find('#material_inward_date');
    var inwardDateVal = inwardDateInput.val().trim();
    var minReportDateVal = inwardDateInput.data('min-report-date');

    if (inwardDateVal && minReportDateVal) {
        var inwardDateObj = new Date(inwardDateVal.split("/").reverse().join("-"));
        var minReportDateObj = new Date(minReportDateVal.split("/").reverse().join("-"));

        if (inwardDateObj > minReportDateObj) {
            toastr.error("Material Inward Date cannot be greater than the Report Date.");
            return false;
        }
    }
    return true;
}

// Validate Del. Date against Material Inward Date (del_date cannot be less than material_inward_date)
function validateDelDateAgainstInwardDate() {
    var inwardDateVal = (jQuery('#MaterialInwardModal #material_inward_date').val() || '').trim();
    var delDateVal = (jQuery('#MaterialInwardModal #del_date').val() || '').trim();
    if (inwardDateVal && delDateVal) {
        var inwardParts = inwardDateVal.split('/');
        var delParts = delDateVal.split('/');
        if (inwardParts.length === 3 && delParts.length === 3) {
            var inwardDateObj = new Date(parseInt(inwardParts[2], 10), parseInt(inwardParts[1], 10) - 1, parseInt(inwardParts[0], 10));
            var delDateObj = new Date(parseInt(delParts[2], 10), parseInt(delParts[1], 10) - 1, parseInt(delParts[0], 10));
            if (delDateObj < inwardDateObj) {
                toastr.error("Del. Date Must Be Greater Than Or Equal To Inward Date.");
                jQuery('#MaterialInwardModal #del_date').focus();
                return false;
            }
        }
    }
    return true;
}

// When Inward Date is changed by user, auto set Del. Date equal to Inward Date
jQuery('#MaterialInwardModal').on('change', '#material_inward_date', function () {
    validateInwardDateAgainstReports();
    var inwDate = jQuery(this).val();
    if (inwDate && isValidDate(inwDate)) {
        jQuery('#MaterialInwardModal #del_date').val(inwDate);
    }
});

// Parent radios drive tests and LNR
jQuery('#MaterialInwardModal').on('change', 'input[name="nabl_type_fix"], input[name="job_type_fix"], input[name="test_at_fix"]', function () {
    let name = jQuery(this).attr('name');
    jQuery('#MaterialInwardModal').find('input[name="' + name + '"]').attr('tabindex', '-1');
    jQuery(this).attr('tabindex', '0');
    if (name === 'nabl_type_fix') {
        filterTestsByNabl();
    }
    var formId = jQuery('#MaterialInwardModal').find('#id').val();
    var customerId = jQuery('#MaterialInwardModal').find('#customer_id').val();
    if (!formId && customerId) {
        getLNRData(customerId);
    }
});

// Tab navigation across top parent radios: nabl_type_fix -> job_type_fix -> test_at_fix -> material_inward_sequence
jQuery(document).on('keydown', '#MaterialInwardModal input[name="nabl_type_fix"]', function (e) {
    if ((e.key === 'Tab' || e.keyCode === 9) && !e.shiftKey) {
        e.preventDefault();
        let $target = jQuery('#MaterialInwardModal input[name="job_type_fix"]:checked');
        if (!$target.length) $target = jQuery('#MaterialInwardModal input[name="job_type_fix"]').first();
        $target.attr('tabindex', '0');
        if ($target.length) $target[0].focus();
    }
});

jQuery(document).on('keydown', '#MaterialInwardModal input[name="job_type_fix"]', function (e) {
    if (e.key === 'Tab' || e.keyCode === 9) {
        if (e.shiftKey) {
            e.preventDefault();
            let $prev = jQuery('#MaterialInwardModal input[name="nabl_type_fix"]:checked');
            if (!$prev.length) $prev = jQuery('#MaterialInwardModal input[name="nabl_type_fix"]').first();
            $prev.attr('tabindex', '0');
            if ($prev.length) $prev[0].focus();
        } else {
            e.preventDefault();
            let $next = jQuery('#MaterialInwardModal input[name="test_at_fix"]:checked');
            if (!$next.length) $next = jQuery('#MaterialInwardModal input[name="test_at_fix"]').first();
            $next.attr('tabindex', '0');
            if ($next.length) $next[0].focus();
        }
    }
});

jQuery(document).on('keydown', '#MaterialInwardModal input[name="test_at_fix"]', function (e) {
    if (e.key === 'Tab' || e.keyCode === 9) {
        if (e.shiftKey) {
            e.preventDefault();
            let $prev = jQuery('#MaterialInwardModal input[name="job_type_fix"]:checked');
            if (!$prev.length) $prev = jQuery('#MaterialInwardModal input[name="job_type_fix"]').first();
            $prev.attr('tabindex', '0');
            if ($prev.length) $prev[0].focus();
        } else {
            e.preventDefault();
            let $next = jQuery('#MaterialInwardModal #material_inward_sequence');
            if ($next.length) $next.focus();
        }
    }
});

jQuery(document).on('keydown', '#MaterialInwardModal #material_inward_sequence', function (e) {
    if ((e.key === 'Tab' || e.keyCode === 9) && e.shiftKey) {
        e.preventDefault();
        let $target = jQuery('#MaterialInwardModal input[name="test_at_fix"]:checked');
        if (!$target.length) $target = jQuery('#MaterialInwardModal input[name="test_at_fix"]').first();
        $target.attr('tabindex', '0');
        if ($target.length) $target[0].focus();
    }
});

/* ---------------------------------------------------------------------
 *  Process Type Rules
 * ------------------------------------------------------------------- */
function handleProcessTypeRules(isUserChange = false) {
    let $qtyInput = jQuery('#MaterialInwardDetailsForm #quantity');
    $qtyInput.val('1').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
}

function handleTestingTypeProcessTypeRules() {
    let $radios = jQuery('#MaterialInwardDetailsForm input[name="process_type_fix"]');
    let $container = jQuery('#process_type_container');

    setRadioReadonly("#MaterialInwardDetailsForm input[name='process_type_fix']", false);
    $radios.prop('disabled', false);
    $container.css('pointer-events', 'auto').css('opacity', '1');
    $container.find('.form-check').css('pointer-events', 'auto').css('opacity', '1');

    let $proc = jQuery('#MaterialInwardDetailsForm #type_of_testing_id_fix');
    if (!$proc.val()) {
        $proc.val('RT').trigger('change.select2');
    }
    setSelect2Readonly('#MaterialInwardDetailsModal #type_of_testing_id_fix', true);
}

jQuery('#MaterialInwardDetailsForm').on('change', '#type_of_testing_id_fix', function () {
    handleTestingTypeProcessTypeRules();
});

jQuery('#MaterialInwardDetailsForm').on('change', 'input[name="process_type_fix"]', function () {
    handleProcessTypeRules(true);
});

function filterTestsByNabl() {
    var nablType = jQuery('#MaterialInwardModal input[name="nabl_type_fix"]:checked').val();
    const allTestingOptions = [
        { value: "RT", text: "RT" },
        { value: "UT", text: "UT" },
        { value: "MPT", text: "MPT" },
        { value: "DPT", text: "DPT" },
        { value: "ET", text: "ET" },
        { value: "VT", text: "VT" },
        { value: "PAUT", text: "PAUT" },
        { value: "UTT", text: "UTT" }
    ];

    let filtered = [...allTestingOptions];
    if (nablType === "NABL") {
        let allowed = (window.locationNablTests || "").split(',').map(s => s.trim().toUpperCase());
        filtered = allTestingOptions.filter(opt => allowed.includes(opt.value));
    }

    let $select = jQuery('#MaterialInwardDetailsForm #type_of_testing_id_fix');
    if ($select.length) {
        let currentVal = $select.attr('data-target-val') || $select.val();
        $select.removeAttr('data-target-val');
        $select.empty().append('<option value="">Select Type of Test</option>');

        filtered.forEach(opt => {
            $select.append(new Option(opt.text, opt.value));
        });

        if (filtered.some(opt => opt.value === currentVal)) {
            $select.val(currentVal).trigger('change.select2');
        } else {
            $select.val('').trigger('change.select2');
        }
    }
}

function getNextDetailSrNo() {
    let maxSr = 0;
    if (Array.isArray(material_inward_details_data)) {
        material_inward_details_data.forEach(function (row) {
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

jQuery('#MaterialInwardDetailsModal').on('show.bs.modal', function () {
    filterTestsByNabl();
    var formType = jQuery('#MaterialInwardDetailsModal').find("#form_type").val();
    if (formType !== 'edit') {
        jQuery('#MaterialInwardDetailsModal #sr_no').val(getNextDetailSrNo());
        jQuery('#MaterialInwardDetailsModal').find("#quantity").removeAttr("min");

        jQuery('#MaterialInwardDetailsForm #type_of_testing_id_fix').val('RT').trigger('change.select2');
        setSelect2Readonly('#MaterialInwardDetailsModal #type_of_testing_id_fix', true);

        setRadioReadonly("#MaterialInwardDetailsForm input[name='process_type_fix']", false);
        jQuery('#MaterialInwardDetailsForm input[name="process_type_fix"]').prop('disabled', false);
        jQuery('#process_type_container').css('pointer-events', 'auto').css('opacity', '1');
    }
});

jQuery('#MaterialInwardDetailsModal').on('shown.bs.modal', function () {
    setSelect2Readonly('#MaterialInwardDetailsModal #type_of_testing_id_fix', true);
    var formType = jQuery('#MaterialInwardDetailsModal').find("#form_type").val();
    if (formType === 'edit') {
        jQuery('#copy_from_sr_no').prop('readonly', true).prop('disabled', true).addClass('skip-tab').attr('tabindex', '-1').val('');
    } else {
        jQuery('#copy_from_sr_no').prop('readonly', false).prop('disabled', false).removeClass('skip-tab').removeAttr('tabindex').val('');
        let currentSr = jQuery('#MaterialInwardDetailsModal #sr_no').val();
        if (!currentSr || currentSr === '') {
            jQuery('#MaterialInwardDetailsModal #sr_no').val(getNextDetailSrNo());
        }
    }
    setTimeout(() => {
        var formType = jQuery('#MaterialInwardDetailsModal').find("#form_type").val();
        if (formType === 'edit') {
            var formIndx = jQuery('#MaterialInwardDetailsModal').find("#form_index").val();
            if (formIndx !== "") {
                setSelect2Readonly('#MaterialInwardDetailsModal #type_of_testing_id_fix', true);
                setRadioReadonly("#MaterialInwardDetailsForm input[name='process_type_fix']", false);
            }
        }
        handleTestingTypeProcessTypeRules();
    }, 150);
});

// Copy data from specified Sr. No. on Blur / Change
jQuery(document).on('blur change', '#copy_from_sr_no', function () {
    let copySrNo = jQuery(this).val().trim();
    if (!copySrNo) return;

    let targetRow = material_inward_details_data.find(r => r.mode !== 'Delete' && String(r.sr_no).trim() === copySrNo);
    if (targetRow) {
        let currentSrNo = jQuery('#MaterialInwardDetailsModal #sr_no').val();
        let form = jQuery('#MaterialInwardDetailsForm');
        form.find('#lab_id').val(targetRow.lab_id || '');
        form.find('#customer_id_no').val(targetRow.customer_id_no || '');
        form.find('#job_desc').val(targetRow.job_desc || targetRow.job_desc || '');
        form.find('#die_no').val(targetRow.die_no || '');
        form.find('#heat_no').val(targetRow.heat_no || '');
        form.find('#drg_no').val(targetRow.drg_no || '');
        if (targetRow.material_id) {
            form.find('#material_id').val(targetRow.material_id).trigger('change.select2');
        }
        form.find('#thickness').val(targetRow.thickness || targetRow.thickness_mm || '');
        form.find('#remark').val(targetRow.remark || '');

        // Maintain latest Sr. No. (Do not overwrite with copied Sr. No.)
        jQuery('#MaterialInwardDetailsModal #sr_no').val(currentSrNo);

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
            jQuery('#MaterialInwardDetailsModal #sr_no').focus().select();
        }, 50);
    }
});

function suggestInwardDieNo(e, $this) {
    commonSuggestionAjax({
        inputElement: $this,
        listSelector: '#inward_die_no_list',
        url: 'get-material_inward_part_no_list',
        responseListKey: 'partNoList'
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

/* ---------------------------------------------------------------------
 *  Detail row remove / edit
 * ------------------------------------------------------------------- */
function resequenceMaterialInwardDetails() {
    let activeSrNo = 1;
    if (Array.isArray(material_inward_details_data)) {
        material_inward_details_data.forEach(function (item) {
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

function removeMaterialInwardDetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        let item = material_inward_details_data[formIndx];
        if ((item.material_inward_details_id && item.material_inward_details_id != 0) || (item.material_inward_detail_id && item.material_inward_detail_id != 0)) {
            item.mode = "Delete";
        } else {
            material_inward_details_data.splice(formIndx, 1);
        }
        resequenceMaterialInwardDetails();
        jQuery('#MaterialInwardDetailTable tbody').empty();
        fillMaterialInwardDetailTable();
    });
}

function editMaterialInwardDetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();
    fillMaterialInwardDetailsForm(formIndx, rawIndx);
}

function fillMaterialInwardDetailsForm(formIndx, rawIndx) {
    let thisForm = jQuery('#MaterialInwardDetailsModal');
    var d = material_inward_details_data[formIndx];
    console.log(d);

    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    thisForm.find("#material_inward_details_id").val(d.material_inward_details_id ?? d.material_inward_detail_id ?? 0);
    thisForm.find("#test_report_rt_id").val(d.test_report_rt_id ?? null);
    thisForm.find("#revision_number").val(d.revision_number ?? "");

    let pType = d.process_type_fix || d.process_type || 'Fresh';
    thisForm.find('input[name="process_type_fix"][value="' + pType + '"]').prop('checked', true);

    thisForm.find("#sr_no").val(d.sr_no ?? "");
    thisForm.find("#type_of_testing_id_fix").val(d.type_of_testing_id_fix || 'RT').trigger('change.select2');
    setSelect2Readonly('#MaterialInwardDetailsModal #type_of_testing_id_fix', true);
    thisForm.find("#lab_id").val(d.lab_id ?? "");
    thisForm.find("#customer_id_no").val(d.customer_id_no ?? "");
    thisForm.find("#job_desc").val(d.job_desc ?? d.job_desc ?? "");
    thisForm.find("#die_no").val(d.die_no ?? "");
    thisForm.find("#heat_no").val(d.heat_no ?? "");
    thisForm.find("#drg_no").val(d.drg_no ?? "");
    thisForm.find("#material_id").val(zeroToEmpty(d.material_id)).trigger('change.select2');
    thisForm.find("#thickness").val(d.thickness ?? d.thickness ?? "");
    thisForm.find("#quantity").val(d.quantity || '1').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
    thisForm.find("#remark").val(d.remark ?? "");

    thisForm.modal('show');
}

/* ---------------------------------------------------------------------
 *  Grid rebuild (14 columns matching Offer Details)
 * ------------------------------------------------------------------- */
function fillMaterialInwardDetailTable() {
    resequenceMaterialInwardDetails();
    let tbody = jQuery('#MaterialInwardDetailTable tbody');
    tbody.empty();
    let totalQty = 0;
    let visibleRowCount = 0;

    if (material_inward_details_data && material_inward_details_data.length > 0) {
        for (let key in material_inward_details_data) {
            let item = material_inward_details_data[key];
            if (item.mode == "Delete") continue;
            visibleRowCount++;
            let formIndx = material_inward_details_data.indexOf(item);

            let process_type = item.process_type_fix || item.process_type_fix || 'Fresh';
            let process_name = item.process_type_name || item.type_of_testing_id_fix || '';
            let sr_no = item.sr_no || '';
            let lab_id = item.lab_id || '';
            let customer_id_no = item.customer_id_no || '';
            let job_desc = item.job_desc || item.job_desc || '';
            let die_no = item.die_no || '';
            let heat_no = item.heat_no || '';
            let drg_no = item.drg_no || '';
            let mat = item.material_name || '';
            let thick = item.thickness || item.thickness || '';
            let qty = item.quantity || 1;
            let rem = item.remark || '';

            totalQty += parseInt(qty) || 0;

            let in_use = (item.in_use == true || item.in_use == "true" || (item.used_qty && parseFloat(item.used_qty) > 0)) ? true : false;
            let actionBtn = in_use ? DetailsActionDropdown('editMaterialInwardDetails') : DetailsActionDropdown('editMaterialInwardDetails', 'removeMaterialInwardDetails');

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
        tbody.append('<tr><td colspan="14" id="noDetails" class="text-center">No Material Inward Details Added</td></tr>');
    }

    jQuery('#MaterialInwardModal #total_qty').val(totalQty > 0 ? totalQty : '');
    updateParentRadiosLockState();
}

/* ---------------------------------------------------------------------
 *  Detail modal lifecycle
 * ------------------------------------------------------------------- */
jQuery('#MaterialInwardDetailsForm').on('reset', function () {
    jQuery('#copy_from_sr_no').val('');
    setTimeout(function () {
        jQuery('#MaterialInwardDetailsForm input[name="process_type_fix"][value="Fresh"]').prop('checked', true);
        handleTestingTypeProcessTypeRules();
        handleProcessTypeRules();
    }, 50);
});

jQuery('#MaterialInwardDetailsModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#MaterialInwardDetailsModal');
    thisModal.find('#copy_from_sr_no').val('').prop('readonly', false).prop('disabled', false).removeClass('skip-tab').removeAttr('tabindex');
    thisModal.find('#sr_no').val('');
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    thisModal.find("#material_inward_details_id").val(0);
    thisModal.find("#quantity").removeAttr("min");
    setRadioReadonly("#MaterialInwardDetailsForm input[name='process_type_fix']", false);
    thisModal.find('#job_desc').prop('readonly', false).removeClass('skip-tab').val('');
    jQuery('#MaterialInwardDetailsForm').trigger("reset");
    jQuery('#MaterialInwardDetailsForm input[name="process_type_fix"][value="Fresh"]').prop('checked', true);

    handleTestingTypeProcessTypeRules();
    handleProcessTypeRules();
    jQuery('#inward_die_no_list').empty();
    jQuery('#inward_drg_no_list').empty();
});

/* ---------------------------------------------------------------------
 *  Detail form submit
 * ------------------------------------------------------------------- */
jQuery('#MaterialInwardDetailsForm').on('submit', function (e) {
    e.preventDefault();
    let thisModal = jQuery('#MaterialInwardDetailsModal');
    let form = this;

    if (!form.checkValidity()) {
        e.stopPropagation();
        jQuery(form).addClass('was-validated');
        return;
    }

    var formValue = {};
    jQuery.each(jQuery(form).serializeArray(), function (i, field) {
        formValue[field.name] = field.value;
    });

    let enteredSrNo = (formValue.sr_no || '').toString().trim();
    if (enteredSrNo !== '') {
        let editIndex = formValue.form_type === 'edit' ? parseInt(formValue.form_index, 10) : -1;
        let isDuplicate = material_inward_details_data.some(function (row, idx) {
            if (!row || row.mode === 'Delete') return false;
            if (editIndex >= 0 && idx === editIndex) return false;
            return (row.sr_no || '').toString().trim() === enteredSrNo;
        });

        if (isDuplicate) {
            toastr.error('Duplicate Sr. No. Found.');
            jQuery('#MaterialInwardDetailsForm #sr_no').focus();
            return;
        }
    }

    formValue.process_type_fix = jQuery('#MaterialInwardDetailsModal input[name="process_type_fix"]:checked').val() || 'Fresh';
    formValue.process_type = formValue.process_type_fix;
    formValue.process_type_name = jQuery('#MaterialInwardDetailsModal #type_of_testing_id_fix option:selected').val() ? jQuery('#MaterialInwardDetailsModal #type_of_testing_id_fix option:selected').text() : (formValue.type_of_testing_id_fix || '');
    formValue.material_name = jQuery('#MaterialInwardDetailsModal #material_id option:selected').val() ? jQuery('#MaterialInwardDetailsModal #material_id option:selected').text() : '';

    if (formValue.form_type == "edit") {
        formValue.mode = (formValue.material_inward_details_id && formValue.material_inward_details_id != 0) ? "Update" : "Insert";
        material_inward_details_data[formValue.form_index] = {
            ...material_inward_details_data[formValue.form_index],
            ...formValue
        };
        fillMaterialInwardDetailTable();
        toastSuccess("Record Updated.");
        thisModal.modal('hide');
    } else {
        formValue.mode = "Insert";
        formValue.material_inward_details_id = 0;
        material_inward_details_data.push(formValue);
        fillMaterialInwardDetailTable();
        toastSuccess("Record Inserted.");

        // ADD Mode: LNR behavior - keep all details, only increment sr_no (+1), modal stays open
        jQuery('#MaterialInwardDetailsModal #sr_no').val(getNextDetailSrNo());
        jQuery('#copy_from_sr_no').val('');
        jQuery('#MaterialInwardDetailsForm #quantity').val('1').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');

        // Process remains RT and readonly
        jQuery('#MaterialInwardDetailsForm #type_of_testing_id_fix').val('RT').trigger('change.select2');
        jQuery('#MaterialInwardDetailsForm #type_of_testing_id_fix').addClass('skip-tab');
        setSelect2Readonly('#MaterialInwardDetailsModal #type_of_testing_id_fix', true);
        jQuery('#MaterialInwardDetailsModal #type_of_testing_id_fix').next('.select2-container').css('pointer-events', 'none');

        setTimeout(function () {
            $(form).removeClass('was-validated');
            $(form).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            jQuery('#MaterialInwardDetailsForm').find('.error').removeClass('error');
            jQuery('#MaterialInwardDetailsForm #sr_no').focus().select();
        }, 150);
    }
});

/* ---------------------------------------------------------------------
 *  Main form submit (assemble JSON + POST)
 * ------------------------------------------------------------------- */
jQuery('#commonMaterialInwardForm').on('submit', function (e) {
    e.preventDefault();
    let form = this;

    validateChallanNoDate();
    validateOfferNoDate();

    // var challanNo = (jQuery('#MaterialInwardModal #challan_no').val() || '').trim();
    var challanDate = (jQuery('#MaterialInwardModal #challan_date').val() || '').trim();
    // if (challanNo && !challanDate) {
    //     toastr.error("Enter Challan Date");
    //     jQuery('#MaterialInwardModal #challan_date').focus();
    //     return;
    // }
    // if (!challanNo && challanDate) {
    //     toastr.error("Enter Challan No.");
    //     jQuery('#MaterialInwardModal #challan_no').focus();
    //     return;
    // }
    if (challanDate && !isValidDate(challanDate)) {
        toastr.error("Enter A Valid Challan Date!");
        return;
    }

    // var offerNo = (jQuery('#MaterialInwardModal #offer_no').val() || '').trim();
    var offerDate = (jQuery('#MaterialInwardModal #offer_date').val() || '').trim();
    // if (offerNo && !offerDate) {
    //     toastr.error("Enter Offer Date");
    //     jQuery('#MaterialInwardModal #offer_date').focus();
    //     return;
    // }
    // if (!offerNo && offerDate) {
    //     toastr.error("Enter Offer No.");
    //     jQuery('#MaterialInwardModal #offer_no').focus();
    //     return;
    // }
    if (offerDate && !isValidDate(offerDate)) {
        toastr.error("Enter A Valid Offer Date!");
        return;
    }

    var dateValue = document.getElementById("material_inward_date").value.trim();
    if (!isValidDate(dateValue)) {
        toastr.error("Enter A Valid Date!");
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        jQuery(form).addClass('was-validated');
        return;
    }

    if (!validateInwardDateAgainstReports()) {
        return;
    }

    if (!validateDelDateAgainstInwardDate()) {
        return;
    }

    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#MaterialInwardModal').find('#submitbtn').prop('disabled', true);

    var formIdnew = jQuery('#MaterialInwardModal').find('#commonMaterialInwardForm').find('#id').val();
    var formUrl = formIdnew != undefined && formIdnew != "" ? "update-material_inward" : "store-material_inward";
    var data = new FormData(form);
    data.append('material_inward_details_data', JSON.stringify(material_inward_details_data ?? []));
    data.append('_token', jQuery('meta[name="csrf-token"]').attr('content'));

    let validData = material_inward_details_data.filter(item => item.mode !== "Delete");
    if (validData.length == 0) {
        toastr.error('Add At Least One Material Inward Detail.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#MaterialInwardModal').find('#submitbtn').prop('disabled', false);
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
                                let seqInput = $('#material_inward_date');
                                if (seqInput.length && !seqInput.prop('readonly')) {
                                    seqInput.focus().select();
                                }
                            }, 50);
                        } else {
                            window.location.reload();
                        }
                    };
                    if (typeof pageRelod !== 'undefined' && pageRelod == 'Yes') {
                        const form = document.getElementById("commonMaterialInwardForm");
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
                            focusInitialMaterialInwardField();
                        }, 100);
                    }
                    if (data.url != "") { toastSuccessPreview(data.response_message, data.url, nextFn); }
                    else { toastSuccess(data.response_message, nextFn); }
                }
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                jQuery('#MaterialInwardModal').find('#submitbtn').prop('disabled', false);
            } else {
                toastr.error(data.response_message);
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                jQuery('#MaterialInwardModal').find('#submitbtn').prop('disabled', false);
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
            jQuery('#MaterialInwardModal').find('#submitbtn').prop('disabled', false);
        }
    });
});

/* ---------------------------------------------------------------------
 *  Helpers & Autocomplete
 * ------------------------------------------------------------------- */
function lockParentRadios(lock) {
    if (typeof window.locationNablDefault !== 'undefined' && window.locationNablDefault === 'Non NABL') {
        setRadioReadonly("#MaterialInwardModal input[name='nabl_type_fix']", true);
    } else {
        setRadioReadonly("#MaterialInwardModal input[name='nabl_type_fix']", lock);
    }
    setRadioReadonly("#MaterialInwardModal input[name='test_at_fix']", lock);
    setRadioReadonly("#MaterialInwardModal input[name='job_type_fix']", lock);
}

function updateParentRadiosLockState() {
    var parentId = jQuery('#MaterialInwardModal').find('#id').val();
    let isAdd = (parentId == "" || parentId == undefined || parentId == 0);
    var formTypeVal = jQuery('#MaterialInwardModal').find('#form_type_value_fix').val();
    var inwardTypeVal = jQuery('#MaterialInwardModal').find('#inward_type_value_fix').val();
    var offerIdVal = jQuery('#MaterialInwardModal').find('#offer_id').val();
    var isFromOffer = (formTypeVal === 'From Offer' || inwardTypeVal === 'Offer' || (offerIdVal && parseInt(offerIdVal) > 0));

    if (!isAdd) {
        setSelect2Readonly(jQuery('#MaterialInwardModal #customer_id'), true);
        jQuery('#MaterialInwardModal #customer_id').addClass('skip-tab');
        lockParentRadios(true);
        if (isFromOffer) {
            jQuery('#MaterialInwardModal').find('[data-bs-target="#MaterialInwardDetailsModal"]').prop('disabled', true).addClass('disabled');
            jQuery('#MaterialInwardModal').find('#offer_no').prop('readonly', true).addClass('skip-tab');
            jQuery('#MaterialInwardModal').find('#offer_date').prop('readonly', true).addClass('skip-tab').css('pointer-events', 'none');
            if (jQuery('#MaterialInwardModal').find('#offer_date').hasClass('hasDatepicker')) {
                jQuery('#MaterialInwardModal').find('#offer_date').datepicker('option', 'disabled', true);
            }
        }
        return;
    }

    let hasRepairDetail = material_inward_details_data.some(function (item) {
        return item.mode !== "Delete" && (item.process_type_fix === 'Repair' || item.process_type === 'Repair');
    });
    let parentInUse = material_inward_details_data.some(function (item) {
        return item.mode !== "Delete" && (item.in_use == true || item.in_use == "true" || (item.used_qty && parseFloat(item.used_qty) > 0));
    });

    if (hasRepairDetail || parentInUse) {
        setSelect2Readonly(jQuery('#MaterialInwardModal #customer_id'), true);
        jQuery('#MaterialInwardModal #customer_id').addClass('skip-tab');
        lockParentRadios(true);
    } else {
        setSelect2Readonly(jQuery('#MaterialInwardModal #customer_id'), false);
        jQuery('#MaterialInwardModal #customer_id').removeClass('skip-tab');
        lockParentRadios(false);
    }
    jQuery('#MaterialInwardModal').find('[data-bs-target="#MaterialInwardDetailsModal"]').prop('disabled', false).removeClass('disabled');
    jQuery('#MaterialInwardModal').find('#offer_no').prop('readonly', false).removeClass('skip-tab');
    jQuery('#MaterialInwardModal').find('#offer_date').prop('readonly', false).removeClass('skip-tab').css('pointer-events', 'auto');
    if (jQuery('#MaterialInwardModal').find('#offer_date').hasClass('hasDatepicker')) {
        jQuery('#MaterialInwardModal').find('#offer_date').datepicker('option', 'disabled', false);
    }
}

function focusInitialMaterialInwardField() {
    let modal = jQuery('#MaterialInwardModal');
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

function getLatestMaterialInwardNo() {
    jQuery.ajax({
        url: "get-latest_material_inward_number",
        type: "GET",
        headers: headerOpt,
        dataType: "json",
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#MaterialInwardModal #material_inward_sequence').val(data.number);
                jQuery('#MaterialInwardModal #material_inward_no').val(data.latest_no);
            }
        }
    });
}

// check sequence number duplication
jQuery(document).on('change', '#commonMaterialInwardForm #material_inward_sequence', function () {
    checkSequence();
});

function checkSequence() {
    let thisForm = jQuery('#commonMaterialInwardForm');
    if (thisForm.find('#material_inward_sequence').prop('readonly')) return;
    let val = thisForm.find('#material_inward_sequence').val();

    if (val > 0 == false) {
        toastr.error('Enter Valid Inward No.');
        jQuery('#material_inward_sequence').parent().parent().parent('div.control-group').addClass('error');
        jQuery('#material_inward_sequence').focus();
        jQuery('#material_inward_sequence').val('');
    } else {
        jQuery('#MaterialInwardModal').find('#submitbtn').prop('disabled', true);

        jQuery('#material_inward_sequence').addClass('file-loader');
        jQuery('#material_inward_sequence').parent().parent().parent('div.control-group').removeClass('error');

        var urL = "check-material_inward_number_duplication?for=add&material_inward_sequence=" + val;

        var formId = jQuery('#commonMaterialInwardForm').find('input[name="id"]').val();

        if (formId !== undefined && formId != "") { // if form is edit
            urL = "check-material_inward_number_duplication?for=edit&material_inward_sequence=" + val + "&id=" + formId;
        }

        jQuery.ajax({
            url: urL,
            type: 'GET',
            headers: headerOpt,
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery('#material_inward_sequence').removeClass('file-loader');
                if (data.response_code == 0) {
                    toastr.error(data.response_message);
                    jQuery('#commonMaterialInwardForm #material_inward_sequence').val('');
                    const input = document.getElementById('material_inward_sequence'); input?.focus();
                    jQuery('#MaterialInwardModal').find('#submitbtn').prop('disabled', false);
                } else {
                    jQuery('#commonMaterialInwardForm #material_inward_no').val(data.latest_no);
                    jQuery('#commonMaterialInwardForm #material_inward_sequence').val(val);
                    jQuery('#MaterialInwardModal').find('#submitbtn').prop('disabled', false);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery('#MaterialInwardModal').find('#submitbtn').prop('disabled', false);
                jQuery('#material_inward_sequence').removeClass('file-loader');
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

function getLNRData(customerId = '') {
    var isInitial = !customerId;
    if (!customerId) {
        customerId = jQuery('#MaterialInwardModal').find('#customer_id').val();
    }
    var nablTypeFix = jQuery('#MaterialInwardModal').find('input[name="nabl_type_fix"]:checked').val();
    var jobTypeFix = jQuery('#MaterialInwardModal').find('input[name="job_type_fix"]:checked').val();
    var testAtFix = jQuery('#MaterialInwardModal').find('input[name="test_at_fix"]:checked').val();

    jQuery.ajax({
        url: "get-material_inward_lnr_data",
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
                        jQuery('#MaterialInwardModal').find('input[name="nabl_type_fix"][value="' + targetNabl + '"]').prop('checked', true);
                    }
                    if (locLnr.test_at_fix) {
                        jQuery('#MaterialInwardModal').find('input[name="test_at_fix"][value="' + locLnr.test_at_fix + '"]').prop('checked', true);
                    }
                    if (locLnr.job_type_fix) {
                        jQuery('#MaterialInwardModal').find('input[name="job_type_fix"][value="' + locLnr.job_type_fix + '"]').prop('checked', true);
                    }
                    filterTestsByNabl();
                    focusInitialMaterialInwardField();
                }
                if (data.lnr_detail_testing_type) {
                    window.lastCustomerTestingType = data.lnr_detail_testing_type;
                } else {
                    window.lastCustomerTestingType = null;
                }
            }
        },
        error: function (jqXHR) {
            console.log('Error fetching Material Inward LNR data');
        }
    });
}