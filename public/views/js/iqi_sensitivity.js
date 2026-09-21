var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

// Edit iqi_sensitivity row click
jQuery('#dyntable tbody').on('click', '.edit_iqi_sensitivity', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillIqiSensitivity(data["id"]);
    }
});

// Function to fetch and fill iqi_sensitivity data
function fetchAndFillIqiSensitivity(id) {
    if (!id) return;
    jQuery('#IqiSensitivityModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-iqi_sensitivity",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.iqi_sensitivity_data != null) {
                jQuery('#IqiSensitivityModal').find('#iqi_sensitivity').val(data.iqi_sensitivity_data.iqi_sensitivity);
                jQuery('#IqiSensitivityModal').find('#id').val(data.iqi_sensitivity_data.iqi_sensitivity_id);
                jQuery('#IqiSensitivityModal').find('#add_new').show();

                const form = document.getElementById("commonIqiSensitivityForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#IqiSensitivityModal').find('#iqi_sensitivity').focus();
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

// Reset button click for iqi_sensitivity modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#IqiSensitivityModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonIqiSensitivityForm").reset();
        const form = document.getElementById("commonIqiSensitivityForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        jQuery('#IqiSensitivityModal').find('#iqi_sensitivity').focus();
    } else {
        fetchAndFillIqiSensitivity(formId);
    }
});

$('#commonIqiSensitivityForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#IqiSensitivityModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#IqiSensitivityModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#IqiSensitivityModal').find('#commonIqiSensitivityForm').find('#id').val();
    var iqi_sensitivity = $("#IqiSensitivityModal").find("#iqi_sensitivity").val();
    var formUrl = formId != undefined && formId != "" ? "update-iqi_sensitivity" : "store-iqi_sensitivity";
    var IqiSensitivityUrl = formId != undefined && formId != "" ? "verify-iqi_sensitivity?iqi_sensitivity=" + encodeURIComponent(iqi_sensitivity) + "&id=" + formId : "verify-iqi_sensitivity?iqi_sensitivity=" + encodeURIComponent(iqi_sensitivity);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (iqi_sensitivity != '' && iqi_sensitivity != undefined) {
        $.ajax({
            url: IqiSensitivityUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#IqiSensitivityModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#IqiSensitivityModal').find('#submitbtn').prop('disabled', false);
                                    addedIqiSensitivity(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonIqiSensitivityForm").reset();
                                        const form = document.getElementById("commonIqiSensitivityForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#IqiSensitivityModal').find('#iqi_sensitivity').focus();
                                        if (partAfterManage != 'iqi_sensitivity') {
                                            jQuery('#IqiSensitivityModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#IqiSensitivityModal').find('#submitbtn').prop('disabled', false);
                                    addedIqiSensitivity(true);

                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#IqiSensitivityModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                 toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#IqiSensitivityModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#IqiSensitivityModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#IqiSensitivityModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#IqiSensitivityModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestIqiSensitivity(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "iqi_sensitivity-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#IqiSensitivityModal #iqi_sensitivity").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#iqi_sensitivity_list').html(data.iqiSensitivityList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#IqiSensitivityModal #iqi_sensitivity").removeClass('file-loader');
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

jQuery(document).on('click', '#iqi_sensitivity_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#iqi_sensitivity_suggesion').val(suggest);
    var hidden = jQuery('#iqi_sensitivity_suggesion').val();
    var suggestion_list = jQuery('#iqi_sensitivity_list').html;
    jQuery('#IqiSensitivityModal').find('#iqi_sensitivity').val(hidden)
    var iqi_sensitivity = hidden;

    if (suggestion_list != '') {
        checkIqiSensitivityName(iqi_sensitivity);
    }
    jQuery('#iqi_sensitivity_list').html('');
});


let lastVerifiedIqiSensitivity = '';

jQuery(document).on('blur', '#IqiSensitivityModal #iqi_sensitivity', function () {
    let iqi_sensitivity = jQuery(this).val().trim();

    if (iqi_sensitivity === '') return;

    if (iqi_sensitivity !== lastVerifiedIqiSensitivity) {
        lastVerifiedIqiSensitivity = iqi_sensitivity;
        checkIqiSensitivityName(iqi_sensitivity);
    }
});

jQuery(document).on('input', '#IqiSensitivityModal #iqi_sensitivity', function () {
    lastVerifiedIqiSensitivity = '';
});

function checkIqiSensitivityName(iqi_sensitivity) {
    var id = jQuery('#IqiSensitivityModal').find('#commonIqiSensitivityForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-iqi_sensitivity?iqi_sensitivity=" + encodeURIComponent(iqi_sensitivity) + "&id=" + id : "verify-iqi_sensitivity?iqi_sensitivity=" + encodeURIComponent(iqi_sensitivity);
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

function verifyIqiSensitivity() {
    var IqiSensitivityName = jQuery('#IqiSensitivityModal #iqi_sensitivity').val();
    var suggestion_list = jQuery('#iqi_sensitivity_list').html;

    if (suggestion_list != '') {
        checkIqiSensitivityName(IqiSensitivityName);
    }
}

jQuery('#IqiSensitivityModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#IqiSensitivityModal');
    thisForm.find('#id').val('');
    document.getElementById("commonIqiSensitivityForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });
    jQuery('#IqiSensitivityModal').find('#add_new').hide();
});

$('#IqiSensitivityModal').on('shown.bs.modal', function () {
    const input = document.getElementById('iqi_sensitivity');
    input?.focus();

    var formIdblank = jQuery('#commonIqiSensitivityForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#IqiSensitivityModal').find('#add_new').show();
    } else {
        jQuery('#IqiSensitivityModal').find('#add_new').hide();
    }
});

jQuery('#IqiSensitivityModal').on('click', '#add_new', function () {
    jQuery('#IqiSensitivityModal').find('#id').val('');
    document.getElementById("commonIqiSensitivityForm").reset();
    const form = document.getElementById("commonIqiSensitivityForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#IqiSensitivityModal').find('#iqi_sensitivity').focus();
    jQuery('#IqiSensitivityModal').find('#add_new').hide();
});

function getIqiSensitivity($this = null) {
    var formUrl = "get-iqi_sensitivities";
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
                    var stgDrpHtml = `<option value="">Select IQI Sensitivity</option>`;
                    for (let indx in data.iqi_sensitivities) {
                        stgDrpHtml += `<option value="${data.iqi_sensitivities[indx].iqi_sensitivity_id}">${data.iqi_sensitivities[indx].iqi_sensitivity}</option>`;
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

function addedIqiSensitivity($event) {
    if ($event == true) {
        getIqiSensitivity(".mst_iqi_sensitivity");
    }
}
