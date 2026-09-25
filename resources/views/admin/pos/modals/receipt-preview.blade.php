{{-- Receipt preview modal — shows a print-friendly view of the current cart.
     JS reads the cart rows + sidebar state and populates the receipt HTML. --}}
@php
    $currency = settings('app_currency', '$');
@endphp
<div class="modal fade" id="receiptPreviewModal" tabindex="-1" role="dialog" aria-labelledby="receiptPreviewLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiptPreviewLabel">
                    <i class="fas fa-receipt"></i> Receipt Preview
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- The receipt container — styled identically to admin.pos.receipt --}}
            <div class="modal-body" style="background:#eef1f4; padding:1.25rem;">
                <div id="rp-receipt" class="receipt" style="position:relative; width:100%; max-width:340px; margin:0 auto; background:#fff; padding:1.25rem; border:1px solid #e2e6ec; font-family:'Poppins',sans-serif; color:#2c3348;">
                    {{-- Logo placeholder --}}
                    <svg class="receipt-logo" width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg" style="display:block; margin:0 auto .5rem;">
                        <circle cx="30" cy="30" r="28" fill="none" stroke="#2f6fed" stroke-width="3"/>
                        <rect x="16" y="20" width="28" height="18" rx="2" fill="none" stroke="#2f6fed" stroke-width="2.5"/>
                        <line x1="20" y1="44" x2="40" y2="44" stroke="#2f6fed" stroke-width="2.5"/>
                    </svg>

                    <h2 style="text-align:center; margin:.25rem 0 1rem; font-size:1.15rem; font-weight:700;">
                        {{ settings('app_name', config('app.name', 'PharMac')) }}
                    </h2>

                    <h3 style="text-align:center; margin:0 0 .5rem; font-size:.95rem; border-top:1px solid #ddd; padding-top:.75rem; font-weight:700;">
                        Contact Us
                    </h3>
                    <div class="contact" style="text-align:center; font-size:.75rem; color:#333; line-height:1.5; margin-bottom:1rem;">
                        Address : {{ settings('company_address', 'street, city, state 0000') }}<br>
                        Email : {{ settings('company_email', 'info@example.com') }}<br>
                        Phone : {{ settings('company_phone', '555-555-5555') }}<br>
                        Cashier : <span id="rp-cashier">{{ auth()->user()->name ?? 'Cashier' }}</span><br>
                        Customer : <span id="rp-customer">Walk-in customer</span>
                    </div>

                    <table class="items" id="rp-items" style="width:100%; border-collapse:collapse; font-size:.72rem;">
                        <thead>
                            <tr>
                                <th style="text-align:left; border-bottom:1px solid #333; padding:.25rem 0; font-weight:700;">Item</th>
                                <th style="text-align:left; border-bottom:1px solid #333; padding:.25rem 0; font-weight:700;">Qty</th>
                                <th style="text-align:left; border-bottom:1px solid #333; padding:.25rem 0; font-weight:700;">Unit</th>
                                <th style="text-align:right; border-bottom:1px solid #333; padding:.25rem 0; font-weight:700;">Sub Total</th>
                            </tr>
                        </thead>
                        <tbody id="rp-items-body">
                            {{-- populated by JS --}}
                        </tbody>
                    </table>

                    <div class="thanks" style="text-align:center; font-size:.75rem; margin:1.25rem 0 1rem; line-height:1.5;">
                        ** Thank you for your visit! **
                    </div>

                    <div class="serial" style="text-align:center; font-size:.7rem; color:#444; border-top:1px dashed #999; padding-top:.5rem;">
                        Serial: <span id="rp-serial">DRAFT</span>
                    </div>

                    {{-- DRAFT watermark overlay --}}
                    <div id="rp-draft-watermark" style="display:none; position:absolute; top:40%; left:0; right:0; text-align:center; font-size:3rem; font-weight:800; color:rgba(232,72,63,.18); transform:rotate(-15deg); pointer-events:none; letter-spacing:.3em;">
                        DRAFT
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="rp-print-btn">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

@once
@push('page-css')
<style>
@media print {
    body, .modal, .modal-backdrop, .modal-dialog, .modal-content { visibility: hidden !important; }
    #rp-receipt, #rp-receipt * { visibility: visible !important; }
    #rp-receipt {
        position: absolute !important;
        left: 0; top: 0; right: 0;
        width: 340px !important;
        margin: 0 auto !important;
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
@endpush

@push('page-js')
<script>
(function(){
    var currency = @json($currency);
    var modal        = document.getElementById('receiptPreviewModal');
    var tbody        = document.getElementById('rp-items-body');
    var customerOut  = document.getElementById('rp-customer');
    var cashierOut   = document.getElementById('rp-cashier');
    var serialOut    = document.getElementById('rp-serial');
    var watermark    = document.getElementById('rp-draft-watermark');
    var printBtn     = document.getElementById('rp-print-btn');

    if (!modal || !tbody) return;

    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    function money(n) {
        var x = Number(n || 0);
        return currency + x.toFixed(2);
    }

    function currentSymbol() {
        var sel = document.getElementById('pos-v2-currency');
        if (sel) {
            var v = sel.value || '';
            if (v.indexOf('$') >= 0) return '$';
            if (v.indexOf('₱') >= 0) return '₱';
        }
        return currency;
    }
    function moneyLive(n) { return currentSymbol() + Number(n || 0).toFixed(2); }

    // Read cart rows from the v2 Sale Summary table.
    // Each row's HTML carries the product name, qty input, and computed line price.
    function readRows() {
        var list = [];
        document.querySelectorAll('#pos-v2-items-tbody .pos-v2-item-row').forEach(function (row) {
            var name = (row.cells[0] ? row.cells[0].textContent || '').replace(/⚠/g, '').trim();
            var qty  = parseInt((row.querySelector('.pos-v2-qty') || {}).value || '0', 10) || 0;
            // The price cell shows "<currency>12.34" — strip the symbol so we can do math.
            var priceTxt = (row.cells[2] ? row.cells[2].textContent || '' : '').replace(/[^0-9.]/g, '');
            var line = parseFloat(priceTxt) || 0;
            var unit = qty > 0 ? line / qty : 0;
            if (qty > 0) {
                list.push({ name: name, qty: qty, unit: unit, line: line });
            }
        });
        return list;
    }

    function readTotals() {
        var subtotal = 0;
        document.querySelectorAll('#pos-v2-items-tbody .pos-v2-item-row').forEach(function (row) {
            var priceTxt = (row.cells[2] ? row.cells[2].textContent || '' : '').replace(/[^0-9.]/g, '');
            subtotal += parseFloat(priceTxt) || 0;
        });
        // Discount: read the hidden "discount" field the form will post.
        var hiddenDiscount = parseFloat((document.getElementById('pos-v2-discount-amount') || {}).value || 0) || 0;
        if (!hiddenDiscount) {
            // Fallback: recompute from the visible Discount controls (POS v2 hasn't run recalcTotals yet).
            var dval  = parseFloat((document.getElementById('pos-v2-discount-value') || {}).value || 0) || 0;
            if (dval < 0) dval = 0;
            if (dval > 100) dval = 100;
            hiddenDiscount = subtotal * (dval / 100);
            if (hiddenDiscount < 0) hiddenDiscount = 0;
            if (hiddenDiscount > subtotal) hiddenDiscount = subtotal;
        }
        var grand = Math.max(0, subtotal - hiddenDiscount);
        // Payment: prefer the visible cash-tendered input.
        var pay = parseFloat((document.getElementById('payment_amount') || {}).value || 0) || 0;
        return { subtotal: subtotal, discount: hiddenDiscount, grand: grand, payment: pay };
    }

    function render() {
        var rows = readRows();
        var totals = readTotals();
        var subtotal = totals.subtotal;
        var grand    = totals.grand;
        var discount = totals.discount;
        var payment  = totals.payment;

        var customerInput = document.querySelector('input[name="customer_name"]');
        if (customerInput) customerOut.textContent = (customerInput.value.trim() || 'Walk-in customer');

        // Build items table (no per-row disc — discount is one whole-sale line at the bottom)
        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:#888; padding:1rem 0;">Cart is empty.</td></tr>';
            watermark.style.display = 'block';
            serialOut.textContent = 'DRAFT';
        } else {
            var html = rows.map(function (r) {
                return '<tr>'
                    + '<td>' + escapeHtml(r.name) + '</td>'
                    + '<td>' + escapeHtml(r.qty) + '</td>'
                    + '<td>' + escapeHtml(moneyLive(r.unit)) + '</td>'
                    + '<td style="text-align:right">' + escapeHtml(moneyLive(r.line)) + '</td>'
                    + '</tr>';
            }).join('');

            // Totals block — matches the printed receipt layout
            html += '<tr class="totals-row" style="padding-top:.4rem;">'
                + '<td colspan="3" style="padding-top:.4rem;">Subtotal</td>'
                + '<td style="text-align:right; padding-top:.4rem;">' + escapeHtml(moneyLive(subtotal)) + '</td>'
                + '</tr>';

            if (discount > 0) {
                html += '<tr class="totals-row" style="padding-top:.2rem;">'
                    + '<td colspan="3">Discount</td>'
                    + '<td style="text-align:right;">−' + escapeHtml(moneyLive(discount)) + '</td>'
                    + '</tr>';
            }

            html += '<tr class="totals-row grand" style="font-weight:700; border-top:1px solid #333; padding-top:.4rem;">'
                + '<td colspan="3" style="padding-top:.4rem;">Total</td>'
                + '<td style="text-align:right; padding-top:.4rem;">' + escapeHtml(moneyLive(grand)) + '</td>'
                + '</tr>';

            if (payment > 0) {
                var paymentMethod = ((document.querySelector('select[name="payment_method"]') || {}).value || 'cash');
                var change = Math.max(0, payment - grand);
                html += '<tr class="totals-row" style="padding-top:.2rem;">'
                    + '<td colspan="3">Paid (' + escapeHtml(String(paymentMethod).toUpperCase()) + ')</td>'
                    + '<td style="text-align:right;">' + escapeHtml(moneyLive(payment)) + '</td>'
                    + '</tr>';
                html += '<tr class="totals-row" style="padding-top:.2rem;">'
                    + '<td colspan="3">Change</td>'
                    + '<td style="text-align:right;">' + escapeHtml(moneyLive(change)) + '</td>'
                    + '</tr>';
            }

            tbody.innerHTML = html;
            watermark.style.display = 'block';
            serialOut.textContent = 'DRAFT (not yet saved)';
        }
    }

    // Refresh whenever the modal opens
    $(modal).on('show.bs.modal', render);

    // Print inside the modal — uses @media print rules in page-css to hide chrome
    if (printBtn) {
        printBtn.addEventListener('click', function () { window.print(); });
    }
})();
</script>
@endpush
@endonce
