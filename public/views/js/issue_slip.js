issue_slip_details_data = [];

// Edit item issue slip row click
jQuery('#dyntable tbody').on('click', '.edit_issue_slip', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["iis_id"]) {
        fetchAndFillItemIssueSlip(data["iis_id"]);
    }
});

// Function to fetch and fill item issue slip data
function fetchAndFillItemIssueSlip(id) {
    if (!id) return;
    jQuery('#IssueSlipModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-issue_slip",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.issue_slip_data != null) {
                jQuery('#IssueSlipModal').find('#iis_number').val(data.issue_slip_data.iis_number);
                jQuery('#IssueSlipModal').find('#iis_sequence').val(data.issue_slip_data.iis_sequence);
                jQuery('#IssueSlipModal').find('#iis_date').val(data.issue_slip_data.iis_date != "" ? data.issue_slip_data.iis_date : "");
                jQuery('#IssueSlipModal').find('#iis_issuer_id').val(data.issue_slip_data.iis_issuer_id != 0 ? data.issue_slip_data.iis_issuer_id : "").trigger('change.select2');
                jQuery('#IssueSlipModal').find('#iis_receiver_id').val(data.issue_slip_data.iis_receiver_id != 0 ? data.issue_slip_data.iis_receiver_id : "").trigger('change.select2');

                if (data.issue_slip_data.iis_issuer_id != 0) {
                    setTimeout(() => {
                        setSelect2Readonly("#iis_issuer_id", true);
                    }, 300);
                }

                if (data.issue_slip_data.iis_receiver_id != 0) {
                    setTimeout(() => {
                        setSelect2Readonly("#iis_receiver_id", true);
                    }, 300);
                }

                jQuery('#IssueSlipModal').find('#iis_checked_by_issuer').val(data.issue_slip_data.iis_checked_by_issuer).trigger('change.select2');
                jQuery('#IssueSlipModal').find('#iis_checked_by_receiver').val(data.issue_slip_data.iis_checked_by_receiver).trigger('change.select2');
                jQuery('#IssueSlipModal').find('#iis_special_note').val(data.issue_slip_data.iis_special_note != "" ? data.issue_slip_data.iis_special_note : "");
                jQuery('#IssueSlipModal').find('#id').val(data.issue_slip_data.iis_id);

                if (data.issue_slip_details_data.length > 0 && !jQuery.isEmptyObject(data.issue_slip_details_data)) {
                    for (let ind in data.issue_slip_details_data) {
                        issue_slip_details_data.push(data.issue_slip_details_data[ind]);
                    }
                    fillIssueSlipDetailsTable();
                }

                const form = document.getElementById("commonIssueSlipForm");
                if (form) form.classList.remove('was-validated');

                if (data.issue_slip_data.in_use == true) {
                    jQuery('#IssueSlipModal').find('#iis_sequence').prop('readonly', true);
                    let nextInput = jQuery('#IssueSlipModal').find('#iis_date');
                    if (nextInput.hasClass('trans-date-picker') || nextInput.hasClass('date-picker')) {
                        nextInput.one('focus', function () {
                            setTimeout(() => {
                                jQuery(this).datepicker('hide');
                            }, 50);
                        });
                    }
                    nextInput.focus();
                } else {
                    jQuery('#IssueSlipModal').find('#iis_sequence').prop('readonly', false);
                    jQuery('#IssueSlipModal').find('#iis_sequence').focus();
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

// Reset button click for item issue slip modal
jQuery('#resetbtn').on('click', function () {
    issue_slip_details_data = [];
    jQuery('#IssueSlipDetailTable tbody').empty();

    var formId = jQuery('#IssueSlipModal').find('#id').val();
    if (!formId) {
        jQuery('#IssueSlipModal').find('#iis_sequence').prop('readonly', false);
        var form = document.getElementById("commonIssueSlipForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        jQuery('#iis_sequence').focus();
        jQuery('#IssueSlipModal').find("#iis_issuer_id").val('').trigger('change.select2');
        jQuery('#IssueSlipModal').find("#iis_receiver_id").val('').trigger('change.select2');
        jQuery('#IssueSlipModal').find("#iis_checked_by_issuer").val('').trigger('change.select2');
        jQuery('#IssueSlipModal').find("#iis_checked_by_receiver").val('').trigger('change.select2');
        getLatestIssueSlipNo();
    } else {
        fetchAndFillItemIssueSlip(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_issue_slip', function () {
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#IssueSlipModal').find('#id').val(data["iis_id"]);
//     jQuery('#IssueSlipModal').modal('show');
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-issue_slip",
//         type: 'GET',
//         data: "id=" + data["iis_id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 jQuery('#IssueSlipModal').find('#iis_number').val(data.issue_slip_data.iis_number);
//                 jQuery('#IssueSlipModal').find('#iis_sequence').val(data.issue_slip_data.iis_sequence);
//                 jQuery('#IssueSlipModal').find('#iis_date').val(data.issue_slip_data.iis_date != "" ? data.issue_slip_data.iis_date : "");
//                 jQuery('#IssueSlipModal').find('#iis_issuer_id').val(data.issue_slip_data.iis_issuer_id != 0 ? data.issue_slip_data.iis_issuer_id : "").trigger('change.select2');
//                 jQuery('#IssueSlipModal').find('#iis_receiver_id').val(data.issue_slip_data.iis_receiver_id != 0 ? data.issue_slip_data.iis_receiver_id : "").trigger('change.select2');

//                 if (data.issue_slip_data.iis_issuer_id != 0) {
//                     setTimeout(() => { 
//                         setSelect2Readonly("#iis_issuer_id", true);
//                     }, 300);
//                 }

//                 if (data.issue_slip_data.iis_receiver_id != 0) {
//                     setTimeout(() => {
//                         setSelect2Readonly("#iis_receiver_id", true);
//                     }, 300);
//                 }

//                 jQuery('#IssueSlipModal').find('#iis_checked_by_issuer').val(data.issue_slip_data.iis_checked_by_issuer).trigger('change.select2');
//                 jQuery('#IssueSlipModal').find('#iis_checked_by_receiver').val(data.issue_slip_data.iis_checked_by_receiver).trigger('change.select2');
//                 jQuery('#IssueSlipModal').find('#iis_special_note').val(data.issue_slip_data.iis_special_note != "" ? data.issue_slip_data.iis_special_note : "");
//                 jQuery('#IssueSlipModal').find('#id').val(data.issue_slip_data.iis_id);

//                 if (data.issue_slip_details_data.length > 0 && !jQuery.isEmptyObject(data.issue_slip_details_data)) {
//                     for (let ind in data.issue_slip_details_data) {
//                         issue_slip_details_data.push(data.issue_slip_details_data[ind]);
//                     }
//                     fillIssueSlipDetailsTable();
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

jQuery('#IssueSlipDetailsModal').on('change', '#iisd_item_id', function () {
    let selectedOption = jQuery(this).find('option:selected');
    let item_type = selectedOption.data('item_type');
    let unit_name = selectedOption.data('unit_name');
    let item_stock = selectedOption.data('item_stock');
    let item_id = jQuery(this).val();

    let $srNoRow = jQuery('#iisd_sr_no_id').closest('.row');
    let $batchNoRow = jQuery('#iisd_batch_no_id').closest('.row');

    jQuery('#IssueSlipDetailsModal').find('#iisd_item_type').val(item_type).trigger("change");
    jQuery('#IssueSlipDetailsModal').find('#iisd_issue_stock').val(item_stock);
    setSelect2Readonly('#iisd_item_type', true);
    jQuery('#IssueSlipDetailsModal').find('#iisd_issue_unit_id').val(unit_name).attr('readonly', true);

    if (!item_id || item_id == "") {
        $srNoRow.show();
        $batchNoRow.hide();
        return;
    }

    $srNoRow.hide();
    $batchNoRow.hide();

    if (item_type === 'general' || item_type === 'ut_probe' || item_type === 'mpt_equipment' || item_type === 'ut_equipment') {
        $srNoRow.show();
    }
    else if (item_type === 'lpt_chemical' || item_type === 'mpt_chemical') {
        $batchNoRow.show();
    }

    if (item_type === 'general') {
        jQuery('#IssueSlipDetailsModal').find("#iisd_issue_qty").val('').prop({ tabindex: 0, readonly: false });
        setSelect2Readonly('#IssueSlipDetailsForm #iisd_sr_no_id', true);
        setSelect2Readonly('#IssueSlipDetailsForm #iisd_batch_no_id', true);
    } else {
        jQuery('#IssueSlipDetailsModal').find("#iisd_issue_qty").val(parseFloat(1).toFixed(3)).prop({ tabindex: -1, readonly: true });
        setSelect2Readonly('#IssueSlipDetailsForm #iisd_sr_no_id', false);
        setSelect2Readonly('#IssueSlipDetailsForm #iisd_batch_no_id', false);
    }
});

jQuery('#iisd_sr_no_id').closest('.row').show();
jQuery('#iisd_batch_no_id').closest('.row').hide();

function getBatchNoAndSrNoIssueSlipData() {
    let iisd_item_type = jQuery('#iisd_item_type option:selected').val();
    let iisd_item_id = jQuery('#iisd_item_id option:selected').val();
    let details_id = jQuery('#IssueSlipDetailsModal').find("#iisd_id").val(); // edit mode if !empty
    if (iisd_item_type != '' && iisd_item_type != undefined && iisd_item_type != "general") {
        return jQuery.ajax({
            url: "get_batch_no_and_sr_no_issue_slip?iisd_item_type=" + iisd_item_type + "&item_id=" + iisd_item_id,
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                var BatchNoDrpHtml = `<option value="">Select Batch No.</option>`;
                var SrNoDrpHtml = `<option value="">Select Sr. No.</option>`;
                if (data.response_code == 1 && data.iisd_batch_no_id.length) {
                    for (let indx in data.iisd_batch_no_id) {
                        BatchNoDrpHtml += `<option data-iisd_batch_no="${data.iisd_batch_no_id[indx].sr_batch_name}" value="${data.iisd_batch_no_id[indx].iisd_batch_no_id}">${data.iisd_batch_no_id[indx].sr_batch_name} </option>`;
                    }
                    jQuery('#iisd_batch_no_id').closest('.row').show();
                    jQuery('#iisd_sr_no_id').closest('.row').hide();
                    jQuery('#iisd_batch_no_id').empty().append(BatchNoDrpHtml).select2().trigger('select2:select');

                    // if edit mode Batch No. dropdown readonly and tabindex focus remove
                    if (details_id != 0 && details_id != undefined) {
                        setSelect2Readonly('#iisd_batch_no_id', true);
                        setTimeout(function () {
                            jQuery('#iisd_batch_no_id').next('.select2-container').find('.select2-selection').attr('tabindex', '-1').blur();
                        }, 200);
                    }
                } else {
                    for (let indx in data.iisd_sr_no_id) {
                        SrNoDrpHtml += `<option data-iisd_sr_no="${data.iisd_sr_no_id[indx].sr_no_name}" value="${data.iisd_sr_no_id[indx].iisd_sr_no_id}">${data.iisd_sr_no_id[indx].sr_no_name} </option>`;
                    }
                    jQuery('#iisd_sr_no_id').closest('.row').show();
                    jQuery('#iisd_batch_no_id').closest('.row').hide();
                    jQuery('#iisd_sr_no_id').empty().append(SrNoDrpHtml).select2().trigger('select2:select');

                    // if edit mode Sr. No. dropdown readonly and tabindex focus remove
                    if (details_id != 0 && details_id != undefined) {
                        setSelect2Readonly('#iisd_sr_no_id', true);
                        setTimeout(function () {
                            jQuery('#iisd_sr_no_id').next('.select2-container').find('.select2-selection').attr('tabindex', '-1').blur();
                        }, 200);
                    }
                }
            }
        });
    } else {
        jQuery('#iisd_batch_no_id').empty().append(`<option value=''>Select Batch No.</option>`).trigger('change.select2');
        jQuery('#iisd_sr_no_id').empty().append(`<option value=''>Select Sr. No.</option>`).trigger('change.select2');
    }
}

jQuery('#IssueSlipDetailsModal #iisd_item_type').on('change', function () {
    getBatchNoAndSrNoIssueSlipData();
});

jQuery('#IssueSlipDetailsModal').on('change', '#iisd_sr_no_id, #iisd_batch_no_id', function () {
    let selectedOption = jQuery(this).find('option:selected');
    var sr_no = selectedOption.data('iisd_sr_no');
    var batch_no = selectedOption.data('iisd_batch_no');
    if (sr_no != '' && sr_no != undefined) {
        jQuery('#IssueSlipDetailsModal').find('#iisd_sr_no_or_batch_no').val(sr_no);
    } else if (batch_no != '' && batch_no != undefined) {
        jQuery('#IssueSlipDetailsModal').find('#iisd_sr_no_or_batch_no').val(batch_no);
    } else {
        jQuery('#IssueSlipDetailsModal').find('#iisd_sr_no_or_batch_no').val('');
    }
});

// get the latest number
function getLatestIssueSlipNo() {
    jQuery.ajax({
        url: "get-latest_issue_slip_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#iis_number').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#iis_sequence').val(data.number);
                jQuery('#iis_date').val(currentDate);
            } else {
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            jQuery('#iis_number').removeClass('file-loader');
            console.log('Field To Get Latest Item Issue Slip No.!')
        }
    });
}

function checkSequence() {
    var formId_iissequence = jQuery('#IssueSlipModal').find('#commonIssueSlipForm').find('#id').val();
    let thisForm = jQuery('#commonIssueSlipForm');
    let val = thisForm.find('#iis_sequence').val();
    if (val != "") {
        if (val > 0 == false) {
            toastr.error('Please Enter Valid Item Issue Slip No.');
            jQuery('#iis_sequence').parent().parent().parent('div.control-group').addClass('error');
            jQuery('#iis_sequence').focus();
            jQuery('#iis_sequence').val('');
        } else {
            jQuery('#iis_sequence').addClass('file-loader');
            jQuery('#iis_sequence').parent().parent().parent('div.control-group').removeClass('error');

            var urL = "check-iis_number_duplication?for=add&iis_sequence=" + val;
            if (formId_iissequence !== undefined) { //if form is edit
                urL = "check-iis_number_duplication?for=edit&iis_sequence=" + val + "&id=" + formId_iissequence;
            }

            jQuery.ajax({
                url: urL,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    jQuery('#iis_sequence').removeClass('file-loader');
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        jQuery('#iis_sequence').val('');
                        const input = document.getElementById('iis_sequence'); input?.focus();
                    } else {
                        jQuery('#iis_number').val(data.latest_no);
                        jQuery('#iis_sequence').val(val);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#iis_sequence').removeClass('file-loader');
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
        jQuery('#iis_number').val('');
        jQuery('#iis_sequence').val('');
    }
}

// Main Item Issue Slip Form Submit
$('#commonIssueSlipForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#IssueSlipModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;
    var dateValue = document.getElementById("iis_date").value.trim();
    if (!isValidDate(dateValue)) {
        toastr.error("Please Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#IssueSlipModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#IssueSlipModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#IssueSlipModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    jQuery('#IssueSlipModal').find('#submitbtn').prop('disabled', true);

    var formIdnew = jQuery('#IssueSlipModal').find('#commonIssueSlipForm').find('#id').val();
    var formUrl = formIdnew != undefined && formIdnew != "" ? "update-issue_slip" : "store-issue_slip";
    var data = new FormData(form);
    data.append('issue_slip_details_data', JSON.stringify(issue_slip_details_data ?? []));
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (issue_slip_details_data.length > 0 && !jQuery.isEmptyObject(issue_slip_details_data)) {
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
                        toastSuccess(data.response_message, redirectFn);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#IssueSlipModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formIdnew == undefined || formIdnew == "") {
                        function nextFn() {
                            jQuery('#IssueSlipModal').find('#iis_sequence').prop('readonly', false);
                            document.getElementById("commonIssueSlipForm").reset();
                            const form = document.getElementById("commonIssueSlipForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            jQuery('#iis_sequence').focus();
                            $("#iis_issuer_id").val('').trigger('change.select2');
                            $("#iis_receiver_id").val('').trigger('change.select2');
                            $("#iis_checked_by_issuer").val('').trigger('change.select2');
                            $("#iis_checked_by_receiver").val('').trigger('change.select2');
                            issue_slip_details_data = [];
                            jQuery('#IssueSlipDetailTable tbody').empty();
                            getLatestIssueSlipNo();
                        }
                        toastSuccess(data.response_message, nextFn);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#IssueSlipModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#IssueSlipModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#IssueSlipModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#IssueSlipModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#IssueSlipModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        toastr.error('Please Add At Least One Item Issue Slip Details.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#IssueSlipModal').find('#submitbtn').prop('disabled', false);
    }
});
// Main Form Submit End

// edit time fill table start
function fillIssueSlipDetailsTable() {
    let thisModal = jQuery('#IssueSlipDetailsModal');
    if (issue_slip_details_data.length > 0) {
        for (let key in issue_slip_details_data) {
            let formIndx = issue_slip_details_data.indexOf(issue_slip_details_data[key]);
            var iisd_item_id = issue_slip_details_data[key].iisd_item_id ? issue_slip_details_data[key].iisd_item_id : null;
            var item_name = iisd_item_id != '' ? thisModal.find('#iisd_item_id option[value="' + iisd_item_id + '"]').text() : '';
            var $opt = thisModal.find('#iisd_item_id option[value="' + iisd_item_id + '"]');
            var iisd_issue_stock = iisd_item_id !== '' ? $opt.data('item_stock') : '';

            var iisd_item_type = issue_slip_details_data[key].iisd_item_type ? issue_slip_details_data[key].iisd_item_type : null;
            var item_type = iisd_item_type != '' ? thisModal.find('#iisd_item_type option[value="' + iisd_item_type + '"]').text() : '';

            var iisd_batch_no_id = issue_slip_details_data[key].iisd_batch_no_id ? issue_slip_details_data[key].iisd_batch_no_id : null;
            var batch_no = iisd_batch_no_id != '' ? thisModal.find('#iisd_batch_no_id option[value="' + iisd_batch_no_id + '"]').text() : '';

            var iisd_sr_no_id = issue_slip_details_data[key].iisd_sr_no_id ? issue_slip_details_data[key].iisd_sr_no_id : null;
            var iisd_sr_no_or_batch_no = issue_slip_details_data[key].iisd_sr_no_or_batch_no ? issue_slip_details_data[key].iisd_sr_no_or_batch_no : '';
            var sr_no = iisd_sr_no_id != '' ? thisModal.find('#iisd_sr_no_id option[value="' + iisd_sr_no_id + '"]').text() : '';

            var iisd_issue_qty = issue_slip_details_data[key].iisd_issue_qty ? parseFloat(issue_slip_details_data[key].iisd_issue_qty).toFixed(3) : "";

            // var iisd_issue_stock = issue_slip_details_data[key].iisd_issue_stock ? parseFloat(issue_slip_details_data[key].iisd_issue_stock).toFixed(3) : "";

            var iisd_issue_type = issue_slip_details_data[key].iisd_issue_type ? issue_slip_details_data[key].iisd_issue_type : null;
            var issue_type = iisd_issue_type != '' ? thisModal.find('#iisd_issue_type option[value="' + iisd_issue_type + '"]').text() : '';

            var iisd_remark = issue_slip_details_data[key].iisd_remark ? issue_slip_details_data[key].iisd_remark : "";

            var in_use = issue_slip_details_data[key].in_use == true ? true : false;


            if (jQuery('#IssueSlipDetailTable tbody').find('#noDetails').length > 0) {
                jQuery('#IssueSlipDetailTable tbody').empty();
            }
            let tblHtml = `<tr>`;
            tblHtml += `<td>`;
            tblHtml += in_use == true ? DetailsActionDropdown('editIssueSlipDetails') : DetailsActionDropdown('editIssueSlipDetails', 'removeIssueSlipDetails');
            tblHtml += ` <input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;
            // tblHtml += `<td>
            //     <div>
            //         <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
            //             <i class="ri-more-2-fill"></i>
            //         </a>
            //         <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
            //             <li><a class="dropdown-item edit-item-btn" onclick="editIssueSlipDetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i>Edit</a></li>`;

            //             if(in_use == false){
            //               tblHtml +=    `<li><a class="dropdown-item remove-item-btn" onclick="removeIssueSlipDetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li>`;
            //             }else{
            //               tblHtml +=    `<li></li>`;
            //             }
            //             tblHtml += `</ul>
            //         </ul>
            //     </div>
            //     <input type="hidden" name="form_indx" value="${formIndx}"/>
            // </td>`;
            tblHtml += `<td>${item_name}</td>`;
            tblHtml += `<td>${item_type}</td>`;
            // tblHtml += `<td>${batch_no}</td>`;
            tblHtml += `<td>${iisd_sr_no_or_batch_no}</td>`;
            tblHtml += `<td>${iisd_issue_qty}</td>`;
            tblHtml += `<td>${iisd_issue_stock}</td>`;
            tblHtml += `<td>${iisd_issue_type}</td>`;
            tblHtml += `<td>${iisd_remark}</td>`;
            tblHtml += `</tr>`;
            jQuery('#IssueSlipDetailTable tbody').append(tblHtml);
        }
    }
}
// edit time fill table end

function editIssueSlipDetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();
    fillIssueSlipDetailsForm(formIndx, rawIndx);
}

// item issue slip details form edit
function fillIssueSlipDetailsForm(formIndx, rawIndx) {
    let thisForm = jQuery('#IssueSlipDetailsModal');
    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    var frmData = issue_slip_details_data[formIndx];
    thisForm.find("#iisd_id").val(frmData.iisd_id);
    thisForm.find("#iisd_sr_no_or_batch_no").val(frmData.iisd_sr_no_or_batch_no);
    thisForm.find("#iisd_item_id").val(frmData.iisd_item_id).trigger('change');
    thisForm.find("#iisd_item_type").val(frmData.iisd_item_type).trigger('change');
    thisForm.find("#iisd_issue_type").val(frmData.iisd_issue_type).trigger('change.select2');
    setTimeout(() => {
        thisForm.find("#iisd_sr_no_id").val(zeroToEmpty(frmData.iisd_sr_no_id)).trigger('change.select2');
    }, 1000);
    setTimeout(() => {
        thisForm.find("#iisd_batch_no_id").val(zeroToEmpty(frmData.iisd_batch_no_id)).trigger('change.select2');
    }, 1000);
    thisForm.find("#iisd_issue_qty").val(frmData.iisd_issue_qty != "" ? parseFloat(frmData.iisd_issue_qty).toFixed(3) : "");
    // thisForm.find("#iisd_issue_stock").val(frmData.iisd_issue_stock ? parseFloat(frmData.iisd_issue_stock).toFixed(3) : "");
    thisForm.find("#iisd_remark").val(frmData.iisd_remark ?? "");

    if (frmData.in_use == true) {
        thisForm.find("#iisd_issue_qty").attr('min', parseFloat(frmData.used_qty).toFixed(3));
    }

    if (frmData.iisd_id != 0) {
        setSelect2Readonly('#iisd_item_id', true);
        setSelect2Readonly('#iisd_item_type', true);
        setSelect2Readonly('#iisd_sr_no_id', true);
        setSelect2Readonly('#iisd_batch_no_id', true);
    }

    thisForm.modal('show');
}

function removeIssueSlipDetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        removeFormObj(formIndx);
        jQuery(th).closest("tr").remove();
    });
}

function removeFormObj(formIndx) {
    delete issue_slip_details_data[formIndx];
    issue_slip_details_data = issue_slip_details_data.filter(element => element != null);
    jQuery('#IssueSlipDetailTable tbody').empty();
    fillIssueSlipDetailsTable();
}

// Item Issue Slip Details Form Submit Start
$('#IssueSlipDetailsForm').on('submit', function (e) {
    e.preventDefault();
    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    var data = new FormData(document.getElementById('IssueSlipDetailsForm'));
    var formValue = Object.fromEntries(data.entries());
    var thisModal = jQuery('#IssueSlipDetailsModal');

    var iisd_issue_qty = formValue.iisd_issue_qty ? parseFloat(formValue.iisd_issue_qty) : 0;
    var used_qty = parseFloat(jQuery('#iisd_issue_qty').attr('min')) || 0;
    if (used_qty > iisd_issue_qty) {
        toastr.error('Minimum Issue Qty. Must Be ' + used_qty.toFixed(3));
        return;
    }

    if (iisd_issue_qty < 0.001) {
        toastr.error('Please Enter Issue Qty. greater than 0.001.');
        return;
    }

    if (formValue.iisd_item_id.trim()) {
        var noDuplicate = true;
        var duplicateMsg = '';

        issue_slip_details_data.forEach(function (row, index) {
            // edit mode ma current row skip
            if (formValue.form_type === 'edit' && index == formValue.form_index) {
                return;
            }

            // Same item
            if (row.iisd_item_id == formValue.iisd_item_id) {
                // Same Batch No
                if (formValue.iisd_batch_no_id && row.iisd_batch_no_id && row.iisd_batch_no_id == formValue.iisd_batch_no_id) {
                    noDuplicate = false;
                    duplicateMsg = 'Batch No. already exists.';
                    return false;
                }

                // Same SR No
                if (formValue.iisd_sr_no_id && row.iisd_sr_no_id && row.iisd_sr_no_id == formValue.iisd_sr_no_id) {
                    noDuplicate = false;
                    duplicateMsg = 'Sr. No. already exists.';
                    return false;
                }
            }
        });

        if (!noDuplicate) {
            toastr.error(duplicateMsg);
            return;
        }

        if (noDuplicate) {
            var iisd_item_id = formValue.iisd_item_id ? formValue.iisd_item_id : null;
            var item_name = iisd_item_id != '' ? thisModal.find('#iisd_item_id option[value="' + iisd_item_id + '"]').text() : '';

            var iisd_item_type = formValue.iisd_item_type ? formValue.iisd_item_type : null;
            var item_type = iisd_item_type != '' ? thisModal.find('#iisd_item_type option[value="' + iisd_item_type + '"]').text() : '';

            var final_result = "";
            var iisd_batch_no_id = formValue.iisd_batch_no_id ? formValue.iisd_batch_no_id : null;
            var batch_no = iisd_batch_no_id != '' ? thisModal.find('#iisd_batch_no_id option[value="' + iisd_batch_no_id + '"]').text() : '';

            var iisd_sr_no_id = formValue.iisd_sr_no_id ? formValue.iisd_sr_no_id : null;
            var sr_no = iisd_sr_no_id != '' ? thisModal.find('#iisd_sr_no_id option[value="' + iisd_sr_no_id + '"]').text() : '';

            var iisd_issue_stock = formValue.iisd_issue_stock ? parseFloat(formValue.iisd_issue_stock).toFixed(3) : "";
            var iisd_issue_qty = formValue.iisd_issue_qty ? parseFloat(formValue.iisd_issue_qty).toFixed(3) : "";

            var iisd_issue_type = formValue.iisd_issue_type ? formValue.iisd_issue_type : null;
            var issue_type = iisd_issue_type != '' ? thisModal.find('#iisd_issue_type option[value="' + iisd_issue_type + '"]').text() : '';

            var iisd_remark = formValue.iisd_remark ? formValue.iisd_remark : "";

            if (batch_no != "") {
                final_result = batch_no;
            } else {
                final_result = sr_no;
            }

            if (item_name != "") {
                if (formValue.form_type == "edit") {
                    issue_slip_details_data[formValue.form_index] = formValue;
                    let tblHtml = ``;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editIssueSlipDetails', 'removeIssueSlipDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                    </td>`;
                    // tblHtml += `<td>
                    //     <div>
                    //         <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                    //             <i class="ri-more-2-fill"></i>
                    //         </a>
                    //         <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                    //             <li><a class="dropdown-item edit-item-btn" onclick="editIssueSlipDetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i>Edit</a></li>
                    //             <li><a class="dropdown-item remove-item-btn" onclick="removeIssueSlipDetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li>
                    //         </ul>
                    //     </div>
                    //     <input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                    // </td>`;
                    tblHtml += `<td>${item_name}</td>`;
                    tblHtml += `<td>${item_type}</td>`;
                    // tblHtml += `<td>${batch_no}</td>`;
                    tblHtml += `<td>${final_result}</td>`;
                    tblHtml += `<td>${iisd_issue_qty}</td>`;
                    tblHtml += `<td>${iisd_issue_stock}</td>`;
                    tblHtml += `<td>${issue_type}</td>`;
                    tblHtml += `<td>${iisd_remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#IssueSlipDetailTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
                    toastSuccess("Record is updated successfully.");
                } else {
                    issue_slip_details_data.push(formValue)
                    let formIndx = issue_slip_details_data.indexOf(formValue);
                    if (jQuery('#IssueSlipDetailTable tbody').find('#noDetails').length > 0) {
                        jQuery('#IssueSlipDetailTable tbody').empty();
                    }

                    let tblHtml = `<tr>`;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editIssueSlipDetails', 'removeIssueSlipDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
                    </td>`;
                    // tblHtml += `<td>
                    //     <div>
                    //         <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                    //             <i class="ri-more-2-fill"></i>
                    //         </a>
                    //         <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                    //             <li><a class="dropdown-item edit-item-btn" onclick="editIssueSlipDetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i>Edit</a></li>
                    //             <li><a class="dropdown-item remove-item-btn" onclick="removeIssueSlipDetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li>
                    //         </ul>
                    //     </div>
                    //     <input type="hidden" name="form_indx" value="${formIndx}"/>
                    // </td>`;
                    tblHtml += `<td>${item_name}</td>`;
                    tblHtml += `<td>${item_type}</td>`;
                    // tblHtml += `<td>${batch_no}</td>`;
                    tblHtml += `<td>${final_result}</td>`;
                    tblHtml += `<td>${iisd_issue_qty}</td>`;
                    tblHtml += `<td>${iisd_issue_stock}</td>`;
                    tblHtml += `<td>${issue_type}</td>`;
                    tblHtml += `<td>${iisd_remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#IssueSlipDetailTable tbody').append(tblHtml);
                    toastSuccess("Record is inserted successfully.");
                }
            }

            if (formValue.form_type == "edit") {
                thisModal.modal('hide');
            } else {
                let formElement = document.getElementById('IssueSlipDetailsForm');
                formElement.reset();
                jQuery('#iisd_item_id').val('').trigger('change');
                jQuery('#iisd_item_type').val('').trigger('change.select2');
                jQuery('#iisd_sr_no_id').val('').trigger('change.select2');
                jQuery('#iisd_batch_no_id').val('').trigger('change.select2');
                jQuery('#iisd_issue_stock').val('');
                jQuery('#iisd_issue_unit_id').val('');
                jQuery('#iisd_issue_qty').val('').prop({ tabindex: -1, readonly: true });
                jQuery('#iisd_issue_type').val('Consumable').trigger('change.select2');
                jQuery('#iisd_remark').val('');
                jQuery('#iisd_sr_no_or_batch_no').val('');

                setTimeout(function () {
                    $(formElement).removeClass('was-validated');
                    $(formElement).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                    thisModal.find('.error').removeClass('error');

                    let $select = jQuery('#iisd_item_id');
                    $select.one('select2:opening', function (e) {
                        e.preventDefault();
                    });

                    let sel = $select.next('.select2-container').find('.select2-selection');
                    if (sel.length) {
                        sel.attr('tabindex', 0).focus();
                    }

                    $select.select2('close');
                }, 150);
            }
        }
    } else {
        toastr.error('Please Select Item Name');
    }
});
// Item Issue Slip Details Form Submit End

jQuery('#IssueSlipModal').on('shown.bs.modal', function () {
    var hasAccess = jQuery('#commonIssueSlipForm').find('#has_access').val();
    var latestnumber = jQuery('#IssueSlipModal').find('#commonIssueSlipForm').find('#id').val();

    if (latestnumber == '') {
        jQuery('#IssueSlipModal').find('#iis_sequence').prop('readonly', false);
        getLatestIssueSlipNo();
        var thisForm = jQuery('#IssueSlipModal');
        thisForm.find('#iis_issuer_id').val('').trigger('change');
        thisForm.find('#iis_receiver_id').val('').trigger('change');
        thisForm.find('#iis_checked_by_issuer').val('').trigger('change');
        thisForm.find('#iis_checked_by_receiver').val('').trigger('change');
    }

    if (!jQuery('#IssueSlipModal').find('#iis_sequence').prop('readonly')) {
        const input = document.getElementById('iis_sequence');
        input?.focus();
    }
});

jQuery('#IssueSlipModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#IssueSlipModal');
    thisForm.find('#id').val('');
    thisForm.find('#iis_sequence').prop('readonly', false);
    thisForm.find('#iis_issuer_id').val('').trigger('change');
    thisForm.find('#iis_receiver_id').val('').trigger('change');
    thisForm.find('#iis_checked_by_issuer').val('').trigger('change');
    thisForm.find('#iis_checked_by_receiver').val('').trigger('change');
    issue_slip_details_data = [];
    jQuery('#IssueSlipDetailTable tbody').empty();
    document.getElementById("commonIssueSlipForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).prop('checked', false);
    });
});

jQuery('#IssueSlipDetailsModal').on('shown.bs.modal', function () {
    let thisModal = jQuery('#IssueSlipDetailsModal');
    var focusremoveid = jQuery('#IssueSlipModal').find('#commonIssueSlipForm').find('#id').val();
    var form_type = thisModal.find("#form_type").val();
    var details_id = thisModal.find("#iisd_id").val();
    var iisd_item_type = thisModal.find("#iisd_item_type option:selected").val();
    if (focusremoveid != undefined && focusremoveid != "") {
        if (details_id != 0) {
            if (iisd_item_type == 'general') {
                jQuery('iisd_issue_qty').focus();
            } else {
                let sel = jQuery('#iisd_issue_type')
                    .next('.select2-container')
                    .find('.select2-selection');
                sel.attr('tabindex', 0).focus();
            }
            setTimeout(function () {
                setSelect2Readonly('#IssueSlipDetailsForm #iisd_item_id', true);
                setSelect2Readonly('#IssueSlipDetailsForm #iisd_item_type', true);
                setSelect2Readonly('#IssueSlipDetailsForm #iisd_sr_no_id', true);
                setSelect2Readonly('#IssueSlipDetailsForm #iisd_batch_no_id', true);
                // let sel = jQuery('#iisd_issue_type')
                //     .next('.select2-container')
                //     .find('.select2-selection');
                // sel.attr('tabindex', 0).focus();
            }, 20);
        } else {
            setTimeout(function () {
                let sel = jQuery('#iisd_item_id')
                    .next('.select2-container')
                    .find('.select2-selection');
                sel.attr('tabindex', 0).focus();
            }, 20);
        }
    } else {
        setTimeout(function () {
            let sel = jQuery('#iisd_item_id')
                .next('.select2-container')
                .find('.select2-selection');
            sel.attr('tabindex', 0).focus();
        }, 20);
    }

    if (form_type == 'add') {
        thisModal.find('#iisd_issue_type').val('Consumable').trigger('change.select2');
    }
    setSelect2Readonly('#IssueSlipDetailsForm #iisd_item_type', true);
});

jQuery('#IssueSlipDetailsModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#IssueSlipDetailsModal');
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    thisModal.find("#iisd_id").val(0);
    jQuery('#IssueSlipDetailsForm').trigger("reset");
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('iis_special_note');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }
});