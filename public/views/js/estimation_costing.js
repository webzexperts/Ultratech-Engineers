var formId = jQuery('#EstimationPendingModal').find('#commonEstimationCostingForm').find('#id').val();

var headerOpt = {
    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
};

function focusEstimationCostingEntry() {
    const $modal = jQuery('#EstimationCostingModal');
    const $pendingBtn = $modal.find('#pending_btn').filter(':visible').first();

    if ($pendingBtn.length && !$pendingBtn.prop('disabled')) {
        $pendingBtn[0].focus();
        $pendingBtn.css({
            'border-color': '#22b378',
            'outline-offset': '2px',
            'box-shadow': '0 0 0 0.25rem rgba(34, 179, 120, 0.25)'
        });
        $pendingBtn.off('blur.focusEstimation').on('blur.focusEstimation', function () {
            jQuery(this).css({
                'border-color': '',
                'outline-offset': '',
                'box-shadow': ''
            });
        });
        return true;
    }

    const ecDate = document.getElementById('ec_date');
    if (ecDate) {
        ecDate.focus();
        if (jQuery(ecDate).hasClass('trans-date-picker')) {
            jQuery(ecDate).datepicker('hide');
        }
        return true;
    }

    return false;
}

function initEstimationCostingFocus() {
    const $modal = jQuery('#EstimationCostingModal');

    $modal.off('shown.bs.modal.focusEstimation');
    $modal.on('shown.bs.modal.focusEstimation', function () {
        const modal = jQuery(this);
        modal.attr('tabindex', '-1');

        let attempts = 0;
        const maxAttempts = 25;
        const interval = setInterval(function () {
            attempts++;
            if (focusEstimationCostingEntry() || attempts >= maxAttempts) {
                clearInterval(interval);
            }
        }, 200);

        modal.find('form').off('reset.focusEstimation').on('reset.focusEstimation', function () {
            setTimeout(focusEstimationCostingEntry, 100);
        });

        $modal.off('hidden.bs.modal.focusEstimation').on('hidden.bs.modal.focusEstimation', function () {
            clearInterval(interval);
        });
    });
}

// Edit estimation & costing row click
jQuery('#dyntable tbody').on('click', '.edit-estimation_costing', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["ec_id"]) {
        fetchAndFillEstimationCosting(data["ec_id"]);
    }
});

// Function to fetch and fill estimation & costing data
function fetchAndFillEstimationCosting(id) {
    if (!id) return;
    jQuery('#EstimationCostingModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-estimation_costing",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.estimation_data != null) {
                jQuery('#EstimationCostingModal').find('#ec_number').val(data.estimation_data.ec_number != "" ? data.estimation_data.ec_number : "");
                jQuery('#EstimationCostingModal').find('#ec_date').val(data.estimation_data.ec_date != "" ? data.estimation_data.ec_date : "");
                jQuery('#EstimationCostingModal').find('#ec_sequence').val(data.estimation_data.ec_sequence != "" ? data.estimation_data.ec_sequence : "");
                jQuery('#EstimationCostingModal').find('#ec_inqd_id').val(data.estimation_data.ec_inqd_id != "" ? data.estimation_data.ec_inqd_id : "");
                jQuery('#EstimationCostingModal').find('#ec_fr_id').val(data.estimation_data.ec_fr_id != "" ? data.estimation_data.ec_fr_id : "");
                jQuery('#EstimationCostingModal').find('#inq_number').val(data.estimation_data.inq_number != "" ? data.estimation_data.inq_number : "");
                jQuery('#EstimationCostingModal').find('#inq_date').val(data.estimation_data.inq_date != "" ? data.estimation_data.inq_date : "");
                jQuery('#EstimationCostingModal').find('#inqd_test_method').val(data.estimation_data.inqd_test_method != "" ? data.estimation_data.inqd_test_method : "").trigger("change.select2");
                jQuery('#EstimationCostingModal').find('#inqd_type_of_job_id').val(data.estimation_data.type_of_job != "" ? data.estimation_data.type_of_job : "");
                jQuery('#EstimationCostingModal').find('#inqd_job_description_id').val(zeroToEmpty(data.estimation_data.inqd_job_description_id)).trigger("change.select2");
                jQuery('#EstimationCostingModal').find('#inqd_part_id').val(zeroToEmpty(data.estimation_data.inqd_part_id)).trigger("change.select2");
                jQuery('#EstimationCostingModal').find('#inqd_process_at').val(zeroToEmpty(data.estimation_data.inqd_process_at)).trigger("change.select2");
                jQuery('#EstimationCostingModal').find('#inqd_description').val(data.estimation_data.inqd_description != "" ? data.estimation_data.inqd_description : "");
                // jQuery('#EstimationCostingModal').find('#inqd_quantity').val(parseFloat(data.estimation_data.inqd_quantity != null ? data.estimation_data.inqd_quantity : "").toFixed(3));
                jQuery('#EstimationCostingModal').find('#inqd_quantity').val(parseFloat(data.estimation_data.inqd_quantity != null ? data.estimation_data.inqd_quantity : "").toFixed(2));
                jQuery('#EstimationCostingModal').find('#inqd_unit_id').val(zeroToEmpty(data.estimation_data.inqd_unit_id)).trigger("change.select2");
                jQuery('#EstimationCostingModal').find('#ec_estimation').val(data.estimation_data.ec_estimation != "" ? data.estimation_data.ec_estimation : "");
                jQuery('#EstimationCostingModal').find('#ec_costing').val(data.estimation_data.ec_costing != "" ? data.estimation_data.ec_costing : "");
                jQuery('#EstimationCostingModal').find('#ec_prepared_by_id').val(data.estimation_data.ec_prepared_by_id != "" ? data.estimation_data.ec_prepared_by_id : "").trigger("change.select2");
                jQuery('#EstimationCostingModal').find('#inq_customer_id').val(zeroToEmpty(data.estimation_data.inq_customer_id)).trigger("change.select2");
                jQuery('#EstimationCostingModal').find('#inq_ref_no_date').val(data.estimation_data.inq_ref_no_date != "" ? data.estimation_data.inq_ref_no_date : "");
                jQuery('#EstimationCostingModal').find('#inqd_remark').val(data.estimation_data.inqd_remark != "" ? data.estimation_data.inqd_remark : "");
                jQuery('#EstimationCostingModal').find('#inq_sp_note').val(data.estimation_data.inq_sp_note != "" ? data.estimation_data.inq_sp_note : "");
                jQuery('#EstimationCostingModal').find('#ec_reviewed_by_id').val(data.estimation_data.ec_reviewed_by_id != "" ? data.estimation_data.ec_reviewed_by_id : "").trigger("change.select2");
                jQuery('#EstimationCostingModal').find('#id').val(data.estimation_data.ec_id != "" ? data.estimation_data.ec_id : "");
                jQuery('#EstimationCostingModal').find('#inqd_id').val(data.estimation_data.inqd_id != "" ? data.estimation_data.inqd_id : "");
                // let filePath = data.estimation_data.inqd_file_upload;
                // let $eye = jQuery('#inqd_file_upload_prev');

                // if (filePath && filePath.trim() !== "") {
                //     let fullUrl = uploadURL + filePath;

                //     $eye.attr('href', fullUrl).removeClass('hide');

                // } else {

                //     $eye.attr('href', '#').addClass('hide');
                // }

                // let filePathfr = data.estimation_data.fr_upload_file;
                // let $eyeicon = jQuery('#fr_file_upload_prev');

                // if (filePathfr && filePathfr.trim() !== "") {

                //     let fullUrl = uploadURL + filePathfr;
                //     $eyeicon.attr('href', fullUrl).removeClass('hide');

                // } else {

                //     $eyeicon.attr('href', '#').addClass('hide');
                // }

                if (data.estimation_data.inqd_file_upload != "" && data.estimation_data.inqd_file_upload != undefined) {

                    jQuery('#EstimationCostingModal').find('#inqd_file_upload_prev').attr('href', uploadURL + data.estimation_data.inqd_file_upload);
                    jQuery('#EstimationCostingModal').find('#inqd_file_upload_prev').text(data.estimation_data.inqd_file_upload.replace('uploads/', ''));
                    jQuery('#EstimationCostingModal').find('#inqd_file_upload_prev').removeClass('hide');
                    jQuery('#EstimationCostingModal').find('#inqd_file_upload_img-prev-box').removeClass('hide');
                    // jQuery('#EstimationCostingModal').find('#inqd_file_upload_remove').addClass('i-block').removeClass('hide');
                } else {
                    jQuery('#EstimationCostingModal').find('#inqd_file_upload_doc').val('');
                    jQuery('#EstimationCostingModal').find('#inqd_file_upload_prev').attr('href', '#');
                    jQuery('#EstimationCostingModal').find('#inqd_file_upload_prev').addClass('hide');
                    jQuery('#EstimationCostingModal').find('#inqd_file_upload_remove').removeClass('i-block').addClass('hide');
                    jQuery('#EstimationCostingModal').find('#inqd_file_upload_img-prev').html('');
                    jQuery('#EstimationCostingModal').find('#inqd_file_upload_img-prev-box').addClass('hide');
                    jQuery('#EstimationCostingModal').find('#inqd_file_upload_img-prev-box').html('');
                }
                if (data.estimation_data.fr_upload_file != "" && data.estimation_data.fr_upload_file != undefined) {

                    jQuery('#EstimationCostingModal').find('#fr_file_upload_prev').attr('href', uploadURL + data.estimation_data.fr_upload_file);
                    jQuery('#EstimationCostingModal').find('#fr_file_upload_prev').text(data.estimation_data.fr_upload_file.replace('uploads/', ''));
                    jQuery('#EstimationCostingModal').find('#fr_file_upload_prev').removeClass('hide');
                    jQuery('#EstimationCostingModal').find('#fr_file_upload_img-prev-box').removeClass('hide');
                    // jQuery('#EstimationCostingModal').find('#inqd_file_upload_remove').addClass('i-block').removeClass('hide');
                } else {
                    jQuery('#EstimationCostingModal').find('#fr_file_upload_doc').val('');
                    jQuery('#EstimationCostingModal').find('#fr_file_upload_prev').attr('href', '#');
                    jQuery('#EstimationCostingModal').find('#fr_file_upload_prev').addClass('hide');
                    jQuery('#EstimationCostingModal').find('#fr_file_upload_remove').removeClass('i-block').addClass('hide');
                    jQuery('#EstimationCostingModal').find('#fr_file_upload_img-prev').html('');
                    jQuery('#EstimationCostingModal').find('#fr_file_upload_img-prev-box').addClass('hide');
                    jQuery('#EstimationCostingModal').find('#fr_file_upload_img-prev-box').html('');
                }

                // if (data.estimation_data.ec_upload_file != "" && data.estimation_data.ec_upload_file != undefined) {
                //     jQuery('#EstimationCostingModal').find("#ec_file_upload_doc").val(data.estimation_data.ec_upload_file);
                //     jQuery('#EstimationCostingModal').find('#ec_file_upload_prev').attr('href', uploadURL + data.estimation_data.ec_upload_file);
                //     jQuery('#EstimationCostingModal').find('#ec_file_upload_prev').removeClass('hide');
                //     jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev-box').removeClass('hide');
                //     jQuery('#EstimationCostingModal').find('#ec_file_upload_remove').addClass('i-block').removeClass('hide');
                // } else {
                //     jQuery('#EstimationCostingModal').find('#ec_file_upload_doc').val('');
                //     jQuery('#EstimationCostingModal').find('#ec_file_upload_prev').attr('href', '#');
                //     jQuery('#EstimationCostingModal').find('#ec_file_upload_prev').addClass('hide');
                //     jQuery('#EstimationCostingModal').find('#ec_file_upload_remove').removeClass('i-block').addClass('hide');
                //     jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev').html('');
                //     jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev-box').addClass('hide');
                //     jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev-box').html('');
                // }

                if (data.estimation_data.ec_upload_file != "" && data.estimation_data.ec_upload_file != undefined) {
                    let fullPath = data.estimation_data.ec_upload_file;
                    let fileName = fullPath.split('/').pop();
                    jQuery('#EstimationCostingModal').find("#ec_file_upload_doc").val(fullPath);
                    jQuery('#EstimationCostingModal').find('#ec_file_upload_prev').attr('href', uploadURL + fullPath).removeClass('hide');
                    jQuery('#EstimationCostingModal').find('#ec_file_upload_remove').addClass('i-block').removeClass('hide');
                    jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev-box').removeClass('hide');
                    jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev').html(fileName);
                    let fileInput = jQuery('#EstimationCostingModal').find('#ec_file_upload');
                    let newFile = new DataTransfer();
                    newFile.items.add(new File([""], fileName));
                    fileInput[0].files = newFile.files;
                } else {
                    jQuery('#EstimationCostingModal').find('#ec_file_upload_doc').val('');
                    jQuery('#EstimationCostingModal').find('#ec_file_upload_prev').attr('href', '#').addClass('hide');
                    jQuery('#EstimationCostingModal').find('#ec_file_upload_remove').removeClass('i-block').addClass('hide');
                    jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev').html('');
                    jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev-box').addClass('hide');
                    jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev-box').html('');
                    jQuery('#EstimationCostingModal').find('#ec_file_upload').val('');
                }

                jQuery('#EstimationCostingModal').find('#pending_btn').prop('disabled', true);
                jQuery('#EstimationCostingModal').find('#submitbtn').prop('disabled', false);

                jQuery('#EstimationCostingModal').find('#add_new').show();

                setTimeout(function () {
                    const $frDate = jQuery('#commonEstimationCostingForm #ec_date');
                    $frDate.trigger('focus');
                    setTimeout(function () {
                        if ($frDate.hasClass('trans-date-picker')) {
                            $frDate.datepicker('hide');
                        }
                    }, 0);
                }, 150);

                const form = document.getElementById("commonEstimationCostingForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#EstimationCostingModal').find('#ec_date').focus();
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

// Reset button click for feasibility review modal
jQuery('#resetbtn').on('click', function () {
    var formId = jQuery('#EstimationCostingModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonEstimationCostingForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        focusEstimationCostingEntry();

        jQuery("#commonEstimationCostingForm #inqd_test_method").val('').trigger("change.select2");
        jQuery("#commonEstimationCostingForm #inqd_job_description_id").val('').trigger("change.select2");
        jQuery("#commonEstimationCostingForm #inqd_part_id").val('').trigger("change.select2");
        jQuery("#commonEstimationCostingForm #inqd_process_at").val('').trigger("change.select2");
        jQuery("#commonEstimationCostingForm #inqd_unit_id").val('').trigger("change.select2");
        jQuery('#commonEstimationCostingForm #inq_customer_id').val('').trigger("change.select2");
        jQuery('#commonEstimationCostingForm #ec_prepared_by_id').val(loginUserId).trigger("change.select2");
        jQuery('#commonEstimationCostingForm #ec_reviewed_by_id').val('').trigger("change.select2");

        jQuery('#EstimationCostingModal #inqd_file_upload_prev').attr('href', '#').addClass('hide');
        jQuery('#EstimationCostingModal #fr_file_upload_prev').attr('href', '#').addClass('hide');

        jQuery('#EstimationCostingModal').find('#ec_file_upload_doc').val('');
        jQuery('#EstimationCostingModal').find('#ec_file_upload_prev').attr('href', '#');
        jQuery('#EstimationCostingModal').find('#ec_file_upload_prev').addClass('hide');
        jQuery('#EstimationCostingModal').find('#ec_file_upload_remove').removeClass('i-block').addClass('hide');
        jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev').html('');
        jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev-box').addClass('hide');
        jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev-box').html('');
        getLatestEstimationCostingNo();
        getPendingEstimation();
    } else {
        fetchAndFillEstimationCosting(formId);
    }
});

// Add New button click for estimation & costing modal
jQuery('#add_new').on('click', function () {
    var form = document.getElementById("commonEstimationCostingForm");
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
    }

    // Clear hidden IDs so the form is in create mode
    jQuery('#EstimationCostingModal').find('#id').val('');
    jQuery('#EstimationCostingModal').find('#inqd_id').val('');
    jQuery('#EstimationCostingModal').find('#ec_fr_id').val('');

    // Clear select2 fields
    jQuery("#commonEstimationCostingForm #inqd_test_method").val('').trigger("change.select2");
    jQuery("#commonEstimationCostingForm #inqd_job_description_id").val('').trigger("change.select2");
    jQuery("#commonEstimationCostingForm #inqd_part_id").val('').trigger("change.select2");
    jQuery("#commonEstimationCostingForm #inqd_process_at").val('').trigger("change.select2");
    jQuery("#commonEstimationCostingForm #inqd_unit_id").val('').trigger("change.select2");
    jQuery('#commonEstimationCostingForm #inq_customer_id').val('').trigger("change.select2");
    jQuery('#commonEstimationCostingForm #ec_prepared_by_id').val(loginUserId).trigger("change.select2");
    jQuery('#commonEstimationCostingForm #ec_reviewed_by_id').val('').trigger("change.select2");

    // Clear file upload previews
    jQuery('#EstimationCostingModal #inqd_file_upload_prev').attr('href', '#').addClass('hide');
    jQuery('#EstimationCostingModal #fr_file_upload_prev').attr('href', '#').addClass('hide');
    jQuery('#EstimationCostingModal').find('#ec_file_upload_doc').val('');
    jQuery('#EstimationCostingModal').find('#ec_file_upload_prev').attr('href', '#').addClass('hide');
    jQuery('#EstimationCostingModal').find('#ec_file_upload_remove').removeClass('i-block').addClass('hide');
    jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev').html('');
    jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev-box').addClass('hide').html('');
    jQuery('#EstimationCostingModal').find('#ec_file_upload').val('');

    // Re-enable pending button and fetch fresh data
    jQuery('#EstimationCostingModal').find('#pending_btn').prop('disabled', false);
    jQuery('#EstimationCostingModal').find('#submitbtn').prop('disabled', false);

     jQuery('#EstimationCostingModal').find('#add_new').hide();

    getLatestEstimationCostingNo();
    getPendingEstimation();

    focusEstimationCostingEntry();
});

// jQuery('#dyntable tbody').on('click', '.edit-estimation_costing', function () {

//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#EstimationCostingModal').find('#id').val(data["ec_id"]);
//     jQuery('#EstimationCostingModal').modal('show');
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-estimation_costing",
//         type: 'GET',
//         data: "id=" + data["ec_id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {

//                 jQuery('#EstimationCostingModal').find('#ec_number').val(data.estimation_data.ec_number != "" ? data.estimation_data.ec_number : "");
//                 jQuery('#EstimationCostingModal').find('#ec_date').val(data.estimation_data.ec_date != "" ? data.estimation_data.ec_date : "");
//                 jQuery('#EstimationCostingModal').find('#ec_sequence').val(data.estimation_data.ec_sequence != "" ? data.estimation_data.ec_sequence : "");
//                 jQuery('#EstimationCostingModal').find('#ec_inqd_id').val(data.estimation_data.ec_inqd_id != "" ? data.estimation_data.ec_inqd_id : "");
//                 jQuery('#EstimationCostingModal').find('#ec_fr_id').val(data.estimation_data.ec_fr_id != "" ? data.estimation_data.ec_fr_id : "");
//                 jQuery('#EstimationCostingModal').find('#inq_number').val(data.estimation_data.inq_number != "" ? data.estimation_data.inq_number : "");
//                 jQuery('#EstimationCostingModal').find('#inq_date').val(data.estimation_data.inq_date != "" ? data.estimation_data.inq_date : "");
//                 jQuery('#EstimationCostingModal').find('#inqd_test_method').val(data.estimation_data.inqd_test_method != "" ? data.estimation_data.inqd_test_method : "").trigger("change.select2");
//                 jQuery('#EstimationCostingModal').find('#inqd_type_of_job_id').val(data.estimation_data.type_of_job != "" ? data.estimation_data.type_of_job : "");
//                 jQuery('#EstimationCostingModal').find('#inqd_job_description_id').val(zeroToEmpty(data.estimation_data.inqd_job_description_id)).trigger("change.select2");
//                 jQuery('#EstimationCostingModal').find('#inqd_part_id').val(zeroToEmpty(data.estimation_data.inqd_part_id)).trigger("change.select2");
//                 jQuery('#EstimationCostingModal').find('#inqd_process_at').val(zeroToEmpty(data.estimation_data.inqd_process_at)).trigger("change.select2");
//                 jQuery('#EstimationCostingModal').find('#inqd_description').val(data.estimation_data.inqd_description != "" ? data.estimation_data.inqd_description : "");
//                 jQuery('#EstimationCostingModal').find('#inqd_quantity').val(parseFloat(data.estimation_data.inqd_quantity != null ? data.estimation_data.inqd_quantity : "").toFixed(3));
//                 jQuery('#EstimationCostingModal').find('#inqd_unit_id').val(zeroToEmpty(data.estimation_data.inqd_unit_id)).trigger("change.select2");
//                 jQuery('#EstimationCostingModal').find('#ec_estimation').val(data.estimation_data.ec_estimation != "" ? data.estimation_data.ec_estimation : "");
//                 jQuery('#EstimationCostingModal').find('#ec_costing').val(data.estimation_data.ec_costing != "" ? data.estimation_data.ec_costing : "");
//                 jQuery('#EstimationCostingModal').find('#ec_prepared_by_id').val(data.estimation_data.ec_prepared_by_id != "" ? data.estimation_data.ec_prepared_by_id : "").trigger("change.select2");
//                 jQuery('#EstimationCostingModal').find('#inq_customer_id').val(zeroToEmpty(data.estimation_data.inq_customer_id)).trigger("change.select2");
//                 jQuery('#EstimationCostingModal').find('#inq_ref_no_date').val(data.estimation_data.inq_ref_no_date != "" ? data.estimation_data.inq_ref_no_date : "");
//                 jQuery('#EstimationCostingModal').find('#inqd_remark').val(data.estimation_data.inqd_remark != "" ? data.estimation_data.inqd_remark : "");
//                 jQuery('#EstimationCostingModal').find('#inq_sp_note').val(data.estimation_data.inq_sp_note != "" ? data.estimation_data.inq_sp_note : "");
//                 jQuery('#EstimationCostingModal').find('#ec_reviewed_by_id').val(data.estimation_data.ec_reviewed_by_id != "" ? data.estimation_data.ec_reviewed_by_id : "").trigger("change.select2");
//                 jQuery('#EstimationCostingModal').find('#id').val(data.estimation_data.ec_id != "" ? data.estimation_data.ec_id : "");
//                 jQuery('#EstimationCostingModal').find('#inqd_id').val(data.estimation_data.inqd_id != "" ? data.estimation_data.inqd_id : "");
//                 // let filePath = data.estimation_data.inqd_file_upload;
//                 // let $eye = jQuery('#inqd_file_upload_prev');

//                 // if (filePath && filePath.trim() !== "") {
//                 //     let fullUrl = uploadURL + filePath;

//                 //     $eye.attr('href', fullUrl).removeClass('hide');

//                 // } else {

//                 //     $eye.attr('href', '#').addClass('hide');
//                 // }

//                 // let filePathfr = data.estimation_data.fr_upload_file;
//                 // let $eyeicon = jQuery('#fr_file_upload_prev');

//                 // if (filePathfr && filePathfr.trim() !== "") {

//                 //     let fullUrl = uploadURL + filePathfr;
//                 //     $eyeicon.attr('href', fullUrl).removeClass('hide');

//                 // } else {

//                 //     $eyeicon.attr('href', '#').addClass('hide');
//                 // }

//                 if (data.estimation_data.inqd_file_upload != "" && data.estimation_data.inqd_file_upload != undefined) {

//                     jQuery('#EstimationCostingModal').find('#inqd_file_upload_prev').attr('href', uploadURL + data.estimation_data.inqd_file_upload);
//                     jQuery('#EstimationCostingModal').find('#inqd_file_upload_prev').text(data.estimation_data.inqd_file_upload.replace('uploads/', ''));
//                     jQuery('#EstimationCostingModal').find('#inqd_file_upload_prev').removeClass('hide');
//                     jQuery('#EstimationCostingModal').find('#inqd_file_upload_img-prev-box').removeClass('hide');
//                     // jQuery('#EstimationCostingModal').find('#inqd_file_upload_remove').addClass('i-block').removeClass('hide');
//                 } else {
//                     jQuery('#EstimationCostingModal').find('#inqd_file_upload_doc').val('');
//                     jQuery('#EstimationCostingModal').find('#inqd_file_upload_prev').attr('href', '#');
//                     jQuery('#EstimationCostingModal').find('#inqd_file_upload_prev').addClass('hide');
//                     jQuery('#EstimationCostingModal').find('#inqd_file_upload_remove').removeClass('i-block').addClass('hide');
//                     jQuery('#EstimationCostingModal').find('#inqd_file_upload_img-prev').html('');
//                     jQuery('#EstimationCostingModal').find('#inqd_file_upload_img-prev-box').addClass('hide');
//                     jQuery('#EstimationCostingModal').find('#inqd_file_upload_img-prev-box').html('');
//                 }
//                 if (data.estimation_data.fr_upload_file != "" && data.estimation_data.fr_upload_file != undefined) {

//                     jQuery('#EstimationCostingModal').find('#fr_file_upload_prev').attr('href', uploadURL + data.estimation_data.fr_upload_file);
//                     jQuery('#EstimationCostingModal').find('#fr_file_upload_prev').text(data.estimation_data.fr_upload_file.replace('uploads/', ''));
//                     jQuery('#EstimationCostingModal').find('#fr_file_upload_prev').removeClass('hide');
//                     jQuery('#EstimationCostingModal').find('#fr_file_upload_img-prev-box').removeClass('hide');
//                     // jQuery('#EstimationCostingModal').find('#inqd_file_upload_remove').addClass('i-block').removeClass('hide');
//                 } else {
//                     jQuery('#EstimationCostingModal').find('#fr_file_upload_doc').val('');
//                     jQuery('#EstimationCostingModal').find('#fr_file_upload_prev').attr('href', '#');
//                     jQuery('#EstimationCostingModal').find('#fr_file_upload_prev').addClass('hide');
//                     jQuery('#EstimationCostingModal').find('#fr_file_upload_remove').removeClass('i-block').addClass('hide');
//                     jQuery('#EstimationCostingModal').find('#fr_file_upload_img-prev').html('');
//                     jQuery('#EstimationCostingModal').find('#fr_file_upload_img-prev-box').addClass('hide');
//                     jQuery('#EstimationCostingModal').find('#fr_file_upload_img-prev-box').html('');
//                 }

//                 // if (data.estimation_data.ec_upload_file != "" && data.estimation_data.ec_upload_file != undefined) {
//                 //     jQuery('#EstimationCostingModal').find("#ec_file_upload_doc").val(data.estimation_data.ec_upload_file);
//                 //     jQuery('#EstimationCostingModal').find('#ec_file_upload_prev').attr('href', uploadURL + data.estimation_data.ec_upload_file);
//                 //     jQuery('#EstimationCostingModal').find('#ec_file_upload_prev').removeClass('hide');
//                 //     jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev-box').removeClass('hide');
//                 //     jQuery('#EstimationCostingModal').find('#ec_file_upload_remove').addClass('i-block').removeClass('hide');
//                 // } else {
//                 //     jQuery('#EstimationCostingModal').find('#ec_file_upload_doc').val('');
//                 //     jQuery('#EstimationCostingModal').find('#ec_file_upload_prev').attr('href', '#');
//                 //     jQuery('#EstimationCostingModal').find('#ec_file_upload_prev').addClass('hide');
//                 //     jQuery('#EstimationCostingModal').find('#ec_file_upload_remove').removeClass('i-block').addClass('hide');
//                 //     jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev').html('');
//                 //     jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev-box').addClass('hide');
//                 //     jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev-box').html('');
//                 // }

//                 if (data.estimation_data.ec_upload_file != "" && data.estimation_data.ec_upload_file != undefined) {
//                     let fullPath = data.estimation_data.ec_upload_file;
//                     let fileName = fullPath.split('/').pop();
//                     jQuery('#EstimationCostingModal').find("#ec_file_upload_doc").val(fullPath);
//                     jQuery('#EstimationCostingModal').find('#ec_file_upload_prev').attr('href', uploadURL + fullPath).removeClass('hide');
//                     jQuery('#EstimationCostingModal').find('#ec_file_upload_remove').addClass('i-block').removeClass('hide');
//                     jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev-box').removeClass('hide');
//                     jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev').html(fileName);
//                     let fileInput = jQuery('#EstimationCostingModal').find('#ec_file_upload');
//                     let newFile = new DataTransfer();
//                     newFile.items.add(new File([""], fileName));
//                     fileInput[0].files = newFile.files;
//                 } else {
//                     jQuery('#EstimationCostingModal').find('#ec_file_upload_doc').val('');
//                     jQuery('#EstimationCostingModal').find('#ec_file_upload_prev').attr('href', '#').addClass('hide');
//                     jQuery('#EstimationCostingModal').find('#ec_file_upload_remove').removeClass('i-block').addClass('hide');
//                     jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev').html('');
//                     jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev-box').addClass('hide');
//                     jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev-box').html('');
//                     jQuery('#EstimationCostingModal').find('#ec_file_upload').val('');
//                 }

//                 jQuery('#EstimationCostingModal').find('#pending_btn').prop('disabled', true);
//                 jQuery('#EstimationCostingModal').find('#submitbtn').prop('disabled', false);
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

jQuery('#EstimationCostingModal').on('show.bs.modal', function () {
    var formId = jQuery('#commonEstimationCostingForm').find('input[name="id"]').val();
    var hasAccess = jQuery('#commonEstimationCostingForm').find('#has_access').val();
    jQuery('#ec_prepared_by_id').val(loginUserId).trigger('change.select2');

    if (formId != "" && formId != undefined) {
        jQuery('#EstimationCostingModal').find('#pending_btn').prop('disabled', true);
        const ecDate = document.getElementById('ec_date');
        if (ecDate) {
            ecDate.focus();
            if (jQuery(ecDate).hasClass('trans-date-picker')) {
                jQuery(ecDate).datepicker('hide');
            }
        }

          jQuery('#EstimationCostingModal').find('#add_new').show();
    }else {
        jQuery('#EstimationCostingModal').find('#add_new').hide();
    }

    if (formId == "") {
        getLatestEstimationCostingNo();
        getPendingEstimation();
        jQuery('#EstimationCostingModal').find('#pending_btn').prop('disabled', false);
        initEstimationCostingFocus();
    }
    setSelect2Readonly('#commonEstimationCostingForm #inq_customer_id', true);
    setSelect2Readonly('#inqd_test_method', true);
    setSelect2Readonly('#inqd_job_description_id', true);
    setSelect2Readonly('#inqd_part_id', true);
    setSelect2Readonly('#inqd_process_at', true);
    setSelect2Readonly('#inqd_unit_id', true);


});

jQuery('#EstimationCostingModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#EstimationCostingModal');
    thisForm.find('#id').val('');
    thisForm.find('#inqd_id').val('');
    thisForm.find('#ec_fr_id').val('');
    jQuery('#EstimationCostingModal #inqd_file_upload_prev').attr('href', '#').addClass('hide');
    jQuery('#EstimationCostingModal #fr_file_upload_prev').attr('href', '#').addClass('hide');
    jQuery('#EstimationCostingModal').find('#ec_file_upload_doc').val('');
    jQuery('#EstimationCostingModal').find('#ec_file_upload_prev').attr('href', '#');
    jQuery('#EstimationCostingModal').find('#ec_file_upload_prev').addClass('hide');
    jQuery('#EstimationCostingModal').find('#ec_file_upload_remove').removeClass('i-block').addClass('hide');
    jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev').html('');
    jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev-box').addClass('hide');
    jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev-box').html('');

    document.getElementById("commonEstimationCostingForm").reset();
    jQuery('#EstimationCostingModal').find('#pending_btn').prop('disabled', false);

       jQuery('#EstimationCostingModal').find('#add_new').hide();
    // thisForm.find('input, textarea, select').each(function () {
    //     jQuery(this).val('');
    //     jQuery(this).removeAttr('readonly');
    //     jQuery(this).prop('checked', false);
    // });
    // window.location.reload();
});


// get the latest number
function getLatestEstimationCostingNo() {
    jQuery.ajax({
        url: "get-latest_estimation_costing_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#ec_number').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#ec_sequence').val(data.number);
                jQuery('#ec_date').val(currentDate);
            } else {
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            jQuery('#ec_number').removeClass('file-loader');
            console.log('Field To Get Latest Estimation Costing No.!')
        }
    });
}



function getPendingEstimation() {

    var thisModal = jQuery('#PendingEstimationModal');
    var thisForm = jQuery('#addPendingestimationForm');
    // if (formId == undefined || formId == "") {
    //     var Url = 'get-inquiry_list_for_feasibility_review';
    // } else {
    //     var Url = 'get-inquiry_list_for_feasibility_review ?id =' + formId;
    // }

    var Url = 'get-pending_inquiry_list_for_estimation_costing';


    jQuery.ajax({
        url: Url,
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                // new code
                // var usedParts = [];
                // var totalDisb = 0;
                // var found = 0;

                // // thisForm.find('#inspectionTable tbody input[name="form_indx"]').each(function (indx) {
                // //     let frmIndx = jQuery(this).val();

                // //     let jbEorkOrderId = inq_data[frmIndx].po_id;
                // //     if (jbEorkOrderId != "" && jbEorkOrderId != null) {
                // //         usedParts.push(Number(jbEorkOrderId));
                // //     }
                // // });

                // function isUsed(pjId) {
                //     if (usedParts.includes(Number(pjId))) {
                //         totalDisb++;
                //         return true;
                //     }
                //     return false;
                // }

                // let totalEntry = 0;
                // var found = 0;

                var tblHtml = ``;
                // end new code

                if (data.inq_data.length > 0 && !jQuery.isEmptyObject(data.inq_data)) {
                    found = 1;

                    for (let idx in data.inq_data) {
                        // var inUse = isUsed(data.inq_data[idx].grn_details);
                        // var in_use = data.inq_data[idx].in_use == true ? 'readonly' : '';
                        // totalEntry++;
                        tblHtml += `<tr>
                                <td><input class="radio-filter-remove" type="radio" name="inqd_id[]" class="simple-check" id="inqd_ids_${data.inq_data[idx].inqd_id}"
                                    value="${data.inq_data[idx].inqd_id}"/></td>
                                <td>${data.inq_data[idx].inq_number}</td>
                                <td>${data.inq_data[idx].inq_date}</td>
                                <td>${data.inq_data[idx].customer != null ? data.inq_data[idx].customer : ""}</td>                               
                                <td>${data.inq_data[idx].inq_ref_no_date != null ? data.inq_data[idx].inq_ref_no_date : ''}</td>
                                <td>${data.inq_data[idx].inqd_test_method != null ? data.inq_data[idx].inqd_test_method : ''}</td>
                                <td>${data.inq_data[idx].type_of_job != null ? data.inq_data[idx].type_of_job : ''}</td>
                                <td>${data.inq_data[idx].job_description != null ? data.inq_data[idx].job_description : ''}</td>
                                <td>${data.inq_data[idx].part != null ? data.inq_data[idx].part : ''}</td>
                                <td>${data.inq_data[idx].inqd_process_at != null ? data.inq_data[idx].inqd_process_at : ''}</td>
                                <!-- <td>${parseFloat(data.inq_data[idx].inqd_quantity).toFixed(3)}</td> -->
                                <td>${parseFloat(data.inq_data[idx].inqd_quantity).toFixed(2)}</td>
                                <td>${data.inq_data[idx].unit != null ? data.inq_data[idx].unit : ''}</td>
                                <td>${data.inq_data[idx].inqd_remark != null ? data.inq_data[idx].inqd_remark : ''}</td>
                                <td>${data.inq_data[idx].inq_sp_note != null ? data.inq_data[idx].inq_sp_note : ''}</td>
                                </tr>`;
                    }
                    jQuery('.toggleModalBtn').prop('disabled', false);
                } else {

                    // tblHtml += `<tr class="centeralign" id="noPendingPo">
                    //     <td colspan="15">No Pending Inquiry Available</td>
                    // </tr>`;

                    tblHtml += '';

                    jQuery('.toggleModalBtn').prop('disabled', true);
                }

                var $table = jQuery("#PendingEstimationModal").find('#pendingEstimationDataTable');
                if (jQuery.fn.DataTable.isDataTable($table)) {
                    $table.DataTable().clear().destroy();
                }
                jQuery('#pendingEstimationDataTable tbody').empty().append(tblHtml);
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

                if (jQuery('#EstimationCostingModal').hasClass('show') && !jQuery('#commonEstimationCostingForm').find('#id').val()) {
                    focusEstimationCostingEntry();
                }

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
}





//<--On Work Order Modal Show-->//
jQuery('#PendingEstimationModal').on('show.bs.modal', function (e) {
    var dt = jQuery('#pendingEstimationDataTable').DataTable();
    fixDataTableColumnsUntilAdjusted(dt);

    var usedParts = [];


    var inqd_id = jQuery('#EstimationCostingModal').find('#inqd_id').val();
    if (inqd_id != "" && inqd_id != null) {
        usedParts.push(Number(inqd_id));
    }

    function isUsed(pjId) {
        if (usedParts.includes(Number(pjId))) {
            return true;
        }
        return false;
    }

    jQuery('#pendingEstimationDataTable tbody tr').each(function (indx) {

        var checkField = jQuery(this).find('input[name="inqd_id[]"]');
        var partId = jQuery(checkField).val();
        var inUse = isUsed(partId);

        if (inUse) {
            jQuery(checkField).prop('checked', true);

        } else {
            jQuery(checkField).prop('checked', false);
        }

    });



});

jQuery('#PendingEstimationModal').on('hide.bs.modal', function (e) {
    this.dataset.customHideFocus = 'true';
    let input;
    if (jQuery('#inqd_id').val() != '') {
        input = document.getElementById('ec_estimation');
    } else {
        const $pendingBtn = jQuery('#EstimationCostingModal').find('#pending_btn');
        input = ($pendingBtn.length && !$pendingBtn.prop('disabled'))
            ? document.getElementById('pending_btn')
            : document.getElementById('ec_date');
    }
    if (input) {
        setTimeout(() => {
            input.focus();

            if (jQuery(input).hasClass('trans-date-picker')) {
                jQuery(input).datepicker('hide');
            }
        }, 100);
    }
});


$('#addPendingestimationForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#PendingEstimationModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    var chkCount = 0;
    var chkArr = [];
    var chkId = [];


    jQuery("#addPendingestimationForm").find("[id^='inqd_ids_']").each(function () {

        var thisId = jQuery(this).attr('id');
        var splt = thisId.split('inqd_ids_');
        var intId = splt[1];

        if (jQuery(this).is(':checked')) {

            chkArr.push(jQuery(this).val())

            chkId.push(intId);

            chkCount++;

        }

    });

    if (chkCount == 0) {
        toastr.error('Select Inquiry Item Pending');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PendingEstimationModal').find('#submitbtn').prop('disabled', false);
        return false;
    }
    else {

        // if (typeof formId === "undefined") {
        //     var url = "get-inquiry_part_data-feasibility_review?inqd_ids=" + chkArr.join(',');
        // } else {
        //     var url = "get-inquiry_part_data-feasibility_review?inqd_ids=" + chkArr.join(',') + "&id=" + formId;
        // }

        var url = "get-inquiry_part_data_estimation_costing?inqd_ids=" + chkArr.join(',');

        jQuery.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function (data) {

                if (data.response_code == 1) {

                    var thisForm = jQuery('#commonEstimationCostingForm');

                    if (data.inq_data && data.inq_data.length > 0) {

                        jQuery.each(data.inq_data, function (key, inq_data) {

                            thisForm.find("#inqd_id").val(inq_data.inqd_id);
                            thisForm.find("#ec_fr_id").val(inq_data.fr_id);
                            thisForm.find("#inq_number").val(inq_data.inq_number);
                            thisForm.find("#inq_date").val(inq_data.inq_number);

                            thisForm.find("#inqd_test_method").val(inq_data.inqd_test_method).trigger("change.select2");
                            thisForm.find("#inqd_type_of_job_id").val(inq_data.type_of_job);
                            thisForm.find("#inqd_job_description_id").val(inq_data.inqd_job_description_id ?? '').trigger("change.select2");
                            thisForm.find("#inqd_part_id").val(inq_data.inqd_part_id ?? '').trigger("change.select2");
                            thisForm.find("#inqd_process_at").val(inq_data.inqd_process_at).trigger("change.select2");
                            thisForm.find("#inqd_description").val(inq_data.inqd_description);
                            // thisForm.find("#inqd_quantity").val(parseFloat(inq_data.inqd_quantity).toFixed(3));
                            thisForm.find("#inqd_quantity").val(parseFloat(inq_data.inqd_quantity).toFixed(2));
                            thisForm.find("#inqd_unit_id").val(inq_data.inqd_unit_id).trigger("change.select2");
                            thisForm.find("#inq_customer_id").val(inq_data.inq_customer_id).trigger("change.select2");
                            thisForm.find("#inq_ref_no_date").val(inq_data.inq_ref_no_date);
                            thisForm.find("#inqd_remark").val(inq_data.inqd_remark);
                            thisForm.find("#inq_sp_note").val(inq_data.inq_sp_note);

                            if (inq_data.inqd_file_upload != "" && inq_data.inqd_file_upload != undefined) {
                                var fileUrl = uploadURL + inq_data.inqd_file_upload;
                                var fileName = inq_data.inqd_file_upload.split('/').pop();
                                thisForm.find('#inqd_file_upload_prev').attr('href', fileUrl).text(fileName).removeClass('hide');
                            } else {
                                thisForm.find('#inqd_file_upload_prev').attr('href', '#').text('').addClass('hide');
                            }

                            // let filePath = inq_data.inqd_file_upload;
                            // let $eye = thisForm.find('#inqd_file_upload_prev');

                            // if (filePath && filePath.trim() !== "" && filePath !== "0") {

                            //     let fullUrl = uploadURL + filePath;
                            //     $eye.attr('href', fullUrl).attr('target', '_blank').removeClass('hide');

                            // } else {
                            //     $eye.attr('href', '#').addClass('hide');
                            // }

                            if (inq_data.fr_file_upload != "" && inq_data.fr_file_upload != undefined) {
                                var fileUrl = uploadURL + inq_data.fr_file_upload;
                                var fileName = inq_data.fr_file_upload.split('/').pop();
                                thisForm.find('#fr_file_upload_prev').attr('href', fileUrl).text(fileName).removeClass('hide');
                            } else {
                                thisForm.find('#fr_file_upload_prev').attr('href', '#').text('').addClass('hide');
                            }

                            // let filePathFR = inq_data.fr_file_upload;
                            // let $eyeicon = thisForm.find('#fr_file_upload_prev');

                            // if (filePathFR && filePathFR.trim() !== "" && filePathFR !== "0") {

                            //     let fullUrl = uploadURL + filePathFR;
                            //     $eyeicon.attr('href', fullUrl).attr('target', '_blank').removeClass('hide');

                            // } else {

                            //     $eyeicon.attr('href', '#').addClass('hide');
                            // }

                        });

                    }

                    jQuery('#full-page-loader')
                        .removeClass('loader-progress-whole-page')
                        .addClass('hidden-loader');

                    jQuery('#PendingForFeasibilityReviewModal')
                        .find('#submitbtn')
                        .prop('disabled', false);

                    const input = document.getElementById('ec_estimation');
                    input?.focus();
                    // jQuery('#commonEstimationCostingForm #ec_estimation').focus();

                    jQuery("#PendingEstimationModal").modal('hide');

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
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PendingEstimationModal').find('#submitbtn').prop('disabled', false);

    }

});



$('#commonEstimationCostingForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#EstimationCostingModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    var dateValue = document.getElementById("ec_date").value.trim();
    if (!isValidDate(dateValue)) {
        toastr.error("Please Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#EstimationCostingModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#EstimationCostingModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#EstimationCostingModal').find('#commonEstimationCostingForm').find('#id').val();
    // var feasibility_review = $("#sensitivity").val();
    var formUrl = formId != undefined && formId != "" ? "update-estimation_costing" : "store-estimation_costing";
    var data = new FormData(this);
    var pending_inqd_id = jQuery('#EstimationCostingModal').find('#commonEstimationCostingForm').find('#inqd_id').val();
    if (pending_inqd_id != undefined && pending_inqd_id != "") {

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
                        jQuery('#EstimationCostingModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formId == undefined || formId == "") {
                        function nextFn() {
                            document.getElementById("commonEstimationCostingForm").reset();
                            const form = document.getElementById("commonEstimationCostingForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }

                            // jQuery('#commonEstimationCostingForm #ec_date').focus();
                            setTimeout(function () {
                                const $frDate = jQuery('#commonEstimationCostingForm #ec_date');
                                $frDate.trigger('focus');
                                setTimeout(function () {
                                    if ($frDate.hasClass('trans-date-picker')) {
                                        $frDate.datepicker('hide');
                                    }
                                }, 0);
                            }, 150);
                            jQuery("#commonEstimationCostingForm #id").val('');
                            jQuery("#commonEstimationCostingForm #inqd_id").val('');
                            jQuery("#commonEstimationCostingForm #ec_fr_id").val('');
                            jQuery("#commonEstimationCostingForm #inqd_test_method").val('').trigger("change.select2");
                            jQuery("#commonEstimationCostingForm #inqd_job_description_id").val('').trigger("change.select2");
                            jQuery("#commonEstimationCostingForm #inqd_part_id").val('').trigger("change.select2");
                            jQuery("#commonEstimationCostingForm #inqd_process_at").val('').trigger("change.select2");
                            jQuery("#commonEstimationCostingForm #inqd_unit_id").val('').trigger("change.select2");
                            jQuery('#commonEstimationCostingForm #inq_customer_id').val('').trigger("change.select2");
                            jQuery('#commonEstimationCostingForm #ec_prepared_by_id').val(loginUserId).trigger("change.select2");
                            jQuery('#commonEstimationCostingForm #ec_reviewed_by_id').val('').trigger("change.select2");

                            jQuery('#EstimationCostingModal #inqd_file_upload_prev').attr('href', '#').addClass('hide');
                            jQuery('#EstimationCostingModal #fr_file_upload_prev').attr('href', '#').addClass('hide');

                            jQuery('#EstimationCostingModal').find('#ec_file_upload_doc').val('');
                            jQuery('#EstimationCostingModal').find('#ec_file_upload_prev').attr('href', '#');
                            jQuery('#EstimationCostingModal').find('#ec_file_upload_prev').addClass('hide');
                            jQuery('#EstimationCostingModal').find('#ec_file_upload_remove').removeClass('i-block').addClass('hide');
                            jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev').html('');
                            jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev-box').addClass('hide');
                            jQuery('#EstimationCostingModal').find('#ec_file_upload_img-prev-box').html('');
                            getLatestEstimationCostingNo();
                            getPendingEstimation();


                        }
                        toastSuccess(data.response_message, nextFn);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#EstimationCostingModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastError(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#EstimationCostingModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#EstimationCostingModal').find('#submitbtn').prop('disabled', false);
                }
            },
            error: function (xhr) {
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                jQuery('#EstimationCostingModal').find('#submitbtn').prop('disabled', false);
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let errorMsg = '';
                    $.each(errors, function (key, value) {
                        errorMsg += value + '\n';
                    });
                    toastr.error(errorMsg);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#EstimationCostingModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#EstimationCostingModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    }
    else {
        toastr.error('Select Inquiry Item Pending');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#EstimationCostingModal').find('#submitbtn').prop('disabled', false);
    }

    // }
});

function validateImage(filePath) {
    var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif|\.pdf)$/i;
    if (!allowedExtensions.exec(filePath)) {
        return false;
    }
    return true;
}


jQuery('#commonEstimationCostingForm #ec_file_upload').on('change', function (e) {
    EstimationCostingfileUpload(e);
});

function EstimationCostingfileUpload(e) {
    var form_data = new FormData();
    var target = e.target;
    var id = target.id;
    var files = target.files;
    var totalfiles = files.length;
    var oldImg = jQuery('#ec_file_upload_doc').val();
    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');
    if (totalfiles > 0) {
        var notValid = 0;
        for (var index = 0; index < totalfiles; index++) {
            if (validateImage(files[index].name) == true) {
                form_data.append("docs[]", files[index]);
            } else {
                notValid = 1;
                toastr.error("Only (jpeg,jpg,png,gif,pdf) files are allowed.");
                jQuery('#' + id).val('');
                e.stopImmediatePropagation();
                return false;
            }
        }

        if (notValid == 0) {
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
                    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    if (data.response_code == 1) {
                        if (oldImg != "") {
                            removeMedia(oldImg);
                        }

                        $('#ec_file_upload_doc').val(data.files);
                        $('#ec_file_upload_prev').attr('href', data.files_url);
                        $('#ec_file_upload_prev').removeClass('hide');
                        // $('.remove-file').removeClass('hide');
                        $('.fileupload-exists').removeClass('hide');
                        $('#ec_file_upload_remove').removeClass('hide');
                        // $('.remove-file').addClass('i-block').removeClass('hide');
                        $('.remove-file').addClass('i-block').removeClass('hide');
                        console.log(data.response_message);
                    } else {
                        console.log(data.response_message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    $('#' + id).parent().parent().find('.uneditable-input').addClass('iconfa-warning-sign upload-error');
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
        if (oldImg != "") {
            let fullPath = oldImg;
            let fileName = fullPath.split('/').pop();
            let fileInput = jQuery('#ec_file_upload');
            let dataTransfer = new DataTransfer();
            dataTransfer.items.add(new File([""], fileName));
            fileInput[0].files = dataTransfer.files;
            return false;
        }

        // if (oldImg != "") {
        //     return false;
        // }

        if (oldImg != "") {
            removeMedia(oldImg);
        }

        $('#ec_file_upload_doc').val('');
        $('#ec_file_upload_prev').attr('href', '#');
        $('#ec_file_upload_prev').addClass('hide');
        $('.remove-file').addClass('hide');
        $('.fileupload-exists').addClass('hide');
        $('.remove-file').removeClass('i-block').addClass('hide');
        $('#ec_file_upload_remove').removeClass('hide');
    }
}

function removeFile(e) {
    e.stopImmediatePropagation();
    toastDetailDelete('Are You Sure, You Want <lw-c>To</lw-c> Delete ?', () => {
        var target = e.target;
        var id = target.getAttribute("data-remove");
        var fileName = jQuery('#' + id + '_doc').val();
        var oldImg = jQuery('#ec_file_upload_doc').val();
        jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');

        if (oldImg != "") {
            removeMedia(oldImg);
        }

        jQuery('#ec_file_upload_doc').val('');
        jQuery('#ec_file_upload').val('');
        jQuery('#ec_file_upload_prev').attr('href', '#');
        jQuery('#ec_file_upload_prev').addClass('hide');
        $('.remove-file').addClass('hide');
        $('.fileupload-exists').addClass('hide');
        jQuery('.remove-file').removeClass('i-block').addClass('hide');
        jQuery('.fileupload-preview').html('');
    });
}

function removeMedia(docName) {
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
            if (data.response_code == 1) {
                console.log(data.response_message);
            } else {
                console.log(data.response_message);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
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