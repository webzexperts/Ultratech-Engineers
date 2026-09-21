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

                // let url = checkFileRoute + "?id=" + d.offer_id + "&name=" + d.pdf_name + "&type=offer";
                // jQuery('#preview_btn').attr('href', url).show();

                jQuery('#OfferModal').find('#offer_sequence').val(d.offer_sequence ?? "").focus();
                jQuery('#OfferModal').find('#offer_no').val(d.offer_no ?? "");
                jQuery('#OfferModal').find('#offer_date').val(d.offer_date ?? "");
                jQuery('#OfferModal').find('#old_offer_date').val(d.offer_date ?? "");

                jQuery('#OfferModal').find('input[name="nabl_type_fix"][value="' + d.nabl_type_fix + '"]').prop('checked', true);
                jQuery('#OfferModal').find('input[name="test_at_fix"][value="' + d.test_at_fix + '"]').prop('checked', true);
                jQuery('#OfferModal').find('input[name="job_type_fix"][value="' + d.job_type_fix + '"]').prop('checked', true);

                filterTestsByNabl();

                jQuery('#OfferModal').find('#customer_id').val(d.customer_id ?? "").trigger('change.select2');
                jQuery('#OfferModal').find('#dc_no').val(d.dc_no ?? "");
                jQuery('#OfferModal').find('#dc_date').val(d.dc_date ?? "");
                jQuery('#OfferModal').find('#po_no').val(d.po_no ?? "");
                jQuery('#OfferModal').find('#po_date').val(d.po_date ?? "");
                jQuery('#OfferModal').find('#sample_drawn_by').val(d.sample_drawn_by ?? "");

                jQuery('#OfferModal').find('#is_any_tpi_witness').val(d.is_any_tpi_witness ?? "").trigger('change.select2');
                toggleTpiName();
                jQuery('#OfferModal').find('#tpi_name').val(d.tpi_name ?? "");

                jQuery('#OfferModal').find('#condition_of_sample').val(d.condition_of_sample ?? "");
                jQuery('#OfferModal').find('#is_equipment_available').val(d.is_equipment_available ?? "").trigger('change.select2');
                jQuery('#OfferModal').find('#competent_personnel_available').val(d.competent_personnel_available ?? "").trigger('change.select2');
                jQuery('#OfferModal').find('#is_test_sub_contracted').val(d.is_test_sub_contracted ?? "").trigger('change.select2');
                jQuery('#OfferModal').find('#test_feasible').val(d.test_feasible ?? "").trigger('change.select2');
                jQuery('#OfferModal').find('#all_test_parameters_are_in_accredited_scope').val(d.all_test_parameters_are_in_accredited_scope ?? "").trigger('change.select2');
                jQuery('#OfferModal').find('#required_statement_of_conformity').val(d.required_statement_of_conformity ?? "").trigger('change.select2');
                jQuery('#OfferModal').find('#additional_requirement_from_customer').val(d.additional_requirement_from_customer ?? "");
                jQuery('#OfferModal').find('#special_note').val(d.special_note ?? "");
                jQuery('#OfferModal').find('#prepared_by_user_id').val(d.prepared_by_user_id ?? "").trigger('change.select2');

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
                var parentInUse = offer_details_data.some(item => item.in_use == true || item.in_use == "true" || (item.used_qty && parseFloat(item.used_qty) > 0));
                if (parentInUse) {
                    lockParentRadios(true);
                    jQuery('#OfferModal').find('#offer_sequence').prop('readonly', true).addClass('skip-tab');
                } else {
                    lockParentRadios(false);
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
                jQuery('#OfferModal').find('#submitbtn').prop('disabled', false);
                jQuery('#OfferModal').find('#add_new').show();
                // jQuery('#OfferModal').find('#preview_btn').show();
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
        // jQuery('#OfferModal').find('#preview_btn').show();
    }
});

jQuery('#OfferModal').on('shown.bs.modal', function () {
    var formId = jQuery('#commonOfferForm').find('input[name="id"]').val();
    if (formId && formId !== "") {
        setTimeout(() => {
            var parentInUse = offer_details_data.some(item => item.in_use == true || item.in_use == "true" || (item.used_qty && parseFloat(item.used_qty) > 0));
            if (parentInUse) {
                jQuery('#OfferModal').find('#dc_no').focus().select();
            } else {
                jQuery('#OfferModal').find('#offer_sequence').focus().select();
            }
        }, 100);
    } else {
        setTimeout(() => {
            focusInitialOfferField();
        }, 100);
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
    jQuery('#OfferModal').find('#id').val('');
    jQuery('#OfferModal').find('#old_offer_date').val('');
    setSelect2Readonly(jQuery('#OfferModal').find('#customer_id'), false);
    jQuery('#OfferModal').find('#customer_id').removeClass('skip-tab');
    jQuery('#OfferModal').find('#offer_date').prop('readonly', false).css('pointer-events', 'auto').removeClass('skip-tab').datepicker('option', 'maxDate', endDate).data('min-report-date', '');
    jQuery('#OfferModal').find('#offer_sequence').prop('readonly', false).removeClass('skip-tab');
    lockParentRadios(false);
    window.lastCustomerTestingType = null;
    offer_details_data = [];
    jQuery('#OfferDetailTable tbody').empty().append('<tr><td colspan="20" id="noDetails" class="text-center">No Offer Details Added</td></tr>');

    // Reset select2 dropdowns
    jQuery('#OfferModal').find('#customer_id').val('').trigger('change');
    ['is_any_tpi_witness', 'is_equipment_available', 'competent_personnel_available', 'is_test_sub_contracted', 'test_feasible', 'all_test_parameters_are_in_accredited_scope', 'required_statement_of_conformity'].forEach(function (f) {
        jQuery('#OfferModal').find('#' + f).val('Yes').trigger('change.select2');
    });
    jQuery('#OfferModal').find('#prepared_by_user_id').val(loginUserId).trigger('change.select2');
    toggleTpiName();

    // Check defaults
    jQuery('#OfferModal').find('input[name="test_at_fix"][value="At Lab"]').prop('checked', true);
    jQuery('#OfferModal').find('input[name="job_type_fix"][value="Non-Welding"]').prop('checked', true);
    if (typeof window.locationNablDefault !== 'undefined') {
        jQuery('#OfferModal').find('input[name="nabl_type_fix"][value="' + window.locationNablDefault + '"]').prop('checked', true);
    }
    filterTestsByNabl();

    jQuery('#OfferModal').find('#add_new').hide();
    // jQuery('#OfferModal').find('#preview_btn').hide();

    if (fetchLatest) {
        getLatestOfferNo();
        getLNRData();
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

// On blur date validation
jQuery('#OfferModal').on('blur', '#offer_date', function () {
    validateOfferDateAgainstReports();
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

// NABL drives which tests are selectable (NABL = location-accredited subset, Non NABL = all).
jQuery('#OfferModal').on('change', 'input[name="nabl_type_fix"]', function () {
    filterTestsByNabl();
    var formId = jQuery('#OfferModal').find('#id').val();
    var customerId = jQuery('#OfferModal').find('#customer_id').val();
    if (!formId && customerId) {
        getLNRData(customerId);
    }
});

/* ---------------------------------------------------------------------
 *  Process Type & Observation Sheet Rules
 * ------------------------------------------------------------------- */
function handleProcessTypeRules(isUserChange = false) {
    let processType = jQuery('#OfferDetailsForm input[name="process_type"]:checked').val() || 'Fresh';
    let $qtyInput = jQuery('#OfferDetailsForm #quantity');

    if (processType === 'Repair') {
        $qtyInput.val('1').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
    } else {
        $qtyInput.prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
        if (isUserChange) {
            $qtyInput.val('');
        }
    }
}

function handleTestingTypeProcessTypeRules() {
    let testType = jQuery('#OfferDetailsForm #type_of_testing_id_fix').val();
    let $radios = jQuery('#OfferDetailsForm input[name="process_type"]');
    let $container = jQuery('#process_type_container');

    // Remove pointer-events from container so Copy button is not affected
    $container.css('pointer-events', 'auto').css('opacity', '1');

    if (testType === 'RT') {
        setRadioReadonly("#OfferDetailsForm input[name='process_type']", false);
        $radios.prop('disabled', false);
        $container.find('.form-check').css('pointer-events', 'auto').css('opacity', '1');
    } else {
        jQuery('#OfferDetailsForm input[name="process_type"][value="Fresh"]').prop('checked', true);
        setRadioReadonly("#OfferDetailsForm input[name='process_type']", true);
        $radios.prop('disabled', true);
        $container.find('.form-check').css('pointer-events', 'none').css('opacity', '0.7');
        handleProcessTypeRules();
    }
}

jQuery('#OfferDetailsForm').on('change', '#type_of_testing_id_fix', function () {
    handleTestingTypeProcessTypeRules();
});

jQuery('#OfferDetailsForm').on('change', 'input[name="process_type"]', function () {
    handleProcessTypeRules(true);
});

function filterTestsByNabl() {
    var nablType = jQuery('#OfferModal input[name="nabl_type_fix"]:checked').val();
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

jQuery('#OfferDetailsModal').on('show.bs.modal', function () {
    filterTestsByNabl();
    var formType = jQuery('#OfferDetailsModal').find("#form_type").val();
    if (formType !== 'edit') {
        resetPendingRepairFields();
        jQuery('#OfferDetailsModal').find("#quantity").removeAttr("min");

        setSelect2Readonly(jQuery('#OfferDetailsModal').find('#type_of_testing_id_fix'), false);
        jQuery('#OfferDetailsModal').find('#type_of_testing_id_fix').removeClass('skip-tab');

        setRadioReadonly("#OfferDetailsForm input[name='process_type']", false);

        setSelect2Readonly(jQuery('#OfferDetailsModal').find('#type_of_job_id'), false);
        jQuery('#OfferDetailsModal').find('#type_of_job_id').removeClass('skip-tab');

        setSelect2Readonly(jQuery('#OfferDetailsModal').find('#inward_job_desc_id'), false);
        jQuery('#OfferDetailsModal').find('#inward_job_desc_id').removeClass('skip-tab');

        setSelect2Readonly(jQuery('#OfferDetailsModal').find('#area_of_coverage_id'), false);
        jQuery('#OfferDetailsModal').find('#area_of_coverage_id').removeClass('skip-tab');

        if (window.lastCustomerTestingType) {
            jQuery('#OfferDetailsModal').find('#type_of_testing_id_fix').val(window.lastCustomerTestingType).trigger('change.select2');
        }
    }
});

jQuery('#OfferDetailsModal').on('shown.bs.modal', function () {
    setTimeout(() => {
        var parentId = jQuery('#OfferModal').find("#id").val();
        var isParentStored = (parentId && parentId !== "" && parentId != 0 && parentId !== "0");
        var formType = jQuery('#OfferDetailsModal').find("#form_type").val();
        let processType = jQuery('#OfferDetailsModal input[name="process_type"]:checked').val() || 'Fresh';
        let testReportRtId = jQuery('#OfferDetailsModal #test_report_rt_id').val();
        let isRepair = processType === 'Repair' || (testReportRtId && testReportRtId !== '' && testReportRtId != 0);

        if (formType === 'edit') {
            var formIndx = jQuery('#OfferDetailsModal').find("#form_index").val();
            if (formIndx !== "") {
                var d = offer_details_data[formIndx];
                let isRowInUse = d && (d.in_use == true || d.in_use == "true" || (d.used_qty && parseFloat(d.used_qty) > 0));
                let isRepairRow = d && (d.process_type === 'Repair' || (d.test_report_rt_id && parseInt(d.test_report_rt_id) > 0));

                if (isRepairRow) {
                    setRepairFieldsReadonly(true);
                }

                if (isRowInUse) {
                    setSelect2Readonly(jQuery('#OfferDetailsModal').find('#type_of_testing_id_fix'), true);
                    jQuery('#OfferDetailsModal').find('#type_of_testing_id_fix').addClass('skip-tab');

                    setRadioReadonly("#OfferDetailsForm input[name='process_type']", true);
                    setSelect2Readonly(jQuery('#OfferDetailsModal').find('#type_of_job_id'), true);
                    jQuery('#OfferDetailsModal').find('#type_of_job_id').addClass('skip-tab');
                    setSelect2Readonly(jQuery('#OfferDetailsModal').find('#inward_job_desc_id'), true);
                    jQuery('#OfferDetailsModal').find('#inward_job_desc_id').addClass('skip-tab');
                    setSelect2Readonly(jQuery('#OfferDetailsModal').find('#area_of_coverage_id'), true);
                    jQuery('#OfferDetailsModal').find('#area_of_coverage_id').addClass('skip-tab');
                }

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

    $partSelect.empty().append('<option value="">Select Part No.</option>');
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

    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    thisForm.find("#offer_details_id").val(d.offer_details_id ?? d.offer_detail_id ?? 0);
    thisForm.find("#test_report_rt_id").val(d.test_report_rt_id ?? d.test_report_rt_id ?? null);
    thisForm.find("#revision_number").val(d.revision_number ?? "");

    if (d.in_use == true || d.in_use == "true" || (d.used_qty && parseFloat(d.used_qty) > 0)) {
        var usedQtyVal = d.used_qty ? parseInt(d.used_qty) : 0;
        thisForm.find("#quantity").attr('min', usedQtyVal);
    } else {
        thisForm.find("#quantity").removeAttr('min');
    }

    let $select = thisForm.find("#type_of_testing_id_fix");
    $select.attr('data-target-val', d.type_of_testing_id_fix ?? "");
    filterTestsByNabl();

    thisForm.find("#type_of_testing_id_fix").val(d.type_of_testing_id_fix ?? "").trigger('change.select2');
    thisForm.find("#type_of_job_id").val(zeroToEmpty(d.type_of_job_id)).trigger('change.select2');
    thisForm.find("#material_id").val(zeroToEmpty(d.material_id)).trigger('change.select2');
    thisForm.find("#area_of_coverage_id").val(zeroToEmpty(d.area_of_coverage_id)).trigger('change.select2');
    thisForm.find("#procedure_ref_id").val(zeroToEmpty(d.procedure_ref_id)).trigger('change.select2');
    thisForm.find("#evaluation_as_per_id").val(zeroToEmpty(d.evaluation_as_per_id)).trigger('change.select2');
    thisForm.find("#acceptance_standard_id").val(zeroToEmpty(d.acceptance_standard_id)).trigger('change.select2');
    thisForm.find("#heat_no").val(d.heat_no ?? "");
    thisForm.find("#rt_no").val(d.rt_no ?? "");
    thisForm.find("#product_code").val(d.product_code ?? "");
    thisForm.find("#thickness").val(d.thickness ?? "");
    thisForm.find("#quantity").val(d.quantity ?? "");
    let pType = d.process_type || 'Fresh';
    thisForm.find('input[name="process_type"][value="' + pType + '"]').prop('checked', true);
    let isRepairRow = (pType === 'Repair') || (d.test_report_rt_id && parseInt(d.test_report_rt_id) > 0);
    if (isRepairRow) {
        setRepairFieldsReadonly(true);
    } else {
        setRepairFieldsReadonly(false);
    }

    if (d && (d.in_use == true || d.in_use == "true" || (d.used_qty && parseFloat(d.used_qty) > 0))) {
        setSelect2Readonly(thisForm.find('#type_of_testing_id_fix'), true);
        thisForm.find('#type_of_testing_id_fix').addClass('skip-tab');

        setRadioReadonly("#OfferDetailsForm input[name='process_type']", true);

        setSelect2Readonly(thisForm.find('#type_of_job_id'), true);
        thisForm.find('#type_of_job_id').addClass('skip-tab');

        setSelect2Readonly(thisForm.find('#inward_job_desc_id'), true);
        thisForm.find('#inward_job_desc_id').addClass('skip-tab');

        setSelect2Readonly(thisForm.find('#area_of_coverage_id'), true);
        thisForm.find('#area_of_coverage_id').addClass('skip-tab');
    }
    thisForm.find('#is_observation_sheet').val(d.is_observation_sheet);
    handleTestingTypeProcessTypeRules();
    handleProcessTypeRules();
    thisForm.find("#approx_value").val(d.approx_value != 0 ? parseFloat(d.approx_value).toFixed(2) : "");
    thisForm.find("#approx_weight").val(d.approx_weight != 0 ? parseFloat(d.approx_weight).toFixed(3) : "");
    thisForm.find("#remark").val(d.remark ?? "");

    // Job Description + Part No + Drg No
    thisForm.find("#inward_job_desc_id").val(zeroToEmpty(d.job_desc_id)).trigger('change');
    thisForm.find("#part_no").val(d.part_no ?? d.part_name ?? "");
    thisForm.find("#drg_no").val(d.drg_no ?? "");

    if (d && (d.is_direct_obs_used === true || d.is_direct_obs_used === "true")) {
        thisForm.find("#quantity").prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
    }

    thisForm.modal('show');
}

/* ---------------------------------------------------------------------
 *  Grid rebuild
 * ------------------------------------------------------------------- */
function fillOfferDetailTable() {
    if (offer_details_data.length > 0) {
        var tblHtml = '';
        for (let key in offer_details_data) {
            let item = offer_details_data[key];
            if (item.mode == "Delete") continue;
            let formIndx = offer_details_data.indexOf(item);
            if (jQuery('#OfferDetailTable tbody').find('#noDetails').length > 0) {
                jQuery('#OfferDetailTable tbody').empty();
            }

            var test = item.type_of_testing_id_fix ?? '';
            var toj = item.type_of_job_name ?? '';
            var jd = item.job_description_name ?? '';
            var drg = item.drg_no ?? '';
            var part = item.part_no || item.part_name || '';
            var mat = item.material_name ?? '';
            var heat = item.heat_no ?? '';
            var rt = item.rt_no ?? '';
            var pcode = item.product_code ?? '';
            var thick = item.thickness ?? '';
            var aoc = item.area_of_coverage_name ?? '';
            var pr = item.procedure_ref_name ?? '';
            var eap = item.evaluation_as_per_name ?? '';
            var acs = item.acceptance_standard_name ?? '';
            var qty = item.quantity ?? '';
            var aval = item.approx_value && item.approx_value != 0 ? parseFloat(item.approx_value).toFixed(2) : '';
            var awt = item.approx_weight && item.approx_weight != 0 ? parseFloat(item.approx_weight).toFixed(3) : '';
            var rem = item.remark ?? '';
            var process_type = item.process_type ?? '';

            tblHtml += `<tr>`;
            tblHtml += `<td>`;
            var in_use = (item.in_use == true || item.in_use == "true" || (item.used_qty && parseFloat(item.used_qty) > 0)) ? true : false;
            tblHtml += in_use ? DetailsActionDropdown('editOfferDetails') : DetailsActionDropdown('editOfferDetails', 'removeOfferDetails');
            tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/></td>`;
            tblHtml += `<td>${test}</td>`;
            tblHtml += `<td>${process_type}</td>`;
            tblHtml += `<td>${toj}</td>`;
            tblHtml += `<td>${jd}</td>`;
            tblHtml += `<td>${part}</td>`;
            tblHtml += `<td>${drg}</td>`;
            tblHtml += `<td>${mat}</td>`;
            tblHtml += `<td>${heat}</td>`;
            tblHtml += `<td>${rt}</td>`;
            tblHtml += `<td>${pcode}</td>`;
            tblHtml += `<td>${thick}</td>`;
            tblHtml += `<td>${aoc}</td>`;
            tblHtml += `<td>${pr}</td>`;
            tblHtml += `<td>${eap}</td>`;
            tblHtml += `<td>${acs}</td>`;
            tblHtml += `<td>${qty}</td>`;
            tblHtml += `<td>${aval}</td>`;
            tblHtml += `<td>${awt}</td>`;
            tblHtml += `<td>${rem}</td>`;
            tblHtml += `</tr>`;
        }
        jQuery('#OfferDetailTable tbody').empty().append(tblHtml);
        if (jQuery('#OfferDetailTable tbody tr').length == 0) {
            jQuery('#OfferDetailTable tbody').append('<tr><td colspan="20" id="noDetails" class="text-center">No Offer Details Added</td></tr>');
        }
    } else {
        jQuery('#OfferDetailTable tbody').empty().append('<tr><td colspan="20" id="noDetails" class="text-center">No Offer Details Added</td></tr>');
    }

    updateParentRadiosLockState();
}

/* ---------------------------------------------------------------------
 *  Detail modal lifecycle
 * ------------------------------------------------------------------- */
jQuery('#OfferDetailsForm').on('reset', function () {
    setTimeout(function () {
        jQuery('#OfferDetailsForm input[name="process_type"][value="Fresh"]').prop('checked', true);
        handleTestingTypeProcessTypeRules();
        handleProcessTypeRules();
    }, 50);
});

jQuery('#OfferDetailsModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#OfferDetailsModal');
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    thisModal.find("#offer_details_id").val(0);
    thisModal.find("#quantity").removeAttr("min");
    setSelect2Readonly(thisModal.find('#type_of_testing_id_fix'), false);
    thisModal.find('#type_of_testing_id_fix').removeClass('skip-tab');
    setRadioReadonly("#OfferDetailsForm input[name='process_type']", false);
    setSelect2Readonly(thisModal.find('#type_of_job_id'), false);
    thisModal.find('#type_of_job_id').removeClass('skip-tab');
    setSelect2Readonly(thisModal.find('#inward_job_desc_id'), false);
    thisModal.find('#inward_job_desc_id').removeClass('skip-tab');
    setSelect2Readonly(thisModal.find('#area_of_coverage_id'), false);
    thisModal.find('#area_of_coverage_id').removeClass('skip-tab');
    resetPendingRepairFields();
    jQuery('#OfferDetailsForm').trigger("reset");
    jQuery('#OfferDetailsForm input[name="process_type"][value="Fresh"]').prop('checked', true);

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

    let currentProcessType = jQuery('#OfferDetailsForm input[name="process_type"]:checked').val() || 'Fresh';
    let currentTestReportRtId = jQuery('#OfferDetailsForm #test_report_rt_id').val();
    if (currentProcessType === 'Repair' && (!currentTestReportRtId || currentTestReportRtId === '' || currentTestReportRtId == 0 || currentTestReportRtId === '0')) {
        toastr.error('Please Select At Least One Report Form Pending.');
        return false;
    }

    if (currentProcessType === 'Repair' && currentTestReportRtId) {
        let isEditMode = jQuery('#OfferDetailsForm #form_type').val() === "edit";
        let editFormIndex = jQuery('#OfferDetailsForm #form_index').val();
        let isDuplicate = offer_details_data.some(function (row, idx) {
            if (row.mode === "Delete") return false;
            if (isEditMode && idx == editFormIndex) return false;
            return row.test_report_rt_id && (row.test_report_rt_id == currentTestReportRtId);
        });
        if (isDuplicate) {
            toastr.error("Duplicate Report Found!");
            return false;
        }
    }

    var qty = parseInt(jQuery('#OfferDetailsForm #quantity').val()) || 0;
    if (qty <= 0) { toastr.error('Enter Quantity greater than 0.'); return; }

    var minQty = parseInt(jQuery('#OfferDetailsForm #quantity').attr('min')) || 0;
    if (qty < minQty) {
        toastr.error('Quantity cannot be less than used quantity (' + minQty + ').');
        return;
    }

    // form -> object
    let formValue = {};
    jQuery.each(jQuery('#OfferDetailsForm').serializeArray(), function (i, f) { formValue[f.name] = f.value; });

    let processTypeVal = jQuery('#OfferDetailsModal input[name="process_type"]:checked').val() || 'Fresh';
    formValue.process_type = processTypeVal;
    formValue.type_of_job_name = jQuery('#type_of_job_id option:selected').text();
    formValue.job_description_name = jQuery('#inward_job_desc_id option:selected').text();
    formValue.part_no = jQuery('#OfferDetailsForm #part_no').val() || '';
    formValue.drg_no = jQuery('#OfferDetailsForm #drg_no').val() || '';
    formValue.part_name = formValue.part_no;
    formValue.material_name = jQuery('#material_id option:selected').text();
    formValue.area_of_coverage_name = jQuery('#area_of_coverage_id option:selected').val() ? jQuery('#area_of_coverage_id option:selected').text() : '';
    formValue.procedure_ref_name = jQuery('#procedure_ref_id option:selected').val() ? jQuery('#procedure_ref_id option:selected').text() : '';
    formValue.evaluation_as_per_name = jQuery('#evaluation_as_per_id option:selected').val() ? jQuery('#evaluation_as_per_id option:selected').text() : '';
    formValue.acceptance_standard_name = jQuery('#acceptance_standard_id option:selected').val() ? jQuery('#acceptance_standard_id option:selected').text() : '';

    if (formValue.form_type == "edit") {
        formValue.mode = (formValue.offer_details_id && formValue.offer_details_id != 0) ? "Update" : "Insert";
        offer_details_data[formValue.form_index] = {
            ...offer_details_data[formValue.form_index],
            ...formValue
        };
        var d = offer_details_data[formValue.form_index];
        var test = d.type_of_testing_id_fix ?? '';
        var toj = d.type_of_job_name ?? '';
        var jd = d.job_description_name ?? '';
        var drg = d.drg_no ?? '';
        var part = d.part_no || d.part_name || '';
        var mat = d.material_name ?? '';
        var heat = d.heat_no ?? '';
        var rt = d.rt_no ?? '';
        var pcode = d.product_code ?? '';
        var thick = d.thickness ?? '';
        var aoc = d.area_of_coverage_name ?? '';
        var pr = d.procedure_ref_name ?? '';
        var eap = d.evaluation_as_per_name ?? '';
        var acs = d.acceptance_standard_name ?? '';
        var qty = d.quantity ?? '';
        var aval = d.approx_value && d.approx_value != 0 ? parseFloat(d.approx_value).toFixed(2) : '';
        var awt = d.approx_weight && d.approx_weight != 0 ? parseFloat(d.approx_weight).toFixed(3) : '';
        var rem = d.remark ?? '';

        let tblHtml = `<td>`;
        var in_use = (d.in_use == true || d.in_use == "true" || (d.used_qty && parseFloat(d.used_qty) > 0)) ? true : false;
        tblHtml += in_use ? DetailsActionDropdown('editOfferDetails') : DetailsActionDropdown('editOfferDetails', 'removeOfferDetails');
        tblHtml += `<input type="hidden" name="form_indx" value="${formValue.form_index}"/></td>`;
        tblHtml += `<td>${test}</td>`;
        tblHtml += `<td>${formValue.process_type}</td>`;
        tblHtml += `<td>${toj}</td>`;
        tblHtml += `<td>${jd}</td>`;
        tblHtml += `<td>${part}</td>`;
        tblHtml += `<td>${drg}</td>`;
        tblHtml += `<td>${mat}</td>`;
        tblHtml += `<td>${heat}</td>`;
        tblHtml += `<td>${rt}</td>`;
        tblHtml += `<td>${pcode}</td>`;
        tblHtml += `<td>${thick}</td>`;
        tblHtml += `<td>${aoc}</td>`;
        tblHtml += `<td>${pr}</td>`;
        tblHtml += `<td>${eap}</td>`;
        tblHtml += `<td>${acs}</td>`;
        tblHtml += `<td>${qty}</td>`;
        tblHtml += `<td>${aval}</td>`;
        tblHtml += `<td>${awt}</td>`;
        tblHtml += `<td>${rem}</td>`;

        jQuery('#OfferDetailTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
        toastSuccess("Record Updated.");
        updateParentRadiosLockState();
        thisModal.modal('hide');
    } else {
        formValue.mode = "Insert";
        formValue.offer_details_id = 0;
        offer_details_data.push(formValue);
        let formIndx = offer_details_data.indexOf(formValue);
        if (jQuery('#OfferDetailTable tbody').find('#noDetails').length > 0) {
            jQuery('#OfferDetailTable tbody').empty();
        }

        var test = formValue.type_of_testing_id_fix ?? '';
        var toj = formValue.type_of_job_name ?? '';
        var jd = formValue.job_description_name ?? '';
        var drg = formValue.drg_no ?? '';
        var part = formValue.part_no || formValue.part_name || '';
        var mat = formValue.material_name ?? '';
        var heat = formValue.heat_no ?? '';
        var rt = formValue.rt_no ?? '';
        var pcode = formValue.product_code ?? '';
        var thick = formValue.thickness ?? '';
        var aoc = formValue.area_of_coverage_name ?? '';
        var pr = formValue.procedure_ref_name ?? '';
        var eap = formValue.evaluation_as_per_name ?? '';
        var acs = formValue.acceptance_standard_name ?? '';
        var qty = formValue.quantity ?? '';
        var aval = formValue.approx_value && formValue.approx_value != 0 ? parseFloat(formValue.approx_value).toFixed(2) : '';
        var awt = formValue.approx_weight && formValue.approx_weight != 0 ? parseFloat(formValue.approx_weight).toFixed(3) : '';
        var rem = formValue.remark ?? '';

        let tblHtml = `<tr>`;
        tblHtml += `<td>`;
        tblHtml += DetailsActionDropdown('editOfferDetails', 'removeOfferDetails');
        tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/></td>`;
        tblHtml += `<td>${test}</td>`;
        tblHtml += `<td>${formValue.process_type}</td>`;
        tblHtml += `<td>${toj}</td>`;
        tblHtml += `<td>${jd}</td>`;
        tblHtml += `<td>${part}</td>`;
        tblHtml += `<td>${drg}</td>`;
        tblHtml += `<td>${mat}</td>`;
        tblHtml += `<td>${heat}</td>`;
        tblHtml += `<td>${rt}</td>`;
        tblHtml += `<td>${pcode}</td>`;
        tblHtml += `<td>${thick}</td>`;
        tblHtml += `<td>${aoc}</td>`;
        tblHtml += `<td>${pr}</td>`;
        tblHtml += `<td>${eap}</td>`;
        tblHtml += `<td>${acs}</td>`;
        tblHtml += `<td>${qty}</td>`;
        tblHtml += `<td>${aval}</td>`;
        tblHtml += `<td>${awt}</td>`;
        tblHtml += `<td>${rem}</td>`;
        tblHtml += `</tr>`;

        jQuery('#OfferDetailTable tbody').append(tblHtml);
        toastSuccess("Record Inserted.");
        updateParentRadiosLockState();

        // reset for next entry
        form.reset();
        resetPendingRepairFields();
        setTimeout(function () {
            $(form).removeClass('was-validated');
            $(form).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            jQuery('#OfferDetailsForm').find('.error').removeClass('error');
        }, 150);
        jQuery('#OfferDetailsForm').find('.js-example-basic-single').val('').trigger('change.select2');
        if (window.lastCustomerTestingType) {
            jQuery('#OfferDetailsForm #type_of_testing_id_fix').val(window.lastCustomerTestingType).trigger('change.select2');
        }
        jQuery('#OfferDetailsForm #part_no').val('');
        jQuery('#OfferDetailsForm #drg_no').val('');
        jQuery('#OfferDetailsForm #heat_no').val('');
        jQuery('#OfferDetailsForm #rt_no').val('');
        jQuery('#OfferDetailsForm #product_code').val('');
        jQuery('#OfferDetailsForm #thickness').val('');
        jQuery('#OfferDetailsForm #quantity').val('').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
        jQuery('#OfferDetailsForm #approx_value').val('');
        jQuery('#OfferDetailsForm #approx_weight').val('');
        jQuery('#OfferDetailsForm #remark').val('');
        jQuery('#inward_part_no_list').empty();
        jQuery('#inward_drg_no_list').empty();
        jQuery('#inward_product_code_list').empty();
        setTimeout(function () {
            let $select = jQuery('#OfferDetailsForm #type_of_testing_id_fix');
            $select.one('select2:opening', function (e) {
                e.preventDefault();
            });

            let sel = $select.next('.select2-container').find('.select2-selection');
            if (sel.length) {
                sel.attr('tabindex', 0).focus();
            }

            $select.select2('close');
        }, 150);
    }
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

    if (!form.checkValidity()) {
        e.stopPropagation();
        jQuery(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#OfferModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    let currentProcessType = jQuery('#OfferDetailsForm input[name="process_type"]:checked').val() || 'Fresh';
    let currentTestReportRtId = jQuery('#OfferDetailsForm #test_report_rt_id').val();
    if (currentProcessType === 'Repair' && (!currentTestReportRtId || currentTestReportRtId === '' || currentTestReportRtId == 0 || currentTestReportRtId === '0')) {
        toastr.error('Please Select At Least One Report Form Pending.');
        return false;
    }
    if (!validateOfferDateAgainstReports()) {
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#OfferModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var poNo = jQuery('#OfferModal').find('#commonOfferForm').find('#po_no').val();
    var poDate = jQuery('#OfferModal').find('#commonOfferForm').find('#po_date').val();

    var formIdnew = jQuery('#OfferModal').find('#commonOfferForm').find('#id').val();
    var formUrl = formIdnew != undefined && formIdnew != "" ? "update-offer" : "store-offer";
    var data = new FormData(form);
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
function getLatestOfferNo() {
    jQuery.ajax({
        url: "get-latest_offer_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
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

    if (val > 0 == false) {
        toastr.error('Enter Valid Offer No.');
        jQuery('#offer_sequence').parent().parent().parent('div.control-group').addClass('error');
        jQuery('#offer_sequence').focus();
        jQuery('#offer_sequence').val('');
    } else {
        jQuery('#OfferModal').find('#submitbtn').prop('disabled', true);
        jQuery('#offer_sequence').addClass('file-loader');
        jQuery('#offer_sequence').parent().parent().parent('div.control-group').removeClass('error');

        var urL = "check-offer_number_duplication?for=add&offer_sequence=" + val;
        var formId = jQuery('#commonOfferForm').find('input[name="id"]').val();
        if (formId !== undefined && formId != "") {
            urL = "check-offer_number_duplication?for=edit&offer_sequence=" + val + "&id=" + formId;
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

// Customer change handler for Customer-wise LNR
jQuery('#OfferModal').on('change', '#customer_id', function () {
    var customerId = jQuery(this).val();
    var formId = jQuery('#OfferModal').find('#id').val();
    if (!formId && customerId) {
        getLNRData(customerId);
    }
});

// Get Last Note Reset (LNR) Data
function getLNRData(customerId = '') {
    var isInitial = !customerId;
    if (!customerId) {
        customerId = jQuery('#OfferModal').find('#customer_id').val();
    }
    var nablTypeFix = jQuery('#OfferModal').find('input[name="nabl_type_fix"]:checked').val();

    jQuery.ajax({
        url: "get-offer_lnr_data",
        type: 'GET',
        headers: headerOpt,
        data: {
            customer_id: customerId,
            nabl_type_fix: nablTypeFix
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
                }
                if (data.lnr_data != null) {
                    var lnr = data.lnr_data;
                    jQuery('#OfferModal').find('#sample_drawn_by').val(lnr.sample_drawn_by ?? "");
                    jQuery('#OfferModal').find('#is_any_tpi_witness').val(lnr.is_any_tpi_witness ?? "Yes").trigger('change.select2');
                    toggleTpiName();
                    jQuery('#OfferModal').find('#tpi_name').val(lnr.tpi_name ?? "");
                    jQuery('#OfferModal').find('#condition_of_sample').val(lnr.condition_of_sample ?? "");
                    jQuery('#OfferModal').find('#is_equipment_available').val(lnr.is_equipment_available ?? "Yes").trigger('change.select2');
                    jQuery('#OfferModal').find('#competent_personnel_available').val(lnr.competent_personnel_available ?? "Yes").trigger('change.select2');
                    jQuery('#OfferModal').find('#is_test_sub_contracted').val(lnr.is_test_sub_contracted ?? "Yes").trigger('change.select2');
                    jQuery('#OfferModal').find('#test_feasible').val(lnr.test_feasible ?? "Yes").trigger('change.select2');
                    jQuery('#OfferModal').find('#all_test_parameters_are_in_accredited_scope').val(lnr.all_test_parameters_are_in_accredited_scope ?? "Yes").trigger('change.select2');
                    jQuery('#OfferModal').find('#required_statement_of_conformity').val(lnr.required_statement_of_conformity ?? "Yes").trigger('change.select2');
                } else {
                    jQuery('#OfferModal').find('#sample_drawn_by').val("");
                    ['is_any_tpi_witness', 'is_equipment_available', 'competent_personnel_available', 'is_test_sub_contracted', 'test_feasible', 'all_test_parameters_are_in_accredited_scope', 'required_statement_of_conformity'].forEach(function (f) {
                        jQuery('#OfferModal').find('#' + f).val('Yes').trigger('change.select2');
                    });
                    toggleTpiName();
                    jQuery('#OfferModal').find('#tpi_name').val("");
                    jQuery('#OfferModal').find('#condition_of_sample').val("");
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

    let hasRepairDetail = offer_details_data.some(function (item) {
        return item.mode !== "Delete" && item.process_type === 'Repair';
    });
    let parentInUse = offer_details_data.some(function (item) {
        return item.mode !== "Delete" && (item.in_use == true || item.in_use == "true" || (item.used_qty && parseFloat(item.used_qty) > 0));
    });

    if (hasRepairDetail || parentInUse) {
        setSelect2Readonly(jQuery('#OfferModal #customer_id'), true);
        jQuery('#OfferModal #customer_id').addClass('skip-tab');
        lockParentRadios(true);
    } else {
        if (isAdd) {
            setSelect2Readonly(jQuery('#OfferModal #customer_id'), false);
            jQuery('#OfferModal #customer_id').removeClass('skip-tab');
            lockParentRadios(false);
        } else {
            setSelect2Readonly(jQuery('#OfferModal #customer_id'), true);
            lockParentRadios(false);
        }
    }
}

function focusInitialOfferField() {
    let modal = jQuery('#OfferModal');
    let nablRadio = modal.find('input[name="nabl_type_fix"]');
    if (nablRadio.hasClass('skip-tab')) {
        let testAtRadio = modal.find('input[name="test_at_fix"]');
        if (testAtRadio.hasClass('skip-tab')) {
            let jobTypeRadio = modal.find('input[name="job_type_fix"]');
            if (jobTypeRadio.hasClass('skip-tab')) {
                modal.find('#offer_sequence').focus().select();
            } else {
                modal.find('input[name="job_type_fix"]:checked').focus();
            }
        } else {
            modal.find('input[name="test_at_fix"]:checked').focus();
        }
    } else {
        modal.find('input[name="nabl_type_fix"]:checked').focus();
    }
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
    const input = document.getElementById('inward_job_desc_id');
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
function renderCopyMaterialInwardTable(data) {
    var $table = jQuery('#CopyMaterialInwardDetailsTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().clear().destroy();
    }

    var tbody = $table.find('tbody');
    tbody.empty();

    jQuery.each(data, function (index, item) {
        var tr = '<tr>';
        tr += '<td><input class="form-check-input" type="radio" name="copy_inward_detail_radio" value="' + index + '"></td>';
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
    var processType = jQuery('#OfferDetailsModal input[name="process_type"]:checked').val() || 'Fresh';

    // In EDIT mode or REPAIR case: always keep Past Inward button disabled
    if (isSavedDetailEdit || processType === 'Repair') {
        jQuery('#copyMaterialBtn').prop('disabled', true);
        window.copyMaterialInwardDetailsData = [];
        return;
    }

    // In ADD mode: check if all required filter fields are selected
    var customer = jQuery('#customer_id').val();
    var jobTypeCasting = jQuery('input[name="job_type_fix"]:checked').val();
    var typeOfJob = jQuery('#type_of_job_id').val();
    var jobDescription = jQuery('#inward_job_desc_id').val();

    if (!customer || !jobTypeCasting || !typeOfJob || !jobDescription) {
        jQuery('#copyMaterialBtn').prop('disabled', true);
        window.copyMaterialInwardDetailsData = [];
        return;
    }

    // Query backend to verify if past inward records actually exist
    jQuery.ajax({
        url: 'get-old-inward-details',
        type: 'POST',
        dataType: 'json',
        data: {
            customer: customer,
            job_type_casting: jobTypeCasting,
            type_of_job: typeOfJob,
            job_description: jobDescription,
            type_of_test: jQuery('#type_of_testing_id_fix').val(),
            _token: jQuery('input[name="_token"]').val()
        },
        success: function (response) {
            if (response && (response.status === 'success' || response.response_code == 1) && response.data && response.data.length > 0) {
                jQuery('#copyMaterialBtn').prop('disabled', false);
                window.copyMaterialInwardDetailsData = response.data;
            } else {
                jQuery('#copyMaterialBtn').prop('disabled', true);
                window.copyMaterialInwardDetailsData = [];
            }
        },
        error: function () {
            jQuery('#copyMaterialBtn').prop('disabled', true);
            window.copyMaterialInwardDetailsData = [];
        }
    });
}

jQuery(document).on('change', '#customer_id, input[name="job_type_fix"], #type_of_job_id, #inward_job_desc_id, #type_of_testing_id_fix', function () {
    validateCopyButton();
});

jQuery('#OfferDetailsForm').on('reset', function () {
    setTimeout(function () {
        validateCopyButton();
    }, 100);
});

jQuery(document).on('click', '#copyMaterialBtn', function (e) {
    e.preventDefault();

    if (window.copyMaterialInwardDetailsData && window.copyMaterialInwardDetailsData.length > 0) {
        renderCopyMaterialInwardTable(window.copyMaterialInwardDetailsData);
        jQuery('#CopyMaterialInwardDetailsModal').modal('show');
        return;
    }

    var copyBtn = jQuery(this);
    var originalText = copyBtn.text();
    copyBtn.prop('disabled', true);

    jQuery.ajax({
        url: 'get-old-inward-details',
        type: 'POST',
        dataType: 'json',
        data: {
            customer: jQuery('#customer_id').val(),
            job_type_casting: jQuery('input[name="job_type_fix"]:checked').val(),
            type_of_job: jQuery('#type_of_job_id').val(),
            job_description: jQuery('#inward_job_desc_id').val(),
            type_of_test: jQuery('#type_of_testing_id_fix').val(),
            _token: jQuery('input[name="_token"]').val()
        },
        success: function (response) {
            if (response && (response.status === 'success' || response.response_code == 1) && response.data && response.data.length > 0) {
                window.copyMaterialInwardDetailsData = response.data;
                renderCopyMaterialInwardTable(response.data);
                jQuery('#CopyMaterialInwardDetailsModal').modal('show');
            } else {
                window.copyMaterialInwardDetailsData = [];
                copyBtn.prop('disabled', true);
                toastr.info('No past inward records found for this customer and job description.');
            }
        },
        error: function () {
            window.copyMaterialInwardDetailsData = [];
            copyBtn.prop('disabled', true);
        },
        complete: function () {
            copyBtn.text(originalText);
        }
    });
});

jQuery('#CopyMaterialInwardDetailsModal').on('shown.bs.modal', function () {
    let $table = jQuery('#CopyMaterialInwardDetailsTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().columns.adjust().draw();
        if (typeof initColumnSearch === 'function') {
            initColumnSearch('#CopyMaterialInwardDetailsTable', [0], 'common_search');
        }
    }
});

jQuery(document).on('click', '#submitCopyMaterialInwardDetailsBtn', function () {
    var selectedRadio = jQuery('input[name="copy_inward_detail_radio"]:checked');
    if (selectedRadio.length === 0) {
        toastr.warning('Please Select At Least One Record.');
        return;
    }

    var index = selectedRadio.val();
    var data = window.copyMaterialInwardDetailsData[index];

    if (data) {
        if (data.material_id) { jQuery('#material_id').val(data.material_id).trigger('change'); }
        if (data.part_no || data.part_name) { jQuery('#part_no').val(data.part_no || data.part_name); }
        if (data.drg_no) { jQuery('#drg_no').val(data.drg_no); }
        if (data.heat_no) { jQuery('#heat_no').val(data.heat_no); }
        if (data.rt_no) { jQuery('#rt_no').val(data.rt_no); }
        if (data.product_code) { jQuery('#product_code').val(data.product_code); }
        if (data.thickness) { jQuery('#thickness').val(data.thickness); }
        if (data.area_of_coverage_id) { jQuery('#area_of_coverage_id').val(data.area_of_coverage_id).trigger('change'); }
        if (data.procedure_ref_id) { jQuery('#procedure_ref_id').val(data.procedure_ref_id).trigger('change'); }
        if (data.evaluation_as_per_id) { jQuery('#evaluation_as_per_id').val(data.evaluation_as_per_id).trigger('change'); }
        if (data.acceptance_standard_id) { jQuery('#acceptance_standard_id').val(data.acceptance_standard_id).trigger('change'); }
        if (data.approx_value) { jQuery('#approx_value').val(data.approx_value); }
        if (data.approx_weight) { jQuery('#approx_weight').val(data.approx_weight); }
        if (data.quantity) { jQuery('#quantity').val(data.quantity); }
        if (data.remark) { jQuery('#remark').val(data.remark); }

        jQuery('#CopyMaterialInwardDetailsModal').modal('hide');

        window.didCopyOffer = true;
    }
});

jQuery('#CopyMaterialInwardDetailsModal').on('hidden.bs.modal', function () {
    if (window.didCopyOffer) {
        window.didCopyOffer = false;
        var typeOfJob = jQuery('#type_of_job_id');
        if (typeOfJob.hasClass("select2-hidden-accessible")) {
            typeOfJob.next('.select2-container').find('.select2-selection').focus();
        } else {
            typeOfJob.focus();
        }
    }
});

/* ---------------------------------------------------------------------
 *  Pending Repair Reports Logic
 * ------------------------------------------------------------------- */
jQuery(document).ready(function () {
    jQuery('#OfferDetailsForm').on('change', 'input[name="process_type"]', function () {
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
        jQuery('#OfferDetailsForm input[name="process_type"][value="Repair"]').prop('checked', true);
        jQuery('#OfferDetailsForm #type_of_testing_id_fix').val('RT').trigger('change.select2');

        // Auto-fill detail fields from pending repair report data
        if (reportData.type_of_job_id) {
            jQuery('#OfferDetailsForm #type_of_job_id').val(reportData.type_of_job_id).trigger('change.select2');
        }
        if (reportData.job_desc_id) {
            jQuery('#OfferDetailsForm #inward_job_desc_id').val(reportData.job_desc_id).trigger('change.select2');
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
    setRadioReadonly("#OfferDetailsForm input[name='process_type']", isReadonly);

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

    form.find('#part_no').prop('readonly', lockDetailFields);
    form.find('#drg_no').prop('readonly', lockDetailFields);
    form.find('#heat_no').prop('readonly', lockDetailFields);
    form.find('#rt_no').prop('readonly', lockDetailFields);
    form.find('#product_code').prop('readonly', lockDetailFields);

    let allSel = '#type_of_testing_id_fix, #type_of_job_id, #inward_job_desc_id, #material_id, #area_of_coverage_id, #procedure_ref_id, #evaluation_as_per_id, #acceptance_standard_id, #part_no, #drg_no, #heat_no, #rt_no, #product_code';
    form.find(allSel).removeClass('skip-tab');

    if (isReadonly) {
        form.find('#type_of_testing_id_fix').addClass('skip-tab');
        if (lockDetailFields) {
            let detailSel = '#type_of_job_id, #inward_job_desc_id, #material_id, #area_of_coverage_id, #procedure_ref_id, #evaluation_as_per_id, #acceptance_standard_id, #part_no, #drg_no, #heat_no, #rt_no, #product_code';
            form.find(detailSel).addClass('skip-tab');
        }
    }
}

function resetPendingRepairFields() {
    let form = jQuery('#OfferDetailsForm');
    form.find('#test_report_rt_id').val(null);
    form.find('#revision_number').val('');

    setRadioReadonly("#OfferDetailsForm input[name='process_type']", false);
    form.find('input[name="process_type"][value="Fresh"]').prop('checked', true);

    setRepairFieldsReadonly(false, false);

    form.find('#type_of_testing_id_fix').off('select2:opening.s2readonly');

    form.find('#quantity').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
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
