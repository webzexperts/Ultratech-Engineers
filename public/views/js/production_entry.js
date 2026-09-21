/* =====================================================================
 *  Production Entry (parent + child) — mirrors material_inward.js.
 *  Detail rows are held in production_entry_details_data[] with a per-row
 *  `mode` (Insert / Update / Delete) and submitted as JSON on save.
 * ===================================================================== */

var production_entry_details_data = [];
var formId = jQuery('#commonProductionEntryForm').find('input[name="id"]').val();

/* ---------------------------------------------------------------------
 *  Edit (open + fill)
 * ------------------------------------------------------------------- */
jQuery('#dyntable tbody').on('click', '.edit-production_entry', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#ProductionEntryModal').find('#id').val(data["production_entry_id"]);
    if (data && data["production_entry_id"]) {
        fetchAndFillProductionEntry(data["production_entry_id"]);
    }
});

function fetchAndFillProductionEntry(id) {
    if (!id) return;
    jQuery('#ProductionEntryModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-production_entry",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.pe_data != null) {
                var d = data.pe_data;
                window.can_update_production_entry = data.can_update !== false;

                if (window.can_update_production_entry === false) {
                    jQuery('#ProductionEntryModal').find('#submitbtn').prop('disabled', true);
                } else {
                    jQuery('#ProductionEntryModal').find('#submitbtn').prop('disabled', false);
                }

                jQuery('#ProductionEntryModal').find('#id').val(d.production_entry_id != "" ? d.production_entry_id : "");

                jQuery('#ProductionEntryModal').find('#production_entry_sequence').val(d.production_entry_sequence ?? "").focus();
                jQuery('#ProductionEntryModal').find('#production_entry_no').val(d.production_entry_no ?? "");
                jQuery('#ProductionEntryModal').find('#production_entry_date').val(d.production_entry_date ?? "");
                jQuery('#ProductionEntryModal').find('#enclosure_id').val(d.enclosure_id ?? "").trigger('change.select2');
                jQuery('#ProductionEntryModal').find('#remark').val(d.remark ?? "");

                production_entry_details_data = [];
                if (data.pe_details_data != "" && data.pe_details_data.length > 0) {
                    data.pe_details_data.forEach(function (item) {
                        if (item.production_entry_detail_id && !item.production_entry_details_id) {
                            item.production_entry_details_id = item.production_entry_detail_id;
                        }
                    });
                    production_entry_details_data.push(...data.pe_details_data);
                    fillProductionEntryDetailTable();
                } else {
                    fillProductionEntryDetailTable();
                }

                const form = document.getElementById("commonProductionEntryForm");
                if (form) form.classList.remove('was-validated');
                if (window.can_update_production_entry === false) {
                    jQuery('#ProductionEntryModal').find('#submitbtn').prop('disabled', true);
                } else {
                    jQuery('#ProductionEntryModal').find('#submitbtn').prop('disabled', false);
                }
                jQuery('#ProductionEntryModal').find('#add_new').show();
            } else {
                console.log(data.response_message);
            }
        },
        error: function (jqXHR) {
            if (jqXHR.status == 401) { console.log(jqXHR.statusText); }
            else { console.log('Something went wrong!'); }
        },
        complete: function () {
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        }
    });
}

/* ---------------------------------------------------------------------
 *  Redraw Details Grid / Table
 * ------------------------------------------------------------------- */
function fillProductionEntryDetailTable() {
    var tblHtml = ``;
    var totalConsAll = 0;

    if (production_entry_details_data.length > 0) {
        production_entry_details_data.forEach(function (d, index) {
            if (d.mode == 'Delete') return;

            var shiftName = d.shift_name ?? jQuery(`#ped_shift_id option[value="${d.shift_id}"]`).text() ?? '';
            var source = d.source_id_fix ?? '';
            var groupName = d.item_group ?? jQuery(`#ped_item_group_id option[value="${d.item_group_id}"]`).text() ?? '';
            var itemName = d.item_name ?? jQuery(`#ped_item_id option[value="${d.item_id}"]`).text() ?? '';
            var stockQty = d.stock_qty ?? parseFloat(d.io_stock_qty || 0).toFixed(3);
            var unit = 'SQIN';
            var prodQty = parseFloat(d.production_sq_in || 0).toFixed(2);
            var retakeQty = parseFloat(d.retake_sq_in || 0).toFixed(2);
            var wastageQty = parseFloat(d.westage_in || 0).toFixed(2);
            var totalQty = parseFloat(d.total_sq_in || 0).toFixed(2);

            totalConsAll += parseFloat(totalQty) || 0;

            tblHtml += `<tr>`;
            tblHtml += `<td>`;
            tblHtml += DetailsActionDropdown('editProductionEntryDetails', 'removeProductionEntryDetails');
            tblHtml += `<input type="hidden" name="form_indx" value="${index}"/></td>`;
            tblHtml += `<td>${shiftName}</td>`;
            tblHtml += `<td>${source}</td>`;
            tblHtml += `<td>${groupName}</td>`;
            tblHtml += `<td>${itemName}</td>`;
            tblHtml += `<td class="text-end">${stockQty}</td>`;
            tblHtml += `<td>SQIN</td>`;
            tblHtml += `<td class="text-end">${prodQty}</td>`;
            tblHtml += `<td class="text-end">${retakeQty}</td>`;
            tblHtml += `<td class="text-end">${wastageQty}</td>`;
            tblHtml += `<td class="text-end">${totalQty}</td>`;
            tblHtml += `</tr>`;
        });
    }

    if (tblHtml === '') {
        tblHtml = `<tr><td colspan="11" id="noDetails" class="text-center">No Production Entry Details Added</td></tr>`;
    }

    jQuery('#ProductionEntryDetailTable tbody').empty().append(tblHtml);
    jQuery('#total_sq_in').val(totalConsAll.toFixed(2));
}

/* ---------------------------------------------------------------------
 *  Modal Lifecycle & Triggers
 * ------------------------------------------------------------------- */
jQuery('#ProductionEntryModal').on('show.bs.modal', function () {
    var formId = jQuery('#commonProductionEntryForm').find('input[name="id"]').val();
    if (formId == "" || formId == undefined) {
        window.can_update_production_entry = true;
        jQuery('#ProductionEntryModal').find('#submitbtn').prop('disabled', false);
        getLatestProductionEntryNo();
        jQuery('#ProductionEntryModal').find('#add_new').hide();
        production_entry_details_data = [];
        fillProductionEntryDetailTable();
    } else {
        jQuery('#ProductionEntryModal').find('#add_new').show();
    }
});

jQuery('#ProductionEntryModal').on('hide.bs.modal', function () {
    let thisModal = jQuery('#ProductionEntryModal');
    thisModal.find("#id").val("");
    jQuery('#commonProductionEntryForm').trigger("reset");
    jQuery('#ProductionEntryModal').find('#enclosure_id').val('').trigger('change.select2');
    thisModal.find('#add_new').hide();
    production_entry_details_data = [];
    fillProductionEntryDetailTable();
});

/* ---------------------------------------------------------------------
 *  Reset / Add New
 * ------------------------------------------------------------------- */
jQuery('#resetbtn').on('click', function () {
    var formId = jQuery('#ProductionEntryModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonProductionEntryForm");
        if (form) { form.reset(); form.classList.remove('was-validated'); }
        jQuery('#ProductionEntryModal').find('#enclosure_id').val('').trigger('change.select2');
        jQuery('#total_sq_in').val('');
        production_entry_details_data = [];
        fillProductionEntryDetailTable();
        getLatestProductionEntryNo();
    } else {
        fetchAndFillProductionEntry(formId);
    }
});

jQuery('#ProductionEntryModal').on('click', '#add_new', function () {
    window.can_update_production_entry = true;
    jQuery('#ProductionEntryModal').find('#submitbtn').prop('disabled', false);
    jQuery('#ProductionEntryModal').find('#id').val('');
    document.getElementById("commonProductionEntryForm").reset();
    const form = document.getElementById("commonProductionEntryForm");
    if (form) { form.classList.remove('was-validated'); }
    jQuery('#ProductionEntryModal').find('#enclosure_id').val('').trigger('change.select2');
    jQuery('#total_sq_in').val('');
    production_entry_details_data = [];
    fillProductionEntryDetailTable();
    getLatestProductionEntryNo();
    jQuery('#ProductionEntryModal').find('#add_new').hide();
});

/* ---------------------------------------------------------------------
 *  Details Modal Management (Sub-modal)
 * ------------------------------------------------------------------- */
jQuery('#open_details_modal_btn').on('click', function () {
    let subForm = jQuery('#ProductionEntryDetailsForm');
    subForm.trigger('reset');
    subForm.find('#form_type').val('add');
    subForm.find('#production_entry_details_id').val(0);
    subForm.removeClass('was-validated');

    subForm.find('#ped_shift_id').val('').trigger('change.select2');
    subForm.find('#ped_source_id_fix').val('').trigger('change.select2');
    subForm.find('#ped_item_group_id').val('').trigger('change.select2');
    subForm.find('#ped_item_id').val('').trigger('change.select2');
    subForm.find('#ped_stock_qty').val('');
    subForm.find('#ped_stock_unit').text('');

    jQuery('#ProductionEntryDetailsModal').modal('show');
});

// Filter Item dropdown on changing Group
jQuery('#ped_item_group_id').on('change', function () {
    var groupId = jQuery(this).val();
    var itemSelect = jQuery('#ped_item_id');
    itemSelect.empty().append('<option value="">Select Item Name</option>');

    var filtered = all_items_cache.filter(function (item) {
        return item.item_group_id == groupId;
    });

    filtered.forEach(function (item) {
        itemSelect.append(`<option value="${item.id}" data-item_group_id="${item.item_group_id}" data-unit="SQIN" data-io_stock_qty="${item.io_stock_qty}">${item.item_name}</option>`);
    });

    itemSelect.val('').trigger('change.select2');
});

// Update stock qty and unit on item selection
jQuery('#ped_item_id').on('change', function () {
    var selectedOpt = jQuery(this).find('option:selected');
    if (selectedOpt.val() != '') {
        var stock = parseFloat(selectedOpt.data('io_stock_qty') || 0).toFixed(3);
        var unit = selectedOpt.data('unit') ?? '';
        jQuery('#ped_stock_qty').val(stock);
        jQuery('#ped_stock_unit').text(unit);
    } else {
        jQuery('#ped_stock_qty').val('');
        jQuery('#ped_stock_unit').text('');
    }
});

// Auto total sqin calculation inside details sub-form
jQuery('#ProductionEntryDetailsModal').on('input change', '#ped_production_sq_in, #ped_westage_in', function () {
    var prod = parseFloat(jQuery('#ped_production_sq_in').val()) || 0;
    var wastage = parseFloat(jQuery('#ped_westage_in').val()) || 0;
    var total = prod + wastage;
    jQuery('#ped_total_sq_in').val(total.toFixed(2));
});

jQuery('#ProductionEntryDetailsModal').on('hide.bs.modal', function () {
    this.dataset.customHideFocus = 'true';
    setTimeout(function () {
        jQuery('#commonProductionEntryForm #remark').focus();
    }, 100);
});

// Save details from sub-modal
jQuery('#add_details_btn').on('click', function () {
    var form = document.getElementById('ProductionEntryDetailsForm');

    if (!form.checkValidity()) {
        jQuery(form).addClass('was-validated');
        return;
    }

    var prod = parseFloat(jQuery('#ped_production_sq_in').val()) || 0;
    var wastage = parseFloat(jQuery('#ped_westage_in').val()) || 0;
    var total = prod + wastage;

    if (total <= 0) {
        toastr.error('Total Cons. Qty. Cannot Be Zero, Please Enter Production Qty. or Wastage Qty.');
        return;
    }

    var stockQty = parseFloat(jQuery('#ped_stock_qty').val()) || 0;
    if (total > stockQty) {
        toastr.error('Total Cons. Qty. Cannot Be Greater Than Stock Qty.');
        return;
    }

    // construct object
    let val = {};
    jQuery.each(jQuery(form).serializeArray(), function (i, f) {
        val[f.name] = f.value;
    });

    val.shift_name = jQuery('#ped_shift_id option:selected').text();
    val.source_id_fix = jQuery('#ped_source_id_fix option:selected').val();
    val.item_group = jQuery('#ped_item_group_id option:selected').text();
    val.item_name = jQuery('#ped_item_id option:selected').text();
    val.unit = jQuery('#ped_stock_unit').text();
    val.io_stock_qty = jQuery('#ped_stock_qty').val();

    if (val.form_type == "edit") {
        val.mode = (val.production_entry_details_id && val.production_entry_details_id != 0) ? "Update" : "Insert";
        production_entry_details_data[val.form_index] = {
            ...production_entry_details_data[val.form_index],
            ...val
        };
        toastSuccess("Record Updated.");
        jQuery('#ProductionEntryDetailsModal').modal('hide');
    } else {
        val.mode = "Insert";
        val.production_entry_details_id = 0;
        production_entry_details_data.push(val);
        toastSuccess("Record Inserted.");

        // Clear details subform for next entry
        form.reset();
        jQuery('#ProductionEntryDetailsForm').removeClass('was-validated');
        jQuery('#ProductionEntryDetailsForm').find('.js-example-basic-single').val('').trigger('change.select2');
        jQuery('#ped_stock_qty').val('');
        jQuery('#ped_stock_unit').text('');
        setTimeout(function () {
            let $select = jQuery('#ped_shift_id');
            $select.one('select2:opening', function (e) {
                e.preventDefault();
            });

            let sel = $select.next('.select2-container').find('.select2-selection');
            if (sel.length) {
                sel.attr('tabindex', 0).focus();
            }

            $select.select2('close');
        }, 150);
    }

    fillProductionEntryDetailTable();
});

// Edit Detail Action
function editProductionEntryDetails(btn) {
    var index = jQuery(btn).closest('tr').find('input[name="form_indx"]').val();
    var rowIdx = jQuery(btn).closest('tr').index();
    var d = production_entry_details_data[index];

    let subForm = jQuery('#ProductionEntryDetailsForm');
    subForm.trigger('reset');
    subForm.find('#form_type').val('edit');
    subForm.find('#form_index').val(index);
    subForm.find('#row_index').val(rowIdx);
    subForm.find('#production_entry_details_id').val(d.production_entry_details_id ?? 0);

    subForm.find('#ped_shift_id').val(d.shift_id).trigger('change.select2');
    subForm.find('#ped_source_id_fix').val(d.source_id_fix).trigger('change.select2');
    subForm.find('#ped_item_group_id').val(d.item_group_id).trigger('change.select2');

    // Trigger group change to set disabled options
    jQuery('#ped_item_group_id').trigger('change');

    subForm.find('#ped_item_id').val(d.item_id).trigger('change.select2');
    jQuery('#ped_item_id').trigger('change');

    subForm.find('#ped_production_sq_in').val(parseFloat(d.production_sq_in || 0).toFixed(2));
    subForm.find('#ped_retake_sq_in').val(parseFloat(d.retake_sq_in || 0).toFixed(2));
    subForm.find('#ped_westage_in').val(parseFloat(d.westage_in || 0).toFixed(2));
    subForm.find('#ped_total_sq_in').val(parseFloat(d.total_sq_in || 0).toFixed(2));

    jQuery('#ProductionEntryDetailsModal').modal('show');
}

// Remove Detail Action
function removeProductionEntryDetails(btn) {
    toastDelete("Do you want to delete this record?", function () {
        var index = jQuery(btn).closest('tr').find('input[name="form_indx"]').val();
        var d = production_entry_details_data[index];

        if (d.production_entry_details_id && d.production_entry_details_id != 0) {
            d.mode = 'Delete';
        } else {
            production_entry_details_data.splice(index, 1);
        }
        toastSuccess("Record Deleted.");
        fillProductionEntryDetailTable();
    });
}

/* ---------------------------------------------------------------------
 *  Main Form Submit
 * ------------------------------------------------------------------- */
jQuery('#commonProductionEntryForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#ProductionEntryModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;

    var dateValue = document.getElementById("production_entry_date").value.trim();
    if (!isValidDate(dateValue)) {
        toastr.error("Enter A Valid Date!");
        resetSubmitState();
        return;
    }
    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        resetSubmitState();
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        jQuery(form).addClass('was-validated');
        resetSubmitState();
        return;
    }

    let activeDetails = production_entry_details_data.filter(item => item.mode !== "Delete");
    if (activeDetails.length == 0) {
        toastr.error('Add At Least One Production Entry Detail.');
        resetSubmitState();
        return;
    }

    var formIdnew = jQuery('#ProductionEntryModal').find('#id').val();
    var formUrl = formIdnew != undefined && formIdnew != "" ? "update-production_entry" : "store-production_entry";

    var data = new FormData(form);
    data.append('production_entry_details_data', JSON.stringify(production_entry_details_data ?? []));
    data.append('_token', jQuery('meta[name="csrf-token"]').attr('content'));

    jQuery.ajax({
        type: 'POST',
        url: formUrl,
        data: data,
        contentType: false,
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                toastSuccess(data.response_message);
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                jQuery('#ProductionEntryModal').find('#submitbtn').prop('disabled', false);

                if (typeof table !== 'undefined') {
                    table.ajax.reload(null, false);
                }

                if (formUrl === "update-production_entry") {
                    jQuery('#ProductionEntryModal').modal('hide');
                } else {
                    // Reset main form
                    form.reset();
                    jQuery(form).removeClass('was-validated');
                    jQuery(form).find('.js-example-basic-single').val('').trigger('change.select2');

                    // Reset details grid
                    production_entry_details_data = [];
                    fillProductionEntryDetailTable();

                    // Load next Sr. No. and focus
                    getLatestProductionEntryNo();
                    setTimeout(function () {
                        jQuery('#production_entry_sequence').focus();
                    }, 200);
                }
            } else {
                toastr.error(data.response_message);
                resetSubmitState();
            }
        },
        error: function (xhr) {
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                let errors = xhr.responseJSON.errors;
                let errorMsg = '';
                jQuery.each(errors, function (key, value) { errorMsg += value + '\n'; });
                toastr.error(errorMsg);
            } else {
                toastr.error('Something went wrong. Please try again.');
            }
            resetSubmitState();
        }
    });
});

function resetSubmitState() {
    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
    if (window.can_update_production_entry !== false) {
        jQuery('#ProductionEntryModal').find('#submitbtn').prop('disabled', false);
    }
}

/* ---------------------------------------------------------------------
 *  Sequence duplicates check
 * ------------------------------------------------------------------- */
function getLatestProductionEntryNo() {
    jQuery.ajax({
        url: "get-latest-production_entry_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#production_entry_date').val(currentDate);
                jQuery('#production_entry_no').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#production_entry_sequence').val(data.number);
                jQuery('#production_entry_sequence').focus();
            }
        },
        error: function () {
            console.log('Failed To Get Latest Production Entry No.!');
        }
    });
}

jQuery('#commonProductionEntryForm').find('#production_entry_sequence').on('change', function () {
    checkSequence();
});

function checkSequence() {
    let thisForm = jQuery('#commonProductionEntryForm');
    if (thisForm.find('#production_entry_sequence').prop('readonly')) return;
    let val = thisForm.find('#production_entry_sequence').val();

    if (val > 0 == false) {
        toastr.error('Enter Valid Sr. No.');
        jQuery('#production_entry_sequence').focus();
        jQuery('#production_entry_sequence').val('');
    } else {
        jQuery('#ProductionEntryModal').find('#submitbtn').prop('disabled', true);
        var urL = "check-production_entry_number_duplication?for=add&production_entry_sequence=" + val;
        var formId = jQuery('#commonProductionEntryForm').find('input[name="id"]').val();
        if (formId !== undefined && formId != "") {
            urL = "check-production_entry_number_duplication?for=edit&production_entry_sequence=" + val + "&id=" + formId;
        }

        jQuery.ajax({
            url: urL,
            type: 'GET',
            headers: headerOpt,
            dataType: 'json',
            processData: false,
            success: function (data) {
                if (data.response_code == 0) {
                    toastr.error(data.response_message);
                    jQuery('#commonProductionEntryForm #production_entry_sequence').val('');
                    jQuery('#production_entry_sequence').focus();
                    if (window.can_update_production_entry !== false) {
                        jQuery('#ProductionEntryModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    jQuery('#commonProductionEntryForm #production_entry_no').val(data.latest_no);
                    jQuery('#commonProductionEntryForm #production_entry_sequence').val(val);
                    if (window.can_update_production_entry !== false) {
                        jQuery('#ProductionEntryModal').find('#submitbtn').prop('disabled', false);
                    }
                }
            },
            error: function () {
                if (window.can_update_production_entry !== false) {
                    jQuery('#ProductionEntryModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    }
}
