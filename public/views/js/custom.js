// document.addEventListener('input', function (event) {

//     const field = event.target;

//     // Allowed types
//     const editableTypes = ['text', 'email', 'search', 'password', 'tel', 'url'];

//     // Check if field is input with supported type OR a textarea
//     const isTextField =
//         (field.tagName === 'INPUT' && editableTypes.includes(field.type)) ||
//         field.tagName === 'TEXTAREA';

//     const isExcluded = field.classList.contains('input-lower-case');

//     if (isTextField && !isExcluded) {
//         // Store cursor position
//         const cursorPosition = field.selectionStart;
//         // Uppercase conversion
//         field.value = field.value.toUpperCase();
//         // Restore cursor ONLY if input supports it
//         if (typeof field.setSelectionRange === 'function') {
//             field.setSelectionRange(cursorPosition, cursorPosition);
//         }
//     }
// });


let startDate = new Date(jQuery('#def_year_startdate').val());

let endDate = new Date(jQuery('#def_year_enddate').val());

var headerOpt = { 'X-CSRF-TOKEN': jQuery('input[name="_token"]').val() };

// old working code start
// function toastSuccess(msg, callNext = null) {
//     // Force blur from whatever was focused (main modal input / button)
//     document.activeElement?.blur();

//     Swal.fire({
//         text: msg,
//         icon: 'success',
//         customClass: {
//             confirmButton: 'btn btn-primary w-xs me-2 mt-2',
//         },
//         buttonsStyling: false,
//         showCloseButton: true,
//         allowOutsideClick: false,
//         allowEscapeKey: false,
//         focusConfirm: false,

//         didOpen: () => {
//             // Disable Bootstrap modal ENTER handling
//             $('.modal').attr('data-bs-keyboard', 'false');

//             // Focus the confirm button so ENTER works
//             setTimeout(() => {
//                 const btn = Swal.getConfirmButton();
//                 if (btn) btn.focus();
//             }, 50);

//             // Add global keydown handler for ENTER
//             document.addEventListener('keydown', handleEnterKey);
//         },

//         willClose: () => {
//             // Re-enable main modal keyboard behavior
//             $('.modal').attr('data-bs-keyboard', 'true');

//             // Remove ENTER handler
//             document.removeEventListener('keydown', handleEnterKey);
//         }
//     }).then(() => {
//         if (callNext) callNext();
//     });


//     // ---------------------------
//     // ENTER KEY HANDLER
//     // ---------------------------
//     function handleEnterKey(e) {
//         if (e.key === "Enter" || e.key === " " || e.code === "Space") {
//             e.preventDefault();
//             e.stopPropagation();

//             const btn = Swal.getConfirmButton();
//             if (btn) btn.click(); // This closes the Swal
//         }
//     }
// }
// old working code end

// new add code last modal open and z index and add data after success response proper set on z index start
// function toastSuccess(msg, callNext = null) {
//     document.activeElement?.blur();

//     const openModals = jQuery('.modal.show').length;
//     const topZIndex = 1050 + (openModals * 15);

//     Swal.fire({
//         text: msg,
//         icon: 'success',
//         customClass: {
//             confirmButton: 'btn btn-primary w-xs me-2 mt-2',
//             container: 'top-most-swal'
//         },
//         didOpen: (el) => {
//             $(el).closest('.swal2-container').css('z-index', topZIndex + 100);
//             $('.modal').attr('data-bs-keyboard', 'false');
//             setTimeout(() => {
//                 const btn = Swal.getConfirmButton();
//                 if (btn) btn.focus();
//             }, 50);
//             document.addEventListener('keydown', handleEnterKey);
//         },
//         buttonsStyling: false,
//         showCloseButton: true,
//         allowOutsideClick: false,
//         allowEscapeKey: false,
//         focusConfirm: false,
//         willClose: () => {
//             $('.modal').attr('data-bs-keyboard', 'true');
//             document.removeEventListener('keydown', handleEnterKey);
//         }
//     }).then(() => {
//         if (callNext) callNext();
//     });

//     function handleEnterKey(e) {
//         if (e.key === "Enter" || e.key === " " || e.code === "Space") {
//             e.preventDefault();
//             e.stopPropagation();
//             const btn = Swal.getConfirmButton();
//             if (btn) btn.click();
//         }
//     }
// }

function toastSuccess(msg, callNext = null) {
    document.activeElement?.blur();

    const openModals = jQuery('.modal.show').length;
    const topZIndex = 1050 + (openModals * 15);

    Swal.fire({
        text: msg,
        icon: 'success',
        customClass: {
            confirmButton: 'btn btn-primary w-xs me-2 mt-2',
            container: 'top-most-swal'
        },
        didOpen: (el) => {
            $(el).closest('.swal2-container').css('z-index', topZIndex + 100);
            $('.modal').attr('data-bs-keyboard', 'false');
            setTimeout(() => {
                const btn = Swal.getConfirmButton();
                if (btn) btn.focus();
            }, 50);
            document.addEventListener('keydown', handleEnterKey);
            document.addEventListener('mousedown', handleEnterKey);
        },
        buttonsStyling: false,
        showCloseButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        focusConfirm: false,
        willClose: () => {
            $('.modal').attr('data-bs-keyboard', 'true');
            document.removeEventListener('keydown', handleEnterKey);
            document.removeEventListener('mousedown', handleEnterKey);
        }
    }).then(() => {
        if (callNext) callNext();
    });

    function handleEnterKey(e) {
        if (e.type === "mousedown" || e.key === "Enter" || e.key === " " || e.code === "Space") {
            e.preventDefault();
            e.stopPropagation();
            const btn = Swal.getConfirmButton();
            if (btn) btn.click();
        }
    }
}
// function toastSuccessPreview(msg, previewUrl = null, callNext = null) {
//     document.activeElement?.blur();
//     Swal.fire({
//         text: msg,
//         icon: 'success',
//         showCancelButton: true,
//         confirmButtonText: 'OK',
//         cancelButtonText: 'Preview',

//         customClass: {
//             confirmButton: 'btn btn-primary w-xs me-2 mt-2',
//             cancelButton: 'btn btn-secondary w-xs mt-2'
//         },

//         buttonsStyling: false,
//         showCloseButton: true,
//         allowOutsideClick: false,
//         allowEscapeKey: false,
//         focusConfirm: false,

//         didOpen: (el) => {

//             $(document).off('focusin.bs.modal');
//             $('.modal.show').css('visibility', 'hidden');
//             $(':focus').blur();
//             document.activeElement?.blur();
//             const confirmBtn = Swal.getConfirmButton();
//             const forceFocus = () => confirmBtn?.focus();
//             setTimeout(forceFocus, 0);
//             setTimeout(forceFocus, 50);
//             setTimeout(forceFocus, 150);

//             function handleKeys(e) {
//                 if (e.key === "Enter" || e.key === " " || e.code === "Space") {
//                     e.preventDefault();
//                     e.stopPropagation();
//                     Swal.clickConfirm();
//                 }
//             }

//             document.addEventListener('keydown', handleKeys);
//             el._handleKeys = handleKeys;
//         },

//         willClose: (el) => {
//             $('.modal.show').css('visibility', 'visible');
//             $(document).on('focusin.bs.modal');
//             if (el._handleKeys) {
//                 document.removeEventListener('keydown', el._handleKeys);
//             }
//         }

//     }).then((result) => {

//         if (result.isConfirmed) {
//             callNext && callNext();
//         }
//         if (result.dismiss === Swal.DismissReason.cancel) {
//             if (previewUrl) {
//                 window.open(encodeURI(previewUrl), '_blank');
//             }
//         }

//     });
// }
// end

// function toastSuccess(msg, callNext = null) {
//     document.activeElement?.blur();
//     const openModals = jQuery('.modal.show').length;
//     const swalZIndex = 1050 + (openModals * 10) + 20;
//     Swal.fire({
//         text: msg,
//         icon: 'success',
//         customClass: {
//             confirmButton: 'btn btn-primary w-xs me-2 mt-2',
//             popup: 'swal-top-most'
//         },
//         buttonsStyling: false,
//         showCloseButton: true,
//         allowOutsideClick: false,
//         allowEscapeKey: false,
//         focusConfirm: false,
//         didOpen: () => {
//             const swalContainer = document.querySelector('.swal2-container');
//             if (swalContainer) {
//                 swalContainer.style.zIndex = swalZIndex;
//             }
//             $('.modal').attr('data-bs-keyboard', 'false');
//             setTimeout(() => {
//                 const btn = Swal.getConfirmButton();
//                 if (btn) btn.focus();
//             }, 50);
//             document.addEventListener('keydown', handleEnterKey);
//         },
//         willClose: () => {
//             $('.modal').attr('data-bs-keyboard', 'true');
//             document.removeEventListener('keydown', handleEnterKey);
//         }
//     }).then(() => {
//         if (callNext) callNext();
//     });

//     function handleEnterKey(e) {
//         if (e.key === "Enter" || e.key === " " || e.code === "Space") {
//             e.preventDefault();
//             e.stopPropagation();
//             const btn = Swal.getConfirmButton();
//             if (btn) btn.click();
//         }
//     }
// }

function toastSuccessPreview(msg, previewUrl = null, callNext = null) {
    document.activeElement?.blur();

    const openModals = jQuery('.modal.show').length;
    const topZIndex = 1050 + (openModals * 15);

    let activeSwalButton = 'confirm'; // default selected button

    Swal.fire({
        text: msg,
        icon: 'success',
        showCancelButton: true,
        confirmButtonText: 'OK',
        cancelButtonText: 'Preview',
        customClass: {
            confirmButton: 'btn btn-primary w-xs me-2 mt-2 swal-btn-focus',
            cancelButton: 'btn btn-secondary w-xs mt-2',
            container: 'top-most-swal'
        },
        buttonsStyling: false,
        showCloseButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        focusConfirm: false,
        focusCancel: false,
        returnFocus: false,

        didOpen: (el) => {
            $(el).closest('.swal2-container').css('z-index', topZIndex + 100);
            $('.modal').attr('data-bs-keyboard', 'false');

            applyFakeFocus();

            document.addEventListener('keydown', handlePreviewKeys, true);
        },

        willClose: () => {
            $('.modal').attr('data-bs-keyboard', 'true');
            document.removeEventListener('keydown', handlePreviewKeys, true);
        }

    }).then((result) => {

        if (result.isConfirmed) {
            if (callNext) callNext();
        }

        if (result.dismiss === Swal.DismissReason.cancel) {
            if (previewUrl) {
                window.open(encodeURI(previewUrl), '_blank');
            }
        }
        if (callNext) callNext();

    });

    function applyFakeFocus() {
        const confirmBtn = Swal.getConfirmButton();
        const cancelBtn = Swal.getCancelButton();

        confirmBtn?.classList.remove('swal-btn-focus');
        cancelBtn?.classList.remove('swal-btn-focus');

        if (activeSwalButton === 'confirm') {
            confirmBtn?.classList.add('swal-btn-focus');
        } else {
            cancelBtn?.classList.add('swal-btn-focus');
        }
    }

    function handlePreviewKeys(e) {
        const confirmBtn = Swal.getConfirmButton();
        const cancelBtn = Swal.getCancelButton();

        if (!Swal.isVisible()) return;

        // ENTER
        if (e.key === "Enter") {
            e.preventDefault();
            e.stopPropagation();

            if (activeSwalButton === 'cancel') {
                cancelBtn?.click();
            } else {
                confirmBtn?.click();
            }
        }

        // SPACE
        if (e.code === "Space" || e.key === " ") {
            e.preventDefault();
            e.stopPropagation();

            if (activeSwalButton === 'cancel') {
                cancelBtn?.click();
            } else {
                confirmBtn?.click();
            }
        }

        // TAB
        if (e.key === "Tab") {
            e.preventDefault();
            e.stopPropagation();

            activeSwalButton = (activeSwalButton === 'confirm') ? 'cancel' : 'confirm';
            applyFakeFocus();
        }

        // ARROWS
        if (e.key === "ArrowRight") {
            e.preventDefault();
            e.stopPropagation();

            activeSwalButton = 'cancel';
            applyFakeFocus();
        }

        if (e.key === "ArrowLeft") {
            e.preventDefault();
            e.stopPropagation();

            activeSwalButton = 'confirm';
            applyFakeFocus();
        }
    }
}


function toastConfirm(message, onConfirm = null) {

    document.activeElement?.blur();

    Swal.fire({

        title: 'Are you sure?',
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
            $('.modal').attr('data-bs-keyboard', 'false');

            setTimeout(() => {
                const btn = Swal.getConfirmButton();
                if (btn) btn.focus();
            }, 50);

            document.addEventListener('keydown', handleEnterConfirm);
            document.addEventListener('mousedown', handleEnterConfirm);
        },

        willClose: () => {
            $('.modal').attr('data-bs-keyboard', 'true');
            document.removeEventListener('keydown', handleEnterConfirm);
            document.removeEventListener('mousedown', handleEnterConfirm);
        }

    }).then(res => {
        if (res.isConfirmed && typeof onConfirm === 'function') {
            onConfirm();
        }
    });

    function handleEnterConfirm(e) {
        if (e.key === "Enter" || e.key === " " || e.code === "Space" || e.code === "mousedown") {
            e.preventDefault();
            e.stopPropagation();
            Swal.clickConfirm();
        }
    }
}

function toastAlert(errorMsg) {
    Swal.fire({
        text: errorMsg,
        customClass: {
            confirmButton: 'btn btn-primary w-xs mt-2',
        },
        buttonsStyling: false,
        showCloseButton: true,
        confirmButtonText: "OK",
        focusConfirm: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
    })
}

function formatPoints($this, num) {

    if (jQuery($this).val() != "" && jQuery($this).val() > -1) {

        let formatVal = Number(jQuery($this).val()).toFixed(num);

        jQuery($this).val(formatVal)

    } else {

        jQuery($this).val('');

    }

}
jQuery(document).on('keypress', '.isNumberKey', function (evt) {

    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode != 46 && charCode > 31
        && (charCode < 48 || charCode > 57))
        return false;

    return true;

});

// for int valuse
// jQuery(document).on('keypress', '.isInteger', function (evt) {
//     var charCode = (evt.which) ? evt.which : evt.keyCode;

//     // Allow only digits (0–9)
//     if (charCode > 31 && (charCode < 48 || charCode > 57)) {
//         return false;
//     }

//     return true;
// });
// Prevent paste of float/special characters
jQuery(document).on('input', '.isInteger', function () {
    // Keep only numbers
    this.value = this.value.replace(/[^0-9]/g, '');
});
(function ($) {

    function updateSubmitButton(modal) {
        // Find submit button across all modal conventions
        let submitBtn = modal.find('#submitbtn, #detailSubmitBtn, #submitDetailRowBtn, #saveObsDetailEditBtn, #saveObsSubDetailBtn, #submitDetailsRowBtn, #submitMaterialRowBtn, #submitChemicalRowBtn, #submitEquipmentRowBtn, #submitProbeRowBtn, #submitCopyMaterialInwardDetailsBtn, #add_details_btn');
        if (!submitBtn.length) {
            submitBtn = modal.find('.modal-footer button[type="submit"], .modal-footer .btn-primary').not('[data-bs-dismiss="modal"]');
        }
        if (!submitBtn.length) return;

        let formTypeField = modal.find('input[name="form_type"], input[name="sub_form_type"], #form_type, #det_form_type, #chem_form_type, #mat_form_type, #eq_form_type, #pb_form_type');
        let idField = modal.find('input[name="id"]');

        // Check for Detail Modals (which have form_type field and are not main forms with id)
        if (formTypeField.length && (!idField.length || (modal.attr('id') && modal.attr('id').toLowerCase().includes('detail')))) {
            let formType = (formTypeField.val() || "").trim().toLowerCase();
            if (formType === "edit") {
                submitBtn.text('Edit');
            } else {
                submitBtn.text('Add');
            }
            return;
        }

        // Special case for Observation Sheet Detail Edit Modal
        if (modal.attr('id') === 'ObservationSheetDetailEditModal') {
            submitBtn.text('Edit');
            return;
        }

        // Check for Main / Master Modals (which have id field)
        if (idField.length) {
            let value = idField.val();
            if (value && value.trim() !== "") {
                submitBtn.text('Update');
            } else {
                submitBtn.text('Submit');
            }
        }
    }

    $(document).on('shown.bs.modal', '.modal', function () {
        let modal = $(this);

        setTimeout(function () {
            updateSubmitButton(modal);
        }, 50);
    });

    $(document).on('input change', '.modal input[name="id"], .modal input[name="form_type"], .modal input[name="sub_form_type"], .modal #form_type, .modal #det_form_type, .modal #chem_form_type, .modal #mat_form_type, .modal #eq_form_type, .modal #pb_form_type', function () {
        let modal = $(this).closest('.modal');
        updateSubmitButton(modal);
    });

    setInterval(function () {
        $('.modal:visible').each(function () {
            updateSubmitButton($(this));
        });
    }, 300);

})(jQuery);
jQuery(document).on('input', '.isNumberKeyNotDot', function () {
    this.value = this.value.replace(/[^0-9]/g, '');
});
jQuery(document).on('keypress', '.isNumberKeyNotZero', function (evt) {

    var charCode = (evt.which) ? evt.which : evt.keyCode;
    var value = jQuery(this).val();
    if (charCode < 48 || charCode > 57) {
        return false;
    }
    if (value.length === 0 && charCode === 48) {
        return false;
    }
    return true;
});

jQuery(document).on('click', '[data-bs-target]', function () {
    var pageName = jQuery('#pageName').val();
    // Agar tab che to skip
    if (jQuery(this).attr('data-bs-toggle') === 'pill') {
        return;
    }

    if (pageName != 'user_acess') {
        let target = jQuery(this).data('bs-target');
        jQuery(target).modal('show');
    }
});

jQuery(document).on('show.bs.modal', '.modal', function () {
    const open = jQuery('.modal.show').length;
    const z = 1050 + (open * 10);

    jQuery(this).css('z-index', z);

    setTimeout(() => {
        jQuery('.modal-backdrop')
            .not('.modal-stack')
            .first()
            .css('z-index', z - 1)
            .addClass('modal-stack');
    }, 0);
});


// jQuery(document).on('shown.bs.modal', '.modal', function () {
//     const $modal = jQuery(this);

//     // add new code plus button last modal open and focus on first field code start
//         // jQuery.fn.modal.Constructor.prototype._enforceFocus = function() {};
//         // setTimeout(() => {
//         //     const firstSelect2 = $modal.find('.select2-container--open .select2-search__field');
//         //     const firstInput = $modal.find('input:not([type=hidden]), select, textarea').filter(':visible').first();

//         //     if (firstSelect2.length > 0) {
//         //         firstSelect2.focus();
//         //     } else if (firstInput.length > 0) {
//         //         firstInput.focus();
//         //     }
//         // }, 10);

//         jQuery.fn.modal.Constructor.prototype._enforceFocus = function() {};
//         setTimeout(() => {
//             const firstSelect2 = $modal.find('.select2-container--open .select2-search__field');
//             const firstInput = $modal.find('input:not([type=hidden]):not([readonly]), select, textarea').filter(':visible').not('.skip-tab').first();

//             if (firstSelect2.length > 0) {
//                 firstSelect2.focus();
//             } else if (firstInput.length > 0) {
//                 setTimeout(() => {
//                     if (firstInput.hasClass('trans-date-picker')) {
//                         firstInput.one('focus', function () {
//                             jQuery(this).datepicker('hide');
//                         });
//                     }
//                     firstInput.focus();
//                     if (firstInput.is('input, textarea') && firstInput.val()) {
//                         const strLength = firstInput.val().length;
//                         firstInput[0].setSelectionRange(strLength, strLength);
//                     }
//                 }, 50);
//             }
//         }, 100);
//     // end

//     $modal.find('.js-example-basic-single').each(function () {
//         const $el = jQuery(this);

//         if ($el.data('select2')) {
//             try { $el.select2('destroy'); } catch (e) { }
//         }

//         $el.select2({
//             dropdownParent: $modal
//         });

//         // Add focus handler for modal Select2 instances
//         $el.on('select2:open', function() {
//             setTimeout(function() {
//                 const searchField = $modal.find('.select2-container--open .select2-search__field');
//                 if (searchField.length > 0) {
//                     searchField[0].focus();
//                 }
//             }, 0);
//         });
//     });

//     // Also handle other Select2 classes in modals
//     $modal.find('select').each(function() {
//         const $el = jQuery(this);
//         if ($el.hasClass('select2-hidden-accessible') || $el.data('select2')) {
//             // Re-initialize if needed
//             if (!$el.data('select2')) {
//                 $el.select2({
//                     dropdownParent: $modal
//                 });
//             }

//             // Ensure focus handler is attached
//             $el.off('select2:open.modal-focus').on('select2:open.modal-focus', function() {
//                 setTimeout(function() {
//                     const searchField = $modal.find('.select2-container--open .select2-search__field');
//                     if (searchField.length > 0) {
//                         searchField[0].focus();
//                     }
//                 }, 0);
//             });
//         }
//     });
// });








// new code start
jQuery(document).on('shown.bs.modal', '.modal', function () {
    const $modal = jQuery(this);
    jQuery.fn.modal.Constructor.prototype._enforceFocus = function () { };

    const firstSelect2 = $modal.find('.select2-container--open .select2-search__field');
    let firstInput = $modal
        .find('input:not([type=hidden]):not([readonly]), select, textarea')
        .filter(':visible')
        .not('.skip-tab, .col-search')
        .filter(function () {
            const $el = jQuery(this);
            // Skip DataTable length dropdown and search filter
            if ($el.closest('.dataTables_length').length || $el.closest('.dataTables_filter').length) {
                return false;
            }
            if (!$el.is('select')) return true;
            const $container = $el.next('.select2-container');
            if ($container.hasClass('select2-readonly')) {
                return false;
            }
            return true;
        })
        .first();

    if (firstSelect2.length > 0) {
        firstSelect2.focus();
    } else if (firstInput.length > 0) {
        setTimeout(() => {
            // Re-query the first input to get a fresh DOM reference in case it was redrawn
            let currentInput = $modal
                .find('input:not([type=hidden]):not([readonly]), select, textarea')
                .filter(':visible')
                .not('.skip-tab, .col-search')
                .filter(function () {
                    const $el = jQuery(this);
                    if ($el.closest('.dataTables_length').length || $el.closest('.dataTables_filter').length) {
                        return false;
                    }
                    if (!$el.is('select')) return true;
                    const $container = $el.next('.select2-container');
                    if ($container.hasClass('select2-readonly')) {
                        return false;
                    }
                    return true;
                })
                .first();

            if (currentInput.length === 0) return;

            // If the first input is a radio button, find the checked one in the group if available
            if (currentInput.is('input[type="radio"]')) {
                const name = currentInput.attr('name');
                if (name) {
                    const checkedRadio = $modal.find(`input[type="radio"][name="${name}"]:checked:visible`);
                    if (checkedRadio.length > 0) {
                        currentInput = checkedRadio;
                    }
                }
            }

            if (currentInput.hasClass('trans-date-picker') || currentInput.hasClass('date-picker')) {
                currentInput.one('focus', function () {
                    if ($modal.attr('id') !== 'RTCameraDetailsModal') {
                        jQuery(this).datepicker('hide');
                    }
                });
            }

            currentInput.focus();
            if (currentInput.hasClass('auto-select') && currentInput.val()) {
                setTimeout(() => {
                    currentInput.select();
                }, 10);
            }

            if (currentInput.is('input[type="text"], textarea') && currentInput.val()) {
                const strLength = currentInput.val().length;
                currentInput[0].setSelectionRange(strLength, strLength);
            }
        }, 200);
    }


});
// new code end

// jQuery(document).on('shown.bs.modal', '.modal', function () {
//     const $modal = jQuery(this);

//     // add new code plus button last modal open and focus on first field code start
//     // jQuery.fn.modal.Constructor.prototype._enforceFocus = function() {};
//     // setTimeout(() => {
//     //     const firstSelect2 = $modal.find('.select2-container--open .select2-search__field');
//     //     const firstInput = $modal.find('input:not([type=hidden]), select, textarea').filter(':visible').first();

//     //     if (firstSelect2.length > 0) {
//     //         firstSelect2.focus();
//     //     } else if (firstInput.length > 0) {
//     //         firstInput.focus();
//     //     }
//     // }, 10);

//     jQuery.fn.modal.Constructor.prototype._enforceFocus = function () { };
//     setTimeout(() => {
//         const firstSelect2 = $modal.find('.select2-container--open .select2-search__field');
//         // const firstInput = $modal.find('input:not([type=hidden]):not([readonly]), select, textarea').filter(':visible').not('.skip-tab').first();
//         const firstInput = $modal
//             .find('input:not([type=hidden]):not([readonly]), select, textarea')
//             .filter(':visible')
//             .not('.skip-tab')
//             .filter(function () {
//                 const $el = $(this);

//                 // agar select nathi → allow
//                 if (!$el.is('select')) return true;

//                 // select che → check next .select2-container
//                 const $container = $el.next('.select2-container');
//                 if ($container.hasClass('select2-readonly')) {
//                     return false; // skip
//                 }

//                 return true; // allow
//             })
//             .first();


//         if (firstSelect2.length > 0) {
//             console.log('if')
//             firstSelect2.focus();
//         } else if (firstInput.length > 0) {

//             setTimeout(() => {
//                 if (firstInput.hasClass('trans-date-picker')) {
//                     firstInput.one('focus', function () {
//                         jQuery(this).datepicker('hide');
//                     });
//                 }
//                 firstInput.focus();
//                 if (firstInput.is('input, textarea') && firstInput.val()) {
//                     const strLength = firstInput.val().length;
//                     firstInput[0].setSelectionRange(strLength, strLength);
//                 }
//             }, 50);
//         }
//     }, 100);
//     // end

//     // $modal.find('.js-example-basic-single').each(function () {
//     //     const $el = jQuery(this);

//     //     // if ($el.data('select2')) {
//     //     //     try { $el.select2('destroy'); } catch (e) { }
//     //     // }

//     //     // $el.select2({
//     //     //     dropdownParent: $modal
//     //     // });

//     //     // Add focus handler for modal Select2 instances
//     //     $el.on('select2:open', function () {
//     //         setTimeout(function () {
//     //             const searchField = $modal.find('.select2-container--open .select2-search__field');
//     //             if (searchField.length > 0) {
//     //                 searchField[0].focus();
//     //             }
//     //         }, 0);
//     //     });
//     // });

//     // // Also handle other Select2 classes in modals
//     // $modal.find('select').each(function () {
//     //     const $el = jQuery(this);
//     //     if ($el.hasClass('select2-hidden-accessible') || $el.data('select2')) {
//     //         // Re-initialize if needed
//     //         if (!$el.data('select2')) {
//     //             $el.select2({
//     //                 dropdownParent: $modal
//     //             });
//     //         }

//     //         // Ensure focus handler is attached
//     //         $el.off('select2:open.modal-focus').on('select2:open.modal-focus', function () {
//     //             setTimeout(function () {
//     //                 const searchField = $modal.find('.select2-container--open .select2-search__field');
//     //                 if (searchField.length > 0) {
//     //                     searchField[0].focus();
//     //                 }
//     //             }, 0);
//     //         });
//     //     }
//     // });
// });


jQuery(document).on('hidden.bs.modal', '.modal', function () {

    jQuery('.modal.show').each(function (i) {
        jQuery(this).css('z-index', 1050 + (i * 10));
    });

    jQuery('.modal-backdrop.modal-stack').each(function (i) {
        jQuery(this).css('z-index', 1040 + (i * 10));
        // jQuery(this).css('z-index', 1040 + i);
    });
    if (jQuery('.modal.show').length) {
        jQuery('body').addClass('modal-open');
    } else {
        jQuery('body').removeClass('modal-open');
        jQuery('.modal-backdrop.modal-stack').removeClass('modal-stack');
    }
});


jQuery(document).on('hidden.bs.modal', '.modal', function () {

    // Remove Bootstrap validate classes
    $(this).find("input, select, textarea").removeClass("is-valid is-invalid");

    // Remove browser default invalid style
    $(this).find("input, select, textarea").each(function () {
        this.setCustomValidity(""); // important
    });

    // Clear error messages
    // $(this).find(".invalid-feedback, .invalid-tooltip").text("");

    // Remove was-validated class from form
    $(this).find("form").removeClass("was-validated");

    // Reset the form
    $(this).find("form")[0]?.reset();

    $(this).find(".js-example-basic-single").val("").trigger("change");

    // Reset DataTable column search inputs & global ext filters for modals
    let $modal = $(this);
    $modal.find('input.col-search').val('');
    window.reportNoSearchTerm = '';
    $modal.find('table').each(function () {
        let tableId = this.id;
        if (tableId) {
            jQuery.fn.dataTable.ext.search =
                jQuery.fn.dataTable.ext.search.filter(f => f.tableId !== tableId);
        }
        if (jQuery.fn.DataTable.isDataTable(this)) {
            let dt = jQuery(this).DataTable();
            dt.search('').columns().search('');
        }
    });
});

//// Old code start code by rp
// jQuery(document).on('hidden.bs.modal', '.modal', function () {
//     const remainingModals = jQuery('.modal.show').length;

//     if (remainingModals > 0) {
//         jQuery('body').addClass('modal-open');
//         jQuery('.modal-backdrop').css({
//             'opacity': '0.45',
//             'display': 'block',
//             'z-index': 1040 + (remainingModals * 10) - 1
//         });
//     } else {
//         jQuery('.modal-backdrop').remove();
//         jQuery('body').removeClass('modal-open');
//     }
// });
//// Old code end rp


jQuery(document).on('show.bs.modal', '.modal', function () {
    setTimeout(() => {
        jQuery('.modal-backdrop').css({
            'opacity': '0.45',
            'display': 'block'
        });
    }, 150);

});
// $(document).find(`:input,textarea,select,button`).each(function () {
//     if (jQuery(this).is('[readonly], :disabled') || jQuery(this).hasClass('skip-tab')) {
//         jQuery(this).attr('tabindex', '-1');  
//     } else {
//         jQuery(this).removeAttr('tabindex'); 
//     }

// });
// $(document).on('focusin mouseenter', ':input, button', function () {

//     const $el = $(this);

//     // jo readonly/disabled nathi ane tabindex -1 che → restore
//     if (!$el.is('[readonly], :disabled') && $el.attr('tabindex') == '-1') {
//         $el.removeAttr('tabindex'); // normal focus back
//     }

// });

$(document).on("input", "input[type='email']", function () {
    let email = $(this).val().trim();
    let tooltip = $(this).siblings(".invalid-tooltip");

    let isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

    if (email !== "" && !isValid) {
        $(this).addClass("is-invalid").removeClass("is-valid");
        tooltip.show().html("Enter Valid Email ID.");
    } else {
        $(this).removeClass("is-invalid").addClass("is-valid");
        tooltip.hide();
    }
});

// Number
// $(document).on(".number", function () {

//     let Number = $(this).val().trim();
//     let tooltip = $(this).siblings(".invalid-tooltip");

//     let isValid = /^\d{10}$/.test(Number);

//     if (Number !== "" && !isValid) {
//         $(this).addClass("is-invalid").removeClass("is-valid");
//         tooltip.show().html("Enter Valid Phone No.");
//     } else {
//         $(this).removeClass("is-invalid").addClass("is-valid");
//         tooltip.hide();
//     }
// });

$(document).on("input", ".number", function () {
    let inputVal = $(this).val().trim();
    let tooltip = $(this).siblings(".invalid-tooltip");
    let isValid = /^\+?\d+$/.test(inputVal);

    if (inputVal !== "" && !isValid) {
        $(this).addClass("is-invalid").removeClass("is-valid");
        tooltip.show().html("Enter Valid Phone No.");
    } else if (inputVal !== "" && isValid) {
        $(this).removeClass("is-invalid").addClass("is-valid");
        tooltip.hide();
    } else {
        $(this).removeClass("is-invalid is-valid");
        tooltip.hide();
    }
});

// gstin validation
function isValidGSTIN(gstin) {
    if (gstin.length !== 15) {
        return false;
    }
    const GSTINregexp = /^([0][1-9]|[1-2][0-9]|[3][0-7])([a-zA-Z]{5}[0-9]{4}[a-zA-Z]{1}[1-9a-zA-Z]{1}[zZ]{1}[0-9a-zA-Z]{1})$/;
    return GSTINregexp.test(gstin);
}

// pan validation
function isValidPAN(pan) {
    if (pan.length !== 10) {
        return false;
    }
    const PANregexp = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
    return PANregexp.test(pan);
}

// phone number validation
$(document).on("input", ".common-phone-validate", function (e) {
    let phoneField = $(this);
    let val = phoneField.val().trim();

    if (val === "") {
        phoneField.removeClass("is-invalid is-valid");
    } else if (val.length > 12) {
        phoneField.addClass("is-invalid").removeClass("is-valid");
    } else {
        phoneField.removeClass("is-invalid").addClass("is-valid");
    }
});

// mobile number validation
$(document).on("input", ".common-mobile-validate", function (e) {
    let mobileField = $(this);
    let val = mobileField.val().trim();

    if (val === "") {
        mobileField.removeClass("is-invalid is-valid");
    } else if (val.length > 10) {
        mobileField.addClass("is-invalid").removeClass("is-valid");
    } else {
        mobileField.removeClass("is-invalid").addClass("is-valid");
    }
});

function toastDetailDelete(message, callback = null) {

    // Key handler for Enter / Space
    const keyHandler = function (e) {
        if (e.key === "Enter" || e.key === " ") {
            e.preventDefault();
            Swal.clickConfirm();
        }
    };

    const openModals = jQuery('.modal.show').length;
    const topZIndex = 1050 + (openModals * 15);

    Swal.fire({
        html: '<div class="mt-3">' +
            '<lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>' +
            '<div class="mt-4 pt-2 fs-15 mx-5">' +
            `<p class="text-muted mx-4 mb-0">${message}</p>` +
            '</div>' +
            '</div>',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        customClass: {
            confirmButton: 'btn btn-primary w-xs me-2 mb-1',
            cancelButton: 'btn btn-danger w-xs mb-1',
        },
        buttonsStyling: false,
        showCloseButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,

        didOpen: (el) => {
            try {
                Swal.getConfirmButton()?.focus({ preventScroll: true });
            } catch (e) { }
            $(el).closest('.swal2-container').css('z-index', topZIndex + 100);

            document.addEventListener("keydown", keyHandler);
        },

        willClose: () => {
            document.removeEventListener("keydown", keyHandler);
        }
    }).then((res) => {
        if (res.isConfirmed && typeof callback === "function") {
            callback();
        }
    });
}

function toastDelete(message, onConfirm = null) {

    document.activeElement?.blur();

    const openModals = jQuery('.modal.show').length;
    const topZIndex = 1050 + (openModals * 15);

    Swal.fire({
        html: `
            <div class="mt-3">
                <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json"
                    trigger="loop"
                    colors="primary:#f7b84b,secondary:#f06548"
                    style="width:100px;height:100px">
                </lord-icon>
                <div class="mt-4 pt-2 fs-15 mx-5">
                    <p class="text-muted mx-4 mb-0">${message}</p>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: "Yes",
        cancelButtonText: "No",
        customClass: {
            confirmButton: "btn btn-primary w-xs me-2 mb-1",
            cancelButton: "btn btn-danger w-xs mb-1",
        },
        buttonsStyling: false,
        showCloseButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        focusConfirm: false,

        didOpen: (el) => {
            $('.modal').attr('data-bs-keyboard', 'false');

            setTimeout(() => {
                const btn = Swal.getConfirmButton();
                if (btn) btn.focus();
            }, 50);
            $(el).closest('.swal2-container').css('z-index', topZIndex + 100);

            document.addEventListener('keydown', handleEnterDelete);
        },

        willClose: () => {
            $('.modal').attr('data-bs-keyboard', 'true');
            document.removeEventListener('keydown', handleEnterDelete);
        }

    }).then(res => {
        if (res.isConfirmed && onConfirm) {
            onConfirm();
        }
    });

    function handleEnterDelete(e) {
        if (e.key === "Enter" || e.key === " " || e.code === "Space") {
            e.preventDefault();
            e.stopPropagation();
            Swal.clickConfirm();
        }
    }
}

jQuery(".trans-date-picker:not([readonly])").datepicker({

    dateFormat: "dd/mm/yy",

    minDate: startDate,

    maxDate: endDate,
    onClose: function () {
        this.focus()
    },

});


const date = new Date();
let currentDay = String(date.getDate()).padStart(2, '0');
let currentMonth = String(date.getMonth() + 1).padStart(2, "0");
let currentYear = date.getFullYear();
let currentDate = `${currentDay}/${currentMonth}/${currentYear}`;

// date validation

// .date-picker on change Validation
jQuery(".date-picker").change(function (e) {
    var idata = e.target.value;
    var check = false;
    var re = /^\d{1,2}\/\d{1,2}\/\d{4}$/;
    if (re.test(idata)) {
        var adata = idata.split('/');
        var dd = parseInt(adata[0], 10);
        var mm = parseInt(adata[1], 10);
        var yyyy = parseInt(adata[2], 10);
        var xdata = new Date(yyyy, mm - 1, dd);
        if ((xdata.getFullYear() === yyyy) && (xdata.getMonth() === mm - 1) && (xdata.getDate() === dd)) {
            check = true;
        } else {
            check = false;
        }
    } else {
        jQuery(e.target).val('');
        check = false;
    }
    if (check == false) {
        toastr.error("Please Enter A Valid Date!");
    }
});

function isValidDate(dateStr) {
    // Check format: DD/MM/YYYY
    const regex = /^\d{1,2}\/\d{1,2}\/\d{4}$/;

    if (!regex.test(dateStr)) return false;

    const parts = dateStr.split("/");
    const dd = parseInt(parts[0], 10);
    const mm = parseInt(parts[1], 10);
    const yyyy = parseInt(parts[2], 10);

    // Month check
    if (mm < 1 || mm > 12) return false;

    // Day check
    const date = new Date(yyyy, mm - 1, dd);
    return (
        date.getFullYear() === yyyy &&
        date.getMonth() === mm - 1 &&
        date.getDate() === dd
    );
}

// Reinitialize date-picker for new elements
jQuery('.date-picker').datepicker({
    dateFormat: "dd/mm/yy",
    // autoclose: true,
    templates: {
        leftArrow: '<i class="fa fa-chevron-left"></i>',
        rightArrow: '<i class="fa fa-chevron-right"></i>'
    }, onClose: function () {
        this.focus()
    },
});

// check date year
function checkDate(date) {

    defYear = jQuery('#def_year_startdate').val();
    var defYearLastDate = jQuery('#def_year_enddate').val();
    var newdate = date.split("/").reverse().join("-");

    const defDate = new Date(defYear);
    const currentDate = new Date(newdate);
    const deflastDate = new Date(defYearLastDate);

    if (date !== undefined && date != "") {
        if (currentDate >= defDate && currentDate <= deflastDate) {
            return 'yes';
        } else {
            return 'no';
        }
    } else {
        return 'yes';
    }


}

// check date on focus on tab
jQuery(".trans-date-picker").one("focus", function (e) {

    if (checkDate(e.target.value) == 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");

        jQuery('.trans-date-picker').trigger('liszt:activate');
        // jQuery(".trans-date-picker").on("focus");
    } else {
        return true;
    }

});

// .trans-date-picker on change Financial Year Validation
jQuery(".trans-date-picker").on("change", function (e) {

    if (checkDate(e.target.value) == 'no') {
        toastr.error("Enter Date within Current Financial Year Selected.");

        // jQuery('#' + id).focus();


        jQuery('.trans-date-picker').trigger('liszt:activate');
        // jQuery(".trans-date-picker").on("focus");
    } else {
        return true;
    }

});

// .trans-date-picker on change Validation
jQuery(".trans-date-picker").change(function (e) {
    var idata = e.target.value;
    var check = false;
    var re = /^\d{1,2}\/\d{1,2}\/\d{4}$/;
    if (re.test(idata)) {
        var adata = idata.split('/');
        var dd = parseInt(adata[0], 10);
        var mm = parseInt(adata[1], 10);
        var yyyy = parseInt(adata[2], 10);
        var xdata = new Date(yyyy, mm - 1, dd);
        if ((xdata.getFullYear() === yyyy) && (xdata.getMonth() === mm - 1) && (xdata.getDate() === dd)) {
            check = true;
        } else {
            check = false;
        }
    } else {
        // jQuery('.trans-date-picker').val('');
        check = false;
    }
    if (check == false) {
        toastr.error("Please Enter A Valid Date!");
    }
});
// 0.001 Min set 
function notOnlyZero(value) {

    if (value === undefined || value === null || value === '') {
        return true;
    }

    var floatValue = parseFloat(value);
    return floatValue > 0;
}

// excel export code start
function newexportaction(e, dt, button, config) {

    var self = this;
    var oldStart = dt.settings()[0]._iDisplayStart;
    dt.one('preXhr', function (e, s, data) {
        // Just this once, load all data from the server...
        data.start = 0;
        data.length = -1;
        dt.one('preDraw', function (e, settings) {
            // Call the original action function
            if (button[0].className.indexOf('buttons-copy') >= 0) {
                jQuery.fn.dataTable.ext.buttons.copyHtml5.action.call(self, e, dt, button, config);
            } else if (button[0].className.indexOf('buttons-excel') >= 0) {
                jQuery.fn.dataTable.ext.buttons.excelHtml5.available(dt, config) ?
                    jQuery.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config) :
                    jQuery.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
            }
            dt.one('preXhr', function (e, s, data) {
                // DataTables thinks the first item displayed is index 0, but we're not drawing that.
                // Set the property to what it was before exporting.
                settings._iDisplayStart = oldStart;
                data.start = oldStart;
            });
            // Reload the grid with the original page. Otherwise, API functions like table.cell(this) don't work properly.
            setTimeout(dt.ajax.reload, 0);
            // Prevent rendering of the full data to the DOM
            return false;
        });
    });
    // Requery the server with the new one-time export settings
    dt.ajax.reload();
}
// excel export code end

// page length code start
jQuery.extend(true, jQuery.fn.dataTable.defaults, {
    "fixedHeader": true,
    "pageLength": 25,
    "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
    "oLanguage": {
        "sEmptyTable": "No record found!",
        "sZeroRecords": "No search results found!",
        "sProcessing": "<div class='center'>"
            + "<img src='images/loaders/loader6.gif' alt='loader'/>"
            + "</div>"
    }
});
// page length code end


// Column search code start
function initColumnSearch(tableSelector, excludeColumns = [], page = null) {
    if (tableSelector === "#LocationTable") {
        jQuery('#LocationTable thead tr.search-row').remove();
        return;
    }

    setTimeout(() => {
        let $searchInput = $('.dataTables_filter input[type="search"]');
        if (!$searchInput.length) return;
    }, 300);

    setTimeout(() => {
        let $searchInput = $('.dataTables_filter input[type="search"]');
        if (!$searchInput.length) return;

        let normalCSS = {
            'background-color': $searchInput.css('background-color'),
            'border-color': $searchInput.css('border-color'),
            'border-width': $searchInput.css('border-width'),
            'border-style': $searchInput.css('border-style'),
            'border-radius': $searchInput.css('border-radius'),
            'color': $searchInput.css('color'),
            'padding': $searchInput.css('padding'),
            'height': $searchInput.outerHeight() + 'px',
            'outline': 'none'
        };

        // Apply normal style to all column search inputs
        $('thead tr.search-row input').css(normalCSS);
        // Apply focus effect same as search box OR custom purple
        $('thead tr.search-row input')
            .on('focus', function () {
                $(this).css({
                    'border-color': '#a78bfa',
                });
            })
            .on('blur', function () {
                $(this).css({
                    'border-color': $searchInput.css('border-color'),
                    'box-shadow': 'none'
                });
            });

        // Apply focus effect same as search box

        jQuery('#LocationTable thead tr.search-row').remove();
    }, 300);
    let $table = jQuery(tableSelector);

    if (!$table.length || !$table.DataTable().settings().length) return;

    let tableApi = $table.DataTable();


    let $origFirstTh = $table.find('thead tr').first().find('th').eq(0);
    let $firstTd = $table.find('tbody tr:first td:first');

    let origText = $origFirstTh.text().trim().toLowerCase();

    let hasActionsHeader =
        $origFirstTh.is('.radio_column') ||
        $origFirstTh.find("i, svg, a, button, input[type=checkbox], .fa, .mdi").length > 0;
    let hasActionsBody =
        $firstTd.is('.radio_column') ||
        $firstTd.find("i, svg, a, button, input[type=checkbox], .fa, .mdi").length > 0;
    let isBlankHeader = origText === "" && !hasActionsHeader;


    const CUSTOM_EXCLUSION_SELECTOR = '.remove_filters_short_qty, .remove_filters_short_close_reason';
    $table.find('tbody tr:first td').each(function (index) {
        let $currentTd = jQuery(this);
        if ($currentTd.is(CUSTOM_EXCLUSION_SELECTOR) || $currentTd.find(CUSTOM_EXCLUSION_SELECTOR).length > 0) {
            excludeColumns.push(index);
        }
    });

    // $table.find('thead tr:first th').each(function (index) {
    //     let $currentTh = jQuery(this);
    //     if ($currentTh.is(CUSTOM_EXCLUSION_SELECTOR) || $currentTh.find(CUSTOM_EXCLUSION_SELECTOR).length > 0) {
    //         excludeColumns.push(index);
    //     }
    // });

    // if (tableApi && tableApi.settings() && tableApi.settings()[0]) {
    //     tableApi.settings()[0].aoColumns.forEach(function (col, index) {
    //         if (col.bSearchable === false || col.searchable === false || (col.sClass && (col.sClass.includes('remove_filters_short_qty') || col.sClass.includes('remove_filters_short_close_reason')))) {
    //             excludeColumns.push(index);
    //         }
    //     });
    // }

    if (origText === "actions" || hasActionsHeader || hasActionsBody) {
        excludeColumns.push(0);
    }

    excludeColumns = [...new Set(excludeColumns)];

    if (page == 'common_search') {

        let targetTableId = jQuery(tableSelector).attr('id') || '';
        let $table = jQuery(tableSelector);
        if (!$table.length || !$table.DataTable().settings().length) return;

        if (targetTableId) {
            jQuery.fn.dataTable.ext.search =
                jQuery.fn.dataTable.ext.search.filter(f => f.tableId !== targetTableId);
        }

        let tableApi = $table.DataTable();
        let $header = $table.closest('.dataTables_scroll').find('.dataTables_scrollHeadInner table thead');


        $header.find('tr.search-row').remove();
        let $searchRow = jQuery('<tr class="search-row"></tr>').appendTo($header);

        let totalColumns = $header.find('tr').first().find('th').length;

        let searchableColumns = [];

        for (let i = 0; i < totalColumns; i++) {
            if (!excludeColumns.includes(i)) searchableColumns.push(i);
        }

        let reportNoColIdxList = [];
        let dateColIdxList = [];

        const srNoRegex = /^(sr|inq.|issue slip|inward no|insp no|order no|po|grn|dc no|planning no)\s*\.?\s*(no|number|no)\.?\s*$/i;

        $header.find('tr').first().find('th').each(function (i) {
            let text = jQuery(this).text().trim().toLowerCase();
            if (srNoRegex.test(text)) reportNoColIdxList.push(i);
            if (text.includes('date')) dateColIdxList.push(i);
        });

        // NEW: This will store only the ACTIVE report-no column
        let activeReportColIdx = -1;

        // Build search row
        $header.find('tr').first().find('th').each(function (i) {
            if (searchableColumns.includes(i)) {
                $searchRow.append(`<th><input type="text" class="col-search" style="width:100%;" placeholder="${jQuery(this).text().trim()}" /></th>`);
            } else {
                $searchRow.append('<th></th>');
            }
        });

        // applynumber style filter
        window.reportNoSearchTerm = '';

        function applyNumberFilter(keyword, value) {
            keyword = keyword.toLowerCase().trim();
            if (!keyword) return false;

            const year = new Date().getFullYear() % 100;
            const next_year = ('0' + ((year + 1) % 100)).slice(-2);
            const current_fin_year = year + '-' + next_year;

            // Only number input → pad to 4 digits
            if (/^\d+$/.test(keyword)) {
                let seq = keyword.padStart(4, '0');
                return value.includes('/' + seq + '/');
            }

            // /digits input
            let m = keyword.match(/^\/(\d+)$/);
            if (m) {
                let seq = m[1].padStart(4, '0');
                return value.includes('/' + seq + '/');
            }

            // prefix/year filter
            let prefix = keyword;
            let year_part = '';

            if (keyword.includes('/')) {
                let parts = keyword.split('/');
                prefix = parts[0] + '/' + parts[1];
                year_part = parts[2] ?? '';
            }

            if (year_part && !/^\d{2}-\d{2}$/.test(year_part)) return value.includes(keyword);
            if (year_part && year_part !== current_fin_year) return false;

            return value.includes(prefix) || value === keyword;
        }

        // FIXED FILTER: APPLY ONLY ON activeReportColIdx
        let reportFilter = function (settings, data) {
            if (settings.nTable.id !== targetTableId) return true;

            if (!window.reportNoSearchTerm) return true;
            if (activeReportColIdx < 0) return true; // no active column

            let reportNoValue = (data[activeReportColIdx] || '').toLowerCase();

            return applyNumberFilter(window.reportNoSearchTerm, reportNoValue);
        };
        reportFilter.tableId = targetTableId;
        jQuery.fn.dataTable.ext.search.push(reportFilter);

        // Column-wise search handling
        tableApi.columns().every(function () {
            let column = this;
            let idx = this.index();
            let headerText = jQuery(column.header()).text().trim().toLowerCase();

            if (searchableColumns.includes(idx)) {
                jQuery('input', $searchRow.find('th').eq(idx))
                    .on('keyup change clear', function () {

                        let val = this.value.trim();
                        let placeholder = jQuery(this).attr('placeholder').trim().toLowerCase();

                        // REPORT-NO COLUMN typed input
                        if (reportNoColIdxList.includes(idx)) {
                            activeReportColIdx = idx;
                            window.reportNoSearchTerm = val;
                            tableApi.draw();
                            return;
                        }

                        // DATE COLUMN
                        if (placeholder.includes("date")) {

                            jQuery.fn.dataTable.ext.search =
                                jQuery.fn.dataTable.ext.search.filter(f => !(f.isDateFilter && f.tableId === targetTableId));

                            if (val) {
                                let searchValParts = val.split('/').map(p => p.trim()).filter(p => p !== "");
                                if (searchValParts.length > 0) {
                                    let dateFilter = function (settings, data, dataIndex) {
                                        if (settings.nTable.id !== targetTableId) return true;
                                        let raw = data[idx] || '';
                                        let div = document.createElement('div');
                                        div.innerHTML = raw;
                                        let cellValue = div.textContent || div.innerText || '';

                                        let dates = cellValue.split(',').map(d => d.trim());
                                        return dates.some(d => {
                                            let parts = d.split('/').map(p => p.trim());
                                            for (let i = 0; i < searchValParts.length; i++) {
                                                if (searchValParts[i] !== parts[i]) return false;
                                            }
                                            return true;
                                        });
                                    };
                                    dateFilter.isDateFilter = true;
                                    dateFilter.tableId = targetTableId;
                                    jQuery.fn.dataTable.ext.search.push(dateFilter);
                                }
                            }

                            tableApi.draw();
                            return;
                        }

                        // NORMAL COLUMN
                        if (column.search() !== val) {
                            column.search(val).draw();
                        }
                    });
            }
        });

        // Align header
        let $scrollHead = jQuery(tableApi.table().container()).find('.dataTables_scrollHead thead');
        let $main = $scrollHead.find('tr.main-header');
        let $search = $scrollHead.find('tr.search-row');
        $search.insertBefore($main);

        let $origHead = jQuery(tableApi.table().header());
        let $mainOrig = $origHead.find('tr.main-header');
        let $searchOrig = $origHead.find('tr.search-row');
        $searchOrig.insertBefore($mainOrig);
        // new code end

    } else {


        let $origHead = jQuery(tableApi.table().header());
        $origHead.find("tr.search-row").remove();

        let $origSearchRow = jQuery('<tr class="search-row"></tr>');
        let totalCols = $origHead.find("tr").first().find("th").length;

        for (let i = 0; i < totalCols; i++) {
            if (excludeColumns.includes(i)) {
                $origSearchRow.append("<th></th>");
            } else {
                let title = $origHead.find("tr").first().find("th").eq(i).text().trim();
                $origSearchRow.append(
                    `<th><input type="text" class="col-search" data-col="${i}" style="width:100%;" placeholder="${title}"></th>`
                );
            }
        }

        $origHead.prepend($origSearchRow);

        let $scrollHead = $table.closest(".dataTables_wrapper")
            .find(".dataTables_scrollHead thead");

        $scrollHead.find("tr.search-row").remove();
        $scrollHead.prepend($origSearchRow.clone());

        $origHead.find("input.col-search").on("keyup change clear", function () {

            let colIdx = jQuery(this).data("col");
            let value = jQuery(this).val();

            tableApi.column(colIdx).search(value).draw();
        });
    }
}
// column search code end

//reset filter button code start 
function addResetButton(table, $tableElement) {
    // Get the closest wrapper around the table where DataTables initializes its UI
    var wrapper = $tableElement.closest('.dataTables_wrapper');

    // Locate the filter (search box) container
    var filterContainer = wrapper.find('.dataTables_filter');

    // Ensure the filter container uses flex styling so the button aligns well with the search input
    // filterContainer.css({
    // 	'display': 'flex',
    // 	'align-items': 'center',
    // 	'gap': '10px',
    // 	'flex-wrap': 'nowrap'
    // });
    // Generate a unique ID for the reset button using the table ID (or a random fallback)
    var tableId = $tableElement.attr('id') || 'datatable-' + Math.floor(Math.random() * 10000);
    var resetBtnId = 'reset-filters-' + tableId;

    // Check if reset button already exists to avoid duplicates
    if (!filterContainer.find('#' + resetBtnId).length) {
        // Create the reset button
        var resetBtn = jQuery('<button>', {
            id: resetBtnId,
            class: 'btn btn-primary',
            type: 'button',
            text: 'Reset Filter'
        });

        // Apply minimal inline styles to the button
        resetBtn.css({
            'outline': 'none',
            'box-shadow': 'none',
            'border': '',
            'background': '',
            'cursor': 'pointer',
            'margin-right': '13px'
        });

        // Remove focus styling when the button is focused
        resetBtn.on('focus', function () {
            jQuery(this).css({
                'outline': 'none',
                'box-shadow': 'none'
            });
            hideLoader();
        });

        // Remove focus when mouseup occurs (for better UX)
        resetBtn.on('mouseup', function () {
            this.blur();
            hideLoader();
        });

        // Add the reset button before the search input in the filter container
        filterContainer.prepend(resetBtn);

        // Button click behavior: reset all filters
        resetBtn.on('click', function () {
            // Clear global search
            table.search('').draw();

            // Loop through each column and clear individual column search input if present
            table.columns().every(function (index) {
                var input = jQuery(this.header())
                    .closest('table')
                    .find('thead tr.search-row th')
                    .eq(index)
                    .find('input');

                if (input.length) {
                    input.val('');      // Clear input value
                    this.search('');    // Clear column search
                }
            });
            window.reportNoSearchTerm = '';
            jQuery.fn.dataTable.ext.search = jQuery.fn.dataTable.ext.search.filter(f => !f.isDateFilter);
            // Redraw the table with all filters cleared
            table.draw();
        });
    }
}
// reset filter button code end

// When any DataTable is initialized, hook into the event to conditionally add the reset button
jQuery(document).on('init.dt', function (e, settings) {
    var table = new jQuery.fn.dataTable.Api(settings);         // DataTable instance
    var $tableElement = jQuery(settings.nTable);               // jQuery-wrapped <table> element

    // Only add reset button if:
    // - Table has 'dataTable' class
    // - Table does NOT have 'remove-reset-filter' class (used to exclude some tables)
    if ($tableElement.hasClass('dataTable') && !$tableElement.hasClass('remove-reset-filter')) {
        addResetButton(table, $tableElement);
    }
});

// call column search on init start
jQuery(document).on('preInit.dt', function (e, settings) {

    let table = settings.nTable;

    setTimeout(() => {
        let $scrollHead = jQuery(table)
            .closest('.dataTables_wrapper')
            .find('.dataTables_scrollHead thead');

        if ($scrollHead.length && !$scrollHead.find("tr.main-header").length) {
            $scrollHead.find("tr:first").addClass("main-header");
        }
    }, 50);
});


jQuery(document).on('init.dt', function (e, settings) {

    let table = settings.nTable;
    let $table = jQuery(table);

    // read excluded columns from attribute
    let excludeColumns = $table.data("exclude-search");

    if (excludeColumns !== undefined && excludeColumns !== null && excludeColumns !== '') {
        excludeColumns = excludeColumns.toString().split(",").map(Number);
    } else {
        excludeColumns = [];
    }

    setTimeout(() => {
        var pageType = $table.hasClass("pending_table") ? "common_search" : null;

        initColumnSearch("#" + $table.attr("id"), excludeColumns, pageType);
    }, 80);
});
//call column search on init end




// This Function Use In Readonly For Select2
// function makeSelect2Readonly(selector, state = false) {

//     const $el = $(selector);

//     if (state) {
//         $el.addClass('readonly-select2');

//         $el.on('select2:opening.select2readonly select2:open.select2readonly', function (e) {
//             e.preventDefault();
//             $el.select2('close');
//         });

//         // $el.next('.select2-container')
//         //     .find('.select2-selection')
//         //     .css({
//         //         'pointer-events': 'none',
//         //         'background-color': '#e9ecef',
//         //         'cursor': 'not-allowed'
//         //     });

//     } else {
//         $el.removeClass('readonly-select2');
//         $el.off('.select2readonly');

//         // $el.next('.select2-container')
//         //     .find('.select2-selection')
//         //     .css({
//         //         'pointer-events': '',
//         //         'background-color': '',
//         //         'cursor': ''
//         //     });
//     }
// }
jQuery(document).on('init.dt', function (e, settings) {

    let wrapper = jQuery(settings.nTableWrapper);

    // prevent duplicates
    if (wrapper.find('.dt-top').length === 0) {
        wrapper.find('.dataTables_length, .dataTables_filter')
            .wrapAll('<div class="dt-top"></div>');
    }

});

// zero to empty function (edit time)
function zeroToEmpty(value) {
    return (value === 0 || value === "0") ? "" : value;
}

/* ------------------------------------------------------
   SELECT2 FULL CONTROL: FOCUS + TAB + ARROWS + ENTER
   (FINAL CLEAN VERSION)
-------------------------------------------------------*/

/* Helpers */
const s2c = $s => $s.next(".select2-container");
const refresh = $s => s2c($s).toggleClass("s2-has-value", !!$s.val()?.length);

/* OPEN */
jQuery(document).on("select2:open", e => {
    const $s = jQuery(e.target), $c = s2c($s);
    $c.addClass("s2-focused");
    refresh($s);

    setTimeout(() => {
        $c.find(".select2-search__field").trigger("focus");
    }, 10);
});

/* CLOSE */
jQuery(document).on("select2:close", e => {
    const $s = jQuery(e.target);
    s2c($s).removeClass("s2-focused");
    refresh($s);
});

/* VALUE CHANGE */
jQuery(document).on("change", "select.select2-hidden-accessible", function () {
    refresh(jQuery(this));
});
jQuery(() => {
    jQuery("select.select2-hidden-accessible").each(function () {
        refresh(jQuery(this));
    });
});

/* TAB FOCUS */
jQuery(document).on("keydown", function (e) {
    if (e.key !== "Tab") return;

    setTimeout(() => {
        const el = document.activeElement;

        if (el.tagName === "SELECT") {
            jQuery(".select2-container").removeClass("s2-focused");
            s2c(jQuery(el)).addClass("s2-focused");
            return;
        }

        const container = el.closest(".select2-container");
        if (container) {
            // jQuery(".select2-container").removeClass("s2-focused");
            jQuery(".select2-container").attr("class");
            // console.log(jQuery(".select2-container").attr("class"));
            // jQuery(container).addClass("s2-focused");
        }
    }, 10);
});

/* ARROW KEYS */
jQuery(document).on("keydown", ".select2-search__field", function () {
    const $c = jQuery(this).closest(".select2-container");

    setTimeout(() => {
        $c.find(".select2-results__option--highlighted")[0]?.scrollIntoView({
            block: "nearest"
        });
    }, 10);
});

/* CLICK = focus */
jQuery(document).on("click", ".select2-container", function () {
    const $c = jQuery(this);

    jQuery(".select2-container").removeClass("s2-focused");
    $c.addClass("s2-focused");

    setTimeout(() => {
        $c.find(".select2-search__field").trigger("focus");
    }, 5);
});

document.querySelectorAll('.skip-tab').forEach(el => {
    el.setAttribute('tabindex', '-1');
});
$(document).ready(function () {
    // After Select2 init
    setTimeout(function () {
        $('.select2-selection--multiple')
            .attr('tabindex', '0');   // allow tab focus
    }, 300);
    // Focus highlight (TAB or mouse)
    $(document).on('focus', '.select2-selection--multiple', function () {
        $(this).closest('.select2-container').addClass('s2-focused');
    });
    // Remove highlight on blur
    $(document).on('blur', '.select2-selection--multiple', function () {
        $(this).closest('.select2-container').removeClass('s2-focused');
    });
});


// document.addEventListener('keydown', function(event) {
//   if (event.key === 'Escape') {
//     event.preventDefault();
//     window.location.reload();
//   }
// });

// new code start
document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('hidden.bs.modal', function (e) {
        // new code dc for details modal hide that time focus on after input
        const closedModal = e.target;
        if (closedModal?.dataset?.customHideFocus === 'true') {
            return;
        }

        const activeModals = document.querySelectorAll('.modal.show');
        if (activeModals.length > 0) {
            const topModal = activeModals[activeModals.length - 1];
            const specificSelectors = [
                'input[type="text"]:not([readonly]):not([disabled])',
                'input[type="date"]:not([readonly]):not([disabled])',
                'input[type="number"]:not([readonly]):not([disabled])',
                'input[type="file"]:not([disabled])',
                'textarea:not([readonly]):not([disabled])',
                'select:not([disabled])',
                'input[type="radio"]:not([disabled])',
                'input[type="checkbox"]:not([disabled])'
            ].join(',');

            const elementToFocus = topModal.querySelector(specificSelectors);
            if (elementToFocus) {
                setTimeout(() => {
                    elementToFocus.focus();
                    if (elementToFocus.tagName === 'INPUT' || elementToFocus.tagName === 'TEXTAREA') {
                        const isTextType = ['text', 'number', 'date'].includes(elementToFocus.type) || elementToFocus.tagName === 'TEXTAREA';
                        if (isTextType && typeof elementToFocus.setSelectionRange === 'function') {
                            const valLength = elementToFocus.value.length;
                            elementToFocus.setSelectionRange(valLength, valLength);
                        }
                    }
                }, 200);
            }
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Tab') {
            const activeModals = document.querySelectorAll('.modal.show');
            if (activeModals.length > 0) {
                const topModal = activeModals[activeModals.length - 1];
                const focusableElements = topModal.querySelectorAll('input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), a[href]');
                if (focusableElements.length > 0) {
                    const firstElement = focusableElements[0];
                    const lastElement = focusableElements[focusableElements.length - 1];

                    if (e.shiftKey) {
                        if (document.activeElement === firstElement) {
                            e.preventDefault();
                            lastElement.focus();
                        }
                    } else {
                        if (document.activeElement === lastElement) {
                            e.preventDefault();
                            firstElement.focus();
                        }
                    }
                }
            }
        }
    });
});

$(document).ajaxSuccess(function (event, xhr, settings, data) {
    if (data && data.response_code == 1) {
        if (settings.url.indexOf('store') !== -1) {
            if ($.fn.DataTable.isDataTable('#dyntable')) {
                $('#dyntable').DataTable().ajax.reload(null, false);
            }
        }
    }
});
// new code end

let skipLoader = false;
function showLoader() {
    if (!skipLoader) {
        $('#full-page-loader').removeClass('hidden-loader').addClass('loader-progress-whole-page');
    }
}

function hideLoader() {
    $('#full-page-loader').removeClass('loader-progress-whole-page').addClass('hidden-loader');
    skipLoader = false;
}

$(document).on('mousedown', 'a, button', function () {
    skipLoader = true;
});

$(document).on('preXhr.dt', function () {
    showLoader();
});

$(document).on('xhr.dt', function () {
    hideLoader();
});

$(document).on('error.dt', function () {
    hideLoader();
});

// formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
// var finaltoken = $('meta[name="csrf-token"]').attr('content');
// console.log("aaaaaaaaa",finaltoken);

// $(document).ajaxSend(function (event, jqXHR, settings) {
//     if (settings.url && (settings.url.includes('delete') || settings.url.includes('remove'))) {
//         skipLoader = false;
//         showLoader();
//     }
// });

// $(document).ajaxComplete(function (event, jqXHR, settings) {
//     if (settings.url && (settings.url.includes('delete') || settings.url.includes('remove'))) {
//         hideLoader();
//     }
// });

// $(document).ajaxError(function (event, jqXHR, settings) {
//     if (settings.url && (settings.url.includes('delete') || settings.url.includes('remove'))) {
//         hideLoader();
//     }
// });

var validChars = [

    "-",

    "+"

];

var specialChars = [

    "!",

    "@",

    "#",

    "$",

    "%",

    "^",

    "&",

    "*",

    "(",

    ")"

];


jQuery(document).on('keydown', '.mobile-f', function (e) {
    if (e.which != 8 && e.which != 0 && e.which != 9 && e.which != 32 && e.which != 96 && e.which != 97 && e.which != 98 && e.which != 99 && e.which != 100 && e.which != 101 && e.which != 102 && e.which != 103 && e.which != 104 && e.which != 105 && e.which != 107 && (e.which < 48 || (e.which > 57 && validChars.includes(e.key) !== true))) {
        e.preventDefault();
    } else if (specialChars.includes(e.key)) {
        e.preventDefault();
    }
});

jQuery(document).on('keydown', '.only-int', function (e) {
    if (!((e.key >= '0' && e.key <= '9') ||
        [8, 9, 13, 27, 46, 37, 38, 39, 40].includes(e.keyCode))) {
        e.preventDefault();
    }
});

document.addEventListener("DOMContentLoaded", function () {

    // All forms disable autocomplete
    document.querySelectorAll("form").forEach(function (form) {
        form.setAttribute("autocomplete", "off");
    });

    // All input & textarea disable autocomplete
    document.querySelectorAll("input, textarea").forEach(function (el) {
        el.setAttribute("autocomplete", "off");
    });

});

// function setSelect2Readonly(selector, state) {
//     const el = $(selector);
//     el.toggleClass('select2-readonly', state);

// }


function setSelect2Readonly(selector, state) {
    const $select = $(selector);
    const $container = $select.next('.select2-container');
    const $selection = $container.find('.select2-selection');
    const $wrapper = $select.closest('.otherselectwidth');
    if (state) {
        // mark readonly
        $select.data('s2-readonly', true);

        // stop opening
        $select.on('select2:opening.s2readonly', function (e) {
            e.preventDefault();
        });

        // remove from TAB order
        $selection.attr('tabindex', '-1');

        // UI readonly
        $container.addClass('select2-readonly');
        $wrapper.find('.plus-icon').addClass('disabled-icon');

    } else {
        // remove readonly

        $select.data('s2-readonly', false);

        $select.off('select2:opening.s2readonly');

        // restore TAB
        $selection.attr('tabindex', '0');

        $container.removeClass('select2-readonly');
        $wrapper.find('.plus-icon').removeClass('disabled-icon');
    }
}

// Radio button readonly function
// function setRadioReadonly(selector, enable) {

//     const $radios = jQuery(selector);

//     $radios.off('.readonly');

//     if (enable) {

//         let selectedValue = $radios.filter(':checked').val();

//         $radios.on('click.readonly keydown.readonly', function (e) {
//             e.preventDefault();
//         });

//         $radios.on('change.readonly', function () {
//             $radios.prop('checked', false);
//             $radios.filter('[value="' + selectedValue + '"]').prop('checked', true);
//         });



//     } else {
//         $radios.off('.readonly');
//     }
// }

function setRadioReadonly(selector, enable) {
    const $radios = jQuery(selector);
    $radios.off('.readonly');
    if (enable) {
        let selectedValue = $radios.filter(':checked').val();
        $radios.addClass('skip-tab');

        $radios.on('click.readonly keydown.readonly', function (e) {
            e.preventDefault();
        });

        $radios.on('change.readonly', function () {
            $radios.prop('checked', false);
            $radios.filter('[value="' + selectedValue + '"]').prop('checked', true);
        });

    } else {
        $radios.off('.readonly');
        $radios.removeClass('skip-tab');
    }
}

function setCheckboxReadonly(selector, isReadonly) {
    if (isReadonly) {
        jQuery(selector).prop('readonly', true).css('pointer-events', 'none').attr('tabindex', '-1').addClass('skip-tab');
    } else {
        jQuery(selector).prop('readonly', false).css('pointer-events', 'auto').attr('tabindex', '0').removeClass('skip-tab');
    }
}

var uploadURL = "";
uploadURL = jQuery('#upload_url').val();


function setSelect2Required(selector, isRequired) {
    var $el = jQuery(selector);

    if (isRequired) {
        $el.prop('required', true)
            .removeClass('skip-tab')
            .attr('aria-required', 'true');
    } else {
        $el.prop('required', false)
            .addClass('skip-tab')
            .removeAttr('aria-required');
    }
}

// focus in search
$(document).on('select2:open', () => {
    requestAnimationFrame(() => {
        const input = document.querySelector('.select2-search__field');
        if (input) input.focus();
    });
});

//for table adjust
function fixDataTableColumnsUntilAdjusted(dt, retries = 15) {
    let attempts = 0;
    let timer = setInterval(function () {
        attempts++;

        try {
            dt.columns.adjust().draw(false);
        } catch (e) { }

        let h = jQuery(dt.table().node()).height();
        let w = jQuery(dt.table().node()).width();

        if ((h > 0 && w > 0) || attempts >= retries) {
            clearInterval(timer);
        }

    }, 200);
}

// jQuery(".current-date-picker:not([readonly])").datepicker({
//     dateFormat: "dd/mm/yy",
//     minDate: '0'
// });

// jQuery(".future-date-picker:not([readonly])").datepicker({
//     dateFormat: "dd/mm/yy",
//     minDate: '+1d'
// });

// date validation

function parseDate(dateStr) {
    // expected format: dd/mm/yyyy
    let parts = dateStr.split('/');

    if (parts.length !== 3) return null;

    let day = parseInt(parts[0], 10);
    let month = parseInt(parts[1], 10) - 1; // JS month 0-based
    let year = parseInt(parts[2], 10);

    return new Date(year, month, day);
}

(function () {
    $(document).on('shown.bs.modal show.bs.modal', '.modal', function () {
        let $modal = $(this);
        $modal.find('.js-example-basic-single').each(function () {
            let $select = $(this);
            // prevent duplicate init
            if (!$select.hasClass('skip-tab')) {
                $select.select2({
                    dropdownParent: $modal,
                    width: '100%',
                    minimumResultsForSearch: 0
                });
            }
            // 🔒 READONLY LOGIC BASED ON CLASS
            if ($select.hasClass('skip-tab')) {
                setSelect2Readonly($select, true);
                setRadioReadonly($select, true);
            } else {
                setSelect2Readonly($select, false);
                setRadioReadonly($select, false);
            }
        });
    });
})();

jQuery(".report-date-picker:not([readonly])").datepicker({

    dateFormat: "dd/mm/yy",
    autoclose: true,
    changeYear: true,              // Allow year dropdown
    changeMonth: true,             // Allow month dropdown (optional)
    yearRange: "2000:4000",
    onClose: function () {
        this.focus()
    },

});

if (jQuery(".report-date-picker:not(.no-fill)").val() == "") {
    jQuery(".report-date-picker:not(.from-april)").datepicker("setDate", "today");
}

function DataYearWise() {

    var startDateStr = jQuery('#def_year_startdate').val(); // "2026-04-01"
    var endDateStr = jQuery('#def_year_enddate').val();   // "2027-03-31"

    var startDate = new Date(startDateStr);
    var endDate = new Date(endDateStr);
    var today = new Date();

    function formatDate(date) {
        let d = String(date.getDate()).padStart(2, '0');
        let m = String(date.getMonth() + 1).padStart(2, '0');
        let y = date.getFullYear();
        return `${d}/${m}/${y}`;
    }

    var formattedStartDate = formatDate(startDate);
    var formattedEndDate = formatDate(endDate);
    var formattedToday = formatDate(today);

    // Set From Date always
    jQuery('.report-date-picker.from-april')
        .datepicker('setDate', formattedStartDate);

    // Check if today is within FY range
    if (today >= startDate && today <= endDate) {
        jQuery('.report-date-picker:not(.from-april)')
            .datepicker('setDate', formattedToday);
    } else {
        jQuery('.report-date-picker:not(.from-april)')
            .datepicker('setDate', formattedEndDate);
    }
}




// details common use new code start
function DetailsActionDropdown(editFn, deleteFn = null) {

    let deleteBtnHtml = '';

    if (deleteFn) {
        deleteBtnHtml = `
        <li>
            <a class="dropdown-item remove-item-btn" onclick="${deleteFn}(this)">
                <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
            </a>
        </li>`;
    }

    return `
        <div >
            <button class="btn btn-soft-secondary btn-sm dropdown" type="button"id="dropdownMenuLink1" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ri-more-fill align-middle"></i>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink1">
                <li>
                    <a class="dropdown-item edit-item-btn" onclick="${editFn}(this)">
                        <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                    </a>
                </li>
                ${deleteBtnHtml}
            </ul>
        </div>`;
}
// details common use new code end

// ==================== GLOBAL FIXEDHEADER FIX FOR ALL DATATABLES ====================
// Runs on every DataTable initialization (perfect for serverSide + scrollX)
$(document).on('init.dt', function (e, settings) {
    var api = new $.fn.dataTable.Api(settings);

    if (api.fixedHeader) {
        setTimeout(function () {
            api.columns.adjust();  // ← Sync widths first
            api.fixedHeader.adjust();
        }, 350);  // 350ms for Velzon load
    }
});

// Backup on every draw (including the first one)
$(document).on('draw.dt', function (e, settings) {
    var api = new $.fn.dataTable.Api(settings);

    if (api.fixedHeader) {
        setTimeout(function () {
            api.columns.adjust();  // ← Sync widths
            api.fixedHeader.adjust();
        }, 150);
    }
});

// On window resize
$(window).on('resize', function () {
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().fixedHeader.adjust();
});


function parseDate(dateStr) {
    let parts = dateStr.split('/');
    return new Date(parts[2], parts[1] - 1, parts[0]); // yyyy, mm-1, dd
}

// function focusPendingButton(modalSelector, delay = 1000) {

//     // setTimeout(() => {
//     //     const $pendingBtn = jQuery(modalSelector)
//     //         .find('.toggleModalBtn, #pending_btn')
//     //         .filter(':visible')
//     //         .not(':disabled')
//     //         .first();

//     //     if ($pendingBtn.length) {
//     //         $pendingBtn.focus();

//     //         // Green highlight (તમારા custom.js જેવું જ)
//     //         $pendingBtn.css({
//     //             'border-color': '#22b378 !important',
//     //             'outline-offset': '2px',
//     //             'box-shadow': '0 0 0 0.25rem rgba(34, 179, 120, 0.25)'
//     //         });

//     //         // 1.8 સેકન્ડ પછી હાઈલાઈટ દૂર થઈ જાય
//     //         // setTimeout(() => {
//     //         //     $pendingBtn.css({
//     //         //         'border-color': '',
//     //         //         'outline-offset': '',
//     //         //         'box-shadow': ''
//     //         //     });
//     //         // }, 1800);

//     //         $pendingBtn.on('blur', function () {
//     //             jQuery(this).css({
//     //                 'border-color': '',
//     //                 'outline-offset': '',
//     //                 'box-shadow': ''
//     //             });
//     //         });

//     //     }
//     // }, delay);
//     setTimeout(() => {
//         const $pendingBtn = jQuery(modalSelector)
//             .find('.toggleModalBtn, #pending_btn')
//             .filter(':visible')
//             .not(':disabled')
//             .first();

//         if ($pendingBtn.length) {
//             $pendingBtn.focus();

//             // Green highlight (તમારા custom.js જેવું જ)
//             $pendingBtn.css({
//                 'border-color': '#22b378 !important',
//                 'outline-offset': '2px',
//                 'box-shadow': '0 0 0 0.25rem rgba(34, 179, 120, 0.25)'
//             });

//             // 1.8 સેકન્ડ પછી હાઈલાઈટ દૂર થઈ જાય
//             // setTimeout(() => {
//             //     $pendingBtn.css({
//             //         'border-color': '',
//             //         'outline-offset': '',
//             //         'box-shadow': ''
//             //     });
//             // }, 1800);

//             $pendingBtn.on('blur', function () {
//                 jQuery(this).css({
//                     'border-color': '',
//                     'outline-offset': '',
//                     'box-shadow': ''
//                 });
//             });

//         }
//     }, delay);
// }
// function focusPendingButton(modalSelector) {
//     const $btn = jQuery(modalSelector)
//         .find('.toggleModalBtn, #pending_btn')
//         .first();

//     if (!$btn.length) return;

//     const observer = new MutationObserver(() => {
//         if (!$btn.is(':disabled') && $btn.is(':visible')) {

//             $btn.focus();

//             // highlight
//             $btn.css({
//                 'border-color': '#22b378',
//                 'outline-offset': '2px',
//                 'box-shadow': '0 0 0 0.25rem rgba(34, 179, 120, 0.25)'
//             });

//             $btn.on('blur', function () {
//                 jQuery(this).css({
//                     'border-color': '',
//                     'outline-offset': '',
//                     'box-shadow': ''
//                 });
//             });

//             observer.disconnect(); // stop after done
//         }
//     });

//     observer.observe($btn[0], {
//         attributes: true,
//         attributeFilter: ['disabled']
//     });
// }
// function focusPendingButton(modalSelector, delay = 1000) {

//     const $modal = jQuery(modalSelector);

//     // 🔥 Bootstrap focus override fix
//     $modal.attr('tabindex', '-1');

//     // 👇 INTERVAL instead of timeout (continuous check)
//     const interval = setInterval(() => {

//         const $pendingBtn = $modal
//             .find('.toggleModalBtn, #pending_btn')
//             .filter(':visible')
//             .not(':disabled')
//             .first();

//         if ($pendingBtn.length) {

//             // ✅ focus fix
//             $pendingBtn.trigger('focus');

//             // ✅ highlight
//             $pendingBtn.css({
//                 'border-color': '#22b378',
//                 'outline-offset': '2px',
//                 'box-shadow': '0 0 0 0.25rem rgba(34, 179, 120, 0.25)'
//             });

//             $pendingBtn.on('blur', function () {
//                 jQuery(this).css({
//                     'border-color': '',
//                     'outline-offset': '',
//                     'box-shadow': ''
//                 });
//             });

//             // ✅ stop interval once done
//             clearInterval(interval);
//         }

//     }, 300); // fast check (no delay feel)

//     // ❗ modal close → cleanup
//     $modal.on('hidden.bs.modal', function () {
//         clearInterval(interval);
//     });
// }

function validatePercentage(input) {
    let value = parseFloat(input.value) || 0;

    if (value > 100) {
        toastr.error("Please enter a value less than or equal to 100.");
        return;
    }

}
function focusPendingButton(modalSelector) {

    const $modal = jQuery(modalSelector);

    $modal.off('shown.bs.modal.focusPending');

    $modal.on('shown.bs.modal.focusPending', function () {

        const modal = jQuery(this);

        modal.attr('tabindex', '-1');

        runFocus(modal);

        // 🔥 detect form reset / changes
        modal.find('form').off('reset.focusPending').on('reset.focusPending', function () {
            setTimeout(() => {
                runFocus(modal);
            }, 100);
        });

    });

    function runFocus(modal) {

        const interval = setInterval(() => {

            const $pendingBtn = modal
                .find('.toggleModalBtn, #pending_btn')
                .filter(':visible')
                .not(':disabled')
                .first();

            if ($pendingBtn.length) {

                $pendingBtn[0].focus();

                $pendingBtn.css({
                    'border-color': '#22b378',
                    'outline-offset': '2px',
                    'box-shadow': '0 0 0 0.25rem rgba(34, 179, 120, 0.25)'
                });

                $pendingBtn.off('blur.focusPending').on('blur.focusPending', function () {
                    jQuery(this).css({
                        'border-color': '',
                        'outline-offset': '',
                        'box-shadow': ''
                    });
                });

                clearInterval(interval);
            }

        }, 200);

        $modal.off('hidden.bs.modal.focusPending')
            .on('hidden.bs.modal.focusPending', function () {
                clearInterval(interval);
            });
    }
}

/*
|--------------------------------------------------------------------------
| Common Suggestion AJAX Function - Production Ready
|--------------------------------------------------------------------------
*/

window.suggestionAjaxStore = {};
window.suggestionLastSearchStore = {};
window.suggestionRequestStore = {};

window.commonSuggestionAjax = function (config) {

    // // Ignore navigation and control keys on keyup event
    // const e = window.event;
    // if (e && e.type === 'keyup') {
    //     const ignoredKeyCodes = [
    //         9,   // Tab
    //         16,  // Shift
    //         17,  // Control
    //         18,  // Alt
    //         20,  // CapsLock
    //         27,  // Escape
    //         13,  // Enter
    //         33, 34, 35, 36, // PageUp, PageDown, End, Home
    //         37, 38, 39, 40  // Arrow keys (Left, Up, Right, Down)
    //     ];
    //     if (ignoredKeyCodes.indexOf(e.keyCode) !== -1) {
    //         return;
    //     }
    // }

    const settings = jQuery.extend({

        inputElement: null,
        listSelector: '',
        url: '',
        searchKey: 'term',
        loaderClass: 'file-loader',
        minLength: 1,
        method: 'GET',
        dataType: 'json',
        extraData: {},

        // Response Keys
        responseCodeKey: 'response_code',
        responseListKey: null,
        responseMessageKey: 'response_message',

        successCallback: null,
        errorCallback: null

    }, config);

    const $input = jQuery(settings.inputElement);
    const $list = jQuery(settings.listSelector);

    // Validation
    if (!$input.length) {
        console.error('Input element not found');
        return;
    }

    if (!$list.length) {
        console.error('Suggestion list element not found');
        return;
    }

    if (!settings.url) {
        console.error('Suggestion URL missing');
        return;
    }

    const search = jQuery.trim($input.val());

    // Unique key
    const uniqueKey =
        settings.listSelector + '_' + $input.attr('id');

    // Empty Search
    if (search.length < settings.minLength) {

        window.suggestionLastSearchStore[uniqueKey] = '';

        if (
            window.suggestionAjaxStore[uniqueKey] &&
            window.suggestionAjaxStore[uniqueKey].readyState !== 4
        ) {
            window.suggestionAjaxStore[uniqueKey].abort();
        }

        $input.removeClass(settings.loaderClass);
        $list.empty();

        return;
    }

    // Prevent same API call
    if (
        window.suggestionLastSearchStore[uniqueKey] === search
    ) {
        return;
    }

    window.suggestionLastSearchStore[uniqueKey] = search;

    // Abort old request
    if (
        window.suggestionAjaxStore[uniqueKey] &&
        window.suggestionAjaxStore[uniqueKey].readyState !== 4
    ) {
        window.suggestionAjaxStore[uniqueKey].abort();
    }

    // Request tracking
    window.suggestionRequestStore[uniqueKey] =
        (window.suggestionRequestStore[uniqueKey] || 0) + 1;

    const currentRequestId =
        window.suggestionRequestStore[uniqueKey];

    $input.addClass(settings.loaderClass);

    // AJAX Call
    window.suggestionAjaxStore[uniqueKey] = jQuery.ajax({

        url: settings.url,
        type: settings.method,
        dataType: settings.dataType,

        data: {
            [settings.searchKey]: search,
            ...settings.extraData
        },

        success: function (response) {

            // Ignore old response
            if (
                currentRequestId !==
                window.suggestionRequestStore[uniqueKey]
            ) {
                return;
            }

            $input.removeClass(settings.loaderClass);

            if (
                response &&
                response[settings.responseCodeKey] == 1
            ) {

                // Dynamic response list key
                let responseListKey =
                    settings.responseListKey;

                // Auto detect response key
                if (!responseListKey) {

                    responseListKey =
                        Object.keys(response).find(function (key) {

                            return (
                                key !== settings.responseCodeKey &&
                                key !== settings.responseMessageKey
                            );
                        });
                }

                $list.html(
                    response[responseListKey] || ''
                );

                if (
                    typeof settings.successCallback === 'function'
                ) {
                    settings.successCallback(response);
                }

            } else {

                $list.empty();

                if (
                    response &&
                    response[settings.responseMessageKey]
                ) {
                    // toastr.error(
                    //     response[settings.responseMessageKey]
                    // );
                }
            }
        },

        error: function (jqXHR, textStatus) {

            // Ignore abort
            if (textStatus === 'abort') {
                return;
            }

            // Ignore old response
            if (
                currentRequestId !==
                window.suggestionRequestStore[uniqueKey]
            ) {
                return;
            }

            $input.removeClass(settings.loaderClass);
            $list.empty();

            // Custom Error Callback
            if (
                typeof settings.errorCallback === 'function'
            ) {
                settings.errorCallback(jqXHR, textStatus);
                return;
            }

            if (jqXHR.status == 401) {

                toastr.error(jqXHR.statusText);

            } else {

                toastr.error('Something went wrong!');
                console.error(jqXHR.responseText);
            }
        }
    });
};

// Generic Suggestion List interactions (Keyboard Navigation & Click Outside closing)
jQuery(document).on('click mousedown focusin', function (e) {
    var $target = jQuery(e.target);
    jQuery('.list-group-item[list-id]').each(function () {
        var listId = jQuery(this).attr('list-id');
        var parentId = jQuery(this).attr('parent-id');
        if (listId && parentId) {
            var $list = jQuery('#' + listId);
            var $input = jQuery('#' + parentId);
            if (!$target.is($input) && !$target.closest($list).length) {
                $list.html('');
            }
        }
    });
});

jQuery(document).on('keydown', 'input', function (e) {
    if (e.which === 40) { // Arrow Down
        var inputId = this.id;
        if (inputId) {
            var firstItem = jQuery('.list-group-item[parent-id="' + inputId + '"]').first();
            if (firstItem.length) {
                e.preventDefault();
                firstItem.focus();
            }
        }
    }
});

jQuery(document).on('click', '.list-group-item', function (e) {
    var suggest = jQuery(this).text();
    var parentId = jQuery(this).attr('parent-id');
    var listId = jQuery(this).attr('list-id');
    if (parentId && listId) {
        var $input = jQuery('#' + parentId);
        if ($input.length) {
            $input.val(suggest).focus();
        }

        // Prevent immediate re-triggering of suggestion ajax
        var storeKey = '#' + listId + '_' + parentId;
        window.suggestionLastSearchStore[storeKey] = suggest;

        jQuery('#' + listId).html('');
    }
});

jQuery(document).on('keydown', '.list-group-item', function (e) {
    var $this = jQuery(this);
    var parentId = $this.attr('parent-id');
    var listId = $this.attr('list-id');

    if (e.which === 13 || e.which === 32) { // Enter or Space
        e.preventDefault();
        var suggest = $this.text();
        if (parentId && listId) {
            jQuery('#' + parentId).val(suggest).focus();

            // Prevent immediate re-triggering of suggestion ajax
            var storeKey = '#' + listId + '_' + parentId;
            window.suggestionLastSearchStore[storeKey] = suggest;

            jQuery('#' + listId).html('');
        }
    } else if (e.which === 40) { // Arrow Down
        e.preventDefault();
        var $next = $this.next('.list-group-item');
        if ($next.length) {
            $next.focus();
        }
    } else if (e.which === 38) { // Arrow Up
        e.preventDefault();
        var $prev = $this.prev('.list-group-item');
        if ($prev.length) {
            $prev.focus();
        } else {
            if (parentId) {
                jQuery('#' + parentId).focus();
            }
        }
    }
});

// Open Select2 and start searching when typing on a focused Select2 dropdown
jQuery(document).on('keydown', '.select2-selection.select2-selection--single', function (e) {
    // Only intercept single printable characters (exclude control keys like Tab, Enter, Esc, Ctrl, Alt, Arrow keys)
    if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
        const $select = jQuery(this).closest('.select2-container').prev('select');
        if ($select.length && !$select.prop('disabled')) {
            e.preventDefault();
            e.stopPropagation();

            // Open the Select2 dropdown
            $select.select2('open');

            // Retrieve the pressed key
            const pressedKey = e.key;

            // Focus the search input and set the initial typed character
            setTimeout(function () {
                const $searchField = jQuery('.select2-container--open .select2-search__field');
                if ($searchField.length) {
                    $searchField.val(pressedKey).trigger('input');
                }
            }, 50);
        }
    }
});

// Prevent focus on Select2 if it is readonly, disabled, or skip-tab
jQuery(document).on('focusin focus', '.select2-selection', function () {
    const $select = jQuery(this).closest('.select2-container').prev('select');
    const isSkip = $select.length && ($select.hasClass('skip-tab') || $select.prop('readonly') || $select.attr('readonly') || $select.prop('disabled') || $select.data('s2-readonly'));
    if (isSkip) {
        jQuery(this).attr('tabindex', '-1').blur();
    }
});

// Automatically apply tabindex="-1" to Select2 dropdowns matching skip-tab/readonly
function syncSelect2Tabindex() {
    jQuery('select').each(function () {
        const $select = jQuery(this);
        const isSkip = $select.hasClass('skip-tab') ||
            $select.prop('readonly') ||
            $select.attr('readonly') ||
            $select.prop('disabled') ||
            $select.data('s2-readonly');

        let $container = $select.next('.select2-container');
        if (!$container.length) {
            $container = $select.siblings('.select2-container');
        }

        if ($container.length) {
            const $selection = $container.find('.select2-selection');
            if (isSkip) {
                $selection.attr('tabindex', '-1');
            } else {
                if ($selection.attr('tabindex') === '-1') {
                    $selection.attr('tabindex', '0');
                }
            }
        }
    });
}

// Sync on Tab keypress to catch dynamically updated fields before focus moves
jQuery(document).on('keydown', function (e) {
    if (e.key === 'Tab') {
        syncSelect2Tabindex();
    }
});

// Keydown handler to navigate table checkboxes/radio buttons row-by-row on Tab keypress
jQuery(document).on('keydown', '.modal table input[type="radio"], .modal table input[type="checkbox"]', function (e) {
    if (e.key === 'Tab') {
        const $current = jQuery(this);
        const $modal = $current.closest('.modal');

        // Find all visible checkboxes and radio buttons in the table body (tbody) inside the modal
        const $inputs = $modal.find('tbody input[type="radio"], tbody input[type="checkbox"]').filter(':visible');

        // 1. If we are on the "checkall" checkbox in the thead
        if ($current.closest('thead').length) {
            if (!e.shiftKey) {
                // Tab from header: go directly to the first checkbox/radio in the table body
                if ($inputs.length > 0) {
                    e.preventDefault();
                    $inputs.first().focus();
                }
            }
            return;
        }

        // 2. If we are on a radio button in the tbody
        if ($current.is('input[type="radio"]')) {
            const index = $inputs.index($current);
            if (index !== -1) {
                if (e.shiftKey) {
                    // Shift+Tab: focus previous checkbox/radio in the table body
                    if (index > 0) {
                        e.preventDefault();
                        $inputs.eq(index - 1).focus();
                    } else {
                        // Shift+Tab on first row: focus the checkall checkbox in the thead
                        const $checkall = $modal.find('thead input[type="checkbox"]').filter(':visible');
                        if ($checkall.length > 0) {
                            e.preventDefault();
                            $checkall.first().focus();
                        }
                    }
                } else {
                    // Tab: focus next checkbox/radio in the table body, or skip pagination to footer
                    if (index < $inputs.length - 1) {
                        e.preventDefault();
                        $inputs.eq(index + 1).focus();
                    } else {
                        // Last radio button: skip pagination and go directly to Submit/close buttons
                        const $submitBtn = $modal.find('.modal-footer').find('button, a').filter(':visible').not(':disabled').first();
                        if ($submitBtn.length > 0) {
                            e.preventDefault();
                            $submitBtn.focus();
                        }
                    }
                }
            }
            return;
        }

        // 3. If we are on a checkbox in the tbody
        if ($current.is('input[type="checkbox"]')) {
            const index = $inputs.index($current);
            if (index !== -1) {
                if (e.shiftKey && index === 0) {
                    // Shift+Tab on the very FIRST checkbox in the tbody: focus the checkall checkbox in the thead
                    const $checkall = $modal.find('thead input[type="checkbox"]').filter(':visible');
                    if ($checkall.length > 0) {
                        e.preventDefault();
                        $checkall.first().focus();
                    }
                } else if (!e.shiftKey && index === $inputs.length - 1) {
                    // Tab on the very LAST checkbox in the tbody: skip pagination and focus footer button
                    const $submitBtn = $modal.find('.modal-footer').find('button, a').filter(':visible').not(':disabled').first();
                    if ($submitBtn.length > 0) {
                        e.preventDefault();
                        $submitBtn.focus();
                    }
                }
                // Otherwise, let the browser's native Tab/Shift+Tab navigate naturally!
            }
        }
    }
});

// Keydown handler to navigate backward from modal footer button directly to the last checkbox/radio button
jQuery(document).on('keydown', '.modal .modal-footer button, .modal .modal-footer a', function (e) {
    if (e.key === 'Tab' && e.shiftKey) {
        const $current = jQuery(this);
        const $modal = $current.closest('.modal');

        // Find if the current button is the FIRST visible, enabled element in the footer
        const $footerBtns = $modal.find('.modal-footer').find('button, a').filter(':visible').not(':disabled');
        if ($footerBtns.length && $current.is($footerBtns.first())) {
            // Shift+Tab on the first footer button: go directly to the last input in the table body (skipping pagination)
            const $inputs = $modal.find('tbody input[type="radio"], tbody input[type="checkbox"]').filter(':visible');
            if ($inputs.length > 0) {
                e.preventDefault();
                $inputs.last().focus();
            }
        }
    }
});

// Apply green focus styles programmatically to checkbox/radio inputs inside modals on focus and clear on blur
jQuery(document).on('focus', '.modal input[type="radio"], .modal input[type="checkbox"]', function () {
    jQuery(this).css({
        'border-color': '#22b378',
        'outline-offset': '2px',
        'box-shadow': '0 0 0 0.25rem rgba(34, 179, 120, 0.25)'
    });
}).on('blur', '.modal input[type="radio"], .modal input[type="checkbox"]', function () {
    jQuery(this).css({
        'border-color': '',
        'outline-offset': '',
        'box-shadow': ''
    });
});

// Automatically ensure all checkbox and radio inputs inside modals have the form-check-input class
jQuery(document).on('show.bs.modal shown.bs.modal draw.dt init.dt', function () {
    jQuery('.modal input[type="radio"], .modal input[type="checkbox"]').addClass('form-check-input');
});
jQuery(document).on('focus click mouseenter', '.modal input[type="radio"], .modal input[type="checkbox"]', function () {
    jQuery(this).addClass('form-check-input');
});

jQuery(document).on('shown.bs.modal', '.modal', function () {
    setTimeout(syncSelect2Tabindex, 150);
});

jQuery(document).ready(function () {
    setTimeout(syncSelect2Tabindex, 500);
});

// Global DataTable Transaction Ordering (Date and Sequence DESC)
jQuery(document).on('preInit.dt', function (e, settings) {
    // Pending lists, Reports, and Summaries should be excluded
    let url = window.location.href.toLowerCase();
    if (url.includes('pending') || url.includes('pend') || url.includes('summary') || url.includes('report')) {
        return;
    }

    if (settings.aoColumns && settings.aoColumns.length > 0) {
        let dateColIndex = -1;
        let seqColIndex = -1;

        for (let i = 0; i < settings.aoColumns.length; i++) {
            let col = settings.aoColumns[i];
            let dataProp = String(col.data || '');
            let nameProp = String(col.name || '');

            // Identify main transaction date column (e.g., po_date, created_on, etc.)
            if (dateColIndex === -1) {
                if (dataProp.endsWith('_date') || nameProp.endsWith('_date') || dataProp === 'date' || nameProp === 'date' || dataProp === 'created_on' || nameProp === 'created_on') {
                    // Exclude delivery, dispatch, validity, expiry dates
                    if (!dataProp.includes('ref_no') && !dataProp.includes('del_') && !dataProp.includes('delivery') && !dataProp.includes('dispatch') && !dataProp.includes('expiry') && !dataProp.includes('challan') && !dataProp.includes('validity') && !dataProp.includes('last_on')) {
                        dateColIndex = i;
                    }
                }
            }

            // Identify transaction sequence column (ends with _sequence)
            if (seqColIndex === -1) {
                if (dataProp.endsWith('_sequence') || nameProp.endsWith('_sequence')) {
                    seqColIndex = i;
                }
            }
        }

        // If both date and sequence columns are found, override default sorting
        if (dateColIndex !== -1 && seqColIndex !== -1) {
            settings.aaSorting = [
                [dateColIndex, 'desc'],
                [seqColIndex, 'desc']
            ];
        }
    }
});
const pageRelod = window.location.origin == 'https://sitasstaging.cbswebtech.com' ? "Yes" : "No";