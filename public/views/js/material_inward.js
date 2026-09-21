/* =====================================================================
 *  Material Inward (parent + child) � mirrors service_po.js structure.
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


                filterTestsByNabl();

                jQuery('#MaterialInwardModal').find('#customer_id').val(d.customer_id ?? "").trigger('change.select2');
                jQuery('#MaterialInwardModal').find('#dc_no').val(d.dc_no ?? "");
                jQuery('#MaterialInwardModal').find('#dc_date').val(d.dc_date ?? "");
                jQuery('#MaterialInwardModal').find('#po_no').val(d.po_no ?? "");
                jQuery('#MaterialInwardModal').find('#po_date').val(d.po_date ?? "");
                jQuery('#MaterialInwardModal').find('#sample_drawn_by').val(d.sample_drawn_by ?? "");

                jQuery('#MaterialInwardModal').find('#is_any_tpi_witness').val(d.is_any_tpi_witness ?? "").trigger('change.select2');
                toggleTpiName();
                jQuery('#MaterialInwardModal').find('#tpi_name').val(d.tpi_name ?? "");

                jQuery('#MaterialInwardModal').find('#condition_of_sample').val(d.condition_of_sample ?? "");
                jQuery('#MaterialInwardModal').find('#is_equipment_available').val(d.is_equipment_available ?? "").trigger('change.select2');
                jQuery('#MaterialInwardModal').find('#competent_personnel_available').val(d.competent_personnel_available ?? "").trigger('change.select2');
                jQuery('#MaterialInwardModal').find('#is_test_sub_contracted').val(d.is_test_sub_contracted ?? "").trigger('change.select2');
                jQuery('#MaterialInwardModal').find('#test_feasible').val(d.test_feasible ?? "").trigger('change.select2');
                jQuery('#MaterialInwardModal').find('#all_test_parameters_are_in_accredited_scope').val(d.all_test_parameters_are_in_accredited_scope ?? "").trigger('change.select2');
                jQuery('#MaterialInwardModal').find('#required_statement_of_conformity').val(d.required_statement_of_conformity ?? "").trigger('change.select2');
                jQuery('#MaterialInwardModal').find('#additional_requirement_from_customer').val(d.additional_requirement_from_customer ?? "");
                jQuery('#MaterialInwardModal').find('#special_note').val(d.special_note ?? "");
                jQuery('#MaterialInwardModal').find('#prepared_by_user_id').val(d.prepared_by_user_id ?? "").trigger('change.select2');

                material_inward_details_data = [];
                if (data.mi_details_data != "" && data.mi_details_data.length > 0) {
                    data.mi_details_data.forEach(function (item) {
                        if (item.material_inward_detail_id && !item.material_inward_details_id) {
                            item.material_inward_details_id = item.material_inward_detail_id;
                        }
                    });
                    material_inward_details_data.push(...data.mi_details_data);
                    fillMaterialInwardDetailTable();
                }

                // Customer is always readonly in Edit mode
                setSelect2Readonly(jQuery('#MaterialInwardModal').find('#customer_id'), true);
                jQuery('#MaterialInwardModal').find('#customer_id').addClass('skip-tab');

                // var parentInUse = material_inward_details_data.some(item => item.in_use == true || item.in_use == "true" || (item.used_qty && parseFloat(item.used_qty) > 0));
                // if (parentInUse) {
                //     jQuery('#MaterialInwardModal').find('#material_inward_date').prop('readonly', true).css('pointer-events', 'none').addClass('skip-tab');
                //     jQuery('#MaterialInwardModal').find('#material_inward_sequence').prop('readonly', true).addClass('skip-tab');
                // } else {
                //     jQuery('#MaterialInwardModal').find('#material_inward_date').prop('readonly', false).css('pointer-events', 'auto').removeClass('skip-tab');
                //     jQuery('#MaterialInwardModal').find('#material_inward_sequence').prop('readonly', false).removeClass('skip-tab');
                // }

                // Reset datepicker maxDate to default (endDate) first
                jQuery('#MaterialInwardModal').find('#material_inward_date').datepicker('option', 'maxDate', endDate);
                var parentInUse = material_inward_details_data.some(item => item.in_use == true || item.in_use == "true" || (item.used_qty && parseFloat(item.used_qty) > 0));
                if (parentInUse) {
                    lockParentRadios(true);
                    jQuery('#MaterialInwardModal').find('#material_inward_sequence').prop('readonly', true).addClass('skip-tab');
                } else {
                    lockParentRadios(false);
                    jQuery('#MaterialInwardModal').find('#material_inward_sequence').prop('readonly', false).removeClass('skip-tab');
                }
                updateParentRadiosLockState();
                // Inward date is always editable now
                jQuery('#MaterialInwardModal').find('#material_inward_date').prop('readonly', false).css('pointer-events', 'auto').removeClass('skip-tab');
                // Store and set min_report_date
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
                jQuery('#MaterialInwardModal').find('#dc_no').focus().select();
            } else {
                jQuery('#MaterialInwardModal').find('#material_inward_sequence').focus().select();
            }
        }, 100);
    } else {
        setTimeout(() => {
            focusInitialMaterialInwardField();
        }, 100);
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
    jQuery('#MaterialInwardModal').find('#id').val('');
    jQuery('#MaterialInwardModal').find('#old_inward_date').val('');
    setSelect2Readonly(jQuery('#MaterialInwardModal').find('#customer_id'), false);
    jQuery('#MaterialInwardModal').find('#customer_id').removeClass('skip-tab');
    // jQuery('#MaterialInwardModal').find('#material_inward_date').prop('readonly', false).css('pointer-events', 'auto').removeClass('skip-tab');
    jQuery('#MaterialInwardModal').find('#material_inward_date').prop('readonly', false).css('pointer-events', 'auto').removeClass('skip-tab').datepicker('option', 'maxDate', endDate).data('min-report-date', '');
    jQuery('#MaterialInwardModal').find('#material_inward_sequence').prop('readonly', false).removeClass('skip-tab');
    lockParentRadios(false);
    window.lastCustomerTestingType = null;
    material_inward_details_data = [];
    jQuery('#MaterialInwardDetailTable tbody').empty().append('<tr><td colspan="19" id="noDetails" class="text-center">No Material Inward Details Added</td></tr>');

    // Reset select2 dropdowns
    jQuery('#MaterialInwardModal').find('#customer_id').val('').trigger('change');
    ['is_any_tpi_witness', 'is_equipment_available', 'competent_personnel_available', 'is_test_sub_contracted', 'test_feasible', 'all_test_parameters_are_in_accredited_scope', 'required_statement_of_conformity'].forEach(function (f) {
        jQuery('#MaterialInwardModal').find('#' + f).val('Yes').trigger('change.select2');
    });
    jQuery('#MaterialInwardModal').find('#prepared_by_user_id').val(loginUserId).trigger('change.select2');
    toggleTpiName();

    // Check defaults
    jQuery('#MaterialInwardModal').find('input[name="test_at_fix"][value="At Lab"]').prop('checked', true);
    jQuery('#MaterialInwardModal').find('input[name="job_type_fix"][value="Non-Welding"]').prop('checked', true);
    if (typeof window.locationNablDefault !== 'undefined') {
        jQuery('#MaterialInwardModal').find('input[name="nabl_type_fix"][value="' + window.locationNablDefault + '"]').prop('checked', true);
    }
    filterTestsByNabl();

    jQuery('#MaterialInwardModal').find('#add_new').hide();
    jQuery('#MaterialInwardModal').find('#preview_btn').hide();

    if (fetchLatest) {
        getLatestMaterialInwardNo();
        getLNRData();
    }
}
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

// On blur date validation
jQuery('#MaterialInwardModal').on('blur', '#material_inward_date', function () {
    validateInwardDateAgainstReports();
});

/* ---------------------------------------------------------------------
 *  Conditional fields
 * ------------------------------------------------------------------- */
jQuery('#MaterialInwardModal').on('change', '#is_any_tpi_witness , input[name="nabl_type_fix"]', function () {
    toggleTpiName();
});
function toggleTpiName() {
    var val = jQuery('#MaterialInwardModal').find('#is_any_tpi_witness').val();
    var nablType = jQuery('#MaterialInwardModal').find('input[name="nabl_type_fix"]:checked').val();
    var tpiElem = jQuery('#MaterialInwardModal').find('#tpi_name');
    var astricElem = jQuery('#MaterialInwardModal').find('#tpi_name_astric');
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
// HOOK: wire the accredited-test source per location when available.
jQuery('#MaterialInwardModal').on('change', 'input[name="nabl_type_fix"]', function () {
    filterTestsByNabl();
    var formId = jQuery('#MaterialInwardModal').find('#id').val();
    var customerId = jQuery('#MaterialInwardModal').find('#customer_id').val();
    if (!formId && customerId) {
        getLNRData(customerId);
    }
});
/* ---------------------------------------------------------------------
 *  Process Type & Observation Sheet Rules
 * ------------------------------------------------------------------- */
/*function updateIsObservationSheet() {
    let nablType = jQuery('#MaterialInwardModal').find('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
    let processType = jQuery('#MaterialInwardDetailsModal').find('input[name="process_type"]:checked').val() || 'Fresh';
    let isObs = (nablType === 'NABL' || processType === 'Repair') ? 'Yes' : 'No';
    jQuery('#MaterialInwardDetailsModal #is_observation_sheet').val(isObs);
    return isObs;
}*/

function handleProcessTypeRules(isUserChange = false) {
    let processType = jQuery('#MaterialInwardDetailsForm input[name="process_type"]:checked').val() || 'Fresh';
    let $qtyInput = jQuery('#MaterialInwardDetailsForm #quantity');

    if (processType === 'Repair') {
        $qtyInput.val('1').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
    } else {
        $qtyInput.prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
        if (isUserChange) {
            $qtyInput.val('');
        }
    }
    //updateIsObservationSheet();
}

function handleTestingTypeProcessTypeRules() {
    let testType = jQuery('#MaterialInwardDetailsForm #type_of_testing_id_fix').val();
    let $radios = jQuery('#MaterialInwardDetailsForm input[name="process_type"]');
    let $container = jQuery('#process_type_container');

    // Remove pointer-events from container so Copy button is not affected
    $container.css('pointer-events', 'auto').css('opacity', '1');

    if (testType === 'RT') {
        setRadioReadonly("#MaterialInwardDetailsForm input[name='process_type']", false);
        $radios.prop('disabled', false);
        $container.find('.form-check').css('pointer-events', 'auto').css('opacity', '1');
    } else {
        jQuery('#MaterialInwardDetailsForm input[name="process_type"][value="Fresh"]').prop('checked', true);
        setRadioReadonly("#MaterialInwardDetailsForm input[name='process_type']", true);
        $radios.prop('disabled', true);
        $container.find('.form-check').css('pointer-events', 'none').css('opacity', '0.7');
        handleProcessTypeRules();
    }
    // updateIsObservationSheet();
}

jQuery('#MaterialInwardDetailsForm').on('change', '#type_of_testing_id_fix', function () {
    handleTestingTypeProcessTypeRules();
});

jQuery('#MaterialInwardDetailsForm').on('change', 'input[name="process_type"]', function () {
    handleProcessTypeRules(true);
});

// jQuery('#MaterialInwardModal').on('change', 'input[name="nabl_type_fix"]', function () {
//     updateIsObservationSheet();
// });

function filterTestsByNabl() {
    // var nablType = (typeof window.locationNablDefault !== 'undefined') ? window.locationNablDefault : 'Non NABL';
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
    let currentVal = $select.attr('data-target-val') || $select.val();
    $select.removeAttr('data-target-val');
    $select.empty().append('<option value="">Select Type of Test</option>');

    if (currentVal && !filtered.some(opt => opt.value === currentVal)) {
        let isEdit = jQuery('#MaterialInwardDetailsModal #form_type').val() === 'edit';
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

jQuery('#MaterialInwardDetailsModal').on('show.bs.modal', function () {
    filterTestsByNabl();
    var formType = jQuery('#MaterialInwardDetailsModal').find("#form_type").val();
    if (formType !== 'edit') {
        resetPendingRepairFields();
        jQuery('#MaterialInwardDetailsModal').find("#quantity").removeAttr("min");

        setSelect2Readonly(jQuery('#MaterialInwardDetailsModal').find('#type_of_testing_id_fix'), false);
        jQuery('#MaterialInwardDetailsModal').find('#type_of_testing_id_fix').removeClass('skip-tab');

        setRadioReadonly("#MaterialInwardDetailsForm input[name='process_type']", false);

        setSelect2Readonly(jQuery('#MaterialInwardDetailsModal').find('#type_of_job_id'), false);
        jQuery('#MaterialInwardDetailsModal').find('#type_of_job_id').removeClass('skip-tab');

        setSelect2Readonly(jQuery('#MaterialInwardDetailsModal').find('#inward_job_desc_id'), false);
        jQuery('#MaterialInwardDetailsModal').find('#inward_job_desc_id').removeClass('skip-tab');

        setSelect2Readonly(jQuery('#MaterialInwardDetailsModal').find('#area_of_coverage_id'), false);
        jQuery('#MaterialInwardDetailsModal').find('#area_of_coverage_id').removeClass('skip-tab');

        if (window.lastCustomerTestingType) {
            jQuery('#MaterialInwardDetailsModal').find('#type_of_testing_id_fix').val(window.lastCustomerTestingType).trigger('change.select2');
        }
    }
});

jQuery('#MaterialInwardDetailsModal').on('shown.bs.modal', function () {
    setTimeout(() => {
        var parentId = jQuery('#MaterialInwardModal').find("#id").val();
        var isParentStored = (parentId && parentId !== "" && parentId != 0 && parentId !== "0");
        var formType = jQuery('#MaterialInwardDetailsModal').find("#form_type").val();
        let processType = jQuery('#MaterialInwardDetailsModal input[name="process_type"]:checked').val() || 'Fresh';
        let testReportRtId = jQuery('#MaterialInwardDetailsModal #test_report_rt_id').val();
        let isRepair = processType === 'Repair' || (testReportRtId && testReportRtId !== '' && testReportRtId != 0);

        if (formType === 'edit') {
            var formIndx = jQuery('#MaterialInwardDetailsModal').find("#form_index").val();
            if (formIndx !== "") {
                var d = material_inward_details_data[formIndx];
                let isRowInUse = d && (d.in_use == true || d.in_use == "true" || (d.used_qty && parseFloat(d.used_qty) > 0));
                let isRepairRow = d && (d.process_type === 'Repair' || (d.test_report_rt_id && parseInt(d.test_report_rt_id) > 0));

                if (isRepairRow) {
                    setRepairFieldsReadonly(true);
                }

                if (isRowInUse) {
                    setSelect2Readonly(jQuery('#MaterialInwardDetailsModal').find('#type_of_testing_id_fix'), true);
                    jQuery('#MaterialInwardDetailsModal').find('#type_of_testing_id_fix').addClass('skip-tab');

                    setRadioReadonly("#MaterialInwardDetailsForm input[name='process_type']", true);
                    setSelect2Readonly(jQuery('#MaterialInwardDetailsModal').find('#type_of_job_id'), true);
                    jQuery('#MaterialInwardDetailsModal').find('#type_of_job_id').addClass('skip-tab');
                    setSelect2Readonly(jQuery('#MaterialInwardDetailsModal').find('#inward_job_desc_id'), true);
                    jQuery('#MaterialInwardDetailsModal').find('#inward_job_desc_id').addClass('skip-tab');
                    setSelect2Readonly(jQuery('#MaterialInwardDetailsModal').find('#area_of_coverage_id'), true);
                    jQuery('#MaterialInwardDetailsModal').find('#area_of_coverage_id').addClass('skip-tab');
                }

                let focusTarget = isRepairRow ? '#thickness' : (isRowInUse ? '#part_no' : null);
                if (focusTarget) {
                    setTimeout(() => {
                        let $elem = jQuery('#MaterialInwardDetailsModal').find(focusTarget);
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
                jQuery('#MaterialInwardDetailsModal').find('#thickness').focus();
            }, 50);
            return;
        }

        setRepairFieldsReadonly(false);
        if (formType !== 'edit' && window.lastCustomerTestingType) {
            jQuery('#MaterialInwardDetailsModal').find('#type_of_testing_id_fix').val(window.lastCustomerTestingType).trigger('change.select2');
        }
        handleTestingTypeProcessTypeRules();
    }, 150);
});

/* ---------------------------------------------------------------------
 *  Detail modal: Job Description -> Part dependency
 * ------------------------------------------------------------------- */
jQuery('#MaterialInwardDetailsForm #inward_job_desc_id').on('change', function () {
    let parts = jQuery(this).find(':selected').data('parts');
    let $partSelect = jQuery('#MaterialInwardDetailsForm #part_id');
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

// jQuery('#MaterialInwardDetailsForm #part_id').on('change', function () {
//     var drg = jQuery(this).find('option:selected').data('drg_no') || '';
//     jQuery('#MaterialInwardDetailsForm #drg_no').val(drg);
// });

function suggestInwardPartNo(e, $this) {
    var jobId = jQuery('#MaterialInwardDetailsForm #inward_job_desc_id').val() || '';
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
function removeMaterialInwardDetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        let item = material_inward_details_data[formIndx];
        if ((item.material_inward_details_id && item.material_inward_details_id != 0) || (item.material_inward_detail_id && item.material_inward_detail_id != 0)) {
            item.mode = "Delete";
        } else {
            material_inward_details_data.splice(formIndx, 1);
        }
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

    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    thisForm.find("#material_inward_details_id").val(d.material_inward_details_id ?? d.material_inward_detail_id ?? 0);
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

        setRadioReadonly("#MaterialInwardDetailsForm input[name='process_type']", true);

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
function fillMaterialInwardDetailTable() {
    if (material_inward_details_data.length > 0) {
        var tblHtml = '';
        for (let key in material_inward_details_data) {
            let item = material_inward_details_data[key];
            if (item.mode == "Delete") continue;
            let formIndx = material_inward_details_data.indexOf(item);
            if (jQuery('#MaterialInwardDetailTable tbody').find('#noDetails').length > 0) {
                jQuery('#MaterialInwardDetailTable tbody').empty();
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
            tblHtml += in_use ? DetailsActionDropdown('editMaterialInwardDetails') : DetailsActionDropdown('editMaterialInwardDetails', 'removeMaterialInwardDetails');
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
        jQuery('#MaterialInwardDetailTable tbody').empty().append(tblHtml);
        if (jQuery('#MaterialInwardDetailTable tbody tr').length == 0) {
            jQuery('#MaterialInwardDetailTable tbody').append('<tr><td colspan="19" id="noDetails" class="text-center">No Material Inward Details Added</td></tr>');
        }
    } else {
        jQuery('#MaterialInwardDetailTable tbody').empty().append('<tr><td colspan="19" id="noDetails" class="text-center">No Material Inward Details Added</td></tr>');
    }

    updateParentRadiosLockState();
}

/* ---------------------------------------------------------------------
 *  Detail modal lifecycle
 * ------------------------------------------------------------------- */
jQuery('#MaterialInwardDetailsForm').on('reset', function () {
    setTimeout(function () {
        jQuery('#MaterialInwardDetailsForm input[name="process_type"][value="Fresh"]').prop('checked', true);
        handleTestingTypeProcessTypeRules();
        handleProcessTypeRules();
    }, 50);
});

jQuery('#MaterialInwardDetailsModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#MaterialInwardDetailsModal');
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    thisModal.find("#material_inward_details_id").val(0);
    thisModal.find("#quantity").removeAttr("min");
    setSelect2Readonly(thisModal.find('#type_of_testing_id_fix'), false);
    thisModal.find('#type_of_testing_id_fix').removeClass('skip-tab');
    setRadioReadonly("#MaterialInwardDetailsForm input[name='process_type']", false);
    setSelect2Readonly(thisModal.find('#type_of_job_id'), false);
    thisModal.find('#type_of_job_id').removeClass('skip-tab');
    setSelect2Readonly(thisModal.find('#inward_job_desc_id'), false);
    thisModal.find('#inward_job_desc_id').removeClass('skip-tab');
    setSelect2Readonly(thisModal.find('#area_of_coverage_id'), false);
    thisModal.find('#area_of_coverage_id').removeClass('skip-tab');
    resetPendingRepairFields();
    jQuery('#MaterialInwardDetailsForm').trigger("reset");
    jQuery('#MaterialInwardDetailsForm input[name="process_type"][value="Fresh"]').prop('checked', true);

    handleTestingTypeProcessTypeRules();
    handleProcessTypeRules();
    jQuery('#inward_part_no_list').empty();
    jQuery('#inward_drg_no_list').empty();
    jQuery('#inward_product_code_list').empty();
    // jQuery('#MaterialInwardDetailsForm #part_id').empty().append('<option value="">Select Part No.</option>').trigger('change.select2');
    // jQuery('#MaterialInwardDetailsForm').find('.select2-hidden-accessible').val('').trigger('change.select2');
    // jQuery('#MaterialInwardDetailsForm').removeClass('was-validated');
    this.dataset.customHideFocus = 'true';
    const input = jQuery("#MaterialInwardModal").find("input[name='sample_drawn_by']");
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 200);
    }
});

/* ---------------------------------------------------------------------
 *  Detail form submit (add / update a row in the in-memory grid)
 * ------------------------------------------------------------------- */
jQuery('#MaterialInwardDetailsForm').on('submit', function (e) {
    e.preventDefault();
    let form = this;
    let thisModal = jQuery('#MaterialInwardDetailsModal');

    if (!form.checkValidity()) {
        e.stopPropagation();
        jQuery(form).addClass('was-validated');
        return;
    }

    let currentProcessType = jQuery('#MaterialInwardDetailsForm input[name="process_type"]:checked').val() || 'Fresh';
    let currentTestReportRtId = jQuery('#MaterialInwardDetailsForm #test_report_rt_id').val();
    if (currentProcessType === 'Repair' && (!currentTestReportRtId || currentTestReportRtId === '' || currentTestReportRtId == 0 || currentTestReportRtId === '0')) {
        toastr.error('Please Select At Least One Report Form Pending.');
        return false;
    }

    if (currentProcessType === 'Repair' && currentTestReportRtId) {
        let isEditMode = jQuery('#MaterialInwardDetailsForm #form_type').val() === "edit";
        let editFormIndex = jQuery('#MaterialInwardDetailsForm #form_index').val();
        let isDuplicate = material_inward_details_data.some(function (row, idx) {
            if (row.mode === "Delete") return false;
            if (isEditMode && idx == editFormIndex) return false;
            return row.test_report_rt_id && (row.test_report_rt_id == currentTestReportRtId);
        });
        if (isDuplicate) {
            toastr.error("Duplicate Report Found!");
            return false;
        }
    }



    var qty = parseInt(jQuery('#MaterialInwardDetailsForm #quantity').val()) || 0;
    if (qty <= 0) { toastr.error('Enter Quantity greater than 0.'); return; }

    var minQty = parseInt(jQuery('#MaterialInwardDetailsForm #quantity').attr('min')) || 0;
    if (qty < minQty) {
        toastr.error('Quantity cannot be less than used quantity (' + minQty + ').');
        return;
    }

    // form -> object
    let formValue = {};
    jQuery.each(jQuery('#MaterialInwardDetailsForm').serializeArray(), function (i, f) { formValue[f.name] = f.value; });

    let processTypeVal = jQuery('#MaterialInwardDetailsModal input[name="process_type"]:checked').val() || 'Fresh';
    formValue.process_type = processTypeVal;
    //formValue.is_observation_sheet = updateIsObservationSheet();
    // display names (for the grid + edit re-fill)
    formValue.type_of_job_name = jQuery('#type_of_job_id option:selected').text();
    formValue.job_description_name = jQuery('#inward_job_desc_id option:selected').text();
    formValue.part_no = jQuery('#MaterialInwardDetailsForm #part_no').val() || '';
    formValue.drg_no = jQuery('#MaterialInwardDetailsForm #drg_no').val() || '';
    formValue.part_name = formValue.part_no;
    formValue.material_name = jQuery('#material_id option:selected').text();
    formValue.area_of_coverage_name = jQuery('#area_of_coverage_id option:selected').val() ? jQuery('#area_of_coverage_id option:selected').text() : '';
    formValue.procedure_ref_name = jQuery('#procedure_ref_id option:selected').val() ? jQuery('#procedure_ref_id option:selected').text() : '';
    formValue.evaluation_as_per_name = jQuery('#evaluation_as_per_id option:selected').val() ? jQuery('#evaluation_as_per_id option:selected').text() : '';
    formValue.acceptance_standard_name = jQuery('#acceptance_standard_id option:selected').val() ? jQuery('#acceptance_standard_id option:selected').text() : '';

    if (formValue.form_type == "edit") {
        formValue.mode = (formValue.material_inward_details_id && formValue.material_inward_details_id != 0) ? "Update" : "Insert";
        material_inward_details_data[formValue.form_index] = {
            ...material_inward_details_data[formValue.form_index],
            ...formValue
        };
        var d = material_inward_details_data[formValue.form_index];
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
        tblHtml += in_use ? DetailsActionDropdown('editMaterialInwardDetails') : DetailsActionDropdown('editMaterialInwardDetails', 'removeMaterialInwardDetails');
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

        jQuery('#MaterialInwardDetailTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
        toastSuccess("Record Updated.");
        updateParentRadiosLockState();
        thisModal.modal('hide');
    } else {
        formValue.mode = "Insert";
        formValue.material_inward_details_id = 0;
        material_inward_details_data.push(formValue);
        let formIndx = material_inward_details_data.indexOf(formValue);
        if (jQuery('#MaterialInwardDetailTable tbody').find('#noDetails').length > 0) {
            jQuery('#MaterialInwardDetailTable tbody').empty();
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
        tblHtml += DetailsActionDropdown('editMaterialInwardDetails', 'removeMaterialInwardDetails');
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

        jQuery('#MaterialInwardDetailTable tbody').append(tblHtml);
        toastSuccess("Record Inserted.");
        updateParentRadiosLockState();

        // reset for next entry
        form.reset();
        resetPendingRepairFields();
        setTimeout(function () {
            $(form).removeClass('was-validated');
            $(form).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            jQuery('#MaterialInwardDetailsForm').find('.error').removeClass('error');
        }, 150);
        jQuery('#MaterialInwardDetailsForm').find('.js-example-basic-single').val('').trigger('change.select2');
        if (window.lastCustomerTestingType) {
            jQuery('#MaterialInwardDetailsForm #type_of_testing_id_fix').val(window.lastCustomerTestingType).trigger('change.select2');
        }
        jQuery('#MaterialInwardDetailsForm #part_no').val('');
        jQuery('#MaterialInwardDetailsForm #drg_no').val('');
        jQuery('#MaterialInwardDetailsForm #heat_no').val('');
        jQuery('#MaterialInwardDetailsForm #rt_no').val('');
        jQuery('#MaterialInwardDetailsForm #product_code').val('');
        jQuery('#MaterialInwardDetailsForm #thickness').val('');
        jQuery('#MaterialInwardDetailsForm #quantity').val('').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
        jQuery('#MaterialInwardDetailsForm #approx_value').val('');
        jQuery('#MaterialInwardDetailsForm #approx_weight').val('');
        jQuery('#MaterialInwardDetailsForm #remark').val('');
        jQuery('#inward_part_no_list').empty();
        jQuery('#inward_drg_no_list').empty();
        jQuery('#inward_product_code_list').empty();
        setTimeout(function () {

            let $select = jQuery('#MaterialInwardDetailsForm #type_of_testing_id_fix');
            $select.one('select2:opening', function (e) {
                e.preventDefault();
            });

            let sel = $select.next('.select2-container').find('.select2-selection');
            if (sel.length) {
                sel.attr('tabindex', 0).focus();
            }

            $select.select2('close');
        }, 150);

        // setTimeout(function () {
        //     $(form).removeClass('was-validated');
        //     $(form).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
        //     jQuery('#MaterialInwardDetailsForm').find('.error').removeClass('error');
        // }, 150);
    }
});

/* ---------------------------------------------------------------------
 *  Main form submit (assemble JSON + POST)
 * ------------------------------------------------------------------- */
jQuery('#commonMaterialInwardForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#MaterialInwardModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;

    var dateValue = document.getElementById("material_inward_date").value.trim();
    // if (typeof isValidDate === 'function' && !isValidDate(dateValue)) {
    //     toastr.error("Enter A Valid Inward Date!");
    //     jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
    //     jQuery('#MaterialInwardModal').find('#submitbtn').prop('disabled', false);
    //     return;
    // }
    // if (typeof checkDate === 'function' && checkDate(dateValue) === 'no') {
    //     toastr.error("Enter Date within Current Financial Year Selected.");
    //     jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
    //     jQuery('#MaterialInwardModal').find('#submitbtn').prop('disabled', false);
    //     return;
    // }
    if (!isValidDate(dateValue)) {
        toastr.error("Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#MaterialInwardModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#MaterialInwardModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        jQuery(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#MaterialInwardModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    let currentProcessType = jQuery('#MaterialInwardDetailsForm input[name="process_type"]:checked').val() || 'Fresh';
    let currentTestReportRtId = jQuery('#MaterialInwardDetailsForm #test_report_rt_id').val();
    if (currentProcessType === 'Repair' && (!currentTestReportRtId || currentTestReportRtId === '' || currentTestReportRtId == 0 || currentTestReportRtId === '0')) {
        toastr.error('Please Select At Least One Report Form Pending.');
        return false;
    }
    if (!validateInwardDateAgainstReports()) {
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#MaterialInwardModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var poNo = jQuery('#MaterialInwardModal').find('#commonMaterialInwardForm').find('#po_no').val();
    var poDate = jQuery('#MaterialInwardModal').find('#commonMaterialInwardForm').find('#po_date').val();

    // PO No / Date validation is now handled natively via dynamic required properties and invalid-tooltips.

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
 *  Auto-number + sequence duplicate check
 * ------------------------------------------------------------------- */
function getLatestMaterialInwardNo() {
    jQuery.ajax({
        url: "get-latest_material_inward_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#material_inward_date').val(currentDate);
                jQuery('#material_inward_no').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#material_inward_sequence').val(data.number);
            } else {
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('Failed To Get Latest Material Inward No.!')
        }
    });
}

// check sequence number duplication
jQuery('#commonMaterialInwardForm').find('#material_inward_sequence').on('change', function () {
    checkSequence();
});

function checkSequence() {

    let thisForm = jQuery('#commonMaterialInwardForm');
    if (thisForm.find('#material_inward_sequence').prop('readonly')) return;
    let val = thisForm.find('#material_inward_sequence').val();

    // Disable submit button as soon as sequence changes


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

        if (formId !== undefined && formId != "") { //if form is edit
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
                    // Keep submit button disabled on duplicate/invalid sequence
                } else {
                    jQuery('#commonMaterialInwardForm #material_inward_no').val(data.latest_no);
                    jQuery('#commonMaterialInwardForm #material_inward_sequence').val(val);
                    // Enable submit button after successful validation
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
                // Keep submit button disabled on error
            }
        });
    }


}

// Customer change handler for Customer-wise LNR
jQuery('#MaterialInwardModal').on('change', '#customer_id', function () {
    var customerId = jQuery(this).val();
    var formId = jQuery('#MaterialInwardModal').find('#id').val();
    if (!formId && customerId) {
        getLNRData(customerId);
    }
});

// Get Last Note Reset (LNR) Data
function getLNRData(customerId = '') {
    var isInitial = !customerId;
    if (!customerId) {
        customerId = jQuery('#MaterialInwardModal').find('#customer_id').val();
    }
    var nablTypeFix = jQuery('#MaterialInwardModal').find('input[name="nabl_type_fix"]:checked').val();

    jQuery.ajax({
        url: "get-material_inward_lnr_data",
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
                        jQuery('#MaterialInwardModal').find('input[name="nabl_type_fix"][value="' + targetNabl + '"]').prop('checked', true);
                    }
                    if (locLnr.test_at_fix) {
                        jQuery('#MaterialInwardModal').find('input[name="test_at_fix"][value="' + locLnr.test_at_fix + '"]').prop('checked', true);
                    }
                    if (locLnr.job_type_fix) {
                        jQuery('#MaterialInwardModal').find('input[name="job_type_fix"][value="' + locLnr.job_type_fix + '"]').prop('checked', true);
                    }
                    filterTestsByNabl();
                }
                if (data.lnr_data != null) {
                    var lnr = data.lnr_data;
                    jQuery('#MaterialInwardModal').find('#sample_drawn_by').val(lnr.sample_drawn_by ?? "");
                    jQuery('#MaterialInwardModal').find('#is_any_tpi_witness').val(lnr.is_any_tpi_witness ?? "Yes").trigger('change.select2');
                    toggleTpiName();
                    jQuery('#MaterialInwardModal').find('#tpi_name').val(lnr.tpi_name ?? "");
                    jQuery('#MaterialInwardModal').find('#condition_of_sample').val(lnr.condition_of_sample ?? "");
                    jQuery('#MaterialInwardModal').find('#is_equipment_available').val(lnr.is_equipment_available ?? "Yes").trigger('change.select2');
                    jQuery('#MaterialInwardModal').find('#competent_personnel_available').val(lnr.competent_personnel_available ?? "Yes").trigger('change.select2');
                    jQuery('#MaterialInwardModal').find('#is_test_sub_contracted').val(lnr.is_test_sub_contracted ?? "Yes").trigger('change.select2');
                    jQuery('#MaterialInwardModal').find('#test_feasible').val(lnr.test_feasible ?? "Yes").trigger('change.select2');
                    jQuery('#MaterialInwardModal').find('#all_test_parameters_are_in_accredited_scope').val(lnr.all_test_parameters_are_in_accredited_scope ?? "Yes").trigger('change.select2');
                    jQuery('#MaterialInwardModal').find('#required_statement_of_conformity').val(lnr.required_statement_of_conformity ?? "Yes").trigger('change.select2');
                } else {
                    jQuery('#MaterialInwardModal').find('#sample_drawn_by').val("");
                    ['is_any_tpi_witness', 'is_equipment_available', 'competent_personnel_available', 'is_test_sub_contracted', 'test_feasible', 'all_test_parameters_are_in_accredited_scope', 'required_statement_of_conformity'].forEach(function (f) {
                        jQuery('#MaterialInwardModal').find('#' + f).val('Yes').trigger('change.select2');
                    });
                    toggleTpiName();
                    jQuery('#MaterialInwardModal').find('#tpi_name').val("");
                    jQuery('#MaterialInwardModal').find('#condition_of_sample').val("");
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
    var parentId = jQuery('#MaterialInwardModal').find('#id').val();
    let isAdd = (parentId == "" || parentId == undefined || parentId == 0);

    let hasRepairDetail = material_inward_details_data.some(function (item) {
        return item.mode !== "Delete" && item.process_type === 'Repair';
    });
    let parentInUse = material_inward_details_data.some(function (item) {
        return item.mode !== "Delete" && (item.in_use == true || item.in_use == "true" || (item.used_qty && parseFloat(item.used_qty) > 0));
    });

    if (hasRepairDetail || parentInUse) {
        setSelect2Readonly(jQuery('#MaterialInwardModal #customer_id'), true);
        jQuery('#MaterialInwardModal #customer_id').addClass('skip-tab');
        lockParentRadios(true);
    } else {
        if (isAdd) {
            setSelect2Readonly(jQuery('#MaterialInwardModal #customer_id'), false);
            jQuery('#MaterialInwardModal #customer_id').removeClass('skip-tab');
            lockParentRadios(false);
        } else {
            setSelect2Readonly(jQuery('#MaterialInwardModal #customer_id'), true);
            lockParentRadios(false);
        }
    }

    // if (isAdd) {
    //     let activeDetailsCount = material_inward_details_data.filter(item => item.mode !== "Delete").length;
    //     if (activeDetailsCount > 0) {
    //         lockParentRadios(true);
    //     } else {
    //         lockParentRadios(false);
    //     }
    // } else {
    //     lockParentRadios(true);
    // }
}

function focusInitialMaterialInwardField() {
    let modal = jQuery('#MaterialInwardModal');
    let nablRadio = modal.find('input[name="nabl_type_fix"]');
    if (nablRadio.hasClass('skip-tab')) {
        let testAtRadio = modal.find('input[name="test_at_fix"]');
        if (testAtRadio.hasClass('skip-tab')) {
            let jobTypeRadio = modal.find('input[name="job_type_fix"]');
            if (jobTypeRadio.hasClass('skip-tab')) {
                modal.find('#material_inward_sequence').focus().select();
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
jQuery('#MaterialInwardModal').on('shown.bs.modal', function () {
    validatePoNoDate();
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
    var formType = jQuery('#MaterialInwardDetailsModal').find("#form_type").val();
    var detailId = jQuery('#MaterialInwardDetailsModal').find("#material_inward_details_id").val();
    var isSavedDetailEdit = formType === 'edit' || (detailId && detailId !== '0');
    var processType = jQuery('#MaterialInwardDetailsModal input[name="process_type"]:checked').val() || 'Fresh';

    // In EDIT mode or REPAIR case: always keep Past Inward button disabled
    if (isSavedDetailEdit || processType === 'Repair') {
        jQuery('#copyMaterialBtn').prop('disabled', true);
        window.copyMaterialInwardDetailsData = [];
        return;
    }

    // In EDIT mode: always keep Past Inward button disabled
    if (isSavedDetailEdit) {
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

jQuery('#MaterialInwardModal').on('shown.bs.modal', function () {
    validateCopyButton();
});

jQuery('#MaterialInwardDetailsForm').on('reset', function () {
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

        window.didCopyMaterialInward = true;
    }
});

jQuery('#CopyMaterialInwardDetailsModal').on('hidden.bs.modal', function () {
    if (window.didCopyMaterialInward) {
        window.didCopyMaterialInward = false;
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
    jQuery('#MaterialInwardDetailsForm').on('change', 'input[name="process_type"]', function () {
        let processType = jQuery(this).val();
        if (processType === 'Repair') {
            jQuery('#pendingBtn').prop('disabled', false);
        } else {
            jQuery('#pendingBtn').prop('disabled', true);
            resetPendingRepairFields();
        }
    });

    jQuery('#pendingBtn').on('click', function () {
        let customerId = jQuery('#commonMaterialInwardForm #customer_id').val();
        let nablType = jQuery('#commonMaterialInwardForm input[name="nabl_type_fix"]:checked').val();

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

                    let selectedTestReportRtId = jQuery('#MaterialInwardDetailsForm #test_report_rt_id').val();
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
                } else {

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
        jQuery('#MaterialInwardDetailsForm #test_report_rt_id').val(reportData.test_report_rt_id);
        jQuery('#MaterialInwardDetailsForm #revision_number').val(revNo);
        jQuery('#MaterialInwardDetailsForm input[name="process_type"][value="Repair"]').prop('checked', true);
        jQuery('#MaterialInwardDetailsForm #type_of_testing_id_fix').val('RT').trigger('change.select2');

        // Auto-fill detail fields from pending repair report data
        if (reportData.type_of_job_id) {
            jQuery('#MaterialInwardDetailsForm #type_of_job_id').val(reportData.type_of_job_id).trigger('change.select2');
        }
        if (reportData.job_desc_id) {
            jQuery('#MaterialInwardDetailsForm #inward_job_desc_id').val(reportData.job_desc_id).trigger('change.select2');
        }
        if (reportData.material_id) {
            jQuery('#MaterialInwardDetailsForm #material_id').val(reportData.material_id).trigger('change.select2');
        }
        if (reportData.area_of_coverage_id) {
            jQuery('#MaterialInwardDetailsForm #area_of_coverage_id').val(reportData.area_of_coverage_id).trigger('change.select2');
        }
        if (reportData.procedure_ref_id) {
            jQuery('#MaterialInwardDetailsForm #procedure_ref_id').val(reportData.procedure_ref_id).trigger('change.select2');
        }
        if (reportData.evaluation_as_per_id) {
            jQuery('#MaterialInwardDetailsForm #evaluation_as_per_id').val(reportData.evaluation_as_per_id).trigger('change.select2');
        }
        if (reportData.acceptance_standard_id) {
            jQuery('#MaterialInwardDetailsForm #acceptance_standard_id').val(reportData.acceptance_standard_id).trigger('change.select2');
        }

        jQuery('#MaterialInwardDetailsForm #part_no').val(reportData.part_no || '');
        jQuery('#MaterialInwardDetailsForm #drg_no').val(reportData.drg_no || '');
        jQuery('#MaterialInwardDetailsForm #heat_no').val(reportData.heat_no || '');
        jQuery('#MaterialInwardDetailsForm #rt_no').val(reportData.rt_no || '');
        jQuery('#MaterialInwardDetailsForm #product_code').val(reportData.product_code || '');

        setRepairFieldsReadonly(true);

        validateCopyButton();

        jQuery('#PendingRepairReportsModal').modal('hide');
        setTimeout(function () {
            let $thickness = jQuery('#MaterialInwardDetailsForm #thickness');
            $thickness.focus();
        }, 250);
    });
});

function setRepairFieldsReadonly(isReadonly, isRevision = false) {
    let form = jQuery('#MaterialInwardDetailsForm');

    if (typeof setSelect2Readonly === 'function') {
        setSelect2Readonly(form.find('#type_of_testing_id_fix'), isReadonly);
    }
    setRadioReadonly("#MaterialInwardDetailsForm input[name='process_type']", isReadonly);

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
    let form = jQuery('#MaterialInwardDetailsForm');
    form.find('#test_report_rt_id').val(null);
    form.find('#revision_number').val('');

    setRadioReadonly("#MaterialInwardDetailsForm input[name='process_type']", false);
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
