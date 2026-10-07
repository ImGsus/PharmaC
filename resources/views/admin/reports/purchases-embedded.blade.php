@php
    $fromDate = old('from_date', request('from_date', ''));
    $toDate   = old('to_date',   request('to_date',   ''));
    $currency = AppSettings::get('app_currency', '$');
@endphp

<div class="report-toolbar">
    <form method="post" action="{{ route('purchases.report') }}" class="generation-filter-form">
        @csrf
        <div class="form-row align-items-end">
            <div class="col-md-4">
                <label for="purchases-from">From</label>
                <input id="purchases-from" type="date" name="from_date" class="form-control" value="{{ $fromDate }}" required>
            </div>
            <div class="col-md-4">
                <label for="purchases-to">To</label>
                <input id="purchases-to" type="date" name="to_date" class="form-control" value="{{ $toDate }}" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fe fe-filter mr-1"></i> Run
                </button>
            </div>
            <div class="col-md-2">
                <div class="btn-group btn-block" role="group">
                    <a class="btn btn-outline-secondary report-csv-link{{ !isset($purchases) ? ' disabled' : '' }}"
                       href="{{ isset($purchases) ? route('purchases.report.export', ['format'=>'csv','from_date'=>$fromDate,'to_date'=>$toDate]) : '#' }}"
                       title="Download CSV" style="flex:1;">
                        <i class="fe fe-download mr-1"></i> CSV
                    </a>
                    <a class="btn btn-outline-danger report-pdf-link{{ !isset($purchases) ? ' disabled' : '' }}"
                       href="{{ isset($purchases) ? route('purchases.report.export', ['format'=>'pdf','from_date'=>$fromDate,'to_date'=>$toDate]) : '#' }}"
                       target="_blank" title="Export PDF" style="flex:1;">
                        <i class="fas fa-file-pdf mr-1"></i> PDF
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

@if(isset($purchases))
<p class="report-summary">
    Purchase records from <strong>{{ $fromDate }}</strong> to <strong>{{ $toDate }}</strong>.
    <strong>{{ $purchases->filter(fn($p) => !empty($p->supplier) && !empty($p->category))->count() }}</strong> result(s).
</p>
<div class="table-responsive">
    <table class="table table-hover table-center generation-report-table">
        <thead>
            <tr>
                <th>Medicine Name</th>
                <th>Category</th>
                <th>Supplier</th>
                <th>Purchase Cost</th>
                <th>Quantity</th>
                <th>Expire Date</th>
            </tr>
        </thead>
        <tbody>
        @forelse($purchases as $purchase)
            @if(!empty($purchase->supplier) && !empty($purchase->category))
            <tr>
                <td>{{ $purchase->product }}</td>
                <td>{{ $purchase->category->name }}</td>
                <td>{{ $purchase->supplier->name }}</td>
                <td>{{ $currency }}{{ number_format((float)$purchase->cost_price, 2) }}</td>
                <td>{{ $purchase->quantity }}</td>
                <td>{{ $purchase->expiry_date ? date_format(date_create($purchase->expiry_date), 'd M, Y') : 'No expiry' }}</td>
            </tr>
            @endif
        @empty
            <tr><td colspan="6" class="text-center text-muted">No purchases found for this date range.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endif
