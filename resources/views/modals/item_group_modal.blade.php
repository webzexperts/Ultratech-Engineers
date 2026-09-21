<div class="modal fade" id="ItemGroupModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ItemGroupModalLabel">Item Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonItemGroupForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">

                    <div class="row mt-2">
                        <div class="row g-1 mt-2">
                            <div class="col-md-6">
                                <div class="row g-1 mb-1">
                                    <div class="col-3">
                                        <label for="item_group" class="form-label">Item Group <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8 position-relative">
                                        <input type="text" name="item_group" id="item_group" class="form-control"  onfocusout="verifyItemGroup()" required>
                                        {{-- onkeyup="suggestItemGroup(event,this)" --}}
                                        {{-- <div id="item_group_list" class="suggestion_list"></div>
                                        <input type="hidden" name="item_group_suggesion" id="item_group_suggesion"> --}}
                                        <div class="invalid-tooltip">
                                            Enter Item Group.
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-1 mb-1">
                                    <div class="col-3">
                                        <label for="ig_item_type" class="form-label">Main Group <sup class="astric">*</sup></label>
                                    </div>

                                    <div class="col-8 position-relative">
                                        <select class="js-example-basic-single" name="ig_item_type" id="ig_item_type" required>
                                            <option value="">Select Main Group</option>
                                            @forelse (getItemType() as $key=>$val)
                                                <option value="{{ $key }}">{{ $val }}</option>
                                                @empty
                                            @endforelse 
                                        </select>
                                        <div class="invalid-tooltip">
                                            Select Main Group.
                                        </div>
                                    </div>
                                </div>           

                                <div class="row g-1 mb-1">
                                    <div class="col-3">
                                        <label for="ig_identification_req" class="form-label">Identification Req.</label>
                                    </div>

                                    <div class="col-8">
                                        <select class="js-example-basic-single skip-tab" name="ig_identification_req" id="ig_identification_req">
                                            <option value="">Select Identification Req.</option>
                                            <option value="yes">Yes</option>
                                            <option value="no">No</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-1 mb-1">
                                    <div class="col-3">
                                        <label for="notify_before" class="form-label">Notify Before <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8 d-flex position-relative">
                                        <input type="text" name="notify_before" id="notify_before" class="form-control isNumberKey"> <span class="form-label ml-2 mt-1">&nbsp;&nbsp;Days </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>  
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="submitbtn">Submit</button>
                    <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                    @if(hasAccess("item_group","add"))
                        <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                    @endif
                    <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/item_group.js?ver='.getJsVersion()) }}"></script>
@endpush