var inter_details_data = [];
var formId = jQuery('#commonInterLocationTransferForm').find('input[name="id"]').val();
let selectedRows = {};
$.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
    var row = $('#pendingInterLocationTransferPendingDataTable').DataTable().row(dataIndex).node();
    var checkbox = $(row).find("input[type='checkbox']");
    if (checkbox.is(':checked')) {
        return true;
    }
    return true;
});
// Edit purchase Indent row click
jQuery('#dyntable tbody').on('click', '.edit-inter_location_transfer', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#InterLocationTransferModal').find('#id').val(data["ilt_id"]);
    if (data && data["ilt_id"]) {
        fetchAndFillILT(data["ilt_id"]);
    }
});

// Function to fetch and fill purchase indent data
function fetchAndFillILT(id) {
    if (!id) return;
    jQuery('#InterLocationTransferModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-inter_location_transfer",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.ilt_data != null) {
                jQuery('#InterLocationTransferModal').find('#id').val(data.ilt_data.ilt_id != "" ? data.ilt_data.ilt_id : "");
                let url = checkFileRoute + "?id=" + data.ilt_data.ilt_id + "&name=" + data.ilt_data.pdf_name + "&type=inter_location_transfer";
                var dc_type = data.ilt_data.dc_type_id;
                jQuery('#InterLocationTransferModal').find('input[name*="dc_type_id"][value="' + dc_type + '"]').prop('checked', true).change();
                setRadioReadonly("input[name='dc_type_id']", true);

                jQuery('#preview_btn').attr('href', url).show();
                jQuery('#InterLocationTransferModal').find('#dc_number').val(data.ilt_data.dc_number != "" ? data.ilt_data.dc_number : "");
                jQuery('#InterLocationTransferModal').find('#dc_date').val(data.ilt_data.dc_date != "" ? data.ilt_data.dc_date : "");
                jQuery('#InterLocationTransferModal').find('#dc_sequence').val(data.ilt_data.dc_sequence != "" ? data.ilt_data.dc_sequence : "");

                jQuery('#InterLocationTransferModal').find('#to_location_id').val(data.ilt_data.to_location_id).trigger('change.select2');

                if (data.ilt_data.in_use == true) {
                    jQuery('#InterLocationTransferModal').find('#to_location_id').addClass('skip-tab');
                    setSelect2Readonly('#InterLocationTransferModal #to_location_id', true);
                } else {
                    jQuery('#InterLocationTransferModal').find('#to_location_id').removeClass('skip-tab');
                    setSelect2Readonly('#InterLocationTransferModal #to_location_id', false);
                }

                setSelect2Readonly('#InterLocationTransferModal #to_location_id', true);
                jQuery('#InterLocationTransferModal').find('#prepared_by_id').val(data.ilt_data.prepared_by_id).trigger('change.select2');

                jQuery('#InterLocationTransferModal').find('#mode_of_transport').val(data.ilt_data.mode_of_transport != "" ? data.ilt_data.mode_of_transport : "");
                jQuery('#InterLocationTransferModal').find('#transporter').val(data.ilt_data.transporter != "" ? data.ilt_data.transporter : "");
                jQuery('#InterLocationTransferModal').find('#vehicle_no').val(data.ilt_data.vehicle_no != "" ? data.ilt_data.vehicle_no : "");
                jQuery('#InterLocationTransferModal').find('#sp_note').val(data.ilt_data.sp_note != "" ? data.ilt_data.sp_note : "");

                if (data.inter_details_data != "" && data.inter_details_data.length > 0) {
                    inter_details_data = data.inter_details_data.map(item => {
                        item.mode = "Update";
                        return item;
                    });
                    fillInterLocationTransferDetailTable();
                }

                jQuery('#InterLocationTransferModal').find('#pending_btn').prop('disabled', true);
                jQuery('#InterLocationTransferModal').find('#submitbtn').prop('disabled', false);

                const form = document.getElementById("InterLocationTransferModal");
                if (form) form.classList.remove('was-validated');
                jQuery('#InterLocationTransferModal').find('#add_new').show();
                jQuery('#InterLocationTransferModal').find('#preview_btn').show();

                if (data.ilt_data.in_use == true) {
                    jQuery('#InterLocationTransferModal').find('#dc_sequence').prop('readonly', true);
                    let nextInput = jQuery('#InterLocationTransferModal').find('#dc_date');
                    if (nextInput.hasClass('trans-date-picker') || nextInput.hasClass('date-picker')) {
                        nextInput.one('focus', function () {
                            setTimeout(() => {
                                jQuery(this).datepicker('hide');
                            }, 50);
                        });
                    }
                    nextInput.focus();
                } else {
                    jQuery('#InterLocationTransferModal').find('#dc_sequence').prop('readonly', false);
                    jQuery('#InterLocationTransferModal').find('#dc_sequence').focus();
                }

            } else {
                console.log(data.response_message);
            }
        },
        error: function (jqXHR) {
            if (jqXHR.status == 401) {
                console.log(jqXHR.statusText);
            } else {
                console.log('Something went wrong!');
            }
            console.log(JSON.parse(jqXHR.responseText));
        },
        complete: function () {
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            const input = document.getElementById('dc_sequence');
            input?.focus();
        }
    });
}

jQuery('#resetbtn').on('click', function () {
    inter_details_data = [];
    selectedRows = {};


    var formId = jQuery('#InterLocationTransferModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonInterLocationTransferForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        jQuery('#commonInterLocationTransferForm').find('#prepared_by_id').val(loginUserId).trigger("change.select2");
        getLatestInterLocationTransferNo();
        resetFieds();
    } else {
        fetchAndFillILT(formId);
    }
});

var allItemOptionsHtml = '';
function updateItemDropdownForDCType() {
    var dc_type_id = jQuery('#commonInterLocationTransferForm').find('input[name*="dc_type_id"]:checked').val();
    var $itemId = jQuery('#InterLocationTransferDetailsModal #item_id');

    if (!allItemOptionsHtml && $itemId.find('option').length > 1) {
        allItemOptionsHtml = $itemId.html();
    }

    var currentVal = $itemId.val();

    if (allItemOptionsHtml) {
        $itemId.html(allItemOptionsHtml);
    }

    if (dc_type_id === 'SQIN from Prod. Area') {
        $itemId.find('option').each(function () {
            var $opt = jQuery(this);
            var val = $opt.val();
            if (!val) return;
            var item_type = $opt.data('item_type');
            var main_group = $opt.data('main_group');
            var isFilm = (item_type === 'film' || main_group === 'Industrial X-Ray Films');
            if (!isFilm) {
                $opt.remove();
            }
        });
    }

    if (currentVal && $itemId.find('option[value="' + currentVal + '"]').length > 0) {
        $itemId.val(currentVal);
    } else {
        $itemId.val('');
    }

    if ($itemId.data('select2')) {
        $itemId.trigger('change.select2');
    }
}

jQuery('#InterLocationTransferDetailsModal').on('show.bs.modal', function () {
    updateItemDropdownForDCType();
    let thisModal = jQuery('#InterLocationTransferDetailsModal');
    var form_type = thisModal.find("#form_type").val();
    var isEdit = (form_type == "edit");
    jQuery('#item_id').trigger('change', [isEdit]);
    var DCTypeId = jQuery('#commonInterLocationTransferForm').find('input[name*="dc_type_id"]:checked').val();
    var details_id = thisModal.find("#inter_location_transfer_details_id").val();
    if (DCTypeId == "From Indent" || (form_type == "edit" && details_id != '0')) {
        jQuery('#item_id').addClass('skip-tab');
        setSelect2Readonly('#item_id', true);
    } else {
        jQuery('#item_id').removeClass('skip-tab');
        setSelect2Readonly('#item_id', false);
    }

    if (form_type == "edit" && details_id != '0') {
        jQuery('#sr_table_pk_id').addClass('skip-tab');
        setSelect2Readonly('#sr_table_pk_id', true);
    } else {
        jQuery('#sr_table_pk_id').removeClass('skip-tab');
        setSelect2Readonly('#sr_table_pk_id', false);
    }

    var mode = jQuery("#InterLocationTransferDetailsModal #form_type").val();
    var pid_id = jQuery("#InterLocationTransferDetailsModal #pid_id").val();
    if (mode == "add" && pid_id == "0") {
        jQuery('#InterLocationTransferDetailsModal #dc_qty').removeAttr('min');
    }





});


jQuery('#InterLocationTransferModal').on('show.bs.modal', function () {
    jQuery('#InterLocationTransferModal').find('#dc_sequence').prop('readonly', false);
    var hasAccess = jQuery('#commonInterLocationTransferForm').find('#has_access').val();
    jQuery('#commonInterLocationTransferForm').find('#prepared_by_id').val(loginUserId).trigger("change.select2");
    var formId = jQuery('#commonInterLocationTransferForm').find('input[name="id"]').val();
    jQuery('#commonInterLocationTransferForm').find('.add_detail').prop('disabled', true);

    if (formId == "" || formId == undefined) {
        inter_details_data = [];
        jQuery('#InterLocationTransferDetailTable tbody').empty();
        jQuery('#commonInterLocationTransferForm').find('#prepared_by_id').val(loginUserId).trigger("change.select2");
        getLatestInterLocationTransferNo();
        jQuery("#InterLocationTransferDetailsModal #to_location_id").removeClass('skip-tab');
        setSelect2Readonly('#InterLocationTransferModal #to_location_id', false);
        // getPendingSuppliersForGrn();
    }
    const input = document.getElementById('dc_sequence');
    input?.focus();

});



jQuery('#InterLocationTransferModal').on('hide.bs.modal', function (e) {
    jQuery('#InterLocationTransferModal').find('#dc_sequence').prop('readonly', false);
    jQuery('#InterLocationTransferDetailTable tbody').empty();
    let thisModal = jQuery('#InterLocationTransferModal');
    thisModal.find("#id").val("");
    var thisForm = jQuery('#InterLocationTransferDetailsModal');
    thisForm.find("#pid_id").val(0);
    inter_details_data = [];
    resetFieds();
    jQuery('#InterLocationTransferModal').find('#add_new').hide();
    jQuery('#InterLocationTransferModal').find('#preview_btn').hide();
    var dc_type = jQuery('#commonInterLocationTransferForm').find('input[name*="dc_type_id"]:checked').val();
    jQuery('#InterLocationTransferDetailsModal').find('input[name*="dc_type_id"][value="' + dc_type + '"]').prop('checked', true).change();
    jQuery("#InterLocationTransferModal #to_location_id").removeClass('skip-tab');
    setSelect2Readonly('#InterLocationTransferModal #to_location_id', false);
});

jQuery('#InterLocationTransferModal').on('click', '#add_new', function () {

    document.getElementById("commonInterLocationTransferForm").reset();
    const form = document.getElementById("commonInterLocationTransferForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    resetFieds();
    jQuery('#dc_sequence').prop('readonly', false).focus();
    getLatestInterLocationTransferNo();
    jQuery('#commonInterLocationTransferForm').find('input[name="id"]').val('');

    jQuery('#InterLocationTransferModal').find('#add_new').hide();
    jQuery('#InterLocationTransferModal').find('#preview_btn').hide();
    jQuery('#commonInterLocationTransferForm').find('#prepared_by_id').val(loginUserId).trigger("change.select2");
});



jQuery('#InterLocationTransferDetailsModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#InterLocationTransferDetailsModal');
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    thisModal.find("#inter_location_transfer_details_id").val(0);
    jQuery(this).find('#item_id option.temp-item').remove();
    jQuery(this).find('#sr_table_pk_id option.temp-sr_table_pk_id').remove();
    jQuery('#InterLocationTransferDetailsForm').trigger("reset");
    clearMovement();
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('mode_of_transport');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }

    jQuery('#InterLocationTransferDetailsForm #dc_qty').removeAttr('min');
    setSelect2Readonly('#InterLocationTransferDetailsForm #item_id', false);
    jQuery('#InterLocationTransferDetailsForm #item_id').removeClass('skip-tab');
    setSelect2Readonly('#InterLocationTransferDetailsForm #sr_table_pk_id', false);
    jQuery('#InterLocationTransferDetailsForm #sr_table_pk_id').removeClass('skip-tab');
});


jQuery('#checkall-po_data').click(function () {
    if (jQuery(this).is(':checked')) {
        jQuery("#pendingInterLocationTransferPendingDataTable").find("[id^=']']:not(.in-use)").prop('checked', true).trigger('change');
        jQuery("#pendingInterLocationTransferPendingDataTable").find("[id^='pid_ids_']").prop('checked', true).trigger('change');
    } else {
        jQuery("#pendingInterLocationTransferPendingDataTable").find("[id^='pid_ids_']:not(.in-use)").prop('checked', false).trigger('change');
        jQuery("#pendingInterLocationTransferPendingDataTable").find("[id^='pid_ids_']").prop('checked', false).trigger('change');
    }

});
function checkesCheckboxFrist($this) {
    var $row = jQuery($this).closest('tr');
    var pid_id = jQuery($this).val();
    if (jQuery($this).is(':checked')) {
        selectedRows[pid_id] = $row;
    } else {
        delete selectedRows[pid_id];
    }
}

jQuery('input[name*="dc_type_id"]').on('change', function () {
    if (jQuery(this).val() == 'From Indent') {
        jQuery('.toggleButton').prop('disabled', true);
        fillPendingILT();
    } else {
        jQuery('#pending_btn').prop('disabled', true);
        jQuery('.toggleButton').prop('disabled', false);
    }
    updateItemDropdownForDCType();
    if (typeof fillInterLocationTransferTable === 'function' && typeof inter_details_data !== 'undefined' && inter_details_data.length > 0) {
        fillInterLocationTransferTable();
    }
});
jQuery('#item_id').on('change', function (e, isEdit = false) {
    let selected = jQuery(this).find('option:selected');
    let main_group = selected.data('main_group');
    let item_group = selected.data('item_group');
    let io_stock_qty = selected.data('io_stock_qty');
    let stock_sq_in = selected.data('stock_sq_in');
    let stock_rate_unit = selected.data('stock_rate_unit');
    let unit = selected.data('unit');
    let item_id = selected.val();

    var dc_type_id = jQuery('#commonInterLocationTransferForm').find('input[name*="dc_type_id"]:checked').val();
    if (dc_type_id == 'SQIN from Prod. Area') {
        io_stock_qty = stock_sq_in;
        unit = 'SQIN';
    }

    if (selected.val() != "") {
        jQuery('#item_group').val(item_group);
        jQuery('#main_group').val(main_group);
        jQuery('#stock_rate_unit').val(stock_rate_unit);
        jQuery('#io_stock_qty').val(io_stock_qty ? parseFloat(io_stock_qty).toFixed(3) : parseFloat(0).toFixed(3));
        jQuery('#io_stock_qty_unit').text(unit).addClass('ms-1');
        jQuery('#pend_pi_qty_unit').text(unit).addClass('ms-1');
        jQuery('#dc_qty_unit').text(unit).addClass('ms-1');

        if (main_group == 'Industrial X-Ray Films' || main_group == 'General') {
            setTimeout(() => {
                jQuery('#InterLocationTransferDetailsForm #sr_table_pk_id').addClass('skip-tab');
                setSelect2Readonly("#InterLocationTransferDetailsForm #sr_table_pk_id", true);
                jQuery('#InterLocationTransferDetailsForm #sr_table_pk_id').empty().append('<option value="">Select Sr. No.</option>');
                jQuery('#InterLocationTransferDetailsForm #sr_table_pk_id').addClass('skip-tab');
                var dcInput = jQuery('#InterLocationTransferDetailsForm #dc_qty');
                dcInput.prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');

            }, 50);

        } else {

            jQuery('#InterLocationTransferDetailsForm #sr_table_pk_id').removeClass('skip-tab');
            setSelect2Readonly("#InterLocationTransferDetailsForm #sr_table_pk_id", false);
            if (!isEdit) {
                getSrNo(main_group, item_id);
            }
        }

        if (main_group == 'Industrial X-Ray Films' || main_group == 'Material – MPT' || main_group == 'Chemical – DPT') {
            jQuery('#dc_qty').removeClass('isNumberKey').addClass('isNumberKeyNotDot');
            jQuery('#dc_qty').attr('onblur', 'formatPoints(this, 0)');
            if (jQuery('#dc_qty').val() !== '') {
                jQuery('#dc_qty').val(parseInt(jQuery('#dc_qty').val()) || '');
            }
        } else {
            jQuery('#dc_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
            jQuery('#dc_qty').attr('onblur', 'formatPoints(this, 3)');
        }

        if (main_group == 'IRED') {
            jQuery("#InterLocationTransferDetailsForm #aerb_no").attr("readonly", false).prop('required', true).removeClass('skip-tab').removeAttr('tabindex');
            jQuery("#InterLocationTransferDetailsForm #application_no").attr("readonly", false).prop("required", true).removeClass('skip-tab').removeAttr('tabindex');
            jQuery("#InterLocationTransferDetailsForm #movement_approval").attr("readonly", false).prop("disabled", false).removeClass('skip-tab').removeAttr('tabindex');
            toggleRequired();
            jQuery("#InterLocationTransferDetailsForm #validity").attr("readonly", false).removeClass('skip-tab').removeAttr('tabindex');
            jQuery('#InterLocationTransferDetailsForm').find('#validity').datepicker('enable');

        } else {
            jQuery("#InterLocationTransferDetailsForm #aerb_no").attr("readonly", true).prop('required', false).addClass('skip-tab').attr('tabindex', '-1');
            jQuery("#InterLocationTransferDetailsForm #application_no").attr("readonly", true).prop("required", false).addClass('skip-tab').attr('tabindex', '-1');
            jQuery("#InterLocationTransferDetailsForm #movement_approval").attr("readonly", true).prop("disabled", true).prop("required", false).addClass('skip-tab').attr('tabindex', '-1');
            jQuery("#InterLocationTransferDetailsForm #validity").attr("readonly", true).addClass('skip-tab').attr('tabindex', '-1');
            jQuery('#InterLocationTransferDetailsForm').find('#validity').datepicker('disable');
        }

    } else {
        jQuery('#item_group').val('');
        jQuery('#main_group').val('');
        jQuery('#io_stock_qty').val('');
        jQuery('#io_stock_qty_unit').text('').removeClass('ms-1');
        jQuery('#pend_pi_qty_unit').text('').removeClass('ms-1');
        jQuery('#dc_qty_unit').text('').removeClass('ms-1');
        jQuery('#InterLocationTransferDetailsForm #sr_table_pk_id').empty().append('<option value="">Select Sr. No.</option>');
        jQuery('#InterLocationTransferDetailsForm #sr_table_pk_id').addClass('skip-tab');
        setSelect2Readonly("#InterLocationTransferDetailsForm #sr_table_pk_id", true);
        jQuery('#stock_rate_unit').val('');
        jQuery('#io_stock_qty').val(parseFloat(0).toFixed(3));
        jQuery('#dc_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        jQuery('#dc_qty').attr('onblur', 'formatPoints(this, 3)');

        jQuery("#InterLocationTransferDetailsForm #aerb_no").attr("readonly", true).prop("required", false).addClass('skip-tab').attr('tabindex', '-1');
        jQuery("#InterLocationTransferDetailsForm #application_no").attr("readonly", true).prop("required", false).addClass('skip-tab').attr('tabindex', '-1');
        jQuery("#InterLocationTransferDetailsForm #movement_approval").attr("readonly", true).prop("disabled", true).prop("required", false).addClass('skip-tab').attr('tabindex', '-1');
        jQuery("#InterLocationTransferDetailsForm #validity").attr("readonly", true).addClass('skip-tab').attr('tabindex', '-1');
        jQuery('#InterLocationTransferDetailsForm').find('#validity').datepicker('disable');

    }

});
function getSrNo(main_group, item_id, selected_sr = '') {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    return jQuery.ajax({
        url: "get-sr_no?main_group=" + main_group + "&item_id=" + item_id,
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            let options = `<option value="">Select Sr. No.</option>`;
            if (data.response_code == 1) {
                if (data.sr_no_data.length > 0) {
                    for (let idx in data.sr_no_data) {
                        options += `<option data-sr_table_unique_id="${data.sr_no_data[idx].sr_table_unique_id}" data-stock_qty="${data.sr_no_data[idx].sr_qty || 0}" value="${data.sr_no_data[idx].sr_table_pk_id}">${data.sr_no_data[idx].name_for_display}</option>`;
                    }
                }
                let $dropdown = jQuery('#InterLocationTransferDetailsForm #sr_table_pk_id');
                $dropdown.empty().append(options);

                // Set value AFTER options loaded
                if (selected_sr) {
                    $dropdown.val(selected_sr);
                }
                $dropdown.trigger('change.select2');
                let selectedOption = $dropdown.find('option:selected');
                jQuery('#sr_table_unique_id').val(selectedOption.data('sr_table_unique_id') || '');
            } else {
                jQuery('#InterLocationTransferDetailsForm #sr_table_pk_id').empty().append(options).trigger('change.select2');
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('Field To Get Sr. No.!')
        },
        complete: function () {
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        }
    });
}
jQuery('#InterLocationTransferDetailsForm #sr_table_pk_id').on('change', function () {
    let selectedOption = jQuery(this).find('option:selected');
    let sr_table_unique_id = selectedOption.data('sr_table_unique_id') || '';

    jQuery('#sr_table_unique_id').val(sr_table_unique_id);
    const dcInput = jQuery('#InterLocationTransferDetailsForm #dc_qty');

    if (selectedOption.val() !== "") {
        if (['dpt_chemical', 'mpt_material', 'dpt_material'].includes(sr_table_unique_id)) {
            dcInput.prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
            let batchStock = selectedOption.data('stock_qty') || 0;
            jQuery('#io_stock_qty').val(parseFloat(batchStock).toFixed(3));
            let formType = jQuery('#InterLocationTransferDetailsForm #form_type').val();
            if (formType === 'add') {
                dcInput.val(parseFloat(batchStock).toFixed(3)).trigger('change');
            }
            setTimeout(function () {
                dcInput.focus();
            }, 100);
        } else {
            dcInput.val((1).toFixed(3)).trigger('change').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
        }
    } else {
        dcInput.val('').trigger('change').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
        let itemSelected = jQuery('#item_id').find('option:selected');
        let io_stock_qty = itemSelected.data('io_stock_qty') || 0;
        jQuery('#io_stock_qty').val(parseFloat(io_stock_qty).toFixed(3));
    }
});
jQuery('#InterLocationTransferDetailsForm #dc_qty').on('change', function () {
    let dc_qty = jQuery(this).val() ? parseFloat(jQuery(this).val()) : 0;
    let stock_rate_unit = jQuery('#InterLocationTransferDetailsForm #stock_rate_unit').val();
    var amount = 0;
    if (stock_rate_unit && !isNaN(stock_rate_unit)) {
        amount = dc_qty * parseFloat(stock_rate_unit);
    }
    jQuery('#InterLocationTransferDetailsForm #amount').val(amount.toFixed(3));
});
// get the latest number
function getLatestInterLocationTransferNo() {
    jQuery.ajax({
        url: "get-latest_inter_location_transfer_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#dc_number').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#dc_sequence').val(data.number);
                jQuery('#dc_date').val(currentDate);
                jQuery("#InterLocationTransferDetailsModal #to_location_id").removeClass('skip-tab');
                setSelect2Readonly('#InterLocationTransferModal #to_location_id', false);
            } else {
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            jQuery('#dc_number').removeClass('file-loader');
            console.log('Field To Get Latest GRN No.!')
        }
    });
}
// check sequence number duplication
jQuery('#commonInterLocationTransferForm').find('#dc_sequence').on('change', function () {

    checkSequence();
});
function checkSequence() {

    let thisForm = jQuery('#commonInterLocationTransferForm');
    let val = thisForm.find('#dc_sequence').val();

    if (val != "") {

        if (val > 0 == false) {
            toastr.error('Enter Valid Inter Location Transfer No.');
            jQuery('#dc_sequence').parent().parent().parent('div.control-group').addClass('error');
            jQuery('#dc_sequence').focus();
            jQuery('#dc_sequence').val('');

        } else {
            jQuery('#dc_sequence').addClass('file-loader');
            jQuery('#dc_sequence').parent().parent().parent('div.control-group').removeClass('error');

            var urL = "check-inter_location_transfer_number_duplication?for=add&dc_sequence=" + val;

            var formId = jQuery('#commonInterLocationTransferForm').find('input[name="id"]').val();

            if (formId !== undefined) { //if form is edit
                urL = "check-inter_location_transfer_number_duplication?for=edit&dc_sequence=" + val + "&id=" + formId;
            }

            jQuery.ajax({
                url: urL,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    jQuery('#dc_sequence').removeClass('file-loader');
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        jQuery('#commonInterLocationTransferForm #dc_sequence').val('');
                        const input = document.getElementById('dc_sequence'); input?.focus();
                    } else {
                        jQuery('#commonInterLocationTransferForm #dc_number').val(data.latest_no);
                        jQuery('#commonInterLocationTransferForm #dc_sequence').val(val);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#dc_sequence').removeClass('file-loader');
                    var errMessage = JSON.parse(jqXHR.responseText);
                    if (errMessage.errors) {
                        validator.showErrors(errMessage.errors);
                    } else if (jqXHR.status == 401) {
                        toastr.error(jqXHR.statusText);
                    } else {
                        toastr.error('Something went wrong!');
                        console.log(JSON.parse(jqXHR.responseText));
                    }

                }
            });
        }
    } else {
        jQuery('#dc_number').val('');
        jQuery('#dc_sequence').val('');
    }

}

// Purchase Order Details Form Submit Start
$('#InterLocationTransferDetailsForm').on('submit', function (e) {

    e.preventDefault();
    let form = this;

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    var data = new FormData(document.getElementById('InterLocationTransferDetailsForm'));
    var formValue = Object.fromEntries(data.entries());
    var thisModal = jQuery('#InterLocationTransferDetailsModal');

    var main_group = formValue.main_group ? formValue.main_group : '';
    var sr_table_pk_id = formValue.sr_table_pk_id ? formValue.sr_table_pk_id : '';

    if (!["Industrial X-Ray Films", "General"].includes(main_group)) {
        if (sr_table_pk_id == "") {
            toastr.error('Select Sr. No.');
            return;
        }

    }

    var dc_qty = formValue.dc_qty ? parseFloat(formValue.dc_qty) : 0;
    // var pend_pi_qty = formValue.pend_pi_qty ? parseFloat(formValue.pend_pi_qty) : 0;
    let ioStock = parseFloat(formValue.io_stock_qty || 0);
    let pendingPI = parseFloat(formValue.pend_pi_qty || 0);
    let MaxQty = Math.min(ioStock, pendingPI);
    let reason = "";
    if (ioStock <= pendingPI) {
        reason = "Stock";
    } else {
        reason = "Pend. PI Qty.";
    }
    var dc_type_id = jQuery('#commonInterLocationTransferForm').find('input[name*="dc_type_id"]:checked').val();
    if (dc_qty > MaxQty && dc_type_id == "From Indent") {
        toastr.error(`DC Qty. Cannot Be Greater Than ${reason} ${MaxQty.toFixed(3)}`);
        return;
    };
    if (dc_qty > ioStock && dc_type_id != "From Indent") {
        toastr.error(`DC Qty. Cannot Be Greater Than Stock ${ioStock.toFixed(3)}`);
        return;
    }
    var used_qty = parseFloat(jQuery('#dc_qty').attr('min')) || 0;
    if (used_qty > dc_qty) {
        toastr.error('DC Qty. Cannot Be Less Than ' + used_qty.toFixed(3));
        return;
    }

    if (dc_qty < 0.001) {
        toastr.error('Enter DC Qty. greater than 0.001.');
        return;
    }

    if (formValue.item_id.trim()) {


        function parseDate(dateStr) {
            if (!dateStr) return null;
            var parts = dateStr.split("/");
            return new Date(parts[2], parts[1] - 1, parts[0]);
        }
        // var PODateValue = jQuery('#po_date').val().trim();
        // var selectedDate = parseDate(formValue.pod_del_date);
        // var poDate = parseDate(PODateValue);

        // if (poDate && selectedDate < poDate) {
        //     toastr.error('Del. Date Must Be Greater Than PO Date');
        //     return;
        // }
        var noDuplicate = true;
        var sr_table_pk_id = formValue.sr_table_pk_id ? formValue.sr_table_pk_id : null;
        var item_id = formValue.item_id ? formValue.item_id : null;

        var currentIndex = formValue.form_type === "edit" ? formValue.form_index : null;

        // var validData = inter_details_data.filter(item => item.mode !== "Delete");

        jQuery.each(inter_details_data, function (index, item) {
            if (item.mode === "Delete") return true;
            if (item.sr_table_pk_id == sr_table_pk_id && item.item_id == item_id) {
                if (currentIndex === null || index != currentIndex) {
                    noDuplicate = false;
                    return false;
                }
            }
        });

        if (!noDuplicate) {
            toastr.error('Duplicate Sr. No. Found.');
            return;
        }

        if (noDuplicate) {
            var dc_type_id = jQuery('#commonInterLocationTransferForm').find('input[name*="dc_type_id"]:checked').val();
            var unit = (dc_type_id === 'SQIN from Prod. Area') ? 'SQIN' : thisModal.find('#io_stock_qty_unit').text();
            var item_id = formValue.item_id ? formValue.item_id : '';
            var item_name = formValue.item_name ? formValue.item_name : thisModal.find('#item_id option[value="' + item_id + '"]').text();
            var pi_no = formValue.pi_no ? zeroToEmpty(formValue.pi_no) : '';
            var pi_date = formValue.pi_date ? formValue.pi_date : '';
            var item_group = formValue.item_group != "" ? formValue.item_group : '';
            var main_group = formValue.main_group != "" ? formValue.main_group : '';
            var io_stock_qty = parseFloat(formValue.io_stock_qty || 0).toFixed(3);
            var pend_pi_qty = formValue.pend_pi_qty != "" ? formValue.pend_pi_qty : '';
            var dc_qty = formValue.dc_qty ? parseFloat(formValue.dc_qty).toFixed(3) : parseFloat(0).toFixed(3);
            var pod_unit = formValue.pod_unit != "" ? formValue.pod_unit : '';
            var sr_table_pk_id = formValue.sr_table_pk_id ? formValue.sr_table_pk_id : '';
            var name_for_display = sr_table_pk_id != "" ? formValue.name_for_display ? formValue.name_for_display : thisModal.find('#sr_table_pk_id option:selected').text() : "";

            // var pod_rate_unit = formValue.pod_rate_unit ? parseFloat(formValue.pod_rate_unit).toFixed(2) : parseFloat(0).toFixed(2);
            // var pod_amount = formValue.pod_amount ? parseFloat(formValue.pod_amount).toFixed(2) : parseFloat(0).toFixed(2);
            // var pod_del_date = formValue.pod_del_date != "" ? formValue.pod_del_date : "";

            var remark = formValue.remark ? formValue.remark : "";
            formValue.name_for_display = sr_table_pk_id != "" ? name_for_display : "";
            formValue.unit = unit;
            var aerb_no = formValue.aerb_no ? formValue.aerb_no : "";
            var application_no = formValue.application_no ? formValue.application_no : "";
            var validity = formValue.validity ? formValue.validity : "";
            var movement_approval_doc = formValue.movement_approval_doc ? formValue.movement_approval_doc : "";
            if (item_id != "") {
                if (formValue.form_type == "edit") {
                    formValue.item_name = item_name;
                    formValue.mode = formValue.inter_location_transfer_details_id == 0 ? "Insert" : "Update";
                    // inter_details_data[formValue.form_index] = formValue;
                    inter_details_data[formValue.form_index] = {
                        ...inter_details_data[formValue.form_index],
                        ...formValue
                    };
                    let tblHtml = ``;
                    tblHtml += `<td>`;
                    var in_use = inter_details_data[formValue.form_index].in_use == true ? true : false;
                    tblHtml += in_use == true ? DetailsActionDropdown('editInterLocationTransferDetails') : DetailsActionDropdown('editInterLocationTransferDetails', 'removeInterLocationTransferDetails');
                    // tblHtml += DetailsActionDropdown('editInterLocationTransferDetails', 'removeInterLocationTransferDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                    </td>`;
                    tblHtml += `<td>${pi_no}</td>`;
                    tblHtml += `<td>${pi_date}</td>`;
                    tblHtml += `<td>${item_name}</td>`;
                    tblHtml += `<td>${item_group}</td>`;
                    tblHtml += `<td>${main_group}</td>`;
                    tblHtml += `<td>${name_for_display}</td>`;
                    tblHtml += `<td>${io_stock_qty}</td>`;
                    tblHtml += `<td>${pend_pi_qty}</td>`;
                    tblHtml += `<td>${dc_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    // tblHtml += `<td>${pod_rate_unit}</td>`;
                    // tblHtml += `<td>${pod_amount}</td>`;
                    // tblHtml += `<td>${pod_del_date}</td>`;
                    tblHtml += `<td>${aerb_no}</td>`;
                    tblHtml += `<td>${application_no}</td>`;
                    if (movement_approval_doc != "") {
                        let fullImagePath = uploadURL + movement_approval_doc;
                        tblHtml += `<td  style="text-align:center; vertical-align:middle;">
                            <a target="_blank" type="button" href="${fullImagePath}" title="View" style="font-size:18px; display:inline-block;">
                                <i class="ri-eye-fill"></i>
                            </a>
                            <input type='hidden' name='movement_approval_doc[]' value="${movement_approval_doc}"/>
                        </td>`;
                    } else {
                        tblHtml += `<td style="text-align:center; vertical-align:middle;">
                            <input type='hidden' name='movement_approval_doc[]' value=""/>
                        </td>`;
                    }
                    tblHtml += `<td>${validity}</td>`;
                    tblHtml += `<td>${remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#InterLocationTransferDetailTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
                    toastSuccess("Record Updated.");
                } else {
                    formValue.mode = "Insert";
                    formValue.inter_location_transfer_details_id = 0;
                    inter_details_data.push(formValue)
                    if (inter_details_data.length > 0) {
                        setRadioReadonly("input[name='dc_type_id']", true);
                    }
                    let formIndx = inter_details_data.indexOf(formValue);
                    if (jQuery('#InterLocationTransferDetailTable tbody').find('#noDetails').length > 0) {
                        jQuery('#InterLocationTransferDetailTable tbody').empty();
                    }

                    let tblHtml = `<tr>`;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editInterLocationTransferDetails', 'removeInterLocationTransferDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
                    </td>`;
                    tblHtml += `<td>${pi_no}</td>`;
                    tblHtml += `<td>${pi_date}</td>`;
                    tblHtml += `<td>${item_name}</td>`;
                    tblHtml += `<td>${item_group}</td>`;
                    tblHtml += `<td>${main_group}</td>`;
                    tblHtml += `<td>${name_for_display}</td>`;
                    tblHtml += `<td>${io_stock_qty}</td>`;
                    tblHtml += `<td>${pend_pi_qty}</td>`;
                    tblHtml += `<td>${dc_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${aerb_no}</td>`;
                    tblHtml += `<td>${application_no}</td>`;
                    if (movement_approval_doc != "") {
                        let fullImagePath = uploadURL + movement_approval_doc;
                        tblHtml += `<td style="text-align:center; vertical-align:middle;">
                            <a target="_blank" href="${fullImagePath}" title="View" style="font-size:18px; display:inline-block;">
                                <i class="ri-eye-fill"></i>
                            </a>
                            <input type='hidden' name='movement_approval_doc[]' value="${movement_approval_doc}"/>
                        </td>`;
                    } else {
                        tblHtml += `<td style="text-align:center; vertical-align:middle;">
                            <input type='hidden' name='movement_approval_doc[]' value=""/>
                        </td>`;
                    }
                    tblHtml += `<td>${validity}</td>`;
                    // tblHtml += `<td>${pod_rate_unit}</td>`;
                    // tblHtml += `<td>${pod_amount}</td>`;
                    // tblHtml += `<td>${pod_del_date}</td>`;
                    tblHtml += `<td>${remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#InterLocationTransferDetailTable tbody').append(tblHtml);
                    toastSuccess("Record Inserted.");
                }
            }
            if (formValue.form_type == "edit") {
                thisModal.modal('hide');
            } else {
                let formElement = document.getElementById('InterLocationTransferDetailsForm');
                formElement.reset();
                clearMovement();
                jQuery('#item_id').val('').trigger('change');
                jQuery('#pod_stock').val('');
                jQuery('#dc_qty').val('');
                jQuery('#pod_unit').val('');
                jQuery('#pod_rate_unit').val('');
                jQuery('#pod_amount').val('');
                jQuery('#pod_del_date').val('');
                jQuery('#remark').val('');
                jQuery('#io_stock_qty_unit').text('').removeClass('ms-1');
                jQuery('#pend_pi_qty_unit').text('').removeClass('ms-1');
                jQuery('#dc_qty_unit').text('').removeClass('ms-1');
                var options = `<option value="">Select Sr. No.</option>`;
                jQuery('#InterLocationTransferDetailsForm #sr_table_pk_id').empty().append(options);
                setTimeout(function () {

                    let $select = jQuery('#InterLocationTransferDetailsForm #item_id');
                    $select.one('select2:opening', function (e) {
                        e.preventDefault();
                    });

                    let sel = $select.next('.select2-container').find('.select2-selection');
                    if (sel.length) {
                        sel.attr('tabindex', 0).focus();
                    }

                    $select.select2('close');
                }, 150);

                setTimeout(function () {
                    $(formElement).removeClass('was-validated');
                    $(formElement).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                    thisModal.find('.error').removeClass('error');
                }, 150);
            }
        }
    } else {
        toastr.error('Select Item Name');
    }
});


function editInterLocationTransferDetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();
    fillInterLocationTransferDetailsForm(formIndx, rawIndx);
}

//  details form edit
function fillInterLocationTransferDetailsForm(formIndx, rawIndx) {
    let thisForm = jQuery('#InterLocationTransferDetailsModal');
    var DCTypeId = jQuery('#commonInterLocationTransferForm').find('input[name*="dc_type_id"]:checked').val();

    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    thisForm.find('#item_id option.temp-item').remove();
    thisForm.find('#sr_table_pk_id option.temp-sr_table_pk_id').remove();
    var frmData = inter_details_data[formIndx];


    let itemId = frmData.item_id;
    let itemText = frmData.item_name;
    let sr_table_pk_id = frmData.sr_table_pk_id;
    let name_for_display = frmData.name_for_display;

    if (thisForm.find("#item_id option[value='" + itemId + "']").length === 0) {
        thisForm.find("#item_id").append(
            `<option  class="temp-item" value="${itemId}" data-main_group="${frmData.main_group}" data-item_group="${frmData.item_group}" data-io_stock_qty="${frmData.io_stock_qty}" selected>${itemText}</option>`
        );
    }


    // check if option exists

    if (frmData.in_use === true) {
        let usedQty = parseFloat(frmData.used_qty) || 0;
        thisForm.find("#dc_qty").attr('min', usedQty.toFixed(3));

    } else {
        thisForm.find("#dc_qty").attr('min', 0);
    }

    thisForm.find("#inter_location_transfer_details_id").val(frmData.inter_location_transfer_details_id);
    thisForm.find("#inter_location_transfer_id").val(frmData.inter_location_transfer_id != "" ? frmData.inter_location_transfer_id : "");
    thisForm.find('#pi_no').val(frmData.pi_no ? zeroToEmpty(frmData.pi_no) : "");
    thisForm.find('#pi_date').val(frmData.pi_date ? frmData.pi_date : "");
    thisForm.find("#item_id").val(zeroToEmpty(frmData.item_id)).trigger('change', [true]);

    thisForm.find("#aerb_no").val(frmData.aerb_no ? frmData.aerb_no : "");
    thisForm.find("#application_no").val(frmData.application_no ? frmData.application_no : "");
    thisForm.find("#validity").val(frmData.validity ? frmData.validity : "");

    if (frmData.movement_approval_doc != "" && frmData.movement_approval_doc != undefined) {
        let fullPath = frmData.movement_approval_doc;
        let fileName = fullPath.split('/').pop();
        thisForm.find("#movement_approval_doc").val(fullPath);
        thisForm.find('#movement_approval_prev').attr('href', uploadURL + fullPath).removeClass('hide');
        thisForm.find('#movement_approval_remove').addClass('i-block').removeClass('hide');
        thisForm.find('#movement_approval_img-prev-box').removeClass('hide');
        thisForm.find('#movement_approval_img-prev').html(fileName);
        let fileInput = thisForm.find('#movement_approval');
        let newFile = new DataTransfer();
        newFile.items.add(new File([""], fileName));
        fileInput[0].files = newFile.files;
    } else {
        thisForm.find('#movement_approval_doc').val('');
        thisForm.find('#movement_approval_prev').attr('href', '#').addClass('hide');
        thisForm.find('#movement_approval_remove').removeClass('i-block').addClass('hide');
        thisForm.find('#movement_approval_img-prev').html('');
        thisForm.find('#movement_approval_img-prev-box').addClass('hide').html('');
        thisForm.find('#movement_approval').val('');
    }
    toggleRequired();

    setTimeout(() => {
        thisForm.find("#io_stock_qty").val(parseFloat(frmData.io_stock_qty || 0).toFixed(3));
    }, 150);
    thisForm.find("#pend_pi_qty").val(frmData.pend_pi_qty != "" ? parseFloat(frmData.pend_pi_qty).toFixed(3) : "");
    let ioStock = parseFloat(frmData.io_stock_qty || 0);
    let pendingPI = parseFloat(frmData.pend_pi_qty || 0);
    let minQty = Math.min(ioStock, pendingPI);
    var decPlaces = (frmData.main_group == 'Industrial X-Ray Films' || frmData.main_group == 'Material – MPT' || frmData.main_group == 'Chemical – DPT') ? 0 : 3;
    thisForm.find("#dc_qty").val(frmData.dc_qty ? parseFloat(frmData.dc_qty).toFixed(decPlaces) : parseFloat(0).toFixed(decPlaces)).change();
    if (frmData.main_group == 'Industrial X-Ray Films' || frmData.main_group == 'Material – MPT' || frmData.main_group == 'Chemical – DPT') {
        thisForm.find('#dc_qty').removeClass('isNumberKey').addClass('isNumberKeyNotDot');
        thisForm.find('#dc_qty').attr('onblur', 'formatPoints(this, 0)');
    } else {
        thisForm.find('#dc_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        thisForm.find('#dc_qty').attr('onblur', 'formatPoints(this, 3)');
    }
    thisForm.find("#pod_rate_unit").val(frmData.pod_rate_unit ? parseFloat(frmData.pod_rate_unit).toFixed(2) : parseFloat(0).toFixed(2)).change();
    thisForm.find("#pod_del_date").val(frmData.pod_del_date ? frmData.pod_del_date : "");
    thisForm.find("#remark").val(frmData.remark ? frmData.remark : "");

    // thisForm.find("#dc_qty").attr('max', parseFloat(frmData.pend_pi_qty).toFixed(3));
    thisForm.find("#dc_qty").attr('max', minQty.toFixed(3));
    // getSrNo();
    getSrNo(frmData.main_group, frmData.item_id, frmData.sr_table_pk_id).done(function () {
        if (thisForm.find("#sr_table_pk_id option[value='" + sr_table_pk_id + "']").length === 0 && name_for_display != undefined && sr_table_pk_id != 0) {
            thisForm.find("#sr_table_pk_id").append(
                `<option  class="temp-sr_table_pk_id" value="${sr_table_pk_id}" data-sr_table_unique_id="${frmData.sr_table_unique_id}" data-stock_qty="${frmData.io_stock_qty || 0}" selected>${name_for_display}</option>`
            );
        }
        jQuery("#InterLocationTransferDetailsForm #sr_table_pk_id").val(frmData.sr_table_pk_id ? zeroToEmpty(frmData.sr_table_pk_id) : "").trigger('change.select2');
        if (['dpt_chemical', 'mpt_material', 'dpt_material'].includes(frmData.sr_table_unique_id) || ['Industrial X-Ray Films', 'General'].includes(frmData.main_group)) {
            thisForm.find('#dc_qty').attr('readonly', false).prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
        } else {
            thisForm.find('#dc_qty').attr('readonly', true).prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
        }
        jQuery("#InterLocationTransferDetailsForm #sr_table_unique_id").val(frmData.sr_table_unique_id ? zeroToEmpty(frmData.sr_table_unique_id) : "");
        if (frmData.in_use == true) {
            setSelect2Readonly('#InterLocationTransferDetailsForm #sr_table_pk_id', true);
            jQuery('#InterLocationTransferDetailsForm #sr_table_pk_id').addClass('skip-tab');
        }
    });


    if (frmData.in_use == true) {

        thisForm.find("#dc_qty").attr('min', parseFloat(frmData.used_qty).toFixed(3));
        setSelect2Readonly('#InterLocationTransferDetailsForm #item_id', true);
        thisForm.find('#item_id').addClass('skip-tab');
        setSelect2Readonly('#InterLocationTransferDetailsForm #sr_table_pk_id', true);
        thisForm.find('#sr_table_pk_id').addClass('skip-tab');

    } else {
        if (DCTypeId == "From Indent" || frmData.pid_id != 0) {
            setSelect2Readonly('#InterLocationTransferDetailsForm #item_id', true);
            thisForm.find('#item_id').addClass('skip-tab');
        } else {
            setSelect2Readonly('#InterLocationTransferDetailsForm #item_id', false);
            thisForm.find('#item_id').removeClass('skip-tab');
        }
        if (frmData.inter_location_transfer_details_id > 0) {
            setSelect2Readonly('#InterLocationTransferDetailsForm #sr_table_pk_id', true);
            thisForm.find('#sr_table_pk_id').addClass('skip-tab');
        } else {
            if (frmData.main_group == 'Industrial X-Ray Films' || frmData.main_group == 'General') {
                setSelect2Readonly('#InterLocationTransferDetailsForm #sr_table_pk_id', true);
                thisForm.find('#sr_table_pk_id').addClass('skip-tab');
            } else {
                setSelect2Readonly('#InterLocationTransferDetailsForm #sr_table_pk_id', false);
                thisForm.find('#sr_table_pk_id').removeClass('skip-tab');
            }
        }
    }

    if (frmData.inter_location_transfer_details_id > 0) {
        if (['Industrial X-Ray Films', 'General', 'Material – MPT', 'Chemical – DPT'].includes(frmData.main_group)) {
            thisForm.find('#dc_qty').trigger('change').prop('readonly', false).removeClass('skip-tab').attr('tabindex', '0')
        } else {
            thisForm.find('#dc_qty').trigger('change').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1')
        }

    } else {
        $table_pk_id_else = thisForm.find('#sr_table_pk_id').removeClass('skip-tab');
        if ($table_pk_id_else != "" && ['Industrial X-Ray Films', 'General', 'Material – MPT', 'Chemical – DPT'].includes(frmData.main_group)) {
            thisForm.find('#dc_qty').trigger('change').prop('readonly', false).removeClass('skip-tab').attr('tabindex', '0')
        } else {
            thisForm.find('#dc_qty').trigger('change').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1')
        }
    }

    thisForm.modal('show');
}

function removeInterLocationTransferDetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        let item = inter_details_data[formIndx];


        if (item.inter_location_transfer_details_id && item.inter_location_transfer_details_id != 0) {
            item.mode = "Delete";
        } else {
            inter_details_data.splice(formIndx, 1);
        }
        removeFormObj();
        // jQuery(th).closest("tr").remove();

    });
}

function removeFormObj(formIndx) {
    // delete inter_details_data[formIndx];
    // inter_details_data = inter_details_data.filter(element => element != null);
    if (inter_details_data.length == 0) {
        setRadioReadonly('input[name*="dc_type_id"]', false);
    }
    jQuery('#InterLocationTransferDetailTable tbody').empty();
    fillInterLocationTransferDetailTable();
}


// edit time fill table start
function fillInterLocationTransferDetailTable() {
    let thisModal = jQuery('#InterLocationTransferDetailsModal');
    jQuery('#InterLocationTransferDetailTable tbody').empty();
    if (inter_details_data.length > 0) {
        var tblHtml = '';
        for (let key in inter_details_data) {
            if (inter_details_data[key].mode == "Delete") {
                continue;
            }
            let formIndx = inter_details_data.indexOf(inter_details_data[key]);
            var item_id = inter_details_data[key].item_id ? inter_details_data[key].item_id : '';
            var item_name = item_id != '' ? thisModal.find('#item_id option[value="' + item_id + '"]').text() || inter_details_data[key].item_name : '';
            var pi_no = inter_details_data[key].pi_no ? zeroToEmpty(inter_details_data[key].pi_no) : '';
            var pi_date = inter_details_data[key].pi_date ? inter_details_data[key].pi_date : '';
            var item_group = inter_details_data[key].item_group != "" ? inter_details_data[key].item_group : '';
            var main_group = inter_details_data[key].main_group != "" ? inter_details_data[key].main_group : '';
            var io_stock_qty = parseFloat(inter_details_data[key].io_stock_qty || 0).toFixed(3);
            // var pend_pi_qty = '';
            pend_pi_qty = inter_details_data[key].pend_pi_qty != "" ? inter_details_data[key].pend_pi_qty : '';
            var dc_qty = inter_details_data[key].dc_qty ? parseFloat(inter_details_data[key].dc_qty).toFixed(3) : parseFloat(0).toFixed(3);
            var sr_table_pk_id = inter_details_data[key].sr_table_pk_id ? inter_details_data[key].sr_table_pk_id : '';
            // var name_for_display = inter_details_data[key].name_for_display ? inter_details_data[key].name_for_display : thisModal.find('#sr_table_pk_id option[value="' + sr_table_pk_id + '"]').text();
            var DCTypeId = jQuery('#commonInterLocationTransferForm').find('input[name*="dc_type_id"]:checked').val();
            var unit = (DCTypeId === 'SQIN from Prod. Area') ? 'SQIN' : (inter_details_data[key].unit != "" ? inter_details_data[key].unit : '');
            var name_for_display = sr_table_pk_id != "" ? inter_details_data[key].name_for_display ? inter_details_data[key].name_for_display : thisModal.find('#sr_table_pk_id option[value="' + sr_table_pk_id + '"]').text().trim() : "";
            // var pod_unit = inter_details_data[key].pod_unit != "" ? inter_details_data[key].pod_unit : '';
            // var pod_rate_unit = inter_details_data[key].pod_rate_unit ? parseFloat(inter_details_data[key].pod_rate_unit).toFixed(2) : parseFloat(0).toFixed(2);
            // var pod_amount = inter_details_data[key].pod_amount ? parseFloat(inter_details_data[key].pod_amount).toFixed(2) : parseFloat(0).toFixed(2);
            // var pod_del_date = inter_details_data[key].pod_del_date != "" ? inter_details_data[key].pod_del_date : "";

            var remark = inter_details_data[key].remark ? inter_details_data[key].remark : "";
            var in_use = inter_details_data[key].in_use == true ? true : false;

            var aerb_no = inter_details_data[key].aerb_no != "" && inter_details_data[key].aerb_no != undefined ? inter_details_data[key].aerb_no : '';
            var application_no = inter_details_data[key].application_no != "" && inter_details_data[key].application_no != undefined ? inter_details_data[key].application_no : '';
            var validity = inter_details_data[key].validity != "" && inter_details_data[key].validity != undefined ? inter_details_data[key].validity : '';
            var movement_approval_doc = inter_details_data[key].movement_approval_doc != "" && inter_details_data[key].movement_approval_doc != undefined ? inter_details_data[key].movement_approval_doc : "";

            if (jQuery('#InterLocationTransferDetailTable tbody').find('#noDetails').length > 0) {
                jQuery('#InterLocationTransferDetailTable tbody').empty();
            }
            tblHtml += `<tr>`;
            tblHtml += `<td>`;
            tblHtml += in_use == true ? DetailsActionDropdown('editInterLocationTransferDetails') : DetailsActionDropdown('editInterLocationTransferDetails', 'removeInterLocationTransferDetails');
            tblHtml += ` <input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;
            tblHtml += `<td>${pi_no}</td>`;
            tblHtml += `<td>${pi_date}</td>`;
            tblHtml += `<td>${item_name}</td>`;
            tblHtml += `<td>${item_group}</td>`;
            tblHtml += `<td>${main_group}</td>`;
            tblHtml += `<td>${name_for_display}</td>`;
            tblHtml += `<td>${io_stock_qty}</td>`;
            tblHtml += `<td>${pend_pi_qty}</td>`;
            tblHtml += `<td>${dc_qty ?? ''}</td>`;
            tblHtml += `<td>${unit ?? ''}</td>`;
            tblHtml += `<td>${aerb_no}</td>`;
            tblHtml += `<td>${application_no}</td>`;

            if (movement_approval_doc != "") {
                let fullImagePath = uploadURL + movement_approval_doc;
                tblHtml += `<td style="text-align:center; vertical-align:middle;">
                    <a target="_blank" href="${fullImagePath}" title="View" style="font-size:18px; display:inline-block;">
                        <i class="ri-eye-fill"></i>
                    </a>
                    <input type='hidden' name='movement_approval_doc[]' value="${movement_approval_doc}"/>
                </td>`;
            } else {
                tblHtml += `<td style="text-align:center; vertical-align:middle;">
                    <input type='hidden' name='movement_approval_doc[]' value=""/>
                </td>`;
            }
            tblHtml += `<td>${validity}</td>`;
            tblHtml += `<td>${remark}</td>`;
            tblHtml += `</tr>`;

        }
        jQuery('#InterLocationTransferDetailTable tbody').empty().append(tblHtml);
    }
    // calculateTotalAmount();
}
// Main Purchase Order Form Submit
$('#commonInterLocationTransferForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#InterLocationTransferModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;

    var dateValue = document.getElementById("dc_date").value.trim();

    // validatePercentage
    if (!isValidDate(dateValue)) {
        toastr.error("Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#InterLocationTransferModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#InterLocationTransferModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#InterLocationTransferModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    jQuery('#InterLocationTransferModal').find('#submitbtn').prop('disabled', true);

    var formIdnew = jQuery('#InterLocationTransferModal').find('#commonInterLocationTransferForm').find('#id').val();
    var formUrl = formIdnew != undefined && formIdnew != "" ? "update-inter_location_transfer" : "store-inter_location_transfer";
    var data = new FormData(form);
    data.append('inter_details_data', JSON.stringify(inter_details_data ?? []));
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));

    let validData = inter_details_data.filter(item => item.mode !== "Delete");

    if (validData.length > 0 && !jQuery.isEmptyObject(validData)) {
        let isValidDetails = true;
        let errorMsg = '';

        $.each(validData, function (index, row) {

            if ((row.sr_table_pk_id === undefined || row.sr_table_pk_id === null || row.sr_table_pk_id === '') && !['Industrial X-Ray Films', 'General'].includes(row.main_group)) {
                errorMsg = `Select SR No.`;
                isValidDetails = false;
                return false;
            }

            if (row.dc_qty === undefined || row.dc_qty === null || row.dc_qty === '' || parseFloat(row.dc_qty) <= 0) {
                errorMsg = `Enter DC Qty.`;
                isValidDetails = false;
                return false;
            }

            if ((!row.aerb_no || row.aerb_no.toString().trim() === '') && (row.main_group === 'IRED')) {
                errorMsg = `Enter AERB No.`;
                isValidDetails = false;
                return false;
            }

            if ((!row.application_no || row.application_no.toString().trim() === '') && (row.main_group === 'IRED')) {
                errorMsg = `Enter Application No.`;
                isValidDetails = false;
                return false;
            }

            if ((!row.movement_approval_doc || row.movement_approval_doc.toString().trim() === '') && (row.main_group === 'IRED')) {
                errorMsg = `Upload Movement Approval Document.`;
                isValidDetails = false;
                return false;
            }

        });

        if (!isValidDetails) {
            toastr.error(errorMsg);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');

            jQuery('#InterLocationTransferModal')
                .find('#submitbtn')
                .prop('disabled', false);

            return;
        }
        jQuery.ajax({
            type: 'POST',
            url: formUrl,
            data: data,
            contentType: false,
            processData: false,
            success: function (data) {
                if (data.response_code == 1) {
                    if (formIdnew != undefined && formIdnew != "") {
                        function redirectFn() {
                            window.location.reload();
                        }
                        if (data.url != "") {
                            toastSuccessPreview(data.response_message, data.url, redirectFn);
                        } else {
                            toastSuccess(data.response_message, redirectFn);
                        }
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#InterLocationTransferModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formIdnew == undefined || formIdnew == "") {
                        function nextFn() {
                            document.getElementById("commonInterLocationTransferForm").reset();
                            const form = document.getElementById("commonInterLocationTransferForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            jQuery('#dc_sequence').prop('readonly', false).focus();
                            $("#to_location_id").val('').trigger('change');
                            $("#mode_of_transport").val('');
                            $("#vehicle_no").val('');
                            $("#transporter").val('');
                            $("#sp_note").val('');
                            inter_details_data = [];
                            selectedRows = {};
                            resetFieds();
                            jQuery('#InterLocationTransferDetailTable tbody').empty();
                            getLatestInterLocationTransferNo();
                            setRadioReadonly("input[name='dc_type_id']", false);
                            jQuery('#InterLocationTransferModal').find('#pending_btn').prop('disabled', true);
                        }

                        if (data.url != "") {
                            toastSuccessPreview(data.response_message, data.url, nextFn);
                        } else {
                            toastSuccess(data.response_message, nextFn);
                        }
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#InterLocationTransferModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#InterLocationTransferModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#InterLocationTransferModal').find('#submitbtn').prop('disabled', false);
                }
            },
            error: function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let errorMsg = '';
                    $.each(errors, function (key, value) {
                        errorMsg += value + '\n';
                    });
                    toastr.error(errorMsg);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#InterLocationTransferModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#InterLocationTransferModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        toastr.error('Add At Least One Inter Location Transfer Detail.');

        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#InterLocationTransferModal').find('#submitbtn').prop('disabled', false);
    }
});
// Main Form Submit End




function fillPendingILT() {
    jQuery('#InterLocationTransferModal').find('#pending_btn').prop('disabled', false);
    var dc_type = jQuery('input[name="dc_type_id"]:checked').val();

    var thisForm = jQuery('#InterLocationTransferDetailsForm');
    var formId = jQuery('#commonInterLocationTransferForm').find('input[name="id"]').val();


    if (dc_type != "") {
        if (formId != undefined && formId != '') {
            var Url = "get-pi_list_for_inter_location_transfer?id=" + formId;
        } else {
            var Url = "get-pi_list_for_inter_location_transfer";
        }

        jQuery.ajax({
            url: Url,
            type: 'GET',
            headers: headerOpt,
            dataType: 'json',
            processData: false,
            success: function (data) {
                if (data.response_code == 1 && data.pi_pending_data.length > 0) {
                    var usedParts = [];
                    var totalDisb = 0;
                    var found = 0;

                    thisForm.find('#InterLocationTransferDetailTable tbody input[name="form_indx"]').each(function (indx) {
                        let frmIndx = jQuery(this).val();

                        let jbEorkOrderId = po_data[frmIndx].po_id;
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
                    var tblHtml = ``;
                    var found = 0;

                    // end new code
                    if (data.pi_pending_data.length > 0 && !jQuery.isEmptyObject(data.pi_pending_data)) {
                        found = 1;

                        for (let idx in data.pi_pending_data) {
                            var inUse = isUsed(data.pi_pending_data[idx].pod_id);
                            var in_use = data.pi_pending_data[idx].in_use == true ? 'readonly' : '';
                            totalEntry++;
                            tblHtml += `
                                    <tr>
                                        <td><input type="checkbox" name="pid_id[]" class="simple-check checkbox-filter-remove ${inUse ? 'in-use' : ''}" id="pid_ids_${data.pi_pending_data[idx].pid_id}" value="${data.pi_pending_data[idx].pid_id}" ${inUse ? 'checked' : ''} ${in_use}  onchange="checkesCheckboxFrist(this)"/></td>
                                        <td>${data.pi_pending_data[idx].pi_no}</td>
                                        <td>${data.pi_pending_data[idx].pi_date}</td>
                                        <td>${data.pi_pending_data[idx].to_location != null && data.pi_pending_data[idx].to_location != undefined ? data.pi_pending_data[idx].to_location : ''}</td>
                                        <td>${data.pi_pending_data[idx].item_name}</td>
                                        <td>${data.pi_pending_data[idx].item_group}</td>
                                        <td>${data.pi_pending_data[idx].main_group}</td>
                                        <td>${parseFloat(data.pi_pending_data[idx].pend_pi_qty).toFixed(3)}</td>
                                        <td>${data.pi_pending_data[idx].unit}</td>
                                        <td>${data.pi_pending_data[idx].remark != null ? data.pi_pending_data[idx].remark : ''}</td>
                                        <td>${data.pi_pending_data[idx].indent_by != null ? data.pi_pending_data[idx].indent_by : ''}</td>
                                    </tr>`;

                        }

                    } else {

                        tblHtml += `<tr class="centeralign" id="noPendingPo">
                                        <td colspan="11">No Pending PO Available</td>
                                    </tr>`;

                    }



                    var $table = jQuery("#InterLocationTransferPendingModal").find('#pendingInterLocationTransferPendingDataTable');
                    if (jQuery.fn.DataTable.isDataTable($table)) {
                        $table.DataTable().clear().destroy();
                    }
                    jQuery('#pendingInterLocationTransferPendingDataTable tbody').empty().append(tblHtml);

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
                    fixDataTableColumnsUntilAdjusted($new);

                    if (dc_type == 'Manual' || formId != "") {
                        jQuery('.toggleModalBtn').prop('disabled', true);
                    } else {
                        jQuery('.toggleModalBtn').prop('disabled', false);
                    }

                } else {
                    jQuery('.toggleModalBtn').prop('disabled', true);
                }
            },

            error: function (jqXHR, textStatus, errorThrown) {
                jQuery('.toggleModalBtn').prop('disabled', true);
                var errMessage = JSON.parse(jqXHR.responseText);
                if (jqXHR.status == 401) {
                    toastr.error(jqXHR.statusText);
                } else {
                    toastr.error('Something went wrong!');
                    console.log(JSON.parse(jqXHR.responseText));

                }
            }
        });

    } else {
        jQuery('.toggleModalBtn').prop('disabled', true);
    }
}

// get selected pending PI
$('#addInterLocationTransferPendingForm').on('submit', function (e) {

    e.preventDefault();

    jQuery('#full-page-loader')
        .removeClass('hidden-loader')
        .addClass('loader-progress-whole-page');

    jQuery('#InterLocationTransferPendingModal')
        .find('#submitbtn')
        .prop('disabled', true);

    let chkArr = [];
    var formId = jQuery('#commonInterLocationTransferForm').find('input[name="id"]').val();

    jQuery("#addInterLocationTransferPendingForm")
        .find("[id^='pid_ids_']:checked")
        .each(function () {
            chkArr.push(jQuery(this).val());
        });

    // No checkbox selected
    if (chkArr.length === 0) {
        toastr.error('Select Purchase Indent From Pending.');

        jQuery('#full-page-loader')
            .removeClass('loader-progress-whole-page')
            .addClass('hidden-loader');

        jQuery('#InterLocationTransferPendingModal')
            .find('#submitbtn')
            .prop('disabled', false);

        return;
    }

    if (formId != undefined && formId != "") {
        var pend_url = "get-pi_part_data_inter_location_transfer?id=" + formId;
    } else {
        var pend_url = "get-pi_part_data_inter_location_transfer";
    }

    jQuery.ajax({
        url: pend_url,
        type: 'GET',
        data: { pid_ids: chkArr.join(',') },
        dataType: 'json',
        success: function (data) {

            if (data.response_code == 1) {

                if (data.pi_data && data.pi_data.length > 0) {
                    inter_details_data = [];
                    for (let ind in data.pi_data) {
                        inter_details_data.push(data.pi_data[ind]);
                    }
                    fillILTDetailsTable(data.pi_data);
                } else {
                    fillILTDetailsTable([]);
                }

                jQuery("#InterLocationTransferPendingModal").modal('hide');
                setRadioReadonly('input[name*="dc_type_id"]', true);
            } else {
                fillILTDetailsTable([]);
            }

            jQuery('#InterLocationTransferPendingModal')
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

            jQuery('#InterLocationTransferPendingModal')
                .find('#submitbtn')
                .prop('disabled', false);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');
        }
    });
});


function fillILTDetailsTable() {
    var dc_type_id = jQuery('#commonInterLocationTransferForm').find("input[name*='dc_type_id']:checked").val();

    let thisModal = jQuery('#InterLocationTransferDetailsModal');
    let tblHtml = ``;
    if (inter_details_data.length > 0) {
        for (let key in inter_details_data) {

            var formIndx = inter_details_data.indexOf(inter_details_data[key]);

            if (dc_type_id == 'From Indent') {
                var pi_no = inter_details_data[key].pi_no ? inter_details_data[key].pi_no : "";
                var pi_date = inter_details_data[key].pi_date ? inter_details_data[key].pi_date : "";
            } else {
                var pi_no = "";
                var pi_date = "";

            }

            if (inter_details_data[key].item_name != '' && inter_details_data[key].item_name != undefined) {
                var item_name = inter_details_data[key].item_name;
            } else {
                var item_name = item_id != '' ? thisModal.find('#item_id option[value="' + item_id + '"]').text() : '';
            }

            var item_group = inter_details_data[key].item_group ? inter_details_data[key].item_group : "";
            var main_group = inter_details_data[key].main_group ? inter_details_data[key].main_group : "";
            var io_stock_qty = inter_details_data[key].io_stock_qty ? parseFloat(inter_details_data[key].io_stock_qty).toFixed(3) : parseFloat(0).toFixed(3);
            var sr_no = inter_details_data[key].sr_no ? inter_details_data[key].sr_no : "";
            var sr_table_pk_id = inter_details_data[key].sr_table_pk_id ? inter_details_data[key].sr_table_pk_id : "";
            var unit = inter_details_data[key].unit != "" ? inter_details_data[key].unit : '';

            var pend_pi_qty = '';
            if (pi_no != "") {
                pend_pi_qty = inter_details_data[key].pend_pi_qty ? parseFloat(inter_details_data[key].pend_pi_qty).toFixed(3) : "";
            } else {
                pend_pi_qty = '';
            }


            var dc_qty = inter_details_data[key].dc_qty ? parseFloat(inter_details_data[key].dc_qty).toFixed(3) : parseFloat(0).toFixed(3);

            var remark = inter_details_data[key].remark ? inter_details_data[key].remark : "";
            var in_use = inter_details_data[key].in_use == true ? true : false;


            tblHtml += `<tr>`;
            tblHtml += `<td>`;
            tblHtml += in_use == true ? DetailsActionDropdown('editInterLocationTransferDetails') : DetailsActionDropdown('editInterLocationTransferDetails', 'removeInterLocationTransferDetails');
            tblHtml += ` <input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;
            tblHtml += `<td>${pi_no}</td>`;
            tblHtml += `<td>${pi_date}</td>`;
            tblHtml += `<td>${item_name}</td>`;
            tblHtml += `<td>${item_group}</td>`;
            tblHtml += `<td>${main_group}</td>`;
            tblHtml += `<td>${sr_table_pk_id}</td>`;
            tblHtml += `<td>${io_stock_qty}</td>`;
            tblHtml += `<td>${pend_pi_qty}</td>`;
            tblHtml += `<td>${dc_qty}</td>`;
            tblHtml += `<td>${unit}</td>`;
            tblHtml += `<td>${remark}</td>`;
            tblHtml += `</tr>`;
        }
        jQuery('#InterLocationTransferDetailTable tbody').empty();
        jQuery('#InterLocationTransferDetailTable tbody').append(tblHtml);
    }

}

jQuery('#InterLocationTransferPendingModal').on('show.bs.modal', function (e) {
    var dt = jQuery('#pendingInterLocationTransferPendingDataTable').DataTable();
    fixDataTableColumnsUntilAdjusted(dt);
    var usedParts = [];
    if (inter_details_data && inter_details_data.length > 0) {
        inter_details_data.forEach(function (item) {
            if (item.pid_id) {
                usedParts.push(Number(item.pid_id));
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
    jQuery('#pendingInterLocationTransferPendingDataTable tbody tr').each(function (indx) {
        totalEntry++;
        var checkField = jQuery(this).find('input[name="pid_id[]"]');
        var partId = jQuery(checkField).val();
        var inUse = isUsed(partId);

        if (formId == undefined) {
            if (inter_details_data.length > 0) {
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
jQuery('#InterLocationTransferPendingModal').on('hide.bs.modal', function (e) {
    selectedRows = {};
});



function resetFieds() {

    jQuery("#to_location_id").val('').trigger('change');
    setRadioReadonly("input[name='dc_type_id']", false);
    jQuery('#InterLocationTransferModal').find('input[name*="dc_type_id"][value="Manual"]').prop('checked', true).change().focus();
    jQuery("#commonInterLocationTransferForm .toggleModalBtn").prop('disabled', true);
    jQuery("#InterLocationTransferDetailsForm #sr_table_unique_id").val('');
    jQuery("#InterLocationTransferDetailsForm #inter_location_transfer_details_id").val('');
    jQuery("#InterLocationTransferDetailsForm #stock_rate_unit").val('');
    jQuery("#InterLocationTransferDetailsForm #amount").val('');

    jQuery("#InterLocationTransferDetailsForm #aerb_no").val('');
    jQuery("#InterLocationTransferDetailsForm #application_no").val('');
    jQuery("#InterLocationTransferDetailsForm #validity").val('');
    jQuery("#InterLocationTransferDetailsForm #aerb_no").attr("readonly", true).prop('required', false).addClass('skip-tab').attr('tabindex', '-1');
    jQuery("#InterLocationTransferDetailsForm #application_no").attr("readonly", true).prop("required", false).addClass('skip-tab').attr('tabindex', '-1');
    jQuery("#InterLocationTransferDetailsForm #movement_approval").attr("readonly", true).prop("disabled", true).prop("required", false).addClass('skip-tab').attr('tabindex', '-1');
    jQuery("#InterLocationTransferDetailsForm #validity").attr("readonly", true).addClass('skip-tab').attr('tabindex', '-1');
    jQuery('#InterLocationTransferDetailsForm').find('#validity').datepicker('disable');
    clearMovement();
    jQuery("#item_id").val("").removeClass('skip-tab');
    jQuery("#sr_table_pk_id").val("").removeClass('skip-tab');
    inter_details_data = [];
    selectedRows = {};
    jQuery('#InterLocationTransferDetailTable tbody').empty();
    jQuery('#InterLocationTransferDetailTable tbody').append(`
        <tr>
            <td colspan="12" class="text-center" id="noDetails">
                No Inter Location Transfer Details Added
            </td>
        </tr>
    `);
    jQuery('#item_id option.temp-item').remove();
    jQuery('#sr_table_pk_id option.temp-sr_table_pk_id').remove();
    setSelect2Readonly('#InterLocationTransferModal #to_location_id', false);
    jQuery("#to_location_id").removeClass('skip-tab');

}

function validateMovementImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.pdf)$/i;
    return allowedExtensions.exec(filePath) ? true : false;
}

jQuery('#InterLocationTransferDetailsForm #movement_approval').on('change', function (e) {
    MovementfileUpload(e);
});

function MovementfileUpload(e) {
    var form_data = new FormData();
    var target = e.target;
    var id = target.id;
    var files = target.files;
    var totalfiles = files.length;
    var oldImg = jQuery('#movement_approval_doc').val();
    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');

    if (totalfiles > 0) {
        var notValid = 0;
        for (var index = 0; index < totalfiles; index++) {
            if (validateMovementImage(files[index].name) == true) {
                form_data.append("docs[]", files[index]);
            } else {
                notValid = 1;
                toastr.error("Only (jpeg,jpg,png,gif,pdf) files are allowed.");
                e.stopImmediatePropagation();
                return false;
            }
        }

        if (notValid == 0) {
            jQuery('#InterLocationTransferDetailsModal').find('#submitbtn').prop('disabled', true);
            jQuery('#' + id).parent().parent().find('.uneditable-input').addClass('file-loader');
            jQuery.ajax({
                url: "upload-docs",
                type: 'POST',
                data: form_data,
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    jQuery('#InterLocationTransferDetailsModal').find('#submitbtn').prop('disabled', false);
                    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    if (data.response_code == 1) {
                        if (oldImg != "" && oldImg.includes('temp_media/')) {
                            removeMediaMovement(oldImg);
                        }
                        $('#movement_approval_doc').val(data.files);
                        $('#movement_approval_prev').attr('href', data.files_url).removeClass('hide');
                        $('#movement_approval_remove').addClass('i-block').removeClass('hide');
                        toggleRequired();
                        console.log(data.response_message);
                    } else {
                        console.log(data.response_message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#InterLocationTransferDetailsModal').find('#submitbtn').prop('disabled', false);
                    $('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    $('#' + id).parent().parent().find('.uneditable-input').addClass('iconfa-warning-sign upload-error');
                    if (jqXHR.status == 401) {
                        toastr.error(jqXHR.statusText);
                    } else {
                        toastr.error('Something went wrong!');
                        console.log(JSON.parse(jqXHR.responseText));
                    }
                }
            });
        }
    } else {
        if (oldImg != "") {
            let fileName = oldImg.split('/').pop();
            let fileInput = jQuery('#movement_approval');
            let dataTransfer = new DataTransfer();
            dataTransfer.items.add(new File([""], fileName));
            fileInput[0].files = dataTransfer.files;
            return false;
        }
        clearMovement();
    }
}

function toggleRequired() {
    let main_group = jQuery('#InterLocationTransferDetailsForm').find('#main_group').val();
    let docVal = jQuery('#InterLocationTransferDetailsModal').find('#movement_approval_doc').val();
    if (main_group == 'IRED') {
        if (docVal && docVal !== "") {
            jQuery('#InterLocationTransferDetailsModal').find('#movement_approval').prop('required', false);
        } else {
            jQuery('#InterLocationTransferDetailsModal').find('#movement_approval').prop('required', true);
        }
    } else {
        jQuery('#InterLocationTransferDetailsModal').find('#movement_approval').prop('required', false);
    }
}

function clearMovement() {
    jQuery('#InterLocationTransferDetailsModal').find('#movement_approval_doc').val('');
    jQuery('#InterLocationTransferDetailsModal').find('#movement_approval').val('');
    jQuery('#InterLocationTransferDetailsModal').find('#movement_approval_prev').attr('href', '#').addClass('hide');
    jQuery('#InterLocationTransferDetailsModal').find('#movement_approval_remove').removeClass('i-block').addClass('hide');
    toggleRequired();
}

function removeFileMovement(e) {
    e.stopImmediatePropagation();
    toastDetailDelete('Are You Sure, You Want To Delete ?', () => {
        var oldImg = jQuery('#movement_approval_doc').val();
        if (oldImg != "" && oldImg.includes('temp_media/')) {
            removeMediaMovement(oldImg);
        }
        clearMovement();
    });
}

function removeMediaMovement(docName) {
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
            console.log(data.response_message);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.status == 401) {
                toastr.error(jqXHR.statusText);
            } else {
                toastr.error('Something went wrong!');
                console.log(JSON.parse(jqXHR.responseText));
            }
        }
    });
}

// Prevent focus on standard inputs/textarea inside ILT Details modal if they are readonly, disabled, or skip-tab
jQuery('#InterLocationTransferDetailsModal').on('focusin focus', 'input, textarea, .select2-selection', function (e) {
    const $el = jQuery(this);
    const isSelect2 = $el.hasClass('select2-selection');
    const $target = isSelect2 ? $el.closest('.select2-container').prev('select') : $el;

    if ($target.length && ($target.hasClass('skip-tab') || $target.prop('readonly') || $target.attr('readonly') || $target.prop('disabled') || $target.data('s2-readonly'))) {
        e.preventDefault();
        if (isSelect2) {
            $el.attr('tabindex', '-1').blur();
        } else {
            $target.attr('tabindex', '-1').blur();
        }
    }
});

// Auto-focus first visible, editable field when Details Modal is shown
jQuery('#InterLocationTransferDetailsModal').on('shown.bs.modal', function () {
    let thisModal = jQuery('#InterLocationTransferDetailsModal');
    setTimeout(function () {
        let firstEditable = thisModal.find('input:not([readonly]):not([disabled]):not(.skip-tab):visible, textarea:not([readonly]):not([disabled]):not(.skip-tab):visible, select:not([readonly]):not([disabled]):not(.skip-tab):visible').first();
        if (firstEditable.length) {
            if (firstEditable.is('select')) {
                let sel = firstEditable.next('.select2-container').find('.select2-selection');
                if (sel.length) {
                    sel.attr('tabindex', 0).focus();
                }
            } else {
                firstEditable.focus();
            }
        }
    }, 50);
});

jQuery('#InterLocationTransferDetailsModal').on('show.bs.modal', function () {
    let thisModal = jQuery('#InterLocationTransferDetailsModal');
    let mainId = jQuery('#InterLocationTransferModal').find('#id').val();
    if (mainId && mainId > 0) {
        thisModal.find("#aerb_no").prop('readonly', true);
        thisModal.find("#application_no").prop('readonly', true);
        thisModal.find("#movement_approval").prop('disabled', true);
        thisModal.find("#movement_approval_remove").addClass('hide');
    }
});

jQuery('#InterLocationTransferDetailsModal').on('hide.bs.modal', function () {
    let thisModal = jQuery('#InterLocationTransferDetailsModal');
    thisModal.find("#aerb_no").prop('readonly', false);
    thisModal.find("#application_no").prop('readonly', false);
    thisModal.find("#movement_approval").prop('disabled', false);
});