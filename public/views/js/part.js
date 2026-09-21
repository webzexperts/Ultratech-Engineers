var currentUrl = window.location.href;
var partAfterManage = currentUrl.split("manage-")[1];
var containerIndex = 0;
var isProgrammaticChange = false;
let lastVerifiedParts = {};

function partDetailsRow(index, in_use = false) {
    let removeBtn = '';
    if (in_use == true) {
        removeBtn = `<button type="button" class="btn btn-danger btn-sm removeContainerBtn" data-index="${index}" style="display:none;" disabled> - </button>`;
    } else {
        removeBtn = `<button type="button" class="btn btn-danger btn-sm removeContainerBtn" data-index="${index}"> - </button>`;
    }
    return `
        <tr class="container-row" data-index="${index}">
            <td class="text-center">
                <input type="hidden" id="part_id_${index}" value="0">
                <input type="hidden" id="mode_${index}" value="Insert">
                ${removeBtn}
            </td>
            <td>
                <div class="position-relative">
                    <input type="text" class="form-control part_no" id="part_no_${index}" required >
                    <div class="invalid-tooltip">Enter Part No.</div>
                </div>
            </td>
            <td>
                <div class="position-relative">
                    <input type="text" class="form-control drg_no" id="drg_no_${index}" required>
                    <div class="invalid-tooltip">Enter Drg. No.</div>
                </div>
            </td>
        </tr>
    `;
}

// Edit part row click
jQuery('#dyntable tbody').on('click', '.edit_part', function () {
    var data = table.row(jQuery(this).parents('tr')).data();
    if (data && data["part_id"]) {
        fetchAndFillPart(data["part_id"]);
    }
});

// Function to fetch and fill part data
function fetchAndFillPart(id) {
    if (!id) return;
    jQuery('#PartModal').modal('show');
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery.ajax({
        url: "edit-part",
        type: 'GET',
        data: { id: id },
        headers: headerOpt,
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.part_data != null) {
                isProgrammaticChange = true;
                jQuery('#PartModal').find('#job_desc_id').val(data.part_data.job_desc_id).trigger("change.select2");
                isProgrammaticChange = false;
                jQuery('#PartModal').find('#job_desc_id').addClass('skip-tab');
                setSelect2Readonly('#PartModal #job_desc_id', true);
                jQuery('#PartModal').find('#id').val(data.part_data.part_id);
                jQuery('#PartModal').find('#add_new').show();

                jQuery('#PartDetailTable tbody').empty();
                containerIndex = 0;
                if (data.parts_data && data.parts_data.length > 0) {
                    data.parts_data.forEach((row, index) => {
                        jQuery('#PartDetailTable tbody').append(partDetailsRow(index, row.in_use));
                        jQuery("#part_id_" + index).val(row.part_id || 0);
                        jQuery("#mode_" + index).val('Update');
                        jQuery("#part_no_" + index).val(row.part_no || '');
                        jQuery("#drg_no_" + index).val(row.drg_no || '');
                    });
                    containerIndex = data.parts_data.length - 1;
                } else {
                    jQuery('#PartDetailTable tbody').append(partDetailsRow(0));
                }

                const form = document.getElementById("commonPartForm");
                if (form) form.classList.remove('was-validated');
                setTimeout(function () {
                    jQuery('#PartDetailTable tbody tr:visible:first').find('.part_no').focus();
                }, 20);
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

// Reset button click for part modal
jQuery('#PartModal').on('click', '#resetbtn', function (e) {
    e.preventDefault();
    lastVerifiedParts = {};
    var formId = jQuery('#PartModal').find('#id').val();
    if (!formId) {
        var form = document.getElementById("commonPartForm");
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
        jQuery('#PartModal').find('#job_desc_id').val('').trigger('change.select2');
        jQuery('#PartModal').find('#job_desc_id').removeClass('skip-tab');
        setSelect2Readonly('#PartModal #job_desc_id', false);
        jQuery('#PartDetailTable tbody').empty();
        containerIndex = 0;
        jQuery('#PartDetailTable tbody').append(partDetailsRow(containerIndex));
        setTimeout(function () {
            let sel = jQuery('#PartModal #job_desc_id')
                .next('.select2-container')
                .find('.select2-selection');
            sel.attr('tabindex', 0).focus();
        }, 20);
    } else {
        fetchAndFillPart(formId);
    }
});

function submitPartForm(form, formUrl, formData, formId) {
    $.ajax({
        type: 'POST',
        url: formUrl,
        data: formData,
        contentType: false,
        processData: false,
        success: function (data) {
            if (data.response_code == 1) {
                if (formId != undefined && formId != "") {
                    if (partAfterManage == 'part') {
                        function redirectFn() {
                            window.location.reload();
                        }
                        toastSuccess(data.response_message, redirectFn);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#PartModal').find('#submitbtn').prop('disabled', false);
                    } else {
                        function nextFn() {
                            document.getElementById("commonPartForm").reset();
                            const form = document.getElementById("commonPartForm");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            jQuery('#PartModal').modal('hide');
                        }
                        toastSuccess(data.response_message, nextFn);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#PartModal').find('#submitbtn').prop('disabled', false);
                        addedPart(true);
                    }
                }
                else if (formId == undefined || formId == "") {
                    function nextFn() {
                        document.getElementById("commonPartForm").reset();
                        const form = document.getElementById("commonPartForm");
                        if (form) {
                            form.classList.remove('was-validated');
                        }
                        jQuery('#commonPartForm #job_desc_id').val('').trigger("change.select2");
                        jQuery('#PartModal').find('#job_desc_id').removeClass('skip-tab');
                        setSelect2Readonly('#PartModal #job_desc_id', false);
                        jQuery('#PartDetailTable tbody').empty();
                        containerIndex = 0;
                        jQuery('#PartDetailTable tbody').append(partDetailsRow(containerIndex));

                        setTimeout(function () {
                            let sel = jQuery('#PartModal #job_desc_id')
                                .next('.select2-container')
                                .find('.select2-selection');
                            sel.attr('tabindex', 0).focus();
                        }, 20);

                        if (partAfterManage != 'part') {
                            jQuery('#PartModal').modal('hide');
                        }
                    }
                    toastSuccess(data.response_message, nextFn);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#PartModal').find('#submitbtn').prop('disabled', false);
                    addedPart(true);
                }
                else {
                    toastError(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#PartModal').find('#submitbtn').prop('disabled', false);
                }
            } else {
                toastr.error(data.response_message);
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                jQuery('#PartModal').find('#submitbtn').prop('disabled', false);
            }
        },
        error: function (xhr) {
            jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
            jQuery('#PartModal').find('#submitbtn').prop('disabled', false);
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                let errors = xhr.responseJSON.errors;
                let errorMsg = '';
                $.each(errors, function (key, value) {
                    errorMsg += value + '\n';
                });
                toastr.error(errorMsg);
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                jQuery('#PartModal').find('#submitbtn').prop('disabled', false);
            } else {
                toastr.error('Something went wrong. Please try again.');
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                jQuery('#PartModal').find('#submitbtn').prop('disabled', false);
            }
        }
    });
}

$('#commonPartForm').on('submit', function (e) {
    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#PartModal').find('#submitbtn').prop('disabled', true);
    e.preventDefault();

    let form = this;
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PartModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    if (jQuery('#PartDetailTable tbody tr:visible').length === 0 || jQuery('#PartDetailTable tbody').find('#noDetails').length > 0) {
        toastr.error("Please Add At Least One Part Details.");
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PartModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var error = false;
    var addedParts = [];
    var visibleRows = [];

    jQuery('#PartDetailTable tbody tr:visible').each(function () {
        var index = jQuery(this).attr('data-index');
        if (index === undefined) return;

        var part_no = jQuery("#part_no_" + index).val();
        var drg_no = jQuery("#drg_no_" + index).val();

        if (!part_no || !part_no.trim()) {
            toastr.error('Enter Part No.');
            jQuery("#part_no_" + index).focus();
            error = true;
            return false;
        }



        if (!drg_no || !drg_no.trim()) {
            toastr.error('Enter Drg. No.');
            jQuery("#drg_no_" + index).focus();
            error = true;
            return false;
        }

        part_no = part_no.trim();
        drg_no = drg_no.trim();

        var combo = part_no.toUpperCase() + '|' + drg_no.toUpperCase();
        if (addedParts.includes(combo)) {
            toastr.error('Duplicate Part No. & Drg. No. Found.');
            jQuery("#part_no_" + index).focus();
            error = true;
            return false;
        }
        addedParts.push(combo);
        visibleRows.push({ index: index, part_no: part_no, drg_no: drg_no });
    });

    if (error) {
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#PartModal').find('#submitbtn').prop('disabled', false);
        return;
    }

    var parts_detail_data = [];
    jQuery('#PartDetailTable tbody tr').each(function () {
        var index = jQuery(this).attr('data-index');
        if (index === undefined) return;

        var mode = jQuery("#mode_" + index).val();
        if (mode === 'Delete') {
            parts_detail_data[index] = null;
        } else {
            parts_detail_data[index] = {
                part_id: jQuery("#part_id_" + index).val() || 0,
                part_no: jQuery("#part_no_" + index).val().trim(),
                drg_no: jQuery("#drg_no_" + index).val().trim()
            };
        }
    });

    var formId = jQuery('#PartModal').find('#commonPartForm').find('#id').val();
    var job_desc_id = jQuery('#PartModal').find("#job_desc_id").val();
    var formUrl = formId != undefined && formId != "" ? "update-part" : "store-part";

    let formData = new FormData(form);
    formData.append('parts_detail_data', JSON.stringify(parts_detail_data));
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    function verifyRowsAndSubmit(rows, index) {
        if (index >= rows.length) {
            submitPartForm(form, formUrl, formData, formId);
            return;
        }

        var row = rows[index];
        var part_id = jQuery("#part_id_" + row.index).val();

        if (job_desc_id && row.part_no && row.drg_no) {
            jQuery.ajax({
                url: "verify-part",
                type: 'GET',
                data: {
                    job_desc_id: job_desc_id,
                    part_no: row.part_no,
                    drg_no: row.drg_no,
                    part_id: part_id
                },
                headers: headerOpt,
                dataType: 'json',
                success: function (data) {
                    if (data.response_code == 1) {
                        toastr.error(data.response_message);
                        jQuery('#part_no_' + row.index).focus();
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#PartModal').find('#submitbtn').prop('disabled', false);
                    } else {
                        verifyRowsAndSubmit(rows, index + 1);
                    }
                },
                error: function () {
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#PartModal').find('#submitbtn').prop('disabled', false);
                }
            });
        } else {
            verifyRowsAndSubmit(rows, index + 1);
        }
    }

    verifyRowsAndSubmit(visibleRows, 0);
});

jQuery('#PartModal').on('hide.bs.modal', function (e) {
    var thisForm = jQuery('#PartModal');
    thisForm.find('#id').val('');
    lastVerifiedParts = {};
    isProgrammaticChange = false;
    document.getElementById("commonPartForm").reset();
    thisForm.find('input, textarea, select').each(function () {
        jQuery(this).val('');
        jQuery(this).removeAttr('readonly');
        jQuery(this).prop('checked', false);
    });
    jQuery('#PartModal').find('#job_desc_id').removeClass('skip-tab');
    setSelect2Readonly('#PartModal #job_desc_id', false);
    thisForm.find('#add_new').hide();

    jQuery('#PartDetailTable tbody').empty();
    containerIndex = 0;
    jQuery('#PartDetailTable tbody').append(partDetailsRow(containerIndex));

    const form = document.getElementById("commonPartForm");
    if (form) {
        form.classList.remove('was-validated');
    }
});

$('#PartModal').on('shown.bs.modal', function () {
    isProgrammaticChange = false;
    var formIdblank = jQuery('#PartModal').find('#id').val();
    if (formIdblank && formIdblank !== "") {
        jQuery('#PartModal').find('#add_new').show();
        setTimeout(function () {
            jQuery('#PartDetailTable tbody tr:visible:first').find('.part_no').focus();
        }, 20);
    } else {
        jQuery('#PartModal').find('#add_new').hide();
        setTimeout(function () {
            let sel = jQuery('#PartModal #job_desc_id')
                .next('.select2-container')
                .find('.select2-selection');
            sel.attr('tabindex', 0).focus();
        }, 20);
    }
});

jQuery('#PartModal').on('click', '#add_new', function () {
    jQuery('#PartModal').find('#id').val('');
    lastVerifiedParts = {};
    isProgrammaticChange = false;
    document.getElementById("commonPartForm").reset();
    const form = document.getElementById("commonPartForm");
    if (form) {
        form.classList.remove('was-validated');
    }
    jQuery('#PartModal').find('#job_desc_id').val('').trigger('change.select2');
    jQuery('#PartModal').find('#job_desc_id').removeClass('skip-tab');
    setSelect2Readonly('#PartModal #job_desc_id', false);
    jQuery('#PartDetailTable tbody').empty();
    containerIndex = 0;
    jQuery('#PartDetailTable tbody').append(partDetailsRow(containerIndex));
    setTimeout(function () {
        let sel = jQuery('#PartModal #job_desc_id')
            .next('.select2-container')
            .find('.select2-selection');
        sel.attr('tabindex', 0).focus();
    }, 20);
    jQuery('#PartModal').find('#add_new').hide();
});

jQuery(document).on('click', '#addContainerBtn', function () {
    containerIndex++;
    if (jQuery('#PartDetailTable tbody').find('#noDetails').length > 0) {
        jQuery('#PartDetailTable tbody').empty();
    }
    jQuery('#PartDetailTable tbody').append(partDetailsRow(containerIndex));
});

jQuery(document).on('click', '.removeContainerBtn', function () {
    let index = jQuery(this).data('index');
    let detailId = jQuery("#part_id_" + index).val();

    toastDetailDelete("Do you want to delete this record?", function () {
        if (detailId != '' && detailId != 0 && detailId != undefined) {
            jQuery("#mode_" + index).val('Delete');
            jQuery('tr[data-index="' + index + '"]').hide();
            jQuery('tr[data-index="' + index + '"]').find('input').removeAttr('required');
        } else {
            jQuery('tr[data-index="' + index + '"]').remove();
        }

        if (jQuery('#PartDetailTable tbody tr:visible').length === 0) {
            jQuery('#PartDetailTable tbody').append(`<tr><td colspan="3" class="text-center" id="noDetails">No Part Details Added</td></tr>`);
        }
    });
});

jQuery(document).on('change', '#PartModal #job_desc_id', function () {
    if (isProgrammaticChange) {
        isProgrammaticChange = false;
        return;
    }
    var job_desc_id = jQuery(this).val();

    if (job_desc_id) {
        jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
        jQuery.ajax({
            url: "edit-part",
            type: 'GET',
            data: { job_desc_id: job_desc_id },
            headers: headerOpt,
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1 && data.part_data != null) {
                    jQuery('#PartModal').find('#id').val(data.part_data.part_id);
                    jQuery('#PartModal').find('#add_new').show();
                    jQuery('#PartModal').find('#job_desc_id').addClass('skip-tab');
                    setSelect2Readonly('#PartModal #job_desc_id', true);

                    jQuery('#PartDetailTable tbody').empty();
                    containerIndex = 0;
                    if (data.parts_data && data.parts_data.length > 0) {
                        data.parts_data.forEach((row, index) => {
                            jQuery('#PartDetailTable tbody').append(partDetailsRow(index, row.in_use));
                            jQuery("#part_id_" + index).val(row.part_id || 0);
                            jQuery("#mode_" + index).val('Update');
                            jQuery("#part_no_" + index).val(row.part_no || '');
                            jQuery("#drg_no_" + index).val(row.drg_no || '');
                        });
                        containerIndex = data.parts_data.length - 1;
                    } else {
                        jQuery('#PartDetailTable tbody').append(partDetailsRow(0));
                    }

                    const form = document.getElementById("commonPartForm");
                    if (form) form.classList.remove('was-validated');
                } else {
                    // Not existing, make sure table has at least one empty row and id is empty
                    jQuery('#PartModal').find('#id').val('');
                    jQuery('#PartModal').find('#add_new').hide();
                    jQuery('#PartModal').find('#job_desc_id').removeClass('skip-tab');
                    setSelect2Readonly('#PartModal #job_desc_id', false);

                    // If table has existing DB records loaded, reset them
                    jQuery('#PartDetailTable tbody').empty();
                    containerIndex = 0;
                    jQuery('#PartDetailTable tbody').append(partDetailsRow(containerIndex));
                }
            },
            error: function (jqXHR) {
                console.log('Error checking job description parts.');
            },
            complete: function () {
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            }
        });
    } else {
        // If they cleared the job description dropdown
        jQuery('#PartModal').find('#id').val('');
        jQuery('#PartModal').find('#add_new').hide();
        jQuery('#PartModal').find('#job_desc_id').removeClass('skip-tab');
        setSelect2Readonly('#PartModal #job_desc_id', false);
        jQuery('#PartDetailTable tbody').empty();
        containerIndex = 0;
        jQuery('#PartDetailTable tbody').append(partDetailsRow(containerIndex));
    }
});

function getPart($this = null) {
    var formUrl = "get-part";
    let selectedJobId = jQuery('#inqd_job_description_id').val() || jQuery('#MaterialInwardDetailsForm #inward_job_desc_id').val() || '';
    
    if (selectedJobId === "") {
        if ($this != null) {
            jQuery($this).each(function () {
                let $dropdown = jQuery(this);
                $dropdown.empty().append(`<option value="">Select Part No.</option>`).val('').trigger('change.select2');
            });
        }
        return;
    }

    let previousSelectedPart = null;

    if ($this != null) {
        previousSelectedPart = jQuery($this).val();
    }

    jQuery.ajax({
        url: formUrl,
        type: 'GET',
        dataType: 'json',
        processData: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (data) {
            if (data.response_code == 1 && $this != null) {
                // Group parts by job_desc_id
                let partsByJob = {};
                for (let indx in data.part) {
                    let partData = data.part[indx];
                    let jid = partData.job_desc_id;
                    if (!partsByJob[jid]) {
                        partsByJob[jid] = [];
                    }
                    partsByJob[jid].push(partData);
                }

                // Dynamically update the data-parts attribute for all job description options
                jQuery('.suggest_job_description option').each(function () {
                    let jid = jQuery(this).val();
                    if (jid) {
                        let jobParts = partsByJob[jid] || [];
                        jQuery(this).attr('data-parts', JSON.stringify(jobParts));
                        jQuery(this).data('parts', jobParts);
                    }
                });

                var stgDrpHtml = `<option value="">Select Part No.</option>`;
                for (let indx in data.part) {
                    let partData = data.part[indx];
                    if (selectedJobId == "" || partData.job_desc_id == selectedJobId) {
                        let dispName = (partData.drg_no && partData.drg_no.trim() !== "") ? (partData.part_no + ' - ' + partData.drg_no) : partData.part_no;
                        stgDrpHtml += `<option value="${partData.part_id}" data-part_no="${partData.part_no}" data-drg_no="${partData.drg_no ?? ''}">${dispName}</option>`;
                    }
                }

                jQuery($this).each(function () {
                    let $dropdown = jQuery(this);
                    
                    let existingVals = [];
                    $dropdown.find('option').each(function() {
                        let val = jQuery(this).val();
                        if (val) existingVals.push(String(val));
                    });

                    $dropdown.empty().append(stgDrpHtml);
                    
                    if (previousSelectedPart &&
                        $dropdown.find('option[value="' + previousSelectedPart + '"]').length > 0) {
                        $dropdown.val(previousSelectedPart).trigger('change');
                    } else {
                        $dropdown.val('').trigger('change');
                    }
                });
            }
        },
        error: function (jqXHR) {
            if (jqXHR.status == 401) {
                console.log(jqXHR.statusText);
            } else {
                console.log('Something went wrong!');
            }

            if (jqXHR.responseText) {
                console.log(JSON.parse(jqXHR.responseText));
            }
        }
    });
}

function addedPart($event) {
    if ($event == true) {
        let parentJobDescId = jQuery('#inqd_job_description_id').val() || jQuery('#MaterialInwardDetailsForm #inward_job_desc_id').val() || '';
        let isOpening = jQuery('#PartModal').is(':visible') === false;
        if (parentJobDescId && isOpening) {
            jQuery('#PartModal #job_desc_id').val(parentJobDescId).trigger('change');
            jQuery('#PartModal #job_desc_id').addClass('skip-tab');
            setSelect2Readonly('#PartModal #job_desc_id', true);
        }
        if (!isOpening) {
            getPart(".suggest_part");
        }
    }
}

function checkDuplicatePart(index) {
    var part_no = jQuery('#part_no_' + index).val();
    var drg_no = jQuery('#drg_no_' + index).val();

    if (!part_no || !drg_no) return;

    part_no = part_no.trim();
    drg_no = drg_no.trim();

    // 1. Table-level check: Check if duplicate exists in another row in the table
    var hasTableDuplicate = false;
    jQuery('#PartDetailTable tbody tr:visible').each(function () {
        var otherIndex = jQuery(this).attr('data-index');
        if (otherIndex !== undefined && otherIndex != index) {
            var other_part_no = jQuery('#part_no_' + otherIndex).val();
            var other_drg_no = jQuery('#drg_no_' + otherIndex).val();
            if (other_part_no && other_drg_no &&
                other_part_no.trim().toUpperCase() === part_no.toUpperCase() &&
                other_drg_no.trim().toUpperCase() === drg_no.toUpperCase()) {
                hasTableDuplicate = true;
                return false;
            }
        }
    });

    if (hasTableDuplicate) {
        toastr.error('Duplicate Job Description, Part No. & Drg. No. Found.');
        jQuery('#part_no_' + index).val('');
        jQuery('#drg_no_' + index).val('');
        delete lastVerifiedParts[index];
        jQuery('#part_no_' + index).focus();
        return;
    }

    // 2. Database-level check: Check via verify-part AJAX endpoint
    var job_desc_id = jQuery('#PartModal #job_desc_id').val();
    var part_id = jQuery('#part_id_' + index).val();

    if (job_desc_id) {
        jQuery.ajax({
            url: "verify-part",
            type: 'GET',
            data: {
                job_desc_id: job_desc_id,
                part_no: part_no,
                drg_no: drg_no,
                part_id: part_id
            },
            headers: headerOpt,
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    toastr.error(data.response_message);
                    jQuery('#part_no_' + index).val('');
                    jQuery('#drg_no_' + index).val('');
                    delete lastVerifiedParts[index];
                    jQuery('#part_no_' + index).focus();
                }
            }
        });
    }
}

jQuery(document).on('blur', '.part_no, .drg_no', function () {
    var index = jQuery(this).closest('tr').attr('data-index');
    if (index === undefined) return;

    var part_no = jQuery('#part_no_' + index).val().trim();
    var drg_no = jQuery('#drg_no_' + index).val().trim();

    if (part_no === '' || drg_no === '') return;

    var cacheKey = index;
    var currentVal = part_no.toUpperCase() + '|' + drg_no.toUpperCase();

    if (!lastVerifiedParts[cacheKey] || lastVerifiedParts[cacheKey] !== currentVal) {
        lastVerifiedParts[cacheKey] = currentVal;
        checkDuplicatePart(index);
    }
});

jQuery(document).on('input', '.part_no, .drg_no', function () {
    var index = jQuery(this).closest('tr').attr('data-index');
    if (index !== undefined) {
        delete lastVerifiedParts[index];
    }
});