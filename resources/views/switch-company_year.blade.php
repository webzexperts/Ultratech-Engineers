@extends('layouts.master')
@section('title') Switch Year @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Master @endslot
@slot('title')Switch Year @endslot
@endcomponent

<div class="row">
            <div class="card">
                {{-- <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Horizontal Form</h4>
                    
                </div><!-- end card header --> --}}
                <div class="card-body">
                    
                    <div class="live-preview">
                       <form id="switchCompanyYearForm" class="stdform" method="post">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-lg-3">
                                    <label for="company_year" class="form-label col-form-label">Select Company Year </label>
                                </div>
                                <div class="col-lg-9">
                                   <select class="js-example-basic-single zindexnotapply" data-placeholder="Select Company Year" id="company_year" name="company_year">
                                                @forelse ($company_years as $company_year)
                                                    <!-- <option value="{{ $company_year->id }}" {{ $company_year['id'] == session('default_year_id') ? 'selected=selected' : "" }}>{{ $company_year->year }}</option> -->
                                                    <option class="zindexnotapply" value="{{ $company_year->id }}" {{ $company_year['id'] == session('default_year_id') ? 'selected=selected' : "" }}><?php echo date('Y', strtotime($company_year->startdate)).'-'.date('y', strtotime($company_year->enddate)); ?></option>
                                                    @empty
                                                @endforelse  
                                            </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
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

$('#switchCompanyYearForm').on('submit', function (e) {
    e.preventDefault();

    let form = this; // 'this' refers to the form element in the event handler

    // Bootstrap 5 validation check
    if (!form.checkValidity()) { // 'form' is now an actual DOM element
        e.stopPropagation();
        $(form).addClass('was-validated');
        return;
    }   

    let formData = new FormData(form);

    $.ajax({
        type: 'POST',
        url: "{{ route('change-company_year') }}",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data) {
        if(data.response_code == 1){  
            toastSuccess(data.response_message, redirectFn);
            function redirectFn() {
                 window.location.href = "dashboard";
            }
        }else{
                 toastr.error(data.response_message);
        }
        },
        error: function (jqXHR, textStatus, errorThrown){
            var errMessage = JSON.parse(jqXHR.responseText);
        
            if(errMessage.errors){
                validator.showErrors(errMessage.errors);
                
            }else if(jqXHR.status == 401){

                 toastr.error(jqXHR.statusText);
            }else{

                 toastr.error('Something went wrong!');
                console.log(JSON.parse(jqXHR.responseText));
            }
        }
    });

});

setTimeout(function () {
    // Focus the select2 selection box
    let sel = jQuery('#company_year')
        .next('.select2-container')
        .find('.select2-selection');

    sel.attr('tabindex', 0).focus();
}, 20);

</script>
@endsection
