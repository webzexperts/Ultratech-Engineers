let formId = jQuery('#commonPOShortCloseForm').find('input:hidden[name="id"]').val();

var po_sc_data = [];
let selectedRows = {};
$.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {

    var row = $('#POShortCloseDataTable').DataTable().row(dataIndex).node();
    var checkbox = $(row).find("input[type='checkbox']");
    if (checkbox.is(':checked')) {
        return true; 
    }
    return true; 
});
jQuery('#resetbtn').on('click', function () {
    po_sc_data = [];
    selectedRows = {};
    jQuery('#POShortCloseDataTable tbody').empty();

    var formId = jQuery('#POShortCloseModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonPOShortCloseForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        setTimeout(function () {
            const $frDate = jQuery('#commonPOShortCloseForm #po_sc_date');
            $frDate.trigger('focus');
            setTimeout(function () {
                if ($frDate.hasClass('trans-date-picker')) {
                    $frDate.datepicker('hide');
                }
            }, 0);
        }, 150);
        PendingPurchaseOrderShortClose();
    }
});

jQuery('#POShortCloseModal').on('show.bs.modal', function () {
    var formId = jQuery('#commonPOShortCloseForm').find('input[name="id"]').val();
    var hasAccess = jQuery('#commonPOShortCloseForm').find('#has_access').val();

    if (formId == "") {
        PendingPurchaseOrderShortClose();
    }

});

function PendingPurchaseOrderShortClose() {
    jQuery('#po_sc_date').val(currentDate);

    jQuery.ajax({
        url: "get-pending_po_sc",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                if (data.po_sc_data.length > 0 && !jQuery.isEmptyObject(data.po_sc_data)) {
                    for (let ind in data.po_sc_data) {
                        po_sc_data.push(data.po_sc_data[ind]);
                    }
                    fillPOShortCloseTable();
                }
            }
        }
    });
}

function fillPOShortCloseTable() {
    let tblHtml = ``;
    if (po_sc_data.length > 0) {
        for (let key in po_sc_data) {

            var formIndx = po_sc_data.indexOf(po_sc_data[key]);

            var po_type_id = po_sc_data[key].po_type_id ? po_sc_data[key].po_type_id : "";
            var po_number = po_sc_data[key].po_number ? po_sc_data[key].po_number : "";
            var po_date = po_sc_data[key].po_date ? po_sc_data[key].po_date : "";
            var supplier_name = po_sc_data[key].supplier_name ? po_sc_data[key].supplier_name : "";
            var ref_no_date = po_sc_data[key].ref_no_date ? po_sc_data[key].ref_no_date : "";

            var bill_to = po_sc_data[key].bill_to ? po_sc_data[key].bill_to : "";
            var ship_to = po_sc_data[key].ship_to ? po_sc_data[key].ship_to : "";

            var item_name = po_sc_data[key].item_name ? po_sc_data[key].item_name : "";

            var item_group = po_sc_data[key].item_group ? po_sc_data[key].item_group : null;
            var main_group = po_sc_data[key].main_group ? po_sc_data[key].main_group : null;
            var scQtyClass = (main_group === 'Industrial X-Ray Films') ? 'isNumberKeyNotDot' : 'isNumberKey';
            var scQtyBlur = (main_group === 'Industrial X-Ray Films') ? 'formatPoints(this,0)' : 'formatPoints(this,3)';
            var unit = po_sc_data[key].unit ? po_sc_data[key].unit : "";
            var pod_del_date = po_sc_data[key].pod_del_date ? po_sc_data[key].pod_del_date : "";
            var pod_remark = po_sc_data[key].pod_remark ? po_sc_data[key].pod_remark : "";
            var prepared_by = po_sc_data[key].prepared_by ? po_sc_data[key].prepared_by : "";

            var pending_qty = po_sc_data[key].pending_qty ? parseFloat(po_sc_data[key].pending_qty).toFixed(3) : "";
            var pod_po_qty = po_sc_data[key].pod_po_qty ? parseFloat(po_sc_data[key].pod_po_qty).toFixed(3) : "";



            tblHtml += `<tr>`;
            tblHtml += `<td>         
                <input class="checkbox-filter-remove" type="checkbox" name="pod_id[]" class="simple-check" id="pod_ids_${po_sc_data[key].pod_id}" value="${po_sc_data[key].pod_id}" onchange="manageQtyfield(this)"/>
                <input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;
            tblHtml += `<td>${po_type_id}</td>`;
            tblHtml += `<td>${po_number}</td>`;
            tblHtml += `<td>${po_date}</td>`;
            tblHtml += `<td>${supplier_name}</td>`;
            tblHtml += `<td>${ref_no_date}</td>`;
            tblHtml += `<td>${bill_to}</td>`;
            tblHtml += `<td>${ship_to}</td>`;
            tblHtml += `<td>${item_name}</td>`;
            tblHtml += `<td>${item_group}</td>`;
            tblHtml += `<td>${main_group}</td>`;
            tblHtml += `<td>${parseFloat(pod_po_qty).toFixed(3) ?? parseFloat(0).toFixed(3)}</td>`;
            tblHtml += `<td>${unit}</td>`;
            tblHtml += `<td>${pending_qty}</td>`;
            tblHtml += `<td class="position-relative">
                            <input type="text" class="form-control ${scQtyClass} remove_filters_short_qty" data-pending="${pending_qty}" max="${pending_qty}" id="po_sc_qty_${po_sc_data[key].pod_id}" name="po_sc_qty[]" onblur="${scQtyBlur}" disabled required>
                            <div class="invalid-tooltip">
                                Enter Short Close Qty.
                            </div>
                        </td>`;
            tblHtml += `<td class="position-relative">
                            <select class="js-example-basic-single form-control remove_filters_short_qty"
                                name="po_sc_regret_reason_id[]" id="po_sc_regret_reason_id_${po_sc_data[key].pod_id}" disabled required>
                                ${reasonOptions}
                            </select>
                            <div class="invalid-tooltip">
                                Select Reason.
                            </div>
                        </td>`;
            tblHtml += `<td>${pod_del_date}</td>`;
            tblHtml += `<td>${pod_remark}</td>`;
            tblHtml += `<td>${prepared_by}</td>`;
            tblHtml += `</tr>`;
        }

        jQuery('#POShortCloseDataTable tbody').empty();
        jQuery('#POShortCloseDataTable tbody').append(tblHtml);

        var $table = jQuery("#POShortCloseModal").find('#POShortCloseDataTable');
        if (jQuery.fn.DataTable.isDataTable($table)) {
            $table.DataTable().clear().destroy();
        }
        jQuery('#POShortCloseDataTable tbody').empty().append(tblHtml);

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
            let table = $('#POShortCloseDataTable').DataTable();

            $.each(selectedRows, function (pid, row) {
                $(table.table().body()).prepend(row); // move to top
            });

            jQuery('#POShortCloseModal .js-example-basic-single').select2({
                width: '100%',
                dropdownParent: jQuery('#POShortCloseModal')
            });
        });
    }
}

jQuery('#checkall-po_data').click(function () {
    if (jQuery(this).is(':checked')) {
        jQuery("#POShortCloseDataTable").find("[id^='pod_ids_']:not(.in-use)").prop('checked', true).trigger('change');
        jQuery("#POShortCloseDataTable").find("[id^='pod_ids_']").prop('checked', true).trigger('change');
        jQuery("#POShortCloseDataTable").find("[id^='po_sc_qty_']").prop('disabled', false).prop('required', true);
        jQuery("#POShortCloseDataTable").find("[id^='po_sc_regret_reason_id_']").prop('disabled', false).prop('required', true);
    } else {
        jQuery("#POShortCloseDataTable").find("[id^='pod_ids_']:not(.in-use)").prop('checked', false).trigger('change');
        jQuery("#POShortCloseDataTable").find("[id^='pod_ids_']").prop('checked', false).trigger('change');
        jQuery("#POShortCloseDataTable").find("[id^='po_sc_qty_']").prop('disabled', true).prop('required', false);
        jQuery("#POShortCloseDataTable").find("[id^='po_sc_regret_reason_id_']").prop('disabled', true).prop('required', false);
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
    var qtyField = $row.find('input[name="po_sc_qty[]"]');
    var reasonField = $row.find('select[name="po_sc_regret_reason_id[]"]');

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



$('#commonPOShortCloseForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#POShortCloseModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;
    var dateValue = document.getElementById("po_sc_date").value.trim();
    if (!isValidDate(dateValue)) {
        toastr.error("Please Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#POShortCloseModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#POShortCloseModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#POShortCloseModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    jQuery('#POShortCloseModal').find('#submitbtn').prop('disabled', true);

    var formId = jQuery('#POShortCloseModal').find('#commonPOShortCloseForm').find('#id').val();
    var formUrl = "store-po_short_close";

    po_sc_data = [];
    var index = 0;
    var hasError = false;
    jQuery('#POShortCloseDataTable tbody tr').each(function (e) {
        var pod_id = jQuery(this).find('input[name="pod_id[]"]');
        if (jQuery(pod_id).is(':checked')) {
            pod_id = jQuery(pod_id).val();
            po_sc_qty = jQuery(this).find('input[name="po_sc_qty[]"]').val();
            po_sc_regret_reason_id = jQuery(this).find('select[name="po_sc_regret_reason_id[]"]').val();
            po_sc_data[index] = { 'pod_id': pod_id, 'po_sc_qty': po_sc_qty, 'po_sc_regret_reason_id': po_sc_regret_reason_id };
            let qtyField = $(this).find('input[name="po_sc_qty[]"]');
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


    if (po_sc_data.length > 0 && !jQuery.isEmptyObject(po_sc_data)) {
        var data = new FormData(form);
        data.append('po_sc_data', JSON.stringify(po_sc_data ?? []));
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
                        jQuery('#POShortCloseModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formId == undefined || formId == "") {
                        function nextFn() {
                            document.getElementById("commonPOShortCloseForm").reset();
                            const form = document.getElementById("commonPOShortCloseForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }

                            setTimeout(function () {
                                const $frDate = jQuery('#commonPOShortCloseForm #po_sc_date');
                                $frDate.trigger('focus');
                                setTimeout(function () {
                                    if ($frDate.hasClass('trans-date-picker')) {
                                        $frDate.datepicker('hide');
                                    }
                                }, 0);
                            }, 150);
                            $("#pisc_sc_regret_reason_id").val('').trigger('change');
                            po_sc_data = [];
                            selectedRows = {};
                            jQuery('#POShortCloseDataTable tbody').empty();
                            jQuery("#POShortCloseDataTable").find("[id^='pod_ids_']:not(.in-use)").prop('checked', false).trigger('change');
                            jQuery("#POShortCloseDataTable").find("[id^='pod_ids_']").prop('checked', false).trigger('change');
                            jQuery("#POShortCloseDataTable").find("[id^='po_sc_qty_']").prop('disabled', true).prop('required', false);
                            jQuery("#POShortCloseDataTable").find("[id^='po_sc_regret_reason_id_']").prop('disabled', true).prop('required', false);
                            PendingPurchaseOrderShortClose();

                        }
                        toastSuccess(data.response_message, nextFn);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#POShortCloseModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#POShortCloseModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#POShortCloseModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#POShortCloseModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#POShortCloseModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        var formId = jQuery('#POShortCloseModal').find('#commonPOShortCloseForm').find('#id').val();
        if (formId != undefined && formId != "") {
            jQuery('#POShortCloseModal').find('#submitbtn').prop('disabled', true);
            toastr.error('You can not update Quotation.');
        }
        toastr.error('Please Add At Least One Short Close Details.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#POShortCloseModal').find('#submitbtn').prop('disabled', false);
    }
});


jQuery('#POShortCloseModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#POShortCloseModal');
    thisModal.find("#id").val("");
    po_sc_data = [];
    selectedRows = {};
    jQuery('#commonPOShortCloseForm').trigger("reset");
    thisModal.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).prop('checked', false);
    });
});