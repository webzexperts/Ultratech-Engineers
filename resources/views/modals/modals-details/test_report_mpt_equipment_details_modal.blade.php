<div class="modal fade" id="MPTEquipmentDetailsModal" aria-labelledby="MPTEquipmentDetailsModalLabel" aria-hidden="true" data-bs-focus="true" data-custom-hide-focus="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="MPTEquipmentDetailsModalLabel">MPT Equipment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="MPTEquipmentDetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="eq_form_type" value="add"/>
                    <input type="hidden" name="form_index" id="eq_form_index" />
                    <input type="hidden" name="row_index" id="eq_row_index" />  
                    <input type="hidden" name="test_report_mpt_equipment_details_id" id="test_report_mpt_equipment_details_id" /> 

                    <div class="row mt-2">
                        <div class="col-12">
                            <!-- Equipment Select -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_em_id" class="form-label">Equipment <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="detail_em_id" id="detail_em_id" required style="width: 100%;">
                                        <option value="">Select Equipment</option>
                                         @forelse(getEquipmentMpt() as $eq)
                                            @php
                                                $parts = array_filter([$eq->em_equipment_name, $eq->em_serial_no, $eq->em_make]);
                                                $dispName = (!empty($eq->name_for_display) && count(explode(' - ', $eq->name_for_display)) > 1) 
                                                    ? $eq->name_for_display 
                                                    : implode(' - ', $parts);
                                            @endphp
                                            <option value="{{ $eq->em_id }}" 
                                                    data-em_equipment_name="{{ $eq->em_equipment_name }}"
                                                    data-make="{{ $eq->em_make }}" 
                                                    data-display="{{ $eq->em_equipment_name }}" 
                                                    data-sr_no="{{ $eq->em_serial_no }}" 
                                                    data-cal_due_date="{{ $eq->em_next_cali_due_date ? \Carbon\Carbon::parse($eq->em_next_cali_due_date)->format('d/m/Y') : '' }}">
                                                {{ $dispName }}
                                            </option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Equipment.</div>
                                </div>
                            </div>
                            <!-- Sr. No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="eq_sr_no" class="form-label">Sr. No.</label></div>
                                <div class="col-8">
                                    <input type="text" name="em_sr_no" id="eq_sr_no" class="form-control" readonly tabindex="-1">
                                </div>
                            </div>
                            <!-- Make -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="eq_make" class="form-label">Make</label></div>
                                <div class="col-8">
                                    <input type="text" name="make" id="eq_make" class="form-control" readonly tabindex="-1">
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
