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
        url: 'get-item_opening',
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

                        // <td><input type="text" class="io_opening_qty isNumberKey" data-old="${parseFloat(data.item_data[idx].io_opening_qty ?? 0).toFixed(3)}"
                        //      name="io_opening_qty" min="${parseFloat(data.item_data[idx].min ?? 0).toFixed(3)}" value="${parseFloat(data.item_data[idx].io_opening_qty ?? 0).toFixed(3)}" onblur="formatPoints(this,3)" ></td>
                        tblHtml += `
                                    <tr>
                                    <td><input type="hidden" name="io_id" value="${data.item_data[idx].io_id}"><input type="hidden" name="io_item_id" value="${data.item_data[idx].item_id}">${data.item_data[idx].item_name}</td>
                                    <td>${data.item_data[idx].item_group}</td>
                                    <td>${data.item_data[idx].item_type}</td>
                                    <td>
                                        <input type="text"
                                             class="form-control form-control-sm remove_filters_short_qty io_opening_qty d-inline-block ${data.item_data[idx].item_type === 'General' ? 'isNumberKey' : 'isNumberKeyNotDot'}"
                                             data-old="${data.item_data[idx].item_type === 'General' ? parseFloat(data.item_data[idx].io_opening_qty ?? 0).toFixed(3) : parseInt(data.item_data[idx].io_opening_qty ?? 0)}"
                                             name="io_opening_qty"
                                             min="${data.item_data[idx].item_type === 'General' ? parseFloat(data.item_data[idx].min ?? 0).toFixed(3) : parseInt(data.item_data[idx].min ?? 0)}"
                                             value="${data.item_data[idx].item_type === 'General' ? parseFloat(data.item_data[idx].io_opening_qty ?? 0).toFixed(3) : parseInt(data.item_data[idx].io_opening_qty ?? 0)}"
                                             onblur="${data.item_data[idx].item_type === 'General' ? 'formatPoints(this,3)' : ''}"
                                        >
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm  io_opening_amount remove_filters_short_qty isNumberKey d-inline-block"  data-old="${parseFloat(data.item_data[idx].io_opening_amount ?? 0).toFixed(2)}"
                                             name="io_opening_amount" value="${parseFloat(data.item_data[idx].io_opening_amount ?? 0).toFixed(2)}" onblur="formatPoints(this,2)" >
                                    </td>
                                    <td>${parseFloat(data.item_data[idx].io_stock_qty ?? 0).toFixed(3)}</td>
                                    <td>${data.item_data[idx].unit}</td>
                                    </tr>`;

                        // <td><input type="hidden" name="io_item_id" value="${data.item_data[idx].item_id}">${data.item_data[idx].item_code}
                        // </td>
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
            var errMessage = JSON.parse(jqXHR.responseText);
            if (jqXHR.status == 401) {
                toastr.error(jqXHR.statusText);
            } else {
                toastr.error('Something went wrong!');
                console.log(JSON.parse(jqXHR.responseText));

            }
        }
    });
}




// Main form submit Stary
$('#commonItemOpening').on('submit', function (e) {

    e.preventDefault();
    let form = this;

    let isValid = true;

    jQuery('#item_opening_table tbody tr').each(function () {

        var openingQty = parseFloat($(this).find('input[name="io_opening_qty"]').val()) || 0;
        var openingAmount = parseFloat($(this).find('input[name="io_opening_amount"]').val()) || 0;
        var minOpeningQty = parseFloat($(this).find('input[name="io_opening_qty"]').attr('min'));

        // Check if the openingQty is less than the minimum allowed value
        if (openingQty < minOpeningQty) {
            toastr.error('Opening Quantity must be at least ' + parseFloat(minOpeningQty).toFixed(3) + '.');
            isValid = false;
            return false; // Break the loop on error
        }



        // if (openingQty > 0 && openingAmount <= 0) {
        //     toastr.error('Enter Opening Amount.');
        //     isValid = false;
        //     return false; // break loop
        // }

        // if (openingAmount > 0 && openingQty <= 0) {
        //     toastr.error('Enter Opening Qty.');
        //     isValid = false;
        //     return false;
        // }
    });

    if (!isValid) {
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#commonItemOpening').find('#submitbtn').prop('disabled', false);
        return;
    }

    var formId = jQuery('#commonItemOpening').find('#id').val();
    var formUrl = "store-item_opening";
    var data = new FormData(form);

    item_opening_data = [];
    var index = 0;
    // jQuery('#item_opening_table tbody tr').each(function (e) {
    //     var item_id = jQuery(this).find('input[name="item_id[]"]');
    //     if (jQuery(item_id).is(':checked')) {
    //         item_id = jQuery(item_id).val();
    //         io_opening_qty = jQuery(this).find('input[name="io_opening_qty"]').val();
    //         io_stock_qty = jQuery(this).find('input[name="io_stock_qty"]').val();
    //         item_opening_data[index] = { 'item_id': item_id, 
    //                                      'io_opening_qty': io_opening_qty,
    //                                      'io_stock_qty' : io_stock_qty
    //                                    };
    //         index++;
    //     }
    // });

    jQuery('#item_opening_table tbody tr').each(function () {

        var item_id = jQuery(this).find('input[name="io_item_id"]').val();
        var io_opening_qty = jQuery(this).find('input[name="io_opening_qty"]').val();
        var io_stock_qty = jQuery(this).find('input[name="io_stock_qty"]').val();
        var io_opening_amount = jQuery(this).find('input[name="io_opening_amount"]').val();
        var io_id = jQuery(this).find('input[name="io_id"]').val();
        var isNew = !io_id || io_id === "null";
        // OPTIONAL: skip rows with zero opening qty
        if (isNew && parseFloat(io_opening_qty) > 0) {

            item_opening_data.push({

                item_id: item_id,
                io_opening_qty: io_opening_qty,
                io_stock_qty: io_stock_qty,
                io_opening_amount: io_opening_amount
            });
        }
        if (!isNew) {
            item_opening_data.push({
                io_id: io_id,
                item_id: item_id,
                io_opening_qty: io_opening_qty,
                io_stock_qty: io_stock_qty,
                io_opening_amount: io_opening_amount
            });
        }
    });


    jQuery('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    jQuery('#commonItemOpening').find('#submitbtn').prop('disabled', true);

    if (item_opening_data.length > 0 && !jQuery.isEmptyObject(item_opening_data)) {
        var data = new FormData(form);
        data.append('item_opening_data', JSON.stringify(item_opening_data ?? []));
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
                        jQuery('#commonItemOpening').find('#submitbtn').prop('disabled', false);
                    }
                    else if (formId == undefined || formId == "") {
                        function nextFn() {
                            document.getElementById("commonItemOpening").reset();
                            const form = document.getElementById("commonItemOpening");
                            if (form) {
                                form.classList.remove('was-validated');
                            }
                            jQuery('#item_opening_table tbody').empty();
                            getItems();
                            $('#item_opening_table .io_opening_qty').change();
                        }
                        toastSuccess(data.response_message, nextFn);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#commonItemOpening').find('#submitbtn').prop('disabled', false);
                    }
                    else {
                        toastr.error(data.response_message);
                        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                        jQuery('#commonItemOpening').find('#submitbtn').prop('disabled', false);
                    }
                } else {
                    toastr.error(data.response_message);
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#commonItemOpening').find('#submitbtn').prop('disabled', false);
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
                    jQuery('#commonItemOpening').find('#submitbtn').prop('disabled', false);
                } else {
                    toastr.error('Something went wrong. Please try again.');
                    jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
                    jQuery('#commonItemOpening').find('#submitbtn').prop('disabled', false);
                }
            }
        });
    } else {
        toastr.error('Please Add At Least One');
        jQuery('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
        jQuery('#commonItemOpening').find('#submitbtn').prop('disabled', false);
    }
});

// $('#item_opening_table').on('change', '.io_opening_qty', function () {
//     updateStockQtyForRow(this);
// });

// function updateStockQtyForRow(input) {

//     var row = $(input).closest('tr');

//     var newOpening = parseFloat($(input).val()) || 0;
//     var oldOpening = parseFloat($(input).data('old')) || 0;

//     var stockInput = row.find('.io_stock_qty');
//     var currentStock = parseFloat(stockInput.val()) || 0;

//     // ADD ONLY DIFFERENCE
//     var diff = newOpening - oldOpening;
//     var newStock = currentStock + diff;

//     stockInput.val(newStock.toFixed(3));
//     row.find('.io_stock_qty_display').html(newStock.toFixed(3));

//     // Save new opening for next change
//     $(input).data('old', newOpening);
// }

// $('#item_opening_table').on('change', '.io_opening_qty', function () {
//     updateStockQtyForRow(this);
// });

// function updateStockQtyForRow(input) {

//     var row = $(input).closest('tr');

//     var newOpening = parseFloat($(input).val()) || 0;
//     var oldOpening = parseFloat($(input).data('old')) || 0;

//     var stockInput = row.find('.io_stock_qty');
//     var currentStock = parseFloat(stockInput.val()) || 0;

//     var diff = newOpening - oldOpening;
//     var newStock = currentStock + diff;


//     // 🚨 INVALID STOCK
//     // if (newStock < 0) {

//     //     toastr.error('Invalid Opening stock! Stock Quantity cannot be negative.');

//     //     // FULL rollback
//     //     $(input).val(oldOpening.toFixed(3));
//     //     $(input).data('old', oldOpening);   // 🔥 THIS WAS MISSING
//     //     stockInput.val(currentStock.toFixed(3));
//     //     row.find('.io_stock_qty_display').html(currentStock.toFixed(3));
//     //     $(input).prop('readonly', true);
//     //     row.find('input[type="checkbox"]').prop('checked', false);
//     //     return;
//     // }

//     if (newStock < 0) {

//         toastr.error('Invalid Opening stock! Stock Quantity cannot be negative.');

//         // rollback opening
//         $(input).val(oldOpening.toFixed(3));
//         $(input).data('old', oldOpening);

//         // rollback stock to BASE STOCK
//         stockInput.val(basestock.toFixed(3));
//         row.find('.io_stock_qty_display').html(basestock.toFixed(3));

//         // make opening readonly
//         $(input).prop('readonly', true);

//         // uncheck checkbox
//         row.find('input[type="checkbox"]').prop('checked', false);

//         return;
//     }


//     // Valid update
//     stockInput.val(newStock.toFixed(3));
//     row.find('.io_stock_qty_display').html(newStock.toFixed(3));

//     // Save opening
//     $(input).data('old', newOpening);


// }


function manageQtyfield($this) {
    var oaQtyField = jQuery($this).parent('td').parent('tr').find('input[name="io_opening_qty"]');
    if (jQuery(oaQtyField).prop('readonly')) {
        jQuery(oaQtyField).prop('readonly', false);
    } else {
        jQuery(oaQtyField).prop('readonly', true);
    }
}

