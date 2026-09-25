{{-- PharMac POS / Cashier — redesigned to match image 12: product tile grid on
     the left, Sale Summary panel on the right. --}}
@extends('admin.layouts.app')

@section('title', 'POS Orders')

@php
    $currency = settings('app_currency', '$');
@endphp

@push('page-css')
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}?v={{ filemtime(public_path('css/pos.css')) }}">
@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">POS / Cashier</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">POS / Cashier</li>
    </ul>
</div>
@endpush

@section('content')
<div class="pos-page pos-page-v2">

    <form method="POST" action="{{ route('sales.store') }}" id="pos-form" autocomplete="off">
        @csrf

        <div class="row pos-v2-row">
            {{-- =================== LEFT: PRODUCT GRID =================== --}}
            <div class="col-lg-7 pos-v2-left">

                {{-- Barcode scanner — USB scanners type the code + press Enter,
                     so this input is always auto-focused and re-focuses after
                     each scan. No need to click it. --}}
                <div class="pos-v2-scanner">
                    <div class="pos-v2-scanner-icon">
                        <i class="fas fa-barcode"></i>
                    </div>
                    <input type="text"
                           id="pos-v2-barcode"
                           class="form-control pos-v2-scanner-input"
                           placeholder="Scan barcode or type SKU / barcode and press Enter"
                           autocomplete="off"
                           autocapitalize="off"
                           spellcheck="false"
                           inputmode="text">
                    <button type="button" class="btn btn-primary pos-v2-scanner-btn" id="pos-v2-barcode-go">
                        <i class="fas fa-search"></i> Add
                    </button>
                </div>

                {{-- Top filter bar --}}
                <div class="pos-v2-toolbar">
                    <select id="pos-v2-category" class="form-control pos-v2-category-select">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <input type="text" id="pos-v2-search" class="form-control pos-v2-search"
                           placeholder="Search In Products" autocomplete="off">

                    <button type="button" id="pos-v2-clear-filters" class="btn btn-light pos-v2-icon-btn" title="Reset filters">
                        <i class="fas fa-undo"></i>
                    </button>
                </div>

                {{-- Pagination bar + secondary actions --}}
                <div class="pos-v2-pager">
                    <button type="button" class="btn btn-link pos-v2-page-btn" data-page-dir="-1">
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <div class="pos-v2-page-numbers" id="pos-v2-page-numbers"></div>
                    <button type="button" class="btn btn-link pos-v2-page-btn" data-page-dir="1">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>

                    <button type="button" class="btn btn-outline-secondary pos-v2-secondary-btn" id="pos-v2-categories-btn" data-toggle="modal" data-target="#categoriesModal">
                        <i class="fas fa-th-list"></i> Categories
                    </button>
                    <button type="button" class="btn btn-outline-secondary pos-v2-secondary-btn" data-toggle="modal" data-target="#calculatorModal">
                        <i class="fas fa-calculator"></i> Calculator
                    </button>
                </div>

                {{-- Product grid --}}
                <div class="pos-v2-grid" id="pos-v2-grid">
                    {{-- populated by JS --}}
                </div>

                {{-- Hidden payload of products (id, name, price, stock, category, image, expired) --}}
                <script id="pos-v2-products" type="application/json">@json($products)</script>
            </div>

            {{-- =================== RIGHT: SALE SUMMARY =================== --}}
            <div class="col-lg-5 pos-v2-right">
                <div class="pos-v2-summary">
                    {{-- Green header --}}
                    <div class="pos-v2-summary-head">
                        <div>
                            <h4>Sale Summary</h4>
                            <small>#<span id="pos-v2-receipt-no">{{ str_pad(mt_rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT) }}</span></small>
                        </div>
                        <div class="pos-v2-head-controls">
                            <select id="pos-v2-currency" class="form-control form-control-sm pos-v2-currency-select">
                                <option value="USD - $" @selected($currency === '$' || stripos($currency, 'USD') !== false)>USD - $</option>
                                <option value="PHP - ₱" @selected(stripos($currency, 'PHP') !== false || $currency === '₱')>PHP - ₱</option>
                            </select>
                            <input type="datetime-local" id="pos-v2-datetime" class="form-control form-control-sm pos-v2-datetime"
                                   value="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}">
                            <button type="button" class="btn btn-sm btn-light pos-v2-icon-btn" id="pos-v2-add-customer" title="Pick customer">
                                <i class="fas fa-user-plus"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Items table --}}
                    <div class="pos-v2-items">
                        <table class="table pos-v2-items-table mb-0">
                            <thead>
                                <tr>
                                    <th>Item Description <i class="fas fa-sort"></i></th>
                                    <th class="text-center" style="width:90px;">Qty</th>
                                    <th class="text-right" style="width:120px;">Price <i class="fas fa-sort"></i></th>
                                    <th class="text-right" style="width:60px;"></th>
                                </tr>
                            </thead>
                            <tbody id="pos-v2-items-tbody">
                                <tr id="pos-v2-empty-row">
                                    <td colspan="4" class="text-center text-muted pos-v2-empty">No items yet. Click a product to add.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Totals --}}
                    <div class="pos-v2-totals">
                        <div class="pos-v2-totals-row">
                            <label>Payment Method</label>
                            <select id="pos-v2-payment-method" name="payment_method" class="form-control form-control-sm">
                                <option value="cash" selected>Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="credit_card">Credit Card</option>
                            </select>
                        </div>
                        <div class="pos-v2-totals-row">
                            <label>Customer</label>
                            <input type="text" name="customer_name" class="form-control form-control-sm" placeholder="Walk-in customer">
                        </div>
                        <div class="pos-v2-totals-row">
                            <label>Notes</label>
                            <input type="text" name="notes" class="form-control form-control-sm" placeholder="Optional">
                        </div>
                        <div class="pos-v2-totals-row">
                            <label>Discount</label>
                            <div class="pos-v2-discount">
                                <span class="pos-v2-discount-unit" title="Percentage discount">%</span>
                                <input type="number" id="pos-v2-discount-value" class="form-control form-control-sm" min="0" max="100" step="0.01" value="0" placeholder="0">
                            </div>
                        </div>
                        {{-- Cash Tendered + Change: shown only for cash payments.
                             Wrapped in a single container so we can toggle it. --}}
                        <div id="pos-v2-cash-block">
                            <div class="pos-v2-totals-row">
                                <label>Cash Tendered</label>
                                {{-- Calculator pipes "=" result here. The hidden payment_amount below
                                     carries the value to the server. --}}
                                <input type="number" id="payment_amount" class="form-control form-control-sm" min="0" step="0.01" value="0" placeholder="0.00">
                            </div>
                            <div class="pos-v2-totals-row pos-v2-grand-row">
                                <label>Change</label>
                                <span>{{ $currency }}<span id="pos-v2-change">0.00</span></span>
                            </div>
                        </div>
                        <div class="pos-v2-totals-row pos-v2-grand-row pos-v2-grand">
                            <label>Total</label>
                            <span>{{ $currency }}<span id="pos-v2-grand">0.00</span></span>
                        </div>

                        {{-- Hidden fields actually posted to the server --}}
                        <input type="hidden" name="discount"       id="pos-v2-discount-amount"  value="0">
                        <input type="hidden" name="payment_amount" id="pos-v2-payment-amount"   value="">
                        <input type="hidden" name="change_amount"  id="pos-v2-change-amount"    value="0">
                    </div>

                    {{-- Action button row --}}
                    <div class="pos-v2-actions">
                        <button type="button" class="btn btn-warning pos-v2-action-btn" id="pos-v2-btn-start-session" data-session-state="closed">
                            <i class="fas fa-play-circle"></i> <span id="pos-v2-session-label">START SESSION</span>
                        </button>
                        <button type="button" class="btn btn-danger pos-v2-action-btn pos-v2-action-icon" id="pos-v2-btn-delete" title="Delete last item">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button type="button" class="btn btn-secondary pos-v2-action-btn pos-v2-action-icon" id="pos-v2-btn-hold" title="Hold sale">
                            <i class="fas fa-pause"></i>
                        </button>
                        <button type="button" class="btn btn-success pos-v2-action-btn pos-v2-action-icon" id="pos-v2-btn-gift" title="Gift / free">
                            <i class="fas fa-gift"></i>
                        </button>
                        <button type="button" class="btn btn-info pos-v2-action-btn" id="pos-v2-btn-comment">
                            <i class="fas fa-comment"></i> COMMENT
                        </button>
                        {{-- SAVE starts disabled; renderCart() flips it on once the cart
                             has at least one product. Clicking while disabled is a no-op. --}}
                        <button type="submit" class="btn btn-primary pos-v2-action-btn" id="pos-v2-btn-save" disabled>
                            <i class="fas fa-save"></i> SAVE
                        </button>
                        <button type="button" class="btn btn-warning pos-v2-action-btn" id="pos-v2-btn-print" data-toggle="modal" data-target="#printOrdersModal">
                            <i class="fas fa-print"></i> PRINT
                        </button>
                        <button type="button" class="btn btn-info pos-v2-action-btn" id="pos-v2-btn-email">
                            <i class="fas fa-envelope"></i> EMAIL
                        </button>
                        <button type="button" class="btn btn-light pos-v2-action-btn" id="pos-v2-btn-clear">
                            <i class="fas fa-eraser"></i> CLEAR
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Modals --}}
@include('admin.pos.modals.calculator')
@include('admin.pos.modals.categories')
@include('admin.pos.modals.history')
@include('admin.pos.modals.report')
@include('admin.pos.modals.receipt-preview')
@include('admin.pos.modals.print-orders')
@include('admin.pos.modals.sessions')
@include('admin.pos.modals.session-choice')
@endsection

@push('page-js')
<script>
(function(){
    var products      = JSON.parse(document.getElementById('pos-v2-products').textContent || '[]');
    var grid          = document.getElementById('pos-v2-grid');
    var pageNumbers   = document.getElementById('pos-v2-page-numbers');
    var itemsTbody    = document.getElementById('pos-v2-items-tbody');
    var searchInput   = document.getElementById('pos-v2-search');
    var categorySel   = document.getElementById('pos-v2-category');
    var clearFilters  = document.getElementById('pos-v2-clear-filters');
    var grandEl       = document.getElementById('pos-v2-grand');
    var changeEl      = document.getElementById('pos-v2-change');
    var discountValue = document.getElementById('pos-v2-discount-value');
    var discountAmount= document.getElementById('pos-v2-discount-amount');
    var paymentInput  = document.getElementById('payment_amount');
    var paymentHidden = document.getElementById('pos-v2-payment-amount');
    var changeHidden  = document.getElementById('pos-v2-change-amount');
    var emptyRow      = document.getElementById('pos-v2-empty-row');
    var currencySel   = document.getElementById('pos-v2-currency');
    var paymentMethodSel = document.getElementById('pos-v2-payment-method');
    var cashBlock     = document.getElementById('pos-v2-cash-block');
    var form          = document.getElementById('pos-form');
    var receiptNoEl   = document.getElementById('pos-v2-receipt-no');

    var PAGE_SIZE = 12;
    var POS_PAGE_STORAGE_KEY = 'pharmacy-pos-product-page';
    var currentPage = 1;
    try {
        var urlPage = parseInt(new URLSearchParams(window.location.search).get('page') || '0', 10);
        var savedPage = urlPage || parseInt(localStorage.getItem(POS_PAGE_STORAGE_KEY) || '1', 10);
        if (savedPage > 0) currentPage = savedPage;
    } catch (e) {
        currentPage = 1;
    }
    var cart = []; // { id, name, price, qty }

    // ---- Barcode scanner ----
    // USB barcode scanners act like keyboards: they type the code quickly
    // and send an Enter key. We listen for Enter on the barcode field,
    // POST to /pos/orders/scan, then add the result to the cart.
    var barcodeInput = document.getElementById('pos-v2-barcode');
    var barcodeGoBtn = document.getElementById('pos-v2-barcode-go');
    var SCAN_URL     = @json(route('pos.orders.scan'));
    var scanBusy     = false;

    function refocusBarcode() {
        // Re-focus on a short delay so it works even if the cashier
        // just clicked something else (e.g. a product tile).
        setTimeout(function () {
            if (barcodeInput && !scanBusy) barcodeInput.focus();
        }, 30);
    }

    function showScanToast(text, isError) {
        if (window.Snackbar) {
            Snackbar.show({
                text: text,
                duration: isError ? 4000 : 2500,
                pos: 'top-right',
                backgroundColor: isError ? '#e8483f' : '#21b573',
                textColor: '#ffffff',
            });
        } else {
            alert(text);
        }
    }

    function performScan() {
        if (!barcodeInput || scanBusy) return;
        var code = (barcodeInput.value || '').trim();
        if (!code) {
            showScanToast('Type or scan a barcode first.', true);
            return;
        }
        scanBusy = true;
        barcodeInput.disabled = true;

        var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        var fd   = new FormData();
        fd.append('barcode', code);

        fetch(SCAN_URL, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf
            },
            body: fd
        })
        .then(function (r) {
            return r.json().catch(function () {
                return { ok: false, message: 'Unexpected server response.' };
            }).then(function (data) { return { status: r.status, data: data }; });
        })
        .then(function (resp) {
            if (resp.data && resp.data.ok && resp.data.product) {
                addToCart(resp.data.product);
                showScanToast('Added: ' + resp.data.product.name, false);
            } else {
                showScanToast((resp.data && resp.data.message) || ('No product for barcode "' + code + '".'), true);
            }
        })
        .catch(function () {
            showScanToast('Network error — could not scan.', true);
        })
        .finally(function () {
            scanBusy = false;
            if (barcodeInput) {
                barcodeInput.disabled = false;
                barcodeInput.value = '';
                barcodeInput.focus();
            }
        });
    }

    if (barcodeInput) {
        // Enter = scan
        barcodeInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                performScan();
            }
        });
        // Auto-focus on page load so the cashier can scan immediately
        refocusBarcode();
    }
    if (barcodeGoBtn) {
        barcodeGoBtn.addEventListener('click', function (e) {
            e.preventDefault();
            performScan();
        });
    }
    // If the cashier clicks anywhere else, pull focus back to the scanner
    // shortly after — so the next scan works without needing to click.
    document.addEventListener('click', function (e) {
        if (!barcodeInput) return;
        // Don't steal focus from real form fields (qty inputs, notes, etc.)
        var t = e.target;
        if (!t) return;
        var tag = (t.tagName || '').toLowerCase();
        if (tag === 'input' || tag === 'textarea' || tag === 'select' || tag === 'button' || tag === 'a') return;
        refocusBarcode();
    });

    var CURRENCY_SYMBOL = @json($currency);
    function currentSymbol() {
        var v = currencySel ? currencySel.value : '';
        if (v.indexOf('$') >= 0) return '$';
        if (v.indexOf('₱') >= 0) return '₱';
        return CURRENCY_SYMBOL;
    }
    function money(n) { return currentSymbol() + Number(n || 0).toFixed(2); }

    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    function filtered() {
        var q = (searchInput.value || '').toLowerCase().trim();
        var cat = categorySel.value;
        return products.filter(function (p) {
            if (cat && String(p.category_id) !== String(cat)) return false;
            if (q && (p.name || '').toLowerCase().indexOf(q) === -1) return false;
            return true;
        });
    }

    function renderPager(total) {
        var totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));
        if (currentPage > totalPages) currentPage = totalPages;
        pageNumbers.innerHTML = '';
        var maxBtns = 5;
        var start = Math.max(1, currentPage - 2);
        var end   = Math.min(totalPages, start + maxBtns - 1);
        if (end - start < maxBtns - 1) start = Math.max(1, end - maxBtns + 1);
        if (start > 1) {
            pageNumbers.appendChild(makePageBtn(1, false));
            if (start > 2) pageNumbers.appendChild(makeEllipsis());
        }
        for (var i = start; i <= end; i++) {
            pageNumbers.appendChild(makePageBtn(i, i === currentPage));
        }
        if (end < totalPages) {
            if (end < totalPages - 1) pageNumbers.appendChild(makeEllipsis());
            pageNumbers.appendChild(makePageBtn(totalPages, false));
        }
        // store totalPages for next/prev
        pageNumbers.dataset.total = totalPages;
    }

    function makeEllipsis() {
        var s = document.createElement('span');
        s.className = 'pos-v2-page-ellipsis';
        s.textContent = '…';
        return s;
    }

    function makePageBtn(n, active) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'btn pos-v2-page-num' + (active ? ' active' : '');
        b.textContent = n;
        b.addEventListener('click', function () { currentPage = n; render(); });
        return b;
    }

    function renderGrid() {
        var list = filtered();
        renderPager(list.length);
        var start = (currentPage - 1) * PAGE_SIZE;
        var page  = list.slice(start, start + PAGE_SIZE);
        if (page.length === 0) {
            grid.innerHTML = '<div class="pos-v2-empty-grid">No products match your filters.</div>';
            return;
        }
        var cards = page.map(function (p) {
            var img = '<img src="' + escapeHtml(p.image || @json(asset('assets/img/productnoimage.png'))) + '" alt="">';
            var exp = p.expired ? ' <span class="pos-v2-tile-exp">⚠ EXPIRED</span>' : '';
            return '<div class="pos-v2-tile' + (p.expired ? ' is-expired' : '') + '"'
                 + ' data-id="' + escapeHtml(p.id) + '"'
                 + ' data-name="' + escapeHtml(p.name) + '"'
                 + ' data-price="' + escapeHtml(p.price) + '"'
                 + ' data-stock="' + escapeHtml(p.stock) + '"'
                 + ' data-expired="' + (p.expired ? '1' : '0') + '">'
                 +   img
                 +   '<div class="pos-v2-tile-name">' + escapeHtml(p.name) + exp + '</div>'
                 + '</div>';
        });

        grid.innerHTML = cards.join('');

        Array.prototype.forEach.call(grid.querySelectorAll('.pos-v2-tile[data-id]'), function (tile) {
            tile.addEventListener('click', function () {
                addToCart({
                    id:      tile.getAttribute('data-id'),
                    name:    tile.getAttribute('data-name'),
                    price:   parseFloat(tile.getAttribute('data-price')) || 0,
                    stock:   parseInt(tile.getAttribute('data-stock') || '0', 10),
                    expired: tile.getAttribute('data-expired') === '1',
                });
            });
        });
    }

    function render() {
        renderGrid();
        saveCurrentPage();
    }

    function saveCurrentPage() {
        try {
            localStorage.setItem(POS_PAGE_STORAGE_KEY, String(currentPage));
            var url = new URL(window.location.href);
            url.searchParams.set('page', String(currentPage));
            window.history.replaceState({}, '', url.toString());
        } catch (e) {
            // Browser storage or URL history may be unavailable.
        }
    }

    function resetProductPage() {
        currentPage = 1;
        saveCurrentPage();
        var url = new URL(window.location.href);
        url.searchParams.delete('page');
        window.history.replaceState({}, '', url.toString());
    }

    function addToCart(p) {
        // If already in cart, increment qty (up to stock)
        for (var i = 0; i < cart.length; i++) {
            if (String(cart[i].id) === String(p.id)) {
                if (cart[i].qty + 1 > p.stock) {
                    if (window.Snackbar) Snackbar.show({ text: 'Stock limit reached for ' + p.name, duration: 3000, pos: 'top-right' });
                    return;
                }
                cart[i].qty += 1;
                renderCart();
                return;
            }
        }
        cart.push({ id: p.id, name: p.name, price: p.price, qty: 1, stock: p.stock, expired: p.expired });
        renderCart();
    }

    function renderCart() {
        if (cart.length === 0) {
            itemsTbody.innerHTML = '';
            itemsTbody.appendChild(emptyRow);
        } else {
            itemsTbody.innerHTML = cart.map(function (c, idx) {
                return '<tr class="pos-v2-item-row' + (c.expired ? ' row-expired' : '') + '" data-idx="' + idx + '">'
                    + '<td>'
                    +   '<input type="hidden" name="items[' + idx + '][product]" value="' + escapeHtml(c.id) + '">'
                    +   escapeHtml(c.name) + (c.expired ? ' <small class="text-danger">⚠</small>' : '')
                    + '</td>'
                    + '<td class="text-center">'
                    +   '<input type="number" class="form-control form-control-sm text-center pos-v2-qty" name="items[' + idx + '][quantity]" min="1" max="' + escapeHtml(c.stock) + '" value="' + escapeHtml(c.qty) + '">'
                    + '</td>'
                    + '<td class="text-right">' + money(c.price * c.qty) + '</td>'
                    + '<td class="text-right"><button type="button" class="btn btn-link text-danger pos-v2-row-remove" data-idx="' + idx + '"><i class="fas fa-times"></i></button></td>'
                    + '</tr>';
            }).join('');
            Array.prototype.forEach.call(itemsTbody.querySelectorAll('.pos-v2-qty'), function (inp) {
                inp.addEventListener('focus', function () {
                    inp.select();
                });
                inp.addEventListener('input', function () {
                    var idx = parseInt(inp.closest('tr').getAttribute('data-idx'), 10);
                    var value = (inp.value || '').trim();
                    if (value === '') {
                        return;
                    }
                    var q = parseInt(value, 10);
                    if (Number.isNaN(q)) {
                        return;
                    }
                    if (q < 1) q = 1;
                    if (q <= cart[idx].stock) {
                        cart[idx].qty = q;
                        var row = inp.closest('tr');
                        if (row) {
                            var priceCell = row.querySelector('td.text-right');
                            if (priceCell) {
                                priceCell.textContent = money(cart[idx].price * q);
                            }
                        }
                        recalcTotals();
                    }
                });
                inp.addEventListener('blur', function () {
                    var idx = parseInt(inp.closest('tr').getAttribute('data-idx'), 10);
                    var value = (inp.value || '').trim();
                    var q = parseInt(value, 10);
                    if (Number.isNaN(q) || q < 1) {
                        q = 1;
                    }
                    if (q > cart[idx].stock) {
                        q = cart[idx].stock;
                        if (window.Snackbar) {
                            Snackbar.show({
                                text: 'Quantity capped at ' + q + ' for this product.',
                                duration: 4000,
                                pos: 'top-right',
                                backgroundColor: '#e8483f',
                                textColor: '#ffffff'
                            });
                        } else {
                            alert('Quantity capped at ' + q + ' for this product.');
                        }
                    }
                    cart[idx].qty = q;
                    inp.value = q;
                    renderCart();
                });
            });
            Array.prototype.forEach.call(itemsTbody.querySelectorAll('.pos-v2-row-remove'), function (btn) {
                btn.addEventListener('click', function () {
                    var idx = parseInt(btn.getAttribute('data-idx'), 10);
                    cart.splice(idx, 1);
                    renderCart();
                });
            });
        }
        recalcTotals();

        // Keep the SAVE button in sync with cart state. It is disabled when
        // the cart is empty so the user can never accidentally trigger a
        // submit on an empty cart.
        var btnSave = document.getElementById('pos-v2-btn-save');
        if (btnSave) {
            btnSave.disabled = cart.length === 0;
        }
    }

    function recalcTotals() {
        var subtotal = cart.reduce(function (acc, c) { return acc + c.price * c.qty; }, 0);
        var discVal  = parseFloat(discountValue.value) || 0;
        if (discVal < 0) discVal = 0;
        if (discVal > 100) discVal = 100;
        var discount = subtotal * (discVal / 100);
        if (discount < 0) discount = 0;
        if (discount > subtotal) discount = subtotal;
        var tax = 0; // no tax row in v2; keep field but show 0 for now
        var grand = Math.max(0, subtotal - discount + tax);
        grandEl.textContent = grand.toFixed(2);

        // payment + change
        var tendered = parseFloat(paymentInput && paymentInput.value ? paymentInput.value : 0) || 0;
        if (tendered < 0) tendered = 0;
        var change = tendered > 0 ? Math.max(0, tendered - grand) : 0;
        if (changeEl) changeEl.textContent = change.toFixed(2);

        // mirror the visible values into the hidden fields the form posts
        if (discountAmount) discountAmount.value = discount.toFixed(2);
        if (paymentHidden)  paymentHidden.value  = tendered > 0 ? tendered.toFixed(2) : '';
        if (changeHidden)   changeHidden.value   = change.toFixed(2);
    }

    // ---- bindings ----
    document.querySelectorAll('.pos-v2-page-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var dir = parseInt(btn.getAttribute('data-page-dir'), 10);
            var totalPages = parseInt(pageNumbers.dataset.total || '1', 10);
            currentPage = Math.max(1, Math.min(totalPages, currentPage + dir));
            saveCurrentPage();
            render();
        });
    });

    if (searchInput) searchInput.addEventListener('input', function () { resetProductPage(); render(); });
    if (categorySel) categorySel.addEventListener('change', function () { resetProductPage(); render(); });
    if (clearFilters) clearFilters.addEventListener('click', function () {
        searchInput.value = ''; categorySel.value = ''; resetProductPage(); render();
    });
    if (discountValue) discountValue.addEventListener('input', recalcTotals);
    if (paymentInput)  paymentInput.addEventListener('input', recalcTotals);
    if (currencySel)   currencySel.addEventListener('change', renderCart);

    // Toggle the Cash Tendered + Change rows based on payment method.
    // For non-cash payments the rows are hidden and the validation in the
    // submit handler is skipped.
    function syncPaymentMethodUI() {
        if (!paymentMethodSel || !cashBlock) return;
        var isCash = paymentMethodSel.value === 'cash';
        cashBlock.style.display = isCash ? '' : 'none';
        if (!isCash && paymentInput) {
            paymentInput.classList.remove('is-invalid');
        }
        recalcTotals();
    }
    if (paymentMethodSel) paymentMethodSel.addEventListener('change', syncPaymentMethodUI);
    // Run once on load so the UI matches the default selection
    syncPaymentMethodUI();

    // Action buttons

    // ---- POS session (Start / End) ----
    // Start Session records a "time in" for this cashier. End Session records
    // the "time out" and opens the Sessions modal showing this shift + history.
    // The button is dual-state: yellow when no session is open, red when one is.
    var sessionBtn      = document.getElementById('pos-v2-btn-start-session');
    var sessionLabel    = document.getElementById('pos-v2-session-label');
    var sessionIcon     = sessionBtn ? sessionBtn.querySelector('i')     : null;
    var SESSION_START   = @json(route('pos.session.start'));
    var SESSION_END     = @json(route('pos.session.end'));
    var SESSION_HISTORY = @json(route('pos.session.history'));
    var sessionBusy     = false;

    function setSessionUiOpen(startedAtIso) {
        if (!sessionBtn) return;
        sessionBtn.dataset.sessionState = 'open';
        sessionBtn.classList.remove('btn-warning');
        sessionBtn.classList.add('btn-danger');
        if (sessionIcon)  { sessionIcon.classList.remove('fa-play-circle'); sessionIcon.classList.add('fa-stop-circle'); }
        if (sessionLabel) {
            var since = startedAtIso ? formatTimeOfDay(startedAtIso) : '';
            sessionLabel.textContent = since ? ('END SESSION · since ' + since) : 'END SESSION';
        }
    }
    function setSessionUiClosed() {
        if (!sessionBtn) return;
        sessionBtn.dataset.sessionState = 'closed';
        sessionBtn.classList.remove('btn-danger');
        sessionBtn.classList.add('btn-warning');
        if (sessionIcon)  { sessionIcon.classList.remove('fa-stop-circle'); sessionIcon.classList.add('fa-play-circle'); }
        if (sessionLabel) { sessionLabel.textContent = 'START SESSION'; }
    }
    function formatTimeOfDay(iso) {
        // iso like "2026-08-12 12:34:56" — show just HH:MM
        if (!iso) return '';
        var m = String(iso).match(/(\d{2}):(\d{2})/);
        return m ? (m[1] + ':' + m[2]) : '';
    }

    function postJson(url, body) {
        var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        var fd = new FormData();
        Object.keys(body || {}).forEach(function (k) { fd.append(k, body[k]); });
        return fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf
            },
            body: fd
        }).then(function (r) {
            return r.json().catch(function () { return { ok: false, message: 'Unexpected server response.' }; })
                .then(function (data) { return { status: r.status, data: data }; });
        });
    }

    if (sessionBtn) {
        sessionBtn.addEventListener('click', function () {
            if (sessionBusy) return;
            var state = sessionBtn.dataset.sessionState || 'closed';

            if (state === 'closed') {
                if (window.jQuery) {
                    window.jQuery('#sessionChoiceModal').modal('show');
                }
                return;
            }

            sessionBusy = true;

            if (state === 'open') {
                // End session
                postJson(SESSION_END, {})
                .then(function (resp) {
                    if (resp.data && resp.data.ok && resp.data.session) {
                        setSessionUiClosed();
                        // Mark the just-closed row so the modal can highlight it.
                        window.__posSessionJustClosed = resp.data.session.id;
                        if (window.jQuery) {
                            window.jQuery('#sessionsModal').modal('show');
                        }
                        if (window.Snackbar) {
                            Snackbar.show({
                                text: 'Session ended. Earnings: ' + (resp.data.session.currency || '') + ' ' + Number(resp.data.session.total_earnings || 0).toFixed(2),
                                duration: 5000, pos: 'top-right',
                                backgroundColor: '#21b573', textColor: '#ffffff'
                            });
                        }
                    } else {
                        if (window.Snackbar) {
                            Snackbar.show({
                                text: (resp.data && resp.data.message) || 'Failed to end session.',
                                duration: 5000, pos: 'top-right',
                                backgroundColor: '#e8483f', textColor: '#ffffff'
                            });
                        }
                    }
                })
                .catch(function () {
                    if (window.Snackbar) Snackbar.show({ text: 'Network error.', duration: 4000, pos: 'top-right', backgroundColor: '#e8483f', textColor: '#ffffff' });
                })
                .finally(function () { sessionBusy = false; });
            } else {
                startSessionNow();
            }
        });

        function startSessionNow() {
            if (sessionBusy) return;
            sessionBusy = true;
            postJson(SESSION_START, {})
            .then(function (resp) {
                if (resp.data && resp.data.ok && resp.data.session) {
                    setSessionUiOpen(resp.data.session.started_at);
                    if (window.Snackbar) {
                        Snackbar.show({
                            text: 'Session started at ' + formatTimeOfDay(resp.data.session.started_at),
                            duration: 3000, pos: 'top-right',
                            backgroundColor: '#21b573', textColor: '#ffffff'
                        });
                    }
                } else if (window.Snackbar) {
                    Snackbar.show({
                        text: (resp.data && resp.data.message) || 'Failed to start session.',
                        duration: 5000, pos: 'top-right',
                        backgroundColor: '#e8483f', textColor: '#ffffff'
                    });
                }
            })
            .catch(function () {
                if (window.Snackbar) Snackbar.show({ text: 'Network error.', duration: 4000, pos: 'top-right', backgroundColor: '#e8483f', textColor: '#ffffff' });
            })
            .finally(function () {
                sessionBusy = false;
            });
        }

        var startNowButton = document.getElementById('pos-session-start-now');
        var viewHistoryButton = document.getElementById('pos-session-view-history');
        if (startNowButton) {
            startNowButton.addEventListener('click', function () {
                if (window.jQuery) window.jQuery('#sessionChoiceModal').modal('hide');
                startSessionNow();
            });
        }
        if (viewHistoryButton) {
            viewHistoryButton.addEventListener('click', function () {
                if (window.jQuery) {
                    window.jQuery('#sessionChoiceModal').modal('hide');
                    window.jQuery('#sessionsModal').modal('show');
                }
            });
        }

        /*
         * On page load, ask the server if this cashier has an open session.
         * If yes, render the button in END state with the correct Time In.
         */
        fetch(SESSION_HISTORY, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
        .then(function (data) {
            if (!data || !data.sessions) return;
            var open = data.sessions.find(function (s) { return s.status === 'open'; });
            if (open) setSessionUiOpen(open.started_at);
        })
        .catch(function () { /* silent */ });
    }

    document.getElementById('pos-v2-btn-delete').addEventListener('click', function () {
        if (cart.length === 0) return;
        cart.pop();
        renderCart();
    });
    document.getElementById('pos-v2-btn-hold').addEventListener('click', function () {
        if (cart.length === 0) return;
        if (window.Snackbar) Snackbar.show({ text: 'Sale held (' + cart.length + ' items)', duration: 3000, pos: 'top-right' });
    });
    document.getElementById('pos-v2-btn-gift').addEventListener('click', function () {
        if (cart.length === 0) return;
        if (window.Snackbar) Snackbar.show({ text: 'Marked as gift (total: ' + money(grandEl.textContent) + ')', duration: 3000, pos: 'top-right' });
    });
    document.getElementById('pos-v2-btn-comment').addEventListener('click', function () {
        var n = document.querySelector('input[name="notes"]');
        if (n) { n.focus(); n.select(); }
    });
    document.getElementById('pos-v2-btn-email').addEventListener('click', function () {
        if (window.Snackbar) Snackbar.show({ text: 'Email receipt — coming soon', duration: 3000, pos: 'top-right' });
    });
    document.getElementById('pos-v2-btn-clear').addEventListener('click', function () {
        cart = [];
        if (discountValue) discountValue.value = 0;
        if (paymentInput) {
            paymentInput.value = 0;
            paymentInput.classList.remove('is-invalid');
        }
        renderCart();
    });

    // AJAX submit: stay on the POS page, open the saved receipt in a new tab.
    if (form) {
        form.addEventListener('submit', function (e) {
            // Block any default submission AND any other listeners that
            // might also try to handle the click.
            e.preventDefault();
            e.stopImmediatePropagation();

            // Guard 1: empty cart. The SAVE button is also disabled in this
            // state, but we belt-and-braces here so a programmatic submit
            // can never get past this point.
            if (cart.length === 0) {
                if (window.Snackbar) {
                    Snackbar.show({ text: 'Add at least one product before saving.', duration: 4000, pos: 'top-right' });
                } else {
                    alert('Add at least one product before saving.');
                }
                return false;
            }

            // Cash validation: when paying with cash, the amount tendered must
            // be at least the grand total. For non-cash methods the field is
            // hidden so this branch is skipped.
            var method = paymentMethodSel ? paymentMethodSel.value : 'cash';
            var tendered = paymentInput ? (parseFloat(paymentInput.value) || 0) : 0;
            var grand = parseFloat(grandEl.textContent) || 0;
            if (method === 'cash' && tendered + 0.0001 < grand) {
                if (window.Snackbar) {
                    Snackbar.show({
                        text: 'Cash tendered (' + money(tendered) + ') must be at least the total (' + money(grand) + ').',
                        duration: 5000, pos: 'top-right',
                        backgroundColor: '#e8483f', textColor: '#ffffff'
                    });
                } else {
                    alert('Cash tendered must be at least the total.');
                }
                // Visual cue on the field
                if (paymentInput) {
                    paymentInput.classList.add('is-invalid');
                    paymentInput.focus();
                    paymentInput.select();
                }
                return false;
            }
            if (paymentInput) paymentInput.classList.remove('is-invalid');

            // Final recalc so the hidden fields hold the latest values
            recalcTotals();

            var btnSave = document.getElementById('pos-v2-btn-save');
            var saveBusy = true;
            if (btnSave) { btnSave.disabled = true; btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> SAVING…'; }

            // While a save is in flight, also lock the session button — but
            // only if a session is currently open. Without this, the cashier
            // can click END before the Save commits, which closes the time
            // window *before* the new sale row is written — so the sale
            // lands in `sales` but is invisible to the session's earnings
            // count (and the next Sessions modal shows 0 orders for the
            // just-closed shift). Saving/ending must be serial.
            //
            // We deliberately *don't* lock when state is "closed": the
            // button then reads "START SESSION" and the cashier needs it
            // free so they can begin a shift before saving.
            if (sessionBtn && sessionBtn.dataset.sessionState === 'open') {
                sessionBtn.disabled = true;
            }

            // Safety net: if the fetch never resolves (network drop, server
            // hang), restore the SAVE button after 20s so the cashier is never
            // stuck on "SAVING…" forever.
            var stuckTimer = setTimeout(function () {
                if (!saveBusy) return;
                saveBusy = false;
                if (btnSave) {
                    // renderCart() will set the correct disabled state from cart length.
                    btnSave.innerHTML = '<i class="fas fa-save"></i> SAVE';
                    btnSave.disabled = cart.length === 0;
                }
                if (sessionBtn && sessionBtn.dataset.sessionState === 'open') {
                    // Re-enable END SESSION if a session is still open. We
                    // never locked it for the closed-state case, so this is
                    // also a no-op there.
                    sessionBtn.disabled = false;
                }
                if (window.Snackbar) {
                    Snackbar.show({
                        text: 'Save is taking too long — please retry.',
                        duration: 5000, pos: 'top-right',
                        backgroundColor: '#e8483f', textColor: '#ffffff'
                    });
                }
            }, 20000);

            // Only save when a session is open. If the cashier hit SAVE
            // without clicking START SESSION first, prompt them to start
            // one — otherwise the sale won't be counted under any shift
            // and the Sessions modal will show 0 earnings for it.
            var state = (sessionBtn && sessionBtn.dataset.sessionState) || 'closed';
            if (state !== 'open') {
                if (window.Snackbar) {
                    Snackbar.show({
                        text: 'Click START SESSION first so this transaction is recorded under your shift.',
                        duration: 5000, pos: 'top-right',
                        backgroundColor: '#e8483f', textColor: '#ffffff'
                    });
                } else {
                    alert('Click START SESSION first so this transaction is recorded under your shift.');
                }
                if (btnSave) {
                    btnSave.disabled = cart.length === 0;
                    btnSave.innerHTML = '<i class="fas fa-save"></i> SAVE';
                }
                if (sessionBtn) sessionBtn.focus();
                if (typeof stuckTimer !== 'undefined') {
                    clearTimeout(stuckTimer);
                }
                saveBusy = false;
                return false;
            }

            if (sessionBtn) {
                sessionBtn.disabled = true;
            }

            var receiptWindow = null;
            try {
                receiptWindow = window.open('', '_blank', 'toolbar=0,location=0,menubar=0,status=0,resizable=1,scrollbars=1,width=900,height=700');
            } catch (e) {
                receiptWindow = null;
            }

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: new FormData(form)
            })
            .then(function (r) {
                return r.json().catch(function () { return { ok: false, message: 'Unexpected server response.' }; })
                    .then(function (data) { return { status: r.status, data: data }; });
            })
            .then(function (resp) {
                if (resp.data && resp.data.ok) {
                    // Open the receipt in a popup window, and fall back if blocked.
                    if (resp.data.receipt_url) {
                        if (receiptWindow && !receiptWindow.closed) {
                            receiptWindow.location = resp.data.receipt_url;
                            receiptWindow.focus();
                        } else {
                            window.open(resp.data.receipt_url, '_blank', 'toolbar=0,location=0,menubar=0,status=0,resizable=1,scrollbars=1,width=900,height=700');
                        }
                    }
                    // Roll the on-screen receipt number over to the saved sale id
                    if (receiptNoEl && resp.data.sale_id) {
                        var id = String(resp.data.sale_id);
                        receiptNoEl.textContent = id.length >= 10 ? id : ('0000000000' + id).slice(-10);
                    }
                    // Clear the cart so the cashier is ready for the next sale
                    cart = [];
                    if (discountValue) discountValue.value = 0;
                    if (paymentInput)  paymentInput.value = 0;
                    renderCart();

                    if (window.Snackbar) {
                        Snackbar.show({
                            text: resp.data.message || ('Transaction #' + resp.data.sale_id + ' recorded.'),
                            duration: 4000, pos: 'top-right'
                        });
                    }
                } else {
                    if (window.Snackbar) {
                        Snackbar.show({
                            text: (resp.data && resp.data.message) || 'Save failed.',
                            duration: 5000, pos: 'top-right'
                        });
                    } else {
                        alert((resp.data && resp.data.message) || 'Save failed.');
                    }
                }
            })
            .catch(function () {
                if (window.Snackbar) {
                    Snackbar.show({ text: 'Network error — could not save.', duration: 5000, pos: 'top-right' });
                } else {
                    alert('Network error — could not save.');
                }
            })
            .finally(function () {
                clearTimeout(stuckTimer);
                saveBusy = false;
                if (btnSave) {
                    // renderCart() decides the correct disabled state from cart length,
                    // so an empty cart after a successful save stays disabled.
                    btnSave.innerHTML = '<i class="fas fa-save"></i> SAVE';
                    btnSave.disabled = cart.length === 0;
                }
                if (sessionBtn) {
                    sessionBtn.disabled = false;
                    // If the session is still logically open, ensure the label
                    // remains in the END SESSION state.
                    if (sessionBtn.dataset.sessionState === 'open') {
                        sessionBtn.classList.remove('btn-warning');
                        sessionBtn.classList.add('btn-danger');
                    }
                }
            });
            return false;
        });
    }

    // Initial render
    render();
})();
</script>
@endpush
