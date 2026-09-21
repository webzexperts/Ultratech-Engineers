var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];
// Edit acceptance standard row click
jQuery('#dyntable tbody').on('click', '.edit_acceptance_standard', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillAcceptanceStandard(data["id"]);
    }
});

// Function to fetch and fill acceptance standard data
function fetchAndFillAcceptanceStandard(id) {
    if (!id) return;
    jQuery('#AcceptanceStandardModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-acceptance_standard",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.acceptance_standard_data != null) {
                jQuery('#AcceptanceStandardModal').find('#acceptance_standard').val(data.acceptance_standard_data.acceptance_standard);
                jQuery('#AcceptanceStandardModal').find('#status').val(data.acceptance_standard_data.status).trigger("change");
                jQuery('#AcceptanceStandardModal').find('#id').val(data.acceptance_standard_data.id);
                jQuery('#AcceptanceStandardModal').find('#add_new').show();

                const form = document.getElementById("commonAcceptanceStandardForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#AcceptanceStandardModal').find('#acceptance_standard').focus();
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

// Reset button click for acceptance standard modal
jQuery('#resetbtn').on('click', function () {
    var formId = jQuery('#AcceptanceStandardModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonAcceptanceStandardForm").reset();
        const form = document.getElementById("commonAcceptanceStandardForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        jQuery('#acceptance_standard').focus();
        jQuery('#status').val('Active').trigger('change');
    } else {
        fetchAndFillAcceptanceStandard(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_acceptance_standard', function () {
//     jQuery('#AcceptanceStandardModal').modal('show');
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-acceptance_standard",
//         type: 'GET',
//         data: "id=" + data["id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 if (data.acceptance_standard_data != null) {
//                     jQuery('#AcceptanceStandardModal').find('#acceptance_standard').val(data.acceptance_standard_data.acceptance_standard);
//                     jQuery('#AcceptanceStandardModal').find('#status').val(data.acceptance_standard_data.status).trigger("change");
//                     jQuery('#AcceptanceStandardModal').find('#id').val(data.acceptance_standard_data.id);
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

$('#commonAcceptanceStandardForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#AcceptanceStandardModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#AcceptanceStandardModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#AcceptanceStandardModal').find('#commonAcceptanceStandardForm').find('#id').val();
    var AcceptanceStandard = $("#acceptance_standard").val();
    var formUrl = formId != undefined && formId != "" ? "update-acceptance_standard" : "store-acceptance_standard";
    var AcceptanceStandardUrl = formId != undefined && formId != "" ? "verify-acceptance_standard?acceptance_standard=" + encodeURIComponent(AcceptanceStandard) + "&id=" + formId : "verify-acceptance_standard?acceptance_standard=" + encodeURIComponent(AcceptanceStandard);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    if (AcceptanceStandard != '' && AcceptanceStandard != undefined) {
        $.ajax({
            url: AcceptanceStandardUrl,
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
                    jQuery('#AcceptanceStandardModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#AcceptanceStandardModal').find('#submitbtn').prop('disabled', false);
                                }
                                 else if (formId == undefined || formId == "") {
                                     function nextFn() {
                                         document.getElementById("commonAcceptanceStandardForm").reset();
                                         const form = document.getElementById("commonAcceptanceStandardForm");
                                         if (form) {
                                             form.classList.remove('was-validated');
                                         }
                                         jQuery('#acceptance_standard').focus();
                                         jQuery('#status').val('Active').trigger('change');
                                         if (partAfterManage != 'acceptance_standard') {
                                             jQuery('#AcceptanceStandardModal').modal('hide');
                                         }
                                     }
                                     toastSuccess(data.response_message, nextFn);
                                     jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                     jQuery('#AcceptanceStandardModal').find('#submitbtn').prop('disabled', false);
                                     addedAcceptanceStandard(true);
                                 }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#AcceptanceStandardModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                 toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#AcceptanceStandardModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#AcceptanceStandardModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#AcceptanceStandardModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#AcceptanceStandardModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestAcceptanceStandard(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "acceptance_standard-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#acceptance_standard").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#acceptance_standard_list').html(data.acceptance_standardList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#acceptance_standard").removeClass('file-loader');
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

jQuery(document).on('click', '#acceptance_standard_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#acceptance_standard_suggesion').val(suggest);
    var hidden = jQuery('#acceptance_standard_suggesion').val();
    var suggestion_list = jQuery('#acceptance_standard_list').html;
    jQuery('#AcceptanceStandardModal').find('#acceptance_standard').val(hidden)
    var acceptance_standard = hidden;
    if (suggestion_list != '') {
        checkAcceptanceStandardName(acceptance_standard);
    }
    jQuery('#acceptance_standard_list').html('');
});

function checkAcceptanceStandardName(acceptance_standard) {
    var formId = jQuery('#AcceptanceStandardModal').find('#commonAcceptanceStandardForm').find('#id').val();
    var formUrl = formId != undefined && formId != "" ? "verify-acceptance_standard?acceptance_standard=" + encodeURIComponent(acceptance_standard) + "&id=" + formId : "verify-acceptance_standard?acceptance_standard=" + encodeURIComponent(acceptance_standard);
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

function verifyAcceptanceStandard() {
    var AcceptanceStandard = jQuery('#acceptance_standard').val();
    var suggestion_list = jQuery('#acceptance_standard_list').html;
    if (suggestion_list != '') {
        checkAcceptanceStandardName(AcceptanceStandard);
    }
}

jQuery('#AcceptanceStandardModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#AcceptanceStandardModal');
    thisForm.find('#id').val('');
    document.getElementById("commonAcceptanceStandardForm").reset();
    // window.location.reload();
    jQuery('#status').val('Active').trigger('change');
    thisForm.find('#add_new').hide();
});

$('#AcceptanceStandardModal').on('shown.bs.modal', function () {
    var formIdblank = jQuery('#AcceptanceStandardModal').find('#commonAcceptanceStandardForm').find('#id').val();
    const input = document.getElementById('acceptance_standard');
    input?.focus();
    if (formIdblank == "" || formIdblank == undefined) {
        jQuery('#status').val('Active').trigger('change');
        jQuery('#AcceptanceStandardModal').find('#add_new').hide();
    } else {
        jQuery('#AcceptanceStandardModal').find('#add_new').show();
    }
});

jQuery('#AcceptanceStandardModal').on('click', '#add_new', function () {
    jQuery('#AcceptanceStandardModal').find('#id').val('');
    document.getElementById("commonAcceptanceStandardForm").reset();
    const form = document.getElementById("commonAcceptanceStandardForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    jQuery('#status').val('Active').trigger('change');

    jQuery('#acceptance_standard').focus();
    jQuery('#AcceptanceStandardModal').find('#add_new').hide();
});

function addedAcceptanceStandard($event) {
    if ($event == true) {
        getAcceptanceStandard(".suggest_acceptance_standard");
    }
}

function getAcceptanceStandard($this = null) {
    var formUrl = "get-acceptance_standards";
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
                    var stgDrpHtml = `<option value="">Select Acceptance Std.</option>`;
                    for (let indx in data.acceptance_standards) {
                        stgDrpHtml += `<option value="${data.acceptance_standards[indx].id}">${data.acceptance_standards[indx].acceptance_standard}</option>`;
                    }

                    jQuery($this).each(function (e) {
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
        }
    });
}