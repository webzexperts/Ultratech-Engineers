var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

// Edit unit row click
jQuery('#dyntable tbody').on('click', '.edit_unit', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillUnit(data["id"]);
    }
});

// Function to fetch and fill unit data
function fetchAndFillUnit(id) {
    if (!id) return;
    jQuery('#UnitModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-unit",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.unit_data != null) {
                jQuery('#UnitModal').find('#unit').val(data.unit_data.unit);
                jQuery('#UnitModal').find('#decimal_place').val(data.unit_data.decimal_place).trigger("change.select2");
                jQuery('#UnitModal').find('#id').val(data.unit_data.id);
                jQuery('#UnitModal').find('#add_new').show();

                const form = document.getElementById("commonUnitForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#UnitModal').find('#unit').focus();
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

// Reset button click for unit modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#UnitModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonUnitForm").reset(); 
        const form = document.getElementById("commonUnitForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        jQuery('#UnitModal').find('#unit').focus();
        jQuery('#decimal_place').val('').trigger('change');
    } else {
        fetchAndFillUnit(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_unit', function () {
//     jQuery('#UnitModal').modal('show');
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-unit",
//         type: 'GET',
//         data: "id=" + data["id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 if (data.unit_data != null) {
//                     jQuery('#UnitModal').find('#unit').val(data.unit_data.unit);
//                     jQuery('#UnitModal').find('#decimal_place').val(data.unit_data.decimal_place).trigger("change.select2");
//                     jQuery('#UnitModal').find('#id').val(data.unit_data.id);
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

$('#commonUnitForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#UnitModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#UnitModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#UnitModal').find('#commonUnitForm').find('#id').val();
    var unit = $("#unit").val();
    var formUrl = formId != undefined && formId != "" ? "update-unit" : "store-unit";
    var UnitUrl = formId != undefined && formId != "" ? "verify-unit?unit=" + encodeURIComponent(unit) + "&id=" + formId : "verify-unit?unit=" + encodeURIComponent(unit);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (unit != '' && unit != undefined) {
        $.ajax({
            url: UnitUrl,
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
                    jQuery('#UnitModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#UnitModal').find('#submitbtn').prop('disabled', false);
                                    addedUnit(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonUnitForm").reset();
                                        const form = document.getElementById("commonUnitForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#unit').focus();
                                        $("#decimal_place").val('').trigger('change');
                                        if (partAfterManage != 'unit') {
                                            jQuery('#UnitModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#UnitModal').find('#submitbtn').prop('disabled', false);
                                    addedUnit(true);

                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#UnitModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                 toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#UnitModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#UnitModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#UnitModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#UnitModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestUnit(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "unit-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#unit").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#unit_list').html(data.unitList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#unit").removeClass('file-loader');
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

jQuery(document).on('click', '#unit_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#unit_suggesion').val(suggest);
    var hidden = jQuery('#unit_suggesion').val();
    var suggestion_list = jQuery('#unit_list').html;
    jQuery('#UnitModal').find('#unit').val(hidden)
    var unit = hidden;

    if (suggestion_list != '') {
        checkUnitName(unit);
    }
    jQuery('#unit_list').html('');
});


let lastVerifiedUnit = '';

jQuery(document).on('blur', '#unit', function () {
    let unit = jQuery(this).val().trim();

    if (unit === '') return;

    if (unit !== lastVerifiedUnit) {
        lastVerifiedUnit = unit;
        checkUnitName(unit);
    }
});

jQuery(document).on('input', '#unit', function () {
    lastVerifiedUnit = '';
});

function checkUnitName(unit) {
    var id = jQuery('#UnitModal').find('#commonUnitForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-unit?unit=" + encodeURIComponent(unit) + "&id=" + id : "verify-unit?unit=" + encodeURIComponent(unit);
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

function verifyUnit() {
    var UnitName = jQuery('#unit').val();
    var suggestion_list = jQuery('#unit_list').html;

    if (suggestion_list != '') {
        checkUnitName(UnitName);
    }
}

jQuery('#UnitModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#UnitModal');
    thisForm.find('#id').val('');
    thisForm.find('#decimal_place').val('').trigger('change');
    document.getElementById("commonUnitForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });
    jQuery('#UnitModal').find('#add_new').hide();
});

$('#UnitModal').on('shown.bs.modal', function () {
    const input = document.getElementById('unit');
    input?.focus();

    var formIdblank = jQuery('#commonUnitForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#UnitModal').find('#add_new').show();
    } else {
        jQuery('#UnitModal').find('#add_new').hide();
    }
});

jQuery('#UnitModal').on('click', '#add_new', function () {
    jQuery('#UnitModal').find('#id').val('');
    document.getElementById("commonUnitForm").reset();
    const form = document.getElementById("commonUnitForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#unit').focus();
    jQuery('#UnitModal').find('#add_new').hide();
});

function getUnit($this = null) {
    var formUrl = "get-units";
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
                    var stgDrpHtml = `<option value="">Select Unit</option>`;
                    for (let indx in data.units) {
                        stgDrpHtml += `<option value="${data.units[indx].id}">${data.units[indx].unit}</option>`;
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

function addedUnit($event) {
    if ($event == true) {
        getUnit(".mst_unit");
    }
}