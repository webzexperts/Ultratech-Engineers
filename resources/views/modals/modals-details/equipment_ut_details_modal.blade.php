
<div class="modal fade" id="EquipmentUTDetailsModal" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Equipment - UT Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="EquipmentUTDetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index" />
                    <input type="hidden" name="row_index" id="row_index" />  
                    <input type="hidden" name="eud_id" id="eud_id"  value="0"/> 

                    <div class="row mb-3 p-1 m-1">
                        <div class="col-lg-12">

                            <div class="row mt-3">
                                <div class="col-12">
                                    <label for="eud_calibration_due_date" class="form-label col-form-label">Calibration Due Date </label>
                                    <input type="text" name="eud_calibration_due_date" id="eud_calibration_due_date" class="form-control date-picker">

                                </div>
                            </div>          
                        </div> 
                    </div> 
                    
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Add</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal"> Close</a>
            </div>
          </form>

        </div>
    </div>
</div>



