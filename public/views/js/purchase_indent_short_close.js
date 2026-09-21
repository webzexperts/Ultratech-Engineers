let formId = jQuery('#commonPurchaseIndentShortCloseForm').find('input:hidden[name="id"]').val();

var purchase_indent_sc_data = [];
let selectedRows = {};
$.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {

    var row = $('#PurchaseIndentShortCloseDataTable').DataTable().row(dataIndex).node();
    var checkbox = $(row).find("input[type='checkbox']");

    if (checkbox.is(':checked')) {
        return true; // always show checked rows
    }

    return true; // default filtering ચાલુ રાખવું હોય તો અહીં condition મૂકી શકો
});
jQuery('#resetbtn').on('click', function () {
    purchase_indent_sc_data = [];
    selectedRows = {};
    jQuery('#PurchaseIndentShortCloseDataTable tbody').empty();

    var formId = jQuery('#PurchaseIndentShortCloseModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonPurchaseIndentShortCloseForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        setTimeout(function () {
            const $frDate = jQuery('#commonPurchaseIndentShortCloseForm #pisc_date');
            $frDate.trigger('focus');
            setTimeout(function () {
                if ($frDate.hasClass('trans-date-picker')) {
                    $frDate.datepicker('hide');
                }
            }, 0);
        }, 150);
        PendingPurchaseIndentShortClose();
    }
});

jQuery('#PurchaseIndentShortCloseModal').on('shown.bs.modal', function () {
    var formId = jQuery('#commonPurchaseIndentShortCloseForm').find('input[name="id"]').val();
    var hasAccess = jQuery('#commonPurchaseIndentShortCloseForm').find('#has_access').val();

    if (formId == "") {
        PendingPurchaseIndentShortClose();
    }

});

function PendingPurchaseIndentShortClose() {
    jQuery('#pisc_date').val(currentDate);

    jQuery.ajax({
        url: "get-pending_purchase_indent_sc",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                if (data.purchase_indent_sc_data.length > 0 && !jQuery.isEmptyObject(data.purchase_indent_sc_data)) {
                    for (let ind in data.purchase_indent_sc_data) {
                        purchase_indent_sc_data.push(data.purchase_indent_sc_data[ind]);
                    }
                    fillPurchaseIndentShortCloseTable();
                }
            }
        }
    });
}

function fillPurchaseIndentShortCloseTable() {
    let tblHtml = ``;
    if (purchase_indent_sc_data.length > 0) {
        for (let key in purchase_indent_sc_data) {

            var formIndx = purchase_indent_sc_data.indexOf(purchase_indent_sc_data[key]);

            var pi_no = purchase_indent_sc_data[key].pi_no ? purchase_indent_sc_data[key].pi_no : "";
            var pi_date = purchase_indent_sc_data[key].pi_date ? purchase_indent_sc_data[key].pi_date : "";

            var location_name = purchase_indent_sc_data[key].location_name ? purchase_indent_sc_data[key].location_name : "";

            var from_location = purchase_indent_sc_data[key].from_location ? purchase_indent_sc_data[key].from_location : "";

            var item_name = purchase_indent_sc_data[key].item_name ? purchase_indent_sc_data[key].item_name : "";

            var item_group = purchase_indent_sc_data[key].item_group ? purchase_indent_sc_data[key].item_group : null;
            var main_group = purchase_indent_sc_data[key].main_group ? purchase_indent_sc_data[key].main_group : null;
            var scQtyClass = (main_group === 'Industrial X-Ray Films') ? 'isNumberKeyNotDot' : 'isNumberKey';
            var scQtyBlur = (main_group === 'Industrial X-Ray Films') ? 'formatPoints(this,0)' : 'formatPoints(this,3)';
            var indent_qty = purchase_indent_sc_data[key].indent_qty ? purchase_indent_sc_data[key].indent_qty : null;
            var unit = purchase_indent_sc_data[key].unit ? purchase_indent_sc_data[key].unit : "";
            var remark = purchase_indent_sc_data[key].remark ? purchase_indent_sc_data[key].remark : "";
            var indent_by = purchase_indent_sc_data[key].indent_by ? purchase_indent_sc_data[key].indent_by : "";

            var pending_qty = purchase_indent_sc_data[key].pending_qty ? parseFloat(purchase_indent_sc_data[key].pending_qty).toFixed(3) : "";
            var pod_po_qty = purchase_indent_sc_data[key].pod_po_qty ? parseFloat(purchase_indent_sc_data[key].pod_po_qty).toFixed(3) : "";



            tblHtml += `<tr>`;
            tblHtml += `<td>         
                <input class="checkbox-filter-remove" type="checkbox" name="pid_id[]" class="simple-check" id="pid_ids_${purchase_indent_sc_data[key].pid_id}" value="${purchase_indent_sc_data[key].pid_id}" onchange="manageQtyfield(this)"/>
                <input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;
            tblHtml += `<td>${pi_no}</td>`;
            tblHtml += `<td>${pi_date}</td>`;
            tblHtml += `<td>${from_location}</td>`;
            tblHtml += `<td>${location_name}</td>`;
            tblHtml += `<td>${item_name}</td>`;
            tblHtml += `<td>${item_group}</td>`;
            tblHtml += `<td>${main_group}</td>`;
            tblHtml += `<td>${parseFloat(indent_qty).toFixed(3) ?? parseFloat(0).toFixed(3)}</td>`;
            tblHtml += `<td>${unit}</td>`;
            tblHtml += `<td>${pending_qty}</td>`;
            tblHtml += `<td class="position-relative">
                            <input type="text" class="form-control ${scQtyClass} remove_filters_short_qty" data-pending="${pending_qty}" max="${pending_qty}" id="pisc_sc_qty_${purchase_indent_sc_data[key].pid_id}" name="pisc_sc_qty[]" onblur="${scQtyBlur}" disabled required>
                            <div class="invalid-tooltip">
                                Enter Short Close Qty.
                            </div>
                        </td>`;
            tblHtml += `<td class="position-relative">
                            <select class="js-example-basic-single form-control remove_filters_short_qty"
                                name="pisc_sc_reason_id[]" id="pisc_sc_reason_id_${purchase_indent_sc_data[key].pid_id}" disabled required>
                                ${reasonOptions}
                            </select>
                            <div class="invalid-tooltip">
                                Select Reason.
                            </div>
                        </td>`;
            tblHtml += `<td>${remark}</td>`;
            tblHtml += `<td>${indent_by}</td>`;
            tblHtml += `</tr>`;
        }

        jQuery('#PurchaseIndentShortCloseDataTable tbody').empty();
        jQuery('#PurchaseIndentShortCloseDataTable tbody').append(tblHtml);

        var $table = jQuery("#PurchaseIndentShortCloseModal").find('#PurchaseIndentShortCloseDataTable');
        if (jQuery.fn.DataTable.isDataTable($table)) {
            $table.DataTable().clear().destroy();
        }
        jQuery('#PurchaseIndentShortCloseDataTable tbody').empty().append(tblHtml);

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
            let table = $('#PurchaseIndentShortCloseDataTable').DataTable();

            $.each(selectedRows, function (pid, row) {
                $(table.table().body()).prepend(row); // move to top
            });

            jQuery('#PurchaseIndentShortCloseModal .js-example-basic-single').select2({
                width: '100%',
                dropdownParent: jQuery('#PurchaseIndentShortCloseModal')
            });
        });
    }
}

jQuery('#checkall-purchase_indent_data').click(function () {
    if (jQuery(this).is(':checked')) {
        jQuery("#PurchaseIndentShortCloseDataTable").find("[id^='pid_ids_']:not(.in-use)").prop('checked', true).trigger('change');
        jQuery("#PurchaseIndentShortCloseDataTable").find("[id^='pid_ids_']").prop('checked', true).trigger('change');
        jQuery("#PurchaseIndentShortCloseDataTable").find("[id^='pisc_sc_qty_']").prop('disabled', false).prop('required', true);
        jQuery("#PurchaseIndentShortCloseDataTable").find("[id^='pisc_sc_reason_id_']").prop('disabled', false).prop('required', true);
    } else {
        jQuery("#PurchaseIndentShortCloseDataTable").find("[id^='pid_ids_']:not(.in-use)").prop('checked', false).trigger('change');
        jQuery("#PurchaseIndentShortCloseDataTable").find("[id^='pid_ids_']").prop('checked', false).trigger('change');
        jQuery("#PurchaseIndentShortCloseDataTable").find("[id^='pisc_sc_qty_']").prop('disabled', true).prop('required', false);
        jQuery("#PurchaseIndentShortCloseDataTable").find("[id^='pisc_sc_reason_id_']").prop('disabled', true).prop('required', false);
    }

});

function manageQtyfield($this) {

    var $row = jQuery($this).closest('tr');
    var pid = jQuery($this).val();

    if (jQuery($this).is(':checked')) {

        selectedRows[pid] = $row; // store row

    } else {

        delete selectedRows[pid]; // remove

    }

    // તમારું existing code ચાલુ રાખો
    var qtyField = $row.find('input[name="pisc_sc_qty[]"]');
    var reasonField = $row.find('select[name="pisc_sc_reason_id[]"]');

    if (jQuery($this).is(':checked')) {
        qtyField.prop('disabled', false).prop('required', true);
        reasonField.prop('disabled', false).prop('required', true);

        var pending = qtyField.attr('data-pending');
        if (qtyField.hasClass('isNumberKeyNotDot')) {
            qtyField.val(parseFloat(pending).toFixed(0));
        } else {
            qtyField.val(pending);
        }
    } else {
        qtyField.val('').prop('disabled', true).prop('required', false);
        reasonField.val('').trigger('change').prop('disabled', true).prop('required', false);
    }
}



$('#commonPurchaseIndentShortCloseForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#PurchaseIndentShortCloseModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;
    var dateValue = document.getElementById("pisc_date").value.trim();
    if (!isValidDate(dateValue)) {
        toastr.error("Please Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PurchaseIndentShortCloseModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PurchaseIndentShortCloseModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PurchaseIndentShortCloseModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    jQuery('#PurchaseIndentShortCloseModal').find('#submitbtn').prop('disabled', true);

    var formId = jQuery('#PurchaseIndentShortCloseModal').find('#commonPurchaseIndentShortCloseForm').find('#id').val();
    var formUrl = "store-purchase_indent_short_close";

    purchase_indent_sc_data = [];
    var index = 0;
    var hasError = false;
    jQuery('#PurchaseIndentShortCloseDataTable tbody tr').each(function (e) {
        var pid_id = jQuery(this).find('input[name="pid_id[]"]');
        if (jQuery(pid_id).is(':checked')) {
            pid_id = jQuery(pid_id).val();
            pisc_sc_qty = jQuery(this).find('input[name="pisc_sc_qty[]"]').val();
            pisc_sc_reason_id = jQuery(this).find('select[name="pisc_sc_reason_id[]"]').val();
            purchase_indent_sc_data[index] = { 'pid_id': pid_id, 'pisc_sc_qty': pisc_sc_qty, 'pisc_sc_reason_id': pisc_sc_reason_id };
            let qtyField = $(this).find('input[name="pisc_sc_qty[]"]');
            let max = parseFloat(qtyField.attr('max')).toFixed(3) || parseFloat(0).toFixed(3);
            let val = parseFloat(qtyField.val()) || 0;

            if (val > max) {
                toastr.error("Short Close Qty. Cannot Be Greater Than " + max + "");
                hasError = true;
                return false;
            }
            if (val < 0.001) {
                toastr.error('Short Close Qty. greater than 0.001.');
                hasError = true;
                return false;
            }
            index++;
        }
    });
    if (hasError) {
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#submitbtn').prop('disabled', false);
        return;
    }


    if (purchase_indent_sc_data.length > 0 && !jQuery.isEmptyObject(purchase_indent_sc_data)) {
        var data = new FormData(form);
        data.append('purchase_indent_sc_data', JSON.stringify(purchase_indent_sc_data ?? []));
        data.append('_token', $('meta[name="csrf-token"]').attr('content'));
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
                        jQuery('#PurchaseIndentShortCloseModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formId == undefined || formId == "") {
                        function nextFn() {
                            document.getElementById("commonPurchaseIndentShortCloseForm").reset();
                            const form = document.getElementById("commonPurchaseIndentShortCloseForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }

                            setTimeout(function () {
                                const $frDate = jQuery('#commonPurchaseIndentShortCloseForm #pisc_date');
                                $frDate.trigger('focus');
                                setTimeout(function () {
                                    if ($frDate.hasClass('trans-date-picker')) {
                                        $frDate.datepicker('hide');
                                    }
                                }, 0);
                            }, 150);
                            $("#pisc_sc_regret_reason_id").val('').trigger('change');
                            purchase_indent_sc_data = [];
                            selectedRows = {};
                            jQuery('#PurchaseIndentShortCloseDataTable tbody').empty();
                            jQuery("#PurchaseIndentShortCloseDataTable").find("[id^='pid_ids_']:not(.in-use)").prop('checked', false).trigger('change');
                            jQuery("#PurchaseIndentShortCloseDataTable").find("[id^='pid_ids_']").prop('checked', false).trigger('change');
                            jQuery("#PurchaseIndentShortCloseDataTable").find("[id^='pisc_sc_qty_']").prop('disabled', true).prop('required', false);
                            jQuery("#PurchaseIndentShortCloseDataTable").find("[id^='pisc_sc_reason_id_']").prop('disabled', true).prop('required', false);
                            PendingPurchaseIndentShortClose();

                        }
                        toastSuccess(data.response_message, nextFn);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#PurchaseIndentShortCloseModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#PurchaseIndentShortCloseModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#PurchaseIndentShortCloseModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#PurchaseIndentShortCloseModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#PurchaseIndentShortCloseModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        var formId = jQuery('#PurchaseIndentShortCloseModal').find('#commonPurchaseIndentShortCloseForm').find('#id').val();
        if (formId != undefined && formId != "") {
            jQuery('#PurchaseIndentShortCloseModal').find('#submitbtn').prop('disabled', true);
            toastr.error('You can not update Quotation.');
        }
        toastr.error('Please Add At Least One Short Close Details.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PurchaseIndentShortCloseModal').find('#submitbtn').prop('disabled', false);
    }
});

jQuery('#PurchaseIndentShortCloseModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#PurchaseIndentShortCloseModal');
    thisModal.find("#id").val("");
    purchase_indent_sc_data = [];
    selectedRows = {};
    jQuery('#commonPurchaseIndentShortCloseForm').trigger("reset");
    thisModal.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).prop('checked', false);
    });
});