

jQuery('#dyntable tbody').on('click', '.edit-instrument', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#InstrumentModal').find('#id').val(data["ins_id"]);
    if (data && data["ins_id"]) {
        fetchAndFillInstrument(data["ins_id"]);
    }
});


function fetchAndFillInstrument(id) {
    if (!id) return;
    jQuery('#InstrumentModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-instrument",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.instrument != null) {
                jQuery('#InstrumentModal').find('#id').val(data.instrument.ins_id != "" ? data.instrument.ins_id : "");
                jQuery('#InstrumentModal').find('#pending_btn').prop('disabled', true);
                jQuery('#InstrumentModal').find('#table_unique_id').val(data.instrument.table_unique_id != "" ? data.instrument.table_unique_id : "");

                jQuery('#InstrumentModal').find('#table_pk_id').val(data.instrument.table_pk_id != "" ? data.instrument.table_pk_id : "");
                jQuery('#InstrumentModal').find('#item_id').val(data.instrument.item_id);
                jQuery('#InstrumentModal').find('#item_group').val(data.instrument.item_group);
                jQuery('#InstrumentModal').find('#main_group').val(data.instrument.main_group);

                jQuery('#InstrumentModal').find("#grn_no").val(data.instrument.grn_number != null ? data.instrument.grn_number : '');
                jQuery('#InstrumentModal').find("#grn_date").val(data.instrument.grn_date != null ? data.instrument.grn_date : '');
                jQuery('#InstrumentModal').find("#supplier").val(data.instrument.supplier_name);
                jQuery('#InstrumentModal').find("#challan_no").val(data.instrument.grn_challan_number != null ? data.instrument.grn_challan_number : '');
                jQuery('#InstrumentModal').find("#challan_date").val(data.instrument.grn_challan_date != null ? data.instrument.grn_challan_date : '');

                jQuery('#InstrumentModal').find('#ins_instrument_no').val(data.instrument.ins_instrument_no != "" ? data.instrument.ins_instrument_no : "");
                jQuery('#InstrumentModal').find('#ins_instrument_name').val(data.instrument.ins_instrument_name != "" ? data.instrument.ins_instrument_name : "");

                jQuery('#InstrumentModal').find('#ins_make').val(data.instrument.ins_make != "" ? data.instrument.ins_make : "");

                jQuery('#InstrumentModal').find('#ins_mfg_sr_no').val(data.instrument.ins_mfg_sr_no != "" ? data.instrument.ins_mfg_sr_no : "");

                jQuery('#InstrumentModal').find('#ins_doc_ref_no').val(data.instrument.ins_doc_ref_no != "" ? data.instrument.ins_doc_ref_no : "");

                jQuery('#InstrumentModal').find('#ins_cali_req').val(data.instrument.ins_cali_req != "" ? data.instrument.ins_cali_req : "").trigger('change');

                jQuery('#InstrumentModal').find('#ins_cali_freq').val(data.instrument.ins_cali_freq != "" ? data.instrument.ins_cali_freq : "");
                jQuery('#InstrumentModal').find('#ins_last_cali_date').val(data.instrument.ins_last_cali_date != "" ? data.instrument.ins_last_cali_date : "");
                jQuery('#InstrumentModal').find('#ins_next_cali_due_date').val(data.instrument.ins_next_cali_due_date != "" ? data.instrument.ins_next_cali_due_date : "");

                if (data.instrument.ins_cali_certificate != "" && data.instrument.ins_cali_certificate != null && data.instrument.ins_cali_certificate != undefined) {
                    let fullPath = data.instrument.ins_cali_certificate;
                    let fileName = fullPath.split('/').pop();
                    jQuery('#InstrumentModal').find("#calibration_certificate_doc").val(fullPath);
                    jQuery('#InstrumentModal').find('#calibration_certificate_prev').attr('href', uploadURL + fullPath).removeClass('hide');
                    jQuery('#InstrumentModal').find('#calibration_certificate_remove').addClass('i-block').removeClass('hide');
                    let fileInput = jQuery('#InstrumentModal').find('#calibration_certificate');
                    if (fileInput.length) {
                        let newFile = new DataTransfer();
                        newFile.items.add(new File([""], fileName));
                        fileInput[0].files = newFile.files;
                    }
                } else {
                    jQuery('#InstrumentModal').find('#calibration_certificate_doc').val('');
                    jQuery('#InstrumentModal').find('#calibration_certificate_prev').attr('href', '#').addClass('hide');
                    jQuery('#InstrumentModal').find('#calibration_certificate_remove').removeClass('i-block').addClass('hide');
                    jQuery('#InstrumentModal').find('#calibration_certificate').val('');
                }

                jQuery('#InstrumentModal').find('#ins_status').val(data.instrument.ins_status || '').trigger('change.select2');
                // jQuery('#commonInstrumentForm').find('#ins_cali_req').val('Yes').trigger('change.select2');


                jQuery('#InstrumentModal').find('#submitbtn').prop('disabled', false);

                const form = document.getElementById("commonInstrumentForm");
                if (Number(data.instrument.current_location_id) == Number(data.instrument.login_location)) {
                    jQuery("#submitbtn").prop('disabled', false);

                } else {
                    jQuery("#submitbtn").prop('disabled', true);

                }
                InstrumentCaliData();
                if (data.is_used) {
                    jQuery('#InstrumentModal').find('#ins_cali_req').prop('disabled', true).trigger('change.select2');
                    jQuery('#InstrumentModal').find('#ins_last_cali_date').prop('readonly', true).css('pointer-events', 'none').datepicker('disable');
                    jQuery('#InstrumentModal').find('#ins_next_cali_due_date').prop('readonly', true).css('pointer-events', 'none').datepicker('disable');
                    jQuery('#InstrumentModal').find('#ins_cali_freq').prop('readonly', true);
                    jQuery('#InstrumentModal').find('#calibration_certificate').prop('disabled', true);
                    jQuery('#InstrumentModal').find('#calibration_certificate_remove').removeClass('i-block').addClass('hide');
                } else {
                    jQuery('#InstrumentModal').find('#ins_cali_req').prop('disabled', false).trigger('change.select2');
                }

                jQuery('#InstrumentModal').find('#add_new').show();
                if (form) form.classList.remove('was-validated');
                jQuery('#InstrumentModal').find('#ins_instrument_no').focus();
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

// Reset button click for material mpt modal
jQuery('#resetbtn').on('click', function () {
    var formId = jQuery('#InstrumentModal').find('#id').val();
    jQuery('#GrnPendingForInstrumentModal').find('#submitbtn').prop('disabled', false);
    if (!formId) {
        var form = document.getElementById("commonInstrumentForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        jQuery('#commonInstrumentForm').find('.toggleModalBtn').prop('disabled', true);
        jQuery('#commonInstrumentForm').find('#ins_status').val('Active').trigger('change.select2');
        jQuery('#commonInstrumentForm').find('#ins_cali_req').prop('disabled', false).val('').trigger('change.select2');
        getPendingInstrument();
        InstrumentCaliData();
        jQuery('#commonInstrumentForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
        jQuery('#commonInstrumentForm').find('#grn_no').prop({ tabindex: -1, readonly: true });
        jQuery('#commonInstrumentForm').find('#grn_date').prop({ tabindex: -1, readonly: true });
        jQuery('#commonInstrumentForm').find('#supplier').prop({ tabindex: -1, readonly: true });
        jQuery('#commonInstrumentForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
        jQuery('#commonInstrumentForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
        jQuery('#commonInstrumentForm').find('#item_group').prop({ tabindex: -1, readonly: true });
        jQuery('#commonInstrumentForm').find('#main_group').prop({ tabindex: -1, readonly: true });
        jQuery('#commonInstrumentForm').find('#table_pk_id').val('');
        jQuery('#commonInstrumentForm').find('#id').val('');
        jQuery('#commonInstrumentForm').find('#item_id').val('');
        clearCertificate();

        focusPendingButton('#InstrumentModal');
        // setTimeout(() => {
        //     const btn = jQuery('#InstrumentModal').find('.toggleModalBtn').filter(':visible').not(':disabled').first();
        //     if (btn.length > 0) {
        //         btn.trigger('focus');
        //         btn.css({'border-color': '#22b378','outline-offset': '2px','box-shadow': '0 0 0 0.25rem rgba(34, 179, 120, 0.25)'});
        //         btn.one('blur', function () {
        //             jQuery(this).css({'border-color': '','outline-offset': '','box-shadow': ''});
        //         });
        //     }
        // }, 1500);
    } else {
        fetchAndFillInstrument(formId);
    }
});


// Main form submit Stary
$('#commonInstrumentForm').on('submit', function (e) {

    e.preventDefault();
    let form = this;

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#InstrumentModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var lastCalDate = jQuery('#ins_last_cali_date').val();
    var nextCalDueDate = jQuery('#ins_next_cali_due_date').val();

    let lastDate = parseDate(lastCalDate);
    let nextDate = parseDate(nextCalDueDate);

    if (nextDate < lastDate) {
        toastr.error('Next Calibration Due Date must be greater than Last Calibration Date.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#InstrumentModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var ins_cali_freq = jQuery('#ins_cali_freq').val();
    if (ins_cali_freq != '' && ins_cali_freq == 0) {
        toastr.error('Calibration Freq. must be greater than 0.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#InstrumentModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var table_pk_id = jQuery('#table_pk_id').val();
    if (table_pk_id == '' || table_pk_id == 0) {
        toastr.error('Select At Least One Pending.');
        // toastr.error('Select Atleast One Pending.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#InstrumentModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#InstrumentModal').find('#commonInstrumentForm').find('#id').val();
    var ins_instrument_no = jQuery('#ins_instrument_no').val();

    var InstrumentUrl = formId != undefined && formId != "" ? "verify-instrument?ins_instrument_no=" + encodeURIComponent(ins_instrument_no) + "&ins_id=" + formId : "verify-instrument?ins_instrument_no=" + encodeURIComponent(ins_instrument_no);
    var formUrl = formId != undefined && formId != "" ? "update-instrument" : "store-instrument";
    var data = new FormData(form);
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#InstrumentModal').find('#submitbtn').prop('disabled', true);
    if ((ins_instrument_no != '' && ins_instrument_no != undefined)) {
        $.ajax({
            url: InstrumentUrl,
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.response_code == 1) {
                    toastr.error(res.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#InstrumentModal').find('#submitbtn').prop('disabled', false);
                } else {
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
                                    jQuery('#InstrumentModal').find('#submitbtn').prop('disabled', false);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                         document.getElementById("commonInstrumentForm").reset();
                                         clearCertificate();
                                         const form = document.getElementById("commonInstrumentForm");
                                         if (form) {
                                             form.classList.remove('was-validated');
                                         }
                                        jQuery('#commonInstrumentForm').find('.toggleModalBtn').prop('disabled', true);
                                        jQuery('#commonInstrumentForm').find('#ins_status').val('Active').trigger('change.select2');
                                        jQuery('#commonInstrumentForm').find('#ins_cali_req').prop('disabled', false).val('').trigger('change.select2');
                                        jQuery('#commonInstrumentForm').find('#table_pk_id').val('');
                                        jQuery('#commonInstrumentForm').find('#id').val('');
                                        jQuery('#commonInstrumentForm').find('#item_id').val('');
                                        CheckInstrument();
                                        getPendingInstrument();
                                        jQuery('#commonInstrumentForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonInstrumentForm').find('#grn_no').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonInstrumentForm').find('#grn_date').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonInstrumentForm').find('#supplier').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonInstrumentForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonInstrumentForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonInstrumentForm').find('#item_group').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonInstrumentForm').find('#main_group').prop({ tabindex: -1, readonly: true });

                                        jQuery('#commonInstrumentForm #ins_cali_freq').prop('readonly', true);
                                        jQuery('#commonInstrumentForm #ins_last_cali_date').prop('readonly', true).css('pointer-events', 'none');
                                        jQuery('#commonInstrumentForm #ins_next_cali_due_date').prop('readonly', true).css('pointer-events', 'none');
                                        jQuery('#commonInstrumentForm #ins_cali_freq').val('');
                                        jQuery('#commonInstrumentForm #ins_last_cali_date').val('');
                                        jQuery('#commonInstrumentForm #ins_next_cali_due_date').val('');
                                        jQuery('#GrnPendingForInstrumentModal').find('#submitbtn').prop('disabled', false);
                                        focusPendingButton('#InstrumentModal');
                                    }
                                    toastSuccess(data.response_message, nextFn);

                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#InstrumentModal').find('#submitbtn').prop('disabled', false);
                                }
                                else {
                                    toastr.error(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#InstrumentModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#InstrumentModal').find('#submitbtn').prop('disabled', false);
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
                                jQuery('#InstrumentModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#InstrumentModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});


$('#InstrumentModal').on('show.bs.modal', function () {
    var formId = jQuery('#InstrumentModal').find('#commonInstrumentForm').find('#id').val();
    if (formId == '' || formId == undefined) {
        jQuery('#commonInstrumentForm').find('#ins_status').val('Active').trigger('change.select2');
        jQuery('#submitbtn').prop('disabled', false);
        // jQuery('#commonInstrumentForm').find('#ins_cali_req').val('Yes').trigger('change.select2');
        CheckInstrument();
        getPendingInstrument();
        InstrumentCaliData();
        focusPendingButton('#InstrumentModal');
    }

    if (formId != "" && formId != undefined) {
        jQuery('#commonInstrumentForm').find('#pending_btn').prop('disabled', true);
    }

    if (formId && formId !== "") {
        jQuery('#InstrumentModal').find('#add_new').show();
    } else {
        jQuery('#InstrumentModal').find('#add_new').hide();
    }
});

jQuery('#InstrumentModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#InstrumentModal');
    thisForm.find('#id').val('');
    document.getElementById("commonInstrumentForm").reset();
    clearCertificate();
    jQuery('#InstrumentModal').find('#pending_btn').prop('disabled', true);
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).prop('checked', false);
    });
    jQuery('#InstrumentModal').find('#add_new').hide();
});

jQuery('#InstrumentModal').on('click', '#add_new', function () {
    jQuery('#InstrumentModal').find('#id').val('');
    document.getElementById("commonInstrumentForm").reset();
    clearCertificate();
    const form = document.getElementById("commonInstrumentForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#commonInstrumentForm').find('.toggleModalBtn').prop('disabled', true);
    jQuery('#commonInstrumentForm').find('#ins_status').val('Active').trigger('change.select2');
    jQuery('#commonInstrumentForm').find('#ins_cali_req').prop('disabled', false).val('').trigger('change.select2');
    getPendingInstrument();
    InstrumentCaliData();
    jQuery('#commonInstrumentForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
    jQuery('#commonInstrumentForm').find('#grn_no').prop({ tabindex: -1, readonly: true });
    jQuery('#commonInstrumentForm').find('#grn_date').prop({ tabindex: -1, readonly: true });
    jQuery('#commonInstrumentForm').find('#supplier').prop({ tabindex: -1, readonly: true });
    jQuery('#commonInstrumentForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
    jQuery('#commonInstrumentForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
    jQuery('#commonInstrumentForm').find('#item_group').prop({ tabindex: -1, readonly: true });
    jQuery('#commonInstrumentForm').find('#main_group').prop({ tabindex: -1, readonly: true });
    jQuery('#commonInstrumentForm').find('#id').val('');
    jQuery('#commonInstrumentForm').find('#table_pk_id').val('');
    jQuery('#commonInstrumentForm').find('#item_id').val('');
    jQuery("#commonInstrumentForm #ins_status").val('Active').trigger("change.select2");
    jQuery('#submitbtn').prop('disabled', false);
    focusPendingButton('#InstrumentModal');
    jQuery('#InstrumentModal').find('#add_new').hide();
});

jQuery('#GrnPendingForInstrumentModal').on('hide.bs.modal', function (e) {
    // this.dataset.customHideFocus = 'true';
    // const input = jQuery('#table_pk_id').val() != '' ? document.getElementById('ins_instrument_no') : document.getElementById('pending_btn');
    // if (input) {
    //     setTimeout(() => {
    //         input.focus();
    //     }, 100);
    // }
});


// get pending probe ut from grn
function getPendingInstrument() {
    var thisForm = jQuery('#addPendigGrnForm');

    return jQuery.ajax({
        url: "get-pending_grn_list_for_instrument",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                if (data.response_code == 1 && data.opening_data.length > 0) {
                    var formIdblank = jQuery('#commonInstrumentForm').find('#id').val();
                    if (formIdblank != "" && formIdblank != undefined) {
                        jQuery('#pending_btn').prop('disabled', true);
                    } else {
                        jQuery('#pending_btn').prop('disabled', false);
                    }
                    // new code
                    var usedParts = [];
                    var totalDisb = 0;
                    var found = 0;

                    thisForm.find('#pendingGrnDataTable tbody input[name="form_indx"]').each(function (indx) {
                        let frmIndx = jQuery(this).val();

                        let jbEorkOrderId = opening_data[frmIndx].table_pk_id;
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
                    if (data.opening_data.length > 0 && !jQuery.isEmptyObject(data.opening_data)) {
                        var formIdblank = jQuery('#commonInstrumentForm').find('#id').val();
                        if (formIdblank != "" && formIdblank != undefined) {
                            jQuery('#pending_btn').prop('disabled', true);
                        } else {
                            jQuery('#pending_btn').prop('disabled', false);
                        }
                        found = 1;

                        for (let idx in data.opening_data) {
                            var inUse = isUsed(data.opening_data[idx].table_pk_id);
                            var in_use = data.opening_data[idx].in_use == true ? 'readonly' : '';
                            totalEntry++;
                            tblHtml += `
                                        <tr>
                                            <td>
                                                <input type="radio" name="table_pk_id[]" class="simple-check radio-filter-remove ${inUse ? 'in-use' : ''}" id="table_pk_ids_${data.opening_data[idx].table_pk_id}" value="${data.opening_data[idx].table_pk_id}" ${inUse ? 'checked' : ''} ${in_use} data-table_unique_id="${data.opening_data[idx].table_unique_id}"/>
                                            </td>
                                            <td>${data.opening_data[idx].table_unique_id}</td>
                                            <td>${data.opening_data[idx].grn_number != null ? data.opening_data[idx].grn_number : ''}</td>
                                            <td>${data.opening_data[idx].grn_date != null ? data.opening_data[idx].grn_date : ''}</td>
                                            <td>${data.opening_data[idx].supplier_name != null ? data.opening_data[idx].supplier_name : ''}</td>
                                            <td>${data.opening_data[idx].grn_challan_number != null ? data.opening_data[idx].grn_challan_number : ''}</td>
                                            <td>${data.opening_data[idx].grn_challan_date != null ? data.opening_data[idx].grn_challan_date : ''}</td>
                                            <td>${data.opening_data[idx].item_name != null ? data.opening_data[idx].item_name : ''}</td>
                                            <td>${data.opening_data[idx].item_group != null ? data.opening_data[idx].item_group : ''}</td>
                                            <td>${data.opening_data[idx].item_type != null ? data.opening_data[idx].item_type : ''}</td>
                                            <td>${data.opening_data[idx].qty != null ? parseFloat(data.opening_data[idx].qty).toFixed(3) : ''}</td>
                                            <td>${data.opening_data[idx].pending_qty != null ? parseFloat(data.opening_data[idx].pending_qty).toFixed(3) : ''}</td>
                                            <td>${data.opening_data[idx].unit}</td>
                                        </tr>`;

                        }

                    } else {

                        tblHtml += `<tr class="centeralign" id="noPendingPo">
                                                <td colspan="9">No Pending  Available</td>
                                            </tr>`;

                        jQuery('#pending_btn').prop('disabled', true);

                    }

                    var $table = jQuery("#GrnPendingForInstrumentModal").find('#pendingGrnDataTable');
                    if (jQuery.fn.DataTable.isDataTable($table)) {
                        $table.DataTable().clear().destroy();
                    }
                    jQuery('#pendingGrnDataTable tbody').empty().append(tblHtml);

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


                } else {
                    jQuery('.toggleModalBtn').prop('disabled', true);
                    //toastr.error(data.response_message);
                }

            } else {
                jQuery('.toggleModalBtn').prop('disabled', true);
                // toastr.error(data.response_message);
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
}

// get selected pending GRN 
$('#addPendigGrnForm').on('submit', function (e) {

    e.preventDefault();

    jQuery('#full-page-loader')
        .removeClass('hidden-loader')
        .addClass('loader-progress-whole-page');

    jQuery('#GrnPendingForInstrumentModal')
        .find('#submitbtn')
        .prop('disabled', true);

    let chkArr = [];
    let uniqueArr = [];

    jQuery("#addPendigGrnForm")
        .find("[id^='table_pk_ids_']:checked")
        .each(function () {
            chkArr.push(jQuery(this).val());
            uniqueArr.push(jQuery(this).data('table_unique_id'));
        });

    // No checkbox selected
    if (chkArr.length === 0) {
        toastr.error('Select At Least One Pending.');

        jQuery('#full-page-loader')
            .removeClass('loader-progress-whole-page')
            .addClass('hidden-loader');

        jQuery('#GrnPendingForInstrumentModal')
            .find('#submitbtn')
            .prop('disabled', false);

        return;
    }

    jQuery.ajax({
        url: 'get-pending_grn_for_instrument',
        type: 'GET',
        data: {
            table_pk_ids: chkArr.join(','),
            table_unique_ids: uniqueArr.join(',')
        },
        dataType: 'json',
        success: function (data) {

            if (data.response_code == 1) {
                if (data.opening_data) {

                    jQuery('#InstrumentModal').find("#table_pk_id").val(data.opening_data.table_pk_id);
                    jQuery('#InstrumentModal').find('#item_group').val(data.opening_data.item_group);
                    jQuery('#InstrumentModal').find("#main_group").val(data.opening_data.item_type);
                    jQuery('#InstrumentModal').find("#table_unique_id").val(data.opening_data.table_unique_id);
                    jQuery('#InstrumentModal').find("#item_id").val(data.opening_data.item_id);
                    jQuery('#InstrumentModal').find('#ins_instrument_name').val(data.opening_data.item_name);
                    jQuery('#InstrumentModal').find("#grn_no").val(data.opening_data.grn_number != null ? data.opening_data.grn_number : '');
                    jQuery('#InstrumentModal').find("#grn_date").val(data.opening_data.grn_date != null ? data.opening_data.grn_date : '');
                    jQuery('#InstrumentModal').find("#supplier").val(data.opening_data.supplier_name);
                    jQuery('#InstrumentModal').find("#challan_no").val(data.opening_data.grn_challan_number != null ? data.opening_data.grn_challan_number : '');
                    jQuery('#InstrumentModal').find("#challan_date").val(data.opening_data.grn_challan_date != null ? data.opening_data.grn_challan_date : '');
                    jQuery('.toggleModalBtn').prop('disabled', true);
                } else {
                    jQuery('#InstrumentModal').find("#table_pk_id").val('');
                    jQuery('#InstrumentModal').find('#item_group').val('');
                    jQuery('#InstrumentModal').find("#main_group").val('');
                    jQuery('#InstrumentModal').find("#table_unique_id").val('');
                    jQuery('#InstrumentModal').find("#item_id").val('');
                    jQuery('#InstrumentModal').find("#grn_no").val('');
                    jQuery('#InstrumentModal').find("#grn_date").val('');
                    jQuery('#InstrumentModal').find("#supplier").val('');
                    jQuery('#InstrumentModal').find("#challan_no").val('');
                    jQuery('#InstrumentModal').find("#challan_date").val('');
                    jQuery('.toggleModalBtn').prop('disabled', false);
                }

                jQuery("#GrnPendingForInstrumentModal").modal('hide');
            } else {

            }

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

            jQuery('#GrnPendingForInstrumentModal')
                .find('#submitbtn')
                .prop('disabled', false);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');
        }
    });
});

// for check radio
jQuery('#GrnPendingForInstrumentModal').on('show.bs.modal', function (e) {
    var dt = jQuery('#pendingGrnDataTable').DataTable();
    fixDataTableColumnsUntilAdjusted(dt);

    var usedParts = [];

    var table_pk_id = jQuery('#InstrumentModal').find('#table_pk_id').val();
    if (table_pk_id != "" && table_pk_id != null) {
        usedParts.push(Number(table_pk_id));
    }

    function isUsed(pjId) {
        if (usedParts.includes(Number(pjId))) {
            return true;
        }
        return false;
    }

    jQuery('#pendingGrnDataTable tbody tr').each(function (indx) {
        var checkField = jQuery(this).find('input[name="table_pk_id[]"]');
        var partId = jQuery(checkField).val();
        var inUse = isUsed(partId);

        if (inUse) {
            jQuery(checkField).prop('checked', true);
        } else {
            jQuery(checkField).prop('checked', false);
        }

    });
});

jQuery('#ins_instrument_no').on('change', function () {
    CheckInstrument();
});

function CheckInstrument() {

    var ins_instrument_no = jQuery('#ins_instrument_no').val();

    var formId = jQuery('#InstrumentModal').find('#commonInstrumentForm').find('#id').val();
    var formUrl = formId != undefined && formId != "" ? "verify-instrument?ins_instrument_no=" + encodeURIComponent(ins_instrument_no) + "&ins_id=" + formId : "verify-instrument?ins_instrument_no=" + encodeURIComponent(ins_instrument_no);

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

jQuery('#ins_cali_req').on('change', function () {
    InstrumentCaliData();
});


function InstrumentCaliData() {

    var ins_cali_req = jQuery('#commonInstrumentForm #ins_cali_req').val();

    if (ins_cali_req === "Yes") {

        jQuery('#commonInstrumentForm #ins_cali_freq').prop({ tabindex: 0, readonly: false, required: true });
        jQuery('#commonInstrumentForm #ins_last_cali_date').prop({ tabindex: 0, readonly: false, required: true }).css('pointer-events', 'auto');
        jQuery('#commonInstrumentForm #ins_next_cali_due_date').prop({ tabindex: 0, readonly: false, required: true }).css('pointer-events', 'auto');
        jQuery('#commonInstrumentForm #ins_last_cali_date').datepicker('enable');
        jQuery('#commonInstrumentForm #ins_next_cali_due_date').datepicker('enable');

        jQuery('label[for="ins_cali_freq"], label[for="ins_last_cali_date"], label[for="ins_next_cali_due_date"]').find('.astric').removeClass('hide d-none');

    } else {
        jQuery('#commonInstrumentForm #ins_cali_freq').prop({ tabindex: -1, readonly: true, required: false }).removeClass('is-invalid');
        jQuery('#commonInstrumentForm #ins_last_cali_date').prop({ tabindex: -1, readonly: true, required: false }).css('pointer-events', 'none').removeClass('is-invalid');
        jQuery('#commonInstrumentForm #ins_next_cali_due_date').prop({ tabindex: -1, readonly: true, required: false }).css('pointer-events', 'none').removeClass('is-invalid');
        jQuery('#commonInstrumentForm #ins_cali_freq').val('');
        jQuery('#commonInstrumentForm #ins_last_cali_date').val('');
        jQuery('#commonInstrumentForm #ins_next_cali_due_date').val('');
        jQuery('#commonInstrumentForm #ins_last_cali_date').datepicker('disable');
        jQuery('#commonInstrumentForm #ins_next_cali_due_date').datepicker('disable');

        jQuery('label[for="ins_cali_freq"], label[for="ins_last_cali_date"], label[for="ins_next_cali_due_date"]').find('.astric').addClass('hide d-none');
    }
}

// Calibration Certificate file change handler
jQuery('#calibration_certificate').on('change', function (e) {
    var form_data = new FormData();
    var id = jQuery(this).attr('id');
    var target = e.target || e.srcElement;
    var files = target.files;
    var totalfiles = files.length;
    var oldImg = jQuery('#calibration_certificate_doc').val();

    if (totalfiles > 0) {
        var notValid = 0;
        for (var index = 0; index < totalfiles; index++) {
            if (validateImage(files[index].name) == true) {
                form_data.append("docs[]", files[index]);
            } else {
                notValid = 1;
                toastr.error("Only (jpeg,jpg,png,gif,pdf) files are allowed.");
                jQuery('#calibration_certificate_doc').val('');
                jQuery('#' + id).val('');
                jQuery('#calibration_certificate_prev').attr('href', '#').addClass('hide');
                jQuery('#calibration_certificate_remove').removeClass('i-block').addClass('hide');
                e.stopImmediatePropagation();
                return false;
            }
        }

        if (notValid == 0) {
            jQuery('#InstrumentModal').find('#submitbtn').prop('disabled', true);
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
                    jQuery('#InstrumentModal').find('#submitbtn').prop('disabled', false);
                    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    if (data.response_code == 1) {
                        if (oldImg != "" && oldImg.includes('temp_media/')) {
                            removeMediaCertificate(oldImg);
                        }
                        $('#calibration_certificate_doc').val(data.files);
                        $('#calibration_certificate_prev').attr('href', data.files_url).removeClass('hide');
                        $('#calibration_certificate_remove').addClass('i-block').removeClass('hide');
                        console.log(data.response_message);
                    } else {
                        console.log(data.response_message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#InstrumentModal').find('#submitbtn').prop('disabled', false);
                    $('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    $('#' + id).parent().parent().find('.uneditable-input').addClass('iconfa-warning-sign upload-error');
                    if (jqXHR.status == 401) {
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
            let fileName = oldImg.split('/').pop();
            let fileInput = jQuery('#calibration_certificate');
            let dataTransfer = new DataTransfer();
            dataTransfer.items.add(new File([""], fileName));
            fileInput[0].files = dataTransfer.files;
            return false;
        }
        clearCertificate();
    }
});

function clearCertificate() {
    jQuery('#InstrumentModal').find('#calibration_certificate_doc').val('');
    jQuery('#InstrumentModal').find('#calibration_certificate').val('');
    jQuery('#InstrumentModal').find('#calibration_certificate_prev').attr('href', '#').addClass('hide');
    jQuery('#InstrumentModal').find('#calibration_certificate_remove').removeClass('i-block').addClass('hide');
}

function removeFile(e) {
    e.stopImmediatePropagation();
    toastDetailDelete('Are You Sure, You Want To Delete ?', () => {
        var target = e.target;
        var id = target.getAttribute("data-remove");
        var oldImg = jQuery('#' + id + '_doc').val();

        if (oldImg != "" && oldImg.includes('temp_media/')) {
            removeMediaCertificate(oldImg);
        }

        jQuery('#' + id + '_doc').val('');
        jQuery('#' + id).val('');
        jQuery('#' + id + '_prev').attr('href', '#').addClass('hide');
        jQuery('#' + id + '_remove').removeClass('i-block').addClass('hide');
    });
}

function removeMediaCertificate(docName) {
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
            console.log(data.response_message);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.status == 401) {
                toastr.error(jqXHR.statusText);
            } else {
                toastr.error('Something went wrong!');
                console.log(JSON.parse(jqXHR.responseText));
            }
        }
    });
}

function validateImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.pdf)$/i;
    if (!allowedExtensions.exec(filePath)) {
        return false;
    }
    return true;
}
