var allInvoiceDetailsData = [];
var availablePendingFilmDcs = [];
var selectedPendingDcIds = new Set();
var _isInvoiceEditMode = false;
var currentInvoiceEditId = null;
var originalInvoiceEditData = null;

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
    jQuery('.trans-date-picker, .date-picker').datepicker({
        dateFormat: "dd/mm/yy",
        onClose: function () {
            this.focus();
        }
    });

    // 2. Initialize Select2
    jQuery('#customer_id, #tax_type, #sac_id, #pos_state_id').select2({
        dropdownParent: jQuery('#InvoiceModal')
    });

    jQuery('#detail_film_brand_id, #detail_film_id').select2({
        dropdownParent: jQuery('#InvoiceDetailModal')
    });

    // 3. Open Modal for ADD Mode
    jQuery('#add_invoice_btn').on('click', function () {
        resetToInvoiceAddMode();
    });

    // 4. Source & Customer Change Handling
    jQuery('input[name="source_type"]').on('change', function () {
        let source = jQuery(this).val();
        let custId = jQuery('#customer_id').val();

        if (source === 'Manual') {
            jQuery('#pending_btn').prop('disabled', true);
        } else {
            jQuery('#pending_btn').prop('disabled', !custId);
        }
    });

    jQuery('#customer_id').on('change', function () {
        let custId = jQuery(this).val();
        let source = jQuery('input[name="source_type"]:checked').val();

        if (custId && source !== 'Manual') {
            jQuery('#pending_btn').prop('disabled', false);
        } else {
            jQuery('#pending_btn').prop('disabled', true);
        }

        if (custId) {
            jQuery.ajax({
                url: 'get-customer-details-for-invoice',
                type: 'GET',
                data: { customer_id: custId },
                headers: getAjaxHeaders(),
                success: function (res) {
                    if (res.response_code == 1 && res.data) {
                        jQuery('#pos_customer').val(res.data.pos_customer || '');
                        jQuery('#pos_address').val(res.data.pos_address || '');
                        if (res.data.pos_state_id) {
                            jQuery('#pos_state_id').val(res.data.pos_state_id).trigger('change');
                        } else {
                            jQuery('#pos_state_id').val('').trigger('change');
                            jQuery('#state_code').val('');
                        }
                        jQuery('#pos_gstin_no').val(res.data.pos_gstin_no || '');
                        jQuery('#pos_pan_no').val(res.data.pos_pan_no || '');
                        if (res.data.due_days && parseInt(res.data.due_days) > 0) {
                            jQuery('#due_days').val(res.data.due_days);
                            calculateInvoiceDueDate();
                        } else {
                            jQuery('#due_days').val('');
                            jQuery('#due_date').val('');
                        }
                    }
                }
            });
        }
    });

    // 4.1 POS State Change Handling (Auto-fill State Code)
    jQuery('#pos_state_id').on('change', function () {
        let stateCode = jQuery(this).find('option:selected').data('state_code') || '';
        jQuery('#state_code').val(stateCode);
    });

    // 5. Sequence Number Change & Submit Button Lock Rule
    jQuery(document).on('keyup change', '#invoice_sequence', function () {
        let seq = jQuery(this).val();
        let id = jQuery('#id').val() || '';

        if (seq != '') {
            jQuery('#submitbtn, #updatebtn').prop('disabled', true);

            jQuery.ajax({
                url: 'check-invoice_number_duplication',
                type: 'GET',
                data: { invoice_sequence: seq, id: id },
                headers: getAjaxHeaders(),
                success: function (res) {
                    if (res.response_code == 1) {
                        jQuery('#invoice_no').val(res.latest_no);
                        jQuery('#submitbtn, #updatebtn').prop('disabled', false);
                    } else {
                        toastr.error(res.response_message || 'Duplicate Bill No. Found.');
                    }
                },
                error: function () {
                    jQuery('#submitbtn, #updatebtn').prop('disabled', false);
                }
            });
        }
    });

    // 6. Tax Type Dropdown Handling
    jQuery('#tax_type').on('change', function () {
        manageGstType();
    });

    // 7. Calculations Triggers
    jQuery(document).on('keyup change blur', '.calc-trigger, .gst-fields', function () {
        calcGstAmount();
    });

    jQuery('#round_off_val').on('input keyup change', function () {
        calcNetAmount();
    });

    jQuery('#invoice_date, #due_days').on('change keyup', function () {
        calculateInvoiceDueDate();
    });

    // 8. Open Pending Modal -> Load Items & Init Standard DataTable
    jQuery('#pending_btn').on('click', function () {
        var customerId = jQuery('#customer_id').val();
        var jobType = jQuery('input[name="job_type_fix"]:checked').val();
        var filmSizeUnit = jQuery('input[name="film_size_unit_fix"]:checked').val();
        var editInvoiceId = jQuery('#commonInvoiceForm #id').val();

        if (!customerId) {
            toastr.error('Please Select Customer first.');
            return;
        }

        var $table = jQuery('#PendingFilmDcTable');
        if (jQuery.fn.DataTable.isDataTable($table)) {
            $table.DataTable().clear().destroy();
        }

        jQuery('#PendingFilmDcTable tbody').html('<tr><td colspan="7" class="text-center">Loading pending items...</td></tr>');
        jQuery('#checkall-pending_data').prop('checked', false);
        selectedPendingDcIds.clear();

        jQuery.ajax({
            url: 'get-pending-film-dc-for-invoice',
            type: 'GET',
            data: {
                customer_id: customerId,
                job_type_fix: jobType,
                film_size_unit_fix: filmSizeUnit,
                edit_invoice_id: editInvoiceId
            },
            headers: getAjaxHeaders(),
            success: function (res) {
                if (res.response_code == 1 && res.film_dcs && res.film_dcs.length > 0) {
                    var html = '';
                    availablePendingFilmDcs = res.film_dcs;
                    jQuery.each(res.film_dcs, function (idx, item) {
                        var partyName = item.party_name || item.customer_client || item.customer_name || '';
                        html += `<tr>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input pending-dc-check" value="${item.film_dc_id}">
                            </td>
                            <td>${item.film_dc_no || ''}</td>
                            <td>${item.film_dc_date || ''}</td>
                            <td>${item.job_type_fix || ''}</td>
                            <td>${partyName}</td>
                            <td>${parseFloat(item.total_qty || 0).toFixed(3)}</td>
                            <td>${item.sp_note || ''}</td>
                        </tr>`;
                    });
                    jQuery('#PendingFilmDcTable tbody').html(html);

                    var $new = $table.DataTable({
                        paging: true,
                        searching: true,
                        dom: 'lrtip',
                        order: [[1, 'desc']],
                        columnDefs: [
                            { orderable: false, targets: 0 }
                        ],
                        "sScrollX": true,
                        "sScrollX": "100%",
                        "bScrollCollapse": true,
                    });

                    $new.on('draw', function () {
                        syncPendingCheckboxesWithSet();
                    });

                    if (typeof fixDataTableColumnsUntilAdjusted === 'function') {
                        fixDataTableColumnsUntilAdjusted($new);
                    }
                } else {
                    availablePendingFilmDcs = [];
                    jQuery('#PendingFilmDcTable tbody').html('<tr><td colspan="7" class="text-center text-muted">No pending Film DC found for this customer.</td></tr>');
                }
            },
            error: function () {
                jQuery('#PendingFilmDcTable tbody').html('<tr><td colspan="7" class="text-center text-danger">Failed to load pending items.</td></tr>');
            }
        });

        jQuery('#PendingFilmDcForInvoiceModal').modal('show');
    });

    jQuery('#PendingFilmDcForInvoiceModal').on('shown.bs.modal', function () {
        let $table = jQuery('#PendingFilmDcTable');
        if (jQuery.fn.DataTable.isDataTable($table)) {
            $table.DataTable().columns.adjust().draw();
            if (typeof initColumnSearch === 'function') {
                initColumnSearch('#PendingFilmDcTable', [0], 'common_search');
            }
        }
    });

    // 9. Checkall & Row Checkbox Multi-Page Handling via Set
    jQuery(document).on('change', '#checkall-pending_data', function () {
        var isChecked = jQuery(this).is(':checked');
        if (isChecked) {
            availablePendingFilmDcs.forEach(function (row) {
                selectedPendingDcIds.add(parseInt(row.film_dc_id));
            });
        } else {
            selectedPendingDcIds.clear();
        }
        syncPendingCheckboxesWithSet();
    });

    jQuery(document).on('change', '.pending-dc-check', function () {
        var id = parseInt(jQuery(this).val());
        if (jQuery(this).is(':checked')) {
            selectedPendingDcIds.add(id);
        } else {
            selectedPendingDcIds.delete(id);
        }
        updatePendingCheckAllState();
    });

    // 10. Import Selected Film DCs from Pending Modal
    jQuery('#import_pending_dc_btn').on('click', function () {
        if (selectedPendingDcIds.size === 0) {
            toastr.error('Select At Least One Item.');
            return;
        }

        let selectedIds = Array.from(selectedPendingDcIds);
        let btn = jQuery(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Submitting...');

        jQuery.ajax({
            url: 'get-film-dc-items-for-invoice',
            type: 'POST',
            data: { film_dc_ids: selectedIds },
            headers: getAjaxHeaders(),
            success: function (res) {
                btn.prop('disabled', false).html('Submit');
                if (res.response_code == 1 && res.items && res.items.length > 0) {
                    res.items.forEach(function (item) {
                        allInvoiceDetailsData.push(item);
                    });
                    renderInvoiceDetailsGrid();
                    calculateAllInvoiceTotals();
                    jQuery('#PendingFilmDcForInvoiceModal').modal('hide');
                    toastr.success('Record Inserted.');
                } else {
                    toastr.info('No item details found for selected Film DCs.');
                }
            },
            error: function () {
                btn.prop('disabled', false).html('Submit');
                toastr.error('Error fetching Film DC details.');
            }
        });
    });

    // 11. Invoice Detail Sub-Modal (Add / Edit Item)
    jQuery('#addDetailRowBtn').on('click', function () {
        resetInvoiceDetailSubModal();
        jQuery('#InvoiceDetailModal #form_type').val('add');
        jQuery('#InvoiceDetailModal #form_index').val('-1');
        jQuery('#InvoiceDetailModal #row_index').val('-1');
        jQuery('#InvoiceDetailModal #submitbtn').text('Add');
        jQuery('#InvoiceDetailModal').modal('show');
    });

    jQuery(document).on('input keyup', '.detail-calc-input', function () {
        let qty = parseFloat(jQuery('#detail_qty').val()) || 0;
        let rate = parseFloat(jQuery('#detail_rate').val()) || 0;
        let amount = qty * rate;
        jQuery('#detail_amount').val(amount.toFixed(2));
    });

    jQuery('#InvoiceDetailForm').on('submit', function (e) {
        e.preventDefault();

        let desc = jQuery('#detail_description').val().trim();
        let qty = parseFloat(jQuery('#detail_qty').val()) || 0;
        let rate = parseFloat(jQuery('#detail_rate').val()) || 0;

        if (!desc) {
            jQuery('#detail_description').addClass('is-invalid');
            return;
        } else {
            jQuery('#detail_description').removeClass('is-invalid');
        }

        if (qty <= 0) {
            jQuery('#detail_qty').addClass('is-invalid');
            return;
        } else {
            jQuery('#detail_qty').removeClass('is-invalid');
        }

        let editIndex = parseInt(jQuery('#detail_edit_index').val());
        let filmBrandId = jQuery('#detail_film_brand_id').val();
        let filmBrandName = jQuery('#detail_film_brand_id option:selected').text();
        if (!filmBrandId) filmBrandName = '';

        let filmId = jQuery('#detail_film_id').val();
        let filmSize = jQuery('#detail_film_id option:selected').text();
        if (!filmId) filmSize = '';

        let amount = qty * rate;

        let rowData = {
            mode: editIndex >= 0 ? (allInvoiceDetailsData[editIndex].mode === 'New' ? 'New' : 'Update') : 'New',
            invoice_detail_id: jQuery('#detail_invoice_detail_id').val() || 0,
            film_dc_id: jQuery('#detail_film_dc_id').val() || null,
            film_dc_detail_id: jQuery('#detail_film_dc_detail_id').val() || null,
            dc_no: jQuery('#detail_dc_no').val() || '',
            dc_date: jQuery('#detail_dc_date').val() || '',
            test_report_no: jQuery('#detail_test_report_no').val() || '',
            description: desc,
            film_brand_id: filmBrandId || null,
            film_brand_name: filmBrandName,
            film_id: filmId || null,
            film_size: filmSize,
            film_qty: parseFloat(jQuery('#detail_film_qty').val()) || 0,
            qty: qty,
            unit_id: null,
            unit_name: jQuery('#detail_unit_name').val() || 'Nos',
            rate: rate,
            amount: amount,
            remark: desc
        };

        if (editIndex >= 0) {
            allInvoiceDetailsData[editIndex] = rowData;
            toastr.success('Record Updated.');
        } else {
            allInvoiceDetailsData.push(rowData);
            toastr.success('Record Inserted.');
        }

        renderInvoiceDetailsGrid();
        calculateAllInvoiceTotals();
        jQuery('#InvoiceDetailModal').modal('hide');
    });

    // (Detail modal Reset button removed as per ERP standard)

    // 12. Main Form Submission (Double Submit Prevention & Toastr)
    jQuery('#commonInvoiceForm').on('submit', function (e) {
        e.preventDefault();

        let form = jQuery(this)[0];
        if (!form.checkValidity()) {
            e.stopPropagation();
            jQuery(this).addClass('was-validated');
            return;
        }

        let activeDetails = allInvoiceDetailsData.filter(function (d) {
            return d.mode !== 'Delete';
        });

        if (activeDetails.length === 0) {
            toastr.error('Please add at least one Invoice Detail item.');
            return;
        }

        let isUpdate = _isInvoiceEditMode;
        let submitBtn = isUpdate ? jQuery('#updatebtn') : jQuery('#submitbtn');
        let originalBtnText = submitBtn.text();

        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');

        let formData = jQuery(this).serializeArray();
        formData.push({ name: 'details', value: JSON.stringify(allInvoiceDetailsData) });

        let postUrl = isUpdate ? "update-invoice" : "store-invoice";

        jQuery.ajax({
            url: postUrl,
            type: 'POST',
            data: formData,
            headers: getAjaxHeaders(),
            success: function (res) {
                submitBtn.prop('disabled', false).text(originalBtnText);
                if (res.response_code == 1) {
                    toastr.success(res.response_message || (isUpdate ? 'Record Updated.' : 'Record Inserted.'));
                    jQuery('#InvoiceModal').modal('hide');
                    if (typeof table !== 'undefined') {
                        table.draw();
                    }
                } else {
                    toastr.error(res.response_message || 'Operation failed.');
                }
            },
            error: function (xhr) {
                submitBtn.prop('disabled', false).text(originalBtnText);
                let err = xhr.responseJSON ? xhr.responseJSON.message : 'Server error occurred.';
                toastr.error(err);
            }
        });
    });

    // 13. Reset Button Handling
    jQuery('#resetbtn').on('click', function () {
        if (_isInvoiceEditMode && originalInvoiceEditData) {
            populateInvoiceFormForEdit(originalInvoiceEditData);
        } else {
            resetToInvoiceAddMode();
        }
    });

    jQuery('#add_new').on('click', function () {
        resetToInvoiceAddMode();
    });

    // 14. Edit Invoice Action Trigger
    jQuery(document).on('click', '.edit-invoice', function () {
        let id = jQuery(this).data('id');
        if (!id) return;

        jQuery.ajax({
            url: "edit-invoice",
            type: 'GET',
            data: { id: id },
            headers: getAjaxHeaders(),
            success: function (res) {
                if (res.response_code == 1 && res.invoice) {
                    _isInvoiceEditMode = true;
                    currentInvoiceEditId = id;
                    originalInvoiceEditData = res;
                    populateInvoiceFormForEdit(res);
                    jQuery('#InvoiceModal').modal('show');
                } else {
                    toastr.error(res.response_message || 'Unable to fetch invoice details.');
                }
            }
        });
    });

    // 15. Delete Invoice (SweetAlert Confirmation)
    jQuery(document).on('click', '.delete-invoice', function () {
        let id = jQuery(this).data('id');
        let name = jQuery(this).data('name') || 'Invoice';

        Swal.fire({
            title: 'Are you sure?',
            text: "Are you sure you want to delete " + name + "?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                jQuery.ajax({
                    url: "delete-invoice",
                    type: 'POST',
                    data: { id: id },
                    headers: getAjaxHeaders(),
                    success: function (res) {
                        if (res.response_code == 1) {
                            toastr.success(res.response_message || 'Record Deleted.');
                            if (typeof table !== 'undefined') {
                                table.draw();
                            }
                        } else {
                            toastr.error(res.response_message || 'Error deleting record.');
                        }
                    },
                    error: function () {
                        toastr.error('Error deleting record.');
                    }
                });
            }
        });
    });
});

/**
 * Edit / Delete Detail Row Action Functions
 */
function editInvoiceDetailRow(th) {
    let formIndx = jQuery(th).closest('tr').find('input[name="form_indx"]').val();
    openDetailEditForIndex(parseInt(formIndx));
}

function removeInvoiceDetailRow(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest('tr').find('input[name="form_indx"]').val();
        let item = allInvoiceDetailsData[formIndx];
        if (item.invoice_detail_id && item.invoice_detail_id != 0) {
            item.mode = "Delete";
        } else {
            allInvoiceDetailsData.splice(formIndx, 1);
        }
        renderInvoiceDetailsGrid();
        calculateAllInvoiceTotals();
    });
}

function openDetailEditForIndex(index) {
    let row = allInvoiceDetailsData[index];
    if (!row) return;

    jQuery('#detail_edit_index').val(index);
    jQuery('#detail_invoice_detail_id').val(row.invoice_detail_id || 0);
    jQuery('#detail_film_dc_id').val(row.film_dc_id || '');
    jQuery('#detail_film_dc_detail_id').val(row.film_dc_detail_id || '');
    jQuery('#detail_dc_no').val(row.dc_no || '');
    jQuery('#detail_dc_date').val(row.dc_date || '');
    jQuery('#detail_test_report_no').val(row.test_report_no || '');
    jQuery('#detail_film_qty').val(row.film_qty || 0);

    jQuery('#detail_description').val(row.description || row.remark || '');
    jQuery('#detail_film_brand_id').val(row.film_brand_id || '').trigger('change');
    jQuery('#detail_film_id').val(row.film_id || '').trigger('change');
    jQuery('#detail_qty').val(row.qty || 1);
    jQuery('#detail_unit_name').val(row.unit_name || 'Nos');
    jQuery('#detail_rate').val(row.rate || 0);
    jQuery('#detail_amount').val((row.amount || 0).toFixed(2));

    jQuery('#InvoiceDetailModal #form_type').val('edit');
    jQuery('#InvoiceDetailModal #form_index').val(index);
    jQuery('#InvoiceDetailModal #row_index').val(index);
    jQuery('#InvoiceDetailModal #submitbtn').text('Edit');
    jQuery('#InvoiceDetailModal').modal('show');
}

function syncPendingCheckboxesWithSet() {
    jQuery('#PendingFilmDcTable tbody .pending-dc-check').each(function () {
        var id = parseInt(jQuery(this).val());
        jQuery(this).prop('checked', selectedPendingDcIds.has(id));
    });
    updatePendingCheckAllState();
}

function updatePendingCheckAllState() {
    var allChecked = availablePendingFilmDcs.length > 0 && selectedPendingDcIds.size === availablePendingFilmDcs.length;
    jQuery('#checkall-pending_data').prop('checked', allChecked);
}

/**
 * Render Invoice Details Grid Table (Without Footer)
 */
function renderInvoiceDetailsGrid() {
    let tbody = jQuery('#invoice_details_tbody');
    let activeRows = 0;
    let totalAmount = 0;

    let html = '';
    allInvoiceDetailsData.forEach(function (row, idx) {
        if (row.mode === 'Delete') return;
        activeRows++;

        let filmQty = parseFloat(row.film_qty || 0);
        let qty = parseFloat(row.qty || 0);
        let amount = parseFloat(row.amount || 0);

        totalAmount += amount;

        html += `<tr>
            <td>
                ${typeof DetailsActionDropdown === 'function' ? DetailsActionDropdown('editInvoiceDetailRow', 'removeInvoiceDetailRow') : '<a href="javascript:void(0);" onclick="editInvoiceDetailRow(this)" class="link-success"><i class="ri-edit-2-line"></i></a> <a href="javascript:void(0);" onclick="removeInvoiceDetailRow(this)" class="link-danger ms-2"><i class="ri-delete-bin-line"></i></a>'}
                <input type="hidden" name="form_indx" value="${idx}"/>
            </td>
            <td>${row.dc_no || '-'}</td>
            <td>${row.dc_date || '-'}</td>
            <td>${row.test_report_no || '-'}</td>
            <td>${row.film_brand_name || '-'}</td>
            <td>${row.film_size || '-'}</td>
            <td>${filmQty.toFixed(2)}</td>
            <td>${qty.toFixed(2)}</td>
            <td>${row.unit_name || 'Nos'}</td>
            <td>${parseFloat(row.rate || 0).toFixed(2)}</td>
            <td class="amount">${amount.toFixed(2)}</td>
        </tr>`;
    });

    if (allInvoiceDetailsData.length === 0 || activeRows === 0) {
        html = `<tr class="centeralign" id="noInvoiceDetails"><td colspan="11">No Invoice Details Added</td></tr>`;
    }

    tbody.html(html);
    jQuery('#basic_amount').val(totalAmount.toFixed(2));
}

/**
 * Manage GST Type based directly on Tax Type dropdown
 */
function manageGstType() {
    var taxType = jQuery('#tax_type').val();
    var thisForm = jQuery('#commonInvoiceForm');

    if (taxType === 'Exempted') {
        thisForm.find(".igst-field").val('');
        thisForm.find(".igst-field:not(.disb)").prop('disabled', true);
        thisForm.find(".sgst-field").val('');
        thisForm.find(".sgst-field:not(.disb)").prop('disabled', true);
        thisForm.find(".cgst-field").val('');
        thisForm.find(".cgst-field:not(.disb)").prop('disabled', true);
    } else if (taxType === 'SGST + CGST') {
        thisForm.find(".igst-field").val('');
        thisForm.find(".sgst-field:not(.disb)").val(parseFloat(9).toFixed(2));
        thisForm.find(".cgst-field:not(.disb)").val(parseFloat(9).toFixed(2));
        thisForm.find(".igst-field:not(.disb)").prop('disabled', true);
        thisForm.find(".sgst-field:not(.disb)").prop('disabled', false);
        thisForm.find(".cgst-field:not(.disb)").prop('disabled', false);
    } else if (taxType === 'IGST') {
        thisForm.find(".sgst-field").val('');
        thisForm.find(".cgst-field").val('');
        thisForm.find(".igst-field:not(.disb)").val(parseFloat(18).toFixed(2));
        thisForm.find(".igst-field:not(.disb)").prop('disabled', false);
        thisForm.find(".sgst-field:not(.disb)").prop('disabled', true);
        thisForm.find(".cgst-field:not(.disb)").prop('disabled', true);
    }
    calcGstAmount();
}

/**
 * Calculate GST Amounts
 */
function calcGstAmount() {
    var thisForm = jQuery('#commonInvoiceForm');
    var taxType = thisForm.find("#tax_type").val();
    var basicAmount = parseFloat(thisForm.find("#basic_amount").val()) || 0;
    var discount = parseFloat(thisForm.find("#discount_amount").val()) || 0;
    var sumAmount = Math.max(0, basicAmount - discount);

    if (taxType === 'Exempted') {
        thisForm.find("#sgst_amount").val('0.00');
        thisForm.find("#cgst_amount").val('0.00');
        thisForm.find("#igst_amount").val('0.00');
    } else if (taxType === 'SGST + CGST') {
        var sgstPer = parseFloat(thisForm.find("#sgst_percentage").val()) || 0;
        var cgstPer = parseFloat(thisForm.find("#cgst_percentage").val()) || 0;

        if (sumAmount > 0 && sgstPer > 0) {
            thisForm.find("#sgst_amount").val((sumAmount * (sgstPer / 100)).toFixed(2));
        } else {
            thisForm.find("#sgst_amount").val('0.00');
        }

        if (sumAmount > 0 && cgstPer > 0) {
            thisForm.find("#cgst_amount").val((sumAmount * (cgstPer / 100)).toFixed(2));
        } else {
            thisForm.find("#cgst_amount").val('0.00');
        }
        thisForm.find("#igst_amount").val('0.00');
    } else if (taxType === 'IGST') {
        var igstPer = parseFloat(thisForm.find("#igst_percentage").val()) || 0;
        if (sumAmount > 0 && igstPer > 0) {
            thisForm.find("#igst_amount").val((sumAmount * (igstPer / 100)).toFixed(2));
        } else {
            thisForm.find("#igst_amount").val('0.00');
        }
        thisForm.find("#sgst_amount").val('0.00');
        thisForm.find("#cgst_amount").val('0.00');
    }

    calcNetAmount();
    calculateRoundoffVal();
}

/**
 * Calculate Net Amount & Round Off
 */
function calcNetAmount() {
    var thisForm = jQuery('#commonInvoiceForm');
    var taxType = thisForm.find("#tax_type").val();
    var basicAmount = parseFloat(thisForm.find("#basic_amount").val()) || 0;
    var discount = parseFloat(thisForm.find("#discount_amount").val()) || 0;
    var otherCharges = parseFloat(thisForm.find("#other_charges").val()) || 0;
    var taxableAmount = Math.max(0, basicAmount - discount);

    var r_val = thisForm.find("#round_off_val").val();
    var r = (r_val !== '' && !isNaN(Number(r_val))) ? Number(r_val) : 0;

    var sgstAmount = parseFloat(thisForm.find("#sgst_amount").val()) || 0;
    var cgstAmount = parseFloat(thisForm.find("#cgst_amount").val()) || 0;
    var igstAmount = parseFloat(thisForm.find("#igst_amount").val()) || 0;

    var totalTax = 0;
    if (taxType === 'SGST + CGST') {
        totalTax = sgstAmount + cgstAmount;
    } else if (taxType === 'IGST') {
        totalTax = igstAmount;
    }

    var subTotal = taxableAmount + totalTax + otherCharges;
    var netAmount = subTotal + r;

    thisForm.find("#net_amount").val(netAmount.toFixed(2));
}

function calculateRoundoffVal() {
    var thisForm = jQuery("#commonInvoiceForm");
    var basicAmount = parseFloat(thisForm.find("#basic_amount").val()) || 0;
    var discount = parseFloat(thisForm.find("#discount_amount").val()) || 0;
    var otherCharges = parseFloat(thisForm.find("#other_charges").val()) || 0;
    var taxableAmount = Math.max(0, basicAmount - discount);

    var sgstAmount = parseFloat(thisForm.find("#sgst_amount").val()) || 0;
    var cgstAmount = parseFloat(thisForm.find("#cgst_amount").val()) || 0;
    var igstAmount = parseFloat(thisForm.find("#igst_amount").val()) || 0;

    var taxType = thisForm.find("#tax_type").val();

    var subtotal = taxableAmount + otherCharges;
    if (taxType === 'SGST + CGST') {
        subtotal += sgstAmount + cgstAmount;
    } else if (taxType === 'IGST') {
        subtotal += igstAmount;
    }

    var roundedNet = Math.round(subtotal);
    var roundOff = (roundedNet - subtotal).toFixed(2);

    thisForm.find("#round_off_val").val(roundOff);
    thisForm.find("#net_amount").val(roundedNet.toFixed(2));
}

function calculateAllInvoiceTotals() {
    renderInvoiceDetailsGrid();
    calcGstAmount();
}

/**
 * Calculate Due Date
 */
function calculateInvoiceDueDate() {
    let invDateStr = jQuery('#invoice_date').val();
    let dueDaysStr = jQuery('#due_days').val();

    if (dueDaysStr !== '' && !isNaN(dueDaysStr) && invDateStr) {
        let dueDays = parseInt(dueDaysStr);
        let parts = invDateStr.split('/');
        if (parts.length === 3) {
            let dt = new Date(parts[2], parts[1] - 1, parts[0]);
            dt.setDate(dt.getDate() + dueDays);

            let d = String(dt.getDate()).padStart(2, '0');
            let m = String(dt.getMonth() + 1).padStart(2, '0');
            let y = dt.getFullYear();

            jQuery('#due_date').val(`${d}/${m}/${y}`);
        }
    }
}

/**
 * Reset Submodal for Detail Entry
 */
function resetInvoiceDetailSubModal() {
    jQuery('#InvoiceDetailModal #form_type').val('add');
    jQuery('#InvoiceDetailModal #form_index').val('-1');
    jQuery('#InvoiceDetailModal #row_index').val('-1');
    jQuery('#InvoiceDetailModal #submitbtn').text('Add');
    jQuery('#detail_edit_index').val('-1');
    jQuery('#detail_invoice_detail_id').val('0');
    jQuery('#detail_film_dc_id').val('');
    jQuery('#detail_film_dc_detail_id').val('');
    jQuery('#detail_dc_no').val('');
    jQuery('#detail_dc_date').val('');
    jQuery('#detail_test_report_no').val('');
    jQuery('#detail_film_qty').val('0');

    jQuery('#detail_description').val('').removeClass('is-invalid');
    jQuery('#detail_film_brand_id').val('').trigger('change');
    jQuery('#detail_film_id').val('').trigger('change');
    jQuery('#detail_qty').val('1').removeClass('is-invalid');
    jQuery('#detail_unit_name').val('Nos');
    jQuery('#detail_rate').val('0.00').removeClass('is-invalid');
    jQuery('#detail_amount').val('0.00');
}

/**
 * Reset Form to ADD Mode
 */
function resetToInvoiceAddMode() {
    _isInvoiceEditMode = false;
    currentInvoiceEditId = null;
    originalInvoiceEditData = null;
    allInvoiceDetailsData = [];

    let form = jQuery('#commonInvoiceForm')[0];
    form.reset();
    jQuery('#commonInvoiceForm').removeClass('was-validated');

    jQuery('#id').val('');
    jQuery('#ref_title').val('Ref. No.');
    jQuery('#ref_no').val('');
    jQuery('#ref_date').val('');
    jQuery('#customer_id').val('').trigger('change');
    jQuery('#pos_state_id').val('').trigger('change');
    jQuery('#state_code').val('');
    jQuery('#sac_id').val('').trigger('change');
    jQuery('#tax_type').val('SGST + CGST').trigger('change');

    let today = new Date();
    let dd = String(today.getDate()).padStart(2, '0');
    let mm = String(today.getMonth() + 1).padStart(2, '0');
    let yyyy = today.getFullYear();
    let todayFmt = `${dd}/${mm}/${yyyy}`;

    jQuery('#invoice_date').val(todayFmt);
    jQuery('#due_days').val('');
    jQuery('#due_date').val('');

    jQuery('#submitbtn').show().prop('disabled', false);
    jQuery('#updatebtn').hide();
    jQuery('#preview_btn').hide();
    jQuery('#add_new').hide();

    manageGstType();
    renderInvoiceDetailsGrid();
    calculateAllInvoiceTotals();

    // Fetch Latest Sequence
    jQuery.ajax({
        url: 'get-latest-sequence-invoice',
        type: 'GET',
        headers: getAjaxHeaders(),
        success: function (res) {
            if (res.response_code == 1) {
                jQuery('#invoice_sequence').val(res.number);
                jQuery('#invoice_no').val(res.latest_no);
            }
        }
    });
}

/**
 * Populate Invoice Form for EDIT Mode
 */
function populateInvoiceFormForEdit(data) {
    let inv = data.invoice;
    let details = data.details || [];

    jQuery('#id').val(inv.invoice_id);
    jQuery('#ref_title').val(inv.ref_title || 'Ref. No.');
    jQuery('#ref_no').val(inv.ref_no || '');
    jQuery('#ref_date').val(inv.ref_date || '');

    jQuery(`input[name="source_type"][value="${inv.source_type}"]`).prop('checked', true);
    jQuery(`input[name="job_type_fix"][value="${inv.job_type_fix}"]`).prop('checked', true);
    jQuery(`input[name="film_size_unit_fix"][value="${inv.film_size_unit_fix}"]`).prop('checked', true);

    jQuery('#customer_id').val(inv.customer_id).trigger('change.select2');
    jQuery('#customer_client').val(inv.customer_client || '');

    jQuery('#tax_type').val(inv.tax_type || 'SGST + CGST').trigger('change.select2');

    jQuery('#invoice_sequence').val(inv.invoice_sequence);
    jQuery('#invoice_no').val(inv.invoice_no);
    jQuery('#invoice_date').val(inv.invoice_date || '');

    if (inv.sac_id) {
        jQuery('#sac_id').val(inv.sac_id).trigger('change.select2');
    }

    jQuery('#pos_customer').val(inv.pos_customer || '');
    jQuery('#pos_address').val(inv.pos_address || '');
    if (inv.pos_state_id) {
        jQuery('#pos_state_id').val(inv.pos_state_id).trigger('change');
    } else {
        jQuery('#pos_state_id').val('').trigger('change');
        jQuery('#state_code').val('');
    }
    jQuery('#pos_gstin_no').val(inv.pos_gstin_no || '');
    jQuery('#pos_pan_no').val(inv.pos_pan_no || '');

    jQuery('#basic_amount').val(parseFloat(inv.basic_amount || 0).toFixed(2));
    jQuery('#discount_amount').val(parseFloat(inv.discount_amount || 0).toFixed(2));
    jQuery('#other_charges').val(parseFloat(inv.other_charges || 0).toFixed(2));

    manageGstType();

    if (inv.sgst_percent) jQuery('#sgst_percentage').val(parseFloat(inv.sgst_percent).toFixed(2));
    if (inv.sgst_amount) jQuery('#sgst_amount').val(parseFloat(inv.sgst_amount).toFixed(2));
    if (inv.cgst_percent) jQuery('#cgst_percentage').val(parseFloat(inv.cgst_percent).toFixed(2));
    if (inv.cgst_amount) jQuery('#cgst_amount').val(parseFloat(inv.cgst_amount).toFixed(2));
    if (inv.igst_percent) jQuery('#igst_percentage').val(parseFloat(inv.igst_percent).toFixed(2));
    if (inv.igst_amount) jQuery('#igst_amount').val(parseFloat(inv.igst_amount).toFixed(2));

    jQuery('#round_off_val').val(parseFloat(inv.round_off || 0).toFixed(2));
    jQuery('#net_amount').val(parseFloat(inv.net_amount || 0).toFixed(2));

    jQuery('#due_days').val(inv.due_days !== null && inv.due_days !== undefined && inv.due_days != 0 ? inv.due_days : '');
    jQuery('#due_date').val(inv.due_date || '');

    allInvoiceDetailsData = details;
    renderInvoiceDetailsGrid();

    jQuery('#submitbtn').hide();
    jQuery('#updatebtn').show().prop('disabled', false);
    jQuery('#add_new').show();

    if (typeof checkFileRoute !== 'undefined') {
        let printUrl = `${checkFileRoute}?id=${btoa(inv.invoice_id)}&name=Invoice_${inv.invoice_no}&type=invoice`;
        jQuery('#preview_btn').attr('href', printUrl).show();
    }
}
