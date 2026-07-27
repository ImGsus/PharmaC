<!-- Header -->
<div class="header">
			
	<!-- Logo -->
	<div class="header-left">
		@php
			$logoPath = AppSettings::get('logo');
			$logoUrl = !empty($logoPath)
				? asset('storage/' . $logoPath) . (file_exists(public_path('storage/' . $logoPath)) ? '?v=' . filemtime(public_path('storage/' . $logoPath)) : '')
				: asset('assets/img/logo-white.png');
		@endphp
		<a href="{{route('dashboard')}}" class="logo">
			<img src="{{ $logoUrl }}" alt="Logo">
		</a>
		<a href="{{route('dashboard')}}" class="logo logo-small">
			<img src="{{asset('assets/img/logo-small.png')}}" alt="Logo" width="30" height="30">
		</a>
	</div>
	<!-- /Logo -->
	
	<a href="javascript:void(0);" id="toggle_btn">
		<i class="fe fe-text-align-left"></i>
	</a>

	<button type="button" id="dashboardThemeToggle" class="theme-switch-btn" aria-label="Toggle night mode">
		<span class="toggle-icon">🌙</span>
		<span class="toggle-text">Night Mode</span>
	</button>
	
	<!-- Mobile Menu Toggle -->
	<a class="mobile_btn" id="mobile_btn">
		<i class="fa fa-bars"></i>
	</a>
	<!-- /Mobile Menu Toggle -->
	
	<!-- Header Right Menu -->
	<ul class="nav user-menu">
		<li class="nav-item d-none d-sm-flex align-items-center me-3">
			<span class="nav-link p-0 text-muted" id="header-current-datetime">Loading current time...</span>
		</li>
		<li class="nav-item dropdown">
			<a href="#" data-target="#add_sales" title="make a sale" data-toggle="modal" class="dropdown-toggle nav-link">
				<i class="fas fa-clipboard"></i>
			</a>
		</li>
		<!-- Notifications -->
		<li class="nav-item dropdown noti-dropdown">
			
			<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
				<i class="fe fe-bell"></i> <span class="badge badge-pill">{{auth()->user()->unReadNotifications->count()}}</span>
			</a>
			<div class="dropdown-menu notifications">
				<div class="topnav-dropdown-header">
					<span class="notification-title">Notifications</span>
					<a href="{{route('mark-as-read')}}" class="clear-noti">Mark All As Read </a>
				</div>
				<div class="noti-content">
					<ul class="notification-list">
						@foreach (auth()->user()->unReadNotifications as $notification)
							<li class="notification-message">
								<a href="{{route('read')}}">
									<div class="media">
										<span class="avatar avatar-sm">
											<img class="avatar-img rounded-circle" alt="Product image" src="{{asset('storage/purchases/'.$notification['image'])}}">
										</span>
										<div class="media-body">
											<h6 class="text-danger">Stock Alert</h6>
											<p class="noti-details">
										@if(isset($notification->data['status']) && $notification->data['status'] === 'out_of_stock')
											<span class="noti-title">{{$notification->data['product_name']}} is out of stock.</span>
										@else
											<span class="noti-title">{{$notification->data['product_name']}} is low on stock ({{$notification->data['quantity']}} left).</span>
										@endif
										<span>Please update the purchase quantity.</span>
											<p class="noti-time"><span class="notification-time">{{$notification->created_at->diffForHumans()}}</span></p>
										</div>
									</div>
								</a>
							</li>
						@endforeach						
					</ul>
				</div>
				<div class="topnav-dropdown-footer">
					<a href="#">View all Notifications</a>
				</div>
			</div>
		</li>
		<!-- /Notifications -->
		
		<!-- User Menu -->
		<li class="nav-item dropdown has-arrow">
			<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
				<span class="user-img"><img class="rounded-circle" src="{{!empty(auth()->user()->avatar) ? asset('storage/users/'.auth()->user()->avatar): asset('assets/img/avatar.png')}}" width="31" alt="avatar"></span>
			</a>
			<div class="dropdown-menu">
				<div class="user-header">
					<div class="avatar avatar-sm">
						<img src="{{!empty(auth()->user()->avatar) ? asset('storage/users/'.auth()->user()->avatar): asset('assets/img/avatar.png')}}" alt="User Image" class="avatar-img rounded-circle">
					</div>
					<div class="user-text">
						<h6>{{auth()->user()->name}}</h6>
					</div>
				</div>
				
				<a class="dropdown-item" href="{{route('profile')}}">My Profile</a>
				@can('view-settings')<a class="dropdown-item" href="{{route('settings')}}">Settings</a>@endcan
				
				<form action="{{route('logout')}}" method="post" class="dropdown-item p-0 m-0">
					@csrf
					<button type="submit" class="btn btn-link text-start w-100">Logout</button>
				</form>
			</div>
		</li>
		<!-- /User Menu -->
		
	</ul>
	<!-- /Header Right Menu -->
	
</div>
<!-- /Header -->