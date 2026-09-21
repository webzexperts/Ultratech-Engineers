setSelect2Readonly('#identification_req', true);
setSelect2Readonly('#item_type', true);
// setSelect2Readonly('#unit_id', true);

// Edit item row click
jQuery('#dyntable tbody').on('click', '.edit_item', function () {
    jQuery('#ItemModal').modal('show');
    var data = table.row(jQuery(this).parents('tr')).data();
    fetchAndFillItem(data["id"]);
});

// Function to fetch and fill item data
function fetchAndFillItem(id) {
    if (!id) return;
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-item",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.item_data != null) {
                jQuery('#ItemModal').find('#item_code').val(data.item_data.item_code);
                jQuery('#ItemModal').find('#item_name').val(data.item_data.item_name);
                jQuery('#ItemModal').find('#item_group_id').val(data.item_data.item_group_id).trigger("change");
                jQuery('#ItemModal').find('#item_type').val(data.item_data.item_type).trigger("change");
                jQuery('#ItemModal').find('#identification_req').val(data.item_data.identification_req).trigger("change.select2");
                jQuery('#ItemModal').find('#inter_location_transfer').val(data.item_data.inter_location_transfer).trigger("change.select2");
                if (data.item_data.used_inter_location_transfer_item == true) {
                    jQuery('#inter_location_transfer').addClass('skip-tab');
                    setSelect2Readonly('#commonItemForm #inter_location_transfer', true);
                }
                if (data.item_data.used_in_any == true) {
                    jQuery('#item_group_id').addClass('skip-tab');
                    setSelect2Readonly('#commonItemForm #item_group_id', true);
                } else {
                    jQuery('#item_group_id').removeClass('skip-tab');
                    setSelect2Readonly('#commonItemForm #item_group_id', false);
                }
                if (data.item_data.used_item_issue == true) {
                    jQuery('#ItemModal').data('used_item_issue', true);
                    jQuery('#ItemModal #conv_factor').prop('readonly', true).prop('required', true).attr('tabindex', '-1').addClass('skip-tab');
                } else {
                    jQuery('#ItemModal').data('used_item_issue', false);
                }
                setTimeout(() => {
                    jQuery('#ItemModal').find('#unit_id').val(data.item_data.unit_id).trigger("change.select2");
                }, 100);
                jQuery('#ItemModal').find('#min_stock_level').val(data.item_data.min_stock_level);
                jQuery('#ItemModal').find('#conv_factor').val(data.item_data.conv_factor);
                jQuery('#ItemModal').find('#document_ref_no').val(data.item_data.document_ref_no);
                jQuery('#ItemModal').find('#validity_date').val(data.item_data.validity_date);
                jQuery('#ItemModal').find('#status').val(data.item_data.status).trigger("change.select2");
                jQuery('#ItemModal').find('#id').val(data.item_data.id);
                jQuery('#ItemModal').find('#add_new').show();

                const form = document.getElementById("commonItemForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#ItemModal').find('#item_name').focus();
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

// Reset button click for item modal
jQuery('#resetbtn').on('click', function () {
    var formId = jQuery('#ItemModal').find('#id').val();
    if (!formId) {
        const form = document.getElementById("commonItemForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
        // getItemCode();
        lastVerifiedItem = '';
        jQuery('#ItemModal').find('#item_name').focus();
        jQuery('#ItemModal').find('#item_group_id').val('').trigger('change.select2');
        jQuery('#item_group_id').removeClass('skip-tab');
        setSelect2Readonly('#commonItemForm #item_group_id', false);
        jQuery('#ItemModal').find('#item_type').val('').trigger('change.select2');
        jQuery('#ItemModal').find('#identification_req').val('').trigger('change.select2');
        jQuery('#ItemModal').find('#inter_location_transfer').val('Allowed').trigger('change.select2');
        jQuery('#inter_location_transfer').removeClass('skip-tab');
        setSelect2Readonly('#commonItemForm #inter_location_transfer', false);
        jQuery('#ItemModal').find('#unit_id').val('').trigger('change.select2');
        jQuery('#ItemModal').find('#status').val('Active').trigger('change.select2');
        jQuery('#ItemModal').find('#document_ref_no').prop({ tabindex: 0, readonly: false });
        jQuery('#ItemModal').find('#validity_date').prop({ tabindex: 0, readonly: false });
        jQuery('#ItemModal').find('#conv_factor').val('');
        jQuery('#ItemModal').data('used_item_issue', false);
    } else {
        fetchAndFillItem(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_item', function () {
//     jQuery('#ItemModal').modal('show');
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#ItemModal').find('#id').val(data["id"]);
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-item",
//         type: 'GET',
//         data: "id=" + data["id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 if (data.item_data != null) {
//                     jQuery('#ItemModal').find('#item_code').val(data.item_data.item_code);
//                     jQuery('#ItemModal').find('#item_name').val(data.item_data.item_name);
//                     jQuery('#ItemModal').find('#item_group_id').val(data.item_data.item_group_id).trigger("change");
//                     jQuery('#ItemModal').find('#item_type').val(data.item_data.item_type).trigger("change");
//                     jQuery('#ItemModal').find('#identification_req').val(data.item_data.identification_req).trigger("change.select2");
//                     setTimeout(() => {

//                         jQuery('#ItemModal').find('#unit_id').val(data.item_data.unit_id).trigger("change.select2");
//                     }, 100);
//                     jQuery('#ItemModal').find('#min_stock_level').val(data.item_data.min_stock_level);
//                     jQuery('#ItemModal').find('#document_ref_no').val(data.item_data.document_ref_no);
//                     jQuery('#ItemModal').find('#validity_date').val(data.item_data.validity_date);
//                     jQuery('#ItemModal').find('#status').val(data.item_data.status).trigger("change.select2");
//                     jQuery('#ItemModal').find('#id').val(data.item_data.id);
//                     jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');

//                     // ChangeItemType();
//                 }
//             } else {
//                 console.log(data.response_message);
//             }
//         },
//         error: function (jqXHR) {
//             if (jqXHR.status == 401) {
//                 console.log(jqXHR.statusText);
//             } else {
//                 console.log('Something went wrong!');
//             }
//             console.log(JSON.parse(jqXHR.responseText));
//         }
//     });
// });

$('#commonItemForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#ItemModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    var dateValue = document.getElementById("validity_date").value.trim();
    if (!isValidDate(dateValue) && dateValue != '') {
        toastr.error("Enter A Valid Date.");
        // toastr.error("Please Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ItemModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var item_type = jQuery('#item_type option:selected').val();
    if (item_type == 'film' || item_type == 'Industrial X-Ray Films') {
        var conv_factor_val = jQuery('#conv_factor').val().trim();
        if (conv_factor_val === '') {
            // toastr.error("Enter Conv. Factor.");
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#ItemModal').find('#submitbtn').prop('disabled', false);
            jQuery('#conv_factor').focus();
            return;
        }
        if (parseInt(conv_factor_val) <= 0 || isNaN(parseInt(conv_factor_val))) {
            toastr.error("Enter Conv. Factor Greater Than 0.");
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#ItemModal').find('#submitbtn').prop('disabled', false);
            jQuery('#conv_factor').focus();
            return;
        }
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ItemModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#ItemModal').find('#commonItemForm').find('#id').val();
    var item_name = $("#item_name").val();
    var formUrl = formId != undefined && formId != "" ? "update-item" : "store-item";
    var ItemUrl = formId != undefined && formId != "" ? "verify-item?item_name=" + encodeURIComponent(item_name) + "&id=" + formId : "verify-item?item_name=" + encodeURIComponent(item_name);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (item_name != '' && item_name != undefined) {
        $.ajax({
            url: ItemUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#ItemModal').find('#submitbtn').prop('disabled', false);
                } else {
                    $.ajax({
                        type: 'POST',
                        url: formUrl,
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function (data) {
                            if (data.response_code == 1) {
                                if (formId != undefined && formId != "") {
                                    function redirectFn() {
                                        window.location.reload();
                                    }
                                    toastSuccess(data.response_message, redirectFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#ItemModal').find('#submitbtn').prop('disabled', false);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonItemForm").reset();
                                        const form = document.getElementById("commonItemForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }
                                        jQuery('#ItemModal').find('#inter_location_transfer').val('Allowed').trigger('change.select2');
                                        // getItemCode();
                                        setSelect2Readonly('#identification_req', true);
                                        setSelect2Readonly('#item_type', true);
                                        // setSelect2Readonly('#unit_id', true);
                                        jQuery('#item_name').focus();
                                        $("#item_group_id").val('').trigger('change');
                                        // $("#item_type").val('').trigger('change');
                                        $("#identification_req").val('').trigger('change');
                                        $("#unit_id").val('').trigger('change');
                                        $("#status").val('Active').trigger('change');
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#ItemModal').find('#submitbtn').prop('disabled', false);
                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#ItemModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#ItemModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#ItemModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#ItemModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#ItemModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestItemName(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "item-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#item_name").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#item_name_list').html(data.itemList);
                } else {
                    toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#item_name").removeClass('file-loader');
                var errMessage = JSON.parse(jqXHR.responseText);
                if (errMessage.errors) {
                    countryValidator.showErrors(errMessage.errors);
                } else if (jqXHR.status == 401) {
                    toastr.error(jqXHR.statusText);
                } else {
                    toastr.error('Something went wrong!');
                    console.log(JSON.parse(jqXHR.responseText));
                }
            }
        });
    }
}

jQuery(document).on('click', '#item_name_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#item_name_suggesion').val(suggest);
    var hidden = jQuery('#item_name_suggesion').val();
    var suggestion_list = jQuery('#item_name_list').html;
    jQuery('#ItemModal').find('#item_name').val(hidden)
    var item_name = hidden;

    if (suggestion_list != '') {
        checkItemName(item_name);
    }
    jQuery('#item_name_list').html('');
});

let lastVerifiedItem = '';

jQuery(document).on('blur', '#item_name', function () {
    let item_name = jQuery(this).val().trim();

    if (item_name === '') return;

    if (item_name !== lastVerifiedItem) {
        lastVerifiedItem = item_name;
        checkItemName(item_name);
    }
});

jQuery(document).on('input', '#item_name', function () {
    lastVerifiedItem = '';
});
function checkItemName(item_name) {
    var id = jQuery('#ItemModal').find('#commonItemForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-item?item_name=" + encodeURIComponent(item_name) + "&id=" + id : "verify-item?item_name=" + encodeURIComponent(item_name);
    jQuery.ajax({
        url: formUrl,
        type: 'GET',
        dataType: 'json',
        processData: false,
        headers: headerOpt,
        success: function (data) {
            if (data.response_code == 1) {
                toastr.error(data.response_message);
            }
        }
    });
}

function verifyItemName() {
    var ItemName = jQuery('#item_name').val();
    var suggestion_list = jQuery('#item_name_list').html;

    if (suggestion_list != '') {
        checkItemName(ItemName);
    }
}

// function checkItemCode(item_code) {
//     var id = jQuery('#ItemModal').find('#commonItemForm').find('#id').val();
//     var formUrl = id != undefined && id != "" ? "verify-item_code?item_code=" + encodeURIComponent(item_code) + "&id=" + id : "verify-item_code?item_code=" + encodeURIComponent(item_code);
//     jQuery.ajax({
//         url: formUrl,
//         type: 'GET',
//         dataType: 'json',
//         processData: false,
//         headers: headerOpt,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 toastr.error(data.response_message, "#item_code");
//             }
//         }
//     });
// }

// function getItemCode() {
//     var formUrl = "get-item_code";
//     jQuery.ajax({
//         url: formUrl,
//         type: 'GET',
//         dataType: 'json',
//         processData: false,
//         headers: headerOpt,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 jQuery("#item_code").val(data.item_code);
//             }
//         }
//     });
// }

jQuery('#commonItemForm #item_group_id').on('change', function () {
    ChangeItemGroup();
    ChangeItemType();
});

function ChangeItemGroup() {
    let selectedOption = jQuery('#commonItemForm #item_group_id').find('option:selected');


    let item_type = selectedOption.data('item_type');
    let identification_req = selectedOption.data('identification_req');

    let $modal = jQuery('#ItemModal');

    if (item_type !== undefined && identification_req !== undefined) {
        $modal.find('#identification_req').val(identification_req).trigger("change.select2");
        $modal.find('#item_type').val(item_type).trigger("change");
    } else {
        $modal.find('#identification_req').val('').trigger("change.select2");
        $modal.find('#item_type').val('').trigger("change");
    }

}


// jQuery('#item_type').on('change',function () {
//     ChangeItemType();
// });

function ChangeItemType() {
    var item_type = jQuery('#item_type option:selected').val();
    jQuery('#ItemModal #document_ref_no').prop('readonly', false).removeClass('skip-tab');
    jQuery('#ItemModal #validity_date').prop('readonly', false).removeClass('skip-tab');
    if (item_type == 'film' || item_type == 'Industrial X-Ray Films') {
         jQuery('#identification_req').val('No').trigger("change.select2");
         setSelect2Readonly('#identification_req', true);
         setSelect2Readonly('#item_type', true);
         let isUsedInIssue = jQuery('#ItemModal').data('used_item_issue');
         if (isUsedInIssue) {
             jQuery('#ItemModal #conv_factor').prop('readonly', true).prop('required', true).attr('tabindex', '-1').addClass('skip-tab');
         } else {
             jQuery('#ItemModal #conv_factor').prop('readonly', false).prop('required', true).attr('tabindex', '0').removeClass('skip-tab');
         }
         jQuery('#ItemModal #conv_factor_astric').show();
 
         jQuery('#ItemModal #document_ref_no').prop({ tabindex: 0, readonly: false });
        jQuery('#ItemModal #validity_date').prop({ tabindex: 0, readonly: false });
        jQuery('#ItemModal #validity_date').datepicker('enable');
    } else if (item_type == 'general') {
        jQuery('#identification_req').val('No').trigger("change.select2");
        setSelect2Readonly('#identification_req', true);
        setSelect2Readonly('#item_type', true);
        jQuery('#ItemModal #conv_factor').val('').prop('readonly', true).prop('required', false).attr('tabindex', '-1');
        jQuery('#ItemModal #conv_factor_astric').hide();

        jQuery('#ItemModal #document_ref_no').prop({ tabindex: 0, readonly: false });
        jQuery('#ItemModal #validity_date').prop({ tabindex: 0, readonly: false });
        jQuery('#ItemModal #validity_date').datepicker('enable');
    } else if (item_type == 'rt_camera' || item_type == 'mpt_equipment' || item_type == 'mpt_material' || item_type == 'dpt_chemical' || item_type == 'ut_equipment' || item_type == 'instrument' || item_type == 'probe_ut') {
        jQuery('#identification_req').val('Yes').trigger("change.select2");
        setSelect2Readonly('#identification_req', true);
        setSelect2Readonly('#item_type', true);
        jQuery('#ItemModal #conv_factor').val('').prop('readonly', true).prop('required', false).attr('tabindex', '-1');
        jQuery('#ItemModal #conv_factor_astric').hide();

        jQuery('#ItemModal #document_ref_no').val('');
        jQuery('#ItemModal #validity_date').val('');

        jQuery('#ItemModal #document_ref_no').prop({ tabindex: -1, readonly: true });
        jQuery('#ItemModal #validity_date').prop({ tabindex: -1, readonly: true });
        jQuery('#ItemModal #validity_date').datepicker('disable');
    } else {
        jQuery('#ItemModal').find('#identification_req').val('').trigger("change.select2");
        jQuery('#ItemModal').find('#item_type').val('').trigger("change.select2");
        setSelect2Readonly('#identification_req', true);
        setSelect2Readonly('#item_type', true);
        jQuery('#ItemModal #conv_factor').val('').prop('readonly', true).prop('required', false).attr('tabindex', '-1');
        jQuery('#ItemModal #conv_factor_astric').hide();
        jQuery('#ItemModal #document_ref_no').prop({ tabindex: 0, readonly: false });
        jQuery('#ItemModal #validity_date').prop({ tabindex: 0, readonly: false });
        jQuery('#ItemModal #validity_date').datepicker('enable');
    }
}

jQuery('#ItemModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#ItemModal');
    thisForm.find('#id').val('');
    lastVerifiedItem = '';
    thisForm.find('#item_type').val('').trigger('change');
    thisForm.find('#identification_req').val('').trigger('change.select2');
    thisForm.find('#unit_id').val('').trigger('change.select2');
    setTimeout(() => {
        thisForm.find('#status').val('Active').trigger('change.select2');
    }, 500);
    document.getElementById("commonItemForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).prop('checked', false);
    });
    jQuery('#ItemModal').find('#add_new').hide();
    jQuery('#ItemModal').find('#conv_factor').val('').prop('readonly', true).prop('required', false).attr('tabindex', '-1');
    jQuery('#ItemModal').find('#conv_factor_astric').hide();
    jQuery('#ItemModal').data('used_item_issue', false);
    jQuery('#inter_location_transfer').removeClass('skip-tab');
    setSelect2Readonly('#commonItemForm #inter_location_transfer', false);
    jQuery('#item_group_id').removeClass('skip-tab');
    setSelect2Readonly('#commonItemForm #item_group_id', false);
});

$('#ItemModal').on('shown.bs.modal', function () {
    const input = document.getElementById('item_name');
    input?.focus();

    var formId = jQuery('#ItemModal').find('#commonItemForm').find('#id').val();

    if (formId == '' || formId == undefined) {
        // setTimeout(function () {
        // getItemCode();
        // }, 300);
    }
    jQuery('#ItemModal').find('#inter_location_transfer').val('Allowed').trigger('change.select2');
    setSelect2Readonly('#identification_req', true);
    setSelect2Readonly('#item_type', true);
    // setSelect2Readonly('#unit_id', true);

    var formIdblank = jQuery('#commonItemForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#ItemModal').find('#add_new').show();
    } else {
        jQuery('#ItemModal').find('#add_new').hide();
    }
});

jQuery('#ItemModal').on('click', '#add_new', function () {
    jQuery('#ItemModal').find('#id').val('');
    document.getElementById("commonItemForm").reset();
    const form = document.getElementById("commonItemForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    lastVerifiedItem = '';
    jQuery('#ItemModal').find('#item_name').focus();
    jQuery('#ItemModal').find('#item_group_id').val('').trigger('change.select2');
    jQuery('#item_group_id').removeClass('skip-tab');
    setSelect2Readonly('#commonItemForm #item_group_id', false);
    jQuery('#ItemModal').find('#item_type').val('').trigger('change.select2');
    jQuery('#ItemModal').find('#identification_req').val('').trigger('change.select2');
    jQuery('#ItemModal').find('#inter_location_transfer').val('Allowed').trigger('change.select2');
    jQuery('#ItemModal').find('#unit_id').val('').trigger('change.select2');
    jQuery('#ItemModal').find('#status').val('Active').trigger('change.select2');
    jQuery('#ItemModal').find('#document_ref_no').prop({ tabindex: 0, readonly: false });
    jQuery('#ItemModal').find('#validity_date').prop({ tabindex: 0, readonly: false });
    jQuery('#ItemModal').find('#conv_factor').val('').prop('readonly', true).prop('required', false).attr('tabindex', '-1');
    jQuery('#ItemModal').data('used_item_issue', false);
    jQuery('#ItemModal').find('#conv_factor_astric').hide();
    jQuery('#ItemModal').find('#add_new').hide();
    jQuery('#inter_location_transfer').removeClass('skip-tab');
    setSelect2Readonly('#commonItemForm #inter_location_transfer', false);
});

jQuery('#ItemGroupModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('item_group_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});
jQuery('#UnitModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('unit_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});