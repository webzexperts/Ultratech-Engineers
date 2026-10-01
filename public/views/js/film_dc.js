var allDetailsData = [];
var availablePendingReports = [];
var selectedPendingReportIds = new Set();
var _isEditMode = false;
var currentEditId = null;

function getAjaxHeaders() {
    if (typeof headerOpt !== 'undefined') {
        return headerOpt;
    }
    return {
        'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content') || jQuery('input[name="_token"]').val()
    };
}

jQuery(document).ready(function () {
    // 1. Initialize Datepickers
    jQuery('.trans-date-picker').datepicker({
        dateFormat: "dd/mm/yy",
        onClose: function () {
            this.focus();
        }
    });

    jQuery('.pending-date-picker').datepicker({
        dateFormat: "dd/mm/yy",
        onClose: function () {
            this.focus();
        }
    });

    // 2. Initialize Select2
    jQuery('#customer_id').select2({
        dropdownParent: jQuery('#FilmDCModal')
    });

    jQuery('#detail_film_brand_id').select2({
        dropdownParent: jQuery('#FilmDCDetailModal')
    });

    jQuery('#detail_film_id').select2({
        dropdownParent: jQuery('#FilmDCDetailModal')
    });

    // 3. Trigger Modal Open for Add
    jQuery('#add_dc_btn').on('click', function () {
        resetToAddMode();
    });

    // 4. Job Type & Customer Change Handling
    jQuery('input[name="job_type_fix"]').on('change', function () {
        let custId = jQuery('#customer_id').val();
        loadPendingCustomers(custId, currentEditId);
        if (!_isEditMode) {
            allDetailsData = [];
            renderDetailsGrid();
        }
    });

    jQuery('input[name="film_size_unit_fix"]').on('change', function () {
        updateFilmSizeLabels();
        let custId = jQuery('#customer_id').val();
        loadPendingCustomers(custId, currentEditId);
        if (!_isEditMode) {
            allDetailsData = [];
            renderDetailsGrid();
        }
    });

    jQuery('input[name="from_type_id"]').on('change', function () {
        let type = jQuery(this).val();
        let currentCustId = jQuery('#customer_id').val();
        loadPendingCustomers(currentCustId, currentEditId);

        if (type === 'Manual') {
            jQuery('#addDetailRowBtn').prop('disabled', false);
            jQuery('#pending_btn').prop('disabled', true);
        } else {
            jQuery('#addDetailRowBtn').prop('disabled', true);
            let custId = jQuery('#customer_id').val();
            jQuery('#pending_btn').prop('disabled', !custId);
        }

        if (!_isEditMode) {
            allDetailsData = [];
            renderDetailsGrid();
        }
        updateFormLockState();
    });

    jQuery('#customer_id').on('change', function () {
        let custId = jQuery(this).val();
        let dcFromType = jQuery('input[name="from_type_id"]:checked').val();
        if (custId && dcFromType !== 'Manual') {
            jQuery('#pending_btn').prop('disabled', false);
        } else {
            jQuery('#pending_btn').prop('disabled', true);
        }
        if (!_isEditMode) {
            allDetailsData = [];
            renderDetailsGrid();
        }
    });

    // 5. Sequence Number Change & Submit Button Lock Rule
    jQuery(document).on('keyup change', '#film_dc_sequence', function () {
        let seq = jQuery(this).val();
        let id = jQuery('#id').val() || '';

        if (seq != '') {
            jQuery('#submitbtn, #updatebtn').prop('disabled', true);

            jQuery.ajax({
                url: 'check-film_dc_number_duplication',
                type: 'GET',
                data: { film_dc_sequence: seq, id: id },
                headers: getAjaxHeaders(),
                success: function (res) {
                    if (res.response_code == 1) {
                        jQuery('#film_dc_no').val(res.latest_no);
                        jQuery('#submitbtn, #updatebtn').prop('disabled', false);
                    } else {
                        toastr.error(res.response_message || 'Duplicate DC No. Found.');
                    }
                },
                error: function () {
                    jQuery('#submitbtn, #updatebtn').prop('disabled', false);
                }
            });
        }
    });

    // 6. Open Pending RT Modal
    jQuery('#pending_btn, #load_pending_reports').on('click', function () {
        let custId = jQuery('#customer_id').val();
        if (!custId) {
            toastr.error('Please Select Customer first.');
            return;
        }

        selectedPendingReportIds.clear();

        fetchAndRenderPendingReports();
        jQuery('#PendingRtForFilmDCModal').modal('show');
    });

    jQuery('#PendingRtForFilmDCModal').on('shown.bs.modal', function () {
        if (jQuery.fn.DataTable.isDataTable('#PendingFilmDcTable')) {
            var dt = jQuery('#PendingFilmDcTable').DataTable();
            if (typeof fixDataTableColumnsUntilAdjusted === 'function') {
                fixDataTableColumnsUntilAdjusted(dt);
            } else {
                dt.columns.adjust().draw();
            }
        }
    });

    // 8. Multi-Page Checkbox Handling in Pending Modal (DataTable Multi-Page Rule)
    jQuery(document).on('change', '.pending_rt_checkbox', function () {
        let id = jQuery(this).val();
        if (jQuery(this).is(':checked')) {
            selectedPendingReportIds.add(id);
        } else {
            selectedPendingReportIds.delete(id);
        }
        syncSelectAllState();
    });

    jQuery(document).on('change', '#checkall-pending_data, #select_all_pending_reports', function () {
        let isChecked = jQuery(this).is(':checked');
        availablePendingReports.forEach(p => {
            let id = String(p.test_report_rt_id);
            if (isChecked) {
                selectedPendingReportIds.add(id);
            } else {
                selectedPendingReportIds.delete(id);
            }
        });
        jQuery('.pending_rt_checkbox').prop('checked', isChecked);
    });

    // 9. Submit Selected Pending Reports from Popup
    jQuery('#submitPendingReportsBtn, #import_pending_btn').on('click', function () {
        if (selectedPendingReportIds.size === 0) {
            toastr.error('Please Select At Least One RT Report.');
            return;
        }

        let reportIds = Array.from(selectedPendingReportIds);
        let btn = jQuery(this);
        btn.prop('disabled', true);

        jQuery.ajax({
            url: 'get-rt-details-for-film_dc',
            type: 'POST',
            data: {
                report_ids: reportIds,
                _token: jQuery('meta[name="csrf-token"]').attr('content') || jQuery('input[name="_token"]').val()
            },
            headers: getAjaxHeaders(),
            success: function (res) {
                btn.prop('disabled', false).text('Submit');
                if (res.response_code == 1 && res.details) {
                    res.details.forEach(item => {
                        allDetailsData.push(item);
                    });

                    // Auto fill customer_client in textarea from selected reports
                    let clients = [];
                    availablePendingReports.forEach(r => {
                        if (selectedPendingReportIds.has(String(r.test_report_rt_id)) && r.customer_client && r.customer_client.trim()) {
                            if (!clients.includes(r.customer_client.trim())) {
                                clients.push(r.customer_client.trim());
                            }
                        }
                    });
                    if (clients.length === 0 && res.details) {
                        res.details.forEach(d => {
                            if (d.customer_client && d.customer_client.trim() && !clients.includes(d.customer_client.trim())) {
                                clients.push(d.customer_client.trim());
                            }
                        });
                    }
                    if (clients.length > 0) {
                        jQuery('#customer_client').val(clients.join('\n'));
                    }

                    renderDetailsGrid();
                    updateFormLockState();
                    jQuery('#PendingRtForFilmDCModal').modal('hide');
                    setTimeout(() => {
                        jQuery('#customer_client').focus();
                    }, 300);
                } else {
                    toastr.error(res.response_message || 'Could not fetch RT Report details.');
                }
            },
            error: function () {
                btn.prop('disabled', false).text('Submit');
                toastr.error('Server error while fetching RT Report details.');
            }
        });
    });

    // 10. Detail Modal: Add Click Handler (Opens Detail Popup Modal in Add Mode)
    jQuery('#addDetailRowBtn').on('click', function () {
        openDetailModalAddMode();
    });

    // 11. Detail Modal: Edit Row Click Handler (Opens Detail Popup Modal in Edit Mode)
    jQuery(document).on('click', '.edit-detail-row', function () {
        let index = jQuery(this).data('index');
        if (allDetailsData[index]) {
            openDetailModalEditMode(index);
        }
    });

    // 12. Detail Modal: Delete Row from Detail Table (Group delete by test_report_rt_id)
    jQuery(document).on('click', '.delete-detail-row', function () {
        let index = jQuery(this).data('index');
        let targetItem = allDetailsData[index];
        if (!targetItem) return;

        toastDelete("Do you want to delete this record?", function () {
            let targetReportId = targetItem.test_report_rt_id;

            if (targetReportId) {
                // Remove from selectedPendingReportIds Set so it can be re-selected if needed
                selectedPendingReportIds.delete(String(targetReportId));
                syncSelectAllState();

                // Mark or remove all rows associated with this test_report_rt_id
                for (let i = allDetailsData.length - 1; i >= 0; i--) {
                    if (allDetailsData[i].test_report_rt_id == targetReportId) {
                        if (allDetailsData[i].film_dc_detail_id) {
                            allDetailsData[i].mode = 'Delete';
                        } else {
                            allDetailsData.splice(i, 1);
                        }
                    }
                }
            } else {
                // Manual entry: single row delete
                if (targetItem.film_dc_detail_id) {
                    targetItem.mode = 'Delete';
                } else {
                    allDetailsData.splice(index, 1);
                }
            }

            renderDetailsGrid();
            updateFormLockState();
        });
    });

    // 13. Detail Modal: Calculation when Film Size or DC Qty changes
    jQuery('#detail_film_id, #detail_dc_quantity').on('change keyup', function () {
        calculateDetailModalSqIn();
    });

    // 14. Detail Modal: Form Submit (Add / Update)
    jQuery('#FilmDCDetailForm').on('submit', function (e) {
        e.preventDefault();

        if (this.checkValidity() === false) {
            e.stopPropagation();
            jQuery(this).addClass('was-validated');
            return;
        }

        jQuery(this).removeClass('was-validated');

        let mode = jQuery('#detail_form_mode').val();
        let rowIndex = jQuery('#detail_row_index').val();

        let unit = jQuery('input[name="film_size_unit_fix"]:checked').val() || 'inch';
        let filmOpt = jQuery('#detail_film_id option:selected');
        let filmId = jQuery('#detail_film_id').val();
        let filmBrandId = jQuery('#detail_film_brand_id').val();
        let filmBrandName = jQuery('#detail_film_brand_id option:selected').text();
        let filmSizeInch = filmOpt.data('size_inch') || filmOpt.data('size') || filmOpt.text();
        let filmSizeCm = filmOpt.data('size_cm') || filmOpt.text();
        let filmSizeDisplay = (unit === 'cm' ? filmSizeCm : filmSizeInch);
        let dbSqIn = filmOpt.data('sq_in');
        let dbSqCm = filmOpt.data('sq_cm');
        let sqIn = parseFilmSqIn(filmSizeInch, dbSqIn);
        let sqCm = parseFilmSqIn(filmSizeCm, dbSqCm);
        let dcQty = parseFloat(jQuery('#detail_dc_quantity').val() || 0);
        let totalSqIn = sqIn * dcQty;
        let totalSqCm = sqCm * dcQty;

        let rowData = {
            test_report_rt_id: jQuery('#detail_test_report_rt_id').val() || null,
            test_report_no: jQuery('#detail_test_report_no').val() || '',
            test_report_date: jQuery('#detail_test_report_date').val() || '',
            die_no: jQuery('#detail_die_no').val() || '',
            part_no: jQuery('#detail_die_no').val() || '',
            film_brand_id: filmBrandId ? parseInt(filmBrandId) : null,
            film_brand: filmBrandId ? filmBrandName : '',
            film_id: filmId ? parseInt(filmId) : null,
            film_size_inch: filmSizeDisplay,
            film_size_cm: filmSizeCm,
            sq_in: sqIn,
            sq_cm: sqCm,
            dc_quantity: Math.round(dcQty),
            total_sq_in: totalSqIn,
            total_sq_cm: totalSqCm,
            remark: jQuery('#detail_remark').val() || '',
            mode: (mode === 'edit' && allDetailsData[rowIndex] && allDetailsData[rowIndex].film_dc_detail_id) ? 'Update' : 'New'
        };

        if (mode === 'add') {
            allDetailsData.push(rowData);
            renderDetailsGrid();
            updateFormLockState();
            toastSuccess('Record Inserted.');
            openDetailModalAddMode();
            setTimeout(() => {
                jQuery('#detail_test_report_no').focus();
            }, 150);
        } else if (mode === 'edit' && rowIndex !== '') {
            if (allDetailsData[rowIndex].film_dc_detail_id) {
                rowData.film_dc_detail_id = allDetailsData[rowIndex].film_dc_detail_id;
                rowData.film_dc_id = allDetailsData[rowIndex].film_dc_id;
            }
            allDetailsData[rowIndex] = rowData;
            renderDetailsGrid();
            updateFormLockState();
            toastSuccess('Record Updated.');
            jQuery('#FilmDCDetailModal').modal('hide');
        }
    });

    // 15. Detail Modal: Reset Button Handler
    jQuery('#detail_resetbtn').on('click', function () {
        let mode = jQuery('#detail_form_mode').val();
        let rowIndex = jQuery('#detail_row_index').val();
        jQuery('#FilmDCDetailForm').removeClass('was-validated');
        if (mode === 'edit' && rowIndex !== '' && allDetailsData[rowIndex]) {
            openDetailModalEditMode(rowIndex);
        } else {
            openDetailModalAddMode();
        }
    });

    // 16. Form Submit (Add / Update) with Double-Click Prevention & Spinner
    jQuery('#commonFilmDCForm').on('submit', function (e) {
        e.preventDefault();

        if (this.checkValidity() === false) {
            e.stopPropagation();
            jQuery(this).addClass('was-validated');
            return;
        }

        let activeRows = allDetailsData.filter(d => d.mode !== 'Delete');
        if (activeRows.length === 0) {
            let fromType = jQuery('input[name="from_type_id"]:checked').val() || 'Test Report RT';
            if (fromType === 'Test Report RT') {
                toastr.error('Please select At least one Report From Pending.');
            } else {
                toastr.error('Please Add At Least One Film DC Detail.');
            }
            return;
        }

        let isUpdate = Boolean(jQuery('#id').val());
        let submitBtn = jQuery('#submitbtn');
        let originalText = isUpdate ? 'Update' : 'Submit';

        submitBtn.prop('disabled', true);

        let $disabledElements = jQuery(this).find(':disabled');
        $disabledElements.prop('disabled', false);
        let formData = jQuery(this).serializeArray();
        $disabledElements.prop('disabled', true);

        formData.push({ name: 'details', value: JSON.stringify(allDetailsData) });

        let targetUrl = isUpdate ? 'update-film_dc' : 'store-film_dc';

        jQuery.ajax({
            url: targetUrl,
            type: 'POST',
            data: formData,
            headers: getAjaxHeaders(),
            success: function (res) {
                submitBtn.prop('disabled', false).html(originalText);
                if (res.response_code == 1) {
                    if (isUpdate) {
                        let redirectFn = function () {
                            jQuery('#FilmDCModal').modal('hide');
                            if (jQuery('#dyntable').length && jQuery.fn.DataTable.isDataTable('#dyntable')) {
                                jQuery('#dyntable').DataTable().ajax.reload(null, false);
                            }
                        };
                        if (res.url && res.url !== '') {
                            jQuery('#preview_btn').attr('href', res.url).show();
                            toastSuccessPreview(res.response_message || 'Record Updated.', res.url, redirectFn);
                        } else {
                            toastSuccess(res.response_message || 'Record Updated.', redirectFn);
                        }
                    } else {
                        let nextFn = function () {
                            resetToAddMode();
                            if (jQuery('#dyntable').length && jQuery.fn.DataTable.isDataTable('#dyntable')) {
                                jQuery('#dyntable').DataTable().ajax.reload(null, false);
                            }
                            setTimeout(() => {
                                jQuery('#film_dc_sequence').focus().select();
                            }, 150);
                        };
                        if (res.url && res.url !== '') {
                            jQuery('#preview_btn').attr('href', res.url).show();
                            toastSuccessPreview(res.response_message || 'Record Inserted.', res.url, nextFn);
                        } else {
                            toastSuccess(res.response_message || 'Record Inserted.', nextFn);
                        }
                    }
                } else {
                    toastr.error(res.response_message || 'Something went wrong');
                }
            },
            error: function () {
                submitBtn.prop('disabled', false).html(originalText);
                toastr.error('Server error occurred.');
            }
        });
    });

    // 17. Edit Click Handler from DataTable
    jQuery(document).on('click', '.edit-film_dc', function () {
        let id = jQuery(this).data('id');
        openEditMode(id);
    });

    // 18. Main Reset Button Handler
    jQuery('#resetbtn').on('click', function () {
        if (_isEditMode && currentEditId) {
            openEditMode(currentEditId);
        } else {
            resetToAddMode();
        }
    });

    // 19. Add New Button Handler
    jQuery('#add_new').on('click', function () {
        resetToAddMode();
    });
});

// Helper Functions

function updateFilmSizeLabels() {
    let unit = jQuery('input[name="film_size_unit_fix"]:checked').val() || 'inch';
    let unitText = unit === 'cm' ? 'cm' : 'inch';

    jQuery('label[for="detail_film_id"]').html(`Film Size (${unitText}) <sup class="astric">*</sup>`);
    jQuery('.th_film_size_unit').text(`Film Size (${unitText})`);

    jQuery('#detail_film_id option').each(function () {
        let opt = jQuery(this);
        if (opt.val()) {
            let size = (unit === 'cm' ? opt.data('size_cm') : opt.data('size_inch')) || opt.data('size') || opt.val();
            opt.text(size);
        }
    });
}

function updateFormLockState() {
    let hasActiveRows = allDetailsData.some(d => d.mode !== 'Delete');
    let fromType = jQuery('input[name="from_type_id"]:checked').val() || jQuery('input[name="from_type_id"]').filter(':checked').val() || 'Test Report RT';

    if (_isEditMode) {
        setRadioReadonly('input[name="from_type_id"]', true);
        setRadioReadonly('input[name="job_type_fix"]', true);
        setRadioReadonly('input[name="film_size_unit_fix"]', true);
        jQuery('#customer_id').prop('disabled', true);
        if (typeof setSelect2Readonly === 'function') {
            setSelect2Readonly('#customer_id', true);
        }
    } else if (fromType === 'Test Report RT' && hasActiveRows) {
        setRadioReadonly('input[name="from_type_id"]', true);
        setRadioReadonly('input[name="job_type_fix"]', true);
        setRadioReadonly('input[name="film_size_unit_fix"]', true);
        jQuery('#customer_id').prop('disabled', true);
        if (typeof setSelect2Readonly === 'function') {
            setSelect2Readonly('#customer_id', true);
        }
    } else {
        setRadioReadonly('input[name="from_type_id"]', false);
        setRadioReadonly('input[name="job_type_fix"]', false);
        setRadioReadonly('input[name="film_size_unit_fix"]', false);
        jQuery('#customer_id').prop('disabled', false);
        if (typeof setSelect2Readonly === 'function') {
            setSelect2Readonly('#customer_id', false);
        }
    }
}

function openDetailModalAddMode() {
    let $form = jQuery('#FilmDCDetailForm');
    $form[0].reset();
    $form.removeClass('was-validated');
    $form.find('input, select, textarea').removeClass('is-valid is-invalid');
    $form.find('input, select, textarea').each(function () {
        this.setCustomValidity('');
    });

    jQuery('#detail_form_mode').val('add');
    jQuery('#detail_row_index').val('');
    jQuery('#detail_film_dc_detail_id').val('');
    jQuery('#detail_test_report_rt_id').val('');

    jQuery('#detail_film_brand_id').val('').trigger('change.select2');
    jQuery('#detail_film_id').val('').trigger('change.select2');

    updateFilmSizeLabels();

    let dcDate = jQuery('#film_dc_date').val();
    jQuery('#detail_test_report_date').val(dcDate);
    jQuery('#detail_total_sq_in').val('0.000');

    jQuery('#detail_submitbtn').text('Submit');
    jQuery('#FilmDCDetailModalLabel').text('Film DC Detail');

    setTimeout(() => {
        $form.removeClass('was-validated');
        $form.find('input, select, textarea').removeClass('is-valid is-invalid');
    }, 50);

    if (!jQuery('#FilmDCDetailModal').hasClass('show')) {
        jQuery('#FilmDCDetailModal').modal('show');
    }
}

function openDetailModalEditMode(index) {
    let item = allDetailsData[index];
    if (!item) return;

    let $form = jQuery('#FilmDCDetailForm');
    $form[0].reset();
    $form.removeClass('was-validated');
    $form.find('input, select, textarea').removeClass('is-valid is-invalid');
    $form.find('input, select, textarea').each(function () {
        this.setCustomValidity('');
    });

    jQuery('#detail_form_mode').val('edit');
    jQuery('#detail_row_index').val(index);
    jQuery('#detail_film_dc_detail_id').val(item.film_dc_detail_id || '');
    jQuery('#detail_test_report_rt_id').val(item.test_report_rt_id || '');

    jQuery('#detail_test_report_no').val(item.test_report_no || '');
    jQuery('#detail_test_report_date').val(item.test_report_date || '');
    jQuery('#detail_die_no').val(item.die_no || item.part_no || '');
    jQuery('#detail_dc_quantity').val(Math.round(item.dc_quantity || 0));
    jQuery('#detail_remark').val(item.remark || '');

    updateFilmSizeLabels();

    jQuery('#detail_film_brand_id').val(item.film_brand_id || '').trigger('change.select2');
    jQuery('#detail_film_id').val(item.film_id || '').trigger('change.select2');

    calculateDetailModalSqIn();

    jQuery('#detail_submitbtn').text('Update');
    jQuery('#FilmDCDetailModalLabel').text('Film DC Detail');

    setTimeout(() => {
        $form.removeClass('was-validated');
        $form.find('input, select, textarea').removeClass('is-valid is-invalid');
    }, 50);

    jQuery('#FilmDCDetailModal').modal('show');
}

function parseFilmSqIn(filmSizeInch, dbSqIn) {
    let sqIn = parseFloat(dbSqIn || 0);
    if (sqIn > 0) return sqIn;
    if (!filmSizeInch) return 0;
    let matches = String(filmSizeInch).match(/^(\d+(?:\.\d+)?)\s*[xX*]\s*(\d+(?:\.\d+)?)/);
    if (matches && matches[1] && matches[2]) {
        return parseFloat(matches[1]) * parseFloat(matches[2]);
    }
    return 0;
}

function calculateDetailModalSqIn() {
    let unit = jQuery('input[name="film_size_unit_fix"]:checked').val() || 'inch';
    let filmOpt = jQuery('#detail_film_id option:selected');
    let filmSizeText = unit === 'cm' ? (filmOpt.data('size_cm') || filmOpt.text()) : (filmOpt.data('size_inch') || filmOpt.data('size') || filmOpt.text());
    let dbSqIn = filmOpt.data('sq_in');
    let dbSqCm = filmOpt.data('sq_cm');

    let sqIn = parseFilmSqIn(filmOpt.data('size_inch') || filmSizeText, dbSqIn);
    let sqCm = parseFilmSqIn(filmOpt.data('size_cm') || filmSizeText, dbSqCm);
    let qty = parseFloat(jQuery('#detail_dc_quantity').val() || 0);

    let activeSq = (unit === 'cm') ? sqCm : sqIn;
    let activeTotalSq = (activeSq * qty).toFixed(3);

    jQuery('#detail_total_sq_in').val(activeTotalSq);
    jQuery('#detail_sq_in').val(sqIn);
    jQuery('#detail_sq_cm').val(sqCm);
}

function resetToAddMode() {
    _isEditMode = false;
    currentEditId = null;
    allDetailsData = [];
    selectedPendingReportIds.clear();

    jQuery('#commonFilmDCForm')[0].reset();
    jQuery('#commonFilmDCForm').removeClass('was-validated');
    jQuery('#commonFilmDCForm').find('input, select, textarea').removeClass('is-valid is-invalid');
    jQuery('#id').val('');

    // Re-enable/unlock all inputs
    setRadioReadonly('input[name="from_type_id"]', false);
    setRadioReadonly('input[name="job_type_fix"]', false);
    setRadioReadonly('input[name="film_size_unit_fix"]', false);
    jQuery('#customer_id').prop('disabled', false);
    if (typeof setSelect2Readonly === 'function') {
        setSelect2Readonly('#customer_id', false);
    }

    jQuery('#submitbtn').show().prop('disabled', false).text('Submit');
    jQuery('#preview_btn').hide();
    jQuery('#add_new').hide();
    jQuery('#pending_btn').prop('disabled', true);
    jQuery('#addDetailRowBtn').prop('disabled', true);

    let today = new Date();
    let dd = String(today.getDate()).padStart(2, '0');
    let mm = String(today.getMonth() + 1).padStart(2, '0');
    let yyyy = today.getFullYear();
    jQuery('#film_dc_date').val(`${dd}/${mm}/${yyyy}`);
    jQuery('input[name="film_size_unit_fix"][value="inch"]').prop('checked', true);
    jQuery('input[name="from_type_id"][value="Test Report RT"]').prop('checked', true);
    jQuery('input[name="job_type_fix"][value="Non-Welding"]').prop('checked', true);

    updateFilmSizeLabels();
    fetchLatestDCSequence();
    loadPendingCustomers();
    renderDetailsGrid();

    setTimeout(() => {
        jQuery('#film_dc_sequence').focus().select();
    }, 150);
}

function openEditMode(id) {
    _isEditMode = true;
    currentEditId = id;
    allDetailsData = [];

    jQuery('#commonFilmDCForm')[0].reset();
    jQuery('#commonFilmDCForm').removeClass('was-validated');
    jQuery('#id').val(id);

    // Re-enable/unlock inputs initially to populate values
    setRadioReadonly('input[name="from_type_id"]', false);
    setRadioReadonly('input[name="job_type_fix"]', false);
    setRadioReadonly('input[name="film_size_unit_fix"]', false);
    jQuery('#customer_id').prop('disabled', false);

    jQuery('#submitbtn').show().prop('disabled', false).text('Update');
    jQuery('#preview_btn').show();
    jQuery('#add_new').show();

    jQuery.ajax({
        url: 'edit-film_dc',
        type: 'GET',
        data: { id: id },
        headers: getAjaxHeaders(),
        success: function (res) {
            if (res.response_code == 1 && res.header_data) {
                let h = res.header_data;
                jQuery('#film_dc_sequence').val(h.film_dc_sequence);
                jQuery('#film_dc_no').val(h.film_dc_no);
                jQuery('#film_dc_date').val(h.film_dc_date);
                jQuery(`input[name="job_type_fix"][value="${h.job_type_fix}"]`).prop('checked', true);
                jQuery(`input[name="from_type_id"][value="${h.from_type_id}"]`).prop('checked', true);
                jQuery(`input[name="film_size_unit_fix"][value="${h.film_size_unit_fix || 'inch'}"]`).prop('checked', true);
                jQuery('#customer_client').val(h.customer_client || '');
                jQuery('#sp_note').val(h.sp_note || '');

                updateFilmSizeLabels();

                if (h.from_type_id === 'Manual') {
                    jQuery('#addDetailRowBtn').prop('disabled', false);
                } else {
                    jQuery('#addDetailRowBtn').prop('disabled', true);
                }
                jQuery('#pending_btn').prop('disabled', true);

                loadPendingCustomers(h.customer_id, id);

                selectedPendingReportIds.clear();
                allDetailsData = res.details_data || [];
                allDetailsData.forEach(d => {
                    if (d.test_report_rt_id) {
                        selectedPendingReportIds.add(String(d.test_report_rt_id));
                    }
                });
                renderDetailsGrid();

                updateFormLockState();

                jQuery('#FilmDCModal').modal('show');
            } else {
                toastr.error(res.response_message || 'Could not load Film DC details.');
            }
        },
        error: function () {
            toastr.error('Something went wrong!');
        }
    });
}

function fetchLatestDCSequence() {
    jQuery('#submitbtn, #updatebtn').prop('disabled', true);
    jQuery.ajax({
        url: 'get-latest-dc-sequence-film_dc',
        type: 'GET',
        headers: getAjaxHeaders(),
        success: function (res) {
            if (res.response_code == 1) {
                jQuery('#film_dc_sequence').val(res.number);
                jQuery('#film_dc_no').val(res.latest_no);
                jQuery('#submitbtn, #updatebtn').prop('disabled', false);
            }
        },
        error: function () {
            jQuery('#submitbtn, #updatebtn').prop('disabled', false);
        }
    });
}

function loadPendingCustomers(selectedId = null, dcId = null) {
    let jobType = jQuery('input[name="job_type_fix"]:checked').val() || 'Non-Welding';
    let filmSizeUnit = jQuery('input[name="film_size_unit_fix"]:checked').val() || 'inch';
    let fromType = jQuery('input[name="from_type_id"]:checked').val() || jQuery('input[name="from_type_id"]').filter(':checked').val() || 'Test Report RT';

    jQuery.ajax({
        url: 'get-pending-customers-for-film_dc',
        type: 'GET',
        data: { 
            job_type_fix: jobType, 
            film_size_unit_fix: filmSizeUnit, 
            from_type_id: fromType,
            film_dc_id: dcId 
        },
        headers: getAjaxHeaders(),
        success: function (res) {
            let select = jQuery('#customer_id');
            select.empty().append('<option value="">Select Customer</option>');

            if (res.customers && res.customers.length > 0) {
                res.customers.forEach(c => {
                    let opt = new Option(`${c.customer}`, c.id, false, false);
                    select.append(opt);
                });
            }

            if (selectedId) {
                select.val(selectedId).trigger('change');
            } else {
                select.val('').trigger('change');
            }

            var formid = jQuery('#id').val();
            if(formid > 0 || formid != "" || _isEditMode){
                jQuery('#pending_btn').prop('disabled', true);
                jQuery('#customer_id').prop('disabled', true);
                if (typeof setSelect2Readonly === 'function') {
                    setSelect2Readonly('#customer_id', true);
                }
            }else{
                let custVal = jQuery('#customer_id').val();
                let currentFromType = jQuery('input[name="from_type_id"]:checked').val() || 'Test Report RT';
                jQuery('#pending_btn').prop('disabled', (!custVal || currentFromType === 'Manual'));
            }
        }
    });
}

function fetchAndRenderPendingReports() {
    let custId = jQuery('#customer_id').val();
    let jobType = jQuery('input[name="job_type_fix"]:checked').val() || 'Non-Welding';
    let filmSizeUnit = jQuery('input[name="film_size_unit_fix"]:checked').val() || 'inch';
    let dcId = jQuery('#id').val() || '';

    jQuery.ajax({
        url: 'get-pending-rt-for-film_dc',
        type: 'GET',
        data: {
            customer_id: custId,
            job_type_fix: jobType,
            film_size_unit_fix: filmSizeUnit,
            film_dc_id: dcId
        },
        headers: getAjaxHeaders(),
        success: function (res) {
            if (res.response_code == 1) {
                availablePendingReports = res.reports || [];
                renderPendingTable();
            }
        }
    });
}

function renderPendingTable() {
    let $table = jQuery('#PendingFilmDcTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().clear().destroy();
        $table.find('thead tr.search-row').remove();
    }

    let tbody = jQuery('#pending_rt_reports_tbody');
    tbody.empty();

    if (availablePendingReports.length === 0) {
        tbody.append('<tr><td colspan="11" class="text-center text-muted">No Pending RT Reports Available</td></tr>');
    } else {
        availablePendingReports.forEach(r => {
            let isChecked = selectedPendingReportIds.has(String(r.test_report_rt_id)) ? 'checked' : '';
            let tr = `<tr>
                <td class="text-center radio_column">
                    <input type="checkbox" class="form-check-input pending_rt_checkbox" value="${r.test_report_rt_id}" ${isChecked}>
                </td>
                <td>${r.test_report_no || ''}</td>
                <td>${r.revision_number || ''}</td>
                <td>${r.test_report_date || ''}</td>
                <td>${r.customer_client || ''}</td>
                <td>${r.rt_no || ''}</td>
                <td>${r.heat_no || ''}</td>
                <td>${r.job_desc || ''}</td>
                <td>${r.part_no || ''}</td>
                <td>${r.drg_no || ''}</td>
                <td>${r.product_code || ''}</td>
            </tr>`;
            tbody.append(tr);
        });
    }

    // Matching test_report_rt.js:864-875 standard
    var $new = $table.DataTable({
        paging: true,
        searching: true,
        "oLanguage": {
            "sSearch": "Search :"
        },
        dom: 'lrtip',
        "sScrollX": true,
        "sScrollX": "100%",
        "sScrollXInner": "110%",
        "bScrollCollapse": true,
        columnDefs: [
            { orderable: false, targets: 0 }
        ]
    });

    syncSelectAllState();
}

// Matching test_report_rt.js:885-893 standard
jQuery('#PendingRtForFilmDCModal').on('shown.bs.modal', function () {
    let $table = jQuery('#PendingFilmDcTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().columns.adjust();
        if (typeof initColumnSearch === 'function') {
            initColumnSearch('#PendingFilmDcTable', [0], 'common_search');
        }
    }
});

// Film DC Main Modal shown focus
jQuery('#FilmDCModal').on('shown.bs.modal', function () {
    if (!_isEditMode) {
        setTimeout(() => {
            jQuery('#film_dc_sequence').focus().select();
        }, 150);
    }
});

// Film DC Detail Modal shown / hidden focus & validation clean
jQuery('#FilmDCDetailModal').on('shown.bs.modal', function () {
    setTimeout(() => {
        jQuery('#detail_test_report_no').focus();
    }, 150);
});

jQuery('#FilmDCDetailModal').on('hidden.bs.modal', function () {
    let $form = jQuery('#FilmDCDetailForm');
    $form.removeClass('was-validated');
    $form.find('input, select, textarea').removeClass('is-valid is-invalid');
    setTimeout(() => {
        jQuery('#sp_note').focus();
    }, 150);
});

function syncSelectAllState() {
    let total = availablePendingReports.length;
    let checkedCount = 0;
    availablePendingReports.forEach(p => {
        if (selectedPendingReportIds.has(String(p.test_report_rt_id))) {
            checkedCount++;
        }
    });
    jQuery('#checkall-pending_data, #select_all_pending_reports').prop('checked', total > 0 && checkedCount === total);
}

function renderDetailsGrid() {
    let tbody = jQuery('#details_tbody');
    tbody.empty();

    let fromTypeId = jQuery('input[name="from_type_id"]:checked').val() || jQuery('input[name="from_type_id"]').filter(':checked').val() || 'Test Report RT';
    let unit = jQuery('input[name="film_size_unit_fix"]:checked').val() || 'inch';

    let visibleIndex = 0;
    allDetailsData.forEach((item, index) => {
        if (item.mode === 'Delete') {
            return;
        }

        visibleIndex++;
        let sqIn = parseFloat(item.sq_in || 0);
        if (sqIn === 0) {
            sqIn = parseFilmSqIn(item.film_size_inch, item.sq_in);
        }
        item.sq_in = sqIn;

        let sqCm = parseFloat(item.sq_cm || 0);
        item.sq_cm = sqCm;

        let qty = parseFloat(item.dc_quantity || 0);
        let totalSqIn = parseFloat(item.total_sq_in);
        if (isNaN(totalSqIn) || totalSqIn === 0) {
            totalSqIn = sqIn * qty;
            item.total_sq_in = totalSqIn;
        }

        let totalSqCm = parseFloat(item.total_sq_cm);
        if (isNaN(totalSqCm) || totalSqCm === 0) {
            totalSqCm = sqCm * qty;
            item.total_sq_cm = totalSqCm;
        }

        let displayTotalSq = (unit === 'cm') ? totalSqCm : totalSqIn;

        let actionDropdown = '';
        if (fromTypeId === 'Manual') {
            actionDropdown = `
                <div class="dropdown d-inline-block">
                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-more-fill align-middle"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item edit-detail-row" data-index="${index}">
                                <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item remove-item-btn delete-detail-row" data-index="${index}">
                                <i class="ri-delete-bin-fill align-bottom me-2 text-danger"></i> Delete
                            </a>
                        </li>
                    </ul>
                </div>`;
        } else {
            actionDropdown = `
                <div class="dropdown d-inline-block">
                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-more-fill align-middle"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="javascript:void(0);" class="dropdown-item remove-item-btn delete-detail-row" data-index="${index}">
                                <i class="ri-delete-bin-fill align-bottom me-2 text-danger"></i> Delete
                            </a>
                        </li>
                    </ul>
                </div>`;
        }

        let rowHtml = `<tr>
            <td class="text-center align-middle">${actionDropdown}</td>
            <td>${item.test_report_no || ''}</td>
            <td>${item.test_report_date || ''}</td>
            <td>${item.die_no || item.part_no || ''}</td>
            <td>${item.film_brand || ''}</td>
            <td>${item.film_size_inch || ''}</td>
            <td>${Math.round(qty)}</td>
            <td>${item.remark || ''}</td>
        </tr>`;
        tbody.append(rowHtml);
    });

    if (visibleIndex === 0) {
        tbody.append('<tr id="noDetails"><td colspan="8" class="text-center" id="noDetailsCell">No Film DC Details Added</td></tr>');
    }

    calculateSummary();
    updateFormLockState();
}

function calculateSummary() {
    let unit = jQuery('input[name="film_size_unit_fix"]:checked').val() || 'inch';
    let totalQty = 0;
    let totalSq = 0;
    let sizeMap = {};

    allDetailsData.forEach(item => {
        if (item.mode !== 'Delete') {
            let qty = parseFloat(item.dc_quantity || 0);
            let sq = (unit === 'cm') ? parseFloat(item.total_sq_cm || 0) : parseFloat(item.total_sq_in || 0);
            if (isNaN(sq) || sq === 0) {
                let singleSq = (unit === 'cm') ? parseFloat(item.sq_cm || 0) : parseFloat(item.sq_in || 0);
                sq = singleSq * qty;
            }
            let size = (unit === 'cm' ? (item.film_size_cm || item.film_size_inch || '') : (item.film_size_inch || item.film_size_cm || ''));

            totalQty += qty;
            totalSq += sq;

            if (size) {
                sizeMap[size] = (sizeMap[size] || 0) + qty;
            }
        }
    });

    jQuery('#total_qty').val(Math.round(totalQty));
    jQuery('#total_square_inch').val(totalSq.toFixed(3));

    let summaryArr = [];
    for (let s in sizeMap) {
        summaryArr.push(`${s}=${Math.round(sizeMap[s])}`);
    }
    jQuery('#film_size_for_print').val(summaryArr.join(', '));
}
