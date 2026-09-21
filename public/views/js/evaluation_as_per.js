var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

// Edit evaluation_as_per row click
jQuery('#dyntable tbody').on('click', '.edit_evaluation_as_per', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillEvaluationAsPer(data["id"]);
    }
});

// Function to fetch and fill evaluation_as_per data
function fetchAndFillEvaluationAsPer(id) {
    if (!id) return;
    jQuery('#EvaluationAsPerModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-evaluation_as_per",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.evaluation_as_per_data != null) {
                jQuery('#EvaluationAsPerModal').find('#evaluation_as_per').val(data.evaluation_as_per_data.evaluation_as_per);
                jQuery('#EvaluationAsPerModal').find('#id').val(data.evaluation_as_per_data.evaluation_as_per_id);
                jQuery('#EvaluationAsPerModal').find('#add_new').show();

                const form = document.getElementById("commonEvaluationAsPerForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#EvaluationAsPerModal').find('#evaluation_as_per').focus();
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

// Reset button click for evaluation_as_per modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#EvaluationAsPerModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonEvaluationAsPerForm").reset();
        const form = document.getElementById("commonEvaluationAsPerForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        jQuery('#EvaluationAsPerModal').find('#evaluation_as_per').focus();
    } else {
        fetchAndFillEvaluationAsPer(formId);
    }
});

$('#commonEvaluationAsPerForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#EvaluationAsPerModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#EvaluationAsPerModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#EvaluationAsPerModal').find('#commonEvaluationAsPerForm').find('#id').val();
    var evaluation_as_per = $("#evaluation_as_per").val();
    var formUrl = formId != undefined && formId != "" ? "update-evaluation_as_per" : "store-evaluation_as_per";
    var EvaluationAsPerUrl = formId != undefined && formId != "" ? "verify-evaluation_as_per?evaluation_as_per=" + encodeURIComponent(evaluation_as_per) + "&id=" + formId : "verify-evaluation_as_per?evaluation_as_per=" + encodeURIComponent(evaluation_as_per);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (evaluation_as_per != '' && evaluation_as_per != undefined) {
        $.ajax({
            url: EvaluationAsPerUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#EvaluationAsPerModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#EvaluationAsPerModal').find('#submitbtn').prop('disabled', false);
                                    addedEvaluationAsPer(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonEvaluationAsPerForm").reset();
                                        const form = document.getElementById("commonEvaluationAsPerForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#evaluation_as_per').focus();
                                        if (partAfterManage != 'evaluation_as_per') {
                                            jQuery('#EvaluationAsPerModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#EvaluationAsPerModal').find('#submitbtn').prop('disabled', false);
                                    addedEvaluationAsPer(true);

                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#EvaluationAsPerModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                 toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#EvaluationAsPerModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#EvaluationAsPerModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#EvaluationAsPerModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#EvaluationAsPerModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestEvaluationAsPer(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "evaluation_as_per-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#evaluation_as_per").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#evaluation_as_per_list').html(data.evaluationAsPerList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#evaluation_as_per").removeClass('file-loader');
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

jQuery(document).on('click', '#evaluation_as_per_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#evaluation_as_per_suggesion').val(suggest);
    var hidden = jQuery('#evaluation_as_per_suggesion').val();
    var suggestion_list = jQuery('#evaluation_as_per_list').html;
    jQuery('#EvaluationAsPerModal').find('#evaluation_as_per').val(hidden)
    var evaluation_as_per = hidden;

    if (suggestion_list != '') {
        checkEvaluationAsPerName(evaluation_as_per);
    }
    jQuery('#evaluation_as_per_list').html('');
});


let lastVerifiedEvaluationAsPer = '';

jQuery(document).on('blur', '#evaluation_as_per', function () {
    let evaluation_as_per = jQuery(this).val().trim();

    if (evaluation_as_per === '') return;

    if (evaluation_as_per !== lastVerifiedEvaluationAsPer) {
        lastVerifiedEvaluationAsPer = evaluation_as_per;
        checkEvaluationAsPerName(evaluation_as_per);
    }
});

jQuery(document).on('input', '#evaluation_as_per', function () {
    lastVerifiedEvaluationAsPer = '';
});

function checkEvaluationAsPerName(evaluation_as_per) {
    var id = jQuery('#EvaluationAsPerModal').find('#commonEvaluationAsPerForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-evaluation_as_per?evaluation_as_per=" + encodeURIComponent(evaluation_as_per) + "&id=" + id : "verify-evaluation_as_per?evaluation_as_per=" + encodeURIComponent(evaluation_as_per);
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

function verifyEvaluationAsPer() {
    var EvaluationAsPerName = jQuery('#evaluation_as_per').val();
    var suggestion_list = jQuery('#evaluation_as_per_list').html;

    if (suggestion_list != '') {
        checkEvaluationAsPerName(EvaluationAsPerName);
    }
}

jQuery('#EvaluationAsPerModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#EvaluationAsPerModal');
    thisForm.find('#id').val('');
    document.getElementById("commonEvaluationAsPerForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });
    jQuery('#EvaluationAsPerModal').find('#add_new').hide();
});

$('#EvaluationAsPerModal').on('shown.bs.modal', function () {
    const input = document.getElementById('evaluation_as_per');
    input?.focus();

    var formIdblank = jQuery('#commonEvaluationAsPerForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#EvaluationAsPerModal').find('#add_new').show();
    } else {
        jQuery('#EvaluationAsPerModal').find('#add_new').hide();
    }
});

jQuery('#EvaluationAsPerModal').on('click', '#add_new', function () {
    jQuery('#EvaluationAsPerModal').find('#id').val('');
    document.getElementById("commonEvaluationAsPerForm").reset();
    const form = document.getElementById("commonEvaluationAsPerForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#evaluation_as_per').focus();
    jQuery('#EvaluationAsPerModal').find('#add_new').hide();
});

function getEvaluationAsPer($this = null) {
    var formUrl = "get-evaluation_as_pers";
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
                    var stgDrpHtml = `<option value="">Select Evaluation as per</option>`;
                    for (let indx in data.evaluation_as_pers) {
                        stgDrpHtml += `<option value="${data.evaluation_as_pers[indx].evaluation_as_per_id}">${data.evaluation_as_pers[indx].evaluation_as_per}</option>`;
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

function addedEvaluationAsPer($event) {
    if ($event == true) {
        getEvaluationAsPer(".mst_evaluation_as_per");
    }
}
