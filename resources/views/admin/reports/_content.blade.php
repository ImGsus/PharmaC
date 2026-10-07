<div class="report-toolbar">
    <form class="form-row align-items-end report-filter-form" method="get" action="{{ route('reports.show', $report) }}">
        <div class="col-md-4"><label for="report-from-{{ $report }}">From</label><input id="report-from-{{ $report }}" type="date" name="from" value="{{ $from }}" class="form-control"></div>
        <div class="col-md-4"><label for="report-to-{{ $report }}">To</label><input id="report-to-{{ $report }}" type="date" name="to" value="{{ $to }}" class="form-control"></div>
        <div class="col-md-2"><button class="btn btn-primary btn-block" type="submit"><i class="fe fe-filter mr-1"></i> Run</button></div>
        <div class="col-md-2">
            <div class="btn-group btn-block" role="group">
                <a class="btn btn-outline-secondary report-csv-link"
                   href="{{ route('reports.export', ['report' => $report]) . '?' . http_build_query(['from' => $from, 'to' => $to]) }}"
                   title="Download CSV"
                   style="flex:1;">
                    <i class="fe fe-download mr-1"></i> CSV
                </a>
                <a class="btn btn-outline-danger report-pdf-link"
                   href="{{ route('reports.pdf', ['report' => $report]) . '?' . http_build_query(['from' => $from, 'to' => $to]) }}"
                   target="_blank"
                   title="Export PDF"
                   style="flex:1;">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </a>
            </div>
        </div>
    </form>
</div>
<p class="report-summary">{{ $definition['description'] }} <strong>{{ $rows->count() }}</strong> result(s).</p>
<div class="table-responsive">
    <table class="table table-hover table-center report-table">
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
