@extends('layouts.master')
@section('title') User Access @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Admin @endslot
@slot('title')User Access @endslot
@endcomponent

<div class="row">
            <div class="card">
              
                <div class="card-body">
                    
                    <div class="live-preview">
                       <form id="editUserAccessForm" class="stdform" method="post">
                        <input type="hidden" name="pageName" id="pageName" value="user_acess">
                            @csrf
                            <div class="row mb-3 d-flex justify-content-between">
                                <div class="col-lg-6 d-flex">
                                    <div class="col-lg-2">
                                        <label for="user_id" class="form-label col-form-label">User</label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select class="js-example-basic-single zindexnotapply" data-placeholder="Select User" id="user_id" name="user_id" required>
                                            <option value="">Select User</option>
                                                @forelse ($users as $user)
                                                    <option value="{{ $user->id }}" >{{ $user->user_name }}</option>
                                                    @empty
                                                @endforelse   
                                                        
                                        </select>
                                        <div class="invalid-tooltip">
                                            Please Select Unit
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6 d-flex justify-content-end">
                                    <div class="col-lg-3">
                                        <label for="copy_user_id" class="form-label col-form-label">Copy Access</label>
                                    </div>
                                    <div class="col-lg-6">
                                    <select class="js-example-basic-single" data-placeholder="Select User" id="copy_user_id" name="copy_user_id">
                                        <option value="">Select User</option>
                                            @forelse ($user2 as $users)
                                                <option value="{{ $users->id }}" >{{ $users->user_name }}</option>
                                                @empty
                                            @endforelse   
                                                    
                                        </select>
                                    </div>
                                </div>
                            </div>  
                           <!-- Dynamic Accordion Will Load Here -->
                        <div id="accessTable"></div>

                            <div class="row mb-3 mt-4">
                                <div class="col-12">                                    
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>                               
                            </div>
                            
                        </form>
                    </div>
                </div>
            </div>
</div>
@endsection


@section('script-manage')
<script>


$(document).ready(function(){

var allCopyUsers = $('#copy_user_id option').clone();

var headerOpt = {'Authorization':'Bearer {{ Auth::user()->auth_token }}'};

$(document).on('change','#user_id',function(){
    let selected = jQuery(this).find('option:selected').val();
    $('#copy_user_id').empty().append(allCopyUsers.clone());
    if(selected != ""){
        $('#copy_user_id').prop('disabled',false);
        $("#copy_user_id option[value='"+selected+"']").remove();
        $('#copy_user_id').trigger('change').trigger('liszt:updated');
        resetForm();
        // console.log("asd");
        loadAccessOption(selected);
    }else{
        $('#copy_user_id').prop('disabled',false);
        $('#copy_user_id').trigger('change').trigger('liszt:updated');
        resetForm();
       $('#accessTable').empty();
    }
});

jQuery(document).on('change','#copy_user_id',function(){
    let userSelected = jQuery('#user_id').find('option:selected').val();
    let selected = jQuery(this).find('option:selected').val();
    if(selected != ""){
        if(selected == 1){ // Only admin
            resetForm(true); //This Will Uncheck all Selected Checkbox
            jQuery('#editUserAccessForm').find('input[type="checkbox"]').prop('checked',true); //For Check All Checkbox
        }else{ // Other User 
            resetForm(true);
            getAccessData(selected,true);
        }
        
    }else{
        resetForm(true);
    }
});

function resetForm(onlyForm = false){
    if(onlyForm == false){
    jQuery('#user_code_text').text('');
    }
    jQuery('input[type="checkbox"]:checked').each(function(){
        jQuery(this).prop('checked',false);
    });
}

function getAccessData(userId,forCopy = false){


    jQuery.ajax({

    url: "{{ route('get-user_access') }}?id="+userId,

    type: 'GET',

    headers: headerOpt,

    dataType: 'json',

    async: false,

    processData: false,

    success: function (data) {

        jQuery('#show-progress').removeClass('loader-progress');

        if(data.response_code == 1){ 

            if(forCopy == false)
            
                jQuery('#user_code_text').text(' - '+data.parson_name);

            // check box print
            for(dkey in data.user_access_data){

                jQuery("input[name='actions["+data.user_access_data[dkey].page+"]["+data.user_access_data[dkey].action+"]'] ").prop('checked',true);
                
                // if all action checked then check box chekced
                    
                // if(data.user_access_data[dkey].action == "1" && data.user_access_data[dkey].action == "2" && data.user_access_data[dkey].action == "3" || data.user_access_data[dkey].action == "4" || data.user_access_data[dkey].action == "5")
                // {
                    
                //     jQuery("input[name='chkname["+data.user_access_data[dkey].page+"]'] ").prop('checked',true);
                // }             

            }

         

            

             jQuery('input:checkbox[name*="actions"]').click(function(){

                    let actionCompare = ['2','3','4','5'];                    

                    let actionVal = jQuery(this).val();

                    let explArr = jQuery(this).attr('name').replace('actions','').replaceAll('][',',').replaceAll('[','').replaceAll(']','').split(',');

                    let ischecked = 0;



                if(jQuery(this).prop('checked') && jQuery.inArray(actionVal,actionCompare) != -1){
                        
                    if(jQuery.inArray(actionVal,actionCompare) != -1 ){

                        jQuery("input[name='actions["+explArr[0]+"][1]'] ").prop('checked',true);

                    }

                }else if(!jQuery(this).prop('checked') && actionVal == "1" ){

                    for(k in actionCompare){

                        if(jQuery("input[name='actions["+explArr[0]+"]["+actionCompare[k]+"]'] ").prop('checked')){

                            ischecked++;

                        }

                    }



                    if(ischecked > 0){

                        return false;

                    }

                    return true;

                }

               

            });

        }else{

            // toastError(data.response_message);
            /*jAlert(data.response_message);

            setTimeout(() => {

                window.location.href = "{{ route('manage-user')}}";

            }, 800);*/
			
			jAlert(data.response_message, 'Alert Dialog', function(r) {
				window.location.href = "{{ route('manage-user')}}";
			});

        }   

    },

    error: function (jqXHR, textStatus, errorThrown){

        jQuery('#show-progress').removeClass('loader-progress');

        var errMessage = JSON.parse(jqXHR.responseText);

        

       if(jqXHR.status == 401){

            

            jAlert(jqXHR.statusText);

        }else{

            jAlert('Something went wrong!');

            console.log(JSON.parse(jqXHR.responseText));

        }

    }

});

return false;

}


// function loadAccessOption(user_id){

// jQuery('#show-progress').addClass('loader-progress');

//     jQuery.ajax({
    
//         url: "{{ route('get-access_modules') }}",
    
//         type: 'GET',
    
//         headers: headerOpt,
    
//         dataType: 'json',
    
//         processData: false,
    
//         success: function (data) {
    
//             if(data.response_code == 1){
    
//                 var accordionTabs = data.parents;

//                   var tabHeadersCount =  [];

//                   var totalTabHeadersCount = [];

//                   for(let indx in accordionTabs){
//                     tabHeadersCount[accordionTabs[indx].module] = 0;
//                     totalTabHeadersCount[accordionTabs[indx].module] = 0;
//                   }


//                   for(let indx in accordionTabs){
//                     for(let pindx in data.pages){

//                       if(data.pages[pindx].parent == accordionTabs[indx].id){
//                         let prev = parseInt(totalTabHeadersCount[accordionTabs[indx].module]);
//                         totalTabHeadersCount[accordionTabs[indx].module] = prev+1;
//                       }
//                     }
//                   }

//                   var tableHtml = ``;

//                   var totalActions = data.actions.length+1;

//                   var thWidth = (66/parseInt(totalActions))+0;

//                   var actionsHeader = `<tr>
//                     <th width="34%">Access</th>
//                     <th width="${thWidth}%"></th>`;

//                   for(let aindx in data.actions){

//                       actionsHeader +=`<th class="org-text" width="${thWidth}%">${data.actions[aindx].display_name}</th>`

//                   }

//                   actionsHeader +=`</tr>`;
      
//                   tableHtml +=`<div class="accordion accordion-primary" id="accordionMaster">`;

//                   for(let accKey in accordionTabs){

//                     for(let key in data.pages){
    
//                         if(data.pages[key].parent == accordionTabs[accKey].id){
                          
//                             if(tabHeadersCount[accordionTabs[accKey].module] == 0){
        
//                                 tableHtml +=`
        
//                                     <h3><a href="javascript:void(0);">${accordionTabs[accKey].display_name}</a></h3>
        
//                                         <div>
        
//                                             <table class="table table-bordered responsive">
        
//                                             <thead>
        
//                                               ${actionsHeader}
        
//                                             </thead>
        
//                                             <tbody>`;
        
//                             }
//                             tabHeadersCount[accordionTabs[accKey].module] = tabHeadersCount[accordionTabs[accKey].module]+1;
        
//                           if(data.pages[key].show_in_access == "YES"){

//                           var approvalMenuDisplay = '';

                        

//                           if(data.pages[key].page == 'sm_approval' || data.pages[key].page == 'md_approval' || data.pages[key].page == 'zsm_approval'){

//                             approvalMenuDisplay =  'style="display:none;"';

//                           }

//                             tableHtml += `<tr ${approvalMenuDisplay}>`;

//                             tableHtml += `<td width="34%" class="org-text"><input type="hidden" name="pages[]" class="exp" value="${data.pages[key].id}"/>${data.pages[key].display_name}</td>`;
//                             tableHtml += `<td width="11%"><input type="checkbox" data-page="${data.pages[key].page}" id="chk_${data.pages[key].id}" name="chkname[${data.pages[key].id}]"></td>`;
//                             for(let akey in data.actions){
        
//                                 tableHtml +=`<td width="${thWidth}%">`;

//                                 if(data.pages[key].actions != "all" || data.pages[key].actions != "no"){
                                        
//                                    if(data.pages[key].actions.includes(String(data.actions[akey].id))){
//                                     tableHtml +=`<input type="checkbox" id="${data.pages[key].page}[]" name="actions[${data.pages[key].id}][${data.actions[akey].id}]" value="${data.actions[akey].id}"/>`;
//                                    }

//                                 }else{
                                   
//                                   tableHtml +=`<input type="checkbox" id="${data.pages[key].page}[]" name="actions[${data.pages[key].id}][${data.actions[akey].id}]" value="${data.actions[akey].id}"/>`;
//                                 }
        
        
//                                 tableHtml +=`</td>`;
        
//                             };
        
//                             tableHtml += `</tr>`;
        
//                           }

//                           if(tabHeadersCount[accordionTabs[accKey].module] == totalTabHeadersCount[accordionTabs[accKey].module]){
//                             tableHtml +=`</tbody></table></div>`;
//                           }
        
//                         }
//                     }
//                 }
      
//                   jQuery('#accessTable').empty().html(tableHtml+=`</tbody></table></div></div>`);
      
//                   jQuery('.accordion ').accordion({heightStyle: "content"});
                  
//                   jQuery("[id*='chk_']").click(function(){
                    
//                     var strpage = jQuery(this).attr('data-page');
                    
//                     if(jQuery(this).prop("checked")){
//                       jQuery('input[id="'+ strpage +'[]"]').each(function(){
//                         jQuery(this).prop('checked', true);
                        
//                       });	
//                     }else{
//                       jQuery('input[id="'+ strpage +'[]"]').each(function(){
//                         jQuery(this).prop('checked', false);
//                       });	
//                     }
                    
//                   });
                  
//                 getAccessData(user_id);
      
    
//             }else{
    
//                 jAlert(data.response_message);
    
//             }   
    
//         },
    
//         error: function (jqXHR, textStatus, errorThrown){
    
//             jQuery('#show-progress').removeClass('loader-progress');
//             var errMessage = JSON.parse(jqXHR.responseText);
    
            
    
//             if(jqXHR.status == 401){
    
                
    
//                 jAlert(jqXHR.statusText);
    
//             }else{
    
//                 jAlert('Something went wrong!');
    
//                 console.log(JSON.parse(jqXHR.responseText));
    
//             }
    
//         }
    
//     });
// }
function loadAccessOption(user_id) {

    $("#show-progress").addClass('loader-progress');

    $.ajax({
        url: "{{ route('get-access_modules') }}",
        type: "GET",
        headers: headerOpt,
        dataType: "json",
        processData: false,

        success: function (data) {

            if (data.response_code != 1) {
                return jAlert(data.response_message);
            }

            let parents = data.parents;
            let pages   = data.pages;
            let actions = data.actions;
            let subModules = data.sub_modules;

            let html = `
            <div class="accordion" id="accordionMaster">
            `;

            let thWidth = (66 / (actions.length + 1));

            let actionHeader = `
                <tr>
                    <th width="34%">Access</th>
                    <th width="${thWidth}%"></th>
            `;

            actions.forEach(a => {
                actionHeader += `
                    <th width="${thWidth}%" class="org-text">${a.display_name}</th>
                `;
            });

            actionHeader += `</tr>`;

            let index = 1;

            parents.forEach(parent => {

                let moduleID = "module_" + index;

                html += `
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading_${moduleID}">
                        <button class="accordion-button collapsed" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapse_${moduleID}">
                            ${parent.display_name}
                        </button>
                    </h2>

                    <div id="collapse_${moduleID}" class="accordion-collapse collapse"
                        data-bs-parent="#accordionMaster">

                        <div class="accordion-body">
                `;

                let renderPageRow = function(p) {
                    let rowHtml = `
                    <tr>
                        <td width="34%">
                            <input type="hidden" name="pages[]" value="${p.id}">
                            ${p.display_name}
                        </td>

                        <td width="11%">
                            <input type="checkbox" id="chk_${p.id}" data-page="${p.page}">
                        </td>
                    `;

                    actions.forEach(ac => {
                        let allowed =
                            p.actions == "all" ||
                            p.actions.includes(String(ac.id));

                        rowHtml += `
                            <td width="${thWidth}%">
                                ${allowed ? `
                                    <input type="checkbox" id="${p.page}[]"
                                        name="actions[${p.id}][${ac.id}]"
                                        value="${ac.id}">
                                ` : ``}
                            </td>
                        `;
                    });

                    rowHtml += `</tr>`;
                    return rowHtml;
                };

                // Filter sub modules for this module
                let parentSubModules = subModules.filter(sm => sm.module_id == parent.id);
                if (parentSubModules.length > 0) {
                    html += `<div class="accordion accordion-flush" id="accordionSub_${parent.id}">`;
                    parentSubModules.forEach(sm => {
                        let smPages = pages.filter(p => p.parent == parent.id && p.sub_module_id == sm.id && p.show_in_access == "YES");
                        if (smPages.length > 0) {
                            let subModuleCollapseID = `submodule_collapse_${parent.id}_${sm.id}`;
                            html += `
                            <div class="accordion-item" style="border: 1px solid #e9ebec; margin-bottom: 5px; border-radius: 4px; overflow: hidden;">
                                <h3 class="accordion-header" id="heading_${subModuleCollapseID}">
                                    <button class="accordion-button collapsed fs-13" type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse_${subModuleCollapseID}"
                                        style="padding: 10px 15px; background-color: #f3f3f9; color: #495057; font-weight: 600;">
                                        ${sm.display_name}
                                    </button>
                                </h3>
                                <div id="collapse_${subModuleCollapseID}" class="accordion-collapse collapse"
                                    data-bs-parent="#accordionSub_${parent.id}">
                                    <div class="accordion-body p-0">
                                        <table class="table table-bordered table-striped mb-0">
                                            <thead>${actionHeader}</thead>
                                            <tbody>
                            `;
                            smPages.forEach(p => {
                                html += renderPageRow(p);
                            });
                            html += `
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            `;
                        }
                    });
                    html += `</div>`;
                }

                // Render direct menus for this module (or all module menus if sub modules are not active)
                let directPages = pages.filter(p => p.parent == parent.id && (parentSubModules.length > 0 ? !p.sub_module_id : true) && p.show_in_access == "YES");
                if (directPages.length > 0) {
                    html += `
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>${actionHeader}</thead>
                            <tbody>
                    `;
                    directPages.forEach(p => {
                        html += renderPageRow(p);
                    });
                    html += `
                            </tbody>
                        </table>
                    </div>
                    `;
                }

                html += `
                        </div>
                    </div>
                </div>
                `;

                index++;
            });

            html += `</div>`;

            $("#accessTable").html(html);
            
            $("#accordionMaster .accordion-item:first .accordion-collapse").addClass("show");
            $("#accordionMaster .accordion-item:first .accordion-button").removeClass("collapsed");

            jQuery(document).on("change", "input[id^='chk_']", function () {
                let pageId = this.id.replace("chk_", ""); 
                let isChecked = jQuery(this).prop("checked");

                jQuery(`input[name^="actions[${pageId}]"]`).prop("checked", isChecked);
            });

            getAccessData(user_id);
        },

        error: function () {
            $("#show-progress").removeClass('loader-progress');
            jAlert("Something went wrong!");
        }
    });
}

$('#editUserAccessForm').on('submit', function (e) {
    e.preventDefault();

    let form = this;

    // Bootstrap validation
    if (!form.checkValidity()) {
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }

    let formData = $(form).serialize();

    $.ajax({
        url: "update-user_access",
        type: 'POST',
        data: formData,
        processData: true,
        contentType: "application/x-www-form-urlencoded; charset=UTF-8",
        success: function (data) {
            if (data.response_code == 1) {
                toastSuccess(data.response_message, redirectFn);
                function redirectFn() {
                    window.location.reload();
                }

            } else {
                  toastr.error(data.response_message);
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
            } else {
                 toastr.error('Something went wrong. Please try again.');
            }
        }
        
    });

});


});


setTimeout(function () {
        // Focus the select2 selection box
        let sel = jQuery('#user_id')
            .next('.select2-container')
            .find('.select2-selection');
 
        sel.attr('tabindex', 0).focus();
    }, 20);
</script>
@endsection
