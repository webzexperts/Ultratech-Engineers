 <div class="modal fade" id="authModal" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authModalLabel">Confirm Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="login" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">                  

                    <div class="row mb-8">
                        <div class="col-lg-2">
                            <label for="password" class="form-label col-form-label">Password <sup class="astric">*</sup></label>
                        </div>
                        <div class="col-lg-6">
                            <input type="text" name="password" id="password" class="form-control" required>
                              
                            <div class="invalid-tooltip">
                                Enter Password.
                            </div>

                        </div>
                    </div>                 
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary " id="submitbtn">Submit</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
          </form>

        </div>
    </div>
</div>


