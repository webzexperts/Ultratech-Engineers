var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

// Edit country row click
jQuery('#dyntable tbody').on('click', '.edit_country', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillCountry(data["id"]);
    }
});

// Function to fetch and fill country data
function fetchAndFillCountry(id) {
    if (!id) return;
    jQuery('#CountryModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-country",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.country != null) {
                jQuery('#CountryModal').find('#country_name').val(data.country.country_name);
                jQuery('#CountryModal').find('#id').val(data.country.id);
                jQuery('#CountryModal').find('#add_new').show();
                const form = document.getElementById("commonCountryForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#CountryModal').find('#country_name').focus();
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

// Reset button click for country modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#CountryModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonCountryForm").reset(); 
        const form = document.getElementById("commonCountryForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        jQuery('#CountryModal').find('#country_name').focus();
    } else {
        fetchAndFillCountry(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_country', function () {
//     jQuery('#CountryModal').modal('show');
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-country",
//         type: 'GET',
//         data: "id=" + data["id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 if (data.country != null) {
//                     jQuery('#CountryModal').find('#country_name').val(data.country.country_name);
//                     jQuery('#CountryModal').find('#id').val(data.country.id);
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

$('#commonCountryForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#CountryModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#CountryModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    var formId = jQuery('#CountryModal').find('#commonCountryForm').find('#id').val();
    var Country = jQuery('#CountryModal').find("#country_name").val();
    var formUrl = formId != undefined && formId != "" ? "update-country" : "store-country";
    var CountryUrl = formId != undefined && formId != "" ? "verify-country?country_name=" + encodeURIComponent(Country) + "&id=" + formId : "verify-country?country_name=" + encodeURIComponent(Country);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    if (Country != '' && Country != undefined) {
        $.ajax({
            url: CountryUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#CountryModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#CountryModal').find('#submitbtn').prop('disabled', false);
                                    addedCountry(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonCountryForm").reset();
                                        const form = document.getElementById("commonCountryForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#country_name').focus();
                                        if (partAfterManage != 'country') {
                                            jQuery('#CountryModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#CountryModal').find('#submitbtn').prop('disabled', false);
                                    addedCountry(true);
                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#CountryModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                // toastAlert(data.response_message);
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#CountryModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#CountryModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                // toastAlert(errorMsg);
                                toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#CountryModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                // toastAlert('Something went wrong. Please try again.');
                                toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#CountryModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});


function suggestCountry(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "country-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#country_name").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#country_name_list').html(data.countryList);
                } else {
                    // toastAlert(data.response_message);
                    toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#country_name").removeClass('file-loader');
                var errMessage = JSON.parse(jqXHR.responseText);
                if (errMessage.errors) {
                    countryValidator.showErrors(errMessage.errors);
                } else if (jqXHR.status == 401) {
                    // toastAlert(jqXHR.statusText);
                    toastr.error(jqXHR.statusText);
                } else {
                    // toastAlert('Something went wrong!');
                    toastr.error('Something went wrong!');
                    console.log(JSON.parse(jqXHR.responseText));
                }
            }
        });
    }
}

jQuery(document).on('click', '#country_name_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#country_suggesion').val(suggest);
    var hidden = jQuery('#country_suggesion').val();
    var suggestion_list = jQuery('#country_name_list').html;
    jQuery('#CountryModal').find('#country_name').val(hidden)
    var country_name = hidden;

    if (suggestion_list != '') {
        checkCountryName(country_name);
    }
    jQuery('#country_name_list').html('');
});
let lastVerifiedcountry = '';

jQuery(document).on('blur', '#country_name', function () {
    let country_name = jQuery(this).val().trim();

    if (country_name === '') return;

    if (country_name !== lastVerifiedcountry) {
        lastVerifiedcountry = country_name;
        checkCountryName(country_name);
    }
});

jQuery(document).on('input', '#country_name', function () {
    lastVerifiedcountry = '';
});

function checkCountryName(country_name) {
    var id = jQuery('#CountryModal').find('#commonCountryForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-country?country_name=" + encodeURIComponent(country_name) + "&id=" + id : "verify-country?country_name=" + encodeURIComponent(country_name);
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

function verifyCountry() {
    var CountryName = jQuery('#country_name').val();
    var suggestion_list = jQuery('#country_name_list').html;

    if (suggestion_list != '') {
        checkCountryName(CountryName);
    }
}

jQuery('#CountryModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#CountryModal');
    thisForm.find('#id').val('');
    lastVerifiedcountry = '';
    document.getElementById("commonCountryForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });

    if (partAfterManage == 'country') {
        // window.location.reload();
    }
    jQuery('#CountryModal').find('#add_new').hide();
});

$('#CountryModal').on('shown.bs.modal', function () {
    const input = document.getElementById('country_name');
    input?.focus();

    var formIdblank = jQuery('#commonCountryForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#CountryModal').find('#add_new').show();
    } else {
        jQuery('#CountryModal').find('#add_new').hide();
    }
});

jQuery('#CountryModal').on('click', '#add_new', function () {
    jQuery('#CountryModal').find('#id').val('');
    document.getElementById("commonCountryForm").reset();
    lastVerifiedcountry = '';
    const form = document.getElementById("commonCountryForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#country_name').focus();
    jQuery('#CountryModal').find('#add_new').hide();
});

function getCountry($this = null) {
    var formUrl = "get-countries";
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
                    var stgDrpHtml = `<option value="">Select Country</option>`;
                    for (let indx in data.countries) {
                        stgDrpHtml += `<option value="${data.countries[indx].id}">${data.countries[indx].country_name}</option>`;
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

function addedCountry($event) {
    if ($event == true) {
        getCountry(".suggest_country_name");
    }
}