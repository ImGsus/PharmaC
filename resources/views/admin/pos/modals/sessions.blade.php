{{-- Sessions modal — shows the current cashier's start/end times and the
     total earnings for each shift. Mirrors admin.pos.modals.print-orders. --}}
<div class="modal fade" id="sessionsModal" tabindex="-1" role="dialog" aria-labelledby="sessionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg pos-sessions-modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-sm btn-link p-0 text-dark mr-2 pos-session-back-btn d-none" id="session-header-back-btn" title="Back to Sessions" aria-label="Back to Sessions">
                        <i class="fas fa-arrow-left fa-lg text-secondary"></i>
                    </button>
                    <h5 class="modal-title mb-0" id="sessionsModalLabel">
                        <i class="fas fa-clock mr-1 text-primary"></i> <span id="sessions-modal-heading">My POS Sessions</span>
                    </h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- VIEW 1: Sessions List -->
                <div id="pos-sessions-list-view" class="pos-sessions-view">
                    <p class="text-muted small mb-3">
                        One row per shift. <strong>Time In</strong> is when you clicked
                        <em>Start Session</em>; <strong>Time Out</strong> is when you
                        clicked <em>End Session</em>. Click the <strong>&hellip;</strong> button under Actions to view products sold and shift earnings.
                    </p>
                    <div id="sessions-result">
                        <p class="text-muted"><i class="fas fa-spinner fa-spin"></i> Loading…</p>
                    </div>
                </div>

                <!-- VIEW 2: Session Details (Products Sold, Orders as Quantity, Earnings, Status) -->
                <div id="pos-sessions-detail-view" class="pos-sessions-view d-none">
                    <div id="session-detail-content"></div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary pos-session-back-btn d-none" id="session-footer-back-btn">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Sessions
                </button>
                <button type="button" class="btn btn-secondary ml-auto" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Standalone Session Orders Modal kept for backward compatibility --}}
<div class="modal fade" id="sessionOrdersModal" tabindex="-1" role="dialog" aria-labelledby="sessionOrdersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg pos-sessions-modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sessionOrdersModalLabel">
                    <i class="fas fa-info-circle mr-1 text-primary"></i> <span>Session Details</span>
                </h5>
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
.pos-sessions-modal-dialog {
    max-width: 880px !important;
    width: 95vw !important;
}
.pos-sessions-table-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.pos-sessions-table {
    width: 100%;
    border-collapse: collapse;
}
.pos-sessions-table th,
.pos-sessions-table td {
    max-width: none !important;
    overflow: visible !important;
    text-overflow: clip !important;
    white-space: nowrap !important;
    padding: .65rem .85rem;
    border-bottom: 1px solid var(--pos-border, #e2e6ec);
    font-size: .88rem;
    text-align: left;
    vertical-align: middle;
}
.pos-sessions-table th:nth-child(1),
.pos-sessions-table td:nth-child(1) {
    width: 70px;
}
.pos-sessions-table th:nth-child(2),
.pos-sessions-table td:nth-child(2) {
    min-width: 170px;
}
.pos-sessions-table th:nth-child(3),
.pos-sessions-table td:nth-child(3) {
    min-width: 170px;
}
.pos-sessions-table th:nth-child(4),
.pos-sessions-table td:nth-child(4) {
    min-width: 95px;
}
.pos-sessions-table th:nth-child(5),
.pos-sessions-table td:nth-child(5) {
    width: 80px;
    text-align: center;
}
.pos-sessions-table thead th {
    background: #f2f4f7;
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
    color: #4b5563;
}
.pos-sessions-table tr.is-open td { background: #fff8e6; }
.pos-sessions-table tr.is-just-closed td { background: #eef9f0; }

.pos-sessions-orders-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 30px;
    padding: 0;
    font-size: 15px;
    font-weight: bold;
    line-height: 1;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #475569;
    transition: all .15s ease;
    cursor: pointer;
}
.pos-sessions-orders-btn:hover,
.pos-sessions-orders-btn:focus {
    background: #0f766e;
    border-color: #0f766e;
    color: #fff;
    box-shadow: 0 2px 6px rgba(15, 118, 110, 0.25);
}

.pos-sessions-status-pill {
    display: inline-block;
    padding: .2rem .6rem;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
}
.pos-sessions-status-pill.open   { background: #fde6b5; color: #8a5b00; }
.pos-sessions-status-pill.closed { background: #d6f0db; color: #1f6c39; }

.pos-session-overall-summary {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}
.pos-session-overall-summary-item {
    padding: 11px 14px;
    border: 1px solid var(--pos-border, #e2e6ec);
    border-radius: 8px;
    background: #f8fafc;
}
.pos-session-overall-summary-item strong {
    display: block;
    font-size: .72rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .03em;
    margin-bottom: 4px;
}
.pos-session-overall-summary-item .summary-value {
    display: block;
    font-weight: 700;
    font-size: 1rem;
    color: #1e293b;
}

.pos-session-products-sold-table,
.pos-session-orders-table {
    width: 100%;
    border-collapse: collapse;
}
.pos-session-products-sold-table th,
.pos-session-products-sold-table td,
.pos-session-orders-table th,
.pos-session-orders-table td {
    max-width: none !important;
    overflow: visible !important;
    text-overflow: clip !important;
    white-space: nowrap !important;
    padding: .55rem .75rem;
    border-bottom: 1px solid var(--pos-border, #e2e6ec);
    font-size: .86rem;
    vertical-align: middle;
}
.pos-session-products-sold-table thead th,
.pos-session-orders-table thead th {
    background: #f1f5f9;
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .03em;
    font-weight: 700;
    color: #475569;
}

body.dark-mode #sessionsModal .modal-content,
body.dark-mode #sessionOrdersModal .modal-content {
    background: #1c2025;
    color: #e2e8f0;
    border: 1px solid #334155;
}
body.dark-mode #sessionsModal .modal-header,
body.dark-mode #sessionOrdersModal .modal-header {
    border-bottom-color: #334155;
    background: #1c2025;
}
body.dark-mode #sessionsModal .modal-footer,
body.dark-mode #sessionOrdersModal .modal-footer {
    border-top-color: #334155;
    background: #1c2025;
}
body.dark-mode .pos-sessions-table thead th,
body.dark-mode .pos-session-products-sold-table thead th,
body.dark-mode .pos-session-orders-table thead th {
    background: #1e293b;
    color: #94a3b8;
    border-bottom-color: #334155;
}
body.dark-mode .pos-sessions-table td,
body.dark-mode .pos-session-products-sold-table td,
body.dark-mode .pos-session-orders-table td {
    border-bottom-color: #334155;
    color: #e2e8f0;
}
body.dark-mode .pos-sessions-table tr.is-open td { background: rgba(245, 158, 11, 0.12); }
body.dark-mode .pos-sessions-table tr.is-just-closed td { background: rgba(16, 185, 129, 0.12); }
body.dark-mode .pos-sessions-orders-btn {
    background: #242c38;
    border-color: #475569;
    color: #cbd5e1;
}
body.dark-mode .pos-sessions-orders-btn:hover {
    background: #0f766e;
    border-color: #0f766e;
    color: #fff;
}
body.dark-mode .pos-session-overall-summary-item {
    background: #1e293b;
    border-color: #334155;
}
body.dark-mode .pos-session-overall-summary-item strong {
    color: #94a3b8;
}
body.dark-mode .pos-session-overall-summary-item .summary-value {
    color: #f8fafc;
}

/* Back and Close buttons in Sessions Modal */
body.dark-mode #session-header-back-btn,
body.dark-mode .pos-session-back-btn {
    color: #e2e8f0 !important;
}
body.dark-mode #session-header-back-btn i,
body.dark-mode .pos-session-back-btn i {
    color: #cbd5e1 !important;
}
body.dark-mode #session-header-back-btn:hover i,
body.dark-mode .pos-session-back-btn:hover i {
    color: #ffffff !important;
}

body.dark-mode #session-footer-back-btn,
body.dark-mode .pos-session-back-btn.btn-outline-secondary {
    background-color: #242c38 !important;
    border-color: #475569 !important;
    color: #e2e8f0 !important;
}
body.dark-mode #session-footer-back-btn:hover,
body.dark-mode #session-footer-back-btn:focus,
body.dark-mode .pos-session-back-btn.btn-outline-secondary:hover,
body.dark-mode .pos-session-back-btn.btn-outline-secondary:focus {
    background-color: #334155 !important;
    border-color: #64748b !important;
    color: #ffffff !important;
}

body.dark-mode #sessionsModal .modal-footer .btn-secondary,
body.dark-mode #sessionOrdersModal .modal-footer .btn-secondary {
    background-color: #242c38 !important;
    border-color: #475569 !important;
    color: #e2e8f0 !important;
}
body.dark-mode #sessionsModal .modal-footer .btn-secondary:hover,
body.dark-mode #sessionOrdersModal .modal-footer .btn-secondary:hover,
body.dark-mode #sessionsModal .modal-footer .btn-secondary:focus,
body.dark-mode #sessionOrdersModal .modal-footer .btn-secondary:focus {
    background-color: #334155 !important;
    border-color: #64748b !important;
    color: #ffffff !important;
}

body.dark-mode #sessionsModal .close,
body.dark-mode #sessionOrdersModal .close {
    color: #94a3b8 !important;
    text-shadow: none !important;
    opacity: 0.8 !important;
}
body.dark-mode #sessionsModal .close:hover,
body.dark-mode #sessionOrdersModal .close:hover {
    color: #ffffff !important;
    opacity: 1 !important;
}

/* Detail view content styling in dark mode */
body.dark-mode #session-detail-content h6,
body.dark-mode #session-detail-content .text-dark {
    color: #f8fafc !important;
}
body.dark-mode #session-detail-content .badge-light {
    background-color: #1e293b !important;
    color: #cbd5e1 !important;
    border-color: #334155 !important;
}
body.dark-mode #session-detail-content .alert-light {
    background-color: #1e293b !important;
    color: #cbd5e1 !important;
    border-color: #334155 !important;
}
body.dark-mode #session-detail-content .card {
    background-color: #1c2025 !important;
    border-color: #334155 !important;
}
body.dark-mode #session-detail-content .card-header.bg-light {
    background-color: #1e293b !important;
    border-bottom-color: #334155 !important;
    color: #f8fafc !important;
}
body.dark-mode #session-detail-content .card-header.bg-light .text-dark {
    color: #f8fafc !important;
}
body.dark-mode #session-detail-content .pos-sessions-table-wrap.border {
    border-color: #334155 !important;
}
body.dark-mode .pos-session-products-sold-table tfoot td,
body.dark-mode .pos-session-orders-table tfoot td {
    background-color: #1e293b !important;
    border-color: #334155 !important;
    color: #f8fafc !important;
}

@media (max-width: 768px) {
    .pos-session-overall-summary { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
    .pos-session-overall-summary { grid-template-columns: 1fr; }
}
</style>
@endpush

@push('page-js')
<script>
(function(){
    var modal = document.getElementById('sessionsModal');
    var result = document.getElementById('sessions-result');
    var listView = document.getElementById('pos-sessions-list-view');
    var detailView = document.getElementById('pos-sessions-detail-view');
    var detailContent = document.getElementById('session-detail-content');
    var headingTitle = document.getElementById('sessions-modal-heading');
    var headerBackBtn = document.getElementById('session-header-back-btn');
    var footerBackBtn = document.getElementById('session-footer-back-btn');
    var sessionsMap = {};

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
        var m = String(s).match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?/);
        if (!m) return escapeHtml(s);
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var hour = parseInt(m[4], 10);
        var min = m[5];
        var ampm = hour >= 12 ? 'PM' : 'AM';
        var h12 = hour % 12;
        if (h12 === 0) h12 = 12;
        return months[parseInt(m[2],10)-1] + ' ' + parseInt(m[3],10) + ', ' + m[1] + ' ' + h12 + ':' + min + ' ' + ampm;
    }
    function durationLabel(min) {
        var h = Math.floor(min / 60);
        var m = min % 60;
        if (h === 0) return m + ' min';
        if (m === 0) return h + ' hr';
        return h + ' hr ' + m + ' min';
    }

    function showSessionsList() {
        if (headingTitle) headingTitle.textContent = 'My POS Sessions';
        if (detailView) detailView.classList.add('d-none');
        if (listView) listView.classList.remove('d-none');
        if (headerBackBtn) headerBackBtn.classList.add('d-none');
        if (footerBackBtn) footerBackBtn.classList.add('d-none');
    }

    function showSessionDetails(sessionId) {
        var s = sessionsMap[String(sessionId)];
        if (!s) return;

        var orders = s.orders || [];
        var isOpen = s.status === 'open';
        var pill = isOpen
            ? '<span class="pos-sessions-status-pill open"><i class="fas fa-play mr-1"></i> Open</span>'
            : '<span class="pos-sessions-status-pill closed"><i class="fas fa-check mr-1"></i> Closed</span>';

        // 1. Aggregate Products Sold in this session
        var productsSoldMap = {};
        var totalQuantitySold = 0;
        orders.forEach(function (order) {
            (order.items || []).forEach(function (item) {
                var key = (item.name || 'N/A') + '|||' + (item.category || 'General');
                if (!productsSoldMap[key]) {
                    productsSoldMap[key] = {
                        name: item.name || 'N/A',
                        category: item.category || 'General',
                        qty: 0,
                        unit_price: item.unit_price || 0,
                        line: 0
                    };
                }
                productsSoldMap[key].qty += Number(item.qty || 0);
                productsSoldMap[key].line += Number(item.line || 0);
                totalQuantitySold += Number(item.qty || 0);
            });
        });

        var productsSoldList = [];
        for (var k in productsSoldMap) {
            if (Object.prototype.hasOwnProperty.call(productsSoldMap, k)) {
                productsSoldList.push(productsSoldMap[k]);
            }
        }

        // 2. Compute earnings breakdown
        var overallSubtotal = orders.reduce(function (sum, order) {
            return sum + Number(order.subtotal || 0);
        }, 0);
        var overallDiscount = orders.reduce(function (sum, order) {
            return sum + Number(order.discount || 0);
        }, 0);
        var overallEarnings = orders.reduce(function (sum, order) {
            return sum + Number(order.total || 0);
        }, 0);

        // 3. Overall Top KPI Summary (Status, Orders as Quantity, Earnings, Shift Duration)
        var overallSummary = '<div class="pos-session-overall-summary">'
            + '<div class="pos-session-overall-summary-item">'
            +   '<strong>Status</strong>'
            +   '<div class="mt-1">' + pill + '</div>'
            + '</div>'
            + '<div class="pos-session-overall-summary-item">'
            +   '<strong>Orders & Quantity</strong>'
            +   '<span class="summary-value text-primary">' + escapeHtml(orders.length) + ' <small class="text-muted font-weight-normal">order(s)</small></span>'
            +   '<div class="small text-muted mt-1 font-weight-600">' + escapeHtml(totalQuantitySold) + ' total unit(s) sold</div>'
            + '</div>'
            + '<div class="pos-session-overall-summary-item">'
            +   '<strong>Earnings</strong>'
            +   '<span class="summary-value text-success">' + escapeHtml(money(overallEarnings, s.currency)) + '</span>'
            +   (overallDiscount > 0 ? '<div class="small text-muted mt-1">Discount: - ' + escapeHtml(money(overallDiscount, s.currency)) + '</div>' : '')
            + '</div>'
            + '<div class="pos-session-overall-summary-item">'
            +   '<strong>Shift Duration</strong>'
            +   '<span class="summary-value">' + escapeHtml(durationLabel(s.duration_min || 0)) + '</span>'
            +   '<div class="small text-muted mt-1">' + formatTime(s.started_at) + '</div>'
            + '</div>'
            + '</div>';

        // 4. Products Sold Table
        var productsSoldHtml = '<div class="mb-4">'
            + '<div class="d-flex align-items-center justify-content-between mb-2">'
            +   '<h6 class="font-weight-bold mb-0 text-dark"><i class="fas fa-boxes text-info mr-1"></i> Products Sold That Day / Shift</h6>'
            +   '<span class="badge badge-light border text-muted">' + productsSoldList.length + ' Product(s)</span>'
            + '</div>';

        if (productsSoldList.length > 0) {
            var prodRows = productsSoldList.map(function (p) {
                return '<tr>'
                    + '<td><strong>' + escapeHtml(p.name) + '</strong></td>'
                    + '<td><span class="badge badge-light border">' + escapeHtml(p.category) + '</span></td>'
                    + '<td class="text-center font-weight-bold text-primary">' + escapeHtml(p.qty) + '</td>'
                    + '<td class="text-right">' + escapeHtml(money(p.unit_price, s.currency)) + '</td>'
                    + '<td class="text-right font-weight-bold">' + escapeHtml(money(p.line, s.currency)) + '</td>'
                    + '</tr>';
            }).join('');

            productsSoldHtml += '<div class="pos-sessions-table-wrap border rounded">'
                + '<table class="pos-session-products-sold-table">'
                +   '<thead><tr><th>Product Name</th><th>Category</th><th class="text-center">Quantity Sold</th><th class="text-right">Unit Price</th><th class="text-right">Total Sales</th></tr></thead>'
                +   '<tbody>' + prodRows + '</tbody>'
                +   '<tfoot>'
                +     '<tr>'
                +       '<td colspan="2" class="text-right font-weight-bold">Total Sold:</td>'
                +       '<td class="text-center font-weight-bold text-primary">' + escapeHtml(totalQuantitySold) + '</td>'
                +       '<td></td>'
                +       '<td class="text-right font-weight-bold text-success">' + escapeHtml(money(overallSubtotal, s.currency)) + '</td>'
                +     '</tr>'
                +   '</tfoot>'
                + '</table></div>';
        } else {
            productsSoldHtml += '<div class="alert alert-light border text-muted py-3 text-center mb-0">'
                + '<i class="fas fa-box-open fa-2x d-block mb-2 text-secondary"></i>'
                + 'No products were sold during this session.'
                + '</div>';
        }
        productsSoldHtml += '</div>';

        // 5. Order Transactions Detail List
        var orderTransactionsHtml = '';
        if (orders.length > 0) {
            var orderCards = orders.map(function (order) {
                var subtotal = Number(order.subtotal || 0);
                var discount = Number(order.discount || 0);
                var discountPercent = subtotal > 0 ? (discount / subtotal) * 100 : 0;
                var discountLabel = 'Discount: ' + discountPercent.toFixed(2).replace(/\.00$/, '') + '%';
                var items = (order.items || []).map(function (item) {
                    return '<tr><td>' + escapeHtml(item.name) + '</td>'
                        + '<td>' + escapeHtml(item.category) + '</td>'
                        + '<td class="text-center font-weight-600">' + escapeHtml(item.qty) + '</td>'
                        + '<td class="text-right">' + escapeHtml(money(item.unit_price, s.currency)) + '</td>'
                        + '<td class="text-right font-weight-600">' + escapeHtml(money(item.line, s.currency)) + '</td></tr>';
                }).join('');

                return '<div class="card border mb-3 shadow-none">'
                    + '<div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">'
                    +   '<span class="font-weight-bold text-dark"><i class="fas fa-receipt mr-1 text-secondary"></i> Order #' + escapeHtml(order.id) + '</span>'
                    +   '<div class="small text-muted">' + escapeHtml(order.customer_name || 'Walk-in customer') + ' &bull; <span class="badge badge-secondary text-uppercase">' + escapeHtml(order.payment_method || 'cash') + '</span></div>'
                    + '</div>'
                    + '<div class="card-body p-0">'
                    +   '<div class="pos-sessions-table-wrap">'
                    +     '<table class="pos-session-orders-table mb-0">'
                    +       '<thead><tr><th>Product</th><th>Category</th><th class="text-center">Qty</th><th class="text-right">Unit Price</th><th class="text-right">Line Total</th></tr></thead>'
                    +       '<tbody>' + items + '</tbody>'
                    +       '<tfoot>'
                    +         '<tr><td colspan="4" class="text-right text-muted">Subtotal</td><td class="text-right"><strong>' + escapeHtml(money(order.subtotal, s.currency)) + '</strong></td></tr>'
                    +         (discount > 0 ? '<tr><td colspan="4" class="text-right text-muted">' + escapeHtml(discountLabel) + '</td><td class="text-right text-danger font-weight-bold">- ' + escapeHtml(money(discount, s.currency)) + '</td></tr>' : '')
                    +         '<tr><td colspan="4" class="text-right font-weight-bold">Order Total</td><td class="text-right font-weight-bold text-success">' + escapeHtml(money(order.total, s.currency)) + '</td></tr>'
                    +       '</tfoot>'
                    +     '</table>'
                    +   '</div>'
                    + '</div>'
                    + '</div>';
            }).join('');

            orderTransactionsHtml = '<div class="mb-2">'
                + '<h6 class="font-weight-bold mb-2 text-dark"><i class="fas fa-file-invoice mr-1 text-secondary"></i> Order Transactions Breakdown (' + orders.length + ')</h6>'
                + orderCards
                + '</div>';
        }

        if (detailContent) {
            detailContent.innerHTML = overallSummary + productsSoldHtml + orderTransactionsHtml;
        }

        if (headingTitle) headingTitle.textContent = 'Session #' + s.id + ' Details';
        if (listView) listView.classList.add('d-none');
        if (detailView) detailView.classList.remove('d-none');
        if (headerBackBtn) headerBackBtn.classList.remove('d-none');
        if (footerBackBtn) footerBackBtn.classList.remove('d-none');
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
            sessionsMap = {};
            var rows = list.map(function (s) {
                sessionsMap[String(s.id)] = s;
                var isOpen = s.status === 'open';
                var cls = isOpen ? 'is-open' : (String(s.id) === String(justClosedId) ? 'is-just-closed' : '');
                var actionButton = '<button type="button" class="btn btn-sm pos-sessions-orders-btn" data-session-id="' + escapeHtml(s.id) + '" title="View Session Details">&#x2026;</button>';

                return '<tr class="' + cls + '">'
                    + '<td><strong>#' + escapeHtml(s.id) + '</strong></td>'
                    + '<td>' + formatTime(s.started_at) + '</td>'
                    + '<td>' + (isOpen ? '<span class="pos-sessions-status-pill open"><i class="fas fa-play mr-1"></i> Running</span>' : formatTime(s.ended_at)) + '</td>'
                    + '<td>' + escapeHtml(durationLabel(s.duration_min || 0)) + '</td>'
                    + '<td class="text-center">' + actionButton + '</td>'
                    + '</tr>';
            }).join('');

            result.innerHTML =
                '<p class="mb-2 text-muted small">Showing the <strong>' + escapeHtml(list.length) + '</strong> most recent session(s).</p>'
                + '<div class="pos-sessions-table-wrap">'
                +   '<table class="pos-sessions-table">'
                +     '<thead><tr><th>ID</th><th>Time In</th><th>Time Out</th><th>Duration</th><th class="text-center">Actions</th></tr></thead>'
                +     '<tbody>' + rows + '</tbody>'
                +   '</table>'
                + '</div>';

            window.__posSessionJustClosed = null;
        })
        .catch(function () {
            result.innerHTML = '<div class="alert alert-danger">Failed to load sessions.</div>';
        });
    }

    // Delegated click handler for the "..." buttons in the table
    result.addEventListener('click', function (e) {
        var btn = e.target.closest('.pos-sessions-orders-btn');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        var sessionId = btn.getAttribute('data-session-id');
        if (sessionId) {
            showSessionDetails(sessionId);
        }
    });

    // Delegated click handler for "Back to Sessions" buttons
    modal.addEventListener('click', function (e) {
        var backBtn = e.target.closest('.pos-session-back-btn');
        if (!backBtn) return;
        e.preventDefault();
        showSessionsList();
    });

    // Reset to Sessions list view every time modal opens
    if (window.jQuery) {
        window.jQuery(modal).on('show.bs.modal', function () {
            showSessionsList();
            load();
        });
    } else {
        modal.addEventListener('show.bs.modal', function () {
            showSessionsList();
            load();
        });
    }
})();
</script>
@endpush
@endonce