
// Edit material mpt row click
jQuery('#dyntable tbody').on('click', '.edit-chemical_dpt', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#ChemicalDptModal').find('#id').val(data["dpt_id"]);
    if (data && data["dpt_id"]) {
        fetchAndFillChemicalDPT(data["dpt_id"]);
    }
});

// Function to fetch and fill material mpt data
function fetchAndFillChemicalDPT(id) {
    if (!id) return;
    jQuery('#ChemicalDptModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-chemical_dpt",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.chemical_dpt != null) {
                // if(data.items != ""){
                //     $('#mm_material_id').html('<option value="">Select MPT Material</option>');
                //     data.items.forEach(function(item) {
                //         $('#mm_material_id').append(
                //             `<option value="${item.id}">${item.item_name}</option>`
                //         );
                //     });
                // }


                jQuery('#ChemicalDptModal').find('#id').val(data.chemical_dpt.dpt_id != "" ? data.chemical_dpt.dpt_id : "");
                jQuery('#ChemicalDptModal').find('#table_unique_id').val(data.chemical_dpt.table_unique_id != "" ? data.chemical_dpt.table_unique_id : "");

                jQuery('#ChemicalDptModal').find('#table_pk_id').val(data.chemical_dpt.table_pk_id != "" ? data.chemical_dpt.table_pk_id : "");
                jQuery('#ChemicalDptModal').find('#item_id').val(data.chemical_dpt.item_id);
                jQuery('#ChemicalDptModal').find('#item_group').val(data.chemical_dpt.item_group);
                jQuery('#ChemicalDptModal').find('#main_group').val(data.chemical_dpt.main_group);

                jQuery('#ChemicalDptModal').find("#grn_no").val(data.chemical_dpt.grn_number != null ? data.chemical_dpt.grn_number : '');
                jQuery('#ChemicalDptModal').find("#grn_date").val(data.chemical_dpt.grn_date != null ? data.chemical_dpt.grn_date : '');
                jQuery('#ChemicalDptModal').find("#supplier").val(data.chemical_dpt.supplier_name);
                jQuery('#ChemicalDptModal').find("#challan_no").val(data.chemical_dpt.grn_challan_number != null ? data.chemical_dpt.grn_challan_number : '');
                jQuery('#ChemicalDptModal').find("#challan_date").val(data.chemical_dpt.grn_challan_date != null ? data.chemical_dpt.grn_challan_date : '');

                jQuery('#ChemicalDptModal').find('#dpt_chemical').val(data.chemical_dpt.dpt_chemical != "" ? data.chemical_dpt.dpt_chemical : "");
                jQuery('#ChemicalDptModal').find('#dpt_designation').val(data.chemical_dpt.dpt_designation != "" ? data.chemical_dpt.dpt_designation : "");

                jQuery('#ChemicalDptModal').find('#dpt_make').val(data.chemical_dpt.dpt_make != "" ? data.chemical_dpt.dpt_make : "");

                jQuery('#ChemicalDptModal').find('#dpt_batch_no').val(data.chemical_dpt.dpt_batch_no != "" ? data.chemical_dpt.dpt_batch_no : "");

                jQuery('#ChemicalDptModal').find('#dpt_identification_no').val(data.chemical_dpt.dpt_identification_no != "" ? data.chemical_dpt.dpt_identification_no : "");

                jQuery('#ChemicalDptModal').find('#dpt_expiry_date').val(data.chemical_dpt.dpt_expiry_date != "" ? data.chemical_dpt.dpt_expiry_date : "");
                jQuery('#ChemicalDptModal').find('#dpt_qty').val(data.chemical_dpt.dpt_qty != "" ? Math.round(data.chemical_dpt.dpt_qty) : "");
                var db_pending = parseFloat(data.chemical_dpt.pending_qty) || 0;
                var db_qty = parseFloat(data.chemical_dpt.dpt_qty) || 0;
                jQuery('#ChemicalDptModal').find('#pending_qty').val(Math.round(db_pending + db_qty));

                if (data.chemical_dpt.in_use == true || parseFloat(data.chemical_dpt.used_qty) > 0) {
                    jQuery('#ChemicalDptModal').find('#dpt_qty').attr('min', parseFloat(data.chemical_dpt.used_qty).toFixed(3));
                } else {
                    jQuery('#ChemicalDptModal').find('#dpt_qty').removeAttr('min');
                }

                jQuery('#ChemicalDptModal').find('#dpt_status').val(data.chemical_dpt.dpt_status || '').trigger('change.select2');

                jQuery('#ChemicalDptModal').find('#pending_btn').prop('disabled', true);
                jQuery('#ChemicalDptModal').find('#submitbtn').prop('disabled', false);

                const form = document.getElementById("commonChemicalDptForm");
                // if (Number(data.chemical_dpt.current_location_id) == Number(data.chemical_dpt.login_location)) {
                if (Number(data.chemical_dpt.own_location_id) == Number(data.chemical_dpt.login_location)) {
                    jQuery("#submitbtn").prop('disabled', false);

                } else {
                    jQuery("#submitbtn").prop('disabled', true);

                }
                jQuery('#ChemicalDptModal').find('#add_new').show();

                if (form) form.classList.remove('was-validated');

                jQuery('#ChemicalDptModal').find('#dpt_chemical').focus();
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

// Reset button click for material mpt modal
jQuery('#resetbtn').on('click', function () {
    var formId = jQuery('#ChemicalDptModal').find('#id').val();
    jQuery('#GrnPendingForChemicalDPTModal').find('#submitbtn').prop('disabled', false);
    if (!formId) {
        var form = document.getElementById("commonChemicalDptForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        jQuery('#commonChemicalDptForm').find('#id').val('');
        jQuery('#commonChemicalDptForm').find('#table_pk_id').val('');
        jQuery('#commonChemicalDptForm').find('#item_id').val('');
        jQuery('#commonChemicalDptForm').find('#pending_qty').val(0);
        jQuery('#commonChemicalDptForm').find('#dpt_qty').removeAttr('min');
        // jQuery('#ChemicalDptModal').find('#dpt_chemical').focus();
        jQuery('#commonChemicalDptForm').find('.toggleModalBtn').prop('disabled', true);
        jQuery('#commonChemicalDptForm').find('#dpt_status').val('Active').trigger('change.select2');
        getPendingChemicalDPT();
        jQuery('#commonChemicalDptForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
        jQuery('#commonChemicalDptForm').find('#grn_no').prop({ tabindex: -1, readonly: true });
        jQuery('#commonChemicalDptForm').find('#grn_date').prop({ tabindex: -1, readonly: true });
        jQuery('#commonChemicalDptForm').find('#supplier').prop({ tabindex: -1, readonly: true });
        jQuery('#commonChemicalDptForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
        jQuery('#commonChemicalDptForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
        jQuery('#commonChemicalDptForm').find('#item_group').prop({ tabindex: -1, readonly: true });
        jQuery('#commonChemicalDptForm').find('#main_group').prop({ tabindex: -1, readonly: true });
        focusPendingButton('#ChemicalDptModal');

    } else {
        fetchAndFillChemicalDPT(formId);
    }
});


// Main form submit Stary
$('#commonChemicalDptForm').on('submit', function (e) {

    e.preventDefault();
    let form = this;

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ChemicalDptModal').find('#submitbtn').prop('disabled', false);
        return;
    }


    var table_pk_id = jQuery('#table_pk_id').val();
    if (table_pk_id == '' || table_pk_id == 0) {
        toastr.error('Select At Least One Pending.');
        // toastr.error('Select Atleast One Pending.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ChemicalDptModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#ChemicalDptModal').find('#commonChemicalDptForm').find('#id').val();
    var table_unique_id = jQuery('#table_unique_id').val();
    var dpt_qty = parseFloat(jQuery('#dpt_qty').val()) || 0;
    var pending_qty = parseFloat(jQuery('#pending_qty').val()) || 0;

    if (dpt_qty <= 0) {
        toastr.error('Enter Qty. greater than 0.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ChemicalDptModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var used_qty = parseFloat(jQuery('#dpt_qty').attr('min')) || 0;
    if (used_qty > 0 && dpt_qty < used_qty) {
        toastr.error('Qty. Cannot Be Less Than ' + used_qty + '.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ChemicalDptModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (dpt_qty > pending_qty) {
        toastr.error('Qty. Cannot Be Greater Than Pending Qty. ' + pending_qty + '.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ChemicalDptModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    var dpt_designation = jQuery('#dpt_designation').val();
    var dpt_batch_no = jQuery('#dpt_batch_no').val();
    var dpt_chemical = jQuery('#dpt_chemical').val();
    var table_pk_id = jQuery('#table_pk_id').val() || '';
    var table_unique_id = jQuery('#table_unique_id').val() || '';
    var MaterialUrl = formId != undefined && formId != "" ? "verify-chemical_dpt?dpt_designation=" + encodeURIComponent(dpt_designation) + "&dpt_batch_no=" + encodeURIComponent(dpt_batch_no) + "&dpt_chemical=" + encodeURIComponent(dpt_chemical) + "&table_pk_id=" + encodeURIComponent(table_pk_id) + "&table_unique_id=" + encodeURIComponent(table_unique_id) + + "&dpt_id=" + formId : "verify-chemical_dpt?dpt_designation=" + encodeURIComponent(dpt_designation) + "&dpt_batch_no=" + encodeURIComponent(dpt_batch_no) + "&dpt_chemical=" + encodeURIComponent(dpt_chemical) + "&table_pk_id=" + encodeURIComponent(table_pk_id) + "&table_unique_id=" + encodeURIComponent(table_unique_id);
    var formUrl = formId != undefined && formId != "" ? "update-chemical_dpt" : "store-chemical_dpt";
    var data = new FormData(form);
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#ChemicalDptModal').find('#submitbtn').prop('disabled', true);
    if ((dpt_make != '' && dpt_make != undefined) && (dpt_batch_no != "" && dpt_batch_no != undefined)) {
        $.ajax({
            url: MaterialUrl,
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.response_code == 1) {
                    toastr.error(res.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#ChemicalDptModal').find('#submitbtn').prop('disabled', false);
                } else {
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
                                    jQuery('#ChemicalDptModal').find('#submitbtn').prop('disabled', false);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonChemicalDptForm").reset();
                                        const form = document.getElementById("commonChemicalDptForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }
                                        // jQuery('#commonChemicalDptForm').find('#dpt_chemical').focus();
                                        jQuery('#commonChemicalDptForm').find('.toggleModalBtn').prop('disabled', true);
                                        jQuery('#commonChemicalDptForm').find('#dpt_status').val('Active').trigger('change.select2');
                                        jQuery('#commonChemicalDptForm').find('#table_pk_id').val('');
                                        jQuery('#commonChemicalDptForm').find('#id').val('');
                                        jQuery('#commonChemicalDptForm').find('#item_id').val('');
                                        jQuery('#commonChemicalDptForm').find('#pending_qty').val(0);
                                        getPendingChemicalDPT();
                                        jQuery('#commonChemicalDptForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonChemicalDptForm').find('#grn_no').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonChemicalDptForm').find('#grn_date').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonChemicalDptForm').find('#supplier').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonChemicalDptForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonChemicalDptForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonChemicalDptForm').find('#item_group').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonChemicalDptForm').find('#main_group').prop({ tabindex: -1, readonly: true });
                                        focusPendingButton('#ChemicalDptModal');
                                        jQuery('#GrnPendingForChemicalDPTModal').find('#submitbtn').prop('disabled', false);
                                    }
                                    toastSuccess(data.response_message, nextFn);


                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#ChemicalDptModal').find('#submitbtn').prop('disabled', false);
                                }
                                else {
                                    toastr.error(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#ChemicalDptModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#ChemicalDptModal').find('#submitbtn').prop('disabled', false);
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
                                jQuery('#ChemicalDptModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#ChemicalDptModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});


$('#ChemicalDptModal').on('show.bs.modal', function () {
    var formIdblank = jQuery('#commonChemicalDptForm').find('input[name="id"]').val();

    if (formIdblank == "" || formIdblank == undefined) {
        getPendingChemicalDPT();
        jQuery('#commonChemicalDptForm').find('#dpt_status').val('Active').trigger('change.select2');
        jQuery('#submitbtn').prop('disabled', false);
        focusPendingButton('#ChemicalDptModal');
    }

    if (formIdblank != "" && formIdblank != undefined) {
        jQuery('#commonChemicalDptForm').find('#pending_btn').prop('disabled', true);
    }

    if (formIdblank && formIdblank !== "") {
        jQuery('#ChemicalDptModal').find('#add_new').show();
    } else {
        jQuery('#ChemicalDptModal').find('#add_new').hide();
    }
});

jQuery('#ChemicalDptModal').on('shown.bs.modal', function () {
    var $datePicker = jQuery('#ChemicalDptModal').find('#dpt_expiry_date');
    if ($datePicker.length) {
        $datePicker.datepicker('option', 'beforeShow', function (input, inst) {
            setTimeout(function () {
                var $input = jQuery(input);
                var offset = $input.offset();
                if (offset) {
                    inst.dpDiv.css({
                        top: (offset.top + $input.outerHeight()) + 'px',
                        left: offset.left + 'px',
                        zIndex: 1060
                    });
                }
            }, 0);
        });
    }
});

jQuery('#ChemicalDptModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#ChemicalDptModal');
    thisForm.find('#id').val('');
    thisForm.find('#pending_qty').val(0);
    document.getElementById("commonChemicalDptForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).prop('checked', false);
    });
    jQuery('#ChemicalDptModal').find('#add_new').hide();
});

jQuery('#ChemicalDptModal').on('click', '#add_new', function () {
    jQuery('#ChemicalDptModal').find('#id').val('');
    document.getElementById("commonChemicalDptForm").reset();
    const form = document.getElementById("commonChemicalDptForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    // jQuery('#ChemicalDptModal').find('#dpt_chemical').focus();
    jQuery('#commonChemicalDptForm').find('.toggleModalBtn').prop('disabled', true);
    jQuery('#commonChemicalDptForm').find('#dpt_status').val('Active').trigger('change.select2');
    getPendingChemicalDPT();
    jQuery('#commonChemicalDptForm').find('#id').val('');
    jQuery('#commonChemicalDptForm').find('#table_pk_id').val('');
    jQuery('#commonChemicalDptForm').find('#item_id').val('');
    jQuery('#commonChemicalDptForm').find('#pending_qty').val(0);
    jQuery('#commonChemicalDptForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
    jQuery('#commonChemicalDptForm').find('#grn_no').prop({ tabindex: -1, readonly: true });
    jQuery('#commonChemicalDptForm').find('#grn_date').prop({ tabindex: -1, readonly: true });
    jQuery('#commonChemicalDptForm').find('#supplier').prop({ tabindex: -1, readonly: true });
    jQuery('#commonChemicalDptForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
    jQuery('#commonChemicalDptForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
    jQuery('#commonChemicalDptForm').find('#item_group').prop({ tabindex: -1, readonly: true });
    jQuery('#commonChemicalDptForm').find('#main_group').prop({ tabindex: -1, readonly: true });
    jQuery('#ChemicalDptModal').find('#add_new').hide();
    focusPendingButton('#ChemicalDptModal');
    jQuery('#submitbtn').prop('disabled', false);
    jQuery("#commonChemicalDptForm #dpt_status").val('Active').trigger("change.select2");

});


jQuery('#GrnPendingForChemicalDPTModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = jQuery('#table_pk_id').val() != '' ? document.getElementById('dpt_chemical') : document.getElementById('pending_btn');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }
});


// get pending probe ut from grn
function getPendingChemicalDPT() {
    var thisForm = jQuery('#addPendigGrnForm');

    return jQuery.ajax({
        url: "get-pending_grn_list_for_chemical_dpt",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                if (data.response_code == 1 && data.opening_data.length > 0) {
                    // new code
                    var usedParts = [];
                    var totalDisb = 0;
                    var found = 0;

                    thisForm.find('#pendingGrnDataTable tbody input[name="form_indx"]').each(function (indx) {
                        let frmIndx = jQuery(this).val();

                        let jbEorkOrderId = opening_data[frmIndx].table_pk_id;
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
                    if (data.opening_data.length > 0 && !jQuery.isEmptyObject(data.opening_data)) {
                        var formIdblank = jQuery('#commonChemicalDptForm').find('#id').val();
                        if (formIdblank != "" && formIdblank != undefined) {
                            jQuery('#pending_btn').prop('disabled', true);
                        } else {
                            jQuery('#pending_btn').prop('disabled', false);
                        }
                        // jQuery('#pending_btn').prop('disabled', false);
                        found = 1;

                        for (let idx in data.opening_data) {
                            var inUse = isUsed(data.opening_data[idx].table_pk_id);
                            var in_use = data.opening_data[idx].in_use == true ? 'readonly' : '';
                            totalEntry++;
                            tblHtml += `
                                        <tr>
                                            <td>
                                                <input type="radio" name="table_pk_id[]" class="simple-check radio-filter-remove ${inUse ? 'in-use' : ''}" id="table_pk_ids_${data.opening_data[idx].table_pk_id}" value="${data.opening_data[idx].table_pk_id}" ${inUse ? 'checked' : ''} ${in_use} data-table_unique_id="${data.opening_data[idx].table_unique_id}"/>
                                            </td>
                                            <td>${data.opening_data[idx].table_unique_id}</td>
                                            <td>${data.opening_data[idx].grn_number != null ? data.opening_data[idx].grn_number : ''}</td>
                                            <td>${data.opening_data[idx].grn_date != null ? data.opening_data[idx].grn_date : ''}</td>
                                            <td>${data.opening_data[idx].supplier_name != null ? data.opening_data[idx].supplier_name : ''}</td>
                                            <td>${data.opening_data[idx].grn_challan_number != null ? data.opening_data[idx].grn_challan_number : ''}</td>
                                            <td>${data.opening_data[idx].grn_challan_date != null ? data.opening_data[idx].grn_challan_date : ''}</td>
                                            <td>${data.opening_data[idx].item_name != null ? data.opening_data[idx].item_name : ''}</td>
                                            <td>${data.opening_data[idx].item_group != null ? data.opening_data[idx].item_group : ''}</td>
                                            <td>${data.opening_data[idx].item_type != null ? data.opening_data[idx].item_type : ''}</td>
                                            <td>${data.opening_data[idx].qty != null ? parseFloat(data.opening_data[idx].qty).toFixed(3) : ''}</td>
                                            <td>${data.opening_data[idx].pending_qty != null ? parseFloat(data.opening_data[idx].pending_qty).toFixed(3) : ''}</td>
                                            <td>${data.opening_data[idx].unit}</td>
                                        </tr>`;

                        }

                    } else {

                        tblHtml += `<tr class="centeralign" id="noPendingPo">
                                                <td colspan="9">No Pending GRN Available</td>
                                            </tr>`;

                        jQuery('#pending_btn').prop('disabled', true);

                    }

                    var $table = jQuery("#GrnPendingForChemicalDPTModal").find('#pendingGrnDataTable');
                    if (jQuery.fn.DataTable.isDataTable($table)) {
                        $table.DataTable().clear().destroy();
                    }
                    jQuery('#pendingGrnDataTable tbody').empty().append(tblHtml);

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


                } else {
                    jQuery('.toggleModalBtn').prop('disabled', true);
                    //toastr.error(data.response_message);
                }

            } else {
                jQuery('.toggleModalBtn').prop('disabled', true);
                // toastr.error(data.response_message);
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

// get selected pending GRN 
$('#addPendigGrnForm').on('submit', function (e) {

    e.preventDefault();

    jQuery('#full-page-loader')
        .removeClass('hidden-loader')
        .addClass('loader-progress-whole-page');

    jQuery('#GrnPendingForChemicalDPTModal')
        .find('#submitbtn')
        .prop('disabled', true);

    let chkArr = [];
    let uniqueArr = [];

    jQuery("#addPendigGrnForm")
        .find("[id^='table_pk_ids_']:checked")
        .each(function () {
            chkArr.push(jQuery(this).val());
            uniqueArr.push(jQuery(this).data('table_unique_id'));
        });

    // No checkbox selected
    if (chkArr.length === 0) {
        toastr.error('Select At Least One Pending.');

        jQuery('#full-page-loader')
            .removeClass('loader-progress-whole-page')
            .addClass('hidden-loader');

        jQuery('#GrnPendingForChemicalDPTModal')
            .find('#submitbtn')
            .prop('disabled', false);

        return;
    }

    jQuery.ajax({
        url: 'get-pending_grn_for_chemical_dpt',
        type: 'GET',
        data: {
            table_pk_ids: chkArr.join(','),
            table_unique_ids: uniqueArr.join(',')
        },
        dataType: 'json',
        success: function (data) {

            if (data.response_code == 1) {
                if (data.opening_data) {

                    jQuery('#ChemicalDptModal').find("#table_pk_id").val(data.opening_data.table_pk_id);
                    jQuery('#ChemicalDptModal').find('#item_group').val(data.opening_data.item_group);
                    jQuery('#ChemicalDptModal').find("#main_group").val(data.opening_data.item_type);
                    jQuery('#ChemicalDptModal').find("#table_unique_id").val(data.opening_data.table_unique_id);
                    jQuery('#ChemicalDptModal').find("#item_id").val(data.opening_data.item_id);
                    jQuery('#ChemicalDptModal').find("#dpt_chemical").val(data.opening_data.item_name);
                    jQuery('#ChemicalDptModal').find("#grn_no").val(data.opening_data.grn_number != null ? data.opening_data.grn_number : '');
                    jQuery('#ChemicalDptModal').find("#grn_date").val(data.opening_data.grn_date != null ? data.opening_data.grn_date : '');
                    jQuery('#ChemicalDptModal').find("#supplier").val(data.opening_data.supplier_name);
                    jQuery('#ChemicalDptModal').find("#challan_no").val(data.opening_data.grn_challan_number != null ? data.opening_data.grn_challan_number : '');
                    jQuery('#ChemicalDptModal').find("#challan_date").val(data.opening_data.grn_challan_date != null ? data.opening_data.grn_challan_date : '');
                    jQuery('#ChemicalDptModal').find("#dpt_qty").val(data.opening_data.pending_qty != null ? Math.round(data.opening_data.pending_qty) : '');
                    jQuery('#ChemicalDptModal').find("#pending_qty").val(data.opening_data.pending_qty != null ? Math.round(data.opening_data.pending_qty) : 0);
                    jQuery('.toggleModalBtn').prop('disabled', true);
                } else {
                    jQuery('#ChemicalDptModal').find("#table_pk_id").val('');
                    jQuery('#ChemicalDptModal').find('#item_group').val('');
                    jQuery('#ChemicalDptModal').find("#main_group").val('');
                    jQuery('#ChemicalDptModal').find("#table_unique_id").val('');
                    jQuery('#ChemicalDptModal').find("#item_id").val('');
                    jQuery('#ChemicalDptModal').find("#dpt_chemical").val('');
                    jQuery('#ChemicalDptModal').find("#grn_no").val('');
                    jQuery('#ChemicalDptModal').find("#grn_date").val('');
                    jQuery('#ChemicalDptModal').find("#supplier").val('');
                    jQuery('#ChemicalDptModal').find("#challan_no").val('');
                    jQuery('#ChemicalDptModal').find("#challan_date").val('');
                    jQuery('.toggleModalBtn').prop('disabled', false);
                }

                jQuery("#GrnPendingForChemicalDPTModal").modal('hide');
            } else {

            }

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

            jQuery('#GrnPendingForChemicalDPTModal')
                .find('#submitbtn')
                .prop('disabled', false);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');
        }
    });
});

// for check radio
jQuery('#GrnPendingForChemicalDPTModal').on('show.bs.modal', function (e) {
    var dt = jQuery('#pendingGrnDataTable').DataTable();
    fixDataTableColumnsUntilAdjusted(dt);

    var usedParts = [];

    var table_pk_id = jQuery('#ChemicalDptModal').find('#table_pk_id').val();
    if (table_pk_id != "" && table_pk_id != null) {
        usedParts.push(Number(table_pk_id));
    }

    function isUsed(pjId) {
        if (usedParts.includes(Number(pjId))) {
            return true;
        }
        return false;
    }

    jQuery('#pendingGrnDataTable tbody tr').each(function (indx) {
        var checkField = jQuery(this).find('input[name="table_pk_id[]"]');
        var partId = jQuery(checkField).val();
        var inUse = isUsed(partId);

        if (inUse) {
            jQuery(checkField).prop('checked', true);
        } else {
            jQuery(checkField).prop('checked', false);
        }

    });
});

jQuery('#dpt_make, #dpt_batch_no ,#dpt_chemical').on('change', function () {
    CheckMPTMaterial();
});

function CheckMPTMaterial() {

    var dpt_designation = jQuery('#dpt_designation').val();
    var dpt_batch_no = jQuery('#dpt_batch_no').val();
    var dpt_chemical = jQuery('#dpt_chemical').val();
    var table_pk_id = jQuery('#table_pk_id').val() || '';
    var table_unique_id = jQuery('#table_unique_id').val() || '';
    var formId = jQuery('#ChemicalDptModal').find('#commonChemicalDptForm').find('#id').val();

    var MaterialUrl = formId != undefined && formId != "" ? "verify-chemical_dpt?dpt_designation=" + encodeURIComponent(dpt_designation) + "&dpt_batch_no=" + encodeURIComponent(dpt_batch_no) + "&dpt_chemical=" + encodeURIComponent(dpt_chemical) + "&table_pk_id=" + encodeURIComponent(table_pk_id) + "&table_unique_id=" + encodeURIComponent(table_unique_id) + + "&dpt_id=" + formId : "verify-chemical_dpt?dpt_designation=" + encodeURIComponent(dpt_designation) + "&dpt_batch_no=" + encodeURIComponent(dpt_batch_no) + "&dpt_chemical=" + encodeURIComponent(dpt_chemical) + "&table_pk_id=" + encodeURIComponent(table_pk_id) + "&table_unique_id=" + encodeURIComponent(table_unique_id);

    jQuery.ajax({
        url: MaterialUrl,
        type: 'GET',
        dataType: 'json',
        processData: false,
        headers: headerOpt,
        success: function (data) {
            if (data.response_code == 1) {
                toastr.error(data.response_message);
            }
        }
    });
}
