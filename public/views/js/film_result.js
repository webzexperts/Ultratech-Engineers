var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

// Edit film_result row click
jQuery('#dyntable tbody').on('click', '.edit_film_result', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillFilmResult(data["id"]);
    }
});

// Function to fetch and fill film_result data
function fetchAndFillFilmResult(id) {
    if (!id) return;
    jQuery('#FilmResultModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-film_result",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.film_result_data != null) {
                var d = data.film_result_data;
                jQuery('#FilmResultModal').find('#film_result_name').val(d.film_result_name);
                jQuery('#FilmResultModal').find('input[name="film_result_type_fix"]').prop('checked', false);
                jQuery('#FilmResultModal').find('input[name="film_result_type_fix"][value="' + d.film_result_type_fix + '"]').prop('checked', true);
                jQuery('#FilmResultModal').find('#id').val(d.film_result_id);
                jQuery('#FilmResultModal').find('#add_new').show();

                if (d.in_use) {
                    jQuery('#FilmResultModal').find('#film_result_name').prop('readonly', true);
                    setRadioReadonly('input[name="film_result_type_fix"]', true);
                } else {
                    jQuery('#FilmResultModal').find('#film_result_name').prop('readonly', false);
                    setRadioReadonly('input[name="film_result_type_fix"]', false);
                }

                const form = document.getElementById("commonFilmResultForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#FilmResultModal').find('#film_result_name').focus();
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

// Reset button click for film_result modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#FilmResultModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonFilmResultForm").reset();
        const form = document.getElementById("commonFilmResultForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        jQuery('#FilmResultModal').find('input[name="film_result_type_fix"]').prop('checked', false);
        jQuery('#FilmResultModal').find('#ftf_accepted').prop('checked', true);
        jQuery('#FilmResultModal').find('#film_result_name').focus();
    } else {
        fetchAndFillFilmResult(formId);
    }
});

$('#commonFilmResultForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#FilmResultModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#FilmResultModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#FilmResultModal').find('#commonFilmResultForm').find('#id').val();
    var film_result_name = $("#film_result_name").val();
    var formUrl = formId != undefined && formId != "" ? "update-film_result" : "store-film_result";
    var FilmResultUrl = formId != undefined && formId != "" ? "verify-film_result?film_result_name=" + encodeURIComponent(film_result_name) + "&id=" + formId : "verify-film_result?film_result_name=" + encodeURIComponent(film_result_name);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (film_result_name != '' && film_result_name != undefined) {
        $.ajax({
            url: FilmResultUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#FilmResultModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#FilmResultModal').find('#submitbtn').prop('disabled', false);
                                    addedFilmResult(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonFilmResultForm").reset();
                                        const form = document.getElementById("commonFilmResultForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }
                                        jQuery('#FilmResultModal').find('input[name="film_result_type_fix"]').prop('checked', false);
                                        jQuery('#FilmResultModal').find('#ftf_accepted').prop('checked', true);
                                        jQuery('#film_result_name').focus();
                                        if (partAfterManage != 'film_result') {
                                            jQuery('#FilmResultModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#FilmResultModal').find('#submitbtn').prop('disabled', false);
                                    addedFilmResult(true);

                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#FilmResultModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                 toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#FilmResultModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#FilmResultModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#FilmResultModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#FilmResultModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestFilmResult(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "film_result_name-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#film_result_name").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#film_result_name_list').html(data.filmResultList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#film_result_name").removeClass('file-loader');
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

jQuery(document).on('click', '#film_result_name_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#FilmResultModal').find('#film_result_name').val(suggest);
    if (jQuery('#film_result_name_list').html() != '') {
        checkFilmResultName(suggest);
    }
    jQuery('#film_result_name_list').html('');
});

let lastVerifiedFilmResult = '';

jQuery(document).on('blur', '#film_result_name', function () {
    let film_result_name = jQuery(this).val().trim();

    if (film_result_name === '') return;

    if (film_result_name !== lastVerifiedFilmResult) {
        lastVerifiedFilmResult = film_result_name;
        checkFilmResultName(film_result_name);
    }
});

jQuery(document).on('input', '#film_result_name', function () {
    lastVerifiedFilmResult = '';
});

function checkFilmResultName(film_result_name) {
    var id = jQuery('#FilmResultModal').find('#commonFilmResultForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-film_result?film_result_name=" + encodeURIComponent(film_result_name) + "&id=" + id : "verify-film_result?film_result_name=" + encodeURIComponent(film_result_name);
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

function verifyFilmResult() {
    var FilmResultName = jQuery('#film_result_name').val();
    var suggestion_list = jQuery('#film_result_name_list').html;

    if (suggestion_list != '') {
        checkFilmResultName(FilmResultName);
    }
}

jQuery('#FilmResultModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#FilmResultModal');
    thisForm.find('#id').val('');
    document.getElementById("commonFilmResultForm").reset();
    thisForm.find('input[name="film_result_type_fix"]').prop('checked', false);
    thisForm.find('#ftf_accepted').prop('checked', true);
    thisForm.find('#film_result_name').prop('readonly', false);
    setRadioReadonly('input[name="film_result_type_fix"]', false);
    jQuery('#FilmResultModal').find('#add_new').hide();
});

$('#FilmResultModal').on('shown.bs.modal', function () {
    const input = document.getElementById('film_result_name');
    input?.focus();

    var formIdblank = jQuery('#commonFilmResultForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#FilmResultModal').find('#add_new').show();
    } else {
        jQuery('#FilmResultModal').find('#add_new').hide();
        jQuery('#ftf_accepted').prop('checked', true);
    }
});

jQuery('#FilmResultModal').on('click', '#add_new', function () {
    jQuery('#FilmResultModal').find('#id').val('');
    document.getElementById("commonFilmResultForm").reset();
    const form = document.getElementById("commonFilmResultForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    jQuery('#FilmResultModal').find('input[name="film_result_type_fix"]').prop('checked', false);
    jQuery('#FilmResultModal').find('#ftf_accepted').prop('checked', true);
    jQuery('#FilmResultModal').find('#film_result_name').prop('readonly', false);
    setRadioReadonly('input[name="film_result_type_fix"]', false);
    jQuery('#film_result_name').focus();
    jQuery('#FilmResultModal').find('#add_new').hide();
});

function getFilmResult($this = null) {
    var formUrl = "get-film_results";
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
                    var stgDrpHtml = `<option value="">Select Film Result</option>`;
                    for (let indx in data.film_results) {
                        stgDrpHtml += `<option value="${data.film_results[indx].film_result_id}">${data.film_results[indx].film_result_name}</option>`;
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

function addedFilmResult($event) {
    if ($event == true) {
        getFilmResult(".mst_film_result");
    }
}
