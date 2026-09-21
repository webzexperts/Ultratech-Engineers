setSelect2Readonly('#ig_identification_req', true);

var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

var item_group = [];

// Edit item group row click
jQuery('#dyntable tbody').on('click', '.edit_item_group', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillItemGroup(data["id"]);
    }
});

// Function to fetch and fill item group data
function fetchAndFillItemGroup(id) {
    if (!id) return;
    jQuery('#ItemGroupModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-item_group",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.item_group_data != null) {
                jQuery('#ItemGroupModal').find('#item_group').val(data.item_group_data.item_group);
                jQuery('#ItemGroupModal').find('#ig_item_type').val(data.item_group_data.item_type).trigger("change");
                // jQuery('#ItemGroupModal').find('#ig_item_type').val(data.item_group_data.item_type).trigger("change.select2");
                jQuery('#ItemGroupModal').find('#ig_identification_req').val(data.item_group_data.identification_req).trigger("change.select2");
                jQuery('#ItemGroupModal').find('#notify_before').val(data.item_group_data.notify_before ?? '0');
                jQuery('#ItemGroupModal').find('#id').val(data.item_group_data.id);
                jQuery('#ItemGroupModal').find('#add_new').show();

                var in_use = data.item_group_data.in_use == true ? true : false;

                setTimeout(() => {
                    if (in_use == true) {
                        setSelect2Readonly('#commonItemGroupForm #ig_item_type', true);
                    } else {
                        setSelect2Readonly('#commonItemGroupForm #ig_item_type', false);
                    }
                }, 200);

                const form = document.getElementById("commonItemGroupForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#ItemGroupModal').find('#item_group').focus();
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
        },
        complete: function () {
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        }
    });
}

// Reset button click for item group modal
jQuery('#resetbtn').on('click', function () {
    var formId = jQuery('#ItemGroupModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonItemGroupForm").reset();
        const form = document.getElementById("commonItemGroupForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        jQuery('#item_group').focus();
        jQuery('#ItemGroupModal').find('#ig_item_type').val('').trigger('change.select2');
        jQuery('#ItemGroupModal').find('#ig_identification_req').val('').trigger('change.select2');
        // jQuery('#ItemGroupModal').find('#notify_before').prop({ tabindex: 0, readonly: false });
        // jQuery('#ItemGroupModal').find('#notify_before').val('0');
        changeIemType();
    } else {
        fetchAndFillItemGroup(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_item_group', function () {
//     jQuery('#ItemGroupModal').modal('show');
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-item_group",
//         type: 'GET',
//         data: "id=" + data["id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 if (data.item_group_data != null) {
//                     jQuery('#ItemGroupModal').find('#item_group').val(data.item_group_data.item_group);
//                     jQuery('#ItemGroupModal').find('#item_type').val(data.item_group_data.item_type).trigger("change.select2");
//                     jQuery('#ItemGroupModal').find('#ig_identification_req').val(data.item_group_data.identification_req).trigger("change.select2");
//                     jQuery('#ItemGroupModal').find('#id').val(data.item_group_data.id);
//                     jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
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


$('#commonItemGroupForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#ItemGroupModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ItemGroupModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#ItemGroupModal').find('#commonItemGroupForm').find('#id').val();
    var item_group = $("#item_group").val();
    var formUrl = formId != undefined && formId != "" ? "update-item_group" : "store-item_group";
    var ItemGroupUrl = formId != undefined && formId != "" ? "verify-item_group?item_group=" + encodeURIComponent(item_group) + "&id=" + formId : "verify-item_group?item_group=" + encodeURIComponent(item_group);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (item_group != '' && item_group != undefined) {
        $.ajax({
            url: ItemGroupUrl,
            type: 'GET',
            dataType: 'json',
            // processData: false,
            // headers: {
            //     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            // },
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#ItemGroupModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#ItemGroupModal').find('#submitbtn').prop('disabled', false);
                                    addedIg(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonItemGroupForm").reset();
                                        const form = document.getElementById("commonItemGroupForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#item_group').focus();
                                        jQuery('#ig_item_type').val('').trigger('change');
                                        if (partAfterManage != 'unit') {
                                            jQuery('#ItemGroupModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#ItemGroupModal').find('#submitbtn').prop('disabled', false);
                                    addedIg(true);
                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#ItemGroupModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#ItemGroupModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#ItemGroupModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#ItemGroupModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#ItemGroupModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});


function changeIemType() {
    var item_type = jQuery('#ig_item_type').val();

    if (item_type != '') {
        if (item_type == 'film' || item_type == 'general') {
            jQuery('#commonItemGroupForm').find('#ig_identification_req').val('no').trigger("change.select2");
        } else {
            jQuery('#commonItemGroupForm').find('#ig_identification_req').val('yes').trigger("change.select2");
        }
        if (item_type == 'film' || item_type == 'general' || item_type == 'mpt_material' || item_type == 'dpt_chemical' || item_type == 'probe_ut') {
            jQuery('#commonItemGroupForm').find('#notify_before').val('0');
            jQuery('#commonItemGroupForm #notify_before').prop({ tabindex: -1, readonly: true }).addClass('skip-tab');
        } else {
            jQuery('#commonItemGroupForm #notify_before').prop({ tabindex: 0, readonly: false }).removeClass('skip-tab');
        }
    } else {
        jQuery('#commonItemGroupForm').find('#notify_before').val('0');
        jQuery('#commonItemGroupForm #notify_before').prop({ tabindex: -1, readonly: true }).addClass('skip-tab');
        jQuery('#commonItemGroupForm').find('#ig_identification_req').val('').trigger("change.select2");
    }

    setSelect2Readonly('#ig_identification_req', true);
}

jQuery('#ig_item_type').on('change', function () {
    changeIemType();
});

function suggestItemGroup(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "item_group-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#item_group").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#item_group_list').html(data.ItemGroupList);
                } else {
                    toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#item_group").removeClass('file-loader');
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

jQuery(document).on('click', '#item_group_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#item_group_suggesion').val(suggest);
    var hidden = jQuery('#item_group_suggesion').val();
    var suggestion_list = jQuery('#item_group_list').html;
    jQuery('#ItemGroupModal').find('#item_group').val(hidden)
    var item_group = hidden;

    if (suggestion_list != '') {
        checkItemGroup(item_group);
    }
    jQuery('#item_group_list').html('');
});

function verifyItemGroup() {
    var item_group = jQuery('#item_group').val();
    var suggestion_list = jQuery('#item_group_list').html;
    if (suggestion_list != '') {
        checkItemGroup(item_group);
    }
}


function checkItemGroup(item_group) {
    var id = jQuery('#ItemGroupModal').find('#commonItemGroupForm').find('#id').val();
    var formUrl = "verify-item_group?item_group=" + encodeURIComponent(item_group);
    if (id != undefined && id != "") {
        formUrl += "&id=" + id;
    }
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

// function checkItemGroup() {
//     var operator = jQuery('#operator').val();
//     var category = jQuery('#category option:selected').val();
//     var id = jQuery('#OperatorModal').find('#commonOperatorForm').find('#id').val();
//     var formUrl = "verify-operator?operator=" + encodeURIComponent(operator) + "&category=" + encodeURIComponent(category);
//     if (id != undefined && id != "") {
//         formUrl += "&id=" + id;
//     }
//     jQuery.ajax({
//         url: formUrl,
//         type: 'GET',
//         dataType: 'json',
//         processData: false,
//         headers: headerOpt,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 toastr.error(data.response_message);
//             }
//         }
//     });
// }


$('#ItemGroupModal').on('shown.bs.modal', function () {
    const input = document.getElementById('item_group');
    input?.focus();

    var formIdblank = jQuery('#commonItemGroupForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#ItemGroupModal').find('#add_new').show();
    } else {
        jQuery('#ItemGroupModal').find('#add_new').hide();
    }
    changeIemType();
});

jQuery('#ItemGroupModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#ItemGroupModal');
    thisForm.find('#id').val('');
    document.getElementById("commonItemGroupForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).prop('checked', false);
    });
    jQuery('#ItemGroupModal').find('#add_new').hide();
});

jQuery('#ItemGroupModal').on('click', '#add_new', function () {
    jQuery('#ItemGroupModal').find('#id').val('');
    document.getElementById("commonItemGroupForm").reset();
    const form = document.getElementById("commonItemGroupForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#item_group').focus();
    setSelect2Readonly('#commonItemGroupForm #ig_item_type', false);
    jQuery('#ItemGroupModal').find('#ig_item_type').val('').trigger('change.select2');
    jQuery('#ItemGroupModal').find('#ig_identification_req').val('').trigger('change.select2');
    // jQuery('#ItemGroupModal').find('#notify_before').prop({ tabindex: 0, readonly: false });
    changeIemType();
    jQuery('#ItemGroupModal').find('#add_new').hide();
});

function getIg($this = null) {
    var formUrl = "get-item_groups";
    jQuery.ajax({
        url: formUrl,
        type: 'GET',
        dataType: 'json',
        processData: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (data) {
            if (data.response_code == 1) {
                if ($this != null) {
                    var stgDrpHtml = `<option value="">Select Item Group</option>`;
                    for (let indx in data.item_group) {
                        stgDrpHtml += `<option value="${data.item_group[indx].id}" data-item_type="${data.item_group[indx].item_type}" data-identification_req="${data.item_group[indx].identification_req}">${data.item_group[indx].item_group}</option>`;
                    }

                    jQuery($this).each(function (e) {
                        let Id = jQuery(this).attr('id');
                        let Selected = jQuery(this).find("option:selected").val();
                        jQuery(this).empty().append(stgDrpHtml);
                        jQuery(this).val(Selected).trigger("change");
                    });
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
        }
    });
}

function addedIg($event) {
    if ($event == true) {
        getIg(".mst_ig");
    }
}