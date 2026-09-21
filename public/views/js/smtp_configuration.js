// Edit SMTP Configuration row click
jQuery('#dyntable tbody').on('click', '.edit_smtp_configuration', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["sc_id"]) {
        jQuery('#SMTPConfigurationModal').find('#id').val(data["sc_id"]);
        fetchAndFillSMTPConfiguration(data["sc_id"]);
    }
});

// Delete SMTP Configuration row click
jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["sc_id"]) {
        toastDelete("Do you want to delete this record?", function () {
            jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
            jQuery.ajax({
                url: "destroy-smtp_configuration",
                type: 'POST',
                data: { id: data["sc_id"] },
                headers: headerOpt,
                dataType: 'json',
                success: function (data) {
                    if (data.response_code == 1) {
                        toastSuccess(data.response_message, function() {
                            window.location.reload();
                        });
                    } else {
                        toastr.error(data.response_message);
                    }
                },
                error: function () {
                    toastr.error('Something went wrong!');
                },
                complete: function () {
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                }
            });
        });
    }
});

// Function to fetch and fill SMTP Configuration data
function fetchAndFillSMTPConfiguration(id) {
    if (!id) return;
    jQuery('#SMTPConfigurationModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-smtp_configuration",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.smtp_data != null) {
                var modal = jQuery('#SMTPConfigurationModal');
                modal.find('#id').val(data.smtp_data.sc_id);
                modal.find('#email').val(data.smtp_data.email);
                modal.find('#cc_email').val(data.smtp_data.cc_email || '');
                modal.find('#password').val(data.smtp_data.password); // Fill decrypted password
                modal.find('#password').attr('type', 'password');
                jQuery('#togglePassword').removeClass('ri-eye-off-fill').addClass('ri-eye-fill');
                modal.find('#password').attr('required', 'required'); // Password required on edit
                modal.find('#pwd_astric').show(); // Show required asterisk for password
                modal.find('#mail_host').val(data.smtp_data.mail_host);
                modal.find('#out_port_no').val(data.smtp_data.out_port_no);
                modal.find('#enable_ssl').prop('checked', data.smtp_data.enable_ssl == 1);
                modal.find('#reply_email').val(data.smtp_data.reply_email || '');
                modal.find('#purchase').prop('checked', data.smtp_data.purchase == 1);

                const form = document.getElementById("commonSMTPConfigurationForm");
                if (form) form.classList.remove('was-validated');
                modal.find('#add_new').show();
                modal.find('#email').focus();
            } else {
                console.log(data.response_message);
            }
        },
        error: function () {
            toastr.error('Something went wrong!');
        },
        complete: function () {
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        }
    });
}

// Reset button click for SMTP Configuration modal
jQuery('#SMTPConfigurationModal').on('click', '#resetbtn', function () {
    var formId = jQuery('#SMTPConfigurationModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonSMTPConfigurationForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
        jQuery('#SMTPConfigurationModal').find('#password').attr('required', 'required');
        jQuery('#SMTPConfigurationModal').find('#pwd_astric').show();
        jQuery('#SMTPConfigurationModal').find('#password').attr('type', 'password');
        jQuery('#togglePassword').removeClass('ri-eye-off-fill').addClass('ri-eye-fill');
        jQuery('#email').focus();
    } else {
        fetchAndFillSMTPConfiguration(formId);
    }
});

// Handle Add/New reset states when opening modal via standard Add button
jQuery('[data-bs-target="#SMTPConfigurationModal"]').on('click', function() {
    var modal = jQuery('#SMTPConfigurationModal');
    modal.find('#id').val('');
    var form = document.getElementById("commonSMTPConfigurationForm");
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
    }
    modal.find('#password').attr('required', 'required');
    modal.find('#pwd_astric').show();
    modal.find('#password').attr('type', 'password');
    jQuery('#togglePassword').removeClass('ri-eye-off-fill').addClass('ri-eye-fill');
    modal.find('#add_new').hide();
});

// Form Submission handling
jQuery('#commonSMTPConfigurationForm').on('submit', function (e) {
    e.preventDefault();
    var form = this;

    // Trim and clear values to avoid space issues
    var emailInput = jQuery('#email');
    emailInput.val(emailInput.val().trim());

    var ccEmailInput = jQuery('#cc_email');
    if (ccEmailInput.val()) {
        ccEmailInput.val(ccEmailInput.val().trim());
    }
    if (ccEmailInput.val() === '') {
        ccEmailInput.removeClass('is-invalid');
        ccEmailInput.siblings('.invalid-tooltip').hide();
    }

    var replyEmailInput = jQuery('#reply_email');
    if (replyEmailInput.val()) {
        replyEmailInput.val(replyEmailInput.val().trim());
    }
    if (replyEmailInput.val() === '') {
        replyEmailInput.removeClass('is-invalid');
        replyEmailInput.siblings('.invalid-tooltip').hide();
    }

    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return false;
    }

    jQuery('#SMTPConfigurationModal').find('#submitbtn').prop('disabled', true);
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');

    var formId = jQuery('#SMTPConfigurationModal').find('#id').val();
    var formUrl = formId != undefined && formId != "" ? "update-smtp_configuration" : "store-smtp_configuration";

    var formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    $.ajax({
        type: 'POST',
        url: formUrl,
        data: formData,
        contentType: false,
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                function redirectFn() {
                    window.location.reload();
                }
                toastSuccess(data.response_message, redirectFn);
            } else {
                toastr.error(data.response_message);
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
        },
        complete: function() {
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#SMTPConfigurationModal').find('#submitbtn').prop('disabled', false);
        }
    });
});

// Toggle password visibility on eye click
jQuery('#SMTPConfigurationModal').on('click', '#togglePassword', function () {
    var passwordField = jQuery('#password');
    var passwordIcon = jQuery(this);
    if (passwordField.attr('type') === 'password') {
        passwordField.attr('type', 'text');
        passwordIcon.removeClass('ri-eye-fill').addClass('ri-eye-off-fill');
    } else {
        passwordField.attr('type', 'password');
        passwordIcon.removeClass('ri-eye-off-fill').addClass('ri-eye-fill');
    }
});

// Add New button click for SMTP Configuration modal (resets from Edit mode to Add mode)
jQuery('#SMTPConfigurationModal').on('click', '#add_new', function () {
    var modal = jQuery('#SMTPConfigurationModal');
    modal.find('#id').val('');
    var form = document.getElementById("commonSMTPConfigurationForm");
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
    }
    modal.find('#password').attr('required', 'required');
    modal.find('#pwd_astric').show();
    modal.find('#password').attr('type', 'password');
    jQuery('#togglePassword').removeClass('ri-eye-off-fill').addClass('ri-eye-fill');
    modal.find('#add_new').hide();
    modal.find('#email').focus();
});
