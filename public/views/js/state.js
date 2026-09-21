var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

// Edit state row click
jQuery('#dyntable tbody').on('click', '.edit_state', function () {
    jQuery('#StateModal').modal('show');
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#StateModal').find('#id').val(data["id"]);
    fetchAndFillState(data["id"]);
});

// Function to fetch and fill state data
function fetchAndFillState(id) {
    if (!id) return;
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-state",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.state_data != null) {
                jQuery('#StateModal').find('#state').val(data.state_data.state);
                jQuery('#StateModal').find('#country_id').val(data.state_data.country_id).trigger("change.select2");

                if (data.state_data.country_id == 1) {
                    jQuery('#StateModal').find('#state_code').prop("readonly", false);
                } else {
                    jQuery('#StateModal').find('#state_code').prop("readonly", true);
                }

                jQuery('#StateModal').find('#state_code').val(data.state_data.state_code);
                jQuery('#StateModal').find('#id').val(data.state_data.id);
                jQuery('#StateModal').find('#add_new').show();

                const form = document.getElementById("commonStateForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#StateModal').find('#state').focus();
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

// Reset button click for state modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#StateModal').find('#id').val();
    if (!formId) {
        const form = document.getElementById("commonStateForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
        jQuery('#StateModal').find('#state').focus();
        jQuery('#StateModal').find('#country_id').val('').trigger('change.select2');
        // jQuery('#StateModal').find('#state_code').prop("disabled", true);
        jQuery('#StateModal').find('#state_code').val('');
        jQuery('#StateModal').find('#state_code').removeClass('is-invalid');
    } else {
        fetchAndFillState(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_state', function () {
//     jQuery('#StateModal').modal('show');
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-state",
//         type: 'GET',
//         data: "id=" + data["id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 if (data.state_data != null) {
//                     jQuery('#StateModal').find('#state').val(data.state_data.state);
//                     jQuery('#StateModal').find('#country_id').val(data.state_data.country_id).trigger("change.select2");

//                     if (data.state_data.country_id == 1) {
//                         jQuery('#StateModal').find('#state_code').prop("disabled", false);
//                     } else {
//                         jQuery('#StateModal').find('#state_code').prop("disabled", true);
//                     }

//                     jQuery('#StateModal').find('#state_code').val(data.state_data.state_code);
//                     jQuery('#StateModal').find('#id').val(data.state_data.id);
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

$('#commonStateForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#StateModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    let countryId = jQuery('#country_id option:selected').val();
    let stateCodeField = jQuery("#StateModal").find("#state_code");
    if (countryId == "1" || countryId == 1) {
        stateCodeField.prop('required', true);
    } else {
        stateCodeField.prop('required', false);
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#StateModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#StateModal').find('#commonStateForm').find('#id').val();
    var stateName = jQuery("#StateModal").find("#state").val();
    var countryName = jQuery('#country_id option:selected').val();
    var state_code = jQuery("#StateModal").find("#state_code").val();

    var StateUrl;
    var StateCodeUrl;
    if (stateName != "") {
        // if (countryName != '' && stateName != "") {
        // StateUrl = formId != undefined && formId != "" ? "verify-state-data?state=" + encodeURIComponent(stateName) + "&country=" + encodeURIComponent(countryName) + "&state_code=" + encodeURIComponent(state_code) + "&id=" + formId : "verify-state-data?state=" + encodeURIComponent(stateName) + "&country=" + encodeURIComponent(countryName) + "&state_code=" + encodeURIComponent(state_code);
        StateUrl = formId != undefined && formId != "" ? "verify-state-data?state=" + encodeURIComponent(stateName) + "&id=" + formId : "verify-state-data?state=" + encodeURIComponent(stateName);
    }
    if (state_code != "" && state_code != undefined) {
        StateCodeUrl = formId != undefined && formId != "" ? "verify-state_code?state_code=" + encodeURIComponent(state_code) + "&countryId=" + encodeURIComponent(countryName) + "&id=" + formId : "verify-state_code?state_code=" + encodeURIComponent(state_code) + "&countryId=" + encodeURIComponent(countryName);
        // StateUrl = formId != undefined && formId != "" ? "verify-state_code?state_code=" + encodeURIComponent(state_code) + "&countryId=" + encodeURIComponent(countryName) + "&id=" + formId : "verify-state_code?state_code=" + encodeURIComponent(state_code) + "&countryId=" + encodeURIComponent(countryName);
    }

    var formUrl = formId != undefined && formId != "" ? "update-state" : "store-state";
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    // if ((stateName != '' && stateName != undefined) && (countryName != "" && countryName != undefined)) {
    if ((stateName != '' && stateName != undefined)) {
        $.ajax({
            url: StateUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#StateModal').find('#submitbtn').prop('disabled', false);
                } else {
                    if (state_code != "" && state_code != undefined) {
                        $.ajax({
                            url: StateCodeUrl,
                            type: 'GET',
                            dataType: 'json',
                            success: function (data) {
                                if (data.response_code == 1) {
                                    toastr.error(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#StateModal').find('#submitbtn').prop('disabled', false);
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
                                                    jQuery('#StateModal').find('#submitbtn').prop('disabled', false);
                                                    addedState(true);
                                                }
                                                else if (formId == undefined || formId == "") {
                                                    function nextFn() {
                                                        document.getElementById("commonStateForm").reset();
                                                        const form = document.getElementById("commonStateForm");
                                                        if (form) {
                                                            form.classList.remove('was-validated');
                                                        }

                                                        jQuery('#state').focus();
                                                        $("#country_id").val('').trigger('change');
                                                        if (partAfterManage != 'state') {
                                                            jQuery('#StateModal').modal('hide');
                                                        }
                                                    }
                                                    toastSuccess(data.response_message, nextFn);
                                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                                    jQuery('#StateModal').find('#submitbtn').prop('disabled', false);
                                                    addedState(true);
                                                }
                                                else {
                                                    toastError(data.response_message);
                                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                                    jQuery('#StateModal').find('#submitbtn').prop('disabled', false);
                                                }
                                            } else {
                                                toastr.error(data.response_message);
                                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                                jQuery('#StateModal').find('#submitbtn').prop('disabled', false);
                                            }
                                        },
                                        error: function (xhr) {
                                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                            jQuery('#StateModal').find('#submitbtn').prop('disabled', false);
                                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                                let errors = xhr.responseJSON.errors;
                                                let errorMsg = '';
                                                $.each(errors, function (key, value) {
                                                    errorMsg += value + '\n';
                                                });
                                                toastr.error(errorMsg);
                                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                                jQuery('#StateModal').find('#submitbtn').prop('disabled', false);
                                            } else {
                                                toastr.error('Something went wrong. Please try again.');
                                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                                jQuery('#StateModal').find('#submitbtn').prop('disabled', false);
                                            }
                                        }
                                    });
                                }
                            }
                        });
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
                                        jQuery('#StateModal').find('#submitbtn').prop('disabled', false);
                                        addedState(true);
                                    }
                                    else if (formId == undefined || formId == "") {
                                        function nextFn() {
                                            document.getElementById("commonStateForm").reset();
                                            const form = document.getElementById("commonStateForm");
                                            if (form) {
                                                form.classList.remove('was-validated');
                                            }

                                            jQuery('#state').focus();
                                            $("#country_id").val('').trigger('change');
                                            if (partAfterManage != 'state') {
                                                jQuery('#StateModal').modal('hide');
                                            }
                                        }
                                        toastSuccess(data.response_message, nextFn);
                                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                        jQuery('#StateModal').find('#submitbtn').prop('disabled', false);
                                        addedState(true);
                                    }
                                    else {
                                        toastError(data.response_message);
                                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                        jQuery('#StateModal').find('#submitbtn').prop('disabled', false);
                                    }
                                } else {
                                    toastr.error(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#StateModal').find('#submitbtn').prop('disabled', false);
                                }
                            },
                            error: function (xhr) {
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#StateModal').find('#submitbtn').prop('disabled', false);
                                if (xhr.responseJSON && xhr.responseJSON.errors) {
                                    let errors = xhr.responseJSON.errors;
                                    let errorMsg = '';
                                    $.each(errors, function (key, value) {
                                        errorMsg += value + '\n';
                                    });
                                    toastr.error(errorMsg);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#StateModal').find('#submitbtn').prop('disabled', false);
                                } else {
                                    toastr.error('Something went wrong. Please try again.');
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#StateModal').find('#submitbtn').prop('disabled', false);
                                }
                            }
                        });
                    }

                }

            }
        });
    }
});

function suggestState(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "state-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#state").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#state_name_list').html(data.stateList);
                } else {
                    toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#state").removeClass('file-loader');
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

jQuery(document).on('click', '#state_name_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#state_suggesion').val(suggest);
    var hidden = jQuery('#state_suggesion').val();
    var suggestion_list = jQuery('#state_name_list').html;
    jQuery('#StateModal').find('#state').val(hidden)
    var state = hidden;

    if (suggestion_list != '') {
        CheckState(state);
    }
    jQuery('#state_name_list').html('');
});
let lastVerifiedState = '';
let lastVerifiedCountry = '';

function verifyStateCountry() {
    let state = jQuery("#state").val().trim();
    // let country = jQuery("#country_id option:selected").val().trim();

    // if (state === '' || country === '') return;

    // if (state !== lastVerifiedState || country !== lastVerifiedCountry) {
    //     lastVerifiedState = state;
    //     lastVerifiedCountry = country;
    // CheckState(state, country);
    // }

    if (state === '') return;

    if (state !== lastVerifiedState) {
        lastVerifiedState = state;
        CheckState(state);
    }
}


jQuery(document).on('blur', '#state', function () {

    var state_code = jQuery('#StateModal').find('#commonStateForm').find('#state_code').val();

    if (state_code != '' && state_code != undefined) {
        CheckStateCode(state_code);
    } else {
        verifyStateCountry();
    }
});

// jQuery(document).on('input', '#state', function () {
//     lastVerifiedState = '';
// });

// jQuery(document).on('change', '#country_id', function () {

//     verifyStateCountry();
// });

function CheckState(state) {
    // function CheckState(state, country, state_code) {
    var state = jQuery('#state').val();
    // var country = jQuery('#country_id option:selected').val();
    // var state_code = jQuery('#state_code').val();
    var formId = jQuery('#StateModal').find('#commonStateForm').find('#id').val();
    // var formUrl = formId != undefined && formId != "" ? "verify-state-data?state=" + encodeURIComponent(state) + "&country=" + encodeURIComponent(country) + "&id=" + formId : "verify-state-data?state=" + encodeURIComponent(state) + "&country=" + encodeURIComponent(country);
    var formUrl = formId != undefined && formId != "" ? "verify-state-data?state=" + encodeURIComponent(state) + "&id=" + formId : "verify-state-data?state=" + encodeURIComponent(state);
    // if (country != '' && state != "") {
    if (state != "") {
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
}

function verifyCountry() {
    var StateName = jQuery('#state').val();
    var suggestion_list = jQuery('#state_name_list').html;

    if (suggestion_list != '') {
        CheckState(StateName);
    }
}

jQuery('#StateModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#StateModal');
    thisForm.find('#id').val('');
    thisForm.find('#country_id').val('').trigger('change');
    document.getElementById("commonStateForm").reset();
    lastVerifiedState = '';
    lastVerifiedCountry = '';
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });

    if (partAfterManage == 'state') {
        // window.location.reload();
    }

    jQuery('#StateModal').find('#add_new').hide();
});

$('#StateModal').on('shown.bs.modal', function () {
    const input = document.getElementById('state');
    input?.focus();

    var formIdblank = jQuery('#commonStateForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#StateModal').find('#add_new').show();
    } else {
        jQuery('#StateModal').find('#add_new').hide();
    }
});

jQuery('#StateModal').on('click', '#add_new', function () {
    jQuery('#StateModal').find('#id').val('');
    document.getElementById("commonStateForm").reset();
    lastVerifiedState = '';
    lastVerifiedCountry = '';
    const form = document.getElementById("commonStateForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#state').focus();
    $("#country_id").val('').trigger('change.select2');
    jQuery('#commonStateForm').find('#state_code').prop({ tabindex: -1, readonly: true });
    jQuery('#StateModal').find('#add_new').hide();
});

// jQuery('#country_id').on('change', function () {
//     CheckState();
// });

jQuery(document).on('change', '#country_id', function () {
    let countryId = jQuery(this).val();
    let stateField = jQuery('#StateModal').find('#state_code');
    if (countryId == "1") {
        stateField.prop('readonly', false);
        stateField.attr('tabindex', 0);
    } else {
        stateField.prop('readonly', true);
        stateField.attr('tabindex', -1);
        stateField.val('');
        stateField.removeClass('is-invalid');
    }
});

// jQuery('#country_id').change(function () {
//     let countryId = jQuery('#country_id option:selected').val();
//     if (countryId == "1" || countryId == 1) {
//         jQuery('#StateModal').find('#state_code').prop({ tabindex: 0, readonly: false });
//         // jQuery('#StateModal').find('#state_code').prop("disabled", false);
//         // jQuery('#state_code').prop("disabled", false);        
//     } else {
//         jQuery('#StateModal').find('#state_code').prop("disabled", true);
//         jQuery('#StateModal').find('#state_code').val('');
//         jQuery('#StateModal').find('#state_code').removeClass('is-invalid');

//         // jQuery('#state_code').prop("disabled", true);
//         // jQuery('#state_code').val('');
//         // jQuery('#state_code').removeClass('is-invalid');
//     }
// });

function CheckStateCode(state_code) {
    var state_code = jQuery('#state_code').val();
    var countryId = jQuery('#country_id option:selected').val();
    var formId = jQuery('#StateModal').find('#commonStateForm').find('#id').val();
    var formUrl = formId != undefined && formId != "" ? "verify-state_code?state_code=" + encodeURIComponent(state_code) + "&countryId=" + encodeURIComponent(countryId) + "&id=" + formId : "verify-state_code?state_code=" + encodeURIComponent(state_code) + "&countryId=" + encodeURIComponent(countryId);
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

jQuery('#state_code').on('change', function () {
    CheckStateCode();
});

function getState($this = null) {
    var formUrl = "get-states";
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
                    var stgDrpHtml = `<option value="">Select State</option>`;
                    for (let indx in data.states) {
                        stgDrpHtml += `<option value="${data.states[indx].id}">${data.states[indx].state}</option>`;
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

function addedState($event) {
    if ($event == true) {
        getState(".suggest_state_name");
    }
}

jQuery('#CountryModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('country_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});