var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

// Edit shift row click
jQuery('#dyntable tbody').on('click', '.edit_shift', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["shift_id"]) {
        fetchAndFillShift(data["shift_id"]);
    }
});

// Function to fetch and fill shift data
function fetchAndFillShift(id) {
    if (!id) return;
    jQuery('#ShiftModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-shift",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.shift_data != null) {
                jQuery('#ShiftModal').find('#shift_name').val(data.shift_data.shift_name);
                jQuery('#ShiftModal').find('#id').val(data.shift_data.shift_id);
                jQuery('#ShiftModal').find('#add_new').show();

                const form = document.getElementById("commonShiftForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#ShiftModal').find('#shift_name').focus();
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

// Reset button click for shift modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#ShiftModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonShiftForm").reset();
        const form = document.getElementById("commonShiftForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        jQuery('#ShiftModal').find('#shift_name').focus();
    } else {
        fetchAndFillShift(formId);
    }
});

$('#commonShiftForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#ShiftModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ShiftModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#ShiftModal').find('#commonShiftForm').find('#id').val();
    var shift_name = $("#shift_name").val();
    var formUrl = formId != undefined && formId != "" ? "update-shift" : "store-shift";
    var ShiftUrl = formId != undefined && formId != "" ? "verify-shift?shift_name=" + encodeURIComponent(shift_name) + "&id=" + formId : "verify-shift?shift_name=" + encodeURIComponent(shift_name);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (shift_name != '' && shift_name != undefined) {
        $.ajax({
            url: ShiftUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#ShiftModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#ShiftModal').find('#submitbtn').prop('disabled', false);
                                    addedShift(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonShiftForm").reset();
                                        const form = document.getElementById("commonShiftForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#shift_name').focus();
                                        if (partAfterManage != 'shift') {
                                            jQuery('#ShiftModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#ShiftModal').find('#submitbtn').prop('disabled', false);
                                    addedShift(true);

                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#ShiftModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#ShiftModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#ShiftModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#ShiftModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#ShiftModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

// function suggestShift(e, $this) {
//     var keyevent = e
//     if (keyevent.key != "Tab") {
//         var search = jQuery($this).val();
//         jQuery.ajax({
//             url: "shift-list?term=" + encodeURI(search),
//             type: 'GET',
//             dataType: 'json',
//             processData: false,
//             success: function (data) {
//                 jQuery("#shift_name").removeClass('file-loader');
//                 if (data.response_code == 1) {
//                     jQuery('#shift_list').html(data.shiftList);
//                 } else {
//                      toastr.error(data.response_message);
//                 }
//             },
//             error: function (jqXHR, textStatus, errorThrown) {
//                 jQuery("#shift_name").removeClass('file-loader');
//                 var errMessage = JSON.parse(jqXHR.responseText);
//                 if (errMessage.errors) {
//                      toastr.error('Something went wrong!');
//                 } else if (jqXHR.status == 401) {
//                       toastr.error(jqXHR.statusText);
//                 } else {
//                       toastr.error('Something went wrong!');
//                     console.log(JSON.parse(jqXHR.responseText));
//                 }
//             }
//         });
//     }
// }

jQuery(document).on('click', '#shift_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#shift_suggesion').val(suggest);
    var hidden = jQuery('#shift_suggesion').val();
    var suggestion_list = jQuery('#shift_list').html;
    jQuery('#ShiftModal').find('#shift_name').val(hidden)
    var shift_name = hidden;

    if (suggestion_list != '') {
        checkShiftName(shift_name);
    }
    jQuery('#shift_list').html('');
});


let lastVerifiedShift = '';

jQuery(document).on('blur', '#shift_name', function () {
    let shift_name = jQuery(this).val().trim();

    if (shift_name === '') return;

    if (shift_name !== lastVerifiedShift) {
        lastVerifiedShift = shift_name;
        checkShiftName(shift_name);
    }
});

jQuery(document).on('input', '#shift_name', function () {
    lastVerifiedShift = '';
});

function checkShiftName(shift_name) {
    var id = jQuery('#ShiftModal').find('#commonShiftForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-shift?shift_name=" + encodeURIComponent(shift_name) + "&id=" + id : "verify-shift?shift_name=" + encodeURIComponent(shift_name);
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

function verifyShift() {
    var ShiftName = jQuery('#shift_name').val();
    var suggestion_list = jQuery('#shift_list').html;

    if (suggestion_list != '') {
        checkShiftName(ShiftName);
    }
}

jQuery('#ShiftModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#ShiftModal');
    thisForm.find('#id').val('');
    document.getElementById("commonShiftForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });
    jQuery('#ShiftModal').find('#add_new').hide();
});

$('#ShiftModal').on('shown.bs.modal', function () {
    const input = document.getElementById('shift_name');
    input?.focus();

    var formIdblank = jQuery('#commonShiftForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#ShiftModal').find('#add_new').show();
    } else {
        jQuery('#ShiftModal').find('#add_new').hide();
    }
});

jQuery('#ShiftModal').on('click', '#add_new', function () {
    jQuery('#ShiftModal').find('#id').val('');
    document.getElementById("commonShiftForm").reset();
    const form = document.getElementById("commonShiftForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#shift_name').focus();
    jQuery('#ShiftModal').find('#add_new').hide();
});

function getShift($this = null) {
    var formUrl = "get-shifts";
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
                    var stgDrpHtml = `<option value="">Select Shift</option>`;
                    for (let indx in data.shifts) {
                        stgDrpHtml += `<option value="${data.shifts[indx].shift_id}">${data.shifts[indx].shift_name}</option>`;
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

function addedShift($event) {
    if ($event == true) {
        getShift(".mst_shift");
    }
}
