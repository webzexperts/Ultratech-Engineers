rt_camera_details_data = [];

// Edit camera rt row click
jQuery('#dyntable tbody').on('click', '.edit_rt_camera', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    jQuery('#RTCameraModal').find('#id').val(data["rt_camera_id"]);
    if (data && data["rt_camera_id"]) {
        fetchAndFillCameraRT(data["rt_camera_id"]);
    }
});

// // Function to fetch and fill camera rt data
function fetchAndFillCameraRT(id) {
    if (!id) return;
    jQuery('#RTCameraModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-rt_camera",
        type: 'GET',
        data: { id: id },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.rt_camera_data != null) {
                jQuery('#RTCameraModal').find('#table_unique_id').val(data.rt_camera_data.table_unique_id);
                jQuery('#RTCameraModal').find('#table_pk_id').val(data.rt_camera_data.table_pk_id);
                jQuery('#RTCameraModal').find('#item_id').val(data.rt_camera_data.item_id);
                jQuery('#RTCameraModal').find('#rt_status').val(data.rt_camera_data.rt_status).trigger('change.select2');
                jQuery('#RTCameraModal').find("#grn_no").val(data.rt_camera_data.grn_number != null ? data.rt_camera_data.grn_number : '');
                jQuery('#RTCameraModal').find("#grn_date").val(data.rt_camera_data.grn_date != null ? data.rt_camera_data.grn_date : '');
                jQuery('#RTCameraModal').find("#supplier").val(data.rt_camera_data.supplier_name);
                jQuery('#RTCameraModal').find("#challan_no").val(data.rt_camera_data.grn_challan_number != null ? data.rt_camera_data.grn_challan_number : '');
                jQuery('#RTCameraModal').find("#challan_date").val(data.rt_camera_data.grn_challan_date != null ? data.rt_camera_data.grn_challan_date : '');

                jQuery('#RTCameraModal').find('#item_group').val(data.rt_camera_data.item_group);
                jQuery('#RTCameraModal').find('#main_group').val(data.rt_camera_data.main_group);
                jQuery('#RTCameraModal').find('#rt_camera_name').val(data.rt_camera_data.rt_camera_name != "" ? data.rt_camera_data.rt_camera_name : "");
                jQuery('#RTCameraModal').find('#rt_serial_no').val(data.rt_camera_data.rt_serial_no != "" ? data.rt_camera_data.rt_serial_no : "");
                jQuery('#RTCameraModal').find('#rt_isotope').val(data.rt_camera_data.rt_isotope).trigger('change');
                jQuery('#RTCameraModal').find('#rt_x_ray').val(data.rt_camera_data.rt_x_ray != "" ? data.rt_camera_data.rt_x_ray : "");
                jQuery('#RTCameraModal').find('#rt_focal_spot').val(data.rt_camera_data.rt_focal_spot != "" ? data.rt_camera_data.rt_focal_spot : "");
                let isLocked = (data.rt_camera_data.is_locked === true || data.rt_camera_data.is_locked == 1);
                if (isLocked) {
                    jQuery('#RTCameraModal').find('#rt_aerb_no').prop('readonly', true);
                    jQuery('#RTCameraModal').find('#application_no').prop('readonly', true);
                    jQuery('#RTCameraModal').find('#validity').prop('readonly', true).datepicker('disable');
                    jQuery('#RTCameraModal').find('#movement_approval').prop('disabled', true);
                } else {
                    jQuery('#RTCameraModal').find('#rt_aerb_no').prop('readonly', false);
                    jQuery('#RTCameraModal').find('#application_no').prop('readonly', false);
                    jQuery('#RTCameraModal').find('#validity').prop('readonly', false).datepicker('enable');
                    jQuery('#RTCameraModal').find('#movement_approval').prop('disabled', false);
                }

                jQuery('#RTCameraModal').find('#rt_aerb_no').val(data.rt_camera_data.rt_aerb_no != "" ? data.rt_camera_data.rt_aerb_no : "");
                jQuery('#RTCameraModal').find('#application_no').val(data.rt_camera_data.application_no);
                jQuery('#RTCameraModal').find('#validity').val(data.rt_camera_data.validity);
                jQuery('#RTCameraModal').find('#rt_document_ref_no').val(data.rt_camera_data.rt_document_ref_no != "" ? data.rt_camera_data.rt_document_ref_no : "");
                jQuery('#RTCameraModal').find('#rt_validity_date').val(data.rt_camera_data.rt_validity_date != "" ? data.rt_camera_data.rt_validity_date : "");
                jQuery('#RTCameraModal').find('#pending_btn').prop('disabled', true);
                jQuery('#RTCameraModal').find('#id').val(data.rt_camera_data.rt_camera_id);
                if (Number(data.rt_camera_data.current_location_id) == Number(data.rt_camera_data.login_location)) {
                    jQuery("#submitbtn").prop('disabled', false);

                } else {
                    jQuery("#submitbtn").prop('disabled', true);

                }

                if (data.rt_camera_data.movement_approval != "" && data.rt_camera_data.movement_approval != undefined && data.rt_camera_data.movement_approval != null) {
                    let fullPath = data.rt_camera_data.movement_approval;
                    let fileName = fullPath.split('/').pop();
                    jQuery('#RTCameraModal').find('#movement_approval_doc').val(fullPath);
                    jQuery('#RTCameraModal').find('#movement_approval_prev').attr('href', uploadURL + fullPath).removeClass('hide');
                    if (isLocked) {
                        jQuery('#RTCameraModal').find('#movement_approval_remove').addClass('hide').removeClass('i-block');
                    } else {
                        jQuery('#RTCameraModal').find('#movement_approval_remove').addClass('i-block').removeClass('hide');
                    }

                    let fileInput = jQuery('#RTCameraModal').find('#movement_approval');
                    let newFile = new DataTransfer();
                    newFile.items.add(new File([""], fileName));
                    fileInput[0].files = newFile.files;
                } else {
                    clearMovement();
                }

                if (data.rt_camera_details_data.length > 0 && !jQuery.isEmptyObject(data.rt_camera_details_data)) {
                    for (let ind in data.rt_camera_details_data) {
                        rt_camera_details_data.push(data.rt_camera_details_data[ind]);
                    }
                    fillRTCameraDetailsTable();
                }
                jQuery('#RTCameraModal').find('#add_new').show();
                jQuery('#xrayValidationMessage').hide();
                jQuery('#focalspotValidationMessage').hide();
                $('#rt_x_ray').removeClass('is-invalid').addClass('is-valid');
                $('#rt_focal_spot').removeClass('is-invalid').addClass('is-valid');

                const form = document.getElementById("commonRTCameraForm");
                if (form) form.classList.remove('was-validated');
                const input = document.getElementById('rt_camera_name');
                input?.focus();
                // jQuery('#RTCameraModal').find('#rt_camera_name').focus();
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

// Reset button click for camera rt modal
jQuery('#resetbtn').on('click', function () {
    setSelect2Readonly("#rt_status", true);
    rt_camera_details_data = [];
    jQuery('#RTCameraDetailTable tbody').empty();

    var formId = jQuery('#RTCameraModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonRTCameraForm");
        if (form) {
            form.reset();
            clearDecayChart();
            clearMovement();
            form.classList.remove('was-validated');
        }

        jQuery('#commonRTCameraForm').find('.toggleModalBtn').prop('disabled', true);
        jQuery('#commonRTCameraForm').find("#rt_isotope").val('').trigger('change.select2');
        jQuery('#rt_status').val('Active').trigger('change.select2');
        jQuery('#RTCameraModal').find('#rt_aerb_no').prop('readonly', false);
        jQuery('#RTCameraModal').find('#application_no').prop('readonly', false);
        jQuery('#RTCameraModal').find('#validity').prop('readonly', false).datepicker('enable');
        jQuery('#RTCameraModal').find('#movement_approval').prop('disabled', false);
        IsotopeChange();
        GetPendingGRNforRTCamera();
        focusPendingButton('#RTCameraModal');
        jQuery('#commonRTCameraForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
        jQuery('#commonRTCameraForm').find('#grn_no').prop({ tabindex: -1, readonly: true });
        jQuery('#commonRTCameraForm').find('#grn_date').prop({ tabindex: -1, readonly: true });
        jQuery('#commonRTCameraForm').find('#supplier').prop({ tabindex: -1, readonly: true });
        jQuery('#commonRTCameraForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
        jQuery('#commonRTCameraForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
        jQuery('#commonRTCameraForm').find('#item_group').prop({ tabindex: -1, readonly: true });
        jQuery('#commonRTCameraForm').find('#main_group').prop({ tabindex: -1, readonly: true });

        $('#rt_x_ray').removeClass('is-invalid').addClass('is-valid');
        $('#rt_focal_spot').removeClass('is-invalid').addClass('is-valid');

        jQuery('#commonRTCameraForm').find('#id').val('');
        jQuery('#commonRTCameraForm').find('#table_pk_id').val('');
        jQuery('#commonRTCameraForm').find('#item_id').val('');
        jQuery('#commonRTCameraForm').find('#rtcd_id').val('');
        jQuery('#commonRTCameraForm').find('#old_loading_date').val('');

    } else {
        fetchAndFillCameraRT(formId);
    }
});

// edit time fill table start
function fillRTCameraDetailsTable() {
    let thisModal = jQuery('#RTCameraDetailsModal');
    if (rt_camera_details_data.length > 0) {
        for (let key in rt_camera_details_data) {
            let formIndx = rt_camera_details_data.indexOf(rt_camera_details_data[key]);
            var rtcd_last_of_loading_date = rt_camera_details_data[key].rtcd_last_of_loading_date ? rt_camera_details_data[key].rtcd_last_of_loading_date : "";
            var rtcd_initial_activity_ci = rt_camera_details_data[key].rtcd_initial_activity_ci ? parseFloat(rt_camera_details_data[key].rtcd_initial_activity_ci).toFixed(3) : "";
            var rtcd_source_size = rt_camera_details_data[key].rtcd_source_size ? rt_camera_details_data[key].rtcd_source_size : "";
            var rtcd_pencil_no = rt_camera_details_data[key].rtcd_pencil_no ? rt_camera_details_data[key].rtcd_pencil_no : "";
            // var rtcd_iga_no = rt_camera_details_data[key].rtcd_iga_no ? rt_camera_details_data[key].rtcd_iga_no : "";
            var rtcd_decay_chart_doc = rt_camera_details_data[key].rtcd_decay_chart_doc ? rt_camera_details_data[key].rtcd_decay_chart_doc : "";

            if (jQuery('#RTCameraDetailTable tbody').find('#noDetails').length > 0) {
                jQuery('#RTCameraDetailTable tbody').empty();
            }
            let tblHtml = `<tr>`;
            tblHtml += `<td>`;
            tblHtml += DetailsActionDropdown('editRTCameraDetails', 'removeRTCameraDetails');
            tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;

            tblHtml += `<td>${rtcd_last_of_loading_date}</td>`;
            tblHtml += `<td>${rtcd_initial_activity_ci}</td>`;
            tblHtml += `<td>${rtcd_source_size}</td>`;
            tblHtml += `<td>${rtcd_pencil_no}</td>`;
            // tblHtml += `<td>${rtcd_iga_no}</td>`;
            if (rtcd_decay_chart_doc != "") {
                let fullImagePath = uploadURL + rtcd_decay_chart_doc;
                tblHtml += `<td style="text-align:center; vertical-align:middle;">
                    <a target="_blank" href="${fullImagePath}" title="View" style="font-size:18px; display:inline-block;">
                        <i class="ri-eye-fill"></i>
                    </a>
                    <input type='hidden' name='rtcd_decay_chart_doc[]' value="${rtcd_decay_chart_doc}"/>
                </td>`;
            } else {
                tblHtml += `<td style="text-align:center; vertical-align:middle;">
                    <input type='hidden' name='rtcd_decay_chart_doc[]' value=""/>
                </td>`;
            }
            tblHtml += `</tr>`;
            jQuery('#RTCameraDetailTable tbody').append(tblHtml);
        }
    }
}
// edit time fill table end

function editRTCameraDetails(th) {
    let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
    let rawIndx = jQuery(th).closest('tr').index();
    // let lastIndex = rt_camera_details_data.length - 1;
    // if (parseInt(formIndx) !== lastIndex) {
    //     toastr.error('Only the latest record can be edited.');
    //     return;
    // }
    fillRTCameraDetailsForm(formIndx, rawIndx);
}

// camera - rt details form edit
function fillRTCameraDetailsForm(formIndx, rawIndx) {
    let thisForm = jQuery('#RTCameraDetailsModal');
    thisForm.find("#form_type").val("edit");
    thisForm.find("#form_index").val(formIndx);
    thisForm.find("#row_index").val(rawIndx);
    var frmData = rt_camera_details_data[formIndx];
    thisForm.find("#rtcd_id").val(frmData.rtcd_id);
    thisForm.find("#old_loading_date").val(frmData.rtcd_last_of_loading_date ?? "");
    thisForm.find("#rtcd_last_of_loading_date").val(frmData.rtcd_last_of_loading_date ?? "");
    thisForm.find("#rtcd_initial_activity_ci").val(frmData.rtcd_initial_activity_ci != "" ? parseFloat(frmData.rtcd_initial_activity_ci).toFixed(3) : "");
    thisForm.find("#rtcd_source_size").val(frmData.rtcd_source_size ?? "");
    thisForm.find("#rtcd_pencil_no").val(frmData.rtcd_pencil_no ?? "");
    // thisForm.find("#rtcd_iga_no").val(frmData.rtcd_iga_no ?? "");

    if (frmData.rtcd_decay_chart_doc != "" && frmData.rtcd_decay_chart_doc != undefined) {
        let fullPath = frmData.rtcd_decay_chart_doc;
        let fileName = fullPath.split('/').pop();
        thisForm.find("#rtcd_decay_chart_doc").val(fullPath);
        thisForm.find('#rtcd_decay_chart_prev').attr('href', uploadURL + fullPath).removeClass('hide');
        thisForm.find('#rtcd_decay_chart_remove').addClass('i-block').removeClass('hide');
        thisForm.find('#rtcd_decay_chart_img-prev-box').removeClass('hide');
        thisForm.find('#rtcd_decay_chart_img-prev').html(fileName);
        let fileInput = thisForm.find('#rtcd_decay_chart');
        let newFile = new DataTransfer();
        newFile.items.add(new File([""], fileName));
        fileInput[0].files = newFile.files;
    } else {
        thisForm.find('#rtcd_decay_chart_doc').val('');
        thisForm.find('#rtcd_decay_chart_prev').attr('href', '#').addClass('hide');
        thisForm.find('#rtcd_decay_chart_remove').removeClass('i-block').addClass('hide');
        thisForm.find('#rtcd_decay_chart_img-prev').html('');
        thisForm.find('#rtcd_decay_chart_img-prev-box').addClass('hide').html('');
        thisForm.find('#rtcd_decay_chart').val('');
    }
    thisForm.modal('show');
}

function removeRTCameraDetails(th) {
    toastDetailDelete("Do you want to delete this record?", () => {
        let formIndx = jQuery(th).closest("tr").find('input[name="form_indx"]').val();
        removeFormObj(formIndx);
        jQuery(th).closest("tr").remove();
    });
}

function removeFormObj(formIndx) {
    delete rt_camera_details_data[formIndx];
    rt_camera_details_data = rt_camera_details_data.filter(element => element != null);
    jQuery('#RTCameraDetailTable tbody').empty();
    fillRTCameraDetailsTable();
}

jQuery(document).ready(function () {
    jQuery('#rt_x_ray, #rt_focal_spot').on('input', function () {
        var $this = jQuery(this);
        var val = $this.val().trim();
        if (val == '') {
            $this.addClass('is-invalid');
            if ($this.attr('id') == 'rt_x_ray') {
                jQuery('#xrayValidationMessage').show();
            } else if ($this.attr('id') === 'rt_focal_spot') {
                jQuery('#focalspotValidationMessage').show();
            }
        } else {
            $this.removeClass('is-invalid');
            if ($this.attr('id') == 'rt_x_ray') {
                jQuery('#xrayValidationMessage').hide();
            } else if ($this.attr('id') === 'rt_focal_spot') {
                jQuery('#focalspotValidationMessage').hide();
            }
        }
    });
});

// Main Camera - RT Form Submit
$('#commonRTCameraForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#RTCameraModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;
    let customError = false;

    $('#cameraValidationMessage, #xrayValidationMessage, #focalspotValidationMessage').hide();
    $('#rt_x_ray, #rt_focal_spot').removeClass('is-invalid');

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        customError = true;
    }


    var rt_isotope = jQuery('#rt_isotope option:selected').val();
    if (rt_isotope == 'X-Ray') {
        if (!jQuery('#rt_x_ray').val()) {
            jQuery('#rt_x_ray').addClass('is-invalid');
            jQuery('#xrayValidationMessage').show();
            customError = true;
        }

        if (!jQuery('#rt_focal_spot').val()) {
            jQuery('#rt_focal_spot').addClass('is-invalid');
            jQuery('#focalspotValidationMessage').show();
            customError = true;
        }

    }

    if (customError) {
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#RTCameraModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var table_pk_id = jQuery('#table_pk_id').val();
    if (table_pk_id == '' || table_pk_id == 0) {
        toastr.error('Select At Least One Pending.');
        // toastr.error('Select Atleast One Pending.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#RTCameraModal').find('#submitbtn').prop('disabled', false);
        return;
    }


    var isotope = jQuery('#rt_isotope option:selected').val();
    var formIdnew = jQuery('#RTCameraModal').find('#commonRTCameraForm').find('#id').val();
    var rt_camera_name = jQuery('#rt_camera_name').val();
    var rt_serial_no = jQuery('#rt_serial_no').val();
    if (rt_camera_name != '' && rt_serial_no != "") {
        RTCameraUrl = formIdnew != undefined && formIdnew != "" ? "verify-rt_camera?rt_camera_name=" + encodeURIComponent(rt_camera_name) + "&rt_serial_no=" + encodeURIComponent(rt_serial_no) + "&camera_id=" + formIdnew : "verify-rt_camera?rt_camera_name=" + encodeURIComponent(rt_camera_name) + "&rt_serial_no=" + encodeURIComponent(rt_serial_no);
    }
    var formUrl = formIdnew != undefined && formIdnew != "" ? "update-rt_camera" : "store-rt_camera";
    var data = new FormData(form);
    data.append('rt_camera_details_data', JSON.stringify(rt_camera_details_data ?? []));
    data.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if ((isotope == 'Ir-192' || isotope == 'Co-60') && (!rt_camera_details_data || rt_camera_details_data.length == 0)) {
        toastr.error('Add At Least One Camera - RT Details.');
        // toastr.error('Please Add At Least One Camera - RT Details.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#RTCameraModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    if ((rt_camera_name != '' && rt_camera_name != undefined) && (rt_serial_no != "" && rt_serial_no != undefined)) {
        $.ajax({
            url: RTCameraUrl,
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.response_code == 1) {
                    toastr.error(res.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#RTCameraModal').find('#submitbtn').prop('disabled', false);
                } else {
                    jQuery.ajax({
                        type: 'POST',
                        url: formUrl,
                        data: data,
                        contentType: false,
                        processData: false,
                        success: function (data) {
                            if (data.response_code == 1) {
                                if (formIdnew != undefined && formIdnew != "") {
                                    function redirectFn() {
                                        window.location.reload();
                                    }
                                    toastSuccess(data.response_message, redirectFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#RTCameraModal').find('#submitbtn').prop('disabled', false);
                                }
                                else if (formIdnew == undefined || formIdnew == "") {
                                    function nextFn() {
                                        document.getElementById("commonRTCameraForm").reset();
                                        const form = document.getElementById("commonRTCameraForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        // jQuery('#commonRTCameraForm').find('.toggleModalBtn').prop('disabled', true);
                                        jQuery('#commonRTCameraForm').find('.toggleModalBtn').prop('disabled', false);
                                        jQuery('#commonRTCameraForm').find("#rt_isotope").val('').trigger('change.select2');
                                        jQuery('#commonRTCameraForm').find("#table_pk_id").val('');
                                        jQuery('#commonRTCameraForm').find('#id').val('');
                                        jQuery('#commonRTCameraForm').find('#item_id').val('');
                                        // $("#rt_isotope").val('').trigger('change.select2');
                                        // $("#rt_grnd_id").val('');
                                        jQuery('#rt_status').val('Active').trigger('change.select2');
                                        rt_camera_details_data = [];
                                        jQuery('#RTCameraDetailTable tbody').empty();
                                        GetPendingGRNforRTCamera();
                                        IsotopeChange();
                                        clearDecayChart();
                                        clearMovement();

                                        jQuery('#commonRTCameraForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonRTCameraForm').find('#grn_no').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonRTCameraForm').find('#grn_date').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonRTCameraForm').find('#supplier').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonRTCameraForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonRTCameraForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonRTCameraForm').find('#item_group').prop({ tabindex: -1, readonly: true });
                                        jQuery('#commonRTCameraForm').find('#main_group').prop({ tabindex: -1, readonly: true });
                                        focusPendingButton('#RTCameraModal');
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#RTCameraModal').find('#submitbtn').prop('disabled', false);
                                }
                                else {
                                    toastr.error(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#RTCameraModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#RTCameraModal').find('#submitbtn').prop('disabled', false);
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
                                jQuery('#RTCameraModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#RTCameraModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});
// Main Form Submit End

// Camera - RT Details Form Submit Start
$('#RTCameraDetailsForm').on('submit', function (e) {
    e.preventDefault();
    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    var data = new FormData(document.getElementById('RTCameraDetailsForm'));
    var formValue = Object.fromEntries(data.entries());
    var thisModal = jQuery('#RTCameraDetailsModal');

    if (formValue.rtcd_last_of_loading_date.trim()) {
        // let allDates = rt_camera_details_data.map(item => item.rtcd_last_of_loading_date).filter(d => d);
        // if (allDates.length > 0) {
        //     let maxDate = allDates.sort().slice(-1)[0];
        //     if (formValue.rtcd_last_of_loading_date < maxDate) {
        //         toastr.error('Date of Loading Must be Greater Then Last Entered Date');
        //         return;
        //     }
        // }

        const inputDate = new Date(formValue.rtcd_last_of_loading_date.split('/').reverse().join('-'));
        if (formValue.form_type == "add") {
            const existingDates = rt_camera_details_data.map(item => item.rtcd_last_of_loading_date).filter(d => d);
            if (existingDates.length > 0) {
                const maxDate = new Date(Math.max(...existingDates.map(d => new Date(d.split('/').reverse().join('-')))));
                if (inputDate <= maxDate) {
                    toastr.error('Date of Loading Must Be Greater Than All Previously Entered Dates.');
                    return;
                }
            }
        }

        var noDuplicate = true;
        if (noDuplicate) {
            var rtcd_last_of_loading_date = formValue.rtcd_last_of_loading_date ? formValue.rtcd_last_of_loading_date : "";
            var rtcd_initial_activity_ci = formValue.rtcd_initial_activity_ci ? parseFloat(formValue.rtcd_initial_activity_ci).toFixed(3) : "";
            var rtcd_source_size = formValue.rtcd_source_size ? formValue.rtcd_source_size : "";
            var rtcd_pencil_no = formValue.rtcd_pencil_no ? formValue.rtcd_pencil_no : "";
            // var rtcd_iga_no = formValue.rtcd_iga_no ? formValue.rtcd_iga_no : "";
            var rtcd_decay_chart_doc = formValue.rtcd_decay_chart_doc ? formValue.rtcd_decay_chart_doc : "";

            if (rtcd_last_of_loading_date != "") {
                if (formValue.form_type == "edit") {

                    const formIndex = parseInt(formValue.form_index);
                    const originalRecord = rt_camera_details_data[formIndex];

                    // Previous record date
                    let prevDate = null;
                    if (formIndex > 0 && rt_camera_details_data[formIndex - 1].rtcd_last_of_loading_date) {
                        prevDate = new Date(rt_camera_details_data[formIndex - 1].rtcd_last_of_loading_date.split('/').reverse().join('-'));
                    }

                    // Next record date
                    let nextDate = null;
                    if (formIndex < rt_camera_details_data.length - 1 && rt_camera_details_data[formIndex + 1].rtcd_last_of_loading_date) {
                        nextDate = new Date(rt_camera_details_data[formIndex + 1].rtcd_last_of_loading_date.split('/').reverse().join('-'));
                    }

                    // Validate previous date
                    if (prevDate && inputDate <= prevDate) {
                        toastr.error('Date of Loading Must Be Greater Than All Previous Dates.');
                        // jQuery('#rtcd_last_of_loading_date').val(originalRecord.rtcd_last_of_loading_date);
                        return;
                    }

                    // Validate next date
                    if (nextDate && inputDate >= nextDate) {
                        toastr.error('Date of Loading Must Be Smaller Than Next Record\'s Date.');
                        // jQuery('#rtcd_last_of_loading_date').val(originalRecord.rtcd_last_of_loading_date);
                        return;
                    }

                    rt_camera_details_data[formValue.form_index] = formValue;
                    let tblHtml = ``;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editRTCameraDetails', 'removeRTCameraDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formValue.form_index}"/>
                    </td>`;

                    tblHtml += `<td>${rtcd_last_of_loading_date}</td>`;
                    tblHtml += `<td>${rtcd_initial_activity_ci}</td>`;
                    tblHtml += `<td>${rtcd_source_size}</td>`;
                    tblHtml += `<td>${rtcd_pencil_no}</td>`;
                    // tblHtml += `<td>${rtcd_iga_no}</td>`;
                    if (rtcd_decay_chart_doc != "") {
                        let fullImagePath = uploadURL + rtcd_decay_chart_doc;
                        tblHtml += `<td style="text-align:center; vertical-align:middle;">
                            <a target="_blank" href="${fullImagePath}" title="View" style="font-size:18px; display:inline-block;">
                                <i class="ri-eye-fill"></i>
                            </a>
                            <input type='hidden' name='rtcd_decay_chart_doc[]' value="${rtcd_decay_chart_doc}"/>
                        </td>`;
                    } else {
                        tblHtml += `<td style="text-align:center; vertical-align:middle;">
                            <input type='hidden' name='rtcd_decay_chart_doc[]' value=""/>
                        </td>`;
                    }
                    tblHtml += `</tr>`;
                    jQuery('#RTCameraDetailTable tbody').find('tr').eq(formValue.row_index).empty().append(tblHtml);
                    toastSuccess("Record Updated.");
                    const input = document.getElementById('rt_document_ref_no');
                    input?.focus();
                } else {
                    rt_camera_details_data.push(formValue)
                    let formIndx = rt_camera_details_data.indexOf(formValue);
                    if (jQuery('#RTCameraDetailTable tbody').find('#noDetails').length > 0) {
                        jQuery('#RTCameraDetailTable tbody').empty();
                    }

                    let tblHtml = `<tr>`;
                    tblHtml += `<td>`;
                    tblHtml += DetailsActionDropdown('editRTCameraDetails', 'removeRTCameraDetails');
                    tblHtml += `<input type="hidden" name="form_indx" value="${formIndx}"/>
                    </td>`;

                    tblHtml += `<td>${rtcd_last_of_loading_date}</td>`;
                    tblHtml += `<td>${rtcd_initial_activity_ci}</td>`;
                    tblHtml += `<td>${rtcd_source_size}</td>`;
                    tblHtml += `<td>${rtcd_pencil_no}</td>`;
                    if (rtcd_decay_chart_doc != "") {
                        let fullImagePath = uploadURL + rtcd_decay_chart_doc;
                        tblHtml += `<td style="text-align:center; vertical-align:middle;">
                            <a target="_blank" href="${fullImagePath}" title="View" style="font-size:18px; display:inline-block;">
                                <i class="ri-eye-fill"></i>
                            </a>
                            <input type='hidden' name='rtcd_decay_chart_doc[]' value="${rtcd_decay_chart_doc}"/>
                        </td>`;
                    } else {
                        tblHtml += `<td style="text-align:center; vertical-align:middle;">
                            <input type='hidden' name='rtcd_decay_chart_doc[]' value=""/>
                        </td>`;
                    }
                    // tblHtml += `<td>${rtcd_iga_no}</td>`;
                    tblHtml += `</tr>`;
                    jQuery('#RTCameraDetailTable tbody').append(tblHtml);
                    toastSuccess("Record Inserted.");
                    const input = document.getElementById('rt_document_ref_no');
                    input?.focus();
                }
            }

            if (formValue.form_type == "edit") {
                thisModal.modal('hide');
            } else {
                let formElement = document.getElementById('RTCameraDetailsForm');
                formElement.reset();
                jQuery('#rtcd_last_of_loading_date').val('');
                jQuery('#rtcd_initial_activity_ci').val('');
                jQuery('#rtcd_source_size').val('');
                jQuery('#rtcd_pencil_no').val('');
                jQuery('#rtcd_decay_chart_prev').attr('href', '#').addClass('hide');
                jQuery('#rtcd_decay_chart_remove').removeClass('i-block').addClass('hide');
                // jQuery('#rtcd_iga_no').val('');

                setTimeout(function () {
                    $(formElement).removeClass('was-validated');
                    $(formElement).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                    thisModal.find('.error').removeClass('error');
                    jQuery('#rtcd_last_of_loading_date').focus();
                }, 150);
            }
        }
    } else {
        toastr.error('Enter Date of Loading.');
    }
});


jQuery('#rt_isotope').on('change', function () {
    IsotopeChange();
});

function IsotopeChange() {
    let isotopeType = jQuery('#rt_isotope').val();
    // jQuery("#RTCameraDetailTable tbody").empty();
    // rt_camera_details_data = [];
    if (isotopeType == 'Ir-192' || isotopeType == 'Co-60') {
        jQuery('#commonRTCameraForm').find('.toggleButton').prop('disabled', false);
        jQuery('#commonRTCameraForm').find('.toggleButton').blur();

        jQuery('#commonRTCameraForm').find('#rt_x_ray').prop({ tabindex: -1, readonly: true });
        jQuery('#commonRTCameraForm').find('#rt_focal_spot').prop({ tabindex: -1, readonly: true });

        jQuery('#commonRTCameraForm').find('#rt_x_ray').val('');
        jQuery('#commonRTCameraForm').find('#rt_focal_spot').val('');

        jQuery('#xrayAsterisk').hide();
        jQuery('#focalspotAsterisk').hide();

        jQuery('#xrayValidationMessage').hide();
        jQuery('#focalspotValidationMessage').hide();
        jQuery('#rt_x_ray, #rt_focal_spot').removeClass('is-invalid');
    } else if (isotopeType == 'X-Ray') {
        jQuery('#commonRTCameraForm').find('#rt_x_ray').prop({ tabindex: 0, readonly: false });
        jQuery('#commonRTCameraForm').find('#rt_focal_spot').prop({ tabindex: 0, readonly: false });

        jQuery('#commonRTCameraForm').find('.toggleButton').prop('disabled', true);
        jQuery('#commonRTCameraForm').find('.toggleButton').blur();

        // jQuery('#commonRTCameraForm').find('#rt_x_ray').prop('required', true);
        // jQuery('#commonRTCameraForm').find('#rt_focal_spot').prop('required', true);

        jQuery('#xrayAsterisk').show();
        jQuery('#focalspotAsterisk').show();
        rt_camera_details_data = [];
        jQuery("#RTCameraDetailTable tbody").empty();
    } else {
        jQuery('#commonRTCameraForm').find('#rt_x_ray').prop({ tabindex: -1, readonly: true });
        jQuery('#commonRTCameraForm').find('#rt_focal_spot').prop({ tabindex: -1, readonly: true });

        jQuery('#commonRTCameraForm').find('#rt_x_ray').val('');
        jQuery('#commonRTCameraForm').find('#rt_focal_spot').val('');

        jQuery('#xrayAsterisk').hide();
        jQuery('#focalspotAsterisk').hide();

        jQuery('#xrayValidationMessage').hide();
        jQuery('#focalspotValidationMessage').hide();

        jQuery('#commonRTCameraForm').find('.toggleButton').prop('disabled', true);
        jQuery('#commonRTCameraForm').find('.toggleButton').blur();
        jQuery('#rt_x_ray, #rt_focal_spot').removeClass('is-invalid');



        // jQuery('#commonRTCameraForm').find('.toggleButton').prop('disabled', true);
        // jQuery('#commonRTCameraForm').find('.toggleButton').blur();
    }
}

$('#RTCameraModal').on('show.bs.modal', function () {
    var formIdblank = jQuery('#commonRTCameraForm').find('#id').val();
    if (formIdblank != "" && formIdblank != undefined) {
        jQuery('#commonRTCameraForm').find('#pending_btn').prop('disabled', true);
        // jQuery('#RTCameraModal').find('#rt_camera_name').focus();
        const input = document.getElementById('rt_camera_name');
        input?.focus();
    }
    if (formIdblank == "") {
        var thisForm = jQuery('#RTCameraModal');
        thisForm.find('#rt_status').val('Active').trigger('change');

        jQuery('#commonRTCameraForm').find('#rt_x_ray').prop({ tabindex: -1, readonly: true });
        jQuery('#commonRTCameraForm').find('#rt_focal_spot').prop({ tabindex: -1, readonly: true });
        GetPendingGRNforRTCamera();
        focusPendingButton('#RTCameraModal');

        jQuery('#commonRTCameraForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
        jQuery('#commonRTCameraForm').find('#grn_no').prop({ tabindex: -1, readonly: true });
        jQuery('#commonRTCameraForm').find('#grn_date').prop({ tabindex: -1, readonly: true });
        jQuery('#commonRTCameraForm').find('#supplier').prop({ tabindex: -1, readonly: true });
        jQuery('#commonRTCameraForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
        jQuery('#commonRTCameraForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
        jQuery('#commonRTCameraForm').find('#item_group').prop({ tabindex: -1, readonly: true });
        jQuery('#commonRTCameraForm').find('#main_group').prop({ tabindex: -1, readonly: true });
        jQuery('#submitbtn').prop('disabled', false);
    }
    IsotopeChange();

    var add_new_formIdblank = jQuery('#commonRTCameraForm').find('input[name="id"]').val();
    if (add_new_formIdblank && add_new_formIdblank !== "") {
        jQuery('#RTCameraModal').find('#add_new').show();
    } else {
        jQuery('#RTCameraModal').find('#add_new').hide();
    }
});

jQuery('#RTCameraModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#RTCameraModal');
    thisForm.find('#submitbtn').prop('disabled', false);
    thisForm.find('#id').val('');
    thisForm.find('#rt_isotope').val('').trigger('change');
    thisForm.find('#rt_status').val('Active').trigger('change');
    thisForm.find('#rt_aerb_no').prop('readonly', false);
    thisForm.find('#application_no').prop('readonly', false);
    thisForm.find('#validity').prop('readonly', false).datepicker('enable');
    thisForm.find('#movement_approval').prop('disabled', false);
    rt_camera_details_data = [];
    jQuery('#RTCameraDetailTable tbody').empty();
    document.getElementById("commonRTCameraForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).prop('checked', false);
    });
    jQuery('#RTCameraModal').find('#add_new').hide();
    clearMovement();
});

jQuery('#RTCameraModal').on('click', '#add_new', function () {
    jQuery('#RTCameraModal').find('#id').val('');
    document.getElementById("commonRTCameraForm").reset();
    const form = document.getElementById("commonRTCameraForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    setSelect2Readonly("#rt_status", true);
    rt_camera_details_data = [];
    jQuery('#RTCameraDetailTable tbody').empty();
    jQuery('#commonRTCameraForm').find('.toggleModalBtn').prop('disabled', true);
    jQuery('#commonRTCameraForm').find("#rt_isotope").val('').trigger('change.select2');
    jQuery('#rt_status').val('Active').trigger('change.select2');
    IsotopeChange();
    GetPendingGRNforRTCamera();
    jQuery('#commonRTCameraForm').find('#table_unique_id').prop({ tabindex: -1, readonly: true });
    jQuery('#commonRTCameraForm').find('#grn_no').prop({ tabindex: -1, readonly: true });
    jQuery('#commonRTCameraForm').find('#grn_date').prop({ tabindex: -1, readonly: true });
    jQuery('#commonRTCameraForm').find('#supplier').prop({ tabindex: -1, readonly: true });
    jQuery('#commonRTCameraForm').find('#challan_no').prop({ tabindex: -1, readonly: true });
    jQuery('#commonRTCameraForm').find('#challan_date').prop({ tabindex: -1, readonly: true });
    jQuery('#commonRTCameraForm').find('#item_group').prop({ tabindex: -1, readonly: true });
    jQuery('#commonRTCameraForm').find('#main_group').prop({ tabindex: -1, readonly: true });
    focusPendingButton('#RTCameraModal');
    jQuery('#submitbtn').prop('disabled', false);
    jQuery("#commonRTCameraForm #rt_status").val('Active').trigger("change.select2");
    clearDecayChart();
    clearMovement();
    // setTimeout(() => {
    //     const btn = jQuery('#RTCameraModal').find('.toggleModalBtn').filter(':visible').not(':disabled').first();
    //     if (btn.length > 0) {
    //         btn.trigger('focus');
    //         btn.css({ 'border-color': '#22b378', 'outline-offset': '2px', 'box-shadow': '0 0 0 0.25rem rgba(34, 179, 120, 0.25)' });
    //         btn.one('blur', function () {
    //             jQuery(this).css({ 'border-color': '', 'outline-offset': '', 'box-shadow': '' });
    //         });
    //     }
    // }, 1500);

    jQuery('#commonRTCameraForm').find('#id').val('');
    jQuery('#commonRTCameraForm').find('#table_pk_id').val('');
    jQuery('#commonRTCameraForm').find('#item_id').val('');
    jQuery('#commonRTCameraForm').find('#rtcd_id').val('');
    jQuery('#commonRTCameraForm').find('#old_loading_date').val('');
    jQuery('#RTCameraModal').find('#add_new').hide();
});

jQuery('#RTCameraDetailsModal').on('shown.bs.modal', function () {
    const input = document.getElementById('rtcd_last_of_loading_date');
    input?.focus();
});

jQuery('#RTCameraDetailsModal').on('hide.bs.modal', function (e) {
    let thisModal = jQuery('#RTCameraDetailsModal');
    thisModal.find('#submitbtn').prop('disabled', false);
    thisModal.find("#form_type").val("add");
    thisModal.find("#form_index").val("");
    thisModal.find("#row_index").val("");
    thisModal.find('#old_loading_date').val("");
    jQuery('#RTCameraDetailsForm').trigger("reset");
    // jQuery('#RTCameraModal').find('#rt_document_ref_no').focus();
    this.dataset.customHideFocus = 'true';
    const input = document.getElementById('rt_document_ref_no');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }
    clearDecayChart();
});

// $('#rt_isotope').on('change', function () {
//     let isotope = $(this).val();
//     if (isotope !== 'X-Ray') {
//         $('#rt_x_ray, #rt_focal_spot').removeClass('is-invalid');
//         $('#xrayValidationMessage, #focalspotValidationMessage').hide();
//         // rt_camera_details_data =[];
//         // jQuery("#RTCameraDetailTable tbody").empty();
//     }
// });

// pending logic code
function GetPendingGRNforRTCamera() {
    var thisModal = jQuery('#PendingForGRNModal');
    var thisForm = jQuery('#commonRTCameraForm');

    jQuery.ajax({
        url: 'get-grn_list_for_rt_camera',
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

                    thisForm.find('#PendingForRTCameraTable tbody input[name="form_indx"]').each(function (indx) {
                        let frmIndx = jQuery(this).val();

                        let jbEorkOrderId = opening_data[frmIndx].table_pk_id;
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
                    if (data.opening_data.length > 0 && !jQuery.isEmptyObject(data.opening_data)) {

                        var formIdblank = jQuery('#commonRTCameraForm').find('#id').val();
                        if (formIdblank != "" && formIdblank != undefined) {
                            jQuery('#pending_btn').prop('disabled', true);
                        } else {
                            jQuery('#pending_btn').prop('disabled', false);
                        }
                        found = 1;
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
                                    <td colspan="12">No Pending Available</td>
                                </tr>`;
                        jQuery('#pending_btn').prop('disabled', true);
                    }

                    var $table = jQuery("#PendingForGRNModal").find('#PendingForRTCameraTable');
                    if (jQuery.fn.DataTable.isDataTable($table)) {
                        $table.DataTable().clear().destroy();
                    }
                    jQuery('#PendingForRTCameraTable tbody').empty().append(tblHtml);
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
                // jQuery('.toggleModalBtn').prop('disabled', false);
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

jQuery('#rt_camera_name, #rt_serial_no').on('change', function () {
    CheckRTCamera();
});

function CheckRTCamera() {
    var rt_camera_name = jQuery('#rt_camera_name').val();
    var rt_serial_no = jQuery('#rt_serial_no').val();
    var formId = jQuery('#RTCameraModal').find('#commonRTCameraForm').find('#id').val();
    var formUrl = formId != undefined && formId != "" ? "verify-rt_camera?rt_camera_name=" + encodeURIComponent(rt_camera_name) + "&rt_serial_no=" + encodeURIComponent(rt_serial_no) + "&camera_id=" + formId : "verify-rt_camera?rt_camera_name=" + encodeURIComponent(rt_camera_name) + "&rt_serial_no=" + encodeURIComponent(rt_serial_no);
    if (rt_camera_name != '' && rt_camera_name != "") {
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

jQuery('#PendingForGRNModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    const input = jQuery('#table_pk_id').val() != '' ? document.getElementById('rt_camera_name') : document.getElementById('pending_btn');
    if (input) {
        setTimeout(() => {
            input.focus();
        }, 100);
    }
});

$('#PendingForRCameraForm').on('submit', function (e) {
    e.preventDefault();
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#PendingForGRNModal').find('#submitbtn').prop('disabled', true);

    let chkArr = [];
    let uniqueArr = [];
    jQuery("#PendingForRCameraForm").find("[id^='table_pk_ids_']:checked")
        .each(function () {
            chkArr.push(jQuery(this).val());
            uniqueArr.push(jQuery(this).data('table_unique_id'));
        });

    if (chkArr.length === 0) {
        toastr.error('Select At Least One Pending.');
        //  toastr.error('Select Atleast One Pending.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PendingForGRNModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    jQuery.ajax({
        url: 'get-grn_part_data_rt_camera',
        type: 'GET',
        data: {
            table_pk_ids: chkArr.join(','),
            table_unique_ids: uniqueArr.join(',')
        },
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1) {
                if (data.opening_data) {

                    jQuery('#RTCameraModal').find("#table_pk_id").val(data.opening_data.table_pk_id);
                    jQuery('#RTCameraModal').find('#item_group').val(data.opening_data.item_group);
                    jQuery('#RTCameraModal').find("#main_group").val(data.opening_data.item_type);
                    jQuery('#RTCameraModal').find("#table_unique_id").val(data.opening_data.table_unique_id);
                    jQuery('#RTCameraModal').find("#item_id").val(data.opening_data.item_id);
                    jQuery('#RTCameraModal').find("#grn_no").val(data.opening_data.grn_number != null ? data.opening_data.grn_number : '');
                    jQuery('#RTCameraModal').find("#grn_date").val(data.opening_data.grn_date != null ? data.opening_data.grn_date : '');
                    jQuery('#RTCameraModal').find("#supplier").val(data.opening_data.supplier_name);
                    jQuery('#RTCameraModal').find("#challan_no").val(data.opening_data.grn_challan_number != null ? data.opening_data.grn_challan_number : '');
                    jQuery('#RTCameraModal').find("#challan_date").val(data.opening_data.grn_challan_date != null ? data.opening_data.grn_challan_date : '');
                    jQuery('.toggleModalBtn').prop('disabled', true);
                } else {
                    jQuery('#RTCameraModal').find("#table_pk_id").val('');
                    jQuery('#RTCameraModal').find('#item_group').val('');
                    jQuery('#RTCameraModal').find("#main_group").val('');
                    jQuery('#RTCameraModal').find("#table_unique_id").val('');
                    jQuery('#RTCameraModal').find("#item_id").val('');
                    jQuery('#RTCameraModal').find("#grn_no").val('');
                    jQuery('#RTCameraModal').find("#grn_date").val('');
                    jQuery('#RTCameraModal').find("#supplier").val('');
                    jQuery('#RTCameraModal').find("#challan_no").val('');
                    jQuery('#RTCameraModal').find("#challan_date").val('');
                    jQuery('.toggleModalBtn').prop('disabled', false);
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


jQuery('#PendingForGRNModal').on('show.bs.modal', function (e) {
    var dt = jQuery('#PendingForRTCameraTable').DataTable();
    fixDataTableColumnsUntilAdjusted(dt);
    var usedParts = [];
    var table_pk_id = jQuery('#RTCameraModal').find('#table_pk_id').val();
    if (table_pk_id != "" && table_pk_id != null) {
        usedParts.push(Number(table_pk_id));
    }
    function isUsed(pjId) {
        if (usedParts.includes(Number(pjId))) {
            return true;
        }
        return false;
    }

    jQuery('#PendingForRTCameraTable tbody tr').each(function (indx) {
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


/* File Upload */

function validateDecayChartImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.pdf)$/i;
    return allowedExtensions.exec(filePath) ? true : false;
}

jQuery('#RTCameraDetailsForm #rtcd_decay_chart').on('change', function (e) {
    DecayChartfileUpload(e);
});

function DecayChartfileUpload(e) {
    var form_data = new FormData();
    var target = e.target;
    var id = target.id;
    var files = target.files;
    var totalfiles = files.length;
    var oldImg = jQuery('#rtcd_decay_chart_doc').val();
    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');

    if (totalfiles > 0) {
        var notValid = 0;
        for (var index = 0; index < totalfiles; index++) {
            if (validateDecayChartImage(files[index].name) == true) {
                form_data.append("docs[]", files[index]);
            } else {
                notValid = 1;
                toastr.error("Only (jpeg,jpg,png,gif,pdf) files are allowed.");
                jQuery('#rtcd_decay_chart_doc').val('');
                jQuery('#' + id).val('');
                jQuery('#rtcd_decay_chart_prev').attr('href', '#').addClass('hide');
                jQuery('#rtcd_decay_chart_remove').removeClass('i-block').addClass('hide');
                e.stopImmediatePropagation();
                return false;
            }
        }

        if (notValid == 0) {
            jQuery('#RTCameraDetailsModal').find('#submitbtn').prop('disabled', true);
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
                    jQuery('#RTCameraDetailsModal').find('#submitbtn').prop('disabled', false);
                    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    if (data.response_code == 1) {
                        if (oldImg != "" && oldImg.includes('temp_media/')) {
                            removeMediaDecayChart(oldImg);
                        }
                        $('#rtcd_decay_chart_doc').val(data.files);
                        $('#rtcd_decay_chart_prev').attr('href', data.files_url).removeClass('hide');
                        $('#rtcd_decay_chart_remove').addClass('i-block').removeClass('hide');
                        toggleRequired();
                        console.log(data.response_message);
                    } else {
                        console.log(data.response_message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#RTCameraDetailsModal').find('#submitbtn').prop('disabled', false);
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
            let fileInput = jQuery('#movement_approval');
            let dataTransfer = new DataTransfer();
            dataTransfer.items.add(new File([""], fileName));
            fileInput[0].files = dataTransfer.files;
            return false;
        }
        clearDecayChart();
    }
}

function toggleRequired() {
    let docVal = jQuery('#RTCameraDetailsModal').find('#rtcd_decay_chart_doc').val();
    if (docVal && docVal !== "") {
        jQuery('#RTCameraDetailsModal').find('#rtcd_decay_chart_doc').prop('required', false);
    } else {
        jQuery('#RTCameraDetailsModal').find('#rtcd_decay_chart_doc').prop('required', true);
    }
}

function clearDecayChart() {
    jQuery('#RTCameraDetailsModal').find('#rtcd_decay_chart_doc').val('');
    jQuery('#RTCameraDetailsModal').find('#rtcd_decay_chart').val('');
    jQuery('#RTCameraDetailsModal').find('#rtcd_decay_chart_prev').attr('href', '#').addClass('hide');
    jQuery('#RTCameraDetailsModal').find('#rtcd_decay_chart_remove').removeClass('i-block').addClass('hide');
    toggleRequired();
}

function removeFileDecayChart(e) {
    e.stopImmediatePropagation();
    toastDetailDelete('Are You Sure, You Want <lw-c>To</lw-c> Delete ?', () => {
        var oldImg = jQuery('#rtcd_decay_chart_doc').val();
        if (oldImg != "" && oldImg.includes('temp_media/')) {
            removeMediaDecayChart(oldImg);
        }
        clearDecayChart();
    });
}

function removeMediaDecayChart(docName) {
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

function validatePermissionImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.pdf)$/i;
    return allowedExtensions.exec(filePath) ? true : false;
}

jQuery('#commonRTCameraForm #movement_approval').on('change', function (e) {
    MovementfileUpload(e);
});

function MovementfileUpload(e) {
    var form_data = new FormData();
    var target = e.target;
    var id = target.id;
    var files = target.files;
    var totalfiles = files.length;
    var oldImg = jQuery('#movement_approval_doc').val();
    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');

    if (totalfiles > 0) {
        var notValid = 0;
        for (var index = 0; index < totalfiles; index++) {
            if (validatePermissionImage(files[index].name) == true) {
                form_data.append("docs[]", files[index]);
            } else {
                notValid = 1;
                toastr.error("Only (jpeg,jpg,png,gif,pdf) files are allowed.");
                jQuery('#movement_approval_doc').val('');
                jQuery('#' + id).val('');
                jQuery('#movement_approval_prev').attr('href', '#').addClass('hide');
                jQuery('#movement_approval_remove').removeClass('i-block').addClass('hide');
                e.stopImmediatePropagation();
                return false;
            }
        }

        if (notValid == 0) {
            jQuery('#RTCameraModal').find('#submitbtn').prop('disabled', true);
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
                    jQuery('#RTCameraModal').find('#submitbtn').prop('disabled', false);
                    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    if (data.response_code == 1) {
                        if (oldImg != "" && oldImg.includes('temp_media/')) {
                            removeMediaMovement(oldImg);
                        }
                        $('#movement_approval_doc').val(data.files);
                        $('#movement_approval_prev').attr('href', data.files_url).removeClass('hide');
                        $('#movement_approval_remove').addClass('i-block').removeClass('hide');
                        toggleRequired();
                        console.log(data.response_message);
                    } else {
                        console.log(data.response_message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#RTCameraModal').find('#submitbtn').prop('disabled', false);
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
            let fileInput = jQuery('#movement_approval');
            let dataTransfer = new DataTransfer();
            dataTransfer.items.add(new File([""], fileName));
            fileInput[0].files = dataTransfer.files;
            return false;
        }
        clearMovement();
    }
}

function clearMovement() {
    jQuery('#RTCameraModal').find('#movement_approval_doc').val('');
    jQuery('#RTCameraModal').find('#movement_approval').val('');
    jQuery('#RTCameraModal').find('#movement_approval_prev').attr('href', '#').addClass('hide');
    jQuery('#RTCameraModal').find('#movement_approval_remove').removeClass('i-block').addClass('hide');
    toggleRequired();
}

function removeFileMovement(e) {
    e.stopImmediatePropagation();
    toastDetailDelete('Are You Sure, You Want <lw-c>To</lw-c> Delete ?', () => {
        var oldImg = jQuery('#movement_approval_doc').val();
        if (oldImg != "" && oldImg.includes('temp_media/')) {
            removeMediaMovement(oldImg);
        }
        clearMovement();
    });
}

function removeMediaMovement(docName) {
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

function toggleRequired() {
    let docVal = jQuery('#RTCameraModal').find('#movement_approval_doc').val();
    if (docVal && docVal !== "") {
        jQuery('#RTCameraModal').find('#movement_approval_doc').prop('required', false);
    } else {
        jQuery('#RTCameraModal').find('#movement_approval_doc').prop('required', true);
    }
}