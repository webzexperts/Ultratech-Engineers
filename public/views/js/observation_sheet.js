var observation_sheet_details_data = [];
var availablePendingInwards = [];
var availablePendingTechniques = [];
var _isEditMode = false;
var currentEditId = null;
var originalObsDetailRowBackup = null;
var isObsDetailSaved = false;

function isSamePendingRecord(d, item) {
    let dInward = parseInt(d.material_inward_details_id || 0);
    let itemInward = parseInt(item.material_inward_details_id || 0);
    let dRt = parseInt(d.test_report_rt_id || 0);
    let itemRt = parseInt(item.test_report_rt_id || 0);
    let dFrom = parseInt(d.from_type_id_fix || 1);
    let itemFrom = parseInt(item.from_type_id_fix || 1);
    return (dInward === itemInward) && (dRt === itemRt) && (dFrom === itemFrom);
}

function setFilmSizeRadioReadonly(isReadonly) {
    let $radios = jQuery('input[name="obs_film_size_unit_fix"]');
    $radios.prop('disabled', false);
    if (isReadonly) {
        $radios.addClass('readonly-radio');
        $radios.parent().css({
            'opacity': '1',
            'pointer-events': 'none'
        });
    } else {
        $radios.removeClass('readonly-radio');
        $radios.parent().css({
            'opacity': '1',
            'pointer-events': 'auto'
        });
    }
}

function updateObsFilmSizeDropdown() {
    let detailIdx = jQuery('#detail_index').val();
    let row = (detailIdx !== '' && observation_sheet_details_data[detailIdx]) ? observation_sheet_details_data[detailIdx] : null;
    let film_size_fix = (row && row.film_size_unit_fix) ? row.film_size_unit_fix : (jQuery('input[name="obs_film_size_unit_fix"]:checked').val() || 'inch');

    jQuery('#sub_film_id option').each(function () {
        let opt = jQuery(this);
        let val = opt.val();
        if (!val) return;
        let inchVal = opt.attr('data-inch') || opt.data('inch') || '';
        let cmVal = opt.attr('data-cm') || opt.data('cm') || '';
        if (film_size_fix === 'inch') {
            opt.text(inchVal);
        } else {
            opt.text(cmVal);
        }
    });
    if (jQuery('#sub_film_id').data('select2')) {
        jQuery('#sub_film_id').trigger('change.select2');
    }
}

jQuery(document).ready(function () {
    // Select2
    jQuery('#ObservationSheetModal #customer_id').select2({
        dropdownParent: jQuery('#ObservationSheetModal')
    });
    jQuery('#ObservationSheetModal #prepared_by_user_id').select2({
        dropdownParent: jQuery('#ObservationSheetModal')
    });

    // Open Pending Inward Modal
    jQuery(document).on('click', '#load_pending_reports', function () {
        let custId = jQuery('#customer_id').val();
        if (custId) {
            fetchCustomerPendingInwards(custId).done(function () {
                jQuery('#PendingInwardForObservationSheetModal').modal('show');
            });
        }
    });

    jQuery('#PendingInwardForObservationSheetModal').on('hidden.bs.modal', function () {
        if (jQuery('#ObservationSheetModal').hasClass('show')) {
            jQuery('body').addClass('modal-open');
        }
    });

    jQuery('#ObservationSheetDetailEditModal').on('shown.bs.modal', function () {
        let $table = jQuery('#ObservationSheetSubDetailTable');
        if (jQuery.fn.DataTable.isDataTable($table)) {
            $table.DataTable().columns.adjust();
        }
    });

    jQuery('#ObservationSheetDetailEditModal').on('hidden.bs.modal', function () {
        let indx = jQuery('#detail_index').val();
        if (!isObsDetailSaved && originalObsDetailRowBackup !== null && indx !== '' && observation_sheet_details_data[indx]) {
            observation_sheet_details_data[indx] = originalObsDetailRowBackup;
            renderObservationSheetDetailsTable();
        }
        originalObsDetailRowBackup = null;
        isObsDetailSaved = false;

        if (jQuery('#ObservationSheetModal').hasClass('show')) {
            jQuery('body').addClass('modal-open');
        }
    });

    jQuery('#PendingTechniqueSheetRtModal').on('shown.bs.modal', function () {
        let $table = jQuery('#PendingTechniqueSheetTable');
        if (jQuery.fn.DataTable.isDataTable($table)) {
            $table.DataTable().columns.adjust();
        }
    });

    jQuery('#PendingTechniqueSheetRtModal').on('hidden.bs.modal', function () {
        if (jQuery('#ObservationSheetDetailEditModal').hasClass('show')) {
            jQuery('body').addClass('modal-open');
        }
    });

    jQuery('#PendingOldRtReportModal').on('shown.bs.modal', function () {
        let $table = jQuery('#PendingOldRtReportTable');
        if (jQuery.fn.DataTable.isDataTable($table)) {
            $table.DataTable().columns.adjust();
        }
    });

    jQuery('#PendingOldRtReportModal').on('hidden.bs.modal', function () {
        if (jQuery('#ObservationSheetDetailEditModal').hasClass('show')) {
            jQuery('body').addClass('modal-open');
        }
    });

    jQuery('#ObservationSheetSubDetailModal').on('hidden.bs.modal', function () {
        if (jQuery('#ObservationSheetDetailEditModal').hasClass('show')) {
            jQuery('body').addClass('modal-open');
        }
    });

    // Intercept clicks and keydown events for custom readonly radio buttons
    jQuery(document).on('click keydown', 'input[type="radio"].readonly-radio', function (e) {
        e.preventDefault();
        return false;
    });

    // Film Size Unit Radio Change
    jQuery(document).on('change', 'input[name="obs_film_size_unit_fix"]', function () {
        let val = jQuery(this).val();
        let detailIdx = jQuery('#detail_index').val();
        if (detailIdx !== '' && observation_sheet_details_data[detailIdx]) {
            observation_sheet_details_data[detailIdx].film_size_unit_fix = val;
            renderObservationSheetSubDetailsTable(detailIdx);
        }
        updateObsFilmSizeDropdown();
    });

    // Customer change
    jQuery('#customer_id').on('change', function () {
        let custId = jQuery(this).val();
        if (custId) {
            jQuery('#load_pending_reports').prop('disabled', _isEditMode ? true : false);
            fetchCustomerPendingInwards(custId);
        } else {
            jQuery('#load_pending_reports').prop('disabled', true);
            availablePendingInwards = [];
            renderPendingInwardModalTable();
        }
    });

    // Select all pending checkbox
    jQuery(document).on('change', '#select_all_pending_obs', function () {
        let isChecked = jQuery(this).is(':checked');
        jQuery('.pending-obs-checkbox').prop('checked', isChecked);
    });

    // Submit pending items from modal
    jQuery(document).on('click', '#submitPendingObsBtn', function () {
        let checkedIndexes = [];
        jQuery('.pending-obs-checkbox:checked').each(function () {
            checkedIndexes.push(jQuery(this).data('index'));
        });

        if (checkedIndexes.length === 0) {
            toastr.error('Please Select At Least One Pending Record');
            return;
        }

        // 1. Process checked items
        checkedIndexes.forEach(function (index) {
            let item = availablePendingInwards[index];
            if (item) {
                let existing = observation_sheet_details_data.find(d => d.mode !== 'Delete' && isSamePendingRecord(d, item));
                if (!existing) {
                    let deletedExisting = observation_sheet_details_data.find(d => d.mode === 'Delete' && isSamePendingRecord(d, item));
                    if (deletedExisting) {
                        deletedExisting.mode = 'Update';
                        deletedExisting.quantity = item.pending_qty || item.quantity || 1;
                    } else {
                        let newIdx = observation_sheet_details_data.length;
                        observation_sheet_details_data.push({
                            material_inward_details_id: item.material_inward_details_id || null,
                            test_report_rt_id: (item.test_report_rt_id && parseInt(item.test_report_rt_id) > 0) ? parseInt(item.test_report_rt_id) : null,
                            from_type_id_fix: parseInt(item.from_type_id_fix || 1),
                            type_of_testing_id_fix: item.type_of_testing_id_fix || 'RT',
                            process_type: item.process_type || 'Fresh',
                            is_observation_sheet: 'Yes',
                            type_of_job_id: item.type_of_job_id || null,
                            job_desc_id: item.job_desc_id || null,
                            part_id: item.part_id || null,
                            part_no: item.part_no || '',
                            drg_no: item.drg_no || '',
                            material_id: item.material_id || null,
                            material: item.material || '',
                            heat_no: item.heat_no || '',
                            rt_no: item.rt_no || '',
                            revision_number: item.revision_number || '',
                            product_code: item.product_code || '',
                            thickness: item.thickness || null,
                            area_of_coverage_id: item.area_of_coverage_id || null,
                            procedure_ref_id: item.procedure_ref_id || null,
                            evaluation_as_per_id: item.evaluation_as_per_id || null,
                            acceptance_standard_id: item.acceptance_standard_id || null,
                            quantity: item.pending_qty || item.quantity || 1,
                            inward_qty: item.inward_qty || item.quantity || 1,
                            // remark: item.remark || '',
                            inward_no: item.material_inward_no || '',
                            inward_date: item.material_inward_date || '',
                            nabl_type_fix: item.nabl_type_fix || 'Non NABL',
                            test_at_fix: item.test_at_fix || 'At Lab',
                            job_type_fix: item.job_type_fix || '',
                            dc_no: item.dc_no || '',
                            dc_date: item.dc_date || '',
                            po_no: item.po_no || '',
                            po_date: item.po_date || '',
                            type_of_job: item.type_of_job || '',
                            job_description: item.job_description || '',
                            sub_details: [],
                            mode: 'Insert'
                        });

                        let isRepair = ((item.process_type || '') === 'Repair' || (item.test_report_rt_id && parseInt(item.test_report_rt_id) > 0));
                        let hasReportId = (item.test_report_rt_id && parseInt(item.test_report_rt_id) > 0);
                        if (isRepair && hasReportId) {
                            fetchOldRtReportDetailsForRepair(item.test_report_rt_id, newIdx);
                        }
                    }
                }
            }
        });

        // 2. Remove/Delete unchecked items
        availablePendingInwards.forEach(function (item, index) {
            if (!checkedIndexes.includes(index)) {
                let matchIdx = observation_sheet_details_data.findIndex(d => d.mode !== 'Delete' && isSamePendingRecord(d, item));
                if (matchIdx !== -1) {
                    let matchedRow = observation_sheet_details_data[matchIdx];
                    if (matchedRow.observation_sheet_details_id) {
                        matchedRow.mode = 'Delete';
                    } else {
                        observation_sheet_details_data.splice(matchIdx, 1);
                    }
                }
            }
        });

        renderObservationSheetDetailsTable();
        jQuery('#PendingInwardForObservationSheetModal').modal('hide');

        if (observation_sheet_details_data.some(d => d.mode !== 'Delete')) {
            setSelect2Readonly('#customer_id', true);
        } else {
            setSelect2Readonly('#customer_id', false);
        }
    });

    // Pending button handler is now dynamically bound in editObservationDetailRow

    // Submit Technique Sheet from Radio Button Selection
    jQuery(document).on('click', '#submitPendingTechniqueBtn', function () {
        let selectedRadio = jQuery('.pending-technique-radio:checked');
        if (selectedRadio.length === 0) {
            toastr.error('Please Select A Technique Sheet');
            return;
        }

        let techId = selectedRadio.data('id');
        let techNo = selectedRadio.data('no');
        let detailIdx = jQuery('#detail_index').val();

        if (detailIdx !== '' && observation_sheet_details_data[detailIdx]) {
            let row = observation_sheet_details_data[detailIdx];
            row.rt_no = techNo;
            jQuery('#report_no').val(techNo);

            skipLoader = false;
            if (typeof showLoader === 'function') showLoader();

            jQuery.ajax({
                url: 'get-technique-sheet-details',
                type: 'GET',
                data: { id: techId },
                headers: headerOpt,
                success: function (res) {
                    if (res.sheet) {
                        row.technique_sheet_rt_id = res.sheet.technique_sheet_rt_id;
                        if (res.sheet.thickness) row.thickness = res.sheet.thickness;
                        if (res.sheet.part_no) row.part_no = res.sheet.part_no;
                        if (res.sheet.drg_no) row.drg_no = res.sheet.drg_no;
                        if (res.sheet.film_size_fix || res.sheet.film_size_unit_fix) {
                            row.film_size_unit_fix = res.sheet.film_size_fix || res.sheet.film_size_unit_fix;
                        }
                        let unitFix = row.film_size_unit_fix || 'inch';
                        if (unitFix === 'cm') {
                            jQuery('#obs_film_size_cm').prop('checked', true);
                        } else {
                            jQuery('#obs_film_size_inch').prop('checked', true);
                        }
                        updateObsFilmSizeDropdown();
                        jQuery('#thickness').val(row.thickness || '');
                        jQuery('#die_no').val(row.part_no || '');
                        jQuery('#drg_no').val(row.drg_no || '');
                    }

                    if (res.details && res.details.length > 0) {
                        let filmSelect = jQuery('#sub_film_id');
                        row.sub_details = res.details.map((d, sIdx) => {
                            let cleanSrNo = d.sr_no ? (!isNaN(d.sr_no) ? parseInt(d.sr_no, 10) : d.sr_no) : (sIdx + 1);
                            let cleanFilmId = d.film_id || '';
                            let isFilmActive = cleanFilmId && filmSelect.find('option[value="' + cleanFilmId + '"]:not(.temp-option)').length > 0;

                            return {
                                sr_no: cleanSrNo,
                                identification: d.identification || '',
                                location: d.location || '',
                                source_id_fix: d.source_id_fix || '',
                                film_brand_id: d.film_brand_id || '',
                                film_brand: d.film_brand || '',
                                film_type_id: d.film_type_id || '',
                                film_type: d.film_type || '',
                                thickness: d.thickness || '',
                                sfd: d.sfd || '',
                                // iqi_designation_id: d.iqi_designation_id || '',
                                // iqi_sensitivity_id: d.iqi_sensitivity_id || '',
                                iqi_designation_id: null,
                                iqi_designation: d.iqi_designation || d.iqi_designation_name || d.iqi || '',
                                iqi: d.iqi || d.iqi_designation || d.iqi_designation_name || '',
                                iqi_sensitivity_id: null,
                                iqi_sensitivity: d.iqi_sensitivity || d.iqi_sensitivity_name || d.sensitivity || '',
                                sensitivity: d.sensitivity || d.iqi_sensitivity || d.iqi_sensitivity_name || '',
                                film_id: isFilmActive ? cleanFilmId : '',
                                film_size_inch: isFilmActive ? (d.film_size_inch || '') : '',
                                film_size_cm: isFilmActive ? (d.film_size_cm || '') : '',
                                film_size: isFilmActive ? ((row.film_size_unit_fix === 'cm' ? (d.film_size_cm || d.film_size_inch || d.film_size) : (d.film_size_inch || d.film_size_cm || d.film_size)) || '') : '',
                                no_of_film_fix: d.no_of_film_fix || 'Single',
                                film_qty: d.film_qty ? parseInt(d.film_qty, 10) : 1
                            };
                        });
                        jQuery('#add_sub_detail_btn').prop('disabled', true);
                        setFilmSizeRadioReadonly(true);
                    }

                    renderObservationSheetSubDetailsTable(detailIdx);
                    jQuery('#PendingTechniqueSheetRtModal').modal('hide');
                },
                complete: function () {
                    if (typeof hideLoader === 'function') hideLoader();
                }
            });
        }
    });

    // Save edited detail row
    jQuery('#saveObsDetailEditBtn').on('click', function () {
        let indx = jQuery('#detail_index').val();
        if (indx !== '' && observation_sheet_details_data[indx]) {
            let row = observation_sheet_details_data[indx];

            if (!row.sub_details || row.sub_details.length === 0) {
                toastr.error('Please Add At Least One Sub Detail Row');
                return;
            }

            // Check required fields in each sub-detail row
            for (let i = 0; i < row.sub_details.length; i++) {
                let s = row.sub_details[i];
                let missing = [];
                if (!s.location) missing.push('Location');
                if (!s.source_id_fix && !s.source) missing.push('Source');
                if (!s.film_brand_id && !s.film_brand) missing.push('Film Brand');
                if (!s.thickness && s.thickness !== 0) missing.push('Thickness');
                if (!s.sfd && s.sfd !== 0) missing.push('SFD');
                if (!s.iqi_designation_id && !s.iqi && !s.iqi_designation) missing.push('IQI Designation');
                if (!s.iqi_sensitivity_id && !s.sensitivity && !s.iqi_sensitivity) missing.push('IQI Sensitivity');
                if (!s.film_id && !s.film_size) missing.push('Film Size');

                if (missing.length > 0) {
                    toastr.error('Please Select Film Size');
                    return;
                }
            }

            row.dc_no = jQuery('#dc_no').val();
            row.rt_no = jQuery('#report_no').val();
            row.revision_number = jQuery('#revision_number').val();
            row.sr_id_no = jQuery('#sr_id_no').val();
            row.job_description = jQuery('#job_description').val();
            row.part_no = jQuery('#die_no').val();
            row.heat_no = jQuery('#heat_no').val();
            row.drg_no = jQuery('#drg_no').val();
            row.thickness = jQuery('#thickness').val();
            row.material = jQuery('#material').val();
            row.film_size_unit_fix = jQuery('input[name="obs_film_size_unit_fix"]:checked').val() || 'inch';

            // row.remark = jQuery('#remark').val();

            if (row.mode !== 'Insert') {
                row.mode = 'Update';
            }
            isObsDetailSaved = true;
            originalObsDetailRowBackup = null;
            renderObservationSheetDetailsTable();
            jQuery('#ObservationSheetDetailEditModal').modal('hide');
        }
    });

    function getNextObsSubDetailSrNo(detailIdx) {
        let row = observation_sheet_details_data[detailIdx];
        if (!row || !row.sub_details) return 1;
        let activeRows = row.sub_details.filter(s => s.mode !== 'Delete');
        if (activeRows.length === 0) return 1;
        let maxSr = Math.max(...activeRows.map(s => parseFloat(s.sr_no || 0)));
        return Math.floor(maxSr) + 1;
    }

    // Add Sub Detail button click
    jQuery('#add_sub_detail_btn').on('click', function () {
        setFilmSizeRadioReadonly(false);
        jQuery('#ObservationSheetSubDetailForm')[0].reset();
        jQuery('#ObservationSheetSubDetailForm').removeClass('was-validated');
        jQuery('#sub_source_id_fix').val('').trigger('change').trigger('change.select2');
        jQuery('#sub_film_brand_id').val('').trigger('change').trigger('change.select2');
        jQuery('#sub_film_type_id').val('').trigger('change').trigger('change.select2');
        // jQuery('#sub_iqi_designation_id').val('').trigger('change').trigger('change.select2');
        // jQuery('#sub_iqi_sensitivity_id').val('').trigger('change').trigger('change.select2');
        jQuery('#sub_iqi_designation').val('');
        jQuery('#sub_iqi_sensitivity').val('');
        jQuery('#sub_film_id').val('').trigger('change').trigger('change.select2');
        jQuery('#sub_no_of_film_fix').val('Single').trigger('change').trigger('change.select2');
        jQuery('#sub_detail_index').val('');
        let detailIdx = jQuery('#detail_index').val();
        let nextSrNo = getNextObsSubDetailSrNo(detailIdx);
        jQuery('#sub_sr_no').val(nextSrNo);
        jQuery('#saveObsSubDetailBtn').text('Add');
        updateObsFilmSizeDropdown();
        jQuery('#ObservationSheetSubDetailModal').modal('show');
    });

    // Save Sub Detail row
    jQuery('#saveObsSubDetailBtn').on('click', function (e) {
        let form = document.getElementById('ObservationSheetSubDetailForm');
        if (form && !form.checkValidity()) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            jQuery(form).addClass('was-validated');
            return;
        }
        jQuery(form).removeClass('was-validated');

        let detailIdx = jQuery('#detail_index').val();
        let subIdx = jQuery('#sub_detail_index').val();

        if (detailIdx !== '' && observation_sheet_details_data[detailIdx]) {
            let row = observation_sheet_details_data[detailIdx];
            if (!row.sub_details) row.sub_details = [];

            let rawSrNo = jQuery('#sub_sr_no').val();
            let parsedSrNo = parseFloat(rawSrNo);
            let finalSrNo = (!isNaN(parsedSrNo) && rawSrNo !== '') ? (parsedSrNo === parseInt(parsedSrNo) ? parseInt(parsedSrNo) : parsedSrNo) : rawSrNo;

            let optSelected = jQuery('#sub_film_id option:selected');
            let inchVal = optSelected.attr('data-inch') || optSelected.data('inch') || '';
            let cmVal = optSelected.attr('data-cm') || optSelected.data('cm') || '';
            let currentUnit = (row.film_size_unit_fix || jQuery('input[name="obs_film_size_unit_fix"]:checked').val() || 'inch');
            let filmSizeVal = optSelected.val() ? (currentUnit === 'cm' ? (cmVal || optSelected.text().trim()) : (inchVal || optSelected.text().trim())) : '';

            let subItem = {
                sr_no: finalSrNo,
                identification: jQuery('#sub_identification').val(),
                thickness: jQuery('#sub_thickness').val(),
                sfd: jQuery('#sub_sfd').val(),
                location: jQuery('#sub_location').val(),
                source_id_fix: jQuery('#sub_source_id_fix').val(),
                source: jQuery('#sub_source_id_fix option:selected').val() ? jQuery('#sub_source_id_fix option:selected').text().trim() : '',
                film_brand_id: jQuery('#sub_film_brand_id').val(),
                film_brand: jQuery('#sub_film_brand_id option:selected').val() ? jQuery('#sub_film_brand_id option:selected').text().trim() : '',
                film_type_id: jQuery('#sub_film_type_id').val(),
                film_type: jQuery('#sub_film_type_id option:selected').val() ? jQuery('#sub_film_type_id option:selected').text().trim() : '',
                // iqi_designation_id: jQuery('#sub_iqi_designation_id').val(),
                // iqi_sensitivity_id: jQuery('#sub_iqi_sensitivity_id').val(),
                iqi_designation_id: null,
                iqi_designation: jQuery('#sub_iqi_designation').val().trim(),
                iqi_sensitivity_id: null,
                iqi_sensitivity: jQuery('#sub_iqi_sensitivity').val().trim(),
                film_id: jQuery('#sub_film_id').val(),
                film_size: filmSizeVal,
                film_size_inch: inchVal,
                film_size_cm: cmVal,
                no_of_film_fix: jQuery('#sub_no_of_film_fix').val() || 'Single',
                film_qty: (jQuery('#sub_no_of_film_fix').val() === 'Double' ? 2 : (jQuery('#sub_no_of_film_fix').val() === 'Triple' ? 3 : (jQuery('#sub_no_of_film_fix').val() === 'Quadra' ? 4 : 1)))
            };

            if (subIdx !== '') {
                let existingSub = row.sub_details[subIdx];
                if (existingSub) {
                    if (existingSub.observation_sheet_details_details_input_id) {
                        subItem.observation_sheet_details_details_input_id = existingSub.observation_sheet_details_details_input_id;
                    }
                    if (existingSub.mode) {
                        subItem.mode = existingSub.mode;
                    }
                }
                row.sub_details[subIdx] = subItem;
                renderObservationSheetSubDetailsTable(detailIdx);
                jQuery('#ObservationSheetSubDetailModal').modal('hide');
            } else {
                row.sub_details.push(subItem);
                renderObservationSheetSubDetailsTable(detailIdx);

                // Do not hide modal on add: keep entered values, increment sr_no and advance location for fast continuous entry!
                let currentSrNo = parseInt(subItem.sr_no) || 0;
                let maxSrNo = 0;
                row.sub_details.forEach(s => {
                    let n = parseInt(s.sr_no) || 0;
                    if (n > maxSrNo) maxSrNo = n;
                });
                let nextSrNo = Math.max(currentSrNo + 1, maxSrNo + 1);
                let nextLoc = checkValue(subItem.location);

                jQuery('#sub_sr_no').val(nextSrNo);
                if (nextLoc) {
                    jQuery('#sub_location').val(nextLoc);
                }
                setTimeout(() => {
                    jQuery('#sub_sr_no').focus().select();
                }, 100);
            }
        }
    });

    // Modal show lifecycle
    jQuery('#ObservationSheetModal').on('show.bs.modal', function () {
        let id = jQuery('#commonObservationSheetForm #id').val();
        if (!id) {
            _isEditMode = false;
            jQuery('#submitbtn').show();
            jQuery('#updatebtn').hide();
            jQuery('#add_new').hide();
            jQuery('#preview_btn').hide();
            loadCustomersList();
            getLatestObservationSheetSequence();
            if (typeof loginUserId !== 'undefined' && loginUserId) {
                jQuery('#prepared_by_user_id').val(loginUserId).trigger('change.select2');
            }
        }
    });

    jQuery('#ObservationSheetModal').on('shown.bs.modal', function () {
        setTimeout(function () {
            jQuery('#observation_sheet_sequence').focus().select();
        }, 150);
    });

    // Modal hide lifecycle
    jQuery('#ObservationSheetModal').on('hide.bs.modal', function () {
        resetObservationSheetForm();
    });

    // Reset button click
    jQuery('#resetbtn').on('click', function () {
        if (_isEditMode && currentEditId) {
            loadObservationSheetEditData(currentEditId);
        } else {
            resetObservationSheetForm();
            loadCustomersList();
            getLatestObservationSheetSequence();
            setTimeout(function () {
                jQuery('#observation_sheet_sequence').focus().select();
            }, 150);
        }
    });

    // Add New button click
    jQuery('#add_new').on('click', function () {
        resetObservationSheetForm();
        _isEditMode = false;
        loadCustomersList();
        jQuery('#submitbtn').show();
        jQuery('#updatebtn').hide();
        jQuery('#add_new').hide();
        jQuery('#preview_btn').hide();
        getLatestObservationSheetSequence();
        setTimeout(function () {
            jQuery('#observation_sheet_sequence').focus().select();
        }, 150);
    });

    // Form submit
    jQuery('#commonObservationSheetForm').on('submit', function (e) {
        e.preventDefault();
        let form = this;

        if (!form.checkValidity()) {
            e.stopPropagation();
            jQuery(form).addClass('was-validated');
            return;
        }

        let validDetails = observation_sheet_details_data.filter(row => row.mode !== 'Delete');
        if (!validDetails || validDetails.length === 0) {
            toastr.error('Please Select At Least One Pending Record');
            return;
        }

        // Validate that RT detail rows have sub-details entered (for both Fresh & Repair cases)
        for (let i = 0; i < validDetails.length; i++) {
            let row = validDetails[i];
            let testType = (row.type_of_testing_id_fix || row.type_of_testing_name || '').toString().toUpperCase();
            let isRT = (testType === 'RT');

            if (isRT) {
                if (!row.sub_details || row.sub_details.length === 0) {
                    toastr.error('Please Enter Film Details');
                    return;
                }
            }
        }

        let formId = jQuery('#id').val();
        let url = formId ? 'update-observation_sheet' : 'store-observation_sheet';
        let formData = new FormData(form);
        formData.append('observation_sheet_details_data', JSON.stringify(observation_sheet_details_data || []));

        let $submitBtn = formId ? jQuery('#updatebtn') : jQuery('#submitbtn');
        $submitBtn.prop('disabled', true);

        skipLoader = false;
        if (typeof showLoader === 'function') showLoader();

        jQuery.ajax({
            type: 'POST',
            url: url,
            data: formData,
            contentType: false,
            processData: false,
            headers: headerOpt,
            success: function (res) {
                if (res.response_code == 1) {
                    let msg = res.response_message || (formId ? 'Record Updated.' : 'Record Inserted.');

                    let nextFn = function () {
                        if (formId) {
                            if (typeof pageRelod !== 'undefined' && pageRelod == 'Yes') {
                                const form = document.getElementById("commonObservationSheetForm");
                                if (form) {
                                    form.classList.remove('was-validated');
                                }
                            } else {
                                window.location.reload();
                            }
                        } else {
                            resetObservationSheetForm();
                            loadCustomersList();
                            getLatestObservationSheetSequence();
                            if (typeof table !== 'undefined') table.ajax.reload();
                        }
                    };

                    if (res.url && res.url != "") {
                        jQuery('#preview_btn').attr('href', res.url).show();
                        toastSuccessPreview(msg, res.url, nextFn);
                    } else {
                        toastSuccess(msg, nextFn);
                    }
                } else {
                    toastr.error(res.response_message || 'Error saving record.');
                }
            },
            complete: function () {
                if (typeof hideLoader === 'function') hideLoader();
                $submitBtn.prop('disabled', false);
            }
        });
    });

    // Edit button click from table
    jQuery(document).on('click', '.edit-sheet', function () {
        let id = jQuery(this).data('id');
        currentEditId = id;
        loadObservationSheetEditData(id);
    });

    jQuery('#commonObservationSheetForm').find('#observation_sheet_sequence').on('change', function () {
        checkObservationSheetSequenceDuplication();
    });
});

function fetchCustomerPendingInwards(customerId) {
    if (!customerId) {
        availablePendingInwards = [];
        renderPendingInwardModalTable();
        return jQuery.Deferred().resolve().promise();
    }

    skipLoader = false;
    if (typeof showLoader === 'function') showLoader();

    return jQuery.ajax({
        url: 'get-pending-inward-for-observation_sheet',
        type: 'GET',
        data: { customer_id: customerId },
        headers: headerOpt,
        success: function (res) {
            if (res.response_code == 1) {
                availablePendingInwards = res.pending_data || [];
            } else {
                availablePendingInwards = [];
            }
            renderPendingInwardModalTable();
        },
        error: function () {
            availablePendingInwards = [];
            renderPendingInwardModalTable();
        },
        complete: function () {
            if (typeof hideLoader === 'function') hideLoader();
        }
    });
}

let isFetchingPendingTechnique = false;
function fetchPendingTechniqueSheets() {
    if (isFetchingPendingTechnique) return;
    isFetchingPendingTechnique = true;

    let $table = jQuery('#PendingTechniqueSheetTable');
    let tbody = jQuery('#pending_technique_tbody');
    tbody.empty();

    skipLoader = false;
    if (typeof showLoader === 'function') showLoader();

    jQuery.ajax({
        url: 'get-pending-technique-sheet-for-observation_sheet',
        type: 'GET',
        headers: headerOpt,
        success: function (res) {
            if (jQuery.fn.DataTable.isDataTable($table)) {
                $table.DataTable().clear().destroy();
                $table.find('thead tr.search-row').remove();
            }

            if (res.response_code == 1 && res.sheets && res.sheets.length > 0) {
                availablePendingTechniques = res.sheets;
                let html = '';
                res.sheets.forEach((s, idx) => {
                    html += `<tr>
                        <td class="text-center">
                            <input type="radio" name="select_pending_technique" class="form-check-input pending-technique-radio" data-index="${idx}" data-id="${s.technique_sheet_rt_id}" data-no="${s.technique_sheet_rt_no}">
                        </td>
                        <td>${s.technique_sheet_rt_no || ''}</td>
                        <td>${s.technique_sheet_rt_date || ''}</td>
                        <td>${s.customer || ''}</td>
                        <td>${s.type_of_job || ''}</td>
                        <td>${s.job_description || ''}</td>
                        <td>${s.part_no || ''}</td>
                        <td>${s.drg_no || ''}</td>
                        <td>${s.area_of_coverage || ''}</td>
                    </tr>`;
                });
                tbody.html(html);

                var $dt = $table.DataTable({
                    destroy: true,
                    paging: true,
                    searching: true,
                    dom: 'rtip',
                    fixedHeader: false,
                    "sScrollX": true,
                    "sScrollX": "100%",
                    "bScrollCollapse": true,
                    order: [[1, 'asc']],
                    columnDefs: [
                        { orderable: false, targets: 0 }
                    ],
                });

                if (typeof initColumnSearch === 'function') {
                    initColumnSearch('#PendingTechniqueSheetTable', [0], 'common_search');
                }
            } else {
                tbody.html('<tr><td colspan="9" class="text-center">No Pending Technique Sheets Found</td></tr>');
            }
            jQuery('#PendingTechniqueSheetRtModal').modal('show');
        },
        complete: function () {
            isFetchingPendingTechnique = false;
            if (typeof hideLoader === 'function') hideLoader();
        }
    });
}

function renderPendingInwardModalTable() {
    let $table = jQuery('#PendingForObservationSheetTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().clear().destroy();
        $table.find('thead tr.search-row').remove();
    }

    let tbody = jQuery('#pending_obs_tbody');
    tbody.empty();
    jQuery('#select_all_pending_obs').prop('checked', false);

    if (availablePendingInwards.length === 0) {
        tbody.append('<tr><td colspan="21" class="text-center">No Pending Inward Records Found</td></tr>');
        return;
    }

    availablePendingInwards.forEach((p, index) => {
        let isSelected = observation_sheet_details_data.some(r => r.mode !== 'Delete' && isSamePendingRecord(r, p));
        let rtNoDisplay = (p.process_type === 'Repair') ? (p.rt_no || p.test_report_no || '') : '';
        let rowHtml = `
            <tr>
                <td class="text-center">
                    <input type="checkbox" class="form-check-input pending-obs-checkbox" data-index="${index}" ${isSelected ? 'checked' : ''}>
                    <input type="hidden" name="from_type_id_fix" value="${p.from_type_id_fix}" >
                </td>
                <td>${rtNoDisplay}</td>
                <td>${p.process_type || 'Fresh'}</td>
                <td>${p.type_of_testing_id_fix || ''}</td>
                <td>${p.material_inward_no || ''}</td>
                <td>${p.material_inward_date || ''}</td>
                <td>${p.nabl_type_fix || 'Non NABL'}</td>
                <td>${p.test_at_fix || 'At Lab'}</td>
                <td>${p.job_type_fix || ''}</td>
                <td>${p.dc_no || ''}</td>
                <td>${p.dc_date || ''}</td>
                <td>${p.po_no || ''}</td>
                <td>${p.po_date || ''}</td>
                <td>${p.type_of_job || ''}</td>
                <td>${p.job_description || ''}</td>
                <td>${p.part_no || ''}</td>
                <td>${p.drg_no || ''}</td>
                <td>${p.material || ''}</td>
                <td>${p.heat_no || ''}</td>
                <td>${p.product_code || ''}</td>
                <td>${p.pending_qty || 0}</td>
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
        "bScrollCollapse": true,
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: 0 }
        ],
    });

    if (typeof initColumnSearch === 'function') {
        initColumnSearch('#PendingForObservationSheetTable', [0], 'common_search');
    }
}

function loadObservationSheetEditData(id) {
    _isEditMode = true;
    skipLoader = false;
    if (typeof showLoader === 'function') showLoader();
    jQuery.ajax({
        url: 'edit-observation_sheet',
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        success: function (res) {
            if (res.response_code == 1) {
                let d = res.obs_data;
                jQuery('#id').val(d.observation_sheet_id);
                jQuery('#observation_sheet_sequence').val(d.observation_sheet_sequence);
                jQuery('#observation_sheet_no').val(d.observation_sheet_no);
                jQuery('#observation_sheet_date').val(d.observation_sheet_date);
                loadCustomersList(d.customer_id).done(function () {
                    jQuery('#customer_id').val(d.customer_id).trigger('change.select2');
                    setSelect2Readonly('#customer_id', true);
                    jQuery('#customer_id').addClass('skip-tab');
                    jQuery('#load_pending_reports').prop('disabled', true);
                    fetchCustomerPendingInwards(d.customer_id);
                });
                setSelect2Readonly('#customer_id', true);
                jQuery('#customer_id').addClass('skip-tab');
                jQuery('#load_pending_reports').prop('disabled', true);
                jQuery('#special_note').val(d.sp_note || '');
                if (d.prepared_by_user_id) {
                    jQuery('#prepared_by_user_id').val(d.prepared_by_user_id).trigger('change.select2');
                }

                observation_sheet_details_data = (res.obs_details_data || []).map(row => {
                    if (row.test_report_rt_id === 0 || row.test_report_rt_id === '0' || !row.test_report_rt_id) {
                        row.test_report_rt_id = null;
                    }
                    return row;
                });
                renderObservationSheetDetailsTable();

                jQuery('#submitbtn').hide();
                jQuery('#updatebtn').show();
                jQuery('#add_new').show();
                if (d.pdf_name) {
                    let encodedId = btoa(d.observation_sheet_id);
                    let url = checkFileRoute + "?id=" + encodedId + "&name=" + d.pdf_name + "&type=observation_sheet";
                    jQuery('#preview_btn').attr('href', url).show();
                }

                jQuery('#ObservationSheetModal').modal('show');
            } else {
                toastr.error(res.response_message || 'Record Not Found.');
            }
        },
        complete: function () {
            if (typeof hideLoader === 'function') hideLoader();
        }
    });
}

function getLatestObservationSheetSequence() {
    jQuery.ajax({
        url: 'get-latest-observation_sheet-number',
        type: 'GET',
        headers: headerOpt,
        success: function (res) {
            if (res.response_code == 1) {
                jQuery('#observation_sheet_sequence').val(res.number);
                jQuery('#observation_sheet_no').val(res.latest_no);
                jQuery('#observation_sheet_date').val(currentDate);
                setTimeout(function () {
                    if (!_isEditMode) {
                        jQuery('#observation_sheet_sequence').focus().select();
                    }
                }, 100);
            }
        }
    });
}

function resetObservationSheetForm() {
    _isEditMode = false;
    currentEditId = null;
    originalObsDetailRowBackup = null;
    isObsDetailSaved = false;

    var form = document.getElementById("commonObservationSheetForm");
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
    }

    setSelect2Readonly('#ObservationSheetModal #customer_id', false);
    jQuery('#commonObservationSheetForm #id').val('');
    jQuery('#customer_id').val('').trigger('change.select2');

    if (typeof loginUserId !== 'undefined' && loginUserId) {
        jQuery('#prepared_by_user_id').val(loginUserId).trigger('change.select2');
    } else {
        let defaultUser = jQuery('#prepared_by_user_id').find('option[selected]').val() || '';
        if (defaultUser) {
            jQuery('#prepared_by_user_id').val(defaultUser).trigger('change.select2');
        }
    }

    jQuery('#load_pending_reports').prop('disabled', true);
    observation_sheet_details_data = [];
    availablePendingInwards = [];
    renderPendingInwardModalTable();
    renderObservationSheetDetailsTable();
    jQuery('#submitbtn').show();
    jQuery('#updatebtn').hide();
    jQuery('#add_new').hide();
    jQuery('#preview_btn').hide();
}

function renderObservationSheetDetailsTable() {
    let tbody = jQuery('#ObservationSheetDetailTable #details_tbody');
    if (tbody.length === 0) tbody = jQuery('#details_tbody');
    tbody.empty();

    let validRows = observation_sheet_details_data.filter(row => row.mode !== 'Delete');
    if (!validRows || validRows.length === 0) {
        tbody.html('<tr id="noDetails"><td colspan="22" class="text-center">No Observation Sheet Details Added</td></tr>');
        return;
    }

    let html = '';
    observation_sheet_details_data.forEach((row, indx) => {
        if (row.mode === 'Delete') return;
        let rtNoDisplay = row.rt_no || row.test_report_no || row.technique_sheet_rt_no || row.technique || '';
        let testType = (row.type_of_testing_id_fix || row.type_of_testing_name || '').toString().toUpperCase();
        let isRT = (testType === 'RT');

        let editOptionHtml = '';
        if (isRT) {
            editOptionHtml = `<li><a class="dropdown-item edit-detail-row" href="javascript:void(0)" onclick="editObservationDetailRow(${indx})"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit</a></li>`;
        }

        let deleteOptionHtml = '';
        if (row.in_use != true && row.in_use != 1) {
            deleteOptionHtml = '<li><a class="dropdown-item remove-detail-row" href="javascript:void(0)" onclick="removeObservationDetailRow(' + indx + ')"><i class="ri-delete-bin-fill align-bottom me-2 text-danger"></i> Delete</a></li>';
        }

        html += '<tr>' +
            '<td class="text-center">' +
            '<div class="dropdown d-inline-block">' +
            '<button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" data-bs-popper-config=\'{"strategy":"fixed"}\' data-bs-boundary="window" aria-expanded="false">' +
            '<i class="ri-more-fill align-middle"></i>' +
            '</button>' +
            '<ul class="dropdown-menu">' +
            editOptionHtml +
            deleteOptionHtml +
            '</ul>' +
            '</div>' +
            '</td>' +
            '<td>' + rtNoDisplay + '</td>' +
            '<td>' + (row.process_type || 'Fresh') + '</td>' +
            '<td>' + (row.type_of_testing_id_fix || '') + '</td>' +
            '<td>' + (row.inward_no || '') + '</td>' +
            '<td>' + (row.inward_date || '') + '</td>' +
            '<td>' + (row.nabl_type_fix || 'Non NABL') + '</td>' +
            '<td>' + (row.test_at_fix || 'At Lab') + '</td>' +
            '<td>' + (row.job_type_fix || '') + '</td>' +
            '<td>' + (row.dc_no || '') + '</td>' +
            '<td>' + (row.dc_date || '') + '</td>' +
            '<td>' + (row.po_no || '') + '</td>' +
            '<td>' + (row.po_date || '') + '</td>' +
            '<td>' + (row.type_of_job || '') + '</td>' +
            '<td>' + (row.job_description || '') + '</td>' +
            '<td>' + (row.part_no || '') + '</td>' +
            '<td>' + (row.drg_no || '') + '</td>' +
            '<td>' + (row.material || '') + '</td>' +
            '<td>' + (row.heat_no || '') + '</td>' +
            '<td>' + (row.product_code || '') + '</td>' +
            '<td>' + (row.quantity || 1) + '</td>' +
            // '<td>' + (row.remark || '') + '</td>' +
            '</tr>';
    });
    tbody.html(html);
}

function editObservationDetailRow(indx) {
    let row = observation_sheet_details_data[indx];
    if (row) {
        let testType = (row.type_of_testing_id_fix || row.type_of_testing_name || '').toString().toUpperCase();
        let isRT = (testType === 'RT');

        if (!isRT) {
            toastr.error('Edit details available only for RT testing');
            return;
        }

        let isFresh = (row.process_type === 'Fresh' || !row.process_type);
        let isRepair = (row.process_type === 'Repair');

        originalObsDetailRowBackup = JSON.parse(JSON.stringify(row));
        isObsDetailSaved = false;

        jQuery('#detail_index').val(indx);
        jQuery('#inward_no').val(row.inward_no);
        jQuery('#inward_date').val(row.inward_date);
        jQuery('#type_of_job').val(row.type_of_job);
        jQuery('#dc_no').val(row.dc_no);

        // Map missing fields for new layout
        jQuery('#nature').val(row.process_type);
        jQuery('#type_of_test').val(row.type_of_testing_id_fix);
        jQuery('#nabl').val(row.nabl_type_fix);
        jQuery('#test_at').val(row.test_at_fix);
        jQuery('#type').val(row.job_type_fix);
        jQuery('#dc_date').val(row.dc_date);
        jQuery('#po_no').val(row.po_no);
        jQuery('#po_date').val(row.po_date);
        jQuery('#product_code').val(row.product_code);

        jQuery('#report_no').val(row.rt_no || row.technique_sheet_rt_no || row.test_report_no || row.technique || '');
        let revNo = row.revision_number || '';
        jQuery('#revision_number').val(revNo);


        jQuery('#job_description').val(row.job_description);
        jQuery('#die_no').val(row.part_no);
        jQuery('#heat_no').val(row.heat_no);
        jQuery('#drg_no').val(row.drg_no);
        jQuery('#thickness').val(row.thickness);
        jQuery('#material').val(row.material);
        jQuery('#total_qty').val(row.quantity);
        // jQuery('#remark').val(row.remark);

        // Film Size Unit Radio handling
        let unitFix = row.film_size_unit_fix || row.film_size_fix || 'inch';
        if (unitFix === 'cm') {
            jQuery('#obs_film_size_cm').prop('checked', true);
        } else {
            jQuery('#obs_film_size_inch').prop('checked', true);
        }
        updateObsFilmSizeDropdown();

        // Handle Pending Button Logic based on Fresh/Repair and Report ID
        let hasReportId = (row.test_report_rt_id && parseInt(row.test_report_rt_id) > 0);
        let hasTechId = (row.technique_sheet_rt_id && parseInt(row.technique_sheet_rt_id) > 0);

        let $btn = jQuery('#btn_pending_technique');
        $btn.off('click.modalToggle'); // Remove old handlers

        if (_isEditMode) {
            $btn.prop('disabled', true).text('Pending');
            jQuery('#add_sub_detail_btn').prop('disabled', true);
            setFilmSizeRadioReadonly(true);
        } else {
            if (isFresh) {
                $btn.prop('disabled', false).text('Pending');
                $btn.on('click.modalToggle', function () {
                    fetchPendingTechniqueSheets();
                });
            } else if (isRepair && !hasReportId) {
                $btn.prop('disabled', false).text('Pending');
                $btn.on('click.modalToggle', function () {
                    fetchPendingOldRtReports();
                });
            } else if (isRepair && hasReportId) {
                $btn.prop('disabled', true).text('Pending');
                // Auto-fetch if sub_details are empty
                if (!row.sub_details || row.sub_details.length === 0) {
                    if (!row._fetching_repair) {
                        row._fetching_repair = true;
                        fetchOldRtReportDetailsForRepair(row.test_report_rt_id, indx);
                    }
                }
            } else {
                $btn.prop('disabled', true);
            }

            if (isRepair || hasTechId) {
                jQuery('#add_sub_detail_btn').prop('disabled', true);
                setFilmSizeRadioReadonly(true);
            } else {
                jQuery('#add_sub_detail_btn').prop('disabled', false);
                setFilmSizeRadioReadonly(false);
            }
        }

        renderObservationSheetSubDetailsTable(indx);
        jQuery('#ObservationSheetDetailEditModal').modal('show');
    }
}

function resequenceObservationSubDetails(detailIdx) {
    let row = observation_sheet_details_data[detailIdx];
    if (!row || !row.sub_details) return;

    let activeSubs = row.sub_details.filter(s => s.mode !== 'Delete');
    let deleteSubs = row.sub_details.filter(s => s.mode === 'Delete');

    activeSubs.sort((a, b) => parseFloat(a.sr_no || 0) - parseFloat(b.sr_no || 0));

    row.sub_details = [...activeSubs, ...deleteSubs];
}

function renderObservationSheetSubDetailsTable(detailIdx) {
    resequenceObservationSubDetails(detailIdx);

    let $table = jQuery('#ObservationSheetSubDetailTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().clear().destroy();
        $table.find('thead tr.search-row').remove();
    }

    let tbody = jQuery('#sub_details_tbody');
    tbody.empty();

    let row = observation_sheet_details_data[detailIdx];
    if (!row || !row.sub_details || row.sub_details.length === 0) {
        tbody.html('<tr><td colspan="13" class="text-center">No Sub Details Added</td></tr>');
        return;
    }

    let activeSubs = row.sub_details.filter(s => s.mode !== 'Delete');
    if (!activeSubs || activeSubs.length === 0) {
        tbody.html('<tr><td colspan="13" class="text-center">No Sub Details Added</td></tr>');
        return;
    }

    activeSubs.sort((a, b) => parseFloat(a.sr_no || 0) - parseFloat(b.sr_no || 0));

    let unit = (row && row.film_size_unit_fix) ? row.film_size_unit_fix : (jQuery('input[name="obs_film_size_unit_fix"]:checked').val() || 'inch');

    let html = '';
    activeSubs.forEach((sub, sIdx) => {
        let realIdx = row.sub_details.indexOf(sub);
        let filmSizeDisplay = sub.film_size || '';
        if (sub.film_id) {
            let opt = jQuery('#sub_film_id option[value="' + sub.film_id + '"]');
            let inchVal = sub.film_size_inch || opt.data('inch');
            let cmVal = sub.film_size_cm || opt.data('cm');
            if (unit === 'cm' && cmVal) {
                filmSizeDisplay = cmVal;
            } else if (inchVal) {
                filmSizeDisplay = inchVal;
            }
        }

        html += `<tr>
            <td class="text-center">
                <div class="dropdown d-inline-block">
                    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' data-bs-boundary="window" aria-expanded="false">
                        <i class="ri-more-fill align-middle"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item edit-sub-row" href="javascript:void(0)" onclick="editObservationSubDetailRow(${realIdx})"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit</a></li>
                        ${(row.in_use == true || row.in_use == 1) ? '' : `<li><a class="dropdown-item remove-sub-row" href="javascript:void(0)" onclick="removeObservationSubDetailRow(${realIdx})"><i class="ri-delete-bin-fill align-bottom me-2 text-danger"></i> Delete</a></li>`}
                    </ul>
                </div>
            </td>
            <td>${sub.sr_no}</td>
            <td>${sub.identification || ''}</td>
            <td>${sub.location || ''}</td>
            <td>${sub.source || sub.source_id_fix || ''}</td>
            <td>${sub.film_brand || ''}</td>
            <td>${sub.film_type || ''}</td>
            <td>${sub.thickness || ''}</td>
            <td>${sub.sfd || ''}</td>
            <td>${sub.iqi_designation || sub.iqi || ''}</td>
            <td>${sub.iqi_sensitivity || sub.sensitivity || ''}</td>
            <td>${filmSizeDisplay}</td>
            <td>${sub.no_of_film_fix || ''}</td>
        </tr>`;
    });
    tbody.html(html);

    var $dt = $table.DataTable({
        paging: true,
        searching: true,
        dom: 'rtip',
        fixedHeader: false,
        scrollCollapse: true,
        "sScrollX": true,
        "sScrollX": "100%",
        "sScrollXInner": "100%",
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: 0 },
            { type: 'num', targets: 1 }
        ],
    });

    $table.css('margin-bottom', '0px');
    $table.closest('.dataTables_scrollBody').css({
        'min-height': '0px',
        'height': 'auto',
        'margin-bottom': '0px',
        'padding-bottom': '0px'
    });

    if (typeof initColumnSearch === 'function') {
        initColumnSearch('#ObservationSheetSubDetailTable', [0], 'common_search');
    }
}

function editObservationSubDetailRow(sIdx) {
    let detailIdx = jQuery('#detail_index').val();
    if (detailIdx !== '' && observation_sheet_details_data[detailIdx]) {
        let row = observation_sheet_details_data[detailIdx];
        let sub = row.sub_details[sIdx];
        if (sub) {
            let film_size_fix = (row && row.film_size_unit_fix) ? row.film_size_unit_fix : (jQuery('input[name="obs_film_size_unit_fix"]:checked').val() || 'inch');
            updateObsFilmSizeDropdown();

            jQuery('#ObservationSheetSubDetailForm').removeClass('was-validated');
            jQuery('#sub_detail_index').val(sIdx);
            jQuery('#sub_sr_no').val(sub.sr_no || (sIdx + 1));
            jQuery('#sub_identification').val(sub.identification || '');
            jQuery('#sub_thickness').val(sub.thickness || '');
            jQuery('#sub_sfd').val(sub.sfd || '');
            jQuery('#sub_location').val(sub.location || '');
            jQuery('#sub_source_id_fix').val(sub.source_id_fix || '').trigger('change').trigger('change.select2');
            jQuery('#sub_film_brand_id').val(sub.film_brand_id || '').trigger('change').trigger('change.select2');
            jQuery('#sub_film_type_id').val(sub.film_type_id || '').trigger('change').trigger('change.select2');
            // jQuery('#sub_iqi_designation_id').val(sub.iqi_designation_id || '').trigger('change').trigger('change.select2');
            // jQuery('#sub_iqi_sensitivity_id').val(sub.iqi_sensitivity_id || '').trigger('change').trigger('change.select2');
            jQuery('#sub_iqi_designation').val(sub.iqi_designation || sub.iqi || '');
            jQuery('#sub_iqi_sensitivity').val(sub.iqi_sensitivity || sub.sensitivity || '');

            let filmSelect = jQuery('#sub_film_id');
            filmSelect.find('.temp-option').remove();
            let filmId = sub.film_id || '';
            if (filmId && filmSelect.find('option[value="' + filmId + '"]').length === 0) {
                let inch = sub.film_size_inch || sub.film_size || '';
                let cm = sub.film_size_cm || '';
                let displayName = (film_size_fix === 'cm') ? (cm || inch || 'Film Size') : (inch || cm || 'Film Size');
                let opt = new Option(displayName, filmId, true, true);
                jQuery(opt).addClass('temp-option')
                    .attr('data-inch', inch)
                    .attr('data-cm', cm)
                    .attr('data-sq_in', sub.sq_in || '')
                    .attr('data-sq_cm', sub.sq_cm || '');
                filmSelect.append(opt);
            }
            filmSelect.val(filmId).trigger('change').trigger('change.select2');
            jQuery('#sub_no_of_film_fix').val(sub.no_of_film_fix || 'Single').trigger('change').trigger('change.select2');
            jQuery('#saveObsSubDetailBtn').text('Edit');
            jQuery('#ObservationSheetSubDetailModal').modal('show');
        }
    }
}

function removeObservationSubDetailRow(sIdx) {
    let detailIdx = jQuery('#detail_index').val();
    if (detailIdx !== '' && observation_sheet_details_data[detailIdx]) {
        let row = observation_sheet_details_data[detailIdx];
        if (row.in_use == true || row.in_use == 1) {
            toastr.error("You Can't Delete, Observation Detail Is Used In Test Report (RT).");
            return;
        }
    }
    toastDetailDelete('Are You Sure, You Want To Delete ?', () => {
        if (detailIdx !== '' && observation_sheet_details_data[detailIdx]) {
            let sub = observation_sheet_details_data[detailIdx].sub_details[sIdx];
            if (sub) {
                if (sub.observation_sheet_details_details_input_id) {
                    sub.mode = 'Delete';
                } else {
                    observation_sheet_details_data[detailIdx].sub_details.splice(sIdx, 1);
                }
            }
            renderObservationSheetSubDetailsTable(detailIdx);
        }
    });
}

function removeObservationDetailRow(indx) {
    if (observation_sheet_details_data[indx] && (observation_sheet_details_data[indx].in_use == true || observation_sheet_details_data[indx].in_use == 1)) {
        toastr.error("You Can't Delete, Observation Detail Is Used In Test Report (RT).");
        return;
    }
    toastDetailDelete('Are You Sure, You Want To Delete ?', () => {
        if (observation_sheet_details_data[indx]) {
            if (observation_sheet_details_data[indx].observation_sheet_details_id) {
                observation_sheet_details_data[indx].mode = 'Delete';
            } else {
                observation_sheet_details_data.splice(indx, 1);
            }
            renderObservationSheetDetailsTable();

            // Jo badhi rows delete thai jay to Customer pachu Editable thay
            if (!_isEditMode) {
                let remaining = observation_sheet_details_data.filter(d => d.mode !== 'Delete');
                if (remaining.length === 0) {
                    setSelect2Readonly('#ObservationSheetModal #customer_id', false);
                }
            }
        }
    });
}

function loadCustomersList(selectedId = null) {
    let custHtml = '<option value="">Select Customer</option>';
    let currentId = jQuery('#commonObservationSheetForm #id').val() || null;
    return jQuery.ajax({
        url: 'get-pending-customers-for-observation_sheet',
        type: 'GET',
        data: { sheet_id: currentId },
        headers: headerOpt,
        success: function (res) {
            if (res.customers) {
                res.customers.forEach(c => {
                    custHtml += '<option value="' + c.id + '">' + c.customer + '</option>';
                });
                jQuery('#customer_id').empty().append(custHtml).trigger('change.select2');
                if (selectedId) {
                    jQuery('#customer_id').val(selectedId).trigger('change.select2');
                }

                // Enforce correct select2 readonly state based on current mode/details
                if (_isEditMode) {
                    setSelect2Readonly('#customer_id', true);
                } else {
                    let hasDetails = (typeof observation_sheet_details_data !== 'undefined' &&
                        observation_sheet_details_data.some(d => d.mode !== 'Delete'));
                    setSelect2Readonly('#customer_id', hasDetails);
                }
            }
        }
    });
}

function checkObservationSheetSequenceDuplication() {
    let seq = jQuery('#observation_sheet_sequence').val();
    if (seq != "") {
        if (seq > 0 == false) {
            toastr.error('Please Enter Valid Sr. No.');
            jQuery('#observation_sheet_sequence').val('');
            jQuery('#observation_sheet_sequence').focus();
            jQuery('#submitbtn, #updatebtn').prop('disabled', false);
        } else {
            jQuery('#observation_sheet_sequence').addClass('file-loader');
            jQuery('#submitbtn, #updatebtn').prop('disabled', true);
            let id = jQuery('#id').val();
            return jQuery.ajax({
                url: 'check-observation_sheet_number_duplication',
                type: 'GET',
                data: { observation_sheet_sequence: seq, id: id },
                dataType: 'json',
                success: function (data) {
                    jQuery('#observation_sheet_sequence').removeClass('file-loader');
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        jQuery('#observation_sheet_sequence').val('');
                        jQuery('#observation_sheet_sequence').focus();
                    } else {
                        jQuery('#observation_sheet_no').val(data.latest_no);
                        jQuery('#observation_sheet_sequence').val(seq);
                        jQuery('#submitbtn, #updatebtn').prop('disabled', false);
                    }
                },
                error: function () {
                    jQuery('#observation_sheet_sequence').removeClass('file-loader');
                    jQuery('#submitbtn, #updatebtn').prop('disabled', false);
                    toastr.error('Something went wrong!');
                }
            });
        }
    } else {
        jQuery('#submitbtn, #updatebtn').prop('disabled', false);
    }
}

let isFetchingPendingOldRt = false;
function fetchPendingOldRtReports() {
    if (isFetchingPendingOldRt) return;
    isFetchingPendingOldRt = true;
    let nablType = jQuery('#nabl').val() || '';
    let customerId = jQuery('#customer_id').val();
    if (!customerId) {
        toastr.error("Please select a customer first.");
        isFetchingPendingOldRt = false;
        return;
    }

    let $table = jQuery('#PendingOldRtReportTable');
    let tbody = jQuery('#pending_old_rt_report_tbody');
    tbody.empty();

    skipLoader = false;
    if (typeof showLoader === 'function') showLoader();

    jQuery.ajax({
        url: 'get-old-rt-reports-list-for-observation-sheet',
        type: 'GET',
        data: { customer_id: customerId, nabl_type: nablType },
        success: function (res) {
            if (jQuery.fn.DataTable.isDataTable($table)) {
                $table.DataTable().clear().destroy();
                $table.find('thead tr.search-row').remove();
            }

            if (res.response_code === 1) {
                let html = '';
                if (res.reports && res.reports.length > 0) {
                    res.reports.forEach(function (report) {
                        html += '<tr>';
                        html += '<td class="text-center"><input type="radio" name="pending_old_rt_report" class="form-check-input pending-old-rt-radio" value="' + report.test_report_rt_id + '" data-report-no="' + (report.test_report_no || '') + '" data-revision-number="' + (report.revision_number || '') + '" data-heat-no="' + (report.heat_no || '') + '" data-product-code="' + (report.product_code || '') + '" data-part-no="' + (report.part_no || '') + '" data-drg-no="' + (report.drg_no || '') + '" data-material="' + (report.material || '') + '"></td>';
                        html += '<td>' + (report.nabl_type_fix || '') + '</td>';
                        html += '<td>' + (report.test_report_no || '') + '</td>';
                        html += '<td>' + (report.test_report_date || '') + '</td>';
                        html += '<td>' + (report.ulr_no || '') + '</td>';
                        html += '<td>' + (report.customer || '') + '</td>';
                        html += '<td>' + (report.type_of_job || '') + '</td>';
                        html += '<td>' + (report.job_description || '') + '</td>';
                        html += '<td>' + (report.part_no || '') + '</td>';
                        html += '<td>' + (report.drg_no || '') + '</td>';
                        html += '<td>' + (report.heat_no || '') + '</td>';
                        html += '<td>' + (report.product_code || '') + '</td>';
                        html += '<td>' + (report.material || '') + '</td>';
                        html += '<td>' + (report.rt_no || '') + '</td>';
                        html += '</tr>';
                    });
                    tbody.html(html);

                    var $dt = $table.DataTable({
                        destroy: true,
                        paging: true,
                        searching: true,
                        dom: 'rtip',
                        fixedHeader: false,
                        "sScrollX": "100%",
                        "bScrollCollapse": true,
                        order: [[2, 'asc']],
                        columnDefs: [
                            { orderable: false, targets: 0 }
                        ],
                    });

                    if (typeof initColumnSearch === 'function') {
                        initColumnSearch('#PendingOldRtReportTable', [0], 'common_search');
                    }
                } else {
                    tbody.html('<tr><td colspan="9" class="text-center">No RT reports found.</td></tr>');
                }

                jQuery('#PendingOldRtReportModal').modal('show');
            }
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            toastr.error('Error fetching old RT reports');
        },
        complete: function () {
            isFetchingPendingOldRt = false;
            if (typeof hideLoader === 'function') hideLoader();
        }
    });
}

jQuery(document).on('click', '#submitPendingOldRtReportBtn', function () {
    let selectedRadio = jQuery('.pending-old-rt-radio:checked');
    if (selectedRadio.length === 0) {
        toastr.error('Please Select An Old RT Report');
        return;
    }

    let reportId = selectedRadio.val();
    let reportNo = selectedRadio.data('report-no') || '';
    let revNo = selectedRadio.data('revision-number') || '';
    let heatNo = selectedRadio.data('heat-no') || '';
    let productCode = selectedRadio.data('product-code') || '';
    let partNo = selectedRadio.data('part-no') || '';
    let drgNo = selectedRadio.data('drg-no') || '';
    let material = selectedRadio.data('material') || '';
    let detailIdx = jQuery('#detail_index').val();

    if (detailIdx !== '' && observation_sheet_details_data[detailIdx]) {
        let row = observation_sheet_details_data[detailIdx];
        row.test_report_rt_id = reportId;
        row.rt_no = reportNo;
        row.test_report_no = reportNo;
        row.revision_number = revNo;
        if (heatNo) row.heat_no = heatNo;
        if (productCode) row.product_code = productCode;
        if (partNo) row.part_no = partNo;
        if (drgNo) row.drg_no = drgNo;
        if (material) row.material = material;

        jQuery('#report_no').val(reportNo);
        jQuery('#revision_number').val(revNo);
        if (row.heat_no) jQuery('#heat_no').val(row.heat_no);
        if (row.product_code) jQuery('#product_code').val(row.product_code);
        if (row.part_no) jQuery('#die_no').val(row.part_no);
        if (row.drg_no) jQuery('#drg_no').val(row.drg_no);
        if (row.material) jQuery('#material').val(row.material);
    }

    // Close modal
    jQuery('#PendingOldRtReportModal').modal('hide');

    // Fetch its details
    fetchOldRtReportDetailsForRepair(reportId, detailIdx);
});

function fetchOldRtReportDetailsForRepair(reportId, detailIdx) {
    skipLoader = false;
    if (typeof showLoader === 'function') showLoader();

    jQuery.ajax({
        url: 'get-old-rt-report-details-for-repair',
        type: 'GET',
        data: { test_report_rt_id: reportId },
        success: function (res) {
            if (res.response_code === 1) {
                let details = res.details || [];
                let sub_details = [];
                // console.log("OLD RT REPORT DETAILS: ", details);

                let row = (detailIdx !== '' && observation_sheet_details_data[detailIdx]) ? observation_sheet_details_data[detailIdx] : null;

                if (res.header && row) {
                    if (res.header.heat_no) {
                        row.heat_no = res.header.heat_no;
                        jQuery('#heat_no').val(res.header.heat_no);
                    }
                    if (res.header.product_code) {
                        row.product_code = res.header.product_code;
                        jQuery('#product_code').val(res.header.product_code);
                    }
                    if (res.header.part_no) {
                        row.part_no = res.header.part_no;
                        jQuery('#die_no').val(res.header.part_no);
                    }
                    if (res.header.drg_no) {
                        row.drg_no = res.header.drg_no;
                        jQuery('#drg_no').val(res.header.drg_no);
                    }
                    if (res.header.material) {
                        row.material = res.header.material;
                        jQuery('#material').val(res.header.material);
                    }
                    if (res.header.film_size_unit_fix) {
                        row.film_size_unit_fix = res.header.film_size_unit_fix;
                        let unitFix = row.film_size_unit_fix || 'inch';
                        if (unitFix === 'cm') {
                            jQuery('#obs_film_size_cm').prop('checked', true);
                        } else {
                            jQuery('#obs_film_size_inch').prop('checked', true);
                        }
                        updateObsFilmSizeDropdown();
                        setFilmSizeRadioReadonly(true);
                    }
                }

                let currentUnit = (row && row.film_size_unit_fix) ? row.film_size_unit_fix : (jQuery('input[name="obs_film_size_unit_fix"]:checked').val() || 'inch');

                details.forEach(function (d, i) {
                    let rawSrNo = d.sr_no !== undefined && d.sr_no !== null && d.sr_no !== '' ? d.sr_no : (i + 1);
                    let formattedSrNo = String(rawSrNo).replace(/\.0+$/, '').replace(/\.$/, '');
                    if (!isNaN(formattedSrNo) && formattedSrNo !== '') {
                        formattedSrNo = parseFloat(formattedSrNo) === parseInt(formattedSrNo) ? parseInt(formattedSrNo) : parseFloat(formattedSrNo);
                    }
                    sub_details.push({
                        sub_index: i,
                        sr_no: formattedSrNo,
                        identification: d.identification || '',
                        thickness: d.thickness || '',
                        sfd: d.sfd || d.detail_sfd || '',
                        location: d.location || '',
                        source_id_fix: d.source_id_fix || d.source || '',
                        source: d.source_name || d.source_id_fix || d.source || '',
                        film_brand_id: d.detail_film_brand_id || d.film_brand_id || '',
                        film_brand: d.film_brand_name || d.film_brand || '',
                        film_type_id: d.film_type_id || '',
                        film_type: d.film_type || '',
                        // iqi_designation_id: d.detail_iqi_designation_id || d.iqi_designation_id || d.iqi_id || '',
                        // iqi_sensitivity_id: d.detail_iqi_sensitivity_id || d.iqi_sensitivity_id || '',
                        iqi_designation_id: null,
                        iqi_designation: d.iqi_designation || d.iqi_designation_name || d.iqi || '',
                        iqi_sensitivity_id: null,
                        iqi_sensitivity: d.iqi_sensitivity || d.iqi_sensitivity_name || d.sensitivity || '',
                        film_id: d.detail_film_id || d.film_id || d.film_size_id || '',
                        film_size_inch: d.film_size_inch || '',
                        film_size_cm: d.film_size_cm || '',
                        film_size: (currentUnit === 'cm' ? (d.film_size_cm || d.film_size_inch || d.film_size) : (d.film_size_inch || d.film_size_cm || d.film_size)) || '',
                        no_of_film_fix: d.no_of_film_fix || d.detail_no_of_film_fix || 'Single',
                        film_qty: d.film_qty || ((d.no_of_film_fix || d.detail_no_of_film_fix) === 'Double' ? 2 : ((d.no_of_film_fix || d.detail_no_of_film_fix) === 'Triple' ? 3 : ((d.no_of_film_fix || d.detail_no_of_film_fix) === 'Quadra' ? 4 : 1)))
                    });
                });

                // Set the sub_details for the row
                observation_sheet_details_data[detailIdx].sub_details = sub_details;

                // Re-render the sub detail table
                renderObservationSheetSubDetailsTable(detailIdx);

                // Update the button text
                jQuery('#btn_pending_technique').prop('disabled', true).text('Pending');
            } else {
                toastr.error(res.response_message || 'Error fetching details');
                observation_sheet_details_data[detailIdx]._fetching_repair = false;
            }
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            toastr.error('Error fetching repair details from RT Report');
            observation_sheet_details_data[detailIdx]._fetching_repair = false;
        },
        complete: function () {
            if (typeof hideLoader === 'function') hideLoader();
        }
    });
}

function checkValue(input) {
    if (!input) return '';
    var firstUnderscorePattern = /^[a-zA-Z0-9]+_[a-zA-Z0-9]+/;

    if (firstUnderscorePattern.test(input)) {
        return '';
    } else {
        if (input.length === 1 && isNaN(input)) {
            var nextChar = String.fromCharCode(input.charCodeAt(0) + 1);
            if (nextChar === '[') nextChar = 'A';
            if (nextChar === '{') nextChar = 'a';
            return nextChar;
        }

        var parts = input.split('-');
        if (parts.length === 2) {
            var part1 = parts[0].trim();
            var part2 = parts[1].trim();

            // 1. Matching prefix + number, e.g. "L1-L2" -> "L2-L3"
            var alphaNumRegex = /^([a-zA-Z]+)(\d+)$/;
            var match1 = part1.match(alphaNumRegex);
            var match2 = part2.match(alphaNumRegex);

            if (match1 && match2 && match1[1].toLowerCase() === match2[1].toLowerCase()) {
                var prefix = match2[1];
                var num2 = parseInt(match2[2], 10);
                return part2 + '-' + prefix + (num2 + 1);
            }

            // 2. Purely numeric matching, e.g. "1-2" -> "2-3"
            if (!isNaN(part1) && !isNaN(part2) && part1 !== '' && part2 !== '') {
                var n1 = parseInt(part1, 10);
                var n2 = parseInt(part2, 10);
                var diff = n2 - n1;
                if (diff <= 0) diff = 1;
                return n2 + '-' + (n2 + diff);
            }

            // 3. Single letter matching on both sides, e.g. "A-B" -> "B-C"
            var singleLetterRegex = /^[a-zA-Z]$/;
            if (singleLetterRegex.test(part1) && singleLetterRegex.test(part2)) {
                var nextCharCode = part2.charCodeAt(0) + 1;
                var nextChar = String.fromCharCode(nextCharCode);
                if (part2 === 'Z') nextChar = 'A';
                if (part2 === 'z') nextChar = 'a';
                return part2 + '-' + nextChar;
            }

            return '';
        } else {
            return input;
        }
    }
}
