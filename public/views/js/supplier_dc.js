var supdc_details_data = [];
var formId = jQuery('#commonSupplierDCForm').find('input[name="id"]').val();
let selectedRows = {};
$.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
    var row = $('#pendingSupplierDCDataTable').DataTable().row(dataIndex).node();
    var checkbox = $(row).find("input[type='checkbox']");
    if (checkbox.is(':checked')) {
        return true;
    }
    return true;
});

// Edit purchase Indent row click
jQuery('#dyntable tbody').on('click', '.edit-supplier_dc', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#SupplierDCModal').find('#id').val(data["sup_dc_id"]);
    if (data && data["sup_dc_id"]) {
        fetchAndFillSupplierDC(data["sup_dc_id"]);
    }
});

// Function to fetch and fill data in edit mode
function fetchAndFillSupplierDC(id) {
    if (!id) return;
    jQuery('#SupplierDCModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-supplier_dc",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.supplier_dc_data != null) {
                jQuery('#SupplierDCModal').find('#id').val(data.supplier_dc_data.sup_dc_id != "" ? data.supplier_dc_data.sup_dc_id : "");
                let url = checkFileRoute + "?id=" + data.supplier_dc_data.sup_dc_id + "&name=" + data.supplier_dc_data.pdf_name + "&type=supplier_dc";
                var sup_dc_type_id = data.supplier_dc_data.sup_dc_type_id;
                jQuery('#SupplierDCModal').find('input[name*="sup_dc_type_id"][value="' + sup_dc_type_id + '"]').prop('checked', true);
                jQuery('#SupplierDCModal').find('input[name*="sup_dc_type_id"][value="' + sup_dc_type_id + '"]').change();
                setRadioReadonly("input[name='sup_dc_type_id']", true);

                jQuery('#preview_btn').attr('href', url).show();
                jQuery('#SupplierDCModal').find('#sup_dc_number').val(data.supplier_dc_data.sup_dc_number != "" ? data.supplier_dc_data.sup_dc_number : "");
                jQuery('#SupplierDCModal').find('#sup_dc_date').val(data.supplier_dc_data.sup_dc_date != "" ? data.supplier_dc_data.sup_dc_date : "");
                jQuery('#SupplierDCModal').find('#sup_dc_sequence').val(data.supplier_dc_data.sup_dc_sequence != "" ? data.supplier_dc_data.sup_dc_sequence : "");
                jQuery('#SupplierDCModal').find('#ref_no_date').val(data.supplier_dc_data.ref_no_date != "" ? data.supplier_dc_data.ref_no_date : "");

                getPendingSuppliersForSupplierDC().done(function () {

                    let supplierId = data.supplier_dc_data.supplier_id;
                    let supplierText = data.supplier_dc_data.supplier_name;
                    let $supplier = jQuery('#SupplierDCModal').find('#supplier_id');
                    if ($supplier.find("option[value='" + supplierId + "']").length === 0) {
                        $supplier.append(
                            `<option class="temp-supplier" value="${supplierId}" selected>${supplierText}</option>`
                        );
                    }
                    $supplier.val(supplierId).trigger('change.select2');
                    $supplier.addClass('skip-tab').prop({ tabindex: -1, readonly: true });


                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                });
                setSelect2Readonly('#supplier_id', true);
                jQuery("#supplier_id").addClass('skip-tab');

                jQuery('#SupplierDCModal').find('#mode_of_transport').val(data.supplier_dc_data.mode_of_transport != "" ? data.supplier_dc_data.mode_of_transport : "");
                jQuery('#SupplierDCModal').find('#transporter').val(data.supplier_dc_data.transporter != "" ? data.supplier_dc_data.transporter : "");
                jQuery('#SupplierDCModal').find('#vehicle_no').val(data.supplier_dc_data.vehicle_no != "" ? data.supplier_dc_data.vehicle_no : "");
                jQuery('#SupplierDCModal').find('#sp_note').val(data.supplier_dc_data.sp_note != "" ? data.supplier_dc_data.sp_note : "");

                jQuery('#SupplierDCModal').find('#prepared_by_user_id').val(data.supplier_dc_data.prepared_by_user_id).trigger('change.select2');
                if (data.supplier_dc_details_data != "" && data.supplier_dc_details_data.length > 0) {
                    supdc_details_data = data.supplier_dc_details_data.map(item => {
                        item.mode = "Update";
                        return item;
                    });
                    fillSupDCDetailsTable();
                }

                jQuery('#SupplierDCModal').find('#pending_btn').prop('disabled', true);
                jQuery('#SupplierDCModal').find('#submitbtn').prop('disabled', false);

                const form = document.getElementById("SupplierDCModal");
                if (form) form.classList.remove('was-validated');
                jQuery('#SupplierDCModal').find('#add_new').show();
                jQuery('#SupplierDCModal').find('#preview_btn').show();

                if (data.supplier_dc_data.in_use == true) {
                    jQuery('#SupplierDCModal').find('#sup_dc_sequence').prop('readonly', true);
                    let nextInput = jQuery('#SupplierDCModal').find('#sup_dc_date');
                    if (nextInput.hasClass('trans-date-picker') || nextInput.hasClass('date-picker')) {
                        nextInput.one('focus', function () {
                            setTimeout(() => {
                                jQuery(this).datepicker('hide');
                            }, 50);
                        });
                    }
                    nextInput.focus();
                } else {
                    jQuery('#SupplierDCModal').find('#sup_dc_sequence').prop('readonly', false);
                    jQuery('#SupplierDCModal').find('#sup_dc_sequence').focus();
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
            if (!jQuery('#SupplierDCModal').find('#sup_dc_sequence').prop('readonly')) {
                jQuery('#SupplierDCModal').find('#sup_dc_sequence').focus();
            }
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        }
    });
}

// get the latest number
function getLatestSupplierDCNo() {
    jQuery.ajax({
        url: "get-latest_supplier_dc_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#sup_dc_number').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#sup_dc_sequence').val(data.number);
                jQuery('#sup_dc_date').val(currentDate);
                setSelect2Readonly('#supplier_id', false);
                jQuery("#supplier_id").removeClass('skip-tab');
            } else {
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            jQuery('#sup_dc_number').removeClass('file-loader');
            console.log('Field To Get Latest DC No.!')
        }
    });
}


jQuery('input[name*="sup_dc_type_id"]').on('change', function () {
    getPendingSuppliersForSupplierDC();
    var sup_dc_type_id = jQuery('#commonSupplierDCForm').find("input[name*='sup_dc_type_id']:checked").val();
    if (sup_dc_type_id == 'Returnable - Service PO') {
        jQuery('.pending_detail').attr('data-bs-target', '#SupplierDCPendingModal');
        jQuery('#commonSupplierDCForm').find('.add_detail').prop('disabled', true);
    } else {
        jQuery('.pending_detail').attr('data-bs-target', '');
        jQuery('#commonSupplierDCForm').find('.add_detail').prop('disabled', false);
        jQuery('.toggleModalBtn').prop('disabled', true);
    }
});

$(document).on('change', 'input[name*="sup_dc_type_id"]', function () {
    const type = $(this).val();

    const typeMapping = {
        'Non Returnable - Manual': ['general', 'film'],
        'SQIN from Prod. Area': ['film'],
        'Returnable - Manual': ['rt_camera', 'mpt_equipment', 'ut_equipment', 'instrument', 'probe_ut']
    };

    const allowedTypes = typeMapping[type] || null;

    const filteredItems = allowedTypes
        ? allItems.filter(item => allowedTypes.includes(item.item_type))
        : allItems;

    let options = `<option value="">Select Item</option>`;

    options += filteredItems.map(item => {
        let stockQty = (type === 'SQIN from Prod. Area') ? (item.stock_sq_in || 0) : (item.io_stock_qty || '');
        let stockUnit = (type === 'SQIN from Prod. Area') ? 'SQIN' : item.unit;
        return `
        <option value="${item.id}"
            data-item_group="${item.item_group}"
            data-main_group="${itemTypeMap[item.item_type] || ''}"
            data-io_stock_qty="${stockQty}"
            data-unit="${stockUnit}">
            ${item.item_name}
        </option>
    `}).join('');

    $('#item_id')
        .select2('destroy')
        .html(options)
        .select2();
});

function getPendingSuppliersForSupplierDC() {
    var sup_dc_type_id = jQuery('#commonSupplierDCForm').find("input[name*='sup_dc_type_id']:checked").val();

    var formId = jQuery('#commonSupplierDCForm').find('input[name="id"]').val();

    if (formId != undefined && formId != "") {
        var Url = 'get-pending_supplier_for_supplier_dc?sup_dc_type_id=' + sup_dc_type_id + "&id=" + formId;
    } else {
        var Url = 'get-pending_supplier_for_supplier_dc?sup_dc_type_id=' + sup_dc_type_id;
    }

    if (sup_dc_type_id != "" && sup_dc_type_id != undefined) {
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
                    for (let indx in data.get_ser_po_supplier) {
                        suppHtml += `<option value="${data.get_ser_po_supplier[indx].id}">${data.get_ser_po_supplier[indx].supplier_name}</option>`;
                    }

                    jQuery('#commonSupplierDCForm').find('#supplier_id').empty().append(suppHtml);

                } else {
                    console.log(data.response_message)
                }
            },
        });

    } else {
        jQuery('#commonSupplierDCForm').find('#supplier_id').empty().append(suppHtml);
    }
}


$('#supplier_id').on('change', function () {
    fillPendingSupplierDC();
});


function fillPendingSupplierDC() {
    let supId = jQuery('#supplier_id option:selected').val();
    var sup_dc_type_id = jQuery('#commonSupplierDCForm').find("input[name*='sup_dc_type_id']:checked").val();

    var thisForm = jQuery('#SupplierDCDetailsForm');

    var formId = jQuery('#commonSupplierDCForm').find('input[name="id"]').val();


    if (supId != "" && sup_dc_type_id == 'Returnable - Service PO') {
        if (formId != undefined && formId != '') {
            var Url = "get-pending_service_po_list_for_supplier_dc?supplier_id=" + supId + "&id=" + formId;
        } else {
            var Url = "get-pending_service_po_list_for_supplier_dc?supplier_id=" + supId;
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

                    thisForm.find('#SupplierDCDetailTable tbody input[name="form_indx"]').each(function (indx) {
                        let frmIndx = jQuery(this).val();

                        let jbEorkOrderId = dc_data[frmIndx].ser_pod_id;
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
                            var inUse = isUsed(data.dc_data[idx].ser_pod_id);
                            var in_use = data.dc_data[idx].in_use == true ? 'readonly' : '';
                            totalEntry++;
                            tblHtml += `
                                    <tr>
                                        <td><input type="checkbox" name="ser_pod_id[]" class="simple-check checkbox-filter-remove ${inUse ? 'in-use' : ''}" id="ser_pod_ids_${data.dc_data[idx].ser_pod_id}" value="${data.dc_data[idx].ser_pod_id}" ${inUse ? 'checked' : ''} ${in_use}/></td>
                                        <td>${data.dc_data[idx].ser_po_number}</td>
                                        <td>${data.dc_data[idx].ser_po_date}</td>
                                        <td>${data.dc_data[idx].purpose ?? ''}</td>
                                        <td>${data.dc_data[idx].ref_no_date != null && data.dc_data[idx].ref_no_date != undefined ? data.dc_data[idx].ref_no_date : ''}</td>
                                        <td>${data.dc_data[idx].bill_to}</td>
                                        <td>${data.dc_data[idx].for_location}</td>
                                        <td>${data.dc_data[idx].item_name}</td>
                                        <td>${data.dc_data[idx].item_group}</td>
                                        <td>${data.dc_data[idx].main_group}</td>
                                        <td>${data.dc_data[idx].name_for_display}</td>
                                        <td>${parseFloat(data.dc_data[idx].pending_qty).toFixed(3)}</td>
                                        <td>${data.dc_data[idx].unit}</td>
                                        <td>${data.dc_data[idx].remark != null && data.dc_data[idx].remark != undefined ? data.dc_data[idx].remark : ""}</td>
                                        <td>${data.dc_data[idx].prepared_by}</td>
                                    </tr>`;

                        }

                    } else {

                        tblHtml += `<tr class="centeralign" id="noPendingPo">
                                        <td colspan="14">No Pending Service PO Available</td>
                                    </tr>`;

                    }



                    var $table = jQuery("#SupplierDCPendingModal").find('#pendingSupplierDCDataTable');
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

                    if (sup_dc_type_id == 'Manual') {
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
$('#addPendinSupplierDCForm').on('submit', function (e) {

    e.preventDefault();

    jQuery('#full-page-loader')
        .removeClass('hidden-loader')
        .addClass('loader-progress-whole-page');

    jQuery('#SupplierDCPendingModal')
        .find('#submitbtn')
        .prop('disabled', true);

    let chkArr = [];
    var formId = jQuery('#commonSupplierDCForm').find('input[name="id"]').val();

    jQuery("#addPendinSupplierDCForm")
        .find("[id^='ser_pod_ids_']:checked")
        .each(function () {
            chkArr.push(jQuery(this).val());
        });

    // No checkbox selected
    if (chkArr.length === 0) {
        toastr.error('Select Service PO From Pending.');

        jQuery('#full-page-loader')
            .removeClass('loader-progress-whole-page')
            .addClass('hidden-loader');

        jQuery('#SupplierDCPendingModal')
            .find('#submitbtn')
            .prop('disabled', false);

        return;
    }

    if (formId != undefined && formId != "") {
        var pend_url = "get-pending_service_po_for_supplier_dc?id=" + formId;
    } else {
        var pend_url = "get-pending_service_po_for_supplier_dc";
    }

    jQuery.ajax({
        url: pend_url,
        type: 'GET',
        data: { ser_pod_ids: chkArr.join(',') },
        dataType: 'json',
        success: function (data) {

            if (data.response_code == 1) {

                if (data.dc_data && data.dc_data.length > 0) {
                    supdc_details_data = [];
                    for (let ind in data.dc_data) {
                        let row = {
                            ...data.dc_data[ind],
                            mode: "Insert",
                            return_qty: 1
                        };

                        supdc_details_data.push(row);
                    }

                    fillSupDCDetailsTable(data.dc_data);
                } else {
                    fillSupDCDetailsTable([]);
                }

                jQuery("#SupplierDCPendingModal").modal('hide');
                setRadioReadonly('input[name*="sup_dc_type_id"]', true);
                jQuery("#commonSupplierDCForm").find('supplier_id').addClass('skip-tab');
                setSelect2Readonly('#supplier_id', true);
            } else {
                fillSupDCDetailsTable([]);
            }

            jQuery('#SupplierDCPendingModal')
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

            jQuery('#SupplierDCPendingModal')
                .find('#submitbtn')
                .prop('disabled', false);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');
        }
    });
});


function fillSupDCDetailsTable() {
    var sup_dc_type_id = jQuery('#commonSupplierDCForm').find("input[name*='sup_dc_type_id']:checked").val();

    let thisModal = jQuery('#SupplierDCDetailsModal');
    let tblHtml = ``;
    if (supdc_details_data.length > 0) {
        for (let key in supdc_details_data) {
            if (supdc_details_data[key].mode == "Delete") {
                continue;
            }

            var formIndx = supdc_details_data.indexOf(supdc_details_data[key]);

            if (sup_dc_type_id == 'Returnable - Service PO') {
                var ser_po_number = supdc_details_data[key].ser_po_number ? supdc_details_data[key].ser_po_number : "";
                var ser_po_date = supdc_details_data[key].ser_po_date ? supdc_details_data[key].ser_po_date : "";

            } else {
                var ser_po_number = "";
                var ser_po_date = "";
            }

            if (supdc_details_data[key].item_name != '' && supdc_details_data[key].item_name != undefined) {
                var item_name = supdc_details_data[key].item_name;
            } else {
                var item_name = item_id != '' ? thisModal.find('#item_id option[value="' + item_id + '"]').text() : '';
            }

            var item_group = supdc_details_data[key].item_group ? supdc_details_data[key].item_group : "";
            var main_group = supdc_details_data[key].main_group ? supdc_details_data[key].main_group : "";
            var sr_no = supdc_details_data[key].name_for_display ? supdc_details_data[key].name_for_display : "";
            var sr_table_pk_id = supdc_details_data[key].sr_table_pk_id ? supdc_details_data[key].sr_table_pk_id : '';

            var pending_qty = '';
            if (ser_po_number != "") {
                pending_qty = supdc_details_data[key].pending_qty ? parseFloat(supdc_details_data[key].pending_qty).toFixed(3) : "";
            } else {
                pending_qty = '';
            }


            var return_qty = supdc_details_data[key].return_qty ? parseFloat(supdc_details_data[key].return_qty).toFixed(3) : parseFloat(0).toFixed(3);

            var io_stock_qty = supdc_details_data[key].io_stock_qty ? parseFloat(supdc_details_data[key].io_stock_qty).toFixed(3) : parseFloat(0).toFixed(3);

            var remark = supdc_details_data[key].remark ? supdc_details_data[key].remark : "";
            var in_use = supdc_details_data[key].in_use == true ? true : false;
            var unit = supdc_details_data[key].unit != "" ? supdc_details_data[key].unit : '';

            var aerb_no = supdc_details_data[key].aerb_no != "" && supdc_details_data[key].aerb_no != undefined ? supdc_details_data[key].aerb_no : "";

            var application_no = supdc_details_data[key].application_no != "" && supdc_details_data[key].application_no != undefined ? supdc_details_data[key].application_no : "";

            var validity = supdc_details_data[key].validity != "" && supdc_details_data[key].validity != undefined ? supdc_details_data[key].validity : "";

            var movement_approval_doc = supdc_details_data[key].movement_approval_doc != "" && supdc_details_data[key].movement_approval_doc != undefined ? supdc_details_data[key].movement_approval_doc : "";



            tblHtml += `<tr>`;
            tblHtml += `<td>`;
            tblHtml += in_use == true ? DetailsActionDropdown('editSupDCDetails') : DetailsActionDropdown('editSupDCDetails', 'removeSupDCDetails');
            tblHtml += ` <input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;
            tblHtml += `<td>${ser_po_number}</td>`;
            tblHtml += `<td>${ser_po_date}</td>`;
            tblHtml += `<td>${item_name}</td>`;
            tblHtml += `<td>${item_group}</td>`;
            tblHtml += `<td>${main_group}</td>`;
            tblHtml += `<td>${sr_no}</td>`;
            tblHtml += `<td>${io_stock_qty}</td>`;
            tblHtml += `<td>${pending_qty}</td>`;
            tblHtml += `<td>${return_qty}</td>`;
            tblHtml += `<td>${unit}</td>`;
            tblHtml += `<td>${aerb_no}</td>`;
            tblHtml += `<td>${application_no}</td>`;

            if (movement_approval_doc != "") {
                let fullImagePath = uploadURL + movement_approval_doc;
                tblHtml += `<td style="text-align:center; vertical-align:middle;">
                    <a target="_blank" href="${fullImagePath}" title="View" style="font-size:18px; display:inline-block;">
                        <i class="ri-eye-fill"></i>
                    </a>
                    <input type='hidden' name='movement_approval_doc[]' value="${movement_approval_doc}"/>
                </td>`;
            } else {
                tblHtml += `<td style="text-align:center; vertical-align:middle;">
                    <input type='hidden' name='movement_approval_doc[]' value=""/>
                </td>`;
            }
            tblHtml += `<td>${validity}</td>`;
            tblHtml += `<td>${remark}</td>`;
            tblHtml += `</tr>`;
        }
        jQuery('#SupplierDCDetailTable tbody').empty();
        jQuery('#SupplierDCDetailTable tbody').append(tblHtml);
    }

}

// edit Supplier DC details
function editSupDCDetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();
    fillSupDCDetailsForm(formIndx, rawIndx);
}

// fill Supplier DC details form edit
function fillSupDCDetailsForm(formIndx, rawIndx) {
    var sup_dc_type_id = jQuery('#commonSupplierDCForm').find("input[name*='sup_dc_type_id']:checked").val();
    let thisForm = jQuery('#SupplierDCDetailsModal');
    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    thisForm.find('#grnd_item_id option.temp-item').remove();
    var frmData = supdc_details_data[formIndx];
    let itemId = frmData.item_id;
    let itemText = frmData.item_name;
    let sr_table_pk_id = frmData.sr_table_pk_id;
    let name_for_display = frmData.name_for_display;

    if (thisForm.find("#item_id option[value='" + itemId + "']").length === 0) {
        let itemUnit = (sup_dc_type_id == 'SQIN from Prod. Area') ? 'SQIN' : frmData.unit;
        thisForm.find("#item_id").append(
            `<option  class="temp-item" value="${itemId}" data-main_group="${frmData.main_group}" data-item_group="${frmData.item_group}" data-io_stock_qty="${frmData.io_stock_qty}" data-unit="${itemUnit}" selected>${itemText}</option>`
        );
    }

    thisForm.find("#sup_dcd_id").val(frmData.sup_dcd_id ? frmData.sup_dcd_id : "0");
    thisForm.find("#ser_pod_id").val(frmData.ser_pod_id ? frmData.ser_pod_id : "");
    thisForm.find("#sr_table_unique_id").val(frmData.sr_table_unique_id ? frmData.sr_table_unique_id : "");
    if (sup_dc_type_id == 'Returnable - Service PO') {
        thisForm.find("#ser_po_number").val(frmData.ser_po_number ? frmData.ser_po_number : "");
        thisForm.find("#ser_po_date").val(frmData.ser_po_date ? frmData.ser_po_date : "");
        thisForm.find('#sr_table_pk_id').addClass('skip-tab');
        thisForm.find('#return_qty').addClass('skip-tab').prop('readonly', true);
    } else {
        thisForm.find("#ser_po_number").val("");
        thisForm.find("#ser_po_date").val("");
        thisForm.find('#return_qty').removeClass('skip-tab').prop('readonly', false);
    }
    thisForm.find("#item_id").val(zeroToEmpty(frmData.item_id)).trigger("change", [true]);

    // Populate new fields
    thisForm.find("#aerb_no").val(frmData.aerb_no ? frmData.aerb_no : "");
    thisForm.find("#remark").val(frmData.remark ? frmData.remark : "");
    thisForm.find("#application_no").val(frmData.application_no ? frmData.application_no : "");
    thisForm.find("#validity").val(frmData.validity ? frmData.validity : "");

    if (frmData.for_calibration === 'Yes') {
        thisForm.find("#for_calibration").prop('checked', true);
    } else {
        thisForm.find("#for_calibration").prop('checked', false);
    }

    thisForm.find("#for_calibration").prop('disabled', true);

    if (sup_dc_type_id == 'Returnable - Service PO') {
        jQuery('#for_calibration_row').show();
    } else {
        jQuery('#for_calibration_row').hide();
        thisForm.find("#for_calibration").prop('checked', false);
    }

    if (frmData.movement_approval_doc != "" && frmData.movement_approval_doc != undefined) {
        let fullPath = frmData.movement_approval_doc;
        let fileName = fullPath.split('/').pop();
        thisForm.find("#movement_approval_doc").val(fullPath);
        thisForm.find('#movement_approval_prev').attr('href', uploadURL + fullPath).removeClass('hide');
        thisForm.find('#movement_approval_remove').addClass('i-block').removeClass('hide');
        thisForm.find('#movement_approval_img-prev-box').removeClass('hide');
        thisForm.find('#movement_approval_img-prev').html(fileName);
        let fileInput = thisForm.find('#movement_approval');
        let newFile = new DataTransfer();
        newFile.items.add(new File([""], fileName));
        fileInput[0].files = newFile.files;
    } else {
        thisForm.find('#movement_approval_doc').val('');
        thisForm.find('#movement_approval_prev').attr('href', '#').addClass('hide');
        thisForm.find('#movement_approval_remove').removeClass('i-block').addClass('hide');
        thisForm.find('#movement_approval_img-prev').html('');
        thisForm.find('#movement_approval_img-prev-box').addClass('hide').html('');
        thisForm.find('#movement_approval').val('');
    }
    toggleRequired();

    thisForm.find("#io_stock_qty").val(frmData.io_stock_qty != "" && frmData.io_stock_qty != undefined ? parseFloat(frmData.io_stock_qty).toFixed(3) : parseFloat(0).toFixed(3));
    if (sup_dc_type_id == 'Returnable - Service PO') {
        thisForm.find("#pending_qty").val(frmData.pending_qty != "" && frmData.pending_qty != undefined ? parseFloat(frmData.pending_qty).toFixed(3) : "").attr('readonly', true);
        thisForm.find("#return_qty").attr('max', parseFloat(frmData.pending_qty).toFixed(3));
        thisForm.find("#item_id").addClass('skip-tab');

    } else if (frmData.sup_dcd_id && frmData.sup_dcd_id != 0) {

        thisForm.find("#item_id").addClass('skip-tab');
    }
    else {
        thisForm.find("#pending_qty").val("").attr('readonly', true);
        thisForm.find("#item_id").removeClass('skip-tab');
    }
    var decPlaces = (frmData.main_group == 'Industrial X-Ray Films') ? 0 : 3;
    thisForm.find("#return_qty").val(frmData.return_qty != "" && frmData.return_qty != undefined ? parseFloat(frmData.return_qty).toFixed(decPlaces) : parseFloat(0).toFixed(decPlaces));
    if (frmData.main_group == 'Industrial X-Ray Films') {
        thisForm.find('#return_qty').removeClass('isNumberKey').addClass('isNumberKeyNotDot');
        thisForm.find('#return_qty').attr('onblur', 'formatPoints(this, 0)');
    } else {
        thisForm.find('#return_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        thisForm.find('#return_qty').attr('onblur', 'formatPoints(this, 3)');
    }
    // thisForm.find("#remark").val(frmData.grnd_remark);

    getSrNo(frmData.main_group, frmData.item_id, frmData.sr_table_pk_id).done(function () {
        if (thisForm.find("#sr_table_pk_id option[value='" + sr_table_pk_id + "']").length === 0 && name_for_display != undefined && sr_table_pk_id != 0) {
            thisForm.find("#sr_table_pk_id").append(
                `<option  class="temp-sr_table_pk_id" value="${sr_table_pk_id}" data-sr_table_unique_id="${frmData.sr_table_unique_id}"  selected>${name_for_display}</option>`
            );
        }
        jQuery("#SupplierDCDetailsForm #sr_table_pk_id").val(frmData.sr_table_pk_id ? zeroToEmpty(frmData.sr_table_pk_id) : "").trigger('change.select2');
        jQuery("#SupplierDCDetailsForm #sr_table_unique_id").val(frmData.sr_table_unique_id ? zeroToEmpty(frmData.sr_table_unique_id) : "");
    });

    if (frmData.in_use == true) {

        thisForm.find("#return_qty").attr('min', parseFloat(frmData.used_qty).toFixed(3));
    }

    if ((sup_dc_type_id == "Returnable - Service PO" && frmData.ser_pod_id != 0) || frmData.sup_dcd_id != "") {
        thisForm.find('#item_id').addClass('skip-tab');
        setSelect2Readonly('#item_id', true);
        thisForm.find('#sr_table_pk_id').addClass('skip-tab');
        setSelect2Readonly('#sr_table_pk_id', true);
        thisForm.find('#return_qty').addClass('skip-tab').prop('readonly', true).attr('tabindex', '-1');
    } else {
        thisForm.find('#item_id').removeClass('skip-tab');
        setSelect2Readonly('#item_id', false);
        thisForm.find('#sr_table_pk_id').removeClass('skip-tab');
        setSelect2Readonly('#sr_table_pk_id', false);
        thisForm.find('#return_qty').removeClass('skip-tab').prop('readonly', false).removeAttr('tabindex');
    }

    thisForm.modal('show');
}

function removeSupDCDetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        let item = supdc_details_data[formIndx];

        if (item.sup_dcd_id && item.sup_dcd_id != 0) {
            item.mode = "Delete";
        } else {
            supdc_details_data.splice(formIndx, 1);
        }
        removeFormObj();

    });
}

function removeFormObj(formIndx) {

    jQuery('#InterLocationTransferDetailTable tbody').empty();
    fillSupDCDetailsTable();
}

jQuery('#item_id').on('change', function (e, isEdit = false) {
    let selected = jQuery(this).find('option:selected');
    let main_group = selected.data('main_group');
    let item_group = selected.data('item_group');
    let io_stock_qty = selected.data('io_stock_qty');
    let stock_rate_unit = selected.data('stock_rate_unit');
    let unit = selected.data('unit');
    let item_id = selected.val();

    var sup_dc_type_id = jQuery('#commonSupplierDCForm').find("input[name*='sup_dc_type_id']:checked").val();
    if (sup_dc_type_id === 'SQIN from Prod. Area') {
        unit = 'SQIN';
        if (typeof allItems !== 'undefined' && Array.isArray(allItems)) {
            let foundItem = allItems.find(i => i.id == item_id);
            if (foundItem && foundItem.stock_sq_in !== undefined && foundItem.stock_sq_in !== null) {
                io_stock_qty = foundItem.stock_sq_in;
            }
        }
    }

    if (isEdit) {
        let formIndx = jQuery('#SupplierDCDetailsModal').find("#form_index").val();
        if (formIndx !== '' && formIndx !== undefined && supdc_details_data[formIndx]) {
            let frmData = supdc_details_data[formIndx];
            if (frmData && frmData.item_id == item_id && frmData.io_stock_qty !== undefined && frmData.io_stock_qty !== null) {
                io_stock_qty = frmData.io_stock_qty;
            }
        }
    }

    if (selected.val() != "") {
        jQuery('#item_group').val(item_group);
        jQuery('#main_group').val(main_group);
        jQuery('#io_stock_qty').val(io_stock_qty ? parseFloat(io_stock_qty).toFixed(3) : parseFloat(0).toFixed(3));
        jQuery('#io_stock_qty_unit').text(unit).addClass('ms-1');
        jQuery('#return_qty_unit').text(unit).addClass('ms-1');

        if (main_group == 'Industrial X-Ray Films' || main_group == 'General') {
            jQuery('#SupplierDCDetailsForm #sr_table_pk_id').addClass('skip-tab');
            setSelect2Readonly("#SupplierDCDetailsForm #sr_table_pk_id", true);
            jQuery('#SupplierDCDetailsForm #sr_table_pk_id').empty().append('<option value="">Select Sr. No.</option>');
            jQuery('#SupplierDCDetailsForm #sr_table_pk_id').addClass('skip-tab');
            var dcInput = jQuery('#SupplierDCDetailsForm #return_qty');
            dcInput.prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');

        } else {
            jQuery('#SupplierDCDetailsForm #sr_table_pk_id').removeClass('skip-tab');
            setSelect2Readonly("#SupplierDCDetailsForm #sr_table_pk_id", false);
            if (!isEdit) {
                getSrNo(main_group, item_id);
            }
        }

        if (main_group == 'Industrial X-Ray Films') {
            jQuery('#return_qty').removeClass('isNumberKey').addClass('isNumberKeyNotDot');
            jQuery('#return_qty').attr('onblur', 'formatPoints(this, 0)');
            if (jQuery('#return_qty').val() !== '') {
                jQuery('#return_qty').val(parseInt(jQuery('#return_qty').val()) || '');
            }
        } else {
            jQuery('#return_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
            jQuery('#return_qty').attr('onblur', 'formatPoints(this, 3)');
        }

        if (main_group == 'IRED') {

            jQuery("#SupplierDCDetailsForm #aerb_no").attr("readonly", false).prop('required', true).removeClass('skip-tab').removeAttr('tabindex');
            jQuery("#SupplierDCDetailsForm #application_no").attr("readonly", false).prop("required", true).removeClass('skip-tab').removeAttr('tabindex');
            jQuery("#SupplierDCDetailsForm #movement_approval").attr("readonly", false).prop("disabled", false).removeClass('skip-tab').removeAttr('tabindex');
            toggleRequired();
            jQuery("#SupplierDCDetailsForm #validity").attr("readonly", false).removeClass('skip-tab').removeAttr('tabindex');
            jQuery('#SupplierDCDetailsForm').find('#validity').datepicker('enable');

        } else {

            jQuery("#SupplierDCDetailsForm #aerb_no").val('');
            jQuery("#SupplierDCDetailsForm #application_no").val('');
            jQuery("#SupplierDCDetailsForm #validity").val('');
            clearMovement();

            jQuery("#SupplierDCDetailsForm #aerb_no").attr("readonly", true).prop('required', false).addClass('skip-tab').attr('tabindex', '-1');
            jQuery("#SupplierDCDetailsForm #application_no").attr("readonly", true).prop("required", false).addClass('skip-tab').attr('tabindex', '-1');
            jQuery("#SupplierDCDetailsForm #movement_approval").attr("readonly", true).prop("disabled", true).prop("required", false).addClass('skip-tab').attr('tabindex', '-1');
            jQuery("#SupplierDCDetailsForm #validity").attr("readonly", true).addClass('skip-tab').attr('tabindex', '-1');
            jQuery('#SupplierDCDetailsForm').find('#validity').datepicker('disable');

        }

    } else {
        jQuery('#item_group').val('');
        jQuery('#main_group').val('');
        jQuery('#io_stock_qty').val('');
        jQuery('#io_stock_qty_unit').text('').removeClass('ms-1');
        jQuery('#return_qty_unit').text('').removeClass('ms-1');
        jQuery('#SupplierDCDetailsForm #sr_table_pk_id').empty().append('<option value="">Select Sr. No.</option>');
        jQuery('#SupplierDCDetailsForm #sr_table_pk_id').addClass('skip-tab');
        setSelect2Readonly("#SupplierDCDetailsForm #sr_table_pk_id", true);
        jQuery('#return_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        jQuery('#return_qty').attr('onblur', 'formatPoints(this, 3)');

        jQuery("#SupplierDCDetailsForm #aerb_no").val('');
        jQuery("#SupplierDCDetailsForm #application_no").val('');
        jQuery("#SupplierDCDetailsForm #validity").val('');

        jQuery("#SupplierDCDetailsForm #aerb_no").attr("readonly", true).prop("required", false).addClass('skip-tab').attr('tabindex', '-1');
        jQuery("#SupplierDCDetailsForm #application_no").attr("readonly", true).prop("required", false).addClass('skip-tab').attr('tabindex', '-1');
        jQuery("#SupplierDCDetailsForm #movement_approval").attr("readonly", true).prop("disabled", true).prop("required", false).addClass('skip-tab').attr('tabindex', '-1');
        jQuery("#SupplierDCDetailsForm #validity").attr("readonly", true).addClass('skip-tab').attr('tabindex', '-1');
        jQuery('#SupplierDCDetailsForm').find('#validity').datepicker('disable');
        clearMovement();

    }

});

function getSrNo(main_group, item_id, selected_sr = '') {
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
                        options += `<option data-sr_table_unique_id="${data.sr_no_data[idx].sr_table_unique_id}" value="${data.sr_no_data[idx].sr_table_pk_id}">${data.sr_no_data[idx].name_for_display}</option>`;
                    }
                }
                let $dropdown = jQuery('#SupplierDCDetailsForm #sr_table_pk_id');
                $dropdown.empty().append(options);

                // Set value AFTER options loaded
                if (selected_sr) {
                    $dropdown.val(selected_sr);
                }
                $dropdown.trigger('change.select2');
                let selectedOption = $dropdown.find('option:selected');
                jQuery('#SupplierDCDetailsForm #sr_table_unique_id').val(selectedOption.data('sr_table_unique_id') || '');
            } else {
                jQuery('#SupplierDCDetailsForm #sr_table_pk_id').empty().append(options).trigger('change.select2');
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

jQuery('#SupplierDCDetailsForm #sr_table_pk_id').on('change', function () {
    let selectedOption = jQuery(this).find('option:selected');
    let sr_table_unique_id = selectedOption.data('sr_table_unique_id') || '';

    jQuery('#SupplierDCDetailsForm #sr_table_unique_id').val(sr_table_unique_id);
    const dcInput = jQuery('#SupplierDCDetailsForm #return_qty');

    if (selectedOption.val() !== "") {
        dcInput.val((1).toFixed(3)).trigger('change').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
    } else {
        dcInput.val('').trigger('change').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
    }
});

//  Details Form Submit Start
$('#SupplierDCDetailsForm').on('submit', function (e) {
    e.preventDefault();
    let form = this;

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    var data = new FormData(document.getElementById('SupplierDCDetailsForm'));
    var formValue = Object.fromEntries(data.entries());
    var thisModal = jQuery('#SupplierDCDetailsModal');

    var main_group = formValue.main_group ? formValue.main_group : '';
    var sr_table_pk_id = formValue.sr_table_pk_id ? formValue.sr_table_pk_id : '';

    if (!["Industrial X-Ray Films", "General"].includes(main_group)) {
        if (sr_table_pk_id == "") {
            toastr.error('Select Sr. No.');
            return;
        }

    }

    var return_qty = formValue.return_qty ? parseFloat(formValue.return_qty) : 0;
    var pending_qty = formValue.pending_qty ? parseFloat(formValue.pending_qty) : 0;
    var sup_dc_type_id = jQuery('#commonSupplierDCForm').find('input[name*="sup_dc_type_id"]:checked').val();
    var ioStock = parseFloat(formValue.io_stock_qty || 0);
    var MaxQty = Math.min(ioStock, pending_qty);
    var reason = "";
    if (ioStock <= pending_qty) {
        reason = "Stock";
    } else {
        reason = "Pend. PO / DC Qty.";
    }


    if (return_qty > MaxQty && sup_dc_type_id == "Returnable - Service PO") {
        toastr.error(`Return Qty. Cannot Be Greater Than ${reason} ${MaxQty.toFixed(3)}.`);
        return;
    } else if (return_qty > ioStock && sup_dc_type_id != "Returnable - Service PO") {
        toastr.error(`Return Qty. Cannot Be Greater Than Stock ${ioStock.toFixed(3)}.`);
        return;
    }
    var used_qty = parseFloat(jQuery('#return_qty').attr('min')) || 0;
    if (used_qty > return_qty) {
        toastr.error('Return Qty. Cannot Be Less Than ' + used_qty.toFixed(3));
        return;
    }

    if (return_qty < 0.001) {
        toastr.error('Enter Return Qty. greater than 0.001.');
        return;
    }

    if (formValue.item_id.trim()) {


        var noDuplicate = true;
        var sr_table_pk_id = formValue.sr_table_pk_id ? formValue.sr_table_pk_id : null;
        var item_id = formValue.item_id ? formValue.item_id : null;

        var currentIndex = formValue.form_type === "edit" ? formValue.form_index : null;

        // var validData = supdc_details_data.filter(item => item.mode !== "Delete");

        jQuery.each(supdc_details_data, function (index, item) {
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
            var ser_po_number = formValue.ser_po_number ? formValue.ser_po_number : "";
            var ser_po_date = formValue.ser_po_date ? formValue.ser_po_date : "";
            var item_group = formValue.item_group != "" ? formValue.item_group : '';
            var main_group = formValue.main_group != "" ? formValue.main_group : '';
            var io_stock_qty = parseFloat(formValue.io_stock_qty || 0).toFixed(3);
            // var sr_no = formValue.name_for_display ? formValue.name_for_display : "";

            var pending_qty = formValue.pending_qty != "" ? parseFloat(formValue.pending_qty).toFixed(3) : '';
            var return_qty = formValue.return_qty ? parseFloat(formValue.return_qty).toFixed(3) : parseFloat(0).toFixed(3);

            // var pod_unit = formValue.pod_unit != "" ? formValue.pod_unit : '';
            var sr_table_pk_id = formValue.sr_table_pk_id ? formValue.sr_table_pk_id : '';
            var sr_no = sr_table_pk_id != "" ? formValue.name_for_display ? formValue.name_for_display : thisModal.find('#sr_table_pk_id option:selected').text() : "";

            // var pod_rate_unit = formValue.pod_rate_unit ? parseFloat(formValue.pod_rate_unit).toFixed(2) : parseFloat(0).toFixed(2);
            // var pod_amount = formValue.pod_amount ? parseFloat(formValue.pod_amount).toFixed(2) : parseFloat(0).toFixed(2);
            // var pod_del_date = formValue.pod_del_date != "" ? formValue.pod_del_date : "";

            var remark = formValue.remark ? formValue.remark : "";
            formValue.name_for_display = sr_table_pk_id != "" ? sr_no : "";
            var unit = thisModal.find('#io_stock_qty_unit').text();
            formValue.unit = unit;

            var aerb_no = formValue.aerb_no != "" && formValue.aerb_no != undefined ? formValue.aerb_no : "";
            var application_no = formValue.application_no != "" && formValue.application_no != undefined ? formValue.application_no : "";
            var validity = formValue.validity != "" && formValue.validity != undefined ? formValue.validity : "";
            var movement_approval_doc = formValue.movement_approval_doc != "" && formValue.movement_approval_doc != undefined ? formValue.movement_approval_doc : "";
            var for_calibration = jQuery('#for_calibration').is(':checked') ? 'Yes' : null;
            formValue.for_calibration = for_calibration;


            if (item_id != "") {
                if (formValue.form_type == "edit") {
                    formValue.item_name = item_name;
                    formValue.mode = formValue.sup_dcd_id == 0 ? "Insert" : "Update";
                    supdc_details_data[formValue.form_index] = {
                        ...supdc_details_data[formValue.form_index],
                        ...formValue
                    };
                    let tblHtml = ``;
                    tblHtml += `<td>`;
                    var in_use = supdc_details_data[formValue.form_index].in_use == true ? true : false;
                    tblHtml += in_use == true ? DetailsActionDropdown('editSupDCDetails') : DetailsActionDropdown('editSupDCDetails', 'removeSupDCDetails');
                    // tblHtml += DetailsActionDropdown('editSupDCDetails', 'removeSupDCDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                    </td>`;
                    tblHtml += `<td>${ser_po_number}</td>`;
                    tblHtml += `<td>${ser_po_date}</td>`;
                    tblHtml += `<td>${item_name}</td>`;
                    tblHtml += `<td>${item_group}</td>`;
                    tblHtml += `<td>${main_group}</td>`;
                    tblHtml += `<td>${sr_no}</td>`;
                    tblHtml += `<td>${io_stock_qty}</td>`;
                    tblHtml += `<td>${pending_qty}</td>`;
                    tblHtml += `<td>${return_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${aerb_no}</td>`;
                    tblHtml += `<td>${application_no}</td>`;
                    if (movement_approval_doc != "") {
                        let fullImagePath = uploadURL + movement_approval_doc;
                        tblHtml += `<td style="text-align:center; vertical-align:middle;">
                            <a target="_blank" href="${fullImagePath}" title="View" style="font-size:18px; display:inline-block;">
                                <i class="ri-eye-fill"></i>
                            </a>
                            <input type='hidden' name='movement_approval_doc[]' value="${movement_approval_doc}"/>
                        </td>`;
                    } else {
                        tblHtml += `<td style="text-align:center; vertical-align:middle;">
                            <input type='hidden' name='movement_approval_doc[]' value=""/>
                        </td>`;
                    }
                    tblHtml += `<td>${validity}</td>`;
                    tblHtml += `<td>${remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#SupplierDCDetailTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
                    toastSuccess("Record Updated.");
                } else {
                    formValue.mode = "Insert";
                    formValue.inter_location_transfer_details_id = 0;
                    supdc_details_data.push(formValue);
                    if (supdc_details_data.length > 0) {
                        setRadioReadonly("input[name='sup_dc_type_id']", true);
                    }
                    let formIndx = supdc_details_data.indexOf(formValue);
                    if (jQuery('#SupplierDCDetailTable tbody').find('#noDetails').length > 0) {
                        jQuery('#SupplierDCDetailTable tbody').empty();
                    }

                    let tblHtml = `<tr>`;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editSupDCDetails', 'removeSupDCDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
                    </td>`;
                    tblHtml += `<td>${ser_po_number}</td>`;
                    tblHtml += `<td>${ser_po_date}</td>`;
                    tblHtml += `<td>${item_name}</td>`;
                    tblHtml += `<td>${item_group}</td>`;
                    tblHtml += `<td>${main_group}</td>`;
                    tblHtml += `<td>${sr_no}</td>`;
                    tblHtml += `<td>${io_stock_qty}</td>`;
                    tblHtml += `<td>${pending_qty}</td>`;
                    tblHtml += `<td>${return_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${aerb_no}</td>`;
                    tblHtml += `<td>${application_no}</td>`;
                    if (movement_approval_doc != "") {
                        let fullImagePath = uploadURL + movement_approval_doc;
                        tblHtml += `<td style="text-align:center; vertical-align:middle;">
                            <a target="_blank" href="${fullImagePath}" title="View" style="font-size:18px; display:inline-block;">
                                <i class="ri-eye-fill"></i>
                            </a>
                            <input type='hidden' name='movement_approval_doc[]' value="${movement_approval_doc}"/>
                        </td>`;
                    } else {
                        tblHtml += `<td style="text-align:center; vertical-align:middle;">
                            <input type='hidden' name='movement_approval_doc[]' value=""/>
                        </td>`;
                    }
                    tblHtml += `<td>${validity}</td>`;
                    tblHtml += `<td>${remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#SupplierDCDetailTable tbody').append(tblHtml);
                    toastSuccess("Record Inserted.");
                }
            }
            if (formValue.form_type == "edit") {
                thisModal.modal('hide');
            } else {
                let formElement = document.getElementById('SupplierDCDetailsForm');
                formElement.reset();
                clearMovement();
                jQuery('#item_id').val('').trigger('change');
                jQuery('#io_stock_id').val('');
                jQuery('#return_qty').val('');
                jQuery('#remark').val('');

                var options = `<option value="">Select Sr. No.</option>`;
                jQuery('#SupplierDCDetailsForm #sr_table_pk_id').empty().append(options);
                setTimeout(function () {

                    let $select = jQuery('#SupplierDCDetailsForm #item_id');
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


$('#commonSupplierDCForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#SupplierDCModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;

    var dateValue = document.getElementById("sup_dc_date").value.trim();

    // validatePercentage
    if (!isValidDate(dateValue)) {
        toastr.error("Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#SupplierDCModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#SupplierDCModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#SupplierDCModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    jQuery('#SupplierDCModal').find('#submitbtn').prop('disabled', true);

    var formIdnew = jQuery('#SupplierDCModal').find('#commonSupplierDCForm').find('#id').val();
    var formUrl = formIdnew != undefined && formIdnew != "" ? "update-supplier_dc" : "store-supplier_dc";
    var data = new FormData(form);
    data.append('supdc_details_data', JSON.stringify(supdc_details_data ?? []));
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));

    let validData = supdc_details_data.filter(item => item.mode !== "Delete");

    if (validData.length > 0 && !jQuery.isEmptyObject(validData)) {
        let isValidDetails = true;
        let errorMsg = '';

        $.each(validData, function (index, row) {

            if ((row.sr_table_pk_id === undefined || row.sr_table_pk_id === null || row.sr_table_pk_id === '') && !['Industrial X-Ray Films', 'General'].includes(row.main_group)) {
                errorMsg = `Select SR No.`;
                isValidDetails = false;
                return false;
            }

            if (row.return_qty === undefined || row.return_qty === null || row.return_qty === '' || parseFloat(row.return_qty) <= 0) {
                errorMsg = `Enter Return Qty.`;
                isValidDetails = false;
                return false;
            }

            if ((!row.aerb_no || row.aerb_no.toString().trim() === '') && (row.main_group === 'IRED')) {
                errorMsg = `Enter AERB No.`;
                isValidDetails = false;
                return false;
            }

            if ((!row.application_no || row.application_no.toString().trim() === '') && (row.main_group === 'IRED')) {
                errorMsg = `Enter Application No.`;
                isValidDetails = false;
                return false;
            }

            if ((!row.movement_approval_doc || row.movement_approval_doc.toString().trim() === '') && (row.main_group === 'IRED')) {
                errorMsg = `Upload Movement Approval Document.`;
                isValidDetails = false;
                return false;
            }


        });

        if (!isValidDetails) {
            toastr.error(errorMsg);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');

            jQuery('#SupplierDCModal')
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
                        jQuery('#SupplierDCModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formIdnew == undefined || formIdnew == "") {
                        function nextFn() {
                            document.getElementById("commonSupplierDCForm").reset();
                            const form = document.getElementById("commonSupplierDCForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            jQuery('#sup_dc_sequence').prop('readonly', false).focus();
                            $("#supplier_id").val('').trigger('change');
                            setSelect2Readonly('#supplier_id', false);
                            jQuery('#supplier_id').removeClass('skip-tab');

                            supdc_details_data = [];
                            selectedRows = {};
                            resetFieds();
                            jQuery('#SupplierDCDetailTable tbody').empty();
                            getLatestSupplierDCNo();
                            setRadioReadonly("input[name='sup_dc_type_id']", false);
                            jQuery('#SupplierDCModal').find('#pending_btn').prop('disabled', true);
                        }

                        if (data.url != "") {
                            toastSuccessPreview(data.response_message, data.url, nextFn);
                        } else {
                            toastSuccess(data.response_message, nextFn);
                        }
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#SupplierDCModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#SupplierDCModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#SupplierDCModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#SupplierDCModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#SupplierDCModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        toastr.error('Add At Least One Supplier DC Detail.');

        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#SupplierDCModal').find('#submitbtn').prop('disabled', false);
    }
});

jQuery('#commonSupplierDCForm').find('#sup_dc_sequence').on('change', function () {
    checkSequence();
});
function checkSequence() {

    let thisForm = jQuery('#commonSupplierDCForm');
    let val = thisForm.find('#sup_dc_sequence').val();

    if (val != "") {

        if (val > 0 == false) {
            toastr.error('Enter Valid  DC No.');
            jQuery('#sup_dc_sequence').parent().parent().parent('div.control-group').addClass('error');
            jQuery('#sup_dc_sequence').focus();
            jQuery('#sup_dc_sequence').val('');

        } else {
            jQuery('#sup_dc_sequence').addClass('file-loader');
            jQuery('#sup_dc_sequence').parent().parent().parent('div.control-group').removeClass('error');

            var urL = "check-supplier_dc_number_duplication?for=add&sup_dc_sequence=" + val;

            var formId = jQuery('#commonSupplierDCForm').find('input[name="id"]').val();

            if (formId !== undefined) { //if form is edit
                urL = "check-supplier_dc_number_duplication?for=edit&sup_dc_sequence=" + val + "&id=" + formId;
            }

            jQuery.ajax({
                url: urL,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    jQuery('#sup_dc_sequence').removeClass('file-loader');
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        jQuery('#commonSupplierDCForm #sup_dc_sequence').val('');
                        const input = document.getElementById('sup_dc_sequence'); input?.focus();
                    } else {
                        jQuery('#commonSupplierDCForm #sup_dc_number').val(data.latest_no);
                        jQuery('#commonSupplierDCForm #sup_dc_sequence').val(val);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#sup_dc_sequence').removeClass('file-loader');
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
        jQuery('#sup_dc_number').val('');
        jQuery('#sup_dc_sequence').val('');
    }

}

// Show Bs And Hide Bs
jQuery('#SupplierDCModal').on('show.bs.modal', function () {
    jQuery('#SupplierDCModal').find('#sup_dc_sequence').prop('readonly', false);
    var formId = jQuery('#commonSupplierDCForm').find('input[name="id"]').val();

    if (formId == "" || formId == undefined) {
        jQuery('#commonSupplierDCForm').find('.add_detail').prop('disabled', false);
        jQuery('#commonSupplierDCForm').find('#supplier_id').removeClass('skip-tab');
        setSelect2Readonly('#supplier_id', false);
        jQuery(this).find('#supplier_id option.temp-supplier').remove();
        jQuery('#commonSupplierDCForm').find('#prepared_by_user_id').val(loginUserId).trigger("change.select2");
        jQuery('#SupplierDCModal').find('#sup_dc_sequence').prop('readonly', false);
        getLatestSupplierDCNo();
        jQuery('#commonSupplierDCForm').find('input[name*="sup_dc_type_id"]:checked').change();
        getPendingSuppliersForSupplierDC();
    }
    const input = document.getElementById('sup_dc_sequence');
    input?.focus();

});
jQuery('#SupplierDCModal').on('hide.bs.modal', function (e) {
    jQuery('#SupplierDCDetailTable tbody').empty();
    let thisModal = jQuery('#SupplierDCModal');
    thisModal.find('#submitbtn').prop('disabled', false);
    thisModal.find("#id").val("");
    thisModal.find('#sup_dc_sequence').prop('readonly', false);
    jQuery(this).find('#supplier_id option.temp-supplier').remove();
    jQuery('#SupplierDCModal').find('#add_new').hide();
    jQuery('#SupplierDCModal').find('#preview_btn').hide();
    resetFieds();
    jQuery('#commonSupplierDCForm').trigger("reset");

});


jQuery('#SupplierDCDetailsModal').on('show.bs.modal', function () {
    let thisModal = jQuery('#SupplierDCDetailsModal');
    var form_type = thisModal.find("#form_type").val();
    var isEdit = (form_type == "edit");
    jQuery('#item_id').trigger('change', [isEdit]);
    var SupDCTypeId = jQuery('#commonSupplierDCForm').find('input[name*="sup_dc_type_id"]:checked').val();
    var details_id = thisModal.find("#sup_dcd_id").val();
    if ((form_type == "edit" && details_id != "0") || SupDCTypeId == "Returnable - Service PO") {
        setSelect2Readonly('#item_id', true);
        jQuery('#item_id').addClass('skip-tab');
        setSelect2Readonly('#sr_table_pk_id', true);
        jQuery('#sr_table_pk_id').addClass('skip-tab');
    } else {
        setSelect2Readonly('#item_id', false);
        jQuery('#item_id').removeClass('skip-tab');
        setSelect2Readonly('#sr_table_pk_id', false);
        jQuery('#sr_table_pk_id').removeClass('skip-tab');
    }
    var mode = jQuery("#SupplierDCDetailsForm #form_type").val();
    var sup_dcd_id = jQuery("#SupplierDCDetailsForm #sup_dcd_id").val();
    if (mode == "add" && sup_dcd_id == "0") {
        jQuery('#SupplierDCDetailsForm #return_qty').removeAttr('min');
    }

    if (!isEdit) {
        if (SupDCTypeId === "Returnable - Service PO") {
            jQuery('#for_calibration_row').show();
            thisModal.find("#for_calibration").prop('disabled', true).prop('checked', false);
        } else {
            jQuery('#for_calibration_row').hide();
            thisModal.find("#for_calibration").prop('disabled', true).prop('checked', false);
        }
    }

    // Make AERB fields read-only if transaction is being edited (main ID exists)
    let mainId = jQuery('#SupplierDCModal').find('#id').val();
    if (mainId && mainId > 0) {
        thisModal.find("#aerb_no").prop('readonly', true);
        thisModal.find("#application_no").prop('readonly', true);
        thisModal.find("#movement_approval").prop('disabled', true);
        thisModal.find("#movement_approval_remove").addClass('hide');
    } 
});
jQuery('#SupplierDCDetailsModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#SupplierDCDetailsModal');
    thisModal.find('#submitbtn').prop('disabled', false);
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    thisModal.find("#sup_dcd_id").val("");
    thisModal.find("#item_id").val("");
    setSelect2Readonly('#item_id', false);
    jQuery('#item_id').removeClass('skip-tab');
    thisModal.find("#ser_pod_id").val("");
    thisModal.find("#sr_table_unique_id").val("");
    thisModal.find("#sr_table_pk_id").val("");
    thisModal.find("#for_calibration").prop('disabled', true).prop('checked', false);
    
    // Restore AERB fields editable state
    thisModal.find("#aerb_no").prop('readonly', false);
    thisModal.find("#application_no").prop('readonly', false);
    thisModal.find("#movement_approval").prop('disabled', false);
    
    clearMovement();

    jQuery(this).find('#item_id option.temp-item').remove();
    jQuery('#SupplierDCDetailsForm').trigger("reset");
    this.dataset.customHideFocus = 'true';
    const input = jQuery("#commonSupplierDCForm").find("#mode_of_transport");
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }

    jQuery('#SupplierDCDetailsForm #return_qty').removeAttr('min');
});


jQuery('#SupplierDCPendingModal').on('show.bs.modal', function (e) {
    var dt = jQuery('#pendingSupplierDCDataTable').DataTable();
    fixDataTableColumnsUntilAdjusted(dt);
    var usedParts = [];
    if (supdc_details_data && supdc_details_data.length > 0) {
        supdc_details_data.forEach(function (item) {
            if (item.ser_pod_id) {
                usedParts.push(Number(item.ser_pod_id));
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
        var checkField = jQuery(this).find('input[name="ser_pod_id[]"]');
        var partId = jQuery(checkField).val();
        var inUse = isUsed(partId);

        if (formId == undefined) {
            if (supdc_details_data.length > 0) {
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

jQuery('#SupplierDCPendingModal').on('hide.bs.modal', function (e) {
    selectedRows = {};
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('ref_no_date');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }
});

jQuery('#SupplierDCPendingModal #checkall-sup_dc_data').click(function () {
    if (jQuery(this).is(':checked')) {
        jQuery("#pendingSupplierDCDataTable").find("[id^='ser_pod_ids_']:not(.in-use)").prop('checked', true).trigger('change');
        jQuery("#pendingSupplierDCDataTable").find("[id^='ser_pod_ids_']").prop('checked', true).trigger('change');
    } else {
        jQuery("#pendingSupplierDCDataTable").find("[id^='ser_pod_ids_']:not(.in-use)").prop('checked', false).trigger('change');
        jQuery("#pendingSupplierDCDataTable").find("[id^='ser_pod_ids_']").prop('checked', false).trigger('change');
    }

});

jQuery('#resetbtn').on('click', function () {
    supdc_details_data = [];
    selectedRows = {};


    var formId = jQuery('#SupplierDCModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonSupplierDCForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        resetFieds();
        jQuery('#commonSupplierDCForm').find('#prepared_by_user_id').val(loginUserId).trigger("change.select2");
        jQuery('#SupplierDCModal').find('#sup_dc_sequence').prop('readonly', false).focus();
        getLatestSupplierDCNo();
    } else {
        fetchAndFillSupplierDC(formId);
    }
});

jQuery('#SupplierDCModal').on('click', '#add_new', function () {

    document.getElementById("commonSupplierDCForm").reset();
    const form = document.getElementById("commonSupplierDCForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    resetFieds();

    jQuery('#commonSupplierDCForm').find('#prepared_by_user_id').val(loginUserId).trigger("change.select2");
    getLatestSupplierDCNo();
    jQuery('#SupplierDCModal').find('#sup_dc_sequence').prop('readonly', false).focus();
    jQuery('#SupplierDCModal').find("#supplier_id").removeClass('skip-tab');
    jQuery('#commonSupplierDCForm').find('input[name="id"]').val('');
    setSelect2Readonly("#supplier_id", false);

    jQuery('#SupplierDCModal').find('#add_new').hide();
    jQuery('#SupplierDCModal').find('#preview_btn').hide();

});

function resetFieds() {

    jQuery("#supplier_id").val('').trigger('change');
    setRadioReadonly("input[name='sup_dc_type_id']", false);
    jQuery('#SupplierDCModal').find('input[name*="sup_dc_type_id"][value="Non Returnable - Manual"]').prop('checked', true).change().focus();
    jQuery("#commonSupplierDCForm .toggleModalBtn").prop('disabled', true);
    jQuery('#commonSupplierDCForm').find('.add_detail').prop('disabled', false);
    jQuery("#SupplierDCDetailsForm #sr_table_unique_id").val('');
    jQuery("#SupplierDCDetailsForm #ser_pod_id").val('');
    jQuery("#SupplierDCDetailsForm #sup_dcd_id").val('');
    jQuery("#item_id").val("").removeClass('skip-tab');
    jQuery("#sr_table_pk_id").val("").removeClass('skip-tab');
    jQuery("#return_qty").val("").removeClass('skip-tab').prop('readonly', false);
    supdc_details_data = [];
    selectedRows = {};
    jQuery('#SupplierDCDetailTable tbody').empty();
    jQuery('#item_id option.temp-item').remove();
    jQuery('#sr_table_pk_id option.temp-sr_table_pk_id').remove();
    setSelect2Readonly('#SupplierDCModal #supplier_id', false);
    jQuery('#supplier_id').removeClass('skip-tab');
    clearMovement();
    jQuery("#SupplierDCDetailsForm #aerb_no").val('');
    jQuery("#SupplierDCDetailsForm #application_no").val('');
    jQuery("#SupplierDCDetailsForm #validity").val('');
    clearMovement();

    jQuery("#SupplierDCDetailsForm #aerb_no").attr("readonly", true).prop('required', false).addClass('skip-tab').attr('tabindex', '-1');
    jQuery("#SupplierDCDetailsForm #application_no").attr("readonly", true).prop("required", false).addClass('skip-tab').attr('tabindex', '-1');
    jQuery("#SupplierDCDetailsForm #movement_approval").attr("readonly", true).prop("disabled", true).prop("required", false).addClass('skip-tab').attr('tabindex', '-1');
    jQuery("#SupplierDCDetailsForm #validity").attr("readonly", true).addClass('skip-tab').attr('tabindex', '-1');
    jQuery('#SupplierDCDetailsForm').find('#validity').datepicker('disable');


}

function validateMovementImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.pdf)$/i;
    return allowedExtensions.exec(filePath) ? true : false;
}

jQuery('#SupplierDCDetailsForm #movement_approval').on('change', function (e) {
    MovementfileUpload(e);
});

function MovementfileUpload(e) {
    var form_data = new FormData();
    var target = e.target;
    var id = target.id;
    var files = target.files;
    var totalfiles = files.length;
    var oldImg = jQuery('#movement_approval_doc').val();
    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');

    if (totalfiles > 0) {
        var notValid = 0;
        for (var index = 0; index < totalfiles; index++) {
            if (validateMovementImage(files[index].name) == true) {
                form_data.append("docs[]", files[index]);
            } else {
                notValid = 1;
                toastr.error("Only (jpeg,jpg,png,gif,pdf) files are allowed.");
                jQuery('#movement_approval_doc').val('');
                jQuery('#' + id).val('');
                jQuery('#movement_approval_prev').attr('href', '#').addClass('hide');
                jQuery('#movement_approval_remove').removeClass('i-block').addClass('hide');
                e.stopImmediatePropagation();
                return false;
            }
        }

        if (notValid == 0) {
            jQuery('#SupplierDCDetailsModal').find('#submitbtn').prop('disabled', true);
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
                    jQuery('#SupplierDCDetailsModal').find('#submitbtn').prop('disabled', false);
                    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    if (data.response_code == 1) {
                        if (oldImg != "" && oldImg.includes('temp_media/')) {
                            removeMediaMovement(oldImg);
                        }
                        $('#movement_approval_doc').val(data.files);
                        $('#movement_approval_prev').attr('href', data.files_url).removeClass('hide');
                        $('#movement_approval_remove').addClass('i-block').removeClass('hide');
                        toggleRequired();
                        console.log(data.response_message);
                    } else {
                        console.log(data.response_message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#SupplierDCDetailsModal').find('#submitbtn').prop('disabled', false);
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
            let fileInput = jQuery('#movement_approval');
            let dataTransfer = new DataTransfer();
            dataTransfer.items.add(new File([""], fileName));
            fileInput[0].files = dataTransfer.files;
            return false;
        }
        clearMovement();
    }
}

function toggleRequired() {
    let main_group = jQuery('#SupplierDCDetailsForm').find('#main_group').val();
    let docVal = jQuery('#SupplierDCDetailsModal').find('#movement_approval_doc').val();
    if (main_group == 'IRED') {
        if (docVal && docVal !== "") {
            jQuery('#SupplierDCDetailsModal').find('#movement_approval').prop('required', false);
        } else {
            jQuery('#SupplierDCDetailsModal').find('#movement_approval').prop('required', true);
        }
    } else {
        jQuery('#SupplierDCDetailsModal').find('#movement_approval').prop('required', false);
    }
}

function clearMovement() {
    jQuery('#SupplierDCDetailsModal').find('#movement_approval_doc').val('');
    jQuery('#SupplierDCDetailsModal').find('#movement_approval').val('');
    jQuery('#SupplierDCDetailsModal').find('#movement_approval_prev').attr('href', '#').addClass('hide');
    jQuery('#SupplierDCDetailsModal').find('#movement_approval_remove').removeClass('i-block').addClass('hide');
    toggleRequired();
}

function removeFileMovement(e) {
    e.stopImmediatePropagation();
    toastDetailDelete('Are You Sure, You Want <lw-c>To</lw-c> Delete ?', () => {
        var oldImg = jQuery('#movement_approval_doc').val();
        if (oldImg != "" && oldImg.includes('temp_media/')) {
            removeMediaMovement(oldImg);
        }
        clearMovement();
    });
}

function removeMediaMovement(docName) {
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