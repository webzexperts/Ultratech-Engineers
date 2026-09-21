// Edit probe ut row click
jQuery('#dyntable tbody').on('click', '.edit-probe_ut', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["pu_id"]) {
        fetchAndFillProbeUT(data["pu_id"]);
    }
});

// Function to fetch and fill probe ut data
function fetchAndFillProbeUT(id) {
    if (!id) return;
    jQuery('#ProbeUtModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-probe_ut",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.probe_ut != null) {
                jQuery('#ProbeUtModal').find('#id').val(data.probe_ut.pu_id != "" ? data.probe_ut.pu_id : "");
                jQuery('#ProbeUtModal').find('#table_pk_id').val(data.probe_ut.table_pk_id != "" ? data.probe_ut.table_pk_id : "");
                jQuery('#ProbeUtModal').find('#item_id').val(data.probe_ut.item_id != "" ? data.probe_ut.item_id : "");
                jQuery('#ProbeUtModal').find('#table_unique_id').val(data.probe_ut.table_unique_id != "" ? data.probe_ut.table_unique_id : "");

                jQuery('#ProbeUtModal').find('#pu_probe').val(data.probe_ut.pu_probe != "" ? data.probe_ut.pu_probe : "");
                jQuery('#ProbeUtModal').find('#pu_serial_number').val(data.probe_ut.pu_serial_number != "" ? data.probe_ut.pu_serial_number : "");
                jQuery('#ProbeUtModal').find('#pu_size_of_probe').val(data.probe_ut.pu_size_of_probe != "" ? data.probe_ut.pu_size_of_probe : "");
                jQuery('#ProbeUtModal').find('#pu_ref_angle').val(data.probe_ut.pu_ref_angle != "" ? data.probe_ut.pu_ref_angle : "");
                jQuery('#ProbeUtModal').find('#pu_frequency').val(data.probe_ut.pu_frequency != "" ? data.probe_ut.pu_frequency : "");
                jQuery('#ProbeUtModal').find('#pu_status').val(data.probe_ut.pu_status || '').trigger('change.select2');

                jQuery('#ProbeUtModal').find('#grn_number').val(data.probe_ut.grn_number != "" ? data.probe_ut.grn_number : "");
                jQuery('#ProbeUtModal').find('#grn_date').val(data.probe_ut.grn_date != "" ? data.probe_ut.grn_date : "");
                jQuery('#ProbeUtModal').find('#grn_challan_date').val(data.probe_ut.grn_challan_date != "" ? data.probe_ut.grn_challan_date : "");
                jQuery('#ProbeUtModal').find('#grn_challan_number').val(data.probe_ut.grn_challan_number != "" ? data.probe_ut.grn_challan_number : "");
                jQuery('#ProbeUtModal').find('#grn_supplier_name').val(data.probe_ut.supplier_name != "" ? data.probe_ut.supplier_name : "");
                jQuery('#ProbeUtModal').find('#item_group').val(data.probe_ut.item_group != "" ? data.probe_ut.item_group : "");
                jQuery('#ProbeUtModal').find('#main_group').val(data.probe_ut.main_group != "" ? data.probe_ut.main_group : "");

                jQuery('#ProbeUtModal').find('#pending_btn').prop('disabled', true);
                if (Number(data.probe_ut.current_location_id) == Number(data.probe_ut.login_location)) {
                    jQuery('#ProbeUtModal').find('#submitbtn').prop('disabled', false);
                } else {
                    jQuery('#ProbeUtModal').find('#submitbtn').prop('disabled', true);
                }
                jQuery('#ProbeUtModal').find('#add_new').show();

                const form = document.getElementById("commonProbeUtForm");
                if (form) form.classList.remove('was-validated');
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

// Reset button click for probe ut modal
jQuery('#resetbtn').on('click', function () {
    var formId = jQuery('#ProbeUtModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonProbeUtForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        jQuery('#commonProbeUtForm').find('.toggleModalBtn').prop('disabled', false);
        jQuery('#commonProbeUtForm #table_pk_id').val('');
        jQuery('#commonProbeUtForm #table_unique_id').val('');
        jQuery('#commonProbeUtForm #item_id').val('');
        jQuery('#commonProbeUtForm').find('#pu_status').val('Active').trigger('change.select2');

        getPendingProbeUt();
        focusPendingButton('#ProbeUtModal');
    } else {
        fetchAndFillProbeUT(formId);
    }
});

// Verification check for duplicate Probe
jQuery('#pu_probe, #pu_serial_number').on('change', function () {
    CheckProbeUT();
});

function CheckProbeUT() {
    var pu_probe = jQuery('#pu_probe').val();
    var pu_serial_number = jQuery('#pu_serial_number').val();
    var formId = jQuery('#ProbeUtModal').find('#commonProbeUtForm').find('#id').val();
    var formUrl = formId != undefined && formId != "" ? "verify-probe_ut?pu_probe=" + encodeURIComponent(pu_probe) + "&pu_serial_number=" + encodeURIComponent(pu_serial_number) + "&pu_id=" + formId : "verify-probe_ut?pu_probe=" + encodeURIComponent(pu_probe) + "&pu_serial_number=" + encodeURIComponent(pu_serial_number);
    if (pu_probe != '' && pu_probe != "") {
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
}

// Main form submit
$('#commonProbeUtForm').on('submit', function (e) {
    e.preventDefault();
    let form = this;

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ProbeUtModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var is_pk = jQuery('#ProbeUtModal').find('#table_pk_id').val();
    if (is_pk == "" || is_pk == undefined) {
        toastr.error('Select At Least One Pending Record.');
        return;
    }

    var pu_probe = jQuery('#pu_probe').val();
    var pu_serial_number = jQuery('#pu_serial_number').val();
    var formId = jQuery('#ProbeUtModal').find('#commonProbeUtForm').find('#id').val();
    var verifyUrl = formId != undefined && formId != "" ? "verify-probe_ut?pu_probe=" + encodeURIComponent(pu_probe) + "&pu_serial_number=" + encodeURIComponent(pu_serial_number) + "&pu_id=" + formId : "verify-probe_ut?pu_probe=" + encodeURIComponent(pu_probe) + "&pu_serial_number=" + encodeURIComponent(pu_serial_number);
    var formUrl = formId != undefined && formId != "" ? "update-probe_ut" : "store-probe_ut";
    var data = new FormData(form);

    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#ProbeUtModal').find('#submitbtn').prop('disabled', true);

    if (pu_probe != '' && pu_serial_number != "") {
        $.ajax({
            url: verifyUrl,
            type: 'GET',
            dataType: 'json',
            headers: headerOpt,
            success: function (res) {
                if (res.response_code == 1) {
                    toastr.error(res.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#ProbeUtModal').find('#submitbtn').prop('disabled', false);
                } else {
                    jQuery.ajax({
                        type: 'POST',
                        url: formUrl,
                        data: data,
                        contentType: false,
                        processData: false,
                        headers: headerOpt,
                        success: function (data) {
                            if (data.response_code == 1) {
                                if (formId != undefined && formId != "") {
                                    function redirectFn() {
                                        window.location.reload();
                                    }
                                    toastSuccess(data.response_message, redirectFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#ProbeUtModal').find('#submitbtn').prop('disabled', false);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonProbeUtForm").reset();
                                        const form = document.getElementById("commonProbeUtForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }
                                        jQuery('#commonProbeUtForm').find('.toggleModalBtn').prop('disabled', false);
                                        jQuery('#commonProbeUtForm #table_pk_id').val('');
                                        jQuery('#commonProbeUtForm #table_unique_id').val('');
                                        jQuery('#commonProbeUtForm #item_id').val('');
                                        jQuery('#commonProbeUtForm').find('#pu_status').val('Active').trigger('change.select2');

                                        getPendingProbeUt();
                                        focusPendingButton('#ProbeUtModal');
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#ProbeUtModal').find('#submitbtn').prop('disabled', false);
                                }
                                else {
                                    toastr.error(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#ProbeUtModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#ProbeUtModal').find('#submitbtn').prop('disabled', false);
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
                                jQuery('#ProbeUtModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#ProbeUtModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            },
            error: function (jqXHR) {
                toastr.error('Verification failed. Please try again.');
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                jQuery('#ProbeUtModal').find('#submitbtn').prop('disabled', false);
            }
        });
    } else {
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ProbeUtModal').find('#submitbtn').prop('disabled', false);
    }
});

function suggestSizeOfProbe(e, $this) {
    var keyevent = e;
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "pu_size_of_probe_list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            headers: headerOpt,
            success: function (data) {
                jQuery("#pu_size_of_probe").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#pu_size_of_probe_list').html(data.sipList);
                } else {
                    toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#pu_size_of_probe").removeClass('file-loader');
                if (jqXHR.status == 401) {
                    toastr.error(jqXHR.statusText);
                } else {
                    toastr.error('Something went wrong!');
                }
            }
        });
    }
}

jQuery(document).on('click', '#pu_size_of_probe_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#pu_size_of_probe_suggesion').val(suggest);
    var hidden = jQuery('#pu_size_of_probe_suggesion').val();
    jQuery('#ProbeUtModal').find('#pu_size_of_probe').val(hidden);
    jQuery('#pu_size_of_probe_list').html('');
});

$('#ProbeUtModal').on('show.bs.modal', function () {
    var formIdblank = jQuery('#commonProbeUtForm').find('input[name="id"]').val();
    if (formIdblank == "" || formIdblank == undefined) {
        jQuery('#commonProbeUtForm').find('#pu_status').val('Active').trigger('change.select2');
        jQuery('#commonProbeUtForm').find('#pending_btn').prop('disabled', false);
        jQuery('#ProbeUtModal').find('#add_new').hide();
        getPendingProbeUt();
        focusPendingButton('#ProbeUtModal');
    }

    if (formIdblank != "" && formIdblank != undefined) {
        setTimeout(() => {
            setSelect2Readonly('#commonProbeUtForm #pu_status', true);
        }, 500);
        jQuery('#commonProbeUtForm').find('#pending_btn').prop('disabled', true);
        jQuery('#ProbeUtModal').find('#add_new').show();
    }
});

jQuery('#ProbeUtModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#ProbeUtModal');
    thisForm.find('#id').val('');
    document.getElementById("commonProbeUtForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).prop('checked', false);
    });
    thisForm.find('#pu_status').val('Active').trigger('change');
    thisForm.find('#add_new').hide();
});

jQuery('#GrnPendingForProbeModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = jQuery('#table_pk_id').val() != '' ? document.getElementById('pu_probe') : document.getElementById('pending_btn');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }
});

// get pending probe ut from view
function getPendingProbeUt() {
    var thisForm = jQuery('#addPendigGrnForm');

    return jQuery.ajax({
        url: "get-pending_grn_list_for_probe_ut",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                var usedParts = [];
                var totalDisb = 0;

                thisForm.find('#pendingGrnDataTable tbody input[name="form_indx"]').each(function (indx) {
                    let frmIndx = jQuery(this).val();
                    let jbEorkOrderId = grn_data[frmIndx].table_pk_id;
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

                var $table = jQuery("#GrnPendingForProbeModal").find('#pendingGrnDataTable');
                if (jQuery.fn.DataTable.isDataTable($table)) {
                    $table.DataTable().clear().destroy();
                }
                jQuery('#pendingGrnDataTable tbody').empty();

                if (data.grn_data.length > 0 && !jQuery.isEmptyObject(data.grn_data)) {
                    var formIdblank = jQuery('#commonProbeUtForm').find('#id').val();
                    if (formIdblank != "" && formIdblank != undefined) {
                        jQuery('#pending_btn').prop('disabled', true);
                    } else {
                        jQuery('#pending_btn').prop('disabled', false);
                    }

                    for (let idx in data.grn_data) {
                        var inUse = isUsed(data.grn_data[idx].table_pk_id);
                        var in_use = data.grn_data[idx].in_use == true ? 'readonly' : '';
                        totalEntry++;
                        tblHtml += `
                                    <tr>
                                        <td>
                                            <input
                                                type="radio"
                                                name="grnd_id[]"
                                                class="simple-check radio-filter-remove ${inUse ? 'in-use' : ''}"
                                                id="grnd_ids_${data.grn_data[idx].table_pk_id}"
                                                value="${data.grn_data[idx].table_pk_id}"
                                                data-table_unique_id="${data.grn_data[idx].table_unique_id}"
                                                ${inUse ? 'checked' : ''}
                                                ${in_use}
                                            />
                                        </td>
                                        <td>${data.grn_data[idx].table_unique_id}</td>
                                        <td>${data.grn_data[idx].grn_number != null ? data.grn_data[idx].grn_number : ''}</td>
                                        <td>${data.grn_data[idx].grn_date != null ? data.grn_data[idx].grn_date : ''}</td>
                                        <td>${data.grn_data[idx].supplier_name != null ? data.grn_data[idx].supplier_name : ''}</td>
                                        <td>${data.grn_data[idx].grn_challan_number != null ? data.grn_data[idx].grn_challan_number : ''}</td>
                                        <td>${data.grn_data[idx].grn_challan_date != null ? data.grn_data[idx].grn_challan_date : ''}</td>
                                        <td>${data.grn_data[idx].item_name != null ? data.grn_data[idx].item_name : ''}</td>
                                        <td>${data.grn_data[idx].item_group != null ? data.grn_data[idx].item_group : ''}</td>
                                        <td>${data.grn_data[idx].item_type != null ? data.grn_data[idx].item_type : ''}</td>
                                        <td>${data.grn_data[idx].qty != null ? parseFloat(data.grn_data[idx].qty).toFixed(3) : ''}</td>
                                        <td>${data.grn_data[idx].pending_qty != null ? parseFloat(data.grn_data[idx].pending_qty).toFixed(3) : ''}</td>
                                        <td>${data.grn_data[idx].unit}</td>
                                    </tr>`;
                    }
                } else {
                    tblHtml += `<tr class="centeralign" id="noPendingPo">
                                    <td colspan="13">No Pending Records Available</td>
                                </tr>`;
                    jQuery('#pending_btn').prop('disabled', true);
                    jQuery('#pendingGrnDataTable tbody').append(tblHtml);
                    return;
                }

                jQuery('#pendingGrnDataTable tbody').append(tblHtml);

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
                toastr.error(data.response_message);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            jQuery('.toggleModalBtn').prop('disabled', true);
            if (jqXHR.status == 401) {
                toastr.error(jqXHR.statusText);
            } else {
                toastr.error('Something went wrong!');
            }
        }
    });
}

// get selected pending GRN / Opening stock
$('#addPendigGrnForm').on('submit', function (e) {
    e.preventDefault();

    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#GrnPendingForProbeModal').find('#submitbtn').prop('disabled', true);

    let chkArr = [];
    let uniqueArr = [];

    jQuery("#addPendigGrnForm").find("[id^='grnd_ids_']:checked")
        .each(function () {
            chkArr.push(jQuery(this).val());
            uniqueArr.push(jQuery(this).data('table_unique_id'));
        });

    if (chkArr.length === 0) {
        toastr.error('Select At Least One Pending.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#GrnPendingForProbeModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    jQuery.ajax({
        url: 'get-pending_grn_for_probe_ut',
        type: 'GET',
        data: {
            grnd_ids: chkArr.join(','),
            table_unique_ids: uniqueArr.join(',')
        },
        dataType: 'json',
        headers: headerOpt,
        success: function (data) {
            if (data.response_code == 1) {
                if (data.grn_data) {
                    jQuery('#ProbeUtModal').find('#table_pk_id').val(data.grn_data.table_pk_id);
                    jQuery('#ProbeUtModal').find('#item_id').val(data.grn_data.item_id);
                    jQuery('#ProbeUtModal').find('#table_unique_id').val(data.grn_data.table_unique_id);
                    jQuery('#ProbeUtModal').find('#item_group').val(data.grn_data.item_group);
                    jQuery('#ProbeUtModal').find('#main_group').val(data.grn_data.item_type);

                    jQuery('#ProbeUtModal').find('#grn_number').val(data.grn_data.grn_number != null ? data.grn_data.grn_number : '');
                    jQuery('#ProbeUtModal').find('#grn_date').val(data.grn_data.grn_date != null ? data.grn_data.grn_date : '');
                    jQuery('#ProbeUtModal').find('#grn_challan_number').val(data.grn_data.grn_challan_number != null ? data.grn_data.grn_challan_number : '');
                    jQuery('#ProbeUtModal').find('#grn_challan_date').val(data.grn_data.grn_challan_date != null ? data.grn_data.grn_challan_date : '');
                    jQuery('#ProbeUtModal').find('#grn_supplier_name').val(data.grn_data.grn_supplier_name != null ? data.grn_data.grn_supplier_name : '');

                    jQuery('.toggleModalBtn').prop('disabled', true);
                } else {
                    jQuery('#ProbeUtModal').find('#table_pk_id').val('');
                    jQuery('#ProbeUtModal').find('#item_id').val('');
                    jQuery('#ProbeUtModal').find('#table_unique_id').val('');
                    jQuery('#ProbeUtModal').find('#item_group').val('');
                    jQuery('#ProbeUtModal').find('#main_group').val('');

                    jQuery('#ProbeUtModal').find('#grn_number').val('');
                    jQuery('#ProbeUtModal').find('#grn_date').val('');
                    jQuery('#ProbeUtModal').find('#grn_challan_number').val('');
                    jQuery('#ProbeUtModal').find('#grn_challan_date').val('');
                    jQuery('#ProbeUtModal').find('#grn_supplier_name').val('');

                    jQuery('.toggleModalBtn').prop('disabled', false);
                }

                jQuery("#GrnPendingForProbeModal").modal('hide');
            } else {
                toastr.error(data.response_message);
            }

            jQuery('#GrnPendingForProbeModal').find('#submitbtn').prop('disabled', false);
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        },
        error: function (jqXHR) {
            if (jqXHR.status == 401) {
                toastr.error(jqXHR.statusText);
            } else {
                toastr.error('Something went wrong!');
            }

            jQuery('#GrnPendingForProbeModal').find('#submitbtn').prop('disabled', false);
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        }
    });
});

jQuery('#GrnPendingForProbeModal').on('show.bs.modal', function (e) {
    var dt = jQuery('#pendingGrnDataTable').DataTable();
    fixDataTableColumnsUntilAdjusted(dt);

    var usedParts = [];
    var table_pk_id = jQuery('#ProbeUtModal').find('#table_pk_id').val();
    if (table_pk_id != "" && table_pk_id != null) {
        usedParts.push(Number(table_pk_id));
    }

    function isUsed(pjId) {
        if (usedParts.includes(Number(pjId))) {
            return true;
        }
        return false;
    }

    jQuery('#pendingGrnDataTable tbody tr').each(function (indx) {
        var checkField = jQuery(this).find('input[name="grnd_id[]"]');
        var partId = jQuery(checkField).val();
        var inUse = isUsed(partId);

        if (inUse) {
            jQuery(checkField).prop('checked', true);
        } else {
            jQuery(checkField).prop('checked', false);
        }
    });
});

jQuery('#ProbeUtModal').on('click', '#add_new', function () {
    jQuery('#ProbeUtModal').find('#id').val('');
    document.getElementById("commonProbeUtForm").reset();
    const form = document.getElementById("commonProbeUtForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#commonProbeUtForm').find('.toggleModalBtn').prop('disabled', false);
    jQuery('#commonProbeUtForm #table_pk_id').val('');
    jQuery('#commonProbeUtForm #table_unique_id').val('');
    jQuery('#commonProbeUtForm #item_id').val('');
    jQuery('#commonProbeUtForm').find('#pu_status').val('Active').trigger('change.select2');

    getPendingProbeUt();
    focusPendingButton('#ProbeUtModal');
    jQuery('#ProbeUtModal').find('#submitbtn').prop('disabled', false);
    jQuery('#ProbeUtModal').find('#add_new').hide();
});