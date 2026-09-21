
pi_details_data = [];
var formId = jQuery('#commonPurchaseIndentForm').find('input[name="id"]').val();
var defaultPurchaseIndentLocation = jQuery('#to_location_id').val() || '';


// Edit purchase Indent row click
jQuery('#dyntable tbody').on('click', '.edit-purchase_indent', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#PurchaseIndentModal').find('#id').val(data["pi_id"]);
    if (data && data["pi_id"]) {
        fetchAndFillPurchaseIndent(data["pi_id"]);
    }
});

// Function to fetch and fill purchase indent data
function fetchAndFillPurchaseIndent(id) {
    if (!id) return;
    jQuery('#PurchaseIndentModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-purchase_indent",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.pi_data != null) {
                jQuery('#PurchaseIndentModal').find('#id').val(data.pi_data.pi_id != "" ? data.pi_data.pi_id : "");
                let url = checkFileRoute + "?id=" + data.pi_data.pi_id + "&name=" + data.pi_data.pdf_name + "&type=purchase_indent";
                jQuery('#preview_btn').attr('href', url).show();
                jQuery('#PurchaseIndentModal').find('#pi_no').val(data.pi_data.pi_no != "" ? data.pi_data.pi_no : "");
                jQuery('#PurchaseIndentModal').find('#pi_date').val(data.pi_data.pi_date != "" ? data.pi_data.pi_date : "");
                jQuery('#PurchaseIndentModal').find('#old_pi_date').val(data.pi_data.pi_date != "" ? data.pi_data.pi_date : "");
                jQuery('#PurchaseIndentModal').find('#pi_sequence').val(data.pi_data.pi_sequence != "" ? data.pi_data.pi_sequence : "");

                jQuery('#PurchaseIndentModal').find('#to_location_id').val(data.pi_data.to_location_id).trigger('change.select2');
                if (data.pi_data.in_use == true) {
                    jQuery('#PurchaseIndentModal').find('#to_location_id').addClass('skip-tab');
                    setSelect2Readonly('#PurchaseIndentModal #to_location_id', true);
                } else {
                    jQuery('#PurchaseIndentModal').find('#to_location_id').removeClass('skip-tab');
                    setSelect2Readonly('#PurchaseIndentModal #to_location_id', false);
                }
                jQuery('#PurchaseIndentModal').find('#indent_by_user_id').val(data.pi_data.indent_by_user_id).trigger('change.select2');

                jQuery('#PurchaseIndentModal').find('#special_note').val(data.pi_data.special_note != "" ? data.pi_data.special_note : "");

                if (data.pi_details_data != "" && data.pi_details_data.length > 0) {
                    pi_details_data = data.pi_details_data.map(item => {
                        item.mode = "Update";
                        return item;
                    });
                    fillPurchaseIndentDetailsTable();
                }

                jQuery('#PurchaseIndentModal').find('#submitbtn').prop('disabled', false);

                const form = document.getElementById("commonPurchaseIndentForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#PurchaseIndentModal').find('#add_new').show();
                jQuery('#PurchaseIndentModal').find('#preview_btn').show();

                if (data.pi_data.in_use == true) {
                    jQuery('#PurchaseIndentModal').find('#pi_sequence').prop('readonly', true);
                    let nextInput = jQuery('#PurchaseIndentModal').find('#pi_date');
                    if (nextInput.hasClass('trans-date-picker') || nextInput.hasClass('date-picker')) {
                        nextInput.one('focus', function () {
                            setTimeout(() => {
                                jQuery(this).datepicker('hide');
                            }, 50);
                        });
                    }
                    nextInput.focus();
                } else {
                    jQuery('#PurchaseIndentModal').find('#pi_sequence').prop('readonly', false);
                    jQuery('#PurchaseIndentModal').find('#pi_sequence').focus();
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

// Reset button click for purchase indent modal
jQuery('#resetbtn').on('click', function () {
    pi_details_data = [];
    jQuery('#PurchaseIndentDetailsTable tbody').empty();
    jQuery('#PurchaseIndentDetailsTable tbody').append(`
        <tr>
            <td colspan="8" class="text-center" id="noDetails">
                No Material Indent Details Added
            </td>
        </tr>
    `);

    var formId = jQuery('#PurchaseIndentModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonPurchaseIndentForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        jQuery('#indent_by_user_id').val(loginUserId).trigger("change.select2");
        jQuery('#pi_sequence').prop('readonly', false).focus();
        jQuery('#PurchaseIndentModal').find("#to_location_id").val(defaultPurchaseIndentLocation).trigger('change.select2');
        getLatestPurchaseIndentNo();
    } else {
        fetchAndFillPurchaseIndent(formId);
    }
});

jQuery('#item_id').on('change', function () {
    let selected = jQuery(this).find('option:selected');
    let main_group = selected.data('main_group');
    let item_group = selected.data('item_group');
    let io_stock_qty = selected.data('io_stock_qty');
    let unit = selected.data('unit');
    if (selected.val() != "") {
        jQuery('#item_group').val(item_group);
        jQuery('#main_group').val(main_group);
        jQuery('#io_stock_qty').val(io_stock_qty ? parseFloat(io_stock_qty).toFixed(3) : parseFloat(0).toFixed(3));
        jQuery('#io_stock_qty_unit').text(unit).addClass('ms-1');
        jQuery('#indent_qty_unit').text(unit).addClass('ms-1');

        if (main_group == 'Industrial X-Ray Films') {
            jQuery('#indent_qty').removeClass('isNumberKey').addClass('isNumberKeyNotDot');
            jQuery('#indent_qty').attr('onblur', 'formatPoints(this, 0)');
            if (jQuery('#indent_qty').val() !== '') {
                jQuery('#indent_qty').val(parseInt(jQuery('#indent_qty').val()) || '');
            }
        } else {
            jQuery('#indent_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
            jQuery('#indent_qty').attr('onblur', 'formatPoints(this, 3)');
        }
    } else {
        jQuery('#item_group').val('');
        jQuery('#main_group').val('');
        jQuery('#io_stock_qty').val('');
        jQuery('#io_stock_qty_unit').text('').removeClass('ms-1');
        jQuery('#indent_qty_unit').text('').removeClass('ms-1');
        jQuery('#indent_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
    }
});

jQuery('#PurchaseIndentModal').on('show.bs.modal', function () {
    var formId = jQuery('#commonPurchaseIndentForm').find('input[name="id"]').val();
    var hasAccess = jQuery('#commonPurchaseIndentForm').find('#has_access').val();
    jQuery('#PurchaseIndentModal').find('#pi_sequence').prop('readonly', false);



    jQuery('#indent_by_user_id').val(loginUserId).trigger("change");

    if (formId == "" || formId == undefined) {
        getLatestPurchaseIndentNo();
        jQuery('#to_location_id').val(defaultPurchaseIndentLocation).trigger('change.select2');
        jQuery('#PurchaseIndentModal').find('#add_new').hide();
        jQuery('#PurchaseIndentModal').find('#preview_btn').hide();
        const input = document.getElementById('pi_sequence');
        input?.focus();
    } else {
        jQuery('#PurchaseIndentModal').find('#add_new').show();
        jQuery('#PurchaseIndentModal').find('#preview_btn').show();

    }
});

jQuery('#PurchaseIndentDetailsModal').on('show.bs.modal', function () {
    let thisModal = jQuery('#PurchaseIndentDetailsModal');
    var focusremoveid = jQuery('#PurchaseIndentModal').find('#commonPurchaseIndentForm').find('#id').val();
    var form_type = thisModal.find("#form_type").val();
    var details_id = thisModal.find("#pid_id").val();
    setTimeout(() => {
        if (form_type == "edit" && details_id != 0) {
            setSelect2Readonly("#PurchaseIndentDetailsForm #item_id", true);
        } else {
            setSelect2Readonly("#PurchaseIndentDetailsForm #item_id", false);
        }
    }, 500);

});

jQuery('#PurchaseIndentModal').on('hide.bs.modal', function (e) {
    jQuery('#PurchaseIndentDetailsTable tbody').empty();
    let thisModal = jQuery('#PurchaseIndentModal');
    thisModal.find("#id").val("");
    var thisForm = jQuery('#PurchaseIndentDetailsModal');
    thisForm.find("#pid_id").val(0);
    pi_details_data = [];
    jQuery("#PurchaseIndentModal #to_location_id").removeClass('skip-tab');
    setSelect2Readonly('#PurchaseIndentModal #to_location_id', false);

    jQuery('#PurchaseIndentModal').find('#add_new').hide();
    jQuery('#PurchaseIndentModal').find('#preview_btn').hide();

    var formElement = document.getElementById("commonPurchaseIndentForm");
    if (formElement) {
        formElement.reset();
        jQuery(formElement).removeClass('was-validated');
    }
    jQuery('#to_location_id').val(defaultPurchaseIndentLocation).trigger('change.select2');
    jQuery('#indent_by_user_id').val(loginUserId).trigger('change.select2');
});


jQuery('#PurchaseIndentDetailsModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#PurchaseIndentDetailsModal');
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    thisModal.find("#pid_id").val(0);
    jQuery(this).find('#item_id option.temp-item').remove();
    jQuery('#PurchaseIndentDetailsForm').trigger("reset");
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('special_note');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }

    jQuery('#PurchaseIndentDetailsForm #indent_qty').removeAttr('min');
});

jQuery('#PurchaseIndentModal').on('click', '#add_new', function () {
    jQuery('#PurchaseIndentModal').find('#id').val('');
    document.getElementById("commonPurchaseIndentForm").reset();
    const form = document.getElementById("commonPurchaseIndentForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#indent_by_user_id').val(loginUserId).trigger("change.select2");
    jQuery('#pi_sequence').focus();
    getLatestPurchaseIndentNo();
    jQuery('#PurchaseIndentDetailsTable tbody').empty();
    jQuery('#PurchaseIndentDetailsTable tbody').append(`
        <tr>
            <td colspan="8" class="text-center" id="noDetails">
                No Material Indent Details Added
            </td>
        </tr>
    `);
    pi_details_data = [];
    jQuery("#to_location_id").val(defaultPurchaseIndentLocation).trigger('change.select2');
    jQuery('#PurchaseIndentModal').find('#add_new').hide();
    jQuery('#PurchaseIndentModal').find('#preview_btn').hide();
});



// Main Purchase Order Form Submit
$('#commonPurchaseIndentForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#PurchaseIndentModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;


    var dateValue = document.getElementById("pi_date").value.trim();
    if (!isValidDate(dateValue)) {
        toastr.error("Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PurchaseIndentModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PurchaseIndentModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PurchaseIndentModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    jQuery('#PurchaseIndentModal').find('#submitbtn').prop('disabled', true);

    var formIdnew = jQuery('#PurchaseIndentModal').find('#commonPurchaseIndentForm').find('#id').val();
    var formUrl = formIdnew != undefined && formIdnew != "" ? "update-purchase_indent" : "store-purchase_indent";
    var data = new FormData(form);
    data.append('pi_details_data', JSON.stringify(pi_details_data ?? []));
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));
    let validData = pi_details_data.filter(item => item.mode !== "Delete");
    if (validData.length > 0) {
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
                        jQuery('#PurchaseIndentModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formIdnew == undefined || formIdnew == "") {
                        function nextFn() {
                            document.getElementById("commonPurchaseIndentForm").reset();
                            const form = document.getElementById("commonPurchaseIndentForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            jQuery('#pi_sequence').prop('readonly', false).focus();
                            $("#to_location_id").val(defaultPurchaseIndentLocation).trigger('change.select2');
                            $("#indent_by_user_id").val(loginUserId).trigger('change.select2');
                            pi_details_data = [];
                            jQuery('#PurchaseIndentDetailsTable tbody').empty();
                            getLatestPurchaseIndentNo();
                        }
                        if (data.url != "") {
                            toastSuccessPreview(data.response_message, data.url, nextFn);
                        } else {
                            toastSuccess(data.response_message, nextFn);
                        }
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#PurchaseIndentModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#PurchaseIndentModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#PurchaseIndentModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#PurchaseIndentModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#PurchaseIndentModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {

        toastr.error('Add At Least One Material Indent Details.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PurchaseIndentModal').find('#submitbtn').prop('disabled', false);
    }
});
// Main Form Submit End



// Purchase Order Details Form Submit Start
$('#PurchaseIndentDetailsForm').on('submit', function (e) {

    e.preventDefault();
    let form = this;


    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    var data = new FormData(document.getElementById('PurchaseIndentDetailsForm'));
    var formValue = Object.fromEntries(data.entries());
    var thisModal = jQuery('#PurchaseIndentDetailsModal');

    var indent_qty = formValue.indent_qty ? parseFloat(formValue.indent_qty) : 0;
    var io_stock_qty = formValue.io_stock_qty ? parseFloat(formValue.io_stock_qty) : 0;
    var min = parseFloat($('#indent_qty').attr('min')) || 0;

    if (indent_qty < min) {
        toastr.error('Indent Qty. Cannot Be Less Than ' + min.toFixed(3));
        return;
    }

    if (indent_qty < 0.001) {
        toastr.error('Enter Indent Qty. greater than 0.001.');
        return;
    }

    // if (indent_qty > io_stock_qty) {
    //     toastr.error('Indent Qty. cannot be greater than Stock Qty.');
    //     return;
    // }

    if (formValue.item_id.trim()) {

        var noDuplicate = true;

        var item_id = formValue.item_id ? formValue.item_id : null;

        var currentIndex = formValue.form_type === "edit" ? formValue.form_index : null;

        var validData = pi_details_data.filter(item => item.mode !== "Delete");

        jQuery.each(validData, function (index, item) {
            if (item.item_id == item_id) {
                if (currentIndex === null || index != currentIndex) {
                    noDuplicate = false;
                    return false;
                }
            }
        });

        if (!noDuplicate) {
            toastr.error('Duplicate Item Found.');
            return;
        }

        if (noDuplicate) {
            var item_id = formValue.item_id ? formValue.item_id : null;
            // var item_name = item_id != '' ? thisModal.find('#item_id option[value="' + item_id + '"]').text() : '';
            var item_name = formValue.item_name ? formValue.item_name : thisModal.find('#item_id option[value="' + item_id + '"]').text();
            var item_group = formValue.item_group != null ? formValue.item_group : null;
            var main_group = formValue.main_group != null ? formValue.main_group : null;
            var indent_qty = formValue.indent_qty != 0 ? parseFloat(formValue.indent_qty).toFixed(3) : parseFloat(0).toFixed(3);
            var io_stock_qty = formValue.io_stock_qty != 0 ? parseFloat(formValue.io_stock_qty).toFixed(3) : parseFloat(0).toFixed(3);
            var remark = formValue.remark != null ? formValue.remark : '';
            var unit = formValue.unit != null ? formValue.unit : thisModal.find("#indent_qty_unit").text();
            if (item_id != "") {
                if (formValue.form_type == "edit") {
                    formValue.item_name = item_name;
                    formValue.mode = formValue.pid_id == 0 ? "Insert" : "Update";
                    // pi_details_data[formValue.form_index] = formValue;
                    pi_details_data[formValue.form_index] = {
                        ...pi_details_data[formValue.form_index],
                        ...formValue
                    };
                    var in_use = pi_details_data[formValue.form_index].in_use == true ? true : false;
                    let tblHtml = ``;
                    tblHtml += `<td>`;
                    tblHtml += in_use == true ? DetailsActionDropdown('editPurchaseIndentDetails') : DetailsActionDropdown('editPurchaseIndentDetails', 'removePurchaseIndentDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                    </td>`;

                    tblHtml += `<td>${item_name}</td>`;
                    tblHtml += `<td>${item_group}</td>`;
                    tblHtml += `<td>${main_group}</td>`;
                    tblHtml += `<td>${io_stock_qty}</td>`;
                    tblHtml += `<td>${indent_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#PurchaseIndentDetailsTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
                    toastSuccess("Record Updated.");
                } else {
                    formValue.pid_id = 0;
                    formValue.mode = "Insert";
                    formValue.unit = unit;
                    pi_details_data.push(formValue)
                    let formIndx = pi_details_data.indexOf(formValue);
                    if (jQuery('#PurchaseIndentDetailsTable tbody').find('#noDetails').length > 0) {
                        jQuery('#PurchaseIndentDetailsTable tbody').empty();
                    }

                    let tblHtml = `<tr>`;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editPurchaseIndentDetails', 'removePurchaseIndentDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
                    </td>`;

                    tblHtml += `<td>${item_name}</td>`;
                    tblHtml += `<td>${item_group}</td>`;
                    tblHtml += `<td>${main_group}</td>`;
                    tblHtml += `<td>${io_stock_qty}</td>`;
                    tblHtml += `<td>${indent_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#PurchaseIndentDetailsTable tbody').append(tblHtml);
                    toastSuccess("Record Inserted.");
                }
            }
            if (formValue.form_type == "edit") {
                thisModal.modal('hide');
            } else {
                let formElement = document.getElementById('PurchaseIndentDetailsForm');
                formElement.reset();
                jQuery('#item_id').val('').trigger('change');
                jQuery('#item_group').val('');
                jQuery('#main_group').val('');
                jQuery('#io_stock_qty').val('');
                jQuery('#indent_qty').val('');
                jQuery('#remark').val('');

                setTimeout(function () {

                    let $select = jQuery('#PurchaseIndentDetailsForm #item_id');
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


function editPurchaseIndentDetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();
    fillPurchaseIndentDetailsForm(formIndx, rawIndx);
}

// purchase order details form edit
function fillPurchaseIndentDetailsForm(formIndx, rawIndx) {
    let thisForm = jQuery('#PurchaseIndentDetailsModal');
    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    var frmData = pi_details_data[formIndx];
    thisForm.find("#pid_id").val(frmData.pid_id);
    thisForm.find('#item_id option.temp-item').remove();
    // thisForm.find("#item_id").val(zeroToEmpty(frmData.item_id != "" ? frmData.item_id : "")).trigger('change');
    let itemId = frmData.item_id;
    let itemText = frmData.item_name;

    // check if option exists
    if (thisForm.find("#item_id option[value='" + itemId + "']").length === 0) {
        thisForm.find("#item_id").append(
            `<option  class="temp-item" value="${itemId}" data-main_group="${frmData.main_group}" data-item_group="${frmData.item_group}" data-io_stock_qty="${frmData.io_stock_qty}" selected>${itemText}</option>`
        );
    }
    if (frmData.in_use === true) {
        let usedQty = parseFloat(frmData.used_qty) || 0;
        thisForm.find("#indent_qty").attr('min', usedQty.toFixed(3));

    } else {
        thisForm.find("#indent_qty").attr('min', 0);
    }

    // set value
    thisForm.find("#item_id").val(itemId).trigger('change');
    var decPlaces = (frmData.main_group == 'Industrial X-Ray Films') ? 0 : 3;
    thisForm.find("#indent_qty").val(frmData.indent_qty != "" ? parseFloat(frmData.indent_qty).toFixed(decPlaces) : "");
    // thisForm.find("#io_stock_qty").val(frmData.io_stock_qty != "" ? parseFloat(frmData.io_stock_qty).toFixed(3) : "");
    thisForm.find("#remark").val(frmData.remark != "" ? frmData.remark : "");

    if (frmData.main_group == 'Industrial X-Ray Films') {
        thisForm.find('#indent_qty').removeClass('isNumberKey').addClass('isNumberKeyNotDot');
        thisForm.find('#indent_qty').attr('onblur', 'formatPoints(this, 0)');
    } else {
        thisForm.find('#indent_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        thisForm.find('#indent_qty').attr('onblur', 'formatPoints(this, 3)');
    }
    setTimeout(() => {

        if (frmData.pid_id != 0) {
            setSelect2Readonly("#PurchaseIndentDetailsForm #item_id", true);
        } else {
            setSelect2Readonly("#PurchaseIndentDetailsForm #item_id", false);
        }
    }, 200);

    if (thisForm.find("#pid_id").val() == frmData.pid_id) {
        thisForm.modal('show');
    }
}
function removePurchaseIndentDetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let index = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        // jQuery(th).closest("tr").remove();
        let item = pi_details_data[index];

        if (item.pid_id && item.pid_id != 0) {
            item.mode = "Delete";
        } else {
            pi_details_data.splice(index, 1);
        }
        removeFormObj();
    });
}

function removeFormObj() {
    // delete pi_details_data[formIndx];
    // pi_details_data = pi_details_data.filter(element => element != null);
    jQuery('#PurchaseIndentDetailsTable tbody').empty();
    fillPurchaseIndentDetailsTable();
}


// edit time fill table start
function fillPurchaseIndentDetailsTable() {
    let thisModal = jQuery('#PurchaseIndentDetailsModal');
    jQuery('#PurchaseIndentDetailsTable tbody').empty();

    if (pi_details_data.length > 0) {
        for (let key in pi_details_data) {
            if (pi_details_data[key].mode == "Delete") {
                continue;
            }
            let formIndx = pi_details_data.indexOf(pi_details_data[key]);
            var item_id = pi_details_data[key].item_id ? pi_details_data[key].item_id : '';
            // var item_name = item_id != '' ? thisModal.find('#item_id option[value="' + item_id + '"]').text() : '';
            var item_name = (pi_details_data[key].item_name !== undefined && pi_details_data[key].item_name !== null)
                ? pi_details_data[key].item_name
                : thisModal.find('#item_id option[value="' + item_id + '"]').text();
            var indent_qty = pi_details_data[key].indent_qty != 0 ? parseFloat(pi_details_data[key].indent_qty).toFixed(3) : parseFloat(0).toFixed(3);
            var io_stock_qty = pi_details_data[key].io_stock_qty != 0 ? parseFloat(pi_details_data[key].io_stock_qty).toFixed(3) : parseFloat(0).toFixed(3);
            var remark = pi_details_data[key].remark != "" ? pi_details_data[key].remark : "";
            var item_group = pi_details_data[key].item_group != "" ? pi_details_data[key].item_group : "";
            var main_group = pi_details_data[key].main_group != "" ? pi_details_data[key].main_group : "";
            var unit = pi_details_data[key].unit != "" ? pi_details_data[key].unit : "";
            var in_use = pi_details_data[key].in_use == true ? true : false;

            if (jQuery('#PurchaseIndentDetailsTable tbody').find('#noDetails').length > 0) {
                jQuery('#PurchaseIndentDetailsTable tbody').empty();
            }
            let tblHtml = `<tr>`;
            tblHtml += `<td>`;
            tblHtml += in_use == true ? DetailsActionDropdown('editPurchaseIndentDetails') : DetailsActionDropdown('editPurchaseIndentDetails', 'removePurchaseIndentDetails');
            tblHtml += ` <input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;

            tblHtml += `<td>${item_name}</td>`;
            tblHtml += `<td>${item_group}</td>`;
            tblHtml += `<td>${main_group}</td>`;
            tblHtml += `<td>${io_stock_qty}</td>`;
            tblHtml += `<td>${indent_qty}</td>`;
            tblHtml += `<td>${unit}</td>`;
            tblHtml += `<td>${remark}</td>`;
            tblHtml += `</tr>`;
            jQuery('#PurchaseIndentDetailsTable tbody').append(tblHtml);
        }
    }
}

// get the latest number
function getLatestPurchaseIndentNo() {
    jQuery.ajax({
        url: "get-latest_purchase_indent_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {

                jQuery('#pi_no').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#pi_sequence').val(data.number);
                jQuery('#pi_date').val(currentDate);
                jQuery("#PurchaseIndentModal #to_location_id").removeClass('skip-tab');
                setSelect2Readonly('#PurchaseIndentModal #to_location_id', false);

            } else {
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            jQuery('#pi_number').removeClass('file-loader');
            console.log('Field To Get Latest Indent No.!')
        }
    });
}

// check sequence number duplication
jQuery('#commonPurchaseIndentForm').find('#pi_sequence').on('change', function () {
    checkSequence();
});
function checkSequence() {

    let thisForm = jQuery('#commonPurchaseIndentForm');
    let val = thisForm.find('#pi_sequence').val();

    if (val != "") {

        if (val > 0 == false) {
            toastr.error('Enter Valid Indent No.');
            jQuery('#pi_sequence').parent().parent().parent('div.control-group').addClass('error');
            jQuery('#pi_sequence').focus();
            jQuery('#pi_sequence').val('');

        } else {
            jQuery('#pi_sequence').addClass('file-loader');
            jQuery('#pi_sequence').parent().parent().parent('div.control-group').removeClass('error');

            var urL = "check-purchase_indent_number_duplication?for=add&pi_sequence=" + val;

            var formId = jQuery('#commonPurchaseIndentForm').find('input[name="id"]').val();

            if (formId !== undefined) { //if form is edit
                urL = "check-purchase_indent_number_duplication?for=edit&pi_sequence=" + val + "&id=" + formId;
            }

            jQuery.ajax({
                url: urL,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    jQuery('#pi_sequence').removeClass('file-loader');
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        jQuery('#commonPurchaseIndentForm #pi_sequence').val('');
                        const input = document.getElementById('pi_sequence'); input?.focus();
                    } else {
                        jQuery('#commonPurchaseIndentForm #pi_no').val(data.latest_no);
                        jQuery('#commonPurchaseIndentForm #pi_sequence').val(val);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#pi_sequence').removeClass('file-loader');
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
        jQuery('#pi_no').val('');
        jQuery('#pi_sequence').val('');
    }

}