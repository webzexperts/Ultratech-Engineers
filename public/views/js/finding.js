var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

// Edit finding row click
jQuery('#dyntable tbody').on('click', '.edit_finding', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillFinding(data["id"]);
    }
});

// Function to fetch and fill finding data
function fetchAndFillFinding(id) {
    if (!id) return;
    jQuery('#FindingModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-finding",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.finding_data != null) {
                var d = data.finding_data;
                jQuery('#FindingModal').find('#finding_name').val(d.finding_name);
                // jQuery('#FindingModal').find('#abbreviation').val(d.abbreviation);
                // jQuery('#FindingModal').find('#required_finding_level').prop('checked', d.required_finding_level == 'Yes');
                jQuery('#FindingModal').find('#id').val(d.finding_id);
                jQuery('#FindingModal').find('#add_new').show();

                const form = document.getElementById("commonFindingForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#FindingModal').find('#finding_name').focus();
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

// Reset button click for finding modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#FindingModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonFindingForm").reset();
        const form = document.getElementById("commonFindingForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        // jQuery('#FindingModal').find('#required_finding_level').prop('checked', true);
        jQuery('#FindingModal').find('#finding_name').focus();
    } else {
        fetchAndFillFinding(formId);
    }
});

$('#commonFindingForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#FindingModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#FindingModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#FindingModal').find('#commonFindingForm').find('#id').val();
    var finding_name = $("#finding_name").val();
    var formUrl = formId != undefined && formId != "" ? "update-finding" : "store-finding";
    var FindingUrl = formId != undefined && formId != "" ? "verify-finding?finding_name=" + encodeURIComponent(finding_name) + "&id=" + formId : "verify-finding?finding_name=" + encodeURIComponent(finding_name);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (finding_name != '' && finding_name != undefined) {
        $.ajax({
            url: FindingUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#FindingModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#FindingModal').find('#submitbtn').prop('disabled', false);
                                    addedFinding(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonFindingForm").reset();
                                        const form = document.getElementById("commonFindingForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#required_finding_level').prop('checked', true);
                                        jQuery('#finding_name').focus();
                                        if (partAfterManage != 'finding') {
                                            jQuery('#FindingModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#FindingModal').find('#submitbtn').prop('disabled', false);
                                    addedFinding(true);

                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#FindingModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                 toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#FindingModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#FindingModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#FindingModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#FindingModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestFinding(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "finding-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#finding_name").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#finding_list').html(data.findingList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#finding_name").removeClass('file-loader');
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

jQuery(document).on('click', '#finding_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#finding_suggesion').val(suggest);
    var hidden = jQuery('#finding_suggesion').val();
    var suggestion_list = jQuery('#finding_list').html;
    jQuery('#FindingModal').find('#finding_name').val(hidden)
    var finding_name = hidden;

    if (suggestion_list != '') {
        checkFindingName(finding_name);
    }
    jQuery('#finding_list').html('');
});


let lastVerifiedFinding = '';

jQuery(document).on('blur', '#finding_name', function () {
    let finding_name = jQuery(this).val().trim();

    if (finding_name === '') return;

    if (finding_name !== lastVerifiedFinding) {
        lastVerifiedFinding = finding_name;
        checkFindingName(finding_name);
    }
});

jQuery(document).on('input', '#finding_name', function () {
    lastVerifiedFinding = '';
});

function checkFindingName(finding_name) {
    var id = jQuery('#FindingModal').find('#commonFindingForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-finding?finding_name=" + encodeURIComponent(finding_name) + "&id=" + id : "verify-finding?finding_name=" + encodeURIComponent(finding_name);
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

function verifyFinding() {
    var FindingName = jQuery('#finding_name').val();
    var suggestion_list = jQuery('#finding_list').html;

    if (suggestion_list != '') {
        checkFindingName(FindingName);
    }
}

jQuery('#FindingModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#FindingModal');
    thisForm.find('#id').val('');
    document.getElementById("commonFindingForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        if (jQuery(this).attr('type') !== 'checkbox') {
            jQuery(this).val('');
        }
        jQuery(this).removeAttr('readonly');
    });
    thisForm.find('#required_finding_level').prop('checked', true);
    jQuery('#FindingModal').find('#add_new').hide();
});

$('#FindingModal').on('shown.bs.modal', function () {
    const input = document.getElementById('finding_name');
    input?.focus();

    var formIdblank = jQuery('#commonFindingForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#FindingModal').find('#add_new').show();
    } else {
        jQuery('#FindingModal').find('#add_new').hide();
        jQuery('#FindingModal').find('#required_finding_level').prop('checked', true);
    }
});

jQuery('#FindingModal').on('click', '#add_new', function () {
    jQuery('#FindingModal').find('#id').val('');
    document.getElementById("commonFindingForm").reset();
    const form = document.getElementById("commonFindingForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#FindingModal').find('#required_finding_level').prop('checked', true);
    jQuery('#finding_name').focus();
    jQuery('#FindingModal').find('#add_new').hide();
});

function getFinding($this = null) {
    var formUrl = "get-findings";
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
                    var stgDrpHtml = `<option value="">Select Finding</option>`;
                    for (let indx in data.findings) {
                        stgDrpHtml += `<option value="${data.findings[indx].finding_id}" data-abbreviation="${data.findings[indx].abbreviation || ''}" data-required_finding_level="${data.findings[indx].required_finding_level || ''}">${data.findings[indx].finding_name}</option>`;
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

function addedFinding($event) {
    if ($event == true) {
        getFinding(".mst_finding");
    }
}
