var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

// Edit film_type row click
jQuery('#dyntable tbody').on('click', '.edit_film_type', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillFilmType(data["id"]);
    }
});

// Function to fetch and fill film_type data
function fetchAndFillFilmType(id) {
    if (!id) return;
    jQuery('#FilmTypeModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-film_type",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.film_type_data != null) {
                jQuery('#FilmTypeModal').find('#film_type').val(data.film_type_data.film_type);
                jQuery('#FilmTypeModal').find('#id').val(data.film_type_data.film_type_id);
                jQuery('#FilmTypeModal').find('#add_new').show();

                const form = document.getElementById("commonFilmTypeForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#FilmTypeModal').find('#film_type').focus();
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

// Reset button click for film_type modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#FilmTypeModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonFilmTypeForm").reset();
        const form = document.getElementById("commonFilmTypeForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        jQuery('#FilmTypeModal').find('#film_type').focus();
    } else {
        fetchAndFillFilmType(formId);
    }
});

$('#commonFilmTypeForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#FilmTypeModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#FilmTypeModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#FilmTypeModal').find('#commonFilmTypeForm').find('#id').val();
    var film_type = jQuery('#FilmTypeModal').find('#film_type').val();
    var formUrl = formId != undefined && formId != "" ? "update-film_type" : "store-film_type";
    var FilmTypeUrl = formId != undefined && formId != "" ? "verify-film_type?film_type=" + encodeURIComponent(film_type) + "&id=" + formId : "verify-film_type?film_type=" + encodeURIComponent(film_type);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (film_type != '' && film_type != undefined) {
        $.ajax({
            url: FilmTypeUrl,
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#FilmTypeModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#FilmTypeModal').find('#submitbtn').prop('disabled', false);
                                    addedFilmType(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonFilmTypeForm").reset();
                                        const form = document.getElementById("commonFilmTypeForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#FilmTypeModal').find('#film_type').focus();
                                        if (partAfterManage != 'film_type') {
                                            jQuery('#FilmTypeModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#FilmTypeModal').find('#submitbtn').prop('disabled', false);
                                    addedFilmType(true);

                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#FilmTypeModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#FilmTypeModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#FilmTypeModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#FilmTypeModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#FilmTypeModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestFilmType(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "film_type-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#film_type").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#film_type_list').html(data.filmTypeList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#film_type").removeClass('file-loader');
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

jQuery(document).on('click', '#film_type_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#film_type_suggesion').val(suggest);
    var hidden = jQuery('#film_type_suggesion').val();
    var suggestion_list = jQuery('#film_type_list').html;
    jQuery('#FilmTypeModal').find('#film_type').val(hidden)
    var film_type = hidden;

    if (suggestion_list != '') {
        checkFilmTypeName(film_type);
    }
    jQuery('#film_type_list').html('');
});


let lastVerifiedFilmType = '';

jQuery(document).on('blur', '#film_type', function () {
    let film_type = jQuery(this).val().trim();

    if (film_type === '') return;

    if (film_type !== lastVerifiedFilmType) {
        lastVerifiedFilmType = film_type;
        checkFilmTypeName(film_type);
    }
});

jQuery(document).on('input', '#film_type', function () {
    lastVerifiedFilmType = '';
});

function checkFilmTypeName(film_type) {
    var id = jQuery('#FilmTypeModal').find('#commonFilmTypeForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-film_type?film_type=" + encodeURIComponent(film_type) + "&id=" + id : "verify-film_type?film_type=" + encodeURIComponent(film_type);
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

function verifyFilmType() {
    var FilmTypeName = jQuery('#film_type').val();
    var suggestion_list = jQuery('#film_type_list').html;

    if (suggestion_list != '') {
        checkFilmTypeName(FilmTypeName);
    }
}

jQuery('#FilmTypeModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#FilmTypeModal');
    thisForm.find('#id').val('');
    document.getElementById("commonFilmTypeForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });
    jQuery('#FilmTypeModal').find('#add_new').hide();
});

$('#FilmTypeModal').on('shown.bs.modal', function () {
    const input = document.getElementById('film_type');
    input?.focus();

    var formIdblank = jQuery('#commonFilmTypeForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#FilmTypeModal').find('#add_new').show();
    } else {
        jQuery('#FilmTypeModal').find('#add_new').hide();
    }
});

jQuery('#FilmTypeModal').on('click', '#add_new', function () {
    jQuery('#FilmTypeModal').find('#id').val('');
    document.getElementById("commonFilmTypeForm").reset();
    const form = document.getElementById("commonFilmTypeForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#film_type').focus();
    jQuery('#FilmTypeModal').find('#add_new').hide();
});

function getFilmType($this = null) {
    var formUrl = "get-film_types";
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
                    var stgDrpHtml = `<option value="">Select Film Type</option>`;
                    for (let indx in data.film_types) {
                        stgDrpHtml += `<option value="${data.film_types[indx].film_type_id}">${data.film_types[indx].film_type}</option>`;
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

function addedFilmType($event) {
    if ($event == true) {
        getFilmType(".mst_film_type");
    }
}
