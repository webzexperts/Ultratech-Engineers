var selectedWorksheets = new Map();
var allPendingWorksheets = [];
window.jobCardWsNoSearchTerm = '';
var pendingWorksheetsXhr = null;

var originalJobCardTheadHtml = '<tr>' +
    '<th style="width: 40px;" class="text-center">' +
    '<input type="checkbox" name="checkall_worksheet" class="simple-check" id="checkall_worksheet"/>' +
    '</th>' +
    '<th style="width: 120px;">Priority</th>' +
    '<th>WS No.</th>' +
    '<th>WS Date</th>' +
    '<th>Customer</th>' +
    '<th>Job Description</th>' +
    '<th>Die No.</th>' +
    '<th>Drg No.</th>' +
    '<th>Heat No.</th>' +
    '<th>Material</th>' +
    '<th>Qty.</th>' +
    '</tr>';

// Custom DataTable Filter for JobCardWorksheetTable (System Document Number Pattern)
$.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
    if (settings.nTable.id !== 'JobCardWorksheetTable') {
        return true;
    }
    var term = (window.jobCardWsNoSearchTerm || '').trim().toLowerCase();
    if (!term) return true;

    // Column 2 is WS No.
    var wsNoVal = (data[2] || '').toLowerCase().trim();
    if (!wsNoVal) return false;

    // If typing digits only: e.g. "1" -> pad to "0001", matches "0001/26-27"
    if (/^\d+$/.test(term)) {
        var seq = term.padStart(4, '0');
        return wsNoVal.startsWith(seq + '/') || wsNoVal.includes('/' + seq + '/') || wsNoVal.includes(seq) || wsNoVal.includes(term);
    }

    // If "/digits" input: e.g. "/1"
    var m = term.match(/^\/(\d+)$/);
    if (m) {
        var seq = m[1].padStart(4, '0');
        return wsNoVal.includes('/' + seq + '/') || wsNoVal.startsWith(seq + '/');
    }

    // Substring match
    return wsNoVal.includes(term);
});

jQuery(document).ready(function () {
    // Main Add Job Card Button Click (Ensure complete form reset for new record)
    jQuery(document).on('click', '#add_job_card_btn', function () {
        jQuery('#commonJobCardForm').find('#id').val('');
    });

    // Modal Shown Event
    jQuery('#JobCardModal').on('shown.bs.modal', function () {
        // Initialize Select2 dropdowns for modal
        jQuery('#JobCardModal .js-example-basic-single').select2({
            dropdownParent: jQuery('#JobCardModal'),
            width: '100%'
        });

        // Ensure datepicker is initialized with transaction datepicker restrictions
        if (!jQuery('#job_card_date').hasClass('hasDatepicker') && typeof startDate !== 'undefined' && typeof endDate !== 'undefined') {
            jQuery('#job_card_date').datepicker({
                dateFormat: "dd/mm/yy",
                minDate: startDate,
                maxDate: endDate,
                onClose: function () {
                    this.focus();
                }
            });
        }

        var formId = jQuery('#commonJobCardForm').find('#id').val();
        if (!formId) {
            initJobCardForm();
        }
        setTimeout(function () {
            jQuery('#job_card_sequence').focus().select();
            if (jQuery.fn.DataTable.isDataTable('#JobCardWorksheetTable')) {
                jQuery('#JobCardWorksheetTable').DataTable().columns.adjust().draw(false);
            }
            syncJobCardTableTabindex();
        }, 150);
    });

    // Tab navigation from Camera to First Checked Priority Dropdown or First Row Checkbox
    jQuery(document).on('keydown', '#JobCardModal #rt_camera_id + .select2-container .select2-selection', function (e) {
        if (e.key === 'Tab' && !e.shiftKey) {
            e.preventDefault();
            var $checkedPriority = jQuery('#JobCardWorksheetTable tbody .worksheet-priority-select:not(:disabled)').first();
            if ($checkedPriority.length) {
                $checkedPriority.next('.select2-container').find('.select2-selection').focus();
            } else {
                var $firstChk = jQuery('#JobCardWorksheetTable tbody .worksheet-row-check').first();
                if ($firstChk.length) {
                    $firstChk.focus();
                } else {
                    jQuery('#submitbtn').focus();
                }
            }
        }
    });

    // Tab navigation from Priority Dropdown to Next Priority or Submit Button
    jQuery(document).on('keydown', '#JobCardWorksheetTable tbody .worksheet-priority-select + .select2-container .select2-selection', function (e) {
        var $curRow = jQuery(this).closest('tr');
        if (e.key === 'Tab' && !e.shiftKey) {
            var $nextPriority = $curRow.nextAll('tr').find('.worksheet-priority-select:not(:disabled)').first();
            if ($nextPriority.length) {
                e.preventDefault();
                $nextPriority.next('.select2-container').find('.select2-selection').focus();
            } else {
                e.preventDefault();
                jQuery('#submitbtn').focus();
            }
        } else if (e.key === 'Tab' && e.shiftKey) {
            var $prevPriority = $curRow.prevAll('tr').find('.worksheet-priority-select:not(:disabled)').first();
            if ($prevPriority.length) {
                e.preventDefault();
                $prevPriority.next('.select2-container').find('.select2-selection').focus();
            } else {
                e.preventDefault();
                jQuery('#rt_camera_id').next('.select2-container').find('.select2-selection').focus();
            }
        }
    });

    // Shift+Tab on Submit Button to jump back to Last Priority Dropdown
    jQuery(document).on('keydown', '#JobCardModal #submitbtn', function (e) {
        if (e.key === 'Tab' && e.shiftKey) {
            var $lastPriority = jQuery('#JobCardWorksheetTable tbody .worksheet-priority-select:not(:disabled)').last();
            if ($lastPriority.length) {
                e.preventDefault();
                $lastPriority.next('.select2-container').find('.select2-selection').focus();
            }
        }
    });

    // Reset Button
    jQuery('#resetbtn').on('click', function () {
        resetJobCardForm();
        setTimeout(function () {
            jQuery('#job_card_sequence').focus().select();
        }, 150);
    });

    // Add New Button (Inside Modal)
    jQuery('#add_new').on('click', function () {
        initJobCardForm();
        setTimeout(function () {
            jQuery('#job_card_sequence').focus().select();
        }, 150);
    });

    // Sequence Duplication & Auto Generation Check
    jQuery('#job_card_sequence').on('change', function () {
        var seq = jQuery(this).val();
        var $form = jQuery('#commonJobCardForm');
        var id = $form.find('#id').val();

        if (seq !== '') {
            // Sequence 0 & Invalid Validation (Matches Observation Sheet & Transaction Standards)
            if (parseInt(seq, 10) <= 0 || isNaN(parseInt(seq, 10))) {
                toastr.error('Please Enter Valid Sr. No.');
                jQuery('#job_card_sequence').val('');
                jQuery('#job_card_sequence').focus();
                jQuery('#submitbtn').prop('disabled', false);
                return;
            }

            // Sequence Number Change & Submit Button Lock Rule
            jQuery('#submitbtn').prop('disabled', true);

            jQuery.ajax({
                url: 'check-job_card_number_duplication',
                type: 'GET',
                data: { sequence: seq, id: id },
                headers: headerOpt,
                dataType: 'json',
                success: function (data) {
                    if (data.response_code == 1) {
                        jQuery('#job_card_no').val(data.latest_no);
                        jQuery('#job_card_sequence').val(data.number);
                    } else {
                        toastr.error(data.response_message || 'Duplicate Job Card No. Found.');
                        jQuery('#job_card_sequence').val('');
                        jQuery('#job_card_sequence').focus();
                    }
                    jQuery('#submitbtn').prop('disabled', false);
                },
                error: function () {
                    jQuery('#submitbtn').prop('disabled', false);
                    toastr.error('Something went wrong!');
                }
            });
        }
    });

    // Column Search Event Delegation for Job Card Table (Smooth & Flicker-Free)
    var jobCardColSearchTimer = null;
    jQuery(document).off('input', '#JobCardModal .dataTables_scrollHead thead tr.search-row input.col-search, #JobCardModal #JobCardWorksheetTable thead tr.search-row input.col-search')
        .on('input', '#JobCardModal .dataTables_scrollHead thead tr.search-row input.col-search, #JobCardModal #JobCardWorksheetTable thead tr.search-row input.col-search', function () {
            var $inp = jQuery(this);
            var colIdx = $inp.data('col');
            if (colIdx === undefined || colIdx === null || colIdx === '') {
                colIdx = $inp.closest('th').index();
            }
            var rawVal = $inp.val() || '';
            var val = rawVal.trim();

            // Sync ONLY to the opposite mirror thead, NEVER overwrite $inp itself while typing!
            var isScrollHead = $inp.closest('.dataTables_scrollHead').length > 0;
            var $wrapper = $inp.closest('.dataTables_wrapper');
            var $otherThead = isScrollHead
                ? $wrapper.find('.dataTables_scrollBody thead')
                : $wrapper.find('.dataTables_scrollHead thead');
            $otherThead.find('tr.search-row th').eq(colIdx).find('input.col-search').val(rawVal);

            clearTimeout(jobCardColSearchTimer);
            jobCardColSearchTimer = setTimeout(function () {
                var $table = jQuery('#JobCardWorksheetTable');
                if (!jQuery.fn.DataTable.isDataTable($table)) return;
                var dt = $table.DataTable();

                // Column 2 is WS No. -> use document number system filter
                if (colIdx == 2) {
                    if (window.jobCardWsNoSearchTerm !== val) {
                        window.jobCardWsNoSearchTerm = val;
                        dt.draw();
                    }
                } else {
                    // Columns 2+ (WS Date, Customer, Job Description, Die No., Drg No., Heat No., Material, Qty) -> text search
                    if (dt.column(colIdx).search() !== val) {
                        dt.column(colIdx).search(val).draw();
                    }
                }
            }, 150);
        });

    // Select All Checkbox Handler (Multi-Page Selection Rule)
    jQuery(document).on('change', '#checkall_worksheet', function () {
        var isChecked = jQuery(this).is(':checked');
        jQuery('#JobCardWorksheetTable tbody .invalid-tooltip').css('display', '');
        jQuery('#JobCardWorksheetTable tbody .worksheet-priority-select').removeClass('is-invalid');
        if (isChecked) {
            allPendingWorksheets.forEach(function (ws) {
                var obsDetailId = ws.observation_sheet_details_id;
                var dId = obsDetailId.toString();
                var existing = selectedWorksheets.get(dId);
                var jobCardDetailId = (existing && existing.job_card_detail_id) ? existing.job_card_detail_id : (ws.job_card_detail_id || 0);
                var existingPriority = existing ? existing.priority_id_fix : (ws.priority_id_fix || '');
                selectedWorksheets.set(dId, {
                    job_card_detail_id: jobCardDetailId,
                    observation_sheet_details_id: obsDetailId,
                    priority_id_fix: existingPriority,
                    mode: (jobCardDetailId ? 'Update' : 'Insert')
                });
            });
        } else {
            selectedWorksheets.forEach(function (item, key) {
                if (item.job_card_detail_id) {
                    item.mode = 'Delete';
                } else {
                    selectedWorksheets.delete(key);
                }
            });
        }
        syncWorksheetRowsState();
        jQuery('#JobCardWorksheetTable tbody .worksheet-priority-select').trigger('change.select2');
    });

    // Row Checkbox Handler (Multi-Page Selection Rule)
    jQuery(document).on('change', '.worksheet-row-check', function () {
        var $chk = jQuery(this);
        var detailId = $chk.val();
        var rowDetailId = $chk.data('detail-id') || 0;
        var $row = $chk.closest('tr');
        var $priority = $row.find('.worksheet-priority-select');
        $priority.removeClass('is-invalid');
        $row.find('.invalid-tooltip').css('display', '');

        if ($chk.is(':checked')) {
            $priority.prop('disabled', false).prop('required', true).trigger('change.select2');
            var existing = selectedWorksheets.get(detailId.toString());
            var jcDetailId = (existing && existing.job_card_detail_id) ? existing.job_card_detail_id : rowDetailId;
            selectedWorksheets.set(detailId.toString(), {
                job_card_detail_id: jcDetailId,
                observation_sheet_details_id: detailId,
                priority_id_fix: $priority.val() || (existing ? existing.priority_id_fix : ''),
                mode: (jcDetailId ? 'Update' : 'Insert')
            });
        } else {
            // 1. FIRST update selectedWorksheets memory!
            var existing = selectedWorksheets.get(detailId.toString());
            if (existing && existing.job_card_detail_id) {
                existing.mode = 'Delete';
            } else {
                selectedWorksheets.delete(detailId.toString());
            }

            // 2. Clear priority dropdown & reset order
            $priority.prop('disabled', true).prop('required', false).trigger('change.select2');
            $priority.val('').trigger('change.select2');
            var $td = $priority.closest('td');
            $td.attr('data-order', 99);

            // 3. NOW redraw DataTable safely
            var $table = jQuery('#JobCardWorksheetTable');
            if (jQuery.fn.DataTable.isDataTable($table)) {
                var dt = $table.DataTable();
                dt.cell($td).invalidate();
                dt.order([[1, 'asc']]).draw(false);
            }
            jQuery('#checkall_worksheet').prop('checked', false);
        }

        var activeCount = 0;
        selectedWorksheets.forEach(function (val) {
            if (val.mode !== 'Delete') activeCount++;
        });
        if (allPendingWorksheets.length > 0 && activeCount === allPendingWorksheets.length) {
            jQuery('#checkall_worksheet').prop('checked', true);
        }
    });

    // Priority Dropdown Handler (Real-Time Grid Sorting)
    jQuery(document).on('change', '.worksheet-priority-select', function () {
        var $select = jQuery(this);
        var detailId = $select.data('id');
        var val = $select.val();
        var $td = $select.closest('td');

        var priorityOrderMap = { '1st': 1, '2nd': 2, '3rd': 3, '4th': 4 };
        var orderVal = priorityOrderMap[val] || 99;
        $td.attr('data-order', orderVal);

        if (val) {
            $select.removeClass('is-invalid');
            $td.find('.invalid-tooltip').css('display', '');
        }

        if (detailId && selectedWorksheets.has(detailId.toString())) {
            var item = selectedWorksheets.get(detailId.toString());
            if (item) {
                item.priority_id_fix = val;
            }
        }

        // Real-time DataTable re-sort by Priority without page jump
        var $table = jQuery('#JobCardWorksheetTable');
        if (jQuery.fn.DataTable.isDataTable($table)) {
            var dt = $table.DataTable();
            dt.cell($td).invalidate();
            dt.order([[1, 'asc']]).draw(false);
        }
    });

    // Edit Job Card from DataTable
    jQuery('#dyntable tbody').on('click', '.edit-job_card', function () {
        var id = jQuery(this).data('id');
        if (!id) return;

        jQuery.ajax({
            url: 'edit-job_card',
            type: 'GET',
            data: { id: id },
            headers: headerOpt,
            dataType: 'json',
            success: function (res) {
                if (res.response_code == 1 && res.job_card) {
                    var jc = res.job_card;
                    var form = jQuery('#commonJobCardForm');
                    var jcId = jc.job_card_id || jc.id || id;
                    form.find('#id').val(jcId);
                    form.data('orig-seq', jc.job_card_sequence);
                    form.data('orig-no', jc.job_card_no);
                    form.find('#job_card_sequence').val(jc.job_card_sequence);
                    form.find('#job_card_no').val(jc.job_card_no);
                    form.find('#job_card_date').val(jc.job_card_date);

                    // Ensure options exist in dropdowns even if inactive
                    ensureSelectOption(form.find('#shift_id'), jc.shift_id, jc.shift_name);
                    ensureSelectOption(form.find('#incharge_authority_person_id'), jc.incharge_authority_person_id, jc.incharge_name);
                    ensureSelectOption(form.find('#radiographer_authority_person_id'), jc.radiographer_authority_person_id, jc.radiographer_name);
                    ensureSelectOption(form.find('#rt_camera_id'), jc.rt_camera_id, jc.camera_name_for_display || jc.rt_camera_name);

                    form.find('#shift_id').val(jc.shift_id).trigger('change');
                    form.find('#incharge_authority_person_id').val(jc.incharge_authority_person_id).trigger('change');
                    form.find('#radiographer_authority_person_id').val(jc.radiographer_authority_person_id).trigger('change');
                    form.find('#rt_camera_id').val(jc.rt_camera_id).trigger('change');

                    jQuery('#submitbtn').text('Update');
                    jQuery('#add_new').show();

                    selectedWorksheets.clear();
                    if (res.job_card_details && res.job_card_details.length > 0) {
                        res.job_card_details.forEach(function (d) {
                            var obsDetailId = d.observation_sheet_details_id;
                            selectedWorksheets.set(obsDetailId.toString(), {
                                job_card_detail_id: d.job_card_detail_id || 0,
                                observation_sheet_details_id: obsDetailId,
                                priority_id_fix: d.priority_id_fix,
                                mode: 'Update'
                            });
                        });
                    }

                    loadPendingWorksheets(jc.job_card_id);
                    jQuery('#JobCardModal').modal('show');
                } else {
                    toastr.error(res.response_message || 'Could not load record.');
                }
            },
            error: function () {
                toastr.error('Failed to fetch Job Card details.');
            }
        });
    });

    // Form Submit (Store / Update)
    jQuery('#commonJobCardForm').on('submit', function (e) {
        e.preventDefault();
        var form = this;

        if (!form.checkValidity()) {
            e.stopPropagation();
            jQuery(form).addClass('was-validated');
            return;
        }

        var jcDate = (jQuery('#job_card_date').val() || '').trim();
        if (!jcDate || (typeof isValidDate === 'function' && !isValidDate(jcDate))) {
            toastr.error('Please Enter A Valid Job Card Date!');
            jQuery('#job_card_date').focus();
            return;
        }
        if (typeof checkDate === 'function' && checkDate(jcDate) == 'no') {
            toastr.error('Enter Date within Current Financial Year Selected.');
            jQuery('#job_card_date').focus();
            return;
        }

        var activeCount = 0;
        var missingPriority = false;
        var detailsArray = [];
        var $firstMissing = null;

        jQuery('#JobCardWorksheetTable tbody .invalid-tooltip').css('display', '');
        jQuery('#JobCardWorksheetTable tbody .worksheet-priority-select').removeClass('is-invalid');

        selectedWorksheets.forEach(function (val, key) {
            if (val.mode !== 'Delete') {
                activeCount++;
                if (!val.priority_id_fix || val.priority_id_fix === '') {
                    missingPriority = true;
                    var $row = jQuery('#JobCardWorksheetTable tbody .worksheet-row-check[value="' + key + '"]').closest('tr');
                    if ($row.length) {
                        var $sel = $row.find('.worksheet-priority-select');
                        $sel.addClass('is-invalid');
                        $row.find('.invalid-tooltip').css('display', 'block');
                        if (!$firstMissing) {
                            $firstMissing = $sel;
                        }
                    }
                }
            }
            detailsArray.push(val);
        });

        if (activeCount === 0) {
            toastr.error('Please Select At Least One Worksheet.');
            return;
        }

        if (missingPriority) {
            if ($firstMissing && $firstMissing.length) {
                setTimeout(function () {
                    $firstMissing.next('.select2-container').find('.select2-selection').focus();
                }, 50);
            }
            return;
        }

        jQuery('#submitbtn').prop('disabled', true);

        var priorityOrderMap = { '1st': 1, '2nd': 2, '3rd': 3, '4th': 4 };
        detailsArray.sort(function (a, b) {
            var pA = priorityOrderMap[a.priority_id_fix] || 999;
            var pB = priorityOrderMap[b.priority_id_fix] || 999;
            return pA - pB;
        });

        var formId = jQuery(form).find('#id').val();
        var formUrl = formId ? 'update-job_card' : 'store-job_card';

        var formData = new FormData(form);
        formData.append('details', JSON.stringify(detailsArray));

        jQuery.ajax({
            url: formUrl,
            type: 'POST',
            data: formData,
            headers: headerOpt,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (data) {
                jQuery('#submitbtn').prop('disabled', false);
                if (data.response_code == 1) {
                    if (!formId) {
                        toastSuccess(data.response_message, function () {
                            initJobCardForm();
                            if (typeof table !== 'undefined') {
                                table.draw(false);
                            }
                        });
                    } else {
                        toastSuccess(data.response_message, function () {
                            jQuery('#JobCardModal').modal('hide');
                            initJobCardForm();
                            if (typeof table !== 'undefined') {
                                table.draw(false);
                            }
                        });
                    }
                } else {
                    toastr.error(data.response_message || 'Error occurred while saving.');
                    if (data.response_message && (data.response_message.indexOf('Used') !== -1 || data.response_message.indexOf('no longer pending') !== -1 || data.response_message.indexOf('already assigned') !== -1)) {
                        loadPendingWorksheets(formId);
                    }
                }
            },
            error: function (jqXHR) {
                jQuery('#submitbtn').prop('disabled', false);
                toastr.error('Something went wrong!');
            }
        });
    });

    // Modal Hide & Hidden Events
    jQuery('#JobCardModal').on('hide.bs.modal', function () {
        window.jobCardWsNoSearchTerm = '';
        jQuery('#JobCardModal input.col-search').val('');
    });

    jQuery('#JobCardModal').on('hidden.bs.modal', function (e) {
        if (e.target !== this) return;

        if (pendingWorksheetsXhr) {
            pendingWorksheetsXhr.abort();
            pendingWorksheetsXhr = null;
        }

        var form = document.getElementById('commonJobCardForm');
        if (form) {
            form.reset();
            jQuery(form).removeClass('was-validated');
        }

        var $form = jQuery('#commonJobCardForm');
        $form.find('#id').val('');
        $form.removeData('orig-seq');
        $form.removeData('orig-no');
        jQuery('.priority-error').remove();
        jQuery('#submitbtn').text('Submit');
        jQuery('#add_new').hide();

        // Reset select2 dropdowns
        jQuery('#shift_id, #incharge_authority_person_id, #radiographer_authority_person_id, #rt_camera_id').val('').trigger('change');

        selectedWorksheets.clear();
        allPendingWorksheets = [];
        window.jobCardWsNoSearchTerm = '';
        jQuery('#JobCardModal input.col-search').val('');
        jQuery('#checkall_worksheet').prop('checked', false);

        var $table = jQuery('#JobCardWorksheetTable');
        if (jQuery.fn.DataTable.isDataTable($table)) {
            $table.DataTable().clear().destroy();
        }
        jQuery('#JobCardModal .dataTables_scrollHead').remove();
        $table.find('thead').html(originalJobCardTheadHtml);
        $table.find('tbody').empty().html('<tr><td colspan="11" class="text-center" id="noDetails">No pending worksheets found.</td></tr>');
    });
});

/**
 * Ensure option exists in select element (e.g. for Inactive masters)
 */
function ensureSelectOption($select, val, text) {
    if (val && $select.find('option[value="' + val + '"]').length === 0) {
        var newOpt = new Option(text || val, val, true, true);
        $select.append(newOpt);
    }
}

/**
 * Sync checkbox and priority states from selectedWorksheets Map across pagination & search
 */
function syncWorksheetRowsState() {
    jQuery('#JobCardWorksheetTable tbody tr').each(function () {
        var $row = jQuery(this);
        var $chk = $row.find('.worksheet-row-check');
        if ($chk.length) {
            var detailId = $chk.val();
            var $priority = $row.find('.worksheet-priority-select');
            var item = selectedWorksheets.get(detailId.toString());
            var isItemActive = item && item.mode !== 'Delete';
            if (isItemActive) {
                $chk.prop('checked', true);
                $priority.prop('disabled', false).prop('required', true);
                if (item.priority_id_fix) {
                    $priority.val(item.priority_id_fix);
                    $priority.removeClass('is-invalid');
                    $row.find('.invalid-tooltip').css('display', '');
                    var priorityOrderMap = { '1st': 1, '2nd': 2, '3rd': 3, '4th': 4 };
                    $priority.closest('td').attr('data-order', priorityOrderMap[item.priority_id_fix] || 99);
                }
            } else {
                $chk.prop('checked', false);
                $priority.prop('disabled', true).prop('required', false);
                $priority.val('');
                $priority.removeClass('is-invalid');
                $row.find('.invalid-tooltip').css('display', '');
                $priority.closest('td').attr('data-order', 99);
            }
        }
    });

    var activeCount = 0;
    selectedWorksheets.forEach(function (val) {
        if (val.mode !== 'Delete') activeCount++;
    });

    if (allPendingWorksheets.length > 0 && activeCount === allPendingWorksheets.length) {
        jQuery('#checkall_worksheet').prop('checked', true);
    } else {
        jQuery('#checkall_worksheet').prop('checked', false);
    }

    // Initialize select2 on visible rows
    jQuery('#JobCardWorksheetTable tbody .worksheet-priority-select').each(function () {
        var $s = jQuery(this);
        if (!$s.data('select2')) {
            $s.select2({
                dropdownParent: jQuery('#JobCardModal'),
                width: '100%'
            });
        }
    });
}

/**
 * Disable tab index on DataTable pagination, column search, and checkall
 */
function syncJobCardTableTabindex() {
    jQuery('#JobCardModal .dataTables_length select').attr('tabindex', '-1');
    jQuery('#JobCardModal input.col-search').attr('tabindex', '-1');
    jQuery('#JobCardModal #checkall_worksheet').attr('tabindex', '-1');
    jQuery('#JobCardModal #JobCardWorksheetTable thead th').attr('tabindex', '-1');
    jQuery('#JobCardModal .dataTables_paginate, #JobCardModal .dataTables_paginate *').attr('tabindex', '-1');
}

/**
 * Fetch latest Job Card number & sequence
 */
function fetchLatestJobCardNumber() {
    jQuery('#submitbtn').prop('disabled', true);
    return jQuery.ajax({
        url: 'get-latest-job_card-number',
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#job_card_no').val(data.latest_no);
                jQuery('#job_card_sequence').val(data.number);
            }
            jQuery('#submitbtn').prop('disabled', false);
            setTimeout(function () {
                jQuery('#job_card_sequence').focus().select();
            }, 100);
        },
        error: function () {
            jQuery('#submitbtn').prop('disabled', false);
        }
    });
}

/**
 * Initialize / Reset form for fresh entry
 */
function initJobCardForm() {
    var form = document.getElementById('commonJobCardForm');
    if (form) {
        form.reset();
        jQuery(form).removeClass('was-validated');
    }

    var $form = jQuery('#commonJobCardForm');
    $form.find('#id').val('');
    $form.removeData('orig-seq');
    $form.removeData('orig-no');
    jQuery('#JobCardWorksheetTable tbody .invalid-tooltip').css('display', '');
    jQuery('#JobCardWorksheetTable tbody .worksheet-priority-select').removeClass('is-invalid');
    jQuery('#submitbtn').text('Submit');
    jQuery('#add_new').hide();

    var dateVal = (typeof currentDate !== 'undefined' && currentDate) ? currentDate : '';
    jQuery('#job_card_date').val(dateVal);

    // Reset select2 dropdowns
    jQuery('#shift_id, #incharge_authority_person_id, #radiographer_authority_person_id, #rt_camera_id').val('').trigger('change');

    selectedWorksheets.clear();
    allPendingWorksheets = [];
    window.jobCardWsNoSearchTerm = '';
    jQuery('#JobCardModal input.col-search').val('');
    jQuery('#checkall_worksheet').prop('checked', false);

    // Fetch Latest Sequence
    fetchLatestJobCardNumber();
    loadPendingWorksheets();
}

/**
 * Reset form action
 */
function resetJobCardForm() {
    var formId = jQuery('#commonJobCardForm').find('#id').val();
    window.jobCardWsNoSearchTerm = '';
    jQuery('#JobCardModal input.col-search').val('');
    if (!formId) {
        initJobCardForm();
    } else {
        // If edit mode, reload original
        jQuery('#dyntable tbody').find('.edit-job_card[data-id="' + formId + '"]').trigger('click');
        setTimeout(function () {
            if (jQuery.fn.DataTable.isDataTable('#JobCardWorksheetTable')) {
                jQuery('#JobCardWorksheetTable').DataTable().columns.adjust();
            }
        }, 300);
    }
}

/**
 * Load Worksheets into Grid Table
 */
function loadPendingWorksheets(jobCardId) {
    if (pendingWorksheetsXhr) {
        pendingWorksheetsXhr.abort();
        pendingWorksheetsXhr = null;
    }

    var $table = jQuery('#JobCardWorksheetTable');
    jQuery('#JobCardWorksheetTable .worksheet-priority-select').each(function () {
        if (jQuery(this).data('select2')) {
            jQuery(this).select2('destroy');
        }
    });

    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().clear().destroy();
    }

    jQuery('#JobCardModal .dataTables_scrollHead').remove();
    $table.find('thead').html(originalJobCardTheadHtml);

    var tbody = jQuery('#JobCardWorksheetTable tbody');
    tbody.html('<tr><td colspan="11" class="text-center"></td></tr>');

    pendingWorksheetsXhr = jQuery.ajax({
        url: 'get-pending-worksheets-for-job_card',
        type: 'GET',
        data: { job_card_id: jobCardId || '' },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            pendingWorksheetsXhr = null;
            tbody.empty();
            if (data.response_code == 1 && data.worksheets && data.worksheets.length > 0) {
                allPendingWorksheets = data.worksheets;
                var html = '';
                allPendingWorksheets.forEach(function (ws) {
                    var detailId = ws.observation_sheet_details_id;
                    var isSelected = selectedWorksheets.has(detailId.toString())
                        ? (selectedWorksheets.get(detailId.toString()).mode !== 'Delete')
                        : (ws.is_selected == 1);

                    var priorityVal = '';
                    var jobCardDetailId = ws.job_card_detail_id || 0;
                    if (selectedWorksheets.has(detailId.toString())) {
                        var sw = selectedWorksheets.get(detailId.toString());
                        priorityVal = (sw.mode !== 'Delete') ? (sw.priority_id_fix || '') : '';
                        if (sw.job_card_detail_id) {
                            jobCardDetailId = sw.job_card_detail_id;
                        }
                    } else if (ws.priority_id_fix && ws.is_selected == 1) {
                        priorityVal = ws.priority_id_fix;
                    }

                    if (isSelected) {
                        selectedWorksheets.set(detailId.toString(), {
                            job_card_detail_id: jobCardDetailId,
                            observation_sheet_details_id: detailId,
                            priority_id_fix: priorityVal,
                            mode: (jobCardDetailId ? 'Update' : 'Insert')
                        });
                    }

                    var checkedAttr = isSelected ? 'checked' : '';
                    var disabledAttr = isSelected ? '' : 'disabled';
                    var requiredAttr = isSelected ? 'required' : '';
                    var priorityOrderMap = { '1st': 1, '2nd': 2, '3rd': 3, '4th': 4 };
                    var orderVal = priorityOrderMap[priorityVal] || 99;

                    html += `<tr>
                        <td class="text-center">
                            <input type="checkbox" class="simple-check worksheet-row-check" value="${detailId}" data-detail-id="${jobCardDetailId}" ${checkedAttr} />
                        </td>
                        <td class="position-relative" data-order="${orderVal}">
                            <select class="form-select form-select-sm js-example-basic-single worksheet-priority-select" data-id="${detailId}" ${disabledAttr} ${requiredAttr} style="width: 100%;">
                                <option value="">Select Priority</option>
                                <option value="1st" ${priorityVal === '1st' ? 'selected' : ''}>1st</option>
                                <option value="2nd" ${priorityVal === '2nd' ? 'selected' : ''}>2nd</option>
                                <option value="3rd" ${priorityVal === '3rd' ? 'selected' : ''}>3rd</option>
                                <option value="4th" ${priorityVal === '4th' ? 'selected' : ''}>4th</option>
                            </select>
                            <div class="invalid-tooltip" style="white-space: nowrap;">
                                Select Priority.
                            </div>
                        </td>
                        <td>${ws.worksheet_no || ''}</td>
                        <td>${ws.worksheet_date || ''}</td>
                        <td>${ws.customer_name || ''}</td>
                        <td>${ws.job_desc || ''}</td>
                        <td>${ws.die_no || ''}</td>
                        <td>${ws.drg_no || ''}</td>
                        <td>${ws.heat_no || ''}</td>
                        <td>${ws.material || ''}</td>
                        <td>${ws.quantity !== null && ws.quantity !== undefined ? ws.quantity : ''}</td>
                    </tr>`;
                });
                tbody.html(html);

                // Initialize DataTable
                var dt = $table.DataTable({
                    paging: true,
                    searching: true,
                    pageLength: 25,
                    dom: 'lrtip',
                    sScrollX: true,
                    sScrollX: "100%",
                    bScrollCollapse: true,
                    order: [[1, 'asc']],
                    columnDefs: [
                        { orderable: false, targets: [0] },
                        { orderable: true, targets: [1] },
                        { width: "120px", targets: 1 }
                    ]
                });

                if (typeof fixDataTableColumnsUntilAdjusted === 'function') {
                    fixDataTableColumnsUntilAdjusted(dt);
                }

                setTimeout(function () {
                    if (jQuery.fn.DataTable.isDataTable($table)) {
                        $table.DataTable().columns.adjust();
                    }
                }, 100);

                dt.on('draw', function () {
                    syncWorksheetRowsState();
                    syncJobCardTableTabindex();
                });

                syncWorksheetRowsState();
                syncJobCardTableTabindex();
            } else {
                allPendingWorksheets = [];
                $table.find('thead tr.search-row').remove();
                jQuery('#JobCardModal .dataTables_scrollHead thead tr.search-row').remove();
                tbody.html('<tr><td colspan="11" class="text-center" id="noDetails">No pending worksheets found.</td></tr>');
            }
        },
        error: function (jqXHR, textStatus) {
            pendingWorksheetsXhr = null;
            if (textStatus === 'abort') return;
            allPendingWorksheets = [];
            $table.find('thead tr.search-row').remove();
            jQuery('#JobCardModal .dataTables_scrollHead thead tr.search-row').remove();
            tbody.html('<tr><td colspan="11" class="text-center">Failed to load worksheets.</td></tr>');
        }
    });
}
