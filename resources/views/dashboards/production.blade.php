

<!-- Card: Location Production Output -->
<div class="col-lg-6 col-12">
    <div class="card h-100 shadow-sm border-0 overflow-hidden">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-3 px-3">
            <h5 class="card-title mb-0 fw-semibold fs-14 text-dark">
                 Production
            </h5>
        </div>
        <div class="card-body p-0">
            <table class="table nowrap align-middle table-bordered mb-0" id="tableProduction" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 34%;">Location</th>
                        <th style="width: 22%; ">Today </th>
                        <th style="width: 22%; ">Yesterday</th>
                        <th style="width: 22%; ">Cumulative</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permittedLocations ?? [] as $idx => $loc)
                        @php
                            $p = $productionSummary[$loc->location_id] ?? ['today' => 0, 'yesterday' => 0, 'cumulative' => 0, 'trend' => '0%', 'is_up' => true];
                        @endphp
                        <tr>
                            <td>
                                {{ $loc->location_name }}
                            </td>
                            <td>
                                <span>{{ number_format($p['today'], 0, '.', '') }} </span>
                            </td>
                            <td>
                                <span>{{ number_format($p['yesterday'], 0, '.', '') }} </span>
                            </td>
                            <td>
                                <span>{{ number_format($p['cumulative'] ?? 0, 0, '.', '') }} </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No locations permitted.</td>
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
                jQuery('#tableProduction').DataTable({
                    pageLength: 10,
                    lengthChange: false,
                    searching: true,
                    ordering: true,
                    info: true,
                    dom: '<"table-responsive"t><"d-flex justify-content-between align-items-center p-2"ip>f'
                });
                jQuery('.dataTables_filter').hide();
            }
        });
    })();
</script>
