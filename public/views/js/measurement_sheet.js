var allReportsData = []; // Stores details for measurement sheet grid
var availablePendingReports = []; // Stores pending RT reports for selected customer
var _isEditMode = false;
var currentEditId = null;

jQuery(document).ready(function () {
    // Initialize custom date pickers
    jQuery('.pending-date-picker').datepicker({
        dateFormat: "dd/mm/yy",
        onClose: function () {
            this.focus();
        }
    });

    // Initialize Select2 for Customer & Prepared By
    jQuery('#customer_id').select2({
        dropdownParent: jQuery('#MeasurementSheetModal')
    });
    jQuery('#prepared_by_user_id').select2({
        dropdownParent: jQuery('#MeasurementSheetModal')
    });

    // Customer list will be loaded dynamically when modal opens for Add or Edit

    // Event: Customer Change
    jQuery('#customer_id').on('change', function () {
        let custId = jQuery(this).val();
        if (custId) {
            if (_isEditMode) {
                jQuery('#load_pending_reports').prop('disabled', true);
            } else {
                jQuery('#load_pending_reports').prop('disabled', false);
            }
            fetchCustomerPendingReports(custId);
        } else {
            jQuery('#load_pending_reports').prop('disabled', true);
            availablePendingReports = [];
        }
        if (!_isEditMode) {
            clearDetailsGrid();
        }
    });

    // Event: Load Pending Reports button click (Opens Modal Popup, clears date filters to show ALL records)
    jQuery('#load_pending_reports').on('click', function () {
        let startVal = jQuery('#pending_start_date').val();
        let endVal = jQuery('#pending_end_date').val();

        jQuery('#pending_start_date').val('');
        jQuery('#pending_end_date').val('');

        let custId = jQuery('#customer_id').val();
        if (custId) {
            if (startVal !== '' || endVal !== '') {
                // If filters were previously filled, fetch unfiltered list
                fetchCustomerPendingReports(custId).done(function () {
                    renderPendingModalTable();
                    jQuery('#PendingRtForMeasurementModal').modal('show');
                });
            } else {
                // Otherwise use already loaded list directly without extra API call
                renderPendingModalTable();
                jQuery('#PendingRtForMeasurementModal').modal('show');
            }
        }
    });

    // Event: Select All Checkbox in Pending Modal
    jQuery(document).on('change', '#select_all_pending_reports', function () {
        let isChecked = jQuery(this).is(':checked');
        let $table = jQuery('#pending_rt_reports_table');
        if (jQuery.fn.DataTable.isDataTable($table)) {
            let dt = $table.DataTable();
            jQuery(dt.rows().nodes()).find('.pending-report-checkbox').prop('checked', isChecked);
        } else {
            jQuery('.pending-report-checkbox').prop('checked', isChecked);
        }
    });

    // Event: Individual Checkbox Change in Pending Modal (Sync select all state across all pages)
    jQuery(document).on('change', '.pending-report-checkbox', function () {
        let $table = jQuery('#pending_rt_reports_table');
        let dt = jQuery.fn.DataTable.isDataTable($table) ? $table.DataTable() : null;
        let allNodes = dt ? jQuery(dt.rows().nodes()) : jQuery('#pending_rt_reports_table tbody');
        let totalBoxes = allNodes.find('.pending-report-checkbox').length;
        let checkedBoxes = allNodes.find('.pending-report-checkbox:checked').length;
        jQuery('#select_all_pending_reports').prop('checked', totalBoxes > 0 && totalBoxes === checkedBoxes);
    });

    // Event: Submit Selected Pending Reports from Popup
    jQuery('#submitPendingReportsBtn').on('click', function () {
        let $table = jQuery('#pending_rt_reports_table');
        let dt = jQuery.fn.DataTable.isDataTable($table) ? $table.DataTable() : null;
        let allNodes = dt ? jQuery(dt.rows().nodes()) : jQuery('#pending_rt_reports_table tbody');

        let checkedCount = allNodes.find('.pending-report-checkbox:checked').length;
        if (checkedCount === 0) {
            toastr.error('Please Select At Least One Pending Report');
            return;
        }

        availablePendingReports.forEach(p => {
            let isChecked = allNodes.find(`.pending-report-checkbox[data-id="${p.test_report_rt_id}"]`).is(':checked');
            let existingIndex = allReportsData.findIndex(r => r.test_report_rt_id === p.test_report_rt_id);

            if (isChecked) {
                if (existingIndex > -1) {
                    if (allReportsData[existingIndex].mode === 'Exclude' || allReportsData[existingIndex].mode === 'Delete') {
                        allReportsData[existingIndex].mode = allReportsData[existingIndex].measurement_sheet_details_id ? 'Update' : 'New';
                    }
                } else {
                    allReportsData.push({
                        test_report_rt_id: p.test_report_rt_id,
                        test_report_no: p.test_report_no,
                        revision_number: p.revision_number,
                        test_report_date: p.test_report_date,
                        nabl_type_fix: p.nabl_type_fix,
                        job_type_fix: p.job_type_fix,
                        rt_no: p.rt_no,
                        heat_no: p.heat_no,
                        material: p.material,
                        type_of_job: p.type_of_job,
                        job_description: p.job_description,
                        part_no: p.part_no,
                        drg_no: p.drg_no,
                        product_code: p.product_code,
                        customer_dc_no: p.customer_dc_no || p.dc_no || '',
                        material_inward_details_id: p.material_inward_details_id,
                        ir_192_sqin: parseFloat(p.mms_ir_192_sqin || 0),
                        co_60_sqin: parseFloat(p.mms_co_60_sqin || 0),
                        x_ray_sqin: parseFloat(p.mms_x_ray_sqin || 0),
                        ir_192_repair_sqin: parseFloat(p.mms_ir_192_repair_sqin || 0),
                        co_60_repair_sqin: parseFloat(p.mms_co_60_repair_sqin || 0),
                        x_ray_repair_sqin: parseFloat(p.mms_x_ray_repair_sqin || 0),
                        remark: '',
                        mode: 'New'
                    });
                }
            } else {
                if (existingIndex > -1) {
                    if (allReportsData[existingIndex].measurement_sheet_details_id) {
                        allReportsData[existingIndex].mode = 'Delete';
                    } else {
                        allReportsData[existingIndex].mode = 'Exclude';
                    }
                }
            }
        });

        renderDetailsTable();
        calculateGrandTotals();
        jQuery('#customer_id').addClass('skip-tab');
        setSelect2Readonly('#customer_id', true);
        jQuery('#PendingRtForMeasurementModal').modal('hide');
        setTimeout(() => {
            jQuery('#sp_note').focus().select();
        }, 150);
    });

    // Event: Direct Auto-Filter Pending Reports on Date Change/Input (Shows ALL records when dates are blank)
    jQuery(document).on('change', '.pending-date-filter', function () {
        let custId = jQuery('#customer_id').val();
        let startDate = jQuery('#pending_start_date').val().trim();
        let endDate = jQuery('#pending_end_date').val().trim();
        if (custId) {
            fetchCustomerPendingReports(custId, startDate, endDate).done(function () {
                renderPendingModalTable();
            });
        }
    });

    // Event: Reset Pending Filter Button
    jQuery('#btn_reset_pending_filter').on('click', function () {
        jQuery('#pending_start_date').val('');
        jQuery('#pending_end_date').val('');

        let custId = jQuery('#customer_id').val();
        if (custId) {
            fetchCustomerPendingReports(custId, '', '').done(function () {
                renderPendingModalTable();
            });
        }
    });

    // Event: Remove Row from Grid (Standard toastDetailDelete Confirmation)
    jQuery(document).on('click', '.remove-detail-row', function () {
        let index = jQuery(this).data('index');
        let row = allReportsData[index];
        if (row) {
            let reportNo = row.test_report_no ? row.test_report_no : 'this item';
            toastDetailDelete(`Are you sure you want to delete?`, function () {
                if (row.measurement_sheet_details_id) {
                    row.mode = 'Delete';
                } else {
                    row.mode = 'Exclude';
                }
                renderDetailsTable();
                calculateGrandTotals();
            });
        }
    });

    // Event: Restore Row (Undo Delete)
    jQuery(document).on('click', '.restore-detail-row', function () {
        let index = jQuery(this).data('index');
        let row = allReportsData[index];
        if (row) {
            if (row.measurement_sheet_details_id) {
                row.mode = 'Update';
            } else {
                row.mode = 'New';
            }
            renderDetailsTable();
            calculateGrandTotals();
        }
    });

    // Event: Edit values in row to update sums
    jQuery(document).on('input', '.grid-input', function () {
        let tr = jQuery(this).closest('tr');
        let index = tr.data('index');
        let field = jQuery(this).data('field');
        let val = parseFloat(jQuery(this).val()) || 0;

        if (allReportsData[index]) {
            allReportsData[index][field] = val;
            calculateGrandTotals();
        }
    });

    // Form Submit (Handles both Submit in ADD mode and Update in EDIT mode)
    jQuery('#commonMeasurementSheetForm').on('submit', function (e) {
        e.preventDefault();
        saveMeasurementSheet();
    });

    // Reset Button Handler
    jQuery('#resetbtn').on('click', function () {
        if (_isEditMode && currentEditId) {
            // EDIT Mode: Reload original edit data
            loadEditData(currentEditId);
        } else {
            // ADD Mode: Reset form completely
            resetForm();
        }
    });

    // Add New Button Handler (In EDIT Mode)
    jQuery('#add_new').on('click', function () {
        _isEditMode = false;
        currentEditId = null;
        setModeUI(false);
        resetForm();
        loadCustomersList();
    });

    // Open Modal for Add
    jQuery('#MeasurementSheetModal').on('show.bs.modal', function (event) {
        if (!_isEditMode) {
            // Triggered via Add Button or in Add Mode
            _isEditMode = false;
            currentEditId = null;
            setModeUI(false);
            resetForm();
            loadCustomersList();
        }
        setTimeout(function () {
            jQuery('#measurement_sheet_sequence').focus().select();
        }, 50);
    });

    // jQuery('#MeasurementSheetModal').on('shown.bs.modal', function () {
    //     setTimeout(function () {
    //         jQuery('#measurement_sheet_sequence').focus().select();
    //     }, 50);
    // });

    // Reset Main Modal Form on Hide
    jQuery('#MeasurementSheetModal').on('hidden.bs.modal', function () {
        _isEditMode = false;
        currentEditId = null;
        setModeUI(false);
        resetForm();
    });

    // Edit Button Action on Datatable
    jQuery(document).on('click', '.edit-sheet', function () {
        let id = jQuery(this).data('id');
        currentEditId = id;
        _isEditMode = true;
        setModeUI(true);
        loadEditData(id);
    });

});

function setModeUI(isEdit) {
    if (isEdit) {
        jQuery('#submitbtn').hide();
        jQuery('#updatebtn').show();
        jQuery('#preview_btn').hide();
        jQuery('#add_new').show();
    } else {
        jQuery('#submitbtn').show();
        jQuery('#updatebtn').hide();
        jQuery('#preview_btn').hide();
        jQuery('#add_new').hide();
    }
}

function saveMeasurementSheet() {
    let form = jQuery('#commonMeasurementSheetForm')[0];
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    // Gather selected detail rows
    let detailsToSend = allReportsData.filter(r => r.mode === 'New' || r.mode === 'Update' || r.mode === 'Delete');
    if (detailsToSend.filter(r => r.mode !== 'Delete').length === 0) {
        toastr.error("Please Select At Least One RT Report From Pending");
        return;
    }

    let btn = _isEditMode ? jQuery('#updatebtn') : jQuery('#submitbtn');
    let origBtnText = btn.html();

    // Disable button to prevent double submission
    btn.prop('disabled', true);
    skipLoader = false;
    if (typeof showLoader === 'function') showLoader();

    let formData = {
        id: jQuery('#id').val(),
        measurement_sheet_no: jQuery('#measurement_sheet_no').val(),
        measurement_sheet_sequence: jQuery('#measurement_sheet_sequence').val(),
        measurement_sheet_date: jQuery('#measurement_sheet_date').val(),
        customer_id: jQuery('#customer_id').val(),
        total_ir_192_sqin: jQuery('#total_ir_192_sqin').val(),
        total_co_60_sqin: jQuery('#total_co_60_sqin').val(),
        total_x_ray_sqin: jQuery('#total_x_ray_sqin').val(),
        total_ir_192_repair_sqin: jQuery('#total_ir_192_repair_sqin').val(),
        total_co_60_repair_sqin: jQuery('#total_co_60_repair_sqin').val(),
        total_x_ray_repair_sqin: jQuery('#total_x_ray_repair_sqin').val(),
        sp_note: jQuery('#sp_note').val(),
        prepared_by_user_id: jQuery('#prepared_by_user_id').val(),
        details: JSON.stringify(detailsToSend)
    };

    let isUpdate = formData.id != "";
    let url = isUpdate ? 'update-measurement_sheet' : 'store-measurement_sheet';

    jQuery.ajax({
        url: url,
        type: 'POST',
        headers: headerOpt,
        data: formData,
        success: function (res) {
            btn.prop('disabled', false);
            if (res.response_code == 1) {
                let nextFn = function () {
                    if (isUpdate) {
                        jQuery('#MeasurementSheetModal').modal('hide');
                    } else {
                        resetForm();
                        loadCustomersList();
                    }
                    table.ajax.reload();
                };
                if (res.url && res.url !== "") {
                    toastSuccessPreview(res.response_message || (isUpdate ? "Record Updated." : "Record Inserted."), res.url, nextFn);
                } else {
                    toastSuccess(res.response_message || (isUpdate ? "Record Updated." : "Record Inserted."), nextFn);
                }
            } else {
                toastr.error(res.response_message || "Error saving record");
            }
        },
        error: function (err) {
            btn.prop('disabled', false);
            toastr.error("Something went wrong");
        },
        complete: function () {
            if (typeof hideLoader === 'function') hideLoader();
        }
    });
}

function loadEditData(id) {
    resetForm(true);

    skipLoader = false;
    if (typeof showLoader === 'function') showLoader();

    jQuery.ajax({
        url: 'edit-measurement_sheet',
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        success: function (res) {
            if (res.response_code == 1) {
                let d = res.sheet_data;
                jQuery('#id').val(d.measurement_sheet_id);
                jQuery('#measurement_sheet_sequence').val(d.measurement_sheet_sequence);
                jQuery('#measurement_sheet_no').val(d.measurement_sheet_no);
                jQuery('#measurement_sheet_date').val(d.measurement_sheet_date);
                jQuery('#sp_note').val(d.sp_note);
                if (d.prepared_by_user_id) {
                    jQuery('#prepared_by_user_id').val(d.prepared_by_user_id).trigger('change.select2');
                }

                // Set customer dropdown and make read-only
                loadCustomersList(d.customer_id).done(function () {
                    jQuery('#customer_id').val(d.customer_id).trigger('change');
                    jQuery('#customer_id').addClass('skip-tab');
                    setSelect2Readonly('#customer_id', true);
                    jQuery('#load_pending_reports').prop('disabled', true);
                });

                // Prepare allReportsData with existing saved rows
                allReportsData = res.details_data.map(r => ({
                    measurement_sheet_details_id: r.measurement_sheet_details_id,
                    test_report_rt_id: r.test_report_rt_id,
                    test_report_no: r.test_report_no,
                    revision_number: r.revision_number,
                    test_report_date: r.test_report_date,
                    nabl_type_fix: r.nabl_type_fix,
                    job_type_fix: r.job_type_fix,
                    rt_no: r.rt_no,
                    heat_no: r.heat_no,
                    material: r.material,
                    type_of_job: r.type_of_job,
                    job_description: r.job_description,
                    part_no: r.part_no,
                    drg_no: r.drg_no,
                    product_code: r.product_code,
                    material_inward_details_id: r.material_inward_details_id,
                    ir_192_sqin: parseFloat(r.ir_192_sqin || 0),
                    co_60_sqin: parseFloat(r.co_60_sqin || 0),
                    x_ray_sqin: parseFloat(r.x_ray_sqin || 0),
                    ir_192_repair_sqin: parseFloat(r.ir_192_repair_sqin || 0),
                    co_60_repair_sqin: parseFloat(r.co_60_repair_sqin || 0),
                    x_ray_repair_sqin: parseFloat(r.x_ray_repair_sqin || 0),
                    remark: r.remark,
                    customer_dc_no: r.customer_dc_no,
                    mode: 'Update'
                }));

                renderDetailsTable();
                calculateGrandTotals();
                let url = checkFileRoute + "?id=" + d.measurement_sheet_id + "&name=" + d.pdf_name + "&type=measurement_sheet";
                jQuery('#preview_btn').attr('href', url).show();
                jQuery('#MeasurementSheetModal').modal('show');
            } else {
                toastr.error(res.response_message || "Record not found");
            }
        },
        error: function () {
            toastr.error("Something went wrong");
        },
        complete: function () {
            if (typeof hideLoader === 'function') hideLoader();
        }
    });
}

function loadCustomersList(selectedId = null) {
    let def = jQuery.Deferred();
    let custHtml = '<option value="">Select Customer</option>';
    let sheetId = jQuery('#id').val();

    jQuery.ajax({
        url: 'get-pending-customers-for-measurement',
        type: 'GET',
        data: { sheet_id: sheetId },
        headers: headerOpt,
        success: function (res) {
            if (res.customers) {
                res.customers.forEach(c => {
                    custHtml += `<option value="${c.id}">${c.customer}</option>`;
                });
                jQuery('#customer_id').empty().append(custHtml).trigger('change.select2');
                if (selectedId) {
                    jQuery('#customer_id').val(selectedId).trigger('change.select2');
                }
                def.resolve();
            }
        }
    });
    return def.promise();
}

function fetchCustomerPendingReports(customerId, startDate = null, endDate = null) {
    let def = jQuery.Deferred();
    if (!customerId) {
        availablePendingReports = [];
        def.resolve();
        return def.promise();
    }
    jQuery.ajax({
        url: 'get-pending-rt-reports-for-measurement',
        type: 'GET',
        data: {
            customer_id: customerId,
            start_date: startDate,
            end_date: endDate
        },
        headers: headerOpt,
        success: function (res) {
            if (res.response_code == 1) {
                availablePendingReports = res.reports || [];
            } else {
                availablePendingReports = [];
            }
            def.resolve();
        },
        error: function () {
            availablePendingReports = [];
            def.resolve();
        }
    });
    return def.promise();
}

function renderPendingModalTable() {
    let $table = jQuery('#PendingRtForMeasurementModal').find('#pending_rt_reports_table');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().clear().destroy();
        $table.find('thead tr.search-row').remove();
    }

    let tbody = jQuery('#pending_rt_reports_tbody');
    tbody.empty();
    jQuery('#select_all_pending_reports').prop('checked', false);

    if (availablePendingReports.length === 0) {
        tbody.append(`<tr><td colspan="19" class="text-center">No Pending RT Reports Found</td></tr>`);
        return;
    }

    availablePendingReports.forEach(p => {
        let isSelected = allReportsData.some(r => r.test_report_rt_id === p.test_report_rt_id && r.mode !== 'Exclude' && r.mode !== 'Delete');
        let rowHtml = `
            <tr>
                <td>
                    <input type="checkbox" class="form-check-input pending-report-checkbox" data-id="${p.test_report_rt_id}" ${isSelected ? 'checked' : ''}>
                </td>
                <td>${p.nabl_type_fix || ''}</td>
                <td>${p.job_type_fix || ''}</td>
                <td>${p.test_report_no || ''}</td>
                <td>${p.revision_number || ''}</td>
                <td>${p.test_report_date || ''}</td>
                <td>${p.rt_no || ''}</td>
                <td>${p.heat_no || ''}</td>
                <td>${p.material || ''}</td>
                <td>${p.type_of_job || ''}</td>
                <td>${p.job_description || ''}</td>
                <td>${p.part_no || ''}</td>
                <td>${p.drg_no || ''}</td>
                <td>${p.product_code || ''}</td>
                <td>${parseFloat(p.mms_ir_192_sqin || 0).toFixed(2)}</td>
                <td>${parseFloat(p.mms_co_60_sqin || 0).toFixed(2)}</td>
                <td>${parseFloat(p.mms_x_ray_sqin || 0).toFixed(2)}</td>
                <td>${parseFloat(p.mms_ir_192_repair_sqin || 0).toFixed(2)}</td>
                <td>${parseFloat(p.mms_co_60_repair_sqin || 0).toFixed(2)}</td>
                <td>${parseFloat(p.mms_x_ray_repair_sqin || 0).toFixed(2)}</td>
                <td>${p.customer_dc_no || p.dc_no || ''}</td>
            </tr>
        `;
        tbody.append(rowHtml);
    });

    var $dt = $table.DataTable({
        paging: true,
        searching: true,
        dom: 'rtip',
        fixedHeader: false,
        "sScrollX": true,
        "sScrollX": "100%",
        "sScrollXInner": "110%",
        "bScrollCollapse": true,
    });

    let allNodes = jQuery($dt.rows().nodes());
    let totalBoxes = allNodes.find('.pending-report-checkbox').length;
    let checkedBoxes = allNodes.find('.pending-report-checkbox:checked').length;
    jQuery('#select_all_pending_reports').prop('checked', totalBoxes > 0 && totalBoxes === checkedBoxes);

    if (typeof initColumnSearch === 'function') {
        initColumnSearch('#pending_rt_reports_table', [0], 'common_search');
    }

    if (typeof fixDataTableColumnsUntilAdjusted === 'function') {
        fixDataTableColumnsUntilAdjusted($dt);
    }
}

// Adjust DataTable columns and attach column search when pending modal is shown
jQuery('#PendingRtForMeasurementModal').on('shown.bs.modal', function () {
    const frDate = document.getElementById('pending_start_date');
    if (frDate) {
        frDate.focus();
        if (jQuery(frDate).hasClass('pending-date-picker')) {
            jQuery(frDate).datepicker('hide');
        }
    }
    let $table = jQuery('#pending_rt_reports_table');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        try {
            $table.DataTable().columns.adjust();
            if (typeof initColumnSearch === 'function') {
                initColumnSearch('#pending_rt_reports_table', [0], 'common_search');
            }
        } catch (err) { }
    }

});

function renderDetailsTable() {
    let tbody = jQuery('#details_tbody');
    tbody.empty();

    allReportsData.forEach((row, index) => {
        if (row.mode === 'Exclude' || row.mode === 'Delete') return;

        let rowHtml = `
            <tr data-index="${index}">
                <td class="text-center align-middle">
                    <div class="dropdown">
                        <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' data-bs-boundary="window" aria-expanded="false">
                            <i class="ri-more-fill align-middle"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item remove-item-btn remove-detail-row" href="javascript:void(0)" data-index="${index}">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-danger"></i> Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>
                <td>${row.nabl_type_fix || ''}</td>
                <td>${row.job_type_fix || ''}</td>
                <td>${row.test_report_no || ''}</td>
                <td>${row.revision_number || ''}</td>
                <td>${row.test_report_date || ''}</td>
                <td>${row.rt_no || ''}</td>
                <td>${row.heat_no || ''}</td>
                <td>${row.material || ''}</td>
                <td>${row.type_of_job || ''}</td>
                <td>${row.job_description || ''}</td>
                <td>${row.part_no || ''}</td>
                <td>${row.drg_no || ''}</td>
                <td>${row.product_code || ''}</td>
                <td>${parseFloat(row.ir_192_sqin || 0).toFixed(2)}</td>
                <td>${parseFloat(row.co_60_sqin || 0).toFixed(2)}</td>
                <td>${parseFloat(row.x_ray_sqin || 0).toFixed(2)}</td>
                <td>${parseFloat(row.ir_192_repair_sqin || 0).toFixed(2)}</td>
                <td>${parseFloat(row.co_60_repair_sqin || 0).toFixed(2)}</td>
                <td>${parseFloat(row.x_ray_repair_sqin || 0).toFixed(2)}</td>
                <td>${row.customer_dc_no || row.dc_no || ''}</td>
                <td>
                    <input type="text" class="form-control grid-text-input" style="width: 140px;" value="${row.remark || ''}" oninput="updateRemark(${index}, this.value)">
                </td>
            </tr>
        `;
        tbody.append(rowHtml);
    });
}

function updateRemark(index, val) {
    if (allReportsData[index]) {
        allReportsData[index].remark = val;
    }
}

function calculateGrandTotals() {
    let tot_ir = 0, tot_co = 0, tot_xray = 0;
    let tot_ir_rep = 0, tot_co_rep = 0, tot_xray_rep = 0;

    allReportsData.forEach(r => {
        if (r.mode !== 'Exclude' && r.mode !== 'Delete') {
            tot_ir += parseFloat(r.ir_192_sqin || 0);
            tot_co += parseFloat(r.co_60_sqin || 0);
            tot_xray += parseFloat(r.x_ray_sqin || 0);
            tot_ir_rep += parseFloat(r.ir_192_repair_sqin || 0);
            tot_co_rep += parseFloat(r.co_60_repair_sqin || 0);
            tot_xray_rep += parseFloat(r.x_ray_repair_sqin || 0);
        }
    });

    jQuery('#total_ir_192_sqin').val(tot_ir.toFixed(2));
    jQuery('#total_co_60_sqin').val(tot_co.toFixed(2));
    jQuery('#total_x_ray_sqin').val(tot_xray.toFixed(2));
    jQuery('#total_ir_192_repair_sqin').val(tot_ir_rep.toFixed(2));
    jQuery('#total_co_60_repair_sqin').val(tot_co_rep.toFixed(2));
    jQuery('#total_x_ray_repair_sqin').val(tot_xray_rep.toFixed(2));
}

function clearDetailsGrid() {
    allReportsData = [];
    jQuery('#details_tbody').empty();
    calculateGrandTotals();
}

function resetForm(isEdit = false) {
    let form = jQuery('#commonMeasurementSheetForm')[0];
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
    }
    jQuery('#id').val('');
    let today = new Date();
    let dd = String(today.getDate()).padStart(2, '0');
    let mm = String(today.getMonth() + 1).padStart(2, '0');
    let yyyy = today.getFullYear();
    jQuery('#measurement_sheet_date').val(`${dd}/${mm}/${yyyy}`);
    jQuery('#sp_note').val('');
    jQuery('#customer_id').removeClass('skip-tab');
    setSelect2Readonly('#customer_id', false);
    jQuery('#customer_id').val('').trigger('change.select2');

    // Reset prepared_by_user_id select2 to the default selected user option
    let defaultUser = jQuery('#prepared_by_user_id').find('option[selected]').val() || '';
    jQuery('#prepared_by_user_id').val(defaultUser).trigger('change.select2');

    jQuery('#load_pending_reports').prop('disabled', true);
    availablePendingReports = [];
    clearDetailsGrid();
    jQuery('#preview_btn').hide();

    if (!isEdit) {
        fetchNextSheetNo();
    } else {
        setTimeout(function () {
            jQuery('#measurement_sheet_sequence').focus().select();
        }, 50);
    }
}

function fetchNextSheetNo() {
    jQuery.ajax({
        url: 'get-latest-measurement_sheet-number',
        type: 'GET',
        headers: headerOpt,
        success: function (res) {
            if (res.response_code == 1) {
                jQuery('#measurement_sheet_sequence').val(res.number);
                jQuery('#measurement_sheet_no').val(res.latest_no);
                if (res.date) {
                    jQuery('#measurement_sheet_date').val(res.date);
                }
                setTimeout(function () {
                    jQuery('#measurement_sheet_sequence').focus().select();
                }, 50);
            }
        }
    });
}

function checkMeasurementSheetSequenceDuplication() {
    let seq = jQuery('#measurement_sheet_sequence').val();
    if (seq != "") {
        if (seq > 0 == false) {
            toastr.error('Please Enter Valid Sr. No.');
            jQuery('#measurement_sheet_sequence').val('');
            jQuery('#measurement_sheet_sequence').focus();
        } else {
            jQuery('#measurement_sheet_sequence').addClass('file-loader');
            let id = jQuery('#id').val();
            jQuery.ajax({
                url: 'check-measurement_sheet_number_duplication',
                type: 'GET',
                data: { measurement_sheet_sequence: seq, id: id },
                dataType: 'json',
                success: function (data) {
                    jQuery('#measurement_sheet_sequence').removeClass('file-loader');
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        jQuery('#measurement_sheet_sequence').val('');
                        jQuery('#measurement_sheet_sequence').focus();
                    } else {
                        jQuery('#measurement_sheet_no').val(data.latest_no);
                        jQuery('#measurement_sheet_sequence').val(seq);
                    }
                },
                error: function () {
                    jQuery('#measurement_sheet_sequence').removeClass('file-loader');
                    toastr.error('Something went wrong!');
                }
            });
        }
    }
}

jQuery('#commonMeasurementSheetForm').find('#measurement_sheet_sequence').on('change', function () {
    checkMeasurementSheetSequenceDuplication();
});

function parseDate(str) {
    if (!str) return null;
    let parts = str.split('/');
    if (parts.length === 3) {
        return new Date(parts[2], parts[1] - 1, parts[0]);
    }
    return null;
}

function setSelect2Readonly(selector, readonly) {
    jQuery(selector).prop('disabled', readonly).trigger('change.select2');
}
