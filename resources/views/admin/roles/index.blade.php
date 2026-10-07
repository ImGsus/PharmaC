@extends('admin.layouts.app')

<x-assets.tabulator />  

@push('page-css')
<style>
	#roleCreateModal .modal-dialog { max-width: 560px; }
	#roleCreateModal .modal-content { border: 1px solid #b8c4d2; border-radius: 8px; box-shadow: 0 16px 36px rgba(31, 45, 61, .2); }
	#roleCreateModal .modal-header { padding: 20px 24px; border-bottom: 1px solid #e5eaf1; }
	#roleCreateModal .modal-body { padding: 24px; }
	#roleCreateModal .form-control { min-height: 44px; border-color: #cfd7e3; border-radius: 5px; }
	#roleCreateModal .modal-footer .btn-primary { min-width: 150px; border-radius: 22px; }
	body.dark-mode #roleCreateModal .modal-content { background: #1c2025; border-color: #475569; color: #f8fafc; }
	body.dark-mode #roleCreateModal .modal-header { border-bottom-color: #475569; }
	body.dark-mode #roleCreateModal label,
	body.dark-mode #roleCreateModal .modal-title { color: #e2e8f0; }
	body.dark-mode #roleCreateModal .form-control { background: #111827; border-color: #475569; color: #f8fafc; }
	body.dark-mode #roleCreateModal .form-control:focus { border-color: #69d9aa; box-shadow: 0 0 0 2px rgba(105, 217, 170, .18); }
	#roleCreateModal .modal-footer .btn-light { color: #344054; background: #f8fafc; border-color: #cbd5e1; }
	#roleCreateModal .modal-footer .btn-light:hover,
	#roleCreateModal .modal-footer .btn-light:focus { color: #fff; background: #2563eb; border-color: #2563eb; }
	body.dark-mode #roleCreateModal .modal-footer .btn-light { color: #e2e8f0; background: #242c38; border-color: #475569; }
	body.dark-mode #roleCreateModal .modal-footer .btn-light:hover,
	body.dark-mode #roleCreateModal .modal-footer .btn-light:focus { color: #ffffff !important; background: #334155 !important; border-color: #64748b !important; }
</style>
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Roles</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Roles</li>
	</ul>
</div>
<div class="col-sm-5 col">
	<button type="button" class="btn btn-primary float-right mt-2" data-toggle="modal" data-target="#roleCreateModal">Add Role</button>
</div>

@endpush

@section('content')

<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<div id="role-table" class="tabulator-table-wrap"></div>
				</div>
			</div>
		</div>
	</div>			
</div>

<div class="modal fade" id="roleCreateModal" tabindex="-1" role="dialog" aria-labelledby="roleCreateModalTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header"><h5 class="modal-title" id="roleCreateModalTitle">Add Role</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button></div>
			<form method="POST" action="{{ route('roles.store') }}">
				@csrf
				<div class="modal-body">
					<div class="form-group"><label for="createRoleName">Role</label><input type="text" name="role" id="createRoleName" class="form-control" placeholder="super-admin" required></div>
					<div class="form-group"><label for="createRolePermissions">Select Permissions</label><select class="select2 form-select form-control" name="permission[]" id="createRolePermissions" multiple required>@foreach ($permissions as $permission)<option value="{{ $permission->name }}">{{ $permission->name }}</option>@endforeach</select></div>
				</div>
				<div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div>
			</form>
		</div>
	</div>
</div>

@endsection


@push('page-js')
	<script>
		$(document).ready(function() {
            if (!window.PharmaTabulator) return;
            window.PharmaTabulator.server({
                el: 'role-table',
                url: "{{route('roles.index')}}",
                columns: [
                    {title: 'Name', field: 'name'},
                    {title: 'Permissions', field: 'permissions', formatter: 'html'},
                    {title: 'Actions', field: 'action', formatter: 'html', headerSort: false, searchable: false, width: 110, hozAlign: 'center'},
                ]
            });
		});
	</script>
	
@endpush
