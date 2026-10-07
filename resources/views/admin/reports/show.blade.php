@extends('admin.layouts.app')

<x-assets.tabulator />

@push('page-css')
<style>
    .report-toolbar { background: #fff; border: 1px solid #e6ebf1; border-radius: 10px; padding: 16px; margin-bottom: 18px; }
    .report-summary { color: #718096; margin-bottom: 16px; }
    .report-table th { white-space: nowrap; }
    body.dark-mode .report-toolbar { background: #1c2025; border-color: rgba(255,255,255,.08); }
    body.dark-mode .report-toolbar .btn-outline-secondary { color: #e7e9ec !important; background: transparent !important; border-color: rgba(255,255,255,.28) !important; }
</style>
@endpush

@push('page-header')
<div class="col-sm-8 col-auto">
    <h3 class="page-title">{{ $definition['title'] }}</h3>
    <ul class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li><li class="breadcrumb-item active">{{ $definition['title'] }}</li></ul>
</div>
<div class="col-sm-4 col text-right"><a href="{{ route('reports.index') }}" class="btn btn-light mt-2"><i class="fe fe-arrow-left mr-1"></i> Reports</a></div>
@endpush

@section('content')
<div class="report-toolbar">
    <form class="form-row align-items-end" method="get" action="{{ route('reports.show', $report) }}">
        <div class="col-md-4"><label for="report-from">From</label><input id="report-from" type="date" name="from" value="{{ $from }}" class="form-control"></div>
        <div class="col-md-4"><label for="report-to">To</label><input id="report-to" type="date" name="to" value="{{ $to }}" class="form-control"></div>
        <div class="col-md-2"><button class="btn btn-primary btn-block" type="submit"><i class="fe fe-filter mr-1"></i> Run</button></div>
        <div class="col-md-2">
            <div class="btn-group btn-block" role="group">
                <a class="btn btn-outline-secondary"
                   href="{{ route('reports.export', ['report' => $report]) . '?' . http_build_query(['from' => $from, 'to' => $to]) }}"
                   title="Download as CSV"
                   style="flex:1;">
                    <i class="fe fe-download mr-1"></i> CSV
                </a>
                <a class="btn btn-outline-danger"
                   href="{{ route('reports.pdf', ['report' => $report]) . '?' . http_build_query(['from' => $from, 'to' => $to]) }}"
                   target="_blank"
                   title="Export as PDF"
                   style="flex:1;">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </a>
            </div>
        </div>
    </form>
</div>
<p class="report-summary">{{ $definition['description'] }} <strong>{{ $rows->count() }}</strong> result(s).</p>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="report-table" class="table table-hover table-center report-table">
                <thead><tr>@if($rows->isNotEmpty()) @foreach(array_keys($rows->first()) as $heading)<th>{{ $heading }}</th>@endforeach @else <th>Result</th>@endif</tr></thead>
                <tbody>
                @forelse($rows as $row)
                    <tr>@foreach($row as $value)<td>{{ $value }}</td>@endforeach</tr>
                @empty
                    <tr><td class="text-center text-muted" colspan="20">No records match the selected report.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
    $(function () {
        if (document.getElementById('report-table') && window.PharmaTabulator) {
            window.PharmaTabulator.fromDom({
                el: 'report-table',
                key: 'report-detail',
                pageLength: 10
            });
        }
    });
</script>
@endpush
