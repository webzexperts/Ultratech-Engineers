var gst_data = [];

// Edit gst configuration row click
jQuery('#dyntable tbody').on('click', '.edit_gst_configuration', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["gc_id"]) {
        jQuery('#GSTConfigurationModal').find('#id').val(data["gc_id"]);
        fetchAndFillGSTConfiguration(data["gc_id"]);
    }
});

// Function to fetch and fill gst configuration data
function fetchAndFillGSTConfiguration(id) {
    if (!id) return;
    gst_data = [];
    jQuery('#GSTCommodityTable tbody').empty();
    jQuery('#GSTConfigurationModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-gst_configuration",
        type: 'GET',
        data: { id: id }, 
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.gst_data != null) {
                jQuery('#GSTConfigurationModal').find('#sac').val(data.gst_data.gc_sac);
                jQuery('#GSTConfigurationModal').find('#description').val(data.gst_data.gc_description);
                jQuery('#GSTConfigurationModal').find('#gc_remark').val(data.gst_data.gc_remarks);
                jQuery('#GSTConfigurationModal').find('#id').val(data.gst_data.gc_id);
                jQuery('#GSTConfigurationModal').find('#add_new').show();
                if (data.gst_details.length > 0 && !jQuery.isEmptyObject(data.gst_details)) {
                    for (let ind in data.gst_details) {
                        gst_data.push(data.gst_details[ind]);
                    }
                    fillGSTCommodityTable();
                }

                const form = document.getElementById("commonGSTConfigurationForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#GSTConfigurationModal').find('#sac').focus();
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

// Reset button click for gst configuration modal
jQuery('#GSTConfigurationModal').on('click', '#resetbtn', function () {
    gst_data = [];
    jQuery('#GSTCommodityTable tbody').empty();

    var formId = jQuery('#GSTConfigurationModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonGSTConfigurationForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
        jQuery('#sac').focus();
        jQuery('#GSTCommodityTable tbody').empty().append(`<tr class="centeralign" id="noContact"><td colspan="6">No GST Commodity Details Added</td></tr>`);
    } else {
        fetchAndFillGSTConfiguration(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_gst_configuration', function () {
//     jQuery('#GSTConfigurationModal').modal('show');
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-gst_configuration",
//         type: 'GET',
//         data: "id=" + data["gc_id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 if (data.gst_data != null) {
//                     jQuery('#GSTConfigurationModal').find('#sac').val(data.gst_data.gc_sac);
//                     jQuery('#GSTConfigurationModal').find('#description').val(data.gst_data.gc_description);
//                     jQuery('#GSTConfigurationModal').find('#gc_remark').val(data.gst_data.gc_remarks);
//                     jQuery('#GSTConfigurationModal').find('#id').val(data.gst_data.gc_id);
//                     if (data.gst_details.length > 0 && !jQuery.isEmptyObject(data.gst_details)) {
//                         for (let ind in data.gst_details) {
//                             gst_data.push(data.gst_details[ind]);
//                         }
//                         fillGSTCommodityTable();
//                     }

//                     jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
//                 }
//             } else {
//                 console.log(data.response_message);
//             }
//         },
//         error: function (jqXHR) {
//             if (jqXHR.status == 401) {
//                 console.log(jqXHR.statusText);
//             } else {
//                 console.log('Something went wrong!');
//             }
//             console.log(JSON.parse(jqXHR.responseText));
//         }
//     });
// });

$('#commonGSTConfigurationForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#GSTConfigurationModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#GSTConfigurationModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    let checkLength = jQuery("#GSTCommodityTable tbody tr").filter(function () {
        return jQuery(this).css('display') !== 'none';
    }).length;

    if (checkLength < 1) {
        toastr.error('Please Add At Least One GST Detail');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#GSTConfigurationModal').find('#submitbtn').prop('disabled', false);
        return false;
    }

    if (gst_data.length < 1) {
        toastr.error('Please Enter GST Details');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#GSTConfigurationModal').find('#submitbtn').prop('disabled', false);
        return false;
    }

    let missingTaxTypes = [];
    let requiredTaxTypes = [1, 2, 3];
    requiredTaxTypes.forEach(function (type) {
        let found = gst_data.some(function (item) {
            return item.tax_type == type;
        });
        if (!found) {
            missingTaxTypes.push(type);
        }
    });

    if (missingTaxTypes.length > 0) {
        for (const type of missingTaxTypes) {
            if (type == 1) {
                toastr.error("Please Enter SGST Tax Details");
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                jQuery('#GSTConfigurationModal').find('#submitbtn').prop('disabled', false);
                return false;
            } else if (type == 2) {
                toastr.error("Please Enter CGST Tax Details");
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                jQuery('#GSTConfigurationModal').find('#submitbtn').prop('disabled', false);
                return false;
            } else if (type == 3) {
                toastr.error("Please Enter IGST Tax Details");
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                jQuery('#GSTConfigurationModal').find('#submitbtn').prop('disabled', false);
                return false;
            }
        }
    }

    var data = new FormData(document.getElementById('commonGSTConfigurationForm'));
    var formValue = Object.fromEntries(data.entries());

    var tax1 = null;
    var tax2 = null;
    var tax3 = null;

    jQuery.each(gst_data, function (index, item) {
        if (item.tax_type == 1) {
            tax1 = item.tax;
        } else if (item.tax_type == 2) {
            tax2 = item.tax;
        } else if (item.tax_type == 3) {
            tax3 = item.tax;
        }
    });
    var plus = null;
    if (tax1 != null && tax2 != null) {
        plus = parseFloat(tax1) + parseFloat(tax2);
    }

    if (tax1 != null && tax2 != null && tax1 != tax2) {
        toastr.error("Please Enter Tax values for SGST And CGST Same");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#GSTConfigurationModal').find('#submitbtn').prop('disabled', false);
        return false;
    } else if (tax3 != plus) {
        toastr.error("IGST tax value should equal to SGST + CGST");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#GSTConfigurationModal').find('#submitbtn').prop('disabled', false);
        return false;
    }

    var formId = jQuery('#GSTConfigurationModal').find('#commonGSTConfigurationForm').find('#id').val();
    var sac = jQuery('#GSTConfigurationModal').find("#sac").val();
    var GSTConfigurationUrl = formId != undefined && formId != "" ? "verify-sac?sac=" + encodeURIComponent(sac) + "&id=" + formId : "verify-sac?sac=" + encodeURIComponent(sac);
    var formUrl = formId != undefined && formId != "" ? "update-gst_configuration" : "store-gst_configuration";

    var formData = new FormData(form);
    formData.append('gst_data', JSON.stringify(gst_data));
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (gst_data.length >= 3) {
        $.ajax({
            url: GSTConfigurationUrl,
            type: 'GET',
            dataType: 'json',
            // headers: {
            //     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            // },
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#GSTConfigurationModal').find('#submitbtn').prop('disabled', false);
                } else {
                    $.ajax({
                        type: 'POST',
                        url: formUrl,
                        data: formData,
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
                                    jQuery('#GSTConfigurationModal').find('#submitbtn').prop('disabled', false);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonGSTConfigurationForm").reset();
                                        const form = document.getElementById("commonGSTConfigurationForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }
                                        jQuery('#sac').focus();
                                        gst_data = [];
                                        jQuery('#GSTCommodityTable tbody').empty();
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#GSTConfigurationModal').find('#submitbtn').prop('disabled', false);
                                }
                                else {
                                    toastr.error(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#GSTConfigurationModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#GSTConfigurationModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#GSTConfigurationModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#GSTConfigurationModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#GSTConfigurationModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
    else {
        toastr.error("Please Enter GST Commodity Details");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#GSTConfigurationModal').find('#submitbtn').prop('disabled', false);
    }
});

$('#gst_details_form').on('submit', function (e) {
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    var data = new FormData(document.getElementById('gst_details_form'));
    var formValue = Object.fromEntries(data.entries());
    var thisModal = jQuery('#GSTCommodityDetailsModal');
    var noDuplicate = true;
    if (formValue.form_type == "edit") {
        jQuery('#GSTCommodityTable tbody input[name*="tax_type[]"]').each(function (indx) {
            if (formValue.tax_type == jQuery(this).val()
                && formValue.effective_date == jQuery(this).closest('tr').find('input[name="effective_date[]"]').val()
                && formValue.row_index != jQuery(this).closest('tr').index()) {
                noDuplicate = false;
                toastr.error("This Tax Type already exists for the same date");
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                return false;
            }
        });
    } else {
        jQuery('#GSTCommodityTable tbody input[name*="tax_type[]"]').each(function (indx) {
            if (formValue.tax_type == jQuery(this).val()
                && formValue.effective_date == jQuery(this).closest('tr').find('input[name="effective_date[]"]').val()) {
                noDuplicate = false;
                toastr.error("This Tax Type already exists for the same date");
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                return false;
            }
        });
    }

    if (noDuplicate) {
        thisModal.find('#tax_type').closest('div.control-group').removeClass('error');
        let sameDateEntries = gst_data.filter(d => d.effective_date == formValue.effective_date);
        if (formValue.tax_type == 1) { // SGST
            let cgst = sameDateEntries.find(d => d.tax_type == 2);
            if (cgst && parseFloat(cgst.tax) != parseFloat(formValue.tax)) {
                thisModal.find('#tax').addClass('error');
                toastr.error("CGST must be same as SGST");
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                noDuplicate = false;
                return;
            } else {
                thisModal.find('#tax').removeClass('error');
            }
        }

        if (formValue.tax_type == 2) { // CGST
            let sgst = sameDateEntries.find(d => d.tax_type == 1);
            if (sgst && parseFloat(sgst.tax) != parseFloat(formValue.tax)) {
                thisModal.find('#tax').addClass('error');
                toastr.error("CGST must be same as SGST");
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                noDuplicate = false;
                return;
            } else {
                thisModal.find('#tax').removeClass('error');
            }
        }

        if (formValue.tax_type == 3) { // IGST
            let sgst = sameDateEntries.find(d => d.tax_type == 1);
            let cgst = sameDateEntries.find(d => d.tax_type == 2);
            if (sgst && cgst) {
                let plus = parseFloat(sgst.tax) + parseFloat(cgst.tax);
                if (plus != parseFloat(formValue.tax)) {
                    thisModal.find('#tax').addClass('error');
                    toastr.error("IGST must equal SGST + CGST");
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    noDuplicate = false;
                    return;
                } else {
                    thisModal.find('#tax').removeClass('error');
                }
            } else {
                thisModal.find('#tax_type').closest('div.control-group').addClass('error');
                toastr.error("SGST and CGST dates must be the same.");
                // toastr.error("Please add both SGST and CGST first");
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                noDuplicate = false;
                return;
            }
        }

        if (noDuplicate) {
            let tax_type = formValue.tax_type ? formValue.tax_type : "";
            let tax = formValue.tax ? parseFloat(formValue.tax).toFixed(3) : "";
            let effective_date = formValue.effective_date ? formValue.effective_date : "";
            let remark = formValue.remark ? formValue.remark : "";
            let fname = thisModal.find('#tax_type option[value="' + tax_type + '"]').text();
            if (formValue.form_type == "edit") {
                gst_data[formValue.form_index] = formValue;

                let tblHtml = ``;
                tblHtml += `<td>`;
                tblHtml += DetailsActionDropdown('editGSTDetails', 'removeGSTDetails');
                tblHtml += `<input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                </td>`;
                // tblHtml += `<td>
                //     <div>
                //         <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                //             <i class="ri-more-2-fill"></i>
                //         </a>
                //         <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                //             <li><a class="dropdown-item edit-item-btn" onclick="editGSTDetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_customer"></i>Edit</a></li>
                //             <li><a class="dropdown-item remove-item-btn" onclick="removeGSTDetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li>
                //         </ul>
                //     </div>
                //     <input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                //     </td>`;
                tblHtml += `<td>${fname}<input type="hidden" name="gst_name[]" value="${fname}"/> 
                    <input type="hidden" name="tax_type[]" value="${tax_type}"/></td>`;
                tblHtml += `<td>${tax}<input type='hidden' name='tax[]' value="${tax}"/></td>`;
                tblHtml += `<td>${effective_date}<input type='hidden' name='effective_date[]' value="${effective_date}"/></td>`;
                tblHtml += `<td>${remark}<input type='hidden' name='remark[]' value="${remark}"/></td>`;
                jQuery('#GSTCommodityTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
                toastSuccess("Record is updated successfully.");
            } else {
                gst_data.push(formValue);
                let formIndx = gst_data.indexOf(formValue);
                if (jQuery('#GSTCommodityTable tbody').find('#noContact').length > 0) {
                    jQuery('#GSTCommodityTable tbody').empty();
                }

                let tblHtml = `<tr>`;
                tblHtml += `<td>`;
                tblHtml += DetailsActionDropdown('editGSTDetails', 'removeGSTDetails');
                tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
                </td>`;
                // tblHtml += `<td>
                //     <div>
                //         <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                //             <i class="ri-more-2-fill"></i>
                //         </a>
                //         <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                //             <li><a class="dropdown-item edit-item-btn" onclick="editGSTDetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_customer"></i>Edit</a></li>
                //             <li><a class="dropdown-item remove-item-btn" onclick="removeGSTDetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li>
                //         </ul>
                //     </div>
                //     <input type="hidden" name="form_indx" value="${formIndx}"/>
                //     </td>`;
                tblHtml += `<td>${fname}<input type="hidden" name="gst_name[]" value="${fname}"/>
                    <input type='hidden' name='tax_type[]' value="${tax_type}"/></td>`;
                tblHtml += `<td>${tax}<input type='hidden' name='tax[]' value="${tax}"/></td>`;
                tblHtml += `<td>${effective_date}<input type='hidden' name='effective_date[]' value="${effective_date}"/></td>`;
                tblHtml += `<td>${remark}<input type='hidden' name='remark[]' value="${remark}"/></td>`;
                tblHtml += `</tr>`;
                jQuery('#GSTCommodityTable tbody').append(tblHtml);
                toastSuccess("Record is inserted successfully.");
            }

            if (formValue.form_type == "edit") {
                thisModal.modal('hide');
            } else {
                // jQuery('#tax_type').val('').trigger('change');
                // jQuery('#tax').val('');
                // jQuery('#effective_date').val(currentDate);
                // jQuery('#remark').val('');

                let formElement = document.getElementById('gst_details_form');
                formElement.reset();
                jQuery('#tax_type').val('').trigger('change');
                jQuery('#tax').val('');
                jQuery('#effective_date').val(currentDate);
                jQuery('#remark').val('');
                setTimeout(function () {
                    $(formElement).removeClass('was-validated');
                    $(formElement).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                    thisModal.find('.error').removeClass('error');

                    let $select = jQuery('#tax_type');
                    $select.one('select2:opening', function (e) {
                        e.preventDefault();
                    });

                    let sel = $select.next('.select2-container').find('.select2-selection');
                    if (sel.length) {
                        sel.attr('tabindex', 0).focus();
                    }

                    $select.select2('close');
                }, 150);
            }
        }
    }
    else {
        thisModal.find('#tax_type').closest('div.control-group').addClass('error').focus();
        toastr.error("This Tax Type Is Already Selected");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
    }
});

function fillGSTCommodityTable() {
    var thisModal = jQuery('#GSTCommodityDetailsModal');
    var formId = jQuery('#GSTConfigurationModal').find('#commonGSTConfigurationForm').find('#id').val();
    if (gst_data.length > 0) {
        for (let key in gst_data) {
            let formIndx = gst_data.indexOf(gst_data[key]);
            var tax_type = gst_data[key].tax_type ? gst_data[key].tax_type : "";
            var tax = gst_data[key].tax ? parseFloat(gst_data[key].tax).toFixed(3) : "";
            var effective_date = gst_data[key].effective_date ? gst_data[key].effective_date : "";
            var remark = gst_data[key].remark ? gst_data[key].remark : "";
            var gcd_details_id = gst_data[key].gcd_details_id ? gst_data[key].gcd_details_id : "";
            var gc_id = gst_data[key].gc_id ? gst_data[key].gc_id : "";

            if (formId == undefined) {
                var fname = thisModal.find('#tax_type option[value="' + tax_type + '"]').text();
            } else {
                var fname = thisModal.find('#tax_type option[value="' + tax_type + '"]').text();
            }

            if (jQuery('#GSTCommodityTable tbody').find('#noContact').length > 0) {
                jQuery('#GSTCommodityTable tbody').empty();
            }

            let tblHtml = `<tr>`;
            tblHtml += `<td>`;
            tblHtml += DetailsActionDropdown('editGSTDetails', 'removeGSTDetails');
            tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;
            // tblHtml += `<td>
            //     <div>
            //         <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
            //             <i class="ri-more-2-fill"></i>
            //         </a>
            //         <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
            //             <li><a class="dropdown-item edit-item-btn" onclick="editGSTDetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i>Edit</a></li>
            //             <li><a class="dropdown-item remove-item-btn" onclick="removeGSTDetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li>
            //         </ul>
            //     </div>
            //     <input type="hidden" name="form_indx" value="${formIndx}"/>
            //     </td>`;
            tblHtml += `<td>${fname}<input type="hidden" name="gst_name[]" value="${fname}"/>
                <input type="hidden" name="gcd_details_id[]" value="${gcd_details_id}"/>
                <input type="hidden" name="gc_id[]" value="${gc_id}"/>
                <input type='hidden' name='tax_type[]' value="${tax_type}"/></td>`;
            tblHtml += `<td>${tax}<input type='hidden' name='tax[]' value="${tax}"/></td>`;
            tblHtml += `<td>${effective_date}<input type='hidden' name='effective_date[]' value="${effective_date}"/></td>`;
            tblHtml += `<td>${remark}<input type='hidden' name='remark[]' value="${remark}"/></td>`;
            tblHtml += `</tr>`;
            jQuery('#GSTCommodityTable tbody').append(tblHtml);
        }
    }
}

function editGSTDetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();
    fillGstDetailForm(formIndx, rawIndx);
}

// details form edit
function fillGstDetailForm(formIndx, rawIndx) {
    let thisForm = jQuery('#GSTCommodityDetailsModal');
    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    var frmData = gst_data[formIndx];
    thisForm.find("#tax_type").val(frmData.tax_type).trigger("change");
    thisForm.find("#tax").val(parseFloat(frmData.tax).toFixed(3));
    thisForm.find("#effective_date").val(frmData.effective_date);
    thisForm.find("#remark").val(frmData.remark);
    thisForm.find("#gcd_details_id").val(frmData.gcd_details_id);
    thisForm.find("#gst_name").val(frmData.gst_name);
    thisForm.find("#gc_id").val(frmData.gc_id);
    thisForm.modal('show');
}

function removeGSTDetails(th) {
    // Are you Sure, You want to Delete ?
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        removeFormObjGSTCommodity(formIndx);
        jQuery(th).closest("tr").remove();
    });
}

function removeFormObjGSTCommodity(formIndx) {
    delete gst_data[formIndx];
    gst_data = gst_data.filter(element => element != null);
    jQuery('#GSTCommodityTable tbody').empty();
    fillGSTCommodityTable()
}

jQuery('#GSTConfigurationModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#GSTConfigurationModal');
    thisForm.find('#id').val('');
    document.getElementById("commonGSTConfigurationForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });

    gst_data = [];
    jQuery('#GSTCommodityTable tbody').empty().append(`<tr class="centeralign" id="noContact"><td colspan="6">No GST Commodity Details Added</td></tr>`);
    jQuery('#GSTConfigurationModal').find('#add_new').hide();
    
    const form = document.getElementById("commonGSTConfigurationForm");
    if (form) {
        form.classList.remove('was-validated');
    }
});

$('#GSTConfigurationModal').on('shown.bs.modal', function () {
    const input = document.getElementById('sac');
    input?.focus();

    var formIdblank = jQuery('#GSTConfigurationModal').find('#id').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#GSTConfigurationModal').find('#add_new').show();
    } else {
        jQuery('#GSTConfigurationModal').find('#add_new').hide();
    }
});

jQuery('#GSTConfigurationModal').on('click', '#add_new', function () {
    jQuery('#GSTConfigurationModal').find('#id').val('');
    document.getElementById("commonGSTConfigurationForm").reset();
    const form = document.getElementById("commonGSTConfigurationForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#sac').focus();
    gst_data = [];
    jQuery('#GSTCommodityTable tbody').empty().append(`<tr class="centeralign" id="noContact"><td colspan="6">No GST Commodity Details Added</td></tr>`);
    jQuery('#GSTConfigurationModal').find('#add_new').hide();
});

jQuery('#GSTCommodityDetailsModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#GSTCommodityDetailsModal');
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    jQuery('#gst_details_form').trigger("reset");
});

jQuery('#GSTCommodityDetailsModal').on('shown.bs.modal', function () {
    setTimeout(function () {
        let sel = jQuery('#tax_type')
            .next('.select2-container')
            .find('.select2-selection');
        sel.attr('tabindex', 0).focus();
    }, 20);

    let thisForm = jQuery('#GSTCommodityDetailsModal');
    let formType = thisForm.find("#form_type").val();
    if (formType == "add") {
        jQuery("#effective_date").val(currentDate);
    }
    else if (formType == "edit") {
        jQuery("#effective_date").val();
    }
    else {
        jQuery("#effective_date").val(currentDate);
    }
});

jQuery(document).on('click', '#sac_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#sac_suggesion').val(suggest);
    var hidden = jQuery('#sac_suggesion').val();
    var suggestion_list = jQuery('#sac_list').html;
    jQuery('#GSTConfigurationModal').find('#sac').val(hidden)
    var sac = hidden;
    if (suggestion_list != '') {
        checkSAC(sac);
    }
    jQuery('#sac_list').html('');
});

function checkSAC() {
    var sac = jQuery('#sac').val();
    var formId = jQuery('#GSTConfigurationModal').find('#commonGSTConfigurationForm').find('#id').val();
    var formUrl = formId != undefined && formId != "" ? "verify-sac?sac=" + encodeURIComponent(sac) + "&id=" + formId : "verify-sac?sac=" + encodeURIComponent(sac);
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

function verifySAC() {
    var SacName = jQuery('#sac').val();
    var suggestion_list = jQuery('#sac_list').html;
    if (suggestion_list != '') {
        checkSAC(SacName);
    }
}

jQuery('#sac').on('change', function () {
    verifySAC();
});