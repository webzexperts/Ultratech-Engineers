// Edit location row click
jQuery('#dyntable tbody').on('click', '.edit_location', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["location_id"]) {
        fetchAndFillLocation(data["location_id"]);
    }
});
// var ckClassicEditor = document.querySelector("#location_header_address")
// if (ckClassicEditor) {

//         ClassicEditor
//             .create(document.querySelector('.ckeditor-classic'))
//             .then(function (editor) {

//             })
//             .catch(function (error) {
//                 console.error(error);
//             });

// }

// Function to fetch and fill location data
function fetchAndFillLocation(id) {
    if (!id) return;
    jQuery('#LocationModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-location",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.location_data != null) {
                jQuery('#LocationModal').find('#location_name').val(data.location_data.location_name);
                jQuery('#LocationModal').find('#location_type').val(data.location_data.location_type).trigger("change.select2");
                jQuery('#LocationModal').find('#location_code').val(data.location_data.location_code);
                jQuery('#LocationModal').find('#location_address').val(data.location_data.location_address);
                jQuery('#LocationModal').find('#location_city_id').val(data.location_data.location_city_id).trigger("change");
                // if (zeroToEmpty(data.location_data.location_country_id) !== '') {
                //     jQuery('#LocationModal').find('#location_country_id').val(zeroToEmpty(data.location_data.location_country_id)).trigger('change.select2');
                //     getLocationStates().done(function () {
                //         jQuery('#LocationModal').find('#location_state_id').val(zeroToEmpty(data.location_data.location_state_id)).trigger('change.select2');
                //         if (zeroToEmpty(data.location_data.location_state_id) !== '') {
                //             getLocationCity().done(function () {
                //                 jQuery('#LocationModal').find('#location_city_id').val(zeroToEmpty(data.location_data.location_city_id)).trigger('change.select2');
                //             });
                //         }
                //     });
                // }
                jQuery('#LocationModal').find('#location_nabl_applicable').val(data.location_data.location_nabl_applicable).trigger("change.select2");
                jQuery('#LocationModal').find('#nabl_id').val(data.location_data.nabl_id).trigger("change.select2");
                jQuery('#LocationModal').find('#location_ilac_applicable').val(data.location_data.location_ilac_applicable).trigger("change.select2");
                jQuery('#LocationModal').find('#location_gstin').val(data.location_data.location_gstin);
                jQuery('#LocationModal').find('#location_pan').val(data.location_data.location_pan);
                jQuery('#LocationModal').find('#location_email_id').val(data.location_data.location_email_id);
                jQuery('#LocationModal').find('#location_phone_no').val(data.location_data.location_phone_no);
                jQuery('#LocationModal').find('#location_pin_code').val(data.location_data.location_pin_code);
                jQuery('#LocationModal').find('#location_company_name').val(data.location_data.location_company_name);
                jQuery('#LocationModal').find('#gst_bill_location').val(data.location_data.gst_bill_location).trigger("change.select2");
                jQuery('#LocationModal').find('#location_status').val(data.location_data.location_status).trigger("change.select2");
                jQuery('#LocationModal').find('#show_all_location_camera').prop('checked', data.location_data.show_all_location_camera === 'Yes');

                // jQuery('#LocationModal').find('#location_header_address').val(data.location_data.location_header_address);
                editors['location_header_address'].setData(data.location_data.location_header_address ?? "");

                if (data.location_data.code_in_use == true) {
                    // setSelect2Required('#location_code', true);
                    jQuery('#LocationModal #location_code').prop('readonly', true).addClass('skip-tab');
                }else{
                    jQuery('#LocationModal #location_code').prop('readonly', false).removeClass('skip-tab');
                }
                if (data.location_data.bill_in_use == true) { 

                    jQuery('#gst_bill_location').addClass('skip-tab');
                    setSelect2Readonly('#gst_bill_location', true);

                } else { 
                    jQuery('#gst_bill_location').removeClass('skip-tab'); 
                    setSelect2Readonly('#gst_bill_location', false);
                }
                jQuery('#LocationModal').find('#location_email_id').val(data.location_data.location_email_id);
            
                // reset all first
                jQuery('input[name="location_nabl_test[]"]').prop('checked', false);

                if (data.location_data.location_nabl_test) {
                    let tests = data.location_data.location_nabl_test.split(',');
                    setTimeout(() => {
                        
                        tests.forEach(function (val) {
                            jQuery('input[name="location_nabl_test[]"][value="' + val + '"]').prop('checked', true);
                        });
                    }, 300);
                }
                if (data.location_data.location_nabl_symbol != "" && data.location_data.location_nabl_symbol != undefined) {
                    let fullPath = data.location_data.location_nabl_symbol;
                    let fileName = fullPath.split('/').pop();
                    jQuery('#LocationModal').find("#location_nabl_symbol_doc").val(fullPath);
                    jQuery('#LocationModal').find('#location_nabl_symbol_prev').attr('href', uploadURL + fullPath).removeClass('hide');
                    jQuery('#LocationModal').find('#location_nabl_symbol_remove').addClass('i-block').removeClass('hide');
                    jQuery('#LocationModal').find('#location_nabl_symbol_img-prev-box').removeClass('hide');
                    jQuery('#LocationModal').find('#location_nabl_symbol_img-prev').html(fileName);
                    let fileInput = jQuery('#LocationModal').find('#location_nabl_symbol');
                    let newFile = new DataTransfer();
                    newFile.items.add(new File([""], fileName));
                    fileInput[0].files = newFile.files;
                } else {
                    jQuery('#LocationModal').find('#location_nabl_symbol_doc').val('');
                    jQuery('#LocationModal').find('#location_nabl_symbol_prev').attr('href', '#').addClass('hide');
                    jQuery('#LocationModal').find('#location_nabl_symbol_remove').removeClass('i-block').addClass('hide');
                    jQuery('#LocationModal').find('#location_nabl_symbol_img-prev').html('');
                    jQuery('#LocationModal').find('#location_nabl_symbol_img-prev-box').addClass('hide');
                    jQuery('#LocationModal').find('#location_nabl_symbol_img-prev-box').html('');
                    jQuery('#LocationModal').find('#location_nabl_symbol').val('');
                }
                if (data.location_data.location_ilac_symbol != "" && data.location_data.location_ilac_symbol != undefined) {
                    let fullPath = data.location_data.location_ilac_symbol;
                    let fileName = fullPath.split('/').pop();
                    jQuery('#LocationModal').find("#location_ilac_symbol_doc").val(fullPath);
                    jQuery('#LocationModal').find('#location_ilac_symbol_prev').attr('href', uploadURL + fullPath).removeClass('hide');
                    jQuery('#LocationModal').find('#location_ilac_symbol_remove').addClass('i-block').removeClass('hide');
                    jQuery('#LocationModal').find('#location_ilac_symbol_img-prev-box').removeClass('hide');
                    jQuery('#LocationModal').find('#location_ilac_symbol_img-prev').html(fileName);
                    let fileInput = jQuery('#LocationModal').find('#location_ilac_symbol');
                    let newFile = new DataTransfer();
                    newFile.items.add(new File([""], fileName));
                    fileInput[0].files = newFile.files;
                } else {
                    jQuery('#LocationModal').find('#location_ilac_symbol_doc').val('');
                    jQuery('#LocationModal').find('#location_ilac_symbol_prev').attr('href', '#').addClass('hide');
                    jQuery('#LocationModal').find('#location_ilac_symbol_remove').removeClass('i-block').addClass('hide');
                    jQuery('#LocationModal').find('#location_ilac_symbol_img-prev').html('');
                    jQuery('#LocationModal').find('#location_ilac_symbol_img-prev-box').addClass('hide');
                    jQuery('#LocationModal').find('#location_ilac_symbol_img-prev-box').html('');
                    jQuery('#LocationModal').find('#location_ilac_symbol').val('');
                }


                UlrConf();
                GSTBillChange();
                jQuery('#LocationModal').find('#add_new').show();
                jQuery('#LocationModal').find('#id').val(data.location_data.location_id);

                const form = document.getElementById("commonLocationForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#LocationModal').find('#location_name').focus();
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

// Reset button click for location modal
jQuery('#resetbtn').on('click', function () {
    var formId = jQuery('#LocationModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonLocationForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        jQuery('#commonLocationForm #location_type').val('').trigger("change.select2");
        jQuery('#commonLocationForm #location_country_id').val('').trigger("change");
        jQuery('#commonLocationForm #location_state_id').val('').trigger("change");
        jQuery('#commonLocationForm #location_city_id').val('').trigger("change");
        jQuery('#commonLocationForm #location_nabl_applicable').val('').trigger("change.select2");
        jQuery('#commonLocationForm #nabl_id').val('').trigger("change.select2");
        jQuery('#commonLocationForm #location_ilac_applicable').val('').trigger("change.select2");
        editors['location_header_address'].setData("");
        jQuery('#commonLocationForm').find('#location_status').val('Active').trigger('change.select2');
        jQuery('#LocationModal').find('#show_all_location_camera').prop('checked', false);
        jQuery('#commonLocationForm').find('#gst_bill_location').val('').trigger('change.select2');
        jQuery('input[name="location_nabl_test[]"]').prop('checked', false);

        jQuery('#location_name').focus();
        jQuery('#LocationModal').find('#location_nabl_symbol_doc').val('');
        jQuery('#LocationModal').find('#location_nabl_symbol_prev').attr('href', '#');
        jQuery('#LocationModal').find('#location_nabl_symbol_prev').addClass('hide');
        jQuery('#LocationModal').find('#location_nabl_symbol_remove').removeClass('i-block').addClass('hide');
        jQuery('#LocationModal').find('#location_nabl_symbol_img-prev').html('');
        jQuery('#LocationModal').find('#location_nabl_symbol_img-prev-box').addClass('hide');
        jQuery('#LocationModal').find('#location_nabl_symbol_img-prev-box').html('');

        jQuery('#LocationModal').find('#location_ilac_symbol_doc').val('');
        jQuery('#LocationModal').find('#location_ilac_symbol_prev').attr('href', '#');
        jQuery('#LocationModal').find('#location_ilac_symbol_prev').addClass('hide');
        jQuery('#LocationModal').find('#location_ilac_symbol_remove').removeClass('i-block').addClass('hide');
        jQuery('#LocationModal').find('#location_ilac_symbol_img-prev').html('');
        jQuery('#LocationModal').find('#location_ilac_symbol_img-prev-box').addClass('hide');
        jQuery('#LocationModal').find('#location_ilac_symbol_img-prev-box').html('');
        UlrConf();
        getLNRData();
        jQuery('#LocationModal #location_code').prop('readonly', false).removeClass('skip-tab');
        jQuery('#LocationModal #gst_bill_location').removeClass('skip-tab');
        setSelect2Readonly('#gst_bill_location', false);
    } else {
        fetchAndFillLocation(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_location', function () {
//     jQuery('#LocationModal').modal('show');
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-location",
//         type: 'GET',
//         data: "id=" + data["location_id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 if (data.location_data != null) {
//                     jQuery('#LocationModal').find('#location_name').val(data.location_data.location_name);
//                     jQuery('#LocationModal').find('#location_type').val(data.location_data.location_type).trigger("change.select2");
//                     jQuery('#LocationModal').find('#location_code').val(data.location_data.location_code);
//                     jQuery('#LocationModal').find('#location_address').val(data.location_data.location_address);
//                     jQuery('#LocationModal').find('#location_country_id').val(data.location_data.location_country_id).trigger("change.select2");
//                     if (zeroToEmpty(data.location_data.location_country_id) !== '') {
//                         jQuery('#LocationModal').find('#location_country_id').val(zeroToEmpty(data.location_data.location_country_id)).trigger('change.select2');
//                         getLocationStates().done(function () {
//                             jQuery('#LocationModal').find('#location_state_id').val(zeroToEmpty(data.location_data.location_state_id)).trigger('change.select2');
//                             if (zeroToEmpty(data.location_data.location_state_id) !== '') {
//                                 getLocationCity().done(function () {
//                                     jQuery('#LocationModal').find('#location_city_id').val(zeroToEmpty(data.location_data.location_city_id)).trigger('change.select2');
//                                 });
//                             }
//                         });
//                     }
//                     jQuery('#LocationModal').find('#location_nabl_applicable').val(data.location_data.location_nabl_applicable).trigger("change.select2");
//                     jQuery('#LocationModal').find('#nabl_id').val(data.location_data.nabl_id).trigger("change.select2");
//                     jQuery('#LocationModal').find('#location_ilac_applicable').val(data.location_data.location_ilac_applicable).trigger("change.select2");
//                     jQuery('#LocationModal').find('#location_gstin').val(data.location_data.location_gstin);
//                     jQuery('#LocationModal').find('#location_company_name').val(data.location_data.location_company_name);
//                     UlrConf();
//                     jQuery('#LocationModal').find('#id').val(data.location_data.location_id);
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

function UlrConf() {
    let thisForm = jQuery('#commonLocationForm');
    let location_nabl_applicable = thisForm.find('#location_nabl_applicable option:selected').val();
    if (location_nabl_applicable == "Yes") {
        setSelect2Required('#nabl_id', true);
        setSelect2Readonly('#nabl_id', false);
        setSelect2Required('#location_nabl_symbol', true);
        jQuery("#location_nabl_symbol").css("pointer-events", "auto");
        setSelect2Readonly('#location_nabl_symbol', false);
        setSelect2Readonly('#location_ilac_applicable', false);
        setSelect2Required('#location_ilac_applicable', true);
        // $('#div_nabl_conf').show();
    } else if (location_nabl_applicable == "No") {
        setSelect2Required('#nabl_id', false);
        setSelect2Readonly('#nabl_id', true);
        setSelect2Required('#location_nabl_symbol', false);
        setSelect2Readonly('#location_nabl_symbol', true);
        jQuery("#location_nabl_symbol").css("pointer-events", "none");
        jQuery('#LocationModal').find('#location_nabl_symbol_doc').val('');
        jQuery('#LocationModal').find('#location_nabl_symbol_prev').attr('href', '#').addClass('hide');
        jQuery('#LocationModal').find('#location_nabl_symbol_remove').removeClass('i-block').addClass('hide');
        jQuery('#LocationModal').find('#location_nabl_symbol_img-prev').html('');
        jQuery('#LocationModal').find('#location_nabl_symbol_img-prev-box').addClass('hide');
        jQuery('#LocationModal').find('#location_nabl_symbol_img-prev-box').html('');
        jQuery('#LocationModal').find('#location_nabl_symbol').val('');
        jQuery('#LocationModal').find('#location_ilac_applicable').val('').trigger("change.select2");
        setSelect2Required('#location_ilac_applicable', false);
        setSelect2Readonly('#location_ilac_applicable', true);
        setSelect2Required('#location_ilac_symbol', false);
        setSelect2Readonly('#location_ilac_symbol', true);
        jQuery("#location_ilac_symbol").css("pointer-events", "none");
        jQuery('#LocationModal').find('#location_ilac_symbol_doc').val('');
        jQuery('#LocationModal').find('#location_ilac_symbol_prev').attr('href', '#').addClass('hide');
        jQuery('#LocationModal').find('#location_ilac_symbol_remove').removeClass('i-block').addClass('hide');
        jQuery('#LocationModal').find('#location_ilac_symbol_img-prev').html('');
        jQuery('#LocationModal').find('#location_ilac_symbol_img-prev-box').addClass('hide');
        jQuery('#LocationModal').find('#location_ilac_symbol_img-prev-box').html('');
        jQuery('#LocationModal').find('#location_ilac_symbol').val('');
        // $('#div_nabl_conf').hide();
        $('#nabl_id').val('').trigger('change');
    } else {
        setSelect2Required('#nabl_id', false);
        // $('#div_nabl_conf').hide();
        setSelect2Readonly('#nabl_id', true);
        setSelect2Required('#location_nabl_symbol', false);
        setSelect2Readonly('#location_nabl_symbol', true);
        jQuery("#location_nabl_symbol").css("pointer-events", "none");
        jQuery('#LocationModal').find('#location_nabl_symbol_doc').val('');
        jQuery('#LocationModal').find('#location_nabl_symbol_prev').attr('href', '#').addClass('hide');
        jQuery('#LocationModal').find('#location_nabl_symbol_remove').removeClass('i-block').addClass('hide');
        jQuery('#LocationModal').find('#location_nabl_symbol_img-prev').html('');
        jQuery('#LocationModal').find('#location_nabl_symbol_img-prev-box').addClass('hide');
        jQuery('#LocationModal').find('#location_nabl_symbol_img-prev-box').html('');
        jQuery('#LocationModal').find('#location_nabl_symbol').val('');
        jQuery('#LocationModal').find('#location_ilac_applicable').val('').trigger("change.select2");
        setSelect2Required('#location_ilac_applicable', false);
        setSelect2Readonly('#location_ilac_applicable', true);
        setSelect2Required('#location_ilac_symbol', false);
        setSelect2Readonly('#location_ilac_symbol', true);
        jQuery("#location_ilac_symbol").css("pointer-events", "none");
        jQuery('#LocationModal').find('#location_ilac_symbol_doc').val('');
        jQuery('#LocationModal').find('#location_ilac_symbol_prev').attr('href', '#').addClass('hide');
        jQuery('#LocationModal').find('#location_ilac_symbol_remove').removeClass('i-block').addClass('hide');
        jQuery('#LocationModal').find('#location_ilac_symbol_img-prev').html('');
        jQuery('#LocationModal').find('#location_ilac_symbol_img-prev-box').addClass('hide');
        jQuery('#LocationModal').find('#location_ilac_symbol_img-prev-box').html('');
        jQuery('#LocationModal').find('#location_ilac_symbol').val('');
        $('#nabl_id').val('').trigger('change');
    }
}

jQuery('#commonLocationForm #location_nabl_applicable').on('change', function () {
    UlrConf();
});

function changeLocationIlacApplicable() {
    let thisForm = jQuery('#commonLocationForm');
    let location_ilac_applicable = thisForm.find('#location_ilac_applicable option:selected').val();
    if (location_ilac_applicable == "Yes") {
        setSelect2Required('#location_ilac_symbol', true);
        jQuery("#location_ilac_symbol").css("pointer-events", "auto");
        setSelect2Readonly('#location_ilac_symbol', false);
    } else if (location_ilac_applicable == "No") {
        setSelect2Required('#location_ilac_symbol', false);
        setSelect2Readonly('#location_ilac_symbol', true);
        jQuery("#location_ilac_symbol").css("pointer-events", "none");
        jQuery('#LocationModal').find('#location_ilac_symbol_doc').val('');
        jQuery('#LocationModal').find('#location_ilac_symbol_prev').attr('href', '#').addClass('hide');
        jQuery('#LocationModal').find('#location_ilac_symbol_remove').removeClass('i-block').addClass('hide');
        jQuery('#LocationModal').find('#location_ilac_symbol_img-prev').html('');
        jQuery('#LocationModal').find('#location_ilac_symbol_img-prev-box').addClass('hide');
        jQuery('#LocationModal').find('#location_ilac_symbol_img-prev-box').html('');
        jQuery('#LocationModal').find('#location_ilac_symbol').val('');

    } else {
        setSelect2Required('#location_ilac_symbol', false);
        setSelect2Readonly('#location_ilac_symbol', true);
        jQuery("#location_ilac_symbol").css("pointer-events", "none");
        jQuery('#LocationModal').find('#location_ilac_symbol_doc').val('');
        jQuery('#LocationModal').find('#location_ilac_symbol_prev').attr('href', '#').addClass('hide');
        jQuery('#LocationModal').find('#location_ilac_symbol_remove').removeClass('i-block').addClass('hide');
        jQuery('#LocationModal').find('#location_ilac_symbol_img-prev').html('');
        jQuery('#LocationModal').find('#location_ilac_symbol_img-prev-box').addClass('hide');
        jQuery('#LocationModal').find('#location_ilac_symbol_img-prev-box').html('');
        jQuery('#LocationModal').find('#location_ilac_symbol').val('');
    }
}

jQuery('#commonLocationForm #location_ilac_applicable').on('change', function () {
    changeLocationIlacApplicable();
});
jQuery('input[name="location_nabl_test[]"]').on('change', function () {

    if (jQuery('input[name="location_nabl_test[]"]:checked').length > 0) {
        jQuery('.nabl-error').hide();
        jQuery('input[name="location_nabl_test[]"]').removeClass('is-invalid');
        $('#location_gstin').removeClass('is-invalid').addClass('is-valid');
    }
});

$('#commonLocationForm').on('submit', function (e) {
    // jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    // jQuery('#LocationModal').find('#submitbtn').prop('disabled', true);
    // e.preventDefault();

    // let form = this;
    // if (!form.checkValidity()) {
    //     e.stopPropagation();
    //     $('.nabl-error').show();
    //     $(form).addClass('was-validated');
    //     jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
    //     jQuery('#LocationModal').find('#submitbtn').prop('disabled', false);
    //     return;
    // }
    $('#location_gstin').removeClass('is-invalid');

    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#LocationModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;

    let headerAddressData = editors['location_header_address'].getData() ?? "";
    headerAddressData = headerAddressData
        .replace(/<strong>/gi, '<b>')
        .replace(/<\/strong>/gi, '</b>');
    $('#location_header_address').val(headerAddressData);

    let customError = false;
    $('#gstinValidationMessage').hide();
    $('#location_gstin').removeClass('is-invalid');

    if (!form.checkValidity()) {
        e.stopPropagation();
        $('.nabl-error').show();
        $(form).addClass('was-validated');
        customError = true;
    }

    let location_gstin = $('#location_gstin').val().trim();

    var gst_bill_location = jQuery('#LocationModal #gst_bill_location option:selected').val();
    if (gst_bill_location == 'Yes') {
        jQuery('#location_gstin').addClass('is-invalid');
        if (location_gstin == "") {
            jQuery('#gstinValidationMessage').show();
            customError = true;
        }
    } else {
        $('#LocationModal #location_gstin').removeClass('is-invalid').addClass('is-valid');
    }
    let nablChecked = $('input[name="location_nabl_test[]"]:checked').length;

    if (nablChecked === 0) {
        $('.nabl-error').show(); // jo tame custom error message use karo cho
        customError = true;

        // optional: red border show karva mate
        $('.border').addClass('is-invalid');
    } else {
        $('.nabl-error').hide();
        $('.border').removeClass('is-invalid').addClass('is-valid');
    }

    if (customError) {
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#LocationModal').find('#submitbtn').prop('disabled', false);
        return;
    }


    if (location_gstin !== "") {
        if (location_gstin.length !== 15) {
        // if (location_gstin.length !== 15 || !isValidGSTIN(location_gstin)) {
            $('#location_gstin').addClass('is-invalid').removeClass('is-valid');
            toastr.error('Enter Valid GSTIN.');
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#LocationModal').find('#submitbtn').prop('disabled', false);
            return;
        } else {
            $('#location_gstin').removeClass('is-invalid').addClass('is-valid');
        }
    }

    var formId = jQuery('#LocationModal').find('#commonLocationForm').find('#id').val();
    var location_name = jQuery('#LocationModal').find("#location_name").val();
    var location_code = jQuery('#LocationModal').find("#location_code").val();
    var formUrl = formId != undefined && formId != "" ? "update-location" : "store-location";
    // var LocationUrl = formId != undefined && formId != "" ? "verify-location_name?location_name=" + encodeURIComponent(location_code) + "&location_code=" + encodeURIComponent(location_code) + "&id=" + formId : "verify-location_name?location_name=" + encodeURIComponent(location_code) + "&location_code=" + encodeURIComponent(location_code);
    var LocationUrl = formId != undefined && formId != "" ? "verify-location_name?location_name=" + encodeURIComponent(location_name) + "&id=" + formId : "verify-location_name?location_name=" + encodeURIComponent(location_name);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    if (location_name != '' && location_name != undefined) {
        $.ajax({
            url: LocationUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#LocationModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#LocationModal').find('#submitbtn').prop('disabled', false);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonLocationForm").reset();
                                        const form = document.getElementById("commonLocationForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }
                                        getLNRData();
                                        jQuery('#commonLocationForm #location_type').val('').trigger("change.select2");
                                        jQuery('#commonLocationForm #location_country_id').val('').trigger("change");
                                        jQuery('#commonLocationForm #location_state_id').val('').trigger("change");
                                        jQuery('#commonLocationForm #location_city_id').val('').trigger("change");
                                        jQuery('#commonLocationForm #location_nabl_applicable').val('').trigger("change.select2");
                                        jQuery('#commonLocationForm #nabl_id').val('').trigger("change.select2");
                                        jQuery('#commonLocationForm #location_ilac_applicable').val('').trigger("change.select2");

                                        jQuery('#LocationModal').find('#location_nabl_symbol_doc').val('');
                                        jQuery('#LocationModal').find('#location_nabl_symbol_prev').attr('href', '#');
                                        jQuery('#LocationModal').find('#location_nabl_symbol_prev').addClass('hide');
                                        jQuery('#LocationModal').find('#location_nabl_symbol_remove').removeClass('i-block').addClass('hide');
                                        jQuery('#LocationModal').find('#location_nabl_symbol_img-prev').html('');
                                        jQuery('#LocationModal').find('#location_nabl_symbol_img-prev-box').addClass('hide');
                                        jQuery('#LocationModal').find('#location_nabl_symbol_img-prev-box').html('');

                                        jQuery('#LocationModal').find('#location_ilac_symbol_doc').val('');
                                        jQuery('#LocationModal').find('#location_ilac_symbol_prev').attr('href', '#');
                                        jQuery('#LocationModal').find('#location_ilac_symbol_prev').addClass('hide');
                                        jQuery('#LocationModal').find('#location_ilac_symbol_remove').removeClass('i-block').addClass('hide');
                                        jQuery('#LocationModal').find('#location_ilac_symbol_img-prev').html('');
                                        jQuery('#LocationModal').find('#location_ilac_symbol_img-prev-box').addClass('hide');
                                        jQuery('#LocationModal').find('#location_ilac_symbol_img-prev-box').html('');
                                        jQuery('#location_name').focus();
                                        UlrConf();
                                        jQuery('#LocationModal').find('#gst_bill_location').val('').trigger('change.select2');
                                        editors['location_header_address'].setData("");
                                        jQuery('#LocationModal').find('#location_status').val('Active').trigger('change.select2');
                                        jQuery('#LocationModal').find('#show_all_location_camera').prop('checked', false);

                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#LocationModal').find('#submitbtn').prop('disabled', false);
                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#LocationModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                 toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#LocationModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#LocationModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#LocationModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#LocationModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestLocationName(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "location_name-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#location_name").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#location_name_list').html(data.location_nameList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#location_name").removeClass('file-loader');
                var errMessage = JSON.parse(jqXHR.responseText);
                if (errMessage.errors) {
                    location_nameValidator.showErrors(errMessage.errors);
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

jQuery(document).on('click', '#location_name_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#location_name_suggesion').val(suggest);
    var hidden = jQuery('#location_name_suggesion').val();
    var suggestion_list = jQuery('#location_name_list').html;
    jQuery('#LocationModal').find('#location_name').val(hidden)
    var location_name = hidden;
    if (suggestion_list != '') {
        checkLocationName(location_name);
    }
    jQuery('#location_name_list').html('');
});

function checkLocationName(location_name, location_code) {
    var id = jQuery('#LocationModal').find('#commonLocationForm').find('#id').val();
    var location_name = jQuery('#LocationModal').find("#location_name").val();
    var location_code = jQuery('#LocationModal').find("#location_code").val();
    // var formUrl = id != undefined && id != "" ? "verify-location_name?location_name=" + encodeURIComponent(location_code) + "&location_code=" + encodeURIComponent(location_code) + "&id=" + id : "verify-location_name?location_name=" + encodeURIComponent(location_code) + "&location_code=" + encodeURIComponent(location_code);
    var formUrl = id != undefined && id != "" ? "verify-location_name?location_name=" + encodeURIComponent(location_name) + "&location_code=" + encodeURIComponent(location_code) + "&id=" + id : "verify-location_name?location_name=" + encodeURIComponent(location_name) + "&location_code=" + encodeURIComponent(location_code);
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

function verifyLocationName() {
    var LocationName = jQuery('#location_name').val();
    var suggestion_list = jQuery('#location_name_list').html;
    if (suggestion_list != '') {
        checkLocationName(LocationName);
    }
    jQuery('#location_name_list').html('');
}

function suggestLocationCode(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "location_code-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#location_code").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#location_code_list').html(data.location_codeList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#location_code").removeClass('file-loader');
                var errMessage = JSON.parse(jqXHR.responseText);
                if (errMessage.errors) {
                    location_codeValidator.showErrors(errMessage.errors);
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

jQuery('#LocationModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#LocationModal');
    thisForm.find('#submitbtn').prop('disabled', false);
    thisForm.find('#id').val('');
    jQuery('#LocationModal').find('#location_nabl_symbol_doc').val('');
    jQuery('#LocationModal').find('#location_nabl_symbol_prev').attr('href', '#');
    jQuery('#LocationModal').find('#location_nabl_symbol_prev').addClass('hide');
    jQuery('#LocationModal').find('#location_nabl_symbol_remove').removeClass('i-block').addClass('hide');
    jQuery('#LocationModal').find('#location_nabl_symbol_img-prev').html('');
    jQuery('#LocationModal').find('#location_nabl_symbol_img-prev-box').addClass('hide');
    jQuery('#LocationModal').find('#location_nabl_symbol_img-prev-box').html('');

    jQuery('#LocationModal').find('#location_ilac_symbol_doc').val('');
    jQuery('#LocationModal').find('#location_ilac_symbol_prev').attr('href', '#');
    jQuery('#LocationModal').find('#location_ilac_symbol_prev').addClass('hide');
    jQuery('#LocationModal').find('#location_ilac_symbol_remove').removeClass('i-block').addClass('hide');
    jQuery('#LocationModal').find('#location_ilac_symbol_img-prev').html('');
    jQuery('#LocationModal').find('#location_ilac_symbol_img-prev-box').addClass('hide');
    jQuery('#LocationModal').find('#location_ilac_symbol_img-prev-box').html('');
    editors['location_header_address'].setData("");
    document.getElementById("commonLocationForm").reset();
    jQuery('input[name="location_nabl_test[]"]').prop('checked', false);
    jQuery('#LocationModal').find('#show_all_location_camera').prop('checked', false);
    // thisForm.find('input, textarea, select').each(function () {
    //     jQuery(this).val('');
    //     jQuery(this).removeAttr('readonly');
    //     jQuery(this).prop('checked', false);
    // });
    jQuery('#LocationModal #location_code').prop('readonly', false).removeClass('skip-tab');
    jQuery('#LocationModal #gst_bill_location').removeClass('skip-tab');
    setSelect2Readonly('#gst_bill_location', false);
    jQuery('#LocationModal').find('#add_new').hide();
});

$('#LocationModal').on('show.bs.modal', function () {
    const input = document.getElementById('location_name');
    input?.focus();
    UlrConf();

    var formIdblank = jQuery('#LocationModal').find('#commonLocationForm').find('#id').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#LocationModal').find('#add_new').show();
    } else {
        jQuery('#LocationModal').find('#add_new').hide();
    }

    if (formIdblank == "") {
        getLNRData();
        jQuery('#LocationModal').find('#location_status').val('Active').trigger('change');
    }


});

jQuery('#LocationModal').on('click', '#add_new', function () {
    jQuery('#LocationModal').find('#id').val('');
    document.getElementById("commonLocationForm").reset();
    jQuery('input[name="location_nabl_test[]"]').prop('checked', false);
    jQuery('#LocationModal').find('#show_all_location_camera').prop('checked', false);
    const form = document.getElementById("commonLocationForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    jQuery('#LocationModal #location_code').prop('readonly', false).removeClass('skip-tab');
    jQuery('#LocationModal #gst_bill_location').removeClass('skip-tab');
    setSelect2Readonly('#gst_bill_location', false);

    jQuery('#location_name').focus();
    jQuery('#LocationModal').find("#location_type").val('').trigger('change');
    jQuery('#LocationModal').find("#gst_bill_location").val('').trigger('change');
    jQuery('#LocationModal').find("#location_country_id").val('').trigger('change');
    jQuery('#LocationModal').find("#location_state_id").val('').trigger('change.select2');
    jQuery('#LocationModal').find("#location_city_id").val('').trigger('change.select2');

    jQuery('#LocationModal').find("#location_nabl_applicable").val('').trigger('change');
    jQuery('#LocationModal').find("#nabl_id").val('').trigger('change');
    jQuery('#LocationModal').find("#location_ilac_applicable").val('').trigger('change');

    editors['location_header_address'].setData("");

    jQuery('#LocationModal').find('#location_nabl_symbol_doc').val('');
    jQuery('#LocationModal').find('#location_nabl_symbol_prev').attr('href', '#');
    jQuery('#LocationModal').find('#location_nabl_symbol_prev').addClass('hide');
    jQuery('#LocationModal').find('#location_nabl_symbol_remove').removeClass('i-block').addClass('hide');
    jQuery('#LocationModal').find('#location_nabl_symbol_img-prev').html('');
    jQuery('#LocationModal').find('#location_nabl_symbol_img-prev-box').addClass('hide');
    jQuery('#LocationModal').find('#location_nabl_symbol_img-prev-box').html('');

    jQuery('#LocationModal').find('#location_ilac_symbol_doc').val('');
    jQuery('#LocationModal').find('#location_ilac_symbol_prev').attr('href', '#');
    jQuery('#LocationModal').find('#location_ilac_symbol_prev').addClass('hide');
    jQuery('#LocationModal').find('#location_ilac_symbol_remove').removeClass('i-block').addClass('hide');
    jQuery('#LocationModal').find('#location_ilac_symbol_img-prev').html('');
    jQuery('#LocationModal').find('#location_ilac_symbol_img-prev-box').addClass('hide');
    jQuery('#LocationModal').find('#location_ilac_symbol_img-prev-box').html('');
    getLNRData();
    jQuery('#LocationModal').find('#add_new').hide();
});


jQuery(document).on('click', '#location_code_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#location_code_suggesion').val(suggest);
    var hidden = jQuery('#location_code_suggesion').val();
    var suggestion_list = jQuery('#location_code_list').html;
    jQuery('#LocationModal').find('#location_code').val(hidden)
    var location_code = hidden;
    jQuery('#location_code_list').html('');
});

// function getLocationStates() {
//     let thisForm = jQuery('#commonLocationForm');
//     let stateIdVal = thisForm.find('#location_country_id option:selected').val();
//     if (stateIdVal != "") {
//         return jQuery.ajax({
//             url: "get-location-states?country_id=" + stateIdVal,
//             type: 'GET',
//             dataType: 'json',
//             processData: false,
//             success: function (data) {
//                 if (data.response_code == 1) {
//                     let dropHtml = `<option value=''>Select State</option>`;
//                     if (!jQuery.isEmptyObject(data.states) && data.states.length > 0) {
//                         for (let idx in data.states) {
//                             dropHtml += `<option value="${data.states[idx].id}">${data.states[idx].state}</option>`;
//                         }
//                     }
//                     thisForm.find('#location_state_id').empty().append(dropHtml);
//                 }
//             }
//         });
//     } else {
//         var dropHtml = `<option value="">Select State</option>`;
//         thisForm.find('#location_state_id').empty().append(dropHtml);
//     }
// }

function getLocationStates() {
    let thisForm = jQuery('#commonLocationForm');
    let stateIdVal = thisForm.find('#location_country_id option:selected').val();
    if (stateIdVal != "") {
        return jQuery.ajax({
            url: "get-location-states?country_id=" + stateIdVal,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    let dropHtml = `<option value=''>Select State</option>`;
                    if (!jQuery.isEmptyObject(data.states)) {
                        jQuery.each(data.states, function (i, state) {
                            dropHtml += `<option value="${state.id}">${state.state}</option>`;
                        });
                    }
                    thisForm.find('#location_state_id').html(dropHtml);
                }
            }
        });
    } else {
        let dropHtml = `<option value="">Select State</option>`;
        thisForm.find('#location_state_id').html(dropHtml);
        return jQuery.Deferred().resolve();
    }
}

// jQuery('#commonLocationForm #location_country_id').on('change', function () {
//     getLocationStates();
// });

// function getLocationCity() {
//     let thisForm = jQuery('#commonLocationForm');
//     let cityVal = thisForm.find('#location_state_id option:selected').val();
//     jQuery("#state_id").val(cityVal).trigger('liszt:updated');
//     if (cityVal != "") {
//         return jQuery.ajax({
//             url: "get-city?state_id=" + cityVal,
//             type: 'GET',
//             dataType: 'json',
//             processData: false,
//             success: function (data) {
//                 if (data.response_code == 1) {
//                     let dropHtml = `<option value=''>Select City</option>`;
//                     if (!jQuery.isEmptyObject(data.cities) && data.cities.length > 0) {
//                         for (let idx in data.cities) {
//                             dropHtml += `<option value="${data.cities[idx].id}">${data.cities[idx].city}</option>`;
//                         }
//                     }
//                     thisForm.find('#location_city_id').empty().append(dropHtml);
//                 }
//             }
//         });
//     } else {
//         var dropHtml = `<option value="">Select City</option>`;
//         thisForm.find('#location_city_id').empty().append(dropHtml);
//     }
// }

// function getLocationCity() {
//     let thisForm = jQuery('#commonLocationForm');
//     let cityVal = thisForm.find('#location_state_id option:selected').val();
//     if (cityVal != "") {
//         return jQuery.ajax({
//             url: "get-city?state_id=" + cityVal,
//             type: 'GET',
//             dataType: 'json',
//             success: function (data) {
//                 if (data.response_code == 1) {
//                     let dropHtml = `<option value=''>Select City</option>`;
//                     if (!jQuery.isEmptyObject(data.cities)) {
//                         jQuery.each(data.cities, function (i, city) {
//                             dropHtml += `<option value="${city.id}">${city.city}</option>`;
//                         });
//                     }
//                     thisForm.find('#location_city_id').html(dropHtml);
//                 }
//             }
//         });
//     } else {
//         let dropHtml = `<option value="">Select City</option>`;
//         thisForm.find('#location_city_id').html(dropHtml);
//         return jQuery.Deferred().resolve();
//     }
// }

// jQuery('#commonLocationForm #location_state_id').on('change', function () {
//     getLocationCity();
// });


function validateImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.pdf)$/i;
    if (!allowedExtensions.exec(filePath)) {
        return false;
    }
    return true;
}

jQuery('#commonLocationForm #location_nabl_symbol').on('change', function (e) {
    LocationNABLsymbolfileUpload(e);
});

function LocationNABLsymbolfileUpload(e) {

    var form_data = new FormData();
    var target = e.target;
    var id = target.id;
    var files = target.files;
    var totalfiles = files.length;
    var oldImg = jQuery('#location_nabl_symbol_doc').val();
    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');
    if (totalfiles > 0) {
        var notValid = 0;
        for (var index = 0; index < totalfiles; index++) {
            if (validateImage(files[index].name) == true) {
                form_data.append("docs[]", files[index]);
            } else {
                notValid = 1;
                toastr.error("Only (jpeg,jpg,png,gif,pdf) files are allowed.");
                jQuery('#location_nabl_symbol_doc').val('');
                jQuery('#' + id).val('');
                jQuery('#location_nabl_symbol_prev').attr('href', '#').addClass('hide');
                jQuery('#location_nabl_symbol_remove').removeClass('i-block').addClass('hide');
                e.stopImmediatePropagation();
                return false;
            }
        }

        if (notValid == 0) {
            jQuery('#LocationModal').find('#submitbtn').prop('disabled', true);
            jQuery('#' + id).parent().parent().find('.uneditable-input').addClass('file-loader');
            jQuery.ajax({
                url: "upload-docs",
                type: 'POST',
                data: form_data,
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    jQuery('#LocationModal').find('#submitbtn').prop('disabled', false);
                    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    if (data.response_code == 1) {
                        if (oldImg != "" && oldImg.includes('temp_media/')) {
                            removeMedianabl(oldImg);
                        }

                        $('#location_nabl_symbol_doc').val(data.files);
                        $('#location_nabl_symbol_prev').attr('href', data.files_url);
                        $('#location_nabl_symbol_prev').removeClass('hide');
                        // $('.remove-file').removeClass('hide');
                        $('.fileupload-exists_nabl').removeClass('hide');
                        $('#location_nabl_symbol_remove').removeClass('hide');
                        // $('.remove-file').addClass('i-block').removeClass('hide');
                        $('.remove-file_nabl').addClass('i-block').removeClass('hide');
                        console.log(data.response_message);
                    } else {
                        console.log(data.response_message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#LocationModal').find('#submitbtn').prop('disabled', false);
                    $('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    $('#' + id).parent().parent().find('.uneditable-input').addClass('iconfa-warning-sign upload-error');
                    var errMessage = JSON.parse(jqXHR.responseText);
                    if (errMessage.errors) {
                        validator.showErrors(errMessage.errors);

                    } else if (jqXHR.status == 401) {
                        toastr.error(jqXHR.statusText);
                    } else {
                        toastr.error('Something went wrong!');
                        console.log(JSON.parse(jqXHR.responseText));
                    }
                }
            });
        }
    } else {
        if (oldImg != "") {
            let fullPath = oldImg;
            let fileName = fullPath.split('/').pop();
            let fileInput = jQuery('#location_nabl_symbol');
            let dataTransfer = new DataTransfer();
            dataTransfer.items.add(new File([""], fileName));
            fileInput[0].files = dataTransfer.files;
            return false;
        }

        // if (oldImg != "") {
        //     return false;
        // }

        if (oldImg != "") {
            removeMedianabl(oldImg);
        }

        $('#location_nabl_symbol_doc').val('');
        $('#location_nabl_symbol_prev').attr('href', '#');
        $('#location_nabl_symbol_prev').addClass('hide');
        $('.remove-file_nabl').addClass('hide');
        $('.fileupload-exists_nabl').addClass('hide');
        $('.remove-file_nabl').removeClass('i-block').addClass('hide');
        $('#location_nabl_symbol_remove').removeClass('hide');
    }
}




function removeFilenabl(e) {
    e.stopImmediatePropagation();
    toastDetailDelete('Are You Sure, You Want <lw-c>To</lw-c> Delete ?', () => {
        var target = e.target;
        var id = target.getAttribute("data-remove");
        var fileName = jQuery('#' + id + '_doc').val();
        var oldImg = jQuery('#location_nabl_symbol_doc').val();
        jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');

        if (oldImg != "") {
            removeMedianabl(oldImg);
        }

        jQuery('#location_nabl_symbol_doc').val('');
        jQuery('#location_nabl_symbol').val('');
        jQuery('#location_nabl_symbol_prev').attr('href', '#');
        jQuery('#location_nabl_symbol_prev').addClass('hide');
        jQuery('#location_nabl_symbol_remove').removeClass('i-block').addClass('hide');
        $('.remove-file_nabl').addClass('hide');
        $('.fileupload-exists_nabl').addClass('hide');
        jQuery('.remove-file_nabl').removeClass('i-block').addClass('hide');
        jQuery('.fileupload-preview').html('');
    });
}

function removeMedianabl(docName) {
    let form_data2 = new FormData();
    form_data2.append('docs[]', docName);
    jQuery.ajax({
        url: "remove-docs",
        type: 'POST',
        data: form_data2,
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function (data) {
            if (data.response_code == 1) {
                console.log(data.response_message);
            } else {
                console.log(data.response_message);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            var errMessage = JSON.parse(jqXHR.responseText);
            if (errMessage.errors) {
                validator.showErrors(errMessage.errors);

            } else if (jqXHR.status == 401) {
                toastr.error(jqXHR.statusText);
            } else {
                toastr.error('Something went wrong!');
                console.log(JSON.parse(jqXHR.responseText));
            }
        }
    });
}

jQuery('#commonLocationForm #location_ilac_symbol').on('change', function (e) {
    LocationILACsymbolfileUpload(e);
});

function LocationILACsymbolfileUpload(e) {

    var form_data = new FormData();
    var target = e.target;
    var id = target.id;
    var files = target.files;
    var totalfiles = files.length;
    var oldImg = jQuery('#location_ilac_symbol_doc').val();
    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');
    if (totalfiles > 0) {
        var notValid = 0;
        for (var index = 0; index < totalfiles; index++) {
            if (validateImage(files[index].name) == true) {
                form_data.append("docs[]", files[index]);
            } else {
                notValid = 1;
                toastr.error("Only (jpeg,jpg,png,gif,pdf) files are allowed.");
                jQuery('#location_ilac_symbol_doc').val('');
                jQuery('#' + id).val('');
                jQuery('#location_ilac_symbol_prev').attr('href', '#').addClass('hide');
                jQuery('#location_ilac_symbol_remove').removeClass('i-block').addClass('hide');
                e.stopImmediatePropagation();
                return false;
            }
        }

        if (notValid == 0) {
            jQuery('#LocationModal').find('#submitbtn').prop('disabled', true);
            jQuery('#' + id).parent().parent().find('.uneditable-input').addClass('file-loader');
            jQuery.ajax({
                url: "upload-docs",
                type: 'POST',
                data: form_data,
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function (data) {
                    jQuery('#LocationModal').find('#submitbtn').prop('disabled', false);
                    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    if (data.response_code == 1) {
                        if (oldImg != "" && oldImg.includes('temp_media/')) {
                            removeMediailac(oldImg);
                        }

                        $('#location_ilac_symbol_doc').val(data.files);
                        $('#location_ilac_symbol_prev').attr('href', data.files_url);
                        $('#location_ilac_symbol_prev').removeClass('hide');
                        // $('.remove-file').removeClass('hide');
                        $('.fileupload-exists_ilac').removeClass('hide');
                        $('#location_ilac_symbol_remove').removeClass('hide');
                        // $('.remove-file').addClass('i-block').removeClass('hide');
                        $('.remove-file_ilac').addClass('i-block').removeClass('hide');
                        console.log(data.response_message);
                    } else {
                        console.log(data.response_message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#LocationModal').find('#submitbtn').prop('disabled', false);
                    $('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    $('#' + id).parent().parent().find('.uneditable-input').addClass('iconfa-warning-sign upload-error');
                    var errMessage = JSON.parse(jqXHR.responseText);
                    if (errMessage.errors) {
                        validator.showErrors(errMessage.errors);

                    } else if (jqXHR.status == 401) {
                        toastr.error(jqXHR.statusText);
                    } else {
                        toastr.error('Something went wrong!');
                        console.log(JSON.parse(jqXHR.responseText));
                    }
                }
            });
        }
    } else {
        if (oldImg != "") {
            let fullPath = oldImg;
            let fileName = fullPath.split('/').pop();
            let fileInput = jQuery('#location_ilac_symbol');
            let dataTransfer = new DataTransfer();
            dataTransfer.items.add(new File([""], fileName));
            fileInput[0].files = dataTransfer.files;
            return false;
        }

        // if (oldImg != "") {
        //     return false;
        // }

        if (oldImg != "") {
            removeMediailac(oldImg);
        }

        $('#location_ilac_symbol_doc').val('');
        $('#location_ilac_symbol_prev').attr('href', '#');
        $('#location_ilac_symbol_prev').addClass('hide');
        $('.remove-file_ilac').addClass('hide');
        $('.fileupload-exists_ilac').addClass('hide');
        $('.remove-file_ilac').removeClass('i-block').addClass('hide');
        $('#location_ilac_symbol_remove').removeClass('hide');
    }
}


function removeFileilac(e) {
    e.stopImmediatePropagation();
    toastDetailDelete('Are You Sure, You Want <lw-c>To</lw-c> Delete ?', () => {
        var target = e.target;
        var id = target.getAttribute("data-remove");
        var fileName = jQuery('#' + id + '_doc').val();
        var oldImg = jQuery('#location_ilac_symbol_doc').val();
        jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');

        if (oldImg != "") {
            removeMediailac(oldImg);
        }

        jQuery('#location_ilac_symbol_doc').val('');
        jQuery('#location_ilac_symbol').val('');
        jQuery('#location_ilac_symbol_prev').attr('href', '#');
        jQuery('#location_ilac_symbol_prev').addClass('hide');
        jQuery('#location_ilac_symbol_remove').removeClass('i-block').addClass('hide');
        $('.remove-file_ilac').addClass('hide');
        $('.fileupload-exists_ilac').addClass('hide');
        jQuery('.remove-file_ilac').removeClass('i-block').addClass('hide');
        jQuery('.fileupload-preview').html('');
    });
}

function removeMediailac(docName) {
    let form_data2 = new FormData();
    form_data2.append('docs[]', docName);
    jQuery.ajax({
        url: "remove-docs",
        type: 'POST',
        data: form_data2,
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function (data) {
            if (data.response_code == 1) {
                console.log(data.response_message);
            } else {
                console.log(data.response_message);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            var errMessage = JSON.parse(jqXHR.responseText);
            if (errMessage.errors) {
                validator.showErrors(errMessage.errors);

            } else if (jqXHR.status == 401) {
                toastr.error(jqXHR.statusText);
            } else {
                toastr.error('Something went wrong!');
                console.log(JSON.parse(jqXHR.responseText));
            }
        }
    });
}

jQuery('#gst_bill_location').on('change', function () {
    GSTBillChange();
    jQuery('#location_gstin').removeClass('is-invalid');
});

function GSTBillChange() {
    let gst_bill_type = jQuery('#gst_bill_location').val();
    if (gst_bill_type == 'Yes') {
        jQuery('#gstinAsterisk').show();
        jQuery('#location_gstin').prop('required', true);
        jQuery('#location_gstin').removeClass('is-invalid');
        // jQuery('#gstinValidationMessage').show();
    } else {
        jQuery('#gstinAsterisk').hide();
        jQuery('#location_gstin').prop('required', false);
        jQuery('#location_gstin').removeClass('is-invalid');
        jQuery('#gstinValidationMessage').hide();
    }
}

// get Last Reset Data
function getLNRData() {
    jQuery.ajax({
        url: "get-location_lnr_data",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                if (data.lnr_data != null) {
                    jQuery('#location_company_name').val(data.lnr_data.location_company_name).trigger("change");
                }
            } else {
                console.log(data.response_message)
            }
        },
    });
}