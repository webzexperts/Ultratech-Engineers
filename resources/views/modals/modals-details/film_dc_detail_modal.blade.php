<div class="modal fade" id="FilmDCDetailModal" aria-labelledby="FilmDCDetailModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-lg" style="max-width: 900px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="FilmDCDetailModalLabel">Film DC Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="FilmDCDetailForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="detail_form_mode" id="detail_form_mode" value="add">
                    <input type="hidden" name="detail_row_index" id="detail_row_index">
                    <input type="hidden" name="film_dc_detail_id" id="detail_film_dc_detail_id">
                    <input type="hidden" name="test_report_rt_id" id="detail_test_report_rt_id">
                    <input type="hidden" name="detail_sq_in" id="detail_sq_in" value="0">
                    <input type="hidden" name="detail_sq_cm" id="detail_sq_cm" value="0">
                    <input type="hidden" name="detail_film_size_inch" id="detail_film_size_inch">
                    <input type="hidden" name="detail_film_brand_name" id="detail_film_brand_name">

                    <div class="row g-2">
                        <!-- Column 1 -->
                        <div class="col-md-6">
                            <!-- RT Report No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="detail_test_report_no" class="form-label">RT Report No.</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="detail_test_report_no" name="test_report_no">
                                </div>
                            </div>

                            <!-- Report Date -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="detail_test_report_date" class="form-label">Report Date</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control trans-date-picker" id="detail_test_report_date" name="test_report_date" autocomplete="off">
                                </div>
                            </div>

                            <!-- Die No. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="detail_die_no" class="form-label">Part No. / Die No.</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="detail_die_no" name="die_no">
                                </div>
                            </div>

                            <!-- Film Brand -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="detail_film_brand_id" class="form-label">Film Brand <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="film_brand_id" id="detail_film_brand_id" required>
                                        <option value="">Select Film Brand</option>
                                        @forelse(getFilmBrands() as $fb)
                                            <option value="{{ $fb->film_brand_id }}">{{ $fb->film_brand }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Film Brand.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Column 2 -->
                        <div class="col-md-6">
                            <!-- Film Size (Film) -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="detail_film_id" class="form-label">Film Size (Inch) <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="film_id" id="detail_film_id" required>
                                        <option value="">Select Film Size</option>
                                        @forelse(getFilms() as $f)
                                            <option value="{{ $f->film_id }}" data-sq_in="{{ $f->sq_in }}" data-sq_cm="{{ $f->sq_cm }}" data-size_inch="{{ $f->film_size_inch }}" data-size_cm="{{ $f->film_size_cm }}" data-size="{{ $f->film_size_inch }}">{{ $f->film_size_inch }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Film Size.</div>
                                </div>
                            </div>

                            <!-- DC Qty. -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="detail_dc_quantity" class="form-label">DC Qty. <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control isNumberKey" id="detail_dc_quantity" name="dc_quantity" required>
                                    <div class="invalid-tooltip">Enter DC Qty.</div>
                                </div>
                            </div>

                            <input type="hidden" id="detail_total_sq_in" name="total_sq_in" value="0.000">

                            <!-- Remark -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="detail_remark" class="form-label">Remark</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control" id="detail_remark" name="remark">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer Buttons -->
                    <div class="modal-footer pb-0 px-0 mt-3">
                        <button type="submit" class="btn btn-success" id="detail_submitbtn">Submit</button>
                        <button type="button" class="btn btn-warning" id="detail_resetbtn">Reset</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
