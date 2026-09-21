service_po_details_data = [];
var formId = jQuery('#commonServicePOForm').find('input[name="id"]').val();

// Edit purchase order row click
jQuery('#dyntable tbody').on('click', '.edit-service_po', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#ServicePOModal').find('#id').val(data["ser_po_id"]);
    if (data && data["ser_po_id"]) {
        fetchAndFillServicePO(data["ser_po_id"]);
    }
});

// // Function to fetch and fill purchase order data
function fetchAndFillServicePO(id) {
    if (!id) return;
    jQuery('#ServicePOModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-service_po",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.ser_po_data != null) {
                jQuery('#ServicePOModal').find('#id').val(data.ser_po_data.ser_po_id != "" ? data.ser_po_data.ser_po_id : "");
                let url = checkFileRoute + "?id=" + data.ser_po_data.ser_po_id + "&name=" + data.ser_po_data.pdf_name + "&type=service_po";
                jQuery('#preview_btn').attr('href', url).show();
                var gst = data.ser_po_data.gst_type_fix_id;

                jQuery('#ServicePOModal').find('input[name*="gst_type_fix_id"][value="' + gst + '"]').prop('checked', true);
                var thisForm = jQuery("#commonServicePOForm");

                if (gst == 3) {
                    thisForm.find(".igst-field").val('')
                    thisForm.find(".igst-field:not(.disb)").prop('disabled', true);
                    thisForm.find(".sgst-field").val('')
                    thisForm.find(".sgst-field:not(.disb)").prop('disabled', true);
                    thisForm.find(".cgst-field").val('')
                    thisForm.find(".cgst-field:not(.disb)").prop('disabled', true);
                } else if (gst == 1) {
                    thisForm.find(".igst-field").val('')
                    thisForm.find(".sgst-field:not(.disb)").val(parseFloat(data.ser_po_data.sgst_percentage).toFixed(2))
                    thisForm.find(".cgst-field:not(.disb)").val(parseFloat(data.ser_po_data.cgst_percentage).toFixed(2))
                    thisForm.find(".igst-field:not(.disb)").prop('disabled', true);
                    thisForm.find(".sgst-field:not(.disb)").prop('disabled', false);
                    thisForm.find(".cgst-field:not(.disb)").prop('disabled', false);
                } else {
                    thisForm.find(".sgst-field").val('')
                    thisForm.find(".cgst-field").val('')
                    thisForm.find(".igst-field:not(.disb)").val(parseFloat(data.ser_po_data.igst_percentage).toFixed(2))
                    thisForm.find(".igst-field:not(.disb)").prop('disabled', false);
                    thisForm.find(".sgst-field:not(.disb)").prop('disabled', true);
                    thisForm.find(".cgst-field:not(.disb)").prop('disabled', true);
                }
                if (data.ser_po_data.in_use == true) {
                    jQuery('#ServicePOModal').find('#for_location_id').addClass('skip-tab');
                    setSelect2Readonly('#ServicePOModal #for_location_id', true);

                } else {
                    jQuery('#ServicePOModal').find('#for_location_id').removeClass('skip-tab');
                    setSelect2Readonly('#ServicePOModal #for_location_id', false);

                }

                jQuery('#ServicePOModal').find('#ser_po_number').val(data.ser_po_data.ser_po_number != "" ? data.ser_po_data.ser_po_number : "");
                jQuery('#ServicePOModal').find('#ser_po_date').val(data.ser_po_data.ser_po_date != "" ? data.ser_po_data.ser_po_date : "");

                jQuery('#ServicePOModal').find('#old_po_date').val(data.ser_po_data.ser_po_date != "" ? data.ser_po_data.ser_po_date : "");

                jQuery('#ServicePOModal').find('#ser_po_sequence').val(data.ser_po_data.ser_po_sequence != "" ? data.ser_po_data.ser_po_sequence : "");

                jQuery('#ServicePOModal').find('#supplier_id').val(data.ser_po_data.supplier_id).trigger('change.select2');

                jQuery('#ServicePOModal').find('#payment_terms').val(data.ser_po_data.payment_terms != "" ? data.ser_po_data.payment_terms : "");

                getSupplierKindAttn().done(function () {
                    jQuery('#ServicePOModal')
                        .find('#kind_attn_id')
                        .val(zeroToEmpty(data.ser_po_data.kind_attn_id))
                        .trigger('change.select2');
                });



                jQuery('#ServicePOModal').find('#ref_no_date').val(data.ser_po_data.ref_no_date != "" ? data.ser_po_data.ref_no_date : "");

                jQuery('#ServicePOModal').find('#purpose').val(data.ser_po_data.purpose != "" ? data.ser_po_data.purpose : "");

                jQuery('#ServicePOModal').find('#bill_to_id').val(data.ser_po_data.bill_to_id != "" ? data.ser_po_data.bill_to_id : "").trigger('change.select2');

                jQuery('#ServicePOModal').find('#for_location_id').val(data.ser_po_data.for_location_id != "" ? data.ser_po_data.for_location_id : "").trigger('change.select2');

                jQuery('#ServicePOModal').find('#sp_note').val(data.ser_po_data.sp_note != "" ? data.ser_po_data.sp_note : "");

                jQuery('#ServicePOModal').find('#terms_and_conditions').val(data.ser_po_data.terms_and_conditions != "" ? data.ser_po_data.terms_and_conditions : "");

                jQuery('#ServicePOModal').find('#prepared_by_user_id').val(data.ser_po_data.prepared_by_user_id != "" ? data.ser_po_data.prepared_by_user_id : "");


                if (data.service_po_details_data != "" && data.service_po_details_data.length > 0) {
                    service_po_details_data.push(...data.service_po_details_data);
                    fillServicePODetailTable();
                }

                jQuery('#ServicePOModal').find('#submitbtn').prop('disabled', false);

                const form = document.getElementById("commonServicePOForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#ServicePOModal').find('#add_new').show();
                jQuery('#ServicePOModal').find('#preview_btn').show();
                if (data.ser_po_data.in_use == true) {
                    jQuery('#ServicePOModal').find('#ser_po_sequence').prop('readonly', true);
                    let nextInput = jQuery('#ServicePOModal').find('#ser_po_date');
                    if (nextInput.hasClass('trans-date-picker') || nextInput.hasClass('date-picker')) {
                        nextInput.one('focus', function () {
                            setTimeout(() => {
                                jQuery(this).datepicker('hide');
                            }, 50);
                        });
                    }
                    nextInput.focus();
                } else {
                    jQuery('#ServicePOModal').find('#ser_po_sequence').prop('readonly', false);
                    jQuery('#ServicePOModal').find('#ser_po_sequence').focus();
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



jQuery('#ServicePOModal').on('show.bs.modal', function () {
    var formId = jQuery('#commonServicePOForm').find('input[name="id"]').val();
    var hasAccess = jQuery('#commonServicePOForm').find('#has_access').val();
    jQuery('#ServicePOModal').find('#ser_po_sequence').prop('readonly', false);
    jQuery('#commonServicePOForm').find('#prepared_by_user_id').val(loginUserId).trigger("change.select2");
    if (formId == "" || formId == undefined) {
        getLatestServicePONo();
        manageGstType();
        jQuery('#ServicePOModal').find('#add_new').hide();
        jQuery('#ServicePOModal').find('#preview_btn').hide();
    } else {
        jQuery('#ServicePOModal').find('#add_new').show();
        jQuery('#ServicePOModal').find('#preview_btn').show();
    }
});

jQuery('#ServicePODetailsModal').on('show.bs.modal', function () {
    let thisModal = jQuery('#ServicePODetailsModal');
    var details_id = thisModal.find("#ser_pod_id").val();
    var form_type = thisModal.find("#form_type").val();
    let in_use = jQuery('#ServicePODetailsForm').data('in_use') === true || jQuery('#ServicePODetailsForm').data('in_use') === 'true';
    if (form_type == "edit") {
        if (details_id != 0) {
            jQuery('#item_id').addClass('skip-tab');
            setSelect2Readonly('#item_id', true);
            thisModal.find('#po_qty').attr('readonly', true).prop('tabindex', -1);
            jQuery('#sr_table_pk_id').addClass('skip-tab');
            setSelect2Readonly('#sr_table_pk_id', true);
            if (in_use) {
                jQuery('#for_calibration').prop('disabled', true);
            }
        } else {
            jQuery('#item_id').removeClass('skip-tab');
            setSelect2Readonly('#item_id', false);
            jQuery('#sr_table_pk_id').removeClass('skip-tab');
            setSelect2Readonly('#sr_table_pk_id', false);
        }
    } else {
        jQuery('#item_id').removeClass('skip-tab');
        setSelect2Readonly('#item_id', false);
        thisModal.find('#po_qty').attr('readonly', false).removeAttr('tabindex', -1);
        jQuery('#sr_table_pk_id').removeClass('skip-tab');
        setSelect2Readonly('#sr_table_pk_id', false);
        jQuery('#for_calibration').prop('disabled', true).prop('checked', false);
        jQuery('#ServicePODetailsForm').data('in_use', false);
        setTimeout(function () {
            let sel = jQuery('#item_id')
                .next('.select2-container')
                .find('.select2-selection');
            sel.attr('tabindex', 0).focus();
        }, 20);
    }
    var mode = jQuery("#ServicePODetailsModal #form_type").val();
    var ser_pod_id = jQuery("#ServicePODetailsModal #ser_pod_id").val();
    if (mode == "add" && ser_pod_id == "0") {
        jQuery('#ServicePODetailsModal #po_qty').removeAttr('min');
    }
    jQuery('#ServicePODetailsForm #sr_table_pk_id').empty().append('<option value="">Select Sr. No.</option>');
});

jQuery('#ServicePODetailsModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#ServicePODetailsModal');
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    thisModal.find("#ser_pod_id").val(0);
    jQuery('#SupplierDetailTable tbody').empty()

    jQuery(this).find('#item_id option.temp-item').remove();
    jQuery('#ServicePODetailsForm').trigger("reset");
    jQuery('#for_calibration').prop('checked', false).prop('disabled', true);
    jQuery('#ServicePODetailsForm').data('in_use', false);
    this.dataset.customHideFocus = 'true';
    const input = jQuery("#commonServicePOForm").find("input[name='gst_type_fix_id']");
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }

    jQuery('#ServicePODetailsForm #po_qty').removeAttr('min');
});

jQuery('#ServicePOModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#ServicePOModal');
    thisModal.find("#id").val("");
    thisModal.find('#ser_po_sequence').prop('readonly', false);
    var thisForm = jQuery('#ServicePODetailsModal');
    thisForm.find("#ser_pod_id").val(0);
    service_po_details_data = [];
    jQuery('#ServicePOModal').find('#add_new').hide();
    jQuery('#ServicePOModal').find('#preview_btn').hide();

    if (formId == "" || formId == undefined) {
        setSelect2Readonly('#supplier_id', false);
        setSelect2Readonly('#kind_attn_id', false);
    }
    jQuery('#ServicePODetailTable tbody').empty();
    jQuery('#commonServicePOForm').trigger("reset");
    jQuery('#commonServicePOForm .toggleModalBtn').prop('disabled', true);
    jQuery('#commonServicePOForm #totalAmount').text("0.00");
    jQuery('#ServicePOModal').find('#for_location_id').removeClass('skip-tab');
    setSelect2Readonly('#ServicePOModal #for_location_id', false);
});

// Reset button click for purchase order modal
jQuery('#resetbtn').on('click', function () {
    service_po_details_data = [];
    selectedRows = {};

    jQuery('#ServicePODetailTable tbody').empty();

    var formId = jQuery('#ServicePOModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonServicePOForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        jQuery('#commonServicePOForm').find('#prepared_by_user_id').val(loginUserId).trigger("change.select2");
        jQuery('#ser_po_sequence').prop('readonly', false).focus();
        jQuery('#ServicePOModal').find("#supplier_id").val('').trigger('change');
        jQuery('#ServicePOModal').find("#kind_attn_id").val('').trigger('change.select2');
        jQuery('#ServicePOModal').find('#bill_to_id').val('').trigger('change');
        jQuery('#ServicePOModal').find('#for_location_id').val('').trigger('change');

        getLatestServicePONo();
    } else {
        fetchAndFillServicePO(formId);
    }
});

jQuery('#ServicePOModal').on('click', '#add_new', function () {
    jQuery('#ServicePOModal').find('#id').val('');
    jQuery("#ServicePODetailsForm #ser_pod_id").val(0);
    jQuery("#ServicePODetailsForm #sr_table_unique_id").val('');
    jQuery('#ServicePOModal').find('#pending_btn').prop('disabled', false);
    document.getElementById("commonServicePOForm").reset();
    const form = document.getElementById("commonServicePOForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#commonServicePOForm').find('#prepared_by_user_id').val(loginUserId).trigger("change.select2");
    jQuery('#ser_po_sequence').prop('readonly', false).focus();
    getLatestServicePONo();
    jQuery('#ServicePODetailTable tbody').empty();
    jQuery('#ServicePODetailTable tbody').append(`
        <tr>
            <td colspan="12" id="noDetails">
                No Service PO Details Added
            </td>
        </tr>
    `);
    service_po_details_data = [];
    jQuery("#totalAmount").text("0.00");
    jQuery('#ServicePOModal').find("#supplier_id").val('').trigger('change');
    jQuery('#ServicePOModal').find('#bill_to_id').val('').trigger('change');
    jQuery('#ServicePOModal').find('#for_location_id').val('').trigger('change');
    jQuery('#ServicePOModal').find('#add_new').hide();
    jQuery('#ServicePOModal').find('#preview_btn').hide();
});



/* Supplier Contact Person */
jQuery('#ServicePOModal').on('change', '#supplier_id', function () {
    getSupplierKindAttn();
});

function getSupplierKindAttn() {

    let selectedOption = jQuery('#supplier_id').find('option:selected');
    let supplier_id = selectedOption.val();
    let $modal = jQuery('#ServicePOModal');
    let $attnSelect = $modal.find('#kind_attn_id');
    $attnSelect.empty().append('<option value="">Select Kind Attn.</option>');
    if (supplier_id) {

        return jQuery.ajax({
            url: 'get-service_po_supplier_kind_attn',
            type: 'POST',
            headers: headerOpt,
            data: {
                'supplier_id': supplier_id
            },

            success: function (data) {
                if (data.response_code == 1) {
                    let Kind_attn = data.Kind_attn;
                    Kind_attn.forEach(function (item) {
                        $attnSelect.append(
                            `<option value="${item.supd_details_id}">${item.contact_person}</option>`
                        );
                    });

                    // if (data.LnrData) {
                    //     jQuery("#terms_and_conditions").val(data.LnrData.po_terms_and_conditions);
                    // } else {
                    //     jQuery("#terms_and_conditions").val('');
                    // }
                }
            }
        });
    } else {
        $modal.find('#kind_attn_id').val('').trigger('change.select2');
        $modal.find("#terms_and_conditions").val('');

    }
}

/* GST Fill As Per Supplier + Bill To */

jQuery('#supplier_id , #bill_to_id').on('change.select2 change', function () {
    $sup_state = jQuery("#supplier_id").find('option:selected').data('state-id');
    $bill_state = jQuery("#bill_to_id").find('option:selected').data('state_id');

    if ($sup_state != undefined && $bill_state != undefined && $sup_state == $bill_state) {
        jQuery('input[name="gst_type_fix_id"][value="1"]').prop('checked', true).trigger('change');
    } else if ($sup_state == undefined && $bill_state == undefined) {
        jQuery('input[name="gst_type_fix_id"][value="3"]').prop('checked', true).trigger('change');
    } else {
        jQuery('input[name="gst_type_fix_id"][value="2"]').prop('checked', true).trigger('change');
    }
});

/* Fill Item Group And Main Group */
jQuery('#ServicePODetailsForm #item_id').on('change', function () {
    let selected = jQuery(this).find('option:selected');
    let main_group = selected.data('main_group');
    let item_group = selected.data('item_group');
    let unit = selected.data('unit');
    if (selected.val() != "") {
        jQuery('#item_group').val(item_group);
        jQuery('#main_group').val(main_group);
        jQuery('#po_qty_unit').text(unit).addClass('ms-1');
        fillPodGRNDetailsTable();

        let allowedMainGroups = ['Equipment - MPT', 'Equipment - UT', 'Equipment – MPT', 'Equipment – UT', 'Instrument'];
        if (allowedMainGroups.includes(main_group)) {
            jQuery('#for_calibration').prop('disabled', true).prop('checked', false);
        } else {
            jQuery('#for_calibration').prop('disabled', true).prop('checked', false);
        }

        if (main_group == 'Industrial X-Ray Films' || main_group == 'General') {

            jQuery('#ServicePODetailsForm #sr_table_pk_id').addClass('skip-tab');
            setSelect2Readonly("#ServicePODetailsForm #sr_table_pk_id", true);
            jQuery('#po_qty').attr('readonly', false).removeAttr('tabindex', -1);

        } else {
            jQuery('#ServicePODetailsForm #sr_table_pk_id').removeClass('skip-tab');
            setSelect2Readonly("#ServicePODetailsForm #sr_table_pk_id", false);
            jQuery('#po_qty').val('').attr('readonly', true).prop('tabindex', -1);
            if (!jQuery('#ServicePODetailsForm #item_id').data('skip-get-sr-no')) {
                getSrNo(main_group, item_id);
            }
        }

        if (main_group == 'Industrial X-Ray Films') {
            jQuery('#po_qty').removeClass('isNumberKey').addClass('isNumberKeyNotDot');
            jQuery('#po_qty').attr('onblur', 'formatPoints(this, 0)');
            if (jQuery('#po_qty').val() !== '') {
                jQuery('#po_qty').val(parseInt(jQuery('#po_qty').val()) || '');
            }
        } else {
            jQuery('#po_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
            jQuery('#po_qty').attr('onblur', 'formatPoints(this, 3)');
        }

    } else {
        jQuery('#item_group').val('');
        jQuery('#main_group').val('');
        jQuery('#po_qty').val('');
        jQuery('#po_qty_unit').text('').removeClass('ms-1');
        jQuery('#for_calibration').prop('checked', false).prop('disabled', true);
        jQuery('#ServicePODetailsForm #sr_table_pk_id').empty().append('<option value="">Select Sr. No.</option>');
        jQuery('#ServicePODetailsForm #sr_table_pk_id').addClass('skip-tab');
        setSelect2Readonly("#ServicePODetailsForm #sr_table_pk_id", true);
        jQuery('#ServicePODetailsForm #sr_table_pk_id').prop('readonly', true);
        jQuery('#ServicePODetailsForm #sr_table_pk_id').prop('tabindex', -1);
        jQuery('#po_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        jQuery('#po_qty').attr('onblur', 'formatPoints(this, 3)');
    }
});

function getSrNo(main_group, item_id, selected_sr = '') {
    if (item_id instanceof HTMLSelectElement) {
        item_id = item_id.value;
    }

    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');

    return jQuery.ajax({
        url: "get-sr_no?main_group=" + main_group + "&item_id=" + item_id,
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            let options = `<option value="">Select Sr. No.</option>`;
            if (data.response_code == 1) {
                if (data.sr_no_data.length > 0) {
                    for (let idx in data.sr_no_data) {
                        options += `<option data-sr_table_unique_id="${data.sr_no_data[idx].sr_table_unique_id}" data-calibration_required="${data.sr_no_data[idx].calibration_required}" value="${data.sr_no_data[idx].sr_table_pk_id}">${data.sr_no_data[idx].name_for_display}</option>`;
                    }
                }
                let $dropdown = jQuery('#ServicePODetailsForm #sr_table_pk_id');
                $dropdown.empty().append(options);

                // Set value AFTER options loaded
                if (selected_sr) {
                    $dropdown.val(selected_sr);
                }
                $dropdown.trigger('change.select2');
                let selectedOption = $dropdown.find('option:selected');
                jQuery('#sr_table_unique_id').val(selectedOption.data('sr_table_unique_id') || '');
            } else {
                jQuery('#ServicePODetailsForm #sr_table_pk_id').empty().append(options).trigger('change.select2');
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('Field To Get Sr. No.!')
        },
        complete: function () {
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        }
    });
}


function removeServicePODetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        let item = service_po_details_data[formIndx];
        console.log(item.ser_pod_id);

        if (item.ser_pod_id && item.ser_pod_id != 0) {
            item.mode = "Delete";
        } else {
            service_po_details_data.splice(formIndx, 1);
        }
        removeFormObj(formIndx);
        // jQuery(th).closest("tr").remove();

    });
}

function removeFormObj(formIndx) {
    // delete service_po_details_data[formIndx];
    // service_po_details_data = service_po_details_data.filter(element => element != null);

    jQuery('#ServicePODetailTable tbody').empty();
    calculateTotalAmount();
    fillServicePODetailTable();
}

function editServicePODetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();
    fillServicePODetailsForm(formIndx, rawIndx);
}

// purchase order details form edit
function fillServicePODetailsForm(formIndx, rawIndx) {
    let thisForm = jQuery('#ServicePODetailsModal');

    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    thisForm.find('#item_id option.temp-item').remove();
    thisForm.find('#sr_table_pk_id option.temp-sr_table_pk_id').remove();
    var frmData = service_po_details_data[formIndx];
    jQuery('#ServicePODetailsForm').data('in_use', frmData.in_use);
    let itemId = frmData.item_id;
    let itemText = frmData.item_name;
    let sr_table_pk_id = frmData.sr_table_pk_id;
    let name_for_display = frmData.name_for_display;

    // check if option exists
    if (thisForm.find("#item_id option[value='" + itemId + "']").length === 0) {
        thisForm.find("#item_id").append(
            `<option  class="temp-item" value="${itemId}" data-main_group="${frmData.main_group}" data-item_group="${frmData.item_group}" selected>${itemText}</option>`
        );
    }
    if (frmData.in_use === true) {
        let usedQty = parseFloat(frmData.used_qty) || 0;
        thisForm.find("#po_qty").attr('min', usedQty.toFixed(3));

    } else {
        thisForm.find("#po_qty").attr('min', 0);
    }

    thisForm.find("#ser_pod_id").val(frmData.ser_pod_id);
    jQuery('#ServicePODetailsForm #item_id').data('skip-get-sr-no', true);
    thisForm.find("#item_id").val(zeroToEmpty(frmData.item_id != "" ? frmData.item_id : "")).trigger('change');
    jQuery('#ServicePODetailsForm #item_id').data('skip-get-sr-no', false);
    if (frmData.ser_pod_id && frmData.ser_pod_id != 0) {
        setSelect2Readonly('#item_id', true);
    } else {
        setSelect2Readonly('#item_id', false);
    }
    var decPlaces = (frmData.main_group == 'Industrial X-Ray Films') ? 0 : 3;
    thisForm.find("#po_qty").val(frmData.po_qty ? parseFloat(frmData.po_qty).toFixed(decPlaces) : parseFloat(0).toFixed(decPlaces)).change();
    if (frmData.main_group == 'Industrial X-Ray Films') {
        thisForm.find('#po_qty').removeClass('isNumberKey').addClass('isNumberKeyNotDot');
        thisForm.find('#po_qty').attr('onblur', 'formatPoints(this, 0)');
    } else {
        thisForm.find('#po_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        thisForm.find('#po_qty').attr('onblur', 'formatPoints(this, 3)');
    }
    let isSerialItem = !['Industrial X-Ray Films', 'General'].includes(frmData.main_group);
    if (isSerialItem) {
        thisForm.find('#po_qty').attr('readonly', true).prop('tabindex', -1);
    } else {
        if (frmData.ser_pod_id && frmData.ser_pod_id != 0) {
            thisForm.find('#po_qty').attr('readonly', true).prop('tabindex', -1);
        } else {
            thisForm.find('#po_qty').attr('readonly', false).removeAttr('tabindex', -1);
        }
    }
    thisForm.find("#rate_unit").val(frmData.rate_unit ? parseFloat(frmData.rate_unit).toFixed(2) : parseFloat(0).toFixed(2)).change();
    thisForm.find("#del_date").val(frmData.del_date ? frmData.del_date : "");
    thisForm.find("#remark").val(frmData.remark ? frmData.remark : "");

    let allowedMainGroups = ['Equipment - MPT', 'Equipment - UT', 'Equipment – MPT', 'Equipment – UT', 'Instrument'];
    let isCaliAllowed = false;
    if (allowedMainGroups.includes(frmData.main_group)) {
        if (frmData.main_group === 'Instrument') {
            if (frmData.calibration_required === 'Yes') {
                isCaliAllowed = true;
            }
        } else {
            isCaliAllowed = true;
        }
    }

    if (frmData.in_use === true || frmData.in_use === 'true') {
        jQuery('#for_calibration').prop('disabled', true);
    } else if (isCaliAllowed) {
        jQuery('#for_calibration').prop('disabled', false);
    } else {
        jQuery('#for_calibration').prop('disabled', true);
    }
    if (frmData.for_calibration === 'Yes') {
        jQuery('#for_calibration').prop('checked', true);
    } else {
        jQuery('#for_calibration').prop('checked', false);
    }


    getSrNo(frmData.main_group, frmData.item_id, frmData.sr_table_pk_id).done(function () {
        if (thisForm.find("#sr_table_pk_id option[value='" + sr_table_pk_id + "']").length === 0 && name_for_display != undefined) {
            thisForm.find("#sr_table_pk_id").append(
                `<option  class="temp-sr_table_pk_id" value="${sr_table_pk_id}" data-sr_table_unique_id="${frmData.sr_table_unique_id}" data-calibration_required="${frmData.calibration_required || 'No'}" selected>${name_for_display}</option>`
            );
        }
        jQuery("#ServicePODetailsForm #sr_table_pk_id").val(frmData.sr_table_pk_id ? frmData.sr_table_pk_id : "").trigger('change.select2');
        thisForm.find("#sr_table_unique_id").val(frmData.sr_table_unique_id ? frmData.sr_table_unique_id : "");

        if (frmData.for_calibration === 'Yes') {
            jQuery('#for_calibration').prop('checked', true);
        } else {
            jQuery('#for_calibration').prop('checked', false);
        }

        if (frmData.ser_pod_id && frmData.ser_pod_id != 0) {
            setSelect2Readonly("#ServicePODetailsForm #sr_table_pk_id", true);
        } else {
            if (frmData.main_group == 'Industrial X-Ray Films' || frmData.main_group == 'General') {
                setSelect2Readonly("#ServicePODetailsForm #sr_table_pk_id", true);
            } else {
                setSelect2Readonly("#ServicePODetailsForm #sr_table_pk_id", false);
            }
        }
    });

    if (frmData.in_use == true) {
        thisForm.find("#po_qty").attr('min', parseFloat(frmData.used_qty).toFixed(3));
    }
    thisForm.modal('show');
}


// edit time fill table start
function fillServicePODetailTable() {
    let thisModal = jQuery('#ServicePODetailsModal');
    if (service_po_details_data.length > 0) {
        var tblHtml = '';
        for (let key in service_po_details_data) {
            if (service_po_details_data[key].mode == "Delete") {
                continue;
            }
            let formIndx = service_po_details_data.indexOf(service_po_details_data[key]);
            var item_id = service_po_details_data[key].item_id ? service_po_details_data[key].item_id : '';
            var item_name = item_id != '' ? thisModal.find('#item_id option[value="' + item_id + '"]').text() || service_po_details_data[key].item_name : '';
            var item_group = service_po_details_data[key].item_group != "" ? service_po_details_data[key].item_group : '';
            var main_group = service_po_details_data[key].main_group != "" ? service_po_details_data[key].main_group : '';

            var sr_table_pk_id = service_po_details_data[key].sr_table_pk_id ? service_po_details_data[key].sr_table_pk_id : '';

            var name_for_display = sr_table_pk_id != "" ? service_po_details_data[key].name_for_display ? service_po_details_data[key].name_for_display : thisModal.find('#sr_table_pk_id option[value="' + sr_table_pk_id + '"]').text().trim() : "";

            var po_qty = service_po_details_data[key].po_qty ? parseFloat(service_po_details_data[key].po_qty).toFixed(3) : parseFloat(0).toFixed(3);
            var rate_unit = service_po_details_data[key].rate_unit ? parseFloat(service_po_details_data[key].rate_unit).toFixed(2) : parseFloat(0).toFixed(2);
            var amount = service_po_details_data[key].amount ? parseFloat(service_po_details_data[key].amount).toFixed(2) : parseFloat(0).toFixed(2);
            var del_date = service_po_details_data[key].del_date != "" ? service_po_details_data[key].del_date : "";

            var remark = service_po_details_data[key].remark ? service_po_details_data[key].remark : "";
            var in_use = service_po_details_data[key].in_use == true ? true : false;
            var unit = service_po_details_data[key].unit != "" ? service_po_details_data[key].unit : '';


            if (jQuery('#ServicePODetailTable tbody').find('#noDetails').length > 0) {
                jQuery('#ServicePODetailTable tbody').empty();
            }
            tblHtml += `<tr>`;
            tblHtml += `<td>`;
            tblHtml += in_use == true ? DetailsActionDropdown('editServicePODetails') : DetailsActionDropdown('editServicePODetails', 'removeServicePODetails');
            tblHtml += ` <input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;
            tblHtml += `<td>${item_name}</td>`;
            tblHtml += `<td>${item_group}</td>`;
            tblHtml += `<td>${main_group}</td>`;
            tblHtml += `<td>${name_for_display}</td>`;
            tblHtml += `<td>${po_qty ?? ''}</td>`;
            tblHtml += `<td>${unit ?? ''}</td>`;
            tblHtml += `<td>${rate_unit ?? ''}</td>`;
            tblHtml += `<td class="amount">${amount ?? ''}</td>`;
            tblHtml += `<td>${del_date ?? ''}</td>`;
            tblHtml += `<td>${remark}</td>`;
            tblHtml += `</tr>`;

        }
        jQuery('#ServicePODetailTable tbody').empty().append(tblHtml);
    }
    calculateTotalAmount();
}

jQuery('#ServicePODetailsModal').on('change', '#po_qty, #rate_unit', function () {
    let qty = parseFloat(jQuery('#po_qty').val()) || 0;
    let rate = parseFloat(jQuery('#rate_unit').val()) || 0;
    let amount = qty * rate;

    // Format to 3 decimal places (optional)
    amount = amount.toFixed(2);
    jQuery('#amount').val(amount);

});

function calculateTotalAmount() {
    let total = 0;

    jQuery('#ServicePODetailTable tbody tr').each(function () {
        let amount = jQuery(this).find('td.amount').text();

        amount = parseFloat(amount) || 0;
        total += amount;
    });

    if (total != 0) {
        jQuery('#commonServicePOForm #totalAmount').text(total.toFixed(2));
        jQuery('#basic_amount').val((total).toFixed(2));
        jQuery('#net_amount').val((total).toFixed(2));
    } else {
        jQuery('#commonServicePOForm #totalAmount').text((0).toFixed(2));
        jQuery('#net_amount').val((0).toFixed(2));
    }
    calcGstAmount();
}

/* Service PO Details Form Submit Handler */
$('#ServicePODetailsForm').on('submit', function (e) {

    e.preventDefault();
    let form = this;

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    var data = new FormData(document.getElementById('ServicePODetailsForm'));
    var formValue = Object.fromEntries(data.entries());
    formValue.for_calibration = jQuery('#for_calibration').is(':checked') ? 'Yes' : 'No';
    let selectedSrOption = jQuery('#sr_table_pk_id option:selected');
    formValue.calibration_required = selectedSrOption.attr('data-calibration_required') || selectedSrOption.data('calibration_required') || 'No';
    var thisModal = jQuery('#ServicePODetailsModal');

    var main_group = formValue.main_group ? formValue.main_group : '';
    var sr_table_pk_id = formValue.sr_table_pk_id ? formValue.sr_table_pk_id : '';

    if (!["Industrial X-Ray Films", "General"].includes(main_group)) {
        if (sr_table_pk_id == "") {
            toastr.error('Select Sr. No.');
            return;
        }

    }

    var po_qty = formValue.po_qty ? parseFloat(formValue.po_qty) : 0;

    if (po_qty < 0.001) {
        toastr.error('Enter PO Qty. greater than 0.001.');
        return;
    }

    if (formValue.item_id.trim()) {

        function parseDate(dateStr) {
            if (!dateStr) return null;
            var parts = dateStr.split("/");
            return new Date(parts[2], parts[1] - 1, parts[0]);
        }

        var noDuplicate = true;
        var sr_table_pk_id = formValue.sr_table_pk_id ? formValue.sr_table_pk_id : null;
        var item_id = formValue.item_id ? formValue.item_id : null;

        var currentIndex = formValue.form_type === "edit" ? formValue.form_index : null;

        // var validData = service_po_details_data.filter(item => item.mode !== "Delete");

        jQuery.each(service_po_details_data, function (index, item) {
            if (item.mode === "Delete") return true;
            if (item.sr_table_pk_id == sr_table_pk_id && item.item_id == item_id) {
                if (currentIndex === null || index != currentIndex) {
                    noDuplicate = false;
                    return false;
                }
            }
        });

        if (!noDuplicate) {
            toastr.error('Duplicate Sr. No. Found.');
            return;
        }

        if (noDuplicate) {
            var item_id = formValue.item_id ? formValue.item_id : '';
            var item_name = formValue.item_name ? formValue.item_name : thisModal.find('#item_id option[value="' + item_id + '"]').text();
            var item_group = formValue.item_group != "" ? formValue.item_group : '';
            var main_group = formValue.main_group != "" ? formValue.main_group : '';
            var sr_table_pk_id = formValue.sr_table_pk_id ? formValue.sr_table_pk_id : '';
            var name_for_display = formValue.name_for_display ? formValue.name_for_display : thisModal.find('#sr_table_pk_id option[value="' + sr_table_pk_id + '"]').text();
            var po_qty = formValue.po_qty ? parseFloat(formValue.po_qty).toFixed(3) : parseFloat(0).toFixed(3);
            var rate_unit = formValue.rate_unit ? parseFloat(formValue.rate_unit).toFixed(2) : parseFloat(0).toFixed(2);
            var amount = formValue.amount ? parseFloat(formValue.amount).toFixed(2) : parseFloat(0).toFixed(2);
            var del_date = formValue.del_date != "" ? formValue.del_date : "";
            var remark = formValue.remark ? formValue.remark : "";
            formValue.name_for_display = name_for_display;
            var unit = thisModal.find('#po_qty_unit').text();
            formValue.unit = unit;
            if (item_id != "") {
                if (formValue.form_type == "edit") {
                    formValue.item_name = item_name;
                    formValue.mode = formValue.ser_pod_id == 0 ? "Insert" : "Update";
                    service_po_details_data[formValue.form_index] = {
                        ...service_po_details_data[formValue.form_index],
                        ...formValue
                    };
                    let tblHtml = ``;
                    tblHtml += `<td>`;
                    var in_use = service_po_details_data[formValue.form_index].in_use == true ? true : false;
                    tblHtml += in_use == true ? DetailsActionDropdown('editServicePODetails') : DetailsActionDropdown('editServicePODetails', 'removeServicePODetails');
                    // tblHtml += DetailsActionDropdown('editServicePODetails', 'removeServicePODetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                    </td>`;
                    tblHtml += `<td>${item_name}</td>`;
                    tblHtml += `<td>${item_group}</td>`;
                    tblHtml += `<td>${main_group}</td>`;
                    tblHtml += `<td>${name_for_display}</td>`;
                    tblHtml += `<td>${po_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${rate_unit}</td>`;
                    tblHtml += `<td class="amount">${amount}</td>`;
                    tblHtml += `<td>${del_date}</td>`;
                    tblHtml += `<td>${remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#ServicePODetailTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
                    toastSuccess("Record Updated.");
                } else {
                    formValue.mode = "Insert";
                    formValue.ser_pod_id = 0;
                    service_po_details_data.push(formValue)

                    let formIndx = service_po_details_data.indexOf(formValue);
                    if (jQuery('#ServicePODetailTable tbody').find('#noDetails').length > 0) {
                        jQuery('#ServicePODetailTable tbody').empty();
                    }

                    let tblHtml = `<tr>`;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editServicePODetails', 'removeServicePODetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
                    </td>`;
                    tblHtml += `<td>${item_name}</td>`;
                    tblHtml += `<td>${item_group}</td>`;
                    tblHtml += `<td>${main_group}</td>`;
                    tblHtml += `<td>${name_for_display}</td>`;
                    tblHtml += `<td>${po_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${rate_unit}</td>`;
                    tblHtml += `<td class="amount">${amount}</td>`;
                    tblHtml += `<td>${del_date}</td>`;
                    tblHtml += `<td>${remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#ServicePODetailTable tbody').append(tblHtml);
                    toastSuccess("Record Inserted.");
                }
            }
            calculateTotalAmount();
            if (formValue.form_type == "edit") {
                thisModal.modal('hide');
            } else {
                let formElement = document.getElementById('ServicePODetailsForm');
                formElement.reset();
                jQuery('#for_calibration').prop('checked', false);
                jQuery('#item_id').val('').trigger('change.select2');
                jQuery('#po_qty').val('').attr('readonly', false).removeAttr('tabindex', -1);
                jQuery('#rate_unit').val('');
                jQuery('#amount').val('');
                jQuery('#del_date').val('');
                jQuery('#remark').val('');
                jQuery('#sr_table_pk_id').val('').trigger('change.select2');
                setSelect2Readonly('#sr_table_pk_id', true);
                setTimeout(function () {

                    let $select = jQuery('#ServicePODetailsForm #item_id');
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
        }

    } else {
        toastr.error('Select Item Name');
    }
});

/* End Service PO Details Form Submit Handler */



/* Main Service PO Form Submit */
$('#commonServicePOForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#ServicePOModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;

    var sgst = parseFloat(document.getElementById("sgst_percentage").value) || 0;
    var cgst = parseFloat(document.getElementById("cgst_percentage").value) || 0;
    var igst = parseFloat(document.getElementById("igst_percentage").value) || 0;

    if (sgst > 0 || cgst > 0 || igst > 0) {

        if (sgst > 100) {
            toastr.error("SGST Cannot Be More Than 100%");
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#ServicePOModal').find('#submitbtn').prop('disabled', false);
            return false;
        }

        if (cgst > 100) {
            toastr.error("CGST Cannot Be More Than 100%");
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#ServicePOModal').find('#submitbtn').prop('disabled', false);
            return false;
        }

        if (igst > 100) {
            toastr.error("IGST Cannot Be More Than 100%");
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#ServicePOModal').find('#submitbtn').prop('disabled', false);
            return false;
        }
    }

    var dateValue = document.getElementById("ser_po_date").value.trim();

    // validatePercentage
    if (!isValidDate(dateValue)) {
        toastr.error("Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ServicePOModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ServicePOModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ServicePOModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    jQuery('#ServicePOModal').find('#submitbtn').prop('disabled', true);

    var formIdnew = jQuery('#ServicePOModal').find('#commonServicePOForm').find('#id').val();
    var formUrl = formIdnew != undefined && formIdnew != "" ? "update-service_po" : "store-service_po";
    var data = new FormData(form);
    data.append('service_po_details_data', JSON.stringify(service_po_details_data ?? []));
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));

    let validData = service_po_details_data.filter(item => item.mode !== "Delete");

    if (validData.length > 0 && !jQuery.isEmptyObject(validData)) {
        let isValidDetails = true;
        let errorMsg = '';

        $.each(validData, function (index, row) {

            if ((row.sr_table_pk_id === undefined || row.sr_table_pk_id === null || row.sr_table_pk_id === '') && !['Industrial X-Ray Films', 'General'].includes(row.main_group)) {
                errorMsg = `Select SR No.`;
                isValidDetails = false;
                return false;
            }

            if (row.po_qty === undefined || row.po_qty === null || row.po_qty === '' || parseFloat(row.po_qty) <= 0) {
                errorMsg = `Enter PO Qty.`;
                isValidDetails = false;
                return false;
            }

            if (row.rate_unit === undefined || row.rate_unit === null || row.rate_unit === '' || parseFloat(row.rate_unit) <= 0) {
                errorMsg = `Enter Rate / Unit.`;
                isValidDetails = false;
                return false;
            }

        });

        if (!isValidDetails) {
            toastr.error(errorMsg);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');

            jQuery('#ServicePOModal')
                .find('#submitbtn')
                .prop('disabled', false);

            return;
        }
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
                        if (data.url != "") {
                            toastSuccessPreview(data.response_message, data.url, redirectFn);
                        } else {
                            toastSuccess(data.response_message, redirectFn);
                        }
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#ServicePOModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formIdnew == undefined || formIdnew == "") {
                        function nextFn() {
                            document.getElementById("commonServicePOForm").reset();
                            const form = document.getElementById("commonServicePOForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            jQuery('#ser_po_sequence').prop('readonly', false).focus();
                            $("#supplier_id").val('').trigger('change');
                            $("#kind_attn_id").val('').trigger('change');
                            $("#purpose").val('');
                            $("#ref_no_date").val('');
                            $("#bill_to_id").val('').trigger('change');
                            $("#for_location_id").val('').trigger('change');
                            service_po_details_data = [];
                            selectedRows = {};
                            jQuery('#ServicePODetailTable tbody').empty();
                            jQuery("#totalAmount").text("0.00");
                            getLatestServicePONo();
                            jQuery('#ServicePOModal').find('#pending_btn').prop('disabled', true);
                        }
                        if (data.url != "") {
                            toastSuccessPreview(data.response_message, data.url, nextFn);
                        } else {
                            toastSuccess(data.response_message, nextFn);
                        }
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#ServicePOModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#ServicePOModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#ServicePOModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#ServicePOModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#ServicePOModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        toastr.error('Add At Least One Service PO Detail.');

        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ServicePOModal').find('#submitbtn').prop('disabled', false);
    }
});
// Main Form Submit End


// get the latest number
function getLatestServicePONo() {
    jQuery.ajax({
        url: "get-latest_service_po_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {

                jQuery('#ser_po_number').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#ser_po_sequence').val(data.number);
                jQuery('#ser_po_date').val(currentDate);
                jQuery('#ServicePOModal').find('#for_location_id').removeClass('skip-tab');
                setSelect2Readonly('#ServicePOModal #for_location_id', false);

            } else {
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('Field To Get Latest Service PO No.!')
        }
    });
}

jQuery('#commonServicePOForm').find('#ser_po_sequence').on('change', function () {
    checkSequence();
});
// check sr no duplication number

function checkSequence() {

    let thisForm = jQuery('#commonServicePOForm');
    let val = thisForm.find('#ser_po_sequence').val();

    if (val != "") {

        if (val > 0 == false) {
            toastr.error('Enter Valid Service PO No.');
            jQuery('#ser_po_sequence').parent().parent().parent('div.control-group').addClass('error');
            jQuery('#ser_po_sequence').focus();
            jQuery('#ser_po_sequence').val('');

        } else {
            jQuery('#ser_po_sequence').addClass('file-loader');
            jQuery('#ser_po_sequence').parent().parent().parent('div.control-group').removeClass('error');

            var urL = "check-service_po_number_duplication?for=add&ser_po_sequence=" + val;

            var formId = jQuery('#commonServicePOForm').find('input[name="id"]').val();

            if (formId !== undefined) { //if form is edit
                urL = "check-service_po_number_duplication?for=edit&ser_po_sequence=" + val + "&id=" + formId;
            }

            jQuery.ajax({
                url: urL,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    jQuery('#ser_po_sequence').removeClass('file-loader');
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        jQuery('#commonServicePOForm #ser_po_sequence').val('');
                        const input = document.getElementById('ser_po_sequence'); input?.focus();
                    } else {
                        jQuery('#commonServicePOForm #ser_po_number').val(data.latest_no);
                        jQuery('#commonServicePOForm #ser_po_sequence').val(val);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#ser_po_sequence').removeClass('file-loader');
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
        jQuery('#ser_po_number').val('');
        jQuery('#ser_po_sequence').val('');
    }

}

/* Last Supplier wise Last 5 Entries */
function fillPodGRNDetailsTable() {
    var supplier_id = jQuery('#supplier_id').val();
    var item_id = jQuery('#item_id').val();
    if (supplier_id || item_id) {
        jQuery.ajax({
            url: 'get-service_po_grn_details',
            type: 'POST',
            headers: headerOpt,
            data: {
                'supplier_id': supplier_id,
                'item_id': item_id

            },
            success: function (data) {
                let tblHtml = '';
                if (data.response_code == 1 && data.grn_data.length > 0) {
                    if (data.grn_data.length > 0 && !jQuery.isEmptyObject(data.grn_data)) {
                        for (let idx in data.grn_data) {
                            tblHtml += `
                                        <tr>
                                            
                                            <td>${(data.grn_data[idx].grn_number == null) ? "" : data.grn_data[idx].grn_number}</td>
                                            <td>${(data.grn_data[idx].grn_date == null) ? "" : data.grn_data[idx].grn_date}</td>
                                            <td>${(data.grn_data[idx].supplier_name == null) ? "" : data.grn_data[idx].supplier_name}</td>
                                            <td>${(data.grn_data[idx].purpose == null) ? "" : data.grn_data[idx].purpose}</td>
                                            <td>${(data.grn_data[idx].grnd_qty == null) ? "" : data.grn_data[idx].grnd_qty}</td>
                                            <td>${(data.grn_data[idx].grnd_rate_unit == null) ? "" : data.grn_data[idx].grnd_rate_unit}</td>
                                        </tr>`;
                        }
                    } else {
                        tblHtml += `<tr class="centeralign" id="noDetails">
                                        <td colspan="6">No data Available</td>
                                    </tr>`;

                    }
                    var $table = jQuery('#SupplierDetailTable');
                    if (jQuery.fn.DataTable.isDataTable($table)) {
                        $table.DataTable().clear().destroy();
                    }
                    jQuery('#SupplierDetailTable tbody').empty().append(tblHtml);

                } else {

                    tblHtml += `<tr class="centeralign" id="noItems">
                                        <td colspan="6">No data Available</td>
                                    </tr>`;
                    var $table = jQuery('#SupplierDetailTable');
                    if (jQuery.fn.DataTable.isDataTable($table)) {
                        $table.DataTable().clear().destroy();
                    }
                    jQuery('#SupplierDetailTable tbody').empty().append(tblHtml);

                }
            }
        });
    }
}


jQuery('#ServicePODetailsForm #sr_table_pk_id').on('change', function () {
    let selectedOption = jQuery(this).find('option:selected');
    let sr_table_unique_id = selectedOption.attr('data-sr_table_unique_id') || selectedOption.data('sr_table_unique_id') || '';
    let calibration_required = selectedOption.attr('data-calibration_required') || selectedOption.data('calibration_required') || 'No';

    let ser_pod_id = jQuery('#ServicePODetailsForm #ser_pod_id').val() || '0';
    let in_use = jQuery('#ServicePODetailsForm').data('in_use') === true || jQuery('#ServicePODetailsForm').data('in_use') === 'true';

    let main_group = jQuery('#ServicePODetailsForm #main_group').val();
    let allowedMainGroups = ['Equipment - MPT', 'Equipment - UT', 'Equipment – MPT', 'Equipment – UT', 'Instrument'];

    if (sr_table_unique_id != "") {
        jQuery('#sr_table_unique_id').val(sr_table_unique_id);
        jQuery('#po_qty').val(parseFloat(1).toFixed(3)).attr('readonly', true).prop('tabindex', -1);
    } else {
        jQuery('#sr_table_unique_id').val('');
        if (main_group == 'Industrial X-Ray Films' || main_group == 'General') {
            if (ser_pod_id == '0') {
                jQuery('#po_qty').val('').attr('readonly', false).removeAttr('tabindex', -1);
            } else {
                jQuery('#po_qty').attr('readonly', true).prop('tabindex', -1);
            }
        } else {
            jQuery('#po_qty').val('').attr('readonly', true).prop('tabindex', -1);
        }
    }

    let isCaliAllowed = false;
    if (allowedMainGroups.includes(main_group)) {
        if (main_group === 'Instrument') {
            if (calibration_required === 'Yes') {
                isCaliAllowed = true;
            }
        } else {
            isCaliAllowed = true;
        }
    }

    if (in_use) {
        jQuery('#for_calibration').prop('disabled', true);
    } else {
        if (isCaliAllowed) {
            jQuery('#for_calibration').prop('disabled', false);
        } else {
            jQuery('#for_calibration').prop('checked', false).prop('disabled', true);
        }
    }
});


jQuery('input[name="gst_type_fix_id"]').on('change', function () {
    manageGstType();
});

function manageGstType() {
    var thisForm = jQuery('#commonServicePOForm');
    var gstType = thisForm.find("input[name='gst_type_fix_id']:checked").val();
    if (gstType != "") {
        if (gstType == 3) {
            thisForm.find(".igst-field").val('')
            thisForm.find(".igst-field:not(.disb)").prop('disabled', true);
            thisForm.find(".sgst-field").val('')
            thisForm.find(".sgst-field:not(.disb)").prop('disabled', true);
            thisForm.find(".cgst-field").val('')
            thisForm.find(".cgst-field:not(.disb)").prop('disabled', true);
        } else if (gstType == 1) {
            thisForm.find(".igst-field").val('')
            thisForm.find(".sgst-field:not(.disb)").val(parseFloat(9).toFixed(2))
            thisForm.find(".cgst-field:not(.disb)").val(parseFloat(9).toFixed(2))
            thisForm.find(".igst-field:not(.disb)").prop('disabled', true);
            thisForm.find(".sgst-field:not(.disb)").prop('disabled', false);
            thisForm.find(".cgst-field:not(.disb)").prop('disabled', false);
        } else {
            thisForm.find(".sgst-field").val('')
            thisForm.find(".cgst-field").val('')
            thisForm.find(".igst-field:not(.disb)").val(parseFloat(18).toFixed(2))
            thisForm.find(".igst-field:not(.disb)").prop('disabled', false);
            thisForm.find(".sgst-field:not(.disb)").prop('disabled', true);
            thisForm.find(".cgst-field:not(.disb)").prop('disabled', true);
        }
    }
    calcGstAmount();
}

function calcGstAmount() {
    var thisForm = jQuery('#commonServicePOForm');
    var gstType = thisForm.find("input[name='gst_type_fix_id']:checked").val();
    var basicAmount = thisForm.find("#basic_amount").val();


    basicAmount = isNaN(Number(basicAmount)) ? 0 : Number(basicAmount);
    var sumAmount = basicAmount;
    if (gstType != "") {
        if (gstType == 3) { // NONE
        } else if (gstType == 1) { //   SGCT+CSGT
            var sgstPer = thisForm.find("#sgst_percentage").val();
            var cgstPer = thisForm.find("#cgst_percentage").val();
            sgstPer = isNaN(Number(sgstPer)) ? 0 : Number(sgstPer);
            cgstPer = isNaN(Number(cgstPer)) ? 0 : Number(cgstPer);
            if (sumAmount > 0 && sgstPer > 0) {
                thisForm.find("#sgst_amount").val((sumAmount * (sgstPer / 100)).toFixed(2));
            } else {
                thisForm.find("#sgst_amount").val('');
            }

            if (sumAmount > 0 && cgstPer > 0) {
                thisForm.find("#cgst_amount").val((sumAmount * (cgstPer / 100)).toFixed(2));
            } else {
                thisForm.find("#cgst_amount").val('');
            }
        }
        else { // IGST
            var igstPer = thisForm.find("#igst_percentage").val();
            igstPer = isNaN(Number(igstPer)) ? 0 : Number(igstPer);
            if (sumAmount > 0 && igstPer > 0) {
                thisForm.find("#igst_amount").val((sumAmount * (igstPer / 100)).toFixed(2));
            } else {
                thisForm.find("#igst_amount").val('');
            }
        }
    }
    calcNetAmount();
    calculateRoundoffVal();
}

jQuery('#round_off_val , #basic_amount').on('input', function () {
    calcNetAmount();
});


function calcNetAmount() {
    var thisForm = jQuery('#commonServicePOForm');
    var gstType = thisForm.find("input[name='gst_type_fix_id']:checked").val();
    var basicAmount = thisForm.find("#basic_amount").val();
    basicAmount = isNaN(Number(basicAmount)) ? 0 : Number(basicAmount);


    var r_val = thisForm.find("#round_off_val").val();
    if (r_val != '') {
        if (r_val.trim() !== "") {
            var r = isNaN(Number(r_val)) ? 0 : Number(r_val);     // Convert round-off to number
        }
    } else {
        var r = 0;
    }

    if (gstType != "") {
        if (gstType == 3) { // None
            if (r < 0) {
                thisForm.find("#net_amount").val(parseFloat((basicAmount) - Math.abs(r)).toFixed(2));
            } else {
                thisForm.find("#net_amount").val(parseFloat(basicAmount + r).toFixed(2));
            }
        } else if (gstType == 1) { //   SGCT+CSGT
            var sgstAmount = thisForm.find("#sgst_amount").val();
            var cgstAmount = thisForm.find("#cgst_amount").val();
            sgstAmount = isNaN(Number(sgstAmount)) ? 0 : Number(sgstAmount);
            cgstAmount = isNaN(Number(cgstAmount)) ? 0 : Number(cgstAmount);
            if (r < 0) {
                thisForm.find("#net_amount").val(parseFloat(basicAmount + sgstAmount + cgstAmount - Math.abs(r)).toFixed(2));
            } else {
                thisForm.find("#net_amount").val(parseFloat(basicAmount + sgstAmount + cgstAmount + r).toFixed(2));
            }
        } else { //IGST
            var igstAmount = thisForm.find("#igst_amount").val();
            igstAmount = isNaN(Number(igstAmount)) ? 0 : Number(igstAmount);
            if (r < 0) {
                thisForm.find("#net_amount").val(parseFloat((basicAmount + igstAmount) - Math.abs(r)).toFixed(2));
            } else {
                thisForm.find("#net_amount").val(parseFloat(basicAmount + igstAmount + r).toFixed(2));
            }
        }
    }
}
jQuery('.gst-fields').on('change keyup', function () {
    calcGstAmount();
});

function calculateRoundoffVal() {
    var thisForm = jQuery("#commonServicePOForm");

    // Get all required values safely
    var basicAmount = parseFloat(thisForm.find("#basic_amount").val()) || 0;


    var sgstAmount = parseFloat(thisForm.find("#sgst_amount").val()) || 0;
    var cgstAmount = parseFloat(thisForm.find("#cgst_amount").val()) || 0;
    var igstAmount = parseFloat(thisForm.find("#igst_amount").val()) || 0;

    var gstType = thisForm.find("input[name*='gst_type_fix_id']:checked").val();

    // calculate subtotal (before round off)
    var subtotal = basicAmount;

    if (gstType == 1) {
        subtotal += sgstAmount + cgstAmount;
    } else if (gstType == 2) {
        subtotal += igstAmount;
    }

    // calculate round-off
    var roundedNet = Math.round(subtotal);
    var roundOff = (roundedNet - subtotal).toFixed(2);

    // update form fields
    thisForm.find("#round_off_val").val(roundOff);
    thisForm.find("#net_amount").val(roundedNet.toFixed(2));
}
