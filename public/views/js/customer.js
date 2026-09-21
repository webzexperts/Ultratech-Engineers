var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

var contact_data = [];

// Edit customer row click
jQuery('#dyntable tbody').on('click', '.edit_customer', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillCustomer(data["id"]);
    }
});

// Function to fetch and fill customer data
function fetchAndFillCustomer(id) {
    if (!id) return;
    jQuery('#CustomerModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-customer",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.customer != null) {
                //jQuery('#CustomerModal').find('#customer_code').val(data.customer.customer_code);
                jQuery('#CustomerModal').find('#customer').val(data.customer.customer);
                jQuery('#CustomerModal').find('#city_id').val(data.customer.city_id).trigger("change.select2");
                jQuery('#CustomerModal').find('#pincode').val(data.customer.pin_code);
                jQuery('#CustomerModal').find('#state').val(data.customer.state);
                jQuery('#CustomerModal').find('#state_code').val(data.customer.state_code);
                jQuery('#CustomerModal').find('#country').val(data.customer.country_name);
                jQuery('#CustomerModal').find('#mobile_no').val(data.customer.phone_no);
                jQuery('#CustomerModal').find('#email').val(data.customer.email);
                jQuery('#CustomerModal').find('#web_address').val(data.customer.web_address);
                jQuery('#CustomerModal').find('#gstin').val(data.customer.gstin);
                jQuery('#CustomerModal').find('#pan').val(data.customer.pan);
                jQuery('#CustomerModal').find('#tan').val(data.customer.tan);
                jQuery('#CustomerModal').find('#msme_reg_no').val(data.customer.msme_reg_no);
                jQuery('#CustomerModal').find('#credit_days').val(data.customer.credit_days);
                //jQuery('#CustomerModal').find('#payment_terms').val(data.customer.payment_terms);
                jQuery('#CustomerModal').find('#address').val(data.customer.address);
                jQuery('#CustomerModal').find('#id').val(data.customer.id);

                if (data.contact.length > 0 && !jQuery.isEmptyObject(data.contact)) {
                    for (let ind in data.contact) {
                        contact_data.push(data.contact[ind]);
                    }
                    fillContactTable();
                }

                const form = document.getElementById("commonCustomerForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#CustomerModal').find('#customer').focus();
                jQuery('#CustomerModal').find('#add_new').show();
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

// Reset button click for customer modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    contact_data = [];
    jQuery('#ContactTable tbody').empty();

    var formId = jQuery('#CustomerModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonCustomerForm").reset(); 
        const form = document.getElementById("commonCustomerForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        getCustomerCode();
        jQuery('#CustomerModal').find('#customer').focus();
        jQuery('#city_id').val('').trigger('change');
    } else {
        fetchAndFillCustomer(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_customer', function () {
//     jQuery('#CustomerModal').modal('show');
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-customer",
//         type: 'GET',
//         data: "id=" + data["id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 if (data.customer != null) {
//                     jQuery('#CustomerModal').find('#customer_code').val(data.customer.customer_code);
//                     jQuery('#CustomerModal').find('#customer').val(data.customer.customer);
//                     jQuery('#CustomerModal').find('#city_id').val(data.customer.city_id).trigger("change.select2");
//                     jQuery('#CustomerModal').find('#pincode').val(data.customer.pin_code);
//                     jQuery('#CustomerModal').find('#state').val(data.customer.state);
//                     jQuery('#CustomerModal').find('#state_code').val(data.customer.state_code);
//                     jQuery('#CustomerModal').find('#country').val(data.customer.country_name);
//                     jQuery('#CustomerModal').find('#mobile_no').val(data.customer.phone_no);
//                     jQuery('#CustomerModal').find('#email').val(data.customer.email);
//                     jQuery('#CustomerModal').find('#web_address').val(data.customer.web_address);
//                     jQuery('#CustomerModal').find('#gstin').val(data.customer.gstin);
//                     jQuery('#CustomerModal').find('#pan').val(data.customer.pan);
//                     jQuery('#CustomerModal').find('#payment_terms').val(data.customer.payment_terms);
//                     jQuery('#CustomerModal').find('#address').val(data.customer.address);
//                     jQuery('#CustomerModal').find('#id').val(data.customer.id);

//                     if (data.contact.length > 0 && !jQuery.isEmptyObject(data.contact)) {
//                         for (let ind in data.contact) {
//                             contact_data.push(data.contact[ind]);
//                         }
//                         fillContactTable();
//                     }

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

// $(document).on('input', '#gstin', function () {
//     this.value = this.value.toUpperCase();
// });

// $(document).on('input', '#pan', function () {
//     this.value = this.value.toUpperCase();
// });

$('#commonCustomerForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#CustomerModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#CustomerModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    // gstin validation
    let gstin = $('#gstin').val().trim();
    if (gstin !== "") {
        if (gstin.length !== 15) {
        // if (gstin.length !== 15 || !isValidGSTIN(gstin)) {
            $('#gstin').addClass('is-invalid').removeClass('is-valid');
            toastr.error('Enter Valid GSTIN.');
            // toastr.error('Please Enter Valid GSTIN');
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#CustomerModal').find('#submitbtn').prop('disabled', false);
            return;
        } else {
            $('#gstin').removeClass('is-invalid').addClass('is-valid');
        }
    }

    // pan validation
    let pan = $('#pan').val().trim();
    if (pan !== "") {
        if (pan.length !== 10 || !isValidPAN(pan)) {
            $('#pan').addClass('is-invalid').removeClass('is-valid');
            toastr.error('Enter Valid PAN.');
            // toastr.error('Please Enter Valid PAN');
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#CustomerModal').find('#submitbtn').prop('disabled', false);
            return;
        } else {
            $('#pan').removeClass('is-invalid').addClass('is-valid');
        }
    }

    // phone validation
    let phoneField = $('#mobile_no');
    let phoneVal = phoneField.val().trim();
    if (phoneVal !== "") {
        if (phoneVal.length > 12) {
            phoneField.addClass('is-invalid').removeClass('is-valid');
            toastr.error('Maximum 12 Digits Are Allowed For Phone No.');
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#CustomerModal').find('#submitbtn').prop('disabled', false);
            e.preventDefault();
            return false;
        }
    }

    var formId = jQuery('#CustomerModal').find('#commonCustomerForm').find('#id').val();
    var customer = jQuery('#CustomerModal').find("#customer").val();
    var CustomerUrl = formId != undefined && formId != "" ? "verify-customer?customer=" + encodeURIComponent(customer) + "&id=" + formId : "verify-customer?customer=" + encodeURIComponent(customer);
    var formUrl = formId != undefined && formId != "" ? "update-customer" : "store-customer";

    var formData = new FormData(form);
    formData.append('contacts', JSON.stringify(contact_data));
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    if (customer != '' && customer != undefined) {
        $.ajax({
            url: CustomerUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#CustomerModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#CustomerModal').find('#submitbtn').prop('disabled', false);
                                    addedCustomer(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonCustomerForm").reset();
                                        const form = document.getElementById("commonCustomerForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }
                                        getCustomerCode();
                                        jQuery('#customer').focus();
                                        jQuery('#city_id').val('').trigger('change');
                                        contact_data = [];
                                        jQuery('#ContactTable tbody').empty();

                                        if (partAfterManage != 'customer') {
                                            jQuery('#CustomerModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#CustomerModal').find('#submitbtn').prop('disabled', false);
                                    addedCustomer(true);
                                }
                                else {
                                    toastr.error(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#CustomerModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#CustomerModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#CustomerModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#CustomerModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#CustomerModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function fillContactTable() {
    if (contact_data.length > 0) {
        for (let key in contact_data) {
            let formIndx = contact_data.indexOf(contact_data[key]);
            var contact_person = contact_data[key].contact_person ? contact_data[key].contact_person : "";
            var contact_designation = contact_data[key].contact_designation ? contact_data[key].contact_designation : "";
            //var contact_mobile_no = contact_data[key].contact_mobile_no ? contact_data[key].contact_mobile_no : "";
            var contact_phone_no = contact_data[key].contact_phone_no ? contact_data[key].contact_phone_no : "";
            var contact_email = contact_data[key].contact_email ? contact_data[key].contact_email : "";
            var send_email_for = contact_data[key].send_email_for ? contact_data[key].send_email_for : "0";
            var in_use = contact_data[key].in_use ? contact_data[key].in_use : "";

            if (jQuery('#ContactTable tbody').find('#noContact').length > 0) {
                jQuery('#ContactTable tbody').empty();
            }

            let tblHtml = `<tr>`;
            tblHtml += `<td>`;
            tblHtml += in_use == true ? DetailsActionDropdown('editContactDetails') : DetailsActionDropdown('editContactDetails', 'removeContactDetails');
            tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;
            // tblHtml += `<td>
            //     <div>
            //         <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
            //             <i class="ri-more-2-fill"></i>
            //         </a>
            //         <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
            //             <li><a class="dropdown-item edit-item-btn" onclick="editContactDetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i>Edit</a></li> `;
            //             if(in_use == true)
            //             {

            //                 tblHtml += ` <li><a class="dropdown-item remove-item-btn" > <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li> `;
            //             }else{
            //                    tblHtml += ` <li><a class="dropdown-item remove-item-btn" onclick = "removeContactDetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li> `;
            //             }

            //       tblHtml += `    </ul>
            //     </div>
            //     <input type="hidden" name="form_indx" value="${formIndx}"/>
            // </td>`;
            tblHtml += `<td>${contact_person}<input type='hidden' name='contact_person[]' value="${contact_person}"/></td>`;
            tblHtml += `<td>${contact_designation}<input type='hidden' name='contact_designation[]' value="${contact_designation}"/></td>`;
            tblHtml += `<td>${contact_phone_no}<input type='hidden' name='contact_phone_no[]' value="${contact_phone_no}"/></td>`;
            // tblHtml += `<td>${contact_mobile_no}<input type='hidden' name='contact_mobile_no[]' value="${contact_mobile_no}"/></td>`;
            tblHtml += `<td>${contact_email}<input type='hidden' name='contact_email[]' value="${contact_email}"/>
            <input type='hidden' name='send_email_for' value="${send_email_for}"/>
            </td>`;
            tblHtml += `</tr>`;
            jQuery('#ContactTable tbody').append(tblHtml);
        }
    }
}

$('#contact_form').on('submit', function (e) {
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    // phone validation
    let phoneField = $('#contact_phone_no');
    let phoneVal = phoneField.val().trim();
    if (phoneVal !== "") {
        if (phoneVal.length > 12) {
            phoneField.addClass('is-invalid').removeClass('is-valid');
            toastr.error('Maximum 12 Digits Are Allowed For Phone No.');
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#ContactModal').find('#submitbtn').prop('disabled', false);
            e.preventDefault();
            return false;
        }
    }

    // mobile validation
    // let mobileField = $('#contact_mobile_no');
    // let mobileVal = mobileField.val().trim();
    // if (mobileVal !== "") {
    //     if (mobileVal.length > 10) {
    //         mobileField.addClass('is-invalid').removeClass('is-valid');
    //         toastr.error('Maximum 10 Digits Are Allowed For Mobile No.');
    //         jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
    //         jQuery('#ContactModal').find('#submitbtn').prop('disabled', false);
    //         e.preventDefault();
    //         return false;
    //     }
    // }
    let emailField = $('#contact_email');
    let emailVal = emailField.val().trim();
    let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    let reportChecked = $('#ContactModal').find('#send_email_for_report').is(':checked');
    let invoiceChecked = $('#ContactModal').find('#send_email_for_invoice').is(':checked');

    if (reportChecked || invoiceChecked) {

        if (emailVal === "") {
            emailField.addClass('is-invalid').removeClass('is-valid');
            toastr.error('Enter Contact Email.');
            // toastr.error('Please Enter Contact Email');
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#ContactModal').find('#submitbtn').prop('disabled', false);
            return false;
        }

        if (!emailPattern.test(emailVal) && emailVal !== "") {
            emailField.addClass('is-invalid').removeClass('is-valid');
            toastr.error('Enter Valid Email ID.');
            // toastr.error('Please Enter Valid Email ID');
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#ContactModal').find('#submitbtn').prop('disabled', false);
            return false;
        }
    }
    var send_email_for = [];
    if (jQuery('#ContactModal').find("#send_email_for_report").is(":checked")) {
        send_email_for.push(jQuery('#ContactModal').find("#send_email_for_report").val());
    }

    if (jQuery('#ContactModal').find("#send_email_for_invoice").is(":checked")) {
        send_email_for.push(jQuery('#ContactModal').find("#send_email_for_invoice").val());
    }

    var data = new FormData(document.getElementById('contact_form'));
    data.append('send_email_for', send_email_for);
    var formValue = Object.fromEntries(data.entries());
    var thisModal = jQuery('#ContactModal');
    var noDuplicate = true;

    if (formValue.form_type == "edit") {
        jQuery('#contactTable tbody input[name*="contact_person[]"]').each(function (indx) {
            if (formValue.contact_person == jQuery(this).val() && formValue.row_index != jQuery(this).closest('tr').index()) {
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                noDuplicate = false;
                return;
            }
        });
    } else {
        jQuery('#contactTable tbody input[name*="contact_person[]"]').each(function (indx) {
            if (formValue.contact_person == jQuery(this).val()) {
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                noDuplicate = false;
                return;
            }
        });
    }

    if (noDuplicate) {
        thisModal.find('#contact_person').closest('div.control-group').removeClass('error');
        var contact_person = formValue.contact_person ? formValue.contact_person : "";
        var contact_designation = formValue.contact_designation ? formValue.contact_designation : "";
        var contact_phone_no = formValue.contact_phone_no ? formValue.contact_phone_no : "";
        //var contact_mobile_no = formValue.contact_mobile_no ? formValue.contact_mobile_no : "";
        var contact_email = formValue.contact_email ? formValue.contact_email : "";
        var send_email_for = formValue.send_email_for ? formValue.send_email_for : "0";

        if (formValue.form_type == "edit") {
            contact_data[formValue.form_index] = formValue;
            let tblHtml = ``;
            tblHtml += `<td>`;
            tblHtml += DetailsActionDropdown('editContactDetails', 'removeContactDetails');
            tblHtml += `<input type="hidden" name="form_indx" value="${formValue.form_index}"/>
            </td>`;
            // tblHtml += `<td>
            // <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
            //     <i class="ri-more-2-fill"></i>
            // </a>
            // <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
            //     <li><a class="dropdown-item edit-item-btn" onclick="editContactDetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_customer"></i>Edit</a></li>
            //     <li><a class="dropdown-item remove-item-btn" onclick="removeContactDetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li>
            // </ul>
            // <input type="hidden" name="form_indx" value="${formValue.form_index}"/>
            // </td>`;
            tblHtml += `<td>${contact_person}<input type='hidden' name='contact_person[]' value="${contact_person}"/></td>`;
            tblHtml += `<td>${contact_designation}<input type='hidden' name='contact_designation[]' value="${contact_designation}"/></td>`;
            tblHtml += `<td>${contact_phone_no}<input type='hidden' name='contact_phone_no[]' value="${contact_phone_no}"/></td>`;
            // tblHtml += `<td>${contact_mobile_no}<input type='hidden' name='contact_mobile_no[]' value="${contact_mobile_no}"/></td>`;
            tblHtml += `<td>${contact_email}<input type='hidden' name='contact_email[]' value="${contact_email}"/>
            <input type='hidden' name='send_email_for' id="send_email_for_report" value="${send_email_for}"/>
            <input type='hidden' name='send_email_for' id="send_email_for_invoice" value="${send_email_for}"/>
            </td>`;
            jQuery('#ContactTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
            toastSuccess("Record Inserted.");
        } else {
            contact_data.push(formValue)
            let formIndx = contact_data.indexOf(formValue);
            if (jQuery('#ContactTable tbody').find('#noContact').length > 0) {
                jQuery('#ContactTable tbody').empty();
            }

            let tblHtml = `<tr>`;
            tblHtml += `<td>`;
            tblHtml += DetailsActionDropdown('editContactDetails', 'removeContactDetails');
            tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;
            // tblHtml += `<td>
            // <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
            //     <i class="ri-more-2-fill"></i>
            // </a>
            // <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
            //     <li><a class="dropdown-item edit-item-btn" onclick="editContactDetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_customer"></i>Edit</a></li>
            //     <li><a class="dropdown-item remove-item-btn" onclick="removeContactDetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li>
            // </ul>
            // <input type="hidden" name="form_indx" value="${formIndx}"/>
            // </td>`;
            tblHtml += `<td>${contact_person}<input type='hidden' name='contact_person[]' value="${contact_person}"/></td>`;
            tblHtml += `<td>${contact_designation}<input type='hidden' name='contact_designation[]' value="${contact_designation}"/></td>`;
            tblHtml += `<td>${contact_phone_no}<input type='hidden' name='contact_phone_no[]' value="${contact_phone_no}"/></td>`;
            // tblHtml += `<td>${contact_mobile_no}<input type='hidden' name='contact_mobile_no[]' value="${contact_mobile_no}"/></td>`;
            tblHtml += `<td>${contact_email}<input type='hidden' name='contact_email[]' value="${contact_email}"/>
            <input type='hidden' name='send_email_for' id="send_email_for_report" value="${send_email_for}"/>
            <input type='hidden' name='send_email_for' id="send_email_for_invoice" value="${send_email_for}"/>
            </td>`;
            tblHtml += `</tr>`;
            jQuery('#ContactTable tbody').append(tblHtml);
            toastSuccess("Record Updated.");
        }

        if (formValue.form_type == "edit") {
            thisModal.modal('hide');
        } else {
            let formElement = document.getElementById('contact_form');
            formElement.reset();
            jQuery('#contact_person').val('');
            jQuery('#contact_designation').val('');
            jQuery('#contact_designation').val('');
            jQuery('#contact_phone_no').val('');
            jQuery('#contact_email').val('');
            // jQuery('#send_email_for_report').val('');
            // jQuery('#send_email_for_invoice').val('');
            jQuery('#send_email_for_report').prop('checked', false);
            jQuery('#send_email_for_invoice').prop('checked', false);
            setTimeout(function () {
                $(formElement).removeClass('was-validated');
                $(formElement).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                thisModal.find('.error').removeClass('error');
                jQuery('#contact_person').focus();
            }, 150);
        }
    } else {
        thisModal.find('#contact_person').closest('div.control-group').addClass('error').focus();
        toastr.error("Duplicate Contact Person Found.");
        // toastr.error("Contact person is already exists");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
    }
});

function editContactDetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();
    fillContactForm(formIndx, rawIndx);
}

function fillContactForm(formIndx, rawIndx) {
    let thisForm = jQuery('#ContactModal');
    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    var frmData = contact_data[formIndx];
    thisForm.find("#contact_person").val(frmData.contact_person);
    thisForm.find("#contact_designation").val(frmData.contact_designation);
    thisForm.find("#contact_phone_no").val(frmData.contact_phone_no);
    // thisForm.find("#contact_mobile_no").val(frmData.contact_mobile_no);
    thisForm.find("#contact_email").val(frmData.contact_email);

    if (frmData.send_email_for && frmData.send_email_for.length > 0) {
        if (typeof frmData.send_email_for == "object") {
            frmData.send_email_for.forEach(function (value) {
                thisForm.find('#send_email_for_' + value).prop('checked', true);
            });
        } else {
            var emailForValues = frmData.send_email_for.split(',');
            var emailForObject = emailForValues.map(function (value) {
                return { value: value.trim() };
            });
            emailForObject.forEach(function (obj) {
                thisForm.find('#send_email_for_' + obj.value).prop('checked', true);
            });
        }
    }

    thisForm.modal('show');
}

function removeContactDetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        removeFormObjContactDetail(formIndx);
        jQuery(th).closest("tr").remove();
    });
}

function removeFormObjContactDetail(formIndx) {
    delete contact_data[formIndx];
    contact_data = contact_data.filter(element => element != null);
    jQuery('#ContactTable tbody').empty();
    fillContactTable()
}

jQuery('#CustomerModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#CustomerModal');
    thisForm.find('#id').val('');
    jQuery('#city_id').change();
    jQuery('#CustomerModal').find('#add_new').hide();
    // jQuery('#city_id').val('').trigger('change');
    document.getElementById("commonCustomerForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        // jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });

    contact_data = [];
    jQuery('#ContactTable tbody').empty();
});

$('#CustomerModal').on('shown.bs.modal', function () {
    jQuery('#city_id').change();
    const input = document.getElementById('customer');
    input?.focus();

    var formId = jQuery('#CustomerModal').find('#commonCustomerForm').find('#id').val();
    if (formId == '' || formId == undefined) {
        setTimeout(function () {
            getCustomerCode();
        }, 300);
        jQuery('#CustomerModal').find('#add_new').hide();
    }else{
        jQuery('#CustomerModal').find('#add_new').show();

    }
});

jQuery('#CustomerModal').on('click', '#add_new', function () {
    jQuery('#CustomerModal').find('#id').val('');
    jQuery("#contact_form #pod_pid_id").val(0).trigger('change.select2');
    jQuery("#contact_form #pod_id").val(0).trigger('change.select2');
    document.getElementById("commonCustomerForm").reset();
    const form = document.getElementById("commonCustomerForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#customer').focus();
    jQuery('#ContactTable tbody').empty();
    jQuery('#ContactTable tbody').append(`
        <tr>
            <td colspan="5" id="noDetails">
                No Contact Details Added
            </td>
        </tr>
    `);
    contact_data = [];

    jQuery('#CustomerModal').find("#city_id").val('').trigger('change');
    jQuery('#CustomerModal').find('#add_new').hide();
    jQuery('#CustomerModal').find('#preview_btn').hide();
});

jQuery('#ContactModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#ContactModal');
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    jQuery('#contact_form').trigger("reset");
});

$('#ContactModal').on('shown.bs.modal', function () {
    const input = document.getElementById('contact_person');
    input?.focus();
});

function suggestCustomer(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "customer-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#customer").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#customer_list').html(data.customerList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#customer").removeClass('file-loader');
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

jQuery(document).on('click', '#customer_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#customer_suggesion').val(suggest);
    var hidden = jQuery('#customer_suggesion').val();
    var suggestion_list = jQuery('#customer_list').html;
    jQuery('#CustomerModal').find('#customer').val(hidden)
    var customer = hidden;

    if (suggestion_list != '') {
        checkCustomerName(customer);
    }
    jQuery('#customer_list').html('');
});

function checkCustomerName(customer) {
    var id = jQuery('#CustomerModal').find('#commonCustomerForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-customer?customer=" + encodeURIComponent(customer) + "&id=" + id : "verify-customer?customer=" + encodeURIComponent(customer);
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

function verifyCustomer() {
    var CustomerName = jQuery('#customer').val();
    var suggestion_list = jQuery('#customer_list').html;

    if (suggestion_list != '') {
        checkCustomerName(CustomerName);
    }
}

function checkCustomerCode(customer_code) {
    var id = jQuery('#CustomerModal').find('#commonCustomerForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-customer_code?customer_code=" + encodeURIComponent(customer_code) + "&id=" + id : "verify-customer_code?customer_code=" + encodeURIComponent(customer_code);
    jQuery.ajax({
        url: formUrl,
        type: 'GET',
        dataType: 'json',
        processData: false,
        headers: headerOpt,
        success: function (data) {
            if (data.response_code == 1) {
                toastr.error(data.response_message, "#customer_code");
            }
        }
    });
}

function getCustomerCode() {
    var formUrl = "get-customer_code";
    jQuery.ajax({
        url: formUrl,
        type: 'GET',
        dataType: 'json',
        processData: false,
        headers: headerOpt,
        success: function (data) {
            if (data.response_code == 1) {
                jQuery("#customer_code").val(data.customer_code);
            }
        }
    });
}

function suggestPaymentTerms(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "payment_terms-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#payment_terms").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#payment_terms_list').html(data.paymentTermsList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#payment_terms").removeClass('file-loader');
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

function suggestDesignation(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "designation-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#contact_designation").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#contact_designation_list').html(data.designationList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#contact_designation").removeClass('file-loader');
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

$('#StateModal').on('shown.bs.modal', function () {
    jQuery("#commonStateForm").find("#state_code").prop('disabled', true);
    jQuery("#country_id").change(function () {
        let country = jQuery("#commonStateForm").find("#country_id").val();
        if (country == 1) {
            jQuery("#commonStateForm").find("#state_code").prop('disabled', false);
        } else {
            jQuery("#commonStateForm").find("#state_code").prop('disabled', true);
        }
    });
});

$('#CityModal').on('shown.bs.modal', function () {
    jQuery("#commonCityForm").find("#state_code").val('');
    let state = jQuery("#state_code").val();
    let country = jQuery("#location_country_id").val();
    jQuery("#state_id").val(state).trigger('liszt:updated');
    if (country != '') {
        jQuery('#commonCityForm #country_name').val(jQuery('#location_country_id option:selected').text());
    }
});

function getCustomer($this = null) {
    var formUrl = "get-customers";
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
                    var stgDrpHtml = `<option value="">Select Customer</option>`;
                    for (let indx in data.customers) {
                        stgDrpHtml += `<option value="${data.customers[indx].id}">${data.customers[indx].customer}</option>`;
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

function addedCustomer($event) {
    if ($event == true) {
        getCustomer(".suggest_customer_name");
    }
}

jQuery('#CityModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('city_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});
