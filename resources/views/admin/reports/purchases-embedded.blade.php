<form method="post" action="{{ route('purchases.report') }}" class="generation-filter-form">
    @csrf
    <div class="form-row align-items-end">
        <div class="col-md-5"><label>From</label><input type="date" name="from_date" class="form-control" required></div>
        <div class="col-md-5"><label>To</label><input type="date" name="to_date" class="form-control" required></div>
        <div class="col-md-2"><button type="submit" class="btn btn-primary btn-block">Run</button></div>
    </div>
</form>
@if(isset($purchases))
<div class="table-responsive mt-4">
    <table class="table table-hover table-center generation-report-table">
        <thead><tr><th>Medicine Name</th><th>Category</th><th>Supplier</th><th>Purchase Cost</th><th>Quantity</th><th>Expire Date</th></tr></thead>
        <tbody>
        @foreach($purchases as $purchase)
            @if(!empty($purchase->supplier) && !empty($purchase->category))
            <tr><td>{{ $purchase->product }}</td><td>{{ $purchase->category->name }}</td><td>{{ $purchase->supplier->name }}</td><td>{{ AppSettings::get('app_currency', '$') }}{{ $purchase->cost_price }}</td><td>{{ $purchase->quantity }}</td><td>{{ $purchase->expiry_date ? date_format(date_create($purchase->expiry_date), 'd M, Y') : 'No expiry' }}</td></tr>
            @endif
        @endforeach
        </tbody>
    </table>
</div>
@endif
