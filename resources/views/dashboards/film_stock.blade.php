<!-- Card: Industrial X-Ray Film Stock -->
<div class="col-lg-6 col-12">
    <div class="card h-100 shadow-sm border-0">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-3 px-3">
            <h5 class="card-title mb-0 fw-semibold fs-14 text-dark">
                Film Stock
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table nowrap align-middle table-bordered mb-0" data-exclude-search="2" id="tableFilmStock" >
                    <thead>
                        <tr>
                            <th style="width: 50%;">Location</th>
                            <th style="width: 35%;">Industrial X-Ray Films</th>
                            <th style="width: 15%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permittedLocations ?? [] as $idx => $loc)
                            @php $locStock = $filmStockSummary[$loc->location_id]['total_stock'] ?? 0; @endphp
                            <tr>
                                <td>
                                    {{ $loc->location_name }}
                                </td>
                                <td>
                                    {{ $locStock }}
                                </td>
                                <td style="text-align:center; vertical-align:middle;">
                                    <a href="javascript:void(0)" type="button" onclick="openFilmGroupModal('{{ $loc->location_id }}', '{{ addslashes($loc->location_name) }}', '{{ $locStock }}')">
                                        <i class="ri-eye-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">No locations permitted.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: FILM STOCK DRILL-DOWN (2-LEVEL MODAL)                 -->
<!-- ============================================================== -->
<div class="modal fade" id="filmStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            
            <!-- LEVEL 1: ITEM GROUP | TOTAL STOCK | ACTION -->
            <div id="filmLevel1View">
                <div class="modal-header">
                    <h5 class="modal-title fs-15 fw-semibold">
                        Film Stock by Item Group
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-3">
                    <div class="table-responsive">
                        <table class="table nowrap align-middle table-bordered mb-0" id="tableFilmGroups" data-exclude-search="2">
                            <thead>
                                <tr>
                                    <th style="width: 50%;">Item Group</th>
                                    <th style="width: 35%;">Total Stock</th>
                                    <th style="width: 15%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="filmGroupTableBody">
                                <!-- Populated via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                </div>
            </div>

            <!-- LEVEL 2: ITEMS WITHIN SELECTED GROUP -->
            <div id="filmLevel2View" class="d-none">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <h5 class="modal-title fs-15 fw-semibold mb-0">
                            Current Stock Details
                        </h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-3">
                    <div class="table-responsive">
                        <table class="table nowrap align-middle table-bordered mb-0" id="tableFilmGroupItems">
                            <thead>
                                <tr>
                                    <th style="width: 50%;">Item Name</th>
                                    <th style="width: 25%;">Current Stock</th>
                                </tr>
                            </thead>
                            <tbody id="filmItemDetailTableBody">
                                <!-- Populated via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn  btn-secondary fw-medium shadow-none" onclick="backToFilmLevel1()">
                         Back
                    </button>
                    <a href="javascript:void(0);" class="btn btn-link link-success fw-medium shadow-none" data-bs-dismiss="modal">Close</a>
                                    

                </div>
            </div>

        </div>
    </div>
</div>

<script>
    (function() {
        const filmStockRawData = @json($filmStockGroupDetails ?? []);
        let activeLocationData = {
            id: null,
            name: '',
            groups: []
        };

        // Group items for a location by item_group
        function getGroupedData(locId) {
            const rawItems = filmStockRawData[locId] || [];
            const groupsMap = {};

            rawItems.forEach(item => {
                const gName = item.group_name || 'Industrial X-Ray Films';
                if (!groupsMap[gName]) {
                    groupsMap[gName] = {
                        group_name: gName,
                        total_stock: 0,
                        items: []
                    };
                }
                const qty = Number(item.stock_qty) || 0;
                groupsMap[gName].total_stock += qty;
                groupsMap[gName].items.push({
                    item_id: item.item_id,
                    item_name: item.item_name,
                    stock_qty: qty
                });
            });

            return Object.values(groupsMap);
        }

        window.openFilmGroupModal = function(locId, locName, totalStock) {

            if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable('#tableFilmGroups')) {
                jQuery('#tableFilmGroups').DataTable().destroy();
            }
            jQuery('#tableFilmGroups thead tr.search-row').remove();

            activeLocationData.id = locId;
            activeLocationData.name = locName;
            activeLocationData.groups = getGroupedData(locId);

            const badgeEl = document.getElementById('filmLevel1LocBadge');
            if (badgeEl) badgeEl.innerText = locName;

            const tbody = document.getElementById('filmGroupTableBody');
            tbody.innerHTML = '';

            const groups = activeLocationData.groups;
            if (groups.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center py-3 text-muted">No film stock records found for this location.</td></tr>';
            } else {
                groups.forEach((grp, idx) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${grp.group_name}</td>
                        <td>${grp.total_stock}</td>
                        <td style="text-align:center; vertical-align:middle;">
                            <a href="javascript:void(0);" type="button" onclick="showFilmLevel2(${idx})">
                               <i class="ri-eye-fill"></i> 
                            </a>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
                if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable && groups.length > 0) {
                 jQuery('#tableFilmGroups').DataTable({
                    pageLength: 10,
                    lengthChange: false,
                    searching: true,
                    ordering: true,
                    autoWidth: false,
                    dom: 'ltip'
                });
                    if (typeof initColumnSearch === 'function') {
                        initColumnSearch('#tableFilmGroups', [2]);
                    }
                }
            }

            document.getElementById('filmLevel1View').classList.remove('d-none');
            document.getElementById('filmLevel2View').classList.add('d-none');

            // Move modal to body (same as camera modal) so it displays centered and at top without jumping or getting hidden
            const modalEl = document.getElementById('filmStockModal');
            if (modalEl.parentNode !== document.body) {
                document.body.appendChild(modalEl);
            }

            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        };

        window.showFilmLevel2 = function(groupIdx) {
            const grp = activeLocationData.groups[groupIdx];
            if (!grp) return;

            const groupNameEl = document.getElementById('filmLevel2GroupName');
            if (groupNameEl) groupNameEl.innerText = grp.group_name;

            const badgeEl = document.getElementById('filmLevel2LocBadge');
            if (badgeEl) badgeEl.innerText = activeLocationData.name;

            const tbody = document.getElementById('filmItemDetailTableBody');
            tbody.innerHTML = '';
            var $table = jQuery("#filmStockModal").find('#tableFilmGroupItems');
            if (jQuery.fn.DataTable.isDataTable($table)) {
                $table.DataTable().clear().destroy();
            }

            const items = grp.items || [];
            if (items.length === 0) {
                tbody.innerHTML = '<tr><td colspan="2" class="text-center py-3 text-muted">No items found in this group.</td></tr>';
            } else {
                items.forEach(item => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${item.item_name}</td>
                        <td>${item.stock_qty}</td>
                    `;
                    tbody.appendChild(tr);
                });

            }
            if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable && items.length > 0) {
                
                jQuery('#tableFilmGroupItems').DataTable({
                    pageLength: 10,
                    lengthChange: false,
                    searching: true,
                    ordering: true,
                    autoWidth: false,
                    dom: 'ltip'
                });
                if (typeof initColumnSearch === 'function') {
                    initColumnSearch('#tableFilmGroupItems', []);
                }
            }

            document.getElementById('filmLevel1View').classList.add('d-none');
            document.getElementById('filmLevel2View').classList.remove('d-none');
        };

        window.backToFilmLevel1 = function() {
            document.getElementById('filmLevel1View').classList.remove('d-none');
            document.getElementById('filmLevel2View').classList.add('d-none');
        };

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable) {
                jQuery('#tableFilmStock').DataTable({
                    pageLength: 10,
                    lengthChange: false,
                    searching: true,
                    ordering: true,
                    info: true,
                    dom: '<"table-responsive"t><"d-flex justify-content-between align-items-center p-2"ip>',
                });
                jQuery('.dataTables_filter').hide();
            }
        });
    })();
</script>
