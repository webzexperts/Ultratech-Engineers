// Edit nabl configuration row click
jQuery('#dyntable tbody').on('click', '.edit_nabl_configuration', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["nabl_id"]) {
        fetchAndFillNABLConfiguration(data["nabl_id"]);
    }
});

// Function to fetch and fill nabl configuration data
function fetchAndFillNABLConfiguration(id) {
    if (!id) return;
    jQuery('#NABLConfigurationModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-nabl_configuration",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.nabl_data != null) {
                jQuery('#NABLConfigurationModal').find('#nabl_location').val(data.nabl_data.nabl_location);
                jQuery('#NABLConfigurationModal').find('#tc_no').val(data.nabl_data.tc_no);
                jQuery('#NABLConfigurationModal').find('#nabl_type').val(data.nabl_data.nabl_type).trigger("change.select2");
                setSelect2Readonly('#NABLConfigurationModal #nabl_type', true);
                jQuery('#NABLConfigurationModal').find('#location_no').val(data.nabl_data.location_no);
                jQuery('#NABLConfigurationModal').find('#status').val(data.nabl_data.status).trigger("change");
                jQuery('#NABLConfigurationModal').find('#id').val(data.nabl_data.nabl_id);
                generateULRPreview();
                jQuery('#NABLConfigurationModal').find('#add_new').show();
                const form = document.getElementById("commonNABLConfigurationForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#NABLConfigurationModal').find('#nabl_location').focus();
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

// Reset button click for nabl configuration modal
jQuery('#resetbtn').on('click', function () {
    var formId = jQuery('#NABLConfigurationModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonNABLConfigurationForm").reset();
        const form = document.getElementById("commonNABLConfigurationForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        // setSelect2Readonly('#ulr_no_format', true);
        jQuery('#commonNABLConfigurationForm #nabl_location').val('').trigger("change");
        jQuery('#commonNABLConfigurationForm #tc_no').val('').trigger("change");
        jQuery('#commonNABLConfigurationForm #location_no').val('').trigger("change");
        jQuery('#NABLConfigurationModal').find('#nabl_type').val('F').trigger("change.select2");

        jQuery('#commonNABLConfigurationForm #status').val('Active').trigger("change.select2");
        jQuery('#nabl_location').focus();
        jQuery('#NABLConfigurationModal').find("#ulr_no_format").prop({ tabindex: -1, readonly: true });
        // jQuery('#commonNABLConfigurationForm #ulr_no_format').prop('readonly', true).addClass('skip-tab');
    } else {
        fetchAndFillNABLConfiguration(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_nabl_configuration', function () {
//     jQuery('#NABLConfigurationModal').modal('show');
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-nabl_configuration",
//         type: 'GET',
//         data: "id=" + data["nabl_id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 if (data.nabl_data != null) {
//                     jQuery('#NABLConfigurationModal').find('#nabl_location').val(data.nabl_data.nabl_location);
//                     jQuery('#NABLConfigurationModal').find('#tc_no').val(data.nabl_data.tc_no);
//                     jQuery('#NABLConfigurationModal').find('#nabl_type').val(data.nabl_data.nabl_type).trigger("change.select2");
//                     setSelect2Readonly('#NABLConfigurationModal #nabl_type', true);
//                     jQuery('#NABLConfigurationModal').find('#location_no').val(data.nabl_data.location_no);
//                     jQuery('#NABLConfigurationModal').find('#status').val(data.nabl_data.status).trigger("change");
//                     jQuery('#NABLConfigurationModal').find('#id').val(data.nabl_data.nabl_id);
//                     generateULRPreview();
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

$('#tc_no').on('input', function () {
    this.value = this.value.replace(/[^A-Za-z0-9]/g, '');
});


$('#commonNABLConfigurationForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#NABLConfigurationModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;

    let tcno = $('#tc_no').val().trim();
    if (tcno !== '' && !/^[A-Za-z0-9]+$/.test(tcno)) {
        $('#tc_no').addClass('is-invalid');
        $('#tc_no_error').text('Only alphanumeric characters allowed (no spaces/symbols)');
        form.classList.add('was-validated');

        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#NABLConfigurationModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#NABLConfigurationModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#NABLConfigurationModal').find('#commonNABLConfigurationForm').find('#id').val();
    var nabl_location = jQuery('#NABLConfigurationModal').find("#nabl_location").val();
    var tc_no = jQuery('#NABLConfigurationModal').find("#tc_no").val();
    var location_no = jQuery('#NABLConfigurationModal').find("#location_no").val();
    var nabl_type = jQuery('#NABLConfigurationModal').find("#nabl_type").val();
    var formUrl = formId != undefined && formId != "" ? "update-nabl_configuration" : "store-nabl_configuration";
    var LocationUrl = formId != undefined && formId != "" ? "verify-nabl_location?nabl_location=" + encodeURIComponent(nabl_location) + "&tc_no=" + encodeURIComponent(tc_no) + "&location_no=" + encodeURIComponent(location_no) + "&nabl_type=" + encodeURIComponent(nabl_type) + "&id=" + formId : "verify-nabl_location?nabl_location=" + encodeURIComponent(nabl_location) + "&tc_no=" + encodeURIComponent(tc_no) + "&location_no=" + encodeURIComponent(location_no) + "&nabl_type=" + encodeURIComponent(nabl_type);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    if (nabl_location != '' && nabl_location != undefined) {
        $.ajax({
            url: LocationUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#NABLConfigurationModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#NABLConfigurationModal').find('#submitbtn').prop('disabled', false);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonNABLConfigurationForm").reset();
                                        const form = document.getElementById("commonNABLConfigurationForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }
                                        jQuery('#commonNABLConfigurationForm #nabl_location').val('').trigger("change");
                                        jQuery('#commonNABLConfigurationForm #tc_no').val('').trigger("change");
                                        jQuery('#commonNABLConfigurationForm #location_no').val('').trigger("change");
                                        jQuery('#NABLConfigurationModal').find('#nabl_type').val('F').trigger("change.select2");

                                        jQuery('#commonNABLConfigurationForm #status').val('Active').trigger("change.select2");
                                        jQuery('#commonNABLConfigurationForm #ulr_no_format').prop({ tabindex: -1, readonly: true });
                                        jQuery('#nabl_location').focus();
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#NABLConfigurationModal').find('#submitbtn').prop('disabled', false);
                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#NABLConfigurationModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#NABLConfigurationModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#NABLConfigurationModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#NABLConfigurationModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#NABLConfigurationModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

jQuery('#NABLConfigurationModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#NABLConfigurationModal');
    thisForm.find('#id').val('');
    document.getElementById("commonNABLConfigurationForm").reset();
    jQuery('#NABLConfigurationModal').find('#nabl_type').val('F').trigger("change.select2");
    setSelect2Readonly('#NABLConfigurationModal #nabl_type', true);
    jQuery('#CountryModal').find('#add_new').hide();
});

$('#NABLConfigurationModal').on('show.bs.modal', function () {
    const input = document.getElementById('nabl_location');
    input?.focus();
    jQuery('#NABLConfigurationModal').find('#nabl_type').val('F').trigger("change.select2");
    jQuery('#NABLConfigurationModal').find('#status').val('Active').trigger("change.select2");
    setSelect2Readonly('#NABLConfigurationModal #nabl_type', true);
    generateULRPreview();

    var formIdblank = jQuery('#commonNABLConfigurationForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#NABLConfigurationModal').find('#add_new').show();
    } else {
        jQuery('#NABLConfigurationModal').find('#add_new').hide();
    }
});

jQuery('#NABLConfigurationModal').on('click', '#add_new', function () {
    jQuery('#NABLConfigurationModal').find('#id').val('');
    document.getElementById("commonNABLConfigurationForm").reset();
    const form = document.getElementById("commonNABLConfigurationForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#nabl_location').focus();
    jQuery('#NABLConfigurationModal').find('#add_new').hide();
    jQuery('#NABLConfigurationModal').find('#ulr_no_format').prop({ tabindex: -1, readonly: true });
    jQuery('#NABLConfigurationModal').find('#status').val('Active').trigger("change.select2");
});

function generateULRPreview() {
    //  form IDs
    const tcInput = $('#tc_no');              // TC No. field (e.g. TC-8418 or 22re)
    const seriesInput = $('#nabl_location');     // Series NAB No. (e.g. TC8418)
    const locationInput = $('#location_no');       // Location No. (e.g. 0, 22)
    const typeInput = $('#nabl_type');          // F or P
    const previewInput = $('#ulr_no_format');      // Preview field

    // Safety check
    if (!tcInput.length || !seriesInput.length || !locationInput.length || !typeInput.length || !previewInput.length) {
        console.warn('ULR preview: Some fields are missing in the DOM');
        previewInput.val('Auto-generated preview').removeClass('is-valid is-invalid');
        return;
    }

    let tcVal = (tcInput.val() || '').trim().toUpperCase();
    let seriesVal = (seriesInput.val() || '').trim().toUpperCase();

    if (!seriesVal || !tcVal) {
        previewInput.val('').removeClass('is-valid is-invalid');
        return;
    }

    const yearPlaceholder = 'year';          // or '20XX' / 'YYYY'
    const serialPlaceholder = '00000001';      // 

    let base = tcVal;
    if (tcVal) {
        base = tcVal;  // if tc_no and series both are avliable
    }
    const preview = `${base}<${yearPlaceholder}><${serialPlaceholder}>`;

    // const preview = `${base}-${yearPlaceholder}>${location}-${serialPlaceholder}>${type}`;

    previewInput
        .val(preview)
        .prop('readonly', true)
        .addClass('is-valid')
        .removeClass('is-invalid');
}

$('#tc_no, #nabl_location, #location_no').on('input change', generateULRPreview);
