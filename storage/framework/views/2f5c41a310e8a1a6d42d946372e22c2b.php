<?php $__env->startSection('title'); ?> Offer <?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<?php $__env->startComponent('components.breadcrumb'); ?>
<?php $__env->slot('li_1'); ?> Transaction <?php $__env->endSlot(); ?>
<?php $__env->slot('title'); ?> Offer <?php $__env->endSlot(); ?>
<?php echo $__env->renderComponent(); ?>

<?php echo $__env->make('modals.modals-transaction.offer_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('modals.modals-details.offer_details_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('modals.modals-details.copy_material_inward_details_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('modals.modals-pending.pending_repair_reports_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('modals.customer_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('modals.modals-details.contact_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('modals.city_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('modals.state_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('modals.country_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('modals.type_of_job_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('modals.job_description_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('modals.material_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('modals.area_of_coverage_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('modals.procedure_reference_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('modals.evaluation_as_per_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('modals.acceptance_standard_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
           <div class="card-header d-flex align-items-center pb-2">
                <h5 class="card-title mb-0 flex-grow-1">Offer</h5>
                <div>
                    <?php if(hasAccess("offer","export")): ?>
                        <a href="javascript:void(0)" class="btn btn-primary" id="export-excel">Excel</a>
                    <?php endif; ?>

                    <?php if(hasAccess("offer","add")): ?>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#OfferModal">Add</button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <table id="dyntable" class="table nowrap align-middle table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="action_col">Actions</th>
                            <th>Offer No.</th>
                            <th>Date</th>
                            <th>NABL</th>
                            <th>Test At</th>
                            <th>Type</th>
                            <th>Customer</th>
                            <th>DC No.</th>
                            <th>DC Date</th>
                            <th>PO No.</th>
                            <th>PO Date</th>
                            <th>Test</th>
                            <th>Type of Job</th>
                            <th>Job Desc.</th>
                            <th>Part No.</th>
                            <th>Drg. No.</th>
                            <th>Material</th>
                            <th>Heat No.</th>
                            <th>RT No.</th>
                            <th>Product Code</th>
                            <th>Qty.</th>
                            <th>Modified By</th>
                            <th>Modified On</th>
                            <th>Created By</th>
                            <th>Created On</th>
                            <th style="display:none;"></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script-manage'); ?>
    <script>
        var headerOpt = {'Authorization':'Bearer <?php echo e(Auth::user()->auth_token); ?>','X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>'};
        jQuery('#export-excel').on('click',function(){
            jQuery('.export_offer').click();
        });
        var table = $('#dyntable').DataTable({
            "processing": false,
            "serverSide": true,
            "scrollX": true,
            "order": [[1, 'desc'],[25, 'desc']],
            dom: 'Blfrtip',
            buttons: [{
                extend:'excel',
                filename: 'Offer List',
                title:"",
                className: 'export_offer d-none',
                exportOptions: {
                    columns: function(idx, data, node) {
                        return idx !== 0 && table.column(idx).visible();
                    },
                    modifier: { page: 'all' }
                },
                action: newexportaction
            }],
            ajax: {
                url: "listing-offer",
                type: "POST",
                headers: headerOpt,
                error: function (jqXHR, textStatus, errorThrown) {
                    jQuery('#dyntable_processing').hide();
                    if (jqXHR.status == 401) {
                        console.log(jqXHR.statusText);
                    } else {
                        console.log('Something went wrong!');
                    }
                    try {
                        console.log(JSON.parse(jqXHR.responseText));
                    } catch (e) {
                        console.log(jqXHR.responseText);
                    }
                }
            },
            columns: [
                { data: 'options', name: 'options', orderable: false, searchable: false, },
                { data: 'offer_no', name: 'offer.offer_no', },
                { data: 'offer_date', name: 'offer.offer_date', },
                { data: 'nabl_type_fix', name: 'offer.nabl_type_fix', },
                { data: 'test_at_fix', name: 'offer.test_at_fix', },
                { data: 'job_type_fix', name: 'offer.job_type_fix', },
                { data: 'customer', name: 'customers.customer', },
                { data: 'dc_no', name: 'offer.dc_no', },
                { data: 'dc_date', name: 'offer.dc_date', },
                { data: 'po_no', name: 'offer.po_no', },
                { data: 'po_date', name: 'offer.po_date', },
                { data: 'type_of_testing_id_fix', name: 'offer_details.type_of_testing_id_fix', },
                { data: 'type_of_job', name: 'type_of_job.type_of_job', },
                { data: 'job_description', name: 'job_descriptions.job_description', },
                { data: 'part_no', name: 'offer_details.part_no', },
                { data: 'drg_no', name: 'offer_details.drg_no', },
                { data: 'material', name: 'materials.material', },
                { data: 'heat_no', name: 'offer_details.heat_no', },
                { data: 'rt_no', name: 'offer_details.rt_no', },
                { data: 'product_code', name: 'offer_details.product_code', },
                { data: 'quantity', name: 'offer_details.quantity', },
                { data: 'last_by', name: 'last_by', },
                { data: 'last_on', name: 'offer.last_on', },
                { data: 'created_by', name: 'created_by', },
                { data: 'created_on', name: 'offer.created_on', },
                { data: 'offer_sequence', name: 'offer.offer_sequence', visible: false, searchable: false, },
            ],
        });

        jQuery('#dyntable tbody').on('click', '.remove-item-btn', function () {
            var data = table.row(jQuery(this).parents('tr')).data();
            toastDelete("Do you want to delete this record?", function () {
                jQuery.ajax({
                    url: "delete-offer",
                    type: 'GET',
                    data: "id=" + data["offer_id"],
                    headers: headerOpt,
                    dataType: 'json',
                    processData: false,
                    success: function (data) {
                        if (data.response_code == 1) {
                            Swal.fire({
                                text: data.response_message,
                                icon: 'success',
                                customClass: {
                                    confirmButton: 'btn btn-primary w-xs mt-2',
                                },
                                buttonsStyling: false
                            })
                            table.row(jQuery(this)).draw(false);
                        } else {
                            console.log(data.response_message);
                            toastr.error(data.response_message);
                        }
                    },
                    error: function (jqXHR) {
                        if (jqXHR.status == 401) {
                            console.log(jqXHR.statusText);
                        } else {
                            console.log('Something went wrong!');
                        }
                        try {
                            console.log(JSON.parse(jqXHR.responseText));
                        } catch (e) {
                            console.log(jqXHR.responseText);
                        }
                    }
                });
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\xampp\htdocs\ultratech-cbs\resources\views/manage/transaction/manage-offer.blade.php ENDPATH**/ ?>