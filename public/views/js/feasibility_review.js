var formId = jQuery('#FeasibilityReviewModal').find('#commonFeasibilityReviewForm').find('#id').val();




var headerOpt = {
    'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
};

function focusFeasibilityReviewEntry() {
    const $modal = jQuery('#FeasibilityReviewModal');
    const $pendingBtn = $modal.find('#fr_date').filter(':visible').first();

    if ($pendingBtn.length && !$pendingBtn.prop('disabled')) {
        $pendingBtn[0].focus();
        $pendingBtn.css({
            'border-color': '#22b378',
            'outline-offset': '2px',
            'box-shadow': '0 0 0 0.25rem rgba(34, 179, 120, 0.25)'
        });
        $pendingBtn.off('blur.focusFeasibility').on('blur.focusFeasibility', function () {
            jQuery(this).css({
                'border-color': '',
                'outline-offset': '',
                'box-shadow': ''
            });
        });
        return true;
    }

    const frDate = document.getElementById('fr_date');
    if (frDate) {
        frDate.focus();
        if (jQuery(frDate).hasClass('trans-date-picker')) {
            jQuery(frDate).datepicker('hide');
        }
        return true;
    }

    return false;
}

function initFeasibilityReviewFocus() {
    const $modal = jQuery('#FeasibilityReviewModal');

    $modal.off('shown.bs.modal.focusFeasibility');
    $modal.on('shown.bs.modal.focusFeasibility', function () {
        const modal = jQuery(this);
        modal.attr('tabindex', '-1');

        let attempts = 0;
        const maxAttempts = 25;
        const interval = setInterval(function () {
            attempts++;
            if (focusFeasibilityReviewEntry() || attempts >= maxAttempts) {
                clearInterval(interval);
            }
        }, 200);

        modal.find('form').off('reset.focusFeasibility').on('reset.focusFeasibility', function () {
            setTimeout(focusFeasibilityReviewEntry, 100);
        });

        $modal.off('hidden.bs.modal.focusFeasibility').on('hidden.bs.modal.focusFeasibility', function () {
            clearInterval(interval);
        });
    });
}

// Edit feasibility review row click
jQuery('#dyntable tbody').on('click', '.edit-feasibility_review', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["fr_id"]) {
        fetchAndFillFeasibilityReview(data["fr_id"]);
    }
});

// Function to fetch and fill feasibility review data
function fetchAndFillFeasibilityReview(id) {
    if (!id) return;
    jQuery('#FeasibilityReviewModal').find('#id').val(id);
    jQuery('#FeasibilityReviewModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-feasibility_review",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.feasibility_data != null) {
                jQuery('#FeasibilityReviewModal').find('#fr_number').val(data.feasibility_data.fr_number != "" ? data.feasibility_data.fr_number : "");
                jQuery('#FeasibilityReviewModal').find('#fr_date').val(data.feasibility_data.fr_date != "" ? data.feasibility_data.fr_date : "");
                jQuery('#FeasibilityReviewModal').find('#fr_sequence').val(data.feasibility_data.fr_sequence != "" ? data.feasibility_data.fr_sequence : "");
                jQuery('#FeasibilityReviewModal').find('#fr_inqd_id').val(data.feasibility_data.fr_inqd_id != "" ? data.feasibility_data.fr_inqd_id : "");
                jQuery('#FeasibilityReviewModal').find('#inq_number').val(data.feasibility_data.inq_number != "" ? data.feasibility_data.inq_number : "");
                jQuery('#FeasibilityReviewModal').find('#inq_date').val(data.feasibility_data.inq_date != "" ? data.feasibility_data.inq_date : "");
                jQuery('#FeasibilityReviewModal').find('#inqd_test_method').val(data.feasibility_data.inqd_test_method != "" ? data.feasibility_data.inqd_test_method : "").trigger("change.select2");
                jQuery('#FeasibilityReviewModal').find('#inqd_type_of_job_id').val(data.feasibility_data.type_of_job != "" ? data.feasibility_data.type_of_job : "");
                jQuery('#FeasibilityReviewModal').find('#inqd_job_description_id').val(data.feasibility_data.inqd_job_description_id != "" ? data.feasibility_data.inqd_job_description_id : "").trigger("change.select2");
                // jQuery('#FeasibilityReviewModal').find('#inqd_part_id').val(data.feasibility_data.inqd_part_id != "" ? data.feasibility_data.inqd_part_id : "").trigger("change.select2");
                jQuery('#FeasibilityReviewModal').find('#inqd_part_no').val(data.feasibility_data.inqd_part_no != "" ? data.feasibility_data.inqd_part_no : "");
                jQuery('#FeasibilityReviewModal').find('#inqd_process_at').val(data.feasibility_data.inqd_process_at != "" ? data.feasibility_data.inqd_process_at : "").trigger("change.select2");
                jQuery('#FeasibilityReviewModal').find('#inqd_description').val(data.feasibility_data.inqd_description != "" ? data.feasibility_data.inqd_description : "");
                // jQuery('#FeasibilityReviewModal').find('#inqd_quantity').val(parseFloat(data.feasibility_data.inqd_quantity != null ? data.feasibility_data.inqd_quantity : "").toFixed(3));
                jQuery('#FeasibilityReviewModal').find('#inqd_quantity').val(parseFloat(data.feasibility_data.inqd_quantity != null ? data.feasibility_data.inqd_quantity : "").toFixed(2));
                jQuery('#FeasibilityReviewModal').find('#inqd_unit_id').val(data.feasibility_data.inqd_unit_id != "" ? data.feasibility_data.inqd_unit_id : "").trigger("change.select2");
                jQuery('#FeasibilityReviewModal').find('#fr_result_id').val(data.feasibility_data.fr_result_id != "" ? data.feasibility_data.fr_result_id : "").trigger("change");
                // jQuery('#FeasibilityReviewModal').find('#fr_suggest_method_id').val(data.feasibility_data.fr_suggest_method_id != "" ? data.feasibility_data.fr_suggest_method_id : "").trigger("change.select2");
                jQuery('#FeasibilityReviewModal').find('#fr_reason_id').val(data.feasibility_data.fr_reason_id != "" ? data.feasibility_data.fr_reason_id : "").trigger("change.select2");
                jQuery('#FeasibilityReviewModal').find('#fr_feasibility_review').val(data.feasibility_data.fr_feasibility_review != "" ? data.feasibility_data.fr_feasibility_review : "");
                jQuery('#FeasibilityReviewModal').find('#fr_prepared_by_id').val(data.feasibility_data.fr_prepared_by_id != "" ? data.feasibility_data.fr_prepared_by_id : "").trigger("change.select2");
                jQuery('#FeasibilityReviewModal').find('#inq_customer_id').val(data.feasibility_data.inq_customer_id != "" ? data.feasibility_data.inq_customer_id : "").trigger("change.select2");
                jQuery('#FeasibilityReviewModal').find('#inq_ref_no_date').val(data.feasibility_data.inq_ref_no_date != "" ? data.feasibility_data.inq_ref_no_date : "");
                jQuery('#FeasibilityReviewModal').find('#inqd_remark').val(data.feasibility_data.inqd_remark != "" ? data.feasibility_data.inqd_remark : "");
                jQuery('#FeasibilityReviewModal').find('#inq_sp_note').val(data.feasibility_data.inq_sp_note != "" ? data.feasibility_data.inq_sp_note : "");
                jQuery('#FeasibilityReviewModal').find('#fr_reviewed_by_id').val(data.feasibility_data.fr_reviewed_by_id != "" ? data.feasibility_data.fr_reviewed_by_id : "").trigger("change.select2");
                jQuery('#FeasibilityReviewModal').find('#id').val(data.feasibility_data.fr_id != "" ? data.feasibility_data.fr_id : "");
                jQuery('#FeasibilityReviewModal').find('#inqd_id').val(data.feasibility_data.inqd_id != "" ? data.feasibility_data.inqd_id : "");

                jQuery('#FeasibilityReviewModal').find('#fr_costing').val(data.feasibility_data.fr_costing != "" ? data.feasibility_data.fr_costing : "");
                jQuery('#FeasibilityReviewModal').find('#fr_estimation').val(data.feasibility_data.fr_estimation != "" ? data.feasibility_data.fr_estimation : "");

                // let filePath = data.feasibility_data.inqd_file_upload;
                // let $eye = jQuery('#inqd_file_upload_prev');

                // if (filePath && filePath.trim() !== "") {
                //     let fullUrl = uploadURL + filePath;
                //     $eye.attr('href', fullUrl).removeClass('hide');

                // } else {
                //     $eye.attr('href', '#').addClass('hide');
                // }

                if (data.feasibility_data.inqd_file_upload != "" && data.feasibility_data.inqd_file_upload != undefined) {

                    jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_prev').attr('href', uploadURL + data.feasibility_data.inqd_file_upload);
                    jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_prev').text(data.feasibility_data.inqd_file_upload.replace('uploads/', ''));
                    jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_prev').removeClass('hide');
                    jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_img-prev-box').removeClass('hide');
                    // jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_remove').addClass('i-block').removeClass('hide');
                } else {
                    jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_doc').val('');
                    jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_prev').attr('href', '#');
                    jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_prev').addClass('hide');
                    jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_remove').removeClass('i-block').addClass('hide');
                    jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_img-prev').html('');
                    jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_img-prev-box').addClass('hide');
                    jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_img-prev-box').html('');
                }

                // if (data.feasibility_data.fr_upload_file != "" && data.feasibility_data.fr_upload_file != undefined) {
                //     jQuery('#FeasibilityReviewModal').find("#fr_file_upload_doc").val(data.feasibility_data.fr_upload_file);
                //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_prev').attr('href', uploadURL + data.feasibility_data.fr_upload_file);
                //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_prev').removeClass('hide');
                //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_img-prev-box').removeClass('hide');
                //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_remove').addClass('i-block').removeClass('hide');
                // } else {
                //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_doc').val('');
                //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_prev').attr('href', '#');
                //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_prev').addClass('hide');
                //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_remove').removeClass('i-block').addClass('hide');
                //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_img-prev').html('');
                //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_img-prev-box').addClass('hide');
                //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_img-prev-box').html('');
                // }

                if (data.feasibility_data.fr_upload_file != "" && data.feasibility_data.fr_upload_file != undefined) {
                    let fullPath = data.feasibility_data.fr_upload_file;
                    let fileName = fullPath.split('/').pop();
                    jQuery('#FeasibilityReviewModal').find("#fr_file_upload_doc").val(fullPath);
                    jQuery('#FeasibilityReviewModal').find('#fr_file_upload_prev').attr('href', uploadURL + fullPath).removeClass('hide');
                    jQuery('#FeasibilityReviewModal').find('#fr_file_upload_remove').addClass('i-block').removeClass('hide');
                    jQuery('#FeasibilityReviewModal').find('#fr_file_upload_img-prev-box').removeClass('hide');
                    jQuery('#FeasibilityReviewModal').find('#fr_file_upload_img-prev').html(fileName);
                    let fileInput = jQuery('#FeasibilityReviewModal').find('#fr_file_upload');
                    let newFile = new DataTransfer();
                    newFile.items.add(new File([""], fileName));
                    fileInput[0].files = newFile.files;
                } else {
                    jQuery('#FeasibilityReviewModal').find('#fr_file_upload_doc').val('');
                    jQuery('#FeasibilityReviewModal').find('#fr_file_upload_prev').attr('href', '#').addClass('hide');
                    jQuery('#FeasibilityReviewModal').find('#fr_file_upload_remove').removeClass('i-block').addClass('hide');
                    jQuery('#FeasibilityReviewModal').find('#fr_file_upload_img-prev').html('');
                    jQuery('#FeasibilityReviewModal').find('#fr_file_upload_img-prev-box').addClass('hide');
                    jQuery('#FeasibilityReviewModal').find('#fr_file_upload_img-prev-box').html('');
                    jQuery('#FeasibilityReviewModal').find('#fr_file_upload').val('');
                }

                jQuery('#FeasibilityReviewModal').find('#pending_btn').prop('disabled', true);
                jQuery('#FeasibilityReviewModal').find('#submitbtn').prop('disabled', false);

                const form = document.getElementById("commonFeasibilityReviewForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#FeasibilityReviewModal').find('#add_new').show();
                if (data.feasibility_data.in_use == true) {
                    jQuery('#FeasibilityReviewModal').find('#fr_sequence').prop('readonly', true);
                    setSelect2Readonly('#fr_result_id', true);
                    const frDate = document.getElementById('fr_date');
                    if (frDate) {
                        frDate.focus();
                        if (jQuery(frDate).hasClass('trans-date-picker')) {
                            jQuery(frDate).datepicker('hide');
                        }
                    }
                } else {
                    jQuery('#FeasibilityReviewModal').find('#fr_sequence').prop('readonly', false);
                    setSelect2Readonly('#fr_result_id', false);
                    jQuery('#FeasibilityReviewModal').find('#fr_sequence').focus();
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
            console.log(JSON.parse(jqXHR.responseText));
        },
        complete: function () {
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        }
    });
}

function resetFeasibilityFields() {
    jQuery('#commonFeasibilityReviewForm').find('#inqd_id').val('');
    jQuery('#FeasibilityReviewModal').find('#inq_number').val('');
    jQuery('#FeasibilityReviewModal').find('#inq_date').val('');
    jQuery('#FeasibilityReviewModal').find('#inqd_type_of_job_id').val('');
    jQuery('#FeasibilityReviewModal').find('#inqd_description').val('');
    jQuery('#FeasibilityReviewModal').find('#inqd_quantity').val('');
    jQuery('#FeasibilityReviewModal').find('#inq_ref_no_date').val('');
    jQuery('#FeasibilityReviewModal').find('#inqd_remark').val('');
    jQuery('#FeasibilityReviewModal').find('#inq_sp_note').val('');
    jQuery('#FeasibilityReviewModal').find('#fr_feasibility_review').val('');
    jQuery('#FeasibilityReviewModal').find('#inqd_test_method').val('').trigger('change.select2');
    jQuery('#FeasibilityReviewModal').find('#inqd_job_description_id').val('').trigger('change.select2');
    jQuery('#FeasibilityReviewModal').find('#inqd_part_no').val('');
    jQuery('#FeasibilityReviewModal').find('#inqd_process_at').val('').trigger('change.select2');
    jQuery('#FeasibilityReviewModal').find('#inqd_unit_id').val('').trigger('change.select2');
    jQuery('#FeasibilityReviewModal').find('#fr_result_id').val('').trigger('change.select2');
    jQuery('#FeasibilityReviewModal').find('#fr_reason_id').val('').trigger('change.select2');
    jQuery('#FeasibilityReviewModal').find('#inq_customer_id').val('').trigger('change.select2');
    jQuery('#FeasibilityReviewModal').find('#fr_prepared_by_id').val(loginUserId).trigger('change.select2');
    jQuery('#FeasibilityReviewModal').find('#fr_reviewed_by_id').val('').trigger('change.select2');
    setSelect2Readonly('#fr_reason_id', true);
    setSelect2Readonly('#fr_result_id', false);
    setSelect2Readonly('#inq_customer_id', true);
    setSelect2Readonly('#inqd_test_method', true);
    setSelect2Readonly('#inqd_job_description_id', true);
    jQuery('#FeasibilityReviewModal').find('#inqd_part_no').prop('readonly', true);
    setSelect2Readonly('#inqd_process_at', true);
    setSelect2Readonly('#inqd_unit_id', true);
    jQuery('#FeasibilityReviewModal').find('#fr_file_upload_doc').val('');
    jQuery('#FeasibilityReviewModal').find('#fr_file_upload').val('');
    jQuery('#FeasibilityReviewModal').find('#fr_file_upload_prev').attr('href', '#').addClass('hide');
    jQuery('#FeasibilityReviewModal').find('#fr_file_upload_remove').removeClass('i-block').addClass('hide');
    jQuery('#FeasibilityReviewModal').find('#fr_file_upload_img-prev').html('');
    jQuery('#FeasibilityReviewModal').find('#fr_file_upload_img-prev-box').addClass('hide').html('');
    jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_prev').attr('href', '#').text('').addClass('hide');
    jQuery('#FeasibilityReviewModal').find('.toggleModalBtn').prop('disabled', false);
}

function prepareFeasibilityAddForm() {
    resetFeasibilityFields();
    fillPendingFeasibility();
    focusFeasibilityReviewEntry();
}

jQuery('#resetbtn').on('click', function () {
    var formId = jQuery('#FeasibilityReviewModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonFeasibilityReviewForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
        jQuery('#FeasibilityReviewModal').find('#fr_sequence').prop('readonly', false);
        prepareFeasibilityAddForm();
        getLatestFeasibilityNo();
    } else {
        fetchAndFillFeasibilityReview(formId);
    }
});

jQuery('#FeasibilityReviewModal').on('click', '#add_new', function () {
    document.getElementById("commonFeasibilityReviewForm").reset();
    const form = document.getElementById("commonFeasibilityReviewForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    jQuery('#commonFeasibilityReviewForm').find('input[name="id"]').val('');
    jQuery('#FeasibilityReviewModal').find('#fr_sequence').prop('readonly', false);
    jQuery('#FeasibilityReviewModal').find('#add_new').hide();
    const frDate = document.getElementById('fr_date');
    if (frDate) {
        frDate.focus();
        if (jQuery(frDate).hasClass('trans-date-picker')) {
            jQuery(frDate).datepicker('hide');
        }
    }
    prepareFeasibilityAddForm();
    getLatestFeasibilityNo();
});

// jQuery('#dyntable tbody').on('click', '.edit-feasibility_review', function () {

//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#FeasibilityReviewModal').find('#id').val(data["fr_id"]);
//     jQuery('#FeasibilityReviewModal').modal('show');
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-feasibility_review",
//         type: 'GET',
//         data: "id=" + data["fr_id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {

//                 jQuery('#FeasibilityReviewModal').find('#fr_number').val(data.feasibility_data.fr_number != "" ? data.feasibility_data.fr_number : "");
//                 jQuery('#FeasibilityReviewModal').find('#fr_date').val(data.feasibility_data.fr_date != "" ? data.feasibility_data.fr_date : "");
//                 jQuery('#FeasibilityReviewModal').find('#fr_sequence').val(data.feasibility_data.fr_sequence != "" ? data.feasibility_data.fr_sequence : "");
//                 jQuery('#FeasibilityReviewModal').find('#fr_inqd_id').val(data.feasibility_data.fr_inqd_id != "" ? data.feasibility_data.fr_inqd_id : "");
//                 jQuery('#FeasibilityReviewModal').find('#inq_number').val(data.feasibility_data.inq_number != "" ? data.feasibility_data.inq_number : "");
//                 jQuery('#FeasibilityReviewModal').find('#inq_date').val(data.feasibility_data.inq_date != "" ? data.feasibility_data.inq_date : "");
//                 jQuery('#FeasibilityReviewModal').find('#inqd_test_method').val(data.feasibility_data.inqd_test_method != "" ? data.feasibility_data.inqd_test_method : "").trigger("change.select2");
//                 jQuery('#FeasibilityReviewModal').find('#inqd_type_of_job_id').val(data.feasibility_data.type_of_job != "" ? data.feasibility_data.type_of_job : "");
//                 jQuery('#FeasibilityReviewModal').find('#inqd_job_description_id').val(data.feasibility_data.inqd_job_description_id != "" ? data.feasibility_data.inqd_job_description_id : "").trigger("change.select2");
//                 jQuery('#FeasibilityReviewModal').find('#inqd_part_id').val(data.feasibility_data.inqd_part_id != "" ? data.feasibility_data.inqd_part_id : "").trigger("change.select2");
//                 jQuery('#FeasibilityReviewModal').find('#inqd_process_at').val(data.feasibility_data.inqd_process_at != "" ? data.feasibility_data.inqd_process_at : "").trigger("change.select2");
//                 jQuery('#FeasibilityReviewModal').find('#inqd_description').val(data.feasibility_data.inqd_description != "" ? data.feasibility_data.inqd_description : "");
//                 jQuery('#FeasibilityReviewModal').find('#inqd_quantity').val(parseFloat(data.feasibility_data.inqd_quantity != null ? data.feasibility_data.inqd_quantity : "").toFixed(3));
//                 jQuery('#FeasibilityReviewModal').find('#inqd_unit_id').val(data.feasibility_data.inqd_unit_id != "" ? data.feasibility_data.inqd_unit_id : "").trigger("change.select2");
//                 jQuery('#FeasibilityReviewModal').find('#fr_result_id').val(data.feasibility_data.fr_result_id != "" ? data.feasibility_data.fr_result_id : "").trigger("change");
//                 // jQuery('#FeasibilityReviewModal').find('#fr_suggest_method_id').val(data.feasibility_data.fr_suggest_method_id != "" ? data.feasibility_data.fr_suggest_method_id : "").trigger("change.select2");
//                 jQuery('#FeasibilityReviewModal').find('#fr_reason_id').val(data.feasibility_data.fr_reason_id != "" ? data.feasibility_data.fr_reason_id : "").trigger("change.select2");
//                 jQuery('#FeasibilityReviewModal').find('#fr_feasibility_review').val(data.feasibility_data.fr_feasibility_review != "" ? data.feasibility_data.fr_feasibility_review : "");
//                 jQuery('#FeasibilityReviewModal').find('#fr_prepared_by_id').val(data.feasibility_data.fr_prepared_by_id != "" ? data.feasibility_data.fr_prepared_by_id : "").trigger("change.select2");
//                 jQuery('#FeasibilityReviewModal').find('#inq_customer_id').val(data.feasibility_data.inq_customer_id != "" ? data.feasibility_data.inq_customer_id : "").trigger("change.select2");
//                 jQuery('#FeasibilityReviewModal').find('#inq_ref_no_date').val(data.feasibility_data.inq_ref_no_date != "" ? data.feasibility_data.inq_ref_no_date : "");
//                 jQuery('#FeasibilityReviewModal').find('#inqd_remark').val(data.feasibility_data.inqd_remark != "" ? data.feasibility_data.inqd_remark : "");
//                 jQuery('#FeasibilityReviewModal').find('#inq_sp_note').val(data.feasibility_data.inq_sp_note != "" ? data.feasibility_data.inq_sp_note : "");
//                 jQuery('#FeasibilityReviewModal').find('#fr_reviewed_by_id').val(data.feasibility_data.fr_reviewed_by_id != "" ? data.feasibility_data.fr_reviewed_by_id : "").trigger("change.select2");
//                 jQuery('#FeasibilityReviewModal').find('#id').val(data.feasibility_data.fr_id != "" ? data.feasibility_data.fr_id : "");
//                 jQuery('#FeasibilityReviewModal').find('#inqd_id').val(data.feasibility_data.inqd_id != "" ? data.feasibility_data.inqd_id : "");
//                 if(data.feasibility_data.in_use == true){
//                     setSelect2Readonly('#fr_result_id', true);
//                 }
//                 else{
//                     setSelect2Readonly('#fr_result_id', false);
//                 }
//                 // let filePath = data.feasibility_data.inqd_file_upload;
//                 // let $eye = jQuery('#inqd_file_upload_prev');

//                 // if (filePath && filePath.trim() !== "") {
//                 //     let fullUrl = uploadURL + filePath;
//                 //     $eye.attr('href', fullUrl).removeClass('hide');

//                 // } else {
//                 //     $eye.attr('href', '#').addClass('hide');
//                 // }

//                 if (data.feasibility_data.inqd_file_upload != "" && data.feasibility_data.inqd_file_upload != undefined) {

//                     jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_prev').attr('href', uploadURL + data.feasibility_data.inqd_file_upload);
//                     jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_prev').text(data.feasibility_data.inqd_file_upload.replace('uploads/', ''));
//                     jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_prev').removeClass('hide');
//                     jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_img-prev-box').removeClass('hide');
//                     // jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_remove').addClass('i-block').removeClass('hide');
//                 } else {
//                     jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_doc').val('');
//                     jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_prev').attr('href', '#');
//                     jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_prev').addClass('hide');
//                     jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_remove').removeClass('i-block').addClass('hide');
//                     jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_img-prev').html('');
//                     jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_img-prev-box').addClass('hide');
//                     jQuery('#FeasibilityReviewModal').find('#inqd_file_upload_img-prev-box').html('');
//                 }

//                 // if (data.feasibility_data.fr_upload_file != "" && data.feasibility_data.fr_upload_file != undefined) {
//                 //     jQuery('#FeasibilityReviewModal').find("#fr_file_upload_doc").val(data.feasibility_data.fr_upload_file);
//                 //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_prev').attr('href', uploadURL + data.feasibility_data.fr_upload_file);
//                 //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_prev').removeClass('hide');
//                 //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_img-prev-box').removeClass('hide');
//                 //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_remove').addClass('i-block').removeClass('hide');
//                 // } else {
//                 //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_doc').val('');
//                 //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_prev').attr('href', '#');
//                 //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_prev').addClass('hide');
//                 //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_remove').removeClass('i-block').addClass('hide');
//                 //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_img-prev').html('');
//                 //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_img-prev-box').addClass('hide');
//                 //     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_img-prev-box').html('');
//                 // }

//                 if (data.feasibility_data.fr_upload_file != "" && data.feasibility_data.fr_upload_file != undefined) {
//                     let fullPath = data.feasibility_data.fr_upload_file;
//                     let fileName = fullPath.split('/').pop();
//                     jQuery('#FeasibilityReviewModal').find("#fr_file_upload_doc").val(fullPath);
//                     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_prev').attr('href', uploadURL + fullPath).removeClass('hide');
//                     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_remove').addClass('i-block').removeClass('hide');
//                     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_img-prev-box').removeClass('hide');
//                     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_img-prev').html(fileName);
//                     let fileInput = jQuery('#FeasibilityReviewModal').find('#fr_file_upload');
//                     let newFile = new DataTransfer();
//                     newFile.items.add(new File([""], fileName));
//                     fileInput[0].files = newFile.files;
//                 } else {
//                     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_doc').val('');
//                     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_prev').attr('href', '#').addClass('hide');
//                     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_remove').removeClass('i-block').addClass('hide');
//                     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_img-prev').html('');
//                     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_img-prev-box').addClass('hide');
//                     jQuery('#FeasibilityReviewModal').find('#fr_file_upload_img-prev-box').html('');
//                     jQuery('#FeasibilityReviewModal').find('#fr_file_upload').val('');
//                 }

//                 jQuery('#FeasibilityReviewModal').find('#pending_btn').prop('disabled', true);
//                 jQuery('#FeasibilityReviewModal').find('#submitbtn').prop('disabled', false);
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

jQuery('#FeasibilityReviewModal').on('show.bs.modal', function () {
    jQuery('#FeasibilityReviewModal').find('#fr_sequence').prop('readonly', false);
    var formId = jQuery('#commonFeasibilityReviewForm').find('input[name="id"]').val();
    jQuery('#fr_prepared_by_id').val(loginUserId).trigger('change.select2');

    if (formId != "" && formId != undefined) {
        jQuery('#FeasibilityReviewModal').find('#pending_btn').prop('disabled', true);
        const frDate = document.getElementById('fr_date');
        if (frDate) {
            frDate.focus();
            if (jQuery(frDate).hasClass('trans-date-picker')) {
                jQuery(frDate).datepicker('hide');
            }
        }
    }

    if (formId == "") {
        fillPendingFeasibility();
        getLatestFeasibilityNo();
        setSelect2Readonly('#fr_reason_id', true);
        setSelect2Readonly('#fr_result_id', false);
        jQuery('#FeasibilityReviewModal').find('#pending_btn').prop('disabled', false);
        initFeasibilityReviewFocus();
    }

    setSelect2Readonly('#inq_customer_id', true);
    setSelect2Readonly('#inqd_test_method', true);
    setSelect2Readonly('#inqd_job_description_id', true);
    jQuery('#FeasibilityReviewModal').find('#inqd_part_no').prop('readonly', true);
    setSelect2Readonly('#inqd_process_at', true);
    setSelect2Readonly('#inqd_unit_id', true);

    var addNewFormId = jQuery('#commonFeasibilityReviewForm').find('input[name="id"]').val();
    if (addNewFormId && addNewFormId !== "") {
        jQuery('#FeasibilityReviewModal').find('#add_new').show();
    } else {
        jQuery('#FeasibilityReviewModal').find('#add_new').hide();
    }
});

jQuery('#FeasibilityReviewModal').on('hide.bs.modal', function (e) {
    jQuery('#FeasibilityReviewModal').find('#submitbtn').prop('disabled', false);
    jQuery('#FeasibilityReviewModal').find('#fr_sequence').prop('readonly', false);
    jQuery('#FeasibilityReviewModal').find('#id').val('');
    jQuery('#FeasibilityReviewModal').find('#add_new').hide();
    document.getElementById("commonFeasibilityReviewForm").reset();
    resetFeasibilityFields();
});


// get the latest number
function getLatestFeasibilityNo() {
    jQuery.ajax({
        url: "get-latest_feasibility_number",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                jQuery('#fr_number').val(data.latest_no).prop({ tabindex: -1, readonly: true });
                jQuery('#fr_sequence').val(data.number);
                jQuery('#fr_date').val(currentDate);
            } else {
                console.log(data.response_message)
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            jQuery('#fr_number').removeClass('file-loader');
            console.log('Field To Get Latest Feasibility Review No.!')
        }
    });
}

jQuery('#FeasibilityReviewModal').on('change', '#fr_result_id', function () {
    if (this.value == 'Feasible') {
        setSelect2Readonly('#fr_reason_id', true);
        // setSelect2Readonly('#fr_suggest_method_id', true);
        jQuery('#fr_reason_id').val('').trigger("change.select2").addClass('skip-tab');
        // jQuery('#fr_suggest_method_id').val('').trigger("change.select2").addClass('skip-tab');
        setSelect2Required('#fr_reason_id', false);
        // setSelect2Required('#fr_suggest_method_id', false);

    } else if (this.value == "Not Feasible") {
        setSelect2Readonly('#fr_reason_id', false);
        // setSelect2Readonly('#fr_suggest_method_id', true);
        jQuery('#fr_reason_id').val('').trigger("change.select2").removeClass('skip-tab').attr('tabindex', '0');
        // jQuery('#fr_suggest_method_id').val('').trigger("change.select2").addClass('skip-tab');
        setSelect2Required('#fr_reason_id', true);
        // setSelect2Required('#fr_suggest_method_id', false);
    }
    else {
        setSelect2Readonly('#fr_reason_id', true);
        // setSelect2Readonly('#fr_suggest_method_id', false);
        jQuery('#fr_reason_id').val('').trigger("change.select2").addClass('skip-tab');
        // jQuery('#fr_suggest_method_id').val('').trigger("change.select2").removeClass('skip-tab').attr('tabindex', '0');
        setSelect2Required('#fr_reason_id', false);
        // setSelect2Required('#fr_suggest_method_id', true);

    }
});

function fillPendingFeasibility() {

    var thisModal = jQuery('#PendingForFeasibilityReviewModal');
    var thisForm = jQuery('#commonFeasibilityReviewForm');
    // console.log(formId,"ss");
    if (formId == undefined || formId == "") {
        var Url = 'get-inquiry_list_for_feasibility_review';
    } else {
        var Url = 'get-inquiry_list_for_feasibility_review ?id =' + formId;
    }

    jQuery.ajax({
        url: Url,
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1 && data.inq_data.length > 0) {
                // new code
                var usedParts = [];
                var totalDisb = 0;
                var found = 0;

                // thisForm.find('#inspectionTable tbody input[name="form_indx"]').each(function (indx) {
                //     let frmIndx = jQuery(this).val();

                //     let jbEorkOrderId = inq_data[frmIndx].po_id;
                //     if (jbEorkOrderId != "" && jbEorkOrderId != null) {
                //         usedParts.push(Number(jbEorkOrderId));
                //     }
                // });

                // function isUsed(pjId) {
                //     if (usedParts.includes(Number(pjId))) {
                //         totalDisb++;
                //         return true;
                //     }
                //     return false;
                // }

                // let totalEntry = 0;
                var tblHtml = ``;
                // var found = 0;

                // end new code

                if (data.inq_data.length > 0 && !jQuery.isEmptyObject(data.inq_data)) {
                    // found = 1;

                    for (let idx in data.inq_data) {
                        // var inUse = isUsed(data.inq_data[idx].grn_details);
                        // var in_use = data.inq_data[idx].in_use == true ? 'readonly' : '';
                        // totalEntry++;
                        tblHtml += `<tr>
                                <td><input class="radio-filter-remove" type="radio" name="inqd_id[]" class="simple-check" id="inqd_ids_${data.inq_data[idx].inqd_id}"
                                    value="${data.inq_data[idx].inqd_id}" /></td>
                                <td>${data.inq_data[idx].inq_number}</td>
                                <td>${data.inq_data[idx].inq_date}</td>
                                <td>${data.inq_data[idx].customer != null ? data.inq_data[idx].customer : ""}</td>                              
                                <td>${data.inq_data[idx].inq_ref_no_date != null ? data.inq_data[idx].inq_ref_no_date : ''}</td>
                                <td>${data.inq_data[idx].inqd_test_method != null ? data.inq_data[idx].inqd_test_method : ''}</td>
                                <td>${data.inq_data[idx].type_of_job != null ? data.inq_data[idx].type_of_job : ''}</td>
                                <td>${data.inq_data[idx].job_description != null ? data.inq_data[idx].job_description : ''}</td>
                                <td>${data.inq_data[idx].inqd_part_no != null ? data.inq_data[idx].inqd_part_no : ''}</td>
                                <td>${data.inq_data[idx].inqd_process_at != null ? data.inq_data[idx].inqd_process_at : ''}</td>
                                <!-- <td>${data.inq_data[idx].inqd_quantity != null ? parseFloat(data.inq_data[idx].inqd_quantity).toFixed(3) : ''}</td> -->
                                <td>${data.inq_data[idx].inqd_quantity != null ? parseFloat(data.inq_data[idx].inqd_quantity).toFixed(2) : ''}</td>
                                <td>${data.inq_data[idx].unit != null ? data.inq_data[idx].unit : ''}</td>
                                <td>${data.inq_data[idx].inq_sp_note != null ? data.inq_data[idx].inq_sp_note : ''}</td>
                                </tr>`;
                    }

                } else {

                    tblHtml += `<tr class="centeralign" id="noPendingPo">
                    <td colspan="14">No Pending Inquiry Available</td>
                </tr>`;

                }

                var $table = jQuery("#PendingForFeasibilityReviewModal").find('#PendingForFeasibilityReviewTable');
                if (jQuery.fn.DataTable.isDataTable($table)) {
                    $table.DataTable().clear().destroy();
                }
                jQuery('#PendingForFeasibilityReviewTable tbody').empty().append(tblHtml);
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

            if (jQuery('#FeasibilityReviewModal').hasClass('show') && !jQuery('#commonFeasibilityReviewForm').find('#id').val()) {
                focusFeasibilityReviewEntry();
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
    jQuery('#PendingForFeasibilityReviewModal').on('show.bs.modal', function (e) {
        var dt = jQuery('#PendingForFeasibilityReviewTable').DataTable();
        fixDataTableColumnsUntilAdjusted(dt);

        var usedParts = [];


        var inqd_id = jQuery('#FeasibilityReviewModal').find('#inqd_id').val();
        if (inqd_id != "" && inqd_id != null) {
            usedParts.push(Number(inqd_id));
        }

        function isUsed(pjId) {
            if (usedParts.includes(Number(pjId))) {
                return true;
            }
            return false;
        }

        jQuery('#PendingForFeasibilityReviewTable tbody tr').each(function (indx) {

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

    jQuery('#PendingForFeasibilityReviewModal').on('hide.bs.modal', function (e) {
        this.dataset.customHideFocus = 'true';
        let input;
        if (jQuery('#inqd_id').val() != '') {
            input = document.getElementById('fr_result_id');
        } else {
            const $pendingBtn = jQuery('#FeasibilityReviewModal').find('#pending_btn');
            input = ($pendingBtn.length && !$pendingBtn.prop('disabled'))
                ? document.getElementById('fr_date')
                : document.getElementById('pending_btn');
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


    $('#feasibilityReviewForm').on('submit', function (e) {
        jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
        jQuery('#PendingForFeasibilityReviewModal').find('#submitbtn').prop('disabled', true);
        e.preventDefault();

        var chkCount = 0;
        var chkArr = [];
        var chkId = [];

        var formId = jQuery('#PendingForFeasibilityReviewModal').find('#feasibilityReviewForm').find('#id').val();
        jQuery("#feasibilityReviewForm").find("[id^='inqd_ids_']").each(function () {

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
            toastr.error('Select Inquiry From Pending');
            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            jQuery('#PendingForFeasibilityReviewModal').find('#submitbtn').prop('disabled', false);
            return false;
        }
        else {

            if (typeof formId === "undefined") {
                var url = "get-inquiry_part_data_feasibility_review?inqd_ids=" + chkArr.join(',');
            } else {
                var url = "get-inquiry_part_data_feasibility_review?inqd_ids=" + chkArr.join(',') + "&id=" + formId;
            }

            jQuery.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function (data) {

                    if (data.response_code == 1) {

                        var thisForm = jQuery('#commonFeasibilityReviewForm');
                        if (data.inq_data && data.inq_data.length > 0) {

                            jQuery.each(data.inq_data, function (key, inq_data) {

                                thisForm.find("#inqd_id").val(inq_data.inqd_id);
                                thisForm.find("#inq_number").val(inq_data.inq_number);
                                thisForm.find("#inq_date").val(inq_data.inq_date);
                                thisForm.find("#inqd_test_method").val(inq_data.inqd_test_method != "" ? inq_data.inqd_test_method : "").trigger("change.select2");
                                thisForm.find("#inqd_type_of_job_id").val(inq_data.type_of_job != "" ? inq_data.type_of_job : "");
                                thisForm.find("#inqd_job_description_id").val(inq_data.inqd_job_description_id != "" ? inq_data.inqd_job_description_id : "").trigger("change.select2");
                                thisForm.find("#inqd_part_no").val(inq_data.inqd_part_no ?? inq_data.part_no ?? inq_data.part ?? "");
                                thisForm.find("#inqd_process_at").val(inq_data.inqd_process_at != "" ? inq_data.inqd_process_at : "").trigger("change.select2");
                                thisForm.find("#inqd_description").val(inq_data.inqd_description != "" ? inq_data.inqd_description : '');
                                // thisForm.find("#inqd_quantity").val(inq_data.inqd_quantity != null ? parseFloat(inq_data.inqd_quantity).toFixed(3) : '');
                                thisForm.find("#inqd_quantity").val(inq_data.inqd_quantity != null ? parseFloat(inq_data.inqd_quantity).toFixed(2) : '');
                                thisForm.find("#inqd_unit_id").val(inq_data.inqd_unit_id != "" ? inq_data.inqd_unit_id : "").trigger("change.select2");
                                thisForm.find("#inq_customer_id").val(inq_data.inq_customer_id != "" ? inq_data.inq_customer_id : "").trigger("change.select2");
                                thisForm.find("#inq_ref_no_date").val(inq_data.inq_ref_no_date != "" ? inq_data.inq_ref_no_date : '');
                                thisForm.find("#inqd_remark").val(inq_data.inqd_remark != "" ? inq_data.inqd_remark : '');
                                thisForm.find("#inq_sp_note").val(inq_data.inq_sp_note != "" ? inq_data.inq_sp_note : '');

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
                            });
                        }

                        jQuery('#full-page-loader')
                            .removeClass('loader-progress-whole-page')
                            .addClass('hidden-loader');

                        jQuery('#PendingForFeasibilityReviewModal')
                            .find('#submitbtn')
                            .prop('disabled', false);

                        jQuery("#PendingForFeasibilityReviewModal").modal('hide');

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



$('#commonFeasibilityReviewForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#FeasibilityReviewModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    var dateValue = document.getElementById("fr_date").value.trim();
    if (!isValidDate(dateValue)) {
        toastr.error("Please Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#FeasibilityReviewModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#FeasibilityReviewModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#FeasibilityReviewModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#FeasibilityReviewModal').find('#commonFeasibilityReviewForm').find('#id').val();
    // var feasibility_review = $("#sensitivity").val();
    var formUrl = formId != undefined && formId != "" ? "update-feasibility_review" : "store-feasibility_review";
    var data = new FormData(this);
    var pending_inqd_id = jQuery('#FeasibilityReviewModal').find('#commonFeasibilityReviewForm').find('#inqd_id').val();
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
                        jQuery('#FeasibilityReviewModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formId == undefined || formId == "") {
                        function nextFn() {
                            document.getElementById("commonFeasibilityReviewForm").reset();
                            const form = document.getElementById("commonFeasibilityReviewForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            const frDate = document.getElementById('fr_date');
                            if (frDate) {
                                frDate.focus();
                                if (jQuery(frDate).hasClass('trans-date-picker')) {
                                    jQuery(frDate).datepicker('hide');
                                }
                            }
                            jQuery('#commonFeasibilityReviewForm').find('input[name="id"]').val('');
                            jQuery('#FeasibilityReviewModal').find('#add_new').hide();
                            prepareFeasibilityAddForm();
                            getLatestFeasibilityNo();
                        }
                        toastSuccess(data.response_message, nextFn);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#FeasibilityReviewModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastError(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#FeasibilityReviewModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#FeasibilityReviewModal').find('#submitbtn').prop('disabled', false);
                }
            },
            error: function (xhr) {
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                jQuery('#FeasibilityReviewModal').find('#submitbtn').prop('disabled', false);
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let errorMsg = '';
                    $.each(errors, function (key, value) {
                        errorMsg += value + '\n';
                    });
                    toastr.error(errorMsg);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#FeasibilityReviewModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#FeasibilityReviewModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    }
    else {
        toastr.error('Select Inquiry From Pending');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#FeasibilityReviewModal').find('#submitbtn').prop('disabled', false);
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

jQuery('#commonFeasibilityReviewForm #fr_file_upload').on('change', function (e) {
    FeasibilityReviewfileUpload(e);
});

function FeasibilityReviewfileUpload(e) {
    var form_data = new FormData();
    var target = e.target;
    var id = target.id;
    var files = target.files;
    var totalfiles = files.length;
    var oldImg = jQuery('#fr_file_upload_doc').val();
    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');
    if (totalfiles > 0) {
        var notValid = 0;
        for (var index = 0; index < totalfiles; index++) {
            if (validateImage(files[index].name) == true) {
                form_data.append("docs[]", files[index]);
            } else {
                notValid = 1;
                toastr.error("Only (jpeg,jpg,png,gif,pdf) files are allowed.");
                jQuery('#fr_file_upload_doc').val('');
                jQuery('#' + id).val('');
                jQuery('#fr_file_upload_prev').attr('href', '#').addClass('hide');
                jQuery('#fr_file_upload_remove').removeClass('i-block').addClass('hide');
                e.stopImmediatePropagation();
                return false;
            }
        }

        if (notValid == 0) {
            jQuery('#FeasibilityReviewModal').find('#submitbtn').prop('disabled', true);
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
                    jQuery('#FeasibilityReviewModal').find('#submitbtn').prop('disabled', false);
                    jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');
                    if (data.response_code == 1) {
                        if (oldImg != "" && oldImg.includes('temp_media/')) {
                            removeMedia(oldImg);
                        }

                        $('#fr_file_upload_doc').val(data.files);
                        $('#fr_file_upload_prev').attr('href', data.files_url);
                        $('#fr_file_upload_prev').removeClass('hide');
                        // $('.remove-file').removeClass('hide');
                        $('.fileupload-exists').removeClass('hide');
                        $('#fr_file_upload_remove').removeClass('hide');
                        // $('.remove-file').addClass('i-block').removeClass('hide');
                        $('.remove-file').addClass('i-block').removeClass('hide');
                        console.log(data.response_message);
                    } else {
                        console.log(data.response_message);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#FeasibilityReviewModal').find('#submitbtn').prop('disabled', false);
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
            let fileInput = jQuery('#fr_file_upload');
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

        $('#fr_file_upload_doc').val('');
        $('#fr_file_upload_prev').attr('href', '#');
        $('#fr_file_upload_prev').addClass('hide');
        $('.remove-file').addClass('hide');
        $('.fileupload-exists').addClass('hide');
        $('.remove-file').removeClass('i-block').addClass('hide');
        $('#fr_file_upload_remove').removeClass('hide');
    }
}

function removeFile(e) {
    e.stopImmediatePropagation();
    toastDetailDelete('Are You Sure, You Want <lw-c>To</lw-c> Delete ?', () => {
        var target = e.target;
        var id = target.getAttribute("data-remove");
        var fileName = jQuery('#' + id + '_doc').val();
        var oldImg = jQuery('#fr_file_upload_doc').val();
        jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');

        if (oldImg != "") {
            removeMedia(oldImg);
        }

        jQuery('#fr_file_upload_doc').val('');
        jQuery('#fr_file_upload').val('');
        jQuery('#fr_file_upload_prev').attr('href', '#');
        jQuery('#fr_file_upload_prev').addClass('hide');
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






