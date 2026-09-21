var formId = jQuery('#commonCompanyForm').find('input:hidden[name="id"]').val();

jQuery(document).ready(function () {
    var headerOpt = { 'Authorization': 'Bearer {{ Auth::user()->auth_token }}' };
});

function validateImage(filePath) {
var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i;
if (!allowedExtensions.exec(filePath)) {
    return false;
}
return true;
}

function fileUpload(e, type = null) {

var form_data = new FormData();

// Read selected files

var target = e.target;

var id = target.id;

console.log(id)

var files = target.files;

var totalfiles = files.length;

var oldImg = jQuery('#' + id + '_doc').val();

jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');

if (totalfiles > 0) {

    var notValid = 0;

    for (var index = 0; index < totalfiles; index++) {

        if (type == 'file') {
            if (validateImage(files[index].name) == true) {

                form_data.append("docs[]", files[index]);

            } else {

                notValid = 1;

                toastError("Only Image files are allowed.");

                e.stopImmediatePropagation();

                return false;

            }

        } else {

            if (validateImage(files[index].name) == true) {

                form_data.append("docs[]", files[index]);

            } else {

                notValid = 1;

                toastError("Only Image files are allowed.");

                e.stopImmediatePropagation();

                return false;

            }
        }

    }

    if (notValid == 0) {

        jQuery('#' + id).parent().parent().parent().find('.uneditable-input').addClass('file-loader');

        jQuery.ajax({

            url: RouteBasePath + "/upload-docs",

            type: 'POST',

            data: form_data,

            headers: headerOpt,

            dataType: 'json',

            processData: false,

            contentType: false,


            success: function (data) {

                jQuery('#' + id).parent().parent().parent().find('.uneditable-input').removeClass('file-loader');

                if (data.response_code == 1) {

                    if (oldImg != "") {

                        removeMedia(oldImg);

                    }

                    jQuery('#' + id + '_doc').val(data.files);

                    jQuery('#' + id + '_prev').attr('href', data.files_url);

                    jQuery('#' + id + '_prev').removeClass('hidden');

                    jQuery('#' + id + '_img-prev-box').html(`<img class="img-polaroid img" alt="image preview" src="${data.files_url}"/>`);

                    jQuery('#' + id + '_img-prev-box').removeClass('hidden');

                    jQuery('#' + id + '_remove').addClass('i-block').removeClass('hidden');


                } else {

                    console.log(data.response_message);

                }

            },

            error: function (jqXHR, textStatus, errorThrown) {

                jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('file-loader');

                jQuery('#' + id).parent().parent().find('.uneditable-input').addClass('iconfa-warning-sign upload-error');

                var errMessage = JSON.parse(jqXHR.responseText);

                if (errMessage.errors) {

                    Validator.showErrors(errMessage.errors);

                } else if (jqXHR.status == 401) {

                    toastError(jqXHR.statusText);

                } else {

                    toastError('Something went wrong!');

                    console.log(JSON.parse(jqXHR.responseText));

                }

            }

        });

    }

} else {

    if (oldImg != "") {

        removeMedia(oldImg);

    }

    jQuery('#' + id + '_doc').val('');

    jQuery('#' + id + '_prev').attr('href', '#');

    jQuery('#' + id + '_prev').addClass('hidden');

    jQuery('#' + id + '_img-prev-box').addClass('hidden');

    jQuery('#' + id + '_img-prev-box').html('');

    jQuery('#' + id + '_remove').removeClass('i-block').addClass('hidden');

}

}


function removeFile(e, type = null) {

e.stopImmediatePropagation();

jConfirm('Are you sure you want <lw-c>to</lw-c> Delete ?', 'Confirmation', function (r) {

    if (r === true) {

        jQuery(".icon").removeClass(".iconfa-file");

        var target = e.target;

        var id = target.getAttribute("data-remove");

        var fileName = jQuery('#' + id + '_doc').val();

        var oldImg = jQuery('#' + id + '_doc').val();



        jQuery('#' + id).parent().parent().find('.uneditable-input').removeClass('iconfa-warning-sign upload-error');



        if (oldImg != "" && type != 'soft') {

            removeMedia(oldImg)

        }

        if (type == 'soft') {
            jQuery('#' + id + '_soft_delete').val(oldImg);
        }

        jQuery('#' + id + '_doc').val('');

        jQuery('#' + id + '_prev').attr('href', '#');

        jQuery('#' + id + '_prev').addClass('hidden');

        jQuery('#' + id + '_remove').removeClass('i-block').addClass('hidden');

        jQuery('#' + id + '_img-prev').html('');

        jQuery('#' + id + '_img-prev-box').addClass('hidden');

        jQuery('#' + id + '_img-prev-box').html('');

    }

});

}

function removeMedia(docName) {

let form_data2 = new FormData();

form_data2.append('docs[]', docName);

jQuery.ajax({

    url: RouteBasePath + "/remove-docs",

    type: 'POST',

    data: form_data2,

    headers: headerOpt,

    dataType: 'json',

    processData: false,

    contentType: false,

    success: function (data) {

        if (data.response_code == 1) {

            console.log(data.response_message);

        } else {

            console.log(data.response_message);

        }

    },

    error: function (jqXHR, textStatus, errorThrown) {

        var errMessage = JSON.parse(jqXHR.responseText);

        if (errMessage.errors) {

            Validator.showErrors(errMessage.errors);

        } else if (jqXHR.status == 401) {

            toastError(jqXHR.statusText);

        } else {

            toastError('Something went wrong!');

            console.log(JSON.parse(jqXHR.responseText));

        }

    }

});

}
