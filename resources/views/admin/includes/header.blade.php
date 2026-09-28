<!-- Header -->
<div class="header" id="admin-header" data-turbo-permanent>
			
	<!-- Logo -->
	<div class="header-left">
		@php
			$logoPath = AppSettings::get('logo');
			$customLogo = !empty($logoPath)
				? asset('storage/' . $logoPath) . (file_exists(public_path('storage/' . $logoPath)) ? '?v=' . filemtime(public_path('storage/' . $logoPath)) : '')
				: null;
			$lightLogo = asset('assets/img/logo.png');
			$darkLogo = asset('assets/img/logo-white.png');
			$logoUrl = $customLogo ?: $lightLogo;
		@endphp
		<a href="{{route('dashboard')}}" class="logo">
			<img id="site-logo" src="{{ $logoUrl }}" alt="Logo" width="180" height="50" data-custom-logo="{{ $customLogo }}" data-light-logo="{{ $lightLogo }}" data-dark-logo="{{ $darkLogo }}">
		</a>
		<a href="{{route('dashboard')}}" class="logo logo-small">
			<img src="{{asset('assets/img/logo-small.png')}}" alt="Logo" width="30" height="30">
		</a>
	</div>
	<!-- /Logo -->
	
	<a href="javascript:void(0);" id="toggle_btn" aria-label="Toggle sidebar" aria-expanded="true">
		<span class="sidebar-toggle-icon" aria-hidden="true">
			<span></span>
			<span></span>
			<span></span>
		</span>
	</a>

	<button type="button" id="dashboardThemeToggle" class="theme-switch-btn" role="switch" aria-checked="false" aria-label="Switch to dark mode">
		<svg class="theme-switch-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
			<defs>
				<mask id="dashboardThemeMoonMask">
					<rect width="24" height="24" fill="white" />
					<circle class="theme-switch-mask" cx="24" cy="0" r="9" fill="black" />
				</mask>
			</defs>
			<circle class="theme-switch-sun" cx="12" cy="12" r="5" mask="url(#dashboardThemeMoonMask)" />
			<g class="theme-switch-rays" stroke="currentColor">
				<line x1="12" y1="1" x2="12" y2="3" />
				<line x1="12" y1="21" x2="12" y2="23" />
				<line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
				<line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
				<line x1="1" y1="12" x2="3" y2="12" />
				<line x1="21" y1="12" x2="23" y2="12" />
				<line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
				<line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
			</g>
		</svg>
	</button>
	
	<!-- Mobile Menu Toggle -->
	<a class="mobile_btn" id="mobile_btn">
		<i class="fa fa-bars"></i>
	</a>
	<!-- /Mobile Menu Toggle -->
	
	<!-- Header Right Menu -->
	<ul class="nav user-menu">
		<li class="nav-item dropdown">
			<a href="#" data-target="#add_sales" title="make a sale" data-toggle="modal" data-turbo="false" class="nav-link">
				<i class="fas fa-clipboard"></i>
			</a>
		</li>
		<!-- Notifications -->
		@php
			$unreadNotifications = auth()->user()->unReadNotifications;
			$unreadNotificationCount = $unreadNotifications->count();
		@endphp
		<li class="nav-item dropdown noti-dropdown">
			
			<a href="#" class="dropdown-toggle nav-link notification-bell-link" data-toggle="dropdown" aria-label="Notifications{{ $unreadNotificationCount ? ', '.$unreadNotificationCount.' unread' : '' }}">
				<i class="fe fe-bell" aria-hidden="true"></i>
				@if($unreadNotificationCount > 0)
					<span class="badge badge-pill notification-count" aria-hidden="true">{{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}</span>
				@endif
			</a>
			<div class="dropdown-menu notifications">
				<div class="topnav-dropdown-header">
					<span class="notification-title">Notifications</span>
					@if($unreadNotificationCount > 0)
						<a href="{{route('mark-as-read')}}" class="clear-noti">Mark All As Read</a>
					@else
						<span class="notification-empty-state-label">All caught up</span>
					@endif
				</div>
				<div class="noti-content">
					<ul class="notification-list">
						@forelse ($unreadNotifications as $notification)
							<li class="notification-message">
								<a href="{{route('read')}}">
									<div class="media">
										<span class="avatar avatar-sm">
											@php
												$notificationImage = $notification->data['image'] ?? null;
													$imagePath = $notificationImage ? storage_path('app/system/purchases/'.$notificationImage) : null;
													if ($notificationImage && !file_exists($imagePath)) $imagePath = storage_path('app/purchases/'.$notificationImage);
											@endphp
											@if(!empty($notificationImage) && file_exists($imagePath))
													<img class="avatar-img rounded-circle" alt="Product image" src="{{ url('storage/system/purchases/'.$notificationImage) }}">
											@else
												<span class="avatar-title rounded-circle bg-light text-muted">
													<i class="fe fe-box"></i>
												</span>
											@endif
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
						@empty
							<li class="notification-empty">No new notifications</li>
						@endforelse
					</ul>
				</div>
				@if($unreadNotificationCount > 0)
					<div class="topnav-dropdown-footer">
						<a href="#">View all Notifications</a>
					</div>
				@endif
			</div>
		</li>
		<!-- /Notifications -->
		
		<!-- User Menu -->
		@php
			$currentUserAvatar = auth()->user()->hasRole('super-admin')
				? asset('assets/img/ADMIN.jpg')
					: (!empty(auth()->user()->avatar)
						? (in_array(auth()->user()->avatar, ['femaleperson.jpg', 'maleperson.jpg'], true) ? asset('assets/img/'.auth()->user()->avatar) : url('storage/system/profiles/'.auth()->user()->avatar))
						: (in_array(strtolower((string) auth()->user()->gender), ['male', 'female'], true) ? asset('assets/img/'.strtolower(auth()->user()->gender).'person.jpg') : asset('assets/img/avatar.png')));
		@endphp
		<li class="nav-item dropdown has-arrow">
			<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
				<span class="user-img"><img class="rounded-circle" src="{{$currentUserAvatar}}" width="31" alt="avatar"></span>
			</a>
			<div class="dropdown-menu">
				<div class="user-header">
					<div class="avatar avatar-sm">
						<img src="{{$currentUserAvatar}}" alt="User Image" class="avatar-img rounded-circle">
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

<script>
	(function () {
		var root = document.documentElement;
		var isDark = root.classList.contains('dark-mode');
		var logo = document.getElementById('site-logo');
		var toggle = document.getElementById('dashboardThemeToggle');

		if (logo && isDark) {
			logo.src = logo.getAttribute('data-dark-logo') || logo.src;
		}
		if (toggle) {
			toggle.classList.toggle('is-dark', isDark);
			toggle.setAttribute('aria-checked', isDark ? 'true' : 'false');
			toggle.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
		}
	})();
</script>