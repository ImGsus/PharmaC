<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- csrf token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ucfirst(AppSettings::get('app_name', 'App'))}} - {{ucfirst($title ?? '')}}</title>
    <script src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@8.0.12/dist/turbo.es2017-umd.js" data-turbo-track="reload"></script>
    <script>
        window.history.scrollRestoration = 'manual';

        (function () {
            // Every page opens at the top (all pages): the old per-page scroll
            // position restore used to reopen pages scrolled down.

            document.addEventListener('turbo:before-visit', function () {
                // Save sidebar scroll position before leaving the page
                var $sidebar = document.querySelector('.sidebar-inner.slimscroll');
                if ($sidebar) {
                    try {
                        sessionStorage.setItem('pharmacy-sidebar-scroll', String($sidebar.scrollTop || 0));
                    } catch (e) {}
                }
            });

            document.addEventListener('turbo:load', function () {
                // Force the viewport to the top after every navigation so pages
                // never reopen scrolled down (rAF catches same-frame shifts).
                window.scrollTo(0, 0);
                requestAnimationFrame(function () {
                    window.scrollTo(0, 0);
                });
            });
        })();
        (function () {
            var root = document.documentElement;
            var body = document.body;
            var storageKey = 'pharmacy-theme';

            try {
                var savedTheme = localStorage.getItem(storageKey);
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                var isDark = savedTheme === 'dark' || (savedTheme !== 'light' && prefersDark);

                root.classList.toggle('dark-mode', isDark);
                root.setAttribute('data-theme', isDark ? 'dark' : 'light');
                root.style.colorScheme = isDark ? 'dark' : 'light';

                if (body) {
                    body.classList.toggle('dark-mode', isDark);
                    body.setAttribute('data-theme', isDark ? 'dark' : 'light');
                }
            } catch (error) {
                root.style.colorScheme = 'light';
            }
        })();

        document.addEventListener('turbo:before-cache', function () {
            document.querySelectorAll('.dashboard-hero.animate__animated, .dashboard-card.animate__animated, .dashboard-card-animation.animate__animated').forEach(function (element) {
                element.classList.remove('animate__animated');
            });
        });

        document.addEventListener('turbo:render', function () {
            var sidebar = document.getElementById('sidebar-scroll-container');
            if (!sidebar) return;

            try {
                var savedSidebarScroll = parseInt(sessionStorage.getItem('pharmacy-sidebar-scroll'), 10) || 0;
                if (savedSidebarScroll > 0) sidebar.scrollTop = savedSidebarScroll;
            } catch (e) {}
        });

        function syncSidebarActiveNavigation(pathname) {
            var menu = document.getElementById('sidebar-menu');
            if (!menu) return;

            var currentPath = (pathname || window.location.pathname).replace(/\/+$/, '') || '/';
            var selectedLink = null;
            var selectedPathLength = -1;

            Array.prototype.forEach.call(menu.querySelectorAll('a[href]'), function (link) {
                var href = link.getAttribute('href');
                if (!href || href === '#') return;

                var target;
                try {
                    target = new URL(href, window.location.href);
                } catch (error) {
                    return;
                }
                if (target.origin !== window.location.origin) return;

                var linkPath = target.pathname.replace(/\/+$/, '') || '/';
                var matchesPath = currentPath === linkPath || currentPath.indexOf(linkPath + '/') === 0;
                if (matchesPath && linkPath.length > selectedPathLength) {
                    selectedLink = link;
                    selectedPathLength = linkPath.length;
                }
            });

            menu.querySelectorAll('li.active').forEach(function (item) {
                item.classList.remove('active');
            });
            menu.querySelectorAll('a.active, a.subdrop, a.sidebar-label-hidden').forEach(function (link) {
                link.classList.remove('active', 'subdrop', 'sidebar-label-hidden');
            });
            if (!selectedLink) return;

            var selectedItem = selectedLink.closest('li');
            if (selectedItem) selectedItem.classList.add('active');

            var parentSubmenu = selectedLink.closest('#sidebar-menu > ul > li.submenu');
            if (parentSubmenu && parentSubmenu !== selectedItem) {
                parentSubmenu.classList.add('active');
                selectedLink.classList.add('active');
            }
        }

        document.addEventListener('turbo:before-render', function (event) {
            window.dashboardChartRequestId = (window.dashboardChartRequestId || 0) + 1;
            window.pharmacyInstantModalHide = true;
            $('.modal').modal('hide');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
            window.pharmacyInstantModalHide = false;
            $('html').removeClass('menu-opened');
            $('.sidebar-overlay').removeClass('opened');
            $('.noti-dropdown').removeClass('show');
            $('.noti-dropdown .dropdown-menu').removeClass('show');
            if (window.pieChart && typeof window.pieChart.destroy === 'function') {
                window.pieChart.destroy();
                window.pieChart = null;
            }

            // Destroy all DataTable instances before rendering new page
            if ($.fn.DataTable) {
                $.fn.DataTable.fnTables().forEach(function(table) {
                    if ($.fn.DataTable.isDataTable(table)) {
                        $(table).DataTable().destroy();
                    }
                });
            }

            // Destroy all Tabulator instances before rendering new page
            if (window.PharmaTabulator) {
                window.PharmaTabulator.destroyAll();
            }

            var currentBody = document.body;
            var nextBody = event.detail.newBody;
            var isDark = currentBody && currentBody.classList.contains('dark-mode');
            var currentMainWrapper = currentBody && currentBody.querySelector('.main-wrapper');
            var nextMainWrapper = nextBody && nextBody.querySelector('.main-wrapper');

            nextBody.classList.toggle('dark-mode', isDark);
            nextBody.setAttribute('data-theme', isDark ? 'dark' : 'light');
            if (currentMainWrapper && nextMainWrapper) {
                nextMainWrapper.classList.toggle('slide-nav', currentMainWrapper.classList.contains('slide-nav'));
            }
            syncSidebarActiveNavigation(window.location.pathname);
        });

        function clearNavigationLock() {
            window.pharmacyNavigationPending = false;
            if (window.pharmacyNavigationTimer) {
                window.clearTimeout(window.pharmacyNavigationTimer);
                window.pharmacyNavigationTimer = null;
            }
            document.querySelectorAll('a[data-navigation-pending="true"]').forEach(function (link) {
                link.removeAttribute('data-navigation-pending');
                link.removeAttribute('aria-busy');
            });
        }

        window.addEventListener('pageshow', clearNavigationLock);
        document.addEventListener('turbo:load', clearNavigationLock);

        /* Lock internal navigation before Turbo/sidebar handlers can queue another request. */
        document.addEventListener('click', function (event) {
            var link = event.target.closest('a[href]');
            if (!link || link.getAttribute('href') === '#' || link.target === '_blank' || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
                return;
            }

            var rowActionPopup = link.closest('#rowActionPopup');
            var rowActionGroup = link.closest('.btn-group');
            if (rowActionPopup || (rowActionGroup && rowActionGroup.querySelector('.row-action-modal-trigger'))) {
                return;
            }

            var target = new URL(link.href, window.location.href);
            if (target.origin !== window.location.origin) {
                return;
            }

            if (target.pathname === window.location.pathname && target.search === window.location.search) {
                event.preventDefault();
                return;
            }

            if (window.pharmacyNavigationPending) {
                event.preventDefault();
                event.stopImmediatePropagation();
                return;
            }

            window.pharmacyNavigationPending = true;
            link.setAttribute('data-navigation-pending', 'true');
            link.setAttribute('aria-busy', 'true');
            window.pharmacyNavigationTimer = window.setTimeout(clearNavigationLock, 8000);
        }, true);

        document.addEventListener('turbo:before-fetch-response', function (event) {
            var response = event.detail.fetchResponse.response;

            if (response.redirected && new URL(response.url).pathname === '/login') {
                event.preventDefault();
                window.location.assign(response.url);
            }
        });

        document.addEventListener('turbo:load', function () {
            // Always initialize on every Turbo navigation
            if (window.pharmacySidebarInit) {
                window.pharmacySidebarInit();
            }
            syncSidebarActiveNavigation(window.location.pathname);
            if (window.pharmacySidebarSlimScrollInit) {
                window.pharmacySidebarSlimScrollInit();
            }

            // Restore sidebar scroll position after slimscroll is re-initialized
            try {
                var savedSidebarScroll = sessionStorage.getItem('pharmacy-sidebar-scroll');
                if (savedSidebarScroll && parseInt(savedSidebarScroll, 10) > 0) {
                    var sidebar = document.getElementById('sidebar-scroll-container');
                    if (sidebar) sidebar.scrollTop = parseInt(savedSidebarScroll, 10);
                }
            } catch (e) {}

            if (window.pharmacyCommonWidgetsInit) {
                window.pharmacyCommonWidgetsInit();
            }
            if (window.pharmacyDashboardInit) {
                window.pharmacyDashboardInit();
            }

            setTimeout(function () {
                var notification = document.getElementById('purchase-created-notification');
                if (!notification || !window.Snackbar) return;
                Snackbar.show({
                    text: notification.getAttribute('data-message'),
                    pos: 'top-right',
                    actionTextColor: '#fff',
                    backgroundColor: '#8dbf42',
                    duration: 5000
                });
                notification.remove();
                window.history.replaceState({}, document.title, window.location.pathname);
            }, 100);
        });
    </script>
    <style>
        html {
            background: #f5f7fb;
        }
        html.dark-mode {
            background: #0f1418;
        }
        html.theme-initializing,
        html.theme-initializing * {
            transition: none !important;
            animation: none !important;
        }
        .turbo-progress-bar {
            display: none !important;
        }
        .user-menu.nav > li > a.notification-bell-link {
            position: relative;
        }
        .user-menu.nav > li > a.notification-bell-link .notification-count {
            align-items: center;
            background-color: #ef4444;
            border: 2px solid #1c2025;
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            font-size: 9px;
            height: 16px;
            justify-content: center;
            line-height: 1;
            min-height: 16px;
            min-width: 16px;
            padding: 0 3px;
            right: 1px;
            top: 5px;
        }
        /* Unread dot indicator for bell dropdown items */
        .noti-item-unread .noti-card-wrapper {
            background: rgba(37, 99, 235, 0.04);
        }
        .noti-unread-dot {
            position: absolute;
            top: 50%;
            left: 4px;
            transform: translateY(-50%);
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #2563eb;
            flex-shrink: 0;
            pointer-events: none;
        }
        .noti-card-wrapper {
            padding-left: 14px !important;
        }
        body.dark-mode .noti-item-unread .noti-card-wrapper {
            background: rgba(59, 130, 246, 0.08);
        }
        @media (max-width: 767.98px) {
            .header .user-menu > li.noti-dropdown > .dropdown-menu.notifications {
                box-sizing: border-box;
                left: auto !important;
                max-height: calc(100dvh - 80px) !important;
                max-width: none !important;
                min-width: 0 !important;
                overflow: hidden !important;
                position: fixed !important;
                right: 8px !important;
                top: 64px !important;
                transform: none !important;
                width: min(350px, calc(100vw - 32px)) !important;
            }
            .header .user-menu > li.noti-dropdown .noti-content {
                height: auto;
                max-height: min(290px, calc(100dvh - 180px));
                overflow-y: auto;
                width: 100%;
            }
            .notifications .notification-empty {
                color: #64748b;
                list-style: none;
                padding: 1rem;
                text-align: center;
            }
            .notification-empty-state-label {
                color: #64748b;
                float: right;
                font-size: 12px;
            }
            body.dark-mode .notifications .notification-empty,
            body.dark-mode .notification-empty-state-label {
                color: #cbd5e1;
            }
        }

        .noti-card-wrapper {
            border-radius: 6px;
            transition: background-color .15s ease;
        }
        .noti-card-wrapper:hover {
            background-color: rgba(0, 0, 0, 0.04);
        }
        .noti-expired-card {
            border-left: 3px solid #ef4444;
            background-color: rgba(239, 68, 68, 0.04);
        }
        .noti-expired-card:hover {
            background-color: rgba(239, 68, 68, 0.08);
        }
        .noti-single-dismiss {
            color: #94a3b8;
            transition: all .15s ease;
        }
        .noti-single-dismiss:hover {
            color: #22c55e !important;
            transform: scale(1.15);
        }
        body.dark-mode .noti-card-wrapper:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }
        body.dark-mode .noti-expired-card {
            background-color: rgba(239, 68, 68, 0.1);
        }
        body.dark-mode .noti-expired-card:hover {
            background-color: rgba(239, 68, 68, 0.18);
        }
        body.dark-mode .noti-details .noti-title {
            color: #f1f5f9 !important;
        }

        .table-responsive {
            max-width: 100%;
            overflow-x: auto;
        }

        .table-responsive:has(.dropdown-toggle) {
            overflow: visible;
        }

        .table-responsive > table,
        table.datatable {
            width: 100% !important;
            max-width: 100%;
            table-layout: fixed;
        }

        .table-responsive > table th,
        .table-responsive > table td,
        table.datatable th,
        table.datatable td {
            max-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table-responsive > table td:has(.dropdown-toggle),
        table.datatable td:has(.dropdown-toggle) {
            max-width: none;
            overflow: visible;
        }

        .table-responsive > table:has(th.action-btn) th:last-child,
        .table-responsive > table:has(th.action-btn) td:last-child,
        table.datatable:has(th.action-btn) th:last-child,
        table.datatable:has(th.action-btn) td:last-child {
            width: 90px !important;
            min-width: 90px;
            text-align: center;
            white-space: nowrap;
            overflow: visible;
            max-width: none;
        }

        .table-responsive > table:has(th.action-btn) td:last-child .dropdown-menu,
        table.datatable:has(th.action-btn) td:last-child .dropdown-menu {
            z-index: 1060;
        }

        .table-responsive .dropdown-menu,
        .tabulator .dropdown-menu {
            z-index: 1060;
        }
    </style>
    <!-- Favicon -->
    @php
        $faviconPath = AppSettings::get('favicon');
        $faviconUrl = !empty($faviconPath)
            ? asset('storage/' . $faviconPath) . (file_exists(public_path('storage/' . $faviconPath)) ? '?v=' . filemtime(public_path('storage/' . $faviconPath)) : '')
            : asset('assets/img/favicon.png');
    @endphp
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">
    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{asset('assets/plugins/fontawesome/css/fontawesome.min.css')}}">
    <!-- Feathericon CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/feathericon.min.css')}}">

    <link rel="stylesheet" href="{{asset('assets/css/icons.min.css')}}">
    <!-- Snackbar CSS -->
	<link rel="stylesheet" href="{{asset('assets/plugins/snackbar/snackbar.min.css')}}">
    <!-- Sweet Alert css -->
    <link rel="stylesheet" href="{{asset('assets/plugins/sweetalert2/sweetalert2.min.css')}}">
    <!-- Snackbar Css -->
    <link rel="stylesheet" href="{{asset('assets/plugins/snackbar/snackbar.min.css')}}">
    <!-- Select2 Css -->
    <link rel="stylesheet" href="{{asset('assets/plugins/select2/css/select2.min.css')}}">
    <!-- Tabulator (shared light/dark tables) -->
    <link rel="stylesheet" href="{{asset('assets/plugins/tabulator/css/tabulator.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/tabulator-theme.css')}}">
    <!-- Main CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <!-- Animation CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/animate.min.css')}}">
    <script src="{{asset('assets/plugins/chart.js/Chart.bundle.min.js')}}" data-turbo-eval="false"></script>
    <!-- Tailwind CSS v4 + daisyUI v5 -->
    <link rel="stylesheet" href="{{asset('css/app.css')}}">
    <!-- Page CSS -->
    @stack('page-css')
    <style>
        /* Keep every dark-mode text field on the same readable surface. */
        html.dark-mode input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
        html.dark-mode select,
        html.dark-mode textarea,
        html.dark-mode .form-control,
        html.dark-mode .dataTables_filter input,
        html.dark-mode .tabulator input:not([type="checkbox"]):not([type="radio"]),
        html.dark-mode .select2-container--default .select2-selection--single,
        html.dark-mode .select2-container--default .select2-selection--multiple,
        body.dark-mode input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
        body.dark-mode select,
        body.dark-mode textarea,
        body.dark-mode .form-control,
        body.dark-mode .dataTables_filter input,
        body.dark-mode .tabulator input:not([type="checkbox"]):not([type="radio"]),
        body.dark-mode .select2-container--default .select2-selection--single,
        body.dark-mode .select2-container--default .select2-selection--multiple {
            background-color: #111827 !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.55) !important;
        }

        html.dark-mode input::placeholder,
        html.dark-mode textarea::placeholder,
        html.dark-mode .form-control::placeholder,
        html.dark-mode .dataTables_filter input::placeholder,
        html.dark-mode .tabulator input::placeholder,
        body.dark-mode input::placeholder,
        body.dark-mode textarea::placeholder,
        body.dark-mode .form-control::placeholder,
        body.dark-mode .dataTables_filter input::placeholder,
        body.dark-mode .tabulator input::placeholder {
            color: #94a3b8 !important;
            opacity: 1;
        }

        html.dark-mode input:not([type="checkbox"]):not([type="radio"]):not([type="file"]):focus,
        html.dark-mode select:focus,
        html.dark-mode textarea:focus,
        html.dark-mode .form-control:focus,
        html.dark-mode .dataTables_filter input:focus,
        html.dark-mode .tabulator input:not([type="checkbox"]):not([type="radio"]):focus,
        html.dark-mode .select2-container--default.select2-container--focus .select2-selection--single,
        html.dark-mode .select2-container--default.select2-container--focus .select2-selection--multiple,
        body.dark-mode input:not([type="checkbox"]):not([type="radio"]):not([type="file"]):focus,
        body.dark-mode select:focus,
        body.dark-mode textarea:focus,
        body.dark-mode .form-control:focus,
        body.dark-mode .dataTables_filter input:focus,
        body.dark-mode .tabulator input:not([type="checkbox"]):not([type="radio"]):focus,
        body.dark-mode .select2-container--default.select2-container--focus .select2-selection--single,
        body.dark-mode .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #69d9aa !important;
            box-shadow: 0 0 0 2px rgba(105, 217, 170, 0.18) !important;
            outline: none;
        }

        body.dark-mode .select2-container--default .select2-selection--single .select2-selection__rendered,
        body.dark-mode .select2-container--default .select2-selection--single .select2-selection__arrow {
            color: #f8fafc !important;
        }

        body:not(.dark-mode) input:not([type="checkbox"]):not([type="radio"]):not([type="file"]):focus,
        body:not(.dark-mode) select:focus,
        body:not(.dark-mode) textarea:focus,
        body:not(.dark-mode) .form-control:focus,
        body:not(.dark-mode) .dataTables_filter input:focus,
        body:not(.dark-mode) .tabulator input:focus,
        body:not(.dark-mode) .select2-container--default.select2-container--focus .select2-selection--single,
        body:not(.dark-mode) .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.18) !important;
            outline: none;
        }

        /* Global dark-mode styling for all Back, Close, Cancel, and Secondary buttons */
        body.dark-mode .modal-header .close,
        body.dark-mode .close,
        body.dark-mode button.close {
            color: #94a3b8 !important;
            text-shadow: none !important;
            opacity: 0.8 !important;
        }

        body.dark-mode .modal-header .close:hover,
        body.dark-mode .modal-header .close:focus,
        body.dark-mode .close:hover,
        body.dark-mode .close:focus,
        body.dark-mode button.close:hover,
        body.dark-mode button.close:focus {
            color: #ffffff !important;
            opacity: 1 !important;
        }

        body.dark-mode .btn-secondary,
        body.dark-mode .btn-outline-secondary,
        body.dark-mode .btn-default,
        body.dark-mode .btn-light,
        body.dark-mode .modal-footer .btn-secondary,
        body.dark-mode .modal-footer .btn-outline-secondary,
        body.dark-mode .modal-footer .btn-light,
        body.dark-mode .modal-footer [data-dismiss="modal"],
        body.dark-mode .btn-back,
        body.dark-mode .back-btn,
        body.dark-mode .purchase-step-prev-btn,
        body.dark-mode .pos-session-back-btn,
        body.dark-mode .prescription-analysis-cancel,
        body.dark-mode [data-action="back"] {
            color: #e2e8f0 !important;
            background-color: #242c38 !important;
            border-color: #475569 !important;
            box-shadow: none !important;
        }

        body.dark-mode .btn-secondary:hover,
        body.dark-mode .btn-secondary:focus,
        body.dark-mode .btn-secondary:active,
        body.dark-mode .btn-outline-secondary:hover,
        body.dark-mode .btn-outline-secondary:focus,
        body.dark-mode .btn-outline-secondary:active,
        body.dark-mode .btn-default:hover,
        body.dark-mode .btn-default:focus,
        body.dark-mode .btn-default:active,
        body.dark-mode .btn-light:hover,
        body.dark-mode .btn-light:focus,
        body.dark-mode .btn-light:active,
        body.dark-mode .modal-footer .btn-secondary:hover,
        body.dark-mode .modal-footer .btn-secondary:focus,
        body.dark-mode .modal-footer .btn-outline-secondary:hover,
        body.dark-mode .modal-footer .btn-outline-secondary:focus,
        body.dark-mode .modal-footer .btn-light:hover,
        body.dark-mode .modal-footer .btn-light:focus,
        body.dark-mode .modal-footer [data-dismiss="modal"]:hover,
        body.dark-mode .modal-footer [data-dismiss="modal"]:focus,
        body.dark-mode .btn-back:hover,
        body.dark-mode .btn-back:focus,
        body.dark-mode .back-btn:hover,
        body.dark-mode .back-btn:focus,
        body.dark-mode .purchase-step-prev-btn:hover,
        body.dark-mode .purchase-step-prev-btn:focus,
        body.dark-mode .pos-session-back-btn:hover,
        body.dark-mode .pos-session-back-btn:focus,
        body.dark-mode .prescription-analysis-cancel:hover,
        body.dark-mode .prescription-analysis-cancel:focus,
        body.dark-mode [data-action="back"]:hover,
        body.dark-mode [data-action="back"]:focus {
            color: #ffffff !important;
            background-color: #334155 !important;
            border-color: #64748b !important;
        }

        body.dark-mode .pos-session-back-btn i,
        body.dark-mode .btn-back i,
        body.dark-mode .back-btn i,
        body.dark-mode .purchase-step-prev-btn i {
            color: #cbd5e1 !important;
        }

        body.dark-mode .pos-session-back-btn:hover i,
        body.dark-mode .btn-back:hover i,
        body.dark-mode .back-btn:hover i,
        body.dark-mode .purchase-step-prev-btn:hover i {
            color: #ffffff !important;
        }
    </style>
    <!--[if lt IE 9]>
        <script src="assets/js/html5shiv.min.js"></script>
        <script src="assets/js/respond.min.js"></script>
    <![endif]-->
</head>
<body>
    <script>
        try {
            document.body.classList.toggle('mini-sidebar', localStorage.getItem('pharmacy-sidebar-collapsed') === 'true');
        } catch (error) {
        }
        document.body.classList.toggle('dark-mode', document.documentElement.classList.contains('dark-mode'));
        document.body.setAttribute('data-theme', document.documentElement.getAttribute('data-theme') || 'light');
        requestAnimationFrame(function () {
            document.documentElement.classList.remove('theme-initializing');
        });
    </script>
    <!-- Main Wrapper -->
    <div class="main-wrapper">

        <!-- Header -->
        @include('admin.includes.header')
        <!-- /Header -->

        <!-- Sidebar -->
        @include('admin.includes.sidebar')
        <!-- /Sidebar -->

        <!-- Page Wrapper -->
        <div class="page-wrapper">

            <div class="content container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div class="row">
                        @stack('page-header')
                    </div>
                </div>
                <!-- /Page Header -->
                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <x-alerts.danger :error="$error" />
                    @endforeach
                @endif

                @yield('content')
                <x-modals.inventory-check />
                <x-modals.notification-center />
            </div>
            </div>
        </div>
        <!-- /Page Wrapper -->

    </div>
    <!-- /Main Wrapper -->
    
<!-- jQuery -->
<script src="{{asset('assets/plugins/jquery/jquery.min.js')}}" data-turbo-eval="false"></script>

<!-- Bootstrap Core JS -->
<script src="{{asset('assets/js/popper.min.js')}}" data-turbo-eval="false"></script>
<script src="{{asset('assets/js/bootstrap.min.js')}}" data-turbo-eval="false"></script>
<script src="{{asset('assets/plugins/slimscroll/jquery.slimscroll.min.js')}}" data-turbo-eval="false"></script>
<!-- Sweet Alert Js -->
<script src="{{asset('assets/plugins/sweetalert2/sweetalert2.min.js')}}" data-turbo-eval="false"></script>
<!-- Snackbar Js -->
<script src="{{asset('assets/plugins/snackbar/snackbar.min.js')}}" data-turbo-eval="false"></script>
<script src="{{asset('assets/js/onscan.min.js')}}" data-turbo-eval="false"></script>
<!-- Select2 JS -->
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}" data-turbo-eval="false"></script>
<!-- Tabulator JS -->
<script src="{{asset('assets/plugins/tabulator/js/tabulator.min.js')}}" data-turbo-eval="false"></script>
<script src="{{asset('assets/js/tabulator-server.js')}}" data-turbo-eval="false"></script>
<!-- Custom JS -->
<script src="{{asset('assets/js/script.js')}}" data-turbo-eval="false"></script>
<script>
    /* Bootstrap modal rescue net: if ANY data-toggle="modal" trigger fires but the
       target modal never becomes visible (CSS conflict / Turbo render), force-show it. */
    $(document).off('click.pharmacyModalBridge').on('click.pharmacyModalBridge', '[data-toggle="modal"]', function (e) {
        var target = $(this).data('target') || $(this).attr('href');
        if (!target || target === '#') return;
        var $modal = $(target);
        if (!$modal.length || !$modal.hasClass('modal')) return;
        e.preventDefault();
        setTimeout(function () {
            if (!$modal.hasClass('show')) $modal.modal('show');
        }, 60);
    });

    $(document).on('hidden.bs.modal', '.modal', function () {
        if ($('.modal.show').length) return;
        $('body').removeClass('modal-open').css('padding-right', '');
        $('.modal-backdrop').remove();
        $('html').removeClass('menu-opened');
        $('.sidebar-overlay').removeClass('opened');
    });

    $(document).ready(function(){
        $('body').on('click','#deletebtn',function(){
            var id = $(this).data('id');
            var route = $(this).data('route');
            swal.queue([
                {
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    type: "warning",
                    showCancelButton: !0,
                    confirmButtonText: '<i class="fe fe-trash mr-1"></i> Delete!',
                    cancelButtonText: '<i class="fa fa-times mr-1"></i> Cancel!',
                    confirmButtonClass: "btn btn-success mt-2",
                    cancelButtonClass: "btn btn-danger ml-2 mt-2",
                    buttonsStyling: !1,
                    preConfirm: function(){
                        return new Promise(function(){
                            $.ajax({
                                url: route,
                                type: "DELETE",
                                data: {"id": id},
                                success: function(){
                                    swal.insertQueueStep(
                                        Swal.fire({
                                            title: "Deleted!",
                                            text: "Resource has been deleted.",
                                            type: "success",
                                            showConfirmButton: !1,
                                            timer: 1500,
                                        })
                                    )
                                    if (window.PharmaTabulator) {
                                        window.PharmaTabulator.reloadAll();
                                    } else if (window.jQuery && $.fn.DataTable) {
                                        $('.datatable').DataTable().ajax.reload();
                                    }
                                }
                            })

                        })
                    }
                }
            ]).catch(swal.noop);
        }); 
    });
    @if(Session::has('message'))
        (function () {
            var notificationShown = false;
            var notificationType = @json(Session::get('alert-type', 'info'));
            var notificationMessage = @json(Session::get('message'));

            function showFlashNotification() {
                if (notificationShown || !window.Snackbar) return;
                notificationShown = true;
                var colors = {
                    info: '#2196f3',
                    warning: '#e2a03f',
                    success: '#8dbf42',
                    danger: '#e7515a'
                };
                Snackbar.show({
                    text: notificationMessage,
                    pos: 'top-right',
                    actionTextColor: '#fff',
                    backgroundColor: colors[notificationType] || colors.info,
                    duration: 5000
                });
            }

            setTimeout(showFlashNotification, 100);
            document.addEventListener('turbo:load', function () {
                setTimeout(showFlashNotification, 100);
            }, { once: true });
        })();
    @endif
</script>
<script>
    function initDashboardDateTime() {
        const el = document.getElementById('dashboard-current-datetime');
        if (!el) {
            return;
        }

        if (window.pharmacyDashboardDateTimeInterval) {
            clearInterval(window.pharmacyDashboardDateTimeInterval);
        }

        function updateDateTime() {
            const now = new Date();
            const date = now.toLocaleDateString('en-US', {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
            const time = now.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            el.textContent = date + ' - ' + time;
        }

        updateDateTime();
        window.pharmacyDashboardDateTimeInterval = setInterval(updateDateTime, 1000);
    }

    /* Run on first load, on Turbo page navigations, and immediately (script sits at end of body). */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboardDateTime);
    } else {
        initDashboardDateTime();
    }
    document.addEventListener('turbo:load', initDashboardDateTime);

    document.addEventListener('turbo:before-cache', function () {
        document.querySelectorAll('form button[type="submit"], form input[type="submit"]').forEach(function (button) {
            button.disabled = false;
            button.removeAttribute('aria-busy');

            var originalText = button.getAttribute('data-original-submit-text');
            if (originalText && button.tagName === 'BUTTON') {
                button.innerHTML = originalText;
            }
        });

        document.querySelectorAll('form').forEach(function (form) {
            $(form).removeData('submitted');
        });
    });

    $(document).off('submit.pharmacySubmitLock', 'form').on('submit.pharmacySubmitLock', 'form', function(e){
        var $form = $(this);
        if ($form.data('submitted')) {
            e.preventDefault();
            return false;
        }
        $form.data('submitted', true);
        $form.find('button[type="submit"], input[type="submit"]').attr('disabled', true).each(function(){
            var $btn = $(this);
            if ($btn.is('button')) {
                if (!$btn.attr('data-original-submit-text')) {
                    $btn.attr('data-original-submit-text', $btn.html());
                }
                $btn.html('Submitting...');
            }
        });
    });
</script>
<div class="modal fade product-row-action-modal" id="rowActionPopup" tabindex="-1" role="dialog" aria-labelledby="rowActionPopupTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row-action-heading">
                    <h5 class="modal-title" id="rowActionPopupTitle">Actions</h5>
                    <span class="row-action-meta"><span class="row-action-context-prefix">Name</span> &rarr; <span class="row-action-context-value"></span><span class="row-action-context-secondary"></span></span>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row-action-popup-menu" id="row-action-popup-content"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="rowActionEditPopup" tabindex="-1" role="dialog" aria-labelledby="rowActionEditPopupTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rowActionEditPopupTitle">Edit</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-0 row-action-edit-body">
                <iframe class="row-action-edit-frame" title="Edit selected record" src="about:blank" allow="camera"></iframe>
                <div class="row-action-edit-loading" role="status">
                    <span class="row-action-edit-spinner" aria-hidden="true"></span>
                    <span>Loading edit form...</span>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    /* ═══ UNIVERSAL ROW ACTION MODAL STYLES (MATCHES OUTSTOCK ACTIONS) ═══ */
    .product-row-action-modal .modal-dialog,
    #rowActionPopup .modal-dialog {
        max-width: 420px;
        width: min(420px, calc(100vw - 32px));
    }
    .modal.fade {
        transition: opacity .35s ease !important;
    }
    .modal-backdrop.fade {
        transition: opacity .35s ease !important;
    }
    .modal .modal-content,
    .product-row-action-modal .modal-content,
    #rowActionPopup .modal-content,
    #rowActionEditPopup .modal-content {
        --animate-duration: .35s;
    }
    .modal.fade .modal-dialog {
        transition: none !important;
        transform: none !important;
    }
    .user-create-camera-modal.is-open .user-create-camera-dialog,
    .profile-camera-modal.is-open .profile-camera-dialog,
    .purchase-camera-modal.is-open .purchase-camera-dialog {
        animation: fadeIn .35s ease both;
    }
    .product-row-action-modal .modal-content,
    #rowActionPopup .modal-content {
        background: #ffffff;
        color: #1e293b;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        box-shadow: 0 16px 36px rgba(15, 23, 42, .2);
    }
    .product-row-action-modal .modal-header,
    #rowActionPopup .modal-header {
        border-bottom: 1px solid #e2e8f0;
        padding: 16px 20px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
    }
    .product-row-action-modal .modal-title,
    #rowActionPopup .modal-title {
        font-size: 1.15rem;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.25;
        margin: 0;
    }
    .product-row-action-modal .row-action-heading,
    #rowActionPopup .row-action-heading {
        min-width: 0;
        flex: 1 1 auto;
    }
    .product-row-action-modal .row-action-meta,
    #rowActionPopup .row-action-meta {
        color: #64748b;
        display: block;
        font-size: 13px;
        font-weight: 500;
        margin-top: 4px;
        overflow-wrap: anywhere;
    }
    .product-row-action-modal .modal-body,
    #rowActionPopup .modal-body {
        max-height: min(65vh, 480px);
        overflow-y: auto;
        padding: 8px 0;
    }
    .product-row-action-modal .dropdown-item,
    #rowActionPopup .row-action-popup-menu > .dropdown-item {
        padding: 10px 20px;
        white-space: normal;
        display: flex;
        align-items: center;
        width: 100%;
        font-size: 14px;
        color: #1e293b;
        border: 0;
        background: transparent;
        text-align: left;
        text-decoration: none;
    }
    .product-row-action-modal .dropdown-item:hover,
    .product-row-action-modal .dropdown-item:focus,
    #rowActionPopup .row-action-popup-menu > .dropdown-item:hover,
    #rowActionPopup .row-action-popup-menu > .dropdown-item:focus {
        background-color: #dbeafe !important;
        color: #1e3a8a !important;
    }
    .product-row-action-modal .dropdown-item.text-danger,
    #rowActionPopup .row-action-popup-menu > .dropdown-item.text-danger {
        color: #dc2626 !important;
    }
    .product-row-action-modal .dropdown-item.text-danger:hover,
    .product-row-action-modal .dropdown-item.text-danger:focus,
    #rowActionPopup .row-action-popup-menu > .dropdown-item.text-danger:hover,
    #rowActionPopup .row-action-popup-menu > .dropdown-item.text-danger:focus {
        color: #b91c1c !important;
    }
    .product-row-action-modal .dropdown-divider,
    #rowActionPopup .row-action-popup-menu > .dropdown-divider {
        margin: 6px 0;
        border-top: 1px solid #e2e8f0;
    }

    body.dark-mode .product-row-action-modal .modal-content,
    body.dark-mode #rowActionPopup .modal-content {
        background: #252b33;
        color: #e2e8f0;
        border-color: #475569;
    }
    body.dark-mode .product-row-action-modal .modal-header,
    body.dark-mode #rowActionPopup .modal-header {
        border-bottom-color: #475569;
    }
    body.dark-mode .product-row-action-modal .modal-title,
    body.dark-mode #rowActionPopup .modal-title {
        color: #e2e8f0;
    }
    body.dark-mode .product-row-action-modal .close,
    body.dark-mode #rowActionPopup .close {
        color: #e2e8f0;
        text-shadow: none;
        opacity: 0.8;
    }
    body.dark-mode .product-row-action-modal .close:hover,
    body.dark-mode #rowActionPopup .close:hover {
        opacity: 1;
    }
    body.dark-mode .product-row-action-modal .row-action-meta,
    body.dark-mode #rowActionPopup .row-action-meta {
        color: #a8b3c4;
    }
    body.dark-mode .product-row-action-modal .dropdown-item,
    body.dark-mode #rowActionPopup .row-action-popup-menu > .dropdown-item {
        color: #e2e8f0;
    }
    body.dark-mode .product-row-action-modal .dropdown-item:hover,
    body.dark-mode .product-row-action-modal .dropdown-item:focus,
    body.dark-mode #rowActionPopup .row-action-popup-menu > .dropdown-item:hover,
    body.dark-mode #rowActionPopup .row-action-popup-menu > .dropdown-item:focus {
        background-color: #bbf7d0 !important;
        color: #14532d !important;
    }
    body.dark-mode .product-row-action-modal .dropdown-item.text-danger,
    body.dark-mode #rowActionPopup .row-action-popup-menu > .dropdown-item.text-danger {
        color: #f87171 !important;
    }
    body.dark-mode .product-row-action-modal .dropdown-item.text-danger:hover,
    body.dark-mode .product-row-action-modal .dropdown-item.text-danger:focus,
    body.dark-mode #rowActionPopup .row-action-popup-menu > .dropdown-item.text-danger:hover,
    body.dark-mode #rowActionPopup .row-action-popup-menu > .dropdown-item.text-danger:focus {
        color: #b91c1c !important;
    }
    body.dark-mode .product-row-action-modal .dropdown-divider,
    body.dark-mode #rowActionPopup .row-action-popup-menu > .dropdown-divider {
        border-top: 1px solid #374151;
    }

    #rowActionEditPopup .modal-dialog {
        height: calc(100vh - 56px);
        max-width: 1100px;
        width: min(1100px, calc(100vw - 48px));
    }
    #rowActionEditPopup .modal-content { height: 100%; }
    #rowActionEditPopup .modal-body { min-height: 0; overflow: hidden; }
    #rowActionEditPopup .row-action-edit-body { position: relative; }
    #rowActionEditPopup .row-action-edit-frame { border: 0; display: block; height: 100%; visibility: hidden; width: 100%; }
    #rowActionEditPopup .row-action-edit-frame.row-action-edit-frame-ready { visibility: visible; }
    #rowActionEditPopup .row-action-edit-loading {
        align-items: center;
        background: #fff;
        color: #64748b;
        display: flex;
        gap: 10px;
        inset: 0;
        justify-content: center;
        position: absolute;
    }
    #rowActionEditPopup .row-action-edit-frame.row-action-edit-frame-ready + .row-action-edit-loading { display: none; }
    #rowActionEditPopup .row-action-edit-spinner {
        animation: row-action-edit-spin .8s linear infinite;
        border: 3px solid #dbeafe;
        border-radius: 50%;
        border-top-color: #2563eb;
        height: 22px;
        width: 22px;
    }
    @keyframes row-action-edit-spin { to { transform: rotate(360deg); } }
    body.dark-mode #rowActionEditPopup .row-action-edit-loading {
        background: #1b2027;
        color: #a8b3c4;
    }
    body.dark-mode #rowActionEditPopup .row-action-edit-spinner {
        border-color: #374151;
        border-top-color: #93c5fd;
    }
    @media (max-width: 767.98px) {
        #rowActionEditPopup .modal-dialog { height: 100dvh; margin: 0; max-width: none; width: 100vw; }
        #rowActionEditPopup .modal-content { border-radius: 0; height: 100dvh; }
    }
</style>
<script src="{{ asset('assets/js/modal-transitions.js') }}?v={{ filemtime(public_path('assets/js/modal-transitions.js')) }}" data-turbo-eval="false"></script>
<script src="{{ asset('assets/js/row-actions-popup.js') }}?v={{ filemtime(public_path('assets/js/row-actions-popup.js')) }}" data-turbo-eval="false"></script>
<script>
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.noti-single-dismiss');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();

    var notiId = btn.getAttribute('data-id');
    var row = btn.closest('.noti-item-row');
    var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    fetch('/notification/mark-single/' + notiId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json'
        }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data && data.success) {
            // Since we now show all recent (read + unread), just mark as read in place:
            // remove the unread class, remove the dot, remove the dismiss button
            if (row) {
                row.classList.remove('noti-item-unread');
                var dot = row.querySelector('.noti-unread-dot');
                if (dot) dot.remove();
                btn.remove();
            }

            // Update bell badge count
            var badge = document.querySelector('.notification-bell-link .notification-count');
            if (data.unread_count > 0) {
                if (badge) badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
            } else {
                if (badge) badge.remove();
                // Update "Mark All As Read" → "All caught up"
                var clearNoti = document.querySelector('.topnav-dropdown-header .clear-noti');
                if (clearNoti) {
                    clearNoti.outerHTML = '<span class="notification-empty-state-label">All caught up</span>';
                }
            }
        }
    })
    .catch(function(err) { console.error('Error marking notification as read:', err); });
});
</script>
<!-- Page JS -->
@stack('page-js')
</body>
</html>