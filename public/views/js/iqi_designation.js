var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

// Edit iqi_designation row click
jQuery('#dyntable tbody').on('click', '.edit_iqi_designation', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillIqiDesignation(data["id"]);
    }
});

// Function to fetch and fill iqi_designation data
function fetchAndFillIqiDesignation(id) {
    if (!id) return;
    jQuery('#IqiDesignationModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-iqi_designation",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.iqi_designation_data != null) {
                jQuery('#IqiDesignationModal').find('#iqi_designation').val(data.iqi_designation_data.iqi_designation);
                jQuery('#IqiDesignationModal').find('#id').val(data.iqi_designation_data.iqi_designation_id);
                jQuery('#IqiDesignationModal').find('#add_new').show();

                const form = document.getElementById("commonIqiDesignationForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#IqiDesignationModal').find('#iqi_designation').focus();
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

// Reset button click for iqi_designation modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#IqiDesignationModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonIqiDesignationForm").reset();
        const form = document.getElementById("commonIqiDesignationForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        jQuery('#IqiDesignationModal').find('#iqi_designation').focus();
    } else {
        fetchAndFillIqiDesignation(formId);
    }
});

$('#commonIqiDesignationForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#IqiDesignationModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#IqiDesignationModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#IqiDesignationModal').find('#commonIqiDesignationForm').find('#id').val();
    var iqi_designation = $("#IqiDesignationModal").find("#iqi_designation").val();
    var formUrl = formId != undefined && formId != "" ? "update-iqi_designation" : "store-iqi_designation";
    var IqiDesignationUrl = formId != undefined && formId != "" ? "verify-iqi_designation?iqi_designation=" + encodeURIComponent(iqi_designation) + "&id=" + formId : "verify-iqi_designation?iqi_designation=" + encodeURIComponent(iqi_designation);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (iqi_designation != '' && iqi_designation != undefined) {
        $.ajax({
            url: IqiDesignationUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#IqiDesignationModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#IqiDesignationModal').find('#submitbtn').prop('disabled', false);
                                    addedIqiDesignation(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonIqiDesignationForm").reset();
                                        const form = document.getElementById("commonIqiDesignationForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#IqiDesignationModal').find('#iqi_designation').focus();
                                        if (partAfterManage != 'iqi_designation') {
                                            jQuery('#IqiDesignationModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#IqiDesignationModal').find('#submitbtn').prop('disabled', false);
                                    addedIqiDesignation(true);

                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#IqiDesignationModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                 toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#IqiDesignationModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#IqiDesignationModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#IqiDesignationModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#IqiDesignationModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestIqiDesignation(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "iqi_designation-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#IqiDesignationModal #iqi_designation").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#iqi_designation_list').html(data.iqiDesignationList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#IqiDesignationModal #iqi_designation").removeClass('file-loader');
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

jQuery(document).on('click', '#iqi_designation_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#iqi_designation_suggesion').val(suggest);
    var hidden = jQuery('#iqi_designation_suggesion').val();
    var suggestion_list = jQuery('#iqi_designation_list').html;
    jQuery('#IqiDesignationModal').find('#iqi_designation').val(hidden)
    var iqi_designation = hidden;

    if (suggestion_list != '') {
        checkIqiDesignationName(iqi_designation);
    }
    jQuery('#iqi_designation_list').html('');
});


let lastVerifiedIqiDesignation = '';

jQuery(document).on('blur', '#IqiDesignationModal #iqi_designation', function () {
    let iqi_designation = jQuery(this).val().trim();

    if (iqi_designation === '') return;

    if (iqi_designation !== lastVerifiedIqiDesignation) {
        lastVerifiedIqiDesignation = iqi_designation;
        checkIqiDesignationName(iqi_designation);
    }
});

jQuery(document).on('input', '#IqiDesignationModal #iqi_designation', function () {
    lastVerifiedIqiDesignation = '';
});

function checkIqiDesignationName(iqi_designation) {
    var id = jQuery('#IqiDesignationModal').find('#commonIqiDesignationForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-iqi_designation?iqi_designation=" + encodeURIComponent(iqi_designation) + "&id=" + id : "verify-iqi_designation?iqi_designation=" + encodeURIComponent(iqi_designation);
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

function verifyIqiDesignation() {
    var IqiDesignationName = jQuery('#IqiDesignationModal #iqi_designation').val();
    var suggestion_list = jQuery('#iqi_designation_list').html;

    if (suggestion_list != '') {
        checkIqiDesignationName(IqiDesignationName);
    }
}

jQuery('#IqiDesignationModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#IqiDesignationModal');
    thisForm.find('#id').val('');
    document.getElementById("commonIqiDesignationForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });
    jQuery('#IqiDesignationModal').find('#add_new').hide();
});

$('#IqiDesignationModal').on('shown.bs.modal', function () {
    const input = document.getElementById('iqi_designation');
    input?.focus();

    var formIdblank = jQuery('#commonIqiDesignationForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#IqiDesignationModal').find('#add_new').show();
    } else {
        jQuery('#IqiDesignationModal').find('#add_new').hide();
    }
});

jQuery('#IqiDesignationModal').on('click', '#add_new', function () {
    jQuery('#IqiDesignationModal').find('#id').val('');
    document.getElementById("commonIqiDesignationForm").reset();
    const form = document.getElementById("commonIqiDesignationForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#IqiDesignationModal').find('#iqi_designation').focus();
    jQuery('#IqiDesignationModal').find('#add_new').hide();
});

function getIqiDesignation($this = null) {
    var formUrl = "get-iqi_designations";
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
                    var stgDrpHtml = `<option value="">Select IQI Designation</option>`;
                    for (let indx in data.iqi_designations) {
                        stgDrpHtml += `<option value="${data.iqi_designations[indx].iqi_designation_id}">${data.iqi_designations[indx].iqi_designation}</option>`;
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

function addedIqiDesignation($event) {
    if ($event == true) {
        getIqiDesignation(".mst_iqi_designation");
    }
}
