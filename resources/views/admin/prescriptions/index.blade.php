@extends('admin.layouts.app')

<x-assets.tabulator />

@push('page-header')
<div class="col-sm-12"><h3 class="page-title">Prescription Verification</h3><ul class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Prescriptions</li></ul></div>
@endpush

@section('content')
<div class="card mb-4"><div class="card-header"><h4 class="card-title">Submit Prescription</h4></div><div class="card-body"><form method="POST" action="{{ route('prescriptions.store') }}" enctype="multipart/form-data">@csrf<div class="row"><div class="col-md-3 form-group"><label>Prescription Number</label><input name="prescription_number" class="form-control"></div><div class="col-md-3 form-group"><label>Patient Name</label><input name="patient_name" required class="form-control"></div><div class="col-md-3 form-group"><label>Prescriber Name</label><input name="prescriber_name" class="form-control"></div><div class="col-md-3 form-group"><label>Issued Date</label><input type="date" name="issued_at" class="form-control"></div><div class="col-md-8 form-group"><label>Prescription Document</label><input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" class="form-control"></div><div class="col-md-4 form-group d-flex align-items-end"><button class="btn btn-primary btn-block">Submit for Verification</button></div></div></form></div></div>
<div class="card"><div class="card-body"><div class="table-responsive">
			<div id="prescription-table" class="tabulator-table-wrap"></div>
		</div></div>
</div>
@endsection

@push('page-js')
<script>
    $(document).ready(function () {
        if (window.PharmaTabulator) {
            window.PharmaTabulator.server({
                el: 'prescription-table',
                url: "{{ route('prescriptions.index') }}",
                placeholder: 'No prescriptions submitted.',
                columns: [
                    {title: 'Date', field: 'created_at'},
                    {title: 'Patient', field: 'patient_name'},
                    {title: 'Prescriber', field: 'prescriber_name'},
                    {title: 'Prescription #', field: 'prescription_number'},
                    {title: 'Status', field: 'status', formatter: 'html'},
                    {title: 'Verification', field: 'verification', formatter: 'html', headerSort: false, searchable: false, width: 420, minWidth: 400},
                ]
            });
        }
    });
</script>
@endpush