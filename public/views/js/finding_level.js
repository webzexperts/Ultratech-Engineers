var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

// Edit finding_level row click
jQuery('#dyntable tbody').on('click', '.edit_finding_level', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillFindingLevel(data["id"]);
    }
});

// Function to fetch and fill finding_level data
function fetchAndFillFindingLevel(id) {
    if (!id) return;
    jQuery('#FindingLevelModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-finding_level",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.finding_level_data != null) {
                jQuery('#FindingLevelModal').find('#finding_level_name').val(data.finding_level_data.finding_level_name);
                jQuery('#FindingLevelModal').find('#id').val(data.finding_level_data.finding_level_id);
                jQuery('#FindingLevelModal').find('#add_new').show();

                const form = document.getElementById("commonFindingLevelForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#FindingLevelModal').find('#finding_level_name').focus();
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

// Reset button click for finding_level modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#FindingLevelModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonFindingLevelForm").reset();
        const form = document.getElementById("commonFindingLevelForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        jQuery('#FindingLevelModal').find('#finding_level_name').focus();
    } else {
        fetchAndFillFindingLevel(formId);
    }
});

$('#commonFindingLevelForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#FindingLevelModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#FindingLevelModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#FindingLevelModal').find('#commonFindingLevelForm').find('#id').val();
    var finding_level_name = $("#finding_level_name").val();
    var formUrl = formId != undefined && formId != "" ? "update-finding_level" : "store-finding_level";
    var FindingLevelUrl = formId != undefined && formId != "" ? "verify-finding_level?finding_level_name=" + encodeURIComponent(finding_level_name) + "&id=" + formId : "verify-finding_level?finding_level_name=" + encodeURIComponent(finding_level_name);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (finding_level_name != '' && finding_level_name != undefined) {
        $.ajax({
            url: FindingLevelUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#FindingLevelModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#FindingLevelModal').find('#submitbtn').prop('disabled', false);
                                    addedFindingLevel(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonFindingLevelForm").reset();
                                        const form = document.getElementById("commonFindingLevelForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#finding_level_name').focus();
                                        if (partAfterManage != 'finding_level') {
                                            jQuery('#FindingLevelModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#FindingLevelModal').find('#submitbtn').prop('disabled', false);
                                    addedFindingLevel(true);

                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#FindingLevelModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                 toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#FindingLevelModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#FindingLevelModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#FindingLevelModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#FindingLevelModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestFindingLevel(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "finding_level-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#finding_level_name").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#finding_level_list').html(data.findingLevelList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#finding_level_name").removeClass('file-loader');
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

jQuery(document).on('click', '#finding_level_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#finding_level_suggesion').val(suggest);
    var hidden = jQuery('#finding_level_suggesion').val();
    var suggestion_list = jQuery('#finding_level_list').html;
    jQuery('#FindingLevelModal').find('#finding_level_name').val(hidden)
    var finding_level_name = hidden;

    if (suggestion_list != '') {
        checkFindingLevelName(finding_level_name);
    }
    jQuery('#finding_level_list').html('');
});


let lastVerifiedFindingLevel = '';

jQuery(document).on('blur', '#finding_level_name', function () {
    let finding_level_name = jQuery(this).val().trim();

    if (finding_level_name === '') return;

    if (finding_level_name !== lastVerifiedFindingLevel) {
        lastVerifiedFindingLevel = finding_level_name;
        checkFindingLevelName(finding_level_name);
    }
});

jQuery(document).on('input', '#finding_level_name', function () {
    lastVerifiedFindingLevel = '';
});

function checkFindingLevelName(finding_level_name) {
    var id = jQuery('#FindingLevelModal').find('#commonFindingLevelForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-finding_level?finding_level_name=" + encodeURIComponent(finding_level_name) + "&id=" + id : "verify-finding_level?finding_level_name=" + encodeURIComponent(finding_level_name);
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

function verifyFindingLevel() {
    var FindingLevelName = jQuery('#finding_level_name').val();
    var suggestion_list = jQuery('#finding_level_list').html;

    if (suggestion_list != '') {
        checkFindingLevelName(FindingLevelName);
    }
}

jQuery('#FindingLevelModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#FindingLevelModal');
    thisForm.find('#id').val('');
    document.getElementById("commonFindingLevelForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });
    jQuery('#FindingLevelModal').find('#add_new').hide();
});

$('#FindingLevelModal').on('shown.bs.modal', function () {
    const input = document.getElementById('finding_level_name');
    input?.focus();

    var formIdblank = jQuery('#commonFindingLevelForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#FindingLevelModal').find('#add_new').show();
    } else {
        jQuery('#FindingLevelModal').find('#add_new').hide();
    }
});

jQuery('#FindingLevelModal').on('click', '#add_new', function () {
    jQuery('#FindingLevelModal').find('#id').val('');
    document.getElementById("commonFindingLevelForm").reset();
    const form = document.getElementById("commonFindingLevelForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#finding_level_name').focus();
    jQuery('#FindingLevelModal').find('#add_new').hide();
});

function getFindingLevel($this = null) {
    var formUrl = "get-finding_levels";
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
                    var stgDrpHtml = `<option value="">Select Finding Level</option>`;
                    for (let indx in data.finding_levels) {
                        stgDrpHtml += `<option value="${data.finding_levels[indx].finding_level_id}">${data.finding_levels[indx].finding_level_name}</option>`;
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

function addedFindingLevel($event) {
    if ($event == true) {
        getFindingLevel(".mst_finding_level");
    }
}
