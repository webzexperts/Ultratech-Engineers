// getCompanyYear();

jQuery('#resetbtn').on('click', function () {
    var formId = jQuery('#CompanyYearModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonCompanyYearForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
        jQuery('#type').change();
        setTimeout(function () {
            let sel = jQuery('#type')
                .next('.select2-container')
                .find('.select2-selection');
            sel.attr('tabindex', 0).focus();
        }, 20);
        getCompanyYear();
    }
});

jQuery(document).ready(function () {
    getCompanyYear();
    jQuery('#type').on('change', function () {
        getCompanyYear();
    });
});

function getCompanyYear() {

    let sel = jQuery("#type").find('option:selected').val();
    jQuery('#startdate').addClass('file-loader');
    jQuery('#enddate').addClass('file-loader');
    jQuery.ajax({
        url: "get-company_year?type=" + sel,
        type: 'GET',
        dataType: 'json',
        processData: false,
        success: function (data) {
            jQuery('#startdate').removeClass('file-loader');
            jQuery('#enddate').removeClass('file-loader');
            if (data.response_code == 1) {
                jQuery('#yearcode').val(data.response_data.year_code);
                jQuery('#year').val(data.response_data.year);
                jQuery('#startdate').val(data.response_data.startdate);
                jQuery('#enddate').val(data.response_data.enddate);

            } else {

                 toastr.error(data.response_message);
            }
        },

        error: function (jqXHR, textStatus, errorThrown) {
            jQuery("#order_unit").removeClass('file-loader');
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


$('#commonCompanyYearForm').on('submit', function (e) {
    e.preventDefault();

    let form = this; // 'this' refers to the form element in the event handler

    // Bootstrap 5 validation check
    if (!form.checkValidity()) { // 'form' is now an actual DOM element
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }


    jQuery('#authModal').modal('show');

});
function resetSubmitState() {
    jQuery('#authModal').find('#submitbtn').prop('disabled', false);
    jQuery('#CompanyYearModal').find('#submitbtn').prop('disabled', false);
}

$('#login').on('submit', function (e) {
    e.preventDefault();

    let form = this; // 'this' refers to the form element in the event handler

    // Bootstrap 5 validation check
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    jQuery('#authModal').find('#submitbtn').prop('disabled', true);
    jQuery('#CompanyYearModal').find('#submitbtn').prop('disabled', true);

    var formUrl = "check-login";
    let formData = new FormData(form);

    $.ajax({
        type: 'POST',
        url: formUrl,
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (data) {

            if (data.response_code == 1) {

                toastSuccess(data.response_message);

                // FIXED: Correct way to hide modal
                $('#authModal').modal('hide');

                // FIXED: Correct form ID
                var formdatacompany = $('#commonCompanyYearForm').serialize();

                var storeUrl = "store-company_year";

                $.ajax({
                    url: storeUrl,
                    type: 'POST',
                    data: formdatacompany,
                    // FIXED: removed processData & contentType for serialized data
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data) {
                        if (data.response_code == 1) {
                            toastSuccess(data.response_message, redirectFn);
                            function redirectFn() {
                                window.location.reload();
                            }
                        } else {
                            toastr.error(data.response_message);
                            resetSubmitState();
                        }
                    },

                    error: function (xhr) {
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
                        resetSubmitState();
                    }

                });

            } else {
                toastr.error(data.response_message);
                resetSubmitState();
            }
        },
        error: function (xhr) {
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
            resetSubmitState();
        }
    });

});

jQuery('#CompanyYearModal').on('shown.bs.modal', function () {
    setTimeout(function () {
        // Focus the select2 selection box
        let sel = jQuery('#type')
            .next('.select2-container')
            .find('.select2-selection');
 
        sel.attr('tabindex', 0).focus();
    }, 20);
    jQuery('#type').change();
    jQuery('#CompanyYearModal').find('#type').val('forward').trigger('change');
});

$('#authModal').on('shown.bs.modal', function () {
    const input = document.getElementById('password');
    input?.focus();
});