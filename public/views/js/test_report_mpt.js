var test_report_mpt_equipment_details_data = [];
var test_report_mpt_material_details_data = [];
var test_report_mpt_details_data = [];
var reportMasterData = null;
var pendingInwardData = [];
var selectedInwardDetailsId = null;
var _openForEdit = false;
var dbUlrId = null;
var dbUlrSequence = null;
var dbUlrNo = null;
var dbUlrYear = null;
var _preventUlrFetch = false;

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

function parseDateStr(str) {
    if (!str) return null;
    var parts = str.split('/');
    if (parts.length === 3) {
        return new Date(parts[2], parts[1] - 1, parts[0]);
    }
    return null;
}

function validateDates() {
    var reportDate = parseDateStr($('#test_report_date').val());
    var testDate = parseDateStr($('#date_of_testing').val());
    var receiptDate = parseDateStr($('#date_of_receipt').val());

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

$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Date change validations
    $(document).on('change', '#date_of_testing', function () {
        var val = $(this).val();
        if (val) {
            $('#date_of_testing_value').val(val);
        }
        var testDate = parseDateStr(val);
        var receiptDate = parseDateStr($('#date_of_receipt').val());
        if (testDate && receiptDate && testDate < receiptDate) {
            toastr.error("Date Of Testing Must Be Greater Than Date Of Receipt.");
        }
        var reportDate = parseDateStr($('#test_report_date').val());
        if (reportDate && testDate && reportDate < testDate) {
            toastr.error("Date Of Testing Must Be Less Than Report Date.");
        }
    });

    $(document).on('change', '#test_report_date', function () {
        var reportDate = parseDateStr($(this).val());
        var receiptDate = parseDateStr($('#date_of_receipt').val());
        if (reportDate && receiptDate && reportDate < receiptDate) {
            toastr.error("Report Date Must Be Greater Than Date Of Receipt.");
        }
        var testDate = parseDateStr($('#date_of_testing').val());
        if (reportDate && testDate && reportDate < testDate) {
            toastr.error("Date Of Testing Must Be Less Than Report Date.");
        }
    });

    // When modal opens via BS modal show event or Add button
    $('#TestReportMptModal').on('show.bs.modal', function () {
        if (!$('#id').val()) {
            setAddMode();
            loadLatestReportNumber();
        }
        fillCustomerDropdown();
    });

    $('#TestReportMptModal').on('shown.bs.modal', function () {
        setTimeout(function () {
            let seqInput = $('#test_report_sequence');
            if (seqInput.prop('readonly')) {
                let dateInput = $('#test_report_date');
                dateInput.focus().select();
                setTimeout(function () {
                    dateInput.datepicker('hide');
                }, 50);
            } else {
                seqInput.focus().select();
            }
        }, 150);
    });

    $('#add_mpt_report_btn').on('click', function () {
        reportMasterData = null;
        selectedInwardDetailsId = null;
        setAddMode();
        loadLatestReportNumber();
    });

    // Pending Inward Modal Event Handlers
    $('#PendingInwardForMptModal').on('show.bs.modal', function () {
        fillPendingMptModalTable();
    });

    // Requirement 2: Focus to Customer Client after Pending Modal closes
    $('#PendingInwardForMptModal').on('hidden.bs.modal', function () {
        setTimeout(function () {
            $('#customer_client').focus();
        }, 200);
    });

    // Focus to Job Description after Copy Modal closes
    $('#CopyReportMptModal').on('hidden.bs.modal', function () {
        setTimeout(function () {
            $('#job_desc_id').focus();
        }, 200);
    });

    $(document).on('click', '#pending_btn', function (e) {
        var customerId = $('#customer_id').val();
        if (!customerId) {
            e.preventDefault();
            e.stopPropagation();
            toastr.error('Select Customer.');
            return false;
        }
    });

    // Customer Change Handler -> Enable Pending Inward button & fetch pending data
    $(document).on('change', '#customer_id', function () {
        var customerId = $(this).val();

        if (_openForEdit || $('#submitbtn').text().trim() === 'Update') {
            $('#pending_btn').prop('disabled', true);
            return;
        }

        if (!customerId) {
            $('#pending_btn').prop('disabled', true);
            pendingInwardData = [];
            return;
        }

        $('#pending_btn').prop('disabled', false);

        $.ajax({
            url: 'get-pending-customer-mpt-data',
            type: 'GET',
            data: { customer_id: customerId, report_id: $('#id').val() },
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    pendingInwardData = data.pending_data;

                    var activeSelectedId = selectedInwardDetailsId || $('#material_inward_details_id').val();
                    if (activeSelectedId) {
                        var exists = pendingInwardData.some(function (item) { return item.material_inward_details_id == activeSelectedId; });
                        if (!exists && reportMasterData && reportMasterData.material_inward_details_id == activeSelectedId) {
                            pendingInwardData.push({
                                from_type_id_fix: reportMasterData.from_type_id_fix || (reportMasterData.observation_sheet_details_id ? 2 : 1),
                                material_inward_details_id: activeSelectedId,
                                material_inward_no: reportMasterData.material_inward_no,
                                material_inward_date: reportMasterData.date_of_receipt ? reportMasterData.date_of_receipt.split('/').reverse().join('-') : null,
                                nabl_type_fix: reportMasterData.nabl_type_fix,
                                test_at_fix: reportMasterData.test_carried_out_at,
                                job_type_fix: reportMasterData.job_type_fix,
                                dc_no: reportMasterData.dc_no,
                                dc_date: reportMasterData.dc_date ? reportMasterData.dc_date.split('/').reverse().join('-') : null,
                                po_no: reportMasterData.po_no,
                                po_date: reportMasterData.po_date ? reportMasterData.po_date.split('/').reverse().join('-') : null,
                                type_of_job: $('#type_of_job_id option:selected').text().trim(),
                                job_description: $('#job_desc_id option:selected').text().trim(),
                                part_no: reportMasterData.part_no,
                                drg_no: reportMasterData.drg_no,
                                material: $('#material_id option:selected').text().trim(),
                                heat_no: reportMasterData.heat_no,
                                product_code: reportMasterData.product_code,
                                thickness: reportMasterData.thickness,
                                observation_sheet_details_id: reportMasterData.observation_sheet_details_id || '',
                                inward_qty: reportMasterData.pend_qty,
                                // reported_qty: 0
                            });
                        }
                    }
                    fillPendingMptModalTable();
                }
            },
            complete: function () {
                $('#submitbtn').prop('disabled', false);
            }
        });
    });

    // Requirement 1: Pending Inward Radio Selection maintained
    function fillPendingMptModalTable() {
        var $table = $("#PendingInwardForMptModal").find('#PendingForMptTable');
        if ($.fn.DataTable.isDataTable($table)) {
            $table.DataTable().clear().destroy();
        }
        var tbody = $('#PendingForMptTable tbody');
        tbody.empty();

        if (pendingInwardData && pendingInwardData.length > 0) {
            var currentVal = $('#material_inward_details_id').val() || selectedInwardDetailsId;
            var currentObsVal = $('#observation_sheet_details_id').val();
            var currentFromType = $('#from_type_id_fix').val();
            pendingInwardData.forEach(function (row) {
                var pendingQty = parseFloat(row.pending_qty || 0);
                var isSameMid = (row.material_inward_details_id == currentVal);
                var isSameObs = (!currentObsVal && (!row.observation_sheet_details_id || row.observation_sheet_details_id == 0)) || (currentObsVal && row.observation_sheet_details_id == currentObsVal);
                var isSameFromType = (!currentFromType) || (row.from_type_id_fix == currentFromType);
                var checked = (isSameMid && isSameObs && isSameFromType) ? 'checked' : '';
                var rowHtml = `<tr>
                    <td class="text-center">
                        <input class="form-check-input radio_item_select" type="radio" name="selectPendingInward" value="${row.material_inward_details_id}" data-obs-id="${row.observation_sheet_details_id || ''}" data-from-type="${row.from_type_id_fix || 1}" ${checked}>
                    </td>
                    <td>${row.material_inward_no || ''}</td>
                    <td>${row.material_inward_date || ''}</td>
                    <td>${row.nabl_type_fix || ''}</td>
                    <td>${row.test_at_fix || ''}</td>
                    <td>${row.job_type_fix || ''}</td>
                    <td>${row.dc_no || ''}</td>
                    <td>${row.dc_date || ''}</td>
                    <td>${row.po_no || ''}</td>
                    <td>${row.po_date || ''}</td>
                    <td>${row.type_of_job || ''}</td>
                    <td>${row.job_description || ''}</td>
                    <td>${row.part_no || ''}</td>
                    <td>${row.drg_no || ''}</td>
                    <td>${row.material || ''}</td>
                    <td>${row.heat_no || ''}</td>
                    <td>${row.product_code || ''}</td>
                    <td>${row.inward_qty || 0}</td>
                    <td>${row.pending_qty || 0}</td>
                </tr>`;
                tbody.append(rowHtml);
            });
        }

        var $new = $table.DataTable({
            paging: true,
            searching: true,
            "oLanguage": {
                "sSearch": "Search :",
                "sEmptyTable": "No Pending Material Inward Available",
                "sZeroRecords": "No Pending Material Inward Available"
            },
            dom: 'lrtip',
            "sScrollX": true,
            "sScrollX": "100%",
            "sScrollXInner": "110%",
            "bScrollCollapse": true,
        });
        $new.on('draw', function () {
            var selMid = window._selectedMaterialInwardDetailsId || $('#material_inward_details_id').val();
            var selObs = window._selectedObsSheetDetailsId || $('#observation_sheet_details_id').val();
            var selFrom = window._selectedFromTypeId || $('#from_type_id_fix').val();
            if (selMid) {
                $('input[name="selectPendingInward"]').each(function () {
                    var mid = $(this).val();
                    var obs = $(this).attr('data-obs-id');
                    var fromT = $(this).attr('data-from-type');
                    if (mid == selMid && (!selObs || obs == selObs) && (!selFrom || fromT == selFrom)) {
                        $(this).prop('checked', true);
                    }
                });
            }
        });

        if (typeof fixDataTableColumnsUntilAdjusted === 'function') {
            fixDataTableColumnsUntilAdjusted($new);
        }
    }

    $('#PendingInwardForMptModal').on('shown.bs.modal', function () {
        var $table = $('#PendingForMptTable');
        if ($.fn.DataTable.isDataTable($table)) {
            $table.DataTable().columns.adjust().draw();
            if (typeof initColumnSearch === 'function') {
                initColumnSearch('#PendingForMptTable', [0], 'common_search');
            }
        }
    });

    // Submit Pending Inward Selection
    $('#submitPendingMptBtn').on('click', function () {
        var selectedRadio = $('input[name="selectPendingInward"]:checked');
        if (selectedRadio.length === 0) {
            toastr.error('Please Select at least one pending item.');
            return;
        }

        var selectedId = selectedRadio.val();
        var obsId = selectedRadio.attr('data-obs-id');
        var fromType = selectedRadio.attr('data-from-type');

        if (obsId === 'null' || obsId === 'undefined' || obsId === '0' || !obsId) {
            obsId = null;
        }
        window._selectedMaterialInwardDetailsId = selectedId;
        window._selectedObsSheetDetailsId = obsId;
        window._selectedFromTypeId = fromType;

        selectedInwardDetailsId = selectedId;
        var row;
        if (selectedId) {
            row = pendingInwardData.find(function (item) {
                var midMatch = (item.material_inward_details_id == selectedId);
                var obsMatch = false;
                if (!obsId && (!item.observation_sheet_details_id || item.observation_sheet_details_id == 0)) {
                    obsMatch = true;
                } else if (obsId && item.observation_sheet_details_id == obsId) {
                    obsMatch = true;
                }
                var fromTypeMatch = (!fromType) || (item.from_type_id_fix == fromType);
                return midMatch && obsMatch && fromTypeMatch;
            });
        }
        if (!row) {
            row = pendingInwardData.find(function (item) { return item.material_inward_details_id == selectedId; });
        }
        if (row) {
            $('#material_inward_details_id').val(row.material_inward_details_id);
            $('#observation_sheet_details_id').val(row.observation_sheet_details_id || '');
            $('#from_type_id_fix').val(row.from_type_id_fix || (row.observation_sheet_details_id ? 2 : 1));
            setRadioReadonly('input[name="nabl_type_fix"]', false);
            setRadioReadonly('input[name="job_type_fix"]', false);
            $('input[name="nabl_type_fix"][value="' + row.nabl_type_fix + '"]').prop('checked', true).trigger('change');
            $('input[name="job_type_fix"][value="' + row.job_type_fix + '"]').prop('checked', true).trigger('change');
            setRadioReadonly('input[name="nabl_type_fix"]', true);
            setRadioReadonly('input[name="job_type_fix"]', true);

            $('#dc_no').val(row.dc_no);
            $('#dc_date').val(row.dc_date);
            $('#po_no').val(row.po_no);
            $('#po_date').val(row.po_date);
            $('#date_of_receipt').val(row.material_inward_date);
            // $('#test_carried_out_at').val(row.test_at_fix);

            $('#type_of_job_id').val(row.type_of_job_id).trigger('change.select2');
            $('#job_desc_id').val(row.job_desc_id).trigger('change.select2');
            $('#part_no').val(row.part_no);
            $('#drg_no').val(row.drg_no);
            $('#material_id').val(row.material_id).trigger('change.select2');
            $('#heat_no').val(row.heat_no);
            $('#product_code').val(row.product_code);
            $('#pend_qty').val(row.pending_qty);

            if (row.thickness) {
                $('#thickness').val(row.thickness);
            }

            if (row.area_of_coverage_id) $('#area_of_coverage_id').val(row.area_of_coverage_id).trigger('change.select2');
            if (row.procedure_ref_id) $('#procedure_ref_id').val(row.procedure_ref_id).trigger('change.select2');
            if (row.acceptance_standard_id) $('#acceptance_standard_id').val(row.acceptance_standard_id).trigger('change.select2');

            if (row.nabl_type_fix === 'NABL') {
                test_report_mpt_details_data = [{
                    test_report_mpt_details_id: 0,
                    sr_no: 1,
                    mpt_test_no: '',
                    heat_no: '',
                    quantity: 1,
                    discontinuity_evaluation: '',
                    result_id: '',
                    result_name: '',
                    mode: 'Insert'
                }];
                renderReportDetailsTable();
            } else {
                var initialQty = parseInt(row.pending_qty || 0);
                test_report_mpt_details_data = [{
                    test_report_mpt_details_id: 0,
                    sr_no: 1,
                    mpt_test_no: '',
                    heat_no: '',
                    quantity: initialQty,
                    discontinuity_evaluation: '',
                    result_id: '',
                    result_name: '',
                    mode: 'Insert'
                }];
                renderReportDetailsTable();
            }
        }

        $('#PendingInwardForMptModal').modal('hide');

        setTimeout(function () {
            setSelect2Readonly('#customer_id', true);
            $('#customer_client').focus();
        }, 150);
    });

    // Copy Report Button Click -> Open Copy Modal
    $('#copy_report_btn').on('click', function () {
        var typeOfJobId = $('#type_of_job_id').val();

        $.ajax({
            url: 'get-test_report_mpt_copy_list',
            type: 'GET',
            data: { type_of_job_id: typeOfJobId },
            dataType: 'json',
            success: function (res) {
                if (res.response_code == 1) {
                    fillCopyMptModalTable(res.reports);
                    $('#CopyReportMptModal').modal('show');
                }
            }
        });
    });

    function fillCopyMptModalTable(reports) {
        var $table = $("#CopyReportMptModal").find('#CopyReportMptTable');
        if ($.fn.DataTable.isDataTable($table)) {
            $table.DataTable().clear().destroy();
        }
        var tbody = $('#CopyReportMptTable tbody');
        tbody.empty();

        if (reports && reports.length > 0) {
            reports.forEach(function (row) {
                var rowHtml = `<tr>
                    <td class="text-center">
                        <input class="form-check-input radio_item_select" type="radio" name="selectCopyReport" value="${row.id}">
                    </td>
                    <td>${row.test_report_no || ''}</td>
                    <td>${row.test_report_date || ''}</td>
                    <td>${row.customer || ''}</td>
                    <td>${row.nabl_type_fix || ''}</td>
                    <td>${row.job_type_fix || ''}</td>
                    <td>${row.type_of_job || ''}</td>
                    <td>${row.job_description || ''}</td>
                    <td>${row.part_no || ''}</td>
                    <td>${row.drg_no || ''}</td>
                    <td>${row.material || ''}</td>
                    <td>${row.heat_no || ''}</td>
                    <td>${row.product_code || ''}</td>
                </tr>`;
                tbody.append(rowHtml);
            });
        }

        var $new = $table.DataTable({
            paging: true,
            searching: true,
            "oLanguage": {
                "sSearch": "Search :",
                "sEmptyTable": "No Reports Available",
                "sZeroRecords": "No Reports Available"
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

    $('#CopyReportMptModal').on('shown.bs.modal', function () {
        var $table = $('#CopyReportMptTable');
        if ($.fn.DataTable.isDataTable($table)) {
            $table.DataTable().columns.adjust().draw();
            if (typeof initColumnSearch === 'function') {
                initColumnSearch('#CopyReportMptTable', [0], 'common_search');
            }
        }
    });

    // Submit Copy Report Selection -> Auto-fill report details
    $('#submitCopyReportMptBtn').on('click', function () {
        var selectedRadio = $('input[name="selectCopyReport"]:checked');
        if (selectedRadio.length === 0) {
            toastr.error('Please select Report.');
            return;
        }

        var reportId = selectedRadio.val();
        $('#CopyReportMptModal').modal('hide');

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
        skipLoader = false;
        showLoader();
        $.ajax({
            url: 'get-test_report_mpt_details_for_copy',
            type: 'GET',
            data: { id: reportId },
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1 && data.report_data != null) {
                    var d = data.report_data;

                    if (copyMaster) {
                        $('#customer_client').val(d.customer_client || '');
                        $('#surface_condition').val(d.surface_condition || '');
                        $('#surface_temp').val(d.surface_temp || '');
                        $('#test_technique').val(d.test_technique || '');
                        $('#type_of_magnetization').val(d.type_of_magnetization || '');
                        $('#type_of_current').val(d.type_of_current || '');
                        $('#prod_pole_spacing').val(d.prod_pole_spacing || '');
                        $('#performance_verification').val(d.performance_verification || '');
                        $('#lighting').val(d.lighting || '');
                        $('#light_intensity').val(d.light_intensity || '');
                        $('#background_light').val(d.background_light || '');
                        $('#uva_light_intensity').val(d.uva_light_intensity || '');
                        $('#yoke_wt_lift_check').val(d.yoke_wt_lift_check || '');
                        $('#sp_note').val(d.sp_note || '');
                        $('#note').val(d.note || '');

                        // Do not copy required fields (customer_id, type_of_job_id, job_desc_id, material_id, date_of_testing, test_carried_out_at, area_of_coverage_id, procedure_ref_id, acceptance_standard_id, tested_by_authority_person_id)
                        // Do not copy thickness, reviewed_by_authority_person_id, authorized_by_authority_person_id per requirements
                    }

                    /*
                    if (copyDetails) {
                        // Report Details (Full Replace)
                        test_report_mpt_details_data = [];
                        if (data.report_details_data && data.report_details_data.length > 0) {
                            var nablType = $('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
                            var detailsToCopy = data.report_details_data;
                            if (nablType === 'NABL') {
                                detailsToCopy = [data.report_details_data[data.report_details_data.length - 1]];
                            }
                            detailsToCopy.forEach(function (row, idx) {
                                test_report_mpt_details_data.push({
                                    test_report_mpt_details_id: '',
                                    sr_no: nablType === 'NABL' ? 1 : parseFloat(row.sr_no || (idx + 1)),
                                    mpt_test_no: row.mpt_test_no || '',
                                    heat_no: row.heat_no || '',
                                    quantity: (nablType === 'NABL') ? 1 : (row.quantity || ''),
                                    discontinuity_evaluation: row.discontinuity_evaluation || '',
                                    result_id: row.result_id || '',
                                    result_name: row.result_name || row.result || '',
                                    mode: 'Insert'
                                });
                            });
                        }
                        resequenceReportDetails(false);
                        renderReportDetailsTable();
                    }
                    */

                }
            },
            complete: function () {
                hideLoader();
            }
        });
    }


    // Open Child Detail Popup Modals
    $(document).on('click', '#addEquipmentRowBtn', function () {
        $('#detail_em_id').find('.temp-option').remove();
        $('#MPTEquipmentDetailsForm')[0].reset();
        $('#detail_em_id').val('').trigger('change.select2');
        $('#eq_form_type').val('add');
        $('#test_report_mpt_equipment_details_id').val(0);
        $('#MPTEquipmentDetailsModal').modal('show');
    });

    $(document).on('click', '#addMaterialRowBtn', function () {
        $('#detail_mm_id').find('.temp-option').remove();
        $('#MPTMaterialDetailsForm')[0].reset();
        $('#detail_mm_id').val('').trigger('change.select2');
        $('#mat_form_type').val('add');
        $('#test_report_mpt_material_details_id').val(0);
        $('#MPTMaterialDetailsModal').modal('show');
    });

    $(document).on('click', '#addDetailRowBtn', function () {
        $('#MPTReportDetailsForm')[0].reset();
        $('#det_form_type').val('add');
        $('#test_report_mpt_details_id').val(0);

        let nablType = $('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
        if (nablType === 'NABL') {
            $('#det_quantity').val(1).prop('readonly', true).attr('tabindex', '-1');
        } else {
            $('#det_quantity').val('').prop('readonly', false).removeAttr('tabindex');
        }

        $('#MPTReportDetailsModal').modal('show');
    });

    // Requirement 5: Focus Navigation on Modal Close
    $('#MPTEquipmentDetailsModal').on('hidden.bs.modal', function () {
        $('#addMaterialRowBtn').focus();
    });
    $('#MPTMaterialDetailsModal').on('hidden.bs.modal', function () {
        $('#addDetailRowBtn').focus();
    });
    $('#MPTReportDetailsModal').on('hidden.bs.modal', function () {
        setTimeout(function () {
            $('#tested_by_authority_person_id').focus();
        }, 200);
    });

    // Auto-fill Equipment Details on Dropdown Change
    $(document).on('change', '#detail_em_id', function () {
        var opt = $(this).find('option:selected');
        $('#eq_make').val(opt.data('make') || '');
        $('#eq_display').val(opt.data('display') || opt.data('em_equipment_name') || '');
        $('#eq_sr_no').val(opt.data('sr_no') || '');
        $('#eq_cal_due_date').val(opt.data('cal_due_date') || '');
    });

    // Auto-fill Material Details on Dropdown Change
    $(document).on('change', '#detail_mm_id', function () {
        var opt = $(this).find('option:selected');
        $('#mat_batch_no').val(opt.data('batch_no') || '');
        $('#mat_make').val(opt.data('make') || '');
        $('#mat_expiry_date').val(opt.data('expiry_date') || '');
    });

    // Requirement 4: Equipment Details Row Submission (Continuous Add & Edit Close)
    $('#submitEquipmentRowBtn').on('click', function () {
        var form = $('#MPTEquipmentDetailsForm')[0];
        if (!form.checkValidity()) {
            $(form).addClass('was-validated');
            return;
        }

        var em_id = $('#detail_em_id').val();
        var opt = $('#detail_em_id option:selected');
        var rawText = opt.text() || '';
        var equipment_name = opt.attr('data-em_equipment_name') || opt.attr('data-display') || $('#eq_display').val() || (rawText.split(' - ')[0] || rawText);
        var make = $('#eq_make').val();
        var display = $('#eq_display').val();
        var em_sr_no = $('#eq_sr_no').val();
        var cal_due_date = $('#eq_cal_due_date').val();
        var form_type = $('#eq_form_type').val();
        var form_index = $('#eq_form_index').val();

        var testingDateVal = $('#date_of_testing').val();
        if (cal_due_date && testingDateVal) {
            var calDue = parseDateStr(cal_due_date);
            var testDate = parseDateStr(testingDateVal);
            if (calDue && testDate && calDue < testDate) {
                toastr.error(' This MPT Equipment is due for Calibration.');
                return;
            }
        }

        var isDuplicate = test_report_mpt_equipment_details_data.some(function (row, idx) {
            if (row.mode === 'Delete') return false;
            if (form_type === 'edit' && idx == form_index) return false;
            return row.em_id == em_id;
        });

        if (isDuplicate) {
            toastr.error("Duplicate Equipment Found.");
            return;
        }

        var rowData = {
            em_id: em_id,
            equipment_name: equipment_name,
            make: make,
            display: display,
            em_sr_no: em_sr_no,
            cal_due_date: cal_due_date,
            mode: form_type === 'edit' ? 'Update' : 'Add'
        };

        if (form_type === 'edit') {
            test_report_mpt_equipment_details_data[form_index] = rowData;
            renderEquipmentTable();
            $('#MPTEquipmentDetailsModal').modal('hide');
        } else {
            test_report_mpt_equipment_details_data.push(rowData);
            renderEquipmentTable();
            form.reset();
            $('#detail_em_id').val('').trigger('change.select2');
            $('#eq_form_type').val('add');
            $(form).removeClass('was-validated');
            setTimeout(function () { $('#detail_em_id').select2('focus'); }, 100);
        }
    });

    // Requirement 4: Material Details Row Submission (Continuous Add & Edit Close)
    $('#submitMaterialRowBtn').on('click', function () {
        var form = $('#MPTMaterialDetailsForm')[0];
        if (!form.checkValidity()) {
            $(form).addClass('was-validated');
            return;
        }

        var mm_id = $('#detail_mm_id').val();
        var opt = $('#detail_mm_id option:selected');
        var material_name = opt.data('material_name') || opt.text();
        var batch_no = $('#mat_batch_no').val();
        var make = $('#mat_make').val();
        var expiry_date = $('#mat_expiry_date').val();
        var form_type = $('#mat_form_type').val();
        var form_index = $('#mat_form_index').val();

        var isDuplicate = test_report_mpt_material_details_data.some(function (row, idx) {
            if (row.mode === 'Delete') return false;
            if (form_type === 'edit' && idx == form_index) return false;
            return row.mm_id == mm_id;
        });

        if (isDuplicate) {
            toastr.error("Duplicate Material Found.");
            return;
        }

        let testDateStr = $('#date_of_testing').val();
        let d1 = parseDateStr(testDateStr);
        let d2 = parseDateStr(expiry_date);
        if (d1 && d2 && d1 > d2) {
            toastr.error('This MPT Material is over to its Expiry Date.');
            return;
        }

        var rowData = {
            mm_id: mm_id,
            material_name: material_name,
            batch_no: batch_no,
            make: make,
            expiry_date: expiry_date,
            mode: form_type === 'edit' ? 'Update' : 'Add'
        };

        if (form_type === 'edit') {
            test_report_mpt_material_details_data[form_index] = rowData;
            renderMaterialTable();
            $('#MPTMaterialDetailsModal').modal('hide');
        } else {
            test_report_mpt_material_details_data.push(rowData);
            renderMaterialTable();
            form.reset();
            $('#detail_mm_id').val('').trigger('change.select2');
            $('#mat_form_type').val('add');
            $(form).removeClass('was-validated');
            setTimeout(function () { $('#detail_mm_id').select2('focus'); }, 100);
        }
    });

    // Requirement 4: Report Details Row Submission (Continuous Add & Edit Close)
    $('#submitDetailsRowBtn').on('click', function () {
        var form = $('#MPTReportDetailsForm')[0];
        if (!form.checkValidity()) {
            $(form).addClass('was-validated');
            return;
        }

        var form_type = $('#det_form_type').val();
        var form_index = $('#det_form_index').val();
        var mpt_test_no = $('#det_mpt_test_no').val();
        var heat_no = $('#det_heat_no').val();
        var quantity = parseInt($('#det_quantity').val()) || 0;
        if (quantity < 1) {
            toastr.error('Enter Quantity greater than 0.');
            return;
        }
        var discontinuity_evaluation = $('#det_discontinuity_evaluation').val();
        var result_id = $('#detail_result_id').val();
        var result_name = $('#detail_result_id option:selected').text();

        // Duplicate MPI Test No. check
        var isDuplicateTestNo = test_report_mpt_details_data.some(function (row, idx) {
            if (row.mode === 'Delete') return false;
            if (form_type === 'edit' && idx == form_index) return false;
            return row.mpt_test_no && mpt_test_no && row.mpt_test_no.trim().toLowerCase() === mpt_test_no.trim().toLowerCase();
        });
        if (isDuplicateTestNo) {
            toastr.error("Duplicate MPI Test No. Found.");
            return;
        }

        var rowData = {
            mpt_test_no: mpt_test_no,
            heat_no: heat_no,
            quantity: quantity,
            discontinuity_evaluation: discontinuity_evaluation,
            result_id: result_id,
            result_name: result_name,
            mode: form_type === 'edit' ? 'Update' : 'Add'
        };

        if (form_type === 'edit') {
            test_report_mpt_details_data[form_index] = rowData;
            renderReportDetailsTable();
            calculateTotalQty();
            $('#MPTReportDetailsModal').modal('hide');
        } else {
            test_report_mpt_details_data.push(rowData);
            renderReportDetailsTable();
            calculateTotalQty();
            form.reset();
            $('#detail_result_id').val('').trigger('change.select2');
            $('#det_form_type').val('add');
            let nablType = $('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
            if (nablType === 'NABL') {
                $('#det_quantity').val(1).prop('readonly', true).attr('tabindex', '-1');
            } else {
                $('#det_quantity').val('').prop('readonly', false).removeAttr('tabindex');
            }
            $(form).removeClass('was-validated');
            $('#det_mpt_test_no').focus();
        }
    });

    $('#add_new').on('click', function () {
        reportMasterData = null;
        selectedInwardDetailsId = null;
        setAddMode();
        loadLatestReportNumber();
        fillCustomerDropdown();
        setTimeout(function () {
            $('#test_report_sequence').focus();
        }, 100);
    });

    $('#submitbtn').on('click', function (e) {
        e.preventDefault();
        var form = $('#commonTestReportMptForm')[0];

        if (!form.checkValidity()) {
            $(form).addClass('was-validated');
            return;
        }

        var matInwardDetailsId = $('#material_inward_details_id').val();
        if (!matInwardDetailsId) {
            toastr.error('Select Pending Inward Data.');
            return;
        }

        // Requirement 3: Validate Date rules before submit
        if (!validateDates()) {
            return;
        }

        var activeEquipment = test_report_mpt_equipment_details_data.filter(function (row) { return row.mode !== 'Delete'; });
        if (activeEquipment.length === 0) {
            toastr.error('Please Add At Least One MPT Equipment Detail');
            return;
        }

        var activeMaterial = test_report_mpt_material_details_data.filter(function (row) { return row.mode !== 'Delete'; });
        if (activeMaterial.length === 0) {
            toastr.error('Please Add At Least One MPT Material Detail');
            return;
        }

        var activeDetails = test_report_mpt_details_data.filter(function (row) { return row.mode !== 'Delete'; });
        if (activeDetails.length === 0) {
            toastr.error('Please Add At Least One Test Report MPT Detail');
            return;
        }

        var invalidDetail = activeDetails.find(function (row) {
            return !row.mpt_test_no || row.mpt_test_no.trim() === '';
        });
        if (invalidDetail) {
            toastr.error('Enter MPT Test No. in Test Report Details.');
            return;
        }

        var invalidQtyDetail = activeDetails.find(function (row) {
            return !row.quantity || parseInt(row.quantity) < 1;
        });
        if (invalidQtyDetail) {
            toastr.error('Enter Quantity greater than 0.');
            return;
        }

        var pendQty = parseInt($('#pend_qty').val()) || 0;
        var totalQty = parseInt($('#total_qty').val()) || 0;
        if (totalQty > pendQty && pendQty > 0) {
            toastr.error('Total Qty Cannot Be Greater Than Pending Qty.');
            return;
        }

        var testingDateVal = $('#date_of_testing').val();
        var testDate = parseDateStr(testingDateVal);
        var isExpired = false;

        if (test_report_mpt_equipment_details_data && test_report_mpt_equipment_details_data.length > 0) {
            test_report_mpt_equipment_details_data.forEach(function (row) {
                if (row.mode === 'Delete') return;
                if (row.cal_due_date && testDate) {
                    var calDueDate = parseDateStr(row.cal_due_date);
                    if (calDueDate && calDueDate < testDate) {
                        isExpired = true;
                    }
                }
            });
        }

        if (isExpired) {
            toastr.error(' This MPT Equipment is due for Calibration.');
            return;
        }

        let d1 = parseDateStr($('#date_of_testing').val());
        if (d1) {
            let activeMats = test_report_mpt_material_details_data.filter(c => c.mode !== 'Delete');
            for (let i = 0; i < activeMats.length; i++) {
                let c = activeMats[i];
                let d2 = parseDateStr(c.expiry_date);
                if (d2 && d1 > d2) {
                    toastr.error('This MPT Material is over to its Expiry Date.');
                    return;
                }
            }
        }

        var id = $('#id').val();
        var url = id ? 'update-test_report_mpt' : 'store-test_report_mpt';

        var btn = $('#submitbtn');

        btn.prop('disabled', true);
        skipLoader = false;
        showLoader();

        var formData = new FormData(form);
        if (id) {
            formData.append('test_report_mpt_id', id);
        }

        formData.append('equipment_details_data', JSON.stringify(test_report_mpt_equipment_details_data));
        formData.append('material_details_data', JSON.stringify(test_report_mpt_material_details_data));
        formData.append('report_details_data', JSON.stringify(test_report_mpt_details_data));

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                btn.prop('disabled', false);
                if (res.response_code == '1') {
                    if (id) {
                        let redirectFn = function () {
                            if (typeof pageRelod !== 'undefined' && pageRelod == 'Yes') {
                                setTimeout(function () {
                                    let seqInput = $('#test_report_sequence');
                                    if (seqInput.length && !seqInput.prop('readonly')) {
                                        seqInput.focus().select();
                                    }
                                }, 50);
                            } else {
                                window.location.reload();
                            }
                        };
                        if (typeof pageRelod !== 'undefined' && pageRelod == 'Yes') {
                            const form = document.getElementById("commonTestReportMptForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                        }
                        if (res.url && res.url !== "") {
                            toastSuccessPreview(res.response_message || "Record Updated.", res.url, redirectFn);
                        } else {
                            toastSuccess(res.response_message || "Record Updated.", redirectFn);
                        }
                    } else {
                        let nextFn = function () {
                            setAddMode();
                            loadLatestReportNumber();
                            fillCustomerDropdown();
                            if (typeof table !== 'undefined') table.draw(false);
                        };
                        if (res.url && res.url !== "") {
                            toastSuccessPreview(res.response_message || "Record Inserted.", res.url, nextFn);
                        } else {
                            toastSuccess(res.response_message || "Record Inserted.", nextFn);
                        }
                    }
                } else {
                    toastr.error(res.response_message || 'An error occurred.');
                }
            },
            error: function (xhr) {
                btn.prop('disabled', false);
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function (key, value) {
                        toastr.error(value[0]);
                    });
                } else {
                    toastr.error('An error occurred while saving.');
                }
            },
            complete: function () {
                hideLoader();
            }
        });
    });

    // Master Edit Handler
    $(document).on('click', '.edit_mpt_report, .edit-item-btn', function () {
        var id = $(this).data('id');
        if (!id && typeof table !== 'undefined') {
            var data = table.row($(this).parents('tr')).data();
            if (data) id = data.id;
        }
        if (id) editMptReport(id);
    });

    $(document).on('click', '#resetbtn', function () {
        if (_openForEdit || $('#submitbtn').text().trim() === 'Update') {
            let reportId = $('#id').val();
            if (reportId) {
                editMptReport(reportId);
            }
        } else {
            setAddMode();
            loadLatestReportNumber();
            fillCustomerDropdown();
            setTimeout(function () {
                let seqInput = $('#test_report_sequence');
                if (seqInput.prop('readonly')) {
                    let dateInput = $('#test_report_date');
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

    $(document).on('click', '#add_new', function () {
        setAddMode();
        loadLatestReportNumber();
        fillCustomerDropdown();
        setTimeout(function () {
            let seqInput = $('#test_report_sequence');
            if (seqInput.prop('readonly')) {
                let dateInput = $('#test_report_date');
                dateInput.focus().select();
                setTimeout(function () {
                    dateInput.datepicker('hide');
                }, 50);
            } else {
                seqInput.focus().select();
            }
        }, 150);
    });
});

function fillCustomerDropdown() {
    var customerDropdown = $('#customer_id');
    customerDropdown.empty().append('<option value="">Select Customer</option>');

    $.ajax({
        url: 'get-pending-customers-for-mpt',
        type: 'GET',
        data: { report_id: $('#id').val() },
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                customerDropdown.empty().append('<option value="">Select Customer</option>');
                data.customers.forEach(function (cust) {
                    customerDropdown.append('<option value="' + cust.id + '">' + cust.customer + '</option>');
                });

                if (reportMasterData && $('#id').val()) {
                    customerDropdown.val(reportMasterData.customer_id).trigger('change.select2');
                    setSelect2Readonly('#customer_id', true);
                } else {
                    customerDropdown.val('').trigger('change.select2');
                    setSelect2Readonly('#customer_id', false);
                }
            }
        },
        complete: function () {
            $('#submitbtn').prop('disabled', false);
        }
    });
}

function loadLatestReportNumber() {
    $.ajax({
        url: 'get-latest-test_report_mpt_number',
        type: 'GET',
        success: function (res) {
            if (res) {
                $('#test_report_no').val(res.latest_no);
                $('#test_report_sequence').val(res.number);
            }
        }
    });
}

function setAddMode() {
    _openForEdit = false;
    dbUlrId = null;
    dbUlrSequence = null;
    dbUlrNo = null;
    dbUlrYear = null;
    $('#detail_em_id').find('.temp-option').remove();
    $('#detail_mm_id').find('.temp-option').remove();
    $('#submitbtn').prop('disabled', false);
    var lastNote = $('#note').val();
    $('#commonTestReportMptForm')[0].reset();
    $('#note').val(lastNote);
    $('#commonTestReportMptForm').removeClass('was-validated');
    $('#commonTestReportMptForm .is-invalid, #commonTestReportMptForm .is-valid').removeClass('is-invalid is-valid');
    $('#id').val('');
    $('#material_inward_details_id').val('');
    $('#observation_sheet_details_id').val('');
    $('#from_type_id_fix').val('');
    reportMasterData = null;
    selectedInwardDetailsId = null;
    $('#TestReportMptModalLabel').text('Test Report (MPT)');
    $('#submitbtn').text('Submit');
    $('#preview_btn, #add_new').hide();

    setSelect2Readonly('#customer_id', false);
    $('#customer_id').val('').trigger('change.select2');
    $('#pending_btn').prop('disabled', true);
    $('#copy_report_btn').prop('disabled', false);

    var dateVal = (typeof currentDate !== 'undefined' && currentDate) ? currentDate : (typeof moment !== 'undefined' ? moment().format('DD/MM/YYYY') : new Date().toLocaleDateString('en-GB'));
    $('#test_report_date').val(dateVal).trigger('change');

    $('#customer_client').val('');
    $('#part_no').val('');
    $('#drg_no').val('');
    $('#heat_no').val('');
    $('#product_code').val('');
    $('#dc_no').val('');
    $('#dc_date').val('');
    $('#po_no').val('');
    $('#po_date').val('');
    $('#date_of_receipt').val('');
    $('#date_of_testing').val('');
    $('#date_of_testing_value').val('');
    $('#test_carried_out_at').val('');
    $('#amendment_no, #amendment_date, #amendment_reason').val('').prop('readonly', true).css('pointer-events', 'none').addClass('skip-tab').attr('tabindex', '-1');
    $('#stage_of_test').val('');
    $('#surface_condition').val('');
    $('#surface_temp').val('');
    $('#thickness').val('');
    $('#test_technique').val('');
    $('#type_of_magnetization').val('');
    $('#type_of_current').val('');
    $('#prod_pole_spacing').val('');
    $('#performance_verification').val('');
    $('#lighting').val('');
    $('#light_intensity').val('');
    $('#background_light').val('');
    $('#uva_light_intensity').val('');
    $('#yoke_wt_lift_check').val('');
    $('#sp_note').val('');
    $('#total_qty').val('');
    $('#pend_qty').val('');

    $('#type_of_job_id').val('').trigger('change.select2');
    $('#job_desc_id').val('').trigger('change.select2');
    $('#material_id').val('').trigger('change.select2');
    $('#area_of_coverage_id').val('').trigger('change.select2');
    $('#procedure_ref_id').val('').trigger('change.select2');
    $('#acceptance_standard_id').val('').trigger('change.select2');

    $('#defectogram_image_doc').val('');
    $('#defectogram_image').val('');
    $('#defectogram_image_prev').attr('href', '#').addClass('hide');
    $('#defectogram_image_remove').addClass('hide').removeClass('i-block');

    test_report_mpt_equipment_details_data = [];
    test_report_mpt_material_details_data = [];
    test_report_mpt_details_data = [];

    // Fetch last report details to carry forward Equipment & Material details
    $.ajax({
        url: 'get-last-mpt-details',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                test_report_mpt_equipment_details_data = (data.equipment_details_data || []).map((row, idx) => ({
                    ...row,
                    sr_no: idx + 1,
                    mode: 'Insert'
                }));
                test_report_mpt_material_details_data = (data.material_details_data || []).map((row, idx) => ({
                    ...row,
                    sr_no: idx + 1,
                    mode: 'Insert'
                }));
                renderEquipmentTable();
                renderMaterialTable();
            }
        }
    });

    dbUlrId = null;
    dbUlrSequence = null;
    dbUlrNo = null;
    dbUlrYear = null;
    _preventUlrFetch = false;

    $('#ulr_id').val('').trigger('change.select2');
    $('#ulr_sequence').val('');
    $('#ulr_no').val('');
    $('#ulr_year').val('');
    $('#tested_by_authority_person_id').val('').trigger('change.select2');
    $('#reviewed_by_authority_person_id').val('').trigger('change.select2');
    $('#authorized_by_authority_person_id').val('').trigger('change.select2');
    handleNablTypeChange();

    renderEquipmentTable();
    renderMaterialTable();
    renderReportDetailsTable();
    validateAmendmentFields();
    getTRLNRData();
}

function setEditMode() {
    $('#TestReportMptModalLabel').text('Test Report (MPT)');
    $('#submitbtn').text('Update');
    $('#preview_btn, #add_new').show();

    setTimeout(function () {
        setSelect2Readonly('#customer_id', true);
    }, 150);
    setRadioReadonly('input[name="nabl_type_fix"]', true);
    setRadioReadonly('input[name="job_type_fix"]', true);
    $('#pending_btn').prop('disabled', true);
    $('#copy_report_btn').prop('disabled', true);
}

function resequenceEquipmentDetails() {
    let activeRows = test_report_mpt_equipment_details_data.filter(r => r.mode !== 'Delete');
    let deleteRows = test_report_mpt_equipment_details_data.filter(r => r.mode === 'Delete');
    activeRows.forEach((row, index) => {
        row.sr_no = index + 1;
    });
    test_report_mpt_equipment_details_data = [...activeRows, ...deleteRows];
}

function resequenceMaterialDetails() {
    let activeRows = test_report_mpt_material_details_data.filter(r => r.mode !== 'Delete');
    let deleteRows = test_report_mpt_material_details_data.filter(r => r.mode === 'Delete');
    activeRows.forEach((row, index) => {
        row.sr_no = index + 1;
    });
    test_report_mpt_material_details_data = [...activeRows, ...deleteRows];
}

function resequenceReportDetails() {
    let activeRows = test_report_mpt_details_data.filter(r => r.mode !== 'Delete');
    let deleteRows = test_report_mpt_details_data.filter(r => r.mode === 'Delete');
    activeRows.forEach((row, index) => {
        row.sr_no = index + 1;
    });
    test_report_mpt_details_data = [...activeRows, ...deleteRows];
}

function renderEquipmentTable() {
    resequenceEquipmentDetails();
    var tbody = $('#EquipmentDetailTable tbody');
    tbody.empty();

    var activeCount = 0;
    $.each(test_report_mpt_equipment_details_data, function (i, row) {
        if (row.mode === 'Delete') return true;
        activeCount++;

        var actionDropdown = DetailsActionDropdown('editEquipmentDetails', 'removeEquipmentDetails');

        var tr = `<tr>
            <td>
                ${actionDropdown}
                <input type="hidden" name="eq_form_indx" value="${i}"/>
            </td>
            <td>${row.equipment_name || ''}</td>
            <td>${row.em_sr_no || ''}</td>
            <td>${row.make || ''}</td>
            <td>${row.cal_due_date || ''}</td>
        </tr>`;
        tbody.append(tr);
    });

    if (activeCount === 0) {
        tbody.html('<tr><td colspan="6" class="text-center">No Equipment Details Added</td></tr>');
    }
}

function editEquipmentDetails(th) {
    var index = $(th).closest('tr').find('input[name="eq_form_indx"]').val();
    var row = test_report_mpt_equipment_details_data[index];

    $('#eq_form_type').val('edit');
    $('#eq_form_index').val(index);

    if (row.em_id && $('#detail_em_id option[value="' + row.em_id + '"]').length === 0) {
        var parts = [row.equipment_name, row.em_sr_no || row.sr_no, row.make].filter(Boolean);
        var displayName = parts.length > 0 ? parts.join(' - ') : (row.equipment_name || 'Equipment');
        var opt = new Option(displayName, row.em_id, true, true);
        $(opt).addClass('temp-option')
            .attr('data-make', row.make || '')
            .attr('data-display', row.equipment_name || row.display || '')
            .attr('data-sr_no', row.em_sr_no || row.sr_no || '')
            .attr('data-cal_due_date', row.cal_due_date || '');
        $('#detail_em_id').append(opt);
    }
    $('#detail_em_id').val(row.em_id).trigger('change.select2');
    $('#eq_make').val(row.make || '');
    $('#eq_display').val(row.display || '');
    $('#eq_sr_no').val(row.em_sr_no || row.sr_no || '');
    $('#eq_cal_due_date').val(row.cal_due_date || '');
    $('#MPTEquipmentDetailsModal').modal('show');
}

function removeEquipmentDetails(th) {
    var index = $(th).closest('tr').find('input[name="eq_form_indx"]').val();
    var eqName = test_report_mpt_equipment_details_data[index] ? (test_report_mpt_equipment_details_data[index].equipment_name || 'Equipment') : 'this item';
    toastDetailDelete("Do you want to delete this record?", function () {
        if (test_report_mpt_equipment_details_data[index].test_report_mpt_equipment_details_id) {
            test_report_mpt_equipment_details_data[index].mode = 'Delete';
        } else {
            test_report_mpt_equipment_details_data.splice(index, 1);
        }
        renderEquipmentTable();
    });
}

function renderMaterialTable() {
    resequenceMaterialDetails();
    var tbody = $('#MaterialDetailTable tbody');
    tbody.empty();

    var activeCount = 0;
    $.each(test_report_mpt_material_details_data, function (i, row) {
        if (row.mode === 'Delete') return true;
        activeCount++;

        var actionDropdown = DetailsActionDropdown('editMaterialDetails', 'removeMaterialDetails');

        var tr = `<tr>
            <td>
                ${actionDropdown}
                <input type="hidden" name="mat_form_indx" value="${i}"/>
            </td>
            <td>${row.material_name || ''}</td>
            <td>${row.batch_no || ''}</td>
            <td>${row.make || ''}</td>
            <td>${row.expiry_date || ''}</td>
        </tr>`;
        tbody.append(tr);
    });

    if (activeCount === 0) {
        tbody.html('<tr><td colspan="5" class="text-center">No Material Details Added</td></tr>');
    }
}

function editMaterialDetails(th) {
    var index = $(th).closest('tr').find('input[name="mat_form_indx"]').val();
    var row = test_report_mpt_material_details_data[index];

    $('#mat_form_type').val('edit');
    $('#mat_form_index').val(index);

    if (row.mm_id && $('#detail_mm_id option[value="' + row.mm_id + '"]').length === 0) {
        var displayName = row.material_name ? (row.material_name + (row.batch_no ? ' - ' + row.batch_no : '')) : (row.mm_material || 'Material');
        var opt = new Option(displayName, row.mm_id, true, true);
        $(opt).addClass('temp-option')
            .attr('data-material_name', row.mm_material || row.material_name || '')
            .attr('data-make', row.make || '')
            .attr('data-batch_no', row.batch_no || '')
            .attr('data-expiry_date', row.expiry_date || '');
        $('#detail_mm_id').append(opt);
    }
    $('#detail_mm_id').val(row.mm_id).trigger('change.select2');
    $('#mat_batch_no').val(row.batch_no);
    $('#mat_make').val(row.make);
    $('#mat_expiry_date').val(row.expiry_date);
    $('#MPTMaterialDetailsModal').modal('show');
}

function removeMaterialDetails(th) {
    var index = $(th).closest('tr').find('input[name="mat_form_indx"]').val();
    toastDetailDelete("Do you want to delete this record?", function () {
        if (test_report_mpt_material_details_data[index].test_report_mpt_material_details_id) {
            test_report_mpt_material_details_data[index].mode = 'Delete';
        } else {
            test_report_mpt_material_details_data.splice(index, 1);
        }
        renderMaterialTable();
    });
}

function updateQtySummary(rows) {
    if (!rows) {
        rows = typeof test_report_mpt_details_data !== 'undefined' ? test_report_mpt_details_data.filter(function (row) { return row.mode !== 'Delete'; }) : [];
    }
    var nablType = $('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
    if (nablType === 'NABL') {
        $('#total_qty').val(1);
    } else {
        var sum = rows.reduce(function (acc, row) { return acc + parseInt(row.quantity || 0); }, 0);
        var pendQty = parseInt($('#pend_qty').val()) || 0;
        if (sum > pendQty && pendQty > 0) {
            toastr.error('Total Qty Cannot Be Greater Than Pending Qty.');
        }
        $('#total_qty').val(sum > 0 ? sum : '');
    }
}

function renderReportDetailsTable() {
    resequenceReportDetails();
    var tbody = $('#ReportDetailTable tbody');
    tbody.empty();

    var activeCount = 0;
    $.each(test_report_mpt_details_data, function (i, row) {
        if (row.mode === 'Delete') return true;
        activeCount++;

        var actionDropdown = DetailsActionDropdown('editReportDetails', 'removeReportDetails');

        var tr = `<tr>
            <td>
                ${actionDropdown}
                <input type="hidden" name="det_form_indx" value="${i}"/>
            </td>
            <td>${row.sr_no || activeCount}</td>
            <td>${row.mpt_test_no || ''}</td>
            <td>${row.heat_no || ''}</td>
            <td>${row.quantity || ''}</td>
            <td>${row.discontinuity_evaluation || ''}</td>
            <td>${row.result_name || ''}</td>
        </tr>`;
        tbody.append(tr);
    });

    if (activeCount === 0) {
        tbody.html('<tr><td colspan="7" class="text-center">No Test Report Details Added</td></tr>');
    }
    updateQtySummary();
}

function editReportDetails(th) {
    var index = $(th).closest('tr').find('input[name="det_form_indx"]').val();
    var row = test_report_mpt_details_data[index];

    $('#det_form_type').val('edit');
    $('#det_form_index').val(index);
    $('#det_mpt_test_no').val(row.mpt_test_no);
    $('#det_heat_no').val(row.heat_no);

    let nablType = $('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
    if (nablType === 'NABL') {
        $('#det_quantity').val(1).prop('readonly', true).attr('tabindex', '-1');
    } else {
        $('#det_quantity').val(row.quantity || '').prop('readonly', false).removeAttr('tabindex');
    }

    $('#det_discontinuity_evaluation').val(row.discontinuity_evaluation);
    $('#detail_result_id').val(row.result_id).trigger('change.select2');
    $('#MPTReportDetailsModal').modal('show');
}

function removeReportDetails(th) {
    var index = $(th).closest('tr').find('input[name="det_form_indx"]').val();
    toastDetailDelete("Do you want to delete this record?", function () {
        if (test_report_mpt_details_data[index].test_report_mpt_details_id) {
            test_report_mpt_details_data[index].mode = 'Delete';
        } else {
            test_report_mpt_details_data.splice(index, 1);
        }
        renderReportDetailsTable();
        calculateTotalQty();
    });
}

function calculateTotalQty() {
    let nablType = $('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
    if (nablType === 'NABL') {
        $('#total_qty').val(1);
    } else {
        var total = 0;
        $.each(test_report_mpt_details_data, function (i, row) {
            if (row.mode !== 'Delete') {
                total += parseInt(row.quantity) || 0;
            }
        });
        var pendQty = parseInt($('#pend_qty').val()) || 0;
        if (total > pendQty && pendQty > 0) {
            toastr.error('Total Qty Cannot Be Greater Than Pending Qty.');
        }
        $('#total_qty').val(total);
    }
}

function handleNablTypeChange() {
    let nablType = $('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
    if (nablType === 'NABL') {
        $('#total_qty').val(1);
        $('#addDetailRowBtn').prop('disabled', true).show();

        setSelect2Readonly('#ulr_id', false);
        $('#ulr_id').removeClass('skip-tab').prop('required', true).trigger('change');

        $('#ulr_sequence').prop('readonly', false).prop('required', true).removeAttr('tabindex');
        $('#ulr_no').prop('readonly', true);
        $('.astric_ulr').html('<sup class="astric">*</sup>');
        $('.astric_ulr_seq').html('<sup class="astric">*</sup>');

        let $ulr = $('#ulr_id');
        let $options = $ulr.find('option[value!=""]');

        if ($options.length === 1) {
            $ulr.val($options.first().val()).trigger('change');
            setSelect2Readonly('#ulr_id', true);
            $('#ulr_id').addClass('skip-tab');
        } else {
            setSelect2Readonly('#ulr_id', false);
            $('#ulr_id').removeClass('skip-tab');
        }



        $('#amendment_no, #amendment_date, #amendment_reason').prop('readonly', false).css('pointer-events', 'auto').removeClass('skip-tab').removeAttr('tabindex');
        if ($('#amendment_date').data('datepicker')) {
            $('#amendment_date').datepicker('enable');
        }

    } else {
        calculateTotalQty();
        $('#addDetailRowBtn').prop('disabled', false).show();

        $('#ulr_id').val('').prop('required', false).trigger('change');
        setSelect2Readonly('#ulr_id', true);
        $('#ulr_id').addClass('skip-tab');

        $('#ulr_sequence').val('').prop('readonly', true).prop('required', false).attr('tabindex', '-1');
        $('#ulr_no').val('').prop('readonly', true);
        $('#ulr_year').val('');
        $('.astric_ulr').html('');
        $('.astric_ulr_seq').html('');

        $('#amendment_no, #amendment_date, #amendment_reason').val('').prop('readonly', true).css('pointer-events', 'none').addClass('skip-tab').attr('tabindex', '-1').removeClass('is-invalid');
        if ($('#amendment_date').data('datepicker')) {
            $('#amendment_date').datepicker('disable');
        }
    }
}

$(document).on('change', 'input[name="nabl_type_fix"]', function () {
    handleNablTypeChange();
});

$(document).on('change', '#ulr_id, #test_report_date', function () {
    let nablType = $('input[name="nabl_type_fix"]:checked').val() || 'Non NABL';
    if (nablType === 'NABL' && !_openForEdit && !_preventUlrFetch) {
        let ulrId = $('#ulr_id').val();
        let date = $('#test_report_date').val();

        let newYear = null;
        if (date && date.includes('/')) {
            let parts = date.split('/');
            if (parts.length === 3) {
                newYear = parts[2];
            }
        }

        let reportId = $('#id').val();
        if (reportId && dbUlrId && ulrId == dbUlrId && newYear == dbUlrYear) {
            _preventUlrFetch = true;
            $('#ulr_sequence').val(dbUlrSequence);
            $('#ulr_no').val(dbUlrNo);
            $('#ulr_year').val(dbUlrYear);
            _preventUlrFetch = false;
        } else {
            fetchLatestUlrNo();
        }
    }
});

$('#ulr_sequence').on('change', function () {
    let seq = $(this).val();
    if (seq && !_preventUlrFetch) {
        checkUlrSequenceDuplication(seq);
    }
});

function checkUlrSequenceDuplication(seq) {
    let ulrId = $('#ulr_id').val();
    let date = $('#test_report_date').val();
    let id = $('#id').val() || '';

    if (ulrId && date) {
        $.ajax({
            type: 'GET',
            url: 'check-mpt-ulr_no',
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
                    $('#ulr_sequence').val('').trigger('change');
                    const input = document.getElementById('ulr_sequence');
                    input?.focus();
                }
            },
            error: function () {
                toastr.error('Error checking ULR sequence.');
                $('#ulr_sequence').val('').trigger('change');
                const input = document.getElementById('ulr_sequence');
                input?.focus();
            }
        });
    }
}

function fetchLatestUlrNo(customSeq = '') {
    let ulrId = $('#ulr_id').val();
    let date = $('#test_report_date').val();
    let id = $('#id').val() || '';

    if (ulrId && date) {
        let payload = {
            ulr_id: ulrId,
            test_report_date: date,
            id: id
        };
        if (customSeq !== '') {
            payload.ulr_sequence = customSeq;
        }
        $.ajax({
            type: 'GET',
            url: 'get-latest_mpt_url_no',
            data: payload,
            success: function (data) {
                if (data.response_code == 1) {
                    $('#ulr_no').val(data.number);
                    $('#ulr_sequence').val(data.ulr_sequence);
                    if (date.includes('/')) {
                        let parts = date.split('/');
                        if (parts.length === 3) {
                            $('#ulr_year').val(parts[2]);
                        }
                    }
                } else {
                    toastr.error(data.response_message || 'Error fetching ULR number.');
                    $('#ulr_no').val('');
                    $('#ulr_sequence').val('');
                    $('#ulr_year').val('');
                }
            },
            error: function () {
                toastr.error('Error fetching ULR number.');
                $('#ulr_no').val('');
                $('#ulr_sequence').val('');
                $('#ulr_year').val('');
            }
        });
    } else {
        $('#ulr_no').val('');
        $('#ulr_sequence').val('');
        $('#ulr_year').val('');
    }
}

function editMptReport(id) {
    // Disable submit button until loading is complete
    $('#submitbtn').prop('disabled', true);

    skipLoader = false;
    showLoader();
    $.ajax({
        url: 'edit-test_report_mpt',
        type: 'GET',
        data: { id: id },
        success: function (res) {
            if (res.response_code == '1') {
                _openForEdit = true;
                setEditMode();

                var m = res.master;
                reportMasterData = m;
                selectedInwardDetailsId = m.material_inward_details_id;

                if (typeof checkFileRoute !== 'undefined' && m.pdf_name) {
                    let previewUrl = checkFileRoute + "?id=" + btoa(m.test_report_mpt_id.toString()) + "&name=" + m.pdf_name + "&type=test_report_mpt";
                    $('#preview_btn').attr('href', previewUrl).show();
                } else {
                    $('#preview_btn').show();
                }

                $('#id').val(m.test_report_mpt_id);
                $('#material_inward_details_id').val(m.material_inward_details_id);
                $('#observation_sheet_details_id').val(m.observation_sheet_details_id || '');
                $('#from_type_id_fix').val(m.from_type_id_fix || (m.observation_sheet_details_id ? 2 : 1));
                $('#test_report_sequence').val(m.test_report_sequence);
                $('#test_report_no').val(m.test_report_no);
                $('#test_report_date').val(m.test_report_date);

                fillCustomerDropdown();

                setRadioReadonly('input[name="nabl_type_fix"]', false);
                setRadioReadonly('input[name="job_type_fix"]', false);
                if (m.nabl_type_fix == 'NABL') {
                    $('#nabl_nabl').prop('checked', true);
                } else {
                    $('#nabl_non_nabl').prop('checked', true);
                }
                if (m.job_type_fix == 'Welding') {
                    $('#job_welding').prop('checked', true);
                } else {
                    $('#job_casting').prop('checked', true);
                }
                setRadioReadonly('input[name="nabl_type_fix"]', true);
                setRadioReadonly('input[name="job_type_fix"]', true);
                $('#customer_client').val(m.customer_client);
                $('#type_of_job_id').val(m.type_of_job_id).trigger('change.select2');
                $('#job_desc_id').val(m.job_desc_id).trigger('change.select2');
                $('#part_no').val(m.part_no);
                $('#drg_no').val(m.drg_no);
                $('#material_id').val(m.material_id).trigger('change.select2');
                $('#heat_no').val(m.heat_no);
                $('#product_code').val(m.product_code);
                $('#dc_no').val(m.dc_no);
                $('#dc_date').val(m.dc_date);
                $('#po_no').val(m.po_no);
                $('#po_date').val(m.po_date);
                $('#date_of_receipt').val(m.date_of_receipt);
                $('#date_of_testing').val(m.date_of_testing);
                $('#date_of_testing_value').val(m.date_of_testing_value || m.date_of_testing);
                $('#test_carried_out_at').val(m.test_carried_out_at);
                $('#amendment_no').val(m.amendment_no || '');
                $('#amendment_date').val(m.amendment_date || '');
                $('#amendment_reason').val(m.amendment_reason || '');
                $('#stage_of_test').val(m.stage_of_test);
                $('#area_of_coverage_id').val(m.area_of_coverage_id).trigger('change.select2');
                $('#surface_condition').val(m.surface_condition);
                $('#surface_temp').val(m.surface_temp);
                $('#thickness').val(m.thickness);
                $('#test_technique').val(m.test_technique);
                $('#type_of_magnetization').val(m.type_of_magnetization);
                $('#type_of_current').val(m.type_of_current);
                $('#prod_pole_spacing').val(m.prod_pole_spacing);
                $('#performance_verification').val(m.performance_verification);
                $('#lighting').val(m.lighting);
                $('#light_intensity').val(m.light_intensity);
                $('#background_light').val(m.background_light);
                $('#uva_light_intensity').val(m.uva_light_intensity);
                $('#yoke_wt_lift_check').val(m.yoke_wt_lift_check);
                $('#procedure_ref_id').val(m.procedure_ref_id).trigger('change.select2');
                $('#acceptance_standard_id').val(m.acceptance_standard_id).trigger('change.select2');
                dbUlrId = m.ulr_id;
                dbUlrSequence = m.ulr_sequence;
                dbUlrNo = m.ulr_no;
                dbUlrYear = m.ulr_year;

                _preventUlrFetch = true;
                var ulrVal = m.ulr_id || '';
                if (ulrVal && $('#ulr_id option[value="' + ulrVal + '"]').length === 0) {
                    $('#ulr_id').append(new Option(m.ulr_name || '', ulrVal, true, true));
                }
                $('#ulr_id').val(ulrVal).trigger('change.select2');
                $('#ulr_sequence').val(m.ulr_sequence);
                $('#ulr_no').val(m.ulr_no);
                $('#ulr_year').val(m.ulr_year);
                _preventUlrFetch = false;

                handleNablTypeChange();
                $('#tested_by_authority_person_id').val(zeroToEmpty(m.tested_by_authority_person_id)).trigger('change.select2');
                $('#reviewed_by_authority_person_id').val(zeroToEmpty(m.reviewed_by_authority_person_id)).trigger('change.select2');
                $('#authorized_by_authority_person_id').val(zeroToEmpty(m.authorized_by_authority_person_id)).trigger('change.select2');
                $('#sp_note').val(m.sp_note);
                $('#note').val(m.note || '');
                $('#total_qty').val(m.total_qty);
                $('#pend_qty').val(m.pend_qty ? m.pend_qty : '');

                if (m.defectogram_image) {
                    let fullPath = m.defectogram_image;
                    let fileName = fullPath.split('/').pop();
                    $('#defectogram_image_doc').val(fullPath);
                    $('#defectogram_image_prev').attr('href', 'storage/' + fullPath).text('View').removeClass('hide');
                    $('#defectogram_image_remove').removeClass('hide').addClass('i-block');

                    let fileInput = $('#defectogram_image');
                    let newFile = new DataTransfer();
                    newFile.items.add(new File([""], fileName));
                    fileInput[0].files = newFile.files;
                } else {
                    $('#defectogram_image_doc').val('');
                    $('#defectogram_image').val('');
                    $('#defectogram_image_prev').attr('href', '#').addClass('hide');
                    $('#defectogram_image_remove').addClass('hide').removeClass('i-block');
                }

                test_report_mpt_equipment_details_data = (res.equipment || []).map(function (row) {
                    var eqName = row.equipment_name || row.em_equipment_name || row.equipment || '';
                    var srNo = row.em_sr_no || row.em_serial_no || '';
                    var make = row.make || row.em_make || '';
                    return {
                        test_report_mpt_equipment_details_id: row.test_report_mpt_equipment_details_id || 0,
                        em_id: row.em_id || '',
                        equipment_name: eqName,
                        make: make,
                        display: row.display || row.em_display || eqName,
                        em_sr_no: srNo,
                        cal_due_date: row.cal_due_date || '',
                        mode: ''
                    };
                });

                test_report_mpt_material_details_data = (res.materials || []).map(function (row) {
                    return {
                        test_report_mpt_material_details_id: row.test_report_mpt_material_details_id || 0,
                        mm_id: row.mm_id || '',
                        material_name: row.mm_material || row.material_name || row.material || '',
                        batch_no: row.batch_no || '',
                        make: row.make || row.mm_make || '',
                        expiry_date: row.expiry_date || '',
                        mode: ''
                    };
                });

                test_report_mpt_details_data = (res.details || []).map(function (row) {
                    return {
                        test_report_mpt_details_id: row.test_report_mpt_details_id || 0,
                        mpt_test_no: row.mpt_test_no || '',
                        heat_no: row.heat_no || '',
                        quantity: row.quantity || 1,
                        discontinuity_evaluation: row.discontinuity_evaluation || '',
                        result_id: row.result_id || '',
                        result_name: row.result_name || row.result || '',
                        mode: ''
                    };
                });

                renderEquipmentTable();
                renderMaterialTable();
                renderReportDetailsTable();

                _openForEdit = true;
                validateAmendmentFields();
                $('#TestReportMptModal').modal('show');
                setTimeout(function () {
                    _openForEdit = false;
                }, 150);
                setTimeout(function () {
                    let seqInput = $('#test_report_sequence');
                    if (seqInput.prop('readonly')) {
                        let dateInput = $('#test_report_date');
                        dateInput.focus().select();
                        setTimeout(function () {
                            dateInput.datepicker('hide');
                        }, 50);
                    } else {
                        seqInput.focus().select();
                    }
                }, 200);
            } else {
                toastr.error(res.response_message);
            }
        },
        error: function () {
            toastr.error('Failed to load report data.');
            $('#submitbtn').prop('disabled', false);
        },
        complete: function () {
            hideLoader();
        }
    });
}

// Requirement 6: Suggestions autocomplete handlers
function suggestCustomerClient(e, element) { commonSuggestionAjax({ inputElement: element, listSelector: '#customer_client_list', url: 'test_report_mpt_customer_client-list', responseListKey: 'customerClientList' }); }
function suggestPartNo(e, element) { commonSuggestionAjax({ inputElement: element, listSelector: '#part_no_list', url: 'test_report_mpt_part_no-list', responseListKey: 'partNoList' }); }
function suggestDrgNo(e, element) { commonSuggestionAjax({ inputElement: element, listSelector: '#drg_no_list', url: 'test_report_mpt_drg_no-list', responseListKey: 'drgNoList' }); }
function suggestTestCarriedOutAt(e, element) { commonSuggestionAjax({ inputElement: element, listSelector: '#test_carried_out_at_list', url: 'test_report_mpt_test_carried_out_at-list', responseListKey: 'testCarriedOutAtList' }); }
function suggestStageOfTest(e, element) { commonSuggestionAjax({ inputElement: element, listSelector: '#stage_of_test_list', url: 'test_report_mpt_stage_of_test-list', responseListKey: 'stageOfTestList' }); }
function suggestSurfaceCondition(e, element) { commonSuggestionAjax({ inputElement: element, listSelector: '#surface_condition_list', url: 'test_report_mpt_surface_condition-list', responseListKey: 'surfaceConditionList' }); }
function suggestSurfaceTemp(e, element) { commonSuggestionAjax({ inputElement: element, listSelector: '#surface_temp_list', url: 'test_report_mpt_surface_temp-list', responseListKey: 'surfaceTempList' }); }
function suggestThickness(e, element) { commonSuggestionAjax({ inputElement: element, listSelector: '#thickness_list', url: 'test_report_mpt_thickness-list', responseListKey: 'thicknessList' }); }
function suggestTestTechnique(e, element) { commonSuggestionAjax({ inputElement: element, listSelector: '#test_technique_list', url: 'test_report_mpt_test_technique-list', responseListKey: 'testTechniqueList' }); }
function suggestTypeOfMagnetization(e, element) { commonSuggestionAjax({ inputElement: element, listSelector: '#type_of_magnetization_list', url: 'test_report_mpt_type_of_magnetization-list', responseListKey: 'typeOfMagnetizationList' }); }
function suggestTypeOfCurrent(e, element) { commonSuggestionAjax({ inputElement: element, listSelector: '#type_of_current_list', url: 'test_report_mpt_type_of_current-list', responseListKey: 'typeOfCurrentList' }); }
function suggestProdPoleSpacing(e, element) { commonSuggestionAjax({ inputElement: element, listSelector: '#prod_pole_spacing_list', url: 'test_report_mpt_prod_pole_spacing-list', responseListKey: 'prodPoleSpacingList' }); }
function suggestPerformanceVerification(e, element) { commonSuggestionAjax({ inputElement: element, listSelector: '#performance_verification_list', url: 'test_report_mpt_performance_verification-list', responseListKey: 'performanceVerificationList' }); }
function suggestLighting(e, element) { commonSuggestionAjax({ inputElement: element, listSelector: '#lighting_list', url: 'test_report_mpt_lighting-list', responseListKey: 'lightingList' }); }
function suggestLightIntensity(e, element) { commonSuggestionAjax({ inputElement: element, listSelector: '#light_intensity_list', url: 'test_report_mpt_light_intensity-list', responseListKey: 'lightIntensityList' }); }
function suggestBackgroundLight(e, element) { commonSuggestionAjax({ inputElement: element, listSelector: '#background_light_list', url: 'test_report_mpt_background_light-list', responseListKey: 'backgroundLightList' }); }
function suggestUvaLightIntensity(e, element) { commonSuggestionAjax({ inputElement: element, listSelector: '#uva_light_intensity_list', url: 'test_report_mpt_uva_light_intensity-list', responseListKey: 'uvaLightIntensityList' }); }
function suggestYokeWtLiftCheck(e, element) { commonSuggestionAjax({ inputElement: element, listSelector: '#yoke_wt_lift_check_list', url: 'test_report_mpt_yoke_wt_lift_check-list', responseListKey: 'yokeWtLiftCheckList' }); }
function suggestDiscontinuityEvaluation(e, element) { commonSuggestionAjax({ inputElement: element, listSelector: '#det_discontinuity_evaluation_list', url: 'test_report_mpt_discontinuity_evaluation-list', responseListKey: 'discontinuityEvaluationList' }); }
function suggestProductCode(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#product_code_list',
        url: 'test_report_mpt_product_code-list',
        responseListKey: 'productCodeList'
    });
}

// Defectogram Sketch file upload validation & handlers
function validateImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i;
    return allowedExtensions.exec(filePath) ? true : false;
}

$(document).on('change', '#defectogram_image', function (e) {
    var form_data = new FormData();
    var target = e.target;
    var files = target.files;
    var totalfiles = files.length;
    var oldImg = $('#defectogram_image_doc').val();

    if (totalfiles > 0) {
        var notValid = 0;
        for (var index = 0; index < totalfiles; index++) {
            if (validateImage(files[index].name) == true) {
                form_data.append("docs[]", files[index]);
            } else {
                notValid = 1;
                toastr.error("Only (jpeg,jpg,png,gif) files are allowed.");
                $('#defectogram_image_doc').val('');
                $('#defectogram_image').val('');
                $('#defectogram_image_prev').attr('href', '#').addClass('hide');
                $('#defectogram_image_remove').removeClass('i-block').addClass('hide');
                return false;
            }
        }

        if (notValid == 0) {
            $('#submitbtn').prop('disabled', true);
            $('#defectogram_image').addClass('file-loader');
            $.ajax({
                url: 'upload-docs',
                type: 'POST',
                data: form_data,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    $('#submitbtn').prop('disabled', false);
                    $('#defectogram_image').removeClass('file-loader');
                    if (data.response_code == 1) {
                        if (oldImg != "" && oldImg.includes('temp_media/')) {
                            if (typeof removeMedia === 'function') removeMedia(oldImg);
                        }

                        $('#defectogram_image_doc').val(data.files);
                        $('#defectogram_image_prev').attr('href', data.files_url).text('View').removeClass('hide');
                        $('#defectogram_image_remove').removeClass('hide').addClass('i-block');
                    } else {
                        toastr.error(data.response_message);
                    }
                },
                error: function () {
                    $('#submitbtn').prop('disabled', false);
                    $('#defectogram_image').removeClass('file-loader');
                    toastr.error('Something went wrong with file upload!');
                }
            });
        }
    } else {
        $('#defectogram_image_doc').val('');
        $('#defectogram_image').val('');
        $('#defectogram_image_prev').attr('href', '#').addClass('hide');
        $('#defectogram_image_remove').addClass('hide').removeClass('i-block');
    }
});

function removeFileTestReportMpt(e) {
    if (e) e.preventDefault();
    if (typeof toastDetailDelete === 'function') {
        toastDetailDelete('Do you want to delete this record?', function () {
            var oldImg = $('#defectogram_image_doc').val();
            if (oldImg && oldImg.includes('temp_media/')) {
                if (typeof removeMedia === 'function') removeMedia(oldImg);
            }
            $('#defectogram_image_doc').val('');
            $('#defectogram_image').val('');
            $('#defectogram_image_prev').attr('href', '#').text('View').addClass('hide');
            $('#defectogram_image_remove').addClass('hide').removeClass('i-block');
        });
    } else {
        var oldImg = $('#defectogram_image_doc').val();
        if (oldImg && oldImg.includes('temp_media/')) {
            if (typeof removeMedia === 'function') removeMedia(oldImg);
        }
        $('#defectogram_image_doc').val('');
        $('#defectogram_image').val('');
        $('#defectogram_image_prev').attr('href', '#').text('View').addClass('hide');
        $('#defectogram_image_remove').addClass('hide').removeClass('i-block');
    }
}

function validateAmendmentFields() {
    var amendmentNo = $('#amendment_no').val() ? $('#amendment_no').val().trim() : '';
    var amendmentDate = $('#amendment_date').val() ? $('#amendment_date').val().trim() : '';

    if (amendmentNo !== '') {
        $('#amendment_date').prop('required', true);
    } else {
        $('#amendment_date').prop('required', false).removeClass('is-invalid');
    }

    if (amendmentDate !== '') {
        $('#amendment_no').prop('required', true);
    } else {
        $('#amendment_no').prop('required', false).removeClass('is-invalid');
    }
}

$(document).on('input change', '#amendment_no, #amendment_date', function () {
    validateAmendmentFields();
});

$('#TestReportMptModal').on('shown.bs.modal', function () {
    validateAmendmentFields();
});

$(document).on('change', '#detail_em_id', function () {
    var option = $(this).find('option:selected');
    if (option.val()) {
        $('#eq_make').val(option.data('make') || '');
        $('#eq_display').val(option.data('display') || option.data('em_equipment_name') || '');
        $('#eq_sr_no').val(option.data('sr_no') || '');
        $('#eq_cal_due_date').val(option.data('cal_due_date') || '');
    } else {
        $('#eq_make').val('');
        $('#eq_display').val('');
        $('#eq_sr_no').val('');
        $('#eq_cal_due_date').val('');
    }
});

function checkReportSequenceDuplication() {
    let seq = $('#test_report_sequence').val();
    if (seq != "") {
        if (seq > 0 == false) {
            toastr.error('Please Enter Valid Sr. No.');
            $('#test_report_sequence').val('');
            $('#test_report_sequence').focus();
        } else {
            $('#test_report_sequence').addClass('file-loader');
            let id = jQuery('#id').val();
            skipLoader = false;
            showLoader();
            $.ajax({
                url: 'check-test_report_mpt_number_duplication',
                type: 'GET',
                data: { test_report_sequence: seq, id: id },
                dataType: 'json',
                success: function (data) {
                    $('#test_report_sequence').removeClass('file-loader');
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        $('#test_report_sequence').val('');
                        $('#test_report_sequence').focus();
                        hideLoader();
                    } else {
                        $('#test_report_no').val(data.latest_no);
                        $('#test_report_sequence').val(seq);
                        hideLoader();
                    }
                },
                error: function () {
                    $('#test_report_sequence').removeClass('file-loader');
                    hideLoader();
                    toastr.error('Something went wrong!');
                }
            });
        }
    }
}

$(document).on('change', '#test_report_sequence', function () {
    checkReportSequenceDuplication();
});

$(document).on('input change', '#total_qty', function () {
    var pendQty = parseInt($('#pend_qty').val()) || 0;
    var totalQty = parseInt($(this).val()) || 0;
    if (totalQty > pendQty && pendQty > 0) {
        toastr.error('Total Qty Cannot Be Greater Than Pending Qty.');
        $(this).val(pendQty);
    }
});

function validateImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i;
    return allowedExtensions.exec(filePath) ? true : false;
}

$(document).on('change', '#defectogram_image', function (e) {
    var form_data = new FormData();
    var target = e.target;
    var files = target.files;
    var totalfiles = files.length;
    var oldImg = $('#defectogram_image_doc').val();

    if (totalfiles > 0) {
        var notValid = 0;
        for (var index = 0; index < totalfiles; index++) {
            if (validateImage(files[index].name) == true) {
                form_data.append("docs[]", files[index]);
            } else {
                notValid = 1;
                toastr.error("Only (jpeg,jpg,png,gif,pdf) files are allowed.");
                $('#defectogram_image_doc').val('');
                $('#defectogram_image').val('');
                $('#defectogram_image_prev').attr('href', '#').addClass('hide');
                $('#defectogram_image_remove').removeClass('i-block').addClass('hide');
                return false;
            }
        }

        if (notValid == 0) {
            $('#submitbtn').prop('disabled', true);
            $('#defectogram_image').addClass('file-loader');
            $.ajax({
                url: 'upload-docs',
                type: 'POST',
                data: form_data,
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    $('#submitbtn').prop('disabled', false);
                    $('#defectogram_image').removeClass('file-loader');
                    if (data.response_code == 1) {
                        if (oldImg != "" && oldImg.includes('temp_media/')) {
                            removeMedia(oldImg);
                        }

                        $('#defectogram_image_doc').val(data.files);
                        $('#defectogram_image_prev').attr('href', data.files_url).text('View').removeClass('hide');
                        $('#defectogram_image_remove').removeClass('hide').addClass('i-block');
                    } else {
                        toastr.error(data.response_message);
                    }
                },
                error: function () {
                    $('#submitbtn').prop('disabled', false);
                    $('#defectogram_image').removeClass('file-loader');
                    toastr.error('Something went wrong with file upload!');
                }
            });
        }
    } else {
        if (oldImg != "") {
            let fileName = oldImg.split('/').pop();
            let fileInput = $('#defectogram_image');
            let dataTransfer = new DataTransfer();
            dataTransfer.items.add(new File([""], fileName));
            fileInput[0].files = dataTransfer.files;
            return false;
        }
        $('#defectogram_image_doc').val('');
        $('#defectogram_image').val('');
        $('#defectogram_image_prev').attr('href', '#').addClass('hide');
        $('#defectogram_image_remove').addClass('hide').removeClass('i-block');
    }
});

function removeFileTestReportMpt(e) {
    e.stopImmediatePropagation();
    toastDetailDelete('Do you want to delete this record?', function () {
        var oldImg = $('#defectogram_image_doc').val();

        if (oldImg != "" && oldImg.includes('temp_media/')) {
            removeMedia(oldImg);
        }

        $('#defectogram_image_doc').val('');
        $('#defectogram_image').val('');

        $('#defectogram_image_prev').attr('href', '#').text('View').addClass('hide');
        $('#defectogram_image_remove').addClass('hide').removeClass('i-block');
    });
}

function getTRLNRData() {
    $.ajax({
        url: "get-test_report_mpt_lnr_data",
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.lnr_data != null) {
                var lnr = data.lnr_data;
                $('#note').val(lnr.note ?? "");
            }
        },
        error: function () {
            console.log('Error fetching Test Report MPT LNR data');
        }
    });
}


