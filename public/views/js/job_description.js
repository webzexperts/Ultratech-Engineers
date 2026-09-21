var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];
let lastVerifiedJobDescription = '';

// Edit job description row click
jQuery('#dyntable tbody').on('click', '.edit_job_description', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        jQuery('#JobDescriptionModal').find('#id').val(data["id"]);
        fetchAndFillJobDescription(data["id"]);
    }
});

// Function to fetch and fill job description data
function fetchAndFillJobDescription(id) {
    if (!id) return;
    jQuery('#JobDescriptionModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-job_description",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.jobdescription != null) {
                jQuery('#JobDescriptionModal').find('#job_description').val(data.jobdescription.job_description);
                jQuery('#JobDescriptionModal').find('#id').val(data.jobdescription.id);
                jQuery('#JobDescriptionModal').find('#add_new').show();

                const form = document.getElementById("commonJobDescriptionForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#JobDescriptionModal').find('#job_description').focus();
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

jQuery('#JobDescriptionModal').on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#JobDescriptionModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonJobDescriptionForm").reset(); 
        const form = document.getElementById("commonJobDescriptionForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        lastVerifiedJobDescription = '';
        jQuery('#JobDescriptionModal').find('#job_description').focus();
    } else {
        fetchAndFillJobDescription(formId);
    }
});

// jQuery('#dyntable tbody').on('click', '.edit_job_description', function () {
//     jQuery('#JobDescriptionModal').modal('show');
//     var data = table.row(jQuery(this).parents('tr')).data();
//     jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
//     jQuery.ajax({
//         url: "edit-job_description",
//         type: 'GET',
//         data: "id=" + data["id"],
//         headers: headerOpt,
//         dataType: 'json',
//         processData: false,
//         success: function (data) {
//             if (data.response_code == 1) {
//                 if (data.jobdescription != null) {
//                     jQuery('#JobDescriptionModal').find('#job_description').val(data.jobdescription.job_description);
//                     jQuery('#JobDescriptionModal').find('#id').val(data.jobdescription.id);
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

$('#commonJobDescriptionForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#JobDescriptionModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#JobDescriptionModal').find('#submitbtn').prop('disabled', false);
        return;
    }
    var formId = jQuery('#JobDescriptionModal').find('#commonJobDescriptionForm').find('#id').val();
    var Job = jQuery('#JobDescriptionModal').find("#job_description").val();
    var formUrl = formId != undefined && formId != "" ? "update-job_description" : "store-job_description";
    var JobDescUrl = formId != undefined && formId != "" ? "verify-job_description?job_description=" + encodeURIComponent(Job) + "&id=" + formId : "verify-job_description?job_description=" + encodeURIComponent(Job);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    if (Job != '' && Job != undefined) {
        $.ajax({
            url: JobDescUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#JobDescriptionModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#JobDescriptionModal').find('#submitbtn').prop('disabled', false);
                                    addedJobDescription(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonJobDescriptionForm").reset();
                                        const form = document.getElementById("commonJobDescriptionForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#job_description').focus();
                                        if (partAfterManage != 'job_description') {
                                            jQuery('#JobDescriptionModal').modal('hide');
                                        }
                                    }   
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#JobDescriptionModal').find('#submitbtn').prop('disabled', false);
                                    addedJobDescription(true);
                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#JobDescriptionModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                 toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#JobDescriptionModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#JobDescriptionModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#JobDescriptionModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#JobDescriptionModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestJobDescription(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "job_description-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#job_description").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#job_description_list').html(data.jobDescriptionList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#job_description").removeClass('file-loader');
                var errMessage = JSON.parse(jqXHR.responseText);
                if (errMessage.errors) {
                    jobDescriptionValidator.showErrors(errMessage.errors);
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

jQuery(document).on('click', '#job_description_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#job_description_suggesion').val(suggest);
    var hidden = jQuery('#job_description_suggesion').val();
    var suggestion_list = jQuery('#job_description_list').html;
    jQuery('#JobDescriptionModal').find('#job_description').val(hidden)
    var job_description = hidden;

    if (suggestion_list != '') {
        checkjobDescription(job_description);
    }
    jQuery('#job_description_list').html('');
});

jQuery(document).on('blur', '#job_description', function () {
    let job_description = jQuery(this).val().trim();

    if (job_description === '') return;

    if (job_description !== lastVerifiedJobDescription) {
        lastVerifiedJobDescription = job_description;
        checkjobDescription(job_description);
    }
});

jQuery(document).on('input', '#job_description', function () {
    lastVerifiedJobDescription = '';
});

function checkjobDescription(job_description) {
    var id = jQuery('#JobDescriptionModal').find('#commonJobDescriptionForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-job_description?job_description=" + encodeURIComponent(job_description) + "&id=" + id : "verify-job_description?job_description=" + encodeURIComponent(job_description);
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

function verifyJobDescription() {
    var JobDesc = jQuery('#job_description').val();
    var suggestion_list = jQuery('#job_description_list').html;

    if (suggestion_list != '') {
        checkjobDescription(JobDesc);
    }
}

jQuery('#JobDescriptionModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#JobDescriptionModal');
    thisForm.find('#id').val('');
    lastVerifiedJobDescription = '';
    document.getElementById("commonJobDescriptionForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });
    thisForm.find('#add_new').hide();

    if (partAfterManage == 'job_description') {
        // window.location.reload();
    }
});

$('#JobDescriptionModal').on('shown.bs.modal', function () {
    const input = document.getElementById('job_description');
    input?.focus();

    var formIdblank = jQuery('#JobDescriptionModal').find('#id').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#JobDescriptionModal').find('#add_new').show();
    } else {
        jQuery('#JobDescriptionModal').find('#add_new').hide();
    }
});

jQuery('#JobDescriptionModal').on('click', '#add_new', function () {
    jQuery('#JobDescriptionModal').find('#id').val('');
    document.getElementById("commonJobDescriptionForm").reset();
    const form = document.getElementById("commonJobDescriptionForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    lastVerifiedJobDescription = '';

    jQuery('#job_description').focus();
    jQuery('#JobDescriptionModal').find('#add_new').hide();
});

function getJobDescription($this = null) {
    var formUrl = "get-job_description";
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
                    var stgDrpHtml = `<option value="">Select Job Description</option>`;
                    for (let indx in data.jobdescriptions) {
                        let jd = data.jobdescriptions[indx];
                        let partsJson = JSON.stringify(jd.parts || []);
                        stgDrpHtml += `<option value="${jd.id}" data-parts='${partsJson}'>${jd.job_description}</option>`;
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

function addedJobDescription($event) {
    if ($event == true) {
        getJobDescription(".suggest_job_description");
    }
}