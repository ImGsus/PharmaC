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

<div class="modal fade" id="temperatureEditModal" tabindex="-1" role="dialog" aria-labelledby="temperatureEditTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header"><h5 class="modal-title" id="temperatureEditTitle">Edit Temperature Reading</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button></div>
			<form method="POST" id="temperatureEditForm">
				@csrf
				@method('PUT')
				<div class="modal-body">
					<div class="form-group"><label for="temperatureEditLocation">Location</label><input id="temperatureEditLocation" name="location" required maxlength="150" class="form-control"></div>
					<div class="form-group"><label for="temperatureEditValue">Temperature</label><input id="temperatureEditValue" name="temperature" required type="number" step="0.01" min="-100" max="200" class="form-control"></div>
					<div class="form-row">
						<div class="form-group col"><label for="temperatureEditMinimum">Minimum</label><input id="temperatureEditMinimum" name="minimum_temperature" type="number" step="0.01" min="-100" max="200" class="form-control"></div>
						<div class="form-group col"><label for="temperatureEditMaximum">Maximum</label><input id="temperatureEditMaximum" name="maximum_temperature" type="number" step="0.01" min="-100" max="200" class="form-control"></div>
					</div>
					<div class="form-group"><label for="temperatureEditRecordedAt">Recorded At</label><input id="temperatureEditRecordedAt" name="recorded_at" required type="datetime-local" class="form-control"></div>
					<div class="form-group mb-0"><label for="temperatureEditNotes">Notes</label><textarea id="temperatureEditNotes" name="notes" maxlength="1000" class="form-control"></textarea></div>
				</div>
				<div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Save Changes</button></div>
			</form>
		</div>
	</div>
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
                    {title: 'Action', field: 'action', formatter: 'html', headerSort: false, searchable: false, width: 110, hozAlign: 'center'},
                ]
            });
        }

		$(document).off('click.temperatureEdit', '.temperature-edit-btn').on('click.temperatureEdit', '.temperature-edit-btn', function () {
			var button = $(this);
			$('#temperatureEditForm').attr('action', button.attr('data-route'));
			$('#temperatureEditLocation').val(button.attr('data-location'));
			$('#temperatureEditValue').val(button.attr('data-temperature'));
			$('#temperatureEditMinimum').val(button.attr('data-minimum'));
			$('#temperatureEditMaximum').val(button.attr('data-maximum'));
			$('#temperatureEditRecordedAt').val(button.attr('data-recorded-at'));
			$('#temperatureEditNotes').val(button.attr('data-notes'));
			$('#temperatureEditModal').modal('show');
		});
    });
</script>
@endpush