var currentUrl = window.location.href;
var test_report_ut_equipment_details_data = [];
var test_report_ut_probe_details_data = [];
var test_report_ut_details_data = [];
var _openForEdit = false;
var dbUlrId = null;
var dbUlrSequence = null;
var dbUlrNo = null;
var dbUlrYear = null;
var pendingInwardData = [];
var selectedInwardDetailsId = null;
var selectedPendingUtId = null;
var reportMasterData = null;
var copyReportsData = [];
var selectedCopyReportUtId = null;

// Sequence helpers
function resequenceEquipmentDetails(isDelete = false) {
    let activeRows = test_report_ut_equipment_details_data.filter(r => r.mode !== 'Delete');
    let deleteRows = test_report_ut_equipment_details_data.filter(r => r.mode === 'Delete');
    activeRows.forEach((row, index) => {
        row.sr_no = index + 1;
    });
    test_report_ut_equipment_details_data = [...activeRows, ...deleteRows];
}

function resequenceProbeDetails(isDelete = false) {
    let activeRows = test_report_ut_probe_details_data.filter(r => r.mode !== 'Delete');
    let deleteRows = test_report_ut_probe_details_data.filter(r => r.mode === 'Delete');
    activeRows.forEach((row, index) => {
        row.sr_no = index + 1;
    });
    test_report_ut_probe_details_data = [...activeRows, ...deleteRows];
}

function resequenceReportDetails(isDelete = false) {
    let activeRows = test_report_ut_details_data.filter(r => r.mode !== 'Delete');
    let deleteRows = test_report_ut_details_data.filter(r => r.mode === 'Delete');
    activeRows.forEach((row, index) => {
        row.sr_no = index + 1;
    });
    test_report_ut_details_data = [...activeRows, ...deleteRows];
}

// Draw Tables
function fillEquipmentTable() {
    let tbody = jQuery('#EquipmentDetailTable tbody');
    tbody.empty();
    let validRows = test_report_ut_equipment_details_data.filter(row => row.mode !== 'Delete');

    if (validRows.length === 0) {
        tbody.append(`<tr><td colspan="6" class="text-center">No Equipment Details Added</td></tr>`);
    } else {
        validRows.forEach((row) => {
            let formIndx = test_report_ut_equipment_details_data.indexOf(row);
            let actionDropdown = DetailsActionDropdown('editEquipmentDetails', 'removeEquipmentDetails');
            let displayName = row.eu_equipment_name || 'Equipment';
            let rowHtml = `<tr>
                <td>
                   ${actionDropdown}
                    <input type="hidden" name="eq_form_indx" value="${formIndx}"/>
                </td>
                <td>${displayName}</td>
                <td>${row.make || ''}</td>
                <td>${row.display || ''}</td>
                <td>${row.eu_sr_no || ''}</td>
                <td>${row.cal_due_date || ''}</td>
            </tr>`;
            tbody.append(rowHtml);
        });
    }
}

function fillProbeTable() {
    let tbody = jQuery('#ProbeDetailTable tbody');
    tbody.empty();
    let validRows = test_report_ut_probe_details_data.filter(row => row.mode !== 'Delete');

    if (validRows.length === 0) {
        tbody.append(`<tr><td colspan="10" class="text-center">No Probe Details Added</td></tr>`);
    } else {
        validRows.forEach((row) => {
            let formIndx = test_report_ut_probe_details_data.indexOf(row);
            let actionDropdown = DetailsActionDropdown('editProbeDetails', 'removeProbeDetails');
            let displayName = row.pu_probe || 'Probe';
            let rowHtml = `<tr>
                <td>
                   ${actionDropdown}
                    <input type="hidden" name="pb_form_indx" value="${formIndx}"/>
                </td>
                <td>${displayName}</td>
                <td>${row.pu_sr_no || ''}</td>
                <td>${row.size_of_probe || ''}</td>
                <td>${row.ref_angle || ''}</td>
                <td>${row.frequency || ''}</td>
                <td>${row.cal_range || ''}</td>
                <td>${row.ref_gain || ''}</td>
                <td>${row.scanning_db || ''}</td>
                <td>${row.transfer_corr_gain || ''}</td>
            </tr>`;
            tbody.append(rowHtml);
        });
    }
}

function fillReportDetailTable() {
    let tbody = jQuery('#ReportDetailTable tbody');
    tbody.empty();
    let validRows = test_report_ut_details_data.filter(row => row.mode !== 'Delete');

    if (validRows.length === 0) {
        tbody.append(`<tr><td colspan="7" class="text-center">No Test Report Details Added</td></tr>`);
    } else {
        validRows.forEach((row) => {
            let formIndx = test_report_ut_details_data.indexOf(row);
            let actionDropdown = DetailsActionDropdown('editReportDetails', 'removeReportDetails');
            let rowHtml = `<tr>
                <td>
                   ${actionDropdown}
                    <input type="hidden" name="form_indx" value="${formIndx}"/>
                </td>
                <td>${row.sr_no}</td>
                <td>${row.ut_test_no || ''}</td>
                <td>${row.heat_no || ''}</td>
                <td>${row.quantity || 0}</td>
                <td>${row.discontinuity_evaluation || ''}</td>
                <td>${row.result_name || ''}</td>
            </tr>`;
            tbody.append(rowHtml);
        });
    }
    updateQtySummary(validRows);
}

function updateQtySummary(rows) {
    if (!rows) {
        rows = typeof test_report_ut_details_data !== 'undefined' ? test_report_ut_details_data.filter(row => row.mode !== 'Delete') : [];
    }
    let nablType = jQuery('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
    if (nablType === 'NABL') {
        jQuery('#total_qty').val(1);
    } else {
        let sum = rows.reduce((acc, row) => acc + parseInt(row.quantity || 0), 0);
        jQuery('#total_qty').val(sum > 0 ? sum : '');
    }
}

function formatDateStr(sqlDate) {
    if (!sqlDate || sqlDate === '0000-00-00') return '';
    let parts = sqlDate.split('-');
    if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    return sqlDate;
}

// Helpers / Dropdown Fillings
jQuery(document).on('change', '#detail_eu_id', function () {
    let opt = jQuery(this).find('option:selected');
    jQuery('#eq_make').val(opt.data('make') || '');
    jQuery('#eq_display').val(opt.data('display') || '');
    jQuery('#eq_sr_no').val(opt.data('sr_no') || '');
    jQuery('#eq_cal_due_date').val(opt.data('cal_due_date') || '');
});

jQuery(document).on('change', '#detail_pu_id', function () {
    let opt = jQuery(this).find('option:selected');
    jQuery('#pb_sr_no').val(opt.data('sr_no') || '');
    jQuery('#pb_size_of_probe').val(opt.data('size') || '');
    jQuery('#pb_ref_angle').val(opt.data('ref_angle') || '');
    jQuery('#pb_frequency').val(opt.data('frequency') || '');
});

// Dropdown Auto fill drawing no. on part_no change
jQuery(document).on('input change', '#part_no', function () {
    let partNo = jQuery(this).val();
    if (window.partsAutocompleteList && partNo) {
        let match = window.partsAutocompleteList.find(p => p.part_no === partNo);
        if (match && match.drg_no) {
            jQuery('#drg_no').val(match.drg_no);
        }
    }
});

// Fetch pending customers material inward list
jQuery(document).on('change', '#customer_id', function () {
    let customerId = jQuery(this).val();

    if (!customerId) {
        jQuery('#pending_btn').prop('disabled', true);
        return;
    }

    let formId = jQuery('#id').val();
    if (formId) {
        jQuery('#pending_btn').prop('disabled', true);
        return;
    }

    jQuery('#pending_btn').prop('disabled', false);

    jQuery.ajax({
        url: 'get-pending-customer-ut-data',
        type: 'GET',
        data: { customer_id: customerId, report_id: jQuery('#id').val() },
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                pendingInwardData = data.pending_data;

                if (selectedInwardDetailsId) {
                    let exists = pendingInwardData.some(item => item.material_inward_details_id == selectedInwardDetailsId);
                    if (!exists && reportMasterData && reportMasterData.material_inward_details_id == selectedInwardDetailsId) {
                        pendingInwardData.push({
                            from_type_id_fix: reportMasterData.from_type_id_fix || (reportMasterData.observation_sheet_details_id ? 2 : 1),
                            material_inward_details_id: selectedInwardDetailsId,
                            material_inward_no: reportMasterData.material_inward_no,
                            material_inward_date: reportMasterData.date_of_receipt ? reportMasterData.date_of_receipt.split('/').reverse().join('-') : null,
                            nabl_type_fix: reportMasterData.nabl_type_fix,
                            test_at_fix: reportMasterData.test_carried_out_at,
                            job_type_fix: reportMasterData.job_type_fix,
                            dc_no: reportMasterData.dc_no,
                            dc_date: reportMasterData.dc_date ? reportMasterData.dc_date.split('/').reverse().join('-') : null,
                            po_no: reportMasterData.po_no,
                            po_date: reportMasterData.po_date ? reportMasterData.po_date.split('/').reverse().join('-') : null,
                            type_of_job: jQuery('#type_of_job_id option:selected').text().trim(),
                            job_description: jQuery('#job_desc_id option:selected').text().trim(),
                            part_no: reportMasterData.part_no,
                            drg_no: reportMasterData.drg_no,
                            material: jQuery('#material_id option:selected').text().trim(),
                            heat_no: reportMasterData.heat_no,
                            product_code: reportMasterData.product_code,
                            thickness: reportMasterData.thickness,
                            observation_sheet_details_id: reportMasterData.observation_sheet_details_id || '',
                            inward_qty: reportMasterData.pend_qty,
                            pending_qty: reportMasterData.pend_qty,
                            reported_qty: 0
                        });
                    }
                    jQuery('#material_inward_details_id').val(selectedInwardDetailsId).trigger('change');
                    selectedInwardDetailsId = null;
                    var formId = jQuery('#id').val();
                    if (formId == undefined || formId == "") {
                        jQuery('#pending_btn').prop('disabled', false);
                    } else {
                        jQuery('#pending_btn').prop('disabled', true);
                    }

                } else {
                    jQuery('#material_inward_details_id').val('');
                    selectedPendingUtId = null;
                }
                fillPendingUtModalTable();
            }
        },
        complete: function () {
            jQuery('#submitbtn').prop('disabled', false);
        }
    });
});

function fillPendingUtModalTable() {
    let $table = jQuery("#PendingInwardForUtModal").find('#PendingForUtTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().clear().destroy();
    }
    let tbody = jQuery('#PendingForUtTable tbody');
    tbody.empty();

    if (pendingInwardData.length === 0) {
        tbody.append(`<tr><td colspan="19" class="text-center">No Pending Material Inward Available</td></tr>`);
        return;
    }

    let currentVal = jQuery('#material_inward_details_id').val();
    let currentObsVal = jQuery('#observation_sheet_details_id').val();
    let currentFromType = jQuery('#from_type_id_fix').val();

    pendingInwardData.forEach(item => {
        let pendingQty = parseFloat(item.pending_qty || 0);
        let isSameMid = (item.material_inward_details_id == currentVal);
        let isSameObs = (!currentObsVal && (!item.observation_sheet_details_id || item.observation_sheet_details_id == 0)) || (currentObsVal && item.observation_sheet_details_id == currentObsVal);
        let isSameFromType = (!currentFromType) || (item.from_type_id_fix == currentFromType);
        let isChecked = (isSameMid && isSameObs && isSameFromType) ? 'checked' : '';
        let rowHtml = `<tr>
            <td><input type="radio" name="pending_ut_id" value="${item.material_inward_details_id}" data-obs-id="${item.observation_sheet_details_id || ''}" data-from-type="${item.from_type_id_fix || 1}" class="form-check-input select-pending-ut-radio radio_item_select" ${isChecked}></td>
            <td>${item.material_inward_no || ''}</td>
            <td>${item.material_inward_date ? formatDateStr(item.material_inward_date) : ''}</td>
            <td>${item.nabl_type_fix || ''}</td>
            <td>${item.test_at_fix || ''}</td>
            <td>${item.job_type_fix || ''}</td>
            <td>${item.dc_no || ''}</td>
            <td>${item.dc_date ? formatDateStr(item.dc_date) : ''}</td>
            <td>${item.po_no || ''}</td>
            <td>${item.po_date ? formatDateStr(item.po_date) : ''}</td>
            <td>${item.type_of_job || ''}</td>
            <td>${item.job_description || ''}</td>
            <td>${item.part_no || ''}</td>
            <td>${item.drg_no || ''}</td>
            <td>${item.material || ''}</td>
            <td>${item.heat_no || ''}</td>
            <td>${item.product_code || ''}</td>
            <td>${parseFloat(item.inward_qty || 0)}</td>
            <td>${pendingQty}</td>
        </tr>`;
        tbody.append(rowHtml);
    });

    var $new = $table.DataTable({
        paging: true,
        searching: true,
        dom: 'lrtip',
        "sScrollX": true,
        "sScrollX": "100%",
        "sScrollXInner": "110%",
        "bScrollCollapse": true,
    });

    $new.on('draw', function () {
        let selMid = window._selectedMaterialInwardDetailsId || jQuery('#material_inward_details_id').val();
        let selObs = window._selectedObsSheetDetailsId || jQuery('#observation_sheet_details_id').val();
        let selFrom = window._selectedFromTypeId || jQuery('#from_type_id_fix').val();
        if (selMid) {
            jQuery(`input[name="pending_ut_id"]`).each(function () {
                let mid = jQuery(this).val();
                let obs = jQuery(this).attr('data-obs-id');
                let fromT = jQuery(this).attr('data-from-type');
                if (mid == selMid && (!selObs || obs == selObs) && (!selFrom || fromT == selFrom)) {
                    jQuery(this).prop('checked', true);
                }
            });
        }
    });

    if (typeof fixDataTableColumnsUntilAdjusted === 'function') {
        fixDataTableColumnsUntilAdjusted($new);
    }
}

jQuery(document).on('click', '#pending_btn', function () {
    fillPendingUtModalTable();
    jQuery('#PendingInwardForUtModal').modal('show');
});

jQuery('#PendingInwardForUtModal').on('shown.bs.modal', function () {
    let $table = jQuery('#PendingForUtTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().columns.adjust().draw();
        if (typeof initColumnSearch === 'function') {
            initColumnSearch('#PendingForUtTable', [0], 'common_search');
        }
    }
});

jQuery(document).on('change', 'input[name="pending_ut_id"]', function () {
    selectedPendingUtId = jQuery(this).val();
});

jQuery(document).on('click', '#submitPendingUtBtn', function () {
    let selectedRadio = jQuery('input[name="pending_ut_id"]:checked');
    if (selectedRadio.length === 0) {
        toastr.error('Please select at least one pending item.');
        return;
    }

    let detailsId = selectedRadio.val();
    let obsId = selectedRadio.attr('data-obs-id');
    let fromType = selectedRadio.attr('data-from-type');

    if (obsId === 'null' || obsId === 'undefined' || obsId === '0' || !obsId) {
        obsId = null;
    }
    window._selectedMaterialInwardDetailsId = detailsId;
    window._selectedObsSheetDetailsId = obsId;
    window._selectedFromTypeId = fromType;

    jQuery('#material_inward_details_id').val(detailsId).trigger('change');
    jQuery('#PendingInwardForUtModal').modal('hide');

    setTimeout(() => {
        jQuery('#customer_client').focus().select();
    }, 150);
});

// Auto fill form when pending inward item is selected
jQuery(document).on('change', '#material_inward_details_id', function () {
    let detailsId = jQuery(this).val();
    if (!detailsId) return;

    // Prevent auto-fill if we are editing an existing report or initializing edit mode
    let currentFormId = jQuery('#id').val();
    if ((currentFormId && currentFormId !== "" && currentFormId != 0) || (_openForEdit && reportMasterData && reportMasterData.material_inward_details_id == detailsId)) {
        _openForEdit = false;
        return;
    }

    let selectedItem;
    if (window._selectedMaterialInwardDetailsId || window._selectedObsSheetDetailsId !== undefined || window._selectedFromTypeId !== undefined) {
        let reqMid = window._selectedMaterialInwardDetailsId;
        let reqObsId = window._selectedObsSheetDetailsId;
        let reqFromType = window._selectedFromTypeId;
        window._selectedMaterialInwardDetailsId = null;
        window._selectedObsSheetDetailsId = null;
        window._selectedFromTypeId = null;

        selectedItem = pendingInwardData.find(item => {
            let midMatch = (item.material_inward_details_id == detailsId || (reqMid && item.material_inward_details_id == reqMid));
            let obsMatch = false;
            if (!reqObsId && (!item.observation_sheet_details_id || item.observation_sheet_details_id == 0)) {
                obsMatch = true;
            } else if (reqObsId && item.observation_sheet_details_id == reqObsId) {
                obsMatch = true;
            }
            let fromTypeMatch = (!reqFromType) || (item.from_type_id_fix == reqFromType);
            return midMatch && obsMatch && fromTypeMatch;
        });
    }

    if (!selectedItem) {
        selectedItem = pendingInwardData.find(item => item.material_inward_details_id == detailsId);
    }

    if (selectedItem) {
        jQuery('#observation_sheet_details_id').val(selectedItem.observation_sheet_details_id || '');
        jQuery('#from_type_id_fix').val(selectedItem.from_type_id_fix || (selectedItem.observation_sheet_details_id ? 2 : 1));
        setRadioReadonly('input[name="nabl_type_fix"]', false);
        setRadioReadonly('input[name="job_type_fix"]', false);
        jQuery(`input[name="nabl_type_fix"][value="${selectedItem.nabl_type_fix}"]`).prop('checked', true).trigger('change');
        jQuery(`input[name="job_type_fix"][value="${selectedItem.job_type_fix}"]`).prop('checked', true).trigger('change');
        setRadioReadonly('input[name="nabl_type_fix"]', true);
        setRadioReadonly('input[name="job_type_fix"]', true);

        // jQuery('#test_carried_out_at').val(selectedItem.test_at_fix || '');
        jQuery('#dc_no').val(selectedItem.dc_no || '');
        jQuery('#dc_date').val(selectedItem.dc_date ? formatDateStr(selectedItem.dc_date) : '');
        jQuery('#po_no').val(selectedItem.po_no || '');
        jQuery('#po_date').val(selectedItem.po_date ? formatDateStr(selectedItem.po_date) : '');
        jQuery('#date_of_receipt').val(selectedItem.material_inward_date ? formatDateStr(selectedItem.material_inward_date) : '');
        jQuery('#heat_no').val(selectedItem.heat_no || '');
        jQuery('#product_code').val(selectedItem.product_code || '');
        jQuery('#thickness').val(selectedItem.thickness || '');

        if (selectedItem.type_of_job_id) {
            jQuery('#type_of_job_id').val(selectedItem.type_of_job_id).trigger('change.select2');
        }
        if (selectedItem.job_desc_id) {
            jQuery('#job_desc_id').val(selectedItem.job_desc_id).trigger('change.select2');
        }
        if (selectedItem.part_no) {
            jQuery('#part_no').val(selectedItem.part_no);
        }
        if (selectedItem.drg_no) {
            jQuery('#drg_no').val(selectedItem.drg_no);
        }
        if (selectedItem.material_id) {
            jQuery('#material_id').val(selectedItem.material_id).trigger('change.select2');
        }
        if (selectedItem.area_of_coverage_id) {
            jQuery('#area_of_coverage_id').val(selectedItem.area_of_coverage_id).trigger('change.select2');
        }
        if (selectedItem.procedure_ref_id) {
            jQuery('#procedure_ref_id').val(selectedItem.procedure_ref_id).trigger('change.select2');
        }
        if (selectedItem.acceptance_standard_id) {
            jQuery('#acceptance_standard_id').val(selectedItem.acceptance_standard_id).trigger('change.select2');
        }

        jQuery('#pend_qty').val(selectedItem.pending_qty || 0);

        if (!_openForEdit) {
            let initialQty = 1;
            if (selectedItem.nabl_type_fix !== 'NABL') {
                initialQty = parseInt(selectedItem.pending_qty || 0);
            }
            test_report_ut_details_data = [{
                test_report_ut_details_id: 0,
                sr_no: 1,
                ut_test_no: '',
                heat_no: '',
                quantity: initialQty,
                discontinuity_evaluation: '',
                detail_result_id: '',
                result_name: '',
                mode: 'Insert'
            }];
            fillReportDetailTable();
        }
        setSelect2Readonly('#customer_id', true);
    }
});

// Resets
function resetReportDetailsForm() {
    jQuery('#UTReportDetailsForm')[0].reset();
    jQuery('#UTReportDetailsForm').removeClass('was-validated');
    jQuery('#detail_result_id').val('').trigger('change.select2');
    jQuery('#UTReportDetailsForm input[type="hidden"]').val('');
}

function resetEquipmentForm() {
    jQuery('#detail_eu_id option.temp-inactive-option').remove();
    jQuery('#UTEquipmentDetailsForm')[0].reset();
    jQuery('#UTEquipmentDetailsForm').removeClass('was-validated');
    jQuery('#detail_eu_id').val('').trigger('change');
    jQuery('#UTEquipmentDetailsForm input[type="hidden"]').val('');
}

function resetProbeForm() {
    jQuery('#detail_pu_id option.temp-inactive-option').remove();
    jQuery('#UTProbeDetailsForm')[0].reset();
    jQuery('#UTProbeDetailsForm').removeClass('was-validated');
    jQuery('#detail_pu_id').val('').trigger('change');
    jQuery('#UTProbeDetailsForm input[type="hidden"]').val('');
}

// Add row triggers
jQuery(document).on('click', '#addDetailRowBtn', function () {
    resetReportDetailsForm();
    jQuery('#det_form_type').val('add');

    let nablType = jQuery('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
    if (nablType === 'NABL') {
        jQuery('#det_quantity').val(1).prop('readonly', true).attr('tabindex', '-1');
    } else {
        jQuery('#det_quantity').prop('readonly', false).removeAttr('tabindex');
    }

    jQuery('#UTReportDetailsModal').modal('show');
});

jQuery(document).on('click', '#addEquipmentRowBtn', function () {
    resetEquipmentForm();
    jQuery('#eq_form_type').val('add');
    jQuery('#test_report_ut_equipment_details_id').val(0);
    jQuery('#UTEquipmentDetailsModal').modal('show');
});

jQuery(document).on('click', '#addProbeRowBtn', function () {
    resetProbeForm();
    jQuery('#pb_form_type').val('add');
    jQuery('#test_report_ut_probe_details_id').val(0);
    jQuery('#UTProbeDetailsModal').modal('show');
});

// Form Submissions Row Level
jQuery(document).on('click', '#submitDetailsRowBtn', function () {
    let form = jQuery('#UTReportDetailsForm');
    if (form[0].checkValidity() === false) {
        form.addClass('was-validated');
        return;
    }

    let serialized = getFormSerializedData(form);
    let quantity = parseInt(serialized.quantity || 0);
    if (quantity < 1) {
        toastr.error('Enter Quantity greater than 0.');
        return;
    }
    let result_id = jQuery('#detail_result_id').val();
    let result_name = jQuery('#detail_result_id option:selected').text();

    // Prevent duplicate UT Test No.
    let ut_test_no = (serialized.ut_test_no || '').trim();
    let isDuplicate = test_report_ut_details_data.some((row, index) => {
        if (row.mode === 'Delete') return false;
        if (serialized.form_type === 'edit' && index == serialized.form_index) return false;
        return row.ut_test_no && row.ut_test_no.trim().toLowerCase() === ut_test_no.toLowerCase();
    });


    let rowData = {
        test_report_ut_details_id: serialized.test_report_ut_details_id || 0,
        ut_test_no: serialized.ut_test_no,
        heat_no: serialized.heat_no,
        quantity: parseInt(serialized.quantity || 0),
        discontinuity_evaluation: serialized.discontinuity_evaluation,
        detail_result_id: result_id,
        result_name: result_name,
        mode: serialized.form_type === 'add' ? 'Insert' : 'Update'
    };

    if (serialized.form_type === 'add') {
        rowData.sr_no = test_report_ut_details_data.filter(r => r.mode !== 'Delete').length + 1;
        test_report_ut_details_data.push(rowData);
        fillReportDetailTable();

        // Reset form and keep modal open for next entry
        resetReportDetailsForm();
        jQuery('#det_form_type').val('add');
        let nablType = jQuery('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
        if (nablType === 'NABL') {
            jQuery('#det_quantity').val(1).prop('readonly', true).attr('tabindex', '-1');
        } else {
            jQuery('#det_quantity').prop('readonly', false).removeAttr('tabindex');
        }
        //toastr.success('Details added successfully.');
        jQuery('#det_ut_test_no').focus();
    } else {
        rowData.sr_no = parseInt(serialized.row_index);
        rowData.mode = (rowData.test_report_ut_details_id > 0) ? 'Update' : 'Insert';
        test_report_ut_details_data[parseInt(serialized.form_index)] = rowData;
        fillReportDetailTable();
        jQuery('#UTReportDetailsModal').modal('hide');
    }
});

jQuery(document).on('click', '#submitEquipmentRowBtn', function () {
    let form = jQuery('#UTEquipmentDetailsForm');
    if (form[0].checkValidity() === false) {
        form.addClass('was-validated');
        return;
    }

    let serialized = getFormSerializedData(form);
    let eu_id = jQuery('#detail_eu_id').val();

    let testingDateVal = jQuery('#date_of_testing').val();
    if (serialized.cal_due_date && testingDateVal) {
        let calDue = parseDateStr(serialized.cal_due_date);
        let testDate = parseDateStr(testingDateVal);
        if (calDue && testDate && calDue < testDate) {
            toastr.error('This UT Equipment is due for Calibration.');
            return;
        }
    }

    // Prevent duplicate Equipment entry
    let isDuplicate = test_report_ut_equipment_details_data.some((row, index) => {
        if (row.mode === 'Delete') return false;
        if (serialized.form_type === 'edit' && index == serialized.form_index) return false;
        return row.detail_eu_id == eu_id;
    });

    if (isDuplicate) {
        toastr.error('Duplicate Equipment Found.');
        return;
    }

    let selectedOption = jQuery('#detail_eu_id option:selected');
    let eu_name = selectedOption.data('eu_equipment_name') || selectedOption.text().trim();

    let rowData = {
        test_report_ut_equipment_details_id: serialized.test_report_ut_equipment_details_id || 0,
        detail_eu_id: eu_id,
        eu_equipment_name: eu_name,
        make: serialized.make,
        display: serialized.display,
        eu_sr_no: serialized.eu_sr_no,
        cal_due_date: serialized.cal_due_date,
        mode: serialized.form_type === 'add' ? 'Insert' : 'Update'
    };

    if (serialized.form_type === 'add') {
        rowData.sr_no = test_report_ut_equipment_details_data.filter(r => r.mode !== 'Delete').length + 1;
        test_report_ut_equipment_details_data.push(rowData);
        fillEquipmentTable();

        // Reset form and keep modal open for next entry
        resetEquipmentForm();
        jQuery('#eq_form_type').val('add');
        jQuery('#test_report_ut_equipment_details_id').val(0);
        //toastr.success('Equipment details added successfully.');
        jQuery('#detail_eu_id').focus();
    } else {
        rowData.sr_no = parseInt(serialized.row_index);
        rowData.mode = (rowData.test_report_ut_equipment_details_id > 0) ? 'Update' : 'Insert';
        test_report_ut_equipment_details_data[parseInt(serialized.form_index)] = rowData;
        fillEquipmentTable();
        jQuery('#UTEquipmentDetailsModal').modal('hide');
    }
});

jQuery(document).on('click', '#submitProbeRowBtn', function () {
    let form = jQuery('#UTProbeDetailsForm');
    if (form[0].checkValidity() === false) {
        form.addClass('was-validated');
        return;
    }

    let serialized = getFormSerializedData(form);
    let pu_id = jQuery('#detail_pu_id').val();

    // Prevent duplicate Probe entry
    let isDuplicate = test_report_ut_probe_details_data.some((row, index) => {
        if (row.mode === 'Delete') return false;
        if (serialized.form_type === 'edit' && index == serialized.form_index) return false;
        return row.detail_pu_id == pu_id;
    });

    if (isDuplicate) {
        toastr.error('Duplicate Probe Found.');
        return;
    }

    let selectedOption = jQuery('#detail_pu_id option:selected');
    let pu_name = selectedOption.data('pu_probe') || selectedOption.text().trim();

    let rowData = {
        test_report_ut_probe_details_id: serialized.test_report_ut_probe_details_id || 0,
        detail_pu_id: pu_id,
        pu_probe: pu_name,
        pu_sr_no: serialized.pu_sr_no,
        size_of_probe: serialized.size_of_probe,
        ref_angle: serialized.ref_angle,
        frequency: serialized.frequency,
        cal_range: serialized.cal_range,
        ref_gain: serialized.ref_gain,
        scanning_db: serialized.scanning_db,
        transfer_corr_gain: serialized.transfer_corr_gain,
        mode: serialized.form_type === 'add' ? 'Insert' : 'Update'
    };

    if (serialized.form_type === 'add') {
        rowData.sr_no = test_report_ut_probe_details_data.filter(r => r.mode !== 'Delete').length + 1;
        test_report_ut_probe_details_data.push(rowData);
        fillProbeTable();

        // Reset form and keep modal open for next entry
        resetProbeForm();
        jQuery('#pb_form_type').val('add');
        jQuery('#test_report_ut_probe_details_id').val(0);
        //toastr.success('Probe details added successfully.');
        jQuery('#detail_pu_id').focus();
    } else {
        rowData.sr_no = parseInt(serialized.row_index);
        rowData.mode = (rowData.test_report_ut_probe_details_id > 0) ? 'Update' : 'Insert';
        test_report_ut_probe_details_data[parseInt(serialized.form_index)] = rowData;
        fillProbeTable();
        jQuery('#UTProbeDetailsModal').modal('hide');
    }
});

// Row Edits & Deletes
function editReportDetails(ele) {
    resetReportDetailsForm();
    let formIndx = jQuery(ele).closest('tr').find('input[name="form_indx"]').val();
    let row = test_report_ut_details_data[formIndx];

    jQuery('#det_form_type').val('edit');
    jQuery('#det_form_index').val(formIndx);
    jQuery('#det_row_index').val(row.sr_no);
    jQuery('#test_report_ut_details_id').val(row.test_report_ut_details_id || 0);

    jQuery('#det_ut_test_no').val(row.ut_test_no);
    jQuery('#det_heat_no').val(row.heat_no);
    jQuery('#det_quantity').val(row.quantity);
    jQuery('#det_discontinuity_evaluation').val(row.discontinuity_evaluation);
    jQuery('#detail_result_id').val(row.detail_result_id).trigger('change.select2');

    let nablType = jQuery('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
    if (nablType === 'NABL') {
        jQuery('#det_quantity').val(1).prop('readonly', true).attr('tabindex', '-1');
    } else {
        jQuery('#det_quantity').prop('readonly', false).removeAttr('tabindex');
    }

    jQuery('#UTReportDetailsModal').modal('show');
}

function removeReportDetails(ele) {
    let formIndx = jQuery(ele).closest('tr').find('input[name="form_indx"]').val();
    toastDetailDelete("Do you want to delete this record?", function () {
        let row = test_report_ut_details_data[formIndx];
        if (row.test_report_ut_details_id > 0) {
            row.mode = 'Delete';
        } else {
            test_report_ut_details_data.splice(formIndx, 1);
        }
        resequenceReportDetails(true);
        fillReportDetailTable();
    });
}

function editEquipmentDetails(ele) {
    resetEquipmentForm();
    let formIndx = jQuery(ele).closest('tr').find('input[name="eq_form_indx"]').val();
    let row = test_report_ut_equipment_details_data[formIndx];

    jQuery('#eq_form_type').val('edit');
    jQuery('#eq_form_index').val(formIndx);
    jQuery('#eq_row_index').val(row.sr_no);
    jQuery('#test_report_ut_equipment_details_id').val(row.test_report_ut_equipment_details_id || 0);

    if (row.detail_eu_id && jQuery('#detail_eu_id option[value="' + row.detail_eu_id + '"]').length === 0) {
        let displayName = row.eu_equipment_name || 'Equipment';
        if (row.eu_sr_no) displayName += ' - ' + row.eu_sr_no;
        if (row.make) displayName += ' - ' + row.make;
        let opt = new Option(displayName, row.detail_eu_id, true, true);
        jQuery(opt).addClass('temp-inactive-option')
            .attr('data-eu_equipment_name', row.eu_equipment_name || '')
            .attr('data-make', row.make || '')
            .attr('data-display', row.display || '')
            .attr('data-sr_no', row.eu_sr_no || '')
            .attr('data-cal_due_date', row.cal_due_date || '');
        jQuery('#detail_eu_id').append(opt);
    }

    jQuery('#detail_eu_id').val(row.detail_eu_id).trigger('change');
    jQuery('#eq_make').val(row.make);
    jQuery('#eq_display').val(row.display);
    jQuery('#eq_sr_no').val(row.eu_sr_no);
    jQuery('#eq_cal_due_date').val(row.cal_due_date);

    jQuery('#UTEquipmentDetailsModal').modal('show');
}

function removeEquipmentDetails(ele) {
    let formIndx = jQuery(ele).closest('tr').find('input[name="eq_form_indx"]').val();
    toastDetailDelete("Do you want to delete this record?", function () {
        let row = test_report_ut_equipment_details_data[formIndx];
        if (row.test_report_ut_equipment_details_id > 0) {
            row.mode = 'Delete';
        } else {
            test_report_ut_equipment_details_data.splice(formIndx, 1);
        }
        resequenceEquipmentDetails(true);
        fillEquipmentTable();
    });
}

function editProbeDetails(ele) {
    resetProbeForm();
    let formIndx = jQuery(ele).closest('tr').find('input[name="pb_form_indx"]').val();
    let row = test_report_ut_probe_details_data[formIndx];

    jQuery('#pb_form_type').val('edit');
    jQuery('#pb_form_index').val(formIndx);
    jQuery('#pb_row_index').val(row.sr_no);
    jQuery('#test_report_ut_probe_details_id').val(row.test_report_ut_probe_details_id || 0);

    if (row.detail_pu_id && jQuery('#detail_pu_id option[value="' + row.detail_pu_id + '"]').length === 0) {
        let displayName = row.pu_probe || 'Probe';
        if (row.pu_sr_no) displayName += ' - ' + row.pu_sr_no;
        let opt = new Option(displayName, row.detail_pu_id, true, true);
        jQuery(opt).addClass('temp-inactive-option')
            .attr('data-pu_probe', row.pu_probe || '')
            .attr('data-sr_no', row.pu_sr_no || '')
            .attr('data-size', row.size_of_probe || '')
            .attr('data-ref_angle', row.ref_angle || '')
            .attr('data-frequency', row.frequency || '');
        jQuery('#detail_pu_id').append(opt);
    }

    jQuery('#detail_pu_id').val(row.detail_pu_id).trigger('change');
    jQuery('#pb_sr_no').val(row.pu_sr_no);
    jQuery('#pb_size_of_probe').val(row.size_of_probe);
    jQuery('#pb_ref_angle').val(row.ref_angle);
    jQuery('#pb_frequency').val(row.frequency);
    jQuery('#pb_cal_range').val(row.cal_range);
    jQuery('#pb_ref_gain').val(row.ref_gain);
    jQuery('#pb_scanning_db').val(row.scanning_db);
    jQuery('#pb_transfer_corr_gain').val(row.transfer_corr_gain);

    jQuery('#UTProbeDetailsModal').modal('show');
}

function removeProbeDetails(ele) {
    let formIndx = jQuery(ele).closest('tr').find('input[name="pb_form_indx"]').val();
    toastDetailDelete("Do you want to delete this record?", function () {
        let row = test_report_ut_probe_details_data[formIndx];
        if (row.test_report_ut_probe_details_id > 0) {
            row.mode = 'Delete';
        } else {
            test_report_ut_probe_details_data.splice(formIndx, 1);
        }
        resequenceProbeDetails(true);
        fillProbeTable();
    });
}

// Select customer dropdown list loader
function fillCustomerDropdown() {
    let customerDropdown = jQuery('#customer_id');
    customerDropdown.empty().append('<option value="">Select Customer</option>');

    jQuery.ajax({
        url: 'get-pending-customers-for-ut',
        type: 'GET',
        data: { report_id: jQuery('#id').val() },
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                customerDropdown.empty().append('<option value="">Select Customer</option>');
                data.customers.forEach(cust => {
                    customerDropdown.append(`<option value="${cust.id}">${cust.customer}</option>`);
                });

                if (reportMasterData) {
                    customerDropdown.val(zeroToEmpty(reportMasterData.customer_id)).trigger('change');
                    if (jQuery('#id').val()) {
                        setSelect2Readonly('#customer_id', true);
                    }
                } else {
                    customerDropdown.trigger('change');
                }
            }
        },
        error: function () {
            jQuery('#submitbtn').prop('disabled', false);
        },
        complete: function () {
            jQuery('#submitbtn').prop('disabled', false);
        }
    });
}

// Reset Main Form
function resetMainForm() {
    _openForEdit = false;
    var lastNote = jQuery('#note').val();
    jQuery('#commonTestReportUtForm')[0].reset();
    jQuery('#note').val(lastNote);
    jQuery('#commonTestReportUtForm').removeClass('was-validated');
    jQuery('#id').val('');
    jQuery('#material_inward_details_id').val('');
    selectedPendingUtId = null;
    jQuery('#observation_sheet_details_id').val('');
    jQuery('#from_type_id_fix').val('');
    jQuery('#defectogram_image_doc').val('');
    jQuery('#defectogram_image').val('');
    jQuery('#defectogram_image_prev').attr('href', '#').addClass('hide');
    jQuery('#defectogram_image_remove').removeClass('i-block').addClass('hide');

    // Reset select2 dropdowns
    jQuery('#type_of_job_id').val('').trigger('change.select2');
    jQuery('#job_desc_id').val('').trigger('change.select2');
    jQuery('#material_id').val('').trigger('change.select2');
    jQuery('#area_of_coverage_id').val('').trigger('change.select2');
    jQuery('#procedure_ref_id').val('').trigger('change.select2');
    jQuery('#acceptance_standard_id').val('').trigger('change.select2');
    jQuery('#ulr_id').val('').trigger('change.select2');
    jQuery('#tested_by_authority_person_id').val('').trigger('change.select2');
    jQuery('#reviewed_by_authority_person_id').val('').trigger('change.select2');
    jQuery('#authorized_by_authority_person_id').val('').trigger('change.select2');

    // Reset suggestion inputs
    jQuery('#customer_client_suggestion').val('');
    jQuery('#part_no_suggestion').val('');
    jQuery('#drg_no_suggestion').val('');
    jQuery('#test_carried_out_at_suggestion').val('');
    jQuery('#surface_condition_suggestion').val('');
    jQuery('#surface_temp_suggestion').val('');
    jQuery('#thickness_suggestion').val('');
    jQuery('#couplant_suggestion').val('');
    jQuery('#test_technique_suggestion').val('');
    jQuery('#ref_block_used_suggestion').val('');
    jQuery('#scanning_area_suggestion').val('');
    jQuery('#scan_plan_no_suggestion').val('');
    jQuery('#product_code_suggestion').val('');
    jQuery('#ulr_year').val('');

    // Clear detail grids
    test_report_ut_details_data = [];
    fillReportDetailTable();

    test_report_ut_equipment_details_data = [];
    test_report_ut_probe_details_data = [];

    jQuery.ajax({
        url: 'get-last-ut-details',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                test_report_ut_equipment_details_data = [];
                test_report_ut_probe_details_data = [];
                if (data.equipment_details_data && data.equipment_details_data.length > 0) {
                    data.equipment_details_data.forEach(row => {
                        test_report_ut_equipment_details_data.push({
                            test_report_ut_equipment_details_id: 0,
                            sr_no: 0,
                            detail_eu_id: row.eu_id,
                            eu_equipment_name: row.eu_equipment_name,
                            make: row.make,
                            display: row.display,
                            eu_sr_no: row.eu_sr_no,
                            cal_due_date: row.cal_due_date,
                            mode: 'Insert'
                        });
                    });
                    resequenceEquipmentDetails(false);
                }
                if (data.probe_details_data && data.probe_details_data.length > 0) {
                    data.probe_details_data.forEach(row => {
                        test_report_ut_probe_details_data.push({
                            test_report_ut_probe_details_id: 0,
                            sr_no: 0,
                            detail_pu_id: row.pu_id,
                            pu_probe: row.pu_probe,
                            pu_sr_no: row.pu_sr_no,
                            size_of_probe: row.size_of_probe,
                            ref_angle: row.ref_angle,
                            frequency: row.frequency,
                            cal_range: row.cal_range,
                            ref_gain: row.ref_gain,
                            scanning_db: row.scanning_db,
                            transfer_corr_gain: row.transfer_corr_gain,
                            mode: 'Insert'
                        });
                    });
                    resequenceProbeDetails(false);
                }
            }
            fillEquipmentTable();
            fillProbeTable();
        },
        error: function () {
            fillEquipmentTable();
            fillProbeTable();
        }
    });

    // Reset and Lock radio buttons to defaults
    setRadioReadonly('input[name="nabl_type_fix"]', false);
    setRadioReadonly('input[name="job_type_fix"]', false);
    jQuery('#nabl_non_nabl').prop('checked', true);
    jQuery('#job_casting').prop('checked', true);
    setRadioReadonly('input[name="nabl_type_fix"]', true);
    setRadioReadonly('input[name="job_type_fix"]', true);

    setSelect2Readonly('#customer_id', false);
    jQuery('#material_inward_details_id').val('');
    jQuery('#copy_report_btn').prop('disabled', false);
    jQuery('#pend_qty').val('');

    dbUlrId = null;
    dbUlrSequence = null;
    dbUlrNo = null;
    dbUlrYear = null;

    reportMasterData = null;
    selectedInwardDetailsId = null;
    selectedPendingUtId = null;
    selectedCopyReportUtId = null;
    fillCustomerDropdown();
    handleNablTypeChange();

    jQuery('#submitbtn').show();
    jQuery('#resetbtn').show();
    jQuery('#preview_btn').hide();
    jQuery('#add_new').hide();

    jQuery('#submitbtn').prop('disabled', false);

    // Get latest sequence
    jQuery.ajax({
        url: 'get-latest-test_report_ut_number',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#test_report_sequence').val(data.number);
                jQuery('#test_report_no').val(data.latest_no);
                jQuery('#test_report_sequence').attr('data-oldval', data.number).focus();
                let dateVal = (typeof currentDate !== 'undefined') ? currentDate : new Date().toLocaleDateString('en-GB');
                jQuery('#test_report_date').val(dateVal).trigger('change');
            }
        }
    });
    getTRLNRData();
}

function checkReportSequenceDuplication() {
    let seq = jQuery('#test_report_sequence').val();
    if (seq != "") {
        if (seq > 0 == false) {
            toastr.error('Please Enter Valid Sr. No.');
            jQuery('#test_report_sequence').val('');
            jQuery('#test_report_sequence').focus();
            jQuery('#submitbtn').prop('disabled', false);
        } else {
            jQuery('#test_report_sequence').addClass('file-loader');
            jQuery('#submitbtn').prop('disabled', true);
            let id = jQuery('#id').val();
            return jQuery.ajax({
                url: 'check-test_report_ut_number_duplication',
                type: 'GET',
                data: { test_report_sequence: seq, id: id },
                dataType: 'json',
                success: function (data) {
                    jQuery('#test_report_sequence').removeClass('file-loader');
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        jQuery('#test_report_sequence').val('');
                        jQuery('#test_report_sequence').focus();
                    } else {
                        jQuery('#test_report_no').val(data.latest_no);
                        jQuery('#test_report_sequence').val(seq);
                        jQuery('#submitbtn').prop('disabled', false);
                    }
                },
                error: function () {
                    jQuery('#test_report_sequence').removeClass('file-loader');
                    jQuery('#submitbtn').prop('disabled', false);
                    toastr.error('Something went wrong!');
                }
            });
        }
    } else {
        jQuery('#submitbtn').prop('disabled', false);
    }
}

jQuery('#commonTestReportUtForm').find('#test_report_sequence').on('change', function () {
    checkReportSequenceDuplication();
});



// Form Posting (Submit)
jQuery(document).on('click', '#submitbtn', function () {
    let form = jQuery('#commonTestReportUtForm');
    if (form[0].checkValidity() === false) {
        form.addClass('was-validated');
        return;
    }

    if (!validateDates()) {
        return;
    }

    // Check if UT Equipment is expired
    let testingDateVal = jQuery('#date_of_testing').val();
    let testDate = parseDateStr(testingDateVal);
    let activeEquipment = test_report_ut_equipment_details_data.filter(r => r.mode !== 'Delete');
    for (let i = 0; i < activeEquipment.length; i++) {
        let eqRow = activeEquipment[i];
        if (eqRow.cal_due_date) {
            let calDueDate = parseDateStr(eqRow.cal_due_date);
            if (calDueDate && testDate && calDueDate < testDate) {
                toastr.error('This UT Equipment is due for Calibration.');
                return;
            }
        }
    }

    let inwardDetailsId = jQuery('#material_inward_details_id').val();
    if (!inwardDetailsId) {
        toastr.error('Please Select At Least One Pending Report');
        return;
    }

    if (test_report_ut_equipment_details_data.filter(r => r.mode !== 'Delete').length === 0) {
        toastr.error('Please Add At Least One UT Equipment Detail');
        return;
    }
    if (test_report_ut_probe_details_data.filter(r => r.mode !== 'Delete').length === 0) {
        toastr.error('Please Add At Least One UT Probe Detail');
        return;
    }
    let activeDetails = test_report_ut_details_data.filter(r => r.mode !== 'Delete');
    if (activeDetails.length === 0) {
        toastr.error('Please Add At Least One Test Report UT Detail');
        return;
    }
    var pendQty = parseInt($('#pend_qty').val()) || 0;
    var totalQty = parseInt($('#total_qty').val()) || 0;
    if (totalQty > pendQty && pendQty > 0) {
        toastr.error('Total Qty Cannot Be Greater Than Pending Qty.');
        return;
    }

    for (let i = 0; i < activeDetails.length; i++) {
        let row = activeDetails[i];
        if (!row.ut_test_no || row.ut_test_no.trim() === '') {
            toastr.error(`Enter UT Test No.`);
            return;
        }
        if (!row.quantity || parseInt(row.quantity) < 1) {
            toastr.error('Enter Quantity greater than 0.');
            return;
        }
        if (!row.discontinuity_evaluation || row.discontinuity_evaluation.trim() === '') {
            toastr.error(`Enter Discontinuity Evaluation`);
            return;
        }
        if (!row.detail_result_id || row.detail_result_id === '') {
            toastr.error(`Enter Result`);
            return;
        }
    }

    let formData = new FormData(form[0]);
    formData.append('equipment_details', JSON.stringify(test_report_ut_equipment_details_data));
    formData.append('probe_details', JSON.stringify(test_report_ut_probe_details_data));
    formData.append('report_details_data', JSON.stringify(test_report_ut_details_data));

    // Support disabling dropdown fields during submission mapping
    if (jQuery('#customer_id').is(':disabled')) {
        formData.append('customer_id', jQuery('#customer_id').val());
    }

    let id = jQuery('#id').val();
    let postUrl = id ? 'update-test_report_ut' : 'store-test_report_ut';

    jQuery('#submitbtn').prop('disabled', true);
    skipLoader = false;
    showLoader();

    jQuery.ajax({
        url: postUrl,
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        headers: headerOpt,
        success: function (data) {
            jQuery('#submitbtn').prop('disabled', false);
            if (data.response_code == 1) {
                let formId = jQuery('#id').val();
                if (formId) {
                    let redirectFn = function () {
                        if (typeof pageRelod !== 'undefined' && pageRelod == 'Yes') {
                            setTimeout(function () {
                                let seqInput = jQuery('#test_report_sequence');
                                if (seqInput.length && !seqInput.prop('readonly')) {
                                    seqInput.focus().select();
                                }
                            }, 50);
                        } else {
                            window.location.reload();
                        }
                    };
                    if (typeof pageRelod !== 'undefined' && pageRelod == 'Yes') {
                        const form = document.getElementById("commonTestReportUtForm");
                        if (form) {
                            form.classList.remove('was-validated');
                        }
                    }
                    if (data.url && data.url != "") {
                        toastSuccessPreview(data.response_message, data.url, redirectFn);
                    } else {
                        toastSuccess(data.response_message, redirectFn);
                    }
                } else {
                    let nextFn = function () {
                        resetMainForm();
                        setTimeout(function () {
                            jQuery('#test_report_sequence').focus().select();
                        }, 50);
                    };
                    if (data.url && data.url != "") {
                        toastSuccessPreview(data.response_message, data.url, nextFn);
                    } else {
                        toastSuccess(data.response_message, nextFn);
                    }
                }
            } else {
                toastr.error(data.response_message);
            }
        },
        error: function (jqXHR) {
            jQuery('#submitbtn').prop('disabled', false);
            toastr.error('Something went wrong!');
        },
        complete: function () {
            hideLoader();
        }
    });
});

// Fetch and Fill UT Test Report details for Edit/Reset
function fetchAndFillTestReportUt(id) {
    if (!id) return;
    _openForEdit = true;

    // Disable submit button until loading is complete
    jQuery('#submitbtn').prop('disabled', true);

    jQuery('#TestReportUtModal').modal('show');
    skipLoader = false;
    showLoader();

    jQuery.ajax({
        url: 'edit-test_report_ut',
        type: 'GET',
        data: { id: id },
        dataType: 'json',
        headers: headerOpt,
        success: function (data) {
            if (data.response_code == 1) {
                reportMasterData = data.report_data;
                selectedInwardDetailsId = reportMasterData.material_inward_details_id;
                jQuery('#observation_sheet_details_id').val(zeroToEmpty(reportMasterData.observation_sheet_details_id));
                jQuery('#from_type_id_fix').val(reportMasterData.from_type_id_fix || (reportMasterData.observation_sheet_details_id ? 2 : 1));

                let previewUrl = checkFileRoute + "?id=" + btoa(reportMasterData.test_report_ut_id) + "&name=" + reportMasterData.pdf_name + "&type=test_report_ut";
                jQuery('#preview_btn').attr('href', previewUrl).show();

                jQuery('#id').val(reportMasterData.test_report_ut_id);
                jQuery('#test_report_sequence').val(reportMasterData.test_report_sequence).attr('data-oldval', reportMasterData.test_report_sequence);
                setTimeout(() => {
                    let seqInput = jQuery('#test_report_sequence');
                    if (seqInput.prop('readonly')) {
                        let dateInput = jQuery('#test_report_date');
                        dateInput.focus().select();
                        setTimeout(function () {
                            dateInput.datepicker('hide');
                        }, 50);
                    } else {
                        seqInput.focus().select();
                    }
                }, 200);
                jQuery('#test_report_no').val(reportMasterData.test_report_no);
                jQuery('#test_report_date').val(reportMasterData.test_report_date);
                jQuery('#date_of_testing').val(reportMasterData.date_of_testing);
                jQuery('#date_of_testing_value').val(reportMasterData.date_of_testing_value || '');
                jQuery('#total_qty').val(reportMasterData.total_qty || '');

                // Fill all text / select fields
                jQuery('#customer_client').val(reportMasterData.customer_client || '');
                jQuery('#type_of_job_id').val(zeroToEmpty(reportMasterData.type_of_job_id)).trigger('change.select2');
                jQuery('#job_desc_id').val(zeroToEmpty(reportMasterData.job_desc_id)).trigger('change.select2');
                jQuery('#part_no').val(reportMasterData.part_no || '');
                jQuery('#drg_no').val(reportMasterData.drg_no || '');
                jQuery('#material_id').val(zeroToEmpty(reportMasterData.material_id)).trigger('change.select2');
                jQuery('#heat_no').val(reportMasterData.heat_no || '');
                jQuery('#product_code').val(reportMasterData.product_code || '');
                jQuery('#dc_no').val(reportMasterData.dc_no || '');
                jQuery('#dc_date').val(reportMasterData.dc_date || '');
                jQuery('#po_no').val(reportMasterData.po_no || '');
                jQuery('#po_date').val(reportMasterData.po_date || '');
                jQuery('#date_of_receipt').val(reportMasterData.date_of_receipt || '');
                jQuery('#test_carried_out_at').val(reportMasterData.test_carried_out_at || '');
                jQuery('#amendment_no').val(reportMasterData.amendment_no || '');
                jQuery('#amendment_date').val(reportMasterData.amendment_date || '');
                jQuery('#amendment_reason').val(reportMasterData.amendment_reason || '');
                jQuery('#stage_of_test').val(reportMasterData.stage_of_test || '');
                jQuery('#area_of_coverage_id').val(zeroToEmpty(reportMasterData.area_of_coverage_id)).trigger('change.select2');
                jQuery('#surface_condition').val(reportMasterData.surface_condition || '');
                jQuery('#surface_temp').val(reportMasterData.surface_temp || '');
                jQuery('#thickness').val(reportMasterData.thickness || '');
                jQuery('#couplant').val(reportMasterData.couplant || '');
                jQuery('#test_technique').val(reportMasterData.test_technique || '');
                jQuery('#ref_block_used').val(reportMasterData.ref_block_used || '');
                jQuery('#scanning_area').val(reportMasterData.scanning_area || '');
                jQuery('#scan_plan_no').val(reportMasterData.scan_plan_no || '');
                jQuery('#procedure_ref_id').val(zeroToEmpty(reportMasterData.procedure_ref_id)).trigger('change.select2');
                jQuery('#acceptance_standard_id').val(zeroToEmpty(reportMasterData.acceptance_standard_id)).trigger('change.select2');

                setRadioReadonly('input[name="nabl_type_fix"]', false);
                setRadioReadonly('input[name="job_type_fix"]', false);
                jQuery(`input[name="nabl_type_fix"][value="${reportMasterData.nabl_type_fix}"]`).prop('checked', true);
                jQuery(`input[name="job_type_fix"][value="${reportMasterData.job_type_fix}"]`).prop('checked', true);
                setRadioReadonly('input[name="nabl_type_fix"]', true);
                setRadioReadonly('input[name="job_type_fix"]', true);
                handleNablTypeChange();

                if (reportMasterData.nabl_type_fix === 'NABL') {
                    dbUlrId = reportMasterData.ulr_id;
                    dbUlrSequence = reportMasterData.ulr_sequence;
                    dbUlrNo = reportMasterData.ulr_no;
                    dbUlrYear = reportMasterData.ulr_year;

                    _preventUlrFetch = true;
                    let ulrVal = zeroToEmpty(reportMasterData.ulr_id);
                    if (ulrVal && jQuery('#ulr_id option[value="' + ulrVal + '"]').length === 0) {
                        jQuery('#ulr_id').append(new Option(reportMasterData.ulr_name || '', ulrVal, true, true));
                    }
                    jQuery('#ulr_id').val(ulrVal).trigger('change.select2');
                    jQuery('#ulr_sequence').val(reportMasterData.ulr_sequence);
                    jQuery('#ulr_no').val(reportMasterData.ulr_no);
                    jQuery('#ulr_year').val(reportMasterData.ulr_year);
                    _preventUlrFetch = false;
                } else {
                    dbUlrId = null;
                    dbUlrSequence = null;
                    dbUlrNo = null;
                    dbUlrYear = null;
                }

                // Tested / Reviewed / Authorized By
                jQuery('#tested_by_authority_person_id').val(zeroToEmpty(reportMasterData.tested_by_authority_person_id)).trigger('change.select2');
                jQuery('#reviewed_by_authority_person_id').val(zeroToEmpty(reportMasterData.reviewed_by_authority_person_id)).trigger('change.select2');
                jQuery('#authorized_by_authority_person_id').val(zeroToEmpty(reportMasterData.authorized_by_authority_person_id)).trigger('change.select2');

                jQuery('#sp_note').val(reportMasterData.sp_note || '');
                jQuery('#note').val(reportMasterData.note || '');

                // Fill detail grids
                test_report_ut_equipment_details_data = data.equipment_details_data.map((row, idx) => ({
                    ...row,
                    sr_no: idx + 1,
                    detail_eu_id: row.eu_id,
                    mode: 'Active'
                }));
                fillEquipmentTable();

                test_report_ut_probe_details_data = data.probe_details_data.map((row, idx) => ({
                    ...row,
                    sr_no: idx + 1,
                    detail_pu_id: row.pu_id,
                    mode: 'Active'
                }));
                fillProbeTable();

                test_report_ut_details_data = data.report_details_data.map((row, idx) => ({
                    ...row,
                    sr_no: idx + 1,
                    detail_result_id: row.result_id,
                    mode: 'Active'
                }));
                fillReportDetailTable();

                // Image display
                if (reportMasterData.defectogram_image) {
                    let fullPath = reportMasterData.defectogram_image;
                    let fileName = fullPath.split('/').pop();
                    jQuery('#defectogram_image_doc').val(fullPath);
                    jQuery('#defectogram_image_prev').attr('href', 'storage/' + fullPath).text('View').removeClass('hide');
                    jQuery('#defectogram_image_remove').removeClass('hide').addClass('i-block');

                    let fileInput = jQuery('#defectogram_image');
                    let newFile = new DataTransfer();
                    newFile.items.add(new File([""], fileName));
                    fileInput[0].files = newFile.files;
                } else {
                    jQuery('#defectogram_image_doc').val('');
                    jQuery('#defectogram_image').val('');
                    jQuery('#defectogram_image_prev').attr('href', '#').addClass('hide');
                    jQuery('#defectogram_image_remove').removeClass('i-block').addClass('hide');
                }

                // Disable some selection changes
                setSelect2Readonly('#customer_id', true);

                // Populate pending qty from edit response
                jQuery('#pend_qty').val(data.pend_qty ?? 0);

                // Disable copy button in edit mode
                jQuery('#copy_report_btn').prop('disabled', true);

                jQuery('#material_inward_details_id').val(reportMasterData.material_inward_details_id);

                // Load customer
                fillCustomerDropdown();

                // Show Add New button
                jQuery('#add_new').show();

                // Finish edit population delay to prevent auto-fetching of ULR and others during populate
                setTimeout(function () {
                    _openForEdit = false;
                }, 150);
            }
        },
        error: function () {
            jQuery('#submitbtn').prop('disabled', false);
        },
        complete: function () {
            hideLoader();
        }
    });
}

// Edit Mode Loader
jQuery(document).on('click', '.edit-test_report_ut', function () {
    let rowData = table.row(jQuery(this).parents('tr')).data();
    fetchAndFillTestReportUt(rowData.id);
});

// File upload validation & handler
function validateImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i;
    return allowedExtensions.exec(filePath) ? true : false;
}

// Image Upload Pre-save callback
jQuery(document).on('change', '#defectogram_image', function (e) {
    var form_data = new FormData();
    var target = e.target;
    var files = target.files;
    var totalfiles = files.length;
    var oldImg = jQuery('#defectogram_image_doc').val();

    if (totalfiles > 0) {
        var notValid = 0;
        for (var index = 0; index < totalfiles; index++) {
            if (validateImage(files[index].name) == true) {
                form_data.append("docs[]", files[index]);
            } else {
                notValid = 1;
                toastr.error("Only (jpeg,jpg,png,gif) files are allowed.");
                jQuery('#defectogram_image_doc').val('');
                jQuery('#defectogram_image').val('');
                jQuery('#defectogram_image_prev').attr('href', '#').addClass('hide');
                jQuery('#defectogram_image_remove').removeClass('i-block').addClass('hide');
                return false;
            }
        }

        if (notValid == 0) {
            jQuery('#submitbtn').prop('disabled', true);
            jQuery('#defectogram_image').addClass('file-loader');
            jQuery.ajax({
                url: 'upload-docs',
                type: 'POST',
                data: form_data,
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    jQuery('#submitbtn').prop('disabled', false);
                    jQuery('#defectogram_image').removeClass('file-loader');
                    if (data.response_code == 1) {
                        if (oldImg != "" && oldImg.includes('temp_media/')) {
                            removeMedia(oldImg);
                        }

                        jQuery('#defectogram_image_doc').val(data.files);
                        jQuery('#defectogram_image_prev').attr('href', data.files_url).text('View').removeClass('hide');
                        jQuery('#defectogram_image_remove').removeClass('hide').addClass('i-block');
                        console.log(data.response_message);
                    } else {
                        toastr.error(data.response_message);
                    }
                },
                error: function () {
                    jQuery('#submitbtn').prop('disabled', false);
                    jQuery('#defectogram_image').removeClass('file-loader');
                    toastr.error('Something went wrong with file upload!');
                }
            });
        }
    } else {
        if (oldImg != "") {
            let fileName = oldImg.split('/').pop();
            let fileInput = jQuery('#defectogram_image');
            let dataTransfer = new DataTransfer();
            dataTransfer.items.add(new File([""], fileName));
            fileInput[0].files = dataTransfer.files;
            return false;
        }
        jQuery('#defectogram_image_doc').val('');
        jQuery('#defectogram_image').val('');
        jQuery('#defectogram_image_prev').attr('href', '#').addClass('hide');
        jQuery('#defectogram_image_remove').addClass('hide').removeClass('i-block');
    }
});

function removeFileTestReportUt(e) {
    e.stopImmediatePropagation();
    toastDetailDelete('Are You Sure, You Want To Delete ?', () => {
        var oldImg = jQuery('#defectogram_image_doc').val();

        if (oldImg != "" && oldImg.includes('temp_media/')) {
            removeMedia(oldImg);
        }

        jQuery('#defectogram_image_doc').val('');
        jQuery('#defectogram_image').val('');

        jQuery('#defectogram_image_prev').attr('href', '#').text('View').addClass('hide');
        jQuery('#defectogram_image_remove').addClass('hide').removeClass('i-block');
    });
}

function removeMedia(docName) {
    let form_data2 = new FormData();
    form_data2.append('docs[]', docName);
    jQuery.ajax({
        url: "remove-docs",
        type: 'POST',
        data: form_data2,
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function (data) {
            if (data.response_code == 1) {
                console.log(data.response_message);
            }
        }
    });
}

function handleNablTypeChange() {
    let nablType = jQuery('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
    if (nablType === 'NABL') {
        jQuery('#total_qty').val(1);
        jQuery('#addDetailRowBtn').prop('disabled', true).show();

        setSelect2Readonly('#ulr_id', false);
        jQuery('#ulr_id').removeClass('skip-tab').prop('required', true).trigger('change');

        jQuery('#ulr_sequence').prop('readonly', false).prop('required', true).removeAttr('tabindex');
        jQuery('#ulr_no').prop('readonly', true);
        jQuery('.astric_ulr').html('<sup class="astric">*</sup>');
        jQuery('.astric_ulr_seq').html('<sup class="astric">*</sup>');

        let $ulr = jQuery('#ulr_id');
        let $options = $ulr.find('option[value!=""]');

        if ($options.length === 1) {
            $ulr.val($options.first().val()).trigger('change');
            setSelect2Readonly('#ulr_id', true);
            jQuery('#ulr_id').addClass('skip-tab');
        } else {
            setSelect2Readonly('#ulr_id', false);
            jQuery('#ulr_id').removeClass('skip-tab');
        }

        jQuery('#amendment_no').prop('disabled', false);
        jQuery('#amendment_date').prop('disabled', false);
        jQuery('#amendment_reason').prop('disabled', false);

    } else {
        let activeRows = test_report_ut_details_data.filter(r => r.mode !== 'Delete');
        updateQtySummary(activeRows);
        jQuery('#addDetailRowBtn').prop('disabled', false).show();

        jQuery('#ulr_id').val('').prop('required', false).trigger('change');
        setSelect2Readonly('#ulr_id', true);
        jQuery('#ulr_id').addClass('skip-tab');

        jQuery('#ulr_sequence').val('').prop('readonly', true).prop('required', false).attr('tabindex', '-1');
        jQuery('#ulr_no').val('').prop('readonly', true);
        jQuery('#ulr_year').val('');
        jQuery('.astric_ulr').html('');
        jQuery('.astric_ulr_seq').html('');

        jQuery('#amendment_no').val('').prop('disabled', true).removeClass('is-invalid');
        jQuery('#amendment_date').val('').prop('disabled', true).removeClass('is-invalid');
        jQuery('#amendment_reason').val('').prop('disabled', true).removeClass('is-invalid');
    }
}

jQuery(document).on('change', 'input[name="nabl_type_fix"]', function () {
    handleNablTypeChange();
});

var _preventUlrFetch = false;

jQuery(document).on('change', '#ulr_id, #test_report_date', function () {
    let nablType = jQuery('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
    if (nablType === 'NABL' && !_openForEdit && !_preventUlrFetch) {
        let ulrId = jQuery('#ulr_id').val();
        let date = jQuery('#test_report_date').val();

        let newYear = null;
        if (date && date.includes('/')) {
            let parts = date.split('/');
            if (parts.length === 3) {
                newYear = parts[2];
            }
        }

        let reportId = jQuery('#id').val();
        if (reportId && dbUlrId && ulrId == dbUlrId && newYear == dbUlrYear) {
            _preventUlrFetch = true;
            jQuery('#ulr_sequence').val(dbUlrSequence);
            jQuery('#ulr_no').val(dbUlrNo);
            jQuery('#ulr_year').val(dbUlrYear);
            _preventUlrFetch = false;
        } else {
            fetchLatestUlrNo();
        }
    }
});

jQuery('#ulr_sequence').on('change', function () {
    let seq = jQuery(this).val();
    if (seq && !_preventUlrFetch) {
        checkUlrSequenceDuplication(seq);
    }
});

function checkUlrSequenceDuplication(seq) {
    let ulrId = jQuery('#ulr_id').val();
    let date = jQuery('#test_report_date').val();
    let id = jQuery('#id').val() || '';

    if (ulrId && date) {
        jQuery.ajax({
            type: 'GET',
            url: 'check-ut-ulr_no',
            data: {
                ulr_sequence: seq,
                ulr_id: ulrId,
                date: date,
                id: id
            },
            success: function (data) {
                if (data.response_code == 1) {
                    fetchLatestUlrNo(seq);
                } else {
                    toastr.error("Duplicate ULR No. Found.");
                    jQuery('#ulr_sequence').val('').trigger('change');
                    const input = document.getElementById('ulr_sequence');
                    input?.focus();
                }
            },
            error: function () {
                toastr.error('Error checking ULR sequence.');
                jQuery('#ulr_sequence').val('').trigger('change');
                const input = document.getElementById('ulr_sequence');
                input?.focus();
            }
        });
    }
}

function fetchLatestUlrNo(customSeq = '') {
    let ulrId = jQuery('#ulr_id').val();
    let date = jQuery('#test_report_date').val();
    let id = jQuery('#id').val() || '';

    if (ulrId && date) {
        let payload = {
            ulr_id: ulrId,
            test_report_date: date,
            id: id
        };
        if (customSeq !== '') {
            payload.ulr_sequence = customSeq;
        }
        jQuery.ajax({
            type: 'GET',
            url: 'get-latest_ut_url_no',
            data: payload,
            success: function (data) {
                if (data.response_code == 1) {
                    jQuery('#ulr_no').val(data.number);
                    jQuery('#ulr_sequence').val(data.ulr_sequence);
                    if (date.includes('/')) {
                        let parts = date.split('/');
                        if (parts.length === 3) {
                            jQuery('#ulr_year').val(parts[2]);
                        }
                    }
                } else {
                    toastr.error(data.response_message || 'Error fetching ULR number.');
                    jQuery('#ulr_no').val('');
                    jQuery('#ulr_sequence').val('');
                    jQuery('#ulr_year').val('');
                }
            },
            error: function () {
                toastr.error('Error fetching ULR number.');
                jQuery('#ulr_no').val('');
                jQuery('#ulr_sequence').val('');
                jQuery('#ulr_year').val('');
            }
        });
    } else {
        jQuery('#ulr_no').val('');
        jQuery('#ulr_sequence').val('');
        jQuery('#ulr_year').val('');
    }
}

// Suggestions mapping functions
function suggestCustomerClient(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#customer_client_list',
        url: 'test_report_ut_customer_client-list',
        responseListKey: 'customerClientList'
    });
}
function suggestPartNo(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#part_no_list',
        url: 'test_report_ut_part_no-list',
        responseListKey: 'partNoList'
    });
}
function suggestDrgNo(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#drg_no_list',
        url: 'test_report_ut_drg_no-list',
        responseListKey: 'drgNoList'
    });
}
function suggestSurfaceCondition(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#surface_condition_list',
        url: 'test_report_ut_surface_condition-list',
        responseListKey: 'surfaceConditionList'
    });
}
function suggestCouplant(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#couplant_list',
        url: 'test_report_ut_couplant-list',
        responseListKey: 'couplantList'
    });
}
function suggestTestTechnique(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#test_technique_list',
        url: 'test_report_ut_test_technique-list',
        responseListKey: 'testTechniqueList'
    });
}
function suggestRefBlockUsed(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#ref_block_used_list',
        url: 'test_report_ut_ref_block_used-list',
        responseListKey: 'refBlockUsedList'
    });
}
function suggestScanningArea(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#scanning_area_list',
        url: 'test_report_ut_scanning_area-list',
        responseListKey: 'scanningAreaList'
    });
}
function suggestScanPlanNo(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#scan_plan_no_list',
        url: 'test_report_ut_scan_plan_no-list',
        responseListKey: 'scanPlanNoList'
    });
}
function suggestTestCarriedOutAt(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#test_carried_out_at_list',
        url: 'test_report_ut_test_carried_out_at-list',
        responseListKey: 'testCarriedOutAtList'
    });
}
function suggestSurfaceTemp(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#surface_temp_list',
        url: 'test_report_ut_surface_temp-list',
        responseListKey: 'surfaceTempList'
    });
}
function suggestThickness(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#thickness_list',
        url: 'test_report_ut_thickness-list',
        responseListKey: 'thicknessList'
    });
}
function suggestProductCode(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#product_code_list',
        url: 'test_report_ut_product_code-list',
        responseListKey: 'productCodeList'
    });
}

function validateAmendmentFields() {
    var amendmentNo = jQuery('#amendment_no').val() ? jQuery('#amendment_no').val().trim() : '';
    var amendmentDate = jQuery('#amendment_date').val() ? jQuery('#amendment_date').val().trim() : '';

    if (amendmentNo !== '') {
        jQuery('#amendment_date').prop('required', true);
    } else {
        jQuery('#amendment_date').prop('required', false).removeClass('is-invalid');
    }

    if (amendmentDate !== '') {
        jQuery('#amendment_no').prop('required', true);
    } else {
        jQuery('#amendment_no').prop('required', false).removeClass('is-invalid');
    }
}

jQuery(document).on('change', '#amendment_no, #amendment_date', function () {
    validateAmendmentFields();
});

// Trigger on load/edit
jQuery('#TestReportUtModal').on('shown.bs.modal', function () {
    validateAmendmentFields();
    setTimeout(function () {
        let seqInput = jQuery('#test_report_sequence');
        if (seqInput.prop('readonly')) {
            let dateInput = jQuery('#test_report_date');
            dateInput.focus().select();
            setTimeout(function () {
                dateInput.datepicker('hide');
            }, 50);
        } else {
            seqInput.focus().select();
        }
    }, 150);
});

// Modal events
jQuery('#TestReportUtModal').on('show.bs.modal', function () {
    if (!_openForEdit) {
        resetMainForm();
    }
});

// Detail modal hidden focus redirection
jQuery('#UTReportDetailsModal').on('hidden.bs.modal', function () {
    setTimeout(function () {
        jQuery('#tested_by_authority_person_id').focus();
    }, 200);
});

jQuery('#UTEquipmentDetailsModal').on('hidden.bs.modal', function () {
    jQuery('#addProbeRowBtn').focus();
});

jQuery('#UTProbeDetailsModal').on('hidden.bs.modal', function () {
    jQuery('#addDetailRowBtn').focus();
});

jQuery('#resetbtn').on('click', function (e) {
    e.preventDefault();
    let formId = jQuery('#id').val();
    if (!formId) {
        resetMainForm();
    } else {
        fetchAndFillTestReportUt(formId);
    }
});

// Setup Datepickers and Select2
jQuery(document).ready(function () {
    jQuery('.trans-date-picker').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true
    }).datepicker('setDate', new Date());

    jQuery('.date-picker').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true
    });

    jQuery('.js-example-basic-single').select2({
        dropdownParent: jQuery('#TestReportUtModal')
    });

    jQuery('#detail_eu_id').select2({ dropdownParent: jQuery('#UTEquipmentDetailsModal') });
    jQuery('#detail_pu_id').select2({ dropdownParent: jQuery('#UTProbeDetailsModal') });
    jQuery('#detail_result_id').select2({ dropdownParent: jQuery('#UTReportDetailsModal') });
});

// Copy button click to open modal
jQuery(document).on('click', '#copy_report_btn', function () {
    let typeOfJobId = jQuery('#type_of_job_id').val();

    jQuery.ajax({
        url: 'get-test_report_ut_copy_list',
        type: 'GET',
        data: { type_of_job_id: typeOfJobId },
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                copyReportsData = data.reports;
                fillCopyReportModalTable();
                jQuery('#CopyReportUtModal').modal('show');
            }
        }
    });
});

function fillCopyReportModalTable() {
    selectedCopyReportUtId = null;
    let $table = jQuery("#CopyReportUtModal").find('#CopyReportUtTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().clear().destroy();
    }

    let tbody = jQuery('#CopyReportUtTable tbody');
    tbody.empty();

    if (copyReportsData.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="13" class="text-center">No Reports Available</td>
            </tr>
        `);
        return;
    }

    copyReportsData.forEach(item => {
        let isChecked = (selectedCopyReportUtId && selectedCopyReportUtId == item.id) ? 'checked' : '';
        let rowHtml = `<tr>
            <td>
                <input type="radio" name="copy_report_id" value="${item.id}" class="form-check-input select-copy-report-radio radio_item_select" ${isChecked}>
            </td>
            <td>${item.test_report_no || ''}</td>
            <td>${item.test_report_date || ''}</td>
            <td>${item.customer || ''}</td>
            <td>${item.nabl_type_fix || ''}</td>
            <td>${item.job_type_fix || ''}</td>
            <td>${item.type_of_job || ''}</td>
            <td>${item.job_description || ''}</td>
            <td>${item.part_no || ''}</td>
            <td>${item.drg_no || ''}</td>
            <td>${item.material || ''}</td>
            <td>${item.heat_no || ''}</td>
            <td>${item.product_code || ''}</td>
        </tr>`;
        tbody.append(rowHtml);
    });

    var $new = $table.DataTable({
        paging: true,
        searching: true,
        dom: 'lrtip',
        "sScrollX": true,
        "sScrollX": "100%",
        "sScrollXInner": "110%",
        "bScrollCollapse": true,
    });

    $new.on('draw', function () {
        if (selectedCopyReportUtId) {
            jQuery(`input[name="copy_report_id"][value="${selectedCopyReportUtId}"]`).prop('checked', true);
        }
    });

    if (typeof fixDataTableColumnsUntilAdjusted === 'function') {
        fixDataTableColumnsUntilAdjusted($new);
    }
}

jQuery('#CopyReportUtModal').on('shown.bs.modal', function () {
    let $table = jQuery('#CopyReportUtTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().columns.adjust().draw();
        if (typeof initColumnSearch === 'function') {
            initColumnSearch('#CopyReportUtTable', [0], 'common_search');
        }
    }
});

jQuery(document).on('change', 'input[name="copy_report_id"]', function () {
    selectedCopyReportUtId = jQuery(this).val();
});

jQuery(document).on('click', '#submitCopyReportUtBtn', function () {
    let reportId = selectedCopyReportUtId || jQuery('input[name="copy_report_id"]:checked').val();
    if (!reportId) {
        toastr.error('Please select Report.');
        return;
    }

    jQuery('#CopyReportUtModal').modal('hide');

    Swal.fire({
        title: 'Confirmation',
        text: 'Do You want to Copy Master Detail?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        customClass: {
            confirmButton: 'btn btn-primary w-xs me-2 mt-2',
            cancelButton: 'btn btn-danger w-xs mt-2',
        },
        buttonsStyling: false,
        showCloseButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        focusConfirm: true,
    }).then((resultMaster) => {
        let copyMaster = resultMaster.isConfirmed;

        Swal.fire({
            title: 'Confirmation',
            text: 'Do You want to Copy Test Report Details?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            customClass: {
                confirmButton: 'btn btn-primary w-xs me-2 mt-2',
                cancelButton: 'btn btn-danger w-xs mt-2',
            },
            buttonsStyling: false,
            showCloseButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            focusConfirm: true,
        }).then((resultDetails) => {
            let copyDetails = resultDetails.isConfirmed;

            if (!copyMaster && !copyDetails) {
                return;
            }

            performCopyReport(reportId, copyMaster, copyDetails);
        });
    });
});

function performCopyReport(reportId, copyMaster, copyDetails) {
    if (typeof showLoader === 'function') showLoader();
    jQuery.ajax({
        url: 'get-test_report_ut_details_for_copy',
        type: 'GET',
        data: { id: reportId },
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.report_data != null) {
                var d = data.report_data;

                if (copyMaster) {
                    jQuery('#customer_client').val(d.customer_client || '');
                    jQuery('#surface_condition').val(d.surface_condition || '');
                    jQuery('#surface_temp').val(d.surface_temp || '');
                    jQuery('#couplant').val(d.couplant || '');
                    jQuery('#test_technique').val(d.test_technique || '');
                    jQuery('#ref_block_used').val(d.ref_block_used || '');
                    jQuery('#scanning_area').val(d.scanning_area || '');
                    jQuery('#scan_plan_no').val(d.scan_plan_no || '');

                    // Do not copy required fields (tested_by_authority_person_id is required, reviewed & authorized are not)
                    jQuery('#reviewed_by_authority_person_id').val(zeroToEmpty(d.reviewed_by_authority_person_id)).trigger('change.select2');
                    jQuery('#authorized_by_authority_person_id').val(zeroToEmpty(d.authorized_by_authority_person_id)).trigger('change.select2');
                }

                /*
                if (copyDetails) {
                    // Copy test report details
                    test_report_ut_details_data = [];
                    if (data.report_details_data && data.report_details_data.length > 0) {
                        var nablType = jQuery('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
                        var detailsToCopy = data.report_details_data;
                        if (nablType === 'NABL') {
                            detailsToCopy = [data.report_details_data[data.report_details_data.length - 1]];
                        }
                        detailsToCopy.forEach((row, idx) => {
                            test_report_ut_details_data.push({
                                test_report_ut_details_id: '',
                                sr_no: nablType === 'NABL' ? 1 : parseFloat(row.sr_no || (idx + 1)),
                                ut_test_no: row.ut_test_no,
                                heat_no: row.heat_no,
                                quantity: (nablType === 'NABL') ? 1 : parseInt(row.quantity || 0),
                                discontinuity_evaluation: row.discontinuity_evaluation,
                                detail_result_id: row.result_id,
                                result_name: row.result_name,
                                mode: 'Insert'
                            });
                        });
                    }
                    resequenceReportDetails(false);
                    fillReportDetailTable();
                }
                */

                // Focus on type of job dropdown after copy completes
                setTimeout(function () {
                    jQuery('#type_of_job_id').focus();
                }, 100);
            }
        },
        complete: function () {
            if (typeof hideLoader === 'function') hideLoader();
        }
    });
}

function validateAmendmentFields() {
    var amendmentNo = jQuery('#amendment_no').val() ? jQuery('#amendment_no').val().trim() : '';
    var amendmentDate = jQuery('#amendment_date').val() ? jQuery('#amendment_date').val().trim() : '';

    if (amendmentNo !== '') {
        jQuery('#amendment_date').prop('required', true);
    } else {
        jQuery('#amendment_date').prop('required', false).removeClass('is-invalid');
    }

    if (amendmentDate !== '') {
        jQuery('#amendment_no').prop('required', true);
    } else {
        jQuery('#amendment_no').prop('required', false).removeClass('is-invalid');
    }
}

jQuery(document).on('input change', '#amendment_no, #amendment_date', function () {
    validateAmendmentFields();
});

jQuery('#TestReportUtModal').on('shown.bs.modal', function () {
    validateAmendmentFields();
});

function parseDateStr(dateStr) {
    if (!dateStr) return null;
    let parts = dateStr.split('/');
    if (parts.length === 3) {
        return new Date(parts[2], parts[1] - 1, parts[0]);
    }
    return null;
}

function validateDates() {
    let reportDate = parseDateStr(jQuery('#test_report_date').val());
    let testDate = parseDateStr(jQuery('#date_of_testing').val());
    let receiptDate = parseDateStr(jQuery('#date_of_receipt').val());

    if (testDate && receiptDate && testDate < receiptDate) {
        toastr.error("Date Of Testing Must Be Greater Than Date Of Receipt.");
        return false;
    }
    if (reportDate && receiptDate && reportDate < receiptDate) {
        toastr.error("Report Date Must Be Greater Than Date Of Receipt.");
        return false;
    }
    if (reportDate && testDate && reportDate < testDate) {
        toastr.error("Date Of Testing Must Be Less Than Report Date.");
        return false;
    }
    return true;
}

jQuery(document).on('change', '#date_of_testing', function () {
    let val = jQuery(this).val();
    if (val) {
        jQuery('#date_of_testing_value').val(val);
    }

    let testDate = parseDateStr(val);
    let receiptDate = parseDateStr(jQuery('#date_of_receipt').val());
    if (testDate && receiptDate && testDate < receiptDate) {
        toastr.error("Date Of Testing Must Be Greater Than Date Of Receipt.");
    }

    let reportDate = parseDateStr(jQuery('#test_report_date').val());
    if (reportDate && testDate && reportDate < testDate) {
        toastr.error("Date Of Testing Must Be Less Than Report Date.");
    }
});

jQuery(document).on('change', '#test_report_date', function () {
    let reportDate = parseDateStr(jQuery(this).val());
    let receiptDate = parseDateStr(jQuery('#date_of_receipt').val());
    let testDate = parseDateStr(jQuery('#date_of_testing').val());

    if (reportDate && receiptDate && reportDate < receiptDate) {
        toastr.error("Report Date Must Be Greater Than Date Of Receipt.");
        return;
    }
    if (reportDate && testDate && reportDate < testDate) {
        toastr.error("Date Of Testing Must Be Less Than Report Date.");
    }
});

function getFormSerializedData(form) {
    let obj = {};
    let array = form.serializeArray();
    jQuery.each(array, function () {
        if (obj[this.name] !== undefined) {
            if (!obj[this.name].push) {
                obj[this.name] = [obj[this.name]];
            }
            obj[this.name].push(this.value || '');
        } else {
            obj[this.name] = this.value || '';
        }
    });
    return obj;
}

// Add New button trigger
jQuery(document).on('click', '#add_new', function () {
    resetMainForm();
    jQuery('#add_new').hide();
});

function getTRLNRData() {
    jQuery.ajax({
        url: "get-test_report_ut_lnr_data",
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.lnr_data != null) {
                var lnr = data.lnr_data;
                jQuery('#note').val(lnr.note ?? "");
            }
        },
        error: function () {
            console.log('Error fetching Test Report UT LNR data');
        }
    });
}
