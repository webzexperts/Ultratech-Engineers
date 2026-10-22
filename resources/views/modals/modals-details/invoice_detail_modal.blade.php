<div class="modal fade" id="InvoiceDetailModal" aria-labelledby="InvoiceDetailModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="InvoiceDetailModalLabel">Invoice Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="InvoiceDetailForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index" value="-1"/>
                    <input type="hidden" name="row_index" id="row_index" value="-1"/>
                    <input type="hidden" id="detail_edit_index" value="-1">
                    <input type="hidden" id="detail_invoice_detail_id" value="0">
                    <input type="hidden" id="detail_film_dc_id" value="">
                    <input type="hidden" id="detail_film_dc_detail_id" value="">
                    <input type="hidden" id="detail_dc_no" value="">
                    <input type="hidden" id="detail_dc_date" value="">
                    <input type="hidden" id="detail_test_report_no" value="">
                    <input type="hidden" id="detail_film_qty" value="0">

                    <div class="row mt-2">
                        <div class="col-md-8">
                            <!-- Description -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="detail_description" class="form-label pt-0 mt-1">Description <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <textarea class="form-control" id="detail_description" name="detail_description" rows="3" required></textarea>
                                    <div class="invalid-tooltip">Enter Description.</div>
                                </div>
                            </div>

                            <!-- Film Brand -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="detail_film_brand_id" class="form-label pt-0">Film Brand</label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" id="detail_film_brand_id" name="detail_film_brand_id" style="width: 100%;">
                                        <option value="">Select Film Brand</option>
                                        @foreach(getFilmBrands() as $brand)
                                            <option value="{{ $brand->film_brand_id }}">{{ $brand->film_brand }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Film Size -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="detail_film_id" class="form-label pt-0">Film Size</label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" id="detail_film_id" name="detail_film_id" style="width: 100%;">
                                        <option value="">Select Film Size</option>
                                        @foreach(getFilms() as $film)
                                            <option value="{{ $film->film_id }}">{{ $film->film_size_inch }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Qty. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="detail_qty" class="form-label pt-0">Qty. <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <input type="text" class="form-control isNumberKey detail-calc-input" id="detail_qty" name="detail_qty" value="1" required>
                                    <div class="invalid-tooltip">Enter Qty.</div>
                                </div>
                            </div>

                            <!-- Unit Name -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="detail_unit_name" class="form-label pt-0">Unit Name</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="detail_unit_name" name="detail_unit_name" value="Nos">
                                </div>
                            </div>

                            <!-- Rate -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="detail_rate" class="form-label pt-0">Rate <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8 position-relative">
                                    <input type="text" class="form-control isNumberKey detail-calc-input" id="detail_rate" name="detail_rate" required>
                                    <div class="invalid-tooltip">Enter Rate.</div>
                                </div>
                            </div>

                            <!-- Amount -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="detail_amount" class="form-label pt-0">Amount</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="detail_amount" name="detail_amount" readonly tabindex="-1">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" form="InvoiceDetailForm" class="btn btn-primary" id="submitbtn">Add</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>
