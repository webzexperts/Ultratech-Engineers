var currentUrl = window.location.href;
var test_report_dpt_chemical_details_data = [];
var test_report_dpt_details_data = [];
var _openForEdit = false;
var dbUlrId = null;
var dbUlrSequence = null;
var dbUlrNo = null;
var dbUlrYear = null;
var pendingInwardData = [];
var selectedInwardDetailsId = null;
var selectedPendingDptId = null;
var reportMasterData = null;
var copyReportsData = [];
var selectedCopyReportDptId = null;

// Sequence helpers
function resequenceChemicalDetails(isDelete = false) {
    let activeRows = test_report_dpt_chemical_details_data.filter(r => r.mode !== 'Delete');
    let deleteRows = test_report_dpt_chemical_details_data.filter(r => r.mode === 'Delete');
    activeRows.forEach((row, index) => {
        row.sr_no = index + 1;
    });
    test_report_dpt_chemical_details_data = [...activeRows, ...deleteRows];
}

function resequenceReportDetails(isDelete = false) {
    let activeRows = test_report_dpt_details_data.filter(r => r.mode !== 'Delete');
    let deleteRows = test_report_dpt_details_data.filter(r => r.mode === 'Delete');
    activeRows.forEach((row, index) => {
        row.sr_no = index + 1;
    });
    test_report_dpt_details_data = [...activeRows, ...deleteRows];
}

// Draw Tables
function fillChemicalTable() {
    let tbody = jQuery('#ChemicalDetailTable tbody');
    tbody.empty();
    let validRows = test_report_dpt_chemical_details_data.filter(row => row.mode !== 'Delete');

    if (validRows.length === 0) {
        tbody.append(`<tr><td colspan="6" class="text-center">No Chemical Details Added</td></tr>`);
    } else {
        validRows.forEach((row) => {
            let formIndx = test_report_dpt_chemical_details_data.indexOf(row);
            let actionDropdown = DetailsActionDropdown('editChemicalDetails', 'removeChemicalDetails');
            let rowHtml = `<tr>
                <td>
                   ${actionDropdown}
                    <input type="hidden" name="chem_form_indx" value="${formIndx}"/>
                </td>
                <td>${row.dpt_chemical || ''}</td>
                
                <td>${row.make || ''}</td>
                <td>${row.batch_no || ''}</td>
                <td>${row.expiry_date || ''}</td>
            </tr>`;
            tbody.append(rowHtml);
        });
    }
}

function fillReportDetailTable() {
    let tbody = jQuery('#ReportDetailTable tbody');
    tbody.empty();
    let validRows = test_report_dpt_details_data.filter(row => row.mode !== 'Delete');

    if (validRows.length === 0) {
        tbody.append(`<tr><td colspan="7" class="text-center">No Test Report Details Added</td></tr>`);
    } else {
        validRows.forEach((row) => {
            let formIndx = test_report_dpt_details_data.indexOf(row);
            let actionDropdown = DetailsActionDropdown('editReportDetails', 'removeReportDetails');
            let rowHtml = `<tr>
                <td>
                   ${actionDropdown}
                    <input type="hidden" name="form_indx" value="${formIndx}"/>
                </td>
                <td>${row.sr_no}</td>
                <td>${row.dpt_test_no || ''}</td>
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
        rows = typeof test_report_dpt_details_data !== 'undefined' ? test_report_dpt_details_data.filter(row => row.mode !== 'Delete') : [];
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
jQuery(document).on('change', '#detail_dpt_id', function () {
    let opt = jQuery(this).find('option:selected');
    jQuery('#chem_designation').val(opt.data('designation') || '');
    jQuery('#chem_make').val(opt.data('make') || '');
    jQuery('#chem_batch_no').val(opt.data('batch_no') || '');
    jQuery('#chem_expiry_date').val(opt.data('expiry_date') || '');
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
        url: 'get-pending-customer-dpt-data',
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
                    selectedPendingDptId = null;
                }
                fillPendingDptModalTable();
            }
        },
        complete: function () {
            jQuery('#submitbtn').prop('disabled', false);
        }
    });
});

function fillPendingDptModalTable() {
    let $table = jQuery("#PendingInwardForDptModal").find('#PendingForDptTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().clear().destroy();
    }
    let tbody = jQuery('#PendingForDptTable tbody');
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
            <td><input type="radio" name="pending_dpt_id" value="${item.material_inward_details_id}" data-obs-id="${item.observation_sheet_details_id || ''}" data-from-type="${item.from_type_id_fix || 1}" class="form-check-input select-pending-dpt-radio radio_item_select" ${isChecked}></td>
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
            jQuery(`input[name="pending_dpt_id"]`).each(function () {
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
    fillPendingDptModalTable();
    jQuery('#PendingInwardForDptModal').modal('show');
});

jQuery('#PendingInwardForDptModal').on('shown.bs.modal', function () {
    let $table = jQuery('#PendingForDptTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().columns.adjust().draw();
        if (typeof initColumnSearch === 'function') {
            initColumnSearch('#PendingForDptTable', [0], 'common_search');
        }
    }
});

jQuery(document).on('change', 'input[name="pending_dpt_id"]', function () {
    selectedPendingDptId = jQuery(this).val();
});

jQuery(document).on('click', '#submitPendingDptBtn', function () {
    let selectedRadio = jQuery('input[name="pending_dpt_id"]:checked');
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
    jQuery('#PendingInwardForDptModal').modal('hide');

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

        jQuery('#dc_no').val(selectedItem.dc_no || '');
        jQuery('#dc_date').val(selectedItem.dc_date ? formatDateStr(selectedItem.dc_date) : '');
        jQuery('#po_no').val(selectedItem.po_no || '');
        jQuery('#po_date').val(selectedItem.po_date ? formatDateStr(selectedItem.po_date) : '');
        jQuery('#date_of_receipt').val(selectedItem.material_inward_date ? formatDateStr(selectedItem.material_inward_date) : '');
        jQuery('#heat_no').val(selectedItem.heat_no || '');
        jQuery('#product_code').val(selectedItem.product_code || '');
        jQuery('#thickness').val(selectedItem.thickness || '');
        jQuery('#part_no').val(selectedItem.part_no || '');
        jQuery('#drg_no').val(selectedItem.drg_no || '');

        let pendingQty = parseFloat(selectedItem.pending_qty || 0);
        jQuery('#pend_qty').val(pendingQty);

        if (selectedItem.type_of_job_id) {
            jQuery('#type_of_job_id').val(selectedItem.type_of_job_id).trigger('change.select2');
        }
        if (selectedItem.job_desc_id) {
            jQuery('#job_desc_id').val(selectedItem.job_desc_id).trigger('change.select2');
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

        if (!_openForEdit) {
            let initialQty = 1;
            if (selectedItem.nabl_type_fix !== 'NABL') {
                initialQty = parseInt(pendingQty || 0);
            }
            test_report_dpt_details_data = [{
                test_report_dpt_details_id: '',
                sr_no: 1,
                dpt_test_no: '',
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

// ULR Sequence and configuration population
jQuery(document).on('change', 'input[name="nabl_type_fix"]', function () {
    handleNablTypeChange();
});

function handleNablTypeChange() {
    let val = jQuery('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
    if (val === 'NABL') {
        jQuery('#total_qty').val(1);
        jQuery('#addDetailRowBtn').prop('disabled', true).show();

        if (typeof test_report_dpt_details_data !== 'undefined') {
            test_report_dpt_details_data.forEach(row => {
                if (row.mode !== 'Delete') {
                    row.quantity = 1;
                }
            });
            fillReportDetailTable();
        }

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

        jQuery('#amendment_no, #amendment_date, #amendment_reason').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
    } else {
        updateQtySummary();
        jQuery('#addDetailRowBtn').prop('disabled', false).show();

        jQuery('#ulr_id').val('').prop('required', false).trigger('change');
        setSelect2Readonly('#ulr_id', true);
        jQuery('#ulr_id').addClass('skip-tab');

        jQuery('#ulr_sequence').val('').prop('readonly', true).prop('required', false).attr('tabindex', '-1');
        jQuery('#ulr_no').val('').prop('readonly', true);
        jQuery('#ulr_year').val('');
        jQuery('.astric_ulr').html('');
        jQuery('.astric_ulr_seq').html('');

        jQuery('#amendment_no, #amendment_date, #amendment_reason').val('').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
    }
}

var _preventUlrFetch = false;

jQuery(document).on('change', '#ulr_id, #test_report_date', function () {
    let reportDateVal = jQuery('#test_report_date').val();
    let nabl = jQuery('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
    if (nabl === 'NABL' && !_openForEdit && !_preventUlrFetch) {
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
            fetchULRSequence();
        }
    }

    let reportDate = parseDateStr(reportDateVal);
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
            url: 'check-dpt-ulr_no',
            data: {
                ulr_sequence: seq,
                ulr_id: ulrId,
                date: date,
                id: id
            },
            success: function (data) {
                if (data.response_code == 1) {
                    fetchULRSequence(seq);
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

function fetchULRSequence(customSeq = '') {
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
            url: 'get-latest_dpt_url_no',
            data: payload,
            dataType: 'json',
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

// Validation helper
function parseDateStr(str) {
    if (!str) return null;
    let parts = str.split('/');
    if (parts.length === 3) {
        return new Date(parts[2], parts[1] - 1, parts[0]);
    }
    return null;
}

// Modal Form resets and openings
jQuery('#TestReportDptModal').on('show.bs.modal', function () {
    if (!_openForEdit) {
        resetDptForm();
    }
});

function fillCustomerDropdown() {
    let customerDropdown = jQuery('#customer_id');
    customerDropdown.empty().append('<option value="">Select Customer</option>');

    jQuery.ajax({
        url: 'get-pending-customers-for-dpt',
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
                    customerDropdown.val(reportMasterData.customer_id).trigger('change.select2');
                    if (jQuery('#id').val()) {
                        setTimeout(() => {
                            setSelect2Readonly('#customer_id', true);
                        }, 500);
                    }
                } else {
                    customerDropdown.trigger('change.select2');
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

jQuery('#TestReportDptModal').on('shown.bs.modal', function () {
    jQuery('#test_report_sequence').focus();
    validateAmendmentFields();
});

function resetDptForm() {
    _openForEdit = false;
    dbUlrSequence = null;
    dbUlrNo = null;
    dbUlrId = null;
    dbUlrYear = null;
    selectedPendingDptId = null;
    selectedCopyReportDptId = null;
    reportMasterData = null;
    selectedInwardDetailsId = null;

    var lastNote = jQuery('#note').val();
    jQuery('#commonTestReportDptForm')[0].reset();
    jQuery('#note').val(lastNote);
    jQuery('#id').val('');
    jQuery('#material_inward_details_id').val('');
    jQuery('#observation_sheet_details_id').val('');
    jQuery('#from_type_id_fix').val('');
    jQuery('#customer_id').val('').trigger('change');
    jQuery('#type_of_job_id').val('').trigger('change');
    jQuery('#job_desc_id').val('').trigger('change');
    jQuery('#material_id').val('').trigger('change');
    jQuery('#area_of_coverage_id').val('').trigger('change');
    jQuery('#procedure_ref_id').val('').trigger('change');
    jQuery('#acceptance_standard_id').val('').trigger('change');
    jQuery('#tested_by_authority_person_id').val('').trigger('change');
    jQuery('#reviewed_by_authority_person_id').val('').trigger('change');
    jQuery('#authorized_by_authority_person_id').val('').trigger('change');

    jQuery('#defectogram_image_doc').val('');
    jQuery('#defectogram_image').val('');
    jQuery('#defectogram_image_remove').addClass('hide');
    jQuery('#defectogram_image_prev').addClass('hide').attr('href', '#');
    jQuery('#amendment_no, #amendment_date, #amendment_reason').val('').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');

    let dateVal = (typeof currentDate !== 'undefined') ? currentDate : new Date().toLocaleDateString('en-GB');
    jQuery('#test_report_date').val(dateVal).trigger('change');

    test_report_dpt_chemical_details_data = [];
    test_report_dpt_details_data = [];

    fillChemicalTable();
    fillReportDetailTable();

    setRadioReadonly('input[name="nabl_type_fix"]', false);
    setRadioReadonly('input[name="job_type_fix"]', false);
    jQuery('input[name="nabl_type_fix"][value="Non NABL"]').prop('checked', true).trigger('change');
    jQuery('input[name="job_type_fix"][value="Non-Welding"]').prop('checked', true);
    setRadioReadonly('input[name="nabl_type_fix"]', true);
    setRadioReadonly('input[name="job_type_fix"]', true);

    setSelect2Readonly('#customer_id', false);
    jQuery('#pending_btn').prop('disabled', true);
    jQuery('#copy_report_btn').prop('disabled', false);

    jQuery('#commonTestReportDptForm').removeClass('was-validated');

    jQuery('#preview_btn').hide();
    jQuery('#add_new').hide();

    jQuery('#submitbtn').prop('disabled', false);

    fillCustomerDropdown();

    // Get latest sequence
    jQuery.ajax({
        url: 'get-latest-test_report_dpt_number',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#test_report_sequence').val(data.number);
                jQuery('#test_report_no').val(data.latest_no);
                jQuery('#test_report_sequence').focus();
            }
        }
    });

    // Fetch last report details to carry forward Chemical details
    jQuery.ajax({
        url: 'get-last-dpt-details',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                test_report_dpt_chemical_details_data = [];
                if (data.chemical_details && data.chemical_details.length > 0) {
                    data.chemical_details.forEach(c => {
                        test_report_dpt_chemical_details_data.push({
                            test_report_dpt_chemical_details_id: '',
                            dpt_id: c.dpt_id,
                            dpt_chemical: c.dpt_chemical,
                            //chemical_designation: c.chemical_designation,
                            make: c.make,
                            batch_no: c.batch_no,
                            expiry_date: c.expiry_date,
                            mode: 'Insert'
                        });
                    });
                }
                resequenceChemicalDetails();
                fillChemicalTable();
            }
        }
    });

    getTRLNRData();
}

// Resequencing details actions
function DetailsActionDropdown(editFunc, removeFunc) {
    return `<div class="dropdown d-inline-block">
        <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="ri-more-fill align-middle"></i>
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="javascript:void(0)" onclick="${editFunc}(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit</a></li>
            <li><a class="dropdown-item" href="javascript:void(0)" onclick="${removeFunc}(this)"><i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete</a></li>
        </ul>
    </div>`;
}

// -----------------------------------------------------
// Grid 1: DPT Chemical Details row actions
// -----------------------------------------------------
jQuery('#addChemicalRowBtn').on('click', function () {
    jQuery('#DPTChemicalDetailsForm')[0].reset();
    jQuery('#detail_dpt_id').find('.temp-option').remove();
    jQuery('#detail_dpt_id').val('').trigger('change');
    jQuery('#chem_form_type').val('add');
    jQuery('#chem_form_index').val('');
    jQuery('#chem_row_index').val('');
    jQuery('#DPTChemicalDetailsModal').modal('show');
});

jQuery('#submitChemicalRowBtn').on('click', function () {
    let form = jQuery('#DPTChemicalDetailsForm');
    if (form[0].checkValidity() === false) {
        form.addClass('was-validated');
        return;
    }

    let dptId = jQuery('#detail_dpt_id').val();
    let chemicalText = jQuery('#detail_dpt_id option:selected').data('dpt_chemical') || '';
    let designation = jQuery('#chem_designation').val();
    let make = jQuery('#chem_make').val();
    let batch = jQuery('#chem_batch_no').val();
    let expiry = jQuery('#chem_expiry_date').val();

    let type = jQuery('#chem_form_type').val();
    let index = jQuery('#chem_form_index').val();

    let d1 = parseDateStr(jQuery('#date_of_testing').val());
    let d2 = parseDateStr(expiry);
    if (d1 && d2 && d1 > d2) {
        toastr.error('This DPT Chemical is over to its Expiry Date.');
        return;
    }

    // Prevent duplicate Chemical entry
    let isDuplicate = test_report_dpt_chemical_details_data.some((row, idx) => {
        if (row.mode === 'Delete') return false;
        if (type === 'edit' && idx == index) return false;
        return row.dpt_id == dptId;
    });

    if (isDuplicate) {
        toastr.error('Duplicate Chemical Found.');
        return;
    }

    if (type === 'add') {
        test_report_dpt_chemical_details_data.push({
            test_report_dpt_chemical_details_id: '',
            dpt_id: dptId,
            dpt_chemical: chemicalText,
            //chemical_designation: designation,
            make: make,
            batch_no: batch,
            expiry_date: expiry,
            mode: 'Insert'
        });
        resequenceChemicalDetails();
        fillChemicalTable();

        // Reset sub-form and keep modal open for next entry
        jQuery('#DPTChemicalDetailsForm')[0].reset();
        jQuery('#detail_dpt_id').val('').trigger('change');
        jQuery('#chem_form_type').val('add');
        jQuery('#chem_form_index').val('');
        jQuery('#chem_row_index').val('');
        jQuery('#detail_dpt_id').focus();
    } else {
        let row = test_report_dpt_chemical_details_data[index];
        row.dpt_id = dptId;
        row.dpt_chemical = chemicalText;
        //row.chemical_designation = designation;
        row.make = make;
        row.batch_no = batch;
        row.expiry_date = expiry;
        if (row.mode !== 'Insert') {
            row.mode = 'Update';
        }
        resequenceChemicalDetails();
        fillChemicalTable();
        jQuery('#DPTChemicalDetailsModal').modal('hide');
    }
});

function editChemicalDetails(elem) {
    let index = jQuery(elem).parents('tr').find('input[name="chem_form_indx"]').val();
    let row = test_report_dpt_chemical_details_data[index];

    jQuery('#chem_form_type').val('edit');
    jQuery('#chem_form_index').val(index);
    jQuery('#test_report_dpt_chemical_details_id').val(row.test_report_dpt_chemical_details_id);

    let dptSelect = jQuery('#detail_dpt_id');
    dptSelect.find('.temp-option').remove();
    if (row.dpt_id && dptSelect.find(`option[value="${row.dpt_id}"]`).length === 0) {
        let parts = [
            row.dpt_chemical,
            row.batch_no,
            //row.chemical_designation,
            row.expiry_date
        ].filter(Boolean);
        let displayText = parts.length > 0 ? parts.join(' - ') : 'DPT Chemical';

        let opt = jQuery('<option></option>')
            .addClass('temp-option')
            .val(row.dpt_id)
            .text(displayText)
            .attr('data-dpt_chemical', row.dpt_chemical || '')
            //.attr('data-designation', row.chemical_designation || '')
            .attr('data-make', row.make || '')
            .attr('data-batch_no', row.batch_no || '')
            .attr('data-expiry_date', row.expiry_date || '');
        dptSelect.append(opt);
    }
    dptSelect.val(row.dpt_id).trigger('change');

    //jQuery('#chem_designation').val(row.chemical_designation);
    jQuery('#chem_make').val(row.make);
    jQuery('#chem_batch_no').val(row.batch_no);
    jQuery('#chem_expiry_date').val(row.expiry_date);

    jQuery('#DPTChemicalDetailsModal').modal('show');
}

function removeChemicalDetails(elem) {
    let index = jQuery(elem).parents('tr').find('input[name="chem_form_indx"]').val();
    toastDetailDelete("Do you want to delete this record?", function () {
        let row = test_report_dpt_chemical_details_data[index];

        if (row.test_report_dpt_chemical_details_id === '') {
            test_report_dpt_chemical_details_data.splice(index, 1);
        } else {
            row.mode = 'Delete';
        }

        resequenceChemicalDetails();
        fillChemicalTable();
    });
}

// -----------------------------------------------------
// Grid 2: Test Report Details row actions
// -----------------------------------------------------
jQuery('#addDetailRowBtn').on('click', function () {
    jQuery('#DPTReportDetailsForm')[0].reset();
    jQuery('#detail_result_id').val('').trigger('change');
    jQuery('#det_form_type').val('add');
    jQuery('#det_form_index').val('');
    jQuery('#det_row_index').val('');

    let nablType = jQuery('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
    if (nablType === 'NABL') {
        jQuery('#det_quantity').val(1).prop('readonly', true).attr('tabindex', '-1');
    } else {
        jQuery('#det_quantity').prop('readonly', false).removeAttr('tabindex');
    }

    jQuery('#DPTReportDetailsModal').modal('show');
});

jQuery('#submitDetailsRowBtn').on('click', function () {
    let form = jQuery('#DPTReportDetailsForm');
    if (form[0].checkValidity() === false) {
        form.addClass('was-validated');
        return;
    }

    let testNo = jQuery('#det_dpt_test_no').val();
    let heatNo = jQuery('#det_heat_no').val();
    let quantity = parseInt(jQuery('#det_quantity').val() || 0);
    if (quantity < 1) {
        toastr.error('Enter Quantity greater than 0.');
        return;
    }
    let evaluation = jQuery('#det_discontinuity_evaluation').val();
    let resultId = jQuery('#detail_result_id').val();
    let resultText = jQuery('#detail_result_id option:selected').text().trim();

    let type = jQuery('#det_form_type').val();
    let index = jQuery('#det_form_index').val();

    // Prevent duplicate DPT Test No.
    let isDuplicate = test_report_dpt_details_data.some((row, idx) => {
        if (row.mode === 'Delete') return false;
        if (type === 'edit' && idx == index) return false;
        return row.dpt_test_no == testNo;
    });

    // if (isDuplicate) {
    //     toastr.error('Duplicate DPT Test No. Entry Not Allowed.');
    //     return;
    // }

    if (type === 'add') {
        test_report_dpt_details_data.push({
            test_report_dpt_details_id: '',
            dpt_test_no: testNo,
            heat_no: heatNo,
            quantity: quantity,
            discontinuity_evaluation: evaluation,
            detail_result_id: resultId,
            result_name: resultText,
            mode: 'Insert'
        });
        resequenceReportDetails();
        fillReportDetailTable();

        // Reset details sub-form and keep modal open for next entry
        jQuery('#DPTReportDetailsForm')[0].reset();
        jQuery('#detail_result_id').val('').trigger('change');
        jQuery('#det_form_type').val('add');
        jQuery('#det_form_index').val('');
        jQuery('#det_row_index').val('');

        let nablType = jQuery('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
        if (nablType === 'NABL') {
            jQuery('#det_quantity').val(1).prop('readonly', true).attr('tabindex', '-1');
        } else {
            jQuery('#det_quantity').prop('readonly', false).removeAttr('tabindex');
        }
        jQuery('#det_dpt_test_no').focus();
    } else {
        let row = test_report_dpt_details_data[index];
        row.dpt_test_no = testNo;
        row.heat_no = heatNo;
        row.quantity = quantity;
        row.discontinuity_evaluation = evaluation;
        row.detail_result_id = resultId;
        row.result_name = resultText;
        if (row.mode !== 'Insert') {
            row.mode = 'Update';
        }
        resequenceReportDetails();
        fillReportDetailTable();
        jQuery('#DPTReportDetailsModal').modal('hide');
    }
});

function editReportDetails(elem) {
    let index = jQuery(elem).parents('tr').find('input[name="form_indx"]').val();
    let row = test_report_dpt_details_data[index];

    jQuery('#det_form_type').val('edit');
    jQuery('#det_form_index').val(index);
    jQuery('#test_report_dpt_details_id').val(row.test_report_dpt_details_id);

    jQuery('#det_dpt_test_no').val(row.dpt_test_no);
    jQuery('#det_heat_no').val(row.heat_no);
    jQuery('#det_quantity').val(row.quantity);
    jQuery('#det_discontinuity_evaluation').val(row.discontinuity_evaluation);
    jQuery('#detail_result_id').val(row.detail_result_id).trigger('change');

    let nablType = jQuery('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
    if (nablType === 'NABL') {
        jQuery('#det_quantity').val(1).prop('readonly', true).attr('tabindex', '-1');
    } else {
        jQuery('#det_quantity').prop('readonly', false).removeAttr('tabindex');
    }

    jQuery('#DPTReportDetailsModal').modal('show');
}

function removeReportDetails(elem) {
    let index = jQuery(elem).parents('tr').find('input[name="form_indx"]').val();
    toastDetailDelete("Do you want to delete this record?", function () {
        let row = test_report_dpt_details_data[index];

        if (row.test_report_dpt_details_id === '') {
            test_report_dpt_details_data.splice(index, 1);
        } else {
            row.mode = 'Delete';
        }

        resequenceReportDetails();
        fillReportDetailTable();
    });
}

// File upload validation & handler
function validateImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i;
    return allowedExtensions.exec(filePath) ? true : false;
}

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
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#submitbtn').prop('disabled', false);
                    jQuery('#defectogram_image').removeClass('file-loader');
                    if (jqXHR.status == 401) {
                        toastr.error(jqXHR.statusText);
                    } else {
                        toastr.error('Something went wrong!');
                    }
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
        jQuery('#defectogram_image_remove').removeClass('i-block').addClass('hide');
    }
});

function removeFileTestReportDpt(e) {
    e.preventDefault();
    jQuery('#defectogram_image').val('');
    jQuery('#defectogram_image_doc').val('');
    jQuery('#defectogram_image_remove').addClass('hide');
    jQuery('#defectogram_image_prev').addClass('hide').attr('href', '#');
}

// Fetch and Fill DPT Test Report details for Edit/Reset
function fetchAndFillTestReportDpt(id) {
    if (!id) return;
    _openForEdit = true;

    // Disable submit button until loading is complete
    jQuery('#submitbtn').prop('disabled', true);

    skipLoader = false;
    showLoader();

    jQuery.ajax({
        url: 'edit-test_report_dpt',
        type: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function (res) {
            if (res.response_code == 1) {
                reportMasterData = res.report_data;
                selectedInwardDetailsId = reportMasterData.material_inward_details_id;
                selectedPendingDptId = reportMasterData.material_inward_details_id || null;
                selectedCopyReportDptId = null;
                jQuery('#observation_sheet_details_id').val(reportMasterData.observation_sheet_details_id || '');

                let previewUrl = checkFileRoute + "?id=" + btoa(reportMasterData.test_report_dpt_id) + "&name=" + reportMasterData.pdf_name + "&type=test_report_dpt";
                jQuery('#preview_btn').attr('href', previewUrl).show();
                jQuery('#add_new').show();

                // Disable selection and actions in edit mode
                setSelect2Readonly('#customer_id', true);
                jQuery('#pending_btn').prop('disabled', true);
                jQuery('#copy_report_btn').prop('disabled', true);

                jQuery('#id').val(reportMasterData.test_report_dpt_id);
                jQuery('#test_report_sequence').val(reportMasterData.test_report_sequence);
                jQuery('#test_report_no').val(reportMasterData.test_report_no);
                jQuery('#test_report_date').val(reportMasterData.test_report_date);
                jQuery('#customer_client').val(reportMasterData.customer_client);
                jQuery('#part_no').val(reportMasterData.part_no);
                jQuery('#drg_no').val(reportMasterData.drg_no);
                jQuery('#heat_no').val(reportMasterData.heat_no);
                jQuery('#product_code').val(reportMasterData.product_code);
                jQuery('#dc_no').val(reportMasterData.dc_no);
                jQuery('#dc_date').val(reportMasterData.dc_date);
                jQuery('#po_no').val(reportMasterData.po_no);
                jQuery('#po_date').val(reportMasterData.po_date);
                jQuery('#date_of_receipt').val(reportMasterData.date_of_receipt);
                jQuery('#date_of_testing').val(reportMasterData.date_of_testing);
                jQuery('#date_of_testing_value').val(reportMasterData.date_of_testing_value);
                jQuery('#test_carried_out_at').val(reportMasterData.test_carried_out_at);
                jQuery('#amendment_no').val(reportMasterData.amendment_no);
                jQuery('#amendment_date').val(reportMasterData.amendment_date);
                jQuery('#amendment_reason').val(reportMasterData.amendment_reason);
                jQuery('#stage_of_test').val(reportMasterData.stage_of_test);

                jQuery('#surface_condition').val(reportMasterData.surface_condition);
                jQuery('#surface_temp').val(reportMasterData.surface_temp);
                jQuery('#thickness').val(reportMasterData.thickness);
                jQuery('#test_technique').val(reportMasterData.test_technique);
                jQuery('#pre_cleaning').val(reportMasterData.pre_cleaning);
                jQuery('#penetrant_application').val(reportMasterData.penetrant_application);
                jQuery('#dwell_time').val(reportMasterData.dwell_time);
                jQuery('#penetrant_remover').val(reportMasterData.penetrant_remover);
                jQuery('#developer').val(reportMasterData.developer);
                jQuery('#developer_application').val(reportMasterData.developer_application);
                jQuery('#developing_time').val(reportMasterData.developing_time);
                jQuery('#lighting').val(reportMasterData.lighting);
                jQuery('#light_intensity').val(reportMasterData.light_intensity);
                jQuery('#background_light').val(reportMasterData.background_light);
                jQuery('#uva_light_intensity').val(reportMasterData.uva_light_intensity);

                jQuery('#sp_note').val(reportMasterData.sp_note);
                jQuery('#note').val(reportMasterData.note || '');
                jQuery('#pend_qty').val(res.pend_qty);
                jQuery('#total_qty').val(reportMasterData.total_qty);

                jQuery('#material_inward_details_id').val(reportMasterData.material_inward_details_id);
                jQuery('#observation_sheet_details_id').val(reportMasterData.observation_sheet_details_id || '');
                jQuery('#from_type_id_fix').val(reportMasterData.from_type_id_fix || (reportMasterData.observation_sheet_details_id ? 2 : 1));

                // Set radios
                setRadioReadonly('input[name="nabl_type_fix"]', false);
                setRadioReadonly('input[name="job_type_fix"]', false);
                jQuery(`input[name="nabl_type_fix"][value="${reportMasterData.nabl_type_fix}"]`).prop('checked', true);
                jQuery(`input[name="job_type_fix"][value="${reportMasterData.job_type_fix}"]`).prop('checked', true);
                setRadioReadonly('input[name="nabl_type_fix"]', true);
                setRadioReadonly('input[name="job_type_fix"]', true);
                handleNablTypeChange();

                if (reportMasterData.nabl_type_fix === 'NABL') {
                    dbUlrSequence = reportMasterData.ulr_sequence;
                    dbUlrNo = reportMasterData.ulr_no;
                    dbUlrId = reportMasterData.ulr_id;
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

                // Autocompletes / Select2 population
                jQuery('#type_of_job_id').val(zeroToEmpty(reportMasterData.type_of_job_id)).trigger('change');
                jQuery('#job_desc_id').val(zeroToEmpty(reportMasterData.job_desc_id)).trigger('change');
                jQuery('#material_id').val(zeroToEmpty(reportMasterData.material_id)).trigger('change');
                jQuery('#area_of_coverage_id').val(zeroToEmpty(reportMasterData.area_of_coverage_id)).trigger('change');
                jQuery('#procedure_ref_id').val(zeroToEmpty(reportMasterData.procedure_ref_id)).trigger('change');
                jQuery('#acceptance_standard_id').val(zeroToEmpty(reportMasterData.acceptance_standard_id)).trigger('change');
                jQuery('#tested_by_authority_person_id').val(zeroToEmpty(reportMasterData.tested_by_authority_person_id)).trigger('change');
                jQuery('#reviewed_by_authority_person_id').val(zeroToEmpty(reportMasterData.reviewed_by_authority_person_id)).trigger('change');
                jQuery('#authorized_by_authority_person_id').val(zeroToEmpty(reportMasterData.authorized_by_authority_person_id)).trigger('change');

                // Load customer dropdown list
                fillCustomerDropdown();

                // Image setting
                if (reportMasterData.defectogram_image) {
                    let fullPath = reportMasterData.defectogram_image;
                    let fileName = fullPath.split('/').pop();
                    jQuery('#defectogram_image_doc').val(fullPath);
                    jQuery('#defectogram_image_remove').removeClass('hide').addClass('i-block');
                    jQuery('#defectogram_image_prev').removeClass('hide').attr('href', 'storage/' + fullPath);
                    let fileInput = jQuery('#defectogram_image');
                    let newFile = new DataTransfer();
                    newFile.items.add(new File([""], fileName));
                    fileInput[0].files = newFile.files;
                } else {
                    jQuery('#defectogram_image_doc').val('');
                    jQuery('#defectogram_image').val('');
                    jQuery('#defectogram_image_remove').addClass('hide').removeClass('i-block');
                    jQuery('#defectogram_image_prev').addClass('hide').attr('href', '#');
                }

                // Populate arrays
                test_report_dpt_chemical_details_data = res.chemical_details_data || [];
                test_report_dpt_chemical_details_data.forEach(c => c.mode = '');
                resequenceChemicalDetails();
                fillChemicalTable();

                test_report_dpt_details_data = (res.report_details_data || []).map(d => {
                    d.detail_result_id = d.result_id;
                    d.mode = '';
                    return d;
                });
                resequenceReportDetails();
                fillReportDetailTable();

                jQuery('#TestReportDptModal').modal('show');
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

// Edit Mode Load Trigger
jQuery(document).on('click', '.edit-test_report_dpt', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    fetchAndFillTestReportDpt(data['id']);
});

jQuery('#TestReportDptModal').on('hidden.bs.modal', function () {
    _openForEdit = false;
    dbUlrSequence = null;
    dbUlrNo = null;
    dbUlrId = null;
    dbUlrYear = null;
    reportMasterData = null;
    selectedInwardDetailsId = null;
    jQuery('#defectogram_image_doc').val('');
    jQuery('#defectogram_image').val('');
    jQuery('#defectogram_image_remove').addClass('hide').removeClass('i-block');
    jQuery('#defectogram_image_prev').addClass('hide').attr('href', '#');
});

// Detail modal hidden focus redirection
jQuery('#DPTReportDetailsModal').on('hidden.bs.modal', function () {
    setTimeout(function () {
        jQuery('#tested_by_authority_person_id').focus();
    }, 200);
});

jQuery('#DPTChemicalDetailsModal').on('hidden.bs.modal', function () {
    jQuery('#addDetailRowBtn').focus();
});

// Form Submission (Add/Edit)
jQuery(document).on('click', '#submitbtn', function (e) {
    e.preventDefault();
    let form = jQuery('#commonTestReportDptForm');

    let inwardDetailsId = jQuery('#material_inward_details_id').val();
    if (!inwardDetailsId) {
        toastr.error('Select Pending Inward Data.');
        return;
    }

    if (form[0].checkValidity() === false) {
        form.addClass('was-validated');
        return;
    }

    let d1 = parseDateStr(jQuery('#date_of_testing').val());
    if (d1) {
        let activeChems = test_report_dpt_chemical_details_data.filter(c => c.mode !== 'Delete');
        for (let i = 0; i < activeChems.length; i++) {
            let c = activeChems[i];
            let d2 = parseDateStr(c.expiry_date);
            if (d2 && d1 > d2) {
                toastr.error('This DPT Chemical is over to its Expiry Date.');
                return;
            }
        }
    }

    if (test_report_dpt_chemical_details_data.filter(c => c.mode !== 'Delete').length === 0) {
        toastr.error('Please add at least one chemical detail row.');
        return;
    }

    let activeDetails = test_report_dpt_details_data.filter(d => d.mode !== 'Delete');
    if (activeDetails.length === 0) {
        toastr.error('Please add at least one test details row.');
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
        if (!row.dpt_test_no || row.dpt_test_no.trim() === '') {
            toastr.error(`Please Enter DPT Test No.`);
            return;
        }
        if (!row.quantity || parseInt(row.quantity) < 1) {
            toastr.error('Enter Quantity greater than 0.');
            return;
        }
        if (!row.discontinuity_evaluation || row.discontinuity_evaluation.trim() === '') {
            toastr.error(`Please Enter Discontinuity Evaluation`);
            return;
        }
        if (!row.detail_result_id || String(row.detail_result_id).trim() === '') {
            toastr.error(`Please Enter Result`);
            return;
        }
    }

    let url = jQuery('#id').val() ? 'update-test_report_dpt' : 'store-test_report_dpt';
    let formData = new FormData(form[0]);

    formData.append('chemical_details', JSON.stringify(test_report_dpt_chemical_details_data));
    formData.append('report_details_data', JSON.stringify(test_report_dpt_details_data));

    jQuery('#submitbtn').prop('disabled', true);
    skipLoader = false;
    showLoader();

    jQuery.ajax({
        url: url,
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
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
                        const form = document.getElementById("commonTestReportDptForm");
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
                        resetDptForm();
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
            if (jqXHR.status == 422) {
                toastr.error('Validation errors occurred.');
            } else {
                toastr.error('Something went wrong!');
            }
        },
        complete: function () {
            hideLoader();
        }
    });
});

// Duplication verification on report sequence
jQuery('#test_report_sequence').on('blur', function () {
    let seq = jQuery(this).val();
    if (!seq) return;

    if (seq > 0 == false) {
        toastr.error('Please Enter Valid Sr. No.');
        jQuery('#test_report_sequence').val('');
        jQuery('#test_report_sequence').focus();
        return;
    }

    jQuery.ajax({
        url: 'check-test_report_dpt_number_duplication',
        type: 'GET',
        data: { test_report_sequence: seq, id: jQuery('#id').val() },
        dataType: 'json',
        success: function (res) {
            if (res.response_code == 0) {
                toastr.error(res.response_message);
                jQuery('#test_report_sequence').val('');
                jQuery('#test_report_sequence').focus();
            } else {
                jQuery('#test_report_no').val(res.latest_no);
                jQuery('#test_report_sequence').val(seq);
            }
        },
        error: function () {
            toastr.error('Something went wrong!');
        }
    });
});

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

// Autocomplete suggestions
function suggestCustomerClient(event, element) {
    bindSuggestionAutocomplete(element, 'test_report_dpt_customer_client-list');
}
function suggestPartNo(event, element) {
    bindSuggestionAutocomplete(element, 'test_report_dpt_part_no-list');
}
function suggestDrgNo(event, element) {
    bindSuggestionAutocomplete(element, 'test_report_dpt_drg_no-list');
}
function suggestSurfaceCondition(event, element) {
    bindSuggestionAutocomplete(element, 'test_report_dpt_surface_condition-list');
}
function suggestTestTechnique(event, element) {
    bindSuggestionAutocomplete(element, 'test_report_dpt_test_technique-list');
}
function suggestTestCarriedOutAt(event, element) {
    bindSuggestionAutocomplete(element, 'test_report_dpt_test_carried_out_at-list');
}
function suggestSurfaceTemp(event, element) {
    bindSuggestionAutocomplete(element, 'test_report_dpt_surface_temp-list');
}
function suggestThickness(event, element) {
    bindSuggestionAutocomplete(element, 'test_report_dpt_thickness-list');
}
function suggestPreCleaning(event, element) {
    bindSuggestionAutocomplete(element, 'test_report_dpt_pre_cleaning-list');
}
function suggestPenetrantApplication(event, element) {
    bindSuggestionAutocomplete(element, 'test_report_dpt_penetrant_application-list');
}
function suggestDwellTime(event, element) {
    bindSuggestionAutocomplete(element, 'test_report_dpt_dwell_time-list');
}
function suggestPenetrantRemover(event, element) {
    bindSuggestionAutocomplete(element, 'test_report_dpt_penetrant_remover-list');
}
function suggestDeveloper(event, element) {
    bindSuggestionAutocomplete(element, 'test_report_dpt_developer-list');
}
function suggestDeveloperApplication(event, element) {
    bindSuggestionAutocomplete(element, 'test_report_dpt_developer_application-list');
}
function suggestLighting(event, element) {
    bindSuggestionAutocomplete(element, 'test_report_dpt_lighting-list');
}
function suggestBackgroundLight(event, element) {
    bindSuggestionAutocomplete(element, 'test_report_dpt_background_light-list');
}
function suggestProductCode(event, element) {
    bindSuggestionAutocomplete(element, 'test_report_dpt_product_code-list');
}

function bindSuggestionAutocomplete(element, url) {
    let parentId = jQuery(element).attr('id');
    let camelKey = parentId.replace(/_([a-z])/g, function (g) { return g[1].toUpperCase(); });
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#' + parentId + '_list',
        url: url,
        responseListKey: camelKey + 'List'
    });
}

// Handle suggestion item selection
jQuery(document).on('click', '.list-group-item', function () {
    let parentId = jQuery(this).attr('parent-id');
    let text = jQuery(this).text().trim();
    jQuery('#' + parentId).val(text);
    jQuery('#' + parentId + '_list').hide();
});

jQuery(document).on('click', function (e) {
    if (!jQuery(e.target).closest('.list-group').length && !jQuery(e.target).is('input')) {
        jQuery('.list-group').hide();
    }
});

// Copy Report Functionality
jQuery(document).on('click', '#copy_report_btn', function () {
    let typeOfJobId = jQuery('#type_of_job_id').val();

    jQuery.ajax({
        url: 'get-test_report_dpt_copy_list',
        type: 'GET',
        data: { type_of_job_id: typeOfJobId },
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                copyReportsData = data.reports;
                fillCopyModalTable();
            }
        }
    });
});

function fillCopyModalTable() {
    selectedCopyReportDptId = selectedCopyReportDptId || null;
    let $table = jQuery("#CopyReportDptModal").find('#CopyReportDptTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().clear().destroy();
    }

    let tbody = jQuery('#CopyReportDptTable tbody');
    tbody.empty();

    if (copyReportsData.length === 0) {
        tbody.append(`<tr><td colspan="12" class="text-center">No Reports Found</td></tr>`);
        jQuery('#CopyReportDptModal').modal('show');
        return;
    }

    copyReportsData.forEach(item => {
        let isChecked = (selectedCopyReportDptId && selectedCopyReportDptId == item.id) ? 'checked' : '';
        let rowHtml = `<tr>
            <td><input type="radio" name="copy_report_id" value="${item.id}" class="form-check-input select-copy-dpt-radio" ${isChecked}></td>
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
        if (selectedCopyReportDptId) {
            jQuery(`input[name="copy_report_id"][value="${selectedCopyReportDptId}"]`).prop('checked', true);
        }
    });
    if (typeof fixDataTableColumnsUntilAdjusted === 'function') {
        fixDataTableColumnsUntilAdjusted($new);
    }

    jQuery('#CopyReportDptModal').modal('show');
}

jQuery('#CopyReportDptModal').on('shown.bs.modal', function () {
    let $table = jQuery('#CopyReportDptTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().columns.adjust().draw();
        if (typeof initColumnSearch === 'function') {
            initColumnSearch('#CopyReportDptTable', [0], 'common_search');
        }
    }
});

jQuery(document).on('change', 'input[name="copy_report_id"]', function () {
    selectedCopyReportDptId = jQuery(this).val();
});

jQuery(document).on('click', '#submitCopyReportDptBtn', function () {
    // let checkedRadio = jQuery('.select-copy-dpt-radio:checked');
    var reportId = selectedCopyReportDptId || jQuery('input[name="copy_report_id"]:checked').val();
    if (!reportId) {
        // if (checkedRadio.length === 0) {
        toastr.error('Please select Report.');
        return;
    }

    // let reportId = checkedRadio.val();
    jQuery('#CopyReportDptModal').modal('hide');

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

            performCopyDptReport(reportId, copyMaster, copyDetails);
        });
    });
});

function performCopyDptReport(reportId, copyMaster, copyDetails) {
    if (typeof showLoader === 'function') showLoader();
    jQuery.ajax({
        url: 'get-test_report_dpt_details_for_copy',
        type: 'GET',
        data: { id: reportId },
        dataType: 'json',
        success: function (res) {
            if (res.response_code == 1 && res.report_data != null) {
                let copyData = res.report_data;

                if (copyMaster) {
                    /*
                    setRadioReadonly('input[name="nabl_type_fix"]', false);
                    setRadioReadonly('input[name="job_type_fix"]', false);
                    if (copyData.nabl_type_fix) {
                        jQuery(`input[name="nabl_type_fix"][value="${copyData.nabl_type_fix}"]`).prop('checked', true).trigger('change');
                    }
                    if (copyData.job_type_fix) {
                        jQuery(`input[name="job_type_fix"][value="${copyData.job_type_fix}"]`).prop('checked', true);
                    }
                    setRadioReadonly('input[name="nabl_type_fix"]', true);
                    setRadioReadonly('input[name="job_type_fix"]', true);

                    jQuery('#customer_client').val(copyData.customer_client || '');
                    jQuery('#part_no').val(copyData.part_no || '');
                    jQuery('#drg_no').val(copyData.drg_no || '');
                    jQuery('#product_code').val(copyData.product_code || '');
                    jQuery('#test_carried_out_at').val(copyData.test_carried_out_at || '');
                    jQuery('#stage_of_test').val(copyData.stage_of_test || '');
                    */

                    jQuery('#surface_condition').val(copyData.surface_condition || '');
                    jQuery('#surface_temp').val(copyData.surface_temp || '');
                    // jQuery('#thickness').val(copyData.thickness || '');
                    jQuery('#test_technique').val(copyData.test_technique || '');
                    jQuery('#pre_cleaning').val(copyData.pre_cleaning || '');
                    jQuery('#penetrant_application').val(copyData.penetrant_application || '');
                    jQuery('#dwell_time').val(copyData.dwell_time || '');
                    jQuery('#penetrant_remover').val(copyData.penetrant_remover || '');
                    jQuery('#developer').val(copyData.developer || '');
                    jQuery('#developer_application').val(copyData.developer_application || '');
                    jQuery('#developing_time').val(copyData.developing_time || '');
                    jQuery('#lighting').val(copyData.lighting || '');
                    jQuery('#light_intensity').val(copyData.light_intensity || '');
                    jQuery('#background_light').val(copyData.background_light || '');
                    jQuery('#uva_light_intensity').val(copyData.uva_light_intensity || '');

                    // jQuery('#sp_note').val(copyData.sp_note || '');
                    // jQuery('#note').val(copyData.note || '');

                    /*
                    if (copyData.material_id) {
                        jQuery('#material_id').val(copyData.material_id).trigger('change');
                    }
                    if (copyData.area_of_coverage_id) {
                        jQuery('#area_of_coverage_id').val(copyData.area_of_coverage_id).trigger('change');
                    }
                    */
                    if (copyData.procedure_ref_id) {
                        // jQuery('#procedure_ref_id').val(copyData.procedure_ref_id).trigger('change');
                    }
                    if (copyData.acceptance_standard_id) {
                        // jQuery('#acceptance_standard_id').val(copyData.acceptance_standard_id).trigger('change');
                    }
                    if (copyData.reviewed_by_authority_person_id) {
                        // jQuery('#reviewed_by_authority_person_id').val(copyData.reviewed_by_authority_person_id).trigger('change');
                    }
                    if (copyData.authorized_by_authority_person_id) {
                        // jQuery('#authorized_by_authority_person_id').val(copyData.authorized_by_authority_person_id).trigger('change');
                    }
                }

                if (copyDetails) {
                    // Copy chemical details (Keep existing details intact as requested)
                    /*
                    test_report_dpt_chemical_details_data = [];
                    if (res.chemical_details_data && res.chemical_details_data.length > 0) {
                        res.chemical_details_data.forEach(c => {
                            test_report_dpt_chemical_details_data.push({
                                test_report_dpt_chemical_details_id: '',
                                dpt_id: c.dpt_id,
                                dpt_chemical: c.dpt_chemical,
                                chemical_designation: c.chemical_designation,
                                make: c.make,
                                batch_no: c.batch_no,
                                expiry_date: c.expiry_date,
                                mode: 'Insert'
                            });
                        });
                    }
                    resequenceChemicalDetails();
                    fillChemicalTable();
                    */

                    /*
                    // Copy report details
                    test_report_dpt_details_data = [];
                    let nablType = jQuery('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
                    if (res.report_details_data && res.report_details_data.length > 0) {
                        var detailsToCopy = res.report_details_data;
                        if (nablType === 'NABL') {
                            detailsToCopy = [res.report_details_data[res.report_details_data.length - 1]];
                        }
                        detailsToCopy.forEach((d, idx) => {
                            test_report_dpt_details_data.push({
                                test_report_dpt_details_id: '',
                                sr_no: nablType === 'NABL' ? 1 : parseFloat(d.sr_no || (idx + 1)),
                                dpt_test_no: d.dpt_test_no,
                                heat_no: d.heat_no,
                                quantity: nablType === 'NABL' ? 1 : parseInt(d.quantity || 0),
                                discontinuity_evaluation: d.discontinuity_evaluation,
                                detail_result_id: d.result_id,
                                result_name: d.result_name,
                                mode: 'Insert'
                            });
                        });
                    }
                    resequenceReportDetails();
                    fillReportDetailTable();
                    */
                }

                setTimeout(function () {
                    jQuery('#type_of_job_id').focus();
                }, 100);
            }
        },
        complete: function () {
            hideLoader();
        }
    });
}

jQuery('#resetbtn').on('click', function (e) {
    e.preventDefault();
    let formId = jQuery('#id').val();
    if (!formId) {
        resetDptForm();
    } else {
        fetchAndFillTestReportDpt(formId);
    }
    setTimeout(function () {
        jQuery('#test_report_sequence').focus();
    }, 150);
});

jQuery(document).on('click', '#add_new', function () {
    resetDptForm();
    jQuery('#add_new').hide();
    setTimeout(function () {
        jQuery('#test_report_sequence').focus();
    }, 150);
});

function getTRLNRData() {
    jQuery.ajax({
        url: "get-test_report_dpt_lnr_data",
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.lnr_data != null) {
                var lnr = data.lnr_data;
                jQuery('#note').val(lnr.note ?? "");
            }
        },
        error: function () {
            console.log('Error fetching Test Report DPT LNR data');
        }
    });
}
