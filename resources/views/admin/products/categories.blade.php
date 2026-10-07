@extends('admin.layouts.app')

<x-assets.tabulator />

@push('page-css')
<style>
/* Keep category data inside the table so it cannot resize the shared header. */
.datatable.table {
	table-layout: fixed;
	width: 100% !important;
}
.table-responsive {
	max-width: 100%;
	overflow-x: auto;
}

/* Keep the single action menu compact. */
#category-table th.actions, #category-table td.actions {
	width: 90px;
	min-width: 90px;
	box-sizing: border-box;
	text-align: center;
}
	/* Keep the Categories action shadow compact and readable. */
	.category-action-button,
	.category-action-button:hover,
	.category-action-button:focus,
	.category-action-button.show {
		box-shadow: 0 3px 2px -2px rgba(96, 165, 250, .8), 0 4px 3px -2px rgba(96, 165, 250, .8) !important;
	}
	.category-action-button:hover,
	.category-action-button:focus,
	.category-action-button.show {
		box-shadow: 0 3px 2px -2px rgba(59, 130, 246, .9), 0 4px 3px -2px rgba(59, 130, 246, .9) !important;
	}
	body.dark-mode .category-action-button,
	body.dark-mode .category-action-button:hover,
	body.dark-mode .category-action-button:focus,
	body.dark-mode .category-action-button.show {
		box-shadow: 0 3px 2px -2px rgba(134, 239, 172, .8), 0 4px 3px -2px rgba(134, 239, 172, .8) !important;
	}
	body.dark-mode .category-action-button:hover,
	body.dark-mode .category-action-button:focus,
	body.dark-mode .category-action-button.show {
		box-shadow: 0 3px 2px -2px rgba(52, 211, 153, .9), 0 4px 3px -2px rgba(52, 211, 153, .9) !important;
	}
	.category-action-modal .modal-dialog {
		max-width: 420px;
		width: min(420px, calc(100vw - 32px));
	}
	.category-action-modal .modal-body {
		max-height: min(65vh, 480px);
		overflow-y: auto;
		padding: 8px 0;
	}
	.category-action-modal .category-action-heading {
		min-width: 0;
	}
	.category-action-modal .category-action-name {
		color: #64748b;
		display: block;
		font-size: 13px;
		font-weight: 500;
		margin-top: 4px;
		overflow-wrap: anywhere;
	}
	.category-action-modal .dropdown-item {
		padding: 10px 20px;
		white-space: normal;
	}
	.category-action-modal .dropdown-item:hover,
	.category-action-modal .dropdown-item:focus {
		background-color: #dbeafe !important;
		color: #1e3a8a !important;
	}
	.category-action-modal .dropdown-item.text-danger:hover,
	.category-action-modal .dropdown-item.text-danger:focus {
		color: #b91c1c !important;
	}
	body.dark-mode .category-action-modal .modal-content {
		background: #252b33;
		color: #e2e8f0;
	}
	body.dark-mode .category-action-modal .dropdown-item:hover,
	body.dark-mode .category-action-modal .dropdown-item:focus {
		background-color: #bbf7d0 !important;
		color: #14532d !important;
	}
	body.dark-mode .category-action-modal .dropdown-item.text-danger:hover,
	body.dark-mode .category-action-modal .dropdown-item.text-danger:focus {
		color: #b91c1c !important;
	}
	body.dark-mode .category-action-modal .modal-header {
		border-color: #475569;
	}
	body.dark-mode .category-action-modal .close {
		color: #e2e8f0;
		text-shadow: none;
	}
	body.dark-mode .category-action-modal .category-action-name {
		color: #a8b3c4;
	}


.category-expiry-switch {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 1rem;
}
.category-expiry-switch input {
	appearance: none;
	-webkit-appearance: none;
	position: relative;
	width: 2.5rem;
	height: 1.35rem;
	margin: 0;
	border: 0;
	border-radius: 999px;
	background: #cbd5e1;
	box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .12);
	cursor: pointer;
	transition: background-color .2s ease, box-shadow .2s ease;
}
.category-expiry-switch input::after {
	content: '';
	position: absolute;
	width: 1rem;
	height: 1rem;
	left: .18rem;
	top: .175rem;
	border-radius: 50%;
	background: #fff;
	box-shadow: 0 1px 3px rgba(15, 23, 42, .3);
	transition: transform .2s ease;
}
.category-expiry-switch input:checked {
	background: #76a7f7;
	box-shadow: inset 0 0 0 1px rgba(76, 144, 255, .25);
}
.category-expiry-switch input:checked::after {
	transform: translateX(1.14rem);
}
.category-expiry-switch input:focus-visible {
	outline: 2px solid rgba(76, 144, 255, .45);
	outline-offset: 2px;
}
</style>
@endpush

@push('page-header')
<div class="col-sm-7 col-auto">
	<h3 class="page-title">Categories</h3>
	<ul class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{route('dashboard')}}">Dashboard</a></li>
		<li class="breadcrumb-item active">Categories</li>
	</ul>
</div>
<div class="col-sm-5 col">
	<a href="#add_categories" data-toggle="modal" class="btn btn-primary float-right mt-2">Add Category</a>
</div>
@endpush

@section('content')
<div class="row">
	<div class="col-sm-12">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<div id="category-table" class="tabulator-table-wrap"></div>
				</div>
			</div>
		</div>
	</div>			
</div>

	<div class="modal fade product-row-action-modal category-action-modal" id="category-action-modal" tabindex="-1" role="dialog" aria-labelledby="category-action-modal-title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<div class="row-action-heading">
						<h5 class="modal-title" id="category-action-modal-title">Categories Actions</h5>
						<span class="row-action-meta">Name &rarr; <span id="category-action-name"></span></span>
					</div>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div id="category-action-modal-content"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- Add Modal -->
<div class="modal fade" id="add_categories" aria-hidden="true" role="dialog">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Add Category</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
					<form id="add-category-form" method="POST" action="{{route('categories.store')}}">
					@csrf
					<div class="row form-row">
						<div class="col-12">
							<div class="form-group">
									<label>Recommended</label>
									<select id="recommended_category" class="form-control">
										<option value="">Select a recommended category</option>
										<option value="medicines" data-name="Medicines" data-description="Prescription Medicines, Over-the-Counter (OTC) Medicines, Generic Medicines, Branded Medicines, Pediatric Medicines, Topical Medicines, etc." data-fixed-key="medicines">Medicines</option>
										<option value="vitamins-supplements" data-name="Vitamins & Supplements" data-description="Vitamins, Minerals, Food Supplements, Herbal Supplements, Nutritional Supplements, etc." data-fixed-key="vitamins-supplements">Vitamins & Supplements</option>
										<option value="medical-supplies" data-name="Medical Supplies" data-description="Bandages, Gauze, Cotton, Syringes, Medical Tape, Gloves, Masks, Thermometers, etc." data-fixed-key="medical-supplies">Medical Supplies</option>
										<option value="personal-care-hygiene" data-name="Personal Care & Hygiene" data-description="Soap, Shampoo, Conditioner, Deodorant, Hand Sanitizer, Wet Wipes, Tissues, Feminine Hygiene Products, etc." data-fixed-key="personal-care-hygiene">Personal Care & Hygiene</option>
										<option value="dental-care" data-name="Dental Care" data-description="Toothbrushes, Toothpaste, Mouthwash, Dental Floss, Denture Care Products, etc." data-fixed-key="dental-care">Dental Care</option>
										<option value="baby-mother-care" data-name="Baby & Mother Care" data-description="Baby Diapers, Baby Wipes, Baby Formula, Baby Bottles, Pacifiers, Baby Soap, Baby Shampoo, Maternity Products, etc." data-fixed-key="baby-mother-care">Baby & Mother Care</option>
										<option value="skin-hair-care" data-name="Skin & Hair Care" data-description="Facial Care Products, Moisturizers, Sunscreen, Lotions, Acne Care, Hair Care Products, etc." data-fixed-key="skin-hair-care">Skin & Hair Care</option>
										<option value="first-aid" data-name="First Aid" data-description="First Aid Kits, Antiseptics, Alcohol, Wound Care Products, Burn Care Products, etc." data-fixed-key="first-aid">First Aid</option>
										<option value="medical-devices" data-name="Medical Devices" data-description="Blood Pressure Monitors, Blood Glucose Meters, Nebulizers, Pulse Oximeters, Medical Scales, etc." data-fixed-key="medical-devices" data-no-expiry="1" data-no-expiry-fixed="1">Medical Devices</option>
										<option value="food-beverages" data-name="Food & Beverages" data-description="Bottled Water, Biscuits, Snacks, Health Drinks, Nutritional Drinks, etc." data-fixed-key="food-beverages">Food & Beverages</option>
										<option value="household-other" data-name="Household & Other" data-description="Disinfectants, Cleaning Products, Insect Repellents, Household Items, and products that do not fit another category." data-fixed-key="household-other">Household & Other</option>
									</select>
								</div>
								<div class="form-group">
									<label>Category</label>
									<input type="text" name="name" class="form-control category-name-input">
								</div>
								<div class="form-group">
									<label>Description</label>
									<textarea name="description" class="form-control category-description-input" rows="3"></textarea>
									<input type="hidden" name="fixed_key" id="fixed_key" class="fixed_key_input">
									<label class="category-expiry-switch mt-3">
										<span>No expiry for products in this category</span>
										<input type="checkbox" name="no_expiry" value="1">
									</label>
								</div>
							</div>
						</div>
					</div>
					<button type="submit" form="add-category-form" class="btn btn-primary btn-block">Save Changes</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- /ADD Modal -->

<!-- Edit Details Modal -->
<div class="modal fade" id="edit_category" aria-hidden="true" role="dialog">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Edit Category</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form method="post" action="{{route('categories.update')}}">
					@csrf
					@method("PUT")
					<div class="row form-row">
						<div class="col-12">
							<input type="hidden" name="id" id="edit_id">
							<div class="form-group">
								<label>Recommended</label>
								<select id="recommended_category_edit" class="form-control">
									<option value="">Select a recommended category</option>
									<option value="medicines" data-name="Medicines" data-description="Prescription Medicines, Over-the-Counter (OTC) Medicines, Generic Medicines, Branded Medicines, Pediatric Medicines, Topical Medicines, etc." data-fixed-key="medicines">Medicines</option>
									<option value="vitamins-supplements" data-name="Vitamins & Supplements" data-description="Vitamins, Minerals, Food Supplements, Herbal Supplements, Nutritional Supplements, etc." data-fixed-key="vitamins-supplements">Vitamins & Supplements</option>
									<option value="medical-supplies" data-name="Medical Supplies" data-description="Bandages, Gauze, Cotton, Syringes, Medical Tape, Gloves, Masks, Thermometers, etc." data-fixed-key="medical-supplies">Medical Supplies</option>
									<option value="personal-care-hygiene" data-name="Personal Care & Hygiene" data-description="Soap, Shampoo, Conditioner, Deodorant, Hand Sanitizer, Wet Wipes, Tissues, Feminine Hygiene Products, etc." data-fixed-key="personal-care-hygiene">Personal Care & Hygiene</option>
									<option value="dental-care" data-name="Dental Care" data-description="Toothbrushes, Toothpaste, Mouthwash, Dental Floss, Denture Care Products, etc." data-fixed-key="dental-care">Dental Care</option>
									<option value="baby-mother-care" data-name="Baby & Mother Care" data-description="Baby Diapers, Baby Wipes, Baby Formula, Baby Bottles, Pacifiers, Baby Soap, Baby Shampoo, Maternity Products, etc." data-fixed-key="baby-mother-care">Baby & Mother Care</option>
									<option value="skin-hair-care" data-name="Skin & Hair Care" data-description="Facial Care Products, Moisturizers, Sunscreen, Lotions, Acne Care, Hair Care Products, etc." data-fixed-key="skin-hair-care">Skin & Hair Care</option>
									<option value="first-aid" data-name="First Aid" data-description="First Aid Kits, Antiseptics, Alcohol, Wound Care Products, Burn Care Products, etc." data-fixed-key="first-aid">First Aid</option>
									<option value="medical-devices" data-name="Medical Devices" data-description="Blood Pressure Monitors, Blood Glucose Meters, Nebulizers, Pulse Oximeters, Medical Scales, etc." data-fixed-key="medical-devices" data-no-expiry="1" data-no-expiry-fixed="1">Medical Devices</option>
									<option value="food-beverages" data-name="Food & Beverages" data-description="Bottled Water, Biscuits, Snacks, Health Drinks, Nutritional Drinks, etc." data-fixed-key="food-beverages">Food & Beverages</option>
									<option value="household-other" data-name="Household & Other" data-description="Disinfectants, Cleaning Products, Insect Repellents, Household Items, and products that do not fit another category." data-fixed-key="household-other">Household & Other</option>
								</select>
							</div>
							<div class="form-group">
								<label>Category</label>
								<input type="text" class="form-control edit_name" name="name">
							</div>
							<div class="form-group">
								<label>Description</label>
								<textarea class="form-control edit_description" name="description" rows="3"></textarea>
								<input type="hidden" name="fixed_key" id="edit_fixed_key">
								<label class="category-expiry-switch mt-3">
									<span>No expiry for products in this category</span>
									<input type="checkbox" name="no_expiry" class="edit_no_expiry" value="1">
								</label>
							</div>
							</div>
						</div>
						
					</div>
					<button type="submit" class="btn btn-primary btn-block">Save Changes</button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- /Edit Details Modal -->

@push('page-js')
<script>
    function escapeHtml(text) {
        return String(text || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    window.pharmacyCategoriesInit = function() {
		var categoryTable = document.getElementById('category-table');
		if (!categoryTable || categoryTable.dataset.pharmacyCategoriesInitialized === 'true') {
			return;
		}

		if (!window.PharmaTabulator) {
			return;
		}

		var existingFixedKeys = @json($existingFixedKeys);
		$('#recommended_category option[data-fixed-key], #recommended_category_edit option[data-fixed-key]').filter(function () {
			return existingFixedKeys.indexOf($(this).attr('data-fixed-key')) !== -1;
		}).remove();

		window.PharmaTabulator.server({
			el: 'category-table',
			url: "{{route('categories.index')}}",
			columns: [
				{ title: 'Name', field: 'name' },
				{ title: 'Created date', field: 'created_at' },
				{ title: 'Actions', field: 'action', formatter: 'html', headerSort: false, searchable: false, width: 120, hozAlign: 'center' }
			]
		});
		categoryTable.dataset.pharmacyCategoriesInitialized = 'true';

		var returnToCategoryActions = false;

		function showCategoryModalAfterActions(modal) {
			var actionModal = $('#category-action-modal');
			returnToCategoryActions = true;
			if (actionModal.hasClass('show')) {
				actionModal.one('hidden.bs.modal.categorySwitch', function () {
					window.setTimeout(function () {
						$(modal).modal('show');
					}, 50);
				}).modal('hide');
				return;
			}
			$(modal).modal('show');
		}

		$('#categoryDescriptionModal').off('hidden.bs.modal.categoryReturn').on('hidden.bs.modal.categoryReturn', function () {
			if (!returnToCategoryActions) return;
			returnToCategoryActions = false;
			window.setTimeout(function () {
				$('#category-action-modal').modal('show');
			}, 50);
		});

		$('#edit_category').off('hidden.bs.modal.categoryReturn').on('hidden.bs.modal.categoryReturn', function () {
			if (!returnToCategoryActions) return;
			returnToCategoryActions = false;
			window.setTimeout(function () {
				$('#category-action-modal').modal('show');
			}, 50);
		});

		$('#edit_category form').off('submit.categoryReturn').on('submit.categoryReturn', function () {
			returnToCategoryActions = false;
		});

		$(document).off('click.pharmacyCategories', '#category-table .category-action-button')
			.on('click.pharmacyCategories', '#category-table .category-action-button', function (event) {
				event.preventDefault();
				var menu = this.nextElementSibling;
				if (!menu || !menu.classList.contains('dropdown-menu')) {
					return;
				}

				this.setAttribute('aria-expanded', 'true');
				$('#category-action-name').text(this.getAttribute('data-category-name') || '');
				$('#category-action-modal-content').html(menu.innerHTML);
				$('#category-action-modal').modal('show');
			});

		$('#category-action-modal').off('hidden.bs.modal.pharmacyCategories').on('hidden.bs.modal.pharmacyCategories', function () {
			if (!returnToCategoryActions) {
				$('#category-table .category-action-button[aria-expanded="true"]').attr('aria-expanded', 'false');
				$('#category-action-modal-content').empty();
			}
		});

		$(document).off('click.pharmacyCategories', '#category-table .editbtn, #category-action-modal .editbtn').on('click.pharmacyCategories', '#category-table .editbtn, #category-action-modal .editbtn', function (event) {
			event.preventDefault();
			var id = $(this).data('id');
			var name = $(this).data('name');
			var description = $(this).data('description') || '';
			var fixedKey = $(this).data('fixed-key') || '';
			var noExpiry = $(this).data('no-expiry') === 1 || $(this).data('no-expiry') === '1';

			$('#edit_id').val(id);
			$('.edit_name').val(name);
			$('.edit_description').val(description);
			$('#edit_fixed_key').val(fixedKey);
			$('.edit_no_expiry').prop('checked', noExpiry).prop('disabled', fixedKey === 'medical-devices');
			$('#recommended_category_edit').val(fixedKey);

			showCategoryModalAfterActions('#edit_category');
		});

		$('#recommended_category').on('change', function () {
			var selected = $(this).find('option:selected');
			$('.category-name-input').val(selected.data('name') || '');
			$('.category-description-input').val(selected.data('description') || '');
			$('#fixed_key').val(selected.data('fixed-key') || '');
			var noExpiry = selected.data('no-expiry') === 1 || selected.data('no-expiry') === '1';
			$('input[name="no_expiry"]', '#add-category-form').prop('checked', noExpiry).prop('disabled', selected.data('no-expiry-fixed') === 1 || selected.data('no-expiry-fixed') === '1');
		});

		$('#recommended_category_edit').on('change', function () {
			var selected = $(this).find('option:selected');
			$('.edit_name').val(selected.data('name') || '');
			$('.edit_description').val(selected.data('description') || '');
			$('#edit_fixed_key').val(selected.data('fixed-key') || '');
			var noExpiry = selected.data('no-expiry') === 1 || selected.data('no-expiry') === '1';
			$('.edit_no_expiry').prop('checked', noExpiry).prop('disabled', selected.data('no-expiry-fixed') === 1 || selected.data('no-expiry-fixed') === '1');
		});

		$(document).off('click.pharmacyCategories', '#category-table .category-description-btn, #category-action-modal .category-description-btn').on('click.pharmacyCategories', '#category-table .category-description-btn, #category-action-modal .category-description-btn', function (event) {
			event.preventDefault();
			var description = $(this).data('description') || '';
			$('#category-description-text').text(description);
			showCategoryModalAfterActions('#categoryDescriptionModal');
		});

	};

	if (!window.pharmacyCategoriesTurboLoadHandler) {
		window.pharmacyCategoriesTurboLoadHandler = function() {
			if (window.pharmacyCategoriesInit) {
				window.pharmacyCategoriesInit();
			}
		};
		document.addEventListener('turbo:load', window.pharmacyCategoriesTurboLoadHandler);
	}
	window.pharmacyCategoriesInit();
</script>
@endpush

<div class="modal fade" id="categoryDescriptionModal" tabindex="-1" role="dialog" aria-labelledby="categoryDescriptionModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="categoryDescriptionModalLabel">Category Description</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p id="category-description-text" class="mb-0"></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

@endsection
