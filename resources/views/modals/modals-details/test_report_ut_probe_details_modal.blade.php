<div class="modal fade" id="UTProbeDetailsModal" aria-labelledby="UTProbeDetailsModalLabel" aria-hidden="true" data-bs-focus="true" data-custom-hide-focus="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="UTProbeDetailsModalLabel">UT Probe Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="UTProbeDetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="pb_form_type" value="add"/>
                    <input type="hidden" name="form_index" id="pb_form_index" />
                    <input type="hidden" name="row_index" id="pb_row_index" />  
                    <input type="hidden" name="test_report_ut_probe_details_id" id="test_report_ut_probe_details_id" /> 

                    <div class="row mt-2">
                        <div class="col-12">
                            <!-- Probe Select -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="detail_pu_id" class="form-label">Probe <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="detail_pu_id" id="detail_pu_id" required style="width: 100%;">
                                        <option value="">Select Probe</option>
                                        @forelse(getProbeUt() as $pb)
                                            <option value="{{ $pb->pu_id }}" 
                                                    data-pu_probe="{{ $pb->pu_probe }}"
                                                    data-sr_no="{{ $pb->pu_serial_number }}" 
                                                    data-size="{{ $pb->pu_size_of_probe }}" 
                                                    data-ref_angle="{{ $pb->pu_ref_angle }}" 
                                                    data-frequency="{{ $pb->pu_frequency }}">
                                                {{ $pb->name_for_display }}
                                            </option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Probe.</div>
                                </div>
                            </div>
                            <!-- Serial No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="pb_sr_no" class="form-label">Probe Sr. No.</label></div>
                                <div class="col-8">
                                    <input type="text" name="pu_sr_no" id="pb_sr_no" class="form-control" readonly tabindex="-1">
                                </div>
                            </div>
                            <!-- Size of Probe -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="pb_size_of_probe" class="form-label">Size of Probe</label></div>
                                <div class="col-8">
                                    <input type="text" name="size_of_probe" id="pb_size_of_probe" class="form-control" readonly tabindex="-1">
                                </div>
                            </div>
                            <!-- Reference Angle -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="pb_ref_angle" class="form-label">Ref. Angle</label></div>
                                <div class="col-8">
                                    <input type="text" name="ref_angle" id="pb_ref_angle" class="form-control" readonly tabindex="-1">
                                </div>
                            </div>
                            <!-- Frequency -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="pb_frequency" class="form-label">Frequency</label></div>
                                <div class="col-8">
                                    <input type="text" name="frequency" id="pb_frequency" class="form-control" readonly tabindex="-1">
                                </div>
                            </div>
                            <!-- Calibration Range -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="pb_cal_range" class="form-label">Cal. Range <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <input type="text" name="cal_range" id="pb_cal_range" class="form-control" required>
                                    <div class="invalid-tooltip">Enter Cal. Range.</div>
                                </div>
                            </div>
                            <!-- Reference Gain -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="pb_ref_gain" class="form-label">Ref. Gain <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <input type="text" name="ref_gain" id="pb_ref_gain" class="form-control" required>
                                    <div class="invalid-tooltip">Enter Ref. Gain.</div>
                                </div>
                            </div>
                            <!-- Scanning dB -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="pb_scanning_db" class="form-label">Scanning dB <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <input type="text" name="scanning_db" id="pb_scanning_db" class="form-control" required>
                                    <div class="invalid-tooltip">Enter Scanning dB.</div>
                                </div>
                            </div>
                            <!-- Transfer Corr Gain -->
                            <div class="row g-2 mb-1">
                                <div class="col-4"><label for="pb_transfer_corr_gain" class="form-label">Transfer Corr. Gain <sup class="astric">*</sup></label></div>
                                <div class="col-8">
                                    <input type="text" name="transfer_corr_gain" id="pb_transfer_corr_gain" class="form-control" required>
                                    <div class="invalid-tooltip">Enter Transfer  Corr. Gain.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submitProbeRowBtn">Add</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>
