<!-- Sidebar -->
<div class="sidebar" id="sidebar">
	<div class="sidebar-inner slimscroll" id="sidebar-scroll-container" data-turbo-permanent>
		<div id="sidebar-menu" class="sidebar-menu">
			
			<ul>
				<li class="menu-title"> 
					<span>Main</span>
				</li>
				<li class="{{ route_is('dashboard') ? 'active' : '' }}"> 
					<a href="{{route('dashboard')}}" data-sidebar-label="Dashboard"><i class="fe fe-home"></i> <span>Dashboard</span></a>
				</li>
				
				@can('view-category')
				<li class="{{ route_is('categories.*') ? 'active' : '' }}"> 
					<a href="{{route('categories.index')}}" data-sidebar-label="Categories"><i class="fe fe-layout"></i> <span>Categories</span></a>
				</li>
				@endcan
				
				@can('view-products')
				<li class="submenu">
					<a href="#" data-sidebar-label="Products"><i class="fe fe-document"></i> <span> Products</span> <span class="menu-arrow"></span></a>
					<ul style="display: none;">
						<li><a class="{{ route_is(('products.*')) ? 'active' : '' }}" href="{{route('products.index')}}">Products</a></li>
						<li><a class="{{ route_is('inventory-check.*') ? 'active' : '' }}" href="{{route('inventory-check.index')}}">Inventory Check</a></li>
		
						@can('view-outstock-products')<li><a class="{{ route_is('outstock') ? 'active' : '' }}" href="{{route('outstock')}}">Out-Stock</a></li>@endcan
						@can('view-expired-products')<li><a class="{{ route_is('expired') ? 'active' : '' }}" href="{{route('expired')}}">Expired</a></li>@endcan
					</ul>
				</li>
				@endcan
				
				@can('view-purchase')
				<li class="{{ route_is('purchases.*') ? 'active' : '' }}"> 
					<a href="{{route('purchases.index')}}" data-sidebar-label="Purchase"><i class="fe fe-star-o"></i> <span>Purchase</span></a>
				</li>
				@endcan
				@can('view-sales')
				<li class="submenu">
					<a href="#" data-sidebar-label="Sale"><i class="fe fe-activity"></i> <span> Sale</span> <span class="menu-arrow"></span></a>
					<ul style="display: none;">
					<li><a class="{{ route_is('pos.orders') ? 'active' : '' }}" href="{{route('pos.orders')}}">Add Sale</a></li>
					<li><a class="{{ route_is('sales.*') ? 'active' : '' }}" href="{{route('sales.index')}}">Sales</a></li>
					<li><a class="{{ route_is('prescriptions.*') ? 'active' : '' }}" href="{{route('prescriptions.index')}}">Prescriptions</a></li>
				</ul>
			</li>
			@endcan
				@can('view-supplier')
				<li class="{{ route_is('suppliers.*') ? 'active' : '' }}">
					<a href="{{route('suppliers.index')}}" data-sidebar-label="Supplier"><i class="fe fe-user"></i> <span>Supplier</span></a>
				</li>
				@endcan

				@can('view-reports')
				<li class="submenu">
					<a href="#" data-sidebar-label="Reports"><i class="fe fe-document"></i> <span> Reports</span> <span class="menu-arrow"></span></a>
					<ul style="display: none;">
						<li><a class="{{ route_is('reports.index') ? 'active' : '' }}" href="{{route('reports.index')}}">Report Dashboard</a></li>
						<li><a class="{{ route_is('audit.index') ? 'active' : '' }}" href="{{route('audit.index')}}">Audit Trail</a></li>
						<li><a class="{{ route_is('temperature.*') ? 'active' : '' }}" href="{{route('temperature.index')}}">Temperature Monitoring</a></li>
					</ul>
				</li>
				@endcan

				@can('view-access-control')
				<li class="submenu">
					<a href="#" data-sidebar-label="Access Control"><i class="fe fe-lock"></i> <span> Access Control</span> <span class="menu-arrow"></span></a>
					<ul style="display: none;">
						@can('view-permission')
						<li><a class="{{ route_is('permissions.index') ? 'active' : '' }}" href="{{route('permissions.index')}}">Permissions</a></li>
						@endcan
						@can('view-role')
						<li><a class="{{ route_is('roles.*') ? 'active' : '' }}" href="{{route('roles.index')}}">Roles</a></li>
						@endcan
					</ul>
				</li>
				@endcan

				@can('view-users')
				<li class="{{ route_is('users.*') ? 'active' : '' }}"> 
					<a href="{{route('users.index')}}" data-sidebar-label="Users"><i class="fe fe-users"></i> <span>Users</span></a>
				</li>
				@endcan
				
				<li class="{{ route_is('profile') ? 'active' : '' }}"> 
					<a href="{{route('profile')}}" data-sidebar-label="Profile"><i class="fe fe-user-plus"></i> <span>Profile</span></a>
				</li>
				<li class="submenu">
					<a href="#" data-sidebar-label="Backups"><i class="material-icons">backup</i> <span> Backups</span> <span class="menu-arrow"></span></a>
					<ul style="display: none;">
						<li><a class="{{ route_is('backup.index') ? 'active' : '' }}" href="{{route('backup.index')}}">Backups</a></li>
						<li><a class="{{ route_is('archive.index') ? 'active' : '' }}" href="{{route('archive.index')}}">Archive</a></li>
					</ul>
				</li>
				@can('view-settings')
				<li class="{{ route_is('settings') ? 'active' : '' }}"> 
					<a href="{{route('settings')}}" data-sidebar-label="Settings">
						<i class="material-icons">settings</i>
						 <span> Settings</span>
					</a>
				</li>
				@endcan
			</ul>
		</div>
	</div>
</div>
<!-- /Sidebar -->
