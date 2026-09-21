@extends('layouts.master')
@section('title') Assign Format No. @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Master @endslot
@slot('title') Assign Format No. @endslot
@endcomponent

@include('modals.assign_formate_no_model')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header mb-2 d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Assign Format No.</h5>
            </div>
            <div class="card-body mt-2">
                <form id="commonAssignFormatNo" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <div class="row mt-2">
                        <div class="col-lg-1">
                            <label for="assign_location_id" class="form-label">Location</label>
                        </div>
                        <div class="col-lg-4 d-flex position-relative">
                            <select class="zindexnotapply js-example-basic-single {{ getCurrentLocation()->location_type != 'HO' ? 'readonly' : '' }}" name="assign_location_id" id="assign_location_id">

                                <!-- <option value="">Select Type of Test</option>                   -->

                                @forelse (getLocations() as $location)
                                <option value="{{ $location->location_id }}" {{ $location->location_id == getCurrentLocation()->location_id ? 'selected' : '' }}>
                                    {{ $location->location_name }}
                                </option>
                                @empty
                                @endforelse
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card mt-3">
                                <div class="card-header align-items-center d-flex pt-0">
                                    <!-- <h4 class="card-title mb-0 flex-grow-1"><b>Camera - RT Details</b></h4> -->
                                    <!-- <div class="flex-shrink-0">
                                        <button type="button" class="btn btn-primary toggleButton" data-bs-target="#RTCameraDetailsModal">Add</button>
                                    </div> -->
                                </div>

                                <div class="card-body">
                                    <div class="live-preview">
                                        <div class="table-responsive details-action">
                                            <table class="table table-bordered align-middle table-nowrap mb-0" id="assignFormateNoDetailTable">
                                                <thead>
                                                    <tr>
                                                        <th scope="col" class="action_col">Actions</th>
                                                        <th>Format Name</th>
                                                        <th>Format No.</th>
                                                        <th>Eff. Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="centeralign" id="noDetails">
                                                        <!-- <td colspan="7">No Camera - RT Details Added</td> -->
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer gap-1">
                <!-- <button type="submit" class="btn btn-primary " id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button> -->
            </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script-modal')
<script>
    window.hasAddAccess = {{ hasAccess("assign_format_no", "add") ? 'true' : 'false' }};
    window.hasEditAccess = {{ hasAccess("assign_format_no", "edit") ? 'true' : 'false' }};

</script>
<script src="{{ URL::asset('views/js/assign_format_no.js?ver='.getJsVersion()) }}"></script>
@endpush
