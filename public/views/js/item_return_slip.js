irs_details_data = [];
var formId = jQuery('#commonItemReturnSlipForm').find('input[name="id"]').val();

// Edit item return slip row click
jQuery('#dyntable tbody').on('click', '.edit_item_return_slip', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["irs_id"]) {
        fetchAndFillItemReturnSlip(data["irs_id"]);
    }
});

// Function to fetch and fill item return slip data
function fetchAndFillItemReturnSlip(id) {
    if (!id) return;
    jQuery('#ItemReturnSlipModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-item_return_slip",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.irs_data != null) {
                jQuery('#ItemReturnSlipModal').find('#irs_number').val(data.irs_data.irs_number);
                jQuery('#ItemReturnSlipModal').find('#irs_sequence').val(data.irs_data.irs_sequence);
                jQuery('#ItemReturnSlipModal').find('#irs_date').val(data.irs_data.irs_date != "" ? data.irs_data.irs_date : "");
                jQuery('#ItemReturnSlipModal').find('#irs_type_id').val(data.irs_data.irs_type_id).trigger('change.select2');
                setSelect2Readonly('#ItemReturnSlipModal #irs_type_id', true);
                if (data.irs_data.irs_employee_id !== '') {
                    getPendingEmployes().done(function (response) {
                        setTimeout(() => {
                            jQuery('#ItemReturnSlipModal').find('#irs_employee_id').val(data.irs_data.irs_employee_id).trigger('change.select2');
                        }, 800);
                    });
                }

                // jQuery('#ItemReturnSlipModal').find('#irs_employee_id').val(data.irs_data.irs_employee_id).trigger('change.select2');
                jQuery('#ItemReturnSlipModal').find('#irs_special_note').val(data.irs_data.irs_special_note != "" ? data.irs_data.irs_special_note : "");
                jQuery('#ItemReturnSlipModal').find('#id').val(data.irs_data.irs_id);
                jQuery('#ItemReturnSlipModal').find('#pending_btn').prop('disabled', true);
                if (data.irs_details_data.length > 0 && !jQuery.isEmptyObject(data.irs_details_data)) {
                    for (let ind in data.irs_details_data) {
                        irs_details_data.push(data.irs_details_data[ind]);
                    }
                    fillItemReturnSlipDetailsTable();
                }

                const form = document.getElementById("commonItemReturnSlipForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#ItemReturnSlipModal').find('#irs_sequence').focus();
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
    irs_details_data = [];
    jQuery('#ItemReturnSlipDetailTable tbody').empty();

    var formId = jQuery('#ItemReturnSlipModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonItemReturnSlipForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        jQuery('#ItemReturnSlipModal').find("#irs_type_id").val('From Issue').trigger('change');
        setSelect2Readonly('#commonItemReturnSlipForm #irs_type_id', false);
        jQuery('#ItemReturnSlipModal').find("#irs_employee_id").val('').trigger('change');
        getLatestItemReturnSlipNo();
        jQuery('#commonItemReturnSlipForm').find('.toggleModalBtn').prop('disabled', true);
        jQuery('#ItemReturnSlipModal').find('#irs_sequence').focus();
    } else {
        fetchAndFillItemReturnSlip(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_item_return_slip', function () {
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#ItemReturnSlipModal').find('#id').val(data["irs_id"]);
//     jQuery('#ItemReturnSlipModal').modal('show');
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-item_return_slip",
//         type: 'GET',
//         data: "id=" + data["irs_id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 jQuery('#ItemReturnSlipModal').find('#irs_number').val(data.irs_data.irs_number);
//                 jQuery('#ItemReturnSlipModal').find('#irs_sequence').val(data.irs_data.irs_sequence);
//                 jQuery('#ItemReturnSlipModal').find('#irs_date').val(data.irs_data.irs_date != "" ? data.irs_data.irs_date : "");
//                 jQuery('#ItemReturnSlipModal').find('#irs_type_id').val(data.irs_data.irs_type_id).trigger('change.select2');
//                 setSelect2Readonly('#ItemReturnSlipModal #irs_type_id', true);
//                 if (data.irs_data.irs_employee_id !== '') {
//                     getPendingEmployes().done(function (response) {
//                         setTimeout(() => {
//                             jQuery('#ItemReturnSlipModal').find('#irs_employee_id').val(data.irs_data.irs_employee_id).trigger('change.select2');
//                         }, 800);
//                     });
//                 }

//                 // jQuery('#ItemReturnSlipModal').find('#irs_employee_id').val(data.irs_data.irs_employee_id).trigger('change.select2');
//                 jQuery('#ItemReturnSlipModal').find('#irs_special_note').val(data.irs_data.irs_special_note != "" ? data.irs_data.irs_special_note : "");
//                 jQuery('#ItemReturnSlipModal').find('#id').val(data.irs_data.irs_id);
//                 jQuery('#ItemReturnSlipModal').find('#pending_btn').prop('disabled', true);
//                 if (data.irs_details_data.length > 0 && !jQuery.isEmptyObject(data.irs_details_data)) {
//                     for (let ind in data.irs_details_data) {
//                         irs_details_data.push(data.irs_details_data[ind]);
//                     }
//                     fillItemReturnSlipDetailsTable();
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

jQuery('#ItemReturnSlipDetailsModal').on('change', '#irsd_item_id', function () {
    let selectedOption = jQuery(this).find('option:selected');
    let item_type = selectedOption.data('item_type');
    let unit_name = selectedOption.data('unit_name');
    let item_id = jQuery(this).val();

    var return_type = jQuery('#ItemReturnSlipModal').find('#irs_type_id option:selected').val();
    if (return_type == "From Issue") {
        jQuery('#ItemReturnSlipDetailsModal').find('#issue_unit').val(unit_name).attr('readonly', true);
    } else {
        // jQuery('#ItemReturnSlipDetailsModal').find('#issue_unit').val('').attr('readonly', true);
    }
    let $srNoRow = jQuery('#irsd_sr_no_id').closest('.row');
    let $batchNoRow = jQuery('#irsd_batch_no_id').closest('.row');
    jQuery('#ItemReturnSlipDetailsModal').find('#item_type_id').val(item_type).trigger("change");
    jQuery('#ItemReturnSlipDetailsModal').find('#irsd_return_qty_unit').val(unit_name);
    setSelect2Readonly('#item_type_id', true);

    if (item_type === 'film') {
        jQuery('#irsd_return_qty, #irsd_non_rerurnable_qty').removeClass('isNumberKey').addClass('isNumberKeyNotDot');
        jQuery('#irsd_return_qty, #irsd_non_rerurnable_qty').attr('onblur', 'formatPoints(this, 0)');
        if (jQuery('#irsd_return_qty').val() !== '') {
            jQuery('#irsd_return_qty').val(parseInt(jQuery('#irsd_return_qty').val()) || '');
        }
        if (jQuery('#irsd_non_rerurnable_qty').val() !== '') {
            jQuery('#irsd_non_rerurnable_qty').val(parseInt(jQuery('#irsd_non_rerurnable_qty').val()) || '');
        }
    } else {
        jQuery('#irsd_return_qty, #irsd_non_rerurnable_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        jQuery('#irsd_return_qty, #irsd_non_rerurnable_qty').attr('onblur', 'formatPoints(this, 3)');
    }

    if (!item_id || item_id == "") {
        $srNoRow.show();
        $batchNoRow.hide();
        jQuery('#irsd_return_qty, #irsd_non_rerurnable_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        jQuery('#irsd_return_qty, #irsd_non_rerurnable_qty').attr('onblur', 'formatPoints(this, 3)');
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
});

jQuery('#irsd_sr_no_id').closest('.row').show();
jQuery('#irsd_batch_no_id').closest('.row').hide();

function getBatchNoAndSrNoReturnSlipData() {
    let item_type_id = jQuery('#item_type_id option:selected').val();
    let irsd_item_id = jQuery('#irsd_item_id option:selected').val();
    let details_id = jQuery('#ItemReturnSlipDetailsModal').find("#irsd_iisd_id").val();
    if (item_type_id != '' && item_type_id != undefined && item_type_id != "general") {
        return jQuery.ajax({
            url: "get_batch_no_and_sr_no_return_slip?item_type_id=" + item_type_id + "&item_id=" + irsd_item_id,
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                var BatchNoDrpHtml = `<option value="">Select Batch No.</option>`;
                var SrNoDrpHtml = `<option value="">Select Sr. No.</option>`;
                if (data.response_code == 1 && data.irsd_batch_no_id.length) {
                    for (let indx in data.irsd_batch_no_id) {
                        BatchNoDrpHtml += `<option data-irsd_batch_no="${data.irsd_batch_no_id[indx].sr_batch_name}" value="${data.irsd_batch_no_id[indx].irsd_batch_no_id}">${data.irsd_batch_no_id[indx].sr_batch_name} </option>`;
                    }
                    jQuery('#irsd_batch_no_id').closest('.row').show();
                    jQuery('#irsd_sr_no_id').closest('.row').hide();
                    jQuery('#irsd_batch_no_id').empty().append(BatchNoDrpHtml).select2().trigger('select2:select');

                    if (details_id != 0 && details_id != undefined) {
                        setSelect2Readonly('#irsd_batch_no_id', true);
                        setTimeout(function () {
                            jQuery('#irsd_batch_no_id').next('.select2-container').find('.select2-selection').attr('tabindex', '-1').blur();
                        }, 200);
                    }
                } else {
                    for (let indx in data.irsd_sr_no_id) {
                        SrNoDrpHtml += `<option data-irsd_sr_no="${data.irsd_sr_no_id[indx].sr_no_name}" value="${data.irsd_sr_no_id[indx].irsd_sr_no_id}">${data.irsd_sr_no_id[indx].sr_no_name} </option>`;
                    }
                    jQuery('#irsd_sr_no_id').closest('.row').show();
                    jQuery('#irsd_batch_no_id').closest('.row').hide();
                    jQuery('#irsd_sr_no_id').empty().append(SrNoDrpHtml).select2().trigger('select2:select');

                    if (details_id != 0 && details_id != undefined) {
                        setSelect2Readonly('#irsd_sr_no_id', true);
                        setTimeout(function () {
                            jQuery('#irsd_sr_no_id').next('.select2-container').find('.select2-selection').attr('tabindex', '-1').blur();
                        }, 200);
                    }
                }
            }
        });
    } else {
        jQuery('#irsd_batch_no_id').empty().append(`<option value=''>Select Batch No.</option>`).trigger('change.select2');
        jQuery('#irsd_sr_no_id').empty().append(`<option value=''>Select Sr. No.</option>`).trigger('change.select2');
        return jQuery.Deferred().resolve().promise();
    }
}

jQuery('#ItemReturnSlipDetailsModal #item_type_id').on('change', function () {
    getBatchNoAndSrNoReturnSlipData();
});

jQuery('#ItemReturnSlipDetailsModal').on('change', '#irsd_sr_no_id, #irsd_batch_no_id', function () {
    let selectedOption = jQuery(this).find('option:selected');
    var sr_no = selectedOption.data('irsd_sr_no');
    var batch_no = selectedOption.data('irsd_batch_no');
    if (sr_no != '' && sr_no != undefined) {
        jQuery('#ItemReturnSlipDetailsModal').find('#irsd_sr_no_or_batch_no').val(sr_no);
    } else if (batch_no != '' && batch_no != undefined) {
        jQuery('#ItemReturnSlipDetailsModal').find('#irsd_sr_no_or_batch_no').val(batch_no);
    } else {
        jQuery('#ItemReturnSlipDetailsModal').find('#irsd_sr_no_or_batch_no').val('');
    }
});

// get the latest number
function getLatestItemReturnSlipNo() {
    jQuery.ajax({
        url: "get-latest_item_return_slip_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#irs_number').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#irs_sequence').val(data.number);
                jQuery('#irs_date').val(currentDate);
            } else {
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            jQuery('#irs_number').removeClass('file-loader');
            console.log('Field To Get Latest Item Return Slip No.!')
        }
    });
}

function checkSequence() {
    var formId_iissequence = jQuery('#ItemReturnSlipModal').find('#commonItemReturnSlipForm').find('#id').val();
    let thisForm = jQuery('#commonItemReturnSlipForm');
    let val = thisForm.find('#irs_sequence').val();
    if (val != "") {
        if (val > 0 == false) {
            toastr.error('Please Enter Valid Item Return Slip No.');
            jQuery('#irs_sequence').parent().parent().parent('div.control-group').addClass('error');
            jQuery('#irs_sequence').focus();
            jQuery('#irs_sequence').val('');
        } else {
            jQuery('#irs_sequence').addClass('file-loader');
            jQuery('#irs_sequence').parent().parent().parent('div.control-group').removeClass('error');

            var urL = "check-irs_number_duplication?for=add&irs_sequence=" + val;
            if (formId_iissequence !== undefined) { //if form is edit
                urL = "check-irs_number_duplication?for=edit&irs_sequence=" + val + "&id=" + formId_iissequence;
            }

            jQuery.ajax({
                url: urL,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    jQuery('#irs_sequence').removeClass('file-loader');
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        jQuery('#irs_sequence').val('');
                        const input = document.getElementById('irs_sequence'); input?.focus();
                    } else {
                        jQuery('#irs_number').val(data.latest_no);
                        jQuery('#irs_sequence').val(val);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#irs_sequence').removeClass('file-loader');
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
        jQuery('#irs_number').val('');
        jQuery('#irs_sequence').val('');
    }
}

jQuery('#ItemReturnSlipModal').on('shown.bs.modal', function () {
    var hasAccess = jQuery('#commonItemReturnSlipForm').find('#has_access').val();
    var formId = jQuery('#commonItemReturnSlipForm').find('input[name="id"]').val();
    if (formId == "" || formId == undefined) {
        getLatestItemReturnSlipNo();
        getPendingEmployes();
        var irs_type_id = jQuery('#commonItemReturnSlipForm').find('#irs_type_id').val();
        if (irs_type_id == "From Issue") {
            setSelect2Readonly('#commonItemReturnSlipForm #irs_type_id', false);
            jQuery('#ItemReturnSlipModal').find('#pending_btn').attr('disabled', false);
        } else if (irs_type_id == "Manual") {
            jQuery('#ItemReturnSlipModal').find('#pending_btn').attr('disabled', true);
        } else {
            jQuery('#ItemReturnSlipModal').find('#pending_btn').attr('disabled', true);
        }

        var thisForm = jQuery('#ItemReturnSlipModal');
        thisForm.find('#irs_type_id').val('From Issue').trigger('change');
        setSelect2Readonly('#commonItemReturnSlipForm #irs_type_id', false);
    } else {
        var not_balnk_irs_type_id = jQuery('#commonItemReturnSlipForm').find('#irs_type_id').val();
        if (not_balnk_irs_type_id == 'Manual') {
            jQuery('#ItemReturnSlipModal').find('.add_detail').attr('disabled', false);
        } else {
            jQuery('#ItemReturnSlipModal').find('.add_detail').attr('disabled', true);
        }
        setTimeout(() => {
            setSelect2Readonly('#commonItemReturnSlipForm #irs_type_id', true);
        }, 300);
        jQuery('#ItemReturnSlipModal').find('#pending_btn').attr('disabled', true);
    }
    const input = document.getElementById('irs_sequence');
    input?.focus();
});

jQuery('#ItemReturnSlipDetailsModal').on('shown.bs.modal', function () {
    let thisModal = jQuery('#ItemReturnSlipDetailsModal');
    var focusremoveid = jQuery('#ItemReturnSlipModal').find('#commonItemReturnSlipForm').find('#id').val();
    var irs_type_id = jQuery('#ItemReturnSlipModal').find("#irs_type_id option:selected").val();
    if (focusremoveid != undefined && focusremoveid != "" && irs_type_id == "From Issue") {
        setTimeout(function () {
            setSelect2Readonly('#ItemReturnSlipDetailsForm #item_type_id', true);
            setSelect2Readonly('#ItemReturnSlipDetailsForm #irsd_item_id', true);
            setSelect2Readonly('#ItemReturnSlipDetailsForm #irsd_sr_no_id', true);
            setSelect2Readonly('#ItemReturnSlipDetailsForm #irsd_batch_no_id', true);
        }, 20);
    } else if (focusremoveid == "" && irs_type_id == "From Issue") {
        setTimeout(function () {
            const input = document.getElementById('irsd_return_qty');
            input?.focus();

            setSelect2Readonly('#ItemReturnSlipDetailsForm #irsd_sr_no_id', true);
            setSelect2Readonly('#ItemReturnSlipDetailsForm #irsd_batch_no_id', true);
        }, 600);

        setTimeout(function () {
            setSelect2Readonly('#ItemReturnSlipDetailsForm #item_type_id', true);
            setSelect2Readonly('#ItemReturnSlipDetailsForm #irsd_item_id', true);
        }, 20);
    } else if (focusremoveid != undefined && focusremoveid != "" && irs_type_id == "Manual") {
        setTimeout(function () {
            let sel = jQuery('#irsd_item_id')
                .next('.select2-container')
                .find('.select2-selection');
            sel.attr('tabindex', 0).focus();
        }, 600);
        setTimeout(function () {
            setSelect2Readonly('#ItemReturnSlipDetailsForm #item_type_id', true);
            setSelect2Readonly('#ItemReturnSlipDetailsForm #irsd_item_id', false);
            setSelect2Readonly('#ItemReturnSlipDetailsForm #irsd_sr_no_id', false);
            setSelect2Readonly('#ItemReturnSlipDetailsForm #irsd_batch_no_id', false);
        }, 600);
    }

    if (focusremoveid == "" && irs_type_id == "Manual") {
        setTimeout(function () {
            let sel = jQuery('#irsd_item_id')
                .next('.select2-container')
                .find('.select2-selection');
            sel.attr('tabindex', 0).focus();
        }, 600);

        setTimeout(function () {
            setSelect2Readonly('#ItemReturnSlipDetailsForm #item_type_id', true);
            setSelect2Readonly('#ItemReturnSlipDetailsForm #irsd_item_id', false);
            setSelect2Readonly('#ItemReturnSlipDetailsForm #irsd_sr_no_id', false);
            setSelect2Readonly('#ItemReturnSlipDetailsForm #irsd_batch_no_id', false);
        }, 600);
    }
});

// onchange of item return slip sequence
jQuery('#commonItemReturnSlipForm').find('#irs_sequence').on('change', function () {
    checkSequence();
});

$('#irs_type_id').on('change', function () {
    IrsType();
});

function IrsType() {
    let irsType = jQuery('#irs_type_id option:selected').val();
    var formId = jQuery('#commonItemReturnSlipForm').find('input[name="id"]').val();
    if (irsType == 'From Issue') {
        if (formId == "" || formId == undefined) {
            jQuery('#commonItemReturnSlipForm').find('.add_detail').prop('disabled', true);
            jQuery('#commonItemReturnSlipForm').find('.add_detail').blur();

            jQuery('#commonItemReturnSlipForm').find('#pending_btn').attr('disabled', false);
            jQuery('#commonItemReturnSlipForm').find('#pending_btn').focus();
        }
        fillPendingItemIssueSlip();
    } else if (irsType == 'Manual') {
        if (formId == "" || formId == undefined) {
            jQuery('#commonItemReturnSlipForm').find('#pending_btn').prop('disabled', true);
            jQuery('#commonItemReturnSlipForm').find('#pending_btn').blur();

            jQuery('#commonItemReturnSlipForm').find('.add_detail').prop('disabled', false);
            jQuery('#commonItemReturnSlipForm').find('.add_detail').focus();
        } else {
            jQuery('#commonItemReturnSlipForm').find('.add_detail').prop('disabled', false);
        }
    } else {
        jQuery('#commonItemReturnSlipForm').find('.add_detail').prop('disabled', true);
        jQuery('#commonItemReturnSlipForm').find('.add_detail').blur();

        jQuery('#commonItemReturnSlipForm').find('#pending_btn').prop('disabled', true);
        jQuery('#commonItemReturnSlipForm').find('#pending_btn').blur();
    }
}

function getPendingEmployes() {
    irs_type_id = jQuery('#irs_type_id option:selected').val();
    var formId_irs_type_id = jQuery('#commonItemReturnSlipForm').find('input[name="id"]').val();
    if (formId_irs_type_id != undefined && formId_irs_type_id != "") {
        var Url = 'get-pending_employee_for_irs?irs_type_id=' + irs_type_id + "&id=" + formId_irs_type_id;
    } else {
        var Url = 'get-pending_employee_for_irs?irs_type_id=' + irs_type_id;
    }

    if (irs_type_id != undefined) {
        if (irs_type_id == "From Issue") {
            return jQuery.ajax({
                url: Url,
                // url: "get-pending_employee_for_irs?irs_type_id=" + irs_type_id,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    let suppHtml = '';
                    suppHtml += `<option value="">Select Employee</option>`;
                    if (data.response_code == 1) {
                        for (let indx in data.get_issue_return_employee) {
                            suppHtml += `<option value="${data.get_issue_return_employee[indx].id}">${data.get_issue_return_employee[indx].user_name}</option>`;
                        }
                        // jQuery('#irs_employee_id').empty().append(suppHtml).select2().trigger('select2:select');
                        jQuery('#commonItemReturnSlipForm').find('#irs_employee_id').empty().append(suppHtml);
                    } else {
                        console.log(data.response_message)
                    }
                },
            });
        } else if (irs_type_id == "Manual") {
            return jQuery.ajax({
                // url: "get-pending_employee_for_irs",
                url: Url,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    let suppHtml = '';
                    suppHtml += `<option value="">Select Employee</option>`;
                    if (data.response_code == 1) {
                        for (let indx in data.get_issue_return_employee) {
                            suppHtml += `<option value="${data.get_issue_return_employee[indx].id}">${data.get_issue_return_employee[indx].user_name}</option>`;
                        }
                        // jQuery('#irs_employee_id').empty().append(suppHtml).select2().trigger('select2:select');
                        jQuery('#commonItemReturnSlipForm').find('#irs_employee_id').empty().append(suppHtml);
                    } else {
                        console.log(data.response_message)
                    }
                },
            });
        }
    }
}

// Item Return Slip Details Form Submit Start
$('#ItemReturnSlipDetailsForm').on('submit', function (e) {
    e.preventDefault();
    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    var data = new FormData(document.getElementById('ItemReturnSlipDetailsForm'));
    var formValue = Object.fromEntries(data.entries());
    var thisModal = jQuery('#ItemReturnSlipDetailsModal');

    var currentQty = formValue.irsd_return_qty ? parseFloat(formValue.irsd_return_qty) : 0;
    var maxQtyAllowed = formValue.irsd_pending_qty ? parseFloat(formValue.irsd_pending_qty) : 0;
    if (maxQtyAllowed > 0) {
        if (currentQty > maxQtyAllowed) {
            toastr.error(`Item Return Qty. cannot exceed pending Item Issue Slip Qty.`);
            return;
        }
    }

    if (currentQty < 0.001) {
        toastr.error('Please Enter Return Qty. greater than 0.001.');
        return;
    }

    if (formValue.irsd_item_id.trim()) {
        var noDuplicate = true;
        var duplicateMsg = '';
        irs_details_data.forEach(function (row, index) {
            // edit mode ma current row skip
            if (formValue.form_type === 'edit' && index == formValue.form_index) {
                return;
            }

            // Same item
            if (row.irsd_item_id == formValue.irsd_item_id) {
                // Same Batch No
                if (formValue.irsd_batch_no_id && row.irsd_batch_no_id && row.irsd_batch_no_id == formValue.irsd_batch_no_id) {
                    noDuplicate = false;
                    duplicateMsg = 'Batch No. already exists.';
                    return false;
                }

                // Same SR No
                if (formValue.irsd_sr_no_id && row.irsd_sr_no_id && row.irsd_sr_no_id == formValue.irsd_sr_no_id) {
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
            var irsd_item_id = formValue.irsd_item_id ? formValue.irsd_item_id : null;
            var item = irsd_item_id != '' ? thisModal.find('#irsd_item_id option[value="' + irsd_item_id + '"]').text() : '';
            var item_type_id = formValue.item_type_id ? formValue.item_type_id : null;
            var item_type = item_type_id != '' ? thisModal.find('#item_type_id option[value="' + item_type_id + '"]').text() : '';

            var final_result = "";
            var irsd_sr_no_id = formValue.irsd_sr_no_id ? formValue.irsd_sr_no_id : null;
            var sr_no = irsd_sr_no_id != '' ? thisModal.find('#irsd_sr_no_id option[value="' + irsd_sr_no_id + '"]').text() : '';
            var irsd_batch_no_id = formValue.irsd_batch_no_id ? formValue.irsd_batch_no_id : null;
            var batch_no = irsd_batch_no_id != '' ? thisModal.find('#irsd_batch_no_id option[value="' + irsd_batch_no_id + '"]').text() : '';

            // var irsd_issue_qty = formValue.irsd_issue_qty ? parseFloat(irs_details_data[key].irsd_issue_qty).toFixed(3) : "";
            var irsd_issue_qty = formValue.irsd_issue_qty ? parseFloat(formValue.irsd_issue_qty).toFixed(3) : "";
            var irsd_pending_qty = formValue.irsd_pending_qty ? parseFloat(formValue.irsd_pending_qty).toFixed(3) : "";
            var irsd_return_qty = formValue.irsd_return_qty ? parseFloat(formValue.irsd_return_qty).toFixed(3) : "";
            var irsd_non_rerurnable_qty = formValue.irsd_non_rerurnable_qty ? parseFloat(formValue.irsd_non_rerurnable_qty).toFixed(3) : "";

            var irsd_reason_id = formValue.irsd_reason_id ? formValue.irsd_reason_id : null;
            var reason = irsd_reason_id != '' ? thisModal.find('#irsd_reason_id option[value="' + irsd_reason_id + '"]').text() : '';

            var irsd_remark = formValue.irsd_remark ? formValue.irsd_remark : "";
            var irsd_return_qty_unit = thisModal.find('#irsd_return_qty_unit').val();

            if (batch_no != "") {
                final_result = batch_no;
            } else {
                final_result = sr_no;
            }

            if (item != "") {
                if (formValue.form_type == "edit") {
                    irs_details_data[formValue.form_index] = formValue;
                    let tblHtml = ``;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editIrsDetails', 'removeIrsDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                    </td>`;
                    // tblHtml += `<td>
                    //     <div>
                    //         <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                    //             <i class="ri-more-2-fill"></i>
                    //         </a>
                    //         <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                    //             <li><a class="dropdown-item edit-item-btn" onclick="editIrsDetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i>Edit</a></li>
                    //             <li><a class="dropdown-item remove-item-btn" onclick="removeIrsDetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li>
                    //         </ul>
                    //     </div>
                    //     <input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                    // </td>`;
                    tblHtml += `<td>${item}</td>`;
                    tblHtml += `<td>${item_type}</td>`;
                    tblHtml += `<td>${final_result}</td>`;
                    tblHtml += `<td>${irsd_issue_qty}</td>`;
                    tblHtml += `<td>${irsd_pending_qty}</td>`;
                    tblHtml += `<td>${irsd_return_qty}</td>`;
                    // tblHtml += `<td>${irsd_non_rerurnable_qty}</td>`;
                    tblHtml += `<td>${irsd_return_qty_unit}</td>`;
                    tblHtml += `<td>${reason}</td>`;
                    tblHtml += `<td>${irsd_remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#ItemReturnSlipDetailTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
                } else {
                    irs_details_data.push(formValue)
                    let formIndx = irs_details_data.indexOf(formValue);
                    if (jQuery('#ItemReturnSlipDetailTable tbody').find('#noDetails').length > 0) {
                        jQuery('#ItemReturnSlipDetailTable tbody').empty();
                    }

                    let tblHtml = `<tr>`;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editIrsDetails', 'removeIrsDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
                    </td>`;
                    // tblHtml += `<td>
                    //     <div>
                    //         <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                    //             <i class="ri-more-2-fill"></i>
                    //         </a>
                    //         <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                    //             <li><a class="dropdown-item edit-item-btn" onclick="editIrsDetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i>Edit</a></li>
                    //             <li><a class="dropdown-item remove-item-btn" onclick="removeIrsDetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li>
                    //         </ul>
                    //     </div>
                    //     <input type="hidden" name="form_indx" value="${formIndx}"/>
                    // </td>`;
                    tblHtml += `<td>${item}</td>`;
                    tblHtml += `<td>${item_type}</td>`;
                    tblHtml += `<td>${final_result}</td>`;
                    tblHtml += `<td>${irsd_issue_qty}</td>`;
                    tblHtml += `<td>${irsd_pending_qty}</td>`;
                    tblHtml += `<td>${irsd_return_qty}</td>`;
                    // tblHtml += `<td>${irsd_non_rerurnable_qty}</td>`;
                    tblHtml += `<td>${irsd_return_qty_unit}</td>`;
                    tblHtml += `<td>${reason}</td>`;
                    tblHtml += `<td>${irsd_remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#ItemReturnSlipDetailTable tbody').append(tblHtml);
                }
            }

            if (formValue.form_type == "edit") {
                thisModal.modal('hide');
            } else {
                let formElement = document.getElementById('ItemReturnSlipDetailsForm');
                formElement.reset();
                jQuery('#irsd_item_id').val('').trigger('change');
                jQuery('#item_type_id').val('').trigger('change.select2');
                jQuery('#irsd_sr_no_id').val('').trigger('change.select2');
                jQuery('#irsd_batch_no_id').val('').trigger('change.select2');
                jQuery('#irsd_issue_qty').val('');
                jQuery('#issue_unit').val('');
                jQuery('#irsd_pending_qty').val('');
                jQuery('#irsd_return_qty').val('');
                jQuery('#irsd_non_rerurnable_qty').val('');
                jQuery('#irsd_reason_id').val('').trigger('change.select2');
                jQuery('#irsd_remark').val('');
                jQuery('#irsd_sr_no_or_batch_no').val('');

                setTimeout(function () {
                    $(formElement).removeClass('was-validated');
                    $(formElement).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                    thisModal.find('.error').removeClass('error');

                    let $select = jQuery('#irsd_item_id');
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
        toastr.error('Please Select Item');
    }
});
// Item Return Slip Details Form Submit End

// Main form submit Stary
$('#commonItemReturnSlipForm').on('submit', function (e) {
    e.preventDefault();
    let form = this;
    var dateValue = document.getElementById("irs_date").value.trim();
    if (!isValidDate(dateValue)) {
        toastr.error("Please Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ItemReturnSlipModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ItemReturnSlipModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ItemReturnSlipModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#ItemReturnSlipModal').find('#commonItemReturnSlipForm').find('#id').val();
    var formUrl = formId != undefined && formId != "" ? "update-item_return_slip" : "store-item_return_slip";
    var data = new FormData(form);

    var isValid = true;

    jQuery.each(irs_details_data, function (index, item) {
        if (item.irsd_return_qty === null || item.irsd_return_qty === undefined || item.irsd_return_qty === '') {
            isValid = false;
            return false; // break loop
        }
    });

    if (!isValid) {
        toastr.error('Please Enter Return Qty.');
        return false;
    }

    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#ItemReturnSlipModal').find('#submitbtn').prop('disabled', true);
    data.append('irs_details_data', JSON.stringify(irs_details_data ?? []));
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (irs_details_data.length > 0 && !jQuery.isEmptyObject(irs_details_data)) {
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
                        jQuery('#ItemReturnSlipModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formId == undefined || formId == "") {
                        function nextFn() {
                            document.getElementById("commonItemReturnSlipForm").reset();
                            const form = document.getElementById("commonItemReturnSlipForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }

                            $("#irs_type_id").val('From Issue').trigger('change');
                            setSelect2Readonly('#commonItemReturnSlipForm #irs_type_id', false);
                            $("#irs_employee_id").val('').trigger('change');
                            irs_details_data = [];
                            jQuery('#ItemReturnSlipDetailTable tbody').empty();
                            jQuery('#pendingIssueDataTable tbody').empty();
                            getLatestItemReturnSlipNo();
                            jQuery('#commonItemReturnSlipForm').find('.toggleModalBtn').prop('disabled', true);
                            jQuery('#ItemReturnSlipModal').find('#irs_sequence').focus();
                        }
                        toastSuccess(data.response_message, nextFn);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#ItemReturnSlipModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#ItemReturnSlipModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#ItemReturnSlipModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#ItemReturnSlipModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#ItemReturnSlipModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        toastr.error('Please Add At Least One Item Return Slip Details.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ItemReturnSlipModal').find('#submitbtn').prop('disabled', false);
    }
});
// Main form submit End

// edit time fill item return slip table start bahar je table ma data dekhaay che e aa fucntion mathi aavtu hoy
function fillItemReturnSlipDetailsTable() {
    let thisModal = jQuery('#ItemReturnSlipDetailsModal');
    let tblHtml = ``;
    if (irs_details_data.length > 0) {
        for (let key in irs_details_data) {
            var formIndx = irs_details_data.indexOf(irs_details_data[key]);

            var irsd_item_id = irs_details_data[key].irsd_item_id ? irs_details_data[key].irsd_item_id : null;
            var item_name = irsd_item_id != '' ? thisModal.find('#irsd_item_id option[value="' + irsd_item_id + '"]').text() : '';

            // var item_type_id = irs_details_data[key].item_type_id ? irs_details_data[key].item_type_id : null;
            // var irsd_item_type = irs_details_data[key].item_type ? irs_details_data[key].item_type : null;
            // var item_type = item_type_id != '' ? thisModal.find('#item_type_id option[value="' + item_type_id + '"]').text() : '';
            // var item_type = item_type_id != '' ? thisModal.find('#item_type_id option[value="' + item_type_id + '"]').text() : '';

            var item_type_id = irs_details_data[key].item_type_id ? irs_details_data[key].item_type_id : null;
            var item_type = item_type_id != '' ? thisModal.find('#item_type_id option[value="' + item_type_id + '"]').text() : '';

            var irsd_sr_no_id = irs_details_data[key].irsd_sr_no_id ? irs_details_data[key].irsd_sr_no_id : null;
            var sr_no = irsd_sr_no_id != '' ? thisModal.find('#irsd_sr_no_id option[value="' + irsd_sr_no_id + '"]').text() : '';

            // var irsd_issue_qty = irs_details_data[key].irsd_issue_qty ? parseFloat(irs_details_data[key].irsd_issue_qty).toFixed(3) : "";

            var irsd_pending_qty = irs_details_data[key].irsd_pending_qty ? parseFloat(irs_details_data[key].irsd_pending_qty).toFixed(3) : "";

            var irsd_return_qty = irs_details_data[key].irsd_return_qty ? parseFloat(irs_details_data[key].irsd_return_qty).toFixed(3) : "";


            var irsd_non_rerurnable_qty = irs_details_data[key].irsd_non_rerurnable_qty ? parseFloat(irs_details_data[key].irsd_non_rerurnable_qty).toFixed(3) : "";

            var grnd_rate_unit = irs_details_data[key].grnd_rate_unit ? parseFloat(irs_details_data[key].grnd_rate_unit).toFixed(2) : "";

            var irsd_reason_id = irs_details_data[key].irsd_reason_id ? irs_details_data[key].irsd_reason_id : null;

            var reason = irsd_reason_id != '' ? thisModal.find('#irsd_reason_id option[value="' + irsd_reason_id + '"]').text() : '';

            var irsd_sr_no_or_batch_no = irs_details_data[key].irsd_sr_no_or_batch_no ? irs_details_data[key].irsd_sr_no_or_batch_no : '';

            var irsd_remark = irs_details_data[key].irsd_remark != "" && irs_details_data[key].irsd_remark != undefined ? irs_details_data[key].irsd_remark : '';
            var issue_unit = irs_details_data[key].issue_unit != "" ? irs_details_data[key].issue_unit : '';

            var irsd_issue_qty = irs_details_data[key].irsd_issue_qty ? parseFloat(irs_details_data[key].irsd_issue_qty).toFixed(3) : "";

            var irsd_iisd_id = irs_details_data[key].irsd_iisd_id != "" ? irs_details_data[key].irsd_iisd_id : '';

            // var in_use = irs_details_data[key].in_use == true ? true : false;
            tblHtml += `<tr>`;
            tblHtml += `<td>`;
            tblHtml += DetailsActionDropdown('editIrsDetails', 'removeIrsDetails');
            tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;
            // tblHtml += `<td>         
            //     <div>
            //         <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
            //             <i class="ri-more-2-fill"></i>
            //         </a>
            //         <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
            //             <li><a class="dropdown-item edit-item-btn" onclick="editIrsDetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i>Edit</a></li>
            //             <li><a class="dropdown-item remove-item-btn" onclick="removeIrsDetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li>
            //         </ul>
            //     </div>
            //     <input type="hidden" name="form_indx" value="${formIndx}"/>
            // </td>`;
            tblHtml += `<td>${item_name}
            <input type="hidden" name="irsd_iisd_id" value="${irsd_iisd_id}">
            </td>`;
            tblHtml += `<td>${item_type}</td>`;
            tblHtml += `<td>${irsd_sr_no_or_batch_no}</td>`;
            tblHtml += `<td>${irsd_issue_qty}</td>`;
            if (irsd_iisd_id > 0) {
                tblHtml += `<td>${irsd_pending_qty}</td>`;
            } else {
                tblHtml += `<td></td>`;
            }
            tblHtml += `<td>${irsd_return_qty}</td>`;
            // tblHtml += `<td>${irsd_non_rerurnable_qty}</td>`;
            tblHtml += `<td>${issue_unit}</td>`;
            tblHtml += `<td>${reason}</td>`;
            tblHtml += `<td>${irsd_remark}</td>`;
            tblHtml += `</tr>`;
        }
        jQuery('#ItemReturnSlipDetailTable tbody').empty();
        jQuery('#ItemReturnSlipDetailTable tbody').append(tblHtml);
    }
}
// edit time fill table end

// edit item return slip details
function editIrsDetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();
    fillIrsDetailsForm(formIndx, rawIndx);
}

// item return slip details form edit 3 dots per click kari aetle je form khule che aema je data aave che e aa fucntion thi aavto hoy.
function fillIrsDetailsForm(formIndx, rawIndx) {
    let thisForm = jQuery('#ItemReturnSlipDetailsModal');
    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    var frmData = irs_details_data[formIndx];
    // thisForm.find("#irsd_id").val(frmData.irsd_id);
    thisForm.find("#irsd_iisd_id").val(frmData.irsd_iisd_id);
    thisForm.find("#irsd_return_qty_unit").val(frmData.issue_unit);
    thisForm.find("#irsd_item_id").val(frmData.irsd_item_id).trigger("change");
    thisForm.find("#item_type_id").val(frmData.item_type_id).trigger("change");
    if (frmData.irsd_iisd_id != "0" || frmData.irsd_iisd_id != 0) {
        thisForm.find("#irsd_return_qty").attr('max', parseFloat(frmData.irsd_pending_qty).toFixed(3));
        thisForm.find("#irsd_issue_qty").val(parseFloat(frmData.irsd_issue_qty).toFixed(3));
        thisForm.find("#irsd_pending_qty").val(frmData.irsd_pending_qty != "" ? parseFloat(frmData.irsd_pending_qty).toFixed(3) : "");
        setTimeout(() => {
            setSelect2Readonly('#ItemReturnSlipDetailsModal #irsd_item_id', true);
        }, 500);
    }
    getBatchNoAndSrNoReturnSlipData().done(function (response) {
        setTimeout(() => {
            // thisForm.find("#irsd_sr_no_id").val(zeroToEmpty(frmData.irsd_sr_no_id) ).trigger('change');
            thisForm.find("#irsd_sr_no_id").val(zeroToEmpty(frmData.irsd_sr_no_id)).trigger('change.select2');
        }, 500);
    });
    setSelect2Readonly('#ItemReturnSlipDetailsForm #irsd_sr_no_id', true);
    getBatchNoAndSrNoReturnSlipData().done(function (response) {
        setTimeout(() => {
            // thisForm.find("#irsd_batch_no_id").val(zeroToEmpty(frmData.irsd_batch_no_id)).trigger('change');
            thisForm.find("#irsd_batch_no_id").val(zeroToEmpty(frmData.irsd_batch_no_id)).trigger('change.select2');
        }, 500);
    });
    setSelect2Readonly('#ItemReturnSlipDetailsForm #irsd_batch_no_id', true);
    // thisForm.find("#issue_unit").val(frmData.issue_unit);
    thisForm.find("#irsd_sr_no_or_batch_no").val(frmData.irsd_sr_no_or_batch_no);
    var decPlaces = (frmData.item_type_id == 'film') ? 0 : 3;
    thisForm.find("#irsd_return_qty").val(frmData.irsd_return_qty != "" && frmData.irsd_return_qty != undefined ? parseFloat(frmData.irsd_return_qty).toFixed(decPlaces) : "");
    thisForm.find("#irsd_issue_qty").val(frmData.irsd_issue_qty != "" && frmData.irsd_issue_qty != undefined ? parseFloat(frmData.irsd_issue_qty).toFixed(3) : "");
    thisForm.find("#irsd_non_rerurnable_qty").val(frmData.irsd_non_rerurnable_qty != "" && frmData.irsd_non_rerurnable_qty != undefined ? parseFloat(frmData.irsd_non_rerurnable_qty).toFixed(decPlaces) : "");
    if (frmData.item_type_id == 'film') {
        thisForm.find('#irsd_return_qty, #irsd_non_rerurnable_qty').removeClass('isNumberKey').addClass('isNumberKeyNotDot');
        thisForm.find('#irsd_return_qty, #irsd_non_rerurnable_qty').attr('onblur', 'formatPoints(this, 0)');
    } else {
        thisForm.find('#irsd_return_qty, #irsd_non_rerurnable_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        thisForm.find('#irsd_return_qty, #irsd_non_rerurnable_qty').attr('onblur', 'formatPoints(this, 3)');
    }
    thisForm.find("#irsd_reason_id").val(frmData.irsd_reason_id).trigger("change");
    thisForm.find("#irsd_remark").val(frmData.irsd_remark != "" && frmData.irsd_remark != undefined ? frmData.irsd_remark : "");

    if (frmData.irsd_id != 0) {
        setSelect2Readonly('#item_type_id', true);
    }

    thisForm.modal('show');
}

// remove item return slip details
function removeIrsDetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        removeFormIssueObj(formIndx);
        jQuery(th).closest("tr").remove();
    });
}

function removeFormIssueObj(formIndx) {
    delete irs_details_data[formIndx];
    irs_details_data = irs_details_data.filter(element => element != null);
    jQuery('#ItemReturnSlipDetailTable tbody').empty();
    fillItemReturnSlipDetailsTable();
}

jQuery('#ItemReturnSlipModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#ItemReturnSlipModal');
    thisForm.find('#id').val('');
    irs_details_data = [];
    jQuery('#ItemReturnSlipDetailTable tbody').empty();
    document.getElementById("commonItemReturnSlipForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).prop('checked', false);
    });
});

jQuery('#ItemReturnSlipDetailsModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#ItemReturnSlipDetailsModal');
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    thisModal.find("#irsd_id").val(0);
    thisModal.find("#irsd_iisd_id").val('');
    thisModal.find("#irsd_sr_no_or_batch_no").val('');
    jQuery('#ItemReturnSlipDetailsForm').trigger("reset");
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('irs_special_note');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }
});

// aa fucntion ma jyare pending wala modal ma data aave che e aa code thi data get thayine aavta hoy che.
async function fillPendingItemIssueSlip() {
    let empId = jQuery('#irs_employee_id option:selected').val();
    var irs_type_id = jQuery('#irs_type_id option:selected').val();
    var thisModal = jQuery('#ItemReturnSlipPendingModal');
    var thisForm = jQuery('#ItemReturnSlipDetailsForm');

    if (irs_type_id != undefined) {
        if (irs_type_id == 'From Issue') {
            jQuery.ajax({
                url: 'get-pending_issue_slip_list_for_irs',
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    if (data.response_code == 1 && data.issue_slip_data.length > 0) {
                        // new code
                        var usedParts = [];
                        var totalDisb = 0;
                        var found = 0;

                        thisForm.find('#pendingIssueDataTable tbody input[name="form_indx"]').each(function (indx) {
                            let frmIndx = jQuery(this).val();
                            let jbEorkOrderId = issue_slip_data[frmIndx].iisd_id;
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

                        if (data.issue_slip_data.length > 0 && !jQuery.isEmptyObject(data.issue_slip_data)) {
                            found = 1;
                            for (let idx in data.issue_slip_data) {
                                var inUse = isUsed(data.issue_slip_data[idx].iisd_id);
                                var in_use = data.issue_slip_data[idx].in_use == true ? 'readonly' : '';
                                totalEntry++;
                                tblHtml += `
                                <tr>
                                    <td><input type="checkbox" name="iisd_id[]" class="simple-check checkbox-filter-remove ${inUse ? 'in-use' : ''}" id="iisd_ids_${data.issue_slip_data[idx].iisd_id}" value="${data.issue_slip_data[idx].iisd_id}" ${inUse ? 'checked' : ''} ${in_use}/></td>
                                    <td>${data.issue_slip_data[idx]?.iisd_sr_no_or_batch_no ?? ''}</td>
                                    <td>${data.issue_slip_data[idx].iis_number}</td>
                                    <td>${data.issue_slip_data[idx].iis_date}</td>
                                    <td>${data.issue_slip_data[idx].user_name != null ? data.issue_slip_data[idx].user_name : ""}</td>
                                    <td>${data.issue_slip_data[idx].item_name}</td>
                                    <td>${data.issue_slip_data[idx].item_type}</td>
                                    <td>${parseFloat(data.issue_slip_data[idx].iisd_issue_qty).toFixed(3)}</td>
                                    <td>${parseFloat(data.issue_slip_data[idx].irsd_pending_qty).toFixed(3)}</td>
                                    <td>${data.issue_slip_data[idx].unit}</td>
                                </tr>`;
                            }
                        } else {
                            tblHtml += `<tr class="centeralign" id="noPendingPo">
                                <td colspan="10">No Pending Issue Slip Available</td>
                            </tr>`;
                        }

                        var $table = jQuery("#ItemReturnSlipPendingModal").find('#pendingIssueDataTable');
                        if (jQuery.fn.DataTable.isDataTable($table)) {
                            $table.DataTable().clear().destroy();
                        }
                        jQuery('#pendingIssueDataTable tbody').empty().append(tblHtml);
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
                        if (irs_type_id == 'Manual') {
                            jQuery('.toggleModalBtn').prop('disabled', true);
                        } else {
                            jQuery('.toggleModalBtn').prop('disabled', false);
                        }
                    } else {
                        jQuery('.toggleModalBtn').prop('disabled', true);
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
    }
}

jQuery('#ItemReturnSlipPendingModal').on('show.bs.modal', function (e) {
    var dt = jQuery('#pendingIssueDataTable').DataTable();
    fixDataTableColumnsUntilAdjusted(dt);

    var usedItemParts = [];
    var totalItemDisb = 0;
    var formIdcheck = jQuery('#commonItemReturnSlipForm').find('input[name="id"]').val();
    jQuery('#ItemReturnSlipDetailTable tbody input[name="form_indx"]').each(function (indx) {
        var frmIndx = jQuery(this).val();
        var prItemId = irs_details_data[frmIndx].iisd_id;
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
    jQuery('#pendingIssueDataTable tbody tr').each(function (indx) {
        totalEntry++;
        var checkField = jQuery(this).find('input[name="iisd_id[]"]');
        var partId = jQuery(checkField).val();
        if (formIdcheck == undefined) {
            if (irs_details_data.length > 0) {
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

// check all
jQuery('#checkall-issue_slip_data').click(function () {
    if (jQuery(this).is(':checked')) {
        jQuery("#ItemReturnSlipPendingModal").find("[id^='iisd_ids_']:not(.in-use)").prop('checked', true).trigger('change');
    } else {
        jQuery("#ItemReturnSlipPendingModal").find("[id^='iisd_ids_']:not(.in-use)").prop('checked', false).trigger('change');
    }
});

// get selected pending Issue Slip
$('#addPendigIISForm').on('submit', function (e) {
    e.preventDefault();
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#ItemReturnSlipPendingModal').find('#submitbtn').prop('disabled', true);

    let chkArr = [];
    var formId = jQuery('#commonItemReturnSlipForm').find('input[name="id"]').val();

    jQuery("#addPendigIISForm").find("[id^='iisd_ids_']:checked")
        .each(function () {
            chkArr.push(jQuery(this).val());
        });

    // No checkbox selected
    if (chkArr.length === 0) {
         toastr.error('Select Issue Slip From Pending');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ItemReturnSlipPendingModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (formId != undefined && formId != "") {
        var pend_url = "get-issue_slip_part_data_irs?id=" + formId;
    } else {
        var pend_url = "get-issue_slip_part_data_irs";
    }

    jQuery.ajax({
        url: pend_url,
        type: 'GET',
        data: { iisd_ids: chkArr.join(',') },
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                if (data.issue_slip_data && data.issue_slip_data.length > 0) {
                    irs_details_data = [];
                    for (let ind in data.issue_slip_data) {
                        // jQuery('#ItemReturnSlipPendingModal').find("#irsd_iisd_id").val(data.issue_slip_data.iisd_id); // add new line
                        irs_details_data.push(data.issue_slip_data[ind]);
                    }
                    fillItemReturnSlipDetailsTable(data.issue_slip_data);
                    jQuery('#ItemReturnSlipPendingModal').find('#pending_btn').prop('disabled', true);
                    jQuery('.toggleModalBtn').prop('disabled', true);
                    setSelect2Readonly('#commonItemReturnSlipForm #irs_type_id', true);
                } else {
                    fillItemReturnSlipDetailsTable([]);
                    jQuery('#ItemReturnSlipPendingModal').find('#pending_btn').prop('disabled', false);
                    setSelect2Readonly('#commonItemReturnSlipForm #irs_type_id', false);
                }
                jQuery("#ItemReturnSlipPendingModal").modal('hide');
            } else {
                fillItemReturnSlipDetailsTable([]);
                jQuery('#ItemReturnSlipPendingModal').find('#pending_btn').prop('disabled', false);
                setSelect2Readonly('#commonItemReturnSlipForm #irs_type_id', false);
            }
            jQuery('#ItemReturnSlipPendingModal').find('#submitbtn').prop('disabled', false);
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        },
        error: function (jqXHR) {
            if (jqXHR.status == 401) {
                toastr.error(jqXHR.statusText);
            } else {
                toastr.error('Something went wrong!');
                console.log(jqXHR.responseText);
            }
            jQuery('#ItemReturnSlipPendingModal').find('#submitbtn').prop('disabled', false);
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        }
    });
});

jQuery('#ItemReturnSlipPendingModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = jQuery('input[name=irsd_iisd_id]').val() != '' ? document.getElementById('irs_special_note') : document.getElementById('irs_employee_id');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }
});