@extends('admin.layouts.app')

@push('page-css')
<style>
    .inventory-check-toolbar { position: sticky; top: 0; z-index: 2; background: #fff; padding: 12px; border-bottom: 1px solid #e6ebf1; }
    .inventory-check-card { border: 1px solid #e6ebf1; border-radius: 8px; margin-bottom: 10px; }
    .inventory-check-card .card-body { padding: 14px; }
    .inventory-check-card h5 { margin-bottom: 4px; }
    .inventory-check-meta { color: #718096; font-size: 13px; }
    body.dark-mode .inventory-check-toolbar, body.dark-mode .inventory-check-card { background: #1c2025; border-color: rgba(255,255,255,.1); }
    @media (min-width: 768px) { .inventory-check-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; } .inventory-check-card { margin-bottom: 0; } }
</style>
@endpush

@push('page-header')
<div class="col-sm-12"><h3 class="page-title">Inventory Check</h3><ul class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Inventory Check</li></ul></div>
@endpush

@section('content')
<div class="card"><div class="card-body p-0"><form method="GET" action="{{ route('inventory-check.index') }}" class="inventory-check-toolbar"><div class="input-group"><input autofocus name="q" value="{{ $search }}" class="form-control" placeholder="Scan barcode or search product / batch"><div class="input-group-append"><button class="btn btn-primary"><i class="fe fe-search"></i> Check</button></div></div></form><div class="p-3"><div class="inventory-check-list">
@forelse($stock as $item)<div class="inventory-check-card"><div class="card-body"><h5>{{ $item->product }}</h5><div class="inventory-check-meta">Barcode: {{ optional($item->purchaseProduct)->barcode ?: 'Not assigned' }}</div><div class="inventory-check-meta">Batch: {{ $item->batch_number ?: 'Not assigned' }} &middot; Supplier: {{ optional($item->supplier)->name ?: 'Unknown' }}</div><div class="mt-2"><strong>{{ $item->quantity }}</strong> units available <span class="float-right badge badge-{{ $item->quantity <= $item->reorder_level ? 'warning' : 'success' }}">{{ $item->quantity <= $item->reorder_level ? 'Reorder soon' : 'In stock' }}</span></div></div></div>@empty<div class="text-center text-muted py-5">No matching stock found.</div>@endforelse
</div>{{ $stock->links() }}</div></div></div>
@endsection