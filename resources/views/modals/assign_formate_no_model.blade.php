<div class="modal fade" id="assignFormateNoDetailsModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ContactModalLabel">Assign Format No. Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="AssignFormatNoDetailsForm" class="row g-3 needs-validation" novalidate>
                    <input type="hidden" name="page_id" id="page_id"  value=""/> 
                    <input type="hidden" name="location_id" id="location_id"  value=""/> 
                    <input type="hidden" name="id" id="id"  value=""/> 
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index" />
                    <input type="hidden" name="row_index" id="row_index" />  
                    
                    <div class="row mt-2">
                        <div class="row g-1">
                            <div class="col-lg-1">
                                <label for="assign_format_no" class="form-label">Format No.<sup class="astric">*</sup></label>
                            </div>
                            <div class="col-lg-4">
                                <input type="text" name="assign_format_no" id="assign_format_no" class="form-control" required>
                                <div class="invalid-tooltip" id="">
                                    Enter Format No.
                                </div>
                            </div>
                        </div>
                        <div class="row g-1">
                            <div class="col-lg-1">
                                <label for="assign_effect_date" class="form-label">Eff. Date<sup class="astric">*</sup></label>
                            </div>
                            <div class="col-lg-4 position-relative">
                                <input type="text" name="assign_effect_date" id="assign_effect_date" class="form-control date-picker" required>
                                <div class="invalid-tooltip">
                                    Enter Eff. Date.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card mt-3">
                                <div class="card-header align-items-center d-flex pt-0">
                                    <h4 class="card-title mb-0 flex-grow-1"><b id="display_name"></b></h4>
                                </div>
                                <div class="card-body">
                                    <div class="live-preview">
                                        <div class="table-responsive details-action">
                                            <table class="table table-bordered align-middle table-nowrap mb-0" id="AssignDataTable">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Format No.</th>
                                                        <th scope="col">Eff. Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- <tr class="centeralign" id="noDetails">
                                                        <td colspan="7">No Camera - RT Details Added</td>
                                                    </tr> -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal"> Close</a>
            </div>
          </form>

        </div>
    </div>
</div>



