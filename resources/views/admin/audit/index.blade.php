@extends('admin.layouts.app')

<x-assets.tabulator />

@push('page-header')
<div class="col-sm-12"><h3 class="page-title">Audit Trail</h3><ul class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Audit Trail</li></ul></div>
@endpush

@section('content')
<div class="card">
	<div class="card-body">
		<div class="table-responsive">
			<div id="audit-table" class="tabulator-table-wrap"></div>
		</div>
	</div>
</div>
@endsection

@push('page-js')
<script>
    $(document).ready(function () {
        if (window.PharmaTabulator) {
            window.PharmaTabulator.server({
                el: 'audit-table',
                url: "{{ route('audit.index') }}",
                placeholder: 'No audit activity recorded.',
                columns: [
                    {title: 'Date', field: 'created_at'},
                    {title: 'User', field: 'user'},
                    {title: 'Action', field: 'action'},
                    {title: 'Route', field: 'route'},
                    {title: 'Method', field: 'method'},
                    {title: 'IP Address', field: 'ip_address'},
                ]
            });
        }
    });
</script>
@endpush