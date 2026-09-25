{{-- History modal — included from admin.pos.orders --}}
<div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">
                    <i class="fas fa-history"></i> Order History
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Customer name</label>
                    <input type="text" id="history-customer" class="form-control" placeholder="Type a customer name to filter">
                </div>
                <div id="history-result" class="mt-3">
                    <p class="text-muted">Type a customer name above, then click <strong>Search</strong>.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="history-search-btn">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </div>
    </div>
</div>

@once
@push('page-js')
<script>
(function(){
    var searchBtn = document.getElementById('history-search-btn');
    var input     = document.getElementById('history-customer');
    var result    = document.getElementById('history-result');
    if (!searchBtn || !input || !result) return;

    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function loadHistory() {
        var customer = input.value.trim();
        if (!customer) {
            result.innerHTML = '<div class="alert alert-warning">Please enter a customer name.</div>';
            return;
        }
        result.innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Loading…</p>';

        var url = '{{ route('pos.orders.history') }}?customer=' + encodeURIComponent(customer);
        fetch(url, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.sales || data.sales.length === 0) {
                    result.innerHTML = '<div class="alert alert-info">No previous sales found for "' + escapeHtml(customer) + '".</div>';
                    return;
                }
                var rows = data.sales.map(function (s) {
                    return '<tr>'
                        + '<td>#' + escapeHtml(s.id) + '</td>'
                        + '<td>' + escapeHtml(s.product_name) + '</td>'
                        + '<td class="text-center">' + escapeHtml(s.quantity) + '</td>'
                        + '<td class="text-right">' + escapeHtml(s.total_price) + '</td>'
                        + '<td>' + escapeHtml((s.payment_method || 'cash').toUpperCase()) + '</td>'
                        + '<td>' + escapeHtml(s.created_at || '') + '</td>'
                        + '<td class="text-center"><a href="/pos/orders/' + encodeURIComponent(s.id) + '/receipt" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary" title="Print receipt"><i class="fas fa-print"></i></a></td>'
                        + '</tr>';
                }).join('');
                result.innerHTML =
                    '<p class="mb-2"><strong>' + escapeHtml(data.count) + '</strong> order(s) for "' + escapeHtml(customer) + '".</p>'
                    + '<div class="table-responsive"><table class="pos-modal-table">'
                    +   '<thead><tr><th>ID</th><th>Product</th><th class="text-center">Qty</th><th class="text-right">Total</th><th>Method</th><th>Date</th><th class="text-center">Receipt</th></tr></thead>'
                    +   '<tbody>' + rows + '</tbody>'
                    + '</table></div>';
            })
            .catch(function () {
                result.innerHTML = '<div class="alert alert-danger">Failed to load history.</div>';
            });
    }

    searchBtn.addEventListener('click', loadHistory);
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); loadHistory(); }
    });

    // When the modal opens, default the input to the current Customer Name field
    $('#historyModal').on('show.bs.modal', function () {
        var c = document.getElementById('customer_name');
        if (c && c.value.trim()) {
            input.value = c.value.trim();
            loadHistory();
        }
    });
})();
</script>
@endpush
@endonce
