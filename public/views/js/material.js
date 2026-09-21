var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];
let lastVerifiedMaterial = '';

// Edit material row click
jQuery('#dyntable tbody').on('click', '.edit_material', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillMaterial(data["id"]);
    }
});

// Function to fetch and fill material data
function fetchAndFillMaterial(id) {
    if (!id) return;
    jQuery('#MaterialModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-material",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.material_data != null) {
                jQuery('#MaterialModal').find('#material').val(data.material_data.material);
                jQuery('#MaterialModal').find('#id').val(data.material_data.id);
                jQuery('#MaterialModal').find('#add_new').show();

                const form = document.getElementById("commonMaterialForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#MaterialModal').find('#material').focus();
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

// Reset button click for material modal
jQuery('#resetbtn').on('click', function () {
    var formId = jQuery('#MaterialModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonMaterialForm").reset(); 
        const form = document.getElementById("commonMaterialForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        lastVerifiedMaterial = '';
        jQuery('#material').focus();
    } else {
        fetchAndFillMaterial(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_material', function () {
//     jQuery('#MaterialModal').modal('show');
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-material",
//         type: 'GET',
//         data: "id=" + data["id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 if (data.material_data != null) {
//                     jQuery('#MaterialModal').find('#material').val(data.material_data.material);
//                     jQuery('#MaterialModal').find('#id').val(data.material_data.id);
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

$('#commonMaterialForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#MaterialModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#MaterialModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#MaterialModal').find('#commonMaterialForm').find('#id').val();
    var material = $("#material").val();
    var formUrl = formId != undefined && formId != "" ? "update-material" : "store-material";
    var MaterialUrl = formId != undefined && formId != "" ? "verify-material?material=" + encodeURIComponent(material) + "&id=" + formId : "verify-material?material=" + encodeURIComponent(material);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (material != '' && material != undefined) {
        $.ajax({
            url: MaterialUrl,
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
                    jQuery('#MaterialModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#MaterialModal').find('#submitbtn').prop('disabled', false);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonMaterialForm").reset();
                                        const form = document.getElementById("commonMaterialForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#material').focus();
                                        if (partAfterManage != 'material') {
                                            jQuery('#MaterialModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#MaterialModal').find('#submitbtn').prop('disabled', false);
                                    addedMaterial(true);
                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#MaterialModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                 toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#MaterialModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#MaterialModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#MaterialModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#MaterialModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestMaterial(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "material-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#material").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#material_list').html(data.materialList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#material").removeClass('file-loader');
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

jQuery(document).on('click', '#material_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#material_suggesion').val(suggest);
    var hidden = jQuery('#material_suggesion').val();
    var suggestion_list = jQuery('#material_list').html;
    jQuery('#MaterialModal').find('#material').val(hidden)
    var material = hidden;

    if (suggestion_list != '') {
        checkMaterialName(material);
    }
    jQuery('#material_list').html('');
});

jQuery(document).on('blur', '#material', function () {
    let material = jQuery(this).val().trim();

    if (material === '') return;

    if (material !== lastVerifiedMaterial) {
        lastVerifiedMaterial = material;
        checkMaterialName(material);
    }
});

jQuery(document).on('input', '#material', function () {
    lastVerifiedMaterial = '';
});

function checkMaterialName(material) {
    var id = jQuery('#MaterialModal').find('#commonMaterialForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-material?material=" + encodeURIComponent(material) + "&id=" + id : "verify-material?material=" + encodeURIComponent(material);
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

function verifyMaterial() {
    var MaterialName = jQuery('#material').val();
    var suggestion_list = jQuery('#material_list').html;

    if (suggestion_list != '') {
        checkMaterialName(MaterialName);
    }
}

jQuery('#MaterialModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#MaterialModal');
    thisForm.find('#id').val('');
    lastVerifiedMaterial = '';
    document.getElementById("commonMaterialForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });
    thisForm.find('#add_new').hide();
    // window.location.reload();
});

$('#MaterialModal').on('shown.bs.modal', function () {
    const input = document.getElementById('material');
    input?.focus();

    var formIdblank = jQuery('#MaterialModal').find('#id').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#MaterialModal').find('#add_new').show();
    } else {
        jQuery('#MaterialModal').find('#add_new').hide();
    }
});

jQuery('#MaterialModal').on('click', '#add_new', function () {
    jQuery('#MaterialModal').find('#id').val('');
    document.getElementById("commonMaterialForm").reset();
    const form = document.getElementById("commonMaterialForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    lastVerifiedMaterial = '';

    jQuery('#material').focus();
    jQuery('#MaterialModal').find('#add_new').hide();
});

function addedMaterial($event) {
    if ($event == true) {
        getMaterial(".suggest_material_name");
    }
}

function getMaterial($this = null) {
    var formUrl = "get-materials";
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
                    var stgDrpHtml = `<option value="">Select Material</option>`;
                    for (let indx in data.materials) {
                        stgDrpHtml += `<option value="${data.materials[indx].id}">${data.materials[indx].material}</option>`;
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