setSelect2Readonly('#commonEquipmentMPTForm #em_status', true);
var formId = jQuery('#EquipmentMPTModal').find('#commonEquipmentMPTForm').find('#id').val();
var equipment_mpt_details_data = [];
var headerOpt = {
    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
};

// Edit equipment mpt row click
jQuery('#dyntable tbody').on('click', '.edit-equipment_mpt', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#EquipmentMPTModal').find('#id').val(data["em_id"]);
    if (data && data["em_id"]) {
        fetchAndFillEquipmentMPT(data["em_id"]);
    }
});

// Function to fetch and fill material mpt data
function fetchAndFillEquipmentMPT(id) {
    if (!id) return;
    jQuery('#EquipmentMPTModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-equipment_mpt",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.equipment_mpt_data != null) {

                jQuery('#EquipmentMPTModal').find('#table_unique_id').val(data.equipment_mpt_data.table_unique_id);
                jQuery('#EquipmentMPTModal').find('#table_pk_id').val(data.equipment_mpt_data.table_pk_id);
                jQuery('#EquipmentMPTModal').find('#item_id').val(data.equipment_mpt_data.item_id);
                jQuery('#EquipmentMPTModal').find('#item_group').val(data.equipment_mpt_data.item_group);
                jQuery('#EquipmentMPTModal').find('#main_group').val(data.equipment_mpt_data.main_group);

                jQuery('#EquipmentMPTModal').find("#em_grn_number").val(data.equipment_mpt_data.grn_number != null ? data.equipment_mpt_data.grn_number : '');
                jQuery('#EquipmentMPTModal').find("#em_grn_date").val(data.equipment_mpt_data.grn_date != null ? data.equipment_mpt_data.grn_date : '');
                jQuery('#EquipmentMPTModal').find("#supplier").val(data.equipment_mpt_data.supplier_name);
                jQuery('#EquipmentMPTModal').find("#challan_no").val(data.equipment_mpt_data.grn_challan_number != null ? data.equipment_mpt_data.grn_challan_number : '');
                jQuery('#EquipmentMPTModal').find("#challan_date").val(data.equipment_mpt_data.grn_challan_date != null ? data.equipment_mpt_data.grn_challan_date : '');

                jQuery('#EquipmentMPTModal').find('#em_equipment_name').val(data.equipment_mpt_data.em_equipment_name != "" ? data.equipment_mpt_data.em_equipment_name : "");
                jQuery('#EquipmentMPTModal').find('#em_make').val(data.equipment_mpt_data.em_make != "" ? data.equipment_mpt_data.em_make : "");
                jQuery('#EquipmentMPTModal').find('#em_serial_no').val(data.equipment_mpt_data.em_serial_no != "" ? data.equipment_mpt_data.em_serial_no : "");

                jQuery('#EquipmentMPTModal').find('#em_last_cali_date').val(data.equipment_mpt_data.em_last_cali_date != "" ? zeroToEmpty(data.equipment_mpt_data.em_last_cali_date) : "");
                jQuery('#EquipmentMPTModal').find('#em_next_cali_due_date').val(data.equipment_mpt_data.em_next_cali_due_date != "" ? zeroToEmpty(data.equipment_mpt_data.em_next_cali_due_date) : "");
                jQuery('#EquipmentMPTModal').find('#ins_cali_freq').val(data.equipment_mpt_data.em_cali_freq != "" ? zeroToEmpty(data.equipment_mpt_data.em_cali_freq) : "");

                if (data.equipment_mpt_data.em_cali_certificate != "" && data.equipment_mpt_data.em_cali_certificate != null && data.equipment_mpt_data.em_cali_certificate != undefined) {
                    let fullPath = data.equipment_mpt_data.em_cali_certificate;
                    let fileName = fullPath.split('/').pop();
                    jQuery('#EquipmentMPTModal').find("#calibration_certificate_doc").val(fullPath);
                    jQuery('#EquipmentMPTModal').find('#calibration_certificate_prev').attr('href', uploadURL + fullPath).removeClass('hide');
                    jQuery('#EquipmentMPTModal').find('#calibration_certificate_remove').addClass('i-block').removeClass('hide');
                    let fileInput = jQuery('#EquipmentMPTModal').find('#calibration_certificate');
                    if (fileInput.length) {
                        let newFile = new DataTransfer();
                        newFile.items.add(new File([""], fileName));
                        fileInput[0].files = newFile.files;
                    }
                } else {
                    jQuery('#EquipmentMPTModal').find('#calibration_certificate_doc').val('');
                    jQuery('#EquipmentMPTModal').find('#calibration_certificate_prev').attr('href', '#').addClass('hide');
                    jQuery('#EquipmentMPTModal').find('#calibration_certificate_remove').removeClass('i-block').addClass('hide');
                    jQuery('#EquipmentMPTModal').find('#calibration_certificate').val('');
                }

                jQuery('#EquipmentMPTModal').find('#em_status').val(data.equipment_mpt_data.em_status != "" ? data.equipment_mpt_data.em_status : "Active").trigger("change.select2");

                jQuery('#EquipmentMPTModal').find('#id').val(data.equipment_mpt_data.em_id != "" ? data.equipment_mpt_data.em_id : "");
                if (Number(data.equipment_mpt_data.current_location_id) == Number(data.equipment_mpt_data.login_location)) {
                    jQuery("#submitbtn").prop('disabled', false);

                } else {
                    jQuery('#EquipmentMPTModal').find('#submitbtn').prop('disabled', true);
                }

                if (data.is_used) {
                    jQuery('#EquipmentMPTModal').find('#em_last_cali_date').prop('readonly', true).css('pointer-events', 'none').datepicker('disable');
                    jQuery('#EquipmentMPTModal').find('#em_next_cali_due_date').prop('readonly', true).css('pointer-events', 'none').datepicker('disable');
                    jQuery('#EquipmentMPTModal').find('#ins_cali_freq').prop('readonly', true);
                    jQuery('#EquipmentMPTModal').find('#calibration_certificate').prop('disabled', true);
                    jQuery('#EquipmentMPTModal').find('#calibration_certificate_remove').removeClass('i-block').addClass('hide');
                } else {
                    jQuery('#EquipmentMPTModal').find('#em_last_cali_date').prop('readonly', false).css('pointer-events', 'auto').datepicker('enable');
                    jQuery('#EquipmentMPTModal').find('#em_next_cali_due_date').prop('readonly', false).css('pointer-events', 'auto').datepicker('enable');
                    jQuery('#EquipmentMPTModal').find('#ins_cali_freq').prop('readonly', false);
                    jQuery('#EquipmentMPTModal').find('#calibration_certificate').prop('disabled', false);
                }


                // jQuery('#EquipmentMPTModal').find('#em_grn_number').val(data.equipment_mpt_data.grn_number != "" ? zeroToEmpty(data.equipment_mpt_data.grn_number) : "");
                // jQuery('#EquipmentMPTModal').find('#em_grn_date').val(data.equipment_mpt_data.grn_date != "" ? zeroToEmpty(data.equipment_mpt_data.grn_date) : "");
                // jQuery('#EquipmentMPTModal').find('#em_supplier').val(data.equipment_mpt_data.supplier_name != "" ? zeroToEmpty(data.equipment_mpt_data.supplier_name) : "");
                // jQuery('#EquipmentMPTModal').find('#em_challan_number').val(data.equipment_mpt_data.grn_challan_number != "" ? zeroToEmpty(data.equipment_mpt_data.grn_challan_number) : "");
                // jQuery('#EquipmentMPTModal').find('#em_challan_date').val(data.equipment_mpt_data.grn_challan_date != null ? zeroToEmpty(data.equipment_mpt_data.grn_challan_date) : "");

                // if (data.equipment_mpt_details_data != "" && data.equipment_mpt_details_data.length > 0) {
                //     equipment_mpt_details_data.push(...data.equipment_mpt_details_data);
                //     fillEquipmentMPTDetailsTable();
                // }
                // setSelect2Readonly("#em_inward_type", true);
                // setSelect2Readonly("#em_mpt_equipment_id", true);
                setSelect2Readonly("#em_status", true);
                jQuery('#EquipmentMPTModal').find('#pending_btn').prop('disabled', true);
                // jQuery('#EquipmentMPTModal').find('#submitbtn').prop('disabled', false);
                jQuery('#EquipmentMPTModal').find('#add_new').show();

                const form = document.getElementById("commonEquipmentMPTForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#EquipmentMPTModal').find('#em_equipment_name').focus();
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
    jQuery('#PendingForEquipmentMPTModal').find('#submitbtn').prop('disabled', false);
    equipment_mpt_details_data = [];
    jQuery('#EquipmentMPTDetailsTable tbody').empty();

    var formId = jQuery('#EquipmentMPTModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonEquipmentMPTForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        jQuery("#commonEquipmentMPTForm #id").val('');
        jQuery("#commonEquipmentMPTForm #table_pk_id").val('');
        jQuery("#commonEquipmentMPTForm #item_id").val('');
        jQuery("#commonEquipmentMPTForm #ins_cali_freq").val('');
        jQuery('#commonEquipmentMPTForm').find('#calibration_certificate_doc').val('');
        jQuery('#commonEquipmentMPTForm').find('#calibration_certificate_prev').attr('href', '#').addClass('hide');
        jQuery('#commonEquipmentMPTForm').find('#calibration_certificate_remove').removeClass('i-block').addClass('hide');
        jQuery('#commonEquipmentMPTForm').find('#calibration_certificate').val('');
        // jQuery("#commonEquipmentMPTForm #em_grnd_id").val(0);
        // jQuery("#commonEquipmentMPTForm #em_inward_type").val('').trigger("change.select2");
        // jQuery("#commonEquipmentMPTForm #em_mpt_equipment_id").val('').trigger("change.select2");
        // getLNRData();
        // InwardTypeChange();
        // setSelect2Readonly("#em_inward_type", false);
        // setSelect2Readonly("#em_mpt_equipment_id", false);
        // jQuery('#commonEquipmentMPTForm').find('#em_equipment_name').focus();
        jQuery("#commonEquipmentMPTForm #table_pk_id").val('');
        jQuery("#commonEquipmentMPTForm #em_status").val('Active').trigger("change.select2");
        jQuery('#commonEquipmentMPTForm').find('.toggleModalBtn').prop('disabled', true);
        resetCaliFieldsMPT();
        getPendingEquipmentMPT();
        focusPendingButton('#EquipmentMPTModal');
        // jQuery('#mpt_equipment_req').show();
        // setTimeout(function () {
        //     let sel = jQuery('#em_inward_type')
        //         .next('.select2-container')
        //         .find('.select2-selection');
        //     sel.attr('tabindex', 0).focus();
        // }, 20);

        jQuery('#commonEquipmentMPTForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
        jQuery('#commonEquipmentMPTForm').find('#em_grn_number').prop({ tabindex: -1, readonly: true });
        jQuery('#commonEquipmentMPTForm').find('#em_grn_date').prop({ tabindex: -1, readonly: true });
        jQuery('#commonEquipmentMPTForm').find('#supplier').prop({ tabindex: -1, readonly: true });
        jQuery('#commonEquipmentMPTForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
        jQuery('#commonEquipmentMPTForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
        jQuery('#commonEquipmentMPTForm').find('#item_group').prop({ tabindex: -1, readonly: true });
        jQuery('#commonEquipmentMPTForm').find('#main_group').prop({ tabindex: -1, readonly: true });

        // setTimeout(() => {
        //     const btn = jQuery('#EquipmentMPTModal').find('.toggleModalBtn').filter(':visible').not(':disabled').first();
        //     if (btn.length > 0) {
        //         btn.trigger('focus');
        //         btn.css({'border-color': '#22b378','outline-offset': '2px','box-shadow': '0 0 0 0.25rem rgba(34, 179, 120, 0.25)'});
        //         btn.one('blur', function () {
        //             jQuery(this).css({'border-color': '','outline-offset': '','box-shadow': ''});
        //         });
        //     }
        // }, 1500);
    } else {
        fetchAndFillEquipmentMPT(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit-equipment_mpt', function () {

//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#EquipmentMPTModal').find('#id').val(data["em_id"]);
//     jQuery('#EquipmentMPTModal').modal('show');
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-equipment_mpt",
//         type: 'GET',
//         data: "id=" + data["em_id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {

//                 jQuery('#EquipmentMPTModal').find('#em_inward_type').val(data.equipment_mpt_data.em_inward_type != "" ? data.equipment_mpt_data.em_inward_type : "").trigger("change.select2");

//                 //  if(data.items != ""){
//                 //     $('#em_mpt_equipment_id').html('<option value="">Select MPT Equipment</option>');
//                 //     data.items.forEach(function(item) {
//                 //         $('#em_mpt_equipment_id').append(
//                 //             `<option value="${item.id}">${item.item_name}</option>`
//                 //         );
//                 //     });
//                 // }


//                 jQuery('#EquipmentMPTModal').find('#em_mpt_equipment_id').val(data.equipment_mpt_data.em_mpt_equipment_id != "" ? data.equipment_mpt_data.em_mpt_equipment_id : "").trigger('change.select2');
//                 jQuery('#EquipmentMPTModal').find('#em_equipment_name').val(data.equipment_mpt_data.em_equipment_name != "" ? data.equipment_mpt_data.em_equipment_name : "");
//                 jQuery('#EquipmentMPTModal').find('#em_make').val(data.equipment_mpt_data.em_make != "" ? data.equipment_mpt_data.em_make : "");
//                 jQuery('#EquipmentMPTModal').find('#em_serial_no').val(data.equipment_mpt_data.em_serial_no != "" ? data.equipment_mpt_data.em_serial_no : "");
//                 jQuery('#EquipmentMPTModal').find('#em_method_of_magnetization_by').val(data.equipment_mpt_data.em_method_of_magnetization_by != "" ? data.equipment_mpt_data.em_method_of_magnetization_by : "");
//                 jQuery('#EquipmentMPTModal').find('#em_type_of_magnetization').val(data.equipment_mpt_data.em_type_of_magnetization != "" ? data.equipment_mpt_data.em_type_of_magnetization : "");
//                 jQuery('#EquipmentMPTModal').find('#em_type_of_current').val(data.equipment_mpt_data.em_type_of_current != "" ? data.equipment_mpt_data.em_type_of_current : "");
//                 jQuery('#EquipmentMPTModal').find('#em_grn_number').val(data.equipment_mpt_data.grn_number != "" ? zeroToEmpty(data.equipment_mpt_data.grn_number) : "");
//                 jQuery('#EquipmentMPTModal').find('#em_grn_date').val(data.equipment_mpt_data.grn_date != "" ? zeroToEmpty(data.equipment_mpt_data.grn_date) : "");
//                 jQuery('#EquipmentMPTModal').find('#em_supplier').val(data.equipment_mpt_data.supplier_name != "" ? zeroToEmpty(data.equipment_mpt_data.supplier_name) : "");
//                 jQuery('#EquipmentMPTModal').find('#em_challan_number').val(data.equipment_mpt_data.grn_challan_number != "" ? zeroToEmpty(data.equipment_mpt_data.grn_challan_number) : "");
//                 jQuery('#EquipmentMPTModal').find('#em_challan_date').val(data.equipment_mpt_data.grn_challan_date != null ? zeroToEmpty(data.equipment_mpt_data.grn_challan_date) : "");
//                 jQuery('#EquipmentMPTModal').find('#em_doc_ref_no').val(data.equipment_mpt_data.em_doc_ref_no != "" ? data.equipment_mpt_data.em_doc_ref_no : "");
//                 jQuery('#EquipmentMPTModal').find('#em_remark').val(data.equipment_mpt_data.em_remark != "" ? data.equipment_mpt_data.em_remark : "");
//                 jQuery('#EquipmentMPTModal').find('#em_status').val(data.equipment_mpt_data.em_status != "" ? data.equipment_mpt_data.em_status : "Active").trigger("change.select2");
//                 jQuery('#EquipmentMPTModal').find('#em_grnd_id').val(data.equipment_mpt_data.em_grnd_id != "" ? data.equipment_mpt_data.em_grnd_id : "");

//                if(data.equipment_mpt_details_data != "" &&  data.equipment_mpt_details_data.length > 0){
//                     equipment_mpt_details_data.push(...data.equipment_mpt_details_data);
//                     fillEquipmentMPTDetailsTable();
//                 }
//                 setSelect2Readonly("#em_inward_type",true);
//                 setSelect2Readonly("#em_mpt_equipment_id",true);
//                 jQuery('#EquipmentMPTModal').find('#pending_btn').prop('disabled', true);
//                 jQuery('#EquipmentMPTModal').find('#submitbtn').prop('disabled', false);
//                 jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');

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

$('#EquipmentMPTModal').on('show.bs.modal', function () {
    setTimeout(() => {
        var formIdblank = jQuery('#commonEquipmentMPTForm').find('input[name="id"]').val();
        setSelect2Readonly('#commonEquipmentMPTForm #em_status', true);
        if (formIdblank == "" || formIdblank == undefined) {
            getPendingEquipmentMPT();
            // getLNRData();
            jQuery('#commonEquipmentMPTForm').find('#em_status').val('Active').trigger('change.select2');
            focusPendingButton('#EquipmentMPTModal');

            jQuery('#commonEquipmentMPTForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
            jQuery('#commonEquipmentMPTForm').find('#em_grn_number').prop({ tabindex: -1, readonly: true });
            jQuery('#commonEquipmentMPTForm').find('#em_grn_date').prop({ tabindex: -1, readonly: true });
            jQuery('#commonEquipmentMPTForm').find('#supplier').prop({ tabindex: -1, readonly: true });
            jQuery('#commonEquipmentMPTForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
            jQuery('#commonEquipmentMPTForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
            jQuery('#commonEquipmentMPTForm').find('#item_group').prop({ tabindex: -1, readonly: true });
            jQuery('#commonEquipmentMPTForm').find('#main_group').prop({ tabindex: -1, readonly: true });
        } else {
            // setSelect2Readonly('#commonEquipmentMPTForm #em_inward_type', true);
            // setSelect2Readonly('#commonEquipmentMPTForm #em_mpt_equipment_id', true);
            jQuery('#commonEquipmentMPTForm').find('#pending_btn').prop('disabled', true);

        }

        if (formIdblank && formIdblank !== "") {
            jQuery('#EquipmentMPTModal').find('#add_new').show();
        } else {
            jQuery('#EquipmentMPTModal').find('#add_new').hide();
        }
    }, 150);
});

// jQuery('#EquipmentMPTModal').on('shown.bs.modal', function () {
//     var formId = jQuery('#commonEquipmentMPTForm').find('input[name="id"]').val();
//     var hasAccess = jQuery('#commonEquipmentMPTForm').find('#has_access').val();
//     var em_inward_type = jQuery('#commonEquipmentMPTForm').find('#em_inward_type').val();
//     if (formId == "" || formId == undefined) {
//         getLNRData();
//         jQuery('#EquipmentMPTModal').find('#em_status').val('Active').trigger("change.select2");
//         setSelect2Readonly("#em_inward_type", false);
//         setSelect2Readonly("#em_mpt_equipment_id", false);
//     }
//     if (em_inward_type == "From GRN") {
//         jQuery('#EquipmentMPTModal').find('#pending_btn').attr('disabled', true);

//     } else {
//         jQuery('#EquipmentMPTModal').find('#pending_btn').attr('disabled', false);
//     }



// });

jQuery('#EquipmentMPTModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#EquipmentMPTModal');
    formId = jQuery('#commonEquipmentMPTForm').find('input[name="id"]').val('');
    jQuery("#commonEquipmentMPTForm #item_id").val('');
    jQuery("#commonEquipmentMPTForm #table_pk_id").val('');

    document.getElementById("commonEquipmentMPTForm").reset();
    clearCertificate();
    // equipment_mpt_details_data = [];
    // jQuery('#EquipmentMPTDetailsTable tbody').empty();
    // thisForm.find('input, textarea').each(function () {
    //     jQuery(this).val('');
    //     jQuery(this).prop('checked', false);
    // });
    // window.location.reload();
    setSelect2Readonly('#commonEquipmentMPTForm #em_status', true);
    // setSelect2Readonly('#commonEquipmentMPTForm #em_inward_type', false);
    // setSelect2Readonly('#commonEquipmentMPTForm #em_mpt_equipment_id', false);
    jQuery('#EquipmentMPTModal').find('#add_new').hide();
});

jQuery('#EquipmentMPTModal').on('click', '#add_new', function () {
    jQuery('#EquipmentMPTModal').find('#id').val('');
    document.getElementById("commonEquipmentMPTForm").reset();
    clearCertificate();
    const form = document.getElementById("commonEquipmentMPTForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    // jQuery('#EquipmentMPTModal').find('#em_equipment_name').focus();
    equipment_mpt_details_data = [];
    jQuery('#EquipmentMPTDetailsTable tbody').empty();
    jQuery("#commonEquipmentMPTForm #table_pk_id").val('');
    jQuery("#commonEquipmentMPTForm #id").val('');
    jQuery("#commonEquipmentMPTForm #item_id").val('');
    jQuery("#commonEquipmentMPTForm #em_status").val('Active').trigger("change.select2");
    jQuery('#commonEquipmentMPTForm').find('.toggleModalBtn').prop('disabled', true);
    getPendingEquipmentMPT();
    jQuery('#commonEquipmentMPTForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
    jQuery('#commonEquipmentMPTForm').find('#em_grn_number').prop({ tabindex: -1, readonly: true });
    jQuery('#commonEquipmentMPTForm').find('#em_grn_date').prop({ tabindex: -1, readonly: true });
    jQuery('#commonEquipmentMPTForm').find('#supplier').prop({ tabindex: -1, readonly: true });
    jQuery('#commonEquipmentMPTForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
    jQuery('#commonEquipmentMPTForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
    jQuery('#commonEquipmentMPTForm').find('#item_group').prop({ tabindex: -1, readonly: true });
    jQuery('#commonEquipmentMPTForm').find('#main_group').prop({ tabindex: -1, readonly: true });
    focusPendingButton('#EquipmentMPTModal');
    jQuery('#submitbtn').prop('disabled', false);
    resetCaliFieldsMPT();
    jQuery("#commonEquipmentMPTForm #em_status").val('Active').trigger("change.select2");
    // setTimeout(() => {
    //     const btn = jQuery('#EquipmentMPTModal').find('.toggleModalBtn').filter(':visible').not(':disabled').first();
    //     if (btn.length > 0) {
    //         btn.trigger('focus');
    //         btn.css({'border-color': '#22b378','outline-offset': '2px','box-shadow': '0 0 0 0.25rem rgba(34, 179, 120, 0.25)'});
    //         btn.one('blur', function () {
    //             jQuery(this).css({'border-color': '','outline-offset': '','box-shadow': ''});
    //         });
    //     }
    // }, 1500);

    jQuery('#EquipmentMPTModal').find('#add_new').hide();
});

// jQuery('#EquipmentMPTDetailsModal').on('shown.bs.modal', function () {
//     var date = jQuery('#EquipmentMPTDetailsModal').find('#emd_calibration_due_date');
//     if (date.hasClass('date-picker')) {
//         setTimeout(() => {
//             date.datepicker('hide');
//         }, 800);
//     }
// });



// jQuery('#EquipmentMPTDetailsModal').on('hide.bs.modal', function (e) {
//     let thisModal = jQuery('#EquipmentMPTDetailsModal');
//     thisModal.find("#form_type").val("add");
//     thisModal.find("#form_index").val("");
//     thisModal.find("#row_index").val("");
//     thisModal.find("#emd_id").val(0);
//     jQuery('#EquipmentMPTDetailsForm').trigger("reset");

//     setTimeout(function () {
//         let sel = jQuery('#em_status')
//             .next('.select2-container')
//             .find('.select2-selection');
//         sel.attr('tabindex', 0).focus();
//     }, 20);

// });

jQuery('#PendingForEquipmentMPTModal').on('show.bs.modal', function (e) {
    var dt = jQuery('#pendingEquipmentMPTDataTable').DataTable();
    fixDataTableColumnsUntilAdjusted(dt);
    var usedParts = [];
    var table_pk_id = jQuery('#EquipmentMPTModal').find('#table_pk_id').val();
    if (table_pk_id != "" && table_pk_id != null) {
        usedParts.push(Number(table_pk_id));
    }
    function isUsed(pjId) {
        if (usedParts.includes(Number(pjId))) {
            return true;
        }
        return false;
    }

    jQuery('#pendingEquipmentMPTDataTable tbody tr').each(function (indx) {

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

jQuery('#PendingForEquipmentMPTModal').on('hide.bs.modal', function (e) {

    this.dataset.customHideFocus = 'true';
    const input = jQuery('#table_pk_id').val() != '' ? document.getElementById('em_equipment_name') : document.getElementById('pending_btn');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }
});



// Equipment UT Details Form Submit Start
// $('#EquipmentMPTDetailsForm').on('submit', function (e) {

//     e.preventDefault();
//     let form = this;
//     var dateValue = document.getElementById("emd_calibration_due_date").value.trim();
//     if (!isValidDate(dateValue)) {
//         toastr.error("Please Enter A Valid Date!");
//         jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
//         jQuery('#EquipmentMPTDetailsModal').find('#submitbtn').prop('disabled', false);
//         return;
//     }

//     if (!form.checkValidity()) {
//         e.stopPropagation();
//         $(form).addClass('was-validated');
//         return;
//     }


//     var data = new FormData(document.getElementById('EquipmentMPTDetailsForm'));
//     var formValue = Object.fromEntries(data.entries());
//     var thisModal = jQuery('#EquipmentMPTDetailsModal');

//     let newDate = parseDate(formValue.emd_calibration_due_date);

//     let total = equipment_mpt_details_data.length;

//     /* ADD MODE */
//     if (total > 0 && formValue.form_type != "edit") {

//         let lastDateStr = equipment_mpt_details_data[total - 1].emd_calibration_due_date;
//         let lastDate = parseDate(lastDateStr);

//         if (newDate <= lastDate) {
//             toastr.error(
//                 "Calibration Due On Must Be Greather then to Last Calibration Due Date"
//             );
//             return;
//         }
//     }

//     /*  EDIT MODE  */
//     if (formValue.form_type == "edit") {

//         let idx = formValue.form_index;
//         if (idx > 0) {
//             let prevDateStr = equipment_mpt_details_data[idx - 1].emd_calibration_due_date;
//             let prevDate = parseDate(prevDateStr);
//             if (newDate <= prevDate) {
//                 toastr.error(
//                     "Calibration Due On Must Be Greather then to Last Calibration Due Date"
//                 );
//                 return;
//             }
//         }
//         if (idx < total - 1) {
//             let nextDateStr = equipment_mpt_details_data[idx + 1].emd_calibration_due_date;
//             let nextDate = parseDate(nextDateStr);
//             if (newDate >= nextDate) {
//                 toastr.error(
//                     "Calibration Due On Must Be Greather then to Last Calibration Due Date"
//                 );
//                 return;
//             }
//         }
//     }
//     if (formValue.emd_calibration_due_date.trim()) {
//         var noDuplicate = true;
//         if (noDuplicate) {
//             var emd_calibration_due_date = formValue.emd_calibration_due_date != null ? formValue.emd_calibration_due_date : "";

//             if (emd_calibration_due_date != "") {
//                 if (formValue.form_type == "edit") {
//                     equipment_mpt_details_data[formValue.form_index] = formValue;
//                     let tblHtml = ``;
//                     tblHtml += `<td>`;
//                     tblHtml += DetailsActionDropdown('editEquipmentMPTDetails', 'removeEquipmentMPTDetails');
//                     tblHtml += `<input type="hidden" name="form_indx" value="${formValue.form_index}"/>
//                     </td>`;
//                     // tblHtml += `<td>
//                     //     <div>
//                     //         <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
//                     //             <i class="ri-more-2-fill"></i>
//                     //         </a>
//                     //         <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
//                     //             <li><a class="dropdown-item edit-item-btn" onclick="editEquipmentMPTDetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i>Edit</a></li>
//                     //             <li><a class="dropdown-item remove-item-btn" onclick="removeEquipmentMPTDetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li>
//                     //         </ul>
//                     //     </div>
//                     //     <input type="hidden" name="form_indx" value="${formValue.form_index}"/>
//                     // </td>`;
//                     tblHtml += `<td>${emd_calibration_due_date}</td>`;
//                     tblHtml += `</tr>`;
//                     jQuery('#EquipmentMPTDetailsTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
//                     toastSuccess("Record is updated successfully.");
//                 } else {
//                     equipment_mpt_details_data.push(formValue)
//                     let formIndx = equipment_mpt_details_data.indexOf(formValue);
//                     if (jQuery('#EquipmentMPTDetailsTable tbody').find('#noDetails').length > 0) {
//                         jQuery('#EquipmentMPTDetailsTable tbody').empty();
//                     }

//                     let tblHtml = `<tr>`;
//                     tblHtml += `<td>`;
//                     tblHtml += DetailsActionDropdown('editEquipmentMPTDetails', 'removeEquipmentMPTDetails');
//                     tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
//                     </td>`;
//                     // tblHtml += `<td>
//                     //     <div>
//                     //         <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
//                     //             <i class="ri-more-2-fill"></i>
//                     //         </a>
//                     //         <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
//                     //             <li><a class="dropdown-item edit-item-btn" onclick="editEquipmentMPTDetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i>Edit</a></li>
//                     //             <li><a class="dropdown-item remove-item-btn" onclick="removeEquipmentMPTDetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li>
//                     //         </ul>
//                     //     </div>
//                     //     <input type="hidden" name="form_indx" value="${formIndx}"/>
//                     // </td>`;
//                     tblHtml += `<td>${emd_calibration_due_date}</td>`;
//                     tblHtml += `</tr>`;
//                     jQuery('#EquipmentMPTDetailsTable tbody').append(tblHtml);
//                     toastSuccess("Record is inserted successfully.");
//                 }
//             }
//             if (formValue.form_type == "edit") {
//                 thisModal.modal('hide');
//             } else {
//                 let formElement = document.getElementById('EquipmentMPTDetailsForm');
//                 formElement.reset();
//                 jQuery('#emd_id').val('');
//                 jQuery('#emd_calibration_due_date').val('');
//                 setTimeout(function () {
//                     const $frDate = jQuery('#EquipmentMPTDetailsForm #emd_calibration_due_date');
//                     $frDate.trigger('focus');
//                     setTimeout(function () {
//                         if ($frDate.hasClass('date-picker')) {
//                             $frDate.datepicker('hide');
//                         }
//                     }, 0);
//                 }, 150);
//                 setTimeout(function () {
//                     $(formElement).removeClass('was-validated');
//                     $(formElement).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
//                     thisModal.find('.error').removeClass('error');
//                 }, 150);
//             };
//         }
//     } else {
//         toastr.error('Please Enter Calibration Due Date');
//     }
// });

// function editEquipmentMPTDetails(th) {
//     let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
//     let rawIndx = jQuery(th).closest('tr').index();
//     equipment_mpt_details_data = equipment_mpt_details_data.filter(item => item !== undefined && item !== null && item !== '');

//     let arrayLength = equipment_mpt_details_data.length - 1;

//     if (arrayLength == formIndx) {
//         fillEquipmentMPTDetailsForm(formIndx, rawIndx);
//     }
//     else if (formIndx != undefined) {
//         toastr.error("You Can Update Only Last Calibration");
//         return false;
//     }
// }

// // purchase order details form edit
// function fillEquipmentMPTDetailsForm(formIndx, rawIndx) {
//     let thisForm = jQuery('#EquipmentMPTDetailsModal');
//     thisForm.find("#form_type").val("edit");
//     thisForm.find("#form_index").val(formIndx);
//     thisForm.find("#row_index").val(rawIndx);
//     var frmData = equipment_mpt_details_data[formIndx];
//     thisForm.find("#emd_id").val(frmData.emd_id);
//     thisForm.find("#emd_calibration_due_date").val(zeroToEmpty(frmData.emd_calibration_due_date != "" ? frmData.emd_calibration_due_date : ""));
//     thisForm.modal('show');
// }

// function removeEquipmentMPTDetails(th) {
//     toastDetailDelete("Do you want to delete this record?", () => {
//         let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
//         equipment_mpt_details_data = equipment_mpt_details_data.filter(item => item !== undefined && item !== null && item !== '');
//         let arrayLength = equipment_mpt_details_data.length - 1;

//         if (arrayLength == formIndx) {
//             removeFormObj(formIndx);
//             jQuery(th).closest("tr").remove();

//         }
//         else if (formIndx != undefined) {
//             toastr.error("You Can Delete Only Last Calibration");
//             return false;
//         }

//         let editRecord = jQuery(th).find('.edit-detail');
//         editEquipmentMPTDetails(editRecord);
//     });
// }

// function removeFormObj(formIndx) {
//     delete equipment_mpt_details_data[formIndx];
//     equipment_mpt_details_data = equipment_mpt_details_data.filter(element => element != null);
//     jQuery('#EquipmentMPTDetailsTable tbody').empty();
//     fillEquipmentMPTDetailsTable();
// }


// // edit time fill table start
// function fillEquipmentMPTDetailsTable() {
//     let thisModal = jQuery('#EquipmentMPTDetailsModal');
//     if (equipment_mpt_details_data.length > 0) {
//         for (let key in equipment_mpt_details_data) {
//             let formIndx = equipment_mpt_details_data.indexOf(equipment_mpt_details_data[key]);
//             var emd_calibration_due_date = equipment_mpt_details_data[key].emd_calibration_due_date != "" ? equipment_mpt_details_data[key].emd_calibration_due_date : '';

//             if (jQuery('#EquipmentMPTDetailsTable tbody').find('#noDetails').length > 0) {
//                 jQuery('#EquipmentMPTDetailsTable tbody').empty();
//             }
//             let tblHtml = `<tr>`;
//             tblHtml += `<td>`;
//             tblHtml += DetailsActionDropdown('editEquipmentMPTDetails', 'removeEquipmentMPTDetails');
//             tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
//             </td>`;
//             // tblHtml += `<td>         
//             //     <div>
//             //         <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
//             //             <i class="ri-more-2-fill"></i>
//             //         </a>
//             //         <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
//             //             <li><a class="dropdown-item edit-item-btn" onclick="editEquipmentMPTDetails(this)"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i>Edit</a></li>
//             //             <li><a class="dropdown-item remove-item-btn" onclick="removeEquipmentMPTDetails(this)"> <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i>Delete</a></li>
//             //         </ul>
//             //     </div>
//             //     <input type="hidden" name="form_indx" value="${formIndx}"/>
//             // </td>`;
//             tblHtml += `<td>${emd_calibration_due_date}</td>`;
//             tblHtml += `</tr>`;
//             jQuery('#EquipmentMPTDetailsTable tbody').append(tblHtml);
//         }
//     }
// }

// get Last Reset Data
// function getLNRData() {
//     jQuery.ajax({
//         url: "get-equipment_mpt_lnr_data",
//         type: 'GET',
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 if (data.lnr_data != null) {

//                     jQuery('#em_inward_type').val(data.lnr_data.em_inward_type).trigger("change");
//                 }
//             } else {
//                 console.log(data.response_message)
//             }
//         },
//     });
// }

// // $('#em_inward_type').on('change', function () {
// //     InwardTypeChange();
// // });

// function InwardTypeChange() {
//     let inwardType = jQuery('#em_inward_type').val();
//     if (inwardType == 'From GRN') {
//         setSelect2Readonly('#em_mpt_equipment_id', true);
//         setSelect2Required('#em_mpt_equipment_id', false);
//         jQuery('#mpt_equipment_req').hide();
//         jQuery('#pending_btn').prop('disabled', false);
//         getPendingEquipmentMPT();
//     } else if (inwardType == 'Manual') {
//         setSelect2Readonly('#em_mpt_equipment_id', false);
//         setSelect2Required('#em_mpt_equipment_id', true);
//         jQuery('#mpt_equipment_req').show();
//         jQuery('#pending_btn').prop('disabled', true);
//     } else {
//         setSelect2Readonly('#em_mpt_equipment_id', false);
//         jQuery('#mpt_equipment_req').show();
//         jQuery('#pending_btn').prop('disabled', false);
//     }
// }

// jQuery('#EquipmentMPTModal').on('change', '#em_inward_type', function () {
//     if (this.value == 'From GRN') {
//         setSelect2Readonly('#em_mpt_equipment_id', true);
//         jQuery('#em_mpt_equipment_id').val('').trigger("change.select2").attr('tabindex', '-1');
//         setSelect2Required('#em_mpt_equipment_id', false);
//         getPendingEquipmentMPT();
//         jQuery('#pending_btn').prop('disabled', false);
//         jQuery('#mpt_equipment_req').hide();
//     }
//     else {
//         setSelect2Readonly('#em_mpt_equipment_id', false);
//         jQuery('#em_mpt_equipment_id').val('').trigger("change.select2").removeClass('skip-tab').attr('tabindex', '0');
//         setSelect2Required('#em_mpt_equipment_id', true);
//         jQuery('#pending_btn').prop('disabled', true);
//         jQuery('#mpt_equipment_req').show();


//     }
// });



$('#commonEquipmentMPTForm').on('submit', function (e) {

    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#EquipmentMPTModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#EquipmentMPTModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var lastCalDate = jQuery('#em_last_cali_date').val();
    var nextCalDueDate = jQuery('#em_next_cali_due_date').val();

    let lastDate = parseDate(lastCalDate);
    let nextDate = parseDate(nextCalDueDate);

    if (nextDate < lastDate) {
        toastr.error('Next Calibration Due Date must be greater than Last Calibration Date.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#EquipmentMPTModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    var tablePKId = jQuery('#table_pk_id').val();
    if (!tablePKId) {
        toastr.error('Select At Least One Pending.');
        // toastr.error('Select Atleast One Pending.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#EquipmentMPTModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#EquipmentMPTModal').find('#commonEquipmentMPTForm').find('#id').val();

    var formUrl = formId != undefined && formId != "" ? "update-equipment_mpt" : "store-equipment_mpt";

    var em_make = jQuery('#em_make').val();
    var em_serial_no = jQuery('#em_serial_no').val();
    var em_equipment_name = jQuery('#em_equipment_name').val();
    if (em_make != '' && em_serial_no != "") {
        MPTEquipmentUrl = formId != undefined && formId != "" ? "verify-equipment_mpt?em_make=" + encodeURIComponent(em_make) + "&em_serial_no=" + encodeURIComponent(em_serial_no) + "&em_equipment_name=" + encodeURIComponent(em_equipment_name) + "&em_id=" + formId : "verify-equipment_mpt?em_make=" + encodeURIComponent(em_make) + "&em_serial_no=" + encodeURIComponent(em_serial_no) + "&em_equipment_name=" + encodeURIComponent(em_equipment_name);
    }

    var data = new FormData(this);
    // data.append('equipment_mpt_details_data', JSON.stringify(equipment_mpt_details_data ?? []));
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));





    // if ((pending_grn_id != undefined && pending_grn_id != "") && (qu_inward_type == "From GRN") ) {
    // if (equipment_mpt_details_data.length > 0 && !jQuery.isEmptyObject(equipment_mpt_details_data)) {
    // if ((em_grnd_id != 0 || em_grnd_id != null) && (em_inward_type != "From GRN") ) {

    // if (em_grnd_id == 0 && em_inward_type === "From GRN") {
    //      toastr.error('Please Select At Least One GRN From Pending');
    //     jQuery('#full-page-loader')
    //         .removeClass('loader-progress-whole-page')
    //         .addClass('hidden-loader');
    //     jQuery('#EquipmentMPTModal')
    //         .find('#submitbtn')
    //         .prop('disabled', false);
    //     return;
    // }


    if ((em_equipment_name != '' && em_equipment_name != undefined) && (em_serial_no != "" && em_serial_no != undefined)) {
        $.ajax({
            url: MPTEquipmentUrl,
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.response_code == 1) {
                    toastr.error(res.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#EquipmentMPTModal').find('#submitbtn').prop('disabled', false);
                } else {

                    $.ajax({
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
                                    jQuery('#EquipmentMPTModal').find('#submitbtn').prop('disabled', false);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        jQuery("#commonEquipmentMPTForm #id").val('');
                                        jQuery("#commonEquipmentMPTForm #table_pk_id").val('');
                                        jQuery("#commonEquipmentMPTForm #item_id").val('');
                                        // jQuery("#commonEquipmentMPTForm #em_grnd_id").val(0);
                                        // jQuery("#commonEquipmentMPTForm #em_inward_type").val('').trigger("change.select2");
                                        // jQuery("#commonEquipmentMPTForm #em_mpt_equipment_id").val('').trigger("change.select2");
                                        jQuery("#commonEquipmentMPTForm #em_status").val('Active').trigger("change.select2");
                                        setSelect2Readonly("#em_status", true);
                                        getPendingEquipmentMPT();
                                        jQuery('#em_equipment_name').focus();
                                        // equipment_mpt_details_data = [];
                                        // jQuery('#EquipmentMPTDetailsTable tbody').empty();
                                        // getLNRData();
                                        // setSelect2Readonly("#em_inward_type", false);
                                        // setTimeout(function () {
                                        //     let sel = jQuery('#em_inward_type')
                                        //         .next('.select2-container')
                                        //         .find('.select2-selection');
                                        //     sel.attr('tabindex', 0).focus();
                                        // }, 20);

                                        jQuery('#commonEquipmentMPTForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonEquipmentMPTForm').find('#em_grn_number').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonEquipmentMPTForm').find('#em_grn_date').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonEquipmentMPTForm').find('#supplier').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonEquipmentMPTForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonEquipmentMPTForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonEquipmentMPTForm').find('#item_group').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonEquipmentMPTForm').find('#main_group').prop({ tabindex: -1, readonly: true });
                                        focusPendingButton('#EquipmentMPTModal');
                                        document.getElementById("commonEquipmentMPTForm").reset();
                                        clearCertificate();
                                        const form = document.getElementById("commonEquipmentMPTForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }
                                        jQuery('#PendingForEquipmentMPTModal').find('#submitbtn').prop('disabled', false);

                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    // setTimeout(() => {
                                    //     const btn = jQuery('#EquipmentMPTModal').find('.toggleModalBtn').filter(':visible').not(':disabled').first();
                                    //     if (btn.length > 0) {
                                    //         btn.trigger('focus');
                                    //         btn.css({'border-color': '#22b378','outline-offset': '2px','box-shadow': '0 0 0 0.25rem rgba(34, 179, 120, 0.25)'});
                                    //         btn.one('blur', function () {
                                    //             jQuery(this).css({'border-color': '','outline-offset': '','box-shadow': ''});
                                    //         });
                                    //     }
                                    // }, 1500);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#EquipmentMPTModal').find('#submitbtn').prop('disabled', false);
                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#EquipmentMPTModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#EquipmentMPTModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#EquipmentMPTModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#EquipmentMPTModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#EquipmentMPTModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }

    // } else {
    //      toastr.error('Please Add At Least One Equipment MPT Details.');
    //     jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
    //     jQuery('#EquipmentMPTModal').find('#submitbtn').prop('disabled', false);
    // }


});


// get pending equipment ut from grn

function getPendingEquipmentMPT() {
    // var em_inward_type = jQuery('#em_inward_type option:selected').val();
    var thisForm = jQuery('#addPendigEquipmentMPTForm');

    jQuery.ajax({
        url: "get-pending_grn_list_for_equipment_mpt",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                if (data.response_code == 1 && data.opening_data.length > 0) {
                    var formIdblank = jQuery('#commonEquipmentMPTForm').find('#id').val();
                    if (formIdblank != "" && formIdblank != undefined) {
                        jQuery('#pending_btn').prop('disabled', true);
                    } else {
                        jQuery('#pending_btn').prop('disabled', false);
                    }
                    // new code
                    var usedParts = [];
                    var totalDisb = 0;
                    var found = 0;

                    thisForm.find('#pendingEquipmentMPTDataTable tbody input[name="form_indx"]').each(function (indx) {
                        let frmIndx = jQuery(this).val();

                        let jbEorkOrderId = opening_data[frmIndx].grnd_id;
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
                    if (data.opening_data.length > 0 && !jQuery.isEmptyObject(data.opening_data)) {
                        found = 1;
                        var formIdblank = jQuery('#commonEquipmentMPTForm').find('#id').val();
                        if (formIdblank != "" && formIdblank != undefined) {
                            jQuery('#pending_btn').prop('disabled', true);
                        } else {
                            jQuery('#pending_btn').prop('disabled', false);
                        }
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
                                        <td colspan="9">No Pending Available</td>
                                    </tr>`;
                        jQuery('.toggleModalBtn').prop('disabled', true);
                    }

                    var $table = jQuery("#PendingForEquipmentMPTModal").find('#pendingEquipmentMPTDataTable');
                    if (jQuery.fn.DataTable.isDataTable($table)) {
                        $table.DataTable().clear().destroy();
                    }
                    jQuery('#pendingEquipmentMPTDataTable tbody').empty().append(tblHtml);

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

                    // if (em_inward_type == 'Manual') {
                    //     jQuery('.toggleModalBtn').prop('disabled', true);
                    // } else {
                    //     jQuery('.toggleModalBtn').prop('disabled', false);
                    // }

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

// get selected pending GRN
$('#addPendigEquipmentMPTForm').on('submit', function (e) {

    let chkArr = [];
    // e.preventDefault();

    // jQuery('#full-page-loader')
    //     .removeClass('hidden-loader')
    //     .addClass('loader-progress-whole-page');

    // // jQuery('#PendingForEquipmentMPTModal')
    // //     .find('#submitbtn')
    // //     .prop('disabled', true);


    // jQuery("#addPendigEquipmentMPTForm")
    //     .find("[id^='grnd_ids_']:checked")
    //     .each(function () {
    //         chkArr.push(jQuery(this).val());
    //     });

    // // No checkbox selected
    // if (chkArr.length === 0) {
    //      toastr.error('Select GRN From Pending');

    //     jQuery('#full-page-loader')
    //         .removeClass('loader-progress-whole-page')
    //         .addClass('hidden-loader');

    //     jQuery('#PendingForEquipmentMPTModal')
    //         .find('#submitbtn')
    //         .prop('disabled', false);

    //     return;
    // }

    e.preventDefault();
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#PendingForEquipmentMPTModal').find('#submitbtn').prop('disabled', true);

    let uniqueArr = [];
    jQuery("#addPendigEquipmentMPTForm").find("[id^='table_pk_ids_']:checked")
        .each(function () {
            chkArr.push(jQuery(this).val());
            uniqueArr.push(jQuery(this).data('table_unique_id'));
        });

    if (chkArr.length === 0) {
        toastr.error('Select At Least One Pending.');
        //  toastr.error('Please Select Atleast One GRN From Pending.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PendingForEquipmentMPTModal').find('#submitbtn').prop('disabled', false);
        return;
    }


    jQuery.ajax({
        url: 'get-pending_grn_for_equipment_mpt',
        type: 'GET',
        data: {
            table_pk_ids: chkArr.join(','),
            table_unique_ids: uniqueArr.join(',')
        },
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                if (data.opening_data) {

                    jQuery('#EquipmentMPTModal').find("#table_pk_id").val(data.opening_data.table_pk_id);
                    jQuery('#EquipmentMPTModal').find('#item_group').val(data.opening_data.item_group);
                    jQuery('#EquipmentMPTModal').find("#main_group").val(data.opening_data.item_type);
                    jQuery('#EquipmentMPTModal').find("#table_unique_id").val(data.opening_data.table_unique_id);
                    jQuery('#EquipmentMPTModal').find("#item_id").val(data.opening_data.item_id);
                    jQuery('#EquipmentMPTModal').find("#em_grn_number").val(data.opening_data.grn_number != null ? data.opening_data.grn_number : '');
                    jQuery('#EquipmentMPTModal').find("#em_grn_date").val(data.opening_data.grn_date != null ? data.opening_data.grn_date : '');
                    jQuery('#EquipmentMPTModal').find("#supplier").val(data.opening_data.supplier_name);
                    jQuery('#EquipmentMPTModal').find("#challan_no").val(data.opening_data.grn_challan_number != null ? data.opening_data.grn_challan_number : '');
                    jQuery('#EquipmentMPTModal').find("#challan_date").val(data.opening_data.grn_challan_date != null ? data.opening_data.grn_challan_date : '');
                    jQuery('.toggleModalBtn').prop('disabled', true);
                } else {
                    jQuery('#EquipmentMPTModal').find("#table_pk_id").val('');
                    jQuery('#EquipmentMPTModal').find('#item_group').val('');
                    jQuery('#EquipmentMPTModal').find("#main_group").val('');
                    jQuery('#EquipmentMPTModal').find("#table_unique_id").val('');
                    jQuery('#EquipmentMPTModal').find("#item_id").val('');
                    jQuery('#EquipmentMPTModal').find("#em_grn_number").val('');
                    jQuery('#EquipmentMPTModal').find("#em_grn_date").val('');
                    jQuery('#EquipmentMPTModal').find("#supplier").val('');
                    jQuery('#EquipmentMPTModal').find("#challan_no").val('');
                    jQuery('#EquipmentMPTModal').find("#challan_date").val('');
                    jQuery('.toggleModalBtn').prop('disabled', false);
                }
                jQuery("#PendingForEquipmentMPTModal").modal('hide');
            } else {

            }

            jQuery('#PendingForEquipmentMPTModal').find('#submitbtn').prop('disabled', false);
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        },
        error: function (jqXHR) {

            if (jqXHR.status == 401) {
                toastr.error(jqXHR.statusText);
            } else {
                toastr.error('Something went wrong!');
                console.log(jqXHR.responseText);
            }

            jQuery('#PendingForEquipmentMPTModal')
                .find('#submitbtn')
                .prop('disabled', false);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');
        }
    });
});

// Calibration Certificate file change handler
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
            jQuery('#EquipmentMPTModal').find('#submitbtn').prop('disabled', true);
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
                    jQuery('#EquipmentMPTModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#EquipmentMPTModal').find('#submitbtn').prop('disabled', false);
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
        clearCertificate();
    }
});

function clearCertificate() {
    jQuery('#EquipmentMPTModal').find('#calibration_certificate_doc').val('');
    jQuery('#EquipmentMPTModal').find('#calibration_certificate').val('');
    jQuery('#EquipmentMPTModal').find('#calibration_certificate_prev').attr('href', '#').addClass('hide');
    jQuery('#EquipmentMPTModal').find('#calibration_certificate_remove').removeClass('i-block').addClass('hide');
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

function validateImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.pdf)$/i;
    if (!allowedExtensions.exec(filePath)) {
        return false;
    }
    return true;
}

function resetCaliFieldsMPT() {
    jQuery('#EquipmentMPTModal').find('#em_last_cali_date').prop('readonly', false).css('pointer-events', 'auto').datepicker('enable');
    jQuery('#EquipmentMPTModal').find('#em_next_cali_due_date').prop('readonly', false).css('pointer-events', 'auto').datepicker('enable');
    jQuery('#EquipmentMPTModal').find('#ins_cali_freq').prop('readonly', false);
    jQuery('#EquipmentMPTModal').find('#calibration_certificate').prop('disabled', false);
}













