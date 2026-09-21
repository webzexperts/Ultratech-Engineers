var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

toggleRadis();
jQuery("input[name='enclosure_type_value_fix']").on("change", function () {
    toggleRadis()
});

function toggleRadis(){
    var radio = jQuery('#EnclosureModal').find('input[name*="enclosure_type_value_fix"]:checked').val();

    if (radio == "Enclosure") {
        jQuery("#layoutRow").show();
        jQuery("#enclosure_layout").prop("required", true);
        jQuery("#enclosure_permission_to_use").prop("required", true);
    } else {
        jQuery("#layoutRow").hide();
        jQuery("#enclosure_layout").prop("required", false).val("");
    }
}

// ---------------------------------------------------------------------------
// Edit
// ---------------------------------------------------------------------------
jQuery('#dyntable tbody').on('click', '.edit_enclosure', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillEnclosure(data["id"]);
    }
});

function fetchAndFillEnclosure(id) {
    if (!id) return;
    jQuery('#EnclosureModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-enclosure",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.enclosure_data != null) {
                var d = data.enclosure_data;
                jQuery('#EnclosureModal').find('#enclosure_name').val(d.enclosure_name);
                jQuery('#EnclosureModal').find('#enclosure_no').val(d.enclosure_no);
                jQuery('#EnclosureModal').find('#enclosure_validity').val(d.enclosure_validity);

                var enclosure_type_value_fix = d.enclosure_type_value_fix;
                jQuery('#EnclosureModal').find('input[name*="enclosure_type_value_fix"][value="' + enclosure_type_value_fix + '"]').prop('checked', true).change();
                setRadioReadonly("input[name='enclosure_type_value_fix']", true);

                if (d.enclosure_layout != "" && d.enclosure_layout != undefined && d.enclosure_layout != null) {
                    let fullPath = d.enclosure_layout;
                    let fileName = fullPath.split('/').pop();
                    jQuery('#EnclosureModal').find('#enclosure_layout_doc').val(fullPath);
                    jQuery('#EnclosureModal').find('#enclosure_layout_prev').attr('href', uploadURL + fullPath).removeClass('hide');
                    jQuery('#EnclosureModal').find('#enclosure_layout_remove').addClass('i-block').removeClass('hide');
                    
                    let fileInput = jQuery('#EnclosureModal').find('#enclosure_layout');
                    let newFile = new DataTransfer();
                    newFile.items.add(new File([""], fileName));
                    fileInput[0].files = newFile.files;
                } else {
                    clearEnclosureLayout();
                }
                toggleEnclosureLayoutRequired();


                if (d.enclosure_permission_to_use != "" && d.enclosure_permission_to_use != undefined && d.enclosure_permission_to_use != null) {
                    let fullPath = d.enclosure_permission_to_use;
                    let fileName = fullPath.split('/').pop();
                    jQuery('#EnclosureModal').find('#enclosure_permission_to_use_doc').val(fullPath);
                    jQuery('#EnclosureModal').find('#enclosure_permission_to_use_prev').attr('href', uploadURL + fullPath).removeClass('hide');
                    jQuery('#EnclosureModal').find('#enclosure_permission_to_use_remove').addClass('i-block').removeClass('hide');
                    
                    let fileInput = jQuery('#EnclosureModal').find('#enclosure_permission_to_use');
                    let newFile = new DataTransfer();
                    newFile.items.add(new File([""], fileName));
                    fileInput[0].files = newFile.files;
                } else {
                    clearPermissionToUse();
                }
                toggleRequired();

                jQuery('#EnclosureModal').find('#id').val(d.enclosure_id);
                jQuery('#EnclosureModal').find('#add_new').show();

                const form = document.getElementById("commonEnclosureForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#EnclosureModal').find('#enclosure_name').focus();
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

function toggleEnclosureLayoutRequired() {
    let docVal = jQuery('#EnclosureModal').find('#enclosure_layout_doc').val();
    if (docVal && docVal !== "") {
        jQuery('#EnclosureModal').find('#enclosure_layout').prop('required', false);
    } else {
        jQuery('#EnclosureModal').find('#enclosure_layout').prop('required', true);
    }
}

function clearEnclosureLayout() {
    jQuery('#EnclosureModal').find('#enclosure_layout_doc').val('');
    jQuery('#EnclosureModal').find('#enclosure_layout').val('');
    jQuery('#EnclosureModal').find('#enclosure_layout_prev').attr('href', '#').addClass('hide');
    jQuery('#EnclosureModal').find('#enclosure_layout_remove').removeClass('i-block').addClass('hide');
    toggleEnclosureLayoutRequired();
}

// ---------------------------------------------------------------------------
// Reset
// ---------------------------------------------------------------------------
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#EnclosureModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonEnclosureForm").reset();
        const form = document.getElementById("commonEnclosureForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        clearEnclosureLayout();
        clearPermissionToUse();
        // jQuery('#EnclosureModal').find('#enclosure_name').focus();
        jQuery("input[name='enclosure_type_value_fix'][value='Enclosure']").focus();
    } else {
        fetchAndFillEnclosure(formId);
    }
});

// ---------------------------------------------------------------------------
// Submit (verify Name + No. for duplicates, then save)
// ---------------------------------------------------------------------------
$('#commonEnclosureForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#EnclosureModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#EnclosureModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#EnclosureModal').find('#commonEnclosureForm').find('#id').val();
    var enclosure_name = $("#enclosure_name").val();
    var enclosure_no = $("#enclosure_no").val();
    var formUrl = formId != undefined && formId != "" ? "update-enclosure" : "store-enclosure";
    var verifyUrl = "verify-enclosure?enclosure_name=" + encodeURIComponent(enclosure_name) + "&enclosure_no=" + encodeURIComponent(enclosure_no) + (formId != undefined && formId != "" ? "&id=" + formId : "");
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (enclosure_name != '' && enclosure_name != undefined && enclosure_no != '' && enclosure_no != undefined) {
        $.ajax({
            url: verifyUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#EnclosureModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#EnclosureModal').find('#submitbtn').prop('disabled', false);
                                    addedEnclosure(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonEnclosureForm").reset();
                                        const form = document.getElementById("commonEnclosureForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }
                                        clearEnclosureLayout();
                                        clearPermissionToUse();
                                        jQuery('#enclosure_name').focus();
                                        if (partAfterManage != 'enclosure') {
                                            jQuery('#EnclosureModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#EnclosureModal').find('#submitbtn').prop('disabled', false);
                                    addedEnclosure(true);

                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#EnclosureModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                 toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#EnclosureModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#EnclosureModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#EnclosureModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#EnclosureModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

// ---------------------------------------------------------------------------
// Individual duplicate checks (blur)
// ---------------------------------------------------------------------------
let lastVerifiedEnclosureName = '';
jQuery(document).on('blur', '#enclosure_name', function () {
    let val = jQuery(this).val().trim();
    if (val === '') return;
    if (val !== lastVerifiedEnclosureName) {
        lastVerifiedEnclosureName = val;
        checkEnclosureField('enclosure_name', val);
    }
});
jQuery(document).on('input', '#enclosure_name', function () {
    lastVerifiedEnclosureName = '';
});

let lastVerifiedEnclosureNo = '';
jQuery(document).on('blur', '#enclosure_no', function () {
    let val = jQuery(this).val().trim();
    if (val === '') return;
    if (val !== lastVerifiedEnclosureNo) {
        lastVerifiedEnclosureNo = val;
        checkEnclosureField('enclosure_no', val);
    }
});
jQuery(document).on('input', '#enclosure_no', function () {
    lastVerifiedEnclosureNo = '';
});

function checkEnclosureField(field, value) {
    var id = jQuery('#EnclosureModal').find('#commonEnclosureForm').find('#id').val();
    var formUrl = "verify-enclosure?" + field + "=" + encodeURIComponent(value) + (id != undefined && id != "" ? "&id=" + id : "");
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

// ---------------------------------------------------------------------------
// Enclosure Name / No. autocomplete (optional, parity with Location)
// ---------------------------------------------------------------------------
function suggestEnclosureName(e, $this) {
    if (e.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "enclosure_name-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#enclosure_name").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#enclosure_name_list').html(data.enclosureNameList);
                } else {
                     toastr.error(data.response_message);
                }
            }
        });
    }
}

function suggestEnclosureNo(e, $this) {
    if (e.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "enclosure_no-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#enclosure_no").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#enclosure_no_list').html(data.enclosureNoList);
                } else {
                     toastr.error(data.response_message);
                }
            }
        });
    }
}

jQuery(document).on('click', '#enclosure_name_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#EnclosureModal').find('#enclosure_name').val(suggest);
    if (jQuery('#enclosure_name_list').html() != '') {
        checkEnclosureField('enclosure_name', suggest);
    }
    jQuery('#enclosure_name_list').html('');
});

jQuery(document).on('click', '#enclosure_no_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#EnclosureModal').find('#enclosure_no').val(suggest);
    if (jQuery('#enclosure_no_list').html() != '') {
        checkEnclosureField('enclosure_no', suggest);
    }
    jQuery('#enclosure_no_list').html('');
});

// ---------------------------------------------------------------------------
// Enclosure Layout file upload (temp upload via upload-docs, mirrors NABL symbol)
// ---------------------------------------------------------------------------
function validateEnclosureImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.pdf)$/i;
    return allowedExtensions.exec(filePath) ? true : false;
}

jQuery('#commonEnclosureForm #enclosure_layout').on('change', function (e) {
    EnclosureLayoutfileUpload(e);
});

function EnclosureLayoutfileUpload(e) {
    var form_data = new FormData();
    var target = e.target;
    var id = target.id;
    var files = target.files;
    var totalfiles = files.length;
    var oldImg = jQuery('#enclosure_layout_doc').val();
    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');

    if (totalfiles > 0) {
        var notValid = 0;
        for (var index = 0; index < totalfiles; index++) {
            if (validateEnclosureImage(files[index].name) == true) {
                form_data.append("docs[]", files[index]);
            } else {
                notValid = 1;
                toastr.error("Only (jpeg,jpg,png,gif,pdf) files are allowed.");
                clearEnclosureLayout();
                e.stopImmediatePropagation();
                return false;
            }
        }

        if (notValid == 0) {
            jQuery('#EnclosureModal').find('#submitbtn').prop('disabled', true);
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
                    jQuery('#EnclosureModal').find('#submitbtn').prop('disabled', false);
                    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    if (data.response_code == 1) {
                        if (oldImg != "" && oldImg.includes('temp_media/')) {
                            removeMediaEnclosure(oldImg);
                        }
                        $('#enclosure_layout_doc').val(data.files);
                        $('#enclosure_layout_prev').attr('href', data.files_url).removeClass('hide');
                        $('#enclosure_layout_remove').addClass('i-block').removeClass('hide');
                        toggleEnclosureLayoutRequired();
                        console.log(data.response_message);
                    } else {
                        console.log(data.response_message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#EnclosureModal').find('#submitbtn').prop('disabled', false);
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
            let fileInput = jQuery('#enclosure_layout');
            let dataTransfer = new DataTransfer();
            dataTransfer.items.add(new File([""], fileName));
            fileInput[0].files = dataTransfer.files;
            return false;
        }
        clearEnclosureLayout();
    }
}

function removeFileEnclosureLayout(e) {
    e.stopImmediatePropagation();
    toastDetailDelete('Are You Sure, You Want <lw-c>To</lw-c> Delete ?', () => {
        var oldImg = jQuery('#enclosure_layout_doc').val();
        if (oldImg != "" && oldImg.includes('temp_media/')) {
            removeMediaEnclosure(oldImg);
        }
        clearEnclosureLayout();
    });
}

function removeMediaEnclosure(docName) {
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
jQuery('#EnclosureModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#EnclosureModal');
    thisForm.find('#id').val('');
    document.getElementById("commonEnclosureForm").reset();
    clearEnclosureLayout();
    clearPermissionToUse();
    jQuery('#EnclosureModal').find('#add_new').hide();
    setRadioReadonly("input[name='enclosure_type_value_fix']", false);
});

$('#EnclosureModal').on('shown.bs.modal', function () {
    const input = document.getElementById('enclosure_name');
    input?.focus();

    var formIdblank = jQuery('#commonEnclosureForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#EnclosureModal').find('#add_new').show();
    } else {
        jQuery('#EnclosureModal').find('#add_new').hide();
    }
    toggleEnclosureLayoutRequired();
    toggleRequired();
});

jQuery('#EnclosureModal').on('click', '#add_new', function () {
    jQuery('#EnclosureModal').find('#id').val('');
    document.getElementById("commonEnclosureForm").reset();
    const form = document.getElementById("commonEnclosureForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    clearEnclosureLayout();
    clearPermissionToUse();
    toggleRadis();
    jQuery('#enclosure_name').focus();
    jQuery('#EnclosureModal').find('#add_new').hide();
    setRadioReadonly("input[name='enclosure_type_value_fix']", false);
    jQuery("input[name='enclosure_type_value_fix'][value='Enclosure']").focus();
});

// ---------------------------------------------------------------------------
// Dropdown population for other modules
// ---------------------------------------------------------------------------
function getEnclosure($this = null) {
    var formUrl = "get-enclosures";
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
                    var stgDrpHtml = `<option value="">Select Enclosure</option>`;
                    for (let indx in data.enclosures) {
                        stgDrpHtml += `<option value="${data.enclosures[indx].enclosure_id}">${data.enclosures[indx].enclosure_name}</option>`;
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

function addedEnclosure($event) {
    if ($event == true) {
        getEnclosure(".mst_enclosure");
    }
}


function validatePermissionImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.pdf)$/i;
    return allowedExtensions.exec(filePath) ? true : false;
}

jQuery('#commonEnclosureForm #enclosure_permission_to_use').on('change', function (e) {
    EnclosurePermissionfileUpload(e);
});

function EnclosurePermissionfileUpload(e) {
    var form_data = new FormData();
    var target = e.target;
    var id = target.id;
    var files = target.files;
    var totalfiles = files.length;
    var oldImg = jQuery('#enclosure_permission_to_use_doc').val();
    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');

    if (totalfiles > 0) {
        var notValid = 0;
        for (var index = 0; index < totalfiles; index++) {
            if (validatePermissionImage(files[index].name) == true) {
                form_data.append("docs[]", files[index]);
            } else {
                notValid = 1;
                toastr.error("Only (jpeg,jpg,png,gif,pdf) files are allowed.");
                clearPermissionToUse();
                e.stopImmediatePropagation();
                return false;
            }
        }

        if (notValid == 0) {
            jQuery('#EnclosureModal').find('#submitbtn').prop('disabled', true);
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
                    jQuery('#EnclosureModal').find('#submitbtn').prop('disabled', false);
                    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    if (data.response_code == 1) {
                        if (oldImg != "" && oldImg.includes('temp_media/')) {
                            removeFilePermissionToUse(oldImg);
                        }
                        $('#enclosure_permission_to_use_doc').val(data.files);
                        $('#enclosure_permission_to_use_prev').attr('href', data.files_url).removeClass('hide');
                        $('#enclosure_permission_to_use_remove').addClass('i-block').removeClass('hide');
                        toggleRequired();
                        console.log(data.response_message);
                    } else {
                        console.log(data.response_message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#EnclosureModal').find('#submitbtn').prop('disabled', false);
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
        clearPermissionToUse();
    }
}

function clearPermissionToUse() {
    jQuery('#EnclosureModal').find('#enclosure_permission_to_use_doc').val('');
    jQuery('#EnclosureModal').find('#enclosure_permission_to_use').val('');
    jQuery('#EnclosureModal').find('#enclosure_permission_to_use_prev').attr('href', '#').addClass('hide');
    jQuery('#EnclosureModal').find('#enclosure_permission_to_use_remove').removeClass('i-block').addClass('hide');
    toggleRequired();
}

function toggleRequired() {
    let docVal = jQuery('#EnclosureModal').find('#enclosure_permission_to_use_doc').val();
    if (docVal && docVal !== "") {
        jQuery('#EnclosureModal').find('#enclosure_permission_to_use_doc').prop('required', false);
    } else {
        jQuery('#EnclosureModal').find('#enclosure_permission_to_use_doc').prop('required', true);
    }
}

function removeFilePermissionToUse(e) {
    e.stopImmediatePropagation();
    toastDetailDelete('Are You Sure, You Want <lw-c>To</lw-c> Delete ?', () => {
        var oldImg = jQuery('#enclosure_permission_to_use_doc').val();
        if (oldImg != "" && oldImg.includes('temp_media/')) {
            removeMediaPermission(oldImg);
        }
        clearPermissionToUse();
    });
}

function removeMediaPermission(docName) {
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