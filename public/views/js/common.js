function getRelationData() {
    let stateId_val = jQuery('#state_id option:selected').val();
    if (stateId_val != "" && stateId_val !== undefined) {
        jQuery.ajax({
            url: "city-relation-field?state_id=" + stateId_val,
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                if (data.response_code == 1) {
                    jQuery('#commonCityForm').find('#country_name').val(data.relation_data.country_name);
                    jQuery('#commonCityForm').find('#state_code').val(data.relation_data.state_code);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#city").removeClass('file-loader');
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
    }else{
        jQuery("#commonCityForm").find('#country_name').val('');
        jQuery("#commonCityForm").find('#state_code').val('');
    }
}

jQuery('#state_id').on('change', function() {
    getRelationData();
});

function getCustomerCityRelationData(e) {
    let thisForm = jQuery('#commonCustomerForm');
    let thisVal = jQuery('#city_id option:selected').val();
    if (thisVal != "") {
        jQuery('#country').addClass('file-loader');
        jQuery('#state').addClass('file-loader');
        jQuery('#state_code').addClass('file-loader');
        jQuery.ajax({
            url: "customer-relation-field?city_id=" + thisVal,
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery('#country').removeClass('file-loader');
                jQuery('#state').removeClass('file-loader');
                jQuery('#state_code').removeClass('file-loader');
                if (data.response_code == 1) {
                    if (data.relation_data.country_id == "1") {
                        jQuery('#commonCustomerForm').find('#pan').prop('disabled', false);
                        jQuery('#commonCustomerForm').find('#gstin').prop('disabled', false);
                    } else {
                        jQuery('#commonCustomerForm').find('#pan').prop('disabled', true).val('');
                        jQuery('#commonCustomerForm').find('#gstin').prop('disabled', true).val('');
                    }
                    jQuery('#commonCustomerForm').find('#country').val(data.relation_data.country_name);
                    jQuery('#commonCustomerForm').find('#state').val(data.relation_data.state);
                    jQuery('#commonCustomerForm').find('#state_code').val(data.relation_data.state_code);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery('#country').removeClass('file-loader');
                jQuery('#state').removeClass('file-loader');
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
    } else {
        jQuery('#commonCustomerForm').find('#pan').prop('disabled', false).val('');
        jQuery('#commonCustomerForm').find('#gstin').prop('disabled', false).val('');
        jQuery('#commonCustomerForm').find('#state').prop('disabled', true).val('');
        jQuery('#commonCustomerForm').find('#country').prop('disabled', true).val('');
        jQuery('#commonCustomerForm').find('#state_code').prop('disabled', true).val('');
    }
}

jQuery('#commonCustomerForm #city_id').on('change', function() {
    getCustomerCityRelationData();
});

function getSupplierCityRelationData(e) {
    let thisForm = jQuery('#commonSupplierForm');
    let thisVal = jQuery('#city_id option:selected').val();
    if (thisVal != "") {
        jQuery('#country').addClass('file-loader');
        jQuery('#state').addClass('file-loader');
        jQuery('#state_code').addClass('file-loader');
        jQuery.ajax({
            url: "customer-relation-field?city_id=" + thisVal,
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery('#country').removeClass('file-loader');
                jQuery('#state').removeClass('file-loader');
                jQuery('#state_code').removeClass('file-loader');
                if (data.response_code == 1) {
                    if (data.relation_data.country_id == "1") {
                        jQuery('#commonSupplierForm').find('#pan').prop('disabled', false);
                        jQuery('#commonSupplierForm').find('#gstin').prop('disabled', false);
                    } else {
                        jQuery('#commonSupplierForm').find('#pan').prop('disabled', true).val('');
                        jQuery('#commonSupplierForm').find('#gstin').prop('disabled', true).val('');
                    }
                    jQuery('#commonSupplierForm').find('#country').val(data.relation_data.country_name);
                    jQuery('#commonSupplierForm').find('#state').val(data.relation_data.state);
                    jQuery('#commonSupplierForm').find('#state_code').val(data.relation_data.state_code);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery('#country').removeClass('file-loader');
                jQuery('#state').removeClass('file-loader');
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
    } else {
        jQuery('#commonSupplierForm').find('#pan').prop('disabled', false).val('');
        jQuery('#commonSupplierForm').find('#gstin').prop('disabled', false).val('');
        jQuery('#commonSupplierForm').find('#state').prop('disabled', true).val('');
        jQuery('#commonSupplierForm').find('#country').prop('disabled', true).val('');
        jQuery('#commonSupplierForm').find('#state_code').prop('disabled', true).val('');
    }
}

jQuery('#commonSupplierForm #city_id').on('change', function() {
    getSupplierCityRelationData();
});


// dc
// function getInquiryKindAttRelationData(e) {
//     let thisForm = jQuery('#commonInquiryForm');
//     let thisVal = thisForm.find('#inq_customer_id option:selected').val();
//     if (thisVal != "") {
//         jQuery.ajax({
//             url: "inquiry_relation_field?customer_id=" + thisVal,
//             type: 'GET',
//             dataType: 'json',
//             processData: false,
//             success: function (data) {
//                 var kindDrpHtml = `<option value="">Select Kind Attn.</option>`;
//                 if (data.response_code == 1 && data.kind_attention.length) {
//                     for (let indx in data.kind_attention) {
//                         let item = data.kind_attention[indx];
//                         let displayText = item.contact_person;
//                         if (item.contact_email && item.contact_email.trim() !== "") {
//                             displayText += " - " + item.contact_email;
//                         }

//                         if (item.contact_mobile_no && item.contact_mobile_no.trim() !== "") {
//                             displayText += " - " + item.contact_mobile_no;
//                         }

//                         kindDrpHtml += `<option value="${item.id}">${displayText}</option>`;
//                     }
//                 }

//                 // if (data.response_code == 1) {
//                 //     var kindDrpHtml = `<option value="">Select Kind Attn.</option>`;
//                 //     if (data.kind_attention.length) {
//                 //         console.log(data.kind_attention);
//                 //         for (let indx in data.kind_attention) {
//                 //             kindDrpHtml += `<option value="${data.kind_attention[indx].id}">${data.kind_attention[indx].contact_person+" - "+data.kind_attention[indx].contact_email+" - "+data.kind_attention[indx].contact_mobile_no} </option>`;
//                 //         }
//                 //     }
//                 // }
//                 thisForm.find('#inq_kind_attn_id').empty().append(kindDrpHtml);
//             }
//         });
//     } else {
//         var kindDrpHtml = `<option value="">Select Kind Attn.</option>`;
//         thisForm.find('#inq_kind_attn_id').empty().append(kindDrpHtml);
//     }
// }

// jQuery('#commonInquiryForm #inq_customer_id').on('change', function() {
//     getInquiryKindAttRelationData();
// });

jQuery('#commonLocationForm #location_city_id').on('change', function() {
    getLocationCityRelationData();
});

function getLocationCityRelationData(e) {
    let thisForm = jQuery('#commonLocationForm');
    let thisVal = jQuery('#location_city_id option:selected').val();
    if (thisVal != "") {
        jQuery('#location_country_id').addClass('file-loader');
        jQuery('#location_state_id').addClass('file-loader');
        jQuery.ajax({
            url: "location-relation-field?location_city_id=" + thisVal,
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery('#location_country_id').removeClass('file-loader');
                jQuery('#location_state_id').removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#commonLocationForm').find('#country_id').val(data.relation_data.country_id);
                    jQuery('#commonLocationForm').find('#state_id').val(data.relation_data.state_id);
                    jQuery('#commonLocationForm').find('#location_country_id').val(data.relation_data.country_name);
                    jQuery('#commonLocationForm').find('#location_state_id').val(data.relation_data.state);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery('#location_country_id').removeClass('file-loader');
                jQuery('#location_state_id').removeClass('file-loader');
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
    } else {
        jQuery('#commonLocationForm').find('#location_state_id').prop('disabled', true).val('');
        jQuery('#commonLocationForm').find('#location_country_id').prop('disabled', true).val('');
    }
}