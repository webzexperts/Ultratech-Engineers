// Edit chemical lpt row click
jQuery('#dyntable tbody').on('click', '.edit_lpt_chemical', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["lpt_id"]) {
        fetchAndFillChemicalLPT(data["lpt_id"]);
    }
});

// Function to fetch and fill chemical lpt data
function fetchAndFillChemicalLPT(id) {
    if (!id) return;
    jQuery('#LPTChemicalModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-lpt_chemical",
        type: 'GET',
        data: { id: id }, 
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.lpt_chemical_data != null) {
                // if(data.items != ""){
                //     $('#lpt_chemical_id').html('<option value="">Select LPT Chemical</option>');
                //     data.items.forEach(function(item) {
                //         $('#lpt_chemical_id').append(
                //             `<option value="${item.id}">${item.item_name}</option>`
                //         );
                //     });
                // }

                jQuery('#LPTChemicalModal').find('#lpt_inward_type').val(data.lpt_chemical_data.lpt_inward_type).trigger('change.select2');
                setSelect2Readonly('#lpt_inward_type', true);
                jQuery('#LPTChemicalModal').find('#lpt_chemical_id').val(data.lpt_chemical_data.lpt_chemical_id).trigger('change.select2');
                if (data.lpt_chemical_data.lpt_inward_type == "From GRN") {
                    setSelect2Readonly('#lpt_chemical_id', true);
                } else if (data.lpt_chemical_data.lpt_inward_type == "Manual") {
                    setSelect2Readonly('#lpt_chemical_id', false);
                }
                jQuery('#LPTChemicalModal').find('#lpt_chemical_name').val(data.lpt_chemical_data.lpt_chemical_name != "" ? data.lpt_chemical_data.lpt_chemical_name : "");
                jQuery('#LPTChemicalModal').find('#lpt_designation').val(data.lpt_chemical_data.lpt_designation != "" ? data.lpt_chemical_data.lpt_designation : "");
                jQuery('#LPTChemicalModal').find('#lpt_make').val(data.lpt_chemical_data.lpt_make != "" ? data.lpt_chemical_data.lpt_make : "");
                jQuery('#LPTChemicalModal').find('#lpt_batch_no').val(data.lpt_chemical_data.lpt_batch_no != "" ? data.lpt_chemical_data.lpt_batch_no : "");
                jQuery('#LPTChemicalModal').find('#lpt_identification_no').val(data.lpt_chemical_data.lpt_identification_no != "" ? data.lpt_chemical_data.lpt_identification_no : "");
                jQuery('#LPTChemicalModal').find('#lpt_mfg_date').val(data.lpt_chemical_data.lpt_mfg_date != "" ? data.lpt_chemical_data.lpt_mfg_date : "");
                jQuery('#LPTChemicalModal').find('#lpt_status').val(data.lpt_chemical_data.lpt_status).trigger('change.select2');
                jQuery('#LPTChemicalModal').find('#lpt_document_ref_no').val(data.lpt_chemical_data.lpt_document_ref_no != "" ? data.lpt_chemical_data.lpt_document_ref_no : "");
                jQuery('#LPTChemicalModal').find('#lpt_validity_date').val(data.lpt_chemical_data.lpt_validity_date != "" ? data.lpt_chemical_data.lpt_validity_date : "");
                jQuery('#LPTChemicalModal').find('#lpt_remark').val(data.lpt_chemical_data.lpt_remark != "" ? data.lpt_chemical_data.lpt_remark : "");
                jQuery('#LPTChemicalModal').find('#grn_number').val(data.lpt_chemical_data.grn_number);
                jQuery('#LPTChemicalModal').find('#grn_date').val(data.lpt_chemical_data.grn_date);
                jQuery('#LPTChemicalModal').find('#supplier_name').val(data.lpt_chemical_data.supplier_name);
                jQuery('#LPTChemicalModal').find('#challan_number').val(data.lpt_chemical_data.grn_challan_number);
                jQuery('#LPTChemicalModal').find('#challan_date').val(data.lpt_chemical_data.grn_challan_date);
                jQuery('#LPTChemicalModal').find('#pending_btn').prop('disabled', true);
                jQuery('#LPTChemicalModal').find('#id').val(data.lpt_chemical_data.lpt_id);

                const form = document.getElementById("commonLPTChemicalForm");
                if (form) form.classList.remove('was-validated');
                setSelect2Readonly("#lpt_inward_type", false);
                InwardTypeChange();
                setTimeout(function () {
                    let sel = jQuery('#lpt_inward_type')
                        .next('.select2-container')
                        .find('.select2-selection');
                    sel.attr('tabindex', 0).focus();
                }, 20);
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

// Reset button click for chemical lpt modal
jQuery('#resetbtn').on('click', function () {
    var formId = jQuery('#LPTChemicalModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonLPTChemicalForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        jQuery('#commonLPTChemicalForm').find('.toggleModalBtn').prop('disabled', true);
        $('#lpt_chemical_id').val('').trigger('change.select2');
        jQuery('#commonLPTChemicalForm').find('#lpt_status').val('Active').trigger('change.select2');
        getLNRData();
        InwardTypeChange();
        setSelect2Readonly("#lpt_inward_type", false);
        setTimeout(function () {
            let sel = jQuery('#lpt_inward_type')
                .next('.select2-container')
                .find('.select2-selection');
            sel.attr('tabindex', 0).focus();
        }, 20);
    } else {
        fetchAndFillChemicalLPT(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_lpt_chemical', function () {
//     jQuery('#LPTChemicalModal').modal('show');
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-lpt_chemical",
//         type: 'GET',
//         data: "id=" + data["lpt_id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 if (data.lpt_chemical_data != null) {
//                     // if(data.items != ""){
//                     //     $('#lpt_chemical_id').html('<option value="">Select LPT Chemical</option>');
//                     //     data.items.forEach(function(item) {
//                     //         $('#lpt_chemical_id').append(
//                     //             `<option value="${item.id}">${item.item_name}</option>`
//                     //         );
//                     //     });
//                     // }

//                     jQuery('#LPTChemicalModal').find('#lpt_inward_type').val(data.lpt_chemical_data.lpt_inward_type).trigger('change.select2');
//                     setSelect2Readonly('#lpt_inward_type', true);
//                     jQuery('#LPTChemicalModal').find('#lpt_chemical_id').val(data.lpt_chemical_data.lpt_chemical_id).trigger('change.select2');
//                     if (data.lpt_chemical_data.lpt_inward_type == "From GRN") {
//                         setSelect2Readonly('#lpt_chemical_id', true);
//                     } else if (data.lpt_chemical_data.lpt_inward_type == "Manual") {
//                         setSelect2Readonly('#lpt_chemical_id', false);
//                     }
//                     jQuery('#LPTChemicalModal').find('#lpt_chemical_name').val(data.lpt_chemical_data.lpt_chemical_name != "" ? data.lpt_chemical_data.lpt_chemical_name : "");
//                     jQuery('#LPTChemicalModal').find('#lpt_designation').val(data.lpt_chemical_data.lpt_designation != "" ? data.lpt_chemical_data.lpt_designation : "");
//                     jQuery('#LPTChemicalModal').find('#lpt_make').val(data.lpt_chemical_data.lpt_make != "" ? data.lpt_chemical_data.lpt_make : "");
//                     jQuery('#LPTChemicalModal').find('#lpt_batch_no').val(data.lpt_chemical_data.lpt_batch_no != "" ? data.lpt_chemical_data.lpt_batch_no : "");
//                     jQuery('#LPTChemicalModal').find('#lpt_identification_no').val(data.lpt_chemical_data.lpt_identification_no != "" ? data.lpt_chemical_data.lpt_identification_no : "");
//                     jQuery('#LPTChemicalModal').find('#lpt_mfg_date').val(data.lpt_chemical_data.lpt_mfg_date != "" ? data.lpt_chemical_data.lpt_mfg_date : "");
//                     jQuery('#LPTChemicalModal').find('#lpt_status').val(data.lpt_chemical_data.lpt_status).trigger('change.select2');
//                     jQuery('#LPTChemicalModal').find('#lpt_document_ref_no').val(data.lpt_chemical_data.lpt_document_ref_no != "" ? data.lpt_chemical_data.lpt_document_ref_no : "");
//                     jQuery('#LPTChemicalModal').find('#lpt_validity_date').val(data.lpt_chemical_data.lpt_validity_date != "" ? data.lpt_chemical_data.lpt_validity_date : "");
//                     jQuery('#LPTChemicalModal').find('#lpt_remark').val(data.lpt_chemical_data.lpt_remark != "" ? data.lpt_chemical_data.lpt_remark : "");
//                     jQuery('#LPTChemicalModal').find('#grn_number').val(data.lpt_chemical_data.grn_number);
//                     jQuery('#LPTChemicalModal').find('#grn_date').val(data.lpt_chemical_data.grn_date);
//                     jQuery('#LPTChemicalModal').find('#supplier_name').val(data.lpt_chemical_data.supplier_name);
//                     jQuery('#LPTChemicalModal').find('#challan_number').val(data.lpt_chemical_data.grn_challan_number);
//                     jQuery('#LPTChemicalModal').find('#challan_date').val(data.lpt_chemical_data.grn_challan_date);
//                     jQuery('#LPTChemicalModal').find('#pending_btn').prop('disabled', true);
//                     jQuery('#LPTChemicalModal').find('#id').val(data.lpt_chemical_data.lpt_id);
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

$('#commonLPTChemicalForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#LPTChemicalModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#LPTChemicalModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    // var lpt_inward_type = jQuery('#lpt_inward_type option:selected').val();
    // var lpt_grnd_id = jQuery('#lpt_grnd_id').val();
    // if(lpt_inward_type == 'From GRN' && lpt_grnd_id == '') {
    //      toastr.error('Please Select Atleast One GRN From Pending.');
    //     jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
    //     jQuery('#LPTChemicalModal').find('#submitbtn').prop('disabled', false);
    //     return;
    // }

    var lpt_inward_type = jQuery('#lpt_inward_type option:selected').val();
    var lpt_chemical_id = jQuery('#lpt_chemical_id option:selected').val();
    var lpt_grnd_id = jQuery('#lpt_grnd_id').val();
    if (lpt_inward_type == 'From GRN' && lpt_grnd_id == '' && lpt_chemical_id == '') {
         toastr.error('Select At Least One GRN From Pending.');
        //  toastr.error('Please Select Atleast One GRN From Pending.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#LPTChemicalModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#LPTChemicalModal').find('#commonLPTChemicalForm').find('#id').val();
    var formUrl = formId != undefined && formId != "" ? "update-lpt_chemical" : "store-lpt_chemical";
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    if (lpt_inward_type != '' && lpt_inward_type != undefined) {
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
                        jQuery('#LPTChemicalModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formId == undefined || formId == "") {
                        function nextFn() {
                            document.getElementById("commonLPTChemicalForm").reset();
                            const form = document.getElementById("commonLPTChemicalForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }

                            setSelect2Readonly('#lpt_inward_type', false);
                            jQuery('#commonLPTChemicalForm').find('.toggleModalBtn').prop('disabled', true);
                            $('#lpt_chemical_id').val('').trigger('change.select2');
                            jQuery('#commonLPTChemicalForm').find('#lpt_status').val('Active').trigger('change.select2');
                            getLNRData();
                            setTimeout(function () {
                                let sel = jQuery('#lpt_inward_type')
                                    .next('.select2-container')
                                    .find('.select2-selection');
                                sel.attr('tabindex', 0).focus();
                            }, 20);
                        }
                        toastSuccess(data.response_message, nextFn);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#LPTChemicalModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastError(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#LPTChemicalModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                     toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#LPTChemicalModal').find('#submitbtn').prop('disabled', false);
                }
            },
            error: function (xhr) {
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                jQuery('#LPTChemicalModal').find('#submitbtn').prop('disabled', false);
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let errorMsg = '';
                    $.each(errors, function (key, value) {
                        errorMsg += value + '\n';
                    });
                     toastr.error(errorMsg);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#LPTChemicalModal').find('#submitbtn').prop('disabled', false);
                } else {
                     toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#LPTChemicalModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    }
});

// $('#LPTChemicalModal').on('shown.bs.modal', function () {
//     var formIdblank = jQuery('#commonLPTChemicalForm').find('input[name="id"]').val();
//     if (formIdblank == "") {
//         getLNRData();
//         setTimeout(function () {
//             let sel = jQuery('#lpt_inward_type')
//                 .next('.select2-container')
//                 .find('.select2-selection');
//             sel.attr('tabindex', 0).focus();
//         }, 20);

//         var thisForm = jQuery('#LPTChemicalModal');
//         thisForm.find('#lpt_status').val('Active').trigger('change');
//         thisForm.find('#lpt_inward_type').val('Manual').trigger('change');
//         setSelect2Readonly('#lpt_inward_type', false);
//     }

//     var LPTInwardType = jQuery('#commonLPTChemicalForm').find('#lpt_inward_type').val();
//     if (LPTInwardType == "From GRN") {
//         jQuery('#LPTChemicalModal').find('#pending_btn').attr('disabled', true);
//     } else {
//         jQuery('#LPTChemicalModal').find('#pending_btn').attr('disabled', true);
//         // jQuery('#LPTChemicalModal').find('#pending_btn').attr('disabled', false);
//     }
// });

$('#LPTChemicalModal').on('show.bs.modal', function () {
    var formIdblank = jQuery('#commonLPTChemicalForm').find('#id').val();
    if (formIdblank == "" || formIdblank == undefined) {
        getLNRData();
        jQuery('#commonLPTChemicalForm').find('#lpt_status').val('Active').trigger('change.select2');
    }

    if(formIdblank != "" &&  formIdblank != undefined){
        setSelect2Readonly('#commonLPTChemicalForm #lpt_inward_type', true);
        setSelect2Readonly('#commonLPTChemicalForm #lpt_chemical_id', true);
        setSelect2Readonly('#commonLPTChemicalForm #lpt_status', true);
        jQuery('#commonLPTChemicalForm').find('#pending_btn').prop('disabled', true);
    }
});

jQuery('#LPTChemicalModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#LPTChemicalModal');
    thisForm.find('#id').val('');
    document.getElementById("commonLPTChemicalForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).prop('checked', false);
    });

    // setSelect2Readonly('#commonLPTChemicalForm #lpt_status', false);
    // setSelect2Readonly('#commonLPTChemicalForm #lpt_inward_type', false);
    // setSelect2Readonly('#commonLPTChemicalForm #lpt_chemical_id', false);
});

// get Last Reset Data
function getLNRData() {
    jQuery.ajax({
        url: "get-chemical_lpt_lnr_data",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                if (data.lnr_data != null) {
                    jQuery('#lpt_inward_type').val(data.lnr_data.lpt_inward_type).trigger("change");
                } else {
                    jQuery('#lpt_inward_type').val('Manual').trigger("change");
                }
            } else {
                console.log(data.response_message)
            }
        },
    });
}

$('#lpt_inward_type').on('change', function () {
    InwardTypeChange();
});

function InwardTypeChange() {
    let inwardType = jQuery('#lpt_inward_type').val();
    if (inwardType == 'From GRN') {
        jQuery('#commonLPTChemicalForm').find('#pending_btn').prop('disabled', true);
        jQuery('#commonLPTChemicalForm').find('#pending_btn').focus();
        setSelect2Readonly('#lpt_chemical_id', true);
        setSelect2Required('#lpt_chemical_id', false);
        jQuery('#lptchemicalAsterisk').hide();
        GetPendingGRNforLPTChemical();
    } else if (inwardType == 'Manual') {
        jQuery('#commonLPTChemicalForm').find('#pending_btn').prop('disabled', true);
        jQuery('#commonLPTChemicalForm').find('#pending_btn').blur();
        setSelect2Readonly('#lpt_chemical_id', false);
        setSelect2Required('#lpt_chemical_id', true);
        jQuery('#lptchemicalAsterisk').show();
    }

    // let inwardType = jQuery('#lpt_inward_type').val();
    // if (inwardType == 'Manual') {
    //     setSelect2Readonly('#lpt_chemical_id', false);
    //     jQuery('#commonLPTChemicalForm').find('#lpt_chemical_id').val('').trigger("change.select2").removeClass('skip-tab').attr('tabindex', '0');
    //     setSelect2Required('#lpt_chemical_id', true);
    //     jQuery('#lptchemicalAsterisk').show();
    //     jQuery('#commonLPTChemicalForm').find('#pending_btn').prop('disabled', true);
    // } else if (inwardType == 'From GRN') {
    //     setSelect2Readonly('#lpt_chemical_id', true);
    //     jQuery('#commonLPTChemicalForm').find('#lpt_chemical_id').val('').trigger("change.select2").attr('tabindex', '-1');
    //     setSelect2Required('#lpt_chemical_id', false);
    //     jQuery('#lptchemicalAsterisk').hide();

    //     var $chemicalType = jQuery('#LPTChemicalModal').find('#lpt_chemical_id');
    //     $chemicalType.val('').trigger('change.select2');
    //     $chemicalType.attr('tabindex', '-1');
    //     setTimeout(function () {
    //         $chemicalType.next('.select2-container').find('.selection span').attr('tabindex', '-1');
    //         $chemicalType.next('.select2-container').find('focus-proxy').remove();
    //     }, 200);
    //     setSelect2Readonly('#lpt_chemical_id', true);
    //     GetPendingGRNforLPTChemical();

    //     jQuery('#commonLPTChemicalForm').find('#pending_btn').prop('disabled', false);
    // }
}

// jQuery('#LPTChemicalModal').on('change', '#lpt_inward_type', function () {
//     if (this.value == 'Manual') {
//         setSelect2Readonly('#lpt_chemical_id', false);
//         jQuery('#commonLPTChemicalForm').find('#lpt_chemical_id').val('').trigger("change.select2").removeClass('skip-tab').attr('tabindex', '0');
//         setSelect2Required('#lpt_chemical_id', true);
//         jQuery('#lptchemicalAsterisk').show();
//         jQuery('#commonLPTChemicalForm').find('#pending_btn').prop('disabled', true);
//         jQuery('#commonLPTChemicalForm').find('#pending_btn').blur();
//     } else {
//         setSelect2Readonly('#lpt_chemical_id', true);
//         jQuery('#commonLPTChemicalForm').find('#lpt_chemical_id').val('').trigger("change.select2").attr('tabindex', '-1');
//         setSelect2Required('#lpt_chemical_id', false);
//         jQuery('#lptchemicalAsterisk').hide();

//         var $chemicalType = jQuery('#LPTChemicalModal').find('#lpt_chemical_id');
//         $chemicalType.val('').trigger('change.select2');
//         $chemicalType.attr('tabindex', '-1');
//         setTimeout(function() {
//             $chemicalType.next('.select2-container').find('.selection span').attr('tabindex', '-1');
//             $chemicalType.next('.select2-container').find('focus-proxy').remove();
//         }, 200);
//         setSelect2Readonly('#lpt_chemical_id', true);
//         GetPendingGRNforLPTChemical();
//     }
// });

function suggestDesignation(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "lpt_chemical_designation-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#lpt_designation").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#lpt_designation_list').html(data.designationList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#lpt_designation").removeClass('file-loader');
                var errMessage = JSON.parse(jqXHR.responseText);
                if (errMessage.errors) {
                    countryValidator.showErrors(errMessage.errors);
                } else if (jqXHR.status == 401) {
                     toastr.error(jqXHR.statusText);
                } else {
                     toastr.error('Something went wrong!');
                    console.log(JSON.parse(jqXHR.responseText));
                }
            }
        });
    }
}

jQuery(document).on('click', '#lpt_designation_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#lpt_designation_suggesion').val(suggest);
    var hidden = jQuery('#lpt_designation_suggesion').val();
    var suggestion_list = jQuery('#lpt_designation_list').html;
    jQuery('#LPTChemicalModal').find('#lpt_designation').val(hidden)
    jQuery('#lpt_designation_list').html('');
});

function suggestMake(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "lpt_make-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#lpt_make").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#lpt_make_list').html(data.makeList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#lpt_make").removeClass('file-loader');
                var errMessage = JSON.parse(jqXHR.responseText);
                if (errMessage.errors) {
                    countryValidator.showErrors(errMessage.errors);
                } else if (jqXHR.status == 401) {
                     toastr.error(jqXHR.statusText);
                } else {
                     toastr.error('Something went wrong!');
                    console.log(JSON.parse(jqXHR.responseText));
                }
            }
        });
    }
}

jQuery(document).on('click', '#lpt_make_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#lpt_make_suggesion').val(suggest);
    var hidden = jQuery('#lpt_make_suggesion').val();
    var suggestion_list = jQuery('#lpt_make_list').html;
    jQuery('#LPTChemicalModal').find('#lpt_make').val(hidden)
    jQuery('#lpt_make_list').html('');
});

// pending logic code
function GetPendingGRNforLPTChemical() {
    var lptinwardtype = jQuery('#lpt_inward_type option:selected').val();
    var thisModal = jQuery('#PendingForGRNModal');
    var thisForm = jQuery('#commonLPTChemicalForm');
    if (lptinwardtype != undefined) {
        if (lptinwardtype == "From GRN") {
            jQuery.ajax({
                url: 'get-grn_list_for_lpt_chemical',
                // url: 'get-grn_list_for_lpt_chemical?lptinwardtype='+lptinwardtype,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    if (data.response_code == 1) {
                        if (data.response_code == 1 && data.grn_data.length > 0) {
                            // new code
                            var usedParts = [];
                            var totalDisb = 0;
                            var found = 0;

                            thisForm.find('#PendingForLPTChemicalTable tbody input[name="form_indx"]').each(function (indx) {
                                let frmIndx = jQuery(this).val();

                                let jbEorkOrderId = grn_data[frmIndx].grnd_id;
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
                            if (data.grn_data.length > 0 && !jQuery.isEmptyObject(data.grn_data)) {
                                found = 1;
                                for (let idx in data.grn_data) {
                                    var inUse = isUsed(data.grn_data[idx].grnd_id);
                                    var in_use = data.grn_data[idx].in_use == true ? 'readonly' : '';
                                    totalEntry++;
                                    tblHtml += `
                                                <tr>
                                                    <td>
                                                        <input type="radio" name="grnd_id[]" class="simple-check radio-filter-remove ${inUse ? 'in-use' : ''}" id="grnd_ids_${data.grn_data[idx].grnd_id}" value="${data.grn_data[idx].grnd_id}" ${inUse ? 'checked' : ''} ${in_use}/>
                                                    </td>
                                                    <td>${data.grn_data[idx].grn_number}</td>
                                                    <td>${data.grn_data[idx].grn_date}</td>
                                                    <td>${data.grn_data[idx].supplier_name != null ? data.grn_data[idx].supplier_name : ''}</td>
                                                    <td>${data.grn_data[idx].grn_challan_number != null ? data.grn_data[idx].grn_challan_number : ''}</td>
                                                    <td>${data.grn_data[idx].grn_challan_date != null ? data.grn_data[idx].grn_challan_date : ''}</td>
                                                    <td>${data.grn_data[idx].item_name != null ? data.grn_data[idx].item_name : ''}</td>
                                                    <td>${data.grn_data[idx].grnd_qty != null ? parseFloat(data.grn_data[idx].grnd_qty).toFixed(3) : ''}</td>
                                                    <td>${data.grn_data[idx].pend_grn_qty != null ? parseFloat(data.grn_data[idx].pend_grn_qty).toFixed(3) : ''}</td>
                                                </tr>`;

                                }
                            } else {
                                tblHtml += `<tr class="centeralign" id="noPendingPo">
                                    <td colspan="9">No Pending GRN Available</td>
                                </tr>`;
                            }

                            var $table = jQuery("#PendingForGRNModal").find('#PendingForLPTChemicalTable');
                            if (jQuery.fn.DataTable.isDataTable($table)) {
                                $table.DataTable().clear().destroy();
                            }
                            jQuery('#PendingForLPTChemicalTable tbody').empty().append(tblHtml);
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
                            if (lptinwardtype == 'Manual') {
                                jQuery('.toggleModalBtn').prop('disabled', true);
                            } else {
                                jQuery('.toggleModalBtn').prop('disabled', false);
                            }
                        } else {
                            jQuery('.toggleModalBtn').prop('disabled', true);
                        }
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
    }
}

// // old working code start
// jQuery('#PendingForGRNModal').on('show.bs.modal', function (e) {
//     var dt = jQuery('#PendingForLPTChemicalTable').DataTable();
//     fixDataTableColumnsUntilAdjusted(dt);
//     var usedParts = [];
//     var grnd_id = jQuery('#LPTChemicalModal').find('#grnd_id').val();
//     if (grnd_id != "" && grnd_id != null) {
//         usedParts.push(Number(grnd_id));
//     }

//     function isUsed(pjId) {
//         if (usedParts.includes(Number(pjId))) {
//             return true;
//         }
//         return false;
//     }

//     jQuery('#PendingForLPTChemicalTable tbody tr').each(function (indx) {
//         var checkField = jQuery(this).find('input[name="grnd_id[]"]');
//         var partId = jQuery(checkField).val();
//         var inUse = isUsed(partId);
//         if (inUse) {
//             jQuery(checkField).prop('checked', true);
//         } else {
//             jQuery(checkField).prop('checked', false);
//         }
//     });
// });
// // old working code end

// // this code is comment start
// jQuery('#PendingForGRNModal').on('show.bs.modal', function (e) {
//     var dt = jQuery('#PendingForLPTChemicalTable').DataTable();
//     fixDataTableColumnsUntilAdjusted(dt);
//     var usedParts = [];
//     var grnd_id = jQuery('#LPTChemicalModal').find('#lpt_grnd_id').val();
//     if (grnd_id != "" && grnd_id != null) {
//         usedParts.push(Number(grnd_id));
//     }

//     function isUsed(pjId) {
//         if (usedParts.includes(Number(pjId))) {
//             return true;
//         }
//         return false;
//     }

//     jQuery('#PendingForLPTChemicalTable tbody tr').each(function (indx) {
//         var checkField = jQuery(this).find('input[name="grnd_id[]"]');
//         var partId = jQuery(checkField).val();
//         var inUse = isUsed(partId);
//         if (inUse) {
//             jQuery(checkField).prop('checked', true);
//         } else {
//             jQuery(checkField).prop('checked', false);
//         }
//     });
// });
// // this code is comment start

jQuery('#PendingForGRNModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = jQuery('#lpt_grnd_id').val() != '' ? document.getElementById('lpt_chemical_name') : document.getElementById('lpt_inward_type');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }
});

$('#PendingForLPTChemicalForm').on('submit', function (e) {
    e.preventDefault();
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#PendingForGRNModal').find('#submitbtn').prop('disabled', true);

    let chkArr = [];
    jQuery("#PendingForLPTChemicalForm").find("[id^='grnd_ids_']:checked")
        .each(function () {
            chkArr.push(jQuery(this).val());
        });

    if (chkArr.length === 0) {
         toastr.error('Select At Least One GRN From Pending.');
        //  toastr.error('Please Select Atleast One GRN From Pending.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PendingForGRNModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    jQuery.ajax({
        url: 'get-grn_part_data_lpt_chemical',
        type: 'GET',
        data: { grnd_ids: chkArr.join(',') },
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                if (data.grn_data) {
                    jQuery('#LPTChemicalModal').find("#lpt_grnd_id").val(data.grn_data.grnd_id);
                    jQuery('#LPTChemicalModal').find('#lpt_chemical_id').val(data.grn_data.lpt_chemical_id).trigger('change.select2');
                    jQuery('#LPTChemicalModal').find("#grn_number").val(data.grn_data.grn_number);
                    jQuery('#LPTChemicalModal').find("#grn_date").val(data.grn_data.grn_date);
                    jQuery('#LPTChemicalModal').find("#supplier_name").val(data.grn_data.supplier_name);
                    jQuery('#LPTChemicalModal').find("#challan_number").val(data.grn_data.grn_challan_number);
                    jQuery('#LPTChemicalModal').find("#challan_date").val(data.grn_data.grn_challan_date);
                    jQuery('#LPTChemicalModal').find('#pending_btn').prop('disabled', true);
                    setSelect2Readonly("#lpt_inward_type", true);
                } else {
                    setSelect2Readonly("#lpt_inward_type", false);
                }
                jQuery("#PendingForGRNModal").modal('hide');
            } else {

            }

            jQuery('#PendingForGRNModal').find('#submitbtn').prop('disabled', false);
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        },
        error: function (jqXHR) {
            if (jqXHR.status == 401) {
                toastr.error(jqXHR.statusText);
            } else {
                toastr.error('Something went wrong!');
                console.log(jqXHR.responseText);
            }
            jQuery('#PendingForGRNModal').find('#submitbtn').prop('disabled', false);
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        }
    });
});

// // old working code start
// $('#PendingForLPTChemicalForm').on('submit', function (e) {
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery('#PendingForGRNModal').find('#submitbtn').prop('disabled', true);
//     e.preventDefault();

//     var chkCount = 0;
//     var chkArr = [];
//     var chkId = [];

//     jQuery("#PendingForLPTChemicalForm").find("[id^='grnd_ids_']").each(function () {
//         var thisId = jQuery(this).attr('id');
//         var splt = thisId.split('grnd_ids_');
//         var intId = splt[1];
//         if (jQuery(this).is(':checked')) {
//             chkArr.push(jQuery(this).val())
//             chkId.push(intId);
//             chkCount++;
//         }
//     });

//     if (chkCount == 0) {
//         toastr.error('Please Select Atleast One GRN From Pending.');
//         //  toastr.error('Please Select Atleast One GRN From Pending.');
//         jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
//         jQuery('#PendingForGRNModal').find('#submitbtn').prop('disabled', false);
//         return false;
//     }
//     else {
//         var url = "get-grn_part_data_lpt_chemical?grnd_ids=" + chkArr.join(',');
//         jQuery.ajax({
//             url: url,
//             type: 'GET',
//             dataType: 'json',
//             success: function (data) {
//                 if (data.response_code == 1) {
//                     var thisForm = jQuery('#commonLPTChemicalForm');
//                     if (data.grn_data) {
//                         thisForm.find("#lpt_grnd_id").val(data.grn_data.grnd_id);
//                         thisForm.find('#lpt_chemical_id').val(data.grn_data.lpt_chemical_id).trigger('change.select2');
//                         thisForm.find("#grn_number").val(data.grn_data.grn_number);
//                         thisForm.find("#grn_date").val(data.grn_data.grn_date);
//                         thisForm.find("#supplier_name").val(data.grn_data.supplier_name);
//                         thisForm.find("#challan_number").val(data.grn_data.grn_challan_number);
//                         thisForm.find("#challan_date").val(data.grn_data.grn_challan_date);
//                     }
//                     jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
//                     jQuery('#PendingForGRNModal').find('#submitbtn').prop('disabled', false);
//                     jQuery("#PendingForGRNModal").modal('hide');
//                 } else {
//                     toastError(data.response_message);
//                 }
//             },
//             error: function (jqXHR) {
//                 if (jqXHR.status == 401) {
//                     toastError(jqXHR.statusText);
//                 } else {
//                     toastError('Something went wrong!');
//                     console.log(jqXHR.responseText);
//                 }
//             }
//         });
//     }
// });
// // old working code start