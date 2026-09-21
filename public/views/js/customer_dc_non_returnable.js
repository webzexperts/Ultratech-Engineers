var deletedDetails = [];

jQuery(document).ready(function () {

    // 2. Load Customer DC Modal Add Trigger
    jQuery('#add_dc_btn').on('click', function () {
        resetFormToAddMode();
    });

    // 3. Customer Select Change -> Enable/Disable Pending Button
    jQuery('#customer_id').on('change', function () {
        var customerId = jQuery(this).val();
        var formId = jQuery('#commonCustomerDCForm #id').val();
        if (!formId && customerId && customerId !== '') {
            jQuery('#pending_btn').prop('disabled', false);
        } else {
            jQuery('#pending_btn').prop('disabled', true);
        }
    });

    // 4. Open Pending Modal -> Load Items
    jQuery('#pending_btn').on('click', function () {
        var customerId = jQuery('#customer_id').val();
        var editDcId = jQuery('#commonCustomerDCForm #id').val();



        var $table = jQuery('#PendingCustomerDcTable');
        if (jQuery.fn.DataTable.isDataTable($table)) {
            $table.DataTable().clear().destroy();
        }

        jQuery('#PendingCustomerDcTable tbody').html('<tr><td colspan="16" class="text-center">Loading pending items...</td></tr>');
        jQuery('#checkall-pending_data').prop('checked', false);

        pending_inward_items_map = {};
        jQuery.ajax({
            url: 'get-pending-customer_dc_non_returnable',
            type: 'GET',
            data: { customer_id: customerId, dc_id: editDcId },
            headers: headerOpt,
            success: function (res) {
                if (res.response_code == 1 && res.data.length > 0) {
                    var html = '';
                    jQuery.each(res.data, function (idx, item) {
                        pending_inward_items_map[item.material_inward_details_id] = item;
                        var inQty = parseInt(item.in_qty) || 0;
                        var pendQty = parseInt(item.pend_qty) || 0;
                        html += `<tr>
                            <td><input type="checkbox" class="form-check-input pending-item-check" value="${item.material_inward_details_id}"></td>
                            <td>${item.mi_number || ''}</td>
                            <td>${item.mi_date || ''}</td>
                            <td>${item.mi_challan_number || ''}</td>
                            <td>${item.mi_challan_date || ''}</td>
                            <td>${item.mi_po_number || ''}</td>
                            <td>${item.mi_po_date || ''}</td>
                            <td>${item.type_of_test || ''}</td>
                            <td>${item.type_of_job || ''}</td>
                            <td>${item.job_description || ''}</td>
                            <td>${item.part_no || ''}</td>
                            <td>${item.drg_no || ''}</td>
                            <td>${item.material_name || item.material || ''}</td>
                            <td>${item.mid_heat_no || ''}</td>
                            <td>${item.mid_rt_no || ''}</td>
                            <td>${item.mid_product_code || ''}</td>
                            <td>${inQty}</td>
                            <td>${pendQty}</td>
                        </tr>`;
                    });
                    jQuery('#PendingCustomerDcTable tbody').html(html);

                    var $new = $table.DataTable({
                        paging: true,
                        searching: true,
                        dom: 'lrtip',
                        order: [[1, 'asc']],
                        columnDefs: [
                            { orderable: false, targets: 0 }
                        ],
                        "sScrollX": true,
                        "sScrollX": "100%",
                        "sScrollXInner": "110%",
                        "bScrollCollapse": true,
                    });
                    if (typeof fixDataTableColumnsUntilAdjusted === 'function') {
                        fixDataTableColumnsUntilAdjusted($new);
                    }
                    updatePendingCheckboxes();
                } else {
                    jQuery('#PendingCustomerDcTable tbody').html('<tr><td colspan="18" class="text-center text-muted">No pending inward items found for this customer.</td></tr>');
                }
            },
            error: function () {
                jQuery('#PendingCustomerDcTable tbody').html('<tr><td colspan="18" class="text-center text-danger">Failed to load pending items.</td></tr>');
            }
        });
    });

    jQuery('#CustomerDCNonReturnableModal').on('shown.bs.modal', function () {
        setTimeout(function () {
            focusFirstInput('#commonCustomerDCForm');
        }, 150);
    });

    jQuery('#PendingInwardForCustomerDcModal').on('shown.bs.modal', function () {
        let $table = jQuery('#PendingCustomerDcTable');
        if (jQuery.fn.DataTable.isDataTable($table)) {
            $table.DataTable().columns.adjust().draw();
            if (typeof initColumnSearch === 'function') {
                initColumnSearch('#PendingCustomerDcTable', [0], 'common_search');
            }
        }
        updatePendingCheckboxes();
    });

    jQuery('#PendingInwardForCustomerDcModal').on('hidden.bs.modal', function () {
        setTimeout(function () {
            var firstDcQtyInput = jQuery('#DCDetailTable tbody tr[data-mid-id]:first .dc_qty_input');
            if (firstDcQtyInput.length > 0) {
                firstDcQtyInput.focus().select();
            } else {
                var $cust = jQuery('#commonCustomerDCForm #customer_id');
                if ($cust.length && !$cust.prop('disabled')) {
                    $cust.focus();
                } else {
                    focusFirstInput('#commonCustomerDCForm');
                }
            }
        }, 150);
    });

    // 5. Checkall in Pending Modal
    jQuery(document).on('change', '#checkall-pending_data', function () {
        var isChecked = jQuery(this).is(':checked');
        var $table = jQuery('#PendingCustomerDcTable');
        if (jQuery.fn.DataTable.isDataTable($table)) {
            var nodes = $table.DataTable().rows({ search: 'applied' }).nodes();
            jQuery('.pending-item-check', nodes).prop('checked', isChecked);
        } else {
            jQuery('.pending-item-check').prop('checked', isChecked);
        }
    });

    jQuery(document).on('change', '.pending-item-check', function () {
        updatePendingCheckboxes();
    });

    // 6. Import Selected Items from Pending Modal into Main Form Grid
    jQuery('#import_pending_btn').on('click', function () {
        var selectedChecks;
        var $table = jQuery('#PendingCustomerDcTable');
        if (jQuery.fn.DataTable.isDataTable($table)) {
            var nodes = $table.DataTable().rows({ search: 'applied' }).nodes();
            selectedChecks = jQuery('.pending-item-check:checked', nodes);
        } else {
            selectedChecks = jQuery('.pending-item-check:checked');
        }

        if (selectedChecks.length === 0) {
            toastr.error("Select At Least One Item.");
            return;
        }

        jQuery('#noDetails').remove();

        selectedChecks.each(function () {
            var mid = jQuery(this).val();
            var item = pending_inward_items_map[mid];
            if (!item) return;

            // Avoid duplicate material_inward_details_id row in grid
            if (jQuery(`#DCDetailTable tbody tr[data-mid-id="${item.material_inward_details_id}"]`).length === 0) {
                var inQty = parseInt(item.in_qty) || 0;
                var pendQty = parseInt(item.pend_qty) || 0;
                var rowHtml = `<tr data-mid-id="${item.material_inward_details_id}" data-is-report-done="${item.is_report_done !== undefined ? item.is_report_done : 1}" data-report-pending-msg="${item.report_pending_msg || ''}">
                    <td class="text-center">
                        <div class="dropdown">
                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' data-bs-boundary="window" aria-expanded="false">
                                <i class="ri-more-fill align-middle"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item remove-item-btn remove-row" href="javascript:void(0);">
                                        <i class="ri-delete-bin-fill align-bottom me-2 text-danger"></i> Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                    <td>${item.mi_number || ''}<input type="hidden" name="material_inward_details_id" value="${item.material_inward_details_id}"></td>
                    <td>${item.mi_date || ''}</td>
                    <td>${item.mi_challan_number || ''}</td>
                    <td>${item.mi_challan_date || ''}</td>
                    <td>${item.mi_po_number || ''}</td>
                    <td>${item.mi_po_date || ''}</td>
                    <td>${item.type_of_test || ''}</td>
                    <td>${item.type_of_job || ''}</td>
                    <td>${item.job_description || ''}</td>
                    <td>${item.part_no || ''}</td>
                    <td>${item.drg_no || ''}</td>
                    <td>${item.material_name || item.material || ''}</td>
                    <td>${item.mid_heat_no || ''}</td>
                    <td>${item.mid_rt_no || ''}</td>
                    <td>${item.mid_product_code || ''}</td>
                    <td>${inQty}</td>
                    <td><input type="hidden" class="pend_qty_val" value="${pendQty}">${pendQty}</td>
                    <td class="position-relative"><input type="text" class="form-control form-control-sm dc_qty_input isInteger" name="dc_qty" value="${pendQty}" max="${pendQty}" required><div class="invalid-tooltip" style="top: auto; bottom: 100%; margin-bottom: 2px; z-index: 1050;">Enter DC Qty.</div></td>
                    <td><input type="text" class="form-control form-control-sm remark_input" name="remark" value=""></td>
                </tr>`;
                jQuery('#DCDetailTable tbody').append(rowHtml);
            }
        });

        jQuery('#PendingInwardForCustomerDcModal').modal('hide');
        if (typeof recalculateTotalQty === 'function') {
            recalculateTotalQty();
        }
    });

    // 7. Recalculate Total Qty
    jQuery('#DCDetailTable').on('input', '.dc_qty_input', function () {
        recalculateTotalQty();
    });

    // 8. Delete Detail Row
    jQuery('#DCDetailTable tbody').on('click', '.remove-row', function () {
        removeCustomerDcRow(this);
    });

    // 8b. Dynamic DC Qty Input Change Handler -> Hide Tooltip
    jQuery('#DCDetailTable tbody').on('input change', '.dc_qty_input', function () {
        var val = jQuery(this).val();
        if (val !== undefined && val !== null && val.trim() !== '' && parseInt(val) > 0) {
            jQuery(this).removeClass('is-invalid').siblings('.invalid-tooltip, .invalid-feedback').css('display', 'none').hide();
        }
    });

    // 9. Recalculate Total Qty Function
    window.recalculateTotalQty = function () {
        var total = 0;
        var hasRows = false;
        jQuery('#DCDetailTable tbody tr[data-mid-id]').each(function () {
            hasRows = true;
            var val = parseInt(jQuery(this).find('.dc_qty_input').val()) || 0;
            total += val;
        });
        jQuery('#total_qty').val(hasRows && total > 0 ? total : '');
        checkCustomerReadonlyState();
    };

    // 10. Reset to ADD Mode
    function resetFormToAddMode() {
        deletedDetails = [];
        var form = document.getElementById("commonCustomerDCForm");
        if (form) {
            form._warning_confirmed = false;
            form.reset();
            form.classList.remove('was-validated');
        }
        jQuery('#commonCustomerDCForm #id').val('');
        jQuery('#DCDetailTable tbody').html('<tr id="noDetails"><td colspan="20" class="text-center" id="noDetailsCell">No Material Outward Details Added</td></tr>');
        jQuery('#total_qty').val('');
        jQuery('#pending_btn').prop('disabled', true);
        setSelect2Readonly('#CustomerDCNonReturnableModal #customer_id', false);
        jQuery('#CustomerDCNonReturnableModal #customer_id').removeClass('skip-tab');

        jQuery('#submitbtn').text('Submit');
        jQuery('#preview_btn').hide().attr('href', '').removeAttr('target');
        jQuery('#add_new').hide();
        jQuery('#commonCustomerDCForm').removeAttr('data-pdf-name');

        loadLatestSequence();
        loadPendingCustomers();
        loadLNRData();

        setTimeout(function () {
            jQuery('#customer_dc_non_returnable_sequence').focus();
        }, 150);
    }

    // 11. Load Sequence Number (Matches Purchase Order & Delivery Challan Customer System Standard)
    function loadLatestSequence() {
        jQuery.ajax({
            url: 'get-latest-dc-sequence-customer_dc_non_returnable',
            type: 'GET',
            headers: headerOpt,
            success: function (res) {
                if (res.response_code == 1) {
                    jQuery('#customer_dc_non_returnable_sequence').val(res.number).prop('readonly', false);
                    jQuery('#customer_dc_non_returnable_no').val(res.latest_no).prop({ tabindex: -1, readonly: true });
                }
            }
        });
    }

    // Sequence change duplication check
    jQuery('#commonCustomerDCForm').find('#customer_dc_non_returnable_sequence').on('change', function () {
        checkCustomerDCSequence();
    });

    function checkCustomerDCSequence() {
        let thisForm = jQuery('#commonCustomerDCForm');
        let val = thisForm.find('#customer_dc_non_returnable_sequence').val();

        if (val != "") {
            if (parseInt(val) > 0 == false) {
                toastr.error('Please Enter Valid DC No.');
                jQuery('#customer_dc_non_returnable_sequence').focus();
                jQuery('#customer_dc_non_returnable_sequence').val('');
            } else {
                jQuery('#customer_dc_non_returnable_sequence').addClass('file-loader');

                var urL = "check-customer_dc_non_returnable_number_duplication?for=add&customer_dc_non_returnable_sequence=" + val;
                var formId = thisForm.find('input[name="id"]').val();

                if (formId != undefined && formId != "") {
                    urL = "check-customer_dc_non_returnable_number_duplication?for=edit&customer_dc_non_returnable_sequence=" + val + "&id=" + formId;
                }

                jQuery.ajax({
                    url: urL,
                    type: 'GET',
                    headers: headerOpt,
                    dataType: 'json',
                    success: function (data) {
                        jQuery('#customer_dc_non_returnable_sequence').removeClass('file-loader');
                        if (data.response_code == 0) {
                            toastr.error(data.response_message);
                            jQuery('#customer_dc_non_returnable_sequence').val('');
                            const input = document.getElementById('customer_dc_non_returnable_sequence');
                            input?.focus();
                        } else {
                            if (data.latest_no) {
                                jQuery('#customer_dc_non_returnable_no').val(data.latest_no);
                            }
                            jQuery('#customer_dc_non_returnable_sequence').val(val);
                        }
                    },
                    error: function () {
                        jQuery('#customer_dc_non_returnable_sequence').removeClass('file-loader');
                    }
                });
            }
        }
    }

    // 12. Load Pending Customers
    function loadPendingCustomers() {
        return jQuery.ajax({
            url: 'get-pending-customers-customer_dc_non_returnable',
            type: 'GET',
            headers: headerOpt,
            success: function (res) {
                if (res.response_code == 1) {
                    var html = '<option value="">Select Customer</option>';
                    jQuery.each(res.customers, function (idx, cust) {
                        html += `<option value="${cust.id}">${cust.customer}</option>`;
                    });
                    jQuery('#customer_id').html(html).trigger('change.select2');
                }
            }
        });
    }

    // 13. Reset Button Actions
    jQuery('#resetbtn').on('click', function () {
        var formId = jQuery('#commonCustomerDCForm #id').val();
        if (!formId) {
            resetFormToAddMode();
        } else {
            fetchAndFillEditRecord(formId);
        }
    });

    jQuery('#add_new').on('click', function () {
        resetFormToAddMode();
    });

    // 14. Edit Record Handler
    jQuery('#dyntable tbody').on('click', '.edit-customer_dc_non_returnable', function () {
        var id = jQuery(this).data('id');
        fetchAndFillEditRecord(id);
    });

    function fetchAndFillEditRecord(id) {
        if (!id) return;
        deletedDetails = [];
        var form = document.getElementById("commonCustomerDCForm");
        if (form) form._warning_confirmed = false;

        jQuery('#CustomerDCNonReturnableModal').modal('show');
        jQuery('#submitbtn').text('Update');
        jQuery('#preview_btn').show();
        jQuery('#add_new').show();

        jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');

        jQuery.ajax({
            url: 'edit-customer_dc_non_returnable',
            type: 'GET',
            data: { id: id },
            headers: headerOpt,
            success: function (res) {
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                if (res.response_code == 1 && res.dc_data) {
                    var data = res.dc_data;
                    jQuery('#commonCustomerDCForm #id').val(data.customer_dc_non_returnable_id);
                    jQuery('#customer_dc_non_returnable_sequence').val(data.customer_dc_non_returnable_sequence);
                    jQuery('#customer_dc_non_returnable_no').val(data.customer_dc_non_returnable_no);
                    jQuery('#customer_dc_non_returnable_date').val(data.customer_dc_non_returnable_date);
                    jQuery('#mode_of_transport').val(data.mode_of_transport);
                    jQuery('#transporter').val(data.transporter);
                    jQuery('#vehicle_no').val(data.vehicle_no || data.vehical_no);
                    jQuery('#sp_note').val(data.sp_note);
                    if (data.prepared_by_user_id) {
                        jQuery('#prepared_by_user_id').val(data.prepared_by_user_id).trigger('change.select2');
                    }

                    loadPendingCustomers().done(function () {
                        var customerVal = data.customer_id;
                        var customerName = data.customer_name || data.customer;
                        if (customerName && customerVal && jQuery('#customer_id option[value="' + customerVal + '"]').length === 0) {
                            jQuery('#customer_id').append(new Option(customerName, customerVal, true, true)).trigger('change.select2');
                        }
                        jQuery('#customer_id').val(customerVal).trigger('change.select2');
                        jQuery('#submitbtn').text('Update');
                        if (res.url) {
                            jQuery('#preview_btn').attr('href', res.url).attr('target', '_blank').show();
                        } else {
                            jQuery('#preview_btn').hide();
                        }
                        jQuery('#add_new').show();
                        jQuery('#pending_btn').prop('disabled', true);
                        if (res.dc_data && res.dc_data.pdf_name) {
                            jQuery('#commonCustomerDCForm').attr('data-pdf-name', res.dc_data.pdf_name);
                        }
                    });

                    // Fill Details Grid
                    jQuery('#DCDetailTable tbody').empty();
                    if (res.dc_details_data && res.dc_details_data.length > 0) {
                        jQuery.each(res.dc_details_data, function (idx, item) {
                            var inQty = parseInt(item.in_qty) || 0;
                            var pendQty = parseInt(item.pend_qty) || 0;
                            var dcQty = parseInt(item.dc_qty) || 0;
                            var rowHtml = `<tr data-mid-id="${item.material_inward_details_id}" data-details-id="${item.customer_dc_non_returnable_details_id || ''}" data-is-report-done="${item.is_report_done !== undefined ? item.is_report_done : 1}" data-report-pending-msg="${item.report_pending_msg || ''}">
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' data-bs-boundary="window" aria-expanded="false">
                                            <i class="ri-more-fill align-middle"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item remove-item-btn remove-row" href="javascript:void(0);">
                                                    <i class="ri-delete-bin-fill align-bottom me-2 text-danger"></i> Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                                <td>${item.mi_number || ''}<input type="hidden" name="material_inward_details_id" value="${item.material_inward_details_id}"><input type="hidden" name="customer_dc_non_returnable_details_id" value="${item.customer_dc_non_returnable_details_id || ''}"></td>
                                <td>${item.mi_date || ''}</td>
                                <td>${item.mi_challan_number || ''}</td>
                                <td>${item.mi_challan_date || ''}</td>
                                <td>${item.mi_po_number || ''}</td>
                                <td>${item.mi_po_date || ''}</td>
                                <td>${item.type_of_test || ''}</td>
                                <td>${item.type_of_job || ''}</td>
                                <td>${item.job_description || ''}</td>
                                <td>${item.part_no || ''}</td>
                                <td>${item.drg_no || ''}</td>
                                <td>${item.material_name || item.material || ''}</td>
                                <td>${item.mid_heat_no || ''}</td>
                                <td>${item.mid_rt_no || ''}</td>
                                <td>${item.mid_product_code || ''}</td>
                                <td>${inQty}</td>
                                <td><input type="hidden" class="pend_qty_val" value="${pendQty}">${pendQty}</td>
                                <td class="position-relative"><input type="text"  class="form-control form-control-sm dc_qty_input isInteger" name="dc_qty" value="${dcQty}" max="${pendQty}" required><div class="invalid-tooltip" style="top: auto; bottom: 100%; margin-bottom: 2px; z-index: 1050;">Enter DC Qty.</div></td>
                                <td><input type="text" class="form-control form-control-sm remark_input" name="remark" value="${item.remark || ''}"></td>
                            </tr>`;
                            jQuery('#DCDetailTable tbody').append(rowHtml);
                        });
                    } else {
                        jQuery('#DCDetailTable tbody').html('<tr id="noDetails"><td colspan="20" class="text-center" id="noDetailsCell">No Material Outward Details Added</td></tr>');
                    }

                    recalculateTotalQty();
                } else {
                    toastr.error(res.response_message || 'Failed to load data.');
                }
            },
            error: function () {
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                toastr.error('Failed to load data.');
            }
        });
    }

    // 15. Submit / Update Form Handler
    jQuery('#commonCustomerDCForm').on('submit', function (e) {
        e.preventDefault();
        var form = this;

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return false;
        }

        // Custom Validation Messages
        var dcSeq = jQuery('#customer_dc_non_returnable_sequence').val().trim();
        var dcDate = jQuery('#customer_dc_non_returnable_date').val().trim();
        var customerId = jQuery('#customer_id').val();

        // Collect Grid Data & Check Pending Test Reports
        var detailsData = [];
        var invalidRow = false;
        var pendingReportMsg = '';

        jQuery('#DCDetailTable tbody tr').each(function () {
            if (jQuery(this).attr('id') === 'noDetails') return;

            var isReportDone = jQuery(this).attr('data-is-report-done');
            var reportMsg = jQuery(this).attr('data-report-pending-msg');
            if (isReportDone !== undefined && parseInt(isReportDone) === 0 && !pendingReportMsg) {
                pendingReportMsg = reportMsg || "Test Report is pending to be created for one or more material inward items.";
            }

            var mid_id = jQuery(this).find('input[name="material_inward_details_id"]').val();
            var detail_id = jQuery(this).attr('data-details-id') || jQuery(this).find('input[name="customer_dc_non_returnable_details_id"]').val() || '';
            var raw_dc_qty = jQuery(this).find('.dc_qty_input').val();
            var dc_qty = parseInt(raw_dc_qty);
            var pend_qty = parseInt(jQuery(this).find('.pend_qty_val').val()) || 0;
            var remark = jQuery(this).find('.remark_input').val() || '';

            if (raw_dc_qty === undefined || raw_dc_qty === null || raw_dc_qty.trim() === '' || isNaN(dc_qty)) {
                invalidRow = true;
                var $input = jQuery(this).find('.dc_qty_input');
                $input.addClass('is-invalid');
                $input.siblings('.invalid-tooltip').css('display', 'block').show().text("Enter DC Qty.");
                $input.focus().select();
                return false;
            }

            if (dc_qty <= 0) {
                invalidRow = true;
                toastr.error("Enter DC Qty. greater than 0.");
                jQuery(this).find('.dc_qty_input').focus().select();
                return false;
            }

            if (dc_qty > pend_qty) {
                invalidRow = true;
                toastr.error("DC Qty. Cannot Be Greater Than Pend. Qty. " + pend_qty + ".");
                jQuery(this).find('.dc_qty_input').focus().select();
                return false;
            }

            if (mid_id) {
                detailsData.push({
                    customer_dc_non_returnable_details_id: detail_id,
                    material_inward_details_id: mid_id,
                    dc_qty: dc_qty,
                    remark: remark,
                    mode: detail_id ? 'Update' : 'Insert'
                });
            }
        });

        if (invalidRow) return false;

        // Append deleted details
        if (deletedDetails && deletedDetails.length > 0) {
            jQuery.each(deletedDetails, function (idx, delItem) {
                detailsData.push(delItem);
            });
        }

        var activeDetailsCount = detailsData.filter(item => item.mode !== 'Delete').length;
        if (activeDetailsCount === 0) {
            toastr.error("Please Add At Least One Material Outward Details.");
            return;
        }

        var formId = jQuery('#commonCustomerDCForm #id').val();
        var isAddMode = !formId;

        // Show Warning Confirmation Popup if Test Report is Pending (Only during STORE mode)
        if (isAddMode && pendingReportMsg && !form._warning_confirmed) {
            toastWarningConfirm(pendingReportMsg + " Do you want to proceed?", function () {
                form._warning_confirmed = true;
                jQuery(form).submit();
            });
            return false;
        }

        form._warning_confirmed = false;

        var formUrl = formId ? "update-customer_dc_non_returnable" : "store-customer_dc_non_returnable";
        var activeBtn = jQuery('#submitbtn');

        // Spinner & Double-submit prevention
        activeBtn.prop('disabled', true);
        jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');

        var formData = new FormData(form);
        formData.append('dc_details_data', JSON.stringify(detailsData));

        jQuery.ajax({
            url: formUrl,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: headerOpt,
            success: function (res) {
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                activeBtn.prop('disabled', false);

                if (res.response_code == 1) {
                    var formIdnew = jQuery('#commonCustomerDCForm #id').val();
                    if (formIdnew != undefined && formIdnew != "") {
                        function updateNext() {
                            jQuery('#CustomerDCNonReturnableModal').modal('hide');
                            resetFormToAddMode();
                            table.ajax.reload(null, false);
                        }
                        if (res.url && res.url !== "") {
                            toastSuccessPreview("Record Updated.", res.url, updateNext);
                        } else {
                            toastSuccess("Record Updated.", updateNext);
                        }
                    } else {
                        function insertNext() {
                            resetFormToAddMode();
                            table.ajax.reload(null, false);
                        }
                        if (res.url && res.url !== "") {
                            toastSuccessPreview("Record Inserted.", res.url, insertNext);
                        } else {
                            toastSuccess("Record Inserted.", insertNext);
                        }
                    }
                } else {
                    toastr.error(res.response_message || "Operation failed.");
                }
            },
            error: function (jqXHR) {
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                activeBtn.prop('disabled', false);
                toastr.error("Something went wrong!");
            }
        });
    });

    // Preview Button Click Handler
    jQuery('#preview_btn').on('click', function () {
        var formId = jQuery('#commonCustomerDCForm #id').val();
        var pdfName = jQuery('#commonCustomerDCForm').attr('data-pdf-name');
        if (formId) {
            var encodedId = btoa(formId);
            var url = 'check-file_exists?id=' + encodedId + '&name=' + (pdfName || ('Customer_DC_Non_Returnable_' + formId)) + '&type=customer_dc_non_returnable';
            window.open(url, '_blank');
        }
    });
});

function removeCustomerDcRow(btn) {
    toastDelete("Are you sure you want to delete this Record?", function () {
        var row = jQuery(btn).closest('tr');
        var detailId = row.attr('data-details-id') || row.find('input[name="customer_dc_non_returnable_details_id"]').val();
        if (detailId) {
            deletedDetails.push({
                customer_dc_non_returnable_details_id: detailId,
                mode: 'Delete'
            });
        }
        row.remove();
        if (jQuery('#DCDetailTable tbody tr').length === 0) {
            jQuery('#DCDetailTable tbody').html('<tr id="noDetails"><td colspan="18" class="text-center" id="noDetailsCell">No Material Outward Details Added</td></tr>');
        }
        if (typeof window.recalculateTotalQty === 'function') {
            window.recalculateTotalQty();
        }
    });
}

function checkCustomerReadonlyState() {
    let rowCount = jQuery('#DCDetailTable tbody tr[data-mid-id]').length;
    if (rowCount > 0) {
        jQuery('#CustomerDCNonReturnableModal #customer_id').addClass('skip-tab');
        setSelect2Readonly('#CustomerDCNonReturnableModal #customer_id', true);
    } else {
        jQuery('#CustomerDCNonReturnableModal #customer_id').removeClass('skip-tab');
        setSelect2Readonly('#CustomerDCNonReturnableModal #customer_id', false);
    }
}

function updatePendingCheckboxes() {
    let addedMidIds = [];
    jQuery('#DCDetailTable tbody tr[data-mid-id]').each(function () {
        let midId = jQuery(this).attr('data-mid-id');
        if (midId) {
            addedMidIds.push(midId.toString());
        }
    });

    let allChecked = true;
    let checkboxes = jQuery('#PendingCustomerDcTable tbody .pending-item-check');
    if (checkboxes.length === 0) {
        allChecked = false;
    } else {
        checkboxes.each(function () {
            let item = jQuery(this).data('item');
            if (item && item.material_inward_details_id && addedMidIds.includes(item.material_inward_details_id.toString())) {
                jQuery(this).prop('checked', true);
            } else {
                allChecked = false;
            }
        });
    }
    jQuery('#checkall-pending_data').prop('checked', allChecked);
}

// 16. LNR Data Loading
function loadLNRData(customerId = '') {
    let formId = jQuery('#commonCustomerDCForm #id').val();
    if (formId && formId !== "") return; // Do not overwrite in Edit mode

    jQuery.ajax({
        url: "get-customer_dc_non_returnable_lnr_data",
        type: 'GET',
        headers: headerOpt,
        data: { customer_id: customerId },
        dataType: 'json',
        success: function (data) {
            if (data.response_code == 1 && data.lnr_data) {
                let lnr = data.lnr_data;
                if (lnr.mode_of_transport) jQuery('#mode_of_transport').val(lnr.mode_of_transport);
                if (lnr.transporter) jQuery('#transporter').val(lnr.transporter);
                if (lnr.vehicle_no || lnr.vehical_no) jQuery('#vehicle_no').val(lnr.vehicle_no || lnr.vehical_no);
                if (lnr.sp_note) jQuery('#sp_note').val(lnr.sp_note);
                if (lnr.prepared_by_user_id) jQuery('#prepared_by_user_id').val(lnr.prepared_by_user_id).trigger('change.select2');
            }
        }
    });
}

// 17. Suggestion List Helper Functions
function suggestVehicleNo(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#vehicle_no_list',
        url: 'customer_dc_non_returnable_vehicle_no-list',
        responseListKey: 'vehicleNoList'
    });
}

function suggestModeOfTransport(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#mode_of_transport_list',
        url: 'customer_dc_non_returnable_mode_of_transport-list',
        responseListKey: 'modeOfTransportList'
    });
}

function suggestSpNote(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#sp_note_list',
        url: 'customer_dc_non_returnable_sp_note-list',
        responseListKey: 'spNoteList'
    });
}

function suggestTransporter(e, element) {
    commonSuggestionAjax({
        inputElement: element,
        listSelector: '#transporter_list',
        url: 'customer_dc_non_returnable_transporter-list',
        responseListKey: 'transporterList'
    });
}

function focusFirstInput(containerSelector) {
    var $container = jQuery(containerSelector);
    var $inputs = $container.find('input, select, textarea').filter(':visible').not(':disabled');

    $inputs.each(function () {
        var $el = jQuery(this);
        if (!$el.prop('readonly') && $el.attr('tabindex') !== '-1') {
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('open');
            } else {
                $el.focus();
            }
            return false;
        }
    });
}

function toastWarningConfirm(message, onConfirm = null) {
    document.activeElement?.blur();

    var openModals = jQuery('.modal.show').length;
    var topZIndex = 1050 + (openModals * 15);

    Swal.fire({
        title: 'Confirmation',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        customClass: {
            confirmButton: 'btn btn-primary w-xs me-2 mt-2',
            cancelButton: 'btn btn-danger w-xs mt-2',
        },
        buttonsStyling: false,
        showCloseButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        focusConfirm: true,
        didOpen: () => {
            const swalContainer = document.querySelector('.swal2-container');
            if (swalContainer) {
                swalContainer.style.zIndex = topZIndex;
            }
        }
    }).then((res) => {
        if (res.isConfirmed && typeof onConfirm === "function") {
            onConfirm();
        }
    });
}



