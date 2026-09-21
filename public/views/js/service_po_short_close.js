let formId = jQuery('#commonServicePOShortCloseForm').find('input:hidden[name="id"]').val();

var service_po_sc_data = [];
let selectedRows = {};
$.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {

    var row = $('#ServicePOShortCloseDataTable').DataTable().row(dataIndex).node();
    var checkbox = $(row).find("input[type='checkbox']");

    if (checkbox.is(':checked')) {
        return true; // always show checked rows
    }

    return true; // default filtering ચાલુ રાખવું હોય તો અહીં condition મૂકી શકો
});
jQuery('#resetbtn').on('click', function () {
    service_po_sc_data = [];
    selectedRows = {};
    jQuery('#ServicePOShortCloseDataTable tbody').empty();

    var formId = jQuery('#ServicePOShortCloseModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonServicePOShortCloseForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        setTimeout(function () {
            const $frDate = jQuery('#commonServicePOShortCloseForm #ser_po_sc_date');
            $frDate.trigger('focus');
            setTimeout(function () {
                if ($frDate.hasClass('trans-date-picker')) {
                    $frDate.datepicker('hide');
                }
            }, 0);
        }, 150);
        PendingServicePOShortClose();
    }
});

jQuery('#ServicePOShortCloseModal').on('shown.bs.modal', function () {
    var formId = jQuery('#commonServicePOShortCloseForm').find('input[name="id"]').val();
    var hasAccess = jQuery('#commonServicePOShortCloseForm').find('#has_access').val();

    if (formId == "") {
        PendingServicePOShortClose();
    }

});

function PendingServicePOShortClose() {
    jQuery('#ser_po_sc_date').val(currentDate);

    jQuery.ajax({
        url: "get-pending_service_po_sc",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                if (data.service_po_sc_data.length > 0 && !jQuery.isEmptyObject(data.service_po_sc_data)) {
                    for (let ind in data.service_po_sc_data) {
                        service_po_sc_data.push(data.service_po_sc_data[ind]);
                    }
                    fillServicePOShortCloseTable();
                }
            }
        }
    });
}

function fillServicePOShortCloseTable() {
    let tblHtml = ``;
    if (service_po_sc_data.length > 0) {
        for (let key in service_po_sc_data) {

            var formIndx = service_po_sc_data.indexOf(service_po_sc_data[key]);

            var ser_po_number = service_po_sc_data[key].ser_po_number ? service_po_sc_data[key].ser_po_number : "";
            var ser_po_date = service_po_sc_data[key].ser_po_date ? service_po_sc_data[key].ser_po_date : "";
            var supplier_name = service_po_sc_data[key].supplier_name ? service_po_sc_data[key].supplier_name : "";
            var purpose = service_po_sc_data[key].purpose ? service_po_sc_data[key].purpose : "";
            var ref_no_date = service_po_sc_data[key].ref_no_date ? service_po_sc_data[key].ref_no_date : "";
            var bill_to = service_po_sc_data[key].bill_to ? service_po_sc_data[key].bill_to : "";
            var for_location = service_po_sc_data[key].for_location ? service_po_sc_data[key].for_location : "";
            var item_name = service_po_sc_data[key].item_name ? service_po_sc_data[key].item_name : "";
            var item_group = service_po_sc_data[key].item_group ? service_po_sc_data[key].item_group : null;
            var main_group = service_po_sc_data[key].main_group ? service_po_sc_data[key].main_group : null;
            var sr_no = service_po_sc_data[key].name_for_display ? service_po_sc_data[key].name_for_display : null;
            var pending_qty = service_po_sc_data[key].pending_qty ? parseFloat(service_po_sc_data[key].pending_qty).toFixed(3) : "";

            var unit = service_po_sc_data[key].unit ? service_po_sc_data[key].unit : "";
            var del_date = service_po_sc_data[key].del_date ? service_po_sc_data[key].del_date : "";
            var remark = service_po_sc_data[key].remark ? service_po_sc_data[key].remark : "";
            var prepared_by = service_po_sc_data[key].prepared_by ? service_po_sc_data[key].prepared_by : "";
            var sr_table_unique_id = service_po_sc_data[key].sr_table_unique_id ? service_po_sc_data[key].sr_table_unique_id : "";
            var sr_table_pk_id = service_po_sc_data[key].sr_table_pk_id ? service_po_sc_data[key].sr_table_pk_id : "";


            tblHtml += `<tr>`;
            tblHtml += `<td>         
                <input class="checkbox-filter-remove" type="checkbox" name="ser_pod_id[]" class="simple-check" id="ser_pod_ids_${service_po_sc_data[key].ser_pod_id}" value="${service_po_sc_data[key].ser_pod_id}" onchange="manageQtyfield(this)"/>
                <input type="hidden" name="form_indx" value="${formIndx}"/>
                <input type="hidden" name="sr_table_unique_id[]" value="${sr_table_unique_id}"/>
                <input type="hidden" name="sr_table_pk_id[]" value="${sr_table_pk_id}"/>
            </td>`;
            tblHtml += `<td>${ser_po_number}</td>`;
            tblHtml += `<td>${ser_po_date}</td>`;
            tblHtml += `<td>${supplier_name}</td>`;
            tblHtml += `<td>${purpose}</td>`;
            tblHtml += `<td>${ref_no_date}</td>`;
            tblHtml += `<td>${bill_to}</td>`;
            tblHtml += `<td>${for_location}</td>`;
            tblHtml += `<td>${item_name}</td>`;
            tblHtml += `<td>${item_group}</td>`;
            tblHtml += `<td>${main_group}</td>`;
            tblHtml += `<td>${sr_no}</td>`;
            tblHtml += `<td>${parseFloat(pending_qty).toFixed(3) ?? parseFloat(0).toFixed(3)}</td>`;
            tblHtml += `<td>${unit}</td>`;
            tblHtml += `<td>${del_date}</td>`;
            tblHtml += `<td class="position-relative">
                            <input type="text" class="form-control isNumberKey remove_filters_short_qty" data-pending="${pending_qty}" max="${pending_qty}" id="ser_po_sc_qty_${service_po_sc_data[key].ser_pod_id}" name="ser_po_sc_qty[]" onblur="formatPoints(this,3)" readonly required>
                            <div class="invalid-tooltip">
                                Enter Short Close Qty.
                            </div>
                        </td>`;
            tblHtml += `<td class="position-relative">
                            <select class="js-example-basic-single form-control remove_filters_short_qty"
                                name="ser_po_sc_reason_id[]" id="pisc_sc_reason_id_${service_po_sc_data[key].ser_pod_id}" disabled required>
                                ${reasonOptions}
                            </select>
                            <div class="invalid-tooltip">
                                Select Reason.
                            </div>
                        </td>`;
            tblHtml += `<td>${remark}</td>`;
            tblHtml += `<td>${prepared_by}</td>`;
            tblHtml += `</tr>`;
        }

        jQuery('#ServicePOShortCloseDataTable tbody').empty();
        jQuery('#ServicePOShortCloseDataTable tbody').append(tblHtml);

        var $table = jQuery("#ServicePOShortCloseModal").find('#ServicePOShortCloseDataTable');
        if (jQuery.fn.DataTable.isDataTable($table)) {
            $table.DataTable().clear().destroy();
        }
        jQuery('#ServicePOShortCloseDataTable tbody').empty().append(tblHtml);

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
            let table = $('#ServicePOShortCloseDataTable').DataTable();

            $.each(selectedRows, function (pid, row) {
                $(table.table().body()).prepend(row); // move to top
            });

            jQuery('#ServicePOShortCloseModal .js-example-basic-single').select2({
                width: '100%',
                dropdownParent: jQuery('#ServicePOShortCloseModal')
            });
        });
    }
}

jQuery('#checkall-service_po_data').click(function () {
    if (jQuery(this).is(':checked')) {
        jQuery("#ServicePOShortCloseDataTable").find("[id^='ser_pod_ids_']:not(.in-use)").prop('checked', true).trigger('change');
        jQuery("#ServicePOShortCloseDataTable").find("[id^='ser_pod_ids_']").prop('checked', true).trigger('change');
        jQuery("#ServicePOShortCloseDataTable").find("[id^='ser_po_sc_qty_']").prop('readonly', true).prop('required', true);
        jQuery("#ServicePOShortCloseDataTable").find("[id^='pisc_sc_reason_id_']").prop('disabled', false).prop('required', true);
    } else {
        jQuery("#ServicePOShortCloseDataTable").find("[id^='ser_pod_ids_']:not(.in-use)").prop('checked', false).trigger('change');
        jQuery("#ServicePOShortCloseDataTable").find("[id^='ser_pod_ids_']").prop('checked', false).trigger('change');
        jQuery("#ServicePOShortCloseDataTable").find("[id^='ser_po_sc_qty_']").prop('readonly', true).prop('required', false);
        jQuery("#ServicePOShortCloseDataTable").find("[id^='pisc_sc_reason_id_']").prop('disabled', true).prop('required', false);
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
    var qtyField = $row.find('input[name="ser_po_sc_qty[]"]');
    var reasonField = $row.find('select[name="ser_po_sc_reason_id[]"]');

    if (jQuery($this).is(':checked')) {
        //qtyField.prop('disabled', false).prop('required', true);
        reasonField.prop('disabled', false).prop('required', true);

        var pending = qtyField.attr('data-pending');
        qtyField.val(pending);
    } else {
        //qtyField.val('').prop('disabled', true).prop('required', false);
        reasonField.val('').trigger('change').prop('disabled', true).prop('required', false);
    }
}



$('#commonServicePOShortCloseForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#ServicePOShortCloseModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;
    var dateValue = document.getElementById("ser_po_sc_date").value.trim();
    if (!isValidDate(dateValue)) {
        toastr.error("Please Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ServicePOShortCloseModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ServicePOShortCloseModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ServicePOShortCloseModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    jQuery('#ServicePOShortCloseModal').find('#submitbtn').prop('disabled', true);

    var formId = jQuery('#ServicePOShortCloseModal').find('#commonServicePOShortCloseForm').find('#id').val();
    var formUrl = "store-service_po_short_close";

    service_po_sc_data = [];
    var index = 0;
    var hasError = false;
    jQuery('#ServicePOShortCloseDataTable tbody tr').each(function (e) {
        var ser_pod_id = jQuery(this).find('input[name="ser_pod_id[]"]');
        if (jQuery(ser_pod_id).is(':checked')) {
            ser_pod_id = jQuery(ser_pod_id).val();
            ser_po_sc_qty = jQuery(this).find('input[name="ser_po_sc_qty[]"]').val();
            ser_po_sc_reason_id = jQuery(this).find('select[name="ser_po_sc_reason_id[]"]').val();

            ser_po_sc_qty = jQuery(this).find('input[name="ser_po_sc_qty[]"]').val();
            sr_table_unique_id = jQuery(this).find('input[name="sr_table_unique_id[]"]').val();
            sr_table_pk_id = jQuery(this).find('input[name="sr_table_pk_id[]"]').val();

            service_po_sc_data[index] = {
                'ser_pod_id': ser_pod_id,
                'ser_po_sc_qty': ser_po_sc_qty,
                'ser_po_sc_reason_id': ser_po_sc_reason_id,
                'sr_table_unique_id': sr_table_unique_id,
                'sr_table_pk_id': sr_table_pk_id,
            };
            let qtyField = $(this).find('input[name="ser_po_sc_qty[]"]');
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


    if (service_po_sc_data.length > 0 && !jQuery.isEmptyObject(service_po_sc_data)) {
        var data = new FormData(form);
        data.append('service_po_sc_data', JSON.stringify(service_po_sc_data ?? []));
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
                        jQuery('#ServicePOShortCloseModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formId == undefined || formId == "") {
                        function nextFn() {
                            document.getElementById("commonServicePOShortCloseForm").reset();
                            const form = document.getElementById("commonServicePOShortCloseForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }

                            setTimeout(function () {
                                const $frDate = jQuery('#commonServicePOShortCloseForm #ser_po_sc_date');
                                $frDate.trigger('focus');
                                setTimeout(function () {
                                    if ($frDate.hasClass('trans-date-picker')) {
                                        $frDate.datepicker('hide');
                                    }
                                }, 0);
                            }, 150);
                            $("#pisc_sc_regret_reason_id").val('').trigger('change');
                            service_po_sc_data = [];
                            selectedRows = {};
                            jQuery('#ServicePOShortCloseDataTable tbody').empty();
                            jQuery("#ServicePOShortCloseDataTable").find("[id^='ser_pod_ids_']:not(.in-use)").prop('checked', false).trigger('change');
                            jQuery("#ServicePOShortCloseDataTable").find("[id^='ser_pod_ids_']").prop('checked', false).trigger('change');
                            jQuery("#ServicePOShortCloseDataTable").find("[id^='ser_po_sc_qty_']").prop('readonly', true).prop('required', false);
                            jQuery("#ServicePOShortCloseDataTable").find("[id^='pisc_sc_reason_id_']").prop('readonly', true).prop('required', false);
                            PendingServicePOShortClose();

                        }
                        toastSuccess(data.response_message, nextFn);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#ServicePOShortCloseModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#ServicePOShortCloseModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#ServicePOShortCloseModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#ServicePOShortCloseModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#ServicePOShortCloseModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        var formId = jQuery('#ServicePOShortCloseModal').find('#commonServicePOShortCloseForm').find('#id').val();
        if (formId != undefined && formId != "") {
            jQuery('#ServicePOShortCloseModal').find('#submitbtn').prop('disabled', true);
            toastr.error('You can not update.');
        }
        toastr.error('Please Add At Least One Short Close Details.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ServicePOShortCloseModal').find('#submitbtn').prop('disabled', false);
    }
});

jQuery('#ServicePOShortCloseModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#ServicePOShortCloseModal');
    thisModal.find("#id").val("");
    service_po_sc_data = [];
    selectedRows = {};
    jQuery('#commonServicePOShortCloseForm').trigger("reset");
    thisModal.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).prop('checked', false);
    });
});