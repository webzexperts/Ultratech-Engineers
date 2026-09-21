var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];
let lastVerifiedTypeOfJob = '';

// Edit type of job row click
jQuery('#dyntable tbody').on('click', '.edit_type_of_job', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        jQuery('#TypeOfJobModal').find('#id').val(data["id"]);
        fetchAndFillTypeofJob(data["id"]);
    }
});

// Function to fetch and fill type of job data
function fetchAndFillTypeofJob(id) {
    if (!id) return;
    jQuery('#TypeOfJobModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-type_of_job",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.type_of_job_data != null) {
                jQuery('#TypeOfJobModal').find('#type_of_job').val(data.type_of_job_data.type_of_job);
                jQuery('#TypeOfJobModal').find('#id').val(data.type_of_job_data.id);
                jQuery('#TypeOfJobModal').find('#add_new').show();

                const form = document.getElementById("commonTypeOfJobForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#TypeOfJobModal').find('#type_of_job').focus();
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

jQuery('#TypeOfJobModal').on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#TypeOfJobModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonTypeOfJobForm").reset(); 
        const form = document.getElementById("commonTypeOfJobForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        lastVerifiedTypeOfJob = '';
        jQuery('#TypeOfJobModal').find('#type_of_job').focus();
    } else {
        fetchAndFillTypeofJob(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_type_of_job', function () {
//     jQuery('#TypeOfJobModal').modal('show');
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-type_of_job",
//         type: 'GET',
//         data: "id=" + data["id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 if (data.type_of_job_data != null) {
//                     jQuery('#TypeOfJobModal').find('#type_of_job').val(data.type_of_job_data.type_of_job);
//                     jQuery('#TypeOfJobModal').find('#id').val(data.type_of_job_data.id);
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

$('#commonTypeOfJobForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#TypeOfJobModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#TypeOfJobModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#TypeOfJobModal').find('#commonTypeOfJobForm').find('#id').val();
    var type_of_job = $("#type_of_job").val();
    var formUrl = formId != undefined && formId != "" ? "update-type_of_job" : "store-type_of_job";
    var TypeofJobUrl = formId != undefined && formId != "" ? "verify-type_of_job?type_of_job=" + encodeURIComponent(type_of_job) + "&id=" + formId : "verify-type_of_job?type_of_job=" + encodeURIComponent(type_of_job);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (type_of_job != '' && type_of_job != undefined) {
        $.ajax({
            url: TypeofJobUrl,
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
                    jQuery('#TypeOfJobModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#TypeOfJobModal').find('#submitbtn').prop('disabled', false);
                                    addedTypeofJob(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonTypeOfJobForm").reset();
                                        const form = document.getElementById("commonTypeOfJobForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#type_of_job').focus();
                                        if (partAfterManage != 'type_of_job') {
                                            jQuery('#TypeOfJobModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#TypeOfJobModal').find('#submitbtn').prop('disabled', false);
                                    addedTypeofJob(true);
                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#TypeOfJobModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                 toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#TypeOfJobModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#TypeOfJobModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#TypeOfJobModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#TypeOfJobModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestTypeOfJob(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "type_of_job-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#type_of_job").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#type_of_job_list').html(data.type_of_jobList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#type_of_job").removeClass('file-loader');
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

jQuery(document).on('click', '#type_of_job_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#type_of_job_suggesion').val(suggest);
    var hidden = jQuery('#type_of_job_suggesion').val();
    var suggestion_list = jQuery('#type_of_job_list').html;
    jQuery('#TypeOfJobModal').find('#type_of_job').val(hidden)
    var type_of_job = hidden;

    if (suggestion_list != '') {
        checkTypeofJobName(type_of_job);
    }
    jQuery('#type_of_job_list').html('');
});

jQuery(document).on('blur', '#type_of_job', function () {
    let type_of_job = jQuery(this).val().trim();

    if (type_of_job === '') return;

    if (type_of_job !== lastVerifiedTypeOfJob) {
        lastVerifiedTypeOfJob = type_of_job;
        checkTypeofJobName(type_of_job);
    }
});

jQuery(document).on('input', '#type_of_job', function () {
    lastVerifiedTypeOfJob = '';
});

function checkTypeofJobName(type_of_job) {
    var id = jQuery('#TypeOfJobModal').find('#commonTypeOfJobForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-type_of_job?type_of_job=" + encodeURIComponent(type_of_job) + "&id=" + id : "verify-type_of_job?type_of_job=" + encodeURIComponent(type_of_job);
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

function verifyTypeOfJob() {
    var TypeOfJobName = jQuery('#type_of_job').val();
    var suggestion_list = jQuery('#type_of_job_list').html;

    if (suggestion_list != '') {
        checkTypeofJobName(TypeOfJobName);
    }
}

jQuery('#TypeOfJobModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#TypeOfJobModal');
    thisForm.find('#id').val('');
    lastVerifiedTypeOfJob = '';
    document.getElementById("commonTypeOfJobForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });
    thisForm.find('#add_new').hide();
});

$('#TypeOfJobModal').on('shown.bs.modal', function () {
    const input = document.getElementById('type_of_job');
    input?.focus();

    var formIdblank = jQuery('#TypeOfJobModal').find('#id').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#TypeOfJobModal').find('#add_new').show();
    } else {
        jQuery('#TypeOfJobModal').find('#add_new').hide();
    }
});

jQuery('#TypeOfJobModal').on('click', '#add_new', function () {
    jQuery('#TypeOfJobModal').find('#id').val('');
    document.getElementById("commonTypeOfJobForm").reset();
    const form = document.getElementById("commonTypeOfJobForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    lastVerifiedTypeOfJob = '';

    jQuery('#type_of_job').focus();
    jQuery('#TypeOfJobModal').find('#add_new').hide();
});

function getTypeofJob($this = null) {
    var formUrl = "get-type_of_job";
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
                    var stgDrpHtml = `<option value="">Select Type of Job</option>`;
                    for (let indx in data.type_of_job) {
                        stgDrpHtml += `<option value="${data.type_of_job[indx].id}">${data.type_of_job[indx].type_of_job}</option>`;
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

function addedTypeofJob($event) {
    if ($event == true) {
        getTypeofJob(".suggest_type_of_job");
    }
}