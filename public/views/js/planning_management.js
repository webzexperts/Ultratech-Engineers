
//get data for edit
pm_details_data = [];

// Edit planning management row click
jQuery('#dyntable tbody').on('click', '.edit-planning_management', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["pm_id"]) {
        fetchAndFillPlanningManagement(data["pm_id"]);
    }
});

// Function to fetch and fill part data
function fetchAndFillPlanningManagement(id) {
    if (!id) return;
    jQuery('#PlanningManagementModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-planning_management",
        type: 'GET',
        data: { id: id }, 
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.pm_data != null) {
                jQuery('#PlanningManagementModal').find('#id').val(data.pm_data.pm_id != "" ? data.pm_data.pm_id : "");

                jQuery('#PlanningManagementModal').find('#pm_number').val(data.pm_data.pm_number != "" ? data.pm_data.pm_number : "");
                jQuery('#PlanningManagementModal').find('#pm_date').val(data.pm_data.pm_date != "" ? data.pm_data.pm_date : "");
                jQuery('#PlanningManagementModal').find('#pm_sequence').val(data.pm_data.pm_sequence != "" ? data.pm_data.pm_sequence : "");
                jQuery('#PlanningManagementModal').find('#pmd_oad_id').val(data.pm_data.pmd_oad_id != "" ? data.pm_data.pmd_oad_id : "");
                jQuery('#PlanningManagementModal').find('#pm_process_at').val(data.pm_data.pm_process_at || '').trigger('change.select2');
                jQuery('#PlanningManagementModal').find('#pm_completion_avrg_period').val(data.pm_data.pm_completion_avrg_period != "" ? data.pm_data.pm_completion_avrg_period : "");

                if(data.pm_details_data != "" &&  data.pm_details_data.length > 0){
                    pm_details_data.push(...data.pm_details_data);
                    fillPMDetailsTable();
                }

                fillPendingOA();

                jQuery('#PlanningManagementModal').find('#pending_btn').prop('disabled', true);
                jQuery('#PlanningManagementModal').find('#submitbtn').prop('disabled', false);

                const form = document.getElementById("commonPMForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#PlanningManagementModal #pm_sequence').focus();
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

// Reset button click for planning management modal
jQuery('#resetbtn').on('click', function () {
    pm_details_data = [];
    jQuery('#PMDetailTable tbody').empty();

    var formId = jQuery('#PlanningManagementModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonPMForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        jQuery('#PlanningManagementModal #pm_sequence').focus();
        jQuery('#PlanningManagementModal #pm_process_at').val('').trigger('change.select2');
        jQuery('#PlanningManagementModal #pm_completion_avrg_period').val('');
        getLatestPMNo();
    } else {
        fetchAndFillPlanningManagement(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit-planning_management', function () {

//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#PlanningManagementModal').find('#id').val(data["pm_id"]);
//     jQuery('#PlanningManagementModal').modal('show');
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-planning_management",
//         type: 'GET',
//         data: "id=" + data["pm_id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {

//                 jQuery('#PlanningManagementModal').find('#id').val(data.pm_data.pm_id != "" ? data.pm_data.pm_id : "");

//                 jQuery('#PlanningManagementModal').find('#pm_number').val(data.pm_data.pm_number != "" ? data.pm_data.pm_number : "");
//                 jQuery('#PlanningManagementModal').find('#pm_date').val(data.pm_data.pm_date != "" ? data.pm_data.pm_date : "");
//                 jQuery('#PlanningManagementModal').find('#pm_sequence').val(data.pm_data.pm_sequence != "" ? data.pm_data.pm_sequence : "");
//                 jQuery('#PlanningManagementModal').find('#pmd_oad_id').val(data.pm_data.pmd_oad_id != "" ? data.pm_data.pmd_oad_id : "");
//                 jQuery('#PlanningManagementModal').find('#pm_process_at').val(data.pm_data.pm_process_at || '').trigger('change.select2');
//                 jQuery('#PlanningManagementModal').find('#pm_completion_avrg_period').val(data.pm_data.pm_completion_avrg_period != "" ? data.pm_data.pm_completion_avrg_period : "");

//                 if(data.pm_details_data != "" &&  data.pm_details_data.length > 0){
//                     pm_details_data.push(...data.pm_details_data);
//                     fillPMDetailsTable();
//                 }

//                 fillPendingOA();

//                 jQuery('#PlanningManagementModal').find('#pending_btn').prop('disabled', true);
//                 jQuery('#PlanningManagementModal').find('#submitbtn').prop('disabled', false);
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

jQuery('#PlanningManagementModal #pendingBtn').on('click', function () {
    var formId = jQuery('#commonPMForm').find('input[name="id"]').val();
    if (formId == "" || formId == undefined) {
        fillPendingOA();
    }
});


var formId = jQuery('#commonPMForm').find('input[name="id"]').val();
jQuery('#PlanningManagementModal').on('shown.bs.modal', function () {
    var hasAccess = jQuery('#commonPMForm').find('#has_access').val();
    var formId = jQuery('#commonPMForm').find('input[name="id"]').val();

    
    if (formId == "" || formId == undefined) {
        // jQuery('#commonPMForm #oa_type_id').val('From Quotation').trigger('change');
        getLatestPMNo();
    }
    const input = document.getElementById('pm_sequence');
    input?.focus();

});

jQuery('#PlanningManagementModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#PlanningManagementModal');
    thisModal.find("#id").val("");
    var thisForm = jQuery('#OADetailsModal');
    thisForm.find("#pmd_oad_id").val(0);
    pm_details_data = [];

    jQuery('#OADetailTable tbody').empty();
    jQuery('#commonPMForm').trigger("reset");
    thisModal.find('#oa_type_id').val('').trigger('change');
    thisModal.find('#oa_customer_id').val('').trigger('change.select2');
    thisModal.find('#oa_kind_attn_id').val('').trigger('change.select2');
    // thisModal.find('input, textarea, select').each(function () {
    //     jQuery(this).val('');
    //     jQuery(this).prop('checked', false);
    // });
});

async function fillPendingOA() {

        var formId = jQuery('#commonPMForm').find('input[name="id"]').val();
        var thisForm = jQuery('#commonPMForm');

            if (formId == undefined) {
                var Url = "get-pending_oa_list_for_pm";
            } else {
                var Url = "get-pending_oa_list_for_pm?id=" + formId;
            }

            jQuery.ajax({
                url: Url,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    if (data.response_code == 1 && data.oa_data.length > 0) {
                        // new code
                        var usedParts = [];
                        var totalDisb = 0;
                        var found = 0;

                        thisForm.find('#PMDetailTable tbody input[name="form_indx"]').each(function (indx) {
                            let frmIndx = jQuery(this).val();

                            let jbEorkOrderId = pm_details_data[frmIndx].pmd_oad_id;
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
                        if (data.oa_data.length > 0 && !jQuery.isEmptyObject(data.oa_data)) {
                            found = 1;

                            for (let idx in data.oa_data) {
                                var inUse = isUsed(data.oa_data[idx].oa_id);
                                var in_use = data.oa_data[idx].in_use == true ? 'readonly' : '';
                                totalEntry++;
                        tblHtml += `<tr>
                                <td><input class="checkbox-filter-remove" type="checkbox" name="oad_id[]" class="simple-check" id="oad_ids_${data.oa_data[idx].pmd_oad_id}"
                                    value="${data.oa_data[idx].pmd_oad_id}"/></td>
                                <td>${data.oa_data[idx].oad_test_method_id}</td>
                                <td>${parseFloat(data.oa_data[idx].pend_oa_qty).toFixed(3)}</td>
                                <td>${data.oa_data[idx].oad_qty != null ? data.oa_data[idx].oad_qty : ""}</td>
                                <td>${data.oa_data[idx].unit != null ? data.oa_data[idx].unit : ''}</td>
                                <td>${data.oa_data[idx].type_of_job != null ? data.oa_data[idx].type_of_job : ''}</td>
                                <td>${data.oa_data[idx].job_description != null ? data.oa_data[idx].job_description : ''}</td>
                                <td>${data.oa_data[idx].part != null ? data.oa_data[idx].part : ''}</td>
                                <td>${data.oa_data[idx].customer != null ? data.oa_data[idx].customer : ''}</td>
                                <td>${data.oa_data[idx].customer_code != null ? data.oa_data[idx].customer_code : ''}</td>
                                <td>${data.oa_data[idx].oa_po_number != null ? data.oa_data[idx].oa_po_number : ''}</td>
                                <td>${data.oa_data[idx].oa_po_date != null ? data.oa_data[idx].oa_po_date : ''}</td>
                                <td>${data.oa_data[idx].oa_number != null ? data.oa_data[idx].oa_number : ''}</td>
                                <td>${data.oa_data[idx].oa_date != null ? data.oa_data[idx].oa_date : ''}</td>
                                <td>${data.oa_data[idx].oa_type_id != null ? data.oa_data[idx].oa_type_id : ''}</td>
                                <td>${data.oa_data[idx].oad_process_at_id != null ? data.oa_data[idx].oad_process_at_id : ''}</td>
                                <td>${data.oa_data[idx].oad_remark != null ? data.oa_data[idx].oad_remark : ''}</td>
                                <td>${data.oa_data[idx].oa_special_note != null ? data.oa_data[idx].oa_special_note : ''}</td>
                                </tr>`;

                            }

                        } else {

                            tblHtml += `<tr class="centeralign" id="noPendingPo">
                                            <td colspan="8">No Pending Quotation Available</td>
                                        </tr>`;

                        }

                        var $table = jQuery("#PMPendingModal").find('#pendingOADataTable');
                        if (jQuery.fn.DataTable.isDataTable($table)) {
                            $table.DataTable().clear().destroy();
                        }
                        jQuery('#pendingOADataTable tbody').empty().append(tblHtml);
                        
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

// after checked inquirys from pending modal getting data
$('#addPendigPMForm').on('submit', function (e) {

    e.preventDefault();

    jQuery('#full-page-loader')
        .removeClass('hidden-loader')
        .addClass('loader-progress-whole-page');

    jQuery('#PMPendingModal')
        .find('#submitbtn')
        .prop('disabled', true);

    let chkArr = [];
    var formId = jQuery('#commonPMForm').find('input[name="id"]').val();

    jQuery("#addPendigPMForm")
        .find("[id^='oad_ids_']:checked")
        .each(function () {
            chkArr.push(jQuery(this).val());
        });

    // No checkbox selected
    if (chkArr.length === 0) {
         toastr.error('Select At least One Order Acceptance From Pending');

        jQuery('#full-page-loader')
            .removeClass('loader-progress-whole-page')
            .addClass('hidden-loader');

        jQuery('#PMPendingModal')
            .find('#submitbtn')
            .prop('disabled', false);

        return;
    }

    if(formId != undefined && formId != ""){
        var pend_url = "get-pending_oa_for_pm?id=" + formId;
    }else{
        var pend_url = "get-pending_oa_for_pm";
    }

    jQuery.ajax({
        url: pend_url,
        type: 'GET',
        data: { oad_ids: chkArr.join(',') },
        dataType: 'json',
        success: function (data) {

            if (data.response_code == 1) {

                if (data.oa_data && data.oa_data.length > 0) {
                    pm_details_data = [];
                    for (let ind in data.oa_data) {
                        pm_details_data.push(data.oa_data[ind]);
                    }
                    fillPMDetailsTable(data.oa_data);
                } else {
                    fillPMDetailsTable([]);
                }

                jQuery("#PMPendingModal").modal('hide');
            } else {
                fillPMDetailsTable([]);
            }

            jQuery('#PMPendingModal')
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

            jQuery('#PMPendingModal')
                .find('#submitbtn')
                .prop('disabled', false);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');
        }
    });
});

// edit time fill quotation table start
function fillPMDetailsTable() {
    let  tblHtml = ``;
    if (pm_details_data.length > 0) {
        for (let key in pm_details_data) {

            var formIndx = pm_details_data.indexOf(pm_details_data[key]);

            var oad_test_method_id = pm_details_data[key].oad_test_method_id ? pm_details_data[key].oad_test_method_id : null;
            var pend_qty = pm_details_data[key].pend_oa_qty ?  parseFloat(pm_details_data[key].pend_oa_qty).toFixed(3) : "";
            var pmd_plan_qty  = pm_details_data[key].pmd_plan_qty ?  parseFloat(pm_details_data[key].pmd_plan_qty).toFixed(3) : "";
            var oad_qty = pm_details_data[key].oad_qty ? parseFloat(pm_details_data[key].oad_qty).toFixed(3) : "";
            var unit = pm_details_data[key].unit ? pm_details_data[key].unit : '';
            
            var type_of_job = pm_details_data[key].type_of_job ? pm_details_data[key].type_of_job : '';
            var job_description = pm_details_data[key].job_description ? pm_details_data[key].job_description : "";
            var part = pm_details_data[key].part ? pm_details_data[key].part : '';
            var customer = pm_details_data[key].customer ? pm_details_data[key].customer : '';
            var customer_code = pm_details_data[key].customer_code ? pm_details_data[key].customer_code : '';
            var oa_po_number = pm_details_data[key].oa_po_number ? pm_details_data[key].oa_po_number : '';
            var oa_po_date = pm_details_data[key].oa_po_date ? pm_details_data[key].oa_po_date : "";
            var oa_number = pm_details_data[key].oa_number ? pm_details_data[key].oa_number : '';
            var oa_date = pm_details_data[key].oa_date ? pm_details_data[key].oa_date : '';
            var oa_type_id = pm_details_data[key].oa_type_id ? pm_details_data[key].oa_type_id : '';
            
            var oad_process_at_id = pm_details_data[key].oad_process_at_id > 0 ? pm_details_data[key].oad_process_at_id : '';

            
            var oad_remark = pm_details_data[key].oad_remark ? pm_details_data[key].oad_remark : "";
            var oa_special_note = pm_details_data[key].oa_special_note ? pm_details_data[key].oa_special_note : "";
            var oad_id = pm_details_data[key].pmd_oad_id ? pm_details_data[key].pmd_oad_id : 0;
            
            var pmd_id = pm_details_data[key].pmd_id ? pm_details_data[key].pmd_id : 0;

          
            tblHtml += `<tr>`;
           
            tblHtml += `
            <input type="hidden" name="form_indx" value="${formIndx}"/>
            <input type="hidden" name="pmd_oad_id[]" value="${oad_id}">
            <input type="hidden" name="pmd_id[]" value="${pmd_id}">
            <input type="hidden" class="pend_qty" name="pend_qty[]" value="${pend_qty}">`;
            tblHtml += `<td>${oad_test_method_id}</td>`;
            if(pmd_id > 0){
                 tblHtml += `<td><input type="text" class="pmd_plan_qty" name="pmd_plan_qty[]" value="${pmd_plan_qty}" onblur="formatPoints(this,3)"> </td>`;
            }else{
                tblHtml += `<td><input type="text" class="pmd_plan_qty" name="pmd_plan_qty[]" value="${pend_qty}" onblur="formatPoints(this,3)"> </td>`;
            }
            // tblHtml += `<td><input type="text" class="pmd_plan_qty" name="pmd_plan_qty[]" value="${pend_qty}" onblur="formatPoints(this,3)"> </td>`;
            tblHtml += `<td>${oad_qty}</td>`;
            tblHtml += `<td>${pend_qty}</td>`;
            tblHtml += `<td>${unit}</td>`;
            tblHtml += `<td>${type_of_job}</td>`;
            tblHtml += `<td>${job_description}</td>`;
            tblHtml += `<td>${part}</td>`;
            tblHtml += `<td>${customer_code}</td>`;
            tblHtml += `<td>${customer}</td>`;
            tblHtml += `<td>${oa_po_number}</td>`;
            tblHtml += `<td>${oa_po_date}</td>`;
            tblHtml += `<td>${oa_number}</td>`;
            tblHtml += `<td>${oa_date}</td>`;
            tblHtml += `<td>${oa_type_id}</td>`;
            tblHtml += `<td>${oad_process_at_id}</td>`;
            tblHtml += `<td>${oad_remark}</td>`;
            tblHtml += `<td>${oa_special_note}</td>`;
           
            tblHtml += `</tr>`;
        }
        jQuery('#PMDetailTable tbody').empty();
        jQuery('#PMDetailTable tbody').append(tblHtml);

        // Add event listener for nrmcd_qty inputs
        jQuery('.pmd_plan_qty').on('change', function() {
            let maxQty = parseFloat(jQuery(this).closest('tr').find('.pend_qty').val());
            let val = parseFloat(jQuery(this).val());
            if (val > maxQty) {
                toastr.error(`Plan Qty. cannot exceed pending Qty. ${maxQty}`);
                // jQuery(this).val(maxQty);
                jQuery(this).focus();

            } else if (val < 0 || isNaN(val)) {
                jQuery(this).val(0);
                jQuery(this).removeClass('input-error'); // add red border

            }
        });
    }
}


// Main Form Submit start
$('#commonPMForm').on('submit', function (e) {
    e.preventDefault();
    let form = this;
    var dateValue = document.getElementById("pm_date").value.trim();

    if (!isValidDate(dateValue)) {
        toastr.error("Please Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PlanningManagementModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PlanningManagementModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PlanningManagementModal').find('#submitbtn').prop('disabled', false);
        return;
    }

     // Before proceeding, validate all rows
    let invalid = false;
    jQuery('#PMDetailTable tbody tr').each(function() {
        let pendQty = parseFloat(jQuery(this).find('.pend_qty').val());
        let planQty = parseFloat(jQuery(this).find('.pmd_plan_qty').val());

        if (planQty > pendQty) {
            invalid = true;
            toastr.error(`Plan Qty. cannot exceed pending Qty. ${pendQty}`);
            return;
            // jQuery(this).find('.nrmcd_qty').val(pendQty);
        } else if (planQty < 0 || isNaN(planQty)) {
            invalid = true;
            toastr.error(`Plan Qty. cannot be negative or empty`);
        }
    });

    if (invalid) {
        return; // stop submission
    }

    pm_details_data = [];
    jQuery('#PMDetailTable tbody tr').each(function () {

        var pmd_oad_id = jQuery(this).find('input[name="pmd_oad_id[]"]').val();
        var pmd_id = jQuery(this).find('input[name="pmd_id[]"]').val();
        var pmd_plan_qty = jQuery(this).find('input[name="pmd_plan_qty[]"]').val();

        // OPTIONAL: skip rows with zero opening qty
        if (parseFloat(pmd_plan_qty) > 0) {
            pm_details_data.push({
                pmd_oad_id: pmd_oad_id,
                pmd_id: pmd_id,
                pmd_plan_qty: pmd_plan_qty,
            });
        }
    });


    jQuery('#PlanningManagementModal').find('#submitbtn').prop('disabled', true);
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');


    var formId = jQuery('#PlanningManagementModal').find('#commonPMForm').find('#id').val();
    var formUrl = formId != undefined && formId != "" ? "update-planning_management" : "store-planning_management";
    var data = new FormData(form);
    data.append('pm_details_data', JSON.stringify(pm_details_data ?? []));
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));
   
    if (pm_details_data.length > 0 && !jQuery.isEmptyObject(pm_details_data)) {

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
                        jQuery('#PlanningManagementModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formId == undefined || formId == "") {
                        function nextFn() {
                            document.getElementById("commonPMForm").reset();
                            const form = document.getElementById("commonPMForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            jQuery('#PlanningManagementModal #pm_sequence').focus();
                            jQuery('#PlanningManagementModal #pm_process_at').val('').trigger('change.select2');
                            jQuery('#PlanningManagementModal #pm_completion_avrg_period').val('');
                            pm_details_data = [];
                            jQuery('#PMDetailTable tbody').empty();
                            getLatestPMNo();
                        }
                        toastSuccess(data.response_message, nextFn);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#PlanningManagementModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#PlanningManagementModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#PlanningManagementModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#PlanningManagementModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#PlanningManagementModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        var formId = jQuery('#PlanningManagementModal').find('#commonPMForm').find('#id').val();
        if (formId != undefined && formId != "") {
            jQuery('#PlanningManagementModal').find('#submitbtn').prop('disabled', true);
            toastr.error('You can not Planning Management.');
        }
        toastr.error('Please Add At Least One Planning Management Details.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PlanningManagementModal').find('#submitbtn').prop('disabled', false);
    }
});
// Main Form Submit End


jQuery('#PMPendingModal').on('show.bs.modal', function (e) {

    var usedItemParts = [];
    var totalItemDisb = 0;
    var formId = jQuery('#commonPMForm').find('input[name="id"]').val();

    jQuery('#PMDetailTable tbody input[name="form_indx"]').each(function (indx) {
        var frmIndx = jQuery(this).val();
        var prItemId = pm_details_data[frmIndx].pmd_oad_id;
        if (prItemId != "" && prItemId != null) {
            usedItemParts.push(Number(prItemId));
        }
    });

    function isItemUsed(pjitemId) {
        if (usedItemParts.includes(Number(pjitemId))) {
            totalItemDisb++;
            return true;
        }
        return false;
    }

    var totalEntry = 0;
    jQuery('#pendingOADataTable tbody tr').each(function (indx) {

        totalEntry++;
        var checkField = jQuery(this).find('input[name="oad_id[]"]');
        var partId = jQuery(checkField).val();
        // var inUse = isItemUsed(partId);

        // Check if it's the first checkbox
        if (formId == undefined) {

            if (pm_details_data.length > 0) {
                var inUse = isItemUsed(partId);

            } else {
                if (indx === 0) {
                    var inUse = true;
                }
            }
        } else {
            var inUse = isItemUsed(partId);
        }

        if (inUse) {
            jQuery(checkField).prop('checked', true);
        } else {
            jQuery(checkField).prop('checked', false);
        }

    });

});

jQuery('#checkall-oa_data').click(function () {
    if (jQuery(this).is(':checked')) {
        jQuery("#PMPendingModal").find("[id^='oad_ids_']:not(.in-use)").prop('checked', true).trigger('change');
    } else {
        jQuery("#PMPendingModal").find("[id^='oad_ids_']:not(.in-use)").prop('checked', false).trigger('change');
    }
});


// get the latest number
function getLatestPMNo() {
    jQuery.ajax({
        url: "get-latest_pm_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#pm_number').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#pm_sequence').val(data.number);
                jQuery('#pm_date').val(currentDate);
            } else {
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            jQuery('#pm_number').removeClass('file-loader');
            console.log('Field To Get Latest Order Acceptance No.!')
        }
    });
}

// onchange of quotatation sequence
jQuery('#commonPMForm').find('#pm_sequence').on('change',function () {
   checkSequence();
});

// check for duplication sequence
function checkSequence() {

    let thisForm = jQuery('#commonPMForm');
    let val = thisForm.find('#pm_sequence').val();

    if (val != "") {

        if (val > 0 == false) {
            toastr.error('Please Enter Valid Planning No.');
            jQuery('#pm_sequence').parent().parent().parent('div.control-group').addClass('error');
            jQuery('#pm_sequence').focus();
            jQuery('#pm_sequence').val('');

        } else {
            jQuery('#pm_sequence').addClass('file-loader');
            jQuery('#pm_sequence').parent().parent().parent('div.control-group').removeClass('error');

            var urL = "check-pm_number_duplication?for=add&pm_sequence=" + val;

            var formId = jQuery('#commonPMForm').find('input[name="id"]').val();

            if (formId !== undefined) { //if form is edit
                urL = "check-pm_number_duplication?for=edit&pm_sequence=" + val + "&id=" + formId;
            }

            jQuery.ajax({
                url: urL,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    jQuery('#pm_sequence').removeClass('file-loader');
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        jQuery('#pm_sequence').val('');
                        const input = document.getElementById('pm_sequence'); input?.focus();
                    } else {
                        
                        jQuery('#pm_number').val(data.latest_no);
                        jQuery('#pm_sequence').val(val);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#pm_sequence').removeClass('file-loader');
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
        jQuery('#pm_number').val('');
        jQuery('#pm_sequence').val('');
    }

}

