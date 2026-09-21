var grn_details_data = [];
var formId = jQuery('#commonGrnForm').find('input[name="id"]').val();
let selectedRows = {};
$.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
    var row = $('#pendingPODataTable').DataTable().row(dataIndex).node();
    var checkbox = $(row).find("input[type='checkbox']");
    if (checkbox.is(':checked')) {
        return true;
    }
    return true;
});
// Edit GRN Supplier row click
jQuery('#dyntable tbody').on('click', '.edit-grn_supplier', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#GrnModal').find('#id').val(data["grn_id"]);
    if (data && data["grn_id"]) {
        fetchAndFillGRNSupplier(data["grn_id"]);
    }
});

// // Function to fetch and fill purchase order data
function fetchAndFillGRNSupplier(id) {
    if (!id) return;
    jQuery('#GrnModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-grn_supplier",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.grn_supplier_data != null) {
                setSelect2Readonly("#grn_supplier_id", true);
                jQuery('#commonGrnForm').find('#grn_supplier_id').prop({ tabindex: -1, readonly: true });
                jQuery("#GrnModal").find("#grn_supplier_id").addClass('skip-tab');
                setSelect2Readonly('#grn_supplier_id', true);
                jQuery('#GrnModal').find('#id').val(data.grn_supplier_data.grn_id != "" ? data.grn_supplier_data.grn_id : "");
                let url = checkFileRoute + "?id=" + data.grn_supplier_data.grn_id + "&name=" + data.grn_supplier_data.pdf_name + "&type=grn_supplier";
                jQuery('#preview_btn').attr('href', url).show();
                var grn_type = data.grn_supplier_data.grn_type_id;

                jQuery('#GrnModal').find('input[name*="grn_type_id"][value="' + grn_type + '"]').prop('checked', true).change();
                setRadioReadonly("input[name='grn_type_id']", true);
                jQuery('#GrnModal').find('#grn_number').val(data.grn_supplier_data.grn_number != "" ? data.grn_supplier_data.grn_number : "");
                jQuery('#GrnModal').find('#grn_date').val(data.grn_supplier_data.grn_date != "" ? data.grn_supplier_data.grn_date : "");
                jQuery('#GrnModal').find('#grn_sequence').val(data.grn_supplier_data.grn_sequence != "" ? data.grn_supplier_data.grn_sequence : "");
                // jQuery('#GrnModal').find('#grn_supplier_id').val(data.grn_supplier_data.grn_supplier_id != "" ? data.grn_supplier_data.grn_supplier_id : "").trigger('change.select2');

                getPendingSuppliersForGrn().done(function () {

                    let supplierId = data.grn_supplier_data.grn_supplier_id;
                    let supplierText = data.grn_supplier_data.supplier_name;
                    let $supplier = jQuery('#GrnModal').find('#grn_supplier_id');
                    if ($supplier.find("option[value='" + supplierId + "']").length === 0) {
                        $supplier.append(
                            `<option class="temp-supplier" value="${supplierId}" selected>${supplierText}</option>`
                        );
                    }
                    $supplier.val(supplierId).trigger('change.select2');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');

                    if (data.grn_supplier_data.in_use == true) {
                        jQuery('#GrnModal').find('#grn_sequence').prop('readonly', true);
                        let nextInput = jQuery('#GrnModal').find('#grn_date');
                        if (nextInput.hasClass('trans-date-picker') || nextInput.hasClass('date-picker')) {
                            nextInput.one('focus', function () {
                                setTimeout(() => {
                                    jQuery(this).datepicker('hide');
                                }, 50);
                            });
                        }
                        nextInput.focus();
                    } else {
                        jQuery('#GrnModal').find('#grn_sequence').prop('readonly', false);
                        const input = document.getElementById('grn_sequence');
                        input?.focus();
                    }
                });
                jQuery('#GrnModal').find('#grn_challan_number').val(data.grn_supplier_data.grn_challan_number != "" ? data.grn_supplier_data.grn_challan_number : "");
                jQuery('#GrnModal').find('#grn_challan_date').val(data.grn_supplier_data.grn_challan_date != "" ? data.grn_supplier_data.grn_challan_date : "");
                jQuery('#GrnModal').find('#grn_total_amount').val(data.grn_supplier_data.grn_total_amount != "" ? data.grn_supplier_data.grn_total_amount : "");
                jQuery('#GrnModal').find('#mode_of_transport').val(data.grn_supplier_data.mode_of_transport != "" ? data.grn_supplier_data.mode_of_transport : "");

                jQuery('#GrnModal').find('#grn_transporter').val(data.grn_supplier_data.grn_transporter != "" ? data.grn_supplier_data.grn_transporter : "");

                jQuery('#GrnModal').find('#grn_vehicle_number').val(data.grn_supplier_data.grn_vehicle_number != "" ? data.grn_supplier_data.grn_vehicle_number : "");
                jQuery('#GrnModal').find('#grn_special_note').val(data.grn_supplier_data.grn_special_note != "" ? data.grn_supplier_data.grn_special_note : "");
                jQuery('#GrnModal').find('#prepared_by_user_id').val(data.grn_supplier_data.prepared_by_user_id != "" ? data.grn_supplier_data.prepared_by_user_id : "").trigger('change.select2');
                grn_details_data = [];


                if (data.grn_supplier_details_data != "" && data.grn_supplier_details_data.length > 0) {
                    grn_details_data.push(...data.grn_supplier_details_data);
                    fillGrnDetailsTable();
                }

                jQuery('#GrnModal').find('#pending_btn').prop('disabled', true);
                jQuery('#GrnModal').find('#submitbtn').prop('disabled', false);
                // jQuery('#GrnModal').find('#grn_sequence').focus();

                const form = document.getElementById("commonGrnForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#GrnModal').find('#add_new').show();
                jQuery('#GrnModal').find('#preview_btn').show();
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
        // complete: function () {
        //     jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        //     const input = document.getElementById('grn_sequence');
        //     input?.focus();
        // }
    });
}

jQuery('#GrnModal').on('show.bs.modal', function () {
    jQuery('#GrnModal').find('#grn_sequence').prop('readonly', false);
    var formId = jQuery('#commonGrnForm').find('input[name="id"]').val();

    if (formId == "" || formId == undefined) {
        jQuery('#commonGrnForm').find('.add_detail').prop('disabled', true);
        jQuery('#commonGrnForm').find('#grn_supplier_id').removeClass('skip-tab');
        setSelect2Readonly('#grn_supplier_id', false);
        jQuery(this).find('#grn_supplier_id option.temp-supplier').remove();
        jQuery('.pending_detail').attr('data-bs-target', '#GrnPendingModal');
        jQuery('#commonGrnForm').find('#prepared_by_user_id').val(loginUserId).trigger("change.select2");
        getLatestGRNNo();
        getPendingSuppliersForGrn();
        // jQuery('#GrnModal .for_po').show();
        // jQuery('#GrnModal .for_dc').hide();
    }
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('grn_sequence');
    input?.focus();

});

jQuery('#commonGrnForm').find('#grn_sequence').on('change', function () {
    checkSequence();
});
function checkSequence() {

    let thisForm = jQuery('#commonGrnForm');
    let val = thisForm.find('#grn_sequence').val();

    if (val != "") {

        if (val > 0 == false) {
            toastr.error('Enter Valid GRN No.');
            jQuery('#grn_sequence').parent().parent().parent('div.control-group').addClass('error');
            jQuery('#grn_sequence').focus();
            jQuery('#grn_sequence').val('');

        } else {
            jQuery('#grn_sequence').addClass('file-loader');
            jQuery('#grn_sequence').parent().parent().parent('div.control-group').removeClass('error');

            var urL = "check-grn_supplier_number_duplication?for=add&grn_sequence=" + val;

            var formId = jQuery('#commonPurchaseOrderForm').find('input[name="id"]').val();

            if (formId !== undefined) { //if form is edit
                urL = "check-grn_supplier_number_duplication?for=edit&grn_sequence=" + val + "&id=" + formId;
            }

            jQuery.ajax({
                url: urL,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    jQuery('#grn_sequence').removeClass('file-loader');
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        jQuery('#commonGrnForm #grn_sequence').val('');
                        const input = document.getElementById('grn_sequence'); input?.focus();
                    } else {
                        jQuery('#commonGrnForm #grn_number').val(data.latest_no);
                        jQuery('#commonGrnForm #grn_sequence').val(val);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#grn_sequence').removeClass('file-loader');
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
        jQuery('#grn_number').val('');
        jQuery('#grn_sequence').val('');
    }

}


// get the latest number
function getLatestGRNNo() {
    jQuery.ajax({
        url: "get-latest_grn_supplier_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#grn_number').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#grn_sequence').val(data.number);
                jQuery('#grn_date').val(currentDate);
            } else {
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            jQuery('#grn_number').removeClass('file-loader');
            console.log('Field To Get Latest GRN No.!')
        }
    });
}

let currentModal = '#GrnPendingModal';

// Radio change
jQuery('input[name*="grn_type_id"]').on('change', function () {
    getPendingSuppliersForGrn();
    var grn_type_id = jQuery('#commonGrnForm').find("input[name*='grn_type_id']:checked").val();
    jQuery('.toggleModalBtn').prop('disabled', true);
    if (grn_type_id == 'Against PO') {

        currentModal = '#GrnPendingModal';
        jQuery('#commonGrnForm').find('.add_detail').prop('disabled', true);
    } else if (grn_type_id == 'Against DC') {

        currentModal = '#GrnSupplierDCPendingModal';
        jQuery('#commonGrnForm').find('.add_detail').prop('disabled', true);
    } else {

        currentModal = '';
        jQuery('#commonGrnForm').find('.add_detail').prop('disabled', false);
    }
});

jQuery('.pending_detail').off('click').on('click', function (e) {

    e.preventDefault();
    e.stopPropagation();
    jQuery(currentModal).modal('show');
});


function getPendingSuppliersForGrn() {
    var grn_type_id = jQuery('#commonGrnForm').find("input[name*='grn_type_id']:checked").val();

    var formId = jQuery('#commonGrnForm').find('input[name="id"]').val();

    if (formId != undefined && formId != "") {
        var Url = 'get-pending_supplier_for_grn?grn_type_id=' + grn_type_id + "&id=" + formId;
    } else {
        var Url = 'get-pending_supplier_for_grn?grn_type_id=' + grn_type_id;
    }

    if (grn_type_id != "" && grn_type_id != undefined) {
        let suppHtml = '';
        suppHtml += `<option value="">Select Supplier</option> `;
        return jQuery.ajax({
            url: Url,
            type: 'GET',
            headers: headerOpt,
            dataType: 'json',
            processData: false,
            success: function (data) {
                if (data.response_code == 1) {
                    for (let indx in data.get_po_supplier) {
                        suppHtml += `<option value="${data.get_po_supplier[indx].id}">${data.get_po_supplier[indx].supplier_name}</option>`;
                    }

                    jQuery('#commonGrnForm').find('#grn_supplier_id').empty().append(suppHtml);

                } else {
                    console.log(data.response_message)
                }
            },
        });

    } else {
        jQuery('#commonGrnForm').find('#grn_supplier_id').empty().append(suppHtml);
    }


}

$('#grn_supplier_id').on('change', function () {
    fillPendingGrn();
});


function fillPendingGrn() {
    let supId = jQuery('#grn_supplier_id option:selected').val();
    var grn_type_id = jQuery('#commonGrnForm').find("input[name*='grn_type_id']:checked").val();

    var thisForm = jQuery('#GrnDetailsForm');

    var formId = jQuery('#commonGrnForm').find('input[name="id"]').val();


    if (supId != "" && grn_type_id == 'Against PO') {
        if (formId != undefined && formId != '') {
            var Url = "get-pending_po_list_for_grn?grn_supplier_id=" + supId + "&id=" + formId;
        } else {
            var Url = "get-pending_po_list_for_grn?grn_supplier_id=" + supId;
        }

        jQuery.ajax({
            url: Url,
            type: 'GET',
            headers: headerOpt,
            dataType: 'json',
            processData: false,
            success: function (data) {
                if (data.response_code == 1 && data.po_data.length > 0) {
                    // new code
                    var usedParts = [];
                    var totalDisb = 0;
                    var found = 0;

                    thisForm.find('#GrnDetailTable tbody input[name="form_indx"]').each(function (indx) {
                        let frmIndx = jQuery(this).val();

                        let jbEorkOrderId = po_data[frmIndx].po_id;
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
                    if (data.po_data.length > 0 && !jQuery.isEmptyObject(data.po_data)) {
                        found = 1;

                        for (let idx in data.po_data) {
                            var inUse = isUsed(data.po_data[idx].pod_id);
                            var in_use = data.po_data[idx].in_use == true ? 'readonly' : '';
                            totalEntry++;
                            tblHtml += `
                                    <tr>
                                        <td><input type="checkbox" name="pod_id[]" class="simple-check checkbox-filter-remove ${inUse ? 'in-use' : ''}" id="pod_ids_${data.po_data[idx].pod_id}" value="${data.po_data[idx].pod_id}" ${inUse ? 'checked' : ''} ${in_use} onchange="checkesCheckboxFristpo(this)"/></td>
                                        <td>${data.po_data[idx].po_number}</td>
                                        <td>${data.po_data[idx].po_date}</td>
                                        <td>${data.po_data[idx].ref_no_date != null && data.po_data[idx].ref_no_date != undefined ? data.po_data[idx].ref_no_date : ''}</td>
                                        <td>${data.po_data[idx].bill_to}</td>
                                        <td>${data.po_data[idx].ship_to}</td>
                                        <td>${data.po_data[idx].item_name}</td>
                                        <td>${data.po_data[idx].item_group}</td>
                                        <td>${data.po_data[idx].main_group}</td>
                                        <td>${parseFloat(data.po_data[idx].pending_qty).toFixed(3)}</td>
                                        <td>${data.po_data[idx].unit}</td>
                                        <td>${data.po_data[idx].pod_del_date}</td>
                                        <td>${data.po_data[idx].pod_remark != null && data.po_data[idx].pod_remark != undefined ? data.po_data[idx].pod_remark : ""}</td>
                                        <td>${data.po_data[idx].prepared_by}</td>
                                    </tr>`;

                        }

                    } else {

                        tblHtml += `<tr class="centeralign" id="noPendingPo">
                                        <td colspan="14">No Pending PO Available</td>
                                    </tr>`;

                    }



                    var $table = jQuery("#GrnPendingModal").find('#pendingPODataTable');
                    if (jQuery.fn.DataTable.isDataTable($table)) {
                        $table.DataTable().clear().destroy();
                    }
                    jQuery('#pendingPODataTable tbody').empty().append(tblHtml);

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

                    if (grn_type_id == 'Manual') {
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

    } else if (supId != "" && grn_type_id == 'Against DC') {

        if (formId != undefined && formId != '') {
            var Url = "get-pending_dc_list_for_grn?grn_supplier_id=" + supId + "&id=" + formId;
        } else {
            var Url = "get-pending_dc_list_for_grn?grn_supplier_id=" + supId;
        }

        jQuery.ajax({
            url: Url,
            type: 'GET',
            headers: headerOpt,
            dataType: 'json',
            processData: false,
            success: function (data) {
                if (data.response_code == 1 && data.dc_data.length > 0) {
                    // new code
                    var usedParts = [];
                    var totalDisb = 0;
                    var found = 0;

                    thisForm.find('#GrnDetailTable tbody input[name="form_indx"]').each(function (indx) {
                        let frmIndx = jQuery(this).val();

                        let jbEorkOrderId = dc_data[frmIndx].dc_id;
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
                    if (data.dc_data.length > 0 && !jQuery.isEmptyObject(data.dc_data)) {
                        found = 1;

                        for (let idx in data.dc_data) {
                            var inUse = isUsed(data.dc_data[idx].dc_id);
                            var in_use = data.dc_data[idx].in_use == true ? 'readonly' : '';
                            totalEntry++;
                            tblHtml += `
                                    <tr>
                                        <td><input type="checkbox" name="sup_dcd_id[]" class="simple-check checkbox-filter-remove ${inUse ? 'in-use' : ''}" id="sup_dcd_ids_${data.dc_data[idx].sup_dcd_id}" value="${data.dc_data[idx].sup_dcd_id}" ${inUse ? 'checked' : ''} ${in_use} onchange="checkesCheckboxFrist(this)"/></td>
                                        <td>${data.dc_data[idx].sup_dc_number}</td>
                                        <td>${data.dc_data[idx].sup_dc_date}</td>
                                        <td>${data.dc_data[idx].ref_no_date != null && data.dc_data[idx].ref_no_date != undefined ? data.dc_data[idx].ref_no_date : ''}</td>
                                        <td>${data.dc_data[idx].item_name}</td>
                                        <td>${data.dc_data[idx].item_group}</td>
                                        <td>${data.dc_data[idx].main_group}</td>
                                        <td>${parseFloat(data.dc_data[idx].pending_qty).toFixed(3)}</td>
                                        <td>${data.dc_data[idx].unit}</td>
                                        <td>${data.dc_data[idx].name_for_display != null && data.dc_data[idx].name_for_display != undefined ? data.dc_data[idx].name_for_display : ""}</td>
                                        <td>${data.dc_data[idx].remark != null && data.dc_data[idx].remark != undefined ? data.dc_data[idx].remark : ""}</td>
                                        <td>${data.dc_data[idx].prepared_by}</td>
                                    </tr>`;

                        }

                    } else {

                        tblHtml += `<tr class="centeralign" id="noPendingDC">
                                        <td colspan="12">No Pending DC Available</td>
                                    </tr>`;

                    }



                    var $table = jQuery("#GrnSupplierDCPendingModal").find('#pendingSupplierDCDataTable');
                    if (jQuery.fn.DataTable.isDataTable($table)) {
                        $table.DataTable().clear().destroy();
                    }
                    jQuery('#pendingSupplierDCDataTable tbody').empty().append(tblHtml);

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

                    if (grn_type_id == 'Manual') {
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


// get selected pending PO 
$('#addPendigGrnForm').on('submit', function (e) {

    e.preventDefault();

    jQuery('#full-page-loader')
        .removeClass('hidden-loader')
        .addClass('loader-progress-whole-page');

    jQuery('#GrnPendingModal')
        .find('#submitbtn')
        .prop('disabled', true);

    let chkArr = [];
    var formId = jQuery('#commonGrnForm').find('input[name="id"]').val();

    jQuery("#addPendigGrnForm")
        .find("[id^='pod_ids_']:checked")
        .each(function () {
            chkArr.push(jQuery(this).val());
        });

    // No checkbox selected
    if (chkArr.length === 0) {
        toastr.error('Select PO From Pending.');

        jQuery('#full-page-loader')
            .removeClass('loader-progress-whole-page')
            .addClass('hidden-loader');

        jQuery('#GrnPendingModal')
            .find('#submitbtn')
            .prop('disabled', false);

        return;
    }

    if (formId != undefined && formId != "") {
        var pend_url = "get-pending_po_for_grn?id=" + formId;
    } else {
        var pend_url = "get-pending_po_for_grn";
    }

    jQuery.ajax({
        url: pend_url,
        type: 'GET',
        data: { pod_ids: chkArr.join(',') },
        dataType: 'json',
        success: function (data) {

            if (data.response_code == 1) {

                if (data.po_data && data.po_data.length > 0) {
                    grn_details_data = [];
                    for (let ind in data.po_data) {
                        grn_details_data.push(data.po_data[ind]);
                    }
                    fillGrnDetailsTable(data.po_data);
                } else {
                    fillGrnDetailsTable([]);
                }

                jQuery("#GrnPendingModal").modal('hide');
                setRadioReadonly('input[name*="grn_type_id"]', true);
                jQuery("#commonGrnForm").find('#grn_supplier_id').addClass('skip-tab');
                setSelect2Readonly('#grn_supplier_id', true);
            } else {
                fillGrnDetailsTable([]);
            }

            jQuery('#GrnPendingModal')
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

            jQuery('#GrnPendingModal')
                .find('#submitbtn')
                .prop('disabled', false);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');
        }
    });
});

// get selected pending PO 
$('#addPendingGrnSupplierDCForm').on('submit', function (e) {

    e.preventDefault();

    jQuery('#full-page-loader')
        .removeClass('hidden-loader')
        .addClass('loader-progress-whole-page');

    jQuery('#GrnSupplierDCPendingModal')
        .find('#submitbtn')
        .prop('disabled', true);

    let chkArr = [];
    var formId = jQuery('#commonGrnForm').find('input[name="id"]').val();

    jQuery("#addPendingGrnSupplierDCForm")
        .find("[id^='sup_dcd_ids_']:checked")
        .each(function () {
            chkArr.push(jQuery(this).val());
        });

    // No checkbox selected
    if (chkArr.length === 0) {
        toastr.error('Select DC From Pending.');

        jQuery('#full-page-loader')
            .removeClass('loader-progress-whole-page')
            .addClass('hidden-loader');

        jQuery('#GrnSupplierDCPendingModal')
            .find('#submitbtn')
            .prop('disabled', false);

        return;
    }

    if (formId != undefined && formId != "") {
        var pend_url = "get-pending_dc_for_grn?id=" + formId;
    } else {
        var pend_url = "get-pending_dc_for_grn";
    }

    jQuery.ajax({
        url: pend_url,
        type: 'GET',
        data: { sup_dcd_ids: chkArr.join(',') },
        dataType: 'json',
        success: function (data) {

            if (data.response_code == 1) {

                if (data.dc_data && data.dc_data.length > 0) {
                    grn_details_data = [];
                    for (let ind in data.dc_data) {
                        grn_details_data.push(data.dc_data[ind]);
                    }
                    fillGrnDetailsTable(data.dc_data);
                } else {
                    fillGrnDetailsTable([]);
                }

                jQuery("#GrnSupplierDCPendingModal").modal('hide');
                setRadioReadonly('input[name*="grn_type_id"]', true);
                jQuery("#commonGrnForm").find('#grn_supplier_id').addClass('skip-tab');
                setSelect2Readonly('#grn_supplier_id', true);
            } else {
                fillGrnDetailsTable([]);
            }

            jQuery('#GrnSupplierDCPendingModal')
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

            jQuery('#GrnSupplierDCPendingModal')
                .find('#submitbtn')
                .prop('disabled', false);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');
        }
    });
});


function fillGrnDetailsTable() {
    var grn_type_id = jQuery('#commonGrnForm').find("input[name*='grn_type_id']:checked").val();

    let thisModal = jQuery('#GrnDetailsModal');
    let tblHtml = ``;
    if (grn_details_data.length > 0) {
        for (let key in grn_details_data) {

            var formIndx = grn_details_data.indexOf(grn_details_data[key]);

            if (grn_type_id == 'Against PO') {
                var po_number = grn_details_data[key].po_number ? grn_details_data[key].po_number : "";
                var po_date = grn_details_data[key].po_date ? grn_details_data[key].po_date : "";
                var sup_dc_number = "";
                var sup_dc_date = "";
            } else if (grn_type_id == 'Against DC') {
                var po_number = "";
                var po_date = "";
                var sup_dc_number = grn_details_data[key].sup_dc_number ? grn_details_data[key].sup_dc_number : "";
                var sup_dc_date = grn_details_data[key].sup_dc_date ? grn_details_data[key].sup_dc_date : "";
            } else {
                var po_number = "";
                var po_date = "";
                var sup_dc_number = "";
                var sup_dc_date = "";
            }

            if (grn_details_data[key].item_name != '' && grn_details_data[key].item_name != undefined) {
                var item_name = grn_details_data[key].item_name;
            } else {
                var item_name = grnd_item_id != '' ? thisModal.find('#grnd_item_id option[value="' + grnd_item_id + '"]').text() : '';
            }

            var item_group = grn_details_data[key].item_group ? grn_details_data[key].item_group : "";
            var main_group = grn_details_data[key].main_group ? grn_details_data[key].main_group : "";
            var sr_no = grn_details_data[key].name_for_display ? grn_details_data[key].name_for_display : "";

            var pending_qty = '';
            if (po_number != "") {
                pending_qty = grn_details_data[key].pending_qty ? parseFloat(grn_details_data[key].pending_qty).toFixed(3) : "";
            } else if (sup_dc_number != "") {
                pending_qty = grn_details_data[key].pending_qty ? parseFloat(grn_details_data[key].pending_qty).toFixed(3) : "";
            } else {
                pending_qty = '';
            }


            var grnd_qty = grn_details_data[key].grnd_qty ? parseFloat(grn_details_data[key].grnd_qty).toFixed(3) : parseFloat(0).toFixed(3);
            var unit = thisModal.find("#grnd_qty_unit").text() ? thisModal.find("#grnd_qty_unit").text() : grn_details_data[key].unit;

            var grnd_rate_unit = grn_details_data[key].grnd_rate_unit ? parseFloat(grn_details_data[key].grnd_rate_unit).toFixed(2) : parseFloat(0).toFixed(2);

            var grnd_amount = grn_details_data[key].grnd_amount ? parseFloat(grn_details_data[key].grnd_amount).toFixed(2) : parseFloat(0).toFixed(2);

            var grnd_remark = grn_details_data[key].grnd_remark ? grn_details_data[key].grnd_remark : "";
            var in_use = grn_details_data[key].in_use == true ? true : false;


            tblHtml += `<tr>`;
            tblHtml += `<td>`;
            tblHtml += in_use == true ? DetailsActionDropdown('editGrnDetails') : DetailsActionDropdown('editGrnDetails', 'removeGrnDetails');
            tblHtml += ` <input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;
            tblHtml += `<td>${po_number}</td>`;
            tblHtml += `<td>${po_date}</td>`;
            tblHtml += `<td>${sup_dc_number}</td>`;
            tblHtml += `<td>${sup_dc_date}</td>`;
            tblHtml += `<td>${item_name}</td>`;
            tblHtml += `<td>${item_group}</td>`;
            tblHtml += `<td>${main_group}</td>`;
            tblHtml += `<td>${sr_no}</td>`;
            tblHtml += `<td>${pending_qty}</td>`;
            tblHtml += `<td>${grnd_qty}</td>`;
            tblHtml += `<td>${unit}</td>`;
            tblHtml += `<td>${grnd_rate_unit}</td>`;
            tblHtml += `<td class="amount" data-val="${grnd_amount}">${grnd_amount}</td>`;
            tblHtml += `<td>${grnd_remark}</td>`;
            tblHtml += `</tr>`;
        }
        jQuery('#GrnDetailTable tbody').empty();
        jQuery('#GrnDetailTable tbody').append(tblHtml);
    }
    calculateTotalAmount();
}


// edit Grn Supplier details
function editGrnDetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();
    fillGrnDetailsForm(formIndx, rawIndx);
}

// GRN Supplier details form edit
function fillGrnDetailsForm(formIndx, rawIndx) {
    var grn_type_id = jQuery('#commonGrnForm').find("input[name*='grn_type_id']:checked").val();


    let thisForm = jQuery('#GrnDetailsModal');
    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    thisForm.find('#grnd_item_id option.temp-item').remove();
    var frmData = grn_details_data[formIndx];

    let itemId = frmData.grnd_item_id;
    let itemText = frmData.item_name;


    if (thisForm.find("#grnd_item_id option[value='" + itemId + "']").length === 0) {
        thisForm.find("#grnd_item_id").append(
            `<option  class="temp-item" value="${itemId}" data-main_group="${frmData.main_group}" data-item_group="${frmData.item_group}" data-unit="${frmData.unit}"selected>${itemText}</option>`
        );
    }

    thisForm.find("#grnd_id").val(frmData.grnd_id ? frmData.grnd_id : "0");
    // thisForm.find("#grnd_pod_id").val(frmData.pod_id ? frmData.pod_id : "0");
    thisForm.find("#table_unique_id").val(frmData.table_unique_id ? frmData.table_unique_id : "");
    thisForm.find("#table_pk_id").val(frmData.table_pk_id ? frmData.table_pk_id : "0");

    if (grn_type_id == 'Against PO') {
        thisForm.find("#po_no").val(frmData.po_number ? frmData.po_number : "");
        thisForm.find("#po_date").val(frmData.po_date ? frmData.po_date : "");
        thisForm.find("#sup_dc_number").val("");
        thisForm.find("#sup_dc_date").val("");
    } else if (grn_type_id == 'Against DC') {
        thisForm.find("#po_no").val("");
        thisForm.find("#po_date").val("");
        thisForm.find("#sup_dc_number").val(frmData.sup_dc_number ? frmData.sup_dc_number : "");
        thisForm.find("#sup_dc_date").val(frmData.sup_dc_date ? frmData.sup_dc_date : "");
        thisForm.find("#sr_no").val(frmData.name_for_display ? frmData.name_for_display : "");
        thisForm.find("#sr_table_unique_id").val(frmData.sr_table_unique_id ? frmData.sr_table_unique_id : "");
        thisForm.find("#sr_table_pk_id").val(frmData.sr_table_pk_id ? frmData.sr_table_pk_id : "");
    } else {
        thisForm.find("#po_no").val("");
        thisForm.find("#po_date").val("");
        thisForm.find("#sup_dc_number").val("");
        thisForm.find("#sup_dc_date").val("");
    }

    thisForm.find("#grnd_item_id").val(zeroToEmpty(frmData.grnd_item_id)).trigger("change");



    if (grn_type_id == 'Against PO' || grn_type_id == 'Against DC') {
        thisForm.find("#pending_qty").val(frmData.pending_qty != "" && frmData.pending_qty != undefined ? parseFloat(frmData.pending_qty).toFixed(3) : "").attr('readonly', true);

        thisForm.find("#grnd_qty").attr('max', parseFloat(frmData.pending_qty).toFixed(3));
        thisForm.find("#grnd_item_id").addClass('skip-tab');

    } else {
        thisForm.find("#pending_qty").val("").attr('readonly', true);
        thisForm.find("#grnd_item_id").removeClass('skip-tab');

    }
    var decPlaces = (frmData.main_group == 'Industrial X-Ray Films') ? 0 : 3;
    thisForm.find("#grnd_qty").val(frmData.grnd_qty != "" && frmData.grnd_qty != undefined ? parseFloat(frmData.grnd_qty).toFixed(decPlaces) : parseFloat(0).toFixed(decPlaces));
    if (frmData.main_group == 'Industrial X-Ray Films') {
        thisForm.find('#grnd_qty').removeClass('isNumberKey').addClass('isNumberKeyNotDot');
        thisForm.find('#grnd_qty').attr('onblur', 'formatPoints(this, 0)');
    } else {
        thisForm.find('#grnd_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        thisForm.find('#grnd_qty').attr('onblur', 'formatPoints(this, 3)');
    }

    thisForm.find("#grnd_rate_unit").val(frmData.grnd_rate_unit != "" && frmData.grnd_rate_unit != undefined ? parseFloat(frmData.grnd_rate_unit).toFixed(2) : parseFloat(0).toFixed(2));

    thisForm.find("#grnd_amount").val(frmData.grnd_amount != undefined && frmData.grnd_amount != "" ? parseFloat(frmData.grnd_amount).toFixed(2) : parseFloat(0).toFixed(2));

    thisForm.find("#grnd_remark").val(frmData.grnd_remark);

    if (frmData.in_use == true) {

        thisForm.find("#grnd_qty").attr('min', parseFloat(frmData.used_qty).toFixed(3));

    }

    if (grn_type_id == "Against PO" || grn_type_id == "Against DC" || frmData.pod_id != 0) {
        thisForm.find('#grnd_item_id').addClass('skip-tab');
    } else {
        thisForm.find('#grnd_item_id').removeClass('skip-tab');
    }

    thisForm.find("#ins_cali_freq").val(frmData.ins_cali_freq ? frmData.ins_cali_freq : "");
    thisForm.find("#ins_last_cali_date").val(frmData.ins_last_cali_date ? frmData.ins_last_cali_date : "");
    thisForm.find("#ins_next_cali_due_date").val(frmData.ins_next_cali_due_date ? frmData.ins_next_cali_due_date : "");

    var formId = jQuery('#commonGrnForm').find('input[name="id"]').val();

    if (frmData.calibration_certificate_doc != "" && frmData.calibration_certificate_doc != undefined) {
        let fullPath = frmData.calibration_certificate_doc;
        thisForm.find("#calibration_certificate_doc").val(fullPath);
        thisForm.find('#calibration_certificate_prev').attr('href', uploadURL + fullPath).removeClass('hide');
        if (formId && formId != "") {
            thisForm.find('#calibration_certificate_remove').removeClass('i-block').addClass('hide');
        } else {
            thisForm.find('#calibration_certificate_remove').addClass('i-block').removeClass('hide');
        }
    } else {
        thisForm.find('#calibration_certificate_doc').val('');
        thisForm.find('#calibration_certificate').val('');
        thisForm.find('#calibration_certificate_prev').attr('href', '#').addClass('hide');
        thisForm.find('#calibration_certificate_remove').removeClass('i-block').addClass('hide');
    }

    toggleCalibrationFields(grn_type_id, frmData.for_calibration);

    if (formId && formId != "" && (grn_type_id == 'Against PO' || grn_type_id == 'Against DC')) {
        thisForm.find("#grnd_qty").attr('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
    } else {
        thisForm.find("#grnd_qty").attr('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
    }

    thisForm.modal('show');
}

function removeGrnDetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        removeFormObj(formIndx);
        jQuery(th).closest("tr").remove();

    });
}

function removeFormObj(formIndx) {
    delete grn_details_data[formIndx];
    grn_details_data = grn_details_data.filter(element => element != null);
    if (grn_details_data.length == 0) {
        setRadioReadonly('#commonGrnForm input[name*="grn_type_id"]', false);
        setSelect2Readonly("#commonGrnForm #grn_supplier_id", false);
    }
    jQuery('#GrnDetailTable tbody').empty();
    calculateTotalAmount();
    fillGrnDetailsTable();
}



jQuery('#GrnDetailsForm #grnd_item_id').on('change', function () {
    let selected = jQuery(this).find('option:selected');
    let main_group = selected.data('main_group');
    let item_group = selected.data('item_group');
    let unit = selected.data('unit');

    if (selected.val() != "") {
        jQuery('#item_group').val(item_group);
        jQuery('#main_group').val(main_group);
        jQuery('#pending_qty_unit').text(unit).addClass('ms-1');
        jQuery('#grnd_qty_unit').text(unit).addClass('ms-1');

        if (main_group == 'Industrial X-Ray Films') {
            jQuery('#grnd_qty').removeClass('isNumberKey').addClass('isNumberKeyNotDot');
            jQuery('#grnd_qty').attr('onblur', 'formatPoints(this, 0)');
            if (jQuery('#grnd_qty').val() !== '') {
                jQuery('#grnd_qty').val(parseInt(jQuery('#grnd_qty').val()) || '');
            }
        } else {
            jQuery('#grnd_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
            jQuery('#grnd_qty').attr('onblur', 'formatPoints(this, 3)');
        }
    } else {
        jQuery('#item_group').val('');
        jQuery('#main_group').val('');
        jQuery('#pending_qty_unit').text('').removeClass('ms-1');
        jQuery('#grnd_qty_unit').text('').removeClass('ms-1');
        jQuery('#grnd_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        jQuery('#grnd_qty').attr('onblur', 'formatPoints(this, 3)');
    }
});

// Details amount
function CalculateAmount() {

    let grn_qty = jQuery('#GrnDetailsModal').find("#grnd_qty").val();
    let rateUnit = jQuery('#GrnDetailsModal').find("#grnd_rate_unit").val();

    var total = 0;
    if (rateUnit != "" && grn_qty != "") {
        total = parseFloat(grn_qty) * parseFloat(rateUnit);
    }

    if (total != 0) {
        jQuery('#GrnDetailsModal').find('#grnd_amount').val(parseFloat(total).toFixed(2));
    } else if (rateUnit == "") {
        jQuery('#GrnDetailsModal').find('#grnd_amount').val('');
    } else {
        jQuery('#GrnDetailsModal').find('#grnd_amount').val(0);
    }

}

// Main GRN Supplier Form Submit
$('#commonGrnForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#GrnModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;



    var dateValue = document.getElementById("grn_date").value.trim();

    if (!isValidDate(dateValue)) {
        toastr.error("Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#GrnModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#GrnModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#GrnModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    jQuery('#GrnModal').find('#submitbtn').prop('disabled', true);

    var formIdnew = jQuery('#GrnModal').find('#commonGrnForm').find('#id').val();
    var formUrl = formIdnew != undefined && formIdnew != "" ? "update-grn_supplier" : "store-grn_supplier";
    var data = new FormData(form);
    data.append('grn_details_data', JSON.stringify(grn_details_data ?? []));
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (grn_details_data.length > 0 && !jQuery.isEmptyObject(grn_details_data)) {
        let isValidDetails = true;
        let errorMsg = '';
        var grn_type_id = jQuery('#commonGrnForm').find("input[name*='grn_type_id']:checked").val();

        $.each(grn_details_data, function (index, row) {

            if (row.grnd_qty === undefined || row.grnd_qty === null || row.grnd_qty === '' || parseFloat(row.grnd_qty) <= 0) {
                errorMsg = `Enter GRN Qty.`;
                isValidDetails = false;
                return false;
            }

            if (row.grnd_rate_unit === undefined || row.grnd_rate_unit === null || row.grnd_rate_unit === '' || parseFloat(row.grnd_rate_unit) <= 0) {
                errorMsg = `Enter Rate / Unit.`;
                isValidDetails = false;
                return false;
            }

            if (grn_type_id === 'Against DC' && row.for_calibration === 'Yes') {
                if (row.ins_cali_freq === undefined || row.ins_cali_freq === null || row.ins_cali_freq.toString().trim() === '') {
                    errorMsg = `Enter Calibration Freq.`;
                    isValidDetails = false;
                    return false;
                }
                if (row.ins_last_cali_date === undefined || row.ins_last_cali_date === null || row.ins_last_cali_date.toString().trim() === '') {
                    errorMsg = `Enter Last Calibration Date.`;
                    isValidDetails = false;
                    return false;
                }
                if (row.ins_next_cali_due_date === undefined || row.ins_next_cali_due_date === null || row.ins_next_cali_due_date.toString().trim() === '') {
                    errorMsg = `Enter Next Calibration Due Date.`;
                    isValidDetails = false;
                    return false;
                }
            }

        });

        if (!isValidDetails) {
            toastr.error(errorMsg);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');

            jQuery('#GrnModal')
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
                        jQuery('#GrnModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formIdnew == undefined || formIdnew == "") {
                        function nextFn() {
                            document.getElementById("commonGrnForm").reset();
                            const form = document.getElementById("commonGrnForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            jQuery('#grn_sequence').focus();
                            resetFieds();
                            getLatestGRNNo();
                            jQuery('#GrnModal').find('#pending_btn').prop('disabled', true);
                        }

                        if (data.url != "") {
                            toastSuccessPreview(data.response_message, data.url, nextFn);
                        } else {
                            toastSuccess(data.response_message, nextFn);
                        }
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#GrnModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#GrnModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#GrnModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#GrnModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#GrnModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        toastr.error('Please Add At Least One GRN Details.');

        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#GrnModal').find('#submitbtn').prop('disabled', false);
    }
});

$('#GrnDetailsForm').on('submit', function (e) {
    e.preventDefault();
    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    var data = new FormData(document.getElementById('GrnDetailsForm'));
    var formValue = Object.fromEntries(data.entries());
    var thisModal = jQuery('#GrnDetailsModal');



    var grnd_qty = formValue.grnd_qty ? parseFloat(formValue.grnd_qty) : 0;
    var grnd_rate_unit = formValue.grnd_rate_unit ? parseFloat(formValue.grnd_rate_unit) : 0;
    var pending_qty = formValue.pending_qty ? parseFloat(formValue.pending_qty) : 0;

    var grn_type_id = jQuery('#commonGrnForm').find('input[name*="grn_type_id"]:checked').val();

    if (grnd_qty > pending_qty && grn_type_id == "Against DC") {
        toastr.error('GRN Qty. Cannot Be Greater Than Pend. PO / DC Qty. ' + pending_qty.toFixed(3) + '.');
        return;
    };


    var used_qty = parseFloat(jQuery('#grnd_qty').attr('min')) || 0;
    if (used_qty > grnd_qty) {
        toastr.error('GRN Qty. Cannot Be Less Than ' + used_qty.toFixed(3));
        return;
    }


    if (grnd_qty < 0.001) {
        toastr.error('Enter GRN Qty. greater than 0.001.');
        return;
    }
    if (grnd_rate_unit < 0.01) {
        toastr.error('Enter Rate / Unit  greater than 0.01.');
        return;
    }

    let lastCalDate = jQuery('#ins_last_cali_date').val();
    let nextCalDueDate = jQuery('#ins_next_cali_due_date').val();
    let isCaliRequired = jQuery('#ins_cali_freq').prop('required');

    if (isCaliRequired && lastCalDate && nextCalDueDate) {
        let lastDate = parseDate(lastCalDate);
        let nextDate = parseDate(nextCalDueDate);

        if (nextDate < lastDate) {
            toastr.error('Next Calibration Due Date must be greater than Last Calibration Date.');
            return;
        }
    }

    if (formValue.grnd_item_id.trim()) {
        var noDuplicate = true;
        var grn_type_id = jQuery('#commonGrnForm').find("input[name*='grn_type_id']:checked").val();
        if (grn_type_id == 'Manual') {

            var item_id = formValue.grnd_item_id ? formValue.grnd_item_id : null;
            var currentIndex = formValue.form_type === "edit" ? formValue.form_index : null;
            jQuery.each(grn_details_data, function (index, item) {
                if (item.grnd_item_id == item_id) {
                    if (currentIndex === null || index != currentIndex) {
                        noDuplicate = false;
                        return false;
                    }
                }
            });
        }
        if (noDuplicate) {
            var table_unique_id = formValue.table_unique_id ? formValue.table_unique_id : "";
            var table_pk_id = formValue.table_pk_id ? formValue.table_pk_id : "";

            var po_no = (grn_type_id == 'Against PO') ? (formValue.po_no || "") : "";
            var po_date = (grn_type_id == 'Against PO') ? (formValue.po_date || "") : "";
            var sup_dc_number = (grn_type_id == 'Against DC') ? (formValue.sup_dc_number || "") : "";
            var sup_dc_date = (grn_type_id == 'Against DC') ? (formValue.sup_dc_date || "") : "";

            var grnd_item_id = formValue.grnd_item_id ? formValue.grnd_item_id : null;
            var item = formValue.item_name ? formValue.item_name : thisModal.find('#grnd_item_id option[value="' + grnd_item_id + '"]').text();

            var item_group = formValue.item_group ? formValue.item_group : "";
            var main_group = formValue.main_group ? formValue.main_group : "";
            var sr_no = formValue.sr_no ? formValue.sr_no : "";
            var pending_qty = formValue.pending_qty ? parseFloat(formValue.pending_qty).toFixed(3) : "";
            var grnd_qty = formValue.grnd_qty ? parseFloat(formValue.grnd_qty).toFixed(3) : "";
            var grnd_rate_unit = formValue.grnd_rate_unit ? parseFloat(formValue.grnd_rate_unit).toFixed(2) : "";
            var unit = thisModal.find('#grnd_qty_unit').text();
            var grnd_amount = formValue.grnd_amount ? parseFloat(formValue.grnd_amount).toFixed(2) : null;

            var grnd_remark = formValue.grnd_remark ? formValue.grnd_remark : "";


            if (item != "") {
                if (formValue.form_type == "edit") {

                    formValue.item_name = item;
                    // po_details_data[formValue.form_index] = formValue;
                    grn_details_data[formValue.form_index] = {
                        ...grn_details_data[formValue.form_index],
                        ...formValue
                    };
                    var in_use = grn_details_data[formValue.form_index].in_use == true ? true : false;
                    let tblHtml = ``;
                    tblHtml += `<td>`;
                    tblHtml += in_use == true ? DetailsActionDropdown('editGrnDetails') : DetailsActionDropdown('editGrnDetails', 'removeGrnDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                    </td>`;
                    tblHtml += `<td>${po_no}</td>`;
                    tblHtml += `<td>${po_date}</td>`;
                    tblHtml += `<td>${sup_dc_number}</td>`;
                    tblHtml += `<td>${sup_dc_date}</td>`;
                    tblHtml += `<td>${item}</td>`;
                    tblHtml += `<td>${item_group}</td>`;
                    tblHtml += `<td>${main_group}</td>`;
                    tblHtml += `<td>${sr_no}</td>`;
                    tblHtml += `<td>${pending_qty}</td>`;
                    tblHtml += `<td>${grnd_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${grnd_rate_unit}</td>`;
                    tblHtml += `<td class="amount" data-val="${grnd_amount}">${grnd_amount}</td>`;
                    tblHtml += `<td>${grnd_remark}</td>`;

                    tblHtml += `</tr>`;
                    jQuery('#GrnDetailTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
                } else {
                    formValue.unit = unit;
                    grn_details_data.push(formValue)
                    if (grn_details_data.length > 0) {
                        setRadioReadonly("input[name='grn_type_id']", true);
                    }
                    let formIndx = grn_details_data.indexOf(formValue);
                    if (jQuery('#GrnDetailTable tbody').find('#noDetails').length > 0) {
                        jQuery('#GrnDetailTable tbody').empty();
                    }

                    let tblHtml = `<tr>`;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editGrnDetails', 'removeGrnDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
                    </td>`;
                    tblHtml += `<td>${po_no}</td>`;
                    tblHtml += `<td>${po_date}</td>`;
                    tblHtml += `<td>${sup_dc_number}</td>`;
                    tblHtml += `<td>${sup_dc_date}</td>`;
                    tblHtml += `<td>${item}</td>`;
                    tblHtml += `<td>${item_group}</td>`;
                    tblHtml += `<td>${main_group}</td>`;
                    tblHtml += `<td>${sr_no}</td>`;
                    tblHtml += `<td>${pending_qty}</td>`;
                    tblHtml += `<td>${grnd_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${grnd_rate_unit}</td>`;
                    tblHtml += `<td class="amount" data-val="${grnd_amount}">${grnd_amount}</td>`;
                    tblHtml += `<td>${grnd_remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#GrnDetailTable tbody').append(tblHtml);
                    toastSuccess("Record Inserted.");
                }
            }
            calculateTotalAmount();
            if (formValue.form_type == "edit") {
                thisModal.modal('hide');
            } else {
                let formElement = document.getElementById('GrnDetailsForm');
                formElement.reset();
                jQuery('#grnd_item_id').val('').trigger('change.select2');
                jQuery('#grnd_desciption').val('');
                jQuery('#pend_po_qty').val('');
                jQuery('#pod_po_qty').val('');
                jQuery('#grnd_qty').val('');
                jQuery('#grnd_rate_unit').val('');
                jQuery('#grnd_amount').val('');
                jQuery('#grnd_remark').val('');
                jQuery('#pending_qty_unit').text('').removeClass('ms-1');
                jQuery('#grnd_qty_unit').text('').removeClass('ms-1');
                jQuery('#calibration_certificate_doc').val('');
                jQuery('#calibration_certificate').val('');
                jQuery('#calibration_certificate_prev').attr('href', '#').addClass('hide');
                jQuery('#calibration_certificate_remove').removeClass('i-block').addClass('hide');


                setTimeout(function () {

                    let $select = jQuery('#GrnDetailsForm #grnd_item_id');
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
            toastr.error("Duplicate Item Found.");
        }
    } else {
        toastr.error('Please Select Item');
    }
});

function calculateTotalAmount() {
    var total = 0;

    jQuery('#GrnDetailTable tbody tr').each(function () {
        var amount = jQuery(this).find('td.amount').text();
        // var amountatt= jQuery('.amount')
        // console.log(amount,"amount");
        amount = parseFloat(amount) || 0;
        total += amount;

    });


    if (total != 0) {
        jQuery('#totalAmount').text(total.toFixed(2));
        jQuery('#commonGrnForm #grn_total_amount').val(total.toFixed(2));
    } else {
        jQuery('#totalAmount').text((0).toFixed(2));
        jQuery('#commonGrnForm #grn_total_amount').val((0).toFixed(2));
    }
}

jQuery('#GrnDetailsModal').on('show.bs.modal', function () {
    let thisModal = jQuery('#GrnDetailsModal');
    var grnTypeId = jQuery('#commonGrnForm').find('input[name*="grn_type_id"]:checked').val();
    var details_id = thisModal.find("#grnd_id").val();
    var form_type = thisModal.find("#form_type").val();
    if (grnTypeId == "Against PO" || grnTypeId == "Against DC" || (form_type == "edit" && details_id != 0)) {
        jQuery('#grnd_item_id').addClass('skip-tab');
    } else {
        jQuery('#grnd_item_id').removeClass('skip-tab');
        setTimeout(function () {
            let sel = jQuery('#grnd_item_id')
                .next('.select2-container')
                .find('.select2-selection');
            sel.attr('tabindex', 0).focus();
        }, 20);
    }
    var mode = jQuery("#GrnDetailsModal #form_type").val();
    var grnd_id = jQuery("#GrnDetailsModal #grnd_id").val();
    if (mode == "add" && grnd_id == "0") {
        jQuery('#GrnDetailsModal #grnd_po_qty').removeAttr('min');
    }
    if (mode == "add") {
        toggleCalibrationFields(grnTypeId, 'No');
        thisModal.find('#calibration_certificate_doc').val('');
        thisModal.find('#calibration_certificate').val('');
        thisModal.find('#calibration_certificate_prev').attr('href', '#').addClass('hide');
        thisModal.find('#calibration_certificate_remove').removeClass('i-block').addClass('hide');
    }

});

jQuery('#GrnPendingModal').on('show.bs.modal', function (e) {
    var dt = jQuery('#pendingPODataTable').DataTable();
    fixDataTableColumnsUntilAdjusted(dt);
    var usedParts = [];
    if (grn_details_data && grn_details_data.length > 0) {
        grn_details_data.forEach(function (item) {
            if (item.table_pk_id) {
                usedParts.push(Number(item.table_pk_id));
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
    jQuery('#pendingPODataTable tbody tr').each(function (indx) {
        totalEntry++;
        var checkField = jQuery(this).find('input[name="pod_id[]"]');
        var partId = jQuery(checkField).val();
        var inUse = isUsed(partId);

        if (formId == undefined) {
            if (grn_details_data.length > 0) {
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
jQuery('#GrnPendingModal').on('hide.bs.modal', function (e) {
    selectedRows = {};
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('grn_challan_number');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }
});
jQuery('#GrnSupplierDCPendingModal').on('show.bs.modal', function (e) {
    var dt = jQuery('#pendingSupplierDCDataTable').DataTable();
    fixDataTableColumnsUntilAdjusted(dt);
    var usedParts = [];
    if (grn_details_data && grn_details_data.length > 0) {
        grn_details_data.forEach(function (item) {
            if (item.table_pk_id) {
                usedParts.push(Number(item.table_pk_id));
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
    jQuery('#pendingSupplierDCDataTable tbody tr').each(function (indx) {
        totalEntry++;
        var checkField = jQuery(this).find('input[name="sup_dcd_id[]"]');
        var partId = jQuery(checkField).val();
        var inUse = isUsed(partId);

        if (formId == undefined) {
            if (grn_details_data.length > 0) {
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
jQuery('#GrnSupplierDCPendingModal').on('hide.bs.modal', function (e) {
    selectedRows = {};

    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('grn_challan_number');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }
});



jQuery('#GrnDetailsModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#GrnDetailsModal');
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    thisModal.find("#grnd_id").val("");
    thisModal.find("#table_unique_id").val("");
    thisModal.find("#table_pk_id").val("");
    thisModal.find("#sr_table_unique_id").val("");
    thisModal.find("#sr_table_pk_id").val("");

    jQuery(this).find('#grnd_item_id option.temp-item').remove();
    jQuery('#GrnDetailsForm').trigger("reset");
    thisModal.find('#calibration_certificate_doc').val('');
    thisModal.find('#calibration_certificate').val('');
    thisModal.find('#calibration_certificate_prev').attr('href', '#').addClass('hide');
    thisModal.find('#calibration_certificate_remove').removeClass('i-block').addClass('hide');
    thisModal.find("#grnd_qty").attr('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
    this.dataset.customHideFocus = 'true';
    const input = jQuery("#commonGrnForm").find("#mode_of_transport");
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }

    jQuery('#GrnDetailsForm #grnd_qty').removeAttr('min');
    jQuery('#GrnDetailsForm #grnd_qty').removeAttr('max');
});


jQuery('#GrnModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#GrnModal');
    thisModal.find("#id").val("");
    jQuery(this).find('#grn_supplier_id option.temp-supplier').remove();
    resetFieds();
    jQuery('#commonGrnForm').trigger("reset");

});


jQuery('#GrnPendingModal #checkall-po_data').click(function () {
    if (jQuery(this).is(':checked')) {
        jQuery("#pendingPODataTable").find("[id^='pod_ids_']:not(.in-use)").prop('checked', true).trigger('change');
        jQuery("#pendingPODataTable").find("[id^='pod_ids_']").prop('checked', true).trigger('change');
    } else {
        jQuery("#pendingPODataTable").find("[id^='pod_ids_']:not(.in-use)").prop('checked', false).trigger('change');
        jQuery("#pendingPODataTable").find("[id^='pod_ids_']").prop('checked', false).trigger('change');
    }

});

jQuery('#GrnSupplierDCPendingModal #checkall-dc_data').click(function () {
    console.log("checkall-dc_data clicked");
    if (jQuery(this).is(':checked')) {
        jQuery("#pendingSupplierDCDataTable").find("[id^='sup_dcd_ids_']:not(.in-use)").prop('checked', true).trigger('change');
        jQuery("#pendingSupplierDCDataTable").find("[id^='sup_dcd_ids_']").prop('checked', true).trigger('change');
    } else {
        jQuery("#pendingSupplierDCDataTable").find("[id^='sup_dcd_ids_']:not(.in-use)").prop('checked', false).trigger('change');
        jQuery("#pendingSupplierDCDataTable").find("[id^='sup_dcd_ids_']").prop('checked', false).trigger('change');
    }

});

jQuery('#resetbtn').on('click', function () {
    po_details_data = [];
    selectedRows = {};


    var formId = jQuery('#GrnModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonGrnForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        resetFieds();
        jQuery('#commonGrnForm').find('#prepared_by_user_id').val(loginUserId).trigger("change.select2");
        getLatestGRNNo();
    } else {
        fetchAndFillGRNSupplier(formId);
    }
});

jQuery('#GrnModal').on('click', '#add_new', function () {

    document.getElementById("commonGrnForm").reset();
    const form = document.getElementById("commonGrnForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    resetFieds();

    getLatestGRNNo();
    jQuery('#GrnModal').find("#grn_supplier_id").removeClass('skip-tab');
    jQuery('#commonGrnForm').find('input[name="id"]').val('');
    setSelect2Readonly("#grn_supplier_id", false);

    jQuery('#GrnModal').find('#add_new').hide();
    jQuery('#GrnModal').find('#preview_btn').hide();
    jQuery('#commonGrnForm').find('#prepared_by_user_id').val(loginUserId).trigger("change.select2");
});


function checkesCheckboxFristpo($this) {
    var $row = jQuery($this).closest('tr');
    var pod_id = jQuery($this).val();
    if (jQuery($this).is(':checked')) {
        selectedRows[pod_id] = $row;
    } else {
        delete selectedRows[pod_id];
    }
}

function checkesCheckboxFrist($this) {
    var $row = jQuery($this).closest('tr');
    var sup_dcd_id = jQuery($this).val();
    if (jQuery($this).is(':checked')) {
        selectedRows[sup_dcd_id] = $row;
    } else {
        delete selectedRows[sup_dcd_id];
    }
}

function resetFieds() {

    jQuery('#GrnModal').find('#grn_sequence').prop('readonly', false);
    jQuery("#grn_supplier_id").val('').trigger('change');
    setRadioReadonly("input[name='grn_type_id']", false);
    jQuery('#GrnModal').find('input[name*="grn_type_id"][value="Against PO"]').prop('checked', true).change().focus();
    jQuery("#commonGrnForm .toggleModalBtn").prop('disabled', true);
    jQuery("#GrnDetailsForm #pod_pid_id").val('');
    jQuery("#GrnDetailsForm #grnd_id").val('');
    jQuery("#GrnDetailsForm #table_unique_id").val('');
    jQuery("#GrnDetailsForm #table_pk_id").val('');
    jQuery("#GrnDetailsForm #sr_table_unique_id").val('');
    jQuery("#GrnDetailsForm #sr_table_pk_id").val('');
    jQuery("#grnd_item_id").val("").removeClass('skip-tab');
    grn_details_data = [];
    selectedRows = {};
    jQuery('#GrnDetailTable tbody').empty();
    jQuery("#totalAmount").text("");
    jQuery('#grn_supplier_id option.temp-supplier').remove();


}

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
            jQuery('#GrnDetailsModal').find('#submitbtn').prop('disabled', true);
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
                    jQuery('#GrnDetailsModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#GrnDetailsModal').find('#submitbtn').prop('disabled', false);
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
        jQuery('#calibration_certificate_doc').val('');
        jQuery('#calibration_certificate').val('');
        jQuery('#calibration_certificate_prev').attr('href', '#').addClass('hide');
        jQuery('#calibration_certificate_remove').removeClass('i-block').addClass('hide');
    }
});

function toggleCalibrationFields(grnTypeId, forCalibration) {
    var formId = jQuery('#commonGrnForm').find('input[name="id"]').val();
    let isEditable = (grnTypeId === 'Against DC' && forCalibration === 'Yes' && (!formId || formId === ""));

    let thisForm = jQuery('#GrnDetailsModal');
    if (isEditable) {
        thisForm.find('#ins_cali_freq').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
        thisForm.find('#ins_last_cali_date').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex').css('pointer-events', 'auto');
        thisForm.find('#ins_last_cali_date').datepicker('enable');
        thisForm.find('#ins_next_cali_due_date').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex').css('pointer-events', 'auto');
        thisForm.find('#ins_next_cali_due_date').datepicker('enable');
        thisForm.find('#calibration_certificate').prop('disabled', false).removeClass('skip-tab').removeAttr('tabindex');

        // Add asterisks for required fields
        jQuery('label[for="ins_cali_freq"], label[for="ins_last_cali_date"], label[for="ins_next_cali_due_date"]').find('.astric').removeClass('hide d-none');
        thisForm.find('#ins_cali_freq').prop('required', true);
        thisForm.find('#ins_last_cali_date').prop('required', true);
        thisForm.find('#ins_next_cali_due_date').prop('required', true);
    } else {
        thisForm.find('#ins_cali_freq').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
        thisForm.find('#ins_last_cali_date').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1').css('pointer-events', 'none');
        thisForm.find('#ins_last_cali_date').datepicker('disable');
        thisForm.find('#ins_next_cali_due_date').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1').css('pointer-events', 'none');
        thisForm.find('#ins_next_cali_due_date').datepicker('disable');
        thisForm.find('#calibration_certificate').prop('disabled', true).addClass('skip-tab').attr('tabindex', '-1');

        // Remove asterisks and required
        jQuery('label[for="ins_cali_freq"], label[for="ins_last_cali_date"], label[for="ins_next_cali_due_date"]').find('.astric').addClass('hide d-none');
        thisForm.find('#ins_cali_freq').prop('required', false).removeClass('is-invalid');
        thisForm.find('#ins_last_cali_date').prop('required', false).removeClass('is-invalid');
        thisForm.find('#ins_next_cali_due_date').prop('required', false).removeClass('is-invalid');
    }
}

function validateImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.pdf)$/i;
    return allowedExtensions.test(filePath);
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