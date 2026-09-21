var formId = jQuery('#MaterialInspectionModal').find('#commonMaterialInspectionForm').find('#id').val();




var headerOpt = {
    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
};

// Edit material inspection row click
jQuery('#dyntable tbody').on('click', '.edit-material_inspection', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["mins_id"]) {
        fetchAndFillMaterialInspection(data["mins_id"]);
    }
});

// Function to fetch and fill material inspection data
function fetchAndFillMaterialInspection(id) {
    if (!id) return;
    jQuery('#MaterialInspectionModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-material_inspection",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.material_inspection_data != null) {
                jQuery('#MaterialInspectionModal').find('#mins_number').val(data.material_inspection_data.mins_number != "" ? data.material_inspection_data.mins_number : "");
                jQuery('#MaterialInspectionModal').find('#mins_date').val(data.material_inspection_data.mins_date != "" ? data.material_inspection_data.mins_date : "");
                jQuery('#MaterialInspectionModal').find('#mins_sequence').val(data.material_inspection_data.mins_sequence != "" ? data.material_inspection_data.mins_sequence : "");
                jQuery('#MaterialInspectionModal').find('#mins_mid_id').val(data.material_inspection_data.mins_mid_id != "" ? data.material_inspection_data.mins_mid_id : "");
                jQuery('#MaterialInspectionModal').find('#mi_number').val(data.material_inspection_data.mi_number != "" ? data.material_inspection_data.mi_number : "");
                jQuery('#MaterialInspectionModal').find('#mi_date').val(data.material_inspection_data.mi_date != "" ? data.material_inspection_data.mi_date : "");
                jQuery('#MaterialInspectionModal').find('#mi_challan_number').val(data.material_inspection_data.mi_challan_number != "" ? data.material_inspection_data.mi_challan_number : "");
                jQuery('#MaterialInspectionModal').find('#mi_challan_date').val(data.material_inspection_data.mi_challan_date != "" ? data.material_inspection_data.mi_challan_date : "");
                jQuery('#MaterialInspectionModal').find('#mi_customer_id').val(data.material_inspection_data.mi_customer_id != "" ? data.material_inspection_data.mi_customer_id : "").trigger("change.select2");

                jQuery('#MaterialInspectionModal').find('#mid_test_method_id').val(data.material_inspection_data.mid_test_method_id != "" ? data.material_inspection_data.mid_test_method_id : "").trigger("change.select2");
                jQuery('#MaterialInspectionModal').find('#mid_type_of_job_id').val(data.material_inspection_data.type_of_job != "" ? data.material_inspection_data.type_of_job : "");
                jQuery('#MaterialInspectionModal').find('#mid_job_desc').val(data.material_inspection_data.job_description != "" ? data.material_inspection_data.job_description : "").trigger("change.select2");
                jQuery('#MaterialInspectionModal').find('#mid_part_id').val(data.material_inspection_data.mid_part_id != "" ? data.material_inspection_data.mid_part_id : "").trigger("change.select2");
                jQuery('#MaterialInspectionModal').find('#mins_description').val(data.material_inspection_data.mins_description != "" ? data.material_inspection_data.mins_description : "");
    
                jQuery('#MaterialInspectionModal').find('#mid_qty').val(parseFloat(data.material_inspection_data.mid_qty != null ? data.material_inspection_data.mid_qty : "").toFixed(3));
                jQuery('#MaterialInspectionModal').find('#mid_pending_qty').val(parseFloat(data.material_inspection_data.mid_pending_qty != null ? data.material_inspection_data.mid_pending_qty : "").toFixed(3));
                jQuery('#MaterialInspectionModal').find('#mins_insp_qty').val(parseFloat(data.material_inspection_data.mins_insp_qty != null ? data.material_inspection_data.mins_insp_qty : "").toFixed(3));
                jQuery('#MaterialInspectionModal').find('#mid_qty_unit_id').val(data.material_inspection_data.mid_qty_unit_id != "" ? data.material_inspection_data.mid_qty_unit_id : "").trigger("change.select2");
                jQuery('#MaterialInspectionModal').find('#mins_result').val(data.material_inspection_data.mins_result != "" ? data.material_inspection_data.mins_result : "").trigger("change");
                jQuery('#MaterialInspectionModal').find('#mins_rej_reason').val(data.material_inspection_data.mins_rej_reason != "" ? data.material_inspection_data.mins_rej_reason : "").trigger("change.select2");
                jQuery('#MaterialInspectionModal').find('#mins_inspected_by_id').val(data.material_inspection_data.mins_inspected_by_id != "" ? data.material_inspection_data.mins_inspected_by_id : "").trigger("change.select2");
                jQuery('#MaterialInspectionModal').find('#mins_special_note').val(data.material_inspection_data.mins_special_note != "" ? data.material_inspection_data.mins_special_note : "").trigger("change.select2");
                jQuery('#MaterialInspectionModal').find('#id').val(data.material_inspection_data.mins_id != "" ? data.material_inspection_data.mins_id : "");
                jQuery('#MaterialInspectionModal').find('#mins_mid_id').val(data.material_inspection_data.mins_mid_id != "" ? data.material_inspection_data.mins_mid_id : "");
                jQuery('#MaterialInspectionModal').find('#pending_btn').prop('disabled', true);
                jQuery('#MaterialInspectionModal').find('#submitbtn').prop('disabled', false);

                const form = document.getElementById("commonMaterialInspectionForm");
                if (form) form.classList.remove('was-validated');
                
                if (data.material_inspection_data.in_use == true) {
                    jQuery('#MaterialInspectionModal').find('#mins_sequence').prop('readonly', true);
                    let nextInput = jQuery('#MaterialInspectionModal').find('#mins_date');
                    if (nextInput.hasClass('trans-date-picker') || nextInput.hasClass('date-picker')) {
                        nextInput.one('focus', function () {
                            setTimeout(() => {
                                jQuery(this).datepicker('hide');
                            }, 50);
                        });
                    }
                    nextInput.focus();
                } else {
                    jQuery('#MaterialInspectionModal').find('#mins_sequence').prop('readonly', false);
                    jQuery('#MaterialInspectionModal').find('#mins_sequence').focus();
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
        },
        complete: function () {
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        }
    });
}

// Reset button click for material inspection modal
jQuery('#resetbtn').on('click', function () {
    var formId = jQuery('#MaterialInspectionModal').find('#id').val();
    if (!formId) {
        jQuery('#MaterialInspectionModal').find('#mins_sequence').prop('readonly', false);
        document.getElementById("commonMaterialInspectionForm").reset(); 
        const form = document.getElementById("commonMaterialInspectionForm");
        if (form) {
            form.classList.remove('was-validated');
        }

        jQuery("#commonMaterialInspectionForm #mins_mid_id").val('');
        jQuery("#commonMaterialInspectionForm #mid_test_method_id").val('').trigger("change.select2");
        jQuery("#commonMaterialInspectionForm #mid_part_id").val('').trigger("change.select2");
        jQuery("#commonMaterialInspectionForm #mid_qty_unit_id").val('').trigger("change.select2");
        jQuery("#commonMaterialInspectionForm #mins_result").val('').trigger("change.select2");
        jQuery("#commonMaterialInspectionForm #mins_rej_reason").val('').trigger("change.select2");
        jQuery("#commonMaterialInspectionForm #mins_inspected_by_id").val('').trigger("change.select2");
        jQuery("#commonMaterialInspectionForm #mi_customer_id").val('').trigger("change.select2");
        getLNRData();
        getLatestMaterialInspectionNo();
        fillPendingMaterialInspection();
        setTimeout(() => {
            const btn = document.getElementById('pending_btn');
            if (btn && !btn.disabled) {
                btn.focus();
            } else {
                $('#pending_btn').prop('disabled', false).focus();
            }
        }, 150);
        setSelect2Readonly('#mins_rej_reason', true);
    } else {
        fetchAndFillMaterialInspection(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit-material_inspection', function () {

//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#MaterialInspectionModal').find('#id').val(data["mins_id"]);
//     jQuery('#MaterialInspectionModal').modal('show');
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-material_inspection",
//         type: 'GET',
//         data: "id=" + data["mins_id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {

//                 jQuery('#MaterialInspectionModal').find('#mins_number').val(data.material_inspection_data.mins_number != "" ? data.material_inspection_data.mins_number : "");
//                 jQuery('#MaterialInspectionModal').find('#mins_date').val(data.material_inspection_data.mins_date != "" ? data.material_inspection_data.mins_date : "");
//                 jQuery('#MaterialInspectionModal').find('#mins_sequence').val(data.material_inspection_data.mins_sequence != "" ? data.material_inspection_data.mins_sequence : "");
//                 jQuery('#MaterialInspectionModal').find('#mins_mid_id').val(data.material_inspection_data.mins_mid_id != "" ? data.material_inspection_data.mins_mid_id : "");
//                 jQuery('#MaterialInspectionModal').find('#mi_number').val(data.material_inspection_data.mi_number != "" ? data.material_inspection_data.mi_number : "");
//                 jQuery('#MaterialInspectionModal').find('#mi_date').val(data.material_inspection_data.mi_date != "" ? data.material_inspection_data.mi_date : "");
//                 jQuery('#MaterialInspectionModal').find('#mi_challan_number').val(data.material_inspection_data.mi_challan_number != "" ? data.material_inspection_data.mi_challan_number : "");
//                 jQuery('#MaterialInspectionModal').find('#mi_challan_date').val(data.material_inspection_data.mi_challan_date != "" ? data.material_inspection_data.mi_challan_date : "");
//                 jQuery('#MaterialInspectionModal').find('#mi_customer_id').val(data.material_inspection_data.mi_customer_id != "" ? data.material_inspection_data.mi_customer_id : "").trigger("change.select2");

//                 jQuery('#MaterialInspectionModal').find('#mid_test_method_id').val(data.material_inspection_data.mid_test_method_id != "" ? data.material_inspection_data.mid_test_method_id : "").trigger("change.select2");
//                 jQuery('#MaterialInspectionModal').find('#mid_type_of_job_id').val(data.material_inspection_data.type_of_job != "" ? data.material_inspection_data.type_of_job : "");
//                 jQuery('#MaterialInspectionModal').find('#mid_job_desc').val(data.material_inspection_data.job_description != "" ? data.material_inspection_data.job_description : "").trigger("change.select2");
//                 jQuery('#MaterialInspectionModal').find('#mid_part_id').val(data.material_inspection_data.mid_part_id != "" ? data.material_inspection_data.mid_part_id : "").trigger("change.select2");
//                 jQuery('#MaterialInspectionModal').find('#mins_description').val(data.material_inspection_data.mins_description != "" ? data.material_inspection_data.mins_description : "");
    
//                 jQuery('#MaterialInspectionModal').find('#mid_qty').val(parseFloat(data.material_inspection_data.mid_qty != null ? data.material_inspection_data.mid_qty : "").toFixed(3));
//                 jQuery('#MaterialInspectionModal').find('#mid_pending_qty').val(parseFloat(data.material_inspection_data.mid_pending_qty != null ? data.material_inspection_data.mid_pending_qty : "").toFixed(3));
//                 jQuery('#MaterialInspectionModal').find('#mins_insp_qty').val(parseFloat(data.material_inspection_data.mins_insp_qty != null ? data.material_inspection_data.mins_insp_qty : "").toFixed(3));
//                 jQuery('#MaterialInspectionModal').find('#mid_qty_unit_id').val(data.material_inspection_data.mid_qty_unit_id != "" ? data.material_inspection_data.mid_qty_unit_id : "").trigger("change.select2");
//                 jQuery('#MaterialInspectionModal').find('#mins_result').val(data.material_inspection_data.mins_result != "" ? data.material_inspection_data.mins_result : "").trigger("change");
//                 jQuery('#MaterialInspectionModal').find('#mins_rej_reason').val(data.material_inspection_data.mins_rej_reason != "" ? data.material_inspection_data.mins_rej_reason : "").trigger("change.select2");
//                 jQuery('#MaterialInspectionModal').find('#mins_inspected_by_id').val(data.material_inspection_data.mins_inspected_by_id != "" ? data.material_inspection_data.mins_inspected_by_id : "").trigger("change.select2");
//                 jQuery('#MaterialInspectionModal').find('#mins_special_note').val(data.material_inspection_data.mins_special_note != "" ? data.material_inspection_data.mins_special_note : "").trigger("change.select2");
//                 jQuery('#MaterialInspectionModal').find('#id').val(data.material_inspection_data.mins_id != "" ? data.material_inspection_data.mins_id : "");
//                 jQuery('#MaterialInspectionModal').find('#mins_mid_id').val(data.material_inspection_data.mins_mid_id != "" ? data.material_inspection_data.mins_mid_id : "");
//                 jQuery('#MaterialInspectionModal').find('#pending_btn').prop('disabled', true);
//                 jQuery('#MaterialInspectionModal').find('#submitbtn').prop('disabled', false);
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

jQuery('#MaterialInspectionModal').on('shown.bs.modal', function () {
    var formId = jQuery('#commonMaterialInspectionForm').find('input[name="id"]').val();
    var hasAccess = jQuery('#commonMaterialInspectionForm').find('#has_access').val();
    if (formId == "") {
        getLNRData();
        fillPendingMaterialInspection();
        getLatestMaterialInspectionNo();
        jQuery('#MaterialInspectionModal').find('#mins_result').trigger('change.select2');
        jQuery('#MaterialInspectionModal').find('#pending_btn').prop('disabled', false);
    }else{
        setTimeout(() => {
            setSelect2Readonly('#MaterialInspectionModal #mins_result',true);
            jQuery('#MaterialInspectionModal #mins_insp_qty').attr('readonly',true).prop('tabindex',-1);
        }, 500);
    }
    if (formId == "") {
        jQuery('#MaterialInspectionModal').find('#mins_sequence').prop('readonly', false);
    }
    if (!jQuery('#MaterialInspectionModal').find('#mins_sequence').prop('readonly')) {
        const input = document.getElementById('mins_sequence');
        input?.focus();
    }
});

jQuery('#MaterialInspectionModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#MaterialInspectionModal');
    thisForm.find('#id').val('');
    thisForm.find('#mins_sequence').prop('readonly', false);
    thisForm.find("#mins_mid_id").val('');
    document.getElementById("commonMaterialInspectionForm").reset();
    setSelect2Readonly('#MaterialInspectionModal #mins_result',false);
    jQuery('#MaterialInspectionModal #mins_insp_qty').attr('readonly',false).removeAttr('tabindex',-1);

});


// get the latest number
function getLatestMaterialInspectionNo() {
    jQuery.ajax({
        url: "get-latest_material_inspection_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#mins_number').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#mins_sequence').val(data.number);
                jQuery('#mins_date').val(currentDate);
            } else {
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            jQuery('#mins_number').removeClass('file-loader');
            console.log('Field To Get Latest Material Inspection No.!')
        }
    });
}


function fillPendingMaterialInspection() {

    var thisModal = jQuery('#PendingForMaterialInspectionModal');
    var thisForm = jQuery('#commonMaterialInspectionForm');
    // console.log(formId,"ss");
    if (formId == undefined || formId == "") {
        var Url = 'get-material_inward_list_for_material_inspection';
    } else {
        var Url = 'get-material_inward_list_for_material_inspection ?id =' + formId;
    }

    jQuery.ajax({
        url: Url,
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1 && data.material_inward_data.length > 0) {
                // new code
                var usedParts = [];
                var totalDisb = 0;
                var found = 0;

                var tblHtml = ``;

                if (data.material_inward_data.length > 0 && !jQuery.isEmptyObject(data.material_inward_data)) {

                    for (let idx in data.material_inward_data) {
                        // var inUse = isUsed(data.material_inward_data[idx].grn_details);
                        // var in_use = data.material_inward_data[idx].in_use == true ? 'readonly' : '';
                        // totalEntry++;
                        tblHtml += `<tr>
                                <td><input class="radio-filter-remove" type="radio" name="mid_id[]" class="simple-check" id="mid_ids_${data.material_inward_data[idx].mid_id}"
                                    value="${data.material_inward_data[idx].mid_id}" /></td>
                                <td>${data.material_inward_data[idx].mi_number}</td>
                                <td>${data.material_inward_data[idx].mi_date}</td>
                                <td>${data.material_inward_data[idx].customer_code != null ? data.material_inward_data[idx].customer_code : ''}</td>
                                <td>${data.material_inward_data[idx].customer != null ? data.material_inward_data[idx].customer : ""}</td>
                                <td>${data.material_inward_data[idx].mi_challan_number != null ? data.material_inward_data[idx].mi_challan_number : ''}</td>
                                <td>${data.material_inward_data[idx].mi_challan_date != null ? data.material_inward_data[idx].mi_challan_date : ''}</td>
                                <td>${data.material_inward_data[idx].mid_test_method_id != null ? data.material_inward_data[idx].mid_test_method_id : ''}</td>
                                <td>${data.material_inward_data[idx].type_of_job != null ? data.material_inward_data[idx].type_of_job : ''}</td>
                                <td>${data.material_inward_data[idx].job_description != null ? data.material_inward_data[idx].job_description : ''}</td>
                                <td>${data.material_inward_data[idx].part != null ? data.material_inward_data[idx].part : ''}</td>
                                <td>${data.material_inward_data[idx].mid_qty != null ? parseFloat(data.material_inward_data[idx].mid_qty).toFixed(3) : ''}</td>
                                <td>${data.material_inward_data[idx].unit != null ? data.material_inward_data[idx].unit : ''}</td>
                                <td>${data.material_inward_data[idx].mid_pend_qty != null ? parseFloat(data.material_inward_data[idx].mid_pend_qty).toFixed(3) : ''}</td>
                                <td>${data.material_inward_data[idx].mi_special_note != null ? data.material_inward_data[idx].mi_special_note : ''}</td>
                                </tr>`;
                    }

                } else {

                    tblHtml += `<tr class="centeralign" id="noPendingPo">
                    <td colspan="14">No Pending Material Inward Available</td>
                </tr>`;

                }

                var $table = jQuery("#PendingForMaterialInspectionModal").find('#PendingForMaterialInspectionTable');
                if (jQuery.fn.DataTable.isDataTable($table)) {
                    $table.DataTable().clear().destroy();
                }
                jQuery('#PendingForMaterialInspectionTable tbody').empty().append(tblHtml);
                var $new = $table.DataTable({
                    paging: true,
                    searching: true,
                    "oLanguage": {
                        "sSearch": "Search :"
                    },
                    // "dom": '<"pending_dataTables_wrapper"fltip>',
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
                //  toastr.error(data.response_message);
            }
        },

        // error: function (jqXHR, textStatus, errorThrown) {
        //     var errMessage = JSON.parse(jqXHR.responseText);
        //     if (errMessage.errors) {
        //         countryValidator.showErrors(errMessage.errors);
        //     } else if (jqXHR.status == 401) {
        //          toastr.error(jqXHR.statusText);
        //     } else {
        //          toastr.error('Something went wrong!');
        //         console.log(JSON.parse(jqXHR.responseText));
        //     }
        // }
    });





    //<--On Work Order Modal Show-->//
    jQuery('#PendingForMaterialInspectionModal').on('show.bs.modal', function (e) {
        var dt = jQuery('#PendingForMaterialInspectionTable').DataTable();
        fixDataTableColumnsUntilAdjusted(dt);

        var usedParts = [];


        var mid_id = jQuery('#MaterialInspectionModal').find('#mid_id').val();
        if (mid_id != "" && mid_id != null) {
            usedParts.push(Number(mid_id));
        }

        function isUsed(pjId) {
            if (usedParts.includes(Number(pjId))) {
                return true;
            }
            return false;
        }

        jQuery('#PendingForMaterialInspectionTable tbody tr').each(function (indx) {

            var checkField = jQuery(this).find('input[name="_id[]"]');
            var partId = jQuery(checkField).val();
            var inUse = isUsed(partId);

            if (inUse) {
                jQuery(checkField).prop('checked', true);

            } else {
                jQuery(checkField).prop('checked', false);
            }

        });



    });

    jQuery('#PendingForMaterialInspectionModal').on('hide.bs.modal', function (e) {

        this.dataset.customHideFocus = 'true';
        const input = jQuery('#mins_mid_id').val() != ''  ? document.getElementById('mins_description') : document.getElementById('mins_date');
        if (input) {
            setTimeout(() => {
                input.focus();

                // agar jQuery datepicker che ane auto open na karvu hoy
                if ($(input).hasClass('trans-date-picker')) {

                    $(input).datepicker('hide');

                }
            }, 100);
        }
    });


    $('#materialInspectionForm').on('submit', function (e) {
        jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
        jQuery('#PendingForMaterialInspectionModal').find('#submitbtn').prop('disabled', true);
        e.preventDefault();

        var chkCount = 0;
        var chkArr = [];
        var chkId = [];

        var formId = jQuery('#PendingForMaterialInspectionModal').find('#materialInspectionForm').find('#id').val();
        jQuery("#materialInspectionForm").find("[id^='mid_ids_']").each(function () {

            var thisId = jQuery(this).attr('id');
            var splt = thisId.split('mid_ids_');
            var intId = splt[1];

            if (jQuery(this).is(':checked')) {

                chkArr.push(jQuery(this).val())

                chkId.push(intId);

                chkCount++;

            }

        });

        if (chkCount == 0) {
             toastr.error('Select Material Inward From Pending');
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#PendingForMaterialInspectionModal').find('#submitbtn').prop('disabled', false);
            return false;
        }
        else {

            if (typeof formId === "undefined") {
                var url = "get-material_inward_part_data_material_inspection?mid_ids=" + chkArr.join(',');
            } else {
                var url = "get-material_inward_part_data_material_inspection?mid_ids=" + chkArr.join(',') + "&id=" + formId;
            }

            jQuery.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function (data) {

                    if (data.response_code == 1) {

                        var thisForm = jQuery('#commonMaterialInspectionForm');
                        if (data.material_inward_data && data.material_inward_data.length > 0) {

                            jQuery.each(data.material_inward_data, function (key, material_inward_data) {

                                thisForm.find("#mins_mid_id").val(material_inward_data.mid_id);
                                thisForm.find("#mi_customer_id").val(material_inward_data.mi_customer_id).trigger("change.select2");
                                thisForm.find("#mi_number").val(material_inward_data.mi_number);
                                thisForm.find("#mi_date").val(material_inward_data.mi_date);
                                thisForm.find("#mi_challan_number").val(material_inward_data.mi_challan_number);
                                thisForm.find("#mi_challan_date").val(material_inward_data.mi_challan_date);
                                thisForm.find("#mid_test_method_id").val(material_inward_data.mid_test_method_id != "" ? material_inward_data.mid_test_method_id : "").trigger("change.select2");
                                thisForm.find("#mid_type_of_job_id").val(material_inward_data.type_of_job != "" ? material_inward_data.type_of_job : "").trigger("change.select2");
                                thisForm.find("#mid_job_desc").val(material_inward_data.job_description != "" ? material_inward_data.job_description : "").trigger("change.select2");
                                thisForm.find("#mid_part_id").val(material_inward_data.mid_part_id != "" ? material_inward_data.mid_part_id : "").trigger("change.select2");
                                thisForm.find("#mid_qty").val(material_inward_data.mid_qty != null ? parseFloat(material_inward_data.mid_qty).toFixed(3) : '');
                                thisForm.find("#mid_pending_qty").val(material_inward_data.mid_pend_qty != null ? parseFloat(material_inward_data.mid_pend_qty).toFixed(3) : '');
                                thisForm.find("#mid_qty_unit_id").val(material_inward_data.mid_qty_unit_id != "" ? material_inward_data.mid_qty_unit_id : "").trigger("change.select2");

                            });
                        }
                        jQuery('#commonMaterialInspectionForm').find('#pending_btn').prop('disabled', true);
                        jQuery('#full-page-loader')
                            .removeClass('loader-progress-whole-page')
                            .addClass('hidden-loader');

                        jQuery('#PendingForMaterialInspectionModal')
                            .find('#submitbtn')
                            .prop('disabled', false);

                        jQuery("#PendingForMaterialInspectionModal").modal('hide');

                    } else {
                        toastError(data.response_message);
                    }
                },
                error: function (jqXHR) {
                    if (jqXHR.status == 401) {
                        toastError(jqXHR.statusText);
                    } else {
                        toastError('Something went wrong!');
                        console.log(jqXHR.responseText);
                    }
                }
            });

        }

    });

}

jQuery('#MaterialInspectionModal').on('change', '#mins_result', function () {

    if (this.value == 'Rejected') {  
        console.log("rejected");
        setSelect2Readonly('#mins_rej_reason', false);
        jQuery('#mins_rej_reason').val('').trigger("change.select2").removeClass('skip-tab');
        setSelect2Required('#mins_rej_reason', true);
    }
    else {
        setSelect2Readonly('#mins_rej_reason', true);
        jQuery('#mins_rej_reason').val('').trigger("change.select2").addClass('skip-tab');
        setSelect2Required('#mins_rej_reason', false);
    }
});

$('#commonMaterialInspectionForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#MaterialInspectionModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    var currentQty = jQuery('#MaterialInspectionModal').find('#mins_insp_qty').val();
    var maxQtyAllowed =jQuery('#MaterialInspectionModal').find('#mid_pending_qty').val();
    if (parseFloat(maxQtyAllowed) > 0 && parseFloat(currentQty) > parseFloat(maxQtyAllowed) && parseFloat(maxQtyAllowed) > 0) {
        
        toastr.error(`Insp. Qty. cannot exceed Pending Qty.`);
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#MaterialInspectionModal').find('#submitbtn').prop('disabled', false);
        return;
      
    }

    if (parseFloat(currentQty) < 0.001) {
        toastr.error('Please Enter Insp. Qty. greater than 0.001.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#MaterialInspectionModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    var dateValue = document.getElementById("mins_date").value.trim();
    if (!isValidDate(dateValue)) {
        toastr.error("Please Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#MaterialInspectionModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#MaterialInspectionModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#MaterialInspectionModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    if (!checkSequence()) {
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#MaterialInspectionModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    var formId = jQuery('#MaterialInspectionModal').find('#commonMaterialInspectionForm').find('#id').val();
    var formUrl = formId != undefined && formId != "" ? "update-material_inspection" : "store-material_inspection";
    var data = new FormData(this);
    var mid_id = jQuery('#MaterialInspectionModal').find('#commonMaterialInspectionForm').find('#mins_mid_id').val();
    if (mid_id != undefined && mid_id != "") {

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
                        jQuery('#MaterialInspectionModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formId == undefined || formId == "") {
                        function nextFn() {


                            jQuery('#MaterialInspectionModal').find('#mins_sequence').prop('readonly', false);
                            jQuery("#commonMaterialInspectionForm #id").val('');
                            jQuery("#commonMaterialInspectionForm #mins_mid_id").val('');
                            jQuery("#commonMaterialInspectionForm #mid_test_method_id").val('').trigger("change.select2");
                            jQuery("#commonMaterialInspectionForm #mid_part_id").val('').trigger("change.select2");
                            jQuery("#commonMaterialInspectionForm #mid_qty_unit_id").val('').trigger("change.select2");
                            jQuery("#commonMaterialInspectionForm #mins_result").val('').trigger("change.select2");
                            jQuery("#commonMaterialInspectionForm #mins_rej_reason").val('').trigger("change.select2");
                            jQuery("#commonMaterialInspectionForm #mins_inspected_by_id").val('').trigger("change.select2");
                            jQuery("#commonMaterialInspectionForm #mi_customer_id").val('').trigger("change.select2");
                            getLNRData();
                            getLatestMaterialInspectionNo();
                            fillPendingMaterialInspection();
                            document.getElementById("commonMaterialInspectionForm").reset();
                            const form = document.getElementById("commonMaterialInspectionForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            setTimeout(() => {
                                const btn = document.getElementById('pending_btn');

                                if (btn && !btn.disabled) {
                                    btn.focus();
                                } else {
                                    $('#pending_btn').prop('disabled', false).focus();
                                }
                            }, 150);

                        }
                        toastSuccess(data.response_message, nextFn);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#MaterialInspectionModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                       
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#MaterialInspectionModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {

                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#MaterialInspectionModal').find('#submitbtn').prop('disabled', false);
                }
            },
            error: function (xhr) {
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                jQuery('#MaterialInspectionModal').find('#submitbtn').prop('disabled', false);
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let errorMsg = '';
                    $.each(errors, function (key, value) {
                        errorMsg += value + '\n';
                    });
                     toastr.error(errorMsg);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#MaterialInspectionModal').find('#submitbtn').prop('disabled', false);
                } else {
                     toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#MaterialInspectionModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    }
    else {
         toastr.error('Select Inquiry From Pending');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#MaterialInspectionModal').find('#submitbtn').prop('disabled', false);
    }
});



jQuery('#commonMaterialInspectionForm').find('#mins_sequence').on('change', function () {
    checkSequence();
});

// check for duplication sequence
// function checkSequence() {

//     let thisForm = jQuery('#commonMaterialInspectionForm');
//     let val = thisForm.find('#mins_sequence').val();

//     if (val != "") {

//         if (val > 0 == false) {
//             toastr.error('Please Enter Valid Inspection No.');
//             jQuery('#mins_sequence').parent().parent().parent('div.control-group').addClass('error');
//             jQuery('#mins_sequence').focus();
//             jQuery('#mins_sequence').val('');

//         } else {
//             jQuery('#mins_sequence').addClass('file-loader');
//             jQuery('#mins_sequence').parent().parent().parent('div.control-group').removeClass('error');

//             var urL = "check-mins_number_duplication?for=add&mi_sequence=" + val;

//             var formId = jQuery('#commonMaterialInspectionForm').find('input[name="id"]').val();


//             if (formId !== undefined) { //if form is edit
//                 urL = "check-mins_number_duplication?for=add&mins_sequence=" + val + "&id=" + formId;
//             }

//             jQuery.ajax({
//                 url: urL,
//                 type: 'GET',
//                 headers: headerOpt,
//                 dataType: 'json',
//                 processData: false,
//                 success: function (data) {
//                     jQuery('#mins_sequence').removeClass('file-loader');
//                     if (data.response_code == 0) {
//                         toastr.error(data.response_message);
//                         jQuery('#mins_sequence').val('');
//                         const input = document.getElementById('mins_sequence'); input?.focus();
//                     } else {

//                         jQuery('#mins_number').val(data.latest_no);
//                         jQuery('#mins_sequence').val(val);
//                     }
//                 },
//                 error: function (jqXHR, textStatus, errorThrown) {
//                     jQuery('#mins_sequence').removeClass('file-loader');
//                     var errMessage = JSON.parse(jqXHR.responseText);
//                     if (errMessage.errors) {
//                         validator.showErrors(errMessage.errors);
//                     } else if (jqXHR.status == 401) {
//                         toastr.error(jqXHR.statusText);
//                     } else {
//                         toastr.error('Something went wrong!');
//                         console.log(JSON.parse(jqXHR.responseText));
//                     }

//                 }
//             });
//         }
//     } else {
//         jQuery('#mins_number').val('');
//         jQuery('#mins_sequence').val('');
//     }

// }

function checkSequence() {

    let thisForm = jQuery('#commonMaterialInspectionForm');
    let val = thisForm.find('#mins_sequence').val();

    if (val > 0 == false) {
        jQuery('#mins_sequence').parent().parent().parent('div.control-group').addClass('error');
        jQuery('#mins_sequence').focus();
        jQuery('#mins_sequence').val('');
        return false;
    }

    jQuery('#mins_sequence').addClass('file-loader');
    jQuery('#mins_sequence').parent().parent().parent('div.control-group').removeClass('error');

    let isValid = false;

    var formId = jQuery('#commonMaterialInspectionForm').find('input[name="id"]').val();

    var urL = "check-mins_number_duplication?for=add&mins_sequence=" + val;

    if (formId !== undefined && formId != "") {
        urL = "check-mins_number_duplication?for=add&mins_sequence=" + val + "&id=" + formId;
    }

    jQuery.ajax({
        url: urL,
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        async: false, // IMPORTANT: block submit
        success: function (data) {
            jQuery('#mins_sequence').removeClass('file-loader');

            if (data.response_code == 0) {
                toastr.error(data.response_message);
                jQuery('#mins_sequence').val('').focus();
                isValid = false;
            } else {
                jQuery('#mins_number').val(data.latest_no);
                jQuery('#mins_sequence').val(val);
                isValid = true;
            }
        },
        error: function () {
            jQuery('#mins_sequence').removeClass('file-loader');
            toastr.error('Something went wrong!');
            isValid = false;
        }
    });

    return isValid;
}


// get Last Reset Data
function getLNRData() {
    jQuery.ajax({
        url: "get-material_inspection_lnr_data",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                if(data.lnr_data != null){
                    
                    jQuery('#mins_inspected_by_id').val(data.lnr_data.mins_inspected_by_id).trigger("change.select2");
                }
            } else {
                console.log(data.response_message)
            }
        },
    });
}

