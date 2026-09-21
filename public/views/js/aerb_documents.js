var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

// ---------------------------------------------------------------------------
// Edit
// ---------------------------------------------------------------------------
jQuery('#dyntable tbody').on('click', '.edit_aerb_documents', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillAerbDocument(data["id"]);
    }
});

function fetchAndFillAerbDocument(id) {
    if (!id) return;
    jQuery('#AerbDocumentModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-aerb_documents",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.aerb_documents_data != null) {
                var d = data.aerb_documents_data;
                jQuery('#AerbDocumentModal').find('#aerb_documents_name').val(d.aerb_documents_name);
                jQuery('#AerbDocumentModal').find('#remark').val(d.remark);

                if (d.aerb_documents_upload != "" && d.aerb_documents_upload != undefined && d.aerb_documents_upload != null) {
                    let fullPath = d.aerb_documents_upload;
                    let fileName = fullPath.split('/').pop();
                    jQuery('#AerbDocumentModal').find('#aerb_documents_upload_doc').val(fullPath);
                    jQuery('#AerbDocumentModal').find('#aerb_documents_upload_prev').attr('href', uploadURL + fullPath).removeClass('hide');
                    jQuery('#AerbDocumentModal').find('#aerb_documents_upload_remove').addClass('i-block').removeClass('hide');
                    let fileInput = jQuery('#AerbDocumentModal').find('#aerb_documents_upload');
                    let newFile = new DataTransfer();
                    newFile.items.add(new File([""], fileName));
                    fileInput[0].files = newFile.files;
                } else {
                    clearAerbDocumentUpload();
                }

                jQuery('#AerbDocumentModal').find('#id').val(d.aerb_documents_id);
                jQuery('#AerbDocumentModal').find('#add_new').show();

                const form = document.getElementById("commonAerbDocumentForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#AerbDocumentModal').find('#aerb_documents_name').focus();
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

function clearAerbDocumentUpload() {
    jQuery('#AerbDocumentModal').find('#aerb_documents_upload_doc').val('');
    jQuery('#AerbDocumentModal').find('#aerb_documents_upload').val('');
    jQuery('#AerbDocumentModal').find('#aerb_documents_upload_prev').attr('href', '#').addClass('hide');
    jQuery('#AerbDocumentModal').find('#aerb_documents_upload_remove').removeClass('i-block').addClass('hide');
}

// ---------------------------------------------------------------------------
// Reset
// ---------------------------------------------------------------------------
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#AerbDocumentModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonAerbDocumentForm").reset();
        const form = document.getElementById("commonAerbDocumentForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        clearAerbDocumentUpload();
        jQuery('#AerbDocumentModal').find('#aerb_documents_name').focus();
    } else {
        fetchAndFillAerbDocument(formId);
    }
});

// ---------------------------------------------------------------------------
// Submit (verify Name duplicate, then save)
// ---------------------------------------------------------------------------
$('#commonAerbDocumentForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#AerbDocumentModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#AerbDocumentModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#AerbDocumentModal').find('#commonAerbDocumentForm').find('#id').val();
    var aerb_documents_name = $("#aerb_documents_name").val();
    var formUrl = formId != undefined && formId != "" ? "update-aerb_documents" : "store-aerb_documents";
    var verifyUrl = formId != undefined && formId != "" ? "verify-aerb_documents?aerb_documents_name=" + encodeURIComponent(aerb_documents_name) + "&id=" + formId : "verify-aerb_documents?aerb_documents_name=" + encodeURIComponent(aerb_documents_name);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (aerb_documents_name != '' && aerb_documents_name != undefined) {
        // $.ajax({
        //     url: verifyUrl,
        //     type: 'GET',
        //     dataType: 'json',
        //     success: function (data) {
        //         if (data.response_code == 1) {
        //             toastr.error(data.response_message);
        //             jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        //             jQuery('#AerbDocumentModal').find('#submitbtn').prop('disabled', false);
        //         } else {
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
                        jQuery('#AerbDocumentModal').find('#submitbtn').prop('disabled', false);
                        addedAerbDocument(true);
                    }
                    else if (formId == undefined || formId == "") {
                        function nextFn() {
                            document.getElementById("commonAerbDocumentForm").reset();
                            const form = document.getElementById("commonAerbDocumentForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            clearAerbDocumentUpload();
                            jQuery('#aerb_documents_name').focus();
                            if (partAfterManage != 'aerb_documents') {
                                jQuery('#AerbDocumentModal').modal('hide');
                            }
                        }
                        toastSuccess(data.response_message, nextFn);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#AerbDocumentModal').find('#submitbtn').prop('disabled', false);
                        addedAerbDocument(true);

                    }
                    else {
                        toastError(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#AerbDocumentModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#AerbDocumentModal').find('#submitbtn').prop('disabled', false);
                }
            },
            error: function (xhr) {
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                jQuery('#AerbDocumentModal').find('#submitbtn').prop('disabled', false);
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let errorMsg = '';
                    $.each(errors, function (key, value) {
                        errorMsg += value + '\n';
                    });
                    toastr.error(errorMsg);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#AerbDocumentModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#AerbDocumentModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
        //             }
        //         }
        //     });
    }
});

// ---------------------------------------------------------------------------
// Duplicate check (blur) + autocomplete
// ---------------------------------------------------------------------------
// let lastVerifiedAerbDocument = '';
// jQuery(document).on('blur', '#aerb_documents_name', function () {
//     let val = jQuery(this).val().trim();
//     if (val === '') return;
//     if (val !== lastVerifiedAerbDocument) {
//         lastVerifiedAerbDocument = val;
//         checkAerbDocumentName(val);
//     }
// });
// jQuery(document).on('input', '#aerb_documents_name', function () {
//     lastVerifiedAerbDocument = '';
// });

function checkAerbDocumentName(aerb_documents_name) {
    var id = jQuery('#AerbDocumentModal').find('#commonAerbDocumentForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-aerb_documents?aerb_documents_name=" + encodeURIComponent(aerb_documents_name) + "&id=" + id : "verify-aerb_documents?aerb_documents_name=" + encodeURIComponent(aerb_documents_name);
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

function suggestAerbDocument(e, $this) {
    if (e.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "aerb_documents_name-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#aerb_documents_name").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#aerb_documents_name_list').html(data.aerbDocumentsNameList);
                } else {
                    toastr.error(data.response_message);
                }
            }
        });
    }
}

jQuery(document).on('click', '#aerb_documents_name_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#AerbDocumentModal').find('#aerb_documents_name').val(suggest);
    if (jQuery('#aerb_documents_name_list').html() != '') {
        checkAerbDocumentName(suggest);
    }
    jQuery('#aerb_documents_name_list').html('');
});

// ---------------------------------------------------------------------------
// AERB Document Upload file upload (temp upload via upload-docs)
// ---------------------------------------------------------------------------
function validateAerbFile(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.pdf)$/i;
    return allowedExtensions.exec(filePath) ? true : false;
}

jQuery('#commonAerbDocumentForm #aerb_documents_upload').on('change', function (e) {
    AerbDocumentUploadfileUpload(e);
});

function AerbDocumentUploadfileUpload(e) {
    var form_data = new FormData();
    var target = e.target;
    var id = target.id;
    var files = target.files;
    var totalfiles = files.length;
    var oldImg = jQuery('#aerb_documents_upload_doc').val();
    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');

    if (totalfiles > 0) {
        var notValid = 0;
        for (var index = 0; index < totalfiles; index++) {
            if (validateAerbFile(files[index].name) == true) {
                form_data.append("docs[]", files[index]);
            } else {
                notValid = 1;
                toastr.error("Only (jpeg,jpg,png,gif,pdf) files are allowed.");
                jQuery('#aerb_documents_upload_doc').val('');
                jQuery('#' + id).val('');
                jQuery('#aerb_documents_upload_prev').attr('href', '#').addClass('hide');
                jQuery('#aerb_documents_upload_remove').removeClass('i-block').addClass('hide');
                e.stopImmediatePropagation();
                return false;
            }
        }

        if (notValid == 0) {
            jQuery('#AerbDocumentModal').find('#submitbtn').prop('disabled', true);
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
                    jQuery('#AerbDocumentModal').find('#submitbtn').prop('disabled', false);
                    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    if (data.response_code == 1) {
                        if (oldImg != "") {
                            removeMediaAerbDocument(oldImg);
                        }
                        $('#aerb_documents_upload_doc').val(data.files);
                        $('#aerb_documents_upload_prev').attr('href', data.files_url).removeClass('hide');
                        $('#aerb_documents_upload_remove').addClass('i-block').removeClass('hide');
                        console.log(data.response_message);
                    } else {
                        console.log(data.response_message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#AerbDocumentModal').find('#submitbtn').prop('disabled', false);
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
            let fileInput = jQuery('#aerb_documents_upload');
            let dataTransfer = new DataTransfer();
            dataTransfer.items.add(new File([""], fileName));
            fileInput[0].files = dataTransfer.files;
            return false;
        }
        clearAerbDocumentUpload();
    }
}

function removeFileAerbDocument(e) {
    e.stopImmediatePropagation();
    toastDetailDelete('Are You Sure, You Want <lw-c>To</lw-c> Delete ?', () => {
        var oldImg = jQuery('#aerb_documents_upload_doc').val();
        if (oldImg != "") {
            removeMediaAerbDocument(oldImg);
        }
        clearAerbDocumentUpload();
    });
}

function removeMediaAerbDocument(docName) {
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

// ---------------------------------------------------------------------------
// Modal lifecycle
// ---------------------------------------------------------------------------
jQuery('#AerbDocumentModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#AerbDocumentModal');
    thisForm.find('#submitbtn').prop('disabled', false);
    thisForm.find('#id').val('');
    document.getElementById("commonAerbDocumentForm").reset();
    clearAerbDocumentUpload();
    jQuery('#AerbDocumentModal').find('#add_new').hide();
});

$('#AerbDocumentModal').on('shown.bs.modal', function () {
    const input = document.getElementById('aerb_documents_name');
    input?.focus();

    var formIdblank = jQuery('#commonAerbDocumentForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#AerbDocumentModal').find('#add_new').show();
    } else {
        jQuery('#AerbDocumentModal').find('#add_new').hide();
    }
});

jQuery('#AerbDocumentModal').on('click', '#add_new', function () {
    jQuery('#AerbDocumentModal').find('#id').val('');
    document.getElementById("commonAerbDocumentForm").reset();
    const form = document.getElementById("commonAerbDocumentForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    clearAerbDocumentUpload();
    jQuery('#aerb_documents_name').focus();
    jQuery('#AerbDocumentModal').find('#add_new').hide();
});

// ---------------------------------------------------------------------------
// Dropdown population for other modules
// ---------------------------------------------------------------------------
function getAerbDocument($this = null) {
    var formUrl = "get-aerb_documents";
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
                    var stgDrpHtml = `<option value="">Select AERB Document</option>`;
                    for (let indx in data.aerb_documents) {
                        stgDrpHtml += `<option value="${data.aerb_documents[indx].aerb_documents_id}">${data.aerb_documents[indx].aerb_documents_name}</option>`;
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
            console.log(JSON.parse(jqXHR.responseText));
        }
    });
}

function addedAerbDocument($event) {
    if ($event == true) {
        getAerbDocument(".mst_aerb_documents");
    }
}
