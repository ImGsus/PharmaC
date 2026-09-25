{{-- Report modal — included from admin.pos.orders --}}
<div class="modal fade" id="reportModal" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">
                    <i class="fas fa-chart-bar"></i> Sales Report
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="btn-group btn-group-toggle mb-3" role="group" id="report-period-group">
                    <button type="button" class="btn btn-outline-primary active" data-period="daily">Daily</button>
                    <button type="button" class="btn btn-outline-primary" data-period="weekly">Weekly</button>
                    <button type="button" class="btn btn-outline-primary" data-period="monthly">Monthly</button>
                    <button type="button" class="btn btn-outline-primary" data-period="yearly">Yearly</button>
                </div>
                <div id="report-result">
                    <p class="text-muted">Choose a period above to load the report.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@once
@push('page-js')
<script>
(function(){
    var resultEl = document.getElementById('report-result');
    var group    = document.getElementById('report-period-group');
    if (!resultEl || !group) return;
    var currency = '{{ settings('app_currency', '$') }}';

    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function loadReport(period) {
        resultEl.innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Loading…</p>';
        var url = '{{ route('pos.orders.report') }}?period=' + encodeURIComponent(period);
        fetch(url, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var rows = (data.sales || []).map(function (s) {
                    return '<tr>'
                        + '<td>#' + escapeHtml(s.id) + '</td>'
                        + '<td>' + escapeHtml(s.product_name) + '</td>'
                        + '<td class="text-center">' + escapeHtml(s.quantity) + '</td>'
                        + '<td class="text-right">' + currency + ' ' + escapeHtml(s.total_price) + '</td>'
                        + '<td>' + escapeHtml(s.created_at || '') + '</td>'
                        + '</tr>';
                }).join('');

                var summary =
                    '<div class="row mb-3">'
                    +   '<div class="col-md-6"><div class="alert alert-info mb-0"><strong>'
                    +       escapeHtml(data.label) + '</strong><br>'
                    +       escapeHtml(data.start) + ' → ' + escapeHtml(data.end)
                    +   '</div></div>'
                    +   '<div class="col-md-6"><div class="alert alert-success mb-0"><strong>'
                    +       'Total income: ' + currency + ' ' + escapeHtml(data.income)
                    +   '</strong><br>' + escapeHtml(data.count) + ' order(s)</div></div>'
                    + '</div>';

                var table = rows
                    ? '<div class="table-responsive"><table class="pos-modal-table">'
                        +   '<thead><tr><th>ID</th><th>Product</th><th class="text-center">Qty</th><th class="text-right">Total</th><th>Date</th></tr></thead>'
                        +   '<tbody>' + rows + '</tbody>'
                        + '</table></div>'
                    : '<div class="alert alert-warning">No sales recorded in this period.</div>';

                resultEl.innerHTML = summary + table;
            })
            .catch(function () {
                resultEl.innerHTML = '<div class="alert alert-danger">Failed to load report.</div>';
            });
    }

    group.querySelectorAll('button[data-period]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            group.querySelectorAll('button[data-period]').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            loadReport(btn.getAttribute('data-period'));
        });
    });

    $('#reportModal').on('show.bs.modal', function () {
        var active = group.querySelector('button.active');
        loadReport(active ? active.getAttribute('data-period') : 'daily');
    });
})();
</script>
@endpush
@endonce
