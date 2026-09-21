var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

// Edit film_brand row click
jQuery('#dyntable tbody').on('click', '.edit_film_brand', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillFilmBrand(data["id"]);
    }
});

// Function to fetch and fill film_brand data
function fetchAndFillFilmBrand(id) {
    if (!id) return;
    jQuery('#FilmBrandModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-film_brand",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.film_brand_data != null) {
                jQuery('#FilmBrandModal').find('#film_brand').val(data.film_brand_data.film_brand);
                jQuery('#FilmBrandModal').find('#id').val(data.film_brand_data.film_brand_id);
                jQuery('#FilmBrandModal').find('#add_new').show();

                const form = document.getElementById("commonFilmBrandForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#FilmBrandModal').find('#film_brand').focus();
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

// Reset button click for film_brand modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#FilmBrandModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonFilmBrandForm").reset();
        const form = document.getElementById("commonFilmBrandForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        jQuery('#FilmBrandModal').find('#film_brand').focus();
    } else {
        fetchAndFillFilmBrand(formId);
    }
});

$('#commonFilmBrandForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#FilmBrandModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#FilmBrandModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#FilmBrandModal').find('#commonFilmBrandForm').find('#id').val();
    var film_brand = jQuery('#FilmBrandModal').find('#film_brand').val();
    var formUrl = formId != undefined && formId != "" ? "update-film_brand" : "store-film_brand";
    var FilmBrandUrl = formId != undefined && formId != "" ? "verify-film_brand?film_brand=" + encodeURIComponent(film_brand) + "&id=" + formId : "verify-film_brand?film_brand=" + encodeURIComponent(film_brand);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (film_brand != '' && film_brand != undefined) {
        $.ajax({
            url: FilmBrandUrl,
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#FilmBrandModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#FilmBrandModal').find('#submitbtn').prop('disabled', false);
                                    addedFilmBrand(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonFilmBrandForm").reset();
                                        const form = document.getElementById("commonFilmBrandForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#FilmBrandModal').find('#film_brand').focus();
                                        if (partAfterManage != 'film_brand') {
                                            jQuery('#FilmBrandModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#FilmBrandModal').find('#submitbtn').prop('disabled', false);
                                    addedFilmBrand(true);

                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#FilmBrandModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#FilmBrandModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#FilmBrandModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#FilmBrandModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#FilmBrandModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestFilmBrand(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "film_brand-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#film_brand").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#film_brand_list').html(data.filmBrandList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#film_brand").removeClass('file-loader');
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

jQuery(document).on('click', '#film_brand_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#film_brand_suggesion').val(suggest);
    var hidden = jQuery('#film_brand_suggesion').val();
    var suggestion_list = jQuery('#film_brand_list').html;
    jQuery('#FilmBrandModal').find('#film_brand').val(hidden)
    var film_brand = hidden;

    if (suggestion_list != '') {
        checkFilmBrandName(film_brand);
    }
    jQuery('#film_brand_list').html('');
});


let lastVerifiedFilmBrand = '';

jQuery(document).on('blur', '#film_brand', function () {
    let film_brand = jQuery(this).val().trim();

    if (film_brand === '') return;

    if (film_brand !== lastVerifiedFilmBrand) {
        lastVerifiedFilmBrand = film_brand;
        checkFilmBrandName(film_brand);
    }
});

jQuery(document).on('input', '#film_brand', function () {
    lastVerifiedFilmBrand = '';
});

function checkFilmBrandName(film_brand) {
    var id = jQuery('#FilmBrandModal').find('#commonFilmBrandForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-film_brand?film_brand=" + encodeURIComponent(film_brand) + "&id=" + id : "verify-film_brand?film_brand=" + encodeURIComponent(film_brand);
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

function verifyFilmBrand() {
    var FilmBrandName = jQuery('#film_brand').val();
    var suggestion_list = jQuery('#film_brand_list').html;

    if (suggestion_list != '') {
        checkFilmBrandName(FilmBrandName);
    }
}

jQuery('#FilmBrandModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#FilmBrandModal');
    thisForm.find('#id').val('');
    document.getElementById("commonFilmBrandForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });
    jQuery('#FilmBrandModal').find('#add_new').hide();
});

$('#FilmBrandModal').on('shown.bs.modal', function () {
    const input = document.getElementById('film_brand');
    input?.focus();

    var formIdblank = jQuery('#commonFilmBrandForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#FilmBrandModal').find('#add_new').show();
    } else {
        jQuery('#FilmBrandModal').find('#add_new').hide();
    }
});

jQuery('#FilmBrandModal').on('click', '#add_new', function () {
    jQuery('#FilmBrandModal').find('#id').val('');
    document.getElementById("commonFilmBrandForm").reset();
    const form = document.getElementById("commonFilmBrandForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#film_brand').focus();
    jQuery('#FilmBrandModal').find('#add_new').hide();
});

function getFilmBrand($this = null) {
    var formUrl = "get-film_brands";
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
                    var stgDrpHtml = `<option value="">Select Film Brand</option>`;
                    for (let indx in data.film_brands) {
                        stgDrpHtml += `<option value="${data.film_brands[indx].film_brand_id}">${data.film_brands[indx].film_brand}</option>`;
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

function addedFilmBrand($event) {
    if ($event == true) {
        getFilmBrand(".mst_film_brand");
    }
}
