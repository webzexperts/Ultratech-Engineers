// Edit sensitivity row click
jQuery('#dyntable tbody').on('click', '.edit_sensitivity', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillSensitivity(data["id"]);
    }
});

// Function to fetch and fill sensitivity data
function fetchAndFillSensitivity(id) {
    if (!id) return;
    jQuery('#SensitivityModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-sensitivity",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.sensitivity_data != null) {
                jQuery('#SensitivityModal').find('#sensitivity').val(data.sensitivity_data.sensitivity);
                jQuery('#SensitivityModal').find('#id').val(data.sensitivity_data.id);

                const form = document.getElementById("commonSensitivityForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#SensitivityModal').find('#sensitivity').focus();
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

// Reset button click for sensitivity modal
jQuery('#resetbtn').on('click', function () {
    var formId = jQuery('#SensitivityModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonSensitivityForm").reset(); 
        const form = document.getElementById("commonSensitivityForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        jQuery('#sensitivity').focus();
    } else {
        fetchAndFillSensitivity(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_sensitivity', function () {
//     jQuery('#SensitivityModal').modal('show');
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-sensitivity",
//         type: 'GET',
//         data: "id=" + data["id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 if (data.sensitivity_data != null) {
//                     jQuery('#SensitivityModal').find('#sensitivity').val(data.sensitivity_data.sensitivity);
//                     jQuery('#SensitivityModal').find('#id').val(data.sensitivity_data.id);
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

$('#commonSensitivityForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#SensitivityModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#SensitivityModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#SensitivityModal').find('#commonSensitivityForm').find('#id').val();
    var sensitivity = $("#sensitivity").val();
    var formUrl = formId != undefined && formId != "" ? "update-sensitivity" : "store-sensitivity";
    var SensitivityUrl = formId != undefined && formId != "" ? "verify-sensitivity?sensitivity=" + encodeURIComponent(sensitivity) + "&id=" + formId : "verify-sensitivity?sensitivity=" + encodeURIComponent(sensitivity);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (sensitivity != '' && sensitivity != undefined) {
        $.ajax({
            url: SensitivityUrl,
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
                    jQuery('#SensitivityModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#SensitivityModal').find('#submitbtn').prop('disabled', false);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonSensitivityForm").reset();
                                        const form = document.getElementById("commonSensitivityForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#sensitivity').focus();
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#SensitivityModal').find('#submitbtn').prop('disabled', false);
                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#SensitivityModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                 toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#SensitivityModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#SensitivityModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#SensitivityModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#SensitivityModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestSensitivity(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "sensitivity-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#sensitivity").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#sensitivity_list').html(data.sensitivityList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#sensitivity").removeClass('file-loader');
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

jQuery(document).on('click', '#sensitivity_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#sensitivity_suggesion').val(suggest);
    var hidden = jQuery('#sensitivity_suggesion').val();
    var suggestion_list = jQuery('#sensitivity_list').html;
    jQuery('#SensitivityModal').find('#sensitivity').val(hidden)
    var sensitivity = hidden;

    if (suggestion_list != '') {
        checkSensitivityName(sensitivity);
    }
    jQuery('#sensitivity_list').html('');
});

function checkSensitivityName(sensitivity) {
    var formId = jQuery('#SensitivityModal').find('#commonSensitivityForm').find('#id').val();
    var formUrl = formId != undefined && formId != "" ? "verify-sensitivity?sensitivity=" + encodeURIComponent(sensitivity) + "&id=" + formId : "verify-sensitivity?sensitivity=" + encodeURIComponent(sensitivity);
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

function verifySensivity() {
    var SensitivityName = jQuery('#sensitivity').val();
    var suggestion_list = jQuery('#sensitivity_list').html;

    if (suggestion_list != '') {
        checkSensitivityName(SensitivityName);
    }
}

jQuery('#SensitivityModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#SensitivityModal');
    thisForm.find('#id').val('');
    document.getElementById("commonSensitivityForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });
    // window.location.reload();
});

$('#SensitivityModal').on('shown.bs.modal', function () {
    const input = document.getElementById('sensitivity');
    input?.focus();
});