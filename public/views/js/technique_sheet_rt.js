var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];
var technique_sheet_rt_details_data = [];
var _openForEdit = false; // Flag: true when modal is opened for editing
var formId = jQuery('#commonTechniqueSheetRtForm').find('input[name="id"]').val();

// Initialize DataTable (done in manage blade script-manage, but referenced here)
// var table is already globally defined in manage-technique_sheet_rt.blade.php

// Draw details grid table rows
function fillTSDetailTable() {
    let tbody = jQuery('#TSDetailTable tbody');
    tbody.empty();

    let film_size_fix = jQuery('input[name="film_size_fix"]:checked').val() || 'inch';
    let sfd_unit_fix = jQuery('input[name="sfd_unit_fix"]:checked').val() || 'mm';

    // Update table headers dynamically based on selected unit
    jQuery('#TSDetailTable thead th.th_sfd_unit').text('SFD (' + sfd_unit_fix + ')');
    jQuery('#TSDetailTable thead th.th_sq_unit').text(film_size_fix === 'inch' ? 'SqIn' : 'SqCm');
    jQuery('#TSDetailTable thead th.th_total_sq_unit').text(film_size_fix === 'inch' ? 'Total SqIn' : 'Total SqCm');

    let validRows = technique_sheet_rt_details_data.filter(row => row.mode !== 'Delete');
    validRows.sort((a, b) => parseFloat(a.sr_no || 0) - parseFloat(b.sr_no || 0));

    if (validRows.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="15" class="text-center" id="noDetails">
                    No Radiographic Shooting Sketch Details Added
                </td>
            </tr>
        `);
    } else {
        validRows.forEach((row, index) => {
            // Find full array index of this item
            let formIndx = technique_sheet_rt_details_data.indexOf(row);

            let mainReportId = jQuery('#commonTechniqueSheetRtForm #id').val();
            let isExistingReport = (mainReportId && mainReportId != 0 && mainReportId != '0');

            let activeFilmId = zeroToEmpty(row.film_id || row.detail_film_id);
            let isFilmActive = activeFilmId && jQuery('#detail_film_id option[value="' + activeFilmId + '"]:not(.temp-option)').length > 0;
            
            let isCopiedRow = (row.is_copy === true || row.is_copied === true);
            let isExistingOrPendingRow = (isExistingReport && row.technique_sheet_rt_details_id && row.technique_sheet_rt_details_id != 0) || row.from_pending === true;

            let film_size_display = '';
            if (isExistingOrPendingRow && !isCopiedRow) {
                film_size_display = film_size_fix === 'inch' ? row.film_size_inch : row.film_size_cm;
            } else if (isFilmActive) {
                film_size_display = film_size_fix === 'inch' ? row.film_size_inch : row.film_size_cm;
            } else {
                film_size_display = '';
            }
            let sq_display = film_size_fix === 'inch' ? parseFloat(row.sq_in || 0).toFixed(2) : parseFloat(row.sq_cm || 0).toFixed(2);
            let total_sq_display = film_size_fix === 'inch' ? parseFloat(row.total_sq_in || 0).toFixed(2) : parseFloat(row.total_sq_cm || 0).toFixed(2);

            let rowHtml = `<tr>
                <td>
                    ${DetailsActionDropdown('editTSDetails', 'removeTSDetails')}
                    <input type="hidden" name="form_indx" value="${formIndx}"/>
                </td>
                <td>${row.sr_no}</td>
                <td>${row.identification}</td>
                <td>${row.location}</td>
                <td>${row.source_id_fix}</td>
                <td>${row.film_brand_name || ''}</td>
                <td>${row.film_type_name || ''}</td>
                <td>${row.thickness || ''}</td>
                <td>${row.sfd || ''}</td>
                <td>${row.iqi_designation || row.iqi_designation_name || ''}</td>
                <td>${row.iqi_sensitivity || row.iqi_sensitivity_name || ''}</td>
                <td>${film_size_display || ''}</td>
                <td>${row.no_of_film_fix}</td>
                <td>${row.test_technique || ''}</td>
                <td>${row.film_position || ''}</td>
                <td class="d-none">${row.film_qty}</td>
                <td class="d-none">${sq_display}</td>
                <td class="d-none">${total_sq_display}</td>
            </tr>`;
            tbody.append(rowHtml);
        });
    }

    // Recalculate and update the header summary fields
    updateHeaderSummaries(validRows, film_size_fix);

    // Update disabled/readonly state of entry type and film size radio buttons
    updateRadioDisabledStates();
}

// Recalculate header values from detail grid row values
function updateHeaderSummaries(rows, film_size_fix) {
    if (rows.length === 0) {
        jQuery('#no_of_films').val('');
        jQuery('#film_size').val('');
        jQuery('#total_area').val('');
        jQuery('#film_brand').val('');
        jQuery('#film_type').val('');
        jQuery('#source_used').val('');
        jQuery('#test_technique').val('');
        updateSourceFieldsState();
        return;
    }

    // Fixed order sequence for sources: Ir-192, Co-60, X-Ray
    const sourceOrder = ['Ir-192', 'Co-60', 'X-Ray'];
    function getSourceRank(s) {
        let idx = sourceOrder.indexOf(s);
        return idx !== -1 ? idx : 999;
    }

    // 1. no_of_films: Total quantities grouped by unique Source (e.g. Co-60 - 3, Ir-192 - 1 = 4)
    let sourceQtyCounts = {};
    let totalQty = 0;
    rows.forEach(r => {
        let source = r.source_id_fix || '';
        if (source) {
            let qty = parseInt(r.film_qty || 0);
            if (sourceQtyCounts[source]) {
                sourceQtyCounts[source] += qty;
            } else {
                sourceQtyCounts[source] = qty;
            }
            totalQty += qty;
        }
    });
    let sortedQtySources = Object.keys(sourceQtyCounts).sort((a, b) => getSourceRank(a) - getSourceRank(b));
    let noOfFilmsOutput = sortedQtySources.map(source => source + " - " + sourceQtyCounts[source]);
    jQuery('#no_of_films').val(noOfFilmsOutput.join(', ') + " = " + totalQty);

    // 2. film_size: Aggregated by Source - Film Type - Film Size - Qty format (e.g. Co-60 - Aapnu NDT High Contrast - 2.5" x 7" - 2 = 2)
    let sizeCounts = {};
    let totalFilmSizeQty = 0;
    rows.forEach(r => {
        let source = r.source_id_fix || '';
        let type = r.film_type_name || '';
        let size = film_size_fix === 'inch' ? r.film_size_inch : r.film_size_cm;
        if (source && type && size) {
            let key = source + " - " + type + " - " + size;
            let qty = parseInt(r.film_qty || 0);
            if (sizeCounts[key]) {
                sizeCounts[key] += qty;
            } else {
                sizeCounts[key] = qty;
            }
            totalFilmSizeQty += qty;
        }
    });
    let sortedFilmSizeKeys = Object.keys(sizeCounts).sort((a, b) => {
        let sourceA = a.split(' - ')[0];
        let sourceB = b.split(' - ')[0];
        return getSourceRank(sourceA) - getSourceRank(sourceB);
    });
    let filmSizeOutput = sortedFilmSizeKeys.map(key => key + " - " + sizeCounts[key]);
    jQuery('#film_size').val(filmSizeOutput.join(', ') + " = " + totalFilmSizeQty);

    // 3. total_area: Dynamic summation grouped by Source (e.g., Ir-192 - 180.00 = 180.00)
    let sourceAreaSums = {};
    rows.forEach(r => {
        let source = r.source_id_fix || '';
        if (source) {
            let area = parseFloat(film_size_fix === 'inch' ? r.total_sq_in : r.total_sq_cm);
            let validArea = isNaN(area) ? 0 : area;
            if (sourceAreaSums[source]) {
                sourceAreaSums[source] += validArea;
            } else {
                sourceAreaSums[source] = validArea;
            }
        }
    });
    let sortedAreaSources = Object.keys(sourceAreaSums).sort((a, b) => getSourceRank(a) - getSourceRank(b));
    let totalAreaOutput = [];
    let totalAreaSum = 0;
    sortedAreaSources.forEach(source => {
        let sum = sourceAreaSums[source];
        if (sum > 0) {
            totalAreaOutput.push(source + " - " + sum.toFixed(2));
            totalAreaSum += sum;
        }
    });
    jQuery('#total_area').val(totalAreaOutput.join(', ') + " = " + totalAreaSum.toFixed(2));

    // 4. film_brand: Comma-separated unique film brand names
    let brands = rows.map(r => r.film_brand_name);
    let uniqueBrands = [...new Set(brands.filter(b => b != ''))];
    jQuery('#film_brand').val(uniqueBrands.join(', '));

    // 5. film_type: Comma-separated unique film type names
    let types = rows.map(r => r.film_type_name);
    let uniqueTypes = [...new Set(types.filter(t => t != ''))];
    jQuery('#film_type').val(uniqueTypes.join(', '));

    // 6. source_used: Comma-separated unique sources
    let sources = rows.map(r => r.source_id_fix);
    let uniqueSources = [...new Set(sources.filter(s => s != ''))];
    uniqueSources.sort((a, b) => getSourceRank(a) - getSourceRank(b));
    jQuery('#source_used').val(uniqueSources.join(', '));

    // 7. test_technique: Comma-separated unique test techniques
    let techniques = rows.map(r => r.test_technique);
    let uniqueTechniques = [...new Set(techniques.filter(t => t && t.trim() !== ''))];
    jQuery('#test_technique').val(uniqueTechniques.join(', '));

    updateSourceFieldsState();
}

// Track unit changes to update grid & header
jQuery(document).on('change', 'input[name="film_size_fix"]', function () {
    fillTSDetailTable();
    updateFilmSizeDropdown();
});

jQuery(document).on('change', 'input[name="sfd_unit_fix"]', function () {
    fillTSDetailTable();
});

// Auto-fill drawing no. when part_id changes
// jQuery(document).on('change', '#part_id', function () {
//     let selectedOpt = jQuery(this).find('option:selected');
//     if (selectedOpt.val()) {
//         jQuery('#drg_no').val(selectedOpt.attr('data-drg_no') || '');
//     } else {
//         jQuery('#drg_no').val('');
//     }
// });


jQuery(document).on('click', '#part_no_list .list-group-item', function () {
    jQuery('#part_no').trigger('change');
});

// Auto sequence fetch / duplication checking
function getLatestTSSequence() {
    jQuery.ajax({
        url: 'get-latest-technique_sheet_number',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#technique_sheet_rt_sequence').val(data.number);
                jQuery('#technique_sheet_rt_no').val(data.latest_no);
                let dateVal = (typeof currentDate !== 'undefined') ? currentDate : new Date().toLocaleDateString('en-GB');
                jQuery('#technique_sheet_rt_date').val(dateVal);
            }
        }
    });
}

function getTSLNRData() {
    jQuery.ajax({
        url: "get-technique_sheet_rt_lnr_data",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.lnr_data != null) {
                var lnr = data.lnr_data;
                jQuery('#lead_screen_thick').val(lnr.lead_screen_thick ?? "");
                jQuery('#lead_screen_thick_back').val(lnr.lead_screen_thick_back ?? "");
            }
        },
        error: function () {
            console.log('Error fetching LNR data');
        }
    });
}

function checkSequenceDuplication() {
    let seq = jQuery('#technique_sheet_rt_sequence').val();
    if (seq != "") {
        if (seq > 0 == false) {
            toastr.error('Please Enter Valid Sr. No.');
            jQuery('#technique_sheet_rt_sequence').val('');
            jQuery('#technique_sheet_rt_no').val('');
            const input = document.getElementById('technique_sheet_rt_sequence');
            input?.focus();
        } else {
            jQuery('#technique_sheet_rt_sequence').addClass('file-loader');
            jQuery('#submitbtn, #updatebtn').prop('disabled', true);
            let id = jQuery('#id').val();
            jQuery.ajax({
                url: 'check-technique_sheet_number_duplication',
                type: 'GET',
                data: { technique_sheet_rt_sequence: seq, id: id },
                dataType: 'json',
                success: function (data) {
                    jQuery('#technique_sheet_rt_sequence').removeClass('file-loader');
                    jQuery('#submitbtn, #updatebtn').prop('disabled', false);
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        jQuery('#technique_sheet_rt_sequence').val('');
                        // jQuery('#technique_sheet_rt_no').val('');
                        const input = document.getElementById('technique_sheet_rt_sequence');
                        input?.focus();
                    } else {
                        jQuery('#technique_sheet_rt_no').val(data.latest_no);
                        jQuery('#technique_sheet_rt_sequence').val(seq);
                    }
                },
                error: function () {
                    jQuery('#technique_sheet_rt_sequence').removeClass('file-loader');
                    jQuery('#submitbtn, #updatebtn').prop('disabled', false);
                    toastr.error('Something went wrong!');
                }
            });
        }
    } else {
        jQuery('#technique_sheet_rt_no').val('');
        jQuery('#technique_sheet_rt_sequence').val('');
    }
}

jQuery('#commonTechniqueSheetRtForm').find('#technique_sheet_rt_sequence').on('change', function () {
    checkSequenceDuplication();
});

// Get next Sr. No. for new details row
function getNextSrNo() {
    let activeRows = technique_sheet_rt_details_data.filter(r => r.mode !== 'Delete');
    if (activeRows.length === 0) return 1;
    let maxSr = Math.max(...activeRows.map(r => parseFloat(r.sr_no || 0)));
    return Math.floor(maxSr) + 1;
}

// Reset entire header and detail form
function resetTechniqueSheetRtForm() {
    document.getElementById("commonTechniqueSheetRtForm").reset();
    const form = document.getElementById("commonTechniqueSheetRtForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    jQuery('#id').val('');
    jQuery('#technique_sheet_rt_sequence').val('');
    jQuery('#technique_sheet_rt_no').val('');
    jQuery('input[name="entry_type_fix"][value="Manual"]').prop('checked', true);
    setRadioReadonly('input[name*="entry_type_fix"]', false);
    setSelect2Readonly('#TechniqueSheetRtModal #customer_id', false);
    jQuery('#customer_id').val('').trigger('change');
    jQuery('#type_of_job_id').val('').trigger('change');
    jQuery('#job_desc_id').val('').trigger('change');
    // jQuery('#part_id').val('').trigger('change');
    jQuery('#part_no').val('');
    jQuery('#drg_no').val('');
    jQuery('#area_of_coverage_id').val('').trigger('change');

    jQuery('#procedure_ref_id').val('').trigger('change');
    jQuery('#evaluation_as_per_id').val('').trigger('change');
    jQuery('#acceptance_standard_id').val('').trigger('change');

    jQuery('#film_size_fix_inch').prop('checked', true);
    jQuery('#sfd_unit_fix_mm').prop('checked', true);
    setRadioReadonly('input[name="sfd_unit_fix"]', false);

    // Reset file fields
    jQuery('#shooting_sketch_image_doc').val('');
    jQuery('#shooting_sketch_image_file').val('');
    jQuery('#shooting_sketch_image_prev').attr('href', '#').addClass('hide');
    jQuery('#shooting_sketch_image_remove').addClass('hide').removeClass('i-block');

    jQuery('#prepared_by_user_id').val(loginUserId).trigger('change');
    jQuery('#checked_by_authority_person_id').val('').trigger('change');
    jQuery('#authorized_by_authority_person_id').val('').trigger('change');

    jQuery('#preview_btn').hide();
    jQuery('#add_new').hide();
    jQuery('#submitbtn').text('Submit');
    jQuery('.toggleBtn').prop('disabled', false);
    jQuery('.toggleModalBtn').prop('disabled', true);
    jQuery('#copyAllBtn').prop('disabled', true);
    technique_sheet_rt_details_data = [];
    fillTSDetailTable();

    getLatestTSSequence();
    getTSLNRData();
}

// Reset button click
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    let formId = jQuery('#id').val();
    if (!formId) {
        resetTechniqueSheetRtForm();
        setTimeout(function () {
            jQuery('#TechniqueSheetRtModal').find('input[name="entry_type_fix"]:checked').focus();
        }, 50);
    } else {
        fetchAndFillTechniqueSheetRt(formId);
    }
});

// Edit listing item click
jQuery('#dyntable tbody').on('click', '.edit-technique_sheet_rt', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#TechniqueSheetRtModal').find('#id').val(data["id"]);
    if (data && data["id"]) {
        fetchAndFillTechniqueSheetRt(data["id"]);
    }
});

// Fetch Technique Sheet details for Edit
function fetchAndFillTechniqueSheetRt(id) {
    if (!id) return;
    _openForEdit = true; // Prevent getLatestTSSequence() in shown.bs.modal
    jQuery('#TechniqueSheetRtModal').modal('show');
    skipLoader = false;
    showLoader();
    jQuery.ajax({
        url: "edit-technique_sheet_rt",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.ts_data != null) {
                var d = data.ts_data;
                jQuery('#TechniqueSheetRtModal #id').val(d.technique_sheet_rt_id);
                jQuery('#TechniqueSheetRtModal #test_report_rt_id').val(d.test_report_rt_id);

                let url = checkFileRoute + "?id=" + btoa(d.technique_sheet_rt_id) + "&name=" + d.pdf_name + "&type=technique_sheet_rt";
                jQuery('#preview_btn').attr('href', url).show();
                jQuery('#technique_sheet_rt_sequence').val(d.technique_sheet_rt_sequence);
                jQuery('#technique_sheet_rt_no').val(d.technique_sheet_rt_no);
                jQuery('#technique_sheet_rt_date').val(d.technique_sheet_rt_date);
                jQuery(`input[name="entry_type_fix"][value="${d.entry_type_fix}"]`).prop('checked', true).change();
                jQuery('#customer_id').val(zeroToEmpty(d.customer_id)).trigger('change');
                setTimeout(() => {
                    setSelect2Readonly('#TechniqueSheetRtModal #customer_id', true);
                }, 500);
                jQuery('#type_of_job_id').val(zeroToEmpty(d.type_of_job_id)).trigger('change');
                // Set part_id value BEFORE triggering job_desc_id change
                // so filterPartsByJobDesc() can read and restore the correct part selection
                // jQuery('#part_id').val(zeroToEmpty(d.part_id));
                jQuery('#part_no').val(d.part_no || '');
                jQuery('#job_desc_id').val(zeroToEmpty(d.job_desc_id)).trigger('change');
                // After filtering, re-apply part_id and update Select2
                // jQuery('#part_id').val(zeroToEmpty(d.part_id)).trigger('change.select2');
                jQuery('#drg_no').val(d.drg_no || '');
                jQuery('#area_of_coverage_id').val(zeroToEmpty(d.area_of_coverage_id)).trigger('change');

                jQuery('#source_size').val(d.source_size);
                jQuery('#xray_focal_size').val(d.xray_focal_size);
                jQuery('#lead_screen_thick').val(d.lead_screen_thick);
                jQuery('#lead_screen_thick_back').val(d.lead_screen_thick_back || '');
                jQuery('#iqi').val(d.iqi);
                jQuery('#film_processing').val(d.film_processing);
                jQuery('#test_technique').val(d.test_technique);
                jQuery('#test_arrangement').val(d.test_arrangement);
                jQuery('#test_class').val(d.test_class);

                jQuery('#customer_procedure_ref').val(d.customer_procedure_ref);
                jQuery('#procedure_ref_id').val(zeroToEmpty(d.procedure_ref_id)).trigger('change');
                jQuery('#evaluation_as_per_id').val(zeroToEmpty(d.evaluation_as_per_id)).trigger('change');
                jQuery('#acceptance_standard_id').val(zeroToEmpty(d.acceptance_standard_id)).trigger('change');

                if (d.film_size_fix === 'cm') {
                    jQuery('#film_size_fix_cm').prop('checked', true);
                } else {
                    jQuery('#film_size_fix_inch').prop('checked', true);
                }

                if (d.sfd_unit_fix === 'inch') {
                    jQuery('#sfd_unit_fix_inch').prop('checked', true);
                } else {
                    jQuery('#sfd_unit_fix_mm').prop('checked', true);
                }
                setRadioReadonly('input[name="sfd_unit_fix"]', false);

                // File Sketch field reset
                jQuery('#shooting_sketch_image_doc').val('');
                jQuery('#shooting_sketch_image_file').val('');
                jQuery('#shooting_sketch_image_prev').attr('href', '#').text('View').addClass('hide');
                jQuery('#shooting_sketch_image_remove').addClass('hide').removeClass('i-block');

                if (d.shooting_sketch_image) {
                    let fullPath = d.shooting_sketch_image;
                    let fileName = fullPath.split('/').pop();
                    jQuery('#shooting_sketch_image_doc').val(fullPath);
                    jQuery('#shooting_sketch_image_prev').attr('href', uploadURL + fullPath).text('View').removeClass('hide');
                    jQuery('#shooting_sketch_image_remove').addClass('i-block').removeClass('hide');

                    let fileInput = jQuery('#shooting_sketch_image_file');
                    let newFile = new DataTransfer();
                    newFile.items.add(new File([""], fileName));
                    fileInput[0].files = newFile.files;
                }

                jQuery('#prepared_by_user_id').val(zeroToEmpty(d.prepared_by_user_id)).trigger('change');
                jQuery('#checked_by_authority_person_id').val(zeroToEmpty(d.checked_by_authority_person_id)).trigger('change');
                jQuery('#authorized_by_authority_person_id').val(zeroToEmpty(d.authorized_by_authority_person_id)).trigger('change');
                jQuery('#sp_note').val(d.sp_note);

                // Load Details grid
                technique_sheet_rt_details_data = [];
                if (data.ts_details_data && data.ts_details_data.length > 0) {
                    data.ts_details_data.forEach(row => {
                        technique_sheet_rt_details_data.push({
                            technique_sheet_rt_details_id: row.technique_sheet_rt_details_id,
                            sr_no: parseFloat(row.sr_no || 0),
                            identification: row.identification,
                            location: row.location,
                            source_id_fix: row.source_id_fix,
                            film_brand_id: row.film_brand_id,
                            film_brand_name: row.film_brand,
                            film_type_id: row.film_type_id,
                            film_type_name: row.film_type,
                            thickness: row.thickness,
                            sfd: row.sfd,
                            // iqi_designation_id: row.iqi_designation_id,
                            iqi_designation: row.iqi_designation || row.iqi_designation_name || '',
                            iqi_designation_name: row.iqi_designation || row.iqi_designation_name || '',
                            // iqi_sensitivity_id: row.iqi_sensitivity_id,
                            iqi_sensitivity: row.iqi_sensitivity || row.iqi_sensitivity_name || '',
                            iqi_sensitivity_name: row.iqi_sensitivity || row.iqi_sensitivity_name || '',
                            film_id: row.film_id,
                            film_size_inch: row.film_size_inch,
                            film_size_cm: row.film_size_cm,
                            no_of_film_fix: row.no_of_film_fix,
                            film_qty: row.film_qty,
                            sq_in: parseFloat(row.sq_in || 0),
                            sq_cm: parseFloat(row.sq_cm || 0),
                            total_sq_in: parseFloat(row.total_sq_in || 0),
                            total_sq_cm: parseFloat(row.total_sq_cm || 0),
                            test_technique: row.test_technique || '',
                            film_position: row.film_position || '',
                            mode: 'Update'
                        });
                    });
                }
                fillTSDetailTable();

                jQuery('#add_new').show();
                jQuery('#submitbtn').text('Update');
                const form = document.getElementById("commonTechniqueSheetRtForm");
                if (form) form.classList.remove('was-validated');

                setTimeout(() => {
                    jQuery('#technique_sheet_rt_sequence').focus().select();
                }, 100);
            } else {
                toastr.error(data.response_message || 'Error fetching data.');
            }
        },
        error: function () {
            toastr.error('Something went wrong!');
        },
        complete: function () {
            hideLoader();
        }
    });
}

// Store pending edit data for TSDetailsModal
var _pendingEditData = null;

// TSDetailsModal shown event: set values after Select2 is initialized
jQuery('#TSDetailsModal').on('shown.bs.modal', function () {
    let thisForm = jQuery('#TSDetailsModal');
    let sfdUnit = jQuery('input[name="sfd_unit_fix"]:checked').val() || 'mm';
    thisForm.find('label[for="detail_sfd"]').html('SFD (' + sfdUnit + ') <sup class="astric">*</sup>');

    if (_pendingEditData) {
        let frmData = _pendingEditData;
        _pendingEditData = null;

        thisForm.find('#detail_sr_no').val(frmData.sr_no);
        thisForm.find('#detail_identification').val(frmData.identification);
        thisForm.find('#detail_location').val(frmData.location);

        thisForm.find('#detail_source_id_fix').val(frmData.source_id_fix).trigger('change').trigger('change.select2');
        thisForm.find('#detail_film_brand_id').val(frmData.film_brand_id).trigger('change').trigger('change.select2');
        thisForm.find('#detail_film_type_id').val(frmData.film_type_id).trigger('change').trigger('change.select2');

        thisForm.find('#detail_thickness').val(frmData.thickness);
        thisForm.find('#detail_sfd').val(frmData.sfd);
        thisForm.find('#detail_test_technique').val(frmData.test_technique || '');
        thisForm.find('#detail_film_position').val(frmData.film_position || '');

        // thisForm.find('#detail_iqi_designation_id').val(frmData.iqi_designation_id).trigger('change').trigger('change.select2');
        thisForm.find('#iqi_designation').val(frmData.iqi_designation || frmData.iqi_designation_name || '');
        // thisForm.find('#detail_iqi_sensitivity_id').val(frmData.iqi_sensitivity_id).trigger('change').trigger('change.select2');
        thisForm.find('#iqi_sensitivity').val(frmData.iqi_sensitivity || frmData.iqi_sensitivity_name || '');
        let mainReportId = jQuery('#id').val() || jQuery('#commonTechniqueSheetRtForm #id').val();
        let isCopiedRow = (frmData.is_copy === true || frmData.is_copied === true);
        let isExistingSavedRow = (mainReportId && mainReportId != 0 && mainReportId != '0') || (frmData.technique_sheet_rt_details_id && frmData.technique_sheet_rt_details_id != 0);
        let isExistingOrPendingRow = isExistingSavedRow || frmData.from_pending === true;

        let filmSelect = thisForm.find('#detail_film_id');
        filmSelect.find('.temp-option').remove();
        let filmId = zeroToEmpty(frmData.film_id || frmData.detail_film_id);

        if (isExistingOrPendingRow && !isCopiedRow && filmId) {
            if (filmSelect.find('option[value="' + filmId + '"]').length === 0) {
                let inch = frmData.film_size_inch || frmData.film_size || '';
                let cm = frmData.film_size_cm || '';
                let filmSizeUnit = jQuery('input[name="film_size_fix"]:checked').val() || jQuery('input[name="film_size_unit_fix"]:checked').val() || 'inch';
                let displayName = (filmSizeUnit === 'cm') ? (cm || inch || 'Film Size') : (inch || cm || 'Film Size');
                let opt = new Option(displayName, filmId, true, true);
                jQuery(opt).addClass('temp-option')
                    .attr('data-inch', inch)
                    .attr('data-cm', cm)
                    .attr('data-sq_in', frmData.sq_in || '')
                    .attr('data-sq_cm', frmData.sq_cm || '');
                filmSelect.append(opt);
            }
            filmSelect.val(filmId).trigger('change').trigger('change.select2');
        } else if (filmId && filmSelect.find('option[value="' + filmId + '"]:not(.temp-option)').length > 0) {
            filmSelect.val(filmId).trigger('change').trigger('change.select2');
        } else {
            filmSelect.val('').trigger('change').trigger('change.select2');
        }
        thisForm.find('#detail_no_of_film_fix').val(frmData.no_of_film_fix).trigger('change').trigger('change.select2');

        updateFilmSizeDropdown();
    } else {
        // Add mode: set defaults
        let formType = thisForm.find('#form_type').val();
        if (formType === 'add') {
            thisForm.find('#detail_no_of_film_fix').val('Single').trigger('change').trigger('change.select2');
            updateFilmSizeDropdown();
        }
    }

    // Clear copy from sr no input
    jQuery('#copy_from_sr_no').val('');

    // Focus on first field
    setTimeout(() => {
        thisForm.find('#detail_sr_no').focus().select();
    }, 100);
});

// Copy data from specified Sr. No. on Blur / Change
jQuery(document).on('blur change', '#copy_from_sr_no', function () {
    let copySrNo = jQuery(this).val().trim();
    if (!copySrNo) return;

    let targetRow = technique_sheet_rt_details_data.find(r => r.mode !== 'Delete' && r.sr_no == copySrNo);
    if (targetRow) {
        let currentSrNo = jQuery('#detail_sr_no').val();

        jQuery('#detail_identification').val(targetRow.identification || '');
        jQuery('#detail_location').val(targetRow.location || '');
        jQuery('#detail_source_id_fix').val(zeroToEmpty(targetRow.source_id_fix)).trigger('change').trigger('change.select2');
        jQuery('#detail_film_brand_id').val(zeroToEmpty(targetRow.film_brand_id || targetRow.detail_film_brand_id)).trigger('change').trigger('change.select2');
        jQuery('#detail_film_type_id').val(zeroToEmpty(targetRow.film_type_id || targetRow.detail_film_type_id)).trigger('change').trigger('change.select2');
        jQuery('#detail_thickness').val(targetRow.thickness || '');
        jQuery('#detail_sfd').val(targetRow.sfd || '');
        jQuery('#detail_test_technique').val(targetRow.test_technique || '');
        jQuery('#detail_film_position').val(targetRow.film_position || '');
        // jQuery('#detail_iqi_designation_id').val(zeroToEmpty(targetRow.iqi_designation_id || targetRow.detail_iqi_designation_id)).trigger('change').trigger('change.select2');
        jQuery('#iqi_designation').val(targetRow.iqi_designation || targetRow.iqi_designation_name || '');
        // jQuery('#detail_iqi_sensitivity_id').val(zeroToEmpty(targetRow.iqi_sensitivity_id || targetRow.detail_iqi_sensitivity_id)).trigger('change').trigger('change.select2');
        jQuery('#iqi_sensitivity').val(targetRow.iqi_sensitivity || targetRow.iqi_sensitivity_name || '');
        let mainReportId = jQuery('#commonTechniqueSheetRtForm #id').val();
        let isExistingSavedRow = (mainReportId && mainReportId != 0 && mainReportId != '0' && targetRow.technique_sheet_rt_details_id && targetRow.technique_sheet_rt_details_id != 0);

        let targetFilmId = zeroToEmpty(targetRow.film_id || targetRow.detail_film_id);
        let filmSelect = jQuery('#detail_film_id');
        if (isExistingSavedRow && targetFilmId && filmSelect.find('option[value="' + targetFilmId + '"]').length > 0) {
            filmSelect.val(targetFilmId).trigger('change').trigger('change.select2');
        } else if (targetFilmId && filmSelect.find('option[value="' + targetFilmId + '"]:not(.temp-option)').length > 0) {
            filmSelect.val(targetFilmId).trigger('change').trigger('change.select2');
        } else {
            filmSelect.val('').trigger('change').trigger('change.select2');
        }
        jQuery('#detail_no_of_film_fix').val(zeroToEmpty(targetRow.no_of_film_fix)).trigger('change').trigger('change.select2');
        updateFilmSizeDropdown();

        // Maintain latest Sr. No. (Do not overwrite with copied Sr. No.)
        jQuery('#detail_sr_no').val(currentSrNo);

        // Clear Copy From Sr. No. input after copy
        jQuery(this).val('');
    } else {
        jQuery(this).val('');
    }
});

// Tab key navigation from Copy From Sr. No. directly to Sr. No.
jQuery(document).on('keydown', '#copy_from_sr_no', function (e) {
    if (e.key === 'Tab' && !e.shiftKey) {
        e.preventDefault();
        jQuery(this).trigger('blur');
        setTimeout(() => {
            jQuery('#detail_sr_no').focus().select();
        }, 50);
    }
});

// Add row trigger click
jQuery(document).on('click', '#addDetailRowBtn', function () {
    let formElement = document.getElementById('TSDetailsForm');
    formElement.reset();
    jQuery('#TSDetailsForm').removeClass('was-validated');
    jQuery('#copy_from_sr_no').val('');
    jQuery('#detail_test_technique').val('');
    jQuery('#detail_film_position').val('');
    jQuery('#iqi_designation').val('');
    jQuery('#iqi_sensitivity').val('');
    jQuery('#detail_film_id').find('.temp-option').remove();
    jQuery('#detail_film_id').val('').trigger('change').trigger('change.select2');

    jQuery('#form_type').val('add');
    jQuery('#form_index').val('');
    jQuery('#row_index').val('');
    jQuery('#technique_sheet_rt_details_id').val('0');
    jQuery('#submitDetailRowBtn').text('Add');

    // Auto set next Sr No
    jQuery('#detail_sr_no').val(getNextSrNo());

    _pendingEditData = null;
    jQuery('#TSDetailsModal').modal('show');
});

// Load a specific detail row into TSDetailsModal by its index in technique_sheet_rt_details_data
function loadTSDetailByIndex(index) {
    let frmData = technique_sheet_rt_details_data[index];
    if (!frmData || frmData.mode === 'Delete') return false;

    let thisForm = jQuery('#TSDetailsModal');
    jQuery('#TSDetailsForm').removeClass('was-validated');

    thisForm.find('#form_type').val('edit');
    thisForm.find('#form_index').val(index);
    thisForm.find('#technique_sheet_rt_details_id').val(frmData.technique_sheet_rt_details_id || 0);
    jQuery('#submitDetailRowBtn').text('Edit');

    thisForm.find('#detail_sr_no').val(frmData.sr_no);
    thisForm.find('#detail_identification').val(frmData.identification || '');
    thisForm.find('#detail_location').val(frmData.location || '');

    thisForm.find('#detail_source_id_fix').val(frmData.source_id_fix || '').trigger('change').trigger('change.select2');
    thisForm.find('#detail_film_brand_id').val(frmData.film_brand_id || '').trigger('change').trigger('change.select2');
    thisForm.find('#detail_film_type_id').val(frmData.film_type_id || '').trigger('change').trigger('change.select2');

    thisForm.find('#detail_thickness').val(frmData.thickness || '');
    thisForm.find('#detail_sfd').val(frmData.sfd || '');
    thisForm.find('#detail_test_technique').val(frmData.test_technique || '');
    thisForm.find('#detail_film_position').val(frmData.film_position || '');

    // thisForm.find('#detail_iqi_designation_id').val(frmData.iqi_designation_id || '').trigger('change').trigger('change.select2');
    thisForm.find('#iqi_designation').val(frmData.iqi_designation || frmData.iqi_designation_name || '');
    // thisForm.find('#detail_iqi_sensitivity_id').val(frmData.iqi_sensitivity_id || '').trigger('change').trigger('change.select2');
    thisForm.find('#iqi_sensitivity').val(frmData.iqi_sensitivity || frmData.iqi_sensitivity_name || '');

    let mainReportId = jQuery('#id').val() || jQuery('#commonTechniqueSheetRtForm #id').val();
    let isCopiedRow = (frmData.is_copy === true || frmData.is_copied === true);
    let isExistingSavedRow = (mainReportId && mainReportId != 0 && mainReportId != '0') || (frmData.technique_sheet_rt_details_id && frmData.technique_sheet_rt_details_id != 0);
    let isExistingOrPendingRow = isExistingSavedRow || frmData.from_pending === true;

    let filmSelect = thisForm.find('#detail_film_id');
    filmSelect.find('.temp-option').remove();
    let filmId = zeroToEmpty(frmData.film_id || frmData.detail_film_id);

    if (isExistingOrPendingRow && !isCopiedRow && filmId) {
        if (filmSelect.find('option[value="' + filmId + '"]').length === 0) {
            let inch = frmData.film_size_inch || frmData.film_size || '';
            let cm = frmData.film_size_cm || '';
            let filmSizeUnit = jQuery('input[name="film_size_fix"]:checked').val() || jQuery('input[name="film_size_unit_fix"]:checked').val() || 'inch';
            let displayName = (filmSizeUnit === 'cm') ? (cm || inch || 'Film Size') : (inch || cm || 'Film Size');
            let opt = new Option(displayName, filmId, true, true);
            jQuery(opt).addClass('temp-option')
                .attr('data-inch', inch)
                .attr('data-cm', cm)
                .attr('data-sq_in', frmData.sq_in || '')
                .attr('data-sq_cm', frmData.sq_cm || '');
            filmSelect.append(opt);
        }
        filmSelect.val(filmId).trigger('change').trigger('change.select2');
    } else if (filmId && filmSelect.find('option[value="' + filmId + '"]:not(.temp-option)').length > 0) {
        filmSelect.val(filmId).trigger('change').trigger('change.select2');
    } else {
        filmSelect.val('').trigger('change').trigger('change.select2');
    }
    thisForm.find('#detail_no_of_film_fix').val(frmData.no_of_film_fix || 'Single').trigger('change').trigger('change.select2');

    updateFilmSizeDropdown();

    // Clear copy from sr no input
    jQuery('#copy_from_sr_no').val('');

    setTimeout(() => {
        thisForm.find('#detail_sr_no').focus().select();
    }, 100);

    return true;
}

// Edit Details Item Click
function editTSDetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();

    let thisForm = jQuery('#TSDetailsModal');
    let frmData = technique_sheet_rt_details_data[formIndx];

    jQuery('#TSDetailsForm').removeClass('was-validated');

    thisForm.find('#form_type').val('edit');
    thisForm.find('#form_index').val(formIndx);
    thisForm.find('#row_index').val(rawIndx);
    thisForm.find('#technique_sheet_rt_details_id').val(frmData.technique_sheet_rt_details_id || 0);
    jQuery('#submitDetailRowBtn').text('Edit');

    // Store data to set in shown.bs.modal after Select2 initializes
    _pendingEditData = frmData;

    // Show modal - values set in shown.bs.modal event
    thisForm.modal('show');
}

// Remove Details Item Click
function removeTSDetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        let item = technique_sheet_rt_details_data[formIndx];
        if (item.technique_sheet_rt_details_id && item.technique_sheet_rt_details_id != 0) {
            item.mode = "Delete";
        } else {
            technique_sheet_rt_details_data.splice(formIndx, 1);
        }
        resequenceTSDetails(true);
        fillTSDetailTable();
    });
}

// Submit Details Subform
jQuery('#submitDetailRowBtn').on('click', function (e) {
    e.preventDefault();
    let form = document.getElementById('TSDetailsForm');

    if (!form.checkValidity()) {
        e.stopPropagation();
        jQuery(form).addClass('was-validated');
        return;
    }

    let sr_no = parseFloat(jQuery('#detail_sr_no').val() || 0);
    let form_type = jQuery('#form_type').val();
    let form_index = jQuery('#form_index').val();

    // Shift serial numbers if there's a duplicate
    let duplicateExists = technique_sheet_rt_details_data.some((row, index) => {
        if (row.mode === 'Delete') return false;
        if (form_type === 'edit' && index == form_index) return false;
        return row.sr_no == sr_no;
    });

    if (duplicateExists) {
        technique_sheet_rt_details_data.forEach((row, index) => {
            if (row.mode === 'Delete') return;
            if (form_type === 'edit' && index == form_index) return;
            if (row.sr_no >= sr_no) {
                row.sr_no = row.sr_no + 1;
            }
        });
    }

    // Film Qty calculation (Single=1, Double=2, Triple=3, Quadra=4)
    let no_of_film_fix = jQuery('#detail_no_of_film_fix').val();
    let film_qty = 1;
    if (no_of_film_fix === 'Double') film_qty = 2;
    else if (no_of_film_fix === 'Triple') film_qty = 3;
    else if (no_of_film_fix === 'Quadra') film_qty = 4;

    let selectedFilm = jQuery('#detail_film_id option:selected');
    let sq_in = parseFloat(selectedFilm.attr('data-sq_in') || 0);
    let sq_cm = parseFloat(selectedFilm.attr('data-sq_cm') || 0);

    let formValue = {
        technique_sheet_rt_details_id: jQuery('#technique_sheet_rt_details_id').val() || 0,
        sr_no: sr_no,
        identification: jQuery('#detail_identification').val(),
        location: jQuery('#detail_location').val(),
        source_id_fix: jQuery('#detail_source_id_fix').val(),
        film_brand_id: jQuery('#detail_film_brand_id').val(),
        film_brand_name: jQuery('#detail_film_brand_id option:selected').text().trim(),
        film_type_id: jQuery('#detail_film_type_id').val(),
        film_type_name: jQuery('#detail_film_type_id option:selected').text().trim(),
        thickness: jQuery('#detail_thickness').val(),
        sfd: jQuery('#detail_sfd').val(),
        // iqi_designation_id: '',
        iqi_designation: jQuery('#iqi_designation').val().trim(),
        iqi_designation_name: jQuery('#iqi_designation').val().trim(),
        // iqi_sensitivity_id: '',
        iqi_sensitivity: jQuery('#iqi_sensitivity').val().trim(),
        iqi_sensitivity_name: jQuery('#iqi_sensitivity').val().trim(),
        film_id: jQuery('#detail_film_id').val(),
        film_size_inch: selectedFilm.attr('data-inch') || '',
        film_size_cm: selectedFilm.attr('data-cm') || '',
        no_of_film_fix: no_of_film_fix,
        film_qty: film_qty,
        sq_in: sq_in,
        sq_cm: sq_cm,
        total_sq_in: sq_in * film_qty,
        total_sq_cm: sq_cm * film_qty,
        test_technique: jQuery('#detail_test_technique').val() || '',
        film_position: jQuery('#detail_film_position').val() || '',
    };

    if (form_type === 'edit') {
        formValue.mode = (formValue.technique_sheet_rt_details_id == 0) ? 'Insert' : 'Update';
        technique_sheet_rt_details_data[form_index] = formValue;
        resequenceTSDetails(false);
        fillTSDetailTable();
        toastSuccess("Record Updated.");

        // Continuous Edit: find the next active row by serial number
        let currentSrNo = parseFloat(formValue.sr_no || 0);
        let nextRowCandidate = null;
        let nextRowIndex = -1;

        technique_sheet_rt_details_data.forEach((row, idx) => {
            if (row.mode === 'Delete') return;
            let rowSrNo = parseFloat(row.sr_no || 0);
            if (rowSrNo > currentSrNo) {
                if (nextRowCandidate === null || rowSrNo < parseFloat(nextRowCandidate.sr_no || 0)) {
                    nextRowCandidate = row;
                    nextRowIndex = idx;
                }
            }
        });

        if (nextRowCandidate !== null && nextRowIndex !== -1) {
            // Next row found: load next row directly without closing modal
            loadTSDetailByIndex(nextRowIndex);
        } else {
            // Last row reached: close modal
            jQuery('#TSDetailsModal').modal('hide');
        }
        return;
    } else {
        formValue.mode = 'Insert';
        technique_sheet_rt_details_data.push(formValue);

        let locVal = jQuery('#detail_location').val();
        let nextLocVal = checkValue(locVal);

        resequenceTSDetails(false);

        // Keep entered values for rapid next entry (do not reset the entire form)
        jQuery('#detail_sr_no').val(getNextSrNo());
        jQuery('#detail_location').val(nextLocVal);
        jQuery('#TSDetailsForm').removeClass('was-validated');
        setTimeout(() => {
            jQuery('#detail_sr_no').focus().select();
        }, 100);

        toastSuccess("Record Inserted.");
    }

    fillTSDetailTable();
});

// File upload validation & handler
function validateImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i;
    return allowedExtensions.exec(filePath) ? true : false;
}

jQuery(document).on('change', '#shooting_sketch_image_file', function (e) {
    TechniqueSheetRtFileUpload(e);
});

function TechniqueSheetRtFileUpload(e) {
    var form_data = new FormData();
    var target = e.target;
    var id = target.id;
    var files = target.files;
    var totalfiles = files.length;
    var oldImg = jQuery('#shooting_sketch_image_doc').val();

    if (totalfiles > 0) {
        var notValid = 0;
        for (var index = 0; index < totalfiles; index++) {
            if (validateImage(files[index].name) == true) {
                form_data.append("docs[]", files[index]);
            } else {
                notValid = 1;
                toastr.error("Only (jpeg,jpg,png,gif) files are allowed.");
                jQuery('#shooting_sketch_image_doc').val('');
                jQuery('#shooting_sketch_image_file').val('');
                jQuery('#shooting_sketch_image_prev').attr('href', '#').addClass('hide');
                jQuery('#shooting_sketch_image_remove').removeClass('i-block').addClass('hide');
                return false;
            }
        }

        if (notValid == 0) {
            jQuery('#submitbtn').prop('disabled', true);
            jQuery('#shooting_sketch_image_file').addClass('file-loader');
            jQuery.ajax({
                url: 'upload-docs', // defined globally in modal push
                type: 'POST',
                data: form_data,
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    jQuery('#submitbtn').prop('disabled', false);
                    jQuery('#shooting_sketch_image_file').removeClass('file-loader');
                    if (data.response_code == 1) {
                        if (oldImg != "" && oldImg.includes('temp_media/')) {
                            removeMedia(oldImg);
                        }

                        jQuery('#shooting_sketch_image_doc').val(data.files);
                        jQuery('#shooting_sketch_image_prev').attr('href', data.files_url).text('View').removeClass('hide');
                        jQuery('#shooting_sketch_image_remove').removeClass('hide').addClass('i-block');
                        console.log(data.response_message);
                    } else {
                        toastr.error(data.response_message);
                    }
                },
                error: function (jqXHR) {
                    jQuery('#submitbtn').prop('disabled', false);
                    jQuery('#shooting_sketch_image_file').removeClass('file-loader');
                    toastr.error('Something went wrong with file upload!');
                }
            });
        }
    } else {
        if (oldImg != "") {
            let fileName = oldImg.split('/').pop();
            let fileInput = jQuery('#shooting_sketch_image_file');
            let dataTransfer = new DataTransfer();
            dataTransfer.items.add(new File([""], fileName));
            fileInput[0].files = dataTransfer.files;
            return false;
        }
        jQuery('#shooting_sketch_image_doc').val('');
        jQuery('#shooting_sketch_image_file').val('');
        jQuery('#shooting_sketch_image_prev').attr('href', '#').text('View').addClass('hide');
        jQuery('#shooting_sketch_image_remove').addClass('hide').removeClass('i-block');
    }
}

function removeFileTechniqueSheetRt(e) {
    e.stopImmediatePropagation();
    toastDetailDelete('Are You Sure, You Want To Delete ?', () => {
        var oldImg = jQuery('#shooting_sketch_image_doc').val();

        if (oldImg != "" && oldImg.includes('temp_media/')) {
            removeMedia(oldImg);
        }

        jQuery('#shooting_sketch_image_doc').val('');
        jQuery('#shooting_sketch_image_file').val('');

        jQuery('#shooting_sketch_image_prev').attr('href', '#').text('View').addClass('hide');
        jQuery('#shooting_sketch_image_remove').addClass('hide').removeClass('i-block');
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

// Add New button click (in modal) to reset for another header add
jQuery(document).on('click', '#add_new', function () {
    resetTechniqueSheetRtForm();
    setTimeout(function () {
        jQuery('#TechniqueSheetRtModal').find('input[name="entry_type_fix"]:checked').focus();
    }, 50);
    jQuery('#add_new').hide();
});

// Modal open/close events
jQuery('#TechniqueSheetRtModal').on('hide.bs.modal', function () {
    jQuery('#TechniqueSheetRtModal').find('#submitbtn').prop('disabled', false);
    resetTechniqueSheetRtForm();
    jQuery('#add_new').hide();
});

jQuery('#TechniqueSheetRtModal').on('shown.bs.modal', function () {
    fillTSDetailTable();
    // If opened for edit, skip getLatestTSSequence() — data loads via AJAX
    if (_openForEdit) {
        _openForEdit = false;
        updateSourceFieldsState();
        if (typeof updateRadioDisabledStates === 'function') updateRadioDisabledStates();
        setTimeout(() => {
            jQuery('#technique_sheet_rt_sequence').focus().select();
        }, 100);
        return;
    }
    let formId = jQuery('#id').val();
    if (formId && formId !== "") {
        jQuery('#add_new').show();
        jQuery('#preview_btn').show();
        setTimeout(() => {
            jQuery('#technique_sheet_rt_sequence').focus().select();
        }, 100);
    } else {
        jQuery('#add_new').hide();
        setTimeout(() => {
            jQuery('#TechniqueSheetRtModal').find('input[name="entry_type_fix"]:checked').focus();
        }, 50);
        getLatestTSSequence();
        getTSLNRData();
        jQuery('#prepared_by_user_id').val(loginUserId).trigger('change');
    }
    // Ensure source fields state is correct on modal open
    updateSourceFieldsState();
    // Update disabled/readonly state of entry type and film size radio buttons
    if (typeof updateRadioDisabledStates === 'function') updateRadioDisabledStates();
});

// Main Form Submit Handler
jQuery('#commonTechniqueSheetRtForm').on('submit', function (e) {
    e.preventDefault();
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#submitbtn').prop('disabled', true);
    let form = this;

    var dateValue = document.getElementById("technique_sheet_rt_date").value.trim();
    if (!isValidDate(dateValue)) {
        toastr.error("Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#submitbtn').prop('disabled', false);
        hideLoader();
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#submitbtn').prop('disabled', false);
        hideLoader();
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        jQuery(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#submitbtn').prop('disabled', false);
        hideLoader();
        return;
    }

    let activeDetails = technique_sheet_rt_details_data.filter(row => row.mode !== 'Delete');
    if (activeDetails.length === 0) {
        toastr.error("Please Add at least one Radiographic Shooting Sketch Details.");
        jQuery('#submitbtn').prop('disabled', false);
        hideLoader();
        return;
    }

    let mainReportId = jQuery('#commonTechniqueSheetRtForm #id').val() || jQuery('#id').val();
    let isExistingReport = (mainReportId && mainReportId != 0 && mainReportId != '0');

    let invalidFilmRow = activeDetails.find(r => {
        let fid = zeroToEmpty(r.film_id || r.detail_film_id);
        if (!fid) return true;
        if (isExistingReport && r.technique_sheet_rt_details_id && r.technique_sheet_rt_details_id != 0) {
            return false;
        }
        let isFilmActive = jQuery('#detail_film_id option[value="' + fid + '"]:not(.temp-option)').length > 0;
        return !isFilmActive;
    });
    if (invalidFilmRow) {
        toastr.error("Please Select Film Size");
        jQuery('#submitbtn').prop('disabled', false);
        hideLoader();
        return;
    }

    let formId = jQuery('#id').val();
    let formUrl = formId ? "update-technique_sheet_rt" : "store-technique_sheet_rt";

    // Temporarily enable disabled radio buttons so they are captured by FormData
    let disabledRadios = jQuery('input[name="entry_type_fix"]:disabled, input[name="film_size_fix"]:disabled');
    disabledRadios.prop('disabled', false);

    let formData = new FormData(form);

    // Restore disabled state immediately
    disabledRadios.prop('disabled', true);

    formData.append('_token', jQuery('meta[name="csrf-token"]').attr('content'));
    formData.append('ts_details_data', JSON.stringify(technique_sheet_rt_details_data));

    jQuery('#full-page-loader').addClass('loader-progress-whole-page')

    jQuery.ajax({
        type: 'POST',
        url: formUrl,
        data: formData,
        contentType: false,
        processData: false,
        success: function (data) {
            jQuery('#submitbtn').prop('disabled', false);
            hideLoader();
            if (data.response_code == 1) {
                if (formId) {
                    let redirectFn = function () {
                        jQuery('#TechniqueSheetRtModal').modal('hide');
                        if ($.fn.DataTable.isDataTable('#dyntable')) {
                            jQuery('#dyntable').DataTable().ajax.reload(null, false);
                        }
                    };
                    if (data.url && data.url != "") {
                        toastSuccessPreview(data.response_message, data.url, redirectFn);
                    } else {
                        toastSuccess(data.response_message, redirectFn);
                    }
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                } else {
                    let nextFn = function () {
                        resetTechniqueSheetRtForm();
                        setTimeout(function () {
                            jQuery('#TechniqueSheetRtModal').find('input[name="entry_type_fix"]:checked').focus();
                        }, 50);
                    };
                    if (data.url && data.url != "") {
                        toastSuccessPreview(data.response_message, data.url, nextFn);
                    } else {
                        toastSuccess(data.response_message, nextFn);
                    }
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#TechniqueSheetRtModal').find('#submitbtn').prop('disabled', false);
                }
            } else {
                toastr.error(data.response_message);
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                jQuery('#TechniqueSheetRtModal').find('#submitbtn').prop('disabled', false);
            }
        },
        error: function (xhr) {
            jQuery('#submitbtn').prop('disabled', false);
            hideLoader();
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                let errors = xhr.responseJSON.errors;
                let errorMsg = '';
                jQuery.each(errors, function (key, value) {
                    errorMsg += value + '\n';
                });
                toastr.error(errorMsg);
            } else {
                toastr.error('Something went wrong. Please try again.');
            }
        }
    });
});

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

            // Mismatch pattern like "L3-A" -> Return Blank ('')
            return '';
        } else {
            return input;
        }
    }
}

function updateFilmSizeDropdown() {
    let film_size_fix = jQuery('input[name="film_size_fix"]:checked').val() || 'inch';
    jQuery('#detail_film_id option').each(function () {
        let opt = jQuery(this);
        let val = opt.val();
        if (!val) return;
        let inchVal = opt.attr('data-inch') || '';
        let cmVal = opt.attr('data-cm') || '';
        if (film_size_fix === 'inch') {
            opt.text(inchVal);
        } else {
            opt.text(cmVal);
        }
    });
    if (jQuery('#detail_film_id').data('select2')) {
        jQuery('#detail_film_id').trigger('change.select2');
    }
}

function resequenceTSDetails(isDelete = false) {
    let activeRows = technique_sheet_rt_details_data.filter(r => r.mode !== 'Delete');
    let deleteRows = technique_sheet_rt_details_data.filter(r => r.mode === 'Delete');

    activeRows.sort((a, b) => parseFloat(a.sr_no || 0) - parseFloat(b.sr_no || 0));

    let hasDecimal = activeRows.some(row => parseFloat(row.sr_no || 0) % 1 !== 0);

    if (isDelete || hasDecimal) {
        activeRows.forEach((row, index) => {
            row.sr_no = index + 1;
        });
    } else {
        activeRows.forEach((row) => {
            row.sr_no = Math.round(parseFloat(row.sr_no || 0));
        });
    }

    technique_sheet_rt_details_data = [...activeRows, ...deleteRows];
}

function updateSourceFieldsState() {
    let sourceUsedVal = jQuery('#source_used').val() || '';
    let hasGamma = sourceUsedVal.includes('Ir-192') || sourceUsedVal.includes('Co-60');
    let hasXray = sourceUsedVal.includes('X-Ray');

    if (hasGamma) {
        jQuery('#source_size').prop('readonly', false).removeAttr('tabindex');
    } else {
        jQuery('#source_size').prop('readonly', true).val('').attr('tabindex', -1);
    }

    if (hasXray) {
        jQuery('#xray_focal_size').prop('readonly', false).removeAttr('tabindex');
    } else {
        jQuery('#xray_focal_size').prop('readonly', true).val('').attr('tabindex', -1);
    }
}

function formatDecimal(val, decimals = 2) {
    if (val === null || val === undefined || String(val).trim() === '' || String(val).toLowerCase() === 'nan' || String(val).toLowerCase() === 'null') {
        return '';
    }
    let parsed = parseFloat(val);
    if (isNaN(parsed)) return val;
    return parsed.toFixed(decimals);
}

// var originalPartOptions = [];

// jQuery(document).ready(function () {
//     jQuery('#part_id option').each(function () {
//         let opt = jQuery(this);
//         originalPartOptions.push({
//             value: opt.val(),
//             text: opt.text().trim(),
//             drg_no: opt.attr('data-drg_no') || '',
//             job_desc_id: opt.attr('data-job_desc_id') || ''
//         });
//     });

//     // Only filter if a job_desc is already selected; otherwise keep all options
//     let initialJobDesc = jQuery('#job_desc_id').val();
//     if (initialJobDesc) {
//         filterPartsByJobDesc(initialJobDesc);
//     }
//     // If no job_desc selected, leave options as-is (all parts remain visible)
// });

// function filterPartsByJobDesc(jobDescId) {
//     let $partSelect = jQuery('#part_id');
//     let currentPartId = $partSelect.val();

//     $partSelect.empty().append('<option value="">Select Part No.</option>');

//     if (jobDescId) {
//         let filteredParts = originalPartOptions.filter(function (opt) {
//             return opt.value === '' || opt.job_desc_id == jobDescId;
//         });

//         filteredParts.forEach(function (opt) {
//             if (opt.value !== '') {
//                 $partSelect.append(
//                     `<option value="${opt.value}" data-drg_no="${opt.drg_no}" data-job_desc_id="${opt.job_desc_id}">
//                         ${opt.text}
//                     </option>`
//                 );
//             }
//         });
//     }

//     if (currentPartId && $partSelect.find(`option[value="${currentPartId}"]`).length > 0) {
//         $partSelect.val(currentPartId);
//     } else {
//         $partSelect.val('');
//     }
//     $partSelect.trigger('change.select2');
// }

// jQuery(document).on('change', '#job_desc_id', function () {
//     let jobDescId = jQuery(this).val();
//     filterPartsByJobDesc(jobDescId);
// });

// Intercept clicks and keydown events for custom readonly radio buttons
jQuery(document).on('click keydown', 'input[type="radio"].readonly-radio', function (e) {
    e.preventDefault();
    return false;
});

// Toggle readonly/editable state on entry type & film size radio buttons based on grid details count / mode
function updateRadioDisabledStates() {
    let formId = jQuery('#id').val();
    let hasDetails = technique_sheet_rt_details_data.filter(row => row.mode !== 'Delete').length > 0;

    // Toggle Copy Buttons
    if (formId && formId !== "") {
        jQuery('#copyAllBtn').prop('disabled', true);
        jQuery('#copyCustBtn').prop('disabled', true);
    } else {
        checkCopyDataAvailable();
    }

    // Ensure all radio buttons are NOT disabled so they submit naturally
    jQuery('input[name="entry_type_fix"], input[name="film_size_fix"]').prop('disabled', false);

    // 1. Entry Type:
    // - In Edit mode: ALWAYS readonly
    // - In Add mode: readonly if details exist, otherwise editable
    if (formId && formId !== "") {
        jQuery('input[name="entry_type_fix"]').addClass('readonly-radio');
        jQuery('input[name="entry_type_fix"]').parent().css({
            'opacity': '1',
            'pointer-events': 'none'
        });
    } else {
        if (hasDetails) {
            jQuery('input[name="entry_type_fix"]').addClass('readonly-radio');
            jQuery('input[name="entry_type_fix"]').parent().css({
                'opacity': '1',
                'pointer-events': 'none'
            });
        } else {
            jQuery('input[name="entry_type_fix"]').removeClass('readonly-radio');
            jQuery('input[name="entry_type_fix"]').parent().css({
                'opacity': '1',
                'pointer-events': 'auto'
            });
        }
    }

    // 2. Film Size (inch / cm):
    // - In Edit mode: ALWAYS readonly
    // - In Add mode: readonly if details exist, otherwise editable
    if ((formId && formId !== "") || hasDetails) {
        jQuery('input[name="film_size_fix"]').addClass('readonly-radio');
        jQuery('input[name="film_size_fix"]').parent().css({
            'opacity': '1',
            'pointer-events': 'none'
        });
    } else {
        jQuery('input[name="film_size_fix"]').removeClass('readonly-radio');
        jQuery('input[name="film_size_fix"]').parent().css({
            'opacity': '1',
            'pointer-events': 'auto'
        });
    }
}

function suggestIqi(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#iqi_list',
        url: 'technique_sheet_rt_iqi-list',
        responseListKey: 'iqiList'
    });
}
jQuery(document).on('click', '#iqi_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#iqi_suggestion').val(suggest);
    var hidden = jQuery('#iqi_suggestion').val();
    jQuery('#TechniqueSheetRtModal').find('#iqi').val(hidden);
    jQuery('#iqi_list').html('');
});

function suggestFilmProcessing(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#film_processing_list',
        url: 'technique_sheet_rt_film_processing-list',
        responseListKey: 'filmProcessingList'
    });
}
jQuery(document).on('click', '#film_processing_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#film_processing_suggestion').val(suggest);
    var hidden = jQuery('#film_processing_suggestion').val();
    jQuery('#TechniqueSheetRtModal').find('#film_processing').val(hidden);
    jQuery('#film_processing_list').html('');
});

function suggestTestTechnique(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#detail_test_technique_list',
        url: 'technique_sheet_rt_test_technique-list',
        responseListKey: 'testTechniqueList'
    });
}
jQuery(document).on('click', '#detail_test_technique_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#detail_test_technique_suggestion').val(suggest);
    var hidden = jQuery('#detail_test_technique_suggestion').val();
    jQuery('#TSDetailsModal').find('#detail_test_technique').val(hidden);
    jQuery('#detail_test_technique_list').html('');
});

function suggestPartNo(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#part_no_list',
        url: 'technique_sheet_rt_part_no-list',
        responseListKey: 'partNoList'
    });
}

function suggestDrgNo(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#drg_no_list',
        url: 'technique_sheet_rt_drg_no-list',
        responseListKey: 'drgNoList'
    });
}

// Copy Buttons logic
/* Old logic - commented out as per requirement
jQuery(document).on('change', '#customer_id', function () {
    let customer_id = jQuery(this).val();
    if (customer_id && customer_id !== "") {
        jQuery('#copyCustBtn').prop('disabled', false);
    } else {
        jQuery('#copyCustBtn').prop('disabled', true);
    }
});

jQuery(document).on('click', '#copyAllBtn', function () {
    jQuery('#tsCopyTable tbody').empty();
    let table7 = jQuery('#tsCopyTable');
    if (jQuery.fn.DataTable.isDataTable(table7)) {
        table7.DataTable().clear().destroy();
        jQuery('#tsCopyTable thead tr.search-row').remove();
    }
    loadTSCopyList('');
});

jQuery(document).on('click', '#copyCustBtn', function () {
    let customer_id = jQuery('#customer_id').val();
    if (!customer_id) return;
    jQuery('#tsCopyTable tbody').empty();
    let table7 = jQuery('#tsCopyTable');
    if (jQuery.fn.DataTable.isDataTable(table7)) {
        table7.DataTable().clear().destroy();
        jQuery('#tsCopyTable thead tr.search-row').remove();
    }
    loadTSCopyList(customer_id);
});
*/

// Enable Copy button when actual copy data exists (based on mode)
function checkCopyDataAvailable() {
    let formId = jQuery('#id').val();
    if (formId && formId !== "") {
        jQuery('#copyAllBtn').prop('disabled', true);
        return;
    }

    jQuery('#copyAllBtn').prop('disabled', false);
}

jQuery(document).on('change', '#type_of_job_id', function () {
    checkCopyDataAvailable();
    var entry_type = jQuery('input[name="entry_type_fix"]:checked').val();
    var formId = jQuery('#commonTechniqueSheetRtForm').find('input[name="id"]').val();
    if (entry_type == 'From RT Report' && (formId == undefined || formId == '')) {
        fillPendingRTList();
    }
});

jQuery(document).on('click', '#copyAllBtn', function () {
    let entry_type = jQuery('input[name="entry_type_fix"]:checked').val();

    if (entry_type === 'From RT Report') {
        fillPendingRTList();
        jQuery('#TestReportRTPendingModal').modal('show');
    } else {
        let type_of_job_id = jQuery('#type_of_job_id').val();
        jQuery('#tsCopyTable tbody').empty();
        let table7 = jQuery('#tsCopyTable');
        if (jQuery.fn.DataTable.isDataTable(table7)) {
            table7.DataTable().clear().destroy();
            jQuery('#tsCopyTable thead tr.search-row').remove();
        }
        loadTSCopyList(type_of_job_id || '');
    }
});

function loadTSCopyList(type_of_job_id) {
    skipLoader = false;
    showLoader();
    jQuery.ajax({
        url: "get-technique-sheet-copy-list",
        type: "GET",
        data: { type_of_job_id: type_of_job_id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                let tblHtml = ``;
                if (data.list && data.list.length > 0) {
                    data.list.forEach(row => {
                        tblHtml += `<tr>
                            <td><input type="radio" name="technique_sheet_rt_id[]" class="simple-check" id="technique_sheet_rt_id_${row.id}" value="${row.id}" /></td>
                            <td>${row.technique_sheet_rt_no || ''}</td>
                            <td>${row.technique_sheet_rt_date || ''}</td>
                            <td>${row.customer || ''}</td>
                            <td>${row.entry_type_fix || ''}</td>
                            <td>${row.type_of_job || ''}</td>
                            <td>${row.job_description || ''}</td>
                            <td>${row.part_no || ''}</td>
                            <td>${row.drg_no || ''}</td>
                            <td>${row.material || ''}</td>
                            <td>${row.heat_no || ''}</td>
                            <td>${row.rt_no || ''}</td>
                            <td>${row.product_code || ''}</td>
                        </tr>`;
                    });
                }

                let table7 = jQuery('#tsCopyTable');
                if (jQuery.fn.DataTable.isDataTable(table7)) {
                    table7.DataTable().clear().destroy();
                    jQuery('#tsCopyTable thead tr.search-row').remove();
                }

                jQuery('#tsCopyTable tbody').empty().append(tblHtml);
                jQuery('#pendingTSCopyModal').modal('show');

                table7.DataTable({
                    pageLength: 10,
                    paging: true,
                    searching: true,
                    oLanguage: {
                        sSearch: "Search:",
                        sEmptyTable: "No Radiographic Shooting Sketch Available",
                        sZeroRecords: "No Radiographic Shooting Sketch Available"
                    },
                    dom: 'lrtip',
                    sScrollX: true,
                    sScrollX: "100%",
                    bScrollCollapse: true,
                    initComplete: function () {
                        if (typeof initColumnSearch === 'function') {
                            initColumnSearch('#tsCopyTable', [0], 'common_search');
                        }
                    }
                });
            } else {
                toastr.error(data.response_message || 'Error loading list.');
            }
        },
        error: function () {
            toastr.error('Something went wrong!');
        },
        complete: function () {
            hideLoader();
        }
    });
}

// Adjust DataTable columns when modal is fully visible
jQuery('#pendingTSCopyModal').on('shown.bs.modal', function () {
    let $table = jQuery('#tsCopyTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().columns.adjust().draw();
        if (typeof initColumnSearch === 'function') {
            initColumnSearch('#tsCopyTable', [0], 'common_search');
        }
    }
});

// Copy Action Submit
jQuery('#pendingTSCopyForm').on('submit', function (e) {
    e.preventDefault();
    let selectedId = jQuery('input[name="technique_sheet_rt_id[]"]:checked').val();
    if (!selectedId) {
        toastr.error("Select Radiographic Shooting Sketch.");
        return false;
    }

    jQuery('#pendingTSCopyModal').modal('hide');
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
            text: 'Do You want to Copy Radiographic Shooting Sketch Details?',
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

            performCopy(selectedId, copyMaster, copyDetails);
        });
    });
});

function performCopy(selectedId, copyMaster, copyDetails) {
    skipLoader = false;
    showLoader();
    jQuery.ajax({
        url: "edit-technique_sheet_rt",
        type: 'GET',
        data: { id: selectedId },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                // 1. Copy Details first so grid/derived header fields get updated first
                if (copyDetails) {
                    technique_sheet_rt_details_data = [];
                    if (data.ts_details_data && data.ts_details_data.length > 0) {
                        data.ts_details_data.forEach(row => {
                            technique_sheet_rt_details_data.push({
                                technique_sheet_rt_details_id: 0,
                                sr_no: Math.round(parseFloat(row.sr_no || 0)),
                                identification: row.identification,
                                location: row.location,
                                source_id_fix: row.source_id_fix,
                                film_brand_id: row.film_brand_id,
                                film_brand_name: row.film_brand,
                                film_type_id: row.film_type_id,
                                film_type_name: row.film_type,
                                thickness: row.thickness,
                                sfd: row.sfd,
                                // iqi_designation_id: row.iqi_designation_id,
                                iqi_designation: row.iqi_designation || row.iqi_designation_name || '',
                                iqi_designation_name: row.iqi_designation || row.iqi_designation_name || '',
                                // iqi_sensitivity_id: row.iqi_sensitivity_id,
                                iqi_sensitivity: row.iqi_sensitivity || row.iqi_sensitivity_name || '',
                                iqi_sensitivity_name: row.iqi_sensitivity || row.iqi_sensitivity_name || '',
                                film_id: row.film_id,
                                film_size_inch: row.film_size_inch,
                                film_size_cm: row.film_size_cm,
                                no_of_film_fix: row.no_of_film_fix,
                                film_qty: row.film_qty,
                                sq_in: parseFloat(row.sq_in || 0),
                                sq_cm: parseFloat(row.sq_cm || 0),
                                total_sq_in: parseFloat(row.total_sq_in || 0),
                                total_sq_cm: parseFloat(row.total_sq_cm || 0),
                                test_technique: row.test_technique || '',
                                film_position: row.film_position || '',
                                mode: 'Insert'
                            });
                        });
                    }
                    fillTSDetailTable();
                }

                // 2. Copy Master fields next
                if (copyMaster && data.ts_data != null) {
                    var d = data.ts_data;
                    // jQuery('#customer_id').val(zeroToEmpty(d.customer_id)).trigger('change');
                    // jQuery('#type_of_job_id').val(zeroToEmpty(d.type_of_job_id)).trigger('change');
                    // jQuery('#part_id').val(zeroToEmpty(d.part_id));
                    // jQuery('#job_desc_id').val(zeroToEmpty(d.job_desc_id)).trigger('change');
                    // jQuery('#part_id').val(zeroToEmpty(d.part_id)).trigger('change.select2');
                    // jQuery('#area_of_coverage_id').val(zeroToEmpty(d.area_of_coverage_id)).trigger('change');
                    // jQuery('#prepared_by_user_id').val(zeroToEmpty(d.prepared_by_user_id)).trigger('change');
                    // jQuery('#checked_by_authority_person_id').val(zeroToEmpty(d.checked_by_authority_person_id)).trigger('change');
                    // jQuery('#authorized_by_authority_person_id').val(zeroToEmpty(d.authorized_by_authority_person_id)).trigger('change');
                    // jQuery('#technique_sheet_rt_date').val(d.technique_sheet_rt_date);

                    // Do not touch Part No. and Drg. No. during Copy so manual input is preserved
                    jQuery('#source_size').val(d.source_size || '');
                    jQuery('#xray_focal_size').val(d.xray_focal_size || '');
                    jQuery('#lead_screen_thick').val(d.lead_screen_thick || '');
                    jQuery('#lead_screen_thick_back').val(d.lead_screen_thick_back || '');
                    jQuery('#iqi').val(d.iqi || '');
                    jQuery('#film_processing').val(d.film_processing || '');
                    jQuery('#test_technique').val(d.test_technique || '');
                    jQuery('#test_arrangement').val(d.test_arrangement || '');
                    jQuery('#test_class').val(d.test_class || '');

                    jQuery('#customer_procedure_ref').val(d.customer_procedure_ref || '');
                    jQuery('#procedure_ref_id').val(zeroToEmpty(d.procedure_ref_id)).trigger('change');
                    jQuery('#evaluation_as_per_id').val(zeroToEmpty(d.evaluation_as_per_id)).trigger('change');
                    jQuery('#acceptance_standard_id').val(zeroToEmpty(d.acceptance_standard_id)).trigger('change');

                    if (d.film_size_fix === 'cm') {
                        jQuery('#film_size_fix_cm').prop('checked', true);
                    } else {
                        jQuery('#film_size_fix_inch').prop('checked', true);
                    }

                    if (d.sfd_unit_fix === 'inch') {
                        jQuery('#sfd_unit_fix_inch').prop('checked', true);
                    } else {
                        jQuery('#sfd_unit_fix_mm').prop('checked', true);
                    }

                    jQuery('#sp_note').val(d.sp_note || '');

                    // Do not clear the uploaded image during copy
                    // jQuery('#shooting_sketch_image_doc').val('');
                    // jQuery('#shooting_sketch_image_file').val('');
                    // jQuery('#shooting_sketch_image_prev').attr('href', '#').text('View').addClass('hide');
                    // jQuery('#shooting_sketch_image_remove').addClass('hide').removeClass('i-block');

                    // if (d.shooting_sketch_image) {
                    //     let fullPath = d.shooting_sketch_image;
                    //     let fileName = fullPath.split('/').pop();
                    //     jQuery('#shooting_sketch_image_doc').val(fullPath);
                    //     jQuery('#shooting_sketch_image_prev').attr('href', uploadURL + fullPath).text('View').removeClass('hide');
                    //     jQuery('#shooting_sketch_image_remove').addClass('i-block').removeClass('hide');
                    // 
                    //     let fileInput = jQuery('#shooting_sketch_image_file');
                    //     let newFile = new DataTransfer();
                    //     newFile.items.add(new File([""], fileName));
                    //     fileInput[0].files = newFile.files;
                    // }

                    // If we did NOT copy details, we manually preserve the master values for readonly derived fields
                    if (!copyDetails) {
                        jQuery('#source_used').val(d.source_used || '');
                        jQuery('#film_brand').val(d.film_brand || '');
                        jQuery('#film_type').val(d.film_type || '');
                        updateSourceFieldsState();
                    }
                }

                // toastSuccess("Technique Sheet data copied.");
            } else {
                toastr.error(data.response_message || 'Error fetching data.');
            }
        },
        error: function () {
            toastr.error('Something went wrong!');
        },
        complete: function () {
            hideLoader();
        }
    });
}

function radioChange() {

    var entry_type = jQuery('input[name="entry_type_fix"]:checked').val();
    var formId = jQuery('#commonTechniqueSheetRtForm').find('input[name="id"]').val();
    if (entry_type == 'From RT Report') {
        // jQuery('.toggleButton').prop('disabled', true);
        if (formId == '' || formId == undefined) {
            getPendingCustomers();
        }
    } else {
        jQuery('.toggleButton').prop('disabled', false);
        jQuery('.toggleModalBtn').prop('disabled', true);
    }
}

jQuery('#commonTechniqueSheetRtForm').find('input[name="entry_type_fix"]').on('change', function () {
    radioChange();
    checkCopyDataAvailable();
});

function getPendingCustomers() {
    var entry_type = jQuery('input[name="entry_type_fix"]:checked').val();

    jQuery.ajax({
        url: "get-customer_for_rt_report?reg_nabl=" + entry_type,
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            var custHtml = '';
            custHtml += `<option value="">Select Customer</option> `;
            if (data.response_code == 1 && data.getCustomer.length > 0) {
                for (let indx in data.getCustomer) {
                    custHtml += `<option value="${data.getCustomer[indx].id}">${data.getCustomer[indx].customer}</option>`;
                }
                jQuery('#customer_id').empty().append(custHtml).trigger('change:select2');
            } else {

                var custHtml = '';
                custHtml += `<option value="">Select Customer</option> `;
                jQuery('#customer_id').empty().append(custHtml).trigger('change:select2');
            }
        },
    });
}

jQuery("#customer_id").on("change", function () {
    checkCopyDataAvailable();
});

function fillPendingRTList() {

    var thisModal = jQuery('#TestReportRTPendingModal');
    var thisForm = jQuery('#commonTechniqueSheetRtForm');

    let type_of_job_id = jQuery('#type_of_job_id').val() || '';
    var Url = "";
    if (formId == undefined) {
        Url = "get-customer_pending_test_report_list?type_of_job_id=" + type_of_job_id;
    } else {
        Url = "get-customer_pending_test_report_list?id=" + formId + "&type_of_job_id=" + type_of_job_id;
    }

    jQuery(".toggleModalBtn").prop('disabled', true);

    jQuery.ajax({
        url: Url,
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            var usedParts = [];
            var totalDisb = 0;
            var found = 0;

            var tblHtml = ``;

            if (data.response_code == 1 && data.PendingList && data.PendingList.length > 0) {
                thisForm.find('#TSDetailTable tbody input[name="form_indx"]').each(function (indx) {
                    let frmIndx = jQuery(this).val();

                    let jbEorkOrderId = PendingList[frmIndx].test_report_rt_id;
                    if (jbEorkOrderId != "" && jbEorkOrderId != null) {
                        usedParts.push(Number(jbEorkOrderId));
                    }
                });

                function isUsed(pjId) {
                    if (usedParts.includes(Number(pjId))) {
                        totalDisb++;
                        return true;
                    }
                    return false;
                }

                let totalEntry = 0;
                found = 1;

                for (let idx in data.PendingList) {
                    var inUse = isUsed(data.PendingList[idx].test_report_rt_id);
                    var in_use = data.PendingList[idx].in_use == true ? 'readonly' : '';
                    totalEntry++;
                    tblHtml += `
                            <tr>
                                <td><input type="radio" name="test_report_rt_id[]" class="simple-check radio-filter-remove ${inUse ? 'in-use' : ''}" id="test_report_rt_ids_${data.PendingList[idx].test_report_rt_id}" value="${data.PendingList[idx].test_report_rt_id}" ${inUse ? 'checked' : ''} ${in_use}/></td>

                                <td>${data.PendingList[idx].test_report_no || ''}</td>
                                <td>${data.PendingList[idx].revision_number || ''}</td>
                                <td>${data.PendingList[idx].test_report_date || ''}</td>
                                <td>${data.PendingList[idx].type_of_job || ''}</td>
                                <td>${data.PendingList[idx].job_description || ''}</td>
                                <td>${data.PendingList[idx].part_no || ''}</td>
                                <td>${data.PendingList[idx].material || ''}</td>
                                <td>${data.PendingList[idx].sp_note || ''}</td>
                            </tr>`;

                }

            } else {
                tblHtml = ``;
            }

            var $table = jQuery("#TestReportRTPendingModal").find('#pendingDataTable');
            if (jQuery.fn.DataTable.isDataTable($table)) {
                $table.DataTable().clear().destroy();
            }
            jQuery('#pendingDataTable tbody').empty().append(tblHtml);

            var $new = $table.DataTable({
                paging: true,
                searching: true,
                "oLanguage": {
                    "sSearch": "Search :",
                    "sEmptyTable": "No Pending Test Report Available.",
                    "sZeroRecords": "No Pending Test Report Available."
                },
                dom: 'lrtip',
                "sScrollX": true,
                "sScrollX": "100%",
                "sScrollXInner": "110%",
                "bScrollCollapse": true,

            });
            fixDataTableColumnsUntilAdjusted($new);
            jQuery(".toggleModalBtn").prop('disabled', false);
        },

        error: function (jqXHR, textStatus, errorThrown) {
            jQuery('.toggleModalBtn').prop('disabled', true);
            if (jqXHR.status == 401) {
                toastr.error(jqXHR.statusText);
            } else {
                toastr.error('Something went wrong!');
                console.log(JSON.parse(jqXHR.responseText));

            }
        }
    });
}

// Pending Modal Submit 
$('#addPendingTestReportRTForm').on('submit', function (e) {

    e.preventDefault();

    jQuery('#full-page-loader')
        .removeClass('hidden-loader')
        .addClass('loader-progress-whole-page');

    jQuery('#TestReportRTPendingModal')
        .find('#submitbtn')
        .prop('disabled', true);

    let chkArr = [];
    var formId = jQuery('#commonTechniqueSheetRtForm').find('input[name="id"]').val();

    jQuery("#addPendingTestReportRTForm")
        .find("[id^='test_report_rt_ids_']:checked")
        .each(function () {
            chkArr.push(jQuery(this).val());
        });

    // No checkbox selected
    if (chkArr.length === 0) {
        toastr.error('Select Test Report RT From Pending.');

        jQuery('#full-page-loader')
            .removeClass('loader-progress-whole-page')
            .addClass('hidden-loader');

        jQuery('#TestReportRTPendingModal')
            .find('#submitbtn')
            .prop('disabled', false);

        return;
    }

    if (formId != undefined && formId != "") {
        var pend_url = "get-pending_test_report_rt_data?id=" + formId;
    } else {
        var pend_url = "get-pending_test_report_rt_data";
    }

    jQuery.ajax({
        url: pend_url,
        type: 'GET',
        data: { test_report_rt_id: chkArr.join(',') },
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                jQuery("#TestReportRTPendingModal").modal('hide');

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
                        text: 'Do You want to Copy Radiographic Shooting Sketch Details?',
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

                        if (copyMaster && data.test_report_data) {
                            FillMasterTable(data.test_report_data);
                        } else {
                            FillMasterTable([]);
                        }

                        if (copyDetails) {
                            technique_sheet_rt_details_data = [];
                            jQuery.each(data.test_report_details, function (i, item) {
                                let selectedFilm = jQuery('#detail_film_id option[value="' + item.film_id + '"]');
                                let film_size_inch = selectedFilm.attr('data-inch') || item.film_size || '';
                                let film_size_cm = selectedFilm.attr('data-cm') || '';
                                let sq_in = parseFloat(selectedFilm.attr('data-sq_in') || item.sq_in || 0);
                                let sq_cm = parseFloat(selectedFilm.attr('data-sq_cm') || item.sq_cm || 0);

                                technique_sheet_rt_details_data.push({
                                    technique_sheet_rt_details_id: 0,
                                    sr_no: Math.round(parseFloat(item.sr_no || 0)),
                                    identification: item.identification,
                                    location: item.location,
                                    source_id_fix: item.source_id_fix,
                                    film_brand_id: item.film_brand_id,
                                    film_brand_name: item.film_brand || '',
                                    film_type_id: item.film_type_id,
                                    film_type_name: item.film_type || '',
                                    thickness: item.thickness,
                                    sfd: item.sfd,
                                    // iqi_designation_id: item.iqi_designation_id,
                                    iqi_designation: item.iqi_designation || item.iqi || '',
                                    iqi_designation_name: item.iqi_designation || item.iqi || '',
                                    // iqi_sensitivity_id: item.iqi_sensitivity_id,
                                    iqi_sensitivity: item.iqi_sensitivity || item.sensitivity || '',
                                    iqi_sensitivity_name: item.iqi_sensitivity || item.sensitivity || '',
                                    film_id: item.film_id,
                                    film_size_inch: film_size_inch,
                                    film_size_cm: film_size_cm,
                                    no_of_film_fix: item.no_of_film_fix,
                                    film_qty: item.film_qty,
                                    sq_in: sq_in,
                                    sq_cm: sq_cm,
                                    total_sq_in: sq_in * parseInt(item.film_qty || 1),
                                    total_sq_cm: sq_cm * parseInt(item.film_qty || 1),
                                    test_technique: item.test_technique || (data.test_report ? data.test_report.test_technique : '') || '',
                                    film_position: item.film_position || '',
                                    mode: 'Insert'
                                });
                            });
                            fillTSDetailTable();
                        } else {
                            fillTSDetailTable();
                        }

                        setRadioReadonly('input[name*="entry_type_fix"]', true);
                        jQuery("#commonTechniqueSheetRtForm").find('customer_id').addClass('skip-tab');
                        // setSelect2Readonly('#customer_id', true);
                        // jQuery('.toggleButton').prop('disabled', true);
                        checkCopyDataAvailable();
                    });
                });
            } else {
                FillMasterTable([]);
                fillTSDetailTable();
            }

            jQuery('#TestReportRTPendingModal')
                .find('#submitbtn')
                .prop('disabled', false);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');
        },
        error: function (jqXHR) {

            if (jqXHR.status == 401) {
                toastr.error(jqXHR.statusText);
            } else {
                toastr.error('Something went wrong!');
                console.log(jqXHR.responseText);
            }

            jQuery('#TestReportRTPendingModal')
                .find('#submitbtn')
                .prop('disabled', false);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');
        }
    });
});

function FillMasterTable(data) {
    if (!data) return;

    // Do not set test_report_rt_id so it remains null
    // Do not touch Part No. and Drg. No. so manual input is preserved
    // jQuery('#area_of_coverage_id').val(data.area_of_coverage_id).trigger('change.select2');
    jQuery('#source_used').val(data.source_used);
    jQuery('#source_size').val(data.source_size);
    jQuery('#lead_screen_thick').val(data.lead_screen_thick);
    jQuery('#lead_screen_thick_back').val(data.lead_screen_thick_back || '');
    jQuery('#xray_focal_size').val(data.xray_focal_size);
    jQuery('#iqi').val(data.iqi);
    jQuery('#film_processing').val(data.film_processing);
    jQuery('#test_technique').val(data.test_technique);
    jQuery('#test_arrangement').val(data.test_arrangement);
    jQuery('#test_class').val(data.test_class);
    jQuery('#film_brand').val(data.film_brand);
    jQuery('#film_type').val(data.film_type);
    jQuery('#procedure_ref_id').val(data.procedure_ref_id).trigger('change.select2');
    jQuery('#evaluation_as_per_id').val(data.evaluation_as_per_id).trigger('change.select2');
    jQuery('#acceptance_standard_id').val(data.acceptance_standard_id).trigger('change.select2');
    jQuery('#customer_procedure_ref').val(data.customer_procedure_ref);
    if (data.film_size_unit_fix) {
        jQuery('input[name="film_size_fix"][value="' + data.film_size_unit_fix + '"]').prop('checked', true).trigger('change');
    }
    if (data.sfd_unit_fix) {
        jQuery('input[name="sfd_unit_fix"][value="' + data.sfd_unit_fix + '"]').prop('checked', true);
    }
    jQuery('#no_of_films').val(data.no_of_films);
    jQuery('#film_size').val(data.film_size);
    jQuery('#total_area').val(data.total_area);

}

function fillRTDetailTable() {

    let tblHtml = '';

    if (technique_sheet_rt_details_data) {

        for (let key in technique_sheet_rt_details_data) {

            if (technique_sheet_rt_details_data[key].mode == "Delete") {
                continue;
            }

            var formIndx = technique_sheet_rt_details_data.indexOf(technique_sheet_rt_details_data[key]);

            var sr_no = technique_sheet_rt_details_data[key].sr_no ?? '';
            var identification = technique_sheet_rt_details_data[key].identification ?? '';
            var location = technique_sheet_rt_details_data[key].location ?? '';
            var source = technique_sheet_rt_details_data[key].source_id_fix ?? '';
            var film_brand = technique_sheet_rt_details_data[key].film_brand ?? '';
            var film_type = technique_sheet_rt_details_data[key].film_type ?? '';
            var thickness = technique_sheet_rt_details_data[key].thickness ?? '';
            var sfd = technique_sheet_rt_details_data[key].sfd ?? '';
            var iqi_designation = technique_sheet_rt_details_data[key].iqi_designation || technique_sheet_rt_details_data[key].iqi_designation_name || '';
            var iqi_sensitivity = technique_sheet_rt_details_data[key].iqi_sensitivity || technique_sheet_rt_details_data[key].iqi_sensitivity_name || '';
            var film_size = technique_sheet_rt_details_data[key].film_size ?? '';
            var no_of_film = technique_sheet_rt_details_data[key].no_of_film_fix ?? '';
            var test_technique = technique_sheet_rt_details_data[key].test_technique ?? '';
            var film_position = technique_sheet_rt_details_data[key].film_position ?? '';

            tblHtml += `<tr>`;

            tblHtml += `<td>
                            ${DetailsActionDropdown('editTSDetails', 'removeTSDetails')}
                            <input type="hidden" name="form_indx" value="${formIndx}">
                        </td>`;

            tblHtml += `<td>${parseInt(sr_no)}</td>`;
            tblHtml += `<td>${identification}</td>`;
            tblHtml += `<td>${location}</td>`;
            tblHtml += `<td>${source}</td>`;
            tblHtml += `<td>${film_brand}</td>`;
            tblHtml += `<td>${film_type}</td>`;
            tblHtml += `<td>${thickness}</td>`;
            tblHtml += `<td>${sfd}</td>`;
            tblHtml += `<td>${iqi_designation}</td>`;
            tblHtml += `<td>${iqi_sensitivity}</td>`;
            tblHtml += `<td>${film_size}</td>`;
            tblHtml += `<td>${no_of_film}</td>`;
            tblHtml += `<td>${test_technique}</td>`;
            tblHtml += `<td>${film_position}</td>`;
            tblHtml += `</tr>`;
        }

    } else {

        tblHtml = `<tr>
                        <td colspan="15" class="text-center">
                            No Radiographic Shooting Sketch Details Added
                        </td>
                   </tr>`;
    }

    $('#TSDetailTable tbody').html(tblHtml);
}

jQuery('#TestReportRTPendingModal').on('show.bs.modal', function (e) {
    var dt = jQuery('#pendingDataTable').DataTable();
    fixDataTableColumnsUntilAdjusted(dt);
    var usedParts = [];
    if (technique_sheet_rt_details_data && technique_sheet_rt_details_data.length > 0) {
        technique_sheet_rt_details_data.forEach(function (item) {
            if (item.test_report_rt_id) {
                usedParts.push(Number(item.test_report_rt_id));
            }
        });
    }
    function isUsed(pjId) {
        if (usedParts.includes(Number(pjId))) {
            return true;
        }
        return false;
    }
    var totalEntry = 0;
    jQuery('#pendingDataTable tbody tr').each(function (indx) {
        totalEntry++;
        var checkField = jQuery(this).find('input[name="test_report_rt_id[]"]');
        var partId = jQuery(checkField).val();
        var inUse = isUsed(partId);

        if (formId == undefined) {
            if (technique_sheet_rt_details_data.length > 0) {
                var inUse = isUsed(partId);
            } else {
                var inUse = false;
            }
        } else {
            var inUse = isUsed(partId);
        }

        if (inUse) {
            jQuery(checkField).prop('checked', true);

        } else {
            jQuery(checkField).prop('checked', false);
        }
    });
});

jQuery('#TestReportRTPendingModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('type_of_job_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 20);
    }
});

jQuery('#TSDetailsModal').on('hide.bs.modal', function () {
    this.dataset.customHideFocus = 'true';
    setTimeout(() => {
        let $select = jQuery('#checked_by_authority_person_id');
        if ($select.length) {
            let $container = $select.next('.select2-container').find('.select2-selection');
            if ($container.length) {
                $container.focus();
            } else {
                $select.focus();
            }
        }
    }, 150);
});