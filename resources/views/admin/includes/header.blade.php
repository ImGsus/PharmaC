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
		@can('view-products')
		<li class="nav-item">
			<a href="#" data-target="#inventory-check-modal" title="Inventory Check" aria-label="Open Inventory Check" data-toggle="modal" data-turbo="false" class="nav-link inventory-check-trigger">
				<i class="fas fa-clipboard-check" aria-hidden="true"></i>
			</a>
		</li>
		@endcan
		<!-- Notifications -->
		@php
			$unreadNotifications = auth()->user()->unReadNotifications;
			$unreadNotificationCount = $unreadNotifications->count();
			// Show recent 8 notifications (read + unread) in the dropdown
			$recentNotifications = auth()->user()->notifications()->latest()->limit(8)->get();
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
						@forelse ($recentNotifications as $notification)
							@php $isUnreadItem = is_null($notification->read_at); @endphp
							@php
								$notiData = $notification->data ?? [];
								$notiType = $notiData['type'] ?? '';
								$isExpiringSoonNoti = $notiType === 'expiring_soon';
								$isExpiredNoti = ($notiType === 'expired') || (!$isExpiringSoonNoti && $notification->type === 'App\Notifications\ProductExpiryNotification');
								$notificationImage = $notiData['image'] ?? null;
								$imagePath = $notificationImage ? storage_path('app/system/purchases/'.$notificationImage) : null;
								if ($notificationImage && !file_exists($imagePath)) $imagePath = storage_path('app/purchases/'.$notificationImage);
								$hasValidImage = !empty($notificationImage) && file_exists($imagePath);
								$readOneUrl = '/notification/read/' . $notification->id; // relative path — works on any host/IP
							@endphp
							<li class="notification-message noti-item-row {{ $isUnreadItem ? 'noti-item-unread' : '' }}" data-id="{{ $notification->id }}">
								<div class="d-flex align-items-start p-2 position-relative noti-card-wrapper {{ $isExpiredNoti ? 'noti-expired-card' : ($isExpiringSoonNoti ? 'noti-soon-card' : '') }}">
									@if($isUnreadItem)<span class="noti-unread-dot"></span>@endif
									<a href="{{ $readOneUrl }}" class="d-flex flex-grow-1 text-reset text-decoration-none mr-2" data-turbo="false">
										<div class="media align-items-center w-100">
											<span class="avatar avatar-sm mr-2 flex-shrink-0">
												@if($hasValidImage)
													<img class="avatar-img rounded-circle" alt="Product image" src="{{ url('storage/system/purchases/'.$notificationImage) }}">
												@elseif($isExpiredNoti)
													<span class="avatar-title rounded-circle" style="background-color: rgba(239,68,68,0.15) !important; color: #ef4444 !important;">
														<i class="fas fa-exclamation-triangle"></i>
													</span>
												@elseif($isExpiringSoonNoti)
													<span class="avatar-title rounded-circle" style="background-color: rgba(245,158,11,0.18) !important; color: #d97706 !important;">
														<i class="fas fa-hourglass-half"></i>
													</span>
												@else
													<span class="avatar-title rounded-circle" style="background-color: rgba(59,130,246,0.15) !important; color: #3b82f6 !important;">
														<i class="fe fe-box"></i>
													</span>
												@endif
											</span>
											<div class="media-body">
												@if($isExpiredNoti)
													<div class="d-flex align-items-center justify-content-between">
														<h6 class="text-danger font-weight-bold mb-0" style="font-size: 0.82rem;">
															<i class="fas fa-exclamation-triangle mr-1"></i> Expired Alert
														</h6>
														@if(!empty($notiData['is_active']))
															<span class="badge badge-danger font-weight-bold" style="font-size: 10px; padding: 2px 5px;">ACTIVE</span>
														@endif
													</div>
													<p class="noti-details mb-0 mt-1" style="font-size: 0.8rem; line-height: 1.3;">
														<span class="noti-title font-weight-bold text-dark">{{ $notiData['product_name'] ?? 'Product' }}</span>
														@if(!empty($notiData['package_label']))
															<span class="text-danger font-weight-bold">{{ $notiData['package_label'] }}</span>
														@endif
														is expired ({{ $notiData['quantity'] ?? 0 }} units) and currently Active.
													</p>
													<span class="text-muted d-block mt-1" style="font-size: 0.74rem;">Needs action: Review or Deactivate &rarr;</span>
												@elseif($isExpiringSoonNoti)
													<div class="d-flex align-items-center justify-content-between">
														<h6 class="font-weight-bold mb-0" style="font-size: 0.82rem; color: #d97706;">
															<i class="fas fa-clock mr-1"></i> Expiring Soon
														</h6>
														<span class="badge font-weight-bold" style="font-size: 10px; padding: 2px 5px; background: #fef3c7; color: #92400e;">
															{{ $notiData['days_left'] ?? 30 }}d left
														</span>
													</div>
													<p class="noti-details mb-0 mt-1" style="font-size: 0.8rem; line-height: 1.3;">
														<span class="noti-title font-weight-bold text-dark">{{ $notiData['product_name'] ?? 'Product' }}</span>
														@if(!empty($notiData['package_label']))
															<span class="font-weight-bold" style="color: #d97706;">{{ $notiData['package_label'] }}</span>
														@endif
														will expire in {{ $notiData['days_left'] ?? 30 }} days ({{ $notiData['quantity'] ?? 0 }} units).
													</p>
													<span class="text-muted d-block mt-1" style="font-size: 0.74rem;">FEFO: Prioritize dispensing &rarr;</span>
												@else
													<h6 class="text-warning font-weight-bold mb-0" style="font-size: 0.82rem;">
														<i class="fe fe-box mr-1"></i> Stock Alert
													</h6>
													<p class="noti-details mb-0 mt-1" style="font-size: 0.8rem; line-height: 1.3;">
														@if(isset($notiData['status']) && $notiData['status'] === 'out_of_stock')
															<span class="noti-title font-weight-bold text-dark">{{ $notiData['product_name'] ?? 'Product' }}</span> is out of stock.
														@else
															<span class="noti-title font-weight-bold text-dark">{{ $notiData['product_name'] ?? 'Product' }}</span> is low on stock ({{ $notiData['quantity'] ?? 0 }} left).
														@endif
													</p>
													<span class="text-muted d-block mt-1" style="font-size: 0.74rem;">Please update purchase quantity &rarr;</span>
												@endif
												<p class="noti-time mb-0 mt-1"><span class="notification-time text-muted" style="font-size: 0.72rem;">{{ $notification->created_at->diffForHumans() }}</span></p>
											</div>
										</div>
									</a>
									@if($isUnreadItem)
									<button type="button" class="btn btn-link btn-sm text-muted p-0 noti-single-dismiss" data-id="{{ $notification->id }}" title="Mark as read" style="font-size: 13px; opacity: 0.7;">
										<i class="fas fa-check"></i>
									</button>
								@endif
								</div>
							</li>
						@empty
							<li class="notification-empty">No recent notifications</li>
						@endforelse
					</ul>
				</div>
				<div class="topnav-dropdown-footer">
					<a href="javascript:void(0);" data-toggle="modal" data-target="#notification-center-modal" class="view-all-notifications-btn">View all Notifications</a>
				</div>
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

		document.addEventListener('click', function(e) {
			if (e.target.closest('.noti-dropdown a')) {
				var dropdown = document.querySelector('.noti-dropdown');
				var menu = dropdown ? dropdown.querySelector('.dropdown-menu') : null;
				if (dropdown) dropdown.classList.remove('show');
				if (menu) menu.classList.remove('show');
			}
		});
	})();
</script>