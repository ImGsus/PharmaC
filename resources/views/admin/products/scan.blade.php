@extends('admin.layouts.app')

@push('page-css')
    <style>
        .barcode-card {
            border-radius: 18px;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.08);
        }
        .barcode-input {
            border-radius: 12px;
            padding: 18px 20px;
            font-size: 1.1rem;
        }
        .barcode-card .card-body {
            padding: 2rem;
        }
    </style>
@endpush

@push('page-header')
<div class="col-sm-12">
    <h3 class="page-title">Barcode Scanner</h3>
    <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
        <li class="breadcrumb-item active">Barcode Scan</li>
    </ul>
</div>
@endpush

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card barcode-card">
            <div class="card-body text-center">
                <h4 class="mb-4">Scan or enter product barcode / SKU</h4>
                <form action="{{ route('products.lookup') }}" method="post">
                    @csrf
                    <div class="mb-3">
                        <input type="text" name="barcode" class="form-control barcode-input text-center" placeholder="Enter SKU or barcode" autofocus autocomplete="off" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">Lookup Product</button>
                </form>
                <div class="mt-3">
                    <a href="/Webby/index.php" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary">Scan with camera</a>
                </div>
                @if(session('product'))
                    <div class="mt-4 text-start">
                        <h5>Product details</h5>
                        <p><strong>Name:</strong> {{ session('product')->purchase->product ?? 'N/A' }}</p>
                        <p><strong>SKU / Barcode:</strong> {{ session('product')->barcode }}</p>
                        <p><strong>Price:</strong> {{ AppSettings::get('app_currency','$') }} {{ session('product')->price }}</p>
                        <p><strong>Expiry:</strong> {{ optional(session('product')->purchase)->expiry_date ? date_format(date_create(session('product')->purchase->expiry_date), 'd M, Y') : 'N/A' }}</p>
                        <p><strong>Status:</strong> {{ session('product')->expired ? 'Expired' : 'Active' }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
