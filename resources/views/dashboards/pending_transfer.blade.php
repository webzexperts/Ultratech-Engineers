

<!-- Card: Pending Inter Location Transfers -->
<div class="col-lg-6 col-12">
    <div class="card h-100 shadow-sm border-0 overflow-hidden">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-3 px-3">
            <h5 class="card-title mb-0 fw-semibold fs-14 text-dark">
                Pending Inter Location Transfer
            </h5>
        </div>
        <div class="card-body p-0">
            <table class="table nowrap align-middle table-bordered mb-0" id="tableTransfer" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 32%;">From Location</th>
                        <th style="width: 33%;">To Location</th>
                        <th style="width: 20%;">DC No.</th>
                        <th style="width: 15%;">DC Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingTransfers ?? [] as $idx => $tr)
                        <tr>
                            <td>
                                {{ $tr->from_location_name ?? '' }}
                            </td>
                            <td>
                                {{ $tr->to_location_name ?? '' }}
                            </td>
                            <td>
                                {{ $tr->dc_number ?? '' }}
                            </td>
                            <td>
                                {{ !empty($tr->dc_date) ? date('d/m/Y', strtotime($tr->dc_date)) : '' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="ri-inbox-line fs-22 d-block mb-1"></i>
                                No pending inter-location transfers awaiting GRN.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable) {
                jQuery('#tableTransfer').DataTable({
                    pageLength: 10,
                    lengthChange: false,
                    searching: true,
                    ordering: true,
                    info: true,
                    dom: '<"table-responsive"t><"d-flex justify-content-between align-items-center p-2"ip>'
                });
                jQuery('.dataTables_filter').hide();
            }
        });
    })();
</script>
