<form id="inventory-check-search-form" method="GET" action="{{ route('inventory-check.modal-content') }}" class="inventory-check-toolbar">
    <div class="input-group">
        <input id="inventory-check-search" name="q" value="{{ $search }}" class="form-control" placeholder="Scan barcode or search product / batch" aria-label="Scan barcode or search product or batch">
        <div class="input-group-append">
            <button type="submit" class="btn btn-primary"><i class="fe fe-search" aria-hidden="true"></i> Check</button>
        </div>
    </div>
</form>

<div class="inventory-check-list">
    @forelse($stock as $item)
        <div class="inventory-check-card">
            <div class="card-body">
                <h5>{{ $item->product }}</h5>
                <div class="inventory-check-meta">Barcode: {{ optional($item->purchaseProduct)->barcode ?: 'Not assigned' }}</div>
                <div class="inventory-check-meta">Batch: {{ $item->batch_number ?: 'Not assigned' }} &middot; Supplier: {{ optional($item->supplier)->name ?: 'Unknown' }}</div>
                <div class="mt-2">
                    <strong>{{ $item->quantity }}</strong> units available
                    <span class="float-right badge badge-{{ $item->quantity <= $item->reorder_level ? 'warning' : 'success' }}">{{ $item->quantity <= $item->reorder_level ? 'Reorder soon' : 'In stock' }}</span>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-5">No matching stock found.</div>
    @endforelse
</div>

<div class="d-flex justify-content-center mt-3">
    {{ $stock->links() }}
</div>