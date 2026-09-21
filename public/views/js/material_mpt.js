
// Edit material mpt row click
jQuery('#dyntable tbody').on('click', '.edit-material_mpt', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#MaterialMptModal').find('#id').val(data["mm_id"]);
    if (data && data["mm_id"]) {
        fetchAndFillMaterialMPT(data["mm_id"]);
    }
});

// Function to fetch and fill material mpt data
function fetchAndFillMaterialMPT(id) {
    if (!id) return;
    jQuery('#MaterialMptModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-material_mpt",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.material_mpt != null) {
                // if(data.items != ""){
                //     $('#mm_material_id').html('<option value="">Select MPT Material</option>');
                //     data.items.forEach(function(item) {
                //         $('#mm_material_id').append(
                //             `<option value="${item.id}">${item.item_name}</option>`
                //         );
                //     });
                // }


                jQuery('#MaterialMptModal').find('#id').val(data.material_mpt.mm_id != "" ? data.material_mpt.mm_id : "");
                jQuery('#MaterialMptModal').find('#table_unique_id').val(data.material_mpt.table_unique_id != "" ? data.material_mpt.table_unique_id : "");

                jQuery('#MaterialMptModal').find('#table_pk_id').val(data.material_mpt.table_pk_id != "" ? data.material_mpt.table_pk_id : "");
                jQuery('#MaterialMptModal').find('#item_id').val(data.material_mpt.item_id);
                jQuery('#MaterialMptModal').find('#item_group').val(data.material_mpt.item_group);
                jQuery('#MaterialMptModal').find('#main_group').val(data.material_mpt.main_group);

                jQuery('#MaterialMptModal').find("#grn_no").val(data.material_mpt.grn_number != null ? data.material_mpt.grn_number : '');
                jQuery('#MaterialMptModal').find("#grn_date").val(data.material_mpt.grn_date != null ? data.material_mpt.grn_date : '');
                jQuery('#MaterialMptModal').find("#supplier").val(data.material_mpt.supplier_name);
                jQuery('#MaterialMptModal').find("#challan_no").val(data.material_mpt.grn_challan_number != null ? data.material_mpt.grn_challan_number : '');
                jQuery('#MaterialMptModal').find("#challan_date").val(data.material_mpt.grn_challan_date != null ? data.material_mpt.grn_challan_date : '');

                jQuery('#MaterialMptModal').find('#mm_material').val(data.material_mpt.mm_material != "" ? data.material_mpt.mm_material : "");

                jQuery('#MaterialMptModal').find('#mm_material_make').val(data.material_mpt.mm_material_make != "" ? data.material_mpt.mm_material_make : "");

                jQuery('#MaterialMptModal').find('#mm_batch_no').val(data.material_mpt.mm_batch_no != "" ? data.material_mpt.mm_batch_no : "");

                jQuery('#MaterialMptModal').find('#mm_identification_no').val(data.material_mpt.mm_identification_no != "" ? data.material_mpt.mm_identification_no : "");

                jQuery('#MaterialMptModal').find('#mm_expiry_date').val(data.material_mpt.mm_expiry_date != "" ? data.material_mpt.mm_expiry_date : "");
                jQuery('#MaterialMptModal').find('#mm_qty').val(data.material_mpt.mm_qty != "" ? Math.round(data.material_mpt.mm_qty) : "");
                jQuery('#MaterialMptModal').find('#pending_qty').val(Math.round(data.material_mpt.pending_qty));

                if (data.material_mpt.in_use == true || parseFloat(data.material_mpt.used_qty) > 0) {
                    jQuery('#MaterialMptModal').find('#mm_qty').attr('min', parseFloat(data.material_mpt.used_qty).toFixed(3));
                } else {
                    jQuery('#MaterialMptModal').find('#mm_qty').removeAttr('min');
                }

                jQuery('#MaterialMptModal').find('#mm_status').val(data.material_mpt.mm_status || '').trigger('change.select2');

                // jQuery('#MaterialMptModal').find('#grn_number').val(data.material_mpt.grn_number != "" ? data.material_mpt.grn_number : "");
                // jQuery('#MaterialMptModal').find('#grn_date').val(data.material_mpt.grn_date != "" ? data.material_mpt.grn_date : "");
                // jQuery('#MaterialMptModal').find('#grn_challan_date').val(data.material_mpt.grn_challan_date != "" ? data.material_mpt.grn_challan_date : "");
                // jQuery('#MaterialMptModal').find('#grn_challan_number').val(data.material_mpt.grn_challan_number != "" ? data.material_mpt.grn_challan_number : "");
                // jQuery('#MaterialMptModal').find('#grn_supplier_name').val(data.material_mpt.grn_supplier_name != "" ? data.material_mpt.grn_supplier_name : "");

                jQuery('#MaterialMptModal').find('#pending_btn').prop('disabled', true);
                jQuery('#MaterialMptModal').find('#submitbtn').prop('disabled', false);
                jQuery('#MaterialMptModal').find('#add_new').show();

                const form = document.getElementById("commonMaterialMptForm");
                // if (Number(data.material_mpt.current_location_id) == Number(data.material_mpt.login_location)) {
                if (Number(data.material_mpt.own_location_id) == Number(data.material_mpt.login_location)) {
                    jQuery("#submitbtn").prop('disabled', false);

                } else {
                    jQuery("#submitbtn").prop('disabled', true);

                }
                if (form) form.classList.remove('was-validated');

                jQuery('#MaterialMptModal').find('#mm_material').focus();

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
    var formId = jQuery('#MaterialMptModal').find('#id').val();
    jQuery('#GrnPendingForMaterialMptModal').find('#submitbtn').prop('disabled', false);
    if (!formId) {
        var form = document.getElementById("commonMaterialMptForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        jQuery('#commonMaterialMptForm').find('#id').val('');
        jQuery('#commonMaterialMptForm').find('#table_pk_id').val('');
        jQuery('#commonMaterialMptForm').find('#item_id').val('');
        jQuery('#commonMaterialMptForm').find('#pending_qty').val(0);
        jQuery('#commonMaterialMptForm').find('#mm_qty').removeAttr('min');
        jQuery('#commonMaterialMptForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
        jQuery('#commonMaterialMptForm').find('#grn_no').prop({ tabindex: -1, readonly: true });
        jQuery('#commonMaterialMptForm').find('#grn_date').prop({ tabindex: -1, readonly: true });
        jQuery('#commonMaterialMptForm').find('#supplier').prop({ tabindex: -1, readonly: true });
        jQuery('#commonMaterialMptForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
        jQuery('#commonMaterialMptForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
        jQuery('#commonMaterialMptForm').find('#item_group').prop({ tabindex: -1, readonly: true });
        jQuery('#commonMaterialMptForm').find('#main_group').prop({ tabindex: -1, readonly: true });
        getPendingMaterialMpt();
        jQuery('#commonMaterialMptForm').find('.toggleModalBtn').prop('disabled', true);
        jQuery('#commonMaterialMptForm').find('#mm_status').val('Active').trigger('change.select2');
        focusPendingButton('#MaterialMptModal');
        // setTimeout(() => {
        //     const btn = jQuery('#MaterialMptModal').find('.toggleModalBtn').filter(':visible').not(':disabled').first();
        //     if (btn.length > 0) {
        //         btn.trigger('focus');
        //         btn.css({'border-color': '#22b378','outline-offset': '2px','box-shadow': '0 0 0 0.25rem rgba(34, 179, 120, 0.25)'});
        //         btn.one('blur', function () {
        //             jQuery(this).css({'border-color': '','outline-offset': '','box-shadow': ''});
        //         });
        //     }
        // }, 1500);
    } else {
        fetchAndFillMaterialMPT(formId);
    }
});


// Main form submit Stary
$('#commonMaterialMptForm').on('submit', function (e) {

    e.preventDefault();
    let form = this;

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#MaterialMptModal').find('#submitbtn').prop('disabled', false);
        return;
    }


    var table_pk_id = jQuery('#table_pk_id').val();
    if (table_pk_id == '' || table_pk_id == 0) {
        toastr.error('Select At Least One Pending.');
        // toastr.error('Select Atleast One Pending.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#MaterialMptModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#MaterialMptModal').find('#commonMaterialMptForm').find('#id').val();
    var table_unique_id = jQuery('#table_unique_id').val();
    var mm_qty = parseFloat(jQuery('#mm_qty').val()) || 0;
    var pending_qty = parseFloat(jQuery('#pending_qty').val()) || 0;

    if (mm_qty <= 0) {
        toastr.error('Enter Quantity greater than 0.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#MaterialMptModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var used_qty = parseFloat(jQuery('#mm_qty').attr('min')) || 0;
    if (used_qty > 0 && mm_qty < used_qty) {
        toastr.error('Qty. Cannot Be Less Than ' + used_qty + '.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#MaterialMptModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (mm_qty > pending_qty) {
        toastr.error('Qty. Cannot Be Greater Than Pending Qty ' + pending_qty + '.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#MaterialMptModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    var mm_material_make = jQuery('#mm_material_make').val();
    var mm_batch_no = jQuery('#mm_batch_no').val();
    var mm_material = jQuery('#mm_material').val();
    var grn_no = jQuery('#grn_no').val() || '';
    var table_unique_id = jQuery('#table_unique_id').val() || '';
    var MaterialUrl = formId != undefined && formId != "" ? "verify-material_mpt?mm_material_make=" + encodeURIComponent(mm_material_make) + "&mm_batch_no=" + encodeURIComponent(mm_batch_no) + "&mm_material=" + encodeURIComponent(mm_material) + "&grn_no=" + encodeURIComponent(grn_no) + "&table_unique_id=" + encodeURIComponent(table_unique_id) + "&table_pk_id=" + encodeURIComponent(table_pk_id) + "&mm_id=" + formId : "verify-material_mpt?mm_material_make=" + encodeURIComponent(mm_material_make) + "&mm_batch_no=" + encodeURIComponent(mm_batch_no) + "&mm_material=" + encodeURIComponent(mm_material) + "&grn_no=" + encodeURIComponent(grn_no) + "&table_unique_id=" + encodeURIComponent(table_unique_id) + "&table_pk_id=" + encodeURIComponent(table_pk_id);
    var formUrl = formId != undefined && formId != "" ? "update-material_mpt" : "store-material_mpt ";
    var data = new FormData(form);
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#MaterialMptModal').find('#submitbtn').prop('disabled', true);
    if ((mm_material_make != '' && mm_material_make != undefined) && (mm_batch_no != "" && mm_batch_no != undefined)) {
        $.ajax({
            url: MaterialUrl,
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.response_code == 1) {
                    toastr.error(res.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#MaterialMptModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#MaterialMptModal').find('#submitbtn').prop('disabled', false);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonMaterialMptForm").reset();
                                        const form = document.getElementById("commonMaterialMptForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#commonMaterialMptForm').find('.toggleModalBtn').prop('disabled', true);
                                        jQuery('#commonMaterialMptForm').find('#mm_status').val('Active').trigger('change.select2');
                                        jQuery('#commonMaterialMptForm').find('#table_pk_id').val('');
                                        jQuery('#commonChemicalDptForm').find('#id').val('');
                                        jQuery('#commonMaterialMptForm').find('#item_id').val('');
                                        jQuery('#commonMaterialMptForm').find('#pending_qty').val(0);
                                        getPendingMaterialMpt();

                                        jQuery('#commonMaterialMptForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonMaterialMptForm').find('#grn_no').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonMaterialMptForm').find('#grn_date').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonMaterialMptForm').find('#supplier').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonMaterialMptForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonMaterialMptForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonMaterialMptForm').find('#item_group').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonMaterialMptForm').find('#main_group').prop({ tabindex: -1, readonly: true });
                                        jQuery('#GrnPendingForMaterialMptModal').find('#submitbtn').prop('disabled', false);
                                        focusPendingButton('#MaterialMptModal');
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    // setTimeout(() => {
                                    //     const btn = jQuery('#MaterialMptModal').find('.toggleModalBtn').filter(':visible').not(':disabled').first();
                                    //     if (btn.length > 0) {
                                    //         btn.trigger('focus');
                                    //         btn.css({'border-color': '#22b378','outline-offset': '2px','box-shadow': '0 0 0 0.25rem rgba(34, 179, 120, 0.25)'});
                                    //         btn.one('blur', function () {
                                    //             jQuery(this).css({'border-color': '','outline-offset': '','box-shadow': ''});
                                    //         });
                                    //     }
                                    // }, 1500);

                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#MaterialMptModal').find('#submitbtn').prop('disabled', false);
                                }
                                else {
                                    toastr.error(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#MaterialMptModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#MaterialMptModal').find('#submitbtn').prop('disabled', false);
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
                                jQuery('#MaterialMptModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#MaterialMptModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});


$('#MaterialMptModal').on('show.bs.modal', function () {
    var formIdblank = jQuery('#commonMaterialMptForm').find('input[name="id"]').val();

    if (formIdblank == "" || formIdblank == undefined) {
        focusPendingButton('#MaterialMptModal');
        getPendingMaterialMpt();
        jQuery('#commonMaterialMptForm').find('#mm_status').val('Active').trigger('change.select2');
        jQuery('#commonMaterialMptForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
        jQuery('#commonMaterialMptForm').find('#grn_no').prop({ tabindex: -1, readonly: true });
        jQuery('#commonMaterialMptForm').find('#grn_date').prop({ tabindex: -1, readonly: true });
        jQuery('#commonMaterialMptForm').find('#supplier').prop({ tabindex: -1, readonly: true });
        jQuery('#commonMaterialMptForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
        jQuery('#commonMaterialMptForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
        jQuery('#commonMaterialMptForm').find('#item_group').prop({ tabindex: -1, readonly: true });
        jQuery('#commonMaterialMptForm').find('#main_group').prop({ tabindex: -1, readonly: true });
        jQuery('#submitbtn').prop('disabled', false);
    }

    if (formIdblank != "" && formIdblank != undefined) {
        jQuery('#commonMaterialMptForm').find('#pending_btn').prop('disabled', true);
    }

    if (formIdblank && formIdblank !== "") {
        jQuery('#MaterialMptModal').find('#add_new').show();
    } else {
        jQuery('#MaterialMptModal').find('#add_new').hide();
    }
});

jQuery('#MaterialMptModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#MaterialMptModal');
    thisForm.find('#id').val('');
    thisForm.find('#pending_qty').val(0);
    document.getElementById("commonMaterialMptForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).prop('checked', false);
    });
    jQuery('#MaterialMptModal').find('#add_new').hide();
});

jQuery('#MaterialMptModal').on('click', '#add_new', function () {
    jQuery('#MaterialMptModal').find('#id').val('');
    document.getElementById("commonMaterialMptForm").reset();
    const form = document.getElementById("commonMaterialMptForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    getPendingMaterialMpt();
    jQuery('#commonMaterialMptForm').find('#id').val('');
    jQuery('#commonMaterialMptForm').find('#table_pk_id').val('');
    jQuery('#commonMaterialMptForm').find('#item_id').val('');
    jQuery('#commonMaterialMptForm').find('#pending_qty').val(0);
    jQuery('#commonMaterialMptForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
    jQuery('#commonMaterialMptForm').find('#grn_no').prop({ tabindex: -1, readonly: true });
    jQuery('#commonMaterialMptForm').find('#grn_date').prop({ tabindex: -1, readonly: true });
    jQuery('#commonMaterialMptForm').find('#supplier').prop({ tabindex: -1, readonly: true });
    jQuery('#commonMaterialMptForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
    jQuery('#commonMaterialMptForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
    jQuery('#commonMaterialMptForm').find('#item_group').prop({ tabindex: -1, readonly: true });
    jQuery('#commonMaterialMptForm').find('#main_group').prop({ tabindex: -1, readonly: true });
    jQuery('#submitbtn').prop('disabled', false);
    jQuery("#commonMaterialMptForm #mm_status").val('Active').trigger("change.select2");
    focusPendingButton('#MaterialMptModal');
    // setTimeout(() => {
    //     const btn = jQuery('#MaterialMptModal').find('.toggleModalBtn').filter(':visible').not(':disabled').first();
    //     if (btn.length > 0) {
    //         btn.trigger('focus');
    //         btn.css({'border-color': '#22b378','outline-offset': '2px','box-shadow': '0 0 0 0.25rem rgba(34, 179, 120, 0.25)'});
    //         btn.one('blur', function () {
    //             jQuery(this).css({'border-color': '','outline-offset': '','box-shadow': ''});
    //         });
    //     }
    // }, 1500);

    jQuery('#MaterialMptModal').find('#add_new').hide();
});

// function suggestMake(e, $this) {
//     var keyevent = e
//     if (keyevent.key != "Tab") {
//         var search = jQuery($this).val();
//         jQuery.ajax({
//             url: "material_mpt_make_list?term=" + encodeURI(search),
//             type: 'GET',
//             dataType: 'json',
//             processData: false,
//             success: function (data) {
//                 jQuery("#mm_material_make").removeClass('file-loader');
//                 if (data.response_code == 1) {
//                     jQuery('#mm_material_make_list').html(data.makeList);
//                 } else {
//                      toastr.error(data.response_message);
//                 }
//             },
//             error: function (jqXHR, textStatus, errorThrown) {
//                 jQuery("#mm_material_make").removeClass('file-loader');
//                 var errMessage = JSON.parse(jqXHR.responseText);
//                 if (errMessage.errors) {
//                     countryValidator.showErrors(errMessage.errors);
//                 } else if (jqXHR.status == 401) {
//                      toastr.error(jqXHR.statusText);
//                 } else {
//                      toastr.error('Something went wrong!');
//                     console.log(JSON.parse(jqXHR.responseText));
//                 }
//             }
//         });
//     }
// }

jQuery(document).on('click', '#mm_material_make_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#mm_material_make_suggesion').val(suggest);
    var hidden = jQuery('#mm_material_make_suggesion').val();
    jQuery('#MaterialMptModal').find('#mm_material_make').val(hidden)
    jQuery('#mm_material_make_list').html('');
});

jQuery('#GrnPendingForMaterialMptModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = jQuery('#table_pk_id').val() != '' ? document.getElementById('mm_material') : document.getElementById('pending_btn');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }
});


// get pending probe ut from grn
function getPendingMaterialMpt() {
    var thisForm = jQuery('#addPendigGrnForm');

    return jQuery.ajax({
        url: "get-pending_grn_list_for_material_mpt",
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
                        var formIdblank = jQuery('#commonMaterialMptForm').find('#id').val();
                        if (formIdblank != "" && formIdblank != undefined) {
                            jQuery('#pending_btn').prop('disabled', true);
                        } else {
                            jQuery('#pending_btn').prop('disabled', false);
                        }
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
                                                <td colspan="13">No Pending Available</td>
                                            </tr>`;

                        jQuery('#pending_btn').prop('disabled', true);

                    }

                    var $table = jQuery("#GrnPendingForMaterialMptModal").find('#pendingGrnDataTable');
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

    jQuery('#GrnPendingForMaterialMptModal')
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
        // toastr.error('Select Atleast One Pending.');

        jQuery('#full-page-loader')
            .removeClass('loader-progress-whole-page')
            .addClass('hidden-loader');

        jQuery('#GrnPendingForMaterialMptModal')
            .find('#submitbtn')
            .prop('disabled', false);

        return;
    }

    jQuery.ajax({
        url: 'get-pending_grn_for_material_mpt',
        type: 'GET',
        data: {
            table_pk_ids: chkArr.join(','),
            table_unique_ids: uniqueArr.join(',')
        },
        dataType: 'json',
        success: function (data) {

            if (data.response_code == 1) {
                if (data.opening_data) {

                    jQuery('#MaterialMptModal').find("#table_pk_id").val(data.opening_data.table_pk_id);
                    jQuery('#MaterialMptModal').find('#item_group').val(data.opening_data.item_group);
                    jQuery('#MaterialMptModal').find("#main_group").val(data.opening_data.item_type);
                    jQuery('#MaterialMptModal').find("#table_unique_id").val(data.opening_data.table_unique_id);
                    jQuery('#MaterialMptModal').find("#item_id").val(data.opening_data.item_id);
                    jQuery('#MaterialMptModal').find("#mm_material").val(data.opening_data.item_name);
                    jQuery('#MaterialMptModal').find("#grn_no").val(data.opening_data.grn_number != null ? data.opening_data.grn_number : '');
                    jQuery('#MaterialMptModal').find("#grn_date").val(data.opening_data.grn_date != null ? data.opening_data.grn_date : '');
                    jQuery('#MaterialMptModal').find("#supplier").val(data.opening_data.supplier_name);
                    jQuery('#MaterialMptModal').find("#challan_no").val(data.opening_data.grn_challan_number != null ? data.opening_data.grn_challan_number : '');
                    jQuery('#MaterialMptModal').find("#challan_date").val(data.opening_data.grn_challan_date != null ? data.opening_data.grn_challan_date : '');
                    jQuery('#MaterialMptModal').find("#mm_qty").val(data.opening_data.pending_qty != null ? Math.round(data.opening_data.pending_qty) : '');
                    jQuery('#MaterialMptModal').find("#pending_qty").val(data.opening_data.pending_qty != null ? Math.round(data.opening_data.pending_qty) : 0);
                    jQuery('.toggleModalBtn').prop('disabled', true);
                } else {
                    jQuery('#MaterialMptModal').find("#table_pk_id").val('');
                    jQuery('#MaterialMptModal').find('#item_group').val('');
                    jQuery('#MaterialMptModal').find("#main_group").val('');
                    jQuery('#MaterialMptModal').find("#table_unique_id").val('');
                    jQuery('#MaterialMptModal').find("#mm_material").val('');
                    jQuery('#EquipmentMPTModal').find("#item_id").val('');
                    jQuery('#EquipmentMPTModal').find("#grn_no").val('');
                    jQuery('#EquipmentMPTModal').find("#grn_date").val('');
                    jQuery('#EquipmentMPTModal').find("#supplier").val('');
                    jQuery('#EquipmentMPTModal').find("#challan_no").val('');
                    jQuery('#EquipmentMPTModal').find("#challan_date").val('');
                    jQuery('#EquipmentMPTModal').find("#mm_qty").val('');
                    jQuery('.toggleModalBtn').prop('disabled', false);
                }

                jQuery("#GrnPendingForMaterialMptModal").modal('hide');
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

            jQuery('#GrnPendingForMaterialMptModal')
                .find('#submitbtn')
                .prop('disabled', false);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');
        }
    });
});

// for check radio
jQuery('#GrnPendingForMaterialMptModal').on('show.bs.modal', function (e) {
    var dt = jQuery('#pendingGrnDataTable').DataTable();
    fixDataTableColumnsUntilAdjusted(dt);

    var usedParts = [];

    var table_pk_id = jQuery('#MaterialMptModal').find('#table_pk_id').val();
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

jQuery('#mm_material_make, #mm_batch_no ,#mm_material').on('change', function () {
    CheckMPTMaterial();
});

function CheckMPTMaterial() {

    var mm_material_make = jQuery('#mm_material_make').val();
    var mm_batch_no = jQuery('#mm_batch_no').val();
    var mm_material = jQuery('#mm_material').val();
    var table_pk_id = jQuery('#table_pk_id').val() || '';
    var table_unique_id = jQuery('#table_unique_id').val() || '';
    var grn_no = jQuery('#grn_no').val() || '';
    var formId = jQuery('#MaterialMptModal').find('#commonMaterialMptForm').find('#id').val();
    var formUrl = formId != undefined && formId != "" ? "verify-material_mpt?mm_material_make=" + encodeURIComponent(mm_material_make) + "&mm_batch_no=" + encodeURIComponent(mm_batch_no) + "&mm_material=" + encodeURIComponent(mm_material) + "&grn_no=" + encodeURIComponent(grn_no) + "&table_unique_id=" + encodeURIComponent(table_unique_id) + "&table_pk_id=" + encodeURIComponent(table_pk_id) + "&mm_id=" + formId : "verify-material_mpt?mm_material_make=" + encodeURIComponent(mm_material_make) + "&mm_batch_no=" + encodeURIComponent(mm_batch_no) + "&mm_material=" + encodeURIComponent(mm_material) + "&grn_no=" + encodeURIComponent(grn_no) + "&table_unique_id=" + encodeURIComponent(table_unique_id) + "&table_pk_id=" + encodeURIComponent(table_pk_id);

    jQuery.ajax({
        url: formUrl,
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