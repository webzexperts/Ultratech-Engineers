// Edit reason row click
jQuery('#dyntable tbody').on('click', '.edit_reason', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#ReasonModal').find('#id').val(data["id"]);
    if (data && data["id"]) {
        fetchAndFillReason(data["id"]);
    }
});

// Function to fetch and fill reason data
function fetchAndFillReason(id) {
    if (!id) return;
    jQuery('#ReasonModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-reason",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.reason_data != null) {
                jQuery('#ReasonModal').find('#reason_type').val(data.reason_data.reason_type).trigger("change.select2");
                jQuery('#ReasonModal').find('#reason_name').val(data.reason_data.reason_name);

                if (data.reason_data.in_use == true) {
                    jQuery('#reason_type').addClass('skip-tab');
                    setSelect2Readonly('#reason_type', true);

                } else {
                    jQuery('#reason_type').removeClass('skip-tab');
                    setSelect2Readonly('#reason_type', false);
                }
                jQuery('#ReasonModal').find('#id').val(data.reason_data.id);
                jQuery('#ReasonModal').find('#add_new').show();
                const form = document.getElementById("commonReasonForm");
                if (form) form.classList.remove('was-validated');
                setTimeout(function () {
                    let sel = jQuery('#reason_type')
                        .next('.select2-container')
                        .find('.select2-selection');
                    sel.attr('tabindex', 0).focus();
                }, 20);
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

jQuery('#ReasonModal').on('click', '#add_new', function () {
    jQuery('#ReasonModal').find('#id').val('');
    document.getElementById("commonReasonForm").reset();
    const form = document.getElementById("commonReasonForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    lastVerifiedReason = '';
    setTimeout(function () {
        let sel = jQuery('#reason_type')
            .next('.select2-container')
            .find('.select2-selection');
        sel.attr('tabindex', 0).focus();
    }, 20);
    jQuery('#ReasonModal').find('#reason_type').val('').trigger('change.select2');
    jQuery('#ReasonModal').find('#reason_name').val('');
    jQuery('#ReasonModal').find('#add_new').hide();
    jQuery('#reason_type').removeClass('skip-tab');
    setSelect2Readonly('#reason_type', false);
});

// Reset button click for reason modal
jQuery('#resetbtn').on('click', function () {
    var formId = jQuery('#ReasonModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonReasonForm").reset(); 
        const form = document.getElementById("commonReasonForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        lastVerifiedReason = '';
        jQuery('#ReasonModal').find('#reason_type').val('').trigger('change.select2');
        setTimeout(function () {
            let sel = jQuery('#reason_type')
                .next('.select2-container')
                .find('.select2-selection');
            sel.attr('tabindex', 0).focus();
        }, 20);

        jQuery('#ReasonModal #reason_type').removeClass('skip-tab');
        setSelect2Readonly('#reason_type', false);
    } else {
        fetchAndFillReason(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_reason', function () {
//     jQuery('#ReasonModal').modal('show');
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-reason",
//         type: 'GET',
//         data: "id=" + data["id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 if (data.reason_data != null) {
//                     jQuery('#ReasonModal').find('#reason_type').val(data.reason_data.reason_type).trigger("change.select2");
//                     jQuery('#ReasonModal').find('#reason_name').val(data.reason_data.reason_name);
//                     jQuery('#ReasonModal').find('#id').val(data.reason_data.id);
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

$('#commonReasonForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#ReasonModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ReasonModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    var formId = jQuery('#ReasonModal').find('#commonReasonForm').find('#id').val();
    var Type = jQuery('#ReasonModal').find("#reason_name").val();
    var reason_type = jQuery('#reason_type option:selected').val();
    var formUrl = formId != undefined && formId != "" ? "update-reason" : "store-reason";
    var ReasonUrl = formId != undefined && formId != "" ? "verify-reason?reason_name=" + encodeURIComponent(Type) + "&reason_type=" + encodeURIComponent(reason_type) + "&id=" + formId : "verify-reason?reason_name=" + encodeURIComponent(Type) + "&reason_type=" + encodeURIComponent(reason_type);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    if (Type != '' && Type != undefined) {
        $.ajax({
            url: ReasonUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#ReasonModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#ReasonModal').find('#submitbtn').prop('disabled', false);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonReasonForm").reset();
                                        const form = document.getElementById("commonReasonForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }
                                        setTimeout(function () {
                                            let sel = jQuery('#reason_type')
                                                .next('.select2-container')
                                                .find('.select2-selection');
                                    
                                            sel.attr('tabindex', 0).focus();
                                        }, 20);
                                        $("#reason_type").val('').trigger('change');
                                    }   
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#ReasonModal').find('#submitbtn').prop('disabled', false);
                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#ReasonModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                 toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#ReasonModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#ReasonModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#ReasonModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#ReasonModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestReason(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "reason-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#reason_name").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#reason_name_list').html(data.reasonList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#reason_name").removeClass('file-loader');
                var errMessage = JSON.parse(jqXHR.responseText);
                if (errMessage.errors) {
                    reason_nameValidator.showErrors(errMessage.errors);
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
let lastVerifiedReason = '';
jQuery(document).on('blur', '#reason_name', function () {
    let reason_type = jQuery(this).val().trim();
    let reason_name = jQuery(this).val().trim();

    if (reason_name === '') return;

    if (reason_name !== lastVerifiedReason && reason_name !== '') {
        lastVerifiedReason = reason_name;
        checkReason(reason_name, reason_type);
    }
});
jQuery(document).on('input', '#reason_name', function () {
    lastVerifiedReason = '';
});
jQuery(document).on('click', '#reason_name_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#reason_name_suggesion').val(suggest);
    var hidden = jQuery('#reason_name_suggesion').val();
    var suggestion_list = jQuery('#reason_name_list').html;
    jQuery('#ReasonModal').find('#reason_name').val(hidden)
    var reason_name = hidden;
    if (suggestion_list != '') {
        checkReason(reason_name);
    }
    jQuery('#reason_name_list').html('');
});

function checkReason(reason_type, reason_type) {
    var id = jQuery('#ReasonModal').find('#commonReasonForm').find('#id').val();
    var reason_type = jQuery('#reason_type option:selected').val();
    var formUrl = id != undefined && id != "" ? "verify-reason?reason_type=" + encodeURIComponent(reason_type) + "&reason_type=" + encodeURIComponent(reason_type) + "&id=" + id : "verify-reason?reason_type=" + encodeURIComponent(reason_type) + "&reason_type=" + encodeURIComponent(reason_type);
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

function verifyReason() {
    var ReasonName = jQuery('#reason_name').val();
    var suggestion_list = jQuery('#reason_name_list').html;
    if (suggestion_list != '') {
        checkReason(ReasonName);
    }
     jQuery('#reason_name_list').html('');
}

jQuery('#ReasonModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#ReasonModal');
    thisForm.find('#id').val('');
    lastVerifiedReason = '';
    document.getElementById("commonReasonForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });

    jQuery('#ReasonModal #reason_type').removeClass('skip-tab');
    setSelect2Readonly('#reason_type', false);

    jQuery('#ReasonModal').find('#add_new').hide();
});

$('#ReasonModal').on('shown.bs.modal', function () {
    setTimeout(function () {
        let sel = jQuery('#reason_type')
            .next('.select2-container')
            .find('.select2-selection');
        sel.attr('tabindex', 0).focus();
    }, 20);


    var formIdblank = jQuery('#commonReasonForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#ReasonModal').find('#add_new').show();
    } else {
        jQuery('#ReasonModal').find('#add_new').hide();
    }
});