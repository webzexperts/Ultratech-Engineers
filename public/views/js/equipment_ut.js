var formId = jQuery('#EquipmentUTModal').find('#commonEquipmentUTForm').find('#id').val();
var headerOpt = {
    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
};

// Edit material mpt row click
jQuery('#dyntable tbody').on('click', '.edit-equipment_ut', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#EquipmentUTModal').find('#id').val(data["eu_id"]);
    if (data && data["eu_id"]) {
        fetchAndFillMaterialMPT(data["eu_id"]);
    }
});

// Function to fetch and fill material mpt data
function fetchAndFillMaterialMPT(id) {
    if (!id) return;
    jQuery('#EquipmentUTModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-equipment_ut",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.equipment_ut_data != null) {

                jQuery('#EquipmentUTModal').find('#table_unique_id').val(data.equipment_ut_data.table_unique_id);
                jQuery('#EquipmentUTModal').find('#table_pk_id').val(data.equipment_ut_data.table_pk_id);
                jQuery('#EquipmentUTModal').find('#item_id').val(data.equipment_ut_data.item_id);
                jQuery('#EquipmentUTModal').find('#item_group').val(data.equipment_ut_data.item_group);
                jQuery('#EquipmentUTModal').find('#main_group').val(data.equipment_ut_data.main_group);

                jQuery('#EquipmentUTModal').find("#eu_grn_number").val(data.equipment_ut_data.grn_number != null ? data.equipment_ut_data.grn_number : '');
                jQuery('#EquipmentUTModal').find("#eu_grn_date").val(data.equipment_ut_data.grn_date != null ? data.equipment_ut_data.grn_date : '');
                jQuery('#EquipmentUTModal').find("#supplier").val(data.equipment_ut_data.supplier_name);
                jQuery('#EquipmentUTModal').find("#challan_no").val(data.equipment_ut_data.grn_challan_number != null ? data.equipment_ut_data.grn_challan_number : '');
                jQuery('#EquipmentUTModal').find("#challan_date").val(data.equipment_ut_data.grn_challan_date != null ? data.equipment_ut_data.grn_challan_date : '');

                jQuery('#EquipmentUTModal').find('#eu_equipment_name').val(data.equipment_ut_data.eu_equipment_name != "" ? data.equipment_ut_data.eu_equipment_name : "");
                jQuery('#EquipmentUTModal').find('#eu_make').val(data.equipment_ut_data.eu_make != "" ? data.equipment_ut_data.eu_make : "");
                jQuery('#EquipmentUTModal').find('#eu_display').val(data.equipment_ut_data.eu_display != "" ? data.equipment_ut_data.eu_display : "");
                jQuery('#EquipmentUTModal').find('#eu_serial_no').val(data.equipment_ut_data.eu_serial_no != "" ? data.equipment_ut_data.eu_serial_no : "");

                jQuery('#EquipmentUTModal').find('#eu_last_cali_date').val(data.equipment_ut_data.eu_last_cali_date != "" ? zeroToEmpty(data.equipment_ut_data.eu_last_cali_date) : "");
                jQuery('#EquipmentUTModal').find('#eu_next_cali_due_date').val(data.equipment_ut_data.eu_next_cali_due_date != "" ? zeroToEmpty(data.equipment_ut_data.eu_next_cali_due_date) : "");
                jQuery('#EquipmentUTModal').find('#ins_cali_freq').val(data.equipment_ut_data.eu_cali_freq != "" ? zeroToEmpty(data.equipment_ut_data.eu_cali_freq) : "");

                if (data.equipment_ut_data.eu_cali_certificate != "" && data.equipment_ut_data.eu_cali_certificate != null && data.equipment_ut_data.eu_cali_certificate != undefined) {
                    let fullPath = data.equipment_ut_data.eu_cali_certificate;
                    let fileName = fullPath.split('/').pop();
                    jQuery('#EquipmentUTModal').find("#calibration_certificate_doc").val(fullPath);
                    jQuery('#EquipmentUTModal').find('#calibration_certificate_prev').attr('href', uploadURL + fullPath).removeClass('hide');
                    jQuery('#EquipmentUTModal').find('#calibration_certificate_remove').addClass('i-block').removeClass('hide');
                    let fileInput = jQuery('#EquipmentUTModal').find('#calibration_certificate');
                    if (fileInput.length) {
                        let newFile = new DataTransfer();
                        newFile.items.add(new File([""], fileName));
                        fileInput[0].files = newFile.files;
                    }
                } else {
                    jQuery('#EquipmentUTModal').find('#calibration_certificate_doc').val('');
                    jQuery('#EquipmentUTModal').find('#calibration_certificate_prev').attr('href', '#').addClass('hide');
                    jQuery('#EquipmentUTModal').find('#calibration_certificate_remove').removeClass('i-block').addClass('hide');
                    jQuery('#EquipmentUTModal').find('#calibration_certificate').val('');
                }

                jQuery('#EquipmentUTModal').find('#eu_doc_ref_no').val(data.equipment_ut_data.eu_doc_ref_no != "" ? data.equipment_ut_data.eu_doc_ref_no : "");
                jQuery('#EquipmentUTModal').find('#eu_status').val(data.equipment_ut_data.eu_status != "" ? data.equipment_ut_data.eu_status : "Active").trigger("change.select2");

                // jQuery('#EquipmentUTModal').find('#eu_grn_number').val(data.equipment_ut_data.grn_number != "" ? zeroToEmpty(data.equipment_ut_data.grn_number) : "");
                // jQuery('#EquipmentUTModal').find('#eu_grn_date').val(data.equipment_ut_data.grn_date != "" ? zeroToEmpty(data.equipment_ut_data.grn_date) : "");
                // jQuery('#EquipmentUTModal').find('#eu_supplier').val(data.equipment_ut_data.supplier_name != "" ? zeroToEmpty(data.equipment_ut_data.supplier_name) : "");
                // jQuery('#EquipmentUTModal').find('#eu_challan_number').val(data.equipment_ut_data.grn_challan_number != "" ? zeroToEmpty(data.equipment_ut_data.grn_challan_number) : "");
                // jQuery('#EquipmentUTModal').find('#eu_challan_date').val(data.equipment_ut_data.grn_challan_date != null ? zeroToEmpty(data.equipment_ut_data.grn_challan_date) : "");

                jQuery('#EquipmentUTModal').find('#id').val(data.equipment_ut_data.eu_id != "" ? data.equipment_ut_data.eu_id : "");
                if (Number(data.equipment_ut_data.current_location_id) == Number(data.equipment_ut_data.login_location)) {
                    jQuery("#submitbtn").prop('disabled', false);

                } else {
                    jQuery('#EquipmentUTModal').find('#submitbtn').prop('disabled', true);
                }

                if (data.is_used) {
                    jQuery('#EquipmentUTModal').find('#eu_last_cali_date').prop('readonly', true).css('pointer-events', 'none').datepicker('disable');
                    jQuery('#EquipmentUTModal').find('#eu_next_cali_due_date').prop('readonly', true).css('pointer-events', 'none').datepicker('disable');
                    jQuery('#EquipmentUTModal').find('#ins_cali_freq').prop('readonly', true);
                    jQuery('#EquipmentUTModal').find('#calibration_certificate').prop('disabled', true);
                    jQuery('#EquipmentUTModal').find('#calibration_certificate_remove').removeClass('i-block').addClass('hide');
                } else {
                    jQuery('#EquipmentUTModal').find('#eu_last_cali_date').prop('readonly', false).css('pointer-events', 'auto').datepicker('enable');
                    jQuery('#EquipmentUTModal').find('#eu_next_cali_due_date').prop('readonly', false).css('pointer-events', 'auto').datepicker('enable');
                    jQuery('#EquipmentUTModal').find('#ins_cali_freq').prop('readonly', false);
                    jQuery('#EquipmentUTModal').find('#calibration_certificate').prop('disabled', false);
                }


                jQuery('#EquipmentUTModal').find('#pending_btn').prop('disabled', true);

                // setSelect2Readonly("#eu_inward_type",true);
                // setSelect2Readonly("#eu_ut_equipment_id",true);
                // jQuery('#EquipmentUTModal').find('#submitbtn').prop('disabled', false);

                jQuery('#EquipmentUTModal').find('#add_new').show();

                const form = document.getElementById("commonEquipmentUTForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#EquipmentUTModal').find('#eu_equipment_name').focus();

                // setSelect2Readonly("#eu_inward_type", false);
                // InwardTypeChange();
                // setTimeout(function () {
                //     let sel = jQuery('#eu_inward_type')
                //         .next('.select2-container')
                //         .find('.select2-selection');
                //     sel.attr('tabindex', 0).focus();
                // }, 20);
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
    equipment_ut_details_data = [];
    jQuery('#EquipmentUTDetailsTable tbody').empty();
    jQuery('#PendingForEquipmentUTModal').find('#submitbtn').prop('disabled', false);
    var formId = jQuery('#EquipmentUTModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonEquipmentUTForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
        jQuery("#commonEquipmentUTForm #item_id").val('');
        jQuery("#commonEquipmentUTForm #table_pk_id").val('');
        jQuery("#commonEquipmentUTForm #id").val('');
        jQuery("#commonEquipmentUTForm #ins_cali_freq").val('');
        jQuery('#commonEquipmentUTForm').find('#calibration_certificate_doc').val('');
        jQuery('#commonEquipmentUTForm').find('#calibration_certificate_prev').attr('href', '#').addClass('hide');
        jQuery('#commonEquipmentUTForm').find('#calibration_certificate_remove').removeClass('i-block').addClass('hide');
        jQuery('#commonEquipmentUTForm').find('#calibration_certificate').val('');
        // jQuery("#commonEquipmentUTForm #eu_grnd_id").val(0);
        jQuery('#commonEquipmentUTForm').find('.toggleModalBtn').prop('disabled', true);
        resetCaliFieldsUT();
        getPendingEquipmentUt();
        focusPendingButton('#EquipmentUTModal');
        // jQuery('#EquipmentUTModal').find('#pending_btn').attr('disabled', false);
        // getLNRData();
        // InwardTypeChange();
        // setSelect2Readonly("#eu_inward_type", false);
        // setTimeout(function () {
        //     let sel = jQuery('#eu_inward_type')
        //         .next('.select2-container')
        //         .find('.select2-selection');
        //     sel.attr('tabindex', 0).focus();
        // }, 20);

        // jQuery('#EquipmentUTModal').find('#eu_equipment_name').focus();
        jQuery('#commonEquipmentUTForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
        jQuery('#commonEquipmentUTForm').find('#eu_grn_number').prop({ tabindex: -1, readonly: true });
        jQuery('#commonEquipmentUTForm').find('#eu_grn_date').prop({ tabindex: -1, readonly: true });
        jQuery('#commonEquipmentUTForm').find('#supplier').prop({ tabindex: -1, readonly: true });
        jQuery('#commonEquipmentUTForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
        jQuery('#commonEquipmentUTForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
        jQuery('#commonEquipmentUTForm').find('#item_group').prop({ tabindex: -1, readonly: true });
        jQuery('#commonEquipmentUTForm').find('#main_group').prop({ tabindex: -1, readonly: true });

        // setTimeout(() => {
        //     const btn = jQuery('#EquipmentUTModal').find('.toggleModalBtn').filter(':visible').not(':disabled').first();
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

// jQuery('#dyntable tbody').on('click', '.edit-equipment_ut', function () {

//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#EquipmentUTModal').find('#id').val(data["eu_id"]);
//     jQuery('#EquipmentUTModal').modal('show');
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-equipment_ut",
//         type: 'GET',
//         data: "id=" + data["eu_id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {

//                 jQuery('#EquipmentUTModal').find('#eu_inward_type').val(data.equipment_ut_data.eu_inward_type != "" ? data.equipment_ut_data.eu_inward_type : "").trigger("change.select2");


//                 jQuery('#EquipmentUTModal').find('#eu_ut_equipment_id').val(data.equipment_ut_data.eu_ut_equipment_id != "" ? data.equipment_ut_data.eu_ut_equipment_id : "").trigger('change.select2');
//                 jQuery('#EquipmentUTModal').find('#eu_equipment_name').val(data.equipment_ut_data.eu_equipment_name != "" ? data.equipment_ut_data.eu_equipment_name : "");
//                 jQuery('#EquipmentUTModal').find('#eu_make').val(data.equipment_ut_data.eu_make != "" ? data.equipment_ut_data.eu_make : "");
//                 jQuery('#EquipmentUTModal').find('#eu_display').val(data.equipment_ut_data.eu_display != "" ? data.equipment_ut_data.eu_display : "");
//                 jQuery('#EquipmentUTModal').find('#eu_serial_no').val(data.equipment_ut_data.eu_serial_no != "" ? data.equipment_ut_data.eu_serial_no : "");
//                 jQuery('#EquipmentUTModal').find('#eu_calibration_standard').val(data.equipment_ut_data.eu_calibration_standard != "" ? data.equipment_ut_data.eu_calibration_standard : "");
//                 jQuery('#EquipmentUTModal').find('#eu_calibration_technique').val(data.equipment_ut_data.eu_calibration_technique != "" ? data.equipment_ut_data.eu_calibration_technique : "");
//                 jQuery('#EquipmentUTModal').find('#eu_grn_number').val(data.equipment_ut_data.grn_number != "" ? zeroToEmpty(data.equipment_ut_data.grn_number ): "");
//                 jQuery('#EquipmentUTModal').find('#eu_grn_date').val(data.equipment_ut_data.grn_date != "" ? zeroToEmpty(data.equipment_ut_data.grn_date) : "");
//                 jQuery('#EquipmentUTModal').find('#eu_supplier').val(data.equipment_ut_data.supplier_name != "" ? zeroToEmpty(data.equipment_ut_data.supplier_name) : "");
//                 jQuery('#EquipmentUTModal').find('#eu_challan_number').val(data.equipment_ut_data.grn_challan_number != "" ? zeroToEmpty(data.equipment_ut_data.grn_challan_number) : "");
//                 jQuery('#EquipmentUTModal').find('#eu_challan_date').val(data.equipment_ut_data.grn_challan_date != null ? zeroToEmpty(data.equipment_ut_data.grn_challan_date) : "");
//                 jQuery('#EquipmentUTModal').find('#eu_doc_ref_no').val(data.equipment_ut_data.eu_doc_ref_no != "" ? data.equipment_ut_data.eu_doc_ref_no : "");
//                 jQuery('#EquipmentUTModal').find('#eu_remark').val(data.equipment_ut_data.eu_remark != "" ? data.equipment_ut_data.eu_remark : "");
//                 jQuery('#EquipmentUTModal').find('#eu_status').val(data.equipment_ut_data.eu_status != "" ? data.equipment_ut_data.eu_status : "Active").trigger("change.select2");
//                 jQuery('#EquipmentUTModal').find('#eu_grnd_id').val(data.equipment_ut_data.eu_grnd_id != "" ? data.equipment_ut_data.eu_grnd_id : "");

//                if(data.equipment_ut_details_data != "" &&  data.equipment_ut_details_data.length > 0){
//                     equipment_ut_details_data.push(...data.equipment_ut_details_data);
//                     fillEquipmentUTDetailsTable();
//                     //  jQuery(".add_detail").attr("disabled", true);
//                 }
//                 setSelect2Readonly("#eu_inward_type",true);
//                 setSelect2Readonly("#eu_ut_equipment_id",true);
//                 jQuery('#EquipmentUTModal').find('#pending_btn').prop('disabled', true);
//                 jQuery('#EquipmentUTModal').find('#submitbtn').prop('disabled', false);
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

$('#EquipmentUTModal').on('show.bs.modal', function () {
    // setTimeout(() => {
    var formIdblank = jQuery('#commonEquipmentUTForm').find('input[name="id"]').val();
    if (formIdblank == "" || formIdblank == undefined) {
        // getLNRData();
        jQuery('#commonEquipmentUTForm').find('#eu_status').val('Active').trigger('change.select2');
        getPendingEquipmentUt();
        focusPendingButton('#EquipmentUTModal');
        jQuery('#submitbtn').prop('disabled', false);
    } else {
        // setSelect2Readonly('#commonEquipmentUTForm #eu_inward_type', true);
        // setSelect2Readonly('#commonEquipmentUTForm #eu_ut_equipment_id', true);
        // setSelect2Readonly('#commonEquipmentUTForm #eu_status', true);
        jQuery('#commonEquipmentUTForm').find('#pending_btn').prop('disabled', true);
        const input = document.getElementById('eu_equipment_name');
        input?.focus();
    }

    if (formIdblank && formIdblank !== "") {
        jQuery('#EquipmentUTModal').find('#add_new').show();
    } else {
        jQuery('#EquipmentUTModal').find('#add_new').hide();
    }
    // }, 1000);
});

// jQuery('#EquipmentUTModal').on('shown.bs.modal', function () {
//     var formId = jQuery('#commonEquipmentUTForm').find('input[name="id"]').val();
//     var hasAccess = jQuery('#commonEquipmentUTForm').find('#has_access').val();
//     var eu_inward_type = jQuery('#commonEquipmentUTForm').find('#eu_inward_type').val();
//     if (formId == "" || formId == undefined) {
//        getLNRData();
//        jQuery('#EquipmentUTModal').find('#eu_status').val('Active').trigger("change.select2");
//         setSelect2Readonly("#eu_inward_type",false);
//         setSelect2Readonly("#eu_ut_equipment_id",false);
//     }

// });

jQuery('#EquipmentUTModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#EquipmentUTModal');
    formId = jQuery('#commonEquipmentUTForm').find('input[name="id"]').val('');
    jQuery("#commonEquipmentUTForm #item_id").val('');
    jQuery("#commonEquipmentUTForm #table_pk_id").val('');
    // equipment_ut_details_data = [];
    // jQuery('#EquipmentUTDetailsTable tbody').empty();

    document.getElementById("commonEquipmentUTForm").reset();
    clearCertificate();
    // thisForm.find('input, textarea').each(function () {
    //     jQuery(this).val('');
    //     jQuery(this).prop('checked', false);
    // });
    // window.location.reload();
    jQuery('#EquipmentUTModal').find('#add_new').hide();
});

jQuery('#EquipmentUTModal').on('click', '#add_new', function () {
    jQuery('#EquipmentUTModal').find('#id').val('');
    document.getElementById("commonEquipmentUTForm").reset();
    clearCertificate();
    const form = document.getElementById("commonEquipmentUTForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#commonEquipmentUTForm').find('.toggleModalBtn').prop('disabled', true);
    // jQuery('#EquipmentUTModal').find('#eu_equipment_name').focus();
    jQuery('#commonEquipmentUTForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
    jQuery('#commonEquipmentUTForm').find('#eu_grn_number').prop({ tabindex: -1, readonly: true });
    jQuery('#commonEquipmentUTForm').find('#eu_grn_date').prop({ tabindex: -1, readonly: true });
    jQuery('#commonEquipmentUTForm').find('#supplier').prop({ tabindex: -1, readonly: true });
    jQuery('#commonEquipmentUTForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
    jQuery('#commonEquipmentUTForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
    jQuery('#commonEquipmentUTForm').find('#item_group').prop({ tabindex: -1, readonly: true });
    jQuery('#commonEquipmentUTForm').find('#main_group').prop({ tabindex: -1, readonly: true });
    getPendingEquipmentUt();
    jQuery("#commonEquipmentUTForm #id").val('');
    jQuery("#commonEquipmentUTForm #table_pk_id").val('');
    jQuery("#commonEquipmentUTForm #item_id").val('');
    jQuery("#commonEquipmentUTForm #ins_cali_freq").val('');
    jQuery('#commonEquipmentUTForm').find('#calibration_certificate_doc').val('');
    jQuery('#commonEquipmentUTForm').find('#calibration_certificate_prev').attr('href', '#').addClass('hide');
    jQuery('#commonEquipmentUTForm').find('#calibration_certificate_remove').removeClass('i-block').addClass('hide');
    jQuery('#commonEquipmentUTForm').find('#calibration_certificate').val('');
    jQuery("#commonEquipmentUTForm #eu_status").val('Active').trigger("change.select2");
    focusPendingButton('#EquipmentUTModal');
    jQuery('#submitbtn').prop('disabled', false);
    resetCaliFieldsUT();

    // setTimeout(() => {
    //     const btn = jQuery('#EquipmentUTModal').find('.toggleModalBtn').filter(':visible').not(':disabled').first();
    //     if (btn.length > 0) {
    //         btn.trigger('focus');
    //         btn.css({'border-color': '#22b378','outline-offset': '2px','box-shadow': '0 0 0 0.25rem rgba(34, 179, 120, 0.25)'});
    //         btn.one('blur', function () {
    //             jQuery(this).css({'border-color': '','outline-offset': '','box-shadow': ''});
    //         });
    //     }
    // }, 1500);

    jQuery('#EquipmentUTModal').find('#add_new').hide();
});

jQuery('#PendingForEquipmentUTModal').on('show.bs.modal', function (e) {
    var dt = jQuery('#pendingEquipmentUTDataTable').DataTable();
    fixDataTableColumnsUntilAdjusted(dt);

    var usedParts = [];

    var table_pk_id = jQuery('#EquipmentUTModal').find('#table_pk_id').val();
    if (table_pk_id != "" && table_pk_id != null) {
        usedParts.push(Number(table_pk_id));
    }

    function isUsed(pjId) {
        if (usedParts.includes(Number(pjId))) {
            return true;
        }
        return false;
    }

    jQuery('#pendingEquipmentUTDataTable tbody tr').each(function (indx) {

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

jQuery('#PendingForEquipmentUTModal').on('hide.bs.modal', function (e) {

    this.dataset.customHideFocus = 'true';
    const input = jQuery('#eu_grnd_id').val() != '' ? document.getElementById('eu_equipment_name') : document.getElementById('eu_inward_type');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }
});



$('#commonEquipmentUTForm').on('submit', function (e) {

    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#EquipmentUTModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#EquipmentUTModal').find('#submitbtn').prop('disabled', false);
        return;
    }


    var lastCalDate = jQuery('#eu_last_cali_date').val();
    var nextCalDueDate = jQuery('#eu_next_cali_due_date').val();

    let lastDate = parseDate(lastCalDate);
    let nextDate = parseDate(nextCalDueDate);

    if (nextDate < lastDate) {
        toastr.error('Next Calibration Due Date must be greater than Last Calibration Date.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#EquipmentUTModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    var tablePKId = jQuery('#table_pk_id').val();
    if (!tablePKId) {
        toastr.error('Select At Least One Pending.');
        // toastr.error('Select Atleast One Pending.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#EquipmentUTModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#EquipmentUTModal').find('#commonEquipmentUTForm').find('#id').val();

    var formUrl = formId != undefined && formId != "" ? "update-equipment_ut" : "store-equipment_ut";

    var eu_make = jQuery('#eu_make').val();
    var eu_serial_no = jQuery('#eu_serial_no').val();
    var eu_equipment_name = jQuery('#eu_equipment_name').val();
    if (eu_make != '' && eu_serial_no != "") {
        UTEquipmentUrl = formId != undefined && formId != "" ? "verify-equipment_ut?eu_make=" + encodeURIComponent(eu_make) + "&eu_serial_no=" + encodeURIComponent(eu_serial_no) + "&eu_equipment_name=" + encodeURIComponent(eu_equipment_name) + "&eu_id=" + formId : "verify-equipment_ut?eu_make=" + encodeURIComponent(eu_make) + "&eu_serial_no=" + encodeURIComponent(eu_serial_no) + "&eu_equipment_name=" + encodeURIComponent(eu_equipment_name);
    }

    var data = new FormData(this);
    // data.append('equipment_ut_details_data', JSON.stringify(equipment_ut_details_data ?? []));
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));
    // var eu_inward_type = jQuery('#EquipmentUTModal').find('#commonEquipmentUTForm').find('#eu_inward_type').val();
    // var eu_grnd_id = jQuery('#EquipmentUTModal').find('#commonEquipmentUTForm').find('#eu_grnd_id').val();
    if ((eu_equipment_name != '' && eu_equipment_name != undefined) && (eu_serial_no != "" && eu_serial_no != undefined)) {
        $.ajax({
            url: UTEquipmentUrl,
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.response_code == 1) {
                    toastr.error(res.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#EquipmentUTModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#EquipmentUTModal').find('#submitbtn').prop('disabled', false);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        jQuery("#commonEquipmentUTForm #id").val('');
                                        jQuery("#commonEquipmentUTForm #table_pk_id").val('');
                                        jQuery("#commonEquipmentUTForm #item_id").val('');
                                        // jQuery("#commonEquipmentUTForm #eu_grnd_id").val(0);
                                        // jQuery("#commonEquipmentUTForm #eu_inward_type").val('').trigger("change.select2");
                                        jQuery("#commonEquipmentUTForm #eu_status").val('Active').trigger("change.select2");
                                        getPendingEquipmentUt();
                                        document.getElementById("commonEquipmentUTForm").reset();
                                        clearCertificate();
                                        const form = document.getElementById("commonEquipmentUTForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        // jQuery('#EquipmentUTModal').find('#eu_equipment_name').focus();
                                        jQuery('#commonEquipmentUTForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonEquipmentUTForm').find('#eu_grn_number').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonEquipmentUTForm').find('#eu_grn_date').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonEquipmentUTForm').find('#supplier').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonEquipmentUTForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonEquipmentUTForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonEquipmentUTForm').find('#item_group').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonEquipmentUTForm').find('#main_group').prop({ tabindex: -1, readonly: true });
                                        jQuery('#PendingForEquipmentUTModal').find('#submitbtn').prop('disabled', false);
                                        focusPendingButton('#EquipmentUTModal');
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    // setTimeout(() => {
                                    //     const btn = jQuery('#EquipmentUTModal').find('.toggleModalBtn').filter(':visible').not(':disabled').first();
                                    //     if (btn.length > 0) {
                                    //         btn.trigger('focus');
                                    //         btn.css({'border-color': '#22b378','outline-offset': '2px','box-shadow': '0 0 0 0.25rem rgba(34, 179, 120, 0.25)'});
                                    //         btn.one('blur', function () {
                                    //             jQuery(this).css({'border-color': '','outline-offset': '','box-shadow': ''});
                                    //         });
                                    //     }
                                    // }, 1500);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#EquipmentUTModal').find('#submitbtn').prop('disabled', false);
                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#EquipmentUTModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#EquipmentUTModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#EquipmentUTModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#EquipmentUTModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#EquipmentUTModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }

    // else {
    //      toastr.error('Please Add At Least One Equipment UT Details.');
    //     jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
    //     jQuery('#EquipmentUTModal').find('#submitbtn').prop('disabled', false);
    // }


});



// Suggest
function suggestMake(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "make-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#eu_make").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#eu_make_list').html(data.MakeList);
                } else {
                    toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#eu_make").removeClass('file-loader');
                var errMessage = JSON.parse(jqXHR.responseText);
                if (errMessage.errors) {
                    MakeValidator.showErrors(errMessage.errors);
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
jQuery(document).on('click', '#eu_make_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#eu_make_suggesion').val(suggest);
    var hidden = jQuery('#eu_make_suggesion').val();
    var suggestion_list = jQuery('#eu_make_list').html;
    jQuery('#EquipmentUTModal').find('#eu_make').val(hidden)
    // var job_description = hidden;

    jQuery('#eu_make_list').html('');
});


function suggestDisplay(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "display-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#eu_display").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#eu_display_list').html(data.DisplayList);
                } else {
                    toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#eu_display").removeClass('file-loader');
                var errMessage = JSON.parse(jqXHR.responseText);
                if (errMessage.errors) {
                    DisplayValidator.showErrors(errMessage.errors);
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
jQuery(document).on('click', '#eu_display_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#eu_display_suggesion').val(suggest);
    var hidden = jQuery('#eu_display_suggesion').val();
    var suggestion_list = jQuery('#eu_display_list').html;
    jQuery('#EquipmentUTModal').find('#eu_display').val(hidden)
    // var job_description = hidden;

    jQuery('#eu_display_list').html('');
});


function suggestCalibrationStandard(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "calibration_standard-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#eu_calibration_standard").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#eu_calibration_standard_list').html(data.CalibrationStandardList);
                } else {
                    toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#eu_calibration_standard").removeClass('file-loader');
                var errMessage = JSON.parse(jqXHR.responseText);
                if (errMessage.errors) {
                    calibrationStandardValidator.showErrors(errMessage.errors);
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
jQuery(document).on('click', '#eu_calibration_standard_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#eu_calibration_standard_suggesion').val(suggest);
    var hidden = jQuery('#eu_calibration_standard_suggesion').val();
    var suggestion_list = jQuery('#eu_calibration_standard_list').html;
    jQuery('#EquipmentUTModal').find('#eu_calibration_standard').val(hidden)
    // var job_description = hidden;

    jQuery('#eu_calibration_standard_list').html('');
});



function suggestCalibrationTechnique(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "calibration_technique-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#eu_calibration_technique").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#eu_calibration_technique_list').html(data.CalibrationTechniqueList);
                } else {
                    toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#eu_calibration_technique").removeClass('file-loader');
                var errMessage = JSON.parse(jqXHR.responseText);
                if (errMessage.errors) {
                    calibrationTechniqueValidator.showErrors(errMessage.errors);
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
jQuery(document).on('click', '#eu_calibration_technique_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#eu_calibration_technique_suggesion').val(suggest);
    var hidden = jQuery('#eu_calibration_technique_suggesion').val();
    var suggestion_list = jQuery('#eu_calibration_technique_list').html;
    jQuery('#EquipmentUTModal').find('#eu_calibration_technique').val(hidden)
    // var job_description = hidden;

    jQuery('#eu_calibration_technique_list').html('');
});


// get pending equipment ut from grn

function getPendingEquipmentUt() {
    // var eu_inward_type = jQuery('#eu_inward_type option:selected').val();
    var thisForm = jQuery('#addPendigEquipmentUTForm');

    jQuery.ajax({
        url: "get-pending_grn_list_for_equipment_ut",
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

                    thisForm.find('#pendingEquipmentUTDataTable tbody input[name="form_indx"]').each(function (indx) {
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
                    var found = 0;

                    // end new code
                    if (data.opening_data.length > 0 && !jQuery.isEmptyObject(data.opening_data)) {
                        found = 1;
                        var formIdblank = jQuery('#commonEquipmentUTForm').find('#id').val();
                        if (formIdblank != "" && formIdblank != undefined) {
                            jQuery('#pending_btn').prop('disabled', true);
                        } else {
                            jQuery('#pending_btn').prop('disabled', false);
                            focusPendingButton('#EquipmentUTModal');
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
                                        <td colspan="9">No Pending GRN Available</td>
                                    </tr>`;

                        jQuery('.toggleModalBtn').prop('disabled', true);
                    }

                    var $table = jQuery("#PendingForEquipmentUTModal").find('#pendingEquipmentUTDataTable');
                    if (jQuery.fn.DataTable.isDataTable($table)) {
                        $table.DataTable().clear().destroy();
                    }
                    jQuery('#pendingEquipmentUTDataTable tbody').empty().append(tblHtml);

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

                    // if (eu_inward_type == 'Manual') {
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
$('#addPendigEquipmentUTForm').on('submit', function (e) {

    e.preventDefault();

    jQuery('#full-page-loader')
        .removeClass('hidden-loader')
        .addClass('loader-progress-whole-page');

    jQuery('#PendingForEquipmentUTModal')
        .find('#submitbtn')
        .prop('disabled', true);

    let chkArr = [];

    // jQuery("#addPendigEquipmentUTForm")
    //     .find("[id^='grnd_ids_']:checked")
    //     .each(function () {
    //         chkArr.push(jQuery(this).val());
    //     });

    let uniqueArr = [];
    jQuery("#addPendigEquipmentUTForm").find("[id^='table_pk_ids_']:checked")
        .each(function () {
            chkArr.push(jQuery(this).val());
            uniqueArr.push(jQuery(this).data('table_unique_id'));
        });

    if (chkArr.length === 0) {
        toastr.error('Select At Least One Pending.');
        //  toastr.error('Please Select Atleast One Pending.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PendingForEquipmentUTModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    jQuery.ajax({
        url: 'get-pending_grn_for_equipment_ut',
        type: 'GET',
        data: {
            table_pk_ids: chkArr.join(','),
            table_unique_ids: uniqueArr.join(',')
        },
        dataType: 'json',
        success: function (data) {

            if (data.response_code == 1) {
                if (data.opening_data) {

                    jQuery('#EquipmentUTModal').find("#table_pk_id").val(data.opening_data.table_pk_id);
                    jQuery('#EquipmentUTModal').find('#item_group').val(data.opening_data.item_group);
                    jQuery('#EquipmentUTModal').find("#main_group").val(data.opening_data.item_type);
                    jQuery('#EquipmentUTModal').find("#table_unique_id").val(data.opening_data.table_unique_id);
                    jQuery('#EquipmentUTModal').find("#item_id").val(data.opening_data.item_id);
                    jQuery('#EquipmentUTModal').find("#eu_grn_number").val(data.opening_data.grn_number != null ? data.opening_data.grn_number : '');
                    jQuery('#EquipmentUTModal').find("#eu_grn_date").val(data.opening_data.grn_date != null ? data.opening_data.grn_date : '');
                    jQuery('#EquipmentUTModal').find("#supplier").val(data.opening_data.supplier_name);
                    jQuery('#EquipmentUTModal').find("#challan_no").val(data.opening_data.grn_challan_number != null ? data.opening_data.grn_challan_number : '');
                    jQuery('#EquipmentUTModal').find("#challan_date").val(data.opening_data.grn_challan_date != null ? data.opening_data.grn_challan_date : '');
                    jQuery('.toggleModalBtn').prop('disabled', true);
                } else {
                    jQuery('#EquipmentUTModal').find("#table_pk_id").val('');
                    jQuery('#EquipmentUTModal').find('#item_group').val('');
                    jQuery('#EquipmentUTModal').find("#main_group").val('');
                    jQuery('#EquipmentUTModal').find("#table_unique_id").val('');
                    jQuery('#EquipmentUTModal').find("#item_id").val('');
                    jQuery('#EquipmentUTModal').find("#eu_grn_number").val('');
                    jQuery('#EquipmentUTModal').find("#eu_grn_date").val('');
                    jQuery('#EquipmentUTModal').find("#supplier").val('');
                    jQuery('#EquipmentUTModal').find("#challan_no").val('');
                    jQuery('#EquipmentUTModal').find("#challan_date").val('');
                    jQuery('.toggleModalBtn').prop('disabled', false);
                }
                jQuery("#PendingForEquipmentUTModal").modal('hide');
            } else {

            }

            jQuery('#PendingForEquipmentUTModal')
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

            jQuery('#PendingForEquipmentUTModal')
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
            jQuery('#EquipmentUTModal').find('#submitbtn').prop('disabled', true);
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
                    jQuery('#EquipmentUTModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#EquipmentUTModal').find('#submitbtn').prop('disabled', false);
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
    jQuery('#EquipmentUTModal').find('#calibration_certificate_doc').val('');
    jQuery('#EquipmentUTModal').find('#calibration_certificate').val('');
    jQuery('#EquipmentUTModal').find('#calibration_certificate_prev').attr('href', '#').addClass('hide');
    jQuery('#EquipmentUTModal').find('#calibration_certificate_remove').removeClass('i-block').addClass('hide');
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

function resetCaliFieldsUT() {
    jQuery('#EquipmentUTModal').find('#eu_last_cali_date').prop('readonly', false).css('pointer-events', 'auto').datepicker('enable');
    jQuery('#EquipmentUTModal').find('#eu_next_cali_due_date').prop('readonly', false).css('pointer-events', 'auto').datepicker('enable');
    jQuery('#EquipmentUTModal').find('#ins_cali_freq').prop('readonly', false);
    jQuery('#EquipmentUTModal').find('#calibration_certificate').prop('disabled', false);
}







