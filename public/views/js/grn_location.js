var formId = jQuery('#commonGRNLocationForm').find('input[name="id"]').val();
grn_location_details_data = [];
let selectedRows = {};
$.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
    var row = $('#pendingInterLocationDataTable').DataTable().row(dataIndex).node();
    var checkbox = $(row).find("input[type='checkbox']");
    if (checkbox.is(':checked')) {
        return true;
    }
    return true;
});

// Edit purchase Indent row click
jQuery('#dyntable tbody').on('click', '.edit-grn_location', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#GrnLocationModal').find('#id').val(data["grn_loc_id"]);
    if (data && data["grn_loc_id"]) {
        fetchAndFillItemReturn(data["grn_loc_id"]);
    }
});

// Function to fetch and fill data in edit mode
function fetchAndFillItemReturn(id) {
    if (!id) return;
    jQuery('#GrnLocationModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-grn_location",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.grn_loc_data != null) {
                jQuery('#GrnLocationModal').find('#id').val(data.grn_loc_data.grn_loc_id != "" ? data.grn_loc_data.grn_loc_id : "");
                let url = checkFileRoute + "?id=" + data.grn_loc_data.grn_loc_id + "&name=" + data.grn_loc_data.pdf_name + "&type=grn_location";

                jQuery('#preview_btn').attr('href', url).show();
                jQuery('#GrnLocationModal').find('#grn_loc_number').val(data.grn_loc_data.grn_loc_number != "" ? data.grn_loc_data.grn_loc_number : "");
                jQuery('#GrnLocationModal').find('#grn_loc_date').val(data.grn_loc_data.grn_loc_date != "" ? data.grn_loc_data.grn_loc_date : "");
                jQuery('#GrnLocationModal').find('#grn_loc_sequence').val(data.grn_loc_data.grn_loc_sequence != "" ? data.grn_loc_data.grn_loc_sequence : "");

                jQuery('#GrnLocationModal').find('#mode_of_transport').val(data.grn_loc_data.mode_of_transport != "" ? data.grn_loc_data.mode_of_transport : "");
                jQuery('#GrnLocationModal').find('#transporter').val(data.grn_loc_data.transporter != "" ? data.grn_loc_data.transporter : "");
                jQuery('#GrnLocationModal').find('#vehicle_no').val(data.grn_loc_data.vehicle_no != "" ? data.grn_loc_data.vehicle_no : "");
                jQuery('#GrnLocationModal').find('#sp_note').val(data.grn_loc_data.sp_note != "" ? data.grn_loc_data.sp_note : "");

                jQuery('#GrnLocationModal').find('#prepared_by_user_id').val(data.grn_loc_data.prepared_by_user_id).trigger('change.select2');



                if (data.grn_loc_details_data != "" && data.grn_loc_details_data.length > 0) {

                    grn_location_details_data = data.grn_loc_details_data.map(item => {
                        item.mode = "Update";
                        return item;
                    });
                    fillGRNLocationDetailTable();
                }

                jQuery('#GrnLocationModal').find('#pending_btn').prop('disabled', true);
                jQuery('#GrnLocationModal').find('#submitbtn').prop('disabled', false);

                const form = document.getElementById("GrnLocationModal");
                if (form) form.classList.remove('was-validated');
                jQuery('#GrnLocationModal').find('#add_new').show();
                jQuery('#GrnLocationModal').find('#preview_btn').show();

                jQuery('#GrnLocationModal').find('#grn_loc_sequence').focus();

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
            jQuery('#GrnLocationModal').find('#grn_loc_sequence').focus();
        }
    });
}



jQuery('#GrnLocationModal').on('show.bs.modal', function () {
    var formId = jQuery('#commonGRNLocationForm').find('input[name="id"]').val();
    var hasAccess = jQuery('#commonGRNLocationForm').find('#has_access').val();


    jQuery('#prepared_by_user_id').val(loginUserId).trigger("change");

    if (formId == "" || formId == undefined) {
        getLatestGRNLocationNo();
        GetPendingLocationTransferforGRN();
        jQuery('#GrnLocationModal').find('#add_new').hide();
        jQuery('#GrnLocationModal').find('#preview_btn').hide();
        const input = document.getElementById('grn_loc_sequence');
        input?.focus();
    } else {
        jQuery('#GrnLocationModal').find('#add_new').show();
        jQuery('#GrnLocationModal').find('#preview_btn').show();

    }
});
jQuery('#GrnLocationModal').on('hide.bs.modal', function (e) {
    jQuery('#GRNLocationDetailTable tbody').empty();
    let thisModal = jQuery('#GrnLocationModal');
    thisModal.find('#submitbtn').prop('disabled', false);
    thisModal.find("#id").val("");
    thisModal.find('#grn_loc_sequence').prop('readonly', false);
    jQuery('#GrnLocationModal').find('#add_new').hide();
    jQuery('#GrnLocationModal').find('#preview_btn').hide();
    resetFieds();
    jQuery('#commonGRNLocationForm').trigger("reset");
});



function GetPendingLocationTransferforGRN() {
    var thisModal = jQuery('#GRNLocationPendingModal');
    var thisForm = jQuery('#commonGRNLocationForm');

    jQuery.ajax({
        url: 'get-pending_location_transfer_list_for_grn_location',
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                if (data.response_code == 1 && data.inter_transfer_data.length > 0) {
                    // new code
                    var usedParts = [];
                    var totalDisb = 0;
                    var found = 0;

                    thisForm.find('#GRNLocationDetailTable tbody input[name="form_indx"]').each(function (indx) {
                        let frmIndx = jQuery(this).val();

                        let jbEorkOrderId = grn_location_details_data[frmIndx].inter_location_transfer_details_id;
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
                    if (data.inter_transfer_data.length > 0 && !jQuery.isEmptyObject(data.inter_transfer_data)) {

                        var formIdblank = jQuery('#commonGRNLocationForm').find('#id').val();
                        if (formIdblank != "" && formIdblank != undefined) {
                            jQuery('#pending_btn').prop('disabled', true);
                        } else {
                            jQuery('#pending_btn').prop('disabled', false);
                        }
                        found = 1;
                        for (let idx in data.inter_transfer_data) {
                            var inUse = isUsed(data.inter_transfer_data[idx].inter_location_transfer_details_id);
                            var in_use = data.inter_transfer_data[idx].in_use == true ? 'readonly' : '';
                            totalEntry++;
                            tblHtml += `
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="inter_location_transfer_details_id[]" class="simple-check  ${inUse ? 'in-use' : ''}" id="inter_location_transfer_details_ids_${data.inter_transfer_data[idx].inter_location_transfer_details_id}" value="${data.inter_transfer_data[idx].inter_location_transfer_details_id}" ${inUse ? 'checked' : ''} ${in_use} onchange="checkesCheckboxFrist(this)"/>
                                            </td>
                                            <td>${data.inter_transfer_data[idx].dc_type_id != null ? data.inter_transfer_data[idx].dc_type_id : ''}</td>
                                            <td>${data.inter_transfer_data[idx].dc_number != null ? data.inter_transfer_data[idx].dc_number : ''}</td>
                                            <td>${data.inter_transfer_data[idx].dc_date != null ? data.inter_transfer_data[idx].dc_date : ''}</td>
                                            <td>${data.inter_transfer_data[idx].location_name != null ? data.inter_transfer_data[idx].location_name : ''}</td>
                                            <td>${data.inter_transfer_data[idx].item_name != null ? data.inter_transfer_data[idx].item_name : ''}</td>
                                            <td>${data.inter_transfer_data[idx].item_group != null ? data.inter_transfer_data[idx].item_group : ''}</td>
                                            <td>${data.inter_transfer_data[idx].main_group != null ? data.inter_transfer_data[idx].main_group : ''}</td>
                                            <td>${data.inter_transfer_data[idx].name_for_display != null ? data.inter_transfer_data[idx].name_for_display : ''}</td>
                                            <td>${data.inter_transfer_data[idx].pend_dc_qty != null ? parseFloat(data.inter_transfer_data[idx].pend_dc_qty).toFixed(3) : ''}</td>
                                            <td>${data.inter_transfer_data[idx].unit != null ? data.inter_transfer_data[idx].unit : ''}</td>
                                            <td>${data.inter_transfer_data[idx].remark != null ? data.inter_transfer_data[idx].remark : ''}</td>
                                            <td>${data.inter_transfer_data[idx].prepared_by != null ? data.inter_transfer_data[idx].prepared_by : ''}</td>
                                        </tr>`;
                        }
                    } else {
                        tblHtml += `<tr class="centeralign" id="noPendingPo">
                                    <td colspan="12">No Pending Available</td>
                                </tr>`;
                        jQuery('#pending_btn').prop('disabled', true);
                    }

                    var $table = jQuery("#GRNLocationPendingModal").find('#pendingInterLocationDataTable');
                    if (jQuery.fn.DataTable.isDataTable($table)) {
                        $table.DataTable().clear().destroy();
                    }
                    jQuery('#pendingInterLocationDataTable tbody').empty().append(tblHtml);
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
                        let table = $('#pendingInterLocationDataTable').DataTable();

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



// Main GRN Supplier Form Submit
$('#commonGRNLocationForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#GrnLocationModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;



    var dateValue = document.getElementById("grn_loc_date").value.trim();

    if (!isValidDate(dateValue)) {
        toastr.error("Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#GrnLocationModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#GrnLocationModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#GrnLocationModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    jQuery('#GrnLocationModal').find('#submitbtn').prop('disabled', true);

    var formIdnew = jQuery('#GrnLocationModal').find('#commonGRNLocationForm').find('#id').val();
    var formUrl = formIdnew != undefined && formIdnew != "" ? "update-grn_location" : "store-grn_location";
    var data = new FormData(form);
    data.append('grn_location_details_data', JSON.stringify(grn_location_details_data ?? []));
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (grn_location_details_data.length > 0 && !jQuery.isEmptyObject(grn_location_details_data)) {
        let isValidDetails = true;
        let errorMsg = '';

        $.each(grn_location_details_data, function (index, row) {

            if (row.qty === undefined || row.qty === null || row.qty === '' || parseFloat(row.qty) <= 0) {
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

            jQuery('#GrnLocationModal')
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
                        jQuery('#GrnLocationModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formIdnew == undefined || formIdnew == "") {
                        function nextFn() {
                            document.getElementById("commonGRNLocationForm").reset();
                            const form = document.getElementById("commonGRNLocationForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            jQuery('#grn_sequence').focus();
                            grn_location_details_data = [];
                            var formIdblank = jQuery('#commonGRNLocationForm').find('#id').val('');
                            jQuery('#GrnLocationModal').find('#pending_btn').prop('disabled', true);
                            resetFieds();
                            getLatestGRNLocationNo();
                            GetPendingLocationTransferforGRN();
                        }

                        if (data.url != "") {
                            toastSuccessPreview(data.response_message, data.url, nextFn);
                        } else {
                            toastSuccess(data.response_message, nextFn);
                        }
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#GrnLocationModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#GrnLocationModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#GrnLocationModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#GrnLocationModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#GrnLocationModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        toastr.error('Add At Least One GRN Details.');

        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#GrnLocationModal').find('#submitbtn').prop('disabled', false);
    }
});


// GRN Location Details Form Submit Start
$('#GrnLocationDetailsForm').on('submit', function (e) {

    e.preventDefault();
    let form = this;

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    var data = new FormData(document.getElementById('GrnLocationDetailsForm'));
    var formValue = Object.fromEntries(data.entries());
    var thisModal = jQuery('#GrnLocationDetailsModal');

    var main_group = formValue.main_group ? formValue.main_group : '';
    var sr_table_pk_id = formValue.sr_table_pk_id ? formValue.sr_table_pk_id : '';

    if (!["Industrial X-Ray Films", "General"].includes(main_group)) {
        if (sr_table_pk_id == "") {
            toastr.error('Select Sr. No.');
            return;
        }

    }

    var qty = formValue.qty ? parseFloat(formValue.qty) : 0;

    if (qty < 0.001) {
        toastr.error('GRN Qty. greater than 0.001.');
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

        // var validData = grn_location_details_data.filter(item => item.mode !== "Delete");

        jQuery.each(grn_location_details_data, function (index, item) {
            if (item.mode === "Delete") return true;
            if (sr_table_pk_id != null && item.sr_table_pk_id == sr_table_pk_id && item.item_id == item_id) {
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
            // console.log(formValue,'formValue');
            var unit = thisModal.find('#qty_unit').text();
            var item_id = formValue.item_id ? formValue.item_id : '';
            var item_name = formValue.item_name ? formValue.item_name : thisModal.find('#item_id option[value="' + item_id + '"]').text();
            var dc_number = formValue.dc_number ? formValue.dc_number : '';
            var dc_date = formValue.dc_date ? formValue.dc_date : '';
            var item_group = formValue.item_group != "" ? formValue.item_group : '';
            var main_group = formValue.main_group != "" ? formValue.main_group : '';
            var qty = formValue.qty ? parseFloat(formValue.qty).toFixed(3) : parseFloat(0).toFixed(3);
            var sr_table_pk_id = formValue.sr_table_pk_id ? formValue.sr_table_pk_id : '';
            var sr_no = sr_table_pk_id != "" ? formValue.sr_no ? formValue.sr_no : thisModal.find('#sr_table_pk_id option[value="' + sr_table_pk_id + '"]').text() : "";

            var remark = formValue.remark ? formValue.remark : "";
            formValue.sr_no = sr_no;
            formValue.unit = unit;

            if (item_id != "") {
                if (formValue.form_type == "edit") {
                    formValue.item_name = item_name;
                    formValue.mode = formValue.grn_locd_id == 0 ? "Insert" : "Update";
                    grn_location_details_data[formValue.form_index] = {
                        ...grn_location_details_data[formValue.form_index],
                        ...formValue
                    };
                    let tblHtml = ``;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editGrnDetails', 'removeGrnDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                    </td>`;
                    tblHtml += `<td>${dc_number}</td>`;
                    tblHtml += `<td>${dc_date}</td>`;
                    tblHtml += `<td>${item_name}</td>`;
                    tblHtml += `<td>${item_group}</td>`;
                    tblHtml += `<td>${main_group}</td>`;
                    tblHtml += `<td>${sr_no}</td>`;
                    tblHtml += `<td>${qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#GRNLocationDetailTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
                    toastSuccess("Record Updated.");
                }
            }
            if (formValue.form_type == "edit") {
                thisModal.modal('hide');
            }
        }
    } else {
        toastr.error('Select Item Name');
    }
});



$('#addPendigGRNLocationForm').on('submit', function (e) {

    e.preventDefault();

    jQuery('#full-page-loader')
        .removeClass('hidden-loader')
        .addClass('loader-progress-whole-page');

    jQuery('#GRNLocationPendingModal')
        .find('#submitbtn')
        .prop('disabled', true);

    let chkArr = [];
    var formId = jQuery('#commonGRNLocationForm').find('input[name="id"]').val();

    jQuery("#addPendigGRNLocationForm")
        .find("[id^='inter_location_transfer_details_ids_']:checked")
        .each(function () {
            chkArr.push(jQuery(this).val());
        });

    // No checkbox selected
    if (chkArr.length === 0) {
        toastr.error('Select Inter Location Transfer From Pending.');

        jQuery('#full-page-loader')
            .removeClass('loader-progress-whole-page')
            .addClass('hidden-loader');

        jQuery('#GRNLocationPendingModal')
            .find('#submitbtn')
            .prop('disabled', false);

        return;
    }

    if (formId != undefined && formId != "") {
        var pend_url = "get-inter_location_transfer_part_data_grn_location?id=" + formId;
    } else {
        var pend_url = "get-inter_location_transfer_part_data_grn_location";
    }

    jQuery.ajax({
        url: pend_url,
        type: 'GET',
        data: { inter_location_transfer_details_ids: chkArr.join(',') },
        dataType: 'json',
        success: function (data) {

            if (data.response_code == 1) {

                if (data.inter_transfer_part_data && data.inter_transfer_part_data.length > 0) {
                    grn_location_details_data = [];
                    for (let ind in data.inter_transfer_part_data) {
                        // grn_location_details_data.push(data.inter_transfer_part_data[ind]);

                        let row = {
                            ...data.inter_transfer_part_data[ind],
                            mode: "Insert",
                            return_qty: 1
                        };

                        grn_location_details_data.push(row);
                    }
                    // grn_location_details_data.push(...data.inq_data);
                    fillGRNLocationDetailTable(data.inter_transfer_part_data);

                } else {
                    fillGRNLocationDetailTable([]);
                }

                jQuery("#GRNLocationPendingModal").modal('hide');
                // jQuery('input[name*="po_type_id"]').addClass('skip-tab');
                // setRadioReadonly('input[name*="po_type_id"]', true);
            } else {
                fillGRNLocationDetailTable([]);
            }

            jQuery('#GRNLocationPendingModal')
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

            jQuery('#GRNLocationPendingModal')
                .find('#submitbtn')
                .prop('disabled', false);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');
        }
    });
});

function fillGRNLocationDetailTable() {
    let thisModal = jQuery('#GrnLocationDetailsModal');
    let tblHtml = ``;
    if (grn_location_details_data.length > 0) {
        for (let key in grn_location_details_data) {
            if (grn_location_details_data[key].mode == "Delete") {
                continue;
            }

            var formIndx = grn_location_details_data.indexOf(grn_location_details_data[key]);

            var dc_number = grn_location_details_data[key].dc_number ? grn_location_details_data[key].dc_number : "";
            var dc_date = grn_location_details_data[key].dc_date ? grn_location_details_data[key].dc_date : "";

            if (grn_location_details_data[key].item_name != '' && grn_location_details_data[key].item_name != undefined) {
                var item_name = grn_location_details_data[key].item_name;
            } else {
                var item_name = item_id != '' ? thisModal.find('#item_id option[value="' + item_id + '"]').text() : '';
            }

            var item_group = grn_location_details_data[key].item_group ? grn_location_details_data[key].item_group : "";
            var main_group = grn_location_details_data[key].main_group ? grn_location_details_data[key].main_group : "";
            var sr_no = grn_location_details_data[key].sr_no ? grn_location_details_data[key].sr_no : "";

            var qty = grn_location_details_data[key].qty ? parseFloat(grn_location_details_data[key].qty).toFixed(3) : parseFloat(0).toFixed(3);
            var unit   = grn_location_details_data[key].unit != "" ? grn_location_details_data[key].unit : '';
            var remark = grn_location_details_data[key].remark ? grn_location_details_data[key].remark : "";
            // var in_use = grn_location_details_data[key].in_use == true ? true : false;


            tblHtml += `<tr>`;
            tblHtml += `<td>`;
            tblHtml += DetailsActionDropdown('editGrnDetails', 'removeGrnDetails');
            tblHtml += ` <input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;
            tblHtml += `<td>${dc_number}</td>`;
            tblHtml += `<td>${dc_date}</td>`;
            tblHtml += `<td>${item_name}</td>`;
            tblHtml += `<td>${item_group}</td>`;
            tblHtml += `<td>${main_group}</td>`;
            tblHtml += `<td>${sr_no}</td>`;
            tblHtml += `<td>${qty}</td>`;
            tblHtml += `<td>${unit}</td>`;
            // tblHtml += `<td>${grnd_qty}</td>`;
            // tblHtml += `<td>${grnd_rate_unit}</td>`;
            // tblHtml += `<td class="amount" data-val="${grnd_amount}">${grnd_amount}</td>`;
            tblHtml += `<td>${remark}</td>`;
            tblHtml += `</tr>`;
        }
        jQuery('#GRNLocationDetailTable tbody').empty();
        jQuery('#GRNLocationDetailTable tbody').append(tblHtml);
    }
    // calculateTotalAmount();
}


// edit GRN Location details
function editGrnDetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();
    fillGrnLocationDetailsForm(formIndx, rawIndx);
}

// quotation details form edit
function fillGrnLocationDetailsForm(formIndx, rawIndx) {

    let thisForm = jQuery('#GrnLocationDetailsModal');
    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    thisForm.find('#item_id option.temp-item').remove();
    thisForm.find('#sr_table_pk_id option.temp-pk_id').remove();
    var frmData = grn_location_details_data[formIndx];

    jQuery('#GrnLocationDetailsForm').data('stock_effect_type', frmData.stock_effect_type);
    jQuery('#GrnLocationDetailsForm').data('unit', frmData.unit);

    let itemId = frmData.item_id;
    let itemText = frmData.item_name;



    if (frmData.sr_table_pk_id != null && frmData.sr_no != sr_table_pk_id && frmData.sr_table_pk_id != '' && frmData.sr_table_pk_id != 0) {
        if (thisForm.find("#sr_table_pk_id option[value='" + frmData.sr_table_pk_id + "']").length === 0) {
            thisForm.find("#sr_table_pk_id").append(
                `<option  class="temp-pk_id" value="${frmData.sr_table_pk_id}"  selected>${frmData.sr_no}</option>`
            );
        }

    }

    jQuery("#GrnLocationDetailsForm #sr_table_unique_id").val(frmData.sr_table_unique_id ? zeroToEmpty(frmData.sr_table_unique_id) : "");
    if (thisForm.find("#item_id option[value='" + itemId + "']").length === 0) {
        thisForm.find("#item_id").append(
            `<option  class="temp-item" value="${itemId}" data-main_group="${frmData.main_group}" data-item_group="${frmData.item_group}" data-unit="${frmData.unit}"selected>${itemText}</option>`
        );
    }

    thisForm.find("#grn_locd_id").val(frmData.grn_locd_id ? frmData.grn_locd_id : "0");
    thisForm.find("#inter_location_transfer_details_id").val(frmData.inter_location_transfer_details_id ? frmData.inter_location_transfer_details_id : "0");
    // thisForm.find("#table_unique_id").val(frmData.table_unique_id ? frmData.table_unique_id : "");
    // thisForm.find("#table_pk_id").val(frmData.table_pk_id ? frmData.table_pk_id : "0");


    thisForm.find("#dc_number").val(frmData.dc_number ? frmData.dc_number : "");
    thisForm.find("#dc_date").val(frmData.dc_date ? frmData.dc_date : "");
    thisForm.find("#item_id").val(zeroToEmpty(frmData.item_id)).trigger("change");
    var decPlaces = (frmData.main_group == 'Industrial X-Ray Films' || frmData.main_group == 'Material – MPT' || frmData.main_group == 'Chemical – DPT') ? 0 : 3;
    thisForm.find("#qty").val(frmData.qty != "" && frmData.qty != undefined ? parseFloat(frmData.qty).toFixed(decPlaces) : parseFloat(0).toFixed(decPlaces));
    if (frmData.main_group == 'Industrial X-Ray Films' || frmData.main_group == 'Material – MPT' || frmData.main_group == 'Chemical – DPT') {
        thisForm.find('#qty').removeClass('isNumberKey').addClass('isNumberKeyNotDot');
        thisForm.find('#qty').attr('onblur', 'formatPoints(this, 0)');
    } else {
        thisForm.find('#qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        thisForm.find('#qty').attr('onblur', 'formatPoints(this, 3)');
    }
    thisForm.find("#remark").val(frmData.remark);
    if (['dpt_chemical', 'mpt_material', 'dpt_material'].includes(frmData.sr_table_unique_id) || ['Industrial X-Ray Films', 'General'].includes(frmData.main_group)) {
        thisForm.find('#qty').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
    } else {
        thisForm.find('#qty').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
    }
    setTimeout(function () {
        if (['dpt_chemical', 'mpt_material', 'dpt_material'].includes(frmData.sr_table_unique_id) || ['Industrial X-Ray Films', 'General'].includes(frmData.main_group)) {
            thisForm.find('#qty').focus();
        }
    }, 150);
    thisForm.modal('show');
}

jQuery('#GrnLocationDetailsForm #item_id').on('change', function () {
    let selected = jQuery(this).find('option:selected');
    let main_group = selected.data('main_group');
    let item_group = selected.data('item_group');
    let unit = selected.data('unit');

    let stock_effect_type = jQuery('#GrnLocationDetailsForm').data('stock_effect_type') || '';
    let stored_unit = jQuery('#GrnLocationDetailsForm').data('unit') || '';
    if (stock_effect_type === 'prod. area' || stored_unit === 'SQIN') {
        unit = 'SQIN';
    }

    if (selected.val() != "") {
        jQuery('#item_group').val(item_group);
        jQuery('#main_group').val(main_group);
        jQuery('#qty_unit').text(unit).addClass('ms-1');

        if (main_group == 'Industrial X-Ray Films' || main_group == 'Material – MPT' || main_group == 'Chemical – DPT') {
            jQuery('#qty').removeClass('isNumberKey').addClass('isNumberKeyNotDot');
            jQuery('#qty').attr('onblur', 'formatPoints(this, 0)');
            if (jQuery('#qty').val() !== '') {
                jQuery('#qty').val(parseInt(jQuery('#qty').val()) || '');
            }
        } else {
            jQuery('#qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
            jQuery('#qty').attr('onblur', 'formatPoints(this, 3)');
        }
    } else {
        jQuery('#item_group').val('');
        jQuery('#main_group').val('');
        jQuery('#qty_unit').text('').removeClass('ms-1');
        jQuery('#qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        jQuery('#qty').attr('onblur', 'formatPoints(this, 3)');
    }
});

function removeGrnDetails(th) {

    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        let item = grn_location_details_data[formIndx];

        if (item.grn_locd_id && item.grn_locd_id != 0) {
            item.mode = "Delete";
        } else {
            grn_location_details_data.splice(formIndx, 1);
        }
        removeFormObj();

    });
}

function removeFormObj(formIndx) {
    jQuery('#GRNLocationDetailTable tbody').empty();
    fillGRNLocationDetailTable();
}



function getLatestGRNLocationNo() {
    jQuery.ajax({
        url: "get-latest_grn_location_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#grn_loc_number').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#grn_loc_sequence').val(data.number);
                jQuery('#grn_loc_date').val(currentDate);

            } else {
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            jQuery('#grn_loc_number').removeClass('file-loader');
            console.log('Field To Get Latest Issue No.!')
        }
    });
}


// check sequence number duplication
jQuery('#commonGRNLocationForm').find('#grn_loc_sequence').on('change', function () {
    checkSequence();
});
function checkSequence() {

    let thisForm = jQuery('#commonGRNLocationForm');
    let val = thisForm.find('#grn_loc_sequence').val();

    if (val != "") {

        if (val > 0 == false) {
            toastr.error('Enter Valid Issue No.');
            jQuery('#grn_loc_sequence').parent().parent().parent('div.control-group').addClass('error');
            jQuery('#grn_loc_sequence').focus();
            jQuery('#grn_loc_sequence').val('');

        } else {
            jQuery('#grn_loc_sequence').addClass('file-loader');
            jQuery('#grn_loc_sequence').parent().parent().parent('div.control-group').removeClass('error');

            var urL = "check-grn_location_number_duplication?for=add&grn_loc_sequence=" + val;

            var formId = jQuery('#commonGRNLocationForm').find('input[name="id"]').val();

            if (formId !== undefined) { //if form is edit
                urL = "check-grn_location_number_duplication?for=edit&grn_loc_sequence=" + val + "&id=" + formId;
            }

            jQuery.ajax({
                url: urL,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    jQuery('#grn_loc_sequence').removeClass('file-loader');
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        jQuery('#commonGRNLocationForm #grn_loc_sequence').val('');
                        const input = document.getElementById('grn_loc_sequence'); input?.focus();
                    } else {
                        jQuery('#commonGRNLocationForm #grn_loc_number').val(data.latest_no);
                        jQuery('#commonGRNLocationForm #grn_loc_sequence').val(val);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#grn_loc_sequence').removeClass('file-loader');
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
        jQuery('#grn_loc_number').val('');
        jQuery('#grn_loc_sequence').val('');
    }

}


jQuery('#checkall-grn_data').click(function () {
    if (jQuery(this).is(':checked')) {
        jQuery("#pendingInterLocationDataTable").find("[id^='inter_location_transfer_details_ids_']:not(.in-use)").prop('checked', true).trigger('change');
        jQuery("#pendingInterLocationDataTable").find("[id^='inter_location_transfer_details_ids_']").prop('checked', true).trigger('change');
    } else {
        jQuery("#pendingInterLocationDataTable").find("[id^='inter_location_transfer_details_ids_']:not(.in-use)").prop('checked', false).trigger('change');
        jQuery("#pendingInterLocationDataTable").find("[id^='inter_location_transfer_details_ids_']").prop('checked', false).trigger('change');
    }

});


function checkesCheckboxFrist($this) {
    var $row = jQuery($this).closest('tr');
    var inter_location_transfer_details_id = jQuery($this).val();
    if (jQuery($this).is(':checked')) {
        selectedRows[inter_location_transfer_details_id] = $row;
    } else {
        delete selectedRows[inter_location_transfer_details_id];
    }
}



jQuery('#GRNLocationPendingModal').on('show.bs.modal', function (e) {
    var dt = jQuery('#pendingInterLocationDataTable').DataTable();
    fixDataTableColumnsUntilAdjusted(dt);
    var usedParts = [];
    if (grn_location_details_data && grn_location_details_data.length > 0) {
        grn_location_details_data.forEach(function (item) {
            if (item.inter_location_transfer_details_id) {
                usedParts.push(Number(item.inter_location_transfer_details_id));
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
    jQuery('#pendingInterLocationDataTable tbody tr').each(function (indx) {
        totalEntry++;
        var checkField = jQuery(this).find('input[name="inter_location_transfer_details_id[]"]');
        var partId = jQuery(checkField).val();
        var inUse = isUsed(partId);

        if (formId == undefined) {
            if (grn_location_details_data.length > 0) {
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
jQuery('#GRNLocationPendingModal').on('hide.bs.modal', function (e) {
    selectedRows = {};
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('mode_of_transport');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }
});
jQuery('#GrnLocationDetailsModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('mode_of_transport');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }
});


jQuery('#resetbtn').on('click', function () {
    grn_location_details_data = [];
    selectedRows = {};


    var formId = jQuery('#GrnLocationModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonGRNLocationForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        resetFieds();
        jQuery('#prepared_by_user_id').val(loginUserId).trigger("change");
        getLatestGRNLocationNo();
        GetPendingLocationTransferforGRN();
    } else {
        fetchAndFillItemReturn(formId);
    }
});

jQuery('#GrnLocationModal').on('click', '#add_new', function () {

    document.getElementById("commonGRNLocationForm").reset();
    const form = document.getElementById("commonGRNLocationForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    resetFieds();

    jQuery('#prepared_by_user_id').val(loginUserId).trigger("change");
    getLatestGRNLocationNo();
    GetPendingLocationTransferforGRN();
    jQuery('#commonGRNLocationForm').find('input[name="id"]').val('');

    jQuery('#GrnLocationModal').find('#add_new').hide();
    jQuery('#GrnLocationModal').find('#preview_btn').hide();
});

function resetFieds() {

    jQuery("#commonGRNLocationForm .toggleModalBtn").prop('disabled', false);
    jQuery("#GrnLocationDetailsForm #sr_table_unique_id").val('');
    jQuery("#GrnLocationDetailsForm #grn_locd_id").val('');
    jQuery("#GrnLocationDetailsForm #inter_location_transfer_details_id").val('');
    // jQuery("#item_id").val("").removeClass('skip-tab');
    grn_location_details_data = [];
    selectedRows = {};
    jQuery('#GRNLocationDetailTable tbody').empty();
    const input = document.getElementById('grn_loc_sequence'); input?.focus();

}