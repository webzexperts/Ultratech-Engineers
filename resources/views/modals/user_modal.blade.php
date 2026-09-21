<style>
    #LocationTable_length {
        display: none !important;
    }

    #LocationTable_filter {
        display: none !important;
    }

    #LocationTable_info {
        display: none !important;
    }

    #LocationTable_paginate {
        display: none !important;
    }
</style>

<div class="modal fade" id="UserModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="UserModalLabel">User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonUserForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">  
                    <div class="row mt-2">
                        <div class="row mb-1">
                            <div class="col-lg-2">
                                <label for="user_name" class="form-label">User Name <sup class="astric">*</sup></label>
                            </div>
                            <div class="col-lg-4 position-relative">
                                <input type="text" name="user_name" id="user_name" class="form-control input-lower-case" required>
                                <!-- <input type="text" name="user_name" id="user_name" class="form-control input-lower-case" onkeyup="suggestUserName(event,this)" required> -->
                                {{-- <div id="user_name_list" class="suggestion_list"></div>
                                <input type="hidden" name="user_suggestion" id="user_suggestion"> --}}
                                <div class="invalid-tooltip">
                                    Enter User Name.
                                </div>
                            </div>
                        </div>

                        <div class="row mb-1">
                            <div class="col-lg-2">
                                <label for="password" class="form-label">Password <sup class="astric">*</sup></label>
                            </div>
                            <div class="col-lg-4 d-flex position-relative">
                                <input type="password" name="password" id="password" class="form-control input-lower-case">
                                <div id="password_error" class="invalid-tooltip">
                                    Enter Password.
                                </div>
                            </div>
                        </div>

                        <div class="row mb-1">
                            <div class="col-lg-2">
                                <label for="person_name" class="form-label">Person Name <sup class="astric">*</sup></label>
                            </div>
                            <div class="col-lg-4 d-flex position-relative">
                                <input type="text" name="person_name" id="person_name" class="form-control input-lower-case" required>
                                <div class="invalid-tooltip">
                                    Enter Person Name.
                                </div>
                            </div>
                        </div>

                        <div class="row mb-1">
                            <div class="col-lg-2">
                                <label for="designation" class="form-label">Designation </label>
                            </div>
                            <div class="col-lg-4 position-relative">
                                <input type="text" name="designation" id="designation" class="form-control" onkeyup="suggestDesignation(event,this)" >
                                <div id="designation_list" class="suggestion_list"></div>
                                <input type="hidden" name="designation_suggestion" id="designation_suggestion">
                            </div>
                        </div>

                        <div class="row mb-1">
                            <div class="col-lg-2">
                                <label for="phone_no" class="form-label">Phone No. </label>
                            </div>
                            <div class="col-lg-4 d-flex position-relative">
                                <input type="text" name="phone_no" id="phone_no" class="form-control mobile-f">
                                <div class="invalid-tooltip">
                                    Enter Valid Phone No.
                                </div>
                            </div>
                        </div>

                        <div class="row mb-1">
                            <div class="col-lg-2">
                                <label for="email" class="form-label">Email ID </label>
                            </div>
                            <div class="col-lg-4 d-flex position-relative">
                                <input type="email" name="email" id="email" class="form-control">
                                <div class="invalid-tooltip" id="email_error">
                                    Enter Valid Email ID.
                                </div>
                            </div>
                        </div>

                        <div class="row mb-1">
                            <div class="col-lg-2">
                                <label for="status" class="form-label">Status <sup class="astric">*</sup></label>
                            </div>
                            <div class="col-lg-4 d-flex position-relative">
                                <select class="js-example-basic-single" name="status" id="status" required>
                                    <option value="Active">Active</option>
                                    <option value="Deactive">Deactive</option>
                                </select>
                            </div>
                            <div class="invalid-tooltip">
                                Select Status.
                            </div>
                        </div>

                        <div class="row mb-1 position-relative">
                            <div class="col-lg-2">
                                <label for="allow_production_back_days_entry" class="form-label">Allow Production Back Days Entry </label>
                            </div>
                            <div class="col-lg-4 d-flex mt-0 align-items-start">
                                <input type="text" name="allow_production_back_days_entry" id="allow_production_back_days_entry" class="form-control isInteger" value="0">
                                <!-- <span class="ml-1">&nbsp;&nbsp;Days</span> -->
                                <span class="text-nowrap ms-2">Days </span>
                            </div>
                            <div class="col-lg-1">
                                <span class="text-nowrap ms-2" style="color:red;"><b>Note:</b> 0 Days = Allow Back Dated Unlimited Days Entry.</span>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="location_ids[]" id="default_location_id" value="1">

                    <div id="location_table_container" class="d-none">
                        <table class="table nowrap align-middle table-bordered remove-reset-filter" id="LocationTable">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" name="checkall_location" class="simple-check" id="checkall_location"/></th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Code</th>
                                    <th>City</th>
                                    <th>State</th>
                                    <th>NABL</th>
                                    <th>NABL Location</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                    <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                    @if(hasAccess("user","add"))
                        <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                    @endif
                    <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/user.js?ver='.getJsVersion()) }}"></script>
@endpush