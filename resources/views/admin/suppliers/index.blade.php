@extends('admin.layouts.app')

<x-assets.tabulator />

@push('page-css')
    
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Supplier</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Supplier</li>
	</ul>
</div>
<div class="col-sm-5 col">
	@can('create-supplier')
	<button type="button" class="btn btn-primary float-right mt-2" data-toggle="modal" data-target="#addSupplierModal">Add New</button>
	@endcan
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-md-12">
	
		<!-- Suppliers -->
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<div id="supplier-table" class="tabulator-table-wrap"></div>
					<div class="modal fade" id="supplierDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
						<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
							<div class="modal-content">
								<div class="modal-header"><h5 class="modal-title">Supplier Products</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
								<div class="modal-body"><p><strong>Supplier:</strong> <span class="supplier-detail-name"></span></p><div class="table-responsive"><table class="table table-bordered supplier-products-detail"><thead><tr><th>Product</th><th>Category</th><th>Quantity</th><th>Cost</th><th>Expire Date</th><th>Date of Purchase</th></tr></thead><tbody></tbody></table></div></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /Suppliers-->
		
	</div>
</div>

<div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="addSupplierModalLabel">Add Supplier</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body custom-edit-service">
				<form method="post" enctype="multipart/form-data" action="{{route('suppliers.store')}}" id="add-supplier-form">
					@csrf
					<input type="hidden" name="form_submit" value="next">
					<input type="hidden" name="from_suppliers_modal" value="1">
					@include('admin.suppliers._form')
					<div class="submit-section text-center">
						<button class="btn btn-primary submit-btn" type="submit" id="add-supplier-next">Next</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection	

@push('page-js')
<script>
	function escapeHtml(text) {
		return $('<div>').text(text == null ? '' : text).html();
	}

    $(document).ready(function() {
        if (window.PharmaTabulator) {
            window.PharmaTabulator.server({
                el: 'supplier-table',
                url: "{{route('suppliers.index')}}",
                columns: [
                    {title: 'Name', field: 'name'},
                    {title: 'Phone', field: 'phone'},
                    {title: 'Email', field: 'email'},
                    {title: 'Address', field: 'address'},
                    {title: 'Company', field: 'company'},
                    {title: 'Action', field: 'action', formatter: 'html', headerSort: false, searchable: false, width: 110, hozAlign: 'center'},
                ]
            });
        }

		$(document).on('click', '.supplier-detail-btn', function () {
			var button = $(this);
			var rows = JSON.parse(button.attr('data-products') || '[]');
			$('.supplier-detail-name').text(button.attr('data-supplier'));
			var body = $('.supplier-products-detail tbody').empty();
			if (!rows.length) body.append('<tr><td colspan="6">No products recorded.</td></tr>');
			rows.forEach(function (row) {
				body.append('<tr><td>'+escapeHtml(row.product)+'</td><td>'+escapeHtml(row.category || '')+'</td><td>'+escapeHtml(row.quantity)+'</td><td>'+escapeHtml(row.cost)+'</td><td>'+escapeHtml(row.expiry || '')+'</td><td>'+escapeHtml(row.submitted || '')+'</td></tr>');
			});
			$('#supplierDetailsModal').modal('show');
		});

		// Re-open the Add Supplier modal if the last submit failed validation
		var addSupplierModal = $('#addSupplierModal');
		var addSupplierForm = $('#add-supplier-form');
		if (addSupplierForm.find('.supplier-validation-alert').length || {{ session('open_add_supplier_modal') ? 'true' : 'false' }}) {
			addSupplierModal.modal('show');
		}
		addSupplierModal.on('hidden.bs.modal', function () {
			// Clear stale validation errors / old input display state on close
			addSupplierForm.find('.supplier-validation-alert').remove();
		});
        
    });
</script> 
@endpush