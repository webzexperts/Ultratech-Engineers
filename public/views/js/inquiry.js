var formId = jQuery('#InquiryModal').find('#commonInquiryForm').find('#id').val();
inquiry_details_data = [];
// setSelect2Readonly('#inqd_estimation_required', true);

// Edit inquiry row click
jQuery('#dyntable tbody').on('click', '.edit_inquiry', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#InquiryModal').find('#id').val(data["inq_id"]);
    if (data && data["inq_id"]) {
        fetchAndFillInquiry(data["inq_id"]);
    }
});

// Function to fetch and fill inquiry data
function fetchAndFillInquiry(id) {
    if (!id) return;
    jQuery('#InquiryModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-inquiry",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.inquiry_data != null) {
                let url = checkFileRoute + "?id=" + data.inquiry_data.inq_id + "&name=" + data.inquiry_data.pdf_name + "&type=inquiry";
                jQuery('#preview_btn').attr('href', url).show();

                jQuery('#InquiryModal').find('#inq_number').val(data.inquiry_data.inq_number).prop({ tabindex: -1, readonly: true });
                jQuery('#InquiryModal').find('#inq_sequence').val(data.inquiry_data.inq_sequence).prop({ tabindex: -1, readonly: true });
                // jQuery('#InquiryModal').find('#inq_date').val(data.inquiry_data.inq_date);
                jQuery('#InquiryModal').find('#inq_date').val(data.inquiry_data.inq_date != "" ? data.inquiry_data.inq_date : "");
                jQuery('#InquiryModal').find('#inq_customer_id').val(zeroToEmpty(data.inquiry_data.inq_customer_id)).trigger('change.select2');
                setTimeout(() => {
                    setSelect2Readonly('#inq_customer_id', true);
                }, 100);

                if (zeroToEmpty(data.inquiry_data.inq_customer_id) !== '') {
                    getInquiryKindAttRelationData().done(function (response) {
                        setTimeout(() => {
                            jQuery('#InquiryModal').find('#inq_kind_attn_id').val(zeroToEmpty(data.inquiry_data.inq_kind_attn_id)).trigger('change.select2');
                        }, 800);
                    });
                }
                jQuery('#InquiryModal').find('#inq_ref_no_date').val(data.inquiry_data.inq_ref_no_date);
                jQuery('#InquiryModal').find('#inq_sp_note').val(data.inquiry_data.inq_sp_note);
                jQuery('#InquiryModal').find('#inq_prepared_by_id').val(zeroToEmpty(data.inquiry_data.inq_prepared_by_id)).trigger('change.select2');
                jQuery('#InquiryModal').find('#id').val(data.inquiry_data.inq_id);

                if (data.inquiry_details_data.length > 0 && !jQuery.isEmptyObject(data.inquiry_details_data)) {
                    for (let ind in data.inquiry_details_data) {
                        inquiry_details_data.push(data.inquiry_details_data[ind]);
                    }
                    fillInquiryDetailsTable();
                }

                setTimeout(function () {
                    const $frDate = jQuery('#commonInquiryForm #inq_date');
                    $frDate.trigger('focus');
                    setTimeout(function () {
                        if ($frDate.hasClass('trans-date-picker')) {
                            $frDate.datepicker('hide');
                        }
                    }, 0);
                }, 150);


                jQuery('#InquiryModal').find('#add_new').show();

                const form = document.getElementById("commonInquiryForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#InquiryModal').find('#inq_date').focus();

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

// Reset button click for inquiry modal
jQuery('#resetbtn').on('click', function () {
    inquiry_details_data = [];
    jQuery('#InquiryDetailTable tbody').empty();

    var formId = jQuery('#InquiryModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonInquiryForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        jQuery('#inq_sequence').focus();
        $("#inq_customer_id").val('').trigger('change');
        $("#inq_kind_attn_id").val('').trigger('change.select2');
        $("#inq_prepared_by_id").val(loginUserId).trigger('change.select2');
        jQuery('#InquiryModal').find('#preview_btn').hide();
        getLatestInquiryNo();
    } else {
        fetchAndFillInquiry(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_inquiry', function () {
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#InquiryModal').find('#id').val(data["inq_id"]);
//     jQuery('#InquiryModal').modal('show');
//     jQuery('#InquiryModal').find('#inq_sequence').prop({ tabindex: -1, readonly: true });
//     jQuery('#inq_date').focus();
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-inquiry",
//         type: 'GET',
//         data: "id=" + data["inq_id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 jQuery('#InquiryModal').find('#inq_number').val(data.inquiry_data.inq_number).prop({ tabindex: -1, readonly: true });
//                 jQuery('#InquiryModal').find('#inq_sequence').val(data.inquiry_data.inq_sequence).prop({ tabindex: -1, readonly: true });
//                 // jQuery('#InquiryModal').find('#inq_date').val(data.inquiry_data.inq_date);
//                 jQuery('#InquiryModal').find('#inq_date').val(data.inquiry_data.inq_date != "" ? data.inquiry_data.inq_date : "");
//                 jQuery('#InquiryModal').find('#inq_customer_id').val(zeroToEmpty(data.inquiry_data.inq_customer_id)).trigger('change.select2');
//                 setTimeout(() => {
//                     setSelect2Readonly('#inq_customer_id', true);
//                 }, 100);

//                 if (zeroToEmpty(data.inquiry_data.inq_customer_id) !== '') {
//                     getInquiryKindAttRelationData().done(function (response) {
//                         setTimeout(() => {
//                             jQuery('#InquiryModal').find('#inq_kind_attn_id').val(zeroToEmpty(data.inquiry_data.inq_kind_attn_id)).trigger('change.select2');
//                         }, 800);
//                     });
//                 }
//                 jQuery('#InquiryModal').find('#inq_ref_no_date').val(data.inquiry_data.inq_ref_no_date);
//                 jQuery('#InquiryModal').find('#inq_sp_note').val(data.inquiry_data.inq_sp_note);
//                 jQuery('#InquiryModal').find('#inq_prepared_by_id').val(zeroToEmpty(data.inquiry_data.inq_prepared_by_id)).trigger('change.select2');
//                 jQuery('#InquiryModal').find('#id').val(data.inquiry_data.inq_id);

//                 if (data.inquiry_details_data.length > 0 && !jQuery.isEmptyObject(data.inquiry_details_data)) {
//                     for (let ind in data.inquiry_details_data) {
//                         inquiry_details_data.push(data.inquiry_details_data[ind]);
//                     }
//                     fillInquiryDetailsTable();
//                 }

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

// edit time fill table start
function fillInquiryDetailsTable() {
    let thisModal = jQuery('#InquiryDetailsModal');
    if (inquiry_details_data.length > 0) {
        for (let key in inquiry_details_data) {
            let formIndx = inquiry_details_data.indexOf(inquiry_details_data[key]);
            var inqd_test_method = inquiry_details_data[key].inqd_test_method ? inquiry_details_data[key].inqd_test_method : null;
            var test_method = inqd_test_method != '' ? thisModal.find('#inqd_test_method option[value="' + inqd_test_method + '"]').text() : '';

            var inqd_type_of_job_id = inquiry_details_data[key].inqd_type_of_job_id ? inquiry_details_data[key].inqd_type_of_job_id : null;
            var type_of_job = inqd_type_of_job_id != '' ? thisModal.find('#inqd_type_of_job_id option[value="' + inqd_type_of_job_id + '"]').text() : '';

            var inqd_job_description_id = inquiry_details_data[key].inqd_job_description_id ? inquiry_details_data[key].inqd_job_description_id : null;
            var job_description = inqd_job_description_id != '' ? thisModal.find('#inqd_job_description_id option[value="' + inqd_job_description_id + '"]').text() : '';

            var part_no = inquiry_details_data[key].inqd_part_no ?? (inquiry_details_data[key].part_no ?? (inquiry_details_data[key].part ?? ''));

            var inqd_process_at = inquiry_details_data[key].inqd_process_at ? inquiry_details_data[key].inqd_process_at : null;
            var process_at = inqd_process_at != '' ? thisModal.find('#inqd_process_at option[value="' + inqd_process_at + '"]').text() : '';

            var inqd_description = inquiry_details_data[key].inqd_description ? inquiry_details_data[key].inqd_description : "";

            // var inqd_quantity = inquiry_details_data[key].inqd_quantity ? parseFloat(inquiry_details_data[key].inqd_quantity).toFixed(3) : "";
            var inqd_quantity = inquiry_details_data[key].inqd_quantity ? parseFloat(inquiry_details_data[key].inqd_quantity).toFixed(2) : "";

            var inqd_unit_id = inquiry_details_data[key].inqd_unit_id ? inquiry_details_data[key].inqd_unit_id : null;
            var unit = inqd_unit_id != '' ? thisModal.find('#inqd_unit_id option[value="' + inqd_unit_id + '"]').text() : '';

            var inqd_feasibility_required = inquiry_details_data[key].inqd_feasibility_required ? inquiry_details_data[key].inqd_feasibility_required : null;
            var feasibility_required = inqd_feasibility_required != '' ? thisModal.find('#inqd_feasibility_required option[value="' + inqd_feasibility_required + '"]').text() : '';

            // var inqd_estimation_required = inquiry_details_data[key].inqd_estimation_required ? inquiry_details_data[key].inqd_estimation_required : null;
            // var estimation_required = inqd_estimation_required != null ? thisModal.find('#inqd_estimation_required option[value="' + inqd_estimation_required + '"]').text() : 'No';

            var inqd_remark = inquiry_details_data[key].inqd_remark ? inquiry_details_data[key].inqd_remark : "";

            var inqd_file_upload_doc = inquiry_details_data[key].inqd_file_upload_doc ? inquiry_details_data[key].inqd_file_upload_doc : "";
            var in_use = inquiry_details_data[key].in_use == true ? true : false;

            if (jQuery('#InquiryDetailTable tbody').find('#noDetails').length > 0) {
                jQuery('#InquiryDetailTable tbody').empty();
            }
            let tblHtml = `<tr>`;
            tblHtml += `<td>`;
            tblHtml += in_use == true ? DetailsActionDropdown('editInquiryDetails') : DetailsActionDropdown('editInquiryDetails', 'removeInquiryDetails');
            tblHtml += ` <input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;
            tblHtml += `<td>${test_method}</td>`;
            tblHtml += `<td>${type_of_job}</td>`;
            tblHtml += `<td>${job_description}</td>`;
            tblHtml += `<td>${part_no}</td>`;
            tblHtml += `<td>${process_at}</td>`;
            tblHtml += `<td>${inqd_quantity}</td>`;
            tblHtml += `<td>${unit}</td>`;
            tblHtml += `<td>${feasibility_required}</td>`;
            // tblHtml += `<td>${estimation_required}</td>`;
            tblHtml += `<td>${inqd_remark}</td>`;
            if (inqd_file_upload_doc != "") {
                let fullImagePath = uploadURL + inqd_file_upload_doc;
                tblHtml += `<td style="text-align:center; vertical-align:middle;">
                    <a target="_blank" href="${fullImagePath}" title="View" style="font-size:18px; display:inline-block;">
                        <i class="ri-eye-fill"></i>
                    </a>
                    <input type='hidden' name='inqd_file_upload_doc[]' value="${inqd_file_upload_doc}"/>
                </td>`;
            } else {
                tblHtml += `<td style="text-align:center; vertical-align:middle;">
                    <input type='hidden' name='inqd_file_upload_doc[]' value=""/>
                </td>`;
            }
            // if (inqd_file_upload_doc != "") {
            //     tblHtml += `<td><a target="_blank" href="${inqd_file_upload_doc}" title="view"><i class="action-icon"></i></a><input type='hidden' name='inqd_file_upload_doc[]' value="${inqd_file_upload_doc}"/>`;
            // } else {
            //     tblHtml += `<td><input type='hidden' name='inqd_file_upload_doc[]' value=""/>`;
            // }
            tblHtml += `</tr>`;
            jQuery('#InquiryDetailTable tbody').append(tblHtml);
        }
    }
}
// edit time fill table end

function editInquiryDetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();
    fillInquiryDetailsForm(formIndx, rawIndx);
}

// inquiry details form edit
function fillInquiryDetailsForm(formIndx, rawIndx) {
    let thisForm = jQuery('#InquiryDetailsModal');
    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    var frmData = inquiry_details_data[formIndx];
    thisForm.find("#inqd_id").val(frmData.inqd_id);
    thisForm.find("#inqd_test_method").val(zeroToEmpty(frmData.inqd_test_method)).trigger('change.select2');
    thisForm.find("#inqd_type_of_job_id").val(zeroToEmpty(frmData.inqd_type_of_job_id)).trigger('change.select2');
    thisForm.find("#inqd_job_description_id").val(zeroToEmpty(frmData.inqd_job_description_id)).trigger('change');
    thisForm.find("#inqd_part_no").val(frmData.inqd_part_no ?? frmData.part_no ?? frmData.part ?? "");
    thisForm.find("#inqd_process_at").val(zeroToEmpty(frmData.inqd_process_at)).trigger('change.select2');
    thisForm.find("#inqd_description").val(frmData.inqd_description);
    // thisForm.find("#inqd_quantity").val(frmData.inqd_quantity != "" ? parseFloat(frmData.inqd_quantity).toFixed(3) : "");
    thisForm.find("#inqd_quantity").val(frmData.inqd_quantity != "" ? parseFloat(frmData.inqd_quantity).toFixed(2) : "");
    thisForm.find("#inqd_unit_id").val(zeroToEmpty(frmData.inqd_unit_id)).trigger('change.select2');
    thisForm.find("#inqd_feasibility_required").val(zeroToEmpty(frmData.inqd_feasibility_required)).trigger('change');
    // thisForm.find("#inqd_estimation_required").val(zeroToEmpty(frmData.inqd_estimation_required)).trigger('change.select2');
    thisForm.find("#inqd_remark").val(frmData.inqd_remark);

    // if (frmData.inqd_file_upload_doc != "" && frmData.inqd_file_upload_doc != undefined) {
    //     thisForm.find("#inqd_file_upload_doc").val(frmData.inqd_file_upload_doc);
    //     thisForm.find('#inqd_file_upload_prev').attr('href', uploadURL + frmData.inqd_file_upload_doc);
    //     thisForm.find('#inqd_file_upload_prev').removeClass('hide');
    //     thisForm.find('#inqd_file_upload_img-prev-box').removeClass('hide');
    //     thisForm.find('#inqd_file_upload_remove').addClass('i-block').removeClass('hide');
    // } else {
    //     thisForm.find('#inqd_file_upload_doc').val('');
    //     thisForm.find('#inqd_file_upload_prev').attr('href', '#');
    //     thisForm.find('#inqd_file_upload_prev').addClass('hide');
    //     thisForm.find('#inqd_file_upload_remove').removeClass('i-block').addClass('hide');
    //     thisForm.find('#inqd_file_upload_img-prev').html('');
    //     thisForm.find('#inqd_file_upload_img-prev-box').addClass('hide');
    //     thisForm.find('#inqd_file_upload_img-prev-box').html('');
    // }

    if (frmData.inqd_file_upload_doc != "" && frmData.inqd_file_upload_doc != undefined) {
        let fullPath = frmData.inqd_file_upload_doc;
        let fileName = fullPath.split('/').pop();
        thisForm.find("#inqd_file_upload_doc").val(fullPath);
        thisForm.find('#inqd_file_upload_prev').attr('href', uploadURL + fullPath).removeClass('hide');
        thisForm.find('#inqd_file_upload_remove').addClass('i-block').removeClass('hide');
        thisForm.find('#inqd_file_upload_img-prev-box').removeClass('hide');
        thisForm.find('#inqd_file_upload_img-prev').html(fileName);
        let fileInput = thisForm.find('#inqd_file_upload');
        let newFile = new DataTransfer();
        newFile.items.add(new File([""], fileName));
        fileInput[0].files = newFile.files;
    } else {
        thisForm.find('#inqd_file_upload_doc').val('');
        thisForm.find('#inqd_file_upload_prev').attr('href', '#').addClass('hide');
        thisForm.find('#inqd_file_upload_remove').removeClass('i-block').addClass('hide');
        thisForm.find('#inqd_file_upload_img-prev').html('');
        thisForm.find('#inqd_file_upload_img-prev-box').addClass('hide').html('');
        thisForm.find('#inqd_file_upload').val('');
    }

    if (frmData.inqd_id != 0) {
        setTimeout(() => {
            if (frmData.in_use) {
                setSelect2Readonly('#inqd_test_method', true);
                setSelect2Readonly('#inqd_type_of_job_id', true);
                setSelect2Readonly('#inqd_job_description_id', true);
                thisForm.find("#inqd_part_no").prop('readonly', true);
                setSelect2Readonly('#inqd_process_at', true);
                thisForm.find("#inqd_quantity").prop({ tabindex: -1, readonly: true });
                setSelect2Readonly('#inqd_unit_id', true);
                setSelect2Readonly('#inqd_feasibility_required', true);
                // setSelect2Readonly('#inqd_estimation_required', true);

                const input = document.getElementById('inqd_description');
                if (input) {
                    setTimeout(() => {
                        input.focus();
                    }, 100);
                }

            } else {
                setSelect2Readonly('#inqd_test_method', false);
                setSelect2Readonly('#inqd_type_of_job_id', false);
                setSelect2Readonly('#inqd_job_description_id', false);
                thisForm.find("#inqd_part_no").prop('readonly', false);
                setSelect2Readonly('#inqd_process_at', false);
                thisForm.find("#inqd_quantity").prop({ tabindex: 1, readonly: false });
                setSelect2Readonly('#inqd_unit_id', false);
                setSelect2Readonly('#inqd_feasibility_required', false);
                // setSelect2Readonly('#inqd_estimation_required', false);
            }



        }, 1000);
    } else {
        // setSelect2Readonly('#inqd_test_method', false);
        // setSelect2Readonly('#inqd_type_of_job_id', false);
        // setSelect2Readonly('#inqd_job_description_id', false);
        // setSelect2Readonly('#inqd_part_id', false);
        // setSelect2Readonly('#inqd_process_at', false);
        // thisForm.find("#inqd_quantity").prop({ tabindex: 0, readonly: false });
        // setSelect2Readonly('#inqd_unit_id', false);
        setSelect2Readonly('#inqd_feasibility_required', false);
        // setSelect2Readonly('#inqd_estimation_required', false);
    }

    thisForm.modal('show');
}

function removeInquiryDetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        removeFormObj(formIndx);
        jQuery(th).closest("tr").remove();
    });
}

function removeFormObj(formIndx) {
    delete inquiry_details_data[formIndx];
    inquiry_details_data = inquiry_details_data.filter(element => element != null);
    jQuery('#InquiryDetailTable tbody').empty();
    fillInquiryDetailsTable();
}

// Main Inquiry Form Submit
$('#commonInquiryForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#InquiryModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;
    var dateValue = document.getElementById("inq_date").value.trim();
    if (!isValidDate(dateValue)) {
        toastr.error("Please Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#InquiryModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#InquiryModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#InquiryModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    jQuery('#InquiryModal').find('#submitbtn').prop('disabled', true);

    var formIdnew = jQuery('#InquiryModal').find('#commonInquiryForm').find('#id').val();
    var formUrl = formIdnew != undefined && formIdnew != "" ? "update-inquiry" : "store-inquiry";
    var data = new FormData(form);
    data.append('inquiry_details_data', JSON.stringify(inquiry_details_data ?? []));
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (inquiry_details_data.length > 0 && !jQuery.isEmptyObject(inquiry_details_data)) {
        jQuery.ajax({
            type: 'POST',
            url: formUrl,
            data: data,
            contentType: false,
            processData: false,
            success: function (data) {
                if (data.response_code == 1) {
                    if (formIdnew != undefined && formIdnew != "") {
                        function redirectFn() {
                            window.location.reload();
                        }
                        if (data.url != undefined && data.url != "") {
                            toastSuccessPreview(data.response_message, data.url, redirectFn);
                        } else {
                            toastSuccess(data.response_message, redirectFn);
                        }
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#InquiryModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formIdnew == undefined || formIdnew == "") {
                        function nextFn() {
                            document.getElementById("commonInquiryForm").reset();
                            const form = document.getElementById("commonInquiryForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            jQuery('#inq_sequence').focus();
                            $("#inq_customer_id").val('').trigger('change');
                            $("#inq_kind_attn_id").val('').trigger('change.select2');
                            $("#inq_prepared_by_id").val(loginUserId).trigger('change.select2');
                            inquiry_details_data = [];
                            jQuery('#InquiryDetailTable tbody').empty();
                            jQuery('#InquiryModal').find('#preview_btn').hide();
                            getLatestInquiryNo();
                        }
                        if (data.url != undefined && data.url != "") {
                            toastSuccessPreview(data.response_message, data.url, nextFn);
                        } else {
                            toastSuccess(data.response_message, nextFn);
                        }
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#InquiryModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#InquiryModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#InquiryModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#InquiryModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#InquiryModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        if (formIdnew != undefined && formIdnew != "") {
            jQuery('#InquiryModal').find('#submitbtn').prop('disabled', true);
            toastr.error('You can not update Inquiry.');
        }
        toastr.error('Please Add At Least One Inquiry Details.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#InquiryModal').find('#submitbtn').prop('disabled', false);
    }
});
// Main Form Submit End

// Inquiry Details Form Submit Start
$('#InquiryDetailsForm').on('submit', function (e) {
    e.preventDefault();
    let form = this;

    // let feasibilityVal = jQuery('#inqd_feasibility_required').val();
    // let estimationField = jQuery('#inqd_estimation_required');

    // if (feasibilityVal == 'Yes') {
    //     jQuery('#est_star').show();
    //     estimationField.prop('required', true);
    // } else {
    //     jQuery('#est_star').hide();
    //     estimationField.prop('required', false);
    //     jQuery('#inqd_estimation_required').val('No').trigger('change.select2');
    // }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    var data = new FormData(document.getElementById('InquiryDetailsForm'));
    var formValue = Object.fromEntries(data.entries());
    var thisModal = jQuery('#InquiryDetailsModal');

    var currentQty = formValue.inqd_quantity ? parseFloat(formValue.inqd_quantity) : 0;
    if (currentQty < 0.001) {
        toastr.error('Please Enter Quantity greater than 0.001.');
        return;
    }

    if (formValue.inqd_test_method.trim()) {
        var noDuplicate = true;
        if (noDuplicate) {
            var inqd_test_method = formValue.inqd_test_method ? formValue.inqd_test_method : null;
            var test_method = inqd_test_method != '' ? thisModal.find('#inqd_test_method option[value="' + inqd_test_method + '"]').text() : '';

            var inqd_type_of_job_id = formValue.inqd_type_of_job_id ? formValue.inqd_type_of_job_id : null;
            var type_of_job = inqd_type_of_job_id != '' ? thisModal.find('#inqd_type_of_job_id option[value="' + inqd_type_of_job_id + '"]').text() : '';

            var inqd_job_description_id = formValue.inqd_job_description_id ? formValue.inqd_job_description_id : null;
            var job_description = inqd_job_description_id != '' ? thisModal.find('#inqd_job_description_id option[value="' + inqd_job_description_id + '"]').text() : '';

            var part_no = formValue.inqd_part_no ?? "";

            var inqd_process_at = formValue.inqd_process_at ? formValue.inqd_process_at : null;
            var process_at = inqd_process_at != '' ? thisModal.find('#inqd_process_at option[value="' + inqd_process_at + '"]').text() : '';

            var inqd_description = formValue.inqd_description ? formValue.inqd_description : "";

            // var inqd_quantity = formValue.inqd_quantity ? parseFloat(formValue.inqd_quantity).toFixed(3) : "";
            var inqd_quantity = formValue.inqd_quantity ? parseFloat(formValue.inqd_quantity).toFixed(2) : "";

            var inqd_unit_id = formValue.inqd_unit_id ? formValue.inqd_unit_id : null;
            var unit = inqd_unit_id != '' ? thisModal.find('#inqd_unit_id option[value="' + inqd_unit_id + '"]').text() : '';

            var inqd_feasibility_required = formValue.inqd_feasibility_required ? formValue.inqd_feasibility_required : null;
            var feasibility_required = inqd_feasibility_required != '' ? thisModal.find('#inqd_feasibility_required option[value="' + inqd_feasibility_required + '"]').text() : '';

            // var inqd_estimation_required = formValue.inqd_estimation_required ? formValue.inqd_estimation_required : null;
            // var estimation_required = inqd_estimation_required != null ? thisModal.find('#inqd_estimation_required option[value="' + inqd_estimation_required + '"]').text() : 'No';

            var inqd_file_upload_doc = formValue.inqd_file_upload_doc ? formValue.inqd_file_upload_doc : "";

            var inqd_remark = formValue.inqd_remark ? formValue.inqd_remark : "";
            if (test_method != "") {
                if (formValue.form_type == "edit") {
                    var in_use = inquiry_details_data[formValue.form_index].in_use == true ? true : false;
                    formValue.in_use = in_use;
                    inquiry_details_data[formValue.form_index] = formValue;
                    let tblHtml = ``;
                    tblHtml += `<td>`;
                    tblHtml += in_use == true ? DetailsActionDropdown('editInquiryDetails') : DetailsActionDropdown('editInquiryDetails', 'removeInquiryDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                    </td>`;
                    tblHtml += `<td>${test_method}</td>`;
                    tblHtml += `<td>${type_of_job}</td>`;
                    tblHtml += `<td>${job_description}</td>`;
                    tblHtml += `<td>${part_no}</td>`;
                    tblHtml += `<td>${process_at}</td>`;
                    tblHtml += `<td>${inqd_quantity}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${feasibility_required}</td>`;
                    // tblHtml += `<td>${estimation_required}</td>`;
                    tblHtml += `<td>${inqd_remark}</td>`;
                    if (inqd_file_upload_doc != "") {
                        let fullImagePath = uploadURL + inqd_file_upload_doc;
                        tblHtml += `<td style="text-align:center; vertical-align:middle;">
                            <a target="_blank" href="${fullImagePath}" title="View" style="font-size:18px; display:inline-block;">
                                <i class="ri-eye-fill"></i>
                            </a>
                            <input type='hidden' name='inqd_file_upload_doc[]' value="${inqd_file_upload_doc}"/>
                        </td>`;
                    } else {
                        tblHtml += `<td style="text-align:center; vertical-align:middle;">
                            <input type='hidden' name='inqd_file_upload_doc[]' value=""/>
                        </td>`;
                    }
                    // if (inqd_file_upload_doc != "") {
                    //     tblHtml += `<td><a target="_blank" href="${inqd_file_upload_doc}" title="view"></a><input type='hidden' name='inqd_file_upload_doc[]' value="${inqd_file_upload_doc}"/>`;
                    // } else {
                    //     tblHtml += `<td><input type='hidden' name='inqd_file_upload_doc[]' value=""/>`;
                    // }
                    tblHtml += `</tr>`;
                    jQuery('#InquiryDetailTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
                    toastSuccess("Record Updated.");
                } else {
                    inquiry_details_data.push(formValue)
                    let formIndx = inquiry_details_data.indexOf(formValue);
                    if (jQuery('#InquiryDetailTable tbody').find('#noDetails').length > 0) {
                        jQuery('#InquiryDetailTable tbody').empty();
                    }

                    let tblHtml = `<tr>`;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editInquiryDetails', 'removeInquiryDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
                    </td>`;
                    tblHtml += `<td>${test_method}</td>`;
                    tblHtml += `<td>${type_of_job}</td>`;
                    tblHtml += `<td>${job_description}</td>`;
                    tblHtml += `<td>${part_no}</td>`;
                    tblHtml += `<td>${process_at}</td>`;
                    tblHtml += `<td>${inqd_quantity}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${feasibility_required}</td>`;
                    // tblHtml += `<td>${estimation_required}</td>`;
                    tblHtml += `<td>${inqd_remark}</td>`;
                    if (inqd_file_upload_doc != "") {
                        let fullImagePath = uploadURL + inqd_file_upload_doc;
                        tblHtml += `<td style="text-align:center; vertical-align:middle;">
                            <a target="_blank" href="${fullImagePath}" title="View" style="font-size:18px; display:inline-block;">
                                <i class="ri-eye-fill"></i>
                            </a>
                            <input type='hidden' name='inqd_file_upload_doc[]' value="${inqd_file_upload_doc}"/>
                        </td>`;
                    } else {
                        tblHtml += `<td style="text-align:center; vertical-align:middle;">
                            <input type='hidden' name='inqd_file_upload_doc[]' value=""/>
                        </td>`;
                    }
                    // if (inqd_file_upload_doc != "") {
                    //     tblHtml += `<td><a target="_blank" href="${inqd_file_upload_doc}" title="view"></a><input type='hidden' name='inqd_file_upload_doc[]' value="${inqd_file_upload_doc}"/>`;
                    // } else {
                    //     tblHtml += `<td><input type='hidden' name='inqd_file_upload_doc[]' value=""/>`;
                    // }
                    tblHtml += `</tr>`;
                    jQuery('#InquiryDetailTable tbody').append(tblHtml);
                    toastSuccess("Record Inserted.");
                }
            }

            if (formValue.form_type == "edit") {
                thisModal.modal('hide');
            } else {
                let formElement = document.getElementById('InquiryDetailsForm');
                formElement.reset();
                jQuery('#inqd_test_method').val('').trigger('change.select2');
                jQuery('#inqd_type_of_job_id').val('').trigger('change.select2');
                jQuery('#inqd_job_description_id').val('').trigger('change');
                jQuery('#inqd_part_no').val('');
                jQuery('#inqd_process_at').val('').trigger('change.select2');
                jQuery('#inqd_description').val('');
                jQuery('#inqd_quantity').val('');
                jQuery('#inqd_unit_id').val('').trigger('change.select2');
                jQuery('#inqd_feasibility_required').val('').trigger('change');
                // jQuery('#inqd_estimation_required').val('').trigger('change.select2');
                jQuery('#inqd_remark').val('');

                setTimeout(function () {
                    $(formElement).removeClass('was-validated');
                    $(formElement).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                    thisModal.find('.error').removeClass('error');
                }, 150);

                setTimeout(function () {
                    $(formElement).removeClass('was-validated');
                    $(formElement).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                    thisModal.find('.error').removeClass('error');

                    let $select = jQuery('#inqd_test_method');
                    $select.one('select2:opening', function (e) {
                        e.preventDefault();
                    });

                    let sel = $select.next('.select2-container').find('.select2-selection');
                    if (sel.length) {
                        sel.attr('tabindex', 0).focus();
                    }

                    $select.select2('close');
                }, 150);

                setTimeout(() => {
                    thisModal.find('#inqd_file_upload_doc').val('');
                    thisModal.find('#inqd_file_upload_prev').attr('href', '#');
                    thisModal.find('#inqd_file_upload_prev').addClass('hide');
                    thisModal.find('#inqd_file_upload_remove').removeClass('i-block').addClass('hide');
                    thisModal.find('#inqd_file_upload_img-prev').html('');
                    thisModal.find('#inqd_file_upload_img-prev-box').addClass('hide');
                    thisModal.find('#inqd_file_upload_img-prev-box').html('');
                }, 300)
            }
        }
    } else {
        toastr.error('Please Select Type of Test');
    }
});
// Inquiry Details Form Submit End

// Feasibility Required If Yes Than Estimation Required Enable
// jQuery('#InquiryDetailsForm #inqd_feasibility_required').on('change', function () {
//     let thisVal = jQuery(this).val();

//     let feasibilityVal = jQuery('#inqd_feasibility_required').val();
//     let estimationField = jQuery('#inqd_estimation_required');
//     if (thisVal == 'Yes') {
//         jQuery('#est_star').show();
//         setSelect2Readonly('#inqd_estimation_required', false);
//         // setSelect2Required('#inqd_estimation_required', true);
//     }
//     else {
//         jQuery('#est_star').hide();
//         jQuery('#inqd_estimation_required').val('').trigger("change.select2");

//         setTimeout(function () {
//             setSelect2Readonly('#inqd_estimation_required', true);

//         }, 500);
//         // setSelect2Required('#inqd_estimation_required', false);
//     }
// });

jQuery('#InquiryModal').on('show.bs.modal', function () {
    var hasAccess = jQuery('#commonInquiryForm').find('#has_access').val();
    // const input = document.getElementById('inq_sequence');
    // input?.focus();
    jQuery('#inq_prepared_by_id').val(loginUserId).trigger('change.select2');

    var latestnumber = jQuery('#InquiryModal').find('#commonInquiryForm').find('#id').val();
    if (latestnumber == '') {
        // setTimeout(function () {
        getLatestInquiryNo();
        // getInquiryKindAttRelationData();
        // }, 300);
        // jQuery('#inq_sequence').focus();
        const input = document.getElementById('inq_sequence');
        input?.focus();
        jQuery('#InquiryModal').find('#preview_btn').hide();
    } else {
        // jQuery('#inq_date').focus();
        const input = document.getElementById('inq_date');
        input?.focus();
        setTimeout(() => {
            setSelect2Readonly('#inq_customer_id', true);
        }, 100);
        jQuery('#InquiryModal').find('#preview_btn').show();
    }

    var date = jQuery('#InquiryModal').find('#inq_date');
    if (date.hasClass('trans-date-picker')) {
        setTimeout(() => {
            date.datepicker('hide');
        }, 800);
    }
});

jQuery('#add_new').on('click', function () {
    jQuery('#InquiryModal').find('#id').val('');
    var form = document.getElementById("commonInquiryForm");
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
    }

    $("#inq_customer_id").val('').trigger('change');
    $("#inq_kind_attn_id").val('').trigger('change.select2');
    $("#inq_prepared_by_id").val(loginUserId).trigger('change.select2');

    jQuery('#inq_sequence').prop({ tabindex: 0, readonly: false });
    setSelect2Readonly('#inq_customer_id', false);
    setSelect2Readonly('#inq_kind_attn_id', false);

    inquiry_details_data = [];
    jQuery('#InquiryDetailTable tbody').empty();
    getLatestInquiryNo();
    jQuery('#inq_sequence').focus();
    jQuery('#add_new').hide();
});

jQuery('#InquiryModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#InquiryModal');
    thisModal.find("#id").val("");
    var thisForm = jQuery('#InquiryDetailsModal');
    thisForm.find("#inqd_id").val(0);
    inquiry_details_data = [];

    if (formId == "" || formId == undefined) {
        thisModal.find("#inq_sequence").prop({ tabindex: 0, readonly: false });
        setSelect2Readonly('#inq_customer_id', false);
        setSelect2Readonly('#inq_kind_attn_id', false);
    }

    jQuery('#InquiryDetailTable tbody').empty();
    jQuery('#commonInquiryForm').trigger("reset");
    thisModal.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).prop('checked', false);
    });

    // jQuery('#CountryModal').find('#add_new').hide();
});

jQuery('#InquiryDetailsModal').on('show.bs.modal', function () {
    let thisModal = jQuery('#InquiryDetailsModal');
    var focusremoveid = jQuery('#InquiryModal').find('#commonInquiryForm').find('#id').val();
    var form_type = thisModal.find("#form_type").val();
    var details_id = thisModal.find("#inqd_id").val();
    if (focusremoveid != undefined && focusremoveid != "") {
        if (details_id != 0) {
            jQuery('#inqd_description').focus();
        } else {
            setTimeout(function () {
                let sel = jQuery('#inqd_test_method')
                    .next('.select2-container')
                    .find('.select2-selection');
                sel.attr('tabindex', 0).focus();
            }, 20);
        }
    } else {
        setTimeout(function () {
            let sel = jQuery('#inqd_test_method')
                .next('.select2-container')
                .find('.select2-selection');
            sel.attr('tabindex', 0).focus();
        }, 20);
    }

    if (form_type == 'add') {
        setSelect2Readonly('#inqd_test_method', false);
        setSelect2Readonly('#inqd_type_of_job_id', false);
        setSelect2Readonly('#inqd_job_description_id', false);
        setSelect2Readonly('#inqd_part_id', false);
        setSelect2Readonly('#inqd_process_at', false);
        thisModal.find("#inqd_quantity").prop({ tabindex: 0, readonly: false });
        setSelect2Readonly('#inqd_unit_id', false);
        setSelect2Readonly('#inqd_feasibility_required', false);
        // setSelect2Readonly('#inqd_estimation_required', true);

        setTimeout(() => {
            thisModal.find('#inqd_file_upload_doc').val('');
            thisModal.find('#inqd_file_upload_prev').attr('href', '#');
            thisModal.find('#inqd_file_upload_prev').addClass('hide');
            thisModal.find('#inqd_file_upload_remove').removeClass('i-block').addClass('hide');
            thisModal.find('#inqd_file_upload_img-prev').html('');
            thisModal.find('#inqd_file_upload_img-prev-box').addClass('hide');
            thisModal.find('#inqd_file_upload_img-prev-box').html('');
        }, 300)
    }
});

jQuery('#InquiryDetailsModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#InquiryDetailsModal');
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    thisModal.find("#inqd_id").val("");
    thisModal.find("#inqd_id").val(0);
    thisModal.find('#submitbtn').prop('disabled', false);
    jQuery('#InquiryDetailsForm').trigger("reset");

    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('inq_sp_note');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }

});

// get the latest number
function getLatestInquiryNo() {
    jQuery.ajax({
        url: "get-latest_inquiry_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#inq_number').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#inq_sequence').val(data.number);
                jQuery('#inq_date').val(currentDate);
            } else {
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            jQuery('#inq_number').removeClass('file-loader');
            console.log('Field To Get Latest Inquiry No.!')
        }
    });
}


jQuery('#commonInquiryForm').find('#inq_sequence').on('change', function () {
    checkSequence();
});

function checkSequence() {
    let thisForm = jQuery('#commonInquiryForm');
    let val = thisForm.find('#inq_sequence').val();
    if (val != "") {
        if (val > 0 == false) {
            toastr.error('Please Enter Valid Inquiry No.');
            jQuery('#inq_sequence').parent().parent().parent('div.control-group').addClass('error');
            jQuery('#inq_sequence').focus();
            jQuery('#inq_sequence').val('');
        } else {
            jQuery('#inq_sequence').addClass('file-loader');
            jQuery('#inq_sequence').parent().parent().parent('div.control-group').removeClass('error');

            var urL = "check-inq_number_duplication?for=add&inq_sequence=" + val;
            if (formId !== undefined) { //if form is edit
                urL = "check-inq_number_duplication?for=edit&inq_sequence=" + val + "&id=" + formId;
            }

            jQuery.ajax({
                url: urL,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    jQuery('#inq_sequence').removeClass('file-loader');
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        jQuery('#inq_sequence').val('');
                        const input = document.getElementById('inq_sequence'); input?.focus();
                    } else {
                        jQuery('#inq_number').val(data.latest_no);
                        jQuery('#inq_sequence').val(val);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#inq_sequence').removeClass('file-loader');
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
        jQuery('#inq_number').val('');
        jQuery('#inq_sequence').val('');
    }
}

function validateImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.pdf)$/i;
    if (!allowedExtensions.exec(filePath)) {
        return false;
    }
    return true;
}

function InquiryfileUpload(e) {
    var form_data = new FormData();
    var target = e.target;
    var id = target.id;
    var files = target.files;
    var totalfiles = files.length;
    var oldImg = jQuery('#inqd_file_upload_doc').val();
    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');
    if (totalfiles > 0) {
        var notValid = 0;
        for (var index = 0; index < totalfiles; index++) {
            if (validateImage(files[index].name) == true) {
                form_data.append("docs[]", files[index]);
            } else {
                notValid = 1;
                toastr.error("Only (jpeg,jpg,png,gif,pdf) files are allowed.");
                jQuery('#inqd_file_upload_doc').val('');
                jQuery('#' + id).val('');
                jQuery('#inqd_file_upload_prev').attr('href', '#').addClass('hide');
                jQuery('#inqd_file_upload_remove').removeClass('i-block').addClass('hide');
                e.stopImmediatePropagation();
                return false;
            }
        }

        if (notValid == 0) {
            jQuery('#InquiryDetailsModal').find('#submitbtn').prop('disabled', true);
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
                    jQuery('#InquiryDetailsModal').find('#submitbtn').prop('disabled', false);
                    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    if (data.response_code == 1) {
                        if (oldImg != "" && oldImg.includes('temp_media/')) {
                            removeMedia(oldImg);
                        }

                        $('#inqd_file_upload_doc').val(data.files);
                        $('#inqd_file_upload_prev').attr('href', data.files_url);
                        $('#inqd_file_upload_prev').removeClass('hide');
                        $('.fileupload-exists').removeClass('hide');
                        $('#inqd_file_upload_remove').removeClass('hide');
                        $('.remove-file').addClass('i-block').removeClass('hide');
                        console.log(data.response_message);
                    } else {
                        console.log(data.response_message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#InquiryDetailsModal').find('#submitbtn').prop('disabled', false);
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
            let fileInput = jQuery('#inqd_file_upload');
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

        $('#inqd_file_upload_doc').val('');
        $('#inqd_file_upload_prev').attr('href', '#');
        $('#inqd_file_upload_prev').addClass('hide');
        $('.remove-file').addClass('hide');
        $('.fileupload-exists').addClass('hide');
        $('.remove-file').removeClass('i-block').addClass('hide');
        $('#inqd_file_upload_remove').removeClass('hide');
    }
}

function removeFile(e) {
    e.stopImmediatePropagation();
    toastDetailDelete('Are You Sure, You Want <lw-c>To</lw-c> Delete ?', () => {
        var target = e.target;
        var id = target.getAttribute("data-remove");
        var fileName = jQuery('#' + id + '_doc').val();
        var oldImg = jQuery('#inqd_file_upload_doc').val();
        jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');

        if (oldImg != "") {
            removeMedia(oldImg);
        }

        jQuery('#inqd_file_upload_doc').val('');
        jQuery('#inqd_file_upload').val('');
        jQuery('#inqd_file_upload_prev').attr('href', '#');
        jQuery('#inqd_file_upload_prev').addClass('hide');
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

function getInquiryKindAttRelationData() {
    let thisForm = jQuery('#commonInquiryForm');
    let inq_customer_id = thisForm.find('#inq_customer_id option:selected').val();
    if (inq_customer_id != '') {
        return jQuery.ajax({
            url: "inquiry_relation_field?customer_id=" + inq_customer_id,
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
                thisForm.find('#inq_kind_attn_id').empty().append(kindDrpHtml);
                // jQuery('#inq_kind_attn_id').empty().append(kindDrpHtml).select2().trigger('select2:select');
            }
        });
    } else {
        var kindDrpHtml = `<option value="">Select Kind Attention</option>`;
        thisForm.find('#inq_kind_attn_id').empty().append(kindDrpHtml);
        // jQuery('#inq_kind_attn_id').empty().append(`<option value=''>Select Kind Attention</option>`).trigger('change.select2');
    }
}

jQuery('#commonInquiryForm #inq_customer_id').on('change', function () {
    getInquiryKindAttRelationData();
});

// jQuery('#InquiryDetailsModal').on('change', '#inqd_job_description_id', function () {
//     let parts = $(this).find(':selected').data('parts');
//     let $partSelect = $('#InquiryDetailsModal').find('#inqd_part_id');
//     $partSelect.empty().append('<option value="">Select Part No.</option>');
//     if (parts && parts.length > 0) {
//         $.each(parts, function (i, part) {
//             $partSelect.append(
//                 `<option value="${part.part_id}">
//                     ${part.part}
//                 </option>`
//             );
//         });
//     }
//     $partSelect.trigger('change');
// });

function suggestInquiryPartNo(e, $this) {
    var keyevent = e;
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        var jobId = jQuery('#InquiryDetailsModal').find('#inqd_job_description_id').val() || '';
        jQuery.ajax({
            url: "get-inquiry_part_no_list?term=" + encodeURI(search) + "&job_desc_id=" + jobId,
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                if (data.response_code == 1) {
                    jQuery('#inqd_part_no_list').html(data.partNoList);
                }
            }
        });
    }
}

jQuery('#InquiryDetailsModal').on('change', '#inqd_job_description_id', function () {
    let jobId = $(this).val();
    let jobText = $(this).find('option:selected').text().trim();

    if (jobId && jobText && jobText !== 'Select Job Description') {
        $('#InquiryDetailsModal').find('#inqd_description').val(jobText);
    }
});

$(document).ready(function () {
    $('#CustomerModal').on('hidden.bs.modal', function (e) {
        e.stopPropagation();
        setTimeout(() => {
            const inqId = $('#InquiryModal #id').val();
            const $cust = $('#inq_customer_id');
            const $kind = $('#inq_kind_attn_id');
            const isEdit = (inqId !== '' && inqId !== undefined);
            const $target = isEdit ? $kind : $cust;

            if (isEdit) {
                $cust.next('.select2-container').find('.select2-selection').attr('tabindex', '-1');
            }

            const $sel = $target.next('.select2-container').find('.select2-selection');
            if ($sel.length) {
                $target.off('select2:opening');
                $sel.attr('tabindex', '0').focus();
                $target.select2('close');
            }
        }, 400);
    });
});

jQuery('#CityModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('city_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});
jQuery('#TypeOfJobModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('inqd_type_of_job_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});
jQuery('#JobDescriptionModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('inqd_job_description_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});
jQuery('#PartModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('inqd_part_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});
jQuery('#UnitModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('inqd_unit_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 500);
    }
});