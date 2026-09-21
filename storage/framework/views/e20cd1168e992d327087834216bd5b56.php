<div class="modal fade" id="CopyMaterialInwardDetailsModal" aria-labelledby="CopyMaterialInwardDetailsModalLabel" aria-hidden="true" data-custom-hide-focus="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="CopyMaterialInwardDetailsModalLabel"> Old Material Inward Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="copyMaterialInwardDetailsForm" class="row g-3 needs-validation" novalidate>
                    <?php echo csrf_field(); ?>
                    <div class="table-responsive">
                        <table class="table nowrap align-middle table-bordered remove-reset-filter cul-search" id="CopyMaterialInwardDetailsTable" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="radio_column"></th>
                                    <th>Inward No.</th>
                                    <th>Inward Date</th>
                                    <th>Part No.</th>
                                    <th>Drg. No.</th>
                                    <th>Heat No.</th>
                                    <th>RT No.</th>
                                    <th>Product Code</th>
                                    <th>Thickness</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be populated via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="submitCopyMaterialInwardDetailsBtn">Add</button>
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>
<?php /**PATH F:\xampp\htdocs\ultratech-cbs\resources\views/modals/modals-details/copy_material_inward_details_modal.blade.php ENDPATH**/ ?>