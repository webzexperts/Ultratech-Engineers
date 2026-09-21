let location_data = [];
var selectedUnits = [];

// Edit user row click
jQuery('#dyntable tbody').on('click', '.edit_user', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillUser(data["id"]);
    }
});

// Function to fetch and fill user data
function fetchAndFillUser(id) {
    if (!id) return;
    selectedUnits = [];
    jQuery('#UserModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-user",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.user != null) {
                jQuery('#UserModal').find('#user_name').val(data.user.user_name);
                jQuery('#UserModal').find('#person_name').val(data.user.person_name);
                jQuery('#UserModal').find('#designation').val(data.user.designation);
                jQuery('#UserModal').find('#phone_no').val(data.user.phone_no);
                jQuery('#UserModal').find('#email').val(data.user.email);
                jQuery('#UserModal').find('#user_type').val(data.user.user_type).trigger("change.select2");
                jQuery('#UserModal').find('#status').val(data.user.status).trigger("change.select2");
                jQuery('#UserModal').find('#allow_production_back_days_entry').val((data.user.allow_production_back_days_entry) ?? 0);
                // for(k in data.user_details_data) {
                //     jQuery('#unit_ids_'+data.user_details_data[k].company_unit_id).prop('checked',true);
                // }     

                for (var k in data.user_details_data) {
                    selectedUnits.push(data.user_details_data[k].location_id);
                }

                function isUsed(pjId) {
                    if (selectedUnits.includes(Number(pjId))) {
                        return true;
                    }
                    return false;
                }
                // setTimeout(function () {
                //     jQuery("[id^='location_ids_']").prop('checked', false);
                //     for (var i = 0; i < selectedUnits.length; i++) {
                //         jQuery('#location_ids_' + selectedUnits[i]).prop('checked', true);
                //     }
                // }, 300);
                location_data = [];
                if (selectedUnits.length > 0) {
                    getLocationData().done(function () {
                        selectedUnits.forEach(id => {
                            $('#location_ids_' + id).prop('checked', true);
                        });
                    });
                } else {
                    getLocationData(); // still load table even if no selection
                }

                // jQuery('#LocationTable tbody tr').each(function (indx) {
                //     var checkField = jQuery(this).find('input[name="location_ids[]"]');
                //     var partId = jQuery(checkField).val();
                //     var inUse = isUsed(partId);
                //     console.log(inUse,'inUse');

                //     if (inUse) {
                //         jQuery(checkField).prop('checked', true);
                //     } else {
                //         jQuery(checkField).prop('checked', false);
                //     }

                // });
                // checkUserName(data.user.user_name, data.user.id);
                jQuery('#UserModal').find('#add_new').show();
                jQuery('#UserModal').find('#id').val(data.user.id);

                const form = document.getElementById("commonUserForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#UserModal').find('#user_name').focus();
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

// Reset button click for user modal
jQuery('#resetbtn').on('click', function () {
    lastVerifiedUserName = '';
    var formId = jQuery('#UserModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonUserForm").reset();
        const form = document.getElementById("commonUserForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        jQuery('#user_name').focus();
        jQuery('#UserModal #user_type').val('').trigger("change.select2");
        jQuery('#UserModal').find('#status').val('Active').trigger('change');
        jQuery('#UserModal').find('#allow_production_back_days_entry').val('0');
    } else {
        fetchAndFillUser(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_user', function () {
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#UserModal').modal('show');
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-user",
//         type: 'GET',
//         data: "id=" + data["id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 if (data.user != null) {
//                     jQuery('#UserModal').find('#user_name').val(data.user.user_name);
//                     jQuery('#UserModal').find('#person_name').val(data.user.person_name);
//                     jQuery('#UserModal').find('#designation').val(data.user.designation);
//                     jQuery('#UserModal').find('#mobile_no').val(data.user.mobile_no);
//                     jQuery('#UserModal').find('#email').val(data.user.email);
//                     jQuery('#UserModal').find('#user_type').val(data.user.user_type).trigger("change.select2");
//                     jQuery('#UserModal').find('#status').val(data.user.status).trigger("change.select2");
//                     // for(k in data.user_details_data) {
//                     //     jQuery('#unit_ids_'+data.user_details_data[k].company_unit_id).prop('checked',true);
//                     // }
//                     var selectedUnits = [];
//                     for (var k in data.user_details_data) {
//                         selectedUnits.push(data.user_details_data[k].company_unit_id);
//                     }
//                     setTimeout(function () {
//                         jQuery("[id^='unit_ids_']").prop('checked', false);
//                         for (var i = 0; i < selectedUnits.length; i++) {
//                             jQuery('#unit_ids_' + selectedUnits[i]).prop('checked', true);
//                         }
//                     }, 300);
//                     // checkUserName(data.user.user_name, data.user.id);
//                     jQuery('#UserModal').find('#id').val(data.user.id);
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

$('#commonUserForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#UserModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this; // 'this' refers to the form element in the event handler

    // Bootstrap 5 validation check
    if (!form.checkValidity()) { // 'form' is now an actual DOM element
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#UserModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#UserModal').find('#commonUserForm').find('#id').val();
    if (formId == undefined || formId == "") {
        if (!password.value.trim()) {
            password.classList.add("is-invalid");
            document.getElementById("password_error").innerHTML = "Enter Password";
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#UserModal').find('#submitbtn').prop('disabled', false);
            return;
        }
    }

    if (email.value.trim() !== "") {
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
            email.classList.add("is-invalid");
            document.getElementById("email_error").innerHTML = "Enter Valid Email ID.";
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#UserModal').find('#submitbtn').prop('disabled', false);
            // document.getElementById("email_error").innerHTML = "Please enter a valid email address";
            return false;
        }
    }
    if ($(".number").hasClass("is-invalid")) {
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#UserModal').find('#submitbtn').prop('disabled', false);
        return false;
    }

    let chkArr = [];
    $("input[name='location_ids[]']:checked").each(function () {
        chkArr.push($(this).val());
    });
    if (chkArr.length === 0) {
        let defLoc = $('#default_location_id').val();
        if (defLoc) {
            chkArr.push(defLoc);
        }
    }

    // Validation
    if (chkArr.length === 0) {
        toastr.error("Select At Least One Location");
        // toastr.error("Please select at least one location");
        jQuery('#full-page-loader').addClass('hidden-loader').removeClass('loader-progress-whole-page');
        jQuery('#submitbtn').prop('disabled', false);
        return;
    }

    var user_name = $("#user_name").val();
    var formUrl = formId != undefined && formId != "" ? "update-user" : "store-user";
    var user_nameUrl = formId != undefined && formId != "" ? "verify-user_name?user_name=" + encodeURIComponent(user_name) + "&id=" + formId : "verify-user_name?user_name=" + encodeURIComponent(user_name);
    let formData = new FormData(form);
    formData.append('location_data', JSON.stringify(chkArr));
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    if (user_name != '' && user_name != undefined) {
        $.ajax({
            url: user_nameUrl,
            type: 'GET',
            dataType: 'json',
            // processData: false,
            // headers: {
            //     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            // },
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#UserModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#UserModal').find('#submitbtn').prop('disabled', false);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        const form = document.getElementById("commonUserForm");
                                        form.reset();

                                        const inputs = form.querySelectorAll("input, select");
                                        inputs.forEach((input) => {
                                            input.classList.remove("is-valid");
                                            input.classList.remove("is-invalid");
                                        });

                                        form.classList.remove('was-validated');
                                        jQuery('#user_name').focus();
                                        jQuery('#UserModal #user_type').val('').trigger("change.select2");
                                        jQuery('#UserModal').find('#status').val('Active').trigger('change');
                                        jQuery('#UserModal').find('#allow_production_back_days_entry').val('0');
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#UserModal').find('#submitbtn').prop('disabled', false);
                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#UserModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#UserModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#UserModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#UserModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#UserModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestUserName(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        jQuery("#user_name").addClass('file-loader');
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "user_name-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
                jQuery("#user_name").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#user_name_list').html(data.usernameList);
                } else {
                    toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#user_name").removeClass('file-loader');
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
}

jQuery(document).on('click', '#user_name_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#user_suggestion').val(suggest);
    var hidden = jQuery('#user_suggestion').val();
    var suggestion_list = jQuery('#user_name_list').html;
    jQuery('#UserModal').find('#user_name').val(hidden)

    var user_name = hidden;
    if (suggestion_list != '') {
        checkUserName(user_name);
    }
    jQuery('#user_name_list').html('');
});



function suggestDesignation(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        jQuery("#designation").addClass('file-loader');
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "admin_designation-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
                jQuery("#designation").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#designation_list').html(data.designationList);
                } else {
                    toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#designation").removeClass('file-loader');
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
}

jQuery(document).on('click', '#designation_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#designation_suggestion').val(suggest);
    var hidden = jQuery('#designation_suggestion').val();
    var suggestion_list = jQuery('#designation_list').html;
    jQuery('#UserModal').find('#designation').val(hidden)

    var designation = hidden;
    if (suggestion_list != '') {
    }
    jQuery('#designation_list').html('');
});

let lastVerifiedUserName = '';

jQuery(document).on('blur', '#user_name', function () {
    let user_name = jQuery(this).val().trim();

    if (user_name === '') return;

    if (user_name !== lastVerifiedUserName) {
        lastVerifiedUserName = user_name;
        checkUserName(user_name);
    }
});

// jQuery(document).on('input', '#user_name', function () {
//     lastVerifiedUserName = '';
// });
// Check Duplicate HSN Code
function checkUserName(username) {
    var id = jQuery('#UserModal').find('#commonUserForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-user_name?user_name=" + encodeURIComponent(username) + "&id=" + id : "verify-user_name?user_name=" + encodeURIComponent(username);
    var uInput = jQuery('#commonUserForm').find('#user_name');
    // Reset previous validation state
    jQuery.ajax({
        url: formUrl,
        type: 'GET',
        dataType: 'json',
        processData: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (data) {
            if (data.response_code == 1) {
                toastr.error(data.response_message);
            }
        }
    });
}

function verifyUser() {
    var username = jQuery('#user_name').val();
    var suggestion_list = jQuery('#user_name_list').html;

    if (suggestion_list != '') {
        checkUserName(username);
    }
}

jQuery('#UserModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#UserModal');
    thisForm.find('#id').val('');
    lastVerifiedUserName = '';
    document.getElementById("commonUserForm").reset();
    // window.location.reload();
    location_data = [];
    jQuery('#UserModal').find('#status').val('Active').trigger('change');
    jQuery('#UserModal').find('#add_new').hide();
});

jQuery('#UserModal').on('shown.bs.modal', function () {
    var dt = jQuery('#LocationTable').DataTable();
    fixDataTableColumnsUntilAdjusted(dt);
    const input = document.getElementById('user_name');
    input?.focus();

    location_data = [];
    jQuery('#LocationTable tbody').empty();

    var formIdblank = jQuery('#UserModal').find('#commonUserForm').find('#id').val();
    if (formIdblank == "") {
        jQuery('#UserModal').find('#status').val('Active').trigger('change');
        getLocationData();
    }
    if (formIdblank && formIdblank !== "") {
        jQuery('#UserModal').find('#add_new').show();
    } else {
        jQuery('#UserModal').find('#add_new').hide();
    }
});
jQuery('#UserModal').on('click', '#add_new', function () {
    jQuery('#UserModal').find('#id').val('');
    lastVerifiedUserName = '';
    document.getElementById("commonUserForm").reset();
    const form = document.getElementById("commonUserForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#user_name').focus();
    jQuery('#UserModal').find('#add_new').hide();
    jQuery('#UserModal').find('#allow_production_back_days_entry').val('0');
});

// $(document).ready(function () {
//     getLocationData();
// });

function getLocationData() {
    return jQuery.ajax({
        url: "get-location_data",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                location_data = [];
                if (data.location_data.length > 0 && !jQuery.isEmptyObject(data.location_data)) {
                    for (let ind in data.location_data) {
                        location_data.push(data.location_data[ind]);
                    }
                    fillLocationTable();
                } else {
                    location_data = [];
                    fillLocationTable();
                }
            }
        },
        error: function (jqXHR) {
            if (jqXHR.status == 401) {
                console.log(jqXHR.statusText);
            } else {
                console.log('Something went wrong!');
            }
            console.log(JSON.parse(jqXHR.responseText));
        }
    });
}

function fillLocationTable() {
    var $table = jQuery('#LocationTable');
    var tblHtml = ``;
    if (location_data.length > 0) {
        let sr_no = 0;
        for (let idx in location_data) {
            ++sr_no;
            tblHtml += `<tr>
                <td>
                    <input type="checkbox" name="location_ids[]" id="location_ids_${location_data[idx].location_id}" class="simple-check" value="${location_data[idx].location_id}"/>
                </td>
                <td>${(location_data[idx].location_name === 'null' || location_data[idx].location_name == null) ? '' : location_data[idx].location_name}</td>
                <td>${(location_data[idx].location_type === 'null' || location_data[idx].location_type == null) ? '' : location_data[idx].location_type}</td>
                <td>${(location_data[idx].location_code === 'null' || location_data[idx].location_code == null) ? '' : location_data[idx].location_code}</td>
                <td>${(location_data[idx].city === 'null' || location_data[idx].city == null) ? '' : location_data[idx].city}</td>
                <td>${(location_data[idx].state === 'null' || location_data[idx].state == null ? '' : location_data[idx].state)}</td>
                <td>${(location_data[idx].location_nabl_applicable === 'null' || location_data[idx].location_nabl_applicable == null) ? '' : location_data[idx].location_nabl_applicable}</td>
                <td>${(location_data[idx].nabl_location === 'null' || location_data[idx].nabl_location == null) ? '' : location_data[idx].nabl_location}</td>
                <td>${location_data[idx].location_status}</td>
            </tr>`;
        }
    } else {
        tblHtml += `<tr class="centeralign" id="noPendingPo">
            <td colspan="9">No Location Data Found</td>
        </tr>`;
    }

    var $table = jQuery("#UserModal").find('#LocationTable');
    if (jQuery.fn.DataTable.isDataTable($table)) {
        $table.DataTable().clear().destroy();
    }
    jQuery('#LocationTable tbody').empty().append(tblHtml);
    var $new = $table.DataTable({
        paging: false,
        searching: false,
        info: false,
        // paging: true,
        // searching: true,
        // "oLanguage": {
        //     "sSearch": "Search :"
        // },
        // dom: 'lrtip',
        "sScrollX": true,
        "sScrollX": "100%",
        "sScrollXInner": "110%",
        "bScrollCollapse": true,
    });
    fixDataTableColumnsUntilAdjusted($new);
}

jQuery('#checkall_location').click(function () {
    if (jQuery(this).is(':checked')) {
        jQuery("[id^='location_ids_']").prop('checked', true);
    } else {
        jQuery("[id^='location_ids_']").prop('checked', false);
    }
});

let chkArr = [];
$("#LocationTable").find("input[name='location_ids[]']:checked").each(function () {
    chkArr.push($(this).val());
});