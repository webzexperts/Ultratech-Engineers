var currentUrl = window.location.href;
var test_report_rt_details_data = [];
var _openForEdit = false; // Flag: true when modal is opened for editing
var _lnrDetails = true;
var dbUlrId = null;
var dbUlrSequence = null;
var dbUlrNo = null;
var dbUlrYear = null;
var dbTestReportDate = null;
var dbOpticalDensity = '';
var sessionLastOpticalDensity = '';
var pendingInwardData = []; // Stores pending inward items fetched for selected customer
var selectedInwardDetailsId = null; // Stores selected inward detail ID for edit mode
var reportMasterData = null; // Stores master report data in edit mode

function resequenceReportDetails(isDelete = false) {
    let isRevision = (jQuery('#revision_number').val() && jQuery('#revision_number').val().toString().trim() !== '') ||
        (reportMasterData && reportMasterData.revision_number && reportMasterData.revision_number.toString().trim() !== '');

    let obsDetailsId = jQuery('#observation_sheet_details_id').val();
    let isObs = (obsDetailsId && obsDetailsId.toString().trim() !== '' && obsDetailsId != 0 && obsDetailsId != '0') ||
        (reportMasterData && reportMasterData.observation_sheet_details_id && reportMasterData.observation_sheet_details_id != 0);

    // resequenceReportDetails માત્ર direct inward માંથી હોય ત્યારે જ થશે:
    // જો Revision કે Observation Sheet હોય તો કશું જ નહિ થાય (return):
    if (isRevision || isObs) {
        return;
    }

    let activeRows = test_report_rt_details_data.filter(r => r.mode !== 'Delete');
    let deleteRows = test_report_rt_details_data.filter(r => r.mode === 'Delete');

    activeRows.sort((a, b) => parseFloat(a.sr_no || 0) - parseFloat(b.sr_no || 0));

    let hasDecimal = activeRows.some(row => parseFloat(row.sr_no || 0) % 1 !== 0);

    if (isDelete || hasDecimal) {
        activeRows.forEach((row, index) => {
            row.sr_no = index + 1;
        });
    }

    test_report_rt_details_data = [...activeRows, ...deleteRows];
}

// Draw details grid table rows
function fillReportDetailTable() {
    let tbody = jQuery('#ReportDetailTable tbody');
    tbody.empty();

    let film_size_fix = jQuery('input[name="film_size_unit_fix"]:checked').val() || 'inch';
    let sfd_unit_fix = jQuery('input[name="sfd_unit_fix"]:checked').val() || 'mm';

    // Update table headers dynamically based on selected unit
    jQuery('#ReportDetailTable thead th.th_sfd_unit').text('SFD (' + sfd_unit_fix + ')');
    jQuery('#ReportDetailTable thead th.th_sq_unit').text(film_size_fix === 'inch' ? 'SqIn' : 'SqCm');
    jQuery('#ReportDetailTable thead th.th_total_sq_unit').text(film_size_fix === 'inch' ? 'Total SqIn' : 'Total SqCm');

    let validRows = test_report_rt_details_data.filter(row => {
        if (row.mode === 'Delete') return false;
        if (row.is_revision_row === false) return false;
        return true;
    });
    validRows.sort((a, b) => parseFloat(a.sr_no || 0) - parseFloat(b.sr_no || 0));

    if (validRows.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="19" class="text-center" id="noDetails">
                    No Details Added
                </td>
            </tr>
        `);
    } else {
        let mainReportId = jQuery('#commonTestReportRtForm #id').val();
        let isExistingReport = (mainReportId && mainReportId != 0 && mainReportId != '0');
        let isRevision = (jQuery('#revision_number').val() && jQuery('#revision_number').val().toString().trim() !== '') || (reportMasterData && reportMasterData.revision_number && reportMasterData.revision_number.toString().trim() !== '');
        let isMapTorevRtDetails = (reportMasterData && reportMasterData.has_revision == true) ? true : false;
        validRows.forEach((row, index) => {
            let formIndx = test_report_rt_details_data.indexOf(row);
            let activeFilmId = zeroToEmpty(row.detail_film_id || row.film_id);
            let isFilmActive = activeFilmId && jQuery('#detail_film_id option[value="' + activeFilmId + '"]:not(.temp-option)').length > 0;

            let isCopiedRow = (row.is_copy === true || row.is_copied === true);
            let isExistingOrPendingRow = (isExistingReport && row.test_report_rt_details_id && row.test_report_rt_details_id != 0) || row.from_pending === true || (row.observation_sheet_details_id && row.observation_sheet_details_id != 0) || isRevision;

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

            let in_use = (reportMasterData && (reportMasterData.in_use == true || reportMasterData.in_use == 1)) ? true : false;
            let isMappedToObs = (reportMasterData && (reportMasterData.is_used_in_os == 1 || reportMasterData.is_used_in_os == true)) ? true : false;
            let isUsedInMs = (reportMasterData && (reportMasterData.is_used_in_ms == 1 || reportMasterData.is_used_in_ms == true)) ? true : false;
            let actionDropdown = (in_use || isMappedToObs || isRevision || isMapTorevRtDetails) ? DetailsActionDropdown('editReportDetails') : DetailsActionDropdown('editReportDetails', 'removeReportDetails');

            let rowHtml = `<tr>
                <td>
                   ${actionDropdown}
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
                <td>${row.optical_density || ''}</td>
                <td>${row.iqi_designation || row.iqi_designation_name || ''}</td>
                <td>${row.iqi_sensitivity || row.iqi_sensitivity_name || ''}</td>
                <td>${film_size_display || ''}</td>
                <td>${row.no_of_film_fix}</td>
                <td>${row.exposure_time || ''}</td>
                <td>${row.finding || row.finding_name || ''}</td>
                <!-- <td>${row.finding_level_name || ''}</td> -->
                <td>${row.film_result_name || ''}</td>
                <td>${row.ug || ''}</td>
                <td class="d-none"><input type="hidden" class="grid-include-checkbox" data-index="${formIndx}" value="${row.include_in_measurement_sheet || 'Yes'}"></td>
                <td class="d-none">${row.film_qty}</td>
                <td class="d-none">${sq_display}</td>
                <td class="d-none">${total_sq_display}</td>
            </tr>`;
            tbody.append(rowHtml);
        });
    }

    // Recalculate and update summaries
    updateHeaderSummaries(validRows, film_size_fix);
}

// Recalculate header values from detail grid row values
function updateHeaderSummaries(rows, film_size_fix) {
    let allActiveDetails = test_report_rt_details_data ? test_report_rt_details_data.filter(row => row.mode !== 'Delete') : [];
    if (!rows) {
        rows = allActiveDetails.filter(row => row.is_revision_row !== false);
    }
    if (!film_size_fix) {
        film_size_fix = jQuery('input[name="film_size_unit_fix"]:checked').val() || 'inch';
    }
    if (allActiveDetails.length === 0) {
        jQuery('#no_of_films').val('');
        jQuery('#film_size').val('');
        jQuery('#total_area').val('');
        jQuery('#abbreviation').val('');
        jQuery('#film_brand').val('');
        jQuery('#film_type').val('');
        jQuery('#source_used').val('');
        jQuery('#camera_ir_192_id').val('').trigger('change.select2');
        jQuery('#camera_co_60_id').val('').trigger('change.select2');
        jQuery('#camera_x_ray_id').val('').trigger('change.select2');
        setSelect2Readonly('#camera_ir_192_id', true);
        setSelect2Readonly('#camera_co_60_id', true);
        setSelect2Readonly('#camera_x_ray_id', true);
        jQuery('#camera_ir_192_id, #camera_co_60_id, #camera_x_ray_id').prop('disabled', true);
        jQuery('#camera_ir_192_id').prop('required', false).removeClass('is-invalid');
        jQuery('#camera_co_60_id').prop('required', false).removeClass('is-invalid');
        jQuery('#camera_x_ray_id').prop('required', false).removeClass('is-invalid');
        jQuery('#astric_camera_ir_192').addClass('d-none');
        jQuery('#astric_camera_co_60').addClass('d-none');
        jQuery('#astric_camera_x_ray').addClass('d-none');
        jQuery('#source_strength').val('').prop('readonly', true).prop('required', false).attr('tabindex', '-1');
        jQuery('#source_size').val('').prop('readonly', true).prop('required', false).attr('tabindex', '-1');
        jQuery('#xray_kv_ma').val('').prop('readonly', true).prop('required', false).attr('tabindex', '-1');
        jQuery('#xray_focal_size').val('').prop('readonly', true).prop('required', false).attr('tabindex', '-1');
        return;
    }
    // Fixed order sequence for sources: Ir-192, Co-60, X-Ray
    const sourceOrder = ['Ir-192', 'Co-60', 'X-Ray'];
    function getSourceRank(s) {
        let idx = sourceOrder.indexOf(s);
        return idx !== -1 ? idx : 999;
    }

    // 1. no_of_films: Total quantities grouped by unique Source
    let sourceQtyCounts = {};
    let totalQty = 0;
    rows.forEach(r => {
        let source = r.source_id_fix || '';
        if (source) {
            let qty = parseInt(r.film_qty || 0);
            sourceQtyCounts[source] = (sourceQtyCounts[source] || 0) + qty;
            totalQty += qty;
        }
    });
    let sortedQtySources = Object.keys(sourceQtyCounts).sort((a, b) => getSourceRank(a) - getSourceRank(b));
    let noOfFilmsOutput = sortedQtySources.map(source => source + " - " + sourceQtyCounts[source]);
    jQuery('#no_of_films').val(noOfFilmsOutput.join(', ') + " = " + totalQty);

    // 2. film_size: Aggregated by Source - Film Type - Film Size - Qty format
    let sizeCounts = {};
    let totalFilmSizeQty = 0;
    rows.forEach(r => {
        let source = r.source_id_fix || '';
        let type = r.film_type_name || '';
        let size = film_size_fix === 'inch' ? r.film_size_inch : r.film_size_cm;
        if (source && type && size) {
            let key = source + " - " + type + " - " + size;
            let qty = parseInt(r.film_qty || 0);
            sizeCounts[key] = (sizeCounts[key] || 0) + qty;
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

    // 3. total_area: Summation grouped by Source
    let sourceAreaSums = {};
    rows.forEach(r => {
        let source = r.source_id_fix || '';
        if (source) {
            let area = parseFloat(film_size_fix === 'inch' ? r.total_sq_in : r.total_sq_cm);
            let validArea = isNaN(area) ? 0 : area;
            sourceAreaSums[source] = (sourceAreaSums[source] || 0) + validArea;
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

    // 4. abbreviation: Unique list from findings
    let abbreviations = rows.map(r => r.finding_abbreviation);
    let uniqueAbbrev = [...new Set(abbreviations.filter(a => a && a !== ''))];
    jQuery('#abbreviation').val(uniqueAbbrev.join(', '));

    // 5. Film Brand, Film Type, Source Used
    let brands = rows.map(r => r.film_brand_name);
    let uniqueBrands = [...new Set(brands.filter(b => b != ''))];
    jQuery('#film_brand').val(uniqueBrands.join(', '));

    let types = rows.map(r => r.film_type_name);
    let uniqueTypes = [...new Set(types.filter(t => t != ''))];
    jQuery('#film_type').val(uniqueTypes.join(', '));

    // ડિટેલ્સ (Hide + Show બંને) માંથી સોર્સ નક્કી કરવા જેથી હાઈડ રોઝ હોય તો પણ સંબંધિત કેમેરા ઓપન રહે
    let sources = allActiveDetails.map(r => r.source_id_fix);
    let uniqueSources = [...new Set(sources.filter(s => s != ''))];
    uniqueSources.sort((a, b) => getSourceRank(a) - getSourceRank(b));
    jQuery('#source_used').val(uniqueSources.join(', '));

    let hasIr192 = uniqueSources.includes('Ir-192');
    let hasCo60 = uniqueSources.includes('Co-60');
    let hasIsotope = hasIr192 || hasCo60;
    let hasXray = uniqueSources.includes('X-Ray');

    if (hasIr192) {
        jQuery('#camera_ir_192_id').prop('disabled', false);
        setSelect2Readonly('#camera_ir_192_id', false);
        jQuery('#camera_ir_192_id').prop('required', true);
        jQuery('#astric_camera_ir_192').removeClass('d-none');
    } else {
        jQuery('#camera_ir_192_id').val('').trigger('change', [true]);
        setSelect2Readonly('#camera_ir_192_id', true);
        jQuery('#camera_ir_192_id').prop('disabled', true);
        jQuery('#camera_ir_192_id').prop('required', false).removeClass('is-invalid');
        jQuery('#astric_camera_ir_192').addClass('d-none');
    }

    if (hasCo60) {
        jQuery('#camera_co_60_id').prop('disabled', false);
        setSelect2Readonly('#camera_co_60_id', false);
        jQuery('#camera_co_60_id').prop('required', true);
        jQuery('#astric_camera_co_60').removeClass('d-none');
    } else {
        jQuery('#camera_co_60_id').val('').trigger('change', [true]);
        setSelect2Readonly('#camera_co_60_id', true);
        jQuery('#camera_co_60_id').prop('disabled', true);
        jQuery('#camera_co_60_id').prop('required', false).removeClass('is-invalid');
        jQuery('#astric_camera_co_60').addClass('d-none');
    }

    if (hasXray) {
        jQuery('#camera_x_ray_id').prop('disabled', false);
        setSelect2Readonly('#camera_x_ray_id', false);
        jQuery('#camera_x_ray_id').prop('required', true);
        jQuery('#astric_camera_x_ray').removeClass('d-none');
    } else {
        jQuery('#camera_x_ray_id').val('').trigger('change', [true]);
        setSelect2Readonly('#camera_x_ray_id', true);
        jQuery('#camera_x_ray_id').prop('disabled', true);
        jQuery('#camera_x_ray_id').prop('required', false).removeClass('is-invalid');
        jQuery('#astric_camera_x_ray').addClass('d-none');
    }

    jQuery('#source_strength').prop('readonly', !hasIsotope).prop('required', false).attr('tabindex', !hasIsotope ? '-1' : '0');
    jQuery('#source_size').prop('readonly', !hasIsotope).prop('required', false).attr('tabindex', !hasIsotope ? '-1' : '0');
    jQuery('#xray_kv_ma').prop('readonly', !hasXray).prop('required', false).attr('tabindex', !hasXray ? '-1' : '0');
    jQuery('#xray_focal_size').prop('readonly', !hasXray).prop('required', false).attr('tabindex', !hasXray ? '-1' : '0');

    if (!hasIsotope) {
        jQuery('#source_strength').val('');
        jQuery('#source_size').val('');
    }
    if (!hasXray) {
        jQuery('#xray_kv_ma').val('');
        jQuery('#xray_focal_size').val('');
    }
}

// Track unit changes
jQuery(document).on('change', 'input[name="film_size_unit_fix"]', function () {
    fillReportDetailTable();
    updateFilmSizeDropdown();
});

jQuery(document).on('change', 'input[name="sfd_unit_fix"]', function () {
    fillReportDetailTable();
});

function calculateDecayedCi(initialCi, loadingDateStr, isotope, testingDateStr) {
    if (!initialCi || !loadingDateStr || !isotope) return initialCi ? parseFloat(initialCi).toFixed(2) + ' Ci' : '';
    initialCi = parseFloat(initialCi);
    if (isNaN(initialCi)) return '';

    if (isotope.toLowerCase() === 'x-rays' || isotope.toLowerCase() === 'x-ray') return '';

    if (!testingDateStr) {
        return '0.00 Ci';
    }

    let testingDate = parseDateStr(testingDateStr);
    let loadingDate = parseDateStr(loadingDateStr);
    if (!testingDate || !loadingDate || loadingDate > testingDate) {
        return initialCi.toFixed(2) + ' Ci';
    }

    let diffTime = Math.abs(testingDate - loadingDate);
    let dayDifference = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    if (dayDifference === 0) dayDifference = 1;

    let halfLife = 0;
    let iso = isotope.toLowerCase();
    if (iso === 'ir-192') halfLife = 74.5;
    else if (iso === 'co-60') halfLife = 1925;
    // else if (iso === 'se-75') halfLife = 120;

    if (halfLife > 0) {
        let decay = Math.pow(Math.E, (dayDifference * 0.693 / halfLife));
        let present = initialCi / decay;
        return present.toFixed(2) + ' Ci';
    }
    return initialCi.toFixed(2) + ' Ci';
}

function extractSavedSourceStrength(savedStr, isotope) {
    if (!savedStr) return '';
    savedStr = savedStr.trim();
    let regex = new RegExp(isotope + '\\s*=\\s*([^,]+)', 'i');
    let match = savedStr.match(regex);
    if (match) {
        return match[1].trim();
    }
    if (savedStr.indexOf('=') === -1 && savedStr.indexOf(',') === -1) {
        return savedStr;
    }
    return '';
}

function extractSavedSourceSize(savedSize, isotope, isFirst) {
    if (!savedSize) return '';
    savedSize = savedSize.trim();
    if (savedSize.indexOf('&') !== -1) {
        let parts = savedSize.split('&').map(p => p.trim());
        return isFirst ? parts[0] : (parts[1] || parts[0]);
    }
    return savedSize;
}

function fetchCameraDetailApi(cameraId) {
    if (!cameraId) return;
    let testingDateStr = jQuery('#date_of_testing').val();
    jQuery.ajax({
        url: 'get-camera_detail',
        type: 'GET',
        data: {
            camera_id: cameraId,
            date_of_testing: testingDateStr
        },
        dataType: 'json',
        success: function (res) {
            if (res.response_code == 1) {
                if (res.loading_date) jQuery('#hidden_camera_loading_date').val(res.loading_date);
                if (res.initial_curie) jQuery('#hidden_camera_initial_curie').val(res.initial_curie);
                if (res.radiation_source) jQuery('#hidden_camera_isotope').val(res.radiation_source);

                let irVal = jQuery('#camera_ir_192_id').val();
                let coVal = jQuery('#camera_co_60_id').val();
                let xrayVal = jQuery('#camera_x_ray_id').val();
                let selectedCount = (irVal ? 1 : 0) + (coVal ? 1 : 0) + (xrayVal ? 1 : 0);

                let isEditOrig = false;
                let origIr = jQuery('#old_ir_camera_id').val() || (reportMasterData ? zeroToEmpty(reportMasterData.ir_camera_id) : '');
                let origCo = jQuery('#old_co_camera_id').val() || (reportMasterData ? zeroToEmpty(reportMasterData.co_camera_id) : '');
                let origXray = jQuery('#old_xray_camera_id').val() || (reportMasterData ? zeroToEmpty(reportMasterData.xray_camera_id) : '');
                let origDate = jQuery('#old_testing_date').val() || (reportMasterData ? reportMasterData.date_of_testing : '');
                let dateMatches = ((testingDateStr || '') == (origDate || ''));
                let c1 = (irVal || '') == (origIr || '');
                let c2 = (coVal || '') == (origCo || '');
                let c3 = (xrayVal || '') == (origXray || '');
                if (dateMatches && c1 && c2 && c3) {
                    isEditOrig = true;
                }

                if (!isEditOrig && selectedCount <= 1) {
                    if (res.source_strength) {
                        let formattedStrength = res.source_strength;
                        if (res.radiation_source && (res.radiation_source.toLowerCase() === 'ir-192' || res.radiation_source.toLowerCase() === 'co-60')) {
                            if (formattedStrength.indexOf('=') === -1) {
                                formattedStrength = res.radiation_source + ' = ' + formattedStrength;
                            }
                        }
                        jQuery('#source_strength').val(formattedStrength);
                    }
                    if (res.source_size) jQuery('#source_size').val(res.source_size);
                    if (res.xray_kv_ma) jQuery('#xray_kv_ma').val(res.xray_kv_ma);
                    if (res.xray_focal_size) jQuery('#xray_focal_size').val(res.xray_focal_size);
                }
            }
        }
    });
}

// Auto fill & combine details on Camera dropdown selection (Ir-192, Co-60, X-Ray)
function updateCameraSourceSummaries(isProgrammatic) {
    let irOpt = jQuery('#camera_ir_192_id').find('option:selected');
    let coOpt = jQuery('#camera_co_60_id').find('option:selected');
    let xrayOpt = jQuery('#camera_x_ray_id').find('option:selected');

    let irVal = irOpt.val();
    let coVal = coOpt.val();
    let xrayVal = xrayOpt.val();

    let testingDateStr = jQuery('#date_of_testing').val();

    let isEditMode = (typeof _openForEdit !== 'undefined' && _openForEdit && typeof reportMasterData !== 'undefined' && reportMasterData) || (jQuery('#id').val() ? true : false);

    // Retrieve original values from hidden inputs
    let origDate = jQuery('#old_testing_date').val() || '';
    let origIr = jQuery('#old_ir_camera_id').val() || '';
    let origCo = jQuery('#old_co_camera_id').val() || '';
    let origXray = jQuery('#old_xray_camera_id').val() || '';

    let c1 = (irVal || '') == (origIr || '');
    let c2 = (coVal || '') == (origCo || '');
    let c3 = (xrayVal || '') == (origXray || '');
    let dateMatches = ((testingDateStr || '') == (origDate || ''));


    // Restore original values if in edit mode and the user selected the original date and all cameras match
    if (isEditMode && dateMatches && c1 && c2 && c3) {
        if (jQuery('#old_source_strength').val()) {
            jQuery('#source_used').val(jQuery('#old_source_used').val() || '');
            jQuery('#source_strength').val(jQuery('#old_source_strength').val() || '');
            jQuery('#source_size').val(jQuery('#old_source_size').val() || '');
            jQuery('#xray_kv_ma').val(jQuery('#old_xray_kv_ma').val() || '');
            jQuery('#xray_focal_size').val(jQuery('#old_xray_focal_size').val() || '');
            return;
        }
    }

    let activeCamId = irVal || coVal || xrayVal;
    if (activeCamId && !isProgrammatic) {
        fetchCameraDetailApi(activeCamId);
    }

    let irStrength = '';
    if (irVal) {
        let savedStr = jQuery('#old_source_strength').val();
        if (isEditMode && dateMatches && (irVal || '') == (origIr || '') && savedStr) {
            let savedIr = extractSavedSourceStrength(savedStr, 'Ir-192');
            if (savedIr) {
                irStrength = savedIr;
            }
        }
        if (!irStrength) {
            let initCi = irOpt.data('initial-activity') || irOpt.data('source-strength');
            let loadDate = irOpt.data('loading-date');
            irStrength = calculateDecayedCi(initCi, loadDate, 'Ir-192', testingDateStr);
        }
    }

    let coStrength = '';
    if (coVal) {
        let savedStr = jQuery('#old_source_strength').val();
        if (isEditMode && dateMatches && (coVal || '') == (origCo || '') && savedStr) {
            let savedCo = extractSavedSourceStrength(savedStr, 'Co-60');
            if (savedCo) {
                coStrength = savedCo;
            }
        }
        if (!coStrength) {
            let initCi = coOpt.data('initial-activity') || coOpt.data('source-strength');
            let loadDate = coOpt.data('loading-date');
            coStrength = calculateDecayedCi(initCi, loadDate, 'Co-60', testingDateStr);
        }
    }

    let strengthArr = [];
    if (irStrength) {
        strengthArr.push('Ir-192 = ' + irStrength);
    }
    if (coStrength) {
        strengthArr.push('Co-60 = ' + coStrength);
    }
    if (strengthArr.length > 0) {
        jQuery('#source_strength').val(strengthArr.join(', '));
    } else if (!activeCamId) {
        jQuery('#source_strength').val('');
    }

    let irSize = '';
    if (irVal) {
        let savedSize = jQuery('#old_source_size').val();
        if (isEditMode && dateMatches && (irVal || '') == (origIr || '') && savedSize) {
            irSize = extractSavedSourceSize(savedSize, 'Ir-192', true);
        }
        if (!irSize) {
            irSize = irOpt.data('source-size') || '';
        }
    }

    let coSize = '';
    if (coVal) {
        let savedSize = jQuery('#old_source_size').val();
        if (isEditMode && dateMatches && (coVal || '') == (origCo || '') && savedSize) {
            coSize = extractSavedSourceSize(savedSize, 'Co-60', false);
        }
        if (!coSize) {
            coSize = coOpt.data('source-size') || '';
        }
    }

    let sizeArr = [];
    if (irSize) sizeArr.push(irSize);
    if (coSize) sizeArr.push(coSize);
    if (sizeArr.length > 0) {
        jQuery('#source_size').val(sizeArr.join(' & '));
    } else if (!activeCamId) {
        jQuery('#source_size').val('');
    }

    if (xrayVal) {
        let savedKvma = jQuery('#old_xray_kv_ma').val();
        let savedFocal = jQuery('#old_xray_focal_size').val();
        let kvma = (isEditMode && dateMatches && (xrayVal || '') == (origXray || '') && savedKvma)
            ? savedKvma
            : (xrayOpt.data('xray-kv-ma') || '');
        let focal = (isEditMode && dateMatches && (xrayVal || '') == (origXray || '') && savedFocal)
            ? savedFocal
            : (xrayOpt.data('xray-focal-size') || '');
        if (kvma) jQuery('#xray_kv_ma').val(kvma);
        if (focal) jQuery('#xray_focal_size').val(focal);
    } else if (!irVal && !coVal && !activeCamId) {
        jQuery('#xray_kv_ma').val('');
        jQuery('#xray_focal_size').val('');
    }
}

function loadRTCamerasDropdown(selectedIrId = '', selectedCoId = '', selectedXrayId = '') {
    return jQuery.ajax({
        url: 'get-rt-cameras-dropdown',
        type: 'GET',
        data: {
            ir_camera_id: selectedIrId,
            co_camera_id: selectedCoId,
            xray_camera_id: selectedXrayId
        },
        dataType: 'json',
        success: function (res) {
            if (res.response_code == 1) {
                // Populate Ir-192
                let $ir = jQuery('#camera_ir_192_id');
                $ir.empty().append('<option value="">Select Camera Ir-192</option>');
                if (res.ir_cameras && res.ir_cameras.length > 0) {
                    res.ir_cameras.forEach(cam => {
                        let name = cam.name_for_display;
                        let opt = jQuery(`<option value="${cam.rt_camera_id}">${name}</option>`)
                            .attr('data-isotope', cam.rt_isotope || '')
                            .attr('data-source-strength', cam.rtcd_initial_activity_ci || '')
                            .attr('data-initial-activity', cam.rtcd_initial_activity_ci || '')
                            .attr('data-loading-date', cam.rtcd_last_of_loading_date || '')
                            .attr('data-source-size', cam.rtcd_source_size || '');
                        $ir.append(opt);
                    });
                }
                $ir.val(selectedIrId).trigger('change.select2');

                // Populate Co-60
                let $co = jQuery('#camera_co_60_id');
                $co.empty().append('<option value="">Select Camera Co-60</option>');
                if (res.co_cameras && res.co_cameras.length > 0) {
                    res.co_cameras.forEach(cam => {
                        let name = cam.name_for_display;
                        let opt = jQuery(`<option value="${cam.rt_camera_id}">${name}</option>`)
                            .attr('data-isotope', cam.rt_isotope || '')
                            .attr('data-source-strength', cam.rtcd_initial_activity_ci || '')
                            .attr('data-initial-activity', cam.rtcd_initial_activity_ci || '')
                            .attr('data-loading-date', cam.rtcd_last_of_loading_date || '')
                            .attr('data-source-size', cam.rtcd_source_size || '');
                        $co.append(opt);
                    });
                }
                $co.val(selectedCoId).trigger('change.select2');

                // Populate X-Ray
                let $xray = jQuery('#camera_x_ray_id');
                $xray.empty().append('<option value="">Select Camera X-Ray</option>');
                if (res.xray_cameras && res.xray_cameras.length > 0) {
                    res.xray_cameras.forEach(cam => {
                        let name = cam.name_for_display;
                        let opt = jQuery(`<option value="${cam.rt_camera_id}">${name}</option>`)
                            .attr('data-xray-kv-ma', cam.rt_x_ray || '')
                            .attr('data-xray-focal-size', cam.rt_focal_spot || '');
                        $xray.append(opt);
                    });
                }
                $xray.val(selectedXrayId).trigger('change.select2');
            }
        }
    });
}

jQuery(document).on('change', '#camera_ir_192_id, #camera_co_60_id, #camera_x_ray_id', function (event, isProgrammatic) {
    updateCameraSourceSummaries(isProgrammatic);
});

// Auto-fill drawing no. on part_no change/input from autocomplete list
jQuery(document).on('input change', '#part_no', function () {
    let partNo = jQuery(this).val();
    if (window.partsAutocompleteList && partNo) {
        let match = window.partsAutocompleteList.find(p => p.part_no === partNo);
        if (match && match.drg_no) {
            jQuery('#drg_no').val(match.drg_no);
        }
    }
});

function fetchPendingRtCustomerData(customerId, processType, isAutoSet) {
    if (!customerId) {
        jQuery('#pending_btn').prop('disabled', true);
        setRadioReadonly('input[name="process_type"]', false);
        return;
    }

    jQuery('#pending_btn').prop('disabled', true);

    if (_openForEdit || isAutoSet) {
        return;
    }

    // Unlock process_type now that customer is selected
    setRadioReadonly('input[name="process_type"]', false);

    processType = processType || jQuery('input[name="process_type"]:checked').val() || 'Fresh';
    jQuery.ajax({
        url: 'get-pending-customer-rt-data',
        type: 'GET',
        data: { customer_id: customerId, report_id: jQuery('#id').val(), process_type: processType },
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                pendingInwardData = data.pending_data || [];

                if (selectedInwardDetailsId) {
                    let exists = pendingInwardData.some(item => item.material_inward_details_id == selectedInwardDetailsId);
                    if (!exists && reportMasterData && reportMasterData.material_inward_details_id == selectedInwardDetailsId) {
                        // Also append it to pendingInwardData so it shows in the modal
                        pendingInwardData.push({
                            material_inward_details_id: selectedInwardDetailsId,
                            material_inward_no: reportMasterData.material_inward_no,
                            material_inward_date: reportMasterData.date_of_receipt ? reportMasterData.date_of_receipt.split('/').reverse().join('-') : null,
                            nabl_type_fix: reportMasterData.nabl_type_fix,
                            test_at_fix: reportMasterData.test_carried_out_at,
                            job_type_fix: reportMasterData.job_type_fix,
                            process_type: reportMasterData.process_type,
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
                            rt_no: reportMasterData.rt_no,
                            product_code: reportMasterData.product_code,
                            inward_qty: reportMasterData.pend_qty,
                            reported_qty: 0
                        });
                    }
                    jQuery('#material_inward_details_id').val(selectedInwardDetailsId).trigger('change');
                    selectedInwardDetailsId = null;
                } else {
                    jQuery('#material_inward_details_id').val('').trigger('change');
                }

                if (pendingInwardData.length > 0) {
                    jQuery('#pending_btn').prop('disabled', false);
                } else {
                    jQuery('#pending_btn').prop('disabled', true);
                }

                fillPendingRtModalTable();
            } else {
                pendingInwardData = [];
                jQuery('#pending_btn').prop('disabled', true);
                fillPendingRtModalTable();
            }
        },
        error: function () {
            pendingInwardData = [];
            jQuery('#pending_btn').prop('disabled', true);
            fillPendingRtModalTable();
        },
        complete: function () {
            jQuery('#submitbtn').prop('disabled', false);
        }
    });
}

// Fetch pending customers material inward list
jQuery(document).on('change', '#customer_id', function (e, isAutoSet) {
    let customerId = jQuery(this).val();
    let processType = jQuery('input[name="process_type"]:checked').val() || 'Fresh';
    if (customerId && processType) {
        fetchPendingRtCustomerData(customerId, processType, isAutoSet);
    } else {
        pendingInwardData = [];
        jQuery('#pending_btn').prop('disabled', true);
        fillPendingRtModalTable();
    }
});

// Re-fetch pending material inward list on process_type change
jQuery(document).on('change', 'input[name="process_type"]', function () {
    updateCopyReportButtonState();
    let processType = jQuery(this).val() || 'Fresh';
    let customerId = jQuery('#customer_id').val();
    if (customerId && processType) {
        resetPendingInwardFields();
        test_report_rt_details_data = [];
        fillReportDetailTable();
        fetchPendingRtCustomerData(customerId, processType, false);
    } else {
        pendingInwardData = [];
        jQuery('#pending_btn').prop('disabled', true);
        fillPendingRtModalTable();
    }
});

function fillPendingRtModalTable() {
    let $table = jQuery("#PendingInwardForRtModal").find('#PendingForRtTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().clear().destroy();
    }

    let processType = jQuery('input[name="process_type"]:checked').val() || 'Fresh';
    let isRepair = (processType === 'Repair');

    let theadHtml = `<tr>
        <th class="radio_column"></th>` +
        (isRepair ? `<th>Report No.</th><th>Rev. No.</th>` : '') +
        `<th>RT No.</th>
        <th>Inward No.</th>
        <th>Date</th>
        <th>Nature</th>
        <th>NABL</th>
        <th>Test At</th>
        <th>Type</th>
        <th>DC No.</th>
        <th>DC Date</th>
        <th>PO No.</th>
        <th>PO Date</th>
        <th>Type of Job</th>
        <th>Job Desc.</th>
        <th>Part No.</th>
        <th>Drg. No.</th>
        <th>Material</th>
        <th>Heat No.</th>
        <th>Product Code</th>
        <th>Qty.</th>
        <th>Pend. Qty.</th>
    </tr>`;
    $table.find('thead').html(theadHtml);

    let tbody = jQuery('#PendingForRtTable tbody');
    tbody.empty();

    if (pendingInwardData.length === 0) {
        let emptyColspan = isRepair ? 23 : 21;
        tbody.append(`
            <tr>
                <td colspan="${emptyColspan}" class="text-center">No Pending Material Inward Available</td>
            </tr>
        `);
        return;
    }

    let currentVal = jQuery('#material_inward_details_id').val();
    let currentObsVal = jQuery('#observation_sheet_details_id').val();

    pendingInwardData.forEach(item => {
        let pendingQty = parseFloat(item.reported_qty || 0);
        let isSameMid = (item.material_inward_details_id == currentVal);
        let isSameObs = (!currentObsVal && (!item.observation_sheet_details_id || item.observation_sheet_details_id == 0)) || (currentObsVal && item.observation_sheet_details_id == currentObsVal);
        let isChecked = (isSameMid && isSameObs) ? 'checked' : '';
        let repairColsHtml = isRepair ? `<td>${item.report_no || ''}</td><td>${item.revision_no || ''}</td>` : '';

        let rowHtml = `<tr>
            <td>
                <input type="radio" name="pending_rt_id" value="${item.material_inward_details_id}" data-obs-id="${item.observation_sheet_details_id}" class="form-check-input select-pending-rt-radio" ${isChecked}>
            </td>
            ${repairColsHtml}
            <td>${item.repair_rt_no || item.rt_no || ''}</td>
            <td>${item.material_inward_no || ''}</td>
            <td>${item.material_inward_date ? formatDateStr(item.material_inward_date) : ''}</td>
            <td>${item.process_type || ''}</td>
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

    $table.off('draw.dt init.dt').on('draw.dt init.dt', function (e) {
        e.stopPropagation();
    });

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
        "bScrollCollapse": true
    });
}

// Pending button click to open modal
jQuery(document).on('click', '#pending_btn', function () {
    fillPendingRtModalTable();
    jQuery('#PendingInwardForRtModal').modal('show');
});

// Adjust DataTable columns when modal is fully visible
jQuery('#PendingInwardForRtModal').on('shown.bs.modal', function () {
    let $table = jQuery('#PendingForRtTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().columns.adjust();
        if (typeof initColumnSearch === 'function') {
            initColumnSearch('#PendingForRtTable', [0], 'common_search');
        }
    }
});

// Submit pending selection from modal
jQuery(document).on('click', '#submitPendingRtBtn', function () {
    let selectedRadio = jQuery('input[name="pending_rt_id"]:checked');
    if (selectedRadio.length === 0) {
        toastr.error('Please select at least one pending item.');
        return;
    }

    let detailsId = selectedRadio.val();
    let obsId = selectedRadio.attr('data-obs-id');
    if (obsId === 'null' || obsId === 'undefined' || obsId === '0' || !obsId) {
        obsId = null;
    }
    window._selectedMaterialInwardDetailsId = detailsId;
    window._selectedObsSheetDetailsId = obsId;

    jQuery('#material_inward_details_id').val(detailsId).trigger('change');
    jQuery('#PendingInwardForRtModal').modal('hide');

    setTimeout(() => {
        jQuery('#customer_client').focus().select();
    }, 150);
});

// Auto fill form when pending inward item is selected
jQuery(document).on('change', '#material_inward_details_id', function () {
    let detailsId = jQuery(this).val();
    if (!detailsId) {
        if (_openForEdit) {
            _openForEdit = false;
        }
        return;
    }

    // Prevent auto-fill if we are editing an existing report or initializing edit mode
    let currentFormId = jQuery('#id').val();
    if ((currentFormId && currentFormId !== "" && currentFormId != 0) || (_openForEdit && reportMasterData && reportMasterData.material_inward_details_id == detailsId)) {
        _openForEdit = false;
        return;
    }

    let selectedItem;
    if (window._selectedMaterialInwardDetailsId || window._selectedObsSheetDetailsId !== undefined) {
        let reqMid = window._selectedMaterialInwardDetailsId;
        let reqObsId = window._selectedObsSheetDetailsId;
        window._selectedMaterialInwardDetailsId = null;
        window._selectedObsSheetDetailsId = null;

        selectedItem = pendingInwardData.find(item => {
            let midMatch = (item.material_inward_details_id == detailsId || (reqMid && item.material_inward_details_id == reqMid));
            let obsMatch = false;
            if (!reqObsId && (!item.observation_sheet_details_id || item.observation_sheet_details_id == 0)) {
                obsMatch = true;
            } else if (reqObsId && item.observation_sheet_details_id == reqObsId) {
                obsMatch = true;
            }
            return midMatch && obsMatch;
        });
    }

    if (!selectedItem) {
        selectedItem = pendingInwardData.find(item => item.material_inward_details_id == detailsId);
    }

    if (selectedItem) {
        resetPendingInwardFields();
        jQuery('#observation_sheet_details_id').val(selectedItem.observation_sheet_details_id || '');
        jQuery('#from_type_id_fix').val(selectedItem.from_type_id_fix || '');
        let revReportId = selectedItem.revision_from_report_rt_id || selectedItem.test_report_rt_id || 0;
        jQuery('#revision_from_report_rt_id').val(revReportId);
        if (revReportId && revReportId > 0) {
            // Revision Case: Load previous report data
            setRadioReadonly('input[name="nabl_type_fix"]', false);
            setRadioReadonly('input[name="job_type_fix"]', false);
            setRadioReadonly('input[name="process_type"]', false);
            jQuery(`input[name="nabl_type_fix"][value="${selectedItem.nabl_type_fix}"]`).prop('checked', true);
            jQuery(`input[name="job_type_fix"][value="${selectedItem.job_type_fix}"]`).prop('checked', true);
            jQuery('input[name="process_type"][value="Repair"]').prop('checked', true);
            setRadioReadonly('input[name="nabl_type_fix"]', true);
            setRadioReadonly('input[name="job_type_fix"]', true);
            setRadioReadonly('input[name="process_type"]', true);
            handleJobTypeChange();

            let pendingQty = parseFloat(selectedItem.pending_qty !== undefined ? selectedItem.pending_qty : (selectedItem.reported_qty || selectedItem.inward_qty || 0));
            jQuery('#pend_qty').val(pendingQty);
            jQuery('#rt_report_qty').val(pendingQty);
            setSelect2Readonly('#customer_id', true);

            loadRevisionReportData(revReportId, selectedItem);
        } else {
            // Normal New Creation Case
            jQuery('#revision_number').val('');
            jQuery('#revision_test_report_rt_id').val('');
            jQuery('#revision_from_report_rt_id').val(0);
            jQuery('#test_report_sequence').prop('readonly', false);
            jQuery('#ulr_sequence').prop('readonly', false);
            jQuery('#ulr_no').prop('readonly', true).addClass('skip-tab');

            setRadioReadonly('input[name="nabl_type_fix"]', false);
            setRadioReadonly('input[name="job_type_fix"]', false);
            setRadioReadonly('input[name="process_type"]', false);
            jQuery(`input[name="nabl_type_fix"][value="${selectedItem.nabl_type_fix}"]`).prop('checked', true);
            jQuery(`input[name="job_type_fix"][value="${selectedItem.job_type_fix}"]`).prop('checked', true);
            jQuery(`input[name="process_type"][value="${selectedItem.process_type}"]`).prop('checked', true);
            setRadioReadonly('input[name="nabl_type_fix"]', true);
            setRadioReadonly('input[name="job_type_fix"]', true);
            setRadioReadonly('input[name="process_type"]', true);
            handleJobTypeChange();

            jQuery('#type_of_job_id').val(selectedItem.type_of_job_id).trigger('change');
            jQuery('#job_desc_id').val(selectedItem.job_desc_id).trigger('change');
            jQuery('#part_no').val(selectedItem.part_no || '');
            jQuery('#drg_no').val(selectedItem.drg_no || '');
            jQuery('#material_id').val(selectedItem.material_id).trigger('change');
            jQuery('#heat_no').val(selectedItem.heat_no || '');
            jQuery('#rt_no').val(selectedItem.rt_no || '');
            jQuery('#product_code').val(selectedItem.product_code || '');
            jQuery('#dc_no').val(selectedItem.dc_no || '');
            jQuery('#dc_date').val(selectedItem.dc_date ? formatDateStr(selectedItem.dc_date) : '');
            jQuery('#po_no').val(selectedItem.po_no || '');
            jQuery('#po_date').val(selectedItem.po_date ? formatDateStr(selectedItem.po_date) : '');
            jQuery('#date_of_receipt').val(selectedItem.material_inward_date ? formatDateStr(selectedItem.material_inward_date) : '');
            let worksheetVal = selectedItem.observation_sheet_no ? (selectedItem.observation_sheet_no + (selectedItem.observation_sheet_date ? (' - ' + selectedItem.observation_sheet_date) : '')) : '';
            jQuery('#worksheet_no').val(worksheetVal);
            jQuery('#area_of_coverage_id').val(selectedItem.area_of_coverage_id).trigger('change');
            jQuery('#procedure_ref_id').val(selectedItem.procedure_ref_id || '').trigger('change');
            jQuery('#evaluation_as_per_id').val(selectedItem.evaluation_as_per_id || '').trigger('change');
            jQuery('#acceptance_standard_id').val(selectedItem.acceptance_standard_id || '').trigger('change');

            let pendingQty = parseFloat(selectedItem.pending_qty !== undefined ? selectedItem.pending_qty : (selectedItem.reported_qty || selectedItem.inward_qty || 0));
            jQuery('#pend_qty').val(pendingQty);
            jQuery('#rt_report_qty').val(pendingQty);

            if (selectedItem.observation_sheet_details_id && selectedItem.observation_sheet_details_id > 0 && selectedItem.process_type && selectedItem.process_type.toLowerCase() === 'fresh') {
                jQuery.ajax({
                    url: 'get-observation-sub-details',
                    type: 'GET',
                    data: { id: selectedItem.observation_sheet_details_id },
                    headers: headerOpt,
                    dataType: 'json',
                    success: function (res) {
                        if (res.response_code == 1) {
                            // Update film size unit and make it readonly
                            if (res.film_size_unit_fix) {
                                setRadioReadonly('input[name="film_size_unit_fix"]', false);
                                jQuery(`input[name="film_size_unit_fix"][value="${res.film_size_unit_fix}"]`).prop('checked', true);
                                setRadioReadonly('input[name="film_size_unit_fix"]', true);
                            }

                            if (res.sub_details && res.sub_details.length > 0) {
                                test_report_rt_details_data = [];
                                res.sub_details.forEach((row, idx) => {
                                    let film_qty = parseInt(row.film_qty || 1);
                                    let sq_in = parseFloat(row.sq_in || 0);
                                    let sq_cm = parseFloat(row.sq_cm || 0);

                                    test_report_rt_details_data.push({
                                        from_pending: true,
                                        test_report_rt_details_id: '',
                                        main_rt_detail_id: row.main_rt_detail_id || row.main_rt_details_id || null,
                                        record_type_id: row.record_type_id || 1,
                                        sr_no: parseFloat(row.sr_no || (idx + 1)),
                                        identification: row.identification || '',
                                        location: row.location || '',
                                        source_id_fix: row.source_id_fix || '',
                                        detail_film_brand_id: row.film_brand_id || '',
                                        film_brand_name: (row.film_brand_name || '').trim(),
                                        detail_film_type_id: row.film_type_id || '',
                                        film_type_name: (row.film_type_name || '').trim(),
                                        thickness: row.thickness || '',
                                        sfd: row.sfd || '',
                                        optical_density: '',
                                        // detail_iqi_designation_id: row.iqi_designation_id || '',
                                        iqi_designation: (row.iqi_designation || row.iqi_designation_name || row.iqi || '').trim(),
                                        iqi_designation_name: (row.iqi_designation || row.iqi_designation_name || row.iqi || '').trim(),
                                        // detail_iqi_sensitivity_id: row.iqi_sensitivity_id || '',
                                        iqi_sensitivity: (row.iqi_sensitivity || row.iqi_sensitivity_name || row.sensitivity || '').trim(),
                                        iqi_sensitivity_name: (row.iqi_sensitivity || row.iqi_sensitivity_name || row.sensitivity || '').trim(),
                                        detail_film_id: row.detail_film_id || '',
                                        film_size_inch: row.film_size_inch || '',
                                        film_size_cm: row.film_size_cm || '',
                                        no_of_film_fix: row.no_of_film_fix || 'Single',
                                        exposure_time: '',
                                        // detail_finding_id: '',
                                        finding_name: '',
                                        finding: '',
                                        finding_abbreviation: '',
                                        detail_film_result_id: '',
                                        film_result_name: '',
                                        ug: '',
                                        include_in_measurement_sheet: 'Yes',
                                        film_qty: film_qty,
                                        sq_in: sq_in,
                                        sq_cm: sq_cm,
                                        total_sq_in: sq_in * film_qty,
                                        total_sq_cm: sq_cm * film_qty,
                                    });
                                });
                                fillReportDetailTable();
                                jQuery('#addDetailRowBtn').prop('disabled', true);
                            } else {
                                jQuery('#addDetailRowBtn').prop('disabled', false);
                            }
                        }
                    },
                    error: function (jqXHR) {
                        console.log('Error fetching observation sub details:', jqXHR);
                        jQuery('#addDetailRowBtn').prop('disabled', false);
                        setRadioReadonly('input[name="film_size_unit_fix"]', false);
                    }
                });
            } else {
                test_report_rt_details_data = [];
                fillReportDetailTable();
                jQuery('#addDetailRowBtn').prop('disabled', false);
                setRadioReadonly('input[name="film_size_unit_fix"]', false);
            }

            handleNablTypeChange();
            updateCopyReportButtonState();
            updateHeaderSummaries();
            setSelect2Readonly('#customer_id', true);
            setRadioReadonly('input[name="process_type"]', true);
        }
    }
    if (_openForEdit) {
        _openForEdit = false;
    }
});

function resetPendingInwardFields() {
    jQuery('#from_type_id_fix').val('');
    jQuery('#observation_sheet_details_id').val('');
    jQuery('#worksheet_no').val('');
    jQuery('#revision_number').val('');
    jQuery('#revision_test_report_rt_id').val('');
    jQuery('#revision_from_report_rt_id').val('');
    jQuery('#part_no').val('');
    jQuery('#drg_no').val('');
    jQuery('#heat_no').val('');
    jQuery('#rt_no').val('');
    jQuery('#product_code').val('');
    jQuery('#dc_no').val('');
    jQuery('#dc_date').val('');
    jQuery('#po_no').val('');
    jQuery('#po_date').val('');
    jQuery('#date_of_receipt').val('');
    jQuery('#type_of_job_id').val('').trigger('change');
    jQuery('#job_desc_id').val('').trigger('change');
    jQuery('#material_id').val('').trigger('change');
    jQuery('#area_of_coverage_id').val('').trigger('change');
    jQuery('#procedure_ref_id').val('').trigger('change');
    jQuery('#evaluation_as_per_id').val('').trigger('change');
    jQuery('#acceptance_standard_id').val('').trigger('change');
    jQuery('#pend_qty').val('');
    jQuery('#rt_report_qty').val('');
}

function formatDateStr(sqlDate) {
    if (!sqlDate || sqlDate === '0000-00-00') return '';
    let parts = sqlDate.split('-');
    if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    return sqlDate;
}

function loadRevisionReportData(reportId, selectedItem) {
    if (!reportId || reportId == 0) return;

    skipLoader = false;
    showLoader();
    jQuery.ajax({
        url: 'get-pending-report-revision-data',
        type: 'GET',
        data: { id: reportId },
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.report_data) {
                var d = data.report_data;

                // 1. Revision Suffix & Report No
                jQuery('#revision_number').val(data.next_revision_number || 'R1');
                jQuery('#revision_test_report_rt_id').val(d.revision_test_report_rt_id || reportId);
                jQuery('#test_report_rt_id').val(d.test_report_rt_id || reportId);
                jQuery('#test_report_sequence').val(d.test_report_sequence || '');
                jQuery('#test_report_no').val(d.test_report_no || '');
                jQuery('#test_report_sequence').prop('readonly', true).addClass('skip-tab');
                jQuery('#main_test_report_date').val(d.test_report_date);
                // Repair case pending ma Test Report Date open rehse DT.08-09-26
                // jQuery('#test_report_date').prop('readonly', true).css('pointer-events', 'none').addClass('skip-tab').attr('tabindex', '-1');
                // if (jQuery('#test_report_date').data('datepicker')) {
                //     jQuery('#test_report_date').datepicker('disable');
                // }
                jQuery('#rt_report_qty').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
                setSelect2Readonly('#result', false);
                jQuery('#addDetailRowBtn').prop('disabled', true);

                // 2. Process Type forced to Repair & Readonly
                setRadioReadonly('input[name="process_type"]', false);
                jQuery('input[name="process_type"][value="Repair"]').prop('checked', true);
                setRadioReadonly('input[name="process_type"]', true);
                updateCopyReportButtonState();

                if (d.film_size_unit_fix) {
                    setRadioReadonly('input[name="film_size_unit_fix"]', false);
                    jQuery(`input[name="film_size_unit_fix"][value="${d.film_size_unit_fix}"]`).prop('checked', true);
                    setRadioReadonly('input[name="film_size_unit_fix"]', true);
                }

                // 3. NABL & ULR fields
                setRadioReadonly('input[name="nabl_type_fix"]', false);
                jQuery(`input[name="nabl_type_fix"][value="${d.nabl_type_fix || (selectedItem ? selectedItem.nabl_type_fix : 'Non NABL')}"]`).prop('checked', true);
                setRadioReadonly('input[name="nabl_type_fix"]', true);

                if (d.nabl_type_fix === 'NABL') {
                    jQuery('#ulr_sequence').val(d.ulr_sequence || '').prop('readonly', true);
                    jQuery('#ulr_no').val(d.ulr_no || '').prop('readonly', true);
                }

                // 4. Master technical fields (with fallback to selectedItem from pending inward)
                jQuery('#customer_client').val(d.customer_client || '');
                jQuery('#type_of_job_id').val(zeroToEmpty(d.type_of_job_id || (selectedItem ? selectedItem.type_of_job_id : ''))).trigger('change');
                jQuery('#job_desc_id').val(zeroToEmpty(d.job_desc_id || (selectedItem ? selectedItem.job_desc_id : ''))).trigger('change');
                jQuery('#part_no').val(d.part_no || (selectedItem ? selectedItem.part_no : '') || '');
                jQuery('#drg_no').val(d.drg_no || (selectedItem ? selectedItem.drg_no : '') || '');
                jQuery('#material_id').val(zeroToEmpty(d.material_id || (selectedItem ? selectedItem.material_id : ''))).trigger('change');
                jQuery('#heat_no').val(d.heat_no || (selectedItem ? selectedItem.heat_no : '') || '');
                jQuery('#rt_no').val(d.rt_no || (selectedItem ? selectedItem.rt_no : '') || '');
                jQuery('#product_code').val(d.product_code || (selectedItem ? selectedItem.product_code : '') || '');
                jQuery('#dc_no').val(d.dc_no || (selectedItem ? selectedItem.dc_no : '') || '');
                jQuery('#dc_date').val(d.dc_date || (selectedItem && selectedItem.dc_date ? formatDateStr(selectedItem.dc_date) : '') || '');
                jQuery('#po_no').val(d.po_no || (selectedItem ? selectedItem.po_no : '') || '');
                jQuery('#po_date').val(d.po_date || (selectedItem && selectedItem.po_date ? formatDateStr(selectedItem.po_date) : '') || '');
                jQuery('#date_of_receipt').val(d.date_of_receipt || (selectedItem && selectedItem.material_inward_date ? formatDateStr(selectedItem.material_inward_date) : '') || '');
                jQuery('#test_carried_out_at').val(d.test_carried_out_at || (selectedItem ? selectedItem.test_at_fix : '') || '');
                jQuery('#stage_of_test').val(d.stage_of_test || '');
                jQuery('#area_of_coverage_id').val(zeroToEmpty(d.area_of_coverage_id || (selectedItem ? selectedItem.area_of_coverage_id : ''))).trigger('change');
                jQuery('#procedure_ref_id').val(zeroToEmpty(d.procedure_ref_id || (selectedItem ? selectedItem.procedure_ref_id : ''))).trigger('change');
                jQuery('#evaluation_as_per_id').val(zeroToEmpty(d.evaluation_as_per_id || (selectedItem ? selectedItem.evaluation_as_per_id : ''))).trigger('change');
                jQuery('#acceptance_standard_id').val(zeroToEmpty(d.acceptance_standard_id || (selectedItem ? selectedItem.acceptance_standard_id : ''))).trigger('change');
                jQuery('#customer_procedure_ref').val(d.customer_procedure_ref || '');
                jQuery('#technique_sheet_rt_id').val(zeroToEmpty(d.technique_sheet_rt_id));
                jQuery('#rss_no').val(zeroToEmpty(d.technique_sheet_rt_id));
                jQuery('#rss_no_display').val(d.technique_sheet_rt_no || d.rss_no || '');
                jQuery('#rss_reference').val(d.rss_reference || '');
                if (d.extra_cameras && d.extra_cameras.length > 0) {
                    d.extra_cameras.forEach(function (cam) {
                        let selectId = '';
                        if (cam.rt_isotope === 'Ir-192') selectId = '#camera_ir_192_id';
                        else if (cam.rt_isotope === 'Co-60') selectId = '#camera_co_60_id';
                        else if (cam.rt_isotope === 'X-Ray') selectId = '#camera_x_ray_id';

                        if (selectId && jQuery(`${selectId} option[value="${cam.rt_camera_id}"]`).length === 0) {
                            let text = cam.name_for_display || (cam.rt_camera_name + (cam.rt_serial_no ? ' - ' + cam.rt_serial_no : ''));
                            let opt = jQuery(`<option value="${cam.rt_camera_id}" class="dynamic-extra-camera">${text}</option>`);
                            if (cam.rt_isotope === 'X-Ray') {
                                opt.attr('data-xray-kv-ma', cam.rt_x_ray || '');
                                opt.attr('data-xray-focal-size', cam.rt_focal_spot || '');
                            } else {
                                opt.attr('data-isotope', cam.rt_isotope || '');
                                opt.attr('data-source-strength', cam.rtcd_initial_activity_ci || '');
                                opt.attr('data-initial-activity', cam.rtcd_initial_activity_ci || '');
                                opt.attr('data-loading-date', cam.rtcd_last_of_loading_date || '');
                                opt.attr('data-source-size', cam.rtcd_source_size || '');
                            }
                            jQuery(selectId).append(opt);
                        }
                    });
                }
                jQuery('#camera_ir_192_id').val(zeroToEmpty(d.ir_camera_id)).trigger('change', [true]);
                jQuery('#camera_co_60_id').val(zeroToEmpty(d.co_camera_id)).trigger('change', [true]);
                jQuery('#camera_x_ray_id').val(zeroToEmpty(d.xray_camera_id)).trigger('change', [true]);
                jQuery('#source_used').val(d.source_used || '');
                jQuery('#source_strength').val(d.source_strength || '');
                jQuery('#source_size').val(d.source_size || '');
                jQuery('#xray_kv_ma').val(d.xray_kv_ma || '');
                jQuery('#xray_focal_size').val(d.xray_focal_size || '');
                jQuery('#lead_screen_thick').val(d.lead_screen_thick || '');
                jQuery('#lead_screen_thick_back').val(d.lead_screen_thick_back || '');
                jQuery('#iqi').val(d.iqi || '');
                jQuery('#film_processing').val(d.film_processing || 'MANUAL');
                jQuery('#test_technique').val(d.test_technique || '');
                jQuery('#test_arrangement').val(d.test_arrangement || '');
                jQuery('#test_class').val(d.test_class || '');
                jQuery('#film_brand').val(d.film_brand || '');
                jQuery('#film_type').val(d.film_type || '');
                jQuery('#tested_by_authority_person_id').val(zeroToEmpty(d.tested_by_authority_person_id)).trigger('change');
                jQuery('#reviewed_by_authority_person_id').val(zeroToEmpty(d.reviewed_by_authority_person_id)).trigger('change');
                jQuery('#authorized_by_authority_person_id').val(zeroToEmpty(d.authorized_by_authority_person_id)).trigger('change');
                jQuery('#sp_note').val(d.sp_note || '');
                jQuery('#note').val(d.note || '');
                jQuery('#worksheet_no').val(d.worksheet_no || d.observation_sheet_no || '');
                jQuery('#observation_sheet_details_id').val(zeroToEmpty(d.observation_sheet_details_id) || (selectedItem ? selectedItem.observation_sheet_details_id : '') || '');

                // Clear Master Result
                jQuery('#result').val('');

                // 5. Populate Details Table (1 & 2 show, 3 hide; 2 clears finding/result, 1 keeps them if Accepted/Not Accepted)
                test_report_rt_details_data = [];
                if (data.report_details_data && data.report_details_data.length > 0) {
                    data.report_details_data.forEach((row, idx) => {
                        let recTypeId = (row.record_type_id && parseInt(row.record_type_id, 10) > 0) ? parseInt(row.record_type_id, 10) : 1;
                        let filmResultName = (row.film_result_type_fix || row.film_result || '').toLowerCase().trim();

                        let isRepair = (row.is_repair_type == 1 || recTypeId == 2);
                        if (!isRepair && recTypeId == 1) {
                            if (!filmResultName.includes('accept') && !filmResultName.includes('ok')) {
                                isRepair = true;
                                recTypeId = 2;
                            }
                        }

                        let isVisibleRow = (row.is_revision_row !== undefined && row.is_revision_row !== null)
                            ? (row.is_revision_row == 1 || row.is_revision_row === true)
                            : (recTypeId === 1 || recTypeId === 2);

                        // let mainDetailId = (row.main_rt_detail_id && row.main_rt_detail_id != 0)
                        //     ? row.main_rt_detail_id
                        //     : (row.test_report_rt_details_id || '');

                        let mainDetailId = (row.main_rt_detail_id !== undefined && row.main_rt_detail_id !== null)
                            ? row.main_rt_detail_id
                            : 0;

                        if (isVisibleRow) {
                            mainDetailId = 0;
                        }

                        test_report_rt_details_data.push({
                            from_pending: true,
                            test_report_rt_details_id: '',
                            main_rt_detail_id: mainDetailId,
                            record_type_id: recTypeId,
                            is_revision_row: isVisibleRow, // 1 & 2: Show (true), 3: Hide (false)
                            sr_no: parseFloat(row.sr_no || (idx + 1)),
                            identification: row.identification,
                            location: row.location,
                            source_id_fix: row.source_id_fix,
                            detail_film_brand_id: row.film_brand_id,
                            film_brand_name: row.film_brand,
                            detail_film_type_id: row.film_type_id,
                            film_type_name: row.film_type,
                            thickness: row.thickness,
                            sfd: row.sfd,
                            optical_density: row.optical_density,
                            // detail_iqi_designation_id: row.iqi_designation_id,
                            iqi_designation: row.iqi_designation || row.iqi || '',
                            iqi_designation_name: row.iqi_designation || row.iqi || '',
                            // detail_iqi_sensitivity_id: row.iqi_sensitivity_id,
                            iqi_sensitivity: row.iqi_sensitivity || row.sensitivity || '',
                            iqi_sensitivity_name: row.iqi_sensitivity || row.sensitivity || '',
                            detail_film_id: row.film_id,
                            film_size_inch: row.film_size_inch,
                            film_size_cm: row.film_size_cm,
                            no_of_film_fix: row.no_of_film_fix,
                            exposure_time: row.exposure_time || '',
                            finding_name: isRepair ? '' : (row.finding || row.finding_name || ''),
                            finding: isRepair ? '' : (row.finding || row.finding_name || ''),
                            finding_abbreviation: isRepair ? '' : (row.finding_abbreviation || row.abbreviation || ''),
                            detail_film_result_id: isRepair ? '' : (row.detail_film_result_id || row.result_id || ''),
                            film_result_name: isRepair ? '' : (row.film_result || ''),
                            ug: row.ug,
                            include_in_measurement_sheet: 'Yes',
                            film_qty: row.film_qty,
                            sq_in: parseFloat(row.sq_in || 0),
                            sq_cm: parseFloat(row.sq_cm || 0),
                            total_sq_in: parseFloat(row.total_sq_in || 0),
                            total_sq_cm: parseFloat(row.total_sq_cm || 0),
                            mode: 'Add'
                        });
                    });
                }
                fillReportDetailTable();
                jQuery('#addDetailRowBtn').prop('disabled', true);
            }
        },
        complete: function () {
            hideLoader();
            setTimeout(function () {
                focusAppropriateFirstField();
            }, 100);
        }
    });
}

// Sequence fetching / duplication checking
function getLatestTestReportRtSequence() {
    jQuery.ajax({
        url: 'get-latest-test_report_rt_number',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#test_report_sequence').val(data.number);
                jQuery('#test_report_no').val(data.latest_no);
                let dateVal = (typeof currentDate !== 'undefined') ? currentDate : new Date().toLocaleDateString('en-GB');
                jQuery('#test_report_date').val(dateVal).trigger('change');
            }
        }
    });
}

function getPendingCustomersForTestReportRt() {
    let custHtml = '<option value="">Select Customer</option>';
    let url = 'get-pending-customers-for-rt';

    return jQuery.ajax({
        url: url,
        type: 'GET',
        data: { report_id: jQuery('#id').val() },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                data.customers.forEach(cust => {
                    custHtml += `<option value="${cust.id}">${cust.customer}</option>`;
                });
                jQuery('#customer_id').empty().append(custHtml).trigger('change.select2');
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
                url: 'check-test_report_rt_number_duplication',
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

jQuery('#commonTestReportRtForm').find('#test_report_sequence').on('change', function () {
    checkReportSequenceDuplication();
});

jQuery('#commonTestReportRtForm').find('#test_report_sequence').on('focus', function () {
    if (jQuery(this).prop('readonly')) {
        let dateInput = jQuery('#test_report_date');
        dateInput.focus().select();
        setTimeout(function () {
            dateInput.datepicker('hide');
        }, 50);
    }
});

// Detail Sr No auto sequence
function getNextDetailSrNo() {
    let activeRows = test_report_rt_details_data.filter(r => r.mode !== 'Delete');
    if (activeRows.length === 0) return 1;
    let maxSr = Math.max(...activeRows.map(r => parseFloat(r.sr_no || 0)));
    return Math.floor(maxSr) + 1;
}

// Reset entire report form
function resetTestReportRtForm() {
    _openForEdit = false;
    dbUlrId = null;
    dbUlrSequence = null;
    dbUlrNo = null;
    dbUlrYear = null;
    dbTestReportDate = null;
    reportMasterData = null;
    jQuery('.dynamic-extra-camera').remove();
    jQuery('#old_ir_camera_id').val('');
    jQuery('#old_co_camera_id').val('');
    jQuery('#old_xray_camera_id').val('');
    jQuery('#old_source_strength').val('');
    jQuery('#old_source_used').val('');
    jQuery('#old_source_size').val('');
    jQuery('#old_xray_kv_ma').val('');
    jQuery('#old_xray_focal_size').val('');
    jQuery('#old_testing_date').val('');
    selectedInwardDetailsId = null;
    sessionLastOpticalDensity = '';
    var lastNote = jQuery('#note').val();
    document.getElementById("commonTestReportRtForm").reset();
    const form = document.getElementById("commonTestReportRtForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    jQuery('#id').val('');
    jQuery('#from_type_id_fix').val('');
    jQuery('#test_report_sequence').val('');
    jQuery('#test_report_no').val('');
    jQuery('#customer_id').val('').trigger('change');
    jQuery('#material_inward_details_id').val('');
    jQuery('#observation_sheet_details_id').val('');
    jQuery('#revision_number').val('');
    jQuery('#revision_test_report_rt_id').val('');
    jQuery('#customer_client').val('');
    jQuery('#type_of_job_id').val('').trigger('change');
    jQuery('#job_desc_id').val('').trigger('change');
    jQuery('#part_no').val('');
    jQuery('#drg_no').val('');
    jQuery('#material_id').val('').trigger('change');
    jQuery('#heat_no').val('');
    jQuery('#rt_no').val('');
    jQuery('#product_code').val('');
    jQuery('#dc_no').val('');
    jQuery('#dc_date').val('');
    jQuery('#po_no').val('');
    jQuery('#po_date').val('');
    jQuery('#date_of_receipt').val('');
    jQuery('#test_carried_out_at').val('');
    jQuery('#amendment_no, #amendment_date, #amendment_reason').val('').prop('readonly', true).css('pointer-events', 'none').addClass('skip-tab').attr('tabindex', '-1');
    jQuery('#stage_of_test').val('');
    jQuery('#rss_reference').val('');
    jQuery('#welding_process').val('');
    jQuery('#welder_name').val('');
    jQuery('#welder_id').val('');
    jQuery('#position').val('');
    jQuery('#purpose_of_testing').val('');
    jQuery('#film_processing').val('MANUAL');
    jQuery('#joint_type').val('');
    loadRTCamerasDropdown('', '', '');
    setSelect2Readonly('#camera_ir_192_id', true);
    setSelect2Readonly('#camera_co_60_id', true);
    setSelect2Readonly('#camera_x_ray_id', true);
    jQuery('#area_of_coverage_id').val('').trigger('change');
    jQuery('#technique_sheet_rt_id').val('');
    jQuery('#rss_no').val('');
    jQuery('#rss_no_display').val('');
    jQuery('#procedure_ref_id').val('').trigger('change');
    jQuery('#evaluation_as_per_id').val('').trigger('change');
    jQuery('#acceptance_standard_id').val('').trigger('change');
    jQuery('#sp_note').val('');
    jQuery('#note').val(lastNote);
    jQuery('#result').val('').trigger('change');
    setSelect2Readonly('#result', false);

    jQuery('#rep_film_size_inch').prop('checked', true);
    jQuery('#rep_sfd_mm').prop('checked', true);
    jQuery('#rep_print_ug_in_report').prop('checked', false);
    setRadioReadonly('input[name="film_size_unit_fix"]', false);
    setRadioReadonly('input[name="sfd_unit_fix"]', false);
    setRadioReadonly('input[name="nabl_type_fix"]', false);
    setRadioReadonly('input[name="job_type_fix"]', false);
    jQuery('#nabl_non_nabl').prop('checked', true);
    jQuery('#job_casting').prop('checked', true);
    setRadioReadonly('input[name="nabl_type_fix"]', true);
    setRadioReadonly('input[name="job_type_fix"]', true);
    jQuery('#process_type_fresh').prop('checked', true);
    setRadioReadonly('input[name="process_type"]', false);

    // Restore Customer and Pending Inward dropdown interactions and pending button
    setSelect2Readonly('#customer_id', false);
    jQuery('#customer_id').removeClass('skip-tab');
    jQuery('#pending_btn').prop('disabled', true);
    jQuery('#test_report_date').prop('readonly', false).css('pointer-events', 'auto').removeClass('skip-tab').removeAttr('tabindex');
    if (jQuery('#test_report_date').data('datepicker')) {
        jQuery('#test_report_date').datepicker('enable');
    }
    updateCopyReportButtonState();
    jQuery('#test_report_sequence').prop('readonly', false).removeClass('skip-tab');

    handleNablTypeChange();
    handleJobTypeChange();

    jQuery('#tested_by_authority_person_id').val('').trigger('change');
    jQuery('#reviewed_by_authority_person_id').val('').trigger('change');
    jQuery('#authorized_by_authority_person_id').val('').trigger('change');

    jQuery('#preview_btn').hide();

    test_report_rt_details_data = [];
    fillReportDetailTable();

    jQuery('#addDetailRowBtn').prop('disabled', false);
    jQuery('#submitbtn').prop('disabled', false);

    getLatestTestReportRtSequence();
}

// Reset button click
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    let formId = jQuery('#id').val();
    if (!formId) {
        resetTestReportRtForm();
        getLatestTestReportRtSequence();
        getTRLNRData();
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
    } else {
        fetchAndFillTestReportRt(formId);
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
    }
});

// Edit listing item click
jQuery('#dyntable tbody').on('click', '.edit-test_report_rt', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        jQuery('#TestReportRtModal').find('#id').val(data["id"]);
        fetchAndFillTestReportRt(data["id"]);
    }
});

// Fetch report details for Edit
function fetchAndFillTestReportRt(id) {
    if (!id) return;
    _openForEdit = true;

    // Disable submit button until loading is complete
    jQuery('#submitbtn').prop('disabled', true);

    // Set skip-tab class and make readonly immediately to prevent any re-initialization reset during modal show events
    jQuery('#customer_id').addClass('skip-tab');
    setSelect2Readonly('#customer_id', true);
    jQuery('#pending_btn').prop('disabled', true);
    jQuery('#copy_report_btn').prop('disabled', true);
    jQuery('#rss_report_btn').prop('disabled', true);

    skipLoader = false;
    showLoader();
    jQuery.ajax({
        url: "edit-test_report_rt",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.report_data != null) {
                var d = data.report_data;
                selectedInwardDetailsId = d.material_inward_details_id;
                reportMasterData = d;
                jQuery('#id').val(d.test_report_rt_id);

                let url = checkFileRoute + "?id=" + btoa(d.test_report_rt_id) + "&name=" + d.pdf_name + "&type=test_report_rt";
                jQuery('#preview_btn').attr('href', url).show();

                jQuery('#test_report_sequence').val(d.test_report_sequence);
                jQuery('#test_report_no').val(d.test_report_no);
                jQuery('#test_report_date').val(d.test_report_date);
                jQuery('#material_inward_details_id').val(d.material_inward_details_id);
                jQuery('#from_type_id_fix').val(d.from_type_id_fix || 1);
                jQuery('#revision_number').val(d.revision_number || '');
                jQuery('#revision_test_report_rt_id').val(zeroToEmpty(d.revision_test_report_rt_id));
                jQuery('#observation_sheet_details_id').val(zeroToEmpty(d.observation_sheet_details_id));
                jQuery('#worksheet_no').val(d.worksheet_no || d.observation_sheet_no || '');

                if (d.revision_number && d.revision_number !== '') {
                    jQuery('#test_report_sequence').prop('readonly', true);
                } else {
                    jQuery('#test_report_sequence').prop('readonly', false);
                }
                getPendingCustomersForTestReportRt().done(function () {
                    let customerVal = zeroToEmpty(d.customer_id);
                    if (d.customer && customerVal && jQuery('#customer_id option[value="' + customerVal + '"]').length === 0) {
                        jQuery('#customer_id').append(new Option(d.customer, customerVal, true, true)).trigger('change.select2');
                    }
                    jQuery('#customer_id').val(customerVal).trigger('change.select2');

                    // Make Customer readonly and disable pending button in edit mode
                    setSelect2Readonly('#customer_id', true);
                    jQuery('#customer_id').addClass('skip-tab');
                    jQuery('#pending_btn').prop('disabled', true);
                });
                jQuery('#customer_client').val(d.customer_client || '');
                jQuery('#type_of_job_id').val(zeroToEmpty(d.type_of_job_id)).trigger('change');
                jQuery('#job_desc_id').val(zeroToEmpty(d.job_desc_id)).trigger('change');
                jQuery('#part_no').val(d.part_no || '');
                jQuery('#drg_no').val(d.drg_no || '');
                jQuery('#material_id').val(zeroToEmpty(d.material_id)).trigger('change');
                jQuery('#heat_no').val(d.heat_no || '');
                jQuery('#rt_no').val(d.rt_no || '');
                jQuery('#product_code').val(d.product_code || '');
                jQuery('#dc_no').val(d.dc_no || '');
                jQuery('#dc_date').val(d.dc_date);
                jQuery('#po_no').val(d.po_no || '');
                jQuery('#po_date').val(d.po_date);
                jQuery('#date_of_receipt').val(d.date_of_receipt);
                jQuery('#date_of_testing').val(d.date_of_testing);
                jQuery('#date_of_testing_value').val(d.date_of_testing_value || '');
                jQuery('#test_carried_out_at').val(d.test_carried_out_at);
                jQuery('#amendment_no').val(d.amendment_no);
                jQuery('#amendment_date').val(d.amendment_date);
                jQuery('#amendment_reason').val(d.amendment_reason || '');
                jQuery('#stage_of_test').val(d.stage_of_test);
                jQuery('#area_of_coverage_id').val(zeroToEmpty(d.area_of_coverage_id)).trigger('change');
                jQuery('#technique_sheet_rt_id').val(zeroToEmpty(d.technique_sheet_rt_id));
                jQuery('#rss_no').val(zeroToEmpty(d.technique_sheet_rt_id));
                jQuery('#rss_no_display').val(d.technique_sheet_rt_no || d.rss_no || '');
                jQuery('#rss_reference').val(d.rss_reference || '');
                jQuery('#welding_process').val(d.welding_process || '');
                jQuery('#welder_name').val(d.welder_name || '');
                jQuery('#welder_id').val(d.welder_id || '');
                jQuery('#position').val(d.position || '');
                jQuery('#purpose_of_testing').val(d.purpose_of_testing || '');
                jQuery('#joint_type ').val(d.joint_type || '');
                loadRTCamerasDropdown(zeroToEmpty(d.ir_camera_id), zeroToEmpty(d.co_camera_id), zeroToEmpty(d.xray_camera_id)).done(function () {
                    jQuery('#camera_ir_192_id').trigger('change', [true]);
                    jQuery('#camera_co_60_id').trigger('change', [true]);
                    jQuery('#camera_x_ray_id').trigger('change', [true]);
                });
                jQuery('#source_used').val(d.source_used || '');
                jQuery('#source_strength').val(d.source_strength || '');
                jQuery('#source_size').val(d.source_size || '');
                jQuery('#xray_kv_ma').val(d.xray_kv_ma || '');
                jQuery('#xray_focal_size').val(d.xray_focal_size || '');
                jQuery('#lead_screen_thick').val(d.lead_screen_thick || '');
                jQuery('#lead_screen_thick_back').val(d.lead_screen_thick_back || '');
                jQuery('#iqi').val(d.iqi || '');
                jQuery('#film_processing').val(d.film_processing || 'MANUAL');
                jQuery('#test_technique').val(d.test_technique || '');
                jQuery('#test_arrangement').val(d.test_arrangement || '');
                jQuery('#test_class').val(d.test_class || '');
                jQuery('#film_brand').val(d.film_brand || '');
                jQuery('#film_type').val(d.film_type || '');
                jQuery('#procedure_ref_id').val(zeroToEmpty(d.procedure_ref_id)).trigger('change');
                jQuery('#evaluation_as_per_id').val(zeroToEmpty(d.evaluation_as_per_id)).trigger('change');
                jQuery('#acceptance_standard_id').val(zeroToEmpty(d.acceptance_standard_id)).trigger('change');
                jQuery('#customer_procedure_ref').val(d.customer_procedure_ref || '');
                jQuery('#pend_qty').val(d.pend_qty);
                jQuery('#rt_report_qty').val(d.rt_report_qty);

                setRadioReadonly('input[name="nabl_type_fix"]', false);
                setRadioReadonly('input[name="job_type_fix"]', false);
                setRadioReadonly('input[name="process_type"]', false);
                jQuery(`input[name="nabl_type_fix"][value="${d.nabl_type_fix}"]`).prop('checked', true);
                jQuery(`input[name="job_type_fix"][value="${d.job_type_fix}"]`).prop('checked', true);
                jQuery(`input[name="process_type"][value="${d.process_type}"]`).prop('checked', true);
                setRadioReadonly('input[name="nabl_type_fix"]', true);
                setRadioReadonly('input[name="job_type_fix"]', true);
                setRadioReadonly('input[name="process_type"]', true);
                handleNablTypeChange();
                handleJobTypeChange();

                if (d.nabl_type_fix === 'NABL') {
                    dbUlrId = d.ulr_id;
                    dbUlrSequence = d.ulr_sequence;
                    dbUlrNo = d.ulr_no;
                    dbUlrYear = d.ulr_year;
                    dbTestReportDate = d.test_report_date;

                    _preventUlrFetch = true;
                    let ulrVal = zeroToEmpty(d.ulr_id);
                    if (ulrVal && jQuery('#ulr_id option[value="' + ulrVal + '"]').length === 0) {
                        jQuery('#ulr_id').append(new Option(d.ulr_name || '', ulrVal, true, true));
                    }
                    jQuery('#ulr_id').val(ulrVal).trigger('change.select2');
                    jQuery('#ulr_sequence').val(d.ulr_sequence);
                    jQuery('#ulr_no').val(d.ulr_no);
                    jQuery('#ulr_year').val(d.ulr_year);
                    _preventUlrFetch = false;
                } else {
                    dbUlrId = null;
                    dbUlrSequence = null;
                    dbUlrNo = null;
                    dbUlrYear = null;
                    dbTestReportDate = null;
                }

                if (d.film_size_unit_fix === 'cm') {
                    jQuery('#rep_film_size_cm').prop('checked', true);
                } else {
                    jQuery('#rep_film_size_inch').prop('checked', true);
                }

                if (d.sfd_unit_fix === 'inch') {
                    jQuery('#rep_sfd_inch').prop('checked', true);
                } else {
                    jQuery('#rep_sfd_mm').prop('checked', true);
                }

                if (d.print_ug_in_report === 'Yes' || d.print_ug_in_report == 1) {
                    jQuery('#rep_print_ug_in_report').prop('checked', true);
                } else {
                    jQuery('#rep_print_ug_in_report').prop('checked', false);
                }

                setRadioReadonly('input[name="film_size_unit_fix"]', true);
                setRadioReadonly('input[name="sfd_unit_fix"]', false);

                jQuery('#tested_by_authority_person_id').val(zeroToEmpty(d.tested_by_authority_person_id)).trigger('change.select2');
                jQuery('#reviewed_by_authority_person_id').val(zeroToEmpty(d.reviewed_by_authority_person_id)).trigger('change.select2');
                jQuery('#authorized_by_authority_person_id').val(zeroToEmpty(d.authorized_by_authority_person_id)).trigger('change.select2');
                jQuery('#sp_note').val(d.sp_note || '');
                jQuery('#note').val(d.note || '');
                jQuery('#result').val(zeroToEmpty(d.result)).trigger('change.select2');

                var isRevisionReport = (d.revision_number && d.revision_number.toString().trim() !== '');

                test_report_rt_details_data = [];
                if (data.report_details_data && data.report_details_data.length > 0) {
                    data.report_details_data.forEach(row => {
                        let recTypeId = (row.record_type_id && parseInt(row.record_type_id, 10) > 0) ? parseInt(row.record_type_id, 10) : 1;
                        let isRevRow = isRevisionReport ? (recTypeId === 1 || recTypeId === 2) : true;
                        if (row.is_revision_row !== undefined && row.is_revision_row !== null) {
                            isRevRow = (row.is_revision_row == 1 || row.is_revision_row === true);
                        }
                        test_report_rt_details_data.push({
                            test_report_rt_details_id: row.test_report_rt_details_id,
                            main_rt_detail_id: row.main_rt_detail_id || null,
                            record_type_id: recTypeId,
                            is_revision_row: isRevRow,
                            sr_no: parseFloat(row.sr_no || 0),
                            identification: row.identification,
                            location: row.location,
                            source_id_fix: row.source_id_fix,
                            detail_film_brand_id: row.film_brand_id,
                            film_brand_name: row.film_brand,
                            detail_film_type_id: row.film_type_id,
                            film_type_name: row.film_type,
                            thickness: row.thickness,
                            sfd: row.sfd,
                            optical_density: row.optical_density,
                            // detail_iqi_designation_id: row.iqi_designation_id,
                            iqi_designation: row.iqi_designation || row.iqi_designation_name || row.iqi || '',
                            iqi_designation_name: row.iqi_designation || row.iqi_designation_name || row.iqi || '',
                            // detail_iqi_sensitivity_id: row.iqi_sensitivity_id,
                            iqi_sensitivity: row.iqi_sensitivity || row.iqi_sensitivity_name || row.sensitivity || '',
                            iqi_sensitivity_name: row.iqi_sensitivity || row.iqi_sensitivity_name || row.sensitivity || '',
                            detail_film_id: row.film_id,
                            film_size_inch: row.film_size_inch,
                            film_size_cm: row.film_size_cm,
                            no_of_film_fix: row.no_of_film_fix,
                            exposure_time: row.exposure_time,
                            // detail_finding_id: row.finding_id,
                            finding: row.finding || row.finding_name || '',
                            finding_name: row.finding || row.finding_name || '',
                            finding_abbreviation: row.abbreviation,
                            // detail_finding_level_id: '',
                            // finding_level_name: '',
                            detail_film_result_id: row.result_id,
                            film_result_name: row.film_result,
                            ug: row.ug,
                            include_in_measurement_sheet: row.include_in_measurement_sheet,
                            film_qty: row.film_qty,
                            sq_in: parseFloat(row.sq_in || 0),
                            sq_cm: parseFloat(row.sq_cm || 0),
                            total_sq_in: parseFloat(row.total_sq_in || 0),
                            total_sq_cm: parseFloat(row.total_sq_cm || 0),
                            mode: 'Update'
                        });
                    });
                }
                resequenceReportDetails(false);
                fillReportDetailTable();
                jQuery('#add_new').show();

                let hasRevisionBuilt = (d.has_revision == 1 || d.has_revision == true);
                let isUsedReport = (d.is_used_in_ms == 1 || d.is_used_in_os || d.in_use == 1);
                let isUsedMasterResult = (d.is_used_in_os || d.has_next_revision || d.in_use == 1);
                let isNabl = (d.nabl_type_fix === 'NABL');

                if (isUsedReport) {
                    // if (isRevisionReport || hasRevisionBuilt) {
                    jQuery('#test_report_date').prop('readonly', true).css('pointer-events', 'none').addClass('skip-tab').attr('tabindex', '-1');
                    if (jQuery('#test_report_date').data('datepicker')) {
                        jQuery('#test_report_date').datepicker('disable');
                    }
                } else {
                    jQuery('#test_report_date').prop('readonly', false).css('pointer-events', 'auto').removeClass('skip-tab').removeAttr('tabindex');
                    if (jQuery('#test_report_date').data('datepicker')) {
                        jQuery('#test_report_date').datepicker('enable');
                    }
                }

                if (isUsedReport || isRevisionReport || hasRevisionBuilt) {
                    jQuery('#test_report_sequence').prop('readonly', true).addClass('skip-tab');
                    jQuery('#ulr_sequence').prop('readonly', true).addClass('skip-tab');
                } else {
                    jQuery('#test_report_sequence').prop('readonly', false).removeClass('skip-tab');
                    if (isNabl) {
                        jQuery('#ulr_sequence').prop('readonly', false).removeClass('skip-tab');
                    } else {
                        jQuery('#ulr_sequence').prop('readonly', true).addClass('skip-tab');
                    }
                }

                if (isRevisionReport) {
                    jQuery('#ulr_sequence').prop('readonly', true).addClass('skip-tab');
                    jQuery('#ulr_no').prop('readonly', true).addClass('skip-tab');
                } else if (!isUsedReport) {
                    if (isNabl) {
                        jQuery('#ulr_sequence').prop('readonly', false).removeClass('skip-tab');
                        jQuery('#ulr_no').prop('readonly', true).addClass('skip-tab');
                    } else {
                        jQuery('#ulr_sequence').prop('readonly', true).addClass('skip-tab');
                        jQuery('#ulr_no').prop('readonly', true).addClass('skip-tab');
                    }
                }

                let isQtyLocked = (isRevisionReport || hasRevisionBuilt || d.is_used_in_os == 1);

                if (isQtyLocked) {
                    jQuery('#rt_report_qty').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
                } else {
                    if (jQuery('input[name="nabl_type_fix"]:checked').val() !== 'NABL') {
                        jQuery('#rt_report_qty').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
                    }
                }
                var thisForm = jQuery('#TestReportRtModal');
                if (isUsedMasterResult) {
                    setSelect2Readonly('#commonTestReportRtForm #result', true);
                    thisForm.find('#result').addClass('skip-tab');
                } else {
                    setSelect2Readonly('#commonTestReportRtForm #result', false);
                    thisForm.find('#result').removeClass('skip-tab');
                }
                if (isUsedReport || isRevisionReport || hasRevisionBuilt) {
                    jQuery('#addDetailRowBtn').prop('disabled', true);
                } else {
                    jQuery('#addDetailRowBtn').prop('disabled', false);
                }
                updateCopyReportButtonState();
                jQuery('#old_ir_camera_id').val(jQuery('#camera_ir_192_id').val() || (d.ir_camera_id ? d.ir_camera_id : ''));
                jQuery('#old_co_camera_id').val(jQuery('#camera_co_60_id').val() || (d.co_camera_id ? d.co_camera_id : ''));
                jQuery('#old_xray_camera_id').val(jQuery('#camera_x_ray_id').val() || (d.xray_camera_id ? d.xray_camera_id : ''));
                jQuery('#old_source_strength').val(d.source_strength || jQuery('#source_strength').val() || '');
                jQuery('#old_source_used').val(d.source_used || jQuery('#source_used').val() || '');
                jQuery('#old_source_size').val(d.source_size || jQuery('#source_size').val() || '');
                jQuery('#old_xray_kv_ma').val(d.xray_kv_ma || jQuery('#xray_kv_ma').val() || '');
                jQuery('#old_xray_focal_size').val(d.xray_focal_size || jQuery('#xray_focal_size').val() || '');
                jQuery('#old_testing_date').val(d.date_of_testing || jQuery('#date_of_testing').val() || '');

                jQuery('#submitbtn').prop('disabled', false);
                jQuery('#TestReportRtModal').modal('show');
            }
        },
        error: function () {
            jQuery('#submitbtn').prop('disabled', false);
        },
        complete: function () {
            _openForEdit = false;
            hideLoader();
            jQuery('#submitbtn').prop('disabled', false);
        }
    });
}

var _pendingEditRowData = null;

// ReportDetailsModal Shown
jQuery('#ReportDetailsModal').on('shown.bs.modal', function () {
    let thisForm = jQuery('#ReportDetailsModal');
    let sfdUnit = jQuery('input[name="sfd_unit_fix"]:checked').val() || 'mm';
    thisForm.find('label[for="detail_sfd"]').html('SFD (' + sfdUnit + ') <sup class="astric">*</sup>');

    let isRevision = (jQuery('#revision_number').val() && jQuery('#revision_number').val().toString().trim() !== '') || (reportMasterData && reportMasterData.revision_number && reportMasterData.revision_number.toString().trim() !== '');

    if (_pendingEditRowData) {
        let frmData = _pendingEditRowData;
        _pendingEditRowData = null;

        thisForm.find('#detail_sr_no').val(frmData.sr_no);
        thisForm.find('#detail_identification').val(frmData.identification);
        thisForm.find('#detail_location').val(frmData.location);
        thisForm.find('#detail_source_id_fix').val(zeroToEmpty(frmData.source_id_fix)).trigger('change').trigger('change.select2');
        thisForm.find('#detail_film_brand_id').val(zeroToEmpty(frmData.detail_film_brand_id)).trigger('change').trigger('change.select2');
        thisForm.find('#detail_film_type_id').val(zeroToEmpty(frmData.detail_film_type_id)).trigger('change').trigger('change.select2');
        thisForm.find('#detail_thickness').val(frmData.thickness);
        thisForm.find('#detail_sfd').val(frmData.sfd);
        thisForm.find('#detail_optical_density').val(frmData.optical_density || '');
        // thisForm.find('#detail_iqi_designation_id').val(zeroToEmpty(frmData.detail_iqi_designation_id)).trigger('change').trigger('change.select2');
        thisForm.find('#iqi_designation').val(frmData.iqi_designation || frmData.iqi_designation_name || '');
        // thisForm.find('#detail_iqi_sensitivity_id').val(zeroToEmpty(frmData.detail_iqi_sensitivity_id)).trigger('change').trigger('change.select2');
        thisForm.find('#iqi_sensitivity').val(frmData.iqi_sensitivity || frmData.iqi_sensitivity_name || '');
        let mainReportId = jQuery('#commonTestReportRtForm #id').val() || jQuery('#id').val();
        let isExistingSavedRow = (mainReportId && mainReportId != 0 && mainReportId != '0') || (frmData.test_report_rt_details_id && frmData.test_report_rt_details_id != 0);
        let isCopiedRow = (frmData.is_copy === true || frmData.is_copied === true);
        let isExistingOrPendingRow = isExistingSavedRow || frmData.from_pending === true || (frmData.observation_sheet_details_id && frmData.observation_sheet_details_id != 0) || isRevision;

        let filmSelect = thisForm.find('#detail_film_id');
        filmSelect.find('.temp-option').remove();
        let filmId = zeroToEmpty(frmData.detail_film_id || frmData.film_id);

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
        thisForm.find('#detail_no_of_film_fix').val(zeroToEmpty(frmData.no_of_film_fix)).trigger('change').trigger('change.select2');
        thisForm.find('#detail_exposure_time').val(frmData.exposure_time || '');
        // thisForm.find('#detail_finding_id').val(zeroToEmpty(frmData.detail_finding_id)).trigger('change').trigger('change.select2');
        thisForm.find('#finding').val(frmData.finding || frmData.finding_name || '');
        thisForm.find('#finding').data('abbreviation', frmData.finding_abbreviation || '');
        // thisForm.find('#detail_finding_level_id').val(zeroToEmpty(frmData.detail_finding_level_id)).trigger('change').trigger('change.select2');
        thisForm.find('#detail_film_result_id').val(zeroToEmpty(frmData.detail_film_result_id)).trigger('change').trigger('change.select2');
        thisForm.find('#detail_ug').val(frmData.ug || '');
        thisForm.find('#include_in_measurement_sheet').val(frmData.include_in_measurement_sheet || 'Yes');
        updateFilmSizeDropdown();
    } else {
        if (thisForm.find('#form_type').val() === 'add') {
            thisForm.find('#detail_no_of_film_fix').val('Single').trigger('change').trigger('change.select2');
            thisForm.find('#include_in_measurement_sheet').val('Yes');
            updateFilmSizeDropdown();
        }
    }

    // If the report is in use / mapped to a Measurement Sheet or Observation Sheet, disable fields for existing rows
    let inUse = (reportMasterData && (reportMasterData.in_use == true || reportMasterData.in_use == 1)) ? true : false;
    let isUsedInMs = (reportMasterData && (reportMasterData.is_used_in_ms == true || reportMasterData.is_used_in_ms == 1)) ? true : false;
    let isMappedToObs = (reportMasterData && (reportMasterData.is_used_in_os == 1 || reportMasterData.is_used_in_os == true)) ? true : false;
    /*
    OLD CODE:
    var isMappedToRev = (reportMasterData && reportMasterData.has_revision == true) ? true : false;
    let isEditRow = thisForm.find('#form_type').val() === 'edit';
    if ((inUse || isUsedInMs || isMappedToObs || isMappedToRev) && isEditRow) {
        setSelect2Readonly('#detail_source_id_fix', true);
        setSelect2Readonly('#detail_film_id', true);
        setSelect2Readonly('#detail_no_of_film_fix', true);
        setSelect2Readonly('#detail_film_result_id', true);
        thisForm.find('#detail_source_id_fix').addClass('skip-tab');
        thisForm.find('#detail_film_id').addClass('skip-tab');
        thisForm.find('#detail_no_of_film_fix').addClass('skip-tab');
        thisForm.find('#detail_film_result_id').addClass('skip-tab');
    } else {
        setSelect2Readonly('#detail_source_id_fix', false);
        setSelect2Readonly('#detail_film_id', false);
        setSelect2Readonly('#detail_no_of_film_fix', false);
        setSelect2Readonly('#detail_film_result_id', false);
        thisForm.find('#detail_source_id_fix').removeClass('skip-tab');
        thisForm.find('#detail_film_id').removeClass('skip-tab');
        thisForm.find('#detail_no_of_film_fix').removeClass('skip-tab');
        thisForm.find('#detail_film_result_id').removeClass('skip-tab');
    }
    */

    let isCurrentRevisionReport = (jQuery('#revision_number').val() && jQuery('#revision_number').val().toString().trim() !== '') || 
        (reportMasterData && reportMasterData.revision_number && reportMasterData.revision_number.toString().trim() !== '');

    var isMappedToRev = false;
    if (reportMasterData) {
        if (isCurrentRevisionReport) {
            // જો આ પોતે Revision (R1) હોય તો R2 બનેલું હોય (has_next_revision) તો જ Readonly થશે
            isMappedToRev = (reportMasterData.has_next_revision == 1 || reportMasterData.has_next_revision == true || reportMasterData.has_next_revision == "1");
        } else {
            // જો આ Base Report (R0) હોય તો જો R1 બનેલું હોય (has_revision) તો Readonly થશે
            isMappedToRev = (reportMasterData.has_revision == 1 || reportMasterData.has_revision == true || reportMasterData.has_revision == "1" || reportMasterData.has_next_revision == 1 || reportMasterData.has_next_revision == true);
        }
    }

    let isEditRow = thisForm.find('#form_type').val() === 'edit';

    if (isMappedToRev && isEditRow) {
        // જ્યારે Revision બનાવેલું હોય ત્યારે ફોર્મની તમામ ફીલ્ડ્સ Readonly / Disabled કરવું
        jQuery('#copy_from_sr_no').prop('readonly', true).prop('disabled', true).addClass('skip-tab');
        thisForm.find('#detail_sr_no').prop('readonly', true).prop('disabled', true).addClass('skip-tab');
        thisForm.find('#detail_identification').prop('readonly', true).addClass('skip-tab');
        thisForm.find('#detail_location').prop('readonly', true).addClass('skip-tab');

        thisForm.find('#detail_source_id_fix').prop('disabled', true).addClass('skip-tab');
        setSelect2Readonly('#detail_source_id_fix', true);

        thisForm.find('#detail_film_brand_id').prop('disabled', true).addClass('skip-tab');
        setSelect2Readonly('#detail_film_brand_id', true);

        thisForm.find('#detail_film_type_id').prop('disabled', true).addClass('skip-tab');
        setSelect2Readonly('#detail_film_type_id', true);

        thisForm.find('#detail_thickness').prop('readonly', true).addClass('skip-tab');
        thisForm.find('#detail_sfd').prop('readonly', true).addClass('skip-tab');
        thisForm.find('#detail_optical_density').prop('readonly', true).addClass('skip-tab');
        thisForm.find('#iqi_designation').prop('readonly', true).addClass('skip-tab');
        thisForm.find('#iqi_sensitivity').prop('readonly', true).addClass('skip-tab');

        thisForm.find('#detail_film_id').prop('disabled', true).addClass('skip-tab');
        setSelect2Readonly('#detail_film_id', true);

        thisForm.find('#detail_no_of_film_fix').prop('disabled', true).addClass('skip-tab');
        setSelect2Readonly('#detail_no_of_film_fix', true);

        thisForm.find('#detail_exposure_time').prop('readonly', true).addClass('skip-tab');
        thisForm.find('#finding').prop('readonly', false).removeClass('skip-tab');

        thisForm.find('#detail_film_result_id').prop('disabled', true).addClass('skip-tab');
        setSelect2Readonly('#detail_film_result_id', true);

        thisForm.find('#detail_ug').prop('readonly', true).addClass('skip-tab');
        thisForm.find('.plus-icon').addClass('disabled-icon').css({'pointer-events': 'none', 'opacity': '0.5'});
    } else if ((inUse || isUsedInMs || isMappedToObs) && isEditRow) {
        jQuery('#copy_from_sr_no').prop('readonly', false).prop('disabled', false).removeClass('skip-tab');
        thisForm.find('#detail_sr_no').prop('readonly', false).prop('disabled', false).removeClass('skip-tab');
        thisForm.find('#detail_identification').prop('readonly', false).removeClass('skip-tab');
        thisForm.find('#detail_location').prop('readonly', false).removeClass('skip-tab');

        thisForm.find('#detail_film_brand_id').prop('disabled', false).removeClass('skip-tab');
        setSelect2Readonly('#detail_film_brand_id', false);

        thisForm.find('#detail_film_type_id').prop('disabled', false).removeClass('skip-tab');
        setSelect2Readonly('#detail_film_type_id', false);

        thisForm.find('#detail_thickness').prop('readonly', false).removeClass('skip-tab');
        thisForm.find('#detail_sfd').prop('readonly', false).removeClass('skip-tab');
        thisForm.find('#detail_optical_density').prop('readonly', false).removeClass('skip-tab');
        thisForm.find('#iqi_designation').prop('readonly', false).removeClass('skip-tab');
        thisForm.find('#iqi_sensitivity').prop('readonly', false).removeClass('skip-tab');

        thisForm.find('#detail_exposure_time').prop('readonly', false).removeClass('skip-tab');
        thisForm.find('#finding').prop('readonly', false).removeClass('skip-tab');
        thisForm.find('#detail_ug').prop('readonly', false).removeClass('skip-tab');
        thisForm.find('.plus-icon').removeClass('disabled-icon').css({'pointer-events': 'auto', 'opacity': '1'});

        thisForm.find('#detail_source_id_fix').prop('disabled', true).addClass('skip-tab');
        setSelect2Readonly('#detail_source_id_fix', true);

        thisForm.find('#detail_film_id').prop('disabled', true).addClass('skip-tab');
        setSelect2Readonly('#detail_film_id', true);

        thisForm.find('#detail_no_of_film_fix').prop('disabled', true).addClass('skip-tab');
        setSelect2Readonly('#detail_no_of_film_fix', true);

        thisForm.find('#detail_film_result_id').prop('disabled', true).addClass('skip-tab');
        setSelect2Readonly('#detail_film_result_id', true);
    } else {
        jQuery('#copy_from_sr_no').prop('readonly', false).prop('disabled', false).removeClass('skip-tab');
        thisForm.find('#detail_sr_no').prop('readonly', false).prop('disabled', false).removeClass('skip-tab');
        thisForm.find('#detail_identification').prop('readonly', false).removeClass('skip-tab');
        thisForm.find('#detail_location').prop('readonly', false).removeClass('skip-tab');

        thisForm.find('#detail_source_id_fix').prop('disabled', false).removeClass('skip-tab');
        setSelect2Readonly('#detail_source_id_fix', false);

        thisForm.find('#detail_film_brand_id').prop('disabled', false).removeClass('skip-tab');
        setSelect2Readonly('#detail_film_brand_id', false);

        thisForm.find('#detail_film_type_id').prop('disabled', false).removeClass('skip-tab');
        setSelect2Readonly('#detail_film_type_id', false);

        thisForm.find('#detail_thickness').prop('readonly', false).removeClass('skip-tab');
        thisForm.find('#detail_sfd').prop('readonly', false).removeClass('skip-tab');
        thisForm.find('#detail_optical_density').prop('readonly', false).removeClass('skip-tab');
        thisForm.find('#iqi_designation').prop('readonly', false).removeClass('skip-tab');
        thisForm.find('#iqi_sensitivity').prop('readonly', false).removeClass('skip-tab');

        thisForm.find('#detail_film_id').prop('disabled', false).removeClass('skip-tab');
        setSelect2Readonly('#detail_film_id', false);

        thisForm.find('#detail_no_of_film_fix').prop('disabled', false).removeClass('skip-tab');
        setSelect2Readonly('#detail_no_of_film_fix', false);

        thisForm.find('#detail_exposure_time').prop('readonly', false).removeClass('skip-tab');
        thisForm.find('#finding').prop('readonly', false).removeClass('skip-tab');

        thisForm.find('#detail_film_result_id').prop('disabled', false).removeClass('skip-tab');
        setSelect2Readonly('#detail_film_result_id', false);

        thisForm.find('#detail_ug').prop('readonly', false).removeClass('skip-tab');
        thisForm.find('.plus-icon').removeClass('disabled-icon').css({'pointer-events': 'auto', 'opacity': '1'});
    }

    if (isUsedInMs && isEditRow) {
        thisForm.find('#include_in_measurement_sheet').prop('disabled', true);
    } else {
        thisForm.find('#include_in_measurement_sheet').prop('disabled', false);
    }

    // Clear copy from sr no input
    jQuery('#copy_from_sr_no').val('');

    if ((isRevision || isMappedToRev) && isEditRow) {
        thisForm.find('#detail_sr_no').prop('readonly', true).prop('disabled', true).addClass('skip-tab').attr('tabindex', '-1');
        jQuery('#copy_from_sr_no').prop('readonly', true).prop('disabled', true).addClass('skip-tab').attr('tabindex', '-1');
        setTimeout(() => {
            jQuery('#detail_identification').focus().select();
        }, 150);
    } else {
        thisForm.find('#detail_sr_no').prop('readonly', false).prop('disabled', false).removeClass('skip-tab').removeAttr('tabindex');
        jQuery('#copy_from_sr_no').prop('readonly', false).prop('disabled', false).removeClass('skip-tab').removeAttr('tabindex');
        setTimeout(() => {
            jQuery('#detail_sr_no').focus().select();
        }, 150);
    }
});

// Copy data from specified Sr. No. on Blur / Change
jQuery(document).on('blur change', '#copy_from_sr_no', function () {
    let copySrNo = jQuery(this).val().trim();
    if (!copySrNo) return;

    let targetRow = test_report_rt_details_data.find(r => r.mode !== 'Delete' && r.sr_no == copySrNo);
    if (targetRow) {
        let currentSrNo = jQuery('#detail_sr_no').val();

        jQuery('#detail_identification').val(targetRow.identification || '');
        jQuery('#detail_location').val(targetRow.location || '');
        jQuery('#detail_source_id_fix').val(zeroToEmpty(targetRow.source_id_fix)).trigger('change').trigger('change.select2');
        jQuery('#detail_film_brand_id').val(zeroToEmpty(targetRow.detail_film_brand_id)).trigger('change').trigger('change.select2');
        jQuery('#detail_film_type_id').val(zeroToEmpty(targetRow.detail_film_type_id)).trigger('change').trigger('change.select2');
        jQuery('#detail_thickness').val(targetRow.thickness || '');
        jQuery('#detail_sfd').val(targetRow.sfd || '');
        jQuery('#detail_optical_density').val(targetRow.optical_density || '');
        // jQuery('#detail_iqi_designation_id').val(zeroToEmpty(targetRow.detail_iqi_designation_id)).trigger('change').trigger('change.select2');
        jQuery('#iqi_designation').val(targetRow.iqi_designation || targetRow.iqi_designation_name || '');
        // jQuery('#detail_iqi_sensitivity_id').val(zeroToEmpty(targetRow.detail_iqi_sensitivity_id)).trigger('change').trigger('change.select2');
        jQuery('#iqi_sensitivity').val(targetRow.iqi_sensitivity || targetRow.iqi_sensitivity_name || '');
        let mainReportId = jQuery('#commonTestReportRtForm #id').val();
        let isExistingSavedRow = (mainReportId && mainReportId != 0 && mainReportId != '0' && targetRow.test_report_rt_details_id && targetRow.test_report_rt_details_id != 0);

        let targetFilmId = zeroToEmpty(targetRow.detail_film_id || targetRow.film_id);
        let filmSelect = jQuery('#detail_film_id');
        if (isExistingSavedRow && targetFilmId && filmSelect.find('option[value="' + targetFilmId + '"]').length > 0) {
            filmSelect.val(targetFilmId).trigger('change').trigger('change.select2');
        } else if (targetFilmId && filmSelect.find('option[value="' + targetFilmId + '"]:not(.temp-option)').length > 0) {
            filmSelect.val(targetFilmId).trigger('change').trigger('change.select2');
        } else {
            filmSelect.val('').trigger('change').trigger('change.select2');
        }
        jQuery('#detail_no_of_film_fix').val(zeroToEmpty(targetRow.no_of_film_fix)).trigger('change').trigger('change.select2');
        jQuery('#detail_exposure_time').val(targetRow.exposure_time || '');
        jQuery('#finding').val(targetRow.finding || targetRow.finding_name || '');
        jQuery('#finding').data('abbreviation', targetRow.finding_abbreviation || '');
        jQuery('#detail_film_result_id').val(zeroToEmpty(targetRow.detail_film_result_id)).trigger('change').trigger('change.select2');
        jQuery('#detail_ug').val(targetRow.ug || '');
        jQuery('#include_in_measurement_sheet').val(targetRow.include_in_measurement_sheet || 'Yes');
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

// Add Row Button Click
jQuery(document).on('click', '#addDetailRowBtn', function () {
    let formElement = document.getElementById('ReportDetailsForm');
    formElement.reset();
    jQuery('#ReportDetailsForm').removeClass('was-validated');
    jQuery('#copy_from_sr_no').val('');
    jQuery('#detail_film_id').find('.temp-option').remove();
    jQuery('#detail_film_id').val('').trigger('change').trigger('change.select2');
    jQuery('#finding').val('').removeData('abbreviation');
    jQuery('#finding_list').empty();
    jQuery('#iqi_designation').val('');
    jQuery('#iqi_sensitivity').val('');

    jQuery('#form_type').val('add');
    jQuery('#form_index').val('');
    jQuery('#row_index').val('');
    jQuery('#test_report_rt_details_id').val('0');
    jQuery('#detail_main_rt_detail_id').val('');
    jQuery('#detail_record_type_id').val('1');
    jQuery('#include_in_measurement_sheet').val('Yes');
    jQuery('#detail_sr_no').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
    jQuery('#copy_from_sr_no').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');

    jQuery('#detail_sr_no').val(getNextDetailSrNo());
    jQuery('#detail_optical_density').val(sessionLastOpticalDensity);
    jQuery('#submitDetailRowBtn').text('Add');
    _pendingEditRowData = null;
    jQuery('#ReportDetailsModal').modal('show');
});

// Load a specific detail row into ReportDetailsModal by its index in test_report_rt_details_data
function loadReportDetailByIndex(index) {
    let frmData = test_report_rt_details_data[index];
    if (!frmData || frmData.mode === 'Delete') return false;

    _pendingEditRowData = frmData;
    let thisForm = jQuery('#ReportDetailsModal');
    jQuery('#ReportDetailsForm').removeClass('was-validated');

    let sfdUnit = jQuery('input[name="sfd_unit_fix"]:checked').val() || 'mm';
    thisForm.find('label[for="detail_sfd"]').html('SFD (' + sfdUnit + ') <sup class="astric">*</sup>');

    thisForm.find('#form_type').val('edit');
    thisForm.find('#form_index').val(index);
    thisForm.find('#test_report_rt_details_id').val(frmData.test_report_rt_details_id || 0);
    thisForm.find('#detail_main_rt_detail_id').val(frmData.main_rt_detail_id || '');
    thisForm.find('#detail_record_type_id').val(frmData.record_type_id || 1);
    jQuery('#submitDetailRowBtn').text('Edit');

    thisForm.find('#detail_sr_no').val(frmData.sr_no);
    thisForm.find('#detail_identification').val(frmData.identification || '');
    thisForm.find('#detail_location').val(frmData.location || '');
    thisForm.find('#detail_source_id_fix').val(zeroToEmpty(frmData.source_id_fix)).trigger('change').trigger('change.select2');
    thisForm.find('#detail_film_brand_id').val(zeroToEmpty(frmData.detail_film_brand_id)).trigger('change').trigger('change.select2');
    thisForm.find('#detail_film_type_id').val(zeroToEmpty(frmData.detail_film_type_id)).trigger('change').trigger('change.select2');
    thisForm.find('#detail_thickness').val(frmData.thickness || '');
    thisForm.find('#detail_sfd').val(frmData.sfd || '');
    thisForm.find('#detail_optical_density').val(frmData.optical_density || '');
    // thisForm.find('#detail_iqi_designation_id').val(zeroToEmpty(frmData.detail_iqi_designation_id)).trigger('change').trigger('change.select2');
    thisForm.find('#iqi_designation').val(frmData.iqi_designation || frmData.iqi_designation_name || '');
    // thisForm.find('#detail_iqi_sensitivity_id').val(zeroToEmpty(frmData.detail_iqi_sensitivity_id)).trigger('change').trigger('change.select2');
    thisForm.find('#iqi_sensitivity').val(frmData.iqi_sensitivity || frmData.iqi_sensitivity_name || '');

    let mainReportId = jQuery('#commonTestReportRtForm #id').val() || jQuery('#id').val();
    let isExistingSavedRow = (mainReportId && mainReportId != 0 && mainReportId != '0') || (frmData.test_report_rt_details_id && frmData.test_report_rt_details_id != 0);
    let isCopiedRow = (frmData.is_copy === true || frmData.is_copied === true);
    let isRevision = (jQuery('#revision_number').val() && jQuery('#revision_number').val().toString().trim() !== '') || (reportMasterData && reportMasterData.revision_number && reportMasterData.revision_number.toString().trim() !== '');
    let isExistingOrPendingRow = isExistingSavedRow || frmData.from_pending === true || (frmData.observation_sheet_details_id && frmData.observation_sheet_details_id != 0) || isRevision;

    let filmSelect = thisForm.find('#detail_film_id');
    filmSelect.find('.temp-option').remove();
    let filmId = zeroToEmpty(frmData.detail_film_id || frmData.film_id);
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
    thisForm.find('#detail_no_of_film_fix').val(zeroToEmpty(frmData.no_of_film_fix)).trigger('change').trigger('change.select2');
    thisForm.find('#detail_exposure_time').val(frmData.exposure_time || '');
    thisForm.find('#finding').val(frmData.finding || frmData.finding_name || '');
    thisForm.find('#finding').data('abbreviation', frmData.finding_abbreviation || '');
    thisForm.find('#detail_film_result_id').val(zeroToEmpty(frmData.detail_film_result_id)).trigger('change').trigger('change.select2');
    thisForm.find('#detail_ug').val(frmData.ug || '');
    thisForm.find('#include_in_measurement_sheet').val(frmData.include_in_measurement_sheet || 'Yes');
    updateFilmSizeDropdown();

    // If report is mapped / in use, apply readonly to locked fields
    let inUse = (reportMasterData && (reportMasterData.in_use == true || reportMasterData.in_use == 1)) ? true : false;
    let isUsedInMs = (reportMasterData && (reportMasterData.is_used_in_ms == true || reportMasterData.is_used_in_ms == 1)) ? true : false;
    let isMappedToObs = (reportMasterData && (reportMasterData.is_used_in_os == 1 || reportMasterData.is_used_in_os == true)) ? true : false;
    if (inUse || isUsedInMs || isMappedToObs) {
        setSelect2Readonly('#detail_source_id_fix', true);
        setSelect2Readonly('#detail_film_id', true);
        setSelect2Readonly('#detail_no_of_film_fix', true);
        setSelect2Readonly('#detail_film_result_id', true);
        thisForm.find('#detail_source_id_fix').addClass('skip-tab');
        thisForm.find('#detail_film_id').addClass('skip-tab');
        thisForm.find('#detail_no_of_film_fix').addClass('skip-tab');
        thisForm.find('#detail_film_result_id').addClass('skip-tab');
    } else {
        setSelect2Readonly('#detail_source_id_fix', false);
        setSelect2Readonly('#detail_film_id', false);
        setSelect2Readonly('#detail_no_of_film_fix', false);
        setSelect2Readonly('#detail_film_result_id', false);
        thisForm.find('#detail_source_id_fix').removeClass('skip-tab');
        thisForm.find('#detail_film_id').removeClass('skip-tab');
        thisForm.find('#detail_no_of_film_fix').removeClass('skip-tab');
        thisForm.find('#detail_film_result_id').removeClass('skip-tab');
    }

    if (isUsedInMs) {
        thisForm.find('#include_in_measurement_sheet').prop('disabled', true);
    } else {
        thisForm.find('#include_in_measurement_sheet').prop('disabled', false);
    }

    jQuery('#copy_from_sr_no').val('');

    if (isRevision) {
        thisForm.find('#detail_sr_no').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
        jQuery('#copy_from_sr_no').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
        setTimeout(() => {
            jQuery('#detail_identification').focus().select();
        }, 100);
    } else {
        thisForm.find('#detail_sr_no').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
        jQuery('#copy_from_sr_no').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
        setTimeout(() => {
            jQuery('#detail_sr_no').focus().select();
        }, 100);
    }

    return true;
}

// Edit Details
function editReportDetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();

    let thisForm = jQuery('#ReportDetailsModal');
    let frmData = test_report_rt_details_data[formIndx];

    jQuery('#ReportDetailsForm').removeClass('was-validated');

    thisForm.find('#form_type').val('edit');
    thisForm.find('#form_index').val(formIndx);
    thisForm.find('#row_index').val(rawIndx);
    thisForm.find('#test_report_rt_details_id').val(frmData.test_report_rt_details_id || 0);
    thisForm.find('#detail_main_rt_detail_id').val(frmData.main_rt_detail_id || '');
    thisForm.find('#detail_record_type_id').val(frmData.record_type_id || 1);
    jQuery('#submitDetailRowBtn').text('Edit');

    _pendingEditRowData = frmData;
    thisForm.modal('show');
}

// Remove Details
function removeReportDetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        let item = test_report_rt_details_data[formIndx];
        if (item.test_report_rt_details_id && item.test_report_rt_details_id != 0) {
            item.mode = "Delete";
        } else {
            test_report_rt_details_data.splice(formIndx, 1);
        }
        resequenceReportDetails(true);
        fillReportDetailTable();
    });
}

// Detail Row Submit
jQuery('#submitDetailRowBtn').on('click', function (e) {
    e.preventDefault();
    let form = document.getElementById('ReportDetailsForm');

    if (!form.checkValidity()) {
        e.stopPropagation();
        jQuery(form).addClass('was-validated');
        return;
    }

    sessionLastOpticalDensity = jQuery('#detail_optical_density').val() || '';

    let sr_no = parseFloat(jQuery('#detail_sr_no').val() || 0);
    let form_type = jQuery('#form_type').val();
    let form_index = jQuery('#form_index').val();

    var isRevision = (jQuery('#revision_number').val() && jQuery('#revision_number').val().toString().trim() !== '') ||
        (reportMasterData && reportMasterData.revision_number && reportMasterData.revision_number.toString().trim() !== '');

    let obsDetailsId = jQuery('#observation_sheet_details_id').val();
    let isObs = (obsDetailsId && obsDetailsId.toString().trim() !== '' && obsDetailsId != 0 && obsDetailsId != '0') ||
        (reportMasterData && reportMasterData.observation_sheet_details_id && reportMasterData.observation_sheet_details_id != 0);

    // Shift serial numbers if there's a duplicate (ONLY for Direct Inward)
    if (!isRevision && !isObs) {
        let duplicateExists = test_report_rt_details_data.some((row, index) => {
            if (row.mode === 'Delete') return false;
            if (form_type === 'edit' && index == form_index) return false;
            return row.sr_no == sr_no;
        });

        if (duplicateExists) {
            test_report_rt_details_data.forEach((row, index) => {
                if (row.mode === 'Delete') return;
                if (form_type === 'edit' && index == form_index) return;
                if (row.sr_no >= sr_no) {
                    row.sr_no = row.sr_no + 1;
                }
            });
        }
    }

    let no_of_film_fix = jQuery('#detail_no_of_film_fix').val();
    let film_qty = 1;
    if (no_of_film_fix === 'Double') film_qty = 2;
    else if (no_of_film_fix === 'Triple') film_qty = 3;
    else if (no_of_film_fix === 'Quadra') film_qty = 4;

    let selectedFilm = jQuery('#detail_film_id option:selected');
    let sq_in = parseFloat(selectedFilm.attr('data-sq_in') || 0);
    let sq_cm = parseFloat(selectedFilm.attr('data-sq_cm') || 0);

    let main_rt_detail_id = jQuery('#detail_main_rt_detail_id').val() || null;
    let record_type_id = parseInt(jQuery('#detail_record_type_id').val() || 1, 10);

    let selectedResultOption = jQuery('#detail_film_result_id option:selected');
    let filmResultType = (selectedResultOption.attr('data-film-result-type') || '').toLowerCase();
    if (!filmResultType) {
        let resultText = (selectedResultOption.text() || '').toLowerCase();
        if (resultText.includes('repair') || resultText.includes('retake') || resultText.includes('reshoot')) {
            filmResultType = 'repair';
        } else if (resultText.includes('ok') || resultText.includes('accept')) {
            filmResultType = 'accepted';
        }
    }

    isRevision = (jQuery('#revision_number').val() && jQuery('#revision_number').val().toString().trim() !== '');

    if (isRevision) {
        // રિવિઝનમાં એડિટ થતા જોઈન્ટ્સનો record_type_id મોડલમાંથી લેવો (જો ન હોય તો ડિફોલ્ટ 2)
        record_type_id = parseInt(jQuery('#detail_record_type_id').val() || 2, 10);
    } else {
        // Fresh Direct Inward અથવા Observation Sheet માટે
        record_type_id = 1;
    }

    let formValue = {
        test_report_rt_details_id: jQuery('#test_report_rt_details_id').val() || 0,
        main_rt_detail_id: main_rt_detail_id,
        record_type_id: record_type_id,
        is_revision_row: true, // સ્ક્રીન પરથી હાઈડ નહિ થાય
        sr_no: sr_no,
        identification: jQuery('#detail_identification').val(),
        location: jQuery('#detail_location').val(),
        source_id_fix: jQuery('#detail_source_id_fix').val(),
        detail_film_brand_id: jQuery('#detail_film_brand_id').val(),
        film_brand_name: jQuery('#detail_film_brand_id option:selected').text().trim(),
        detail_film_type_id: jQuery('#detail_film_type_id').val(),
        film_type_name: jQuery('#detail_film_type_id option:selected').text().trim(),
        thickness: jQuery('#detail_thickness').val(),
        sfd: jQuery('#detail_sfd').val(),
        optical_density: jQuery('#detail_optical_density').val(),
        iqi_designation: jQuery('#iqi_designation').val().trim(),
        iqi_designation_name: jQuery('#iqi_designation').val().trim(),
        // detail_iqi_designation_id: '',
        iqi_sensitivity: jQuery('#iqi_sensitivity').val().trim(),
        iqi_sensitivity_name: jQuery('#iqi_sensitivity').val().trim(),
        // detail_iqi_sensitivity_id: '',
        detail_film_id: jQuery('#detail_film_id').val(),
        film_size_inch: selectedFilm.attr('data-inch') || '',
        film_size_cm: selectedFilm.attr('data-cm') || '',
        no_of_film_fix: no_of_film_fix,
        exposure_time: jQuery('#detail_exposure_time').val(),
        finding: jQuery('#finding').val().trim(),
        finding_name: jQuery('#finding').val().trim(),
        // detail_finding_id: '',
        finding_abbreviation: jQuery('#finding').data('abbreviation') || jQuery('#finding').val().trim(),
        // detail_finding_level_id: '',
        // finding_level_name: '',
        detail_film_result_id: jQuery('#detail_film_result_id').val() || '',
        film_result_name: jQuery('#detail_film_result_id').val() ? jQuery('#detail_film_result_id option:selected').text().trim() : '',
        ug: jQuery('#detail_ug').val(),
        include_in_measurement_sheet: 'Yes',
        film_qty: film_qty,
        sq_in: sq_in,
        sq_cm: sq_cm,
        total_sq_in: sq_in * film_qty,
        total_sq_cm: sq_cm * film_qty,
    };

    if (form_type === 'edit') {
        formValue.mode = (formValue.test_report_rt_details_id == 0) ? 'Insert' : 'Update';
        test_report_rt_details_data[form_index] = formValue;
        resequenceReportDetails(false);
        fillReportDetailTable();
        toastSuccess("Record Updated.");

        // Continuous Edit: find the next active row by serial number
        let currentSrNo = parseFloat(formValue.sr_no || 0);
        let nextRowCandidate = null;
        let nextRowIndex = -1;
        let isRev = (jQuery('#revision_number').val() && jQuery('#revision_number').val().toString().trim() !== '') ||
            (reportMasterData && reportMasterData.revision_number && reportMasterData.revision_number.toString().trim() !== '');

        test_report_rt_details_data.forEach((row, idx) => {
            if (row.mode === 'Delete') return;
            // માત્ર એક્ટિવ શો થતા જોઈન્ટ્સ (is_revision_row !== false) પર જ આગળ વધવું
            if (row.is_revision_row === false) return;
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
            loadReportDetailByIndex(nextRowIndex);
        } else {
            // Last row reached: close modal
            jQuery('#ReportDetailsModal').modal('hide');
        }
        return;
    } else {
        formValue.mode = 'Insert';
        test_report_rt_details_data.push(formValue);

        if (_lnrDetails) {
            let locVal = jQuery('#detail_location').val();
            let nextLocVal = checkValue(locVal);

            // LNR Behavior: Keep entered values, only update sr_no and location
            jQuery('#detail_sr_no').val(getNextDetailSrNo());
            jQuery('#detail_location').val(nextLocVal);
            jQuery('#copy_from_sr_no').val('');
            jQuery('#ReportDetailsForm').removeClass('was-validated');
            setTimeout(() => {
                jQuery('#detail_sr_no').focus().select();
            }, 250);
            toastSuccess("Record Inserted.");
        } else {
            // Reset details form for rapid entry (Normal Behavior)
            form.reset();
            jQuery('#ReportDetailsForm .js-example-basic-single').val('').trigger('change').trigger('change.select2');
            jQuery('#detail_sr_no').val(getNextDetailSrNo());
            jQuery('#detail_no_of_film_fix').val('Single').trigger('change').trigger('change.select2');
            jQuery('#detail_optical_density').val(sessionLastOpticalDensity);
            jQuery('#include_in_measurement_sheet').val('Yes');
            jQuery('#finding').val('').removeData('abbreviation');
            jQuery('#finding_list').empty();
            jQuery('#iqi_designation').val('');
            jQuery('#iqi_sensitivity').val('');
            jQuery('#copy_from_sr_no').val('');
            jQuery('#ReportDetailsForm').removeClass('was-validated');
            setTimeout(() => {
                jQuery('#detail_sr_no').focus().select();
            }, 250);
            toastSuccess("Record Inserted.");
        }
    }

    resequenceReportDetails(false);
    fillReportDetailTable();
});

// Detail modal hidden focus redirection
jQuery('#ReportDetailsModal').on('hidden.bs.modal', function () {
    this.dataset.customHideFocus = 'true';
    setTimeout(function () {
        let $select = jQuery('#tested_by_authority_person_id');
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

// Update detail array when grid checkbox changes
jQuery('#ReportDetailTable tbody').on('change', '.grid-include-checkbox', function () {
    let formIndx = jQuery(this).data('index');
    let isChecked = jQuery(this).is(':checked');
    if (test_report_rt_details_data[formIndx]) {
        test_report_rt_details_data[formIndx].include_in_measurement_sheet = isChecked ? 'Yes' : null;
    }
});

// Modal open/close events
jQuery('#TestReportRtModal').on('hide.bs.modal', function () {
    resetTestReportRtForm();
    jQuery('#add_new').hide();
});

jQuery('#TestReportRtModal').on('shown.bs.modal', function () {
    fillReportDetailTable();
    let formId = jQuery('#id').val();
    if (formId && formId !== "") {
        jQuery('#add_new').show();
        setTimeout(function () {
            focusAppropriateFirstField();
        }, 150);
    } else {
        getPendingCustomersForTestReportRt();
        jQuery('#add_new').hide();
        getLatestTestReportRtSequence();
        getTRLNRData();
        setTimeout(function () {
            focusAppropriateFirstField();
        }, 150);
    }
});

// Main Form Submit
jQuery('#submitbtn').on('click', function (e) {
    e.preventDefault();
    let form = document.getElementById('commonTestReportRtForm');

    if (!form.checkValidity()) {
        e.stopPropagation();
        jQuery(form).addClass('was-validated');

        let $firstInvalid = jQuery(form).find(':invalid, .is-invalid').first();
        if ($firstInvalid.length) {
            $firstInvalid.focus();
            if ($firstInvalid.hasClass('select2-hidden-accessible')) {
                $firstInvalid.select2('open');
            }
            if ($firstInvalid[0] && typeof $firstInvalid[0].scrollIntoView === 'function') {
                $firstInvalid[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
        return;
    }

    var dateValue = jQuery('#test_report_date').val().trim();
    if (!isValidDate(dateValue)) {
        toastr.error("Enter A Valid Date!");
        jQuery('#test_report_date').focus();
        if (jQuery('#test_report_date')[0] && typeof jQuery('#test_report_date')[0].scrollIntoView === 'function') {
            jQuery('#test_report_date')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Selected Financial Year.");
        jQuery('#test_report_date').focus();
        if (jQuery('#test_report_date')[0] && typeof jQuery('#test_report_date')[0].scrollIntoView === 'function') {
            jQuery('#test_report_date')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }

    if (!validateDates()) {
        return;
    }

    let inwardDetailsId = jQuery('#material_inward_details_id').val();
    if (!inwardDetailsId) {
        toastr.error("Please Add At Least Inward from Pending List");
        return;
    }

    let rtReportQty = parseInt(jQuery('#rt_report_qty').val() || 0);
    let pendQty = parseInt(jQuery('#pend_qty').val() || 0);
    if (rtReportQty > pendQty) {
        toastr.error("RT Report Qty. cannot be more than Pending Qty.");
        return;
    }

    let activeDetails = test_report_rt_details_data.filter(row => row.mode !== 'Delete' && row.is_revision_row !== false);
    if (activeDetails.length === 0) {
        toastr.error("Please Add at least one RT Report Detail");
        return;
    }

    let mainReportId = jQuery('#commonTestReportRtForm #id').val();
    let isExistingReport = (mainReportId && mainReportId != 0 && mainReportId != '0');
    let isRevision = (jQuery('#revision_number').val() && jQuery('#revision_number').val().toString().trim() !== '') || (reportMasterData && reportMasterData.revision_number && reportMasterData.revision_number.toString().trim() !== '');

    let invalidFilmRow = activeDetails.find(r => {
        let fid = zeroToEmpty(r.detail_film_id || r.film_id);
        if (!fid) return true;
        if ((isExistingReport && r.test_report_rt_details_id && r.test_report_rt_details_id != 0) || r.from_pending === true || (r.observation_sheet_details_id && r.observation_sheet_details_id != 0) || isRevision) {
            return false;
        }
        let isFilmActive = jQuery('#detail_film_id option[value="' + fid + '"]:not(.temp-option)').length > 0;
        return !isFilmActive;
    });
    if (invalidFilmRow) {
        toastr.error("Please Select Film Size");
        return;
    }

    let allActiveRows = test_report_rt_details_data.filter(row => row.mode !== 'Delete');
    let activeSources = [...new Set(allActiveRows.map(r => r.source_id_fix).filter(s => s))];
    if (activeSources.includes('Ir-192') && !jQuery('#camera_ir_192_id').val()) {
        toastr.error("Please Select Camera Ir-192");
        jQuery('#camera_ir_192_id').focus();
        if (jQuery('#camera_ir_192_id').hasClass('select2-hidden-accessible')) jQuery('#camera_ir_192_id').select2('open');
        if (jQuery('#camera_ir_192_id')[0] && typeof jQuery('#camera_ir_192_id')[0].scrollIntoView === 'function') {
            jQuery('#camera_ir_192_id')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }
    if (activeSources.includes('Co-60') && !jQuery('#camera_co_60_id').val()) {
        toastr.error("Please Select Camera Co-60");
        jQuery('#camera_co_60_id').focus();
        if (jQuery('#camera_co_60_id').hasClass('select2-hidden-accessible')) jQuery('#camera_co_60_id').select2('open');
        if (jQuery('#camera_co_60_id')[0] && typeof jQuery('#camera_co_60_id')[0].scrollIntoView === 'function') {
            jQuery('#camera_co_60_id')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }
    if (activeSources.includes('X-Ray') && !jQuery('#camera_x_ray_id').val()) {
        toastr.error("Please Select Camera X-Ray");
        jQuery('#camera_x_ray_id').focus();
        if (jQuery('#camera_x_ray_id').hasClass('select2-hidden-accessible')) jQuery('#camera_x_ray_id').select2('open');
        if (jQuery('#camera_x_ray_id')[0] && typeof jQuery('#camera_x_ray_id')[0].scrollIntoView === 'function') {
            jQuery('#camera_x_ray_id')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }

    for (let i = 0; i < activeDetails.length; i++) {
        let row = activeDetails[i];
        let srNo = row.sr_no || (i + 1);
        if (!row.finding && !row.finding_name) {
            toastr.error(`Enter Finding in Test Report Details.`);
            return;
        }
        if (!row.detail_film_result_id && !row.film_result_name) {
            toastr.error(`Please select Result Test Report Details`);
            return;
        }
    }

    jQuery('#submitbtn').prop('disabled', true);
    skipLoader = false;
    showLoader();

    let formId = jQuery('#id').val();
    let formUrl = formId ? "update-test_report_rt" : "store-test_report_rt";

    // Temporarily enable disabled inputs so their values are sent in FormData
    let disabledInputs = jQuery(form).find(':disabled');
    disabledInputs.prop('disabled', false);

    let formData = new FormData(form);
    formData.append('report_details_data', JSON.stringify(test_report_rt_details_data));

    // Restore disabled state
    disabledInputs.prop('disabled', true);

    jQuery.ajax({
        url: formUrl,
        type: 'POST',
        data: formData,
        headers: { 'X-CSRF-TOKEN': jQuery('input[name="_token"]').val() },
        processData: false,
        contentType: false,
        success: function (data) {
            jQuery('#submitbtn').prop('disabled', false);
            hideLoader();
            if (data.response_code == 1) {
                if (formId) {
                    // let redirectFn = function () {
                    //     jQuery('#TestReportRtModal').modal('hide');
                    //     if (typeof table !== 'undefined') table.draw(false);
                    // };
                    // console.log(pageRelod, window.location.origin);
                    // return;
                    let redirectFn = function () {
                        if (typeof pageRelod !== 'undefined' && pageRelod == 'Yes') {
                            setTimeout(function () {
                                focusAppropriateFirstField();
                            }, 50);
                        } else {
                            window.location.reload();
                        }
                    };
                    if (typeof pageRelod !== 'undefined' && pageRelod == 'Yes') {
                        const form = document.getElementById("commonTestReportRtForm");
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
                        getPendingCustomersForTestReportRt().done(function () {
                            setSelect2Readonly('#customer_id', false);
                            jQuery('#customer_id').removeClass('skip-tab');
                            getTRLNRData();
                        });
                        resetTestReportRtForm();
                        setTimeout(function () {
                            focusAppropriateFirstField();
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
        error: function () {
            jQuery('#submitbtn').prop('disabled', false);
            hideLoader();
            toastr.error('Something went wrong!');
        }
    });
});

jQuery(document).on('click', '#add_new', function () {
    resetTestReportRtForm();
    getPendingCustomersForTestReportRt().done(function () {
        setSelect2Readonly('#customer_id', false);
        jQuery('#customer_id').removeClass('skip-tab');
        getTRLNRData();
    });
    focusAppropriateFirstField();
    jQuery('#add_new').hide();
});

function focusAppropriateFirstField() {
    let seqInput = jQuery('#test_report_sequence');
    if (!seqInput.prop('readonly')) {
        seqInput.focus().select();
    } else {
        let dateInput = jQuery('#test_report_date');
        if (!dateInput.prop('readonly')) {
            dateInput.focus().select();
            setTimeout(function () {
                dateInput.datepicker('hide');
            }, 50);
        } else {
            jQuery('#customer_client').focus();
        }
    }
}

function updateCopyReportButtonState() {
    let formId = jQuery('#id').val();
    let processType = jQuery('input[name="process_type"]:checked').val() || '';
    let obsDetailsId = jQuery('#observation_sheet_details_id').val() || '';
    if (!formId && processType === 'Fresh' && (!obsDetailsId || obsDetailsId == '0')) {
        jQuery('#copy_report_btn').prop('disabled', false);
        jQuery('#rss_report_btn').prop('disabled', false);
    } else {
        jQuery('#copy_report_btn').prop('disabled', true);
        jQuery('#rss_report_btn').prop('disabled', true);
    }
}

function handleNablTypeChange() {
    let nablType = jQuery('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
    if (nablType === 'NABL') {
        jQuery('#rt_report_qty').val(1).prop('readonly', true).attr('tabindex', '-1');

        setSelect2Readonly('#ulr_id', false);
        jQuery('#ulr_id').removeClass('skip-tab').trigger('change');
        // jQuery('#ulr_id').removeClass('skip-tab').prop('required', true).trigger('change');

        jQuery('#ulr_sequence').prop('readonly', false).prop('required', true).removeAttr('tabindex');
        jQuery('#ulr_no').prop('readonly', true);
        jQuery('.astric_ulr').html('<sup class="astric">*</sup>');
        jQuery('.astric_ulr_seq').html('<sup class="astric">*</sup>');

        let $ulr = jQuery('#ulr_id');
        let $options = $ulr.find('option[value!=""]'); // Ignore placeholder option

        if ($options.length === 1) {
            $ulr.val($options.first().val()).trigger('change');
            setSelect2Readonly('#ulr_id', true);
            jQuery('#ulr_id').addClass('skip-tab');
        } else {
            setSelect2Readonly('#ulr_id', false);
            jQuery('#ulr_id').removeClass('skip-tab');
        }

        jQuery('#amendment_no, #amendment_date, #amendment_reason').prop('readonly', false).css('pointer-events', 'auto').removeClass('skip-tab').removeAttr('tabindex');
        if (jQuery('#amendment_date').data('datepicker')) {
            jQuery('#amendment_date').datepicker('enable');
        }
    } else {
        let isRevision = (jQuery('#revision_number').val() && jQuery('#revision_number').val().toString().trim() !== '');
        let isQtyLocked = isRevision;
        if (typeof reportMasterData !== 'undefined' && reportMasterData) {
            let hasRevisionBuilt = (reportMasterData.has_revision == 1 || reportMasterData.has_revision == true);
            let isUsedInOs = (reportMasterData.is_used_in_os == 1 || reportMasterData.is_used_in_os == true);
            if (hasRevisionBuilt || isUsedInOs) {
                isQtyLocked = true;
            }
        }

        if (isQtyLocked) {
            jQuery('#rt_report_qty').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
        } else {
            jQuery('#rt_report_qty').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
        }

        jQuery('#ulr_id').trigger('change');
        jQuery('#ulr_id').val('').prop('required', false).trigger('change');
        setSelect2Readonly('#ulr_id', true);
        jQuery('#ulr_id').addClass('skip-tab');

        jQuery('#ulr_sequence').val('').prop('readonly', true).prop('required', false).attr('tabindex', '-1');
        jQuery('#ulr_no').val('').prop('readonly', true);
        jQuery('#ulr_year').val('');
        jQuery('.astric_ulr').html('');
        jQuery('.astric_ulr_seq').html('');

        jQuery('#amendment_no, #amendment_date, #amendment_reason').val('').prop('readonly', true).css('pointer-events', 'none').addClass('skip-tab').attr('tabindex', '-1');
        if (jQuery('#amendment_date').data('datepicker')) {
            jQuery('#amendment_date').datepicker('disable');
        }
    }
}

jQuery(document).on('mousedown focus click', '#commonTestReportRtForm .trans-date-picker, #commonTestReportRtForm .date-picker', function (e) {
    if (jQuery(this).prop('readonly') || jQuery(this).prop('disabled') || jQuery(this).css('pointer-events') === 'none') {
        if (jQuery(this).data('datepicker')) {
            jQuery(this).datepicker('hide');
        }
        jQuery(this).blur();
        e.preventDefault();
        e.stopPropagation();
        return false;
    }
});

function handleJobTypeChange() {
    let jobType = jQuery('input[name="job_type_fix"]:checked').val() || 'Non-Welding';
    if (jobType === 'Non-Welding') {
        jQuery('#welding_process, #joint_type, #welder_name, #welder_id, #position, #purpose_of_testing').val('').prop('readonly', true).css('pointer-events', 'none').addClass('skip-tab').attr('tabindex', '-1');
        jQuery('#welding_process_suggestion, #joint_type_suggestion, #welder_name_suggestion, #welder_id_suggestion, #position_suggestion, #purpose_of_testing_suggestion').val('');
        jQuery('#welding_process_list, #joint_type_list, #welder_name_list, #welder_id_list, #position_list, #purpose_of_testing_list').empty();
    } else {
        jQuery('#welding_process, #joint_type, #welder_name, #welder_id, #position, #purpose_of_testing').prop('readonly', false).css('pointer-events', 'auto').removeClass('skip-tab').removeAttr('tabindex');
    }
}

jQuery(document).on('change', 'input[name="job_type_fix"]', function () {
    handleJobTypeChange();
});

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
            url: 'check-ulr_no',
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
            date: date,
            id: id
        };
        if (customSeq !== '') {
            payload.ulr_sequence = customSeq;
        }
        jQuery.ajax({
            type: 'GET',
            url: 'get-latest_url_no',
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

// Enter Key Focus Navigation
jQuery(document).on('keydown', '#commonTestReportRtForm input, #commonTestReportRtForm select, #commonTestReportRtForm textarea, #ReportDetailsForm input, #ReportDetailsForm select, #ReportDetailsForm textarea', function (e) {
    if (e.key === 'Enter') {
        // Exclude textarea, submit/reset/close button, and select2 open search fields
        if (this.tagName === 'TEXTAREA' || this.type === 'submit' || jQuery(this).hasClass('select2-search__field') || jQuery(this).is('button')) {
            return;
        }

        // If focus is on a Select2 dropdown selection container, let select2 handle it
        if (jQuery(this).hasClass('select2-hidden-accessible')) {
            return;
        }

        e.preventDefault();

        // Get list of all visible, enabled, non-readonly elements
        let form = jQuery(this).closest('form');
        let elements = form.find('input, select, textarea, button').filter(':visible:not([disabled])');
        let focusables = [];

        elements.each(function () {
            let $el = jQuery(this);
            if ($el.hasClass('skip-tab')) return;

            if ($el.is('select')) {
                if ($el.hasClass('select2-hidden-accessible')) {
                    let $s2 = $el.next('.select2-container').find('.select2-selection');
                    if ($s2.length && $s2.attr('tabindex') !== '-1') {
                        focusables.push($s2[0]);
                    }
                } else {
                    focusables.push(this);
                }
            } else if ($el.is('input') || $el.is('textarea') || $el.is('button')) {
                if (!$el.prop('readonly') || $el.attr('id') === 'ulr_sequence') {
                    focusables.push(this);
                }
            }
        });

        // Find index of current active element
        let current = document.activeElement;
        let index = focusables.indexOf(current);

        // Check if inside select2 container
        if (index === -1) {
            let $s2Sel = jQuery(current).closest('.select2-selection');
            if ($s2Sel.length) {
                index = focusables.indexOf($s2Sel[0]);
            }
        }

        if (index > -1 && index < focusables.length - 1) {
            let nextEl = focusables[index + 1];
            jQuery(nextEl).focus();
            if (nextEl.tagName === 'INPUT' || nextEl.tagName === 'TEXTAREA') {
                nextEl.select();
            }
        }
    }
});

function parseDateStr(dateStr) {
    if (!dateStr) return null;
    if (dateStr.indexOf('/') !== -1) {
        let parts = dateStr.split('/');
        if (parts.length === 3) {
            return new Date(parts[2], parts[1] - 1, parts[0]);
        }
    }
    if (dateStr.indexOf('-') !== -1) {
        let parts = dateStr.split('-');
        if (parts.length === 3) {
            if (parts[0].length === 4) {
                return new Date(parts[0], parts[1] - 1, parts[2]);
            } else {
                return new Date(parts[2], parts[1] - 1, parts[0]);
            }
        }
    }
    let d = new Date(dateStr);
    return isNaN(d.getTime()) ? null : d;
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
    updateCameraSourceSummaries();

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

jQuery(document).on('change', '#rss_no', function () {
    let sheetId = jQuery(this).val();
    if (!sheetId) return;

    // Prevent auto-fill if we are initializing edit mode (handled by master fetch)
    if (_openForEdit && reportMasterData && reportMasterData.technique_sheet_rt_id == sheetId) {
        return;
    }

    skipLoader = false;
    showLoader();
    jQuery.ajax({
        url: 'get-technique-sheet-details',
        type: 'GET',
        data: { id: sheetId },
        dataType: 'json',
        success: function (res) {
            hideLoader();
            if (res.response_code == 1 && res.sheet) {
                let s = res.sheet;
                jQuery('#rss_reference').val(s.rss_reference || '');
                jQuery('#welding_process').val(s.welding_process || '');
                jQuery('#welder_name').val(s.welder_name || '');
                jQuery('#welder_id').val(s.welder_id || '');
                jQuery('#position').val(s.position || '');
                jQuery('#purpose_of_testing').val(s.purpose_of_testing || '');
                jQuery('#joint_type').val(s.joint_type || '');
                jQuery('#source_used').val(s.source_used || '');
                jQuery('#source_strength').val(s.source_strength || '');
                jQuery('#source_size').val(s.source_size || '');
                jQuery('#xray_kv_ma').val(s.xray_kv_ma || '');
                jQuery('#xray_focal_size').val(s.xray_focal_size || '');
                jQuery('#lead_screen_thick').val(s.lead_screen_thick || '');
                jQuery('#lead_screen_thick_back').val('');
                jQuery('#iqi').val(s.iqi || '');
                jQuery('#film_processing').val(s.film_processing || 'MANUAL');
                jQuery('#test_technique').val(s.test_technique || '');
                jQuery('#test_arrangement').val(s.test_arrangement || '');
                jQuery('#test_class').val(s.test_class || '');
                jQuery('#film_brand').val(s.film_brand || '');
                jQuery('#film_type').val(s.film_type || '');
                jQuery('#procedure_ref_id').val(s.procedure_ref_id || '').trigger('change');
                jQuery('#evaluation_as_per_id').val(s.evaluation_as_per_id || '').trigger('change');
                jQuery('#acceptance_standard_id').val(s.acceptance_standard_id || '').trigger('change');
                jQuery('#customer_procedure_ref').val(s.customer_procedure_ref || '');
            }
        },
        error: function () {
            hideLoader();
        }
    });
});


jQuery(document).on('click keydown', 'input.is-readonly', function (e) {
    if (e.type === 'click' || e.keyCode === 32 || (e.keyCode >= 37 && e.keyCode <= 40)) {
        e.preventDefault();
    }
});

function formatDecimal(val, decimals = 2) {
    if (val === null || val === undefined || String(val).trim() === '' || String(val).toLowerCase() === 'nan' || String(val).toLowerCase() === 'null') {
        return '';
    }
    let parsed = parseFloat(val);
    if (isNaN(parsed)) return val;
    return parsed.toFixed(decimals);
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

            // Mismatch pattern like "L3-A" -> Return Blank ('')
            return '';
        } else {
            return input;
        }
    }
}

setRadioReadonly('input[name="nabl_type_fix"]', true);
setRadioReadonly('input[name="job_type_fix"]', true);
setRadioReadonly('input[name="process_type"]', false);

function suggestCustomerClient(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#customer_client_list',
        url: 'test_report_rt_customer_client-list',
        responseListKey: 'customerClientList'
    });
}
function suggestPartNo(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#part_no_list',
        url: 'test_report_rt_part_no-list',
        responseListKey: 'partNoList'
    });
}
function suggestDrgNo(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#drg_no_list',
        url: 'test_report_rt_drg_no-list',
        responseListKey: 'drgNoList'
    });
}
function suggestWeldingProcess(e, element) {
    if (jQuery(element).prop('readonly') || jQuery(element).prop('disabled') || jQuery('input[name="job_type_fix"]:checked').val() === 'Non-Welding') {
        return;
    }
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#welding_process_list',
        url: 'test_report_rt_welding_process-list',
        responseListKey: 'weldingProcessList'
    });
}
function suggestJointType(e, element) {
    if (jQuery(element).prop('readonly') || jQuery(element).prop('disabled') || jQuery('input[name="job_type_fix"]:checked').val() === 'Non-Welding') {
        return;
    }
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#joint_type_list',
        url: 'test_report_rt_joint_type-list',
        responseListKey: 'jointTypeList'
    });
}
/*
function suggestWelderName(e, element) {
    if (jQuery(element).prop('readonly') || jQuery(element).prop('disabled') || jQuery('input[name="job_type_fix"]:checked').val() === 'Non-Welding') {
        return;
    }
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#welder_name_list',
        url: 'test_report_rt_welder_name-list',
        responseListKey: 'welderNameList'
    });
}
function suggestWelderId(e, element) {
    if (jQuery(element).prop('readonly') || jQuery(element).prop('disabled') || jQuery('input[name="job_type_fix"]:checked').val() === 'Non-Welding') {
        return;
    }
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#welder_id_list',
        url: 'test_report_rt_welder_id-list',
        responseListKey: 'welderIdList'
    });
}
function suggestPosition(e, element) {
    if (jQuery(element).prop('readonly') || jQuery(element).prop('disabled') || jQuery('input[name="job_type_fix"]:checked').val() === 'Non-Welding') {
        return;
    }
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#position_list',
        url: 'test_report_rt_position-list',
        responseListKey: 'positionList'
    });
}
function suggestPurposeOfTesting(e, element) {
    if (jQuery(element).prop('readonly') || jQuery(element).prop('disabled') || jQuery('input[name="job_type_fix"]:checked').val() === 'Non-Welding') {
        return;
    }
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#purpose_of_testing_list',
        url: 'test_report_rt_purpose_of_testing-list',
        responseListKey: 'purposeOfTestingList'
    });
}
*/
function suggestIqi(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#iqi_list',
        url: 'test_report_rt_iqi-list',
        responseListKey: 'iqiList'
    });
}
function suggestFilmProcessing(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#film_processing_list',
        url: 'test_report_rt_film_processing-list',
        responseListKey: 'filmProcessingList'
    });
}
function suggestTestTechnique(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#test_technique_list',
        url: 'test_report_rt_test_technique-list',
        responseListKey: 'testTechniqueList'
    });
}
function suggestProductCode(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#product_code_list',
        url: 'test_report_rt_product_code-list',
        responseListKey: 'productCodeList'
    });
}
function suggestFinding(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#finding_list',
        url: 'test_report_rt_finding-list',
        responseListKey: 'findingList'
    });
}
jQuery(document).on('click keydown', '#finding_list .list-group-item', function (e) {
    if (e.type === 'click' || e.which === 13 || e.which === 32) {
        let abbreviation = jQuery(this).attr('data-abbreviation') || '';
        jQuery('#finding').data('abbreviation', abbreviation);
    }
});
function suggestLeadScreenThick(e, element) {
    let id = jQuery(element).attr('id');
    let listId = id + '_list';
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#' + listId,
        url: 'test_report_rt_lead_screen_thick-list',
        responseListKey: 'leadScreenThickList',
        extraData: {
            parentId: id,
            listId: listId
        }
    });
}
function suggestTestCarriedOutAt(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#test_carried_out_at_list',
        url: 'test_report_rt_test_carried_out_at-list',
        responseListKey: 'testCarriedOutAtList'
    });
}

jQuery(document).ready(function () {
    loadAllTechniqueSheets();
});

function loadAllTechniqueSheets() {
    let rssDropdown = jQuery('#rss_no');
    jQuery.ajax({
        url: 'get-technique-sheets-for-customer',
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            if (res.response_code == 1) {
                rssDropdown.empty().append('<option value="">Select RSS No</option>');
                res.sheets.forEach(sheet => {
                    rssDropdown.append(`<option value="${sheet.technique_sheet_rt_id}">${sheet.technique_sheet_rt_no}</option>`);
                });
                rssDropdown.trigger('change.select2');
            }
        }
    });
}

function updateFilmSizeDropdown() {
    let film_size_fix = jQuery('input[name="film_size_unit_fix"]:checked').val() || 'inch';
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
jQuery('#FilmBrandModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('detail_film_brand_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});
jQuery('#FilmTypeModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('detail_film_type_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});
// jQuery('#IqiDesignationModal').on('hide.bs.modal', function (e) {
//     this.dataset.customHideFocus = 'true';
//     const input = document.getElementById('detail_iqi_designation_id');
//     if (input) {
//         setTimeout(() => {
//             input.focus();
//         }, 500);
//     }
// });
// jQuery('#IqiSensitivityModal').on('hide.bs.modal', function (e) {
//     this.dataset.customHideFocus = 'true';
//     const input = document.getElementById('detail_iqi_sensitivity_id');
//     if (input) {
//         setTimeout(() => {
//             input.focus();
//         }, 500);
//     }
// });
jQuery('#FilmModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('detail_film_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});
// jQuery('#FindingModal').on('hide.bs.modal', function (e) {
//     this.dataset.customHideFocus = 'true';
//     const input = document.getElementById('detail_finding_id');
//     if (input) {
//         setTimeout(() => {
//             input.focus();
//         }, 500);
//     }
// });
// jQuery('#FindingLevelModal').on('hide.bs.modal', function (e) {
//     this.dataset.customHideFocus = 'true';
//     const input = document.getElementById('detail_finding_level_id');
//     if (input) {
//         setTimeout(() => {
//             input.focus();
//         }, 500);
//     }
// });
jQuery('#FilmResultModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('detail_film_result_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});

// Dynamic Finding Level Required toggle based on selected Finding's Req. Finding Level flag
/*jQuery(document).on('change', '#detail_finding_id', function () {
   
    let reqLevel = jQuery(this).find('option:selected').attr('data-required_finding_level');
    let findingLevelSelect = jQuery('#detail_finding_level_id');
    let astric = jQuery('#detail_finding_level_astric');

    if (reqLevel === 'Yes') {
        findingLevelSelect.prop('required', true);
        astric.removeClass('d-none');
    } else {
        findingLevelSelect.prop('required', false);
        astric.addClass('d-none');
    }
    
});*/

var copyReportsData = [];

// Copy button click to open modal
jQuery(document).on('click', '#copy_report_btn', function () {
    let typeOfJobId = jQuery('#type_of_job_id').val();

    jQuery.ajax({
        url: 'get-test_report_rt_copy_list',
        type: 'GET',
        data: { type_of_job_id: typeOfJobId },
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                copyReportsData = data.reports;
                fillCopyReportModalTable();
                jQuery('#CopyReportRtModal').modal('show');
            }
        }
    });
});

function fillCopyReportModalTable() {
    let $table = jQuery("#CopyReportRtModal").find('#CopyReportRtTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().clear().destroy();
    }

    let tbody = jQuery('#CopyReportRtTable tbody');
    tbody.empty();

    if (copyReportsData.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="14" class="text-center">No Reports Available</td>
            </tr>
        `);
        return;
    }

    copyReportsData.forEach(item => {
        let rowHtml = `<tr>
            <td>
                <input type="radio" name="copy_report_id" value="${item.id}" class="form-check-input select-copy-report-radio">
            </td>
            <td>${item.test_report_no || ''}</td>
            <td>${item.revision_number || ''}</td>
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
            <td>${item.rt_no || ''}</td>
            <td>${item.product_code || ''}</td>
        </tr>`;
        tbody.append(rowHtml);
    });

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
    });
    if (typeof fixDataTableColumnsUntilAdjusted === 'function') {
        fixDataTableColumnsUntilAdjusted($new);
    }
}

// Adjust DataTable columns when copy modal is fully visible
jQuery('#CopyReportRtModal').on('shown.bs.modal', function () {
    let $table = jQuery('#CopyReportRtTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().columns.adjust().draw();
        if (typeof initColumnSearch === 'function') {
            initColumnSearch('#CopyReportRtTable', [0], 'common_search');
        }
    }
});

// Submit copy selection from modal
jQuery(document).on('click', '#submitCopyReportRtBtn', function () {
    let selectedRadio = jQuery('input[name="copy_report_id"]:checked');
    if (selectedRadio.length === 0) {
        toastr.error('Please select Report');
        return;
    }

    let reportId = selectedRadio.val();
    jQuery('#CopyReportRtModal').modal('hide');

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
                jQuery('#type_of_job_id').focus();
                return;
            }

            performCopyReport(reportId, copyMaster, copyDetails);
        });
    });
});

function performCopyReport(reportId, copyMaster, copyDetails) {
    skipLoader = false;
    showLoader();
    jQuery.ajax({
        url: 'get-test_report_rt_details_for_copy',
        type: 'GET',
        data: { id: reportId },
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.report_data != null) {
                var d = data.report_data;

                if (copyMaster) {
                    // Populate Technical Master fields (excluding unique/identifying ones and user-specified exclusions)
                    jQuery('#welding_process').val(d.welding_process || '');
                    jQuery('#joint_type').val(d.joint_type || '');
                    jQuery('#welder_name').val(d.welder_name || '');
                    jQuery('#welder_id').val(d.welder_id || '');
                    jQuery('#position').val(d.position || '');
                    jQuery('#purpose_of_testing').val(d.purpose_of_testing || '');
                    // jQuery('#source_used').val(d.source_used || '');
                    // jQuery('#source_strength').val(d.source_strength || '');
                    // jQuery('#source_size').val(d.source_size || '');
                    // jQuery('#xray_kv_ma').val(d.xray_kv_ma || '');
                    // jQuery('#xray_focal_size').val(d.xray_focal_size || '');
                    jQuery('#lead_screen_thick').val(d.lead_screen_thick || '');
                    jQuery('#lead_screen_thick_back').val(d.lead_screen_thick_back || '');
                    jQuery('#iqi').val(d.iqi || '');
                    jQuery('#film_processing').val(d.film_processing || 'MANUAL');
                    jQuery('#test_technique').val(d.test_technique || '');
                    jQuery('#test_arrangement').val(d.test_arrangement || '');
                    jQuery('#test_class').val(d.test_class || '');
                    jQuery('#film_brand').val(d.film_brand || '');
                    jQuery('#film_type').val(d.film_type || '');
                    jQuery('#procedure_ref_id').val(zeroToEmpty(d.procedure_ref_id)).trigger('change');
                    jQuery('#evaluation_as_per_id').val(zeroToEmpty(d.evaluation_as_per_id)).trigger('change');
                    jQuery('#acceptance_standard_id').val(zeroToEmpty(d.acceptance_standard_id)).trigger('change');
                    jQuery('#customer_procedure_ref').val(d.customer_procedure_ref || '');

                    // Copy second column fields
                    jQuery('#heat_no').val(d.heat_no || '');
                    jQuery('#product_code').val(d.product_code || '');
                    jQuery('#dc_no').val(d.dc_no || '');
                    jQuery('#dc_date').val(d.dc_date || '');
                    jQuery('#po_no').val(d.po_no || '');
                    jQuery('#po_date').val(d.po_date || '');
                    jQuery('#date_of_receipt').val(d.date_of_receipt || '');
                    jQuery('#date_of_testing').val('');
                    jQuery('#date_of_testing_value').val('');
                    jQuery('#test_carried_out_at').val(d.test_carried_out_at || '');
                    jQuery('#stage_of_test').val(d.stage_of_test || '');
                    jQuery('#area_of_coverage_id').val(zeroToEmpty(d.area_of_coverage_id)).trigger('change');
                    jQuery('#reviewed_by_authority_person_id').val(zeroToEmpty(d.reviewed_by_authority_person_id)).trigger('change');
                    jQuery('#authorized_by_authority_person_id').val(zeroToEmpty(d.authorized_by_authority_person_id)).trigger('change');
                    if (d.film_size_unit_fix === 'cm') {
                        jQuery('#rep_film_size_cm').prop('checked', true);
                    } else {
                        jQuery('#rep_film_size_inch').prop('checked', true);
                    }

                    if (d.sfd_unit_fix === 'inch') {
                        jQuery('#rep_sfd_inch').prop('checked', true);
                    } else {
                        jQuery('#rep_sfd_mm').prop('checked', true);
                    }
                }

                if (copyDetails) {
                    // Populate Details Grid
                    test_report_rt_details_data = [];
                    if (data.report_details_data && data.report_details_data.length > 0) {
                        let nablType = jQuery('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
                        let detailsToCopy = data.report_details_data;
                        if (nablType === 'NABL') {
                            detailsToCopy = [data.report_details_data[data.report_details_data.length - 1]];
                        }
                        detailsToCopy.forEach((row, idx) => {
                            test_report_rt_details_data.push({
                                is_copy: true,
                                test_report_rt_details_id: '',
                                main_rt_detail_id: null,
                                record_type_id: 1,
                                sr_no: nablType === 'NABL' ? 1 : parseFloat(row.sr_no || (idx + 1)),
                                identification: row.identification,
                                location: row.location,
                                source_id_fix: row.source_id_fix,
                                detail_film_brand_id: row.film_brand_id,
                                film_brand_name: row.film_brand,
                                detail_film_type_id: row.film_type_id,
                                film_type_name: row.film_type,
                                thickness: row.thickness,
                                sfd: row.sfd,
                                optical_density: row.optical_density,
                                // detail_iqi_designation_id: row.iqi_designation_id,
                                iqi_designation: row.iqi_designation || row.iqi_designation_name || row.iqi || '',
                                iqi_designation_name: row.iqi_designation || row.iqi_designation_name || row.iqi || '',
                                // detail_iqi_sensitivity_id: row.iqi_sensitivity_id,
                                iqi_sensitivity: row.iqi_sensitivity || row.iqi_sensitivity_name || row.sensitivity || '',
                                iqi_sensitivity_name: row.iqi_sensitivity || row.iqi_sensitivity_name || row.sensitivity || '',
                                detail_film_id: row.film_id,
                                film_size_inch: row.film_size_inch,
                                film_size_cm: row.film_size_cm,
                                no_of_film_fix: row.no_of_film_fix,
                                exposure_time: row.exposure_time,
                                // detail_finding_id: row.finding_id,
                                finding: row.finding || row.finding_name || '',
                                finding_name: row.finding || row.finding_name || '',
                                finding_abbreviation: row.abbreviation,
                                // detail_finding_level_id: '',
                                // finding_level_name: '',
                                detail_film_result_id: row.result_id,
                                film_result_name: row.film_result,
                                ug: row.ug,
                                include_in_measurement_sheet: row.include_in_measurement_sheet,
                                film_qty: row.film_qty,
                                sq_in: parseFloat(row.sq_in || 0),
                                sq_cm: parseFloat(row.sq_cm || 0),
                                total_sq_in: parseFloat(row.total_sq_in || 0),
                                total_sq_cm: parseFloat(row.total_sq_cm || 0),
                                mode: 'Add'
                            });
                        });
                    }
                    resequenceReportDetails(false);
                    fillReportDetailTable();
                }
            }
        },
        complete: function () {
            hideLoader();
            setTimeout(function () {
                jQuery('#type_of_job_id').focus();
            }, 200);
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

jQuery(document).on('change', '#amendment_no, #amendment_date', function () {
    validateAmendmentFields();
});

// Trigger on load/edit
jQuery('#TestReportRtModal').on('shown.bs.modal', function () {
    validateAmendmentFields();
    handleJobTypeChange();
    updateCopyReportButtonState();
    let formId = jQuery('#id').val();
    if (!_openForEdit && (!formId || formId == '0')) {
        loadRTCamerasDropdown('', '', '');
    }
});

function getTRLNRData() {
    jQuery.ajax({
        url: "get-test_report_rt_lnr_data",
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.lnr_data != null) {
                var lnr = data.lnr_data;
                jQuery('#test_carried_out_at').val(lnr.test_carried_out_at ?? "");
                jQuery('#lead_screen_thick').val(lnr.lead_screen_thick ?? "");
                jQuery('#lead_screen_thick_back').val(lnr.lead_screen_thick_back ?? "");
                dbOpticalDensity = lnr.optical_density ?? "";
                jQuery('#detail_optical_density').val(dbOpticalDensity);
                jQuery('#note').val(lnr.note ?? "");
            }
        },
        error: function () {
            console.log('Error fetching Test Report RT LNR data');
        }
    });

}

var copyRssData = [];

// Open RSS modal
jQuery(document).on('click', '#rss_report_btn', function () {
    showLoader();
    jQuery.ajax({
        url: 'get-technique-sheets-for-customer',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            hideLoader();
            if (data.response_code == 1) {
                copyRssData = data.sheets || [];
                fillCopyRssModalTable();
                jQuery('#CopyRssRtModal').modal('show');
            }
        },
        error: function () {
            hideLoader();
        }
    });
});

function fillCopyRssModalTable() {
    let $table = jQuery("#CopyRssRtModal").find('#CopyRssRtTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().clear().destroy();
    }

    let tbody = jQuery('#CopyRssRtTable tbody');
    tbody.empty();

    if (copyRssData.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="9" class="text-center">No RSS Available</td>
            </tr>
        `);
        return;
    }

    copyRssData.forEach(item => {
        let rowHtml = `<tr>
            <td>
                <input type="radio" name="copy_rss_id" value="${item.technique_sheet_rt_id}" class="form-check-input select-copy-rss-radio">
            </td>
            <td>${item.technique_sheet_rt_no || ''}</td>
            <td>${item.technique_sheet_rt_date || ''}</td>
            <td>${item.customer || ''}</td>
            <td>${item.type_of_job || ''}</td>
            <td>${item.job_description || ''}</td>
            <td>${item.part_no || ''}</td>
            <td>${item.drg_no || ''}</td>
            <td>${item.area_of_coverage || ''}</td>
        </tr>`;
        tbody.append(rowHtml);
    });

    var $new = $table.DataTable({
        paging: true,
        searching: true,
        "oLanguage": {
            "sSearch": "Search :"
        },
        dom: 'lrtip',
        "sScrollX": true,
        "sScrollX": "100%",
        "sScrollXInner": "100%",
        "bScrollCollapse": true,
    });
    if (typeof fixDataTableColumnsUntilAdjusted === 'function') {
        fixDataTableColumnsUntilAdjusted($new);
    }
}

jQuery('#CopyRssRtModal').on('shown.bs.modal', function () {
    let $table = jQuery('#CopyRssRtTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().columns.adjust().draw();
        if (typeof initColumnSearch === 'function') {
            initColumnSearch('#CopyRssRtTable', [0], 'common_search');
        }
    }
});

// Submit RSS Selection
jQuery(document).on('click', '#submitCopyRssRtBtn', function () {
    let selectedRadio = jQuery('input[name="copy_rss_id"]:checked');
    if (selectedRadio.length === 0) {
        toastr.error('Please select RSS No.');
        return;
    }

    let rssId = selectedRadio.val();
    let selectedRssItem = copyRssData.find(x => x.technique_sheet_rt_id == rssId);

    jQuery('#CopyRssRtModal').modal('hide');

    showLoader();
    jQuery.ajax({
        url: 'get-technique-sheet-details',
        type: 'GET',
        data: { id: rssId },
        dataType: 'json',
        success: function (res) {
            hideLoader();
            if (res.response_code == 1 && res.sheet) {
                let s = res.sheet;
                jQuery('#technique_sheet_rt_id').val(s.technique_sheet_rt_id || '');
                jQuery('#rss_no').val(s.technique_sheet_rt_id || '');
                jQuery('#rss_reference').val(s.technique_sheet_rt_no || (selectedRssItem ? selectedRssItem.technique_sheet_rt_no : ''));

                // Set ONLY Film Size Unit and SFD Unit radios in master
                if (s.film_size_fix) {
                    jQuery(`input[name="film_size_unit_fix"][value="${s.film_size_fix}"]`).prop('checked', true).trigger('change');
                }
                if (s.sfd_unit_fix) {
                    jQuery(`input[name="sfd_unit_fix"][value="${s.sfd_unit_fix}"]`).prop('checked', true).trigger('change');
                }

                // Fill Details Table
                if (res.details && res.details.length > 0) {
                    test_report_rt_details_data = [];
                    res.details.forEach(d => {
                        test_report_rt_details_data.push({
                            sr_no: d.sr_no,
                            identification: d.identification || '',
                            location: d.location || '',
                            source_id_fix: d.source_id_fix || '',
                            film_brand_id: d.film_brand_id || '',
                            detail_film_brand_id: d.film_brand_id || '',
                            film_brand: d.film_brand || '',
                            film_type_id: d.film_type_id || '',
                            detail_film_type_id: d.film_type_id || '',
                            film_type: d.film_type || '',
                            thickness: d.thickness || '',
                            sfd: d.sfd || '',
                            // iqi_designation_id: d.iqi_designation_id || '',
                            // detail_iqi_designation_id: d.iqi_designation_id || '',
                            iqi_designation: d.iqi_designation || d.iqi || '',
                            iqi_designation_name: d.iqi_designation || d.iqi || '',
                            iqi: d.iqi || '',
                            // iqi_sensitivity_id: d.iqi_sensitivity_id || '',
                            // detail_iqi_sensitivity_id: d.iqi_sensitivity_id || '',
                            iqi_sensitivity: d.iqi_sensitivity || d.sensitivity || '',
                            iqi_sensitivity_name: d.iqi_sensitivity || d.sensitivity || '',
                            sensitivity: d.sensitivity || '',
                            optical_density: d.optical_density || '',
                            film_id: d.film_id || '',
                            detail_film_id: d.film_id || '',
                            film_size_inch: d.film_size_inch || '',
                            film_size_cm: d.film_size_cm || '',
                            no_of_film_fix: d.no_of_film_fix || 'Single',
                            film_qty: d.film_qty || 1,
                            sq_in: parseFloat(d.sq_in || 0),
                            sq_cm: parseFloat(d.sq_cm || 0),
                            total_sq_in: parseFloat(d.total_sq_in || 0),
                            total_sq_cm: parseFloat(d.total_sq_cm || 0),
                            include_in_measurement_sheet: 'Yes',
                            record_type_id: 1,
                            mode: 'Add',
                            is_copy: true
                        });
                    });
                    fillReportDetailTable();
                    if (typeof calculateReportTotals === 'function') {
                        calculateReportTotals();
                    }
                }
            }
        },
        error: function () {
            hideLoader();
            toastr.error('Failed to fetch RSS details.');
        }
    });
});




