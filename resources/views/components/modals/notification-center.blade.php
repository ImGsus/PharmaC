<style>
    #notification-center-modal .modal-dialog {
        width: calc(100% - 1rem);
        max-width: 1080px;
        margin: 1.75rem auto;
    }
    #notification-center-modal .modal-content {
        max-height: calc(100vh - 2.5rem);
        display: flex;
        flex-direction: column;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        overflow: hidden;
    }
    #notification-center-modal .modal-header {
        position: sticky;
        top: 0;
        z-index: 5;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        padding: 14px 20px;
    }
    #notification-center-modal .noti-modal-icon-badge {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: rgba(37, 99, 235, 0.1);
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    #notification-center-modal .modal-body {
        min-height: 250px;
        max-height: calc(88vh - 75px);
        overflow-y: auto;
        overscroll-behavior: contain;
        padding: 18px 20px;
        background: #f8fafc;
    }

    /* Modal toolbar & filters */
    #notification-center-modal .noti-filters-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 16px;
        margin-bottom: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    #notification-center-modal .noti-nav-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    #notification-center-modal .noti-nav-pills .nav-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 20px;
        color: #475569;
        background: #f1f5f9;
        border: 1px solid transparent;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    #notification-center-modal .noti-nav-pills .nav-link:hover {
        background: #e2e8f0;
        color: #1e293b;
    }
    #notification-center-modal .noti-nav-pills .nav-link.active {
        background: #2563eb;
        color: #ffffff;
    }
    #notification-center-modal .noti-nav-pills .nav-link .badge {
        font-size: 10.5px;
        padding: 2px 6px;
        border-radius: 10px;
        background: rgba(0, 0, 0, 0.08);
        color: inherit;
    }
    #notification-center-modal .noti-nav-pills .nav-link.active .badge {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }
    #notification-center-modal .noti-search-form {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 240px;
    }
    #notification-center-modal .noti-search-input {
        font-size: 13px;
        border-radius: 20px;
        border: 1px solid #cbd5e1;
        padding: 5px 14px;
        outline: none;
        width: 100%;
        transition: border-color 0.2s;
    }
    #notification-center-modal .noti-search-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    /* Notification Cards */
    #notification-center-modal .noti-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px 18px;
        margin-bottom: 10px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        position: relative;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    #notification-center-modal .noti-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.07);
        border-color: #cbd5e1;
    }
    #notification-center-modal .noti-card.unread {
        border-left: 4px solid #2563eb;
        background: #f8fafc;
    }
    #notification-center-modal .noti-card.unread.type-expired {
        border-left-color: #ef4444;
        background: #fff8f8;
    }
    #notification-center-modal .noti-card.unread.type-expiring-soon {
        border-left-color: #f59e0b;
        background: #fffdf5;
    }

    #notification-center-modal .noti-avatar {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        font-size: 17px;
    }
    #notification-center-modal .noti-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    #notification-center-modal .noti-avatar.avatar-expired {
        background-color: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }
    #notification-center-modal .noti-avatar.avatar-soon {
        background-color: rgba(245, 158, 11, 0.15);
        color: #d97706;
    }
    #notification-center-modal .noti-avatar.avatar-stock {
        background-color: rgba(59, 130, 246, 0.12);
        color: #2563eb;
    }

    #notification-center-modal .noti-body {
        flex-grow: 1;
        min-width: 0;
    }
    #notification-center-modal .noti-header-line {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 3px;
    }
    #notification-center-modal .noti-title-text {
        font-size: 14.5px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    #notification-center-modal .noti-package-badge {
        font-size: 11px;
        font-weight: 700;
        color: #ef4444;
        background: rgba(239, 68, 68, 0.1);
        padding: 2px 7px;
        border-radius: 10px;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    #notification-center-modal .noti-package-badge.badge-soon {
        color: #d97706;
        background: rgba(245, 158, 11, 0.1);
        border-color: rgba(245, 158, 11, 0.2);
    }
    #notification-center-modal .noti-message-text {
        font-size: 13px;
        color: #475569;
        margin: 3px 0 6px 0;
        line-height: 1.4;
    }
    #notification-center-modal .noti-meta-line {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        font-size: 11.5px;
        color: #64748b;
    }
    #notification-center-modal .noti-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
        margin-left: auto;
    }

    #notification-center-modal .empty-noti-state {
        text-align: center;
        padding: 50px 20px;
        background: #ffffff;
        border-radius: 10px;
        border: 1px dashed #cbd5e1;
    }
    #notification-center-modal .empty-noti-icon {
        font-size: 42px;
        color: #94a3b8;
        margin-bottom: 12px;
    }

    /* Dark Mode Adjustments */
    body.dark-mode #notification-center-modal .modal-content {
        background: #1c2025;
        color: #f3f4f6;
        border-color: rgba(255, 255, 255, .1);
    }
    body.dark-mode #notification-center-modal .modal-header {
        background: #1c2025;
        border-color: rgba(255, 255, 255, .1);
        color: #f3f4f6;
    }
    body.dark-mode #notification-center-modal .modal-header .close {
        color: #cbd5e1;
        opacity: 0.8;
    }
    body.dark-mode #notification-center-modal .modal-header .close:hover {
        color: #ffffff;
        opacity: 1;
    }
    body.dark-mode #notification-center-modal .modal-body {
        background: #14181d;
    }
    body.dark-mode #notification-center-modal .noti-filters-bar {
        background: #1c2025;
        border-color: #2b313a;
    }
    body.dark-mode #notification-center-modal .noti-nav-pills .nav-link {
        background: #252b33;
        color: #94a3b8;
    }
    body.dark-mode #notification-center-modal .noti-nav-pills .nav-link:hover {
        background: #333a44;
        color: #f1f5f9;
    }
    body.dark-mode #notification-center-modal .noti-nav-pills .nav-link.active {
        background: #3b82f6;
        color: #ffffff;
    }
    body.dark-mode #notification-center-modal .noti-search-input {
        background: #252b33;
        border-color: #3b424d;
        color: #f1f5f9;
    }
    body.dark-mode #notification-center-modal .noti-search-input:focus {
        border-color: #3b82f6;
    }
    body.dark-mode #notification-center-modal .noti-card {
        background: #1c2025;
        border-color: #2b313a;
    }
    body.dark-mode #notification-center-modal .noti-card:hover {
        border-color: #3b424d;
    }
    body.dark-mode #notification-center-modal .noti-card.unread {
        background: #1e2638;
        border-left-color: #3b82f6;
    }
    body.dark-mode #notification-center-modal .noti-card.unread.type-expired {
        background: #2e1c20;
        border-left-color: #ef4444;
    }
    body.dark-mode #notification-center-modal .noti-card.unread.type-expiring-soon {
        background: #2e2617;
        border-left-color: #f59e0b;
    }
    body.dark-mode #notification-center-modal .noti-title-text {
        color: #f1f5f9;
    }
    body.dark-mode #notification-center-modal .noti-message-text {
        color: #cbd5e1;
    }
    body.dark-mode #notification-center-modal .noti-meta-line {
        color: #94a3b8;
    }
    body.dark-mode #notification-center-modal .empty-noti-state {
        background: #1c2025;
        border-color: #2b313a;
        color: #cbd5e1;
    }
    body.dark-mode #notification-center-modal .empty-noti-icon {
        color: #64748b;
    }
</style>

<div class="modal fade" id="notification-center-modal" tabindex="-1" role="dialog" aria-labelledby="notification-center-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="noti-modal-icon-badge mr-2">
                        <i class="fe fe-bell"></i>
                    </div>
                    <h5 class="modal-title font-weight-bold mb-0" id="notification-center-modal-title">Notifications Center</h5>
                </div>
                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-primary mr-2" id="btn-modal-mark-all-read" title="Mark all as read">
                        <i class="fas fa-check-double mr-1"></i> Mark All Read
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary mr-3" id="btn-modal-clear-read" title="Clear read notifications">
                        <i class="fas fa-trash-alt mr-1"></i> Clear Read
                    </button>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="font-size: 24px; line-height: 1; padding: 0; margin: 0; border: none; background: transparent;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <div class="modal-body" id="notification-center-modal-body" aria-live="polite">
                <div class="text-center text-muted py-5">
                    <i class="fa fa-spinner fa-spin fa-2x mb-3 text-primary"></i>
                    <p class="mb-0">Loading notifications...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('page-js')
<script>
(function ($) {
    var endpoint = @json(route('notifications.modal-content'));
    var currentTab = 'all';
    var currentSearch = '';
    var currentPageUrl = null;
    var searchTimer = null;
    var activeRequestId = 0;

    function getCsrfToken() {
        return (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    }

    function syncBellCount(unreadCount) {
        var bellBadge = document.querySelector('.notification-bell-link .notification-count');
        if (unreadCount > 0) {
            if (bellBadge) {
                bellBadge.textContent = unreadCount > 9 ? '9+' : unreadCount;
            } else {
                var bellLink = document.querySelector('.notification-bell-link');
                if (bellLink) {
                    var newBadge = document.createElement('span');
                    newBadge.className = 'badge badge-pill notification-count';
                    newBadge.textContent = unreadCount > 9 ? '9+' : unreadCount;
                    bellLink.appendChild(newBadge);
                }
            }
        } else {
            if (bellBadge) bellBadge.remove();
            var notiList = document.querySelector('.notification-list');
            if (notiList) notiList.innerHTML = '<li class="notification-empty">No new notifications</li>';
            var clearNoti = document.querySelector('.topnav-dropdown-header .clear-noti');
            if (clearNoti) {
                clearNoti.outerHTML = '<span class="notification-empty-state-label">All caught up</span>';
            }
        }
    }

    function loadModalContent(url) {
        var requestId = ++activeRequestId;
        var $body = $('#notification-center-modal-body');

        var targetUrl = url || endpoint;
        if (!url) {
            var params = [];
            if (currentTab) params.push('tab=' + encodeURIComponent(currentTab));
            if (currentSearch) params.push('search=' + encodeURIComponent(currentSearch));
            if (params.length > 0) targetUrl += (targetUrl.indexOf('?') === -1 ? '?' : '&') + params.join('&');
        }

        $body.html('<div class="text-center text-muted py-5"><i class="fa fa-spinner fa-spin fa-2x mb-3 text-primary"></i><p class="mb-0">Loading notifications...</p></div>');

        $.get(targetUrl)
            .done(function (markup) {
                if (requestId !== activeRequestId) return;
                $body.html(markup);
            })
            .fail(function () {
                if (requestId !== activeRequestId) return;
                $body.html('<div class="alert alert-danger mb-0" role="alert">Notifications could not be loaded. Please try again.</div>');
            });
    }

    // Modal show handler
    $(document).off('.notiModal')
        .on('show.bs.modal.notiModal', '#notification-center-modal', function () {
            // Close header dropdown if open
            $('.noti-dropdown').removeClass('show');
            $('.noti-dropdown .dropdown-menu').removeClass('show');
            loadModalContent();
        })
        .on('click.notiModal', '.view-all-notifications-btn', function (e) {
            e.preventDefault();
            $('.noti-dropdown').removeClass('show');
            $('.noti-dropdown .dropdown-menu').removeClass('show');
            $('#notification-center-modal').modal('show');
        })
        // Tab switching
        .on('click.notiModal', '#notification-center-modal .noti-tab-link', function (e) {
            e.preventDefault();
            currentTab = $(this).data('tab') || 'all';
            loadModalContent();
        })
        // Search input keyup
        .on('input.notiModal', '#modal-noti-search-input', function () {
            var val = $(this).val();
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                currentSearch = val;
                loadModalContent();
            }, 350);
        })
        // Search submit button
        .on('click.notiModal', '#modal-noti-search-btn', function () {
            currentSearch = $('#modal-noti-search-input').val();
            loadModalContent();
        })
        // Clear search button
        .on('click.notiModal', '#modal-noti-search-clear', function () {
            currentSearch = '';
            $('#modal-noti-search-input').val('');
            loadModalContent();
        })
        // Pagination link click
        .on('click.notiModal', '#notification-center-modal .modal-pagination-wrapper a', function (e) {
            e.preventDefault();
            var pageUrl = $(this).attr('href');
            if (pageUrl) {
                loadModalContent(pageUrl);
            }
        })
        // Top "Mark All Read" button
        .on('click.notiModal', '#btn-modal-mark-all-read', function (e) {
            e.preventDefault();
            var $btn = $(this);
            $btn.prop('disabled', true);

            $.ajax({
                url: @json(route('mark-as-read')),
                type: 'GET',
                dataType: 'json',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() }
            })
            .always(function () {
                $btn.prop('disabled', false);
                syncBellCount(0);
                loadModalContent();
            });
        })
        // Top "Clear Read" button
        .on('click.notiModal', '#btn-modal-clear-read', function (e) {
            e.preventDefault();
            if (!confirm('Clear all read notifications?')) return;

            var $btn = $(this);
            $btn.prop('disabled', true);

            $.ajax({
                url: @json(route('notifications.clear-read')),
                type: 'POST',
                dataType: 'json',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() }
            })
            .always(function (data) {
                $btn.prop('disabled', false);
                if (data && typeof data.unread_count !== 'undefined') {
                    syncBellCount(data.unread_count);
                }
                loadModalContent();
            });
        })
        // Single Mark Read button
        .on('click.notiModal', '#notification-center-modal .modal-mark-single-read', function (e) {
            e.preventDefault();
            var notiId = $(this).data('id');
            var $card = $('#modal-noti-item-' + notiId);
            var $btn = $(this);

            $.ajax({
                url: '/notification/mark-single/' + notiId,
                type: 'POST',
                dataType: 'json',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() }
            })
            .done(function (data) {
                if (data && data.success) {
                    $card.removeClass('unread').addClass('read');
                    $btn.remove();
                    if (typeof data.unread_count !== 'undefined') {
                        syncBellCount(data.unread_count);
                    }
                    // Update Unread tab count pill
                    var $unreadTab = $('#modal-noti-tabs [data-tab="unread"] .badge');
                    if ($unreadTab.length && typeof data.unread_count !== 'undefined') {
                        $unreadTab.text(data.unread_count);
                    }
                }
            });
        })
        // Single Delete button
        .on('click.notiModal', '#notification-center-modal .modal-delete-single-noti', function (e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this notification?')) return;

            var notiId = $(this).data('id');
            var $card = $('#modal-noti-item-' + notiId);

            $.ajax({
                url: '/notification/' + notiId,
                type: 'DELETE',
                dataType: 'json',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() }
            })
            .done(function (data) {
                if (data && data.success) {
                    $card.css({
                        transition: 'opacity 0.25s ease, transform 0.25s ease',
                        opacity: '0',
                        transform: 'translateY(-10px)'
                    });
                    setTimeout(function () {
                        $card.remove();
                        if (typeof data.unread_count !== 'undefined') {
                            syncBellCount(data.unread_count);
                        }
                    }, 250);
                }
            });
        });

    // Check if URL has ?open_notifications=1 on load
    if (window.location.search.indexOf('open_notifications=1') !== -1) {
        setTimeout(function () {
            $('#notification-center-modal').modal('show');
            if (window.history && window.history.replaceState) {
                var cleanUrl = window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            }
        }, 300);
    }
})(jQuery);
</script>
@endpush
