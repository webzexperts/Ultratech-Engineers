var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

// Edit material_type row click
jQuery('#dyntable tbody').on('click', '.edit_material_type', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillMaterialType(data["id"]);
    }
});

// Function to fetch and fill material_type data
function fetchAndFillMaterialType(id) {
    if (!id) return;
    jQuery('#MaterialTypeModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-material_type",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.material_type_data != null) {
                jQuery('#MaterialTypeModal').find('#material_type').val(data.material_type_data.material_type);
                jQuery('#MaterialTypeModal').find('#id').val(data.material_type_data.material_type_id);
                jQuery('#MaterialTypeModal').find('#add_new').show();

                const form = document.getElementById("commonMaterialTypeForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#MaterialTypeModal').find('#material_type').focus();
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

// Reset button click for material_type modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#MaterialTypeModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonMaterialTypeForm").reset();
        const form = document.getElementById("commonMaterialTypeForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        jQuery('#MaterialTypeModal').find('#material_type').focus();
    } else {
        fetchAndFillMaterialType(formId);
    }
});

$('#commonMaterialTypeForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#MaterialTypeModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#MaterialTypeModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#MaterialTypeModal').find('#commonMaterialTypeForm').find('#id').val();
    var material_type = jQuery('#MaterialTypeModal').find('#material_type').val();
    var formUrl = formId != undefined && formId != "" ? "update-material_type" : "store-material_type";
    var MaterialTypeUrl = formId != undefined && formId != "" ? "verify-material_type?material_type=" + encodeURIComponent(material_type) + "&id=" + formId : "verify-material_type?material_type=" + encodeURIComponent(material_type);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (material_type != '' && material_type != undefined) {
        $.ajax({
            url: MaterialTypeUrl,
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#MaterialTypeModal').find('#submitbtn').prop('disabled', false);
                } else {
                    $.ajax({
                        type: 'POST',
                        url: formUrl,
                        data: formData,
                        contentType: false,
                        processData: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (data) {
                            if (data.response_code == 1) {
                                if (formId != undefined && formId != "") {
                                    function redirectFn() {
                                        window.location.reload();
                                    }
                                    toastSuccess(data.response_message, redirectFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#MaterialTypeModal').find('#submitbtn').prop('disabled', false);
                                    addedMaterialType(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonMaterialTypeForm").reset();
                                        const form = document.getElementById("commonMaterialTypeForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#MaterialTypeModal').find('#material_type').focus();
                                        if (partAfterManage != 'material_type') {
                                            jQuery('#MaterialTypeModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#MaterialTypeModal').find('#submitbtn').prop('disabled', false);
                                    addedMaterialType(true);

                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#MaterialTypeModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#MaterialTypeModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#MaterialTypeModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#MaterialTypeModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#MaterialTypeModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestMaterialType(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "material_type-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#material_type").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#material_type_list').html(data.materialTypeList);
                } else {
                    toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#material_type").removeClass('file-loader');
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

jQuery(document).on('click', '#MaterialTypeModal #material_type_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#material_type_suggesion').val(suggest);
    var hidden = jQuery('#material_type_suggesion').val();
    var suggestion_list = jQuery('#material_type_list').html;
    jQuery('#MaterialTypeModal').find('#material_type').val(hidden)
    var material_type = hidden;

    if (suggestion_list != '') {
        checkMaterialTypeName(material_type);
    }
    jQuery('#material_type_list').html('');
});


let lastVerifiedMaterialType = '';

jQuery(document).on('blur', '#MaterialTypeModal #material_type', function () {
    let material_type = jQuery(this).val().trim();

    if (material_type === '') return;

    if (material_type !== lastVerifiedMaterialType) {
        lastVerifiedMaterialType = material_type;
        checkMaterialTypeName(material_type);
    }
});

jQuery(document).on('input', '#MaterialTypeModal #material_type', function () {
    lastVerifiedMaterialType = '';
});

function checkMaterialTypeName(material_type) {
    var id = jQuery('#MaterialTypeModal').find('#commonMaterialTypeForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-material_type?material_type=" + encodeURIComponent(material_type) + "&id=" + id : "verify-material_type?material_type=" + encodeURIComponent(material_type);
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

function verifyMaterialType() {
    var MaterialTypeName = jQuery('#MaterialTypeModal #material_type').val();
    var suggestion_list = jQuery('#material_type_list').html;

    if (suggestion_list != '') {
        checkMaterialTypeName(MaterialTypeName);
    }
}

jQuery('#MaterialTypeModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#MaterialTypeModal');
    thisForm.find('#id').val('');
    document.getElementById("commonMaterialTypeForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });
    jQuery('#MaterialTypeModal').find('#add_new').hide();
});

$('#MaterialTypeModal').on('shown.bs.modal', function () {
    const input = document.getElementById('material_type');
    input?.focus();

    var formIdblank = jQuery('#commonMaterialTypeForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#MaterialTypeModal').find('#add_new').show();
    } else {
        jQuery('#MaterialTypeModal').find('#add_new').hide();
    }
});

jQuery('#MaterialTypeModal').on('click', '#add_new', function () {
    jQuery('#MaterialTypeModal').find('#id').val('');
    document.getElementById("commonMaterialTypeForm").reset();
    const form = document.getElementById("commonMaterialTypeForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#material_type').focus();
    jQuery('#MaterialTypeModal').find('#add_new').hide();
});

function getMaterialType($this = null) {
    var formUrl = "get-material_types";
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
                    var stgDrpHtml = `<option value="">Select Material Type</option>`;
                    for (let indx in data.material_types) {
                        stgDrpHtml += `<option value="${data.material_types[indx].material_type_id}">${data.material_types[indx].material_type}</option>`;
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

function addedMaterialType($event) {
    if ($event == true) {
        getMaterialType(".mst_material_type");
    }
}
