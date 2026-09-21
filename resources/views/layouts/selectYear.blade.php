 <link href="{{ URL::asset('build/css/select2.min.css')}}" rel="stylesheet" type="text/css" />
@extends('layouts.master-without-nav')
@section('title')
@endsection
@section('content')

<div class="auth-page-wrapper pt-5">
    <!-- auth page bg -->
    <div class="auth-one-bg-position auth-one-bg"  id="auth-particles">
        <div class="bg-overlay"></div>

        <div class="shape">
            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
                <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
            </svg>
        </div>
    </div>

    <!-- auth page content -->
    <div class="auth-page-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center mt-sm-5 mb-4 text-white-50">
                        <div>
                            <a href="index" class="d-inline-block auth-logo">
                                <img src="{{ URL::asset('build/images/ultratech.png')}}" alt="" height="100">
                            </a>
                        </div>
                        
                    </div>
                </div>
            </div>
            <!-- end row -->

            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card mt-4">

                        <div class="card-body p-4">
                            {{-- <div class="text-center mt-2">
                                <h5 class="text-primary">Welcome Back !</h5>
                                <p class="text-muted">Sign in to continue to Velzon.</p>
                            </div> --}}
                            <div class="p-2 mt-4">
                              
                                <form id="locationForm" action="{{ route('getUserPremission') }}" method="POST">
                                    @csrf
{{--                                     
                                    @if ($errors->any())
                                            <div class="alert alert-danger">                       

                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                    @endif --}}
                                
                                    
                                        @php
                                        
                                        if(Auth::id() != 1)
                                        {
                                            
                                            $getLocations =  App\Models\Location::join('user_locations', 'user_locations.location_id', 'location.location_id')->where('user_locations.user_id', Auth::id())->select('user_locations.location_id', 'location.location_id', 'location.location_name')->orderBy('location.location_name', 'Asc')->distinct('location_name')->get();
                                            
                                            
                                        }else{
                                            
                                            $getLocations = App\Models\Location::orderBy('location.location_name', 'Asc')->get();

                                            
                                        }
                                    @endphp
                                    

                                    

                                        {{-- <div class="col-sm-12">                     --}}
                                            <div class="row g-2 ml-2 mb-2">
                                       <div class="col-12">
                                        <div class=" gap-2 position-relative">
                                                    
                                            <select name="user_location_id" id="user_location_id"  class="js-example-basic-single  @error('user_location') is-invalid @enderror input-lower-case" >    
                                                    @if(count($getLocations) > 1) 

                                                    <option value="">Select Location...</option>
                                                    
                                                    @endif
                                                @foreach ($getLocations as $getLocationsData)    
                                                
                                                <option value="{{ $getLocationsData->location_id }}">{{ $getLocationsData->location_name }}</option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('user_location_id'))
                                                    <span id="location_error" style="color:#f06548;">
                                                    <strong>{{ $errors->first('user_location_id') }}</strong>
                                                </span>
                                            @endif 
                                        </div> 
                                       </div>
                                            </div>
                                    <!-- <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="" id="auth-remember-check">
                                        <label class="form-check-label" for="auth-remember-check">Remember me</label>
                                    </div> -->

                                    <div class="mt-4">
                                        <button class="btn btn-success w-100" type="submit">Sign In</button>
                                    </div>

                                    
                                </form>
                            </div>
                        </div>
                        <!-- end card body -->
                    </div>

                </div>
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>

    <footer class="footer">
        <div class="container">          
        	<div class="row">
            	<div class="col-lg-12">
                    <div class="text-center">      
                        
			            	<img src="{{ asset('images/icons/cbs_webtech.png') }}" alt="company logo" height="99px" width="180px"/>              
                        
                   	</div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center">      
                        <p class="mb-2 text-muted">
                            © 2026 Ultratech Engineers All Rights Reserved.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- end Footer -->
</div>
@endsection
@section('script')
<script>

   
var _pendingChar = null;

// Inject pending char AFTER dropdown fully renders (select2:open fires when ready)
jQuery('#user_location_id').on('select2:open', function () {
    setTimeout(function () {
        var searchField = document.querySelector('.select2-container--open .select2-search__field');
        if (!searchField) return;
        searchField.focus();
        if (_pendingChar !== null) {
            searchField.value = _pendingChar;
            _pendingChar = null;
            searchField.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }, 0);
});

// Focus selection box on page load (no auto-open)
setTimeout(function () {
    var $select = jQuery('#user_location_id');
    if (!($select.data('select2') || $select.hasClass('select2-hidden-accessible'))) {
        $select.focus();
        return;
    }
    $select.next('.select2-container').find('.select2-selection').focus();
}, 300);

// When user types on the focused selection box → store char → open dropdown
jQuery(document).on("keydown", ".select2-selection", function (e) {
    // Ignore special keys, control keys, and shortcuts
    if (e.key.length !== 1 || e.ctrlKey || e.altKey || e.metaKey) {
        return;
    }

    const $selection = jQuery(this);
    const $select = $selection.closest(".select2-container").prev("select");

    // If select2 not already open
    if (!$select.data('select2').isOpen()) {
        $select.select2('open');

        // put typed character inside search box and focus it
        setTimeout(() => {
            const searchField = document.querySelector('.select2-container--open .select2-search__field');
            if (searchField) {
                searchField.focus();
                searchField.value = e.key;
                // trigger search
                searchField.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }, 15);
    } else {
        // If select2 is already open but selection element still has focus, redirect key to search field
        const searchField = document.querySelector('.select2-container--open .select2-search__field');
        if (searchField && document.activeElement !== searchField) {
            searchField.focus();
            searchField.value += e.key;
            // trigger search
            searchField.dispatchEvent(new Event('input', { bubbles: true }));
            e.preventDefault();
        }
    }
});


     $('#user_location_id').on('change', function () {

        if ($(this).val() != '') {

            $('#location_error').css('display', 'none');

        }

    });

// jQuery('#locationForm').on('submit', function(e) {
//     e.preventDefault();

//     jQuery.ajax({
//         url: "{{ route('getUserPremission') }}",
//         type: "POST",
//         data: jQuery(this).serialize(),
//         success: function(res) {
//             window.location.href = "{{ route('/') }}";
//         }
//     });
// });

</script>
<script src="{{ URL::asset('build/libs/particles.js/particles.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/particles.app.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/password-addon.init.js') }}"></script>
<script src="{{ URL::asset('js/select2.min.js') }}"></script>
<script src="{{ URL::asset('build/js/pages/select2.init.js') }}"></script>
@endsection
