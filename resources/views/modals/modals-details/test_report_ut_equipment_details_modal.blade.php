<div class="modal fade" id="UTEquipmentDetailsModal" aria-labelledby="UTEquipmentDetailsModalLabel" aria-hidden="true" data-bs-focus="true" data-custom-hide-focus="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="UTEquipmentDetailsModalLabel">UT Equipment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="UTEquipmentDetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="eq_form_type" value="add"/>
                    <input type="hidden" name="form_index" id="eq_form_index" />
                    <input type="hidden" name="row_index" id="eq_row_index" />  
                    <input type="hidden" name="test_report_ut_equipment_details_id" id="test_report_ut_equipment_details_id" /> 

                    <div class="row mt-2">
                        <div class="col-12">
                            <!-- Equipment Select -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_eu_id" class="form-label">Equipment <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="detail_eu_id" id="detail_eu_id" required style="width: 100%;">
                                        <option value="">Select Equipment</option>
                                        @forelse(getEquipmentUt() as $eq)
                                            <option value="{{ $eq->eu_id }}" 
                                                    data-eu_equipment_name="{{ $eq->eu_equipment_name }}"
                                                    data-make="{{ $eq->eu_make }}" 
                                                    data-display="{{ $eq->eu_display }}" 
                                                    data-sr_no="{{ $eq->eu_serial_no }}" 
                                                    data-cal_due_date="{{ $eq->eu_next_cali_due_date ? \Carbon\Carbon::parse($eq->eu_next_cali_due_date)->format('d/m/Y') : '' }}">
                                                {{ $eq->name_for_display }}
                                            </option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Equipment.</div>
                                </div>
                            </div>
                            <!-- Make -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="eq_make" class="form-label">Make</label></div>
                                <div class="col-8">
                                    <input type="text" name="make" id="eq_make" class="form-control" readonly tabindex="-1">
                                </div>
                            </div>
                            <!-- Display -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="eq_display" class="form-label">Display</label></div>
                                <div class="col-8">
                                    <input type="text" name="display" id="eq_display" class="form-control" readonly tabindex="-1">
                                </div>
                            </div>
                            <!-- Sr. No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="eq_sr_no" class="form-label">Sr. No.</label></div>
                                <div class="col-8">
                                    <input type="text" name="eu_sr_no" id="eq_sr_no" class="form-control" readonly tabindex="-1">
                                </div>
                            </div>
                            <!-- Calibration Due Date -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="eq_cal_due_date" class="form-label">Cali. Due Date</label></div>
                                <div class="col-8">
                                    <input type="text" name="cal_due_date" id="eq_cal_due_date" class="form-control" readonly tabindex="-1">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submitEquipmentRowBtn">Add</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>
