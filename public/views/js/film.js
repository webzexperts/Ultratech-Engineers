var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

// Auto-calculation: SqIn, Length(cm), Width(cm), SqCm, Film Size (inch), Film Size (cm)
function calcFilm() {
    var rawLi = jQuery('#length_inch').val();
    var rawWi = jQuery('#width_inch').val();
    var li = parseFloat(rawLi);
    var wi = parseFloat(rawWi);
    var liValid = !isNaN(li) && li > 0 && rawLi !== '';
    var wiValid = !isNaN(wi) && wi > 0 && rawWi !== '';

    li = isNaN(li) ? 0 : li;
    wi = isNaN(wi) ? 0 : wi;

    var sqIn = li * wi;
    var lengthCm = li * 2.54;
    var widthCm = wi * 2.54;

    // round cm to 2 decimals first, then derive SqCm from rounded cm values
    lengthCm = parseFloat(lengthCm.toFixed(2));
    widthCm = parseFloat(widthCm.toFixed(2));
    var sqCm = lengthCm * widthCm;

    jQuery('#sq_in').val(sqIn > 0 ? sqIn.toFixed(2) : '');
    jQuery('#length_cm').val(lengthCm > 0 ? lengthCm.toFixed(2) : '');
    jQuery('#width_cm').val(widthCm > 0 ? widthCm.toFixed(2) : '');
    jQuery('#sq_cm').val(sqCm > 0 ? sqCm.toFixed(2) : '');

    if (liValid && wiValid) {
        var filmSizeInch = formatVal(li) + ' X ' + formatVal(wi);
        var filmSizeCm = formatVal(lengthCm) + ' X ' + formatVal(widthCm);
        jQuery('#film_size_inch').val(filmSizeInch);
        jQuery('#film_size_cm').val(filmSizeCm);
    } else {
        jQuery('#film_size_inch').val('');
        jQuery('#film_size_cm').val('');
    }
}
function formatVal(val) {
    let str = Number(val).toFixed(2);
    return str.replace(/\.00$/, ''); // છેલ્લે .00 હોય તો જ કાઢશે
}


jQuery(document).on('input', '#length_inch, #width_inch', function () {
    calcFilm();
});

function clearFilmCalc() {
    jQuery('#film_size_inch').val('');
    jQuery('#film_size_cm').val('');
    jQuery('#sq_in').val('');
    jQuery('#length_cm').val('');
    jQuery('#width_cm').val('');
    jQuery('#sq_cm').val('');
}

// Edit film row click
jQuery('#dyntable tbody').on('click', '.edit-film', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillFilm(data["id"]);
    }
});

// Function to fetch and fill film data
function fetchAndFillFilm(id) {
    if (!id) return;
    jQuery('#FilmModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-film",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.film_data != null) {
                var d = data.film_data;
                jQuery('#FilmModal').find('#film_size_inch').val(d.film_size_inch);
                jQuery('#FilmModal').find('#length_inch').val(d.length_inch);
                jQuery('#FilmModal').find('#width_inch').val(d.width_inch);
                jQuery('#FilmModal').find('#sq_in').val(d.sq_in);
                jQuery('#FilmModal').find('#film_size_cm').val(d.film_size_cm);
                jQuery('#FilmModal').find('#length_cm').val(d.length_cm);
                jQuery('#FilmModal').find('#width_cm').val(d.width_cm);
                jQuery('#FilmModal').find('#sq_cm').val(d.sq_cm);
                jQuery('#FilmModal').find('#status').val(d.status).trigger('change');
                jQuery('#FilmModal').find('#id').val(d.film_id);
                jQuery('#FilmModal').find('#add_new').show();

                const form = document.getElementById("commonFilmForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#FilmModal').find('#length_inch').focus();
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

// Reset button click for film modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#FilmModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonFilmForm").reset();
        const form = document.getElementById("commonFilmForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        clearFilmCalc();
        jQuery('#status').val('Active').trigger('change');
        jQuery('#FilmModal').find('#length_inch').focus();
    } else {
        fetchAndFillFilm(formId);
    }
});

$('#commonFilmForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#FilmModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#FilmModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#FilmModal').find('#commonFilmForm').find('#id').val();
    var film_size_inch = $("#film_size_inch").val();
    var film_size_cm = $("#film_size_cm").val();
    var formUrl = formId != undefined && formId != "" ? "update-film" : "store-film";
    var FilmUrl = formId != undefined && formId != "" ? "verify-film?film_size_inch=" + encodeURIComponent(film_size_inch) + "&film_size_cm=" + encodeURIComponent(film_size_cm) + "&id=" + formId : "verify-film?film_size_inch=" + encodeURIComponent(film_size_inch) + "&film_size_cm=" + encodeURIComponent(film_size_cm);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (film_size_inch != '' && film_size_inch != undefined) {
        $.ajax({
            url: FilmUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#FilmModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#FilmModal').find('#submitbtn').prop('disabled', false);
                                    addedFilm(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonFilmForm").reset();
                                        const form = document.getElementById("commonFilmForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        clearFilmCalc();
                                        $("#status").val('Active').trigger('change');
                                        jQuery('#length_inch').focus();
                                        if (partAfterManage != 'film') {
                                            jQuery('#FilmModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#FilmModal').find('#submitbtn').prop('disabled', false);
                                    addedFilm(true);

                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#FilmModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#FilmModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#FilmModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#FilmModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#FilmModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestFilm(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "film_list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#film_size_inch").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#film_list').html(data.filmList);
                } else {
                    toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#film_size_inch").removeClass('file-loader');
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

jQuery(document).on('click', '#film_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#film_suggesion').val(suggest);
    var hidden = jQuery('#film_suggesion').val();
    var suggestion_list = jQuery('#film_list').html;
    jQuery('#FilmModal').find('#film_size_inch').val(hidden)
    var film_size_inch = hidden;

    if (suggestion_list != '') {
        checkFilmName(film_size_inch);
    }
    jQuery('#film_list').html('');
});


let lastVerifiedFilmInch = '';
let lastVerifiedFilmCm = '';

jQuery(document).on('blur', '#length_inch, #width_inch', function () {
    checkFilmVerification();
});

jQuery(document).on('input', '#length_inch, #width_inch', function () {
    lastVerifiedFilmInch = '';
    lastVerifiedFilmCm = '';
});

function checkFilmVerification() {
    let film_size_inch = jQuery('#film_size_inch').val().trim();
    let film_size_cm = jQuery('#film_size_cm').val().trim();

    // Check only if both values are available
    if (film_size_inch === '' || film_size_cm === '') return;

    if (film_size_inch !== lastVerifiedFilmInch || film_size_cm !== lastVerifiedFilmCm) {
        lastVerifiedFilmInch = film_size_inch;
        lastVerifiedFilmCm = film_size_cm;
        checkFilmName(film_size_inch, film_size_cm);
    }
}

function checkFilmName(film_size_inch, film_size_cm) {
    var id = jQuery('#FilmModal').find('#commonFilmForm').find('#id').val();
    var formUrl = "verify-film?film_size_inch=" + encodeURIComponent(film_size_inch);
    if (film_size_cm != undefined && film_size_cm != "") {
        formUrl += "&film_size_cm=" + encodeURIComponent(film_size_cm);
    }
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

function checkFilmCmName(film_size_cm) {
    var id = jQuery('#FilmModal').find('#commonFilmForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-film?film_size_cm=" + encodeURIComponent(film_size_cm) + "&id=" + id : "verify-film?film_size_cm=" + encodeURIComponent(film_size_cm);
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

function verifyFilm() {
    var FilmName = jQuery('#film_size_inch').val();
    var suggestion_list = jQuery('#film_list').html;

    if (suggestion_list != '') {
        checkFilmName(FilmName);
    }
}

jQuery('#FilmModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#FilmModal');
    thisForm.find('#id').val('');
    document.getElementById("commonFilmForm").reset();
    thisForm.find('input, textarea').each(function () {
        jQuery(this).val('');
        if (this.id !== 'film_size_inch' && this.id !== 'film_size_cm') {
            jQuery(this).removeAttr('readonly');
        }
        jQuery(this).prop('checked', false);
    });
    thisForm.find('#status').val('Active').trigger('change');
    jQuery('#FilmModal').find('#add_new').hide();
});

$('#FilmModal').on('shown.bs.modal', function () {
    const input = document.getElementById('length_inch');
    input?.focus();

    var formIdblank = jQuery('#commonFilmForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#FilmModal').find('#add_new').show();
    } else {
        jQuery('#FilmModal').find('#add_new').hide();
        jQuery('#status').val('Active').trigger('change');
    }
});

jQuery('#FilmModal').on('click', '#add_new', function () {
    jQuery('#FilmModal').find('#id').val('');
    document.getElementById("commonFilmForm").reset();
    const form = document.getElementById("commonFilmForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    clearFilmCalc();
    jQuery('#status').val('Active').trigger('change');
    jQuery('#length_inch').focus();
    jQuery('#FilmModal').find('#add_new').hide();
});

function getFilm($this = null) {
    var formUrl = "get-films";
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
                    var stgDrpHtml = `<option value="">Select Film Size</option>`;
                    for (let indx in data.films) {
                        stgDrpHtml += `<option value="${data.films[indx].film_id}" data-sq_in="${data.films[indx].sq_in || ''}" data-sq_cm="${data.films[indx].sq_cm || ''}" data-inch="${data.films[indx].film_size_inch || ''}" data-cm="${data.films[indx].film_size_cm || ''}">${data.films[indx].film_size_inch} / ${data.films[indx].film_size_cm}</option>`;
                    }

                    jQuery($this).each(function (e) {
                        let Id = jQuery(this).attr('id');
                        let Selected = jQuery(this).find("option:selected").val();
                        jQuery(this).empty().append(stgDrpHtml);
                        jQuery(this).val(Selected).trigger("change");
                    });
                    if (typeof updateFilmSizeDropdown === 'function') {
                        updateFilmSizeDropdown();
                    }
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

function addedFilm($event) {
    if ($event == true) {
        getFilm(".mst_film");
    }
}
