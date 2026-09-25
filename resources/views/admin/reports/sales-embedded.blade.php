<form method="post" action="{{ route('sales.report') }}" class="generation-filter-form">
    @csrf
    <div class="form-row align-items-end">
        <div class="col-md-5"><label>From</label><input type="date" name="from_date" class="form-control" required></div>
        <div class="col-md-5"><label>To</label><input type="date" name="to_date" class="form-control" required></div>
        <div class="col-md-2"><button type="submit" class="btn btn-primary btn-block">Run</button></div>
    </div>
</form>
@if(isset($sales))
<div class="table-responsive mt-4">
    <table class="table table-hover table-center generation-report-table">
        <thead><tr><th>Medicine Name</th><th>Quantity</th><th>Total Price</th><th>Date</th></tr></thead>
        <tbody>
        @foreach($sales as $sale)
            @if(!empty($sale->product->purchase))
            <tr><td>{{ $sale->product->purchase->product }}</td><td>{{ $sale->quantity }}</td><td>{{ AppSettings::get('app_currency', '$') }} {{ $sale->total_price }}</td><td>{{ date_format(date_create($sale->created_at), 'd M, Y') }}</td></tr>
            @endif
        @endforeach
        </tbody>
    </table>
</div>
@endif
