<div class="modal fade" id="ItemModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ItemModalLabel">Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonItemForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="row g-1 mt-2">
                            <div class="col-md-4"> 
                                <!-- <div class="row g-2 mb-1">
                                    <div class="col-4">
                                        <label for="item_code" class="form-label">Item Code </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="item_code" id="item_code" class="form-control skip-tab" readonly>
                                    </div>
                                </div> -->

                                <div class="row g-2 mb-1 position-relative">
                                    <div class="col-4">
                                        <label for="item_name" class="form-label">Item <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="item_name" id="item_name" class="form-control" required>
                                        {{-- onkeyup="suggestItemName(event,this)"  --}}
                                        {{-- <div id="item_name_list" class="suggestion_list"></div>
                                        <input type="hidden" name="item_name_suggesion" id="item_name_suggesion"> --}}
                                        <div class="invalid-tooltip">
                                            Enter Item.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1 position-relative">
                                    <div class="col-4">
                                        <label for="item_group_id" class="form-label">Item Group <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="otherselectwidth">
                                            <select class="js-example-basic-single mst_ig" name="item_group_id" id="item_group_id" required>
                                                <option value="">Select Item Group</option>
                                                @forelse(getItemGroups() as $item_group)
                                                    <option value="{{ $item_group->id }}" data-item_type="{{ $item_group->item_type }}" data-identification_req="{{ $item_group->identification_req }}">{{ $item_group->item_group }}</option>
                                                    @empty
                                                @endforelse
                                            </select>
                                            <div class="invalid-tooltip">
                                                Select Item Group.
                                            </div>
                                             @if(hasAccess("item_group","add"))
                                                <i class="plus-icon bx bx-plus-medical"
                                                onclick="addedIg(true)"
                                                data-bs-target="#ItemGroupModal"></i>
                                            @endif

                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-1 position-relative">
                                    <div class="col-4">
                                        <label for="item_type" class="form-label">Main Group </label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single skip-tab" name="item_type" id="item_type">
                                            <option value="">Select Item Type</option>
                                            @forelse(getItemType() as $key=>$val)
                                                <option value="{{ $key }}">{{ $val }}</option>
                                                @empty
                                            @endforelse
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-2 mb-1 position-relative">
                                    <div class="col-4">
                                        <label for="identification_req" class="form-label">Identification Req.</label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single skip-tab" name="identification_req" id="identification_req">
                                            <option value="">Select Iden. Req.</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                
                                <div class="row g-2 ml-2 mb-1 position-relative">
                                    <div class="col-4">
                                        <label for="unit_id" class="form-label">Unit <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="otherselectwidth">
                                            <select class="js-example-basic-single mst_unit" name="unit_id" id="unit_id" required>
                                                <option value="">Select Unit</option>
                                                @forelse(getUnit() as $unit)
                                                    <option value="{{ $unit->id }}">{{ $unit->unit }}</option>
                                                    @empty
                                                @endforelse
                                            </select>
                                            <div class="invalid-tooltip">
                                                Select Unit.
                                            </div>
                                             @if(hasAccess("unit","add"))
                                                <i class="plus-icon bx bx-plus-medical"
                                                onclick="addedUnit(true)"
                                                data-bs-target="#UnitModal"></i>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1 position-relative" id="conv_factor_div">
                                    <div class="col-4">
                                        <label for="conv_factor" class="form-label">Conv. Factor <sup class="astric" id="conv_factor_astric" style="display:none;"> *</sup></label>
                                    </div>
                                    <div class="col-8 d-flex align-items-center">
                                        <input type="text" name="conv_factor" id="conv_factor" class="form-control isNumberKeyNotDot" readonly tabindex="-1">
                                        <span class="text-nowrap ms-2">SQIN</span>
                                        <div class="invalid-tooltip">
                                            Enter Conv. Factor.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 ml-2 mb-1 position-relative">
                                    <div class="col-4">
                                        <label for="inter_location_transfer" class="form-label">Inter Location Transfer <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="inter_location_transfer" id="inter_location_transfer" required>
                                            <!-- <option value="">Select Inter Location Transfer</option> -->
                                            <option value="Allowed">Allowed</option>
                                            <option value="Not Allowed">Not Allowed</option>
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Inter Location Transfer.
                                        </div>
                                    </div>
                                </div>

                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="min_stock_level" class="form-label">Min. Stock Level </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="min_stock_level" id="min_stock_level" class="form-control isNumberKey" onblur="formatPoints(this,3)">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row g-2 ml-2 mb-1">
                                    <div class="col-4">
                                        <label for="document_ref_no" class="form-label">Document Ref. No. </label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" name="document_ref_no" id="document_ref_no" class="form-control">
                                    </div>
                                </div>
                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="validity_date" class="form-label">Validity Date </label>
                                    </div>
                                    <div class="col-8 d-flex position-relative">
                                        <input type="text" name="validity_date" id="validity_date" class="form-control date-picker">
                                    </div>
                                </div>

                                <div class="row ml-2 g-2 mb-1">
                                    <div class="col-4">
                                        <label for="status" class="form-label">Status <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <select class="js-example-basic-single" name="status" id="status" required>
                                            <option value="Active">Active</option>
                                            <option value="Deactive">Deactive</option>
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Status.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                @if(hasAccess("item","add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/item.js?ver='.getJsVersion()) }}"></script>
@endpush