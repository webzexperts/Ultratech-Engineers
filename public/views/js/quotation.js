
quotation_details_data = [];

function resetQuotationTabToDetails() {
    var tabEl = document.getElementById('steparrow-gen-info-tab');
    if (tabEl) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
            var tab = bootstrap.Tab.getOrCreateInstance(tabEl);
            tab.show();
        } else {
            tabEl.click();
        }
    }
}

jQuery('#QuotationModal').on('shown.bs.modal', function () {
    var formId = jQuery('#commonQuotationForm').find('input[name="id"]').val();
    var hasAccess = jQuery('#commonQuotationForm').find('#has_access').val();

    if (formId == "") {
        jQuery('#QuotationModal').find('#quot_sequence').prop('readonly', false);
        getLatestQuotationNo();
        getPendingCustomer();
        getAllQuotNo();
        jQuery('#QuotationModal').find('#add_new').hide();
        jQuery('#QuotationModal').find('#preview_btn').hide();
    } else {
        jQuery('#QuotationModal').find('#add_new').show();
        jQuery('#QuotationModal').find('#preview_btn').show();
    }
    if (!jQuery('#QuotationModal').find('#quot_sequence').prop('readonly')) {
        const input = document.getElementById('quot_sequence');
        input?.focus();
    }
    resetQuotationTabToDetails();
});

// Edit quotation row click
jQuery('#dyntable tbody').on('click', '.edit-quotation', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["quot_id"]) {
        jQuery('#QuotationModal').find('#id').val(data["quot_id"]);
        fetchAndFillQuotation(data["quot_id"]);
    }
});

// Function to fetch and fill quotation data
function fetchAndFillQuotation(id) {
    if (!id) return;
    jQuery('#QuotationModal').find('#id').val(id);
    jQuery('#QuotationModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-quotation",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.quotation_data != null) {
                jQuery('#QuotationModal').find('#id').val(data.quotation_data.quot_id != "" ? data.quotation_data.quot_id : "");

                let url = checkFileRoute + "?id=" + data.quotation_data.quot_id + "&name=" + data.quotation_data.pdf_name + "&type=quotation";
                jQuery('#preview_btn').attr('href', url).show();

                jQuery('#QuotationModal').find('#quot_number').val(data.quotation_data.quot_number != "" ? data.quotation_data.quot_number : "");
                jQuery('#QuotationModal').find('#quot_date').val(data.quotation_data.quot_date != "" ? data.quotation_data.quot_date : "");
                jQuery('#QuotationModal').find('#quot_sequence').val(data.quotation_data.quot_sequence != "" ? data.quotation_data.quot_sequence : "");
                jQuery('#QuotationModal').find('#quotd_inqd_id').val(data.quotation_data.quotd_inqd_id != "" ? data.quotation_data.quotd_inqd_id : "");

                if (zeroToEmpty(data.quotation_data.quot_customer_id) !== '') {
                    getPendingCustomer().done(function () {
                        jQuery('#QuotationModal').find('#quot_customer_id').val(zeroToEmpty(data.quotation_data.quot_customer_id)).trigger('change.select2');
                        setSelect2Readonly("#QuotationModal #quot_customer_id", true);
                        getCustomerKindAttn().done(function () {
                            jQuery('#QuotationModal').find('#quot_kind_attn_id').val(data.quotation_data.quot_kind_attn_id != "" ? data.quotation_data.quot_kind_attn_id : "").trigger("change.select2");
                        });
                    });
                }
                jQuery('#QuotationModal').find('#quot_sac_id').val(data.quotation_data.quot_sac_id != 0 ? data.quotation_data.quot_sac_id : "").trigger("change.select2");

                jQuery('#QuotationModal').find('#quot_ref_no_date').val(data.quotation_data.quot_ref_no_date != "" ? data.quotation_data.quot_ref_no_date : "");

                jQuery('#QuotationModal').find('#quot_offer_validity').val(data.quotation_data.quot_offer_validity != "" ? data.quotation_data.quot_offer_validity : "");

                jQuery('#QuotationModal').find('#quot_special_note').val(data.quotation_data.quot_special_note != "" ? data.quotation_data.quot_special_note : "");

                jQuery('#QuotationModal').find('#quot_prepared_by_id').val(data.quotation_data.quot_prepared_by_id != 0 ? data.quotation_data.quot_prepared_by_id : "").trigger("change.select2");

                jQuery('#QuotationModal').find('#quot_authorised_by_id').val(data.quotation_data.quot_authorised_by_id != 0 ? data.quotation_data.quot_authorised_by_id : "").trigger("change.select2");


                // getAllQuotNo();
                getAllQuotNo().done(function () {

                    let copyFromId = zeroToEmpty(data.quotation_data.quot_copy_from_id);
                    if (copyFromId) {
                        jQuery('#QuotationModal')
                            .find('#quot_copy_from_id')
                            .val(copyFromId) // 🔑 force string
                            .trigger('change.select2');
                    }
                });

                jQuery('#QuotationModal').find('#quot_terms_and_conditions').val(data.quotation_data.quot_terms_and_conditions != "" ? data.quotation_data.quot_terms_and_conditions : "");

                // if (data.quotation_data.quot_revision_doc != "" && data.quotation_data.quot_revision_doc != undefined) {

                //     jQuery('#QuotationModal').find('#revision_doc_doc').val(data.quotation_data.quot_revision_doc);
                //     jQuery('#QuotationModal').find('#revision_doc_prev').attr('href', uploadURL + data.quotation_data.quot_revision_doc);
                //     jQuery('#QuotationModal').find('#revision_doc_prev').removeClass('hide');
                //     jQuery('#QuotationModal').find('#revision_doc_img-prev-box').removeClass('hide');
                //     jQuery('#QuotationModal').find('.fileupload-exists').removeClass('hide');
                // } else {
                //     jQuery('#QuotationModal').find('#revision_doc_doc').val('');
                //     jQuery('#QuotationModal').find('#revision_doc_prev').attr('href', '#');
                //     jQuery('#QuotationModal').find('#revision_doc_prev').addClass('hide');
                //     jQuery('#QuotationModal').find('#revision_doc_img-prev').html('');
                //     jQuery('#QuotationModal').find('#revision_doc_img-prev-box').addClass('hide');
                //     jQuery('#QuotationModal').find('#revision_doc_img-prev-box').html('');
                //     jQuery('#QuotationModal').find('.fileupload-exists').addClass('hide');

                // }

                if (data.quotation_data.quot_revision_doc != "" && data.quotation_data.quot_revision_doc != undefined) {
                    let fullPath = data.quotation_data.quot_revision_doc;
                    let fileName = fullPath.split('/').pop();
                    jQuery('#QuotationModal').find('#revision_doc_doc').val(fullPath);
                    jQuery('#QuotationModal').find('#revision_doc_prev').attr('href', uploadURL + fullPath).removeClass('hide');
                    jQuery('#QuotationModal').find('#revision_doc_img-prev-box').removeClass('hide');
                    jQuery('#QuotationModal').find('.fileupload-exists').removeClass('hide');
                    jQuery('#QuotationModal').find('#revision_doc_img-prev').html(fileName);
                    let fileInput = jQuery('#QuotationModal').find('#revision_doc');
                    let newFile = new DataTransfer();
                    newFile.items.add(new File([""], fileName));
                    fileInput[0].files = newFile.files;
                } else {
                    jQuery('#QuotationModal').find('#revision_doc_doc').val('');
                    jQuery('#QuotationModal').find('#revision_doc_prev').attr('href', '#').addClass('hide');
                    jQuery('#QuotationModal').find('#revision_doc_img-prev').html('');
                    jQuery('#QuotationModal').find('#revision_doc_img-prev-box').addClass('hide').html('');
                    jQuery('#QuotationModal').find('.fileupload-exists').addClass('hide');
                    jQuery('#QuotationModal').find('#revision_doc').val('');
                }

                if (data.quotation_details_data != "" && data.quotation_details_data.length > 0) {
                    quotation_details_data.push(...data.quotation_details_data);
                    fillIQuotationDetailsTable();
                }

                jQuery('#QuotationModal').find('#pending_btn').prop('disabled', true);
                jQuery('#QuotationModal').find('#submitbtn').prop('disabled', false);
                jQuery('#QuotationModal').find('#add_new').show();

                const form = document.getElementById("commonQuotationForm");
                if (form) form.classList.remove('was-validated');

                if (data.quotation_data.in_use == true) {
                    jQuery('#QuotationModal').find('#quot_sequence').prop('readonly', true);
                    let nextInput = jQuery('#QuotationModal').find('#quot_date');
                    if (nextInput.hasClass('trans-date-picker') || nextInput.hasClass('date-picker')) {
                        nextInput.one('focus', function () {
                            setTimeout(() => {
                                jQuery(this).datepicker('hide');
                            }, 50);
                        });
                    }
                    nextInput.focus();
                } else {
                    jQuery('#QuotationModal').find('#quot_sequence').prop('readonly', false);
                    jQuery('#QuotationModal').find('#quot_sequence').focus();
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
        },
        complete: function () {
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        }
    });
}

// Reset button click for quotation modal
jQuery('#resetbtn').on('click', function () {
    quotation_details_data = [];
    jQuery('#QuotationDetailTable tbody').empty();
    jQuery('#QuotationDetailTable tbody').append(`<tr><td colspan="15" class="text-center" id="noDetails">No Quotation Details Added</td></tr>`);

    var formId = jQuery('#QuotationModal').find('#id').val();
    if (!formId) {
        jQuery('#QuotationModal').find('#quot_sequence').prop('readonly', false);
        var form = document.getElementById("commonQuotationForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        jQuery('#quot_sequence').focus();
        setSelect2Readonly("#QuotationModal #quot_customer_id", false);
        $("#quot_customer_id").val('').trigger('change.select2');
        $("#quot_kind_attn_id").val('').trigger('change.select2');
        $("#quot_sac_id").val('').trigger('change.select2');
        $("#quot_prepared_by_id").val(loginUserId).trigger('change.select2');
        $("#quot_authorised_by_id").val('').trigger('change.select2');
        $("#quot_copy_from_id").val('').trigger('change.select2');
        getLatestQuotationNo();
        getPendingCustomer();

        jQuery('#QuotationModal').find('#revision_doc_doc').val('');
        jQuery('#QuotationModal').find('#revision_doc').val('');
        jQuery('#QuotationModal').find('#revision_doc_prev').attr('href', '#');
        jQuery('#QuotationModal').find('#revision_doc_prev').addClass('hide');
        jQuery('#QuotationModal').find('.remove-file').addClass('hide');
        jQuery('#QuotationModal').find('.fileupload-exists').addClass('hide');
        jQuery('#QuotationModal').find('#add_new').hide();
        jQuery('#QuotationModal').find('#preview_btn').hide();
        jQuery('.toggleModalBtn').prop('disabled', true);
    } else {
        fetchAndFillQuotation(formId);
    }
    resetQuotationTabToDetails();
});

jQuery('#QuotationModal').on('click', '#add_new', function () {
    jQuery('#QuotationModal').find('#quot_sequence').prop('readonly', false);
    var form = document.getElementById("commonQuotationForm");
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
    }
    jQuery('#commonQuotationForm').find('input[name="id"]').val('');
    jQuery('#QuotationModal').find('#add_new').hide();

    quotation_details_data = [];
    jQuery('#QuotationDetailTable tbody').empty();
    jQuery('#QuotationDetailTable tbody').append(`<tr><td colspan="15" class="text-center" id="noDetails">No Quotation Details Added</td></tr>`);

    jQuery('#quot_sequence').focus();
    setSelect2Readonly("#QuotationModal #quot_customer_id", false);
    $("#quot_customer_id").val('').trigger('change.select2');
    $("#quot_kind_attn_id").val('').trigger('change.select2');
    $("#quot_sac_id").val('').trigger('change.select2');
    $("#quot_prepared_by_id").val(loginUserId).trigger('change.select2');
    $("#quot_authorised_by_id").val('').trigger('change.select2');
    $("#quot_copy_from_id").val('').trigger('change.select2');
    getLatestQuotationNo();
    getPendingCustomer();

    jQuery('#QuotationModal').find('#revision_doc_doc').val('');
    jQuery('#QuotationModal').find('#revision_doc').val('');
    jQuery('#QuotationModal').find('#revision_doc_prev').attr('href', '#').addClass('hide');
    jQuery('#QuotationModal').find('.remove-file').addClass('hide');
    jQuery('#QuotationModal').find('.fileupload-exists').addClass('hide');

    jQuery('#QuotationModal').find('#pending_btn').prop('disabled', true);
    jQuery('#QuotationModal').find('#submitbtn').prop('disabled', false);
    resetQuotationTabToDetails();
});

// // get data for edit
// jQuery('#dyntable tbody').on('click', '.edit-quotation', function () {

//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#QuotationModal').find('#id').val(data["quot_id"]);
//     jQuery('#QuotationModal').modal('show');
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-quotation",
//         type: 'GET',
//         data: "id=" + data["quot_id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {

//                 jQuery('#QuotationModal').find('#id').val(data.quotation_data.quot_id != "" ? data.quotation_data.quot_id : "");

//                 jQuery('#QuotationModal').find('#quot_number').val(data.quotation_data.quot_number != "" ? data.quotation_data.quot_number : "");
//                 jQuery('#QuotationModal').find('#quot_date').val(data.quotation_data.quot_date != "" ? data.quotation_data.quot_date : "");
//                 jQuery('#QuotationModal').find('#quot_sequence').val(data.quotation_data.quot_sequence != "" ? data.quotation_data.quot_sequence : "");
//                 jQuery('#QuotationModal').find('#quotd_inqd_id').val(data.quotation_data.quotd_inqd_id != "" ? data.quotation_data.quotd_inqd_id : "");

//                 if (zeroToEmpty(data.quotation_data.quot_customer_id) !== '') {
//                     getPendingCustomer().done(function () {
//                         setTimeout(() => {
//                             jQuery('#QuotationModal').find('#quot_customer_id').val(zeroToEmpty(data.quotation_data.quot_customer_id)).trigger('change');
//                         }, 500);
//                     });
//                 }
//                 jQuery('#QuotationModal').find('#quot_sac_id').val(data.quotation_data.quot_sac_id != "" ? data.quotation_data.quot_sac_id : "").trigger("change.select2");

//                 jQuery('#QuotationModal').find('#quot_customer_id').val(data.quotation_data.quot_customer_id || '').trigger('change.select2');

//                 jQuery('#QuotationModal').find('#quot_customer_id').one('change', function () {
//                     getCustomerKindAttn().done(function () {
//                         jQuery('#QuotationModal')
//                             .find('#quot_kind_attn_id')
//                             .val(data.quotation_data.quot_kind_attn_id)
//                             .trigger('change.select2');
//                     });
//                 });


//                 jQuery('#QuotationModal').find('#quot_kind_attn_id').val(data.quotation_data.quot_kind_attn_id != "" ? data.quotation_data.quot_kind_attn_id : "").trigger("change.select2");

//                 jQuery('#QuotationModal').find('#quot_ref_no_date').val(data.quotation_data.quot_ref_no_date != "" ? data.quotation_data.quot_ref_no_date : "");

//                 jQuery('#QuotationModal').find('#quot_offer_validity').val(data.quotation_data.quot_offer_validity != "" ? data.quotation_data.quot_offer_validity : "");

//                 jQuery('#QuotationModal').find('#quot_special_note').val(data.quotation_data.quot_special_note != "" ? data.quotation_data.quot_special_note : "");

//                 jQuery('#QuotationModal').find('#quot_prepared_by_id').val(data.quotation_data.quot_prepared_by_id != "" ? data.quotation_data.quot_prepared_by_id : "").trigger("change.select2");

//                 jQuery('#QuotationModal').find('#quot_authorised_by_id').val(data.quotation_data.quot_authorised_by_id != "" ? data.quotation_data.quot_authorised_by_id : "").trigger("change.select2");


//                 // getAllQuotNo();
//                 getAllQuotNo().done(function () {

//                     let copyFromId = data.quotation_data.quot_copy_from_id;
//                     if (copyFromId) {
//                         jQuery('#QuotationModal')
//                             .find('#quot_copy_from_id')
//                             .val(copyFromId) // 🔑 force string
//                             .trigger('change.select2');
//                     }
//                 });

//                 jQuery('#QuotationModal').find('#quot_terms_and_conditions').val(data.quotation_data.quot_terms_and_conditions != "" ? data.quotation_data.quot_terms_and_conditions : "");

//                 // if (data.quotation_data.quot_revision_doc != "" && data.quotation_data.quot_revision_doc != undefined) {

//                 //     jQuery('#QuotationModal').find('#revision_doc_doc').val(data.quotation_data.quot_revision_doc);
//                 //     jQuery('#QuotationModal').find('#revision_doc_prev').attr('href', uploadURL + data.quotation_data.quot_revision_doc);
//                 //     jQuery('#QuotationModal').find('#revision_doc_prev').removeClass('hide');
//                 //     jQuery('#QuotationModal').find('#revision_doc_img-prev-box').removeClass('hide');
//                 //     jQuery('#QuotationModal').find('.fileupload-exists').removeClass('hide');
//                 // } else {
//                 //     jQuery('#QuotationModal').find('#revision_doc_doc').val('');
//                 //     jQuery('#QuotationModal').find('#revision_doc_prev').attr('href', '#');
//                 //     jQuery('#QuotationModal').find('#revision_doc_prev').addClass('hide');
//                 //     jQuery('#QuotationModal').find('#revision_doc_img-prev').html('');
//                 //     jQuery('#QuotationModal').find('#revision_doc_img-prev-box').addClass('hide');
//                 //     jQuery('#QuotationModal').find('#revision_doc_img-prev-box').html('');
//                 //     jQuery('#QuotationModal').find('.fileupload-exists').addClass('hide');

//                 // }

//                 if (data.quotation_data.quot_revision_doc != "" && data.quotation_data.quot_revision_doc != undefined) {
//                     let fullPath = data.quotation_data.quot_revision_doc;
//                     let fileName = fullPath.split('/').pop();
//                     jQuery('#QuotationModal').find('#revision_doc_doc').val(fullPath);
//                     jQuery('#QuotationModal').find('#revision_doc_prev').attr('href', uploadURL + fullPath).removeClass('hide');
//                     jQuery('#QuotationModal').find('#revision_doc_img-prev-box').removeClass('hide');
//                     jQuery('#QuotationModal').find('.fileupload-exists').removeClass('hide');
//                     jQuery('#QuotationModal').find('#revision_doc_img-prev').html(fileName);
//                     let fileInput = jQuery('#QuotationModal').find('#revision_doc');
//                     let newFile = new DataTransfer();
//                     newFile.items.add(new File([""], fileName));
//                     fileInput[0].files = newFile.files;
//                 } else {
//                     jQuery('#QuotationModal').find('#revision_doc_doc').val('');
//                     jQuery('#QuotationModal').find('#revision_doc_prev').attr('href', '#').addClass('hide');
//                     jQuery('#QuotationModal').find('#revision_doc_img-prev').html('');
//                     jQuery('#QuotationModal').find('#revision_doc_img-prev-box').addClass('hide').html('');
//                     jQuery('#QuotationModal').find('.fileupload-exists').addClass('hide');
//                     jQuery('#QuotationModal').find('#revision_doc').val('');
//                 }

//                 if (data.quotation_details_data != "" && data.quotation_details_data.length > 0) {
//                     quotation_details_data.push(...data.quotation_details_data);
//                     fillIQuotationDetailsTable();
//                 }

//                 jQuery('#QuotationModal').find('#pending_btn').prop('disabled', true);
//                 jQuery('#QuotationModal').find('#submitbtn').prop('disabled', false);
//                 jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');

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
// get the latest number
function getLatestQuotationNo() {
    jQuery.ajax({
        url: "get-latest_quotation_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#quot_sequence_date').val();
                jQuery('#quot_number').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#quot_sequence').val(data.number);
                jQuery('#quot_date').val(currentDate);
            } else {
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            jQuery('#quotation_number').removeClass('file-loader');
            console.log('Field To Get Latest Quotation No.!')
        }
    });
}

// get Pending Customers
function getPendingCustomer() {

    let thisForm = jQuery('#commonQuotationForm');
    let customer_id = jQuery('#quot_customer_id option:selected').val();
    var formId = jQuery('#commonQuotationForm').find('input[name="id"]').val();

    if (formId != undefined && formId != "") {
        var Url = 'get-pending_customer_for_quotation?customer_id=' + customer_id + "&id=" + formId;
    } else {
        var Url = 'get-pending_customer_for_quotation?customer_id=' + customer_id;
    }

    return jQuery.ajax({
        url: Url,
        type: 'GET',
        dataType: 'json',
        processData: false,
        success: function (data) {
            var CustDrpHtml = `<option value="">Select Customer</option>`;
            if (data.response_code == 1 && data.quot_customer.length) {
                for (let indx in data.quot_customer) {
                    let item = data.quot_customer[indx];
                    let displayText = item.customer;
                    CustDrpHtml += `<option value="${item.id}">${displayText}</option>`;
                }
            }

            thisForm.find('#quot_customer_id').empty().append(CustDrpHtml);
        }
    });

}

// get All Quotation no
function getAllQuotNo() {
    let thisForm = jQuery('#commonQuotationForm');
    var formQuId = jQuery('#commonQuotationForm').find('input[name="id"]').val();
    if (formQuId == undefined) {
        var Url = "get_all_quotation_no";
    } else {
        var Url = "get_all_quotation_no?id=" + formQuId;
    }
    return jQuery.ajax({
        url: Url,
        type: 'GET',
        dataType: 'json',
        processData: false,
        success: function (data) {
            var CopyDrpHtml = `<option value="">Select Copy From</option>`;
            if (data.response_code == 1 && data.quot_terms.length) {
                for (let indx in data.quot_terms) {
                    let item = data.quot_terms[indx];
                    let displayText = item.quot_number;
                    CopyDrpHtml += `<option value="${item.quot_id}">${displayText}</option>`;
                }
            }

            thisForm.find('#quot_copy_from_id').empty().append(CopyDrpHtml);
        }
    });

}

jQuery('#quot_copy_from_id').on('change', function () {
    getTermsAndConditions();
})
// get terms and conditions as per selection
function getTermsAndConditions() {
    let thisForm = jQuery('#commonQuotationForm');
    let quot_id = thisForm.find('#quot_copy_from_id option:selected').val();
    if (quot_id != "") {
        return jQuery.ajax({
            url: "get_quotation_terms_and_conditions?quot_id=" + quot_id,
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                if (data.response_code == 1) {
                    jQuery('#QuotationModal').find('#quot_terms_and_conditions').val(data.terms_conditions.quot_terms_and_conditions);
                } else {
                    jQuery('#QuotationModal').find('#quot_terms_and_conditions').val('');
                }
            }
        });
    }
}

jQuery('#commonQuotationForm').find('#quot_customer_id').on('change', function () {
    setTimeout(() => {
        getPendingListForQuotation();
    }, 1000);
});

// getPending Inquiry/Estimation List After Customer Selection
function getPendingListForQuotation() {
    let thisForm = jQuery('#commonQuotationForm');
    let customer_id = thisForm.find('#quot_customer_id option:selected').val();
    var formId = jQuery('#commonQuotationForm').find('input[name="id"]').val();

    if (formId != "" && formId != undefined) {
        var urL = "get-pending_inquiry_estimation_for_quotation?customer_id=" + customer_id + "&id=" + formId;
    } else {
        var urL = "get-pending_inquiry_estimation_for_quotation?customer_id=" + customer_id;

    }
    if (customer_id != "") {
        jQuery.ajax({
            url: urL,
            // url: "get-pending_inquiry_estimation_for_quotation?customer_id=" + customer_id,
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {

                if (data.response_code == 1 && data.inq_data.length > 0) {
                    // new code
                    var usedParts = [];
                    var totalDisb = 0;
                    var found = 0;
                    function isUsed(pjId) {
                        if (usedParts.includes(Number(pjId))) {
                            totalDisb++;
                            return true;
                        }
                        return false;
                    }

                    let totalEntry = 0;
                    var tblHtml = ``;
                    var found = 0;

                    // end new code
                    if (data.inq_data.length > 0 && !jQuery.isEmptyObject(data.inq_data)) {
                        found = 1;

                        for (let idx in data.inq_data) {

                            // var inUse = isUsed(data.inq_data[idx].inqd_id);
                            // var in_use = data.inq_data[idx].in_use == true ? 'readonly' : '';
                            totalEntry++;
                            tblHtml += `<tr>
                                <td><input class="checkbox-filter-remove" type="checkbox" name="inqd_id[]" class="simple-check" id="inqd_ids_${data.inq_data[idx].quotd_inqd_id}"
                                    value="${data.inq_data[idx].quotd_inqd_id}"/></td>
                                <td>${data.inq_data[idx].quotd_inq_number}</td>
                                <td>${data.inq_data[idx].quotd_inq_date}</td>
                                <td>${data.inq_data[idx].customer != null ? data.inq_data[idx].customer : ""}</td>
                                <td>${data.inq_data[idx].inq_ref_no_date != null ? data.inq_data[idx].inq_ref_no_date : ''}</td>
                                <td>${data.inq_data[idx].inqd_test_method != null ? data.inq_data[idx].inqd_test_method : ''}</td>
                                <td>${data.inq_data[idx].inqd_process_at != null ? data.inq_data[idx].inqd_process_at : ''}</td>
                                <td>${data.inq_data[idx].type_of_job != null ? data.inq_data[idx].type_of_job : ''}</td>
                                <td>${data.inq_data[idx].job_description != null ? data.inq_data[idx].job_description : ''}</td>
                                <td>${data.inq_data[idx].inqd_part_no != null ? data.inq_data[idx].inqd_part_no : ''}</td>
                                
                                <td>${parseFloat(data.inq_data[idx].inqd_quantity).toFixed(2)}</td>
                                <td>${data.inq_data[idx].unit != null ? data.inq_data[idx].unit : ''}</td>
                                <td>${data.inq_data[idx].inqd_remark != null ? data.inq_data[idx].inqd_remark : ''}</td>
                                <td>${data.inq_data[idx].inq_sp_note != null ? data.inq_data[idx].inq_sp_note : ''}</td>
                                </tr>`;
                        }

                    } else {

                        tblHtml += `<tr class="centeralign" id="noPendingPo">
                    <td colspan="15">No Pending Inquiry Available</td>
                </tr>`;

                    }

                    var $table = jQuery("#QuotationPendingModal").find('#pendingQuotationDataTable');
                    if (jQuery.fn.DataTable.isDataTable($table)) {
                        $table.DataTable().clear().destroy();
                    }
                    jQuery('#pendingQuotationDataTable tbody').empty().append(tblHtml);
                    var $new = $table.DataTable({
                        paging: true,
                        searching: true,
                        "oLanguage": {
                            "sSearch": "Search :"
                        },
                        dom: 'lrtip',
                        "sScrollX": true,
                        "sScrollX": "100%",
                        "sScrollXInner": "110%",
                        "bScrollCollapse": true,

                    });
                    fixDataTableColumnsUntilAdjusted($new);
                    if (formId != "" && formId != undefined) {
                        jQuery('#QuotationModal').find('#pending_btn').prop('disabled', true);
                    } else {
                        jQuery('#QuotationModal').find('#pending_btn').prop('disabled', false);
                    }

                } else {
                    jQuery('#QuotationModal').find('#pending_btn').prop('disabled', true);
                }

            }
        });
    } else {
        jQuery('#QuotationModal').find('#pending_btn').prop('disabled', true);

    }
}

// after checked inquirys from pending modal getting data

$('#addPendigQuotationForm').on('submit', function (e) {

    e.preventDefault();

    jQuery('#full-page-loader')
        .removeClass('hidden-loader')
        .addClass('loader-progress-whole-page');

    jQuery('#QuotationPendingModal')
        .find('#submitbtn')
        .prop('disabled', true);

    let chkArr = [];
    var formId = jQuery('#commonQuotationForm').find('input[name="id"]').val();

    jQuery("#addPendigQuotationForm")
        .find("[id^='inqd_ids_']:checked")
        .each(function () {
            chkArr.push(jQuery(this).val());
        });

    // No checkbox selected
    if (chkArr.length === 0) {
        toastr.error('Select Inquiry Item Pending');

        jQuery('#full-page-loader')
            .removeClass('loader-progress-whole-page')
            .addClass('hidden-loader');

        jQuery('#QuotationPendingModal')
            .find('#submitbtn')
            .prop('disabled', false);

        return;
    }

    if (formId != undefined && formId != "") {
        var pend_url = "get-pending_inquiry_for_quotation?id=" + formId;
    } else {
        var pend_url = "get-pending_inquiry_for_quotation";
    }

    jQuery.ajax({
        // url: "get-pending_inquiry_for_quotation",
        url: pend_url,
        type: 'GET',
        data: { inqd_ids: chkArr.join(',') },
        dataType: 'json',
        success: function (data) {

            if (data.response_code == 1) {

                if (data.inq_data && data.inq_data.length > 0) {
                    quotation_details_data = [];
                    for (let ind in data.inq_data) {
                        quotation_details_data.push(data.inq_data[ind]);
                    }
                    // quotation_details_data.push(...data.inq_data);
                    fillIQuotationDetailsTable(data.inq_data);
                } else {
                    fillIQuotationDetailsTable([]);
                }

                jQuery("#QuotationPendingModal").modal('hide');
            } else {
                fillIQuotationDetailsTable([]);
            }

            jQuery('#QuotationPendingModal')
                .find('#submitbtn')
                .prop('disabled', false);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');
        },
        error: function (jqXHR) {

            if (jqXHR.status == 401) {
                toastr.error(jqXHR.statusText);
            } else {
                toastr.error('Something went wrong!');
                console.log(jqXHR.responseText);
            }

            jQuery('#QuotationPendingModal')
                .find('#submitbtn')
                .prop('disabled', false);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');
        }
    });
});

jQuery('#checkall-quot_data').click(function () {
    if (jQuery(this).is(':checked')) {
        jQuery("#QuotationPendingModal").find("[id^='inqd_ids_']:not(.in-use)").prop('checked', true).trigger('change');
    } else {
        jQuery("#QuotationPendingModal").find("[id^='inqd_ids_']:not(.in-use)").prop('checked', false).trigger('change');
    }
});

// function copyQtyUnit() {
//     let quotd_unit_id = jQuery('#quotd_unit_id option:selected').val();
//     jQuery('#quotd_rate_unit_id').val(quotd_unit_id).trigger('change.select2');
// }

// jQuery('#quotd_rate_unit_id').on('change', function () {
//     copyQtyUnit();
// })


// onchange of quotatation sequence
jQuery('#commonQuotationForm').find('#quot_sequence').on('change', function () {
    checkSequence();
});
jQuery('#commonQuotationForm').find('#revision_doc').on('change', function (e) {
    QuotfileUpload(e);
});

// check for duplication sequence
function checkSequence() {

    let thisForm = jQuery('#commonQuotationForm');
    let val = thisForm.find('#quot_sequence').val();

    if (val != "") {

        if (val > 0 == false) {
            toastr.error('Please Enter Valid Quotation No.');
            jQuery('#quot_sequence').parent().parent().parent('div.control-group').addClass('error');
            jQuery('#quot_sequence').focus();
            jQuery('#quot_sequence').val('');

        } else {
            jQuery('#quot_sequence').addClass('file-loader');
            jQuery('#quot_sequence').parent().parent().parent('div.control-group').removeClass('error');

            var urL = "check-quotation_number_duplication?for=add&quot_sequence=" + val;

            var formId = jQuery('#commonQuotationForm').find('input[name="id"]').val();

            if (formId !== undefined) { //if form is edit
                urL = "check-quotation_number_duplication?for=edit&quot_sequence=" + val + "&id=" + formId;
            }

            jQuery.ajax({
                url: urL,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    jQuery('#quot_sequence').removeClass('file-loader');
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        jQuery('#quot_sequence').val('');
                        const input = document.getElementById('quot_sequence'); input?.focus();
                    } else {

                        jQuery('#quot_number').val(data.latest_no);
                        jQuery('#quot_sequence').val(val);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#quot_sequence').removeClass('file-loader');
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
        jQuery('#quotation_number').val('');
        jQuery('#quot_sequence').val('');
    }

}

function getCustomerKindAttn() {
    let thisForm = jQuery('#commonQuotationForm');
    let customer_id = thisForm.find('#quot_customer_id option:selected').val();
    if (customer_id != "") {
        return jQuery.ajax({
            url: "quotation_kind_attn?customer_id=" + customer_id,
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                var kindDrpHtml = `<option value="">Select Kind Attn.</option>`;
                if (data.response_code == 1 && data.kind_attention.length) {
                    for (let indx in data.kind_attention) {
                        let item = data.kind_attention[indx];
                        let displayText = item.contact_person;
                        if (item.contact_email && item.contact_email.trim() !== "") {
                            displayText += " - " + item.contact_email;
                        }

                        if (item.contact_mobile_no && item.contact_mobile_no.trim() !== "") {
                            displayText += " - " + item.contact_mobile_no;
                        }

                        kindDrpHtml += `<option value="${item.id}">${displayText}</option>`;
                    }
                }

                thisForm.find('#quot_kind_attn_id').empty().append(kindDrpHtml);
            }
        });
    } else {
        var kindDrpHtml = `<option value="">Select Kind Attn.</option>`;
        thisForm.find('#quot_kind_attn_id').empty().append(kindDrpHtml);
    }
}

jQuery('#commonQuotationForm #quot_customer_id').on('change', function () {
    getCustomerKindAttn();
});

// edit quotation details
function editQuotationDetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();
    fillQuotationDetailsForm(formIndx, rawIndx);
}

// quotation details form edit
function fillQuotationDetailsForm(formIndx, rawIndx) {

    let thisForm = jQuery('#QuotationDetailsModal');
    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    var frmData = quotation_details_data[formIndx];
    // console.log("frmdata",frmData);

    if (frmData.quotd_inq_number != undefined && frmData.quotd_inq_number != "") {
        // thisForm.find("#quotd_qty").attr('min', parseFloat(frmData.used_qty).toFixed(3));
        thisForm.find("#quotd_qty").attr('min', parseFloat(frmData.used_qty).toFixed(2));
    }
    thisForm.find("#quotd_id").val(frmData.quotd_id ? frmData.quotd_id : "0");
    thisForm.find("#quotd_inqd_id").val(frmData.quotd_inqd_id ? frmData.quotd_inqd_id : "0");
    thisForm.find("#quotd_inq_number").val(frmData.quotd_inq_number ? frmData.quotd_inq_number : "");
    thisForm.find("#quotd_inq_date").val(frmData.quotd_inq_date ? frmData.quotd_inq_date : "");

    thisForm.find("#quotd_test_method_id").val(zeroToEmpty(frmData.quotd_test_method_id)).trigger("change");
    setSelect2Readonly('#quotd_test_method_id', true);
    thisForm.find("#quotd_type_of_job_id").val(zeroToEmpty(frmData.quotd_type_of_job_id)).trigger("change");
    setSelect2Readonly('#quotd_type_of_job_id', true);
    thisForm.find("#quotd_job_desc_id").val(zeroToEmpty(frmData.quotd_job_desc_id)).trigger("change");
    thisForm.find("#quotd_part_no").val(frmData.quotd_part_no ?? (frmData.part ?? (frmData.part_no ?? "")));
    thisForm.find("#quotd_process_at_id").val(zeroToEmpty(frmData.quotd_process_at_id)).trigger("change");
    
    var description = frmData.quotd_description ? frmData.quotd_description.trim() : "";
    if (description === "") {
        var quotd_job_desc_id = zeroToEmpty(frmData.quotd_job_desc_id);
        if (quotd_job_desc_id != "") {
            var jobText = thisForm.find('#quotd_job_desc_id option[value="' + quotd_job_desc_id + '"]').text().trim();
            if (jobText && jobText !== 'Select Job Description') {
                description = jobText;
            }
        }
    }
    thisForm.find("#quotd_description").val(description);
    // thisForm.find("#quotd_qty").val(frmData.quotd_qty != "" ? parseFloat(frmData.quotd_qty).toFixed(3) : "").attr('readonly', true);
    thisForm.find("#quotd_qty").val(frmData.quotd_qty != "" ? parseFloat(frmData.quotd_qty).toFixed(2) : "").attr('readonly', true);
    thisForm.find("#quotd_unit_id").val(zeroToEmpty(frmData.quotd_unit_id)).trigger("change");

    thisForm.find("#quotd_rate_unit").val(frmData.quotd_rate_unit != "" && frmData.quotd_rate_unit != undefined ? parseFloat(frmData.quotd_rate_unit).toFixed(2) : "");
    thisForm.find("#quotd_rate_unit_id").val(zeroToEmpty(frmData.quotd_rate_unit_id)).trigger("change");
    setSelect2Readonly('#quotd_rate_unit_id', true);


    thisForm.find("#quotd_minimum_charge").val(frmData.quotd_minimum_charge != "" && frmData.quotd_minimum_charge != undefined ? parseFloat(frmData.quotd_minimum_charge).toFixed(2) : "");
    thisForm.find("#quotd_minimum_charge_unit_id").val(zeroToEmpty(frmData.quotd_minimum_charge_unit_id)).trigger("change");

    thisForm.find("#quotd_conveyance_charge").val(frmData.quotd_conveyance_charge != undefined && frmData.quotd_conveyance_charge != "" ? parseFloat(frmData.quotd_conveyance_charge).toFixed(2) : "");
    thisForm.find("#quotd_remark").val(frmData.quotd_remark);


    thisForm.modal('show');
}

// remove quotation details
function removeQuotationDetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        removeFormQuotObj(formIndx);
        jQuery(th).closest("tr").remove();
    });
}

function removeFormQuotObj(formIndx) {
    delete quotation_details_data[formIndx];
    quotation_details_data = quotation_details_data.filter(element => element != null);
    jQuery('#QuotationDetailTable tbody').empty();
    fillIQuotationDetailsTable();
}

// edit time fill quotation table start
function fillIQuotationDetailsTable() {
    let thisModal = jQuery('#QuotationDetailsModal');
    let tblHtml = ``;
    if (quotation_details_data.length > 0) {
        for (let key in quotation_details_data) {

            formIndx = quotation_details_data.indexOf(quotation_details_data[key]);

            var quotd_inq_number = quotation_details_data[key].quotd_inq_number ? quotation_details_data[key].quotd_inq_number : "";
            var quotd_inq_date = quotation_details_data[key].quotd_inq_date ? quotation_details_data[key].quotd_inq_date : "";

            var quotd_test_method_id = quotation_details_data[key].quotd_test_method_id ? quotation_details_data[key].quotd_test_method_id : null;

            var test_method = quotd_test_method_id != '' ? thisModal.find('#quotd_test_method_id option[value="' + quotd_test_method_id + '"]').text() : '';

            var quotd_type_of_job_id = quotation_details_data[key].quotd_type_of_job_id ? quotation_details_data[key].quotd_type_of_job_id : null;
            var type_of_job = quotd_type_of_job_id != '' ? thisModal.find('#quotd_type_of_job_id option[value="' + quotd_type_of_job_id + '"]').text() : '';

            var quotd_job_desc_id = quotation_details_data[key].quotd_job_desc_id ? quotation_details_data[key].quotd_job_desc_id : null;
            var job_description = quotd_job_desc_id != '' ? thisModal.find('#quotd_job_desc_id option[value="' + quotd_job_desc_id + '"]').text() : '';

            var part_no = quotation_details_data[key].quotd_part_no ? quotation_details_data[key].quotd_part_no : "";
            if (part_no == "" || part_no == undefined) {
                var quotd_part_id = quotation_details_data[key].quotd_part_id ? zeroToEmpty(quotation_details_data[key].quotd_part_id) : null;
                part_no = quotd_part_id != '' ? thisModal.find('#quotd_part_id option[value="' + quotd_part_id + '"]').text() : '';
            }

            if (part_no == "" || part_no == undefined) {
                part_no = quotation_details_data[key].part == null ? '' : quotation_details_data[key].part;
            }

            var quotd_process_at_id = quotation_details_data[key].quotd_process_at_id ? quotation_details_data[key].quotd_process_at_id : null;
            var process_at = quotd_process_at_id != '' ? thisModal.find('#quotd_process_at_id option[value="' + quotd_process_at_id + '"]').text() : '';

            var quotd_description = quotation_details_data[key].quotd_description ? quotation_details_data[key].quotd_description : "";

            // var quotd_qty = quotation_details_data[key].quotd_qty ? parseFloat(quotation_details_data[key].quotd_qty).toFixed(3) : "";
            var quotd_qty = quotation_details_data[key].quotd_qty ? parseFloat(quotation_details_data[key].quotd_qty).toFixed(2) : "";

            var quotd_unit_id = quotation_details_data[key].quotd_unit_id ? quotation_details_data[key].quotd_unit_id : null;
            var unit = quotd_unit_id != '' ? thisModal.find('#quotd_unit_id option[value="' + quotd_unit_id + '"]').text() : '';

            var quotd_rate_unit = quotation_details_data[key].quotd_rate_unit ? parseFloat(quotation_details_data[key].quotd_rate_unit).toFixed(2) : "";

            var quotd_rate_unit_id = quotation_details_data[key].quotd_rate_unit_id ? quotation_details_data[key].quotd_rate_unit_id : null;
            var rate_unit = quotd_rate_unit_id != '' ? thisModal.find('#quotd_rate_unit_id option[value="' + quotd_rate_unit_id + '"]').text() : '';

            var quotd_minimum_charge = quotation_details_data[key].quotd_minimum_charge ? parseFloat(quotation_details_data[key].quotd_minimum_charge).toFixed(2) : "";

            var quotd_minimum_charge_unit_id = quotation_details_data[key].quotd_minimum_charge_unit_id ? quotation_details_data[key].quotd_minimum_charge_unit_id : null;
            var quotd_minimum_charge_unit = quotd_minimum_charge_unit_id != '' ? thisModal.find('#quotd_minimum_charge_unit_id option[value="' + quotd_minimum_charge_unit_id + '"]').text() : '';

            var quotd_conveyance_charge = quotation_details_data[key].quotd_conveyance_charge ? parseFloat(quotation_details_data[key].quotd_conveyance_charge).toFixed(2) : "";

            var quotd_remark = quotation_details_data[key].quotd_remark ? quotation_details_data[key].quotd_remark : "";

            var in_use = quotation_details_data[key].in_use == true ? true : false;


            // if (jQuery('#QuotationDetailTable tbody').find('#noDetails').length > 0) {
            // jQuery('#QuotationDetailTable tbody').empty();
            // }
            tblHtml += `<tr>`;
            tblHtml += `<td>`;
            tblHtml += in_use == true ? DetailsActionDropdown('editQuotationDetails') : DetailsActionDropdown('editQuotationDetails', 'removeQuotationDetails');
            tblHtml += ` <input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;
            // tblHtml += `<td>         
            //     <div>
            //         <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
            //             <i class="ri-more-2-fill"></i>
            //         </a>
            //         <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
            //             <li><a class="dropdown-item edit-item-btn" onclick="editQuotationDetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i>Edit</a></li>`;
            //             if(in_use == false){
            //                 tblHtml += `    <li><a class="dropdown-item remove-item-btn" onclick="removeQuotationDetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li>`;
            //             }
            //             else{
            //                   tblHtml += `<li></li>`;
            //             }
            //             tblHtml += `  </ul>
            //         </ul>
            //     </div>
            //     <input type="hidden" name="form_indx" value="${formIndx}"/>
            // </td>`;
            tblHtml += `<td>${quotd_inq_number}</td>`;
            tblHtml += `<td>${quotd_inq_date}</td>`;
            tblHtml += `<td>${test_method}</td>`;
            tblHtml += `<td>${type_of_job}</td>`;
            tblHtml += `<td>${job_description}</td>`;
            tblHtml += `<td>${part_no}</td>`;
            tblHtml += `<td>${process_at}</td>`;
            // tblHtml += `<td>${quotd_description}</td>`;
            tblHtml += `<td>${quotd_qty}</td>`;
            tblHtml += `<td>${unit}</td>`;
            tblHtml += `<td>${quotd_rate_unit}</td>`;
            tblHtml += `<td>${rate_unit}</td>`;
            tblHtml += `<td>${quotd_minimum_charge}</td>`;
            tblHtml += `<td>${quotd_minimum_charge_unit}</td>`;
            tblHtml += `<td>${quotd_conveyance_charge}</td>`;
            tblHtml += `<td>${quotd_remark}</td>`;

            tblHtml += `</tr>`;
        }
        jQuery('#QuotationDetailTable tbody').empty();
        jQuery('#QuotationDetailTable tbody').append(tblHtml);
    }
}
// edit time fill table end

// Main Quotataion Form Submit
$('#commonQuotationForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#QuotationModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;
    var dateValue = document.getElementById("quot_date").value.trim();
    if (!isValidDate(dateValue)) {
        toastr.error("Please Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#QuotationModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#QuotationModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#QuotationModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    jQuery('#QuotationModal').find('#submitbtn').prop('disabled', true);

    var formId = jQuery('#QuotationModal').find('#commonQuotationForm').find('#id').val();
    var formUrl = formId != undefined && formId != "" ? "update-quotation" : "store-quotation";
    var data = new FormData(form);
    data.append('quotation_details_data', JSON.stringify(quotation_details_data ?? []));
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (quotation_details_data.length > 0 && !jQuery.isEmptyObject(quotation_details_data)) {
        jQuery.ajax({
            type: 'POST',
            url: formUrl,
            data: data,
            contentType: false,
            processData: false,
            success: function (data) {
                if (data.response_code == 1) {
                    if (formId != undefined && formId != "") {
                        function redirectFn() {
                            window.location.reload();
                        }
                        if (data.url != undefined && data.url != "") {
                            toastSuccessPreview(data.response_message, data.url, redirectFn);
                        } else {
                            toastSuccess(data.response_message, redirectFn);
                        }
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#QuotationModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formId == undefined || formId == "") {
                        function nextFn() {
                            jQuery('#QuotationModal').find('#quot_sequence').prop('readonly', false);
                            document.getElementById("commonQuotationForm").reset();
                            const form = document.getElementById("commonQuotationForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            jQuery('#quot_sequence').focus();
                            $("#quot_customer_id").val('').trigger('change.select2');
                            $("#quot_kind_attn_id").val('').trigger('change.select2');
                            $("#quot_sac_id").val('').trigger('change.select2');
                            $("#quot_prepared_by_id").val(loginUserId).trigger('change.select2');
                            $("#quot_authorised_by_id").val('').trigger('change.select2');
                            $("#quot_copy_from_id").val('').trigger('change.select2');
                            quotation_details_data = [];
                            jQuery('#QuotationDetailTable tbody').empty();
                            jQuery('#QuotationDetailTable tbody').append(`<tr><td colspan="15" class="text-center" id="noDetails">No Quotation Details Added</td></tr>`);
                            // jQuery('#QuotationTCTable tbody').empty();
                            // jQuery('#QuotationRVDTable tbody').empty();

                            jQuery('#QuotationModal').find('#revision_doc_doc').val('');
                            jQuery('#QuotationModal').find('#revision_doc').val('');
                            jQuery('#QuotationModal').find('#revision_doc_prev').attr('href', '#');
                            jQuery('#QuotationModal').find('#revision_doc_prev').addClass('hide');
                            jQuery('#QuotationModal').find('.remove-file').addClass('hide');
                            jQuery('#QuotationModal').find('.fileupload-exists').addClass('hide');
                            jQuery('#QuotationModal').find('#add_new').hide();
                            jQuery('#QuotationModal').find('#preview_btn').hide();
                            getLatestQuotationNo();
                            getPendingCustomer();
                            getPendingListForQuotation();
                            resetQuotationTabToDetails();
                        }
                        if (data.url != undefined && data.url != "") {
                            toastSuccessPreview(data.response_message, data.url, nextFn);
                        } else {
                            toastSuccess(data.response_message, nextFn);
                        }
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#QuotationModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#QuotationModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#QuotationModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#QuotationModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#QuotationModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        var formId = jQuery('#QuotationModal').find('#commonQuotationForm').find('#id').val();
        if (formId != undefined && formId != "") {
            jQuery('#QuotationModal').find('#submitbtn').prop('disabled', true);
            toastr.error('You can not update Quotation.');
        }
        toastr.error('Please Add At Least One Quotation Details.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#QuotationModal').find('#submitbtn').prop('disabled', false);
    }
});
// Main Form Submit End


// Quotation Details Form Submit Start
$('#QuotationDetailsForm').on('submit', function (e) {
    e.preventDefault();
    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    var data = new FormData(document.getElementById('QuotationDetailsForm'));
    var formValue = Object.fromEntries(data.entries());
    var thisModal = jQuery('#QuotationDetailsModal');

    var quotd_qty = formValue.quotd_qty ? parseFloat(formValue.quotd_qty) : 0;
    var used_qty = parseFloat(jQuery('#quotd_qty').attr('min')) || 0;
    if (used_qty > quotd_qty) {
        // toastr.error('Minimum Quotation Qty. Must Be ' + used_qty.toFixed(3));
        toastr.error('Minimum Quotation Qty. Must Be ' + used_qty.toFixed(2));
        return;
    }

    if (formValue.quotd_test_method_id.trim()) {
        var noDuplicate = true;
        if (noDuplicate) {

            var quotd_inq_number = formValue.quotd_inq_number ? formValue.quotd_inq_number : "";
            var quotd_inq_date = formValue.quotd_inq_date ? formValue.quotd_inq_date : "";

            var quotd_test_method_id = formValue.quotd_test_method_id ? formValue.quotd_test_method_id : null;
            var test_method = quotd_test_method_id != '' ? thisModal.find('#quotd_test_method_id option[value="' + quotd_test_method_id + '"]').text() : '';

            var quotd_type_of_job_id = formValue.quotd_type_of_job_id ? formValue.quotd_type_of_job_id : null;
            var type_of_job = quotd_type_of_job_id != '' ? thisModal.find('#quotd_type_of_job_id option[value="' + quotd_type_of_job_id + '"]').text() : '';

            var quotd_job_desc_id = formValue.quotd_job_desc_id ? formValue.quotd_job_desc_id : null;
            var job_description = quotd_job_desc_id != '' ? thisModal.find('#quotd_job_desc_id option[value="' + quotd_job_desc_id + '"]').text() : '';

            var part_no = formValue.quotd_part_no ? formValue.quotd_part_no : "";
            if (part_no == "" || part_no == undefined) {
                var quotd_part_id = formValue.quotd_part_id ? formValue.quotd_part_id : null;
                part_no = quotd_part_id != '' ? thisModal.find('#quotd_part_id option[value="' + quotd_part_id + '"]').text() : '';
            }

            var quotd_process_at_id = formValue.quotd_process_at_id ? formValue.quotd_process_at_id : null;
            var process_at = quotd_process_at_id != '' ? thisModal.find('#quotd_process_at_id option[value="' + quotd_process_at_id + '"]').text() : '';

            var quotd_description = formValue.quotd_description ? formValue.quotd_description : "";

            // var quotd_qty = formValue.quotd_qty ? parseFloat(formValue.quotd_qty).toFixed(3) : "";
            var quotd_qty = formValue.quotd_qty ? parseFloat(formValue.quotd_qty).toFixed(2) : "";

            var quotd_unit_id = formValue.quotd_unit_id ? formValue.quotd_unit_id : null;
            var unit = quotd_unit_id != '' ? thisModal.find('#quotd_unit_id option[value="' + quotd_unit_id + '"]').text() : '';

            var quotd_rate_unit = formValue.quotd_rate_unit ? parseFloat(formValue.quotd_rate_unit).toFixed(2) : "";

            var quotd_rate_unit_id = formValue.quotd_rate_unit_id ? formValue.quotd_rate_unit_id : null;
            var rate_unit = quotd_rate_unit_id != '' ? thisModal.find('#quotd_rate_unit_id option[value="' + quotd_rate_unit_id + '"]').text() : '';

            var quotd_minimum_charge = formValue.quotd_minimum_charge ? parseFloat(formValue.quotd_minimum_charge).toFixed(2) : "";

            var quotd_minimum_charge_unit_id = formValue.quotd_minimum_charge_unit_id ? formValue.quotd_minimum_charge_unit_id : null;
            var minumum_charge = quotd_minimum_charge_unit_id != '' ? thisModal.find('#quotd_minimum_charge_unit_id option[value="' + quotd_minimum_charge_unit_id + '"]').text() : '';

            var quotd_conveyance_charge = formValue.quotd_conveyance_charge ? parseFloat(formValue.quotd_conveyance_charge).toFixed(2) : "";
            var quotd_remark = formValue.quotd_remark ? formValue.quotd_remark : "";


            if (test_method != "") {
                if (formValue.form_type == "edit") {
                    quotation_details_data[formValue.form_index] = formValue;
                    let tblHtml = ``;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editQuotationDetails', 'removeQuotationDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                    </td>`;
                    // tblHtml += `<td>
                    //     <div>
                    //         <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                    //             <i class="ri-more-2-fill"></i>
                    //         </a>
                    //         <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                    //             <li><a class="dropdown-item edit-item-btn" onclick="editQuotationDetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i>Edit</a></li>
                    //             <li><a class="dropdown-item remove-item-btn" onclick="removeQuotationDetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li>
                    //         </ul>
                    //     </div>
                    //     <input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                    // </td>`;
                    tblHtml += `<td>${quotd_inq_number}</td>`;
                    tblHtml += `<td>${quotd_inq_date}</td>`;
                    tblHtml += `<td>${test_method}</td>`;
                    tblHtml += `<td>${type_of_job}</td>`;
                    tblHtml += `<td>${job_description}</td>`;
                    tblHtml += `<td>${part_no}</td>`;
                    tblHtml += `<td>${process_at}</td>`;
                    tblHtml += `<td>${quotd_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${quotd_rate_unit}</td>`;
                    tblHtml += `<td>${rate_unit}</td>`;
                    tblHtml += `<td>${quotd_minimum_charge}</td>`;
                    tblHtml += `<td>${minumum_charge}</td>`;
                    tblHtml += `<td>${quotd_conveyance_charge}</td>`;
                    tblHtml += `<td>${quotd_remark}</td>`;

                    tblHtml += `</tr>`;
                    jQuery('#QuotationDetailTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
                } else {
                    quotation_details_data.push(formValue)
                    let formIndx = quotation_details_data.indexOf(formValue);
                    if (jQuery('#QuotationDetailTable tbody').find('#noDetails').length > 0) {
                        jQuery('#QuotationDetailTable tbody').empty();
                    }

                    let tblHtml = `<tr>`;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editQuotationDetails', 'removeQuotationDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
                    </td>`;
                    // tblHtml += `<td>
                    //     <div>
                    //         <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                    //             <i class="ri-more-2-fill"></i>
                    //         </a>
                    //         <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                    //             <li><a class="dropdown-item edit-item-btn" onclick="editQuotationDetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i>Edit</a></li>
                    //             <li><a class="dropdown-item remove-item-btn" onclick="removeQuotationDetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li>
                    //         </ul>
                    //     </div>
                    //     <input type="hidden" name="form_indx" value="${formIndx}"/>
                    // </td>`;
                    tblHtml += `<td>${quotd_inq_number}</td>`;
                    tblHtml += `<td>${quotd_inq_date}</td>`;
                    tblHtml += `<td>${test_method}</td>`;
                    tblHtml += `<td>${type_of_job}</td>`;
                    tblHtml += `<td>${job_description}</td>`;
                    tblHtml += `<td>${part_no}</td>`;
                    tblHtml += `<td>${process_at}</td>`;
                    tblHtml += `<td>${quotd_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${quotd_rate_unit}</td>`;
                    tblHtml += `<td>${rate_unit}</td>`;
                    tblHtml += `<td>${quotd_minimum_charge}</td>`;
                    tblHtml += `<td>${minumum_charge}</td>`;
                    tblHtml += `<td>${quotd_conveyance_charge}</td>`;
                    tblHtml += `<td>${quotd_remark}</td>`;

                    tblHtml += `</tr>`;
                    jQuery('#QuotationDetailTable tbody').append(tblHtml);
                }
            }
            thisModal.modal('hide');
            this.reset();
        } else {
            toastr.error("Name Is Already Taken");
        }
    } else {
        toastr.error('Please Select Type of Test');
    }
});
// Quotation Details Form Submit End


jQuery('#QuotationModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#QuotationModal');
    thisModal.find('#submitbtn').prop('disabled', false);
    thisModal.find("#id").val("");
    thisModal.find('#quot_sequence').prop('readonly', false);
    thisModal.find('#add_new').hide();

    quotation_details_data = [];
    jQuery('#QuotationDetailTable tbody').empty();
    jQuery('#QuotationDetailTable tbody').append(`<tr><td colspan="15" class="text-center" id="noDetails">No Quotation Details Added</td></tr>`);
    jQuery('#commonQuotationForm').trigger("reset");
    const form = document.getElementById("commonQuotationForm");
    if (form) form.classList.remove('was-validated');

    thisModal.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).prop('checked', false);
    });

    $("#quot_customer_id").val('').trigger('change.select2');
    $("#quot_kind_attn_id").val('').trigger('change.select2');
    $("#quot_sac_id").val('').trigger('change.select2');
    $("#quot_prepared_by_id").val(loginUserId).trigger('change.select2');
    $("#quot_authorised_by_id").val('').trigger('change.select2');
    $("#quot_copy_from_id").val('').trigger('change.select2');

    thisModal.find('#revision_doc_doc').val('');
    thisModal.find('#revision_doc').val('');
    thisModal.find('#revision_doc_prev').attr('href', '#').addClass('hide');
    thisModal.find('.remove-file').addClass('hide');
    thisModal.find('.fileupload-exists').addClass('hide');
});

jQuery('#QuotationDetailsModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#QuotationDetailsModal');
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    jQuery('#QuotationDetailsForm').trigger("reset");

});

jQuery('#QuotationPendingModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    // const input = jQuery('#QuotationDetailsForm #quotd_inqd_id').val() != '' ? document.getElementById('quot_kind_attn_id') : document.getElementById('quot_customer_id');
    const input = quotation_details_data.length > 0 ? document.getElementById('quot_kind_attn_id') : document.getElementById('quot_customer_id');
    if (input) {
        setTimeout(() => {
            input.focus();
            // setTimeout(function () {
            //     let sel = input
            //         .next('.select2-container')
            //         .find('.select2-selection');
            //     sel.attr('tabindex', 0).focus();
            // }, 20);
        }, 100);
    }
});

jQuery('#QuotationModal').on('show.bs.modal', function () {
    jQuery('#quot_prepared_by_id').val(loginUserId).trigger('change.select2');
});

//<--On Work Order Modal Show-->//
jQuery('#QuotationPendingModal').on('show.bs.modal', function (e) {

    var usedItemParts = [];
    var totalItemDisb = 0;
    var formId = jQuery('#commonQuotationForm').find('input[name="id"]').val();

    jQuery('#QuotationDetailTable tbody input[name="form_indx"]').each(function (indx) {
        var frmIndx = jQuery(this).val();
        var prItemId = quotation_details_data[frmIndx].quotd_inqd_id;
        if (prItemId != "" && prItemId != null) {
            usedItemParts.push(Number(prItemId));
        }
    });

    function isItemUsed(pjitemId) {
        if (usedItemParts.includes(Number(pjitemId))) {
            totalItemDisb++;
            return true;
        }
        return false;
    }

    var totalEntry = 0;
    jQuery('#pendingQuotationDataTable tbody tr').each(function (indx) {

        totalEntry++;
        var checkField = jQuery(this).find('input[name="inqd_id[]"]');
        var partId = jQuery(checkField).val();
        // var inUse = isItemUsed(partId);

        // Check if it's the first checkbox
        if (formId == undefined) {

            if (quotation_details_data.length > 0) {
                var inUse = isItemUsed(partId);

            } else {
                if (indx === 0) {
                    var inUse = true;
                }
            }
        } else {
            var inUse = isItemUsed(partId);
        }

        if (inUse) {
            jQuery(checkField).prop('checked', true);
        } else {
            jQuery(checkField).prop('checked', false);
        }

    });

});


// file upload functions
function validateImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.pdf)$/i;
    if (!allowedExtensions.exec(filePath)) {
        return false;
    }
    return true;
}

function QuotfileUpload(e) {
    var form_data = new FormData();

    // Read selected files

    var target = e.target;

    var id = target.id;

    var files = target.files;

    var totalfiles = files.length;

    var oldImg = jQuery('#revision_doc_doc').val();


    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');

    if (totalfiles > 0) {

        var notValid = 0;

        for (var index = 0; index < totalfiles; index++) {

            if (validateImage(files[index].name) == true) {

                form_data.append("docs[]", files[index]);

            } else {

                notValid = 1;

                toastr.error("Only (jpeg,jpg,png,gif,pdf) files are allowed.");

                jQuery('#revision_doc_doc').val('');

                jQuery('#' + id).val('');

                jQuery('#revision_doc_prev').attr('href', '#').addClass('hide');

                jQuery('#revision_doc_remove').removeClass('i-block').addClass('hide');

                e.stopImmediatePropagation();

                return false;

            }

        }

        if (notValid == 0) {
            jQuery('#QuotationModal').find('#submitbtn').prop('disabled', true);
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
                    jQuery('#QuotationModal').find('#submitbtn').prop('disabled', false);
                    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    if (data.response_code == 1) {

                        if (oldImg != "" && oldImg.includes('temp_media/')) {
                            removeMedia(oldImg);
                        }

                        jQuery('#QuotationModal').find('#revision_doc_doc').val(data.files);
                        jQuery('#QuotationModal').find('#revision_doc_prev').attr('href', data.files_url);
                        jQuery('#QuotationModal').find('#revision_doc_prev').removeClass('hide');
                        jQuery('#QuotationModal').find('.fileupload-exists').removeClass('hide');
                        console.log(data.response_message);
                    } else {
                        console.log(data.response_message);
                    }
                },

                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#QuotationModal').find('#submitbtn').prop('disabled', false);
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
            let fileInput = jQuery('#QuotationModal').find('#revision_doc');
            let dataTransfer = new DataTransfer();
            dataTransfer.items.add(new File([""], fileName));
            fileInput[0].files = dataTransfer.files;
            return false;
        }

        if (oldImg != "" && oldImg.includes('temp_media/')) {
            removeMedia(oldImg);
        }

        jQuery('#QuotationModal').find('#revision_doc_doc').val('');
        jQuery('#QuotationModal').find('#revision_doc_prev').attr('href', '#');
        jQuery('#QuotationModal').find('#revision_doc_prev').addClass('hide');
        jQuery('#QuotationModal').find('.remove-file').addClass('hide');
        jQuery('#QuotationModal').find('.fileupload-exists').addClass('hide');
        jQuery('#QuotationModal').find('.remove-file').removeClass('i-block').addClass('hide');

    }

}

function removeFile(e) {
    e.stopImmediatePropagation();

    toastDetailDelete('Are You Sure, You Want <lw-c>To</lw-c> Delete ?', () => {
        var target = e.target;
        var id = target.getAttribute("data-remove");

        var fileName = jQuery('#' + id + '_doc').val();
        var oldImg = jQuery('#QuotationModal').find('#revision_doc_doc').val();

        jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');

        if (oldImg != "" && oldImg.includes('temp_media/')) {
            removeMedia(oldImg);
        }

        jQuery('#QuotationModal').find('#revision_doc_doc').val('');
        jQuery('#QuotationModal').find('#revision_doc').val('');
        jQuery('#QuotationModal').find('#revision_doc_prev').attr('href', '#');
        jQuery('#QuotationModal').find('#revision_doc_prev').addClass('hide');
        jQuery('#QuotationModal').find('.remove-file').addClass('hide');
        jQuery('#QuotationModal').find('.fileupload-exists').addClass('hide');

        jQuery('#QuotationModal').find('.remove-file').removeClass('i-block').addClass('hide');
        jQuery('#QuotationModal').find('.fileupload-preview').html('');
    });
}

function removeMedia(docName) {


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

jQuery('#QuotationDetailsModal').on('change', '#quotd_job_desc_id', function () {
    let jobId = $(this).val();
    let jobText = $(this).find('option:selected').text().trim();

    if (jobId && jobText && jobText !== 'Select Job Description') {
        $('#QuotationDetailsModal').find('#quotd_description').val(jobText);
    }
});

jQuery('#QuotationDetailsModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    let input;
    jQuery("#QuotationDetailTable tbody").length > 0 ? input = document.getElementById('quot_offer_validity') : input = document.getElementsByClassName('btn-soft-secondary');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }
});

function suggestQuotationPartNo(e, $this) {
    var keyevent = e;
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        var jobId = jQuery('#QuotationDetailsModal').find('#quotd_job_desc_id').val() || '';
        jQuery.ajax({
            url: "get-quotation_part_no_list?term=" + encodeURI(search) + "&job_desc_id=" + jobId,
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                if (data.response_code == 1) {
                    jQuery('#quotd_part_no_list').html(data.partNoList);
                }
            }
        });
    }
}