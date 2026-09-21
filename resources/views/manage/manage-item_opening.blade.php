@extends('layouts.master')
@section('title') Item Opening @endsection
@section('css')

@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Master @endslot
@slot('title')Item Opening @endslot
@endcomponent

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Item Opening</h5>
            </div>
            <div class="card-body">
                <form id="commonItemOpening" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <table id="item_opening_table" class="table nowrap align-middle table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <!-- <th class="action_col">Actions</th> -->
                                {{-- <th>Item Code</th> --}}
                                <th>Item</th>
                                <th>Item Group</th>
                                <th>Main Group</th>
                                <th>Opening Qty.</th>
                                <th>Opening Amount</th>
                                <th>Current Stock Qty.</th>
                                <th>Unit</th>
                                <!-- <th>Rate/Unit</th> -->
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                        <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-modal')
<script src="{{ URL::asset('views/js/item_opening.js?ver='.getJsVersion()) }}"></script>
@endpush