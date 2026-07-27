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
    <div class="col-md-12">
        <div class="text-center mt-5">
            <h4 class="mb-4">Camera Scanner</h4>
            <p class="text-muted">Click the button below to open the camera scanner.</p>
            <button id="open_webby" class="btn btn-primary btn-lg">Open Camera Scanner</button>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function(){
        var btn = document.getElementById('open_webby');
        if(!btn) return;
        btn.addEventListener('click', function(){
            var params = new URLSearchParams(window.location.search);
            var origin = params.get('origin');
            var url = '/Webby/index.php';
            if(origin){
                url += '?origin=' + encodeURIComponent(origin);
            }
            window.open(url, 'webby_scanner', 'width=900,height=700');
        });
    });
</script>
@endsection
