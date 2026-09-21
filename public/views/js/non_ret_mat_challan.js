var formId = jQuery('#commonNRMCForm').find('input[name="id"]').val();

dc_details_data = [];

// Edit non returnable material challan row click
jQuery('#dyntable tbody').on('click', '.edit-non_returnable_material_challan', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["nrmc_id"]) {
        fetchAndFillNonRetMatChallan(data["nrmc_id"]);
    }
});
 
// Function to fetch and fill non returnable material challan data
function fetchAndFillNonRetMatChallan(id) {
    if (!id) return;
    jQuery('#NRMCModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-non_returnable_material_challan",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.nrmc_data != null) {
                jQuery('#NRMCModal').find('#id').val(data.nrmc_data.nrmc_id != "" ? data.nrmc_data.nrmc_id : "");
                jQuery('#NRMCModal').find('#nrmc_number').val(data.nrmc_data.nrmc_number != "" ? data.nrmc_data.nrmc_number : "");
                jQuery('#NRMCModal').find('#nrmc_date').val(data.nrmc_data.nrmc_date != "" ? data.nrmc_data.nrmc_date : "");
                jQuery('#NRMCModal').find('#nrmc_sequence').val(data.nrmc_data.nrmc_sequence != "" ? data.nrmc_data.nrmc_sequence : "");
 
                 if (zeroToEmpty(data.nrmc_data.nrmc_customer_id) !== '') {
                     getPendingCustomers().done(function () {
                            setTimeout(() => {
                                jQuery('#NRMCModal').find('#nrmc_customer_id').val(zeroToEmpty(data.nrmc_data.nrmc_customer_id)).trigger('change');
                            }, 100);
                    });
                 }
 
                // jQuery('#NRMCModal').find('#nrmc_customer_id').val(data.nrmc_data.nrmc_customer_id).trigger('change.select2');
 
                jQuery('#NRMCModal').find('#nrmc_transporter').val(data.nrmc_data.nrmc_transporter != "" ? data.nrmc_data.nrmc_transporter : "");
 
                jQuery('#NRMCModal').find('#nrmc_vehicle_number').val(data.nrmc_data.nrmc_vehicle_number != "" ? data.nrmc_data.nrmc_vehicle_number : "");
               
                jQuery('#NRMCModal').find('#nrmc_lr_no_date').val(data.nrmc_data.nrmc_lr_no_date != "" ? data.nrmc_data.nrmc_lr_no_date : "");
 
                jQuery('#NRMCModal').find('#nrmc_special_note').val(data.nrmc_data.nrmc_special_note != "" ? data.nrmc_data.nrmc_special_note : "");
 
 
                if (data.dc_details_data != "" && data.dc_details_data.length > 0) {
                    dc_details_data.push(...data.dc_details_data);
                    fillDCDetailsTable();
                }
 
                jQuery('#NRMCModal').find('#submitbtn').prop('disabled', false);
 
                const form = document.getElementById("commonNRMCForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#nrmc_sequence').focus();
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
 
// Reset button click for non returnable material challan modal
jQuery('#resetbtn').on('click', function () {
    dc_details_data = [];
    jQuery('#DCDetailTable tbody').empty();
 
    var formId = jQuery('#NRMCModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonNRMCForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
 
        jQuery('#nrmc_sequence').focus();
        jQuery('#commonNRMCForm #nrmc_customer_id').val('').trigger('change.select2');
        jQuery('#commonNRMCForm').find('.toggleModalBtn').prop('disabled', true);
        getLatestDCNo();
        getPendingCustomers();
    } else {
        fetchAndFillNonRetMatChallan(formId);
    }
});
 
// get data for edit
// jQuery('#dyntable tbody').on('click', '.edit-non_returnable_material_challan', function () {

//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#NRMCModal').find('#id').val(data["nrmc_id"]);
//     jQuery('#NRMCModal').modal('show');
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-non_returnable_material_challan",
//         type: 'GET',
//         data: "id=" + data["nrmc_id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {

//                 jQuery('#NRMCModal').find('#id').val(data.nrmc_data.nrmc_id != "" ? data.nrmc_data.nrmc_id : "");

//                 jQuery('#NRMCModal').find('#nrmc_number').val(data.nrmc_data.nrmc_number != "" ? data.nrmc_data.nrmc_number : "");
//                 jQuery('#NRMCModal').find('#nrmc_date').val(data.nrmc_data.nrmc_date != "" ? data.nrmc_data.nrmc_date : "");
//                 jQuery('#NRMCModal').find('#nrmc_sequence').val(data.nrmc_data.nrmc_sequence != "" ? data.nrmc_data.nrmc_sequence : "");

//                  if (zeroToEmpty(data.nrmc_data.nrmc_customer_id) !== '') {
//                      getPendingCustomers().done(function () {
//                             setTimeout(() => {
//                                 jQuery('#NRMCModal').find('#nrmc_customer_id').val(zeroToEmpty(data.nrmc_data.nrmc_customer_id)).trigger('change');
//                             }, 100);
//                     });
//                  }

//                 // jQuery('#NRMCModal').find('#nrmc_customer_id').val(data.nrmc_data.nrmc_customer_id).trigger('change.select2');

//                 jQuery('#NRMCModal').find('#nrmc_transporter').val(data.nrmc_data.nrmc_transporter != "" ? data.nrmc_data.nrmc_transporter : "");

//                 jQuery('#NRMCModal').find('#nrmc_vehicle_number').val(data.nrmc_data.nrmc_vehicle_number != "" ? data.nrmc_data.nrmc_vehicle_number : "");
                
//                 jQuery('#NRMCModal').find('#nrmc_lr_no_date').val(data.nrmc_data.nrmc_lr_no_date != "" ? data.nrmc_data.nrmc_lr_no_date : "");

//                 jQuery('#NRMCModal').find('#nrmc_special_note').val(data.nrmc_data.nrmc_special_note != "" ? data.nrmc_data.nrmc_special_note : "");


//                 if (data.dc_details_data != "" && data.dc_details_data.length > 0) {
//                     dc_details_data.push(...data.dc_details_data);
//                     fillDCDetailsTable();
//                 }

//                 jQuery('#NRMCModal').find('#submitbtn').prop('disabled', false);
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

jQuery('#NRMCModal').on('shown.bs.modal', function () {
    var hasAccess = jQuery('#commonNRMCForm').find('#has_access').val();
    var formId = jQuery('#commonNRMCForm').find('input[name="id"]').val();
    
    if (formId == "" || formId == undefined) {
         getPendingCustomers();
          getLatestDCNo();
    }else{
        setTimeout(() => {
            setSelect2Readonly('#nrmc_customer_id',true);
        }, 300);
    }
    const input = document.getElementById('nrmc_sequence');
    input?.focus();

});

jQuery('#PendingInwardForDCModal').on('show.bs.modal', function (e) {

    var usedItemParts = [];
    var usedItemPartsmins = [];
    var totalItemDisb = 0;
    var formId = jQuery('#commonNRMCForm').find('input[name="id"]').val();

    jQuery('#DCDetailTable tbody input[name="form_indx"]').each(function (indx) {
        var frmIndx = jQuery(this).val();
        var material_type = jQuery(this).closest('tr').find('input[name="material_type"]').val();
        console.log(material_type);

        if(material_type == 'Accepted'){
            var prItemId = dc_details_data[frmIndx].mins_id;
            if (prItemId != "" && prItemId != null) {
                usedItemPartsmins.push(Number(prItemId));
            }

        }else{
            var prItemId = dc_details_data[frmIndx].mid_id;
            if (prItemId != "" && prItemId != null) {
                usedItemParts.push(Number(prItemId));
            }

        }

       
    });
    function isItemUsed(pjitemId) {
        if (usedItemParts.includes(Number(pjitemId))) {
            totalItemDisb++;
            return true;
        }
        return false;
    }
    function isItemUsedmis(pjitemId) {
        if (usedItemPartsmins.includes(Number(pjitemId))) {
            totalItemDisb++;
            return true;
        }
        return false;
    }
    var totalEntry = 0;
    jQuery('#PendingForMaterialDCTable tbody tr').each(function (indx) {

        totalEntry++;
        var checkField = jQuery(this).find('input[name="mid_id[]"]');
        var formValue = jQuery(this).find('input[name="mid_id[]"]').data('from');
        var partId = jQuery(checkField).val();
        var minsId = jQuery(checkField).data('mins_id');

        if(formValue == 'Material Inspection'){
              // Check if it's the first checkbox
            if (formId == undefined) {
                if (dc_details_data.length > 0) {
                    var inUse = isItemUsedmis(minsId);
                } else {
                    if (indx === 0) {
                        var inUse = true;
                    }
                }
            } else {
                var inUse = isItemUsedmis(minsId);
            }

            if (inUse) {
                jQuery(checkField).prop('checked', true);
            } else {
                jQuery(checkField).prop('checked', false);
            }

        }else{
              // Check if it's the first checkbox
            if (formId == undefined) {
                if (dc_details_data.length > 0) {
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

        }
        
      

    });

});

jQuery('#NRMCModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#NRMCModal');
    thisModal.find("#id").val("");
    dc_details_data = [];

    jQuery('#DCDetailTable tbody').empty();
    jQuery('#commonNRMCForm').trigger("reset");
    thisModal.find('#nrmc_customer_id').val('').trigger('change.select2');

});

jQuery('#PendingInwardForDCModal').on('hide.bs.modal', function (e) {
     this.dataset.customHideFocus = 'true';
    setTimeout(() => {
        const input = document.getElementById('nrmc_transporter');
        input?.focus();
    }, 100);

});

// get the latest number
function getLatestDCNo() {
    jQuery.ajax({
        url: "get-latest_nrmc_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#nrmc_number').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#nrmc_sequence').val(data.number);
                jQuery('#nrmc_date').val(currentDate);
            } else {
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            jQuery('#nrmc_number').removeClass('file-loader');
            console.log('Field To Get Latest Order Acceptance No.!')
        }
    });
}

// onchange of dc sequence
jQuery('#commonNRMCForm').find('#nrmc_sequence').on('change',function () {
   checkSequence();
});

// check for duplication sequence
function checkSequence() {

    let thisForm = jQuery('#commonNRMCForm');
    let val = thisForm.find('#nrmc_sequence').val();

    if (val != "") {

        if (val > 0 == false) {
            toastr.error('Please Enter Valid DC No.');
            jQuery('#nrmc_sequence').parent().parent().parent('div.control-group').addClass('error');
            jQuery('#nrmc_sequence').focus();
            jQuery('#nrmc_sequence').val('');

        } else {
            jQuery('#nrmc_sequence').addClass('file-loader');
            jQuery('#nrmc_sequence').parent().parent().parent('div.control-group').removeClass('error');

            var urL = "check-nrmc_number_duplication?for=add&nrmc_sequence=" + val;

            var formId = jQuery('#commonNRMCForm').find('input[name="id"]').val();

            if (formId !== undefined) { //if form is edit
                urL = "check-nrmc_number_duplication?for=edit&nrmc_sequence=" + val + "&id=" + formId;
            }

            jQuery.ajax({
                url: urL,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    jQuery('#nrmc_sequence').removeClass('file-loader');
                    if (data.response_code == 0) {
                        toastr.error(data.response_message);
                        jQuery('#nrmc_sequence').val('');
                        const input = document.getElementById('nrmc_sequence'); input?.focus();
                    } else {
                        
                        jQuery('#nrmc_number').val(data.latest_no);
                        jQuery('#nrmc_sequence').val(val);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#nrmc_sequence').removeClass('file-loader');
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
        jQuery('#nrmc_number').val('');
        jQuery('#nrmc_sequence').val('');
    }

}

// onchange of customer 
jQuery('#commonNRMCForm').find('#nrmc_customer_id').on('change',function () {
   fillPendingDC();
});

// get pending customer from Inward/Inspection
function getPendingCustomers() {

    var formId = jQuery('#commonNRMCForm').find('input[name="id"]').val();
    var dc_customer_id = jQuery('#commonNRMCForm #nrmc_customer_id option:selected').val();

    if(formId != undefined && formId != ""){
        var Url = 'get-pending_customer_for_dc?&id='+ formId + "&dc_customer_id" + dc_customer_id;
    }else{
        var Url = 'get-pending_customer_for_dc';
    }
    return jQuery.ajax({
        url: Url,
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            let custHtml = '';
            custHtml += `<option value="">Select Customer</option> `;
            if (data.response_code == 1) {
                for (let indx in data.get_dc_customer) {
                    custHtml += `<option value="${data.get_dc_customer[indx].id}">${data.get_dc_customer[indx].customer}</option>`;

                }
                // jQuery('#nrmc_customer_id').empty().append(custHtml).select2().trigger('select2:select');
                jQuery('#commonNRMCForm').find('#nrmc_customer_id').empty().append(custHtml);

            } else {
                console.log(data.response_message)
            }
        },
    });
}

async function fillPendingDC() {

        let cusId = jQuery('#nrmc_customer_id option:selected').val();
        var formId = jQuery('#commonNRMCForm').find('input[name="id"]').val();

        var thisModal = jQuery('#PendingInwardForDCModal');
        var thisForm = jQuery('#commonNRMCForm');

        if (cusId != "") {
            if (formId == undefined) {
                var Url = "get-pending_inward_list_for_dc?dc_customer_id=" + cusId;
            } else {
                var Url = "get-pending_inward_list_for_dc?dc_customer_id=" + cusId + "&id=" + formId;
            }

            jQuery.ajax({
                url: Url,
                type: 'GET',
                headers: headerOpt,
                dataType: 'json',
                processData: false,
                success: function (data) {
                    if (data.response_code == 1 && data.inward_data.length > 0) {
                        // new code
                        var usedParts = [];
                        var totalDisb = 0;
                        var found = 0;

                        thisForm.find('#DCDetailTable tbody input[name="form_indx"]').each(function (indx) {
                            let frmIndx = jQuery(this).val();

                            let jbEorkOrderId = dc_details_data[frmIndx].mid_id;
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
                        if (data.inward_data.length > 0 && !jQuery.isEmptyObject(data.inward_data)) {
                            found = 1;

                            for (let idx in data.inward_data) {
                                var inUse = isUsed(data.inward_data[idx].mid_id);
                                var in_use = data.inward_data[idx].in_use == true ? 'readonly' : '';
                                totalEntry++;
                                let row = data.inward_data[idx];
                                var material_type = '';
                                if(row.material_type == 'Rejected'){
                                    material_type = 'Direct Rejected';
                                }else{
                                    material_type = row.material_type ?? '';
                                }

                                tblHtml += `<tr>
                                <td><input type="checkbox" class="checkbox-filter-remove" type="checkbox" name="mid_id[]" data-mins_id="${row.mins_id}" data-from="${row.material_from}" class="simple-check" id="mid_ids_${row.mid_id}"
                                    value="${row.mid_id}"/></td>

                                <td>${row.material_from != null ? row.material_from : ''}</td> 
                                <td>${row.mi_number != null ? row.mi_number : ''}</td> 
                                <td>${row.mi_date != null ? row.mi_date : ''}</td> 
                                <td>${row.mi_challan_number != null ? row.mi_challan_number : ''}</td> 
                                <td>${row.mi_challan_date != null ? row.mi_challan_date : ''}</td>
                                <td>${row.type_of_job != null ? row.type_of_job : ''}</td> 
                                <td>${row.job_description != null ? row.job_description : ''}</td>
                                <td>${row.mid_test_method_id != null ? row.mid_test_method_id : ''}</td>
                                <td>${material_type}</td>
                                <td>${row.pend_dc_qty != null ? parseFloat(row.pend_dc_qty).toFixed(3) : ''}
                                 </td> 
                                </tr>`;

                            }

                        } else {
                            tblHtml += `<tr class="centeralign" id="noPendingPo">
                                            <td colspan="11">No Pending Material Available</td>
                                        </tr>`;
                        }

                        var $table = jQuery("#PendingInwardForDCModal").find('#PendingForMaterialDCTable');
                        if (jQuery.fn.DataTable.isDataTable($table)) {
                            $table.DataTable().clear().destroy();
                        }
                        jQuery('#PendingForMaterialDCTable tbody').empty().append(tblHtml);
                        
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
                        jQuery('.toggleModalBtn').prop('disabled', false);

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

        } else {
            jQuery('.toggleModalBtn').prop('disabled', true);
        }
}

// after checked inward from pending modal getting data
$('#addPendigDCForm').on('submit', function (e) {

    e.preventDefault();

    jQuery('#full-page-loader')
        .removeClass('hidden-loader')
        .addClass('loader-progress-whole-page');

    jQuery('#PendingInwardForDCModal')
        .find('#submitbtn')
        .prop('disabled', true);

    let chkArr = [];
    let mid_ids = [];
    let mins_ids = [];


    var formId = jQuery('#commonNRMCForm').find('input[name="id"]').val();

    jQuery("#addPendigDCForm")
        .find("[id^='mid_ids_']:checked")
        .each(function () {
            chkArr.push(jQuery(this).val());

            let value = jQuery(this).val();
            let fromType = jQuery(this).data('from'); // data-from
            let mins_id = jQuery(this).data('mins_id'); // data-from

            if (fromType === "Material Inward") {
                mid_ids.push(value);
            } else {
                mins_ids.push(mins_id);
            }
        });

    // No checkbox selected
    if (chkArr.length === 0) {
         toastr.error('Select At least One Material From Pending');

        jQuery('#full-page-loader')
            .removeClass('loader-progress-whole-page')
            .addClass('hidden-loader');

        jQuery('#PendingInwardForDCModal')
            .find('#submitbtn')
            .prop('disabled', false);

        return;
    }

    if(formId != undefined && formId != ""){
        var pend_url = "get-pending_inwad_for_dc?id=" + formId;
    }else{
        var pend_url = "get-pending_inwad_for_dc";
    }

    jQuery.ajax({
        url: pend_url,
        type: 'GET',
        // data: { mid_ids: chkArr.join(',') },
        data: {
            mid_ids: mid_ids.join(','), 
            mins_ids: mins_ids.join(',')
        },
        dataType: 'json',
        success: function (data) {

            if (data.response_code == 1) {

                if (data.inward_data && data.inward_data.length > 0) {
                    dc_details_data = [];
                    for (let ind in data.inward_data) {
                        dc_details_data.push(data.inward_data[ind]);
                    }
                    fillDCDetailsTable(data.inward_data);
                } else {
                    fillDCDetailsTable([]);
                }

                jQuery("#PendingInwardForDCModal").modal('hide');
            } else {
                fillDCDetailsTable([]);
            }

            jQuery('#PendingInwardForDCModal')
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

            jQuery('#PendingInwardForDCModal')
                .find('#submitbtn')
                .prop('disabled', false);

            jQuery('#full-page-loader')
                .removeClass('loader-progress-whole-page')
                .addClass('hidden-loader');
        }
    });
});

jQuery('#checkall-inwd_data').click(function () {
    if (jQuery(this).is(':checked')) {
        jQuery("#PendingInwardForDCModal").find("[id^='mid_ids_']:not(.in-use)").prop('checked', true).trigger('change');
    } else {
        jQuery("#PendingInwardForDCModal").find("[id^='mid_ids_']:not(.in-use)").prop('checked', false).trigger('change');
    }
});



// edit time fill quotation table start
function fillDCDetailsTable() {
    let thisModal = jQuery('#NRMCModal');
    let  tblHtml = ``;
    if (dc_details_data.length > 0 && !jQuery.isEmptyObject(dc_details_data)) {
        for (let key in dc_details_data) {
            let row = dc_details_data[key];
            var formIndx = dc_details_data.indexOf(dc_details_data[key]);

            var material_type = '';
            if(row.material_type == 'Rejected'){
                material_type = 'Direct Rejected';
            }else{
                material_type = row.material_type ?? '';
            }

            let nrmcd_id = row.nrmcd_id ?? '';
            let job_description = row.job_description ?? '';
            let type_of_job = row.type_of_job ?? '';
            let part = row.part ?? '';
            let test_method = row.mid_test_method_id ?? '';
            let material_from = row.material_from ?? '';
            let mi_challan_number = row.mi_challan_number ?? '';
            let mi_challan_date = row.mi_challan_date ?? '';
            let nrmcd_qty = row.nrmcd_qty ?? '';
            let pend_dc_qty = row.pend_dc_qty ?? ''; 
            let unit = row.unit ?? '';
            let mid_remark = row.mid_remark ?? '';
            let mid_id = row.mid_id ?? '';
            let mins_id = row.mins_id ?? '';
            

            tblHtml += `<tr>`;
            tblHtml += `<td>
                <input type="hidden" name="form_indx" value="${formIndx}"/>
                <input type="hidden" id="mid_id" name="mid_id" value="${mid_id}">
                <input type="hidden" name="mins_id" value="${mins_id}">
                <input type="hidden" name="nrmcd_id" value="${nrmcd_id}">
                <input type="hidden" name="material_type" value="${material_type}">${material_type}
            </td>`;
            tblHtml += `<td>${mi_challan_number}</td>`;
            tblHtml += `<td>${mi_challan_date}</td>`;
            tblHtml += `<td>${type_of_job}</td>`;
            tblHtml += `<td>${job_description}</td>`;
            tblHtml += `<td>${part}</td>`;
            tblHtml += `<td>${test_method}</td>`;
            tblHtml += `<td><input type="hidden" class="pend_dc_qty" name="pend_dc_qty" id="pend_dc_qty" value="${parseFloat(pend_dc_qty).toFixed(3)}" > ${parseFloat(pend_dc_qty).toFixed(3)}</td>`;
            if(row.nrmcd_id > 0){
                tblHtml += `<td><input type="text" name="nrmcd_qty" class="form-control nrmcd_qty"  value="${parseFloat(nrmcd_qty).toFixed(3)}" onblur="formatPoints(this,3)" ></td>`;
            }else{
                tblHtml += `<td><input type="text" name="nrmcd_qty" class="form-control nrmcd_qty" value="${parseFloat(pend_dc_qty).toFixed(3)}" onblur="formatPoints(this,3)" ></td>`;
            }
            // tblHtml += `<td><input type="text" name="nrmcd_qty" class="form-control nrmcd_qty" value="${parseFloat(mid_qty).toFixed(3)}"></td>`;
            tblHtml += `<td>${unit}</td>`;
            tblHtml += `<td>${mid_remark}</td>`;
            tblHtml += `</tr>`;
        }

        jQuery('#DCDetailTable tbody').empty();
        jQuery('#DCDetailTable tbody').append(tblHtml);

        // Add event listener for nrmcd_qty inputs
        jQuery('.nrmcd_qty').on('change', function() {
            let maxQty = parseFloat(jQuery(this).closest('tr').find('.pend_dc_qty').val());
            let val = parseFloat(jQuery(this).val());
            if (val > maxQty) {
                toastr.error(`DC Qty. cannot exceed pending Qty. ${maxQty}`);
                jQuery(this).val(maxQty);
                jQuery(this).focus();

            } else if (val < 0 || isNaN(val)) {
                jQuery(this).val(0);
                jQuery(this).removeClass('input-error'); // add red border

            }
        });


    } else {
        jQuery('#DCDetailTable tbody').html(`
            <tr class="centeralign">
                <td colspan="11">No Pending Material Available</td>
            </tr>
        `);
    }

}


// Main form submit Stary
$('#commonNRMCForm').on('submit', function (e) {
    
    e.preventDefault();
    let form = this;

   
    var dateValue = document.getElementById("nrmc_date").value.trim();
    if (!isValidDate(dateValue)) {
        toastr.error("Please Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#NRMCModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#NRMCModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#NRMCModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    // Before proceeding, validate all rows
    let invalid = false;
    jQuery('#DCDetailTable tbody tr').each(function() {
        let pendQty = parseFloat(jQuery(this).find('.pend_dc_qty').val());
        let nrmcdQty = parseFloat(jQuery(this).find('.nrmcd_qty').val());

        if (nrmcdQty > pendQty) {
            invalid = true;
            toastr.error(`DC Qty. (${nrmcdQty}) cannot exceed pending Qty. ${pendQty}`);
            jQuery(this).find('.nrmcd_qty').val(pendQty);
        } else if (nrmcdQty < 0 || isNaN(nrmcdQty)) {
            invalid = true;
            toastr.error(`Qty. cannot be negative or empty`);
        }
    });

    if (invalid) {
        return; // stop submission
    }

    jQuery('#NRMCModal').find('#submitbtn').prop('disabled', true);
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');

    // if (dc_details_data.length === 0) {
    //     toastr.error('Please add at least one valid row.');
    //     return;
    // }


    dc_details_data = [];
    jQuery('#DCDetailTable tbody tr').each(function () {

        var mid_id = jQuery(this).find('input[name="mid_id"]').val();
        var nrmcd_id = jQuery(this).find('input[name="nrmcd_id"]').val();
        var mins_id = jQuery(this).find('input[name="mins_id"]').val();
        var nrmcd_qty = jQuery(this).find('input[name="nrmcd_qty"]').val();
        var material_type = jQuery(this).find('input[name="material_type"]').val();
        

        // OPTIONAL: skip rows with zero opening qty
        if (parseFloat(nrmcd_qty) > 0) {
            dc_details_data.push({
                nrmcd_id: nrmcd_id,
                mid_id: mid_id,
                mins_id: mins_id,
                nrmcd_qty: nrmcd_qty,
                material_type : material_type
            });
        }
    });


    var formId = jQuery('#NRMCModal').find('#commonNRMCForm').find('#id').val();
    var formUrl = formId != undefined && formId != "" ? "update-non_returnable_material_challan" : "store-non_returnable_material_challan";

     if (dc_details_data.length > 0 && !jQuery.isEmptyObject(dc_details_data)) {
         jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
         jQuery('#NRMCModal').find('#submitbtn').prop('disabled', true);
         var data = new FormData(form);
         data.append('dc_details_data', JSON.stringify(dc_details_data ?? []));
         data.append('_token', $('meta[name="csrf-token"]').attr('content'));

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
                        jQuery('#NRMCModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formId == undefined || formId == "") {
                        function nextFn() {
                            document.getElementById("commonNRMCForm").reset();
                            const form = document.getElementById("commonNRMCForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            jQuery('#nrmc_sequence').focus();
                            dc_details_data = [];
                            jQuery('#commonNRMCForm #nrmc_customer_id').val('').trigger('change.select2');
                            jQuery('#DCDetailTable tbody').empty();
                            jQuery('#commonNRMCForm').find('.toggleModalBtn').prop('disabled', true);
                            getLatestDCNo();
                            getPendingCustomers();
                        }
                        toastSuccess(data.response_message, nextFn);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#NRMCModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#NRMCModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#NRMCModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#NRMCModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#NRMCModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        toastr.error('Please Add At Least One ');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#NRMCModal').find('#submitbtn').prop('disabled', false);
    }
});