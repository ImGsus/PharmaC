<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title }} — {{ $appName }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #1a202c; background: #f8fafc; }

/* Screen styles */
.pdf-page { max-width: 1100px; margin: 20px auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 32px 40px; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
.pdf-header { display: flex; align-items: flex-start; justify-content: space-between; border-bottom: 3px solid #1e3a5f; padding-bottom: 16px; margin-bottom: 20px; }
.pdf-logo-area { display: flex; align-items: center; gap: 12px; }
.pdf-logo-area img { max-height: 52px; max-width: 160px; object-fit: contain; }
.pdf-app-name { font-size: 22px; font-weight: 700; color: #1e3a5f; letter-spacing: 0.5px; }
.pdf-app-subtitle { font-size: 10px; color: #64748b; margin-top: 2px; }
.pdf-meta { text-align: right; }
.pdf-meta h1 { font-size: 15px; font-weight: 700; color: #1e3a5f; margin-bottom: 4px; }
.pdf-meta p { font-size: 10px; color: #64748b; line-height: 1.6; }
.pdf-summary { background: #f0f7ff; border-left: 4px solid #2563eb; padding: 8px 14px; margin-bottom: 18px; border-radius: 0 4px 4px 0; font-size: 11px; color: #1e3a5f; }
.pdf-table-wrap { overflow-x: auto; margin-bottom: 20px; }
table { width: 100%; border-collapse: collapse; font-size: 11px; }
thead tr { background: #1e3a5f; color: #fff; }
thead th { padding: 8px 10px; text-align: left; font-weight: 600; white-space: nowrap; border: 1px solid #16304f; }
tbody tr:nth-child(even) { background: #f8fafc; }
tbody tr:hover { background: #eff6ff; }
tbody td { padding: 7px 10px; border: 1px solid #e2e8f0; vertical-align: top; }
.pdf-footer-row { border-top: 2px solid #1e3a5f; padding-top: 12px; margin-top: 8px; display: flex; justify-content: space-between; align-items: center; font-size: 10px; color: #64748b; }
.badge-expired { background: #fee2e2; color: #991b1b; padding: 1px 6px; border-radius: 9999px; font-size: 10px; font-weight: 600; }
.badge-critical { background: #fef3c7; color: #92400e; padding: 1px 6px; border-radius: 9999px; font-size: 10px; font-weight: 600; }
.badge-soon { background: #fef9c3; color: #713f12; padding: 1px 6px; border-radius: 9999px; font-size: 10px; font-weight: 600; }

/* Action buttons (screen only) */
.no-print { display: block; }
.print-actions { position: sticky; top: 0; z-index: 100; background: #1e3a5f; padding: 10px 20px; display: flex; gap: 12px; align-items: center; justify-content: flex-end; }
.print-actions span { color: #fff; font-size: 13px; font-weight: 600; margin-right: auto; }
.btn-print { background: #2563eb; color: #fff; border: none; padding: 8px 18px; border-radius: 5px; cursor: pointer; font-size: 13px; font-weight: 600; }
.btn-print:hover { background: #1d4ed8; }
.btn-close-pdf { background: transparent; color: #cbd5e1; border: 1px solid #475569; padding: 8px 14px; border-radius: 5px; cursor: pointer; font-size: 13px; }
.btn-close-pdf:hover { background: #334155; }

@media print {
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
    body { background: #fff !important; color: #000 !important; font-size: 10px; }
    .no-print, .print-actions { display: none !important; }
    .pdf-page { max-width: none; margin: 0; border: none; border-radius: 0; padding: 0; box-shadow: none; }
    thead tr { background: #1e3a5f !important; color: #fff !important; }
    tbody tr:nth-child(even) { background: #f8fafc !important; }
    table { page-break-inside: auto; }
    tr { page-break-inside: avoid; }
    @php echo '@page { margin: 1.5cm; size: A4 ' . $orientation . '; }'; @endphp
}
</style>
</head>
<body>

<div class="print-actions no-print">
    <span>📄 {{ $title }}</span>
    <button class="btn-print" onclick="window.print()">🖨️ Print / Save as PDF</button>
    <button class="btn-close-pdf" onclick="window.close()">✕ Close</button>
</div>

<div class="pdf-page">
    {{-- Header --}}
    <div class="pdf-header">
        <div class="pdf-logo-area">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $appName }} Logo">
            @endif
            <div>
                <div class="pdf-app-name">{{ $appName }}</div>
                <div class="pdf-app-subtitle">Pharmacy Management System</div>
            </div>
        </div>
        <div class="pdf-meta">
            <h1>{{ $title }}</h1>
            <p>Period: {{ \Carbon\Carbon::parse($from)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</p>
            <p>Generated: {{ $generatedAt }}</p>
        </div>
    </div>

    {{-- Summary banner --}}
    <div class="pdf-summary">
        {{ $definition['description'] }}
        &nbsp;|&nbsp; <strong>{{ $rows->count() }}</strong> record(s) found for the selected period.
    </div>

    {{-- Table --}}
    <div class="pdf-table-wrap">
        @if($rows->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th style="width:36px;">#</th>
                    @foreach(array_keys($rows->first()) as $heading)
                    <th>{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $i => $row)
                <tr>
                    <td style="color:#94a3b8;">{{ $i + 1 }}</td>
                    @foreach($row as $col => $value)
                    <td>@if($col === 'Status' && $value === 'Expired')<span class="badge-expired">{{ $value }}</span>@elseif($col === 'Status' && $value === 'Critical')<span class="badge-critical">{{ $value }}</span>@elseif($col === 'Status' && $value === 'Expiring soon')<span class="badge-soon">{{ $value }}</span>@else{{ $value }}@endif</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="text-align:center; padding: 40px 0; color: #64748b;">
            <p style="font-size:14px;">No records found for the selected date range.</p>
            <p style="font-size:11px; margin-top:6px;">Try adjusting the From / To dates and regenerating the report.</p>
        </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="pdf-footer-row">
        <span>{{ $appName }} &copy; {{ date('Y') }} &mdash; Confidential</span>
        <span>Total Records: <strong>{{ $rows->count() }}</strong></span>
        <span>{{ $generatedAt }}</span>
    </div>
</div>

<script>
    // Auto-print after page fully loads
    window.addEventListener('load', function() {
        setTimeout(function() { window.print(); }, 700);
    });
</script>
</body>
</html>
