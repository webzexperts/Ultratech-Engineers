po_details_data = [];
var formId = jQuery('#commonPurchaseOrderForm').find('input[name="id"]').val();
let selectedRows = {};
$.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
    var row = $('#pendingPIDataTable').DataTable().row(dataIndex).node();
    var checkbox = $(row).find("input[type='checkbox']");
    if (checkbox.is(':checked')) {
        return true;
    }
    return true;
});
// Edit purchase order row click
jQuery('#dyntable tbody').on('click', '.edit-purchase_order', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#PurchaseOrderModal').find('#id').val(data["po_id"]);
    if (data && data["po_id"]) {
        fetchAndFillPurchaseOrder(data["po_id"]);
    }
});

// // Function to fetch and fill purchase order data
function fetchAndFillPurchaseOrder(id) {
    if (!id) return;
    jQuery('#PurchaseOrderModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-purchase_order",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.po_data != null) {
                jQuery('#PurchaseOrderModal').find('#id').val(data.po_data.po_id != "" ? data.po_data.po_id : "");
                let url = checkFileRoute + "?id=" + data.po_data.po_id + "&name=" + data.po_data.pdf_name + "&type=purchase_order";
                jQuery('#preview_btn').attr('href', url).show();
                var po_type = data.po_data.po_type_id;
                var gst = data.po_data.gst_type_fix_id;

                jQuery('#PurchaseOrderModal').find('input[name*="gst_type_fix_id"][value="' + gst + '"]').prop('checked', true);
                var thisForm = jQuery("#commonPurchaseOrderForm");

                if (gst == 3) {
                    thisForm.find(".igst-field").val('')
                    thisForm.find(".igst-field:not(.disb)").prop('disabled', true);
                    thisForm.find(".sgst-field").val('')
                    thisForm.find(".sgst-field:not(.disb)").prop('disabled', true);
                    thisForm.find(".cgst-field").val('')
                    thisForm.find(".cgst-field:not(.disb)").prop('disabled', true);
                } else if (gst == 1) {
                    thisForm.find(".igst-field").val('')
                    thisForm.find(".sgst-field:not(.disb)").val(parseFloat(data.po_data.sgst_percentage).toFixed(2))
                    thisForm.find(".cgst-field:not(.disb)").val(parseFloat(data.po_data.cgst_percentage).toFixed(2))
                    thisForm.find(".igst-field:not(.disb)").prop('disabled', true);
                    thisForm.find(".sgst-field:not(.disb)").prop('disabled', false);
                    thisForm.find(".cgst-field:not(.disb)").prop('disabled', false);
                } else {
                    thisForm.find(".sgst-field").val('')
                    thisForm.find(".cgst-field").val('')
                    thisForm.find(".igst-field:not(.disb)").val(parseFloat(data.po_data.igst_percentage).toFixed(2))
                    thisForm.find(".igst-field:not(.disb)").prop('disabled', false);
                    thisForm.find(".sgst-field:not(.disb)").prop('disabled', true);
                    thisForm.find(".cgst-field:not(.disb)").prop('disabled', true);
                }

                jQuery('#PurchaseOrderModal').find('input[name*="po_type_id"][value="' + po_type + '"]').prop('checked', true).change();
                jQuery('#PurchaseOrderModal').find('#po_number').val(data.po_data.po_number != "" ? data.po_data.po_number : "");
                jQuery('#PurchaseOrderModal').find('#po_date').val(data.po_data.po_date != "" ? data.po_data.po_date : "");
                jQuery('#PurchaseOrderModal').find('#old_po_date').val(data.po_data.po_date != "" ? data.po_data.po_date : "");
                jQuery('#PurchaseOrderModal').find('#po_sequence').val(data.po_data.po_sequence != "" ? data.po_data.po_sequence : "");
                jQuery('#PurchaseOrderModal').find('#po_supplier_id').val(data.po_data.po_supplier_id).trigger('change.select2');
                jQuery('#PurchaseOrderModal').find('#payment_terms').val(data.po_data.payment_terms != "" ? data.po_data.payment_terms : "");

                getSupplierKindAttn().done(function () {
                    jQuery('#PurchaseOrderModal')
                        .find('#po_kind_attn_id')
                        .val(zeroToEmpty(data.po_data.po_kind_attn_id))
                        .trigger('change.select2');
                });



                jQuery('#PurchaseOrderModal').find('#ref_no_date').val(data.po_data.ref_no_date != "" ? data.po_data.ref_no_date : "");
                setRadioReadonly("input[name='po_type_id']", true);
                jQuery('#PurchaseOrderModal').find('#bill_to_location_id').val(data.po_data.bill_to_location_id != "" ? data.po_data.bill_to_location_id : "").trigger('change.select2');

                jQuery('#PurchaseOrderModal').find('#ship_to_location_id').val(data.po_data.ship_to_location_id != "" ? data.po_data.ship_to_location_id : "").trigger('change.select2');

                if (data.po_data.in_use == true) {
                    jQuery('#PurchaseOrderModal').find('#ship_to_location_id').addClass('skip-tab');
                    setSelect2Readonly('#PurchaseOrderModal #ship_to_location_id', true);
                    setSelect2Readonly('#PurchaseOrderModal #po_supplier_id', true);
                    jQuery('#PurchaseOrderModal').find('#po_supplier_id').addClass('skip-tab');
                } else {
                    jQuery('#PurchaseOrderModal').find('#ship_to_location_id').removeClass('skip-tab');
                    setSelect2Readonly('#PurchaseOrderModal #ship_to_location_id', false);
                    jQuery('#PurchaseOrderModal').find('#po_supplier_id').removeClass('skip-tab');
                    setSelect2Readonly('#PurchaseOrderModal #po_supplier_id', false);
                }
                jQuery('#PurchaseOrderModal').find('#po_sp_note').val(data.po_data.po_sp_note != "" ? data.po_data.po_sp_note : "");

                jQuery('#PurchaseOrderModal').find('#po_terms_and_conditions').val(data.po_data.po_terms_and_conditions != "" ? data.po_data.po_terms_and_conditions : "");

                jQuery('#PurchaseOrderModal').find('#po_sp_note').val(data.po_data.po_sp_note != "" ? data.po_data.po_sp_note : "");
                jQuery('#PurchaseOrderModal').find('#prepared_by_user_id').val(data.po_data.prepared_by_user_id != "" ? data.po_data.prepared_by_user_id : "");



                if (data.po_details_data != "" && data.po_details_data.length > 0) {
                    po_details_data.push(...data.po_details_data);
                    fillPurchaseOrderDetailTable();
                }

                jQuery('#PurchaseOrderModal').find('#pending_btn').prop('disabled', true);
                jQuery('#PurchaseOrderModal').find('#submitbtn').prop('disabled', false);

                const form = document.getElementById("commonPurchaseOrderForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#PurchaseOrderModal').find('#add_new').show();
                jQuery('#PurchaseOrderModal').find('#preview_btn').show();

                if (data.po_data.in_use == true) {
                    jQuery('#PurchaseOrderModal').find('#po_sequence').prop('readonly', true);
                    let nextInput = jQuery('#PurchaseOrderModal').find('#po_date');
                    if (nextInput.hasClass('trans-date-picker') || nextInput.hasClass('date-picker')) {
                        nextInput.one('focus', function () {
                            setTimeout(() => {
                                jQuery(this).datepicker('hide');
                            }, 50);
                        });
                    }
                    nextInput.focus();
                } else {
                    jQuery('#PurchaseOrderModal').find('#po_sequence').prop('readonly', false);
                    jQuery('#PurchaseOrderModal').find('#po_sequence').focus();
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

// Reset button click for purchase order modal
jQuery('#resetbtn').on('click', function () {
    po_details_data = [];
    selectedRows = {};

    jQuery('#PurchaseOrderDetailTable tbody').empty();

    var formId = jQuery('#PurchaseOrderModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonPurchaseOrderForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        jQuery('#commonPurchaseOrderForm').find('#prepared_by_user_id').val(loginUserId).trigger("change.select2");
        jQuery('#po_sequence').prop('readonly', false).focus();
        jQuery('#PurchaseOrderModal').find("#po_supplier_id").val('').trigger('change');
        jQuery('#PurchaseOrderModal').find("#po_kind_attn_id").val('').trigger('change.select2');
        jQuery('#PurchaseOrderModal').find('#bill_to_location_id').val('').trigger('change');
        jQuery('#PurchaseOrderModal').find('#ship_to_location_id').val('').trigger('change');

        getLatestPurchaseOrderNo();
    } else {
        fetchAndFillPurchaseOrder(formId);
    }
});



jQuery('#PurchaseOrderModal').on('show.bs.modal', function () {
    var formId = jQuery('#commonPurchaseOrderForm').find('input[name="id"]').val();
    var hasAccess = jQuery('#commonPurchaseOrderForm').find('#has_access').val();
    jQuery('#commonPurchaseOrderForm').find('#prepared_by_user_id').val(loginUserId).trigger("change.select2");
    jQuery('#PurchaseOrderModal').find('#po_sequence').prop('readonly', false);
    if (formId == "" || formId == undefined) {
        setRadioReadonly("input[name='po_type_id']", false);
        getLatestPurchaseOrderNo();
        manageGstType();
        jQuery('#PurchaseOrderModal').find('#add_new').hide();
        jQuery('#PurchaseOrderModal').find('#preview_btn').hide();
        setRadioReadonly("input[name='po_type_id']", false);
    } else {
        jQuery('#PurchaseOrderModal').find('#add_new').show();
        jQuery('#PurchaseOrderModal').find('#preview_btn').show();
        setRadioReadonly("input[name='po_type_id']", false);
    }
});
jQuery('#PurchaseOrderModal').on('click', '#add_new', function () {
    jQuery('#PurchaseOrderModal').find('#id').val('');
    jQuery("#PurchaseOrderDetailsForm #pod_pid_id").val(0).trigger('change.select2');
    jQuery("#PurchaseOrderDetailsForm #pod_id").val(0).trigger('change.select2');
    setRadioReadonly("input[name='po_type_id']", false);
    jQuery('#PurchaseOrderModal').find('#pending_btn').prop('disabled', false);
    document.getElementById("commonPurchaseOrderForm").reset();
    const form = document.getElementById("commonPurchaseOrderForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#commonPurchaseOrderForm').find('#prepared_by_user_id').val(loginUserId).trigger("change.select2");
    jQuery('#po_sequence').prop('readonly', false).focus();
    getLatestPurchaseOrderNo();
    jQuery('#PurchaseOrderDetailTable tbody').empty();
    jQuery('#PurchaseOrderDetailTable tbody').append(`
        <tr>
            <td colspan="12" id="noDetails">
                No Purchase Order Details Added
            </td>
        </tr>
    `);
    po_details_data = [];
    jQuery("#totalAmount").text("0.00");

    jQuery('#PurchaseOrderModal').find('input[name*="po_type_id"][value="Manual"]').prop('checked', true).change();
    jQuery('#PurchaseOrderModal').find("#po_supplier_id").val('').trigger('change');
    jQuery('#PurchaseOrderModal').find('#bill_to_location_id').val('').trigger('change');
    jQuery('#PurchaseOrderModal').find('#ship_to_location_id').val('').trigger('change');

    jQuery('#PurchaseOrderModal').find('#add_new').hide();
    jQuery('#PurchaseOrderModal').find('#preview_btn').hide();
});


jQuery('#PurchaseOrderModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#PurchaseOrderModal');
    thisModal.find("#id").val("");
    var thisForm = jQuery('#PurchaseOrderDetailsModal');
    thisForm.find("#pod_id").val(0);
    thisForm.find("#pod_pid_id").val(0);
    po_details_data = [];
    jQuery('#PurchaseOrderModal').find('#add_new').hide();
    jQuery('#PurchaseOrderModal').find('#preview_btn').hide();
    setRadioReadonly("input[name='po_type_id']", false);

    if (formId == "" || formId == undefined) {
        // thisModal.find("#po_number").prop({ tabindex: 0, readonly: false });
        setSelect2Readonly('#po_supplier_id', false);
        setSelect2Readonly('#po_kind_attn_id', false);
    }
    jQuery('#PurchaseOrderDetailTable tbody').empty();
    jQuery('#commonPurchaseOrderForm').trigger("reset");
    jQuery('#commonPurchaseOrderForm .toggleModalBtn').prop('disabled', true);
    jQuery('#commonPurchaseOrderForm #totalAmount').text("0.00");
    var po_type = jQuery('#commonPurchaseOrderForm').find('input[name*="po_type_id"]:checked').val();
    jQuery('#PurchaseOrderModal').find('input[name*="po_type_id"][value="' + po_type + '"]').prop('checked', true).change();
    jQuery('#PurchaseOrderModal').find('#ship_to_location_id').removeClass('skip-tab');
    setSelect2Readonly('#PurchaseOrderModal #ship_to_location_id', false);
    jQuery('#PurchaseOrderModal').find('#po_supplier_id').removeClass('skip-tab');
    setSelect2Readonly('#PurchaseOrderModal #po_supplier_id', false);
    jQuery('#PurchaseOrderModal').find('#po_sequence').prop('readonly', false);
    // thisModal.find('input, textarea, select').each(function () {
    //     jQuery(this).val('');
    //     jQuery(this).prop('checked', false);
    // });
});

jQuery('#PurchaseOrderDetailsModal').on('show.bs.modal', function () {
    let thisModal = jQuery('#PurchaseOrderDetailsModal');
    var PoTypeId = jQuery('#commonPurchaseOrderForm').find('input[name*="po_type_id"]:checked').val();
    var details_id = thisModal.find("#pod_id").val();
    var form_type = thisModal.find("#form_type").val();
    if (PoTypeId == "From Indent" || (form_type == "edit" && details_id != 0)) {
        jQuery('#pod_item_id').addClass('skip-tab');
    } else {
        jQuery('#pod_item_id').removeClass('skip-tab');
        setTimeout(function () {
            let sel = jQuery('#pod_item_id')
                .next('.select2-container')
                .find('.select2-selection');
            sel.attr('tabindex', 0).focus();
        }, 20);
    }
    var mode = jQuery("#PurchaseOrderDetailsModal #form_type").val();
    var pod_id = jQuery("#PurchaseOrderDetailsModal #pod_id").val();
    if (mode == "add" && pod_id == "0") {
        jQuery('#PurchaseOrderDetailsModal #pod_po_qty').removeAttr('min');
    }




});

jQuery('#PurchaseOrderDetailsModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#PurchaseOrderDetailsModal');
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    thisModal.find("#pod_id").val(0);
    thisModal.find("#pod_pid_id").val(0);
    jQuery('#podGRNDetailsTable tbody').empty()

    jQuery(this).find('#pod_item_id option.temp-item').remove();
    jQuery('#PurchaseOrderDetailsForm').trigger("reset");
    this.dataset.customHideFocus = 'true';
    const input = jQuery("#commonPurchaseOrderForm").find("input[name='gst_type_fix_id']");
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }

    jQuery('#PurchaseOrderDetailsForm #pod_po_qty').removeAttr('min');
    jQuery('#PurchaseOrderDetailsForm #pod_po_qty').removeAttr('max');
});

// get the latest number
function getLatestPurchaseOrderNo() {
    jQuery.ajax({
        url: "get-latest_purchase_order_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {

                jQuery('#po_number').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#po_sequence').val(data.number);
                jQuery('#po_date').val(currentDate);
                jQuery('#PurchaseOrderModal').find('#ship_to_location_id').removeClass('skip-tab');
                setSelect2Readonly('#PurchaseOrderModal #ship_to_location_id', false);

                jQuery('#PurchaseOrderModal').find('#po_supplier_id').removeClass('skip-tab');
                setSelect2Readonly('#PurchaseOrderModal #po_supplier_id', false);

            } else {
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            jQuery('#po_number').removeClass('file-loader');
            console.log('Field To Get Latest Purchase Order No.!')
        }
    });
}

jQuery('#PurchaseOrderModal').on('change', '#po_supplier_id', function () {
    getSupplierKindAttn();
});

function getSupplierKindAttn() {

    let selectedOption = jQuery('#po_supplier_id').find('option:selected');
    let supplier_id = selectedOption.val();
    let $modal = jQuery('#PurchaseOrderModal');
    let $attnSelect = $modal.find('#po_kind_attn_id');
    $attnSelect.empty().append('<option value="">Select Kind Attn.</option>');
    if (supplier_id) {

        return jQuery.ajax({
            url: 'get-supplier_kind_attn',
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

                    if (data.LnrData) {
                        jQuery("#po_terms_and_conditions").val(data.LnrData.po_terms_and_conditions);
                    } else {
                        jQuery("#po_terms_and_conditions").val('');
                    }
                }
            }
        });
    } else {
        $modal.find('#po_kind_attn_id').val('').trigger('change.select2');
        $modal.find("#po_terms_and_conditions").val('');

    }
}


jQuery('#PurchaseOrderDetailsModal').on('change', '#pod_item_id', function () {

    let selectedOption = jQuery(this).find('option:selected');

    let stock = selectedOption.data('stock');
    let unit = selectedOption.data('unit');
    let desciption = selectedOption.data('item_name');
    let formId = jQuery('#commonPurchaseOrderForm').find('input[name="id"]').val();

    let $modal = jQuery('#PurchaseOrderDetailsModal');

    if (stock !== undefined && unit !== undefined) {
        $modal.find('#pod_stock').val(stock);
        $modal.find('#pod_unit').val(unit);
        if (formId == "" || formId == undefined) {
            $modal.find('#pod_description').val(desciption);
        }
    } else {
        // reset if no item selected
        $modal.find('#pod_stock').val('');
        $modal.find('#pod_unit').val('');
    }
});

// Main Purchase Order Form Submit
$('#commonPurchaseOrderForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#PurchaseOrderModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;



    var dateValue = document.getElementById("po_date").value.trim();
    var sgst = parseFloat(document.getElementById("sgst_percentage").value) || 0;
    var cgst = parseFloat(document.getElementById("cgst_percentage").value) || 0;
    var igst = parseFloat(document.getElementById("igst_percentage").value) || 0;

    if (sgst > 0 || cgst > 0 || igst > 0) {

        if (sgst > 100) {
            toastr.error("SGST Cannot Be More Than 100%");
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#PurchaseOrderModal').find('#submitbtn').prop('disabled', false);
            return false;
        }

        if (cgst > 100) {
            toastr.error("CGST Cannot Be More Than 100%");
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#PurchaseOrderModal').find('#submitbtn').prop('disabled', false);
            return false;
        }

        if (igst > 100) {
            toastr.error("IGST Cannot Be More Than 100%");
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#PurchaseOrderModal').find('#submitbtn').prop('disabled', false);
            return false;
        }
    }

    // validatePercentage
    if (!isValidDate(dateValue)) {
        toastr.error("Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PurchaseOrderModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PurchaseOrderModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PurchaseOrderModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    jQuery('#PurchaseOrderModal').find('#submitbtn').prop('disabled', true);

    var formIdnew = jQuery('#PurchaseOrderModal').find('#commonPurchaseOrderForm').find('#id').val();
    var formUrl = formIdnew != undefined && formIdnew != "" ? "update-purchase_order" : "store-purchase_order";
    var data = new FormData(form);
    data.append('po_details_data', JSON.stringify(po_details_data ?? []));
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (po_details_data.length > 0 && !jQuery.isEmptyObject(po_details_data)) {
        let isValidDetails = true;
        let errorMsg = '';

        function parseDate(dateStr) {
            if (!dateStr) return null;
            var parts = dateStr.split("/");
            return new Date(parts[2], parts[1] - 1, parts[0]);
        }
        let poDate = parseDate(dateValue);

        $.each(po_details_data, function (index, row) {

            if (row.pod_po_qty === undefined || row.pod_po_qty === null || row.pod_po_qty === '' || parseFloat(row.pod_po_qty) <= 0) {
                errorMsg = `Enter PO Qty.`;
                isValidDetails = false;
                return false;
            }

            if (row.pod_rate_unit === undefined || row.pod_rate_unit === null || row.pod_rate_unit === '' || parseFloat(row.pod_rate_unit) <= 0) {
                errorMsg = `Enter Rate / Unit.`;
                isValidDetails = false;
                return false;
            }

            if (row.pod_del_date === undefined || row.pod_del_date === null || row.pod_del_date.trim() === '') {
                errorMsg = `Enter Del. Date.`;
                isValidDetails = false;
                return false;
            }

            let selectedDate = parseDate(row.pod_del_date);
            if (poDate && selectedDate < poDate) {
                errorMsg = `Del. Date Must Be Greater Than PO Date`;
                isValidDetails = false;
                return false;
            }

        });

        if (!isValidDetails) {
            toastr.error(errorMsg);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');

            jQuery('#PurchaseOrderModal')
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
                        jQuery('#PurchaseOrderModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formIdnew == undefined || formIdnew == "") {
                        function nextFn() {
                            document.getElementById("commonPurchaseOrderForm").reset();
                            const form = document.getElementById("commonPurchaseOrderForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            jQuery('#po_sequence').prop('readonly', false).focus();

                            $("#po_supplier_id").val('').trigger('change');
                            $("#bill_to_location_id").val('').trigger('change.select2');
                            $("#ship_to_location_id").val('').trigger('change.select2');
                            $("#PurchaseOrderDetailsForm #pod_pid_id").val(0).trigger('change.select2');
                            $("#PurchaseOrderDetailsForm #pod_id").val(0).trigger('change.select2');
                            po_details_data = [];
                            selectedRows = {};
                            jQuery('#PurchaseOrderDetailTable tbody').empty();
                            jQuery("#totalAmount").text("0.00");
                            getLatestPurchaseOrderNo();
                            jQuery('#PurchaseOrderModal').find('input[name*="po_type_id"][value="Manual"]').prop('checked', true).change().focus();
                            setRadioReadonly("input[name='po_type_id']", false);
                            jQuery('#PurchaseOrderModal').find('#pending_btn').prop('disabled', true);
                        }

                        if (data.url != "") {
                            toastSuccessPreview(data.response_message, data.url, nextFn);
                        } else {
                            toastSuccess(data.response_message, nextFn);
                        }
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#PurchaseOrderModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#PurchaseOrderModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#PurchaseOrderModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#PurchaseOrderModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#PurchaseOrderModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        toastr.error('Add At Least One Purchase Order Details.');

        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PurchaseOrderModal').find('#submitbtn').prop('disabled', false);
    }
});
// Main Form Submit End

// Purchase Order Details Form Submit Start
$('#PurchaseOrderDetailsForm').on('submit', function (e) {

    e.preventDefault();
    let form = this;


    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    var dateValue = document.getElementById("pod_del_date").value.trim();
    if (!isValidDate(dateValue) && dateValue != "") {
        toastr.error("Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PurchaseOrderDetailsModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var data = new FormData(document.getElementById('PurchaseOrderDetailsForm'));
    var formValue = Object.fromEntries(data.entries());
    var thisModal = jQuery('#PurchaseOrderDetailsModal');

    var pod_po_qty = formValue.pod_po_qty ? parseFloat(formValue.pod_po_qty) : 0;
    var pod_rate_unit = formValue.pod_rate_unit ? parseFloat(formValue.pod_rate_unit) : 0;
    // var pend_pi_qty = formValue.pend_pi_qty ? parseFloat(formValue.pend_pi_qty) : 0;

    var po_type_id = jQuery('#commonPurchaseOrderForm').find('input[name*="po_type_id"]:checked').val();
    // if (pod_po_qty > pend_pi_qty && po_type_id == "From Indent") {
    //     toastr.error('PO Qty. Cannot Be Greater Than Pending PI Qty. ' + pend_pi_qty.toFixed(3));
    //     return;
    // };
    // var used_qty = parseFloat(jQuery('#pod_po_qty').attr('min')) || 0;
    // if (used_qty > pod_po_qty) {
    //     toastr.error('PO Qty. Cannot Be Less Than ' + used_qty.toFixed(3));
    //     return;
    // }

    if (pod_po_qty < 0.001) {
        toastr.error('Enter PO Qty. greater than 0.001.');
        return;
    }
    if (pod_rate_unit < 0.01) {
        toastr.error('Enter Rate / Unit greater than 0.01.');
        return;
    }

    if (formValue.pod_item_id.trim()) {

        function parseDate(dateStr) {
            if (!dateStr) return null;
            var parts = dateStr.split("/");
            return new Date(parts[2], parts[1] - 1, parts[0]);
        }
        var PODateValue = jQuery('#po_date').val().trim();
        var selectedDate = parseDate(formValue.pod_del_date);
        var poDate = parseDate(PODateValue);

        if (poDate && selectedDate < poDate) {
            toastr.error('Del. Date Must Be Greater Than PO Date');
            return;
        }
        var noDuplicate = true;

        if (noDuplicate) {
            var pod_item_id = formValue.pod_item_id ? formValue.pod_item_id : '';
            var item_name = formValue.item_name ? formValue.item_name : thisModal.find('#pod_item_id option[value="' + pod_item_id + '"]').text();
            // var pi_no = formValue.pi_no ? formValue.pi_no : '';
            // var pi_date = formValue.pi_date ? formValue.pi_date : '';
            var item_group = formValue.item_group != "" ? formValue.item_group : '';
            var main_group = formValue.main_group != "" ? formValue.main_group : '';
            var io_stock_qty = parseFloat(formValue.io_stock_qty || 0).toFixed(3);
            // var pend_pi_qty = formValue.pend_pi_qty != "" ? formValue.pend_pi_qty : '';
            var pod_po_qty = formValue.pod_po_qty ? parseFloat(formValue.pod_po_qty).toFixed(3) : parseFloat(0).toFixed(3);
            var pod_unit = formValue.pod_unit != "" ? formValue.pod_unit : '';
            var pod_rate_unit = formValue.pod_rate_unit ? parseFloat(formValue.pod_rate_unit).toFixed(2) : parseFloat(0).toFixed(2);
            var pod_amount = formValue.pod_amount ? parseFloat(formValue.pod_amount).toFixed(2) : parseFloat(0).toFixed(2);
            var pod_del_date = formValue.pod_del_date != "" ? formValue.pod_del_date : "";

            var pod_remark = formValue.pod_remark ? formValue.pod_remark : "";
            var unit = thisModal.find('#pod_po_qty_unit').text();
            formValue.unit = unit;

            if (pod_item_id != "") {
                if (formValue.form_type == "edit") {
                    formValue.item_name = item_name;
                    // po_details_data[formValue.form_index] = formValue;
                    po_details_data[formValue.form_index] = {
                        ...po_details_data[formValue.form_index],
                        ...formValue
                    };
                    var in_use = po_details_data[formValue.form_index].in_use == true ? true : false;
                    let tblHtml = ``;
                    tblHtml += `<td>`;
                    tblHtml += in_use == true ? DetailsActionDropdown('editPurchaseOrderDetails') : DetailsActionDropdown('editPurchaseOrderDetails', 'removePurchaseOrderDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                    </td>`;
                    // tblHtml += `<td>${pi_no}</td>`;
                    // tblHtml += `<td>${pi_date}</td>`;
                    tblHtml += `<td>${item_name}</td>`;
                    tblHtml += `<td>${item_group}</td>`;
                    tblHtml += `<td>${main_group}</td>`;
                    tblHtml += `<td>${io_stock_qty}</td>`;
                    // tblHtml += `<td>${pend_pi_qty}</td>`;
                    tblHtml += `<td>${pod_po_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${pod_rate_unit}</td>`;
                    tblHtml += `<td class="amount">${pod_amount}</td>`;
                    tblHtml += `<td>${pod_del_date}</td>`;
                    tblHtml += `<td>${pod_remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#PurchaseOrderDetailTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
                    toastSuccess("Record Updated.");
                } else {
                    po_details_data.push(formValue)
                    if (po_details_data.length > 0) {
                        setRadioReadonly("input[name='po_type_id']", true);
                    }
                    let formIndx = po_details_data.indexOf(formValue);
                    if (jQuery('#PurchaseOrderDetailTable tbody').find('#noDetails').length > 0) {
                        jQuery('#PurchaseOrderDetailTable tbody').empty();
                    }

                    let tblHtml = `<tr>`;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editPurchaseOrderDetails', 'removePurchaseOrderDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
                    </td>`;
                    // tblHtml += `<td>${pi_no}</td>`;
                    // tblHtml += `<td>${pi_date}</td>`;
                    tblHtml += `<td>${item_name}</td>`;
                    tblHtml += `<td>${item_group}</td>`;
                    tblHtml += `<td>${main_group}</td>`;
                    tblHtml += `<td>${io_stock_qty}</td>`;
                    // tblHtml += `<td>${pend_pi_qty}</td>`;
                    tblHtml += `<td>${pod_po_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${pod_rate_unit}</td>`;
                    tblHtml += `<td class="amount">${pod_amount}</td>`;
                    tblHtml += `<td>${pod_del_date}</td>`;
                    tblHtml += `<td>${pod_remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#PurchaseOrderDetailTable tbody').append(tblHtml);
                    toastSuccess("Record Inserted.");
                }
            }
            calculateTotalAmount();
            if (formValue.form_type == "edit") {
                thisModal.modal('hide');
            } else {
                let isRepeat = jQuery('#repeat_item').is(':checked');
                if (isRepeat) {
                    jQuery('#pod_po_qty').val('');
                    jQuery('#pod_amount').val('');
                    jQuery('#pod_del_date').val('');
                    jQuery('#pod_remark').val('');

                    let formElement = document.getElementById('PurchaseOrderDetailsForm');
                    setTimeout(function () {
                        $(formElement).removeClass('was-validated');
                        $(formElement).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                        thisModal.find('.error').removeClass('error');
                        jQuery('#pod_po_qty').focus();
                    }, 150);
                } else {
                    let formElement = document.getElementById('PurchaseOrderDetailsForm');
                    formElement.reset();
                    jQuery('#pod_item_id').val('').trigger('change.select2');
                    jQuery('#pod_stock').val('');
                    jQuery('#pod_po_qty').val('');
                    jQuery('#pod_unit').val('');
                    jQuery('#pod_rate_unit').val('');
                    jQuery('#pod_amount').val('');
                    jQuery('#pod_del_date').val('');
                    jQuery('#pod_remark').val('');
                    jQuery('#io_stock_qty_unit').text('').removeClass('ms-1');
                    jQuery('#pod_po_qty_unit').text('').removeClass('ms-1');
                    setTimeout(function () {

                        let $select = jQuery('#PurchaseOrderDetailsForm #pod_item_id');
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
        }
    } else {
        toastr.error('Select Item Name');
    }
});


function editPurchaseOrderDetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();
    fillPurchaseOrderDetailsForm(formIndx, rawIndx);
}

// purchase order details form edit
function fillPurchaseOrderDetailsForm(formIndx, rawIndx) {
    let thisForm = jQuery('#PurchaseOrderDetailsModal');
    var PoTypeId = jQuery('#commonPurchaseOrderForm').find('input[name*="po_type_id"]:checked').val();


    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    thisForm.find('#pod_item_id option.temp-item').remove();
    var frmData = po_details_data[formIndx];

    let itemId = frmData.pod_item_id;
    let itemText = frmData.item_name;

    // check if option exists
    if (thisForm.find("#pod_item_id option[value='" + itemId + "']").length === 0) {
        thisForm.find("#pod_item_id").append(
            `<option  class="temp-item" value="${itemId}" data-main_group="${frmData.main_group}" data-item_group="${frmData.item_group}" data-io_stock_qty="${frmData.io_stock_qty}" selected>${itemText}</option>`
        );
    }
    if (frmData.in_use === true) {
        let usedQty = parseFloat(frmData.used_qty) || 0;
        thisForm.find("#pod_po_qty").attr('min', usedQty.toFixed(3));

    } else {
        thisForm.find("#pod_po_qty").attr('min', 0);
    }

    thisForm.find("#pod_id").val(frmData.pod_id);
    thisForm.find("#pod_pid_id").val(frmData.pod_pid_id != "" ? frmData.pod_pid_id : "");
    // thisForm.find('#pi_no').val(frmData.pi_no ? frmData.pi_no : "");
    // thisForm.find('#pi_date').val(frmData.pi_date ? frmData.pi_date : "");
    thisForm.find("#pod_item_id").val(zeroToEmpty(frmData.pod_item_id != "" ? frmData.pod_item_id : "")).trigger('change');
    thisForm.find("#io_stock_qty").val(parseFloat(frmData.io_stock_qty || 0).toFixed(3));
    // thisForm.find("#pend_pi_qty").val(frmData.pend_pi_qty != "" ? parseFloat(frmData.pend_pi_qty).toFixed(3) : "");
    var decPlaces = (frmData.main_group == 'Industrial X-Ray Films') ? 0 : 3;
    thisForm.find("#pod_po_qty").val(frmData.pod_po_qty ? parseFloat(frmData.pod_po_qty).toFixed(decPlaces) : parseFloat(0).toFixed(decPlaces)).change();
    if (frmData.main_group == 'Industrial X-Ray Films') {
        thisForm.find('#pod_po_qty').removeClass('isNumberKey').addClass('isNumberKeyNotDot');
        thisForm.find('#pod_po_qty').attr('onblur', 'formatPoints(this, 0)');
    } else {
        thisForm.find('#pod_po_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        thisForm.find('#pod_po_qty').attr('onblur', 'formatPoints(this, 3)');
    }
    thisForm.find("#pod_rate_unit").val(frmData.pod_rate_unit ? parseFloat(frmData.pod_rate_unit).toFixed(2) : parseFloat(0).toFixed(2)).change();
    thisForm.find("#pod_del_date").val(frmData.pod_del_date ? frmData.pod_del_date : "");
    thisForm.find("#pod_remark").val(frmData.pod_remark ? frmData.pod_remark : "");
    // thisForm.find("#pod_po_qty").attr('max', parseFloat(frmData.pend_pi_qty).toFixed(3));

    if (frmData.in_use == true) {

        thisForm.find("#pod_po_qty").attr('min', parseFloat(frmData.used_qty).toFixed(3));

    }

    if (PoTypeId == "From Indent" || frmData.pod_id != 0) {
        thisForm.find('#pod_item_id').addClass('skip-tab');
    } else {
        thisForm.find('#pod_item_id').removeClass('skip-tab');
    }


    thisForm.modal('show');
}

function calculateTotalAmount() {
    let total = 0;

    jQuery('#PurchaseOrderDetailTable tbody tr').each(function () {
        let amount = jQuery(this).find('td.amount').text();

        amount = parseFloat(amount) || 0;
        total += amount;
    });

    if (total != 0) {
        jQuery('#totalAmount').text(total.toFixed(2));
        jQuery('#basic_amount').val((total).toFixed(2));
        jQuery('#net_amount').val((total).toFixed(2));
    } else {
        jQuery('#totalAmount').text((0).toFixed(2));
        jQuery('#net_amount').val((0).toFixed(2));
    }
    calcGstAmount();
}

jQuery('#PurchaseOrderDetailsForm #pod_item_id').on('change', function () {
    var tblHtml = `<tr class="centeralign" id="noItems">
                                        <td colspan="5">No data Available</td>
                                    </tr>`;
    jQuery('#podGRNDetailsTable tbody').empty().append(tblHtml);
    let selected = jQuery(this).find('option:selected');
    let main_group = selected.data('main_group');
    let item_group = selected.data('item_group');
    let io_stock_qty = selected.data('io_stock_qty');
    let unit = selected.data('unit');
    if (selected.val() != "") {
        jQuery('#item_group').val(item_group);
        jQuery('#main_group').val(main_group);
        jQuery('#io_stock_qty').val(io_stock_qty ? parseFloat(io_stock_qty).toFixed(3) : parseFloat(0).toFixed(3));
        jQuery('#io_stock_qty_unit').text(unit).addClass('ms-1');
        // jQuery('#pend_pi_qty_unit').text(unit).addClass('ms-1');
        jQuery('#pod_po_qty_unit').text(unit).addClass('ms-1');
        fillPodGRNDetailsTable();

        if (main_group == 'Industrial X-Ray Films') {
            jQuery('#pod_po_qty').removeClass('isNumberKey').addClass('isNumberKeyNotDot');
            jQuery('#pod_po_qty').attr('onblur', 'formatPoints(this, 0)');
            if (jQuery('#pod_po_qty').val() !== '') {
                jQuery('#pod_po_qty').val(parseInt(jQuery('#pod_po_qty').val()) || '');
            }
        } else {
            jQuery('#pod_po_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
            jQuery('#pod_po_qty').attr('onblur', 'formatPoints(this, 3)');
        }
    } else {
        jQuery('#item_group').val('');
        jQuery('#main_group').val('');
        jQuery('#io_stock_qty').val('');
        jQuery('#io_stock_qty_unit').text('').removeClass('ms-1');
        // jQuery('#pend_pi_qty_unit').text('').removeClass('ms-1');
        jQuery('#pod_po_qty_unit').text('').removeClass('ms-1');
        jQuery('#pod_po_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        jQuery('#pod_po_qty').attr('onblur', 'formatPoints(this, 3)');
    }
});

function fillPodGRNDetailsTable() {
    var po_supplier_id = jQuery('#po_supplier_id').val();
    var pod_item_id = jQuery('#pod_item_id').val();
    if (po_supplier_id || pod_item_id) {
        jQuery.ajax({
            url: 'get-pod_grn_details',
            type: 'POST',
            headers: headerOpt,
            data: {
                'supplier_id': po_supplier_id,
                'item_id': pod_item_id

            },
            success: function (data) {
                let tblHtml = '';
                if (data.response_code == 1 && data.grn_data.length > 0) {
                    if (data.grn_data.length > 0 && !jQuery.isEmptyObject(data.grn_data)) {
                        for (let idx in data.grn_data) {
                            tblHtml += `
                                        <tr>
                                            <
                                            <td>${(data.grn_data[idx].grn_number == null) ? "" : data.grn_data[idx].grn_number}</td>
                                            <td>${(data.grn_data[idx].grn_date == null) ? "" : data.grn_data[idx].grn_date}</td>
                                            <td>${(data.grn_data[idx].supplier_name == null) ? "" : data.grn_data[idx].supplier_name}</td>
                                            <td>${(data.grn_data[idx].grnd_qty == null) ? "" : data.grn_data[idx].grnd_qty}</td>
                                            <td>${(data.grn_data[idx].grnd_rate_unit == null) ? "" : data.grn_data[idx].grnd_rate_unit}</td>
                                        </tr>`;
                        }
                    } else {
                        tblHtml += `<tr class="centeralign" id="noDetails">
                                        <td colspan="5">No data Available</td>
                                    </tr>`;

                    }
                    var $table = jQuery('#podGRNDetailsTable');
                    if (jQuery.fn.DataTable.isDataTable($table)) {
                        $table.DataTable().clear().destroy();
                    }
                    jQuery('#podGRNDetailsTable tbody').empty().append(tblHtml);

                } else {
                    //toastr.error(data.response_message);

                    tblHtml += `<tr class="centeralign" id="noItems">
                                        <td colspan="5">No data Available</td>
                                    </tr>`;
                    var $table = jQuery('#podGRNDetailsTable');
                    if (jQuery.fn.DataTable.isDataTable($table)) {
                        $table.DataTable().clear().destroy();
                    }
                    jQuery('#podGRNDetailsTable tbody').empty().append(tblHtml);

                }

            }
        });

    }
}

function removePurchaseOrderDetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        removeFormObj(formIndx);
        jQuery(th).closest("tr").remove();

    });
}

function removeFormObj(formIndx) {
    delete po_details_data[formIndx];
    po_details_data = po_details_data.filter(element => element != null);
    if (po_details_data.length == 0) {
        setRadioReadonly('input[name*="po_type_id"]', false);
    }
    jQuery('#PurchaseOrderDetailTable tbody').empty();
    calculateTotalAmount();
    fillPurchaseOrderDetailTable();
}


// edit time fill table start
function fillPurchaseOrderDetailTable() {
    let thisModal = jQuery('#PurchaseOrderDetailsModal');
    if (po_details_data.length > 0) {
        var tblHtml = '';
        for (let key in po_details_data) {
            let formIndx = po_details_data.indexOf(po_details_data[key]);
            var pod_item_id = po_details_data[key].pod_item_id ? po_details_data[key].pod_item_id : '';
            var item_name = pod_item_id != '' ? thisModal.find('#pod_item_id option[value="' + pod_item_id + '"]').text() || po_details_data[key].item_name : '';
            // var pi_no = po_details_data[key].pi_no ? po_details_data[key].pi_no : '';
            // var pi_date = po_details_data[key].pi_date ? po_details_data[key].pi_date : '';
            var item_group = po_details_data[key].item_group != "" ? po_details_data[key].item_group : '';
            var main_group = po_details_data[key].main_group != "" ? po_details_data[key].main_group : '';
            var io_stock_qty = parseFloat(po_details_data[key].io_stock_qty || 0).toFixed(3);
            // var pend_pi_qty = po_details_data[key].pend_pi_qty != "" ? po_details_data[key].pend_pi_qty : '';
            var pod_po_qty = po_details_data[key].pod_po_qty ? parseFloat(po_details_data[key].pod_po_qty).toFixed(3) : parseFloat(0).toFixed(3);
            var pod_unit = po_details_data[key].pod_unit != "" ? po_details_data[key].pod_unit : '';
            var pod_rate_unit = po_details_data[key].pod_rate_unit ? parseFloat(po_details_data[key].pod_rate_unit).toFixed(2) : parseFloat(0).toFixed(2);
            var pod_amount = po_details_data[key].pod_amount ? parseFloat(po_details_data[key].pod_amount).toFixed(2) : parseFloat(0).toFixed(2);
            var pod_del_date = po_details_data[key].pod_del_date != "" ? po_details_data[key].pod_del_date : "";

            var pod_remark = po_details_data[key].pod_remark ? po_details_data[key].pod_remark : "";
            var in_use = po_details_data[key].in_use == true ? true : false;
            var unit = po_details_data[key].unit != "" ? po_details_data[key].unit : '';

            if (jQuery('#PurchaseOrderDetailTable tbody').find('#noDetails').length > 0) {
                jQuery('#PurchaseOrderDetailTable tbody').empty();
            }
            tblHtml += `<tr>`;
            tblHtml += `<td>`;
            tblHtml += in_use == true ? DetailsActionDropdown('editPurchaseOrderDetails') : DetailsActionDropdown('editPurchaseOrderDetails', 'removePurchaseOrderDetails');
            tblHtml += ` <input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;
            // tblHtml += `<td>${pi_no}</td>`;
            // tblHtml += `<td>${pi_date}</td>`;
            tblHtml += `<td>${item_name}</td>`;
            tblHtml += `<td>${item_group}</td>`;
            tblHtml += `<td>${main_group}</td>`;
            tblHtml += `<td>${io_stock_qty}</td>`;
            // tblHtml += `<td>${pend_pi_qty}</td>`;
            tblHtml += `<td>${pod_po_qty ?? ''}</td>`;
            tblHtml += `<td>${unit ?? ''}</td>`;
            tblHtml += `<td>${pod_rate_unit ?? ''}</td>`;
            tblHtml += `<td class="amount">${pod_amount ?? ''}</td>`;
            tblHtml += `<td>${pod_del_date ?? ''}</td>`;
            tblHtml += `<td>${pod_remark}</td>`;
            tblHtml += `</tr>`;

        }
        jQuery('#PurchaseOrderDetailTable tbody').empty().append(tblHtml);
    }
    calculateTotalAmount();
}


jQuery('#PurchaseOrderDetailsModal').on('change', '#pod_po_qty, #pod_rate_unit', function () {
    let qty = parseFloat(jQuery('#pod_po_qty').val()) || 0;
    let rate = parseFloat(jQuery('#pod_rate_unit').val()) || 0;
    let amount = qty * rate;

    // Format to 3 decimal places (optional)
    amount = amount.toFixed(2);

    jQuery('#pod_amount').val(amount);

});

jQuery('#commonPurchaseOrderForm').find('#po_sequence').on('change', function () {
    checkSequence();
});
function checkSequence() {

    let thisForm = jQuery('#commonPurchaseOrderForm');
    let val = thisForm.find('#po_sequence').val();

    if (val != "") {

        if (val > 0 == false) {
            toastr.error('Enter Valid Purchase Order No.');
            jQuery('#po_sequence').parent().parent().parent('div.control-group').addClass('error');
            jQuery('#po_sequence').focus();
            jQuery('#po_sequence').val('');

        } else {
            jQuery('#po_sequence').addClass('file-loader');
            jQuery('#po_sequence').parent().parent().parent('div.control-group').removeClass('error');

            var urL = "check-purchase_order_number_duplication?for=add&po_sequence=" + val;

            var formId = jQuery('#commonPurchaseOrderForm').find('input[name="id"]').val();

            if (formId !== undefined) { //if form is edit
                urL = "check-purchase_order_number_duplication?for=edit&po_sequence=" + val + "&id=" + formId;
            }

            jQuery.ajax({
                url: urL,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    jQuery('#po_sequence').removeClass('file-loader');
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        jQuery('#commonPurchaseOrderForm #po_sequence').val('');
                        const input = document.getElementById('po_sequence'); input?.focus();
                    } else {
                        jQuery('#commonPurchaseOrderForm #po_number').val(data.latest_no);
                        jQuery('#commonPurchaseOrderForm #po_sequence').val(val);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#po_sequence').removeClass('file-loader');
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
        jQuery('#po_number').val('');
        jQuery('#po_sequence').val('');
    }

}
jQuery('#po_supplier_id , #bill_to_location_id').on('change.select2 change', function () {
    $sup_state = jQuery("#po_supplier_id").find('option:selected').data('state-id');
    $bill_state = jQuery("#bill_to_location_id").find('option:selected').data('state_id');

    if ($sup_state != undefined && $bill_state != undefined && $sup_state == $bill_state) {
        jQuery('input[name="gst_type_fix_id"][value="1"]').prop('checked', true).trigger('change');
    } else if ($sup_state == undefined && $bill_state == undefined) {
        jQuery('input[name="gst_type_fix_id"][value="3"]').prop('checked', true).trigger('change');
    } else {
        jQuery('input[name="gst_type_fix_id"][value="2"]').prop('checked', true).trigger('change');
    }
});

jQuery('input[name="gst_type_fix_id"]').on('change', function () {
    manageGstType();
});
function manageGstType() {
    var thisForm = jQuery('#commonPurchaseOrderForm');
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
    var thisForm = jQuery('#commonPurchaseOrderForm');
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
    var thisForm = jQuery('#commonPurchaseOrderForm');
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
    var thisForm = jQuery("#commonPurchaseOrderForm");

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

jQuery('input[name*="po_type_id"]').on('change', function () {
    if (jQuery(this).val() == 'From Indent') {
        jQuery('#pending_btn').prop('disabled', false);
        jQuery('.toggleButton').prop('disabled', true);
        GetPendingPIforPO();
    } else {
        jQuery('#pending_btn').prop('disabled', true);
        jQuery('.toggleButton').prop('disabled', false);
    }
});

function GetPendingPIforPO() {
    var thisModal = jQuery('#PurchaseOrderPendingModal');
    var thisForm = jQuery('#commonPurchaseOrderForm');

    jQuery.ajax({
        url: 'get-purchase_indent_list_for_purchase_order',
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                if (data.response_code == 1 && data.pi_data.length > 0) {
                    // new code
                    var usedParts = [];
                    var totalDisb = 0;
                    var found = 0;

                    thisForm.find('#pendingPIDataTable tbody input[name="form_indx"]').each(function (indx) {
                        let frmIndx = jQuery(this).val();

                        let jbEorkOrderId = pi_data[frmIndx].pod_pid_id;
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
                    var tblHtml = ``;
                    if (data.pi_data.length > 0 && !jQuery.isEmptyObject(data.pi_data)) {

                        var formIdblank = jQuery('#commonPurchaseOrderForm').find('#id').val();
                        // if (formIdblank != "" && formIdblank != undefined) {
                        //     jQuery('#pending_btn').prop('disabled', true);
                        // } else {
                        //     jQuery('#pending_btn').prop('disabled', false);
                        // }
                        found = 1;
                        for (let idx in data.pi_data) {
                            var inUse = isUsed(data.pi_data[idx].pod_pid_id);
                            var in_use = data.pi_data[idx].in_use == true ? 'readonly' : '';
                            totalEntry++;
                            tblHtml += `
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="pod_pid_id[]" class="simple-check  ${inUse ? 'in-use' : ''}" id="pod_pid_ids_${data.pi_data[idx].pod_pid_id}" value="${data.pi_data[idx].pod_pid_id}" ${inUse ? 'checked' : ''} ${in_use} onchange="checkesCheckboxFrist(this)"/>
                                                    </td>
                                                    <td>${data.pi_data[idx].pi_no != null ? data.pi_data[idx].pi_no : ''}</td>
                                                    <td>${data.pi_data[idx].pi_date != null ? data.pi_data[idx].pi_date : ''}</td>
                                                    <td>${data.pi_data[idx].location_name != null ? data.pi_data[idx].location_name : ''}</td>
                                                    <td>${data.pi_data[idx].item_name != null ? data.pi_data[idx].item_name : ''}</td>
                                                    <td>${data.pi_data[idx].item_group != null ? data.pi_data[idx].item_group : ''}</td>
                                                    <td>${data.pi_data[idx].main_group != null ? data.pi_data[idx].main_group : ''}</td>
                                                    <td>${data.pi_data[idx].pend_pi_qty != null ? parseFloat(data.pi_data[idx].pend_pi_qty).toFixed(3) : ''}</td>
                                                    <td>${data.pi_data[idx].unit != null ? data.pi_data[idx].unit : ''}</td>
                                                    <td>${data.pi_data[idx].remark != null ? data.pi_data[idx].remark : ''}</td>
                                                    <td>${data.pi_data[idx].indent_by != null ? data.pi_data[idx].indent_by : ''}</td>
                                                </tr>`;
                        }
                    } else {
                        tblHtml += `<tr class="centeralign" id="noPendingPo">
                                    <td colspan="12">No Pending Available</td>
                                </tr>`;
                        jQuery('#pending_btn').prop('disabled', true);
                    }

                    var $table = jQuery("#PurchaseOrderPendingModal").find('#pendingPIDataTable');
                    if (jQuery.fn.DataTable.isDataTable($table)) {
                        $table.DataTable().clear().destroy();
                    }
                    jQuery('#pendingPIDataTable tbody').empty().append(tblHtml);
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
                    $new.on('draw', function () {
                        let table = $('#pendingPIDataTable').DataTable();

                        $.each(selectedRows, function (pid, row) {
                            $(table.table().body()).prepend(row); // move to top
                        });
                    });
                } else {
                    jQuery('.toggleModalBtn').prop('disabled', true);
                }
                // jQuery('.toggleModalBtn').prop('disabled', false);
            } else {
                jQuery('.toggleModalBtn').prop('disabled', true);
                toastr.error(data.response_message);
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
jQuery('#checkall-pi_data').click(function () {
    if (jQuery(this).is(':checked')) {
        jQuery("#pendingPIDataTable").find("[id^='pod_pid_ids_']:not(.in-use)").prop('checked', true).trigger('change');
        jQuery("#pendingPIDataTable").find("[id^='pod_pid_ids_']").prop('checked', true).trigger('change');
    } else {
        jQuery("#pendingPIDataTable").find("[id^='pod_pid_ids_']:not(.in-use)").prop('checked', false).trigger('change');
        jQuery("#pendingPIDataTable").find("[id^='pod_pid_ids_']").prop('checked', false).trigger('change');
    }

});

$('#addPendigPoForm').on('submit', function (e) {

    e.preventDefault();

    jQuery('#full-page-loader')
        .removeClass('hidden-loader')
        .addClass('loader-progress-whole-page');

    jQuery('#PurchaseOrderPendingModal')
        .find('#submitbtn')
        .prop('disabled', true);

    let chkArr = [];
    var formId = jQuery('#commonPurchaseOrderForm').find('input[name="id"]').val();

    jQuery("#addPendigPoForm")
        .find("[id^='pod_pid_ids_']:checked")
        .each(function () {
            chkArr.push(jQuery(this).val());
        });

    // No checkbox selected
    if (chkArr.length === 0) {
        toastr.error('Select Purchase Indent From Pending.');

        jQuery('#full-page-loader')
            .removeClass('loader-progress-whole-page')
            .addClass('hidden-loader');

        jQuery('#PurchaseOrderPendingModal')
            .find('#submitbtn')
            .prop('disabled', false);

        return;
    }

    if (formId != undefined && formId != "") {
        var pend_url = "get-purchase_indent_part_data_purchase_order?id=" + formId;
    } else {
        var pend_url = "get-purchase_indent_part_data_purchase_order";
    }

    jQuery.ajax({
        url: pend_url,
        type: 'GET',
        data: { pod_pid_ids: chkArr.join(',') },
        dataType: 'json',
        success: function (data) {

            if (data.response_code == 1) {

                if (data.po_details_data && data.po_details_data.length > 0) {
                    po_details_data = [];
                    for (let ind in data.po_details_data) {
                        po_details_data.push(data.po_details_data[ind]);
                    }
                    // po_details_data.push(...data.inq_data);
                    fillPurchaseOrderDetailTable(data.po_details_data);

                } else {
                    fillPurchaseOrderDetailTable([]);
                }

                jQuery("#PurchaseOrderPendingModal").modal('hide');
                jQuery('input[name*="po_type_id"]').addClass('skip-tab');
                setRadioReadonly('input[name*="po_type_id"]', true);
            } else {
                fillPurchaseOrderDetailTable([]);
            }

            jQuery('#PurchaseOrderPendingModal')
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
                // console.log(jqXHR.responseText);
            }

            jQuery('#PurchaseOrderPendingModal')
                .find('#submitbtn')
                .prop('disabled', false);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');
        }
    });
});


jQuery('#PurchaseOrderPendingModal').on('show.bs.modal', function (e) {
    var dt = jQuery('#pendingPIDataTable').DataTable();
    fixDataTableColumnsUntilAdjusted(dt);
    var usedParts = [];
    if (po_details_data && po_details_data.length > 0) {
        po_details_data.forEach(function (item) {
            if (item.pod_pid_id) {
                usedParts.push(Number(item.pod_pid_id));
            }
        });
    }
    function isUsed(pjId) {
        if (usedParts.includes(Number(pjId))) {
            return true;
        }
        return false;
    }
    var totalEntry = 0;
    jQuery('#pendingPIDataTable tbody tr').each(function (indx) {
        totalEntry++;
        var checkField = jQuery(this).find('input[name="pod_pid_id[]"]');
        var partId = jQuery(checkField).val();
        var inUse = isUsed(partId);

        if (formId == undefined) {
            if (po_details_data.length > 0) {
                var inUse = isUsed(partId);
            } else {
                var inUse = false;
            }
        } else {
            var inUse = isUsed(partId);
        }

        if (inUse) {
            jQuery(checkField).prop('checked', true);

        } else {
            jQuery(checkField).prop('checked', false);
        }
    });
});
jQuery('#PurchaseOrderPendingModal').on('hide.bs.modal', function (e) {
    selectedRows = {};
});

function checkesCheckboxFrist($this) {
    var $row = jQuery($this).closest('tr');
    var pod_pid_id = jQuery($this).val();
    if (jQuery($this).is(':checked')) {
        selectedRows[pod_pid_id] = $row;
    } else {
        delete selectedRows[pod_pid_id];
    }
}


$(document).ready(function () {
    $('#SupplierModal').on('hidden.bs.modal', function (e) {
        e.stopPropagation();
        setTimeout(() => {
            const poId = $('#PurchaseOrderModal #id').val();
            const $cust = $('#po_supplier_id');
            const $kind = $('#po_kind_attn_id');
            const isEdit = (poId !== '' && poId !== undefined);
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

jQuery('#commonPurchaseOrderForm').on('change', '#po_date', function () {
    var poDateValue = jQuery(this).val().trim();
    if (!poDateValue) return;

    function parseDate(dateStr) {
        if (!dateStr) return null;
        var parts = dateStr.split("/");
        return new Date(parts[2], parts[1] - 1, parts[0]);
    }

    var poDate = parseDate(poDateValue);
    if (!poDate) return;

    if (po_details_data && po_details_data.length > 0) {
        var invalid = false;
        $.each(po_details_data, function (index, row) {
            var selectedDate = parseDate(row.pod_del_date);
            if (poDate && selectedDate < poDate) {
                invalid = true;
                return false;
            }
        });

        if (invalid) {
            toastr.error('Del. Date Must Be Greater Than PO Date');
        }
    }
});