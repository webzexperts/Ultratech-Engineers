jQuery(document).ready(function () {
    // Initialize details grid table on page load so headers and column search inputs appear immediately
    let initialDetailsTable = jQuery('#rtLabelPrintDetailsTable');
    if (initialDetailsTable.length) {
        let initialDt = initialDetailsTable.DataTable({
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            paging: true,
            searching: true,
            dom: 'blfrtip',
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: 0 }
            ],
            scrollX: true,
            bScrollCollapse: true
        });
        if (typeof fixDataTableColumnsUntilAdjusted === 'function') {
            fixDataTableColumnsUntilAdjusted(initialDt);
        }
        if (typeof initColumnSearch === 'function') {
            initColumnSearch('#rtLabelPrintDetailsTable', [0], 'common_search');
        }
    }

    // Open reports selection modal
    jQuery('#select_report_btn').on('click', function () {
        jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
        jQuery('#select_report_btn').prop('disabled', true);

        jQuery.ajax({
            url: 'get-rt_reports_list',
            type: 'GET',
            headers: headerOpt,
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    let tblHtml = ``;
                    if (data.reports && data.reports.length > 0) {
                        data.reports.forEach(row => {
                            tblHtml += `<tr>
                                <td>
                                    <input type="radio" name="rt_report_id" class="form-check-input select-copy-report-radio" id="rt_report_id_${row.id}" value="${row.id}" data-report-no="${row.test_report_no}" />
                                </td>
                                <td>${row.test_report_no || ''}</td>
                                <td>${row.revision_number || ''}</td>
                                <td>${row.test_report_date || ''}</td>
                                <td>${row.customer || ''}</td>
                                <td>${row.rt_no || ''}</td>
                                <td>${row.heat_no || ''}</td>
                                <td>${row.material || ''}</td>
                                <td>${row.job_description || ''}</td>
                            </tr>`;
                        });
                    }

                    let table = jQuery('#rtReportsTable');
                    if (jQuery.fn.DataTable.isDataTable(table)) {
                        table.DataTable().clear().destroy();
                    }
                    jQuery('#rtReportsTable tbody').empty().append(tblHtml);

                    let selectedReportId = jQuery('#selected_report_id').val();
                    if (selectedReportId) {
                        jQuery(`#rtReportsTable input[name="rt_report_id"][value="${selectedReportId}"]`).prop('checked', true);
                    }

                    jQuery('#RtReportsListModal').modal('show');

                    $new = table.DataTable({
                        pageLength: 50,
                        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                        paging: true,
                        searching: true,
                        oLanguage: {
                            sSearch: "Search :",
                            sEmptyTable: "No Test Report (RT) Available",
                            sZeroRecords: "No Test Report (RT) Available"
                        },
                        dom: 'blfrtip',
                        order: [[1, 'asc']],
                        columnDefs: [
                            { orderable: false, targets: 0 }
                        ],
                        sScrollX: true,
                        sScrollX: "100%",
                        sScrollXInner: "110%",
                        bScrollCollapse: true,
                    });
                    if (typeof fixDataTableColumnsUntilAdjusted === 'function') {
                        fixDataTableColumnsUntilAdjusted($new);
                    }
                } else {
                    toastr.error('Failed to load reports.');
                }
            },
            error: function () {
                toastr.error('Something went wrong!');
            },
            complete: function () {
                jQuery('#select_report_btn').prop('disabled', false);
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            }
        });
    });

    // Adjust columns when reports list modal is fully displayed
    jQuery('#RtReportsListModal').on('shown.bs.modal', function () {
        let $table = jQuery('#rtReportsTable');
        if (jQuery.fn.DataTable.isDataTable($table)) {
            $table.DataTable().columns.adjust().draw();
            if (typeof initColumnSearch === 'function') {
                initColumnSearch('#rtReportsTable', [0], 'common_search');
            }
        }
    });

    // Handle submit selection of a report from selection modal
    jQuery('#submitReportSelect').on('click', function () {
        let checkedRadio = jQuery('input[name="rt_report_id"]:checked');
        if (checkedRadio.length === 0) {
            toastr.error("Please Select Test Report (RT)");
            return;
        }

        let selectedId = checkedRadio.val();
        let selectedReportNo = checkedRadio.data('report-no');

        jQuery('#selected_report_id').val(selectedId);
        jQuery('#selected_report_no').val(selectedReportNo);
        jQuery('#RtReportsListModal').modal('hide');

        // Fetch details of the selected report
        jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');

        jQuery.ajax({
            url: 'get-rt_report_details_for_print',
            type: 'GET',
            data: { id: selectedId },
            headers: headerOpt,
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1) {
                    let tblHtml = ``;
                    if (data.report_details_data && data.report_details_data.length > 0) {
                        data.report_details_data.forEach(row => {
                            /*
                            // Old logic: rendering multiple grid rows based on film_qty
                            let qty = parseInt(row.film_qty) || 1;
                            for (let i = 0; i < qty; i++) {
                                tblHtml += `<tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="selected_detail_id[]" class="form-check-input detail-row-checkbox" value="${row.id}" />
                                    </td>
                                    <td>${row.result || ''}</td>
                                    <td>${data.report_data.test_report_no || ''}</td>
                                    <td>${data.report_data.test_report_date || ''}</td>
                                    <td>${data.report_data.customer || ''}</td>
                                    <td>${data.report_data.party || ''}</td>
                                    <td>${parseInt(row.sr_no) || ''}</td>
                                    <td>${row.identification || ''}</td>
                                    <td>${row.location || ''}</td>
                                    <td>${row.film_type || ''}</td>
                                    <td>${row.film_size || ''}</td>
                                    <td>${row.no_of_film_fix || ''}</td>
                                    <td>${row.thickness || ''}</td>
                                    <td>${row.sfd || ''}</td>
                                    <td>${row.density || ''}</td>
                                    <td>${row.iqi || ''}</td>
                                    <td>${row.sensitivity || ''}</td>
                                </tr>`;
                            }
                            */

                            // New logic: Single grid row per item
                            tblHtml += `<tr>
                                <td class="text-center">
                                    <input type="checkbox" name="selected_detail_id[]" class="form-check-input detail-row-checkbox" value="${row.id}" />
                                </td>
                                <td>${row.result || ''}</td>
                                <td>${data.report_data.test_report_no || ''}</td>
                                <td>${data.report_data.revision_number || ''}</td>
                                <td>${data.report_data.test_report_date || ''}</td>
                                <td>${data.report_data.customer || ''}</td>
                                <td>${data.report_data.party || ''}</td>
                                <td>${parseInt(row.sr_no) || ''}</td>
                                <td>${row.identification || ''}</td>
                                <td>${row.location || ''}</td>
                                <td>${row.film_type || ''}</td>
                                <td>${row.film_size || ''}</td>
                                <td>${row.no_of_film_fix || ''}</td>
                                <td>${row.thickness || ''}</td>
                                <td>${row.sfd || ''}</td>
                                <td>${row.density || ''}</td>
                                <td>${row.iqi || ''}</td>
                                <td>${row.sensitivity || ''}</td>
                            </tr>`;
                        });

                        let detailsTable = jQuery('#rtLabelPrintDetailsTable');
                        if (jQuery.fn.DataTable.isDataTable(detailsTable)) {
                            detailsTable.DataTable().clear().destroy();
                        }

                        jQuery('#rtLabelPrintDetailsTable tbody').empty().append(tblHtml);
                        detailsTable.show();

                        let dt = detailsTable.DataTable({
                            pageLength: 25,
                            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                            paging: true,
                            searching: true,
                            dom: 'blfrtip',
                            order: [[7, 'asc']],
                            columnDefs: [
                                { orderable: false, targets: 0 }
                            ],
                            scrollX: true,
                            bScrollCollapse: true
                        });

                        if (typeof fixDataTableColumnsUntilAdjusted === 'function') {
                            fixDataTableColumnsUntilAdjusted(dt);
                        }

                        if (typeof initColumnSearch === 'function') {
                            initColumnSearch('#rtLabelPrintDetailsTable', [0], 'common_search');
                        }

                        // Attach draw listener
                        dt.on('draw', function () {
                            jQuery('#rtLabelPrintDetailsTable th').not(':first').attr('tabindex', '-1');
                            updatePrintButtonState();
                        });

                        jQuery('#print_label_btn').prop('disabled', true); // Disabled until checked
                        jQuery('#select_all_checkbox').prop('checked', false);
                    } else {
                        let detailsTable = jQuery('#rtLabelPrintDetailsTable');
                        if (jQuery.fn.DataTable.isDataTable(detailsTable)) {
                            detailsTable.DataTable().clear().destroy();
                        }
                        jQuery('#rtLabelPrintDetailsTable tbody').empty();
                        jQuery('#rtLabelPrintDetailsTable').hide();
                        toastr.error('No detail records found for this report.');
                        jQuery('#print_label_btn').prop('disabled', true);
                    }
                } else {
                    toastr.error('Failed to load report details.');
                }
            },
            error: function () {
                toastr.error('Something went wrong!');
            },
            complete: function () {
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            }
        });
    });

    // Select all checkbox functionality (delegated to document to handle DataTables cloned scroll header)
    jQuery(document).on('change', '#select_all_checkbox', function () {
        let isChecked = jQuery(this).is(':checked');

        // Sync both checkboxes (original and cloned)
        jQuery('#select_all_checkbox').prop('checked', isChecked);

        // Toggle checkboxes of all visible rows
        jQuery('#rtLabelPrintDetailsTable tbody tr:visible').each(function () {
            jQuery(this).find('.detail-row-checkbox').prop('checked', isChecked);
        });
        updatePrintButtonState();
    });

    // Update select_all and print button states on row checkbox click
    jQuery(document).on('change', '.detail-row-checkbox', function () {
        updatePrintButtonState();
    });

    function updatePrintButtonState() {
        let detailsTable = jQuery('#rtLabelPrintDetailsTable');
        let dt = jQuery.fn.DataTable.isDataTable(detailsTable) ? detailsTable.DataTable() : null;

        let totalCheckedCount = 0;
        if (dt) {
            totalCheckedCount = jQuery(dt.rows().nodes()).find('.detail-row-checkbox:checked').length;
        } else {
            totalCheckedCount = detailsTable.find('.detail-row-checkbox:checked').length;
        }

        if (totalCheckedCount > 0) {
            jQuery('#print_label_btn').prop('disabled', false);
        } else {
            jQuery('#print_label_btn').prop('disabled', true);
        }

        let visibleRows = detailsTable.find('tbody tr:visible');
        let visibleCheckedCount = visibleRows.find('.detail-row-checkbox:checked').length;
        let totalVisible = visibleRows.length;

        if (totalVisible > 0 && visibleCheckedCount === totalVisible) {
            jQuery('#select_all_checkbox').prop('checked', true);
        } else {
            jQuery('#select_all_checkbox').prop('checked', false);
        }
    }

    // Print label button click handler
    jQuery('#print_label_btn').on('click', function () {
        // let selectedReportId = jQuery('#selected_report_id').val();
        // let selectedDetailIds = [];

        // jQuery('#rtLabelPrintDetailsTable tbody tr:visible').find('.detail-row-checkbox:checked').each(function () {
        //     selectedDetailIds.push(jQuery(this).val());
        // });

        // if (!selectedReportId || selectedDetailIds.length === 0) {
        //     toastr.error("Please Select At Least One Report To Print");
        //     return;
        // }
        let detailsTable = jQuery('#rtLabelPrintDetailsTable');
        let dt = jQuery.fn.DataTable.isDataTable(detailsTable) ? detailsTable.DataTable() : null;

        let checkedCheckboxes;
        if (dt) {
            checkedCheckboxes = jQuery(dt.rows().nodes()).find('.detail-row-checkbox:checked');
        } else {
            checkedCheckboxes = detailsTable.find('.detail-row-checkbox:checked');
        }

        if (checkedCheckboxes.length === 0) {
            toastr.error("Please select At Least One Report.");
            return;
        }
        let ids = [];
        checkedCheckboxes.each(function () {
            ids.push(jQuery(this).val());
        });
        jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');

        jQuery.ajax({
            url: 'print-rt_label_print',
            type: 'POST',
            data: {
                details_ids: ids.join(',')
            },
            headers: headerOpt,
            dataType: 'json',
            success: function (data) {
                if (data.response_code == 1 && data.url) {
                    window.open(data.url, '_blank');
                } else {
                    toastr.error(data.response_message || 'Failed to generate PDF.');
                }
            },
            error: function () {
                toastr.error('Something went wrong while generating label print PDF!');
            },
            complete: function () {
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
            }
        });
    });
});