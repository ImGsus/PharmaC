{{-- Print orders modal — lets the cashier pick which saved sale to print.
     Opened from the Print button on the POS page. Each row shows the order
     id, date/time, customer, total, and a Print button that opens the
     saved receipt in a new tab. Items are loaded via AJAX from the
     existing pos.orders.history endpoint (recent sales, optional filter). --}}
<div class="modal fade" id="printOrdersModal" tabindex="-1" role="dialog" aria-labelledby="printOrdersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printOrdersModalLabel">
                    <i class="fas fa-print"></i> Print — Pick an Order
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-row align-items-end mb-3">
                    <div class="form-group col-md-6 mb-2">
                        <label class="mb-1">Filter by customer (optional)</label>
                        <input type="text" id="print-orders-customer" class="form-control" placeholder="Type a customer name">
                    </div>
                    <div class="form-group col-md-6 mb-2">
                        <button type="button" class="btn btn-primary" id="print-orders-search-btn">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="print-orders-refresh-btn">
                            <i class="fas fa-sync"></i> Show Recent
                        </button>
                    </div>
                </div>
                <div id="print-orders-result">
                    <p class="text-muted">Click <strong>Show Recent</strong> to load the most recent orders.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@once
@push('page-css')
<style>
.pos-print-orders-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
.pos-print-orders-table th, .pos-print-orders-table td {
    padding: .55rem .75rem;
    border-bottom: 1px solid var(--pos-border, #e2e6ec);
    font-size: .9rem;
    text-align: left;
    white-space: normal;
    word-break: break-word;
}
.pos-print-orders-table th:nth-child(1), .pos-print-orders-table td:nth-child(1) { width: 8%; }
.pos-print-orders-table th:nth-child(2), .pos-print-orders-table td:nth-child(2) { width: 18%; }
.pos-print-orders-table th:nth-child(3), .pos-print-orders-table td:nth-child(3) { width: 38%; }
.pos-print-orders-table th:nth-child(4), .pos-print-orders-table td:nth-child(4) { width: 12%; white-space: nowrap; }
.pos-print-orders-table th:nth-child(5), .pos-print-orders-table td:nth-child(5) { width: 12%; white-space: nowrap; }
.pos-print-orders-table th:nth-child(6), .pos-print-orders-table td:nth-child(6) { width: 12%; white-space: nowrap; }
.pos-print-orders-table thead th {
    background: #f2f4f7;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .03em;
}
.print-orders-items-table { width: 100%; border-collapse: collapse; margin-top: .75rem; }
.print-orders-items-table th, .print-orders-items-table td {
    padding: .45rem .6rem;
    border: 1px solid var(--pos-border, #e2e6ec);
    font-size: .85rem;
}
.print-orders-details-row td { background: #fbfcfd; }
.print-orders-details-toggle { min-width: 38px; }
.print-orders-table-responsive { overflow-x: visible !important; }
</style>
@endpush

@push('page-js')
<script>
(function(){
    var modal      = document.getElementById('printOrdersModal');
    var result     = document.getElementById('print-orders-result');
    var input      = document.getElementById('print-orders-customer');
    var searchBtn  = document.getElementById('print-orders-search-btn');
    var refreshBtn = document.getElementById('print-orders-refresh-btn');
    if (!modal || !result) return;

    var currency = @json($currency);

    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }
    function money(n) {
        var x = Number(n || 0);
        return currency + x.toFixed(2);
    }

    function load(customer) {
        result.innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Loading…</p>';
        var url = '{{ route('pos.orders.list') }}';
        if (customer) {
            url += '?customer=' + encodeURIComponent(customer);
        }
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var orders = (data && data.orders) || [];
            if (orders.length === 0) {
                result.innerHTML = '<div class="alert alert-info mb-0">No saved orders found.</div>';
                return;
            }

            var rows = orders.map(function (o) {
                var itemsSummary = (o.items || []).map(function (it) {
                    return escapeHtml(it.name) + ' × ' + escapeHtml(it.qty);
                }).join(', ');
                var detailsRows = (o.items || []).map(function (it) {
                    return '<tr>'
                        + '<td>' + escapeHtml(it.name) + '</td>'
                        + '<td>' + escapeHtml(it.category || 'Uncategorized') + '</td>'
                        + '<td>' + escapeHtml(it.qty) + '</td>'
                        + '<td class="text-right">' + escapeHtml(money(it.unit_price)) + '</td>'
                        + '<td class="text-right">' + escapeHtml(money(it.line)) + '</td>'
                        + '</tr>';
                }).join('');
                var detailsTable = '<table class="print-orders-items-table">'
                    + '<thead><tr><th>Product</th><th>Category</th><th>Qty</th><th class="text-right">Unit</th><th class="text-right">Line Total</th></tr></thead>'
                    + '<tbody>' + detailsRows + '</tbody>'
                    + '<tfoot><tr><td colspan="4" class="text-right"><strong>Total</strong></td>'
                    + '<td class="text-right"><strong>' + escapeHtml(money(o.total)) + '</strong></td></tr></tfoot>'
                    + '</table>';
                return '<tr>'
                    + '<td><strong>#' + escapeHtml(o.id) + '</strong></td>'
                    + '<td>' + escapeHtml(o.created_at || '') + '</td>'
                    + '<td>' + escapeHtml(o.customer_name || 'Walk-in customer') + '<br>'
                    +   '<small class="text-muted">' + (itemsSummary || '—') + '</small>'
                    + '</td>'
                    + '<td>' + escapeHtml((o.payment_method || 'cash').toUpperCase()) + '</td>'
                    + '<td class="text-right">' + escapeHtml(money(o.total)) + '</td>'
                    + '<td class="text-center">'
                    +   '<button type="button" class="btn btn-sm btn-outline-secondary print-orders-toggle-btn" data-order-id="' + escapeHtml(o.id) + '" title="View items">&#x2026;</button> '
                    +   '<button type="button" class="btn btn-sm btn-primary print-orders-print-btn" data-receipt-url="' + escapeHtml('/pos/orders/' + encodeURIComponent(o.id) + '/receipt') + '" title="Print receipt">'
                    +     '<i class="fas fa-print"></i> Print'
                    +   '</button>'
                    + '</td>'
                    + '</tr>'
                    + '<tr class="print-orders-details-row d-none" id="print-orders-details-' + escapeHtml(o.id) + '">'
                    + '<td colspan="6">' + detailsTable + '</td>'
                    + '</tr>';
            }).join('');

            var heading = customer
                ? '<p class="mb-2"><strong>' + escapeHtml(data.count) + '</strong> order(s) for "' + escapeHtml(customer) + '".</p>'
                : '<p class="mb-2">Showing the <strong>' + escapeHtml(orders.length) + '</strong> most recent order(s).</p>';

            result.innerHTML =
                heading
                + '<div class="table-responsive print-orders-table-responsive"><table class="pos-print-orders-table">'
                +   '<thead><tr><th>Order #</th><th>Date / Time</th><th>Customer</th><th>Method</th><th class="text-right">Total</th><th class="text-center">Action</th></tr></thead>'
                +   '<tbody>' + rows + '</tbody>'
                + '</table></div>';
            attachDetailToggles();
            attachPrintButtons();
        })
        .catch(function () {
            result.innerHTML = '<div class="alert alert-danger">Failed to load orders.</div>';
        });
    }

    function attachDetailToggles() {
        result.querySelectorAll('.print-orders-toggle-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var orderId = btn.getAttribute('data-order-id');
                if (!orderId) return;
                var details = document.getElementById('print-orders-details-' + orderId);
                if (!details) return;
                details.classList.toggle('d-none');
            });
        });
    }

    function attachPrintButtons() {
        result.querySelectorAll('.print-orders-print-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var url = btn.getAttribute('data-receipt-url');
                if (!url) return;
                var width = 900;
                var height = 700;
                var left = (window.screen.width / 2) - (width / 2);
                var top = (window.screen.height / 2) - (height / 2);
                window.open(url, '_blank', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=' + width + ',height=' + height + ',top=' + top + ',left=' + left);
            });
        });
    }

            if (searchBtn) {
        searchBtn.addEventListener('click', function () {
            load(input.value.trim());
        });
    }
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function () {
            if (input) input.value = '';
            load('');
        });
    }
    if (input) {
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); load(input.value.trim()); }
        });
    }

    // Auto-load the most recent orders every time the modal opens — that
    // matches the user's "click Print, see the orders, pick one" expectation.
    $(modal).on('show.bs.modal', function () {
        if (input) input.value = '';
        load('');
    });
})();
</script>
@endpush
@endonce
