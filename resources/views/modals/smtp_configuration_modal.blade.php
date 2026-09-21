<div class="modal fade" id="SMTPConfigurationModal" aria-labelledby="SMTPConfigurationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="SMTPConfigurationModalLabel">SMTP Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="commonSMTPConfigurationForm" class="needs-validation" autocomplete="off" novalidate>
                    @csrf
                    <input type="hidden" name="id" id="id">

                    <!-- Fake inputs to trick Chrome's aggressive autofill -->
                    <input type="text" style="display:none;" name="fakeemailremembered"/>
                    <input type="password" style="display:none;" name="fakepasswordremembered"/>

                    <div class="row">
                        <div class="col-md-5">
                            <!-- Email -->
                            <div class="row g-2 mb-2">
                                <div class="col-4">
                                    <label for="email" class="form-label">Email <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <input type="email" name="email" id="email" class="form-control" autocomplete="off" required>
                                    <div class="invalid-tooltip">Enter Email.</div>
                                </div>
                            </div>

                            <!-- CC Email -->
                            <div class="row g-2 mb-2">
                                <div class="col-4">
                                    <label for="cc_email" class="form-label">CC Email</label>
                                </div>
                                <div class="col-8">
                                    <input type="email" name="cc_email" id="cc_email" class="form-control" autocomplete="off">
                                    <div class="invalid-tooltip">Enter CC Email.</div>
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="row g-2 mb-2">
                                <div class="col-4">
                                    <label for="password" class="form-label">Password <sup class="astric" id="pwd_astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="password" name="password" id="password" class="form-control" autocomplete="new-password" required>
                                        <i class="plus-icon ri-eye-fill" id="togglePassword" style="font-size: 16px;"></i>
                                    </div>
                                    <div class="invalid-tooltip">Enter Password.</div>
                                </div>
                            </div>

                            <!-- Mail Host -->
                            <div class="row g-2 mb-2">
                                <div class="col-4">
                                    <label for="mail_host" class="form-label">Mail Host <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <input type="text" name="mail_host" id="mail_host" class="form-control" autocomplete="off" required>
                                    <div class="invalid-tooltip">Enter Mail Host.</div>
                                </div>
                            </div>

                            <!-- Out Port No & SSL -->
                            <div class="row g-2 mb-2">
                                <div class="col-4">
                                    <label for="out_port_no" class="form-label">Out Port No. <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="text" name="out_port_no" id="out_port_no" class="form-control" autocomplete="off" required>
                                        <div class="form-check mb-0" style="min-width: 100px;">
                                            <input class="form-check-input" type="checkbox" name="enable_ssl" id="enable_ssl" value="1">
                                            <label class="form-check-label" for="enable_ssl">Enable SSL</label>
                                        </div>
                                    </div>
                                    <div class="invalid-tooltip">Enter Out Port No.</div>
                                </div>
                            </div>

                            <!-- Reply Email -->
                            <div class="row g-2 mb-2">
                                <div class="col-4">
                                    <label for="reply_email" class="form-label">Reply Email</label>
                                </div>
                                <div class="col-8">
                                    <input type="email" name="reply_email" id="reply_email" class="form-control" autocomplete="off">
                                    <div class="invalid-tooltip">Enter Reply Email.</div>
                                </div>
                            </div>

                            <!-- Purchase Checkbox -->
                            <div class="row g-2 mb-2">
                                <div class="col-4"></div>
                                <div class="col-8">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="purchase" id="purchase" value="1">
                                        <label class="form-check-label" for="purchase">Purchase</label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="submit" form="commonSMTPConfigurationForm" class="btn btn-success" id="submitbtn">Submit</button>
                <button type="button" class="btn btn-warning" id="resetbtn">Reset</button>
                @if(hasAccess("smtp_configuration","add"))
                    <button type="button" class="btn btn-info" id="add_new" style="display:none;">Add New</button>
                @endif
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('script-modal')
    <script src="{{ URL::asset('views/js/smtp_configuration.js?ver='.getJsVersion()) }}"></script>
@endpush
