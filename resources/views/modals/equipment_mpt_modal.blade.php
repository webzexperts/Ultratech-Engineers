<div class="modal fade" id="EquipmentMPTModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="EquipmentModalLabel">Equipment - MPT</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonEquipmentMPTForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <input type="hidden" name="table_pk_id" id="table_pk_id">
                    <input type="hidden" name="item_id" id="item_id">
                    <div class="row">
                        <div class="row g-1 mt-2">
                            <div class="col-md-4">
                                <div class="row g-2 mb-1">
                                    <div class="col-4"></div>
                                    <div class="col-8">
                                        <button type="button" 
                                            class="btn btn-success btn-sm toggleModalBtn" 
                                            data-bs-target="#PendingForEquipmentMPTModal" 
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
                                        <label for="em_grn_number" class="form-label"> GRN No. & Date</label>
                                    </div>
                                    <div  class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                        <input type="text" class="form-control skip-tab" id="em_grn_number" name="em_grn_number" autocomplete="off" readonly/>
                                    
                                        <!-- <label for="em_grn_date" class="form-label mb-1"> GRN Date </label> -->
                                        <input type="text" class="form-control skip-tab" id="em_grn_date" name="em_grn_date" autocomplete="off" readonly style="width:100px; "/>
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
                                            <input type="text" class="form-control skip-tab" id="challan_no"  readonly>  
                                            <input type="text" class="form-control skip-tab" id="challan_date" tabindex="-1" readonly autocomplete="off" style="width:100px; "/>
                                         </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="row g-2 ml-2  mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Item Group</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="item_group" name="item_group" readonly>  
                                    </div>
                                </div>
                                <div class="row g-2 ml-2  mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Main Group</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control skip-tab" id="main_group" name="main_group" readonly>  
                                    </div>
                                </div>
                               
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Equipment <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="em_equipment_name" name="em_equipment_name" required>
                                        <div class="invalid-tooltip">
                                            Enter Equipment.
                                        </div>
                                    </div>
                                </div>

                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="em_make" class="form-label">Make <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="em_make" id="em_make" class="form-control" required>
                                        <div class="invalid-tooltip" >
                                            Enter Make.
                                        </div>
                                    </div>
                                </div>

                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4"> 
                                        <label for="eu_serial_no" class="form-label">Mfg. Sr. No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="em_serial_no" id="em_serial_no" class="form-control" required>
                                        <div class="invalid-tooltip" >
                                            Enter Mfg. Sr. No.
                                        </div>
                                    </div>
                                </div>
                               
                            </div>

                            <div class="col-md-4">

                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="ins_cali_freq" class="form-label">Calibration Freq. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8 d-flex">
                                        <input type="text" name="ins_cali_freq" id="ins_cali_freq" class="form-control isNumberKeyNotZero" required>
                                        <span class="mt-2 ml-1 ms-1">Days</span>
                                        <div class="invalid-tooltip" >
                                            Enter Calibration Freq.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="em_last_cali_date" class="form-label">Last Calibration Date <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="em_last_cali_date" id="em_last_cali_date" class="form-control date-picker" required>
                                        <div class="invalid-tooltip" >
                                            Enter Last Calibration Date.
                                        </div>
                                    </div>
                                </div>
                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="em_next_cali_due_date" class="form-label">Next Calibration Due Date <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="em_next_cali_due_date" id="em_next_cali_due_date" class="form-control date-picker" required>
                                        <div class="invalid-tooltip" >
                                            Enter Next Calibration Due Date.
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Calibration Certificate </label>
                                    </div>
                                    <div class="col-8">
                                        <div class="input-append fileupload position-relative">
                                            <div class="d-flex align-items-center gap-2">
                                                <input class="form-control" type="file" name="calibration_certificate" id="calibration_certificate" accept=".png,.jpg,.jpeg,.gif,.pdf">
                                                <input type="hidden" id="calibration_certificate_doc" name="calibration_certificate_doc"  />
                                                <a href="#" data-remove="calibration_certificate" id="calibration_certificate_remove" class="btn fileupload-exists hide" data-dismiss="fileupload" onclick="removeFile(event)" >Remove</a>
                                                <a target="_blank" class="btn img-prev hide" id="calibration_certificate_prev">View</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="em_status" class="form-label">Status </label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single skip-tab" name="em_status" id="em_status">
                                            <option value="Active" selected>Active</option>
                                            <option value="Outside">Outside</option>
                                            <option value="Service PO">Service PO</option>
                                            <option value="Deactive">Deactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                     <!-- <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4 class="card-title mb-0 flex-grow-1">
                                <b>Equipment MPT Details</b>
                            </h4>
                            <div class="flex-shrink-0">
                                <button type="button" class="btn btn-primary add_detail" data-bs-target="#EquipmentMPTDetailsModal">Add</button>
                            </div>
                        </div>
                       
                        <div class="card-body">
                            <div class="table-responsive details-action">
                                <table class="table table-bordered align-middle mb-0" id="EquipmentMPTDetailsTable">
                                    <thead>
                                        <tr>
                                            <th>Actions</th>
                                            <th>Calibration Due Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="2" class="text-center" id="noDetails">
                                                No Equipment MPT Details Added
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div> -->
                    
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                @if(hasAccess("equipment_mpt","add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/equipment_mpt.js?ver='.getJsVersion()) }}"></script>
@endpush