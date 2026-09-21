 <div class="modal fade" id="PurchaseIndentModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="PurchaseIndentModalLabel">Material Indent</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonPurchaseIndentForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id"> 
                    <input type="hidden" name="old_pi_date" id="old_pi_date">    
                    
                    <div class="row">
                        <div class="row g-1">
                        <!-- Indent No -->
                            <div class="col-md-4">
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="supplier_name" class="form-label">
                                            Indent No. <sup class="astric">*</sup>
                                        </label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control isNumberKey"
                                                id="pi_sequence" name="pi_sequence"
                                                style="max-width:50px" autofocus required>

                                            <input type="text" class="form-control skip-tab"
                                                id="pi_no" name="pi_no"
                                                tabindex="-1" readonly>

                                            <div class="invalid-tooltip">
                                             Enter Indent No.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="pi_date" class="form-label">
                                            Indent Date <sup class="astric">*</sup>
                                        </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control trans-date-picker" id="pi_date" name="pi_date" required autocomplete="off"/>
                                        <div class="invalid-tooltip">
                                            Enter Indent Date
                                        </div>
                                    </div>
                                </div>
                                @php
                                    $locations  = getOtherLocations();
                                    $hoCount    = $locations->where('location_type', 'HO')->count();
                                    $hoLocation = $locations->where('location_type', 'HO')->first();
                                @endphp
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="city_id" class="form-label">
                                            To Location <sup class="astric">*</sup>
                                        </label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single suggest_city_name"
                                            name="to_location_id"
                                            id="to_location_id" required>
                                            <option value="">Select To Location</option>
                                            @forelse(getPurchaseIndentLocations() as $location)
                                                <option value="{{ $location->location_id }}" {{ ($hoCount == 1 && $location->location_id == $hoLocation->location_id) ? 'selected' : '' }} >{{ $location->location_name }}</option>
                                                @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select To Location
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card mt-3">
                                <div class="card-header d-flex align-items-center">
                                    <div class="flex-shrink-0 mr-5">
                                        <button type="button" class="btn btn-primary add_detail" data-bs-target="#PurchaseIndentDetailsModal">Add</button>
                                    </div>
                                    <h4 class="card-title mb-0 flex-grow-1 ml-2">
                                        <b>Material Indent Details</b>
                                    </h4>

                                </div>
                            
                                <div class="card-body">
                                    <div class="table-responsive details-action">
                                        <table class="table table-bordered align-middle mb-0" id="PurchaseIndentDetailsTable">
                                            <thead>
                                                <tr>
                                                    <th class="action_detail_width">Actions</th>
                                                    <th>Item</th>
                                                    <th>Item Group</th>
                                                    <th>Main Group</th>
                                                    <th>Stock</th>
                                                    <th>Indent Qty.</th>
                                                    <th>Unit</th>
                                                    <th>Remark</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="8" class="text-center" id="noDetails">
                                                        No Material Indent Details Added
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="row g-1">
                            <div class="col-md-4">
                                <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Indent By </label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single skip-tab" name="indent_by_user_id" id="indent_by_user_id" readonly >
                                            <option value="">Select Indent By</option>
                                            @forelse(getUsers() as $users)
                                                <!-- <option value="{{ $users->id }}"  {{ auth()->id() == $users->id ? 'selected' : '' }}>{{ $users->user_name }}</option> -->
                                                <option value="{{ $users->id }}"  {{ auth()->id() == $users->id ? 'selected' : '' }}>{{ $users->person_name }}</option>
                                                @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Indent By.
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-2 mb-1">
                                    <div class="col-4 justify-content-start">
                                        <label class="form-label mt-1">Special Note </label>
                                    </div>
                                    <div class="col-8">
                                        <textarea name="special_note" id="special_note" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                
                            </div>
                        </div>
                    </div>
            </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary " id="submitbtn">Submit</button>
                        <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                         @if(hasAccess("purchase_indent","print"))
                            <a type="button" class="btn btn-secondary" target="_blank"  id="preview_btn" style="display:none;">Preview</a>
                        @endif
                        @if(hasAccess("purchase_indent","add"))
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
<script src="{{ URL::asset('views/js/purchase_indent.js?ver='.getJsVersion()) }}"></script>
@endpush
