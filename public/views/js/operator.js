jQuery('#dyntable tbody').on('click', '.edit_operator', function () {
    jQuery('#OperatorModal').modal('show');
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-operator",
        type: 'GET',
        data: "id=" + data["id"],
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                if (data.operator_data != null) {
                    jQuery('#commonOperatorForm').find('#operator').val(data.operator_data.operator);
                    jQuery('#commonOperatorForm').find('#category').val(data.operator_data.category).trigger("change.select2");
                    getCategory(data.operator_data.category);
                    jQuery('#commonOperatorForm').find('#designation').val(data.operator_data.designation);
                    jQuery('#commonOperatorForm').find('#status').val(data.operator_data.status).trigger("change.select2");
                    
                    var imageUrl = data.operator_data.image_url || '';
                    if (imageUrl) {
                        $('#sign_preview').attr('src', imageUrl).removeClass('d-none');
                    } else {
                        $('#sign_preview').attr('src', '').addClass('d-none');
                    }

                    jQuery('#commonOperatorForm').find('#id').val(data.operator_data.id);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
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
});

$('#upload_sign_file').on('change', function () {
    let file = this.files[0];
    if (file) {
        let allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            toastr.error('Only JPG, JPEG, PNG, GIF image files are allowed.');
            $(this).val('');
            return false;
        }
    }
});

$('#commonOperatorForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#OperatorModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#OperatorModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    let fileInput = document.getElementById('upload_sign_file');
    if (fileInput && fileInput.files.length > 0) {
        let file = fileInput.files[0];
        let allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            toastr.error('Only JPG, JPEG, PNG, GIF image files are allowed.');
            return false;
        }
    }

    var formId = jQuery('#OperatorModal').find('#commonOperatorForm').find('#id').val();
    var operator = jQuery('#operator').val();
    var category = jQuery('#category option:selected').val();
    var formUrl = formId != undefined && formId != "" ? "update-operator" : "store-operator";
    var operatorUrl = formId != undefined && formId != "" ? "verify-operator?operator=" + encodeURIComponent(operator) + "&category=" + encodeURIComponent(category) + "&id=" + formId : "verify-operator?operator=" + encodeURIComponent(operator) + "&category=" + encodeURIComponent(category);
    let formData = new FormData(form);
    if (operator != '' && operator != undefined) {
        $.ajax({
            url: operatorUrl,
            type: 'GET',
            dataType: 'json',
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#OperatorModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#OperatorModal').find('#submitbtn').prop('disabled', false);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonOperatorForm").reset();

                                        const signPreview = document.getElementById("sign_preview");
                                        const signInput = document.getElementById("upload_sign_file");
                                        const deleteImageInput = document.getElementById("delete_image");

                                        signPreview.src = '';
                                        signPreview.classList.add('d-none');
                                        signInput.value = '';
                                        deleteImageInput.value = '0';

                                        const form = document.getElementById("commonOperatorForm");
                                        if (form) {
                                            form.classList.remove('was-validated'); 
                                        }
                                        setTimeout(function () {
                                            let sel = jQuery('#category')
                                                .next('.select2-container')
                                                .find('.select2-selection');
                                    
                                            sel.attr('tabindex', 0).focus();
                                        }, 20);
                                        $("#category").val('').trigger('change');
                                        jQuery('#status').val('1').trigger('change');
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#OperatorModal').find('#submitbtn').prop('disabled', false);
                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#OperatorModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                 toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#OperatorModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#OperatorModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#OperatorModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#OperatorModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestOperator(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "operator-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#operator").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#operator_list').html(data.operatorList);
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
    jQuery('#OperatorModal').find('#operator').val(hidden)
    var operator = hidden;
    if (suggestion_list != '') {
        checkOperator(operator);
    }
    jQuery('#operator_list').html('');
});

function checkOperator() {
    var operator = jQuery('#operator').val();
    var category = jQuery('#category option:selected').val();
    var id = jQuery('#OperatorModal').find('#commonOperatorForm').find('#id').val();
    var formUrl = "verify-operator?operator=" + encodeURIComponent(operator) + "&category=" + encodeURIComponent(category);
    if (id != undefined && id != "") {
        formUrl += "&id=" + id;
    }
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

function verifyOperator() {
    var operator = jQuery('#operator').val();
    var suggestion_list = jQuery('#operator_list').html;
    if (suggestion_list != '') {
        checkOperator(operator);
    }
}

function suggestDesignation(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "operator-designation-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#designation").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#designation_list').html(data.designationList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#designation").removeClass('file-loader');
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

jQuery('#OperatorModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#OperatorModal');
    thisForm.find('#id').val('');
    thisForm.find('#category').val('').trigger('change');
    const signPreview = document.getElementById("sign_preview");
    signPreview.classList.add('d-none');
    document.getElementById("commonOperatorForm").reset();
});

jQuery('#OperatorModal').on('shown.bs.modal', function () {
    setTimeout(function () {
        let sel = jQuery('#category')
            .next('.select2-container')
            .find('.select2-selection');
        sel.attr('tabindex', 0).focus();
    }, 20);
});

function getOperator(category) {
    var id = jQuery('#OperatorModal').find('#commonOperatorForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "get-operators?category=" + category + "&id=" + id : "get-operators?category=" + category;

    jQuery.ajax({
        url: formUrl,
        type: 'GET',
        dataType: 'json',
        processData: false,
        headers: headerOpt,
        success: function (data) {
            if (data.response_code == 1) {
                if (category != null) {
                    var stgDrpHtml = `<option value="">Select Operator</option>`;
                    if(category == "assistant") {
                        for (let indx in data.assistantOperator) {
                            stgDrpHtml += `<option value="${data.assistantOperator[indx].id}">${data.assistantOperator[indx].operator}</option>`;
                        }
                    }

                    if(category == "tested_by") {
                        for (let indx in data.testedbyOperator) {
                            stgDrpHtml += `<option value="${data.testedbyOperator[indx].id}">${data.testedbyOperator[indx].operator}</option>`;
                        }
                    }

                    if(category == "radiographer") {
                        for (let indx in data.radiographerOperator) {
                            stgDrpHtml += `<option value="${data.radiographerOperator[indx].id}">${data.radiographerOperator[indx].operator}</option>`;
                        }
                    }

                    if(category == "signature") {
                        for (let indx in data.signatureOperator) {
                            stgDrpHtml += `<option value="${data.signatureOperator[indx].id}">${data.signatureOperator[indx].operator}</option>`;
                        }
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

function showValidationTooltip(input, show) {
    const tooltip = jQuery(input).siblings('.invalid-tooltip');
    if (show) {
        tooltip.show();
        jQuery(input).addClass('is-invalid');
    } else {
        tooltip.hide();
        jQuery(input).removeClass('is-invalid');
    }
}

(function () {
    'use strict';
    var form = document.getElementById('commonOperatorForm');
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            const designation = document.getElementById('designation');
            if (designation.required && !designation.value.trim()) {
                showValidationTooltip(designation, true);
            } else {
                showValidationTooltip(designation, false);
            }
        } else {
            showValidationTooltip(document.getElementById('designation'), false);
        }
        form.classList.add('was-validated');
    }, false);
})();

function getCategory() {
    var cat = jQuery('#category').val();
    var designation = jQuery('#designation');
    var label = jQuery('#designation_label');
    var tooltip = jQuery('#designationTooltip');
    if (cat === 'assistant' || cat === 'radiographer') {
        designation.prop('readonly', true).attr('tabindex', '-1').blur();
        designation.prop('required', false).val('');
        label.find('sup.astric').remove();
        tooltip.hide();
        designation.removeClass('is-invalid');
    } else if (cat === 'tested_by' || cat === 'signature') {
        designation.prop('readonly', false).removeAttr('tabindex');
        designation.prop('required', true);
        if (label.find('sup.astric').length === 0) {
            label.append(' <sup class="astric">*</sup>');
        }
        tooltip.hide();
    } else {
        designation.prop('readonly', false).removeAttr('tabindex');
        designation.prop('required', false);
        label.find('sup.astric').remove();
        tooltip.hide();
        designation.removeClass('is-invalid');
    }
}

jQuery('#category').on('change',function () {
    getCategory();
});

// new working code start
document.addEventListener("DOMContentLoaded", function () {
    const signInput = document.getElementById("upload_sign_file");
    const signPreview = document.getElementById("sign_preview");
    const deleteBtn = document.getElementById("delete_sign_btn");

    signInput?.addEventListener("change", function () {
        if (this.files && this.files[0]) {
            let reader = new FileReader();
            reader.onload = function (e) {
                signPreview.src = e.target.result;
                signPreview.classList.remove('d-none');
                document.getElementById("delete_image").value = '0';
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    deleteBtn?.addEventListener("click", function () {
        signPreview.src = '';
        signPreview.classList.add('d-none');
        signInput.value = '';
        document.getElementById("delete_image").value = '1';
    });
});
// new working code end

// old working code start
// document.addEventListener("DOMContentLoaded", function() {
//     const signInput = document.getElementById("upload_sign_file");
//     const signPreview = document.getElementById("sign_preview");

//     signInput?.addEventListener("change", function () {
//         if (this.files && this.files[0]) {
//             let reader = new FileReader();
//             reader.onload = function (e) {
//                 signPreview.src = e.target.result;
//                 document.getElementById("delete_image").value = '0';
//             };
//             reader.readAsDataURL(this.files[0]);
//         }
//     });

//     const deleteBtn = document.getElementById("delete_sign_btn");
//     deleteBtn?.addEventListener("click", function () {
//         signPreview.src = ''; 
//         document.getElementById("upload_sign_file").value = '';
//         document.getElementById("delete_image").value = '1';
//     });
// });
// old working code end