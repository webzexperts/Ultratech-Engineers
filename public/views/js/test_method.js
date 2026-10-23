var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

// Edit test_method row click
jQuery('#dyntable tbody').on('click', '.edit_test_method', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillTestMethod(data["id"]);
    }
});

// Function to fetch and fill test_method data
function fetchAndFillTestMethod(id) {
    if (!id) return;
    jQuery('#TestMethodModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-test_method",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.test_method_data != null) {
                jQuery('#TestMethodModal').find('#test_method').val(data.test_method_data.test_method);
                jQuery('#TestMethodModal').find('#id').val(data.test_method_data.test_method_id);
                jQuery('#TestMethodModal').find('#add_new').show();

                const form = document.getElementById("commonTestMethodForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#TestMethodModal').find('#test_method').focus();
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

// Reset button click for test_method modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#TestMethodModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonTestMethodForm").reset();
        const form = document.getElementById("commonTestMethodForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        jQuery('#TestMethodModal').find('#test_method').focus();
    } else {
        fetchAndFillTestMethod(formId);
    }
});

$('#commonTestMethodForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#TestMethodModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#TestMethodModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#TestMethodModal').find('#commonTestMethodForm').find('#id').val();
    var test_method = jQuery('#TestMethodModal').find('#test_method').val();
    var formUrl = formId != undefined && formId != "" ? "update-test_method" : "store-test_method";
    var verifyUrl = formId != undefined && formId != "" ? "verify-test_method?test_method=" + encodeURIComponent(test_method) + "&id=" + formId : "verify-test_method?test_method=" + encodeURIComponent(test_method);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (test_method != '' && test_method != undefined) {
        $.ajax({
            url: verifyUrl,
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#TestMethodModal').find('#submitbtn').prop('disabled', false);
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
                                        if (typeof table !== 'undefined') {
                                            table.draw(false);
                                        } else {
                                            window.location.reload();
                                        }
                                    }
                                    toastSuccess(data.response_message, redirectFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#TestMethodModal').find('#submitbtn').prop('disabled', false);
                                    jQuery('#TestMethodModal').modal('hide');
                                    addedTestMethod(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonTestMethodForm").reset();
                                        const form = document.getElementById("commonTestMethodForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#TestMethodModal').find('#test_method').focus();
                                        if (partAfterManage != 'test_method') {
                                            jQuery('#TestMethodModal').modal('hide');
                                        } else if (typeof table !== 'undefined') {
                                            table.draw(false);
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#TestMethodModal').find('#submitbtn').prop('disabled', false);
                                    addedTestMethod(true);
                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#TestMethodModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#TestMethodModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#TestMethodModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                toastr.error(errorMsg);
                            } else {
                                toastr.error('Something went wrong. Please try again.');
                            }
                        }
                    });
                }
            }
        });
    }
});

let lastVerifiedTestMethod = '';

jQuery(document).on('blur', '#TestMethodModal #test_method', function () {
    let test_method = jQuery(this).val().trim();

    if (test_method === '') return;

    if (test_method !== lastVerifiedTestMethod) {
        lastVerifiedTestMethod = test_method;
        checkTestMethodName(test_method);
    }
});

jQuery(document).on('input', '#TestMethodModal #test_method', function () {
    lastVerifiedTestMethod = '';
});

function checkTestMethodName(test_method) {
    var id = jQuery('#TestMethodModal').find('#commonTestMethodForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-test_method?test_method=" + encodeURIComponent(test_method) + "&id=" + id : "verify-test_method?test_method=" + encodeURIComponent(test_method);
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

jQuery('#TestMethodModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#TestMethodModal');
    thisForm.find('#id').val('');
    document.getElementById("commonTestMethodForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });
    jQuery('#TestMethodModal').find('#add_new').hide();
});

$('#TestMethodModal').on('shown.bs.modal', function () {
    const input = document.getElementById('test_method');
    input?.focus();

    var formIdblank = jQuery('#commonTestMethodForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#TestMethodModal').find('#add_new').show();
    } else {
        jQuery('#TestMethodModal').find('#add_new').hide();
    }
});

jQuery('#TestMethodModal').on('click', '#add_new', function () {
    jQuery('#TestMethodModal').find('#id').val('');
    document.getElementById("commonTestMethodForm").reset();
    const form = document.getElementById("commonTestMethodForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#test_method').focus();
    jQuery('#TestMethodModal').find('#add_new').hide();
});

function getTestMethod($this = null) {
    var formUrl = "get-test_methods";
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
                    var stgDrpHtml = `<option value="">Select Test Method</option>`;
                    for (let indx in data.test_methods) {
                        stgDrpHtml += `<option value="${data.test_methods[indx].test_method_id}">${data.test_methods[indx].test_method}</option>`;
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

function addedTestMethod($event) {
    if ($event == true) {
        getTestMethod(".mst_test_method");
    }
}
