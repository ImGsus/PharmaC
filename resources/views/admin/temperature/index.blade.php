@extends('admin.layouts.app')

<x-assets.tabulator />

@push('page-header')
<div class="col-sm-12"><h3 class="page-title">Temperature Monitoring</h3><ul class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Temperature</li></ul></div>
@endpush

@section('content')
<div class="card mb-4"><div class="card-header"><h4 class="card-title">Record Reading</h4></div><div class="card-body"><form method="POST" action="{{ route('temperature.store') }}">@csrf<div class="row"><div class="col-md-3 form-group"><label>Location</label><input name="location" required class="form-control" placeholder="Medicine refrigerator"></div><div class="col-md-2 form-group"><label>Temperature</label><input name="temperature" required type="number" step="0.01" class="form-control"></div><div class="col-md-2 form-group"><label>Minimum</label><input name="minimum_temperature" type="number" step="0.01" class="form-control"></div><div class="col-md-2 form-group"><label>Maximum</label><input name="maximum_temperature" type="number" step="0.01" class="form-control"></div><div class="col-md-3 form-group"><label>Recorded At</label><input name="recorded_at" required type="datetime-local" value="{{ now()->format('Y-m-d\\TH:i') }}" class="form-control"></div><div class="col-md-9 form-group"><label>Notes</label><input name="notes" class="form-control"></div><div class="col-md-3 form-group d-flex align-items-end"><button class="btn btn-primary btn-block">Save Reading</button></div></div></form></div></div>
<div class="card"><div class="card-body"><div class="table-responsive">
			<div id="temperature-table" class="tabulator-table-wrap"></div>
		</div></div>
</div>
@endsection

@push('page-js')
<script>
    $(document).ready(function () {
        if (window.PharmaTabulator) {
            window.PharmaTabulator.server({
                el: 'temperature-table',
                url: "{{ route('temperature.index') }}",
                placeholder: 'No temperature readings recorded.',
                columns: [
                    {title: 'Recorded', field: 'recorded_at'},
                    {title: 'Location', field: 'location'},
                    {title: 'Temperature', field: 'temperature'},
                    {title: 'Safe Range', field: 'safe_range'},
                    {title: 'Source', field: 'source'},
                    {title: 'Status', field: 'status', formatter: 'html', headerSort: false, searchable: false},
                ]
            });
        }
    });
</script>
@endpush