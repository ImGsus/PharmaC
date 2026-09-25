{{-- Sessions modal — shows the current cashier's start/end times and the
     total earnings for each shift. Mirrors admin.pos.modals.print-orders. --}}
<div class="modal fade" id="sessionsModal" tabindex="-1" role="dialog" aria-labelledby="sessionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sessionsModalLabel">
                    <i class="fas fa-clock"></i> My POS Sessions
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    One row per shift. <strong>Time In</strong> is when you clicked
                    <em>Start Session</em>; <strong>Time Out</strong> is when you
                    clicked <em>End Session</em>. Earnings are summed from sales
                    recorded during the shift.
                </p>
                <div id="sessions-result">
                    <p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Loading…</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="sessionOrdersModal" tabindex="-1" role="dialog" aria-labelledby="sessionOrdersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sessionOrdersModalLabel"><i class="fas fa-receipt mr-1"></i> Session Orders</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="session-orders-result"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@once
@push('page-css')
<style>
.pos-sessions-table { width: 100%; border-collapse: collapse; }
.pos-sessions-table th, .pos-sessions-table td {
    padding: .55rem .75rem;
    border-bottom: 1px solid var(--pos-border, #e2e6ec);
    font-size: .9rem;
    text-align: left;
    vertical-align: middle;
}
.pos-sessions-table thead th {
    background: #f2f4f7;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .03em;
}
.pos-sessions-table tr.is-open td { background: #fff8e6; }
.pos-sessions-table tr.is-just-closed td { background: #eef9f0; }
.pos-sessions-status-pill {
    display: inline-block;
    padding: .15rem .55rem;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
}
.pos-sessions-status-pill.open   { background: #fde6b5; color: #8a5b00; }
.pos-sessions-status-pill.closed { background: #d6f0db; color: #1f6c39; }
.pos-session-orders-table { width: 100%; border-collapse: collapse; }
.pos-session-orders-table th, .pos-session-orders-table td {
    padding: .55rem .7rem;
    border-bottom: 1px solid var(--pos-border, #e2e6ec);
    font-size: .88rem;
    vertical-align: middle;
}
.pos-session-orders-table thead th {
    background: #f2f4f7;
    font-size: .75rem;
    text-transform: uppercase;
}
.pos-session-order-heading { margin: 1rem 0 .45rem; }
.pos-session-order-heading:first-child { margin-top: 0; }
.pos-session-overall-summary {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 18px;
}
.pos-session-overall-summary-item {
    padding: 10px 12px;
    border: 1px solid var(--pos-border, #e2e6ec);
    border-radius: 6px;
    background: #f8fafc;
}
.pos-session-overall-summary-item strong {
    display: block;
    font-size: .75rem;
    color: #6b7785;
    text-transform: uppercase;
}
.pos-session-overall-summary-item span {
    display: block;
    margin-top: 3px;
    font-weight: 700;
}
@media (max-width: 576px) {
    .pos-session-overall-summary { grid-template-columns: 1fr; }
}
</style>
@endpush

@push('page-js')
<script>
(function(){
    var modal  = document.getElementById('sessionsModal');
    var result = document.getElementById('sessions-result');
    var ordersModal = document.getElementById('sessionOrdersModal');
    var ordersResult = document.getElementById('session-orders-result');
    var sessionOrders = {};
    var ordersOpenedFromSessions = false;
    if (!modal || !result) return;

    var currency = @json($currency);

    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }
    function money(n, c) {
        return (c || currency) + ' ' + Number(n || 0).toFixed(2);
    }
    function formatTime(s) {
        if (!s) return '—';
        // Backend ships "Y-m-d H:i:s"; show date + short time.
        var m = String(s).match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/);
        if (!m) return escapeHtml(s);
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return months[parseInt(m[2],10)-1] + ' ' + parseInt(m[3],10) + ', ' +
               m[1] + ' ' + m[4] + ':' + m[5];
    }
    function durationLabel(min) {
        var h = Math.floor(min / 60);
        var m = min % 60;
        if (h === 0) return m + ' min';
        if (m === 0) return h + ' hr';
        return h + ' hr ' + m + ' min';
    }

    function load() {
        result.innerHTML = '<p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Loading…</p>';
        fetch('{{ route('pos.session.history') }}', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(function (r) { return r.json().catch(function () { return { ok: false, message: 'Bad JSON.' }; }); })
        .then(function (data) {
            if (!data.ok) {
                result.innerHTML = '<div class="alert alert-danger">' +
                    escapeHtml(data.message || 'Failed to load sessions.') + '</div>';
                return;
            }
            var list = data.sessions || [];
            if (list.length === 0) {
                result.innerHTML = '<div class="alert alert-info mb-0">No sessions yet. Click <strong>Start Session</strong> to begin one.</div>';
                return;
            }
            var justClosedId = window.__posSessionJustClosed || null;
            sessionOrders = {};
            var rows = list.map(function (s) {
                sessionOrders[String(s.id)] = s.orders || [];
                var isOpen = s.status === 'open';
                var cls = isOpen ? 'is-open' : (String(s.id) === String(justClosedId) ? 'is-just-closed' : '');
                var pill = isOpen
                    ? '<span class="pos-sessions-status-pill open">Open</span>'
                    : '<span class="pos-sessions-status-pill closed">Closed</span>';
                var orderCount = (s.orders || []).length;
                var orderButton = orderCount > 0
                    ? '<button type="button" class="btn btn-sm btn-outline-secondary pos-sessions-orders-btn" data-session-id="' + escapeHtml(s.id) + '">&#x2026;</button> '
                        + '<strong>' + escapeHtml(orderCount) + '</strong>'
                    : '0';

                return '<tr class="' + cls + '">'
                    + '<td><strong>#' + escapeHtml(s.id) + '</strong></td>'
                    + '<td>' + formatTime(s.started_at) + '</td>'
                    + '<td>' + (isOpen ? '— still running' : formatTime(s.ended_at)) + '</td>'
                    + '<td>' + escapeHtml(durationLabel(s.duration_min || 0)) + '</td>'
                    + '<td class="text-center">' + orderButton + '</td>'
                    + '<td class="text-right">' + escapeHtml(money(s.total_earnings, s.currency)) + '</td>'
                    + '<td class="text-center">' + pill + '</td>'
                    + '</tr>'
                    ;
            }).join('');
            result.innerHTML =
                '<p class="mb-2">Showing the <strong>' + escapeHtml(list.length) + '</strong> most recent session(s).</p>'
                + '<div class="table-responsive"><table class="pos-sessions-table">'
                +   '<thead><tr><th>ID</th><th>Time In</th><th>Time Out</th><th>Duration</th><th class="text-center">Orders</th><th class="text-right">Earnings</th><th class="text-center">Status</th></tr></thead>'
                +   '<tbody>' + rows + '</tbody>'
                + '</table></div>';
            attachSessionOrderToggles();
            // Clear the "just closed" marker so re-opening the modal later
            // doesn't keep highlighting the old row.
            window.__posSessionJustClosed = null;
        })
        .catch(function () {
            result.innerHTML = '<div class="alert alert-danger">Failed to load sessions.</div>';
        });
    }

    function attachSessionOrderToggles() {
        result.querySelectorAll('.pos-sessions-orders-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var sessionId = btn.getAttribute('data-session-id');
                if (!sessionId) return;
                var orders = sessionOrders[String(sessionId)] || [];
                var orderDetails = orders.map(function (order) {
                    var subtotal = Number(order.subtotal || 0);
                    var discount = Number(order.discount || 0);
                    var discountPercent = subtotal > 0 ? (discount / subtotal) * 100 : 0;
                    var discountLabel = 'Discount: ' + discountPercent.toFixed(2).replace(/\.00$/, '') + '%';
                    var items = (order.items || []).map(function (item) {
                        return '<tr><td>' + escapeHtml(item.name) + '</td>'
                            + '<td>' + escapeHtml(item.category) + '</td>'
                            + '<td class="text-center">' + escapeHtml(item.qty) + '</td>'
                            + '<td class="text-right">' + escapeHtml(money(item.unit_price)) + '</td>'
                            + '<td class="text-right">' + escapeHtml(money(item.line)) + '</td></tr>';
                    }).join('');
                    return '<h6 class="pos-session-order-heading">Order #' + escapeHtml(order.id) + ' - '
                        + escapeHtml(order.customer_name || 'Walk-in customer') + ' ('
                        + escapeHtml((order.payment_method || 'cash').toUpperCase()) + ')</h6>'
                        + '<div class="table-responsive"><table class="pos-session-orders-table">'
                        + '<thead><tr><th>Product</th><th>Category</th><th class="text-center">Qty</th><th class="text-right">Unit</th><th class="text-right">Line Total</th></tr></thead>'
                        + '<tbody>' + items + '</tbody>'
                        + '<tfoot>'
                        + '<tr><td colspan="4" class="text-right"><strong>Subtotal</strong></td><td class="text-right"><strong>' + escapeHtml(money(order.subtotal)) + '</strong></td></tr>'
                        + '<tr><td colspan="4" class="text-right"><strong>' + escapeHtml(discountLabel) + ' =</strong></td><td class="text-right"><strong>- ' + escapeHtml(money(discount)) + '</strong></td></tr>'
                        + '<tr><td colspan="4" class="text-right"><strong>Total</strong></td><td class="text-right"><strong>' + escapeHtml(money(order.total)) + '</strong></td></tr>'
                        + '</tfoot>'
                        + '</table></div>';
                }).join('');
                var overallSubtotal = orders.reduce(function (sum, order) {
                    return sum + Number(order.subtotal || 0);
                }, 0);
                var overallDiscount = orders.reduce(function (sum, order) {
                    return sum + Number(order.discount || 0);
                }, 0);
                var overallEarnings = orders.reduce(function (sum, order) {
                    return sum + Number(order.total || 0);
                }, 0);
                var overallSummary = '<div class="pos-session-overall-summary">'
                    + '<div class="pos-session-overall-summary-item"><strong>Overall Subtotal</strong><span>' + escapeHtml(money(overallSubtotal)) + '</span></div>'
                    + '<div class="pos-session-overall-summary-item"><strong>Overall Discount Used</strong><span>- ' + escapeHtml(money(overallDiscount)) + '</span></div>'
                    + '<div class="pos-session-overall-summary-item"><strong>Overall Earnings</strong><span>' + escapeHtml(money(overallEarnings)) + '</span></div>'
                    + '</div>';
                ordersResult.innerHTML = overallSummary + (orderDetails || '<div class="text-muted">No order details available.</div>');
                if (window.jQuery) {
                    ordersOpenedFromSessions = true;
                    window.jQuery('#sessionsModal').one('hidden.bs.modal.sessionOrders', function () {
                        window.jQuery(ordersModal).modal('show');
                    }).modal('hide');
                }
            });
        });
    }

    $(ordersModal).on('hidden.bs.modal', function () {
        if (ordersOpenedFromSessions) {
            ordersOpenedFromSessions = false;
            $(modal).modal('show');
        }
    });

    $(modal).on('show.bs.modal', load);
})();
</script>
@endpush
@endonce