<div class="modal fade" id="ProductionEntryDetailsModal" aria-labelledby="ProductionEntryDetailsModalLabel" aria-hidden="true" data-bs-focus="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ProductionEntryDetailsModalLabel">Production Entry Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="ProductionEntryDetailsForm" class="row g-3 needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" id="form_type" value="add"/>
                    <input type="hidden" name="form_index" id="form_index"/>
                    <input type="hidden" name="row_index" id="row_index"/>
                    <input type="hidden" name="production_entry_details_id" id="production_entry_details_id" value="0"/>

                    <div class="row mt-2">
                        {{-- ===== Column 1 ===== --}}
                        <div class="col-md-6">
                            <!-- Shift -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="ped_shift_id" class="form-label">Shift <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="shift_id" id="ped_shift_id" required>
                                        <option value="">Select Shift</option>
                                        @forelse(getShifts() as $shift)
                                            <option value="{{ $shift->shift_id }}">{{ $shift->shift_name }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Shift.</div>
                                </div>
                            </div>

                            <!-- Source -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="ped_source_id_fix" class="form-label">Source <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="source_id_fix" id="ped_source_id_fix" required>
                                        <option value="">Select Source</option>
                                        <option value="Ir-192">Ir-192</option>
                                        <option value="Co-60">Co-60</option>
                                        <option value="X-Ray">X-Ray</option>
                                    </select>
                                    <div class="invalid-tooltip">Select Source.</div>
                                </div>
                            </div>

                            <!-- Item Group -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="ped_item_group_id" class="form-label">Item Group <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="item_group_id" id="ped_item_group_id" required>
                                        <option value="">Select Item Group</option>
                                        @forelse(getItemGroups() as $group)
                                            @if(in_array($group->item_type, ['film', 'Industrial X-Ray Films']))
                                                <option value="{{ $group->id }}">{{ $group->item_group }}</option>
                                            @endif
                                        @empty
                                        @endforelse
                                    </select>
                                    <div class="invalid-tooltip">Select Item Group.</div>
                                </div>
                            </div>

                            <!-- Item Name -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="ped_item_id" class="form-label">Item Name <sup class="astric">*</sup></label>
                                </div>
                                <div class="col-8">
                                    <select class="js-example-basic-single" name="item_id" id="ped_item_id" required>
                                        <option value="">Select Item Name</option>
                                    </select>
                                    <div class="invalid-tooltip">Select Item Name.</div>
                                </div>
                            </div>

                            <!-- Stock Qty -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="ped_stock_qty" class="form-label">Stock Qty.</label>
                                </div>
                                <div class="col-8 d-flex align-items-center">
                                    <input type="text" class="form-control isNumberKey" id="ped_stock_qty" name="stock_qty" readonly tabindex="-1">
                                    <span class="text-nowrap ms-2" id="ped_stock_unit"></span>
                                </div>
                            </div>
                        </div>

                        {{-- ===== Column 2 ===== --}}
                        <div class="col-md-6">
                            <!-- Production Qty -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="ped_production_sq_in" class="form-label">Production Qty.</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control isNumberKeyNotDot" id="ped_production_sq_in" name="production_sq_in" >
                                    <div class="invalid-tooltip">Enter Production Qty.</div>
                                </div>
                            </div>

                            <!-- Retake Qty -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="ped_retake_sq_in" class="form-label">Retake Qty.</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control isNumberKeyNotDot" id="ped_retake_sq_in" name="retake_sq_in" >
                                    <div class="invalid-tooltip">Enter Retake Qty.</div>
                                </div>
                            </div>

                            <!-- Wastage Qty -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="ped_westage_in" class="form-label">Wastage Qty.</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control isNumberKeyNotDot" id="ped_westage_in" name="westage_in" >
                                    <div class="invalid-tooltip">Enter Wastage Qty.</div>
                                </div>
                            </div>

                            <!-- Total Consumption Qty -->
                            <div class="row g-2 mb-1">
                                <div class="col-4">
                                    <label for="ped_total_sq_in" class="form-label">Total Cons. Qty.</label>
                                </div>
                                <div class="col-8">
                                    <input type="text" class="form-control skip-tab" id="ped_total_sq_in" name="total_sq_in" readonly tabindex="-1">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="add_details_btn">Add</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    var all_items_cache = @json(getItemsForProduction());
</script>
