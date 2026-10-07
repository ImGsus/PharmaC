@php
    $fromDate = old('from_date', request('from_date', ''));
    $toDate   = old('to_date',   request('to_date',   ''));
    $currency = AppSettings::get('app_currency', '$');
@endphp

<div class="report-toolbar">
    <form method="post" action="{{ route('sales.report') }}" class="generation-filter-form">
        @csrf
        <div class="form-row align-items-end">
            <div class="col-md-4">
                <label for="sales-from">From</label>
                <input id="sales-from" type="date" name="from_date" class="form-control" value="{{ $fromDate }}" required>
            </div>
            <div class="col-md-4">
                <label for="sales-to">To</label>
                <input id="sales-to" type="date" name="to_date" class="form-control" value="{{ $toDate }}" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fe fe-filter mr-1"></i> Run
                </button>
            </div>
            <div class="col-md-2">
                <div class="btn-group btn-block" role="group">
                    <a class="btn btn-outline-secondary report-csv-link{{ !isset($sales) ? ' disabled' : '' }}"
                       href="{{ isset($sales) ? route('sales.report.export', ['format'=>'csv','from_date'=>$fromDate,'to_date'=>$toDate]) : '#' }}"
                       title="Download CSV" style="flex:1;">
                        <i class="fe fe-download mr-1"></i> CSV
                    </a>
                    <a class="btn btn-outline-danger report-pdf-link{{ !isset($sales) ? ' disabled' : '' }}"
                       href="{{ isset($sales) ? route('sales.report.export', ['format'=>'pdf','from_date'=>$fromDate,'to_date'=>$toDate]) : '#' }}"
                       target="_blank" title="Export PDF" style="flex:1;">
                        <i class="fas fa-file-pdf mr-1"></i> PDF
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

@if(isset($sales))
<p class="report-summary">
    Sales and dispensing transactions from <strong>{{ $fromDate }}</strong> to <strong>{{ $toDate }}</strong>.
    <strong>{{ $sales->filter(fn($s) => !empty($s->product?->purchase))->count() }}</strong> result(s).
</p>
<div class="table-responsive">
    <table class="table table-hover table-center generation-report-table">
        <thead>
            <tr>
                <th>Medicine Name</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        @forelse($sales as $sale)
            @if(!empty($sale->product?->purchase))
            <tr>
                <td>{{ $sale->product->purchase->product }}</td>
                <td>{{ $sale->quantity }}</td>
                <td>{{ $currency }} {{ number_format((float)$sale->total_price, 2) }}</td>
                <td>{{ date_format(date_create($sale->created_at), 'd M, Y') }}</td>
            </tr>
            @endif
        @empty
            <tr><td colspan="4" class="text-center text-muted">No sales found for this date range.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endif
