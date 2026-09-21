var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

// Edit procedure_reference row click
jQuery('#dyntable tbody').on('click', '.edit_procedure_reference', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillProcedureReference(data["id"]);
    }
});

// Function to fetch and fill procedure_reference data
function fetchAndFillProcedureReference(id) {
    if (!id) return;
    jQuery('#ProcedureReferenceModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-procedure_reference",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.procedure_reference_data != null) {
                jQuery('#ProcedureReferenceModal').find('#procedure_reference').val(data.procedure_reference_data.procedure_reference);
                jQuery('#ProcedureReferenceModal').find('#id').val(data.procedure_reference_data.procedure_reference_id);
                jQuery('#ProcedureReferenceModal').find('#add_new').show();

                const form = document.getElementById("commonProcedureReferenceForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#ProcedureReferenceModal').find('#procedure_reference').focus();
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

// Reset button click for procedure_reference modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#ProcedureReferenceModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonProcedureReferenceForm").reset();
        const form = document.getElementById("commonProcedureReferenceForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        jQuery('#ProcedureReferenceModal').find('#procedure_reference').focus();
    } else {
        fetchAndFillProcedureReference(formId);
    }
});

$('#commonProcedureReferenceForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#ProcedureReferenceModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#ProcedureReferenceModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#ProcedureReferenceModal').find('#commonProcedureReferenceForm').find('#id').val();
    var procedure_reference = $("#procedure_reference").val();
    var formUrl = formId != undefined && formId != "" ? "update-procedure_reference" : "store-procedure_reference";
    var ProcedureReferenceUrl = formId != undefined && formId != "" ? "verify-procedure_reference?procedure_reference=" + encodeURIComponent(procedure_reference) + "&id=" + formId : "verify-procedure_reference?procedure_reference=" + encodeURIComponent(procedure_reference);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (procedure_reference != '' && procedure_reference != undefined) {
        $.ajax({
            url: ProcedureReferenceUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#ProcedureReferenceModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#ProcedureReferenceModal').find('#submitbtn').prop('disabled', false);
                                    addedProcedureReference(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonProcedureReferenceForm").reset();
                                        const form = document.getElementById("commonProcedureReferenceForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#procedure_reference').focus();
                                        if (partAfterManage != 'procedure_reference') {
                                            jQuery('#ProcedureReferenceModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#ProcedureReferenceModal').find('#submitbtn').prop('disabled', false);
                                    addedProcedureReference(true);

                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#ProcedureReferenceModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                 toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#ProcedureReferenceModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#ProcedureReferenceModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#ProcedureReferenceModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#ProcedureReferenceModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestProcedureReference(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "procedure_reference-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#procedure_reference").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#procedure_reference_list').html(data.procedureReferenceList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#procedure_reference").removeClass('file-loader');
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
    }
}

jQuery(document).on('click', '#procedure_reference_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#procedure_reference_suggesion').val(suggest);
    var hidden = jQuery('#procedure_reference_suggesion').val();
    var suggestion_list = jQuery('#procedure_reference_list').html;
    jQuery('#ProcedureReferenceModal').find('#procedure_reference').val(hidden)
    var procedure_reference = hidden;

    if (suggestion_list != '') {
        checkProcedureReferenceName(procedure_reference);
    }
    jQuery('#procedure_reference_list').html('');
});


let lastVerifiedProcedureReference = '';

jQuery(document).on('blur', '#procedure_reference', function () {
    let procedure_reference = jQuery(this).val().trim();

    if (procedure_reference === '') return;

    if (procedure_reference !== lastVerifiedProcedureReference) {
        lastVerifiedProcedureReference = procedure_reference;
        checkProcedureReferenceName(procedure_reference);
    }
});

jQuery(document).on('input', '#procedure_reference', function () {
    lastVerifiedProcedureReference = '';
});

function checkProcedureReferenceName(procedure_reference) {
    var id = jQuery('#ProcedureReferenceModal').find('#commonProcedureReferenceForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-procedure_reference?procedure_reference=" + encodeURIComponent(procedure_reference) + "&id=" + id : "verify-procedure_reference?procedure_reference=" + encodeURIComponent(procedure_reference);
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

function verifyProcedureReference() {
    var ProcedureReferenceName = jQuery('#procedure_reference').val();
    var suggestion_list = jQuery('#procedure_reference_list').html;

    if (suggestion_list != '') {
        checkProcedureReferenceName(ProcedureReferenceName);
    }
}

jQuery('#ProcedureReferenceModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#ProcedureReferenceModal');
    thisForm.find('#id').val('');
    document.getElementById("commonProcedureReferenceForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });
    jQuery('#ProcedureReferenceModal').find('#add_new').hide();
});

$('#ProcedureReferenceModal').on('shown.bs.modal', function () {
    const input = document.getElementById('procedure_reference');
    input?.focus();

    var formIdblank = jQuery('#commonProcedureReferenceForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#ProcedureReferenceModal').find('#add_new').show();
    } else {
        jQuery('#ProcedureReferenceModal').find('#add_new').hide();
    }
});

jQuery('#ProcedureReferenceModal').on('click', '#add_new', function () {
    jQuery('#ProcedureReferenceModal').find('#id').val('');
    document.getElementById("commonProcedureReferenceForm").reset();
    const form = document.getElementById("commonProcedureReferenceForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#procedure_reference').focus();
    jQuery('#ProcedureReferenceModal').find('#add_new').hide();
});

function getProcedureReference($this = null) {
    var formUrl = "get-procedure_references";
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
                if ($this != null) {
                    var stgDrpHtml = `<option value="">Select Procedure Reference</option>`;
                    for (let indx in data.procedure_references) {
                        stgDrpHtml += `<option value="${data.procedure_references[indx].procedure_reference_id}">${data.procedure_references[indx].procedure_reference}</option>`;
                    }

                    jQuery($this).each(function (e) {
                        let Id = jQuery(this).attr('id');
                        let Selected = jQuery(this).find("option:selected").val();
                        jQuery(this).empty().append(stgDrpHtml);
                        jQuery(this).val(Selected).trigger("change");
                    });
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
        }
    });
}

function addedProcedureReference($event) {
    if ($event == true) {
        getProcedureReference(".mst_procedure_reference");
    }
}
