

var dc_details_data = [];
var formId = jQuery('#commonDCCustomerForm').find('input[name="id"]').val();

// Edit purchase Indent row click
jQuery('#dyntable tbody').on('click', '.edit-delivery_challan_customer', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#DcCustomerModal').find('#id').val(data["dc_id"]);
    if (data && data["dc_id"]) {
        fetchAndFillDC(data["dc_id"]);
    }
});

// Function to fetch and fill purchase indent data
function fetchAndFillDC(id) {
    if (!id) return;
    jQuery('#DcCustomerModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-delivery_challan_customer",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.dc_data != null) {
                jQuery('#DcCustomerModal').find('#id').val(data.dc_data.dc_id != "" ? data.dc_data.dc_id : "");

                let url = checkFileRoute + "?id=" + data.dc_data.dc_id + "&name=" + data.dc_data.pdf_name + "&type=delivery_challan_customer";

                jQuery('#preview_btn').attr('href', url).show();

                jQuery('#DcCustomerModal').find('#dc_number').val(data.dc_data.dc_number != "" ? data.dc_data.dc_number : "");

                jQuery('#DcCustomerModal').find('#dc_date').val(data.dc_data.dc_date != "" ? data.dc_data.dc_date : "");

                jQuery('#DcCustomerModal').find('#dc_sequence').val(data.dc_data.dc_sequence != "" ? data.dc_data.dc_sequence : "");

                jQuery('#DcCustomerModal').find('#customer_id').val(data.dc_data.customer_id).trigger('change.select2');
                if (data.dc_data.in_use == true) {
                    jQuery('#DcCustomerModal').find('#customer_id').addClass('skip-tab');
                    setSelect2Readonly('#DcCustomerModal #customer_id', true);

                } else {
                    jQuery('#DcCustomerModal').find('#customer_id').removeClass('skip-tab');
                    setSelect2Readonly('#DcCustomerModal #customer_id', false);

                }
                jQuery('#DcCustomerModal').find('#prepared_by_user_id').val(data.dc_data.prepared_by_user_id).trigger('change.select2');

                jQuery('#DcCustomerModal').find('#mode_of_transport').val(data.dc_data.mode_of_transport != "" ? data.dc_data.mode_of_transport : "");
                jQuery('#DcCustomerModal').find('#transporter').val(data.dc_data.transporter != "" ? data.dc_data.transporter : "");
                jQuery('#DcCustomerModal').find('#vehicle_no').val(data.dc_data.vehicle_no != "" ? data.dc_data.vehicle_no : "");
                jQuery('#DcCustomerModal').find('#special_note').val(data.dc_data.special_note != "" ? data.dc_data.special_note : "");

                if (data.dc_details_data != "" && data.dc_details_data.length > 0) {
                    dc_details_data = data.dc_details_data.map(item => {
                        item.mode = "Update";
                        return item;
                    });
                    fillDCDetailTable();
                }

                jQuery('#DcCustomerModal').find('#pending_btn').prop('disabled', true);
                jQuery('#DcCustomerModal').find('#submitbtn').prop('disabled', false);

                const form = document.getElementById("DcCustomerModal");
                if (form) form.classList.remove('was-validated');
                jQuery('#DcCustomerModal').find('#add_new').show();
                jQuery('#DcCustomerModal').find('#preview_btn').show();

                if (data.dc_data.in_use == true) {
                    jQuery('#DcCustomerModal').find('#dc_sequence').prop('readonly', true);
                    let nextInput = jQuery('#DcCustomerModal').find('#dc_date');
                    if (nextInput.hasClass('trans-date-picker') || nextInput.hasClass('date-picker')) {
                        nextInput.one('focus', function () {
                            setTimeout(() => {
                                jQuery(this).datepicker('hide');
                            }, 50);
                        });
                    }
                    nextInput.focus();
                } else {
                    jQuery('#DcCustomerModal').find('#dc_sequence').prop('readonly', false);
                    jQuery('#DcCustomerModal').find('#dc_sequence').focus();
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
        }
    });
}


jQuery('#resetbtn').on('click', function () {
    dc_details_data = [];

    var formId = jQuery('#DcCustomerModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonDCCustomerForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
        jQuery('#commonDCCustomerForm').find('#prepared_by_user_id').val(loginUserId).trigger("change.select2");
        getLatestDeliveryChallanNo();
        resetFieds();
    } else {
        fetchAndFillDC(formId);
    }
});

jQuery('#DCDetailsModal').on('show.bs.modal', function () {
    let thisModal = jQuery('#DCDetailsModal');
    var details_id = thisModal.find("#dc_detail_id").val();
    var form_type = thisModal.find("#form_type").val();
    jQuery('#sr_table_pk_id').addClass('skip-tab');

    if (form_type == "edit" && details_id != 0) {
        jQuery('#item_id').addClass('skip-tab');
        jQuery('#sr_table_pk_id').addClass('skip-tab');
    } else {
        jQuery('#item_id').removeClass('skip-tab');

        setTimeout(function () {
            let sel = jQuery('#item_id')
                .next('.select2-container')
                .find('.select2-selection');
            sel.attr('tabindex', 0).focus();
        }, 20);
    }
    var mode = jQuery("#DCDetailsModal #form_type").val();
    var dc_detail_id = jQuery("#DCDetailsModal #dc_detail_id").val();
    if (mode == "add") {
        jQuery('#DCDetailsModal #dc_qty').removeAttr('min');
    }

});

jQuery('#DCDetailsModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#DCDetailsModal');
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    thisModal.find("#dc_detail_id").val(0);
    jQuery(this).find('#item_id option.temp-item').remove();
    jQuery(this).find('#sr_table_pk_id option.temp-sr_table_pk_id').remove();
    jQuery('#DCDetailsForm').trigger("reset");
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('mode_of_transport');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }

    jQuery('#DCDetailsModal #dc_qty').removeAttr('min');
    jQuery('#DCDetailsModal #dc_qty').removeAttr('max');
});

jQuery('#DcCustomerModal').on('show.bs.modal', function () {
    jQuery('#DcCustomerModal').find('#dc_sequence').prop('readonly', false);
    var hasAccess = jQuery('#commonDCCustomerForm').find('#has_access').val();
    jQuery('#commonDCCustomerForm').find('#prepared_by_user_id').val(loginUserId).trigger("change.select2");
    var formId = jQuery('#commonDCCustomerForm').find('input[name="id"]').val();

    if (formId == "" || formId == undefined) {
        jQuery('#commonDCCustomerForm').find('#prepared_by_user_id').val(loginUserId).trigger("change.select2");
        getLatestDeliveryChallanNo();
        jQuery('#DcCustomerModal').find('#add_new').hide();
        jQuery('#DcCustomerModal').find('#preview_btn').hide();
    } else {
        jQuery('#DcCustomerModal').find('#add_new').show();
        jQuery('#DcCustomerModal').find('#preview_btn').show();
    }
    const input = document.getElementById('dc_sequence');
    input?.focus();

});

jQuery('#DcCustomerModal').on('hide.bs.modal', function (e) {
    jQuery('#DcCustomerModal').find('#dc_sequence').prop('readonly', false);
    jQuery('#DCDetailTable tbody').empty();
    let thisModal = jQuery('#DcCustomerModal');
    thisModal.find("#id").val("");
    var thisForm = jQuery('#DCDetailsModal');
    thisForm.find("#dc_detail_id").val(0);
    dc_details_data = [];
    resetFieds();
    jQuery('#DcCustomerModal').find('#add_new').hide();
    jQuery('#DcCustomerModal').find('#preview_btn').hide();
    jQuery('#DcCustomerModal').find('#customer_id').removeClass('skip-tab');
    setSelect2Readonly('#DcCustomerModal #customer_id', false);
});

jQuery('#DcCustomerModal').on('click', '#add_new', function () {

    document.getElementById("commonDCCustomerForm").reset();
    const form = document.getElementById("commonDCCustomerForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    jQuery('#DCDetailTable tbody').empty();
    jQuery('#DCDetailTable tbody').append(`
        <tr>
            <td colspan="8" id="noDetails">
                No Delivery Challan Details Added
            </td>
        </tr>
    `);
    dc_details_data = [];
    getLatestDeliveryChallanNo();
    jQuery('#dc_sequence').prop('readonly', false).focus();
    jQuery('#commonDCCustomerForm').find('input[name="id"]').val('');
    jQuery('#DcCustomerModal').find('#add_new').hide();
    jQuery('#DcCustomerModal').find('#preview_btn').hide();
    jQuery('#DcCustomerModal').find("#customer_id").val('').trigger('change');
    jQuery('#commonDCCustomerForm').find('#prepared_by_user_id').val(loginUserId).trigger("change.select2");

});

jQuery('#DCDetailsForm #sr_table_pk_id').on('change', function () {
    let selectedOption = jQuery(this).find('option:selected');
    let sr_table_unique_id = selectedOption.data('sr_table_unique_id') || '';

    if (selectedOption.val() !== "") {
        jQuery('#sr_table_unique_id').val(sr_table_unique_id);
        if (['dpt_chemical', 'mpt_material'].includes(sr_table_unique_id)) {
            let batchStock = selectedOption.data('stock_qty') || 0;
            jQuery('#io_stock_qty').val(parseFloat(batchStock).toFixed(3));
            jQuery('#dc_qty').attr('readonly', false).prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
            jQuery('#DCDetailsForm #dc_qty').removeClass('skip-tab');
            setTimeout(function () {
                jQuery('#dc_qty').focus();
            }, 100);
        } else {
            jQuery('#dc_qty').val(parseFloat(1).toFixed(3)).attr('readonly', true).prop('tabindex', -1);
        }
    } else {
        jQuery('#sr_table_unique_id').val('');
        jQuery('#dc_qty').val('').attr('readonly', false).removeAttr('tabindex');
        let itemSelected = jQuery('#item_id').find('option:selected');
        let io_stock_qty = itemSelected.data('io_stock_qty') || 0;
        jQuery('#io_stock_qty').val(parseFloat(io_stock_qty).toFixed(3));
    }
});

function resetFieds() {

    jQuery("#DCDetailsForm #sr_table_unique_id").val('');
    jQuery("#DCDetailsForm #dc_detail_id").val('');
    jQuery("#DCDetailsForm #stock_rate_unit").val('');
    jQuery("#DCDetailsForm #amount").val('');
    jQuery("#item_id").val("").removeClass('skip-tab');
    jQuery("#sr_table_pk_id").val("").removeClass('skip-tab');
    dc_details_data = [];
    jQuery('#DCDetailTable tbody').empty();
    jQuery('#item_id option.temp-item').remove();
    jQuery('#sr_table_pk_id option.temp-sr_table_pk_id').remove();

}


jQuery('#item_id').on('change', function () {
    let selected = jQuery(this).find('option:selected');
    let main_group = selected.data('main_group');
    let item_group = selected.data('item_group');
    let io_stock_qty = selected.data('io_stock_qty');
    let unit = selected.data('unit');
    let item_id = selected.val();
    if (selected.val() != "") {
        jQuery('#item_group').val(item_group);
        jQuery('#main_group').val(main_group);
        jQuery('#stock_rate_unit').val(stock_rate_unit);
        jQuery('#io_stock_qty').val(io_stock_qty ? parseFloat(io_stock_qty).toFixed(3) : parseFloat(0).toFixed(3));
        jQuery('#io_stock_qty_unit').text(unit).addClass('ms-1');
        jQuery('#dc_qty_unit').text(unit).addClass('ms-1');

        if (main_group == 'Industrial X-Ray Films' || main_group == 'General') {

            jQuery('#DCDetailsModal #sr_table_pk_id').empty().append('<option value="">Select Sr. No.</option>');
            setSelect2Readonly("#DCDetailsModal #sr_table_pk_id", true);
            jQuery("#DCDetailsModal #sr_table_pk_id").addClass('skip-tab');
            var dcInput = jQuery('#DCDetailsModal #dc_qty');
            dcInput.val('').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex', -1);

        } else {
            setSelect2Readonly('#DCDetailsModal #sr_table_pk_id', false);
            jQuery("#DCDetailsModal #sr_table_pk_id").removeClass('skip-tab');
            getSrNo(main_group, item_id);
            var dcInput = jQuery('#DCDetailsModal #dc_qty');
            dcInput.val('').prop('readonly', true).removeClass('skip-tab').prop('tabindex', -1);
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

    } else {
        jQuery('#item_group').val('');
        jQuery('#main_group').val('');
        jQuery('#io_stock_qty').val('');
        jQuery('#dc_qty').val('');
        jQuery('#io_stock_qty_unit').text('').removeClass('ms-1');
        jQuery('#pend_pi_qty_unit').text('').removeClass('ms-1');
        jQuery('#dc_qty_unit').text('').removeClass('ms-1');
        jQuery('#DCDetailsModal #sr_table_pk_id').empty().append('<option value="">Select Sr. No.</option>');
        // jQuery('#DCDetailsForm #sr_table_pk_id').addClass('skip-tab');
        setSelect2Readonly("#DCDetailsModal #sr_table_pk_id", true);
        jQuery('#dc_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        jQuery('#dc_qty').attr('onblur', 'formatPoints(this, 3)');
        jQuery('#stock_rate_unit').val('');
        jQuery('#io_stock_qty').val('');
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
                        options += `<option data-sr_table_unique_id="${data.sr_no_data[idx].sr_table_unique_id}" data-stock_qty="${data.sr_no_data[idx].sr_qty || 0}" data-current_location_id="${data.sr_no_data[idx].current_location_id || ''}" value="${data.sr_no_data[idx].sr_table_pk_id}">${data.sr_no_data[idx].name_for_display}</option>`;
                    }
                }
                let $dropdown = jQuery('#DCDetailsForm #sr_table_pk_id');
                $dropdown.empty().append(options);

                // Set value AFTER options loaded
                if (selected_sr) {
                    $dropdown.val(selected_sr);
                }
                $dropdown.trigger('change.select2');
                let selectedOption = $dropdown.find('option:selected');
                jQuery('#sr_table_unique_id').val(selectedOption.data('sr_table_unique_id') || '');
            } else {
                jQuery('#DCDetailsForm #sr_table_pk_id').empty().append(options).trigger('change.select2');
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


// Purchase Order Details Form Submit Start
$('#DCDetailsForm').on('submit', function (e) {

    e.preventDefault();
    let form = this;

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    var data = new FormData(document.getElementById('DCDetailsForm'));
    var formValue = Object.fromEntries(data.entries());
    var thisModal = jQuery('#DCDetailsModal');

    var main_group = formValue.main_group ? formValue.main_group : '';
    var sr_table_pk_id = formValue.sr_table_pk_id ? formValue.sr_table_pk_id : '';

    if (!["Industrial X-Ray Films", "General"].includes(main_group)) {
        if (sr_table_pk_id == "") {
            toastr.error('Select Sr. No.');
            return;
        }

    }

    var dc_qty = formValue.dc_qty ? parseFloat(formValue.dc_qty) : 0;
    let ioStock = parseFloat(formValue.io_stock_qty || 0);
    let MaxQty = Math.min(ioStock);
    let reason = "";

    if (dc_qty > MaxQty) {
        toastr.error(`DC Qty. Cannot Be Greater Than Stock ${ioStock.toFixed(3)}`);
        return;
    };
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

        var noDuplicate = true;
        var sr_table_pk_id = formValue.sr_table_pk_id ? formValue.sr_table_pk_id : null;
        var item_id = formValue.item_id ? formValue.item_id : null;

        var currentIndex = formValue.form_type === "edit" ? formValue.form_index : null;

        jQuery.each(dc_details_data, function (index, item) {
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
            var item_id = formValue.item_id ? formValue.item_id : '';
            var item_name = formValue.item_name ? formValue.item_name : thisModal.find('#item_id option[value="' + item_id + '"]').text();
            var item_group = formValue.item_group != "" ? formValue.item_group : '';
            var main_group = formValue.main_group != "" ? formValue.main_group : '';
            var io_stock_qty = parseFloat(formValue.io_stock_qty || 0).toFixed(3);
            var dc_qty = formValue.dc_qty ? parseFloat(formValue.dc_qty).toFixed(3) : parseFloat(0).toFixed(3);
            var pod_unit = formValue.pod_unit != "" ? formValue.pod_unit : '';
            var sr_table_pk_id = formValue.sr_table_pk_id ? formValue.sr_table_pk_id : '';
            var name_for_display = sr_table_pk_id != "" ? formValue.name_for_display ? formValue.name_for_display : thisModal.find('#sr_table_pk_id option:selected').text() : "";
            var unit = thisModal.find('#io_stock_qty_unit').text();

            var remark = formValue.remark ? formValue.remark : "";
            formValue.name_for_display = sr_table_pk_id != "" ? name_for_display : "";
            formValue.unit = unit;
            if (item_id != "") {
                if (formValue.form_type == "edit") {
                    formValue.item_name = item_name;
                    formValue.mode = formValue.dc_detail_id == 0 ? "Insert" : "Update";
                    dc_details_data[formValue.form_index] = {
                        ...dc_details_data[formValue.form_index],
                        ...formValue
                    };
                    let tblHtml = ``;
                    tblHtml += `<td>`;
                    var in_use = dc_details_data[formValue.form_index].in_use == true ? true : false;
                    tblHtml += in_use == true ? DetailsActionDropdown('editDCDetails') : DetailsActionDropdown('editDCDetails', 'removeDCDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                    </td>`;
                    tblHtml += `<td>${item_name}</td>`;
                    tblHtml += `<td>${item_group}</td>`;
                    tblHtml += `<td>${main_group}</td>`;
                    tblHtml += `<td>${name_for_display}</td>`;
                    tblHtml += `<td>${io_stock_qty}</td>`;
                    tblHtml += `<td>${dc_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#DCDetailTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
                    toastSuccess("Record Updated.");
                } else {
                    formValue.mode = "Insert";
                    formValue.dc_detail_id = 0;
                    dc_details_data.push(formValue)

                    let formIndx = dc_details_data.indexOf(formValue);
                    if (jQuery('#DCDetailTable tbody').find('#noDetails').length > 0) {
                        jQuery('#DCDetailTable tbody').empty();
                    }

                    let tblHtml = `<tr>`;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editDCDetails', 'removeDCDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
                    </td>`;
                    tblHtml += `<td>${item_name}</td>`;
                    tblHtml += `<td>${item_group}</td>`;
                    tblHtml += `<td>${main_group}</td>`;
                    tblHtml += `<td>${name_for_display}</td>`;
                    tblHtml += `<td>${io_stock_qty}</td>`;
                    tblHtml += `<td>${dc_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#DCDetailTable tbody').append(tblHtml);
                    toastSuccess("Record Inserted.");
                }
            }
            if (formValue.form_type == "edit") {
                thisModal.modal('hide');
            } else {
                let formElement = document.getElementById('DCDetailsForm');
                formElement.reset();
                jQuery('#item_id').val('').trigger('change.select2');
                jQuery('#io_stock_qty').val('');
                jQuery('#item_group').val('');
                jQuery('#main_group').val('');
                jQuery('#dc_qty').val('');
                jQuery('#remark').val('');
                jQuery('#io_stock_qty_unit').text('').removeClass('ms-1');
                jQuery('#dc_qty_unit').text('').removeClass('ms-1');
                var options = `<option value="">Select Sr. No.</option>`;
                jQuery('#DCDetailsForm #sr_table_pk_id').empty().append(options);
                setSelect2Readonly('#DCDetailsForm #sr_table_pk_id', true);
                jQuery('#DCDetailsForm #sr_table_pk_id').addClass('skip-tab');
                setTimeout(function () {

                    let $select = jQuery('#DCDetailsForm #item_id');
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

function editDCDetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();
    fillDCDetailsFrom(formIndx, rawIndx);
}

//  details form edit
function fillDCDetailsFrom(formIndx, rawIndx) {
    let thisForm = jQuery('#DCDetailsModal');
    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    thisForm.find('#item_id option.temp-item').remove();
    thisForm.find('#sr_table_pk_id option.temp-sr_table_pk_id').remove();
    var frmData = dc_details_data[formIndx];


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

    thisForm.find("#dc_detail_id").val(frmData.dc_detail_id);
    thisForm.find("#item_id").val(zeroToEmpty(frmData.item_id)).trigger('change');
    thisForm.find("#io_stock_qty").val(parseFloat(frmData.io_stock_qty || 0).toFixed(3));
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
    thisForm.find("#remark").val(frmData.remark ? frmData.remark : "");
    thisForm.find("#dc_qty").attr('max', minQty.toFixed(3));
    // getSrNo();
    getSrNo(frmData.main_group, frmData.item_id, frmData.sr_table_pk_id).done(function () {
        if (thisForm.find("#sr_table_pk_id option[value='" + sr_table_pk_id + "']").length === 0 && name_for_display != undefined && sr_table_pk_id != 0) {
            thisForm.find("#sr_table_pk_id").append(
                `<option  class="temp-sr_table_pk_id" value="${sr_table_pk_id}" data-sr_table_unique_id="${frmData.sr_table_unique_id}" data-stock_qty="${frmData.io_stock_qty || 0}" selected>${name_for_display}</option>`
            );
        }
        jQuery("#DCDetailsForm #sr_table_pk_id").val(frmData.sr_table_pk_id ? zeroToEmpty(frmData.sr_table_pk_id) : "").trigger('change.select2');
        if (['dpt_chemical', 'mpt_material'].includes(frmData.sr_table_unique_id) || ['Industrial X-Ray Films', 'General'].includes(frmData.main_group)) {
            thisForm.find('#dc_qty').attr('readonly', false).prop('readonly', false).removeAttr('tabindex');
            thisForm.find('#dc_qty').focus();
        } else {
            thisForm.find('#dc_qty').attr('readonly', true).prop('readonly', true).prop('tabindex', -1);
        }
        jQuery("#DCDetailsForm #sr_table_unique_id").val(frmData.sr_table_unique_id ? zeroToEmpty(frmData.sr_table_unique_id) : "");
    });

    if (frmData.in_use == true) {

        thisForm.find("#dc_qty").attr('min', parseFloat(frmData.used_qty).toFixed(3));

    }

    if (frmData.dc_detail_id > 0) {
        $table_pk_id = thisForm.find('#sr_table_pk_id').addClass('skip-tab');

    } else {
        $table_pk_id_else = thisForm.find('#sr_table_pk_id').removeClass('skip-tab');

    }

    thisForm.modal('show');
}

function removeDCDetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        let item = dc_details_data[formIndx];
        if (item.dc_detail_id && item.dc_detail_id != 0) {
            item.mode = "Delete";
        } else {
            dc_details_data.splice(formIndx, 1);
        }
        removeFormObj();

    });
}

function removeFormObj(formIndx) {
    jQuery('#DCDetailTable tbody').empty();
    fillDCDetailTable();
}


// edit time fill table start
function fillDCDetailTable() {
    let thisModal = jQuery('#DCDetailsModal');
    jQuery('#DCDetailTable tbody').empty();
    if (dc_details_data.length > 0) {
        var tblHtml = '';
        for (let key in dc_details_data) {
            if (dc_details_data[key].mode == "Delete") {
                continue;
            }
            let formIndx = dc_details_data.indexOf(dc_details_data[key]);
            var item_id = dc_details_data[key].item_id ? dc_details_data[key].item_id : '';
            var item_name = item_id != '' ? thisModal.find('#item_id option[value="' + item_id + '"]').text() || dc_details_data[key].item_name : '';
            var item_group = dc_details_data[key].item_group != "" ? dc_details_data[key].item_group : '';
            var main_group = dc_details_data[key].main_group != "" ? dc_details_data[key].main_group : '';
            var io_stock_qty = parseFloat(dc_details_data[key].io_stock_qty || 0).toFixed(3);
            var dc_qty = dc_details_data[key].dc_qty ? parseFloat(dc_details_data[key].dc_qty).toFixed(3) : parseFloat(0).toFixed(3);
            var sr_table_pk_id = dc_details_data[key].sr_table_pk_id ? dc_details_data[key].sr_table_pk_id : '';

            var name_for_display = sr_table_pk_id != "" ? dc_details_data[key].name_for_display ? dc_details_data[key].name_for_display : thisModal.find('#sr_table_pk_id option[value="' + sr_table_pk_id + '"]').text().trim() : "";

            var unit = dc_details_data[key].unit != "" ? dc_details_data[key].unit : '';

            var remark = dc_details_data[key].remark ? dc_details_data[key].remark : "";
            var in_use = dc_details_data[key].in_use == true ? true : false;

            if (jQuery('#DCDetailTable tbody').find('#noDetails').length > 0) {
                jQuery('#DCDetailTable tbody').empty();
            }
            tblHtml += `<tr>`;
            tblHtml += `<td>`;
            tblHtml += in_use == true ? DetailsActionDropdown('editDCDetails') : DetailsActionDropdown('editDCDetails', 'removeDCDetails');
            tblHtml += ` <input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;
            tblHtml += `<td>${item_name}</td>`;
            tblHtml += `<td>${item_group}</td>`;
            tblHtml += `<td>${main_group}</td>`;
            tblHtml += `<td>${name_for_display}</td>`;
            tblHtml += `<td>${io_stock_qty}</td>`;
            tblHtml += `<td>${dc_qty ?? ''}</td>`;
            tblHtml += `<td>${unit}</td>`;
            tblHtml += `<td>${remark}</td>`;
            tblHtml += `</tr>`;

        }
        jQuery('#DCDetailTable tbody').empty().append(tblHtml);
    }
    // calculateTotalAmount();
}

// Main Delivery Challan Form Submit
$('#commonDCCustomerForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#DcCustomerModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;

    var dateValue = document.getElementById("dc_date").value.trim();

    // validatePercentage
    if (!isValidDate(dateValue)) {
        toastr.error("Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#DcCustomerModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#DcCustomerModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#DcCustomerModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    jQuery('#DcCustomerModal').find('#submitbtn').prop('disabled', true);

    var formIdnew = jQuery('#DcCustomerModal').find('#commonDCCustomerForm').find('#id').val();
    var formUrl = formIdnew != undefined && formIdnew != "" ? "update-delivery_challan_customer" : "store-delivery_challan_customer";
    var data = new FormData(form);
    data.append('dc_details_data', JSON.stringify(dc_details_data ?? []));
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));

    let validData = dc_details_data.filter(item => item.mode !== "Delete");

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

        });

        if (!isValidDetails) {
            toastr.error(errorMsg);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');

            jQuery('#DcCustomerModal')
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
                        jQuery('#DcCustomerModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formIdnew == undefined || formIdnew == "") {
                        function nextFn() {
                            document.getElementById("commonDCCustomerForm").reset();
                            const form = document.getElementById("commonDCCustomerForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            jQuery('#dc_sequence').prop('readonly', false).focus();
                            $("#customer_id").val('').trigger('change');
                            $("#mode_of_transport").val('');
                            $("#vehicle_no").val('');
                            $("#transporter").val('');
                            $("#sp_note").val('');
                            dc_details_data = [];
                            resetFieds();
                            jQuery('#DCDetailTable tbody').empty();
                            getLatestDeliveryChallanNo();
                        }

                        if (data.url != "") {
                            toastSuccessPreview(data.response_message, data.url, nextFn);
                        } else {
                            toastSuccess(data.response_message, nextFn);
                        }
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#DcCustomerModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#DcCustomerModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#DcCustomerModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#DcCustomerModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#DcCustomerModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        toastr.error('Add At Least One Delivery Challan Detail.');

        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#DcCustomerModal').find('#submitbtn').prop('disabled', false);
    }
});
// Main Form Submit End


// get the latest number
function getLatestDeliveryChallanNo() {
    jQuery.ajax({
        url: "get-latest_delivery_challan_customer_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#dc_number').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#dc_sequence').val(data.number);
                jQuery('#dc_date').val(currentDate);
                jQuery('#DcCustomerModal').find('#customer_id').removeClass('skip-tab');
                setSelect2Readonly('#DcCustomerModal #customer_id', false);
                jQuery('#dc_sequence').focus();
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
jQuery('#commonDCCustomerForm').find('#dc_sequence').on('change', function () {

    checkSequence();
});
function checkSequence() {

    let thisForm = jQuery('#commonDCCustomerForm');
    let val = thisForm.find('#dc_sequence').val();

    if (val != "") {

        if (val > 0 == false) {
            toastr.error('Enter Valid Inter Delivery Challan No.');
            jQuery('#dc_sequence').parent().parent().parent('div.control-group').addClass('error');
            jQuery('#dc_sequence').focus();
            jQuery('#dc_sequence').val('');

        } else {
            jQuery('#dc_sequence').addClass('file-loader');
            jQuery('#dc_sequence').parent().parent().parent('div.control-group').removeClass('error');

            var urL = "check-delivery_challan_customer_number_duplication?for=add&dc_sequence=" + val;

            var formId = jQuery('#commonDCCustomerForm').find('input[name="id"]').val();

            if (formId !== undefined) { //if form is edit
                urL = "check-delivery_challan_customer_number_duplication?for=edit&dc_sequence=" + val + "&id=" + formId;
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
                        jQuery('#commonDCCustomerForm #dc_sequence').val('');
                        const input = document.getElementById('dc_sequence'); input?.focus();
                    } else {
                        jQuery('#commonDCCustomerForm #dc_number').val(data.latest_no);
                        jQuery('#commonDCCustomerForm #dc_sequence').val(val);
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