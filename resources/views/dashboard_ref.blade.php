@extends('layouts.master')

@section('title')
    @lang('translation.dashboards')
@endsection

@section('css')
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .live-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #64748b;
            transition: all 0.3s ease;
        }
        .live-indicator.active {
            background-color: var(--vz-success);
            box-shadow: 0 0 10px var(--vz-success);
            animation: pulse-live 1.5s infinite;
        }
        @keyframes pulse-live {
            0% { opacity: 0.6; }
            50% { opacity: 1; }
            100% { opacity: 0.6; }
        }
        
        .new-row-highlight {
            animation: highlight-row 1.5s ease-out;
        }
        @keyframes highlight-row {
            0% { background-color: rgba(16, 185, 129, 0.15); }
            100% { background-color: transparent; }
        }
        
        .dashboard-card {
            height: calc(100% - 32px);
            margin-bottom: 32px;
        }
        .table-card {
            max-height: 380px;
            overflow-y: auto;
        }
    </style>
@endsection

@section('content')
    <!-- Breadcrumb -->
    @component('components.breadcrumb')
        @slot('li_1') Dashboards @endslot
        @slot('title') NDT Operations Control Centre @endslot
    @endcomponent

    <!-- Welcome Message & Controls -->
    <div class="row mb-4 align-items-center">
        <div class="col-sm-6">
            <h4 class="fs-18 mb-1">Welcome back, {{ Auth::user()->user_name }}!</h4>
            <p class="text-muted mb-0">System status is fully active. Below are the operational statistics for Ultratech.</p>
        </div>
        <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
            <div class="d-inline-flex align-items-center bg-light border rounded-pill px-3 py-2">
                <span class="live-indicator me-2" id="liveIndicator"></span>
                <span class="text-muted fw-medium fs-13 me-3" style="user-select:none;">LIVE SIMULATOR</span>
                <div class="form-check form-switch p-0 m-0" style="min-height: auto;">
                    <input class="form-check-input ms-0" type="checkbox" role="switch" id="liveFeedSwitch" style="cursor: pointer; width: 36px; height: 18px;">
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Grids Row 1: Pending Transfers & Cameras -->
    <div class="row">
        <!-- Grid 1: Pending Inter Location Transfer -->
        <div class="col-xl-6">
            <div class="card dashboard-card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Pending Inter Location Transfers</h4>
                    <span class="badge bg-soft-warning text-warning fs-12">Action Pending</span>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive table-card">
                        <table class="table align-middle table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">From Location</th>
                                    <th scope="col">To Location</th>
                                    <th scope="col">DC No.</th>
                                    <th scope="col">DC Date</th>
                                </tr>
                            </thead>
                            <tbody id="pendingTransfersTable">
                                @foreach($pendingTransfers as $pt)
                                    <tr>
                                        <td><i class="fa-solid fa-arrow-up-from-bracket text-primary me-2"></i>{{ $pt['from_location'] }}</td>
                                        <td><i class="fa-solid fa-arrow-down-to-bracket text-success me-2"></i>{{ $pt['to_location'] }}</td>
                                        <td class="fw-semibold text-primary">{{ $pt['dc_number'] }}</td>
                                        <td>{{ $pt['dc_date'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid 2: Camera Locations & Source counts -->
        <div class="col-xl-6">
            <div class="card dashboard-card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Active Radiography Sources & Cameras</h4>
                    <span class="badge bg-soft-info text-info fs-12">AERB Monitored</span>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive table-card">
                        <table class="table align-middle table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Location</th>
                                    <th scope="col" class="text-center">Ir-192</th>
                                    <th scope="col" class="text-center">Co-60</th>
                                    <th scope="col" class="text-center">X-Ray</th>
                                    <th scope="col" class="text-center">Total Count</th>
                                    <th scope="col" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="cameraCountsTable">
                                @foreach($cameraCounts as $cc)
                                    <tr>
                                        <td><i class="fa-solid fa-building-circle-check text-muted me-2"></i>{{ $cc['location_name'] }}</td>
                                        <td class="text-center fw-medium">{{ $cc['ir192_count'] }}</td>
                                        <td class="text-center fw-medium">{{ $cc['co60_count'] }}</td>
                                        <td class="text-center fw-medium">{{ $cc['xray_count'] }}</td>
                                        <td class="text-center"><span class="badge bg-soft-primary text-primary fs-12 px-2">{{ $cc['total_count'] }}</span></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-soft-primary view-camera-btn" data-location-id="{{ $cc['location_id'] }}" data-location-name="{{ $cc['location_name'] }}" title="View Cameras list">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Grids Row 2: Production Output & Film Stock -->
    <div class="row">
        <!-- Grid 3: Location Production Output -->
        <div class="col-xl-6">
            <div class="card dashboard-card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Location Production Volume</h4>
                    <span class="badge bg-soft-success text-success fs-12">Today vs Yesterday</span>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive table-card">
                        <table class="table align-middle table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Location</th>
                                    <th scope="col" class="text-end">Today's Output</th>
                                    <th scope="col" class="text-end">Yesterday's Output</th>
                                    <th scope="col" class="text-center">Trend</th>
                                </tr>
                            </thead>
                            <tbody id="productionTable">
                                @foreach($productionData as $prod)
                                    <tr>
                                        <td><i class="fa-solid fa-industry text-muted me-2"></i>{{ $prod['location_name'] }}</td>
                                        <td class="text-end fw-semibold text-primary">{{ number_format($prod['today_prod']) }} SQIN</td>
                                        <td class="text-end text-muted">{{ number_format($prod['yesterday_prod']) }} SQIN</td>
                                        <td class="text-center">
                                            @if($prod['today_prod'] >= $prod['yesterday_prod'])
                                                <span class="text-success"><i class="fa-solid fa-circle-arrow-up me-1"></i>Up</span>
                                            @else
                                                <span class="text-danger"><i class="fa-solid fa-circle-arrow-down me-1"></i>Down</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid 4: Film Stock Inventory -->
        <div class="col-xl-6">
            <div class="card dashboard-card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Industrial X-Ray Film Stock</h4>
                    <span class="badge bg-soft-info text-info fs-12">Film Sheets</span>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive table-card">
                        <table class="table align-middle table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Location</th>
                                    <th scope="col" class="text-end">Film Stock Total</th>
                                    <th scope="col" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="filmStockTable">
                                @foreach($filmStockData as $fs)
                                    <tr>
                                        <td><i class="fa-regular fa-images text-muted me-2"></i>{{ $fs['location_name'] }}</td>
                                        <td class="text-end fw-semibold text-success">{{ number_format($fs['total_film_stock']) }} SQIN</td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-soft-primary view-film-grouped-btn" data-location-id="{{ $fs['location_id'] }}" data-location-name="{{ $fs['location_name'] }}" title="View Stock Groups">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 1: Camera Details Modal -->
    <div class="modal fade" id="cameraDetailsModal" tabindex="-1" aria-labelledby="cameraDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cameraDetailsModalLabel">Active Cameras List</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Isotope</th>
                                    <th>Camera Name</th>
                                    <th>Serial No.</th>
                                </tr>
                            </thead>
                            <tbody id="cameraDetailsModalBody">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 2: Film Stock Grouped Modal (Level 2) -->
    <div class="modal fade" id="filmGroupedModal" tabindex="-1" aria-labelledby="filmGroupedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filmGroupedModalLabel">Film Inventory Grouped</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item Group</th>
                                    <th>Item Name</th>
                                    <th class="text-end">Stock Total</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="filmGroupedModalBody">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal 3: Film Current Stock Details Modal (Level 3) -->
    <div class="modal fade" id="filmDetailsModal" tabindex="-1" aria-labelledby="filmDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-soft-info">
                    <h5 class="modal-title" id="filmDetailsModalLabel">Current Stock Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item Name</th>
                                    <th class="text-end">Opening Stock</th>
                                    <th class="text-end">Current Stock</th>
                                    <th>Last Updated</th>
                                </tr>
                            </thead>
                            <tbody id="filmDetailsModalBody">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Back</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        // Receive server variables
        let pendingTransfers = @json($pendingTransfers);
        let cameraCounts = @json($cameraCounts);
        const cameraDetailsJson = @json($cameraDetails);
        let productionData = @json($productionData);
        let filmStockData = @json($filmStockData);
        let filmStockGroupedJson = @json($filmStockGrouped);
        let filmStockDetailsJson = @json($filmStockDetails);

        // Modal states for live updates
        let currentOpenModal = { type: null, locationId: null, itemName: null, locationName: null };

        // DOM elements
        const liveSwitch = document.getElementById("liveFeedSwitch");
        const liveIndicator = document.getElementById("liveIndicator");

        // Render operational grids
        function renderOperationalGrids() {
            // 1. Pending Transfers Table
            const ptTable = document.getElementById("pendingTransfersTable");
            ptTable.innerHTML = "";
            pendingTransfers.forEach(pt => {
                ptTable.innerHTML += `
                    <tr class="${pt.isNew ? 'new-row-highlight' : ''}">
                        <td><i class="fa-solid fa-arrow-up-from-bracket text-primary me-2"></i>${pt.from_location}</td>
                        <td><i class="fa-solid fa-arrow-down-to-bracket text-success me-2"></i>${pt.to_location}</td>
                        <td class="fw-semibold text-primary">${pt.dc_number}</td>
                        <td>${pt.dc_date}</td>
                    </tr>
                `;
                if (pt.isNew) setTimeout(() => delete pt.isNew, 2000);
            });

            // 2. Cameras count table
            const camTable = document.getElementById("cameraCountsTable");
            camTable.innerHTML = "";
            cameraCounts.forEach(cc => {
                camTable.innerHTML += `
                    <tr class="${cc.isNew ? 'new-row-highlight' : ''}">
                        <td><i class="fa-solid fa-building-circle-check text-muted me-2"></i>${cc.location_name}</td>
                        <td class="text-center fw-medium">${cc.ir192_count}</td>
                        <td class="text-center fw-medium">${cc.co60_count}</td>
                        <td class="text-center fw-medium">${cc.xray_count}</td>
                        <td class="text-center"><span class="badge bg-soft-primary text-primary fs-12 px-2">{{ $cc['total_count'] }}</span></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-soft-primary view-camera-btn" data-location-id="${cc.location_id}" data-location-name="${cc.location_name}" title="View Cameras list">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
                if (cc.isNew) setTimeout(() => delete cc.isNew, 2000);
            });

            // Bind camera buttons
            document.querySelectorAll(".view-camera-btn").forEach(btn => {
                btn.addEventListener("click", function() {
                    const locId = parseInt(this.getAttribute("data-location-id"));
                    const locName = this.getAttribute("data-location-name");
                    
                    currentOpenModal = { type: 'camera', locationId: locId, itemName: null, locationName: locName };
                    populateCameraModal();
                    
                    const modal = new bootstrap.Modal(document.getElementById('cameraDetailsModal'));
                    modal.show();
                });
            });

            // 3. Location Production table
            const prodTable = document.getElementById("productionTable");
            prodTable.innerHTML = "";
            productionData.forEach(prod => {
                prodTable.innerHTML += `
                    <tr class="${prod.isNew ? 'new-row-highlight' : ''}">
                        <td><i class="fa-solid fa-industry text-muted me-2"></i>${prod.location_name}</td>
                        <td class="text-end fw-semibold text-primary">${Number(prod.today_prod).toLocaleString()} SQIN</td>
                        <td class="text-end text-muted">${Number(prod.yesterday_prod).toLocaleString()} SQIN</td>
                        <td class="text-center">
                            ${prod.today_prod >= prod.yesterday_prod ? '<span class="text-success"><i class="fa-solid fa-circle-arrow-up me-1"></i>Up</span>' : '<span class="text-danger"><i class="fa-solid fa-circle-arrow-down me-1"></i>Down</span>'}
                        </td>
                    </tr>
                `;
                if (prod.isNew) setTimeout(() => delete prod.isNew, 2000);
            });

            // 4. Film Stock table
            const fsTable = document.getElementById("filmStockTable");
            fsTable.innerHTML = "";
            filmStockData.forEach(fs => {
                fsTable.innerHTML += `
                    <tr class="${fs.isNew ? 'new-row-highlight' : ''}">
                        <td><i class="fa-regular fa-images text-muted me-2"></i>${fs.location_name}</td>
                        <td class="text-end fw-semibold text-success">${Number(fs.total_film_stock).toLocaleString()} SQIN</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-soft-primary view-film-grouped-btn" data-location-id="${fs.location_id}" data-location-name="${fs.location_name}">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
                if (fs.isNew) setTimeout(() => delete fs.isNew, 2000);
            });

            // Re-bind grouped film buttons
            document.querySelectorAll(".view-film-grouped-btn").forEach(btn => {
                btn.addEventListener("click", function() {
                    const locId = parseInt(this.getAttribute("data-location-id"));
                    const locName = this.getAttribute("data-location-name");
                    
                    currentOpenModal = { type: 'filmGrouped', locationId: locId, itemName: null, locationName: locName };
                    populateFilmGroupedModal();

                    const modal = new bootstrap.Modal(document.getElementById('filmGroupedModal'));
                    modal.show();
                });
            });
        }

        // Modal data populators
        function populateCameraModal() {
            if (currentOpenModal.type !== 'camera') return;
            document.getElementById("cameraDetailsModalLabel").innerText = `Camera Inventory - ${currentOpenModal.locationName}`;
            const modalBody = document.getElementById("cameraDetailsModalBody");
            modalBody.innerHTML = "";
            
            const list = cameraDetailsJson[currentOpenModal.locationId] || [];
            if (list.length === 0) {
                modalBody.innerHTML = `<tr><td colspan="3" class="text-center py-3 text-muted">No active radiography sources.</td></tr>`;
            } else {
                list.forEach(cam => {
                    modalBody.innerHTML += `
                        <tr class="${cam.isNew ? 'new-row-highlight' : ''}">
                            <td><span class="badge bg-soft-primary text-primary">${cam.isotope}</span></td>
                            <td class="fw-medium">${cam.camera_name}</td>
                            <td class="text-muted">${cam.serial_no}</td>
                        </tr>
                    `;
                    if (cam.isNew) setTimeout(() => delete cam.isNew, 2000);
                });
            }
        }

        function populateFilmGroupedModal() {
            if (currentOpenModal.type !== 'filmGrouped') return;
            document.getElementById("filmGroupedModalLabel").innerText = `Film Inventory - ${currentOpenModal.locationName}`;
            const modalBody = document.getElementById("filmGroupedModalBody");
            modalBody.innerHTML = "";

            const list = filmStockGroupedJson[currentOpenModal.locationId] || [];
            if (list.length === 0) {
                modalBody.innerHTML = `<tr><td colspan="4" class="text-center py-3 text-muted">No stock totals found.</td></tr>`;
            } else {
                list.forEach(fg => {
                    modalBody.innerHTML += `
                        <tr class="${fg.isNew ? 'new-row-highlight' : ''}">
                            <td class="text-muted">${fg.group_name}</td>
                            <td class="fw-semibold">${fg.item_name}</td>
                            <td class="text-end fw-semibold text-success">${Number(fg.stock_total).toLocaleString()} SQIN</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-soft-info view-item-details-btn" data-location-id="${currentOpenModal.locationId}" data-item-name="${fg.item_name}">
                                    <i class="fa-solid fa-eye"></i> View Details
                                </button>
                            </td>
                        </tr>
                    `;
                    if (fg.isNew) setTimeout(() => delete fg.isNew, 2000);
                });
                bindFilmDetailsButtons();
            }
        }

        function bindFilmDetailsButtons() {
            document.querySelectorAll(".view-item-details-btn").forEach(btn => {
                btn.addEventListener("click", function() {
                    const locId = parseInt(this.getAttribute("data-location-id"));
                    const itemName = this.getAttribute("data-item-name");
                    
                    currentOpenModal = { type: 'filmDetails', locationId: locId, itemName: itemName, locationName: null };
                    populateFilmDetailsModal();

                    const modal = new bootstrap.Modal(document.getElementById('filmDetailsModal'));
                    modal.show();
                });
            });
        }

        function populateFilmDetailsModal() {
            if (currentOpenModal.type !== 'filmDetails') return;
            document.getElementById("filmDetailsModalLabel").innerText = `Stock Details - ${currentOpenModal.itemName}`;
            const modalBody = document.getElementById("filmDetailsModalBody");
            modalBody.innerHTML = "";

            const list = filmStockDetailsJson[currentOpenModal.locationId] || [];
            const details = list.filter(d => d.item_name === currentOpenModal.itemName);

            if (details.length === 0) {
                modalBody.innerHTML = `<tr><td colspan="4" class="text-center py-3 text-muted">No details found.</td></tr>`;
            } else {
                details.forEach(det => {
                    modalBody.innerHTML += `
                        <tr class="${det.isNew ? 'new-row-highlight' : ''}">
                            <td class="fw-medium">${det.item_name}</td>
                            <td class="text-end text-muted">${Number(det.opening_stock).toLocaleString()} SQIN</td>
                            <td class="text-end fw-semibold text-success">${Number(det.current_stock).toLocaleString()} SQIN</td>
                            <td>${det.last_updated}</td>
                        </tr>
                    `;
                    if (det.isNew) setTimeout(() => delete det.isNew, 2000);
                });
            }
        }

        // Modal close listeners to clean tracking state
        document.getElementById('cameraDetailsModal').addEventListener('hidden.bs.modal', () => { currentOpenModal = { type: null, locationId: null, itemName: null, locationName: null }; });
        document.getElementById('filmGroupedModal').addEventListener('hidden.bs.modal', () => { currentOpenModal = { type: null, locationId: null, itemName: null, locationName: null }; });
        document.getElementById('filmDetailsModal').addEventListener('hidden.bs.modal', () => { 
            const locName = document.getElementById("filmGroupedModalLabel").innerText.replace("Film Inventory - ", "");
            const locId = Object.keys(filmStockGroupedJson).find(id => filmStockData.find(d => d.location_id == id).location_name === locName);
            if (locId) {
                currentOpenModal = { type: 'filmGrouped', locationId: parseInt(locId), itemName: null, locationName: locName };
            } else {
                currentOpenModal = { type: null, locationId: null, itemName: null, locationName: null };
            }
        });

        // Dynamic Live Operations Simulation Tick
        function simulateNewInspection() {
            const dateString = new Date().toISOString().split('T')[0];
            const roll = Math.random();
            const year = new Date().getFullYear();

            // A. Pending Transfers Simulation
            if (roll < 0.3) {
                const fromLoc = cameraCounts[Math.floor(Math.random() * cameraCounts.length)].location_name;
                let toLoc = cameraCounts[Math.floor(Math.random() * cameraCounts.length)].location_name;
                while (fromLoc === toLoc) {
                    toLoc = cameraCounts[Math.floor(Math.random() * cameraCounts.length)].location_name;
                }
                const dcNum = `ILT-${year}-${Math.floor(1000 + Math.random() * 9000)}`;
                pendingTransfers.unshift({
                    from_location: fromLoc,
                    to_location: toLoc,
                    dc_number: dcNum,
                    dc_date: dateString,
                    isNew: true
                });
                if (pendingTransfers.length > 5) pendingTransfers.pop();
            } else if (roll < 0.6 && pendingTransfers.length > 0) {
                pendingTransfers.pop();
            }

            // B. Camera Location Movements Simulation
            if (roll < 0.25) {
                const fromLocIdx = Math.floor(Math.random() * cameraCounts.length);
                const fromLocId = cameraCounts[fromLocIdx].location_id;
                const fromLocList = cameraDetailsJson[fromLocId];
                
                if (fromLocList && fromLocList.length > 1) {
                    let toLocIdx = Math.floor(Math.random() * cameraCounts.length);
                    while (toLocIdx === fromLocIdx) {
                        toLocIdx = Math.floor(Math.random() * cameraCounts.length);
                    }
                    const toLocId = cameraCounts[toLocIdx].location_id;

                    const camIdx = Math.floor(Math.random() * fromLocList.length);
                    const [movedCam] = fromLocList.splice(camIdx, 1);
                    movedCam.isNew = true;
                    
                    if (!cameraDetailsJson[toLocId]) cameraDetailsJson[toLocId] = [];
                    cameraDetailsJson[toLocId].push(movedCam);

                    const iso = movedCam.isotope;
                    if (iso === 'Ir-192') {
                        cameraCounts[fromLocIdx].ir192_count--;
                        cameraCounts[toLocIdx].ir192_count++;
                    } else if (iso === 'Co-60') {
                        cameraCounts[fromLocIdx].co60_count--;
                        cameraCounts[toLocIdx].co60_count++;
                    } else {
                        cameraCounts[fromLocIdx].xray_count--;
                        cameraCounts[toLocIdx].xray_count++;
                    }
                    cameraCounts[fromLocIdx].total_count--;
                    cameraCounts[toLocIdx].total_count++;
                    cameraCounts[fromLocIdx].isNew = true;
                    cameraCounts[toLocIdx].isNew = true;

                    if (currentOpenModal.type === 'camera' && (currentOpenModal.locationId === fromLocId || currentOpenModal.locationId === toLocId)) {
                        populateCameraModal();
                    }
                }
            }

            // C. Production outputs fluctuation
            const activeLocIdx = Math.floor(Math.random() * productionData.length);
            const prodIncrement = Math.floor(30 + Math.random() * 80);
            productionData[activeLocIdx].today_prod += prodIncrement;
            productionData[activeLocIdx].isNew = true;

            // D. Film Stock consumption simulation
            if (roll > 0.4) {
                const activeFsIdx = Math.floor(Math.random() * filmStockData.length);
                const locId = filmStockData[activeFsIdx].location_id;
                const filmGroup = filmStockGroupedJson[locId];
                
                if (filmGroup && filmGroup.length > 0) {
                    const itemIdx = Math.floor(Math.random() * filmGroup.length);
                    const item = filmGroup[itemIdx];
                    const consumption = Math.floor(100 + Math.random() * 200);
                    
                    if (item.stock_total > consumption) {
                        item.stock_total -= consumption;
                        item.isNew = true;
                        filmStockData[activeFsIdx].total_film_stock -= consumption;
                        filmStockData[activeFsIdx].isNew = true;

                        const detailList = filmStockDetailsJson[locId];
                        if (detailList) {
                            const detItem = detailList.find(d => d.item_name === item.item_name);
                            if (detItem && detItem.current_stock > consumption) {
                                detItem.current_stock -= consumption;
                                detItem.last_updated = dateString;
                                detItem.isNew = true;
                            }
                        }

                        if (currentOpenModal.type === 'filmGrouped' && currentOpenModal.locationId === locId) {
                            populateFilmGroupedModal();
                        } else if (currentOpenModal.type === 'filmDetails' && currentOpenModal.locationId === locId && currentOpenModal.itemName === item.item_name) {
                            populateFilmDetailsModal();
                        }
                    }
                }
            }

            renderOperationalGrids();
        }

        let liveInterval = null;
        liveSwitch.addEventListener("change", function(e) {
            if (e.target.checked) {
                liveIndicator.classList.add("active");
                liveInterval = setInterval(simulateNewInspection, 4000);
            } else {
                liveIndicator.classList.remove("active");
                clearInterval(liveInterval);
                liveInterval = null;
            }
        });

        window.onload = function() {
            renderOperationalGrids();
        };
    </script>
@endsection
