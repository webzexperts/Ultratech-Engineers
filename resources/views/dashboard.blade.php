@extends('layouts.master')
@section('title')
@lang('translation.dashboards')
@endsection

@section('content')
<div class="blank-container mb-3">
    <div class="welcome-text">
        <h1 class="name_as_is">Welcome, {{ Auth::user()->user_name }}</h1>
    </div>
</div>

@if(isset($permittedLocations))
    <!-- Dashboard Cards Grid -->
    <div class="row g-4">
        <!-- Card 1: Pending Inter Location Transfers -->
        <!-- @if(hasAccess('inter_location_transfer', 'manage')) -->
            @include('dashboards.pending_transfer')
        <!-- @endif -->

        <!-- Card 2: Radiography Sources & Cameras -->
        <!-- @if(hasAccess('rt_camera', 'manage')) -->
            @include('dashboards.camera')
        <!-- @endif -->

        <!-- Card 3: Location Production Output -->
        <!-- @if(hasAccess('production_entry', 'manage')) -->
            @include('dashboards.production')
        <!-- @endif -->

        <!-- Card 4: Industrial X-Ray Film Stock -->
        <!-- @if(hasAccess(['item_opening_prod_area', 'item_opening', 'production_entry'], 'manage')) -->
            @include('dashboards.film_stock')
        <!-- @endif -->
    </div>
@endif
@endsection

