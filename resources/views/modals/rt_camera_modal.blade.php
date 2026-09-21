<div class="modal fade" id="RTCameraModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="RTCameraModalLabel">Camera - RT</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonRTCameraForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="table_pk_id" id="table_pk_id">
                    <input type="hidden" name="item_id" id="item_id">
                    <div class="row">
                        <!-- <div class="row g-1">
                            <div class="col-md-4">
                                <div class="row g-2">
                                    <div class="col-4"></div>
                                    <div class="col-8">
                                        <button type="button" 
                                            class="btn btn-success btn-sm toggleModalBtn" 
                                            data-bs-target="#PendingForGRNModal" 
                                            id="pending_btn" disabled>
                                            Pending
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        <div class="row g-1">
                            <div class="col-md-4">
                                <div class="row g-2 mb-1">
                                    <div class="col-4"></div>
                                    <div class="col-8">
                                        <button type="button" 
                                            class="btn btn-success btn-sm toggleModalBtn" 
                                            data-bs-target="#PendingForGRNModal" 
                                            id="pending_btn" disabled>
                                            Pending
                                        </button>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Type</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="table_unique_id" name="table_unique_id" readonly>  
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">GRN No. & Date </label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control skip-tab" id="grn_no" tabindex="-1" readonly>
                                            <input type="text" class="form-control skip-tab" id="grn_date" tabindex="-1" readonly autocomplete="off" style="width:100px; "/>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Supplier </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="supplier" name="supplier" readonly>  
                                    </div>
                                </div>
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Challan No. & Date </label>
                                    </div>
                                    <div class="col-8">
                                         <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control skip-tab" id="challan_no" name="challan_no" readonly>  
                                            <input type="text" class="form-control skip-tab" id="challan_date" tabindex="-1" readonly autocomplete="off" style="width:100px; "/>
                                         </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Item Group</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab " id="item_group" name="item_group" readonly>  
                                    </div>
                                </div>
                                
                            </div>

                            <div class="col-md-4">

                                <div class="row g-2 ml-2  mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Main Group</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="main_group" name="main_group" readonly>  
                                    </div>
                                </div>
                               

                                <div class="row g-2 ml-2  mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Camera Name <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="rt_camera_name" name="rt_camera_name" required>
                                        <div class="invalid-tooltip">
                                            Enter Camera Name.
                                          
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2  mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Camera Sr. No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="rt_serial_no" name="rt_serial_no" required>
                                        <div class="invalid-tooltip">
                                            Enter Camera Sr. No.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Isotope <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="rt_isotope" id="rt_isotope" required>
                                            <option value="">Select Isotope</option>
                                            @forelse(getIsotopeType() as $isotope_type)
                                                <option value="{{ $isotope_type }}">{{ $isotope_type }}</option>
                                                @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Isotope.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">X-Ray KV & mA <sup class="astric" id="xrayAsterisk" style="display:none">*</sup></label>
                                    </div>
                                    <div class="col-8 d-flex">
                                        <input type="text" class="form-control " id="rt_x_ray" name="rt_x_ray" autocomplete="off"/>
                                        {{-- <span class="form-label x_ray_text mt-1">(KV&mA)</span> --}}
                                        <div class="invalid-tooltip" id="xrayValidationMessage" style="display:none">
                                            Enter X-Ray (KV & mA).
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Focal Spot <sup class="astric" id="focalspotAsterisk" style="display:none">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="rt_focal_spot" name="rt_focal_spot">
                                        <div class="invalid-tooltip" id="focalspotValidationMessage" style="display:none">
                                            Enter Focal Spot.
                                        </div>
                                    </div>
                                </div>

                                
                            </div>

                            <div class="col-md-4">

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">AERB No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="rt_aerb_no" name="rt_aerb_no" required>
                                        <div class="invalid-tooltip">
                                            Enter AERB No.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Application No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="application_no" name="application_no" required>
                                        <div class="invalid-tooltip">
                                            Enter Application No.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1 position-relative">
                                    <div class="col-4">
                                        <label class="form-label">Movement Approval <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="input-append fileupload position-relative">
                                            <div class="d-flex align-items-center gap-2">
                                                <input class="form-control" type="file" name="movement_approval" id="movement_approval" accept=".png,.jpg,.jpeg,.gif,.pdf" required>
                                                <input type="hidden" id="movement_approval_doc" name="movement_approval_doc"  />
                                                <a href="#" data-remove="movement_approval" id="movement_approval_remove" class="btn fileupload-exists hide" data-dismiss="fileupload" onclick="removeFileMovement(event)" >Remove</a>
                                                <a target="_blank" class="btn img-prev hide" id="movement_approval_prev">View</a>
                                                <div class="invalid-tooltip">
                                                     Select Movement Approval.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="from-label">Validity </label>
                                    </div>
                                    <div class="col-8 postition-relative">
                                        <input type="text" class="form-control date-picker" id="validity" name="validity" autocomplete="off">
                                        <div class="invalid-tooltip">
                                            Enter Validity 
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Status</label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single form-select skip-tab" name="rt_status" id="rt_status">
                                            <option value="Active">Active</option>
                                            <option value="Outside">Outside</option>
                                            <option value="Service PO">Service PO</option>
                                            <option value="Deactive">Deactive</option>
                                        </select>
                                    </div>
                                </div>

                                
                                
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card mt-3">
                                <div class="card-header align-items-center d-flex pt-0">
                                    <div class="flex-shrink-0 mr-5">
                                        <button type="button" class="btn btn-primary toggleButton" data-bs-target="#RTCameraDetailsModal">Add</button>
                                    </div>
                                    <h4 class="card-title mb-0 flex-grow-1 ml-2"><b>Camera - RT Details</b></h4>
                                </div>

                                <div class="card-body">
                                    <div class="live-preview">
                                        <div class="table-responsive details-action">
                                            <table class="table table-bordered align-middle table-nowrap mb-0" id="RTCameraDetailTable">
                                                <thead>
                                                    <tr>
                                                        <th scope="col" class="action_col">Actions</th>
                                                        <th scope="col">Last Date of Loading</th>
                                                        <th scope="col">Initial Activity (Ci)</th>
                                                        <th scope="col">Source Size</th>
                                                        <th scope="col">Pencil No.</th>
                                                        <!-- <th scope="col">IGA No.</th> -->
                                                        <th scope="col">Decay Chart</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="centeralign" id="noDetails">
                                                        <td colspan="6">No Camera - RT Details Added</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="row g-1">
                            <!-- <div class="col-md-4">
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Status</label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single form-select skip-tab" name="rt_status" id="rt_status">
                                            <option value="Active">Active</option>
                                            <option value="Outside">Outside</option>
                                            <option value="Service PO">Service PO</option>
                                            <option value="Deactive">Deactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div> -->
                            <div class="col-md-4">
                                <div class="row g-2 ml-2 mb-1" style="display: none;">
                                    <div class="col-4">
                                        <label class="form-label">Document Ref. No. </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="rt_document_ref_no" name="rt_document_ref_no">
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4"></div>
                                    <div class="col-8">
                                        <div class="half-life-text">
                                            <div>Half Time for Ir-192 = 74.5 Days </div>
                                            <div>Half Time for Co-60 = 1925 Days (5.3 Years)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4" >
                                <div class="row g-2 ml-2 mb-1" style="display: none;">
                                    <div class="col-4">
                                        <label class="form-label">Validity Date </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control date-picker" id="rt_validity_date" name="rt_validity_date" autocomplete="off"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                    <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                    @if(hasAccess("rt_camera","add"))
                        <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                    @endif
                    <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/rt_camera.js?ver='.getJsVersion()) }}"></script>
@endpush