issue_details_data = [];
var formId = jQuery('#commonItemIssueForm').find('input[name="id"]').val();


// Edit purchase Indent row click
jQuery('#dyntable tbody').on('click', '.edit-item_issue', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#ItemIssueModal').find('#id').val(data["issue_id"]);
    if (data && data["issue_id"]) {
        fetchAndFillItemIssue(data["issue_id"]);
    }
});

// Function to fetch and fill purchase indent data
function fetchAndFillItemIssue(id) {
    if (!id) return;
    jQuery('#ItemIssueModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-item_issue",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.issue_data != null) {
                jQuery('#ItemIssueModal').find('#id').val(data.issue_data.issue_id != "" ? data.issue_data.issue_id : "");
                let url = checkFileRoute + "?id=" + data.issue_data.issue_id + "&name=" + data.issue_data.pdf_name + "&type=item_issue";

                jQuery('#preview_btn').attr('href', url).show();
                jQuery('#ItemIssueModal').find('#issue_number').val(data.issue_data.issue_number != "" ? data.issue_data.issue_number : "");
                jQuery('#ItemIssueModal').find('#issue_date').val(data.issue_data.issue_date != "" ? data.issue_data.issue_date : "");
                jQuery('#ItemIssueModal').find('#issue_sequence').val(data.issue_data.issue_sequence != "" ? data.issue_data.issue_sequence : "");



                jQuery('#ItemIssueModal').find('#issue_to').val(data.issue_data.issue_to != "" ? data.issue_data.issue_to : "");
                jQuery('#ItemIssueModal').find('#special_note').val(data.issue_data.special_note != "" ? data.issue_data.special_note : "");
                jQuery('#ItemIssueModal').find('#prepared_by_user_id').val(data.issue_data.prepared_by_user_id).trigger('change.select2');

                issue_details_data = [];
                if (data.issue_details_data != "" && data.issue_details_data.length > 0) {
                    issue_details_data = data.issue_details_data.map(item => {
                        item.mode = "Update";
                        return item;
                    });
                    fillItemIssueDetailsTable();
                }

                jQuery('#ItemIssueModal').find('#submitbtn').prop('disabled', false);

                const form = document.getElementById("ItemIssueModal");
                if (form) form.classList.remove('was-validated');
                jQuery('#ItemIssueModal').find('#add_new').show();
                jQuery('#ItemIssueModal').find('#preview_btn').show();

                jQuery('#ItemIssueModal').find('#issue_sequence').focus();

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


jQuery('#ItemIssueModal').on('show.bs.modal', function () {
    var formId = jQuery('#commonItemIssueForm').find('input[name="id"]').val();
    var hasAccess = jQuery('#commonItemIssueForm').find('#has_access').val();


    jQuery('#prepared_by_user_id').val(loginUserId).trigger("change");

    if (formId == "" || formId == undefined) {
        getLatestItemIssueNo();
        jQuery('#ItemIssueModal').find('#add_new').hide();
        jQuery('#ItemIssueModal').find('#preview_btn').hide();
        const input = document.getElementById('issue_sequence');
        input?.focus();
    } else {
        jQuery('#ItemIssueModal').find('#add_new').show();
        jQuery('#ItemIssueModal').find('#preview_btn').show();

    }
});

jQuery('#ItemIssueModal').on('hide.bs.modal', function (e) {
    jQuery('#ItemIssueDetailsTable tbody').empty();
    let thisModal = jQuery('#ItemIssueModal');
    thisModal.find("#id").val("");
    var thisForm = jQuery('#ItemIssueDetailsModal');
    thisForm.find("#issue_detail_id").val(0);
    issue_details_data = [];
    resetFieds();
    jQuery('#ItemIssueModal').find('#add_new').hide();
    jQuery('#ItemIssueModal').find('#preview_btn').hide();

});

jQuery('#ItemIssueDetailsModal').on('show.bs.modal', function () {
    let thisModal = jQuery('#ItemIssueDetailsModal');
    var form_type = thisModal.find("#form_type").val();
    var issue_detail_id = thisModal.find("#issue_detail_id").val();

    // if (form_type == 'add') {
    //     jQuery('#item_id').val('').trigger('change');
    //     jQuery('#issue_type').val('Consumption').trigger('change');
    // }


    if (form_type == "edit") {
        jQuery('#item_id').addClass('skip-tab');
        jQuery('#sr_table_pk_id').addClass('skip-tab');

        let main_group = jQuery('#ItemIssueDetailsForm #main_group').val();
        if (main_group == 'Industrial X-Ray Films') {
            jQuery('#ItemIssueDetailsForm #issue_type').addClass('skip-tab');
            setSelect2Readonly("#ItemIssueDetailsForm #issue_type", true);
        } else {
            jQuery('#ItemIssueDetailsForm #issue_type').removeClass('skip-tab');
            setSelect2Readonly("#ItemIssueDetailsForm #issue_type", false);
        }
    } else {
        jQuery('#ItemIssueDetailsForm #wastage_reason_id').val('').trigger('change');
        jQuery('#ItemIssueDetailsForm #wastage_reason_id').addClass('skip-tab');
        setSelect2Readonly("#ItemIssueDetailsForm #wastage_reason_id", true);
        // jQuery('#item_id').val('').trigger('change');
        jQuery('#item_id').removeClass('skip-tab');
        // jQuery('#sr_table_pk_id').removeClass('skip-tab');
        setTimeout(function () {
            let sel = jQuery('#item_id')
                .nextAll('.select2-container')
                .first()
                .find('.select2-selection');
            sel.attr('tabindex', 0).focus();
        }, 20);
    }

});


jQuery('#ItemIssueDetailsModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#ItemIssueDetailsModal');
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    thisModal.find("#issue_detail_id").val(0);
    jQuery(this).find('#item_id option.temp-item').remove();
    jQuery(this).find('#sr_table_pk_id option.temp-sr_table_pk_id').remove();
    jQuery('#InterLocationTransferDetailsForm').trigger("reset");
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('issue_to');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }
});


jQuery('#ItemIssueModal').on('click', '#add_new', function () {

    document.getElementById("commonItemIssueForm").reset();
    const form = document.getElementById("commonItemIssueForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    resetFieds();

    jQuery('#prepared_by_user_id').val(loginUserId).trigger("change");
    getLatestItemIssueNo();
    jQuery('#commonItemIssueForm').find('input[name="id"]').val('');

    jQuery('#ItemIssueModal').find('#add_new').hide();
    jQuery('#ItemIssueModal').find('#preview_btn').hide();
});



jQuery('#issue_type').on('change', function () {
    var selected = jQuery(this).find('option:selected');
    if (selected.val() == 'Consumption' || selected.val() == 'Production Area') {
        jQuery('#ItemIssueDetailsForm #wastage_reason_id').val('').trigger('change');
        jQuery('#ItemIssueDetailsForm #wastage_reason_id').addClass('skip-tab');
        setSelect2Readonly("#ItemIssueDetailsForm #wastage_reason_id", true);

    } else {
        jQuery('#ItemIssueDetailsForm #wastage_reason_id').removeClass('skip-tab');
        setSelect2Readonly("#ItemIssueDetailsForm #wastage_reason_id", false);

    }
});

jQuery('#item_id').on('change', function () {
    let selected = jQuery(this).find('option:selected');
    let main_group = selected.data('main_group');
    let item_group = selected.data('item_group');
    let io_stock_qty = selected.data('io_stock_qty');
    let stock_rate_unit = selected.data('stock_rate_unit');
    let unit = selected.data('unit');
    let conv_factor = selected.data('conv_factor') || 0;
    let item_type = selected.data('item_type') || '';
    let item_id = selected.val();

    // Dynamically manage "Production Area" option based on group
    let issueTypeSelect = jQuery('#ItemIssueDetailsForm #issue_type');
    let hasProdArea = issueTypeSelect.find('option[value="Production Area"]').length > 0;
    if (selected.val() != "" && main_group == 'Industrial X-Ray Films') {
        if (!hasProdArea) {
            issueTypeSelect.append('<option value="Production Area">Production Area</option>');
        }
    } else {
        if (hasProdArea) {
            issueTypeSelect.find('option[value="Production Area"]').remove();
        }
    }
    issueTypeSelect.trigger('change.select2');

    if (selected.val() != "") {
        jQuery('#item_group').val(item_group);
        jQuery('#main_group').val(main_group);
        jQuery('#stock_rate_unit').val(stock_rate_unit);
        jQuery('#io_stock_qty').val(io_stock_qty ? parseFloat(io_stock_qty).toFixed(3) : parseFloat(0).toFixed(3));
        jQuery('#issue_stock_qty_unit').text(unit).addClass('ms-1');
        jQuery('#issue_qty_unit').text(unit).addClass('ms-1');
        jQuery('#conv_factor').val(conv_factor);
        jQuery('#item_type').val(item_type);
        console.log(main_group);
        

        if (main_group == 'Industrial X-Ray Films' || main_group == 'Material – MPT' || main_group == 'Chemical – DPT') {
            jQuery('#issue_qty').removeClass('isNumberKey').addClass('isNumberKeyNotDot');
            jQuery('#issue_qty').attr('onblur', 'formatPoints(this, 0)');
            if (jQuery('#issue_qty').val() !== '') {
                jQuery('#issue_qty').val(parseInt(jQuery('#issue_qty').val()) || '');
            }
        } else {
            jQuery('#issue_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
            jQuery('#issue_qty').attr('onblur', 'formatPoints(this, 3)');
        }

        if (main_group == 'Industrial X-Ray Films' || main_group == 'General') {
            jQuery('#ItemIssueDetailsForm #sr_table_pk_id').empty().append('<option value="">Select Sr. No.</option>');
            jQuery('#ItemIssueDetailsForm #sr_table_pk_id').addClass('skip-tab');
            setSelect2Readonly("#ItemIssueDetailsForm #sr_table_pk_id", true);

            if (main_group == 'Industrial X-Ray Films') {
                jQuery('#ItemIssueDetailsForm #issue_type').val('Production Area').trigger('change');
                jQuery('#ItemIssueDetailsForm #issue_type').addClass('skip-tab');
                setSelect2Readonly("#ItemIssueDetailsForm #issue_type", true);
            } else {
                jQuery('#ItemIssueDetailsForm #issue_type').val('Consumption').trigger('change');
                jQuery('#ItemIssueDetailsForm #issue_type').removeClass('skip-tab');
                setSelect2Readonly("#ItemIssueDetailsForm #issue_type", false);
            }
            jQuery('#ItemIssueDetailsForm #wastage_reason_id').val('').trigger('change');

            jQuery('#issue_qty').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');



        } else {
            jQuery('#ItemIssueDetailsForm #sr_table_pk_id').removeClass('skip-tab');
            setSelect2Readonly("#ItemIssueDetailsForm #sr_table_pk_id", false);
            getSrNo(main_group, item_id);

            if (main_group == 'Material – MPT' || main_group == 'Chemical – DPT') {
                jQuery('#ItemIssueDetailsForm #issue_type').val('Consumption').trigger('change');
                jQuery('#ItemIssueDetailsForm #issue_type').removeClass('skip-tab');
                setSelect2Readonly("#ItemIssueDetailsForm #issue_type", false);


            } else {
                jQuery('#ItemIssueDetailsForm #issue_type').val('Wastage').trigger('change');
                jQuery('#ItemIssueDetailsForm #issue_type').addClass('skip-tab');
                setSelect2Readonly("#ItemIssueDetailsForm #issue_type", true);

            }
        }


    } else {
        jQuery('#item_group').val('');
        jQuery('#main_group').val('');
        jQuery('#io_stock_qty').val('');
        jQuery('#issue_stock_qty_unit').text('').removeClass('ms-1');
        jQuery('#issue_qty_unit').text('').removeClass('ms-1');
        jQuery('#conv_factor').val(0);
        jQuery('#item_type').val('');
        jQuery('#ItemIssueDetailsForm #sr_table_pk_id').empty().append('<option value="">Select Sr. No.</option>');
        jQuery('#ItemIssueDetailsForm #sr_table_pk_id').addClass('skip-tab');
        setSelect2Readonly("#ItemIssueDetailsForm #sr_table_pk_id", true);
        jQuery('#io_stock_qty').val(parseFloat(0).toFixed(3));
        jQuery('#ItemIssueDetailsForm #issue_type').val('Consumption').trigger('change');
        jQuery('#ItemIssueDetailsForm #issue_type').removeClass('skip-tab');
        setSelect2Readonly("#ItemIssueDetailsForm #issue_type", false);
        jQuery('#ItemIssueDetailsForm #wastage_reason_id').val('').trigger('change');
        jQuery('#issue_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        jQuery('#issue_qty').attr('onblur', 'formatPoints(this, 3)');
        jQuery('#issue_qty').val('').trigger('change').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');

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
                        options += `<option data-sr_table_unique_id="${data.sr_no_data[idx].sr_table_unique_id}" data-stock_qty="${data.sr_no_data[idx].sr_qty || 0}" data-current_location_id="${data.sr_no_data[idx].current_location_id || ''}" value="${data.sr_no_data[idx].sr_table_pk_id}">${data.sr_no_data[idx].name_for_display}</option>`;
                    }
                }
                let $dropdown = jQuery('#ItemIssueDetailsForm #sr_table_pk_id');
                $dropdown.empty().append(options);

                // Set value AFTER options loaded
                if (selected_sr) {
                    $dropdown.val(selected_sr);
                }
                $dropdown.trigger('change.select2');
                let selectedOption = $dropdown.find('option:selected');
                jQuery('#sr_table_unique_id').val(selectedOption.data('sr_table_unique_id') || '');
            } else {
                jQuery('#ItemIssueDetailsForm #sr_table_pk_id').empty().append(options).trigger('change.select2');
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
jQuery('#ItemIssueDetailsForm #sr_table_pk_id').on('change', function () {
    let selectedOption = jQuery(this).find('option:selected');
    let sr_table_unique_id = selectedOption.data('sr_table_unique_id') || '';

    jQuery('#sr_table_unique_id').val(sr_table_unique_id);
    if (selectedOption.val() != "") {
        if (['dpt_chemical', 'mpt_material', 'dpt_material'].includes(sr_table_unique_id)) {
            let batchStock = selectedOption.data('stock_qty') || 0;
            jQuery('#io_stock_qty').val(parseFloat(batchStock).toFixed(3));
            jQuery('#issue_qty').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
            jQuery('#ItemIssueDetailsForm #issue_qty').removeClass('skip-tab');
            setTimeout(function () {
                jQuery('#issue_qty').focus();
            }, 100);
        } else {
            jQuery('#issue_qty').val(parseFloat(1).toFixed(3)).trigger('change').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1');
            jQuery('#ItemIssueDetailsForm #issue_qty').addClass('skip-tab');
        }
    } else {
        jQuery('#issue_qty').val('').trigger('change').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
        jQuery('#ItemIssueDetailsForm #issue_qty').removeClass('skip-tab');
        let itemSelected = jQuery('#item_id').find('option:selected');
        let io_stock_qty = itemSelected.data('io_stock_qty') || 0;
        jQuery('#io_stock_qty').val(parseFloat(io_stock_qty).toFixed(3));
    }
});





// Purchase Order Details Form Submit Start
$('#ItemIssueDetailsForm').on('submit', function (e) {

    e.preventDefault();
    let form = this;


    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    var data = new FormData(document.getElementById('ItemIssueDetailsForm'));
    var formValue = Object.fromEntries(data.entries());
    var thisModal = jQuery('#ItemIssueDetailsModal');

    var main_group = formValue.main_group ? formValue.main_group : '';
    var sr_table_pk_id = formValue.sr_table_pk_id ? formValue.sr_table_pk_id : '';

    if (!["Industrial X-Ray Films", "General"].includes(main_group)) {
        if (sr_table_pk_id == "") {
            toastr.error('Select Sr. No.');
            return;
        }

    }

    var issue_type = formValue.issue_type ? formValue.issue_type : '';

    if (issue_type == 'Wastage') {
        if (formValue.wastage_reason_id == '') {
            toastr.error('Select Wastage Reason.');
            return;
        }
    }


    var issue_qty = formValue.issue_qty ? parseFloat(formValue.issue_qty) : 0;
    var io_stock_qty = formValue.io_stock_qty ? parseFloat(formValue.io_stock_qty) : 0;


    if (issue_qty > io_stock_qty) {
        toastr.error('Issue Qty. cannot be greater than Stock ' + io_stock_qty.toFixed(3) + '.');
        return;
    }

    if (issue_qty < 0.001) {
        toastr.error('Enter Issue Qty. greater than 0.001.');
        return;
    }

    if (formValue.item_id.trim()) {

        var noDuplicate = true;
        var sr_table_pk_id = formValue.sr_table_pk_id ? formValue.sr_table_pk_id : null;
        var item_id = formValue.item_id ? formValue.item_id : null;

        var currentIndex = formValue.form_type === "edit" ? formValue.form_index : null;

        // var validData = issue_details_data.filter(item => item.mode !== "Delete");



        jQuery.each(issue_details_data, function (index, item) {
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
            var item_id = formValue.item_id ? formValue.item_id : null;
            var item_name = formValue.item_name ? formValue.item_name : thisModal.find('#item_id option[value="' + item_id + '"]').text();
            var item_group = formValue.item_group != null ? formValue.item_group : null;
            var main_group = formValue.main_group != null ? formValue.main_group : null;
            var issue_qty = formValue.issue_qty != 0 ? parseFloat(formValue.issue_qty).toFixed(3) : parseFloat(0).toFixed(3);
            var issue_type = formValue.issue_type != null ? formValue.issue_type : '';
            var wastage_reason_id = formValue.wastage_reason_id != null ? formValue.wastage_reason_id : '';
            var wastage_reason = formValue.wastage_reason_id != null && formValue.wastage_reason_id != "" ? thisModal.find('#wastage_reason_id option[value="' + wastage_reason_id + '"]').text() : '';
            var sr_no = formValue.sr_table_pk_id != "" && formValue.sr_table_pk_id != null ? thisModal.find('#sr_table_pk_id option[value="' + sr_table_pk_id + '"]').text() : "";
            var io_stock_qty = formValue.io_stock_qty != 0 ? parseFloat(formValue.io_stock_qty).toFixed(3) : parseFloat(0).toFixed(3);
            var remark = formValue.remark != null ? formValue.remark : '';
            var conv_factor = formValue.conv_factor ? parseInt(formValue.conv_factor) : 0;
            formValue.conv_factor = conv_factor;
            formValue.item_type = formValue.item_type || '';
            formValue.sr_no = sr_no;
            var unit = thisModal.find('#issue_qty_unit').text();
            formValue.unit = unit;

            if (item_id != "") {
                if (formValue.form_type == "edit") {
                    formValue.item_name = item_name;
                    formValue.mode = formValue.issue_detail_id == 0 ? "Insert" : "Update";
                    issue_details_data[formValue.form_index] = {
                        ...issue_details_data[formValue.form_index],
                        ...formValue
                    };
                    var in_use = issue_details_data[formValue.form_index].in_use == true ? true : false;
                    let tblHtml = ``;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editItemIssueDetails', 'removeItemIssueDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                    </td>`;

                    tblHtml += `<td>${item_name}</td>`;
                    tblHtml += `<td>${item_group}</td>`;
                    tblHtml += `<td>${main_group}</td>`;
                    tblHtml += `<td>${sr_no}</td>`;
                    tblHtml += `<td>${io_stock_qty}</td>`;
                    tblHtml += `<td>${issue_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${issue_type}</td>`;
                    tblHtml += `<td>${wastage_reason}</td>`;
                    tblHtml += `<td>${remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#ItemIssueDetailsTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
                    toastSuccess("Record Updated.");
                } else {
                    formValue.issue_detail_id = 0;
                    formValue.mode = "Insert";
                    issue_details_data.push(formValue)
                    let formIndx = issue_details_data.indexOf(formValue);
                    if (jQuery('#ItemIssueDetailsTable tbody').find('#noDetails').length > 0) {
                        jQuery('#ItemIssueDetailsTable tbody').empty();
                    }

                    let tblHtml = `<tr>`;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editItemIssueDetails', 'removeItemIssueDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
                    </td>`;

                    tblHtml += `<td>${item_name}</td>`;
                    tblHtml += `<td>${item_group}</td>`;
                    tblHtml += `<td>${main_group}</td>`;
                    tblHtml += `<td>${sr_no}</td>`;
                    tblHtml += `<td>${io_stock_qty}</td>`;
                    tblHtml += `<td>${issue_qty}</td>`;
                    tblHtml += `<td>${unit}</td>`;
                    tblHtml += `<td>${issue_type}</td>`;
                    tblHtml += `<td>${wastage_reason}</td>`;
                    tblHtml += `<td>${remark}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#ItemIssueDetailsTable tbody').append(tblHtml);
                    toastSuccess("Record Inserted.");
                }
            }
            if (formValue.form_type == "edit") {
                thisModal.modal('hide');
            } else {
                let formElement = document.getElementById('ItemIssueDetailsForm');
                formElement.reset();
                jQuery('#item_id').val('').trigger('change');
                jQuery('#item_group').val('');
                jQuery('#main_group').val('');
                jQuery('#io_stock_qty').val('');
                jQuery('#issue_qty').val('');
                jQuery('#remark').val('');
                jQuery('#sr_table_unique_id').val('');
                jQuery('#conv_factor').val('');
                jQuery('#item_type').val('');

                setTimeout(function () {

                    let $select = jQuery('#ItemIssueDetailsForm #item_id');
                    $select.one('select2:opening', function (e) {
                        e.preventDefault();
                    });

                    let sel = $select.nextAll('.select2-container').first().find('.select2-selection');
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


function editItemIssueDetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();
    fillItemIssueDetailsForm(formIndx, rawIndx);
}

// purchase order details form edit
function fillItemIssueDetailsForm(formIndx, rawIndx) {
    let thisForm = jQuery('#ItemIssueDetailsModal');
    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    var frmData = issue_details_data[formIndx];
    thisForm.find("#issue_detail_id").val(frmData.issue_detail_id);
    thisForm.find('#item_id option.temp-item').remove();
    let itemId = frmData.item_id;
    let itemText = frmData.item_name;

    var sr_table_pk_id = frmData.sr_table_pk_id;
    var sr_no = frmData.sr_no;

    // check if option exists
    if (thisForm.find("#item_id option[value='" + itemId + "']").length === 0) {
        thisForm.find("#item_id").append(
            `<option  class="temp-item" value="${itemId}" data-main_group="${frmData.main_group}" data-item_group="${frmData.item_group}" data-io_stock_qty="${frmData.io_stock_qty}" data-conv_factor="${frmData.conv_factor || 0}" data-item_type="${frmData.item_type || ''}" selected>${itemText}</option>`
        );
    }
    thisForm.find("#item_id").val(itemId).trigger('change');
    thisForm.find("#conv_factor").val(frmData.conv_factor || 0);
    thisForm.find("#item_type").val(frmData.item_type || '');
    thisForm.find("#io_stock_qty").val(parseFloat(frmData.io_stock_qty || 0).toFixed(3));
    if (frmData.issue_detail_id != 0) {
        thisForm.find('#item_id').addClass('skip-tab');
        thisForm.find('#sr_table_pk_id').addClass('skip-tab');
    } else {
        thisForm.find('#item_id').removeClass('skip-tab');
        if (frmData.main_group == 'Industrial X-Ray Films' || frmData.main_group == 'General') {
            thisForm.find('#sr_table_pk_id').addClass('skip-tab');
        } else {
            thisForm.find('#sr_table_pk_id').removeClass('skip-tab');
        }
    }
    getSrNo(frmData.main_group, frmData.item_id, frmData.sr_table_pk_id).done(function () {
        if (thisForm.find("#sr_table_pk_id option[value='" + sr_table_pk_id + "']").length === 0 && sr_no != undefined && sr_table_pk_id != 0) {
            thisForm.find("#sr_table_pk_id").append(
                `<option  class="temp-sr_table_pk_id" value="${sr_table_pk_id}" data-sr_table_unique_id="${frmData.sr_table_unique_id}" data-stock_qty="${frmData.io_stock_qty || 0}" selected>${sr_no}</option>`
            );
        }
        jQuery("#ItemIssueDetailsForm #sr_table_pk_id").val(frmData.sr_table_pk_id ? frmData.sr_table_pk_id : "").trigger('change.select2');
        jQuery("#ItemIssueDetailsForm #sr_table_unique_id").val(frmData.sr_table_unique_id ? zeroToEmpty(frmData.sr_table_unique_id) : "");
    });
    var decPlaces = (frmData.main_group == 'Industrial X-Ray Films' || frmData.main_group == 'Material – MPT' || frmData.main_group == 'Chemical – DPT') ? 0 : 3;
    thisForm.find("#issue_qty").val(frmData.issue_qty != "" ? parseFloat(frmData.issue_qty).toFixed(decPlaces) : "");
    if (frmData.main_group == 'Industrial X-Ray Films' || frmData.main_group == 'Material – MPT' || frmData.main_group == 'Chemical – DPT') {
        thisForm.find('#issue_qty').removeClass('isNumberKey').addClass('isNumberKeyNotDot');
        thisForm.find('#issue_qty').attr('onblur', 'formatPoints(this, 0)');
    } else {
        thisForm.find('#issue_qty').removeClass('isNumberKeyNotDot').addClass('isNumberKey');
        thisForm.find('#issue_qty').attr('onblur', 'formatPoints(this, 3)');
    }
    if (frmData.main_group == 'Industrial X-Ray Films' || frmData.main_group == 'General') {

        thisForm.find('#issue_qty').trigger('change').prop('readonly', false).removeClass('skip-tab').attr('tabindex', '0')

    } else if (['dpt_chemical', 'mpt_material', 'dpt_material'].includes(frmData.sr_table_unique_id)) {
        thisForm.find('#issue_qty').trigger('change').prop('readonly', false).removeClass('skip-tab').removeAttr('tabindex');
    } else {
        thisForm.find('#issue_qty').trigger('change').prop('readonly', true).addClass('skip-tab').attr('tabindex', '-1')
    }
    thisForm.find("#remark").val(frmData.remark != "" ? frmData.remark : "");
    let issueTypeSelect = jQuery('#ItemIssueDetailsForm #issue_type');
    let hasProdArea = issueTypeSelect.find('option[value="Production Area"]').length > 0;
    if (frmData.main_group == 'Industrial X-Ray Films') {
        if (!hasProdArea) {
            issueTypeSelect.append('<option value="Production Area">Production Area</option>');
        }
        jQuery("#ItemIssueDetailsForm #issue_type").val('Production Area').trigger('change.select2');
        jQuery('#ItemIssueDetailsForm #issue_type').addClass('skip-tab');
        setSelect2Readonly("#ItemIssueDetailsForm #issue_type", true);
    } else {
        if (hasProdArea) {
            issueTypeSelect.find('option[value="Production Area"]').remove();
        }
        jQuery("#ItemIssueDetailsForm #issue_type").val(frmData.issue_type ? frmData.issue_type : "").trigger('change');
        if (frmData.main_group == 'General' || frmData.main_group == 'Material – MPT' || frmData.main_group == 'Chemical – DPT') {
            jQuery('#ItemIssueDetailsForm #issue_type').removeClass('skip-tab');
            setSelect2Readonly("#ItemIssueDetailsForm #issue_type", false);
        } else {
            jQuery('#ItemIssueDetailsForm #issue_type').addClass('skip-tab');
            setSelect2Readonly("#ItemIssueDetailsForm #issue_type", true);
        }
    }
    jQuery("#ItemIssueDetailsForm #wastage_reason_id").val(frmData.wastage_reason_id ? zeroToEmpty(frmData.wastage_reason_id) : "").trigger('change.select2');


    thisForm.modal('show');
}
function removeItemIssueDetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let index = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        let item = issue_details_data[index];

        if (item.issue_detail_id && item.issue_detail_id != 0) {
            item.mode = "Delete";
        } else {
            issue_details_data.splice(index, 1);
        }
        removeFormObj();
    });
}

function removeFormObj() {
    jQuery('#ItemIssueDetailsTable tbody').empty();
    fillItemIssueDetailsTable();
}


// edit time fill table start
function fillItemIssueDetailsTable() {
    let thisModal = jQuery('#ItemIssueDetailsModal');
    jQuery('#ItemIssueDetailsTable tbody').empty();

    if (issue_details_data.length > 0) {
        for (let key in issue_details_data) {
            if (issue_details_data[key].mode == "Delete") {
                continue;
            }
            let formIndx = issue_details_data.indexOf(issue_details_data[key]);
            var item_id = issue_details_data[key].item_id ? issue_details_data[key].item_id : '';
            var item_name = (issue_details_data[key].item_name !== undefined && issue_details_data[key].item_name !== null)
                ? issue_details_data[key].item_name
                : thisModal.find('#item_id option[value="' + item_id + '"]').text();
            var issue_qty = issue_details_data[key].issue_qty != 0 ? parseFloat(issue_details_data[key].issue_qty).toFixed(3) : parseFloat(0).toFixed(3);
            var io_stock_qty = issue_details_data[key].io_stock_qty != 0 ? parseFloat(issue_details_data[key].io_stock_qty).toFixed(3) : parseFloat(0).toFixed(3);
            var remark = issue_details_data[key].remark != "" ? issue_details_data[key].remark : "";
            var item_group = issue_details_data[key].item_group != "" ? issue_details_data[key].item_group : "";
            var main_group = issue_details_data[key].main_group != "" ? issue_details_data[key].main_group : "";
            var sr_no = issue_details_data[key].sr_no != "" ? issue_details_data[key].sr_no : "";
            var issue_type = issue_details_data[key].issue_type != "" ? issue_details_data[key].issue_type : "";
            var wastage_reason = issue_details_data[key].wastage_reason_id != null && issue_details_data[key].wastage_reason_id != "" ? thisModal.find('#wastage_reason_id option[value="' + issue_details_data[key].wastage_reason_id + '"]').text() : '';
            var unit = issue_details_data[key].unit != "" ? issue_details_data[key].unit : '';


            if (jQuery('#ItemIssueDetailsTable tbody').find('#noDetails').length > 0) {
                jQuery('#ItemIssueDetailsTable tbody').empty();
            }
            let tblHtml = `<tr>`;
            tblHtml += `<td>`;
            tblHtml += DetailsActionDropdown('editItemIssueDetails', 'removeItemIssueDetails');
            tblHtml += ` <input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;

            tblHtml += `<td>${item_name}</td>`;
            tblHtml += `<td>${item_group}</td>`;
            tblHtml += `<td>${main_group}</td>`;
            tblHtml += `<td>${sr_no}</td>`;
            tblHtml += `<td>${io_stock_qty}</td>`;
            tblHtml += `<td>${issue_qty}</td>`;
            tblHtml += `<td>${unit}</td>`;
            tblHtml += `<td>${issue_type}</td>`;
            tblHtml += `<td>${wastage_reason}</td>`;
            tblHtml += `<td>${remark}</td>`;
            tblHtml += `</tr>`;
            jQuery('#ItemIssueDetailsTable tbody').append(tblHtml);
        }
    }
}




$('#commonItemIssueForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#ItemIssueModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;

    var dateValue = document.getElementById("issue_date").value.trim();

    // validatePercentage
    if (!isValidDate(dateValue)) {
        toastr.error("Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ItemIssueModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ItemIssueModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ItemIssueModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    jQuery('#ItemIssueModal').find('#submitbtn').prop('disabled', true);

    var formIdnew = jQuery('#ItemIssueModal').find('#commonItemIssueForm').find('#id').val();
    var formUrl = formIdnew != undefined && formIdnew != "" ? "update-item_issue" : "store-item_issue";
    var data = new FormData(form);
    data.append('issue_details_data', JSON.stringify(issue_details_data ?? []));
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));

    let validData = issue_details_data.filter(item => item.mode !== "Delete");

    if (validData.length > 0 && !jQuery.isEmptyObject(validData)) {
        let isValidDetails = true;
        let errorMsg = '';

        $.each(validData, function (index, row) {

            if ((row.sr_table_pk_id === undefined || row.sr_table_pk_id === null || row.sr_table_pk_id === '') && !['Industrial X-Ray Films', 'General'].includes(row.main_group)) {
                errorMsg = `Select SR No.`;
                isValidDetails = false;
                return false;
            }

            if (row.issue_qty === undefined || row.issue_qty === null || row.issue_qty === '' || parseFloat(row.issue_qty) <= 0) {
                errorMsg = `Enter Issue Qty.`;
                isValidDetails = false;
                return false;
            }

        });

        if (!isValidDetails) {
            toastr.error(errorMsg);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');

            jQuery('#ItemIssueModal')
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
                        jQuery('#ItemIssueModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formIdnew == undefined || formIdnew == "") {
                        function nextFn() {
                            document.getElementById("commonItemIssueForm").reset();
                            const form = document.getElementById("commonItemIssueForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            resetFieds();
                            getLatestItemIssueNo();

                        }

                        if (data.url != "") {
                            toastSuccessPreview(data.response_message, data.url, nextFn);
                        } else {
                            toastSuccess(data.response_message, nextFn);
                        }
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#ItemIssueModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#ItemIssueModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#ItemIssueModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#ItemIssueModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#ItemIssueModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        toastr.error('Add At Least One Item Issue Detail.');

        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ItemIssueModal').find('#submitbtn').prop('disabled', false);
    }
});


jQuery('#resetbtn').on('click', function () {
    issue_details_data = [];
    selectedRows = {};


    var formId = jQuery('#ItemIssueModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonItemIssueForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        jQuery('#prepared_by_user_id').val(loginUserId).trigger("change");
        getLatestItemIssueNo();
        resetFieds();
    } else {
        fetchAndFillItemIssue(formId);
    }
});




function getLatestItemIssueNo() {
    jQuery.ajax({
        url: "get-latest_item_issue_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {

                jQuery('#issue_number').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#issue_sequence').val(data.number);
                jQuery('#issue_date').val(currentDate);

            } else {
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            jQuery('#issue_number').removeClass('file-loader');
            console.log('Field To Get Latest Issue No.!')
        }
    });
}


// check sequence number duplication
jQuery('#commonItemIssueForm').find('#issue_sequence').on('change', function () {
    checkSequence();
});
function checkSequence() {

    let thisForm = jQuery('#commonItemIssueForm');
    let val = thisForm.find('#issue_sequence').val();

    if (val != "") {

        if (val > 0 == false) {
            toastr.error('Enter Valid Issue No.');
            jQuery('#issue_sequence').parent().parent().parent('div.control-group').addClass('error');
            jQuery('#issue_sequence').focus();
            jQuery('#issue_sequence').val('');

        } else {
            jQuery('#issue_sequence').addClass('file-loader');
            jQuery('#issue_sequence').parent().parent().parent('div.control-group').removeClass('error');

            var urL = "check-item_issue_number_duplication?for=add&issue_sequence=" + val;

            var formId = jQuery('#commonItemIssueForm').find('input[name="id"]').val();

            if (formId !== undefined) { //if form is edit
                urL = "check-item_issue_number_duplication?for=edit&issue_sequence=" + val + "&id=" + formId;
            }

            jQuery.ajax({
                url: urL,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    jQuery('#issue_sequence').removeClass('file-loader');
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        jQuery('#commonItemIssueForm #issue_sequence').val('');
                        const input = document.getElementById('issue_sequence'); input?.focus();
                    } else {
                        jQuery('#commonItemIssueForm #issue_number').val(data.latest_no);
                        jQuery('#commonItemIssueForm #issue_sequence').val(val);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#issue_sequence').removeClass('file-loader');
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
        jQuery('#issue_number').val('');
        jQuery('#issue_sequence').val('');
    }

}


function suggestDesignation(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#issue_to_list',
        url: 'item_issue_to-list',
        responseListKey: 'issueToList'
    });
}




function resetFieds() {
    jQuery('#issue_to_list').html('');
    jQuery('#issue_sequence').focus();
    $("#issue_to").val('');
    $("#special_note").val('');
    issue_details_data = [];
    selectedRows = {};
    jQuery('#ItemIssueDetailsTable tbody').empty();
    jQuery('#ItemIssueDetailsTable tbody').append(`
        <tr>
            <td colspan="11" class="text-center" id="noDetails">
                No Item Issue Details Added
            </td>
        </tr>
    `);

}