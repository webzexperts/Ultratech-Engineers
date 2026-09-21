$(document).ready(function () {

    // Non-HO hoy to readonly set karo
    if ($('#assign_location_id').hasClass('readonly')) {
        setTimeout(function () {
            setSelect2Readonly('#assign_location_id', true);
        }, 200);
    }

    // page load par data load karo
    let location_id = $('#assign_location_id').val();
    if (location_id) {
        getAssignDataBasedOnLocation(location_id);
    }

    // change event
    $('#assign_location_id').on('change', function () {
        var location_id = $(this).val();
        getAssignDataBasedOnLocation(location_id);
    });

});
$('#assign_location_id').trigger('change');
function getAssignDataBasedOnLocation(location_id) {
    jQuery.ajax({
        url: 'get-assign-data-based-on-location?location_id=' + location_id,
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            var tblHtml = ``;
            if (data.response_code == 1 && data.assign_data.length > 0) {

                if (data.assign_data.length > 0 && !jQuery.isEmptyObject(data.assign_data)) {
                    found = 1;

                    for (let idx in data.assign_data) {
                        let addRevisionHtml = '';
                        if (window.hasAddAccess) {
                            addRevisionHtml = `<li>
                                                    <a class="dropdown-item edit-item-btn" onclick="revisionAssignDetails(this)">
                                                        <i class="ri-add-box-fill align-bottom me-2 text-muted" id="rev_a"></i>
                                                        Add / Revision
                                                    </a>
                                                </li>`;
                        }

                        let editHtml = '';
                        if (window.hasEditAccess && data.assign_data[idx].assign_id != null) {
                            editHtml = `<li>
                                            <a class="dropdown-item edit-item-btn" onclick="editAssignDetails(this)">
                                                <i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i>
                                                Edit
                                            </a>
                                        </li>`;
                        }

                        let actionCell = `<td>
                                            <div>
                                                <a href="#" role="button" id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ri-more-2-fill"></i>
                                                </a>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                                                    ${addRevisionHtml}
                                                    ${editHtml}
                                                </ul>
                                            </div>
                                        </td>`;

                        tblHtml += `<tr>
                                        ${actionCell}
                                        <td>
                                            <input type="hidden" name="page_id" value="${data.assign_data[idx].menu_id}">
                                            <input type="hidden" name="assign_id" value="${data.assign_data[idx].assign_id ?? data.assign_data[idx].id ?? ''}">
                                            ${(data.assign_data[idx].display_name == null) ? "" : data.assign_data[idx].display_name}
                                        </td>
                                        <td>${(data.assign_data[idx].assign_format_no == null) ? "" : data.assign_data[idx].assign_format_no}</td>
                                        <td>${(data.assign_data[idx].assign_effect_date == null) ? "" : data.assign_data[idx].assign_effect_date}</td>
                                    </tr>`;
                    }
                } else {
                    tblHtml += `<tr class="centeralign" id="noItems">
                                    <td colspan="4">No Data Available</td>
                                </tr>`;

                }
                var $table = jQuery('#assignFormateNoDetailTable');
                if (jQuery.fn.DataTable.isDataTable($table)) {
                    $table.DataTable().clear().destroy();
                }
                jQuery('#assignFormateNoDetailTable tbody').empty().append(tblHtml);

                var $new = $table.DataTable({
                    paging: true,
                    // searching: true,
                    // "oLanguage": {
                    //     "sSearch": "Search :"
                    // },
                    dom: 'lrtip',
                    "sScrollX": true,
                    "sScrollX": "100%",
                    "sScrollXInner": "100%",
                    "bScrollCollapse": true,

                });
                fixDataTableColumnsUntilAdjusted($new);

            } else {
                //toastr.error(data.response_message);
            }
        },

        error: function (jqXHR, textStatus, errorThrown) {
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


function revisionAssignDetails(th) {
    let page_id = jQuery(th).closest("tr").find('input[name="page_id"]').val();
    // let assign_id = jQuery(th).closest("tr").find('input[name="assign_id"]').val();
    let assign_id = '';
    let location_id = jQuery('#assign_location_id').val();

    fillAssignFormateDetailsForm(page_id, assign_id, location_id);
}
function fillAssignFormateDetailsForm(page_id, assign_id, location_id) {
    let thisForm = jQuery('#assignFormateNoDetailsModal');
    thisForm.find("#page_id").val(page_id);
    thisForm.find("#id").val(assign_id);
    thisForm.find("#location_id").val(location_id);
    getAssignDataBasedOnPageId(page_id, location_id);

    // var frmData = rt_camera_details_data[formIndx];

    thisForm.modal('show');
}
function getAssignDataBasedOnPageId(page_id, location_id, assign_id = '') {
    jQuery.ajax({
        url: 'get-assign-data?page_id=' + page_id + '&location_id=' + location_id + '&assign_id=' + assign_id,
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            var tblHtml = ``;
            let displayName = data.display_name?.display_name ?? '';
            jQuery('#AssignFormatNoDetailsForm').find('#display_name').text(displayName);
            if (data.response_code == 1 && data.assign_data.length > 0) {
                if (data.assign_data.length > 0 && !jQuery.isEmptyObject(data.assign_data)) {
                    found = 1;

                    for (let idx in data.assign_data) {
                        tblHtml += `
                                    <tr>
                                        <
                                        <td>${(data.assign_data[idx].assign_format_no == null) ? "" : data.assign_data[idx].assign_format_no}</td>
                                        <td>${(data.assign_data[idx].assign_effect_date == null) ? "" : data.assign_data[idx].assign_effect_date}</td>
                                    </tr>`;
                    }
                } else {
                    tblHtml += `<tr class="centeralign" id="noItems">
                                    <td colspan="2">No data Available</td>
                                </tr>`;

                }
                var $table = jQuery('#AssignDataTable');
                if (jQuery.fn.DataTable.isDataTable($table)) {
                    $table.DataTable().clear().destroy();
                }
                jQuery('#AssignDataTable tbody').empty().append(tblHtml);

            } else {
                //toastr.error(data.response_message);
            }
            if (data.last_effective_data && data.last_effective_data.assign_format_no) {
                jQuery('#AssignFormatNoDetailsForm').find('#assign_format_no').val(data.last_effective_data.assign_format_no);
            }
        }
    });
}

function editAssignDetails(th) {
    let page_id = jQuery(th).closest("tr").find('input[name="page_id"]').val();
    let assignId = jQuery(th).closest("tr").find('input[name="assign_id"]').val();
    let location_id = jQuery('#assign_location_id').val();

    if (!assignId) return;
    jQuery('#assignFormateNoDetailsModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-assign_format_no",
        type: 'GET',
        data: { id: assignId },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.assign_data != null) {
                jQuery('#assignFormateNoDetailsModal').find('#id').val(data.assign_data.assign_id);
                jQuery('#assignFormateNoDetailsModal').find('#page_id').val(data.assign_data.page_id);
                jQuery('#assignFormateNoDetailsModal').find('#location_id').val(data.assign_data.assign_location_id);
                jQuery('#assignFormateNoDetailsModal').find('#assign_format_no').val(data.assign_data.assign_format_no);
                jQuery('#assignFormateNoDetailsModal').find('#assign_effect_date').val(data.assign_data.assign_effect_date).trigger("change");
                const form = document.getElementById("commonNABLConfigurationForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#assignFormateNoDetailsModal').find('#assign_format_no').focus();
                getAssignDataBasedOnPageId(page_id, location_id, assignId);
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
$('#AssignFormatNoDetailsForm').on('submit', function (e) {

    e.preventDefault();
    let form = this;

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#assignFormateNoDetailsModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#assignFormateNoDetailsModal').find('#AssignFormatNoDetailsForm').find('#id').val();
    var assign_effect_date = jQuery('#assign_effect_date').val();
    var page_id = jQuery('#page_id').val();
    var location_id = jQuery('#location_id').val();

    var AssignUrl = formId != undefined && formId != "" ? "verify-assign_format_no?assign_effect_date=" + encodeURIComponent(assign_effect_date) + "&page_id=" + encodeURIComponent(page_id) + "&location_id=" + encodeURIComponent(location_id) + "&id=" + formId : "verify-assign_format_no?assign_effect_date=" + encodeURIComponent(assign_effect_date) + "&page_id=" + encodeURIComponent(page_id) + "&location_id=" + encodeURIComponent(location_id);

    var formUrl = formId != undefined && formId != "" ? "update-assign_format_no" : "store-assign_format_no";
    var data = new FormData(form);

    data.append('_token', $('meta[name="csrf-token"]').attr('content'));
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#assignFormateNoDetailsModal').find('#submitbtn').prop('disabled', true);

    if ((assign_effect_date != '' && assign_effect_date != undefined) && (page_id != "" && page_id != undefined)) {
        $.ajax({
            url: AssignUrl,
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.response_code == 1) {
                    toastr.error(res.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#assignFormateNoDetailsModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#assignFormateNoDetailsModal').find('#submitbtn').prop('disabled', false);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("AssignFormatNoDetailsForm").reset();
                                        const form = document.getElementById("AssignFormatNoDetailsForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }
                                        jQuery('#AssignFormatNoDetailsForm').find('.toggleModalBtn').prop('disabled', true);
                                        jQuery('#AssignFormatNoDetailsForm').find('#id').val('');
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    setTimeout(function () {
                                        getAssignDataBasedOnPageId(page_id, location_id);
                                    }, 500);

                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#assignFormateNoDetailsModal').find('#submitbtn').prop('disabled', false);
                                }
                                else {
                                    toastr.error(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#assignFormateNoDetailsModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#assignFormateNoDetailsModal').find('#submitbtn').prop('disabled', false);
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
                                jQuery('#assignFormateNoDetailsModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#assignFormateNoDetailsModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

$('#assignFormateNoDetailsModal').on('show.bs.modal', function () {
    var formIdblank = jQuery('#AssignFormatNoDetailsForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#AssignDataTable tbody').empty();
        jQuery('#assignFormateNoDetailTable tbody').empty();
    } else {
    }
});
jQuery('#assignFormateNoDetailsModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#assignFormateNoDetailsModal');
    thisForm.find('#id').val('');
    document.getElementById("AssignFormatNoDetailsForm").reset();
    jQuery('#AssignDataTable tbody').empty();
    let location_id = $('#assign_location_id').val();
    if (location_id) {
        getAssignDataBasedOnLocation(location_id);
    }
});
