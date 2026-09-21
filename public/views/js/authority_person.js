var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];


jQuery(document).ready(function () {
    toggleAuthorityFields();
});

jQuery("#authority_person_type_value_fix").on("change", function () {
    toggleAuthorityFields()
});

function toggleAuthorityFields() {

    var type = jQuery("#authority_person_type_value_fix").val();

    // Hide all fields
    jQuery("#location_row").hide();
    jQuery("#validity_row").hide();
    jQuery("#pms_row").hide();
    jQuery("#certificate_row").hide();

    if (type == "RSO") {
        jQuery("#location_row").show();
        jQuery("#validity_row").show();
        jQuery("#pms_row").show();
        jQuery("#certificate_row").show();
        jQuery("#current_location_id").prop("required", true);
        jQuery("#validity").prop("required", true);
        jQuery("#pms_no").prop("required", true);
        jQuery("#certificate").prop("required", true);

    }
    else if (type == "Radiographer") {
        jQuery("#location_row").show();
        jQuery("#pms_row").show();
        jQuery("#certificate_row").show();
        jQuery("#validity").prop("required", false).val('');
        jQuery("#current_location_id").prop("required", true);
        jQuery("#pms_no").prop("required", true);
        jQuery("#certificate").prop("required", true);
    }else{
        jQuery("#validity").prop("required", false).val('');
        jQuery("#current_location_id").prop("required", false).val('').trigger('change.select2');
        jQuery("#pms_no").prop("required", false).val('');
        jQuery("#certificate").prop("required", false).val('');
        jQuery("#authority_person_type_value_fix").val('').trigger('change.select2');
        clearCertificate();
    }
}

// Build the comma-separated operator_type from checked boxes (live, mirrors server logic)
function buildOperatorType() {
    var labels = [];
    if (jQuery('#tested_by').is(':checked'))     labels.push('Tested By');
    if (jQuery('#reviewed_by').is(':checked'))   labels.push('Reviewed By');
    if (jQuery('#authorized_by').is(':checked')) labels.push('Authorized By');
    if (jQuery('#checked_by').is(':checked'))    labels.push('Checked By');
    if (jQuery('#approved_by').is(':checked'))   labels.push('Approved By');
    jQuery('#operator_type').val(labels.join(', '));
}

jQuery(document).on('change', '.operator_type_chk', function () {
    buildOperatorType();
});

// Edit authority_person row click
jQuery('#dyntable tbody').on('click', '.edit_authority_person', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillAuthorityPerson(data["id"]);
    }
});

// Function to fetch and fill authority_person data
function fetchAndFillAuthorityPerson(id) {
    if (!id) return;
    jQuery('#AuthorityPersonModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-authority_person",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.authority_person_data != null) {
                var d = data.authority_person_data;
                jQuery('#AuthorityPersonModal').find('#operator').val(d.operator);
                jQuery('#AuthorityPersonModal').find('#designation').val(d.designation);
                jQuery('#AuthorityPersonModal').find('#status').val(d.status).trigger('change');

                jQuery('#AuthorityPersonModal').find('#tested_by').prop('checked', d.tested_by == 'Yes');
                jQuery('#AuthorityPersonModal').find('#reviewed_by').prop('checked', d.reviewed_by == 'Yes');
                jQuery('#AuthorityPersonModal').find('#authorized_by').prop('checked', d.authorized_by == 'Yes');
                jQuery('#AuthorityPersonModal').find('#checked_by').prop('checked', d.checked_by == 'Yes');
                jQuery('#AuthorityPersonModal').find('#approved_by').prop('checked', d.approved_by == 'Yes');
                buildOperatorType();

                // Reset file fields first
                jQuery('#AuthorityPersonModal').find('#signature_doc').val('');
                jQuery('#AuthorityPersonModal').find('#signature_prev').attr('href', '#').addClass('hide');
                jQuery('#AuthorityPersonModal').find('#signature_remove').addClass('hide').removeClass('i-block');
                jQuery('#AuthorityPersonModal').find('#signature').val('').prop('required', false).removeAttr('required');

                if (d.signature) {
                    let fullPath = d.signature;
                    let fileName = fullPath.split('/').pop();
                    jQuery('#AuthorityPersonModal').find('#signature_doc').val(fullPath);
                    jQuery('#AuthorityPersonModal').find('#signature_prev').attr('href', uploadURL + fullPath).removeClass('hide');
                    jQuery('#AuthorityPersonModal').find('#signature_remove').addClass('i-block').removeClass('hide');

                    let fileInput = jQuery('#AuthorityPersonModal').find('#signature');
                    let newFile = new DataTransfer();
                    newFile.items.add(new File([""], fileName));
                    fileInput[0].files = newFile.files;
                }

                if(d.authority_person_type_value_fix != ''){

                    jQuery('#AuthorityPersonModal').find('#authority_person_type_value_fix').val(d.authority_person_type_value_fix).trigger('change');
                    jQuery('#AuthorityPersonModal').find('#current_location_id').val(d.current_location_id).trigger('change.select2');
                    jQuery('#AuthorityPersonModal').find('#validity').val(d.validity);
                    jQuery('#AuthorityPersonModal').find('#pms_no').val(d.pms_no);

                    if (d.certificate) {
                        let fullPath1 = d.certificate;
                        let fileName1 = fullPath1.split('/').pop();
                        jQuery('#AuthorityPersonModal').find('#certificate_doc').val(fullPath1);
                        jQuery('#AuthorityPersonModal').find('#certificate_prev').attr('href', uploadURL + fullPath1).removeClass('hide');
                        jQuery('#AuthorityPersonModal').find('#certificate_remove').addClass('i-block').removeClass('hide');

                        let fileInput = jQuery('#AuthorityPersonModal').find('#certificate');
                        let newFile = new DataTransfer();
                        newFile.items.add(new File([""], fileName1));
                        fileInput[0].files = newFile.files;
                    }
                }

                

                jQuery('#AuthorityPersonModal').find('#id').val(d.authority_person_id);
                jQuery('#AuthorityPersonModal').find('#add_new').show();

                const form = document.getElementById("commonAuthorityPersonForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#AuthorityPersonModal').find('#operator').focus();
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

// Restore the form to a clean "add" state
function resetAuthorityPersonForm() {
    document.getElementById("commonAuthorityPersonForm").reset();
    const form = document.getElementById("commonAuthorityPersonForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    jQuery('#AuthorityPersonModal').find('#id').val('');
    jQuery('#AuthorityPersonModal').find('#operator_type').val('');
    jQuery('#AuthorityPersonModal').find('#signature_doc').val('');
    jQuery('#AuthorityPersonModal').find('#signature_prev').attr('href', '#').addClass('hide');
    jQuery('#AuthorityPersonModal').find('#signature_remove').addClass('hide').removeClass('i-block');
    jQuery('#AuthorityPersonModal').find('#signature').val('').prop('required', false);
    jQuery('#AuthorityPersonModal').find('#status').val('Active').trigger('change');
    jQuery('#AuthorityPersonModal').find('.operator_type_chk').prop('checked', false);

    jQuery('#AuthorityPersonModal').find('#authority_person_type_value_fix').val('').trigger('change');
    jQuery('#AuthorityPersonModal').find('#current_location_id').val('').trigger('change.select2');
    jQuery('#AuthorityPersonModal').find('#pms_no').val('');

}

// Reset button click for authority_person modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#AuthorityPersonModal').find('#id').val();
    if (!formId) {
        resetAuthorityPersonForm();
        clearCertificate();
        toggleAuthorityFields();
        jQuery('#AuthorityPersonModal').find('#operator').focus();
    } else {
        fetchAndFillAuthorityPerson(formId);
    }
});

$('#commonAuthorityPersonForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#AuthorityPersonModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#AuthorityPersonModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    buildOperatorType();

    let checkedCheckboxes = jQuery('#AuthorityPersonModal').find('.operator_type_chk:checked').length;
    if (checkedCheckboxes === 0) {
        toastr.error("Please Select At Least One Operator Type.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#AuthorityPersonModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#AuthorityPersonModal').find('#commonAuthorityPersonForm').find('#id').val();
    var operator = $("#operator").val();
    var formUrl = formId != undefined && formId != "" ? "update-authority_person" : "store-authority_person";
    var AuthorityPersonUrl = formId != undefined && formId != "" ? "verify-authority_person?operator=" + encodeURIComponent(operator) + "&id=" + formId : "verify-authority_person?operator=" + encodeURIComponent(operator);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (operator != '' && operator != undefined) {
        $.ajax({
            url: AuthorityPersonUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#AuthorityPersonModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#AuthorityPersonModal').find('#submitbtn').prop('disabled', false);
                                    addedAuthorityPerson(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        resetAuthorityPersonForm();
                                        clearCertificate();
                                        jQuery('#operator').focus();
                                        if (partAfterManage != 'authority_person') {
                                            jQuery('#AuthorityPersonModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#AuthorityPersonModal').find('#submitbtn').prop('disabled', false);
                                    addedAuthorityPerson(true);

                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#AuthorityPersonModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                 toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#AuthorityPersonModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#AuthorityPersonModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#AuthorityPersonModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#AuthorityPersonModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestAuthorityPerson(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "authority_person-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#operator").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#operator_list').html(data.authorityPersonList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#operator").removeClass('file-loader');
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

jQuery(document).on('click', '#operator_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#operator_suggesion').val(suggest);
    var hidden = jQuery('#operator_suggesion').val();
    var suggestion_list = jQuery('#operator_list').html;
    jQuery('#AuthorityPersonModal').find('#operator').val(hidden)
    var operator = hidden;

    if (suggestion_list != '') {
        checkAuthorityPersonName(operator);
    }
    jQuery('#operator_list').html('');
});


let lastVerifiedAuthorityPerson = '';

jQuery(document).on('blur', '#operator', function () {
    let operator = jQuery(this).val().trim();

    if (operator === '') return;

    if (operator !== lastVerifiedAuthorityPerson) {
        lastVerifiedAuthorityPerson = operator;
        checkAuthorityPersonName(operator);
    }
});

jQuery(document).on('input', '#operator', function () {
    lastVerifiedAuthorityPerson = '';
});

function checkAuthorityPersonName(operator) {
    var id = jQuery('#AuthorityPersonModal').find('#commonAuthorityPersonForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-authority_person?operator=" + encodeURIComponent(operator) + "&id=" + id : "verify-authority_person?operator=" + encodeURIComponent(operator);
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

function verifyAuthorityPerson() {
    var AuthorityPersonName = jQuery('#operator').val();
    var suggestion_list = jQuery('#operator_list').html;

    if (suggestion_list != '') {
        checkAuthorityPersonName(AuthorityPersonName);
    }
}

jQuery('#AuthorityPersonModal').on('hide.bs.modal', function (e) {
    jQuery('#AuthorityPersonModal').find('#submitbtn').prop('disabled', false);
    resetAuthorityPersonForm();
    clearCertificate();
    jQuery('#AuthorityPersonModal').find('#add_new').hide();
});

$('#AuthorityPersonModal').on('shown.bs.modal', function () {
    const input = document.getElementById('operator');
    input?.focus();

    var formIdblank = jQuery('#commonAuthorityPersonForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#AuthorityPersonModal').find('#add_new').show();
    } else {
        jQuery('#AuthorityPersonModal').find('#add_new').hide();
        jQuery('#AuthorityPersonModal').find('#signature').prop('required', false);
        jQuery('#AuthorityPersonModal').find('#status').val('Active').trigger('change');
    }
});

jQuery('#AuthorityPersonModal').on('click', '#add_new', function () {
    jQuery('#AuthorityPersonModal').find('#operator').focus();
    resetAuthorityPersonForm();
    clearCertificate();
    toggleAuthorityFields();
    jQuery('#operator').focus();
    jQuery('#AuthorityPersonModal').find('#add_new').hide();
});

function getAuthorityPerson($this = null) {
    var formUrl = "get-authority_persons";
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
                    var stgDrpHtml = `<option value="">Select Operator</option>`;
                    for (let indx in data.authority_persons) {
                        stgDrpHtml += `<option value="${data.authority_persons[indx].authority_person_id}">${data.authority_persons[indx].operator}</option>`;
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

function addedAuthorityPerson($event) {
    if ($event == true) {
        getAuthorityPerson(".mst_authority_person");
    }
}

jQuery(document).on('change', '#signature', function (e) {
    AuthorityPersonfileUpload(e);
});

function validateImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.pdf)$/i;
    if (!allowedExtensions.exec(filePath)) {
        return false;
    }
    return true;
}

function AuthorityPersonfileUpload(e) {
    var form_data = new FormData();
    var target = e.target;
    var id = target.id;
    var files = target.files;
    var totalfiles = files.length;
    var oldImg = jQuery('#signature_doc').val();

    if (totalfiles > 0) {
        var notValid = 0;
        for (var index = 0; index < totalfiles; index++) {
            if (validateImage(files[index].name) == true) {
                form_data.append("docs[]", files[index]);
            } else {
                notValid = 1;
                toastr.error("Only (jpeg,jpg,png,gif,pdf) files are allowed.");
                jQuery('#' + id).val('');
                e.stopImmediatePropagation();
                return false;
            }
        }

        if (notValid == 0) {
            jQuery('#AuthorityPersonModal').find('#submitbtn').prop('disabled', true);
            jQuery('#' + id).parent().parent().find('.uneditable-input').addClass('file-loader');
            jQuery.ajax({
                url: "upload-docs",
                type: 'POST',
                data: form_data,
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    jQuery('#AuthorityPersonModal').find('#submitbtn').prop('disabled', false);
                    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    if (data.response_code == 1) {
                        if (oldImg != "" && oldImg.includes('temp_media/')) {
                            removeMedia(oldImg);
                        }

                        jQuery('#signature_doc').val(data.files);
                        jQuery('#signature_prev').attr('href', data.files_url);
                        jQuery('#signature_prev').removeClass('hide');
                        jQuery('#signature_remove').removeClass('hide').addClass('i-block');
                        console.log(data.response_message);
                    } else {
                        console.log(data.response_message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#AuthorityPersonModal').find('#submitbtn').prop('disabled', false);
                    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    jQuery('#' + id).parent().parent().find('.uneditable-input').addClass('iconfa-warning-sign upload-error');
                    var errMessage = JSON.parse(jqXHR.responseText);
                    if (errMessage.errors) {
                        toastr.error('File upload validation failed');
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
}

function removeFileAuthorityPerson(e) {
    e.stopImmediatePropagation();
    toastDetailDelete('Are You Sure, You Want To Delete ?', () => {
        var target = e.target;
        var id = target.getAttribute("data-remove");
        var oldImg = jQuery('#' + id + '_doc').val();

        if (oldImg != "" && oldImg.includes('temp_media/')) {
            removeMedia(oldImg);
        }
        
        jQuery('#' + id + '_doc').val('');
        jQuery('#' + id).val('');
        
        jQuery('#' + id + '_prev').attr('href', '#').addClass('hide');
        jQuery('#' + id + '_remove').addClass('hide').removeClass('i-block');
        
        jQuery('#' + id).prop('required', false);
    });
}

function removeMedia(docName) {
    let form_data2 = new FormData();
    form_data2.append('docs[]', docName);
    jQuery.ajax({
        url: "remove-docs",
        type: 'POST',
        data: form_data2,
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function (data) {
            if (data.response_code == 1) {
                console.log(data.response_message);
            } else {
                console.log(data.response_message);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.status == 401) {
                toastr.error(jqXHR.statusText);
            } else {
                console.log('Something went wrong!');
            }
        }
    });
}


function validateCertificateImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.pdf)$/i;
    return allowedExtensions.exec(filePath) ? true : false;
}

jQuery('#commonAuthorityPersonForm #certificate').on('change', function (e) {
    CertificatefileUpload(e);
});

function CertificatefileUpload(e) {
    var form_data = new FormData();
    var target = e.target;
    var id = target.id;
    var files = target.files;
    var totalfiles = files.length;
    var oldImg = jQuery('#certificate_doc').val();
    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');

    if (totalfiles > 0) {
        var notValid = 0;
        for (var index = 0; index < totalfiles; index++) {
            if (validateCertificateImage(files[index].name) == true) {
                form_data.append("docs[]", files[index]);
            } else {
                notValid = 1;
                toastr.error("Only (jpeg,jpg,png,gif,pdf) files are allowed.");
                jQuery('#certificate_doc').val('');
                jQuery('#' + id).val('');
                jQuery('#certificate_prev').attr('href', '#').addClass('hide');
                jQuery('#certificate_remove').removeClass('i-block').addClass('hide');
                e.stopImmediatePropagation();
                return false;
            }
        }

        if (notValid == 0) {
            jQuery('#AuthorityPersonModal').find('#submitbtn').prop('disabled', true);
            jQuery('#' + id).parent().parent().find('.uneditable-input').addClass('file-loader');
            jQuery.ajax({
                url: "upload-docs",
                type: 'POST',
                data: form_data,
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    jQuery('#AuthorityPersonModal').find('#submitbtn').prop('disabled', false);
                    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    if (data.response_code == 1) {
                        if (oldImg != "" && oldImg.includes('temp_media/')) {
                            removeMediaCertificate(oldImg);
                        }
                        $('#certificate_doc').val(data.files);
                        $('#certificate_prev').attr('href', data.files_url).removeClass('hide');
                        $('#certificate_remove').addClass('i-block').removeClass('hide');
                        toggleRequired();
                        console.log(data.response_message);
                    } else {
                        console.log(data.response_message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#AuthorityPersonModal').find('#submitbtn').prop('disabled', false);
                    $('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    $('#' + id).parent().parent().find('.uneditable-input').addClass('iconfa-warning-sign upload-error');
                    if (jqXHR.status == 401) {
                        toastr.error(jqXHR.statusText);
                    } else {
                        toastr.error('Something went wrong!');
                        console.log(JSON.parse(jqXHR.responseText));
                    }
                }
            });
        }
    } else {
        if (oldImg != "") {
            let fileName = oldImg.split('/').pop();
            let fileInput = jQuery('#enclosure_permission_to_use');
            let dataTransfer = new DataTransfer();
            dataTransfer.items.add(new File([""], fileName));
            fileInput[0].files = dataTransfer.files;
            return false;
        }
        clearCertificate();
    }
}

function toggleRequired() {
    let docVal = jQuery('#AuthorityPersonModal').find('#certificate_doc').val();
    if (docVal && docVal !== "") {
        jQuery('#AuthorityPersonModal').find('#certificate_doc').prop('required', false);
    } else {
        jQuery('#AuthorityPersonModal').find('#certificate_doc').prop('required', true);
    }
}

function clearCertificate() {
    jQuery('#AuthorityPersonModal').find('#certificate_doc').val('');
    jQuery('#AuthorityPersonModal').find('#certificate').val('');
    jQuery('#AuthorityPersonModal').find('#certificate_prev').attr('href', '#').addClass('hide');
    jQuery('#AuthorityPersonModal').find('#certificate_remove').removeClass('i-block').addClass('hide');
    toggleRequired();
}

function removeFileCertificate(e) {
    e.stopImmediatePropagation();
    toastDetailDelete('Are You Sure, You Want <lw-c>To</lw-c> Delete ?', () => {
        var oldImg = jQuery('#certificate_doc').val();
        if (oldImg != "" && oldImg.includes('temp_media/')) {
            removeMediaCertificate(oldImg);
        }
        clearCertificate();
    });
}

function removeMediaCertificate(docName) {
    let form_data2 = new FormData();
    form_data2.append('docs[]', docName);
    jQuery.ajax({
        url: "remove-docs",
        type: 'POST',
        data: form_data2,
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function (data) {
            console.log(data.response_message);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.status == 401) {
                toastr.error(jqXHR.statusText);
            } else {
                toastr.error('Something went wrong!');
                console.log(JSON.parse(jqXHR.responseText));
            }
        }
    });
}