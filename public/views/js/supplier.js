var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];
var contact_data = [];

// Edit supplier row click
jQuery('#dyntable tbody').on('click', '.edit_supplier', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillSupplier(data["id"]);
    }
});

// Function to fetch and fill supplier data
function fetchAndFillSupplier(id) {
    if (!id) return;
    jQuery('#SupplierModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-supplier",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.supplier != null) {
                jQuery('#SupplierModal').find('#supplier_name').val(data.supplier.supplier_name);
                jQuery('#SupplierModal').find('#address').val(data.supplier.address);
                jQuery('#SupplierModal').find('#city_id').val(data.supplier.city_id).trigger("change.select2");
                jQuery('#SupplierModal').find('#pincode').val(data.supplier.pin_code);
                jQuery('#SupplierModal').find('#state').val(data.supplier.state);
                // jQuery('#SupplierModal').find('#state_code').val(data.supplier.state_code);
                jQuery('#SupplierModal').find('#country').val(data.supplier.country_name);
                jQuery('#SupplierModal').find('#pincode').val(data.supplier.pincode);
                // jQuery('#SupplierModal').find('#contact_person').val(data.supplier.contact_person);
                jQuery('#SupplierModal').find('#phone_no').val(data.supplier.phone_no);
                jQuery('#SupplierModal').find('#email_id').val(data.supplier.email_id);
                jQuery('#SupplierModal').find('#web_address').val(data.supplier.web_address);
                jQuery('#SupplierModal').find('#payment_terms').val(data.supplier.payment_terms);
                jQuery('#SupplierModal').find('#GSTIN').val(data.supplier.GSTIN);
                jQuery('#SupplierModal').find('#PAN').val(data.supplier.PAN);
                jQuery('#SupplierModal').find('#TAN').val(data.supplier.TAN);
                jQuery('#SupplierModal').find('#msme_reg_no').val(data.supplier.msme_reg_no);
                jQuery('#SupplierModal').find('#id').val(data.supplier.id);
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');

                getCustomerCityRelationData();
                if (data.contact_data.length > 0 && !jQuery.isEmptyObject(data.contact_data)) {
                    for (let ind in data.contact_data) {
                        contact_data.push(data.contact_data[ind]);
                    }
                    fillContactTable();
                }
                jQuery('#SupplierModal').find('#add_new').show();

                const form = document.getElementById("commonSupplierForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#SupplierModal').find('#supplier_name').focus();
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

// Reset button click for supplier modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    contact_data = [];
    jQuery('#SupplierDetailTable tbody').empty();

    var formId = jQuery('#SupplierModal').find('#id').val();
    contact_data = [];
    jQuery('#SupplierDetailTable tbody').empty();
    if (!formId) {
        document.getElementById("commonSupplierForm").reset();
        const form = document.getElementById("commonSupplierForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        // jQuery('#SupplierDetailTable tbody').empty();
        jQuery('#SupplierModal').find('#supplier_name').focus();
        jQuery('#SupplierModal').find('#city_id').val('').trigger('change.select2');
    } else {
        fetchAndFillSupplier(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_supplier', function () {
//     jQuery('#SupplierModal').modal('show');
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-supplier",
//         type: 'GET',
//         data: "id=" + data["id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 if (data.supplier != null) {
//                     jQuery('#SupplierModal').find('#supplier_name').val(data.supplier.supplier_name);
//                     jQuery('#SupplierModal').find('#address').val(data.supplier.address);
//                     jQuery('#SupplierModal').find('#city_id').val(data.supplier.city_id).trigger("change.select2");
//                     jQuery('#SupplierModal').find('#pincode').val(data.supplier.pin_code);
//                     jQuery('#SupplierModal').find('#state').val(data.supplier.state);
//                     jQuery('#SupplierModal').find('#state_code').val(data.supplier.state_code);
//                     jQuery('#SupplierModal').find('#country').val(data.supplier.country_name);
//                     jQuery('#SupplierModal').find('#pincode').val(data.supplier.pincode);
//                     jQuery('#SupplierModal').find('#contact_person').val(data.supplier.contact_person);
//                     jQuery('#SupplierModal').find('#contact_person_mobile').val(data.supplier.contact_person_mobile);
//                     jQuery('#SupplierModal').find('#contact_person_email_id').val(data.supplier.contact_person_email_id);
//                     jQuery('#SupplierModal').find('#web_address').val(data.supplier.web_address);
//                     jQuery('#SupplierModal').find('#payment_terms').val(data.supplier.payment_terms);
//                     jQuery('#SupplierModal').find('#GSTIN').val(data.supplier.GSTIN);
//                     jQuery('#SupplierModal').find('#pan').val(data.supplier.PAN);
//                     jQuery('#SupplierModal').find('#id').val(data.supplier.id);
//                     jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');

//                     getCustomerCityRelationData();
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

// $(document).on('input', '#pan', function () {
//     this.value = this.value.toUpperCase();
// });

// $(document).on('input', '#GSTIN', function () {
//     this.value = this.value.toUpperCase();
// });

$('#commonSupplierForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#SupplierModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#SupplierModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    // pan validation
    let pan = $('#PAN').val().trim();
    if (pan !== "") {
        if (pan.length !== 10 || !isValidPAN(pan)) {
            $('#PAN').addClass('is-invalid').removeClass('is-valid');
            toastr.error('Enter Valid PAN');
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#SupplierModal').find('#submitbtn').prop('disabled', false);
            return;
        } else {
            $('#PAN').removeClass('is-invalid').addClass('is-valid');
        }
    }

    // GSTIN validation
    let GSTIN = $('#GSTIN').val().trim();
    if (GSTIN !== "") {
        if (GSTIN.length !== 15) {
        // if (GSTIN.length !== 15 || !isValidGSTIN(GSTIN)) {
            $('#GSTIN').addClass('is-invalid').removeClass('is-valid');
            toastr.error('Enter Valid GSTIN');
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#SupplierModal').find('#submitbtn').prop('disabled', false);
            return;
        } else {
            $('#GSTIN').removeClass('is-invalid').addClass('is-valid');
        }
    }

    // mobile validation
    let mobileField = $('#phone_no');
    let mobileVal = mobileField.val().trim();
    if (mobileVal !== "") {
        if (mobileVal.length > 12) {
            mobileField.addClass('is-invalid').removeClass('is-valid');
            toastr.error('Maximum 12 Digits Are Allowed For Mobile No.');
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#SupplierModal').find('#submitbtn').prop('disabled', false);
            e.preventDefault();
            return false;
        }
    }

    let emailField = jQuery('#commonSupplierForm #email_id');
    let emailVal = emailField.val().trim();
    let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailPattern.test(emailVal) && emailVal !== "") {
        emailField.addClass('is-invalid').removeClass('is-valid');
        toastr.error('Enter Valid Email ID.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#SupplierModal').find('#submitbtn').prop('disabled', false);
        return false;
    }

    var formId = jQuery('#SupplierModal').find('#commonSupplierForm').find('#id').val();
    var supplier_name = jQuery('#SupplierModal').find("#supplier_name").val();
    var formUrl = formId != undefined && formId != "" ? "update-supplier" : "store-supplier";
    var SupplierUrl = formId != undefined && formId != "" ? "verify-supplier?supplier_name=" + encodeURIComponent(supplier_name) + "&id=" + formId : "verify-supplier?supplier_name=" + encodeURIComponent(supplier_name);
    let formData = new FormData(form);
    formData.append('contact_data', JSON.stringify(contact_data));
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    if (supplier_name != '' && supplier_name != undefined) {
        $.ajax({
            url: SupplierUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#SupplierModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#SupplierModal').find('#submitbtn').prop('disabled', false);
                                    addedSupplier(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonSupplierForm").reset();
                                        const form = document.getElementById("commonSupplierForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#supplier_name').focus();
                                        jQuery('#city_id').val('').trigger('change');

                                        if (partAfterManage != 'supplier') {
                                            jQuery('#SupplierModal').modal('hide');
                                        }
                                    }
                                    contact_data = [];
                                    jQuery('#SupplierDetailTable tbody').empty();
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#SupplierModal').find('#submitbtn').prop('disabled', false);
                                    addedSupplier(true);
                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#SupplierModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#SupplierModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#SupplierModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#SupplierModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#SupplierModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestSupplier(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "supplier_name-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#supplier_name").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#supplier_name_list').html(data.supplierList);
                } else {
                    toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#supplier_name").removeClass('file-loader');
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

jQuery(document).on('click', '#supplier_name_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#supplier_name_suggesion').val(suggest);
    var hidden = jQuery('#supplier_name_suggesion').val();
    var suggestion_list = jQuery('#supplier_name_list').html;
    jQuery('#SupplierModal').find('#supplier_name').val(hidden)
    var supplier_name = hidden;

    if (suggestion_list != '') {
        checkSupplierName(supplier_name);
    }
    jQuery('#supplier_name_list').html('');
});

let lastVerifiedSupplier = '';

jQuery(document).on('blur', '#supplier_name', function () {
    let supplier_name = jQuery(this).val().trim();

    if (supplier_name === '') return;

    if (supplier_name !== lastVerifiedSupplier) {
        lastVerifiedSupplier = supplier_name;
        checkSupplierName(supplier_name);
    }
});

jQuery(document).on('input', '#supplier_name', function () {
    lastVerifiedSupplier = '';
});

function checkSupplierName(supplier_name) {
    var formId = jQuery('#SupplierModal').find('#commonSupplierForm').find('#id').val();
    var formUrl = formId != undefined && formId != "" ? "verify-supplier?supplier_name=" + encodeURIComponent(supplier_name) + "&id=" + formId : "verify-supplier?supplier_name=" + encodeURIComponent(supplier_name);
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

function verifySupplier() {
    var SupplierName = jQuery('#supplier_name').val();
    var suggestion_list = jQuery('#supplier_name_list').html;

    if (suggestion_list != '') {
        checkSupplierName(SupplierName);
    }
}

// jQuery(document).on('click', '#supplier_name_list', function (e) {
//     var suggest = e.target.innerHTML;
//     jQuery('#supplier_name_suggesion').val(suggest);
//     var hidden = jQuery('#supplier_name_suggesion').val();
//     var suggestion_list = jQuery('#supplier_name_list').html;
//     jQuery('#SupplierModal').find('#supplier_name').val(hidden)
//     var supplier_name = hidden;
//     if (suggestion_list != '') {
//         checkSupplierName(supplier_name);
//     }
//     jQuery('#supplier_name_list').html('');
// });

// function checkSupplierName(supplier_name) {
//     var formId = jQuery('#SupplierModal').find('#commonSupplierForm').find('#id').val();
//     var formUrl = formId != undefined && formId != "" ? "verify-supplier?supplier_name=" + encodeURIComponent(supplier_name) + "&id=" + formId : "verify-supplier?supplier_name=" + encodeURIComponent(supplier_name);
//     jQuery.ajax({
//         url: formUrl,
//         type: 'GET',
//         dataType: 'json',
//         processData: false,
//         headers: headerOpt,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 toastr.error(data.response_message);
//             }
//         }
//     });
// }

// function verifySupplier() {
//     var SupplierName = jQuery('#supplier_name').val();
//     var suggestion_list = jQuery('#supplier_name_list').html;
//     if (suggestion_list != '') {
//         checkSupplierName(SupplierName);
//     }
// }

jQuery('#SupplierModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#SupplierModal');
    thisForm.find('#id').val('');
    jQuery('#city_id').change();
    // jQuery('#city_id').val('').trigger('change');
    lastVerifiedSupplier = '';
    contact_data = [];
    jQuery('#SupplierDetailTable tbody').empty();
    jQuery('#SupplierModal').find('#city_id').val('').trigger('change.select2');
    document.getElementById("commonSupplierForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        // jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });

    jQuery('#SupplierModal').find('#add_new').hide();
});

$('#SupplierModal').on('shown.bs.modal', function () {
    jQuery('#city_id').change();
    const input = document.getElementById('supplier_name');
    input?.focus();

    var formIdblank = jQuery('#commonSupplierForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#SupplierModal').find('#add_new').show();
    } else {
        jQuery('#SupplierModal').find('#add_new').hide();
    }
});

jQuery('#SupplierModal').on('click', '#add_new', function () {
    jQuery('#SupplierModal').find('#id').val('');
    document.getElementById("commonSupplierForm").reset();
    lastVerifiedSupplier = '';
    const form = document.getElementById("commonSupplierForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#supplier_name').focus();
    $("#city_id").val('').trigger('change.select2');
    contact_data = [];
    jQuery('#SupplierDetailTable tbody').empty();
    jQuery('#SupplierModal').find('#add_new').hide();
});

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

function getSupplier($this = null) {
    var formUrl = "get-suppliers";
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
                    var stgDrpHtml = `<option value="">Select Supplier</option>`;
                    for (let indx in data.suppliers) {
                        stgDrpHtml += `<option value="${data.suppliers[indx].id}" data-state-id="${data.suppliers[indx].state_id}">${data.suppliers[indx].supplier_name}</option>`;
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

function addedSupplier($event) {
    if ($event == true) {
        getSupplier(".suggest_supplier_name");
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
jQuery('#SupplierContactModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#SupplierContactModal');
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    jQuery('#supplier_detail_form').trigger("reset");

});

$('#supplier_detail_form').on('submit', function (e) {
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    // phone validation
    // let phoneField = jQuery('#supplier_detail_form #phone_no');
    // let phoneVal = phoneField.val().trim();
    // if (phoneVal !== "") {
    //     if (phoneVal.length > 10) {
    //         phoneField.addClass('is-invalid').removeClass('is-valid');
    //         toastr.error('Maximum 10 Digits Are Allowed For Phone No.');
    //         jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
    //         jQuery('#SupplierContactModal').find('#submitbtn').prop('disabled', false);
    //         e.preventDefault();
    //         return false;
    //     }
    // }

    let emailField = jQuery('#supplier_detail_form #email_id');
    let emailVal = emailField.val().trim();
    let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // if (emailVal === "") {
    //     emailField.addClass('is-invalid').removeClass('is-valid');
    //     toastr.error('Enter Valid Email ID');
    //     jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
    //     jQuery('#SupplierContactModal').find('#submitbtn').prop('disabled', false);
    //     return false;
    // }

    if (!emailPattern.test(emailVal) && emailVal !== "") {
        emailField.addClass('is-invalid').removeClass('is-valid');
        toastr.error('Enter Valid Email ID.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#SupplierContactModal').find('#submitbtn').prop('disabled', false);
        return false;
    }

    var data = new FormData(document.getElementById('supplier_detail_form'));
    var formValue = Object.fromEntries(data.entries());
    var thisModal = jQuery('#SupplierContactModal');
    var noDuplicate = true;

    if (formValue.form_type == "edit") {
        jQuery('#SupplierDetailTable tbody input[name*="contact_person[]"]').each(function (indx) {
            if (formValue.contact_person == jQuery(this).val() && formValue.row_index != jQuery(this).closest('tr').index()) {
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                noDuplicate = false;
                return;
            }
        });
    } else {
        jQuery('#SupplierDetailTable tbody input[name*="contact_person[]"]').each(function (indx) {
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
        var phone_no = formValue.phone_no ? formValue.phone_no : "";
        var email_id = formValue.email_id ? formValue.email_id : "";

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
            // tblHtml += `<td>${phone_no}<input type='hidden' name='phone_no[]' value="${phone_no}"/></td>`;
            // tblHtml += `<td>${email_id}<input type='hidden' name='email_id[]' value="${email_id}"/>
            tblHtml += `<td>${phone_no}</td>`;
            tblHtml += `<td>${email_id}
            </td>`;
            jQuery('#SupplierDetailTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
            toastSuccess("Record Updated.");
        } else {
            contact_data.push(formValue)
            let formIndx = contact_data.indexOf(formValue);
            if (jQuery('#SupplierDetailTable tbody').find('#noDetails').length > 0) {
                jQuery('#SupplierDetailTable tbody').empty();
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
            // tblHtml += `<td>${phone_no}<input type='hidden' name='phone_no[]' value="${phone_no}"/></td>`;
            // tblHtml += `<td>${email_id}<input type='hidden' name='email_id[]' value="${email_id}"/>
            tblHtml += `<td>${phone_no}</td>`;
            tblHtml += `<td>${email_id}
            </td>`;
            tblHtml += `</tr>`;
            jQuery('#SupplierDetailTable tbody').append(tblHtml);
            toastSuccess("Record Inserted.");
        }

        if (formValue.form_type == "edit") {
            thisModal.modal('hide');
        } else {
            let formElement = document.getElementById('supplier_detail_form');
            formElement.reset();
            jQuery('#supplier_detail_form #contact_person').val('');
            jQuery('#supplier_detail_form #phone_no').val('');
            jQuery('#supplier_detail_form #email_id').val('');
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
    let thisForm = jQuery('#SupplierContactModal');
    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    var frmData = contact_data[formIndx];
    thisForm.find("#contact_person").val(frmData.contact_person);
    thisForm.find("#phone_no").val(frmData.phone_no);
    thisForm.find("#email_id").val(frmData.email_id);

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
    jQuery('#SupplierDetailTable tbody').empty();
    fillContactTable();
}
function fillContactTable() {
    if (contact_data.length > 0) {
        for (let key in contact_data) {
            let formIndx = contact_data.indexOf(contact_data[key]);
            var contact_person = contact_data[key].contact_person ? contact_data[key].contact_person : "";
            var phone_no = contact_data[key].phone_no ? contact_data[key].phone_no : "";
            var email_id = contact_data[key].email_id ? contact_data[key].email_id : "";
            var in_use = contact_data[key].in_use ? contact_data[key].in_use : "";

            if (jQuery('#SupplierDetailTable tbody').find('#noDetails').length > 0) {
                jQuery('#SupplierDetailTable tbody').empty();
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
            // tblHtml += `<td>${phone_no}<input type='hidden' name='phone_no[]' value="${phone_no}"/></td>`;
            // tblHtml += `<td>${email_id}<input type='hidden' name='email_id[]' value="${email_id}"/>`;
            tblHtml += `<td>${phone_no}</td>`;
            tblHtml += `<td>${email_id}</td>`;
            //  tblHtml += `<td>${phone_no}</td>`;
            // tblHtml += `<td>${email_id}</td>`;
            tblHtml += `</tr>`;
            jQuery('#SupplierDetailTable tbody').append(tblHtml);
        }
    }
}
