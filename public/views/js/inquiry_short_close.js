let formId = jQuery('#commonInquiryShortCloseForm').find('input:hidden[name="id"]').val();


var inq_sc_data = [];

jQuery('#resetbtn').on('click', function () {
    inq_sc_data = [];
    jQuery('#InquiryShortCloseDataTable tbody').empty();

    var formId = jQuery('#InquiryShortCloseModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonInquiryShortCloseForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }

        setTimeout(function () {
            const $frDate = jQuery('#commonInquiryShortCloseForm #inq_sc_date');
            $frDate.trigger('focus');
            setTimeout(function () {
                if ($frDate.hasClass('trans-date-picker')) {
                    $frDate.datepicker('hide');
                }
            }, 0);
        }, 150);
        $("#inq_sc_regret_reason_id").val('').trigger('change');
        PendingInquirySC();
    }
});

jQuery('#InquiryShortCloseModal').on('shown.bs.modal', function () {
    var formId = jQuery('#commonInquiryShortCloseForm').find('input[name="id"]').val();
    var hasAccess = jQuery('#commonInquiryShortCloseForm').find('#has_access').val();
    inq_sc_data = [];
    if (formId == "") {
        PendingInquirySC();
    }

});

function PendingInquirySC() {
    jQuery('#inq_sc_date').val(currentDate);

    jQuery.ajax({
        url: "get-pending_inq_sc",
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                if (data.inq_sc_data.length > 0 && !jQuery.isEmptyObject(data.inq_sc_data)) {
                    for (let ind in data.inq_sc_data) {
                        inq_sc_data.push(data.inq_sc_data[ind]);
                    }
                    fillInqSCTable();
                }
            }
        }
    });
}

function fillInqSCTable() {
    let tblHtml = ``;
    if (inq_sc_data.length > 0) {
        for (let key in inq_sc_data) {

            var formIndx = inq_sc_data.indexOf(inq_sc_data[key]);

            var inq_number = inq_sc_data[key].inq_number ? inq_sc_data[key].inq_number : "";
            var inq_date = inq_sc_data[key].inq_date ? inq_sc_data[key].inq_date : "";

            //  var cust_code = inq_sc_data[key].customer_code ? inq_sc_data[key].customer_code : "";

            var customer = inq_sc_data[key].customer ? inq_sc_data[key].customer : "";
            var inq_ref_no_date = inq_sc_data[key].inq_ref_no_date ? inq_sc_data[key].inq_ref_no_date : "";

            var test_method = inq_sc_data[key].inqd_test_method ? inq_sc_data[key].inqd_test_method : '';

            var type_of_job = inq_sc_data[key].type_of_job
                ? inq_sc_data[key].type_of_job : '';

            var job_description = inq_sc_data[key].job_description ? inq_sc_data[key].job_description : '';

            var part_no = inq_sc_data[key].inqd_part_no ? inq_sc_data[key].inqd_part_no : '';

            var process_at = inq_sc_data[key].inqd_process_at ? inq_sc_data[key].inqd_process_at : '';

            // var inqd_quantity = inq_sc_data[key].inqd_quantity ? parseFloat(inq_sc_data[key].inqd_quantity).toFixed(3) : "";
            var inqd_quantity = inq_sc_data[key].inqd_quantity ? parseFloat(inq_sc_data[key].inqd_quantity).toFixed(2) : "";

            var inqd_remark = inq_sc_data[key].inqd_remark ? inq_sc_data[key].inqd_remark : "";
            var inq_sp_note = inq_sc_data[key].inq_sp_note ? inq_sc_data[key].inq_sp_note : "";

            tblHtml += `<tr>`;
            tblHtml += `<td>         
                <input class="checkbox-filter-remove" type="checkbox" name="inqd_id[]" class="simple-check" id="inqd_ids_${inq_sc_data[key].inqd_id}" value="${inq_sc_data[key].inqd_id}"/>
                <input type="hidden" name="form_indx" value="${formIndx}"/>
            </td>`;
            tblHtml += `<td>${inq_number}</td>`;
            tblHtml += `<td>${inq_date}</td>`;
            // tblHtml += `<td>${cust_code}</td>`;
            tblHtml += `<td>${customer}</td>`;
            tblHtml += `<td>${inq_ref_no_date}</td>`;
            tblHtml += `<td>${test_method}</td>`;
            tblHtml += `<td>${process_at}</td>`;
            tblHtml += `<td>${type_of_job}</td>`;
            tblHtml += `<td>${job_description}</td>`;
            tblHtml += `<td>${part_no}</td>`;
            tblHtml += `<td><input type="hidden" name="inq_sc_qty" value="${inqd_quantity}"> ${inqd_quantity}</td>`;
            tblHtml += `<td>${inqd_remark}</td>`;
            tblHtml += `<td>${inq_sp_note}</td>`;

            tblHtml += `</tr>`;
        }
        // jQuery('#InquiryShortCloseDataTable tbody').empty();
        // jQuery('#InquiryShortCloseDataTable tbody').append(tblHtml);

        var $table = jQuery("#InquiryShortCloseModal").find('#InquiryShortCloseDataTable');
        if (jQuery.fn.DataTable.isDataTable($table)) {
            $table.DataTable().clear().destroy();
        }

        jQuery('#InquiryShortCloseDataTable tbody').empty().append(tblHtml);

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
    }
}

$('#commonInquiryShortCloseForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#InquiryShortCloseModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();
    let form = this;
    var dateValue = document.getElementById("inq_sc_date").value.trim();
    if (!isValidDate(dateValue)) {
        toastr.error("Please Enter A Valid Date!");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#InquiryShortCloseModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (checkDate(dateValue) === 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#InquiryShortCloseModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#InquiryShortCloseModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    jQuery('#InquiryShortCloseModal').find('#submitbtn').prop('disabled', true);

    var formId = jQuery('#InquiryShortCloseModal').find('#commonInquiryShortCloseForm').find('#id').val();
    var formUrl = "store-inquiry_short_close";

    inq_sc_data = [];
    var index = 0;
    jQuery('#InquiryShortCloseDataTable tbody tr').each(function (e) {
        var Inqd_id = jQuery(this).find('input[name="inqd_id[]"]');
        if (jQuery(Inqd_id).is(':checked')) {
            Inqd_id = jQuery(Inqd_id).val();
            inq_sc_qty = jQuery(this).find('input[name="inq_sc_qty"]').val();
            inq_sc_data[index] = { 'inqd_id': Inqd_id, 'inq_sc_qty': inq_sc_qty };
            index++;
        }
    });


    if (inq_sc_data.length > 0 && !jQuery.isEmptyObject(inq_sc_data)) {
        var data = new FormData(form);
        data.append('inq_sc_data', JSON.stringify(inq_sc_data ?? []));
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
                        jQuery('#InquiryShortCloseModal').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formId == undefined || formId == "") {
                        function nextFn() {
                            document.getElementById("commonInquiryShortCloseForm").reset();
                            const form = document.getElementById("commonInquiryShortCloseForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }

                            setTimeout(function () {
                                const $frDate = jQuery('#commonInquiryShortCloseForm #inq_sc_date');
                                $frDate.trigger('focus');
                                setTimeout(function () {
                                    if ($frDate.hasClass('trans-date-picker')) {
                                        $frDate.datepicker('hide');
                                    }
                                }, 0);
                            }, 150);
                            $("#inq_sc_regret_reason_id").val('').trigger('change');
                            inq_sc_data = [];
                            jQuery('#InquiryShortCloseDataTable tbody').empty();
                            PendingInquirySC();

                        }
                        toastSuccess(data.response_message, nextFn);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#InquiryShortCloseModal').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#InquiryShortCloseModal').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#InquiryShortCloseModal').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#InquiryShortCloseModal').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#InquiryShortCloseModal').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        var formId = jQuery('#InquiryShortCloseModal').find('#commonInquiryShortCloseForm').find('#id').val();
        if (formId != undefined && formId != "") {
            jQuery('#InquiryShortCloseModal').find('#submitbtn').prop('disabled', true);
            toastr.error('You can not update Quotation.');
        }
        toastr.error('Please Add At Least One Short Close Details.');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#InquiryShortCloseModal').find('#submitbtn').prop('disabled', false);
    }
});
jQuery('#checkall-inq_data').click(function () {
    if (jQuery(this).is(':checked')) {
        jQuery("#InquiryShortCloseModal").find("[id^='inqd_ids_']:not(.in-use)").prop('checked', true).trigger('change');
    } else {
        jQuery("#InquiryShortCloseModal").find("[id^='inqd_ids_']:not(.in-use)").prop('checked', false).trigger('change');
    }
});