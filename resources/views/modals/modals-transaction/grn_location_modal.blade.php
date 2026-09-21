 <div class="modal fade" id="GrnLocationModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="GrnLocationModalLabel">Goods Received Note (Location)</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonGRNLocationForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">                                 

                    <div class="row">
                        <div class="row g-1">
                            <div class="col-md-4">
                                <!-- COLUMN 1 -->
                                <div class="row g-1 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">GRN No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control" id="grn_loc_sequence" name="grn_loc_sequence" style="max-width:80px" autofocus required>
                                             <div class="invalid-tooltip">
                                                Enter GRN No.
                                            </div>
                                            <input type="text" class="form-control" id="grn_loc_number" name="grn_loc_number" tabindex="-1" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-1 mb-1">
                                    <div class="col-4">
                                        <label class="from-label"> GRN Date <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8 d-flex gap-2 postition-relative">
                                        <input type="text" class="form-control trans-date-picker" id="grn_loc_date" name="grn_loc_date" required autocomplete="off">
                                        <div class="invalid-tooltip">
                                            Enter DC Date
                                        </div>
                                        <button type="button" class="btn btn-success btn-sm toggleModalBtn" id="pending_btn" disabled data-bs-target="#GRNLocationPendingModal">
                                            Pending
                                        </button>
                                    </div>
                                </div>                                
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4 class="card-title mb-0 flex-grow-1">
                                <b>GRN Details</b>
                            </h4>
                            <div class="flex-shrink-0">
                            </div>
                        </div>
                       
                        <div class="card-body">
                            <div class="table-responsive details-action">
                                <table class="table table-bordered align-middle mb-0" id="GRNLocationDetailTable">
                                    <thead>
                                        <tr>
                                            <th class="action_detail_width">Actions</th>
                                            <th>DC No.</th>
                                            <th>DC Date</th>
                                            <th>Item</th>
                                            <th>Item Group</th>
                                            <th>Main Group</th>
                                            <th>Sr. No.</th>
                                            <!-- <th>Stock</th> -->
                                            <th>GRN Qty.</th>
                                            <th>Unit</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="10" class="text-center" id="noDetails">
                                                No GRN Location Details Added
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="row g-1">
                            <div class="col-md-4">                                
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="mode_of_transport" class="form-label">Mode of Transport </label>
                                    </div>
                                    <div class="col-8">
                                            <input type="text" class="form-control" id="mode_of_transport" name="mode_of_transport" autocomplete="off" />
                                    </div>
                                </div>
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="transporter" class="form-label">Transporter</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="transporter" name="transporter" autocomplete="off" />                            
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">   
                                
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="vehicle_no" class="form-label">Vehicle No. </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="vehicle_no" name="vehicle_no" autocomplete="off" />
                                    </div>
                                </div>
                                <div class="row  g-2 mb-1"> 
                                    <div class="col-4 justify-content-start">
                                        <label for="sp_note" class="form-label justi mt-1">Special Note</label>
                                    </div>
                                    <div class="col-8">
                                        <textarea class="form-control" name="sp_note" id="sp_note"></textarea>
                                    </div>
                                </div>

                            </div>

                            <div class="col-md-4">                                
                                
                                <div class="row  g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Prepared By </label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single skip-tab" name="prepared_by_user_id" id="prepared_by_user_id" readonly >
                                            <option value="">Select Prepared By</option>
                                            @forelse(getUsers() as $users)
                                                <!-- <option value="{{ $users->id }}"  {{ auth()->id() == $users->id ? 'selected' : '' }}>{{ $users->user_name }}</option> -->
                                                <option value="{{ $users->id }}"  {{ auth()->id() == $users->id ? 'selected' : '' }}>{{ $users->person_name }}</option>
                                                @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Prepared By.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                      
                    </div>
                </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary " id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                 @if(hasAccess("grn_location","print"))
                    <a type="button" class="btn btn-secondary" target="_blank"  id="preview_btn" style="display:none;">Preview</a>
                @endif
                @if(hasAccess("grn_location","add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>

        </div>
    </div>
</div>


 @push('script-modal')
 <script>
    let loginUserId = {{ auth()->id() }};
    let checkFileRoute = "{{ route('check-file_exists') }}";
</script>
<script src="{{ URL::asset('views/js/grn_location.js?ver='.getJsVersion()) }}"></script>
@endpush
