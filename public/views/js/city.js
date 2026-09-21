var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

// Edit city row click
jQuery('#dyntable tbody').on('click', '.edit_city', function () {
    jQuery('#CityModal').modal('show');
    var data = table.row(jQuery(this).parents('tr')).data();
    fetchAndFillCity(data["id"]);
});

// Function to fetch and fill city data
function fetchAndFillCity(id) {
    if (!id) return;
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-city",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.city_data != null) {
                jQuery('#CityModal').find('#city').val(data.city_data.city);
                jQuery('#CityModal').find('#state_id').val(data.city_data.state_id).trigger("change.select2");
                jQuery('#CityModal').find('#state_code').val(data.city_data.state_code);
                jQuery('#CityModal').find('#country_name').val(data.city_data.country_name);
                jQuery('#CityModal').find('#id').val(data.city_data.id);
                jQuery('#CityModal').find('#add_new').show();

                const form = document.getElementById("commonCityForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#CityModal').find('#city').focus();
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
        },
        complete: function () {
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        }
    });
}

// Reset button click for city modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#CityModal').find('#id').val();
    if (!formId) {
        const form = document.getElementById("commonCityForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
        lastVerifiedCity = '';
        jQuery('#CityModal').find('#city').focus();
        jQuery('#CityModal').find('#state_id').val('').trigger('change.select2');
        jQuery("#commonCityForm").find('#state_code').prop({ tabindex: -1, readonly: true });
        jQuery("#commonCityForm").find('#country_name').prop({ tabindex: -1, readonly: true });
    } else {
        fetchAndFillCity(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_city', function () {
//     jQuery('#CityModal').modal('show');
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-city",
//         type: 'GET',
//         data: "id=" + data["id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 if (data.city_data != null) {
//                     jQuery('#CityModal').find('#city').val(data.city_data.city);
//                     jQuery('#CityModal').find('#state_id').val(data.city_data.state_id).trigger("change.select2");
//                     jQuery('#CityModal').find('#state_code').val(data.city_data.state_code);
//                     jQuery('#CityModal').find('#country_name').val(data.city_data.country_name);
//                     jQuery('#CityModal').find('#id').val(data.city_data.id);
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

$('#commonCityForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#CityModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#CityModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    let stateName = jQuery("#state_id").val();
    let districtName = jQuery("#city").val();
    var formId = jQuery('#CityModal').find('#commonCityForm').find('#id').val();
    // var CityUrl = formId != undefined && formId != "" ? "verify-city-data?state=" + encodeURIComponent(stateName) + "&city=" + encodeURIComponent(districtName) + "&id=" + formId : "verify-city-data?state=" + encodeURIComponent(stateName) + "&city=" + encodeURIComponent(districtName);
    var CityUrl = formId != undefined && formId != "" ? "verify-city-data?city=" + encodeURIComponent(districtName) + "&id=" + formId : "verify-city-data?city=" + encodeURIComponent(districtName);
    var formUrl = formId != undefined && formId != "" ? "update-city" : "store-city";
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if((stateName != '' && stateName != undefined)) {
        $.ajax({
            url: CityUrl,
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
                    jQuery('#CityModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#CityModal').find('#submitbtn').prop('disabled', false);
                                    addedCity(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonCityForm").reset();
                                        const form = document.getElementById("commonCityForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#city').focus();
                                        $("#state_id").val('').trigger('change');
                                        if (partAfterManage != 'city') {
                                            jQuery('#CityModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#CityModal').find('#submitbtn').prop('disabled', false);
                                    addedCity(true);
                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#CityModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                 toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#CityModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#CityModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#CityModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#CityModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestCity(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "city-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#city").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#city_name_list').html(data.cityList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#city").removeClass('file-loader');
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

jQuery(document).on('click', '#city_name_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#city_suggesion').val(suggest);
    var hidden = jQuery('#city_suggesion').val();
    var suggestion_list = jQuery('#city_name_list').html;
    jQuery('#CityModal').find('#city').val(hidden)
    var city = hidden;

    if (suggestion_list != '') {
        CheckCity(city);
    }
    jQuery('#city_name_list').html('');
});


let lastVerifiedCity = '';

jQuery(document).on('blur', '#city', function () {
    let city = jQuery(this).val().trim();
    // var state = jQuery('#state_id').val();
    if (city === '') return;

    if (city !== lastVerifiedCity) {
        lastVerifiedCity = city;
        // CheckCity(city,state);
        CheckCity(city);
    }
});

jQuery(document).on('input', '#city', function () {
    lastVerifiedCity = '';
});

// function CheckCity(city,state) {
function CheckCity(city) {
    var state = jQuery('#state_id').val();
    var city = jQuery('#city').val();
    var formId = jQuery('#CityModal').find('#commonCityForm').find('#id').val();
    // var formUrl = formId != undefined && formId != "" ? "verify-city-data?state=" + encodeURIComponent(state) + "&city=" + encodeURIComponent(city) + "&id=" + formId : "verify-city-data?state=" + encodeURIComponent(state) + "&city=" + encodeURIComponent(city);
    var formUrl = formId != undefined && formId != "" ? "verify-city-data?city=" + encodeURIComponent(city) + "&id=" + formId : "verify-city-data?city=" + encodeURIComponent(city);
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
    var CityName = jQuery('#city').val();
    var suggestion_list = jQuery('#city_name_list').html;

    if (suggestion_list != '') {
        CheckCity(CityName);
    }
}

jQuery('#CityModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#CityModal');
    thisForm.find('#id').val('');
    document.getElementById("commonCityForm").reset();
    lastVerifiedCity = '';
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        // jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });

    if (partAfterManage == 'city') {
        // window.location.reload();
    }

    jQuery('#CityModal').find('#add_new').hide();
});

$('#CityModal').on('shown.bs.modal', function () {
    const input = document.getElementById('city');
    input?.focus();

    var formIdblank = jQuery('#commonCityForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#CityModal').find('#add_new').show();
    } else {
        jQuery('#CityModal').find('#add_new').hide();
    }
});

jQuery('#CityModal').on('click', '#add_new', function () {
    jQuery('#CityModal').find('#id').val('');
    document.getElementById("commonCityForm").reset();
    const form = document.getElementById("commonCityForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#city').focus();
    lastVerifiedCity = '';
    $("#state_id").val('').trigger('change.select2');
    jQuery('#CityModal').find('#add_new').hide();
});

jQuery("#commonCityForm").find('#state_code').prop({ tabindex: -1, readonly: true });
jQuery("#commonCityForm").find('#country_name').prop({ tabindex: -1, readonly: true });

// jQuery('#state_id').on('change',function () {
//     CheckCity();
// });

function getCity($this = null) {
    var formUrl = "get-cities";
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
                    var stgDrpHtml = `<option value="">Select City</option>`;
                    for (let indx in data.cities) {
                        stgDrpHtml += `<option value="${data.cities[indx].id}">${data.cities[indx].city}</option>`;
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

function addedCity($event) {
    if ($event == true) {
        getCity(".suggest_city_name");
    }
}

jQuery('#CountryModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('country_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});
jQuery('#StateModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('state_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});