@yield('css')
<!-- Layout config Js -->
<script src="{{ URL::asset('build/js/layout.js') }}"></script>
<!-- Select2 Css -->
 <link href="{{ URL::asset('build/css/select2.min.css')}}" rel="stylesheet" type="text/css" />
<!-- Bootstrap Css -->
<link href="{{ URL::asset('build/css/bootstrap.min.css') }}"  rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="{{ URL::asset('build/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
<!-- CK Editor Css-->
<link href="{{ URL::asset('build/libs/quill/quill.core.css') }}" rel="stylesheet" type="text/css"/>
<!-- App Css-->
<link href="{{ URL::asset('build/css/app.min.css') }}"  rel="stylesheet" type="text/css" />
<!-- custom Css-->
<link href="{{ URL::asset('build/css/custom.min.css?ver='.getCssVersion()) }}"  rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/css/toastr.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/css/jquery-ui.min.css')}}" rel="stylesheet" type="text/css" />
{{-- @yield('css') --}}