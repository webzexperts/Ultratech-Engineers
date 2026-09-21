jQuery('#resetbtn').on('click', function () {
    var formId = jQuery('#commonItemOpening').find('#id').val();
    if (!formId) {
        document.getElementById("commonItemOpening").reset();
        const form = document.getElementById("commonItemOpening");
        if (form) {
            form.classList.remove('was-validated');
        }
        getItems();
    }
});

item_opening_data = [];
jQuery(document).ready(function () {
    getItems();
})

function getItems() {
    jQuery.ajax({
        url: 'get-item_opening_prod_area',
        type: 'GET',
        headers: headerOpt,
        dataType: 'json',
        processData: false,
        success: function (data) {
            var tblHtml = ``;
            if (data.response_code == 1 && data.item_data.length > 0) {

                if (data.item_data.length > 0 && !jQuery.isEmptyObject(data.item_data)) {
                    found = 1;

                    for (let idx in data.item_data) {
                        tblHtml += `
                                    <tr>
                                    <td>
                                        <input type="hidden" name="item_opening_prod_area_id" value="${data.item_data[idx].item_opening_prod_area_id}">
                                        <input type="hidden" name="io_item_id" value="${data.item_data[idx].item_id}">
                                        ${data.item_data[idx].item_name}
                                    </td>
                                    <td>${data.item_data[idx].item_group}</td>
                                    <!-- <td>Industrial X-Ray Films</td> -->
                                    <td>
                                        <input type="text"
                                             class="form-control form-control-sm remove_filters_short_qty io_opening_qty d-inline-block isNumberKeyNotDot"
                                             data-old="${parseInt(data.item_data[idx].opening_sq_in ?? 0)}"
                                             name="io_opening_qty"
                                             min="0"
                                             value="${parseInt(data.item_data[idx].opening_sq_in ?? 0)}"
                                        >
                                    </td>
                                    <td>${parseInt(data.item_data[idx].stock_sq_in ?? 0)}</td>
                                    <td>SQIN</td>
                                    </tr>`;
                    }

                } else {
                    tblHtml += `<tr class="centeralign" id="noItems">
                                    <td colspan="6">No Items Available</td>
                                </tr>`;
                }

                var $table = jQuery('#item_opening_table');
                if (jQuery.fn.DataTable.isDataTable($table)) {
                    $table.DataTable().clear().destroy();
                }
                jQuery('#item_opening_table tbody').empty().append(tblHtml);

                var $new = $table.DataTable({
                    paging: true,
                    searching: true,
                    "oLanguage": {
                        "sSearch": "Search :"
                    },
                    dom: 'lfrtip',
                });

            } else {
                //toastr.error(data.response_message);
            }
        },

        error: function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.status == 401) {
                toastr.error(jqXHR.statusText);
            } else {
                toastr.error('Something went wrong!');
            }
        }
    });
}

// Main form submit
$('#commonItemOpening').on('submit', function (e) {
    e.preventDefault();
    let form = this;
    let isValid = true;

    jQuery('#item_opening_table tbody tr').each(function () {
        var openingQty = parseInt($(this).find('input[name="io_opening_qty"]').val()) || 0;
        var minOpeningQty = parseInt($(this).find('input[name="io_opening_qty"]').attr('min')) || 0;

        if (openingQty < minOpeningQty) {
            toastr.error('Opening Quantity must be at least ' + minOpeningQty + '.');
            isValid = false;
            return false;
        }
    });

    if (!isValid) {
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#commonItemOpening').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#commonItemOpening').find('#id').val();
    var formUrl = "store-item_opening_prod_area";

    item_opening_data = [];
    jQuery('#item_opening_table tbody tr').each(function () {
        var item_id = jQuery(this).find('input[name="io_item_id"]').val();
        var io_opening_qty = jQuery(this).find('input[name="io_opening_qty"]').val();
        var item_opening_prod_area_id = jQuery(this).find('input[name="item_opening_prod_area_id"]').val();
        var isNew = !item_opening_prod_area_id || item_opening_prod_area_id === "null" || item_opening_prod_area_id === "undefined";

        if (isNew && parseInt(io_opening_qty) > 0) {
            item_opening_data.push({
                item_id: item_id,
                io_opening_qty: io_opening_qty
            });
        }
        if (!isNew) {
            item_opening_data.push({
                item_opening_prod_area_id: item_opening_prod_area_id,
                item_id: item_id,
                io_opening_qty: io_opening_qty
            });
        }
    });

    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#commonItemOpening').find('#submitbtn').prop('disabled', true);

    if (item_opening_data.length > 0) {
        var data = new FormData(form);
        data.append('item_opening_data', JSON.stringify(item_opening_data));
        jQuery.ajax({
            type: 'POST',
            url: formUrl,
            data: data,
            contentType: false,
            processData: false,
            success: function (data) {
                if (data.response_code == 1) {
                    function nextFn() {
                        document.getElementById("commonItemOpening").reset();
                        const form = document.getElementById("commonItemOpening");
                        if (form) {
                            form.classList.remove('was-validated');
                        }
                        jQuery('#item_opening_table tbody').empty();
                        getItems();
                    }
                    toastSuccess(data.response_message, nextFn);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#commonItemOpening').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#commonItemOpening').find('#submitbtn').prop('disabled', false);
                }
            },
            error: function (xhr) {
                toastr.error('Something went wrong!');
                jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                jQuery('#commonItemOpening').find('#submitbtn').prop('disabled', false);
            }
        });
    } else {
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#commonItemOpening').find('#submitbtn').prop('disabled', false);
    }
});
