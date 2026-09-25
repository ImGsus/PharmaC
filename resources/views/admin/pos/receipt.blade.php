{{-- PharMac POS receipt — standalone print-friendly page. --}}
@php
    $currency = settings('app_currency', '$');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Receipt #{{ $sale->id }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #eef1f4;
            margin: 0;
            padding: 1.5rem;
            color: #2c3348;
        }
        .print-bar {
            width: 340px;
            margin: 0 auto 0;
            background: #128587;
            color: #fff;
            text-align: center;
            padding: .9rem;
            font-weight: 700;
            font-size: 1.05rem;
            border-radius: 6px 6px 0 0;
            cursor: pointer;
            user-select: none;
        }
        .print-bar:hover { background: #0f7274; }
        .receipt {
            position: relative;
            width: 340px;
            margin: 0 auto;
            background: #fff;
            padding: 1.5rem;
            border: 1px solid #e2e6ec;
            border-top: none;
        }
        .receipt-logo {
            display: block;
            margin: 0 auto .5rem;
        }
        .receipt h2 {
            text-align: center;
            margin: .25rem 0 1rem;
            font-size: 1.15rem;
            font-weight: 700;
        }
        .receipt h3 {
            text-align: center;
            margin: 0 0 .5rem;
            font-size: .95rem;
            border-top: 1px solid #ddd;
            padding-top: .75rem;
            font-weight: 700;
        }
        .receipt .contact {
            text-align: center;
            font-size: .75rem;
            color: #333;
            line-height: 1.5;
            margin-bottom: 1rem;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            font-size: .72rem;
        }
        table.items th {
            text-align: left;
            border-bottom: 1px solid #333;
            padding: .25rem 0;
            font-weight: 700;
        }
        table.items td {
            padding: .3rem 0;
            vertical-align: top;
        }
        .totals-row td { padding-top: .2rem; }
        .totals-row.grand td {
            font-weight: 700;
            border-top: 1px solid #333;
            padding-top: .4rem;
        }
        .thanks {
            text-align: center;
            font-size: .75rem;
            margin: 1.25rem 0 1rem;
            line-height: 1.5;
        }
        .serial {
            text-align: center;
            font-size: .7rem;
            color: #444;
            border-top: 1px dashed #999;
            padding-top: .5rem;
        }
        .receipt-toolbar {
            display: flex;
            justify-content: center;
            gap: .5rem;
            margin-bottom: 1rem;
        }
        .receipt-toolbar button {
            border: none;
            border-radius: 6px;
            padding: .7rem 1rem;
            font-weight: 700;
            cursor: pointer;
            color: #fff;
        }
        .receipt-toolbar .btn-print { background: #128587; }
        .receipt-toolbar .btn-print:hover { background: #0f7274; }
        .receipt-toolbar .btn-edit { background: #4b67f0; }
        .receipt-toolbar .btn-edit:hover { background: #3c52d1; }
        .receipt-contact-edit {
            display: none;
            margin-top: 1rem;
            text-align: left;
        }
        .receipt-contact-edit label {
            display: block;
            font-size: .78rem;
            margin-bottom: .15rem;
            font-weight: 700;
        }
        .receipt-contact-edit textarea,
        .receipt-contact-edit input {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #cdd5e0;
            border-radius: 6px;
            padding: .6rem .75rem;
            margin-bottom: .75rem;
            font-family: 'Poppins', sans-serif;
            font-size: .85rem;
        }
        .receipt-contact-edit-actions {
            display: flex;
            justify-content: flex-end;
            gap: .5rem;
        }
        .receipt-contact-edit-actions button {
            border: none;
            border-radius: 6px;
            padding: .55rem 1rem;
            font-weight: 700;
            cursor: pointer;
        }
        .receipt-contact-edit-actions .btn-save { background: #128587; color: #fff; }
        .receipt-contact-edit-actions .btn-cancel { background: #6c757d; color: #fff; }
        @media print {
            body { background: #fff; padding: 0; }
            .receipt-toolbar { display: none; }
            .receipt-contact-edit { display: none !important; }
            .receipt  { border: none; }
        }
    </style>
</head>
<body>
    <div class="receipt-toolbar">
        <button class="btn-print" type="button" onclick="window.print()">Print Receipt</button>
        <button class="btn-edit" type="button" id="receipt-edit-btn">Edit Contact</button>
    </div>

    <div class="receipt">
        {{-- Logo placeholder (POS inc monitor icon, blue stroke) --}}
        <svg class="receipt-logo" width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
            <circle cx="30" cy="30" r="28" fill="none" stroke="#2f6fed" stroke-width="3"/>
            <rect x="16" y="20" width="28" height="18" rx="2" fill="none" stroke="#2f6fed" stroke-width="2.5"/>
            <line x1="20" y1="44" x2="40" y2="44" stroke="#2f6fed" stroke-width="2.5"/>
        </svg>

        <h2>{{ $companyName }}</h2>

        <h3>Contact Us</h3>
        <div class="contact" id="receipt-contact-view">
            <span id="receipt-contact-address">{{ $companyAddress }}</span><br>
            Email : <span id="receipt-contact-email">{{ $companyEmail }}</span><br>
            Phone : <span id="receipt-contact-phone">{{ $companyPhone }}</span><br>
            Cashier : {{ $cashierName }}@if($sale->customer_name)
                <br>Customer : {{ $sale->customer_name }}
            @endif
        </div>

        <div class="receipt-contact-edit" id="receipt-contact-edit">
            <label for="receipt-address-input">Address</label>
            <textarea id="receipt-address-input" rows="2">{{ $companyAddress }}</textarea>
            <label for="receipt-email-input">Email</label>
            <input id="receipt-email-input" type="text" value="{{ $companyEmail }}">
            <label for="receipt-phone-input">Phone</label>
            <input id="receipt-phone-input" type="text" value="{{ $companyPhone }}">
            <div class="receipt-contact-edit-actions">
                <button type="button" class="btn-save" id="receipt-contact-save">Save</button>
                <button type="button" class="btn-cancel" id="receipt-contact-cancel">Cancel</button>
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th style="text-align:right">Sub Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    @php
                        $productName = optional(optional($item->product)->purchase)->product ?? ('Product #' . $item->product_id);
                        $lineTotal   = (float) $item->total_price;
                        $unitPrice   = $item->quantity > 0 ? $lineTotal / $item->quantity : 0;
                    @endphp
                    <tr>
                        <td>{{ $productName }}</td>
                        <td>{{ (int) $item->quantity }}</td>
                        <td>{{ $currency }}{{ number_format($unitPrice, 2) }}</td>
                        <td style="text-align:right">{{ $currency }}{{ number_format($lineTotal, 2) }}</td>
                    </tr>
                @endforeach

                {{-- Subtotal = sum of the per-line totals (pre-discount) --}}
                <tr class="totals-row">
                    <td colspan="3" style="padding-top:.4rem;">Subtotal</td>
                    <td style="text-align:right; padding-top:.4rem;">{{ $currency }}{{ number_format($subtotal, 2) }}</td>
                </tr>

                @if (!empty($sale->discount) && (float) $sale->discount > 0)
                    <tr class="totals-row">
                        <td colspan="3">Discount</td>
                        <td style="text-align:right;">−{{ $currency }}{{ number_format((float) $sale->discount, 2) }}</td>
                    </tr>
                @endif

                <tr class="totals-row grand">
                    <td colspan="3">Total</td>
                    <td style="text-align:right">
                        {{ $currency }}{{ number_format(max(0, $subtotal - (float) ($sale->discount ?? 0)), 2) }}
                    </td>
                </tr>
                @if ($payment > 0)
                    <tr class="totals-row">
                        <td colspan="3">Paid ({{ strtoupper($sale->payment_method ?? 'cash') }})</td>
                        <td style="text-align:right">{{ $currency }}{{ number_format($payment, 2) }}</td>
                    </tr>
                    <tr class="totals-row">
                        <td colspan="3">Change</td>
                        <td style="text-align:right">{{ $currency }}{{ number_format($change, 2) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        @if (!empty($sale->notes))
            <div class="contact" style="border-top:1px dashed #ccc; padding-top:.5rem; margin-top:1rem; text-align:left;">
                <strong>Notes:</strong> {{ $sale->notes }}
            </div>
        @endif

        <div class="thanks">
            ** Thank you for your visit! **
        </div>

        <div class="serial">
            Serial: {{ str_pad($sale->id, 10, '0', STR_PAD_LEFT) }}
            &nbsp; {{ $sale->created_at ? $sale->created_at->format('m/d/y') : '' }}
            &nbsp; {{ $sale->created_at ? $sale->created_at->format('H:i') : '' }}
        </div>
    </div>

    <script>
        (function() {
            var editBtn   = document.getElementById('receipt-edit-btn');
            var saveBtn   = document.getElementById('receipt-contact-save');
            var cancelBtn = document.getElementById('receipt-contact-cancel');
            var editPane  = document.getElementById('receipt-contact-edit');
            var viewPane  = document.getElementById('receipt-contact-view');
            var addressEl = document.getElementById('receipt-address-input');
            var emailEl   = document.getElementById('receipt-email-input');
            var phoneEl   = document.getElementById('receipt-phone-input');
            var addressView = document.getElementById('receipt-contact-address');
            var emailView   = document.getElementById('receipt-contact-email');
            var phoneView   = document.getElementById('receipt-contact-phone');

            if (!editBtn || !saveBtn || !cancelBtn || !editPane || !viewPane) return;

            var storageKey = 'pharmac_receipt_contact';

            function loadStoredContact() {
                try {
                    var stored = localStorage.getItem(storageKey);
                    if (!stored) return;
                    var data = JSON.parse(stored);
                    if (data.address) {
                        addressView.textContent = data.address;
                        addressEl.value = data.address;
                    }
                    if (data.email) {
                        emailView.textContent = data.email;
                        emailEl.value = data.email;
                    }
                    if (data.phone) {
                        phoneView.textContent = data.phone;
                        phoneEl.value = data.phone;
                    }
                } catch (e) {
                    console.warn('Could not load saved contact info.', e);
                }
            }

            function saveContact() {
                var data = {
                    address: addressEl.value,
                    email: emailEl.value,
                    phone: phoneEl.value,
                };
                try {
                    localStorage.setItem(storageKey, JSON.stringify(data));
                } catch (e) {
                    console.warn('Could not save contact info.', e);
                }
            }

            function openEdit() {
                editPane.style.display = 'block';
                viewPane.style.display = 'none';
            }
            function closeEdit() {
                editPane.style.display = 'none';
                viewPane.style.display = 'block';
            }

            loadStoredContact();

            editBtn.addEventListener('click', function () {
                openEdit();
            });
            saveBtn.addEventListener('click', function () {
                addressView.textContent = addressEl.value;
                emailView.textContent = emailEl.value;
                phoneView.textContent = phoneEl.value;
                saveContact();
                closeEdit();
            });
            cancelBtn.addEventListener('click', function () {
                addressEl.value = addressView.textContent;
                emailEl.value   = emailView.textContent;
                phoneEl.value   = phoneView.textContent;
                closeEdit();
            });
        })();
    </script>
</body>
</html>
