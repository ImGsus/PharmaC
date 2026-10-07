<!-- Filters & Search Toolbar -->
<div class="noti-filters-bar">
    <ul class="noti-nav-pills" id="modal-noti-tabs">
        <li>
            <a href="javascript:void(0);" data-tab="all" class="nav-link noti-tab-link {{ $tab === 'all' ? 'active' : '' }}">
                All <span class="badge">{{ $counts['all'] ?? 0 }}</span>
            </a>
        </li>
        <li>
            <a href="javascript:void(0);" data-tab="unread" class="nav-link noti-tab-link {{ $tab === 'unread' ? 'active' : '' }}">
                Unread <span class="badge">{{ $counts['unread'] ?? 0 }}</span>
            </a>
        </li>
        <li>
            <a href="javascript:void(0);" data-tab="expired" class="nav-link noti-tab-link {{ $tab === 'expired' ? 'active' : '' }}">
                <i class="fas fa-exclamation-triangle text-danger"></i> Expired <span class="badge">{{ $counts['expired'] ?? 0 }}</span>
            </a>
        </li>
        <li>
            <a href="javascript:void(0);" data-tab="expiring_soon" class="nav-link noti-tab-link {{ $tab === 'expiring_soon' ? 'active' : '' }}">
                <i class="fas fa-clock" style="color: #d97706;"></i> Expiring Soon (FEFO) <span class="badge">{{ $counts['expiring_soon'] ?? 0 }}</span>
            </a>
        </li>
        <li>
            <a href="javascript:void(0);" data-tab="stock" class="nav-link noti-tab-link {{ $tab === 'stock' ? 'active' : '' }}">
                <i class="fe fe-box text-primary"></i> Stock Alerts <span class="badge">{{ $counts['stock'] ?? 0 }}</span>
            </a>
        </li>
    </ul>

    <form id="modal-noti-search-form" class="noti-search-form" onsubmit="return false;">
        <input type="text" id="modal-noti-search-input" class="noti-search-input" placeholder="Search notifications..." value="{{ $search }}">
        <button type="button" id="modal-noti-search-btn" class="btn btn-sm btn-primary" title="Search">
            <i class="fas fa-search"></i>
        </button>
        @if(!empty($search))
            <button type="button" id="modal-noti-search-clear" class="btn btn-sm btn-light" title="Clear search">
                <i class="fas fa-times"></i>
            </button>
        @endif
    </form>
</div>

<!-- Notification Cards List -->
<div class="noti-cards-list" id="modal-noti-cards-container">
    @forelse ($notifications as $notification)
        @php
            $data = $notification->data ?? [];
            $type = $data['type'] ?? '';
            $isExpired = $type === 'expired' || ($notification->type === 'App\Notifications\ProductExpiryNotification' && $type !== 'expiring_soon');
            $isSoon = $type === 'expiring_soon';
            $isUnread = is_null($notification->read_at);

            $productImage = $data['image'] ?? null;
            $imagePath = $productImage ? storage_path('app/system/purchases/'.$productImage) : null;
            if ($productImage && !file_exists($imagePath)) $imagePath = storage_path('app/purchases/'.$productImage);
            $hasValidImage = !empty($productImage) && file_exists($imagePath);

            // Determine the best destination URL for the action button (relative paths — host-agnostic)
            $productId = $data['product_id'] ?? null;
            $actionUrl = $data['action_url'] ?? null;
            if (!empty($actionUrl)) {
                // Strip host from any stored action URL so it works on any IP
                $actionPath  = parse_url($actionUrl, PHP_URL_PATH);
                $actionQuery = parse_url($actionUrl, PHP_URL_QUERY);
                $actionUrl   = $actionPath . ($actionQuery ? '?' . $actionQuery : '');
            } else {
                if ($isExpired) {
                    $actionUrl = '/products/expired';
                } elseif ($isSoon && $productId) {
                    $actionUrl = '/products/' . $productId . '/edit';
                } elseif ($isSoon) {
                    $actionUrl = '/products';
                } elseif ($productId) {
                    $actionUrl = '/products/' . $productId . '/edit';
                } else {
                    $actionUrl = '#';
                }
            }
        @endphp
        <div class="noti-card {{ $isUnread ? 'unread' : 'read' }} {{ $isExpired ? 'type-expired' : ($isSoon ? 'type-expiring-soon' : 'type-stock') }}" id="modal-noti-item-{{ $notification->id }}" data-id="{{ $notification->id }}">
            <!-- Avatar / Icon -->
            <div class="noti-avatar {{ $isExpired ? 'avatar-expired' : ($isSoon ? 'avatar-soon' : 'avatar-stock') }}">
                @if($hasValidImage)
                    <img src="{{ url('storage/system/purchases/'.$productImage) }}" alt="Product Image">
                @elseif($isExpired)
                    <i class="fas fa-exclamation-triangle"></i>
                @elseif($isSoon)
                    <i class="fas fa-hourglass-half"></i>
                @else
                    <i class="fe fe-box"></i>
                @endif
            </div>

            <!-- Body -->
            <div class="noti-body">
                <div class="noti-header-line">
                    @if($isExpired)
                        <span class="badge badge-danger font-weight-bold" style="font-size: 11px;">
                            <i class="fas fa-exclamation-triangle mr-1"></i> EXPIRED ALERT
                        </span>
                        @if(!empty($data['is_active']))
                            <span class="badge badge-danger font-weight-bold" style="font-size: 10px;">ACTIVE IN SYSTEM</span>
                        @endif
                    @elseif($isSoon)
                        <span class="badge font-weight-bold" style="font-size: 11px; background: #fef3c7; color: #92400e;">
                            <i class="fas fa-clock mr-1"></i> EXPIRING SOON (FEFO)
                        </span>
                        <span class="badge font-weight-bold" style="font-size: 10px; background: #fed7aa; color: #7c2d12;">
                            {{ $data['days_left'] ?? 30 }} DAYS LEFT
                        </span>
                    @else
                        <span class="badge badge-warning text-dark font-weight-bold" style="font-size: 11px;">
                            <i class="fe fe-box mr-1"></i> STOCK ALERT
                        </span>
                    @endif

                    @if(!empty($data['package_label']))
                        <span class="noti-package-badge {{ $isSoon ? 'badge-soon' : '' }}">
                            {{ $data['package_label'] }}
                        </span>
                    @endif
                </div>

                <h4 class="noti-title-text">
                    {{ $data['product_name'] ?? 'Pharmacy Inventory Item' }}
                </h4>

                <p class="noti-message-text">
                    {{ $data['message'] ?? 'Notification regarding inventory status.' }}
                </p>

                <div class="noti-meta-line">
                    <span><i class="far fa-clock mr-1"></i> {{ $notification->created_at->diffForHumans() }} ({{ $notification->created_at->format('M d, Y h:i A') }})</span>
                    @if(!empty($data['expiry_date']))
                        <span><i class="far fa-calendar-alt mr-1"></i> Expiry: <strong>{{ $data['expiry_date'] }}</strong></span>
                    @endif
                    @if(isset($data['quantity']))
                        <span><i class="fas fa-layer-group mr-1"></i> Quantity: <strong>{{ $data['quantity'] }}</strong></span>
                    @endif
                    @if(!$isUnread)
                        <span class="text-success"><i class="fas fa-check mr-1"></i> Read</span>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="noti-actions">
                <a href="/notification/read/{{ $notification->id }}" class="btn btn-sm {{ $isExpired ? 'btn-danger' : ($isSoon ? 'btn-warning text-dark' : 'btn-primary') }}" data-turbo="false">
                    @if($isExpired)
                        <i class="fas fa-external-link-alt mr-1"></i> Review
                    @elseif($isSoon)
                        <i class="fas fa-external-link-alt mr-1"></i> View Item
                    @else
                        <i class="fas fa-box mr-1"></i> Details
                    @endif
                </a>

                @if($isUnread)
                    <button type="button" class="btn btn-sm btn-outline-secondary modal-mark-single-read" data-id="{{ $notification->id }}" title="Mark as read">
                        <i class="fas fa-check"></i>
                    </button>
                @endif

                <button type="button" class="btn btn-sm btn-outline-danger modal-delete-single-noti" data-id="{{ $notification->id }}" title="Delete notification">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
    @empty
        <div class="empty-noti-state">
            <div class="empty-noti-icon">
                <i class="fe fe-bell-off"></i>
            </div>
            <h4 class="font-weight-bold mb-2">No Notifications Found</h4>
            <p class="text-muted mb-3">
                @if(!empty($search))
                    No notifications match your search query "<strong>{{ $search }}</strong>".
                @elseif($tab === 'unread')
                    Great job! You have no unread notifications at the moment.
                @elseif($tab === 'expired')
                    No active expired product alerts found.
                @elseif($tab === 'expiring_soon')
                    No items are expiring within the next 30 days.
                @elseif($tab === 'stock')
                    No low or out-of-stock items reported.
                @else
                    You don't have any notifications right now.
                @endif
            </p>
            @if(!empty($search) || $tab !== 'all')
                <button type="button" class="btn btn-primary btn-sm noti-tab-link" data-tab="all">
                    View All Notifications
                </button>
            @endif
        </div>
    @endforelse
</div>

<!-- Modal Pagination -->
@if($notifications->hasPages())
    <div class="d-flex justify-content-center mt-3 modal-pagination-wrapper">
        {{ $notifications->links() }}
    </div>
@endif
