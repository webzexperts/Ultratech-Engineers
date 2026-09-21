var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];

// Edit area_of_coverage row click
jQuery('#dyntable tbody').on('click', '.edit_area_of_coverage', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["id"]) {
        fetchAndFillAreaOfCoverage(data["id"]);
    }
});

// Function to fetch and fill area_of_coverage data
function fetchAndFillAreaOfCoverage(id) {
    if (!id) return;
    jQuery('#AreaOfCoverageModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-area_of_coverage",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.area_of_coverage_data != null) {
                jQuery('#AreaOfCoverageModal').find('#area_of_coverage').val(data.area_of_coverage_data.area_of_coverage);
                jQuery('#AreaOfCoverageModal').find('#id').val(data.area_of_coverage_data.area_of_coverage_id);
                jQuery('#AreaOfCoverageModal').find('#add_new').show();

                const form = document.getElementById("commonAreaOfCoverageForm");
                if (form) form.classList.remove('was-validated');
                jQuery('#AreaOfCoverageModal').find('#area_of_coverage').focus();
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

// Reset button click for area_of_coverage modal
jQuery(document).on('click', '#resetbtn', function (e) {
    e.preventDefault();
    var formId = jQuery('#AreaOfCoverageModal').find('#id').val();
    if (!formId) {
        document.getElementById("commonAreaOfCoverageForm").reset();
        const form = document.getElementById("commonAreaOfCoverageForm");
        if (form) {
            form.classList.remove('was-validated');
        }
        jQuery('#AreaOfCoverageModal').find('#area_of_coverage').focus();
    } else {
        fetchAndFillAreaOfCoverage(formId);
    }
});

$('#commonAreaOfCoverageForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#AreaOfCoverageModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#AreaOfCoverageModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#AreaOfCoverageModal').find('#commonAreaOfCoverageForm').find('#id').val();
    var area_of_coverage = $("#area_of_coverage").val();
    var formUrl = formId != undefined && formId != "" ? "update-area_of_coverage" : "store-area_of_coverage";
    var AreaOfCoverageUrl = formId != undefined && formId != "" ? "verify-area_of_coverage?area_of_coverage=" + encodeURIComponent(area_of_coverage) + "&id=" + formId : "verify-area_of_coverage?area_of_coverage=" + encodeURIComponent(area_of_coverage);
    let formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    if (area_of_coverage != '' && area_of_coverage != undefined) {
        $.ajax({
            url: AreaOfCoverageUrl,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#AreaOfCoverageModal').find('#submitbtn').prop('disabled', false);
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
                                    jQuery('#AreaOfCoverageModal').find('#submitbtn').prop('disabled', false);
                                    addedAreaOfCoverage(true);
                                }
                                else if (formId == undefined || formId == "") {
                                    function nextFn() {
                                        document.getElementById("commonAreaOfCoverageForm").reset();
                                        const form = document.getElementById("commonAreaOfCoverageForm");
                                        if (form) {
                                            form.classList.remove('was-validated');
                                        }

                                        jQuery('#area_of_coverage').focus();
                                        if (partAfterManage != 'area_of_coverage') {
                                            jQuery('#AreaOfCoverageModal').modal('hide');
                                        }
                                    }
                                    toastSuccess(data.response_message, nextFn);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#AreaOfCoverageModal').find('#submitbtn').prop('disabled', false);
                                    addedAreaOfCoverage(true);

                                }
                                else {
                                    toastError(data.response_message);
                                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                    jQuery('#AreaOfCoverageModal').find('#submitbtn').prop('disabled', false);
                                }
                            } else {
                                 toastr.error(data.response_message);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#AreaOfCoverageModal').find('#submitbtn').prop('disabled', false);
                            }
                        },
                        error: function (xhr) {
                            jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                            jQuery('#AreaOfCoverageModal').find('#submitbtn').prop('disabled', false);
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                let errors = xhr.responseJSON.errors;
                                let errorMsg = '';
                                $.each(errors, function (key, value) {
                                    errorMsg += value + '\n';
                                });
                                 toastr.error(errorMsg);
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#AreaOfCoverageModal').find('#submitbtn').prop('disabled', false);
                            } else {
                                 toastr.error('Something went wrong. Please try again.');
                                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                                jQuery('#AreaOfCoverageModal').find('#submitbtn').prop('disabled', false);
                            }
                        }
                    });
                }
            }
        });
    }
});

function suggestAreaOfCoverage(e, $this) {
    var keyevent = e
    if (keyevent.key != "Tab") {
        var search = jQuery($this).val();
        jQuery.ajax({
            url: "area_of_coverage-list?term=" + encodeURI(search),
            type: 'GET',
            dataType: 'json',
            processData: false,
            success: function (data) {
                jQuery("#area_of_coverage").removeClass('file-loader');
                if (data.response_code == 1) {
                    jQuery('#area_of_coverage_list').html(data.areaOfCoverageList);
                } else {
                     toastr.error(data.response_message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                jQuery("#area_of_coverage").removeClass('file-loader');
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

jQuery(document).on('click', '#area_of_coverage_list', function (e) {
    var suggest = e.target.innerHTML;
    jQuery('#area_of_coverage_suggesion').val(suggest);
    var hidden = jQuery('#area_of_coverage_suggesion').val();
    var suggestion_list = jQuery('#area_of_coverage_list').html;
    jQuery('#AreaOfCoverageModal').find('#area_of_coverage').val(hidden)
    var area_of_coverage = hidden;

    if (suggestion_list != '') {
        checkAreaOfCoverageName(area_of_coverage);
    }
    jQuery('#area_of_coverage_list').html('');
});


let lastVerifiedAreaOfCoverage = '';

jQuery(document).on('blur', '#area_of_coverage', function () {
    let area_of_coverage = jQuery(this).val().trim();

    if (area_of_coverage === '') return;

    if (area_of_coverage !== lastVerifiedAreaOfCoverage) {
        lastVerifiedAreaOfCoverage = area_of_coverage;
        checkAreaOfCoverageName(area_of_coverage);
    }
});

jQuery(document).on('input', '#area_of_coverage', function () {
    lastVerifiedAreaOfCoverage = '';
});

function checkAreaOfCoverageName(area_of_coverage) {
    var id = jQuery('#AreaOfCoverageModal').find('#commonAreaOfCoverageForm').find('#id').val();
    var formUrl = id != undefined && id != "" ? "verify-area_of_coverage?area_of_coverage=" + encodeURIComponent(area_of_coverage) + "&id=" + id : "verify-area_of_coverage?area_of_coverage=" + encodeURIComponent(area_of_coverage);
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

function verifyAreaOfCoverage() {
    var AreaOfCoverageName = jQuery('#area_of_coverage').val();
    var suggestion_list = jQuery('#area_of_coverage_list').html;

    if (suggestion_list != '') {
        checkAreaOfCoverageName(AreaOfCoverageName);
    }
}

jQuery('#AreaOfCoverageModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#AreaOfCoverageModal');
    thisForm.find('#id').val('');
    document.getElementById("commonAreaOfCoverageForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });
    jQuery('#AreaOfCoverageModal').find('#add_new').hide();
});

$('#AreaOfCoverageModal').on('shown.bs.modal', function () {
    const input = document.getElementById('area_of_coverage');
    input?.focus();

    var formIdblank = jQuery('#commonAreaOfCoverageForm').find('input[name="id"]').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#AreaOfCoverageModal').find('#add_new').show();
    } else {
        jQuery('#AreaOfCoverageModal').find('#add_new').hide();
    }
});

jQuery('#AreaOfCoverageModal').on('click', '#add_new', function () {
    jQuery('#AreaOfCoverageModal').find('#id').val('');
    document.getElementById("commonAreaOfCoverageForm").reset();
    const form = document.getElementById("commonAreaOfCoverageForm");
    if (form) {
        form.classList.remove('was-validated');
    }

    jQuery('#area_of_coverage').focus();
    jQuery('#AreaOfCoverageModal').find('#add_new').hide();
});

function getAreaOfCoverage($this = null) {
    var formUrl = "get-area_of_coverages";
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
                    var stgDrpHtml = `<option value="">Select Area of Coverage</option>`;
                    for (let indx in data.area_of_coverages) {
                        stgDrpHtml += `<option value="${data.area_of_coverages[indx].area_of_coverage_id}">${data.area_of_coverages[indx].area_of_coverage}</option>`;
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

function addedAreaOfCoverage($event) {
    if ($event == true) {
        getAreaOfCoverage(".mst_area_of_coverage");
    }
}
