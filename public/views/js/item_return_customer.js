var item_return_data = [];
var dc_details_data = [];
var formId = jQuery('#commonItemReturnCustomerForm').find('input[name="id"]').val();

let selectedRows = {};
$.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
    var row = $('#pendingCustomerDCDataTable').DataTable().row(dataIndex).node();
    var checkbox = $(row).find("input[type='checkbox']");
    if (checkbox.is(':checked')) {
        return true;
    }
    return true;
});

// Edit purchase Indent row click
jQuery('#dyntable tbody').on('click', '.edit-item_return_customer', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#ItemReturnCustomerModal').find('#id').val(data["return_id"]);
    if (data && data["return_id"]) {
        fetchAndFillItemReturn(data["return_id"]);
    }
});

// Function to fetch and fill data in edit mode
function fetchAndFillItemReturn(id) {
    if (!id) return;
    jQuery('#ItemReturnCustomerModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-item_return_customer",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.dc_data != null) {
                jQuery('#ItemReturnCustomerModal').find('#id').val(data.dc_data.return_id != "" ? data.dc_data.return_id : "");
                let url = checkFileRoute + "?id=" + data.dc_data.return_id + "&name=" + data.dc_data.pdf_name + "&type=item_return_customer";

                jQuery('#preview_btn').attr('href', url).show();
                jQuery('#ItemReturnCustomerModal').find('#return_number').val(data.dc_data.return_number != "" ? data.dc_data.return_number : "");
                jQuery('#ItemReturnCustomerModal').find('#return_date').val(data.dc_data.return_date != "" ? data.dc_data.return_date : "");
                jQuery('#ItemReturnCustomerModal').find('#return_sequence').val(data.dc_data.return_sequence != "" ? data.dc_data.return_sequence : "");

                getPendingCustomersForItemReturn().done(function () {

                    let customerId = data.dc_data.customer_id;
                    let customerText = data.dc_data.customer;
                    let $customer = jQuery('#ItemReturnCustomerModal').find('#customer_id');
                    if ($customer.find("option[value='" + customerId + "']").length === 0) {
                        $customer.append(
                            `<option class="temp-customer" value="${customerId}" selected>${customerText}</option>`
                        );
                    }
                    $customer.val(customerId).trigger('change.select2');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                });
                setTimeout(() => {
                    setSelect2Readonly('#customer_id', true);
                }, 500);

                jQuery('#ItemReturnCustomerModal').find('#mode_of_transport').val(data.dc_data.mode_of_transport != "" ? data.dc_data.mode_of_transport : "");
                jQuery('#ItemReturnCustomerModal').find('#transporter').val(data.dc_data.transporter != "" ? data.dc_data.transporter : "");
                jQuery('#ItemReturnCustomerModal').find('#vehicle_no').val(data.dc_data.vehicle_no != "" ? data.dc_data.vehicle_no : "");
                jQuery('#ItemReturnCustomerModal').find('#special_note').val(data.dc_data.special_note != "" ? data.dc_data.special_note : "");

                jQuery('#ItemReturnCustomerModal').find('#prepared_by_user_id').val(data.dc_data.prepared_by_user_id).trigger('change.select2');
                if (data.dc_details_data != "" && data.dc_details_data.length > 0) {
                    dc_details_data = data.dc_details_data.map(item => {
                        item.mode = "Update";
                        return item;
                    });
                    fillCustomerDCDetailsTable();
                }

                jQuery('#ItemReturnCustomerModal').find('#pending_btn').prop('disabled', true);
                jQuery('#ItemReturnCustomerModal').find('#submitbtn').prop('disabled', false);

                const form = document.getElementById("ItemReturnCustomerModal");
                if (form) form.classList.remove('was-validated');
                jQuery('#ItemReturnCustomerModal').find('#add_new').show();
                jQuery('#ItemReturnCustomerModal').find('#preview_btn').show();

                jQuery('#ItemReturnCustomerModal').find('#sup_dc_sequence').focus();

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
            jQuery('#ItemReturnCustomerModal').find('#sup_dc_sequence').focus();
        }
    });
}



jQuery('#ItemReturnCustomerModal').on('show.bs.modal', function () {
    var hasAccess = jQuery('#commonItemReturnCustomerForm').find('#has_access').val();
    jQuery('#commonItemReturnCustomerForm').find('#prepared_by_user_id').val(loginUserId).trigger("change.select2");
    var formId = jQuery('#commonItemReturnCustomerForm').find('input[name="id"]').val();

    if (formId == "" || formId == undefined) {
        jQuery('#commonItemReturnCustomerForm').find('#prepared_by_user_id').val(loginUserId).trigger("change.select2");
        getLatestItemReturnNo();
        jQuery('#ItemReturnCustomerModal').find('#add_new').hide();
        jQuery('#ItemReturnCustomerModal').find('#preview_btn').hide();
    } else {
        setSelect2Readonly('#ItemReturnCustomerModal #customer_id', true);
        jQuery('#ItemReturnCustomerModal').find('#add_new').show();
        jQuery('#ItemReturnCustomerModal').find('#preview_btn').show();
    }
    const input = document.getElementById('return_sequence');
    input?.focus();
    getPendingCustomersForItemReturn();

});

jQuery('#ItemReturnCustomerModal').on('hide.bs.modal', function (e) {
    jQuery('#ItemReturnDetailTable tbody').empty();
    let thisModal = jQuery('#ItemReturnCustomerModal');
    thisModal.find("#id").val("");
    var thisForm = jQuery('#ItemReturnDetailsModal');
    thisForm.find("#return_detail_id").val(0);
    thisForm.find("#dc_detail_id").val(0);
    item_return_data = [];
    dc_details_data = [];
    jQuery('#ItemReturnCustomerModal').find('#add_new').hide();
    jQuery('#ItemReturnCustomerModal').find('#preview_btn').hide();
});

jQuery('#ItemReturnDetailsModal').on('show.bs.modal', function () {
    let thisModal = jQuery('#ItemReturnDetailsModal');
    var details_id = thisModal.find("#return_detail_id").val();
    var form_type = thisModal.find("#form_type").val();
    jQuery('#sr_table_pk_id').addClass('skip-tab');

    if (form_type == "edit" && details_id != 0) {
        jQuery('#item_id').addClass('skip-tab');
        jQuery('#sr_table_pk_id').addClass('skip-tab');
    } else {
        jQuery('#item_id').addClass('skip-tab');

        setTimeout(function () {
            let sel = jQuery('#item_id')
                .next('.select2-container')
                .find('.select2-selection');
            sel.attr('tabindex', 0).focus();
        }, 20);
    }
    var mode = jQuery("#ItemReturnDetailsModal #form_type").val();
    var dc_detail_id = jQuery("#ItemReturnDetailsModal #return_detail_id").val();
    if (mode == "add") {
        jQuery('#ItemReturnDetailsModal #grn_qty').removeAttr('min');
    }

});

jQuery('#ItemReturnDetailsModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#ItemReturnDetailsModal');
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    thisModal.find("#return_detail_id").val(0);
    jQuery(this).find('#item_id option.temp-item').remove();
    jQuery(this).find('#sr_table_pk_id option.temp-sr_table_pk_id').remove();
    jQuery('#ItemReturnDetailsForm').trigger("reset");
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('mode_of_transport');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }

    jQuery('#ItemReturnDetailsModal #grn_qty').removeAttr('min');
    jQuery('#ItemReturnDetailsModal #grn_qty').removeAttr('max');
});

jQuery('#CustomerDCPendingModal').on('hide.bs.modal', function (e) {
    selectedRows = {};
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('mode_of_transport');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }
});
jQuery('#CustomerDCPendingModal').on('show.bs.modal', function (e) {
    var dt = jQuery('#pendingCustomerDCDataTable').DataTable();
    fixDataTableColumnsUntilAdjusted(dt);
    var usedParts = [];
    if (dc_details_data && dc_details_data.length > 0) {
        dc_details_data.forEach(function (item) {
            if (item.dc_detail_id) {
                usedParts.push(Number(item.dc_detail_id));
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
    jQuery('#pendingCustomerDCDataTable tbody tr').each(function (indx) {
        totalEntry++;
        var checkField = jQuery(this).find('input[name="dc_detail_id[]"]');
        var partId = jQuery(checkField).val();
        var inUse = isUsed(partId);

        if (formId == undefined) {
            if (dc_details_data.length > 0) {
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

jQuery('#ItemReturnCustomerModal').on('click', '#add_new', function () {

    document.getElementById("commonItemReturnCustomerForm").reset();
    const form = document.getElementById("commonItemReturnCustomerForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    jQuery('#ItemReturnDetailTable tbody').empty();
    jQuery('#ItemReturnDetailTable tbody').append(`
        <tr>
            <td colspan="10" id="noDetails">
                No Item Return Details Added
            </td>
        </tr>
    `);
    dc_details_data = [];
    getLatestItemReturnNo();
    jQuery('#ItemReturnCustomerModal').find("#customer_id").val('').trigger('change');
    getPendingCustomersForItemReturn();
    jQuery('#return_sequence').focus();
    jQuery('#commonItemReturnCustomerForm').find('input[name="id"]').val('');
    jQuery('#ItemReturnCustomerModal').find('#add_new').hide();
    jQuery('#ItemReturnCustomerModal').find('#preview_btn').hide();
    jQuery('#commonItemReturnCustomerForm').find('#prepared_by_user_id').val(loginUserId).trigger("change.select2");

});

jQuery('#resetbtn').on('click', function () {
    dc_details_data = [];
    selectedRows = {};
    var formId = jQuery('#ItemReturnCustomerModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonItemReturnCustomerForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
        jQuery('#commonItemReturnCustomerForm').find('#prepared_by_user_id').val(loginUserId).trigger("change.select2");
        getLatestItemReturnNo();
    } else {
        fetchAndFillItemReturn(formId);
    }
});

jQuery('#item_id').on('change', function () {
    let selected = jQuery(this).find('option:selected');
    let main_group = selected.data('main_group');
    let item_group = selected.data('item_group');
    let io_stock_qty = selected.data('io_stock_qty');
    let stock_rate_unit = selected.data('stock_rate_unit');
    let unit = selected.data('unit');
    let item_id = selected.val();
    if (selected.val() != "") {
        jQuery('#item_group').val(item_group);
        jQuery('#main_group').val(main_group);
        jQuery('#io_stock_qty').val(io_stock_qty ? parseFloat(io_stock_qty).toFixed(3) : parseFloat(0).toFixed(3));
        jQuery('#pend_qty_unit').text(unit).addClass('ms-1');
        jQuery('#dc_qty_unit').text(unit).addClass('ms-1');

        if (main_group == 'Industrial X-Ray Films' || main_group == 'General') {
            jQuery('#ItemReturnDetailsForm #sr_table_pk_id').addClass('skip-tab');
            setSelect2Readonly("#ItemReturnDetailsForm #sr_table_pk_id", true);
            jQuery('#ItemReturnDetailsForm #sr_table_pk_id').empty().append('<option value="">Select Sr. No.</option>');
            jQuery('#ItemReturnDetailsForm #sr_table_pk_id').addClass('skip-tab');
            var dcInput = jQuery('#ItemReturnDetailsForm #grn_qty');
            dcInput.prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');

        } else {

            jQuery('#ItemReturnDetailsForm #sr_table_pk_id').removeClass('skip-tab');
            setSelect2Readonly("#ItemReturnDetailsForm #sr_table_pk_id", false);
            getSrNo(main_group, item_id);
        }

        if (main_group == 'Industrial X-Ray Films' || main_group == 'Material – MPT' || main_group == 'Chemical – DPT') {
            jQuery('#grn_qty').removeClass('isNumberKey').addClass('isNumberKeyNotDot');
            jQuery('#grn_qty').attr('onblur', 'formatPoints(this, 0)');
            if (jQuery('#grn_qty').val() !== '') {
                jQuery('#grn_qty').val(parseInt(jQuery('#grn_qty').val()) || '');
            }
        } else {
            jQuery('#grn_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
            jQuery('#grn_qty').attr('onblur', 'formatPoints(this, 3)');
        }

    } else {
        jQuery('#item_group').val('');
        jQuery('#main_group').val('');
        jQuery('#dc_qty_unit').text('').removeClass('ms-1');
        jQuery('#pend_qty_unit').text('').removeClass('ms-1');
        jQuery('#ItemReturnDetailsForm #sr_table_pk_id').empty().append('<option value="">Select Sr. No.</option>');
        jQuery('#ItemReturnDetailsForm #sr_table_pk_id').addClass('skip-tab');
        setSelect2Readonly("#ItemReturnDetailsForm #sr_table_pk_id", true);
        jQuery('#grn_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        jQuery('#grn_qty').attr('onblur', 'formatPoints(this, 3)');
    }

});

// Get Pending Customes
function getPendingCustomersForItemReturn() {

    var formId = jQuery('#commonItemReturnCustomerForm').find('input[name="id"]').val();

    if (formId != undefined && formId != "") {
        var Url = 'get-pending_customer_for_item_return_customer?id=' + formId;
    } else {
        var Url = 'get-pending_customer_for_item_return_customer';
    }
    let custHtml = '';
    custHtml += `<option value="">Select Customer</option> `;
    return jQuery.ajax({
        url: Url,
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1 && data.dc_customer.length > 0) {
                for (let indx in data.dc_customer) {
                    custHtml += `<option value="${data.dc_customer[indx].id}">${data.dc_customer[indx].customer}</option>`;
                }

                jQuery('#commonItemReturnCustomerForm').find('#customer_id').empty().append(custHtml);
                setSelect2Readonly('#customer_id', false);
                jQuery('#customer_id').removeClass('skip-tab');

            } else {
                console.log(data.response_message);
                jQuery('#customer_id').addClass('skip-tab');
            }
        },
    });
}

// get Pending List OF Selected Customer
$('#customer_id').on('change', function () {
    fillPendingDcForReturn();
});


function fillPendingDcForReturn() {
    let custId = jQuery('#customer_id option:selected').val();
    var sup_dc_type_id = jQuery('#commonItemReturnCustomerForm').find("input[name*='sup_dc_type_id']:checked").val();

    var thisForm = jQuery('#ItemReturnDetailsModal');

    var formId = jQuery('#commonItemReturnCustomerForm').find('input[name="id"]').val();


    if (custId != "") {
        if (formId != undefined && formId != '') {
            var Url = "get-pending_dc_customer_list_for_item_return_customer?customer_id=" + custId + "&id=" + formId;
        } else {
            var Url = "get-pending_dc_customer_list_for_item_return_customer?customer_id=" + custId;
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

                    thisForm.find('#ItemReturnDetailTable tbody input[name="form_indx"]').each(function (indx) {
                        let frmIndx = jQuery(this).val();

                        let jbEorkOrderId = dc_data[frmIndx].dc_detail_id;
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
                            var inUse = isUsed(data.dc_data[idx].dc_detail_id);
                            var in_use = data.dc_data[idx].in_use == true ? 'readonly' : '';
                            totalEntry++;
                            tblHtml += `
                                    <tr>
                                        <td><input type="checkbox" name="dc_detail_id[]" class="simple-check checkbox-filter-remove ${inUse ? 'in-use' : ''}" id="dc_detail_ids_${data.dc_data[idx].dc_detail_id}" value="${data.dc_data[idx].dc_detail_id}" ${inUse ? 'checked' : ''} ${in_use}/></td>
                                        <td>${data.dc_data[idx].dc_number}</td>
                                        <td>${data.dc_data[idx].dc_date}</td>
                                        <td>${data.dc_data[idx].item_name}</td>
                                        <td>${data.dc_data[idx].item_group}</td>
                                        <td>${data.dc_data[idx].main_group}</td>
                                        <td>${data.dc_data[idx].name_for_display != null ? data.dc_data[idx].name_for_display : ""}</td>
                                        <td>${parseFloat(data.dc_data[idx].pending_qty).toFixed(3)}</td>
                                        <td>${data.dc_data[idx].unit}</td>
                                        <td>${data.dc_data[idx].remark != null && data.dc_data[idx].remark != undefined ? data.dc_data[idx].remark : ""}</td>
                                        <td>${data.dc_data[idx].prepared_by}</td>
                                    </tr>`;

                        }

                    } else {

                        tblHtml += `<tr class="centeralign" id="noPendingPo">
                                        <td colspan="14">No Pending Item Retutn Details Available</td>
                                    </tr>`;

                    }



                    var $table = jQuery("#CustomerDCPendingModal").find('#pendingCustomerDCDataTable');
                    if (jQuery.fn.DataTable.isDataTable($table)) {
                        $table.DataTable().clear().destroy();
                    }
                    jQuery('#pendingCustomerDCDataTable tbody').empty().append(tblHtml);

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
                    jQuery('.toggleModalBtn').prop('disabled', false);
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
$('#addPendingCustomerDCForm').on('submit', function (e) {

    e.preventDefault();

    jQuery('#full-page-loader')
        .removeClass('hidden-loader')
        .addClass('loader-progress-whole-page');

    jQuery('#CustomerDCPendingModal')
        .find('#submitbtn')
        .prop('disabled', true);

    let chkArr = [];
    var formId = jQuery('#commonItemReturnCustomerForm').find('input[name="id"]').val();

    jQuery("#addPendingCustomerDCForm")
        .find("[id^='dc_detail_ids_']:checked")
        .each(function () {
            chkArr.push(jQuery(this).val());
        });

    // No checkbox selected
    if (chkArr.length === 0) {
        toastr.error('Select Delivery Challan From Pending.');

        jQuery('#full-page-loader')
            .removeClass('loader-progress-whole-page')
            .addClass('hidden-loader');

        jQuery('#CustomerDCPendingModal')
            .find('#submitbtn')
            .prop('disabled', false);

        return;
    }

    if (formId != undefined && formId != "") {
        var pend_url = "get-pending_dc_for_item_return_customer?id=" + formId;
    } else {
        var pend_url = "get-pending_dc_for_item_return_customer";
    }

    jQuery.ajax({
        url: pend_url,
        type: 'GET',
        data: { dc_detail_ids: chkArr.join(',') },
        dataType: 'json',
        success: function (data) {

            if (data.response_code == 1) {

                if (data.dc_data && data.dc_data.length > 0) {
                    dc_details_data = [];
                    for (let ind in data.dc_data) {
                        let row = {
                            ...data.dc_data[ind],
                            mode: "Insert",
                            grn_qty: data.dc_data[ind].pending_qty

                        };
                        dc_details_data.push(row);
                    }

                    fillCustomerDCDetailsTable(data.dc_data);
                } else {
                    fillCustomerDCDetailsTable([]);
                }

                jQuery("#CustomerDCPendingModal").modal('hide');
                jQuery("#commonItemReturnCustomerForm").find('customer_id').addClass('skip-tab');
                setSelect2Readonly('#customer_id', true);
            } else {
                fillCustomerDCDetailsTable([]);
            }

            jQuery('#CustomerDCPendingModal')
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

            jQuery('#CustomerDCPendingModal')
                .find('#submitbtn')
                .prop('disabled', false);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');
        }
    });
});


function fillCustomerDCDetailsTable() {

    let thisModal = jQuery('#ItemReturnCustomerModal');
    let tblHtml = ``;
    if (dc_details_data.length > 0) {
        for (let key in dc_details_data) {
            if (dc_details_data[key].mode == "Delete") {
                continue;
            }

            var formIndx = dc_details_data.indexOf(dc_details_data[key]);

            var dc_number = dc_details_data[key].dc_number ? dc_details_data[key].dc_number : "";
            var dc_date = dc_details_data[key].dc_date ? dc_details_data[key].dc_date : "";

            if (dc_details_data[key].item_name != '' && dc_details_data[key].item_name != undefined) {
                var item_name = dc_details_data[key].item_name;
            } else {
                var item_name = item_id != '' ? thisModal.find('#item_id option[value="' + item_id + '"]').text() : '';
            }

            var item_group = dc_details_data[key].item_group ? dc_details_data[key].item_group : "";
            var main_group = dc_details_data[key].main_group ? dc_details_data[key].main_group : "";
            var sr_no = dc_details_data[key].name_for_display ? dc_details_data[key].name_for_display : "";
            var sr_table_pk_id = dc_details_data[key].sr_table_pk_id ? dc_details_data[key].sr_table_pk_id : '';

            var pending_qty = dc_details_data[key].pending_qty ? parseFloat(dc_details_data[key].pending_qty).toFixed(3) : "";

            var grn_qty = dc_details_data[key].grn_qty ? parseFloat(dc_details_data[key].grn_qty).toFixed(3) : parseFloat(dc_details_data[key].pending_qty).toFixed(3);

            var remark = dc_details_data[key].remark ? dc_details_data[key].remark : "";
            var unit = dc_details_data[key].unit != "" ? dc_details_data[key].unit : '';
            var in_use = dc_details_data[key].in_use == true ? true : false;

            tblHtml += `<tr>`;
            tblHtml += `<td>`;
            tblHtml += in_use == true ? DetailsActionDropdown('editDetails') : DetailsActionDropdown('editDetails', 'removeDetails');
            tblHtml += ` <input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;
            tblHtml += `<td>${dc_number}</td>`;
            tblHtml += `<td>${dc_date}</td>`;
            tblHtml += `<td>${item_name}</td>`;
            tblHtml += `<td>${item_group}</td>`;
            tblHtml += `<td>${main_group}</td>`;
            tblHtml += `<td>${sr_no}</td>`;
            tblHtml += `<td>${pending_qty}</td>`;
            tblHtml += `<td>${grn_qty}</td>`;
            tblHtml += `<td>${unit}</td>`;
            tblHtml += `<td>${remark}</td>`;
            tblHtml += `</tr>`;
        }
        jQuery('#ItemReturnDetailTable tbody').empty();
        jQuery('#ItemReturnDetailTable tbody').append(tblHtml);
    }
}

// Edit Item Return details
function editDetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();
    fillItemReturnDetailsForm(formIndx, rawIndx);
}

// quotation details form edit
function fillItemReturnDetailsForm(formIndx, rawIndx) {
    let thisForm = jQuery('#ItemReturnDetailsModal');
    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    thisForm.find('#item_id option.temp-item').remove();
    var frmData = dc_details_data[formIndx];

    let itemId = frmData.item_id;
    let itemText = frmData.item_name;
    let sr_table_pk_id = frmData.sr_table_pk_id;
    let name_for_display = frmData.name_for_display;

    if (thisForm.find("#item_id option[value='" + itemId + "']").length === 0) {
        thisForm.find("#item_id").append(
            `<option  class="temp-item" value="${itemId}" data-main_group="${frmData.main_group}" data-item_group="${frmData.item_group}" data-unit="${frmData.unit}"selected>${itemText}</option>`
        );
    }

    thisForm.find("#return_detail_id").val(frmData.return_detail_id ? frmData.return_detail_id : "0");
    thisForm.find("#dc_detail_id").val(frmData.dc_detail_id ? frmData.dc_detail_id : "");
    thisForm.find("#sr_table_unique_id").val(frmData.sr_table_unique_id ? frmData.sr_table_unique_id : "");
    thisForm.find("#dc_number").val(frmData.dc_number ? frmData.dc_number : "");
    thisForm.find("#dc_date").val(frmData.dc_date ? frmData.dc_date : "");
    var decPlaces = (frmData.main_group == 'Industrial X-Ray Films') ? 0 : 3;
    thisForm.find("#grn_qty").val(frmData.grn_qty != "" && frmData.grn_qty != undefined ? parseFloat(frmData.grn_qty).toFixed(decPlaces) : parseFloat(frmData.pending_qty).toFixed(decPlaces));
    if (frmData.main_group == 'Industrial X-Ray Films' || frmData.main_group == 'Material – MPT' || frmData.main_group == 'Chemical – DPT') {
        thisForm.find('#grn_qty').removeClass('isNumberKey').addClass('isNumberKeyNotDot');
        thisForm.find('#grn_qty').attr('onblur', 'formatPoints(this, 0)');
    } else {
        thisForm.find('#grn_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        thisForm.find('#grn_qty').attr('onblur', 'formatPoints(this, 3)');
    }


    thisForm.find("#item_id").val(zeroToEmpty(frmData.item_id)).trigger("change");
    thisForm.find("#item_id").addClass('skip-tab');
    thisForm.find("#pending_qty").val(frmData.pending_qty != "" && frmData.pending_qty != undefined ? parseFloat(frmData.pending_qty).toFixed(decPlaces) : "").attr('readonly', true);
    thisForm.find("#grn_qty").attr('max', parseFloat(frmData.pending_qty).toFixed(decPlaces));
    thisForm.find("#remark").val(frmData.grnd_remark);

    getSrNo(frmData.main_group, frmData.item_id, frmData.sr_table_pk_id).done(function () {
        if (thisForm.find("#sr_table_pk_id option[value='" + sr_table_pk_id + "']").length === 0 && name_for_display != undefined && sr_table_pk_id != 0) {
            thisForm.find("#sr_table_pk_id").append(
                `<option  class="temp-sr_table_pk_id" value="${sr_table_pk_id}" data-sr_table_unique_id="${frmData.sr_table_unique_id}" data-stock_qty="${frmData.io_stock_qty || 0}" selected>${name_for_display}</option>`
            );
        }
        jQuery("#ItemReturnDetailsModal #sr_table_pk_id").val(frmData.sr_table_pk_id ? zeroToEmpty(frmData.sr_table_pk_id) : "").trigger('change.select2');
        jQuery("#ItemReturnDetailsModal #sr_table_unique_id").val(frmData.sr_table_unique_id ? zeroToEmpty(frmData.sr_table_unique_id) : "");
        setTimeout(() => {
            setSelect2Readonly('#ItemReturnDetailsModal #sr_table_pk_id', true);
            jQuery('#ItemReturnDetailsModal #sr_table_pk_id').prop('tabindex', -1);
        }, 1000);

    });

    if (frmData.in_use == true) {
        thisForm.find("#grn_qty").attr('min', parseFloat(frmData.used_qty).toFixed(3));
    }

    if (frmData.sr_table_unique_id == null || frmData.sr_table_unique_id == "" || ['dpt_chemical', 'mpt_material', 'dpt_material'].includes(frmData.sr_table_unique_id)) {
        thisForm.find('#grn_qty').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
    } else {
        thisForm.find('#grn_qty').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
    }
    thisForm.modal('show');
}

function removeDetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        let item = dc_details_data[formIndx];
        console.log(item);


        if (item.return_detail_id && item.return_detail_id != 0) {
            item.mode = "Delete";
        } else {
            dc_details_data.splice(formIndx, 1);
        }
        removeFormObj();

    });
}

function removeFormObj(formIndx) {
    jQuery('#ItemReturnDetailTable tbody').empty();
    fillCustomerDCDetailsTable();
}

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
                        options += `<option data-sr_table_unique_id="${data.sr_no_data[idx].sr_table_unique_id}" data-stock_qty="${data.sr_no_data[idx].sr_qty || 0}" data-current_location_id="${data.sr_no_data[idx].current_location_id || ''}" value="${data.sr_no_data[idx].sr_table_pk_id}">${data.sr_no_data[idx].name_for_display}</option>`;
                    }
                }
                let $dropdown = jQuery('#ItemReturnDetailsForm #sr_table_pk_id');
                $dropdown.empty().append(options);

                // Set value AFTER options loaded
                if (selected_sr) {
                    $dropdown.val(selected_sr);
                }
                $dropdown.trigger('change.select2');
                let selectedOption = $dropdown.find('option:selected');
                jQuery('#ItemReturnDetailsForm #sr_table_unique_id').val(selectedOption.data('sr_table_unique_id') || '');
            } else {
                jQuery('#ItemReturnDetailsForm #sr_table_pk_id').empty().append(options).trigger('change.select2');
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

jQuery('#ItemReturnDetailsForm #sr_table_pk_id').on('change', function () {
    let selectedOption = jQuery(this).find('option:selected');
    let sr_table_unique_id = selectedOption.data('sr_table_unique_id') || '';

    jQuery('#ItemReturnDetailsForm #sr_table_unique_id').val(sr_table_unique_id);
    const dcInput = jQuery('#ItemReturnDetailsForm #grn_qty');

    if (selectedOption.val() !== "") {
        if (['dpt_chemical', 'mpt_material', 'dpt_material'].includes(sr_table_unique_id)) {
            dcInput.prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
            jQuery('#ItemReturnDetailsForm #grn_qty').removeClass('skip-tab');
            setTimeout(function () {
                dcInput.focus();
            }, 100);
        } else {
            dcInput.val((1).toFixed(3)).trigger('change').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
        }
    } else {
        dcInput.val('').trigger('change').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
    }
});

//  Details Form Submit Start
$('#ItemReturnDetailsForm').on('submit', function (e) {
    e.preventDefault();
    let form = this;

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    var data = new FormData(document.getElementById('ItemReturnDetailsForm'));
    var formValue = Object.fromEntries(data.entries());
    var thisModal = jQuery('#ItemReturnDetailsModal');

    var main_group = formValue.main_group ? formValue.main_group : '';
    var sr_table_pk_id = formValue.sr_table_pk_id ? formValue.sr_table_pk_id : '';

    if (!["Industrial X-Ray Films", "General"].includes(main_group)) {
        if (sr_table_pk_id == "") {
            toastr.error('Select Sr. No.');
            return;
        }

    }

    var grn_qty = formValue.grn_qty ? parseFloat(formValue.grn_qty) : 0;
    var pending_qty = formValue.pending_qty ? parseFloat(formValue.pending_qty) : 0;
    var sup_dc_type_id = jQuery('#commonInterLocationTransferForm').find('input[name*="sup_dc_type_id"]:checked').val();
    var MaxQty = Math.min(pending_qty);
    var reason = "";

    if (grn_qty > MaxQty) {
        toastr.error(`GRN Qty. Cannot Be Greater Than Stock ${MaxQty.toFixed(3)}.`);
        return;
    }
    var used_qty = parseFloat(jQuery('#grn_qty').attr('min')) || 0;
    if (used_qty > grn_qty) {
        toastr.error('GRN Qty. Cannot Be Less Than ' + used_qty.toFixed(3));
        return;
    }

    if (grn_qty < 0.001) {
        toastr.error('GRN Return Qty. greater than 0.001.');
        return;
    }

    if (formValue.item_id.trim()) {


        var noDuplicate = true;
        var sr_table_pk_id = formValue.sr_table_pk_id ? formValue.sr_table_pk_id : null;
        var item_id = formValue.item_id ? formValue.item_id : null;

        var currentIndex = formValue.form_type === "edit" ? formValue.form_index : null;

        //var validData = dc_details_data.filter(item => item.mode !== "Delete");

        jQuery.each(dc_details_data, function (index, item) {
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
            var dc_number = formValue.dc_number ? formValue.dc_number : "";
            var dc_date = formValue.dc_date ? formValue.dc_date : "";
            var item_group = formValue.item_group != "" ? formValue.item_group : '';
            var main_group = formValue.main_group != "" ? formValue.main_group : '';
            var io_stock_qty = parseFloat(formValue.io_stock_qty || 0).toFixed(3);

            var pending_qty = formValue.pending_qty != "" ? parseFloat(formValue.pending_qty).toFixed(3) : '';
            var grn_qty = formValue.grn_qty ? parseFloat(formValue.grn_qty).toFixed(3) : parseFloat(0).toFixed(3);

            var sr_table_pk_id = formValue.sr_table_pk_id ? formValue.sr_table_pk_id : '';
            var sr_no = sr_table_pk_id != "" ? formValue.name_for_display ? formValue.name_for_display : thisModal.find('#sr_table_pk_id option:selected').text() : "";

            var unit = thisModal.find('#pend_qty_unit').text();



            var remark = formValue.remark ? formValue.remark : "";
            formValue.name_for_display = sr_table_pk_id != "" ? sr_no : "";
            formValue.unit = unit;
            if (item_id != "") {
                if (formValue.form_type == "edit") {
                    formValue.item_name = item_name;
                    formValue.mode = formValue.return_detail_id == 0 ? "Insert" : "Update";
                    dc_details_data[formValue.form_index] = {
                        ...dc_details_data[formValue.form_index],
                        ...formValue
                    };
                    let tblHtml = ``;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editDetails', 'removeDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                    </td>`;
                    tblHtml += `<td>${dc_number}</td>`;
                    tblHtml += `<td>${dc_date}</td>`;
                    tblHtml += `<td>${item_name}</td>`;
                    tblHtml += `<td>${item_group}</td>`;
                    tblHtml += `<td>${main_group}</td>`;
                    tblHtml += `<td>${sr_no}</td>`;
                    tblHtml += `<td>${pending_qty}</td>`;
                    tblHtml += `<td>${grn_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#ItemReturnDetailTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
                    toastSuccess("Record Updated.");
                } else {
                    formValue.mode = "Insert";
                    formValue.return_detail_id = 0;
                    dc_details_data.push(formValue);
                    let formIndx = dc_details_data.indexOf(formValue);
                    if (jQuery('#ItemReturnDetailTable tbody').find('#noDetails').length > 0) {
                        jQuery('#ItemReturnDetailTable tbody').empty();
                    }

                    let tblHtml = `<tr>`;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editDetails', 'removeDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
                    </td>`;
                    tblHtml += `<td>${dc_number}</td>`;
                    tblHtml += `<td>${dc_date}</td>`;
                    tblHtml += `<td>${item_name}</td>`;
                    tblHtml += `<td>${item_group}</td>`;
                    tblHtml += `<td>${main_group}</td>`;
                    tblHtml += `<td>${sr_no}</td>`;
                    tblHtml += `<td>${pending_qty}</td>`;
                    tblHtml += `<td>${grn_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#ItemReturnDetailTable tbody').append(tblHtml);
                    toastSuccess("Record Inserted.");
                }
            }
            if (formValue.form_type == "edit") {
                thisModal.modal('hide');
            } else {
                let formElement = document.getElementById('ItemReturnDetailsForm');
                formElement.reset();
                jQuery('#item_id').val('').trigger('change');
                jQuery('#io_stock_id').val('');
                jQuery('#grn_qty').val('');
                jQuery('#remark').val('');

                var options = `<option value="">Select Sr. No.</option>`;
                jQuery('#ItemReturnDetailsForm #sr_table_pk_id').empty().append(options);
                setTimeout(function () {

                    let $select = jQuery('#ItemReturnDetailsForm #item_id');
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

// Main Form Submit
$('#commonItemReturnCustomerForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#ItemReturnCustomerModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;

    var dateValue = document.getElementById("return_date").value.trim();

    // validatePercentage
    if (!isValidDate(dateValue)) {
        toastr.error("Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ItemReturnCustomerModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ItemReturnCustomerModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ItemReturnCustomerModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    jQuery('#ItemReturnCustomerModal').find('#submitbtn').prop('disabled', true);

    var formIdnew = jQuery('#ItemReturnCustomerModal').find('#commonItemReturnCustomerForm').find('#id').val();
    var formUrl = formIdnew != undefined && formIdnew != "" ? "update-item_return_customer" : "store-item_return_customer";
    var data = new FormData(form);
    data.append('dc_details_data', JSON.stringify(dc_details_data ?? []));
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));

    let validData = dc_details_data.filter(item => item.mode !== "Delete");

    if (validData.length > 0 && !jQuery.isEmptyObject(validData)) {
        let isValidDetails = true;
        let errorMsg = '';

        $.each(validData, function (index, row) {

            if ((row.sr_table_pk_id === undefined || row.sr_table_pk_id === null || row.sr_table_pk_id === '') && !['Industrial X-Ray Films', 'General'].includes(row.main_group)) {
                errorMsg = `Select SR No.`;
                isValidDetails = false;
                return false;
            }

            if (row.grn_qty === undefined || row.grn_qty === null || row.grn_qty === '' || parseFloat(row.grn_qty) <= 0) {
                errorMsg = `Enter GRN Qty.`;
                isValidDetails = false;
                return false;
            }

        });

        if (!isValidDetails) {
            toastr.error(errorMsg);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');

            jQuery('#ItemReturnCustomerModal')
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
                        jQuery('#ItemReturnCustomerModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formIdnew == undefined || formIdnew == "") {
                        function nextFn() {
                            document.getElementById("commonItemReturnCustomerForm").reset();
                            const form = document.getElementById("commonItemReturnCustomerForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            jQuery('#return_sequence').focus();
                            $("#customer_id").val('').trigger('change');
                            setSelect2Readonly('#customer_id', true);
                            jQuery('#customer_id').removeClass('skip-tab');
                            dc_details_data = [];
                            selectedRows = {};
                            jQuery('#ItemReturnDetailTable tbody').empty();
                            getLatestItemReturnNo();
                            getPendingCustomersForItemReturn();
                            jQuery('#ItemReturnCustomerModal').find('#pending_btn').prop('disabled', true);
                        }

                        if (data.url != "") {
                            toastSuccessPreview(data.response_message, data.url, nextFn);
                        } else {
                            toastSuccess(data.response_message, nextFn);
                        }
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#ItemReturnCustomerModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#ItemReturnCustomerModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#ItemReturnCustomerModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#ItemReturnCustomerModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#ItemReturnCustomerModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        toastr.error('Add At Least One Item Return Detail.');

        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ItemReturnCustomerModal').find('#submitbtn').prop('disabled', false);
    }
});

// Main Form Submit End

jQuery('#CustomerDCPendingModal #checkall-dc_data').click(function () {
    if (jQuery(this).is(':checked')) {
        jQuery("#pendingCustomerDCDataTable").find("[id^='dc_detail_ids_']:not(.in-use)").prop('checked', true).trigger('change');
        jQuery("#pendingCustomerDCDataTable").find("[id^='dc_detail_ids_']").prop('checked', true).trigger('change');
    } else {
        jQuery("#pendingCustomerDCDataTable").find("[id^='dc_detail_ids_']:not(.in-use)").prop('checked', false).trigger('change');
        jQuery("#pendingCustomerDCDataTable").find("[id^='dc_detail_ids_']").prop('checked', false).trigger('change');
    }

});

// get the latest number
function getLatestItemReturnNo() {
    jQuery.ajax({
        url: "get-latest_item_return_customer_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#return_number').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#return_sequence').val(data.number);
                jQuery('#return_date').val(currentDate);
                jQuery('#return_sequence').focus();
            } else {
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            jQuery('#return_number').removeClass('file-loader');
            console.log('Field To Get Latest GRN No.!')
        }
    });
}
// check sequence number duplication
jQuery('#commonItemReturnCustomerForm').find('#return_sequence').on('change', function () {
    checkSequence();
});
function checkSequence() {

    let thisForm = jQuery('#commonItemReturnCustomerForm');
    let val = thisForm.find('#return_sequence').val();

    if (val != "") {

        if (val > 0 == false) {
            toastr.error('Enter Valid Inter GRN No.');
            jQuery('#return_sequence').parent().parent().parent('div.control-group').addClass('error');
            jQuery('#return_sequence').focus();
            jQuery('#return_sequence').val('');

        } else {
            jQuery('#return_sequence').addClass('file-loader');
            jQuery('#return_sequence').parent().parent().parent('div.control-group').removeClass('error');

            var urL = "check-item_return_customer_number_duplication?for=add&return_sequence=" + val;

            var formId = jQuery('#commonItemReturnCustomerForm').find('input[name="id"]').val();

            if (formId !== undefined) { //if form is edit
                urL = "check-item_return_customer_number_duplication?for=edit&return_sequence=" + val + "&id=" + formId;
            }

            jQuery.ajax({
                url: urL,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    jQuery('#return_sequence').removeClass('file-loader');
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        jQuery('#commonItemReturnCustomerForm #return_sequence').val('');
                        const input = document.getElementById('return_sequence'); input?.focus();
                    } else {
                        jQuery('#commonItemReturnCustomerForm #return_number').val(data.latest_no);
                        jQuery('#commonItemReturnCustomerForm #return_sequence').val(val);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#return_sequence').removeClass('file-loader');
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
        jQuery('#return_number').val('');
        jQuery('#return_sequence').val('');
    }

}