 <div class="modal fade" id="ItemIssueModal" aria-labelledby="fullscreeexampleModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="InterLocationTransferModalLabel">Item Issue (Internal)</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="commonItemIssueForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">                                 

                    <div class="row">
                        <div class="row g-1">
                            <div class="col-md-4">                         

                                <!-- COLUMN 1 -->
                                <div class="row g-1 mb-1">
                                    <div class="col-4">
                                        <label class="form-label">Issue No. <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <div class="d-flex gap-2 position-relative">
                                            <input type="text" class="form-control" id="issue_sequence" name="issue_sequence" style="max-width:80px" autofocus required>
                                            <div class="invalid-tooltip">
                                                Enter Issue No.
                                            </div>
                                            <input type="text" class="form-control" id="issue_number" name="issue_number" tabindex="-1" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-1 mb-1">
                                    <div class="col-4">
                                        <label class="from-label">Issue Date <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8 postition-relative">
                                        <input type="text" class="form-control trans-date-picker" id="issue_date" name="issue_date" required autocomplete="off">
                                        <div class="invalid-tooltip">
                                            Enter Issue Date
                                        </div>
                                    </div>
                                </div>                               
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <div class="flex-shrink-0 mr-5">
                                <button type="button" class="btn btn-primary toggleButton" data-bs-target="#ItemIssueDetailsModal">Add</button>
                            </div>
                            <h4 class="card-title mb-0 flex-grow-1 ml-2">
                                <b>Item Issue Details</b>
                            </h4>
                        </div>
                       
                        <div class="card-body">
                            <div class="table-responsive details-action">
                                <table class="table table-bordered align-middle mb-0" id="ItemIssueDetailsTable">
                                    <thead>
                                        <tr>
                                            <th>Actions</th>
                                            <th>Item</th>
                                            <th>Item Group</th>
                                            <th>Main Group</th>
                                            <th>Sr. No.</th>
                                            <th>Stock</th>
                                            <th>Issue Qty.</th>
                                            <th>Unit</th>
                                            <th>Issue Type</th>
                                            <th>Wastage Reason</th>
                                            <th>Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="11" class="text-center" id="noDetails">
                                                No Item Issue Details Added
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

                                <!-- COLUMN 1 -->
                                <div class="row g-1 mb-1">
                                    <div class="col-4 justify-content-start">
                                        <label class="form-label">Issue To <sup class="astric">*</sup></label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" class="form-control" id="issue_to" name="issue_to" autocomplete="off" onkeyup="suggestDesignation(event,this)" required/>
                                          <div id="issue_to_list" class="suggestion_list"></div>
                                        <input type="hidden" name="issue_to_suggestion" id="issue_to_suggestion">
                                        <div class="invalid-tooltip">
                                            Enter Issue To.
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-1 mb-1">
                                    <div class="col-4 justify-content-start">
                                        <label class="from-label">Special Note </label>
                                    </div>
                                    <div class="col-8 postition-relative">
                                        <textarea class="form-control" name="special_note" id="special_note"></textarea>
                                    </div>
                                </div>                               
                                <div class="row g-1 mb-1">
                                    <div class="col-4">
                                        <label class="from-label">Prepared By</label>
                                    </div>
                                    <div class="col-8 postition-relative">
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
                 @if(hasAccess("item_issue","print"))
                    <a type="button" class="btn btn-secondary" target="_blank"  id="preview_btn" style="display:none;">Preview</a>
                @endif
                @if(hasAccess("item_issue","add"))
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
<script src="{{ URL::asset('views/js/item_issue.js?ver='.getJsVersion()) }}"></script>
@endpush
