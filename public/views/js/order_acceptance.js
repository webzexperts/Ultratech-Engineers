$('#OrderAcceptanceModal #oa_type_id').on('change', function () {
    OAType();
    var formId = jQuery('#commonOAForm').find('input[name="id"]').val();
    var oa_type_id = jQuery('#commonOAForm').find('#oa_type_id').val();
    if (formId == "" || formId == undefined) {
        getPendingCustomers();
    }
});


function copyQtyUnit() {
    let unit_id = jQuery('#OADetailsModal #oad_unit_id option:selected').val();
    jQuery('#oad_rate_unit_id').val(unit_id).trigger('change.select2');
}

jQuery('#OADetailsModal #oad_unit_id').on('change', function () {
    copyQtyUnit();
})

// Edit order acceptance row click
jQuery('#dyntable tbody').on('click', '.edit-order_acceptance', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["oa_id"]) {
        fetchAndFillOrderAcceptance(data["oa_id"]);
    }
});

// Function to fetch and fill order acceptance data
function fetchAndFillOrderAcceptance(id) {
    if (!id) return;
    jQuery('#OrderAcceptanceModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-order_acceptance",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.oa_data != null) {
                jQuery('#OrderAcceptanceModal').find('#id').val(data.oa_data.oa_id != "" ? data.oa_data.oa_id : "");

                jQuery('#OrderAcceptanceModal').find('#oa_number').val(data.oa_data.oa_number != "" ? data.oa_data.oa_number : "");
                jQuery('#OrderAcceptanceModal').find('#oa_date').val(data.oa_data.oa_date != "" ? data.oa_data.oa_date : "");
                jQuery('#OrderAcceptanceModal').find('#oa_sequence').val(data.oa_data.oa_sequence != "" ? data.oa_data.oa_sequence : "");
                jQuery('#OrderAcceptanceModal').find('#oad_quotd_id').val(data.oa_data.oad_quotd_id != "" ? data.oa_data.oad_quotd_id : "");

                jQuery('#OrderAcceptanceModal').find('#oa_type_id').val(data.oa_data.oa_type_id || '').trigger('change');

                if (zeroToEmpty(data.oa_data.quot_customer_id) !== '') {
                    getPendingCustomers().done(function () {
                        setTimeout(() => {
                            jQuery('#OrderAcceptanceModal').find('#oa_customer_id').val(zeroToEmpty(data.oa_data.oa_customer_id)).trigger('change');
                        }, 100);
                    });
                }

                //jQuery('#OrderAcceptanceModal').find('#oa_customer_id').val(data.oa_data.oa_customer_id || '').trigger('change.select2');


                jQuery('#OrderAcceptanceModal').find('#oa_customer_id').one('change', function () {
                    getCustomerKindAttn().done(function () {
                        jQuery('#OrderAcceptanceModal')
                            .find('#oa_kind_attn_id')
                            .val(data.oa_data.oa_kind_attn_id)
                            .trigger('change.select2');
                    });
                });


                jQuery('#OrderAcceptanceModal').find('#oa_kind_attn_id').val(data.oa_data.oa_kind_attn_id != "" ? data.oa_data.oa_kind_attn_id : "").trigger("change.select2");

                jQuery('#OrderAcceptanceModal').find('#oa_po_number').val(data.oa_data.oa_po_number != "" ? data.oa_data.oa_po_number : "");

                jQuery('#OrderAcceptanceModal').find('#oa_po_date').val(data.oa_data.oa_po_date != "" ? data.oa_data.oa_po_date : "");

                jQuery('#OrderAcceptanceModal').find('#oa_special_note').val(data.oa_data.oa_special_note != "" ? data.oa_data.oa_special_note : "");

                // getAllOANo();
                getAllOANo().done(function () {

                    let copyFromId = data.oa_data.oa_copy_from_id;
                    if (copyFromId) {
                        jQuery('#OrderAcceptanceModal')
                            .find('#oa_copy_from_id')
                            .val(copyFromId) // 🔑 force string
                            .trigger('change.select2');
                    }
                });

                jQuery('#OrderAcceptanceModal').find('#oa_terms_and_conditions').val(data.oa_data.oa_terms_and_conditions != "" ? data.oa_data.oa_terms_and_conditions : "");

                // if (data.oa_data.oa_file_upload != "" && data.oa_data.oa_file_upload != undefined) {
                //     jQuery('#OrderAcceptanceModal').find("#oa_file_upload_doc").val(data.oa_data.oa_file_upload);
                //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_prev').attr('href', uploadURL + data.oa_data.oa_file_upload);
                //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_prev').removeClass('hide');
                //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev-box').removeClass('hide');
                //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_remove').addClass('i-block').removeClass('hide');
                // } else {
                //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_doc').val('');
                //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_prev').attr('href', '#');
                //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_prev').addClass('hide');
                //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_remove').removeClass('i-block').addClass('hide');
                //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev').html('');
                //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev-box').addClass('hide');
                //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev-box').html('');
                // }

                if (data.oa_data.oa_file_upload != "" && data.oa_data.oa_file_upload != undefined) {
                    let fullPath = data.oa_data.oa_file_upload;
                    let fileName = fullPath.split('/').pop();
                    jQuery('#OrderAcceptanceModal').find("#oa_file_upload_doc").val(fullPath);
                    jQuery('#OrderAcceptanceModal').find('#oa_file_upload_prev').attr('href', uploadURL + fullPath).removeClass('hide');
                    jQuery('#OrderAcceptanceModal').find('#oa_file_upload_remove').addClass('i-block').removeClass('hide');
                    jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev-box').removeClass('hide');
                    jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev').html(fileName);
                    let fileInput = jQuery('#OrderAcceptanceModal').find('#oa_file_upload');
                    let newFile = new DataTransfer();
                    newFile.items.add(new File([""], fileName));
                    fileInput[0].files = newFile.files;
                } else {
                    jQuery('#OrderAcceptanceModal').find('#oa_file_upload_doc').val('');
                    jQuery('#OrderAcceptanceModal').find('#oa_file_upload_prev').attr('href', '#').addClass('hide');
                    jQuery('#OrderAcceptanceModal').find('#oa_file_upload_remove').removeClass('i-block').addClass('hide');
                    jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev').html('');
                    jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev-box').addClass('hide');
                    jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev-box').html('');
                    jQuery('#OrderAcceptanceModal').find('#oa_file_upload').val('');
                }

                if (data.oa_details_data != "" && data.oa_details_data.length > 0) {
                    oa_details_data.push(...data.oa_details_data);
                    fillOADetailsTable();
                }

                jQuery('#OrderAcceptanceModal').find('#pending_btn').prop('disabled', true);
                jQuery('#OrderAcceptanceModal').find('#submitbtn').prop('disabled', false);

                const form = document.getElementById("commonOAForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#OrderAcceptanceModal #oa_sequence').focus();
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
            setTimeout(() => {
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            }, 1000);
        }
    });
}

// Reset button click for order acceptance modal
jQuery('#resetbtn').on('click', function () {
    oa_details_data = [];
    jQuery('#OADetailTable tbody').empty();

    var formId = jQuery('#OrderAcceptanceModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonOAForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        jQuery('#OrderAcceptanceModal #oa_sequence').focus();
        // Select the first option
        var firstVal = jQuery('#OrderAcceptanceModal #oa_type_id option:first').val();
        // Set Select2 value
        jQuery('#OrderAcceptanceModal #oa_type_id').val(firstVal).trigger('change');
        jQuery('#OrderAcceptanceModal #oa_customer_id').val('').trigger('change.select2');
        jQuery('#OrderAcceptanceModal #oa_customer_id').val('').trigger('change.select2');
        jQuery('#OrderAcceptanceModal #oa_kind_attn_id').val('').trigger('change.select2');
        jQuery('#OrderAcceptanceModal #oa_copy_from_id').val('').trigger('change.select2');
        jQuery('#OrderAcceptanceModal').find('#oa_file_upload_doc').val('');
        jQuery('#OrderAcceptanceModal').find('#oa_file_upload_prev').attr('href', '#');
        jQuery('#OrderAcceptanceModal').find('#oa_file_upload_prev').addClass('hide');
        jQuery('#OrderAcceptanceModal').find('#oa_file_upload_remove').removeClass('i-block').addClass('hide');
        jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev').html('');
        jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev-box').addClass('hide');
        jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev-box').html('');
        getLatestOANo();
        setSelect2Readonly('#oa_customer_id', false);
    } else {
        fetchAndFillOrderAcceptance(formId);
    }
});

// // get data for edit
// jQuery('#dyntable tbody').on('click', '.edit-order_acceptance', function () {

//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#OrderAcceptanceModal').find('#id').val(data["oa_id"]);
//     jQuery('#OrderAcceptanceModal').modal('show');
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-order_acceptance",
//         type: 'GET',
//         data: "id=" + data["oa_id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {

//                 jQuery('#OrderAcceptanceModal').find('#id').val(data.oa_data.oa_id != "" ? data.oa_data.oa_id : "");

//                 jQuery('#OrderAcceptanceModal').find('#oa_number').val(data.oa_data.oa_number != "" ? data.oa_data.oa_number : "");
//                 jQuery('#OrderAcceptanceModal').find('#oa_date').val(data.oa_data.oa_date != "" ? data.oa_data.oa_date : "");
//                 jQuery('#OrderAcceptanceModal').find('#oa_sequence').val(data.oa_data.oa_sequence != "" ? data.oa_data.oa_sequence : "");
//                 jQuery('#OrderAcceptanceModal').find('#oad_quotd_id').val(data.oa_data.oad_quotd_id != "" ? data.oa_data.oad_quotd_id : "");

//                 jQuery('#OrderAcceptanceModal').find('#oa_type_id').val(data.oa_data.oa_type_id || '').trigger('change');

//                  if (zeroToEmpty(data.oa_data.quot_customer_id) !== '') {
//                      getPendingCustomers().done(function () {
//                             setTimeout(() => {
//                                 jQuery('#OrderAcceptanceModal').find('#oa_customer_id').val(zeroToEmpty(data.oa_data.oa_customer_id)).trigger('change');
//                             }, 100);
//                     });
//                  }

//                 //jQuery('#OrderAcceptanceModal').find('#oa_customer_id').val(data.oa_data.oa_customer_id || '').trigger('change.select2');


//                 jQuery('#OrderAcceptanceModal').find('#oa_customer_id').one('change', function () {
//                     getCustomerKindAttn().done(function () {
//                         jQuery('#OrderAcceptanceModal')
//                             .find('#oa_kind_attn_id')
//                             .val(data.oa_data.oa_kind_attn_id)
//                             .trigger('change.select2');
//                     });
//                 });


//                 jQuery('#OrderAcceptanceModal').find('#oa_kind_attn_id').val(data.oa_data.oa_kind_attn_id != "" ? data.oa_data.oa_kind_attn_id : "").trigger("change.select2");

//                 jQuery('#OrderAcceptanceModal').find('#oa_po_number').val(data.oa_data.oa_po_number != "" ? data.oa_data.oa_po_number : "");

//                 jQuery('#OrderAcceptanceModal').find('#oa_po_date').val(data.oa_data.oa_po_date != "" ? data.oa_data.oa_po_date : "");

//                 jQuery('#OrderAcceptanceModal').find('#oa_special_note').val(data.oa_data.oa_special_note != "" ? data.oa_data.oa_special_note : "");

//                 // getAllOANo();
//                 getAllOANo().done(function () {

//                     let copyFromId = data.oa_data.oa_copy_from_id;
//                     if (copyFromId) {
//                         jQuery('#OrderAcceptanceModal')
//                             .find('#oa_copy_from_id')
//                             .val(copyFromId) // 🔑 force string
//                             .trigger('change.select2');
//                     }
//                 });

//                 jQuery('#OrderAcceptanceModal').find('#oa_terms_and_conditions').val(data.oa_data.oa_terms_and_conditions != "" ? data.oa_data.oa_terms_and_conditions : "");

//                 // if (data.oa_data.oa_file_upload != "" && data.oa_data.oa_file_upload != undefined) {
//                 //     jQuery('#OrderAcceptanceModal').find("#oa_file_upload_doc").val(data.oa_data.oa_file_upload);
//                 //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_prev').attr('href', uploadURL + data.oa_data.oa_file_upload);
//                 //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_prev').removeClass('hide');
//                 //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev-box').removeClass('hide');
//                 //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_remove').addClass('i-block').removeClass('hide');
//                 // } else {
//                 //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_doc').val('');
//                 //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_prev').attr('href', '#');
//                 //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_prev').addClass('hide');
//                 //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_remove').removeClass('i-block').addClass('hide');
//                 //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev').html('');
//                 //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev-box').addClass('hide');
//                 //     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev-box').html('');
//                 // }

//                 if (data.oa_data.oa_file_upload != "" && data.oa_data.oa_file_upload != undefined) {
//                     let fullPath = data.oa_data.oa_file_upload;
//                     let fileName = fullPath.split('/').pop();
//                     jQuery('#OrderAcceptanceModal').find("#oa_file_upload_doc").val(fullPath);
//                     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_prev').attr('href', uploadURL + fullPath).removeClass('hide');
//                     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_remove').addClass('i-block').removeClass('hide');
//                     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev-box').removeClass('hide');
//                     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev').html(fileName);
//                     let fileInput = jQuery('#OrderAcceptanceModal').find('#oa_file_upload');
//                     let newFile = new DataTransfer();
//                     newFile.items.add(new File([""], fileName));
//                     fileInput[0].files = newFile.files;
//                 } else {
//                     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_doc').val('');
//                     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_prev').attr('href', '#').addClass('hide');
//                     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_remove').removeClass('i-block').addClass('hide');
//                     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev').html('');
//                     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev-box').addClass('hide');
//                     jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev-box').html('');
//                     jQuery('#OrderAcceptanceModal').find('#oa_file_upload').val('');
//                 }

//                 if(data.oa_details_data != "" &&  data.oa_details_data.length > 0){
//                     oa_details_data.push(...data.oa_details_data);
//                     fillOADetailsTable();
//                 }

//                 jQuery('#OrderAcceptanceModal').find('#pending_btn').prop('disabled', true);
//                 jQuery('#OrderAcceptanceModal').find('#submitbtn').prop('disabled', false);
//                 setTimeout(() => {
//                     jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
//                 }, 1000);

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

jQuery('#OADetailsModal').on('change', '#oad_job_desc_id', function () {
    let parts = $(this).find(':selected').data('parts');
    let $partSelect = $('#OADetailsModal').find('#oad_part_id');
    $partSelect.empty().append('<option value="">Select Part No.</option>');
    // $partSelect.empty().append('<option value="">Select Part</option>');
    if (parts && parts.length > 0) {
        $.each(parts, function (i, part) {
            $partSelect.append(
                `<option value="${part.part_id}">
                    ${part.part}
                </option>`
            );
        });
    }
    $partSelect.trigger('change');
});

function getCustomerKindAttn() {
    let thisForm = jQuery('#commonOAForm');
    let customer_id = thisForm.find('#oa_customer_id option:selected').val();
    if (customer_id != "") {
        return jQuery.ajax({
            url: "quotation_kind_attn?customer_id=" + customer_id,
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                var kindDrpHtml = `<option value="">Select Kind Attention</option>`;
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
                setTimeout(() => {
                    thisForm.find('#oa_kind_attn_id').empty().append(kindDrpHtml).trigger('change');
                }, 500);
            }
        });
    } else {
        var kindDrpHtml = `<option value="">Select Kind Attention</option>`;
        thisForm.find('#oa_kind_attn_id').empty().append(kindDrpHtml);
        return jQuery.Deferred().resolve();
    }
}

jQuery('#commonOAForm #oa_customer_id').on('change', function () {
    getCustomerKindAttn();
});

function getAllOANo() {
    let thisForm = jQuery('#commonOAForm');
    var formoaId = jQuery('#commonOAForm').find('input[name="id"]').val();
    if (formoaId == undefined) {
        var Url = "get_all_oa_no";
    } else {
        var Url = "get_all_oa_no?id=" + formoaId;
    }
    return jQuery.ajax({
        url: Url,
        type: 'GET',
        dataType: 'json',
        processData: false,
        success: function (data) {
            var CopyDrpHtml = `<option value="">Select Copy From</option>`;
            if (data.response_code == 1 && data.oa_terms.length) {
                for (let indx in data.oa_terms) {
                    let item = data.oa_terms[indx];
                    let displayText = item.oa_number;
                    CopyDrpHtml += `<option value="${item.oa_id}">${displayText}</option>`;
                }
            }

            thisForm.find('#oa_copy_from_id').empty().append(CopyDrpHtml);
        }
    });

}

jQuery('#oa_copy_from_id').on('change', function () {
    getTermsAndConditions();
})
// get terms and conditions as per selection
function getTermsAndConditions() {
    let thisForm = jQuery('#commonOAForm');
    let oa_id = thisForm.find('#oa_copy_from_id option:selected').val();
    if (oa_id != "") {
        toastConfirm("Do You Want to Replace Terms & Conditions?", function () {
            return jQuery.ajax({
                url: "get_oa_terms_and_conditions?oa_id=" + oa_id,
                type: 'GET',
                dataType: 'json',
                processData: false,
                success: function (data) {
                    if (data.response_code == 1) {
                        jQuery('#OrderAcceptanceModal').find('#oa_terms_and_conditions').val(data.terms_conditions.oa_terms_and_conditions);
                    } else {
                        jQuery('#OrderAcceptanceModal').find('#oa_terms_and_conditions').val('');
                    }
                },
                complete: function () {
                    setTimeout(() => {
                        jQuery('#OrderAcceptanceModal').find('#oa_terms_and_conditions').trigger('focus');
                    }, 100);
                }
            });
        });

    } else {
        setTimeout(() => {
            thisForm.find('#oa_copy_from_id').trigger('focus');
        }, 100);
    }
}

$('#OrderAcceptanceModal #oa_customer_id').on('change', function () {
    fillPendingOA();
});

oa_details_data = [];

var formId = jQuery('#commonOAForm').find('input[name="id"]').val();
jQuery('#OrderAcceptanceModal').on('shown.bs.modal', function () {
    var hasAccess = jQuery('#commonOAForm').find('#has_access').val();
    var formId = jQuery('#commonOAForm').find('input[name="id"]').val();


    if (formId == "" || formId == undefined) {
        jQuery('#commonOAForm #oa_type_id').val('From Quotation').trigger('change');
        getLatestOANo();
        getAllOANo();
    }
    const input = document.getElementById('oa_sequence');
    input?.focus();

});

jQuery('#OrderAcceptanceModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#OrderAcceptanceModal');
    thisModal.find("#id").val("");
    var thisForm = jQuery('#OADetailsModal');
    thisForm.find("#oad_quotd_id").val(0);
    oa_details_data = [];

    jQuery('#OrderAcceptanceModal').find('#oa_file_upload_doc').val('');
    jQuery('#OrderAcceptanceModal').find('#oa_file_upload_prev').attr('href', '#');
    jQuery('#OrderAcceptanceModal').find('#oa_file_upload_prev').addClass('hide');
    jQuery('#OrderAcceptanceModal').find('#oa_file_upload_remove').removeClass('i-block').addClass('hide');
    jQuery('#OADetailTable tbody').empty();
    jQuery('#commonOAForm').trigger("reset");
    thisModal.find('#oa_type_id').val('').trigger('change');
    thisModal.find('#oa_customer_id').val('').trigger('change.select2');
    thisModal.find('#oa_kind_attn_id').val('').trigger('change.select2');
    // thisModal.find('input, textarea, select').each(function () {
    //     jQuery(this).val('');
    //     jQuery(this).prop('checked', false);
    // });
});

jQuery('#OAPendingModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    let input;
    jQuery("#OADetailTable tbody #noDetails").length > 0 ? input = document.getElementById('oa_customer_id') : input = document.getElementById('oa_kind_attn_id');

    if (input) {
        setTimeout(function () {
            input.focus();
        }, 100);
    }
});

jQuery('#OADetailsModal').on('show.bs.modal', function (e) {
    var oad_id = jQuery('#OADetailsForm #oad_quotd_id').val();
    var formId = jQuery('#commonOAForm').find('input[name="id"]').val();

    if ((oad_id > 0 || oad_id > "0") && (formId != "")) {

        setTimeout(() => {
            setSelect2Readonly('#OADetailsForm #oad_test_method_id', true);
            setSelect2Readonly('#OADetailsForm #oad_type_of_job_id', true);
            setSelect2Readonly('#OADetailsForm #oad_job_desc_id', true);
            setSelect2Readonly('#OADetailsForm #oad_part_id', true);
            setSelect2Readonly('#OADetailsForm #oad_process_at_id', true);
            setSelect2Readonly('#OADetailsForm #oad_unit_id', true);
            jQuery('#OADetailsForm #oad_qty').prop('readonly', true);
            jQuery('#OADetailsForm #oad_description').prop('readonly', true).addClass('skip-tab');
            jQuery('#OADetailsForm #oad_rate_unit').prop('readonly', true);
            jQuery('#OADetailsForm #oad_remark').prop('readonly', true).attr('tabindex', -1);
        }, 500);
    } else if (oad_id == 0 && formId != "") {

        setTimeout(() => {
            setSelect2Readonly('#OADetailsForm #oad_test_method_id', true);
            setSelect2Readonly('#OADetailsForm #oad_type_of_job_id', true);
            setSelect2Readonly('#OADetailsForm #oad_job_desc_id', true);
            setSelect2Readonly('#OADetailsForm #oad_part_id', true);
            setSelect2Readonly('#OADetailsForm #oad_process_at_id', true);
            setSelect2Readonly('#OADetailsForm #oad_unit_id', true);
            jQuery('#OADetailsForm #oad_qty').prop('readonly', false);
            jQuery('#OADetailsForm #oad_description').prop('readonly', true).addClass('skip-tab');
            jQuery('#OADetailsForm #oad_rate_unit').prop('readonly', false);
            jQuery('#OADetailsForm #oad_remark').prop('readonly', true).attr('tabindex', -1);

        }, 500);

    } else if (oad_id > 0 && formId == "") {

        setTimeout(() => {
            setSelect2Readonly('#OADetailsForm #oad_test_method_id', true);
            setSelect2Readonly('#OADetailsForm #oad_type_of_job_id', true);
            jQuery('#OADetailsForm #oad_job_desc_id').focus();

        }, 500);

    } else {

        setTimeout(() => {
            setSelect2Readonly('#OADetailsForm #oad_test_method_id', false);
            setSelect2Readonly('#OADetailsForm #oad_type_of_job_id', false);
            setSelect2Readonly('#OADetailsForm #oad_job_desc_id', false);
            setSelect2Readonly('#OADetailsForm #oad_part_id', false);
            setSelect2Readonly('#OADetailsForm #oad_process_at_id', false);
            setSelect2Readonly('#OADetailsForm #oad_unit_id', false);
            jQuery('#OADetailsForm #oad_description').prop('readonly', false).removeClass('skip-tab');;
            jQuery('#OADetailsForm #oad_qty').prop('readonly', false);
            jQuery('#OADetailsForm #oad_rate_unit').prop('readonly', false);
            jQuery('#OADetailsForm #oad_remark').prop('readonly', false).attr('tabindex', 0);
            // jQuery('#OADetailsForm #oad_remark').prop('readonly',false).attr('tabindex',1);

        }, 500);
    }
    var mode = jQuery("#OADetailsForm #form_type").val();
    var pod_id = jQuery("#OADetailsForm #pod_id").val();
    if (mode == "add" && pod_id == "0") {
        jQuery('#OADetailsForm #oad_qty').removeAttr('min');
    }

});

jQuery('#OADetailsModal').on('hide.bs.modal', function (e) {

    let thisModal = jQuery('#OADetailsModal');
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    jQuery('#OADetailsForm').trigger("reset");

    // Enable all select2 fields
    setSelect2Readonly('#OADetailsForm #oad_test_method_id', false);
    setSelect2Readonly('#OADetailsForm #oad_type_of_job_id', false);
    setSelect2Readonly('#OADetailsForm #oad_job_desc_id', false);
    setSelect2Readonly('#OADetailsForm #oad_part_id', false);
    setSelect2Readonly('#OADetailsForm #oad_process_at_id', false);
    setSelect2Readonly('#OADetailsForm #oad_unit_id', false);
    setSelect2Readonly('#OADetailsForm #oad_rate_unit_id', false);

    // Reset input fields
    jQuery('#OADetailsForm #oad_qty').prop('readonly', false);
    jQuery('#OADetailsForm #oad_rate_unit').prop('readonly', false);

    jQuery('#OADetailsForm #oad_description')
        .prop('readonly', false)
        .removeClass('skip-tab');

    jQuery('#OADetailsForm #oad_remark')
        .prop('readonly', false)
        .removeAttr('tabindex');

    this.dataset.customHideFocus = 'true';
    setTimeout(() => {
        const input = document.getElementById('oa_file_upload');
        input?.focus();
    }, 100);

});


jQuery('#OrderAcceptanceModal').on('show.bs.modal', function (e) {

    var formId = jQuery('#commonOAForm').find('input[name="id"]').val();
    var oa_type = jQuery('#OrderAcceptanceModal #oa_type_id').val();

    if (formId != "" && formId != undefined) {
        setTimeout(() => {
            setSelect2Readonly('#commonOAForm #oa_customer_id', true);
            setSelect2Readonly('#commonOAForm #oa_type_id', true);
        }, 800);
    } else {
        // getGRNLNRData();
    }

    if (oa_type == "From Quotation") {
        jQuery('#OrderAcceptanceModal .add_detail').prop('readonly', true);
    } else {
        jQuery('#OrderAcceptanceModal .add_detail').prop('readonly', false);
    }

});


function OAType() {
    let oaType = jQuery('#oa_type_id option:selected').val();
    var formId = jQuery('#commonOAForm').find('input[name="id"]').val();

    if (oaType == 'From Quotation') {
        if (formId != "" && formId != undefined) {
            jQuery('#commonOAForm').find('.toggleModalBtn').prop('disabled', true);
            jQuery('#commonOAForm').find('.add_detail').prop('disabled', true);

        } else {
            // jQuery('#commonOAForm').find('.toggleModalBtn').prop('disabled', false);
        }
        jQuery('#commonOAForm').find('.toggleModalBtn').focus();
        setSelect2Readonly('#GrnDetailsModal #grnd_item_id', true);
        jQuery('#GrnDetailsModal #grnd_description').attr('readonly', true);
    } else if (oaType == 'Manual') {
        jQuery('#commonOAForm').find('.toggleModalBtn').prop('disabled', true);
        jQuery('#commonOAForm').find('.toggleModalBtn').blur();
        setSelect2Readonly('#GrnDetailsModal #grnd_item_id', false);
        jQuery('#GrnDetailsModal #grnd_description').attr('readonly', false);
        jQuery('#commonOAForm').find('.add_detail').prop('disabled', false);

    } else {
        jQuery('#commonOAForm').find('.toggleModalBtn').prop('disabled', true);
        jQuery('#commonOAForm').find('.toggleModalBtn').blur();
        setSelect2Readonly('#GrnDetailsModal #grnd_item_id', false);
        jQuery('#GrnDetailsModal #grnd_description').attr('readonly', false);
        jQuery('#commonOAForm').find('.add_detail').prop('disabled', false);

    }
}

// get pending customer from quotation
function getPendingCustomers() {

    var oa_type_id = jQuery('#oa_type_id option:selected').val();
    var formId = jQuery('#commonOAForm').find('input[name="id"]').val();

    if (formId != undefined && formId != "") {
        var Url = 'get-pending_customer_for_oa?oa_type_id=' + oa_type_id + "&id=" + formId;
    } else {
        var Url = 'get-pending_customer_for_oa?oa_type_id=' + oa_type_id;
    }

    if (oa_type_id != undefined) {
        if (oa_type_id == "From Quotation") {

            return jQuery.ajax({
                url: Url,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    let custHtml = '';
                    custHtml += `<option value="">Select Customer</option> `;
                    if (data.response_code == 1) {
                        for (let indx in data.get_oa_customer) {
                            custHtml += `<option value="${data.get_oa_customer[indx].id}">${data.get_oa_customer[indx].customer}</option>`;

                        }
                        jQuery('#commonOAForm').find('#oa_customer_id').empty().append(custHtml);
                        // jQuery('#oa_customer_id').empty().append(custHtml).select2().trigger('select2:select');

                    } else {
                        console.log(data.response_message)
                    }
                },
            });

        } else {
            return jQuery.ajax({
                url: "get-pending_customer_for_oa",
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    let custHtml = '';
                    custHtml += `<option value="">Select Customer</option> `;
                    if (data.response_code == 1) {
                        for (let indx in data.get_oa_customer) {
                            custHtml += `<option value="${data.get_oa_customer[indx].id}">${data.get_oa_customer[indx].customer}</option>`;

                        }
                        jQuery('#commonOAForm').find('#oa_customer_id').empty().append(custHtml);
                        // jQuery('#oa_customer_id').empty().append(custHtml).select2().trigger('select2:select');

                    } else {
                        console.log(data.response_message)
                    }
                },
            });
        }
    }
    return jQuery.Deferred().resolve();
}

async function fillPendingOA() {
    // async function fillPendingGrn() {
    // return new Promise((resolve, reject) => {
    let cusId = jQuery('#oa_customer_id option:selected').val();
    var oa_type_id = jQuery('#oa_type_id option:selected').val();
    var formId = jQuery('#commonOAForm').find('input[name="id"]').val();


    var thisModal = jQuery('#OAPendingModal');
    var thisForm = jQuery('#OADetailsForm');

    if (cusId != "" && oa_type_id == 'From Quotation') {
        if (formId == undefined) {
            var Url = "get-pending_quot_list_for_oa?oa_customer_id=" + cusId;
        } else {
            var Url = "get-pending_quot_list_for_oa?oa_customer_id=" + cusId + "&id=" + formId;
        }

        jQuery.ajax({
            url: Url,
            type: 'GET',
            headers: headerOpt,
            dataType: 'json',
            processData: false,
            success: function (data) {
                if (data.response_code == 1 && data.quot_data.length > 0) {
                    // new code
                    var usedParts = [];
                    var totalDisb = 0;
                    var found = 0;

                    thisForm.find('#OADetailTable tbody input[name="form_indx"]').each(function (indx) {
                        let frmIndx = jQuery(this).val();

                        let jbEorkOrderId = quot_data[frmIndx].oad_id;
                        if (jbEorkOrderId != "" && jbEorkOrderId != null) {
                            usedParts.push(Number(jbEorkOrderId));
                        }
                    });

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
                    if (data.quot_data.length > 0 && !jQuery.isEmptyObject(data.quot_data)) {
                        found = 1;

                        for (let idx in data.quot_data) {
                            var inUse = isUsed(data.quot_data[idx].guotd_id);
                            var in_use = data.quot_data[idx].in_use == true ? 'readonly' : '';
                            totalEntry++;
                            tblHtml += `<tr>
                                <td><input class="checkbox-filter-remove" type="checkbox" name="quotd_id[]" class="simple-check" id="quotd_ids_${data.quot_data[idx].oad_quotd_id}"
                                    value="${data.quot_data[idx].oad_quotd_id}"/></td>
                                <td>${data.quot_data[idx].oad_quot_number}</td>
                                <td>${data.quot_data[idx].oad_quot_date}</td>
                                <td>${data.quot_data[idx].customer != null ? data.quot_data[idx].customer : ""}</td>
                                <td>${data.quot_data[idx].customer_code != null ? data.quot_data[idx].customer_code : ''}</td>
                                <td>${data.quot_data[idx].oad_ref_no_date != null ? data.quot_data[idx].oad_ref_no_date : ''}</td>
                                <td>${data.quot_data[idx].oad_test_method_id != null ? data.quot_data[idx].oad_test_method_id : ''}</td>
                                <td>${data.quot_data[idx].oad_process_at_id != null ? data.quot_data[idx].oad_process_at_id : ''}</td>
                                <td>${data.quot_data[idx].type_of_job != null ? data.quot_data[idx].type_of_job : ''}</td>
                                <td>${data.quot_data[idx].job_description != null ? data.quot_data[idx].job_description : ''}</td>
                                <td>${data.quot_data[idx].part != null ? data.quot_data[idx].part : ''}</td>
                                
                                <td>${parseFloat(data.quot_data[idx].oad_qty).toFixed(3)}</td>
                                <td>${data.quot_data[idx].unit != null ? data.quot_data[idx].unit : ''}</td>
                              
                                <td>${data.quot_data[idx].oad_remark != null ? data.quot_data[idx].oad_remark : ''}</td>
                                </tr>`;

                        }

                    } else {

                        tblHtml += `<tr class="centeralign" id="noPendingPo">
                                            <td colspan="8">No Pending Quotation Available</td>
                                        </tr>`;

                    }

                    var $table = jQuery("#OAPendingModal").find('#pendingQuotationDataTable');
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

                    if (oa_type_id == 'Manual') {
                        jQuery('.toggleModalBtn').prop('disabled', true);
                    } else {
                        jQuery('.toggleModalBtn').prop('disabled', false);
                    }

                } else {
                    jQuery('.toggleModalBtn').prop('disabled', true);
                    //toastr.error(data.response_message);
                }
            },

            error: function (jqXHR, textStatus, errorThrown) {
                jQuery('.toggleModalBtn').prop('disabled', true);
                var errMessage = JSON.parse(jqXHR.responseText);
                if (jqXHR.status == 401) {
                    toastr.error(jqXHR.statusText);
                } else {
                    toastr.error('Something went wrong!');
                    console.log(JSON.parse(jqXHR.responseText));

                }
            }
        });

    } else {
        jQuery('.toggleModalBtn').prop('disabled', true);
    }
    // });
}

// after checked inquirys from pending modal getting data

$('#addPendigOAForm').on('submit', function (e) {

    e.preventDefault();

    jQuery('#full-page-loader')
        .removeClass('hidden-loader')
        .addClass('loader-progress-whole-page');

    jQuery('#OAPendingModal')
        .find('#submitbtn')
        .prop('disabled', true);

    let chkArr = [];
    var formId = jQuery('#commonOAForm').find('input[name="id"]').val();

    jQuery("#addPendigOAForm")
        .find("[id^='quotd_ids_']:checked")
        .each(function () {
            chkArr.push(jQuery(this).val());
        });

    // No checkbox selected
    if (chkArr.length === 0) {
         toastr.error('Select At least One Quotation From Pending');

        jQuery('#full-page-loader')
            .removeClass('loader-progress-whole-page')
            .addClass('hidden-loader');

        jQuery('#OAPendingModal')
            .find('#submitbtn')
            .prop('disabled', false);

        return;
    }

    if (formId != undefined && formId != "") {
        var pend_url = "get-pending_quotation_for_oa?id=" + formId;
    } else {
        var pend_url = "get-pending_quotation_for_oa";
    }

    jQuery.ajax({
        url: pend_url,
        type: 'GET',
        data: { quotd_ids: chkArr.join(',') },
        dataType: 'json',
        success: function (data) {

            if (data.response_code == 1) {

                if (data.quot_data && data.quot_data.length > 0) {
                    oa_details_data = [];
                    for (let ind in data.quot_data) {
                        oa_details_data.push(data.quot_data[ind]);
                    }
                    fillOADetailsTable(data.quot_data);
                    setSelect2Readonly("#oa_customer_id", true);
                } else {
                    fillOADetailsTable([]);
                    setSelect2Readonly("#oa_customer_id", false);
                }

                jQuery("#OAPendingModal").modal('hide');
            } else {
                fillOADetailsTable([]);
                setSelect2Readonly("#oa_customer_id", false);
            }

            jQuery('#OAPendingModal')
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

            jQuery('#OAPendingModal')
                .find('#submitbtn')
                .prop('disabled', false);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');
        }
    });
});

// edit OA details
function editOADetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();
    fillOADetailsForm(formIndx, rawIndx);
}

// OA details form edit
function fillOADetailsForm(formIndx, rawIndx) {

    let thisForm = jQuery('#OADetailsModal');
    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    var frmData = oa_details_data[formIndx];
    // console.log("frmdata",frmData);
    thisForm.find("#oad_id").val(frmData.oad_id ? frmData.oad_id : "0");
    thisForm.find("#oad_quotd_id").val(frmData.oad_quotd_id ? frmData.oad_quotd_id : "0");
    thisForm.find("#oad_quot_number").val(frmData.oad_quot_number ? frmData.oad_quot_number : "");
    thisForm.find("#oad_quot_date").val(frmData.oad_quot_date ? frmData.oad_quot_date : "");
    thisForm.find("#oad_test_method_id").val(zeroToEmpty(frmData.oad_test_method_id)).trigger("change");
    thisForm.find("#oad_type_of_job_id").val(zeroToEmpty(frmData.oad_type_of_job_id)).trigger("change");
    thisForm.find("#oad_job_desc_id").val(zeroToEmpty(frmData.oad_job_desc_id)).trigger("change");
    setTimeout(() => {
        thisForm.find("#oad_part_id").val(zeroToEmpty(frmData.oad_part_id)).trigger("change.select2");
    }, 20);
    thisForm.find("#oad_description").val(frmData.oad_description);
    thisForm.find("#oad_process_at_id").val(zeroToEmpty(frmData.oad_process_at_id)).trigger("change");
    thisForm.find("#oad_qty").val(frmData.oad_qty != "" ? parseFloat(frmData.oad_qty).toFixed(3) : "");
    thisForm.find("#oad_unit_id").val(zeroToEmpty(frmData.oad_unit_id)).trigger("change");
    thisForm.find("#oad_rate_unit").val(frmData.oad_rate_unit != "" && frmData.oad_rate_unit != undefined ? parseFloat(frmData.oad_rate_unit).toFixed(2) : "");
    thisForm.find("#oad_rate_unit_id").val(zeroToEmpty(frmData.oad_rate_unit_id)).trigger("change");
    thisForm.find("#oad_minimum_charge").val(frmData.oad_minimum_charge != "" && frmData.oad_minimum_charge != undefined ? parseFloat(frmData.oad_minimum_charge).toFixed(2) : "");
    thisForm.find("#oad_minimum_charge_unit_id").val(zeroToEmpty(frmData.oad_minimum_charge_unit_id)).trigger("change");
    thisForm.find("#oad_conveyance_charge").val(frmData.oad_conveyance_charge != undefined && frmData.oad_conveyance_charge != "" ? parseFloat(frmData.oad_conveyance_charge).toFixed(2) : "");
    thisForm.find("#oad_remark").val(frmData.oad_remark);

    if (frmData.oad_quot_number != undefined && frmData.oad_quot_number != "") {
        thisForm.find("#oad_qty").attr('max', parseFloat(frmData.pend_oa_qty).toFixed(3));
    }
    if (frmData.in_use == true) {
        thisForm.find("#oad_qty").attr('min', parseFloat(frmData.used_qty).toFixed(3));
    }


    thisForm.modal('show');
}

// remove quotation details
function removeOADetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        removeFormQuotObj(formIndx);
        jQuery(th).closest("tr").remove();
    });
}

function removeFormQuotObj(formIndx) {
    delete oa_details_data[formIndx];
    oa_details_data = oa_details_data.filter(element => element != null);
    jQuery('#OADetailTable tbody').empty();
    fillOADetailsTable();
}


// edit time fill quotation table start
function fillOADetailsTable() {
    let thisModal = jQuery('#OADetailsModal');
    let tblHtml = ``;
    if (oa_details_data.length > 0) {
        for (let key in oa_details_data) {

            var formIndx = oa_details_data.indexOf(oa_details_data[key]);

            var oad_quot_number = oa_details_data[key].oad_quot_number ? oa_details_data[key].oad_quot_number : "";
            var oad_quot_date = oa_details_data[key].oad_quot_date ? oa_details_data[key].oad_quot_date : "";

            var oad_test_method_id = oa_details_data[key].oad_test_method_id ? oa_details_data[key].oad_test_method_id : null;

            var test_method = oad_test_method_id != '' ? thisModal.find('#oad_test_method_id option[value="' + oad_test_method_id + '"]').text() : '';

            var oad_type_of_job_id = oa_details_data[key].oad_type_of_job_id ? oa_details_data[key].oad_type_of_job_id : null;
            var type_of_job = oad_type_of_job_id != '' ? thisModal.find('#oad_type_of_job_id option[value="' + oad_type_of_job_id + '"]').text() : '';

            var oad_job_desc_id = oa_details_data[key].oad_job_desc_id ? oa_details_data[key].oad_job_desc_id : null;
            var job_description = oad_job_desc_id != '' ? thisModal.find('#oad_job_desc_id option[value="' + oad_job_desc_id + '"]').text() : '';

            var oad_part_id = oa_details_data[key].oad_part_id > 0 ? oa_details_data[key].oad_part_id : null;
            var part_no = oad_part_id != '' ? thisModal.find('#oad_part_id option[value="' + oad_part_id + '"]').text() : '';

            if (part_no == "" || part_no == undefined || part_no == 0) {
                part_no = oa_details_data[key].part ? oa_details_data[key].part : "";
            }

            var oad_process_at_id = oa_details_data[key].oad_process_at_id ? oa_details_data[key].oad_process_at_id : '';
            var process_at = oad_process_at_id != '' ? thisModal.find('#oad_process_at_id option[value="' + oad_process_at_id + '"]').text() : '';

            var oad_description = oa_details_data[key].oad_description ? oa_details_data[key].oad_description : "";

            var oad_qty = oa_details_data[key].oad_qty ? parseFloat(oa_details_data[key].oad_qty).toFixed(3) : "";

            var oad_unit_id = oa_details_data[key].oad_unit_id ? oa_details_data[key].oad_unit_id : null;
            var unit = oad_unit_id != '' ? thisModal.find('#oad_unit_id option[value="' + oad_unit_id + '"]').text() : '';

            var oad_rate_unit = oa_details_data[key].oad_rate_unit ? parseFloat(oa_details_data[key].oad_rate_unit).toFixed(2) : "";

            var oad_rate_unit_id = oa_details_data[key].oad_rate_unit_id ? oa_details_data[key].oad_rate_unit_id : null;
            var rate_unit = oad_rate_unit_id != '' ? thisModal.find('#oad_rate_unit_id option[value="' + oad_rate_unit_id + '"]').text() : '';

            var oad_minimum_charge = oa_details_data[key].oad_minimum_charge ? parseFloat(oa_details_data[key].oad_minimum_charge).toFixed(2) : "";

            var oad_minimum_charge_unit_id = oa_details_data[key].oad_minimum_charge_unit_id ? oa_details_data[key].oad_minimum_charge_unit_id : null;
            var oad_minimum_charge_unit = oad_minimum_charge_unit_id != '' ? thisModal.find('#oad_minimum_charge_unit_id option[value="' + oad_minimum_charge_unit_id + '"]').text() : '';

            var oad_conveyance_charge = oa_details_data[key].oad_conveyance_charge ? parseFloat(oa_details_data[key].oad_conveyance_charge).toFixed(2) : "";

            var oad_remark = oa_details_data[key].oad_remark ? oa_details_data[key].oad_remark : "";

            var in_use = oa_details_data[key].in_use == true ? true : false;



            tblHtml += `<tr>`;
            tblHtml += `<td>`;
            tblHtml += in_use == true ? DetailsActionDropdown('editOADetails') : DetailsActionDropdown('editOADetails', 'removeOADetails');
            tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;
            // tblHtml += `<td>         
            //     <div>
            //         <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
            //             <i class="ri-more-2-fill"></i>
            //         </a>
            //         <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
            //             <li><a class="dropdown-item edit-item-btn" onclick="editOADetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i>Edit</a></li>
            //             <li><a class="dropdown-item remove-item-btn" onclick="removeOADetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li>
            //         </ul>
            //     </div>
            //     <input type="hidden" name="form_indx" value="${formIndx}"/>
            // </td>`;
            tblHtml += `<td>${oad_quot_number}</td>`;
            tblHtml += `<td>${oad_quot_date}</td>`;
            tblHtml += `<td>${test_method}</td>`;
            tblHtml += `<td>${type_of_job}</td>`;
            tblHtml += `<td>${job_description}</td>`;
            tblHtml += `<td>${part_no}</td>`;
            tblHtml += `<td>${process_at}</td>`;
            // tblHtml += `<td>${oad_description}</td>`;
            tblHtml += `<td>${oad_qty}</td>`;
            tblHtml += `<td>${unit}</td>`;
            tblHtml += `<td>${oad_rate_unit}</td>`;
            tblHtml += `<td>${rate_unit}</td>`;
            tblHtml += `<td>${oad_minimum_charge}</td>`;
            tblHtml += `<td>${oad_minimum_charge_unit}</td>`;
            tblHtml += `<td>${oad_conveyance_charge}</td>`;
            tblHtml += `<td>${oad_remark}</td>`;

            tblHtml += `</tr>`;
        }
        jQuery('#OADetailTable tbody').empty();
        jQuery('#OADetailTable tbody').append(tblHtml);
    }
}

// Quotation Details Form Submit Start
$('#OADetailsForm').on('submit', function (e) {
    e.preventDefault();
    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    var data = new FormData(document.getElementById('OADetailsForm'));
    var formValue = Object.fromEntries(data.entries());
    var thisModal = jQuery('#OADetailsModal');

    var currentQty = formValue.oad_qty ? parseFloat(formValue.oad_qty) : 0;
    var maxQtyAllowed = formValue.pend_oa_qty ? parseFloat(formValue.pend_oa_qty) : 0;

    var used_qty = parseFloat(jQuery('#oad_qty').attr('min')) || 0;
    if (currentQty < used_qty) {
        toastr.error('Minimum OA Qty. Must Be ' + used_qty.toFixed(3));
        return;
    }

    if (maxQtyAllowed > 0) {
        if (currentQty > maxQtyAllowed) {
            toastr.error(`Qty. cannot exceed pending Quotation Qty.`);
            return;
        }
    }

    if (currentQty < 0.001) {
        toastr.error('Please Enter Qty. greater than 0.001.');
        return;
    }

    if (formValue.oad_test_method_id.trim()) {
        var noDuplicate = true;
        if (noDuplicate) {

            var oad_quot_number = formValue.oad_quot_number ? formValue.oad_quot_number : "";
            var oad_quot_date = formValue.oad_quot_date ? formValue.oad_quot_date : "";

            var oad_test_method_id = formValue.oad_test_method_id ? formValue.oad_test_method_id : null;
            var test_method = oad_test_method_id != '' ? thisModal.find('#oad_test_method_id option[value="' + oad_test_method_id + '"]').text() : '';

            var oad_type_of_job_id = formValue.oad_type_of_job_id ? formValue.oad_type_of_job_id : null;
            var type_of_job = oad_type_of_job_id != '' ? thisModal.find('#oad_type_of_job_id option[value="' + oad_type_of_job_id + '"]').text() : '';

            var oad_job_desc_id = formValue.oad_job_desc_id ? formValue.oad_job_desc_id : null;
            var job_description = oad_job_desc_id != '' ? thisModal.find('#oad_job_desc_id option[value="' + oad_job_desc_id + '"]').text() : '';

            var oad_part_id = formValue.oad_part_id ? formValue.oad_part_id : null;
            var part_no = oad_part_id != '' ? thisModal.find('#oad_part_id option[value="' + oad_part_id + '"]').text() : '';

            var oad_process_at_id = formValue.oad_process_at_id ? formValue.oad_process_at_id : null;
            var process_at = oad_process_at_id != '' ? thisModal.find('#oad_process_at_id option[value="' + oad_process_at_id + '"]').text() : '';

            var oad_description = formValue.oad_description ? formValue.oad_description : "";

            var oad_qty = formValue.oad_qty ? parseFloat(formValue.oad_qty).toFixed(3) : "";

            var oad_unit_id = formValue.oad_unit_id ? formValue.oad_unit_id : null;
            var unit = oad_unit_id != '' ? thisModal.find('#oad_unit_id option[value="' + oad_unit_id + '"]').text() : '';

            var oad_rate_unit = formValue.oad_rate_unit ? parseFloat(formValue.oad_rate_unit).toFixed(2) : "";

            var oad_rate_unit_id = formValue.oad_rate_unit_id ? formValue.oad_rate_unit_id : null;
            var rate_unit = oad_rate_unit_id != '' ? thisModal.find('#oad_rate_unit_id option[value="' + oad_rate_unit_id + '"]').text() : '';

            var oad_minimum_charge = formValue.oad_minimum_charge ? parseFloat(formValue.oad_minimum_charge).toFixed(2) : "";

            var oad_minimum_charge_unit_id = formValue.oad_minimum_charge_unit_id ? formValue.oad_minimum_charge_unit_id : null;
            var minumum_charge = oad_minimum_charge_unit_id != '' ? thisModal.find('#oad_minimum_charge_unit_id option[value="' + oad_minimum_charge_unit_id + '"]').text() : '';

            var oad_conveyance_charge = formValue.oad_conveyance_charge ? parseFloat(formValue.oad_conveyance_charge).toFixed(2) : "";
            var oad_remark = formValue.oad_remark ? formValue.oad_remark : "";


            if (test_method != "") {
                if (formValue.form_type == "edit") {
                    oa_details_data[formValue.form_index] = formValue;
                    let tblHtml = ``;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editOADetails', 'removeOADetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                    </td>`;
                    // tblHtml += `<td>
                    //     <div>
                    //         <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                    //             <i class="ri-more-2-fill"></i>
                    //         </a>
                    //         <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                    //             <li><a class="dropdown-item edit-item-btn" onclick="editOADetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i>Edit</a></li>
                    //             <li><a class="dropdown-item remove-item-btn" onclick="removeOADetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li>
                    //         </ul>
                    //     </div>
                    //     <input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                    // </td>`;
                    tblHtml += `<td>${oad_quot_number}</td>`;
                    tblHtml += `<td>${oad_quot_date}</td>`;
                    tblHtml += `<td>${test_method}</td>`;
                    tblHtml += `<td>${type_of_job}</td>`;
                    tblHtml += `<td>${job_description}</td>`;
                    tblHtml += `<td>${part_no}</td>`;
                    tblHtml += `<td>${process_at}</td>`;
                    tblHtml += `<td>${oad_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${oad_rate_unit}</td>`;
                    tblHtml += `<td>${rate_unit}</td>`;
                    tblHtml += `<td>${oad_minimum_charge}</td>`;
                    tblHtml += `<td>${minumum_charge}</td>`;
                    tblHtml += `<td>${oad_conveyance_charge}</td>`;
                    tblHtml += `<td>${oad_remark}</td>`;

                    tblHtml += `</tr>`;
                    jQuery('#OADetailTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);

                } else {
                    oa_details_data.push(formValue)
                    let formIndx = oa_details_data.indexOf(formValue);
                    if (jQuery('#OADetailTable tbody').find('#noDetails').length > 0) {
                        jQuery('#OADetailTable tbody').empty();
                    }

                    let tblHtml = `<tr>`;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editOADetails', 'removeOADetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
                    </td>`;
                    // tblHtml += `<td>
                    //     <div>
                    //         <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                    //             <i class="ri-more-2-fill"></i>
                    //         </a>
                    //         <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                    //             <li><a class="dropdown-item edit-item-btn" onclick="editOADetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i>Edit</a></li>
                    //             <li><a class="dropdown-item remove-item-btn" onclick="removeOADetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li>
                    //         </ul>
                    //     </div>
                    //     <input type="hidden" name="form_indx" value="${formIndx}"/>
                    // </td>`;
                    tblHtml += `<td>${oad_quot_number}</td>`;
                    tblHtml += `<td>${oad_quot_date}</td>`;
                    tblHtml += `<td>${test_method}</td>`;
                    tblHtml += `<td>${type_of_job}</td>`;
                    tblHtml += `<td>${job_description}</td>`;
                    tblHtml += `<td>${part_no}</td>`;
                    tblHtml += `<td>${process_at}</td>`;
                    tblHtml += `<td>${oad_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${oad_rate_unit}</td>`;
                    tblHtml += `<td>${rate_unit}</td>`;
                    tblHtml += `<td>${oad_minimum_charge}</td>`;
                    tblHtml += `<td>${minumum_charge}</td>`;
                    tblHtml += `<td>${oad_conveyance_charge}</td>`;
                    tblHtml += `<td>${oad_remark}</td>`;

                    tblHtml += `</tr>`;
                    jQuery('#OADetailTable tbody').append(tblHtml);
                    toastSuccess("Record is inserted successfully.");

                }
            }

            if (formValue.form_type == "edit") {
                thisModal.modal('hide');
            } else {
                let formElement = document.getElementById('OADetailsForm');
                formElement.reset();
                jQuery('#oad_test_method_id').val('').trigger('change.select2');
                jQuery('#oad_type_of_job_id').val('').trigger('change.select2');
                jQuery('#oad_job_desc_id').val('').trigger('change.select2');
                jQuery('#oad_part_id').val('').trigger('change.select2');
                jQuery('#oad_process_at_id').val('').trigger('change.select2');
                jQuery('#oad_desciption').val('');
                jQuery('#oad_qty').val('');
                jQuery('#oad_rate_unit').val('');
                jQuery('#oad_minimum_charge').val('');
                jQuery('#oad_conveyance_charge').val('');
                jQuery('#oad_remark').val('');
                jQuery('#oad_unit_id').val('').trigger('change.select2');
                jQuery('#oad_rate_unit_id').val('').trigger('change.select2');
                jQuery('#oad_minimum_charge_unit_id').val('').trigger('change.select2');
                setTimeout(function () {

                    let $select = jQuery('#OADetailsForm #oad_test_method_id');
                    $select.one('select2:opening', function (e) {
                        e.preventDefault();
                    });

                    let sel = $select.next('.select2-container').find('.select2-selection');
                    if (sel.length) {
                        sel.attr('tabindex', 0).focus();
                    }

                    $select.select2('close');
                }, 150);

                setTimeout(function () {
                    $(formElement).removeClass('was-validated');
                    $(formElement).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                    thisModal.find('.error').removeClass('error');
                }, 150);
            }
        } else {
            toastr.error("Name Is Already Taken");
        }
    } else {
        toastr.error('Please Select Type of Test');
    }
});
// Quotation Details Form Submit End

// Main Form Submit start
$('#commonOAForm').on('submit', function (e) {
    // jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    // jQuery('#OrderAcceptanceModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;
    var dateValue = document.getElementById("oa_date").value.trim();
    if (!isValidDate(dateValue)) {
        toastr.error("Please Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#OrderAcceptanceModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#OrderAcceptanceModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#OrderAcceptanceModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    let isValid = true;
    let errorMsg = '';

    jQuery.each(oa_details_data, function (index, item) {

        if (!item.oad_qty) {
            errorMsg = 'Please Enter Qty.';
            isValid = false;
            return false;
        }

        if (Number(item.oad_qty) > Number(item.pend_oa_qty)) {
            errorMsg = 'Qty cannot exceed pending Qty.';
            isValid = false;
            return false;
        }

    });

    if (!isValid) {
        toastr.error(errorMsg);
        return false;
    }


    jQuery('#OrderAcceptanceModal').find('#submitbtn').prop('disabled', true);
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');


    var formId = jQuery('#OrderAcceptanceModal').find('#commonOAForm').find('#id').val();
    var formUrl = formId != undefined && formId != "" ? "update-order_acceptance" : "store-order_acceptance";
    var data = new FormData(form);
    data.append('oa_details_data', JSON.stringify(oa_details_data ?? []));
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (oa_details_data.length > 0 && !jQuery.isEmptyObject(oa_details_data)) {

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
                        toastSuccess(data.response_message, redirectFn);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#OrderAcceptanceModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formId == undefined || formId == "") {
                        function nextFn() {
                            document.getElementById("commonOAForm").reset();
                            const form = document.getElementById("commonOAForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            jQuery('#OrderAcceptanceModal #oa_sequence').focus();
                            // Select the first option
                            var firstVal = jQuery('#OrderAcceptanceModal #oa_type_id option:first').val();
                            // Set Select2 value
                            jQuery('#OrderAcceptanceModal #oa_type_id').val(firstVal).trigger('change');
                            jQuery('#OrderAcceptanceModal #oa_customer_id').val('').trigger('change.select2');
                            jQuery('#OrderAcceptanceModal #oa_customer_id').val('').trigger('change.select2');
                            jQuery('#OrderAcceptanceModal #oa_kind_attn_id').val('').trigger('change.select2');
                            jQuery('#OrderAcceptanceModal').find('#oa_file_upload_doc').val('');
                            jQuery('#OrderAcceptanceModal').find('#oa_file_upload_prev').attr('href', '#');
                            jQuery('#OrderAcceptanceModal').find('#oa_file_upload_prev').addClass('hide');
                            jQuery('#OrderAcceptanceModal').find('#oa_file_upload_remove').removeClass('i-block').addClass('hide');
                            jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev').html('');
                            jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev-box').addClass('hide');
                            jQuery('#OrderAcceptanceModal').find('#oa_file_upload_img-prev-box').html('');
                            oa_details_data = [];
                            jQuery('#OADetailTable tbody').empty();
                            getLatestOANo();
                            setSelect2Readonly('#oa_customer_id', false);
                        }
                        toastSuccess(data.response_message, nextFn);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#OrderAcceptanceModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#OrderAcceptanceModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#OrderAcceptanceModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#OrderAcceptanceModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#OrderAcceptanceModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        var formId = jQuery('#OrderAcceptanceModal').find('#commonOAForm').find('#id').val();
        if (formId != undefined && formId != "") {
            jQuery('#OrderAcceptanceModal').find('#submitbtn').prop('disabled', true);
            toastr.error('You can not Order Acceptance.');
        }
        toastr.error('Please Add At Least One Order Acceptance Details.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#OrderAcceptanceModal').find('#submitbtn').prop('disabled', false);
    }
});
// Main Form Submit End


jQuery('#OAPendingModal').on('show.bs.modal', function (e) {

    var usedItemParts = [];
    var totalItemDisb = 0;
    var formId = jQuery('#commonOAForm').find('input[name="id"]').val();

    jQuery('#OADetailTable tbody input[name="form_indx"]').each(function (indx) {
        var frmIndx = jQuery(this).val();
        var prItemId = oa_details_data[frmIndx].oad_quotd_id;
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
        var checkField = jQuery(this).find('input[name="quotd_id[]"]');
        var partId = jQuery(checkField).val();
        // var inUse = isItemUsed(partId);

        // Check if it's the first checkbox
        if (formId == undefined) {

            if (oa_details_data.length > 0) {
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

jQuery('#checkall-quot_data').click(function () {
    if (jQuery(this).is(':checked')) {
        jQuery("#OAPendingModal").find("[id^='oad_ids_']:not(.in-use)").prop('checked', true).trigger('change');
    } else {
        jQuery("#OAPendingModal").find("[id^='oad_ids_']:not(.in-use)").prop('checked', false).trigger('change');
    }
});


// file upload functions

function validateImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.pdf)$/i;
    if (!allowedExtensions.exec(filePath)) {
        return false;
    }
    return true;
}

jQuery('#commonOAForm #oa_file_upload').on('change', function (e) {
    OAfileUpload(e);
});

function OAfileUpload(e) {
    var form_data = new FormData();
    var target = e.target;
    var id = target.id;
    var files = target.files;
    var totalfiles = files.length;
    var oldImg = jQuery('#oa_file_upload_doc').val();
    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');
    if (totalfiles > 0) {
        var notValid = 0;
        for (var index = 0; index < totalfiles; index++) {
            if (validateImage(files[index].name) == true) {
                form_data.append("docs[]", files[index]);
            } else {
                notValid = 1;
                toastr.error("Only (jpeg,jpg,png,gif,pdf) files are allowed.");
                jQuery('#' + id).val('');
                e.stopImmediatePropagation();
                return false;
            }
        }

        if (notValid == 0) {
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
                    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    if (data.response_code == 1) {
                        if (oldImg != "") {
                            removeMedia(oldImg);
                        }

                        $('#oa_file_upload_doc').val(data.files);
                        $('#oa_file_upload_prev').attr('href', data.files_url);
                        $('#oa_file_upload_prev').removeClass('hide');
                        $('.fileupload-exists').removeClass('hide');
                        $('#oa_file_upload_remove').removeClass('hide');
                        $('.remove-file').addClass('i-block').removeClass('hide');
                    } else {
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
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
            let fileInput = jQuery('#oa_file_upload');
            let dataTransfer = new DataTransfer();
            dataTransfer.items.add(new File([""], fileName));
            fileInput[0].files = dataTransfer.files;
            return false;
        }

        // if (oldImg != "") {
        //     return false;
        // }

        if (oldImg != "") {
            removeMedia(oldImg);
        }

        $('#oa_file_upload_doc').val('');
        $('#oa_file_upload_prev').attr('href', '#');
        $('#oa_file_upload_prev').addClass('hide');
        $('.remove-file').addClass('hide');
        $('.fileupload-exists').addClass('hide');
        $('.remove-file').removeClass('i-block').addClass('hide');
        $('#oa_file_upload_remove').removeClass('hide');
    }
}

function removeFile(e) {
    e.stopImmediatePropagation();
    toastDetailDelete('Are You Sure, You Want <lw-c>To</lw-c> Delete ?', () => {
        var target = e.target;
        var id = target.getAttribute("data-remove");
        var fileName = jQuery('#' + id + '_doc').val();
        var oldImg = jQuery('#oa_file_upload_doc').val();
        jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');

        if (oldImg != "") {
            removeMedia(oldImg);
        }

        jQuery('#oa_file_upload_doc').val('');
        jQuery('#oa_file_upload').val('');
        jQuery('#oa_file_upload_prev').attr('href', '#');
        jQuery('#oa_file_upload_prev').addClass('hide');
        $('.remove-file').addClass('hide');
        $('.fileupload-exists').addClass('hide');
        jQuery('.remove-file').removeClass('i-block').addClass('hide');
        jQuery('.fileupload-preview').html('');
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


// get the latest number
function getLatestOANo() {
    jQuery.ajax({
        url: "get-latest_oa_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#oa_number').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#oa_sequence').val(data.number);
                jQuery('#oa_date').val(currentDate);
            } else {
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            jQuery('#oa_number').removeClass('file-loader');
            console.log('Field To Get Latest Order Acceptance No.!')
        }
    });
}

// onchange of quotatation sequence
jQuery('#commonOAForm').find('#oa_sequence').on('change', function () {
    checkSequence();
});

// check for duplication sequence
function checkSequence() {

    let thisForm = jQuery('#commonOAForm');
    let val = thisForm.find('#oa_sequence').val();

    if (val != "") {

        if (val > 0 == false) {
            toastr.error('Please Enter Valid Order Acceptance No.');
            jQuery('#oa_sequence').parent().parent().parent('div.control-group').addClass('error');
            jQuery('#oa_sequence').focus();
            jQuery('#oa_sequence').val('');

        } else {
            jQuery('#oa_sequence').addClass('file-loader');
            jQuery('#oa_sequence').parent().parent().parent('div.control-group').removeClass('error');

            var urL = "check-oa_number_duplication?for=add&oa_sequence=" + val;

            var formId = jQuery('#commonOAForm').find('input[name="id"]').val();

            if (formId !== undefined) { //if form is edit
                urL = "check-oa_number_duplication?for=edit&oa_sequence=" + val + "&id=" + formId;
            }

            jQuery.ajax({
                url: urL,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    jQuery('#oa_sequence').removeClass('file-loader');
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        jQuery('#oa_sequence').val('');
                        const input = document.getElementById('oa_sequence'); input?.focus();
                    } else {

                        jQuery('#oa_number').val(data.latest_no);
                        jQuery('#oa_sequence').val(val);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#oa_sequence').removeClass('file-loader');
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
        jQuery('#oa_number').val('');
        jQuery('#oa_sequence').val('');
    }

}

// check all 
jQuery('#checkall-quot_data').click(function () {
    if (jQuery(this).is(':checked')) {
        jQuery("#OAPendingModal").find("[id^='quotd_ids_']:not(.in-use)").prop('checked', true).trigger('change');
    } else {
        jQuery("#OAPendingModal").find("[id^='quotd_ids_']:not(.in-use)").prop('checked', false).trigger('change');
    }
});
