@php
    $totalIrActive = array_sum(array_column($cameraSummary ?? [], 'ir192_active'));
    $totalCoActive = array_sum(array_column($cameraSummary ?? [], 'co60_active'));
    $totalXrActive = array_sum(array_column($cameraSummary ?? [], 'xray_active'));
    $grandTotalActive = $totalIrActive + $totalCoActive + $totalXrActive;
@endphp

<!-- Card: Radiography Sources & Cameras -->
<div class="col-lg-6 col-12">
    <div class="card h-100 shadow-sm border-0 overflow-hidden">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-3 px-3">
            <h5 class="card-title mb-0 fw-semibold fs-14 text-dark">
                Camera
            </h5>
        </div>
        <div class="card-body p-0">
             <div class="table-responsive">
            <table class="table nowrap align-middle table-bordered mb-0" data-exclude-search="4" id="tableCamera" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 40%;">Location</th>
                        <th style="width: 15%;">Ir-192</th>
                        <th style="width: 15%;">Co-60</th>
                        <th style="width: 15%;">X-Ray</th>
                        <th style="width: 15%; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permittedLocations ?? [] as $loc)
                        @php
                            $cam = $cameraSummary[$loc->location_id] ?? [
                                'ir192_total' => 0, 'ir192_active' => 0,
                                'co60_total' => 0, 'co60_active' => 0,
                                'xray_total' => 0, 'xray_active' => 0,
                                'total_count' => 0, 'total_active' => 0
                            ];
                        @endphp
                        <tr>
                            <td>{{ $loc->location_name }}</td>
                            <td>{{ $cam['ir192_active'] }}</td>
                            <td>{{ $cam['co60_active'] }}</td>
                            <td>{{ $cam['xray_active'] }}</td>
                            <td style="text-align:center; vertical-align:middle;">
                                <a href="javascript:void(0)" type="button" onclick="openCameraModal('{{ $loc->location_id }}', '{{ addslashes($loc->location_name) }}')">
                                    <i class="ri-eye-fill"></i> 
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">No data.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="fw-bold">
                    <tr>
                        <th class="text-end">Total:</th>
                        <th>{{ $totalIrActive }}</th>
                        <th>{{ $totalCoActive }}</th>
                        <th>{{ $totalXrActive }}</th>
                        <th style="text-align: center;">{{ $grandTotalActive }}</th>
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: CAMERA DETAILS MODAL (Isotope | Camera Name | Sr. No.)-->
<!-- ============================================================== -->
<div class="modal fade" id="cameraDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fs-15 fw-semibold">
                    Camera Inventory Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="table-responsive">
                    <table class="table nowrap align-middle table-bordered mb-0" data-exclude-search="" id="cameraItemsTable" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 25%;">Isotope</th>
                                <th style="width: 45%;">Camera Name</th>
                                <th style="width: 30%;">Sr. No.</th>
                            </tr>
                        </thead>
                        <tbody id="cameraItemsTableBody">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const cameraDetailsData = @json($cameraDetails ?? []);

        window.openCameraModal = function(locId, locName) {
            if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable('#cameraItemsTable')) {
                jQuery('#cameraItemsTable').DataTable().destroy();
            }
            jQuery('#cameraItemsTable thead tr.search-row').remove();

            const tbody = document.getElementById('cameraItemsTableBody');
            tbody.innerHTML = '';

            const items = cameraDetailsData[locId] || [];
            if (items.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center py-3 text-muted">No cameras found at this location.</td></tr>';
            } else {
                items.forEach((cam, idx) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${cam.isotope || ''}</td>
                        <td>${cam.camera_name || ''}</td>
                        <td>${cam.serial_no || ''}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            const modalEl = document.getElementById('cameraDetailModal');
            if (modalEl.parentNode !== document.body) {
                document.body.appendChild(modalEl);
            }

            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable && items.length > 0) {
                jQuery('#cameraItemsTable').DataTable({
                    pageLength: 10,
                    lengthChange: false,
                    searching: true,
                    ordering: true,
                    autoWidth: false,
                    dom: 'ltip'
                });
                //     pageLength: 10,
                //     lengthChange: false,
                //     searching: true,
                //     ordering: true,
                //     info: false,
                //     paging: false,
                //     autoWidth: false,
                //     dom: 't'
                // });
                if (typeof initColumnSearch === 'function') {
                    initColumnSearch('#cameraItemsTable', []);
                }
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable) {
                jQuery('#tableCamera').DataTable({
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
